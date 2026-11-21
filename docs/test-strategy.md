# Required risk-based test strategy

## Updated v0.1.0 test gates (2026-11-21)

The current [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22) architecture/release decision supersedes the original blanket real destructive fault-test timing; all runtime UNKNOWN/STOP and safe synthetic/HTTP security contracts remain in force.

- **Completed CF05-01/02 development stage:** [PR #34](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/34) independently **APPROVED FOR DEVELOPMENT MERGE** at `11d7dd3cf4bdcf831aa87782b7466d2378fc733f` (squash commit `dc7a172bfd476467d7980cbf16a2657cfc1e729c`). F34-01/P2 and F34-02/P3 **CLOSED**. Executed synthetic `f29-rights.php` **113** assertions, `invoice.php` **301** assertions, [CI 9/9 PASS](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/44948777072) across selected PHP 7.4/8.2, V22/V23/V24. No real finance, release, or 21-job result follows.
- **For each further CF05-03..07 implementation PR:** focused security/transaction/rights/direct-service/profile/one-guard/invoice error-classification/post-commit regressions and independent **exact-head** implementation review; keep PHP >=7.4 syntax and profile B/C behavior.
- **For v0.1.0:** full authenticated real **CF03-NORMAL A/B/C/D** positive flows (existing and invoice-created Third Party, line/validation/link, PDF/SMTP verified attachment, member Agenda ON/OFF and small successful batch) with independent DB/filesystem/delivery evidence; all 21 pinned PHP/Core synthetic CI jobs, safe negative browser/security cases, independent risk review and Owner acceptance.
- **Later iteration:** destructive real `CF03-FAULT` cases, including ROLLBACK/lost ACK/reconnect/SQL errors, concurrency, destructive replay and controlled PDF/SMTP faults, remain **OPEN/NOT EXECUTED**. No runtime fault handling is waived; no prior invalid RB01-A rollback is promoted to PASS.
- **Operator gate:** [PR #32](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/32) is squash-merged, but F32-MR-01 (accepted limitation) requires a private, least-privilege A/B/C target-user launcher and successful read-only preflight **before new real witness execution**. A/C tests must be strictly serialized; every business write requires its own Owner GO.
- **Release:** new v0.1.0 ZIP without RC suffix, exact D hosted ZIP test, R0-04/R0-07 and separate Owner release GO. Synthetic CI and old POS01/POS02 do not qualify the new full workflow.

This is the default validation contract for subsequent phases. User authorization and
safe test-environment boundaries remain mandatory; this document does not authorize
business writes, migrations or changes to accounts, rights or configuration.

| Level | Scope | Required when |
| --- | --- | --- |
| T1 – Baseline | PHP lint, existing automated runners, language parity, Git status/diff/whitespace | Relevant code changes and CI |
| T2 – Change-specific | Changed behavior, regression cases, relevant business boundaries | Every functional change |
| T3 – Integration | Real Dolibarr/DB/HTTP/browser and permission boundaries | Integration risks and milestones |
| T4 – Extended | Version matrix, concurrency, failure injection, performance, broad regression | Concrete risks and before releases |

## Execution rules

- Reuse established tests and tools. Do not re-read every unchanged test or all 71
  requirements for a small change. Select the affected matrix rows first.
- Add tests for new requirements, observed defects, security risks or uncovered
  meaningful boundaries. Test/assertion counts are evidence, not targets.
- Permissions, integrity, booking, transactions, invoices, replay and email require
  deeper validation than text changes or presentation-only adjustments.
- Preserve working automated regressions in CI. Do not repeatedly run the same checks
  without changed code, a failure, or a concrete unresolved concern. CI-triggered runs
  are expected and need no artificial suppression.
- Keep successful output concise; save full logs to private files and inspect failures
  narrowly. Create a new harness only when available tools cannot cover the change.
- Real business-write tests require an explicitly authorized, suitable test environment.
  Read-only approval never authorizes creation of convenient fixtures or failure injection.
- Report synthetic and real tests separately. Missing integration evidence stays
  **NOT EXECUTED**, regardless of synthetic results. Unchanged row counts alone cannot
  prove that existing business records were not modified; also review executed paths.
- Retain anonymized reproducible evidence outside temporary storage before finishing.
  Never publish credentials, cookies, tokens, profiles, real member data or private DB
  details. Do not retrospectively claim missing artifacts were restored.

## Commands and traceability

`php test/unit/run.php` runs established module contracts and language-key parity.
`DOLIBARR_TEST_ROOT=/path/to/core php test/unit/preview.php` runs preview regressions
against the pinned V22 calendar APIs without application/database bootstrap.
Lint changed PHP files locally, inspect `git diff --check`, then inspect the final CI.
The existing workflow runs the entire automated suite on PHP 7.4/8.2.

`docs/requirements.csv` is the sole requirement-to-test mapping. The small legend in
`docs/test-coverage.md` explains its identifiers; it does not duplicate the matrix.
Use `test/integration/browser-smoke.md` for historical artifact provenance and the
focused read-only HTTP options check. Full historical browser repetition is unnecessary
when a changed path can be proved by a focused integration test.

Before accepting/merging, review the final diff, exact tested SHA, CI, target branch
and applicable protection/review rules. Never bypass repository protections.

## Compact completion report

Normally one or two pages:
1. Implemented changes.
2. Changed files and commits.
3. Executed tests, results and evidence.
4. Tests not executed and why.
5. New/remaining risks (link historical analysis rather than repeating it).
6. Status and recommended next action.

Expand only for significant new findings. Preserve prior detailed reports as history.
