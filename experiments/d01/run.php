<?php
/* SPDX-License-Identifier: GPL-3.0-or-later; real loaded-Core D01 lab only. */
require __DIR__.'/core-lab.php';
if (($argv[1] ?? '') !== '--run-approved' || empty($argv[2])) { exit(77); }
$out = $argv[2];
if (is_file($out) || is_file($out.'.started')) { exit(78); }
d01Save($out.'.started', array('scope'=>'D01 artificial IDs4000..4499 in existing restricted lab; no automatic rerun'));
$cases = array(); $failure = null; $next = 4000; $audit = null;
function testCase($id, callable $f) {
 global $cases;
 D01Native::$case = $id; $offset = count(D01Native::$trace);
 $v = $f(); $v['id']=$id; $v['measurement']='PASS'; $v['trace_range']=array($offset,count(D01Native::$trace)-1);
 $cases[]=$v; echo json_encode(array('case'=>$id,'status'=>'PASS'))."\n"; fflush(STDOUT);
}
function fixture($d, $label) { global $next; $id=$next++; d01Seed($d,$id,$label); return $id; }
function engine($d) { d01Rows($d,"SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='probe'"); }
try {
 $audit = d01Connect();
 $original = d01Rows($audit,'SELECT id,label,n FROM probe ORDER BY id');
 $inspection=json_decode(file_get_contents(__DIR__.'/evidence/inspection.json'),true,512,JSON_THROW_ON_ERROR);
 d01Assert(hash('sha256',json_encode($original))===$inspection['historical_sha256'],'Baseline changed');
 foreach (array('commit','rollback') as $end) {
  testCase('D01-BASE-'.$end,function() use ($audit,$end) {
   $id=fixture($audit,'base-'.$end); $d=d01Connect(); engine($d); $entry=d01Entry($d);
   d01Assert(d01Value($d,$id,true)===0,'Lock/read mismatch'); engine($d);
   d01Q($d,'UPDATE probe SET n=2 WHERE id='.$id);
   d01Assert(d01Value($audit,$id)===0,'Uncommitted write exposed');
   $ret=$d->$end(); $depth=$d->transaction_opened; $persisted=d01Value($audit,$id);
   d01Assert($ret && $depth===0 && $persisted===($end==='commit'?2:0),'End/persistence mismatch'); d01Close($d);
   return array('fixture'=>$id,'entry'=>$entry,'return'=>$ret,'depth'=>$depth,'persisted'=>$persisted);
  });
 }
 foreach(array('commit','rollback') as $inner) {
  testCase('D01-NEST-'.$inner,function() use ($audit,$inner) {
   $id=fixture($audit,'nested-'.$inner); $d=d01Connect(); $entry=d01Entry($d);
   $offset=count(D01Native::$trace); d01Assert($d->begin()===1 && $d->transaction_opened===2,'Nested begin');
   d01Q($d,'UPDATE probe SET n=3 WHERE id='.$id);
   $ret=$d->$inner();d01Assert($ret && $d->transaction_opened===1,'Inner counter');
   $sql=array_column(array_slice(D01Native::$trace,$offset),'sql');
   d01Assert(count($sql)===1 && strpos($sql[0],'UPDATE')===0,'Unexpected inner SQL');
   d01Assert(d01Value($audit,$id)===0,'Nested write persisted early');
   d01Assert($d->commit()===1 && d01Value($audit,$id)===3,'Inner rollback is counter-only, not savepoint rollback');d01Close($d);
   return array('fixture'=>$id,'entry'=>$entry,'inner_return'=>$ret,'inner_SQL'=>$sql,'outer_commit'=>1,'persisted'=>3,'limit'=>'Core nesting uses counters; inner rollback does not undo inner writes');
  });
 }
 $levels=array('READ UNCOMMITTED','READ COMMITTED','REPEATABLE READ','SERIALIZABLE');
 foreach($levels as $session) { foreach($levels as $pending) {
  testCase('D01-ISO-'.str_replace(' ','-',$session).'-'.str_replace(' ','-',$pending),function() use($audit,$session,$pending) {
   $id=fixture($audit,'isolation');$guard=fixture($audit,'guard');$d=d01Connect();$w=d01Connect();
   // Test stimulus on a new disposable connection, never product/session normalization.
   d01Q($d,'SET SESSION TRANSACTION ISOLATION LEVEL '.$session);engine($d);
   $declared=d01Rows($d,'SELECT @@transaction_isolation AS session_default')[0]['session_default'];
   d01Q($d,'SET TRANSACTION ISOLATION LEVEL '.$pending);$entry=d01Entry($d);
   d01Value($d,$guard,true);engine($d);$first=d01Value($d,$id);
   d01Q($w,'SET SESSION innodb_lock_wait_timeout=1');d01Q($w,'UPDATE probe SET n=1 WHERE id='.$id);
   $second=d01Value($d,$id);$current=d01Value($d,$id,true);$third=d01Value($d,$id);
   d01Assert($first===0 && $second===0 && $current===1 && $third===0,'RR behavior failed');
   d01Assert($d->rollback() && $d->transaction_opened===0 && d01Value($audit,$id)===1,'Isolation completion');
   d01Close($d);d01Close($w);return array('fixture'=>$id,'guard'=>$guard,'session'=>$declared,'pending_before_D01'=>$pending,'entry'=>$entry,'reads'=>array($first,$second,$current,$third),'evidence'=>'Repeatable consistent reads plus current locking read; not actual-event isolation proof');
  });
 }}
 testCase('D01-FOREIGN-1568',function() use($audit) {
  $id=fixture($audit,'foreign');$d=d01Connect();$native=$d->db;
  d01Assert($native->query('BEGIN') && $d->transaction_opened===0,'Foreign setup');
  d01Assert($native->query('UPDATE probe SET n=7 WHERE id='.$id),'Foreign write');
  $offset=count(D01Native::$trace);$set=$d->query('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');$errno=$native->errno;$core_errno=$d->lasterrno;
  $attempt=array_slice(D01Native::$trace,$offset);
  d01Assert($set===false && $errno===1568 && count($attempt)===1 && $d->transaction_opened===0,'Foreign entry not rejected');
  d01Assert(d01Value($audit,$id)===0 && d01Value($d,$id)===7,'Foreign work disturbed');
  // The explicit fault-originator ends its OWN synthetic foreign transaction. Not guard cleanup.
  d01Assert($native->query('ROLLBACK') && d01Value($audit,$id)===0,'Foreign owner cleanup');d01Close($d);
  return array('fixture'=>$id,'native_errno'=>$errno,'Core_errno'=>$core_errno,'attempt_SQL'=>array_column($attempt,'sql'),'foreign_end_by_admission'=>0,'foreign_uncommitted_value'=>7,'cleanup'=>'single native rollback by fault-originator after rejection');
 });
 foreach(array(0,1,2) as $mode) { foreach(array('commit','rollback') as $end) {
  testCase('D01-COMPLETION-'.$mode.'-'.$end,function() use($audit,$mode,$end) {
   $id=fixture($audit,'completion');$d=d01Connect(); d01Q($d,'SET SESSION completion_type='.$mode);
   $pre=d01Rows($d,'SELECT @@autocommit AS ac,@@completion_type AS completion_type')[0];$entry=d01Entry($d);d01Value($d,$id,true);d01Q($d,'UPDATE probe SET n=4 WHERE id='.$id);
   $ret=$d->$end();$depth=$d->transaction_opened;$persisted=d01Value($audit,$id);
   // Observe subsequent admission, WITHOUT attempting any foreign/replacement rollback.
   $nextSet=$d->query('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');$errno=$d->db->errno;$continued=false;
   if($nextSet!==false) { $continued=$d->begin()===1; if($continued) { $d->rollback(); } }
   d01Assert($ret && $depth===0 && $persisted===($end==='commit'?4:0),'Completion acknowledgement/data mismatch');
   d01Close($d);
   return array('fixture'=>$id,'settings'=>$pre,'entry'=>$entry,'Core_return'=>$ret,'Core_depth'=>$depth,'persisted'=>$persisted,'next_SET_ok'=>$nextSet!==false,'next_SET_errno'=>$errno,'next_begin'=>$continued,'observation'=>'No normalization or completion whitelist; next SET failure/connection error stops the next operation');
  });
 }}
 foreach(array('SELECT 1','SELECT n FROM probe WHERE id=1') as $prefix) {
  testCase('D01-INTERVENING-'.(strpos($prefix,'FROM')!==false?'InnoDB':'constant'),function() use($audit,$prefix) {
   $id=fixture($audit,'intervening');$d=d01Connect();$w=d01Connect();d01Q($d,'SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');
   d01Q($d,'SET TRANSACTION ISOLATION LEVEL REPEATABLE READ'); d01Rows($d,$prefix);$begin=$d->begin();
   $first=d01Value($d,$id);d01Q($w,'UPDATE probe SET n=1 WHERE id='.$id);$second=d01Value($d,$id);$d->rollback();d01Close($d);d01Close($w);
   return array('fixture'=>$id,'prefix'=>$prefix,'Core_begin'=>$begin,'first'=>$first,'second'=>$second,'RR_retained'=>$second===0,'classification'=>'negative ordering control outside supported D01; observational result');
  });
 }
 testCase('D01-LATE-OVERRIDE',function() use($audit) {
  $id=fixture($audit,'override');$d=d01Connect();$w=d01Connect();$entry=d01Entry($d);d01Assert($d->db->query('COMMIT')===true,'Injected early end');d01Assert($d->db->query('SET TRANSACTION ISOLATION LEVEL READ COMMITTED')===true && $d->db->query('BEGIN')===true,'Injected replacement');
  $first=d01Value($d,$id);d01Q($w,'UPDATE probe SET n=1 WHERE id='.$id);$second=d01Value($d,$id);$ack=$d->commit();
  d01Assert($first===0 && $second===1 && $ack===1 && $d->transaction_opened===0,'Blind spot reproduction');d01Close($d);d01Close($w);
  return array('fixture'=>$id,'entry'=>$entry,'Core_ack'=>$ack,'reads'=>array($first,$second),'classification'=>'EXPECTED LIMIT: honest entry does not detect later same-connection replacement or prove ongoing isolation/identity');
 });
 testCase('D01-STATEMENT-ERROR',function() use($audit) {
  $id=fixture($audit,'statement-error');$d=d01Connect();$entry=d01Entry($d);d01Q($d,'UPDATE probe SET n=5 WHERE id='.$id);
  $r=$d->query('SELECT missing_d01_column FROM probe WHERE id='.$id);$errno=$d->db->errno;$core_errno=$d->lasterrno;
  d01Assert($r===false && $errno===1054 && $d->transaction_opened===1,'Expected statement error');
  $rollback=$d->rollback();d01Assert($rollback && d01Value($audit,$id)===0,'Statement-error rollback');d01Close($d);
  return array('fixture'=>$id,'entry'=>$entry,'native_errno'=>$errno,'Core_errno'=>$core_errno,'rollback_return'=>$rollback,'persisted'=>0,'limit'=>'one known local lab statement; no general CF02 S-class mapping');
 });
 testCase('D01-CLOSED-CONNECTION',function() use($audit) {
  $id=fixture($audit,'disconnect');$d=d01Connect();$entry=d01Entry($d);d01Q($d,'UPDATE probe SET n=6 WHERE id='.$id);$native=$d->db;$native->close();
  $offset=count(D01Native::$trace);$error=null;try{$d->commit();}catch(Throwable $e){$error=get_class($e);}
  $persisted=d01Value($audit,$id);d01Assert($error!==null && $persisted===0 && count(D01Native::$trace)===$offset,'Connection error must escape');
  return array('fixture'=>$id,'entry'=>$entry,'error_class'=>$error,'persisted'=>$persisted,'outcome'=>'UNKNOWN/STOP; no retry or rollback; own client connection deliberately closed before Core commit');
 });
 $final=d01Rows($audit,'SELECT id,label,n FROM probe ORDER BY id');$old=array_values(array_filter($final,static function($r){return (int)$r['id']<4000 || (int)$r['id']>=4500;}));
 d01Assert($old===$original,'Historical lab rows changed');
} catch(Throwable $e) { $failure=array('class'=>get_class($e),'errno'=>$e->getCode(),'reason'=>$e instanceof LogicException?$e->getMessage():'Runtime failure; preserve state, do not rerun'); }
$result=array('format'=>'D01 evidence v1','status'=>$failure?'STOP':'PASS','cases'=>$cases,'failure'=>$failure,'new_fixture_range'=>array(4000,$next-1),'historical_preserved'=>isset($old)&&$old===$original,'trace'=>D01Native::$trace);
d01Save($out,$result);echo json_encode(array('status'=>$result['status'],'cases'=>count($cases),'failure'=>$failure,'evidence'=>$out))."\n";exit($failure?1:0);
