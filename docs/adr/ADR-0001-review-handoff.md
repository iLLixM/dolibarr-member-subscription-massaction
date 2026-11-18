# ADR-0001 — Independent architecture review handoff (CF-00)

**OWNER-APPROVED ARCHITECTURE STRATEGY – DETAILED DESIGN PENDING INDEPENDENT REVIEW**

Review requested; no independent acceptance is claimed. This is a documentation-only
handoff, not an implementation PR, product test report or release approval.

## Start here and source identity

Read the [ADR](ADR-0001-core-first-transaction-profiles.md) and
[risk register](ADR-0001-risk-register.md). The ADR contains all twelve binding
Owner decisions from [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22),
approved 2026-11-18. The issue was read completely, including its empty comment
list; body update 2026-11-18T14:21:48Z. Code baseline, fetched from origin and
confirmed via GitHub: **`48d196cfb04421d775ab4e6757083f6a755121df`**.
The CF-00 branch starts from that exact main commit. Review the documentation delta
separately from this unchanged implementation baseline.

| Source to inspect | Why |
| --- | --- |
| [Baseline source](https://github.com/iLLixM/dolibarr-member-subscription-massaction/tree/48d196cfb04421d775ab4e6757083f6a755121df) | `class/databasestate.class.php` (beforeBegin/afterBegin/active/completed/rollbackContext/isolation/engines); `class/booking.class.php` (locks/rollback/commit); `class/invoice.class.php` (shared context and nested APIs); `lib/booking.lib.php` and `subscription.php` (readiness, consume/session/post-commit boundaries). |
| [Core DoliDB methods](https://github.com/Dolibarr/dolibarr/blob/5c6c7ff6a5112413b1c27221c8d11750afad56f8/htdocs/core/db/DoliDB.class.php#L216) | Logical counter nesting; inner rollback is not SQL rollback; outer rollback resets counter even on SQL failure. |
| [SH-01 pinned report](https://github.com/iLLixM/dolibarr-member-subscription-massaction/blob/7df49867e001f2f56ce95d04c8ce5c4bfbd582c3/docs/sh01-mysql-restricted-feasibility.md), [laboratory evidence](https://github.com/iLLixM/dolibarr-member-subscription-massaction/tree/7df49867e001f2f56ce95d04c8ce5c4bfbd582c3/experiments/sh01) | Preserve NO-GO, traces, reproduction and manifest unchanged; original spike `409a0f51a99c92155a159b0e12301ee09612230f`. Counterexamples, not Core-Guarded qualification. |
| [PR #13](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/13), [validation](../phase4-implementation-validation.md#pr-13-post-merge-closure-and-mdb-test-01--2026-11-17), [coverage](../test-coverage.md#i107-hardening-evidence) | Bounded tested source `0999efb4aafca43d90ce12e38d370662ad0c63a1`, merged same tested tree; I107 and MDB closure, retained faults and non-blocking I107-FU-01 diagnostic limit. |
| [#20](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/20), [#21](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/21), [#19](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/19) | Current release governance; historical SH plan; release preparation CLOSED WITHOUT MERGE at `a74edb29ba6ca49af46c389f98c4bfd86cf161b6`. None authorizes implementation/release or supplies a Core-first ZIP. |

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
| Admission/evidence | Core ack + bounded application assumptions; no P_S | Existing native/event/freshness/actual isolation/end evidence | Existing native/depth/active-idle snapshots |
| Replacement/end limits | Hidden same-connection replacement/original end may be unprovable | Original event checked at endpoints, not continuous tracking | Same-connection replacement can evade snapshot |
| Isolation | Ordered recipe must be accepted and tested; no actual-event proof | Actual own ACTIVE event RR required | Existing policy; no new equivalent actual-event claim |
| Initial function | Contributions with qualified standard Agenda; **no invoice/customer/PDF** | Retain qualified contribution/customer/invoice scope | Retain qualified contribution/customer/invoice scope |
| Failure | Detected ambiguity UNKNOWN/STOP; no retry or replacement rollback in any profile | Existing contract retained | Existing contract retained |
| Current evidence | No product qualification | Bounded V24.0.3/PHP8.2.35/MySQL8.4.13 history | Bounded V24.0.3/PHP8.2.35/MariaDB10.11.19 history |

## Critical questions and design gates

| Gate | Review question / required disposition | Risks |
| --- | --- | --- |
| Q01 — Entry, ownership and Core nesting | Are acknowledged begin + stable native identity + depth assumptions sufficiently precise for the bounded caller/hook set? Handle hidden foreign physical transactions honestly. Verify every nested Core API error/depth return, inner rollback semantics, one admitted outer rollback, no counter draining. Identify unsupported callers. | R01–R04/R06 |
| Q02 — Completion and errors | Can commit/rollback admission and outcome vocabulary distinguish CG application ack, Enhanced event completion and MariaDB snapshot? Which errors invalidate context (including begin failure/deadlock/end reply loss)? No automatic repair/readback redemption; preserve uncertainty after session-save failures. Is the rollback predicate conservative without accidentally requiring impossible event proof for CG? | R02–R06/R08/R12 |
| Q03 — Actual isolation and SQL order | Is the candidate preflight → next-transaction RR → immediate Core BEGIN → locks → metadata sequence viable on real loaded Core, with no intervening SQL or one-shot consumption? Address late overrides, all session defaults, error handling and concurrency/R16. Source/native primitive evidence is insufficient for acceptance of implementation. State the supported operating assumptions and what must refuse. | R07–R09 |
| Q04 — Explicit profile and upgrade | Determine exact config/revision and new-vs-existing install detection without new tables/schema migration. Missing/invalid metadata cannot silently downgrade Enhanced. How are admin changes serialized/detected between members and during an active member without switching its context? | R13 |
| Q05 — Hook/engine boundary | Is the standard contribution Agenda scope complete and conditional? How are unsupported extra tables, implicit commits and external effects communicated/admitted? No arbitrary-extension atomicity claim. Contribution mail needs its own permitted post-commit qualification, not implied DB approval. | R01/R10/R11 |
| Q06 — Preview/session binding | Verify no BEGIN/SET/locking/business side effects in preview, fresh transaction admission at book, server-held family/profile/revision/feature binding, stale rejection on profile change and immutable historical result evidence. Does replay/readability survive unavailable booking prerequisites? | R09/R12/R13 |
| Q07 — Functional boundary | Do all service/HTTP entry points reject CG customer/invoice/autocreate/PDF before DML, including old sessions and direct calls, while retaining qualified Enhanced/MariaDB functions? | R14 |
| Q08 — Claims and rollout | Are policy (V22–V24, DB minimums, PHP/Core compatibility), synthetic coverage, exact historical real profiles and future CG evidence clearly separate? Is closed #19 preserved and a new exact-artifact release gate required? | R15 |

A design review can approve a precise contract with specified future test obligations;
it cannot mark those unexecuted tests PASS. Any Q left materially ambiguous must be
resolved or recorded as an explicit review blocker, not hidden under Owner strategic
risk acceptance. Concrete setting names, interfaces/result schemas and migration
mechanism are deliberately proposals for review/CF-01, not invented Owner decisions.

## Available evidence versus missing evidence

| Kind | Available now | Boundary / missing |
| --- | --- | --- |
| Current implementation/source | Pinned main, observer/booking/invoice/controller and Core methods inspected | No CG adapter or migration implemented; inspection is not execution. |
| Existing synthetic CI | Historical 21 PHP/Core pairs; observer/booking/invoice/request/preview/session regressions indexed in existing coverage | No CG-specific tests; no CI rerun result claimed by writing this design. V23 synthetic/source coverage is not real integration. |
| Real Enhanced MySQL | I1-05 failures preserved → FIX01/I107 qualification at MySQL8.4.13/V24.0.3/PHP8.2.35; active-isolation, Agenda/rollback/concurrency/R16 evidence | Exact instrumented profile only; I107 invoice CLI seller warning retained; arbitrary hooks and general versions unqualified. |
| Real MariaDB | MDB-01–09 9/9, independent confirmation Owner-reported; real Core persistence/invoice/Agenda/rollback/concurrency/R16/HTTP-replay, exact MariaDB10.11.19 profile | Snapshot limits retained; MDB-09 Chromium positive/replay, Firefox result read only; no new PDF/SMTP or physical-crash qualification. |
| SH-01 native restricted MySQL | DML-only MySQL8.4.13 lab, 28 native subcases/11 groups; real dropped replies and marker/isolation/completion counterexamples | Not 28 safety PASS; nesting is a counter model; actual Dolibarr Core integration NOT EXECUTED. Strict prototype refuses normal completion too. NO-GO stands. |
| Core-Guarded future qualification | Required scenarios in risk register and #22 CF-03 | **NOT EXECUTED:** product synthetic cases, loaded-Core restricted DB contribution/rollback/concurrency/HTTP-replay, profile migration, preview separation and permitted post-commit tests. Need separately approved environments/operations. |
| Release artifact | Historical #19 source/ZIP/hash and evidence preserved | No qualified Core-first ZIP, R0-04/R0-07 or separate Owner release GO. |

CF-00 runs no PHP/DB/HTTP/SMTP/fault campaign. It changes no historical PASS/FAIL or
71-ID requirement mapping. An automatic CI run on a documentation PR, if present,
is only unchanged-code synthetic CI; it cannot close any missing Core-first gate.

## Review decision criteria and next action

| Permitted detailed-design verdict | Criteria |
| --- | --- |
| **APPROVED** | All twelve strategic decisions accurately represented; explicit evidence distinctions and accepted limits; Q01–Q08 have precise, implementable dispositions with no unresolved safety-contract ambiguity; controls/rollback/error/migration/feature boundaries are testable; future unexecuted obligations remain separate. This approves detailed architecture only. |
| **CHANGES REQUIRED** | A concrete contradiction/unsafe rule exists: silent downgrade, ignored detected uncertainty, physical proof mislabeling, foreign rollback/counter draining, premature isolation/read-view assumptions, invoice bypass, broadened history, or missing enforceable contract. Identify exact clause/risk and required correction. |
| **INCONCLUSIVE** | Necessary Core sequence, isolation, caller/hook or migration facts are missing so feasibility cannot be decided honestly. Name the missing evidence and bounded follow-up; do not invent approval or authorize implementation by default. |

- [ ] Independent reviewer records exact reviewed documentation commit, verdict,
  findings and Q01–Q08 disposition.
- [ ] Owner evaluates the detailed-design verdict and separately commissions CF-01/CF-02.
- [ ] Later implementation passes CF-03 synthetic/real non-regression and independent
  security review, followed by a new release PR, R0-04/R0-07 and separate Owner GO.

The minimal [AGENTS pointer](../../AGENTS.md#architecture-and-compatibility) explains
that old universal MySQL event/isolation/provable-rollback wording is scoped to the
current implementation/Enhanced, while MariaDB retains its own contract and the new
CG detailed design is pending. Full operational/user-facing cleanup belongs to the
accepted implementation/release changes, not retroactive historical rewrites.
