<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
require_once __DIR__.'/databasestate.class.php';
/** Configuration admission, not physical transaction observation or control. */
class MsmTransactionProfile
{
	const SETTING = 'MEMBERSUBSCRIPTIONMASSACTION_TRANSACTION_PROFILE';
	private $db;
	private $observer;
	private $entity;
	public function __construct($db, MsmDatabaseState $observer, $entity)
	{
		if (!$observer->isFor($db) || !is_int($entity) || $entity < 1) { throw new RuntimeException('MSMProfileUnavailable'); }
		$this->db = $db; $this->observer = $observer; $this->entity = $entity;
	}
	/** Always read the authoritative entity row; never use cached conf globals. */
	protected function read()
	{
		$r = $this->db->query('SELECT '.$this->db->decrypt('value').' AS value FROM '.$this->db->prefix().'const WHERE name = '.$this->db->encrypt(self::SETTING).' AND entity = '.$this->entity);
		if (!$r) { throw new RuntimeException('MSMProfileUnavailable'); }
		try {
			$row = $this->db->fetch_object($r);
			if ($this->db->fetch_object($r)) { throw new RuntimeException('MSMProfileUnavailable'); }
			return $row ? (string) $row->value : null;
		} finally { $this->db->free($r); }
	}
	private static function revision($raw) { return hash('sha256', $raw === null ? 'missing' : 'configured:'.$raw); }
	public static function choices($family)
	{
		if ($family === 'mysql') { return array('CoreGuarded', 'EnhancedMySQL'); }
		if ($family === 'mariadb') { return array('MariaDBSnapshot'); }
		throw new RuntimeException('MSMDatabaseUnsupported');
	}
	/** Missing MySQL history is ambiguous, including fresh activation/reactivation. */
	public function current()
	{
		$family = $this->observer->family(); $raw = $this->read();
		$profile = ''; $reason = 'MSMProfileSelectionRequired';
		if ($raw === null && $family === 'mariadb') { $profile = 'MariaDBSnapshot'; $reason = ''; }
		elseif ($raw !== null) {
			$c = json_decode($raw, true);
			if (is_array($c) && count($c) === 4 && ($c['version'] ?? null) === 1 && ($c['family'] ?? null) === $family
				&& in_array($c['profile'] ?? null, self::choices($family), true)
				&& is_string($c['nonce'] ?? null) && preg_match('/^[a-f0-9]{32}$/D', $c['nonce'])) {
				$profile = $c['profile']; $reason = '';
			} else { $reason = 'MSMProfileInvalid'; }
		}
		$enabled = in_array($profile, array('EnhancedMySQL', 'MariaDBSnapshot'), true);
		if ($profile === 'CoreGuarded') { $reason = 'MSMCoreGuardedPending'; }
		return array('family' => $family, 'profile' => $profile, 'revision' => self::revision($raw),
			'features' => array('booking' => $enabled, 'invoice' => $enabled, 'autocreate' => $enabled, 'sendmail' => $enabled), 'reason' => $reason);
	}
	public function binding(array $current, $user, $revision)
	{
		if ((int) ($user->id ?? 0) < 1 || !empty($user->socid) || !is_int($revision) || $revision < 0) { throw new RuntimeException('MSMProfileChanged'); }
		return array('family' => $current['family'], 'profile' => $current['profile'], 'revision' => $current['revision'],
			'features' => $current['features'], 'owner' => (int) $user->id, 'entity' => $this->entity, 'confirmation_revision' => $revision);
	}
	/** Linearization point: fresh SELECT before each member's BEGIN. An admitted
	 * member keeps this profile; a later admin change affects subsequent members.
	 */
	public function admit($binding, $user, array $options, $revision = null)
	{
		$c = $this->current();
		if (!is_array($binding) || !is_int($binding['confirmation_revision'] ?? null)
			|| ($revision !== null && $binding['confirmation_revision'] !== $revision)
			|| $binding !== $this->binding($c, $user, $binding['confirmation_revision'])) { throw new RuntimeException('MSMProfileChanged'); }
		if ($c['reason']) { throw new RuntimeException($c['reason']); }
		foreach (array('invoice', 'autocreate', 'sendmail') as $feature) {
			if (!empty($options[$feature]) && !$c['features'][$feature]) { throw new RuntimeException('MSMProfileFeatureUnavailable'); }
		}
		return $c;
	}
	/** Single-row CAS in Core's configuration table, not DELETE+INSERT. No new
	 * table/journal; unique(name,entity) makes competing first writes fail closed.
	 */
	protected function compareAndSet($raw, $next)
	{
		$name = $this->db->encrypt(self::SETTING); $value = $this->db->encrypt($next);
		if ($raw === null) {
			$sql = 'INSERT INTO '.$this->db->prefix()."const(name,value,type,visible,note,entity) VALUES (".$name.','.$value.", 'chaine', 0, 'Transaction profile and revision', ".$this->entity.')';
		} else {
			$sql = 'UPDATE '.$this->db->prefix().'const SET value = '.$value.' WHERE name = '.$name.' AND entity = '.$this->entity.' AND BINARY value = BINARY '.$this->db->encrypt($raw);
		}
		try { $r = $this->db->query($sql); }
		catch (Throwable $e) { throw new RuntimeException('MSMProfileSaveConflict'); }
		if (!$r || $this->db->affected_rows($r) !== 1) { throw new RuntimeException('MSMProfileSaveConflict'); }
	}
	public function configure(array $post, array $session, $user, $method)
	{
		if (empty($user->admin) || empty($user->id) || !empty($user->socid)) { throw new RuntimeException('NotEnoughPermissions'); }
		if ($method !== 'POST' || !msmValidCsrf($post['token'] ?? null, $session)
			|| array_diff(array_keys($post), array('token', 'action', 'profile', 'revision')) || ($post['action'] ?? null) !== 'save') { throw new RuntimeException('MSMInvalidForm'); }
		if ((int) $this->db->transaction_opened !== 0) { throw new RuntimeException('MSMProfileUnavailable'); }
		// CAS persistence assumes the ordinary admin path, without a foreign explicit tx.
		// Core depth 0 and autocommit=1 alone do not prove physical transaction absence.
		$r = $this->db->query('SELECT @@autocommit AS enabled');
		if (!$r) { throw new RuntimeException('MSMProfileUnavailable'); }
		try { $mode = $this->db->fetch_object($r); }
		finally { $this->db->free($r); }
		if (!$mode || (string) $mode->enabled !== '1') { throw new RuntimeException('MSMProfileUnavailable'); }
		$family = $this->observer->family();
		if (!is_string($post['profile'] ?? null) || !in_array($post['profile'], self::choices($family), true)) { throw new RuntimeException('MSMProfileInvalid'); }
		$raw = $this->read();
		if (!is_string($post['revision'] ?? null) || !hash_equals(self::revision($raw), $post['revision'])) { throw new RuntimeException('MSMProfileSaveConflict'); }
		$next = json_encode(array('version' => 1, 'family' => $family, 'profile' => $post['profile'], 'nonce' => bin2hex(random_bytes(16))));
		$this->compareAndSet($raw, $next);
	}
}
