<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

require_once __DIR__.'/batch.lib.php';

/**
 * Resolve only the three supported tokens, without evaluating template code.
 * @param string $template Plain text template (at most 1024 characters)
 * @param string $type Verified membership type label
 * @param int|null $start Verified start date
 * @param int|null $end Verified end date
 * @return string Plain text description, empty when required dates are unavailable
 * @throws InvalidArgumentException
 */
function msmDescription($template, $type, $start, $end)
{
	if (!is_string($template) || dol_strlen($template) > 1024 || preg_match('/[\p{Cc}\p{Cf}]/u', $template) !== 0) {
		throw new InvalidArgumentException('SubscriptionBulkDescriptionInvalid');
	}
	if ((strpos($template, '{Start date}') !== false && $start <= 0) || (strpos($template, '{End date}') !== false && $end <= 0)) {
		return '';
	}
	return strtr($template, array(
		'{Member type}' => $type,
		'{Start date}' => $start > 0 ? dol_print_date($start, 'day') : '',
		'{End date}' => $end > 0 ? dol_print_date($end, 'day') : '',
	));
}

/**
 * Count processable rows from the current validation result, without SQL.
 * @param array<int,array<string,mixed>> $rows Preview rows
 * @return array{selected:int,ready:int,skipped:int}
 */
function msmPreviewCounts($rows)
{
	$counts = array('selected' => count($rows), 'ready' => 0, 'skipped' => 0);
	foreach ($rows as $row) {
		$counts[empty($row['errors']) && in_array($row['status'], array('ready', 'warning'), true) ? 'ready' : 'skipped']++;
	}
	return $counts;
}

/**
 * Preserve manual ends; only recalculate an automatic end when its start changes.
 * @param array<string,mixed> $previous Previous server-side preview row
 * @param int $start Submitted start
 * @param int $end Submitted end
 * @param bool $recalculate Explicitly reset the end to its suggestion
 * @param int|null $common Explicitly applied common start, null otherwise
 * @param bool $calendar Explicitly replace even manual ends with the configured calendar rule
 * @return array{start:int,end:int|null,manualend:bool}
 */
function msmPeriodInput($previous, $start, $end, $recalculate = false, $common = null, $calendar = false)
{
	$manual = !empty($previous['manualend']) || $end !== $previous['end'];
	if ($common !== null) {
		$start = $common;
	}
	if ($recalculate || (!$manual && $start !== $previous['start'])) {
		$end = null;
		$manual = false;
	}
	if ($calendar) {
		$calendarend = MsmPeriodService::subscriptionCalendarEndForBatch($start);
		if ($calendarend !== null) {
			$end = $calendarend;
			$manual = true;
		}
	}
	return array('start' => $start, 'end' => $end, 'manualend' => $manual);
}

/**
 * Summarize differences in actual displayed periods, durations and amounts.
 * @param array<int,array<string,mixed>> $rows Preview rows
 * @return string[] Translation keys
 */
function msmPreviewWarnings($rows)
{
	$periods = $durations = $amounts = array();
	foreach ($rows as $row) {
		if ($row['start'] && $row['end']) {
			$periods[$row['start'].':'.$row['end']] = true;
		}
		if ($row['quantity']) {
			$durations[$row['quantity'].$row['unit']] = true;
		}
		if ($row['amount'] !== null) {
			$amounts[(string) $row['amount']] = true;
		}
	}
	$warnings = array();
	if (count($periods) > 1 || count($durations) > 1) {
		$warnings[] = 'SubscriptionBulkDifferentPeriods';
	}
	if (count($amounts) > 1) {
		$warnings[] = 'SubscriptionBulkDifferentAmounts';
	}
	return $warnings;
}


/** Strict ISO calendar input. Zero denotes invalid input and is never normalized. */
function msmReadDate($value)
{
	if (!is_string($value) || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/D', $value, $m)
		|| (int) $m[1] < 1970 || (int) $m[1] > 9998 || !checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
		return 0;
	}
	return dol_mktime(0, 0, 0, (int) $m[2], (int) $m[3], (int) $m[1]);
}

/** Independent one-hour preview store; the original 15-minute selection is unchanged. */
function msmCreatePreview(array &$session, array $selection, $now)
{
	global $langs, $user;
	require_once __DIR__.'/../class/preview.class.php';
	$runs = $session['msm_previews'] ?? array();
	foreach ($runs as $key => $run) {
		if ($run['expires'] <= $now && ($run['phase'] ?? '') !== 'processing') { unset($runs[$key]); }
	}
	while (count($runs) >= 5) {
		$removed = false;
		foreach ($runs as $oldkey => $oldrun) {
			if (($oldrun['phase'] ?? '') !== 'processing') { unset($runs[$oldkey]); $removed = true; break; }
		}
		if (!$removed) { throw new RuntimeException('MSMInvalidRun'); }
	}
	$key = bin2hex(random_bytes(16));
	$runs[$key] = array('owner' => $selection['owner'], 'entity' => $selection['entity'], 'expires' => $now + 3600,
		'format' => 4, 'phase' => 'preview', 'ids' => $selection['ids'], 'revision' => 0, 'rows' => array(), 'input' => array(), 'common' => '',
		'options' => array('invoice' => msmInvoiceDefault($user), 'sendmail' => (bool) getDolGlobalInt('ADHERENT_DEFAULT_SENDINFOBYMAIL'), 'autocreate' => false,
			'description' => $langs->transnoentities('Subscription').', {Member type}, {Start date} - {End date}'));
	$session['msm_previews'] = $runs;
	return $key;
}

