<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
error_reporting(E_ALL & ~E_DEPRECATED);
require_once __DIR__.'/../../class/corefinancequalification.class.php';
$passed = 0;
function checkCore($ok, $name)
{
	global $passed;
	if (!$ok) { throw new RuntimeException('CF05-06 FAIL: '.$name); }
	$passed++;
}
function rejectCore(callable $operation, $name)
{
	try { $operation(); }
	catch (RuntimeException $e) {
		checkCore($e->getMessage()==='MSMCoreFinanceUnqualified', $name);
		return;
	}
	throw new RuntimeException('CF05-06 expected rejection: '.$name);
}
$version = getenv('DOLIBARR_TEST_VERSION') ?: '';
$root = getenv('DOLIBARR_TEST_ROOT') ?: '';
checkCore(MsmCoreFinanceQualification::sourceAllowed(
	'22.0.5','b0687548ac6fb52ac4150ba1a2c6fa5b6d5314f9'),
	'pinned V22 invoice source accepted');
checkCore(MsmCoreFinanceQualification::sourceAllowed(
	'23.0.4','b051a68760027703176662e96dd9d00bd0ab62d3'),
	'pinned V23 invoice source accepted');
checkCore(MsmCoreFinanceQualification::sourceAllowed(
	'24.0.3','f019dff8a7389282cf549c0a4ad9c9a665865fcd'),
	'pinned V24 invoice source accepted');
checkCore(!MsmCoreFinanceQualification::sourceAllowed('99.0.0',
	'b0687548ac6fb52ac4150ba1a2c6fa5b6d5314f9'), 'unqualified Core version denied');
checkCore(!MsmCoreFinanceQualification::sourceAllowed('22.0.5',str_repeat('0',40)),
	'patched invoice source denied');
checkCore(!MsmCoreFinanceQualification::triggerAllowed('22.0.5',
	'interface_50_modAgenda_ActionsAuto.class.php',str_repeat('0',40)), 'patched Agenda trigger denied');
checkCore(!MsmCoreFinanceQualification::triggerAllowed('22.0.5',
	'interface_20_all_Evil.class.php','b948ef99484444c3944d9cce1465af87c8f006f7'),
	'foreign all-module trigger rejected');
rejectCore(static function() { MsmCoreFinanceQualification::requireSource('22.0.5', 'modified PHP'); },
	'modified Core source fails before begin');
