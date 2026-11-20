<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
 * SPDX-License-Identifier: GPL-3.0-or-later
 * Pure CF-03 operator contract: no DB connection and no business SQL.
 */
require_once __DIR__.'/cf03-evidence.lib.php';

final class Cf03LabContract
{
    const COUNTS = array('adherent', 'subscription', 'actioncomm', 'actioncomm_resources',
        'societe', 'socpeople', 'facture', 'facturedet', 'element_element',
        'paiement', 'paiement_facture', 'bank');
    const BASIC_ENGINES = array('adherent', 'adherent_type', 'subscription',
        'actioncomm', 'actioncomm_resources');
    const INVOICE_ENGINES = array('societe', 'societe_extrafields',
        'socpeople', 'socpeople_extrafields', 'societe_commerciaux',
        'facture', 'facture_extrafields', 'facturedet',
        'facturedet_extrafields', 'element_element', 'ecm_files');

    public static function need($condition, $reason)
    {
        if (!$condition) { throw new RuntimeException($reason); }
    }

    public static function environments()
    {
        return array(
            'A' => array('family'=>'mysql', 'profile'=>'CoreGuarded', 'version'=>'8.4.13',
                'document_root'=>'/home/marcel/dolibarr-i105-v24-mysql/htdocs',
                'data_root'=>'/home/marcel/dolibarr-i105-v24-mysql-data',
                'db_host'=>'127.0.0.1', 'db_port'=>3307, 'db_name'=>'dolibarr_i105_v24',
                'db_user'=>'dolibarr_i105_v24_limited', 'core_version'=>'24.0.3',
                'web_url'=>'http://i105-v24.localhost:8091/', 'ps'=>'denied'),
            'B' => array('family'=>'mariadb', 'profile'=>'MariaDBSnapshot', 'version'=>'10.11.19',
                'document_root'=>'/home/marcel/dolibarr-v24/htdocs',
                'data_root'=>'/home/marcel/dolibarr-v24-data',
                'db_host'=>'localhost', 'db_port'=>3306, 'db_name'=>'dolibarr_db_v24',
                'db_user'=>null, 'core_version'=>'24.0.3',
                'web_url'=>'http://doli24.test:8090/', 'ps'=>'not_required'),
            'C' => array('family'=>'mysql', 'profile'=>'EnhancedMySQL', 'version'=>'8.4.13',
                'document_root'=>'/home/marcel/dolibarr-i105-v24-mysql/htdocs',
                'data_root'=>'/home/marcel/dolibarr-i105-v24-mysql-data',
                'db_host'=>'127.0.0.1', 'db_port'=>3307, 'db_name'=>'dolibarr_i105_v24',
                'db_user'=>null, 'core_version'=>'24.0.3',
                'web_url'=>'http://i105-v24.localhost:8091/', 'ps'=>'select'),
            'D' => array('family'=>'mysql', 'profile'=>'CoreGuarded', 'version'=>'8.4.13',
                'document_root'=>null, 'data_root'=>null,
                'db_host'=>null, 'db_port'=>null, 'db_name'=>null,
                'db_user'=>null, 'core_version'=>'22.0.5',
                'web_url'=>null, 'ps'=>'denied')
        );
    }

    /** V22 defines DOL_VERSION in filefunc; V24 uses a dedicated version file.
     * Read the installed source without executing bootstrap code or trusting DB labels.
     */
    public static function versionSource($expected)
    {
        if ($expected === '22.0.5') { return 'filefunc.inc.php'; }
        if ($expected === '24.0.3') { return 'version.inc.php'; }
        throw new RuntimeException('CF03CoreVersionPolicyUnsupported');
    }

    public static function assertCoreVersion($expected, $source)
    {
        self::versionSource($expected);
        self::need(is_string($source), 'CF03CoreVersionReadUnavailable');
        if ($expected === '22.0.5') {
            self::need(preg_match("/^[ \t]*define\('DOL_VERSION',\s*'22\.0\.5'\);/m",
                $source) === 1, 'CF03WrongCoreVersion');
            return;
        }
        self::need(preg_match("/^[ \t]*define\('DOL_MAJOR_VERSION',\s*'24'\);/m",
            $source) === 1
            && preg_match("/^[ \t]*define\('DOL_MINOR_VERSION',\s*'0\.3'\);/m",
                $source) === 1, 'CF03WrongCoreVersion');
    }

