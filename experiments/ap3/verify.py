#!/usr/bin/env python3
"""Offline AP3 artifact, SQL-order and independent persistence cross-checks. No DB/HTTP."""
# SPDX-License-Identifier: GPL-3.0-or-later
import hashlib
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
LAB = ROOT / 'experiments/ap3'
EVIDENCE = LAB / 'evidence'

def read(name):
    return json.loads((EVIDENCE / name).read_text())

def sha(path):
    return hashlib.sha256(path.read_bytes()).hexdigest()

def trace_check(events, terminal):
    assert not any(e['event'] == 'forbidden_sql_attempt' for e in events)
    sql = [e for e in events if e['event'] == 'sql' and e['role'] == 'lab']
    for i, e in enumerate(sql):
        if e['sql'] == 'BEGIN':
            assert i and sql[i-1]['sql'] == 'SET TRANSACTION ISOLATION LEVEL REPEATABLE READ'
            assert sql[i-1]['ok'] and sql[i-1]['connection'] == e['connection']
    ends = [i for i,e in enumerate(events) if e['event'] == 'physical_end_ack']
    if terminal and ends:
        after = events[ends[0]+1:]
        assert not any(e['event'] == 'sql' and e['role'] == 'lab' for e in after)
    decisions = [e for e in events if e['event'] == 'end_decision']
    if terminal:
        assert all(e['batch'] == 'STOP' for e in decisions)
    # Independent auditors must not reuse the terminal connection.
    lab_ids = {e['connection'] for e in sql}
    auditor_ids = {e['connection'] for e in events if e['event'] == 'sql' and e['role'] == 'independent'}
    assert not lab_ids & auditor_ids

