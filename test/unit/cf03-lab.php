<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 * Pure no-DB contract tests: no network, no HTTP, no business writes.
 */
if (PHP_SAPI!=='cli') { exit(64); }
require_once __DIR__.'/../../tools/cf03-lab.lib.php';
$passed=0;
function c3ok($ok,$message)
{
    global $passed;
    if (!$ok) { throw new RuntimeException('CF03 unit failed: '.$message); }
    $passed++;
}
function c3stop($fn,$message,$expectedReason)
{
    try { $fn(); }
    catch (RuntimeException $error) {
        c3ok($error->getMessage()===$expectedReason,
            $message.' expected '.$expectedReason.' got '.$error->getMessage());
        return;
    }
    throw new RuntimeException('CF03 unit not denied: '.$message);
}
function c3config($id,$scenario='contribution',$agenda='on')
{
    $e=Cf03LabContract::environments()[$id];
    $marker='CF03LAB-'.$id.'-ABCDEF123';
    $config=array('scenario'=>$scenario,'member_id'=>271,'member_ref'=>'MEM2611-0271',
        'marker'=>$marker,'start'=>'2080-01-01','end'=>'2080-12-31',
        'amount'=>'120.00','agenda'=>$agenda,
        'actor_id'=>$scenario==='invoice-existing'
            || ($scenario==='contribution' && $agenda==='on')?4:0);
    if ($scenario==='invoice-existing') {
        $config['invoice']=array('date'=>'2026-11-20','quantity'=>1.0,
            'product'=>0,'vat'=>0.0,'term'=>1,'payment_mode'=>0,
            'bank'=>0,'currency'=>'EUR');
    }
    return $config;
}
function c3make($id,$scenario,$agenda)
{
    $expected=c3config($id,$scenario,$agenda);
    $m=array('id'=>271,'ref'=>'MEM2611-0271','firstname'=>'Synthetic',
        'lastname'=>$expected['marker'],
        'email_hash'=>hash('sha256',strtolower($expected['marker']).'@example.invalid'),
        'entity'=>1,'status'=>1,'datefin'=>null,'type_id'=>2,
        'fk_soc'=>$scenario==='invoice-existing'?5:0);
    $env=Cf03LabContract::environments()[$id];
    $engines=array_fill_keys(array_merge(Cf03LabContract::BASIC_ENGINES,
        Cf03LabContract::INVOICE_ENGINES),'InnoDB');
    $s=array('environment'=>$id,'identity'=>array('schema'=>'synthetic',
            'effective_account'=>'fixture@127.0.0.1','autocommit'=>'1',
            'completion'=>'NO_CHAIN'),
        'profile'=>array('name'=>$env['profile'],'family'=>$env['family'],
            'raw_hash'=>str_repeat('a',64)),
        'engines'=>$engines,'type'=>array('id'=>2,'status'=>1,'entity'=>1,
            'subscription'=>'1','amount'=>'120.00000000'),
        'source_hashes'=>array('php'=>str_repeat('b',64)),
        'source_manifest_version'=>'CF03-W01-MODULE-PHP-1',
        'deployed_source_hashes'=>array('product'=>str_repeat('f',64)),
        'deployment_kind'=>$id==='D'?'verified_deployed_copy':'source_checkout',
        'core_source_hashes'=>array('core'=>str_repeat('e',64)),
        'schema_columns'=>Cf03LabContract::columnPolicy(),
        'config_sha256'=>str_repeat('c',64),
        'php_version'=>$id==='D'?'8.3.37':'8.2.35',
        'php_major_minor'=>$id==='D'?'8.3':'8.2',
        'performance_schema'=>$env['ps']==='select'?'select_allowed':
            ($env['ps']==='denied'?'select_denied_1142':'not_required'),
        'agenda_enabled'=>$agenda==='on',
        'invoice_product'=>array('id'=>0,'type'=>1,'status'=>1,'entity'=>1),
        'actor'=>$expected['actor_id']>0
            ? array('id'=>4,'entity'=>1,'status'=>1):null,
        'customer_exists'=>$scenario==='invoice-existing',
        'customer'=>$scenario==='invoice-existing'
            ? array('id'=>5,'entity'=>1,'client'=>1):null,
        'invoice_agenda_flags'=>array('BILL_CREATE'=>false,
            'BILL_VALIDATE'=>$agenda==='on','LINEBILL_INSERT'=>false),
        'invoice_agenda_supported'=>true,
        'invoice_agenda_events'=>array(),'invoice_agenda_resources'=>array(),
        'member'=>$m,'subscriptions'=>array(),
        'agenda_events'=>array(),'agenda_resources'=>array(),
        'invoices'=>array(),
        'counts'=>array_fill_keys(Cf03LabContract::COUNTS,20));
    $after=$s;
    if ($scenario!=='no-change') {
        $after['member']['datefin']='2080-12-31 00:00:00';
        $after['subscriptions'][]=array('id'=>57,'member_id'=>271,'type_id'=>2,
            'amount'=>'120.00','start'=>'2080-01-01 00:00:00',
            'end'=>'2080-12-31 00:00:00','marker'=>true);
        $after['counts']['subscription']++;
        if ($agenda==='on') {
            $after['agenda_events'][]=array('id'=>50,'code'=>'AC_MEMBER_SUBSCRIPTION_CREATE',
                'member_id'=>271,
                'entity'=>1,'author_id'=>4,'owner_id'=>4);
            $after['agenda_resources'][]=array('id'=>51,'agenda_id'=>50,
                'element_type'=>'user','actor_id'=>4);
            $after['counts']['actioncomm']++;
            $after['counts']['actioncomm_resources']++;
        }
        if ($scenario==='invoice-existing') {
            $after['invoices'][]=array('id'=>60,'subscription_id'=>57,'fk_soc'=>5,
                'entity'=>1,'type'=>0,'status'=>1,'paid'=>0,'date'=>'2026-11-20',
                'term'=>1,'payment_mode'=>0,'bank'=>0,'currency'=>'EUR',
                'ref'=>'F-00060','total'=>'120.00',
                'lines'=>array(array('id'=>61,'total'=>'120.00','marker'=>true,
                    'quantity'=>1.0,'product'=>0,'product_type'=>1,'vat'=>'0.0',
                    'date_start'=>'2080-01-01 00:00:00')));
            foreach (array('facture','facturedet','element_element') as $name) {
                $after['counts'][$name]++;
            }
            if ($agenda==='on') {
                $after['invoice_agenda_events'][]=array('id'=>52,
                    'code'=>'AC_BILL_VALIDATE','invoice_id'=>60,
                    'entity'=>1,'author_id'=>4,'owner_id'=>4);
                $after['invoice_agenda_resources'][]=array('id'=>53,
                    'agenda_id'=>52,'element_type'=>'user','actor_id'=>4);
                $after['counts']['actioncomm']++;
                $after['counts']['actioncomm_resources']++;
            }
        }
    }
    return array($expected,$s,$after);
}
// GR-02: real environments must be pinned to the expected PHP major/minor;
// baseline and verify additionally bind the exact patch/runtime version.
foreach (array('A'=>'8.2','B'=>'8.2','C'=>'8.2','D'=>'8.3') as $envId=>$target) {
    c3ok(Cf03LabContract::phpTarget($envId)===$target,
        'expected PHP target '.$envId);
    c3ok(Cf03LabContract::assertPhpRuntime($envId,$target.'.35')===$target,
        'accepted PHP target '.$envId);
    $incorrect=$envId==='D'?'8.2.35':'8.3.37';
    c3stop(static function()use($envId,$incorrect){
        Cf03LabContract::assertPhpRuntime($envId,$incorrect);
    },'wrong target PHP '.$envId,'CF03WrongPHPRuntime');
    list($runtimeExpected,$runtimeBefore,$runtimeAfter)=
        c3make($envId,'no-change','off');
    $runtimeWrong=$runtimeBefore;
    $runtimeWrong['php_version']=$incorrect;
    c3stop(static function()use($runtimeWrong,$runtimeExpected,$envId){
        Cf03LabContract::baseline($runtimeWrong,$runtimeExpected,$envId);
    },'wrong runtime baseline '.$envId,'CF03WrongPHPRuntime');
    $runtimeWrong=$runtimeAfter;
    $runtimeWrong['php_version']=$target.'.99';
    c3stop(static function()use($runtimeBefore,$runtimeWrong,$runtimeExpected,$envId){
        Cf03LabContract::verify($runtimeBefore,$runtimeWrong,$runtimeExpected,$envId);
    },'changed PHP patch during case '.$envId,'CF03ContextChanged');
    $runtimeWrong=$runtimeBefore;
    unset($runtimeWrong['php_major_minor']);
    c3stop(static function()use($runtimeWrong,$runtimeExpected,$envId){
        Cf03LabContract::baseline($runtimeWrong,$runtimeExpected,$envId);
    },'missing PHP major/minor '.$envId,'CF03WrongPHPRuntime');
}
// Regression: Dolibarr V22.0.5 has no htdocs/version.inc.php.
// Official pinned V22 filefunc.inc.php defines DOL_VERSION directly;
// V24.0.3 uses htdocs/version.inc.php major/minor declarations.
c3ok(Cf03LabContract::versionSource('22.0.5')==='filefunc.inc.php',
    'V22 version source');
