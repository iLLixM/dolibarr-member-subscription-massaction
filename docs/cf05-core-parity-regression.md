# PR #38 – Core parity and Massaction regression acceptance (Owner 2026-11-22)

## Final PR #38 regression disposition and exact-source evidence

**Authoritative post-PR #38 state (2026-11-24):** [PR #38](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/38) was independently reviewed and **squash-merged** into `main` as `0998fbaf58155cca04d31ab99c6bb47420e53c72`; reviewed HEAD `b15b93580f995fb05a696a125c2a5fd995dd4bb3` has **identical Git tree** `a6e7256fb1572361c4dd4d850124ca7a6afff566`. The independent verdict was **APPROVED FOR DEVELOPMENT MERGE WITH DOCUMENTED LIMITATIONS**, with exact-source Standard 9/9 + Full 21/21 + Hosted 1/1 (**31/31**) synthetic CI PASS. Phase 4 development is merged; **Phase 5 real A–D integration, CF05-07, package, hosted and Owner release acceptance are NOT complete**. All older dated DRAFT/finance-default-deny/core-source-allowlist statements below are historical and not current requirements.

**Independent disposition:** F38-01–06 `CLOSED`; F38-07–09 `SUPERSEDED BY OWNER VARIANT B` (not technically closed); F38-10–13 `INDEPENDENTLY CLOSED`; F38-14/P2 `INDEPENDENTLY CLOSED` (server-confirmed `manualend` restored only after fresh fingerprint revalidation); F36-01 historically closed. **Separate real incident:** `DOLIENV-ACC-01` in B remains `UNKNOWN/STOP`, diagnostic subscription #3 and rollback unconfirmed; lack of a persisted record does not prove rollback ACK. No replay/compensation. The later local B finance cases are Owner-accepted positive results; the historical incident is unchanged.

**Coverage contracts:** accepted Core normal numbering/model/Agenda/trigger execution, no source hashes; original confirmed full invoice row not fingerprint-only, canonical `FACTURE_ADDON` model across V22/V23/V24 and `'0'`/integer zero fallback, stable second-member fingerprint; F38-14 genuine controller `manualend` in confirmed row versus fresh Booking row, with both boolean values and rejection of changed real finance parameters. Original one-shot/file-session, same active guard, readback, rights/entity and STOP tests remain binding. These are **offline and synthetic** Core/DB doubles, not real A–D acceptance.

**Current Phase 5:** Owner-accepted Codex outcomes on deployed `ba5c9a3121754fcbd4259bb8906ad1dd17f1ce00`: local A/B/C protected readiness 21 checks PASS; B01/B02 and A02/C02 bounded finance/PDF PASS; additional non-mail matrix 8/8 PASS; local B module mail/verified PDF attachments 3/3 PASS (two fresh bulk runs, local capture only). Historical B02 mail/attachment and DOLIENV-ACC-01 remain UNKNOWN/STOP, latter rollback unconfirmed. See [Phase 5 qualification register](evidence/cf03/phase5-local-qualification-2026-11-24.md). These accepted summaries suffice under modified OD-P5-02; no external reproduction of raw artifacts is asserted.

**Operational/release boundary:** Local readiness accepted for the tested scope; new cases require protected preflight, current source/profile binding, fresh fixtures, Owner authorization, controlled backups and independent readbacks. Full CF03-NORMAL A–D, A/C mail, remaining live/configuration coverage, isolated Hosted D, CF05-07/CF03-RISK, final R0-03, R0-04, R0-07 and separate Owner Release GO remain OPEN / NO-GO. CF03-FAULT destructive tests remain deferred. Never replay historical UNKNOWN operations.

---

> **Archived date-bound content below:** previous sources/labels/admission claims reflect their original times; the post-PR38 section above governs current status.

> **Current environment convention — 2026-11-24:** dolienv **A = MariaDBSnapshot**,
> **B = CoreGuarded**, **C = EnhancedMySQL**, **D = hosted CoreGuarded**.
> [Current topology, bounded acceptance and privacy rules](dolienv-environments.md) control
> environment naming/readiness. Earlier CF03 labels, shared A/C bindings and dated
> evidence below retain their historical meaning; do not relabel/replay old tools.
> Current B finance acceptance is **BLOCKED (UNKNOWN/STOP)**; D has read-only
> browser/hosting-CLI evidence, not business-write qualification.


