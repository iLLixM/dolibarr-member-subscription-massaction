<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
 * SPDX-License-Identifier: GPL-3.0-or-later
 * Actual cf03State() SQL projection on a strict SELECT-only fake resultset.
 * No database connection, HTTP request, business DML or Evidence file writes.
 */
if (PHP_SAPI!=='cli') { exit(64); }
require_once __DIR__.'/../../tools/cf03-lab.php';
$checks=0;
function readerOk($ok,$label)
{
    global $checks;
    if (!$ok) { throw new RuntimeException('CF03 reader unit failed: '.$label); }
    $checks++;
}
function readerStop($callback,$expectedReason,$label)
{
    try { $callback(); }
    catch (RuntimeException $error) {
        readerOk($error->getMessage()===$expectedReason,
            $label.': expected '.$expectedReason.', got '.$error->getMessage());
        return;
    }
    throw new RuntimeException('CF03 reader unit expected STOP: '.$label);
}
final class Cf03FakeResult
{
    private $records;
    public function __construct(array $records) { $this->records=$records; }
    public function fetch_assoc() { return array_shift($this->records); }
    public function free() {}
}
final class Cf03FakeDb
{
    public $after;
    public $memberOn;
    public $billCreate;
    public $billValidate;
    public $lineOn;
    public $fault='';
    public $resourceActor=4;
    public $sql=array();
    public $events=array();
    public function __construct($after,$memberOn,$billCreate,$billValidate,$lineOn=false)
    {
        $this->after=$after;
        $this->memberOn=$memberOn;
        $this->billCreate=$billCreate;
        $this->billValidate=$billValidate;
        $this->lineOn=$lineOn;
        if (!$after) { return; }
        if ($memberOn) { $this->events[]=array('id'=>50,'code'=>'AC_MEMBER_SUBSCRIPTION_CREATE',
            'fk_element'=>271,'elementtype'=>'member','entity'=>1,
            'fk_user_author'=>4,'fk_user_action'=>4); }
        if ($billCreate) { $this->events[]=array('id'=>54,'code'=>'AC_BILL_CREATE',
            'fk_element'=>60,'elementtype'=>'facture','entity'=>1,
            'fk_user_author'=>4,'fk_user_action'=>4); }
        if ($billValidate) { $this->events[]=array('id'=>52,'code'=>'AC_BILL_VALIDATE',
            'fk_element'=>60,'elementtype'=>'facture','entity'=>1,
            'fk_user_author'=>4,'fk_user_action'=>4); }
    }
    private function projection($sql, array $records)
    {
        if (!preg_match('/^SELECT (.*?) FROM llx_actioncomm /s',$sql,$match)) {
            throw new RuntimeException('Invalid fake SELECT');
        }
        $fields=array_map('trim',explode(',',$match[1]));
        $result=array();
        foreach ($records as $record) {
            $projected=array();
            foreach ($fields as $field) {
                if (!array_key_exists($field,$record)) {
                    throw new RuntimeException('Missing fake field '.$field);
                }
                // Database resultset exposes ONLY columns actually SELECTed.
                if ($this->fault===$field) { continue; }
                $projected[$field]=$record[$field];
            }
            $result[]=$projected;
        }
        return $result;
    }
    public function query($sql)
    {
        $this->sql[]=$sql;
        $marker='CF03LAB-B-ABCDEF123';
        if (strpos($sql,'FROM llx_adherent WHERE')!==false) {
            $rows=array(array('rowid'=>271,'ref'=>'MEM2611-0271',
                'firstname'=>'Synthetic','lastname'=>$marker,
                'email'=>strtolower($marker).'@example.invalid',
                'entity'=>1,'fk_adherent_type'=>2,'fk_soc'=>5,'statut'=>1,
                'datefin'=>$this->after?'2080-12-31 00:00:00':null));
        } elseif (strpos($sql,'FROM llx_adherent_type WHERE')!==false) {
            $rows=array(array('rowid'=>2,'statut'=>0,'entity'=>1,'subscription'=>'1',
                'amount'=>'120.00000000','duration'=>12));
        } elseif (strpos($sql,'FROM llx_const ')!==false) {
            $module=$this->memberOn || $this->billCreate || $this->billValidate || $this->lineOn;
            $flags=array('MAIN_MODULE_AGENDA'=>$module?'1':'0',
                'MAIN_AGENDA_ACTIONAUTO_MEMBER_SUBSCRIPTION_CREATE'=>$this->memberOn?'1':'0',
                'MAIN_AGENDA_ACTIONAUTO_BILL_CREATE'=>$this->billCreate?'1':'0',
                'MAIN_AGENDA_ACTIONAUTO_BILL_VALIDATE'=>$this->billValidate?'1':'0',
                'MAIN_AGENDA_ACTIONAUTO_LINEBILL_INSERT'=>$this->lineOn?'1':'0',
                'ADHERENT_PRODUCT_ID_FOR_SUBSCRIPTIONS'=>'0');
            $rows=array();
            foreach ($flags as $name=>$value) {
                $rows[]=array('name'=>$name,'value'=>$value,'entity'=>1);
            }
        } elseif (strpos($sql,'FROM llx_user WHERE')!==false) {
            $rows=array(array('rowid'=>4,'entity'=>1,'statut'=>1));
        } elseif (strpos($sql,'FROM llx_subscription WHERE')!==false
            && strpos($sql,'FROM llx_facture f JOIN')===false) {
            $rows=$this->after?array(array('rowid'=>57,'fk_adherent'=>271,'fk_type'=>2,
                'subscription'=>'120.00','dateadh'=>'2080-01-01 00:00:00',
                'datef'=>'2080-12-31 00:00:00','note'=>$marker)):array();
        } elseif (strpos($sql,"FROM llx_actioncomm ")!==false) {
            $type=strpos($sql,"elementtype='facture'")!==false?'facture':'member';
            $selected=array_values(array_filter($this->events,static function($event)use($type) {
                return $event['elementtype']===$type;
            }));
            $rows=$this->projection($sql,$selected);
        } elseif (strpos($sql,'FROM llx_facture f JOIN llx_element_element')!==false) {
            $rows=$this->after?array(array('rowid'=>60,'entity'=>1,'fk_soc'=>5,
                'type'=>0,'fk_statut'=>1,'paye'=>0,'ref'=>'F-00060',
                'total_ttc'=>'120.00','invoice_date'=>'2026-11-20',
                'fk_cond_reglement'=>1,'fk_mode_reglement'=>0,'fk_account'=>0,
                'multicurrency_code'=>'EUR','subscription_id'=>57)):array();
        } elseif (strpos($sql,'FROM llx_facturedet ')!==false) {
            $rows=$this->after?array(array('rowid'=>61,'total_ttc'=>'120.00',
                'description'=>$marker,'qty'=>'1','fk_product'=>0,
                'product_type'=>1,'tva_tx'=>'0.0',
                'start_date'=>'2080-01-01 00:00:00')):array();
        } elseif (strpos($sql,'FROM llx_actioncomm_resources WHERE')!==false) {
            preg_match('/fk_actioncomm=([0-9]+)/',$sql,$m);
            $eventId=isset($m[1])?(int)$m[1]:-1;
            $rows=array();
            foreach ($this->events as $event) {
                if ($event['id']===$eventId) {
                    $rows[]=array('rowid'=>$eventId+1,'element_type'=>'user',
                        'fk_element'=>$eventId===50?4:$this->resourceActor);
                }
            }
        } elseif (preg_match('/^SELECT COUNT\(\*\) AS n FROM llx_([a-z_]+)$/D',$sql,$m)) {
            $name=$m[1];$n=20;
            if ($this->after) {
                if ($name==='subscription' || in_array($name,
                    array('facture','facturedet','element_element'),true)) { $n++; }
                if ($name==='actioncomm' || $name==='actioncomm_resources') {
                    $n+=count($this->events);
                }
            }
            $rows=array(array('n'=>$n));
        } elseif (strpos($sql,'FROM llx_societe ')!==false) {
            $rows=array(array('rowid'=>5,'entity'=>1,'client'=>1));
        } else {
            throw new RuntimeException('Unmodeled SQL read: '.$sql);
        }
        return new Cf03FakeResult($rows);
    }
}
function cf03ReaderContext()
{
    $env=Cf03LabContract::environments()['B'];
    $engines=array_fill_keys(array_merge(Cf03LabContract::BASIC_ENGINES,
        Cf03LabContract::INVOICE_ENGINES),'InnoDB');
    return array('environment'=>'B','identity'=>array('schema'=>'fake',
        'effective_account'=>'fake@localhost','autocommit'=>'1',
        'completion'=>'NO_CHAIN'),'profile'=>array('name'=>$env['profile'],
        'family'=>$env['family'],'raw_hash'=>str_repeat('a',64)),
        'engines'=>$engines,'source_hashes'=>array('tools'=>str_repeat('b',64)),
        'source_manifest_version'=>'CF03-W01-MODULE-PHP-1',
        'deployed_source_hashes'=>array('module'=>str_repeat('c',64)),
        'deployment_kind'=>'source_checkout',
        'core_source_hashes'=>array('core'=>str_repeat('d',64)),
        'schema_columns'=>Cf03LabContract::columnPolicy(),
        'config_sha256'=>str_repeat('e',64),'performance_schema'=>'not_required');
}
function cf03ReaderExpected($memberOn)
{
    return array('scenario'=>'invoice-existing','member_id'=>271,
        'member_ref'=>'MEM2611-0271','marker'=>'CF03LAB-B-ABCDEF123',
        'start'=>'2080-01-01','end'=>'2080-12-31','amount'=>'120.00',
        'agenda'=>$memberOn?'on':'off','actor_id'=>4,
        'invoice'=>array('date'=>'2026-11-20','quantity'=>1.0,
            'product'=>0,'vat'=>0.0,'term'=>1,'payment_mode'=>0,'bank'=>0,
            'currency'=>'EUR'));
}
// Exercises real cf03State() from tools/cf03-lab.php, including the invoice
// SELECT, fetch_assoc projection, mapping and full verification contracts.
$cases=array(
    array(false,false,false,'all-off'),
    array(false,false,true,'member-off-bill-validate-on'),
    array(false,true,false,'member-off-bill-create-on'),
    array(false,true,true,'member-off-both-bill-on'),
    array(true,false,false,'member-on-bill-off'),
    array(true,false,true,'member-on-bill-validate-on'),
    array(true,true,false,'member-on-bill-create-on'),
    array(true,true,true,'member-on-both-bill-on')
);
foreach ($cases as $case) {
    list($memberOn,$create,$validate,$label)=$case;
    $expected=cf03ReaderExpected($memberOn);
    $beforeDb=new Cf03FakeDb(false,$memberOn,$create,$validate);
    $afterDb=new Cf03FakeDb(true,$memberOn,$create,$validate);
    $before=cf03State($beforeDb,cf03ReaderContext(),$expected);
    $after=cf03State($afterDb,cf03ReaderContext(),$expected);
    Cf03LabContract::baseline($before,$expected,'B');
    readerOk(Cf03LabContract::verify($before,$after,$expected,'B')['status']==='PASS',
        $label.' actual SQL-reader PASS');
    foreach ($afterDb->sql as $sql) {
        if (strpos($sql,"elementtype='facture'")!==false) {
            foreach (array('entity','fk_user_author','fk_user_action') as $field) {
                readerOk(strpos($sql,$field)!==false,
                    $label.' projection contains '.$field);
            }
        }
    }
    if (!$create && !$validate) {
        $unexpected=new Cf03FakeDb(true,$memberOn,true,false);
        $unexpectedState=cf03State($unexpected,cf03ReaderContext(),$expected);
        $unexpectedState['invoice_agenda_flags']=$before['invoice_agenda_flags'];
        readerStop(static function()use($before,$unexpectedState,$expected) {
            Cf03LabContract::verify($before,$unexpectedState,$expected,'B');
        },'CF03InvoiceAgendaMismatch',
            $label.' unconfigured invoice event rejected');
    }
    if ($create || $validate) {
        $missing=new Cf03FakeDb(true,$memberOn,
            $create && $validate ? $create : false,false);
        $missingState=cf03State($missing,cf03ReaderContext(),$expected);
        $missingState['invoice_agenda_flags']=$after['invoice_agenda_flags'];
        readerStop(static function()use($before,$missingState,$expected) {
            Cf03LabContract::verify($before,$missingState,$expected,'B');
        },'CF03InvoiceAgendaMismatch',$label.' missing required invoice event');
        foreach (array('fk_user_author','fk_user_action','entity') as $field) {
            $wrong=new Cf03FakeDb(true,$memberOn,$create,$validate);
            foreach ($wrong->events as &$event) {
                if ($event['code']===($validate?'AC_BILL_VALIDATE':'AC_BILL_CREATE')) {
                    $event[$field]=$field==='entity'?2:44;
                }
            }
            unset($event);
            $wrongState=cf03State($wrong,cf03ReaderContext(),$expected);
            readerStop(static function()use($before,$wrongState,$expected) {
                Cf03LabContract::verify($before,$wrongState,$expected,'B');
            },'CF03InvoiceAgendaActorMismatch',
                $label.' wrong '.$field.' from real projection');
        }
        $wrong=new Cf03FakeDb(true,$memberOn,$create,$validate);
        $wrong->resourceActor=44;
        $wrongState=cf03State($wrong,cf03ReaderContext(),$expected);
        readerStop(static function()use($before,$wrongState,$expected) {
            Cf03LabContract::verify($before,$wrongState,$expected,'B');
        },'CF03InvoiceAgendaActorMismatch',
            $label.' wrong user resource from actual SQL Resultset');
        $wrong=new Cf03FakeDb(true,$memberOn,$create,$validate);
        $wrong->fault='fk_user_author';
        readerStop(static function()use($wrong,$expected) {
            cf03State($wrong,cf03ReaderContext(),$expected);
        },'CF03InvoiceAgendaProjectionIncomplete',
            $label.' incomplete Resultset must fail closed');
    }
}
$expect=cf03ReaderExpected(false);
$lineDb=new Cf03FakeDb(false,false,false,true,true);
$line=cf03State($lineDb,cf03ReaderContext(),$expect);
readerStop(static function()use($line,$expect) {
    Cf03LabContract::baseline($line,$expect,'B');
},'CF03InvoiceAgendaConfigUnsupported','line insert ON must STOP at baseline');
echo 'CF03 invoice actual reader and independent Agenda flags: '.$checks
    .' assertions PASS (strict fake SELECT-only DB; no real DB/HTTP).'.PHP_EOL;