c3ok(Cf03LabContract::versionSource('24.0.3')==='version.inc.php',
    'V24 version source');
$fixtureV22="<?php\nif (!defined('DOL_VERSION')) {\n"
    ."\tdefine('DOL_VERSION', '22.0.5'); // a.b.c\n}\n";
$fixtureV24="<?php\ndefine('DOL_MAJOR_VERSION', '24');\n"
    ."define('DOL_MINOR_VERSION', '0.3');\n"
    ."define('DOL_VERSION', constant('DOL_MAJOR_VERSION').'.'.constant('DOL_MINOR_VERSION'));\n";
Cf03LabContract::assertCoreVersion('22.0.5',$fixtureV22);
c3ok(true,'V22 22.0.5 official-style version declaration accepted');
Cf03LabContract::assertCoreVersion('24.0.3',$fixtureV24);
c3ok(true,'V24 24.0.3 split version declarations accepted');
foreach (array('22.0.4','22.0.6','23.0.0','24.0.3','untrusted') as $bad) {
    c3stop(static function()use($bad){
        Cf03LabContract::assertCoreVersion('22.0.5',
            "<?php\n"."define('DOL_VERSION', '".$bad."');\n");
    },'V22 wrong version denied '.$bad,'CF03WrongCoreVersion');
}
foreach (array('0.0','0.2','0.4','22.0.5') as $bad) {
    c3stop(static function()use($bad){
        Cf03LabContract::assertCoreVersion('24.0.3',
            "<?php\n"."define('DOL_MAJOR_VERSION', '24');\n"
            ."define('DOL_MINOR_VERSION', '".$bad."');\n");
    },'V24 wrong minor version denied '.$bad,'CF03WrongCoreVersion');
}
c3stop(static function() {
    Cf03LabContract::versionSource('23.0.0');
},'unqualified version source denied','CF03CoreVersionPolicyUnsupported');
c3stop(static function() {
    Cf03LabContract::assertCoreVersion('22.0.5',null);
},'unreadable version denied','CF03CoreVersionReadUnavailable');
// Prevent SQL drift before a live selected-table witness.
$columns=Cf03LabContract::columnPolicy();
Cf03LabContract::columnsComplete($columns);
c3ok(true,'V22/V24 schema projection present');
unset($columns['facturedet'][array_search('qty',$columns['facturedet'],true)]);
c3stop(static function()use($columns){Cf03LabContract::columnsComplete($columns);},
    'missing invoice quantity column rejected','CF03WitnessSchemaUnsupported');
