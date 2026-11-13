# Phase 4 implementation validation

## Current gate status — 2026-11-13

| Gate | Current status / provenance | Scope / next step |
| --- | --- | --- |
| G2 | **FINAL APPROVED – PASS WITH DOCUMENTED LIMITATIONS** | Completed for the bounded Dolibarr V22.0.5 profile below |
| G3 | **APPROVED – PASS WITH DOCUMENTED LIMITATIONS** | Completed for the controlled V22 PDF/mail profile below; SMTP status uncertainty retained |
| G4 | **PASS – OWNER ACCEPTED** | Owner-reported manual acceptance on 2026-11-12; subsequent targeted UI regressions passed |
| V24 | **NOT QUALIFIED** | V24.0.3 C2.1 T1/T2 synthetic regressions PASS; real runtime qualification NOT EXECUTED |
| Production | **NOT QUALIFIED** | No release or production approval |

The completed Phase 4 implementation baseline is merge commit
`dd68f2a4c2601f03b4588c0cca965292a2a1cb60`.
[PR #4](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/4)
is **MERGED** following the final independent external review **APPROVED FOR MERGE**
on feature HEAD `5630cf6034a32d3a2f6e940dde820a3523012e05`. The owner-reported
external approval is not a formal GitHub review approval.
**FS4-01, FS4-02, FS4-02-R1 and FS4-03: CLOSED.** The final evidence-mapping
corrections are retained in `5630cf6`; the requirement matrix is unchanged by this update.
GitHub CI on the merge commit passed for PHP 7.4 and 8.2
([workflow 43410265664](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/43410265664)).
The merge does not constitute a release or production approval. Temporary module ID
`584620` must be replaced or permanently reserved before public module distribution.

The historical UI-cleanup implementation baseline is
`89b40b39337b66f7263ea1b629ee83ac667dd7a4`; the historical G2/G3 runtime-corrected
baseline is `efb59b071a51373bf7e208de4043b1291beb7ad5`.
The sections below retain historical development/test evidence;
the current gate table and the G2/G3/G4 closure sections supersede earlier pending-gate
statements only within their expressly tested scopes.

## C2.1 compatibility corrections (2026-11-13)

Implementation and exact tested commit: `553fa75aba5d7b710a4b2363d087898c2213bfb4`,
based on module `b946c8142355030532068e07166731541ceea69e`, on
`feature/v24-compatibility`. This subsequent documentation update changes no tested code.
C1-01–C1-05 are implemented locally; independent follow-up review remains pending.
The earlier V22 G2/G3/G4 evidence and owner decisions O1–O7 remain scoped as recorded.

**Binding C2.1 owner decisions E1–E4:**

- **E1 / R16:** V22.0.5 retains the end of the contribution with the latest start.
  V24.0.3 requires the maximum contribution end, matching its actual
  `Adherent::update_end_date()` (Core change `f63662b4a5f`). The module keeps the
  observed update return and persisted-value checks; it never repairs `datefin`.
  `MsmCoreCompatibility` selects exact pins, not a blanket `>=24` branch.
  Unknown versions stop booking before database access. Historical V22-only
  statements requiring latest-start rather than MAX remain correct for V22.
- **E2 / VAT:** V24.0.3 positive numeric `ADHERENT_VAT_FOR_SUBSCRIPTIONS` values
  identify dictionary records, not rates. Invoice preflight rejects them with a
  DE/EN message; no `c_tva` ID resolver or silent zero-rate fallback is introduced.
  `0` and `defaultforfoundationcountry` retain their existing Core delegation;
  V22 behavior remains unchanged. Subscription-only operation remains possible.
  Changed tax settings invalidate the confirmed fingerprint before business APIs.
- **E3 / scope:** only Core V22.0.5 `88ad132763be45e9620128c1830e41493e1cdd05`
  and V24.0.3 `5c6c7ff6a5112413b1c27221c8d11750afad56f8` are policy targets.
  These policies and synthetic PASS results do not qualify V24 installations,
  other patches, Multicompany, additional tax profiles or production.
- **E4 / execution:** T1/T2 only. No application bootstrap, real DB connection,
  business operation, live PDF generation, SMTP or installation change was used.
  Real T3/T4 operations require separate owner authorization.

C1-03 uses `crabe` on V22 and `sponge` on V24 only when model configuration is
empty. Explicit type/general models retain priority; invalid paths or unavailable
files remain errors. C1-04 supplies both `accountid=0` and legacy `ccountid=0`,
without changing hook outcomes, attachments or post-commit transport guards.
C1-05 adds the optional trailing `ref_ext` to the booking double, preserving the
ten existing parameters. PHP 7.4-compatible syntax is retained.

### Executed T1/T2 evidence

Local PHP **8.2.35**. Set `DOLIBARR_TEST_ROOT` to the pinned Core checkout and run
`php test/unit/<runner>.php`. V22 used `/home/marcel/dolibarr-bulk-test`; V24 used
`/home/marcel/dolibarr-v24`. The final sequence per Core was `invoice`,
`invoiceprofile`, `maildelivery`, `invoicepdf`, `postcommit`.
`invoice` includes `booking`, which includes `preview`; these were not also run
standalone in the final sequence. Counts below are per component, not summed
again when included by another runner.

| Runner / assertions | V22.0.5 | V24.0.3 | Evidence boundary |
| --- | --- | --- | --- |
| `preview.php` / 388 | PASS | PASS | Pinned Core calendar APIs; synthetic input/history |
| `booking.php` / 208 additional | PASS | PASS | API signature, R16, transaction/replay, hook/renderer contracts |
| `invoiceprofile.php` / 40 additional | PASS | PASS | Tax preflight and configuration changes; real model-file readability checks |
| `invoice.php` / 60 additional | PASS | PASS | Synthetic Core business adapters, rollback, VAT changes before booking |
| `maildelivery.php` / 17 | PASS | PASS | Same fixture-only runner under both environments; not a real Core transport test |
| `invoicepdf.php` / 16 additional | PASS | PASS | Isolated temporary synthetic files; no Dolibarr Documents or Core PDF rendering |
| `postcommit.php` / 13 additional | PASS | PASS | Synthetic checkpoint/PDF/mail ordering |

Version-independent `php test/unit/run.php`: **98 PASS**, including language-key
parity, exact-version policy and rejection before DB access.
`node test/unit/loading.js`: **6 PASS**, synthetic DOM only.
PHP lint: **30 files PASS**. `git diff --check` and committed whitespace check: PASS.
At the historical C2.1 local-only stage, the PHP 7.4/8.2 CI workflow was unchanged;
PHP 7.4 execution and new GitHub CI were **NOT EXECUTED** at that time (no push
was then authorized). The subsequent C2.2/C2.3 and PR #6 evidence below supersedes
that missing-CI status, without changing the original local test results.

R16 tests execute each pinned Core's real end-update method using an in-memory DB
adapter. The identical older-longer overlap yields 2030-06-30 on V22 and 2030-12-31
on V24. Newer-longer, tied starts in either modeled result order, failed Core
update and corrupted stored end are covered. V22 Core does not define an SQL tie
order; these tests do not promise deterministic real-DB ordering for tied starts.
V24 uses the maximum end independently of that ordering. The VAT tests also expose
Core's V24-only VAT hook: synthetic non-VAT seller returns zero on V22 and the
explicit synthetic hook's rate on V24; the module preserves each Core result.
Initial fixture-only failures (missing country cache / VAT hook contract) were
resolved by matching the actual Core APIs; existing regression assertions remain.

Sanitized command output, exits, exact Core/implementation SHAs and PHP file hashes
are retained locally in `/home/marcel/dolibarr-c21-validation-20261113/results.json`.
No private runtime data or original V22 evidence was copied into this document.
The authoritative matrix retains all 71 IDs, 18 columns and its first ten
historical fields; only directly affected current traceability fields are extended.

**NOT EXECUTED / NOT QUALIFIED:** real V24 booking/invoice rollback, PDF/ECM,
SMTP/delivery, authenticated HTTP/browser, external-writer/concurrency,
connection/COMMIT loss, Multicompany and additional tax/product profiles.
Temporary module ID, release limits, SMTP diagnostic 121 and prior risk boundaries
remain. G3-W02 remains template configuration only, not a module finding.
Next: independent C2.1 review, then separately authorized bounded V24 T3/T4 tests.

## PR 6 follow-up: PR6-01 to PR6-03

Subsequent review corrections, 2026-11-13, implementation commit
`c67b5cd0bc2ef34a23012e870928dad3bbaa730b`:

- **PR6-01:** V24.0.3 invoice preflight now accepts only the exact supported
  settings: unset/empty, `0`, `defaultforfoundationcountry`. All other values
  reject with the existing DE/EN key and clarified setting-level message.
  Tests cover positive IDs, `7foo`, ` 7bad`, `+7bad`, decimals/exponents,
  negative values, alternate zero representations, whitespace and unknown text.
  The real profile/preview rejection is exercised; changed confirmation is
  rejected before any synthetic business API call. V22's previous behavior and
  subscription-only booking remain covered. This tightens E2, without adding a
  tax dictionary resolver or changing E1–E4/O1–O7.
- **PR6-02:** PR CI checks `git diff HEAD^1 HEAD --check`, comparing the test
  merge with its first parent. Push CI retains the initial-commit check and adds
  `-m` for merge parents: `git diff-tree --check --root -m -r HEAD`.
  An isolated temporary Git repository reproduced a two-parent test merge with
  trailing whitespace: the previous command exited 0; the new PR and push
  commands exited nonzero and detected the error. No project history was merged
  or rewritten. CI matrix and all runner commands remain intact.
- **PR6-03:** The previous local-only CI limitation is now explicitly historical.
  The initial expanded CI on `1c7903d71fbae08b631c85cb48c375bdb2a92536` passed
  [push run 43501212921](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/43501212921)
  and [PR run 43501309268](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/43501309268).
  The corrected implementation `c67b5cd` also passed all three jobs in
  [push run 43506166580](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/43506166580)
  and [PR run 43506174704](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/43506174704).

| PHP | Pinned Dolibarr Core | Initial expanded CI | Corrected implementation CI |
| --- | --- | --- | --- |
| 7.4 | V22.0.5 / `88ad132763be45e9620128c1830e41493e1cdd05` | PASS | PASS |
| 8.2 | V22.0.5 / `88ad132763be45e9620128c1830e41493e1cdd05` | PASS | PASS |
| 8.2 | V24.0.3 / `5c6c7ff6a5112413b1c27221c8d11750afad56f8` | PASS | PASS |

Local PHP 8.2 regression on the corrected implementation: 388 preview,
208 additional booking/mail, **59 profile**, **76 invoice**, 17 mail adapter,
16 PDF adapter and 13 post-commit assertions per Core. Included assertions are
not summed again. Standalone contracts/language parity: 98 PASS; synthetic
JavaScript: 6 PASS; PHP lint and full PR diff whitespace: PASS.
Commands remain those in the C2.1 section; `invoice` includes booking/preview.
Sanitized local command/negative-test evidence is retained outside the repository
in `/home/marcel/dolibarr-c21-validation-20261113/pr6-followup-local.json`.
The synthetic Git fixture was temporary; its construction and command results
are recorded in that evidence. No real database connection, business write,
Core PDF or SMTP was exercised.

This is implementation and CI evidence for targeted independent follow-up review,
not independent acceptance or merge approval. PR #6 remains Draft.
**Real V24 integration and production remain NOT QUALIFIED.** Existing V22
qualification limits, temporary module ID and all documented risks remain.

## Scope and provenance

Phase 4B–4E implements the optional invoice workflow designed in Phase 4A. The implementation is based on module commit `c58231101b7fb8be20d0051575ab87011da5bcf1` and the inspected Dolibarr V22.0.5 Core commit `88ad132763be45e9620128c1830e41493e1cdd05`. Dolibarr V24.0.0 commit `769c7db907099643558e77d7002c109cfda919e5` remains a source comparison only.

The original Phase 4 implementation commit was `07e662231907c4d3344883fe51e321939ec6823c`. The historical review-correction implementation commit tested in the original automated table below is `c74e7a6ae5b41acd18012fe9f1f512196e859e55`. The four original local milestone commits are:

| Phase | Commit | Result |
| --- | --- | --- |
| 4B | `b646b23` | Invoice preflight and confirmed-state contracts |
| 4C | `4b20d8a` | Transactional contribution and invoice orchestration |
| 4D | `9e1d3d0` | Post-commit invoice PDF and mail attachment handling |
| 4E | `07e6622` | Preview, results, loading indicator and complete flow integration |

During the original local implementation stage, no Core file, database, configured Dolibarr document directory, module activation, installation symlink, SMTP configuration or existing test fixture was changed. At the original local completion stage, no push, pull request, merge or tag had been created. The branch was subsequently published as draft PR #4; later review corrections are recorded below.

## 4B — preflight and confirmed state

`MsmInvoiceProfile` resolves a deterministic, read-only invoice plan. It checks the current entity, invoice and customer permissions, linked-customer access, explicit customer-creation consent, base currency, invoice date, payment term, number and PDF models, configured product/service and tax settings. Unsupported local-tax, packaging and stock profiles fail closed.

The initial invoice selection follows O1: only `ADHERENT_BANK_USE=invoiceonly` suggests invoice creation. It creates no bank or payment dependency. User changes survive subsequent preview revisions. A versioned server-side state contains the effective invoice, customer, amount, description, product, tax, date, currency and model snapshot. Old state receives no implicit invoice consent, and consumed or processing confirmations cannot be reopened. Booking repeats permission, entity, object and configuration checks against the confirmed state.

## 4C — transactional database workflow

`MsmBookingService` remains the owner of one outer transaction per member. `MsmInvoiceService` uses Dolibarr Core APIs for optional `Societe::create_from_member()`, `Facture::create()`, `Facture::addline()`, contribution-to-invoice linking and `Facture::validate()`. It does not create payments or bank entries.

The service locks and reloads the relevant member, customer and product records and verifies the expected InnoDB profile and transaction depth. It then verifies the stored contribution, customer state, validated invoice identity and reference, currency, dates, terms, single gross line, product, quantity, VAT, description, totals and exact contribution link. R16 is rechecked after invoice operations. An invoice or customer ID becomes a confirmed result only after a verified commit.

Known failures attempt the owned rollback and allow the batch policy to continue. A rollback, transaction-depth or commit ambiguity produces an unknown result and stops later business and delivery actions. Diagnostic stage and unconfirmed object IDs are retained in the session result solely for reconciliation. There is no automatic retry, compensating deletion, transaction-counter drain or durable operation journal.

Customer creation is implemented inside the same service because its lifetime and rollback decision are inseparable from the invoice operation; no separate third-party adapter was needed. This is an internal structure choice and does not change O3.

## 4D — post-commit PDF and mail

`MsmInvoicePdfService` starts only from a confirmed committed invoice result. It reloads and authorizes the invoice and its contribution link, invokes the configured Core document model and derives the expected file below the current entity's invoice document root. It rejects pre-existing or stale output, paths outside the canonical root, symlinks, unreadable or non-regular files, invalid PDF signature/MIME data and invoice metadata mismatch.

Only a bounded descriptor containing invoice identity, reference, file name, size, modification time and hash enters the session checkpoint. The local path is derived and verified again immediately before standard mail transport; it is never accepted from HTTP or persisted in the result. Synthetic PDF tests use only isolated temporary directories.

`MsmSubscriptionMail` preserves the contribution-only path. For invoice mail it rechecks invoice permissions and requires the verified invoice PDF. Missing or changed output blocks standard delivery. Hook takeover, hook failure, standard send, transport failure and unknown result remain distinct. A PDF or mail failure after commit does not relabel or roll back the successful business objects, and no result GET regenerates or resends anything.

## Review corrections F4-01–F4-07

- **F4-01:** confirmed plain-text descriptions are encoded exactly once at the `Facture::addline()` adapter boundary. Contribution notes retain the confirmed plain text, while stored invoice-line postconditions compare the encoded representation.
- **F4-02:** invoice availability and its O1 default no longer depend on the selected mail option or advanced send permission. The send permission is required only for the combined invoice-and-mail action.
- **F4-03:** invoice-PDF identity, descriptor and attachment availability are verified before `sendMail` hooks. A standard transport re-resolves the attachment after a zero hook response; negative and positive hook semantics remain distinct.
- **F4-04:** the canonical invoice directory is checked in addition to the PDF file. Symlinked invoice directories pointing inside or outside the trusted resolved entity root are rejected during generation and attachment resolution.
- **F4-05:** preview and confirmation render the server-confirmed product mapping, payment term, number model and PDF model. Explicit customer creation warns when the confirmed Dolibarr setting may also create a contact.
- **F4-06:** the bounded V22 transaction-engine preflight now includes `ecm_files`, which the V22 invoice reference update path can modify.
- **F4-07:** the requirement matrix records the O1 deviation and names the concrete regression assertions for the affected critical requirements.

### Later owner decision — 2026-11-13 (F4-05 display scope)

The original F4-05 evidence above records the historical display requirement.
The owner subsequently removed number/PDF model names from preview and confirmation.
Both models remain unchanged in server-side planning, validation, snapshots and
booking. Fee, date, VAT, product/service and payment terms remain visible. This is
a presentation-only revision; the historical technical evidence is retained.

## 4E — user flow and results

The preview exposes invoice intent, linked or planned customer, invoice amount, date, currency, product, VAT, payment term and the mandatory PDF attachment relationship when mail is selected. The customer-creation option is available only with invoice selection and the required rights.

The confirmation options use a compact, content-sized, borderless table with left-aligned cells and normal font weight. The three translated option values and row-header semantics are retained. Styling is scoped to this confirmation-only table; other tables and result rendering are unchanged.

The result page separates contribution, customer, invoice, PDF, mail, known rollback and unknown transaction outcomes. Customer and invoice links are rendered only after a fresh access check. Unknown outcomes show the diagnostic stage and explicitly unconfirmed IDs; they do not offer a retry action.

The module JavaScript acts only on the final confirmation form. It sets the action before disabling the submit control, prevents duplicate submits, announces progress, moves focus to the status element and resets the state on `pageshow`, including browser back-forward cache restoration. Server-side form processing remains functional without JavaScript.

## Historical automated validation

The following commands were executed on review-correction implementation commit `c74e7a6ae5b41acd18012fe9f1f512196e859e55`, using PHP 8.2.35, Node.js 18.20.4 and the pinned local V22.0.5 source tree. Counts from runners that include another runner are reported separately and are not added as unique assertions.

| Command | Result |
| --- | --- |
| `php test/unit/run.php` | PASS — 87 synthetic contract assertions |
| `node test/unit/loading.js` | PASS — 6 synthetic DOM assertions |
| `DOLIBARR_TEST_ROOT=/home/marcel/dolibarr php test/unit/booking.php` | PASS — 387 preview/calendar assertions and 87 booking/mail assertions |
| `DOLIBARR_TEST_ROOT=/home/marcel/dolibarr php test/unit/maildelivery.php` | PASS — 17 mail-adapter assertions |
| `DOLIBARR_TEST_ROOT=/home/marcel/dolibarr php test/unit/invoiceprofile.php` | PASS — 387 included preview/calendar assertions and 24 profile assertions |
| `DOLIBARR_TEST_ROOT=/home/marcel/dolibarr php test/unit/invoice.php` | PASS — 387 included preview/calendar, 87 included booking/mail and 34 invoice assertions |
| `DOLIBARR_TEST_ROOT=/home/marcel/dolibarr php test/unit/invoicepdf.php` | PASS — 387 included preview/calendar, 87 included booking/mail and 16 isolated PDF assertions |
| `DOLIBARR_TEST_ROOT=/home/marcel/dolibarr php test/unit/postcommit.php` | PASS — 387 included preview/calendar, 87 included booking/mail and 13 checkpoint assertions |
| PHP lint over every module PHP file | PASS |
| `git diff --check` | PASS |
| Requirements CSV integrity | PASS — 71 unique IDs, 18 columns, original first ten columns preserved |

The GitHub workflow includes the focused Phase 4 runners in its PHP 7.4 and PHP 8.2 matrix. The published correction HEAD d817bc44a5e68008a7163db8cb45ab972aa6e9f6 passed both PHP 7.4 and PHP 8.2 GitHub CI checks. The table above retains that historical local test baseline. PHP 7.4 was not installed locally, and no package was installed for this task.

## Remaining runtime qualification

G2 and G3 are completed and independently approved for the bounded V22.0.5 profiles
below. Real SMTP delivery and owner mailbox/attachment checks have been executed.
G4 manual browser/UX acceptance is complete as reported by the owner; see the
G4 closure below. Automated browser checks do not by themselves prove a real
BFCache restoration or manual visual acceptance.
The following remain **NOT EXECUTED / NOT QUALIFIED**:

- real connection loss, uncertain physical COMMIT acknowledgement and external writers;
- arbitrary installed third-party hooks, Multicompany and unsupported tax/stock profiles;
- V24 runtime/schema qualification and production acceptance.

Independent overall and targeted follow-up reviews have occurred. The owner reports
**G2 FINAL APPROVED – PASS WITH DOCUMENTED LIMITATIONS** and
**G3 APPROVED – PASS WITH DOCUMENTED LIMITATIONS**. This acceptance does
not extend beyond the tested V22 profile or constitute production approval.

## Manual reconciliation for an unknown outcome

An unknown outcome must never be retried from the module. Preserve the result page and its run identifier, diagnostic stage and any unconfirmed subscription, customer or invoice IDs. With separately authorized read-only access, inspect the member and contribution, `llx_subscription`, `llx_facture`, `llx_facturedet`, the Core element link, the member/customer link and the R16 member end date. Also inspect the invoice reference/status and any document metadata before classifying the operation.

The absence of one row, ID, relation or file is insufficient evidence that all effects failed. Core triggers, number allocation and filesystem operations may have separate effects. Classify the outcome only as verified success, proven rollback, definitely not started or unresolved. An unresolved result remains unresolved and requires manual business reconciliation. Session loss can remove the module's diagnostic information because O6 deliberately retains a session checkpoint rather than a persistent journal.

## Residual risk and next gates

The implementation contains guards for R04–R09, R16 and P4-01–P4-05. The bounded G2/G3 evidence and accepted limitations below qualify only the stated V22 profiles. Core nesting, enabled triggers, shared customer/product state, numbering models, nontransactional effects, document hooks, filesystem replacement races and external transports cannot be established by doubles. The engine list is deliberately bounded to tables reached by the inspected V22 paths; installed hooks or triggers may touch additional nontransactional tables. Directory and file checks reduce symlink exposure but cannot eliminate a privileged filesystem actor racing between verification and Core generation or transport. Unsupported profiles are rejected rather than silently approximated. G2/G3 qualify the bounded database/PDF/mail profiles below; broader profiles need separate evidence.

The G2/G3 independent reviews and the implementation PHP matrix are complete.
G4 is PASS – OWNER ACCEPTED for the tested V22.0.5 profile. The final independent
external review approved the merge recorded above. V24 still requires its own
compatible installation and evidence.
Production, release and general-use approval remain separate decisions.

Historical implementation-stage conclusion (superseded by the current gate overview):
**PHASE 4 IMPLEMENTATION COMPLETE – SYNTHETICALLY VALIDATED – READY FOR INDEPENDENT REVIEW**

## Targeted follow-up completion — 2026-11-11

The original implementation `07e6622` received an independent overall review. Corrections `c74e7a6` and documentation `d817bc4` passed GitHub CI on PHP 7.4/8.2. The subsequent independent follow-up closed F4-02–F4-06 and identified remaining entity-fidelity/test-evidence issues (F4-01, F4-07, N4-01, N4-02).

Implementation `559db935334be8407432c4d04b599419997cd8b6` now uses `htmlspecialchars(..., ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', true)` without prior decoding. This matches the inspected V22 `Facture::addline()` contract, which assigns the provided description to the stored line. Literal expected values cover `&amp;`, `&lt;`, numeric entities, actual tags, quotes, Unicode and unknown placeholders. Each case checks the stored value, one HTML decode back to the confirmed text, and the unchanged contribution note. Advanced permissions are explicitly enabled in the renderer regression and the prior synthetic configuration is restored.

Targeted local PHP 8.2 validation: invoice runner PASS (388 included preview, 87 included booking/mail, 55 invoice assertions); post-commit runner PASS (same included suites, 13 post-commit assertions); standalone contracts/language parity PASS (87); mail adapter PASS (17); synthetic JavaScript PASS (6). Lint of all three changed PHP files and whitespace checks PASS. These results supersede only the affected counts above. The requirement matrix retains 71 IDs, 18 columns and the first ten historical columns, with literal link/zero-invoice/post-commit assertion references corrected. Final published-HEAD CI is recorded in PR #4.

At that historical correction stage, real invoice/rollback, Core PDF, SMTP and browser integration had not been executed. The subsequent G2/G3 evidence below supersedes only the completed V22 portions.

## G2 closure — 2026-11-11

**G2 FINAL APPROVED – PASS WITH DOCUMENTED LIMITATIONS** applies only to
Dolibarr V22.0.5 Core `88ad132763be45e9620128c1830e41493e1cdd05`, PHP 8.2.35,
MariaDB 10.11/InnoDB, entity 1, EUR, quantity-one standard invoices, zero membership
VAT, no contribution product or local taxes, Mercure numbering and Sponge PDF.
The real run used module `431c25a` plus the local V22 renderer fix, subsequently
published in `efb59b071a51373bf7e208de4043b1291beb7ad5`. Its real V22 translation
regressions and full GitHub PHP 7.4/8.2 matrix passed (push workflow 43098579042).
No real runtime qualification of PHP 7.4 is implied by that synthetic CI matrix.

| Case | Actual result and provenance |
| --- | --- |
| G2-01 | PASS: real HTTP contribution and validated 120 EUR invoice with existing customer and verified own PDF |
| G2-02 | PASS: real HTTP zero contribution and validated zero invoice with verified own PDF |
| G2-03 | PASS: real CLI monthly 15 EUR contribution, explicitly created customer, validated invoice and verified PDF |
| G2-04 | PASS: controlled exception after real invoice validation; confirmed rollback; all 20 original table hashes before/after equal, including Agenda/resources/extrafields and events |
| G2-05 | PASS: one owner-executed real CLI batch retained the successful row and rolled back the failed row; not a browser/HTTP mixed-batch acceptance |
| G2-06 | PASS within scope: authenticated role boundaries and rejected repeated consumed confirmation POSTs; unchanged observed DB/document metadata and PDF contents |
| G2-07 | PASS within scope: stored contribution/invoice values, exact Core links, customer links, R16 latest-start rule, canonical PDF/ECM identity and unchanged payment/bank records reconciled |

Exactly three successful contributions/invoices were created, with one controlled
rollback attempt. No G2 SMTP mail was selected. Existing target-member fields were
checked; separate baseline differences found to predate the G2 run were explicitly
excluded from attribution to G2. No automatic repair, deletion, retry or restore ran.
Twelve real HTTP preview variants covered English/German, linked/unlinked members,
mixed selections and invoice on/off after the renderer correction.

The owner supplied an administrative database-trigger count of zero and explicitly
accepted the isolated installation's backup/restore risk. No additional grants or
restore test were required. Enabled Core Agenda automation was retained and included
in the database/rollback checks.

Limits retained from the accepted independent follow-up:

- The active CLI executable was not separately retained. Its previously recorded
  hash, owner output and original results are available; the disabled archive differs
  only by its post-run execution guard. Reversing that guard solely in memory matches
  the recorded hash. Derived bytes are not presented as an original execution artifact.
- The later read-only invoice-tree inspection found no rollback-attributable remnants
  and consistent ECM metadata; it does not establish absence of transient or external
  filesystem effects. Three original success-PDF hashes match; an older specimen was
  separately identified.
- Replay showed rejection plus unchanged DB/ECM and PDF contents. HTTP did not expose
  an independent file-mtime observation; no universal no-regeneration guarantee follows.

Sanitized original evidence remains in the protected local test evidence area.
Credentials, mailbox details, private records, raw dumps and deployment paths are
excluded from this repository. No G2 business transaction is to be repeated for G3.

## G3 controlled execution — 2026-11-11

The owner separately authorized bounded V22 business writes, PDF generation and
mail delivery only to a controlled recipient, and accepted the new G3 backup/restore
risk without additional archive checks. The approved plan reuses G2 for invoice-on /
mail-off and limits new business operations to two: invoice+mail and contribution-only
mail, using unused periods. No third business operation is required for negative tests.

The first real HTTP operation committed one contribution and validated invoice, then
produced its own canonical Sponge PDF with matching invoice link and ECM metadata.
R16 and expected member-field changes were reconciled; unrelated recorded rows and
payment/bank data were unchanged. The expected transport-success result was not
confirmed by the runner. Processing stopped before the second business operation,
with no rebooking or resend. A sanitized log review for the attempt window contains
both a Core SMTP success marker and a later mail-error marker. At that initial stop,
mailbox receipt, attachment identity, template rendering and
the exact delivery classification remained pending; those markers alone were not
a complete delivery proof. The subsequent owner evidence is recorded below.

Existing isolated tests passed for PDF identity/path/signature/metadata protection,
missing/changed PDF blocking before hooks/transport, post-commit business-success
retention, hook takeover/failure, transport failure, unknown outcomes, disabled mail
and denied permissions. Five focused additional invoice-mail adapter assertions
passed outside the repository. These are T2 synthetic evidence, not live SMTP failure
injection or mailbox evidence. No production mail-code change was required.

The owner subsequently confirmed receipt of the first message with exactly one PDF
whose SHA-256 matches the generated own-invoice PDF. A further sanitized log review
reported Core SMTP applicative error 121 (server response could not be read), alongside
the success marker. The exact SMTP phase is not established. The module was not
changed to suppress this Core error, and the first message was never resent.

After the owner confirmed receipt of the first message, the independent second case
created one contribution-only booking and reported standard transport acceptance.
It created no invoice, PDF, ECM entry or invoice link. Its identical consumed POST
was rejected without DB changes. Exactly two G3 business operations occurred.
Existing target-member changes were limited to end-date/timestamp fields; recorded
unrelated rows and payment/bank data were unchanged.

| Case | Current evidence |
| --- | --- |
| G3-01 | PASS within scope: real invoice/PDF and owner-confirmed English receipt, correct substitutions, exactly one own PDF with matching name/hash; Core diagnostic retained below |
| G3-02 | PASS: real contribution-only transport accepted, owner confirmed English receipt/correct member with no attachment; no new invoice/PDF/ECM effects |
| G3-03 | PASS: accepted G2 invoice-on/mail-off evidence reused without another booking |
| G3-04 | PASS at T2 scope: isolated missing/changed PDF blocks delivery and confirmed business success survives post-commit errors |
| G3-05 | PASS at T2 negative-contract scope; real first-action Core diagnostic despite confirmed receipt documented separately |
| G3-06 | PASS within scope: denied restricted sessions and consumed-post replay for G3-02; first-case replay intentionally not executed after uncertain initial result |
| G3-07 | PASS within scope: stored objects/R16/links/ECM/payment/bank reconciliation and owner mailbox/hash review complete |

Independent acceptance: **G3 APPROVED – PASS WITH DOCUMENTED LIMITATIONS** for the tested V22 profile. Additional real sends or bookings are unnecessary.
The received MIME headers have not been independently inspected; the module/HTTP
PDF MIME is application/pdf and the owner confirmed the received attachment bytes.
At the historical G3 closure, G4 browser/UX remained a separate gate; its later
owner acceptance is recorded below. During G3, no production/Core code change, configuration
change, Git publication, automatic resend or restore was performed.

The owner completed both mailbox checks: English subjects and member-name
substitutions are resolved, the invoice mail has exactly its expected own PDF with
the matching SHA-256, and the contribution-only mail has no attachment. This is
owner-observed mailbox evidence, not an agent-connected mailbox or raw MIME audit.
The first-send Core error 121 remains a technical uncertainty in the reported send
status, while actual mailbox receipt is confirmed by the owner. It is not evidence
of failed delivery; no silent conversion of failed/unknown Core results into sent is
implemented. A separate mailbox duplicate-count audit was not supplied; observed
replay rejection, unchanged database state and Core controller flow are the stated
no-resend evidence. No additional business operation was executed to improve counts.

### Owner scope decision: template configuration

G3-W02 concerns Dolibarr template configuration only and is not a module defect,
validation failure, development task or G4 blocker. It is excluded from further
module-development work. No template or implementation change is requested or made
under this decision; the historical technical delivery evidence remains unchanged.

## G4 closure — owner acceptance 2026-11-12; consolidated 2026-11-13

**G4 PASS – OWNER ACCEPTED** applies to the tested Dolibarr V22.0.5 profile.
The manual results below are reported by the owner, not a claim that the agent
performed or independently observed those manual steps.

| Owner item | Chrome 157.0.8103.38 | Firefox ESR 153.6.0esr | Evidence provenance |
| --- | --- | --- | --- |
| M01 — real BFCache restoration | PASS | PASS | Owner-reported manual acceptance |
| M02 — visual/business preview and layout | PASS | PASS | Owner-reported manual acceptance |
| M03 — result/warning comprehension | PASS | PASS | Owner-reported manual acceptance |
| M04 — visible focus and native controls | PASS | PASS | Owner-reported manual acceptance |
| M05 — overall acceptance decision | — | — | PASS — owner decision for the tested profile |

Separate automated evidence used Chromium Headless Shell 158.0.8127.18 and
Playwright-Firefox 159.0. The G4-LAYOUT-01 correction was qualified at 1440, 1280,
1024, 768 and 390 px, with an additional exact 1024 × 768 viewport check.
**G4-LAYOUT-01: CLOSED** (`d9b45f87bf01b4bab4587a5f9d4cedc6b2da47c7`).
The wider single-line description field passed targeted checks at 1440, 1024,
768 and 390 px. **G4-UX-01: CLOSED**
(`1377b417537c740780130650b67d80907d2f0d5e`).

Subsequent presentation-only cleanups removed technical model labels while retaining
internal planning, clarified EN/DE preview notices and introduced semantic option
rows (`5381366c961b0603cf2e384902037083b1922282`). The final compact, borderless,
left-aligned confirmation option table was checked in both engines at 1440 and
1024 px (`89b40b39337b66f7263ea1b629ee83ac667dd7a4`). These targeted regressions
passed without invalidating the owner acceptance. Date labels, business logic,
permissions and delivery behavior were unchanged. The 19 monitored business tables
remained unchanged during these read-only browser checks. Result fixtures for
otherwise unavailable sessions remained explicitly synthetic, with simplified CSS.

GitHub PHP 7.4/8.2 CI on the implementation commit `89b40b3` passed for both
[PR workflow 43335309353](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/43335309353)
and [push workflow 43335224395](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/43335224395).
CI and automated browser results are separate from the owner-reported manual evidence.
No browser, booking, PDF or SMTP test was repeated for this documentation update.

This closes the tested V22 G4 scope only. The G2/G3 profile limits, Core SMTP
121 uncertainty despite confirmed receipt, template-only G3-W02 decision, unexecuted
connection-loss/external-writer/Multicompany scenarios and other residual risks above
remain unchanged. V24 and production remain **NOT QUALIFIED**. The temporary module
ID and release restrictions remain in force. At this historical G4 documentation
stage, PR #4 remained draft for a separate independent delta review; no merge,
release or production approval was asserted then. The subsequent external approval
and merge are recorded in the current status above; release and production remain unqualified.

## Final review follow-up — 2026-11-13

FS4-01 adds an EN/DE warning in preview and confirmation when V22 invoice validation
may change an existing linked customer's classification. The Core target is client
1, or client 3 for an existing prospect (2/3) when prospect/customer support is not
disabled. Existing client 1 is unchanged; client 3 changes only when that support is
disabled. Invoice-off and new-customer plans have no existing-customer warning.
`SOCIETE_DISABLE_PROSPECTSCUSTOMERS` is included in the server-side profile settings,
so the normal re-evaluation/fingerprint check rejects stale confirmed profiles.
No classification write, invoice validation or transaction behavior was changed.
The existing contact-creation warning remains independent.

FS4-02 reviews all 71 requirements and updates the current evidence fields of 70
rows. All 18 columns, 71 IDs and the first ten historical columns are preserved.
The coverage legend distinguishes real G2/G3 evidence, automated/synthetic cases
and Owner-reported manual browser acceptance (M01–M04 per browser; M05 overall).
The G4 decision remains PASS – OWNER ACCEPTED. Unexecuted risk scenarios and
V24/production qualification remain open; FS4-03 was outside that follow-up and is recorded separately below.

Targeted FS4 checks: PHP lint, 388 included preview/calendar assertions, 25 invoice
profile assertions, 179 booking/mail assertions and 87 standalone/parity assertions
passed. Chromium/Firefox passed the live read-only preview/confirmation flow and
the labeled offline EN/DE classification matrix. The live edit check waits for
redirect completion; the initial immediate visibility check was a harness timing
issue, not a module change. All 19 monitored business tables remained unchanged.
CSV integrity and evidence-reference checks passed; no new business operations.

### FS4-03 — Core field terminology

Preview field and confirmation column now use the existing Core `Label` key:
English "Label", German "Bezeichnung", matching the V22 single-contribution form.
The unused module key was removed in EN/DE. Help text, placeholders, dates, input
name/values/escaping and invoice processing remain unchanged. Targeted renderer
checks read the actual Core translations with invoice off/on; this is a UI-only
follow-up, with no new business operations or broader qualification.
