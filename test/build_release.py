"""Focused packaging checks using temporary local Git fixtures."""
# Copyright (C) 2026 iLLixM
# SPDX-License-Identifier: GPL-3.0-or-later

import hashlib
import json
from pathlib import Path
import re
import runpy
import shutil
import subprocess
import sys
import tempfile
import unittest
from unittest.mock import patch
from zipfile import ZipFile


ROOT = Path(__file__).resolve().parents[1]
BUILDER = Path("tools/build-release.py")
PACKAGING = runpy.run_path(str(ROOT / BUILDER))
MODULE = PACKAGING["MODULE"]
DESCRIPTOR = PACKAGING["DESCRIPTOR"]


class BuildReleaseTest(unittest.TestCase):
    def setUp(self):
        temp = tempfile.TemporaryDirectory()
        self.addCleanup(temp.cleanup)
        self.workspace = Path(temp.name)
        self.repo = self.workspace / "repo"
        self.repo.mkdir()
        self.git("init", "--quiet")
        self.git("config", "user.name", "Packaging Fixture")
        self.git("config", "user.email", "fixture@example.invalid")
        for file in (*PACKAGING["RELEASE_FILES"], ".gitignore"):
            data = subprocess.check_output(["git", "show", f"HEAD:{file}"], cwd=ROOT)
            path = self.repo / file
            path.parent.mkdir(parents=True, exist_ok=True)
            path.write_bytes(data)
        script = self.repo / BUILDER
        script.parent.mkdir()
        shutil.copyfile(ROOT / BUILDER, script)
        self.descriptor = (self.repo / DESCRIPTOR).read_text(encoding="utf-8")
        self.composer = (self.repo / "composer.json").read_text(encoding="utf-8")
        self.version = json.loads(self.composer)["version"]
        self.source = self.commit()

    def git(self, *args):
        return subprocess.check_output(["git", *args], cwd=self.repo, text=True).strip()

    def commit(self):
        self.git("add", "--all")
        self.git("commit", "--quiet", "-m", "Packaging fixture")
        return self.git("rev-parse", "HEAD")

    def run_builder(self, *args):
        return subprocess.run(
            [sys.executable, str(self.repo / BUILDER), *map(str, args)],
            cwd=self.workspace, capture_output=True, text=True,
        )

    def build(self, *args):
        result = self.run_builder(*args)
        self.assertEqual(result.returncode, 0, result.stderr)
        report = json.loads(result.stdout)
        output = Path(report["output_dir"])
        package = output / report["zip"]
        digest = hashlib.sha256(package.read_bytes()).hexdigest()
        self.assertEqual(report["zip_sha256"], digest)
        self.assertEqual((output / "SHA256SUMS").read_text(), f"{digest}  {package.name}\n")
        return report, package

    def reject(self, source, message):
        output = self.workspace / "rejected"
        result = self.run_builder(source, output)
        self.assertNotEqual(result.returncode, 0)
        self.assertIn(message, result.stderr)
        self.assertFalse(output.exists())

    def set_metadata(self, descriptor, composer):
        (self.repo / DESCRIPTOR).write_text(descriptor, encoding="utf-8")
        (self.repo / "composer.json").write_text(composer, encoding="utf-8")
        return self.commit()

    def test_head_default_and_repeatable_package(self):
        readme = (self.repo / "README.md").read_bytes()
        (self.repo / "README.md").write_text("Uncommitted README")
        report, package = self.build()
        self.assertEqual(report["source_commit"], self.source)
        self.assertEqual(report["version"], self.version)
        self.assertEqual(package.parent, self.repo / "build-output" / self.version / self.source)
        with ZipFile(package) as archive:
            self.assertEqual(report["files"], 32)
            self.assertEqual(len(archive.namelist()), 32)
            self.assertEqual(archive.read(f"{MODULE}/README.md"), readme)
        for file in (package, package.parent / "SHA256SUMS"):
            relative = file.relative_to(self.repo).as_posix()
            self.assertEqual(self.git("check-ignore", "--", relative), relative)
        explicit, repeated = self.build(self.source, "repeat")
        self.assertEqual(Path(explicit["output_dir"]), self.workspace / "repeat")
        self.assertEqual(package.read_bytes(), repeated.read_bytes())
        custom, custom_package = self.build("--output-dir", "custom")
        self.assertEqual(custom["source_commit"], self.source)
        for prior, args in ((package, ()), (repeated, (self.source, "repeat"))):
            with self.subTest(output=prior.parent):
                prior.write_bytes(b"old zip")
                (prior.parent / "SHA256SUMS").write_text("old checksum")
                notes = prior.parent / "notes.txt"
                notes.write_text("keep")
                _, rebuilt = self.build(*args)
                self.assertEqual(rebuilt, prior)
                self.assertEqual(rebuilt.read_bytes(), custom_package.read_bytes())
                self.assertEqual(notes.read_text(), "keep")
        self.assertEqual(self.git("rev-parse", "HEAD"), self.source)

    def test_future_metadata_and_local_tags(self):
        for future in ("2.3.4-dev", "2.3.4-beta", "2.3.4-rc.1"):
            with self.subTest(version=future):
                self.assertNotEqual(self.version, future)
                descriptor = self.descriptor.replace(f"'{self.version}'", f"'{future}'", 1)
                descriptor = re.sub(r"\$this->phpmin\s*=.*?;", "$this->phpmin = array(8, 1, 2);", descriptor)
                metadata = json.loads(self.composer)
                metadata["version"] = future
                metadata["require"]["php"] = ">=8.1.2"
                source = self.set_metadata(descriptor, json.dumps(metadata))
                self.git("tag", "-a", f"v{future}", "-m", "Fixture release", source)
                report, package = self.build(f"refs/tags/v{future}")
                self.assertEqual(report["source_commit"], source)
                self.assertEqual(report["version"], future)
                self.assertEqual(package.parent, self.repo / "build-output" / future / source)
                self.assertEqual(package.name, f"module_{MODULE}-{future}.zip")
        self.git("tag", "v9.9.9", source)
        self.git("tag", "v02.3.4", source)
        prior, _ = self.build(self.source, "--output-dir", "earlier")
        self.assertEqual(prior["version"], self.version)
        self.reject("v9.9.9", "Release tag and module version must match")
        self.reject("v02.3.4", "Release tag and module version must match")

    def test_conflicting_artifact_path_leaves_existing_zip(self):
        _, package = self.build()
        package.write_bytes(b"keep zip")
        checksum = package.parent / "SHA256SUMS"
        checksum.unlink()
        checksum.mkdir()
        result = self.run_builder()
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("Output path is not a file", result.stderr)
        self.assertEqual(package.read_bytes(), b"keep zip")
        self.assertTrue(checksum.is_dir())

    def test_checksum_replace_failure_restores_previous_build(self):
        _, package = self.build()
        original_bytes = package.read_bytes()
        checksum = package.parent / "SHA256SUMS"
        original_checksum = checksum.read_bytes()
        replace = Path.replace

        def fail_checksum(source, destination):
            if destination == checksum:
                raise OSError("Synthetic checksum replacement failure")
            return replace(source, destination)

        with patch.object(Path, "replace", fail_checksum):
            with self.assertRaisesRegex(OSError, "checksum replacement failure"):
                PACKAGING["write_artifacts"](package.parent, package.name, b"new zip", "new digest")
        self.assertEqual(package.read_bytes(), original_bytes)
        self.assertEqual(checksum.read_bytes(), original_checksum)

    def test_invalid_metadata_writes_no_artifacts(self):
        invalid_version = self.descriptor.replace(f"'{self.version}'", "'../escape'", 1)
        version_line = f"$this->version = '{self.version}';"
        fixtures = (
            ("mismatched version", self.descriptor, self.composer.replace(f'"{self.version}"', '"9.9.9"'),
             "versions must match"),
            ("invalid version", invalid_version, self.composer, "Invalid module descriptor version"),
            ("missing version", self.descriptor.replace(version_line, f"// {version_line}"),
             self.composer, "Missing module descriptor field: version"),
            ("duplicate descriptor version", self.descriptor.replace(version_line, f"{version_line} {version_line}"),
             self.composer, "Repeated module descriptor assignment: version"),
            ("missing Composer version", self.descriptor,
             json.dumps({k: v for k, v in json.loads(self.composer).items() if k != "version"}),
             "versions must match"),
            ("duplicate Composer version", self.descriptor, self.composer.replace('"version":', '"version": "9.9.9", "version":'),
             "Duplicate composer.json key: version"),
            ("inconsistent module ID", self.descriptor.replace("= 584620;", "= 584621;"),
             self.composer, "Module identity metadata differs"),
            ("PHP mismatch", self.descriptor, re.sub(r'"php": ".*?"', '"php": ">=9.9"', self.composer),
             "PHP minima must match"),
        )
        for case, descriptor, composer, reason in fixtures:
            with self.subTest(case=case):
                self.reject(self.set_metadata(descriptor, composer), reason)


if __name__ == "__main__":
    unittest.main()
