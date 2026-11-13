<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
// Real pinned Core calendar helpers, synthetic data only; never bootstrap main/master/conf.
$core = getenv('DOLIBARR_TEST_ROOT');
if (!$core || !is_file($core.'/htdocs/core/lib/functions.lib.php')) { fwrite(STDERR, "Set DOLIBARR_TEST_ROOT to a V22.0.5 or V24.0.3 source checkout.\n"); exit(1); }
define('DOL_DOCUMENT_ROOT', $core.'/htdocs');
define('DOL_URL_ROOT', '');
if (is_file(DOL_DOCUMENT_ROOT.'/version.inc.php')) {
	require DOL_DOCUMENT_ROOT.'/version.inc.php';
} else {
	preg_match("/define\('DOL_VERSION', '([^']+)'\)/", file_get_contents(DOL_DOCUMENT_ROOT.'/filefunc.inc.php'), $coreVersion);
	if (empty($coreVersion[1])) { throw new RuntimeException('Cannot read Core version'); }
	define('DOL_VERSION', $coreVersion[1]);
}
define('MAIN_DB_PREFIX', 'test_');
require DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require DOL_DOCUMENT_ROOT.'/core/class/translate.class.php';
require DOL_DOCUMENT_ROOT.'/adherents/class/adherent.class.php';
require __DIR__.'/../../lib/membersubscriptionmassaction.lib.php';
require __DIR__.'/../../class/preview.class.php';
require __DIR__.'/../../lib/preview.lib.php';
require __DIR__.'/../../lib/preview_renderer.lib.php';
error_reporting(E_ALL & ~E_DEPRECATED);
set_error_handler(static function ($severity, $message, $file, $line) {
	if (!(error_reporting() & $severity)) { return false; }
	throw new ErrorException($message, 0, $severity, $file, $line);
});
$conf = (object) array('entity' => 1, 'global' => new stdClass(), 'modules' => array('member' => 1, 'invoice' => 1, 'societe' => 1), 'cache' => array(), 'currency' => 'EUR', 'tzuserinputkey' => 'gmt', 'tzuserdisplaykey' => 'gmt', 'format_date_short' => '%m/%d/%Y');
$langs = new Translate(DOL_DOCUMENT_ROOT, $conf); $langs->setDefaultLang('en_US');
$_SESSION = array();
$passed = 0;
function verify($condition, $label) { global $passed; if (!$condition) { throw new RuntimeException('FAIL '.$label); } $passed++; }
function fails(callable $call, $key) { try { $call(); } catch (RuntimeException $e) { verify($e->getMessage() === $key, $key); return; } catch (InvalidArgumentException $e) { verify($e->getMessage() === $key, $key); return; } throw new RuntimeException('Expected '.$key); }
function stamp($s) { return msmReadDate($s); }
function iso($t) { return dol_print_date($t, '%Y-%m-%d'); }

