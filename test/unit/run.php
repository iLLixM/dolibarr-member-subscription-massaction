<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

if (PHP_SAPI !== 'cli') {
	http_response_code(404);
	exit;
}

// Standalone synthetic contract tests: no Dolibarr bootstrap or database.
error_reporting(E_ALL & ~E_DEPRECATED);
set_error_handler(static function ($severity, $message, $file, $line) {
	if (!(error_reporting() & $severity)) { return false; }
	throw new ErrorException($message, 0, $severity, $file, $line);
});
define('DOL_DOCUMENT_ROOT', __DIR__.'/../fixtures');
define('DOL_URL_ROOT', '/erp');
require_once __DIR__.'/../../lib/membersubscriptionmassaction.lib.php';
require_once __DIR__.'/../../class/actions_membersubscriptionmassaction.class.php';
require_once __DIR__.'/../../core/modules/modMemberSubscriptionMassaction.class.php';

class TestUser
{
	public $id = 11;
	public $socid = 0;
	public $read = true;
	public $create = true;
	public function hasRight($module, $first, $second = '') { return $first === 'lire' ? $this->read : $this->create; }
}
class TestLanguages
{
	public function load($file) {}
	public function trans($key) { return $key; }
}
function isModEnabled($name) { return !in_array($name, $GLOBALS['disabled'], true); }
function dol_escape_htmltag($text) { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }
function getEntity($name) { return $GLOBALS['entities']; }
function restrictedArea($user, $feature, $id, $table, $feature2, $key, $select, $draft, $mode)
{
	if ($mode !== 1) { throw new RuntimeException('Access check must not terminate the page'); }
	return $id === $GLOBALS['denied'] ? 0 : 1;
}
function GETPOST($key, $type, $method = 0) { return $_POST[$key] ?? ''; }
function getDolGlobalInt($key, $default = 0) { return $GLOBALS['limit']; }
function accessforbidden() { throw new RuntimeException('Forbidden'); }
function dol_now() { return 1000; }
function dol_buildpath($path, $url = 0) { return '/erp/custom'.$path; }
$disabled = array(); $entities = '1'; $denied = 0; $limit = 1000;
$user = new TestUser(); $langs = new TestLanguages(); $conf = (object) array('entity' => 1);
$object = null; $action = '';
$hook = new ActionsMemberSubscriptionMassaction(null);

