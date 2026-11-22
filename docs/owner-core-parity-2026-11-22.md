# Binding Owner decision — 2026-11-22: Dolibarr Core parity with Massaction-specific safety

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
