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
ini_set('session.use_cookies', '0'); session_save_path($root);
$key = str_repeat('c', 32); $user = (object) array('id' => 18);
$binding = array('profile'=>'CoreGuarded','entity'=>1,'owner'=>18,'revision'=>'known','confirmation_revision'=>4);
$rows = array(7 => array('start'=>123,'end'=>456,'amount'=>12.5,'description'=>'Synthetic contribution','fingerprint'=>'signed','errors'=>array(),'overlaps'=>array()));
$opts = array('invoice'=>false,'autocreate'=>false,'sendmail'=>false,'description'=>'Synthetic contribution');
$sessionid = 'cf02'.bin2hex(random_bytes(12));
try {
	session_id($sessionid); verifySession(session_start(), 'start isolated file session');
	$_SESSION = array('msm_previews'=>array($key=>array('owner'=>18,'entity'=>1,'phase'=>'confirm','revision'=>5,
		'profile'=>$binding,'ids'=>array(7),'options'=>$opts,'rows'=>$rows,
		'results'=>array(7=>array('status'=>'unknown','stop'=>false)))));
	$checkpoint = new MsmFileBookingCheckpoint($key);
	verifySession(session_write_close(), 'persist controller confirmation');
	verifySession(session_start(), 'reopen to consume');
	$_SESSION['msm_previews'][$key]['phase'] = 'processing';
	$_SESSION['msm_previews'][$key]['revision'] = 6;
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
	verifySession(session_start(), 'reopen for changed confirmation');
	$_SESSION['msm_previews'][$key]['results'][7]['status']='success';
	verifySession(session_write_close(), 'persist changed result');
	denySession(static function()use($checkpoint,$binding,$user,$confirmed,$opts){$checkpoint->requireProcessing(7,$binding,$user,$confirmed,$opts);},'completed member cannot be booked twice');
	verifySession(session_start(), 'reopen for phase completion');
	$_SESSION['msm_previews'][$key]['phase']='done';
	verifySession(session_write_close(), 'persist completed phase');
	denySession(static function()use($checkpoint,$binding,$user,$confirmed,$opts){$checkpoint->requireProcessing(7,$binding,$user,$confirmed,$opts);},'completed run denied');
	verifySession(session_start(), 'reopen for test cleanup');
	session_destroy(); session_write_close();
} finally {
	if (session_status() === PHP_SESSION_ACTIVE) { session_abort(); }
	@unlink($root.'/sess_'.$sessionid); @rmdir($root);
	session_save_path($prior); ini_set('session.use_cookies', (string) $oldCookies);
}
echo 'CF-02 durable file-session admission: '.$passed.' assertions PASS (local file I/O; no DB/HTTP).'.PHP_EOL;