// An unrelated CF03 exception must NEVER make a negative test green.
try {
    c3stop(static function() {
        throw new RuntimeException('CF03WrongProfile');
    },'unrelated STOP rejected','CF03FixtureMismatch');
    throw new RuntimeException('CF03 stop mismatch falsely accepted');
} catch (RuntimeException $reasonMismatch) {
    c3ok(strpos($reasonMismatch->getMessage(),'CF03 unit failed:')===0,
        'exact negative error contract enforced');
}
// Synthetic operator identities, unrelated to any private development machine.
foreach (array(
    'CF03_A_DOCUMENT_ROOT'=>'/opt/cf03-fixture/mysql/htdocs',
    'CF03_A_DATA_ROOT'=>'/opt/cf03-fixture/mysql/data',
    'CF03_A_DB_HOST'=>'127.0.0.1',
    'CF03_A_DB_PORT'=>'3307',
    'CF03_A_DB_NAME'=>'cf03_mysql_fixture',
    'CF03_A_DB_USER'=>'cf03_limited_fixture',
    'CF03_A_WEB_URL'=>'http://127.0.0.1:8091/',
    'CF03_B_DOCUMENT_ROOT'=>'/opt/cf03-fixture/mariadb/htdocs',
    'CF03_B_DATA_ROOT'=>'/opt/cf03-fixture/mariadb/data',
    'CF03_B_DB_HOST'=>'127.0.0.1',
    'CF03_B_DB_PORT'=>'3306',
    'CF03_B_DB_NAME'=>'cf03_mariadb_fixture',
    'CF03_B_WEB_URL'=>'http://127.0.0.1:8090/',
    'CF03_C_DB_USER'=>'cf03_enhanced_fixture'
) as $key=>$value) { putenv($key.'='.$value); }
$environments=Cf03LabContract::environments();
c3ok(count($environments)===4,'four environments');
c3ok($environments['A']['document_root']===$environments['C']['document_root']
    && $environments['A']['db_name']===$environments['C']['db_name']
    && $environments['A']['db_user']!==$environments['C']['db_user'],
    'A/C shared database with separately supplied C user');
