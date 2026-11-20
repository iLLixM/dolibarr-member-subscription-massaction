# CF03-W01 – Witness hardening and real-test admission

**Implementation status:** Test-only hardening in [Draft PR #32](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/32). Earlier Owner A–D read-only preflights PASS; **no new real DB booking, invoice, rollback or HTTP test executed for W01**. Issue #22 CF-03 gates remain OPEN.

## Delivered components

- `tools/cf03-evidence.lib.php`: isolated, immutable evidence directory and file handling; no SQL/HTTP.
- `tools/cf03-lab.lib.php`: explicit and strictly validated invoice expectations, selected V22/V24 SQL schema policy, bounded Agenda event/actor reconciliation, D evidence-root argument validation.
- `tools/cf03-lab.php`: read-only information_schema column verification, source and Core hash binding, selected invoice header, line, linked pre-existing customer and attributed Agenda readback; one-shot baseline and verify.
- `test/unit/cf03-evidence.php` and `test/unit/cf03-lab.php`: pure file-state and expected/negative business contracts.
- Standard nine-profile and optional 21-profile CI invoke both unit scripts. Separate targeted PHP 8.3/Dolibarr V22.0.5 CI checks the hosting runtime without changing the established standard matrix.

No production booking/transaction/rights/invoice/PDF/mail code or Dolibarr Core file was edited.

## D – separate, strictly private hosting evidence root

A/B/C retain the fixed `/var/lib/dolibarr-test-results` parent. D must supply **both** `--evidence-root=/PRIVATE/CANONICAL/DIRECTORY` and `--evidence-dir=/PRIVATE/CANONICAL/DIRECTORY/cf03-d-unique-case-01` with `--baseline` and `--verify`. These options are forbidden on `--preflight`/`--check-only`. There is no environment-variable fallback or automatic account/profile switching.

The operator creates the root **in advance**, outside every served webroot and outside the CF03 checkout, owned by the PHP CLI effective user, canonical without symlinks, readable/writable and mode 0700. The harness verifies ownership, path, mode and file isolation; it does not create the parent, fix permissions or use sudo. For D the owner check requires PHP POSIX effective-user support; without it the run STOPS. The root cannot be the Dolibarr site parent or an ancestor of the checkout. Use only a fresh empty case directory with validated `cf03-d-...` basename; never reuse a case.

`--baseline` exclusively creates the private mode-0700 case directory, mode-0600 `baseline.json` and `baseline.sha256`. Before `--verify`, **exactly those two regular, private, same-owner files** must exist with valid SHA-256 and valid JSON. Verification writes the exclusive `verify.started` marker **before** after-state readback, followed by `after.json` and `verdict.json`. STOP or partial evidence is preserved permanently. No retry, rebooking, overwrite, cleanup, or implicit directory recovery.

Example **parameter shape only**; not a booking authorization:

~~~sh
PRIVATE_ROOT=/ACTUAL/OWNER_PRIVATE/PATH
EVIDENCE_DIR="$PRIVATE_ROOT/cf03-d-newcase001"

# Only after separate case approval and check-only PASS:
# /usr/bin/php tools/cf03-lab.php --baseline --environment=D \
#   --document-root=/dorfverein-borlas.de/erptest/htdocs \
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
