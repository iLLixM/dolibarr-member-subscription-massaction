<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
// CF05 synthetic original Booking + Invoice + file-session + after-commit
// orchestration. No Dolibarr database, HTTP, PDF generation or SMTP.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
ob_start();
require __DIR__.'/invoice.php';
require_once DOL_DOCUMENT_ROOT.'/core/modules/syslog/mod_syslog_file.php';
$cf05Base = $passed;

class Cf05FinanceDb extends InvoiceDatabaseDouble
{
	public $profileRaw;
	public function encrypt($s) { return "'".addslashes($s)."'"; }
	public function decrypt($s) { return $s; }
	public function query($sql)
	{
		if (strpos($sql, ' FROM test_const WHERE ') !== false) {
			$this->queries[] = $sql;
			return (object) array('rows'=>array((object)array('value'=>$this->profileRaw)));
		}
		return parent::query($sql);
	}
}
/** Faithful bounded synthetic guard: never calls P_S or invents event identity. */
class Cf05FinanceGuard extends MsmCoreGuardedTransaction
{
	private $fixtureDb;
	private $attempted = false;
	private $ended = false;
	public function __construct($db) { parent::__construct($db); $this->fixtureDb=$db; }
	public function beforeBegin() {
		if ($this->attempted || $this->fixtureDb->transaction_opened !== 0) {
			return array('state'=>MsmDatabaseState::UNKNOWN,'fresh'=>false,'owned'=>false);
		}
		return array('state'=>MsmDatabaseState::IDLE,'fresh'=>true,'owned'=>false);
	}
	public function begin() {
		$this->attempted=true;
		return $this->fixtureDb->begin();
	}
	public function beginAttempted() { return $this->attempted; }
	public function afterBegin() { return $this->active(); }
	public function active() {
		$valid = !$this->ended && $this->fixtureDb->transaction_opened===1 && $this->fixtureDb->active;
		return array('state'=>$valid?MsmDatabaseState::ACTIVE:MsmDatabaseState::UNKNOWN,
			'fresh'=>$valid,'owned'=>$valid,'isolation'=>'REPEATABLE READ');
	}
	public function requireSupportedIsolation(array $state) {
		if (($state['state'] ?? null)!==MsmDatabaseState::ACTIVE || !$state['fresh']) {
			throw new RuntimeException('MSMTransactionUnknown');
		}
	}
	public function rollbackContext() { return $this->active(); }
	public function beforeCompletion() {
		$this->requireSupportedIsolation($this->active());
	}
	public function completed($action, $ack) {
		$valid=$this->attempted && ($ack===true||$ack===1) && $this->fixtureDb->transaction_opened===0
			&& in_array($action,array('COMMIT','ROLLBACK'),true);
		$this->ended=true;
		return array('state'=>$valid?MsmDatabaseState::IDLE:MsmDatabaseState::UNKNOWN,
			'fresh'=>$valid,'owned'=>false);
	}
	public function nonterminalAcknowledged() {
		return $this->ended && $this->fixtureDb->transaction_opened===0;
	}
}
class Cf05FinanceInvoice extends MsmInvoiceService
{
	protected function invoice() { return new InvoiceObjectDouble($this->db); }
	protected function customer() { return new InvoiceCustomerDouble($this->db); }
}
class Cf05FinanceBooking extends BookingServiceDouble
{
	private $guard;
	protected function coreTransaction() {
		if ($this->guard === null) { $this->guard=new Cf05FinanceGuard($this->profileDb); }
		return $this->guard;
	}
	protected function invoicing() {
		$guard=new ReflectionProperty(MsmBookingService::class,'activeTransaction');
		$guard->setAccessible(true);
		$checkpoint=new ReflectionProperty(MsmBookingService::class,'coreCheckpoint');
		$checkpoint->setAccessible(true);
		return new Cf05FinanceInvoice($this->profileDb,$this->databaseState,
			$guard->getValue($this),$checkpoint->getValue($this));
	}
}
class Cf05FinancePdfSpy
{
	public $calls=0;
	public function generate($identity,$user) {
		$this->calls++;
		return array('status'=>'verified','message'=>'MSMPdfVerified');
	}
}
class Cf05FinanceMailSpy
{
	public $calls=0;
	public function send($member,$row,$user,$selected,$invoice=null) {
		$this->calls++;
		return $selected ? 'transport' : 'not_selected';
	}
}
$cf05Tmp=sys_get_temp_dir().'/cf05-finance-'.bin2hex(random_bytes(12));
if (!mkdir($cf05Tmp,0700)) { throw new RuntimeException('Cannot create local synthetic session dir'); }
$cf05OldSessionPath=session_save_path();
$cf05OldCookies=ini_get('session.use_cookies');
$cf05OldLimiter=session_cache_limiter();
$cf05OriginalConf=clone $conf;
if (session_status()===PHP_SESSION_ACTIVE) { session_write_close(); }
ini_set('session.use_cookies','0');
session_cache_limiter('');
session_save_path($cf05Tmp);
$conf->entity=1;
$conf->global=new stdClass();
$conf->global->SYSLOG_FILE=$cf05Tmp.'/qualified.log';
$conf->modules=array('member'=>1,'invoice'=>1,'societe'=>1,'syslog'=>1);
$conf->loghandlers=array(new mod_syslog_file());
try {
	foreach (array(
		array('name'=>'existing','autocreate'=>false,'mode'=>'','change'=>false,'expected'=>'success'),
		array('name'=>'new','autocreate'=>true,'mode'=>'','change'=>false,'expected'=>'success'),
		array('name'=>'precheck','autocreate'=>true,'mode'=>'','change'=>true,'expected'=>'failed'),
		array('name'=>'customer_fail','autocreate'=>true,'mode'=>'customer','change'=>false,'expected'=>'unknown'),
		array('name'=>'invoice_create_fail','autocreate'=>true,'mode'=>'create','change'=>false,'expected'=>'unknown'),
		array('name'=>'invoice_validate_fail','autocreate'=>true,'mode'=>'validate','change'=>false,'expected'=>'unknown'),
		array('name'=>'invoice_depth','autocreate'=>true,'mode'=>'depth','change'=>false,'expected'=>'unknown')
	) as $case) {
		$db=new Cf05FinanceDb(); $db->family='mysql'; $db->profileRaw=json_encode(array(
			'version'=>1,'family'=>'mysql','profile'=>'CoreGuarded','nonce'=>str_repeat('b',32)));
		$db->customerId=$case['autocreate']?0:4;
		$db->mode=$case['mode'];
		$flow=new Cf05FinanceBooking($db);
		$flow->object=new BookingMemberDouble($db);
		$flow->object->id=1; $flow->object->entity=1; $flow->object->typeid=1;
		$flow->object->socid=$db->customerId; $flow->object->morphy='phy';
		$flow->object->firstname='Synthetic'; $flow->object->lastname='Member';
		$flow->reader=new BookingPreviewDouble();
		$row=array('start'=>stamp('2026-10-10'),'end'=>stamp('2027-10-09'),
			'amount'=>24.5,'description'=>'Synthetic fee','fingerprint'=>'cf05-confirmed',
			'errors'=>array(),'overlaps'=>array(),'autocreate'=>$case['autocreate'],
			'invoice'=>array('customer_id'=>$db->customerId,'create_customer'=>$case['autocreate'],
				'entity'=>$case['change']?2:1,'date'=>'2026-11-10','term'=>1,'payment_mode'=>0,
				'bank'=>0,'currency'=>'EUR','pdf_model'=>'crabe','product'=>0,
				'vat'=>0,'quantity'=>1,'price_base'=>'TTC','type'=>1));
		$flow->reader->row=$row;
		$user=new PreviewUser();
		$options=array('invoice'=>true,'autocreate'=>$case['autocreate'],
			'sendmail'=>true,'description'=>'Synthetic fee');
		$binding=FixtureTransactionProfile::context($db,$user);
		$key=bin2hex(random_bytes(16));
		$sessionid='cf05'.bin2hex(random_bytes(8));
		session_id($sessionid);
		verify(session_start(),'CF05 local session start '.$case['name']);
		$state=array('phase'=>'confirm','revision'=>5,'owner'=>$user->id,'entity'=>1,
			'ids'=>array(1),'profile'=>$binding,'options'=>$options,'rows'=>array(1=>$row),
			'results'=>array(1=>array('status'=>'unknown','stop'=>false)),'overlap'=>array());
		$_SESSION=array('msm_previews'=>array($key=>$state));
		$checkpoint=new MsmFileBookingCheckpoint($key);
		verify(session_write_close(),'CF05 persisted original confirm '.$case['name']);
		verify(session_start(),'CF05 consume session '.$case['name']);
		$state['phase']='processing'; $state['revision']=6;
		$_SESSION['msm_previews'][$key]=$state;
		verify(session_write_close(),'CF05 persist consumption '.$case['name']);
		$pdf=new Cf05FinancePdfSpy(); $mail=new Cf05FinanceMailSpy();
		$done=msmProcessBooking($state,$flow,$mail,$user,$checkpoint,$pdf);
		$r=$done['results'][1];
		verify($r['status']===$case['expected'],'CF05 expected booking status '.$case['name']);
		verify(count(array_keys($db->events,'subscription',true))===1,
			'CF05 one original contribution and no replay '.$case['name']);
		if ($case['expected']==='success') {
			verify(!$r['stop'] && ($r['completion'] ?? '')==='nonterminal_acknowledged'
				&& ($r['invoice']['id'] ?? null)===42 && ($r['invoice']['customer_created'] ?? null)===$case['autocreate'],
				'CF05 one guarded financial flow with confirmed nonterminal completion '.$case['name']);
			verify($pdf->calls===1 && $mail->calls===1
				&& ($r['invoice']['pdf']['status'] ?? '')==='verified' && $r['mail']==='transport',
				'CF05 persisted PDF/mail effects only after accepted completion '.$case['name']);
			verify($db->transaction_opened===0 && !in_array('rollback',$db->events,true),
				'CF05 no second transaction owner '.$case['name']);
		} elseif ($case['expected']==='failed') {
			verify(($r['rollback'] ?? null)==='confirmed' && $db->saved===null
				&& !in_array('invoice-create',$db->calls,true) && $pdf->calls===0 && $mail->calls===0,
				'CF05 typed pre-Core finance business refusal permits only owned rollback');
		} else {
			verify($r['stop'] && ($r['rollback'] ?? null)==='unconfirmed'
				&& !in_array('rollback',$db->events,true) && $pdf->calls===0 && $mail->calls===0,
				'CF05 unqualified customer/invoice/error context UNKNOWN/STOP '.$case['name']);
		}
		verify(session_start(),'CF05 read committed result '.$case['name']);
		verify(($_SESSION['msm_previews'][$key]['phase'] ?? null)==='done'
			&& ($_SESSION['msm_previews'][$key]['results'][1]['status'] ?? null)===$case['expected'],
			'CF05 durable terminal result no replay '.$case['name']);
		session_destroy(); session_write_close();
		@unlink($cf05Tmp.'/sess_'.$sessionid);
	}
} finally {
	if (session_status()===PHP_SESSION_ACTIVE) { session_abort(); }
	foreach (glob($cf05Tmp.'/sess_*') ?: array() as $file) { @unlink($file); }
	@rmdir($cf05Tmp);
	session_save_path($cf05OldSessionPath);
	ini_set('session.use_cookies',(string)$cf05OldCookies);
	session_cache_limiter($cf05OldLimiter);
	$conf=$cf05OriginalConf;
}
echo ($passed-$cf05Base)." CF05 guarded CoreGuarded financial orchestration assertions PASS (synthetic only; no real DB/HTTP/PDF/SMTP).\n";
ob_end_flush();
