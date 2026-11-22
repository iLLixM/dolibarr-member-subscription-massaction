# Required risk-based test strategy

> **CURRENT BINDING OWNER DECISION — 2026-11-22 / PR #38, CORE PARITY + MASSACTION SAFETY (DRAFT, NOT YET INDEPENDENTLY REVIEWED).** The Owner expressly **supersedes the earlier CF05-06 exact-Core/trigger-blob allowlist and PROV-directory prohibition**. The module trusts Dolibarr's normal `Adherent::subscription()`, `Societe::create_from_member()`, `Facture::create()/addline()/validate()`, numbering/PDF models, enabled triggers/Agenda/Logevents and extension behavior as the Core single-member workflow does. **Full `CoreGuarded` invoice, conditional auto Third Party, post-COMMIT PDF and optional verified-attachment email stay enabled**, along with `EnhancedMySQL` and `MariaDBSnapshot`. Removed as normative admission checks: 15-version invoice source hashes, standard trigger hashes, prohibition of custom enabled triggers, Agenda file-copy/Logevents settings and `PROV` existence check. Do not rebuild those allowlists under F38-08/09. Core model lookup supports installed model roots without unsafe model-name paths.
>
> **STILL BINDING:** Massaction's per-member rights/entity, server-confirmed preview/fingerprint and user/profile revision, controlled original DoliDB Guard, effective transactional-table/engine checks, consumed one-shot session, confirmed nonterminal completion, durable post-COMMIT PDF/mail checkpoints, verified attachment/recipient identity, and `UNKNOWN/STOP` on uncertain completion or external effects with **no automatic booking/PDF/mail replay or foreign rollback**. No guarantee that Dolibarr Core SQL and filesystem are jointly atomic or that arbitrary custom extensions are benign. **F36-02/P2 = OWNER-ACCEPTED CORE RESIDUAL RISK** (conditional pre-outer-COMMIT `PROV` rename); it is not proven impossible or technically fixed. **F38-07/08/09 = SUPERSEDED BY OWNER ARCHITECTURE DECISION**, not historically falsified or independently technically CLOSED. F38-01..06 remain CLOSED per prior reviews and regressions. F36-01 remains historically CLOSED on the old default-deny branch, while its later Owner activation is binding.
>
> **EVIDENCE AND RELEASE:** Newly added `test/unit/cf05-core-parity.php`, positive/negative existing/new customer and contribution/mail orchestration, standard/custom model regression and 9-/21-/Hosted synthetic CI are development evidence, **not real A–D**. Real CF03-NORMAL A–D needs separately Owner-authorized cases, F32-MR-01 private launcher/read-only preflight, A/C serialized use, DB/invoice/PDF/file/SMTP evidence, CF05-07/Owner risk review, later exact-ZIP/hosted D and separate release GO. Purposefully destructive CF03-FAULT is DEFERRED. Earlier statements below that require Core source/trigger pinning or productive CoreGuarded default deny are preserved **only as dated history**, not current policy. [Current decision and finding map](owner-core-parity-2026-11-22.md).


**PR #38 CF05-06 corrective checkpoint — 2026-11-22 (DRAFT / NOT INDEPENDENTLY CLOSED):** The new server-owned `MsmCoreFinanceQualification` implements a bounded supported-configuration gate for the Owner-authorized CoreGuarded invoice/Third-Party/PDF/mail workflow. **15 exact pinned V22.0.0–V24.0.3 Core invoice source blobs** and version-matched standard Agenda/Logevents trigger blobs are checked against installed content before a CoreGuarded invoice's first outer BEGIN. Unknown/patched Core, enabled third-party or unqualified core triggers, Agenda automatic file copying or enabled unqualified invoice Logevents are rejected; an existing provisional `PROV` invoice file/directory is refused immediately before Core `Facture::validate()`. The original rights/confirmation/same guard/one-shot/postcommit/no-retry contracts remain in force. **The finance feature gate remains ON for supported qualified Core configurations; this is not a blanket re-lock.** **F38-04/P2:** five remaining Massaction/CSRF/Admin/Period/Preview UI security entry points added to Full+Hosted CI with broad class/lib/admin fallback and a missing-path negative check. **F36-02/P2 and F38-07/P2:** BOUNDED MITIGATION IMPLEMENTED / INDEPENDENT SOURCE REVIEW PENDING, **NOT CLOSED** and no real A–D DB/FS/SMTP qualification; custom trigger side effects and external filesystem races are not certified. See [the source-qualification report](cf05-06-standard-core-source-qualification.md). **No Owner operational GO or release GO.**


## Updated v0.1.0 test gates (2026-11-21)

The current [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22) architecture/release decision supersedes the original blanket real destructive fault-test timing; all runtime UNKNOWN/STOP and safe synthetic/HTTP security contracts remain in force.

- **Completed CF05-01/02 development stage:** [PR #34](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/34) independently **APPROVED FOR DEVELOPMENT MERGE** at `11d7dd3cf4bdcf831aa87782b7466d2378fc733f` (squash commit `dc7a172bfd476467d7980cbf16a2657cfc1e729c`). F34-01/P2 and F34-02/P3 **CLOSED**. Executed synthetic `f29-rights.php` **113** assertions, `invoice.php` **301** assertions, [CI 9/9 PASS](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/44948777072) across selected PHP 7.4/8.2, V22/V23/V24. No real finance, release, or 21-job result follows.
- **PR #36 Phase-3 development is MERGED:** [PR #36](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/36) independent review head `33cb3da3b389f9df2870bc8c6223c86a959cb467` = identical tree to squash `d0b15785c60b74aa7ad59a1c687db94193d159c6`, `APPROVED FOR DEVELOPMENT MERGE`, **F36-01/P1 CLOSED**, **F36-02/P2 OPEN**. [Standard 9/9 CI](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/44956004981) + [hosted 1/1 CI](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/44956004947) on reviewed source; selected logs verify **101 CF01**, **303 Invoice** and **78 CF05** *synthetic-only* assertions. Positive CoreGuarded finance tests require test-local qualification; production `invoice/autocreate/sendmail` remain default OFF. No 21-job release PASS or real DB/SMTP qualification follows.
- **Phase-4 focused PR #38 (DRAFT):** The existing full 21-job `.github/workflows/ci-full.yml` is now also triggered by PRs modifying the narrowly defined PDF/mail/postcommit/one-shot security family, while the standard 9-job PR lane remains. The targeted hosted PHP8.3/V22.0.5 job additionally runs mail/PDF/postcommit checks on relevant Phase-4 changes. New synthetic regressions distinguish business completion, pre-/post-generator PDF ambiguity, independently checked invoice attachment, negative post-hook/SMTP results, finite status pairs, exact-profile original One-Shot Direct-Service authorization, checkpoint persistence failures, transport/hook acceptance versus actual delivery; check UNKNOWN effect STOP and no following member or automatic resend. This is **not** a real DB/SMTP/PDF integration or full release evidence. See [Phase-4 evidence plan](cf05-phase4-postcommit-offline-evidence.md).
- **For future CF05-06/07 and separately authorized finance admission PRs:** focused security/transaction/rights/direct-service/profile/one-guard/invoice error-classification/post-commit regressions and independent **exact-head** implementation review; keep PHP >=7.4 syntax and profile B/C behavior.
- **F36-02 source qualification is next:** [CF05-06 V22/V24 standard-Core and filesystem plan](cf05-06-standard-core-qualification-plan.md) must determine reachability/mitigation of conditional `Facture::validate()` `PROV` rename and supported trigger/engine/document paths **before** any CoreGuarded finance unlock.
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
