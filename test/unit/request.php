<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__.'/preview.php';
require_once __DIR__.'/../../class/databasestate.class.php';
require_once __DIR__.'/../../lib/booking.lib.php';
require_once __DIR__.'/../../lib/booking_renderer.lib.php';
$beforeRequest = $passed;
$conf->file = (object) array('dol_document_root' => array());
/** Request-contract doubles only; no HTTP transport, database, PDF or SMTP. */
class RequestObserver
{
	public $calls = 0; public $fault = '';
	public function beforeBegin()
	{
		$this->calls++;
		return array('state' => $this->fault ? 'UNKNOWN' : 'IDLE', 'fresh' => !$this->fault,
			'reason' => $this->fault === 'denied' ? 'raw SQL error 1142 with sensitive details' : 'MSMDatabaseObservationUnavailable');
	}
}
class RequestBookingService
{
	public $tables = array(); public $creation = array(); public $books = 0; public $fault = false;
	public function checkTables($invoice, $autocreate = false)
	{
		$this->tables[] = $invoice; $this->creation[] = $autocreate;
		if ($this->fault) { throw new RuntimeException('raw SQL SELECT denied 1142'); }
	}
	public function book($id, $row, $options, $user, $overlap)
	{
		$this->books++;
		return array('status' => 'success', 'message' => 'MSMBooked', 'id' => 71, 'amount' => 12,
			'mail' => 'not_selected', 'stop' => false, '_member' => (object) array('id' => $id));
	}
}
class RequestProfiles
{
	public function admit(...$args) { return array('profile' => 'MariaDBSnapshot'); }
}
class RequestMailFence { public function send(...$args) { return 'not_selected'; } }
$now = dol_now(); $key = str_repeat('c',32);
$base = array('owner' => 1, 'entity' => 1, 'expires' => $now + 300, 'format' => 4,
	'phase' => 'confirm', 'revision' => 5, 'ids' => array(1), 'options' => array('invoice' => false, 'sendmail' => false, 'autocreate' => false, 'description' => 'Synthetic'),
	'rows' => array(1 => array('member' => 'Synthetic <member>', 'type' => 'Synthetic type', 'amount' => 12, 'description' => '<safe description>',
		'start' => stamp('2030-01-01'), 'end' => stamp('2030-12-31'), 'errors' => array(), 'notices' => array(), 'overlaps' => array(), 'quantity' => 1, 'unit' => 'y')));
