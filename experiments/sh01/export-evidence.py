#!/usr/bin/env python3
"""Export only allowlisted artificial-lab evidence; redact marker nonces, never export config."""
# SPDX-License-Identifier: GPL-3.0-or-later
import hashlib
import json
from pathlib import Path
import re
import sys
root = Path(__file__).resolve().parents[2]
raw = root/'build-output/sh01-evidence'
dest = root/'experiments/sh01/evidence'
names = ['native.json','lost-ack-COMMIT.json','lost-ack-ROLLBACK.json','isolation-ordering.json',
         'rollback-to.json','session-diagnostics.json','session-diagnostics-reconciled.json',
         'source-audit.json','inspect-final.json','reference.json','final-lab-read.json',
         'existing-database-synthetic.log']
markers = {}
origins = []
def digest(b):return hashlib.sha256(b).hexdigest()
def replace(m):
    value = m.group(0)
    if value not in markers:
        markers[value] = 'sh01_REDACTED_MARKER_'+str(len(markers)+1).zfill(3)
    return markers[value]
for name in names:
    data = (raw/name).read_bytes()
    text = re.sub(r'\bsh01_[0-9a-f]{32}\b', replace, data.decode())
    out = text.encode()
    (dest/name).write_bytes(out)
    origins.append({'file':name,'raw_sha256':digest(data),'review_sha256':digest(out),
                    'transform':'Experimental savepoint nonce identifiers normalized; all other fields preserved' if out!=data else 'byte-identical'})
owner = Path.home()/'dolibarr-sh01-20261118/provision-result.json'
data = owner.read_bytes();json.loads(data)
(dest/'owner-provision-result.json').write_bytes(data)
origins.append({'file':'owner-provision-result.json','raw_sha256':digest(data),'review_sha256':digest(data),
                'transform':'byte-identical Owner-executed provisioning report; not an independent global DB audit'})
(dest/'EVIDENCE-ORIGINS.json').write_text(json.dumps({'sources':origins,'raw_directory':'build-output/sh01-evidence (local, ignored)',
       'credential_exports':0,'session_or_auth_packet_exports':0,'marker_identifiers_redacted':len(markers)},indent=2)+'\n')
r=json.loads((dest/'native.json').read_text());assert r['failure'] is None and len(r['tests'])==23
cases=[]
for t in r['tests']:
    cases.append({'id':t['id'],'precondition':t['precondition'],'actions':[x for x in r['trace'] if x['case']==t['id']],
                  'expected':t['expected'],'actual':t['actual'],'measurement':t['measurement'],'security_assessment':t['security_contract'],
                  'evidence':'native.json'})
for name in ['lost-ack-COMMIT.json','lost-ack-ROLLBACK.json','rollback-to.json']:
    t=json.loads((dest/name).read_text());assert t['measurement']=='PASS'
    cases.append({'id':t['id'],'precondition':t.get('precondition','Fresh dedicated connection, restricted artificial lab'),
                  'actions':t['trace'],'expected':t['expected'],'actual':t['actual'],'measurement':t['measurement'],
                  'security_assessment':t['security_contract'],'evidence':name})
t=json.loads((dest/'isolation-ordering.json').read_text());assert t['failure'] is None
for c in t['tests']:
    cases.append({'id':c['id'],'precondition':'Fresh restricted connection; session default READ COMMITTED; one-shot RR before tested prefix',
                  'actions':[x for x in t['trace'] if x['case']==c['id']],
                  'expected':'Measure RR persistence across the pre-BEGIN query, not assume it',
                  'actual':c,'measurement':'PASS (measurement only)',
                  'security_assessment':'FAIL for this insertion point: second consistent read changes after independent commit; not RR behavior',
                  'evidence':'isolation-ordering.json'})
assert len(cases)==28 and len({c['id'] for c in cases})==28
families={re.match(r'POC-\d+',c['id'])[0] for c in cases};assert families=={'POC-'+str(i).zfill(2) for i in range(1,12)}
summary={'decision':'NO-GO for SH-02 based on the tested construction under unchanged safety contracts; no universal impossibility theorem',
         'native_measurements':28,'mandatory_groups':11,'actual_Core_business_integration':'NOT EXECUTED',
         'synthetic_regression':'188 existing observer assertions PASS; counter nesting explicitly modeled',
         'diagnostics':'Original expectation STOP retained, reconciled from recorded trace without SQL rerun',
         'cases':cases}
(dest/'TEST-MATRIX.json').write_text(json.dumps(summary,indent=2)+'\n')
print(json.dumps({'exported_raw_evidence_files':len(names)+1,'native_subcases':28,'mandatory_groups':len(families),
                  'normalized_markers':len(markers),'decision':summary['decision']}))
