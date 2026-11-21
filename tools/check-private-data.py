#!/usr/bin/env python3
"""Reject private operator identities in the tracked working tree (not git history).

This checks the exact checked-out bytes and fails closed on unreadable text.
Local operators must keep actual DB/host/path parameters OUTSIDE the repository.
"""
from __future__ import annotations

import pathlib
import re
import subprocess
import sys

ROOT = pathlib.Path(__file__).resolve().parents[1]
TRACKED = subprocess.check_output(["git", "ls-files", "-z"], cwd=ROOT).split(b"\0")
SUFFIXES = {".php", ".md", ".txt", ".json", ".py", ".sh", ".js", ".yml",
            ".yaml", ".csv", ".lang", ".ini", ".xml", ".env"}
# Split sensitive patterns in source so the scanner does not match itself.
BANNED = [
    "mar" + "cel",
    "dolibarr" + "_i105",
    "dolibarr" + "_db_v24",
    "i105" + "-v24",
    "doli24" + ".test",
    "msm" + "sh01lab",
    "10.0" + ".2.100",
    "iLLixM@" + "proton.me",
    "/var/" + "lib/dolibarr-test-results",
]
HOME_PATH = re.compile(re.escape("/" + "home/") + r"[A-Za-z0-9._-]+")
MAC_HOME = re.compile(re.escape("/" + "Users/") + r"[A-Za-z0-9._-]+")
issues = []
scanned = 0
for name in TRACKED:
    if not name:
        continue
    relative = pathlib.Path(name.decode("utf-8", "strict"))
    if relative.suffix.lower() not in SUFFIXES:
        continue
    scanned += 1
    try:
        content = (ROOT / relative).read_text(encoding="utf-8")
    except (UnicodeError, OSError) as exc:
        issues.append(f"{relative}: unable to audit UTF-8 source ({type(exc).__name__})")
        continue
    for number, line in enumerate(content.splitlines(), 1):
        lower = line.lower()
        if any(token.lower() in lower for token in BANNED) or HOME_PATH.search(line) or MAC_HOME.search(line):
            issues.append(f"{relative}:{number}: private environment identifier")
if issues:
    print("FAIL: privacy scan detected private operator/environment material", file=sys.stderr)
    print("\n".join(issues[:100]), file=sys.stderr)
    sys.exit(1)
print(f"PASS: checked {scanned} tracked text files for banned private identifiers")
