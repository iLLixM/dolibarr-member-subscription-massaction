#!/usr/bin/env python3
"""Read-only reconciliation of a mistaken diagnostic expectation; no SQL, no retry."""
# SPDX-License-Identifier: GPL-3.0-or-later
import hashlib
import json
from pathlib import Path
import sys
source, target = map(Path, sys.argv[1:])
r = json.loads(source.read_text())
if target.exists():
    raise SystemExit('Prior reconciliation exists; preserve it')
t = r['trace']
assert r['status'] == 'STOP' and r['failure']['assertion'] == 'Diagnostics profile unexpected'
assert t[2]['result'][0]['tracking'] == 'CHARACTERISTICS'
commits = [int(x['result'][0]['Value']) for x in t if x['sql'] == "SHOW SESSION STATUS LIKE 'Com_commit'"]
assert commits == [0, 1, 2]
assert t[-3]['errno'] == 1305 and t[-2]['errno'] == 1227
assert t[-1]['sql'].startswith('SELECT VARIABLE_VALUE FROM performance_schema.session_status')
assert t[-1]['result'] == []
result = {'status': 'RECONCILED MEASUREMENT', 'source_sha256': hashlib.sha256(source.read_bytes()).hexdigest(),
          'original_STOP_preserved': True, 'sql_reexecution': False,
          'mistake': 'Original helper incorrectly required errno1142 for P_S session_status; actual query succeeded with no matching rows.',
          'actual': {'session_tracking_setting': 'CHARACTERISTICS', 'idle_commit_counter_increment': 1,
                     'active_commit_counter_increment': 1, 'outside_savepoint_create_ack': True,
                     'outside_release_errno': 1305, 'innodb_trx_errno': 1227, 'ps_session_status_rows': []},
          'scope': 'This does not grant access to events_transactions_current; its denial1142 was separately verified. No physical transaction identity or isolation obtained.'}
target.write_text(json.dumps(result, indent=2)+'\n')
print(json.dumps(result))