if ($root !== '' && $version !== '') {
	$invoiceFile = $root.'/htdocs/compta/facture/class/facture.class.php';
	$coreSrc = file_get_contents($invoiceFile);
	checkCore(is_string($coreSrc) && strlen($coreSrc)>150000,
		'pinned real Core invoice source fixture exists');
	MsmCoreFinanceQualification::requireSource($version, $coreSrc);
	checkCore(true, 'real checked-out '.$version.' exact invoice blob accepted');
	foreach (array('interface_50_modAgenda_ActionsAuto.class.php',
		'interface_20_all_Logevents.class.php') as $name) {
		$data = file_get_contents($root.'/htdocs/core/triggers/'.$name);
		checkCore(is_string($data) && MsmCoreFinanceQualification::triggerAllowed($version,$name,
			sha1('blob '.strlen($data)."\0".$data)),
			'pinned '.$version.' qualified trigger source '.$name);
	}
} else {
	checkCore(true, 'standalone source fixture omitted before Core checkout');
}
$GLOBALS['cf05Settings'] = array();
$GLOBALS['cf05Enabled'] = array('agenda'=>true);
function isModEnabled($name) { return !empty($GLOBALS['cf05Enabled'][$name]); }
function getDolGlobalInt($name) { return (int) ($GLOBALS['cf05Settings'][$name] ?? 0); }
function getDolGlobalString($name) { return (string) ($GLOBALS['cf05Settings'][$name] ?? ''); }
function dol_buildpath($path, $absolute = 0) { return $GLOBALS['cf05Fixture'].'/extra'; }
function dol_sanitizeFileName($name) { return preg_replace('/[^a-zA-Z0-9()_-]/', '_', $name); }
$temp = sys_get_temp_dir().'/cf05-qual-'.bin2hex(random_bytes(6));
if (!mkdir($temp, 0700) || !mkdir($temp.'/core') || !mkdir($temp.'/extra') || !mkdir($temp.'/docs')) {
	throw new RuntimeException('CF05-06 local test fixture creation failed');
}
$GLOBALS['cf05Fixture']=$temp;
try {
	MsmCoreFinanceQualification::requireTriggerPaths($temp.'/core', array(), '22.0.5');
	checkCore(true, 'no enabled unqualified triggers accepted');
	file_put_contents($temp.'/extra/interface_80_modCustom_CreatesFiles.class.php', '<?php');
	$GLOBALS['cf05Enabled']['custom']=true;
	rejectCore(static function()use($temp){
		MsmCoreFinanceQualification::requireTriggerPaths($temp.'/core', array('/custom/triggers'), '22.0.5');
	}, 'external enabled trigger refused before BEGIN');
	$GLOBALS['cf05Enabled']['custom']=false;
	MsmCoreFinanceQualification::requireTriggerPaths($temp.'/core', array('/custom/triggers'), '22.0.5');
	checkCore(true,'disabled extra trigger does not disable standard invoice finance');
	$GLOBALS['cf05Settings']['MAIN_COPY_FILE_IN_EVENT_AUTO']=1;
	rejectCore(static function()use($temp){
		MsmCoreFinanceQualification::requireTriggerPaths($temp.'/core', array(), '22.0.5');
	}, 'Agenda automatic file copies prohibited in admitted finance configuration');
	$GLOBALS['cf05Settings']['MAIN_COPY_FILE_IN_EVENT_AUTO']=0;
	$GLOBALS['cf05Settings']['MAIN_LOGEVENTS_BILL_VALIDATE']=1;
	rejectCore(static function()use($temp){
		MsmCoreFinanceQualification::requireTriggerPaths($temp.'/core', array(), '22.0.5');
	}, 'unqualified Logevents invoice writes prohibited');
	$GLOBALS['cf05Settings']['MAIN_LOGEVENTS_BILL_VALIDATE']=0;
	$conf=(object) array('facture'=>(object) array('dir_output'=>$temp.'/docs'));
	$provisional=(object) array('id'=>42,'ref'=>'(PROV42)');
	MsmCoreFinanceQualification::requireProvisionalPathSafe($provisional);
	checkCore(true,'fresh invoice with absent PROV directory admitted');
	mkdir($temp.'/docs/(PROV42)');
	rejectCore(static function()use($provisional){
		MsmCoreFinanceQualification::requireProvisionalPathSafe($provisional);
	}, 'existing PROV directory rejected before Facture validate');
	rmdir($temp.'/docs/(PROV42)');
	file_put_contents($temp.'/docs/(PROV42)', 'orphan attachment');
	rejectCore(static function()use($provisional){
		MsmCoreFinanceQualification::requireProvisionalPathSafe($provisional);
	}, 'existing PROV file rejected before Facture validate');
	unlink($temp.'/docs/(PROV42)');
	$provisional->ref='(PROV42)/../bad';
	rejectCore(static function()use($provisional){
		MsmCoreFinanceQualification::requireProvisionalPathSafe($provisional);
	}, 'unsafe/foreign invoice provisional reference rejected');
} finally {
	@unlink($temp.'/extra/interface_80_modCustom_CreatesFiles.class.php');
	@rmdir($temp.'/extra'); @rmdir($temp.'/core'); @rmdir($temp.'/docs'); @rmdir($temp);
}
echo $passed." CF05-06 offline source/trigger/PROV-rename qualification assertions PASS (no DB/SMTP).\n";
