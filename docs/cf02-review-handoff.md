# CF-02 — Independent external review handoff (2026-11-20)

**Decision:** **READY FOR INDEPENDENT REVIEW — OWNER TEST EVIDENCE ACCEPTED WITH DOCUMENTED LIMITS**. This is **not** an independent review verdict, development-merge authorization, production compatibility certification or v0.1.0 release approval.

- PR: [#29](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/29)
- Issue / binding architecture: [#22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22), [ADR-0001](adr/ADR-0001-core-first-transaction-profiles.md) and pre-CF-02 D-01/AP3 Completion Gate.
- Source implementation tested: `c2347f610efc1c4c160dd0b21ffaf095753e7e40`; baseline `main`: `4698ec1540110a6250ba95b8bf0e57689a5e9f8b`.
- **Reviewer must freeze the current PR HEAD as an exact 40-character SHA when starting the independent review.** The review-readiness follow-up adds documentation only; it does not change the implementation/witness/test PHP or CI files. Do not infer that a later amendment has the same reviewed HEAD.
- Initial code-HEAD PR-CI: [run #44723124036](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/44723124036), **9/9 PASS** (standard nine PHP/Core profiles). New review-readiness documentation-HEAD CI must be evaluated separately.
- Positive operator evidence: [Owner acceptance, POS01-A/B exact statuses and independently matched baseline SHA-256](evidence/cf02/pos01-owner-test-acceptance-2026-11-20.md). Earlier [Stage-0](evidence/cf02/stage0-owner-reported-2026-11-20.md) and [real empty Core transaction](evidence/cf02/empty-core-transaction-2026-11-20.md) remain separate.

## Review scope and essential invariants

Review as an **independent Senior PHP/Dolibarr Developer, Software Architect and Security/Transaction Integration Reviewer**. Do not rubber-stamp the Owner-accepted test exceptions. Examine the actual PR code/diff and relevant pinned V24.0.3 Core behavior.

1. **D-01 admission / bounded Core transaction:** final preflight; one-shot `SET TRANSACTION ISOLATION LEVEL REPEATABLE READ` **immediately** before Core BEGIN on same native connection; no SQL between, one outer per member, proper depth; correct interpretation of 1568, autocommit, known foreign transaction and native reconnection.
2. **Completion and errors E01–E15:** separate acknowledged current-member outcome from permission to continue batch; `NO_CHAIN` and `CHAIN/RELEASE` stop behavior, second pre-end completion check; UNKNOWN/STOP after lost/unacknowledged BEGIN/COMMIT/ROLLBACK and SQL 1213/1205; no rollback of unknown foreign/replaced context, retry/compensation, observer fallback, extra journal/table or fabricated physical event identity.
3. **Service-level admission / HTTP:** original confirmation source, CSRF, bound owner/entity/profile/revision and one-time local file-session checkpoint, replay/consumed status, direct `MsmBookingService::book` denial and fail-closed supported logger/runtime constraints; under-lock revalidation/rights, fingerprint/overlap and persisted Core R16 outcome.
4. **Functional boundaries:** CoreGuarded **contribution-only**; server-side direct invoice/customer/PDF/mail refusal. Verify no effective broadened feature admission, no hidden P_S permission dependency. Existing EnhancedMySQL and MariaDBSnapshot observer/qualified invoice paths must not be silently changed or downgraded; consider selected Core/Agenda transactional tables and unknown third-party hooks.
5. **Evidence integrity and compatibility:** distinguish synthetic adapter/session/unit tests and nine-profile CI from actual restricted MySQL8.4.13/Dolibarr24.0.3/PHP8.2 authenticated booking. Do not transform the Owner-reported/observed UI acknowledgement into independent physical COMMIT evidence. Verify the intentional POS01-B marker exception and preserved original STOP.

## Executed evidence status

| Evidence | Outcome and limit |
| --- | --- |
| Synthetic bounded adapter / local session | 88 / 19 assertions PASS; no full live DB/HTTP inference |
| Existing pinned-Core/profile/booking/invoice/controller regression | Standard 9/9 PR CI PASS on code SHA; synthetic Core/fixture exercises, not real Enhanced/MariaDB invoice |
| Restricted MySQL / P_S / table engines / loaded Core admission | Owner-run PASS (MySQL 8.4.13, reduced rights, P_S 1142, InnoDB), bounded lab |
| Empty actual Core transaction | Owner-run BEGIN+ROLLBACK PASS, NO_CHAIN, no business DML |
| Real authenticated MySQL POS01-A | Subscription #65 and Agenda persisted, but input start was incorrect and marker absent: original witness STOP, test procedure deviation |
| Real authenticated MySQL POS01-B | Subscription #66 correct member/type/120 EUR/2080 dates/datefin, Agenda event/resource, stable guarded table counts, browser "commit confirmed"; original witness STOP only for absent note marker; **Owner formally accepts this exception for review entry** |

## Explicitly open / not qualified by this handoff

- **Real negative rollback/error-injection** and actual ambiguous lost-ACK/physical connection faults for CF-02; synthetic error mappings do not replace real injections.
- **Multi-member NO_CHAIN**, CHAIN/RELEASE under real HTTP, repeated/parallel request, true crash/replay/concurrency and optional Agenda OFF.
- **Real EnhancedMySQL and MariaDBSnapshot contribution/invoice non-regression on this CF-02 source** (historical independent real tests cover older branches/profiles only).
- **Full 21-profile optional CI** on CF-02 current source; the nine-profile reduced PR CI is the agreed development standard, not a release gate substitute.
- Wider V22/V23 live compatibility, arbitrary third-party hooks, alternate PHP session/log handlers, other DB configurations, complete post-commit mail/PDF tests, v0.1.0 ZIP and release gates.
- **External review findings, independent review verdict, separate Owner merge decision, CF-03 through CF-05 and release approval remain OPEN.** Issue #22 and its CF-02/CF-03 checkboxes remain open until the appropriate separate gate decisions.

## Requested reviewer deliverable

Provide a precise **reviewed HEAD**, base, files/commit scope, evidence examined, 9-job CI result and any exceptions; identify findings with severity, source path/line and reproduction or proof; classify CF-02 implementation correctness separately from CF-03/release qualification; state **APPROVED FOR DEVELOPMENT MERGE WITH DOCUMENTED LIMITS** or **CHANGES REQUIRED** (or a narrower scoped judgment). Independently evaluate the marker waiver without silently converting the original witness STOP to PASS.

**No merge, tag, ZIP, release or Issue #22 closure as part of this review handoff.**
