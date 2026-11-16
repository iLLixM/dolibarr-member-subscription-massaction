<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

// Use the standard authenticated bootstrap. Server paths, never request paths.
define('CSRFCHECK_WITH_TOKEN', '1');
$loaded = false;
$candidates = array();
if (!empty($_SERVER['CONTEXT_DOCUMENT_ROOT'])) {
	$candidates[] = rtrim($_SERVER['CONTEXT_DOCUMENT_ROOT'], '/').'/main.inc.php';
}
if (!empty($_SERVER['SCRIPT_FILENAME'])) {
	$candidates[] = dirname($_SERVER['SCRIPT_FILENAME'], 3).'/main.inc.php';
}
$candidates[] = dirname(__DIR__, 2).'/main.inc.php';
foreach (array_unique($candidates) as $bootstrap) {
	if (is_file($bootstrap)) {
		require_once $bootstrap;
		$loaded = true;
		break;
	}
}
if (!$loaded) {
	http_response_code(500);
	exit('Dolibarr bootstrap unavailable.');
}
header('Cache-Control: no-store');
require_once __DIR__.'/lib/membersubscriptionmassaction.lib.php';
$langs->load('membersubscriptionmassaction@membersubscriptionmassaction');
try { MsmCoreCompatibility::booking($db); }
catch (Throwable $e) { accessforbidden($langs->trans('MSMCoreContractUnsupported')); }
if (!msmCanUse($user)) {
	accessforbidden();
}
require_once __DIR__.'/class/booking.class.php';
$databaseState = new MsmDatabaseState($db);
$bookingService = new MsmBookingService($db, $databaseState);
$databasePreflight = $databaseState->beforeBegin();
if ($databasePreflight['state'] !== MsmDatabaseState::IDLE || !$databasePreflight['fresh']) {
	accessforbidden($langs->trans($databasePreflight['reason']));
}
try { $bookingService->checkTables(); }
catch (Throwable $e) { accessforbidden($langs->trans('MSMTransactionUnsupported')); }
$method = $_SERVER['REQUEST_METHOD'] ?? '';
if ($method !== 'GET' && $method !== 'POST') {
	http_response_code(405);
	header('Allow: GET, POST');
	exit;
}

