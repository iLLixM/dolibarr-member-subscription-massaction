<?php
/* SPDX-License-Identifier: GPL-3.0-or-later; new artificial lab objects only. */
require __DIR__.'/completion.php';
if(PHP_SAPI!=='cli'||($argv[1]??'')!=='--run-approved'||empty($argv[2])||is_file($argv[2])||is_file($argv[2].'.started'))exit(77);
ap3WriteOnce($argv[2].'.started',array('scope'=>'new AP3 lab range6000..6099 only; no automatic rerun'));
$cases=array();$failure=null;$id=6000;
try {
 ap3CliLogger(__DIR__.'/../../build-output/ap3-private/file-logger.log');$audit=ap3Connect(null,'independent');
 $baseline=json_decode(file_get_contents(__DIR__.'/evidence/inspection.json'),true,512,JSON_THROW_ON_ERROR);ap3Need(ap3Hash(ap3Snapshot($audit))===$baseline['baseline_sha256'],'Baseline changed');
 $plans=array();foreach(array('NO_CHAIN','CHAIN','RELEASE')as $mode)foreach(array('commit','rollback')as $end)$plans[]=array($mode,$end,'');
 foreach(array('unreadable','malformed','inconsistent','close_before_end')as $fault)$plans[]=array($fault==='inconsistent'?'CHAIN':'NO_CHAIN','commit',$fault);
 foreach($plans as $plan){list($mode,$end,$fault)=$plan;Ap3Trace::$case='AP3-CLI-'.$mode.'-'.$end.($fault?'-'.$fault:'');$start=count(Ap3Trace::$events);$ids=range($id,$id+2);$id+=3;
  foreach($ids as $n)ap3Seed($audit,$n,Ap3Trace::$case);
  $d=ap3Connect();ap3InitialMode($d,$mode);ap3MonitorStart();$checkpointFile=__DIR__.'/../../build-output/ap3-private/checkpoint-'.$ids[0].'.json';$writes=0;
  $checkpoint=static function($state)use($checkpointFile,&$writes){$writes++;return file_put_contents($checkpointFile,json_encode($state),LOCK_EX)!==false;};
  $result=ap3Batch($d,$ids,$end,$checkpoint,$fault);
  $expectedContinue=$mode==='NO_CHAIN'&&$fault==='';ap3Need($result['batch']===($expectedContinue?'CONTINUE':'STOP'),'Continuation mismatch');
  $first=$result['results'][$ids[0]];ap3Need($first['member']===($fault==='close_before_end'?'UNKNOWN':($end==='commit'?'SUCCESS':'ROLLBACK_ACKNOWLEDGED')),'Member outcome mismatch');
  ap3Need($result['next_preflight_calls']===($expectedContinue?3:1),'Next-member preflight after STOP');
  foreach(array_slice($ids,1) as $n)ap3Need($expectedContinue?$result['results'][$n]['member']==='SUCCESS':$result['results'][$n]['member']==='NOT_PROCESSED','Unprocessed labeling');
  $lastCheckpoint=json_decode(file_get_contents($checkpointFile),true,512,JSON_THROW_ON_ERROR);ap3Need($lastCheckpoint['member']===($expectedContinue?'SUCCESS':$first['member']),'Independent checkpoint');
  $monitor=ap3Monitor();$ackIndex=$d->db->monitorAtAck;$afterAck=array_slice($monitor,$ackIndex);
  if($fault!=='close_before_end')ap3Need(count(array_filter($afterAck,static function($c){return $c['function']==='dol_syslog';}))>0,'Core post-end logger call missing');
  if($d->connected){$d->close();}
  $events=array_slice(Ap3Trace::$events,$start);ap3Need(!array_filter($events,static function($e){return $e['event']==='forbidden_sql_attempt';}),'Forbidden SQL attempted');
  $persisted=array();foreach($ids as $i=>$n){$persisted[$n]=ap3Value($audit,$n);ap3Need($persisted[$n]===($fault==='close_before_end'||($i===0&&$end==='rollback')||($i>0&&!$expectedContinue)?0:1),'Artificial persistence mismatch');}
  $cases[]=array('id'=>Ap3Trace::$case,'status'=>'PASS','evidence_kind'=>$fault&&$fault!=='close_before_end'?'real Core/DB plus explicitly fault-injected classification observation':'real loaded Core/DB and actual file logging','mode'=>$mode,'operation'=>$end,'fault'=>$fault,'fixtures'=>$ids,'result'=>$result,'checkpoint_writes'=>$writes,'persisted'=>$persisted,'monitor'=>$monitor,'monitor_after_last_physical_ack'=>$afterAck,'trace'=>$events);
  echo json_encode(array('case'=>Ap3Trace::$case,'status'=>'PASS','member'=>$first['member'],'batch'=>$result['batch']))."\n";
 }
 // Unsupported handler types are denied before lab transactions/DML. No unknown logger invoked.
 foreach(array('logger','session')as $kind){Ap3Trace::$case='AP3-UNQUALIFIED-'.$kind;$ids=range($id,$id+2);$id+=3;foreach($ids as $n)ap3Seed($audit,$n,'unsupported-'.$kind);$d=ap3Connect();$saved=$conf->loghandlers;
  if($kind==='logger')$conf->loghandlers=array();$start=count(Ap3Trace::$events);
  $r=ap3Batch($d,$ids,'commit',static function($s){throw new LogicException('Checkpoint must not run');},'',$kind!=='session');
  ap3Need($r['batch']==='STOP'&&$r['next_preflight_calls']===0&&count(Ap3Trace::$events)===$start,'Unqualified path executed work');$conf->loghandlers=$saved;$d->close();
  $cases[]=array('id'=>Ap3Trace::$case,'status'=>'PASS','evidence_kind'=>'synthetic unsupported-profile declaration; actual lab connection unused','result'=>$r,'fixtures'=>$ids,'business_DML'=>0);
 }
 // Own test-originator controls this foreign physical transaction; guard performs no end.
 Ap3Trace::$case='AP3-FOREIGN-1568';$n=$id++;ap3Seed($audit,$n,'foreign');$d=ap3Connect();$d->db->query('BEGIN');$d->db->query('UPDATE probe SET n=8 WHERE id='.$n);$start=count(Ap3Trace::$events);
 $r=ap3Batch($d,array($n),'commit',static function($s){return true;});$guard=array_slice(Ap3Trace::$events,$start);
 ap3Need($r['batch']==='STOP'&&$r['results'][$n]['native_errno']===1568&&!array_filter($guard,static function($e){return ($e['event']==='sql')&&in_array($e['sql'],array('COMMIT','ROLLBACK','BEGIN'),true);})&&ap3Value($audit,$n)===0,'Foreign guard side effect');
 // Fault-originator's own rollback, not an adapter cleanup/retry.
 $d->db->query('ROLLBACK');$d->close();$cases[]=array('id'=>Ap3Trace::$case,'status'=>'PASS','evidence_kind'=>'real foreign physical transaction, Core logical0','fixture'=>$n,'result'=>$r,'guard_trace'=>$guard,'originator_cleanup'=>'one own native rollback after observed guard refusal');
 $all=ap3Snapshot($audit);$old=array_values(array_filter($all,static function($r){return (int)$r['id']<6000||(int)$r['id']>6999;}));ap3Need(ap3Hash($old)===$baseline['baseline_sha256'],'Historical lab rows changed');
}catch(Throwable $e){$failure=array('class'=>get_class($e),'reason'=>$e instanceof LogicException?$e->getMessage():'Runtime failure; preserve state; no rerun');}
ap3WriteOnce($argv[2],array('status'=>$failure?'STOP':'PASS','cases'=>$cases,'failure'=>$failure,'historical_rows_preserved'=>isset($old)&&ap3Hash($old)===$baseline['baseline_sha256'],'new_fixture_range'=>array(6000,$id-1),'logger_profile'=>ap3LoggerProfile(),'trace'=>Ap3Trace::$events));echo json_encode(array('status'=>$failure?'STOP':'PASS','cases'=>count($cases),'failure'=>$failure))."\n";exit($failure?1:0);
