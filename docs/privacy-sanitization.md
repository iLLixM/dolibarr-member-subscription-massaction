# Repository privacy and historical evidence handling (2026-11-21)

## Current working tree

The public **current file tree** must not contain operator personal usernames, home paths,
private database schema/account identifiers, private hostnames or a personal contact email.
A source-based fail-closed CI guard is implemented in `tools/check-private-data.py`. It scans
tracked text sources, documentation, snapshot text and experimental tools, including
historical material, for known private labels and personal home paths.

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

The public current-tree privacy scan is only a bounded denylist/pattern guard,
not a complete PII detector or history purge. It must be exercised on the
exact PR HEAD; independent code/evidence-scope review remains mandatory.
No source fingerprints, historical hashes or original evidence are restamped.

## CI coverage addition

The privacy scanner now also audits tracked historical one-shot marker
files, git configuration text, additional common source/log extensions
and Windows-style home paths. The offline regression in
`test/unit/privacy-guard.py` builds an isolated temporary Git repository,
verifies a harmless placeholder PASS and verifies forbidden personal,
Linux-home, Windows-home, marker-file, gitignore and account examples
STOP. It runs in the standard nine-job CI, optional 21-job CI and
targeted PHP8.3/V22 hosted workflow. Neither scanner nor test has
network, database or business-write access. This scanner cannot assert
that all identifiers are absent or remove old Git objects.

## Review scope and remaining limits

This privacy work does not change production booking/transaction/invoice algorithms.
It changes the CF03 read-only witness's local environment binding and evidence-root
permission contract. These changes require exact-HEAD synthetic CI and independent
security review before owner-approved PR merge. The existing POS02-A1/A2 PASS and
F32-W01-01 CLOSED refer to their earlier pinned source/evidence only; RB01-A-01
remains INVALID/STOP (normal booking persisted; no fault injection executed).
