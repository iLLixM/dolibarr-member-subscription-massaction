# Reproducible v0.1.0 release-candidate build

Run from the reviewed source checkout with Python 3 (standard library only):

```sh
python3 tools/build-release.py
```

No Dolibarr bootstrap, network, database, package manager, activation, business
operation, PDF generation or SMTP transport is invoked. Output is ignored by Git:

```
build-output/v0.1.0-rc1/module_membersubscriptionmassaction-0.1.0.zip
build-output/v0.1.0-rc1/module_membersubscriptionmassaction-0.1.0.zip.sha256
build-output/v0.1.0-rc1/PACKAGE-MANIFEST.json
```

**RELEASE CANDIDATE**, not a final release. Version metadata is 0.1.0. The numeric
ZIP filename matches the V24.0.3 installer rule in Core `htdocs/admin/modules.php`
(filename check around lines 257–264; root lookup around 291–305 at Core pin
`5c6c7ff6a5112413b1c27221c8d11750afad56f8`). A `-rc1` suffix in its version part
would fail that rule. Candidate labeling is therefore in the enclosing directory,
manifest and release notes; retain the filename/root for actual import.

## Inputs and deterministic format

`tools/release-files.txt` is the explicit, sorted allowlist. Only its listed bytes
are packaged, plus the generated manifest. All currently present runtime PHP,
language and JS files are checked for allowlist coverage, never implicitly added.
Developer/test artifacts, repository metadata, local configuration, caches,
credentials, documents, sessions, fixtures and private evidence are excluded.
Whitelist expansion needs review. No symlinked source/parent or unsafe ZIP path is
accepted. The single archive root is `membersubscriptionmassaction/`; extracting
that root into `htdocs/custom/` yields the expected module installation layout.

Sorted entries use ZIP_STORED, fixed 1980-01-01 timestamps, regular-file 0644 modes,
no host UID/local path/clock/Git state and no compression-library dependency.
Given identical listed bytes and tooling, output is byte-identical across runs and
checkout paths. The deterministic manifest records candidate status, version,
retained module ID, development baseline and every payload's size/SHA-256. It omits
its own recursive hash; the outer ZIP SHA-256 covers the embedded manifest.
The external manifest must match the embedded copy byte for byte.

## Verification before review or R0-04

- Build twice and compare ZIP hashes/bytes. Validate CRCs, unique single-root paths,
  absence of absolute/parent paths and symlinks, expected regular-file modes.
- Independently compare every archive payload to the explicit allowlist and source,
  every manifest size/hash, and the ZIP hash sidecar. Verify descriptor version/ID,
  Composer version/PHP minimum and complete runtime coverage.
- Review packaged text for secrets/private data and ensure no ignored artifacts or
  unwanted files entered the allowlist. Output directories are not committed.
- Record the source commit, package SHA and checks outside the archive after the
  source commit exists. The manifest's development baseline is not the build HEAD;
  its per-file hashes identify exact build inputs without self-reference.
- Run PHP lint, language parity, relevant synthetic runners and the full 21-job CI
  matrix. New build-HEAD results are separate from historical qualification.

R0 structural/reproducibility checks do **not** prove external-module import,
activation/deactivation or live business behavior. R0-04 must use the exact verified
ZIP on separately approved installations. R0-07 must independently review that
package, source/docs and R0-04 evidence before the Owner's final release decision.
No tag, release, automatic publication, production approval or new test campaign is
performed by this builder. See [release notes](release-notes-v0.1.0.md).
