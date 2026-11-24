# Codex repository guidance

## Current source of truth after PR #38 – 2026-11-24

- **Phase 4 development is merged**: PR #38 squash commit `0998fbaf58155cca04d31ab99c6bb47420e53c72`, reviewed source `b15b93580f995fb05a696a125c2a5fd995dd4bb3`, identical tree. Green 9/21/Hosted=31/31 CI is **synthetic**, not real acceptance.
- Current authority: [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22), [Owner Core parity](docs/owner-core-parity-2026-11-22.md), [current baseline](docs/current-development-baseline.md), [dolienv](docs/dolienv-environments.md) and [release gates](docs/release-qualification-v0.1.0.md). This section supersedes older dated future-work, default-deny or shared A/C-site assertions below; preserve those solely as historical evidence.
- **Owner Variant B:** normal Dolibarr Core/numbering/PDF/trigger/Agenda/extensions at parity, plus original confirmed full-row/fingerprint, consumed file-session one-shot, user/entity/rights/profile revision, same active owned Guard, engine/readbacks, ACK, UNKNOWN/STOP and no retries/foreign rollback. Full CoreGuarded Finance is **implemented and enabled**. Do **not** reintroduce source PHP SHA allowlists, blanket `PROV` prohibition or default-deny. F36-02 filesystem exposure remains Owner-accepted residual Core risk.
- Findings F38-01–06 CLOSED, F38-07–09 SUPERSEDED, F38-10–14 INDEPENDENTLY CLOSED. `DOLIENV-ACC-01` old B finance attempt is `UNKNOWN/STOP`, rollback unconfirmed. Do not retry or compensate it; new B retest requires separate Owner GO, private target-user preflight, fresh fixtures and independent post-state.
- Current dolienv: **A=MariaDBSnapshot, B=CoreGuarded, C=EnhancedMySQL, D=Hosted CoreGuarded**. B/C have separate DB schemas/webroots, shared MySQL server/local module source. Old A/B reversal and A/C shared-schema scripts **must never be blindly relabeled**. Do not run uncoordinated B/C qualification.
- F32-MR-01 protected launcher, full real A–D CF03-NORMAL, real module SMTP/attachment, CF05-07, exact ZIP R0-03, Hosted D R0-04, independent R0-07 and separate Owner release GO are **OPEN**. CF03-FAULT destructive tests DEFERRED. No new real business/FS/mail writes without a separate case-specific approval.
- Publish no private hosts, users, schemas, DB accounts, passwords, session material, local paths, dumps or raw evidence. Preserve requirements.csv original 71 IDs and historical audit records.

---

> Historical record follows. Current status is defined above.

## Scope and sources of truth

- This repository is a standalone Dolibarr module. Do not modify Dolibarr Core.
- Follow `CONTRIBUTING.md` for development, Git, language, security, and licensing rules.
- `docs/requirements.csv` is the only authoritative requirement matrix; preserve all 71 IDs and historical fields.
- Use `docs/test-strategy.md` for T1–T4 validation and evidence requirements.
- Use `docs/test-coverage.md` only as the legend for existing test references and evidence limits.
- Treat `docs/phase3b-validation.md` as the validated Phase 3B baseline.
- For invoice work, use `docs/phase4-invoice-architecture.md` and
  `docs/phase4-invoice-implementation-plan.md` for binding decisions and gates.
  Establish current implementation and evidence status from the active branch and
  applicable validation documents.
- Do not create a parallel architecture, requirement matrix, test strategy, or evidence history.

## Current environment naming (Owner update 2026-11-24)

- Follow [dolienv environments](docs/dolienv-environments.md): A MariaDBSnapshot,
  B CoreGuarded, C EnhancedMySQL, D hosted CoreGuarded. Older CF03 A/B labels and
  A/C shared-schema instructions remain historical; never merely swap letters in
  legacy tools/manifests. New B/C schemas are separate, but their server and local
  module source are shared; no parallel qualification is implied.
- A/C bounded finance/PDF cases passed. B finance remains UNKNOWN/STOP at
  finance_precheck; D has read-only browser/hosting-CLI evidence only in the new
  setup campaign. Preserve no-retry and all existing profile safety contracts.
- Publish no real hosting origin, private route/path, DB/account/login identity,
  secret/session material, dumps, documents or private inventory. A/B/C local
  reader/backup-all does not automatically include D. No release approval follows.

