#!/usr/bin/env python3
"""Owner-operated protected HTTP/session proof. No credential/session output or storage.
Temporary www-data PHP server on loopback; genuine I105 authentication, new lab rows.
"""
# SPDX-License-Identifier: GPL-3.0-or-later
import getpass
import http.cookiejar
from html.parser import HTMLParser
import json
import os
from pathlib import Path
import subprocess
import sys
import time
import urllib.error
import urllib.parse
import urllib.request

HERE = Path(__file__).resolve().parent
class Stop(RuntimeError): pass
class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl): return None
class Fields(HTMLParser):
    def __init__(self): super().__init__(); self.fields = {}
    def handle_starttag(self, tag, attrs):
        a = dict(attrs)
        if tag == 'input' and a.get('name') and a.get('type') == 'hidden':
            self.fields[a['name']] = a.get('value', '')

def main():
    if sys.argv[1:] != ['--run-approved']: raise Stop('Use --run-approved once')
    marker = HERE / 'evidence/HTTP-OWNER-R1.started'
    if marker.exists(): raise Stop('HTTP already attempted; preserve state, do not rerun')
    cpath = Path(os.environ.get('AP3_CONFIG', str(Path.home() / '.config/dolibarr-sh01/app.json')))
    if cpath.stat().st_mode & 0o077: raise Stop('Protected lab app config required')
    lab = json.loads(cpath.read_text())
    if (lab['host'], lab['port'], lab['database'], lab['user']) != ('127.0.0.1',3307,'msmsh01lab','msmsh01lab'):
        raise Stop('Restricted lab identity mismatch')
    prior=json.loads((HERE/'evidence/HTTP-OWNER-result.json').read_text())
    if prior['cases'] or prior['failure']['stage']!='genuine-authentication':raise Stop('Unexpected prior progress; no continuation')
    password = getpass.getpass('Existing I105 Dolibarr password for msm-i105 (never printed): ')
    marker.open('x').write(json.dumps({'mode':'Owner-authorized temporary protected authenticated HTTP/lab proof','no_rerun':True})+'\n')
    proc = None; cases = []; failure = None; ready = None; collected = {}; stopped = None; stage = 'owner-bridge'
    def save(name, data):
        with (HERE / 'evidence' / name).open('x') as f: json.dump(data, f, indent=2); f.write('\n')
    try:
        proc = subprocess.Popen(['sudo','-u','www-data','/usr/bin/php','-d','display_errors=0','-d','log_errors=0',str(HERE/'owner-server-r1.php'),'--serve-approved'],stdin=subprocess.PIPE,stdout=subprocess.PIPE,text=True)
        def ipc(obj):
            proc.stdin.write(json.dumps(obj)+'\n');proc.stdin.flush()
            line = proc.stdout.readline()
            if not line: raise Stop('Owner bridge closed; no retry')
            return json.loads(line)
        ready = ipc({'lab':lab});del lab
        if ready.get('status') != 'READY': raise Stop('Protected bridge inspection failed')
        base = 'http://127.0.0.1:'+str(ready['port']); route = ready['route']
        jar = http.cookiejar.CookieJar();opener = urllib.request.build_opener(urllib.request.ProxyHandler({}),NoRedirect(),urllib.request.HTTPCookieProcessor(jar))
        def request(query='', data=None):
            url = base+route+query
            headers = {'Host':'i105-v24.localhost:8091','Referer':'http://i105-v24.localhost:8091'+route,'Connection':'close'}
            req = urllib.request.Request(url,urllib.parse.urlencode(data).encode() if data is not None else None,headers)
            try:
                with opener.open(req,timeout=25) as response: return response.status,response.read(1000000)
            except urllib.error.HTTPError as e: return e.code,e.read(1000000)
        # Read-only server readiness only; never retry a business/test POST.
        stage = 'bootstrap-readiness'
        for attempt in range(30):
            try: status,body = request();break
            except urllib.error.URLError:
                if proc.poll() is not None: raise Stop('Temporary server exited')
                time.sleep(.1)
        else: raise Stop('Temporary server not reachable')
        stage = 'genuine-authentication'
        if status!=200:raise Stop('Bootstrap HTTP status not200; no login/test POST')
        parser = Fields();parser.feed(body.decode('utf-8','replace'))
        if 'token' not in parser.fields: raise Stop('Core login form not found; no test POST executed')
        login = dict(parser.fields,username='msm-i105',password=password,actionlogin='login')
        status,body = request(data=login);del login,password
        status,body = request('?ap3_action=inspect')
        profile = json.loads(body)
        if profile.get('status') != 'AUTHENTICATED': raise Stop('Core authentication not confirmed')
        save('HTTP-R1-profile.json',{'status':'PASS','profile':profile['profile'],'auth':'genuine protected main.inc.php; credential/cookie/token not retained','transport':'ephemeral loopback PHP cli-server; Apache I105 session path mirrored; not Apache request SAPI'})
        names=['HTTP_'+mode+'_'+end for mode in ('NO_CHAIN','CHAIN','RELEASE') for end in ('COMMIT','ROLLBACK')]
        names += ['HTTP_CHECKPOINT_FAILURE','HTTP_UNKNOWN_CLOSE','HTTP_UNREADABLE']
        for name in names:
            stage = name
            status,body = request('?ap3_action=inspect');inspect = json.loads(body)
            token = inspect['csrf']
            status,body = request(data={'ap3_action':'run','case':name,'token':token});del token
            result = json.loads(body)
            if status != 200 or result.get('status') != 'MEASURED': raise Stop('HTTP case stopped; preserve state')
            status,body = request('?ap3_action=result&case='+name);readback = json.loads(body)
            if status != 200 or readback.get('status') != 'READ_ONLY_RESULT': raise Stop('Stored result not readable')
            stored = readback['stored']
            expected = result['result']['results'];first = next(iter(expected.values()))
            if name == 'HTTP_CHECKPOINT_FAILURE':
                if first['member']!='SUCCESS' or first['batch']!='STOP' or first['checkpoint'] is not False or stored['phase']!='processing' or not stored['consumed']:
                    raise Stop('E15/consumed checkpoint-failure mismatch')
            else:
                for fixture,outcome in expected.items():
                    actual=stored['results'][fixture]
                    if actual['member']!=outcome['member']: raise Stop('Durable member result mismatch')
                if stored['phase']!='done': raise Stop('Expected durable done state')
            # Reuse only this already consumed case. Fresh CSRF permits testing replay guard itself.
            status,body=request('?ap3_action=inspect');token=json.loads(body)['csrf']
            status,body=request(data={'ap3_action':'run','case':name,'token':token});del token
            replay=json.loads(body)
            if replay.get('status')!='CONSUMED' or replay.get('lab_connections')!=0: raise Stop('Consumed replay was not rejected before lab access')
            cases.append({'id':name,'status':'PASS','first_member':first['member'],'batch':result['result']['batch'],'readback':stored,'replay_rejected_before_lab':True})
            print(json.dumps({'case':name,'status':'PASS','member':first['member'],'batch':result['result']['batch']}),flush=True)
        stage='collect-and-stop'
    except BaseException as e:
        failure={'stage':stage,'class':type(e).__name__,'reason':str(e) if isinstance(e,Stop) else 'Runtime failure; preserve state, no rerun'}
    finally:
        if proc is not None:
            try:
                if ready and ready.get('status')=='READY':
                    collected=ipc({'op':'collect'})
                    for name,data in (collected.get('evidence') or {}).items():
                        if not name.endswith('.json') or '/' in name: raise Stop('Unsafe evidence basename')
                        save(name,data)
                    stopped=ipc({'op':'stop'})
                    save('HTTP-R1-reference-after.json',stopped)
                    if stopped.get('status')!='PASS' or not stopped.get('reference_business_rows_equal'):
                        failure=failure or {'stage':'reference-readback','reason':'Reference business rows changed; no automatic repair'}
                proc.stdin.close();proc.wait(timeout=10)
            except BaseException:
                failure=failure or {'stage':'bridge-finalization','reason':'Could not confirm server stop/reference readback; owner reconciliation required'}
                try:
                    if proc.poll() is None and ready and ready.get('status')=='READY':
                        stopped=ipc({'op':'stop'})
                        save('HTTP-R1-reference-finally.json',stopped)
                except BaseException:pass
                if proc.poll() is None: proc.terminate();proc.wait(timeout=5)
        data={'status':'STOP' if failure else 'PASS','cases':cases,'failure':failure,'session_tokens_logged':False,'real_business_operations':0,'reference_business_equal':stopped.get('reference_business_rows_equal') if stopped else None,'temporary_server_stopped':stopped.get('server_terminated') if stopped else None}
        save('HTTP-OWNER-R1-result.json',data)
        print(json.dumps({'status':data['status'],'cases':len(cases),'failure':failure,'server_stopped':data['temporary_server_stopped'],'evidence':str(HERE/'evidence/HTTP-OWNER-R1-result.json')}),flush=True)
    return 1 if failure else 0
if __name__=='__main__':
    try: raise SystemExit(main())
    except Stop as e: print(json.dumps({'status':'STOP','reason':str(e),'action':'Do not rerun automatically'}));raise SystemExit(1)
