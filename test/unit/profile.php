<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__.'/booking.php';
require_once __DIR__.'/../../lib/profile.lib.php';
$profileBaseline = $passed;
/** In-memory SQL result model only. No server/bootstrap, credentials or transport. */
class ProfileDatabase extends BookingDatabase
{
	public $profileRaw = null; public $affected = 0; public $writes = 0;
	public $onRead; public $onWrite; public $configDenied = false;
	public function encrypt($s) { return "'".addslashes($s)."'"; }
	public function decrypt($s) { return $s; }
	public function affected_rows($r = null) { return $this->affected; }
	public function query($sql)
	{
		if ($sql === 'SELECT @@autocommit AS enabled') { return (object) array('rows'=>array((object) array('enabled'=>$this->autocommit))); }
		if (strpos($sql, 'test_const') === false) { return parent::query($sql); }
		$this->queries[] = $sql;
		if ($this->configDenied) { return false; }
		if (strpos($sql, 'SELECT value AS value FROM test_const') === 0) {
			$raw = strpos($sql, 'entity = 1') !== false ? $this->profileRaw : null;
			if ($this->onRead) { $f=$this->onRead; $this->onRead=null; $f($this); }
			return (object) array('rows' => $raw === null ? array() : array((object) array('value' => $raw)));
		}
		if ($this->onWrite) { $f=$this->onWrite; $this->onWrite=null; $f($this); }
		$this->affected = 0;
		preg_match_all("/'([^']*)'/", $sql, $m);
		$m[1] = array_map('stripslashes', $m[1]);
		if (strpos($sql, 'INSERT INTO test_const') === 0) {
			if ($this->profileRaw !== null) { return false; }
			$this->profileRaw=$m[1][1];
		} elseif (strpos($sql, 'UPDATE test_const SET value') === 0) {
			if ($this->profileRaw !== $m[1][2]) { return (object) array('rows'=>array()); }
			verify(strpos($sql,'AND BINARY value = BINARY')!==false,'CF01 CAS compares exact bytes');
			$this->profileRaw=$m[1][0];
		} else { throw new LogicException('Unexpected synthetic config SQL'); }
		$this->writes++; $this->affected=1;
		return (object) array('rows'=>array());
	}
}
class ProfileAdmin extends PreviewUser { public $admin = 1; }
class ProfileBookingFlow extends BookingServiceDouble
{
	protected function profiles() { global $conf; return new MsmTransactionProfile($this->profileDb, $this->databaseState, (int)$conf->entity); }
}
function profileRaw($family,$profile,$nonce='a') { return json_encode(array('version'=>1,'family'=>$family,'profile'=>$profile,'nonce'=>str_repeat($nonce,32))); }
function profileStore($family='mysql',$raw=null)
{
	$db=new ProfileDatabase();$db->family=$family;$db->profileRaw=$raw;
	return array($db,new MsmTransactionProfile($db,new SyntheticDatabaseState($db),1));
}
function profileState()
{
	return array('owner'=>1,'entity'=>1,'expires'=>dol_now()+600,'phase'=>'preview','format'=>4,'revision'=>0,'ids'=>array(1),'rows'=>array(),'input'=>array(),'common'=>'',
		'options'=>array('invoice'=>false,'autocreate'=>false,'sendmail'=>false,'description'=>'Synthetic'));
}
$user=new PreviewUser();$admin=new ProfileAdmin();$conf->entity=1;
$conf->global=new stdClass();$conf->modules['member']=1;
// Fresh, previously activated, reactivated and unknown installations are deliberately
// indistinguishable without reliable history: absent MySQL config never downgrades.
foreach(array('fresh','legacy','reactivated','unknown') as $history){
	list($pd,$ps)=profileStore();$c=$ps->current();$b=$ps->binding($c,$user,0);
	verify($c['reason']==='MSMProfileSelectionRequired'&&!$c['features']['booking'],'CF01 '.$history.' missing MySQL requires admin');
	fails(static function()use($ps,$b,$user){$ps->admit($b,$user,array());},'MSMProfileSelectionRequired');
	verify(!$pd->writes&&!$pd->events,'CF01 no implicit activation writes/transaction');
}
list($pd,$ps)=profileStore('mariadb');$c=$ps->current();
verify($c['profile']==='MariaDBSnapshot'&&$c['features']['invoice'],'CF01 missing MariaDB preserves snapshot/invoice scope');
$ps->admit($ps->binding($c,$user,0),$user,array('invoice'=>true));
foreach(array('', '{}', 'bad',profileRaw('mysql','invalid'),profileRaw('mariadb','MariaDBSnapshot')) as $raw){
	list($pd,$ps)=profileStore('mysql',$raw);$c=$ps->current();
	fails(static function()use($ps,$c,$user){$ps->admit($ps->binding($c,$user,0),$user,array());},'MSMProfileInvalid');
}
list($pd,$ps)=profileStore('mysql',profileRaw('mysql','CoreGuarded'));$pd->psDenied=true;
$c=$ps->current();$b=$ps->binding($c,$user,0);
verify($ps->admit($b,$user,array('invoice'=>false))['features']['booking'], 'CF02 CoreGuarded contributes without P_S');
verify($c['features']['booking'] && !$c['features']['invoice']
	&& !$c['features']['autocreate'] && !$c['features']['sendmail'],
	'F36-01 valid configured CoreGuarded retains contribution but all finance gates are default OFF');
