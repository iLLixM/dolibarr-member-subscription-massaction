<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
// Included by request.php; isolated workers execute the instrumented real controller.
$controllerBaseline=$passed;$controllerEvidence=array();
foreach (array(array('session','CoreGuarded'),array('logger','CoreGuarded'),array('binding','CoreGuarded'),array('after_dml','CoreGuarded'),array('logger','MariaDBSnapshot'),array('logger','EnhancedMySQL'),array('csrf','CoreGuarded'),array('entity','CoreGuarded')) as $case) {
	$directory=sys_get_temp_dir().'/f28'.bin2hex(random_bytes(8));mkdir($directory,0700);
	$spec=array('case'=>$case[0],'profile'=>$case[1],'method'=>'POST','directory'=>$directory);
	try {
		$execute=static function($method)use(&$spec,$directory){
			$spec['method']=$method;file_put_contents($directory.'/spec.json',json_encode($spec));
			$cmd=escapeshellarg(PHP_BINARY).' -d xdebug.mode=off '.escapeshellarg(__DIR__.'/../fixtures/controller-runtime.php').' --fixture '.escapeshellarg($directory.'/spec.json');
			$lines=array();exec($cmd,$lines,$exit);$result=json_decode(implode("\n",$lines),true);
			verify($exit===0&&is_array($result),'F28 actual controller fixture completion');return $result;
		};
		$postResult=$execute('POST');
		if ($case[1]==='CoreGuarded'&&in_array($case[0],array('session','logger'),true)) {
			verify($postResult['phase']==='confirm'&&$postResult['revision']===5&&$postResult['result_status']===null,'F28 known denial leaves confirmation without UNKNOWN');
			verify($postResult['book_calls']===0&&!in_array('session_closed',$postResult['events'],true),'F28 refusal precedes close/member invocation');
			verify($postResult['flash']===($case[0]==='session'?'MSMCoreGuardedSessionUnsupported':'MSMCoreGuardedLoggerUnsupported'),'F28 precise active-session flash');
			$getResult=$execute('GET');verify($getResult['visible_specific_error']&&$getResult['has_confirmation_form'],'F28 actual redirected GET displays localized diagnosis');
		} elseif ($case[1]==='CoreGuarded'&&in_array($case[0],array('binding','after_dml'),true)) {
			verify($postResult['phase']==='processing'&&$postResult['revision']===6&&$postResult['result_status']==='unknown','F28 post-close failure never unconsumes');
			verify($postResult['book_calls']===($case[0]==='after_dml'?1:0),'F28 renewed binding precedes service');
			$spec['revision']='6'; $replay=$execute('POST');verify($replay['phase']==='processing'&&$replay['book_calls']===0,'F28 consumed replay cannot invoke service');
		} elseif ($case[1]!=='CoreGuarded') {
			verify($postResult['phase']==='done'&&$postResult['result_status']==='success'&&$postResult['book_calls']===1,'F28 legacy profiles do not gain CG runtime restrictions');
		} else { verify($postResult['book_calls']===0&&in_array('forbidden',$postResult['events'],true),'F28 CSRF/entity guards retained'); }
		$controllerEvidence[]=$postResult;
	} finally { foreach(glob($directory.'/*')as$file){unlink($file);}rmdir($directory); }
}
if(getenv('F28_CONTROLLER_TRACE'))file_put_contents(getenv('F28_CONTROLLER_TRACE'),json_encode(array('kind'=>'actual controller with synthetic bootstrap/admission/services; local PHP sessions; no DB/HTTP','cases'=>$controllerEvidence),JSON_PRETTY_PRINT)."\n");
echo ($passed-$controllerBaseline)." F28 controller assertions passed (actual instrumented control flow; no DB/HTTP).\n";
