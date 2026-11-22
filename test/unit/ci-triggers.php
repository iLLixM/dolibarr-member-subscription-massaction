<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
// F38-04: static coverage of full and hosted CI trigger paths.
// PHP 7.4-compatible, offline; no YAML parser dependency or network requests.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__, 2);
$required = array(
	'class/booking.class.php',
	'docs/cf05-06-standard-core-source-qualification.md',
	'docs/cf05-06-standard-core-qualification-plan.md',
	'docs/adr/ADR-0001-core-first-transaction-profiles.md',
	'docs/adr/ADR-0001-risk-register.md',
	'docs/current-development-baseline.md',
	'class/actions_membersubscriptionmassaction.class.php',
	'lib/membersubscriptionmassaction.lib.php',
	'admin/setup.php',
	'langs/de_DE/membersubscriptionmassaction.lang',
	'langs/en_US/membersubscriptionmassaction.lang',
	'class/period.class.php',
	'lib/preview_renderer.lib.php',
	'subscription.php',
	'class/coreguardedrightsdb.class.php',
	'class/corecompatibility.class.php',
	'class/invoiceprofile.class.php',
	'lib/preview.lib.php',
	'lib/batch.lib.php',
	'test/unit/preview.php',
	'test/unit/request.php',
	'test/unit/controller-runtime.php',
	'test/unit/f29-rights.php',
	'test/unit/f29-book-flow.php',
	'test/unit/userreload.php',
	'test/unit/database.php',
	'class/invoice.class.php',
	'class/coreguardedtransaction.class.php',
	'class/transactionprofile.class.php',
	'class/preview.class.php',
	'class/databasestate.class.php',
	'class/activetransactionguard.class.php',
	'class/bookingmember.class.php',
	'class/filebookingcheckpoint.class.php',
	'class/invoicepdf.class.php',
	'class/mail.class.php',
	'lib/booking.lib.php',
	'lib/booking_renderer.lib.php',
	'lib/profile.lib.php',
	'test/unit/coreguarded-session.php',
	'test/unit/terminal.php',
	'test/unit/profile.php',
	'test/unit/booking.php',
	'test/unit/invoice.php',
	'test/unit/invoiceprofile.php',
	'test/unit/cf05-finance.php',
	'test/unit/maildelivery.php',
	'test/unit/invoicepdf.php',
	'test/unit/postcommit.php',
	'test/unit/ci-triggers.php',
	'test/unit/cf05-core-parity.php',
	'.github/workflows/ci.yml',
	'.github/workflows/ci-full.yml',
	'.github/workflows/cf03-php83-hosted.yml',
);
$requiredBroad = array('class/**', 'lib/**', 'admin/**');
function msmCiContainsRequired(array $paths, array $required)
{
	foreach ($required as $path) { if (!isset($paths[$path])) { return false; } }
	return true;
}
foreach (array('ci-full.yml', 'cf03-php83-hosted.yml') as $name) {
	$file = $root.'/.github/workflows/'.$name;
	$source = file_get_contents($file);
	if (!is_string($source) || !preg_match('/(?m)^  pull_request:\s*\n    paths:\s*\n((?:      - [^\n]+\n|      #[^\n]*\n)+)/', $source, $matches)) {
		throw new RuntimeException('F38-04 missing pull_request paths: '.$name);
	}
	$paths = array();
	foreach (explode("\n", $matches[1]) as $line) {
		if (preg_match('/^      - [\x27\x22]([^\x27\x22]+)[\x27\x22]$/', $line, $match)) {
			$paths[$match[1]] = true;
		}
	}
	foreach (array_merge($required, $requiredBroad) as $path) {
		if (!isset($paths[$path])) { throw new RuntimeException('F38-04 uncovered '.$path.' in '.$name); }
	}
	// Synthetic negative check: a formerly missing critical entry MUST fail
	// the same helper that drives the actual workflow coverage verdict.
	$mutated = $paths;
	unset($mutated['class/actions_membersubscriptionmassaction.class.php']);
	if (msmCiContainsRequired($mutated, $required)
		|| !msmCiContainsRequired($paths, array_merge($required, $requiredBroad))) {
		throw new RuntimeException('F38-04 negative coverage self-test failed');
	}
	if (!preg_match('/(?m)^  workflow_dispatch:\s*$/', $source)
		|| !preg_match('/(?m)^        run:\s*\\|\s*\n(?:          [^\n]*\n)*?          php test\\/unit\\/ci-triggers\\.php(?:\\s|$)/', $source)) {
		throw new RuntimeException('F38-04 missing executable manual/static gate in '.$name);
	}
}
$standard = file_get_contents($root.'/.github/workflows/ci.yml');
if (!is_string($standard)
	|| !preg_match('/(?m)^        run:\s*\\|\s*\n(?:          [^\n]*\n)*?          php test\\/unit\\/ci-triggers\\.php(?:\\s|$)/', $standard)) {
	throw new RuntimeException('F38-04 reduced standard PR CI omits static trigger test');
}
// Comment-only occurrences must never pass as executable test gates.
$probe = preg_replace('/(?m)^(          php test\\/unit\\/ci-triggers\\.php)\\s*$/', '          # php test/unit/ci-triggers.php', $standard);
if ($probe === $standard || preg_match('/(?m)^        run:\s*\\|\s*\n(?:          [^\n]*\n)*?          php test\\/unit\\/ci-triggers\\.php(?:\\s|$)/', $probe)) {
	throw new RuntimeException('F38-04 comment-only static gate bypass');
}
echo 'F38-04 full+hosted security path coverage PASS ('.count($required).' exact paths, 3 broad class/lib/admin fallback paths, executable steps and missing-entry negative)'.PHP_EOL;
