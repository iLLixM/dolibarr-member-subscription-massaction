<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 * Synthetic one-shot filesystem contract; no DB/HTTP or business writes.
 */
if (PHP_SAPI !== 'cli') { exit(64); }
require_once __DIR__.'/../../tools/cf03-evidence.lib.php';
$count=0;
function evOk($condition,$message)
{
    global $count;
    if (!$condition) { throw new RuntimeException('CF03 Evidence test failure: '.$message); }
    $count++;
}
function evStop($callback,$reason)
{
    try { $callback(); }
    catch (RuntimeException $e) {
        evOk($e->getMessage()===$reason,$reason);
        return;
    }
    throw new RuntimeException('Missing expected STOP: '.$reason);
}
putenv('CF03_EVIDENCE_ROOT=/tmp/cf03-evidence-unit-fixture');
$root=sys_get_temp_dir().'/cf03-evidence-unit-'.bin2hex(random_bytes(8));
evOk(@mkdir($root,0700),'create sandbox');
$site=$root.'/website';
$doc=$site.'/htdocs';
$source=$root.'/checkout';
$private=$root.'/private-proof';
evOk(mkdir($doc,0700,true),'create synthetic webroot');
evOk(mkdir($source,0700),'create checkout');
evOk(mkdir($private,0700),'create evidence root');
$dir=$private.'/cf03-d-testcase05';
evOk(Cf03Evidence::choice('D',$dir,$private)===$private,'D private root selected');
evStop(static function()use($dir){Cf03Evidence::choice('D',$dir,'/tmp/../unsafe');},
    'CF03UnsafeEvidenceRoot');
evStop(static function()use($root){Cf03Evidence::choice('D',$root.'/cf03-d-testcase05',$root.'/');},
    'CF03UnsafeEvidencePath');
evStop(static function()use($private){Cf03Evidence::choice('A',$private.'/cf03-a-testcase05',$private);},
    'CF03EvidenceRootNotPermitted');
evStop(static function()use($site,$doc,$source){
    Cf03Evidence::privateRoot($site,'D',$doc,$source);
},'CF03EvidenceInsideWebOrSource');
evStop(static function()use($source,$doc){
    Cf03Evidence::privateRoot($source,'D',$doc,$source);
},'CF03EvidenceInsideWebOrSource');
$hostSibling=$site.'/cf03-private-evidence';
evOk(mkdir($hostSibling,0700),'create a private sibling of htdocs');
Cf03Evidence::privateRoot($hostSibling,'D',$doc,$source);
evOk(true,'hosting private sibling accepted when strictly outside htdocs');
Cf03Evidence::directory($dir,true,'D',$private,$doc,$source);
evOk(is_dir($dir) && (fileperms($dir)&0077)===0,'private one-shot directory');
evStop(static function()use($dir,$private,$doc,$source){
    Cf03Evidence::directory($dir,true,'D',$private,$doc,$source);
},'CF03EvidenceExistsNoRetry');
Cf03Evidence::json($dir,'baseline.json',array('case'=>'CF03','nonce'=>'fixture'));
Cf03Evidence::exclusive($dir,'baseline.sha256',hash_file('sha256',$dir.'/baseline.json')."\n");
$before=Cf03Evidence::readyToVerify($dir);
evOk($before['case']==='CF03','baseline integrity');
evStop(static function()use($dir){
    Cf03Evidence::exclusive($dir,'baseline.json','overwritten');
},'CF03EvidenceExistsNoRetry');
evStop(static function()use($dir){Cf03Evidence::exclusive($dir,'conf.php','leak');},
    'CF03EvidenceWriteForbidden');
Cf03Evidence::exclusive($dir,'verify.started',"one-shot\n");
evStop(static function()use($dir){Cf03Evidence::readyToVerify($dir);},
    'CF03EvidenceExistsNoRetry');
$other=$private.'/cf03-d-testcase06';
Cf03Evidence::directory($other,true,'D',$private,$doc,$source);
Cf03Evidence::json($other,'baseline.json',array('case'=>'CF03'));
Cf03Evidence::exclusive($other,'baseline.sha256',str_repeat('0',64)."\n");
evStop(static function()use($other){Cf03Evidence::readyToVerify($other);},
    'CF03BaselineIntegrityMismatch');
