#!/usr/bin/env python3
"""Read pinned Core Git objects and module source; no checkout, bootstrap or database."""
# SPDX-License-Identifier: GPL-3.0-or-later
import hashlib
import json
from pathlib import Path
import re
import subprocess
import sys

module = Path(__file__).resolve().parents[2]
coregit = Path(sys.argv[1])
output = Path(sys.argv[2])
base = 'a74edb29ba6ca49af46c389f98c4bfd86cf161b6'
workflow = (module/'.github/workflows/ci.yml').read_text()
pairs = re.findall(r"core: '([^']+)'\s+core-ref: ([0-9a-f]{40})", workflow)
refs = dict(pairs)

def extract(text, method):
    match = re.search(r'\tpublic function '+method+r'\([^\n]+', text)
    assert match, method
    start = text.index('{', match.start())
    depth = 0
    for i in range(start, len(text)):
        depth += (text[i] == '{') - (text[i] == '}')
        if not depth:
            return text[match.start():i+1], text[:match.start()].count('\n')+1
    raise ValueError('Unterminated method')

rows = []
for version, pin in refs.items():
    data = subprocess.check_output(['git', 'show', pin+':htdocs/core/db/DoliDB.class.php'], cwd=coregit).decode()
    methods = {}
    for name in ['begin', 'commit', 'rollback']:
        body, line = extract(data, name)
        methods[name] = {'line': line, 'sha256': hashlib.sha256(body.encode()).hexdigest(),
                         'url': f'https://github.com/Dolibarr/dolibarr/blob/{pin}/htdocs/core/db/DoliDB.class.php#L{line}'}
    rows.append({'version': version, 'pin': pin, 'methods': methods})
assert len(rows) == 15
assert all(len({x['methods'][name]['sha256'] for x in rows}) == 1 for name in ['begin','commit','rollback'])
files = {}
for name in ['class/databasestate.class.php','class/booking.class.php','lib/booking.lib.php','subscription.php','test/fixtures/database_state.php','test/unit/database.php']:
    data = (module/name).read_bytes()
    assert data == subprocess.check_output(['git','show',base+':'+name], cwd=module)
    files[name] = hashlib.sha256(data).hexdigest()
root = Path('/home/marcel/dolibarr-i105-v24-mysql')
link = root/'htdocs/custom/membersubscriptionmassaction'
runtime = {'core_pin': subprocess.check_output(['git','rev-parse','HEAD'],cwd=root).decode().strip(),
           'module_symlink_target': str(link.resolve()),
           'module_pin': subprocess.check_output(['git','rev-parse','HEAD'],cwd=link.resolve()).decode().strip(),
           'observer_bytes_equal_analysis_base': (link/'class/databasestate.class.php').read_bytes() == (module/'class/databasestate.class.php').read_bytes(),
           'database_bootstrap_or_business_checks': 'NOT EXECUTED; source metadata only'}
result = {'module_source_base':base,'core_versions':rows,'all_fifteen_core_transaction_method_hashes_identical':True,
          'module_file_hashes':files,'reference_source_metadata':runtime,
          'scope':'Source comparison, NOT runtime Core integration; live SQL uses the explicitly labeled counter MODEL'}
output.write_text(json.dumps(result,indent=2)+'\n')
print(json.dumps({'core_tags':len(rows),'three_method_hashes_identical':True,'reference':runtime}))
