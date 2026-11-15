# Codex repository guidance

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

## Architecture and compatibility

- Dolibarr V22.0.5 is the primary implementation reference with historical bounded acceptance.
- V24.0.3 completed the real C3 campaign in the exact profile documented in
  `docs/phase4-implementation-validation.md`; external conditional review is complete,
  with OD-PR6-01/02 accepted for a bounded development merge only. Actual merge
  remains a separate owner decision.
  Other versions/profiles and production/public release are not generally qualified.
- Preserve PHP 7.4 compatibility and established Dolibarr V22 coding conventions.
- Reuse Dolibarr object APIs, hooks, translations, authenticated bootstrap, CSRF checks, output escaping, and object/entity access checks.
- Preserve Phase 3B subscription booking, R16 postconditions, replay protection, transaction ownership, and permission checks.
- Owner decisions O1–O7 are binding for invoice work; do not change them silently.
- Keep one controlled database transaction per member for the contribution and optional customer/invoice operations.
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
