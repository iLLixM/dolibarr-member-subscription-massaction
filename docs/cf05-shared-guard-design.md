# CF05-01/02 – Shared active transaction guard (implementation handoff)

**Effective development baseline:** Issue #22 (Owner, 2026-11-21). **Scope:** focused implementation of CF05-01 and CF05-02 only. This is **not** a finance enablement, CF05-03..07 completion, live test permission or release approval.

## One transaction owner; two distinct responsibilities

`MsmBookingService` continues to own the **only outer DoliDB begin/commit/rollback per member**. It chooses a profile-aware **active guard** for the member: `MsmCoreGuardedTransaction` on CoreGuarded or the existing `MsmDatabaseState` for EnhancedMySQL/MariaDBSnapshot. That *exact already-selected instance* is now injected into each `MsmInvoiceService` factory call. The Invoice service never constructs, starts or ends a transaction, never constructs a CoreGuarded observer, and never substitutes a second P_S observer for its owned-context checks.

The shared minimum `MsmActiveTransactionGuard` interface has only three operations:
- `isFor($db)`: strict original DoliDB identity.
- `active()`: profile-dependent current owned-context observation.
- `requireSupportedIsolation($state)`: same profile guard's isolation admission for that observation.

It intentionally has **no** `begin()`, `commit()`, `rollback()` or `completed()` and cannot be used as another transaction manager.

`MsmDatabaseState` is **retained separately** in Booking and Invoice for DB-family discovery, existing `information_schema`/InnoDB engine verification, `MsmTransactionProfile` admission and read-only metadata. On restricted Oracle MySQL/CoreGuarded, using `MsmDatabaseState::family()` or `requireTransactionalTables()` requires only version/metadata/schema rights; it must never call `MsmDatabaseState::active()` or request Performance Schema permissions merely because a financial checkpoint exists.

## Protected Core call sequence

The Invoice service receives both the *metadata observer* and the *active guard*, with database-identity checks on each. It uses the injected active guard's `active()` and `requireSupportedIsolation()` exclusively for owned-context validation:

1. Direct service entry: authoritative feature/profile admission; CoreGuarded currently **feature-denied** (CF05-03 pending). A future CoreGuarded invoice admission additionally requires a CoreGuarded guard, not `MsmDatabaseState`.
2. Active guard before customer processing and **immediately before and after** `Societe::create_from_member()`.
3. Active guard before and after `Facture::create()`.
4. Active guard before and after `Facture::addline()`.
5. Active guard before and after `Facture::validate()`.
6. Guard checkpoints before and after persisted invoice/customer/link readbacks and after stored invoice fetch, then Booking's original guard checkpoint before a single Core-owned completion.

An unknown/changed active guard at any boundary stops further business APIs. Financial `MSMConsistencyError` or other generic Invoice/Third Party exceptions are **NOT** automatically qualified CoreGuarded business rollback origins. The existing scoped contribution-only rollback classification is not imported into post-invoice stages. Real SQL/deadlock/timeout/connection failures remain UNKNOWN/STOP; **CF05-04 still needs a dedicated origin-specific classification and independent review**. All synthetic fault cases must keep no automatic retry, foreign rollback or follow-on effects.

Existing EnhancedMySQL event and MariaDB snapshot observers retain their own active and isolation behavior; there is no new P_S permission requirement in CoreGuarded. D-01 order (preflight → SET RR → immediate Core BEGIN → locks → post-lock metadata/readback) remains in Booking unchanged.

## Explicit non-goals and remaining CF05 gates

- **CF05-03:** coherent server-authoritative feature enablement across profile/Preview/controller/Booking/direct services. CoreGuarded invoice/customer/PDF/mail remain **OFF**.
- **CF05-04:** strict, finite error origin and verified owned-context rollback qualification for the newly reached Core customer/invoice APIs.
- **CF05-05:** post-commit PDF/SMTP after suitable acknowledged nonterminal completion and dependable checkpoint; terminal CHAIN/RELEASE stop.
- **CF05-06:** exact V22/V24 Core/Agenda/COMPANY_CREATE/CONTACT_CREATE/BILL_CREATE/LINEBILL_INSERT/BILL_VALIDATE/engine and side-effect call-graph qualification.
- **CF05-07:** full PHP/Core synthetic regression, independent exact-head security review, Owner acceptance, then separately authorized **real CF03-NORMAL A/B/C/D**. Real CF03-FAULT is still deferred/OPEN, not a PASS.
- **Release:** a new v0.1.0 ZIP, actual hosted D installation, R0-04, R0-07 and separate Owner release GO remain OPEN.

## Regressions expected in this PR

- Both metadata observer and guard are bound to the same original DoliDB; foreign guard injection must STOP.
- Production factory always hands the **same active guard instance** to the Invoice service, not a newly made guard.
- Independent metadata checks and active ownership checkpoints; CoreGuarded direct owned checks must not make P_S queries.
- Before/after checks around customer/invoice/create/line/validate; at any invalidated guard checkpoint **no next Core API**, no automatic replay.
- Existing EnhancedMySQL/MariaDBSnapshot invoice integration doubles and profiles remain unchanged; PHP >=7.4 syntax; no new DB tables/migrations or released financial feature flags.

**Review limitation:** This pure implementation and synthetic test suite does not prove real Customer/Invoice/Agenda transactional semantics on restricted MySQL, physical transaction event identity, a qualified CoreGuarded financial rollback, real V22/V24 invoice outcome or ZIP/release admission.
