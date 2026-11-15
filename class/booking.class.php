<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
require_once __DIR__.'/preview.class.php';
require_once __DIR__.'/corecompatibility.class.php';
/** One owned transaction per member. Invoice operations share the owned transaction. */
class MsmBookingService
{
	private $db;
	public function __construct($db) { $this->db = $db; }
	protected function invoicing() { require_once __DIR__.'/invoice.class.php'; return new MsmInvoiceService($this->db); }
	protected function member($id)
	{
		require_once __DIR__.'/bookingmember.class.php';
		$member = new MsmBookingMember($this->db); $member->id = $id;
		return $member;
	}
	protected function currentUser($user)
	{
		$current = new User($this->db);
		if ($current->fetch($user->id) <= 0 || empty($current->statut)) { throw new RuntimeException('NotEnoughPermissions'); }
		$current->getrights('', 1);
		return $current;
	}
	protected function preview($member) { return new MsmPreviewService($this->db, $member); }
	protected function one($sql)
	{
		$res = $this->db->query($sql);
		if (!$res) { throw new RuntimeException('SubscriptionBulkDatabaseError'); }
		$row = $this->db->fetch_object($res); $this->db->free($res);
		return $row;
	}
	protected function transactionActive()
	{
		$row = $this->one('SELECT @@in_transaction AS active');
		return $row && (int) $row->active === 1;
	}
	protected function lock($id)
	{
		// This adapter is intentionally limited to the validated MySQL/MariaDB driver.
		if ($this->db->type !== 'mysqli') { throw new RuntimeException('MSMTransactionUnsupported'); }
		foreach (array('adherent', 'adherent_type', 'subscription') as $table) {
			$row = $this->one("SELECT ENGINE AS engine FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '".$this->db->escape($this->db->prefix().$table)."'");
			if (!$row || strtoupper($row->engine) !== 'INNODB') { throw new RuntimeException('MSMTransactionUnsupported'); }
		}
		$row = $this->one('SELECT rowid, fk_adherent_type FROM '.$this->db->prefix().'adherent WHERE rowid = '.((int) $id).' FOR UPDATE');
		if (!$row) { throw new RuntimeException('NotEnoughPermissions'); }
		$this->one('SELECT rowid FROM '.$this->db->prefix().'adherent_type WHERE rowid = '.((int) $row->fk_adherent_type).' FOR UPDATE');
	}
	protected function verifyStored($id, $subscription, $member, array $row)
	{
		$saved = $this->one('SELECT fk_adherent, fk_type, subscription, dateadh, datef, note FROM '.$this->db->prefix().'subscription WHERE rowid = '.((int) $subscription));
		// E1: preserve the actual pinned Core policy; never repair Core data here.
		$policy = MsmCoreCompatibility::current();
		$latest = $this->one('SELECT '.($policy['latest_end'] ? 'MAX(datef) AS datef' : 'datef').' FROM '.$this->db->prefix().'subscription WHERE fk_adherent = '.((int) $id).($policy['latest_end'] ? '' : ' ORDER BY dateadh DESC'.$this->db->plimit(1)));
		$stored = $this->one('SELECT datefin FROM '.$this->db->prefix().'adherent WHERE rowid = '.((int) $id));
		if ($member->msmEndUpdate <= 0 || !$saved || !$latest || !$stored
			|| (int) $saved->fk_adherent !== (int) $id || (int) $saved->fk_type !== (int) $member->typeid
			|| abs((float) $saved->subscription - $row['amount']) > 0.00000001
			|| $saved->dateadh !== $this->db->idate($row['start']) || $saved->datef !== $this->db->idate($row['end'])
			|| (string) $saved->note !== $row['description'] || $stored->datefin !== $latest->datef) {
			throw new RuntimeException('MSMConsistencyError');
		}
	}
	/** Roll back only our own context; ambiguous depth/connection failures stop the batch. */
	private function rollbackOwned()
	{
		if ((int) $this->db->transaction_opened !== 1 || !$this->transactionActive()) { return false; }
		return $this->db->rollback() && (int) $this->db->transaction_opened === 0 && !$this->transactionActive();
	}
	public function book($id, array $expected, array $options, $user, $overlapConfirmed)
	{
		$result = array('status' => 'failed', 'message' => 'MSMBookingFailed', 'id' => 0, 'amount' => 0, 'mail' => 'not_selected', 'stop' => false);
		if ((!empty($options['invoice']) && empty($expected['invoice'])) || (!empty($options['autocreate']) && empty($options['invoice']))) { $result['message'] = 'MSMUnsupportedAction'; return $result; }
		if (!MsmPreviewService::canCreateSubscriptionWithOptions($user, !empty($options['invoice']), !empty($options['sendmail']))) { $result['message'] = 'NotEnoughPermissions'; return $result; }
		// Never touch a transaction owned by another caller.
		if ((int) $this->db->transaction_opened !== 0) { $result['status'] = 'unknown'; $result['stop'] = true; return $result; }
		$owned = false; $committing = false; $invoice = null;
		$diagnostic = array('stage' => 'preflight', 'subscription_id' => 0, 'invoice_id' => 0, 'customer_id' => 0);
		try {
			MsmCoreCompatibility::current(); // Reject unknown semantics before any transaction/write.
			if ($this->transactionActive()) { $result['status'] = 'unknown'; $result['stop'] = true; return $result; }
			if (!$this->db->begin()) { throw new RuntimeException('SubscriptionBulkDatabaseError'); }
			$owned = true;
			$this->lock($id);
			if (!empty($options['invoice'])) { $this->invoicing()->lock($id); }
			$user = $this->currentUser($user);
			if (!MsmPreviewService::canCreateSubscriptionWithOptions($user, !empty($options['invoice']), !empty($options['sendmail']))) { throw new RuntimeException('NotEnoughPermissions'); }
			$member = $this->member($id);
			$row = $this->preview($member)->previewSubscriptionWithOptions($user, $expected['start'], !empty($options['invoice']), $options['sendmail'], $expected['end'], !empty($options['autocreate']), $options['description']);
			if ($row['errors'] || !hash_equals($expected['fingerprint'], $row['fingerprint']) || ($row['overlaps'] && !$overlapConfirmed)) {
				$result['status'] = 'skipped';
				throw new RuntimeException('MSMChangedConfirmation');
			}
			$diagnostic['stage'] = 'subscription';
			$subscription = $member->subscription($row['start'], $row['amount'], 0, '', $row['description'], '', '', '', $row['end'], $member->typeid);
			$diagnostic['subscription_id'] = max(0, (int) $subscription);
			if ((int) $this->db->transaction_opened !== 1 || !$this->transactionActive()) { throw new RuntimeException('MSMTransactionUnknown'); }
			if ($subscription <= 0) { throw new RuntimeException('MSMBookingFailed'); }
			if (!empty($options['invoice'])) { $invoice = $this->invoicing()->create($member, $subscription, $row, $user, $diagnostic); }
			$this->verifyStored($id, $subscription, $member, $row);
			$diagnostic['stage'] = 'commit';
			$committing = true;
			if (!$this->db->commit() || (int) $this->db->transaction_opened !== 0 || $this->transactionActive()) { throw new RuntimeException('MSMTransactionUnknown'); }
			$owned = false;
			$result = array('status' => 'success', 'message' => 'MSMBooked', 'id' => (int) $subscription, 'amount' => $row['amount'], 'mail' => 'not_selected', 'stop' => false);
			// Internal only; never put live DB objects in the session/result renderer.
			$result['_member'] = $member;
			if ($invoice !== null) { $result['invoice'] = $invoice; }
		} catch (Throwable $e) {
			$safe = false;
			if (!$owned) { $result['stop'] = true; }
			if ($owned && !$committing) { try { $safe = $this->rollbackOwned(); } catch (Throwable $ignored) {} }
			if ($committing || ($owned && !$safe)) { $result['status'] = 'unknown'; $result['stop'] = true; $result['message'] = 'MSMTransactionUnknown'; }
			else { $result['message'] = in_array($e->getMessage(), array('MSMCoreVersionUnsupported', 'MSMChangedConfirmation', 'MSMConsistencyError', 'MSMTransactionUnsupported', 'NotEnoughPermissions', 'MSMInvoiceFailed'), true) ? $e->getMessage() : 'MSMBookingFailed'; }
		}
		if ($result['status'] !== 'success') { $result['diagnostic'] = $diagnostic; $result['rollback'] = $owned && !$committing && !empty($safe) ? 'confirmed' : 'unconfirmed'; }
		return $result;
	}
}
