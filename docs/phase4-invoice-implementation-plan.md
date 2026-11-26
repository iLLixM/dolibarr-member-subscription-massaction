# Phase 4 — Invoice implementation and validation plan

Current qualification status and applicable boundaries are recorded in the [M3.1 integrated assessment](m31-integrated-development-qualification.md).

## Implementation completion and next delivery sequence (2026-11-24)

**Authoritative post-PR #38 state (2026-11-24):** [PR #38](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/38) was independently reviewed and **squash-merged** into `main` as `0998fbaf58155cca04d31ab99c6bb47420e53c72`; reviewed HEAD `b15b93580f995fb05a696a125c2a5fd995dd4bb3` has **identical Git tree** `a6e7256fb1572361c4dd4d850124ca7a6afff566`. The independent verdict was **APPROVED FOR DEVELOPMENT MERGE WITH DOCUMENTED LIMITATIONS**, with exact-source Standard 9/9 + Full 21/21 + Hosted 1/1 (**31/31**) synthetic CI PASS. Phase 4 development is merged; **Phase 5 real A–D integration, CF05-07, package, hosted and Owner release acceptance are NOT complete**. All older dated DRAFT/finance-default-deny/core-source-allowlist statements below are historical and not current requirements.

**Original Phase 4A–4E steps below are preserved as historical plans.** The current financed contribution/invoice/Third Party/PDF/mail Phase-4 source has been completed and merged as PR #38; historical phrases such as `READY FOR PHASE 4B`, `CoreGuarded finance OFF`, `future implementation` or `DRAFT PR #38` are not current tasks. Owner O1–O7 business decisions remain authoritative where compatible with later Issue #22/Owner Variant B.

**Binding policy:** [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22) and [Owner Variant B](owner-core-parity-2026-11-22.md): Dolibarr Core parity plus Massaction-specific safeguards. Full contribution/optional invoice and conditional linked third party, validated line and relation, post-COMMIT PDF and optional mail/verified attachment are implemented and enabled for `CoreGuarded`, `EnhancedMySQL`, `MariaDBSnapshot`. Normal Core numbering/models/Agenda/triggers/extensions are accepted; **do not reintroduce** Core/trigger source SHA allowlists, a separate `PROV` veto or blanket CoreGuarded-finance default deny. Preserve server-owned rights/entity, fresh fingerprint/full confirmation, original consumed one-shot, same active owned transaction guard, transactional-table/readbacks, acknowledged completion, conservative `UNKNOWN/STOP` and no automatic retry/foreign rollback. `F36-02/P2` is OWNER-ACCEPTED **Core filesystem residual risk**, not technically removed.

**Next qualification work, not a rewrite of Phase 4:** remaining A/C mail and safe live/configuration scenarios, remaining Hosted D finance/PDF/mail and final exact package; CF05-07, R0-03/04/07 and Owner Release GO.

**Historical Phase 5 snapshot (2026-11-24):** A/B/C local readiness (21 checks), bounded finance/PDF, 8/8 additional non-mail and B 3/3 mail/attachment capture cases remain accepted at `ba5c9a3121754fcbd4259bb8906ad1dd17f1ce00`. Hosted D now has a fresh independent checkout/dedicated schema, Dolibarr 22.0.5, MySQL 8.4.13, Web PHP 8.4.28 CGI/FastCGI, CLI 8.3.37. Four roles and two separate contribution-only bookings, persisted result reload, replay denial and SQL readback PASS within scope on `0adc259d4739b44f9b1fb3a49574d7e1c318132c`; no D finance/PDF/mail/ZIP PASS. Modified OD-P5-02 accepts authorized summaries within scope, not independent external reproduction. See [current baseline](current-development-baseline.md), [local register](evidence/cf03/phase5-local-qualification-2026-11-24.md) and [D environment evidence](dolienv-environments.md).

**Historical operational/release snapshot (2026-11-24, current instructions in the linked M3.1 assessment):** Phase 5 PARTIAL; new cases require protected F32-MR-01 target-user preflight, actual source/profile/role binding, fresh fixtures, Owner GO, controlled backups and independent readbacks. Full CF03-NORMAL A–D, A/C mail, remaining D finance/PDF/mail/attachments, wider live safety/configuration, CF05-07/CF03-RISK, R0-03, exact hosted ZIP R0-04, R0-07 and separate Owner Release GO remain OPEN / NO-GO. R0-04 BLOCKED pending final package and its qualification. CF03-FAULT destructive tests DEFERRED. Historical B02 mail/attachment and DOLIENV-ACC-01 UNKNOWN/STOP (latter rollback unconfirmed) unchanged; no replay or compensation.


