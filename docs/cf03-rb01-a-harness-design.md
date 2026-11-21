# CF03 RB01-A — Design, admission and assurance contract for a real CoreGuarded rollback

**State:** DESIGN / PREPARATION ONLY — NOT IMPLEMENTED, NOT EXECUTED, NO REAL-FAULT GO.
**Repository:** iLLixM/dolibarr-member-subscription-massaction, Draft PR #32, Issue #22.
**Design source:** reviewed application logic at `332f87810bdcc4301c5fc174dc7fac2e20dca986`. A future RB01 test-only code build requires its **own** exact SHA, source inventory and independent injection review.
**Companion:** [RB01-A operator runbook](cf03-rb01-a-operator-runbook.md).
**Historical positives:** [POS02-A1/A2 TECHNICAL PASS](evidence/cf03/CF03-POS02-A1-A2-technischer-Abschlussbericht-2026-11-21.md). Never reuse members 59/60, subscriptions 68/69 or those markers.

## 1. Precise test objective and scope

RB01-A demonstrates that the **actual Dolibarr Core subscription DML** executed by `MsmBookingService::book()`, inside a CoreGuarded-owned transaction, is undone by **one** Core `$db->rollback()` after a deterministic, **qualified business error** *after successful Core DML but strictly before the COMMIT attempt*. A successful read-only `no-change` verification alone is insufficient.

Scope: one new validated paid synthetic entity-1 member; one attempted contribution of EUR 120.00 for `2080-01-01..2080-12-31`; Member Agenda **OFF** initially; no invoice/customer creation, email, PDF or payment; MySQL 8.4.13/Dolibarr V24.0.3/PHP 8.2, CoreGuarded, autocommit=1, NO_CHAIN, restricted A-like DB user.

Not in scope: SQL exceptions, deadlocks 1213, timeouts 1205, transport/reconnect failures, nested transactions, lost COMMIT ACK, automatic retries, batch continuation, arbitrary third-party hooks, full filesystem atomicity, physical transaction-event identity or environments B/C/D. Those require ERR01/BATCH/other qualification.

## 2. Critical architecture facts (verified at the design source)

1. `class/booking.class.php`: `book()` calls `$member->subscription(...)` at lines ~227–231, then `verifyStored(...)` at ~233, then `beforeCompletion()` and `$db->commit()` at ~235–239. `verifyStored()` is **protected**, and uses real DB SELECTs to confirm the newly stored subscription and member `datefin`.
2. The CoreGuarded exception policy is **finite**: `coreGuardedBusinessRollbackAllowed()` admits `MSMChangedConfirmation`, `NotEnoughPermissions`, `MSMTransactionUnsupported` and `MSMConsistencyError`. These are qualified business exceptions; arbitrary SQL exceptions are **not** qualified for safe rollback. Catch invokes the existing `rollbackOwned(true)` once only when `owned && !committing`.
3. `rollbackOwned(true)` checks the owned ACTIVE context, calls `beforeCompletion()`, exactly one normal Dolibarr `$db->rollback()`, and classifies the bounded ACK/depth/connection result. This proves a **Core-acknowledged** rollback, not an independently identified physical InnoDB transaction event.
4. `subscription.php` constructs `new MsmBookingService(...)` at line ~38. The normal Web flow requires a genuine authenticated session, CSRF, a consumed preview and an unforgeable `MsmFileBookingCheckpoint`. Do **not** bypass this with a direct CLI service call, handcrafted session or replayed POST.
5. `tools/cf03-lab.php` is **read-only**; `--scenario=no-change` checks selected unchanged state and twelve table counts, but it does not inject an error, establish that Core-DML happened, check all rows/files or prove a physical ROLLBACK event.
6. `Cf03LabContract::environments()['A']` pins a concrete document root, MySQL schema, account, port, source-copy identity and PHP runtime. **Do not** override these to point at an unrelated clone. An isolated instance must preserve the required mapping in its own filesystem/network namespace, or use a distinct, independently reviewed RB01-specific read-only witness with accurate environment attestation.

## 3. Safe injection specification — separate test-only implementation required