foreach (array('invoice','autocreate','sendmail') as $feature) {
	fails(static function()use($ps,$b,$user,$feature){
		$ps->admit($b,$user,array($feature=>true));
	}, 'MSMProfileFeatureUnavailable');
}
$forged = $b; $forged['features'] = array('booking'=>true,'invoice'=>true,
	'autocreate'=>true,'sendmail'=>true);
fails(static function()use($ps,$forged,$user){
	$ps->admit($forged,$user,array('invoice'=>true,'autocreate'=>true,'sendmail'=>true));
}, 'MSMProfileChanged');
$maliciousPreview = profileState(); $maliciousPreview['profile']=$b;
$maliciousPreview['options']['invoice']=true;
$maliciousPreview['options']['autocreate']=true;
$maliciousPreview['options']['sendmail']=true;
$reconciled=msmSyncPreviewProfile($maliciousPreview,$ps,$user);
verify(!$reconciled['options']['invoice']&&!$reconciled['options']['autocreate']
	&&!$reconciled['options']['sendmail'] && $reconciled['profile']['features']===$c['features'],
	'F36-01 server-side Preview strips all CoreGuarded finance options even from stale/manipulated session');
foreach (array('invoice','autocreate','sendmail') as $feature) {
	$preview=profileState(); $preview['profile']=$b;
	$post=array('action'=>'confirm','revision'=>'0','common'=>'','description'=>'Synthetic',
		'start'=>array(1=>'2026-10-10'),'end'=>array(1=>'2027-10-09'),
		$feature=>'1');
	fails(static function()use($preview,$post){msmUpdatePreview($preview,$post);},
		'MSMProfileFeatureUnavailable');
}
verify(!$pd->writes&&!$pd->events && $pd->transaction_opened===0,
	'F36-01 feature/Preview/manipulated binding denial performs no SQL DML or BEGIN');
verify(!array_filter($pd->queries,static function($q){return stripos($q,'performance_schema')!==false;}),'CF01 CoreGuarded blocked without P_S fallback');
// An ordinary authorized admin may select CoreGuarded, but cannot use the
// profile settings route to grant unqualified financial options.
list($gateDb,$gateProfile)=profileStore('mysql',profileRaw('mysql','CoreGuarded'));
$gateBefore=$gateProfile->current();
$gateProfile->configure(array('token'=>'synthetic-csrf','action'=>'save','profile'=>'CoreGuarded',
	'revision'=>$gateBefore['revision']),array('token'=>'synthetic-csrf'),$admin,'POST');
