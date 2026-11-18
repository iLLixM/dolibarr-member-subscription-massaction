<?php
/* Real MySQL reply-loss worker, dedicated restricted lab connection only. */
require __DIR__.'/lab.php';
if(PHP_SAPI!=='cli'||($argv[1]??'')!=='--run-approved'||!in_array($argv[2]??'',array('COMMIT','ROLLBACK'),true)||!ctype_digit($argv[3]??''))exit(77);
$kind=$argv[2];$port=(int)$argv[3];$l=new ShLab();$l->case='POC-10-wire-'.$kind;
try {
 $reader=$l->connect();$id=$kind==='COMMIT'?190:191;$l->fixture($reader,$id);
 $d=$l->connect($port);$g=new ShMarkerHypothesis($l,$d);$l->require($g->prepare(),'Prepare failed');$l->q($d,'BEGIN');$l->require($g->beginEvidence(1),'Begin evidence failed');$l->q($d,'UPDATE probe SET n=1 WHERE id='.$id);$l->require($g->checkpoint($d),'Pre-completion marker failed');
 $errno=null;try{$l->q($d,$kind);}catch(mysqli_sql_exception $e){$errno=$e->getCode();}
 $l->require(in_array($errno,array(2006,2013),true),'Missing client transport error');$result=$g->completion($kind,false);$start=count($l->trace);$blocked=false;try{$g->write('UPDATE probe SET n=99 WHERE id='.$id);}catch(LogicException $e){$blocked=true;}
 $l->require($blocked&&$result==='UNKNOWN/STOP'&&count($l->trace)===$start,'Follow-up SQL after missing ack');$n=$l->value($reader,$id);$l->require($n===($kind==='COMMIT'?1:0),'Independent completion result unexpected');
 echo json_encode(array('id'=>$l->case,'measurement'=>'PASS','precondition'=>'Dedicated restricted lab connection through one-shot loopback response-loss proxy','action'=>'Forward exactly one '.$kind.'; proxy consumes its server OK then closes only this connection','expected'=>'Native client connection error; UNKNOWN/STOP; no follow-up DML/rollback','actual'=>array('client_errno'=>$errno,'observer_result'=>$result,'guarded_write_blocked'=>$blocked,'extra_observer_sql'=>0,'independent_n'=>$n,'fixture_id'=>$id),'security_contract'=>'Real reply loss; independent lab postcondition does not redeem missing acknowledgement in production','trace'=>$l->trace))."\n";
 $l->close($d);$l->close($reader);
} catch(Throwable $e){echo json_encode(array('status'=>'STOP','error_class'=>get_class($e),'errno'=>$e->getCode(),'assertion'=>$e instanceof LogicException?$e->getMessage():null,'trace'=>$l->trace))."\n";exit(1);}
