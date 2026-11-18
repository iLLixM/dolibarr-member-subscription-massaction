<?php
/* SPDX-License-Identifier: GPL-3.0-or-later; real Core lost reply, lab-only proxy. */
require __DIR__.'/core-lab.php';
if(($argv[1]??'')!=='--run-approved'||!in_array($argv[2]??'',array('COMMIT','ROLLBACK'),true)||!ctype_digit($argv[3]??''))exit(77);
$kind=$argv[2];$id=$kind==='COMMIT'?4080:4081;D01Native::$case='D01-LOST-'.$kind;
try {
 $audit=d01Connect();d01Seed($audit,$id,'lost-'.strtolower($kind));$d=d01Connect((int)$argv[3]);$entry=d01Entry($d);d01Value($d,$id,true);d01Q($d,'UPDATE probe SET n=9 WHERE id='.$id);
 $end=strtolower($kind);$ret=$d->$end();$errno=$d->db->errno;$core_errno=$d->lasterrno;$depth=$d->transaction_opened;
 // Readback is lab diagnosis only, never recovery/admission of application success.
 $persisted=d01Value($audit,$id);d01Assert(!$ret && in_array($errno,array(2006,2013),true),'Expected lost acknowledgement');
 d01Assert($persisted===($kind==='COMMIT'?9:0),'Unexpected artificial persistence');
 echo json_encode(array('status'=>'PASS','kind'=>$kind,'fixture'=>$id,'entry'=>$entry,'Core_return'=>$ret,'native_errno'=>$errno,'Core_errno'=>$core_errno,'Core_depth'=>$depth,'persisted'=>$persisted,'classification'=>'UNKNOWN/STOP regardless of independent readback; no retry or compensating rollback','trace'=>D01Native::$trace))."\n";
} catch(Throwable $e) {echo json_encode(array('status'=>'STOP','class'=>get_class($e),'reason'=>$e instanceof LogicException?$e->getMessage():'Runtime failure; preserve state','trace'=>D01Native::$trace))."\n";exit(1);}
