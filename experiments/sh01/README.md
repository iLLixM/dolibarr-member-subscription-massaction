# SH-01 experimental lab — not a module backend

Read [the feasibility report](../../docs/sh01-mysql-restricted-feasibility.md) first.
Source base `a74edb29ba6ca49af46c389f98c4bfd86cf161b6`. Recommendation: **NO-GO for
SH-02 from this tested construction**; independent architecture review and Owner
decision remain required. **SH-02 is NOT AUTHORIZED.**
No code here is loaded by the module or in its allowlist.
Do not copy `ShMarkerHypothesis` into product code or treat positive primitive
checks as proof of ownership/completion. It intentionally always stops at completion.

## Safety and environment

Only Oracle MySQL Community8.4.13 on loopback3307, schema/account `msmsh01lab`,
PHP8.2.35 mysqli/mysqlnd, Python3 standard library. Application credentials are read
in memory from `~/.config/dolibarr-sh01/app.json` (0600), never output or committed.
An optional SH01_CONFIG specifies the file **path**, never a password; endpoint and
account remain hard-gated. Normal workers use no admin credentials or Core bootstrap.

The separate `owner-provision.py --run-approved` is **Owner-only**. It uses the
existing container's protected administrative client internally to create a brand
new schema/account and one artificial InnoDB table/two seeds. Only schema DML is
granted, no P_S/admin/global privilege. It refuses existing targets/previous attempts
and does not reset/drop/alter them. The Owner already ran it for the retained lab.
Do not execute it again here. Never run it through the application worker or add
privileges to existing accounts. It writes no password to process arguments/results.

The current lab and first-run markers are retained as evidence. **Do not repeat
write commands on it.** Reproduction below requires a separately approved pristine
lab with these explicit endpoint/schema guards, not cleanup or truncation of the
existing one. No automatic schema reset, backup/restore, server restart, KILL,
privileged instrumentation window or real business object is required/authorized.

## Reproduction on a fresh approved laboratory

After separate Owner provisioning, run from the spike checkout, using a new output
directory. Commands below are **first-run steps, not a continuation/retry recipe**:

```sh
mkdir -p build-output/sh01-evidence
php -d xdebug.mode=off experiments/sh01/inspect.php --inspect
php -d xdebug.mode=off experiments/sh01/run.php --run-approved build-output/sh01-evidence/native.json
php -d xdebug.mode=off experiments/sh01/session-diagnostics.php --run-approved build-output/sh01-evidence/session-diagnostics.json
python3 experiments/sh01/lost-ack.py --run-approved COMMIT build-output/sh01-evidence/lost-ack-COMMIT.json
python3 experiments/sh01/lost-ack.py --run-approved ROLLBACK build-output/sh01-evidence/lost-ack-ROLLBACK.json
php -d xdebug.mode=off experiments/sh01/isolation-ordering.php --run-approved build-output/sh01-evidence/isolation-ordering.json
php -d xdebug.mode=off experiments/sh01/rollback-to.php --run-approved build-output/sh01-evidence/rollback-to.json
php -d xdebug.mode=off experiments/sh01/final-lab-read.php --read-only build-output/sh01-evidence/final-lab-read.json
```

Read and inspect each result before the next command. On an unexpected STOP, retain
all state/results and reconcile; do not rerun automatically. These are single-process
lab accident guards, not a concurrency-safe multi-process test scheduler. `run.php` requires only
the two pristine seeds and refuses a prior start marker. Native SQL updates are
restricted to artificial IDs102–194; the final27 rows remain available for read-only
review. Session-only SETs affect solely each dedicated lab connection.

The proxy opens an ephemeral loopback socket for **one** dedicated client, forwards
one exact COMMIT or ROLLBACK, consumes its server OK and closes that connection.
Auth packets are opaque in-memory forwarding only: no packet dump, credential log,
external endpoint or other connection is involved. The worker's normal independent
reader verifies the artificial row; the client guard still returns UNKNOWN/STOP.
This proxy is fault instrumentation, **not a deployment or production backend**.

`ShDepthModel` mirrors the source's counters; it does not load Core. The read-role
trace label alone is not evidence of independence: compare connection handles and
actual writer/reader sequences, especially POC-07's own-transaction consistent reads.
No membership/invoice/Core business integration is claimed.

## Source and non-writing checks

```sh
python3 experiments/sh01/source-audit.py /path/to/Core-git build-output/source-audit.json
php -d xdebug.mode=off test/unit/database.php
```

Source audit reads all15 pinned Git objects without changing a checkout; it compares
the three Core transaction methods and hashes the unchanged module files. The
reference HTTP/source metadata check does not log in or query business objects.
These are not real Core integration tests. Missing objects/source paths must be
reported, not replaced by a different Core version or a live-site bootstrap.

## Retained diagnostics anomaly and evidence

The initial session diagnostics source incorrectly required error1142 for
`performance_schema.session_status`; actual SELECT returned an empty result.
`evidence/session-diagnostics.json` retains that original STOP and complete trace;
`evidence/session-diagnostics-initial.php.txt` retains its initial source. The corrected
helper is for later reproduction, **not a claim of a second original native run**.
The recorded outcome was reconciled without SQL by:

```sh
python3 experiments/sh01/reconcile-diagnostics.py build-output/sh01-evidence/session-diagnostics.json build-output/sh01-evidence/session-diagnostics-reconciled.json
```

This command applies only to that original STOP trace, not a newly successful run.

`evidence/TEST-MATRIX.json` contains28 subcases across POC-01–11 with prerequisites,
SQL actions, expected/actual outcomes and security assessments. Successful measurement
is different from satisfying a safety contract. Artificial marker nonces are normalized;
EVIDENCE-ORIGINS records original/review hashes. Private raw evidence remains ignored.
Export is explicit and allowlisted (`export-evidence.py`); it never copies app config.
The exporter reflects this recorded run, including its retained anomaly, not arbitrary
future test output. Review new output manually before exporting it.

MANIFEST.json and SHA256SUMS cover the report, scripts and review evidence; no
self-recursive checksum. Verify from the spike root with the included read-only
`verify-evidence.py`. No packaged v0.1.0 ZIP is rebuilt by these commands.
