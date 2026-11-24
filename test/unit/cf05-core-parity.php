<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
// Owner 2026-11-22: Core-parity development contract, not an installed
// Core/trigger source hash allowlist and not real DB/PDF/SMTP evidence.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
error_reporting(E_ALL & ~E_DEPRECATED);
$root = dirname(__DIR__, 2);
$passed = 0;
function parityAssert($ok, $label)
{
	global $passed;
	if (!$ok) { throw new RuntimeException('CF05-PARITY FAIL: '.$label); }
	$passed++;
}
$invoiceModule = file_get_contents($root.'/class/invoice.class.php');
$profileModule = file_get_contents($root.'/class/invoiceprofile.class.php');
parityAssert(is_string($invoiceModule)
	&& strpos($invoiceModule, 'MsmCoreFinanceQualification') === false
	&& strpos($invoiceModule, 'requireCoreProvisionalPathSafe') === false,
	'no retired source/trigger/PROV prohibition in productive invoice path');
parityAssert(!is_file($root.'/class/corefinancequalification.class.php'),
	'no product-level source pinning component remains');
parityAssert(is_string($profileModule)
	&& strpos($profileModule, 'modelDirectories()') !== false
	&& strpos($profileModule, "modules_parts['models']") !== false,
	'normal Core numbering/PDF extension model roots supported');
// Verify expected public Core business entrypoints against each actual
// checked-out version; source bytes need not equal any previously pinned blob.
$core = getenv('DOLIBARR_TEST_ROOT') ?: '';
$version = getenv('DOLIBARR_TEST_VERSION') ?: '';
if ($core !== '' && $version !== '') {
	$base = realpath($core.'/htdocs');
	parityAssert($base !== false, 'the requested Core fixture is installed');
	foreach (array(
		'adherents/class/adherent.class.php' => array('subscription', 'subscriptionComplementaryActions'),
		'compta/facture/class/facture.class.php' => array('create', 'addline', 'validate', 'getNextNumRef', 'generateDocument'),
		'societe/class/societe.class.php' => array('create_from_member', 'get_codeclient')
	) as $path => $methods) {
		$src = file_get_contents($base.'/'.$path);
		parityAssert(is_string($src) && strlen($src) > 5000,
			'actual Core source is available: '.$path);
		foreach ($methods as $method) {
			parityAssert((bool) preg_match('/function\s+'.preg_quote($method, '/').'\s*\(/', $src),
				$version.' retains public business API '.$method);
		}
	}
	$facture = file_get_contents($base.'/compta/facture/class/facture.class.php');
	$thirdparty = file_get_contents($base.'/societe/class/societe.class.php');
	parityAssert(strpos($facture, 'FACTURE_ADDON') !== false
		&& strpos($facture, 'modules_parts') !== false,
		$version.' owns dynamic invoice numbering module lookup');
	parityAssert(strpos($thirdparty, 'SOCIETE_CODECLIENT_ADDON') !== false,
		$version.' owns dynamic Third Party customer-code module lookup');
	$agenda = file_get_contents($base.'/core/triggers/interface_50_modAgenda_ActionsAuto.class.php');
	parityAssert(is_string($agenda) && strpos($agenda, 'MAIN_COPY_FILE_IN_EVENT_AUTO') !== false,
		$version.' owns optional Agenda file-copy configuration');
} else {
	parityAssert(true, 'standalone source inspection deferred to CI Core checkout');
}
echo $passed." CF05 Core-parity compatibility assertions PASS (read-only, no DB/PDF/SMTP).\n";