c3ok($environments['D']['core_version']==='22.0.5','V22 pinned');
foreach(array('A','B','C','D') as $id) {
    $overrides=$id==='B'?array('--db-user=mariadb_application'):
        ($id==='C'?array('--db-user=enhanced_application'):
        ($id==='D'?array('--document-root=/tmp/dolibarr-v22/htdocs',
            '--db-host=127.0.0.1','--db-port=3307','--db-name=v22_hosted',
            '--db-user=hosted_app'):array()));
    $args=array_merge(array('cf03-lab.php','--preflight','--environment='.$id),$overrides);
    $parsed=Cf03LabContract::arguments($args);
    c3ok($parsed[0]==='preflight' && $parsed[1]===$id,'preflight '.$id);
    c3stop(static function()use($args){
        Cf03LabContract::arguments(array_merge($args,array('--db-name=foreign')));
    },'no accidental override '.$id,($id==='D'?'CF03InvalidArgument':'CF03EnvironmentOverrideDenied'));
    foreach(array('contribution','no-change') as $scenario) {
        foreach(array('on','off') as $agenda) {
            list($e,$before,$after)=c3make($id,$scenario,$agenda);
            Cf03LabContract::baseline($before,$e,$id);
            c3ok(Cf03LabContract::verify($before,$after,$e,$id)['status']==='PASS',
                'scenario '.$id.' '.$scenario.' '.$agenda);
            foreach (array('status'=>0,'entity'=>2,'subscription'=>'0') as $key=>$bad) {
                $wrongType=$before;
                $wrongType['type'][$key]=$bad;
                c3stop(static function()use($wrongType,$e,$id) {
                    Cf03LabContract::baseline($wrongType,$e,$id);
                },'invalid membership type '.$key,'CF03MemberTypeMismatch');
            }
            $wrong=$after;$wrong['config_sha256']=str_repeat('f',64);
            c3stop(static function()use($before,$wrong,$e,$id){
                Cf03LabContract::verify($before,$wrong,$e,$id);
            },'configuration changed','CF03ContextChanged');
            $wrong=$after;$wrong['profile']['raw_hash']=str_repeat('f',64);
            c3stop(static function()use($before,$wrong,$e,$id){
                Cf03LabContract::verify($before,$wrong,$e,$id);
            },'profile changed','CF03ContextChanged');
            $wrong=$after;$wrong['counts']['bank']++;
            c3stop(static function()use($before,$wrong,$e,$id){
                Cf03LabContract::verify($before,$wrong,$e,$id);
            },'unexpected bank DML','CF03UnexpectedTableDelta');
                        $wrong=$after;$wrong['deployed_source_hashes']['product']=str_repeat('d',64);
            c3stop(static function()use($before,$wrong,$e,$id){
                Cf03LabContract::verify($before,$wrong,$e,$id);
            },'deployed product source changed','CF03ContextChanged');
            $wrong=$after;$wrong['core_source_hashes']['core']=str_repeat('a',64);
            c3stop(static function()use($before,$wrong,$e,$id){
                Cf03LabContract::verify($before,$wrong,$e,$id);
            },'Dolibarr Core source changed','CF03ContextChanged');
$wrong=$after;$wrong['source_hashes']['php']=str_repeat('d',64);
            c3stop(static function()use($before,$wrong,$e,$id){
                Cf03LabContract::verify($before,$wrong,$e,$id);
            },'source changed','CF03ContextChanged');
            $wrong=$before;$wrong['member']['datefin']='2079-12-31 00:00:00';
            c3stop(static function()use($wrong,$e,$id){
                Cf03LabContract::baseline($wrong,$e,$id);
            },'prior member end disallowed','CF03FixtureMismatch');
            if($scenario==='contribution') {
                $wrong=$after;$wrong['subscriptions'][0]['marker']=false;
                c3stop(static function()use($before,$wrong,$e,$id){
                    Cf03LabContract::verify($before,$wrong,$e,$id);
                },'marker required','CF03SubscriptionPostconditionMismatch');
            }
        }
    }
    if($id==='B' || $id==='C') {
        list($e,$before,$after)=c3make($id,'invoice-existing','on');
        Cf03LabContract::baseline($before,$e,$id);
        c3ok(Cf03LabContract::verify($before,$after,$e,$id)['status']==='PASS',
            'invoice existing '.$id);
        $wrong=$after;$wrong['invoices'][0]['lines'][0]['marker']=false;
        c3stop(static function()use($before,$wrong,$e,$id){
            Cf03LabContract::verify($before,$wrong,$e,$id);
        },'invoice marker','CF03InvoiceLineMismatch');
        $wrong=$after;$wrong['invoices'][0]['currency']='USD';
        c3stop(static function()use($before,$wrong,$e,$id){
            Cf03LabContract::verify($before,$wrong,$e,$id);
        },'invoice currency mismatch','CF03InvoicePostconditionMismatch');
        $wrong=$after;$wrong['invoices'][0]['lines'][0]['quantity']=2.0;
        c3stop(static function()use($before,$wrong,$e,$id){
            Cf03LabContract::verify($before,$wrong,$e,$id);
        },'invoice quantity mismatch','CF03InvoiceLineMismatch');
        $wrong=$after;$wrong['counts']['actioncomm']++;
        c3stop(static function()use($before,$wrong,$e,$id){
            Cf03LabContract::verify($before,$wrong,$e,$id);
        },'unexplained invoice Agenda count denied','CF03UnexpectedTableDelta');
        $wrong=$after;$wrong['invoice_agenda_events'][]=array('id'=>60,
            'code'=>'AC_BILL_CREATE','invoice_id'=>60,
            'entity'=>1,'author_id'=>4,'owner_id'=>4);
        c3stop(static function()use($before,$wrong,$e,$id){
            Cf03LabContract::verify($before,$wrong,$e,$id);
        },'unknown invoice Agenda event','CF03InvoiceAgendaMismatch');
        $wrong=$before;$wrong['invoice_agenda_supported']=false;
        c3stop(static function()use($wrong,$e,$id){
            Cf03LabContract::baseline($wrong,$e,$id);
        },'unattributed line bill trigger denied','CF03InvoiceAgendaConfigUnsupported');
        $wrong=$after;$wrong['invoices'][0]['fk_soc']=66;
        c3stop(static function()use($before,$wrong,$e,$id){
            Cf03LabContract::verify($before,$wrong,$e,$id);
        },'invoice customer','CF03InvoicePostconditionMismatch');
    }
}
c3stop(static function(){
    Cf03LabContract::arguments(array('cf03-lab.php','--preflight','--environment=C',
        '--db-user=cf03_limited_fixture'));
},'C must not use A limited account','CF03EnhancedMustUseSeparateAccount');
c3stop(static function(){
    Cf03LabContract::arguments(array('cf03-lab.php','--baseline','--environment=A',
        '--scenario=invoice-existing','--member-id=1',
        '--member-ref=MEM2611-0271','--marker=CF03LAB-A-ABCDEF123',
        '--start=2080-01-01','--end=2080-12-31','--amount=120',
        '--agenda=on','--evidence-dir=/tmp/cf03-evidence-unit-fixture/cf03-a-testunit'));
},'CoreGuarded invoice denied','CF03CoreGuardedInvoiceForbidden');
c3stop(static function(){
    Cf03LabContract::arguments(array('cf03-lab.php','--preflight','--environment=A',
        '--evidence-dir=/tmp/unsafe'));
},'preflight cannot write evidence','CF03EvidencePathNotPermitted');
// Paket B: product, customer and Core Agenda identity negative/positive contracts.
foreach (array('B','C') as $environment) {
    list($e,$before,$after)=c3make($environment,'invoice-existing','on');
    $e['invoice']['product']=19;
    $before['invoice_product']=array('id'=>19,'type'=>0,'status'=>1,'entity'=>1);
    $after['invoice_product']=$before['invoice_product'];
    $after['invoices'][0]['lines'][0]['product']=19;
    $after['invoices'][0]['lines'][0]['product_type']=0;
    Cf03LabContract::baseline($before,$e,$environment);
    c3ok(Cf03LabContract::verify($before,$after,$e,$environment)['status']==='PASS',
        'valid goods invoice '.$environment);
    $wrong=$after;$wrong['invoices'][0]['lines'][0]['product_type']=1;
    c3stop(static function()use($before,$wrong,$e,$environment){
        Cf03LabContract::verify($before,$wrong,$e,$environment);
    },'wrong goods product type','CF03InvoiceLineMismatch');
    $wrong=$before;$wrong['invoice_product']['type']=9;
    c3stop(static function()use($wrong,$e,$environment){
        Cf03LabContract::baseline($wrong,$e,$environment);
    },'unsupported product type','CF03InvoiceProductMismatch');
    $wrong=$before;$wrong['invoice_product']['id']=20;
    c3stop(static function()use($wrong,$e,$environment){
        Cf03LabContract::baseline($wrong,$e,$environment);
    },'product unrelated to confirmed plan','CF03InvoiceProductMismatch');
    foreach (array(0,2) as $client) {
        $wrong=$before;$wrong['customer']['client']=$client;
        c3stop(static function()use($wrong,$e,$environment){
            Cf03LabContract::baseline($wrong,$e,$environment);
        },'prospect-only/noncustomer denied '.$client,'CF03InvoiceCustomerMismatch');
    }
    $before['customer']['client']=3;$after['customer']['client']=3;
    Cf03LabContract::baseline($before,$e,$environment);
    c3ok(Cf03LabContract::verify($before,$after,$e,$environment)['status']==='PASS',
        'combined customer/prospect eligible');
    $wrong=$after;$wrong['invoice_agenda_events'][0]['author_id']=43;
    c3stop(static function()use($before,$wrong,$e,$environment){
        Cf03LabContract::verify($before,$wrong,$e,$environment);
    },'invoice event wrong author','CF03InvoiceAgendaActorMismatch');
    $wrong=$after;$wrong['invoice_agenda_resources'][0]['actor_id']=43;
    c3stop(static function()use($before,$wrong,$e,$environment){
        Cf03LabContract::verify($before,$wrong,$e,$environment);
    },'invoice wrong actor assignment','CF03InvoiceAgendaActorMismatch');
    $wrong=$before;$wrong['actor']['status']=0;
    c3stop(static function()use($wrong,$e,$environment){
        Cf03LabContract::baseline($wrong,$e,$environment);
    },'inactive actor admission denied','CF03ExpectedActorUnavailable');
}
foreach (array('A','B','C','D') as $environment) {
    list($e,$before,$after)=c3make($environment,'contribution','on');
    $wrong=$after;$wrong['agenda_events'][0]['owner_id']=44;
    c3stop(static function()use($before,$wrong,$e,$environment){
        Cf03LabContract::verify($before,$wrong,$e,$environment);
    },'wrong member Agenda owner','CF03AgendaActorMismatch');
    $wrong=$after;$wrong['agenda_resources'][0]['actor_id']=44;
    c3stop(static function()use($before,$wrong,$e,$environment){
        Cf03LabContract::verify($before,$wrong,$e,$environment);
    },'wrong member Agenda resource','CF03AgendaActorMismatch');
    $wrong=$before;$wrong['actor']=null;
    c3stop(static function()use($wrong,$e,$environment){
        Cf03LabContract::baseline($wrong,$e,$environment);
    },'unknown actor denied','CF03ExpectedActorUnavailable');
}
// W01 CLI contract: no implicit D evidence parent, no non-invoice extra flags,
// explicit invoice expectations, and D base kept outside the legacy local path.
$common=array('--member-id=271','--member-ref=MEM2611-0271',
    '--marker=CF03LAB-D-ABCDEF123','--start=2080-01-01',
    '--end=2080-12-31','--amount=120.00','--agenda=off',
    '--scenario=no-change');
