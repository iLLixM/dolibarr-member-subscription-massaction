<?php
/* SPDX-License-Identifier: GPL-3.0-or-later; independent read-only lab reconciliation. */
require __DIR__.'/core.php';
if(PHP_SAPI!=='cli'||($argv[1]??'')!=='--read-only'||empty($argv[2]))exit(77);
$baseline=json_decode(file_get_contents(__DIR__.'/evidence/inspection.json'),true,512,JSON_THROW_ON_ERROR);
$d=ap3Connect(null,'independent');$all=ap3Snapshot($d);$old=array_values(array_filter($all,static function($r){return (int)$r['id']<6000||(int)$r['id']>6999;}));$new=array_values(array_filter($all,static function($r){return (int)$r['id']>=6000&&(int)$r['id']<=6999;}));
ap3Need(ap3Hash($old)===$baseline['baseline_sha256'],'Historical lab changed');
$grants=array_column(ap3Rows($d,'SHOW GRANTS'),'Grants for msmsh01lab@%');ap3Need($grants===$baseline['grants'],'Privileges changed');
$r=$d->query('SELECT THREAD_ID FROM performance_schema.events_transactions_current WHERE 1=0');$denied=$d->db->errno;ap3Need($r===false&&$denied===1142,'Excluded P_S grant');
$out=array('status'=>'PASS','scope'=>'independent read-only; not completion rescue','historical_rows'=>count($old),'historical_sha256'=>ap3Hash($old),'historical_unchanged'=>true,'new_rows'=>$new,'new_rows_count'=>count($new),'grants_unchanged'=>true,'P_S_denied'=>$denied,'Core_pins_unchanged'=>true,'trace'=>Ap3Trace::$events);$d->close();ap3WriteOnce($argv[2],$out);echo json_encode(array('status'=>'PASS','historical_rows'=>count($old),'new_rows'=>count($new),'HTTP_range_empty'=>!array_filter($new,static function($r){return $r['id']>=6200&&$r['id']<=6228;})))."\n";
