<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
 * SPDX-License-Identifier: GPL-3.0-or-later
 * Parse ACTUAL published operator command blocks; never execute them.
 * No filesystem writes, DB, HTTP or business operations.
 */
if (PHP_SAPI!=='cli') { exit(64); }
require_once __DIR__.'/../../tools/cf03-lab.lib.php';
$runbook=file_get_contents(__DIR__.'/../../docs/cf03-operator-runbook.md');
if (!is_string($runbook)) { throw new RuntimeException('CF03RunbookNotReadable'); }
$passed=0;
function runbookOk($condition,$label)
{
    global $passed;
    if (!$condition) { throw new RuntimeException('CF03 runbook unit failed: '.$label); }
    $passed++;
}
function runbookArguments($runbook,$marker,$function,array $variables)
{
    $regex='/<!-- CF03-CASE-'.preg_quote($marker,'/').' -->\s*\x60{3}sh\s*(.*?)\x60{3}/s';
    runbookOk(preg_match($regex,$runbook,$m)===1,'documented block '.$marker);
    $fn='/\b'.preg_quote($function,'/').'\(\)\s*\{(.*?)\n\}/s';
    runbookOk(preg_match($fn,$m[1],$body)===1,'actual command '.$marker);
    preg_match_all('/--[a-z][a-z-]*=(?:"\$[A-Z_]+"|[A-Za-z0-9._:\/-]+)/',
        $body[1],$matches);
    runbookOk(count($matches[0])>=8,'full documented CLI arguments '.$marker);
    $args=array('cf03-lab.php','--check-only');
    foreach ($matches[0] as $arg) {
        if (preg_match('/^(--[a-z][a-z-]*=)"\$([A-Z_]+)"$/D',$arg,$sub)) {
            runbookOk(array_key_exists($sub[2],$variables),
                'declared variable '.$sub[2].' in '.$marker);
            $arg=$sub[1].$variables[$sub[2]];
        }
        $args[]=$arg;
    }
    return $args;
}
$shared=array(
    'MEMBER_ID'=>'271','MEMBER_REF'=>'MEM2611-0271',
    'ACTOR_ID'=>'4','MARKER'=>'CF03LAB-A-ABCDEF123',
    'ENVIRONMENT'=>'B','DB_USER'=>'mariadb_application',
    'INVOICE_DATE'=>'2026-11-20','INVOICE_PRODUCT'=>'0',
    'INVOICE_VAT'=>'0','INVOICE_TERM'=>'1',
    'INVOICE_PAYMENT_MODE'=>'0','INVOICE_BANK'=>'0',
    'INVOICE_CURRENCY'=>'EUR',
    'DOCUMENT_ROOT'=>'/synthetic/htdocs',
    'DB_HOST'=>'127.0.0.1','DB_PORT'=>'3306',
    'DB_NAME'=>'synthetic_hosted'
);
$cases=array(
    array('A','common','A','contribution',array(
        'MARKER'=>'CF03LAB-A-ABCDEF123')),
    array('INVOICE','invoice_case','B','invoice-existing',array(
        'MARKER'=>'CF03LAB-B-ABCDEF123','ENVIRONMENT'=>'B',
        'DB_USER'=>'mariadb_application')),
    array('INVOICE','invoice_case','C','invoice-existing',array(
        'MARKER'=>'CF03LAB-C-ABCDEF123','ENVIRONMENT'=>'C',
        'DB_USER'=>'enhanced_application')),
    array('D','hosted_case','D','contribution',array(
        'MARKER'=>'CF03LAB-D-ABCDEF123','DB_USER'=>'hosted_user'))
);
foreach ($cases as $case) {
    list($marker,$function,$environment,$scenario,$overrides)=$case;
    $variables=array_merge($shared,$overrides);
    $args=runbookArguments($runbook,$marker,$function,$variables);
    list($mode,$id,$policy,$opts)=Cf03LabContract::arguments($args);
    runbookOk($mode==='check-only' && $id===$environment
        && $opts['scenario']===$scenario,
        'valid documented case '.$environment.' '.$scenario);
    runbookOk(isset($opts['actor-id']) && $opts['actor-id']==='4',
        'authenticated operator option present '.$environment);
    if ($scenario==='invoice-existing') {
        runbookOk(($opts['invoice-quantity']??null)==='1'
            && ($opts['agenda']??null)==='off',
            'invoice flags and one-unit contract '.$environment);
    }
    $withoutActor=array_values(array_filter($args,static function($value){
        return strpos($value,'--actor-id=')!==0;
    }));
    try {
        Cf03LabContract::arguments($withoutActor);
        throw new RuntimeException('CF03RunbookUnexpectedActorAcceptance');
    } catch (RuntimeException $error) {
        runbookOk($error->getMessage()==='CF03InvalidExpectedActor',
            'actor omission rejected '.$environment);
    }
}
echo 'CF03 documented operator CLI contracts: '.$passed
    .' assertions PASS (no DB/HTTP).'.PHP_EOL;
