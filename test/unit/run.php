<?php
/* Copyright (C) 2026 iLLixM
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
define('DOL_VERSION', '99.0.0');
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
if (($argv[1] ?? '') === 'oversized-entry') {
	$_SESSION = array('token' => 'trusted-csrf'); $_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST = array('token' => 'trusted-csrf', 'confirmmassaction' => 'Confirm', 'toselect' => range(10000, 10100));
	// These IDs cannot be fetched by the fixture: any object query prevents the notice.
	register_shutdown_function(static function () { echo json_encode($_SESSION); });
	$hook->doMassActions(array('currentcontext' => 'memberlist', 'massaction' => ActionsMemberSubscriptionMassaction::ACTION, 'toselect' => $_POST['toselect']), $object, $action);
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
check($descriptor->need_dolibarr_version === array(21, 0, 0), 'primary version');
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
foreach (array('21.0.0', '22.0.0', '22.0.5', '23.0.4', '24.0.0', '24.0.1', '24.0.2', '24.0.3', '24.0.99', '25.0.0-alpha', '99.0.0') as $version) {
	check(is_array(MsmCoreCompatibility::profile($version)), 'open admission '.$version);
}
foreach (array(22 => 5, 23 => 4, 24 => 3) as $major => $lastPatch) {
	for ($patch = 0; $patch <= $lastPatch; $patch++) {
		$version = $major.'.0.'.$patch;
		check(MsmCoreCompatibility::profile($version)['latest_end'] === ($major === 24 && $patch >= 2), 'P1 historical R16 contract '.$version);
	}
}
foreach (array('21.0.0', '22.0.6', '23.0.5', '23.1.0', '24.0.4', '25.0.0', '99.0.0', '24.0.2-rc1') as $version) {
	check(MsmCoreCompatibility::profile($version)['latest_end'] === null, 'unverified R16 uses observer '.$version);
}
check(MsmCoreCompatibility::profile('24.0.1')['latest_end'] === false, 'pre-boundary Core expectation');
check(MsmCoreCompatibility::profile('24.0.2')['latest_end'] === true, 'R16 boundary');
check(MsmCoreCompatibility::profile('99.0.0')['latest_end'] === null, 'future uses observer, not guessed semantics');
foreach (array('', '20.9.9', 'garbage') as $version) {
	rejects(static function () use ($version) { MsmCoreCompatibility::profile($version); }, 'invalid/below-minimum version '.$version);
}
$modelsRoot = sys_get_temp_dir().'/msm-model-capability-'.bin2hex(random_bytes(8));
mkdir($modelsRoot.'/core/modules/facture/doc', 0700, true);
try {
	rejects(static function () use ($modelsRoot) { MsmCoreCompatibility::defaultPdf($modelsRoot); }, 'missing Core PDF models rejected');
	file_put_contents($modelsRoot.'/core/modules/facture/doc/pdf_sponge.modules.php', '<?php');
	check(MsmCoreCompatibility::defaultPdf($modelsRoot) === 'sponge', 'available sponge fallback without version restriction');
	file_put_contents($modelsRoot.'/core/modules/facture/doc/pdf_crabe.modules.php', '<?php');
	check(MsmCoreCompatibility::defaultPdf($modelsRoot) === 'crabe', 'available historical fallback preserved');
} finally {
	foreach (array('crabe', 'sponge') as $model) { $path = $modelsRoot.'/core/modules/facture/doc/pdf_'.$model.'.modules.php'; if (is_file($path)) { unlink($path); } }
	foreach (array('/core/modules/facture/doc', '/core/modules/facture', '/core/modules', '/core', '') as $dir) { rmdir($modelsRoot.$dir); }
}
class UnsupportedCoreContract { private function hidden() {} public function required($a, $b) {} }
foreach (array(array('missing' => 0), array('hidden' => 0), array('required' => 1)) as $calls) {
	rejects(static function () use ($calls) { MsmCoreCompatibility::methods(new UnsupportedCoreContract(), $calls); }, 'objective callable mismatch');
}
class FinalEndContract { final public function update_end_date($user) {} }
class TypedEndContract { public function update_end_date($user): int { return 1; } }
foreach (array('FinalEndContract', 'TypedEndContract') as $class) {
	rejects(static function () use ($class) { MsmCoreCompatibility::extension($class, 'update_end_date', 1); }, 'incompatible observer rejected before declaration');
}
$probe = 'define("DOL_DOCUMENT_ROOT", '.var_export(DOL_DOCUMENT_ROOT, true).');'
	.'class CommonHookActions { final public function doMassActions($parameters, &$object, &$action, $hookmanager = null) {} }'
	.'require '.var_export(realpath(__DIR__.'/../../class/actions_membersubscriptionmassaction.class.php'), true).';'
	.'$hook = new ActionsMemberSubscriptionMassaction(null); $o = null; $a = "";'
	.'exit($hook->doMassActions(array("massaction" => "memberSubscriptionMassactionStart"), $o, $a) === -1 ? 0 : 1);';
exec(escapeshellarg(PHP_BINARY).' -r '.escapeshellarg($probe), $probeOutput, $probeExit);
check($probeExit === 0, 'incompatible Core hook controlled without inheritance fatal');
// PR11-F02: declarations run in isolated processes, so a fatal cannot hide a
// successful rejection. Abstract bases with implemented obligations remain valid.
foreach (array(false, true) as $extra) {
	$probe = 'define("DOL_DOCUMENT_ROOT", '.var_export(DOL_DOCUMENT_ROOT, true).');'
		.'abstract class CommonHookActions { abstract public function addMoreMassActions($p, &$o, &$a, $h = null);'
		.'abstract public function doMassActions($p, &$o, &$a, $h = null);'
		.($extra ? 'abstract public function futureRequired();' : '').'}'
		.'require '.var_export(realpath(__DIR__.'/../../class/actions_membersubscriptionmassaction.class.php'), true).';'
		.'$h = new ActionsMemberSubscriptionMassaction(null);'
		.'exit(is_subclass_of($h, "CommonHookActions") === '.($extra ? 'false' : 'true').' ? 0 : 1);';
	$probeOutput = array(); exec(escapeshellarg(PHP_BINARY).' -r '.escapeshellarg($probe).' 2>&1', $probeOutput, $probeExit);
	check($probeExit === 0, 'PR11 abstract hook obligations '.(int) $extra.' '.implode(' ', $probeOutput));
}
$probe = 'define("DOL_DOCUMENT_ROOT", '.var_export(DOL_DOCUMENT_ROOT, true).');'
	.'abstract class Adherent { public function update_end_date($u) {} abstract public function futureRequired(); }'
	.'try { require '.var_export(realpath(__DIR__.'/../../class/bookingmember.class.php'), true).'; }'
	.'catch (RuntimeException $e) { exit($e->getMessage() === "MSMCoreContractUnsupported" ? 0 : 2); } exit(1);';
$probeOutput = array(); exec(escapeshellarg(PHP_BINARY).' -r '.escapeshellarg($probe).' 2>&1', $probeOutput, $probeExit);
check($probeExit === 0, 'PR11 abstract member rejected before declaration '.implode(' ', $probeOutput));
require_once __DIR__.'/../../class/booking.class.php';
$untouched = new class {
	public $transaction_opened = 0; public $calls = 0;
	public function query($sql) { $this->calls++; throw new LogicException('Unexpected database access'); }
};
$result = (new MsmBookingService($untouched))->book(1, array(), array('invoice' => false), $user, false);
check($result['status'] === 'failed' && $result['message'] === 'MSMCoreContractUnsupported', 'future Core missing DB contract rejected');
check($untouched->calls === 0, 'capability check performs no database access');
echo $passed." assertions passed (synthetic contracts; no Dolibarr runtime).\n";

foreach (array(null, '', 'bad', '1.5', '1e2', '-2', '0', 0, -1, true, array(), (string) PHP_INT_MAX.'0') as $bad) {
	$conf->global = (object) array('MEMBERSUBSCRIPTIONMASSACTION_MAX_MEMBERS_PER_BATCH' => $bad);
	check(msmBatchLimit() === 100, 'Issue8 invalid module value defaults to100');
}
foreach (array(25,100,150) as $max) {
	$conf->global = (object) array('MEMBERSUBSCRIPTIONMASSACTION_MAX_MEMBERS_PER_BATCH' => (string)$max);
	check(msmBatchLimit() === $max, 'Issue8 configured boundary');
	foreach (array($max-1,$max) as $n) { msmRequireBatchSize(range(1,$n)); check(true,'Issue8 within boundary'); }
	rejects(static function () use ($max) { msmRequireBatchSize(range(1,$max+1)); }, 'Issue8 boundary exceeded');
}
$conf->global = (object) array('MEMBERSUBSCRIPTIONMASSACTION_MAX_MEMBERS_PER_BATCH'=>'150','MAIN_LIMIT_FOR_MASS_ACTIONS'=>'25');
check(msmBatchLimit()===25,'Issue8 global minimum');
$conf->global = new stdClass();
exec(escapeshellarg(PHP_BINARY).' '.escapeshellarg(__FILE__).' oversized-entry', $noticeOutput, $noticeExit);
$notice=json_decode(implode("\n",$noticeOutput),true);
check($noticeExit===0 && ($notice['msm_batch_limit']['count']??0)===101 && !isset($notice['membersubscriptionmassaction']), 'Issue8 oversized hook exits before object fetch, retains count only');
echo "Issue8 limit and early-hook contracts passed.\n";

$conf->global=(object)array('MAIN_LIMIT_FOR_MASS_ACTIONS'=>'0');
check(msmBatchLimit()===0,'Issue8 configured global zero fails closed');
