# CF05 Phase 3 – CoreGuarded financial feature admission and error provenance

**Development status: IMPLEMENTATION IN DRAFT PR #36 / INDEPENDENT REVIEW PENDING.** Binding product/architecture baseline: [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22), [ADR-0001](adr/ADR-0001-core-first-transaction-profiles.md) and its [risk register](adr/ADR-0001-risk-register.md). **This is not a release, real integration PASS, or deployment authorization.**

## What changed

- The server-authoritative `MsmTransactionProfile::current()` admits the existing invoice, invoice-linked `autocreate` and optional mail features also on the explicitly selected **CoreGuarded** profile, without changing the configured profile binding, nonce/revision, CoreGuarded/Enhanced/MariaDB observer selection or read-only Preview behavior.
- Existing confirmation/CSRF, permissions, one-shot file-session consumption, fresh re-preview, rights/entity/customer/product/engine/Agenda checks, lock order, guard identity and direct Invoice admission remain mandatory. Neither an unchecked UI switch nor a different database observer can enable service DML.
- The **same already-active profile-aware guard** and original Booking-owned DoliDB transaction are used through `Societe::create_from_member()`, `Facture::create()`, `Facture::addline()`, `Facture::validate()`, customer/member and persisted invoice readbacks. The Invoice service receives the *original consumed file-session capability* from Booking. It does not construct a new guard, a DB transaction or a reusable booking authorization.
- The original session capability is one-shot for each member's Invoice continuation and disallows fabricated direct-service calls without prior consumption and original owner/option/preview row matching. Direct Invoice service still checks the actual configured profile and requires a `MsmCoreGuardedTransaction` active guard (not `MsmDatabaseState` or P_S) under CoreGuarded.
- **Post-commit PDF/mail:** a suitable acknowledged **nonterminal** completion (typically `NO_CHAIN`) and the persisted, independently re-read `processing` file-session checkpoint are prerequisites. PDF and mail each have separate one-shot gate checks; invoice email requires a persisted *verified* invoice PDF, and the mail adapter independently rechecks the actual attachment. No effects or next-member work follow `UNKNOWN` or terminal `CHAIN`/`RELEASE`.

## Finite fault-provenance / rollback contract

| Origin | Example | CoreGuarded disposition |
| --- | --- | --- |
| Previously qualified `preflight` or `subscription` stage | Existing finite business reasons `MSMChangedConfirmation`, `NotEnoughPermissions`, `MSMTransactionUnsupported`, `MSMConsistencyError` | **Only when** the original one-owner rollback context remains demonstrably active/fresh and `beforeCompletion()` passes: one Core rollback, otherwise `UNKNOWN/STOP` |
| Local, typed `finance_precheck` before *any* Core financial object API | `MsmQualifiedCoreFinanceBusinessError` with specifically `MSMChangedConfirmation` or `NotEnoughPermissions` | Same strict known-owned rollback requirement. **Plain `RuntimeException` with identical message does not qualify** |
| `Societe::create_from_member()`, `Societe::fetch()`, member/customer-link SQL; `Facture::create()`, `addline()`, `validate()`, all persisted Invoice/line/link/party readbacks | API returned failure, exception, mismatched stored value, SQL/rights/hook side-effect error | **`UNKNOWN/STOP`**, no generic financial rollback, no compensation, retry or next-member business work. **No claim** that unknown writes were fully rolled back |
| Connection/owner/depth mismatch, SQL deadlock/timeout, lost BEGIN/COMMIT/ROLLBACK acknowledgement | Unclassifiable transport/SQL or ownership/terminal end ambiguity | `UNKNOWN/STOP`; no foreign rollback, no automatic new transaction, PDF, SMTP or next member |
| Acknowledged `CHAIN` or `RELEASE` terminal completion | Core COMMIT/ROLLBACK with terminal semantics | STOP before further module SQL/PDF/mail/next member; no fabricated nonterminal continuity |

**Important:** A typed error is *not* proof of an intact transaction. `MsmCoreGuardedTransaction::rollbackContext()` and `beforeCompletion()` must pass on the **original** bound guard immediately before the **single Booking-owned** `$db->rollback()`. CoreGuarded still cannot demonstrate a physical transaction-event ID. In particular the source is not permitted to classify arbitrary Dolibarr Core/trigger failures as safe rollback merely because their return code looks business-related.

## Synthetic evidence and non-goals

New focused tests cover: authoritative profile enablement, mismatched guard/profile/direct service without consumed capability, typed versus generic error origins and future stages, one-shot persisted session/finance capability and PDF/mail continuation, and synthetic end-to-end Booking→Invoice with existing/new Third Party, a qualified pre-Core business rejection, unqualified customer/invoice negative outcomes and no later API/SMTP after uncertainty. These are **synthetic** tests; see the exact-head CI of PR #36 for actual pass/fail status.

**Still not proven by this PR:** The exact real **V22.0.5/V24.0.3** Third Party/optional contact, invoice line/validation, standard Agenda/trigger call graph and potential non-transactional side effects in environments A/D; physical CoreGuarded transaction event identity (not available); actual InnoDB tables, real PDF output/filesystem and isolated SMTP transport; CF03-NORMAL A/B/C/D with fresh Owner-approved one-shot fixtures; full **21-job release CI**; real D exact ZIP installation, R0-04/R0-07 and separate Owner release GO. **CF05-06/07 and all real release/acceptance gates remain OPEN**. Real destructive CF03-FAULT is deferred/OPEN/NOT EXECUTED, not silently passed.

The legacy F32-MR-01/P2 private target-user launcher and read-only preflight restriction remains **OWNER ACCEPTED / NOT FIXED** before any further real A/B/C witness use. Do not repeat consumed POS02/RB01 fixtures. No new DB schema/journal, payment or automatic retry was introduced.
