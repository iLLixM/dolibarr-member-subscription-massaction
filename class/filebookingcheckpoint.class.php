<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
/** Bounded file-session checkpoint: no DB object, logger, hook or arbitrary callback. */
final class MsmFileBookingCheckpoint
{
	private $key;
	private $path;
	private $session;
	private $confirmedOrigin;
	private $attempted = array();
	public function __construct($key)
	{
		if (!is_string($key) || !preg_match('/^[a-f0-9]{32}$/D', $key)) { throw new RuntimeException('MSMSessionFailure'); }
		$this->path = self::requireSession(); $this->session = session_id(); $this->key = $key;
		// Only an object created by the controller in the original active
		// CONFIRM session can authorize direct DML. An object manufactured from
		// an already consumed or interrupted session must never enable replay.
		$state = session_status() === PHP_SESSION_ACTIVE ? ($_SESSION['msm_previews'][$key] ?? null) : null;
		$this->confirmedOrigin = is_array($state) && ($state['phase'] ?? null) === 'confirm'
			? array('revision' => $state['revision'] ?? null, 'ids' => $state['ids'] ?? null,
				'rows' => $state['rows'] ?? null, 'options' => $state['options'] ?? null,
				'profile' => $state['profile'] ?? null, 'owner' => $state['owner'] ?? null,
				'entity' => $state['entity'] ?? null) : null;
	}
	public static function requireSession()
	{
		$path = session_save_path();
		if (session_module_name() !== 'files' || session_id() === '' || strpos($path, ';') !== false
			|| strpos($path, '://') !== false || $path === '' || $path[0] !== '/' || !is_dir($path)
			|| !is_readable($path) || !is_writable($path) || realpath($path) === false) { throw new RuntimeException('MSMCoreGuardedSessionUnsupported'); }
		return realpath($path);
	}
	/** Inspect actual objects/settings before work; never call an unknown exporter. */
	public static function requireRuntime()
	{
		global $conf;
		self::requireSession();
		if (!isModEnabled('syslog') || empty($conf->loghandlers) || !is_array($conf->loghandlers)
			|| getDolGlobalString('SYSLOG_DISABLE_LOGHANDLER_FILE') || getDolGlobalString('MAIN_SYSLOG_DISABLE_FILE')) { throw new RuntimeException('MSMCoreGuardedLoggerUnsupported'); }
		$source = realpath(DOL_DOCUMENT_ROOT.'/core/modules/syslog/mod_syslog_file.php');
		if ($source === false) { throw new RuntimeException('MSMCoreGuardedLoggerUnsupported'); }
		foreach ($conf->loghandlers as $handler) {
			if (!is_object($handler) || get_class($handler) !== 'mod_syslog_file'
				|| realpath((new ReflectionMethod($handler, 'export'))->getFileName()) !== $source) { throw new RuntimeException('MSMCoreGuardedLoggerUnsupported'); }
		}
		$file = str_replace('DOL_DATA_ROOT', defined('DOL_DATA_ROOT') ? DOL_DATA_ROOT : '', getDolGlobalString('SYSLOG_FILE'));
		if ($file === '' && defined('DOL_DATA_ROOT')) { $file = DOL_DATA_ROOT.'/dolibarr.log'; }
		if ($file === '' || $file[0] !== '/' || strpos($file, '://') !== false || is_link($file)
			|| !is_dir(dirname($file)) || !is_writable(dirname($file))
			|| (file_exists($file) && (!is_file($file) || !is_writable($file)))
			|| getDolGlobalString('SYSLOG_FILE_ONEPERSESSION') || defined('SYSLOG_FILE_ADDSUFFIX') || defined('SYSLOG_FILE_ADDIP')) { throw new RuntimeException('MSMCoreGuardedLoggerUnsupported'); }
	}
	public function requireBinding()
	{
		if (self::requireSession() !== $this->path || session_id() !== $this->session || session_status() !== PHP_SESSION_NONE) { throw new RuntimeException('MSMSessionFailure'); }
	}
	/** Independently read back the consumed run before admitting direct service DML. */
	public function requireProcessing($id, array $binding, $user, array $expected, array $options)
	{
		$this->requireBinding();
		if ($this->confirmedOrigin === null || isset($this->attempted[$id])) {
			throw new RuntimeException('MSMCoreGuardedSessionUnsupported');
		}
		// A real, separately closed file-session readback is required; in-memory
		// $_SESSION alone does not demonstrate durable controller consumption.
		set_error_handler(static function () { throw new RuntimeException('MSMSessionFailure'); });
		try {
			if (!session_start()) { throw new RuntimeException('MSMSessionFailure'); }
			$state = $_SESSION['msm_previews'][$this->key] ?? null;
			$confirmed = $expected;
			unset($confirmed['_profile']);
			if (!is_array($state) || ($state['phase'] ?? null) !== 'processing'
				|| !is_int($this->confirmedOrigin['revision'])
				|| ($state['revision'] ?? null) !== $this->confirmedOrigin['revision'] + 1
				|| ($state['ids'] ?? null) !== $this->confirmedOrigin['ids']
				|| ($state['rows'] ?? null) !== $this->confirmedOrigin['rows']
				|| ($state['options'] ?? null) !== $this->confirmedOrigin['options']
				|| ($state['profile'] ?? null) !== $this->confirmedOrigin['profile']
				|| ($state['owner'] ?? null) !== $this->confirmedOrigin['owner']
				|| ($state['entity'] ?? null) !== $this->confirmedOrigin['entity']
				|| !is_array($state['ids'] ?? null) || !in_array($id, $state['ids'], true)
				|| !isset($state['rows'][$id]) || $state['rows'][$id] !== $confirmed
				|| ($state['options'] ?? null) !== $options
				|| ($state['profile'] ?? null) !== $binding
				|| ($binding['profile'] ?? null) !== 'CoreGuarded'
				|| (int) ($state['owner'] ?? 0) !== (int) ($user->id ?? 0)
				|| (int) ($state['entity'] ?? 0) !== (int) ($binding['entity'] ?? 0)
				|| ($state['results'][$id]['status'] ?? null) !== 'unknown') {
				throw new RuntimeException('MSMCoreGuardedSessionUnsupported');
			}
		} finally {
			if (session_status() === PHP_SESSION_ACTIVE && !session_write_close()) {
				throw new RuntimeException('MSMSessionFailure');
			}
			restore_error_handler();
		}
		// Once admitted, even an unsuccessful Core BEGIN may have an unknown
		// acknowledgement. This in-process capability cannot be reused.
		$this->attempted[$id] = true;
	}
	public function __invoke(array $updated)
	{
		$this->requireBinding();
		// Session warnings are failures too; never retry a failed write/reopen.
		set_error_handler(static function () { throw new RuntimeException('MSMSessionFailure'); });
		try {
			if (!session_start()) { throw new RuntimeException('MSMSessionFailure'); }
			$current = $_SESSION['msm_previews'][$this->key] ?? null;
			if (!$current || $current['phase'] !== 'processing' || $current['revision'] !== $updated['revision']) { throw new RuntimeException('MSMSessionFailure'); }
			$_SESSION['msm_previews'][$this->key] = $updated;
			if (!session_write_close()) { throw new RuntimeException('MSMSessionFailure'); }
		} finally {
			if (session_status() === PHP_SESSION_ACTIVE) { session_abort(); }
			restore_error_handler();
		}
	}
}
