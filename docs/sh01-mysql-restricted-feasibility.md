# SH-01 — Restricted MySQL shared-hosting feasibility

Assessment date: 2026-11-18. Source base: `a74edb29ba6ca49af46c389f98c4bfd86cf161b6`.
Study branch: `spike/sh01-restricted-mysql`. Binding workstream:
[Issue #21](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/21).

**Recommendation: NO-GO for SH-02 on the evidence from the tested construction.**
The combination of standard mysqli identity, next-transaction isolation setup and
SAVEPOINT markers does not supply the unchanged ownership, isolation and exact
completion proofs. Positive primitive tests do not make an equivalent observer.
This is a bounded rejection of the evaluated design, **not a theorem that every
conceivable SQL/protocol construction is impossible**. A different construction
remains unproved and requires a new explicit proof/review, not implementation by
assuming weaker guarantees. No production adapter or SH-02 architecture is supplied.

**SH-01 FEASIBILITY ASSESSED – PENDING INDEPENDENT ARCHITECTURE REVIEW AND OWNER GO/NO-GO**
This assessment is not independent security approval and does not close Issue #21.
Its release-blocking Owner decision remains in force; this study does not authorize
merging PR #19 or publishing v0.1.0.

## 1. Scope, reference and privilege boundary

The [binding safety section](../experiments/sh01/evidence/issue21-contract-snapshot.md)
is preserved verbatim. None of its contracts is waived by this study.

| Evidence | Actual scope |
| --- | --- |
| Preferred reference | Unauthenticated `http://i105-v24.localhost:8091/` via `127.0.0.1:8091` plus the correct Host header: HTTP200 and Dolibarr24.0.3 marker. Direct Python hostname resolution failed (`gaierror`); no hosts/DNS change. |
| Reference source | Core `5c6c7ff6a5112413b1c27221c8d11750afad56f8`; existing I105 module `0999efb`, not the release HEAD. Its observer bytes equal the analysis base. No module deployment or authenticated business request. |
| Real experimental server | Oracle MySQL Community Server8.4.13, PHP8.2.35 mysqli/mysqlnd, loopback TCP3307. Existing server/container reused without restart or instrumentation/config changes. |
| Isolation | New database/account `msmsh01lab`, one artificial InnoDB `probe` table. Owner executed the separate reviewed provisioning starter. No imported Dolibarr/business data. |
| Proven application privileges | Only global USAGE plus SELECT/INSERT/UPDATE/DELETE on `msmsh01lab`.*; no roles/admin grants. `events_transactions_current` SELECT denied1142. `innodb_trx` diagnostic denied1227. |
| Final artificial state | 27 rows: two original seeds unchanged, 25 new artificial fixtures retained for review. No automatic cleanup/reset/drop. |
| Existing installation | No worker SQL targets `dolibarr_i105_v24` or existing PoC rows. No complete before/after audit of that database was performed; do not infer global quiescence from this scope statement. |

Private application credentials remain outside Git, mode0600. The agent used only
the new application account, not administrative credentials. Owner provisioning
created only the new schema/account/test table and did not add privileges to an
existing account. The laboratory table is **not** a proposed persistent module
table, migration or operations journal. No new PHP extension was installed.

## 2. Current implementation and Core contracts

The [source audit](../experiments/sh01/evidence/source-audit.json) binds hashes of
`class/databasestate.class.php`, `class/booking.class.php`, `lib/booking.lib.php`,
`subscription.php`, observer fixtures and tests to the analysis base. It also checks
all15 pinned V22/V23/V24 Core sources from the existing21-job matrix: the extracted
`DoliDB::begin`, `commit` and `rollback` bodies are identical across those sources.
This is source evidence, not15 live integrations. Representative
[Core transaction methods](https://github.com/Dolibarr/dolibarr/blob/5c6c7ff6a5112413b1c27221c8d11750afad56f8/htdocs/core/db/DoliDB.class.php#L216)
use logical nesting counters. Nested begin/commit/rollback do not create independent
SQL savepoints; the inner rollback merely decrements depth. At outer depth Core
issues SQL, and rollback resets its counter even when its SQL result is false.

Booking ordering at the analysis base:

1. `beforeBegin()`; engine preflight `checkTables()`; Core `begin()`.
2. `afterBegin()` and actual-own-event isolation admission; member/type and optional
   customer/product locks; second engine check **after locks**, before business reads.
3. Rights, eligibility, fingerprint/overlap and stored-value/R16 checks; fresh owned
   checkpoints around Core subscription/customer/invoice work.
4. Fresh owned checkpoint; Core `commit()`; `completed('COMMIT', ack)` before success.
   Rollback requires a fresh owned rollback context and its expected completion.
5. Only confirmed business commit permits later PDF/mail. Uncertainty latches stop;
   it cannot be redeemed by a later idle result or a successful query.

`msmRequireBookingDatabase()` also calls `beforeBegin()` during preview/confirmation
readiness; done/processing results bypass new-operation readiness. Therefore putting
`SET TRANSACTION` into that observer API is **not a read-only drop-in change**: it
changes the next transaction's settings during non-booking requests too.

### Required safety matrix

| Requirement | Existing instrumented MySQL | Existing MariaDB | Tested restricted hypothesis |
| --- | --- | --- | --- |
| 1. Idle before BEGIN | Native identity, depth0, fresh completed InnoDB-read event, autocommit1 | Native handle/ID, depth0 and `@@in_transaction=0` snapshot | SET TRANSACTION succeeds in tested idle context, rejects active1568; mutates next settings; no ended-event identity |
| 2. Own physical start | New ACTIVE event after calibrated idle, depth1, fresh counter checkpoint | Own begin acknowledgement, depth1 and active snapshot; weaker physical continuity | Marker create/release demonstrates a transaction in controlled test; no server-owned transaction token |
| 3. Identity/continuity | Bound native handle/server/thread/event plus fresh save/release counter progress | Stable native handle/ID and current active state; replacement may be invisible | Handle swap caught; retained marker catches tested replacements; re-creation/renewal examples invalidate equivalence |
| 4. Core nesting | Caller depth must return to1; same physical event | Same depth policy, current status only | Source-compatible counter MODEL works on MySQL; not actual Core integration or a physical identity proof |
| 5. Unexpected endings | Endpoint must remain the same ACTIVE event; wrong ended event/state rejected | Only endpoint status; COMMIT/BEGIN can restore active and evade snapshot | Retained marker detects ordinary endings, not a re-created marker or all renewal interleavings |
| 6. Actual isolation | Reads own ACTIVE `ISOLATION_LEVEL`; only RR admitted for business | No corresponding active-isolation enforcement in current MariaDB branch | Controlled immediate SET→BEGIN shows RR-style reads; override and actual pre-BEGIN metadata prefix yield non-RR reads |
| 7. Safe rollback | Fresh owned context before rollback, same event ROLLED BACK afterward | Owned logical/active snapshot then idle snapshot; not event-bound | Marker evidence insufficient for equivalent ownership/completion; no confirmed product rollback claim |
| 8. Commit/uncertainty | Ack plus expected own event COMMITTED, then separate fresh-idle proof | Ack/depth and idle snapshot, no ended-event identity | Ack/marker disappearance do not type/bind completion; strict prototype always returns UNKNOWN/STOP at completion |
| 9. Loss/reconnect | Identity mismatch/error stops; no context inheritance or retry | Native mismatch/error stops; weaker unchanged-handle history | New handle rejected; two real dropped replies produce client2006, stop and zero further guarded DML |

Existing MySQL checkpoints are endpoint evidence under the documented
instrumentation/native-connection profile, **not continuous monitoring**, durable
recovery or generic atomicity of arbitrary external hooks. MariaDB's separately
accepted snapshot limits are not permission to weaken a new MySQL backend.
Historical G0/I105/FIX01/I107/MDB gates in
[the validation history](phase4-implementation-validation.md#mysql-iteration-1)
remain unchanged; I107-FU-01's diagnostic distinction remains open.

## 3. Threat model and alternatives

Trusted: the MySQL server, PHP/mysqli and honest SQL results, the explicit fixture
scope, and normal OS/process isolation. Fabricated SQL results or arbitrary malicious
PHP code able to rewrite the observer itself are outside both designs' guarantees.
In scope: unexpected SQL on the shared native connection through Core/extensions,
transaction replacement, one-shot isolation changes, native-handle replacement and
missing responses. Other connections can change business rows but cannot normally
replace this connection's transaction; row locks/revalidation remain a separate duty.

| Variant | Evidence / limit |
| --- | --- |
| Stable mysqli + CONNECTION_ID | Necessary connection binding; same connection hosted both old and replacement transactions in POC-05. Not a transaction token. |
| Unpredictable retained marker | Detects removed/expired markers1305. Same-name replacement is accepted if the fault actor knows the name. DoliDB logs queries and retains public `lastquery`; a nonce is not automatically a secret from same-process collaborators. No forged result required. |
| Release/re-create marker | POC-06 rotation-gap accepts COMMIT/BEGIN injected between those two native calls. This is an explicit inter-call fault model, **not an observed spontaneous Core interleaving**. Paired markers/serialization might narrow that window but are unproved here and do not establish exact completion. |
| ROLLBACK TO marker | POC-06 alternative changes artificial n1→0: it validates by undoing intervening work. Not acceptable as a passive checkpoint after business DML. |
| SAVEPOINT success alone | It succeeds outside a transaction in this profile; later release fails1305. Success alone proves no physical start. |
| SET TRANSACTION RR | Conditional enforcement, not an actual active-isolation observation. The source-compatible metadata prefix consumes the intended next-transaction behavior before Core BEGIN when session default is RC. An ordinary InnoDB-read control gives the same non-RR outcome. |
| Session default / SHOW counters / mysqlnd statistics | MySQL rejects MariaDB's `@@in_transaction` with1193. Session default differs from observed active behavior. `Com_commit` increments equally for idle no-op and active COMMIT. Installed mysqli exposes no server_status property, no transaction counters in get_connection_stats, and no tested session-tracker getter. No event/ownership/completion proof. |
| Session transaction tracker | Setting CHARACTERISTICS succeeds. MySQL's C API provides tracker retrieval, but that is not an exposed equivalent on this tested standard mysqli interface. A different driver/protocol interposer is an unqualified architecture, not the laboratory reply-drop proxy as a production solution. |
| Other SQL diagnostics | INNODB_TRX1227 needs unavailable access. P_S session_status returned an empty matching result, **not**1142; no inference that all P_S tables are inaccessible. Exact events-table access remains denied1142. |
| Multi-statement transaction manager, XA/routines, durable sentinel table | Not implemented/tested. These would introduce different transaction-control/privilege or persistence assumptions; not a demonstrated equivalent observer within the unchanged Core API/no-table contract. |
| Switch backend after failure | Forbidden by Issue21. A permissive logical-depth/autocommit fallback is not proposed. |

The documented [SAVEPOINT rules](https://dev.mysql.com/doc/refman/8.4/en/savepoint.html),
[next-transaction settings](https://dev.mysql.com/doc/refman/8.4/en/set-transaction.html)
and [implicit-completion rules](https://dev.mysql.com/doc/refman/8.4/en/implicit-commit.html)
explain the tested primitive boundaries. Primary API references:
[PHP connection statistics](https://www.php.net/manual/en/mysqli.get-connection-stats.php),
[MySQL session tracking](https://dev.mysql.com/doc/refman/8.4/en/session-state-tracking.html),
[C tracker API](https://dev.mysql.com/doc/c-api/8.4/en/mysql-session-track-get-first.html).
These references describe primitives; they are **not an equivalence proof**.

## 4. Actually executed test matrix

28 native subcases cover all11 requested POC groups. A successful measurement means
that its documented observation was reproduced; it is **not 28 safety PASS results**.
Per-subcase preconditions, actual SQL actions, expectations, observations and security
assessment are in [TEST-MATRIX.json](../experiments/sh01/evidence/TEST-MATRIX.json).

| ID | Test action / expected boundary | Observed result / security assessment |
| --- | --- | --- |
| POC-01 | SET RR on fresh idle connection | Accepted; conditional admission primitive only |
| POC-02 | BEGIN, marker checkpoint, real DML, independent visibility, rollback | Checkpoint accepted; independent reader0 before/after rollback; not unique transaction ownership |
| POC-03 | Foreign explicit BEGIN or autocommit-off | Refused before guarded DML (1568 / mode check); no observer rollback |
| POC-04 | Unexpected COMMIT and ROLLBACK | Next marker check1305; independent values1/0; detects tested cases |
| POC-05 | COMMIT/BEGIN, ROLLBACK/BEGIN, implicit BEGIN and autocommit0→1 | Same connection retained; old marker1305. No full history guarantee |
| POC-06 | Remove/re-create/rotate marker; ROLLBACK TO alternative | Removed rejected; replacement+same-name accepted; injected renewal gap accepted twice; rollback-to undoes work. **FAIL for tested hypothesis** |
| POC-07 | Session/one-shot defaults; late override; source-compatible engine prefix | Immediate forced-RR tests stable0→0 despite RC default; late override and pre-BEGIN engine/read prefixes yield0→1. **FAIL for unqualified actual-RR claim** |
| POC-08 | Nested counter model over actual MySQL | Logical inner calls preserve outer context; inner rollback does not undo SQL. **MODEL**, not loaded Core integration |
| POC-09 | Close old handle; new connection attempts inheritance | Refused; IDs changed59→60 in retained trace. No transparent reconnect or ownership transfer |
| POC-10 | Expected/no-op completion after premature opposite operation; real COMMIT/ROLLBACK reply loss | Ack+missing-marker+autocommit signatures match despite opposite data outcomes. Proxy forwards one target, discards its server OK; native client2006. Committed lab row190=1, rolled-back191=0. Guard stays UNKNOWN/STOP |
| POC-11 | DML/probe/completion attempts after latched uncertainty | Guard blocks all further SQL; no retry/rollback/redeeming state transition. Safe refusal alone is not useful booking support |

For the no-op completion comparison, matching **chosen endpoint signatures** do not
prove that every possible SQL diagnostic is indistinguishable. They refute promoting
those particular observations to an exact own-completion guarantee. The strict
experiment intentionally refuses normal completion too; always stopping is safe
but does not meet the goal of a usable qualified additional backend.

The reply-loss proxy is a real packet-forwarding laboratory fault on a dedicated
loopback client, not a false return injected after an acknowledged call. It stores
no authentication/network payload. Its observed server OK is an independent test
oracle; it is **not delivered to or silently used to redeem the observer**. No
KILL, server restart, foreign socket or server setting was involved.

## 5. Evidence quality, anomalies and unexecuted scope

- **Synthetic:** existing observer suite188 assertions PASS; Core logical-depth model
  is explicitly labeled. No synthetic output substitutes for native SQL observations.
- **Real native MySQL:** dedicated restricted account, artificial DML, independent
  readers/writers, source-compatible metadata ordering, native errors and actual
  withheld replies. This provides real evidence for the negative feasibility result.
- **Actual Dolibarr Core integration:** **NOT EXECUTED**. No separate full Dolibarr
  test copy was provisioned, and the existing database was not used for writes.
  Native prefix/counter models are not reclassified as live Core/member/invoice tests.
- The extra diagnostics helper initially STOPped because it incorrectly expected
  session_status to return1142. Actual empty results were reconciled **from the saved
  trace without rerunning SQL**; the original STOP and initial source are retained.
  The future helper expectation is corrected, not claimed as a second successful run.
- Final independent lab SELECTs retain both seeds and all27 rows. Retained committed
  artificial outcomes are intentional evidence, not product subscriptions.
- No MariaDB live rerun, full instrumented-MySQL campaign, V22/V23 business run,
  shared-provider/netcup change, arbitrary filesystem/hook failure, PDF, SMTP or
  business load test was executed. No positive SH-04/R0-04 result is inferred.

Reviewable traces normalize only experimental marker identifiers; original raw-file
hashes and transformation origins are in
[EVIDENCE-ORIGINS.json](../experiments/sh01/evidence/EVIDENCE-ORIGINS.json).
Raw artifacts remain local under ignored `build-output/sh01-evidence/`. No credentials,
real members, session tokens, dumps or authentication packets are exported.
[Reproduction and safeguards](../experiments/sh01/README.md) describe first-run-only
commands, the Owner boundary, the diagnostic reconciliation and manifest verification.

## 6. Decision and independent review handoff

| Criterion | Instrumented MySQL | MariaDB snapshot | Restricted construction tested here |
| --- | --- | --- | --- |
| Privileges | Schema rights + one P_S SELECT; stable instrumented profile | Schema rights and session-status support | DML-only achieved; no events-table SELECT/admin rights |
| Ownership | Native/event-bound with freshness checks | Logical/active snapshot, accepted bounded limitations | Marker/connection primitives, not equivalent proof |
| Continuity | Same event across owned checkpoints | Snapshot only | Counterexamples for retained-name replay/renewal model |
| Isolation | Actual ACTIVE event value admitted | No equivalent actual-event gate in this branch | Conditional SET works; actual-ordering counterexample |
| Completion | Expected own ended event + ack + fresh idle | Ack + idle snapshot | Exact event/type unavailable; strict result UNKNOWN |
| Failure reaction | Latched unknown, no retry/foreign rollback | Existing accepted snapshot policy | Stop preserved; no functional confirmed-completion path |
| Residual risk | Endpoint/instrumentation/trusted-driver limits; not a journal | Weaker continuity already documented | Additional assumptions would weaken/change required proof if accepted silently |

**No GO and no SH-02 implementation proposal.** The experiment does not justify
accepting those additional assumptions or borrowing MariaDB's historical risk
acceptance. Keep both existing backends unchanged and keep the restricted-host
failure closed. Product support and the v0.1.0 release strategy must not be widened
by merely marking this spike complete.

Independent senior transaction/database review should check: (1) the exact threat
boundary for shared-connection SQL and nonce visibility; (2) the metadata-prefix
isolation result against the real caller sequence; (3) whether any admissible,
non-destructive mechanism can bind completion to the exact owned transaction using
only the allowed APIs; (4) reply-loss latching and evidence provenance. A materially
different proof must be separately commissioned; there is no permission here to
change protections, introduce a transport/proxy dependency, add a journal or start SH-02.

Owner options after review: retain the existing required P_S grant if actually
available, explicitly revisit the restricted-host/release scope, or authorize another
bounded investigation. None is executed by this report. The source, tests, CI,
release branch/PR19, prepared ZIP61e4ad4… and existing database configuration remain
untouched by SH-01. No push, remote PR/issue edit, merge, tag or release is part of
this delivery.