## Architecture and compatibility

- Core-first strategy: Owner decisions ADR-01–ADR-12 in Issue #22 (2026-11-18)
  govern the new Core-Guarded design; see
  [ADR-0001](docs/adr/ADR-0001-core-first-transaction-profiles.md), its risk register
  and review handoff. CF-00 was independently and Owner-accepted on 2026-11-18,
  merged in PR #23 as `6247704815cf532ec632616011133c646aa00005`; this is no release
  approval. CF-01 implements profile/readiness boundaries only, with Core-Guarded
  booking still disabled pending separately authorized CF-02 and qualification. The existing G0/MySQL event,
  actual-event isolation and provable-completion rules below still constrain the
  current code and Enhanced MySQL; they are not universal requirements for the new,
  deliberately weaker Core-Guarded profile. MariaDB retains its existing snapshot
  contract. No silent Enhanced downgrade or historical evidence reinterpretation.
  Owner-approved D-01/D-02 (2026-11-18) fix the preferred Core-Guarded SET→Core-BEGIN
  entry sequence and bounded Core/standard-Agenda scope; F23-01–07/K23-01 are closed.
  For this design, the older event/isolation/physical ownership/completion wording
  below applies to Enhanced evidence, not Core-Guarded rollback admission or
  post-commit acceptance. Use ADR section 5: a permitted single Core rollback needs
  application-context checks; normal acknowledged Core commit is application success.
  Neither requires an Enhanced physical event proof. MariaDB remains snapshot-based.
  CF-00 alone did not authorize coding. The separate CF-01 task authorizes only
  profile configuration, revision binding and preview separation, not CF-02 booking,
  session/global rewrites or new mail tests.

- P2 admission is >=21.0.0 with no upper bound or exact patch/SHA allowlist.
  P1 source-analyzed 15 V22/V23/V24 tags; V21/future versions remain unqualified.
  Keep capability checks, actual Core R16 observation and independent known-contract
  checks (24.0.2 boundary). Qualification is evidence, not admission.
- D-P1-01 accepts only empty/unset, `0` and `defaultforfoundationcountry` VAT
  configuration on every Core; no V22 silent-zero fallback for other values.
  Preserve subscription-only behavior. P3 is Owner-accepted with documented
  limitations; independent overall review and merge decisions remain separate.

- Dolibarr V22.0.5 is the primary implementation reference with historical bounded acceptance.
- V24.0.3 completed the real C3 campaign in the exact profile documented in
  `docs/phase4-implementation-validation.md`; PR #6 merged on 2026-11-15 as
  `96f870e789eb26b6eb8a6b8eeb0c79812018b0ab`, the completed compatibility
  development baseline. Final external review: APPROVED FOR MERGE – ALL CONDITIONS
  FULFILLED; OD-PR6-01/02 fulfilled. All three post-merge PHP/Core CI jobs passed.
  Other versions/profiles and production/public release are not generally qualified.
- Preserve PHP 7.4 compatibility and established Dolibarr V22 coding conventions.
- Reuse Dolibarr object APIs, hooks, translations, authenticated bootstrap, CSRF checks, output escaping, and object/entity access checks.
- Preserve Phase 3B subscription booking, R16 postconditions, replay protection, transaction ownership, and permission checks.
- Owner decisions O1–O7 are binding for invoice work; do not change them silently.
- Keep one controlled database transaction per member for the contribution and optional customer/invoice operations.
- Booking and Invoice must share one `MsmDatabaseState` bound to the same DoliDB
  object. Preserve G0 connection/event ownership, freshness, depth and expected-own
  completion before fresh idle; the observer does not own outer transaction control.
- Check engines before BEGIN and again after member/type and optional customer/product
  locks, before ordinary business reads. Do not establish a pre-lock MySQL read view,
  change isolation as a workaround or replace Core R16 writes.
- Include standard Core Agenda tables in both engine gates only when Agenda and a
  relevant selected-path auto-event are enabled. Keep unknown third-party effects
  outside any general atomicity promise.
- Require REPEATABLE READ from the actual own MySQL ACTIVE event, not merely the
  session default. Keep business isolation policy separate from G0 ownership and
  completion: a known unsupported isolation may roll back its fresh owned context;
  malformed/uncertain observations never authorize rollback or retry.
- Roll back only a fresh, provably owned open context; lost ownership/completion stays
  `unknown/stop` without retry or rollback of a replacement transaction.