    public static function arguments(array $argv)
    {
        $mode=null; $opts=array();
        $modes=array('--preflight','--check-only','--baseline','--verify');
        $keys=array('environment','document-root','db-host','db-port','db-name',
            'db-user','scenario','member-id','member-ref','marker','start','end',
            'amount','agenda','evidence-dir','evidence-root','invoice-date',
            'invoice-quantity','invoice-product','invoice-vat','invoice-term',
            'invoice-payment-mode','invoice-bank','invoice-currency');
        foreach (array_slice($argv,1) as $arg) {
            if (in_array($arg,$modes,true)) {
                self::need($mode===null,'CF03DuplicateMode');
                $mode=substr($arg,2); continue;
            }
            self::need(strpos($arg,'--')===0 && strpos($arg,'=')!==false,'CF03InvalidArgument');
            list($key,$value)=explode('=',substr($arg,2),2);
            self::need(in_array($key,$keys,true) && !array_key_exists($key,$opts)
                && $value!=='','CF03InvalidArgument');
            $opts[$key]=$value;
        }
        self::need($mode!==null,'CF03MissingMode');
        $id=$opts['environment']??'';
        $all=self::environments();
        self::need(isset($all[$id]),'CF03UnknownEnvironment');
        $env=$all[$id];
        foreach (array('document-root','db-host','db-port','db-name','db-user') as $key) {
            $dest=str_replace('-','_',$key);
            if ($env[$dest] === null) {
                self::need(!empty($opts[$key]),'CF03MissingEnvironmentParameter');
                $env[$dest]=$opts[$key];
            } elseif (isset($opts[$key])) {
                self::need((string)$env[$dest]===$opts[$key],'CF03EnvironmentOverrideDenied');
            }
        }
        self::need(is_string($env['document_root'])
            && substr($env['document_root'],0,1)==='/'
            && preg_match('~^[a-zA-Z0-9_./-]+$~D',$env['document_root'])===1
            && preg_match('/^[a-zA-Z0-9._:-]{1,100}$/D',$env['db_host'])===1
            && preg_match('/^[a-zA-Z0-9_]{1,64}$/D',$env['db_name'])===1
            && preg_match('/^[a-zA-Z0-9_%-]{1,64}$/D',$env['db_user'])===1
            && ctype_digit((string)$env['db_port']) && (int)$env['db_port']>0
            && (int)$env['db_port']<=65535,'CF03InvalidEnvironmentParameter');
        $env['db_port']=(int)$env['db_port'];
        if ($id==='C') {
            self::need($env['db_user']!=='dolibarr_i105_v24_limited','CF03EnhancedMustUseSeparateAccount');
        }
        if ($mode!=='preflight') {
            self::need(in_array($opts['scenario']??null,
                array('contribution','no-change','invoice-existing'),true),'CF03InvalidScenario');
            self::need($id!=='A' || $opts['scenario']!=='invoice-existing',
                'CF03CoreGuardedInvoiceForbidden');
            self::need($id!=='D' || $opts['scenario']!=='invoice-existing',
                'CF03CoreGuardedInvoiceForbidden');
            self::need(is_string($opts['member-id']??null)
                && preg_match('/^[1-9][0-9]{0,8}$/D',$opts['member-id'])===1,
                'CF03InvalidMemberId');
            self::need(is_string($opts['member-ref']??null)
                && preg_match('/^[A-Z0-9][A-Z0-9_-]{4,31}$/D',$opts['member-ref'])===1,
                'CF03InvalidMemberRef');
            self::need(is_string($opts['marker']??null)
                && preg_match('/^CF03LAB-'.preg_quote($id,'/').'-[A-Z0-9]{8,20}$/D',
                    $opts['marker'])===1,'CF03InvalidFixtureMarker');
            self::need(is_string($opts['start']??null) && is_string($opts['end']??null)
                && preg_match('/^20[5-9][0-9]-[0-9]{2}-[0-9]{2}$/D',$opts['start'])===1
                && preg_match('/^20[5-9][0-9]-[0-9]{2}-[0-9]{2}$/D',$opts['end'])===1
                && self::day($opts['start']) && self::day($opts['end'])
                && $opts['end']>$opts['start'],'CF03InvalidDates');
            self::need(is_string($opts['amount']??null)
                && preg_match('/^[1-9][0-9]{0,5}(?:\.[0-9]{1,2})?$/D',
                    $opts['amount'])===1,'CF03InvalidAmount');
            self::need(in_array($opts['agenda']??null,array('on','off'),true),
                'CF03InvalidAgenda');
            $invoiceKeys=array('invoice-date','invoice-quantity','invoice-product',
                'invoice-vat','invoice-term','invoice-payment-mode','invoice-bank',
                'invoice-currency');
            if (($opts['scenario']??null)==='invoice-existing') {
                foreach ($invoiceKeys as $key) {
                    self::need(isset($opts[$key]),'CF03MissingInvoiceExpectedField');
                }
                self::need(preg_match('/^20[2-9][0-9]-[0-9]{2}-[0-9]{2}$/D',
                    $opts['invoice-date'])===1 && self::day($opts['invoice-date']),
                    'CF03InvalidInvoiceDate');
                foreach (array('invoice-product','invoice-term','invoice-payment-mode',
                    'invoice-bank') as $key) {
                    self::need(preg_match('/^[0-9]{1,9}$/D',$opts[$key])===1,
                        'CF03InvalidInvoiceExpectedField');
                }
                self::need(preg_match('/^[1-9][0-9]{0,5}(?:\\.[0-9]{1,4})?$/D',
                    $opts['invoice-quantity'])===1
                    && preg_match('/^[0-9]{1,3}(?:\\.[0-9]{1,4})?$/D',
                    $opts['invoice-vat'])===1
                    && preg_match('/^[A-Z]{3}$/D',$opts['invoice-currency'])===1,
                    'CF03InvalidInvoiceExpectedField');
            } else {
                foreach ($invoiceKeys as $key) {
                    self::need(!isset($opts[$key]),'CF03InvoiceOptionsNotPermitted');
                }
            }
        }
        $path=$opts['evidence-dir']??'';
        $evidenceRoot=$opts['evidence-root']??null;
        if ($mode==='baseline' || $mode==='verify') {
            Cf03Evidence::choice($id,$path,$evidenceRoot);
        } else {
            self::need($path==='' && $evidenceRoot===null,
                'CF03EvidencePathNotPermitted');
        }
        return array($mode,$id,$env,$opts);
    }

