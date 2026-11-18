# Phase A package finalization — 2026-11-18

Source baseline: `7781395a42f6ddfc2e8429fe48dc1191a6804ae2`, existing Draft PR #19,
branch `release/prepare-v0.1.0`. F19-01 and F19-02 from tracking Issue #20 are the
only changes beyond release documentation, DE/EN status text and package tooling.
No product logic, business API, descriptor, Composer, test or CI change is permitted.

The Owner confirms **Compatible with Dolibarr V22, V23 and V24.** Five synthetic
V23 PHP8.2/Core combinations remain included; no real V23 test is claimed or added.
Technical admission >=21, database minimums MariaDB10.11/MySQL8.4, PostgreSQL
exclusion, ID584620 and session/checkpoint architecture are unchanged.

## Current acceptance boundaries

The new artifact is a v0.1.0 distribution package, without RC labeling. This is
**not final release approval**. R0-04 exact-package installation/integration and
R0-07 independent release review remain **NOT EXECUTED / PENDING**. PR #19 stays
Draft; Issue #20 remains open. No installation, business operation, tag, merge or
GitHub Release is performed by Phase A.

Shipped product text/manifest is timeless; progress belongs here, in PR #19 and
Issue #20. Relative links connect files shipped together. Source-only references
use `blob/v0.1.0/` with existing repository target paths. The future tag has not been
created: its eventual HTTP availability must be checked at publication, and is not
claimed now. This does not depend on retaining the temporary release branch.

## Phase A checks and artifact identity

Local checks on the Phase A changes:

| Check | Executed result / evidence boundary |
| --- | --- |
| PHP syntax | PASS, all 36 existing PHP files on PHP8.2.35 CLI; no PHP source change |
| Existing synthetic regressions | PASS: standalone141 plus Issue8 contracts, database188, loading6; all eight Booking/MailDelivery/InvoiceProfile/Invoice/InvoicePDF/PostCommit/UserReload/Request runners on pinned V22.0.5 and V24.0.3 sources. No database, HTTP business, PDF or SMTP integration. |
| DE/EN | PASS: 124 matching keys; only MSMDevelopmentStatus changes; placeholders and other values unchanged |
| F19-01 | RESOLVED in the prepared artifact: timeless v0.1.0 docs/status text; no RC labels, candidate/status manifest fields or pending release-gate text in any payload/manifest; no README matches for the excluded names (case-insensitive) |
| F19-02 | RESOLVED in the prepared artifact: 16 relative links checked, six version-tag target paths checked against source, no temporary-branch link. Tag HTTP availability remains untested until publication. |
| V23 statement | Exact V22/V23/V24 compatibility statement retained in README and matrix; five synthetic V23 pairs preserved; no real V23 integration claimed or executed |
| Package integrity | PASS: unchanged 29-file allowlist plus manifest =30 entries; CRC/path/mode checks, all payload/source bytes and 29 size/hash entries, embedded/external manifest and ZIP sidecar validated independently |
| Reproducibility and security | PASS: byte-identical second build and separate copied checkout build; isolated symlink, traversal and unlisted runtime-file probes rejected |
| Scope | PASS: eleven changed documentation/language/build paths; product PHP/JS, descriptor/Composer, tests, CI, allowlist, requirements and historical campaign files byte-unchanged from 7781395 |
| Whitespace | git diff --check PASS |

The new output is `build-output/v0.1.0/module_membersubscriptionmassaction-0.1.0.zip`,
with freshly generated manifest/SHA sidecars and source attribution. It was fully
rebuilt, not renamed. ZIP SHA-256:
`61e4ad4bc40a31d87e265b0a05433403d77bbc177899c5f58b7416d5a3653ed6`.
Manifest SHA-256:
`ddb1b4a7fdf33c18d22eaff15fb114103cf44c486840eac8b2234e2998fd17d8`.
The prior package is preserved as historical evidence and must not be renamed or
used as the new qualification artifact. Its old SHA-256 is **not valid for the new ZIP**.

The exact new source HEAD, ZIP SHA and PR/push CI links are recorded in PR #19 and
Issue #20 after publication; they identify the new R0-04/R0-07 assessment inputs.
Any later payload change requires fresh exact-artifact assessment. New-HEAD
21-job PR/push CI must complete before Phase A completion is reported; final run
links/results are recorded in the PR/issue, separately from the earlier R0 runs.

Changed files: README.md, CHANGELOG.md, docs/compatibility.md, docs/installation.md,
docs/operations.md, docs/release-build.md, docs/release-notes-v0.1.0.md, this
validation record, both DE/EN language files and tools/build-release.py.
The initial independent helper stopped on a missing Python zipfile import before
archive validation; the corrected helper completed all checks without business work.

## Historical removed release-note status at 7781395

The following text is preserved as an earlier state, not the status of the new ZIP:

# Release notes — v0.1.0 candidate

**RELEASE CANDIDATE PREPARATION — NOT A PUBLISHED RELEASE.** Descriptor and Composer
metadata target `0.1.0`; no tag or GitHub Release is created. R0-04 integration of
the final ZIP and R0-07 independent release review remain **NOT EXECUTED / PENDING**.
The successful development baseline is PR #16 merge
`48d196cfb04421d775ab4e6757083f6a755121df`. Its independent implementation review
and post-merge synthetic CI are separate from the outstanding release gates.


## Historical release-acceptance text at 7781395


- **R0-04:** import the exact candidate ZIP on separately approved test installations;
  verify installed layout/version/ID collision handling, activation/deactivation,
  permissions, DE/EN and selected real preview/booking/invoice/PDF/mail/session
  paths for agreed V22/V24 and database reference profiles. Agree the concrete
  matrix and any business/SMTP operations separately; no such operation in R0.
- **R0-07:** independent review of the final source delta, package allowlist,
  manifest/SHA/reproducibility, licenses, current docs and R0-04 results; separate
  Owner decision before tagging, publishing or distributing a final release.

No general HTTP-load/replay/parallel/PDF/SMTP, database-version, production or
public-release approval is asserted. R0-03 structural validation is not a Dolibarr
import test. See [build instructions](https://github.com/iLLixM/dolibarr-member-subscription-massaction/blob/7781395a42f6ddfc2e8429fe48dc1191a6804ae2/docs/release-build.md) in the source repository.


## Historical R0 preparation record at 7781395

All statements below describe the original preparation and original artifact.
They do not extend those test results or checksum validity to the Phase A ZIP.

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
