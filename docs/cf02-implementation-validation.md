# CF-02 – Core-Guarded contribution-only development evidence

**Current status (2026-11-20): READY FOR INDEPENDENT EXTERNAL REVIEW — OWNER TEST EVIDENCE ACCEPTED WITH DOCUMENTED LIMITS; INDEPENDENT REVIEW, MERGE AND RELEASE PENDING.**

The [current review handoff](cf02-review-handoff.md) and [Owner-accepted POS01-A/B evidence](evidence/cf02/pos01-owner-test-acceptance-2026-11-20.md) supersede the execution-status claims in dated earlier sections below. Those earlier statements intentionally retain their contemporaneous provenance and are **not** statements that live MySQL booking remains untested. The Owner accepts the absent POS01-B description marker **only as a documented review-entry exception**; the original witness STOP is retained.

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

## Current test matrix and provenance (Owner acceptance, 2026-11-20)

| Layer | Evidence / result | Limit |
| --- | --- | --- |
| Synthetic bounded adapter | 88 assertions PASS; nine-profile PR CI includes new runner | No physical event-identity guarantee |
| Durable local file-session / replay admission | 19 assertions PASS | No real crash/replay or multi-member HTTP qualification |
| Existing synthetic and pinned Dolibarr Core regression | Owner-run V24.0.3 synthetics PASS; [nine-profile PR CI #44723124036](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/44723124036) 9/9 PASS at `c2347f610efc1c4c160dd0b21ffaf095753e7e40` | Synthetic PHP/Core V22/V23/V24, not real DB nonregression across profiles |
| Restricted MySQL and loaded Core admission | Owner-run read-only B/C/D PASS, P_S 1142, selected InnoDB tables and NO_CHAIN | No system table privilege or arbitrary hook coverage |
| Real loaded-Core empty transaction | Owner-run one Core BEGIN/ROLLBACK PASS | No contribution or actual COMMIT in empty test |
| Actual CoreGuarded HTTP contribution + Agenda | Owner-run POS01-A and B persisted subscriptions #65/#66 and standard Agenda events; B had correct member, dates, amount, R16 end, user resource and only expected table-count deltas; browser reported "commit confirmed" | A date/marker deviation, B marker-only witness STOP; Owner accepted B marker exception for review entry |
| Real error injection, concurrency, CHAIN/RELEASE, Agenda OFF | NOT EXECUTED on CF-02 | Deferred explicit negative CF-03 gates |
| Real EnhancedMySQL / MariaDBSnapshot invoice non-regression | NOT EXECUTED on this CF-02 implementation | Historical different source/profile evidence exists; new CF-03 test required |
| Full optional 21-profile matrix | NOT EXECUTED on CF-02 | Nine-profile CI is accepted development baseline; full CI before later qualification as required |

The counts above are **not additive** where a runner invokes other runners. The original POS01-B witness reports `STOP / CF02SubscriptionPostconditionMismatch` because `note_contains_marker=false`; this is **not** reclassified as PASS. See [Owner disposition](evidence/cf02/pos01-owner-test-acceptance-2026-11-20.md).

Use tools/cf02-run-synthetic.sh for repeatable no-business-DB tests.
Use tools/cf02-lab-readonly.sh --check-only for a read-only SQL privilege/configuration inspection (no writes).

### Example restricted-MySQL operator invocation

    CF02_DB_FAMILY=mysql CF02_DB_NAME=dolibarr_i105_v24 \
    CF02_DB_USER=dolibarr_i105_v24_limited CF02_DB_HOST=127.0.0.1 \
    CF02_DB_PORT=3307 sh tools/cf02-lab-readonly.sh --check-only

Do not paste passwords, cookies, session identifiers, raw member records or private DB exports into CI or versioned evidence.

## Explicit remaining gates

The implementation is Owner-admitted **for independent external review only**, not for production or development merge. Before a development merge: inspect the effective diff, code-HEAD and documentation-HEAD nine-profile CI, PHP 7.4/8.2 pinned-Core contracts, security invariants, independent transaction review and the **separate Owner merge decision**. The 21-profile full workflow is a later full-regression option, not a prerequisite to begin external review.
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

    sudo -u www-data env CF02_DOLIBARR_ROOT=/home/marcel/dolibarr-i105-v24-mysql \
      /usr/bin/php -d xdebug.mode=off tools/cf02-loaded-core-entry.php --check-only

Only after verifying the output and intended target, optionally execute the
separate empty transaction mode with `CF02_ACK_EMPTY_TRANSACTION=YES`.
Do not repeat after an unexpected STOP, and never paste the Dolibarr conf.php
or passwords into the evidence or PR.

## Direct replay-latch follow-up and final initial CI (2026-11-20)

An additional service capability gate binds the MsmFileBookingCheckpoint object
to the original live confirmation phase and captures the expected member rows,
options, revision, owner and entity *before* session consumption. After durable
consumption it verifies the independently reopened file-session snapshot, and
latches each member's attempt once per controller/request. A new checkpoint
constructed from an already consumed session is not a valid booking capability,
preventing E15/UNKNOWN direct-service replay through freshly constructed objects.
The initial 15 file-session assertions were extended to **19 PASS** locally.

[GitHub PR-CI run #44711910617](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/44711910617):
**9/9 PASS** at commit `c2972cdefc73ca6dad7c57527ec3254c5bd3dc63`.
The first two CI attempts and their genuine synthetic fixture failures are retained
in the PR history; no rewriting of earlier evidence. This green CI is synthetic
and does not itself qualify authenticated HTTP or business contributions.

Operator testing must begin with the non-writing restricted-account read-only
script and the non-writing loaded-Core `--check-only` script. The optional
`--empty-transaction` mode runs only a single empty Core BEGIN/ROLLBACK with an
explicit environment opt-in; it does not issue member, customer or Agenda DML.
Real contribution/Agenda/invoice and concurrency qualification remain open.

## Stage-0 Owner-reported MySQL qualification (2026-11-20)

The independent lab operator executed synthetic runner B, restricted-MySQL privilege/engine inspection C and loaded-Core read-only preflight D at exact source HEAD `f205f9a7c2954670fe6f0e42fb78051a9ba6fa04`. All three reported PASS. The limited user had DML-only schema grants, no Performance Schema SELECT (`ERROR 1142`), `autocommit=1`, `completion_type=NO_CHAIN`; Dolibarr V24.0.3 and MySQL 8.4.13 were separately identified. Evidence provenance and the strict absence of a real Core BEGIN/ROLLBACK or business DML in the first three checks are recorded at [CF-02 Stage-0 lab evidence](evidence/cf02/stage0-owner-reported-2026-11-20.md). This is **Owner-reported, not independently repeated**.

## Owner-reported real empty Core transaction (2026-11-20)

The separate, explicitly opted-in `tools/cf02-loaded-core-entry.php --empty-transaction` execution under the `www-data` account on Dolibarr 24.0.3 with the previously validated restricted-MySQL user reported **PASS**, `completion=NO_CHAIN`, `can_continue=true`, and `failure=null`. It attempted one empty outer Core BEGIN/ROLLBACK sequence, without member/Agenda DML. The run does not include a separately captured SQL trace and did not itself print the exact worktree SHA; the script Git blob was unchanged across the immediately preceding documentation-only commits. Full provenance, original JSON and limits: [real empty transaction evidence](evidence/cf02/empty-core-transaction-2026-11-20.md). No Core-COMMIT or business-DML qualification follows.

## POS01 read-only witness and Owner-operated real HTTP gate (2026-11-20)

A new CLI tool `tools/cf02-contribution-witness.php`, pure comparison contract `tools/cf02-contribution-witness.lib.php` and `test/unit/cf02-contribution-witness.php` prepare a **single synthetic-member, contribution-only** positive integration case. The witness does not perform business SQL writes or HTTP calls; the actual one-time business action must be carried out by the Owner through the standard Dolibarr webfrontend. Before/after evidence and a one-shot marker are persisted outside the webroot. Exact prerequisites, commands and all known limits are documented in [POS01 lab runbook](cf02-positive-contribution-lab.md). **No real POS01 contribution, Agenda business DML or authenticated HTTP test has been executed on the new code by the author.** Independent acknowledgement is not inferred from persisted rows.

## POS01 identity adaptation for the Owner-created member (2026-11-20)

The Owner created technical member ID **56** with Dolibarr-generated reference **MEM2611-0056** but has no editable member-login field. POS01 now uses both `llx_adherent.rowid` and `llx_adherent.ref` as a mandatory exact identity pair (`--member-id=56 --member-ref=MEM2611-0056`) and retains synthetic name/email, entity, status, no third party and no prior subscription as independent protections. Login is no longer required to match the marker, but remains in the before/after snapshot so unexpected login mutations are still detected. Run the revised non-writing `--check-only` first; no business DML is authorized by this change. [Runbook](cf02-positive-contribution-lab.md).

## Review-entry disposition (2026-11-20)

**Owner acceptance:** the provided B/C/D, real empty Core transaction, POS01-A and POS01-B positive contribution/Agenda evidence is sufficient for an independent CF-02 review **with the missing POS01-B note marker accepted as a documented non-blocking exception**. The Owner explicitly waived a test rerun solely for that marker. A remains a procedural deviation; B remains script STOP even though its other checked real postconditions passed. No code/witness/CI changes, no altered stored subscription or overwritten evidence. Current authority and reviewer questions: [CF-02 independent review handoff](cf02-review-handoff.md). Independent review, CF-03 negative/other-profile tests, development merge and release remain open.