$hosted=array('cf03-lab.php','--baseline','--environment=D',
    '--document-root=/synthetic/htdocs','--db-host=127.0.0.1',
    '--db-port=3306','--db-name=synthetic_hosted','--db-user=hosted_user');
c3stop(static function()use($hosted,$common){
    Cf03LabContract::arguments(array_merge($hosted,$common,
        array('--evidence-dir=/private/cf03-d-testfixture1')));
},'D evidence root explicitly required','CF03UnsafeEvidenceRoot');
list($mode,$envName,$env,$opts)=Cf03LabContract::arguments(
    array_merge($hosted,$common,array('--evidence-root=/private/evidence',
        '--evidence-dir=/private/evidence/cf03-d-testfixture1')));
c3ok($mode==='baseline' && $envName==='D'
    && $opts['evidence-root']==='/private/evidence','hosted root accepted');
c3stop(static function()use($hosted,$common){
    Cf03LabContract::arguments(array_merge($hosted,$common,
        array('--evidence-root=/synthetic/../unsafe',
            '--evidence-dir=/synthetic/../unsafe/cf03-d-testfixture1')));
},'traversal root denied','CF03UnsafeEvidenceRoot');
$invoiceArgs=array('cf03-lab.php','--check-only','--environment=C',
    '--db-user=enhanced_user','--scenario=invoice-existing',
    '--member-id=271','--member-ref=MEM2611-0271',
    '--marker=CF03LAB-C-ABCDEF123','--start=2080-01-01',
    '--end=2080-12-31','--amount=120.00','--agenda=on','--actor-id=4');
