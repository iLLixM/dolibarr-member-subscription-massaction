<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

require_once __DIR__.'/period.class.php';
/** Composition over the V22 member object; all database operations are reads. */
class MsmPreviewService
{
	public $db;
	public $member;
	private $memberAccessible = false;
	public function __construct($db, $member) { $this->db = $db; $this->member = $member; }

	/**
	 * Check permissions for the subscription batch and its optional actions.
	 * @param User $user User
	 * @param bool $invoice Create and validate an invoice
	 * @param bool $sendmail Send a confirmation
	 * @return bool
	 */
	public static function canCreateSubscriptionWithOptions($user, $invoice = false, $sendmail = false)
	{
		if (!empty($user->socid) || !isModEnabled('member') || !$user->hasRight('adherent', 'lire') || !$user->hasRight('adherent', 'cotisation', 'creer')) {
			return false;
		}
		if ($invoice && (!isModEnabled('invoice') || !isModEnabled('societe') || !$user->hasRight('societe', 'lire') || !$user->hasRight('facture', 'lire') || !$user->hasRight('facture', 'creer'))) {
			return false;
		}
		if ($invoice && getDolGlobalInt('MAIN_USE_ADVANCED_PERMS') && (!$user->hasRight('facture', 'invoice_advance', 'validate') || ($sendmail && !$user->hasRight('facture', 'invoice_advance', 'send')))) {
			return false;
		}
		return true;
	}

	/**
	 * Resolve an individual amount; an explicit zero type amount is valid and never missing.
	 * @param int|float|string|null $configured Type amount
	 * @return float Non-negative gross amount
	 */
	public function getSubscriptionAmountForBatch($configured)
	{
		$amount = $configured;
		if ($amount === null || $amount === '') {
			$sql = "SELECT subscription FROM ".$this->db->prefix()."subscription WHERE fk_adherent = ".((int) $this->member->id);
			$sql .= ' ORDER BY datec DESC, rowid DESC'.$this->db->plimit(1);
			$res = $this->db->query($sql);
			if (!$res) {
				throw new RuntimeException('SubscriptionBulkDatabaseError');
			}
			$row = $this->db->fetch_object($res);
			$amount = $row ? $row->subscription : null;
			$this->db->free($res);
		}
		if (!is_numeric($amount) || !is_finite((float) $amount) || (float) $amount < 0) {
			throw new RuntimeException('SubscriptionBulkAmountMissing');
		}
		return (float) $amount;
	}

	/**
	 * Load and authorize the member/type before processing one batch entry.
	 * @param User $user User
	 * @return AdherentType
	 */
	protected function subscriptionTypeForBatch($user)
	{
		require_once DOL_DOCUMENT_ROOT.'/adherents/class/adherent_type.class.php';
		$fetched = $this->member->fetch($this->member->id, '', 0, '', false, false);
		if ($fetched < 0) { throw new RuntimeException('SubscriptionBulkDatabaseError'); }
		if ($fetched === 0 || !in_array((int) $this->member->entity, array_map('intval', explode(',', getEntity('adherent'))), true)
			|| restrictedArea($user, 'adherent', $this->member->id, '', '', 'socid', 'rowid', 0, 1) <= 0) {
			throw new RuntimeException('NotEnoughPermissions');
		}
		$this->memberAccessible = true;
		$res = $this->db->query('SELECT rowid FROM '.$this->db->prefix().'adherent_type WHERE rowid = '.((int) $this->member->typeid).' AND entity IN ('.getEntity('adherent_type').')');
		if (!$res) { throw new RuntimeException('SubscriptionBulkDatabaseError'); }
		if (!$this->db->fetch_object($res)) {
			throw new RuntimeException('NotEnoughPermissions');
		}
		$type = new AdherentType($this->db);
		$fetched = $type->fetch($this->member->typeid);
		if ($fetched < 0) { throw new RuntimeException('SubscriptionBulkDatabaseError'); }
		if ($fetched === 0) { throw new RuntimeException('SubscriptionBulkIneligible'); }
		self::validateEligibility($this->member, $type);
		return $type;
	}