$gateAfter=$gateProfile->current();
verify($gateDb->writes===1 && $gateAfter['profile']==='CoreGuarded'
	&& $gateAfter['features']['booking'] && !$gateAfter['features']['invoice']
	&& !$gateAfter['features']['autocreate'] && !$gateAfter['features']['sendmail'],
	'F36-01 normal admin profile configuration cannot lift the closed finance qualification gate');
// Admin authorization/CSRF/field/family checks execute before config mutation.
list($pd,$ps)=profileStore();$revision=$ps->current()['revision'];$session=array('token'=>'synthetic-csrf');
$post=array('token'=>'synthetic-csrf','action'=>'save','profile'=>'EnhancedMySQL','revision'=>$revision);
foreach(array('normal','external') as $case){$u=clone $admin;if($case==='normal'){$u->admin=0;}else{$u->socid=8;}
	$before=count($pd->queries);fails(static function()use($ps,$post,$session,$u){$ps->configure($post,$session,$u,'POST');},'NotEnoughPermissions');
	verify(count($pd->queries)===$before&&!$pd->writes,'CF01 admin denial before storage');}
foreach(array('token','method','entity','profile') as $field){$bad=$post;$method='POST';
	if($field==='token'){$bad['token']='wrong';}elseif($field==='method'){$method='GET';}elseif($field==='entity'){$bad['entity']=2;}else{$bad['profile']='MariaDBSnapshot';}
	fails(static function()use($ps,$bad,$session,$admin,$method){$ps->configure($bad,$session,$admin,$method);},$field==='profile'?'MSMProfileInvalid':'MSMInvalidForm');
	verify(!$pd->writes,'CF01 invalid admin input no write');}
$pd->autocommit='0';
fails(static function()use($ps,$post,$session,$admin){$ps->configure($post,$session,$admin,'POST');},'MSMProfileUnavailable');
verify(!$pd->writes&&!$pd->events,'CF01 no config write/commit into implicit admin context');$pd->autocommit='1';
$ps->configure($post,$session,$admin,'POST');$c=$ps->current();$oldBinding=$ps->binding($c,$user,2);
verify($pd->writes===1&&$c['profile']==='EnhancedMySQL'&&!$pd->events,'CF01 explicit legacy/new admin selection, config only');
fails(static function()use($ps,$post,$session,$admin){$ps->configure($post,$session,$admin,'POST');},'MSMProfileSaveConflict');
$newPost=$post;$newPost['revision']=$c['revision'];$ps->configure($newPost,$session,$admin,'POST');
verify($ps->current()['revision']!==$c['revision'],'CF01 same profile save rotates revision');
fails(static function()use($ps,$oldBinding,$user){$ps->admit($oldBinding,$user,array());},'MSMProfileChanged');
// Competing INSERT/UPDATE cannot overwrite the winning revision; no automatic retry.
foreach(array(null,profileRaw('mysql','EnhancedMySQL')) as $raw){
	list($pd,$ps)=profileStore('mysql',$raw);$c=$ps->current();$racePost=$post;$racePost['revision']=$c['revision'];
	$winner=profileRaw('mysql','CoreGuarded','b');$pd->onWrite=static function($db)use($winner){$db->profileRaw=$winner;};
	fails(static function()use($ps,$racePost,$session,$admin){$ps->configure($racePost,$session,$admin,'POST');},'MSMProfileSaveConflict');
	verify($pd->profileRaw===$winner&&!$pd->writes,'CF01 config CAS retains concurrent winner');
}
// Family, feature, entity, owner and confirmation revision are server-held.
list($pd,$ps)=profileStore('mysql',profileRaw('mysql','EnhancedMySQL'));$c=$ps->current();$b=$ps->binding($c,$user,3);
foreach(array('owner','entity','revision','profile','family','features','confirmation_revision') as $field){$bad=$b;$bad[$field]=$field==='features'?array():($field==='confirmation_revision'?4:'changed');
	fails(static function()use($ps,$bad,$user){$ps->admit($bad,$user,array(),3);},'MSMProfileChanged');}
