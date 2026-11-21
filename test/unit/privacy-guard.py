#!/usr/bin/env python3
"""Offline regression of the current-tree privacy guard; creates only temp git fixtures."""
from __future__ import annotations

import pathlib
import shutil
import subprocess
import sys
import tempfile

SOURCE = pathlib.Path(__file__).resolve().parents[2] / "tools" / "check-private-data.py"


def run(*args: str, cwd: pathlib.Path) -> subprocess.CompletedProcess[str]:
    return subprocess.run(args, cwd=str(cwd), text=True, capture_output=True, check=False)


checks = 0
with tempfile.TemporaryDirectory(prefix="msm-privacy-unit-") as scratch:
    root = pathlib.Path(scratch)
    (root / "tools").mkdir()
    shutil.copyfile(SOURCE, root / "tools" / "check-private-data.py")
    init = run("git", "init", "-q", cwd=root)
    assert init.returncode == 0, init.stderr
    forbidden = "mar" + "cel"
    cases = (
        ("fixture.md", "documented synthetic placeholder", True),
        ("fixture.md", "operator " + forbidden, False),
        ("fixture.php", "<?php // /home/" + forbidden + "/project", False),
        ("fixture.txt", "Windows C:\\Users\\" + forbidden + "\\project", False),
        ("evidence.started", "local /home/" + forbidden + "/workspace", False),
        (".gitignore", "private /home/" + forbidden, False),
        ("fixture.json", '{"account":"' + ("dolibarr" + "_i105") + '"}', False),
    )
    for name, body, accepted in cases:
        for stale in ("fixture.md", "fixture.php", "fixture.txt",
                      "evidence.started", ".gitignore", "fixture.json"):
            target = root / stale
            if target.exists():
                target.unlink()
        (root / name).write_text(body + "\n", encoding="utf-8")
        added = run("git", "add", "-A", cwd=root)
        assert added.returncode == 0, added.stderr
        check = run(sys.executable, "tools/check-private-data.py", cwd=root)
        assert (check.returncode == 0) is accepted, (
            name, accepted, check.stdout, check.stderr
        )
        checks += 1

print(f"Privacy guard offline regressions: {checks} cases PASS (no DB/HTTP).")