## F38-13/P2 – Core-falsy zero numbering default and confirmed multi-member regression

Independent F38-12 follow-up closed F38-12/P2 and identified F38-13/P2 (review HEAD `b8e23f321bd18f89ef55093663605f9dba807b3a`): Core's `!getDolGlobalString('FACTURE_ADDON')` treats the string `'0'` and stored integer `0` as falsy, selecting the V22 `mod_facture_terre` or V23/V24 `mod_facture_mars` default. The module formerly accepted only strict `''`, failing a legitimate Core fallback.

**Minimal production fix:** the preexisting `numberModel()` default condition matches Core falsy semantics; original F38-12 canonical effective-model snapshot, read-only Preview and every remaining security gate are unchanged.

**Required regression:** `test/unit/invoiceprofile.php` covers both `'0'` and `0` for modern model resolution, canonical snapshots before/after Core normalization, no Core global mutation, still rejects `'00'`, `'0.0'`, traversal and missing models, and preserves original true-model/finance-settings mutation cases. `test/unit/invoice.php` extends the TWO originally confirmed members' production Preview→Booking→Invoice synthetic core-normalization loop to both zero representations under pinned V22/V23/V24 versions. Existing F38-10/11/12 tests remain.

**Evidence status:** synthetic/hosted CI on FINAL exact HEAD must be verified separately; new F38-13 independently **OPEN until focused read-only delta review**. Full real SQL/trigger/document/SMTP/CF03-NORMAL A–D, CF05-07, R0-04, R0-07 and release Owner GO are not covered or authorized.


## F38-12/P2 canonical number-model confirmation and two-member batch regression (2026-11-22)

**Root cause:** `Facture::validate()` in supported V22/23/24 may update `$conf->global->FACTURE_ADDON` from missing/blank/`terre`/`mercure` to its canonical effective name during first-member validation. A later `MsmBookingService::book()` refreshed preview previously treated the unchanged *effective* numbering model as a changed confirmation because `invoice.settings.FACTURE_ADDON` held the raw pre-Core alias.

**Production change:** only `MsmInvoiceProfile::resolve()` includes canonical `$number` in the returned `settings['FACTURE_ADDON']`, using its existing read-only `numberModel()`; no writing to Core configuration or weakening of the complete fingerprint.

| Required case | Regression | Evidence boundary |
| --- | --- | --- |
| Absent, blank, `terre`, `mercure` before first invoice validation | `invoiceprofile.php`: canonical original vs normalized full plan; `invoice.php`: two-member synthetic Booking/Invoice batch with normalizing invoice double, both members successful and two invoice validations | Pinned V22/V23/V24 source-backed Core model resolution; **no real DB/SMTP** |
| Original confirmed member 2 following internal Core alias rewrite | `invoice.php`: full production Preview fingerprint equals refreshed member-2 fingerprint and Booking continues | Actual module Preview/Booking/Invoice call path with in-memory Core double |
| Genuine change to effective numbering model | `invoice.php`: negative Booking case returns `MSMChangedConfirmation` before any subscription/invoice Core business calls | Synthetic; unmodified original session consent |
| Other finance/configuration changes | `invoiceprofile.php` finance-setting negative, existing invoice, CoreGuarded Session, and postcommit tests | Core safety gates remain unchanged |
| No application configuration write | `invoiceprofile.php` explicitly checks original `$conf->global->FACTURE_ADDON` after `resolve()` | Read-only preview |

**Status:** F38-10/11 independently CLOSED; F38-12 implementation awaiting final exact-HEAD Standard 9/9, Full 21/21, Hosted 1/1 and independent F38-12 focused delta review. Does not supersede real A–D/case-by-case GO, CF05-07, exact ZIP, R0-04/R0-07 or deferred CF03-FAULT.


## F38-10 and F38-11 targeted follow-up regressions (2026-11-22)

