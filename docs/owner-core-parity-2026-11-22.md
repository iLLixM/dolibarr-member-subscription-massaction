# Binding Owner decision — 2026-11-22: Dolibarr Core parity with Massaction-specific safety

## Final implementation ratification and findings after PR #38

**Authoritative post-PR #38 state (2026-11-24):** [PR #38](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/38) was independently reviewed and **squash-merged** into `main` as `0998fbaf58155cca04d31ab99c6bb47420e53c72`; reviewed HEAD `b15b93580f995fb05a696a125c2a5fd995dd4bb3` has **identical Git tree** `a6e7256fb1572361c4dd4d850124ca7a6afff566`. The independent verdict was **APPROVED FOR DEVELOPMENT MERGE WITH DOCUMENTED LIMITATIONS**, with exact-source Standard 9/9 + Full 21/21 + Hosted 1/1 (**31/31**) synthetic CI PASS. Phase 4 development is merged; **Phase 5 real A–D integration, CF05-07, package, hosted and Owner release acceptance are NOT complete**. All older dated DRAFT/finance-default-deny/core-source-allowlist statements below are historical and not current requirements.

**Binding policy:** [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22) and [Owner Variant B](owner-core-parity-2026-11-22.md): Dolibarr Core parity plus Massaction-specific safeguards. Full contribution/optional invoice and conditional linked third party, validated line and relation, post-COMMIT PDF and optional mail/verified attachment are implemented and enabled for `CoreGuarded`, `EnhancedMySQL`, `MariaDBSnapshot`. Normal Core numbering/models/Agenda/triggers/extensions are accepted; **do not reintroduce** Core/trigger source SHA allowlists, a separate `PROV` veto or blanket CoreGuarded-finance default deny. Preserve server-owned rights/entity, fresh fingerprint/full confirmation, original consumed one-shot, same active owned transaction guard, transactional-table/readbacks, acknowledged completion, conservative `UNKNOWN/STOP` and no automatic retry/foreign rollback. `F36-02/P2` is OWNER-ACCEPTED **Core filesystem residual risk**, not technically removed.

**Independent disposition:** F38-01–06 `CLOSED`; F38-07–09 `SUPERSEDED BY OWNER VARIANT B` (not technically closed); F38-10–13 `INDEPENDENTLY CLOSED`; F38-14/P2 `INDEPENDENTLY CLOSED` (server-confirmed `manualend` restored only after fresh fingerprint revalidation); F36-01 historically closed. **Separate real incident:** `DOLIENV-ACC-01` in B remains `UNKNOWN/STOP`, diagnostic subscription #3 and rollback unconfirmed; lack of a persisted record does not prove rollback ACK. No replay/compensation. The later local B finance cases are Owner-accepted positive results; the historical incident is unchanged.

**Current dolienv topology:** A = local Dolibarr 24.0.3/PHP 8.2.35/MariaDB 10.11.19 `MariaDBSnapshot`; B = local Dolibarr 24.0.3/PHP 8.2.35/MySQL 8.4.13 `CoreGuarded`; C = same Dolibarr/PHP/MySQL versions `EnhancedMySQL`; D = hosted Dolibarr 22.0.5/MySQL 8.4.13/CLI PHP 8.3.37 `CoreGuarded`. A/B/C have separate schemas, webroots, accounts, sessions, document roots; B/C share MySQL server and local module source only. Legacy CF03 A/B labels and older shared A/C commands cannot be relabeled/reused. Earlier evidence on `46f78d8...` remains historical; current local qualification uses the merged PR #39 baseline. D isolated copy pending. [Canonical matrix](dolienv-environments.md).

