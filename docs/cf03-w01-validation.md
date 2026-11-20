# CF03-W01 – Witness hardening and real-test admission

**Implementation status:** Test-only hardening in [Draft PR #32](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/32). Earlier Owner A–D read-only preflights PASS; **no new real DB booking, invoice, rollback or HTTP test executed for W01**. Issue #22 CF-03 gates remain OPEN.

## F32-W01 Invoice-Agenda Final Delta – F32-W01-08 and F32-W01-11 (2026-11-21)

Following the independent review of fixed HEAD `7fc5898d57d7c6bba51cf7cb2c572c5dc3131217`, **9 of the 10 original findings were independently CLOSED**, F32-W01-08 P2 remained PARTIAL and new F32-W01-11 P2 OPEN. The original review authorized **conditional controlled real-test admission on that exact HEAD only** for A/B contribution and selected no-change, C/D subject to extra Owner conditions, while B/C invoice-existing remained NO-GO.

The subsequent owner-authorized **test-only** delta changes the following contracts:

- **F32-W01-08:** The production `cf03State()` Invoice-Agenda SELECT now projects `id,code,fk_element,entity,fk_user_author,fk_user_action`; missing/null required columns lead to `CF03InvoiceAgendaProjectionIncomplete` instead of silently converting absent values to 0. The existing `eventActor()` checks still require correct entity, author, owner, assigned user resource and the actual CLI-confirmed authenticated operator.
- **F32-W01-11:** `--agenda` controls ONLY `AC_MEMBER_SUBSCRIPTION_CREATE`; effective `BILL_CREATE` and `BILL_VALIDATE` flags independently determine expected invoice events. Every positive `invoice-existing` case on B/C requires `--actor-id=<ACTUAL_UI_USER_ROWID>` **even when `--agenda=off`**. The same actor and invoice expectations are bound at check-only, baseline and verify; `LINEBILL_INSERT` remains an early baseline STOP if enabled, not a workaround by disabling authentic Core settings. Counts remain exact and distinguish member/invoice deltas.
- **Actual readback regression:** `test/unit/cf03-reader.php` **requires the actual `tools/cf03-lab.php`** and invokes its real `cf03State()` SQL mapping on a strict SELECT-only in-memory DB/Resultset double; the fake projects only explicitly SELECTed actioncomm columns, so the historical omitted-column bug fails. It exercises all eight Member ON/OFF × BillCreate ON/OFF × BillValidate ON/OFF combinations (with line event disabled), missing and unexpected events, bad actor/owner/entity/resource and missing resultset columns with exact STOP reasons. No real DB, HTTP, booking or evidence writes occur. The normal CLI entrypoint remains executable only when invoked as the main script.
- Standard nine-profile, optional full and targeted PHP 8.3/V22.0.5 CI run this reader test in addition to existing W01 contracts.

**Status restriction:** A successful CI on the corrected final HEAD is implementation evidence, not an independent finding closure. The earlier partial real-test GO belongs strictly to `7fc5898d57d7c6bba51cf7cb2c572c5dc3131217`; it does **not** transfer automatically to the new source. An independent, tightly scoped F32-W01-08/-11 delta review and a fresh Owner-specific admission are required before any B/C invoice-existing case. PR #32 remains OPEN/DRAFT, Issue #22 six CF-03 gates remain OPEN, and no merge, release, production or real-DB DML action is authorized.

## F32-W01 Paket B — source provenance and invoice/Agenda contracts (2026-11-20)

Following the Owner's explicit choice of a **bounded, scenario-specific runtime source manifest** instead of a full Dolibarr tree attestation, Paket B corrects **F32-W01-02/06/07/08/09** in Draft PR #32. These are implementation claims pending independent follow-up, **not** finding closures or permission for real bookings.

- `tools/cf03-source.lib.php` inventories all PHP files in the module's production class/lib/admin/module-descriptor directories plus `subscription.php` and the CF03 harness libraries, hashes them deterministically and compares the same installed PHP inventory with the source checkout (including D). Selected version-aware Core runtime dependencies include Dolibarr version identity, DB adapter, CommonObject, User, Adherent, Subscription, ActionComm, automatic Agenda trigger, and in the invoice/preflight union Facture, PaymentTerm, Product and Societe. The manifest explicitly does **not** claim a complete ZIP payload, universal transitive third-party PHP coverage or immutable physical transaction identity.
- B/C `invoice-existing` now binds the invoice product to the actual read-only setting `ADHERENT_PRODUCT_ID_FOR_SUBSCRIPTIONS` and, when nonzero, the selected `llx_product` record and its `fk_product_type`, entity and sellable status; product type 0 (goods) and 1 (service) are both supported, and no product means default type 1. B/C customer classification is limited to `client=1` or `3`. CLI `--invoice-quantity` is limited to 1 (or 1.0), reflecting the module's actual one-unit invoice plan.
- Agenda ON for a positive action now requires `--actor-id=POSITIVE_USER_ID` from the **actual authenticated Dolibarr operator**. The witness reads the corresponding enabled entity-1 `llx_user`, and verifies both event `fk_user_author` and `fk_user_action` and the single user-resource FK match that bound actor. Core V22.0.5/V24.0.3 set the automatic event author/owner to the trigger user and ActionComm assigns the owner by default. This is a bounded Core event contract, **not** a rule that unrelated third-party user assignments universally equal author/owner. For `no-change` or Agenda OFF, no `--actor-id` option is admitted.
- The SQL schema read-only policy now includes `llx_user`, `llx_product` and event author/owner columns. New offline tests cover source mutations in previously omitted module/Core files, goods products, customer classes 0/1/2/3, quantity 0/0.5/2, missing/incorrect actor IDs and wrong event author/owner/resource assignment. Negative tests retain exact required STOP reasons per Paket A.

**Operator constraints:** A/C still share one DB and use strictly sequential manually selected accounts. D's `source_checkout` still does not qualify a ZIP release. Preflight/baseline/verify must be rerun on the final reviewed source HEAD. Actor ID must be copied from the actual UI session/user evidence and cannot be inferred from a synthetic fixture. `LINEBILL_INSERT` event attribution remains the previously accepted fail-closed STOP. Do not disable genuine Core Agenda features to force a PASS.

**Status:** W01 remains **NO REAL-TEST RELEASE** pending green CI and independent delta review of all findings (Paket A and B) at the exact final SHA. No production booking/transaction/rights/invoice/PDF/mail code changed, no real A-D DB/HTTP test, no merge/release/Issue #22 completion gate.

## F32-W01 independent review follow-up — Paket A (2026-11-20)

The independent W01 review on PR HEAD `460b75d28bedfc9f4397c0cbf314d56964e638e1` ruled **CHANGES REQUIRED / NO REAL-TEST RELEASE**, with four P1 and six P2 findings. This branch has since received a **scoped Paket A correction**, which does **not** constitute independent finding closure or real-test admission.

Paket A covers F32-W01-01 (Dolibarr V22/V24 paid member type `statut=0` active), F32-W01-05 (`adherent_type.entity=1` and `subscription='1'`), F32-W01-03 (0700/effective-CLI-owner evidence-root requirement for **all A/B/C/D**), F32-W01-04 (valid case baseline and expected state admitted, then exclusive `verify.started` **before any fresh runtime/DB/config/source access**; after this seal, early STOP attempts write a non-overwriting `verdict.json`), and the focused F32-W01-10 negative-assertion helper (requires exact `CF03...` STOP reason, not merely the prefix).

A/B/C retain the previously fixed `/var/lib/dolibarr-test-results` path, but the Owner must **manually** ensure it is a canonical directory owned by the actual PHP-CLI effective user with mode 0700 before any baseline/verify; the tool does not chmod/chown a shared parent. An installation that previously worked with a group/world-accessible parent must now STOP rather than silently accept a weak evidence root. No direct local DB, HTTP action or filesystem configuration change was made by the developer.

**One-shot boundary:** The case is sealed only after an otherwise valid local baseline/fixture is admitted and before fresh external observation starts. An invalid baseline remains STOP; a sealed case is never retried. Any early post-seal DB/config/source STOP is recorded via exclusive `verdict.json` when possible; a filesystem write failure still preserves `verify.started` as durable no-retry evidence. Do not delete/reinitialize a case directory or repeat a booked action.

**Still OPEN:** P1 F32-W01-02 (source/runtime manifest) and P2 F32-W01-06/07/08/09 plus the wider test-coverage refinement of -10. Paket B and independent follow-up review remain required. **All seven real-test families remain NO-GO**, PR #32 stays DRAFT, and Issue #22 CF-03 gates remain OPEN.

## Delivered components

- `tools/cf03-evidence.lib.php`: isolated, immutable evidence directory and file handling; no SQL/HTTP.
- `tools/cf03-lab.lib.php`: explicit and strictly validated invoice expectations, selected V22/V24 SQL schema policy, bounded Agenda event/actor reconciliation, D evidence-root argument validation.
- `tools/cf03-lab.php`: read-only information_schema column verification, source and Core hash binding, selected invoice header, line, linked pre-existing customer and attributed Agenda readback; one-shot baseline and verify.
- `test/unit/cf03-evidence.php` and `test/unit/cf03-lab.php`: pure file-state and expected/negative business contracts.
- Standard nine-profile and optional 21-profile CI invoke both unit scripts. Separate targeted PHP 8.3/Dolibarr V22.0.5 CI checks the hosting runtime without changing the established standard matrix.

No production booking/transaction/rights/invoice/PDF/mail code or Dolibarr Core file was edited.

## D – separate, strictly private hosting evidence root

A/B/C retain the fixed `/var/lib/dolibarr-test-results` parent. D must supply **both** `--evidence-root=/PRIVATE/CANONICAL/DIRECTORY` and `--evidence-dir=/PRIVATE/CANONICAL/DIRECTORY/cf03-d-unique-case-01` with `--baseline` and `--verify`. These options are forbidden on `--preflight`/`--check-only`. There is no environment-variable fallback or automatic account/profile switching.

The operator creates the root **in advance**, outside every served webroot and outside the CF03 checkout, owned by the PHP CLI effective user, canonical without symlinks, readable/writable and mode 0700. The harness verifies ownership, path, mode and file isolation; it does not create the parent, fix permissions or use sudo. For D the owner check requires PHP POSIX effective-user support; without it the run STOPS. The root cannot be the Dolibarr site parent itself, any webroot, or an ancestor of a webroot/checkout. A separate private subdirectory **beside** htdocs under the same hosting site directory is permitted only if the operator verifies that no HTTP route, alias, download handler or public backup exposes it. Use only a fresh empty case directory with validated `cf03-d-...` basename; never reuse a case.

`--baseline` exclusively creates the private mode-0700 case directory, mode-0600 `baseline.json` and `baseline.sha256`. Before `--verify`, **exactly those two regular, private, same-owner files** must exist with valid SHA-256 and valid JSON. Verification writes the exclusive `verify.started` marker **before** after-state readback, followed by `after.json` and `verdict.json`. STOP or partial evidence is preserved permanently. No retry, rebooking, overwrite, cleanup, or implicit directory recovery.

Example **parameter shape only**; not a booking authorization:

~~~sh
PRIVATE_ROOT=/ACTUAL/OWNER_PRIVATE/PATH
EVIDENCE_DIR="$PRIVATE_ROOT/cf03-d-newcase001"

# Only after separate case approval and check-only PASS:
# /usr/bin/php tools/cf03-lab.php --baseline --environment=D \
#   --document-root=/ACTUAL/HOSTING/SHELL/HTDOCS \
#   --db-host=... --db-port=3306 --db-name=... --db-user=... \
#   --scenario=contribution --member-id=... --member-ref=... \
#   --marker=CF03LAB-D-... --start=2080-01-01 --end=2080-12-31 \
#   --amount=120.00 --agenda=on \
#   --evidence-root="$PRIVATE_ROOT" --evidence-dir="$EVIDENCE_DIR"
~~~

## SQL readback and invoice policy

The independent SELECT-only witness checks selected columns for adherent, type, subscription, actioncomm/resources, constants, customer, invoice, line and relation tables against pinned V22/V24 schema expectations. Column fingerprints are sorted before comparing baseline and after state. Unknown/missing columns lead to STOP rather than silently skipping validation.

The `invoice-existing` scenario remains **B/C only**, one fresh synthetic member with one *already-linked existing customer*, one subscription, one validated invoice and one invoice line. It additionally requires **all eight** explicit UI-confirmed inputs:

| Required option | Check |
| --- | --- |
| `--invoice-date=YYYY-MM-DD` | Invoice date |
| `--invoice-quantity=1` | Exact positive quantity |
| `--invoice-product=0` | Product ID (0 if no product) |
| `--invoice-vat=0` | Actual expected VAT rate |
| `--invoice-term=1` | Payment terms |
| `--invoice-payment-mode=0` | Payment mode |
| `--invoice-bank=0` | Bank account |
| `--invoice-currency=EUR` | ISO currency code |

The witness independently checks invoice ID, link to new subscription, customer/entity, validated non-provisional reference, gross amount, invoice date/term/payment/bank/currency, line ID/description marker/product/product type/quantity/VAT/start/gross total, and strict selected global table-count deltas.

Invoice Agenda `AC_BILL_CREATE` and `AC_BILL_VALIDATE` events are required **exactly when enabled** by the module/Core configuration and must link to the newly created invoice and one matching user resource each. The member Agenda contribution event is still checked separately. No unaccounted global Agenda changes are accepted. If `MAIN_AGENDA_ACTIONAUTO_LINEBILL_INSERT` is enabled, the limited witness **STOPS**, because line-level attribution needs a separate specialized witness. Do not change actual Agenda settings just to force a PASS.

**Limits:** New-customer auto-creation, multi-line invoice, tax allocation beyond specified gross/VAT, optional unrelated triggers, filesystem/PDF, SMTP, HTTP acknowledgement, real transaction COMMIT/ROLLBACK-event identity and arbitrary concurrency/fault evidence are **not** certified by W01. The no-change scenario is a selected state readback, not proof of a physical rollback.

## Acceptance and remaining stages

W01 acceptance requires the nine-profile standard CI, targeted PHP 8.3/V22.0.5 CI, pure negative/file contracts and independent tool security review. Before first actual booking: Owner must explicitly authorize a case-specific operator procedure, fresh fixture, manual UI action, check-only/baseline/verify one-shot sequence, private evidence preservation and independent reconciliation.

All six CF-03 Issue #22 qualification checkboxes remain open. The central PR stays draft until remaining real MySQL/MariaDB/Enhanced/hosted integration and external final review are complete.
