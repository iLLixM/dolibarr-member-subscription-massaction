<?php
/* SPDX-License-Identifier: GPL-3.0-or-later
 * Owner-only www-data bridge: protected reference bootstrap, anonymous IPC,
 * short-lived loopback PHP server; no admin SQL, permanent endpoint or core edit.
 * Invoke only through owner-http.py; stdout starts with PRIVATE route capability.
 */
if(PHP_SAPI!=='cli'||($argv[1]??'')!=='--serve-approved'||function_exists('posix_geteuid')&&posix_geteuid()!==33)exit(77);
ini_set('display_errors','0');ini_set('log_errors','0');umask(0077);mysqli_report(MYSQLI_REPORT_OFF);
function ownerNeed($ok,$text){if(!$ok)throw new LogicException($text);}
function ownerReference()
{
 require '/home/marcel/dolibarr-i105-v24-mysql/htdocs/conf/conf.php';
 ownerNeed($dolibarr_main_db_name==='dolibarr_i105_v24'&&$dolibarr_main_db_host==='127.0.0.1'&&(int)$dolibarr_main_db_port===3307&&$dolibarr_main_db_prefix==='llx_','Reference identity mismatch');
 $r=new mysqli($dolibarr_main_db_host,$dolibarr_main_db_user,$dolibarr_main_db_pass,$dolibarr_main_db_name,(int)$dolibarr_main_db_port);$r->set_charset('utf8mb4');return $r;
}
function ownerSnapshot($d)
{
 $out=array();
 foreach(array('adherent','subscription','societe','socpeople','facture','facturedet','actioncomm','actioncomm_resources','element_element','ecm_files','paiement','bank','product')as $table){
  $r=$d->query('SHOW COLUMNS FROM llx_'.$table);ownerNeed((bool)$r,'Reference columns unavailable');$cols=array();$order='';
  while($c=$r->fetch_assoc()){ownerNeed(preg_match('/^[A-Za-z0-9_]+$/D',$c['Field']),'Unsafe column');$cols[]='`'.$c['Field'].'`';if($c['Field']==='rowid'||$c['Field']==='id')$order='`'.$c['Field'].'`';}
  $r=$d->query('SELECT SHA2(JSON_ARRAY('.implode(',',$cols).'),256) AS h FROM llx_'.$table.' ORDER BY '.($order?:'h'));ownerNeed((bool)$r,'Reference hash read failed');$h=hash_init('sha256');$n=0;while($row=$r->fetch_assoc()){hash_update($h,$row['h']."\n");$n++;}$out[$table]=array('rows'=>$n,'sha256'=>hash_final($h));
 }return $out;
}
$p=null;$memory=null;$ref=null;$runtime=null;
try {
 foreach(json_decode(file_get_contents(__DIR__.'/core-pins.json'),true,512,JSON_THROW_ON_ERROR)as $file=>$sha)ownerNeed(hash_file('sha256','/home/marcel/dolibarr-i105-v24-mysql/htdocs/'.$file)===$sha,'Core pin mismatch');
 $request=json_decode(fgets(STDIN),true,512,JSON_THROW_ON_ERROR);$lab=$request['lab']??array();
 ownerNeed(($lab['host']??'')==='127.0.0.1'&&($lab['port']??0)===3307&&($lab['database']??'')==='msmsh01lab'&&($lab['user']??'')==='msmsh01lab','Restricted lab only');
 // Stop only a stranded server from the failed initial AP3 harness, same UID/exact script.
 $reconciled=0;foreach(glob('/proc/[0-9]*/cmdline')as $pf){$pid=(int)basename(dirname($pf));if(@fileowner(dirname($pf))!==33)continue;$args=explode(chr(0),(string)@file_get_contents($pf));if(!in_array(__DIR__.'/http-router.php',$args,true))continue;ownerNeed(in_array('-S',$args,true),'Unexpected old server');ownerNeed(posix_kill($pid,SIGTERM),'Could not stop old dedicated server');for($i=0;$i<50&&posix_kill($pid,0);$i++)usleep(100000);ownerNeed(!posix_kill($pid,0),'Old dedicated server still alive; no forced kill');$reconciled++;}
 $runtime=sys_get_temp_dir().'/dolibarr-ap3-www-'.bin2hex(random_bytes(10));ownerNeed(mkdir($runtime,0700),'Private runtime path');
 $ref=ownerReference();$before=ownerSnapshot($ref);
 $sock=stream_socket_server('tcp://127.0.0.1:0',$errno,$errstr);ownerNeed(is_resource($sock),'Loopback bind');$address=stream_socket_get_name($sock,false);$port=(int)substr(strrchr($address,':'),1);fclose($sock);
 $route='/ap3-private-'.bin2hex(random_bytes(24));
 // Private ephemeral shared memory, mode0600, same www-data UID only. No credential file.
 $payload=json_encode(array('lab'=>$lab,'route'=>$route,'runtime'=>$runtime));$key=random_int(100000,2000000000);$memory=shmop_open($key,'n',0600,strlen($payload));ownerNeed((bool)$memory,'Private memory unavailable');shmop_write($memory,$payload,0);unset($request,$lab,$payload);putenv('AP3_SHM_KEY='.$key);
 $command=array(PHP_BINARY,'-d','display_errors=0','-d','log_errors=0','-d','xdebug.mode=develop','-d','xdebug.show_exception_trace=0','-d','xdebug.show_error_trace=0','-d','session.save_handler=files','-d','session.save_path=/home/marcel/dolibarr-i105-v24-mysql-data/sessions','-d','disable_functions=mail,fsockopen,pfsockopen,stream_socket_client,socket_connect,curl_exec,curl_multi_exec','-S','127.0.0.1:'.$port,__DIR__.'/http-router-r1.php');
 $p=proc_open($command,array(0=>array('file','/dev/null','r'),1=>array('file','/dev/null','a'),2=>array('file','/dev/null','a')),$pipes,$runtime);
 ownerNeed(is_resource($p),'PHP server spawn');
 echo json_encode(array('status'=>'READY','port'=>$port,'route'=>$route,'reference_before'=>$before,'runtime_path'=>$runtime,'prior_dedicated_servers_stopped'=>$reconciled,'profile'=>array('session_handler'=>'files','session_path'=>'existing I105 sessions','bind'=>'127.0.0.1','transports_disabled'=>true)))."\n";fflush(STDOUT);
 while(($line=fgets(STDIN))!==false){$cmd=json_decode($line,true);$op=$cmd['op']??'';
  if($op==='collect'){$out=array();foreach(glob($runtime.'/*.json')as $file){ownerNeed(!is_link($file),'Unexpected result symlink');$out[basename($file)]=json_decode(file_get_contents($file),true,512,JSON_THROW_ON_ERROR);}echo json_encode(array('status'=>'COLLECTED','evidence'=>(object)$out))."\n";fflush(STDOUT);}
  elseif($op==='stop'){proc_terminate($p);$status=proc_close($p);$p=null;$after=ownerSnapshot($ref);echo json_encode(array('status'=>$before===$after?'PASS':'STOP','reference_business_rows_equal'=>$before===$after,'reference_before'=>$before,'reference_after'=>$after,'auth_scope'=>'normal login/session/audit state may change; these13 business tables only','server_terminated'=>true,'runtime_retained'=>$runtime))."\n";fflush(STDOUT);break;}
  else throw new LogicException('Unexpected IPC operation');
 }
}catch(Throwable $e){echo json_encode(array('status'=>'STOP','class'=>get_class($e),'reason'=>$e instanceof LogicException?$e->getMessage():'Owner bridge runtime failure; no secrets emitted'))."\n";fflush(STDOUT);}
finally{if(is_resource($p)){proc_terminate($p);proc_close($p);}if($memory){shmop_write($memory,str_repeat(chr(0),shmop_size($memory)),0);shmop_delete($memory);shmop_close($memory);}if($ref)$ref->close();}
