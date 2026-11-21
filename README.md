# Member Subscription Massaction for Dolibarr

**Status: active development, not released or production-qualified.** This repository is the Dolibarr module for previewing, confirming and processing bulk member subscriptions with optional invoicing and delivery. The **v0.1.0 product target is the complete business workflow on each of the three transaction profiles**. That target is **Owner-approved but not yet fully implemented or qualified**. See [the current development baseline](docs/current-development-baseline.md) and the binding [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22).

**Development checkpoint (2026-11-21, after PR #34):** [PR #32](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/32) integrated read-only CF03 witness/evidence tooling; [PR #33](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/33) aligned the Owner strategy and documentation; [PR #34](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/34) is **SQUASH-MERGED** into `main` as `dc7a172bfd476467d7980cbf16a2657cfc1e729c`. **CF05-01/02 shared active guard integration is implemented, independently reviewed and merged** (reviewed source `11d7dd3cf4bdcf831aa87782b7466d2378fc733f`, F34-01/P2 and F34-02/P3 **CLOSED**, standard CI [9/9 SUCCESS](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/44948777072)). **CF05-03..07 and full-feature qualification remain OPEN**. This development merge has not enabled CoreGuarded invoicing, auto-Third-Party creation, PDF or mail. See [current phase status](docs/current-development-baseline.md#cf05-0102-merged-implementation-checkpoint) and [CF05 shared-guard design](docs/cf05-shared-guard-design.md).

## Intended v0.1.0 functionality

For each selected, authorized member, the confirmed massaction must support:

1. A fresh preview with business eligibility, rights, entity, type/fee/date, duplicate/overlap and confirmation checks.
2. The member contribution and Core-controlled R16 subscription end date, with standard member Agenda events where configured.
3. **Optional invoice** for an existing linked third party, or **optional creation of a third party only when an invoice is selected**, using Dolibarr Core APIs. Add invoice line(s), validate and link the invoice, and verify persisted business postconditions.
4. **Optional invoice PDF** after successful, suitable acknowledged DB completion; **optional contribution email**, including a separately verified PDF attachment when the invoice-mail path requires it.
5. Member-specific status for booking, invoice, PDF and delivery, including explicit `UNKNOWN/STOP` without automatic replay or recovery.

No standalone third-party creation, automatic payment/bank entry, new module persistence journal or claimed transactionally atomic SMTP/PDF behavior is part of v0.1.0.

### Transaction profiles: plan versus current implementation

| Profile | Backend and transaction evidence | **v0.1.0 target** | **Current development implementation** |
| --- | --- | --- | --- |
| `CoreGuarded` | Restricted Oracle MySQL; bounded Dolibarr Core ACK/connection/transaction-depth continuity, **no physical event identity** and no general Performance Schema SELECT | Contribution, linked/created third party for invoice, invoice, PDF, email | **Contribution-only admitted**; **CF05-01/02 shared guard and invoice checkpoints merged**, but invoices/auto-third-party/PDF/mail remain closed pending CF05-03..07 and separate qualification |
| `EnhancedMySQL` | Qualified Oracle MySQL, explicit P_S permissions/instrumentation, own transaction event observations | The same full workflow | Historical bounded invoice/third-party paths exist; retain their admitted scope, requalify the complete new workflow |
| `MariaDBSnapshot` | MariaDB >= 10.11 with bounded `@@in_transaction` snapshot checks, no MySQL event-equivalence claim | The same full workflow | Historical bounded invoice/third-party paths exist; retain their admitted scope, requalify the complete new workflow |

**Equal intended business capabilities do not mean equivalent guard evidence or current feature enablement.** The module does not silently downgrade from configured EnhancedMySQL to CoreGuarded. Read-only preview is not an authorization to book.

## Architecture and safety

Booking owns **one outer Dolibarr DoliDB transaction per member**. **CF05-01/02 now injects the same bound, active profile-aware guard** from Booking into Invoice, while retaining a separate `MsmDatabaseState` for DB-family/engine metadata; the invoice constructor never creates a second guard or transaction manager. Before/after checks surround `Societe::create_from_member()`, `Facture::create()`, `Facture::addline()`, `Facture::validate()` and the persisted readbacks. Future CF05 work must qualify these paths for actual CoreGuarded financial admission. Use standard Core business APIs, preserve the targeted real Core/Agenda/trigger/engine call-graph qualification and verify saved values before completion.

For CoreGuarded, the bounded entry sequence is **preflight → `SET TRANSACTION ISOLATION LEVEL REPEATABLE READ` → immediately Core BEGIN → locks → business reads** on the same connection. Do not claim P_S-like physical transaction identity. A classified, specifically qualified business failure may allow exactly one owned rollback only while the owned context is demonstrably intact. Unclassified SQL, connection, deadlock, completion or ownership errors stop with `UNKNOWN`; never roll back a foreign or replacement transaction. Known terminal `CHAIN`/`RELEASE` completions stop before PDF, mail or the next member; post-commit processing needs a suitable acknowledged nonterminal completion and a dependable checkpoint.

Server-side confirmation, CSRF, permissions, entity, revision/CAS, one-shot session/checkpoint and direct-service restrictions remain mandatory. No automatic rebooking, reinvoicing, resend, compensation or whole-batch atomicity guarantee. Arbitrary third-party hooks and nontransactional side effects are outside the bounded guarantee. See the [current baseline](docs/current-development-baseline.md) and [ADR-0001](docs/adr/ADR-0001-core-first-transaction-profiles.md).

## Supported platforms and qualification

The documented release compatibility target is **Dolibarr V22, V23 and V24 (Owner-confirmed)**. Retain that target as a product statement, while distinguishing synthetic/source checks from *actual real integration evidence*: **V23 has synthetic coverage, not a completed real V23 integration campaign**. V22.0.5 and V24.0.3 have separately pinned historical evidence, but the complete new CF05 workflow still requires fresh A–D integration. Capability admission at Dolibarr >=21 alone does not assert real support for every version.

- **PHP:** module syntax minimum 7.4, subject to Dolibarr/Core requirements; CI includes PHP 7.4/8.2 and a targeted PHP 8.3/V22 witness lane.
- **Database:** Oracle MySQL >=8.4 (CoreGuarded; Enhanced requires separate P_S qualification), or MariaDB >=10.11 (MariaDBSnapshot), direct `mysqli`/InnoDB under the qualified contracts. PostgreSQL is not supported.
- **Module ID:** `584620` is an unreserved development/community ID retained for the bounded v0.1.0 plan under the Owner's [Issue #20 decision](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/20); no permanent ID assignment is implied.

## Development setup (approved isolated environments only)

1. Use an appropriate, explicitly approved Dolibarr test installation and check out this module separately. The repository root is the module root, without an extra `htdocs` layer.
2. Install/link it under `htdocs/custom/membersubscriptionmassaction`, with normal web-server read/traverse permissions. Do not overwrite existing deployments without a separate change decision.
3. Enable the Dolibarr **Members** module, then enable **Member Subscription Massaction** using the admin module list. Activation changes configuration; this is not a read-only operation.
4. Configure the explicit, backend-compatible transaction profile. Unknown legacy/upgrade MySQL installations require an explicit admin decision; changes invalidate pending confirmations. Do not automatically switch profile/account, and **never operate A and C concurrently** (they share a reference schema/webroot).
5. Grant suitable member read/subscription rights. In the member list, choose **Create member subscriptions (bulk)**, inspect the read-only preview, select only server-admitted options, confirm and execute using normal module controls. Do not use currently closed CoreGuarded invoice/auto-third-party/PDF/mail options as if CF05 were implemented.

For local technical evaluation, see the [CF03 operator runbook](docs/cf03-operator-runbook.md). It contains historical command patterns; **A/B/C real witness execution remains blocked until a protected target-user launcher with correct `CF03_*` environment transfer has passed a private read-only preflight** (accepted F32-MR-01 limitation). Every real business-write case requires separate prior Owner authorization. Never reuse historical one-shot test fixtures.

## Tests, evidence and remaining gates

- Standard PR CI: **nine** selected PHP/Core jobs; targeted hosted CI: **one** PHP 8.3/V22.0.5 job. The optional **21-job** PHP/Core matrix remains required for the complete v0.1.0 release security/regression baseline. Green CI is synthetic evidence, not real DB/HTTP admission.
- Useful offline checks: `php test/unit/run.php`, `php test/unit/coreguarded.php`, `php test/unit/cf03-lab.php`, `php test/unit/cf03-evidence.php` and `node test/unit/loading.js`; pinned Core-dependent tests require `DOLIBARR_TEST_ROOT` and the approved source checkout. See [test strategy](docs/test-strategy.md) and [coverage](docs/test-coverage.md).
- Real accepted history: CF02 **POS01-C PASS**; CF03 **POS02-A1/A2 TECHNICAL PASS** for two CoreGuarded contribution-only cases. **F32-W01-01 CLOSED** at its earlier exact reviewed SHA. **RB01-A INVALID/STOP as rollback evidence**: a normal contribution persisted. No fixture replay, result reclassification or physical COMMIT-identity claim.
- **Already merged:** CF05-01/02 common active-guard contract, same-instance Booking→Invoice injection, readback guard checks and synthetic regressions; original review findings F34-01/P2 and F34-02/P3 **CLOSED**, source-exact standard CI **9/9 PASS**. This is not an invoice admission or real integration result.
- **Required before v0.1.0:** **CF05-03..07** server-side feature admission, qualified financial error/rollback origins, post-commit PDF/mail, real Core/trigger/engine qualification and independent approval; **CF03-NORMAL** authenticated real full workflows A/B/C/D; retained synthetic security/transaction negatives and the **21-job** CI; independent evidence/risk review; new exact-source **v0.1.0 ZIP**, real hosted D ZIP installation, R0-04, R0-07 and separate Owner release GO.
- **CF03-FAULT:** real destructive injections are deferred to a later iteration and stay **OPEN / NOT EXECUTED**. Runtime fail-closed contracts and safe synthetic regressions are not deferred.

No release, tag, full production qualification or public ZIP is authorized by the development merges. The historical PR #19 ZIP/hashes are **not** the new release artifact; final naming/documentation should use **v0.1.0 without an RC suffix**.

## Documentation map

Start with [current development baseline and roadmap](docs/current-development-baseline.md), [CF05 shared-guard design](docs/cf05-shared-guard-design.md) and the [documentation guide](docs/documentation-guide.md). Binding decisions: [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22), [ADR-0001](docs/adr/ADR-0001-core-first-transaction-profiles.md), [risk register](docs/adr/ADR-0001-risk-register.md). Release tracker: [Issue #20](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/20).

Older [Phase 4 architecture](docs/phase4-invoice-architecture.md), [implementation validation](docs/phase4-implementation-validation.md), [71-row requirements matrix](docs/requirements.csv), historical CF02/CF03 evidence and [CHANGELOG](CHANGELOG.md) retain their dated source and test provenance. Their former contribution-only/deferred CF05 statements are **historical**, not current release strategy.

Origin: module-owned composition based on [Dolibarr PR #42620](https://github.com/Dolibarr/dolibarr/pull/42620), without modifying Dolibarr Core files. License: GPL-3.0-or-later; see [COPYING](COPYING).
