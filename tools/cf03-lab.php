<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
 * SPDX-License-Identifier: GPL-3.0-or-later
 * CLI read-only lab preflight and one-shot independently reconciled witness.
 * Never books, creates, updates, deletes, retries, or changes DB configuration.
 */
if (PHP_SAPI!=='cli') { exit(64); }
ini_set('display_errors','0');
umask(0077);
require_once __DIR__.'/cf03-lab.lib.php';

function cf03Query($db,$sql)
{
    Cf03LabContract::need(strpos($sql,'SELECT ')===0 && strpos($sql,';')===false
        && stripos($sql,' FOR UPDATE')===false && stripos($sql,' INTO OUTFILE')===false
        && stripos($sql,' INTO DUMPFILE')===false,'CF03ForbiddenSQL');
    try {
        $r=$db->query($sql); $out=array();
        while ($row=$r->fetch_assoc()) { $out[]=$row; }
        $r->free(); return $out;
    } catch (Throwable $e) { throw new RuntimeException('CF03DatabaseReadUnavailable'); }
}
function cf03One($db,$sql)
{
    $rows=cf03Query($db,$sql);
    Cf03LabContract::need(count($rows)===1,'CF03UnexpectedRowCount');
    return $rows[0];
}
function cf03Quoted($db,$value)
{
    return "'".$db->real_escape_string($value)."'";
}
function cf03Source()
{
    $paths=array('subscription.php','class/booking.class.php','class/databasestate.class.php',
        'class/coreguardedtransaction.class.php','class/coreguardedrightsdb.class.php',
        'class/transactionprofile.class.php','class/filebookingcheckpoint.class.php',
        'class/invoice.class.php','lib/booking.lib.php','tools/cf03-lab.php',
        'tools/cf03-lab.lib.php','tools/cf03-evidence.lib.php');
    $out=array();
    foreach ($paths as $path) {
        $file=dirname(__DIR__).'/'.$path;
        Cf03LabContract::need(is_file($file) && !is_link($file),'CF03SourceUnavailable');
        $out[$path]=hash_file('sha256',$file);
    }
    return $out;
}
function cf03Configure($id,array $env)
{
    Cf03LabContract::need(PHP_VERSION_ID>=70400,'CF03PHPUnsupported');
    Cf03LabContract::need(extension_loaded('mysqli'),'CF03MysqliUnavailable');
    $root=realpath($env['document_root']);
    Cf03LabContract::need($root!==false && is_file($root.'/conf/conf.php'),
        'CF03WrongDolibarrRoot');
    $versionPath=Cf03LabContract::versionSource($env['core_version']);
    Cf03LabContract::need(is_file($root.'/'.$versionPath)
        && !is_link($root.'/'.$versionPath),'CF03CoreVersionSourceUnavailable');
    $module=realpath($root.'/custom/membersubscriptionmassaction');
    Cf03LabContract::need($module!==false && is_dir($module)
        && $module===realpath($root.'/custom/membersubscriptionmassaction'),
        'CF03ModuleNotInstalled');
    $checkout=realpath(dirname(__DIR__));
    Cf03LabContract::need($checkout!==false
        && ($id==='D' || $module===$checkout),'CF03WrongModuleSymlink');
    // D is tested from a deployed ZIP copy, not necessarily a checkout symlink.
    // Compare every selected runtime file with this exact CF-03 source checkout.
    $deployed=array();
    foreach (array('subscription.php','class/booking.class.php',
        'class/databasestate.class.php','class/coreguardedtransaction.class.php',
        'class/coreguardedrightsdb.class.php','class/transactionprofile.class.php',
        'class/filebookingcheckpoint.class.php','class/invoice.class.php',
        'lib/booking.lib.php') as $path) {
        $installed=$module.'/'.$path; $source=$checkout.'/'.$path;
        Cf03LabContract::need(is_file($installed) && is_file($source)
            && !is_link($installed) && !is_link($source),'CF03DeployedSourceMissing');
        $actual=hash_file('sha256',$installed);
        Cf03LabContract::need(hash_equals(hash_file('sha256',$source),$actual),
            'CF03DeployedSourceMismatch');
        $deployed[$path]=$actual;
    }
    $version=file_get_contents($root.'/'.$versionPath);
    Cf03LabContract::assertCoreVersion($env['core_version'],$version);
    // Bind the actual selected Core source in addition to its version string.
    $corePaths=array($versionPath,'adherents/class/adherent.class.php',
        'core/db/mysqli.class.php','user/class/user.class.php');
    $coreHashes=array();
    foreach($corePaths as $path) {
        $coreFile=$root.'/'.$path;
        Cf03LabContract::need(is_file($coreFile) && !is_link($coreFile),
            'CF03CoreSourceUnavailable');
        $coreHashes[$path]=hash_file('sha256',$coreFile);
    }
    // Approved local Dolibarr config; execute as its trusted web system user.
    require $root.'/conf/conf.php';
    Cf03LabContract::need(($dolibarr_main_db_type??'')==='mysqli'
        && ($dolibarr_main_db_host??'')===$env['db_host']
        && (int)($dolibarr_main_db_port??0)===$env['db_port']
        && ($dolibarr_main_db_name??'')===$env['db_name']
        && ($dolibarr_main_db_user??'')===$env['db_user']
        && ($dolibarr_main_db_prefix??'llx_')==='llx_',
        'CF03WrongDatabaseConfiguration');
    try {
        mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
        $db=new mysqli($dolibarr_main_db_host,$dolibarr_main_db_user,
            $dolibarr_main_db_pass,$dolibarr_main_db_name,$env['db_port']);
        $db->set_charset('utf8mb4');
    } catch (Throwable $e) { throw new RuntimeException('CF03DatabaseConnectionUnavailable'); }
    $identity=cf03One($db,'SELECT DATABASE() AS schemaname, CURRENT_USER() AS account, '
        .'VERSION() AS version, @@version_comment AS version_comment, '
        .'CONNECTION_ID() AS connection_id, @@SESSION.autocommit AS ac, '
        .'@@SESSION.completion_type AS completion');
    Cf03LabContract::need($identity['schemaname']===$env['db_name']
        && strpos($identity['account'],$env['db_user'].'@')===0,
        'CF03EffectiveDatabaseAccountMismatch');
    Cf03LabContract::need(strpos($identity['version'],$env['version'])===0,
        'CF03UnexpectedServerVersion');
    if ($env['family']==='mariadb') {
        Cf03LabContract::need(stripos($identity['version'],'MariaDB')!==false,
            'CF03WrongDatabaseFamily');
    } else {
        Cf03LabContract::need(stripos($identity['version'],'MariaDB')===false
            && preg_match('/^MySQL (?:Community|Enterprise) Server\b/',$identity['version_comment'])===1,
            'CF03WrongDatabaseFamily');
    }
    Cf03LabContract::need((string)$identity['ac']==='1'
        && in_array((string)$identity['completion'],array('NO_CHAIN','0'),true),
        'CF03WrongSessionSettings');
    $raw=cf03Query($db,"SELECT value FROM llx_const WHERE name='MEMBERSUBSCRIPTIONMASSACTION_TRANSACTION_PROFILE' AND entity=1");
    Cf03LabContract::need(count($raw)<=1,'CF03DuplicateProfileConfiguration');
    if (count($raw)===0 && $env['family']==='mariadb') {
        $profile=array('name'=>'MariaDBSnapshot','family'=>'mariadb',
            'raw_hash'=>hash('sha256','missing'));
    } else {
        Cf03LabContract::need(count($raw)===1,'CF03ProfileNotSelected');
        $obj=json_decode($raw[0]['value'],true);
        Cf03LabContract::need(is_array($obj) && count($obj)===4
            && ($obj['version']??null)===1
            && ($obj['family']??null)===$env['family']
            && ($obj['profile']??null)===$env['profile']
            && is_string($obj['nonce']??null)
            && preg_match('/^[a-f0-9]{32}$/D',$obj['nonce'])===1,
            'CF03WrongSelectedProfile');
        $profile=array('name'=>$obj['profile'],'family'=>$obj['family'],
            'raw_hash'=>hash('sha256',$raw[0]['value']));
    }
    $ps='not_required';
    if ($env['family']==='mysql') {
        try {
            $res=$db->query('SELECT THREAD_ID, EVENT_ID, STATE, END_EVENT_ID, '
                .'NUMBER_OF_SAVEPOINTS, NUMBER_OF_RELEASE_SAVEPOINT, ISOLATION_LEVEL '
                .'FROM performance_schema.events_transactions_current '
                .'WHERE THREAD_ID=PS_CURRENT_THREAD_ID()');
            if ($res instanceof mysqli_result) { $res->free(); }
            $ps='select_allowed';
        } catch (Throwable $e) {
            $ps=((int)$db->errno===1142 || (int)$e->getCode()===1142)
                ? 'select_denied_1142':'unavailable';
        }
        Cf03LabContract::need(($env['ps']==='denied' && $ps==='select_denied_1142')
            || ($env['ps']==='select' && $ps==='select_allowed'),
            'CF03PerformanceSchemaPrivilegeMismatch');
    }
    $engines=array();
    foreach (cf03Query($db,"SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES "
        ."WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME LIKE 'llx_%'") as $row) {
        $engines[substr($row['TABLE_NAME'],4)]=$row['ENGINE'];
    }
    // Validate the exact V22/V24 SQL projection before admitting a witness case.
    $columns=array();
    $sqlTables=array_keys(Cf03LabContract::columnPolicy());
    foreach (cf03Query($db,"SELECT TABLE_NAME,COLUMN_NAME FROM information_schema.COLUMNS "
        ."WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('llx_"
        .implode("','llx_",$sqlTables)."')") as $column) {
        $name=substr($column['TABLE_NAME'],4);
        $columns[$name][]=$column['COLUMN_NAME'];
    }
    Cf03LabContract::columnsComplete($columns);
    foreach (Cf03LabContract::BASIC_ENGINES as $name) {
        Cf03LabContract::need(($engines[$name]??null)==='InnoDB','CF03RequiredEngineUnavailable');
    }
    if ($id==='B' || $id==='C') {
        foreach (Cf03LabContract::INVOICE_ENGINES as $name) {
            Cf03LabContract::need(($engines[$name]??null)==='InnoDB',
                'CF03InvoiceEngineUnavailable');
        }
    }
    $out=array(
        'environment'=>$id,'family'=>$env['family'],
        'identity'=>array('schema'=>$identity['schemaname'],
            'effective_account'=>$identity['account'],'version'=>$identity['version'],
            'autocommit'=>(string)$identity['ac'],
            'completion'=>(string)$identity['completion']),
        'profile'=>$profile,'performance_schema'=>$ps,'engines'=>$engines,
        'source_hashes'=>cf03Source(),
        'deployed_source_hashes'=>$deployed,
        'deployment_kind'=>$module===$checkout?'source_checkout':'verified_deployed_copy',
        'core_source_hashes'=>$coreHashes,
        'schema_columns'=>$columns,
        'config_sha256'=>hash_file('sha256',$root.'/conf/conf.php'),
        'dolibarr_version'=>$env['core_version'],
        'php_version'=>PHP_VERSION,
        'module_directory'=>$module);
    return array($db,$out);
}
function cf03State($db,array $context,array $expected)
{
    $id=$expected['member_id'];
    $m=cf03One($db,'SELECT rowid,ref,firstname,lastname,email,entity,'
        .'fk_adherent_type,fk_soc,statut,CAST(datefin AS CHAR) AS datefin '
        .'FROM llx_adherent WHERE rowid='.$id);
    $t=cf03One($db,'SELECT rowid,statut,amount,duration FROM llx_adherent_type WHERE rowid='
        .(int)$m['fk_adherent_type']);
    $flags=cf03Query($db,"SELECT name, value, entity FROM llx_const "
        ."WHERE name IN ('MAIN_MODULE_AGENDA','MAIN_AGENDA_ACTIONAUTO_MEMBER_SUBSCRIPTION_CREATE',"
        ."'MAIN_AGENDA_ACTIONAUTO_BILL_CREATE','MAIN_AGENDA_ACTIONAUTO_BILL_VALIDATE',"
        ."'MAIN_AGENDA_ACTIONAUTO_LINEBILL_INSERT') "
        ."AND entity IN (0,1) ORDER BY entity");
    $on=array();foreach($flags as $f){$on[$f['name']]=$f['value'];}
    Cf03LabContract::need(isset($on['MAIN_MODULE_AGENDA'])
        && isset($on['MAIN_AGENDA_ACTIONAUTO_MEMBER_SUBSCRIPTION_CREATE'])
        && in_array($on['MAIN_MODULE_AGENDA'],array('0','1'),true)
        && in_array($on['MAIN_AGENDA_ACTIONAUTO_MEMBER_SUBSCRIPTION_CREATE'],array('0','1'),true),
        'CF03AgendaConfigurationMissing');
    $invoiceFlags=array();
    foreach (array('BILL_CREATE','BILL_VALIDATE','LINEBILL_INSERT') as $action) {
        $val=$on['MAIN_AGENDA_ACTIONAUTO_'.$action]??'0';
        Cf03LabContract::need(in_array($val,array('0','1'),true),
            'CF03InvoiceAgendaConfigUnknown');
        $invoiceFlags[$action]=$on['MAIN_MODULE_AGENDA']==='1' && $val==='1';
    }
    // The line event has no unambiguous invoice-id association in this scoped
    // witness: do not promise complete Agenda proof when it is enabled.
    $invoiceAgendaSupported=!$invoiceFlags['LINEBILL_INSERT'];
    $subRows=cf03Query($db,'SELECT rowid,fk_adherent,fk_type,subscription,'
        .'CAST(dateadh AS CHAR) AS dateadh,CAST(datef AS CHAR) AS datef,note '
        .'FROM llx_subscription WHERE fk_adherent='.$id.' ORDER BY rowid');
    $subs=array();foreach($subRows as $sub){
        $subs[]=array('id'=>(int)$sub['rowid'],'member_id'=>(int)$sub['fk_adherent'],
            'type_id'=>(int)$sub['fk_type'],'amount'=>$sub['subscription'],
            'start'=>$sub['dateadh'],'end'=>$sub['datef'],
            'marker'=>strpos((string)$sub['note'],$expected['marker'])!==false);
    }
    $actionRows=cf03Query($db,"SELECT id,code,fk_element FROM llx_actioncomm "
        ."WHERE elementtype='member' AND fk_element=".$id
        ." AND code='AC_MEMBER_SUBSCRIPTION_CREATE' ORDER BY id");
    $actions=array();$resources=array();
    foreach($actionRows as $action) {
        $event=(int)$action['id'];
        $actions[]=array('id'=>$event,'code'=>$action['code'],
            'member_id'=>(int)$action['fk_element']);
        foreach(cf03Query($db,'SELECT rowid,element_type,fk_element FROM llx_actioncomm_resources '
            .'WHERE fk_actioncomm='.$event.' ORDER BY rowid') as $link){
            $resources[]=array('id'=>(int)$link['rowid'],'agenda_id'=>$event,
                'element_type'=>$link['element_type'],
                'actor_id'=>(int)$link['fk_element']);
        }
    }
    $invoices=array();$invoiceEvents=array();$invoiceResources=array();
    $linked=cf03Query($db,"SELECT f.rowid,f.entity,f.fk_soc,f.type,f.fk_statut,"
        ."f.paye,f.ref,f.total_ttc,CAST(f.datef AS CHAR) AS invoice_date,"
        ."f.fk_cond_reglement,f.fk_mode_reglement,f.fk_account,"
        ."f.multicurrency_code,ee.fk_source AS subscription_id "
        ."FROM llx_facture f JOIN llx_element_element ee ON ee.fk_target=f.rowid "
        ."WHERE ee.sourcetype='subscription' AND ee.targettype='facture' "
        ."AND ee.fk_source IN (SELECT rowid FROM llx_subscription WHERE fk_adherent=".$id.")");
    foreach ($linked as $inv) {
        $lines=array();
        foreach(cf03Query($db,'SELECT rowid,total_ttc,description,qty,fk_product,'
            .'product_type,tva_tx,CAST(date_start AS CHAR) AS start_date FROM llx_facturedet '
            .'WHERE fk_facture='.(int)$inv['rowid'].' ORDER BY rowid') as $line) {
            $lines[]=array('id'=>(int)$line['rowid'],'total'=>$line['total_ttc'],
                'quantity'=>$line['qty'],'product'=>(int)$line['fk_product'],
                'product_type'=>(int)$line['product_type'],'vat'=>$line['tva_tx'],
                'date_start'=>$line['start_date'],
                'marker'=>strpos((string)$line['description'],$expected['marker'])!==false);
        }
        $invoices[]=array('id'=>(int)$inv['rowid'],
            'subscription_id'=>(int)$inv['subscription_id'],
            'entity'=>(int)$inv['entity'],'fk_soc'=>(int)$inv['fk_soc'],
            'type'=>(int)$inv['type'],'status'=>(int)$inv['fk_statut'],
            'paid'=>(int)$inv['paye'],'ref'=>$inv['ref'],
            'total'=>$inv['total_ttc'],'date'=>$inv['invoice_date'],
            'term'=>(int)$inv['fk_cond_reglement'],
            'payment_mode'=>(int)$inv['fk_mode_reglement'],
            'bank'=>(int)$inv['fk_account'],
            'currency'=>$inv['multicurrency_code'],'lines'=>$lines);
        foreach (cf03Query($db,'SELECT id,code,fk_element FROM llx_actioncomm '
            ."WHERE elementtype='facture' AND fk_element=".(int)$inv['rowid']
            .' ORDER BY id') as $action) {
            $event=(int)$action['id'];
            $invoiceEvents[]=array('id'=>$event,'code'=>$action['code'],
                'invoice_id'=>(int)$action['fk_element']);
            foreach (cf03Query($db,'SELECT rowid,element_type,fk_element '
                .'FROM llx_actioncomm_resources WHERE fk_actioncomm='.$event
                .' ORDER BY rowid') as $res) {
                $invoiceResources[]=array('id'=>(int)$res['rowid'],
                    'agenda_id'=>$event,'element_type'=>$res['element_type'],
                    'actor_id'=>(int)$res['fk_element']);
            }
        }
    }
    $counts=array();
    foreach(Cf03LabContract::COUNTS as $table){
        $counts[$table]=(int)cf03One($db,'SELECT COUNT(*) AS n FROM llx_'.$table)['n'];
    }
    $customerExists=false; $customer=null;
    if ((int)$m['fk_soc']>0) {
        $customers=cf03Query($db,'SELECT rowid,entity,client FROM llx_societe '
            .'WHERE rowid='.(int)$m['fk_soc']);
        $customerExists=count($customers)===1;
        if ($customerExists) {
            $c=$customers[0];
            $customer=array('id'=>(int)$c['rowid'],
                'entity'=>(int)$c['entity'],'client'=>(int)$c['client']);
        }
    }
    return array(
        'environment'=>$context['environment'],
        'identity'=>$context['identity'],
        'profile'=>$context['profile'],
        'engines'=>$context['engines'],
        'source_hashes'=>$context['source_hashes'],
        'deployed_source_hashes'=>$context['deployed_source_hashes'],
        'deployment_kind'=>$context['deployment_kind'],
        'core_source_hashes'=>$context['core_source_hashes'],
        'schema_columns'=>$context['schema_columns'],
        'config_sha256'=>$context['config_sha256'],
        'performance_schema'=>$context['performance_schema'],
        'member'=>array('id'=>(int)$m['rowid'],'ref'=>$m['ref'],
            'firstname'=>$m['firstname'],'lastname'=>$m['lastname'],
            'email_hash'=>hash('sha256',strtolower((string)$m['email'])),
            'entity'=>(int)$m['entity'],'type_id'=>(int)$m['fk_adherent_type'],
            'fk_soc'=>(int)$m['fk_soc'],'status'=>(int)$m['statut'],
            'datefin'=>$m['datefin']),
        'type'=>array('id'=>(int)$t['rowid'],'status'=>(int)$t['statut'],
            'amount'=>$t['amount'],'duration'=>$t['duration']),
        'agenda_enabled'=>$on['MAIN_MODULE_AGENDA']==='1'
            && $on['MAIN_AGENDA_ACTIONAUTO_MEMBER_SUBSCRIPTION_CREATE']==='1',
        'customer_exists'=>$customerExists,'customer'=>$customer,
        'invoice_agenda_flags'=>$invoiceFlags,
        'invoice_agenda_supported'=>$invoiceAgendaSupported,
        'invoice_agenda_events'=>$invoiceEvents,
        'invoice_agenda_resources'=>$invoiceResources,
        'subscriptions'=>$subs,'agenda_events'=>$actions,
        'agenda_resources'=>$resources,'invoices'=>$invoices,'counts'=>$counts);
}