c3stop(static function()use($invoiceArgs){
    Cf03LabContract::arguments($invoiceArgs);
},'missing invoice options denied','CF03MissingInvoiceExpectedField');
$invoiceArgs=array_merge($invoiceArgs,array('--invoice-date=2026-11-20',
    '--invoice-quantity=1','--invoice-product=0','--invoice-vat=0',
    '--invoice-term=1','--invoice-payment-mode=0','--invoice-bank=0',
    '--invoice-currency=EUR'));
list($mode,$envName,$env,$opts)=Cf03LabContract::arguments($invoiceArgs);
$expected=Cf03LabContract::expected($opts);
c3ok($mode==='check-only' && $envName==='C'
    && $expected['invoice']['currency']==='EUR'
    && $expected['invoice']['quantity']===1.0,
    'explicit invoice expectations accepted');
c3stop(static function()use($invoiceArgs){
    $bad=$invoiceArgs;
    $bad[array_search('--invoice-quantity=1',$bad,true)]='--invoice-quantity=0';
    Cf03LabContract::arguments($bad);
},'zero invoice quantity denied','CF03InvalidInvoiceExpectedField');
foreach (array('2','0.5','3.0') as $wrongQuantity) {
    $bad=$invoiceArgs;
    $bad[array_search('--invoice-quantity=1',$bad,true)]='--invoice-quantity='.$wrongQuantity;
    c3stop(static function()use($bad){Cf03LabContract::arguments($bad);},
        'unsupported quantity '.$wrongQuantity,'CF03InvalidInvoiceExpectedField');
}
$withoutActor=array_values(array_diff($invoiceArgs,array('--actor-id=4')));
c3stop(static function()use($withoutActor){Cf03LabContract::arguments($withoutActor);},
    'missing operator actor','CF03InvalidExpectedActor');
