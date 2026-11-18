<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
require_once __DIR__.'/batch.lib.php';
require_once __DIR__.'/profile.lib.php';
/** Readiness gates new operations, never the display of consumed session results. */
function msmRequireBookingDatabase(array $state, $observer, $service, $profiles, $user)
{
	if (in_array($state['phase'] ?? '', array('done', 'processing'), true)) { return; }
	if (($state['phase'] ?? '') !== 'confirm') { throw new InvalidArgumentException('MSMInvalidForm'); }
	msmRequireBatchSize($state['ids']);
	$profile = $profiles->admit($state['profile'] ?? null, $user, $state['options'], $state['revision']);
	try {
		$observed = $observer->beforeBegin();
		if ($observed['state'] !== MsmDatabaseState::IDLE || !$observed['fresh']) {
			throw new RuntimeException($observed['reason']);
		}
		$service->checkTables(!empty($state['options']['invoice']), !empty($state['options']['autocreate']));
	} catch (Throwable $e) {
		$reason = in_array($e->getMessage(), array('MSMDatabaseUnsupported', 'MSMDatabaseBootstrapUnavailable', 'MSMDatabaseObservationUnavailable', 'MSMTransactionUnsupported'), true) ? $e->getMessage() : 'MSMDatabaseObservationUnavailable';
		throw new RuntimeException($reason === 'MSMDatabaseObservationUnavailable' && $profile['profile'] === 'EnhancedMySQL' ? 'MSMEnhancedUnavailable' : $reason);
	}
}
/** Strict confirmation request: all business values remain on the server. */
function msmBookingPost(array $state, array $post, $action)
{
	if (array_diff(array_keys($post), array('token', 'preview', 'revision', 'action', 'overlap'))
		|| ($post['action'] ?? null) !== $action || ($post['revision'] ?? null) !== (string) $state['revision']) { throw new InvalidArgumentException('MSMInvalidForm'); }
	$overlap = $post['overlap'] ?? array();
	if (!is_array($overlap) || array_diff(array_keys($overlap), $state['ids'])) { throw new InvalidArgumentException('MSMInvalidForm'); }
	foreach ($overlap as $value) { if ($value !== '1') { throw new InvalidArgumentException('MSMInvalidForm'); } }
	return $overlap;
}
/** Editing valid consent advances its revision without changing the selected profile. */
function msmEditConfirmation(array $state, array $post)
{
	msmBookingPost($state, $post, 'edit');
	if (($state['phase'] ?? '') !== 'confirm') { throw new InvalidArgumentException('MSMInvalidForm'); }
	$state['phase'] = 'preview'; $state['revision']++;
	if (isset($state['profile'])) { $state['profile']['confirmation_revision'] = $state['revision']; }
	return $state;
}
/** Consume before any business write. The controller must durably save the session first. */
function msmConsumeConfirmation(array $state, array $post)
{
	$overlap = msmBookingPost($state, $post, 'book');
	if (($state['phase'] ?? '') !== 'confirm') { throw new InvalidArgumentException('MSMInvalidForm'); }
	msmRequireBatchSize($state['ids']);
	$state['phase'] = 'processing'; $state['revision']++; $state['overlap'] = $overlap;
	$state['results'] = array();
	foreach ($state['ids'] as $id) { $state['results'][$id] = array('status' => 'unknown', 'message' => 'MSMInterrupted', 'id' => 0, 'amount' => 0, 'mail' => 'unknown', 'stop' => false); }
	return $state;
}
/** Pure batch orchestration with a persistence checkpoint before/after each side effect. */
function msmProcessBooking(array $state, $service, $mailer, $user, callable $save, $pdf = null)
{
	msmRequireBatchSize($state['ids']);
	$stop = false;
	foreach ($state['ids'] as $id) {
		$row = $state['rows'][$id];
		if ($stop || $row['errors'] || ($row['overlaps'] && empty($state['overlap'][$id]))) {
			$state['results'][$id] = array('status' => 'skipped', 'message' => $stop ? 'MSMBatchStopped' : 'MSMNotConfirmed', 'id' => 0, 'amount' => 0, 'mail' => 'not_selected', 'stop' => false);
			$save($state); continue;
		}
		$row['_profile'] = $state['profile'] ?? null;
		$result = $service->book($id, $row, $state['options'], $user, !empty($state['overlap'][$id]));
		$member = $result['_member'] ?? null; unset($result['_member']);
		if ($result['status'] === 'success' && $state['options']['sendmail']) { $result['mail'] = 'unknown'; }
		$state['results'][$id] = $result;
		$save($state); // A crash from here must never rebook or resend automatically.
		if ($result['status'] === 'success') {
			if (!empty($state['options']['invoice'])) {
				$state['results'][$id]['invoice']['pdf'] = array('status' => 'unknown', 'message' => 'MSMPdfInterrupted');
				$save($state);
				$state['results'][$id]['invoice']['pdf'] = $pdf !== null
					? $pdf->generate($state['results'][$id]['invoice'], $user)
					: array('status' => 'failed', 'message' => 'MSMPdfGenerationFailed');
				$save($state);
			}
			$invoice = $state['results'][$id]['invoice'] ?? null;
			$state['results'][$id]['mail'] = $mailer->send($member, $row, $user, $state['options']['sendmail'], $invoice);
			$save($state);
		}
		$stop = $result['stop'];
	}
	$state['phase'] = 'done'; $save($state);
	return $state;
}

/** Recheck object access for result links without changing the completed operation. */
function msmResultAccess($db, $user, array $state)
{
	$access = array();
	foreach ($state['results'] ?? array() as $id => $result) {
		$access[$id] = array('invoice' => false, 'customer' => false);
		if (empty($result['invoice']['id'])) { continue; }
		try {
			if (!empty($user->socid) || !isModEnabled('invoice') || !isModEnabled('societe')
				|| !$user->hasRight('facture', 'lire') || !$user->hasRight('societe', 'lire')) { continue; }
			require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
			$invoice = new Facture($db);
			if ($invoice->fetch((int) $result['invoice']['id']) > 0
				&& (int) $invoice->entity === (int) $result['invoice']['entity']
				&& (int) $invoice->socid === (int) $result['invoice']['customer_id']
				&& in_array((int) $invoice->entity, array_map('intval', explode(',', getEntity('invoice'))), true)
				&& checkUserAccessToObject($user, array('facture'), $invoice)) { $access[$id]['invoice'] = true; }
			if (!empty($result['invoice']['customer_created']) && $user->hasRight('societe', 'lire')) {
				require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
				$customer = new Societe($db);
				if ($customer->fetch((int) $result['invoice']['customer_id']) > 0
					&& in_array((int) $customer->entity, array_map('intval', explode(',', getEntity('societe'))), true)
					&& checkUserAccessToObject($user, array('societe'), $customer)) { $access[$id]['customer'] = true; }
			}
		} catch (Throwable $ignored) { $access[$id] = array('invoice' => false, 'customer' => false); }
	}
	return $access;
}
