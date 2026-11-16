<?php
/* Copyright (C) 2026 Kim Wittkowski <kim@wittkowski-it.de>
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
// Subprocess: check the pinned Core User class itself, without booting main/conf/DB.
if (($argv[1] ?? '') === '--core-user-contract') {
	$core = getenv('DOLIBARR_TEST_ROOT');
	if (!$core || !is_file($core.'/htdocs/user/class/user.class.php')) { fwrite(STDERR, "Set DOLIBARR_TEST_ROOT to a pinned Dolibarr source checkout.\n"); exit(1); }
	define('DOL_DOCUMENT_ROOT', $core.'/htdocs');
	define('MAIN_DB_PREFIX', 'test_');
	require DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
	require DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
	require __DIR__.'/../../class/corecompatibility.class.php';
	MsmCoreCompatibility::methods('User', array('fetch' => 1, 'loadRights' => 2, 'hasRight' => 3));
	$status = new ReflectionProperty('User', 'status');
	if (!$status->isPublic() || $status->isStatic()) { fwrite(STDERR, "User::\$status is not a public instance property.\n"); exit(1); }
	echo "ok\n";
	exit(0);
}
// Real pinned Core bootstrap and doubles from the preview harness; the Core User class is not loaded there.
require __DIR__.'/preview.php';
require_once __DIR__.'/../../class/booking.class.php';
require_once __DIR__.'/../../class/invoicepdf.class.php';
require_once __DIR__.'/../../class/mail.class.php';
$reloadBaseline = $passed;

/** Owned-transaction double: only the preflight, lock and rollback statements of book(). */
class ReloadDatabase extends PreviewDatabase
{
	public $type = 'mysqli'; public $transaction_opened = 0; public $events = array();
	public function escape($s) { return $s; }
	public function begin() { $this->events[] = 'begin'; $this->transaction_opened++; return 1; }
	public function commit() { $this->events[] = 'commit'; $this->transaction_opened--; return 1; }
	public function rollback() { $this->events[] = 'rollback'; $this->transaction_opened = 0; return 1; }
	public function query($sql)
	{
		$this->queries[] = $sql;
		if ($sql === 'SELECT @@in_transaction AS active') { $row = (object) array('active' => (int) ($this->transaction_opened > 0)); }
		elseif (strpos($sql, 'SELECT ENGINE AS engine') === 0) { $row = (object) array('engine' => 'InnoDB'); }
		elseif (strpos($sql, 'FOR UPDATE') !== false) { $this->events[] = 'lock'; $row = (object) array('rowid' => 1, 'fk_adherent_type' => 1); }
		else { throw new LogicException('Unexpected reload query '.$sql); }
		return (object) array('rows' => array($row));
	}
}
// Conditional, so declared at runtime: the contract subprocess above can load the Core class instead.
if (!class_exists('User', false)) {
	/** Minimal double of the Core User API used by the reload helpers. Deliberately no getrights() and no $statut. */
	class User
	{
		public static $records = array(); public static $calls = array();
		public $id = 0; public $socid = 0; public $status;
		private $denied = array(); private $loaded = false;
		public function __construct($db) {}
		public function fetch($id)
		{
			self::$calls[] = 'fetch:'.$id;
			$record = self::$records[$id] ?? 0;
			if (!is_array($record)) { return $record; }
			$this->id = $id; $this->status = $record['status']; $this->denied = $record['denied'];
			return 1;
		}
		public function loadRights($moduletag = '', $forcereload = 0)
		{
			self::$calls[] = 'loadRights:'.$moduletag.':'.$forcereload;
			$this->loaded = true;
		}
		public function hasRight(...$parts) { return $this->loaded && !in_array(implode('.', $parts), $this->denied, true); }
	}
} else {
	throw new RuntimeException('The synthetic harness must not load the Core User class');
}
User::$records = array(
	1 => array('status' => 1, 'denied' => array()),
	2 => array('status' => 0, 'denied' => array()),
	3 => 0,
	4 => -1,
	5 => array('status' => 1, 'denied' => array('adherent.cotisation.creer')),
	6 => array('status' => 1, 'denied' => array('facture.creer')),
);
class ReloadBookingFlow extends MsmBookingService
{
	public $admitted = false;
	public function reload($user) { return $this->currentUser($user); }
	// Reached only after the reloaded permission check; stop here with a marker, before any write.
	protected function member($id) { $this->admitted = true; throw new RuntimeException('MSMReloadAdmitted'); }
}
class ReloadPdfFlow extends MsmInvoicePdfService
{
	public $loaded = false;
	public function reload($user) { return $this->currentUser($user); }
	// Reached only after the permission check; a marker proves the reloaded user was admitted.
	protected function load(array $identity, $user) { $this->loaded = true; throw new RuntimeException('MSMPdfIdentityMismatch'); }
}
class ReloadMailFlow extends MsmSubscriptionMail
{
	public function check($user, $invoice) { return $invoice ? $this->invoicePermitted($user) : $this->permitted($user); }
}
/** Stale caller with every right; the helpers must never trust it. */
function reloadCaller($id) { $caller = new PreviewUser(); $caller->id = $id; return $caller; }
function reloadCalls() { $calls = User::$calls; User::$calls = array(); return $calls; }