c3stop(static function()use($invoiceArgs){
    $bad=$invoiceArgs;
    $bad[array_search('--invoice-currency=EUR',$bad,true)]='--invoice-currency=EU';
    Cf03LabContract::arguments($bad);
},'invalid currency denied','CF03InvalidInvoiceExpectedField');

$invoiceOffArgs=$invoiceArgs;
$invoiceOffArgs[array_search('--agenda=on',$invoiceOffArgs,true)]='--agenda=off';
list($invoiceMode,$invoiceEnv,$invoiceConfig,$invoiceOptions)=
    Cf03LabContract::arguments($invoiceOffArgs);
c3ok(Cf03LabContract::expected($invoiceOptions)['actor_id']===4,
    'Invoice requires actor even if member Agenda is OFF');
$invoiceOffNoActor=array_values(array_diff($invoiceOffArgs,array('--actor-id=4')));
c3stop(static function()use($invoiceOffNoActor) {
    Cf03LabContract::arguments($invoiceOffNoActor);
},'Invoice member OFF still needs actor','CF03InvalidExpectedActor');

foreach (array('B','C') as $invoiceEnvironment) {
    list($expected,$before,$after)=c3make($invoiceEnvironment,'invoice-existing','off');
    Cf03LabContract::baseline($before,$expected,$invoiceEnvironment);
    c3ok(Cf03LabContract::verify($before,$after,$expected,$invoiceEnvironment)['status']==='PASS',
        'invoice without Agenda '.$invoiceEnvironment);
    list($expected,$before,$after)=c3make($invoiceEnvironment,'invoice-existing','on');
    $before['invoice_agenda_flags']['BILL_CREATE']=true;
    $after['invoice_agenda_flags']['BILL_CREATE']=true;
    $after['invoice_agenda_events'][]=array('id'=>54,'code'=>'AC_BILL_CREATE',
        'invoice_id'=>60,'entity'=>1,'author_id'=>4,'owner_id'=>4);
    $after['invoice_agenda_resources'][]=array('id'=>55,'agenda_id'=>54,
        'element_type'=>'user','actor_id'=>4);
    $after['counts']['actioncomm']++;
    $after['counts']['actioncomm_resources']++;
    Cf03LabContract::baseline($before,$expected,$invoiceEnvironment);
    c3ok(Cf03LabContract::verify($before,$after,$expected,$invoiceEnvironment)['status']==='PASS',
        'two configured invoice Agenda actions '.$invoiceEnvironment);
}
// End-to-end CLI regression on a synthetic file tree: first --verify is
// sealed BEFORE cf03Configure() finds its deliberately absent conf.php.
// The second invocation cannot re-use the same case (no database accessed).
$caseRoot=sys_get_temp_dir().'/cf03-seal-'.bin2hex(random_bytes(7));
$fakeDoc=$caseRoot.'/site/htdocs';
$privateRoot=$caseRoot.'/private';
c3ok(mkdir($fakeDoc,0700,true),'synthetic hosted webroot');
c3ok(mkdir($privateRoot,0700),'synthetic hosted Evidence parent');
$caseDir=$privateRoot.'/cf03-d-verifysealfail1';
list($expected,$beforeState,$unchanged)=c3make('D','no-change','off');
Cf03LabContract::baseline($beforeState,$expected,'D');
Cf03Evidence::directory($caseDir,true,'D',$privateRoot,$fakeDoc,dirname(__DIR__,2));
Cf03Evidence::json($caseDir,'baseline.json',array(
    'case'=>'CF03','environment'=>'D','expected'=>$expected,
    'before'=>$beforeState,'created_utc'=>gmdate('c')));
