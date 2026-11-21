<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
if (PHP_SAPI !== 'cli' || ($argv[1] ?? '') !== '--fixture' || empty($argv[2])) { exit(77); }
// Synthetic bootstrap/admission/services; execute the actual controller body, not
// a rewritten flow. Native PHP sessions persist between isolated CLI requests.
ob_start();
require __DIR__.'/../unit/booking.php';
ob_clean();
$spec = json_decode(file_get_contents($argv[2]), true);
$repo = dirname(__DIR__, 2); $key = str_repeat('a', 32); $events = array(); $bookCalls = 0;
ini_set('session.use_cookies', '0'); session_cache_limiter(''); session_save_path($spec['directory']);
session_id('f28'.substr(hash('sha256',$spec['directory']),0,32));
$originalId = session_id();
if ($spec['case'] === 'session') { session_set_save_handler(new SessionHandler(), false); }
session_start();
class F28Profiles
{
	public $name;
	public function current() { return array('family' => $this->name === 'MariaDBSnapshot' ? 'mariadb' : 'mysql', 'profile' => $this->name, 'revision' => str_repeat('b',64), 'features' => array('booking'=>true,'invoice'=>true,'autocreate'=>true,'sendmail'=>true), 'reason'=>''); }
	public function binding($c,$user,$rev) { return array('family'=>$c['family'],'profile'=>$c['profile'],'revision'=>$c['revision'],'features'=>$c['features'],'owner'=>1,'entity'=>1,'confirmation_revision'=>$rev); }
	public function admit(...$args) { $GLOBALS['events'][] = 'admission_spy'; return $this->current(); }
}
class F28Database extends BookingDatabase { public function close() { $GLOBALS['events'][]='db_close_spy'; } }
class F28Observer { public function beforeBegin() { return array('state'=>'IDLE','fresh'=>true); } }
class F28Service
{
	public function coreGuardedReadiness() { $GLOBALS['events'][] = 'core_guarded_readiness_spy'; }
	public function checkTables(...$args) { $GLOBALS['events'][] = 'tables_spy'; }
	public function book(...$args) {
		$GLOBALS['bookCalls']++; $GLOBALS['events'][] = 'book_possible_DML_spy';
		if ($GLOBALS['spec']['case'] === 'after_dml') { throw new RuntimeException('Synthetic error after possible business DML'); }
		return array('status'=>'success','message'=>'MSMBooked','id'=>7,'amount'=>12,'mail'=>'not_selected','stop'=>false,'_member'=>new stdClass());
	}
}
class F28Languages extends Translate
{
	public $labels;
	public function __construct($root) {
		parent::__construct(DOL_DOCUMENT_ROOT, $GLOBALS['conf']); $this->setDefaultLang('en_US');
		$this->labels = array(); foreach (file($root.'/langs/en_US/membersubscriptionmassaction.lang',FILE_IGNORE_NEW_LINES) as $line) {
			if ($line !== '' && $line[0] !== '#' && strpos($line,'=') !== false) { list($k,$v)=explode('=',$line,2); $this->labels[$k]=$v; }
		}
	}
	public function trans($key, $param1 = '', $param2 = '', $param3 = '', $param4 = '', $maxsize = 0)
	{
		return $this->labels[$key] ?? parent::trans($key, $param1, $param2, $param3, $param4, $maxsize);
	}

