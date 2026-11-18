<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
require_once __DIR__.'/../class/transactionprofile.class.php';
/** Sync only unconsumed previews. No observer readiness, BEGIN, SET or row locks. */
function msmSyncPreviewProfile(array $state, $profiles, $user)
{
	if (in_array($state['phase'] ?? '', array('processing', 'done'), true)) { return $state; }
	$c = $profiles->current(); $binding = $profiles->binding($c, $user, $state['revision']);
	if (($state['profile'] ?? null) !== $binding) {
		$stale = isset($state['profile']) || $state['phase'] === 'confirm' || !empty($state['rows']);
		$state['phase'] = 'preview'; $state['rows'] = array(); $state['revision']++;
		$state['profile'] = $profiles->binding($c, $user, $state['revision']);
		if ($stale) { $state['flash'] = 'MSMProfileChanged'; }
	}
	$state['profile_notice'] = $c['reason'];
	foreach (array('invoice', 'autocreate', 'sendmail') as $feature) {
		if (!$c['features'][$feature]) { $state['options'][$feature] = false; }
	}
	return $state;
}
function msmProfileError($message)
{
	return in_array($message, array('MSMProfileUnavailable', 'MSMProfileChanged', 'MSMProfileInvalid', 'MSMProfileSelectionRequired', 'MSMProfileFeatureUnavailable', 'MSMCoreGuardedPending', 'MSMEnhancedUnavailable', 'MSMProfileSaveConflict'), true);
}
