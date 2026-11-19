<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
 * SPDX-License-Identifier: GPL-3.0-or-later
 * Owner-operated, read-only relevant runtime snapshot. No main/login, callbacks,
 * business API, session read/start, SMTP/PDF, SQL-trigger inventory or Lab DML.
 */
if (PHP_SAPI !== 'cli' || ($argv[1] ?? '') !== '--read-only') { exit(77); }
ini_set('display_errors','0'); ini_set('log_errors','0'); error_reporting(E_ALL & ~E_DEPRECATED); ob_start();
$target='/var/lib/dolibarr-test-results/f27-final-runtime-20261119.json';
$finished=false;register_shutdown_function(static function()use(&$finished){if(!$finished){while(ob_get_level())ob_end_clean();echo json_encode(array('status'=>'STOP','reason'=>'Protected Core reader terminated before sanitized completion; no retry'))."\n";}});
if (file_exists($target)) { $finished=true; ob_end_clean(); echo json_encode(array('status'=>'STOP','reason'=>'Evidence exists; do not overwrite/repeat'))."\n"; exit(1); }
class F27ReadStop extends RuntimeException {}
function f27Need($ok,$message) { if(!$ok)throw new F27ReadStop($message); }
$report=array('kind'=>'READ-ONLY LIVE CONFIGURATION; callbacks selected, not executed','time'=>gmdate('c'),'status'=>'STOP');$db=null;
try {
	$root='/home/marcel/dolibarr-i105-v24-mysql/htdocs'; chdir($root);
	require_once $root.'/filefunc.inc.php';
	require_once $root.'/core/class/conf.class.php';
	require_once $root.'/core/class/translate.class.php';
	require_once $root.'/core/lib/security.lib.php';
	require_once $root.'/core/db/mysqli.class.php';
	mysqli_report(MYSQLI_REPORT_OFF);
	f27Need($dolibarr_main_db_name==='dolibarr_i105_v24'&&$dolibarr_main_db_host==='127.0.0.1'&&(int)$dolibarr_main_db_port===3307&&$dolibarr_main_db_prefix==='llx_','Reference identity mismatch');
	$conf=new Conf();
	foreach(array('host','port','name','user','pass','type','prefix','character_set')as$field){$var='dolibarr_main_db_'.$field;$conf->db->$field=$$var??'';}
	$conf->db->dolibarr_main_db_collation=$dolibarr_main_db_collation??'utf8mb4_unicode_ci';
	$conf->db->dolibarr_main_db_encryption=$dolibarr_main_db_encryption??0;$conf->db->dolibarr_main_db_cryptkey=$dolibarr_main_db_cryptkey??'';
	$conf->file->dol_document_root=array('main'=>DOL_DOCUMENT_ROOT);
	foreach(preg_split('/[;,]/',$dolibarr_main_document_root_alt??'')as$i=>$dir)if($dir!=='')$conf->file->dol_document_root['alt'.$i]=$dir;
	class F27ReadNative extends mysqli {
		public static $reads=0;public static $blocked=0;public static $charset=0;
		#[\ReturnTypeWillChange]
		public function query(string $sql,int $mode=MYSQLI_STORE_RESULT) {
			if(preg_match('/^SET collation_connection = [a-z0-9_]+$/Di',$sql)){self::$charset++;return parent::query($sql,$mode);}
			if(!preg_match('/^(SELECT|SHOW COLUMNS)\b/i',$sql)||preg_match('/\b(?:INTO OUTFILE|INTO DUMPFILE|FOR UPDATE|SLEEP|GET_LOCK)\b/i',$sql)||strpos($sql,';')!==false){self::$blocked++;throw new F27ReadStop('Non-read statement refused');}
			self::$reads++;return parent::query($sql,$mode);
		}
	}
	class F27ReadDriver extends DoliDBMysqli { public function connect($host,$login,$passwd,$name,$port=0) { return new F27ReadNative($host,$login,$passwd,$name,$port); } }
	$db=new F27ReadDriver('mysqli',$conf->db->host,$conf->db->user,$conf->db->pass,$conf->db->name,(int)$conf->db->port);
	f27Need($db->ok,'Reference database unavailable');
	$rows=static function($sql)use($db){$r=$db->query($sql);f27Need($r instanceof mysqli_result,'Read failed');$a=$r->fetch_all(MYSQLI_ASSOC);$r->free();return $a;};
	$identity=$rows('SELECT DATABASE() AS db,VERSION() AS version,@@autocommit AS autocommit,@@SESSION.completion_type AS completion_type')[0];
	f27Need($identity['db']==='dolibarr_i105_v24'&&$identity['version']==='8.4.13'&&DOL_VERSION==='24.0.3','Runtime version mismatch');$report['identity']=$identity;$report['php']=PHP_VERSION;$report['driver']=mysqli_get_client_info();
	// Refuse executing unknown local-constant or logger implementations in a reader.
	$pre=$rows('SELECT '.$db->decrypt('name').' AS name,'.$db->decrypt('value').' AS value FROM llx_const WHERE '.$db->decrypt('name').' IN (\'LOCAL_CONSTS_FILES\',\'SYSLOG_HANDLERS\',\'MAIN_MODULE_MULTICOMPANY\') AND entity IN (0,1) ORDER BY entity');$v=array();foreach($pre as$r)$v[$r['name']]=dolDecrypt($r['value']);
	f27Need(empty($v['LOCAL_CONSTS_FILES'])&&empty($v['MAIN_MODULE_MULTICOMPANY']),'Local override/multicompany requires separate bounded reader');
	$handlers=json_decode($v['SYSLOG_HANDLERS']??'[]',true);f27Need(is_array($handlers)&&!array_diff($handlers,array('mod_syslog_file')),'Unknown logger: reader will not instantiate it');
	$langs=new Translate('', $conf);
	f27Need($conf->setValues($db)>=0,'Effective configuration load failed');
	$flags=array();foreach(array('MAIN_AGENDA_ACTIONAUTO_MEMBER_SUBSCRIPTION_CREATE','MAIN_LOGEVENTS_MEMBER_SUBSCRIPTION_CREATE','MAIN_LOGEVENTS_ACTION_CREATE','MAIN_LOGEVENTS_DISABLE_ALL','MAIN_COPY_FILE_IN_EVENT_AUTO','MAIN_STORE_COMPUTED_EXTRAFIELDS','MAIN_EXTRAFIELDS_DISABLED')as$k)$flags[$k]=(bool)getDolGlobalString($k);
	$report['flags']=$flags;$report['modules']=array();foreach(array('agenda','notification','blockedlog','workflow','syslog','societe')as$m)$report['modules'][$m]=isModEnabled($m);
	$report['selected_callbacks']=array();$report['extra_trigger_directory_count']=count($conf->modules_parts['triggers']);
	foreach(array_merge(array('/core/triggers'),$conf->modules_parts['triggers'])as$rel){f27Need(is_string($rel),'Non-scalar trigger directory');$dir=dol_buildpath($rel,0);if(!is_dir($dir))continue;
		foreach(scandir($dir)as$name){if(!preg_match('/^interface_([0-9]+)_([^_]+)_(.+)\.class\.php$/i',$name,$m))continue;
			$module=strtolower(preg_replace('/^mod/i','',$m[2]));if($module!=='all'&&!isModEnabled($module))continue;
			$path=$dir.'/'.$name;f27Need(is_file($path)&&is_readable($path),'Unreadable selected callback');
			$report['selected_callbacks'][]=array('file'=>$name,'module'=>$module,'sha256'=>hash_file('sha256',$path),'standard_core'=>strpos(realpath($path),realpath($root).'/')===0,'selection'=>'actual dispatcher candidate for both actions; action guard evaluated by source review, not executed');
		}
	}
	$report['extrafields']=array();foreach(array('adherent','subscription','actioncomm')as$type){$r=$rows("SELECT COUNT(*) AS definitions,SUM(CASE WHEN fieldcomputed IS NOT NULL AND fieldcomputed<>'' THEN 1 ELSE 0 END) AS computed FROM llx_extrafields WHERE elementtype='".$type."'")[0];$report['extrafields'][$type]=array('definitions'=>(int)$r['definitions'],'computed'=>(int)$r['computed']);}
	$report['engines']=$rows("SELECT TABLE_NAME AS name,ENGINE AS engine FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('llx_adherent','llx_adherent_type','llx_subscription','llx_actioncomm','llx_actioncomm_resources','llx_events','llx_extrafields') ORDER BY TABLE_NAME");
	$report['logger']=array('enabled'=>isModEnabled('syslog'),'level'=>getDolGlobalInt('SYSLOG_LEVEL'),'classes'=>array());foreach($conf->loghandlers as$h){$f=(new ReflectionMethod($h,'export'))->getFileName();$report['logger']['classes'][]=array('class'=>get_class($h),'source'=>basename($f),'sha256'=>hash_file('sha256',$f));}
	$log=str_replace('DOL_DATA_ROOT',DOL_DATA_ROOT,getDolGlobalString('SYSLOG_FILE',DOL_DATA_ROOT.'/dolibarr.log'));$report['logger']['local_file']=strpos($log,'://')===false&&isset($log[0])&&$log[0]==='/';$report['logger']['parent_writable']=is_writable(dirname($log));
	$site='/etc/apache2/sites-enabled/dolibarr-i105-mysql.conf';$text=file_get_contents($site);$path='';if(preg_match('/php_admin_value\s+session.save_path\s+([^\r\n]+)/',$text,$m))$path=trim($m[1]," \t\"'");
	$handler=ini_get('session.save_handler');foreach(array_merge(array('/etc/php/8.2/apache2/php.ini'),glob('/etc/php/8.2/apache2/conf.d/*.ini'))as$iniFile){$ini=file_get_contents($iniFile);if(preg_match('/^session.save_handler\s*=\s*([^;\r\n]+)/m',$ini,$m))$handler=trim($m[1]);}if(preg_match('/php_admin_value\s+session.save_handler\s+([^\r\n]+)/',$text,$m))$handler=trim($m[1]," \t\"'");
	$report['session']=array('apache_declared_handler'=>$handler,'vhost_path_present'=>$path!=='','path_local'=>isset($path[0])&&$path[0]==='/'&&strpos($path,'://')===false,'path_readable'=>$path!==''&&is_readable($path),'path_writable'=>$path!==''&&is_writable($path),'session_started'=>false,'observation'=>'Apache ini/vhost declaration and www-data filesystem access; reader SAPI is CLI, not an Apache request');
	$report['core_sources']=array();foreach(array('core/class/conf.class.php','core/class/interfaces.class.php','core/triggers/interface_50_modAgenda_ActionsAuto.class.php')as$f)$report['core_sources'][$f]=hash_file('sha256',$root.'/'.$f);
	$report['readonly']=array('read_queries'=>F27ReadNative::$reads,'blocked_statements'=>F27ReadNative::$blocked,'initial_driver_charset_statements'=>F27ReadNative::$charset,'business_APIs_called'=>0,'trigger_callbacks_executed'=>0,'session_reads'=>0,'SQL_trigger_checks'=>0);
	$report['status']='PASS';
}catch(Throwable $e){$report['failure']=array('class'=>get_class($e),'reason'=>$e instanceof F27ReadStop?$e->getMessage():'Runtime inspection failed; no raw error or credentials emitted');}
if($db&&$db->connected)$db->close();
$finished=true;ob_end_clean();$f=fopen($target,'x');if(!$f){echo json_encode(array('status'=>'STOP','reason'=>'Cannot create sanitized evidence'))."\n";exit(1);}fwrite($f,json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n");fclose($f);chmod($target,0644);
echo json_encode(array('status'=>$report['status'],'read_only'=>true,'evidence'=>$target,'failure'=>$report['failure']??null))."\n";exit($report['status']==='PASS'?0:1);
