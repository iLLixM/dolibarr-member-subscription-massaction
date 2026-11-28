#!/usr/bin/env python3
"""Build a reproducible Dolibarr module ZIP from local committed Git files."""
# Copyright (C) 2026 iLLixM
# SPDX-License-Identifier: GPL-3.0-or-later

import argparse
import hashlib
import io
import json
import os
from pathlib import Path
import re
import shutil
import stat
import subprocess
import zipfile

ROOT = Path(__file__).resolve().parents[1]
MODULE = "membersubscriptionmassaction"
MODULE_ID = 584620
DESCRIPTOR = "core/modules/modMemberSubscriptionMassaction.class.php"
VERSION_PATTERN = re.compile(
    r"(?:0|[1-9][0-9]*)\.(?:0|[1-9][0-9]*)\.(?:0|[1-9][0-9]*)"
    r"(?:-[0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*)?(?:\+[0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*)?"
)
PHP_NUMBER = r"(0|[1-9][0-9]*)"
PHP_COMPOSER_MINIMUM = re.compile(rf"\s*>=\s*{PHP_NUMBER}\.{PHP_NUMBER}(?:\.{PHP_NUMBER})?\s*")
PHP_DESCRIPTOR_MINIMUM = rf"array\s*\(\s*{PHP_NUMBER}\s*,\s*{PHP_NUMBER}(?:\s*,\s*{PHP_NUMBER})?\s*\)"
PHP_STRING_LITERAL = r"""(['"])([^'"]*)\1"""
# Scan comments and strings before looking for executable metadata assignments.
DESCRIPTOR_ASSIGNMENT = re.compile(
    r"""//[^\r\n]*|\#[^\r\n]*|/\*[\s\S]*?\*/|"(?:\\.|[^"\\])*"|'(?:\\.|[^'\\])*'"""
    r"|\$this\s*->\s*(?P<name>version|phpmin|numero|rights_class)\s*=(?![=>])"
)
RELEASE_FILES = (
    "COPYING",
    "README.md",
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
    "composer.json",
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
NON_DISTRIBUTION_DIRECTORIES = {".github", "docs", "experiments", "test", "tools"}
NON_DISTRIBUTION_FILES = {".gitattributes", ".gitignore", "AGENTS.md", "CHANGELOG.md", "CONTRIBUTING.md"}


def git(*args):
    # A partial clone can otherwise fetch missing blobs during a read.
    local_only = dict(os.environ, GIT_NO_LAZY_FETCH="1", GIT_ALLOW_PROTOCOL="")
    return subprocess.check_output(["git", *args], cwd=ROOT, env=local_only)


def sha256(data):
    return hashlib.sha256(data).hexdigest()


def unique_json_object(pairs):
    result = {}
    for key, value in pairs:
        if key in result:
            raise ValueError(f"Duplicate composer.json key: {key}")
        result[key] = value
    return result


def valid_version(value):
    if not isinstance(value, str) or not VERSION_PATTERN.fullmatch(value):
        return False
    prerelease = value.split("+", 1)[0].partition("-")[2]
    return not any(
        part.isdigit() and len(part) > 1 and part.startswith("0")
        for part in prerelease.split(".")
    )


def module_version(payload):
    descriptor = payload[DESCRIPTOR].decode("utf-8")
    fields = {}
    for match in DESCRIPTOR_ASSIGNMENT.finditer(descriptor):
        name = match.group("name")
        if name is None:
            continue
        if name in fields:
            raise ValueError(f"Repeated module descriptor assignment: {name}")
        fields[name] = match.end()

    def read_field(name, pattern):
        if name not in fields:
            raise ValueError(f"Missing module descriptor field: {name}")
        match = re.match(r"\s*" + pattern + r"\s*;", descriptor[fields[name]:])
        if match is None:
            raise ValueError(f"Invalid module descriptor field: {name}")
        return match

    version = read_field("version", PHP_STRING_LITERAL)[2]
    if not valid_version(version):
        raise ValueError("Invalid module descriptor version")

    composer = json.loads(payload["composer.json"], object_pairs_hook=unique_json_object)
    if not isinstance(composer, dict):
        raise ValueError("Expected a composer.json object")
    if not valid_version(composer.get("version")) or composer["version"] != version:
        raise ValueError("Module descriptor and composer.json versions must match")

    requirements = composer.get("require")
    php = requirements.get("php") if isinstance(requirements, dict) else None
    php_requirement = PHP_COMPOSER_MINIMUM.fullmatch(php) if isinstance(php, str) else None
    php_minimum = read_field("phpmin", PHP_DESCRIPTOR_MINIMUM)
    if php_requirement is None or (php_requirement[1], php_requirement[2], php_requirement[3] or "0") != (
        php_minimum[1], php_minimum[2], php_minimum[3] or "0"
    ):
        raise ValueError("Module descriptor and composer.json PHP minima must match")

    if (
        composer.get("license") != "GPL-3.0-or-later"
        or composer.get("name") != "illixm/dolibarr-member-subscription-massaction"
        or composer.get("type") != "dolibarr-module"
        or read_field("numero", PHP_NUMBER)[1] != str(MODULE_ID)
        or read_field("rights_class", PHP_STRING_LITERAL)[2] != MODULE
    ):
        raise ValueError("Module identity metadata differs")
    return version


def build(revision):
    origin_ref = git("rev-parse", "--symbolic-full-name", "--verify", "--end-of-options", revision).decode().strip()
    head = git("rev-parse", "--verify", "--end-of-options", (origin_ref or revision) + "^{commit}").decode().strip()
    tree = git("rev-parse", head + "^{tree}").decode().strip()
    tree_entries = {}
    for line in git("ls-tree", "-rz", head).split(b"\0"):
        if line:
            metadata, path = line.split(b"\t", 1)
            mode, kind, blob = metadata.decode().split()
            tree_entries[path.decode()] = (mode, kind, blob)

    sources = set(RELEASE_FILES)
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
    for source in sorted(RELEASE_FILES):
        mode, kind, blob = tree_entries[source]
        if kind != "blob" or mode not in {"100644", "100755"}:
            raise ValueError(f"Distribution input is not a regular file: {source}")
        payload[source] = git("cat-file", "blob", blob)

    version = module_version(payload)
    if origin_ref.startswith("refs/tags/"):
        tag = origin_ref[len("refs/tags/"):]
        tag_version = tag[1:] if tag.startswith("v") else tag
        if re.match(r"v?[0-9]+\.[0-9]+(?:\.|$)", tag) and tag_version != version:
            raise ValueError("Release tag and module version must match")

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
        "version": version,
        "files": len(payload),
        "zip_bytes": len(zip_data),
        "zip_sha256": sha256(zip_data),
    }
    return zip_data, report


