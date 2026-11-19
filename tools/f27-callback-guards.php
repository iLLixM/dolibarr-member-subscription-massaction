<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
 * SPDX-License-Identifier: GPL-3.0-or-later
 * Execute only selected early-return guards with observed flags and a DB fence.
 * No native DB, protected configuration, business API or Agenda-create invocation.
 */
if(PHP_SAPI!=='cli'||($argv[1]??'')!=='--check'||empty($argv[2])||empty($argv[3]))exit(77);
$root=rtrim($argv[2],'/').'/htdocs';$output=$argv[3];if(file_exists($output))throw new LogicException('Do not overwrite evidence');
$runtime=json_decode(file_get_contents(__DIR__.'/../docs/evidence/f27/f27-runtime-readonly.json'),true);
define('DOL_DOCUMENT_ROOT',$root);require_once $root.'/version.inc.php';require_once $root.'/core/lib/functions.lib.php';require_once $root.'/core/class/conf.class.php';require_once $root.'/core/class/translate.class.php';require_once $root.'/user/class/user.class.php';require_once $root.'/core/db/mysqli.class.php';
$conf=new Conf();foreach($runtime['modules']as$m=>$on)if($on)$conf->modules[$m]=$m;
foreach($runtime['flags']as$k=>$v)$conf->global->$k=$v?1:0;
$conf->file->dol_document_root=array('main'=>$root);$langs=new Translate('',$conf);
class F27NoDatabase extends DoliDBMysqli { public $calls=array();public function __construct(){$this->connected=false;$this->type='mysqli';}public function query($sql,$usesavepoint=0,$type='auto',$result_mode=0){$this->calls[]='query';throw new LogicException('Unexpected DB method in no-op guard');} }
$db=new F27NoDatabase();$user=new User($db);$object=new stdClass();$cases=array();
foreach($runtime['selected_callbacks']as$c){$file=$root.'/core/triggers/'.$c['file'];if(hash_file('sha256',$file)!==$c['sha256'])throw new LogicException('Observed source drift');require_once $file;}
$definitions=array(array('InterfaceLogevents','MEMBER_SUBSCRIPTION_CREATE'),array('InterfaceLogevents','ACTION_CREATE'),array('InterfaceContactRoles','MEMBER_SUBSCRIPTION_CREATE'),array('InterfaceContactRoles','ACTION_CREATE'),array('InterfaceActionsAuto','ACTION_CREATE'));
foreach($definitions as$d){list($class,$action)=$d;$trigger=new $class($db);$before=count($db->calls);$return=$trigger->runTrigger($action,$object,$user,$langs,$conf);if($return!==0||count($db->calls)!==$before)throw new LogicException('Guard did not return without DB');$cases[]=array('class'=>$class,'action'=>$action,'return'=>$return,'DB_calls'=>array_slice($db->calls,$before));}
$record=array('kind'=>'Pinned actual Core guard methods, observed flag inputs, synthetic objects/DB-rejecting spy; no native DB','Core'=>'5c6c7ff6a5112413b1c27221c8d11750afad56f8','cases'=>$cases,'native_DB_connections'=>0,'business_DML'=>0,'Agenda_MEMBER_SUBSCRIPTION_CREATE'=>'NOT EXECUTED; it is the bounded write path, reviewed statically');
file_put_contents($output,json_encode($record,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n");echo json_encode(array('status'=>'PASS','guard_cases'=>count($cases),'DB_calls'=>count($db->calls)))."\n";
