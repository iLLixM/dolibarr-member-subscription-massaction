<?php
/* SPDX-License-Identifier: GPL-3.0-or-later; one B connection, inherited Core SQL. */
require __DIR__.'/core-lab.php';
if (($argv[1]??'')!=='--worker' || !in_array($argv[2]??'',array('correct','prelock-metadata'),true)) exit(77);
$mode=$argv[2];$id=$mode==='correct'?4060:4061;D01Native::$case='D01-LOCK-B-'.$mode;
try {
 $d=d01Connect();
 d01Rows($d,"SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='probe'");
 $entry=d01Entry($d);
 if($mode==='prelock-metadata') d01Rows($d,"SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='probe'");
 echo json_encode(array('ready'=>true,'connection'=>$d->db->thread_id))."\n";fflush(STDOUT);
 d01Assert(trim(fgets(STDIN))==='lock','Handshake');$start=microtime(true);$locked=d01Value($d,$id,true);$end=microtime(true);
 d01Rows($d,"SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='probe'");$read=d01Value($d,$id);$ret=$d->rollback();
 d01Assert($locked===1 && $ret && $d->transaction_opened===0,'Lock/current/rollback');
 d01Assert($read===($mode==='correct'?1:0),'Read view ordering');
 echo json_encode(array('status'=>'PASS','mode'=>$mode,'entry'=>$entry,'lock_start'=>$start,'lock_end'=>$end,'lock_seconds'=>$end-$start,'locked'=>$locked,'consistent'=>$read,'rollback'=>$ret,'trace'=>D01Native::$trace))."\n";
} catch(Throwable $e) {echo json_encode(array('status'=>'STOP','class'=>get_class($e),'reason'=>$e instanceof LogicException?$e->getMessage():'worker runtime failure','trace'=>D01Native::$trace))."\n";exit(1);}
