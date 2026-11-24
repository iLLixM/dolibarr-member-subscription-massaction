<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
require_once __DIR__.'/batch.lib.php';
require_once __DIR__.'/profile.lib.php';
require_once __DIR__.'/../class/filebookingcheckpoint.class.php';
/** Readiness gates new operations, never the display of consumed session results. */
function msmRequireBookingDatabase(array $state, $observer, $service, $profiles, $user)
{
	if (in_array($state['phase'] ?? '', array('done', 'processing'), true)) { return; }
	if (($state['phase'] ?? '') !== 'confirm') { throw new InvalidArgumentException('MSMInvalidForm'); }
	msmRequireBatchSize($state['ids']);
	$profile = $profiles->admit($state['profile'] ?? null, $user, $state['options'], $state['revision']);
	try {
		$observed = $profile['profile'] === 'CoreGuarded' ? null : $observer->beforeBegin();
		if ($profile['profile'] === 'CoreGuarded') {
			$service->coreGuardedReadiness();
			$observed = array('state' => MsmDatabaseState::IDLE, 'fresh' => true);
		}
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
	$coreGuarded = ($state['profile']['profile'] ?? '') === 'CoreGuarded';
	if ($coreGuarded) {
		MsmFileBookingCheckpoint::requireRuntime();
		if (!($save instanceof MsmFileBookingCheckpoint)) { throw new RuntimeException('MSMCoreGuardedSessionUnsupported'); }
		$save->requireBinding();
	}
	$stop = false;
	foreach ($state['ids'] as $id) {
		$row = $state['rows'][$id];
		if ($stop || $row['errors'] || ($row['overlaps'] && empty($state['overlap'][$id]))) {
			$state['results'][$id] = array('status' => 'skipped', 'message' => $stop ? 'MSMBatchStopped' : 'MSMNotConfirmed', 'id' => 0, 'amount' => 0, 'mail' => 'not_selected', 'stop' => false);
			$save($state); continue;
		}
		$row['_profile'] = $state['profile'] ?? null;
		$result = $coreGuarded
			? $service->book($id, $row, $state['options'], $user, !empty($state['overlap'][$id]), $save)
			: $service->book($id, $row, $state['options'], $user, !empty($state['overlap'][$id]));
		$stop = !empty($result['stop']) || ($coreGuarded && (($result['completion'] ?? '') !== 'nonterminal_acknowledged'
			|| !in_array($result['status'], array('success', 'failed', 'skipped'), true)
			|| ($result['status'] !== 'success' && ($result['rollback'] ?? '') !== 'confirmed'))); // Decide before checkpoints or any post-completion effect.
		if ($coreGuarded) {
			$result['stop'] = $stop;
			// Completion, file, attachment and transport are distinct evidence.
			// A committed contribution must not become a failed DB operation
			// because a later external effect was unverified.
			$result['attachment_status'] = !empty($state['options']['invoice']) && !empty($state['options']['sendmail'])
				? 'not_processed' : 'not_required';
		}
		$member = $result['_member'] ?? null; unset($result['_member']);
		if ($stop && $coreGuarded) {
			$state['results'][$id] = $result;
			if ($result['status'] === 'success') {
				$state['results'][$id]['mail'] = 'not_processed';
				$state['results'][$id]['attachment_status'] = 'not_processed';
				if (!empty($result['invoice'])) { $state['results'][$id]['invoice']['pdf'] = array('status' => 'not_processed', 'message' => 'MSMPdfNotProcessed'); }
			}
			msmStopUnprocessed($state, $id); $state['phase'] = 'done';
			msmSaveBookingCheckpoint($state, $save, true);
			return $state; // Only the fixed DB-independent checkpoint may follow terminal completion.
		}
		if (!$stop && $result['status'] === 'success' && $state['options']['sendmail']) { $result['mail'] = 'unknown'; }
		$state['results'][$id] = $result;
		if (!msmSaveBookingCheckpoint($state, $save, $coreGuarded)) { msmStopUnprocessed($state, $id); return $state; } // No automatic rebook/resend.
		if (!$stop && $result['status'] === 'success') {
			if (!empty($state['options']['invoice'])) {
				$state['results'][$id]['invoice']['pdf'] = array('status' => 'unknown', 'message' => 'MSMPdfInterrupted');
				if (!msmSaveBookingCheckpoint($state, $save, $coreGuarded)) { msmStopUnprocessed($state, $id); return $state; }
				if ($coreGuarded) {
					try { $save->requirePostCommitEffect((int) $id, 'pdf'); }
					catch (Throwable $e) { $state['checkpoint_failed'] = true; msmStopUnprocessed($state, $id); return $state; }
				}
				try {
					$state['results'][$id]['invoice']['pdf'] = $pdf !== null
						? $pdf->generate($state['results'][$id]['invoice'], $user)
						: array('status' => 'failed', 'message' => 'MSMPdfGenerationFailed');
					if (!is_array($state['results'][$id]['invoice']['pdf'])
						|| !in_array($state['results'][$id]['invoice']['pdf']['status'] ?? '', array('verified', 'failed', 'unknown'), true)) {
						throw new RuntimeException('MSMPdfUnknown');
					}
				} catch (Throwable $e) {
					if (!$coreGuarded) { throw $e; }
					$state['results'][$id]['invoice']['pdf'] = array('status' => 'unknown', 'message' => 'MSMPdfUnknown');
				}
				if (($state['results'][$id]['invoice']['pdf']['status'] ?? null) === 'unknown' && $coreGuarded) {
					// A partially written document may have escaped an exception.
					// STOP other members, never regenerate or dispatch SMTP.
					$state['results'][$id]['mail'] = 'not_processed';
					$state['results'][$id]['attachment_status'] = 'not_processed';
					msmStopUnprocessed($state, $id); $state['phase'] = 'done';
					msmSaveBookingCheckpoint($state, $save, true);
					return $state;
				}
				if (!msmSaveBookingCheckpoint($state, $save, $coreGuarded)) {
					if ($coreGuarded) {
						// PDF may exist, but its verified descriptor could not be
						// durably checkpointed. Never imply verified persistence.
						$state['results'][$id]['invoice']['pdf'] = array('status'=>'unknown','message'=>'MSMPdfUnknown');
						$state['results'][$id]['mail'] = 'not_processed';
						$state['results'][$id]['attachment_status'] = 'not_processed';
					}
					msmStopUnprocessed($state, $id); return $state;
				}
			}
			$invoice = $state['results'][$id]['invoice'] ?? null;
			if ($coreGuarded) {
				if (empty($state['options']['sendmail'])) {
					$state['results'][$id]['mail'] = 'not_selected';
				} elseif ($invoice !== null && ($invoice['pdf']['status'] ?? null) !== 'verified') {
					// A failed PDF is not a transport failure or a successful attachment.
					$state['results'][$id]['mail'] = 'attachment_missing';
					$state['results'][$id]['attachment_status'] = 'missing';
				} else {
					// 'unknown' was durably persisted before hooks/SMTP.
					if (!msmSaveBookingCheckpoint($state, $save, true)) { msmStopUnprocessed($state, $id); return $state; }
					try { $save->requirePostCommitEffect((int) $id, 'mail'); }
					catch (Throwable $e) { $state['checkpoint_failed'] = true; msmStopUnprocessed($state, $id); return $state; }
					try {
						// Only the production adapter exposes separately derived
						// evidence. Fail closed instead of pretending a legacy spy
						// is an authoritative real-file/transport verifier.
						$delivery = $mailer->sendWithEvidence($member, $row, $user, true, $invoice, $save, (int) $id);
						if (!is_array($delivery)
							|| !in_array($delivery['mail'] ?? '', array('transport', 'hook', 'failed', 'missing', 'disabled', 'denied', 'attachment_missing', 'unknown'), true)
							|| !in_array($delivery['attachment'] ?? '', array('verified_for_transport', 'verified_before_hook', 'missing', 'not_sent', 'unknown', 'not_required'), true)
							|| !msmValidMailAttachmentEvidence($delivery, $invoice !== null)) {
							throw new RuntimeException('MSMMailUnknown');
						}
					} catch (Throwable $e) {
						// F38-06: contribution-only mails have no invoice attachment.
						$delivery = array('mail' => 'unknown',
							'attachment' => $invoice !== null ? 'unknown' : 'not_required');
					}
					$state['results'][$id]['mail'] = $delivery['mail'];
					$state['results'][$id]['attachment_status'] = $delivery['attachment'];
				}
			} else {
				$state['results'][$id]['mail'] = $mailer->send($member, $row, $user, $state['options']['sendmail'], $invoice);
			}
			if ($coreGuarded && $state['results'][$id]['mail'] === 'unknown') {
				// The external effect may have happened. Persist ambiguity and
				// stop the remaining batch; never attempt an automatic resend.
				msmStopUnprocessed($state, $id); $state['phase'] = 'done';
				msmSaveBookingCheckpoint($state, $save, true);
				return $state;
			}
			if (!msmSaveBookingCheckpoint($state, $save, $coreGuarded)) {
				if ($coreGuarded) {
					// Transport may have occurred but its result could not be
					// durably checkpointed; preserve DB and PDF status only.
					$state['results'][$id]['mail'] = 'unknown';
					$state['results'][$id]['attachment_status'] = $invoice !== null ? 'unknown' : 'not_required';
				}
				msmStopUnprocessed($state, $id); return $state;
			}
		}
	}
	$state['phase'] = 'done'; msmSaveBookingCheckpoint($state, $save, $coreGuarded);
	return $state;
}

/** Finite combinations; never infer successful transport from a stray adapter
 * status. The invoice-less mail path must not invent invoice attachments.
 */
function msmValidMailAttachmentEvidence(array $delivery, $withInvoice)
{
	$mail = $delivery['mail'] ?? null;
	$attachment = $delivery['attachment'] ?? null;
	if (!$withInvoice) {
		return $attachment === 'not_required'
			&& in_array($mail,array('transport','hook','failed','denied','missing','disabled','unknown'),true);
	}
	$allowed = array(
		'transport' => 'verified_for_transport',
		'hook' => 'verified_before_hook',
		'attachment_missing' => 'missing',
		'unknown' => 'unknown',
		'failed' => 'not_sent',
		'disabled' => 'not_sent',
		'denied' => 'not_sent',
		'missing' => 'not_sent',
	);
	return isset($allowed[$mail]) && $attachment === $allowed[$mail];
}
/** Mark only following selected slots, preserving the acknowledged current result. */
function msmStopUnprocessed(array &$state, $current)
{
	$after = false;
	foreach ($state['ids'] as $id) {
		if ($after) { $state['results'][$id] = array('status' => 'not_processed', 'message' => 'MSMBatchStopped', 'id' => 0, 'amount' => 0, 'mail' => 'not_selected', 'stop' => false); }
		if ($id === $current) { $after = true; }
	}
	$state['batch_stop'] = true;
}
/** E15: failed persistence never changes an acknowledged DB result or permits retry. */
function msmSaveBookingCheckpoint(array &$state, callable $save, $coreGuarded)
{
	try {
		if ($save($state) === false) { throw new RuntimeException('MSMSessionFailure'); }
		return true;
	} catch (Throwable $e) {
		if (!$coreGuarded) { throw $e; }
		$state['phase'] = 'processing'; $state['batch_stop'] = true;
		$state['checkpoint_failed'] = true; $state['flash'] = 'MSMSessionFailure';
		return false;
	}
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