	public function transnoentities($key, $param1 = '', $param2 = '', $param3 = '', $param4 = '', $param5 = '')
	{
		return $this->labels[$key] ?? parent::transnoentities($key, $param1, $param2, $param3, $param4, $param5);
	}
}
if (!function_exists('restrictedArea')) { function restrictedArea(...$args) { return 1; } }
if (!function_exists('getEntity')) { function getEntity(...$args) { return '1'; } }
function f28Header($value,$replace=true,$code=0) { $GLOBALS['events'][] = strpos($value,'Location:')===0 ? 'redirect:'.$code : 'header'; }
function f28Accessforbidden(...$args) { $GLOBALS['events'][] = 'forbidden'; exit; }
function f28LlxHeader(...$args) {}
function f28LlxFooter(...$args) {}
function f28Title(...$args) { return ''; }
function f28Close() {
	$r=session_write_close(); $GLOBALS['events'][] = 'session_closed';
	if ($GLOBALS['spec']['case']==='binding' && empty($GLOBALS['bindingInjected'])) { session_id('f28changedbinding'); $GLOBALS['bindingInjected']=true; }
	return $r;
}
$GLOBALS['f28profiles'] = new F28Profiles(); $GLOBALS['f28profiles']->name = $spec['profile'];
$GLOBALS['f28observer'] = new F28Observer(); $GLOBALS['f28service'] = new F28Service();
$user = new PreviewUser(); $db = new F28Database(); $conf->file = (object) array('dol_document_root'=>array('main'=>DOL_DOCUMENT_ROOT)); $langs = new F28Languages($repo);
$conf->modules['membersubscriptionmassaction'] = 1; $conf->modules['syslog'] = 1;
$conf->global->SYSLOG_FILE = $spec['directory'].'/synthetic.log';
require_once DOL_DOCUMENT_ROOT.'/core/modules/syslog/mod_syslog_file.php';
$conf->loghandlers = array($spec['case']==='logger' ? new stdClass() : new mod_syslog_file());
if (!isset($_SESSION['msm_previews'])) {
	$initial = array('format'=>4,'owner'=>1,'entity'=>1,'expires'=>dol_now()+300,'phase'=>'confirm','revision'=>5,'ids'=>array(1),
		'options'=>array('invoice'=>false,'sendmail'=>false,'autocreate'=>false,'description'=>'Synthetic'),
		'rows'=>array(1=>array('member'=>'Synthetic','type'=>'Synthetic','amount'=>12,'description'=>'Synthetic','start'=>1,'end'=>2,'errors'=>array(),'notices'=>array(),'overlaps'=>array(),'quantity'=>1,'unit'=>'y')));
	$initial['profile']=$GLOBALS['f28profiles']->binding($GLOBALS['f28profiles']->current(),$user,5);
	$_SESSION=array('token'=>'synthetic-token','msm_previews'=>array($key=>$initial));
}
$initialPhase=$_SESSION['msm_previews'][$key]['phase'];
$_SERVER['REQUEST_METHOD']=$spec['method']; $_SERVER['CONTEXT_DOCUMENT_ROOT']=$spec['directory'];
$_POST=$spec['method']==='POST'?array('preview'=>$key,'token'=>'synthetic-token','revision'=>(string)($spec['revision']??'5'),'action'=>'book'):array();
if ($spec['case']==='csrf') { $_POST['token']='wrong'; }
if ($spec['case']==='entity') { $_SESSION['msm_previews'][$key]['entity']=2; }
$_GET=$spec['method']==='GET'?array('preview'=>$key):array();
// Only bootstrap, object construction and effect boundaries are instrumented.
// Preserve all conditionals, pure-consume call, session assignment, catch and exit.
file_put_contents($spec['directory'].'/main.inc.php','<?php /* synthetic authenticated bootstrap, no protected config */');
$source=getenv('F28_CONTROLLER_REF')?shell_exec('git -C '.escapeshellarg($repo).' show '.escapeshellarg(getenv('F28_CONTROLLER_REF').':subscription.php')):file_get_contents($repo.'/subscription.php');
$sourceSha=hash('sha256',$source);
$replacements=array(
 'new MsmDatabaseState($db)'=>"\$GLOBALS['f28observer']",
 'new MsmBookingService($db, $databaseState)'=>"\$GLOBALS['f28service']",
 'new MsmTransactionProfile($db, $databaseState, (int) $conf->entity)'=>"\$GLOBALS['f28profiles']");
foreach ($replacements as $from=>$to) { $source=str_replace($from,$to,$source,$n); if($n!==1)throw new LogicException('Controller construction instrumentation drift'); }
$source=str_replace('__DIR__',var_export($repo,true),$source);
foreach (array('header'=>'f28Header','accessforbidden'=>'f28Accessforbidden','llxHeader'=>'f28LlxHeader','llxFooter'=>'f28LlxFooter','load_fiche_titre'=>'f28Title','session_write_close'=>'f28Close') as $from=>$to) {
 $source=preg_replace('/(?<![A-Za-z0-9_])'.preg_quote($from,'/').'\(/',$to.'(',$source);
}
register_shutdown_function(static function () use ($spec,$key,$originalId,$sourceSha,$initialPhase) {
	$html=ob_get_contents(); ob_end_clean();
	if(session_status()===PHP_SESSION_ACTIVE)session_write_close();
	session_id($originalId);session_start();$stored=$_SESSION['msm_previews'][$key]??array();session_write_close();
	$error=$spec['case']==='session'?'MSMCoreGuardedSessionUnsupported':'MSMCoreGuardedLoggerUnsupported';
	$label=$GLOBALS['langs']->labels[$error];
	echo json_encode(array('case'=>$spec['case'],'method'=>$spec['method'],'profile'=>$spec['profile'],'source_sha256'=>$sourceSha,
		'phase'=>$stored['phase']??null,'revision'=>$stored['revision']??null,'initial_phase'=>$initialPhase,'flash'=>$stored['flash']??null,
		'result_status'=>$stored['results'][1]['status']??null,'book_calls'=>$GLOBALS['bookCalls'],'events'=>$GLOBALS['events'],
		'visible_specific_error'=>strpos($html,dol_escape_htmltag($label))!==false,'has_confirmation_form'=>strpos($html,'msm-confirm-form')!==false));
});
file_put_contents($spec['directory'].'/controller.php',$source);
require $spec['directory'].'/controller.php';
