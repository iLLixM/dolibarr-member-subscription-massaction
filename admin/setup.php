<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
define('CSRFCHECK_WITH_TOKEN', '1');
$loaded = false;
$candidates = array();
if (!empty($_SERVER['CONTEXT_DOCUMENT_ROOT'])) { $candidates[] = rtrim($_SERVER['CONTEXT_DOCUMENT_ROOT'], '/').'/main.inc.php'; }
if (!empty($_SERVER['SCRIPT_FILENAME'])) { $candidates[] = dirname($_SERVER['SCRIPT_FILENAME'], 4).'/main.inc.php'; }
$candidates[] = dirname(__DIR__, 3).'/main.inc.php';
foreach (array_unique($candidates) as $bootstrap) {
	if (is_file($bootstrap)) { require_once $bootstrap; $loaded = true; break; }
}
if (!$loaded) { http_response_code(500); exit('Dolibarr bootstrap unavailable.'); }
require_once __DIR__.'/../lib/membersubscriptionmassaction.lib.php';
require_once __DIR__.'/../lib/profile.lib.php';
require_once __DIR__.'/../lib/preview_renderer.lib.php';
$langs->load('membersubscriptionmassaction@membersubscriptionmassaction');
if (empty($user->admin) || !empty($user->socid) || !isModEnabled('membersubscriptionmassaction')) { accessforbidden(); }
$profiles = new MsmTransactionProfile($db, new MsmDatabaseState($db), (int) $conf->entity);
$method = $_SERVER['REQUEST_METHOD'] ?? '';
if (!in_array($method, array('GET', 'POST'), true)) { http_response_code(405); header('Allow: GET, POST'); exit; }
$error = ''; $saved = false; $current = null;
try {
	if ($method === 'POST') { $profiles->configure($_POST, $_SESSION, $user, $method); $saved = true; }
	$current = $profiles->current();
} catch (Throwable $e) {
	$error = msmProfileError($e->getMessage()) || in_array($e->getMessage(), array('MSMInvalidForm', 'NotEnoughPermissions', 'MSMDatabaseUnsupported'), true) ? $e->getMessage() : 'MSMProfileUnavailable';
}
header('Cache-Control: no-store');
llxHeader('', $langs->trans('MSMProfileSetup'));
print load_fiche_titre($langs->trans('MSMProfileSetup'));
print '<p>'.msmText('MSMProfileSetupHelp').'</p>';
if ($error) { print '<div class="error">'.msmText($error).'</div>'; }
if ($saved) { print '<div class="ok">'.msmText('MSMProfileSaved').'</div>'; }
if ($current !== null) {
	if ($current['reason']) { print '<div class="warning">'.msmText($current['reason']).'</div>'; }
	print '<form method="post"><input type="hidden" name="token" value="'.dol_escape_htmltag(newToken()).'">';
	print '<input type="hidden" name="revision" value="'.dol_escape_htmltag($current['revision']).'">';
	print '<label>'.msmText('MSMProfileSelect').' <select name="profile" required><option value="">'.msmText('MSMProfileSelect').'</option>';
	foreach (MsmTransactionProfile::choices($current['family']) as $choice) {
		print '<option value="'.dol_escape_htmltag($choice).'"'.($choice === $current['profile'] ? ' selected' : '').'>'.msmText('MSMProfile'.$choice).'</option>';
	}
	print '</select></label> <button class="button" name="action" value="save">'.msmText('Save').'</button></form>';
}
llxFooter(); $db->close();