require_once __DIR__.'/lib/preview.lib.php';
require_once __DIR__.'/class/period.class.php';
require_once __DIR__.'/lib/preview_renderer.lib.php';
require_once __DIR__.'/lib/booking.lib.php';
require_once __DIR__.'/lib/booking_renderer.lib.php';
$langs->load('members');
$error = '';
try {
	if ($method === 'GET' && isset($_GET['run'])) {
		$selection = msmReadRun($_SESSION, $_GET['run'], (int) $user->id, (int) $conf->entity, dol_now());
		msmCheckMembers($db, $user, $selection['ids']);
		$key = msmCreatePreview($_SESSION, $selection, dol_now());
		header('Location: '.dol_buildpath('/membersubscriptionmassaction/subscription.php', 1).'?preview='.$key, true, 303);
		exit;
	}
	$key = $method === 'POST' ? ($_POST['preview'] ?? null) : ($_GET['preview'] ?? null);
	$state = msmReadPreview($_SESSION, $key, (int) $user->id, (int) $conf->entity, dol_now());
	$state = msmUpgradePreview($state);
	$_SESSION['msm_previews'][$key] = $state;
} catch (Exception $e) {
	accessforbidden($langs->trans('MSMInvalidRun'));
}
if ($method === 'POST') {
	if (!msmValidCsrf($_POST['token'] ?? null, $_SESSION)) { accessforbidden(); }
	if (($_POST['action'] ?? null) === 'cancel' && $state['phase'] === 'preview') {
		unset($_SESSION['msm_previews'][$key]);
		header('Location: '.msmListUrl(), true, 303);
		exit;
	}
	try {
		$action = $_POST['action'] ?? null;
		if ($action === 'book') {
			$state = msmConsumeConfirmation($state, $_POST);
			$_SESSION['msm_previews'][$key] = $state;
			// Persist consumed state before releasing PHP's session lock and before any write.
			if (!session_write_close()) { throw new RuntimeException('MSMSessionFailure'); }
			require_once __DIR__.'/class/booking.class.php';
			require_once __DIR__.'/class/mail.class.php';
			require_once __DIR__.'/class/invoicepdf.class.php';
			$save = static function ($updated) use ($key) {
				if (!session_start()) { throw new RuntimeException('MSMSessionFailure'); }
				$current = $_SESSION['msm_previews'][$key] ?? null;
				if (!$current || $current['phase'] !== 'processing' || $current['revision'] !== $updated['revision']) {
					session_write_close(); throw new RuntimeException('MSMSessionFailure');
				}
				$_SESSION['msm_previews'][$key] = $updated;
				if (!session_write_close()) { throw new RuntimeException('MSMSessionFailure'); }
			};
			$save($state); // Read back consumed state before the first database side effect.
			$state = msmProcessBooking($state, $bookingService, new MsmSubscriptionMail($db), $user, $save, new MsmInvoicePdfService($db));
		} elseif ($action === 'edit') {
			msmBookingPost($state, $_POST, 'edit');
			if ($state['phase'] !== 'confirm') { throw new InvalidArgumentException('MSMInvalidForm'); }
			$state['phase'] = 'preview'; $state['revision']++;
			$_SESSION['msm_previews'][$key] = $state;
		} else {
			$state = msmUpdatePreview($state, $_POST);
			$state = msmEvaluatePreview($db, $user, $state);
			$bookingService->checkTables(!empty($state['options']['invoice']));
			if ($action === 'confirm') {
				if (count($state['ids']) > 50) { throw new InvalidArgumentException('MSMBatchLimit'); }
				$state['phase'] = 'confirm';
			}
			$_SESSION['msm_previews'][$key] = $state;
		}
	} catch (Throwable $e) {
		// Once consumed, keep results/pending uncertainty intact; never reopen booking.
		if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['msm_previews'][$key])) {
			$_SESSION['msm_previews'][$key]['flash'] = in_array($e->getMessage(), array('SubscriptionBulkInvalidDate', 'SubscriptionBulkDescriptionInvalid', 'MSMBatchLimit', 'MSMTransactionUnsupported', 'MSMDatabaseObservationUnavailable'), true) ? $e->getMessage() : 'MSMInvalidForm';
		}
	}
	header('Location: '.dol_buildpath('/membersubscriptionmassaction/subscription.php', 1).'?preview='.$key, true, 303);
	exit;
}
try {
	if ($state['phase'] === 'preview') { $state = msmEvaluatePreview($db, $user, $state); }
	if (in_array($state['phase'], array('preview', 'confirm'), true)) { $bookingService->checkTables(!empty($state['options']['invoice'])); }
	$error = $state['flash'] ?? '';
	unset($state['flash']);
	$_SESSION['msm_previews'][$key] = $state;
} catch (Throwable $e) {
	$error = in_array($e->getMessage(), array('MSMTransactionUnsupported', 'MSMDatabaseObservationUnavailable'), true) ? $e->getMessage() : 'SubscriptionBulkDatabaseError';
	$state['rows'] = array();
}
$titlekey = msmPageTitleKey($state['phase']);
$morejs = $state['phase'] === 'confirm' ? array(dol_buildpath('/membersubscriptionmassaction/js/subscription.js', 1)) : array();
llxHeader('', $langs->trans($titlekey), '', '', 0, 0, $morejs);
print load_fiche_titre($langs->trans($titlekey));
if ($error) { print '<div class="error">'.msmText($error).'</div>'; }
if ($state['rows']) {
	$access = $state['phase'] === 'done' ? msmResultAccess($db, $user, $state) : array();
	print $state['phase'] === 'preview' ? msmRenderPreview($state, $key) : msmRenderBooking($state, $key, $access);
}
else { print '<p><a href="'.dol_escape_htmltag(msmListUrl()).'">'.msmText('MSMBack').'</a></p>'; }
llxFooter();
$db->close();
