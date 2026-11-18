<?php
/* SPDX-License-Identifier: GPL-3.0-or-later; read-only lab and genuine file-logger inspect. */
require __DIR__.'/core.php';
if(PHP_SAPI!=='cli'||($argv[1]??'')!=='--inspect'||empty($argv[2]))exit(77);
try {
 ap3CliLogger(__DIR__.'/../../build-output/ap3-private/file-logger.log');ap3MonitorStart();
 $d=ap3Connect(null,'independent');$rows=ap3Snapshot($d);
 ap3Need(!array_filter($rows,static function($r){return (int)$r['id']>=6000&&(int)$r['id']<=6999;}),'AP3 fixture range occupied; no rerun');
 $grants=array_map(static function($r){return array_values($r)[0];},ap3Rows($d,'SHOW GRANTS'));ap3Need(count($grants)===2&&strpos($grants[1],'SELECT, INSERT, UPDATE, DELETE ON `msmsh01lab`.*')!==false,'Unexpected lab grants');
 $r=$d->query('SELECT THREAD_ID FROM performance_schema.events_transactions_current WHERE 1=0');$ps=$d->db->errno;ap3Need($r===false&&$ps===1142,'Expected P_S denial');
 $logger=ap3LoggerProfile();ap3Need($logger['supported'],'File logger preflight');
 $core=array();foreach(array('core/db/DoliDB.class.php','core/db/mysqli.class.php','core/lib/functions.lib.php','core/modules/syslog/mod_syslog_file.php') as $f)$core[$f]=hash_file('sha256',DOL_DOCUMENT_ROOT.'/'.$f);
 $data=array('status'=>'PASS','php'=>PHP_VERSION,'driver'=>mysqli_get_client_info(),'mysql'=>'8.4.13','dolibarr'=>DOL_VERSION,'Core'=>'5c6c7ff6a5112413b1c27221c8d11750afad56f8','base'=>'c79830976a5f1e1bd57d51005ff34c10c424e36f','logger'=>$logger,'logger_profile'=>'CLI explicit standard file logger/DEBUG; actual HTTP configuration remains separate','grants'=>$grants,'P_S_denied'=>$ps,'baseline_rows'=>count($rows),'baseline_sha256'=>ap3Hash($rows),'core_sha256'=>$core,'trace'=>Ap3Trace::$events,'monitor'=>ap3Monitor());
 ap3WriteOnce($argv[2],$data);echo json_encode(array('status'=>'PASS','historical_rows'=>count($rows),'logger'=>$logger,'P_S_denied'=>$ps))."\n";
}catch(Throwable $e){echo json_encode(array('status'=>'STOP','class'=>get_class($e),'reason'=>$e instanceof LogicException?$e->getMessage():'Runtime inspection failed','trace'=>Ap3Trace::$events))."\n";exit(1);}
