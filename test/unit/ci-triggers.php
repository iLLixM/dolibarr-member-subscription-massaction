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
	'.github/workflows/ci.yml',
	'.github/workflows/ci-full.yml',
	'.github/workflows/cf03-php83-hosted.yml',
);
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
	foreach ($required as $path) {
		if (!isset($paths[$path])) { throw new RuntimeException('F38-04 uncovered '.$path.' in '.$name); }
	}
	if (strpos($source, 'workflow_dispatch:') === false
		|| strpos($source, 'php test/unit/ci-triggers.php') === false) {
		throw new RuntimeException('F38-04 missing manual trigger or static gate in '.$name);
	}
}
$standard = file_get_contents($root.'/.github/workflows/ci.yml');
if (!is_string($standard) || strpos($standard, 'php test/unit/ci-triggers.php') === false) {
	throw new RuntimeException('F38-04 reduced standard PR CI omits static trigger test');
}
echo 'F38-04 full+hosted security path coverage PASS ('.count($required).' exact paths, 3 CI workflows).'.PHP_EOL;
