# Changelog

## Unreleased

### v0.1.0 release candidate preparation — 2026-11-18

- Prepare descriptor/Composer version 0.1.0 and current DE/EN display/status text;
  retain technical PHP7.4/Core>=21 admission without new runtime version guards.
- Adopt user-facing README, compatibility, installation, operation/reconciliation
  and release notes; preserve the former README and dated plans as history.
- Retain unreserved module ID584620 by explicit Owner exception; require local
  collision checking. Intended release scope: Core V22–V24, MariaDB>=10.11 or
  Oracle MySQL>=8.4; actual synthetic/real profiles remain explicitly separate.
- Record PR #16 merge `48d196c`, Issue #8 closure and post-merge 21/21 CI PASS;
  keep F16-01 accepted, F16-02 scoped Owner HTTP100 and F16-03/04/05 P3 limits.
  Update 53 obsolete requirement implementation-review statuses without changing
  historic requirement/evidence fields or promoting unexecuted scenarios to PASS.
- Add deterministic allowlisted Dolibarr candidate ZIP build, manifest and SHA-256.
  No business logic, test, CI, runtime configuration or database changes.
- R0-05 accepts the existing session/checkpoint risk within the limited intended
  scope; no journal or automatic retry/resume/redelivery is introduced. R0-04
  final ZIP integration and R0-07 independent release review remain pending;
  no final tag, release, production or general load approval.

### Historical development changes and review checkpoints

Entries below retain their original pre-merge/0.1.0-dev status wording where
applicable; current PR #16 and release-preparation status is recorded above.

- Issue #8: replace the fixed 50-member booking cap with configurable
  `MEMBERSUBSCRIPTIONMASSACTION_MAX_MEMBERS_PER_BATCH`, default 100 even for
  existing activations, capped by `MAIN_LIMIT_FOR_MASS_ACTIONS`. Reject oversized
  selections early with DE/EN selected-count/maximum feedback and a return link;
  recheck current limits for pending sessions, without truncation or splitting.
- PR #16 independent review of `5f71102` completed: APPROVED FOR MERGE WITH
  DOCUMENTED SCOPE. F16-01 CLOSED – OWNER ACCEPTED (I105 URL assignment error;
  actual MariaDB V24 target accepted). F16-02 RESOLVED WITH DOCUMENTED SCOPE –
  OWNER-CONFIRMED HTTP TEST: later manual authenticated 100/100 contribution-only
  browser booking, about 6 seconds confirmation-to-results, no invoices/email.
  Keep original automated CLI evidence separate; no independent HTTP reproduction
  or new traces claimed. F16-03/04/05 remain P3, non-merge-blocking. No general
  HTTP-load/replay/parallel/PDF/SMTP, production or release approval; PR stays Draft.

- Complete Oracle-MySQL iteration 1 with PR #13 merged on 2026-11-17 as
  `aaf1f3786a4c9ea7e1707ad41cb821b0bb271e3d` (same tree as tested `0999efb`).
  Both independent I107/MDB follow-ups completed positively: APPROVED FOR MERGE
  WITH DOCUMENTED SCOPE; I107-F01/F02/F03 closed. Retain open non-blocking
  P3 Low diagnostic observation I107-FU-01.
- Complete real MariaDB 10.11.19 / Dolibarr V24.0.3 / PHP 8.2.35 regression
  qualification: MDB-01–MDB-09 9/9 PASS, independently confirmed. Actual Core
  transactions, Agenda, invoices/customer creation, atomic rollback, competition,
  R16 and authenticated HTTP replay passed within their documented limits.
  Agenda OFF was process-local; no new PDF/SMTP qualification or release approval.

- I107-F01: conditionally include standard Core Agenda `actioncomm` and
  `actioncomm_resources` in both engine gates, respecting enabled events and
  invoice/customer/contact options without pre-lock metadata reads.
- I107-F02: observe actual own event `ISOLATION_LEVEL` in the existing MySQL query;
  admit only REPEATABLE READ. Preserve known-own rollback for other valid levels
  and fail closed on malformed observations, without changing G0 ownership states.
- Requalify targeted real Agenda/invoice, post-trigger rollback, concurrency and
  R16 paths plus seven read-only actual-isolation cases. Negative Engine cases
  remain synthetic; no Core table conversion, new PDF generation or SMTP delivery.
  Historical I107 checkpoint corrected I107-F03 to Ready for review, with targeted
  follow-up and Owner merge then outstanding; their completion is recorded above.

- Add shared DoliDB-bound `MsmDatabaseState` observation for MariaDB and Oracle
  MySQL; Booking/Invoice keep one outer Core transaction and shared ownership.
- Add MySQL fresh-idle, connection/thread/event and SAVEPOINT/RELEASE freshness
  checks; verify expected own COMMIT/ROLLBACK before the next idle proof. Retain
  MariaDB snapshot semantics, conditional engine preflight and fail-closed admission.