def main():
    manifest = json.loads((LAB / 'MANIFEST.json').read_text())
    paths = [i['path'] for i in manifest['files']]
    assert len(set(paths)) == len(paths)
    actual = sorted(p.relative_to(ROOT).as_posix() for p in LAB.rglob('*') if p.is_file()
                    and '__pycache__' not in p.parts and p.name not in ('MANIFEST.json','SHA256SUMS'))
    actual.append('docs/ap3-completion-gate-v1.md')
    assert sorted(paths) == sorted(actual), 'Exact artifact completeness'
    for item in manifest['files']:
        assert not Path(item['path']).is_absolute() and '..' not in Path(item['path']).parts
        p = ROOT / item['path']
        assert p.stat().st_size == item['bytes'] and sha(p) == item['sha256'], item['path']
    sums = {}
    for line in (LAB / 'SHA256SUMS').read_text().splitlines():
        digest,name = line.split('  ',1)
        assert name not in sums and sha(ROOT / name) == digest, name
        sums[name] = digest
    assert set(sums) == set(paths) | {'experiments/ap3/MANIFEST.json'}
    inspect = read('inspection.json')
    assert inspect['P_S_denied'] == 1142 and inspect['baseline_rows'] == 81
    assert inspect['mysql'] == '8.4.13' and inspect['php'] == '8.2.35'
    cli = read('cli-r1.json')
    assert cli['status'] == 'PASS' and len(cli['cases']) == 13 and cli['historical_rows_preserved']
    expected = {}
    for c in cli['cases']:
        assert c['status'] == 'PASS'
        if 'mode' in c:
            r = c['result']; terminal = r['batch'] == 'STOP'
            trace_check(c['trace'], terminal)
            assert r['mail_calls'] == r['pdf_calls'] == 0
            assert r['next_preflight_calls'] == (1 if terminal else 3)
            expected.update({int(k):v for k,v in c['persisted'].items()})
            if c['fault'] != 'close_before_end':
                after = c['monitor_after_last_physical_ack']
                assert any(x['function'] == 'dol_syslog' for x in after)
                assert any(x['function'] == 'mod_syslog_file->export' for x in after)
        elif c['id'].startswith('AP3-UNQUALIFIED-'):
            assert c['result']['next_preflight_calls'] == c['business_DML'] == 0
            expected.update({i:0 for i in c['fixtures']})
        else:
            assert c['result']['results'][str(c['fixture'])]['native_errno'] == 1568
            assert not any(e.get('sql') in ('BEGIN','COMMIT','ROLLBACK') for e in c['guard_trace'])
            expected[c['fixture']] = 0
    for op,value in [('COMMIT',1),('ROLLBACK',0)]:
        c = read('lost-'+op+'.json');r = c['result'];first = next(iter(r['results'].values()))
        assert c['status'] == 'PASS' and not c['Core_return']
        assert first['member'] == 'UNKNOWN' and first['native_errno'] == 2006 and r['batch'] == 'STOP'
        assert c['persisted'] == value
        assert c['proxy']['forwarded_target'] == c['proxy']['server_ok_discarded'] == 1
        assert c['proxy']['raw_packets_logged'] == 0
        trace_check(c['trace'],True)
        expected.update({int(k):v for k,v in c['values'].items()})
    http = read('HTTP-OWNER-R1-result.json')
    assert http['status'] == 'PASS' and len(http['cases']) == 9
    assert http['reference_business_equal'] and http['temporary_server_stopped']
    profile = read('HTTP-R1-profile.json')['profile']
    assert profile['session_handler'] == 'files' and profile['session_path_is_I105']
    assert profile['sapi'] == 'cli-server' and profile['logger']['level'] == 4
    ref = read('HTTP-R1-reference-after.json')
    assert len(ref['reference_before']) == 13 and ref['reference_before'] == ref['reference_after']
    for summary in http['cases']:
        c = read(summary['id']+'.json');r = c['result'];terminal = r['batch'] == 'STOP'
        assert summary['status'] == 'PASS' and summary['replay_rejected_before_lab']
        assert summary['readback']['consumed']
        trace_check(c['shutdown_trace'],terminal)
        assert any(x['function'] == 'dol_shutdown' for x in c['shutdown_monitor'])
        assert r['mail_calls'] == r['pdf_calls'] == 0
        expected.update({int(k):v for k,v in c['persisted'].items()})
        first = next(iter(r['results'].values()))
        stored = summary['readback']
        if c['case'] == 'HTTP_CHECKPOINT_FAILURE':
            assert first['member'] == 'SUCCESS' and first['end_ack'] and not first['checkpoint']
            assert stored['phase'] == 'processing'
            assert all(v['member'] == 'PENDING_RESULT_RECORD' for v in stored['results'].values())
        else:
            assert stored['phase'] == 'done'
            for k,v in r['results'].items(): assert stored['results'][k]['member'] == v['member']
        if terminal:
            assert r['next_preflight_calls'] == 1
            assert all(v['member'] == 'NOT_PROCESSED' for v in list(r['results'].values())[1:])
    final = read('audit-final.json')
    assert final['historical_unchanged'] and final['historical_rows'] == 81
    assert final['historical_sha256'] == inspect['baseline_sha256']
    assert final['P_S_denied'] == 1142 and final['grants_unchanged'] and final['Core_pins_unchanged']
    assert {int(x['id']):int(x['n']) for x in final['new_rows']} == expected and len(expected) == 70
    matrix = read('TEST-MATRIX.json')
    assert len(matrix['cases']) == 24 and all(c['status'] == 'PASS' for c in matrix['cases'])
    assert [(g['id'],g['status']) for g in matrix['groups']] == [('G1','PARTIAL'),('G2','PASS'),('G3','PASS'),('G4','PASS'),('G5','PASS'),('G6','PARTIAL')]
    assert read('cli.json')['status'] == read('HTTP-OWNER-result.json')['status'] == 'STOP'
    print(json.dumps({'status':'VERIFIED','payload_files':len(paths),'checksums':len(sums),'controlled_cases':24,
                      'historical_rows_preserved':81,'new_lab_rows':70,'database_or_HTTP_connections':0,
                      'gate':'INDEPENDENT REVIEW PENDING; G1/G6 partial limits retained'}))

if __name__ == '__main__':main()