**Current Phase 5:** Owner-accepted Codex outcomes on deployed `ba5c9a3121754fcbd4259bb8906ad1dd17f1ce00`: local A/B/C protected readiness 21 checks PASS; B01/B02 and A02/C02 bounded finance/PDF PASS; additional non-mail matrix 8/8 PASS; local B module mail/verified PDF attachments 3/3 PASS (two fresh bulk runs, local capture only). Historical B02 mail/attachment and DOLIENV-ACC-01 remain UNKNOWN/STOP, latter rollback unconfirmed. See [Phase 5 qualification register](evidence/cf03/phase5-local-qualification-2026-11-24.md). These accepted summaries suffice under modified OD-P5-02; no external reproduction of raw artifacts is asserted.

**Operational/release boundary:** Local readiness accepted for the tested scope; new cases require protected preflight, current source/profile binding, fresh fixtures, Owner authorization, controlled backups and independent readbacks. Full CF03-NORMAL A–D, A/C mail, remaining live/configuration coverage, isolated Hosted D, CF05-07/CF03-RISK, final R0-03, R0-04, R0-07 and separate Owner Release GO remain OPEN / NO-GO. CF03-FAULT destructive tests remain deferred. Never replay historical UNKNOWN operations.

---

> **Archived date-bound content below:** previous sources/labels/admission claims reflect their original times; the post-PR38 section above governs current status.

## F38-13/P2 – Core-falsy FACTURE_ADDON default parity (2026-11-22)

Independent F38-12 delta review of PR #38 exact HEAD `b8e23f321bd18f89ef55093663605f9dba807b3a` determined **F38-12/P2 INDEPENDENTLY CLOSED**, preserved F38-10/11 CLOSED and newly found **F38-13/P2 OPEN**, an otherwise supported Core fallback rejected when `FACTURE_ADDON` is numeric zero or string `'0'`. Dolibarr V22–V24 `getDolGlobalString()` casts both to `'0'`; Core `Facture::getNextNumRef()` uses `if (!getDolGlobalString('FACTURE_ADDON'))` to choose V22 `mod_facture_terre` or V23/V24 `mod_facture_mars`. The module previously used `=== ''`, rejecting `'0'` as an invalid model.

**Owner-authorized bounded corrective implementation:** use the same PHP-falsy test in existing `MsmInvoiceProfile::numberModel()`, retaining the existing `terre`/`mercure` aliases, model-name safety and F38-12 canonical read-only settings snapshot. Both `'0'` and integer `0` must produce the appropriate Core default without writing `$conf->global`; `'00'`, `'0.0'`, traversal and missing models stay denied. Positive two-member first Core-normalization cases and full exact-HEAD CI are required. **F38-13 status: FIX IMPLEMENTED / INDEPENDENT EXACT-HEAD DELTA CLOSURE PENDING.** No Owner-Variant-B change, no new source allowlist or transaction/one-shot changes. F36-02 remains OWNER-ACCEPTED CORE RESIDUAL RISK; real A–D/Release remain separate NO-GO gates.


## F38-12/P2 – batch numbering snapshot normalization (2026-11-22; independent closure pending)

The independent F38-10/F38-11 delta review of `970c318b2aaad3eb0b06b43b4e804eb26360cf41` declared **F38-10/P2 CLOSED** (strict original direct-finance confirmation) and **F38-11/P2 CLOSED** (Core numbering default/alias/legacy model lookup) but discovered **F38-12/P2 OPEN, DEVELOPMENT-MERGE BLOCKER**. In V22/23/24, `Facture::validate()` may normalize a missing, blank or legacy `FACTURE_ADDON` inside the same PHP request after booking the first member. A later per-member preview previously included the mutated raw setting in `invoice.settings`, changing the confirmation fingerprint although the effective numbering model did not change.

