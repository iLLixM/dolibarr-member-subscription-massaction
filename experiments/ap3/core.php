<?php
/* Copyright (C) 2026 iLLixM; SPDX-License-Identifier: GPL-3.0-or-later
 * AP3 experiment only; never included by module/runtime product code.
 */
if(!in_array(PHP_SAPI,array('cli','cli-server'),true)){http_response_code(404);exit;}
function ap3Need($ok,$label) { if(!$ok) throw new LogicException($label); }
function ap3LoadLibraries()
{
 global $conf,$langs;
 $root=getenv('AP3_CORE')?:'/home/marcel/dolibarr-i105-v24-mysql';
 foreach(json_decode(file_get_contents(__DIR__.'/core-pins.json'),true,512,JSON_THROW_ON_ERROR)as $file=>$sha)ap3Need(hash_file('sha256',$root.'/htdocs/'.$file)===$sha,'Unpinned Core source');
 if(!defined('DOL_DOCUMENT_ROOT')) define('DOL_DOCUMENT_ROOT',$root.'/htdocs');
 if(!defined('DOL_URL_ROOT')) define('DOL_URL_ROOT','');
 if(!defined('DOL_DATA_ROOT')) define('DOL_DATA_ROOT',sys_get_temp_dir());
 require_once DOL_DOCUMENT_ROOT.'/version.inc.php';
 require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
 require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
 require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
 require_once DOL_DOCUMENT_ROOT.'/core/class/conf.class.php';
 require_once DOL_DOCUMENT_ROOT.'/core/class/translate.class.php';
 require_once DOL_DOCUMENT_ROOT.'/core/db/mysqli.class.php';
 require_once DOL_DOCUMENT_ROOT.'/core/modules/syslog/mod_syslog_file.php';
 if(!isset($conf)) { $conf=new Conf(); $conf->db->character_set='utf8mb4';$conf->db->dolibarr_main_db_collation='utf8mb4_unicode_ci';$conf->file->dol_document_root=array('main'=>DOL_DOCUMENT_ROOT);$langs=new Translate('',$conf); }
 mysqli_report(MYSQLI_REPORT_OFF);
}
ap3LoadLibraries();
class Ap3Trace
{
 public static $events=array(); public static $case='inspect';public static $role='lab';
 public static function event($kind,array $data=array()) { self::$events[]=array_merge(array('case'=>self::$case,'event'=>$kind,'time'=>microtime(true)),$data); }
}
class Ap3Native extends mysqli
{
 public $role='lab';public $afterEnd=false;public $allowNext=false;public $monitorAtAck=0;public $connectionId=0;
 #[\ReturnTypeWillChange]
 public function query(string $query,int $result_mode=MYSQLI_STORE_RESULT)
 {
  if($this->afterEnd&&!$this->allowNext) {Ap3Trace::event('forbidden_sql_attempt',array('role'=>$this->role,'connection'=>$this->connectionId,'sql'=>$query));throw new LogicException('SQL attempted after unqualified end');}
  ap3Need(!preg_match('/\b(?:USE|CREATE|DROP|ALTER|TRUNCATE|GRANT|REVOKE|GLOBAL|KILL)\b/i',$query)&&strpos($query,';')===false,'Unsafe lab SQL');
  if(preg_match('/^(INSERT|UPDATE|DELETE|REPLACE)/i',$query)) ap3Need((bool)preg_match('/^(INSERT INTO probe VALUES \(6[0-9]{3},\x27AP3-[a-zA-Z0-9-]+\x27,0\)|UPDATE probe SET n=[0-9]+ WHERE id=6[0-9]{3})$/D',$query),'DML outside new AP3 range');
  $start=microtime(true);$r=parent::query($query,$result_mode);$err=$this->errno;
  if(!$this->connectionId)$this->connectionId=$this->thread_id;
  Ap3Trace::event('sql',array('role'=>$this->role,'connection'=>$this->connectionId,'sql'=>$query,'started'=>$start,'ok'=>$r!==false,'errno'=>$err));
  if(in_array($query,array('COMMIT','ROLLBACK'),true)&&$r) {$this->afterEnd=true;$this->allowNext=false;$this->monitorAtAck=function_exists('xdebug_get_monitored_functions')?count(xdebug_get_monitored_functions()):0;Ap3Trace::event('physical_end_ack',array('connection'=>$this->connectionId,'operation'=>$query,'monitor_count'=>$this->monitorAtAck));}
  return $r;
 }
 #[\ReturnTypeWillChange]
 public function close() { Ap3Trace::event('native_close',array('role'=>$this->role,'connection'=>$this->connectionId));return parent::close(); }
}
class Ap3Core extends DoliDBMysqli
{
 public function connect($host,$login,$passwd,$name,$port=0) {$d=new Ap3Native($host,$login,$passwd,$name,$port);$d->role=Ap3Trace::$role;return $d;}
}
function ap3Config($provided=null)
{
 if($provided===null){$p=getenv('AP3_CONFIG')?:getenv('HOME').'/.config/dolibarr-sh01/app.json';ap3Need(is_file($p)&&(fileperms($p)&0077)===0,'Protected app config required');$provided=json_decode(file_get_contents($p),true,512,JSON_THROW_ON_ERROR);}
 ap3Need($provided['host']==='127.0.0.1'&&$provided['port']===3307&&$provided['database']==='msmsh01lab'&&$provided['user']==='msmsh01lab','Restricted lab only');return $provided;
}
function ap3Connect($config=null,$role='lab',$proxyPort=0)
{
 $c=ap3Config($config);Ap3Trace::$role=$role;$d=new Ap3Core('mysqli',$c['host'],$c['user'],$c['password'],$c['database'],$proxyPort?:$c['port']);unset($c);
 ap3Need($d->ok,'Lab unavailable');$m=ap3Rows($d,'SELECT DATABASE() AS db,CURRENT_USER() AS account,VERSION() AS version,@@autocommit AS ac')[0];
 ap3Need($m['db']==='msmsh01lab'&&$m['account']==='msmsh01lab@%'&&$m['version']==='8.4.13'&&$m['ac']==='1','Lab identity mismatch');return $d;
}
function ap3Rows($d,$sql) {$r=$d->query($sql);ap3Need($r instanceof mysqli_result,'Read failed');$a=$r->fetch_all(MYSQLI_ASSOC);$r->free();return $a;}
function ap3Q($d,$sql) {ap3Need($d->query($sql)!==false,'Unexpected lab SQL failure');}
function ap3Seed($d,$id,$label) {$label=str_replace('_','-',$label);ap3Need($id>=6000&&$id<=6999&&preg_match('/^[A-Za-z0-9-]+$/D',$label),'Bad synthetic fixture');ap3Q($d,"INSERT INTO probe VALUES (".$id.",'AP3-".$label."',0)");}
function ap3Value($d,$id) {return (int)ap3Rows($d,'SELECT n FROM probe WHERE id='.(int)$id)[0]['n'];}
function ap3Snapshot($d) {return ap3Rows($d,'SELECT id,label,n FROM probe ORDER BY id');}
function ap3Hash($rows) {return hash('sha256',json_encode($rows));}
function ap3MonitorStart() {static $started=false;if($started&&function_exists('xdebug_stop_function_monitor'))xdebug_stop_function_monitor();$started=true;if(function_exists('xdebug_start_function_monitor'))xdebug_start_function_monitor(array('dol_syslog','mod_syslog_file->export','LogHandler->setIdent','mod_syslog_file->setIdent','dol_shutdown','DoliDBMysqli->close','Ap3Core->close','Ap3Native->close','session_start','session_write_close','session_abort'));}
function ap3Monitor()
{
 $calls=function_exists('xdebug_get_monitored_functions')?xdebug_get_monitored_functions():array();$out=array();
 foreach($calls as $call){$file=$call['filename'];$out[]=array('function'=>$call['function'],'source'=>strpos($file,DOL_DOCUMENT_ROOT.'/')===0?substr($file,strlen(DOL_DOCUMENT_ROOT)+1):basename($file),'line'=>$call['lineno']);}return $out;
}
function ap3LoggerProfile()
{
 global $conf;
 $classes=array();$safe=isModEnabled('syslog')&&!empty($conf->loghandlers);
 foreach($conf->loghandlers??array() as $h){$class=get_class($h);$r=new ReflectionMethod($h,'export');$file=$r->getFileName();$classes[]=array('class'=>$class,'source'=>strpos($file,DOL_DOCUMENT_ROOT.'/')===0?substr($file,strlen(DOL_DOCUMENT_ROOT)+1):basename($file),'sha256'=>hash_file('sha256',$file));$safe=$safe&&$class==='mod_syslog_file'&&realpath($file)===realpath(DOL_DOCUMENT_ROOT.'/core/modules/syslog/mod_syslog_file.php');}
 return array('supported'=>$safe,'classes'=>$classes,'level'=>getDolGlobalInt('SYSLOG_LEVEL'),'syslog_enabled'=>isModEnabled('syslog'),'scope'=>'Exact pinned standard file logger only; no blanket third-party logger qualification');
}
function ap3CliLogger($file)
{
 global $conf;
 $conf->modules['syslog']=1;$conf->syslog=(object)array('enabled'=>1);$conf->loghandlers=array(new mod_syslog_file());$conf->global->SYSLOG_FILE=$file;$conf->global->SYSLOG_LEVEL=LOG_DEBUG;
}
function ap3InitialMode($d,$mode)
{
 ap3Need(in_array($mode,array('NO_CHAIN','CHAIN','RELEASE'),true),'Initial mode');
 Ap3Trace::event('initial_test_stimulus',array('mode'=>$mode,'authorization'=>'Owner: initial new lab connections only; no adapter normalization'));
 ap3Q($d,"SET SESSION completion_type='".$mode."'");
}
function ap3WriteOnce($file,$data) {$f=fopen($file,'x');ap3Need((bool)$f,'Existing evidence; do not rerun');fwrite($f,json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");fclose($f);}
