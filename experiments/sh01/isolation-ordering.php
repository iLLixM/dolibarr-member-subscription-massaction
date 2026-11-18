<?php
/* Exact native SQL prefix experiment, not full Core integration. */
require __DIR__.'/lab.php';
if(PHP_SAPI!=='cli'||($argv[1]??'')!=='--run-approved'||empty($argv[2])||file_exists($argv[2]))exit(77);
$l=new ShLab();$tests=array();$failure=null;
try {
 foreach(array('engine-metadata','ordinary-InnoDB-read') as $i=>$kind){
  $l->case='POC-07-prefix-'.$kind;$writer=$l->connect();$id=192+$i;$l->fixture($writer,$id);$d=$l->connect();
  $l->q($d,'SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');$g=new ShMarkerHypothesis($l,$d);$l->require($g->prepare(),'Prepare failed');
  if($i===0)$l->q($d,"SELECT ENGINE AS engine FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='probe'",'core-prefix-model');
  else $l->q($d,'SELECT n FROM probe WHERE id='.$id,'core-prefix-control');
  $l->q($d,'BEGIN');$ok=$g->beginEvidence(1);$first=$l->value($d,$id);$l->q($writer,'UPDATE probe SET n=1 WHERE id='.$id,'independent-writer');$second=$l->value($d,$id);$l->require($ok&&$first===0&&in_array($second,array(0,1),true),'Invalid read experiment');
  $tests[]=array('id'=>$l->case,'measurement'=>'PASS (observation, not safety acceptance)','fixture'=>$id,'session_default'=>$l->meta($d)['session_isolation'],'first'=>$first,'second'=>$second,'repeatable_read_behavior'=>$second===0,'prefix'=>$kind,'scope'=>'SQL-shape Core prefix model, not a bootstrapped Dolibarr integration');$l->close($d);$l->close($writer);
 }
}catch(Throwable $e){$failure=array('class'=>get_class($e),'errno'=>$e->getCode(),'assertion'=>$e instanceof LogicException?$e->getMessage():null);}
$r=array('tests'=>$tests,'failure'=>$failure,'trace'=>$l->trace);file_put_contents($argv[2],json_encode($r,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n");echo json_encode(array('tests'=>$tests,'failure'=>$failure))."\n";exit($failure?1:0);