$symlink=$private.'/cf03-d-testcase07';
evOk(symlink($other,$symlink),'make symlink');
evStop(static function()use($symlink,$private,$doc,$source) {
    Cf03Evidence::directory($symlink,false,'D',$private,$doc,$source);
},'CF03UnsafeEvidenceDirectory');
$open=$root.'/open';
evOk(mkdir($open,0700),'create synthetic evidence root');
// Group/other read is permitted, but parent write by other principals STOPs.
foreach (array('A','B','C','D') as $envName) {
    foreach (array(0700,0750,0755) as $permissions) {
        evOk(chmod($open,$permissions),'set safe root mode');
        clearstatcache(true,$open);
        Cf03Evidence::privateRoot($open,$envName,$doc,$source);
        evOk(true,'readable evidence parent accepted '.$envName.' '.decoct($permissions));
    }
    foreach (array(0770,0777) as $permissions) {
        evOk(chmod($open,$permissions),'set unsafe parent mode');
        clearstatcache(true,$open);
        evStop(static function()use($open,$envName,$doc,$source) {
            Cf03Evidence::privateRoot($open,$envName,$doc,$source);
        },'CF03EvidenceParentUnsafePermissions');
    }
}
// The actual effective read/write result, not the mode alone, governs STOP.
// Privileged CI users may still have access with mode 0500.
evOk(chmod($open,0500),'set restricted root mode');
clearstatcache(true,$open);
if (!is_readable($open) || !is_writable($open)) {
    evStop(static function()use($open,$doc,$source) {
        Cf03Evidence::privateRoot($open,'A',$doc,$source);
    },'CF03EvidenceParentUnavailable');
} else {
    Cf03Evidence::privateRoot($open,'A',$doc,$source);
    evOk(true,'privileged effective access is authoritative');
}
evOk(chmod($open,0755),'restore readable root for private case test');
clearstatcache(true,$open);
$permissiveCase=$open.'/cf03-d-openroot01';
Cf03Evidence::directory($permissiveCase,true,'D',$open,$doc,$source);
evOk((fileperms($permissiveCase) & 0077)===0,
    'case directory remains private under permissive root');
Cf03Evidence::json($permissiveCase,'baseline.json',array('case'=>'root-policy'));
evOk((fileperms($permissiveCase.'/baseline.json') & 0077)===0,
    'baseline file remains private under permissive root');
$rootLink=$root.'/root-link';
evOk(symlink($open,$rootLink),'create linked evidence root');
evStop(static function()use($rootLink,$doc,$source) {
    Cf03Evidence::privateRoot($rootLink,'D',$doc,$source);
},'CF03EvidenceParentUnavailable');
unlink($rootLink);

// Simulate DB failure AFTER a valid case is permanently sealed.
// A later verify for the same case MUST STOP before any external DB work.
$retry=$private.'/cf03-d-testcase08';
Cf03Evidence::directory($retry,true,'D',$private,$doc,$source);
Cf03Evidence::json($retry,'baseline.json',array('case'=>'CF03','environment'=>'D'));
Cf03Evidence::exclusive($retry,'baseline.sha256',
    hash_file('sha256',$retry.'/baseline.json')."\n");
try {
    Cf03Evidence::startVerify($retry,'D',$private,$doc,$source,
        static function($baseline) {
            evOk($baseline['environment']==='D','preflight-free verify admitted');
        });
    throw new RuntimeException('CF03DatabaseReadUnavailable');
} catch (RuntimeException $failure) {
    evOk($failure->getMessage()==='CF03DatabaseReadUnavailable',
        'external DB failure simulated after marker');
}
evOk(is_file($retry.'/verify.started'),'verify attempt sealed before DB failure');
evStop(static function()use($retry,$private,$doc,$source){
    Cf03Evidence::startVerify($retry,'D',$private,$doc,$source,
        static function($baseline) { evOk(true,'unexpected second admission'); });
},'CF03EvidenceExistsNoRetry');
unlink($symlink);
foreach (array($dir,$other,$retry) as $d) {
    foreach (scandir($d) as $f) {
        if($f!=='.' && $f!=='..') { unlink($d.'/'.$f); }
    }
    rmdir($d);
}
unlink($permissiveCase.'/baseline.json');
rmdir($permissiveCase);
rmdir($open);
rmdir($private);
rmdir($source);
rmdir($hostSibling);
rmdir($doc);
rmdir($site);
rmdir($root);
echo 'CF03 Evidence filesystem contracts: '.$count.' assertions PASS (no DB/HTTP).'.PHP_EOL;