**Owner-authorized bounded fix:** `MsmInvoiceProfile::resolve()` writes only `$settings['FACTURE_ADDON'] = $number` into the returned *read-only confirmed snapshot*, with `$number` already resolved to the effective Core numbering model. The actual `$conf->global` configuration remains untouched; every other setting and the full fingerprint/one-shot/rights/entity/transaction safety contract remains bound. Positive synthetic two-member Booking/Invoice and model-alias tests simulate the first Core numbering normalization; changed effective number models and other financial settings still invalidate an old confirmation. **F38-12: FIX IMPLEMENTED / EXACT-HEAD REGRESSION AND INDEPENDENT FOLLOW-UP REVIEW PENDING** until the final CI and external review are documented. No Owner change to Variant B, no real A–D, no development merge/release GO. F36-02 stays OWNER-ACCEPTED CORE RESIDUAL RISK.


## F38-10/F38-11 focused correction – 2026-11-22 (implementation; external closure pending)

Independent holistic PR #38 review on `859ea280b3b726870cdb7727af72c04514c26e5f` reported two new P2 module-specific findings; verdict `CHANGES REQUIRED BEFORE DEVELOPMENT MERGE`. **F38-10**: the first directly invoked CoreGuarded Invoice service must match the original entire confirmed row, not only its fingerprint. **F38-11**: invoice numbering profile availability must match supported Core defaults/aliases and legacy `model/model.modules.php` lookup. The focused PR #38 correction adds strict original-row comparison (only the exact optional `_profile` carrier is excluded after validation), Core-version-aware read-only numbering normalization and legacy document-root lookup, plus synthetic first-call and V22/V23/V24 model regressions. **Both statuses: FIX IMPLEMENTED / INDEPENDENT EXACT-HEAD DELTA REVIEW PENDING; NOT CLOSED.** No Owner security-policy change, PHP source allowlist, new Core transaction qualification, real A–D writing, development-merge or release authorization follows. Final source CI results must be separately verified on the exact final correction HEAD.


**Scope:** Issue #22 / ADR-06, ADR-11, ADR-12, D-01/D-02 / PR #38. **Status: Owner-approved architecture; PR #38 implementation not independently reviewed or merged; real A–D and release gates OPEN.** This document **supersedes conflicting earlier date-bound default-deny and source-pinning text**, preserving it for audit.

## Normative product/architecture contract

1. Preserve complete contribution, invoice for existing or invoice-conditional new Third Party, invoice line/validation/link, selected invoice PDF and optional email/verified attachment for **CoreGuarded**, **EnhancedMySQL** and **MariaDBSnapshot**. PDF and SMTP remain downstream of suitable acknowledged nonterminal business COMMIT and original verified post-COMMIT checkpoint in the module; this deliberate Massaction sequencing may be more conservative than the single-member controller.
2. Trust Dolibarr Core's own transaction-nesting and extension behavior for `Adherent::subscription()`, `Societe::create_from_member()`, `Facture::create()/addline()/validate()`, numbering/customer-code/PDF models and enabled Agenda/Logevents/third-party triggers. Do **not** source-approve all Core, model or trigger PHP; do **not** reject otherwise configured Dolibarr operations solely for unknown source hashes, installed custom trigger, Agenda file copy/Logevents or a preexisting `PROV` directory.
3. Do **not** promise atomic SQL+filesystem rollback, faultless extensions or physical parity of transaction observation. ADR-12 distinctions remain: CoreGuarded has bounded connection/depth/ACK evidence; EnhancedMySQL and MariaDBSnapshot retain their separate observer contracts. Do not automatically compensate, retry, perform foreign rollback or replay.
4. Preserve *modular, not Core-PHP-pinning* Massaction safeguards: server permissions/entity, fresh member and row confirmation, confirmation fingerprint/profile revision, original one-shot/anti-duplicate session, active owned Guard with one Booking outer DoliDB transaction per member, transactional table checks, safe post-COMMIT PDF/mail actions, recipient/attachment verification, conservative UNKNOWN/STOP on uncertain action and per-member outcome distinction. Direct service entry points cannot borrow a fabricated capability.
5. Existing supported business-scope exclusions (e.g. automatic payment/bank posting is **out of v0.1.0 scope**; unqualified product/local-tax/packaging settings) are **not automatically revoked** by this decision. Review them separately against the single-member Core flow when they materially prevent the defined business process. Core's file/model lookup should accept normally configured installed model paths while rejecting user-supplied traversal.
6. New or upgraded Core versions and extensions follow Dolibarr's normal API/compatibility checks plus regression/real-test evidence, not a hard-coded catalog of 15 exact Core source blobs. PHP>=7.4 and existing MySQL/MariaDB profile/version target still apply where compatible with host Core.

