<?php
/* SPDX-License-Identifier: GPL-3.0-or-later; uninstrumented exact Core factory control. */
require __DIR__.'/core-lab.php';
if(($argv[1]??'')!=='--run-approved'||empty($argv[2])||is_file($argv[2])||is_file($argv[2].'.started'))exit(77);
d01Save($argv[2].'.started',array('scope'=>'new artificial4090/4091; exact getDoliDBInstance factory, no derived driver'));
$results=array();$failure=null;
try {
 $audit=d01Connect();
 foreach(array('commit','rollback') as $operation) {
  $id=$operation==='commit'?4090:4091;d01Seed($audit,$id,'factory-'.$operation);
  $c=d01Config();$d=getDoliDBInstance('mysqli',$c['host'],$c['user'],$c['password'],$c['database'],$c['port']);unset($c);
  d01Assert(get_class($d)==='DoliDBMysqli' && get_class($d->db)==='mysqli','Exact factory types');
  $identity=d01Rows($d,'SELECT DATABASE() AS db,CURRENT_USER() AS account,VERSION() AS version,@@autocommit AS ac')[0];
  d01Assert($identity['db']==='msmsh01lab'&&$identity['account']==='msmsh01lab@%'&&$identity['version']==='8.4.13'&&$identity['ac']==='1'&&$d->transaction_opened===0,'Factory identity');
  $handle=$d->db;$cid=$handle->thread_id;
  d01Q($d,'SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
  $begin=$d->begin();
  d01Assert($begin===1&&$d->transaction_opened===1&&$d->db===$handle&&$handle->thread_id===$cid,'Exact factory entry');
  d01Value($d,$id,true);d01Q($d,'UPDATE probe SET n=12 WHERE id='.$id);d01Assert(d01Value($audit,$id)===0,'Factory early write exposure');
  $ret=$d->$operation();$persisted=d01Value($audit,$id);d01Assert($ret&&$d->transaction_opened===0&&$persisted===($operation==='commit'?12:0),'Factory completion');
  $results[]=array('operation'=>$operation,'fixture'=>$id,'identity'=>$identity,'begin'=>$begin,'return'=>$ret,'depth'=>$d->transaction_opened,'persisted'=>$persisted,'DoliDB_class'=>get_class($d),'native_class'=>get_class($d->db),'connection'=>$cid,'trace_limit'=>'Exact factory connection is deliberately not instrumented; wire/order corroboration is supplied by inherited-method/native-instrumented cases');d01Close($d);
 }
} catch(Throwable $e){$failure=array('class'=>get_class($e),'reason'=>$e instanceof LogicException?$e->getMessage():'Runtime failure; preserve state');}
d01Save($argv[2],array('status'=>$failure?'STOP':'PASS','cases'=>$results,'failure'=>$failure,'audit_trace'=>D01Native::$trace));echo json_encode(array('status'=>$failure?'STOP':'PASS','cases'=>count($results),'failure'=>$failure))."\n";exit($failure?1:0);
