<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

require_once __DIR__.'/../class/corecompatibility.class.php';
require_once __DIR__.'/batch.lib.php';

/**
 * Check the module and existing core permissions; external users are excluded.
 * @param User $user Current user
 * @return bool
 */
function msmCanUse($user)
{
	try { MsmCoreCompatibility::current(); MsmCoreCompatibility::methods($user, array('hasRight' => 3)); } catch (RuntimeException $e) { return false; }
	if (!function_exists('isModEnabled') || !function_exists('getEntity') || !function_exists('restrictedArea')) { return false; }
	return is_callable(array($user, 'hasRight')) && !empty($user->id) && empty($user->socid)
		&& isModEnabled('membersubscriptionmassaction') && isModEnabled('member')
		&& $user->hasRight('adherent', 'lire') && $user->hasRight('adherent', 'cotisation', 'creer');
}

/**
 * Validate the uncoerced selection before any object access. Duplicates are rejected.
 * @param mixed $input Raw selection
 * @param int $limit Configured maximum
 * @return int[] Positive, unique IDs in selection order
 * @throws InvalidArgumentException
 */
function msmSelectionIds($input, $limit)
{
	if (!is_array($input) || !$input || $limit < 1 || count($input) > $limit) {
		throw new InvalidArgumentException('MSMInvalidSelection');
	}
	$ids = array();
	foreach ($input as $value) {
		if ((!is_string($value) && !is_int($value)) || !preg_match('/^[1-9][0-9]*$/D', (string) $value)
			|| strlen((string) $value) > strlen((string) PHP_INT_MAX)
			|| (strlen((string) $value) === strlen((string) PHP_INT_MAX) && strcmp((string) $value, (string) PHP_INT_MAX) > 0)) {
			throw new InvalidArgumentException('MSMInvalidSelection');
		}
		$id = (int) $value;
		if (isset($ids[$id])) {
			throw new InvalidArgumentException('MSMInvalidSelection');
		}
		$ids[$id] = $id;
	}
	return array_values($ids);
}

/**
 * Check every selected object without disclosing which object was inaccessible.
 * @param int[] $ids Validated IDs
 * @param callable $canAccess Object access check
 * @return void
 * @throws RuntimeException
 */
function msmCheckAccess(array $ids, callable $canAccess)
{
	foreach ($ids as $id) {
		if (!$canAccess($id)) {
			throw new RuntimeException('MSMSelectionUnavailable');
		}
	}
}

/**
 * Reuse core object loading and access checks, including shared entity rules.
 * @param DoliDB $db Database handler
 * @param User $user Current user
 * @param int[] $ids Validated IDs
 * @return void
 */
function msmCheckMembers($db, $user, array $ids)
{
	MsmCoreCompatibility::load('adherents/class/adherent.class.php', 'Adherent', array('fetch' => 6));
	$entities = array_map('intval', explode(',', getEntity('adherent')));
	msmCheckAccess($ids, static function ($id) use ($db, $user, $entities) {
		$member = new Adherent($db);
		return $member->fetch($id, '', 0, '', false, false) > 0
			&& in_array((int) $member->entity, $entities, true)
			&& restrictedArea($user, 'adherent', $id, '', '', 'socid', 'rowid', 0, 1) > 0;
	});
}

/**
 * Require the existing session CSRF token, even when global token checks are disabled.
 * @param mixed $token Supplied POST token
 * @param array $session Current session
 * @return bool
 */
function msmValidCsrf($token, array $session)
{
	return is_string($token) && $token !== '' && isset($session['token'])
		&& is_string($session['token']) && hash_equals($session['token'], $token);
}

/**
 * Create a short-lived selection. No business values are accepted or stored.
 * @param array $session Session storage
 * @param int[] $ids Validated IDs
 * @param int $owner User ID
 * @param int $entity Current entity
 * @param int $now Current timestamp
 * @return string Opaque identifier
 */
function msmCreateRun(array &$session, array $ids, $owner, $entity, $now)
{
	if ($owner < 1 || $entity < 1) {
		throw new InvalidArgumentException('MSMInvalidRun');
	}
	$ids = msmSelectionIds($ids, count($ids));
	$runs = isset($session['membersubscriptionmassaction']) && is_array($session['membersubscriptionmassaction'])
		? $session['membersubscriptionmassaction'] : array();
	foreach ($runs as $key => $run) {
		if (!is_array($run) || !isset($run['expires']) || $run['expires'] <= $now) {
			unset($runs[$key]);
		}
	}
	while (count($runs) >= 5) {
		array_shift($runs);
	}
	$token = bin2hex(random_bytes(16));
	$runs[$token] = array('owner' => (int) $owner, 'entity' => (int) $entity, 'expires' => $now + 900, 'ids' => $ids);
	$session['membersubscriptionmassaction'] = $runs;
	return $token;
}

/**
 * Read a run without mutating session state. Permissions are rechecked by the caller.
 * @param array $session Session storage
 * @param mixed $token Opaque identifier
 * @param int $owner User ID
 * @param int $entity Current entity
 * @param int $now Current timestamp
 * @return array Valid run
 * @throws RuntimeException
 */
function msmReadRun(array $session, $token, $owner, $entity, $now)
{
	if (!is_string($token) || !preg_match('/^[a-f0-9]{32}$/D', $token)) {
		throw new RuntimeException('MSMInvalidRun');
	}
	$runs = $session['membersubscriptionmassaction'] ?? null;
	$run = is_array($runs) ? ($runs[$token] ?? null) : null;
	if (!is_array($run) || !isset($run['owner'], $run['entity'], $run['expires'], $run['ids'])
		|| $run['owner'] !== (int) $owner || $run['entity'] !== (int) $entity || !is_int($run['expires']) || $run['expires'] <= $now) {
		throw new RuntimeException('MSMInvalidRun');
	}
	return $run;
}

/**
 * Cancel only a valid run belonging to this user and entity.
 * @param array $session Session storage
 * @param mixed $token Opaque identifier
 * @param int $owner User ID
 * @param int $entity Entity
 * @param int $now Current timestamp
 * @return void
 */
function msmCancelRun(array &$session, $token, $owner, $entity, $now)
{
	msmReadRun($session, $token, $owner, $entity, $now);
	unset($session['membersubscriptionmassaction'][$token]);
}

/**
 * Fixed internal destination; request parameters never influence redirects.
 * @return string
 */
function msmListUrl()
{
	return DOL_URL_ROOT.'/adherents/list.php';
}
