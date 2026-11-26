<?php
/* Copyright (C) 2026 iLLixM
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
	private $financeAttempted = array();
	private $postEffects = array();
	private $mailEvidenceClaimed = array();
	private $mailRecipients = array(); // Original fresh Booking recipient; never persisted or re-created.
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
		// PHP reopens the bound session file without listing the directory.
		if (session_module_name() !== 'files' || session_id() === '' || strpos($path, ';') !== false
			|| strpos($path, '://') !== false || $path === '' || $path[0] !== '/' || !is_dir($path)
			|| !is_writable($path) || realpath($path) === false) { throw new RuntimeException('MSMCoreGuardedSessionUnsupported'); }
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
	/** F38-03: attach the freshly fetched member identity at Booking's
	 * verified-preview boundary, before any business DML. Never accept a
	 * caller-supplied confirmation row as the trusted recipient itself.
	 * An empty email may still be captured (the mail adapter returns missing).
	 */
	public function bindMailRecipient($id, $member, array $verifiedRow)
	{
		$this->requireBinding();
		$origin = $this->confirmedOrigin;
		if (!is_int($id) || $id < 1 || !isset($this->attempted[$id])
			|| isset($this->mailRecipients[$id]) || !is_array($origin)
			|| empty($origin['options']['sendmail'])
			|| !in_array($id, $origin['ids'] ?? array(), true)
			|| !is_object($member) || (int) ($member->id ?? 0) !== $id
			|| (int) ($member->entity ?? 0) !== (int) ($origin['entity'] ?? 0)
			|| !is_array($origin['rows'][$id] ?? null)
			|| !is_string($verifiedRow['fingerprint'] ?? null)
			|| !is_string($origin['rows'][$id]['fingerprint'] ?? null)
			|| !hash_equals($origin['rows'][$id]['fingerprint'], $verifiedRow['fingerprint'])) {
			throw new RuntimeException('MSMCoreGuardedSessionUnsupported');
		}
		$this->mailRecipients[$id] = array(
			'email' => (string) ($member->email ?? ''),
			'entity' => (int) $member->entity,
			'typeid' => (int) ($member->typeid ?? 0));
	}
	/** Compare exact persisted confirmation while excluding the internal
	 * profile carrier injected by the Booking orchestrator only.
	 */
	private function mailConfirmedRowMismatch(array $row, array $confirmed)
	{
		unset($row['_profile']);
		return $row !== $confirmed;
	}
	/** No SQL and no new permission grant: the originally consumed, in-process
	 * capability is the only possible CoreGuarded Invoice continuation.
	 */
	public function requireFinanceContinuation($id, array $row, $user)
	{
		$this->requireBinding();
		$origin = $this->confirmedOrigin;
		if (!isset($this->attempted[$id]) || isset($this->financeAttempted[$id])
			|| !is_array($origin) || empty($origin['options']['invoice'])
			|| ($origin['profile']['profile'] ?? null) !== 'CoreGuarded'
			|| (int) ($origin['owner'] ?? 0) !== (int) ($user->id ?? 0)
			|| !isset($origin['rows'][$id]['invoice'])
			// F38-10: fingerprint equality alone does not bind the actual invoice
			// parameters supplied to a direct service caller.
			|| !is_array($origin['rows'][$id] ?? null)
			|| (array_key_exists('_profile', $row) && $row['_profile'] !== $origin['profile'])
			|| $this->mailConfirmedRowMismatch($row, $origin['rows'][$id])
			|| !is_string($origin['rows'][$id]['fingerprint'] ?? null)
			|| !hash_equals($origin['rows'][$id]['fingerprint'], (string) ($row['fingerprint'] ?? ''))
			|| empty($row['invoice'])
			|| !empty($row['autocreate']) !== !empty($origin['options']['autocreate'])) {
			throw new RuntimeException('MSMCoreGuardedSessionUnsupported');
		}
		$this->financeAttempted[$id] = true; // Never another Invoice attempt.
	}
	/** Independently read the last durable one-shot result before a post-COMMIT
	 * effect. Never reopen a consumed run to replay a PDF or SMTP operation.
	 */
	public function requirePostCommitEffect($id, $kind)
	{
		$this->requireBinding();
		if (!in_array($kind, array('pdf', 'mail'), true)
			|| !isset($this->attempted[$id]) || !is_array($this->confirmedOrigin)
			|| isset($this->postEffects[$id][$kind])) {
			throw new RuntimeException('MSMSessionFailure');
		}
		$origin = $this->confirmedOrigin;
		set_error_handler(static function () { throw new RuntimeException('MSMSessionFailure'); });
		try {
			if (!session_start()) { throw new RuntimeException('MSMSessionFailure'); }
			$state = $_SESSION['msm_previews'][$this->key] ?? null;
			$result = is_array($state) ? ($state['results'][$id] ?? null) : null;
			$invoice = is_array($result) ? ($result['invoice'] ?? null) : null;
			if (!is_array($state) || ($state['phase'] ?? null) !== 'processing'
				|| ($state['revision'] ?? null) !== $origin['revision'] + 1
				|| ($state['profile'] ?? null) !== $origin['profile']
				|| ($state['owner'] ?? null) !== $origin['owner']
				|| ($state['entity'] ?? null) !== $origin['entity']
				|| ($state['options'] ?? null) !== $origin['options']
				|| !is_array($result) || ($result['status'] ?? null) !== 'success'
				|| ($result['completion'] ?? null) !== 'nonterminal_acknowledged'
				|| !empty($result['stop'])
				|| ($kind === 'pdf' && (empty($origin['options']['invoice'])
					|| !is_array($invoice) || (int) ($invoice['id'] ?? 0) < 1
					|| ($invoice['pdf']['status'] ?? null) !== 'unknown'))
				|| ($kind === 'mail' && (empty($origin['options']['sendmail'])
					|| ($result['mail'] ?? null) !== 'unknown'
					|| (!empty($origin['options']['invoice']) && (!is_array($invoice)
						|| ($invoice['pdf']['status'] ?? null) !== 'verified'))))) {
				throw new RuntimeException('MSMSessionFailure');
			}
		} finally {
			if (session_status() === PHP_SESSION_ACTIVE && !session_write_close()) {
				throw new RuntimeException('MSMSessionFailure');
			}
			restore_error_handler();
		}
		// Bind the post-effect authorization to the exact durable invoice
		// descriptor, not merely the member number and a claimed status.
		$this->postEffects[$id][$kind] = $kind === 'mail' ? array('invoice'=>$invoice) : true;
	}
	/** One-shot continuation for the actual mail adapter, not merely a
	 * boolean asserted by its caller. requirePostCommitEffect('mail') must
	 * already have re-read the persisted original confirmed result.
	 * This proof never writes a DB row or opens another transaction.
	 */
	public function requireMailEvidence($id, $user, array $invoice = null, array $current = null,
		$member = null, array $row = array())
	{
		$this->requireBinding();
		$origin = $this->confirmedOrigin;
		if (!is_int($id) || $id < 1 || !is_array($origin)
			|| !isset($this->postEffects[$id]['mail'])
			|| ($this->postEffects[$id]['mail']['invoice'] ?? null) !== $invoice
			|| isset($this->mailEvidenceClaimed[$id])
			|| !isset($this->attempted[$id])
			|| !in_array($id, $origin['ids'] ?? array(), true)
			|| (int) ($origin['owner'] ?? 0) !== (int) ($user->id ?? 0)
			|| ($origin['profile']['profile'] ?? null) !== 'CoreGuarded'
			|| !is_array($current) || ($current['profile'] ?? null) !== 'CoreGuarded'
			|| ($current['family'] ?? null) !== ($origin['profile']['family'] ?? null)
			|| ($current['revision'] ?? null) !== ($origin['profile']['revision'] ?? null)
			|| ($current['features'] ?? null) !== ($origin['profile']['features'] ?? null)
			|| !empty($current['reason'])
			|| empty($origin['profile']['features']['sendmail'])
			|| empty($origin['options']['sendmail'])
			|| !is_array($origin['rows'][$id] ?? null)
			// The effect is bound to the *actual* original email recipient
			// and exact server-confirmed fee/period/description/fingerprint.
			|| !is_array($this->mailRecipients[$id] ?? null)
			|| !is_object($member) || (int) ($member->id ?? 0) !== $id
			|| (int) ($member->entity ?? 0) !== $this->mailRecipients[$id]['entity']
			|| (int) ($member->typeid ?? 0) !== $this->mailRecipients[$id]['typeid']
			|| (string) ($member->email ?? '') !== $this->mailRecipients[$id]['email']
			|| !isset($row['_profile']) || $row['_profile'] !== $origin['profile']
			|| $this->mailConfirmedRowMismatch($row, $origin['rows'][$id])
			|| (bool) !empty($origin['options']['invoice']) !== ($invoice !== null)
			|| ($invoice !== null && ((int) ($invoice['member_id'] ?? 0) !== $id
				|| (int) ($invoice['entity'] ?? 0) !== (int) ($origin['entity'] ?? 0)
				|| (int) ($invoice['id'] ?? 0) < 1
				|| ($invoice['pdf']['status'] ?? null) !== 'verified'))) {
			throw new RuntimeException('MSMCoreGuardedSessionUnsupported');
		}
		$this->mailEvidenceClaimed[$id] = true; // No second adapter attempt.
	}
	public function __invoke(array $updated, $isolatedMailId = null)
	{
		$this->requireBinding();
		// Session warnings are failures too; never retry a failed write/reopen.
		set_error_handler(static function () { throw new RuntimeException('MSMSessionFailure'); });
		try {
			if (!session_start()) { throw new RuntimeException('MSMSessionFailure'); }
			$current = $_SESSION['msm_previews'][$this->key] ?? null;
			if (!$current || $current['phase'] !== 'processing' || $current['revision'] !== $updated['revision']) { throw new RuntimeException('MSMSessionFailure'); }
			if ($isolatedMailId !== null) {
				$origin = $this->confirmedOrigin;
				$result = $updated['results'][$isolatedMailId] ?? null;
				$previous = $current['results'][$isolatedMailId] ?? array();
				$attachment = empty($origin['options']['invoice']) ? 'not_required' : 'unknown';
				if (!isset($this->mailEvidenceClaimed[$isolatedMailId]) || !is_array($origin)
					|| $updated['phase'] !== 'processing' || $updated['revision'] !== $origin['revision'] + 1
					|| ($previous['mail'] ?? null) !== 'unknown' || !is_array($result)
					|| ($result['status'] ?? null) !== 'success'
					|| ($result['completion'] ?? null) !== 'nonterminal_acknowledged'
					|| !empty($result['stop']) || ($result['mail'] ?? null) !== 'unknown'
					|| ($result['attachment_status'] ?? null) !== $attachment
					|| array_replace($previous, array('attachment_status' => $attachment)) !== $result) {
					throw new RuntimeException('MSMSessionFailure');
				}
				foreach (array('owner', 'entity', 'ids', 'rows', 'options', 'profile') as $key) {
					if (($current[$key] ?? null) !== $origin[$key] || ($updated[$key] ?? null) !== $origin[$key]) {
						throw new RuntimeException('MSMSessionFailure');
					}
				}
			}
			$_SESSION['msm_previews'][$this->key] = $updated;
			if (!session_write_close()) { throw new RuntimeException('MSMSessionFailure'); }
			if ($isolatedMailId !== null) {
				// A successful write return alone cannot authorize the next member.
				$this->requireBinding();
				if (!session_start() || ($_SESSION['msm_previews'][$this->key] ?? null) !== $updated) {
					throw new RuntimeException('MSMSessionFailure');
				}
				if (!session_abort()) { throw new RuntimeException('MSMSessionFailure'); }
			}
		} finally {
			if (session_status() === PHP_SESSION_ACTIVE) { session_abort(); }
			restore_error_handler();
		}
	}
}
