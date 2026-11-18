# R0 preparation validation — 2026-11-18

Scope: metadata, DE/EN display/status text, documentation and release build tooling.
Development base: `48d196cfb04421d775ab4e6757083f6a755121df` (verified origin/main,
PR #16 merged). Work is isolated on `release/prepare-v0.1.0`; existing live module
symlinks/test worktrees, Core checkouts, configuration, databases and Documents were
not changed. No business or delivery test was executed in R0.

## Local checks actually executed

| Check | Result / scope |
| --- | --- |
| PHP syntax | PASS, all 36 existing PHP files, PHP 8.2.35 CLI |
| Standalone contracts / translations | PASS, 141 assertions plus Issue8 limit/early-hook contracts; 124 DE/EN keys, identical key sets, all existing placeholders unchanged |
| Database observer protocol | PASS, 188 synthetic assertions; no actual database or network |
| Loading JS | PASS, 6 synthetic DOM assertions, Node18.20.4; no browser |
| Booking, maildelivery, invoiceprofile, invoice, invoicepdf, postcommit, userreload, request runners | PASS on PHP8.2.35 against pinned V22.0.5 `88ad132763be45e9620128c1830e41493e1cdd05` and V24.0.3 `5c6c7ff6a5112413b1c27221c8d11750afad56f8` sources; no live Core bootstrap/DB/PDF/SMTP. Included runner counts overlap, not unique test totals. |
| Requirements | PASS: exactly 71 IDs/18 columns in original order; first ten columns and every test/evidence assignment unchanged; only 53 implementation-status cells and 53 corresponding review-status phrases in coverage gaps updated |
| Original README history | PASS: complete former narrative/sources retained in development-history.md, with relocated relative links and historical preface |
| Product logic / tests / CI | Byte-preserved against base in class/, lib/, js/, test/ and .github/; descriptor differs only in version and ID comment; Composer dependencies/scripts unchanged |
| Documentation / whitespace | Local user-document link targets/fragments checked; git diff --check PASS; historical pending statuses explicitly scoped, final release gates still pending |

The descriptor remains ID584620, technical PHP>=7.4/Core>=21 with no new runtime
numeric database/version guard. Only module display name/status values changed in
DE/EN; no key/placeholder, permission, hook, constant or schema change.

## Candidate package and independent checks

Build: [procedure](release-build.md), explicit `tools/release-files.txt`, standard
Python3.11 only. Output under ignored `build-output/v0.1.0-rc1/`; no artifact is
committed. Numeric filename plus candidate-directory/manifest labeling follows the
inspected V24 installer naming/root rule. This remains a source/structure check,
not an import result.

29 allowlisted payload files plus embedded `PACKAGE-MANIFEST.json` = 30 entries.
A separately implemented local verifier checks ZIP CRCs, duplicates, regular 0644
modes, fixed timestamps, safe paths, single module root, explicit allowlist and all
29 payload sizes/SHA-256 against both manifest and source. External and embedded
manifests match byte for byte; outer checksum covers the manifest too. The package
has no tests/Git/cache/local configuration/credential/session/fixture/private
evidence files. A second build is byte-identical, including a separately copied checkout path.
Isolated negative probes rejected symlinked input, parent traversal in the allowlist
and a new runtime PHP file absent from the allowlist. Check results and final checksum
are in ignored `R0-INTEGRITY-RESULT.json` and the ZIP `.sha256` sidecar.

Exact source commit, package SHA-256 and full new-HEAD 21-job PR/push CI run links
are recorded in the R0 Draft PR after publication. The development base in the
manifest is not a claim that base contains the final release metadata. The manifest
per-file hashes identify the actual candidate inputs without a recursive commit/hash.

## Still unexecuted / not authorized by R0

- R0-04: final candidate ZIP import, activation/deactivation and agreed real release
  integration/permissions/DE-EN/business/delivery scenarios on approved profiles.
- R0-07: independent release review of final source, docs, package and R0-04 evidence.
- No final tag, GitHub Release, production deployment or general database-version,
  HTTP load, parallelism, crash recovery, PDF/SMTP approval.

The original campaigns, accepted risks and open I107-FU-01/F16-03/04/05 observations
remain unchanged; see [release notes](release-notes-v0.1.0.md). R0 completion is
conditional on the new-HEAD CI finishing successfully; its status is reported in
that PR, separately from historical runs.

## Changed-file overview

- User entry/identity guidance: `README.md`, `CHANGELOG.md`, `CONTRIBUTING.md`,
  `AGENTS.md`.
- Metadata: `composer.json`,
  `core/modules/modMemberSubscriptionMassaction.class.php`.
- Display/status translations: `langs/de_DE/membersubscriptionmassaction.lang`,
  `langs/en_US/membersubscriptionmassaction.lang`.
- Existing documentation consolidated: `docs/requirements.csv`,
  `docs/phase4-implementation-validation.md`, `docs/test-coverage.md`,
  `docs/phase4-invoice-implementation-plan.md`, `docs/phase4-invoice-architecture.md`,
  `docs/porting-plan.md`, `docs/p3-qualification-summary.md`.
- User release documents: `docs/compatibility.md`, `docs/installation.md`,
  `docs/operations.md`, `docs/release-notes-v0.1.0.md`.
- Historical preservation: `docs/development-history.md`.
- Build/validation: `tools/build-release.py`, `tools/release-files.txt`,
  `docs/release-build.md`, this `docs/release-preparation-validation.md`.

Build outputs/manifests/checksum reports remain local and ignored, outside the
committed source diff. No other repository paths are intended to change.