	/** Validate business eligibility after authorizing both objects. */
	public static function validateEligibility($member, $type)
	{
		if ($member->status != Adherent::STATUS_VALIDATED || $type->status != 1 || !$type->subscription) {
			throw new RuntimeException('SubscriptionBulkIneligible');
		}
		$allowed = getDolGlobalString('MEMBER_SUBSCRIPTION_ALLOWED_FOR_STATUS');
		if ($allowed !== '' && !in_array((int) $member->status, array_map('intval', explode(',', $allowed)), true)) {
			throw new RuntimeException('SubscriptionBulkIneligible');
		}
		if (getDolGlobalString('MEMBER_NEWFORM_DOLIBARRTURNOVER') && $member->morphy === 'mor') {
			throw new RuntimeException('SubscriptionBulkSpecialAmount');
		}
	}

	/**
	 * Reject duplicate start days and return existing inclusive overlaps for review.
	 * @param int $start Start date
	 * @param int $end End date
	 * @return array<int,array{id:int,start:?string,end:?string}> Overlap snapshot, empty if none
	 */
	public function checkSubscriptionPeriodForBatch($start, $end)
	{
		if (!is_numeric($start) || !is_numeric($end) || $start <= 0 || $end <= 0) {
			throw new RuntimeException('SubscriptionBulkInvalidDate');
		}
		$daystart = MsmPeriodService::subscriptionCalendarShift($start, 0, 'd');
		if (MsmPeriodService::subscriptionCalendarShift($end, 0, 'd') <= $daystart) {
			throw new RuntimeException('SubscriptionBulkEndNotAfterStart');
		}
		$nextstart = MsmPeriodService::subscriptionCalendarShift($start, 1, 'd');
		$nextend = MsmPeriodService::subscriptionCalendarShift($end, 1, 'd');
		$sql = "SELECT rowid FROM ".$this->db->prefix()."subscription WHERE fk_adherent = ".((int) $this->member->id);
		$sql .= " AND dateadh >= '".$this->db->idate($daystart)."' AND dateadh < '".$this->db->idate($nextstart)."'";
		$sql .= $this->db->plimit(1);
		$res = $this->db->query($sql);
		if (!$res) {
			throw new RuntimeException('SubscriptionBulkDatabaseError');
		}
		$duplicate = $this->db->fetch_object($res);
		$this->db->free($res);
		if ($duplicate) {
			throw new RuntimeException('SubscriptionBulkDuplicateStart');
		}
		$sql = "SELECT rowid, dateadh, datef FROM ".$this->db->prefix()."subscription WHERE fk_adherent = ".((int) $this->member->id);
		$sql .= " AND (dateadh IS NULL OR dateadh < '".$this->db->idate($nextend)."')";
		$sql .= " AND (datef IS NULL OR datef >= '".$this->db->idate($daystart)."') ORDER BY rowid";
		$res = $this->db->query($sql);
		if (!$res) {
			throw new RuntimeException('SubscriptionBulkDatabaseError');
		}
		$overlaps = array();
		while ($existing = $this->db->fetch_object($res)) {
			$overlaps[] = array('id' => (int) $existing->rowid, 'start' => $existing->dateadh, 'end' => $existing->datef);
		}
		$this->db->free($res);
		return $overlaps;
	}

	/**
	 * Resolve the linked third party; return true only for a permitted planned creation.
	 * @param User $user User
	 * @param bool $invoice Invoice option
	 * @param bool $autocreate Explicit creation option
	 * @return bool True if a third party must be created by the core complementary action
	 */
	protected function subscriptionThirdPartyForBatch($user, $invoice, $autocreate)
	{
		global $conf;
		$this->member->thirdparty = null;
		if ($this->member->socid > 0) {
			$fetched = isModEnabled('societe') && $user->hasRight('societe', 'lire') ? $this->member->fetch_thirdparty() : 0;
			if ($fetched < 0) { throw new RuntimeException('SubscriptionBulkDatabaseError'); }
			if ($fetched > 0
				&& is_object($this->member->thirdparty)
				&& in_array((int) $this->member->thirdparty->entity, array_map('intval', explode(',', getEntity('societe'))), true)
				&& checkUserAccessToObject($user, array('societe'), $this->member->thirdparty)) {
				return false;
			}
			$this->member->thirdparty = null;
			if ($invoice) {
				throw new RuntimeException('MSMThirdPartyInaccessible');
			}
		} elseif ($invoice) {
			if (!$autocreate) {
				throw new RuntimeException('SubscriptionBulkThirdPartyMissing');
			}
			if (!isModEnabled('societe') || !$user->hasRight('societe', 'creer') || (int) $this->member->entity !== (int) $conf->entity) {
				throw new RuntimeException('NotEnoughPermissions');
			}
			return true;
		}
		return false;
	}

