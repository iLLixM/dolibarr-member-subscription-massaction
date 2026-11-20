<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
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
evOk(mkdir($open,0777),'create root to deny');
chmod($open,0755);
evStop(static function()use($open,$doc,$source){
    Cf03Evidence::privateRoot($open,'D',$doc,$source);
},'CF03EvidenceRootNotPrivate');
unlink($symlink);
foreach (array($dir,$other) as $d) {
    foreach (scandir($d) as $f) {
        if($f!=='.' && $f!=='..') { unlink($d.'/'.$f); }
    }
    rmdir($d);
}
rmdir($open);
rmdir($private);
rmdir($source);
rmdir($doc);
rmdir($site);
rmdir($root);
echo 'CF03 Evidence filesystem contracts: '.$count.' assertions PASS (no DB/HTTP).'.PHP_EOL;
