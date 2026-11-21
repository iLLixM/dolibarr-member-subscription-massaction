<?php
/* Copyright (C) 2026 iLLixM <iLLixM>
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
if(PHP_SAPI!=='cli'){exit(64);}
require_once __DIR__.'/../../tools/cf02-contribution-witness.lib.php';
$pass=0;
function wOk($cond,$reason){global $pass;if(!$cond){throw new RuntimeException($reason);}$pass++;}
function wDeny($f,$reason){try{$f();}catch(RuntimeException $e){wOk(strpos($e->getMessage(),'CF02')===0,$reason);return;}throw new RuntimeException('Not denied: '.$reason);}
function wMake($agenda) {
 $e=Cf02ContributionWitnessContract::parameters('271','CF02LAB-ABCDEF12','2080-01-01','2080-12-31','120.00',$agenda,'MEM2611-0271');
 $b=array('identity'=>array('schema'=>'cf03_mysql_fixture','account'=>'cf03_restricted_fixture@127.0.0.1',
  'version'=>'8.4.13','autocommit'=>'1','completion'=>'NO_CHAIN'),
  'profile'=>array('family'=>'mysql','profile'=>'CoreGuarded','raw_hash'=>str_repeat('a',64)),
  'member'=>array('id'=>271,'ref'=>'MEM2611-0271','login'=>null,'firstname'=>'Synthetic','lastname'=>'CF02LAB-ABCDEF12',
    'email_hash'=>hash('sha256','cf02lab-abcdef12@example.invalid'),'entity'=>1,'type_id'=>19,
    'fk_soc'=>0,'status'=>1,'datefin'=>null),
  'type'=>array('id'=>19,'status'=>1,'amount'=>'120.00','duration'=>'1y'),
  'agenda_enabled'=>$agenda==='on',
  'engines'=>array('adherent'=>'InnoDB','adherent_type'=>'InnoDB','subscription'=>'InnoDB',
    'actioncomm'=>'InnoDB','actioncomm_resources'=>'InnoDB'),
  'subscriptions'=>array(),'agenda_events'=>array(),'agenda_resources'=>array(),
  'counts'=>array_fill_keys(Cf02ContributionWitnessContract::COUNTS,10),
  'source_hashes'=>array('booking'=>str_repeat('c',64)));
 $a=$b;$a['member']['datefin']='2080-12-31 00:00:00';
 $a['subscriptions']=array(array('id'=>128,'member_id'=>271,'type_id'=>19,'amount'=>'120.00',
   'start'=>'2080-01-01 00:00:00','end'=>'2080-12-31 00:00:00','note_contains_marker'=>true));
 $a['counts']['subscription']++;
 if($agenda==='on'){
  $a['counts']['actioncomm']++;$a['counts']['actioncomm_resources']++;
  $a['agenda_events']=array(array('id'=>73,'code'=>'AC_MEMBER_SUBSCRIPTION_CREATE','member_id'=>271));
  $a['agenda_resources']=array(array('id'=>89,'agenda_id'=>73,'element_type'=>'user','actor_id'=>4));
 }
 return array($e,$b,$a);
}
foreach(array('on','off') as $agenda){
 list($e,$before,$after)=wMake($agenda);
 Cf02ContributionWitnessContract::assertBaseline($before,$e);
 wOk(Cf02ContributionWitnessContract::assertPositive($before,$after,$e)['status']==='PASS','valid '.$agenda);
 foreach(array('member.ref'=>'MEM2611-9999','member.fk_soc'=>20,'type.amount'=>'121.00',
   'profile.profile'=>'EnhancedMySQL','identity.completion'=>'CHAIN',
   'engines.subscription'=>'MyISAM') as $field=>$value){
   $wrong=$before;list($outer,$inner)=explode('.',$field);
   $wrong[$outer][$inner]=$value;
   wDeny(static function() use($wrong,$e){Cf02ContributionWitnessContract::assertBaseline($wrong,$e);},'refuse '.$field);
 }
 $withLogin=$before;$withLogin['member']['login']='generated-by-core';
 Cf02ContributionWitnessContract::assertBaseline($withLogin,$e);
 wOk(true,'optional core login accepted');
 $wrong=$before;$wrong['subscriptions']=array(array('id'=>1));
 wDeny(static function()use($wrong,$e){Cf02ContributionWitnessContract::assertBaseline($wrong,$e);},'prior subscription');
 foreach(array('counts.facture','counts.societe','counts.subscription',
   'member.datefin','subscriptions.0') as $which){
   $wrong=$after;
   if($which==='counts.facture'){$wrong['counts']['facture']++;}
   elseif($which==='counts.societe'){$wrong['counts']['societe']++;}
   elseif($which==='counts.subscription'){$wrong['counts']['subscription']++;}
   elseif($which==='member.datefin'){$wrong['member']['datefin']='2080-12-30 00:00:00';}
   else{$wrong['subscriptions'][0]['note_contains_marker']=false;}
   wDeny(static function()use($before,$wrong,$e){Cf02ContributionWitnessContract::assertPositive($before,$wrong,$e);},'postcondition '.$which);
 }
 $wrong=$after;$wrong['source_hashes']['booking']=str_repeat('e',64);
 wDeny(static function()use($before,$wrong,$e){Cf02ContributionWitnessContract::assertPositive($before,$wrong,$e);},'source drift');
 if($agenda==='on'){
  $wrong=$after;$wrong['agenda_resources'][0]['actor_id']=0;
  wDeny(static function()use($before,$wrong,$e){Cf02ContributionWitnessContract::assertPositive($before,$wrong,$e);},'actor missing');
 }else{
  $wrong=$after;$wrong['agenda_events']=array(array('id'=>7));
  wDeny(static function()use($before,$wrong,$e){Cf02ContributionWitnessContract::assertPositive($before,$wrong,$e);},'unexpected agenda');
 }
}
foreach(array(
 array('0','CF02LAB-ABCDEF12','2080-01-01','2080-12-31','120.00','on','MEM2611-0271'),
 array('1','NORMAL-MEMBER','2080-01-01','2080-12-31','120.00','on','MEM2611-0271'),
 array('1','CF02LAB-ABCDEF12','2080-02-30','2080-12-31','120.00','on','MEM2611-0271'),
 array('1','CF02LAB-ABCDEF12','2080-01-01','2080-12-31','-10','on','MEM2611-0271'),
 array('1','CF02LAB-ABCDEF12','2080-01-01','2080-12-31','120.00','else','MEM2611-0271'),
 array('1','CF02LAB-ABCDEF12','2080-01-01','2080-12-31','120.00','on','invalid reference')
) as $args){
 wDeny(static function()use($args){call_user_func_array(array('Cf02ContributionWitnessContract','parameters'),$args);},'invalid parameters');
}
echo 'CF02 contribution witness: '.$pass.' synthetic assertions PASS (no database/HTTP).'.PHP_EOL;
