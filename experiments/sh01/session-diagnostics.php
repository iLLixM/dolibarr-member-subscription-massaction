<?php
/* Additional session diagnostics only on the dedicated restricted lab. */
require __DIR__.'/lab.php';
if(PHP_SAPI!=='cli'||($argv[1]??'')!=='--run-approved'||empty($argv[2])||file_exists($argv[2]))exit(77);
$l=new ShLab();$l->case='SESSION-DIAGNOSTICS';$d=$l->connect();$failure=null;
try {
 $l->q($d,"SET SESSION session_track_transaction_info='CHARACTERISTICS'");$tracking=$l->q($d,'SELECT @@session.session_track_transaction_info AS tracking')[0]['tracking'];
 $counter=static function()use($l,$d){return (int)$l->q($d,"SHOW SESSION STATUS LIKE 'Com_commit'")[0]['Value'];};
 $before=$counter();$l->q($d,'COMMIT');$afterIdle=$counter();$l->q($d,'BEGIN');$l->q($d,'COMMIT');$afterReal=$counter();
 $noMarker=$l->q($d,'SAVEPOINT sh01_outside');$missing=null;try{$l->q($d,'RELEASE SAVEPOINT sh01_outside');}catch(mysqli_sql_exception $e){$missing=$e->getCode();}
 $processErr=null;try{$l->q($d,'SELECT trx_id FROM information_schema.innodb_trx WHERE trx_mysql_thread_id=CONNECTION_ID()');}catch(mysqli_sql_exception $e){$processErr=$e->getCode();}
 $sessionTableRows=null;$sessionTableErr=null;try{$sessionTableRows=$l->q($d,"SELECT VARIABLE_VALUE FROM performance_schema.session_status WHERE VARIABLE_NAME='Com_commit'");}catch(mysqli_sql_exception $e){$sessionTableErr=$e->getCode();}
 $l->require($tracking==='CHARACTERISTICS'&&$afterIdle===$before+1&&$afterReal===$afterIdle+1&&$missing===1305&&$processErr!==null&&($sessionTableErr===1142||is_array($sessionTableRows)),'Diagnostics profile unexpected');
 $result=array('measurement'=>'PASS','tracking_variable'=>$tracking,'session_tracker_api_exposed'=>function_exists('mysqli_session_track_get_first'),'idle_commit_counter_increment'=>$afterIdle-$before,'real_commit_counter_increment'=>$afterReal-$afterIdle,'savepoint_outside_tx_ack'=>$noMarker['ack'],'release_outside_errno'=>$missing,'innodb_trx_errno'=>$processErr,'ps_session_status_errno'=>$sessionTableErr,'ps_session_status_rows'=>$sessionTableRows,'interpretation'=>'Session tracker setup and command counters are not an exact completion-event proof exposed by standard mysqli');
}catch(Throwable $e){$failure=array('class'=>get_class($e),'errno'=>$e->getCode(),'assertion'=>$e instanceof LogicException?$e->getMessage():null);$result=array('status'=>'STOP','failure'=>$failure);}
$result['trace']=$l->trace;file_put_contents($argv[2],json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n");$l->close($d);unset($result['trace']);echo json_encode($result)."\n";exit($failure?1:0);
