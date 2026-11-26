# Changelog

## Unreleased – development completion and next release gates

### 2026-11-26 – Integrated development review preparation

- PR #51 integrated the independently accepted Pre-Release Test-Hardening package with documented limitations. See the [M3.1 review basis](docs/m31-integrated-development-qualification.md) for exact merge/CI provenance and consolidated CF05/CF03 evidence.
- CF05-07 independent technical qualification and CF03-RISK independent assessment are complete; formal Owner acceptances remain pending. M3.2 Cross-Entity Variant B is Owner accepted; real cross-entity cases remain BLOCKED / NOT EXECUTED. Release v0.1.0 is NO-GO.

### 2026-11-24 – Historical PR #45 shared-hosting compatibility and PR #44 documentation consolidation

- PR #45 squash-merged as `b026f1504db27308fc7326e2e88ec2e53b2f48d9`, tree-identical to reviewed `7e5f64d07ab3830ce355cd745782c4f0c2a955b0`; APPROVED FOR DEVELOPMENT MERGE WITH DOCUMENTED LIMITATIONS; 31/31 exact-head CI.
- PR #45 removes only the unnecessary directory-listing/readability prerequisite in `MsmFileBookingCheckpoint::requireSession()`. The PHP `files` handler, nonempty session ID, plain absolute existing writable directory, canonical binding and actual reopen/read/write/close/readback remain required. A known session file may be accessible even when the directory cannot be listed (e.g. owner mode `0300`). Preserve the original consumed confirmation, one-shot/processing/result persistence, owned guard and finance/post-COMMIT checks. Do not widen provider permissions to make a test pass. The six concrete Core/model/PDF file readability checks remain a separate contract.
- Local accepted real cases on `ba5c9a3121754fcbd4259bb8906ad1dd17f1ce00`: protected readiness 21 checks, initial finance/PDF PASS within scope, additional non-mail 8/8 PASS, local B mail/verified attachments 3/3 PASS (capture only). Hosted D independent checkout and dedicated schema, Dolibarr 22.0.5, Oracle MySQL 8.4.13, Web PHP 8.4.28 CGI/FastCGI / CLI 8.3.37: four roles, two separate contribution-only cases, persisted result reload, consumed replay refusal and separate SQL readback PASS within scope on `0adc259d4739b44f9b1fb3a49574d7e1c318132c`. D finance/PDF/mail, broader live cases and exact ZIP OPEN; A/C mail OPEN. Modified OD-P5-02 accepts authorized summaries within scope, not independent external reproduction.
- PR #44 merged; #41–#43 closed without merge as superseded. Historical source identities unchanged.
- At this historical checkpoint F45-01/02 were open and F45-03 awaited review/merge. Subsequent closure is recorded in the current baseline. Draft #40 requires #45 correction; Phase 5 PARTIAL / release NO-GO. See [current baseline](docs/current-development-baseline.md).

### 2026-11-24 – Phase 5 bounded local qualification

**Historical Phase 5 snapshot:** Owner-accepted Codex outcomes on deployed `ba5c9a3121754fcbd4259bb8906ad1dd17f1ce00`: local A/B/C protected readiness 21 checks PASS; B01/B02 and A02/C02 bounded finance/PDF PASS; additional non-mail matrix 8/8 PASS; local B module mail/verified PDF attachments 3/3 PASS (two fresh bulk runs, local capture only). Historical B02 mail/attachment and DOLIENV-ACC-01 remain UNKNOWN/STOP, latter rollback unconfirmed. See [Phase 5 qualification register](docs/evidence/cf03/phase5-local-qualification-2026-11-24.md). These accepted summaries suffice under modified OD-P5-02; no external reproduction of raw artifacts is asserted.

**Operational/release boundary:** Local readiness accepted for the tested scope; new cases require protected preflight, current source/profile binding, fresh fixtures, Owner authorization, controlled backups and independent readbacks. Full CF03-NORMAL A–D, A/C mail, remaining live/configuration coverage, isolated Hosted D, CF05-07/CF03-RISK, final R0-03, R0-04, R0-07 and separate Owner Release GO remain OPEN / NO-GO. CF03-FAULT destructive tests remain deferred. Never replay historical UNKNOWN operations.

