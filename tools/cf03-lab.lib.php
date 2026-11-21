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

    /** Columns actually read by the V22/V24 witness: fail before any case snapshot. */
    public static function columnPolicy()
    {
        return array(
            'adherent'=>array('rowid','ref','firstname','lastname','email','entity',
                'fk_adherent_type','fk_soc','statut','datefin'),
            'adherent_type'=>array('rowid','statut','entity','subscription','amount','duration'),
            'subscription'=>array('rowid','fk_adherent','fk_type','subscription','dateadh',
                'datef','note'),
            'actioncomm'=>array('id','code','fk_element','elementtype',
                'entity','fk_user_author','fk_user_action'),
            'actioncomm_resources'=>array('rowid','fk_actioncomm','element_type','fk_element'),
            'const'=>array('name','value','entity'),
            'societe'=>array('rowid','entity','client'),
            'facture'=>array('rowid','entity','fk_soc','type','fk_statut','paye',
                'ref','total_ttc','datef','fk_cond_reglement','fk_mode_reglement',
                'fk_account','multicurrency_code'),
            'facturedet'=>array('rowid','fk_facture','total_ttc','description',
                'qty','fk_product','product_type','tva_tx','date_start'),
            'element_element'=>array('fk_source','fk_target','sourcetype','targettype'),
            'user'=>array('rowid','entity','statut'),
            'product'=>array('rowid','entity','tosell','fk_product_type')
        );
    }

    public static function columnsComplete(array $observed)
    {
        foreach (self::columnPolicy() as $table=>$fields) {
            foreach ($fields as $column) {
                self::need(in_array($column,$observed[$table]??array(),true),
                    'CF03WitnessSchemaUnsupported');
            }
        }
    }

    /** A and C share the same source/schema. Only the approved DB user differs.
     * Actual paths, hosts, accounts and schemas belong in an untracked local
     * operator environment, never in the public Git tree.
     */
    private static function operatorValue($id, $name)
    {
        $value=getenv('CF03_'.$id.'_'.$name);
        return $value===false || $value==='' ? null : $value;
    }

    public static function environments()
    {
        $doc=self::operatorValue('A','DOCUMENT_ROOT');
        $data=self::operatorValue('A','DATA_ROOT');
        $host=self::operatorValue('A','DB_HOST');
        $port=self::operatorValue('A','DB_PORT');
        $schema=self::operatorValue('A','DB_NAME');
        $url=self::operatorValue('A','WEB_URL');
        return array(
            'A' => array('family'=>'mysql', 'profile'=>'CoreGuarded', 'version'=>'8.4.13',
                'document_root'=>$doc, 'data_root'=>$data,
                'db_host'=>$host, 'db_port'=>$port, 'db_name'=>$schema,
                'db_user'=>self::operatorValue('A','DB_USER'), 'core_version'=>'24.0.3',
                'php_target'=>'8.2', 'web_url'=>$url, 'ps'=>'denied'),
            'B' => array('family'=>'mariadb', 'profile'=>'MariaDBSnapshot', 'version'=>'10.11.19',
                'document_root'=>self::operatorValue('B','DOCUMENT_ROOT'),
                'data_root'=>self::operatorValue('B','DATA_ROOT'),
                'db_host'=>self::operatorValue('B','DB_HOST'),
                'db_port'=>self::operatorValue('B','DB_PORT'),
                'db_name'=>self::operatorValue('B','DB_NAME'),
                'db_user'=>self::operatorValue('B','DB_USER'),
                'core_version'=>'24.0.3', 'php_target'=>'8.2',
                'web_url'=>self::operatorValue('B','WEB_URL'), 'ps'=>'not_required'),
            'C' => array('family'=>'mysql', 'profile'=>'EnhancedMySQL', 'version'=>'8.4.13',
                'document_root'=>$doc, 'data_root'=>$data,
                'db_host'=>$host, 'db_port'=>$port, 'db_name'=>$schema,
                'db_user'=>self::operatorValue('C','DB_USER'), 'core_version'=>'24.0.3',
                'php_target'=>'8.2', 'web_url'=>$url, 'ps'=>'select'),
            'D' => array('family'=>'mysql', 'profile'=>'CoreGuarded', 'version'=>'8.4.13',
                'document_root'=>null, 'data_root'=>null,
                'db_host'=>null, 'db_port'=>null, 'db_name'=>null,
                'db_user'=>null, 'core_version'=>'22.0.5',
                'php_target'=>'8.3', 'web_url'=>null, 'ps'=>'denied')
        );
    }

    /** Real W01 environments bind the actual PHP CLI major/minor runtime. */
    public static function phpTarget($id)
    {
        $environments=self::environments();
        self::need(isset($environments[$id]),'CF03UnknownEnvironment');
        return $environments[$id]['php_target'];
    }

    public static function assertPhpRuntime($id,$version)
    {
        $target=self::phpTarget($id);
        self::need(is_string($version)
            && preg_match('/^([0-9]+)\\.([0-9]+)\\.[0-9]+(?:[A-Za-z0-9._+-]*)$/D',
                $version,$matches)===1
            && $matches[1].'.'.$matches[2]===$target,'CF03WrongPHPRuntime');
        return $target;
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
            'invoice-payment-mode','invoice-bank','invoice-currency','actor-id');
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
            self::need(is_string($all['A']['db_user']) && $env['db_user']!==$all['A']['db_user'],'CF03EnhancedMustUseSeparateAccount');
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
            // Positive invoice cases always bind the authenticated UI actor,
            // regardless of the independently configured member Agenda flag.
            if ($opts['scenario']==='invoice-existing'
                || ($opts['scenario']==='contribution' && $opts['agenda']==='on')) {
                self::need(isset($opts['actor-id'])
                    && preg_match('/^[1-9][0-9]{0,8}$/D',$opts['actor-id'])===1,
                    'CF03InvalidExpectedActor');
            } else {
                self::need(!isset($opts['actor-id']),'CF03UnexpectedActorOption');
            }
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
                self::need(($opts['invoice-quantity']==='1' || $opts['invoice-quantity']==='1.0')
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
            'amount'=>$opts['amount'],'agenda'=>$opts['agenda'],
            'actor_id'=>isset($opts['actor-id'])?(int)$opts['actor-id']:0);
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
        $runtime=self::assertPhpRuntime($id,$s['php_version']??null);
        self::need(($s['php_major_minor']??null)===$runtime,
            'CF03WrongPHPRuntime');
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
        // Dolibarr V22/V24 llx_adherent_type: statut=1 ACTIVE, 0 INACTIVE.
        // The paid-fixture contract also requires entity 1 and subscription '1'.
        self::need(($s['type']['id']??null)===$m['type_id']
            && ($s['type']['status']??null)===1
            && ($s['type']['entity']??null)===1
            && ($s['type']['subscription']??null)==='1'
            && isset($s['type']['amount'])
            && abs((float)$s['type']['amount']-(float)$e['amount'])<0.000001,
            'CF03MemberTypeMismatch');
        self::need(($s['agenda_enabled']??null)===($e['agenda']==='on'),
            'CF03AgendaMismatch');
        if ($e['scenario']==='invoice-existing'
            || ($e['scenario']==='contribution' && $e['agenda']==='on')) {
            self::need(($e['actor_id']??0)>0
                && ($s['actor']['id']??null)===$e['actor_id']
                && ($s['actor']['entity']??null)===1
                && ($s['actor']['status']??null)===1,
                'CF03ExpectedActorUnavailable');
        }
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
            self::need(isset($s['invoice_product'])
                && ($s['invoice_product']['id']??null)===$e['invoice']['product']
                && ($s['invoice_product']['entity']??null)===1
                && ($s['invoice_product']['status']??null)===1
                && in_array($s['invoice_product']['type']??null,array(0,1),true)
                && ($e['invoice']['quantity']??null)===1.0,
                'CF03InvoiceProductMismatch');
            self::need(($s['customer_exists']??null)===true,'CF03CustomerNotFound');
            self::need(($s['customer']['id']??null)===$m['fk_soc']
                && ($s['customer']['entity']??null)===1
                && in_array($s['customer']['client']??0,array(1,3),true),
                'CF03InvoiceCustomerMismatch');
        } else {
            self::need($m['fk_soc']===0,'CF03UnexpectedCustomer');
        }
        foreach (self::COUNTS as $table) {
            self::need(isset($s['counts'][$table]) && is_int($s['counts'][$table])
                && $s['counts'][$table]>=0,'CF03MissingCount');
        }
    }

    /** Bound Core event author/owner and default user resource; no arbitrary ID. */
    public static function eventActor(array $event, array $resource, array $e, $reason)
    {
        $id=$e['actor_id']??0;
        self::need($id>0 && ($event['id']??0)>0
            && ($event['entity']??null)===1
            && ($event['author_id']??null)===$id
            && ($event['owner_id']??null)===$id
            && ($resource['id']??0)>0
            && ($resource['agenda_id']??null)===$event['id']
            && ($resource['element_type']??null)==='user'
            && ($resource['actor_id']??null)===$id, $reason);
    }

    public static function verify(array $before,array $after,array $e,$id)
    {
        self::baseline($before,$e,$id);
        foreach (array('environment','identity','profile','engines','type',
            'php_version','php_major_minor','source_hashes','source_manifest_version','deployed_source_hashes','deployment_kind','core_source_hashes','config_sha256','performance_schema','agenda_enabled','customer_exists','customer','invoice_agenda_flags','invoice_agenda_supported','schema_columns','invoice_product','actor') as $field) {
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
        if ($agendaExpected===1) {
            self::need(($before['actor']['id']??null)===$e['actor_id']
                && ($before['actor']['entity']??null)===1
                && ($before['actor']['status']??null)===1,
                'CF03ExpectedActorUnavailable');
        }
        self::need(count($after['agenda_events']??array())===$agendaExpected
            && count($after['agenda_resources']??array())===$agendaExpected,
            'CF03AgendaCountMismatch');
        if ($agendaExpected===1) {
            $event=$after['agenda_events'][0]; $actor=$after['agenda_resources'][0];
            self::need(($event['member_id']??null)===$e['member_id']
                && ($event['code']??null)==='AC_MEMBER_SUBSCRIPTION_CREATE'
                ,'CF03AgendaActorMismatch');
            self::eventActor($event,$actor,$e,'CF03AgendaActorMismatch');
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
                && ($line['product_type']??null)===$before['invoice_product']['type']
                && ($line['date_start']??null)===$e['start'].' 00:00:00'
                && abs((float)($line['vat']??-1)-$plan['vat'])<0.000001
                && abs((float)($line['quantity']??-1)-$plan['quantity'])<0.000001
                && abs((float)($line['total']??-1)-(float)$e['amount'])<0.000001,
                'CF03InvoiceLineMismatch');
            $invoiceEvents=$after['invoice_agenda_events']??array();
            $invoiceResources=$after['invoice_agenda_resources']??array();
            $wanted=array();
            // Dolibarr stores member and invoice Agenda actions under
            // independent flags. --agenda refers to MEMBER only.
            foreach (array('BILL_CREATE','BILL_VALIDATE') as $action) {
                if (!empty($before['invoice_agenda_flags'][$action])) {
                    $wanted[]='AC_'.$action;
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
                $matching=null;
                foreach ($invoiceEvents as $event) {
                    if (($event['id']??null)===($res['agenda_id']??null)) {
                        $matching=$event; break;
                    }
                }
                self::need(is_array($matching),'CF03InvoiceAgendaActorMismatch');
                self::eventActor($matching,$res,$e,'CF03InvoiceAgendaActorMismatch');
                $actors[]=$res['agenda_id'];
            }
            sort($actors);
            $eventIds=array_column($invoiceEvents,'id'); sort($eventIds);
            self::need($actors===$eventIds,'CF03InvoiceAgendaActorMismatch');
        }
        foreach (self::COUNTS as $table) {
            $delta=0;
            if ($positive && $table==='subscription') { $delta=1; }
            if ($positive && $e['agenda']==='on'
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