## Finding disposition under the changed acceptance contract

| Finding | Binding current treatment | Reason and proof boundary |
| --- | --- | --- |
| F38-01/P2 | CLOSED, regression retained | PDF after-effect ambiguity => UNKNOWN/STOP |
| F38-02/P2 | CLOSED, regression retained | Hook/SMTP after-effect ambiguity => UNKNOWN/STOP |
| F38-03/P2 | CLOSED, regression retained | One-shot original recipient/entity/confirmed row |
| F38-04/P2 | CLOSED, regression retained | Full/Hosted critical-change triggers; update paths after removed source test |
| F38-05/P3 | CLOSED, regression retained | Finite mail/attachment evidence combinations |
| F38-06/P3 | CLOSED, regression retained | Contribution-only unknown mail => attachment not_required |
| F38-07/P2 | **SUPERSEDED BY OWNER CORE-PARITY DECISION**, review revised code | Technical source-qualification gate no longer an acceptance requirement; do not claim old reviewed source as technically safe |
| F38-08/P2 | **SUPERSEDED**, no new number-model SHA allowlist | Honor Core dynamic numbering/customer-code model lookup; validate safe model naming and availability |
| F38-09/P2 | **SUPERSEDED**, old guard removed | Agenda string-vs-int bug no longer in productive code; retained checks must use correct Core semantics |
| F36-01/P1 | HISTORICALLY CLOSED | Prior default-deny correction closed on original SHA; superseded Owner full feature activation |
| F36-02/P2 | **OWNER-ACCEPTED CORE RESIDUAL RISK**, real evidence pending | Pre-outer-COMMIT `Facture::validate()` may rename `PROV` directory; SQL rollback does not reverse it. No claim of atomicity or completed real A/D qualification |

**Meaning of SUPERSEDED:** previous independent findings were justified by their previous *source-approval policy*; the new Owner removed the requirement, not the underlying potential Core side effects. Neither the old review nor synthetic green CI can be cited as a real production-transaction proof.

## Delivery and review gates

- **PR #38 Development Merge** requires independent exact-HEAD holistic review of Core parity, correct deletion of the old source gate, sufficient massaction anti-replay/identity/transaction/side-effect controls, healthy standard/full/hosted CI and accurate current documentation. F36-02's accepted *Core-only* risk cannot by itself be treated as a source-pinning merge blocker. A new *module-introduced* error still blocks appropriately.
- **Phase 5 real A–D:** case-specific Owner GO, supported private A/B/C launcher and read-only preflight, one-shot fresh test fixtures and A/C serialized operation, independently reconciled DB, customer, invoice, line, subscription, PDF/hash/path, verified attachment and SMTP effects. Investigate any changed Core or custom-module behavior observed in real tests.
- **Release v0.1.0:** separate CF05-07 and financial risk acceptance, final-source 9/21/hosted CI, package ZIP/checksum, R0-04 hosted D and R0-07 independent acceptance and Owner release GO. CF03-FAULT destructive injections deferred/not passed.

## Archived evidence

`docs/cf05-06-standard-core-source-qualification.md` documents **historic read-only Core-source evidence only**, including the conditional PROV rename, and must never be used as the current product source-allowlist. Previous PR and reviewer decisions remain dated, immutable provenance. No actual DB/SMTP operation has been carried out by this policy update.