### 2026-11-24 – PR #38 Phase 4 completed (historical development checkpoint)

- [PR #38](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/38) independently reviewed and **squash-merged** to `main` as `0998fbaf58155cca04d31ab99c6bb47420e53c72`, tree-identical to reviewed `b15b93580f995fb05a696a125c2a5fd995dd4bb3`. Exact-head CI: **9/9 Standard, 21/21 Full, 1/1 Hosted SUCCESS**. Verdict: APPROVED FOR DEVELOPMENT MERGE WITH DOCUMENTED LIMITATIONS.
- Owner Variant B Core parity plus Massaction rights, original confirmed Preview/full-row, one-shot, same owned transaction guard and UNKNOWN/STOP. Contribution, optional invoice/conditional new Third Party, line/validate/link, post-COMMIT PDF and optional mail/verified attachment implemented across three profiles. No Core/trigger source-hash qualification, `PROV` veto or CoreGuarded finance default-deny.
- F38-01–06 CLOSED; F38-07–09 SUPERSEDED BY OWNER DECISION; F38-10–14 INDEPENDENTLY CLOSED. F36-02 retains Owner-accepted Core filesystem risk.
- Current dolienv A=MariaDBSnapshot, B=CoreGuarded, C=EnhancedMySQL, D=Hosted CoreGuarded. A/C bounded prior-source positives; old B finance UNKNOWN/STOP remains unconfirmed and corrected finance needs a **new** separately approved retest; D only read-only hosting evidence.
- **v0.1.0 NOT RELEASED**. Phase 5 real A–D, private F32-MR-01 launcher, actual module SMTP/attachment, CF05-07/risk review, R0-03 package, R0-04 hosted D, R0-07 and release Owner GO OPEN/NO-GO; CF03-FAULT destructive work DEFERRED.

---

> Historical record follows. Current status is defined above.

> **CURRENT BINDING OWNER DECISION — 2026-11-22 / PR #38, CORE PARITY + MASSACTION SAFETY (DRAFT, NOT YET INDEPENDENTLY REVIEWED).** The Owner expressly **supersedes the earlier CF05-06 exact-Core/trigger-blob allowlist and PROV-directory prohibition**. The module trusts Dolibarr's normal `Adherent::subscription()`, `Societe::create_from_member()`, `Facture::create()/addline()/validate()`, numbering/PDF models, enabled triggers/Agenda/Logevents and extension behavior as the Core single-member workflow does. **Full `CoreGuarded` invoice, conditional auto Third Party, post-COMMIT PDF and optional verified-attachment email stay enabled**, along with `EnhancedMySQL` and `MariaDBSnapshot`. Removed as normative admission checks: 15-version invoice source hashes, standard trigger hashes, prohibition of custom enabled triggers, Agenda file-copy/Logevents settings and `PROV` existence check. Do not rebuild those allowlists under F38-08/09. Core model lookup supports installed model roots without unsafe model-name paths.
>
> **STILL BINDING:** Massaction's per-member rights/entity, server-confirmed preview/fingerprint and user/profile revision, controlled original DoliDB Guard, effective transactional-table/engine checks, consumed one-shot session, confirmed nonterminal completion, durable post-COMMIT PDF/mail checkpoints, verified attachment/recipient identity, and `UNKNOWN/STOP` on uncertain completion or external effects with **no automatic booking/PDF/mail replay or foreign rollback**. No guarantee that Dolibarr Core SQL and filesystem are jointly atomic or that arbitrary custom extensions are benign. **F36-02/P2 = OWNER-ACCEPTED CORE RESIDUAL RISK** (conditional pre-outer-COMMIT `PROV` rename); it is not proven impossible or technically fixed. **F38-07/08/09 = SUPERSEDED BY OWNER ARCHITECTURE DECISION**, not historically falsified or independently technically CLOSED. F38-01..06 remain CLOSED per prior reviews and regressions. F36-01 remains historically CLOSED on the old default-deny branch, while its later Owner activation is binding.
>
> **EVIDENCE AND RELEASE:** Newly added `test/unit/cf05-core-parity.php`, positive/negative existing/new customer and contribution/mail orchestration, standard/custom model regression and 9-/21-/Hosted synthetic CI are development evidence, **not real A–D**. Real CF03-NORMAL A–D needs separately Owner-authorized cases, F32-MR-01 private launcher/read-only preflight, A/C serialized use, DB/invoice/PDF/file/SMTP evidence, CF05-07/Owner risk review, later exact-ZIP/hosted D and separate release GO. Purposefully destructive CF03-FAULT is DEFERRED. Earlier statements below that require Core source/trigger pinning or productive CoreGuarded default deny are preserved **only as dated history**, not current policy. [Current decision and finding map](docs/owner-core-parity-2026-11-22.md).


### 2026-11-22 – F38-04 / F36-02 / F38-07 bounded correction, PR #38 DRAFT

**PR #38 CF05-06 corrective checkpoint — 2026-11-22 (DRAFT / NOT INDEPENDENTLY CLOSED):** The new server-owned `MsmCoreFinanceQualification` implements a bounded supported-configuration gate for the Owner-authorized CoreGuarded invoice/Third-Party/PDF/mail workflow. **15 exact pinned V22.0.0–V24.0.3 Core invoice source blobs** and version-matched standard Agenda/Logevents trigger blobs are checked against installed content before a CoreGuarded invoice's first outer BEGIN. Unknown/patched Core, enabled third-party or unqualified core triggers, Agenda automatic file copying or enabled unqualified invoice Logevents are rejected; an existing provisional `PROV` invoice file/directory is refused immediately before Core `Facture::validate()`. The original rights/confirmation/same guard/one-shot/postcommit/no-retry contracts remain in force. **The finance feature gate remains ON for supported qualified Core configurations; this is not a blanket re-lock.** **F38-04/P2:** five remaining Massaction/CSRF/Admin/Period/Preview UI security entry points added to Full+Hosted CI with broad class/lib/admin fallback and a missing-path negative check. **F36-02/P2 and F38-07/P2:** BOUNDED MITIGATION IMPLEMENTED / INDEPENDENT SOURCE REVIEW PENDING, **NOT CLOSED** and no real A–D DB/FS/SMTP qualification; custom trigger side effects and external filesystem races are not certified. See [the source-qualification report](docs/cf05-06-standard-core-source-qualification.md). **No Owner operational GO or release GO.**


### Owner-requested full CoreGuarded finance re-activation (2026-11-22, PR #38 DRAFT)

- **Policy change (not independently approved):** The server-authoritative CoreGuarded finance gate returns `true` on the PR branch. `booking/invoice/autocreate/sendmail` feature flags and the dependent invoice PDF path are available under normal valid CoreGuarded configuration and unchanged rights/confirmation/guard/one-shot checkpoints. The earlier default-denied state remains a historically CLOSED F36-01 finding on its earlier exact source, **not evidence of this new unlock's safety**.
- **Risk boundary:** **F36-02/P2 OPEN / CF05-06 REQUIRED** remains materially relevant because the V22/V24 Core invoice validator may rename `PROV` documents prior to the outer commit. This was previously deferred only because finance was closed; now a reviewer must re-evaluate the opened path and any mitigation/support restriction before actual real finance writes. No new real test, production deployment, rollback guarantee, ZIP/tag or release GO is claimed.
- **Latest corrective scope:** F38-03 recipient/entity/confirmed-row one-shot binding; F38-04 complete full/hosted CI security-path filter test; F38-06 invoice-less mail exception-status consistency. All remain independent-review pending until exact-HEAD CI and delta review.

## Unreleased

### 2026-11-24 – dolienv documentation and bounded setup acceptance

- Establish current A=MariaDBSnapshot, B=CoreGuarded, C=EnhancedMySQL,
  D=hosted CoreGuarded naming, separate local B/C schemas and hosting-local
  read-only tooling. Preserve old CF03 letter/topology/evidence provenance.
- Record limited real A/C invoice/PDF/R16 acceptance, B finance UNKNOWN/STOP,
  local-only capture versus untested module mail, and D CLI/Web evidence limits.
- Document private timestamped A/B/C backup integrity checks (no restore),
  hosting protection rechecks and pseudonymization requirements. No product,
  security-policy, release or general compatibility change.


### Phase-4 development in PR #38 – checkpointed PDF/attachment/mail evidence, offline regression

- [PR #38](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/38) (draft, **not merged or production-admitted**) distinguishes the acknowledged Core completion, verified/stale/unknown PDF file, separately verified invoice attachment and actual mail transport/hook outcomes, without conflating a hooked send with delivery. The existing mail `send()` API remains compatible; `sendWithEvidence()` supplies independently reported attachment status.
- Under the qualified test-only CoreGuarded path, uncertain PDF generation or SMTP/hook outcomes retain the already committed business result, mark downstream effects uncertain or not processed and **STOP subsequent member actions** without blind regeneration, resend, rollback or compensation.
- Add offline tests of invalid/changed PDF and hook outcomes, post-commit UNKNOWN STOP, one-shot original file-session checkpoints and B/C regression boundaries. Trigger the existing **21-job full PHP/Core matrix** plus targeted hosted PHP8.3/V22.0.5 offline PDF/mail checks for this sensitive file family while keeping unrelated ordinary PR CI at nine jobs.
- **No actual CoreGuarded finance unlock**: `MsmTransactionProfile::coreGuardedFinanceQualified()` is still production `false`; F36-02 remains OPEN / CF05-06, real A–D and release gates remain OPEN.

### 2026-11-21 – Phase-3 CoreGuarded finance code merged, productive admission OFF (PR #36)

- [PR #36](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/36) **SQUASH-MERGED** as `d0b15785c60b74aa7ad59a1c687db94193d159c6`, reviewed HEAD `33cb3da3b389f9df2870bc8c6223c86a959cb467`, identical Git tree `6cc4834b9fe5100aca99126885791b3aeff12fef`. Independent `APPROVED FOR DEVELOPMENT MERGE`; F36-01/P1 **CLOSED**, F36-02/P2 **OPEN / CF05-06 REQUIRED**; no new P0–P3.
- Booking owns the sole DoliDB transaction; the original shared guard and consumed confirmation/file-session capability protect new CoreGuarded invoice/linked Third-Party operations. Typed **pre-financial-Core** business errors may qualify one original owned rollback only with intact verified context; other customer/invoice/Core/SQL/connection failures remain `UNKNOWN/STOP`, with no replay or compensation.
- Implemented acknowledged nonterminal post-COMMIT PDF/mail checkpoint gates, but file/SMTP effects are **not DB-atomic**. Production CoreGuarded `invoice`, `autocreate`, `sendmail` are **default-denied** by the authoritative profile until subsequent separate qualification/Owner GO; contribution admission remains. Positive finance orchestration is synthetic and test-only.
- Exact reviewed source [CI 9/9](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/44956004981) + [Hosted CI 1/1](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/44956004947) SUCCESS, with selected independent log assertions 101 CF01, 303 Invoice and 78 CF05 (synthetic). Conditional `Facture::validate()` provisional `PROV` directory rename remains **F36-02/P2 OPEN** for CF05-06. Real A–D, 21-job CI, ZIP/hosted D and release GO remain OPEN.

### 2026-11-21 – CF05-01/02 shared active guard merged (PR #34)

- [PR #33](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/33) squash-merged the documentation baseline as `d0d0fddd5797a1424eb004d541d8036511193ec6` after independent review. Historical documentation exception F33-DOC-03/P3 remained PARTIAL / OWNER ACCEPTED; it is not a runtime feature waiver.
- [PR #34](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/34) **squash-merged** to `main` as `dc7a172bfd476467d7980cbf16a2657cfc1e729c` (reviewed PR HEAD `11d7dd3cf4bdcf831aa87782b7466d2378fc733f`). CF05-01/02 uses **one shared active profile guard** between Booking and Invoice with separate DB metadata/engine checks and no second transaction manager/Performance Schema fallback for CoreGuarded.
- Explicit transaction checks surround `Societe::create_from_member()`, `Facture::create()`, `Facture::addline()`, `Facture::validate()` and every persisted Invoice readback. CoreGuarded rollback classification admits only previously qualified `preflight`/`subscription` stages; customer/invoice/unknown stages remain `UNKNOWN/STOP`.
- Independent exact-HEAD delta review concluded **APPROVED FOR DEVELOPMENT MERGE**; F34-01/P2 and F34-02/P3 **CLOSED**, no new findings. Standard [CI #44948777072](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/44948777072) **9/9 PASS** on the reviewed head; `test/unit/f29-rights.php` **113** and `test/unit/invoice.php` **301** synthetic assertions PASS.
- **No release or real financial admission:** CoreGuarded invoice/autocreate/sendmail gates stay OFF. CF05-03..07, real CF03-NORMAL A–D, release 21-profile CI, new exact ZIP and Owner GO remain OPEN. Real destructive CF03-FAULT remains deferred/OPEN. No one-shot fixture re-use.


### 2026-11-21 – Core-first full-function product baseline and PR #32 closure

- Owner-revised [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22): **v0.1.0 full massaction workflow across CoreGuarded, EnhancedMySQL and MariaDBSnapshot**. Revised ADR-06 removes contribution-only as a permanent target, **not** as a current safety gate: CoreGuarded invoicing/auto-third-party/PDF/mail are still closed until CF05-01..07.
- Revised ADR-11: focused CF05 implementation/security review, full real **normal/success** integration A–D and retained synthetic security tests/21-job PHP/Core matrix required for the release. Destructive real **CF03-FAULT** tests move to a later iteration, stay OPEN/NOT EXECUTED and do not relax UNKNOWN/STOP contracts.
- [PR #32](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/32) **squash-merged** on 2026-11-21 into `main` as `425980c43a51a9f27073133212f18825ac6da248`: CF03 read-only witnesses, one-shot private evidence, positive/negative synthetic tests and current-tree privacy remediation. Reviewed source `cbc97843fb017135739a39ea12ef26c7fa66dfdb` had 9/9 standard + 1/1 hosted CI PASS; production behavior unchanged.
- Independent merge review recorded F32-MR-01/P2 (sudo environment handoff) and F32-MR-02/P3 (historical evidence-parent prose), **both explicitly Owner-accepted as limitations, not fixes**. Real A/B/C witness use remains blocked pending private launcher/read-only preflight; source/code document the real evidence-parent write restriction.
- CF03 POS02-A1/A2 **Owner TECHNICAL PASS**, earlier exact-source F32-W01-01 CLOSED. RB01-A **INVALID/STOP as rollback evidence**: an ordinary contribution persisted, never silently reclassify or repeat. No full finance/ZIP/release GO follows from these development milestones.
- Documentation alignment clarifies current-vs-historical evidence and [release gates](docs/release-qualification-v0.1.0.md); no functional code, fresh real case or release package is created by this docs-only change.

- CF-02 / PR #29: implement bounded CoreGuarded contribution-only booking,
  server-side direct-service feature restrictions, transaction/terminal STOP
  and file-session admission. Independent final review of exact HEAD
  `290782dfdf6d6048b801843b5e1c4e464ac3bfdc` concluded **APPROVED FOR
  DEVELOPMENT MERGE WITH DOCUMENTED LIMITS**; F29-01/P1 and F29-02/P1
  **CLOSED**, no remaining P0/P1 blockers; standard PR CI **9/9 PASS**.
  New Owner-operated restricted-MySQL authenticated POS01-C formally **PASS**:
  member #58 / subscription #67, EUR 120, correct 2080 dates and persisted
  marker, one Agenda action/resource, matching Core R16 end, no unexpected
  protected-table count change. Independent readback and baseline integrity
  checked; physical commit-event identity is not claimed. Historical POS01-A/B
  STOPs and Owner review-entry exception are preserved. Separate Owner
  development-merge authorization fulfilled by normal PR #29 merge
  `f0b1e6d40ff55abb1e7555c800caea0fdd1fe90d`.
  D29-01/P3 documentation drift resolved. CF-03 real negative/other-profile
  regression, release artifacts, production and v0.1.0 approval remain OPEN.
- CF-01: add administrator-controlled transaction profiles with entity-specific revision/CAS,
  safe explicit MySQL migration and unchanged MariaDB Snapshot default. Separate read-only
  preview from final/per-member booking admission; invalidate stale confirmations without
  reopening consumed results. CoreGuarded contribution-only booking was later implemented,
  independently reviewed and development-merged in PR #29 as recorded above; no release approval.

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
