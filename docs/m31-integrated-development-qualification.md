# M3.1 — Integrated development qualification review basis

**Disposition** — PREPARATION COMPLETE – INDEPENDENT INTEGRATED REVIEW PENDING.
Technical implementation and consolidated test hardening are integrated. The
assessment below prepares CF05-07 and CF03-RISK for independent review and
subsequent Owner acceptance. Phase 5 remains PARTIAL. Release v0.1.0 is NO-GO.

## Source and PR #51 closure

Verified remote main and actual squash [`8ab803d135ac534dd2d08d28f47a8e427cd23302`](https://github.com/iLLixM/dolibarr-member-subscription-massaction/commit/8ab803d135ac534dd2d08d28f47a8e427cd23302).
The sole parent is `f1cc6dc9ca8a274d8af8c324fe87310c84606471`. The approved
[PR #51](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/51)
HEAD `3d3cbda7e0ca46db1f2fe5651a273e7aeea4b4bf` and squash share tree
`cf2b1ffcf9f38baae8edf6b18e8f0b4d7348a771`. The CI integration
`aee7c63bfab736b5be8fcae5a154f15ce43afe59` used that tree. Its recorded
parents are the target and approved HEAD. Approved-HEAD CI 31/31 SUCCESS
([Standard 9/9](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/45807198021),
[Full 21/21](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/45807197900),
[Hosted simulation 1/1](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/45807197891)).
The exact CI integration parents and tree were verified against GitHub.
Original technical review HEAD `500a7fe6ca5e59e2549d62af89fa62f017bfecf9`
closed F45-01, F45-02, F49-03, F50-02 and F50-03. Final review closed
F51-01 and F51-02. All seven are **INDEPENDENTLY CLOSED**.
Pre-Release Test-Hardening is integrated and **CLOSED WITH DOCUMENTED
LIMITATIONS**. The [test register](test-coverage.md#v010-pre-release-test-hardening)
records exact assertion counts, local permission evidence and synthetic faults.
The merged hardening tree adds no production behavior or live qualification.

## CF05-01–06 consolidated implementation assessment

| Package and acceptance objective | Merged implementation and disposition | Qualification and remaining limit |
| --- | --- | --- |
| CF05-01 — a single transaction owner and common active guard | Booking owns one outer transaction per member and injects the selected guard into Invoice. Identity, ownership and native/logical state are checked at existing boundaries. PR #34 independently approved, F34-01/02 closed. [Guard design](cf05-shared-guard-design.md) · [Booking](../class/booking.class.php) · [CoreGuarded](../class/coreguardedtransaction.class.php) · [Enhanced/MariaDB observer](../class/databasestate.class.php) | Synthetic common contracts plus real normal finance below. MySQL CoreGuarded has bounded evidence under ADR-12; MariaDBSnapshot and EnhancedMySQL keep their own evidence semantics. |
| CF05-02 — shared Invoice guard and readback coverage | Entry requires owned context. Table/engine checks and postconditions cover actual customer, line, invoice and relation. Locks and revalidation remain tied to the admitted member. PR #34 then #36/#38. [Invoice implementation](../class/invoice.class.php) · [Invoice tests](../test/unit/invoice.php) | Positive A–D outcomes. Core internals and extensions can have effects beyond inspected objects. |
| CF05-03 — financial admission, subscription and conditional Third Party | Server-owned options/profile/rights/entity and original CoreGuarded one-shot; standard Core subscription and R16; existing accessible customer or explicit auto-creation **only with invoice**. Core creates, adds line, validates and links invoice. No payment/bank operation. PR #36's F36-01 default-deny closure is historical. PR #38 implements enabled finance on all profiles under Owner Variant B. [Admission](../class/transactionprofile.class.php) · [Preview](../class/preview.class.php) · [Booking](../class/booking.class.php) · [Invoice](../class/invoice.class.php) | Existing/new customer, lines, validated invoice and link confirmed on respective A–D sources. No universal model, currency, VAT/product or entity-sharing qualification. |
| CF05-04 — finite rollback provenance and truthful UNKNOWN/STOP | Only qualified determinate failures and an intact owned rollback may permit continuation; technical/ambiguous outcome retains reconciliation state. Terminal CHAIN/RELEASE stops before later effects. CPC-02 unifies follower and batch result display without creating new continuation rights. [Booking](../class/booking.class.php) · [Terminal tests](../test/unit/terminal.php) | Synthetic ownership, failure, rollback and E15 proofs. Historical RB01-A is INVALID/STOP; B incident remains UNKNOWN/STOP with unconfirmed rollback. No destructive full-feature rollback/lost-ACK campaign. |
| CF05-05 — acknowledged COMMIT, durable result, PDF and mail evidence | Confirmed business result survives effect uncertainty. The CoreGuarded file-session binds the original consumed capability, qualified result, PDF, recipient and effect attempt. PDF checks path, identity, metadata and attachment bytes. F38-01 preserved by CPC-01 Variant A; ADR-08B deferred. ADR-08A permits only positively isolated standard SMTP failure with truthful UNKNOWN, verified original-session readback and fresh context. PRs #38, #45, #49, #50 and #51. [Checkpoint](../class/filebookingcheckpoint.class.php) · [PDF](../class/invoicepdf.class.php) · [Mail](../class/mail.class.php) · [Orchestrator](../lib/booking.lib.php) | A–D positive source-bound evidence and separate #49 controlled B transport case; #51 readback/transport faults synthetic with isolated file I/O. No exactly-once effects or guaranteed delivery. |
| CF05-06 — bound Core/configuration and side-effect risk | Owner Variant B adopts normal Core models/numbering, Agenda/Logevents, triggers and extensions with Massaction safeguards. F38-07/08/09 superseded by policy, not technically closed under the old criteria. Retain API/rights/confirmation/guard/engine checks. F36-02 pre-COMMIT PROV rename remains Owner-accepted Core residual risk. F38-10–14 independently closed. [Owner policy](owner-core-parity-2026-11-22.md) · [Source analysis](cf05-06-standard-core-source-qualification.md) · [Core parity tests](../test/unit/cf05-core-parity.php) | V22/V24 actual supported workflows below. V23 remains synthetic. No source/trigger SHA allowlists, blanket PROV veto or finance default deny. No claim of arbitrary-extension safety or filesystem rollback. |

CF05-01–06 development implementation and prior scoped reviews are available.
Integrated CF05-07 acceptance of the full chain remains separate. The
[development baseline](current-development-baseline.md) contains prior review
and squash provenance. [Issues #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22)
and [#20](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/20)
retain binding decisions and release gates.

## CF03-NORMAL — source-bound A–D matrix

A (MariaDBSnapshot, MariaDB 10.11.19), B (CoreGuarded, Oracle MySQL 8.4.13)
and C (EnhancedMySQL, Oracle MySQL 8.4.13) use Dolibarr 24.0.3 and PHP 8.2.35.
D is separate Hosted CoreGuarded, Dolibarr 22.0.5, Oracle MySQL 8.4.13,
Web PHP 8.4.28 CGI/FastCGI and CLI PHP 8.3.37. [Environment register](dolienv-environments.md)

| Tested source and cases | Profile / actual observed result | Acceptance, limitations and relevant delta |
| --- | --- | --- |
| `ba5c9a3121754fcbd4259bb8906ad1dd17f1ce00` — Phase 5 preflight, A02/B01/B02/C02 | A–C protected runtime checks 21 PASS; existing customer finance/PDF in A/B/C. B02 confirmed finance/PDF and new customer, but mail/attachment UNKNOWN/STOP. R16, invoice lines/links and configured Agenda observed in scope. | Owner-authorized and reconciled summaries, accepted with documented limitations via #44. B02 unchanged after cause review. #45 file session compatibility, #49 mail continuation and #50 result corrections assessed by their own deltas. [Local checkpoint 1](evidence/cf03/phase5-local-qualification-2026-11-24.md#checkpoint-1--initial-phase-5-readiness-and-finance-pr-41) |
| Same `ba5c9a3121754fcbd4259bb8906ad1dd17f1ce00` — A/C-auto and A/B/C-on/off, B-mail-single/double | Eight non-mail cases PASS (20 contributions over 11 confirmations, A/C auto-customer invoices and PDFs, 18 Agenda pairs). B mail/attachment 3/3 PASS in two fresh confirmations, local capture and hash/size matching. | Same accepted register, disjoint campaigns. Captures have no relay; Agenda counts bounded to recorded configurations. [Local checkpoints 2 and 3](evidence/cf03/phase5-local-qualification-2026-11-24.md) |
| `c2dc2cfdadf2c4def6bcd2f2ec20473cea3683b3` — P5L-A/C-M01/M02, P5L-B-R45 | A/C mail and invoice/PDF 3/3 each; observed attachment byte matches via local capture. B contribution-only post-#45 reload and consumed replay denied, 24-table reconciliation. | Accepted local #47 matrix; no external delivery. Retain recorded harness stops, later reconciliations and restored settings. [Local completion](evidence/cf03/phase5-local-completion-2026-11-25.md) |
| `0adc259d4739b44f9b1fb3a49574d7e1c318132c` — Hosted #45 focused test | D contribution-only, stored result reload and consumed replay denied, hosting-local SQL readback. Provider session settings unchanged. | Owner-authorized summary, #45 accepted in scope. Later #48 and #49 tests extend finance/mail coverage on other sources. [Hosted #45 record](evidence/cf03/phase5-local-qualification-2026-11-24.md#subsequent-hosted-d-checkpoint--pr-45-separate-from-local-totals) |
| `c2dc2cfdadf2c4def6bcd2f2ec20473cea3683b3` — D-F01–03, P01–03, AOFF, SMTP2 | D existing/new customer, automatic and manual end with R16, linked validated invoice, audited Agenda and zero contribution events under AOFF. Physical/authenticated PDFs 3/3; SMTP2 1+2 recipients/attachments independently compared to PDFs, receipts Owner-confirmed. | Accepted #48 summary; D-M01/02 historical no-relay capture BLOCKED, SMTP2 has no independent raw SMTP/MIME capture. No weak outcome retrospectively upgraded. [Hosted completion](evidence/cf03/phase5-hosted-d-2026-11-25.md) |
| `1ac1a8f2dfd7384829fa5b85235f1e4884d689db` — ADR08A A/B/C | A/C 2/2 successful each with local capture. B 4/4 committed, sink withheld first final ACK and captured all four; first mail/attachment UNKNOWN while three followers completed. Persisted outcomes/replay checked. | Independently accepted bounded technical evidence through #49 using authorized summaries, not raw-artifact reproduction. B-only controlled transport fault. [ADR-08A test register](test-coverage.md#adr-08a-focused-evidence) |
| `8bc36c4d4aab423e644276999066339ec1a5fd3c` — ADR08A D scopes A/B | Positive 2/2 D with SMTP handoffs, Owner receipt/PDF matches and independent 14-table replay reconciliation; separate Owner-observed continuation after intermittent SMTP response errors. | #49 accepted with the recorded retrospective Owner timing disposition; natural-error runs have no exact missing reply, independent SQL or warning attachment hash match. [Hosted ADR-08A](evidence/cf03/adr-08a-hosted-d-2026-11-26.md) |
| `042c917bd5d8844c33758f8c26170fd7eb7ecb87` — CPC02-A/B/C | Each 2 confirmed, 1 genuine business SKIPPED, explicit zero other outcomes, result reload and consumed replay refusal. Independent 24-table and file-session readbacks. B nonterminal acknowledgement. | #50 development qualification closed with limits. Invoice/mail deselected and real STOP not induced; synthetic STOP and #51 selected-effect/save-failure coverage complement these runs. [CPC-02 test register](test-coverage.md#cpc-02-cross-profile-batch-results) |

**Assessment** — CF03-NORMAL PASS within the respective accepted development
case matrices. Modified OD-P5-02 permits source-bound authorized summaries
and confirmations; independent reviews assessed their documented scope.
Each later change has a scoped regression or synthetically verified boundary
as shown. There is no one final-source A–D run. No material normal-case
rerun is identified for review entry. A specific new contradiction or
configuration proposed for acceptance requires a targeted decision.

## CF03-SECURITY-BASELINE and CF03-FAULT

| Boundary | Evidence and outcome | Limit or review disposition |
| --- | --- | --- |
| Operator roles and CSRF | A–C roles P5L-S01 on `c2dc2cfdadf2c4def6bcd2f2ec20473cea3683b3`; invalid CSRF and replay in earlier finance/ON cases on `ba5c9a3121754fcbd4259bb8906ad1dd17f1ce00`. D-S01/S02 on `c2dc2cfdadf2c4def6bcd2f2ec20473cea3683b3`. Actual authenticated Web PASS in scope. | Restricted role loss is covered; distinct entities and arbitrary runtime rights changes are different tests. [A–C](evidence/cf03/phase5-local-completion-2026-11-25.md) · [D](evidence/cf03/phase5-hosted-d-2026-11-25.md) |
| Stale consent, expiry, batch limits | A–C P5L-S02/S03/S04 and D-S03/S04/S05 on `c2dc2cfdadf2c4def6bcd2f2ec20473cea3683b3`: Web PASS. A–C used deliberately aged sessions; D elapsed one hour. Settings restored as recorded. | Fresh policy/fee and process-capability mismatch checks also synthetic. No natural expiry timing claim for A–C. |
| Entity, user, original capability | P5L-A/B/C-S05 and D-S06 real **BLOCKED**. Synthetic foreign member, run/preview owner/entity, profile, recipient/invoice and amount/options/user mismatches. [Cross-entity decision](m31-cross-entity-owner-decision.md) | Successful single-entity Web cases do not qualify inter-entity access or MultiCompany sharing. |
| Result, checkpoint and consumed replay | Focused #45/B-R45, D-S07 and later #49/#50 real source-bound reload/replay as above. #51 F45-01 local unprivileged listing denied, file roundtrip PASS. F45-02 and F51-02 fresh negative bindings and positive control. | #51 fault evidence synthetic/isolated. Guard/logger/file-session admission unchanged. Historical unknown operations untouched. |
| Transaction and selected effect boundaries | [CoreGuarded guard](../test/unit/coreguarded.php), [finance](../test/unit/cf05-finance.php), [database](../test/unit/database.php), [postcommit](../test/unit/postcommit.php), [terminal](../test/unit/terminal.php) and [checkpoint](../test/unit/coreguarded-session.php) exercise ACK/rollback, unsafe context, STOP, authorized scoped continuation, failed saves/readback and effect suppression. #51 also tests XOAUTH2, stale errors and hooks. | Runtime permission proof is local. DB/native transaction and effect faults are doubles. Hosted simulation excluded standalone file-session suite. The earlier normal and SMTP runs are the only real business/effect evidence for these boundaries. |
| Destructive real CF03-FAULT | **DEFERRED / NOT EXECUTED** for deliberate lost/unacknowledged DB COMMIT/ROLLBACK, reconnect/kill, abort/deadlock/timeout, hidden COMMIT/BEGIN, physical storage/crash, destructive replay/parallel interleaving and forced restore of the full financial workflow. | Known ACK/context/engine problems stop; invisible same-connection transitions and irreversible extension/filesystem effects may evade detection. Controlled #49 B SMTP fault is transport-only. Prior narrower isolation/rollback experiments keep their original scope. Archive integrity is not recovery execution. [Risk register](adr/ADR-0001-risk-register.md#integrated-cf03-risk-review-matrix) |

CF03-SECURITY-BASELINE remains **PARTIAL**. Historical B02 mail/attachment,
DOLIENV-ACC-01 and earlier D SMTP UNKNOWN/STOP stay unchanged. No automatic
replay, resend, retry or compensation.

## Requirement and review decisions

All 71 individual [CSV rows](requirements.csv) carry an M3.1 classification
in the Coverage gap / risk column with their original source/evidence intact.
Classifications are review inputs, not acceptance of new limitations. No
material development implementation blocker was identified in the bounded
assessment. The concrete unresolved live evidence gap is the authorized
cross-entity matrix. Arbitrary extension behavior, destructive faults and
the final ZIP remain outside the present development evidence.

| Next action | Acceptance criterion |
| --- | --- |
| CF05-07 independent integrated review | Review exact merged implementation, CF05-01–06 above, end-to-end controls, source deltas and A–D matrix. Record material gaps or bounded approval with evidence; obtain separate Owner acceptance. |
| CF03-RISK independent review | Review current guards and per-profile detectable/undetectable outcomes, extension and recovery assumptions and operational fallback. Record residual dispositions and obtain separate Owner acceptance as required. |
| Cross-entity Owner decision | Apply decision paper Variant A criteria or record independently assessed Variant B restrictions explicitly. Keep four blocked cases below PASS unless actually executed successfully. |
| M3.2–M3.4 and release | Separate acceptance steps. Then bind remaining release tests to the final artifact under #20, retaining R0-04 exact-package and R0-07 review plus separate Owner Release GO. |

**Review readiness** — Ready for independent integrated review with the
cross-entity question and evidence boundaries stated. CF05-07 and CF03-RISK
remain pending review and Owner acceptance.
