# Reproducible v0.1.0 distribution build

Run from the source checkout with Python 3 (standard library only):

```sh
python3 tools/build-release.py
```

No Dolibarr bootstrap, network, database, package manager, activation, business
operation, PDF generation or SMTP transport is invoked. Output is ignored by Git:

```
build-output/v0.1.0/module_membersubscriptionmassaction-0.1.0.zip
build-output/v0.1.0/module_membersubscriptionmassaction-0.1.0.zip.sha256
build-output/v0.1.0/PACKAGE-MANIFEST.json
```

The version is 0.1.0. The numeric ZIP filename matches the inspected V24.0.3
installer rule in Core `htdocs/admin/modules.php` (filename check around lines
257–264; module-root lookup around 291–305 at Core pin
`5c6c7ff6a5112413b1c27221c8d11750afad56f8`). Retain the filename and module root.

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
checkout paths. The deterministic manifest records version, retained module ID,
development baseline and every payload's size/SHA-256. It omits its own recursive
hash; the outer ZIP SHA-256 covers the embedded manifest. The external manifest
must match the embedded copy byte for byte.

## Verification

- Build twice and compare ZIP hashes/bytes. Validate CRCs, unique single-root paths,
  absence of absolute/parent paths and symlinks, expected regular-file modes.
- Independently compare every archive payload to the explicit allowlist and source,
  every manifest size/hash, and the ZIP hash sidecar. Verify descriptor version/ID,
  Composer version/PHP minimum and complete runtime coverage.
- Check release text and links, review packaged files for secrets/private data and
  ensure no ignored or unwanted files entered the allowlist. Outputs are not committed.
- Record the source commit, package SHA and checks outside the archive after the
  source commit exists. The manifest's development baseline is not the build HEAD;
  its per-file hashes identify exact build inputs without self-reference.
- Run PHP lint, language parity, relevant synthetic runners and the full 21-job CI
  matrix. New build-HEAD results are separate from historical qualification.

Structural/reproducibility checks do not prove external-module import,
activation/deactivation or live business behavior. Installation and business
validation must be tied to the exact source/package hashes being assessed. A source
or payload change produces a new artifact identity and calls for reassessment.
The builder does not tag, publish or deploy the module. See
[installation](installation.md) and [release notes](release-notes-v0.1.0.md).