- Resolve F01: authenticated stored results remain readable without new-operation
  readiness; preserve CSRF, single-use confirmation and replay protection.
- Resolve I105-FIND-01 through FIX01: defer in-transaction engine metadata reads
  until member/type/customer/product locks are held, keeping preview and unchanged
  Core R16 reads current without isolation or transaction-control changes.
- Retain historical I1-05 FAILED (19/20), then record FIX01 PASSED (20/20) and
  21/21 synthetic PHP/Core CI on the correction. Together these requalify only
  V24.0.3/PHP 8.2.35/MySQL Community 8.4.13 in the documented instrumented
  mysqli/InnoDB/REPEATABLE READ profile. I1-06 documents the review handoff;
  I1-07 and merge were separate subsequent gates, now completed as recorded above.
  Production/public-release approval remains outside that development merge.

- Reload the current user with `User::loadRights()` and check `User::$status`
  instead of the deprecated `getrights()`/`$statut` (behavior unchanged). Add
  `test/unit/userreload.php` for the reload and permission paths.

- P2: admit Dolibarr >=21.0.0 without upper bound; separate objective Core
  capability checks from source expectations and qualification. Observe the actual
  Core R16 end while retaining independent known-contract checks at the 24.0.2 boundary.
- Apply the same invoice VAT literal policy on every version; remove V22 silent-zero
  fallback for unsupported settings. Keep subscription-only booking unchanged.
- Resolve empty PDF defaults by available Core models; preserve explicit settings.
- Add negative inheritance/observer, actual VAT/price-hook and stored-state tests;
  configure 21 pinned PHP/Core jobs. No new real integration or production approval.

- Merge PR #6 on 2026-11-15 as `96f870e789eb26b6eb8a6b8eeb0c79812018b0ab`,
  completing bounded V22.0.5/V24.0.3 compatibility development. Final independent
  review: APPROVED FOR MERGE – ALL CONDITIONS FULFILLED; OD-PR6-01/02 fulfilled.
  Post-merge CI run 43660371617 passed PHP 7.4/V22.0.5, PHP 8.2/V22.0.5 and
  PHP 8.2/V24.0.3. Retain `0.1.0-dev`, temporary ID `584620`, unexecuted scenarios
  and residual risks; production/public release remain NOT QUALIFIED / NOT APPROVED.

- Align exact V22.0.5/V24.0.3 R16 policies with each Core end-date rule (C1-01).
- Reject unsupported V24 membership VAT settings during invoice preflight (C1-02 / PR6-01).
- Use version-correct PDF defaults only when configuration is empty (C1-03).
- Supply both Core subscription-mail hook account keys (C1-04).
- Match the optional trailing Core `ref_ext` in the synthetic booking double (C1-05).
- Complete the bounded V24.0.3 C3 integration campaign: Core booking/invoices,
  customer creation, zero fee, R16, controlled transaction faults, PDF/ECM,
  authenticated replay, Chromium/Firefox and one owner-confirmed SMTP delivery.
  Final independent review and development merge are complete as recorded above;
  no production/public release approval.

- Add optional, transactionally linked and validated member-subscription invoices.
- Add explicit missing-third-party creation consent and deterministic invoice preflight.
- Generate and verify invoice PDFs after commit and require them for standard invoice email.
- Report invoice, customer, PDF, mail, rollback and uncertain outcomes separately.
- Add final-submit/BFCache UI handling and synthetic Phase 4 profile, transaction, file and mail tests.

- Add explicit, single-use booking confirmation and persistent per-member results.
- Book through the V22 member API with owned transactions, member locks and R16 checks.
- Send optional confirmation mail only after commit, preserving subscription hooks and templates.
- Disable invoice/third-party options and add synthetic transaction, replay and mail regressions.

- Match V22 single-subscription defaults for zero membership-duration quantities.

- Keep planned third-party creation dependent on invoice mode, including no-JavaScript forms.
- Map preview requirements to concrete tests and retain sanitized available browser evidence.
- Establish risk-based validation and compact reporting for subsequent phases.

- Add read-only per-member amount, eligibility, period, description and overlap services.
- Add interactive dates, manual-end preservation, explicit common/calendar actions and summaries.
- Add simulation-only invoice, third-party and mail preflight, without a booking endpoint.
- Separate one-hour previews from selection runs; enforce CSRF, owner/entity and stale-form checks.
- Add V22 calendar compatibility regression coverage, EN/DE UI and browser validation.

### Initial module scaffold and Dolibarr V22.0.5 hook integration

- Add an external module descriptor and member-list massaction entry.
- Validate member selection and open an authenticated session-bound technical assistant.
- Add EN/DE labels, synthetic contracts and minimal CI.
- Keep all subscription, invoice and email business processing for later phases.
