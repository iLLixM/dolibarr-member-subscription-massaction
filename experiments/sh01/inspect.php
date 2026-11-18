<?php
/* SH-01 restricted application preflight; no bootstrap, writes or transaction. */
if (PHP_SAPI !== 'cli' || ($argv[1] ?? '') !== '--inspect') { exit(77); }
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
 $path=getenv('SH01_CONFIG') ?: getenv('HOME').'/.config/dolibarr-sh01/app.json';
 $c=json_decode(file_get_contents($path),true,512,JSON_THROW_ON_ERROR);
 if($c['host']!=='127.0.0.1'||$c['port']!==3307||$c['database']!=='msmsh01lab'||$c['user']!=='msmsh01lab')throw new LogicException('Unexpected lab endpoint');
 $d=new mysqli($c['host'],$c['user'],$c['password'],$c['database'],$c['port']);$d->set_charset('utf8mb4');
 $meta=$d->query('SELECT VERSION() AS version,@@version_comment AS edition,DATABASE() AS schema_name,@@autocommit AS autocommit,@@transaction_isolation AS session_isolation')->fetch_assoc();
 $grants=array_map(static function($a){return reset($a);},$d->query('SHOW GRANTS')->fetch_all(MYSQLI_NUM));
 $denied=null;try{$d->query('SELECT THREAD_ID FROM performance_schema.events_transactions_current WHERE 1=0');}catch(mysqli_sql_exception $e){$denied=$e->getCode();}
 $stats=array_keys($d->get_connection_stats());
 echo json_encode(array('mode'=>'read-only restricted application preflight','identity'=>$meta,'grants'=>$grants,'performance_schema_select_error'=>$denied,'mysqli_stat_keys_containing_transaction'=>array_values(preg_grep('/trans|commit|rollback|savepoint/i',$stats)),'mysqli_has_server_status_property'=>property_exists($d,'server_status'),'session_transaction_status_rows'=>$d->query("SHOW SESSION STATUS LIKE '%transaction%'")->fetch_all(MYSQLI_ASSOC)))."\n";
} catch(Throwable $e){echo json_encode(array('status'=>'STOP','error_class'=>get_class($e),'code'=>$e->getCode()))."\n";exit(1);}
