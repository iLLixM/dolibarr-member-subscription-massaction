<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
require __DIR__.'/booking.php';
require __DIR__.'/../../class/invoice.class.php';
$beforeInvoice = $passed;
class InvoiceDatabaseDouble extends BookingDatabase
{
	public $invoice = null; public $invoiceLine = null; public $link = false; public $customerId = 4; public $mode = ''; public $calls = array(); public $nonTransactionalTable = '';
	public function query($sql)
	{
		if ($this->nonTransactionalTable !== '' && strpos($sql, 'SELECT ENGINE AS engine') === 0 && strpos($sql, "'test_".$this->nonTransactionalTable."'") !== false) {
			$this->queries[] = $sql; return (object) array('rows' => array((object) array('engine' => 'MyISAM')));
		}
		if (strpos($sql, 'SELECT fk_soc FROM') === 0) { $r = (object) array('fk_soc' => $this->customerId); }
		elseif (strpos($sql, 'SELECT entity, fk_soc') === 0) { $r = $this->invoice; }
		elseif (strpos($sql, 'SELECT COUNT(*) AS n, SUM(qty)') === 0) { $r = $this->invoiceLine; }
		elseif (strpos($sql, 'SELECT COUNT(*) AS n FROM test_element_element') === 0) { $r = (object) array('n' => $this->link ? 1 : 0); }
		elseif (strpos($sql, 'SELECT client FROM') === 0) { $r = (object) array('client' => 1); }
		else { return parent::query($sql); }
		$this->queries[] = $sql;
		return (object) array('rows' => $r ? array($r) : array());
	}
	public function rollback() { $this->invoice = null; $this->invoiceLine = null; $this->link = false; return parent::rollback(); }
}
class InvoiceObjectDouble
{
	private $db; public $ref = 'INV-SYNTHETIC';
	public function __construct($db) { $this->db = $db; }
	public function create($user)
	{
		$this->db->calls[] = 'invoice-create';
		if ($this->db->mode === 'create') { return -1; }
		$this->db->invoice = (object) array('entity' => $this->entity, 'fk_soc' => $this->socid, 'type' => $this->type, 'fk_statut' => 0, 'paye' => 0, 'total_ttc' => 0, 'ref' => $this->ref,
			'datef' => $this->db->idate($this->date), 'fk_cond_reglement' => $this->cond_reglement_id, 'fk_mode_reglement' => $this->mode_reglement_id,
			'fk_account' => $this->fk_account, 'multicurrency_code' => $this->multicurrency_code);
		$this->db->link = $this->linked_objects === array('subscription' => 9);
		return 42;
	}
	public function addline(...$args)
	{
		$this->db->calls[] = 'invoice-line';
		verify($args[1] === 0 && $args[2] === 1 && $args[13] === 'TTC' && $args[15] === 1, 'core gross-price arguments');
		if ($this->db->mode === 'line') { return -1; }
		$this->db->invoiceLine = (object) array('n' => 1, 'qty' => $args[2], 'total' => $args[14], 'description' => $args[0],
			'product' => $args[6], 'product_type' => $args[15], 'vat' => $args[3], 'date_start' => $this->db->idate($args[8]));
		$this->db->invoice->total_ttc = $args[14];
		return 100;
	}
	public function validate($user)
	{
		$this->db->calls[] = 'invoice-validate';
		if ($this->db->mode === 'validate') { return -1; }
		if ($this->db->mode === 'link') { $this->db->link = false; }
		if ($this->db->mode === 'amount') { $this->db->invoice->total_ttc++; }
		if ($this->db->mode === 'depth') { $this->db->transaction_opened = 0; $this->db->active = false; }
		$this->db->invoice->fk_statut = 1;
		return 1;
	}
	public function fetch($id) { return 1; }
}
class InvoiceCustomerDouble
{
	private $db; public $entity = 1;
	public function __construct($db) { $this->db = $db; }
	public function fetch($id) { return $id === $this->db->customerId ? 1 : 0; }
	public function create_from_member($member, $name, $alias)
	{
		$this->db->calls[] = 'customer-create';
		if ($this->db->mode === 'customer') { return -1; }
		$this->db->customerId = 5; return 5;
	}
}
class InvoiceServiceDouble extends MsmInvoiceService
{
	protected function invoice() { return new InvoiceObjectDouble($this->db); }
	protected function customer() { return new InvoiceCustomerDouble($this->db); }
}
class InvoiceBookingDouble extends BookingServiceDouble
{
	public $adapter;
	protected function invoicing() { return $this->adapter; }
}
function invoiceBookingFixture($create = false)
{
	list($unused, $old, $row) = bookingFixture();
	$db = new InvoiceDatabaseDouble(); $db->customerId = $create ? 0 : 4;
	$member = new BookingMemberDouble($db); $member->id = 1; $member->entity = 1; $member->typeid = 1; $member->socid = $db->customerId; $member->morphy = 'phy'; $member->company = ''; $member->firstname = 'Synthetic'; $member->lastname = 'Invoice';
	$row['invoice'] = array('customer_id' => $db->customerId, 'create_customer' => $create, 'entity' => 1, 'date' => '2026-11-10', 'term' => 1, 'payment_mode' => 0, 'bank' => 0, 'currency' => 'EUR', 'pdf_model' => 'crabe', 'product' => 0, 'vat' => 0, 'quantity' => 1, 'price_base' => 'TTC', 'type' => 1);
	$row['autocreate'] = $create;
	$service = new InvoiceBookingDouble($db); $service->object = $member; $service->reader = new BookingPreviewDouble(); $service->reader->row = $row; $service->adapter = new InvoiceServiceDouble($db);
	return array($db, $service, $row);
}
$conf->global = new stdClass(); $conf->modules = array('member' => 1, 'invoice' => 1, 'societe' => 1);
$user = new PreviewUser(); $opts = array('invoice' => true, 'autocreate' => false, 'sendmail' => false, 'description' => 'Synthetic fee');
foreach (array('', 'create', 'line', 'validate', 'link', 'amount', 'depth') as $mode) {
	list($db, $service, $row) = invoiceBookingFixture(); $db->mode = $mode;
	$r = $service->book(1, $row, $opts, $user, false);
	verify($r['status'] === ($mode === '' ? 'success' : ($mode === 'depth' ? 'unknown' : 'failed')), 'transactional invoice '.$mode);
	if ($mode === '') { verify($r['invoice']['id'] === 42 && $r['invoice']['subscription_id'] === 9 && $db->transaction_opened === 0, 'committed linked invoice identity'); }
	elseif ($mode !== 'depth') { verify($db->saved === null && $db->invoice === null && $r['rollback'] === 'confirmed', 'invoice failure rolls subscription back '.$mode); }
	else { verify($r['stop'] && $r['diagnostic']['invoice_id'] === 42 && $r['diagnostic']['subscription_id'] === 9, 'unknown retains unconfirmed identifiers'); }
}
foreach (array('success', 'customer', 'commit') as $mode) {
	list($db, $service, $row) = invoiceBookingFixture(true); $db->mode = $mode; $db->commitFail = $mode === 'commit'; $opts['autocreate'] = true;
	$r = $service->book(1, $row, $opts, $user, false);
	verify($r['status'] === ($mode === 'success' ? 'success' : ($mode === 'commit' ? 'unknown' : 'failed')), 'customer and commit '.$mode);
	if ($mode === 'commit') { verify($r['stop'] && $r['diagnostic']['customer_id'] === 5 && $r['diagnostic']['invoice_id'] === 42, 'unknown customer creation diagnostic'); }
}
list($db, $service, $row) = invoiceBookingFixture(); $row['amount'] = 0; $service->reader->row = $row; $opts['autocreate'] = false;
verify($service->book(1, $row, $opts, $user, false)['status'] === 'success', 'zero gross invoice allowed');
// Independent literal expectations: one HTML rendering must reproduce confirmed plain text.
$descriptions = array(
	array('Fee &amp;', 'Fee &amp;amp;'),
	array('5 &lt; 6', '5 &amp;lt; 6'),
	array('&#60;script&#62;', '&amp;#60;script&amp;#62;'),
	array('<b>Text</b>', '&lt;b&gt;Text&lt;/b&gt;'),
	array('Fee & < > "quoted" \'single\' Ä ü € {Unknown}', 'Fee &amp; &lt; &gt; &quot;quoted&quot; &#039;single&#039; Ä ü € {Unknown}')
);
foreach ($descriptions as $case) {
	list($db, $service, $row) = invoiceBookingFixture();
	$description = $case[0];
	$row['description'] = $description; $service->reader->row = $row; $opts['description'] = $description;
	verify($service->book(1, $row, $opts, $user, false)['status'] === 'success', 'invoice description special characters booked');
	verify($db->invoiceLine->description === $case[1], 'invoice description literal HTML persistence value');
	verify(htmlspecialchars_decode($db->invoiceLine->description, ENT_QUOTES) === $description, 'invoice description renders original confirmed plain text');
	verify($db->saved->note === $description, 'contribution description remains confirmed plain text');
}
list($db, $service, $row) = invoiceBookingFixture(); $db->nonTransactionalTable = 'ecm_files';
$blocked = $service->book(1, $row, $opts, $user, false);
verify($blocked['status'] === 'failed' && $blocked['message'] === 'MSMTransactionUnsupported' && !$db->calls && !in_array('subscription', $db->events, true), 'nontransactional ecm_files rejected before writes');
list($db, $service, $row) = invoiceBookingFixture(); $user->denied = array('facture.creer');
verify($service->book(1, $row, $opts, $user, false)['status'] === 'failed' && !$db->calls && !$db->events, 'invoice permission checked before writes');
$user->denied = array();
// C1-02: re-resolve the real tax/profile snapshot before any synthetic business API.
class TaxPreflightProfile extends MsmInvoiceProfile
{
	protected function paymentTerm() { return 1; }
	protected function paymentTermLabel($id) { return 'Synthetic term'; }
}
class TaxPreflightPreview extends PreviewFixtureService
{
	protected function invoiceProfile() { return new TaxPreflightProfile($this->db); }
}
$conf->global = new stdClass(); $mysoc = (object) array('country_id' => 1);
list($db, $service, $unusedRow) = invoiceBookingFixture(true);
$sourceMember = new PreviewMember();
$service->reader = new TaxPreflightPreview($db, $sourceMember);
$service->reader->type = (object) array('duration' => '1y', 'label' => 'Synthetic type', 'amount' => '12', 'status' => 1, 'subscription' => 1);
// Only the preview period reads are delegated to the existing in-memory reader.
$service->reader->db = new PreviewDatabase();
$opts = array('invoice' => true, 'autocreate' => true, 'sendmail' => false, 'description' => 'Synthetic fee');
$conf->global->ADHERENT_VAT_FOR_SUBSCRIPTIONS = '0';
$confirmed = $service->reader->previewSubscriptionWithOptions($user, stamp('2030-01-01'), true, false, null, true, $opts['description']);
verify(!$confirmed['errors'], 'VAT confirmed invoice profile');
foreach (array('7', '7foo', ' 7bad', '0foo', '-1', 'garbage') as $setting) {
	$conf->global->ADHERENT_VAT_FOR_SUBSCRIPTIONS = $setting;
	$fresh = $service->reader->previewSubscriptionWithOptions($user, $confirmed['start'], true, false, $confirmed['end'], true, $opts['description']);
	verify(in_array('MSMInvoiceVatIdUnsupported', $fresh['errors'], true) === (DOL_VERSION === '24.0.3'), 'PR6 real preflight rejects unsupported VAT '.DOL_VERSION.' '.$setting);
	$result = $service->book(1, $confirmed, $opts, $user, false);
	verify($result['status'] === 'skipped' && $result['message'] === 'MSMChangedConfirmation', 'VAT changed confirmation rejects booking '.DOL_VERSION.' '.$setting);
	verify(!in_array('subscription', $db->events, true) && !$db->calls && $result['rollback'] === 'confirmed', 'VAT change causes no business API call');
}
// Fresh subscription-only consent remains usable even with the same tax setting.
$opts['invoice'] = false; $opts['autocreate'] = false;
$confirmed = $service->reader->previewSubscriptionWithOptions($user, stamp('2030-01-01'), false, false, null, false, $opts['description']);
verify(!$confirmed['errors'] && !isset($confirmed['invoice']), 'VAT subscription-only confirmation valid');
$result = $service->book(1, $confirmed, $opts, $user, false);
verify($result['status'] === 'success' && !$db->calls, 'VAT ID does not block subscription-only booking');
echo ($passed - $beforeInvoice)." invoice transaction assertions passed (synthetic Core API doubles; no database).\n";
