#!/usr/bin/env python3
"""Build the deterministic installable v0.1.0 payload from Git blobs."""
# Copyright (C) 2026 iLLixM
# SPDX-License-Identifier: GPL-3.0-or-later

import argparse
import hashlib
import io
import json
from pathlib import Path
import re
import stat
import subprocess
import zipfile

ROOT = Path(__file__).resolve().parents[1]
MODULE = "membersubscriptionmassaction"
VERSION = "0.1.0"
MODULE_ID = 584620
DESCRIPTOR = "core/modules/modMemberSubscriptionMassaction.class.php"
RUNTIME_FILES = (
    "admin/setup.php",
    "class/actions_membersubscriptionmassaction.class.php",
    "class/activetransactionguard.class.php",
    "class/booking.class.php",
    "class/bookingmember.class.php",
    "class/corecompatibility.class.php",
    "class/coreguardedrightsdb.class.php",
    "class/coreguardedtransaction.class.php",
    "class/databasestate.class.php",
    "class/filebookingcheckpoint.class.php",
    "class/invoice.class.php",
    "class/invoicepdf.class.php",
    "class/invoiceprofile.class.php",
    "class/mail.class.php",
    "class/period.class.php",
    "class/preview.class.php",
    "class/transactionprofile.class.php",
    "core/modules/modMemberSubscriptionMassaction.class.php",
    "js/subscription.js",
    "langs/de_DE/membersubscriptionmassaction.lang",
    "langs/en_US/membersubscriptionmassaction.lang",
    "lib/batch.lib.php",
    "lib/booking.lib.php",
    "lib/booking_renderer.lib.php",
    "lib/membersubscriptionmassaction.lib.php",
    "lib/preview.lib.php",
    "lib/preview_renderer.lib.php",
    "lib/profile.lib.php",
    "subscription.php",
)
RELEASE_FILES = {
    **{name: name for name in RUNTIME_FILES},
    "COPYING": "COPYING",
    "README.md": "docs/distribution/README.md",
    "composer.json": "composer.json",
}
NON_DISTRIBUTION_DIRECTORIES = {".github", "docs", "experiments", "test", "tools"}
NON_DISTRIBUTION_FILES = {".gitattributes", ".gitignore", "AGENTS.md", "CHANGELOG.md", "CONTRIBUTING.md", "README.md"}


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

    sources = set(RELEASE_FILES.values())
    unexpected = {
        name for name in tree_entries
        if name not in sources
        and ("/" not in name or name.split("/", 1)[0] not in NON_DISTRIBUTION_DIRECTORIES)
        and name not in NON_DISTRIBUTION_FILES
    }
    if unexpected:
        raise ValueError(f"Unclassified source files: {sorted(unexpected)}")
    missing = sources - tree_entries.keys()
    if missing:
        raise ValueError(f"Missing distribution files: {sorted(missing)}")

    payload = {}
    for destination, source in sorted(RELEASE_FILES.items()):
        mode, kind, blob = tree_entries[source]
        if kind != "blob" or mode not in {"100644", "100755"}:
            raise ValueError(f"Distribution input is not a regular file: {source}")
        payload[destination] = git("cat-file", "blob", blob)

    composer = json.loads(payload["composer.json"])
    descriptor = payload[DESCRIPTOR]
    if (composer["version"] != VERSION or composer["license"] != "GPL-3.0-or-later"
            or composer["name"] != "illixm/dolibarr-member-subscription-massaction"
            or composer["type"] != "dolibarr-module" or composer["require"]["php"] != ">=7.4"
            or f"$this->version = '{VERSION}';".encode() not in descriptor
            or f"$this->numero = {MODULE_ID};".encode() not in descriptor
            or f"$this->rights_class = '{MODULE}';".encode() not in descriptor):
        raise ValueError("Release metadata differs")

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
    report = {
        "source_commit": head,
        "source_tree": tree,
        "module": MODULE,
        "module_id": MODULE_ID,
        "version": VERSION,
        "files": len(payload),
        "zip_bytes": len(zip_data),
        "zip_sha256": sha256(zip_data),
    }
    return zip_data, report


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("source", help="Exact 40-digit source commit")
    parser.add_argument("output", type=Path, help="New output directory")
    args = parser.parse_args()
    if not re.fullmatch(r"[0-9a-f]{40}", args.source):
        parser.error("Use an exact Git SHA")
    archive, report = build(args.source)
    args.output.mkdir(mode=0o700)
    name = f"module_{MODULE}-{VERSION}.zip"
    (args.output / name).write_bytes(archive)
    (args.output / "SHA256SUMS").write_text(f"{report['zip_sha256']}  {name}\n", encoding="ascii")
    print(json.dumps({"status": "STRUCTURAL PASS", "zip": name, **report}, sort_keys=True))


if __name__ == "__main__":
    main()