fails(static function()use($ps,$user){$ps->admit(null,$user,array());},'MSMProfileChanged');
$entity2=new MsmTransactionProfile($pd,new SyntheticDatabaseState($pd),2);
fails(static function()use($entity2,$b,$user){$entity2->admit($b,$user,array(),3);},'MSMProfileChanged');
// Cached globals are intentionally wrong; fresh DB config determines the profile.
$conf->global->MEMBERSUBSCRIPTIONMASSACTION_TRANSACTION_PROFILE=profileRaw('mysql','CoreGuarded');
verify($ps->current()['profile']==='EnhancedMySQL','CF01 cached constants ignored');
// Read-only preview binding works with P_S denied, with zero BEGIN/SET/locks/probes.
$pd->psDenied=true;$before=count($pd->queries);$state=msmSyncPreviewProfile(profileState(),$ps,$user);
verify($state['phase']==='preview'&&$state['profile']['profile']==='EnhancedMySQL','CF01 preview available without physical readiness');
foreach(array_slice($pd->queries,$before) as $sql){verify(!preg_match('/BEGIN|COMMIT|ROLLBACK|SAVEPOINT|SET TRANSACTION|FOR UPDATE|performance_schema/i',$sql),'CF01 preview SQL is nonlocking read only');}
$state['phase']='confirm';$state['profile']['confirmation_revision']=$state['revision'];
$state['options']['invoice']=false;
$edited=msmEditConfirmation($state,array('action'=>'edit','revision'=>(string)$state['revision']));
$edited=msmSyncPreviewProfile($edited,$ps,$user);
verify($edited['revision']===$state['revision']+1&&!isset($edited['flash']),'CF01 ordinary edit preserves profile binding without false config-change warning');

$observer=new SyntheticDatabaseState($pd);$service=new BookingServiceDouble($pd);
fails(static function()use($state,$observer,$service,$ps,$user){msmRequireBookingDatabase($state,$observer,$service,$ps,$user);},'MSMEnhancedUnavailable');
verify(!$pd->events&&!$pd->writes,'CF01 configured Enhanced 1142 refuses, no fallback');
// Old sessions and changed configuration invalidate consent, never consumed results.
$old=profileState();$old['phase']='confirm';$old['revision']=7;
$sync=msmSyncPreviewProfile($old,$ps,$user);
verify($sync['phase']==='preview'&&$sync['revision']===8&&$sync['flash']==='MSMProfileChanged','CF01 old unbound confirmation needs fresh consent');
$pd->profileRaw=profileRaw('mysql','CoreGuarded','c');$sync=msmSyncPreviewProfile($state,$ps,$user);
verify($sync['phase']==='preview'&&$sync['revision']>$state['revision']&&$sync['profile_notice']==='' ,'CF01 changed admin config invalidates pending confirmation');
fails(static function()use($sync){msmConsumeConfirmation($sync,array('action'=>'book','revision'=>(string)$sync['revision']));},'MSMInvalidForm');
foreach(array('done','processing')as $phase){$done=$state;$done['phase']=$phase;$pd->configDenied=true;$before=count($pd->queries);
	verify(msmSyncPreviewProfile($done,$ps,$user)===$done&&count($pd->queries)===$before,'CF01 consumed result read bypasses profile storage');
	fails(static function()use($done){msmConsumeConfirmation($done,array('action'=>'book','revision'=>(string)$done['revision']));},'MSMInvalidForm');}