Cf03Evidence::exclusive($caseDir,'baseline.sha256',
    hash_file('sha256',$caseDir.'/baseline.json')."\n");
$flags=array(
    '--verify','--environment=D','--document-root='.$fakeDoc,
    '--db-host=127.0.0.1','--db-port=3306','--db-name=cf03_synthetic',
    '--db-user=cf03_synthetic','--scenario=no-change','--member-id=271',
    '--member-ref=MEM2611-0271',
    '--marker=CF03LAB-D-ABCDEF123','--start=2080-01-01',
    '--end=2080-12-31','--amount=120.00','--agenda=off',
    '--evidence-root='.$privateRoot,'--evidence-dir='.$caseDir
);
$command=escapeshellarg(PHP_BINARY).' '
    .escapeshellarg(dirname(__DIR__,2).'/tools/cf03-lab.php').' '
    .implode(' ',array_map('escapeshellarg',$flags)).' 2>&1';
$output=array();$exitStatus=0;
exec($command,$output,$exitStatus);
$response=json_decode(implode("\n",$output),true);
c3ok($exitStatus===1 && is_array($response)
    && ($response['reason']??null)==='CF03WrongDolibarrRoot',
    'first sealed verify stops on unavailable external config');
c3ok(is_file($caseDir.'/verify.started'),'CLI seals verify before configure');
$verdict=json_decode(file_get_contents($caseDir.'/verdict.json'),true);
c3ok(($verdict['reason']??null)==='CF03WrongDolibarrRoot',
    'early STOP persisted as exclusive verdict');
$output=array();$exitStatus=0;
exec($command,$output,$exitStatus);
$response=json_decode(implode("\n",$output),true);
c3ok($exitStatus===1 && is_array($response)
    && ($response['reason']??null)==='CF03EvidenceExistsNoRetry',
    'repeated verify must STOP at admission without external config');
$still=json_decode(file_get_contents($caseDir.'/verdict.json'),true);
c3ok(($still['reason']??null)==='CF03WrongDolibarrRoot',
    'second attempt must not overwrite original failure');
foreach (scandir($caseDir) as $name) {
    if($name!=='.' && $name!=='..') { unlink($caseDir.'/'.$name); }
}
rmdir($caseDir);rmdir($privateRoot);rmdir($fakeDoc);
rmdir($caseRoot.'/site');rmdir($caseRoot);
echo 'CF03 A-D pure harness contracts: '.$passed.' assertions PASS (no DB/HTTP).'.PHP_EOL;
