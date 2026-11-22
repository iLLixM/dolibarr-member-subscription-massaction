<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
require_once __DIR__.'/invoiceprofile.class.php';
require_once __DIR__.'/databasestate.class.php';
require_once __DIR__.'/transactionprofile.class.php';
require_once __DIR__.'/coreguardedtransaction.class.php';
require_once __DIR__.'/filebookingcheckpoint.class.php';
/** Only the finite, pre-Core, non-SQL finance refusal may be rollback-qualified. */
class MsmQualifiedCoreFinanceBusinessError extends RuntimeException {}
/** Core business APIs only; the caller owns the outer transaction. */
class MsmInvoiceService
{
	protected $db;
	protected $databaseState;
	/** The active guard is explicitly injected, never constructed or inferred here. */
	protected $activeGuard;
	/** File-session capability from the Booking owner; never constructed by Invoice. */
	protected $coreCheckpoint;
	public function __construct($db, MsmDatabaseState $databaseState, MsmActiveTransactionGuard $activeGuard, MsmFileBookingCheckpoint $checkpoint = null)
	{
		$this->db = $db; $this->databaseState = $databaseState; $this->activeGuard = $activeGuard;
		$this->coreCheckpoint = $checkpoint;
		if (!$this->databaseState->isFor($db) || !$this->activeGuard->isFor($db)) {
			throw new InvalidArgumentException('MSMDatabaseUnsupported');
		}
	}
	protected function one($sql)
	{
		$res = $this->db->query($sql);
		if (!$res) { throw new RuntimeException('SubscriptionBulkDatabaseError'); }
		$row = $this->db->fetch_object($res); $this->db->free($res); return $row;
	}
	protected function invoice() { require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php'; return new Facture($this->db); }
	protected function customer() { require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php'; return new Societe($this->db); }
	/** Qualified Core source/trigger gate: do not replace with a UI/admin flag.
	 * This seam is overridden solely by isolated synthetic financial fixtures.
	 */
	protected function requireCoreFinanceQualification()
	{
		require_once __DIR__.'/corefinancequalification.class.php';
		MsmCoreFinanceQualification::requireReady();
	}
	protected function requireCoreProvisionalPathSafe($invoice)
	{
		require_once __DIR__.'/corefinancequalification.class.php';
		MsmCoreFinanceQualification::requireProvisionalPathSafe($invoice);
	}
	public function checkTables()
	{
		// The first Booking checkTables() runs BEFORE outer BEGIN.
		if ($this->activeGuard instanceof MsmCoreGuardedTransaction) { $this->requireCoreFinanceQualification(); }
		$this->databaseState->requireTransactionalTables(array('societe', 'societe_extrafields', 'socpeople', 'socpeople_extrafields', 'societe_commerciaux', 'facture', 'facture_extrafields', 'facturedet', 'facturedet_extrafields', 'element_element', 'ecm_files'));
	}
	public function lock($id)
	{
		// The booking owner checks engines before BEGIN and after all locks.
		$member = $this->one('SELECT fk_soc FROM '.$this->db->prefix().'adherent WHERE rowid = '.((int) $id).' FOR UPDATE');
		if (!$member) { throw new RuntimeException('NotEnoughPermissions'); }
		if ($member->fk_soc) { $this->one('SELECT rowid FROM '.$this->db->prefix().'societe WHERE rowid = '.((int) $member->fk_soc).' FOR UPDATE'); }
		$product = getDolGlobalInt('ADHERENT_PRODUCT_ID_FOR_SUBSCRIPTIONS');
		if ($product) {
			$this->one('SELECT rowid FROM '.$this->db->prefix().'product WHERE rowid = '.$product.' FOR UPDATE');
		}
	}
	/** Check after every nested API call, before any further business operation. */
	protected function owned()
	{
		$state = $this->activeGuard->active();
		if ($state['state'] !== MsmDatabaseState::ACTIVE || !$state['fresh'] || !$state['owned']) { throw new RuntimeException('MSMTransactionUnknown'); }
		$this->activeGuard->requireSupportedIsolation($state);
	}
	/** Encode confirmed plain text once for the HTML-capable Core invoice-line field. */
	protected function invoiceDescription($description)
	{
		return htmlspecialchars($description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', true);
	}
	/** Production always constructs the real authoritative profile. Test
	 * doubles can supply an explicitly isolated synthetic qualification state
	 * without bypassing the actual direct-service admission decision.
	 */
	protected function financeAdmissionProfile()
	{
		global $conf;
		return new MsmTransactionProfile($this->db, $this->databaseState, (int) $conf->entity);
	}
	/** Directly callable service must recheck authoritative profile before any writes. */
	protected function requireInvoiceAdmission()
	{
		$current = $this->financeAdmissionProfile()->current();
		if (empty($current['features']['invoice']) || $current['reason'] !== '') { throw new RuntimeException('MSMProfileFeatureUnavailable'); }
		// An explicitly injected guard must match the authoritative profile.
		// In particular, a future CoreGuarded feature admission must never
		// fall back to an Enhanced/P_S observer at this direct-service boundary.
		if ($current['profile'] === 'CoreGuarded') {
			if (!($this->activeGuard instanceof MsmCoreGuardedTransaction)) { throw new RuntimeException('MSMTransactionUnknown'); }
		} elseif (!($this->activeGuard instanceof MsmDatabaseState)) {
			throw new RuntimeException('MSMTransactionUnknown');
		}
	}
	public function create($member, $subscription, array $row, $actor, array &$diagnostic)
	{
		global $conf, $langs, $user;
		$diagnostic['stage'] = 'finance_precheck';
		$this->requireInvoiceAdmission();
		$this->owned();
		// No direct CoreGuarded invoice call without the original Controller's
		// already-consumed, owner/entity/profile/row-bound one-shot capability.
		if ($this->activeGuard instanceof MsmCoreGuardedTransaction) {
			$this->requireCoreFinanceQualification();
			if ($this->coreCheckpoint === null) { throw new RuntimeException('MSMCoreGuardedSessionUnsupported'); }
			$this->coreCheckpoint->requireFinanceContinuation((int) $member->id, $row, $actor);
		}
		if (!MsmPreviewService::canCreateSubscriptionWithOptions($actor, true, false)) {
			throw new MsmQualifiedCoreFinanceBusinessError('NotEnoughPermissions');
		}
		$plan = $row['invoice'] ?? null;
		if (!is_array($plan) || (int) ($plan['entity'] ?? 0) !== (int) $conf->entity
			|| (int) $member->entity !== (int) $conf->entity
			|| (empty($plan['create_customer']) && ((int) ($plan['customer_id'] ?? 0) <= 0
				|| (int) $member->socid !== (int) $plan['customer_id']))) {
			throw new MsmQualifiedCoreFinanceBusinessError('MSMChangedConfirmation');
		}
		$customerid = (int) $plan['customer_id'];
		if ($plan['create_customer'] && (!empty($member->socid) || empty($row['autocreate'])
			|| !$actor->hasRight('societe', 'creer'))) {
			throw new MsmQualifiedCoreFinanceBusinessError('MSMChangedConfirmation');
		}
		// Any subsequent Core call, SQL failure or postcondition error is
		// unqualified and remains UNKNOWN/STOP under CoreGuarded.
		$diagnostic['stage'] = 'customer';
		if ($plan['create_customer']) {
			$customer = $this->customer(); $fullname = $member->getFullName($langs);
			$name = $member->morphy === 'mor' ? $member->company : $fullname;
			$alias = $member->morphy === 'mor' ? $fullname : $member->company;
			$previous = $user;
			$this->owned(); // Before nested Core Societe::create_from_member().
			try { $user = $actor; $customerid = $customer->create_from_member($member, $name, $alias); }
			finally { $user = $previous; }
			$diagnostic['customer_id'] = max(0, (int) $customerid); $this->owned();
			if ($customerid <= 0) { throw new RuntimeException('MSMInvoiceFailed'); }
		}
		$customer = $this->customer();
		$this->owned(); // Before the Core customer readback.
		$customerFetched = $customer->fetch($customerid);
		$this->owned(); // Do not issue the member-link SELECT after lost ownership.
		if ($customerFetched <= 0 || (int) $customer->entity !== $plan['entity']) { throw new RuntimeException('MSMConsistencyError'); }
		$link = $this->one('SELECT fk_soc FROM '.$this->db->prefix().'adherent WHERE rowid = '.((int) $member->id));
		$this->owned(); // Before moving on to invoice creation.
		if (!$link || (int) $link->fk_soc !== $customerid) { throw new RuntimeException('MSMConsistencyError'); }
		$member->socid = $member->fk_soc = $customerid; $member->thirdparty = $customer;
		$invoice = $this->invoice();
		$invoice->type = 0; $invoice->entity = $plan['entity']; $invoice->socid = $customerid;
		$invoice->date = msmReadDate($plan['date']); $invoice->cond_reglement_id = $plan['term'];
		$invoice->mode_reglement_id = $plan['payment_mode']; $invoice->fk_account = $plan['bank'];
		$invoice->multicurrency_code = $plan['currency']; $invoice->model_pdf = $plan['pdf_model'];
		$invoice->linked_objects = array('subscription' => (int) $subscription);
		$diagnostic['stage'] = 'invoice_create';
		$this->owned();
		$id = $invoice->create($actor); $diagnostic['invoice_id'] = max(0, (int) $id); $this->owned();
		if ($id <= 0) { throw new RuntimeException('MSMInvoiceFailed'); }
		$diagnostic['stage'] = 'invoice_line';
		$this->owned(); // Before nested Core Facture::addline().
		$line = $invoice->addline($this->invoiceDescription($row['description']), 0, $plan['quantity'], $plan['vat'], 0, 0, $plan['product'], 0, $row['start'], '', 0, 0, 0, $plan['price_base'], $row['amount'], $plan['type']);
		$this->owned(); if ($line <= 0) { throw new RuntimeException('MSMInvoiceFailed'); }
		$diagnostic['stage'] = 'invoice_validate';
		// F36-02: the stock Core validator may rename an existing provisional
		// document directory before the OUTER transaction commits. Reject a
		// reachable source directory without modifying it.
		if ($this->activeGuard instanceof MsmCoreGuardedTransaction) {
			$this->requireCoreProvisionalPathSafe($invoice);
		}
		$this->owned(); // Before nested Core Facture::validate().
		$validated = $invoice->validate($actor); $this->owned();
		if ($validated <= 0) { throw new RuntimeException('MSMInvoiceFailed'); }
		$diagnostic['stage'] = 'invoice_postconditions';
		$this->owned(); // Prevent further SQL if the last Core API changed context.
		$this->verify($id, $customerid, $subscription, $row);
		$this->owned(); // Persisted row checks must not redeem lost ownership.
		$stored = $this->invoice();
		if ($stored->fetch($id) <= 0) { throw new RuntimeException('MSMConsistencyError'); }
		$this->owned();
		return array('id' => (int) $id, 'ref' => (string) $stored->ref, 'entity' => $plan['entity'], 'customer_id' => $customerid,
			'customer_created' => $plan['create_customer'], 'subscription_id' => (int) $subscription, 'member_id' => (int) $member->id,
			'pdf_model' => $plan['pdf_model'], 'pdf' => 'pending');
	}
	protected function verify($id, $customer, $subscription, array $row)
	{
		$p = $row['invoice'];
		$this->owned();
		$invoice = $this->one('SELECT entity, fk_soc, type, fk_statut, paye, total_ttc, ref, datef, fk_cond_reglement, fk_mode_reglement, fk_account, multicurrency_code FROM '.$this->db->prefix().'facture WHERE rowid = '.((int) $id));
		$this->owned(); // Invoice row readback cannot authorize the next SELECT.
		$line = $this->one('SELECT COUNT(*) AS n, SUM(qty) AS qty, SUM(total_ttc) AS total, MAX(description) AS description, MAX(fk_product) AS product, MAX(product_type) AS product_type, MAX(tva_tx) AS vat, MAX(date_start) AS date_start FROM '.$this->db->prefix().'facturedet WHERE fk_facture = '.((int) $id));
		$this->owned(); // Line readback cannot authorize the next SELECT.
		$link = $this->one("SELECT COUNT(*) AS n FROM ".$this->db->prefix()."element_element WHERE sourcetype = 'subscription' AND targettype = 'facture' AND fk_source = ".((int) $subscription).' AND fk_target = '.((int) $id));
		$this->owned(); // Link readback cannot authorize the next SELECT.
		$party = $this->one('SELECT client FROM '.$this->db->prefix().'societe WHERE rowid = '.((int) $customer));
		$this->owned(); // Party readback must be checked before any further Core API.
		if (!$invoice || (int) $invoice->entity !== $p['entity'] || (int) $invoice->fk_soc !== $customer || (int) $invoice->type !== 0
			|| (int) $invoice->fk_statut !== 1 || (int) $invoice->paye !== 0 || !$invoice->ref || strpos($invoice->ref, 'PROV') !== false
			|| abs((float) $invoice->total_ttc - $row['amount']) > 0.00000001
			|| substr((string) $invoice->datef, 0, 10) !== $p['date'] || (int) $invoice->fk_cond_reglement !== $p['term']
			|| (int) $invoice->fk_mode_reglement !== $p['payment_mode'] || (int) $invoice->fk_account !== $p['bank']
			|| (string) $invoice->multicurrency_code !== $p['currency']
			|| !$line || (int) $line->n !== 1 || (float) $line->qty !== (float) $p['quantity'] || abs((float) $line->total - $row['amount']) > 0.00000001
			|| (string) $line->description !== $this->invoiceDescription($row['description']) || (int) $line->product !== (int) $p['product']
			|| (int) $line->product_type !== (int) $p['type'] || abs((float) $line->vat - (float) $p['vat']) > 0.00000001
			|| (string) $line->date_start !== $this->db->idate($row['start'])
			|| !$link || (int) $link->n !== 1 || !$party || !in_array((int) $party->client, array(1, 3), true)) { throw new RuntimeException('MSMConsistencyError'); }
	}
}
