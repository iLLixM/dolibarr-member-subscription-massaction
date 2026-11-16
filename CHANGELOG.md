# Changelog

## Unreleased

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
