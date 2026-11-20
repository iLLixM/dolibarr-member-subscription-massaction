<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
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
function c3stop($fn,$message)
{
    try { $fn(); }
    catch (RuntimeException $error) {
        c3ok(strpos($error->getMessage(),'CF03')===0,$message);
        return;
    }
    throw new RuntimeException('CF03 unit not denied: '.$message);
}
function c3config($id,$scenario='contribution',$agenda='on')
{
    $e=Cf03LabContract::environments()[$id];
    $marker='CF03LAB-'.$id.'-ABCDEF123';
    return array('scenario'=>$scenario,'member_id'=>271,'member_ref'=>'MEM2611-0271',
        'marker'=>$marker,'start'=>'2080-01-01','end'=>'2080-12-31',
        'amount'=>'120.00','agenda'=>$agenda);
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
        'engines'=>$engines,'type'=>array('id'=>2,'status'=>1,'amount'=>'120.00000000'),
        'source_hashes'=>array('php'=>str_repeat('b',64)),
        'config_sha256'=>str_repeat('c',64),
        'performance_schema'=>$env['ps']==='select'?'select_allowed':
            ($env['ps']==='denied'?'select_denied_1142':'not_required'),
        'agenda_enabled'=>$agenda==='on',
        'customer_exists'=>$scenario==='invoice-existing',
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
                'member_id'=>271);
            $after['agenda_resources'][]=array('id'=>51,'agenda_id'=>50,
                'element_type'=>'user','actor_id'=>4);
            $after['counts']['actioncomm']++;
            $after['counts']['actioncomm_resources']++;
        }
        if ($scenario==='invoice-existing') {
            $after['invoices'][]=array('id'=>60,'subscription_id'=>57,'fk_soc'=>5,
                'entity'=>1,'type'=>0,'status'=>1,'paid'=>0,
                'ref'=>'F-00060','total'=>'120.00',
                'lines'=>array(array('id'=>61,'total'=>'120.00','marker'=>true)));
            foreach (array('facture','facturedet','element_element') as $name) {
                $after['counts'][$name]++;
            }
            if ($agenda==='on') {
                $after['counts']['actioncomm']++;
                $after['counts']['actioncomm_resources']++;
            }
        }
    }
    return array($expected,$s,$after);
}
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
    },'no accidental override '.$id);
    foreach(array('contribution','no-change') as $scenario) {
        foreach(array('on','off') as $agenda) {
            list($e,$before,$after)=c3make($id,$scenario,$agenda);
            Cf03LabContract::baseline($before,$e,$id);
            c3ok(Cf03LabContract::verify($before,$after,$e,$id)['status']==='PASS',
                'scenario '.$id.' '.$scenario.' '.$agenda);
            $wrong=$after;$wrong['config_sha256']=str_repeat('f',64);
            c3stop(static function()use($before,$wrong,$e,$id){
                Cf03LabContract::verify($before,$wrong,$e,$id);
            },'configuration changed');
            $wrong=$after;$wrong['profile']['raw_hash']=str_repeat('f',64);
            c3stop(static function()use($before,$wrong,$e,$id){
                Cf03LabContract::verify($before,$wrong,$e,$id);
            },'profile changed');
            $wrong=$after;$wrong['counts']['bank']++;
            c3stop(static function()use($before,$wrong,$e,$id){
                Cf03LabContract::verify($before,$wrong,$e,$id);
            },'unexpected bank DML');
            $wrong=$after;$wrong['source_hashes']['php']=str_repeat('d',64);
            c3stop(static function()use($before,$wrong,$e,$id){
                Cf03LabContract::verify($before,$wrong,$e,$id);
            },'source changed');
            $wrong=$before;$wrong['member']['datefin']='2079-12-31 00:00:00';
            c3stop(static function()use($wrong,$e,$id){
                Cf03LabContract::baseline($wrong,$e,$id);
            },'prior member end disallowed');
            if($scenario==='contribution') {
                $wrong=$after;$wrong['subscriptions'][0]['marker']=false;
                c3stop(static function()use($before,$wrong,$e,$id){
                    Cf03LabContract::verify($before,$wrong,$e,$id);
                },'marker required');
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
        },'invoice marker');
        $wrong=$after;$wrong['invoices'][0]['fk_soc']=66;
        c3stop(static function()use($before,$wrong,$e,$id){
            Cf03LabContract::verify($before,$wrong,$e,$id);
        },'invoice customer');
    }
}
c3stop(static function(){
    Cf03LabContract::arguments(array('cf03-lab.php','--preflight','--environment=C',
        '--db-user=dolibarr_i105_v24_limited'));
},'C must not use A limited account');
c3stop(static function(){
    Cf03LabContract::arguments(array('cf03-lab.php','--baseline','--environment=A',
        '--scenario=invoice-existing','--member-id=1',
        '--member-ref=MEM2611-0271','--marker=CF03LAB-A-ABCDEF123',
        '--start=2080-01-01','--end=2080-12-31','--amount=120',
        '--agenda=on','--evidence-dir=/var/lib/dolibarr-test-results/cf03-a-testunit'));
},'CoreGuarded invoice denied');
c3stop(static function(){
    Cf03LabContract::arguments(array('cf03-lab.php','--preflight','--environment=A',
        '--evidence-dir=/tmp/unsafe'));
},'preflight cannot write evidence');
echo 'CF03 A-D pure harness contracts: '.$passed.' assertions PASS (no DB/HTTP).'.PHP_EOL;
