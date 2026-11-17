<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
/** Parse positive integer configuration without truncation or overflow. */
function msmPositiveBatchLimit($value, $fallback)
{
	if (!is_int($value) && !is_string($value)) { return $fallback; }
	$value = (string) $value;
	if (!preg_match('/^[0-9]+$/D', $value)) { return $fallback; }
	$value = ltrim($value, '0');
	if ($value === '' || strlen($value) > strlen((string) PHP_INT_MAX)
		|| (strlen($value) === strlen((string) PHP_INT_MAX) && strcmp($value, (string) PHP_INT_MAX) > 0)) { return $fallback; }
	return (int) $value;
}
/** Effective current limit, including already activated installations. */
function msmBatchLimit()
{
	global $conf;
	return min(msmPositiveBatchLimit($conf->global->MEMBERSUBSCRIPTIONMASSACTION_MAX_MEMBERS_PER_BATCH ?? null, 100),
		(isset($conf->global->MAIN_LIMIT_FOR_MASS_ACTIONS) ? msmPositiveBatchLimit($conf->global->MAIN_LIMIT_FOR_MASS_ACTIONS, 0) : 1000));
}
/** Recheck current configuration before expensive processing or business writes. */
function msmRequireBatchSize(array $ids)
{
	if (count($ids) > msmBatchLimit()) { throw new InvalidArgumentException('MSMBatchLimit'); }
}
/** Bounded, session-bound notice; no selected IDs or preview rows are retained. */
function msmBatchNotice(array &$session, $count, $owner, $entity, $now)
{
	$token = bin2hex(random_bytes(16));
	$session['msm_batch_limit'] = array('token' => $token, 'count' => $count, 'owner' => $owner, 'entity' => $entity, 'expires' => $now + 900);
	return $token;
}
