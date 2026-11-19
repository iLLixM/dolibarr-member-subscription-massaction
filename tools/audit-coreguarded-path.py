#!/usr/bin/env python3
"""Read-only pinned-source audit. No PHP/Core bootstrap, application config or DB."""
# SPDX-License-Identifier: GPL-3.0-or-later
import argparse
import hashlib
import json
from pathlib import Path
import re
import subprocess
PIN = '5c6c7ff6a5112413b1c27221c8d11750afad56f8'
METHODS = {
 'adherents/class/adherent.class.php':['subscription','update_end_date'],
 'adherents/class/subscription.class.php':['create'],
 'core/class/commontrigger.class.php':['call_trigger'],
 'core/class/interfaces.class.php':['run_triggers'],
 'core/triggers/interface_50_modAgenda_ActionsAuto.class.php':['runTrigger'],
 'comm/action/class/actioncomm.class.php':['create'],
 'core/class/commonobject.class.php':['insertExtraFields'],
 'core/triggers/interface_20_all_Logevents.class.php':['runTrigger'],
 'core/triggers/interface_50_modNotification_Notification.class.php':['runTrigger'],
 'core/triggers/interface_99_modBlockedlog_ActionsBlockedLog.class.php':['runTrigger'],
 'core/db/DoliDB.class.php':['begin','commit','rollback'],
 'core/modules/syslog/mod_syslog_file.php':['export']}
# Tokenize source as data. Count code braces including interpolation, ignoring
# comment/string-token braces. No include, eval, callback or application execution.
TOKENIZER = r'''
$t=token_get_all(stream_get_contents(STDIN));$out=[];
for($i=0;$i<count($t);$i++){
 if(!is_array($t[$i])||$t[$i][0]!==T_FUNCTION)continue;
 $start=$t[$i][2];$name=null;$body='';$depth=0;$begun=false;
 for($j=$i+1;$j<count($t);$j++){
  $v=$t[$j];$txt=is_array($v)?$v[1]:$v;
  if(!$begun&&is_array($v)&&$v[0]===T_STRING&&$name===null)$name=$txt;
  if(!is_array($v)&&$v==='{'){$depth++;$begun=true;}
  elseif($begun&&is_array($v)&&in_array($v[0],[T_CURLY_OPEN,T_DOLLAR_OPEN_CURLY_BRACES],true))$depth++;
  if($begun)$body.=$txt;
  if(!is_array($v)&&$v==='}'&&--$depth===0&&$begun)break;
  if(!$begun&&!is_array($v)&&$v===';')break;
 }
 if($name&&$begun)$out[$name]=['line'=>$start,'body'=>$body];
}echo json_encode($out);
'''
def main():
 a=argparse.ArgumentParser();a.add_argument('--core-root',type=Path,required=True);a.add_argument('--output',type=Path,required=True);args=a.parse_args()
 assert not args.output.exists(),'Never overwrite evidence'
 files=[]
 for relative,names in METHODS.items():
  data=(args.core_root/'htdocs'/relative).read_bytes()
  assert data==subprocess.check_output(['git','-C',str(args.core_root),'show',PIN+':htdocs/'+relative]),'Pinned source mismatch: '+relative
  methods=json.loads(subprocess.check_output(['php','-r',TOKENIZER],input=data));selected={}
  for name in names:
   m=methods[name];body=m['body']
   selected[name]={'line':m['line'],'body_sha256':hashlib.sha256(body.encode()).hexdigest(),
    'direct_begin':len(re.findall(r'->db->begin\s*\(',body)),
    'direct_commit':len(re.findall(r'->db->commit\s*\(',body)),
    'direct_rollback':len(re.findall(r'->db->rollback\s*\(',body)),
    'completion_type_reference':bool(re.search('completion_type',body,re.I))}
   assert not selected[name]['completion_type_reference'],relative+'::'+name
  files.append({'path':'htdocs/'+relative,'bytes':len(data),'sha256':hashlib.sha256(data).hexdigest(),'methods':selected})
 result={'kind':'STATIC SOURCE AUDIT ONLY','Core':PIN,'dolibarr':'24.0.3','files':files,'runtime_configuration':'NOT INSPECTED','new_real_contribution_Agenda_test':'NOT EXECUTED','claim':'No direct completion_type reference in selected pinned methods. Dynamic callback closure requires the report configuration and independent review. No physical identity guarantee.'}
 args.output.write_text(json.dumps(result,indent=2)+'\n');print(json.dumps({'status':'PASS','pinned_files':len(files),'database_connections':0,'kind':result['kind']}))
if __name__=='__main__':main()