// Actual production profile storage/admission in Booking; no bypass through a direct call.
list($unused,$legacy,$row)=bookingFixture();
foreach(array('CoreGuarded','EnhancedMySQL','MariaDBSnapshot') as $name){
	$family=$name==='MariaDBSnapshot'?'mariadb':'mysql';list($pd,$ps)=profileStore($family,profileRaw($family,$name));
	$flow=new ProfileBookingFlow($pd);$flow->autoBindProfile=false;$flow->object=new BookingMemberDouble($pd);$flow->object->id=1;$flow->object->typeid=1;$flow->object->entity=1;
	$flow->reader=new BookingPreviewDouble();$flow->reader->row=$row;$confirmed=$row;$confirmed['_profile']=$ps->binding($ps->current(),$user,0);
	$result=$flow->book(1,$confirmed,$options,$user,false);
	verify($name==='CoreGuarded'?$result['message']==='MSMCoreGuardedSessionUnsupported'&&$result['stop']&&empty($pd->events):$result['status']==='success','CF01 direct booking admission '.$name);
	if($name!=='CoreGuarded'){
		$pd->events=array();$result=$flow->book(2,$row,$options,$user,false);
		verify($result['message']==='MSMProfileChanged'&&$result['stop']&&!$pd->events,'CF01 direct unbound booking refused');
	}
}
// A confirmed and fully authorized CoreGuarded Invoice plan still fails in
// the *real production Booking profile path*, before BEGIN or Core DML.
list($blockedDb,$blockedProfile)=profileStore('mysql',profileRaw('mysql','CoreGuarded'));
$blockedFlow=new ProfileBookingFlow($blockedDb); $blockedFlow->autoBindProfile=false;
$blockedFlow->object=new BookingMemberDouble($blockedDb);
$blockedFlow->object->id=1; $blockedFlow->object->typeid=1; $blockedFlow->object->entity=1;
$blockedFlow->reader=new BookingPreviewDouble(); $blockedFlow->reader->row=$row;
$blockedPlan=$row; $blockedPlan['invoice']=array('customer_id'=>4,'entity'=>1,'create_customer'=>false);
$blockedPlan['_profile']=$blockedProfile->binding($blockedProfile->current(),$user,0);
$blockedOptions=array('invoice'=>true,'autocreate'=>false,'sendmail'=>false,
	'description'=>'Synthetic');
$blockedResult=$blockedFlow->book(1,$blockedPlan,$blockedOptions,$user,false);
verify($blockedResult['message']==='MSMProfileFeatureUnavailable'
	&& $blockedResult['stop'] && !$blockedDb->events
	&& $blockedDb->transaction_opened===0 && $blockedDb->saved===null,
	'F36-01 production Booking rejects otherwise valid CoreGuarded invoice before BEGIN');