	protected function invoiceProfile() { require_once __DIR__.'/invoiceprofile.class.php'; return new MsmInvoiceProfile($this->db); }

	/**
	 * Read and validate planned values without creating records or sending mail.
	 * @param User $user User
	 * @param int|null $date Start date, null for an individual suggestion
	 * @param bool $invoice Invoice option
	 * @param bool $sendmail Mail option
	 * @param int|null $end Confirmed end, null for a duration-based suggestion
	 * @param bool $autocreate Explicit third-party creation option
	 * @param string|null $descriptiontemplate Plain text template, null for the legacy label
	 * @return array<string,mixed> Display values, errors, warnings and server-side snapshot
	 */
	public function previewSubscriptionWithOptions($user, $date, $invoice, $sendmail, $end = null, $autocreate = false, $descriptiontemplate = null)
	{
		require_once __DIR__.'/../lib/preview.lib.php';
		global $langs;
		$row = array('member' => '', 'type' => '', 'quantity' => 0, 'unit' => '', 'amount' => null, 'start' => $date, 'end' => $end, 'thirdparty' => '', 'description' => '', 'overlaps' => array(), 'notices' => array(), 'errors' => array(), 'warnings' => array(), 'status' => 'error', 'autocreate' => false, 'fingerprint' => '');
		if (!self::canCreateSubscriptionWithOptions($user)) {
			$row['errors'][] = 'NotEnoughPermissions';
			if (in_array('SubscriptionBulkDatabaseError', $row['errors'], true)) { throw new RuntimeException('SubscriptionBulkDatabaseError'); }
			$row['notices'] = $row['errors'];
			return $row;
		}
		try {
			$type = $this->subscriptionTypeForBatch($user);
			$row['member'] = $this->member->getFullName($langs);
			$row['type'] = $type->label;
			$duration = MsmPeriodService::duration($type);
			$row['quantity'] = $duration[0];
			$row['unit'] = $duration[1];
		} catch (Throwable $e) {
			if ($this->memberAccessible) { $row['member'] = $this->member->getFullName($langs); }
			$row['errors'][] = in_array($e->getMessage(), array('NotEnoughPermissions', 'SubscriptionBulkIneligible', 'SubscriptionBulkSpecialAmount', 'SubscriptionBulkInvalidDate'), true) ? $e->getMessage() : 'SubscriptionBulkDatabaseError';
			if (in_array('SubscriptionBulkDatabaseError', $row['errors'], true)) { throw new RuntimeException('SubscriptionBulkDatabaseError'); }
			$row['notices'] = $row['errors'];
			return $row;
		}
		try {
			$row['amount'] = $this->getSubscriptionAmountForBatch($type->amount);
		} catch (Throwable $e) {
			$row['errors'][] = $e->getMessage() === 'SubscriptionBulkAmountMissing' ? $e->getMessage() : 'SubscriptionBulkDatabaseError';
		}
		try {
			$row['start'] = $date === null ? MsmPeriodService::subscriptionStartDateForBatch($this->member, dol_now()) : $date;
			$row['end'] = $end === null ? MsmPeriodService::subscriptionEndDateForBatch($row['start'], $type) : $end;
			$row['overlaps'] = $this->checkSubscriptionPeriodForBatch($row['start'], $row['end']);
			if ($row['overlaps']) {
				$row['warnings'][] = 'SubscriptionBulkOverlapWarning';
			}
		} catch (Throwable $e) {
			$row['errors'][] = in_array($e->getMessage(), array('SubscriptionBulkDuplicateStart', 'SubscriptionBulkDatabaseError', 'SubscriptionBulkEndNotAfterStart'), true) ? $e->getMessage() : 'SubscriptionBulkInvalidDate';
		}
		try {
			$row['description'] = msmDescription($descriptiontemplate === null ? $langs->transnoentities('Subscription') : $descriptiontemplate, $row['type'], $row['start'], $row['end']);
		} catch (InvalidArgumentException $e) {
			$row['errors'][] = 'SubscriptionBulkDescriptionInvalid';
		}
		try {
			$row['autocreate'] = $this->subscriptionThirdPartyForBatch($user, $invoice, $autocreate);
			if ($row['autocreate']) {
				$row['thirdparty'] = $this->member->morphy === 'mor' ? $this->member->company : $row['member'];
				$row['warnings'][] = 'SubscriptionBulkThirdPartyPlanned';
			} elseif (is_object($this->member->thirdparty)) {
				$row['thirdparty'] = $this->member->thirdparty->name;
			}
		} catch (Throwable $e) {
			$row['errors'][] = in_array($e->getMessage(), array('NotEnoughPermissions', 'SubscriptionBulkThirdPartyMissing', 'MSMThirdPartyInaccessible'), true) ? $e->getMessage() : 'SubscriptionBulkDatabaseError';
		}
		if (!self::canCreateSubscriptionWithOptions($user, $invoice, $sendmail)) {
			$row['errors'][] = 'NotEnoughPermissions';
		}
		if ($invoice && isModEnabled('stock') && getDolGlobalInt('STOCK_CALCULATE_ON_BILL')) {
			$row['errors'][] = 'ErrorMassValidationNotAllowedWhenStockIncreaseOnAction';
		}
		if ($invoice && getDolGlobalString('ADHERENT_PRODUCT_ID_FOR_SUBSCRIPTIONS')) {
			require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
			$product = new Product($this->db);
			$result = $product->fetch(getDolGlobalInt('ADHERENT_PRODUCT_ID_FOR_SUBSCRIPTIONS'));
			if ($result < 0) { throw new RuntimeException('SubscriptionBulkDatabaseError'); }
			if ($result === 0 || $product->status != 1 || !(isModEnabled('product') || isModEnabled('service'))
				|| !in_array((int) $product->entity, array_map('intval', explode(',', getEntity('product'))), true)
				|| !checkUserAccessToObject($user, array('produit', 'service'), $product)) {
				$row['errors'][] = 'MSMProductUnavailable';
			}
		}
		if ($invoice && !$row['errors']) {
			try { $row['invoice'] = $this->invoiceProfile()->resolve($this->member, $user, $row['autocreate'], $sendmail); }
			catch (Throwable $e) { $row['errors'][] = in_array($e->getMessage(), array('NotEnoughPermissions', 'MSMProductUnavailable', 'SubscriptionBulkThirdPartyMissing', 'MSMInvoiceVatIdUnsupported', 'MSMCoreVersionUnsupported'), true) ? $e->getMessage() : 'MSMInvoiceProfileUnsupported'; }
		}
		if ($sendmail && !isValidEmail((string) $this->member->email)) {
			$row['warnings'][] = 'SubscriptionBulkEmailMissing';
		}
		if ($sendmail && getDolGlobalInt('MAIN_DISABLE_ALL_MAILS')) {
			$row['warnings'][] = 'SubscriptionBulkEmailDisabled';
		}
		if (in_array('SubscriptionBulkDatabaseError', $row['errors'], true)) { throw new RuntimeException('SubscriptionBulkDatabaseError'); }
		$row['notices'] = array_merge($row['errors'], $row['warnings']);
		$row['status'] = $row['errors'] ? 'error' : ($row['warnings'] ? 'warning' : 'ready');
		// Kept in the session only; detects changed amounts, recipients, links and settings.
		$source = array($this->member->typeid, $this->member->socid, $this->member->entity, $this->member->email, $this->member->default_lang, $this->member->morphy, $this->member->company, $this->member->address, $this->member->zip, $this->member->town, $this->member->country_id, $this->member->phone);
		foreach (array('ADHERENT_BANK_USE', 'ADHERENT_PRODUCT_ID_FOR_SUBSCRIPTIONS', 'ADHERENT_VAT_FOR_SUBSCRIPTIONS', 'ADHERENT_EMAIL_TEMPLATE_SUBSCRIPTION', 'ADHERENT_MAIL_FROM', 'FACTURE_ADDON_PDF') as $setting) {
			$source[] = getDolGlobalString($setting);
		}
		$row['fingerprint'] = hash('sha256', json_encode(array($row, $source, $invoice, $sendmail, $autocreate, $descriptiontemplate)));
		return $row;
	}
}
