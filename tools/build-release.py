#!/usr/bin/env python3
"""Build and verify the allowlisted Dolibarr v0.1.0 distribution package (no bootstrap)."""
# Copyright (C) 2026 iLLixM <iLLixM@proton.me>
# SPDX-License-Identifier: GPL-3.0-or-later
import hashlib
import io
import json
from pathlib import Path, PurePosixPath
import re
import stat
import zipfile

ROOT = Path(__file__).resolve().parents[1]
MODULE = 'membersubscriptionmassaction'
VERSION = '0.1.0'
PREFIX = MODULE + '/'
EPOCH = (1980, 1, 1, 0, 0, 0)


def require(condition, message):
    if not condition:
        raise ValueError(message)


def digest(data):
    return hashlib.sha256(data).hexdigest()


def canonical(value):
    return (json.dumps(value, indent=2, sort_keys=True, ensure_ascii=False) + '\n').encode('utf-8')


def check_path(name):
    require(isinstance(name, str) and name != '', 'Empty/non-string path')
    p = PurePosixPath(name)
    require(not p.is_absolute() and str(p) == name and '\\' not in name
            and all(part not in ('', '.', '..') for part in name.split('/')), 'Unsafe/noncanonical path: ' + name)
    require(not re.match(r'^[A-Za-z]:', name), 'Drive path not allowed')


def source_bytes(name):
    check_path(name)
    p = ROOT
    for part in PurePosixPath(name).parts:
        p = p / part
        require(not p.is_symlink(), 'Symlink not allowed: ' + name)
    require(p.is_file(), 'Missing/nonregular allowlisted input: ' + name)
    return p.read_bytes()


def build():
    names = [line for line in (ROOT / 'tools/release-files.txt').read_text().splitlines()
             if line and not line.startswith('#')]
    require(names == sorted(set(names)), 'Allowlist must be sorted and unique')
    files = {name: source_bytes(name) for name in names}
    # All present runtime files must be explicitly accounted for; discovery never includes them automatically.
    runtime = {'subscription.php'}
    for folder, suffix in [('class', '.php'), ('lib', '.php'), ('core/modules', '.php'),
                           ('langs', '.lang'), ('js', '.js')]:
        runtime.update(p.relative_to(ROOT).as_posix() for p in (ROOT / folder).rglob('*')
                       if p.is_file() and p.suffix == suffix)
    require(runtime <= set(files), 'Runtime files missing from explicit allowlist: ' + str(runtime - set(files)))
    require(all(not set(PurePosixPath(n).parts) & {'test', 'tests', '.git', '.github', 'vendor',
                '__pycache__', 'build-output', 'conf', 'documents', 'sessions', 'fixtures'}
                for n in names), 'Forbidden development/private directory')
    require(all(n.endswith(('.php', '.js', '.lang', '.md', '.json')) or n == 'COPYING'
                for n in names), 'Unapproved file type')
    descriptor = files['core/modules/modMemberSubscriptionMassaction.class.php'].decode()
    require(re.search(r"\$this->version\s*=\s*'0\.1\.0';", descriptor), 'Descriptor version mismatch')
    require(re.search(r'\$this->numero\s*=\s*584620;', descriptor), 'Module ID mismatch')
    composer = json.loads(files['composer.json'])
    require(composer['version'] == VERSION and composer['require']['php'] == '>=7.4'
            and composer['license'] == 'GPL-3.0-or-later', 'Composer metadata mismatch')
    for locale in ['de_DE', 'en_US']:
        text = files[f'langs/{locale}/membersubscriptionmassaction.lang'].decode()
        require('v0.1.0' in text, 'Language version status missing')
        name = re.search(r'^ModuleMemberSubscriptionMassactionName=(.*)$', text, re.M)
        require(name and not re.search(r'\((?:development|Entwicklung)\)', name[1]), 'Development display name remains')
    for name in ['README.md', 'docs/release-notes-v0.1.0.md']:
        require(b'v0.1.0' in files[name], 'User documentation version missing')
    manifest = {
        'module': MODULE, 'version': VERSION,
        'module_id': 584620, 'development_baseline': '48d196cfb04421d775ab4e6757083f6a755121df',
        'format': 'ZIP_STORED; fixed 1980-01-01 UTC-like ZIP timestamp; Unix regular files 0644; sorted paths',
        'files': [{'path': n, 'bytes': len(files[n]), 'sha256': digest(files[n])} for n in names],
    }
    manifest_bytes = canonical(manifest)
    payload = {PREFIX + n: b for n, b in files.items()}
    payload[PREFIX + 'PACKAGE-MANIFEST.json'] = manifest_bytes
    # No runtime clock, UID, local path, Git metadata or compression-library variation.
    buffer = io.BytesIO()
    with zipfile.ZipFile(buffer, 'w', compression=zipfile.ZIP_STORED) as archive:
        for name, data in sorted(payload.items()):
            entry = zipfile.ZipInfo(name, EPOCH)
            entry.create_system = 3
            entry.external_attr = (stat.S_IFREG | 0o644) << 16
            entry.compress_type = zipfile.ZIP_STORED
            archive.writestr(entry, data)
    data = buffer.getvalue()
    with zipfile.ZipFile(io.BytesIO(data)) as archive:
        require(archive.testzip() is None, 'Archive CRC failure')
        require(archive.namelist() == sorted(payload), 'Duplicate/missing archive entries')
        require(all(archive.read(n) == b for n, b in payload.items()), 'Payload changed')
    output = ROOT / 'build-output' / ('v' + VERSION)
    require(not (ROOT / 'build-output').is_symlink() and not output.is_symlink(), 'Unsafe output directory')
    output.mkdir(parents=True, exist_ok=True)
    filename = f'module_{MODULE}-{VERSION}.zip'
    for name, content in {
        filename: data, 'PACKAGE-MANIFEST.json': manifest_bytes,
        filename + '.sha256': (digest(data) + '  ' + filename + '\n').encode(),
    }.items():
        target = output / name
        require(not target.is_symlink(), 'Output symlink not allowed')
        target.write_bytes(content)
    print(json.dumps({'status': 'STRUCTURAL PASS; NOT an import test', 'version': VERSION,
                      'zip': str(output / filename), 'payload_files': len(files),
                      'archive_entries': len(payload), 'sha256': digest(data)}))


if __name__ == '__main__':
    build()