$post = array('preview' => $key, 'token' => 'synthetic-token', 'revision' => '5', 'action' => 'book');
foreach (array('confirm') as $phase) {
	$state = $base; $state['phase'] = $phase; $observer = new RequestObserver(); $service = new RequestBookingService();
	msmRequireBookingDatabase($state, $observer, $service, new RequestProfiles(), new PreviewUser());
	verify($observer->calls === 1 && $service->tables === array(false) && !$service->books, 'F01 '.$phase.' requires readiness without writes');
	$state['options']['invoice'] = true; msmRequireBookingDatabase($state, $observer, $service, new RequestProfiles(), new PreviewUser());
	verify($service->tables === array(false,true), 'F01 invoice readiness only when selected '.$phase);
	foreach (array('unobservable','instrumentation','denied') as $fault) {
		$observer->fault = $fault;
		fails(static function () use ($state,$observer,$service) { msmRequireBookingDatabase($state,$observer,$service, new RequestProfiles(), new PreviewUser()); }, 'MSMDatabaseObservationUnavailable');
		verify(!$service->books, 'F01 readiness rejection cannot book '.$phase.'/'.$fault);
	}
}
$observer = new RequestObserver(); $service = new RequestBookingService(); $service->fault = true;
fails(static function () use ($base,$observer,$service) { msmRequireBookingDatabase($base,$observer,$service, new RequestProfiles(), new PreviewUser()); }, 'MSMDatabaseObservationUnavailable');
// Stored results bypass ALL readiness, including missing rights/instrumentation.
foreach (array('done','processing') as $phase) {
	foreach (array('success','unknown') as $status) {
		$state = $base; $state['phase'] = $phase;
		$state['results'] = array(1 => array('status' => $status, 'message' => $status === 'success' ? 'MSMBooked' : 'MSMTransactionUnknown',
			'id' => $status === 'success' ? 71 : 0, 'amount' => 12, 'mail' => 'not_selected', 'stop' => $status === 'unknown',
			'diagnostic' => array('stage' => 'commit', 'subscription_id' => 71), 'rollback' => 'unconfirmed'));
		$observer = new RequestObserver(); $observer->fault = 'denied'; $service = new RequestBookingService(); $service->fault = true;
		$session = array('token' => 'synthetic-token', 'msm_previews' => array($key => $state));
		foreach (array('GET','GET','POST') as $method) {
			$read = msmReadPreview($session,$key,1,1,$now);
			if ($method === 'POST') { fails(static function () use ($read,$post) { msmConsumeConfirmation($read,$post); }, 'MSMInvalidForm'); }
			msmRequireBookingDatabase($read,$observer,$service, new RequestProfiles(), new PreviewUser());
			$html = msmRenderBooking($read,$key);
			verify(strpos($html,'<form') === false && strpos($html,'MSMStatus_'.$status) !== false && strpos($html,'&lt;safe description&gt;') !== false, 'F01 stored result visible and escaped '.$phase.'/'.$status.'/'.$method);
			verify($session['msm_previews'][$key] === $state && !$observer->calls && !$service->tables && !$service->books, 'F01 consumed result read never restarts or mutates '.$phase.'/'.$status.'/'.$method);
		}
		fails(static function () use ($session,$key,$now) { msmReadPreview($session,$key,2,1,$now); }, 'MSMInvalidRun');
		fails(static function () use ($session,$key,$now) { msmReadPreview($session,$key,1,2,$now); }, 'MSMInvalidRun');
	}
}
// Successful synthetic POST path: CSRF/session binding then ready check and durable consumption.
$session = array('token' => 'synthetic-token', 'msm_previews' => array($key => $base));
verify(msmValidCsrf($post['token'],$session) && !msmValidCsrf('wrong',$session) && !msmValidCsrf(null,$session), 'F01 CSRF contract unchanged');
$state = msmReadPreview($session,$key,1,1,$now); $ready = msmConsumeConfirmation($state,$post);
$observer = new RequestObserver(); $service = new RequestBookingService(); msmRequireBookingDatabase($state,$observer,$service, new RequestProfiles(), new PreviewUser());
$session['msm_previews'][$key] = $ready; $checkpoints = array();
$done = msmProcessBooking($ready,$service,new RequestMailFence(),new PreviewUser(),static function ($updated) use (&$session,$key,&$checkpoints) { $session['msm_previews'][$key] = $updated; $checkpoints[] = $updated; });
verify($service->books === 1 && $done['phase'] === 'done' && $done['results'][1]['status'] === 'success', 'F01 valid synthetic POST books once');
fails(static function () use ($done,$post) { msmConsumeConfirmation($done,$post); }, 'MSMInvalidForm');
verify($service->books === 1 && $checkpoints, 'F01 POST replay cannot book again');
// Result links default to denied when Core object reads cannot be performed.
$dbUnavailable = new class { public function __call($name,$args) { throw new RuntimeException('raw SQL unavailable; sensitive details'); } };
$done['results'][1]['invoice'] = array('id' => 42, 'entity' => 1, 'customer_id' => 4, 'customer_created' => true);
$access = msmResultAccess($dbUnavailable,new PreviewUser(),$done);
verify($access[1] === array('invoice' => false, 'customer' => false), 'F01 unavailable DB denies invoice/customer links without exposing errors');
// I107 selected customer-creation option reaches the read-only Agenda gate.
$state=$base;$state['options']['invoice']=true;$state['options']['autocreate']=true;
$observer=new RequestObserver();$service=new RequestBookingService();msmRequireBookingDatabase($state,$observer,$service, new RequestProfiles(), new PreviewUser());
verify($service->tables===array(true)&&$service->creation===array(true)&&!$service->books,'I107 authenticated preflight forwards invoice/autocreate without booking');
echo ($passed-$beforeRequest)." request readiness/result assertions passed (synthetic contracts; no HTTP/database/business operations).\n";

$state=$base;$state['ids']=range(1,100);$conf->global->MEMBERSUBSCRIPTIONMASSACTION_MAX_MEMBERS_PER_BATCH='100';
$ready=msmConsumeConfirmation($state,$post);verify($ready['phase']==='processing','Issue8 default100 confirmation');
$conf->global->MEMBERSUBSCRIPTIONMASSACTION_MAX_MEMBERS_PER_BATCH='25';
fails(static function () use($state,$post){msmConsumeConfirmation($state,$post);},'MSMBatchLimit');
$observer=new RequestObserver();$service=new RequestBookingService();
fails(static function () use($state,$observer,$service){msmRequireBookingDatabase($state,$observer,$service, new RequestProfiles(), new PreviewUser());},'MSMBatchLimit');
verify(!$observer->calls&&!$service->books,'Issue8 reduced limit blocks before DB readiness');
fails(static function () use($ready,$service){msmProcessBooking($ready,$service,new RequestMailFence(),new PreviewUser(),static function($x){});},'MSMBatchLimit');
verify(!$service->books,'Issue8 reduced processing limit zero business calls');
$conf->global->MEMBERSUBSCRIPTIONMASSACTION_MAX_MEMBERS_PER_BATCH='100';
$state['ids']=range(1,101);fails(static function () use($state,$post){msmConsumeConfirmation($state,$post);},'MSMBatchLimit');
verify(count($state['ids'])===101&&$state['phase']==='confirm','Issue8 no truncation or consumption on rejection');
echo "Issue8 stale/reduced/oversized request contracts passed.\n";

// CF-01: readiness is a final-confirmation gate, never a preview operation.
$state=$base;$state['phase']='preview';$observer=new RequestObserver();$service=new RequestBookingService();
fails(static function()use($state,$observer,$service){msmRequireBookingDatabase($state,$observer,$service,new RequestProfiles(),new PreviewUser());},'MSMInvalidForm');
verify(!$observer->calls&&!$service->tables,'CF01 preview never invokes transaction readiness');
require __DIR__.'/profile.php';

require __DIR__."/controller-runtime.php";
