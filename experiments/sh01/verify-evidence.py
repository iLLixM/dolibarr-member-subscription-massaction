#!/usr/bin/env python3
"""Seal/verify SH-01 source and sanitized evidence only; never run SQL or business tests."""
# SPDX-License-Identifier: GPL-3.0-or-later
import hashlib
import json
from pathlib import Path
import re
import subprocess
import sys

ROOT = Path(__file__).resolve().parents[2]
BASE = 'a74edb29ba6ca49af46c389f98c4bfd86cf161b6'
MANIFEST = 'experiments/sh01/evidence/MANIFEST.json'
SUMS = 'experiments/sh01/evidence/SHA256SUMS'


def digest(b):
    return hashlib.sha256(b).hexdigest()


def paths():
    found = [ROOT/'docs/sh01-mysql-restricted-feasibility.md']
    found += [p for p in (ROOT/'experiments/sh01').rglob('*') if p.is_file()]
    result = []
    for p in found:
        name = p.relative_to(ROOT).as_posix()
        assert not p.is_symlink() and '__pycache__' not in p.parts, 'Unsafe/unreviewed study file'
        if name not in (MANIFEST, SUMS):
            result.append(name)
    return sorted(result)


def facts():
    evidence = ROOT/'experiments/sh01/evidence'
    native = json.loads((evidence/'native.json').read_text())
    assert native['failure'] is None and native['performance_schema_select_errno'] == 1142
    assert native['database'] == 'msmsh01lab' and native['php'] == '8.2.35'
    assert len(native['grants']) == 2 and native['mysql_in_transaction_variable_errno'] == 1193
    assert not native['client_capabilities']['server_status_property']
    assert native['production_dolibarr_accesses'] == 0
    matrix = json.loads((evidence/'TEST-MATRIX.json').read_text())
    assert len(matrix['cases']) == 28
    assert {re.match(r'POC-\d+', c['id'])[0] for c in matrix['cases']} == {f'POC-{i:02d}' for i in range(1,12)}
    for kind in ('COMMIT', 'ROLLBACK'):
        r = json.loads((evidence/f'lost-ack-{kind}.json').read_text())
        assert r['proxy']['server_ok_discarded'] == r['proxy']['forwarded_target'] == 1
        assert r['proxy']['raw_packets_logged'] == 0
        assert r['actual']['client_errno'] == 2006 and r['actual']['guarded_write_blocked']
        assert r['actual']['observer_result'] == 'UNKNOWN/STOP' and r['actual']['extra_observer_sql'] == 0
        assert r['actual']['independent_n'] == (1 if kind == 'COMMIT' else 0)
    r = json.loads((evidence/'isolation-ordering.json').read_text())
    assert r['failure'] is None and all(t['first'] == 0 and t['second'] == 1 for t in r['tests'])
    r = json.loads((evidence/'final-lab-read.json').read_text())
    assert r['status'] == 'PASS' and len(r['rows']) == 27 and r['seed_rows_unchanged'] and r['performance_schema_select_errno'] == 1142
    r = json.loads((evidence/'source-audit.json').read_text())
    assert len(r['core_versions']) == 15 and r['all_fifteen_core_transaction_method_hashes_identical']
    for name, sha in r['module_file_hashes'].items():
        assert digest((ROOT/name).read_bytes()) == sha
        assert (ROOT/name).read_bytes() == subprocess.check_output(['git','show',BASE+':'+name],cwd=ROOT)
    changed = subprocess.check_output(['git','diff',BASE,'--name-only'],cwd=ROOT).decode().splitlines()
    assert all(n == 'docs/sh01-mysql-restricted-feasibility.md' or n.startswith('experiments/sh01/') for n in changed)
    for n in paths():
        p = ROOT/n
        if p.suffix == '.md':
            for target in re.findall(r'\]\(([^)]+)\)', p.read_text()):
                if target.startswith(('https:', 'http:', 'mailto:')):
                    continue
                name, _, fragment = target.partition('#')
                q = p.parent/name if name else p
                assert q.is_file(), (n, target)
                if fragment:
                    hs = [re.sub(r'[^\w\- ]','',line.lstrip('#').strip().lower()).replace(' ','-')
                          for line in q.read_text().splitlines() if line.startswith('#')]
                    assert fragment in hs, (n, target)
    # Only this new lab's password is checked in memory, never root/production credentials.
    config = Path.home()/'.config/dolibarr-sh01/app.json'
    if config.exists():
        password = json.loads(config.read_text())['password'].encode()
        assert all(password not in (ROOT/n).read_bytes() for n in paths()), 'Private lab credential found in study artifacts'
    assert '188 database observation assertions passed' in (evidence/'existing-database-synthetic.log').read_text()
    return {'mandatory_groups':11, 'native_measurements':28, 'core_source_tags':15,
            'existing_synthetic_assertions':188, 'actual_Core_business_integration':'NOT EXECUTED',
            'recommendation':'NO-GO for tested construction / SH-02; pending independent architecture review and Owner decision'}


if __name__ == '__main__':
    facts_result = facts()
    if sys.argv[1:] == ['--seal']:
        files = [{'path':n,'bytes':len((ROOT/n).read_bytes()),'sha256':digest((ROOT/n).read_bytes())} for n in paths()]
        manifest = {'analysis_base':BASE, 'facts':facts_result, 'files':files,
                    'scope':'Study/source + sanitized artificial evidence; not release package or independent security approval'}
        (ROOT/MANIFEST).write_text(json.dumps(manifest,indent=2)+'\n')
        names = sorted([f['path'] for f in files]+[MANIFEST])
        (ROOT/SUMS).write_text(''.join(digest((ROOT/n).read_bytes())+'  '+n+'\n' for n in names))
    elif sys.argv[1:]:
        raise SystemExit('Usage: verify-evidence.py [--seal]')
    m = json.loads((ROOT/MANIFEST).read_text())
    assert m['analysis_base'] == BASE and sorted(f['path'] for f in m['files']) == paths()
    for f in m['files']:
        data = (ROOT/f['path']).read_bytes()
        assert len(data) == f['bytes'] and digest(data) == f['sha256'], f['path']
    expected = ''.join(digest((ROOT/n).read_bytes())+'  '+n+'\n' for n in sorted(paths()+[MANIFEST]))
    assert (ROOT/SUMS).read_text() == expected
    print(json.dumps({'status':'PASS','manifest_payload_files':len(m['files']),
                      'checksum_entries':len(m['files'])+1,'manifest_sha256':digest((ROOT/MANIFEST).read_bytes()),
                      'facts':facts_result}))