$conf->modules['invoice'] = 1; $conf->modules['societe'] = 1; $conf->modules['member'] = 1; $conf->global = new stdClass();
$db = new ReloadDatabase();
$booking = new ReloadBookingFlow($db); $pdf = new ReloadPdfFlow($db); $mail = new ReloadMailFlow($db);

// Reload: fetch, refuse inactive/missing/failed users, then force a fresh rights load.
foreach (array('booking' => $booking, 'pdf' => $pdf) as $label => $service) {
	reloadCalls();
	$current = $service->reload(reloadCaller(1));
	verify($current instanceof User && $current->id === 1, $label.' returns the reloaded user, not the caller');
	verify(reloadCalls() === array('fetch:1', 'loadRights::1'), $label.' forces a full rights reload');
	foreach (array(2 => 'inactive', 3 => 'missing', 4 => 'failed fetch') as $id => $case) {
		fails(static function () use ($service, $id) { $service->reload(reloadCaller($id)); }, 'NotEnoughPermissions');
		verify(reloadCalls() === array('fetch:'.$id), $label.' '.$case.' user refused before loading rights');
	}
}
foreach (array(false, true) as $invoice) {
	$suffix = $invoice ? ' (invoice mail)' : ' (mail)';
	verify($mail->check(reloadCaller(1), $invoice) === true, 'active user permitted'.$suffix);
	verify(reloadCalls() === array('fetch:1', 'loadRights::1'), 'forced rights reload'.$suffix);
	foreach (array(2 => 'inactive', 3 => 'missing', 4 => 'failed fetch', 5 => 'denied right') as $id => $case) {
		verify($mail->check(reloadCaller($id), $invoice) === false, $case.' user refused'.$suffix);
		reloadCalls();
	}
}
verify($mail->check(reloadCaller(6), false) === true && $mail->check(reloadCaller(6), true) === false, 'invoice right only required for invoice mail');
reloadCalls();

// Booking: the reloaded rights decide, inside the owned transaction and before any write.
$options = array('invoice' => false, 'autocreate' => false, 'sendmail' => false, 'description' => 'Synthetic fee');
$row = array('start' => stamp('2026-10-10'), 'end' => stamp('2027-10-09'), 'amount' => 24.5, 'description' => 'Synthetic fee', 'fingerprint' => 'same', 'errors' => array(), 'overlaps' => array());
foreach (array(1 => 'active', 5 => 'denied right', 2 => 'inactive', 4 => 'failed fetch') as $id => $case) {
	$db = new ReloadDatabase(); $booking = new ReloadBookingFlow($db);
	$result = $booking->book(1, $row, $options, reloadCaller($id), false);
	verify($result['status'] === 'failed' && $result['rollback'] === 'confirmed' && $db->events === array('begin', 'lock', 'lock', 'rollback'), 'owned rollback, no write for '.$case.' user');
	verify($booking->admitted === ($id === 1) && ($id === 1 || $result['message'] === 'NotEnoughPermissions'), 'booking admission follows the reloaded '.$case.' user');
}
reloadCalls();

// PDF: generation and attachment re-check the reloaded user before touching the invoice.
$identity = array('id' => 42, 'entity' => 1, 'customer_id' => 4, 'ref' => 'INV-SYNTHETIC', 'subscription_id' => 7, 'pdf_model' => 'crabe');
verify($pdf->generate($identity, reloadCaller(1))['message'] === 'MSMPdfIdentityMismatch' && $pdf->loaded, 'active reloaded user reaches the PDF identity check');
foreach (array(6 => 'denied right', 2 => 'inactive', 4 => 'failed fetch') as $id => $case) {
	$pdf->loaded = false;
	$outcome = $pdf->generate($identity, reloadCaller($id));
	verify($outcome === array('status' => 'failed', 'message' => 'NotEnoughPermissions') && !$pdf->loaded, 'PDF generation refused for '.$case.' user');
}
fails(static function () use ($pdf, $identity) { $pdf->attachment($identity, array('status' => 'verified'), reloadCaller(6)); }, 'NotEnoughPermissions');
reloadCalls();

// Mail: send() reports a denied reloaded user before hooks, attachments or transport.
$member = (object) array('id' => 1, 'entity' => 1, 'typeid' => 1, 'email' => 'synthetic@example.invalid');
verify($mail->send($member, $row, reloadCaller(5), true) === 'denied', 'mail denied for reloaded user without subscription right');
verify($mail->send($member, $row, reloadCaller(6), true, array('pdf' => array('status' => 'verified'))) === 'denied', 'invoice mail denied for reloaded user without invoice right');
verify($mail->send($member, $row, reloadCaller(2), true) === 'denied', 'mail denied for inactive reloaded user');
reloadCalls();

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
	$lines = array();
	exec(escapeshellarg(PHP_BINARY).' '.escapeshellarg(__FILE__).' --core-user-contract 2>&1', $lines, $exit);
	verify($exit === 0 && $lines === array('ok'), 'pinned Core User exposes fetch(), loadRights(), hasRight() and public $status '.implode("\n", $lines));
}
echo ($passed - $reloadBaseline)." user reload assertions passed (synthetic user double; pinned Core User contract in a subprocess).\n";