| Finding | New executable evidence | Boundary |
| --- | --- | --- |
| **F38-10/P2** | `coreguarded-session.php` independently consumed capability for each changed FIRST Finance continuation (amount, start/end, description, invoice plan, injected `_profile`, additional field), unchanged original fingerprint, plus positive exact profile; `invoice.php` public direct service rejects original-capability amount/invoice tampering before Invoice Core API; `cf05-finance.php` changes row after confirmed subscription and rejects before invoice Core | Synthetic original File-Session and Core/DB doubles; no real DML/HTTP |
| **F38-11/P2** | `invoiceprofile.php` calls production `resolve()/models()` with Core V22/V23/V24 effective numbering defaults and aliases, direct custom numbering/PDF, read-only `dol_document_root` legacy fallback, invalid/missing models | Pinned checkout and synthetic file fixtures; no actual invoice numbering generation |

**Gate status:** Focused code and tests implemented in PR #38; external exact-HEAD follow-up closure PENDING. Recheck exact final source Standard 9/9, Full 21/21, Hosted 1/1. Preserve Owner Variant B, earlier Finding dispositions and separate real A–D/Release NO-GO.


**Development-only evidence plan.** Read with [binding Owner decision](owner-core-parity-2026-11-22.md), [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22) and ADR-06/11/12. **No real A–D or release claim.**

| Scenario / security property | Executable regression | Evidence type |
| --- | --- | --- |
| Member contribution without invoice, amount=0 and regular amount | `test/unit/booking.php`, `test/unit/cf05-finance.php` | Synthetic Core adapter and original guarded session |
| Invoice existing linked Third Party, line, validation, subscription link | `test/unit/invoice.php`, CF05 `existing` | Synthetic production service call shape/readback |
| Invoice auto-create Third Party only if selected with invoice | `test/unit/invoice.php`, CF05 `new`, `test/unit/invoiceprofile.php` | Synthetic Core API calls plus rights |
| Contribution-only mail success and exception without fictional attachment | CF05 `contribution_mail_success`, `contribution_mail_throws` | Postcommit adapter, no SMTP |
| PDF verified, attached and mail transport after suitable CoreGuarded completion | CF05 `existing` / `new`, PDF/mail/postcommit unit families | Synthetic file/hash and mail evidence |
| Standard Agenda and Logevents settings no longer rejected by source whitelist | CF05 `core_agenda_copy_parity`, `test/unit/cf05-core-parity.php` | Synthetic settings + read-only pinned Core inspection; **does not execute real Agenda trigger** |
| Installed Core/extension invoice numbering/PDF model lookup | `test/unit/invoiceprofile.php` custom directory fixture; `test/unit/cf05-core-parity.php` | Production model admission via injected directory seam; no arbitrary PHP execution |
| Real checked-out V22/V23/V24 Core business entrypoint availability | `test/unit/cf05-core-parity.php` on each pinned CI Core | Read-only source/method availability, **not real API integration** |
| Privilege/entity/revision/fingerprint/direct service and one-shot | `test/unit/profile.php`, `coreguarded-session.php`, `f29-rights.php`, `preview.php` | Negative synthetic; no real HTTP |
| Modified confirmation, duplicate/replay, lost/unknown Core transaction | `test/unit/booking.php`, `coreguarded.php`, CF05 `depth` and negative cases | Synthetic stop/rollback staging |
| Postcommit PDF/SMTP unknown/exception and no following member | CF05 `pdf_unknown`, `mail_unknown`, `pdf_throws`, `mail_throws`, `pdf_invalid_written`, negative transport/hook | Synthetic two-member STOP; no external transport |
| Full + Hosted security-relevant path trigger | `test/unit/ci-triggers.php`, Full/Hosted workflow filters | Static workflow regression |

**Acceptance:** Full 9-job standard / 21-job Full / 1-job hosted checks must succeed on the *identical final PR-HEAD*. Passing is not an assertion that arbitrary external Core plugins are benign. Source hash/pinning tests were removed by Owner policy, *not silently skipped*. Previously existing secured business scope exclusions and ADR-12 transaction observer distinction remain unchanged.

**Known gap:** Real V22–V24 database transaction, Core trigger/Agenda, customer-code numbering, PDF filesystem, hook/SMTP interactions, deployment ZIP and exact final-source installation have not been tested in this work package. They belong to Owner-authorized Phase 5 and release evidence.
