#!/usr/bin/env python3
"""Build the deterministic installable v0.1.0 payload from Git blobs."""
# Copyright (C) 2026 iLLixM
# SPDX-License-Identifier: GPL-3.0-or-later

import argparse
import hashlib
import io
import json
from pathlib import Path, PurePosixPath
import re
import stat
import subprocess
import zipfile

ROOT = Path(__file__).resolve().parents[1]
MODULE = "membersubscriptionmassaction"
VERSION = "0.1.0"
MODULE_ID = 584620
DESCRIPTOR = "core/modules/modMemberSubscriptionMassaction.class.php"
RELEASE_FILES = {
    "COPYING": "COPYING",
    "README.md": "docs/distribution/README.md",
    "composer.json": "composer.json",
    "subscription.php": "subscription.php",
    "admin/setup.php": "admin/setup.php",
    DESCRIPTOR: DESCRIPTOR,
    "js/subscription.js": "js/subscription.js",
    **{f"langs/{lang}/{MODULE}.lang": f"langs/{lang}/{MODULE}.lang" for lang in ("de_DE", "en_US")},
}


def git(*args):
    return subprocess.check_output(["git", *args], cwd=ROOT)


def sha256(data):
    return hashlib.sha256(data).hexdigest()


def build(revision):
    head = git("rev-parse", "--verify", revision + "^{commit}").decode().strip()
    tree = git("rev-parse", head + "^{tree}").decode().strip()
    tree_entries = {}
    for line in git("ls-tree", "-rz", head).split(b"\0"):
        if line:
            metadata, path = line.split(b"\t", 1)
            mode, kind, blob = metadata.decode().split()
            tree_entries[path.decode()] = (mode, kind, blob)

    mapping = dict(RELEASE_FILES)
    for name in tree_entries:
        if re.fullmatch(r"(class|lib)/[a-z0-9_]+\.(class|lib)\.php", name):
            mapping[name] = name
    roots = {"admin", "class", "core", "js", "langs", "lib"}
    omitted = {path for path in tree_entries if PurePosixPath(path).parts[0] in roots} - set(mapping.values())
    if omitted:
        raise ValueError(f"Unclassified runtime files: {sorted(omitted)}")

    payload = {}
    records = []
    for destination, source in sorted(mapping.items()):
        mode, kind, blob = tree_entries[source]
        if kind != "blob" or mode not in {"100644", "100755"}:
            raise ValueError(f"Runtime input is not a regular file: {source}")
        data = git("cat-file", "blob", blob)
        payload[destination] = data
        records.append({"path": destination, "source": source, "bytes": len(data), "sha256": sha256(data)})

    composer = json.loads(payload["composer.json"])
    descriptor = payload[DESCRIPTOR]
    if (composer["version"] != VERSION or composer["license"] != "GPL-3.0-or-later"
            or composer["require"]["php"] != ">=7.4"
            or b"$this->version = '0.1.0';" not in descriptor
            or b"$this->numero = 584620;" not in descriptor):
        raise ValueError("Release metadata differs")

    manifest = {
        "module": MODULE, "module_id": MODULE_ID, "version": VERSION,
        "source_commit": head, "source_tree": tree,
        "format": "ZIP_STORED, sorted paths, fixed timestamp, Unix regular files 0644",
        "files": records,
    }
    manifest_bytes = (json.dumps(manifest, sort_keys=True, indent=2) + "\n").encode()
    payload["PACKAGE-MANIFEST.json"] = manifest_bytes
    buffer = io.BytesIO()
    with zipfile.ZipFile(buffer, "w", compression=zipfile.ZIP_STORED) as archive:
        for name, data in sorted(payload.items()):
            entry = zipfile.ZipInfo(f"{MODULE}/{name}", (1980, 1, 1, 0, 0, 0))
            entry.create_system = 3
            entry.external_attr = (stat.S_IFREG | 0o644) << 16
            archive.writestr(entry, data)
    zip_data = buffer.getvalue()
    with zipfile.ZipFile(io.BytesIO(zip_data)) as archive:
        entries = [f"{MODULE}/{name}" for name in sorted(payload)]
        if archive.testzip() is not None or archive.namelist() != entries:
            raise ValueError("Archive validation failed")
        for name, data in payload.items():
            if archive.read(f"{MODULE}/{name}") != data:
                raise ValueError(f"Packaged data differs: {name}")
    return zip_data, manifest_bytes


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("source", help="Exact 40-digit source commit")
    parser.add_argument("output", type=Path, help="New output directory")
    args = parser.parse_args()
    if not re.fullmatch(r"[0-9a-f]{40}", args.source):
        parser.error("Use an exact Git SHA")
    archive, manifest = build(args.source)
    args.output.mkdir(mode=0o700)
    name = f"module_{MODULE}-{VERSION}.zip"
    (args.output / name).write_bytes(archive)
    (args.output / "PACKAGE-MANIFEST.json").write_bytes(manifest)
    (args.output / "SHA256SUMS").write_text(
        f"{sha256(archive)}  {name}\n{sha256(manifest)}  PACKAGE-MANIFEST.json\n", encoding="ascii"
    )
    print(f"STRUCTURAL PASS {args.source} {sha256(archive)}")


if __name__ == "__main__":
    main()
