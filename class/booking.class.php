<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
require_once __DIR__.'/preview.class.php';
require_once __DIR__.'/corecompatibility.class.php';
require_once __DIR__.'/databasestate.class.php';
/** One owned transaction per member. Invoice operations share the owned transaction. */
class MsmBookingService
{
	private $db;
	protected $databaseState;
	public function __construct($db, MsmDatabaseState $databaseState = null)
	{
		$this->db = $db; $this->databaseState = $databaseState ?? new MsmDatabaseState($db);
		if (!$this->databaseState->isFor($db)) { throw new InvalidArgumentException('MSMDatabaseUnsupported'); }
	}
	protected function invoicing() { require_once __DIR__.'/invoice.class.php'; return new MsmInvoiceService($this->db, $this->databaseState); }
	protected function member($id)
	{
		require_once __DIR__.'/bookingmember.class.php';
		$member = new MsmBookingMember($this->db); $member->id = $id;
		return $member;
	}
	protected function currentUser($user)
	{
		$current = new User($this->db);
		if ($current->fetch($user->id) <= 0 || empty($current->status)) { throw new RuntimeException('NotEnoughPermissions'); }
		$current->loadRights('', 1);
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
	protected function requireOwned()
	{
		$state = $this->databaseState->active();
		if ($state['state'] !== MsmDatabaseState::ACTIVE || !$state['fresh'] || !$state['owned']) { throw new RuntimeException('MSMTransactionUnknown'); }
	}
	/** Read-only readiness; final locks repeat the checks under the owned transaction. */
	public function checkTables($invoice = false)
	{
		$this->databaseState->requireTransactionalTables(array('adherent', 'adherent_type', 'subscription'));
		if ($invoice) {
			$this->invoicing()->checkTables();
			if (getDolGlobalInt('ADHERENT_PRODUCT_ID_FOR_SUBSCRIPTIONS')) { $this->databaseState->requireTransactionalTables(array('product')); }
		}
	}
	protected function lock($id)
	{
		$this->checkTables();
		$row = $this->one('SELECT rowid, fk_adherent_type FROM '.$this->db->prefix().'adherent WHERE rowid = '.((int) $id).' FOR UPDATE');
		if (!$row) { throw new RuntimeException('NotEnoughPermissions'); }
		$this->one('SELECT rowid FROM '.$this->db->prefix().'adherent_type WHERE rowid = '.((int) $row->fk_adherent_type).' FOR UPDATE');
	}
	protected function verifyStored($id, $subscription, $member, array $row)
	{
		$saved = $this->one('SELECT fk_adherent, fk_type, subscription, dateadh, datef, note FROM '.$this->db->prefix().'subscription WHERE rowid = '.((int) $subscription));
		// E2: retain independent checks for analyzed contracts; Core remains the only writer.
		$policy = MsmCoreCompatibility::current();
		$latest = $policy['latest_end'] === null ? null : $this->one('SELECT '.($policy['latest_end'] ? 'MAX(datef) AS datef' : 'datef').' FROM '.$this->db->prefix().'subscription WHERE fk_adherent = '.((int) $id).($policy['latest_end'] ? '' : ' ORDER BY dateadh DESC'.$this->db->plimit(1)));
		$stored = $this->one('SELECT datefin FROM '.$this->db->prefix().'adherent WHERE rowid = '.((int) $id));
		// SQL NULL and Core's empty timestamp represent the same absent end.
		$objectEnd = ($member->datefin === null || $member->datefin === '') ? null : $this->db->idate($member->datefin);
		$storedEnd = $stored && $stored->datefin !== '' ? $stored->datefin : null;
		$latestEnd = $latest && $latest->datef !== '' ? $latest->datef : null;
		if ($member->msmEndUpdate <= 0 || !$saved || !$stored
			|| (int) $saved->fk_adherent !== (int) $id || (int) $saved->fk_type !== (int) $member->typeid
			|| abs((float) $saved->subscription - $row['amount']) > 0.00000001
			|| $saved->dateadh !== $this->db->idate($row['start']) || $saved->datef !== $this->db->idate($row['end'])
			|| (string) $saved->note !== $row['description']
			|| $storedEnd !== $member->msmCoreEnd
			|| $objectEnd !== $member->msmCoreEnd
			|| ($policy['latest_end'] !== null && (!$latest || $storedEnd !== $latestEnd))) {
			throw new RuntimeException('MSMConsistencyError');
		}
	}
	/** Roll back only our own context; ambiguous depth/connection failures stop the batch. */
	private function rollbackOwned()
	{
		$state = $this->databaseState->rollbackContext();
		// FAILED is reserved for future failed-but-open backends, never an alias for UNKNOWN.
		if (!in_array($state['state'], array(MsmDatabaseState::ACTIVE, MsmDatabaseState::FAILED), true) || !$state['owned'] || !$state['fresh']) { return false; }
		$acknowledged = $this->db->rollback();
		$ended = $this->databaseState->completed('ROLLBACK', $acknowledged);
		return $ended['state'] === MsmDatabaseState::IDLE && $ended['fresh'];
	}
	public function book($id, array $expected, array $options, $user, $overlapConfirmed)
	{
		$result = array('status' => 'failed', 'message' => 'MSMBookingFailed', 'id' => 0, 'amount' => 0, 'mail' => 'not_selected', 'stop' => false);
		if ((!empty($options['invoice']) && empty($expected['invoice'])) || (!empty($options['autocreate']) && empty($options['invoice']))) { $result['message'] = 'MSMUnsupportedAction'; return $result; }
		if (!MsmPreviewService::canCreateSubscriptionWithOptions($user, !empty($options['invoice']), !empty($options['sendmail']))) { $result['message'] = 'NotEnoughPermissions'; return $result; }
		// Never touch a transaction owned by another caller.
		if ((int) $this->db->transaction_opened !== 0) { $result['status'] = 'unknown'; $result['stop'] = true; return $result; }
		$owned = false; $beginAttempted = false; $committing = false; $invoice = null;
		$diagnostic = array('stage' => 'preflight', 'subscription_id' => 0, 'invoice_id' => 0, 'customer_id' => 0);
		try {
			MsmCoreCompatibility::booking($this->db); // Capability failures precede transactions/writes.
			$initial = $this->databaseState->beforeBegin();
			if ($initial['state'] !== MsmDatabaseState::IDLE || !$initial['fresh']) {
				$result['status'] = 'unknown'; $result['stop'] = true; $result['message'] = $initial['reason']; return $result;
			}
			$beginAttempted = true;
			if (!$this->db->begin()) { throw new RuntimeException('SubscriptionBulkDatabaseError'); }
			$owned = true;
			$begun = $this->databaseState->afterBegin();
			if ($begun['state'] !== MsmDatabaseState::ACTIVE || !$begun['fresh'] || !$begun['owned']) { throw new RuntimeException('MSMTransactionUnknown'); }
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
			$this->requireOwned();
			$diagnostic['stage'] = 'subscription';
			$subscription = $member->subscription($row['start'], $row['amount'], 0, '', $row['description'], '', '', '', $row['end'], $member->typeid);
			$diagnostic['subscription_id'] = max(0, (int) $subscription);
			$this->requireOwned();
			if ($subscription <= 0) { throw new RuntimeException('MSMBookingFailed'); }
			if (!empty($options['invoice'])) { $invoice = $this->invoicing()->create($member, $subscription, $row, $user, $diagnostic); }
			$this->verifyStored($id, $subscription, $member, $row);
			$this->requireOwned();
			$diagnostic['stage'] = 'commit';
			$committing = true;
			$acknowledged = $this->db->commit();
			$ended = $this->databaseState->completed('COMMIT', $acknowledged);
			if ($ended['state'] !== MsmDatabaseState::IDLE || !$ended['fresh']) { throw new RuntimeException('MSMTransactionUnknown'); }
			$owned = false;
			$result = array('status' => 'success', 'message' => 'MSMBooked', 'id' => (int) $subscription, 'amount' => $row['amount'], 'mail' => 'not_selected', 'stop' => false);
			// Internal only; never put live DB objects in the session/result renderer.
			$result['_member'] = $member;
			if ($invoice !== null) { $result['invoice'] = $invoice; }
		} catch (Throwable $e) {
			$safe = false;
			if (!$owned) { $result['stop'] = true; }
			if ($owned && !$committing) { try { $safe = $this->rollbackOwned(); } catch (Throwable $ignored) {} }
			if ($committing || ($beginAttempted && !$owned) || ($owned && !$safe)) { $result['status'] = 'unknown'; $result['stop'] = true; $result['message'] = 'MSMTransactionUnknown'; }
			else { $result['message'] = in_array($e->getMessage(), array('MSMCoreVersionUnsupported', 'MSMCoreContractUnsupported', 'MSMChangedConfirmation', 'MSMConsistencyError', 'MSMTransactionUnsupported', 'NotEnoughPermissions', 'MSMInvoiceFailed'), true) ? $e->getMessage() : 'MSMBookingFailed'; }
		}
		if ($result['status'] !== 'success') { $result['diagnostic'] = $diagnostic; $result['rollback'] = $owned && !$committing && !empty($safe) ? 'confirmed' : 'unconfirmed'; }
		return $result;
	}
}