**Approved candidate design for subsequent implementation review, not an installed harness:**

- Use a **disposable isolated clone** of A, never the active A/C shared schema, normal public HTTP entrypoint, production module installation or shared hosting D.
- In a **distinct test-only branch/build**, define a subclass of `MsmBookingService`. Only the isolated controller build may instantiate that subclass in place of the regular booking service; both modifications must be identified by reviewed SHA-256 hashes and published test-only code diff. There is **no source change to productive classes in PR #32**.
- Make `book()` fail closed **before Core BEGIN** unless the member ID, reference, exact marker, entity, profile, contribution-only options, session checkpoint and approved single-case allowlist match. No request/header/URL flag is sufficient by itself to enable injection.
- Override `protected verifyStored($id, $subscription, $member, array $row)` in the test-only subclass: call `parent::verifyStored(...)` first; confirm the one-shot whitelist and that the in-transaction Core-DML probe is positive; create an exclusive, private and minimal `injection-reached` evidence marker; **throw exactly `new RuntimeException('MSMConsistencyError')`**. This is after actual Core DML/verification and before the first COMMIT attempt. If the marker cannot be durably recorded, STOP as `INSUFFICIENT EVIDENCE`. Never return normally from this override for the target case.
- Injection should not perform any SQL writes, `COMMIT`, `ROLLBACK`, reconnect, schema DDL, raw SQL trigger, `KILL`, `SAVEPOINT` or change transaction/session settings. It must not disable the regular Core/session/rights/guard path.
- The separate injection marker is **outside** the `cf03-lab.php` evidence case directory: its one-shot verify requires exactly `baseline.json` and `baseline.sha256` before creating `verify.started`. Do not create ancillary files inside that case directory.
- Positive evidence: real parent `verifyStored()` success, exact case/connection/depth/stage trace, qualified `MSMConsistencyError` injection, bounded `rollback=confirmed` result and no COMMIT attempt. A generated subscription ID in a pre-rollback diagnostic is **not** proof of persistence.
- Negative harness tests must demonstrate no injection in ordinary production build, wrong member/marker/profile/session, reused one-shot token, missing isolation attestation, wrong source hash or absent private marker directory. The only authentic UI action must be a freshly confirmed single-member POST.

The test-only patch itself (including controller subclass selection and marker writer), isolation controls and any synthetic tests require an **independent security/integration review** before real use. A documentation-only approval does not grant this review.

## 4. Non-negotiable isolated environment

| Requirement | Admission proof |
| --- | --- |
| Dedicated cloned A-RB instance | Separate MySQL server UUID/volume, isolated network and application filesystem; **not** the A/C shared database |
| Recovery | Tested offline restore from disposable clone backup/snapshot, not a planned restore of active A/C |
| Accounts | Independently generated test credentials; effective restricted application account, no P_S SELECT (1142), no broad elevated grants in Web runtime |
| Versions/source | V24.0.3, MySQL 8.4.13, PHP CLI 8.2.x; reviewed base, exact test-only overlay/source and deployed hashes pinned |
| Profile/settings | CoreGuarded, `autocommit=1`, `NO_CHAIN`, InnoDB business and Agenda tables, Member Agenda OFF; no profile/setting changes inside evidence window |
| Isolation | Single operator, no scheduled tasks, background business writers or open public access; side-effectful third-party hooks disabled in this isolated clone and documented |
| Mail/files | Mail transport disabled at external boundary, no PDF/invoice selected; private evidence root 0700 and individual files 0600, no public aliases |
| Fixture | New synthetic ID/ref/marker/email, active paid type `statut=1`, EUR 120.00, `datefin=NULL`, no existing contribution/customer link |
| Executable test harness | Pre-reviewed test-only branch/build, one-shot injection and explicit prior case-specific Owner-GO |

A mere database export imported under a **different** schema/port/document root does **not** satisfy the hard-coded A witness: obtain an independently reviewed RB01 witness mapping or reproduce the pinned A mapping *inside the isolated namespace*. Never relax W01's production-facing admission rules to make a clone pass.