/** Fail closed on any query other than the three read contracts. */
class PreviewDatabase
{
	public $history = array(); public $periods = array(); public $queries = array(); public $fail = false;
	public function prefix() { return 'test_'; }
	public function plimit($n) { return ' LIMIT '.(int) $n; }
	public function idate($t) { return dol_print_date($t, '%Y-%m-%d %H:%M:%S'); }
	public function free($r) {}
	public function fetch_object($r) { return array_shift($r->rows); }
	public function query($sql)
	{
		$this->queries[] = $sql;
		if ($this->fail) { return false; }
		if ($sql === 'SELECT subscription FROM test_subscription WHERE fk_adherent = 1 ORDER BY datec DESC, rowid DESC LIMIT 1') {
			$rows = $this->history;
			usort($rows, static function ($a, $b) { return strcmp($b['datec'], $a['datec']) ?: $b['rowid'] <=> $a['rowid']; });
			return (object) array('rows' => $rows ? array((object) $rows[0]) : array());
		}
		if (preg_match("/^SELECT rowid FROM test_subscription WHERE fk_adherent = 1 AND dateadh >= '([^']+)' AND dateadh < '([^']+)' LIMIT 1$/D", $sql, $m)) {
			$rows = array_filter($this->periods, static function ($r) use ($m) { return $r['dateadh'] !== null && $r['dateadh'] >= $m[1] && $r['dateadh'] < $m[2]; });
		} elseif (preg_match("/^SELECT rowid, dateadh, datef FROM test_subscription WHERE fk_adherent = 1 AND \\(dateadh IS NULL OR dateadh < '([^']+)'\\) AND \\(datef IS NULL OR datef >= '([^']+)'\\) ORDER BY rowid$/D", $sql, $m)) {
			$rows = array_filter($this->periods, static function ($r) use ($m) { return ($r['dateadh'] === null || $r['dateadh'] < $m[1]) && ($r['datef'] === null || $r['datef'] >= $m[2]); });
		} else { throw new LogicException('Unexpected query: '.$sql); }
		return (object) array('rows' => array_map(static function ($r) { return (object) $r; }, array_values($rows)));
	}
}
class PreviewUser
{
	public $socid = 0; public $id = 1; public $denied = array();
	public function hasRight(...$parts) { return !in_array(implode('.', $parts), $this->denied, true); }
}
$db = new PreviewDatabase(); $member = (object) array('id' => 1, 'datefin' => null, 'datevalid' => null);
$service = new MsmPreviewService($db, $member); $user = new PreviewUser();
foreach (array(24, '12.5', 0, '0', '0.0') as $v) { verify($service->getSubscriptionAmountForBatch($v) === (float) $v, 'configured amount'); }
foreach (array(-1, '-2', 'nan', INF, NAN, array(), false, '  ') as $v) { fails(static function () use ($service, $v) { $service->getSubscriptionAmountForBatch($v); }, 'SubscriptionBulkAmountMissing'); }
fails(static function () use ($service) { $service->getSubscriptionAmountForBatch(null); }, 'SubscriptionBulkAmountMissing');
$db->history = array(array('datec' => '2026-01-01', 'rowid' => 9, 'subscription' => 99), array('datec' => '2026-02-01', 'rowid' => 2, 'subscription' => 12), array('datec' => '2026-02-01', 'rowid' => 3, 'subscription' => 0));
foreach (array(null, '') as $v) { verify($service->getSubscriptionAmountForBatch($v) === 0.0, 'latest creation then rowid, zero retained'); }
$db->fail = true; fails(static function () use ($service) { $service->getSubscriptionAmountForBatch(null); }, 'SubscriptionBulkDatabaseError'); $db->fail = false;
foreach (array('adherent.lire', 'adherent.cotisation.creer', 'facture.lire', 'facture.creer', 'facture.invoice_advance.validate', 'facture.invoice_advance.send') as $right) {
	$conf->global->MAIN_USE_ADVANCED_PERMS = 1; $user->denied = array($right);
	verify(!MsmPreviewService::canCreateSubscriptionWithOptions($user, true, true), 'required right '.$right);
}
$user->denied = array('facture.creer'); verify(MsmPreviewService::canCreateSubscriptionWithOptions($user, false, true), 'invoice rights irrelevant without invoice'); $user->denied = array();
$user->denied = array('facture.invoice_advance.send');
verify(MsmPreviewService::canCreateSubscriptionWithOptions($user, true, false), 'invoice without mail does not require advanced send right');
verify(!MsmPreviewService::canCreateSubscriptionWithOptions($user, true, true), 'invoice mail requires advanced send right');
$user->denied = array();
foreach (array('member', 'invoice', 'societe') as $module) { unset($conf->modules[$module]); verify(!MsmPreviewService::canCreateSubscriptionWithOptions($user, true), 'disabled module'); $conf->modules[$module] = 1; }
$user->socid = 2; verify(!MsmPreviewService::canCreateSubscriptionWithOptions($user), 'external user'); $user->socid = 0;
foreach (array('2026-02-31', '2027-02-29', '1969-12-31', '9999-01-01', '', '2026-1-01', '2026-01', array('2026-01-01'), null, 123) as $v) { verify(msmReadDate($v) === 0, 'strict invalid calendar input'); }
verify(iso(stamp('2028-02-29')) === '2028-02-29', 'leap date');
foreach (array('UTC', 'Europe/Berlin') as $zone) {
	date_default_timezone_set($zone);
	foreach (array('gmt', 'tzserver', 'tzuserrel') as $inputzone) {
		$conf->tzuserinputkey = $inputzone; $_SESSION['dol_tz_string'] = 'America/New_York';
		foreach (array('2026-03-29' => '2026-03-30', '2026-10-25' => '2026-10-26', '2028-02-28' => '2028-02-29', '2026-12-31' => '2027-01-01') as $start => $end) {
			verify(iso(MsmPeriodService::subscriptionEndDateForBatch(stamp($start), (object) array('duration' => '1d'))) === $end, 'one day exception '.$zone.$inputzone.$start);
		}
		foreach (array(array(0, 0), array(1, 0), array(0, 1), array(1, 1)) as $flags) {
			$conf->global->MEMBER_SUBSCRIPTION_SUGGEST_END_OF_MONTH = $flags[0]; $conf->global->MEMBER_SUBSCRIPTION_SUGGEST_END_OF_YEAR = $flags[1];
			foreach (array('2d' => '2026-10-11', '3w' => '2026-10-30', '1m' => '2026-11-09', '4m' => '2027-02-09', 'y' => '2027-10-09', '5y' => '2031-10-09', '' => '2027-10-09') as $duration => $end) {
				verify(iso(MsmPeriodService::subscriptionEndDateForBatch(stamp('2026-10-10'), (object) array('duration' => $duration))) === $end, 'duration ignores calendar flags');
			}
			$end = MsmPeriodService::subscriptionCalendarEndForBatch(stamp('2026-10-10'));
			verify($flags[0] ? iso($end) === '2026-10-31' : ($flags[1] ? iso($end) === '2026-12-31' : $end === null), 'calendar precedence');
		}
	}
}
date_default_timezone_set('UTC'); $conf->tzuserinputkey = 'gmt'; $conf->global = new stdClass();
foreach (array('-1y', '-0y', '00y', '0.0y', '0h', '0', ' y', '2h', 'mn', '3s', '1.5m', 'xyz', '1Y', '9999999999999999y') as $duration) { fails(static function () use ($duration) { MsmPeriodService::subscriptionEndDateForBatch(stamp('2026-01-01'), (object) array('duration' => $duration)); }, 'SubscriptionBulkInvalidDate'); }
// V22 AdherentType::fetch plus the single-subscription form default zero to one.
foreach (array('d', 'w', 'm', 'y') as $unit) {
	verify(MsmPeriodService::duration((object) array('duration' => '0'.$unit)) === array(1, $unit), 'zero quantity defaults to one '.$unit);
	verify(MsmPeriodService::duration((object) array('duration' => '1'.$unit)) === array(1, $unit), 'positive quantity retained '.$unit);
	verify(MsmPeriodService::subscriptionEndDateForBatch(stamp('2026-10-10'), (object) array('duration' => '0'.$unit)) === MsmPeriodService::subscriptionEndDateForBatch(stamp('2026-10-10'), (object) array('duration' => '1'.$unit)), 'zero and one share end date '.$unit);
}
verify(MsmPeriodService::duration((object) array('duration' => '2y')) === array(2, 'y'), 'multiple years retained');
verify(MsmPeriodService::duration((object) array()) === array(1, 'y'), 'missing duration defaults to year');
verify(iso(MsmPeriodService::subscriptionEndDateForBatch(stamp('2026-10-10'), (object) array('duration' => '0d'))) === '2026-10-11', 'zero day retains following-day exception');
$now = stamp('2026-10-10');
$member->datefin = stamp('2026-09-30'); $member->datevalid = stamp('2025-02-20');
verify(iso(MsmPeriodService::subscriptionStartDateForBatch($member, $now)) === '2026-10-01', 'history priority');
$member->datefin = null; verify(iso(MsmPeriodService::subscriptionStartDateForBatch($member, $now)) === '2025-02-20', 'validation fallback');
$member->datevalid = null; verify(MsmPeriodService::subscriptionStartDateForBatch($member, $now) === $now, 'today fallback');
$conf->global->MEMBER_SUBSCRIPTION_START_AFTER = '2w'; verify(iso(MsmPeriodService::subscriptionStartDateForBatch($member, $now)) === '2026-10-24', 'offset from today');
$conf->global->MEMBER_SUBSCRIPTION_START_AFTER = 'y'; fails(static function () use ($member, $now) { MsmPeriodService::subscriptionStartDateForBatch($member, $now); }, 'SubscriptionBulkInvalidDate');
$conf->global = new stdClass();
foreach (array('m' => '2026-10-01', 'Y' => '2026-01-01', '3m' => '2026-07-01') as $correction => $expected) { $conf->global->MEMBER_SUBSCRIPTION_START_FIRST_DAY_OF = $correction; verify(iso(MsmPeriodService::subscriptionStartDateForBatch($member, $now)) === $expected, 'start correction'); }
$conf->global = new stdClass();
foreach (array(array('2026-01-10 15:00:00', '2026-02-10', 'duplicate'), array('2026-01-01', '2026-01-10', 'overlap'), array('2026-01-15', '2026-01-20', 'overlap'), array('2026-01-01', '2026-03-01', 'overlap'), array('2026-01-20', null, 'overlap'), array(null, null, 'overlap'), array('2026-02-11', null, 'clear'), array('2026-01-01', '2026-01-09', 'clear')) as $case) {
	$db->periods = array(array('rowid' => 1, 'dateadh' => $case[0] === null ? null : str_pad($case[0], 19, ' 00:00:00'), 'datef' => $case[1] === null ? null : str_pad($case[1], 19, ' 00:00:00')));
	if ($case[2] === 'duplicate') { fails(static function () use ($service) { $service->checkSubscriptionPeriodForBatch(stamp('2026-01-10'), stamp('2026-02-10')); }, 'SubscriptionBulkDuplicateStart'); }
	else { verify((bool) $service->checkSubscriptionPeriodForBatch(stamp('2026-01-10'), stamp('2026-02-10')) === ($case[2] === 'overlap'), 'inclusive overlap '.$case[2]); }
}
$db->periods = array(); verify(!$service->checkSubscriptionPeriodForBatch(stamp('2026-01-10'), stamp('2026-02-10')), 'fresh periods after change');
fails(static function () use ($service) { $service->checkSubscriptionPeriodForBatch(stamp('2026-01-10'), stamp('2026-01-10')); }, 'SubscriptionBulkEndNotAfterStart');
foreach (array(str_repeat('a', 1025), "a\nb", "a\0b", "\xff", array()) as $template) { fails(static function () use ($template) { msmDescription($template, 'type', 1, 1); }, 'SubscriptionBulkDescriptionInvalid'); }
verify(msmDescription(str_repeat('ü', 1024), 'type', 1, 1) === str_repeat('ü', 1024), 'unicode character count');
verify(strpos(msmDescription('{Member type} {Start date} {End date} {Unknown}', '<script>', stamp('2026-01-01'), stamp('2026-02-01')), '<script>') === 0, 'plain text substitution, escaped only when rendered');
verify(substr(msmDescription('{Unknown}', '', 1, 1), 0) === '{Unknown}', 'unknown placeholder');
$previous = array('start' => stamp('2026-01-10'), 'end' => stamp('2026-02-09'), 'manualend' => false);
verify(msmPeriodInput($previous, stamp('2026-01-11'), $previous['end'])['end'] === null, 'automatic end follows start');
$manual = stamp('2026-05-01'); verify(msmPeriodInput($previous, stamp('2026-01-11'), $manual)['end'] === $manual, 'manual end preserved');
verify(msmPeriodInput($previous, $previous['start'], $manual, true)['end'] === null, 'explicit reset');
$conf->global->MEMBER_SUBSCRIPTION_SUGGEST_END_OF_MONTH = 1;
verify(iso(msmPeriodInput($previous, $previous['start'], $manual, false, null, true)['end']) === '2026-01-31', 'explicit calendar replaces manual');
$conf->global = new stdClass();
$session = array(); $selection = array('owner' => 1, 'entity' => 1, 'ids' => array(1, 2));
$key = msmCreatePreview($session, $selection, 1000);
verify(msmReadPreview($session, $key, 1, 1, 4599)['ids'] === array(1, 2), 'one hour preview');
foreach (array(array($key, 1, 1, 4600), array($key, 2, 1, 1001), array($key, 1, 2, 1001), array(array($key), 1, 1, 1001), array(str_repeat('a', 32), 1, 1, 1001)) as $args) { fails(static function () use ($session, $args) { msmReadPreview($session, ...$args); }, 'MSMInvalidRun'); }
for ($i = 0; $i < 6; $i++) { msmCreatePreview($session, $selection, 1000); }
verify(count($session['msm_previews']) === 5, 'preview limit');
$state = msmReadPreview($session, array_key_last($session['msm_previews']), 1, 1, 1001);
$state['rows'] = array(1 => $previous, 2 => $previous);
$post = array('revision' => '0', 'action' => 'refresh', 'common' => '2027-01-01', 'description' => '{Member type}', 'start' => array(1 => '2026-01-10', 2 => '2026-01-10'), 'end' => array(1 => '2026-02-09', 2 => '2026-02-09'));
$updated = msmUpdatePreview($state, $post); verify($updated['input'][1]['start'] === $previous['start'], 'common input alone inert');
$post['action'] = 'common'; verify(iso(msmUpdatePreview($state, $post)['input'][2]['start']) === '2027-01-01', 'explicit common'); $post['action'] = 'refresh';
foreach (array('amount', 'type', 'ids', 'owner', 'entity', 'other_linked_objects') as $field) { $bad = $post; $bad[$field] = '1'; fails(static function () use ($state, $bad) { msmUpdatePreview($state, $bad); }, 'MSMInvalidForm'); }
foreach (array('invoice' => array(), 'sendmail' => 'yes', 'revision' => '99', 'action' => 'book', 'description' => array(), 'start' => array(1 => array(), 2 => '2026-01-10')) as $field => $value) { $bad = $post; $bad[$field] = $value; fails(static function () use ($state, $bad) { msmUpdatePreview($state, $bad); }, $field === 'description' ? 'SubscriptionBulkDescriptionInvalid' : 'MSMInvalidForm'); }
$bad = $post; $bad['start'][3] = '2026-01-01'; fails(static function () use ($state, $bad) { msmUpdatePreview($state, $bad); }, 'MSMInvalidForm');
$bad = $post; $bad['action'] = 'reset:3'; fails(static function () use ($state, $bad) { msmUpdatePreview($state, $bad); }, 'MSMInvalidForm');
$bad = $post; $bad['start'][1] = '2026-02-31'; verify(msmUpdatePreview($state, $bad)['input'][1]['start'] === 0, 'invalid row date retained for validation');
$bad = $post; $bad['action'] = 'reset:1'; $bad['end'][2] = '2026-06-01'; $r = msmUpdatePreview($state, $bad); verify($r['input'][1]['end'] === null && iso($r['input'][2]['end']) === '2026-06-01', 'reset isolates other row');
$rows = array(array('status' => 'ready', 'errors' => array(), 'start' => 1, 'end' => 2, 'quantity' => 1, 'unit' => 'y', 'amount' => 0), array('status' => 'warning', 'errors' => array(), 'start' => 1, 'end' => 3, 'quantity' => 2, 'unit' => 'm', 'amount' => 12), array('status' => 'error', 'errors' => array('bad'), 'start' => 1, 'end' => 3, 'quantity' => 1, 'unit' => 'y', 'amount' => null));
verify(msmPreviewCounts($rows) === array('selected' => 3, 'ready' => 2, 'skipped' => 1), 'preview counts');
verify(count(msmPreviewWarnings($rows)) === 2, 'different amounts and periods');
verify(strpos(msmDateField('end[1]', '2026-01-01', '<script>'), '<script>') === false, 'HTML escaping');
// Preview-only services must not contain a business write path or locking query.
foreach (array(__DIR__.'/../../class/preview.class.php', __DIR__.'/../../class/period.class.php', __DIR__.'/../../class/actions_membersubscriptionmassaction.class.php') as $file) {
	$source = file_get_contents($file);
	verify(!preg_match('/->(?:subscription|subscriptionComplementaryActions|create|validate|generateDocument|sendEmail)\s*\(|FOR UPDATE|\b(?:INSERT INTO|UPDATE llx_|DELETE FROM)\b/', $source), 'read-only service structure');
}

