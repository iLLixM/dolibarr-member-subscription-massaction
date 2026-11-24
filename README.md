# Member Subscription Massaction for Dolibarr

**Development milestone — 24 November 2026:** [PR #38](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/38) was **squash-merged into main** as `0998fbaf58155cca04d31ab99c6bb47420e53c72`. Its independently reviewed source `b15b93580f995fb05a696a125c2a5fd995dd4bb3` has the **same Git tree** (`a6e7256fb1572361c4dd4d850124ca7a6afff566`). Verdict: `APPROVED FOR DEVELOPMENT MERGE WITH DOCUMENTED LIMITATIONS`. Exact-source CI: **Standard 9/9 · Full 21/21 · Hosted 1/1 SUCCESS**. **v0.1.0 is not released or production-qualified.**

**Binding Owner Variant B (Issue #22):** Dolibarr Core parity plus Massaction-specific safeguards. All three transaction profiles have contribution and optional Finance/linked-or-conditional-new Third Party, line/validated Invoice, post-COMMIT PDF and optional verified-attachment Mail implemented and enabled. Use normal Core numbering, configured models, Agenda/triggers and extensions; do not restore source-PHP SHA allowlists, the obsolete `PROV` veto or default CoreGuarded finance deny. Preserve server-owned fresh confirmation and fingerprint, rights/entity, original consumed one-shot session, same active owned Guard, engine/readbacks, conservative `UNKNOWN/STOP` and no replay/foreign rollback. **F36-02 remains an Owner-accepted Core filesystem risk**, not technically eliminated.

**Current dolienv (2026-11-24):** A=MariaDBSnapshot (Dolibarr 24.0.3/MariaDB 10.11.19/PHP 8.2.35); B=CoreGuarded (Dolibarr 24.0.3/MySQL 8.4.13/PHP 8.2.35); C=EnhancedMySQL (same Dolibarr/MySQL/PHP versions, separate schema/webroot from B); D=Hosted CoreGuarded (Dolibarr 22.0.5/MySQL 8.4.13/CLI PHP 8.3.37). A/B/C have separate schemas and installations; **B/C share a MySQL server and local module source, not a schema**. Historic CF03 letters and historical A/C shared-schema tools are not valid current routes.

**Current Phase 5:** Owner-accepted Codex outcomes on deployed `ba5c9a3121754fcbd4259bb8906ad1dd17f1ce00`: local A/B/C protected readiness 21 checks PASS; B01/B02 and A02/C02 bounded finance/PDF PASS; additional non-mail matrix 8/8 PASS; local B module mail/verified PDF attachments 3/3 PASS (two fresh bulk runs, local capture only). Historical B02 mail/attachment and DOLIENV-ACC-01 remain UNKNOWN/STOP, latter rollback unconfirmed. See [Phase 5 qualification register](docs/evidence/cf03/phase5-local-qualification-2026-11-24.md). These accepted summaries suffice under modified OD-P5-02; no external reproduction of raw artifacts is asserted.

**Operational/release boundary:** Local readiness accepted for the tested scope; new cases require protected preflight, current source/profile binding, fresh fixtures, Owner authorization, controlled backups and independent readbacks. Full CF03-NORMAL A–D, A/C mail, remaining live/configuration coverage, isolated Hosted D, CF05-07/CF03-RISK, final R0-03, R0-04, R0-07 and separate Owner Release GO remain OPEN / NO-GO. CF03-FAULT destructive tests remain deferred. Never replay historical UNKNOWN operations.

**Read first:** [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22), [current development baseline](docs/current-development-baseline.md), [Owner architecture](docs/owner-core-parity-2026-11-22.md), [dolienv topology](docs/dolienv-environments.md), [Phase 4 design](docs/phase4-invoice-architecture.md), [release gates](docs/release-qualification-v0.1.0.md). Dated older content below may describe earlier statuses; this current header and linked baseline take precedence.
## Intended v0.1.0 functionality

For each selected, authorized member, the confirmed massaction must support:

1. A fresh preview with business eligibility, rights, entity, type/fee/date, duplicate/overlap and confirmation checks.
2. The member contribution and Core-controlled R16 subscription end date, with standard member Agenda events where configured.
3. **Optional invoice** for an existing linked third party, or **optional creation of a third party only when an invoice is selected**, using Dolibarr Core APIs. Add invoice line(s), validate and link the invoice, and verify persisted business postconditions.
4. **Optional invoice PDF** after successful, suitable acknowledged DB completion; **optional contribution email**, including a separately verified PDF attachment when the invoice-mail path requires it.
5. Member-specific status for booking, invoice, PDF and delivery, including explicit `UNKNOWN/STOP` without automatic replay or recovery.

No standalone third-party creation, automatic payment/bank entry, new module persistence journal or claimed transactionally atomic SMTP/PDF behavior is part of v0.1.0.

### Transaction profiles

| Profile | Evidence | Development capability and limit |
| --- | --- | --- |
| `CoreGuarded` | MySQL bounded connection/depth/ACK, SET→Core-BEGIN; no P_S event proof | Full finance enabled; local B bounded finance/mail PASS, D pending |
| `EnhancedMySQL` | MySQL Performance Schema event, ownership and isolation | Full feature implementation, complete final-source integration pending |
| `MariaDBSnapshot` | MariaDB snapshot, `@@in_transaction`, connection/depth | Full feature implementation, complete final-source integration pending |

Equal business capability does **not** mean equal transaction observability. `CoreGuarded` does not silently downgrade from Enhanced, and synthetic tests are not production approvals.


## Architecture and safety

One outer DoliDB transaction per member is owned by Booking. The Invoice service receives the same active owned guard and uses Core `Societe::create_from_member()` and `Facture::create()/addline()/validate()` with independent readbacks. CoreGuarded retains the bounded SET RR → Core BEGIN path, original consumed file-session capability, full confirmed row/fingerprint and conservative outcomes. PDF and mail occur after suitable ACK/nonterminal COMMIT with checkpoints, actual PDF/attachment and recipient verification; delivery is not guaranteed. Uncertain or external effects trigger STOP and **never automatic booking/reinvoice/resend**.

F38-01–06 CLOSED; F38-07–09 SUPERSEDED; F38-10–14 INDEPENDENTLY CLOSED. The original B UNKNOWN/STOP remains unconfirmed even though the code defect F38-14 was fixed. F36-02 is Owner-accepted residual Core risk. [Design](docs/phase4-invoice-architecture.md) · [ADR](docs/adr/ADR-0001-core-first-transaction-profiles.md).


## Supported platforms and qualification

The documented release compatibility target is **Dolibarr V22, V23 and V24 (Owner-confirmed)**. Retain that target as a product statement, while distinguishing synthetic/source checks from *actual real integration evidence*: **V23 has synthetic coverage, not a completed real V23 integration campaign**. V22.0.5 and V24.0.3 have separately pinned historical evidence, but the local Phase 5 cases are accepted on V24; complete A–D qualification remains open. Capability admission at Dolibarr >=21 alone does not assert real support for every version.

- **PHP:** module syntax minimum 7.4, subject to Dolibarr/Core requirements; CI includes PHP 7.4/8.2 and a targeted PHP 8.3/V22 witness lane.
- **Database:** Oracle MySQL >=8.4 (CoreGuarded; Enhanced requires separate P_S qualification), or MariaDB >=10.11 (MariaDBSnapshot), direct `mysqli`/InnoDB under the qualified contracts. PostgreSQL is not supported.
- **Module ID:** `584620` is an unreserved development/community ID retained for the bounded v0.1.0 plan under the Owner's [Issue #20 decision](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/20); no permanent ID assignment is implied.

## Development setup (approved isolated environments only)

The repository root is the Dolibarr module root. For an Owner-approved isolated installation, link to `htdocs/custom/membersubscriptionmassaction`, enable Dolibarr Members and this module, select a compatible transaction profile and configure the appropriate rights. The Members bulk-subscription action offers Preview → Confirmation → Results. **Enabling, profile changes and confirmed bookings are writes** and require approval.

Historical `CF03_*` operator commands are not ready for new dolienv use: F32-MR-01 protected target-user launcher and read-only preflight must be verified per new case, with individual Owner GO and fresh one-shot fixtures. B/C share server/source: no uncontrolled parallel qualification. No private identities, paths, DB names or credentials in Git.


## Tests, evidence and remaining gates

- Source-verified PR #38 CI (9/21/1 = **31/31 SUCCESS**) is synthetic/offline evidence; not live financial or mail qualification. Use `php test/unit/run.php` and targeted runners under [test strategy](docs/test-strategy.md) / [coverage](docs/test-coverage.md).
- Next: remaining A/C mail and live/configuration cases, isolated Hosted D, exact package and CF05-07 operating-risk acceptance. No historical UNKNOWN replay.
- Release **NO-GO**: source-exact sanitized v0.1.0 ZIP/manifest/SHA256 (R0-03), Hosted D exact package (R0-04), independent R0-07 and distinct Owner Release GO. Destructive CF03-FAULT deferred. [Current gate plan](docs/release-qualification-v0.1.0.md).


## Documentation map

Start with [current development baseline and roadmap](docs/current-development-baseline.md), [CF05 shared-guard design](docs/cf05-shared-guard-design.md) and the [documentation guide](docs/documentation-guide.md). Binding decisions: [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22), [ADR-0001](docs/adr/ADR-0001-core-first-transaction-profiles.md), [risk register](docs/adr/ADR-0001-risk-register.md). Release tracker: [Issue #20](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/20).

Older [Phase 4 architecture](docs/phase4-invoice-architecture.md), [implementation validation](docs/phase4-implementation-validation.md), [71-row requirements matrix](docs/requirements.csv), historical CF02/CF03 evidence and [CHANGELOG](CHANGELOG.md) retain their dated source and test provenance. Their former contribution-only/deferred CF05 statements are **historical**, not current release strategy.

Origin: module-owned composition based on [Dolibarr PR #42620](https://github.com/Dolibarr/dolibarr/pull/42620), without modifying Dolibarr Core files. License: GPL-3.0-or-later; see [COPYING](COPYING).
