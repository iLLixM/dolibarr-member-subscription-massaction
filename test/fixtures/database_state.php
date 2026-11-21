<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
require_once __DIR__.'/../../class/databasestate.class.php';
/** Synthetic connection/event model. No transport, credentials or real SQL execution. */
trait DatabaseStateModel
{
	public $family = 'mariadb'; public $native;
	public $serverUuid = 'synthetic-server'; public $threadId = '81'; public $autocommit = '1'; public $performanceSchema = '1';
	public $instrumented = true; public $currentInstrumented = true; public $psDenied = false; public $lost = false; public $membersPresent = true;
	public $event = null; public $eventSequence = '100'; public $probeFault = ''; public $markers = array();
	public $versionOverride; public $commentOverride;
	public $isolation = 'REPEATABLE READ'; public $nextIsolation = null;
	public function observationInit() { $this->native = (object) array('thread_id' => '51'); }
	public static function incrementDecimal($value)
	{
		for ($i = strlen($value) - 1; $i >= 0; $i--) {
			if ($value[$i] !== '9') { $value[$i] = chr(ord($value[$i]) + 1); return $value; }
			$value[$i] = '0';
		}
		return '1'.$value;
	}
	public function modelEvent($state)
	{
		$this->eventSequence = self::incrementDecimal($this->eventSequence);
		$this->event = (object) array('THREAD_ID' => $this->threadId, 'EVENT_ID' => $this->eventSequence,
			'STATE' => $state, 'END_EVENT_ID' => $state === 'ACTIVE' ? null : self::incrementDecimal($this->eventSequence),
			'NUMBER_OF_SAVEPOINTS' => '0', 'NUMBER_OF_RELEASE_SAVEPOINT' => '0', 'ISOLATION_LEVEL' => $this->isolation);
	}
	public function modelBegin() { $this->currentInstrumented = $this->instrumented; if ($this->family === 'mysql' && $this->instrumented) { $this->modelEvent('ACTIVE'); if ($this->nextIsolation !== null) { $this->event->ISOLATION_LEVEL = $this->nextIsolation; } } $this->nextIsolation = null; }
	public function modelEnd($state)
	{
		$this->markers = array();
		if ($this->family === 'mysql' && $this->event && $this->event->STATE === 'ACTIVE') {
			$this->event->STATE = $state; $this->event->END_EVENT_ID = self::incrementDecimal($this->event->EVENT_ID);
		}
	}
	/** Return null only for non-observer SQL; unknown queries remain hard failures. */
	public function observationQuery($sql)
	{
		if ($this->lost) { throw new RuntimeException('Synthetic connection lost', 2006); }
		$row = null;
		if ($sql === 'SELECT VERSION() AS version, @@version_comment AS version_comment, CONNECTION_ID() AS connection_id') {
			$row = (object) array('version' => $this->versionOverride ?? ($this->family === 'mysql' ? '8.4.13' : '10.11.19-MariaDB'),
				'version_comment' => $this->commentOverride ?? ($this->family === 'mysql' ? 'MySQL Community Server - GPL' : 'Debian 12'), 'connection_id' => $this->native->thread_id);
		} elseif ($sql === 'SELECT CONNECTION_ID() AS cid, PS_CURRENT_THREAD_ID() AS tid, @@server_uuid AS uuid, @@autocommit AS ac, @@performance_schema AS ps') {
			$row = (object) array('cid' => $this->native->thread_id, 'tid' => $this->threadId, 'uuid' => $this->serverUuid, 'ac' => $this->autocommit, 'ps' => $this->performanceSchema);
		} elseif ($sql === 'SELECT THREAD_ID, EVENT_ID, STATE, END_EVENT_ID, NUMBER_OF_SAVEPOINTS, NUMBER_OF_RELEASE_SAVEPOINT, ISOLATION_LEVEL FROM performance_schema.events_transactions_current WHERE THREAD_ID = PS_CURRENT_THREAD_ID()') {
			if ($this->psDenied) { throw new RuntimeException('Synthetic SELECT denied', 1142); }
			$row = $this->event ? clone $this->event : null;
		} elseif ($sql === 'SELECT @@in_transaction AS active') { $row = (object) array('active' => $this->active ? '1' : '0');
		} elseif ($sql === 'SELECT rowid FROM '.$this->prefix().'adherent ORDER BY rowid'.$this->plimit(1)) {
			if ($this->membersPresent) { $row = (object) array('rowid' => '1'); }
			if ($this->instrumented && !$this->active && $this->autocommit === '1') { $this->modelEvent('COMMITTED'); }
		} elseif (preg_match('/^SAVEPOINT (msm_[a-f0-9]{32})$/D', $sql, $m)) {
			if ($this->probeFault === 'save-error') { throw new RuntimeException('Synthetic SAVEPOINT error', 1064); }
			if (!$this->active) { throw new RuntimeException('Synthetic missing transaction'); }
			$this->markers[$m[1]] = true;
			if ($this->currentInstrumented && $this->event && $this->event->STATE === 'ACTIVE' && $this->probeFault !== 'stale-save') {
				$this->event->NUMBER_OF_SAVEPOINTS = self::incrementDecimal($this->event->NUMBER_OF_SAVEPOINTS);
			}
			if ($this->probeFault === 'replace-on-save') { $this->modelEvent('ACTIVE'); }
			return (object) array('rows' => array());
		} elseif (preg_match('/^RELEASE SAVEPOINT (msm_[a-f0-9]{32})$/D', $sql, $m)) {
			if ($this->probeFault === 'release-error' || !isset($this->markers[$m[1]])) { throw new RuntimeException('Synthetic RELEASE error', 1305); }
			if ($this->probeFault !== 'omit-release') { unset($this->markers[$m[1]]); }
			if ($this->currentInstrumented && $this->event && $this->event->STATE === 'ACTIVE' && !in_array($this->probeFault, array('stale-release', 'omit-release'), true)) {
				$this->event->NUMBER_OF_RELEASE_SAVEPOINT = self::incrementDecimal($this->event->NUMBER_OF_RELEASE_SAVEPOINT);
			}
			return (object) array('rows' => array());
		} else { return null; }
		return (object) array('rows' => $row ? array($row) : array());
	}
}
class SyntheticDatabaseState extends MsmDatabaseState
{
	protected function nativeHandle()
	{
		if ($this->db->type !== 'mysqli') { throw new RuntimeException('MSMDatabaseUnsupported'); }
		return $this->db->native;
	}
}