// Exercise the successful hook path in its own process because it must exit after redirect.
if (($argv[1] ?? '') === 'entry') {
	$_SESSION = array('token' => 'trusted-csrf');
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST = array('token' => 'trusted-csrf', 'confirmmassaction' => 'Confirm', 'toselect' => array('2', '1'));
	register_shutdown_function(static function () { echo json_encode($_SESSION['membersubscriptionmassaction'] ?? null); });
	$hook->doMassActions(array('currentcontext' => 'memberlist', 'massaction' => ActionsMemberSubscriptionMassaction::ACTION, 'toselect' => array(2, 1)), $object, $action);
	exit(2);
}
$passed = 0;
function check($condition, $label)
{
	global $passed;
	if (!$condition) { throw new RuntimeException('FAIL: '.$label); }
	$passed++;
}
function rejects(callable $operation, $label)
{
	try { $operation(); } catch (InvalidArgumentException $e) { check(true, $label); return; }
	catch (RuntimeException $e) { check(true, $label); return; }
	check(false, $label);
}
check(msmSelectionIds(array('2', '1'), 2) === array(2, 1), 'selection order');
foreach (array(null, '', '1', array(), array(0), array(-1), array('x'), array('1x'), array('01'), array('1.0'), array('1e2'), array('+1'), array(' 1'), array('1 '), array(true), array(1.0), array(array(1)), array((string) PHP_INT_MAX.'0'), array(1, '1')) as $invalid) {
	rejects(static function () use ($invalid) { msmSelectionIds($invalid, 5); }, 'invalid ID shape');
}
rejects(static function () { msmSelectionIds(array(1, 2), 1); }, 'limit');
rejects(static function () { msmSelectionIds(array(1), 0); }, 'disabled limit');
msmCheckMembers(null, $user, array(1, 2)); check(true, 'accessible objects');
rejects(static function () use ($user) { msmCheckMembers(null, $user, array(999)); }, 'missing member');
rejects(static function () use ($user) { msmCheckMembers(null, $user, array(3)); }, 'foreign entity');
$denied = 2;
rejects(static function () use ($user) { msmCheckMembers(null, $user, array(2)); }, 'restricted object');
$denied = 0; $entities = '1,2';
msmCheckMembers(null, $user, array(3)); check(true, 'shared entity through getEntity'); $entities = '1';
check(msmCanUse($user), 'core permissions');
$user->read = false; check(!msmCanUse($user), 'read denied'); $user->read = true;
$user->create = false; check(!msmCanUse($user), 'creation denied'); $user->create = true;
$user->socid = 3; check(!msmCanUse($user), 'external denied'); $user->socid = 0;
foreach (array('member', 'membersubscriptionmassaction') as $module) { $disabled = array($module); check(!msmCanUse($user), 'disabled module'); }
$disabled = array();
$session = array('unrelated' => array('keep' => true));
$token = msmCreateRun($session, array(2, 1), 11, 1, 1000);
check((bool) preg_match('/^[a-f0-9]{32}$/D', $token), 'opaque token');
check(msmReadRun($session, $token, 11, 1, 1001)['ids'] === array(2, 1), 'run selection');
rejects(static function () use ($token) { msmReadRun(array('membersubscriptionmassaction' => 'invalid'), $token, 11, 1, 1001); }, 'malformed session namespace');
$before = $session; msmReadRun($session, $token, 11, 1, 1001); check($session === $before, 'GET read is non-consuming');
foreach (array(null, '', array($token), 'x'.$token, strtoupper($token), str_repeat('f', 32)) as $bad) {
	rejects(static function () use ($session, $bad) { msmReadRun($session, $bad, 11, 1, 1001); }, 'invalid run ID');
}
rejects(static function () use ($session, $token) { msmReadRun($session, $token, 12, 1, 1001); }, 'wrong owner');
rejects(static function () use ($session, $token) { msmReadRun($session, $token, 11, 2, 1001); }, 'wrong entity');
rejects(static function () use ($session, $token) { msmReadRun($session, $token, 11, 1, 1900); }, 'expiry boundary');
for ($i = 0; $i < 7; $i++) { msmCreateRun($session, array(1), 11, 1, 1001); }
check(count($session['membersubscriptionmassaction']) === 5, 'bounded runs');
check($session['unrelated'] === array('keep' => true), 'other namespaces preserved');
$token = msmCreateRun($session, array(1), 11, 1, 1001);
$before = $session;
rejects(static function () use (&$session, $token) { msmCancelRun($session, $token, 12, 1, 1002); }, 'foreign cancel');
check($session === $before, 'foreign cancel non-mutating');
msmCancelRun($session, $token, 11, 1, 1002);
rejects(static function () use ($session, $token) { msmReadRun($session, $token, 11, 1, 1002); }, 'cancel removes only own run');
check(msmValidCsrf('secret', array('token' => 'secret')), 'CSRF valid');
foreach (array(null, '', array('secret'), 'wrong') as $bad) { check(!msmValidCsrf($bad, array('token' => 'secret')), 'CSRF denied'); }
$_GET['backtopage'] = 'https://example.org/'; $_POST['url'] = '//example.org/';
check(msmListUrl() === '/erp/adherents/list.php', 'redirect independent of user input');
check($hook->addMoreMassActions(array('currentcontext' => 'memberlist'), $object, $action) === 0, 'append hook return');
check(strpos($hook->resprints, ActionsMemberSubscriptionMassaction::ACTION) !== false, 'own option');
$hook->addMoreMassActions(array('currentcontext' => 'invoicecard'), $object, $action);
check($hook->resprints === '', 'no other context');
$disabled = array('membersubscriptionmassaction');
$hook->addMoreMassActions(array('currentcontext' => 'memberlist'), $object, $action);
check($hook->resprints === '', 'disabled hides option'); $disabled = array();
check($hook->doMassActions(array('currentcontext' => 'memberlist', 'massaction' => 'createsubscription'), $object, $action) === 0, 'core action untouched');
$_SESSION = array('token' => 'secret'); $_SERVER['REQUEST_METHOD'] = 'POST';
$params = array('currentcontext' => 'memberlist', 'massaction' => ActionsMemberSubscriptionMassaction::ACTION, 'toselect' => array(1));
foreach (array(array(), array('confirmmassaction' => 'Confirm', 'token' => 'wrong')) as $post) {
	$_POST = $post;
	rejects(static function () use ($hook, $params, &$object, &$action) { $hook->doMassActions($params, $object, $action); }, 'entry rejects missing confirm/CSRF');
}
$_POST = array('confirmmassaction' => 'Confirm', 'token' => 'secret', 'toselect' => array('1x'));
check($hook->doMassActions($params, $object, $action) === -1, 'raw coercion attack rejected');
check(!isset($_SESSION['membersubscriptionmassaction']), 'invalid selection stores no run');
$_POST['toselect'] = array('2');
check($hook->doMassActions($params, $object, $action) === -1, 'hook/request selection mismatch');
$descriptor = new modMemberSubscriptionMassaction(null);
check($descriptor->name === 'MemberSubscriptionMassaction', 'descriptor name');
check($descriptor->numero === 584620, 'temporary development ID');
check($descriptor->module_parts['hooks'] === array('memberlist'), 'narrow hook registration');
check($descriptor->need_dolibarr_version === array(22, 0, 5), 'primary version');
check($descriptor->depends === array('modAdherent'), 'member dependency');
check($descriptor->rights_class === 'membersubscriptionmassaction', 'rights namespace');
check($descriptor->init() === 1 && $descriptor->remove() === 1, 'no custom lifecycle SQL');
$cmd = escapeshellarg(PHP_BINARY).' '.escapeshellarg(__FILE__).' entry';
exec($cmd, $output, $exit);
check($exit === 0, 'successful hook exits');
$runs = json_decode(implode("\n", $output), true);
check(is_array($runs) && count($runs) === 1 && reset($runs)['ids'] === array(2, 1), 'successful hook preserves both selected IDs');
foreach (array('core/modules/modMemberSubscriptionMassaction.class.php', 'class/actions_membersubscriptionmassaction.class.php', 'lib/membersubscriptionmassaction.lib.php', 'subscription.php', 'COPYING', 'README.md', 'CONTRIBUTING.md', 'CHANGELOG.md', 'composer.json', '.github/workflows/ci.yml') as $file) {
	check(is_file(__DIR__.'/../../'.$file), 'structure '.$file);
}
$translations = array();
foreach (array('en_US', 'de_DE') as $locale) {
	$keys = array();
	foreach (file(__DIR__.'/../../langs/'.$locale.'/membersubscriptionmassaction.lang', FILE_IGNORE_NEW_LINES) as $line) {
		if ($line !== '' && $line[0] !== '#' && strpos($line, '=') !== false) { $keys[] = explode('=', $line, 2)[0]; }
	}
	sort($keys); $translations[] = $keys;
}
check($translations[0] === $translations[1], 'language key parity');
require_once __DIR__.'/../../class/corecompatibility.class.php';
check(MsmCoreCompatibility::profile('22.0.5') === array('latest_end' => false, 'pdf' => 'crabe'), 'exact V22 compatibility policy');
check(MsmCoreCompatibility::profile('24.0.3') === array('latest_end' => true, 'pdf' => 'sponge'), 'exact V24 compatibility policy');
foreach (array('', '22.0.4', '24.0.0', '24.0.1', '24.0.2', '24.0.4', '25.0.0-alpha') as $version) {
	rejects(static function () use ($version) { MsmCoreCompatibility::profile($version); }, 'unreviewed Core version rejected '.$version);
}
// Unknown patch semantics must stop before database or business access.
define('DOL_VERSION', '24.0.2');
require_once __DIR__.'/../../class/booking.class.php';
$untouched = new class {
	public $transaction_opened = 0; public $calls = 0;
	public function query($sql) { $this->calls++; throw new LogicException('Unexpected database access'); }
};
$result = (new MsmBookingService($untouched))->book(1, array(), array('invoice' => false), $user, false);
check($result['status'] === 'failed' && $result['message'] === 'MSMCoreVersionUnsupported', 'unknown Core booking rejected');
check($untouched->calls === 0, 'unknown Core performs no database access');
echo $passed." assertions passed (synthetic contracts; no Dolibarr runtime).\n";
