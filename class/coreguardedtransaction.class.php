<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
require_once __DIR__.'/databasestate.class.php';

/** The SET was refused before Core BEGIN; no transaction of ours may be rolled back. */
class MsmCoreGuardedEntryRejected extends RuntimeException {}

/** Bounded MySQL/Core transaction admission and acknowledged end classification.
 *
 * This class owns NO SQL transaction: the booking service remains the single outer
 * owner and calls Dolibarr's begin/commit/rollback. The accepted ADR-12 blind spots
 * are not represented as physical identity or Performance Schema evidence.
 */
class MsmCoreGuardedTransaction
{
	private $db;
	private $handle;
	private $thread;
	private $ready = false;
	private $owning = false;
	private $uncertain = false;
	private $attempted = false;
	private $completion = 'UNKNOWN';

	public function __construct($db) { $this->db = $db; }
	public function isFor($db) { return $this->db === $db; }
	protected function nativeHandle()
	{
		if (($this->db->type ?? null) !== 'mysqli' || !(($this->db->db ?? null) instanceof mysqli)) {
			throw new RuntimeException('MSMDatabaseUnsupported');
		}
		return $this->db->db;
	}
	protected function nativeThread($handle) { return (string) $handle->thread_id; }
	private function depth($expected)
	{
		return isset($this->db->transaction_opened) && is_numeric($this->db->transaction_opened)
			&& (string) $this->db->transaction_opened === (string) $expected;
	}
	private function sameConnection()
	{
		try {
			$handle = $this->nativeHandle();
			$thread = $this->nativeThread($handle);
			return $this->handle === $handle && $thread !== '' && $thread !== '0' && $this->thread === $thread;
		} catch (Throwable $e) { return false; }
	}
	private function row($sql)
	{
		$res = $this->db->query($sql);
		if (!$res) { throw new RuntimeException('MSMDatabaseObservationUnavailable'); }
		try { return $this->db->fetch_object($res); }
		finally { $this->db->free($res); }
	}
	/** Unknown completion values are NOT excluded; they only prohibit continuation. */
	private static function mode($value)
	{
		if ($value === '0' || $value === 0 || $value === 'NO_CHAIN') { return 'NO_CHAIN'; }
		if ($value === '1' || $value === 1 || $value === 'CHAIN') { return 'CHAIN'; }
		if ($value === '2' || $value === 2 || $value === 'RELEASE') { return 'RELEASE'; }
		return 'UNKNOWN';
	}
	private function result($state, $fresh, $reason = '')
	{
		return array('state' => $state, 'fresh' => $fresh,
			'owned' => $this->owning && !$this->uncertain, 'reason' => $reason,
			'family' => 'mysql', 'isolation' => 'REPEATABLE READ',
			'continuity' => 'core_bounded', 'completion_mode' => $this->completion);
	}
	private function unknown($reason = 'MSMTransactionUnknown')
	{
		$this->uncertain = true; $this->ready = false;
		return $this->result(MsmDatabaseState::UNKNOWN, false, $reason);
	}
	/** All SQL-based preflight precedes the final SET/BEGIN pair. */
	public function beforeBegin()
	{
		if ($this->owning || $this->uncertain || !$this->depth(0)) { return $this->unknown(); }
		$this->ready = false;
		try {
			$handle = $this->nativeHandle(); $thread = $this->nativeThread($handle);
			if ($thread === '' || $thread === '0' || ($this->handle !== null && ($this->handle !== $handle || $this->thread !== $thread))) {
				return $this->unknown();
			}
			$row = $this->row('SELECT CONNECTION_ID() AS cid, @@SESSION.autocommit AS ac, @@SESSION.completion_type AS completion');
			if (!$row || (string) ($row->cid ?? '') !== $thread || (string) ($row->ac ?? '') !== '1') {
				return $this->unknown('MSMTransactionUnsupported');
			}
			$this->handle = $handle; $this->thread = $thread;
			$this->completion = self::mode($row->completion ?? null);
			$this->ready = true;
			return $this->result(MsmDatabaseState::IDLE, true);
		} catch (Throwable $e) { return $this->unknown('MSMDatabaseObservationUnavailable'); }
	}
	public function beginAttempted() { return $this->attempted; }
	/** D-01: no intervening SQL or connection change between successful SET and Core BEGIN. */
	public function begin()
	{
		if (!$this->ready || $this->uncertain || !$this->depth(0) || !$this->sameConnection()) { $this->unknown(); throw new RuntimeException('MSMTransactionUnknown'); }
		$this->ready = false;
		try {
			$set = $this->db->query('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
			if (!$set) {
				$code = method_exists($this->db, 'lasterrno') ? (int) $this->db->lasterrno() : 0;
				if ($code === 1568) { throw new MsmCoreGuardedEntryRejected('MSMTransactionUnsupported'); }
				throw new RuntimeException('MSMTransactionUnknown');
			}
		} catch (Throwable $e) {
			// 1568 is the bounded foreign-transaction refusal. Any other SQL/transport
			// failure is uncertain and never authorizes a rollback or a BEGIN.
			$errno = (int) $e->getCode();
			if ($errno === 1568 || $e instanceof MsmCoreGuardedEntryRejected) {
				throw new MsmCoreGuardedEntryRejected('MSMTransactionUnsupported');
			}
			$this->unknown(); throw new RuntimeException('MSMTransactionUnknown');
		}
		if (!$this->sameConnection()) { $this->unknown(); throw new RuntimeException('MSMTransactionUnknown'); }
		$this->attempted = true;
		try { $ack = $this->db->begin(); }
		catch (Throwable $e) { $this->unknown(); throw new RuntimeException('MSMTransactionUnknown'); }
		if ($ack !== 1 && $ack !== true) { $this->unknown(); throw new RuntimeException('MSMTransactionUnknown'); }
		$this->owning = true;
		if (!$this->depth(1) || !$this->sameConnection()) { $this->unknown(); throw new RuntimeException('MSMTransactionUnknown'); }
		return true;
	}
	public function afterBegin() { return $this->active(); }
	public function active()
	{
		if (!$this->owning || $this->uncertain || !$this->depth(1) || !$this->sameConnection()) { return $this->unknown(); }
		return $this->result(MsmDatabaseState::ACTIVE, true);
	}
	public function requireSupportedIsolation(array $state)
	{
		if (($state['state'] ?? null) !== MsmDatabaseState::ACTIVE || !$state['fresh']) { throw new RuntimeException('MSMTransactionUnknown'); }
		// One-shot SET RR is the bounded isolation policy; no physical-event claim.
	}
	public function rollbackContext() { return $this->active(); }
	/** Second read immediately before either acknowledged Core end; never after end. */
	public function beforeCompletion()
	{
		if ($this->active()['state'] !== MsmDatabaseState::ACTIVE) { throw new RuntimeException('MSMTransactionUnknown'); }
		try {
			$row = $this->row('SELECT @@SESSION.completion_type AS completion');
			if (!$row || self::mode($row->completion ?? null) !== $this->completion || !$this->sameConnection() || !$this->depth(1)) {
				$this->unknown(); throw new RuntimeException('MSMTransactionUnknown');
			}
		} catch (Throwable $e) { $this->unknown(); throw new RuntimeException('MSMTransactionUnknown'); }
	}
	/** Never query or re-BEGIN after an acknowledged end, especially CHAIN/RELEASE. */
	public function completed($operation, $acknowledged)
	{
		if (!$this->owning || $this->uncertain || !in_array($operation, array('COMMIT', 'ROLLBACK'), true)
			|| ($acknowledged !== 1 && $acknowledged !== true) || !$this->depth(0)) { return $this->unknown(); }
		// RELEASE may already have closed the native connection after a valid ack.
		if ($this->completion !== 'RELEASE' && !$this->sameConnection()) { return $this->unknown(); }
		$this->owning = false;
		return $this->result(MsmDatabaseState::IDLE, true);
	}
	public function nonterminalAcknowledged()
	{
		return !$this->uncertain && !$this->owning && $this->completion === 'NO_CHAIN' && $this->sameConnection() && $this->depth(0);
	}
}
