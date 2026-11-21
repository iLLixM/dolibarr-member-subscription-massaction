<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
require __DIR__.'/booking.php';
require __DIR__.'/../../class/invoice.class.php';
$beforeInvoice = $passed;
class InvoiceDatabaseDouble extends BookingDatabase
{
	public $readOrder = array();
	public $invoice = null; public $invoiceLine = null; public $link = false; public $customerId = 4; public $mode = ''; public $calls = array(); public $nonTransactionalTable = '';
	public function query($sql)
	{
		if ($this->active && (strpos($sql, 'FOR UPDATE') !== false || strpos($sql, 'SELECT ENGINE AS engine') === 0)) { $this->readOrder[] = $sql; }
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
		if ($this->db->mode === 'vat') { $this->db->invoiceLine->vat++; }
		if ($this->db->mode === 'price_hook') { $this->db->invoiceLine->total++; }
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
	protected function requireInvoiceAdmission() {} // Synthetic tests use admitted Enhanced/MariaDB fixture.
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
	$service = new InvoiceBookingDouble($db); $service->object = $member; $service->reader = new BookingPreviewDouble(); $service->reader->row = $row; $service->adapter = new InvoiceServiceDouble($db, $service->observer(), $service->observer());
	return array($db, $service, $row);
}
// FIX01: no consistent metadata read before customer/product lock acquisition.
$conf->global = new stdClass(); $conf->global->ADHERENT_PRODUCT_ID_FOR_SUBSCRIPTIONS = 9;
list($db, $service, $row) = invoiceBookingFixture(); $db->family = 'mysql';
$invoiceOptions = array('invoice' => true, 'autocreate' => false, 'sendmail' => false, 'description' => 'Synthetic fee');
$r = $service->book(1, $row, $invoiceOptions, new PreviewUser(), false);
$locks = array_filter(array_keys($db->readOrder), static function ($i) use ($db) { return strpos($db->readOrder[$i], 'FOR UPDATE') !== false; });
$engines = array_filter(array_keys($db->readOrder), static function ($i) use ($db) { return strpos($db->readOrder[$i], 'SELECT ENGINE AS engine') === 0; });
verify($r['status'] === 'success' && max($locks) < min($engines), 'FIX01 all invoice locks precede first consistent metadata read');
verify(count(array_filter($db->readOrder, static function ($sql) { return strpos($sql, 'product WHERE rowid = 9 FOR UPDATE') !== false; })) === 1, 'FIX01 product lock retained');

$conf->global = new stdClass(); $conf->modules = array('member' => 1, 'invoice' => 1, 'societe' => 1);
$user = new PreviewUser(); $opts = array('invoice' => true, 'autocreate' => false, 'sendmail' => false, 'description' => 'Synthetic fee');
foreach (array('', 'create', 'line', 'validate', 'link', 'amount', 'vat', 'price_hook', 'depth') as $mode) {
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
	verify(in_array('MSMInvoiceVatIdUnsupported', $fresh['errors'], true) === true, 'PR6 real preflight rejects unsupported VAT '.DOL_VERSION.' '.$setting);
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
// The same context must survive every factory call and every nested invoice API.
$opts = array('invoice' => true, 'autocreate' => true, 'sendmail' => false, 'description' => 'Synthetic fee');
foreach (array(false,true) as $create) {
	foreach (array(0,24.5) as $amount) {
		list($db, $service, $row) = invoiceBookingFixture($create); $db->family = 'mysql'; $row['amount'] = $amount; $service->reader->row = $row;
		$r = $service->book(1, $row, $opts, new PreviewUser(), false);
		verify($r['status'] === 'success' && $r['invoice']['customer_created'] === $create && $r['invoice']['subscription_id'] === 9, 'MySQL shared invoice/customer/zero context '.(int) $create.'/'.$amount);
		verify($db->invoice->fk_statut === 1 && $db->link && $db->invoice->total_ttc === $amount && $db->transaction_opened === 0, 'MySQL confirmed linked validated invoice');
	}
}
foreach (array('customer','create','line','validate','link','amount','vat','price_hook','depth') as $mode) {
	list($db, $service, $row) = invoiceBookingFixture(true); $db->family = 'mysql'; $db->mode = $mode;
	$r = $service->book(1, $row, $opts, new PreviewUser(), false);
	verify($r['status'] === ($mode === 'depth' ? 'unknown' : 'failed'), 'MySQL invoice negative '.$mode);
	if ($mode !== 'depth') { verify($r['rollback'] === 'confirmed' && $db->invoice === null && $db->saved === null, 'MySQL invoice and contribution rollback '.$mode); }
}
// A replacement still ACTIVE event after each Core write cannot permit the NEXT API.
class ReplacingInvoice extends InvoiceObjectDouble
{
	private $database; public $replaceAt;
	public function __construct($db, $stage) { parent::__construct($db); $this->database = $db; $this->replaceAt = $stage; }
	private function replace($stage) { if ($this->replaceAt === $stage) { $this->database->modelEvent('ACTIVE'); } }
	public function create($user) { $r = parent::create($user); $this->replace('create'); return $r; }
	public function addline(...$args) { $r = parent::addline(...$args); $this->replace('line'); return $r; }
	public function validate($user) { $r = parent::validate($user); $this->replace('validate'); return $r; }
}
class ReplacingCustomer extends InvoiceCustomerDouble
{
	private $database;
	public function __construct($db) { parent::__construct($db); $this->database = $db; }
	public function create_from_member($member, $name, $alias) { $r = parent::create_from_member($member,$name,$alias); $this->database->modelEvent('ACTIVE'); return $r; }
}
class FaultInvoiceService extends InvoiceServiceDouble
{
	public $replaceAt;
	protected function invoice() { return new ReplacingInvoice($this->db, $this->replaceAt); }
	protected function customer() { return $this->replaceAt === 'customer' ? new ReplacingCustomer($this->db) : parent::customer(); }
}
foreach (array('customer','create','line','validate') as $stage) {
	list($db, $service, $row) = invoiceBookingFixture(true); $db->family = 'mysql';
	$service->adapter = new FaultInvoiceService($db, $service->observer(), $service->observer()); $service->adapter->replaceAt = $stage;
	$r = $service->book(1, $row, $opts, new PreviewUser(), false);
	verify($r['status'] === 'unknown' && $r['stop'] && !in_array('rollback', $db->events, true), 'invoice API event replacement unknown/stop '.$stage);
	$expectedCalls = array('customer-create','invoice-create','invoice-line','invoice-validate');
	verify($db->calls === array_slice($expectedCalls,0,array_search($stage,array('customer','create','line','validate'),true)+1), 'no API after lost invoice context '.$stage);
}
// Exercise the actual production factory repeatedly, not just the static invoice double.
class FactoryBookingFixture extends BookingServiceDouble { public function invoiceFactory() { return parent::invoicing(); } }
$db = new BookingDatabase(); $db->family = 'mysql'; $service = new FactoryBookingFixture($db);
verify($service->observer()->beforeBegin()['state'] === 'IDLE', 'factory idle setup'); $db->begin();
verify($service->observer()->afterBegin()['state'] === 'ACTIVE', 'factory owned setup');
$ownedCheck = new ReflectionMethod(MsmInvoiceService::class, 'owned'); $ownedCheck->setAccessible(true);
$token = $db->event->EVENT_ID;
foreach (array($service->invoiceFactory(), $service->invoiceFactory()) as $freshFactory) { $ownedCheck->invoke($freshFactory); }
verify($db->event->EVENT_ID === $token && !$db->markers, 'all production invoice factories share unchanged owned context');
$db->rollback(); verify($service->observer()->completed('ROLLBACK', true)['state'] === 'IDLE', 'factory own rollback setup complete');
$otherDb = new BookingDatabase(); $otherState = new SyntheticDatabaseState($otherDb);
fails(static function () use ($db,$otherState) { new MsmBookingService($db, $otherState); }, 'MSMDatabaseUnsupported');
fails(static function () use ($db,$otherState) { new MsmInvoiceService($db, $otherState, $otherState); }, 'MSMDatabaseUnsupported');
fails(static function () use ($db,$otherState) { new MsmInvoiceService($db, new SyntheticDatabaseState($db), $otherState); }, 'MSMDatabaseUnsupported');
// CF05-01/02: metadata observation and transaction ownership are different
// objects for CoreGuarded. The real Booking factory must reuse its current
// exact guard, and direct Invoice construction may never substitute another DB.
$activeProperty = new ReflectionProperty(MsmBookingService::class, 'activeTransaction');
$activeProperty->setAccessible(true);
$invoiceGuardProperty = new ReflectionProperty(MsmInvoiceService::class, 'activeGuard');
$invoiceGuardProperty->setAccessible(true);
$invoiceMetaProperty = new ReflectionProperty(MsmInvoiceService::class, 'databaseState');
$invoiceMetaProperty->setAccessible(true);
$db = new BookingDatabase(); $db->family = 'mysql';
$service = new FactoryBookingFixture($db); $metadata = $service->observer();
$guardForCore = new class($db) extends MsmCoreGuardedTransaction {
	private $fixtureDb;
	public $probeCount = 0;
	public $supportedCount = 0;
	public function __construct($db) { $this->fixtureDb = $db; parent::__construct($db); }
	public function active() {
		$this->probeCount++;
		return array('state'=>MsmDatabaseState::ACTIVE,'fresh'=>true,'owned'=>true);
	}
	public function requireSupportedIsolation(array $state) { $this->supportedCount++; }
};
$activeProperty->setValue($service, $guardForCore);
$factories = array($service->invoiceFactory(), $service->invoiceFactory());
foreach ($factories as $invoiceService) {
	verify($invoiceGuardProperty->getValue($invoiceService) === $guardForCore,
		'CF05 factory passes identical active CoreGuarded guard');
	verify($invoiceMetaProperty->getValue($invoiceService) === $metadata,
		'CF05 invoice metadata remains separate original database observer');
	$ownedCheck->invoke($invoiceService);
}
verify($guardForCore->probeCount === 2 && $guardForCore->supportedCount === 2,
	'CF05 CoreGuarded invoice ownership uses only injected guard');
verify(!array_filter($db->queries, static function($q){return stripos($q, 'performance_schema') !== false;}),
	'CF05 CoreGuarded invoice checkpoints never use Performance Schema');
$otherDb = new BookingDatabase();
fails(static function() use($db,$metadata,$otherDb) {
	new MsmInvoiceService($db, $metadata, new MsmCoreGuardedTransaction($otherDb));
}, 'MSMDatabaseUnsupported');

// Strict synthetic checkpoints: the same injected guard is queried before
// and after EACH Core operation. Losing it at any one boundary must prevent
// the next business operation and cannot silently fall back to metadata.
class Cf05BoundaryGuard implements MsmActiveTransactionGuard
{
	private $db;
	public $blockedAfter = '';
	public $probes = 0;
	public $isolationChecks = 0;
	public function __construct($db) { $this->db = $db; }
	public function isFor($db) { return $this->db === $db; }
	public function active() {
		$this->probes++;
		if ($this->blockedAfter !== '' && in_array($this->blockedAfter, $this->db->calls, true)) {
			return array('state'=>MsmDatabaseState::UNKNOWN,'fresh'=>false,'owned'=>false);
		}
		return array('state'=>MsmDatabaseState::ACTIVE,'fresh'=>true,'owned'=>true);
	}
	public function requireSupportedIsolation(array $state) { $this->isolationChecks++; }
}
foreach (array('customer-create','invoice-create','invoice-line','invoice-validate') as $stage) {
	list($db,$booking,$row)=invoiceBookingFixture(true);
	$spy = new Cf05BoundaryGuard($db); $spy->blockedAfter = $stage;
	$service = new InvoiceServiceDouble($db, $booking->observer(), $spy);
	$member = $booking->object;
	$diagnostic = array('stage'=>'preflight','subscription_id'=>9,'invoice_id'=>0,'customer_id'=>0);
	fails(static function() use($service,$member,$row,&$diagnostic) {
		$service->create($member,9,$row,new PreviewUser(),$diagnostic);
	},'MSMTransactionUnknown');
	$expectedCalls = array('customer-create','invoice-create','invoice-line','invoice-validate');
	verify($db->calls === array_slice($expectedCalls,0,array_search($stage,$expectedCalls,true)+1),
		'CF05 no subsequent Core API after guard loss '.$stage);
	verify($spy->probes > 1 && $spy->isolationChecks === $spy->probes-1,
		'CF05 direct-service guard checked at each boundary '.$stage);
}
list($db,$booking,$row)=invoiceBookingFixture(true);
$spy = new Cf05BoundaryGuard($db);
$service = new InvoiceServiceDouble($db, $booking->observer(), $spy);
$member = $booking->object;
$diagnostic = array('stage'=>'preflight','subscription_id'=>9,'invoice_id'=>0,'customer_id'=>0);
$created = $service->create($member,9,$row,new PreviewUser(),$diagnostic);
verify($created['id'] === 42 && $spy->probes >= 10 && $spy->probes === $spy->isolationChecks,
	'CF05 complete customer/invoice chain uses shared guard before and after Core calls');
// Early read-only table readiness is invoice-selective; final lock checks remain.
foreach (array('ecm_files', 'product') as $unsupportedTable) {
	list($db, $service, $row) = invoiceBookingFixture(); $db->family = 'mysql'; $db->nonTransactionalTable = $unsupportedTable;
	$oldProduct = $conf->global->ADHERENT_PRODUCT_ID_FOR_SUBSCRIPTIONS ?? null;
	$conf->global->ADHERENT_PRODUCT_ID_FOR_SUBSCRIPTIONS = $unsupportedTable === 'product' ? 7 : 0;
	$service->checkTables(false);
	verify(!$db->events && !$db->calls, 'contribution-only readiness ignores invoice/product engine '.$unsupportedTable);
	fails(static function () use ($service) { $service->checkTables(true); }, 'MSMTransactionUnsupported');
	verify(!$db->events && !$db->calls, 'invoice readiness fails before BEGIN/business writes '.$unsupportedTable);
	if ($oldProduct === null) { unset($conf->global->ADHERENT_PRODUCT_ID_FOR_SUBSCRIPTIONS); }
	else { $conf->global->ADHERENT_PRODUCT_ID_FOR_SUBSCRIPTIONS = $oldProduct; }
}
// I107-F01: only possible selected invoice/customer/contact events need Agenda.
$invoiceAgendaGlobals = $conf->global; $invoiceAgendaModules = $conf->modules;
foreach (array('BILL_CREATE','LINEBILL_INSERT','BILL_VALIDATE','COMPANY_CREATE','CONTACT_CREATE','COMPANY_MODIFY') as $event) {
	foreach (array(false,true) as $create) {
		foreach (array(false,true) as $badEngine) {
			$conf->modules = array('member'=>1,'invoice'=>1,'societe'=>1,'agenda'=>1);
			$conf->global = (object) array('THIRDPARTY_DEFAULT_CREATE_CONTACT'=>1);
			$conf->global->{'MAIN_AGENDA_ACTIONAUTO_'.$event} = 1;
			list($db, $service, $row) = invoiceBookingFixture($create); $db->family = 'mysql';
			if ($badEngine) { $db->nonTransactionalTable = 'actioncomm_resources'; }
			$selected = array('invoice'=>true,'autocreate'=>$create,'sendmail'=>false,'description'=>'Synthetic fee');
			$r = $service->book(1, $row, $selected, new PreviewUser(), false);
			$reachable = in_array($event,array('BILL_CREATE','LINEBILL_INSERT','BILL_VALIDATE'),true) || ($create && in_array($event,array('COMPANY_CREATE','CONTACT_CREATE'),true));
			$blocked = $badEngine && $reachable;
			verify($r['status'] === ($blocked ? 'failed' : 'success'), 'I107 invoice Agenda reachability '.$event.'/'.(int)$create.'/'.(int)$badEngine);
			$queries = preg_grep("/TABLE_NAME = 'test_actioncomm(?:_resources)?'/", $db->queries);
			verify((bool)$queries === $reachable, 'I107 no unreachable invoice Agenda dependency '.$event.'/'.(int)$create);
			if ($blocked) { verify(!$db->calls && !in_array('subscription',$db->events,true), 'I107 invoice Agenda nontransactional gate before all business APIs '.$event); }
			elseif ($reachable) {
				$locks = array_keys(array_filter($db->readOrder,static function($sql){return strpos($sql,'FOR UPDATE')!==false;}));
				$engines = array_keys(array_filter($db->readOrder,static function($sql){return strpos($sql,'SELECT ENGINE AS engine')===0;}));
				verify(max($locks)<min($engines), 'I107 Agenda metadata remains after all invoice locks '.$event);
			}
		}
	}
}
$conf->modules = array('member'=>1,'invoice'=>1,'societe'=>1,'agenda'=>1);
$conf->global = (object) array('MAIN_AGENDA_ACTIONAUTO_CONTACT_CREATE'=>1);
list($db,$service,$row)=invoiceBookingFixture(true); $db->nonTransactionalTable='actioncomm';
$selected=array('invoice'=>true,'autocreate'=>true,'sendmail'=>false,'description'=>'Synthetic fee');
verify($service->book(1,$row,$selected,new PreviewUser(),false)['status']==='success', 'I107 disabled automatic contact creation has no CONTACT_CREATE dependency');
foreach (array('REPEATABLE READ','READ COMMITTED','SERIALIZABLE','READ UNCOMMITTED') as $level) {
	$conf->global=(object)array('MAIN_AGENDA_ACTIONAUTO_BILL_CREATE'=>1);
	list($db,$service,$row)=invoiceBookingFixture(true);$db->family='mysql';$db->nextIsolation=$level;
	$r=$service->book(1,$row,$selected,new PreviewUser(),false);
	verify($r['status']===($level==='REPEATABLE READ'?'success':'failed'), 'I107 invoice isolation and Agenda combined '.$level);
	if($level!=='REPEATABLE READ'){verify(!$db->calls&&!in_array('subscription',$db->events,true)&&$r['rollback']==='confirmed','I107 invoice isolation rejects before customer/subscription/invoice '.$level);}
}
// Direct nested Invoice guard uses the same event policy and retains safe teardown.
list($db,$service,$row)=invoiceBookingFixture();$db->family='mysql';$state=$service->observer();$state->beforeBegin();$db->nextIsolation='READ COMMITTED';$db->begin();$state->afterBegin();
$guard=new ReflectionMethod(MsmInvoiceService::class,'owned');$guard->setAccessible(true);
fails(static function()use($guard,$service){$guard->invoke($service->adapter);},'MSMTransactionUnsupported');
verify($state->rollbackContext()['owned']&&!$db->calls,'I107 Invoice policy rejection preserves own rollback eligibility');$db->rollback();verify($state->completed('ROLLBACK',true)['state']==='IDLE','I107 Invoice policy own rollback confirmed');
$conf->global=$invoiceAgendaGlobals;$conf->modules=$invoiceAgendaModules;

echo ($passed - $beforeInvoice)." invoice transaction assertions passed (synthetic Core API doubles; no database).\n";