// Direct business eligibility contract, after object authorization.
$type = (object) array('status' => 1, 'subscription' => 1);
$eligible = (object) array('status' => 1, 'morphy' => 'phy');
foreach (array(-1, 0) as $status) { $eligible->status = $status; fails(static function () use ($eligible, $type) { MsmPreviewService::validateEligibility($eligible, $type); }, 'SubscriptionBulkIneligible'); }
$eligible->status = 1;
foreach (array('0', '-1,0', '2') as $allowed) { $conf->global->MEMBER_SUBSCRIPTION_ALLOWED_FOR_STATUS = $allowed; fails(static function () use ($eligible, $type) { MsmPreviewService::validateEligibility($eligible, $type); }, 'SubscriptionBulkIneligible'); }
foreach (array('', '-1,0,1', '1') as $allowed) { $conf->global->MEMBER_SUBSCRIPTION_ALLOWED_FOR_STATUS = $allowed; MsmPreviewService::validateEligibility($eligible, $type); verify(true, 'validated allowed'); }
$type->status = 0; fails(static function () use ($eligible, $type) { MsmPreviewService::validateEligibility($eligible, $type); }, 'SubscriptionBulkIneligible');
$type->status = 1; $type->subscription = 0; fails(static function () use ($eligible, $type) { MsmPreviewService::validateEligibility($eligible, $type); }, 'SubscriptionBulkIneligible'); $type->subscription = 1;
$conf->global->MEMBER_NEWFORM_DOLIBARRTURNOVER = 1; $eligible->morphy = 'mor'; fails(static function () use ($eligible, $type) { MsmPreviewService::validateEligibility($eligible, $type); }, 'SubscriptionBulkSpecialAmount');
$eligible->morphy = 'phy'; MsmPreviewService::validateEligibility($eligible, $type); verify(true, 'physical member unaffected'); $conf->global = new stdClass();