**Historical PR #45 checkpoint:** development squash `b026f1504db27308fc7326e2e88ec2e53b2f48d9`, reviewed tree identical; #44 consolidation completed and #41–#43 closed without merge as superseded. [Current baseline](current-development-baseline.md) governs source identity, F45-01/02 open test precision, F45-03 documentation follow-up and remaining gates. Next: focused F45 test-only follow-up, remaining A/C mail and D integration, final source/ZIP, operational risk and independent release review. No additional fundamental Phase 4 implementation stage planned.

---

> **Archived date-bound content below:** previous sources/labels/admission claims reflect their original times; the post-PR38 section above governs current status.

> **Post-PR #36 Phase-3 development checkpoint (2026-11-21):** [PR #36](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/36) was **squash-merged** to `d0b15785c60b74aa7ad59a1c687db94193d159c6` after independent review `33cb3da3b389f9df2870bc8c6223c86a959cb467`: F36-01/P1 **CLOSED**, F36-02/P2 **OPEN / CF05-06 REQUIRED**. Shared guard, CoreGuarded finance/auto-third-party, typed error and postcommit PDF/mail business code are now **implemented**, but operative CoreGuarded finance admission is **default-denied**, no full V22/V24 Core/trigger/FS or real A–D acceptance is established. The original Phase-4A/4B–4E material below is dated historical planning, not an instruction to activate finance. See [current baseline](current-development-baseline.md) and [next CF05-06 plan](cf05-06-standard-core-qualification-plan.md).

