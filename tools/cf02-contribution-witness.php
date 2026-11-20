<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
 * SPDX-License-Identifier: GPL-3.0-or-later
 * CLI only; read-only independent MySQL witness for Owner-operated HTTP booking.
 */
if (PHP_SAPI !== 'cli') { exit(64); }
ini_set('display_errors', '0');
umask(0077);
require_once __DIR__.'/cf02-contribution-witness.lib.php';
function cf02Need($ok, $reason) { Cf02ContributionWitnessContract::requireTrue($ok, $reason); }
function cf02Args($argv) {
    $mode = null; $a = array();
    foreach (array_slice($argv, 1) as $v) {
        if (in_array($v, array('--check-only','--baseline','--verify'), true)) {
            cf02Need($mode === null, 'CF02DuplicateMode'); $mode = substr($v, 2); continue;
        }
        cf02Need(strpos($v, '--') === 0 && strpos($v, '=') !== false, 'CF02InvalidArgument');
        list($k,$value) = explode('=', substr($v, 2), 2);
        cf02Need(in_array($k, array('member-id','marker','start','end','amount','agenda','evidence-dir'), true)
            && !isset($a[$k]) && $value !== '', 'CF02InvalidArgument');
        $a[$k] = $value;
    }
    cf02Need($mode !== null, 'CF02MissingMode');
    $e = Cf02ContributionWitnessContract::parameters($a['member-id'] ?? null, $a['marker'] ?? null,
        $a['start'] ?? null, $a['end'] ?? null, $a['amount'] ?? null, $a['agenda'] ?? null);
    $path = $a['evidence-dir'] ?? '';
    if ($mode !== 'check-only' || $path !== '') {
        cf02Need(preg_match('~^/var/lib/dolibarr-test-results/cf02-pos01-[a-z0-9-]{6,40}$~D', $path) === 1
            && !is_link($path), 'CF02UnsafeEvidencePath');
    }
    return array($mode,$e,$path);
}
function cf02Rows($db, $sql) {
    cf02Need(strpos($sql,'SELECT ') === 0 && strpos($sql,';') === false
        && stripos($sql,' FOR UPDATE') === false && stripos($sql,' INTO OUTFILE') === false, 'CF02ForbiddenSQL');
    try {
        $res = $db->query($sql); $out = array();
        while ($row = $res->fetch_assoc()) { $out[] = $row; }
        $res->free(); return $out;
    } catch (Throwable $e) { throw new RuntimeException('CF02ReadUnavailable'); }
}
function cf02One($db, $sql) {
    $rows = cf02Rows($db,$sql);
    cf02Need(count($rows)===1,'CF02UnexpectedRowCount');
    return $rows[0];
}
function cf02Snapshot($db, $e) {
    $id = $e['member_id'];
    $identity = cf02One($db,'SELECT DATABASE() AS schemaname, CURRENT_USER() AS account, VERSION() AS version, @@SESSION.autocommit AS ac, @@SESSION.completion_type AS mode');
    $m = cf02One($db,'SELECT rowid,login,firstname,lastname,email,entity,fk_adherent_type,fk_soc,statut, CAST(datefin AS CHAR) AS datefin FROM llx_adherent WHERE rowid='.$id);
    $t = cf02One($db,'SELECT rowid,statut,amount,duration FROM llx_adherent_type WHERE rowid='.(int)$m['fk_adherent_type']);
    $p = cf02Rows($db,"SELECT value FROM llx_const WHERE name='MEMBERSUBSCRIPTIONMASSACTION_TRANSACTION_PROFILE' AND entity=1");
    cf02Need(count($p) === 1, 'CF02WrongProfile');
    $profile = json_decode($p[0]['value'],true);
    cf02Need(is_array($profile) && count($profile)===4 && ($profile['version']??null)===1
        && ($profile['family']??null)==='mysql' && ($profile['profile']??null)==='CoreGuarded'
        && is_string($profile['nonce']??null) && preg_match('/^[a-f0-9]{32}$/D',$profile['nonce'])===1,'CF02WrongProfile');
    $flags = cf02Rows($db,"SELECT name,value FROM llx_const WHERE name IN ('MAIN_MODULE_AGENDA','MAIN_AGENDA_ACTIONAUTO_MEMBER_SUBSCRIPTION_CREATE') AND entity IN (0,1) ORDER BY entity");
    $f = array(); foreach($flags as $item) { $f[$item['name']] = $item['value']; }
    cf02Need(isset($f['MAIN_MODULE_AGENDA']) && isset($f['MAIN_AGENDA_ACTIONAUTO_MEMBER_SUBSCRIPTION_CREATE'])
        && in_array($f['MAIN_MODULE_AGENDA'],array('0','1'),true)
        && in_array($f['MAIN_AGENDA_ACTIONAUTO_MEMBER_SUBSCRIPTION_CREATE'],array('0','1'),true),'CF02AgendaConfigurationUnknown');
    $engineRows = cf02Rows($db,"SELECT TABLE_NAME,ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('llx_adherent','llx_adherent_type','llx_subscription','llx_actioncomm','llx_actioncomm_resources')");
    $engines=array(); foreach($engineRows as $row) { $engines[substr($row['TABLE_NAME'],4)]=$row['ENGINE']; }
    $subRows = cf02Rows($db,'SELECT rowid,fk_adherent,fk_type,subscription, CAST(dateadh AS CHAR) AS dateadh,CAST(datef AS CHAR) AS datef,note FROM llx_subscription WHERE fk_adherent='.$id.' ORDER BY rowid');
    $subs=array(); foreach($subRows as $sub) {
        $subs[]=array('id'=>(int)$sub['rowid'],'member_id'=>(int)$sub['fk_adherent'],'type_id'=>(int)$sub['fk_type'],
            'amount'=>$sub['subscription'],'start'=>$sub['dateadh'],'end'=>$sub['datef'],
            'note_contains_marker'=>strpos((string)$sub['note'],$e['marker'])!==false);
    }
    $eventRows=cf02Rows($db,"SELECT id,code,fk_element FROM llx_actioncomm WHERE elementtype='member' AND fk_element=".$id." AND code='AC_MEMBER_SUBSCRIPTION_CREATE' ORDER BY id");
    $events=array();$resources=array();
    foreach($eventRows as $row) {
        $eventId=(int)$row['id'];
        $events[]=array('id'=>$eventId,'code'=>$row['code'],'member_id'=>(int)$row['fk_element']);
        foreach(cf02Rows($db,'SELECT rowid,element_type,fk_element FROM llx_actioncomm_resources WHERE fk_actioncomm='.$eventId.' ORDER BY rowid') as $rr) {
            $resources[]=array('id'=>(int)$rr['rowid'],'agenda_id'=>$eventId,
                'element_type'=>$rr['element_type'],'actor_id'=>(int)$rr['fk_element']);
        }
    }
    $counts=array();foreach(Cf02ContributionWitnessContract::COUNTS as $table) {
        $counts[$table]=(int)cf02One($db,'SELECT COUNT(*) AS n FROM llx_'.$table)['n'];
    }
    $sources=array();foreach(array('subscription.php','class/booking.class.php',
        'class/coreguardedtransaction.class.php','class/transactionprofile.class.php',
        'class/filebookingcheckpoint.class.php','lib/booking.lib.php',
        'tools/cf02-contribution-witness.php','tools/cf02-contribution-witness.lib.php') as $path) {
        $sources[$path]=hash_file('sha256',dirname(__DIR__).'/'.$path);
    }
    return array(
        'identity'=>array('schema'=>$identity['schemaname'],'account'=>$identity['account'],
            'version'=>$identity['version'],'autocommit'=>(string)$identity['ac'],'completion'=>$identity['mode']),
        'profile'=>array('family'=>'mysql','profile'=>'CoreGuarded','raw_hash'=>hash('sha256',$p[0]['value'])),
        'agenda_enabled'=>$f['MAIN_MODULE_AGENDA']==='1' && $f['MAIN_AGENDA_ACTIONAUTO_MEMBER_SUBSCRIPTION_CREATE']==='1',
        'member'=>array('id'=>(int)$m['rowid'],'login'=>$m['login'],'firstname'=>$m['firstname'],
            'lastname'=>$m['lastname'],'email_hash'=>hash('sha256',strtolower((string)$m['email'])),
            'entity'=>(int)$m['entity'],'type_id'=>(int)$m['fk_adherent_type'],
            'fk_soc'=>(int)$m['fk_soc'],'status'=>(int)$m['statut'],'datefin'=>$m['datefin']),
        'type'=>array('id'=>(int)$t['rowid'],'status'=>(int)$t['statut'],'amount'=>$t['amount'],'duration'=>$t['duration']),
        'engines'=>$engines,'subscriptions'=>$subs,'agenda_events'=>$events,
        'agenda_resources'=>$resources,'counts'=>$counts,'source_hashes'=>$sources);
}
function cf02EvidenceDir($path, $create) {
    $parent='/var/lib/dolibarr-test-results';
    cf02Need(is_dir($parent) && realpath($parent)===$parent && !is_link($parent),'CF02EvidenceParentUnavailable');
    if (!file_exists($path)) {
        cf02Need($create && mkdir($path,0700),'CF02EvidenceDirectoryUnavailable');
    }
    cf02Need(!is_link($path) && realpath($path)===$path && (fileperms($path)&0077)===0
        && is_readable($path) && is_writable($path),'CF02UnsafeEvidenceDirectory');
}
function cf02Exclusive($path,$data) {
    $fd=@fopen($path,'x');
    cf02Need($fd!==false,'CF02EvidenceExistsNoRetry');
    try {
        cf02Need(fwrite($fd,$data)===strlen($data) && fflush($fd),'CF02EvidenceWriteFailure');
    } finally { fclose($fd); }
    chmod($path,0600);
}
function cf02JsonFile($path,$data) {
    cf02Exclusive($path,json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");
}
try {
    list($mode,$expected,$path)=cf02Args($argv);
    cf02Need(PHP_MAJOR_VERSION===8 && PHP_MINOR_VERSION===2,'CF02PhpVersionMismatch');
    $root=realpath(getenv('CF02_DOLIBARR_ROOT')?:'');
    cf02Need($root!==false && is_file($root.'/htdocs/conf/conf.php')
        && is_file($root.'/htdocs/version.inc.php')
        && realpath($root.'/htdocs/custom/membersubscriptionmassaction')===realpath(dirname(__DIR__)),'CF02WrongCorePath');
    $v=file_get_contents($root.'/htdocs/version.inc.php');
    cf02Need(preg_match("/define\('DOL_MAJOR_VERSION',\s*'24'\)/",$v)===1
        && preg_match("/define\('DOL_MINOR_VERSION',\s*'0\.3'\)/",$v)===1,'CF02CoreVersionMismatch');
    require $root.'/htdocs/conf/conf.php';
    cf02Need(($dolibarr_main_db_type??'')==='mysqli'
        && ($dolibarr_main_db_host??'')==='127.0.0.1'
        && (int)($dolibarr_main_db_port??0)===3307
        && ($dolibarr_main_db_name??'')==='dolibarr_i105_v24'
        && ($dolibarr_main_db_user??'')==='dolibarr_i105_v24_limited'
        && ($dolibarr_main_db_prefix??'llx_')==='llx_','CF02WrongDatabaseTarget');
    try {
        mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
        $db=new mysqli($dolibarr_main_db_host,$dolibarr_main_db_user,$dolibarr_main_db_pass,$dolibarr_main_db_name,3307);
    } catch (Throwable $e) { throw new RuntimeException('CF02DatabaseConnectionUnavailable'); }
    if($mode==='verify') {
        cf02EvidenceDir($path,false);
        cf02Need(is_file($path.'/baseline.json') && is_file($path.'/baseline.sha256')
            && !is_link($path.'/baseline.json') && !is_link($path.'/baseline.sha256'),'CF02BaselineMissing');
        $sha=trim(file_get_contents($path.'/baseline.sha256'));
        cf02Need(preg_match('/^[a-f0-9]{64}$/D',$sha)===1
            && hash_equals($sha,hash_file('sha256',$path.'/baseline.json')),'CF02BaselineTampered');
        $before=json_decode(file_get_contents($path.'/baseline.json'),true);
        cf02Need(is_array($before) && ($before['case']??null)==='POS01'
            && ($before['expected']??null)===$expected,'CF02BaselineMismatch');
        Cf02ContributionWitnessContract::assertBaseline($before['before'],$expected);
        cf02Exclusive($path.'/verify.started',gmdate('c')."\n");
        $after=cf02Snapshot($db,$expected);
        cf02JsonFile($path.'/after.json',array('case'=>'POS01','after'=>$after,'observed_utc'=>gmdate('c')));
        try { $result=Cf02ContributionWitnessContract::assertPositive($before['before'],$after,$expected); }
        catch (RuntimeException $e) {
            $result=array('status'=>'STOP','case'=>'POS01','reason'=>$e->getMessage(),
                'action'=>'preserve evidence; no automatic repeat or cleanup');
        }
        cf02JsonFile($path.'/verdict.json',$result);
    } else {
        $snapshot=cf02Snapshot($db,$expected);
        Cf02ContributionWitnessContract::assertBaseline($snapshot,$expected);
        if($mode==='baseline') {
            cf02EvidenceDir($path,true);
            $record=array('case'=>'POS01','expected'=>$expected,'before'=>$snapshot,'created_utc'=>gmdate('c'));
            cf02JsonFile($path.'/baseline.json',$record);
            cf02Exclusive($path.'/baseline.sha256',hash_file('sha256',$path.'/baseline.json')."\n");
        }
        $result=array('status'=>'PASS','case'=>'POS01','mode'=>$mode,
            'member_id'=>$expected['member_id'],'agenda_expected'=>$expected['agenda'],
            'writes'=>'no_business_sql');
    }
    echo json_encode($result,JSON_UNESCAPED_SLASHES)."\n";
    exit($result['status']==='PASS'?0:1);
} catch(Throwable $e) {
    $message=$e->getMessage();
    if(strpos($message,'CF02')!==0){$message='CF02UnexpectedRuntimeFailure';}
    echo json_encode(array('status'=>'STOP','reason'=>$message,
        'action'=>'preserve the evidence; do not repeat a started booking'),JSON_UNESCAPED_SLASHES)."\n";
    exit(1);
}