## 5. Evidence, independent reconciliation and expected outcome

- **Owner and build:** dated prior GO; isolation attestation; server UUID contrast; backup/restore rehearsal; tested source/harness SHA; deployment/module/core hash inventory; private root permissions; unique fixture ID and marker.
- **W01 support (read-only):** `--preflight` PASS; `--scenario=no-change --agenda=off`, `--check-only` PASS; one `--baseline`; after a single authorized UI action, one `--verify` with expected outcome `selected_database_state_unchanged`.
- **Separate rollback trace:** injection marker proves the dedicated test-only subclass reached the post-Core-DML checkpoint. Capture Core transaction ownership/depth and connection identity before/after the permitted Core rollback, qualified error, acknowledgement, controller UI result, whether COMMIT was attempted, and whether the marker was consumed. Keep sensitive details private.
- **Independent DB readback:** fresh **separate** SQL connection after request completion; exact fixture member fields and `datefin` unchanged; no subscription/Agenda/linked invoice for fixture; all **12** monitored table counts unchanged, plus selected-row/key snapshots or digests of Core and potentially touched tables, not counts alone.
- **Independent files:** inventory/hash comparison for selected business document paths and any known side-effect directories; distinguish allowable technical session/syslog/injection-evidence changes from forbidden business PDF/mail or other artifacts. Do not claim system-wide absence of side effects beyond inventoried paths.
- **Identity:** profile, DB account/schema/host/server UUID, config hash, Core and module source, PHP runtime and Agenda configuration unchanged **within** baseline/verify window.

**PASS only if ALL are true:** case GO and isolation proved; the deterministic probe proves real Core DML occurred in an owned transaction; `MSMConsistencyError` was thrown post-DML and pre-COMMIT; exactly one Core rollback was acknowledged with bounded stable identity/depth; `no-change` one-shot verification PASS; independent row-level/count/file evidence supports no persisted business modifications; no unanticipated output, mail or follow-on booking.

**FAIL:** any persisted unintended business change, a confirmed COMMIT or policy violation. **INSUFFICIENT EVIDENCE / STOP:** absent injection marker, ambiguous checkpoint/connection/rollback acknowledgement, missing snapshots/owner record, interrupted observation, unqualified error or incomplete filesystem boundary. Do not silently equate `no-change PASS` with `RB01-A PASS`. No retries, fixture reuse, compensating write or auto-cleanup after STOP.

**MySQL limits:** An InnoDB `AUTO_INCREMENT` allocation may leave a gap after rollback; sequence gaps are **not** themselves rollback failures. No DDL/administrative statement may execute inside the business transaction because some implicitly commit and cannot be undone. See [MySQL 8.4 AUTO_INCREMENT](https://dev.mysql.com/doc/refman/8.4/en/innodb-auto-increment-handling.html), [statements causing implicit commit](https://dev.mysql.com/doc/refman/8.4/en/implicit-commit.html), and [InnoDB COMMIT/ROLLBACK](https://dev.mysql.com/doc/refman/8.4/en/innodb-autocommit-commit-rollback.html).

## 6. Work packages and gate state

| Gate | Required deliverable | Status |
| --- | --- | --- |
| RB01-DESIGN | This specification and operator instructions | DOCUMENTED |
| RB01-HARNESS | Isolated test-only subclass/controller integration, one-shot marker, synthetic negatives, exact-source diff | NOT IMPLEMENTED |
| RB01-ISOLATION | Owner-attested separate instance, restore rehearsal, disabled external effects | NOT VERIFIED |
| RB01-REVIEW | Independent review of test-only injection and safe recovery | OPEN |
| RB01-GO | Prior case-specific written Owner authorization | NOT GRANTED |
| RB01-RUN | One real post-Core-DML injected rollback, protected evidence and independent analysis | NOT EXECUTED |

The ongoing [CF03 qualification plan](cf03-qualification-plan.md) and [CF03 Stage-1 operator runbook](cf03-operator-runbook.md) remain valid. RB01-A does not close ERR01, BATCH01, CONC01 or any other Issue #22 acceptance gate.
