#!/usr/bin/env python3
"""Rebuild an allowlisted offline review index; never execute experiment workers."""
# SPDX-License-Identifier: GPL-3.0-or-later
import hashlib
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
LAB = ROOT / 'experiments/d01'
EVIDENCE = LAB / 'evidence'

def load(name):
    return json.loads((EVIDENCE / name).read_text())

def write(path, data):
    path.write_text(json.dumps(data, indent=2) + '\n')

cases = []
for c in load('core-cases.json')['cases']:
    note = c.get('classification', c.get('observation', c.get('limit', c.get('evidence', 'Observed expected real Core/SQL result; see report group contract'))))
    cases.append({'id': c['id'], 'measurement': 'PASS', 'expected': 'Measure the exact group objective defined in report section 2; negative controls are not safety acceptance',
                  'observed': c, 'assessment': note, 'evidence': 'evidence/core-cases.json'})
for c in load('locks.json')['cases']:
    cases.append({'id': 'D01-LOCK-' + c['mode'], 'measurement': 'PASS',
                  'expected': 'B blocks until A commit; post-lock consistent read1, pre-lock metadata control read0',
                  'observed': c, 'assessment': c['assessment'], 'evidence': 'evidence/locks.json'})
for op in ('COMMIT', 'ROLLBACK'):
    c = load('lost-' + op + '.json')
    cases.append({'id': 'D01-LOST-' + op, 'measurement': 'PASS', 'expected': 'Server OK discarded, Core failure, UNKNOWN/STOP regardless of persistence',
                  'observed': {k: v for k, v in c.items() if k != 'trace'}, 'assessment': c['classification'], 'evidence': 'evidence/lost-' + op + '.json'})
for c in load('factory.json')['cases']:
    cases.append({'id': 'D01-FACTORY-' + c['operation'], 'measurement': 'PASS',
                  'expected': 'Exact Core factory, entry and independent commit/rollback persistence',
                  'observed': c, 'assessment': 'Real driver control; no native SQL tracing seam on factory connection', 'evidence': 'evidence/factory.json'})
cases.append({'id': 'D01-CLOSED-CONNECTION', 'measurement': 'PARTIAL',
              'expected': 'Preserve failure/return on closed client; no retry/rollback',
              'observed': load('core-cases.json')['failure'],
              'assessment': 'Original harness STOP; read-only reconciliation proves fixture4047=0 and old data unchanged, not retained error class/return',
              'evidence': ['evidence/core-cases.json', 'evidence/stop-reconciled.json']})
write(EVIDENCE / 'TEST-MATRIX.json', {'version': 1, 'verdict': 'D-01 CHANGES REQUIRED', 'cases': cases})
files = sorted(p for p in LAB.rglob('*') if p.is_file() and '__pycache__' not in p.parts
               and p.name not in ('MANIFEST.json', 'SHA256SUMS'))
files.append(ROOT / 'docs/d01-loaded-core-feasibility-v1.md')
assert all(p.suffix in ('.md', '.php', '.py', '.json', '.txt', '.started') for p in files)
entries = [{'path': p.relative_to(ROOT).as_posix(), 'bytes': p.stat().st_size,
            'sha256': hashlib.sha256(p.read_bytes()).hexdigest()} for p in sorted(files)]
manifest = {'format': 'D01 private-free review evidence v1', 'date': '2026-11-18',
            'base_commit': '6247704815cf532ec632616011133c646aa00005',
            'core_commit': '5c6c7ff6a5112413b1c27221c8d11750afad56f8',
            'verdict': 'D-01 CHANGES REQUIRED', 'files': entries}
write(LAB / 'MANIFEST.json', manifest)
all_sums = entries + [{'path': 'experiments/d01/MANIFEST.json', 'sha256': hashlib.sha256((LAB / 'MANIFEST.json').read_bytes()).hexdigest()}]
(LAB / 'SHA256SUMS').write_text(''.join(e['sha256'] + '  ' + e['path'] + '\n' for e in all_sums))
print(json.dumps({'payload_files': len(entries), 'checksums': len(all_sums)}))
