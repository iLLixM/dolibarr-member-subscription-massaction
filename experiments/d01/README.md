# D-01 experimental loaded-Core lab

Read the [versioned technical report](../../docs/d01-loaded-core-feasibility-v1.md).
Verdict: **D-01 CHANGES REQUIRED**. No product/module booking code is loaded here.
No complete Core-Guarded adapter, business qualification or release is supplied.

## Exact runtime and safety

Use pinned V24.0.3 Core `5c6c7ff6a5112413b1c27221c8d11750afad56f8` and PHP8.2.35
with mysqli/mysqlnd against loopback MySQL8.4.13. Existing artificial InnoDB table
`msmsh01lab.probe(id,label,n)` must be separately Owner-provisioned. Account
`msmsh01lab@%` has only SELECT/INSERT/UPDATE/DELETE on that schema; no P_S or admin
rights. `D01_CONFIG` may point to a private0600 app.json with host/port/database/user/
password fields; default `~/.config/dolibarr-sh01/app.json`. Endpoint/schema/account
are checked; credentials never appear in arguments, output or Git. No privileged
provisioning helper is included or called. `D01_CORE` selects the pinned source
checkout, without changing it or reading its conf.php.

The existing retained lab contains 54 new D-01 artificial rows plus27 historical
SH-01 rows. **Do not rerun writes here.** Reproduction needs separately approved
fresh lab state with the exact endpoint/account guards, no occupied4000–4499 IDs,
and a new evidence output area. Do not drop/reset old data or alter grants to fit.
The test account cannot access I105 business tables; no application bootstrap is
performed. The optional reference HTTP request is anonymous, no business action.

`core-lab.php` loads actual Core libraries and transactions. It overrides only
connection construction to instrument real native mysqli query calls; transaction
methods and query remain inherited. `factory.php` additionally checks exact factory/
driver/native classes without that override. The factory connection is not traced;
its independent reader is traced. Logger modules/handlers are disabled in this
minimal in-memory lab configuration, so arbitrary logging/hook SQL is out of scope.

Session SETs only establish per-test initial conditions on disposable connections;
no global change or product session rewrite occurs. SQL DML tracing accepts only
new D-01 artificial INSERTs/UPDATEs; no DELETE, DDL, USE, KILL or GRANT. The exact
factory control contains only the same hard-coded fixture operations. The lost-reply
proxy forwards opaque authentication in memory and discards exactly one selected
server end OK, without retaining packet/auth data. No other server connection is
interrupted. Adapted from SH-01's GPL helper at documentation commit
`7df49867e001f2f56ce95d04c8ce5c4bfbd582c3`; no marker/depth-model is reused.

## First-run procedure on a separately approved fresh lab

After source inspection and lint, run each phase once, inspect its result, and
stop for reconciliation if its output says STOP. Do not chain a retry or continue
blindly. `run.php` in this **original evidence version** intentionally retains the
last-case harness assertion bug; do not treat its STOP as an all-green result.
Fixes for future reproduction require separate evidence, not overwritten history.
The recorded follow-up `reconcile.php` only reads fixture4047 and old-row hashes;
it does not re-execute that failed case. Its PARTIAL classification stays.

```sh
php -d xdebug.mode=off experiments/d01/inspect.php --inspect experiments/d01/evidence/inspection.json
php -d xdebug.mode=off experiments/d01/run.php --run-approved experiments/d01/evidence/core-cases.json
# The recorded STOP was inspected and reconciled before proceeding:
php -d xdebug.mode=off experiments/d01/reconcile.php --read-only experiments/d01/evidence/stop-reconciled.json
php -d xdebug.mode=off experiments/d01/locks.php --run-approved experiments/d01/evidence/locks.json
python3 experiments/d01/lost-ack.py --run-approved COMMIT experiments/d01/evidence/lost-COMMIT.json
python3 experiments/d01/lost-ack.py --run-approved ROLLBACK experiments/d01/evidence/lost-ROLLBACK.json
php -d xdebug.mode=off experiments/d01/factory.php --run-approved experiments/d01/evidence/factory.json
php -d xdebug.mode=off experiments/d01/final-read.php --read-only experiments/d01/evidence/final-read.json
```

Inspection checks the current retained hash, not a requirement that SH-01 has been
replayed; no SH-01 experiment should be rerun. Write workers refuse existing output/
started markers and seed unique IDs; no auto retry. Output files should be in a new
reviewed checkout/evidence area. These markers are accident guards, not a general
multi-operator lock service. `lock-worker.php` and `lost-ack.php` are internal helpers,
not independent rerun commands. Do not run code through Apache.

The original first-run Core helper is archived as evidence/core-lab-first-run.php.txt;
the final helper adds only optional proxy-port routing, with unchanged default lab
endpoint. Synthetic labels, integer rows and SQL traces are safe to review; no
business dump, app.json, session, token, mail/PDF or raw protocol data is included.

## Read-only package verification

```sh
python3 experiments/d01/verify.py
sha256sum -c experiments/d01/SHA256SUMS
```

The verifier never connects to a database or executes PHP. Its successful result
means package/evidence consistency, not safety approval or closed Owner gates.
