<?php
/* Copyright (C) 2026 iLLixM; SPDX-License-Identifier: GPL-3.0-or-later
 * EXPERIMENT ONLY. Fixed isolated schema; no Dolibarr bootstrap or product adapter.
 */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
final class ShLab
{
 public $trace = array(); public $case = 'preflight'; private $config; private $handles; private $next = 0;
 public function __construct() {
  $p=getenv('SH01_CONFIG') ?: getenv('HOME').'/.config/dolibarr-sh01/app.json';
  if ((fileperms($p)&0077)!==0) throw new LogicException('Unsafe config mode');
  $this->config=json_decode(file_get_contents($p),true,512,JSON_THROW_ON_ERROR); $c=$this->config;
  if($c['host']!=='127.0.0.1'||$c['port']!==3307||$c['database']!=='msmsh01lab'||$c['user']!=='msmsh01lab')throw new LogicException('Lab endpoint mismatch');
  $this->handles=new SplObjectStorage();
 }
 public function connect($proxyPort=0) {
  $c=$this->config;$d=mysqli_init();$d->options(MYSQLI_OPT_CONNECT_TIMEOUT,3);$d->options(MYSQLI_OPT_READ_TIMEOUT,8);
  $d->real_connect($c['host'],$c['user'],$c['password'],$c['database'],$proxyPort ?: $c['port']);$d->set_charset('utf8mb4');
  $this->handles[$d]='connection-'.(++$this->next);
  $id=$this->q($d,'SELECT DATABASE() AS db,CURRENT_USER() AS account,VERSION() AS version','harness')[0];
  if($id['db']!=='msmsh01lab'||$id['account']!=='msmsh01lab@%'||$id['version']!=='8.4.13')throw new LogicException('Connected outside restricted lab');
  return $d;
 }
 public function q($d,$sql,$actor='harness') {
  if(preg_match('/\b(?:USE|CREATE|DROP|ALTER|TRUNCATE|GRANT|REVOKE|KILL)\b/i',$sql))throw new LogicException('No administrative/DDL operation in worker');
  $entry=array('case'=>$this->case,'handle'=>$this->handles[$d]??'unbound','actor'=>$actor,'sql'=>$sql);
  try {$r=$d->query($sql);$v=$r instanceof mysqli_result?$r->fetch_all(MYSQLI_ASSOC):array('ack'=>true,'affected_rows'=>$d->affected_rows);$entry['result']=$v;$this->trace[]=$entry;return $v;}
  catch(Throwable $e){$entry['error_class']=get_class($e);$entry['errno']=$e->getCode();$this->trace[]=$entry;throw $e;}
 }
 public function fixture($d,$id) {$this->q($d,"INSERT INTO probe VALUES (".(int)$id.",'SH01-".(int)$id."',0)");}
 public function value($d,$id) {return (int)$this->q($d,'SELECT n FROM probe WHERE id='.(int)$id,'independent-reader')[0]['n'];}
 public function meta($d) {return $this->q($d,'SELECT CONNECTION_ID() AS cid,@@autocommit AS ac,@@transaction_isolation AS session_isolation','hypothesis')[0];}
 public function require($ok,$label){if(!$ok)throw new LogicException($label);}
 public function close($d){try{$d->close();}catch(Throwable $ignored){}}
}
/** Illustrative marker hypothesis, NOT proof of ownership and NOT a product observer.
 * Positive endpoint checks deliberately expose what these primitives can/cannot show.
 * Completion always stays UNKNOWN without an exact-ended-event proof.
 */
final class ShMarkerHypothesis
{
 private $lab;private $db;private $id;private $marker;public $stopped=false;public $reason='';
 public function __construct($lab,$db){$this->lab=$lab;$this->db=$db;$this->id=(string)$db->thread_id;}
 private function stop($reason){$this->stopped=true;$this->reason=$reason;return false;}
 private function same($d){return $d===$this->db && (string)$d->thread_id===$this->id;}
 public function prepare(){
  if($this->stopped)return false;
  try {if(!$this->same($this->db)||$this->lab->meta($this->db)['ac']!=='1')return $this->stop('identity/autocommit');
   $this->lab->q($this->db,'SET TRANSACTION ISOLATION LEVEL REPEATABLE READ','hypothesis');return true;
  }catch(Throwable $e){return $this->stop('prepare errno '.$e->getCode());}
 }
 public function beginEvidence($depth){
  if($this->stopped||$depth!==1)return $this->stop('logical depth not one');
  try {$this->marker='sh01_'.bin2hex(random_bytes(16));$this->lab->q($this->db,'SAVEPOINT '.$this->marker,'hypothesis');return $this->checkpoint($this->db);}
  catch(Throwable $e){return $this->stop('marker errno '.$e->getCode());}
 }
 public function markerForFaultOnly(){return $this->marker;}
 public function checkpoint($db,$betweenReleaseAndCreate=null){
  if($this->stopped)return false;
  try {if(!$this->same($db))return $this->stop('native handle/connection changed');
   $this->lab->q($db,'RELEASE SAVEPOINT '.$this->marker,'hypothesis');
   if($betweenReleaseAndCreate)$betweenReleaseAndCreate();
   $this->marker='sh01_'.bin2hex(random_bytes(16));$this->lab->q($db,'SAVEPOINT '.$this->marker,'hypothesis');return true;
  }catch(Throwable $e){return $this->stop('checkpoint errno '.$e->getCode());}
 }
 public function completion($operation,$ack){$this->stop($ack?'No exact own completion-event proof':'Completion acknowledgement missing');return 'UNKNOWN/STOP';}
 public function write($sql){if($this->stopped)throw new LogicException('Latched UNKNOWN blocks subsequent DML');return $this->lab->q($this->db,$sql,'guarded-lab-write');}
}
/** Source-compatible logical-counter MODEL, not loaded Core / full Dolibarr integration. */
final class ShDepthModel
{
 public $depth=0;private $lab;private $db;
 public function __construct($l,$d){$this->lab=$l;$this->db=$d;}
 public function begin(){if(!$this->depth)$this->lab->q($this->db,'BEGIN','counter-model');$this->depth++;return 1;}
 public function commit(){if($this->depth<=1){$this->lab->q($this->db,'COMMIT','counter-model');$this->depth=0;}else $this->depth--;return 1;}
 public function rollback(){if($this->depth<=1){$this->lab->q($this->db,'ROLLBACK','counter-model');$this->depth=0;}else $this->depth--;return 1;}
}