/** Validate identity and expiry before trusting any server-side state. */
function msmReadPreview(array $session, $key, $owner, $entity, $now)
{
	$proxy = array('membersubscriptionmassaction' => $session['msm_previews'] ?? array());
	return msmReadRun($proxy, $key, $owner, $entity, $now);
}

/** The planned third-party action requires invoice mode and its own permission. */
function msmCanPlanThirdParty($invoice, $user)
{
	return $invoice && isModEnabled('societe') && $user->hasRight('societe', 'creer');
}

/** Validate the complete form, preserving only authorized date/option overrides. */
function msmUpdatePreview(array $state, array $post)
{
	global $user;
	if (isset($state['phase']) && $state['phase'] !== 'preview') { throw new InvalidArgumentException('MSMInvalidForm'); }
	$allowed = array('token', 'preview', 'revision', 'action', 'common', 'description', 'invoice', 'sendmail', 'autocreate', 'start', 'end');
	if (array_diff(array_keys($post), $allowed) || !is_string($post['revision'] ?? null)
		|| $post['revision'] !== (string) $state['revision'] || !is_string($post['action'] ?? null)) {
		throw new InvalidArgumentException('MSMInvalidForm');
	}
	$action = $post['action'];
	$reset = null;
	if (preg_match('/^reset:([1-9][0-9]*)$/D', $action, $m)) { $reset = (int) $m[1]; }
	if (!in_array($action, array('refresh', 'common', 'calendar', 'confirm'), true) && !in_array($reset, $state['ids'], true)) {
		throw new InvalidArgumentException('MSMInvalidForm');
	}
	if ($action === 'calendar' && !getDolGlobalInt('MEMBER_SUBSCRIPTION_SUGGEST_END_OF_MONTH') && !getDolGlobalInt('MEMBER_SUBSCRIPTION_SUGGEST_END_OF_YEAR')) {
		throw new InvalidArgumentException('MSMInvalidForm');
	}
	foreach (array('start', 'end') as $field) {
		if (!isset($post[$field]) || !is_array($post[$field]) || count($post[$field]) !== count($state['ids'])
			|| array_diff(array_keys($post[$field]), $state['ids'])) { throw new InvalidArgumentException('MSMInvalidForm'); }
		foreach ($post[$field] as $value) {
			if (!is_string($value) || strlen($value) > 10) { throw new InvalidArgumentException('MSMInvalidForm'); }
		}
	}
	foreach (array('invoice', 'sendmail', 'autocreate') as $field) {
		if (isset($post[$field]) && $post[$field] !== '1') { throw new InvalidArgumentException('MSMInvalidForm'); }
		$state['options'][$field] = isset($post[$field]);
		if ($state['options'][$field] && isset($state['profile']) && empty($state['profile']['features'][$field])) { throw new RuntimeException('MSMProfileFeatureUnavailable'); }
	}
	// A no-JavaScript form can still submit the previously checked dependent box.
	// Disabling invoices only removes an option; it never grants an action.
	if (!$state['options']['invoice']) { $state['options']['autocreate'] = false; }
	if ($state['options']['autocreate'] && !msmCanPlanThirdParty(true, $user)) {
		throw new InvalidArgumentException('MSMInvalidForm');
	}
	msmDescription($post['description'] ?? null, '', 1, 1);
	$state['options']['description'] = $post['description'];
	if (!is_string($post['common'] ?? null) || strlen($post['common']) > 10) { throw new InvalidArgumentException('MSMInvalidForm'); }
	$state['common'] = $post['common'];
	$common = $action === 'common' ? msmReadDate($post['common']) : null;
	if ($common === 0) { throw new InvalidArgumentException('SubscriptionBulkInvalidDate'); }
	foreach ($state['ids'] as $id) {
		$state['input'][$id] = msmPeriodInput($state['rows'][$id], msmReadDate($post['start'][$id]), msmReadDate($post['end'][$id]), $reset === $id, $common, $action === 'calendar');
	}
	$state['revision']++;
	if (isset($state['profile'])) { $state['profile']['confirmation_revision'] = $state['revision']; }
	return $state;
}

/** Read current business data on every refresh; no user-supplied amounts/types. */
function msmEvaluatePreview($db, $user, array $state)
{
	msmRequireBatchSize($state['ids']);
	require_once DOL_DOCUMENT_ROOT.'/adherents/class/adherent.class.php';
	require_once __DIR__.'/../class/preview.class.php';
	foreach ($state['ids'] as $id) {
		$member = new Adherent($db);
		$member->id = $id;
		$service = new MsmPreviewService($db, $member);
		$input = $state['input'][$id] ?? array('start' => null, 'end' => null, 'manualend' => false);
		$options = $state['options'];
		if (!$input['manualend']) { $input['end'] = null; }
		$row = $service->previewSubscriptionWithOptions($user, $input['start'], $options['invoice'], $options['sendmail'], $input['end'], $options['autocreate'], $options['description']);
		$row['manualend'] = $input['manualend'];
		$state['rows'][$id] = $row;
	}
	return $state;
}

/** New runs only: never reapply a default to an explicit user choice. */
function msmInvoiceDefault($user)
{
	return getDolGlobalString('ADHERENT_BANK_USE') === 'invoiceonly'
		&& MsmPreviewService::canCreateSubscriptionWithOptions($user, true, false);
}

/** Old confirmed previews need fresh consent; consumed runs must never reopen. */
function msmUpgradePreview(array $state)
{
	if (($state['format'] ?? 0) === 4) { return $state; }
	if (in_array($state['phase'] ?? '', array('processing', 'done'), true)) { return $state; }
	$state['format'] = 4; $state['phase'] = 'preview'; $state['revision']++;
	$state['options']['invoice'] = false; $state['options']['autocreate'] = false;
	$state['rows'] = array();
	return $state;
}