> **Post-PR34 status (2026-11-21):** CF05-01/02 shared active Booking→Invoice guard, separate metadata and Core/readback checkpoints are **merged and independently reviewed** ([PR #34](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/34), squash `dc7a172bfd476467d7980cbf16a2657cfc1e729c`). **CF05-03..07 remain OPEN**: do not enable CoreGuarded invoice/auto-third-party/PDF/mail or assert real transaction/ZIP acceptance on the basis of this infrastructure change. Current [CF05 design](cf05-shared-guard-design.md) and [baseline](current-development-baseline.md) control; the earlier Phase 4A milestones below remain dated history.

> **2026-11-21 update — historical Phase 4A/4B–4E plan.** Phase 4 was subsequently implemented and tested on bounded historical V22/V24 profiles. The new binding [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22) calls for **CF05-01..07** to extend the complete business massaction to *all three transaction profiles*, especially currently contribution-only CoreGuarded. Reuse O1–O7 business semantics where still valid; **do not** treat the 2026-11-10 proposed milestones or their `NOT EXECUTED` labels as the latest implementation status. The [current development baseline](current-development-baseline.md) defines shared guard, finite qualified rollback, post-commit PDF/mail, D-02 V22/V24 Core/trigger qualification and full-feature A–D real normal-case gates. No code, side effects or real test are authorized by this historical plan.

Status: **PHASE 4A FINALIZED – OWNER DECISIONS INCORPORATED – READY FOR PHASE 4B PLANNING**, design only, 2026-11-10. Previous architecture commit: `b28e5f9`.
Baseline: `5c511b7a2c6dee6d810ffc0c1469d1539d7e5a1c`.
Read with [architecture and source evidence](phase4-invoice-architecture.md), [71-row authoritative matrix](requirements.csv) and [test strategy](test-strategy.md).

No feature, test, configuration, schema, version or runtime changes are part of this architecture commit. The following PRs are proposals, not created PRs or approval to execute them. New invoice behavior remains disabled until its full path and gates are complete. No partial PR should expose a working-looking invoice checkbox backed only by preview code.

## Binding owner-decision summary

| Decision | Contract for subsequent PRs | Requirements / outstanding evidence |
| --- | --- | --- |
| O1 | Only ADHERENT_BANK_USE=invoiceonly preselects a new run; user choice persists; current entity; independent availability/rights and write gate; no Bank requirement inferred from Core UI | C08-03, C03-06–11, C01-06/07; 4B default/fingerprint matrix |
| O2 | One owned combined DB transaction including validation/R16; PDF/mail after confirmed commit | C04, C05-08/09/11; G2 integrity and G4 separate risk acceptance pending |
| O3 | Autocreate off by default; explicit invoice-linked consent/rights/current entity; Core code/contact/link/customer effects disclosed; no inaccessible-link replacement or duplicate merge | C05-03/04; 4B preflight and 4C real effects/rollback pending |
| O4 | Standard invoice, current Core issue date, confirmed gross fee, membership VAT/product configuration, zero allowed, company currency, no payments/bank | C05-05/06/10, C08-02; architecture initial-profile table and later real qualification |
| O5 | Invoice off/mail off: contribution only; off/on: standard mail without invoice; on/off: invoice+PDF without mail; on/on: own verified PDF mandatory in standard mail, no attachment checkbox | C05-09/11, C06-01–08; 4D artifact/hook evidence pending |
| O6 | Session/checkpoints, no journal/table/resume/rebook/reinvoice/resend; unknown stops and uses read-only manual reconciliation | C03-09/11, C04-07/08, C06-08; 4C procedure/evidence review; production journal decision later |
| O7 | Separate persistent-within-session results page; contribution/invoice/customer/PDF/mail/rollback/unknown display; 4E loader/BFCache/keyboard/focus/status and no-JS | C07-03–08; UI work remains unimplemented |

Preserve all 71 original requirement IDs/text and historical evidence. In the respective implementation PRs, update C08-03's deliberate configured-default deviation and C07-03/05/07/08's results-page alternative and actual test status. Do not mark planned functionality implemented during documentation finalization.

## 1. Sequence and prerequisites

1. O1–O7 are incorporated and binding; retain original-versus-Phase-3B deviations. Obtain a separate Phase 4B implementation instruction.
2. Implement pure preflight/snapshot contracts without enabling invoice booking.
3. Add combined database booking and its focused synthetic tests; qualify it on an explicitly authorized isolated V22 installation before activation.
4. Add post-commit document/attachment processing and its tests; separately authorize any real SMTP test.
5. Finish UI, real integration, independent review and owner acceptance. Deliver the O7 loading/BFCache/keyboard/focus/status scope while retaining the separate results page.
6. Qualify V24 separately before any support/release claim; source compatibility alone does not permit upgrading the existing V22 environment.

O6 selects the existing session/checkpoint model without new tables, journal, automatic resume/rebooking/reinvoicing/resend for the initial implementation. Production journaling is reserved for a later explicit decision, not a current 4C prerequisite. A release/module-ID reservation and production authorization remain separate from these development PRs.

## 2. Bounded proposed PRs

### 4A — Architecture and owner decisions (this deliverable)

- **Scope/IDs:** reconstruct C05-01–11, C06-05 and the dependency slice; compare actual V22/V24 contracts and original Core feature. No behavior change.
- **Files:** only the two Phase 4 documents. No matrix/test/status rewrites or production code.
- **Dependencies:** merged baseline, reachable historical sources, immutable V22/V24 source refs.
- **Risks:** incorrectly treating old invoice preview or Phase 3B acceptance as invoice evidence; undocumented requirements drift.
- **Checks:** source/ref verification, documentation consistency, relative links, 71-row matrix preservation, whitespace and exact changed-file list. No business tests or unnecessary regression rerun.
- **Acceptance:** viable recommended DB/PDF/mail boundary; alternatives, owner decisions and real-test gaps explicit; baseline/source/runtime unchanged.
- **Phase 3B impact:** none. **Next gate:** G0 documentation consistency and separate Phase 4B commissioning. O1–O7 are decided; invoice-risk acceptance remains pending.

### 4B — Invoice preflight and confirmed-state design

- **Scope/IDs:** C01-06/07; C02-01–03/11; C03-06–11; C05-01/03/04/05/06/08/10; C08-02/03. Add deterministic read-only invoice/customer/product/config context; preserve amounts/dates. Invoice consent remains unavailable for actual writes until 4C/4D are qualified.
- **Likely files:** `class/preview.class.php`, `lib/preview.lib.php`, strict request/state helpers in `lib/booking.lib.php`, `subscription.php`; focused preview/unit fixtures. Update affected CSV coverage/status only in the later implementation task, preserving all original IDs/columns. Add a small invoice-preflight DTO/helper only if necessary.
- **Dependencies:** binding O1 configured default, O3 restricted customer effects and O4 Core-parity profile; verified Core signatures and supported configuration profile.
- **Work:** for each new run, initialize invoice from `getDolGlobalString('ADHERENT_BANK_USE') === 'invoiceonly'` in current entity context, separately from availability/rights/write enablement. Only `invoiceonly` suggests checked; `bankdirect`, `bankviainvoice`, `0`, empty/missing/unknown values suggest unchecked and never cause payment operations. Keep autocreate off and mail independent. Preserve user overrides across refresh/edit/revisions; snapshot the effective setting and invalidate stale confirmation on configuration change without resetting user choice. Old session formats require explicit fresh consent. Recheck rights/object/entity; detect missing/inaccessible linked customer distinctly; plan current-entity autocreation; snapshot payment terms, invoice date/currency, customer tax/address, product/tax/packaging, rounding/model/config. Introduce state-version invalidation without reviving consumed runs. Never accept raw POST invoice/customer/link IDs or amounts.
- **Risks:** P4-01/02/04, stale configuration, field omissions; legacy confirmations unexpectedly obtaining new consent.
- **Tests:** T1 existing suites/lint/language parity; T2 all five O1 value categories, Bank module off/on independently of invoice availability, denied rights, user overrides surviving refresh/edit/revision, changed setting/entity and legacy consent; invoice off/on, autocreate dependency, rights loss, changed customer/product/config, zero amount, unsupported stock/packaging, forged fields, old state migration. Existing invoice-rejection tests must still prove writes are disabled at this stage; add adapter/preflight tests rather than deleting protection assertions.
- **Acceptance:** pure preflight has no business writes; exact snapshot contract documented; unsupported configurations fail clearly. Invoice-off output and required rights unchanged.
- **Phase 3B impact:** only shared preview/state paths; regression is mandatory. **Next gate:** reviewed snapshot contract and owner-approved supported scope.

### 4C — Combined contribution/customer/invoice database booking

- **Scope/IDs:** C04-01–09, C05-02–08/10/11, C03-09/11 and C01-06/07. One owned transaction per member, all business writes through Core APIs. No payment/bank calls, no PDF/mail inside the transaction.
- **Likely files/components:** `class/booking.class.php`; new narrow `class/invoice.class.php`, optionally `class/thirdparty.class.php`; `lib/booking.lib.php` result contract; targeted `test/unit/booking.php` extensions/new focused invoice runner if existing harness cannot express it. Keep `class/bookingmember.class.php` R16 behavior unless a demonstrated adaptation is necessary. No Core patches.
- **Dependencies:** 4B; decided O2 transaction principle and O6 session/checkpoints; separate risk acceptance remains pending; same connection/driver and engine profile; inventory of enabled triggers, document callbacks and numbering modules. Add guards for every actually touched table/profile, not an arbitrary claim that the original three-table check suffices.
- **Work:** lock/reload→contribution→customer if approved→invoice with exact subscription link→gross line→validate→stored-value/R16/link checks→owned commit. Map failures to known rollback versus unknown stop. Verify actual invoice identity/status, quantity, totals and relationship, not just positive method returns. Record contribution/invoice IDs only as confirmed successes after commit. Specify sanitized evidence fields, read-only reconciliation queries, storage/access/retention and apply architecture section 7.1 to actual code before seeking authorization for real 4C tests. No absence-of-row inference may trigger a retry.
- **Risks:** R05/R06/R08/R16 and P4-01/02/03. Core nesting and trigger effects can invalidate atomicity assumptions. Existing customer promotion/contact creation and provisional-file renames are part of review.
- **Tests:** T1/T2 normal and zero combined booking; each Core return failure/exception; link failure; validation rights; stale customer; R16 failure after invoice work; nested depth mismatch; commit/rollback uncertainty; one row succeeds/one fails; invoice-off path unchanged. T3 only after separate authorization: real Core DB methods, rollback at each meaningful stage, persisted link/totals/end, unchanged preexisting records and zero payment/bank effects. T4 concurrency/connection loss under a separately approved scenario.
- **Acceptance:** known rollback leaves no row-owned invoice/customer/contribution/link; verified commit retains all required objects; unknown stops without delivery/retry; triggers remain enabled in the approved profile. Do not enable full user invoice workflow while required PDF handling is missing.
- **Phase 3B impact:** highest shared-path risk; exact subscription-only transaction/R16/mail behavior must remain. **Next gate:** independent transaction review and real V22 evidence for supported configuration; no blanket transfer of old risk acceptance.

### 4D — Post-commit PDF and verified invoice mail attachment

- **Scope/IDs:** C05-09/11, C06-01–08 and related rights/results. Explicit Core PDF generation after confirmed commit; retain existing independent subscription mail.
- **Likely files/components:** new `class/invoicepdf.class.php`; `class/mail.class.php`, `lib/booking.lib.php`; focused artifact/mail tests and localized warnings. PDF adapter accepts invoice identity, not request paths; mail accepts a verified descriptor.
- **Dependencies:** 4C result/checkpoint contract, binding O5 required PDF for invoice+mail (no attachment checkbox), approved model/entity-root/hook policy. No global PDF suppression or new mail queue implied.
- **Work:** persist committed result before generation; reload authorized invoice; select Core model/language; capture errors/warnings; verify canonical readable PDF under correct entity root; checkpoint before standard mail. Handle hook 0/positive/negative/exception and identity replacement; keep positive handled distinct from sent. Preserve successful booking after any document/mail failure.
- **Risks:** R04/R07/R08, P4-02/05; metadata writes after file generation, symlink/path races, stale files and external hook transport.
- **Tests:** T1/T2 PDF negative/exception/positive-with-missing-file, wrong entity, traversal/symlink, wrong invoice, unsafe template path, metadata failure, no invoice/no attachment, missing PDF blocks required standard send, hook takeover/replacement, language/template and special-character rendering. Existing mail suites reused. T3 real selected model and document metadata checks with synthetic invoice, then separately authorized recipient/SMTP checks only if needed. No live email failure injection without explicit scope.
- **Acceptance:** PDF status truthful and separate; canonical own PDF only; no delivery before confirmed commit; missing/unsafe PDF never silently omitted from required invoice mail; no automatic retry/resend. Subscription-only standard mail still has no attachments.
- **Phase 3B impact:** mail signatures/orchestration; preserve old path and finally context restoration. **Next gate:** artifact/mail review and approved real evidence; unresolved hooks explicitly unsupported.

### 4E — UI, real integration and independent completion review

- **Scope/IDs:** C05-01–11, C06-05 plus C07-03–09, C08-03 and shared replay/permission behavior. Enable complete invoice option only after preceding gates. Clear customer creation consent, full confirmation and separate DB/invoice/PDF/mail results with authorized links.
- **Likely files:** `subscription.php`, preview/booking renderers, localization files; module JS for the O7 final-submit loader/BFCache increment, with usable no-JavaScript flow; existing integration checklists, requirements coverage and phase evidence docs. No Core list rewrite or global notification change.
- **Dependencies:** decided O1 default and O7 separate-results/loader/BFCache/keyboard/focus/status scope; 4B–4D tested contract; explicitly authorized isolated installation, current backups covering the actual pre-test state, permitted recipient and enabled-hook inventory. Symlink/module activation changes require their own authorized operational step, not incidental test setup.
- **Risks:** stale confirmation, browser replay, misleading counts, inaccessible links, long PDF/SMTP requests, remaining no-journal uncertainty.
- **Tests:** T1/T2 result totals, validated-invoice count/number/link, created customer, PDF/mail/hook, known rollback/unknown statuses, escaped/localized messages and loading/BFCache/keyboard/focus behavior; T3 authenticated HTTP, no-JS and owner/manual Chrome/Firefox acceptance below. T4 invoice concurrency, version and hooks according to actual risk. Store sanitized evidence durably, not only in `/tmp`; record exact tested SHAs and provenance.
- **Acceptance:** each selected option matches executed behavior; known DB failure and post-commit warning visibly different; result GET/refresh/Back cannot book/generate/send; correct linked objects; no payment/bank creation; all remaining original requirements either proved or explicitly deferred/approved. Independent review and invoice-specific residual-risk acceptance required.
- **Phase 3B impact:** UI and shared controllers; demonstrate invoice-off remains usable when invoice/customer modules or rights are absent. **Next gate:** owner acceptance for bounded development use; production release is a separate decision.

## 3. Focused validation catalogue (planned, not executed)

These scenario labels are planning references, not a second requirement matrix. On implementation, map final test identifiers into existing CSV rows and preserve historical provenance. Prefer existing runners and fixtures; add tests for new branches/failures, not an artificial Cartesian-product target.

| Group | Minimal decisive cases | Level / requirement focus |
| --- | --- | --- |
| Options/access | Invoice off with no invoice rights; invoice on with read/create/advanced validate/send combinations; internal/external user; missing customer, allowed creation, inaccessible existing link; lost right after confirmation | T2 then targeted T3; C01-06/07, C05-01/03/04/08 |
| Price/description | Mixed fees, explicit zero, null/empty history fallback, invalid negative; gross/VAT/local-tax/rounding supported profiles; packaging changes quantity; configured product unavailable/foreign; UTF-8 and one-time escaping | T2 and real line/PDF sample T3; C02-01–03/11, C05-05/06/10, C08-02 |
| State/freshness | Change customer/address/tax/product/amount/options/config after confirmation; forged links and amounts; old state version; two tabs; consumed run replay | T2 plus authenticated T3; C03-06–11 |
| DB success | Existing customer and approved new customer/contact; exact contribution→invoice link; validated status/ref, one line/totals; zero fee; R16; no payment/bank | T3 with Core methods; C04-01/02/09, C05-02–08/10 |
| DB failure | Negative/exception at customer/create/link/line/validate, stored-value mismatch; fresh third-party/member link rollback; prior unrelated data unchanged; row A success/B rollback | T2 controlled adapters + T3 focused real failure; C04-03/04/07/08, C05-11 |
| Reconciliation | Read-only classification of definitely not executed, verified success, proven rollback and unresolved outcome; missing row/ID/PDF or lost consent never authorizes retry; invoice/link/customer/R16 and document evidence | 4C procedure review before real tests; C04-07/08, C05-11, O6 |
| Uncertainty/concurrency | Foreign outer transaction, leaked nesting, physical COMMIT acknowledgement loss, rollback failure; two module connections same member; separate Core writer | T2 then specifically authorized T4; R05/R06, C03-09, C04-05/06/08 |
| PDF | Positive generation plus canonical file; negative/throw/missing/wrong format; wrong entity/root, symlink escape, stale file, metadata error, configured model | T2/T3; C05-09/11, C06-05, R04 |
| Mail | Off, standard success/failure/disabled; invoice requires verified PDF per O5; no invoice unchanged; hook 0/+/-/throw/replacement; unknown result not resent | T2 then separately authorized delivery T3; C06-01–08 |
| UI/results | Correct separate counts/IDs/links; escaping; persistent warning beside successful booking; refresh/Back/POST replay; keyboard and no-JS; final-submit loader, BFCache reset, keyboard/focus/status handling with no-JS fallback | T2/T3; C07-03–09 |
| Compatibility | V24 trailing subscription argument/schema; retained R16; customer alias; packaging/stock-managed difference; sponge model/common document hooks | T4 separate V24 installation; no runtime claim from signature match |

**T1 commands for later functional PRs:** lint changed PHP files, run existing `php test/unit/run.php`, `DOLIBARR_TEST_ROOT=<pinned V22 source> php test/unit/booking.php` (includes preview), and `php test/unit/maildelivery.php`; language parity and `git diff --check`. Follow existing CI PHP 7.4/8.2 contract where still supported. No duplicate standalone preview rerun when booking already includes it unless a failure justifies one. This documentation-only task requires formal/source checks, not rerunning those suites.

**T3 evidence contract:** exact code/Core SHAs, DB/document identity and approved backup, specific writable fixture scope and recipient, before/after relevant-row values/hashes, object/link/totals/end-date reads, trigger inventory, transaction outcome, document evidence and sanitized HTTP observations. Counts alone do not prove unchanged preexisting data. Normal login metadata changes must be handled explicitly, unlike the historical aggregate-user-hash limitation. Real fault injection remains distinct from adapters. Do not rerun a stopped write test without reconciliation and authorization.

**Operational guard:** existing read-only runner rights do not authorize invoice creation or new privileged programs. Current historic backups are not automatically the baseline for future writes; verify coverage of then-current state. Preserve existing records, isolate synthetic fixtures, log unique run IDs to the approved persistent result directory, and avoid raw credentials/tokens/documents in repository artifacts. No automatic cleanup, restore or extra “convenient” fixture writes without scope.

## 4. Manual browser acceptance to schedule

On approved V22 runtime and later V24 independently:

1. Invoice off: existing preview/confirmation/result flow, independent mail option, all old dates/amounts/R16 behavior unchanged.
2. Invoice on: default matches owner decision; autocreate depends on invoice and permission; missing/inaccessible customer messages differ; user sees creation intent, fee, tax/date policy and description before final confirmation.
3. Confirmed combined booking: invoice link opens the correct validated invoice; linked contribution/member/customer and totals agree with read-only DB evidence. Confirm invoice-side linked-elements UI and contribution-side navigation independently.
4. Known rollback: clear failed row without a misleading successful subscription/invoice count. Mixed rows retain successful results.
5. PDF/mail failure after commit: successful business IDs remain; document and mail warning are distinct; no “retry booking” button or silent attachment omission.
6. Refresh, repeated POST, Back/Forward and multiple tabs do not repeat effects. The planned loader applies to final submit only, cancellation/validation errors do not stick, `pageshow` restores UI.
7. Keyboard navigation, focus/status announcements, narrow screen, German/English, escaped unusual text and no-JavaScript forms remain usable. No global toast/notification changes.
8. Removed rights prevent access to results' invoice/PDF links and later delivery without undoing a prior commit. Run real shared-entity cases only when an authorized Multicompany environment exists; otherwise explicitly retain that gap.

Browser acceptance is complementary to authenticated HTTP and DB reconciliation; owner observations must be labeled as such. A success screenshot or received email alone does not prove transaction or attachment safety.

## 5. V24 qualification prerequisites

Use official V24.0.0 source `769c7db907099643558e77d7002c109cfda919e5`, a separate compatible initialized schema, document root and authorized synthetic fixtures. Review actual PHP/database prerequisites, model configuration, rights and installed extensions at that time. No downgrade/upgrade of the existing V22 instance is implied.

Re-run affected API contracts and real combined success/rollback/R16, zero invoice, canonical PDF, permissions and replay cases. Specifically test changed packaging behavior, `sponge` fallback, template-path restriction, model warning/exception propagation and common-document moreparams context. Verify subscription schema fields through Core APIs, not V22 SQL assumptions. Broader V23/V25 and other drivers remain out of scope. Reuse tests where possible, but do not equate identical method signatures with runtime qualification.

## 6. Gates and remaining technical/operational approvals

| Gate | Required evidence/decision | Blocking effect |
| --- | --- | --- |
| G0 Design | O1–O7 documented as decided; architecture/profile/reconciliation/plan consolidated | Documentation finalization only; separate 4B commissioning required. |
| G1 Read-only contracts | 4B configured-default, supported-profile and state/rights contracts sufficiently tested | Invoice write functionality remains disabled. |
| G2 Transaction | 4C independent review, qualified actual trigger/hook/numbering/document/DB profile, reviewed reconciliation procedure and explicitly authorized real rollback/link/zero/R16 evidence | No real write tests without separate authorization; no general invoice use before qualification. |
| G3 Document/delivery | 4D post-commit PDF, safe association, mandatory invoice attachment and hook evidence; separate real PDF/mail and SMTP authorization | No standard invoice mail without verified own PDF; no delivery approval from architecture alone. |
| G4 V22 acceptance | 4E exact-SHA CI, HTTP/browser/DB evidence, invoice-specific R04–R09/R16 and new-risk decisions | Bounded development acceptance only; not production release. |
| G5 V24 support | Separate runtime qualification above | V24 remains source-compared, NOT EXECUTED until satisfied. |

O1–O7 are decided, not pending approvals. Validation precedes commit; explicit PDF/mail follow confirmed commit. Actual permitted profiles/side effects, numbering/models, reconciliation evidence and V24 adapter details remain to qualify. Invoice-specific R04–R09 acceptance follows integration evidence at G4; Phase 3B acceptance is not transferable. Inadequately controlled side effects block a profile without global trigger suppression. Later production use/journaling, runtime changes and real test permissions remain separate. No new business conflict was found in this finalization; new evidence of one must be documented against the relevant requirement rather than changing O1–O7 silently.

## 7. Completion of this design task

Only documentation is to be committed locally with `docs: finalize Phase 4A owner decisions and invoice architecture`, after checking the exact two-file diff, whitespace, source links, matrix preservation and unchanged source/worktrees. No new tests have been executed and no invoice behavior has been implemented by this plan. No push, PR, main/tag change, module activation, symlink change, database action or mail is authorized here.

**PHASE 4A FINALIZED – OWNER DECISIONS INCORPORATED – READY FOR PHASE 4B PLANNING** — architecture decisions are incorporated; separate commissioning is required for Phase 4B, and no invoice implementation or financial operations are authorized.
