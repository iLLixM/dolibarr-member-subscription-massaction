# Member Subscription Massaction for Dolibarr

Process selected members' subscription contributions in one confirmed massaction, using the normal Dolibarr single-member business process with safeguards for bulk execution. Optional invoices, PDF documents and member emails are implemented across all three transaction profiles.

**Development status (24 November 2026):** Phase 4 implementation, #44 documentation consolidation and [#45 shared-hosting session fix](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/45) are merged. Phase 5 qualification is partial; **v0.1.0 is not released or production-qualified**. Development descriptor: `0.1.0-dev`. See [current development baseline](docs/current-development-baseline.md) and [release qualification](docs/release-qualification-v0.1.0.md).

## Intended v0.1.0 functionality

- Select eligible members in the Members list; preview subscription periods, amounts, overlap and effective options. Default batch limit 100, administrator-configurable. Confirm the actual server-held plans.
- Book contributions with Core-controlled membership end (including R16 retention of a later end) and standard configured Agenda effects.
- Optionally create and validate an invoice for a linked third party; create a third party from member data only when invoice creation and the corresponding option are selected and authorized. Verify invoice line, relation and persisted business values.
- After suitable acknowledged nonterminal completion, generate the optional invoice PDF and send the optional configured member email, verifying the intended PDF attachment where required.
- Report each member's booking, invoice, PDF, mail and attachment outcome separately. Stop on uncertainty, without automatic repeat or resend.

Not in scope: standalone third-party creation without invoice, automatic payment/bank booking, an additional module persistence journal, atomic processing of the entire batch, or common SQL/PDF/SMTP atomicity.

### Transaction profiles

The same business functionality is implemented and enabled for all profiles, subject to each profile's own runtime admission and observation contract.

| Profile | Database / observation | Qualification boundary |
| --- | --- | --- |
| `CoreGuarded` | Oracle MySQL, original DoliDB connection/depth, SET RR → Core BEGIN, bounded ACK; no P_S event proof | B bounded finance/mail PASS; D focused contribution/session/replay PASS; D finance/PDF/mail OPEN |
| `EnhancedMySQL` | Oracle MySQL, qualified Performance Schema events and privileges | C bounded finance/PDF and non-mail PASS; C mail and remaining cases OPEN |
| `MariaDBSnapshot` | MariaDB, bounded snapshot, `@@in_transaction` and connection/depth | A bounded finance/PDF and non-mail PASS; A mail and remaining cases OPEN |

Equal business capability does not mean equal transaction observability. Do not silently downgrade an explicitly selected EnhancedMySQL profile.

## Architecture and safety

**Binding strategy:** [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22) and [Owner Variant B](docs/owner-core-parity-2026-11-22.md): Dolibarr Core parity plus Massaction-specific safeguards. Use ordinary Core APIs, configured numbering/document models, Agenda, Logevents, triggers and extensions. No Core modifications, additional source/trigger hash allowlist, blanket PROV veto or blanket CoreGuarded finance denial.

Authenticated selection → fresh server-side preview → full confirmation and fingerprint/profile revision → original consumed one-shot file-session capability for CoreGuarded → one owned outer DoliDB transaction per member, same active guard for Booking and Invoice → contribution/optional third party and validated invoice via Core APIs and readbacks → suitable completion ACK → post-COMMIT PDF and optional verified recipient/attachment mail checkpoints → persisted individual results.

`Adherent::subscription()`, `Societe::create_from_member()` and `Facture::create()/addline()/validate()` remain the business APIs. Rights/entity, confirmation freshness, transaction ownership, relevant engines/readbacks and replay fences apply before and throughout processing. Uncertain or terminal states prevent unsafe continuation. Do not infer a rollback from later SQL absence; do not retry uncertain operations or roll back a foreign transaction. Transport handoff is not guaranteed delivery. F36-02 is Owner-accepted Core filesystem residual risk (possible pre-outer-COMMIT PROV rename), not a guarantee of SQL/filesystem rollback.

**Shared hosting:** PR #45 removes only the unnecessary directory-listing/readability prerequisite in `MsmFileBookingCheckpoint::requireSession()`. The PHP `files` handler, nonempty session ID, plain absolute existing writable directory, canonical binding and actual reopen/read/write/close/readback remain required. A known session file may be accessible even when the directory cannot be listed (e.g. owner mode `0300`). Preserve the original consumed confirmation, one-shot/processing/result persistence, owned guard and finance/post-COMMIT checks. Do not widen provider permissions to make a test pass. The six concrete Core/model/PDF file readability checks remain a separate contract. See [design](docs/phase4-invoice-architecture.md), [transaction ADR](docs/adr/ADR-0001-core-first-transaction-profiles.md) and [risk register](docs/adr/ADR-0001-risk-register.md).

## Supported platforms and qualification

