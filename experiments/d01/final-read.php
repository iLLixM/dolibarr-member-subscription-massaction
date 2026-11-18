<?php
/* SPDX-License-Identifier: GPL-3.0-or-later; final independent lab-only readback. */
require __DIR__.'/core-lab.php';
if(($argv[1]??'')!=='--read-only'||empty($argv[2]))exit(77);
$d=d01Connect();$baseline=json_decode(file_get_contents(__DIR__.'/evidence/inspection.json'),true,512,JSON_THROW_ON_ERROR);
$rows=d01Rows($d,'SELECT id,label,n FROM probe ORDER BY id');$old=array_values(array_filter($rows,static function($r){return (int)$r['id']<4000||(int)$r['id']>=4500;}));
d01Assert(hash('sha256',json_encode($old))===$baseline['historical_sha256'],'Historical rows changed');
$expected=array(4000=>2,4001=>0,4002=>3,4003=>3);for($i=4004;$i<=4035;$i++)$expected[$i]=$i%2===0?1:0;
$expected+=array(4036=>0,4037=>4,4038=>0,4039=>4,4040=>0,4041=>4,4042=>0,4043=>1,4044=>1,4045=>1,4046=>0,4047=>0,4060=>1,4061=>1,4080=>9,4081=>0,4090=>12,4091=>0);
$new=array_values(array_filter($rows,static function($r){return (int)$r['id']>=4000&&(int)$r['id']<4500;}));
$actual=array();foreach($new as $r) {d01Assert(strpos($r['label'],'D01-')===0,'Unexpected fixture label');$actual[(int)$r['id']]=(int)$r['n'];}
d01Assert($actual===$expected,'Unexpected new fixture set/values');
foreach($baseline['core_sha256'] as $file=>$sha)d01Assert(hash_file('sha256',DOL_DOCUMENT_ROOT.'/'.$file)===$sha,'Core source changed');
$ps=$d->query('SELECT THREAD_ID FROM performance_schema.events_transactions_current WHERE 1=0');$psErr=$d->db->errno;d01Assert($ps===false&&$psErr===1142,'P_S unexpectedly accessible');
$grants=array_map(static function($r){return array_values($r)[0];},d01Rows($d,'SHOW GRANTS'));d01Assert($grants===$baseline['grants'],'Grants changed');
$result=array('status'=>'PASS','mode'=>'final independent read-only; no retry, no bootstrap/business actions','historical_rows'=>count($old),'historical_sha256'=>hash('sha256',json_encode($old)),'historical_preserved'=>true,'new_synthetic_rows'=>count($new),'expected_values'=>$expected,'Core_sources_unchanged'=>true,'P_S_denied'=>$psErr,'grants_unchanged'=>true,'trace'=>D01Native::$trace);
d01Save($argv[2],$result);echo json_encode(array('status'=>'PASS','historical_rows'=>count($old),'new_synthetic_rows'=>count($new),'Core_sources_unchanged'=>true,'P_S_denied'=>$psErr))."\n";
