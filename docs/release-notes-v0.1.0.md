# Release notes — v0.1.0 candidate

**RELEASE CANDIDATE PREPARATION — NOT A PUBLISHED RELEASE.** Descriptor and Composer
metadata target `0.1.0`; no tag or GitHub Release is created. R0-04 integration of
the final ZIP and R0-07 independent release review remain **NOT EXECUTED / PENDING**.
The successful development baseline is PR #16 merge
`48d196cfb04421d775ab4e6757083f6a755121df`. Its independent implementation review
and post-merge synthetic CI are separate from the outstanding release gates.

## Included functionality

- Member-list bulk action with interactive per-member date/amount preview,
  description placeholders, common start/calendar actions and overlap consent.
- Explicit confirmation, session-bound results, current rights/entity checks,
  stale/replay protection and one controlled transaction per member.
- Optional explicit customer creation, linked validated standard invoice,
  post-commit Core PDF and independent confirmation email; verified PDF required
  for standard invoice email. No payment or bank-entry creation.
- Configurable batch maximum, default 100, capped by Core's global mass-action
  limit; localized early oversize feedback before member/preview processing.
- MariaDB and Oracle MySQL transaction observation with profile-specific admission
  and fail-closed handling, retaining existing Core R16 end-date semantics.

## Requirements and release identity

[Compatibility](compatibility.md) is the authoritative current matrix: intended
Dolibarr V22–V24, technical Core admission >=21, PHP >=7.4, Owner minimums
MariaDB10.11/MySQL8.4, no PostgreSQL. Exact real V22.0.5/V24.0.3 profiles and 21
synthetic PHP/Core pairs are distinct. V21, future versions, other server patches
and extra configurations are not qualified just because admission accepts them.

**R0-02 Owner exception:** ID **584620** remains in the community/shared unreserved
range. No permanent ID has been reserved or substituted. Collision risk is
consciously accepted for this limited scope, and local collision checking against
all installed descriptors is an installation prerequisite. This supersedes older
instructions demanding reservation/replacement before any distribution; those
instructions remain historical in the development archive. No right ID, hook,
module constant, database object or technical PHP minimum is changed.

## Completed development gates and evidence boundaries

- V22 G2/G3/G4 bounded approvals and V24 C3 completion remain historical,
  profile-specific evidence. PR #6's independent final review and Owner decisions
  were completed; its original real baseline/evidence package remain separate.
- PR #13 merged after I107 and MDB follow-ups: I107-F01/F02/F03 closed; real
  MariaDB10.11.19/V24.0.3/PHP8.2.35 MDB-01–09 **9/9 PASS**, independently confirmed.
  MySQL's exact Community8.4.13 profile retains the original I1-05 failure and
  later FIX01/I107 success rather than rewriting the history.
- PR #16 merged; Issue #8 is closed. Independent review: **APPROVED FOR MERGE WITH
  DOCUMENTED SCOPE**. F16-01 **CLOSED – OWNER ACCEPTED**; F16-02 **RESOLVED WITH
  DOCUMENTED SCOPE – OWNER-CONFIRMED HTTP TEST**. The later manual HTTP100
  (100/100, about 6 seconds, no invoice/email) is separate from automated CLI100
  and independently reviewed evidence; no new independent HTTP traces are claimed.

See [validation history](https://github.com/iLLixM/dolibarr-member-subscription-massaction/blob/release/prepare-v0.1.0/docs/phase4-implementation-validation.md),
[test coverage](https://github.com/iLLixM/dolibarr-member-subscription-massaction/blob/release/prepare-v0.1.0/docs/test-coverage.md) and the archived
[development README](https://github.com/iLLixM/dolibarr-member-subscription-massaction/blob/release/prepare-v0.1.0/docs/development-history.md) in the source repository. Private
original evidence is not included in the installable ZIP.

## Accepted residual risks and open observations

R0-05 keeps session/checkpoints without a journal/new module tables, automatic
retry/resume/rebooking/reinvoicing/PDF regeneration/redelivery. Unknown outcomes
require manual reconciliation. Nontransactional external hooks and PDF/SMTP
side effects, session loss/crashes, parallelism and performance remain limited;
Owner acceptance does not grant general production/load approval.
I107-FU-01 and F16-03/04/05 remain non-blocking P3 observations; details and recovery
procedure are in [operations](operations.md). G3's historical SMTP Core121
uncertainty remains despite receipt; G3-W02 remains template configuration, not an
open module task. Previously unexecuted cases are not silently promoted to PASS.

## Remaining release acceptance

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
import test. See [build instructions](https://github.com/iLLixM/dolibarr-member-subscription-massaction/blob/release/prepare-v0.1.0/docs/release-build.md) in the source repository.
