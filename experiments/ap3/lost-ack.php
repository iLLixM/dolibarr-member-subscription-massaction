<?php
/* SPDX-License-Identifier: GPL-3.0-or-later; actual Core/file-logger reply-loss cases. */
require __DIR__.'/completion.php';
if(PHP_SAPI!=='cli'||($argv[1]??'')!=='--run-approved'||!in_array($argv[2]??'',array('COMMIT','ROLLBACK'),true)||!ctype_digit($argv[3]??''))exit(77);
$kind=$argv[2];$first=$kind==='COMMIT'?6400:6403;$ids=range($first,$first+2);Ap3Trace::$case='AP3-LOST-'.$kind;
try {
 ap3CliLogger(__DIR__.'/../../build-output/ap3-private/file-logger.log');$audit=ap3Connect(null,'independent');foreach($ids as $id)ap3Seed($audit,$id,'lost-'.$kind);
 $d=ap3Connect(null,'lab',(int)$argv[3]);ap3MonitorStart();$checkpoint=__DIR__.'/../../build-output/ap3-private/lost-'.$kind.'-checkpoint.json';
 $result=ap3Batch($d,$ids,strtolower($kind),static function($state)use($checkpoint){return file_put_contents($checkpoint,json_encode($state),LOCK_EX)!==false;});
 $state=$result['results'][$first];ap3Need($state['member']==='UNKNOWN'&&$state['batch']==='STOP'&&!$state['end_ack']&&$result['next_preflight_calls']===1,'Unacknowledged end not stopped');
 ap3Need($result['results'][$first+1]['member']==='NOT_PROCESSED'&&$result['results'][$first+2]['member']==='NOT_PROCESSED','Follower executed');
 $stored=json_decode(file_get_contents($checkpoint),true,512,JSON_THROW_ON_ERROR);ap3Need($stored['member']==='UNKNOWN','Unknown checkpoint missing');
 $values=array();foreach($ids as $id)$values[$id]=ap3Value($audit,$id);ap3Need($values[$first]===($kind==='COMMIT'?1:0)&&$values[$first+1]===0&&$values[$first+2]===0,'Diagnostic independent persistence mismatch');
 $d->close();ap3Need(!array_filter(Ap3Trace::$events,static function($e){return $e['event']==='forbidden_sql_attempt';}),'Unexpected SQL attempt');
 echo json_encode(array('status'=>'PASS','kind'=>$kind,'fixtures'=>$ids,'result'=>$result,'Core_return'=>$state['Core_return'],'persisted'=>$values[$first],'values'=>$values,'monitor'=>ap3Monitor(),'trace'=>Ap3Trace::$events,'classification'=>'UNKNOWN/STOP; independent readback is diagnostic only, no rescue/retry/compensation'))."\n";
}catch(Throwable $e){echo json_encode(array('status'=>'STOP','class'=>get_class($e),'reason'=>$e instanceof LogicException?$e->getMessage():'Runtime failure; do not rerun','trace'=>Ap3Trace::$events))."\n";exit(1);}
