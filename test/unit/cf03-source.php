<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
 * SPDX-License-Identifier: GPL-3.0-or-later
 * Synthetic filesystem source attestation. No SQL/HTTP/business writes.
 */
if (PHP_SAPI!=='cli') { exit(64); }
require_once __DIR__.'/../../tools/cf03-source.lib.php';
$n=0;
function sourceOk($ok,$label) {
    global $n;
    if (!$ok) { throw new RuntimeException('CF03 source unit failed: '.$label); }
    $n++;
}
function sourceStop($call,$reason) {
    try { $call(); }
    catch (RuntimeException $e) {
        sourceOk($e->getMessage()===$reason,$reason.' got '.$e->getMessage());
        return;
    }
    throw new RuntimeException('CF03 source unit not denied: '.$reason);
}
$module=dirname(__DIR__,2);
$realPaths=Cf03SourceManifest::modulePaths($module);
foreach (array('class/bookingmember.class.php',
    'class/preview.class.php','class/invoiceprofile.class.php',
    'lib/profile.lib.php','lib/preview.lib.php') as $file) {
    sourceOk(in_array($file,$realPaths,true),'transitive module PHP '.$file);
}
foreach (array('contribution','invoice-existing') as $scenario) {
    $core=Cf03SourceManifest::corePaths('24.0.3',$scenario);
    sourceOk(in_array('adherents/class/subscription.class.php',$core,true),
        'Core subscription '.$scenario);
    sourceOk(in_array('core/triggers/interface_50_modAgenda_ActionsAuto.class.php',$core,true),
        'Core Agenda trigger '.$scenario);
    sourceOk(in_array('compta/facture/class/facture.class.php',$core,true)
        ===($scenario==='invoice-existing'),
        'invoice path scoped '.$scenario);
}
$root=sys_get_temp_dir().'/cf03-source-'.bin2hex(random_bytes(8));
$checkout=$root.'/checkout';
$installed=$root.'/installed';
$real=realpath($module);
sourceOk(mkdir($checkout,0700,true),'temp checkout created');
sourceOk(mkdir($installed,0700,true),'temp installed created');
foreach (array($checkout,$installed) as $target) {
    foreach ($realPaths as $path) {
        $dir=dirname($target.'/'.$path);
        if (!is_dir($dir)) { sourceOk(mkdir($dir,0700,true),'created subdir'); }
        sourceOk(file_put_contents($target.'/'.$path,'<?php // '.$path."\n")!==false,
            'test source populated');
    }
}
$before=Cf03SourceManifest::deployed($checkout,$installed);
sourceOk(count($before)===count($realPaths),'full installed inventory');
foreach (array('class/invoiceprofile.class.php','class/preview.class.php',
    'class/bookingmember.class.php','lib/profile.lib.php') as $path) {
    $copy=$installed.'/'.$path;
    file_put_contents($copy,'<?php // MUTATION '.$path."\n");
    sourceStop(static function()use($checkout,$installed) {
        Cf03SourceManifest::deployed($checkout,$installed);
    },'CF03DeployedSourceMismatch');
    file_put_contents($copy,'<?php // '.$path."\n");
}
$addon=$installed.'/class/future_dynamic_extension.php';
sourceOk(file_put_contents($addon,'<?php // new source')!==false,'extra PHP fixture');
sourceStop(static function()use($checkout,$installed) {
    Cf03SourceManifest::deployed($checkout,$installed);
},'CF03DeployedSourceMismatch');
unlink($addon);
$coreRoot=$root.'/core';
sourceOk(mkdir($coreRoot,0700,true),'fake Core created');
$coreFiles=Cf03SourceManifest::corePaths('24.0.3','invoice-existing');
foreach ($coreFiles as $path) {
    $dir=dirname($coreRoot.'/'.$path);
    if (!is_dir($dir)) { mkdir($dir,0700,true); }
    sourceOk(file_put_contents($coreRoot.'/'.$path,'<?php // Core '.$path)!==false,
        'fake Core file populated');
}
$coreBefore=Cf03SourceManifest::hashes($coreRoot,$coreFiles,
    'CF03CoreSourceUnavailable');
foreach (array('compta/facture/class/facture.class.php',
    'core/triggers/interface_50_modAgenda_ActionsAuto.class.php',
    'adherents/class/subscription.class.php') as $path) {
    file_put_contents($coreRoot.'/'.$path,'<?php // changed Core');
    $current=Cf03SourceManifest::hashes($coreRoot,$coreFiles,
        'CF03CoreSourceUnavailable');
    sourceOk($current!==$coreBefore,'Core mutation changes fingerprint '.$path);
    file_put_contents($coreRoot.'/'.$path,'<?php // Core '.$path);
}
$cleanup=function($dir)use(&$cleanup) {
    foreach (scandir($dir) as $part) {
        if ($part==='.' || $part==='..') { continue; }
        $name=$dir.'/'.$part;
        if (is_dir($name) && !is_link($name)) { $cleanup($name); }
        else { unlink($name); }
    }
    rmdir($dir);
};
$cleanup($root);
echo 'CF03 W01 source manifest: '.$n.' assertions PASS (no DB/HTTP).'.PHP_EOL;
