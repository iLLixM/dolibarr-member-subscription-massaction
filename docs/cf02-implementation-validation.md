# CF-02 – Core-Guarded contribution-only development evidence

**Status: DEVELOPMENT – NOT YET INDEPENDENTLY REVIEWED OR LIVE-QUALIFIED.**

Branch: feature/cf02-core-guarded-contribution; baseline main: 4698ec1540110a6250ba95b8bf0e57689a5e9f8b.
Authority: [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22) and [ADR-0001](adr/ADR-0001-core-first-transaction-profiles.md).
The pre-CF-02 Completion Gate was independently assessed and Owner accepted with documented limits.

## Implementation boundaries

- CoreGuarded uses a new bounded MySQL coordinator, but the booking service remains the only owner of the outer Dolibarr Core begin/commit/rollback.
- The required D-01 order is finished preflight, SET TRANSACTION REPEATABLE READ, immediately Core BEGIN on the same connection, member/type locks, then checked business and Agenda operations.
- The new CoreGuarded transaction checks current native connection identity and Dolibarr depth. It does not read the Performance Schema, create journals, run compensation or perform a second transaction manager.
- Completion is classified before the Core end, never via a forbidden post-end SQL probe. Known CHAIN/RELEASE produce a terminal STOP after a confirmed Core end; confirmed member outcomes survive and following members are NOT_PROCESSED.
- Lost acknowledgement, connection/depth uncertainty, unexpected completion changes and unqualified SQL faults remain UNKNOWN/STOP. Only narrowly qualified business errors permit one normal Core rollback; no S SQL error mapping is claimed.
- Administrative CoreGuarded selection admits contributions only. Invoice/customer creation, PDF and mail are denied. Existing EnhancedMySQL and MariaDBSnapshot capabilities must remain available.
- Direct CoreGuarded booking service invocation requires a bound file-session checkpoint and independently reopened proof of the durably consumed run and specific expected member/options. The supported standard file logger check remains mandatory.
- Unsupported third-party hooks, other session handlers and other loggers are not universally qualified; no new permissions are requested.

## Test matrix and provenance

| Layer | Executed result | Qualification limit |
| --- | --- | --- |
| Adapter synthetic | Initial PHP 8.4 local run: 88 assertions PASS | No live loaded Dolibarr/MySQL or native connection |
| Durable file-session synthetic | Initial PHP 8.4 local run: 15 assertions PASS | Genuine isolated local session files, no HTTP/DB |
| Existing no-Core suites | run.php 141, database.php 188, loading.js 6 PASS locally | Existing synthetic contracts, not real integration |
| Pinned-Core suites | NOT EXECUTED locally | To be executed by GitHub PR CI and operator's V24.0.3 environment |
| Restricted MySQL read-only | NOT EXECUTED by author | Owner separately confirmed limited user and P_S 1142 |
| Real MySQL contribution/Agenda/rollback/HTTP/concurrency | NOT EXECUTED | Requires version-bound isolated synthetic fixtures and operator traces |
| Enhanced MySQL/MariaDB real invoice regression | NOT EXECUTED on this branch | Full CF-03 qualification remains required |

Use tools/cf02-run-synthetic.sh for repeatable no-business-DB tests.
Use tools/cf02-lab-readonly.sh --check-only for a read-only SQL privilege/configuration inspection (no writes).

### Example restricted-MySQL operator invocation

    CF02_DB_FAMILY=mysql CF02_DB_NAME=dolibarr_i105_v24 \
    CF02_DB_USER=dolibarr_i105_v24_limited CF02_DB_HOST=127.0.0.1 \
    CF02_DB_PORT=3307 sh tools/cf02-lab-readonly.sh --check-only

Do not paste passwords, cookies, session identifiers, raw member records or private DB exports into CI or versioned evidence.

## Explicit remaining gates

The current work is a development implementation, not production authorization.
Before an independent development merge: inspect the effective diff, full PHP 7.4/8.2 pinned-Core regression jobs, security invariants, external transaction review and Owner merge decision.
Before production/release: real restricted-MySQL contribution+Agenda ON/OFF, rollback and errors, concurrency, HTTP session/replay, EnhancedMySQL and MariaDB invoice non-regression, cross-version and package qualification.

Historical D-01, AP3 and F27 evidence files are unchanged; a new result must be separately versioned and SHA-256 bound to its exact tested commit.

## Automated PR-CI execution at integration HEAD (2026-11-20)

[GitHub PR-CI run #44711013746](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/44711013746) completed **9/9 SUCCESS** for commit `6d220c91c7c72ef86b0207069c86e52be84de124`. This included PHP 7.4 with Dolibarr V22.0.0/V22.0.5, PHP 8.2 with V22.0.5/V23.0.0/V23.0.4/V24.0.0–V24.0.3, all synthetic Core-Guarded adapter/file-session runners, and historical booking, controller, invoice, mail, terminal and request tests. The first two earlier PR-CI runs failed due to incomplete test double wiring (`SyntheticDatabaseState::beginAttempted`, missing `F28Service::coreGuardedReadiness`) and were corrected with follow-up commits; these historical failures are not erased. The new run confirms only the tested synthetic contracts. **Real HTTP, MySQL business DML and MariaDB/Enhanced regressions remain NOT EXECUTED.**

## Operator loaded-Core restricted MySQL empty-transaction gate

Added `tools/cf02-loaded-core-entry.php`. It loads genuine pinned Dolibarr Core
database methods and the existing local MySQL application configuration, without
HTTP/business bootstrap. The default `--check-only` performs bounded
read-only admission; `--empty-transaction` additionally requires
`CF02_ACK_EMPTY_TRANSACTION=YES`, performs exactly one Core BEGIN/ROLLBACK on
an empty own transaction, and **never** writes contribution/customer/Agenda DML.
It checks the exact restricted database/schema identity and fails closed.
This is a separately limited D-01 loaded-Core gate, **NOT** an authenticated
HTTP/Agenda contribution integration test. This script has been syntax checked
locally but has **NOT EXECUTED** against the real database by the author.

Run in the operator's isolated MySQL V24.0.3 installation, as the web-account:

    CF02_DOLIBARR_ROOT=/home/marcel/dolibarr-i105-v24-mysql \
      sudo -u www-data /usr/bin/php -d xdebug.mode=off tools/cf02-loaded-core-entry.php --check-only

Only after verifying the output and intended target, optionally execute the
separate empty transaction mode with `CF02_ACK_EMPTY_TRANSACTION=YES`.
Do not repeat after an unexpected STOP, and never paste the Dolibarr conf.php
or passwords into the evidence or PR.
