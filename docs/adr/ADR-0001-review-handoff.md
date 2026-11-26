# ADR-0001 — Independent architecture review handoff (CF-00)

> **Historical record — current navigation after PR #45 (2026-11-24):** the dated statements, earlier PR status, old A/B/C mappings and original test identities below are retained as historical evidence, not present authorization or roadmap. PR #38 full finance implementation is merged; Owner Variant B supersedes former source/trigger allowlists, blanket PROV veto and default-deny. PR #44 consolidation and PR #45 session compatibility fix are merged. Local A/B/C bounded results, B mail and focused D contribution/session/replay are accepted within their recorded scope. Full A–D integration and release remain open / NO-GO. F36-02 remains Owner-accepted Core residual risk. See [current baseline](../current-development-baseline.md), [binding Owner decision](../owner-core-parity-2026-11-22.md) and [current environments](../dolienv-environments.md). No old command or fixture is authorized by this record.


> **Current CF05 navigation (2026-11-21):** This is an archived **CF-00** independent review handoff, not a current CF05 authorization. [PR #36](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/36) is now development-squash-merged as `d0b15785c60b74aa7ad59a1c687db94193d159c6` (reviewed `33cb3da3b389f9df2870bc8c6223c86a959cb467`, identical tree), **F36-01/P1 CLOSED**, **F36-02/P2 OPEN / CF05-06 REQUIRED**. Read the [binding ADR](ADR-0001-core-first-transaction-profiles.md), [risk register](ADR-0001-risk-register.md), [Phase-3 record](../cf05-phase3-finance-admission.md) and [CF05-06 qualification plan](../cf05-06-standard-core-qualification-plan.md). No new operational finance/release GO was issued.

> **Historical CF-00 reviewer handoff (2026-11-18), not the current roadmap.** The independent CF-00 findings were later resolved/accepted within their original source scope. Issue #22's **2026-11-21 Owner amendments to ADR-06 and ADR-11** supersede the original contribution-only permanent scope and fault-test timing; see [current baseline](../current-development-baseline.md) and [ADR-0001 current amendment](ADR-0001-core-first-transaction-profiles.md#binding-2026-11-21-owner-amendment--adr-06-and-adr-11). PR #32 is squash-merged as `425980c43a51a9f27073133212f18825ac6da248`; CF05 is required before v0.1.0. The historical F23 review instructions below are preserved, not restarted.

**CF-00 OWNER-ACCEPTED – APPROVED FOR DOCUMENTATION MERGE (2026-11-18)**

CF-00 closure: [PR #23](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/23),
normal merge `6247704815cf532ec632616011133c646aa00005`, final reviewed HEAD
`d867c19c57b40e286ec08a9a935c4be138601a8a`;
[external verdict and Owner acceptance](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/23#issuecomment-6985938043):
**APPROVED FOR CF-00 WITH DOCUMENTED SCOPE**. F23-01–07 and K23-01 CLOSED.
The original drafting/review-request status below is historical and superseded for
CF-00; decisions, risk boundaries and unexecuted technical gates remain preserved.
CF-01 implementation status is recorded in the central validation; no Core-Guarded
booking, real D-01 integration or release approval follows from this closure.

**Historical drafting/review context:**

Initial review at `8fd9ca321a8090ec8c91a084efffc06d943bf9e3`: **CHANGES REQUIRED
BEFORE CF-00 APPROVAL**, 2026-11-18. The Owner approved the F23 disposition approach
and D-01/D-02 that day in the supplied follow-up instruction. **Focused independent
F23 follow-up PENDING**; no acceptance or finding closure by Codex. This is a documentation-only
handoff, not an implementation PR, product test report or release approval.

## Start here and source identity

Read the [ADR](ADR-0001-core-first-transaction-profiles.md) and
[risk register](ADR-0001-risk-register.md). The ADR contains all twelve binding
Owner decisions from [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22),
approved 2026-11-18. The issue was read completely, including its empty comment
list; body update 2026-11-18T14:21:48Z. Code baseline, fetched from origin and
confirmed via GitHub: **`48d196cfb04421d775ab4e6757083f6a755121df`**.
The CF-00 branch starts from that exact main commit. The original documentation
commit `8fd9ca321a8090ec8c91a084efffc06d943bf9e3` and its original verdict are
preserved in history. Review the corrective delta separately from that commit and
the unchanged implementation baseline.

| Source to inspect | Why |
| --- | --- |
| [Baseline source](https://github.com/iLLixM/dolibarr-member-subscription-massaction/tree/48d196cfb04421d775ab4e6757083f6a755121df) | `class/databasestate.class.php` (beforeBegin/afterBegin/active/completed/rollbackContext/isolation/engines); `class/booking.class.php` (locks/rollback/commit); `class/invoice.class.php` (shared context and nested APIs); `lib/booking.lib.php` and `subscription.php` (readiness, consume/session/post-commit boundaries). |
| [Core DoliDB methods](https://github.com/Dolibarr/dolibarr/blob/5c6c7ff6a5112413b1c27221c8d11750afad56f8/htdocs/core/db/DoliDB.class.php#L216) | Logical counter nesting; inner rollback is not SQL rollback; outer rollback resets counter even on SQL failure. |
| [SH-01 pinned report](https://github.com/iLLixM/dolibarr-member-subscription-massaction/blob/7df49867e001f2f56ce95d04c8ce5c4bfbd582c3/docs/sh01-mysql-restricted-feasibility.md), [laboratory evidence](https://github.com/iLLixM/dolibarr-member-subscription-massaction/tree/7df49867e001f2f56ce95d04c8ce5c4bfbd582c3/experiments/sh01) | Preserve NO-GO, traces, reproduction and manifest unchanged; original spike `409a0f51a99c92155a159b0e12301ee09612230f`. Counterexamples, not Core-Guarded qualification. |
| [PR #13](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/13), [validation](../phase4-implementation-validation.md#pr-13-post-merge-closure-and-mdb-test-01--2026-11-17), [coverage](../test-coverage.md#i107-hardening-evidence) | Bounded tested source `0999efb4aafca43d90ce12e38d370662ad0c63a1`, merged same tested tree; I107 and MDB closure, retained faults and non-blocking I107-FU-01 diagnostic limit. |
| [#20](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/20), [#21](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/21), [#19](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/19) | Current release governance; historical SH plan; release preparation CLOSED WITHOUT MERGE at `a74edb29ba6ca49af46c389f98c4bfd86cf161b6`. None authorizes implementation/release or supplies a Core-first ZIP. |

## Finding disposition F23-01 through F23-07

Source: supplied **PR #23 – Independent CF-00 Architecture & Transaction Security
Review**, 18 November 2026, examined HEAD `8fd9ca321a8090ec8c91a084efffc06d943bf9e3`.
No new DB/HTTP/SMTP tests were executed by that review. The Owner's supplied
**CF-00 Follow-up – PR #23: Architecture Review Findings F23-01 bis F23-07** approves
the handling below and D-01/D-02 on **2026-11-18**. See the
[decision record](ADR-0001-core-first-transaction-profiles.md#follow-up-owner-decisions-d-01-and-d-02--2026-11-18)
for rationale, boundaries and future evidence, durably referenced in Issue #22.
The attachments are source inputs; no private paths, raw traces or report copies
are published. These dispositions mean adopted actions, **not independent closure**.

| Finding | Original severity | Disposition | Corrective contract / next gate |
| --- | --- | --- | --- |
| F23-01 | HIGH | **ACCEPTED** | D-01 successful same-connection SET immediately before Core BEGIN; no intervening SQL, failed entry/1568/known foreign context rejects without foreign rollback. Later limited loaded-Core entry proof, not event-equivalence. |
| F23-02 | HIGH | **ACCEPTED** | D-01 normative preferred Preflight → SET RR → Core-BEGIN → Locks → business reads; real restricted loaded-Core qualification before production contribution DML. No event-isolation or session-default substitute; incompatible recipe returns to Owner. |
| F23-03 | HIGH | **ACCEPTED WITH LIMITS** | E01–E15 deterministic phase/error matrix, RB single Core rollback and qualified S statement-error branch; 1213 and unqualified/unknown 1205 stop. No physical proof prerequisite; ordinary acknowledged commit is application success. Numeric code/depth alone insufficient; mappings/evidence remain future. |
| F23-04 | HIGH | **ACCEPTED WITH LIMITS** | D-02 known Core path + conditional standard Agenda including ACTION_CREATE; known transaction-endangering combinations incompatible; unexamined third-party hooks compatibility reservation, no universal inventory/test/block duty. Optional mail separately qualified. |
| F23-05 | MEDIUM | **DEFERRED TO CF-01** | Authoritative revision/per-member snapshot or atomic admission comparison, concurrent requests/admin change, no cached-constant freshness assumption and no global serialization. Binding implementation requirement, not additional CF-00 blocker. |
| F23-06 | MEDIUM | **DEFERRED TO CF-02** | Server-owned profile/feature guard at all relevant module controller/Booking/direct Invoice/customer entry points; no changes to general Core APIs. Not additional CF-00 blocker; server-side enforcement must later be proved. |
| F23-07 | LOW | **ACCEPTED** | Minimal AGENTS clarifications explicitly scope event/isolation/completion evidence to Enhanced, preserve MariaDB snapshots and allow only the profile's admitted rollback/accepted post-commit outcome. |

Historical Q verdicts at the original HEAD: **Q01/Q02/Q05 CHANGES REQUIRED;
Q03 INCONCLUSIVE; Q04/Q06/Q07/Q08 APPROVED** (4 approved / 3 changes / 1 inconclusive).
These were architecture findings, not implemented product regressions or newly run
tests. The corrective text does not rewrite those original results. The new focused
verdict must identify the follow-up commit and assess F23-01–04/07 plus the explicit
CF-01/CF-02 deferrals. No completed independent acceptance checkbox is introduced.

## Strategy already approved; detailed contract to review

Do not reopen strategy selection merely because the new profile has weaker proof.
ADR-01/02/03 choose default new-install MySQL Core-Guarded and optional P_S Enhanced;
ADR-04/05 require explicit fixed selection/no fallback; ADR-06 restricts initial
Core-Guarded to contributions; ADR-07 preserves MariaDB; ADR-08 requires detected
uncertainty to stop; ADR-09 retains no journal/tables/migrations; ADR-10 separates
preview from physical readiness; ADR-11 separates implementation/tests/security/
release/Owner gates; ADR-12 expressly accepts bounded physical proof reduction
**only for Core-Guarded**. Exact wording and section traces are in the ADR.

The proposed small profile boundary changes evidence/admission, not Core business
writes or the single outer transaction owner. Locks, fresh eligibility/rights/entity,
fingerprint/overlap/R16, strict confirmation/CSRF/replay and side-effect checkpoints
remain shared. Read-only preview cannot authorize a booking. All new Core-Guarded
controls remain **unimplemented/unqualified** in this documentation branch.

| Review dimension | Core-Guarded | Enhanced MySQL | MariaDB Snapshot |
| --- | --- | --- | --- |
| Admission/evidence | D-01 SET→Core BEGIN admission, expected depth/identity; application ack, no P_S | Existing native/event/freshness/actual isolation/end evidence | Existing native/depth/active-idle snapshots |
| Replacement/end limits | Hidden same-connection replacement/original end may be unprovable | Original event checked at endpoints, not continuous tracking | Same-connection replacement can evade snapshot |
| Isolation | D-01 preferred ordered recipe approved by Owner, loaded-Core proof pending; no actual-event proof | Actual own ACTIVE event RR required | Existing policy; no new equivalent actual-event claim |
| Initial function | Contributions with qualified standard Agenda; **no invoice/customer/PDF** | Retain qualified contribution/customer/invoice scope | Retain qualified contribution/customer/invoice scope |
| Failure | Detected ambiguity UNKNOWN/STOP; no retry or replacement rollback in any profile | Existing contract retained | Existing contract retained |
| Current evidence | No product qualification | Bounded V24.0.3/PHP8.2.35/MySQL8.4.13 history | Bounded V24.0.3/PHP8.2.35/MariaDB10.11.19 history |

## Critical questions and design gates

| Gate | Review question / required disposition | Risks |
| --- | --- | --- |
| Q01 — Entry, ownership and Core nesting | Does the revised D-01/RB contract precisely require successful same-connection SET→Core BEGIN with zero intervening SQL, reject 1568/foreign entry without rollback, and handle nested Core errors without draining counters? The limited real proof is a later pre-DML gate, not a CF-00 test or universal event-proof requirement. | R01–R04/R06; F23-01/03 |
| Q02 — Completion and errors | Confirm E01–E15 priority and RB/S predicates: normal Core commit = application success; one permissible rollback on known business/bounded statement error; 1213 versus qualified/unknown 1205; lost ACK/depth/connection contradictions stop. Does E15 preserve DB success while stopping on uncertain session persistence, without readback/retry? | R02–R06/R08/R12; F23-03 |
| Q03 — Isolation and SQL order | D-01 now fixes the preferred sequence; confirm its bounded contract, no SQL between SET/BEGIN, no actual-event isolation requirement and return-to-Owner rule if not Core-compatible. Later loaded-Core restricted tests must cover four isolation levels, one-shot/intervening/late overrides and relevant completion variants (e.g. NO_CHAIN, CHAIN, RELEASE) under autocommit=1 before production contribution DML; NOT EXECUTED here. Completion values are not a general admission exclusion; no blanket variant qualification is claimed. | R07–R09; F23-02 |
| Q04 — Explicit profile and upgrade | Original architecture APPROVED. F23-05 is binding CF-01 work: authoritative revision, atomic admission comparison or precise per-member snapshot, no cached-constant assumption, safe migration and original-context handling of concurrent admin changes; no global serialization. Concrete mechanism/evidence deferred, not another CF-00 blocker. | R13; F23-05 |
| Q05 — Hook/engine boundary | Confirm D-02 known Core/Agenda/ACTION_CREATE path and conditional engine/rollback qualification; known transaction-endangering combinations incompatible, unexamined hooks compatibility reservation. No universal third-module inventory/test/block requirement or arbitrary external-effect guarantee. Optional mail needs separate qualification. | R01/R10/R11; F23-04 |
| Q06 — Preview/session binding | Original architecture APPROVED. Preserve no BEGIN/SET/locking/business writes in preview; server-held owner/entity/revision/profile/feature binding, fresh booking admission, stale rejection and stored-result/replay readability. Revision races are CF-01 implementation duties, not a demand for global locking or real tests in CF-00. | R09/R12/R13; F23-05 |
| Q07 — Functional boundary | Original architecture APPROVED. F23-06 binds CF-02 module controller, Booking and directly callable Invoice/customer entries to server-owned feature policy before DML; missing context denies unqualified CG financial actions. Preserve qualified other-profile paths and general Core APIs. Implementation proof deferred. | R14; F23-06 |
| Q08 — Claims and rollout | Original architecture APPROVED. Confirm unchanged ADR-01–12, exact historical versus future evidence, SH-01 NO-GO, closed #19, new release PR/R0 gates and no implementation/release approval from this documentary correction. | R15 |

A precise documentary contract can be accepted with binding future test gates.
Missing implementation execution alone is **not** an additional CF-00 blocker.
D-01/D-02 are approved design choices, not open strategy selections or physical
proof-equivalence requirements. The focused reviewer should identify a concrete
remaining ambiguity/contradiction if any; do not turn the accepted weaker guarantees
or deferred F23-05/06 mechanism details back into demands for Enhanced monitoring,
global/session rewriting or universal extension screening.

## Available evidence versus missing evidence

| Kind | Available now | Boundary / missing |
| --- | --- | --- |
| Current implementation/source | Pinned main, observer/booking/invoice/controller and Core methods inspected | No CG adapter or migration implemented; inspection is not execution. |
| Existing synthetic CI | Historical 21 PHP/Core pairs; observer/booking/invoice/request/preview/session regressions indexed in existing coverage | No CG-specific tests; no CI rerun result claimed by writing this design. V23 synthetic/source coverage is not real integration. |
| Real Enhanced MySQL | I1-05 failures preserved → FIX01/I107 qualification at MySQL8.4.13/V24.0.3/PHP8.2.35; active-isolation, Agenda/rollback/concurrency/R16 evidence | Exact instrumented profile only; I107 invoice CLI seller warning retained; arbitrary hooks and general versions unqualified. |
| Real MariaDB | MDB-01–09 9/9, independent confirmation Owner-reported; real Core persistence/invoice/Agenda/rollback/concurrency/R16/HTTP-replay, exact MariaDB10.11.19 profile | Snapshot limits retained; MDB-09 Chromium positive/replay, Firefox result read only; no new PDF/SMTP or physical-crash qualification. |
| SH-01 native restricted MySQL | DML-only MySQL8.4.13 lab, 28 native subcases/11 groups; real dropped replies and marker/isolation/completion counterexamples | Not 28 safety PASS; nesting is a counter model; actual Dolibarr Core integration NOT EXECUTED. Strict prototype refuses normal completion too. NO-GO stands. |
| Core-Guarded future qualification | Required scenarios in risk register and #22 CF-03 | **NOT EXECUTED:** D-01 limited loaded-Core entry/order/isolation proof before production contribution DML, then product synthetic cases and real restricted DB contribution/rollback/concurrency/HTTP-replay, profile migration, preview separation and permitted post-commit tests. Separate authorized stages/environments; none run by CF-00. |
| Release artifact | Historical #19 source/ZIP/hash and evidence preserved | No qualified Core-first ZIP, R0-04/R0-07 or separate Owner release GO. |

CF-00 runs no PHP/DB/HTTP/SMTP/fault campaign. It changes no historical PASS/FAIL or
71-ID requirement mapping. An automatic CI run on a documentation PR, if present,
is only unchanged-code synthetic CI; it cannot close any missing Core-first gate.

## Review decision criteria and next action

| Permitted detailed-design verdict | Criteria |
| --- | --- |
| **APPROVED** | All twelve strategic decisions accurately represented; explicit evidence distinctions and accepted limits; F23-01–04/07 have precise corrective contracts without unresolved safety ambiguity; D-01/D-02 and accepted blind spots are respected; CF-01/02 deferrals and future technical gates are explicit and testable. Unexecuted implementation tests alone do not block this documentary verdict. This approves detailed architecture only. |
| **CHANGES REQUIRED** | A concrete contradiction/unsafe rule exists: silent downgrade, ignored detected uncertainty, physical proof mislabeling, foreign rollback/counter draining, premature isolation/read-view assumptions, invoice bypass, broadened history, or missing enforceable contract. Identify exact clause/risk and required correction. |
| **INCONCLUSIVE** | A material unresolved documentary assumption/contract prevents an honest architecture verdict. Name the precise question and bounded follow-up; do not require real CF-01/02 tests inside CF-00 or treat accepted physical-proof limits/deferred mechanisms as missing equivalence. Implementation stays unapproved. |

- [ ] Independent focused reviewer records exact follow-up documentation commit,
  verdict and F23-01–F23-07 disposition, preserving the initial review history.
- [ ] Owner evaluates the detailed-design verdict and separately commissions CF-01/CF-02.
- [ ] Later implementation passes CF-03 synthetic/real non-regression and independent
  security review, followed by a new release PR, R0-04/R0-07 and separate Owner GO.

The minimal [AGENTS pointer](../../AGENTS.md#architecture-and-compatibility) explains
that old universal MySQL event/isolation/provable-rollback wording is scoped to the
current implementation/Enhanced, while MariaDB retains its own contract and the new
CG detailed design is pending. Full operational/user-facing cleanup belongs to the
accepted implementation/release changes, not retroactive historical rewrites.
