#!/usr/bin/env python3
"""Read-only offline integrity and independently evaluated evidence consistency."""
# SPDX-License-Identifier: GPL-3.0-or-later
import hashlib
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
LAB = ROOT / 'experiments/d01'

def digest(path):
    return hashlib.sha256(path.read_bytes()).hexdigest()

def read(name):
    return json.loads((LAB / 'evidence' / name).read_text())

def entry_trace(entry, trace):
    a, b = entry['sql_indices']
    assert b == a + 1
    assert trace[a]['sql'] == 'SET TRANSACTION ISOLATION LEVEL REPEATABLE READ'
    assert trace[b]['sql'] == 'BEGIN'
    assert trace[a]['ok'] and trace[b]['ok']
    assert trace[a]['connection'] == trace[b]['connection'] == entry['connection']

def main():
    manifest = json.loads((LAB / 'MANIFEST.json').read_text())
    assert manifest['verdict'] == 'D-01 CHANGES REQUIRED'
    paths = [item['path'] for item in manifest['files']]
    assert len(paths) == len(set(paths))
    assert all(not Path(p).is_absolute() and '..' not in Path(p).parts for p in paths)
    actual = sorted(p.relative_to(ROOT).as_posix() for p in LAB.rglob('*') if p.is_file()
                    and '__pycache__' not in p.parts and p.name not in ('MANIFEST.json', 'SHA256SUMS'))
    actual.append('docs/d01-loaded-core-feasibility-v1.md')
    assert sorted(paths) == sorted(actual), 'File completeness'
    for item in manifest['files']:
        path = ROOT / item['path']
        assert path.stat().st_size == item['bytes'] and digest(path) == item['sha256'], item['path']
    sums = {}
    for line in (LAB / 'SHA256SUMS').read_text().splitlines():
        sha, name = line.split('  ', 1)
        assert name not in sums
        sums[name] = sha
        assert digest(ROOT / name) == sha, name
    assert set(sums) == set(paths) | {'experiments/d01/MANIFEST.json'}
    inspect = read('inspection.json')
    assert inspect['server']['version'] == '8.4.13' and inspect['P_S_errno'] == 1142
    assert len(inspect['grants']) == 2 and inspect['historical_rows'] == 27
    assert inspect['methods']['begin']['class'] == 'DoliDB'
    assert inspect['methods']['query']['class'] == 'DoliDBMysqli'
    r = read('core-cases.json')
    assert r['status'] == 'STOP' and len(r['cases']) == 31
    assert r['failure']['reason'] == 'Connection error must escape'
    for c in r['cases']:
        if 'entry' in c:
            entry_trace(c['entry'], r['trace'])
    isos = [c for c in r['cases'] if c['id'].startswith('D01-ISO-')]
    assert len(isos) == 16 and all(c['reads'] == [0, 0, 1, 0] for c in isos)
    foreign = next(c for c in r['cases'] if c['id'] == 'D01-FOREIGN-1568')
    assert foreign['native_errno'] == 1568 and foreign['Core_errno'] == 'DB_ERROR_1568'
    assert foreign['attempt_SQL'] == ['SET TRANSACTION ISOLATION LEVEL REPEATABLE READ']
    assert foreign['foreign_end_by_admission'] == 0
    for c in r['cases']:
        if c['id'].startswith('D01-COMPLETION-'):
            mode = c['settings']['completion_type']
            assert c['settings']['ac'] == '1' and c['Core_return'] and c['Core_depth'] == 0
            expected = {'NO_CHAIN': (True, 0), 'CHAIN': (False, 1568), 'RELEASE': (False, 2006)}[mode]
            assert (c['next_SET_ok'], c['next_SET_errno']) == expected
    lock = read('locks.json')
    assert lock['status'] == 'PASS' and len(lock['cases']) == 2
    for c in lock['cases']:
        b = c['B']
        assert c['B_pending_before_A_commit'] and b['lock_seconds'] >= .40
        assert b['locked'] == 1 and b['consistent'] == (1 if c['mode'] == 'correct' else 0)
        assert b['lock_start'] < c['A_commit_window'][0] <= b['lock_end']
        entry_trace(c['A_entry'], lock['trace'])
        entry_trace(b['entry'], b['trace'])
    for op, expected, depth in [('COMMIT', 9, 1), ('ROLLBACK', 0, 0)]:
        c = read('lost-' + op + '.json')
        assert c['status'] == 'PASS' and not c['Core_return'] and c['native_errno'] == 2006
        assert c['persisted'] == expected and c['Core_depth'] == depth
        assert c['proxy']['forwarded_target'] == c['proxy']['server_ok_discarded'] == 1
        assert c['proxy']['raw_packets_logged'] == 0
        entry_trace(c['entry'], c['trace'])
    factory = read('factory.json')
    assert factory['status'] == 'PASS' and len(factory['cases']) == 2
    assert all(c['DoliDB_class'] == 'DoliDBMysqli' and c['native_class'] == 'mysqli'
               and c['return'] and c['depth'] == 0 for c in factory['cases'])
    final = read('final-read.json')
    assert final['historical_preserved'] and final['new_synthetic_rows'] == 54
    assert final['historical_sha256'] == inspect['historical_sha256']
    assert final['Core_sources_unchanged'] and final['grants_unchanged']
    assert final['expected_values']['4047'] == 0
    matrix = read('TEST-MATRIX.json')
    assert len(matrix['cases']) == 38 and sum(c['measurement'] == 'PARTIAL' for c in matrix['cases']) == 1
    print(json.dumps({'status': 'VERIFIED', 'payload_files': len(paths), 'checksums': len(sums),
                      'completed_measurements': 37, 'partial_cases': 1,
                      'verdict': manifest['verdict'], 'database_connections': 0}))

if __name__ == '__main__':
    main()