    private static function day($date)
    {
        $parts=explode('-',$date);
        return checkdate((int)$parts[1],(int)$parts[2],(int)$parts[0]);
    }

    public static function expected(array $opts)
    {
        $e=array('scenario'=>$opts['scenario'],'member_id'=>(int)$opts['member-id'],
            'member_ref'=>$opts['member-ref'],'marker'=>$opts['marker'],
            'start'=>$opts['start'],'end'=>$opts['end'],
            'amount'=>$opts['amount'],'agenda'=>$opts['agenda']);
        if ($e['scenario']==='invoice-existing') {
            $e['invoice']=array('date'=>$opts['invoice-date'],
                'quantity'=>(float)$opts['invoice-quantity'],
                'product'=>(int)$opts['invoice-product'],
                'vat'=>(float)$opts['invoice-vat'],
                'term'=>(int)$opts['invoice-term'],
                'payment_mode'=>(int)$opts['invoice-payment-mode'],
                'bank'=>(int)$opts['invoice-bank'],
                'currency'=>$opts['invoice-currency']);
        }
        return $e;
    }

    public static function baseline(array $s,array $e,$id)
    {
        self::need(($s['environment']??null)===$id && ($s['profile']['name']??null)
            ===self::environments()[$id]['profile'],'CF03WrongProfile');
        self::need(($s['identity']['autocommit']??null)==='1'
            && ($s['identity']['completion']??null)==='NO_CHAIN',
            'CF03WrongTransactionSettings');
        $m=$s['member']??array();
        self::need(($m['id']??null)===$e['member_id']
            && ($m['ref']??null)===$e['member_ref']
            && ($m['firstname']??null)==='Synthetic'
            && ($m['lastname']??null)===$e['marker']
            && ($m['email_hash']??null)===hash('sha256',strtolower($e['marker']).'@example.invalid')
            && ($m['entity']??null)===1 && ($m['status']??null)===1
            && array_key_exists('datefin',$m) && $m['datefin']===null
            && ($m['type_id']??0)>0,'CF03FixtureMismatch');
        self::need(($s['type']['id']??null)===$m['type_id']
            && ($s['type']['status']??null)===1
            && isset($s['type']['amount'])
            && abs((float)$s['type']['amount']-(float)$e['amount'])<0.000001,
            'CF03MemberTypeMismatch');
        self::need(($s['agenda_enabled']??null)===($e['agenda']==='on'),
            'CF03AgendaMismatch');
        self::need(count($s['subscriptions']??array())===0
            && count($s['agenda_events']??array())===0
            && count($s['invoices']??array())===0,'CF03FixtureAlreadyUsed');
        self::need(($e['scenario']==='invoice-existing')===($m['fk_soc']>0),
            'CF03CustomerFixtureMismatch');
        foreach (self::BASIC_ENGINES as $table) {
            if ($e['agenda']==='off' && in_array($table,array('actioncomm','actioncomm_resources'),true)) {
                continue;
            }
            self::need(($s['engines'][$table]??null)==='InnoDB','CF03NonTransactionalTable');
        }
        if ($e['scenario']==='invoice-existing') {
            self::need(isset($e['invoice']) && is_array($e['invoice']),
                'CF03MissingInvoiceExpectedField');
            self::need(($s['invoice_agenda_supported']??null)===true,
                'CF03InvoiceAgendaConfigUnsupported');
            foreach (self::INVOICE_ENGINES as $table) {
                self::need(($s['engines'][$table]??null)==='InnoDB','CF03NonTransactionalInvoiceTable');
            }
            self::need(($s['customer_exists']??null)===true,'CF03CustomerNotFound');
            self::need(($s['customer']['id']??null)===$m['fk_soc']
                && ($s['customer']['entity']??null)===1
                && ($s['customer']['client']??0)>0,
                'CF03InvoiceCustomerMismatch');
        } else {
            self::need($m['fk_soc']===0,'CF03UnexpectedCustomer');
        }
        foreach (self::COUNTS as $table) {
            self::need(isset($s['counts'][$table]) && is_int($s['counts'][$table])
                && $s['counts'][$table]>=0,'CF03MissingCount');
        }
    }

