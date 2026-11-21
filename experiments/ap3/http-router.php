<?php
/* SPDX-License-Identifier: GPL-3.0-or-later
 * Ephemeral loopback-only router. No module install, public endpoint or fake login.
 */
if(PHP_SAPI!=='cli-server'){http_response_code(404);exit;}
ini_set('display_errors','0');ini_set('log_errors','0');error_reporting(E_ALL&~E_DEPRECATED);
$channel=fopen('php://fd/3','r');if(!$channel){http_response_code(404);exit;}rewind($channel);$private=json_decode(stream_get_contents($channel),true);fclose($channel);
$path=parse_url($_SERVER['REQUEST_URI']??'',PHP_URL_PATH);
if(!is_array($private)||!hash_equals($private['route'],$path??'')||($_SERVER['REMOTE_ADDR']??'')!=='127.0.0.1'){http_response_code(404);exit;}
if(!in_array($_SERVER['REQUEST_METHOD'],array('GET','POST'),true)){http_response_code(405);exit;}
$core='/opt/private-fixture/mysql/htdocs';
// Use actual protected I105 config, PHP file session path and genuine authentication.
chdir($core);define('CSRFCHECK_WITH_TOKEN','1');define('NOREQUIREMENU','1');
require $core.'/main.inc.php';
if(empty($user->id)||($user->login??'')!=='msm-i105'){http_response_code(403);exit;}
require_once __DIR__.'/completion.php';
header('Content-Type: application/json');header('Cache-Control: no-store');
function ap3HttpOut($v){echo json_encode($v,JSON_UNESCAPED_SLASHES);}
try {
 ap3Need(session_status()===PHP_SESSION_ACTIVE,'Authenticated session not active');
 $profile=array('php'=>PHP_VERSION,'sapi'=>PHP_SAPI,'dolibarr'=>DOL_VERSION,'session_handler'=>ini_get('session.save_handler'),'session_path_is_I105'=>session_save_path()==='/opt/private-fixture/mysql-data/sessions','logger'=>ap3LoggerProfile(),'actor'=>(int)$user->id,'business_bootstrap'=>'genuine authenticated main.inc.php; no business API called');
 $action=$_POST['ap3_action']??$_GET['ap3_action']??'inspect';
 if($action==='inspect'){
  ap3HttpOut(array('status'=>'AUTHENTICATED','csrf'=>newToken(),'profile'=>$profile));return;
 }
 if($action==='result'){
  $key=$_GET['case']??'';ap3Need(preg_match('/^[A-Z0-9_-]+$/D',$key),'Bad case');
  $stored=$_SESSION['ap3_completion'][$key]??null;ap3Need(is_array($stored),'No stored case');
  ap3HttpOut(array('status'=>'READ_ONLY_RESULT','stored'=>$stored,'profile'=>$profile));return;
 }
 ap3Need($_SERVER['REQUEST_METHOD']==='POST'&&is_string($_POST['token']??null)&&(hash_equals($_SESSION['token']??'',$_POST['token'])||hash_equals($_SESSION['newtoken']??'',$_POST['token'])),'Invalid POST token');
 ap3Need($action==='run','Bad action');
 $definitions=array();$ordinal=0;
 foreach(array('NO_CHAIN','CHAIN','RELEASE')as $m)foreach(array('commit','rollback')as $end){$definitions['HTTP_'.$m.'_'.strtoupper($end)]=array($m,$end,'',6200+$ordinal*3);$ordinal++;}
 $definitions['HTTP_CHECKPOINT_FAILURE']=array('CHAIN','commit','checkpoint_failure',6220);
 $definitions['HTTP_UNKNOWN_CLOSE']=array('NO_CHAIN','commit','close_before_end',6223);
 $definitions['HTTP_UNREADABLE']=array('NO_CHAIN','commit','unreadable',6226);
 $key=$_POST['case']??'';ap3Need(isset($definitions[$key]),'Unknown fixed HTTP case');
 if(isset($_SESSION['ap3_completion'][$key])){ap3HttpOut(array('status'=>'CONSUMED','stored'=>$_SESSION['ap3_completion'][$key],'lab_connections'=>0));return;}
 ap3Need($profile['session_handler']==='files'&&$profile['session_path_is_I105'],'Unqualified actual session path');
 ap3Need($profile['logger']['supported'],'Unqualified actual logger path');
 $file=$private['runtime'].'/'.$key.'.json';ap3Need(!file_exists($file),'Case evidence exists; no rerun');
 list($mode,$end,$fault,$first)=$definitions[$key];$ids=range($first,$first+2);$originalSessionPath=session_save_path();
 $_SESSION['ap3_completion'][$key]=array('phase'=>'processing','consumed'=>true,'results'=>array_fill_keys($ids,array('member'=>'PENDING_RESULT_RECORD')),'case'=>$key);
 ap3Need(session_write_close(),'Durable consumption failed before DML');
 // Protected reference connection is closed before any lab transaction. No business DML.
 $db->close();$db=null;
 Ap3Trace::$case=$key;ap3MonitorStart();$audit=ap3Connect($private['lab'],'independent');foreach($ids as $id)ap3Seed($audit,$id,$key);
 $lab=ap3Connect($private['lab']);ap3InitialMode($lab,$mode);$db=$lab; // Core shutdown will use regular close() on this lab context.
 $checkpoint=static function($state)use($key,$fault,$private,$originalSessionPath,$ids){
  if($fault==='checkpoint_failure'){
   $blocked=$private['runtime'].'/blocked-checkpoint';ap3Need(!file_exists($blocked),'Checkpoint-fault path exists');mkdir($blocked,0700);chmod($blocked,0000);
   // Real files-handler reopen failure on an exclusively new inaccessible directory.
   // Prior consumption stays durable at the original path. No retry or alternate save.
   session_save_path($blocked);$ok=@session_start();if($ok)session_abort();chmod($blocked,0700);rmdir($blocked);
   Ap3Trace::event('checkpoint_io_failure',array('handler'=>'files','session_start_return'=>$ok,'fault'=>'private inaccessible test path; process-local stimulus'));
   return false;
  }
  if(!session_start())return false;
  if(($_SESSION['ap3_completion'][$key]['phase']??'')!=='processing'){session_abort();return false;}
  $_SESSION['ap3_completion'][$key]['results'][$state['fixture']]=$state;
  if($state['batch']==='STOP'){foreach($_SESSION['ap3_completion'][$key]['results']as $id=>$value){if(($value['member']??'')==='PENDING_RESULT_RECORD')$_SESSION['ap3_completion'][$key]['results'][$id]=array('member'=>'NOT_PROCESSED','reason'=>'terminal_batch');}}
  $_SESSION['ap3_completion'][$key]['batch']=$state['batch'];
  if($state['batch']==='STOP'||$state['fixture']===end($ids))$_SESSION['ap3_completion'][$key]['phase']='done';
  $ok=session_write_close();Ap3Trace::event('session_checkpoint',array('ok'=>$ok,'handler'=>'files','uses_lab_connection'=>false));return $ok;
 };
 $result=ap3Batch($lab,$ids,$end,$checkpoint,$fault==='checkpoint_failure'?'':$fault);
 $monitor=ap3Monitor();$monitorAtAck=$lab->db->monitorAtAck;
 $persisted=array();foreach($ids as $id)$persisted[$id]=ap3Value($audit,$id);
 $audit->close();
 $record=array('case'=>$key,'status'=>'MEASURED','profile'=>$profile,'mode'=>$mode,'operation'=>$end,'fault'=>$fault,'fixtures'=>$ids,'result'=>$result,'persisted'=>$persisted,'monitor'=>$monitor,'monitor_after_last_physical_ack'=>array_slice($monitor,$monitorAtAck),'trace'=>Ap3Trace::$events,'real_scope'=>'authenticated main.inc.php plus restricted artificial lab transactions; no contributions/customer/invoice/mail/PDF');
 // Core registered dol_shutdown earlier. This callback runs AFTER that real close/logger path.
 register_shutdown_function(static function()use($file,$record){$record['shutdown_trace']=Ap3Trace::$events;$record['shutdown_monitor']=ap3Monitor();$record['session_status_at_shutdown']=session_status();ap3WriteOnce($file,$record);});
 ap3HttpOut(array('status'=>'MEASURED','case'=>$key,'result'=>$result,'profile'=>$profile));
}catch(Throwable $e){
 $safe=array('status'=>'STOP','case'=>isset($key)&&is_string($key)?$key:'unknown','class'=>get_class($e),'reason'=>$e instanceof LogicException?$e->getMessage():'Runtime failure; preserve state, no rerun');
 if(isset($private['runtime'])){
  $f=$private['runtime'].'/HTTP_STOP_'.bin2hex(random_bytes(4)).'.json';$safe['trace']=Ap3Trace::$events;ap3WriteOnce($f,$safe);
 }
 http_response_code(409);ap3HttpOut($safe);
}