- MySQL real qualification is limited to the V24.0.3/PHP 8.2.35/MySQL Community
  8.4.13 profile in `docs/phase4-implementation-validation.md`. MariaDB keeps its own
  evidence/continuity limits; synthetic CI is not blanket DB qualification.
  MariaDB 10.11.19 / V24.0.3 / PHP 8.2.35 / mysqli / InnoDB completed real
  MDB-01–MDB-09 requalification (9/9 PASS, independently confirmed) at `0999efb`.
  Both I107 and MDB external follow-ups are complete; I107-F01/F02/F03 are closed.
  PR #13 merged on 2026-11-17 as `aaf1f3786a4c9ea7e1707ad41cb821b0bb271e3d`
  with the same tested tree: APPROVED FOR MERGE WITH DOCUMENTED SCOPE.
  I107-FU-01 remains open, non-blocking P3 Low; preserve the last-observed versus
  completed-transaction isolation diagnostic distinction. PostgreSQL remains
  iteration 2; other profiles and production/public releases remain unqualified.
- Generate PDFs and send mail only after a confirmed COMMIT.
- Never automatically retry or compensate an unknown booking, COMMIT, PDF, hook, or mail outcome.
- Reject unsupported invoice, tax, product, stock, entity, or document configurations safely and explicitly.

## Validation

- Preserve the existing PHP runners and CI matrix.
- For functional changes, run focused T1 baseline checks and T2 change-specific regressions.
- `test/unit/booking.php` already includes `test/unit/preview.php`; avoid redundant runs without a concrete reason.
- Reuse existing harnesses before adding a new test tool or fixture layer.
- Run risk- or milestone-required T3/T4 tests within the approved scope. Real-environment
  tests require an approved installation. Obtain separate authorization for business
  writes, environment changes, live fault injection and SMTP delivery; isolated
  synthetic fault tests remain allowed.
- Never present synthetic adapters, stubs, source inspection, or assertion counts as real Dolibarr integration evidence.
- Mark every required but unperformed check as `NOT EXECUTED` and state the missing authorization or environment.
- Keep chat output compact; retain sanitized, reproducible evidence for material tests,
  including successful integration cases and failures.
- For changed PHP, include appropriate lint, language parity, targeted runners, and `git diff --check`.

## Safety and Git boundaries

- Do not alter real business data, run migrations, send SMTP mail, or change a test installation without explicit authorization.
- Never publish credentials, cookies, sessions, tokens, private records, documents, database dumps, or local configuration.
- Do not modify `main`, force-push, merge, tag, release, or change remote resources unless the task explicitly authorizes it.
- Local commits are allowed only when the current task explicitly requests them.
- Use short-lived branches and pull requests against `main`; keep each change small and reviewable.

## Working method

- Read only task-relevant files and the applicable sections of the documents above.
- Inspect the affected component and established APIs before editing; prefer the smallest coherent change.
- Preserve unrelated user changes and do not rewrite working code or tests without a demonstrated need.
- Investigate failures narrowly instead of repeating broad repository, architecture, or requirement audits.
- Write code identifiers, comments, commit messages, and review text in English.
- Finish with a concise report covering changed files and behavior, executed tests, remaining risks, and open evidence.

## CF-02 branch implementation addendum (2026-11-20)

The Owner authorized development of CoreGuarded contribution-only booking in
feature/cf02-core-guarded-contribution. Issue #22 and ADR-0001, including D-01/D-02,
E01–E15, and the Owner-accepted bounded Completion Gate, are authoritative.
PR #29 is an Owner test-accepted **external review candidate**, but remains
independently unreviewed and does NOT qualify production CoreGuarded DML,
development merge or release. EnhancedMySQL retains its event-observer contract,
MariaDBSnapshot retains the historical snapshot contract. No invoice/customer
creation, PDF or mail under CoreGuarded until independently qualified and
separately Owner approved. Owner-run real restricted-MySQL 8.4.13/V24.0.3
contribution+Agenda and the actual empty Core transaction were positive; the
formal POS01-B witness still reports STOP for a missing description marker,
which the Owner explicitly waived **only for external review entry**. No code
or witness PASS may be rewritten to erase this limitation. Real negative
fault/concurrency/replay and current-source MariaDB/Enhanced invoice tests,
independent code review, and separate merge/release authorization remain open.
See docs/cf02-review-handoff.md and the preserved POS01 evidence.