def write_artifacts(output, name, archive, digest):
    output.mkdir(mode=0o700, exist_ok=True)
    staging = output / ".build-release-staging"
    # A shared output directory can only have one active writer.
    staging.mkdir(mode=0o700)
    try:
        zip_target = output / name
        sums_target = output / "SHA256SUMS"
        for target in (zip_target, sums_target):
            if target.exists() and not (target.is_file() or target.is_symlink()):
                raise OSError(f"Output path is not a file: {target}")

        staged_zip = staging / name
        staged_sums = staging / "SHA256SUMS"
        staged_zip.write_bytes(archive)
        staged_sums.write_text(f"{digest}  {name}\n", encoding="ascii")

        previous_zip = staging / "previous.zip"
        had_zip = zip_target.exists() or zip_target.is_symlink()
        if had_zip:
            shutil.copy2(zip_target, previous_zip, follow_symlinks=False)
        staged_zip.replace(zip_target)
        try:
            staged_sums.replace(sums_target)
        except OSError:
            if had_zip:
                previous_zip.replace(zip_target)
            else:
                zip_target.unlink()
            raise
    finally:
        shutil.rmtree(staging)


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("source", nargs="?", default="HEAD", help="Local Git commit or tag (default: HEAD)")
    parser.add_argument("output", nargs="?", type=Path, help="Output directory (replaces existing build files)")
    parser.add_argument(
        "-o", "--output-dir", type=Path, help="Output directory (default: build-output/<version>/<commit>/)"
    )
    args = parser.parse_args()
    if args.output is not None and args.output_dir is not None:
        parser.error("Specify the output directory only once")
    try:
        archive, report = build(args.source)
        output = args.output_dir if args.output_dir is not None else args.output
        if output is None:
            output = ROOT / "build-output" / report["version"] / report["source_commit"]
            output.parent.mkdir(parents=True, exist_ok=True)
        name = f"module_{MODULE}-{report['version']}.zip"
        write_artifacts(output, name, archive, report["zip_sha256"])
    except subprocess.CalledProcessError:
        parser.exit(1, "Build failed: unable to read the requested local Git source\n")
    except (OSError, ValueError) as error:
        parser.exit(1, f"Build failed: {error}\n")
    print(json.dumps({
        "status": "STRUCTURAL PASS", "zip": name, "output_dir": str(output.absolute()), **report
    }, sort_keys=True))


if __name__ == "__main__":
    main()
