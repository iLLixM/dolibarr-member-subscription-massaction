# CF05-01/02 – Shared active transaction guard (implementation record)

> **Post-PR #36 status:** CF05-01/02 below is the historical PR #34 implementation contract; [PR #36](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/36) subsequently merged the Phase-3 finance, typed-error and postcommit code (`d0b15785c60b74aa7ad59a1c687db94193d159c6`), with **CoreGuarded production finance still OFF** and **F36-02/P2 OPEN**. See [Phase-3 record](cf05-phase3-finance-admission.md) and [CF05-06 next qualification](cf05-06-standard-core-qualification-plan.md).

**Effective development baseline:** Issue #22 (Owner, 2026-11-21). **Status: CF05-01/02 IMPLEMENTED, independently reviewed and SQUASH-MERGED** in [PR #34](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/34) (`main` merge `dc7a172bfd476467d7980cbf16a2657cfc1e729c`, reviewed PR HEAD `11d7dd3cf4bdcf831aa87782b7466d2378fc733f`). Independent final verdict `APPROVED FOR DEVELOPMENT MERGE`, **F34-01/P2 and F34-02/P3 CLOSED**, no new findings; [exact-head CI](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/44948777072) **9/9 PASS**. **No** finance enablement, CF05-03..07 completion, live test permission or release approval.

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

1. Direct service entry: authoritative feature/profile admission; CoreGuarded remains **feature-denied in production after PR #36** (finance business code merged; CF05-06/07 and Owner admission still pending). The implemented CoreGuarded invoice path additionally requires a CoreGuarded guard, not `MsmDatabaseState`.
2. Active guard before customer processing and **immediately before and after** `Societe::create_from_member()`.
3. Active guard before and after `Facture::create()`.
4. Active guard before and after `Facture::addline()`.
5. Active guard before and after `Facture::validate()`.
6. Guard checkpoints before and after persisted invoice/customer/link readbacks and after stored invoice fetch, then Booking's original guard checkpoint before a single Core-owned completion.

An unknown/changed active guard at any boundary stops further business APIs. Financial `MSMConsistencyError` or other generic Invoice/Third Party exceptions are **NOT** automatically qualified CoreGuarded business rollback origins. The existing scoped contribution-only rollback classification is not imported into post-invoice stages. Real SQL/deadlock/timeout/connection failures remain UNKNOWN/STOP; **CF05-04 still needs a dedicated origin-specific classification and independent review**. All synthetic fault cases must keep no automatic retry, foreign rollback or follow-on effects.

Existing EnhancedMySQL event and MariaDB snapshot observers retain their own active and isolation behavior; there is no new P_S permission requirement in CoreGuarded. D-01 order (preflight → SET RR → immediate Core BEGIN → locks → post-lock metadata/readback) remains in Booking unchanged.

## Explicit non-goals and remaining CF05 gates

- **CF05-03:** full finance implementation merged in PR #36 but production CoreGuarded admission remains **default-denied**; distinct qualification and Owner approval remain.
- **CF05-04:** finite typed pre-financial-Core business origin/owned-context rollback implemented in PR #36; unqualified Core customer/invoice/SQL failures still `UNKNOWN/STOP`.
- **CF05-05:** post-commit PDF/SMTP checkpoint gating implemented and synthetically tested in PR #36, **not enabled/real-qualified under CoreGuarded**; terminal `CHAIN`/`RELEASE` STOP.
- **CF05-06:** exact V22/V24 Core/Agenda/COMPANY_CREATE/CONTACT_CREATE/BILL_CREATE/LINEBILL_INSERT/BILL_VALIDATE/engine and side-effect call-graph qualification.
- **CF05-07:** full PHP/Core synthetic regression, independent exact-head security review, Owner acceptance, then separately authorized **real CF03-NORMAL A/B/C/D**. Real CF03-FAULT is still deferred/OPEN, not a PASS.
- **Release:** a new v0.1.0 ZIP, actual hosted D installation, R0-04, R0-07 and separate Owner release GO remain OPEN.

## Implemented synthetic regression evidence (reviewed PR #34 HEAD)

- Both metadata observer and guard are bound to the same original DoliDB; foreign guard injection must STOP.
- Production factory always hands the **same active guard instance** to the Invoice service, not a newly made guard.
- Independent metadata checks and active ownership checkpoints; CoreGuarded direct owned checks must not make P_S queries.
- Before/after checks around customer/invoice/create/line/validate; at any invalidated guard checkpoint **no next Core API**, no automatic replay.
- Existing EnhancedMySQL/MariaDBSnapshot invoice integration doubles and profiles remain unchanged; PHP >=7.4 syntax; no new DB tables/migrations or released financial feature flags.
- **F34-01:** negative tests verify qualified rollback only for previously admitted `preflight`/`subscription` reasons; customer/invoice/commit/unknown stages are excluded. They also inject active-guard loss *before* each Core write, and run actual non-overridden direct Invoice admission on a mismatched guard type **with the same DoliDB**.
- **F34-02:** `Societe::fetch()`, the member/Third-Party-link SELECT and the four direct Invoice SELECTs have explicit ownership checks. Synthetic fault injection verifies STOP immediately after `Societe::fetch()` and independently after each of the four direct Invoice SELECTs: no subsequent SELECT or Core operation follows an observed guard loss. The member-link SELECT and final stored `Facture::fetch()` retain explicit ownership checks but are **not separately fault-injected** in this test suite.
- **Executed test evidence:** `test/unit/f29-rights.php` **113/113 PASS** and `test/unit/invoice.php` **301/301 PASS**, verified in independent [9-job standard CI](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/44948777072). The direct rollback-classifier test and existing Booking catch regressions were accepted as sufficient **for the isolated CF05-01/02 test finding**; they do not qualify a new financial rollback origin or simulate an authorized real CoreGuarded invoice booking.

**Review limitation:** This pure implementation and synthetic test suite does not prove real Customer/Invoice/Agenda transactional semantics on restricted MySQL, physical transaction event identity, a qualified CoreGuarded financial rollback, real V22/V24 invoice outcome or ZIP/release admission.
