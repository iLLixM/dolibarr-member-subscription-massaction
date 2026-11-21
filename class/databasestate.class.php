<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
require_once __DIR__.'/activetransactionguard.class.php';
/** Physical observation only. The caller owns DoliDB begin/commit/rollback.
 * MariaDB offers a status snapshot, NOT MySQL's event/checkpoint continuity.
 * Checkpoints are endpoint evidence, not continuous tracking or crash recovery.
 * Unknown ownership stays latched until this observer/connection is discarded.
 * FAILED (open, rollback-capable) and BUSY are reserved backend states; no PgSQL backend.
 */
class MsmDatabaseState implements MsmActiveTransactionGuard
{
	const IDLE = 'IDLE';
	const ACTIVE = 'ACTIVE';
	const UNKNOWN = 'UNKNOWN';
	const FAILED = 'FAILED';
	const BUSY = 'BUSY';
	protected $db;
	private $handle;
	private $connectionId;
	private $family;
	private $isolation;
	private $connection;
	private $token;
	private $idleEvent;
	private $ready = false;
	private $owning = false;
	private $uncertain = false;

	public function __construct($db) { $this->db = $db; }
	/** Injection must never attach another DoliDB object's observer to business writes. */
	public function isFor($db) { return $this->db === $db; }
	/** Narrow native seam for synthetic fixtures; production requires the actual mysqli. */
	protected function nativeHandle()
	{
		if (($this->db->type ?? '') !== 'mysqli' || !(($this->db->db ?? null) instanceof mysqli)) { throw new RuntimeException('MSMDatabaseUnsupported'); }
		return $this->db->db;
	}
	private function one($sql)
	{
		$r = $this->db->query($sql);
		if (!$r) { throw new RuntimeException('MSMDatabaseObservationUnavailable'); }
		try { return $this->db->fetch_object($r); }
		finally { $this->db->free($r); }
	}
	private function command($sql)
	{
		$r = $this->db->query($sql);
		if (!$r) { throw new RuntimeException('MSMDatabaseObservationUnavailable'); }
	}
	/** No floating point conversion, including BIGINT UNSIGNED counters. */
	private static function decimal($value)
	{
		if (is_int($value) && $value >= 0) { return (string) $value; }
		if (!is_string($value) || !preg_match('/^[0-9]+$/D', $value)) { throw new RuntimeException('MSMDatabaseObservationUnavailable'); }
		return ltrim($value, '0') ?: '0';
	}
	private static function greater($a, $b) { return strlen($a) > strlen($b) || (strlen($a) === strlen($b) && strcmp($a, $b) > 0); }
	private static function nextDecimal($value)
	{
		for ($i = strlen($value) - 1; $i >= 0; $i--) {
			if ($value[$i] !== '9') { $value[$i] = chr(ord($value[$i]) + 1); return $value; }
			$value[$i] = '0';
		}
		return '1'.$value;
	}
	private function depth($n) { return isset($this->db->transaction_opened) && self::decimal($this->db->transaction_opened) === (string) $n; }
	private function identify()
	{
		$handle = $this->nativeHandle();
		if ($this->handle !== null && $handle !== $this->handle) { throw new RuntimeException('MSMTransactionUnknown'); }
		$id = self::decimal($handle->thread_id);
		if ($this->connectionId !== null && $id !== $this->connectionId) { throw new RuntimeException('MSMTransactionUnknown'); }
		if ($this->family !== null) { return; }
		$r = $this->one('SELECT VERSION() AS version, @@version_comment AS version_comment, CONNECTION_ID() AS connection_id');
		if (!$r || self::decimal($r->connection_id ?? null) !== $id) { throw new RuntimeException('MSMDatabaseUnsupported'); }
		$version = (string) ($r->version ?? ''); $comment = (string) ($r->version_comment ?? '');
		if (stripos($version, 'MariaDB') !== false && stripos($comment, 'MySQL') === false) { $family = 'mariadb'; }
		elseif (preg_match('/^[0-9]+\.[0-9]+\.[0-9]+(?:-(?:commercial|debug|log))?$/D', $version) && preg_match('/^MySQL (?:Community|Enterprise) Server\b/', $comment)) { $family = 'mysql'; }
		else { throw new RuntimeException('MSMDatabaseUnsupported'); }
		$this->handle = $handle; $this->connectionId = $id; $this->family = $family;
	}
	/** Server-family discovery only; no physical transaction readiness/probe. */
	public function family() { $this->identify(); return $this->family; }
	private function result($state, $fresh, $reason = '')
	{
		return array('state' => $state, 'fresh' => $fresh, 'owned' => $this->owning && !$this->uncertain,
			'family' => $this->family, 'isolation' => $this->isolation, 'continuity' => $this->family === 'mysql' ? 'event_checkpoint' : 'snapshot',
			'connection' => array('id' => $this->connectionId, 'thread' => $this->connection[2] ?? null, 'server' => $this->connection[0] ?? null), 'transaction' => $this->token, 'reason' => $reason);
	}
	/** Business admission after a fresh owned observation, not an ownership rule.
	 * Other known isolation levels must still allow the existing safe rollback path.
	 * No SQL: the actual event value came from the same freshness observation.
	 */
	public function requireSupportedIsolation(array $state)
	{
		if (($state['family'] ?? null) === 'mysql' && ($state['isolation'] ?? null) !== 'REPEATABLE READ') { throw new RuntimeException('MSMTransactionUnsupported'); }
	}
	private function unknown($reason)
	{
		// Once ownership becomes uncertain no later snapshot may redeem that context.
		if ($this->owning) { $this->uncertain = true; }
		return $this->result(self::UNKNOWN, false, $reason);
	}
	private function failure($e)
	{
		$reason = in_array($e->getMessage(), array('MSMDatabaseUnsupported', 'MSMDatabaseBootstrapUnavailable', 'MSMTransactionUnsupported', 'MSMTransactionUnknown'), true) ? $e->getMessage() : 'MSMDatabaseObservationUnavailable';
		return $this->unknown($reason); // Never expose raw SQL, native errors or credentials.
	}
	/** Query only this connection; missing/partial data is never interpreted as idle. */
	private function snapshot()
	{
		$this->identify();
		$m = $this->one('SELECT CONNECTION_ID() AS cid, PS_CURRENT_THREAD_ID() AS tid, @@server_uuid AS uuid, @@autocommit AS ac, @@performance_schema AS ps');
		if (!$m || self::decimal($m->cid ?? null) !== $this->connectionId || self::decimal($m->ps ?? null) !== '1' || empty($m->uuid)) { throw new RuntimeException('MSMDatabaseObservationUnavailable'); }
		$key = array((string) $m->uuid, self::decimal($m->cid), self::decimal($m->tid ?? null));
		if ($this->connection !== null && $this->connection !== $key) { throw new RuntimeException('MSMTransactionUnknown'); }
		$this->connection = $key;
		$r = $this->one('SELECT THREAD_ID, EVENT_ID, STATE, END_EVENT_ID, NUMBER_OF_SAVEPOINTS, NUMBER_OF_RELEASE_SAVEPOINT, ISOLATION_LEVEL FROM performance_schema.events_transactions_current WHERE THREAD_ID = PS_CURRENT_THREAD_ID()');
		$s = array('event' => null, 'raw' => 'missing', 'autocommit' => self::decimal($m->ac ?? null));
		if (!$r) { return $s; }
		foreach (array('THREAD_ID', 'EVENT_ID', 'STATE', 'END_EVENT_ID', 'NUMBER_OF_SAVEPOINTS', 'NUMBER_OF_RELEASE_SAVEPOINT', 'ISOLATION_LEVEL') as $field) {
			if (!property_exists($r, $field)) { throw new RuntimeException('MSMDatabaseObservationUnavailable'); }
		}
		if (!in_array($r->ISOLATION_LEVEL, array('REPEATABLE READ', 'READ COMMITTED', 'READ UNCOMMITTED', 'SERIALIZABLE'), true)) { throw new RuntimeException('MSMDatabaseObservationUnavailable'); }
		$this->isolation = $r->ISOLATION_LEVEL;
		if (self::decimal($r->THREAD_ID) !== $key[2]) { throw new RuntimeException('MSMTransactionUnknown'); }
		$s['event'] = self::decimal($r->EVENT_ID); $s['save'] = self::decimal($r->NUMBER_OF_SAVEPOINTS); $s['release'] = self::decimal($r->NUMBER_OF_RELEASE_SAVEPOINT);
		if ($r->STATE === 'ACTIVE' && $r->END_EVENT_ID === null) { $s['raw'] = 'active'; }
		elseif (in_array($r->STATE, array('COMMITTED', 'ROLLED BACK'), true) && $r->END_EVENT_ID !== null) { self::decimal($r->END_EVENT_ID); $s['raw'] = $r->STATE; }
		else { throw new RuntimeException('MSMDatabaseObservationUnavailable'); }
		return $s;
	}
	private function mariaActive()
	{
		$this->identify(); $r = $this->one('SELECT @@in_transaction AS active');
		if (!$r || !in_array(self::decimal($r->active ?? null), array('0', '1'), true)) { throw new RuntimeException('MSMDatabaseObservationUnavailable'); }
		return self::decimal($r->active) === '1';
	}
	/** Keep engine policy local to the backend; no SQL or native handle in services. */
	public function requireTransactionalTables(array $tables)
	{
		$this->identify();
		foreach ($tables as $table) {
			$name = $this->db->prefix().$table;
			if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/D', $name)) { throw new RuntimeException('MSMTransactionUnsupported'); }
			$r = $this->one("SELECT ENGINE AS engine FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '".$this->db->escape($name)."'");
			if (!$r || strtoupper((string) $r->engine) !== 'INNODB') { throw new RuntimeException('MSMTransactionUnsupported'); }
		}
	}
	private function idle()
	{
		$this->identify();
		if ($this->family === 'mariadb') { return !$this->mariaActive(); }
		$a = $this->snapshot();
		if ($a['raw'] === 'active' || $a['autocommit'] !== '1') { return false; }
		$this->requireTransactionalTables(array('adherent'));
		// The selected real Core table must contain a row. Empty tables fail closed;
		// never seed business data or substitute optimizer-eliminated constant SQL.
		$r = $this->one('SELECT rowid FROM '.$this->db->prefix().'adherent ORDER BY rowid'.$this->db->plimit(1));
		if (!$r || !isset($r->rowid)) { throw new RuntimeException('MSMDatabaseBootstrapUnavailable'); }
		$b = $this->snapshot();
		if ($b['raw'] !== 'COMMITTED' || $b['autocommit'] !== '1' || ($a['event'] !== null && !self::greater($b['event'], $a['event']))) { return false; }
		$this->idleEvent = $b['event']; return true;
	}
	public function beforeBegin()
	{
		$this->ready = false;
		try {
			if ($this->owning || $this->uncertain || !$this->depth(0) || !$this->idle()) { return $this->unknown('MSMDatabaseObservationUnavailable'); }
			$this->ready = true;
			return $this->result(self::IDLE, true);
		} catch (Throwable $e) { return $this->failure($e); }
	}
	/** Call only after acknowledged DoliDB::begin. No native BEGIN is issued here. */
	public function afterBegin()
	{
		if (!$this->ready || $this->owning || $this->uncertain) { return $this->unknown('MSMTransactionUnknown'); }
		$this->ready = false; $this->owning = true;
		try {
			$this->identify();
			if (!$this->depth(1)) { return $this->unknown('MSMTransactionUnknown'); }
			if ($this->family === 'mysql') {
				$s = $this->snapshot();
				if ($this->idleEvent === null || $s['raw'] !== 'active' || !self::greater($s['event'], $this->idleEvent)) { return $this->unknown('MSMTransactionUnknown'); }
				$this->token = $s['event'];
			}
			return $this->active();
		} catch (Throwable $e) { return $this->failure($e); }
	}
	private function sameActive($s) { return $s['raw'] === 'active' && $s['autocommit'] === '1' && $this->token !== null && $s['event'] === $this->token; }
	public function active()
	{
		try {
			if (!$this->owning || $this->uncertain || !$this->depth(1)) { return $this->unknown('MSMTransactionUnknown'); }
			$this->identify();
			if ($this->family === 'mariadb') { return $this->mariaActive() ? $this->result(self::ACTIVE, true) : $this->unknown('MSMTransactionUnknown'); }
			$a = $this->snapshot();
			if (!$this->sameActive($a)) { return $this->unknown('MSMTransactionUnknown'); }
			$marker = 'msm_'.bin2hex(random_bytes(16));
			// On any probe failure stop; do not clean markers in an uncertain/replaced
			// transaction. Connection teardown, not business compensation, releases it.
			$this->command('SAVEPOINT '.$marker); $b = $this->snapshot();
			if (!$this->sameActive($b) || $b['save'] !== self::nextDecimal($a['save']) || $b['release'] !== $a['release']) { return $this->unknown('MSMTransactionUnknown'); }
			$this->command('RELEASE SAVEPOINT '.$marker); $c = $this->snapshot();
			if (!$this->sameActive($c) || $c['save'] !== $b['save'] || $c['release'] !== self::nextDecimal($b['release'])) { return $this->unknown('MSMTransactionUnknown'); }
			return $this->result(self::ACTIVE, true);
		} catch (Throwable $e) { return $this->failure($e); }
	}
	/** Future FAILED backends may return an owned/fresh failed-but-open context here. */
	public function rollbackContext() { return $this->active(); }
	/** Observe expected completion BEFORE idle bootstrap replaces the latest event. */
	public function completed($operation, $acknowledged)
	{
		try {
			if (!$acknowledged || !$this->owning || $this->uncertain || !$this->depth(0) || !in_array($operation, array('COMMIT', 'ROLLBACK'), true)) { return $this->unknown('MSMTransactionUnknown'); }
			$this->identify();
			if ($this->family === 'mysql') {
				$s = $this->snapshot(); $expected = $operation === 'COMMIT' ? 'COMMITTED' : 'ROLLED BACK';
				if ($s['event'] !== $this->token || $s['raw'] !== $expected) { return $this->unknown('MSMTransactionUnknown'); }
			}
			if (!$this->idle()) { return $this->unknown('MSMTransactionUnknown'); }
			$completedToken = $this->token;
			$this->owning = false; $this->token = null;
			$result = $this->result(self::IDLE, true);
			$result['completed_transaction'] = $completedToken; $result['completed_operation'] = $operation;
			return $result;
		} catch (Throwable $e) { return $this->failure($e); }
	}
}
