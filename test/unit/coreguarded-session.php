<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__.'/../../class/filebookingcheckpoint.class.php';
$passed = 0;
function verifySession($ok, $name) { global $passed; if (!$ok) { throw new RuntimeException('FAILED '.$name); } $passed++; }
function denySession(callable $fn, $name)
{
	try { $fn(); } catch (RuntimeException $e) {
		verifySession(in_array($e->getMessage(), array('MSMCoreGuardedSessionUnsupported', 'MSMSessionFailure'), true), $name); return;
	}
	throw new RuntimeException('Expected denial '.$name);
}
$root = sys_get_temp_dir().'/cf02-session-'.bin2hex(random_bytes(12));
if (!mkdir($root, 0700)) { throw new RuntimeException('Unable to create test directory'); }
$prior = session_save_path(); $oldCookies = ini_get('session.use_cookies');
$oldGc = ini_get('session.gc_probability');
ini_set('session.use_cookies', '0');
ini_set('session.gc_probability', '0'); // Shared hosts can manage session cleanup separately.
session_save_path($root);
$key = str_repeat('c', 32); $user = (object) array('id' => 18);
$features=array('booking'=>true,'invoice'=>true,'autocreate'=>true,'sendmail'=>true);
$binding = array('family'=>'mysql','profile'=>'CoreGuarded','entity'=>1,'owner'=>18,
	'revision'=>'known','confirmation_revision'=>4,'features'=>$features);
$currentFinanceProfile=array('family'=>'mysql','profile'=>'CoreGuarded',
	'revision'=>'known','features'=>$features,'reason'=>'');
