<?php
/* SPDX-License-Identifier: GPL-3.0-or-later; read-only state reconciliation after harness STOP. */
require __DIR__.'/core-lab.php';
if(($argv[1]??'')!=='--read-only'||empty($argv[2]))exit(77);
$d=d01Connect();$rows=d01Rows($d,'SELECT id,label,n FROM probe ORDER BY id');
$old=array_values(array_filter($rows,static function($r){return (int)$r['id']<4000||(int)$r['id']>=4500;}));
$baseline=json_decode(file_get_contents(__DIR__.'/evidence/inspection.json'),true,512,JSON_THROW_ON_ERROR);
$unchanged=hash('sha256',json_encode($old))===$baseline['historical_sha256'];
$closed=d01Value($d,4047);d01Assert($unchanged && $closed===0,'Unexpected state after harness STOP');
d01Save($argv[2],array('status'=>'PASS','mode'=>'read-only; no retry','historical_preserved'=>$unchanged,'historical_sha256'=>hash('sha256',json_encode($old)),'new_fixtures'=>array_values(array_filter($rows,static function($r){return (int)$r['id']>=4000&&(int)$r['id']<4500;})),'closed_connection_fixture'=>array('id'=>4047,'persisted'=>0),'original_case'=>'PARTIAL: harness counted its independent audit SELECT as an unexpected extra trace; exception class/return not retained in original result, so no retrospective full PASS','trace'=>D01Native::$trace));echo "PASS read-only reconciliation; historical rows preserved, fixture4047=0; no rerun\n";