/** Synthetic member values, no business API capable of creating anything. */
class PreviewMember
{
	public $id = 1; public $typeid = 1; public $entity = 1; public $socid = 0;
	public $email = 'member@example.invalid'; public $default_lang = 'en_US'; public $morphy = 'phy';
	public $company = ''; public $address = ''; public $zip = ''; public $town = ''; public $country_id = 1; public $phone = '';
	public $datefin = null; public $datevalid = null; public $status = 1; public $thirdparty;
	public function getFullName($langs) { return '<Synthetic member>'; }
	public function fetch_thirdparty() { return 0; }
}
class PreviewFixtureService extends MsmPreviewService
{
	public $type; public $deny = false;
	protected function invoiceProfile() { return new class { public function resolve(...$args) { return array('synthetic' => true); } }; }
	protected function subscriptionTypeForBatch($user)
	{
		if ($this->deny) { throw new RuntimeException('NotEnoughPermissions'); }
		self::validateEligibility($this->member, $this->type);
		return $this->type;
	}
}
$db = new PreviewDatabase(); $member = new PreviewMember(); $service = new PreviewFixtureService($db, $member);
$service->type = (object) array('duration' => 'y', 'label' => '<Synthetic type>', 'amount' => '0', 'status' => 1, 'subscription' => 1);
$user = new PreviewUser(); $start = stamp('2027-01-01');
$row = $service->previewSubscriptionWithOptions($user, $start, false, false, null, false, '{Member type} {Start date} {End date}');
verify($row['status'] === 'ready' && $row['amount'] === 0.0 && iso($row['end']) === '2027-12-31', 'composed preview zero amount');
$service->type->amount = '-1'; $bad = $service->previewSubscriptionWithOptions($user, $start, false, false);
verify($bad['status'] === 'error' && $bad['end'] === $row['end'] && $bad['member'] !== '', 'amount failure retains other details'); $service->type->amount = '24';
$service->deny = true; $bad = $service->previewSubscriptionWithOptions($user, $start, false, false);
verify($bad['member'] === '' && $bad['amount'] === null && $bad['thirdparty'] === '', 'denied member details hidden'); $service->deny = false;
$row = $service->previewSubscriptionWithOptions($user, $start, true, false);
verify(in_array('SubscriptionBulkThirdPartyMissing', $row['errors'], true), 'invoice needs third party');
$row = $service->previewSubscriptionWithOptions($user, $start, true, false, null, true);
verify($row['autocreate'] && $row['status'] === 'warning', 'planned third party without writes');
$user->denied = array('societe.creer'); $row = $service->previewSubscriptionWithOptions($user, $start, true, false, null, true);
verify(in_array('NotEnoughPermissions', $row['errors'], true), 'third party creation permission'); $user->denied = array();
$member->entity = 2; $row = $service->previewSubscriptionWithOptions($user, $start, true, false, null, true);
verify(in_array('NotEnoughPermissions', $row['errors'], true), 'no planned third party outside current entity'); $member->entity = 1;
$member->socid = 8; $row = $service->previewSubscriptionWithOptions($user, $start, true, false);
verify(in_array('MSMThirdPartyInaccessible', $row['errors'], true), 'inaccessible existing third party differs from missing link'); $member->socid = 0;
$member->email = ''; $conf->global->MAIN_DISABLE_ALL_MAILS = 1;
$row = $service->previewSubscriptionWithOptions($user, $start, false, true);
verify(in_array('SubscriptionBulkEmailMissing', $row['warnings'], true) && in_array('SubscriptionBulkEmailDisabled', $row['warnings'], true) && !$row['errors'], 'mail preflight warnings only'); $conf->global = new stdClass();
$db->periods = array(array('rowid' => 1, 'dateadh' => '2026-12-01 00:00:00', 'datef' => '2027-02-01 00:00:00'));
$row = $service->previewSubscriptionWithOptions($user, $start, false, false);
verify($row['status'] === 'warning' && count($row['overlaps']) === 1, 'overlap warning processable');
$db->periods[0]['dateadh'] = '2027-01-01 15:30:00'; $bad = $service->previewSubscriptionWithOptions($user, $start, false, false);
verify($bad['status'] === 'error' && in_array('SubscriptionBulkDuplicateStart', $bad['errors'], true), 'duplicate day hard error');
$db->fail = true; fails(static function () use ($service, $user, $start) { $service->previewSubscriptionWithOptions($user, $start, false, false); }, 'SubscriptionBulkDatabaseError'); $db->fail = false; $db->periods = array();
$rows = array(); foreach (array('24', '-1', '0', '12') as $amount) { $service->type->amount = $amount; $rows[] = $service->previewSubscriptionWithOptions($user, $start, false, false); }
verify(msmPreviewCounts($rows) === array('selected' => 4, 'ready' => 3, 'skipped' => 1), 'mixed row failure isolated');
$service->type->amount = '12'; $a = $service->previewSubscriptionWithOptions($user, $start, false, false); $service->type->amount = '13'; $b = $service->previewSubscriptionWithOptions($user, $start, false, false);
verify($a['amount'] !== $b['amount'] && $a['fingerprint'] !== $b['fingerprint'], 'fresh business values and future snapshot');
foreach (array('2027-01-31' => '2027-03-02', '2028-01-31' => '2028-03-01') as $s => $e) { verify(iso(MsmPeriodService::subscriptionEndDateForBatch(stamp($s), (object) array('duration' => '1m'))) === $e, 'reference month overflow semantics'); }
$conf->global->MEMBER_SUBSCRIPTION_START_FIRST_DAY_OF = '3m'; $member->datefin = stamp('2027-06-15');
verify(iso(MsmPeriodService::subscriptionStartDateForBatch($member, $start)) === '2027-03-01', 'quarter correction based on previous end'); $conf->global = new stdClass();
// OPT group: invoice/third-party dependency, including native forms without JavaScript.
$conf->global = new stdClass(); $conf->modules['societe'] = 1; $user = new PreviewUser();
$state = array('revision' => 0, 'ids' => array(1), 'rows' => array(1 => $previous), 'options' => array('invoice' => true, 'autocreate' => false, 'sendmail' => false, 'description' => ''), 'common' => '');
$post = array('revision' => '0', 'action' => 'refresh', 'common' => '', 'description' => '', 'invoice' => '1', 'autocreate' => '1', 'start' => array(1 => '2026-01-10'), 'end' => array(1 => '2026-02-09'));
$planned = msmUpdatePreview($state, $post);
verify($planned['options']['invoice'] && $planned['options']['autocreate'], 'OPT-02 explicit invoice and customer creation');
unset($post['invoice'], $post['autocreate']); $post['sendmail'] = '1';
$enabled = msmUpdatePreview($state, $post);
verify($enabled['options']['sendmail'] && !$enabled['options']['invoice'] && !$enabled['options']['autocreate'], 'OPT-01 independent mail without invoices');
foreach (array('invoice', 'autocreate') as $option) {
	foreach (array(array('1'), 'yes') as $value) {
		$bad = $post; $bad[$option] = $value;
		fails(static function () use ($state, $bad) { msmUpdatePreview($state, $bad); }, 'MSMInvalidForm');
	}
}
// Exercise the actual renderer, not a duplicate of its condition.
// The real price() helper expects the standard application decimal configuration.
$conf->global->MAIN_MAX_DECIMALS_UNIT = 2;
$conf->global->MAIN_MAX_DECIMALS_TOT = 2;
$conf->global->MAIN_MAX_DECIMALS_SHOWN = 2;
$state['rows'] = array();
foreach (array(array(true, true, true, false), array(false, true, true, true), array(true, false, true, true), array(true, true, false, true)) as $case) {
	$state['options']['invoice'] = $case[0]; $state['options']['autocreate'] = true;
	if ($case[1]) { $conf->modules['societe'] = 1; } else { unset($conf->modules['societe']); }
	$user->denied = $case[2] ? array() : array('societe.creer');
	$html = msmRenderPreview($state, str_repeat('a', 32));
	preg_match('/<input[^>]*name="autocreate"[^>]*>/', $html, $m);
	$enabledOption = $case[0] && $case[1] && $case[2];
	verify(isset($m[0]) && (strpos($m[0], ' disabled') === false) === $enabledOption
		&& (strpos($m[0], ' checked') !== false) === $enabledOption, 'OPT-09 renderer dependency matrix');
}
$previousGlobals = clone $conf->global; $previousDenied = $user->denied;
$conf->global->MAIN_USE_ADVANCED_PERMS = 1;
$state['options']['invoice'] = true; $state['options']['sendmail'] = true; $user->denied = array('facture.invoice_advance.send');
$html = msmRenderPreview($state, str_repeat('a', 32));
preg_match('/<input[^>]*name="invoice"[^>]*>/', $html, $m);
verify(isset($m[0]) && strpos($m[0], ' disabled') === false, 'invoice renderer availability independent from selected mail');
verify(!MsmPreviewService::canCreateSubscriptionWithOptions($user, true, true), 'advanced permission denies combined invoice mail while invoice control remains available');
$conf->global = $previousGlobals; $user->denied = $previousDenied;
$conf->modules['societe'] = 1;
echo $passed." assertions passed (Core ".DOL_VERSION." calendar APIs, synthetic business data; no database connection).\n";
