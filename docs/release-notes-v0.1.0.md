# Release notes — v0.1.0

MemberSubscriptionMassaction provides a guided bulk subscription workflow with
optional Core customer, invoice, PDF and confirmation-email handling.
**Compatible with Dolibarr V22, V23 and V24.** The compatibility statement,
synthetic coverage and real integration evidence have the distinct scopes below.

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

[Compatibility](compatibility.md) is the authoritative matrix: compatible with
Dolibarr V22, V23 and V24; technical Core admission >=21, PHP >=7.4, Owner minimums
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

See [validation history](https://github.com/iLLixM/dolibarr-member-subscription-massaction/blob/v0.1.0/docs/phase4-implementation-validation.md),
[test coverage](https://github.com/iLLixM/dolibarr-member-subscription-massaction/blob/v0.1.0/docs/test-coverage.md) and the archived
[development README](https://github.com/iLLixM/dolibarr-member-subscription-massaction/blob/v0.1.0/docs/development-history.md) in the source repository. Private
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

## Package verification and operating scope

The distribution has one `membersubscriptionmassaction/` module root, an embedded
file manifest and an external SHA-256 checksum. See [installation](installation.md)
for verification/import and the [build instructions](https://github.com/iLLixM/dolibarr-member-subscription-massaction/blob/v0.1.0/docs/release-build.md)
for reproducibility. Archive integrity checks do not validate a site's installation
or business outcomes. No general HTTP-load/replay/parallel/PDF/SMTP, database-version
or unrestricted production guarantee is implied; use the documented profile limits.