| Item | Intended release scope / current evidence |
| --- | --- |
| Dolibarr | Owner-confirmed V22/V23/V24 target; V23 synthetic coverage only, no real V23 integration campaign claimed |
| PHP | Syntax minimum 7.4, subject to Core requirements; CI PHP 7.4/8.2 plus targeted PHP 8.3/V22 lane; focused D real Web evidence PHP 8.4.28, not general PHP 8.4 CI |
| Database | Oracle MySQL >=8.4 (Enhanced requires qualified P_S instrumentation/rights), MariaDB >=10.11; direct mysqli and relevant InnoDB tables; PostgreSQL not supported |
| Module ID | `584620`, unreserved community ID retained by Owner under [Issue #20](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/20); not a permanent upstream assignment |

The descriptor's Core >=21 admission floor is not a claim of real compatibility with every version.

| dolienv | Core / runtime / profile |
| --- | --- |
| A | V24.0.3, PHP 8.2.35, MariaDB 10.11.19, MariaDBSnapshot |
| B | V24.0.3, PHP 8.2.35, Oracle MySQL 8.4.13, CoreGuarded |
| C | V24.0.3, PHP 8.2.35, Oracle MySQL 8.4.13, EnhancedMySQL |
| D | V22.0.5, Web PHP 8.4.28 CGI/FastCGI, CLI 8.3.37, Oracle MySQL 8.4.13, CoreGuarded |

A/B/C are separate installations and schemas; B/C share server and local module source, not a schema. D has a separate checkout/database. [Current environments](docs/dolienv-environments.md) govern mapping; do not relabel historical tools.

## Development setup (approved isolated environments only)

No published v0.1.0 installation package is available from this baseline. For an explicitly approved isolated source installation, place or link the module root as `htdocs/custom/membersubscriptionmassaction`. Enable Members and this module, choose a compatible transaction profile, configure applicable user rights and batch limit. For invoice/PDF/mail use the appropriate Core customer, invoice, numbering, document and mail settings and permissions; use an isolated mail sink in tests.

Open the Members list, select eligible members, choose the bulk subscription action, inspect Preview, then confirm only intended options. Review persisted per-member Results; reloading a result is not a new booking. If UNKNOWN/STOP occurs, preserve it and reconcile independently under operator rules rather than resubmitting.

Enabling/disabling, profile/settings changes and bookings are writes. New cases require Owner authorization, fresh fixtures, protected target-user F32-MR-01 preflight, actual source/profile/role binding, backup integrity and independent readbacks. See [operator runbook](docs/cf03-operator-runbook.md).

## Tests, evidence and remaining gates

Local accepted real cases on `ba5c9a3121754fcbd4259bb8906ad1dd17f1ce00`: protected readiness 21 checks, initial finance/PDF PASS within scope, additional non-mail 8/8 PASS, local B mail/verified attachments 3/3 PASS (capture only). Hosted D independent checkout and dedicated schema, Dolibarr 22.0.5, Oracle MySQL 8.4.13, Web PHP 8.4.28 CGI/FastCGI / CLI 8.3.37: four roles, two separate contribution-only cases, persisted result reload, consumed replay refusal and separate SQL readback PASS within scope on `0adc259d4739b44f9b1fb3a49574d7e1c318132c`. D finance/PDF/mail, broader live cases and exact ZIP OPEN; A/C mail OPEN. Modified OD-P5-02 accepts authorized summaries within scope, not independent external reproduction.

- [PR #45](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/45) independent review on `7e5f64d07ab3830ce355cd745782c4f0c2a955b0`: APPROVED FOR DEVELOPMENT MERGE WITH DOCUMENTED LIMITATIONS; Standard 9/9, Full 21/21, Hosted 1/1 SUCCESS. Squash `b026f1504db27308fc7326e2e88ec2e53b2f48d9` is tree-identical. CI is synthetic; see [test strategy](docs/test-strategy.md), [coverage](docs/test-coverage.md). Standalone contracts: `php test/unit/run.php` and targeted runners.
- F45-01/02 test precision remains open; F45-03 documentation remediation in this follow-up awaits review/merge. Draft #40 lacks #45 production correction and requires final source refresh and new package identity.
- Release NO-GO: full CF03-NORMAL, CF05-07/CF03-RISK, R0-03 final ZIP/manifest/hash, R0-04 exact-package integration, R0-07 independent acceptance and separate Owner Release GO. CF03-FAULT destructive tests deferred. Historical B02 mail/attachment and DOLIENV-ACC-01 remain UNKNOWN/STOP; latter rollback unconfirmed. No replay/compensation.

## Documentation map

| Need | Read |
| --- | --- |
| Current development and roadmap | [Development baseline](docs/current-development-baseline.md), [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22) |
| Product/architecture decisions | [Owner Variant B](docs/owner-core-parity-2026-11-22.md), [Phase 4 design](docs/phase4-invoice-architecture.md), [transaction ADR](docs/adr/ADR-0001-core-first-transaction-profiles.md), [shared guard](docs/cf05-shared-guard-design.md) |
| Environments and evidence | [dolienv](docs/dolienv-environments.md), [Phase 5 register](docs/evidence/cf03/phase5-local-qualification-2026-11-24.md), [CF03 plan](docs/cf03-qualification-plan.md) |
| Tests and release | [Test strategy](docs/test-strategy.md), [coverage](docs/test-coverage.md), [release gates](docs/release-qualification-v0.1.0.md), [Issue #20](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/20) |
| Contributing and history | [Contributing](CONTRIBUTING.md), [AGENTS](AGENTS.md), [documentation guide](docs/documentation-guide.md), [71-ID requirements matrix](docs/requirements.csv), [CHANGELOG](CHANGELOG.md) |

Earlier validation and requirement rows retain their original scope and sources, not current deployment authorization. Origin: module-owned composition based on [Dolibarr PR #42620](https://github.com/Dolibarr/dolibarr/pull/42620). License GPL-3.0-or-later; see [COPYING](COPYING).