try {
    list($mode,$id,$env,$opts)=Cf03LabContract::arguments($argv);
    list($db,$context)=cf03Configure($id,$env);
    if ($mode==='preflight') {
        $result=array('status'=>'PASS','mode'=>'preflight','environment'=>$id,
            'family'=>$context['family'],'profile'=>$context['profile']['name'],
            'account'=>$context['identity']['effective_account'],
            'schema'=>$context['identity']['schema'],
            'version'=>$context['identity']['version'],
            'core_version'=>$context['dolibarr_version'],
            'php_version'=>$context['php_version'],
            'ps'=>$context['performance_schema'],
            'profile_sha256'=>$context['profile']['raw_hash'],
            'config_sha256'=>$context['config_sha256'],
            'source_hashes'=>$context['source_hashes'],
            'deployed_source_hashes'=>$context['deployed_source_hashes'],
            'deployment_kind'=>$context['deployment_kind'],
            'core_source_hashes'=>$context['core_source_hashes'],
            'writes'=>'no_business_sql');
    } else {
        $e=Cf03LabContract::expected($opts);
        if ($mode==='verify') {
            $dir=$opts['evidence-dir'];
            $evidenceRoot=Cf03Evidence::choice($id,$dir,$opts['evidence-root']??null);
            Cf03Evidence::directory($dir,false,$id,$evidenceRoot,
                $env['document_root'],dirname(__DIR__));
            $before=Cf03Evidence::readyToVerify($dir);
            Cf03LabContract::need(is_array($before) && ($before['case']??null)==='CF03'
                && ($before['environment']??null)===$id
                && ($before['expected']??null)===$e,'CF03BaselineMismatch');
            Cf03LabContract::baseline($before['before'],$e,$id);
            Cf03Evidence::exclusive($dir,'verify.started',gmdate('c')."\n");
            $after=cf03State($db,$context,$e);
            Cf03Evidence::json($dir,'after.json',array('case'=>'CF03','after'=>$after,
                'observed_utc'=>gmdate('c')));
            try { $result=Cf03LabContract::verify($before['before'],$after,$e,$id); }
            catch (RuntimeException $error) {
                $result=array('status'=>'STOP','case'=>'CF03','reason'=>$error->getMessage(),
                    'action'=>'preserve immutable evidence; do not rebook or retry');
            }
            Cf03Evidence::json($dir,'verdict.json',$result);
        } else {
            $snapshot=cf03State($db,$context,$e);
            Cf03LabContract::baseline($snapshot,$e,$id);
            if ($mode==='baseline') {
                $dir=$opts['evidence-dir'];
                $evidenceRoot=Cf03Evidence::choice($id,$dir,$opts['evidence-root']??null);
                Cf03Evidence::directory($dir,true,$id,$evidenceRoot,
                    $env['document_root'],dirname(__DIR__));
                Cf03Evidence::json($dir,'baseline.json',array('case'=>'CF03',
                    'environment'=>$id,'expected'=>$e,'before'=>$snapshot,
                    'created_utc'=>gmdate('c')));
                Cf03Evidence::exclusive($dir,'baseline.sha256',
                    hash_file('sha256',$dir.'/baseline.json')."\n");
            }
            $result=array('status'=>'PASS','mode'=>$mode,'environment'=>$id,
                'scenario'=>$e['scenario'],'member_id'=>$e['member_id'],
                'writes'=>'no_business_sql');
        }
    }
    echo json_encode($result,JSON_UNESCAPED_SLASHES)."\n";
    exit($result['status']==='PASS'?0:1);
} catch(Throwable $error) {
    $reason=$error->getMessage();
    if(strpos($reason,'CF03')!==0){$reason='CF03UnexpectedRuntimeFailure';}
    echo json_encode(array('status'=>'STOP','reason'=>$reason,
        'action'=>'do not book, retry or cleanup automatically; preserve any evidence'),
        JSON_UNESCAPED_SLASHES)."\n";
    exit(1);
}