    public static function verify(array $before,array $after,array $e,$id)
    {
        self::baseline($before,$e,$id);
        foreach (array('environment','identity','profile','engines','type',
            'source_hashes','deployed_source_hashes','deployment_kind','core_source_hashes','config_sha256','performance_schema','agenda_enabled','customer_exists','customer','invoice_agenda_flags','invoice_agenda_supported','schema_columns') as $field) {
            self::need(array_key_exists($field,$after)
                && $before[$field]===$after[$field],'CF03ContextChanged');
        }
        foreach ($before['member'] as $field=>$value) {
            if ($field==='datefin') { continue; }
            self::need(array_key_exists($field,$after['member']??array())
                && $after['member'][$field]===$value,'CF03MemberMutated');
        }
        $scenario=$e['scenario'];
        $positive=$scenario!=='no-change';
        self::need(($after['member']['datefin']??null)===
            ($positive?$e['end'].' 00:00:00':null),'CF03MemberEndMismatch');
        $subs=$after['subscriptions']??array();
        self::need(count($subs)===($positive?1:0),'CF03SubscriptionCountMismatch');
        if ($positive) {
            $sub=$subs[0];
            self::need(($sub['id']??0)>0 && ($sub['member_id']??null)===$e['member_id']
                && ($sub['type_id']??null)===$before['member']['type_id']
                && abs((float)($sub['amount']??-1)-(float)$e['amount'])<0.000001
                && ($sub['start']??null)===$e['start'].' 00:00:00'
                && ($sub['end']??null)===$e['end'].' 00:00:00'
                && ($sub['marker']??false)===true,'CF03SubscriptionPostconditionMismatch');
        }
        $agendaExpected=$positive && $e['agenda']==='on' ? 1 : 0;
        self::need(count($after['agenda_events']??array())===$agendaExpected
            && count($after['agenda_resources']??array())===$agendaExpected,
            'CF03AgendaCountMismatch');
        if ($agendaExpected===1) {
            $event=$after['agenda_events'][0]; $actor=$after['agenda_resources'][0];
            self::need(($event['member_id']??null)===$e['member_id']
                && ($event['code']??null)==='AC_MEMBER_SUBSCRIPTION_CREATE'
                && ($actor['agenda_id']??null)===$event['id']
                && ($actor['element_type']??null)==='user'
                && ($actor['actor_id']??0)>0,'CF03AgendaActorMismatch');
        }
        $invoices=$after['invoices']??array();
        self::need(count($invoices)===($scenario==='invoice-existing'?1:0),
            'CF03InvoiceCountMismatch');
        if ($scenario==='invoice-existing') {
            $invoice=$invoices[0];
            $plan=$e['invoice'];
            self::need(($invoice['id']??0)>0
                && ($invoice['subscription_id']??null)===$subs[0]['id']
                && ($invoice['fk_soc']??null)===$before['member']['fk_soc']
                && ($invoice['entity']??null)===1
                && ($invoice['type']??null)===0 && ($invoice['status']??null)===1
                && ($invoice['paid']??null)===0
                && !empty($invoice['ref']) && strpos($invoice['ref'],'PROV')===false
                && ($invoice['date']??null)===$plan['date']
                && ($invoice['term']??null)===$plan['term']
                && ($invoice['payment_mode']??null)===$plan['payment_mode']
                && ($invoice['bank']??null)===$plan['bank']
                && ($invoice['currency']??null)===$plan['currency']
                && abs((float)($invoice['total']??-1)-(float)$e['amount'])<0.000001
                && count($invoice['lines']??array())===1,
                'CF03InvoicePostconditionMismatch');
            $line=$invoice['lines'][0];
            self::need(($line['id']??0)>0
                && ($line['marker']??false)===true
                && ($line['product']??null)===$plan['product']
                && ($line['product_type']??null)===1
                && ($line['date_start']??null)===$e['start'].' 00:00:00'
                && abs((float)($line['vat']??-1)-$plan['vat'])<0.000001
                && abs((float)($line['quantity']??-1)-$plan['quantity'])<0.000001
                && abs((float)($line['total']??-1)-(float)$e['amount'])<0.000001,
                'CF03InvoiceLineMismatch');
            $invoiceEvents=$after['invoice_agenda_events']??array();
            $invoiceResources=$after['invoice_agenda_resources']??array();
            $wanted=array();
            if ($e['agenda']==='on') {
                foreach (array('BILL_CREATE','BILL_VALIDATE') as $action) {
                    if (!empty($before['invoice_agenda_flags'][$action])) {
                        $wanted[]='AC_'.$action;
                    }
                }
            }
            $observed=array();
            foreach ($invoiceEvents as $event) {
                self::need(($event['id']??0)>0
                    && ($event['invoice_id']??null)===$invoice['id']
                    && in_array($event['code']??'', $wanted,true),
                    'CF03InvoiceAgendaMismatch');
                $observed[]=$event['code'];
            }
            sort($wanted);sort($observed);
            self::need($observed===$wanted,'CF03InvoiceAgendaMismatch');
            $actors=array();
            foreach ($invoiceResources as $res) {
                self::need(($res['id']??0)>0
                    && ($res['element_type']??null)==='user'
                    && ($res['actor_id']??0)>0
                    && in_array($res['agenda_id']??0,
                        array_column($invoiceEvents,'id'),true),
                    'CF03InvoiceAgendaActorMismatch');
                $actors[]=$res['agenda_id'];
            }
            sort($actors);
            $eventIds=array_column($invoiceEvents,'id'); sort($eventIds);
            self::need($actors===$eventIds,'CF03InvoiceAgendaActorMismatch');
        }
        foreach (self::COUNTS as $table) {
            $delta=0;
            if ($positive && $table==='subscription') { $delta=1; }
            if ($scenario==='contribution' && $e['agenda']==='on'
                && in_array($table,array('actioncomm','actioncomm_resources'),true)) { $delta=1; }
            if ($scenario==='invoice-existing' && in_array($table,
                array('facture','facturedet','element_element'),true)) { $delta=1; }
            $actual=$after['counts'][$table]??null;
            $minimal=$before['counts'][$table]+$delta;
            if ($scenario==='invoice-existing'
                && in_array($table,array('actioncomm','actioncomm_resources'),true)) {
                $minimal+=count($after[$table==='actioncomm'
                    ? 'invoice_agenda_events':'invoice_agenda_resources']??array());
            }
            self::need($actual===$minimal,'CF03UnexpectedTableDelta');
        }
        return array('status'=>'PASS','environment'=>$id,'scenario'=>$scenario,
            'subscription_id'=>$positive?$subs[0]['id']:null,
            'invoice_id'=>$scenario==='invoice-existing'?$invoices[0]['id']:null,
            'agenda_count'=>$agendaExpected,
            'invoice_agenda_count'=>$scenario==='invoice-existing'
                ? count($after['invoice_agenda_events']):0,
            'outcome'=>$scenario==='no-change'?'selected_database_state_unchanged':
                ($scenario==='invoice-existing'?'linked_invoice_database_rows_observed':
                'persisted_core_contribution_observed'),
            'acknowledgement'=>'not_determined_by_database_readback',
            'pdf_smtp'=>'not_determined_by_database_readback');
    }
}
