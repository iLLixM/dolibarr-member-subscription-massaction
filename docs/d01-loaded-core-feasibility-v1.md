# D-01 loaded-Core / restricted-MySQL gate — v1

Date: 2026-11-18. Related to [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22).
Authority: Owner's separate D-01 execution instruction, after CF-00 acceptance.
Architecture/source baseline: `6247704815cf532ec632616011133c646aa00005` (PR #23
merge); current origin/main was still this commit. CF-01 PR #24 is separate and
not incorporated. No productive module code is changed or invoked here.

**Verdict: D-01 CHANGES REQUIRED**

The preferred **Preflight → SET TRANSACTION ISOLATION LEVEL REPEATABLE READ →
DoliDB::begin() → Locks → Reads** entry itself works on the tested stack, including
foreign-entry refusal and behavioral RR. However, `@@autocommit=1` before entry does
**not** suppress CHAIN/RELEASE behavior for these explicit Core transactions.
Regular Core COMMIT/ROLLBACK can acknowledge completion while leaving a chained
transaction or closing the connection. The next entry then fails with 1568/2006.
Before CF-02 contribution DML, the completion/next-member contract needs a bounded,
reviewed clarification as described below. This is not grounds to normalize session
settings or to reintroduce a completion-value admission whitelist.

## 1. Actually executed scope

| Item | Exact observation |
| --- | --- |
| Dolibarr | V24.0.3, Core `5c6c7ff6a5112413b1c27221c8d11750afad56f8` |
| PHP / driver | CLI 8.2.35 / direct mysqli, mysqlnd 8.2.35 |
| MySQL | 8.4.13, MySQL Community Server - GPL, loopback TCP 3307 |
| Isolated schema/account | `<PRIVATE_RESTRICTED_LAB>`, `<PRIVATE_RESTRICTED_LAB>@%`; existing Owner-provisioned SH-01 lab, new D-01-only rows |
| Grants | USAGE; SELECT, INSERT, UPDATE, DELETE on `<PRIVATE_RESTRICTED_LAB>`.* only; no roles/additional grants seen in SHOW GRANTS |
| P_S | Explicit negative preflight SELECT denied with native 1142 / Core `DB_ERROR_1142`; no P_S used by entry/tests |
| Initial session | autocommit=1, session REPEATABLE-READ, completion_type=NO_CHAIN |
| Table | Existing artificial `probe(id,label,n)`, InnoDB; no DDL or schema changes |
| Module baseline | `6247704815cf532ec632616011133c646aa00005`; no module booking/backend executed |
| I105 reference | HTTP 200, unauthenticated GET; V24.0.3 source; installed module checkout `0999efb4aafca43d90ce12e38d370662ad0c63a1`, not main/CF-01; no test writes to `<PRIVATE_MYSQL_SCHEMA>` |

Genuine Core `Conf`, `Translate`, `functions.lib.php`, `DoliDB` and `DoliDBMysqli`
are loaded. **No mocked Core transactions and no SH-01 depth/marker model.** Most
cases inherit constructor/query/begin/commit/rollback unchanged; only connection
creation substitutes a tracing native mysqli subclass whose query delegates to the
real extension. Reflection proves the method origin; every SQL query at that seam
is traced, including driver/ordinary Core side calls. Two additional real factory
controls use unmodified `getDoliDBInstance`, exact `DoliDBMysqli` and exact `mysqli`
classes without this seam. SQL order in those controls is corroborated by source
and the traced cases, not independently wire-captured on the exact factory handle.

This is **loaded Core database-library/driver integration**, not `main.inc.php`
application bootstrap, an authenticated business request, or a subscription booking
campaign. Genuine `dol_syslog` is loaded with no enabled log module/handlers in the
lab. SQL-backed/custom loggers and third-party hooks are not qualified by this
result. All initialization/configuration/engine queries finish before the critical
SET→BEGIN pair. Core/bootstrap and logger configuration differences must be covered
in the later actual adapter/approved installation tests.

