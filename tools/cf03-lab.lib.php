<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
 * SPDX-License-Identifier: GPL-3.0-or-later
 * Pure CF-03 operator contract: no DB connection and no business SQL.
 */
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

    public static function arguments(array $argv)
    {
        $mode=null; $opts=array();
        $modes=array('--preflight','--check-only','--baseline','--verify');
        $keys=array('environment','document-root','db-host','db-port','db-name',
            'db-user','scenario','member-id','member-ref','marker','start','end',
            'amount','agenda','evidence-dir');
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
        }
        $path=$opts['evidence-dir']??'';
        if ($mode==='baseline' || $mode==='verify') {
            self::need(preg_match('~^/var/lib/dolibarr-test-results/cf03-'
                .strtolower($id).'-[a-z0-9-]{6,40}$~D',$path)===1,
                'CF03UnsafeEvidencePath');
        } else {
            self::need($path==='','CF03EvidencePathNotPermitted');
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
        return array('scenario'=>$opts['scenario'],'member_id'=>(int)$opts['member-id'],
            'member_ref'=>$opts['member-ref'],'marker'=>$opts['marker'],
            'start'=>$opts['start'],'end'=>$opts['end'],
            'amount'=>$opts['amount'],'agenda'=>$opts['agenda']);
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
            foreach (self::INVOICE_ENGINES as $table) {
                self::need(($s['engines'][$table]??null)==='InnoDB','CF03NonTransactionalInvoiceTable');
            }
            self::need(($s['customer_exists']??null)===true,'CF03CustomerNotFound');
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
            'source_hashes','core_source_hashes','config_sha256','performance_schema','agenda_enabled') as $field) {
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
            self::need(($invoice['id']??0)>0
                && ($invoice['subscription_id']??null)===$subs[0]['id']
                && ($invoice['fk_soc']??null)===$before['member']['fk_soc']
                && ($invoice['entity']??null)===1
                && ($invoice['type']??null)===0 && ($invoice['status']??null)===1
                && ($invoice['paid']??null)===0
                && !empty($invoice['ref']) && strpos($invoice['ref'],'PROV')===false
                && abs((float)($invoice['total']??-1)-(float)$e['amount'])<0.000001
                && count($invoice['lines']??array())===1
                && abs((float)($invoice['lines'][0]['total']??-1)-(float)$e['amount'])<0.000001
                && ($invoice['lines'][0]['marker']??false)===true,
                'CF03InvoicePostconditionMismatch');
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
            if ($scenario==='invoice-existing' && $e['agenda']==='on'
                && in_array($table,array('actioncomm','actioncomm_resources'),true)) {
                self::need(is_int($actual) && $actual>=$minimal+1,
                    'CF03AgendaInvoiceCountMismatch');
            } else {
                self::need($actual===$minimal,'CF03UnexpectedTableDelta');
            }
        }
        return array('status'=>'PASS','environment'=>$id,'scenario'=>$scenario,
            'subscription_id'=>$positive?$subs[0]['id']:null,
            'invoice_id'=>$scenario==='invoice-existing'?$invoices[0]['id']:null,
            'agenda_count'=>$agendaExpected,
            'outcome'=>$scenario==='no-change'?'selected_database_state_unchanged':
                ($scenario==='invoice-existing'?'linked_invoice_database_rows_observed':
                'persisted_core_contribution_observed'),
            'acknowledgement'=>'not_determined_by_database_readback',
            'pdf_smtp'=>'not_determined_by_database_readback');
    }
}
