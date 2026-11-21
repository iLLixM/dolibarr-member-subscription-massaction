<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
// Actual orchestrator + local PHP file sessions; services are spies, no Core/DB DML.
ob_start();
require_once __DIR__.'/booking.php';
require_once DOL_DOCUMENT_ROOT.'/core/modules/syslog/mod_syslog_file.php';
$terminalBaseline = $passed;
class TerminalBookingSpy
{
	public $calls = array();
	public $result;
	public $changeSessionPath = null;
	public function book($id, $row, $options, $user, $overlap)
	{
		$this->calls[] = array('event' => 'service_preflight_and_DML_spy', 'id' => $id);
		if ($this->changeSessionPath !== null) { session_save_path($this->changeSessionPath); }
		return $this->result;
	}
}
class TerminalEffectFence
{
	public $calls = 0;
	public function send(...$args) { $this->calls++; throw new LogicException('Mail after terminal completion'); }
	public function generate(...$args) { $this->calls++; throw new LogicException('PDF after terminal completion'); }
}
$terminalRoot = sys_get_temp_dir().'/msm-terminal-'.bin2hex(random_bytes(8));
mkdir($terminalRoot, 0700);
$oldConf = clone $conf; $oldSessionPath = session_save_path(); $oldCookies = ini_get('session.use_cookies'); $oldLimiter = session_cache_limiter();
if (session_status() === PHP_SESSION_ACTIVE) { session_write_close(); }
ini_set('session.use_cookies', '0'); session_cache_limiter(''); session_save_path($terminalRoot);
$conf->modules['syslog'] = 1; $conf->loghandlers = array(new mod_syslog_file());
$conf->global->SYSLOG_FILE = $terminalRoot.'/qualified.log';
$key = str_repeat('a', 32); $callTrace = array();
try {
	foreach (array('success', 'rollback', 'unknown', 'E02', 'E15', 'continue', 'rollback_continue', 'rollback_wrong_continue', 'missing_completion', 'unknown_wrong_continue') as $case) {
		session_id('msmterminal'.bin2hex(random_bytes(8))); session_start();
		$state = array('phase' => 'processing', 'revision' => 2, 'ids' => array(1, 2), 'overlap' => array(),
			'profile' => array('profile' => 'CoreGuarded'),
			'options' => array('invoice' => true, 'sendmail' => true),
			'rows' => array(1 => array('errors' => array(), 'overlaps' => array()), 2 => array('errors' => array(), 'overlaps' => array())),
			'results' => array(1 => array('status' => 'unknown'), 2 => array('status' => 'unknown')));
		$_SESSION = array('msm_previews' => array($key => $state), 'unrelated' => 'preserved');
		$checkpoint = new MsmFileBookingCheckpoint($key); session_write_close();
		MsmFileBookingCheckpoint::requireRuntime(); verify(true, 'F27 actual file session and standard logger admission');
		$spy = new TerminalBookingSpy(); $effect = new TerminalEffectFence();
		$status = in_array($case, array('rollback','rollback_continue','rollback_wrong_continue'), true) ? 'failed' : ($case === 'unknown' || $case === 'unknown_wrong_continue' ? 'unknown' : ($case === 'E02' ? 'not_processed' : 'success'));
		$spy->result = array('status' => $status, 'message' => $status === 'success' ? 'MSMBooked' : 'MSMBatchStopped', 'id' => $status === 'success' ? 7 : 0,
			'amount' => 12, 'mail' => 'not_selected', 'stop' => !in_array($case, array('continue','rollback_continue','rollback_wrong_continue','missing_completion','unknown_wrong_continue'), true), 'rollback' => in_array($case, array('rollback','rollback_continue'), true) ? 'confirmed' : 'unconfirmed');
		if (in_array($case, array('continue','rollback_continue','rollback_wrong_continue','unknown_wrong_continue'), true)) { $spy->result['completion'] = 'nonterminal_acknowledged'; }
		if ($case === 'success') { $spy->result['invoice'] = array('id' => 42, 'ref' => 'SYNTHETIC', 'entity' => 1, 'customer_id' => 3, 'customer_created' => false, 'pdf' => 'pending'); }
		if ($case === 'E15') { $spy->changeSessionPath = $terminalRoot.'/missing'; }
		if (in_array($case, array('continue','rollback_continue'), true)) { $state['options'] = array('invoice' => false, 'sendmail' => false); }
		// A separate no-effect mail spy is allowed only for the nonterminal path.
		$mail = in_array($case, array('continue','rollback_continue'), true) ? new class { public function send(...$args) { return 'not_selected'; } } : $effect;
		$monitorEnabled = function_exists('xdebug_info') && in_array('develop', xdebug_info('mode'), true);
		$monitorOffset = $monitorEnabled ? count(xdebug_get_monitored_functions()) : 0;
		if ($monitorEnabled) { xdebug_start_function_monitor(array('msmProcessBooking', 'TerminalBookingSpy->book', 'MsmFileBookingCheckpoint->__invoke', 'session_start', 'session_write_close', 'TerminalEffectFence->send', 'TerminalEffectFence->generate')); }
		$done = msmProcessBooking($state, $spy, $mail, new PreviewUser(), $checkpoint, $effect);
		$monitor = array();
		if ($monitorEnabled) {
			foreach (array_slice(xdebug_get_monitored_functions(), $monitorOffset) as $entry) { $monitor[] = array('function' => $entry['function'], 'source' => basename($entry['filename']), 'line' => $entry['lineno']); }
			xdebug_stop_function_monitor();
			$functions = array_column($monitor, 'function');
			verify(in_array('TerminalBookingSpy->book', $functions, true) && in_array('MsmFileBookingCheckpoint->__invoke', $functions, true) && array_search('TerminalBookingSpy->book', $functions, true) < array_search('MsmFileBookingCheckpoint->__invoke', $functions, true), 'F27 service result precedes fixed checkpoint');
			verify(count(array_keys($functions, 'TerminalBookingSpy->book', true)) === (in_array($case, array('continue','rollback_continue'), true) ? 2 : 1), 'F27 per-case native call slice');
			verify(!in_array('TerminalEffectFence->send', $functions, true) && !in_array('TerminalEffectFence->generate', $functions, true), 'F27 no monitored terminal effects');
		}
		verify(count($spy->calls) === (in_array($case, array('continue','rollback_continue'), true) ? 2 : 1), 'F27 no following preflight/DML/retry '.$case);
		verify($effect->calls === 0, 'F27 no terminal mail/PDF '.$case);
		verify($done['results'][1]['status'] === $status, 'F27 acknowledged/unknown/E02 member outcome preserved '.$case);
		if (!in_array($case, array('continue','rollback_continue'), true)) { verify($done['results'][2]['status'] === 'not_processed' && $done['batch_stop'], 'F27 explicit NOT_PROCESSED followers '.$case); }
		if ($case === 'E15') {
			verify($done['checkpoint_failed'] && $done['phase'] === 'processing' && $done['results'][1]['status'] === 'success', 'F27 E15 success retained with persistence warning');
			session_save_path($terminalRoot);
		} else { verify($done['phase'] === 'done', 'F27 durable terminal/normal end '.$case); }
		session_start(); $stored = $_SESSION['msm_previews'][$key];
		verify($_SESSION['unrelated'] === 'preserved', 'F27 unrelated session state preserved');
		if ($case === 'E15') { verify($stored['phase'] === 'processing' && $stored['results'][1]['status'] === 'unknown', 'F27 failed checkpoint cannot fabricate durable success'); }
		else { verify($stored['phase'] === 'done' && $stored['results'][1]['status'] === $status, 'F27 fresh file-session readback'); }
		fails(static function () use ($stored) { msmConsumeConfirmation($stored, array('preview' => str_repeat('a',32), 'token' => 'synthetic', 'revision' => (string)$stored['revision'], 'action' => 'book'));  }, 'MSMInvalidForm');
		session_write_close();
		if ($case === 'success') { $renderState = $done; verify($done['results'][1]['invoice']['pdf']['status'] === 'not_processed', 'F27 terminal PDF explicitly not generated'); }
		$callTrace[] = array('case' => $case, 'calls' => $spy->calls, 'mail_pdf_calls' => $effect->calls, 'member' => $done['results'][1]['status'], 'follower' => $done['results'][2]['status'], 'phase' => $done['phase'], 'checkpoint_failed' => !empty($done['checkpoint_failed']), 'function_monitor' => $monitor);
	}
	foreach ($renderState['rows'] as &$renderRow) { $renderRow += array('member' => '<script>Synthetic</script>', 'type' => 'Synthetic', 'amount' => 12, 'start' => 1, 'end' => 2, 'description' => 'Synthetic'); } unset($renderRow);
	$html = msmRenderBooking($renderState, $key);
	verify(strpos($html, msmText('MSMStatus_not_processed')) !== false && strpos($html, msmText('MSMNotProcessedTotal', 1)) !== false, 'F27 NOT_PROCESSED label/count rendered');
	verify(strpos($html, '<script>') === false && strpos($html, '&lt;script&gt;') !== false, 'F27 result output escaped');
	$renderState['results'][2]['status'] = 'skipped';
	verify(strpos(msmRenderBooking($renderState, $key), msmText('MSMStatus_skipped')) !== false, 'F27 historical skipped remains displayable');

	// No arbitrary save callback may follow a Core-Guarded completion.
	$customCalls = 0;
	fails(static function () use ($state,$spy,$effect,&$customCalls) { msmProcessBooking($state,$spy,$effect,new PreviewUser(),static function()use(&$customCalls){$customCalls++;}); }, 'MSMCoreGuardedSessionUnsupported');
	verify($customCalls === 0, 'F27 arbitrary checkpoint never invoked');
	foreach (array(new stdClass(), new class { public function export() { throw new LogicException('Must not invoke unknown exporter'); } }) as $handler) {
		$conf->loghandlers = array($handler); $before = count($spy->calls);
		fails(static function () use ($state,$spy,$effect,$checkpoint) { msmProcessBooking($state,$spy,$effect,new PreviewUser(),$checkpoint); }, 'MSMCoreGuardedLoggerUnsupported');
		verify(count($spy->calls) === $before, 'F27 unknown logger denied before service');
	}
	$conf->loghandlers = array(new mod_syslog_file());
	foreach (array('tcp://example.invalid/log', $terminalRoot.'/missing/log') as $path) {
		$conf->global->SYSLOG_FILE = $path;
		fails(static function () { MsmFileBookingCheckpoint::requireRuntime(); }, 'MSMCoreGuardedLoggerUnsupported');
	}
	$conf->global->SYSLOG_FILE = $terminalRoot.'/qualified.log';
	session_save_path($terminalRoot.'/missing');
	fails(static function () use ($state,$spy,$effect,$checkpoint) { msmProcessBooking($state,$spy,$effect,new PreviewUser(),$checkpoint); }, 'MSMCoreGuardedSessionUnsupported');
	session_save_path('1;'.$terminalRoot);
	fails(static function () { MsmFileBookingCheckpoint::requireRuntime(); }, 'MSMCoreGuardedSessionUnsupported');
	session_save_path($terminalRoot);
	$before = count($spy->calls); $originalId = session_id(); session_id('msmdifferentbinding');
	fails(static function () use ($state,$spy,$effect,$checkpoint) { msmProcessBooking($state,$spy,$effect,new PreviewUser(),$checkpoint); }, 'MSMSessionFailure');
	verify(count($spy->calls) === $before, 'F27 stale checkpoint binding denied before service'); session_id($originalId);

	session_set_save_handler(new SessionHandler(), false);
	fails(static function () { MsmFileBookingCheckpoint::requireRuntime(); }, 'MSMCoreGuardedSessionUnsupported');
	session_module_name('files');
} finally {
	if (session_status() === PHP_SESSION_ACTIVE) { session_abort(); }
	session_save_path($oldSessionPath); ini_set('session.use_cookies', $oldCookies); session_cache_limiter($oldLimiter); $conf = $oldConf;
	foreach (glob($terminalRoot.'/*') as $file) { unlink($file); } rmdir($terminalRoot);
}
if (getenv('MSM_TERMINAL_TRACE')) { file_put_contents(getenv('MSM_TERMINAL_TRACE'), json_encode(array('scope'=>'synthetic actual batch orchestrator; no DB/SMTP/PDF','cases'=>$callTrace), JSON_PRETTY_PRINT)."\n"); }
echo ($passed-$terminalBaseline)." terminal-boundary assertions passed (actual orchestrator, fake services, local PHP sessions; no DB).\n";