Source hashes, method lines, versions and grants: [inspection](../experiments/d01/evidence/inspection.json).
Core/driver sources and all 27 old synthetic SH-01 rows are independently unchanged
at [final readback](../experiments/d01/evidence/final-read.json). New retained fixtures:
4000–4047, 4060–4061, 4080–4081, 4090–4091 (**54 rows**). The tests made no privileged
changes and never read administrative credentials or application conf.php.

## 2. Test matrix and observations

The [machine matrix](../experiments/d01/evidence/TEST-MATRIX.json) lists all individual
case IDs, fixtures, expected/observed results and evidence pointers. PASS below means
that a measurement/negative control met its stated objective, not blanket safety.
The main runner ends with a retained harness STOP, not an all-green campaign.

| Group | Cases / measured result | Scope / finding |
| --- | --- | --- |
| Normal entry/end | 2 traced + 2 exact-factory controls; SET succeeds, Core begin=1/depth1, no SQL between SET and BEGIN on traced handle; commit=1, rollback=true/depth0 | Artificial update invisible to independent reader before end; only commit persists it. |
| Core nesting | 2; nested begin returns1/depth2, nested commit/rollback returns1/depth1 without SQL; outer commit persists | Nested rollback is **counter decrement, not savepoint undo**. CF-02 must not mistake it for atomic inner rollback. |
| Isolation | 16; four session defaults × four preceding one-shot levels; D-01 overrides next isolation with RR | Consistent reads 0→0, current locking read1, later consistent read0; writer proceeds, unlike SERIALIZABLE read locking. Session variable is not actual-event proof. |
| Foreign transaction | 1; native BEGIN and uncommitted n=7 at Core depth0; D-01 SET=false/native1568, Core `DB_ERROR_1568`; no Core begin/commit/rollback | Independent reader stays0, originator still sees7; only the **fault-originator** later rolls back its own transaction. |
| Completion variants | 6; NO_CHAIN/CHAIN/RELEASE × commit/rollback; every initial entry and Core end acknowledged, persisted values match operation | **F-D01-01:** next SET succeeds only for NO_CHAIN; CHAIN→1568, RELEASE→2006. No automatic retry or foreign cleanup. |
| Intervening SQL controls | 2, deliberately invalid ordering | Constant SELECT1 retains RR here; an InnoDB SELECT between SET and BEGIN consumes it: subsequent reads0→1 under session RC. No exception to the no-intervening-SQL rule is inferred. |
| Coordinated lock/read view | 2, separate OS processes/connections | Correct sequence B waits ~0.468s for A commit, locking and subsequent consistent reads both1. Pre-lock ENGINE metadata control waits ~0.471s but consistent read remains0 while locking read sees1. |
| Later replacement blind spot | 1; deliberate native premature COMMIT + RC replacement BEGIN, same handle/Core depth | Core commit acknowledges; reads0→1. **Expected undetectable limit** without a continuous physical observer. Entry is not ongoing identity/isolation proof. |
| Local SQL error | 1; missing-column native1054/Core `DB_ERROR_NOSUCHFIELD` after owned artificial UPDATE; one Core rollback=true, value0 | Bounded lab statement only; does not define CF-02's full admitted error map. |
| Own client close before commit | 1 **PARTIAL / harness STOP** | No acknowledgement is recorded; independent reconciliation confirms value0 and old rows unchanged. Exception/return was not serialized, so no retrospective full PASS. No rerun. |
| Real lost end acknowledgements | 2; proxy forwards one exact COMMIT/ROLLBACK and discards its real server OK | Core commit=0/depth1 and rollback=false/**depth0**, native2006; persisted9/0 for diagnosis. Both remain **UNKNOWN/STOP**, despite readback. No retry/compensation. |

Totals: **37 completed measurements/controls and one partial case**, not 37 proofs
of safety. The six completion measurements include the restrictive actual outcomes.
[Main trace](../experiments/d01/evidence/core-cases.json),
[lock traces](../experiments/d01/evidence/locks.json),
[factory controls](../experiments/d01/evidence/factory.json),
[lost COMMIT](../experiments/d01/evidence/lost-COMMIT.json),
[lost ROLLBACK](../experiments/d01/evidence/lost-ROLLBACK.json).

Session-default/completion/timeout SETs are **test stimuli on newly opened,
disposable lab connections**, before preflight/entry. They are not production
workarounds. The D-01 entry recipe sets only the next transaction's isolation.
No global settings were written. Connections are closed; no old rows are reset,
truncated or deleted. The foreign context is never ended by the admission guard.
The intentionally replaced transaction is owned by that fault-injection case;
this is not a general replacement-recovery algorithm.

## 3. Required bounded clarification before CF-02

**F-D01-01 — Explicit Core completion under CHAIN/RELEASE.** Do not infer physical
idle, a reusable connection, or permission to begin another member from Core depth0
and a successful end. Preserve successful acknowledged *member* outcome separately
from subsequent connection admission. On the observed next SET1568 or connection
error2006, stop before BEGIN/DML; do not drain counters, roll back chained/foreign
contexts, reconnect-and-retry the member, or change session/global completion values.

A precise CF-02 contract and independent review must decide whether these outcomes
mean a documented bounded stop after that member, or whether ordinary Core completion
needs an explicitly authorized, separately qualified change for repeated-member
operation. An explicit `AND NO CHAIN NO RELEASE` end via an appropriate Core API is
only a **candidate for review**, not implemented/approved here. There is no such
parameter in the inspected `DoliDB::commit()` / `rollback()` signatures. Do not bypass
Core ownership or create a second controller to make the test green. No completion
value whitelist or automatic downgrade is proposed.

**F-D01-02 — Failed end diagnostics.** Core query intentionally does not refresh
`lasterrno` for COMMIT/ROLLBACK. The failed rollback additionally resets its logical
counter to0. Capture the end return/native error immediately and latch UNKNOWN/STOP;
never infer a confirmed rollback from that counter or plausible later data. The two
real discarded-response cases establish this requirement, not a general fault map.

The entry order itself requires no replacement: retain D-01 exactly, and perform
all metadata/logging/lazy-load SQL first. Locks must precede snapshot-establishing
engine/business reads. These proposed clarifications remain within ADR-08/12;
this report neither revises Owner decisions nor self-approves a CF-02 adapter.

## 4. Evidence limitations and unexecuted scenarios

- **NOT EXECUTED:** full main.inc.php/authenticated adapter/business integration,
  actual contributions, Agenda/customer/invoice/PDF/SMTP operations, CF-03 regression,
  module profile changes, migration, HTTP replay, V22/V23 loaded-Core execution,
  shared-hosting provider tests, other PHP/MySQL versions, mysqlnd-independent clients.
- **NOT EXECUTED:** SQL-backed/custom loggers, arbitrary hooks, server crash/restart,
  foreign KILL, deadlock matrix, exhaustive timeout/error mapping, automatic reconnect,
  network partitions and all completion/isolation combinations beyond the stated cases.
- No physical event/isolation/transaction identity proof, marker/SAVEPOINT hypothesis,
  persistent journal or Enhanced Observer is used. A later same-connection switch may
  remain invisible. Independent synthetic row readback is diagnostic, not recovery.
- Initial read-only helper attempts needed native-vs-Core error-number correction
  and `label` column correction before any fixture/write case. The first main run's
  last assertion mistakenly counted its own independent reader SELECT as unexpected
  extra trace. [Read-only reconciliation](../experiments/d01/evidence/stop-reconciled.json)
  preserves the distinction; the original STOP/result and first helper source remain.
  No real write case was rerun.

## 5. Reproduction and integrity

[Lab instructions](../experiments/d01/README.md) describe separate fresh lab
provisioning, the exact commands, no-rerun markers, tracing seam, failure handling
and private config boundary. Do not execute this again against the retained IDs.
[Manifest](../experiments/d01/MANIFEST.json) and
[SHA-256 list](../experiments/d01/SHA256SUMS) cover the report, helpers and sanitized
synthetic evidence; the [read-only verifier](../experiments/d01/verify.py) checks
completeness, hashes, SQL order, fixtures, expected outcomes and evidence distinctions.

This is a limited technical feasibility gate with a required completion-contract
follow-up, not CF-02 implementation, independent acceptance, CF-03 completion,
production approval or release qualification. Issue #22's later gates remain open.
