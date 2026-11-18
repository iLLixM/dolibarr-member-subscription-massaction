<?php
/* SPDX-License-Identifier: GPL-3.0-or-later; real coordinated two-process locking. */
require __DIR__.'/core-lab.php';
if(($argv[1]??'')!=='--run-approved' || empty($argv[2]) || is_file($argv[2]) || is_file($argv[2].'.started'))exit(77);
d01Save($argv[2].'.started',array('scope'=>'new D01 fixture4060/4061 only'));
$results=array();$failure=null;
try {
 $audit=d01Connect();
 foreach(array('correct','prelock-metadata') as $mode) {
  $id=$mode==='correct'?4060:4061;D01Native::$case='D01-LOCK-A-'.$mode;d01Seed($audit,$id,'lock-'.$mode);
  $a=d01Connect();$entry=d01Entry($a);d01Value($a,$id,true);d01Q($a,'UPDATE probe SET n=1 WHERE id='.$id);
  $p=proc_open(array(PHP_BINARY,'-d','xdebug.mode=off',__DIR__.'/lock-worker.php','--worker',$mode),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
  d01Assert(is_resource($p),'Worker start');stream_set_timeout($pipes[1],10);
  $ready=json_decode(fgets($pipes[1]),true,512,JSON_THROW_ON_ERROR);d01Assert(!empty($ready['ready']),'B readiness');
  fwrite($pipes[0],"lock\n");fflush($pipes[0]);usleep(450000);
  $read=array($pipes[1]);$write=null;$except=null;$pending=stream_select($read,$write,$except,0)===0;
  d01Assert($pending,'B did not wait');$release_start=microtime(true);d01Assert($a->commit()===1,'A commit');$release_end=microtime(true);
  $b=json_decode(fgets($pipes[1]),true,512,JSON_THROW_ON_ERROR);fclose($pipes[0]);fclose($pipes[1]);$stderr=stream_get_contents($pipes[2]);fclose($pipes[2]);$code=proc_close($p);
  d01Assert($code===0 && $stderr==='' && $b['status']==='PASS' && $b['lock_seconds']>=0.40 && $b['lock_start']<$release_start && $b['lock_end']>=$release_start,'Coordinated wait evidence');
  d01Assert(d01Value($audit,$id)===1,'Committed artificial value');
  $results[]=array('fixture'=>$id,'mode'=>$mode,'A_entry'=>$entry,'A_commit_window'=>array($release_start,$release_end),'B_pending_before_A_commit'=>$pending,'B'=>$b,'assessment'=>$mode==='correct'?'Fresh read after lock and metadata':'EXPECTED COUNTEREXAMPLE: pre-lock ENGINE SELECT establishes stale consistent read; ordering is required');d01Close($a);
 }
} catch(Throwable $e) {$failure=array('class'=>get_class($e),'reason'=>$e instanceof LogicException?$e->getMessage():'Runtime failure; do not repeat');}
d01Save($argv[2],array('status'=>$failure?'STOP':'PASS','cases'=>$results,'failure'=>$failure,'trace'=>D01Native::$trace));echo json_encode(array('status'=>$failure?'STOP':'PASS','cases'=>count($results),'failure'=>$failure))."\n";exit($failure?1:0);