$rows = array(7 => array('start'=>123,'end'=>456,'amount'=>12.5,'description'=>'Synthetic contribution','fingerprint'=>'signed','errors'=>array(),'overlaps'=>array()));
$opts = array('invoice'=>false,'autocreate'=>false,'sendmail'=>false,'description'=>'Synthetic contribution');
$sessionid = 'cf02'.bin2hex(random_bytes(12));
try {
	// The owner can open known files but cannot list the session directory.
	verifySession(chmod($root, 0300), 'configure non-listable session directory');
	clearstatcache(true, $root);
	session_id($sessionid); verifySession(session_start(), 'start isolated file session');
	$_SESSION = array('msm_previews'=>array($key=>array('owner'=>18,'entity'=>1,'phase'=>'confirm','revision'=>5,
		'profile'=>$binding,'ids'=>array(7),'options'=>$opts,'rows'=>$rows,
		'results'=>array(7=>array('status'=>'unknown','stop'=>false)))));
	$checkpoint = new MsmFileBookingCheckpoint($key);
	verifySession(session_write_close(), 'persist controller confirmation');
	verifySession(session_start(), 'reopen to consume');
	$_SESSION['msm_previews'][$key]['phase'] = 'processing';
	$_SESSION['msm_previews'][$key]['revision'] = 6;
	$updated = $_SESSION['msm_previews'][$key];
	verifySession(session_write_close(), 'persist controller consumption');
	$confirmed = $rows[7]; $confirmed['_profile'] = $binding;
	$checkpoint->requireProcessing(7, $binding, $user, $confirmed, $opts);
	verifySession(session_status() === PHP_SESSION_NONE, 'independent readback closed session');
	denySession(static function()use($checkpoint,$binding,$user,$confirmed,$opts){$checkpoint->requireProcessing(7,$binding,$user,$confirmed,$opts);},'same-capability replay denied');
	$replayed = new MsmFileBookingCheckpoint($key);
	denySession(static function()use($replayed,$binding,$user,$confirmed,$opts){$replayed->requireProcessing(7,$binding,$user,$confirmed,$opts);},'new checkpoint after consumption denied');
	denySession(static function() use ($checkpoint,$binding,$user,$confirmed,$opts) { $checkpoint->requireProcessing(8,$binding,$user,$confirmed,$opts); }, 'unknown member denied');
	$wrong=$confirmed; $wrong['amount']=999;
	denySession(static function()use($checkpoint,$binding,$user,$wrong,$opts){$checkpoint->requireProcessing(7,$binding,$user,$wrong,$opts);},'wrong expected amount denied');
	$wrong=$binding;$wrong['owner']=99;
	denySession(static function()use($checkpoint,$wrong,$user,$confirmed,$opts){$checkpoint->requireProcessing(7,$wrong,$user,$confirmed,$opts);},'forged profile binding denied');
	$changed=$opts;$changed['invoice']=true;
	denySession(static function()use($checkpoint,$binding,$user,$confirmed,$changed){$checkpoint->requireProcessing(7,$binding,$user,$confirmed,$changed);},'forged invoice option denied');
	$wrong=(object)array('id'=>99);
	denySession(static function()use($checkpoint,$binding,$wrong,$confirmed,$opts){$checkpoint->requireProcessing(7,$binding,$wrong,$confirmed,$opts);},'different user denied');
	$updated['results'][7]['status'] = 'success';
	$checkpoint($updated);
	verifySession(session_start(), 'reopen after saving result');
	verifySession($_SESSION['msm_previews'][$key]['results'][7]['status'] === 'success', 'read back saved result');
	verifySession(session_write_close(), 'close persisted result');
	denySession(static function()use($checkpoint,$binding,$user,$confirmed,$opts){$checkpoint->requireProcessing(7,$binding,$user,$confirmed,$opts);},'completed member cannot be booked twice');
	verifySession(session_start(), 'reopen for phase completion');
	$_SESSION['msm_previews'][$key]['phase']='done';
	verifySession(session_write_close(), 'persist completed phase');
	denySession(static function()use($checkpoint,$binding,$user,$confirmed,$opts){$checkpoint->requireProcessing(7,$binding,$user,$confirmed,$opts);},'completed run denied');
	// CF05: original CONFIRM → durable processing → one-shot finance → acknowledged
	// NO_CHAIN result → independently checkpointed PDF and mail. No DB or SMTP.
	$financeKey = str_repeat('d',32);
	$financeRow = $rows[7]; $financeRow['invoice']=array('create_customer'=>true,'customer_id'=>0);
	$financeRow['autocreate']=true; $financeRows=array(7=>$financeRow);
	$financeOpts=array('invoice'=>true,'autocreate'=>true,'sendmail'=>true,
		'description'=>'Synthetic contribution');
	verifySession(session_start(), 'CF05 new synthetic finance confirmation');
	$_SESSION['msm_previews'][$financeKey]=array('owner'=>18,'entity'=>1,'phase'=>'confirm','revision'=>10,
		'profile'=>$binding,'ids'=>array(7),'options'=>$financeOpts,'rows'=>$financeRows,
		'results'=>array(7=>array('status'=>'unknown','stop'=>false)));
	$financeCheckpoint = new MsmFileBookingCheckpoint($financeKey);
	verifySession(session_write_close(), 'CF05 persist finance confirmation');
	verifySession(session_start(), 'CF05 consume original finance confirmation');
	$_SESSION['msm_previews'][$financeKey]['phase']='processing';
	$_SESSION['msm_previews'][$financeKey]['revision']=11;
	verifySession(session_write_close(), 'CF05 persist finance consumption');
	$financeExpected=$financeRow; $financeExpected['_profile']=$binding;
	$financeCheckpoint->requireProcessing(7,$binding,$user,$financeExpected,$financeOpts);
	$recipient=(object)array('id'=>7,'entity'=>1,'typeid'=>1,'email'=>'original@example.invalid');
	$financeCheckpoint->bindMailRecipient(7,$recipient,$financeRow);
	denySession(static function()use($financeCheckpoint,$recipient,$financeRow){
		$financeCheckpoint->bindMailRecipient(7,$recipient,$financeRow);
	},'F38-03 recipient identity seal cannot be overwritten');
	// F38-10: each negative is the FIRST finance invocation on an original,
	// independently consumed capability. An unchanged fingerprint cannot mask
	// altered actual fee, dates, description, invoice plan or profile carrier.
	foreach (array('amount', 'start', 'end', 'description', 'invoice', '_profile', 'extra') as $field) {
		$testKey=bin2hex(random_bytes(16));
		verifySession(session_start(), 'F38-10 fresh confirmation '.$field);
		$_SESSION['msm_previews'][$testKey]=array('owner'=>18,'entity'=>1,
			'phase'=>'confirm','revision'=>10,'profile'=>$binding,'ids'=>array(7),
			'options'=>$financeOpts,'rows'=>$financeRows,
			'results'=>array(7=>array('status'=>'unknown','stop'=>false)));
		$testCheckpoint=new MsmFileBookingCheckpoint($testKey);
		verifySession(session_write_close(), 'F38-10 persist confirm '.$field);
		verifySession(session_start(), 'F38-10 consume confirm '.$field);
		$_SESSION['msm_previews'][$testKey]['phase']='processing';
		$_SESSION['msm_previews'][$testKey]['revision']=11;
		verifySession(session_write_close(), 'F38-10 persist consumed '.$field);
		$testCheckpoint->requireProcessing(7,$binding,$user,$financeExpected,$financeOpts);
		$changedRow=$financeRow;
		switch ($field) {
			case 'amount': $changedRow['amount']=999; break;
			case 'start': $changedRow['start']=999; break;
			case 'end': $changedRow['end']=999; break;
			case 'description': $changedRow['description']='altered'; break;
			case 'invoice': $changedRow['invoice']['customer_id']=99; break;
			case '_profile': $changedRow['_profile']=$binding; $changedRow['_profile']['owner']=99; break;
			case 'extra': $changedRow['extra']='injected'; break;
		}
		verifySession($changedRow['fingerprint']===$financeRow['fingerprint'],
			'F38-10 original fingerprint retained '.$field);
		denySession(static function()use($testCheckpoint,$changedRow,$user){
			$testCheckpoint->requireFinanceContinuation(7,$changedRow,$user);
		},'F38-10 first altered direct finance attempt denied '.$field);
	}
	// Matching optional profile carrier is permitted, but never authorizes replay.
	$financeWithProfile=$financeRow; $financeWithProfile['_profile']=$binding;
	$financeCheckpoint->requireFinanceContinuation(7,$financeWithProfile,$user);
	verifySession(true, 'CF05 same original owner one-shot finance capability admitted');
	denySession(static function()use($financeCheckpoint,$financeRow,$user){
		$financeCheckpoint->requireFinanceContinuation(7,$financeRow,$user);
	}, 'CF05 duplicate finance API continuation denied');
	denySession(static function()use($financeCheckpoint,$financeRow,$user){
		$financeCheckpoint->requireFinanceContinuation(8,$financeRow,$user);
	}, 'CF05 foreign member denied');
	$wrongRow=$financeRow; $wrongRow['fingerprint']='changed';
	$otherKey = new MsmFileBookingCheckpoint($financeKey);
	denySession(static function()use($otherKey,$financeRow,$user){
		$otherKey->requireFinanceContinuation(7,$financeRow,$user);
	}, 'CF05 new capability from consumed file session denied');
	denySession(static function()use($financeCheckpoint){
		$financeCheckpoint->requirePostCommitEffect(7,'pdf');
	}, 'CF05 PDF denied before acknowledged nonterminal result');
	verifySession(session_start(), 'CF05 record accepted nonterminal invoice');
	$_SESSION['msm_previews'][$financeKey]['results'][7]=array(
		'status'=>'success','completion'=>'nonterminal_acknowledged','stop'=>false,
		'invoice'=>array('id'=>42,'member_id'=>7,'entity'=>1,
			'pdf'=>array('status'=>'unknown')),'mail'=>'unknown');
	verifySession(session_write_close(), 'CF05 persist accepted nonterminal checkpoint');
	$financeCheckpoint->requirePostCommitEffect(7,'pdf');
	denySession(static function()use($financeCheckpoint){
		$financeCheckpoint->requirePostCommitEffect(7,'pdf');
	},'CF05 PDF replay blocked');
	denySession(static function()use($financeCheckpoint){
		$financeCheckpoint->requirePostCommitEffect(7,'mail');
	},'CF05 mail blocked without verified invoice PDF');
	verifySession(session_start(), 'CF05 store verified invoice PDF');
	$_SESSION['msm_previews'][$financeKey]['results'][7]['invoice']['pdf']=array('status'=>'verified');
	verifySession(session_write_close(), 'CF05 persist verified invoice PDF');
	$financeCheckpoint->requirePostCommitEffect(7,'mail');
	denySession(static function()use($financeCheckpoint){
		$financeCheckpoint->requirePostCommitEffect(7,'mail');
	},'CF05 mail resend blocked');
	// F38-03: original identity/descriptor and exact profile revision must
	// match the independently persisted one-shot mail admission.
	$persistedInvoice=array('id'=>42,'member_id'=>7,'entity'=>1,
		'pdf'=>array('status'=>'verified'));
	$mailRow=$financeRow; $mailRow['_profile']=$binding;
	$wrongInvoice=$persistedInvoice; $wrongInvoice['id']=99;
	denySession(static function()use($financeCheckpoint,$user,$wrongInvoice,$currentFinanceProfile,$recipient,$mailRow){
		$financeCheckpoint->requireMailEvidence(7,$user,$wrongInvoice,$currentFinanceProfile,$recipient,$mailRow);
	},'F38-03 altered invoice cannot borrow the already consumed mail admission');
	$wrongProfile=$currentFinanceProfile; $wrongProfile['revision']='new-admin-nonce';
	denySession(static function()use($financeCheckpoint,$user,$persistedInvoice,$wrongProfile,$recipient,$mailRow){
		$financeCheckpoint->requireMailEvidence(7,$user,$persistedInvoice,$wrongProfile,$recipient,$mailRow);
	},'F38-03 refreshed or forged profile cannot use original checkpoint');
	$wrongRecipient=clone $recipient; $wrongRecipient->email='attacker@example.invalid';
	denySession(static function()use($financeCheckpoint,$user,$persistedInvoice,$currentFinanceProfile,$wrongRecipient,$mailRow){
		$financeCheckpoint->requireMailEvidence(7,$user,$persistedInvoice,$currentFinanceProfile,$wrongRecipient,$mailRow);
	},'F38-03 same member ID with substituted email denied before hook/SMTP');
	$wrongRecipient=clone $recipient; $wrongRecipient->entity=2;
	denySession(static function()use($financeCheckpoint,$user,$persistedInvoice,$currentFinanceProfile,$wrongRecipient,$mailRow){
		$financeCheckpoint->requireMailEvidence(7,$user,$persistedInvoice,$currentFinanceProfile,$wrongRecipient,$mailRow);
	},'F38-03 same member ID and email but different entity denied before hook/SMTP');
	$wrongRecipient=clone $recipient; $wrongRecipient->typeid=5;
	denySession(static function()use($financeCheckpoint,$user,$persistedInvoice,$currentFinanceProfile,$wrongRecipient,$mailRow){
		$financeCheckpoint->requireMailEvidence(7,$user,$persistedInvoice,$currentFinanceProfile,$wrongRecipient,$mailRow);
	},'F38-03 changed membership type denied before hook/SMTP');
	foreach (array('description'=>'forged','amount'=>42,'start'=>987,'end'=>654,'fingerprint'=>'forged') as $field=>$value) {
		$wrongRow=$mailRow; $wrongRow[$field]=$value;
		denySession(static function()use($financeCheckpoint,$user,$persistedInvoice,$currentFinanceProfile,$recipient,$wrongRow){
			$financeCheckpoint->requireMailEvidence(7,$user,$persistedInvoice,$currentFinanceProfile,$recipient,$wrongRow);
		},'F38-03 changed confirmed mail parameter '.$field.' denied');
	}
	$wrongRow=$mailRow; $wrongRow['_profile']['owner']=999;
	denySession(static function()use($financeCheckpoint,$user,$persistedInvoice,$currentFinanceProfile,$recipient,$wrongRow){
		$financeCheckpoint->requireMailEvidence(7,$user,$persistedInvoice,$currentFinanceProfile,$recipient,$wrongRow);
	},'F38-03 altered profile carrier denied');
	$financeCheckpoint->requireMailEvidence(7,$user,$persistedInvoice,$currentFinanceProfile,$recipient,$mailRow);
	verifySession(true,'F38-03 exact original member, email, entity, row, profile and invoice permits one evidence continuation');
	denySession(static function()use($financeCheckpoint,$user,$persistedInvoice,$currentFinanceProfile,$recipient,$mailRow){
		$financeCheckpoint->requireMailEvidence(7,$user,$persistedInvoice,$currentFinanceProfile,$recipient,$mailRow);
	},'F38-03 post-effect mail evidence replay denied');
	verifySession(session_start(), 'CF05 change completion to terminal');
	$_SESSION['msm_previews'][$financeKey]['results'][7]['completion']='terminal_acknowledged';
	verifySession(session_write_close(), 'CF05 terminal result persisted');
	$otherKey = new MsmFileBookingCheckpoint($financeKey);
	denySession(static function()use($otherKey){
		$otherKey->requirePostCommitEffect(7,'mail');
	},'CF05 no new postcommit capability after consumed state');
	verifySession(session_start(), 'reopen for test cleanup');
	session_destroy(); session_write_close();
} finally {
	if (session_status() === PHP_SESSION_ACTIVE) { session_abort(); }
	@unlink($root.'/sess_'.$sessionid); @rmdir($root);
	session_save_path($prior); ini_set('session.use_cookies', (string) $oldCookies);
	ini_set('session.gc_probability', (string) $oldGc);
}
echo 'CF-02 durable file-session admission: '.$passed.' assertions PASS (local file I/O; no DB/HTTP).'.PHP_EOL;
