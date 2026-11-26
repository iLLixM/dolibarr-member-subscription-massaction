<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
// CF05 synthetic original Booking + Invoice + file-session + after-commit
// orchestration. Local invalid PDF temporary file, no Dolibarr DB, HTTP or SMTP.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
ob_start();
require __DIR__.'/coreguarded.php'; // Reuse the native-boundary DB fixture; invoice tests reset the assertion counter.
require __DIR__.'/invoice.php';
require_once __DIR__.'/../../class/invoicepdf.class.php';
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
/** Fixture supplies the synthetic persisted admin row, but INHERITS the
 * actual production CoreGuarded finance admission (now enabled by Owner).
 * This test must no longer override the real feature gate.
 */
class Cf05QualifiedFinanceProfile extends FixtureTransactionProfile {}
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
	protected function financeAdmissionProfile() {
		global $conf;
		return new Cf05QualifiedFinanceProfile($this->db,$this->databaseState,(int)$conf->entity);
	}
	protected function invoice() { return new InvoiceObjectDouble($this->db); }
	protected function customer() { return new InvoiceCustomerDouble($this->db); }
}
class Cf05FinanceBooking extends BookingServiceDouble
{
	private $guard;
	protected function profiles() {
		global $conf;
		return new Cf05QualifiedFinanceProfile($this->profileDb,$this->databaseState,(int)$conf->entity);
	}
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
/** F38-10: mutate the finance row only after Booking confirmed and posted the
 * subscription. Invoice admission must reject before any invoice Core API.
 */
class Cf05TamperedFinanceInvoice extends Cf05FinanceInvoice
{
	public function create($member,$subscription,array $row,$actor,array &$diagnostic)
	{
		$row['amount'] = $row['amount'] + 1;
		return parent::create($member,$subscription,$row,$actor,$diagnostic);
	}
}
class Cf05TamperedFinanceBooking extends Cf05FinanceBooking
{
	protected function invoicing()
	{
		$guard = new ReflectionProperty(MsmBookingService::class,'activeTransaction');
		$guard->setAccessible(true);
		$checkpoint = new ReflectionProperty(MsmBookingService::class,'coreCheckpoint');
		$checkpoint->setAccessible(true);
		return new Cf05TamperedFinanceInvoice($this->profileDb,$this->databaseState,
			$guard->getValue($this),$checkpoint->getValue($this));
	}
}
/** Unmodified, real production profile admission for a confirmed file-session.
 * It must deny finance before Core BEGIN even when the stored confirmation
 * is valid and the PreviewUser has the usual rights.
 */
class Cf05ProductionGateBooking extends Cf05FinanceBooking
{
	protected function profiles() {
		global $conf;
		return new MsmTransactionProfile($this->profileDb,$this->databaseState,(int)$conf->entity);
	}
}
/** Simulate a file-session persistence disruption after the external
 * effect already ran. This is synthetic local file-session I/O only.
 */
function cf05LosePersistedCheckpoint($key)
{
	if (!session_start()) { throw new RuntimeException('Cannot open test-only file-session'); }
	$_SESSION['msm_previews'][$key]['revision']=99;
	if (!session_write_close()) { throw new RuntimeException('Cannot close test-only file-session'); }
}
class Cf05FinancePdfSpy
{
	public $calls=0; public $status='verified'; public $throws=false; public $loseCheckpointKey=null;
	public function generate($identity,$user) {
		$this->calls++;
		if ($this->loseCheckpointKey !== null) { cf05LosePersistedCheckpoint($this->loseCheckpointKey); }
		if ($this->throws) { throw new RuntimeException('Synthetic PDF interruption after attempted effect'); }
		return array('status'=>$this->status,
			'message'=>$this->status==='verified'?'MSMPdfVerified':'MSMPdfUnknown',
			'name'=>'SYNTHETIC.pdf','size'=>32,'mtime'=>1791590400,
			'sha256'=>str_repeat('a',64));
	}
}
/** Test-only qualified subtype exercises production sendWithEvidence() including
 * the real one-shot capability and profile decision. No SMTP/Core hook or PDF
 * leaves the process: only sendInternal() is replaced with a synthetic return.
 */
class Cf05InvalidWrittenPdf extends MsmInvoicePdfService
{
	public $root; public $calls=0; private $stored;
	public function __construct($db,$root) {
		parent::__construct($db); $this->root=$root;
		$this->stored=(object) array('ref'=>'INV-SYNTHETIC',
			'last_main_doc'=>'','status'=>1,'entity'=>1);
	}
	protected function currentUser($user) { return $user; }
	protected function entityRoot($entity) { return $this->root; }
	protected function load(array $identity,$user) { return $this->stored; }
	protected function generateCore($invoice,$model,$langs) {
		$this->calls++;
		$dir=$this->root.'/INV-SYNTHETIC';
		if (!is_dir($dir)) { mkdir($dir,0700); }
		file_put_contents($dir.'/INV-SYNTHETIC.pdf','INVALID AFTER GENERATOR START');
		$invoice->last_main_doc='facture/INV-SYNTHETIC/INV-SYNTHETIC.pdf';
		return 1;
	}
}
class Cf05FinanceMailSpy extends MsmSubscriptionMail
{
	public $calls=0; public $status='transport'; public $throws=false; public $loseCheckpointKey=null;
	public $transportFailures=array(); public $members=array(); public $opaqueFailure=false;
	protected function evidenceProfile() {
		global $conf;
		return new Cf05QualifiedFinanceProfile($this->db,new SyntheticDatabaseState($this->db),(int)$conf->entity);
	}
	protected function permitted($user) { return true; }
	protected function invoicePermitted($user) { return true; }
	protected function sendInternal($member,array $row,$user,$selected,$invoice,&$afterEffect,&$isolatedSmtpFailure=null) {
		$this->calls++;
		$this->members[] = $member->id;
		$afterEffect=true;
		if ($this->loseCheckpointKey !== null) { cf05LosePersistedCheckpoint($this->loseCheckpointKey); }
		if (in_array($member->id, $this->transportFailures, true)) {
			$isolatedSmtpFailure = true;
			return 'failed';
		}
		if ($this->throws) { throw new RuntimeException('Synthetic uncertain SMTP after attempted effect'); }
		if ($this->opaqueFailure) { return 'failed'; }
		return $selected?$this->status:'not_selected';
	}
}
class Cf05ProductionEvidenceMailSpy extends Cf05FinanceMailSpy
{
	protected function evidenceProfile() {
		global $conf;
		return new MsmTransactionProfile($this->db,new SyntheticDatabaseState($this->db),(int)$conf->entity);
	}
}
/** Exercise the actual batch, one-shot and persistence boundaries for four members. */
class Adr08aBookingSpy
{
	public $members = array();
	public $contextFails = false;
	private $key;
	private $uncertain;
	public function __construct($key, array $uncertain) { $this->key = $key; $this->uncertain = $uncertain; }
	public function requireIsolatedMailContinuation()
	{
		if ($this->contextFails) { throw new RuntimeException('MSMTransactionUnknown'); }
	}
	public function book($id, $row, $options, $user, $overlap, $checkpoint)
	{
		$checkpoint->requireProcessing($id, $row['_profile'], $user, $row, $options);
		if ($this->members) {
			verify(session_start(), 'ADR-08A reopen before next booking');
			foreach ($this->members as $prior) {
				$stored = $_SESSION['msm_previews'][$this->key]['results'][$prior];
				$mail = in_array($prior, $this->uncertain, true) ? 'unknown' : 'transport';
				verify($stored['status'] === 'success' && $stored['mail'] === $mail
					&& $stored['attachment_status'] === (!$options['invoice'] ? 'not_required' :
						($mail === 'unknown' ? 'unknown' : 'verified_for_transport')),
					'ADR-08A each truthful outcome was read back before next booking');
			}
			session_abort();
		}
		$this->members[] = $id;
		$member = (object) array('id'=>$id,'entity'=>1,'typeid'=>1,'email'=>'synthetic@example.invalid');
		$checkpoint->bindMailRecipient($id, $member, $row);
		$result = array('status'=>'success','completion'=>'nonterminal_acknowledged','stop'=>false,
			'mail'=>'not_selected','message'=>'MSMBooked','id'=>$id,'amount'=>120,'_member'=>$member);
		if ($options['invoice']) {
			$result['invoice'] = array('id'=>$id+10,'entity'=>1,'member_id'=>$id,'pdf'=>'pending');
		}
		return $result;
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
	session_id('msmcontext'.bin2hex(random_bytes(8)));
	// Call the production service and production guard logic directly, with
	// only the native handle and SQL responses supplied by the existing fixture.
	foreach (array('valid', 'chain', 'release', 'lost_ack', 'depth', 'replacement',
		'thread', 'read_failure', 'changed_autocommit', 'changed_completion', 'runtime') as $case) {
		$db = new CgFixtureDB();
		$db->mode = $case === 'chain' ? 'CHAIN' : ($case === 'release' ? 'RELEASE' : 'NO_CHAIN');
		$guard = new CgFixtureAdapter($db);
		$service = new class($db, $guard) extends MsmBookingService {
			private $fixtureGuard;
			public function __construct($db, $guard) { parent::__construct($db); $this->fixtureGuard = $guard; }
			protected function coreTransaction() { return $this->fixtureGuard; }
		};
		verify($guard->beforeBegin()['state'] === MsmDatabaseState::IDLE && $guard->begin(), 'F49-03 synthetic Core entry '.$case);
		$guard->beforeCompletion();
		$ack = $db->commit();
		$completed = $guard->completed('COMMIT', $case === 'lost_ack' ? false : $ack);
		verify($completed['state'] === ($case === 'lost_ack' ? MsmDatabaseState::UNKNOWN : MsmDatabaseState::IDLE),
			'F49-03 fixture completion '.$case);
		$originalLoggers = $conf->loghandlers;
		switch ($case) {
			case 'depth': $db->transaction_opened = 1; break;
			case 'replacement': $db->handle = clone $db->handle; break;
			case 'thread': $db->handle->id = '1452'; break;
			case 'read_failure': $db->readFail = true; break;
			case 'changed_autocommit': $db->autocommit = '0'; break;
			case 'changed_completion': $db->mode = 'CHAIN'; break;
			case 'runtime': $conf->loghandlers = array(); break;
		}
		$offset = count($db->commands);
		try {
			if ($case === 'valid') { $service->requireIsolatedMailContinuation(); }
			else {
				$reason = array('read_failure'=>'MSMDatabaseObservationUnavailable',
					'changed_autocommit'=>'MSMTransactionUnsupported', 'runtime'=>'MSMCoreGuardedLoggerUnsupported');
				fails(static function () use ($service) { $service->requireIsolatedMailContinuation(); },
					$reason[$case] ?? 'MSMTransactionUnknown');
			}
		} finally {
			$conf->loghandlers = $originalLoggers;
		}
		$observes = in_array($case, array('valid','read_failure','changed_autocommit','changed_completion'), true);
		verify(array_slice($db->commands, $offset) === ($observes
			? array('SELECT CONNECTION_ID() AS cid, @@SESSION.autocommit AS ac, @@SESSION.completion_type AS completion') : array()),
			'F49-03 context check must perform no write, transaction action or unexpected SQL '.$case);
	}

	foreach (array('one_with_invoice', 'multiple_contribution_only', 'context_failure', 'checkpoint_failure',
		'unqualified', 'opaque_transport_error', 'contradictory') as $scenario) {
		$key = str_repeat('b', 32);
		$db = new Cf05FinanceDb();
		$profile = new Cf05QualifiedFinanceProfile($db, new SyntheticDatabaseState($db), 1);
		$db->family = 'mysql';
		$db->profileRaw = json_encode(array('version'=>1,'family'=>'mysql','profile'=>'CoreGuarded',
			'nonce'=>str_repeat('e',32)), JSON_THROW_ON_ERROR);
		$current = $profile->current();
		$user = new PreviewUser();
		$binding = $profile->binding($current, $user, 5);
		$options = array('invoice'=>$scenario !== 'multiple_contribution_only','autocreate'=>false,
			'sendmail'=>true,'description'=>'Synthetic');
		$ids = array(1,2,3,4);
		$row = array('fingerprint'=>'original','errors'=>array(),'overlaps'=>array());
		$state = array('phase'=>'confirm','revision'=>5,'owner'=>$user->id,'entity'=>1,'ids'=>$ids,
			'profile'=>$binding,'options'=>$options,'rows'=>array_fill_keys($ids, $row),
			'results'=>array_fill_keys($ids, array('status'=>'unknown')));
		session_id('msmadr08a'.bin2hex(random_bytes(8)));
		verify(session_start(), 'ADR-08A start original confirmation');
		$_SESSION = array('msm_previews'=>array($key=>$state));
		$checkpoint = new MsmFileBookingCheckpoint($key);
		$state = msmConsumeConfirmation($state, array('action'=>'book','revision'=>'5'));
		$_SESSION['msm_previews'][$key] = $state;
		verify(session_write_close(), 'ADR-08A persist consumed confirmation');
		$uncertain = $scenario === 'multiple_contribution_only' ? array(1,3) : array(1);
		$service = new Adr08aBookingSpy($key, $uncertain);
		$service->contextFails = $scenario === 'context_failure';
		$mail = new Cf05FinanceMailSpy($db);
		$mail->transportFailures = $uncertain;
		if ($scenario === 'checkpoint_failure') { $mail->loseCheckpointKey = $key; }
		if ($scenario === 'unqualified') { $mail->transportFailures = array(); $mail->throws = true; }
		if ($scenario === 'opaque_transport_error') { $mail->transportFailures = array(); $mail->opaqueFailure = true; }
		if ($scenario === 'contradictory') {
			$mail = new class($db) extends Cf05FinanceMailSpy {
				public function sendWithEvidence($member, array $row, $user, $selected, array $invoice = null, $checkpoint = null, $id = null)
				{
					parent::sendWithEvidence($member, $row, $user, $selected, $invoice, $checkpoint, $id);
					return array('mail'=>'transport','attachment'=>'verified_for_transport','continuation'=>'isolated_smtp_failure');
				}
			};
		}
		$pdf = new Cf05FinancePdfSpy();
		$done = msmProcessBooking($state,$service,$mail,$user,$checkpoint,$pdf);
		$continues = in_array($scenario,array('one_with_invoice','multiple_contribution_only'),true);
		$expected = $continues ? $ids : array(1);
		verify($service->members === $expected && $mail->members === $expected, 'ADR-08A exactly one attempt per admitted member '.$scenario);
		verify(empty($done['batch_stop']) === $continues, 'ADR-08A batch decision '.$scenario);
		verify($done['results'][1]['status'] === 'success' && $done['results'][1]['mail'] === 'unknown'
			&& $done['results'][1]['attachment_status'] === ($options['invoice'] ? 'unknown' : 'not_required'),
			'ADR-08A DB and mail verdict remain independent '.$scenario);
		if ($options['invoice']) {
			verify($done['results'][1]['invoice']['pdf']['status'] === 'verified', 'ADR-08A PDF remains verified '.$scenario);
		}
		if (!$continues) {
			verify($done['results'][2]['status'] === 'not_processed', 'ADR-08A no subsequent write '.$scenario);
		}
		verify(!empty($done['checkpoint_failed']) === ($scenario === 'checkpoint_failure'), 'ADR-08A E15 boundary '.$scenario);
		fails(static function () use ($done) {
			msmConsumeConfirmation($done, array('action'=>'book','revision'=>(string) $done['revision']));
		}, 'MSMInvalidForm');
	}

	foreach (array(
		array('name'=>'existing','autocreate'=>false,'mode'=>'','change'=>false,'expected'=>'success'),
		array('name'=>'new','autocreate'=>true,'mode'=>'','change'=>false,'expected'=>'success'),
		array('name'=>'contribution_mail_success','autocreate'=>false,'mode'=>'','change'=>false,
			'expected'=>'success','no_invoice'=>true),
		array('name'=>'core_agenda_copy_parity','autocreate'=>false,'mode'=>'','change'=>false,
			'expected'=>'success','agenda'=>true,'agenda_copy'=>'yes','logevents'=>'1'),
		array('name'=>'precheck','autocreate'=>true,'mode'=>'','change'=>true,'expected'=>'failed'),
		array('name'=>'finance_tampered_row','autocreate'=>true,'mode'=>'','change'=>false,'expected'=>'unknown','tampered_finance'=>true),
		array('name'=>'customer_fail','autocreate'=>true,'mode'=>'customer','change'=>false,'expected'=>'unknown'),
		array('name'=>'invoice_create_fail','autocreate'=>true,'mode'=>'create','change'=>false,'expected'=>'unknown'),
		array('name'=>'invoice_validate_fail','autocreate'=>true,'mode'=>'validate','change'=>false,'expected'=>'unknown'),
		array('name'=>'invoice_depth','autocreate'=>true,'mode'=>'depth','change'=>false,'expected'=>'unknown'),
		array('name'=>'pdf_unknown','autocreate'=>true,'mode'=>'','change'=>false,'expected'=>'success','pdf_status'=>'unknown'),
		array('name'=>'mail_unknown','autocreate'=>true,'mode'=>'','change'=>false,'expected'=>'success','mail_status'=>'unknown'),
		array('name'=>'pdf_throws','autocreate'=>true,'mode'=>'','change'=>false,'expected'=>'success','pdf_throw'=>true),
		array('name'=>'mail_throws','autocreate'=>true,'mode'=>'','change'=>false,'expected'=>'success','mail_throw'=>true),
		array('name'=>'pdf_invalid_written','autocreate'=>true,'mode'=>'','change'=>false,'expected'=>'success','pdf_invalid_written'=>true),
		array('name'=>'hook_negative_after_effect','autocreate'=>true,'mode'=>'','change'=>false,'expected'=>'success','mail_status'=>'failed'),
		array('name'=>'transport_negative_after_effect','autocreate'=>false,'mode'=>'','change'=>false,'expected'=>'success','mail_status'=>'failed'),
		array('name'=>'pdf_checkpoint_lost','autocreate'=>true,'mode'=>'','change'=>false,'expected'=>'success','pdf_checkpoint_lost'=>true),
		array('name'=>'mail_checkpoint_lost','autocreate'=>true,'mode'=>'','change'=>false,'expected'=>'success','mail_checkpoint_lost'=>true),
		array('name'=>'contribution_mail_throws','autocreate'=>false,'mode'=>'','change'=>false,
			'expected'=>'success','no_invoice'=>true,'mail_throw'=>true)
	) as $case) {
		// Enabling a standard Agenda/file-copy or Logevents configuration
		// must no longer invoke the retired source/trigger allowlist.
		$conf->modules['agenda']=!empty($case['agenda']) ? 1 : 0;
		$conf->global->MAIN_COPY_FILE_IN_EVENT_AUTO=$case['agenda_copy'] ?? '';
		$conf->global->MAIN_LOGEVENTS_BILL_VALIDATE=$case['logevents'] ?? '';
		$db=new Cf05FinanceDb(); $db->family='mysql'; $db->profileRaw=json_encode(array(
			'version'=>1,'family'=>'mysql','profile'=>'CoreGuarded','nonce'=>str_repeat('b',32)));
		$db->customerId=$case['autocreate']?0:4;
		$db->mode=$case['mode'];
		$flow=!empty($case['tampered_finance']) ? new Cf05TamperedFinanceBooking($db) : new Cf05FinanceBooking($db);
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
		// msmEvaluatePreview() appends this UI flag after calculating the
		// fingerprint. The in-transaction preview service returns no such key.
		$row['manualend']=$case['name']==='existing';
		$user=new PreviewUser();
		$options=array('invoice'=>empty($case['no_invoice']),'autocreate'=>$case['autocreate'],
			'sendmail'=>true,'description'=>'Synthetic fee');
		$qualifiedProfile=new Cf05QualifiedFinanceProfile($db,new SyntheticDatabaseState($db),1);
		$binding=$qualifiedProfile->binding($qualifiedProfile->current(),$user,0);
		$key=bin2hex(random_bytes(16));
		$sessionid='cf05'.bin2hex(random_bytes(8));
		session_id($sessionid);
		verify(session_start(),'CF05 local session start '.$case['name']);
		$expectStop=isset($case['pdf_status'])||isset($case['mail_status'])
			|| !empty($case['pdf_throw']) || !empty($case['mail_throw']) || !empty($case['pdf_invalid_written'])
			|| !empty($case['pdf_checkpoint_lost']) || !empty($case['mail_checkpoint_lost']);
		$ids=$expectStop?array(1,2):array(1);
		$rows=$expectStop?array(1=>$row,2=>$row):array(1=>$row);
		$state=array('phase'=>'confirm','revision'=>5,'owner'=>$user->id,'entity'=>1,
			'ids'=>$ids,'profile'=>$binding,'options'=>$options,'rows'=>$rows,
			'results'=>array(1=>array('status'=>'unknown','stop'=>false),
				2=>array('status'=>'unknown','stop'=>false)),'overlap'=>array());
		$_SESSION=array('msm_previews'=>array($key=>$state));
		$checkpoint=new MsmFileBookingCheckpoint($key);
		verify(session_write_close(),'CF05 persisted original confirm '.$case['name']);
		verify(session_start(),'CF05 consume session '.$case['name']);
		$state['phase']='processing'; $state['revision']=6;
		$_SESSION['msm_previews'][$key]=$state;
		verify(session_write_close(),'CF05 persist consumption '.$case['name']);
		// Owner 2026-11-22 explicitly reactivates finance in the actual
		// production profile, without bypassing the original file-session.
		$productionProfile=new MsmTransactionProfile($db,new SyntheticDatabaseState($db),1);
		$liveCurrent=$productionProfile->current();
		verify($liveCurrent['features']['invoice'] && $liveCurrent['features']['autocreate']
			&& $liveCurrent['features']['sendmail'],
			'Owner production CoreGuarded profile now enables full finance '.$case['name']);
		$pdf=new Cf05FinancePdfSpy(); $mail=new Cf05FinanceMailSpy($db);
		if (!empty($case['pdf_invalid_written'])) {
			$pdfRoot=$cf05Tmp.'/pdf-'.$case['name'];
			if (!mkdir($pdfRoot,0700)) { throw new RuntimeException('Cannot create test-only PDF root'); }
			$pdf=new Cf05InvalidWrittenPdf($db,$pdfRoot);
		}
		$realAdmissionMail=new Cf05ProductionEvidenceMailSpy($db);
		$forged = $realAdmissionMail->sendWithEvidence($flow->object,$row,$user,true,
			array('id'=>42,'member_id'=>1,'entity'=>1,'pdf'=>array('status'=>'verified')),
			$checkpoint,1);
		verify($forged['mail']==='denied' && $realAdmissionMail->calls===0,
			'F38-03 production CoreGuarded direct evidence denies without original post-COMMIT mail gate '.$case['name']);
		$missingCapability=$mail->sendWithEvidence($flow->object,$row,$user,true,
			array('id'=>42,'member_id'=>1,'entity'=>1,'pdf'=>array('status'=>'verified')));
		verify($missingCapability['mail']==='denied' && $mail->calls===0,
			'F38-03 qualified positive adapter cannot self-authorize without original checkpoint '.$case['name']);
		if (isset($case['pdf_status'])) { $pdf->status=$case['pdf_status']; }
		if (isset($case['mail_status'])) { $mail->status=$case['mail_status']; }
		if (!empty($case['pdf_throw'])) { $pdf->throws=true; }
		if (!empty($case['mail_throw'])) { $mail->throws=true; }
		if (!empty($case['pdf_checkpoint_lost'])) { $pdf->loseCheckpointKey=$key; }
		if (!empty($case['mail_checkpoint_lost'])) { $mail->loseCheckpointKey=$key; }
		$done=msmProcessBooking($state,$flow,$mail,$user,$checkpoint,$pdf);
		$r=$done['results'][1];
		verify($r['status']===$case['expected'],'CF05 expected booking status '.$case['name']);
		verify(count(array_keys($db->events,'subscription',true))===1,
			'CF05 one original contribution and no replay '.$case['name']);
		if ($case['expected']==='success') {
			verify(!$r['stop'] && ($r['completion'] ?? '')==='nonterminal_acknowledged'
				&& (!empty($case['no_invoice']) ? !isset($r['invoice']) :
					(($r['invoice']['id'] ?? null)===42 && ($r['invoice']['customer_created'] ?? null)===$case['autocreate'])),
				'CF05 guarded contribution/financial flow with confirmed nonterminal completion '.$case['name']);
			if ($expectStop) {
				verify($done['batch_stop'] && $done['results'][2]['status']==='not_processed'
					&& $pdf->calls===(!empty($case['no_invoice'])?0:1) && $db->transaction_opened===0
					&& count(array_keys($db->events,'subscription',true))===1,
					'CF05 Phase4 ambiguous postcommit effect stops next member without rollback '.$case['name']);
				if (isset($case['pdf_status']) || !empty($case['pdf_throw']) || !empty($case['pdf_invalid_written'])
					|| !empty($case['pdf_checkpoint_lost'])) {
					verify($r['invoice']['pdf']['status']==='unknown' && $r['mail']==='not_processed'
						&& $r['attachment_status']==='not_processed' && $mail->calls===0,
						'CF05 Phase4 PDF uncertainty prevents SMTP and preserves file status');
				} elseif (!empty($case['no_invoice'])) {
					verify(!isset($r['invoice']) && $r['mail']==='unknown'
						&& $r['attachment_status']==='not_required' && $mail->calls===1,
						'F38-06 contribution-only exception preserves UNKNOWN/STOP but no fictional invoice attachment');
				} else {
					verify($r['invoice']['pdf']['status']==='verified' && $r['mail']==='unknown'
						&& $r['attachment_status']==='unknown' && $mail->calls===1,
						'CF05 Phase4 uncertain SMTP preserves verified PDF and unknown attachment separately');
				}
			} else {
				if (!empty($case['no_invoice'])) {
					verify(!isset($r['invoice']) && $pdf->calls===0 && $mail->calls===1
						&& $r['mail']==='transport' && $r['attachment_status']==='not_required',
						'Owner Core-parity contribution-only mail transports without a fabricated PDF attachment');
				} else {
					verify($pdf->calls===1 && $mail->calls===1
						&& ($r['invoice']['pdf']['status'] ?? '')==='verified'
						&& $r['mail']==='transport' && $r['attachment_status']==='verified_for_transport',
						'CF05 Phase4 completion, verified PDF, attachment and mail evidence separate '.$case['name']);
				}
			}
			verify($db->transaction_opened===0 && !in_array('rollback',$db->events,true),
				'CF05 no second transaction owner '.$case['name']);
		} elseif ($case['expected']==='failed') {
			verify(($r['rollback'] ?? null)==='confirmed' && $db->saved===null
				&& !in_array('invoice-create',$db->calls,true) && $pdf->calls===0 && $mail->calls===0,
				'CF05 typed pre-Core finance business refusal permits only owned rollback');
		} else {
			if (!empty($case['tampered_finance'])) {
				verify(!$db->calls && ($r['diagnostic']['stage'] ?? null)==='finance_precheck',
					'F38-10 finance row altered after Booking original subscription cannot enter Invoice Core');
			}
			verify($r['stop'] && ($r['rollback'] ?? null)==='unconfirmed'
				&& !in_array('rollback',$db->events,true) && $pdf->calls===0 && $mail->calls===0,
				'CF05 unqualified customer/invoice/error context UNKNOWN/STOP '.$case['name']);
		}
		verify(session_start(),'CF05 read committed result '.$case['name']);
		$lostCheckpoint = !empty($case['pdf_checkpoint_lost']) || !empty($case['mail_checkpoint_lost']);
		if ($lostCheckpoint) {
			// A failed write cannot durably persist a new STOP flag. What
			// prevents replay is the originally consumed processing session.
			verify(($done['checkpoint_failed'] ?? null)===true
				&& ($done['phase'] ?? '')==='processing'
				&& ($_SESSION['msm_previews'][$key]['phase'] ?? '')==='processing'
				&& ($_SESSION['msm_previews'][$key]['revision'] ?? 0)===99,
				'F38 checkpoint write failure preserves consumed original session without claiming durable STOP '.$case['name']);
			$freshReplay = new MsmFileBookingCheckpoint($key);
			verify(session_write_close(),'F38 close consumed checkpoint before replay denial');
			$expectRow=$row; $expectRow['_profile']=$binding;
			fails(static function()use($freshReplay,$binding,$user,$expectRow,$options){
				$freshReplay->requireProcessing(1,$binding,$user,$expectRow,$options);
			},'MSMCoreGuardedSessionUnsupported');
			verify(session_start(),'F38 restore consumed session for cleanup');
		} else {
			verify(($_SESSION['msm_previews'][$key]['phase'] ?? null)==='done'
				&& ($_SESSION['msm_previews'][$key]['results'][1]['status'] ?? null)===$case['expected'],
				'CF05 durable terminal result no replay '.$case['name']);
		}
		$replayEvidence=$mail->sendWithEvidence($flow->object,$row,$user,true,
			array('id'=>42,'member_id'=>1,'entity'=>1,'pdf'=>array('status'=>'verified')),
			$checkpoint,1);
		verify($replayEvidence['mail']==='denied'
			&& $mail->calls===($case['expected']==='success' && !isset($case['pdf_status'])
				&& empty($case['pdf_throw']) && empty($case['pdf_invalid_written'])
				&& empty($case['pdf_checkpoint_lost']) ? 1 : 0),
			'F38-03 post-completion direct-service evidence replay always denied '.$case['name']);
		if ($expectStop && !$lostCheckpoint) {
			verify(($_SESSION['msm_previews'][$key]['results'][2]['status'] ?? null)==='not_processed'
				&& !empty($_SESSION['msm_previews'][$key]['batch_stop']),
				'CF05 Phase4 postcommit ambiguity persisted before any next member');
		}
		session_destroy(); session_write_close();
		@unlink($cf05Tmp.'/sess_'.$sessionid);
		if (!empty($case['pdf_invalid_written'])) {
			$file=$pdfRoot.'/INV-SYNTHETIC/INV-SYNTHETIC.pdf';
			verify(is_file($file) && file_get_contents($file)!=='',
				'F38-01 untrusted PDF file existed when output became unknown');
			@unlink($file); @rmdir($pdfRoot.'/INV-SYNTHETIC'); @rmdir($pdfRoot);
		}
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
