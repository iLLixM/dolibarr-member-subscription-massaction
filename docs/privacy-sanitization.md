# Repository privacy and historical evidence handling (2026-11-21)

## Current privacy and historical-evidence guidance (post PR #38 – 2026-11-24)

The current source was independently reviewed and squash-merged as `0998fbaf58155cca04d31ab99c6bb47420e53c72`. Use the [new dolienv mapping](dolienv-environments.md) A MariaDB, B CoreGuarded MySQL, C EnhancedMySQL MySQL, D hosted; earlier A/C shared-schema and reversed A/B commands below are historical only. Continue to pseudonymize public current-tree docs; never publish private hostnames, URLs, account/database/login identities, paths, dumps, secrets or raw operator evidence. F32-MR-01 private target-user launcher and read-only preflight remain open; the original B UNKNOWN/STOP case is not eligible for retry. Nothing in this documentation authorizes real writes, deployment or release.

---

> Historical record follows. Current status is defined above.

> **Current environment convention — 2026-11-24:** dolienv **A = MariaDBSnapshot**,
> **B = CoreGuarded**, **C = EnhancedMySQL**, **D = hosted CoreGuarded**.
> [Current topology, bounded acceptance and privacy rules](dolienv-environments.md) control
> environment naming/readiness. Earlier CF03 labels, shared A/C bindings and dated
> evidence below retain their historical meaning; do not relabel/replay old tools.
> Current B finance acceptance is **BLOCKED (UNKNOWN/STOP)**; D has read-only
> browser/hosting-CLI evidence, not business-write qualification.


> **PR #32 final status (2026-11-21):** Current-tree pseudonymization/evidence tooling was **squash-merged** as `425980c43a51a9f27073133212f18825ac6da248`, reviewed source `cbc97843fb017135739a39ea12ef26c7fa66dfdb`. No permanent private-term blacklist is kept in source/CI. F32-MR-01/P2 target-user private environment launcher and F32-MR-02/P3 historic root-prose deviation were **expressly Owner-accepted, not technically fixed**. The latter's **actual runtime** boundary is canonical evidence parent with no group/other write, case 0700/file 0600, operator root for A/B/C and full A–D webroot/source exclusion. Current-tree anonymization **does not purge old Git commits or PR/Issue discussions**. Historic original artifact hashes do not certify the redacted bytes. See [current baseline](current-development-baseline.md).

## Current working tree

The public **current file tree** must not contain operator personal usernames, home paths,
private database schema/account identifiers, private hostnames or a personal contact email.
Source and documentation were pseudonymized as a one-time remediation. No
persistent list of private operator identifiers is retained in repository
code, tests or CI configuration. Future changes must use operator-neutral
placeholders rather than real local identities.

`tools/cf03-lab.lib.php` obtains A/B identities and the approved C account from private
operator-only environment values. `CF03_EVIDENCE_ROOT` supplies the real A/B/C evidence
parent outside Git. Secrets are **never** passed as command-line arguments; existing
trusted Dolibarr `conf.php` supplies the actual DB password. Missing mandatory
bindings STOP rather than guessing local paths or accounts. Existing A/C shared
schema and different effective DB accounts remain binding; do not run them concurrently.

The W01 evidence parent allows read/traverse access, but **group/other write bits
are rejected**. A case directory remains owner-restricted 0700 and evidence files
0600, with exclusive one-shot writes. This is not a security guarantee against the
trusted parent owner, privileged writers, uninspected network mounts, the same UID
or public HTTP aliases. Operators must separately attest such exposure.

## Historical evidence redaction and integrity

Earlier D01/AP3/CF02 documents and experimental scripts contained literal local
paths, database account/schema names and host identifiers. Their **current file
contents have been pseudonymized**. Pseudonymization changes the archived bytes:
original historical SHA-256 manifests, source pins, byte counts and any internal
checksums covering those original files MUST NOT be treated as valid for the
redacted *current* copies. In particular, historic approval and review claims
continue to describe **their immutable old pinned commits**, not a new cryptographic
attestation of redacted files. Do not silently restamp old verification results or
call redacted fixture data independently revalidated.

A private original evidence archive, if needed for audit, must be stored and
access-controlled separately by the Owner. Historical lab scripts with sanitized
synthetic paths are archived examples, not automatically eligible for real business
operations or destructive testing.

**Git history and existing GitHub issue/PR discussions are not scrubbed by a new
commit.** Their earlier commit objects or cached views may continue exposing old
labels. A genuine retrospective purge requires a separately authorized, coordinated
history rewrite and migration of SHA-anchored audit references; even then remote
forks and external mirrors cannot be guaranteed purged. Treat previously exposed
values as disclosed and rotate credentials if any actual secret was published.

## Phase 1 evidence-root recheck (2026-11-21)

The bounded permission decision remains unchanged: a trusted, canonical parent
may be readable/traversable by other users but must not be group/other writable;
the single-use case directory is 0700 and every file is 0600. The operator
must exclude HTTP aliases, untrusted ancestors, network mounts and
same-UID/privileged interference. These are environmental limits, not
claims of stronger physical isolation.

The CF03 witness now enforces exclusion of the live webroot, its site root,
its ancestors/descendants and the source checkout in ALL profiles A/B/C/D,
rather than only D. It rejects an unavailable realpath. It also opens each
one-shot evidence file under a temporarily restrictive process umask (0077),
restores the previous umask immediately after fopen and checks the new file
mode BEFORE the first data write. The earlier chmod(0600) after writing
remains a final check, not the first privacy boundary. These changes are
restricted to CF03 read-only evidence handling; they do not grant a business-
test or merge GO. Synthetic regression covers all profiles and umask 0000.

This is a one-time source-remediation policy, not a standing forbidden-term
dictionary. Independent code/evidence-scope review remains mandatory.
No source fingerprints, historical hashes or original evidence are restamped.

## Review scope and remaining limits

This privacy work does not change production booking/transaction/invoice algorithms.
It changes the CF03 read-only witness's local environment binding and evidence-root
permission contract. These changes require exact-HEAD synthetic CI and independent
security review before owner-approved PR merge. The existing POS02-A1/A2 PASS and
F32-W01-01 CLOSED refer to their earlier pinned source/evidence only; RB01-A-01
remains INVALID/STOP (normal booking persisted; no fault injection executed).
