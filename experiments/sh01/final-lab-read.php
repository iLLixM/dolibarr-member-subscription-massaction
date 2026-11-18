<?php
/* Final independent SELECTs in the artificial lab only. */
require __DIR__.'/lab.php';
if(PHP_SAPI!=='cli'||($argv[1]??'')!=='--read-only'||empty($argv[2])||file_exists($argv[2]))exit(77);
$l=new ShLab();$d=$l->connect();$rows=$l->q($d,'SELECT id,label,n FROM probe ORDER BY id');$l->require(count($rows)===27 && $rows[0]['id']==='1' && $rows[1]['id']==='2' && $rows[0]['n']==='0' && $rows[1]['n']==='0','Unexpected final lab rows');
$grants=$l->q($d,'SHOW GRANTS');$err=null;try{$l->q($d,'SELECT THREAD_ID FROM performance_schema.events_transactions_current WHERE 1=0');}catch(mysqli_sql_exception $e){$err=$e->getCode();}$l->require($err===1142,'PS permission changed');
$r=array('status'=>'PASS','database'=>'msmsh01lab','rows'=>$rows,'seed_rows_unchanged'=>true,'new_artificial_rows'=>25,'grants'=>$grants,'performance_schema_select_errno'=>$err,'trace'=>$l->trace,'existing_Dolibarr_database_audit'=>'NOT EXECUTED; no test-worker SQL targets it');file_put_contents($argv[2],json_encode($r,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n");$l->close($d);echo json_encode(array('status'=>'PASS','artificial_rows'=>count($rows),'seed_rows_unchanged'=>true,'PS_select_errno'=>$err))."\n";