foreach (array('autocreate','sendmail') as $feature) {
	$forgedOptions=$blockedOptions; $forgedOptions[$feature]=true;
	$blockedResult=$blockedFlow->book(1,$blockedPlan,$forgedOptions,$user,false);
	verify($blockedResult['message']==='MSMProfileFeatureUnavailable'
		&& $blockedResult['stop'] && !$blockedDb->events,
		'F36-01 production Booking rejects '.$feature.' even with invoice and full rights');
}
// The fresh per-member SELECT is the admission snapshot. Admin may change later;
// this admitted member finishes, but the next member cannot inherit the old profile.
list($pd,$ps)=profileStore('mysql',profileRaw('mysql','EnhancedMySQL'));$confirmed=$row;$confirmed['_profile']=$ps->binding($ps->current(),$user,0);
$flow=new ProfileBookingFlow($pd);$flow->autoBindProfile=false;$flow->object=new BookingMemberDouble($pd);$flow->object->id=1;$flow->object->typeid=1;$flow->object->entity=1;$flow->reader=new BookingPreviewDouble();$flow->reader->row=$row;
$pd->onRead=static function($db){$db->profileRaw=profileRaw('mysql','CoreGuarded','d');};
$first=$flow->book(1,$confirmed,$options,$user,false);$count=count($pd->events);$second=$flow->book(2,$confirmed,$options,$user,false);
verify($first['status']==='success'&&$second['message']==='MSMProfileChanged'&&$second['stop']&&count($pd->events)===$count,'CF01 per-member linearization, no mid-transaction switch or later BEGIN');
// Controller's only physical-readiness call remains inside the book action.
$controller=file_get_contents(__DIR__.'/../../subscription.php');
verify(substr_count($controller,'msmRequireBookingDatabase(')===1,'CF01 controller readiness only on final booking');
// F24-03: execute the actual controller's session-admission block, replacing only
// its terminal HTTP denial with an exception. No bootstrap, HTTP or duplicated flow.
$start=strpos($controller,'// Reject invalid POST tokens before any module preview/session mutation.');
$end=strpos($controller,"\n".'if (in_array($state[\'phase\']',$start===false?0:$start);
verify($start!==false&&$end!==false&&$end>$start,'F24 controller admission block located');
$admission=substr($controller,$start,$end-$start);
$admission=str_replace('accessforbidden(', '$deny(', $admission);
verify(substr_count($controller,'msmValidCsrf(')===1,'F24 one controller CSRF check');
list($pd,$ps)=profileStore('mysql',profileRaw('mysql','EnhancedMySQL'));
$stale=profileState();$stale['revision']=3;$stale['phase']='confirm';
$stale['profile']=$ps->binding($ps->current(),$user,3);
$pd->profileRaw=profileRaw('mysql','EnhancedMySQL','e');
$previewKey=str_repeat('f',32);
$invoke=static function($method,$token,$session)use($admission,$previewKey,$ps,$user){
	global $conf,$langs;
	$_POST=array('preview'=>$previewKey,'token'=>$token);$_GET=array('preview'=>$previewKey);$_SESSION=$session;
	$profiles=$ps;
	$deny=static function(...$args){throw new RuntimeException('F24TestAccessDenied');};
	eval($admission);
};
$savedGlobals=array($_SESSION,$_POST??array(),$_GET??array());
try {
	foreach(array('preview','confirm','processing','done') as $phase){
	 $stored=$stale;$stored['phase']=$phase;
	 $session=array('token'=>'synthetic-csrf','msm_previews'=>array($previewKey=>$stored),'unrelated'=>'preserved');
	 $before=count($pd->queries);
	 fails(static function()use($invoke,$session){$invoke('POST','invalid',$session);},'F24TestAccessDenied');
	 verify($_SESSION===$session&&count($pd->queries)===$before,'F24 invalid POST rejects stale '.$phase.' before profile reads/session mutation');
	}
	foreach(array('POST','GET') as $method){
	 $session=array('token'=>'synthetic-csrf','msm_previews'=>array($previewKey=>$stale));
	 $invoke($method,$method==='POST'?'synthetic-csrf':null,$session);
	 $synced=$_SESSION['msm_previews'][$previewKey];
	 verify($synced['phase']==='preview'&&$synced['revision']===4&&$synced['flash']==='MSMProfileChanged','F24 valid '.$method.' retains stale-binding invalidation');
	}
	foreach(array('processing','done') as $phase){
	 $stored=$stale;$stored['phase']=$phase;
	 $session=array('token'=>'synthetic-csrf','msm_previews'=>array($previewKey=>$stored));$before=count($pd->queries);
	 $invoke('POST','synthetic-csrf',$session);
	 verify($_SESSION===$session&&count($pd->queries)===$before,'F24 valid consumed '.$phase.' unchanged by admission');
	}
} finally {list($_SESSION,$_POST,$_GET)=$savedGlobals;}
echo ($passed-$profileBaseline)." CF01 profile/configuration/readiness assertions passed (synthetic only; no real DB/HTTP).\n";
