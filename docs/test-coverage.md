# Reading the existing requirement matrix

The 71 IDs and original columns in `requirements.csv` remain authoritative. Implemented/partial rows identify actual components, existing assertion labels,
real test references and explicit coverage gaps. No parallel requirement list is kept.

- `run:<label>` means the literal label (or clearly identified label prefix) in
  `test/unit/run.php`. Find it with a literal text search. Parameterized assertions
  share a label; inspect their adjacent data table for the covered cases.
- `preview:<label>` means the same in `test/unit/preview.php`. Error-key references
  identify `fails(...)` assertions. For eligibility, see the `Direct business
  eligibility contract` block: status, configured status list and subscription flag
  are separate cases even though they deliberately share one error key.
- `preview:OPT-01/02/09` cover independent mail, explicit invoice/customer consent
  and option dependencies. Adjacent failures reject malformed or forged fields.
- `ST-*` refers to `test/integration/smoke.md`; historical evidence has its date and
  baseline in `test/integration/browser-smoke.md`.
- `BR-*` refers to the historical 13 Chrome cases documented in
  `test/integration/browser-smoke.md`. Their original raw harness is unavailable;
  these references do not imply a new execution.
- `OPT-HTTP-*` refers to the targeted, real no-JavaScript HTTP test in
  `test/integration/options-http.py` and its anonymized evidence file.

A PASS applies only to the named, implemented portion. Invoice profile, transaction,
PDF, post-commit and UI runners use synthetic adapters/files; they are not real invoice,
filesystem, SMTP, browser or Multicompany integration. A fingerprint is not an atomic
replay test, and synthetic entity cases are not real Multicompany evidence.

Do not inflate assertion counts to disguise gaps. Revisit medium/high-risk missing
coverage when the relevant path changes or becomes capable of writing business data.

Phase 3B additions: `booking:<label>` references test/unit/booking.php;
`maildelivery:<label>` references test/unit/maildelivery.php. The booking runner
includes the existing preview runner once. Former OPT scenarios allowing invoice
planning are retained as history; current invoice consent has explicit Phase 4 tests.
Phase 4 references use `invoiceprofile:`, `invoice:`, `invoicepdf:`, `postcommit:` and
`loading:` for the correspondingly named runners.
`userreload:<label>` references test/unit/userreload.php. It runs the unmodified
current-user reload helpers of booking, PDF and mail against a synthetic `User`
double (inactive, missing and failed fetch, forced rights reload, denied rights)
and checks the pinned Core `User` call shape in a subprocess. It is not a real
user, permission or database integration.
The Phase 4 review-correction references intentionally use literal labels for the
critical description-encoding, permission, PDF/hook ordering, symlink-directory,
`ecm_files` and confirmed-profile rendering cases. A generic runner prefix alone is
not evidence for those boundaries.
Historical OPT-HTTP evidence is not evidence of the new booking controller.
Current evidence is separated by provenance (see `docs/phase3b-validation.md`):

- Automated synthetic coverage: preview rules, booking/transaction control paths,
  mail adapters, hook outcomes and UI rendering regressions.
- Real integration: original ten-member batch and independent database reconciliation;
  INT-01–INT-11 within their stated scopes, including controlled rollback/R16 adapters
  and two parallel database connections. INT-03 itself remains a function-boundary test.
- `HTTP-01`–`HTTP-05` and `HTTP-03-phase`: separate authenticated HTTP/session follow-up,
  classified HTTP REPLAY VALIDATED WITH LIMITATIONS; no mail selected. Guard attribution
  uses request values and source; the full-user hash comparison has a documented limitation.
- `UI-01`–`UI-09`: owner-performed manual browser acceptance, 9/9 PASS; not an automated
  or agent-reproduced browser test.
- External confirmation: the owner reported receipt of ten original emails. This is
  not an agent-observed mailbox check or validation of every template/language variant.

At the historical Phase 3B checkpoint, arbitrary external hooks, actual server/connection
loss with uncertain COMMIT replies, complete session loss and other Dolibarr versions
remained unvalidated. Later bounded MySQL fault cases are indexed below; they do not
qualify all network failures or other versions. No permanent
exactly-once guarantee or unrestricted production approval is claimed. Synthetic
mail-failure/hook tests do not become real SMTP-failure tests through successful receipts.
The owner accepted the documented R05–R09 boundaries for Phase 3B on V22.0.5; these
limits remain in force. No new requirement matrix or test execution is implied.

## Phase 4 completion and final-review provenance

Use `phase4-implementation-validation.md` for the bounded G2/G3/G4 evidence.
`G2-*` names real V22 Core/DB/PDF cases; G2-05 is a CLI mixed batch, not a
browser booking. `G3-*` separates real delivery/owner receipt from T2 synthetic
negative PDF/mail/hook cases. Core diagnostic 121 remains uncertain despite receipt.

`G4-01`–`G4-08` identify browser/UX areas, not additional business-write tests.
Owner-reported manual browser acceptance on 2026-11-12 was 9/9 PASS in Chrome
157.0.8103.38 and Firefox ESR 153.6.0esr: M01 maps to G4-06, M02 to G4-01/02/03/07,
M03 to G4-04/05/08, M04 to G4-07; M05 is the overall PASS – OWNER ACCEPTED decision,
not separate evidence for every requirement. Matrix references apply only to the
named UI aspect. Automated Playwright checks and labeled synthetic result/rendering
fixtures remain separate evidence; none establishes new rollback, SMTP fault or
Multicompany qualification. Historical Phase 3 evidence and original matrix columns
are retained; current profile limits supersede obsolete blanket Phase 4 deferrals.

FS4-01 classification warnings use the confirmed customer/configuration snapshot.
The profile setting is re-resolved in the existing fingerprint path before booking.
The client 0/1/2/3 × setting 0/1 × invoice off/on matrix is synthetic, not a new
live classification write. That V22 evidence does not qualify the V24 classification
matrix; the later bounded C3 profile is recorded below. Production is NOT QUALIFIED.

## V24.0.3 C3 references (2026-11-14)

Use the [consolidated C3 evidence index](phase4-implementation-validation.md#c3-v2403-integration-campaign-2026-11-14)
for exact Core/module SHAs, private artifact references, chronology and profile.
These IDs are integration evidence, not additional unit-runner assertion counts:

- `C32-01` / `C32-07`: real list/action/preview and separate-role direct wizard
  denial; no negative final booking POST or full rights matrix.
- `C32-02`–`C32-06`: real subscription-only, existing-customer invoice,
  new-customer consent, zero invoice and positive R16 overlap respectively.
  C32-06's missing-consent negative check is **isolated synthetic** production
  function evidence beside a real unchecked checkbox, not a second HTTP write.
- `C32-08`: real Core invoice validation followed by a controlled pre-COMMIT fault
  and observed rollback; `C32-09`: a real COMMIT succeeds, then its acknowledgement
  is withheld by the adapter. Original result stays unknown and batch stops.
  Neither is a physical network interruption or complete failure-point matrix.
- `C3.3-A`: actual canonical PDF/ECM/attachment checks, authenticated PDF download,
  anonymous denial and selected=true disabled-mail adapter; downstream mail hooks
  and transport were not executed by A.
- `C3.3-B-R1`: actual consumed byte-identical POST, original token/revision and
  authenticated session, one authorized preceding invoice booking, no duplicate
  effects. Mail was off. This supersedes the **gap**, not the original NOT EXECUTED
  history, and does not establish mail-enabled replay or crash durability.
- `C3.3-C`: automated real Chromium/Firefox preview/options/confirmation,
  overlap notice, stale revision/CSRF/invalid-key and role boundaries. The actual
  successful result and invoice link come from B-R1 in both engines. Earlier
  unavailable-outcome fixtures remain synthetic. No V24 manual BFCache, keyboard
  or exhaustive layout/selection/permission acceptance is implied.
- `C3.3-D`: existing invoice, one real production-adapter/SMTPS send, real
  sendMail/sendMailAfter dispatch without registered extra handlers, safe recipient
  and restore checks. PDF receipt/hash/content and delivery are **owner-reported**;
  no agent mailbox access. Generic template contains no amount/period text tokens;
  corresponding invoice data were checked in the attachment.

Append these references only to the actually observed requirement aspect. Real
positive cases do not establish their unexecuted negative variants. Existing V22,
synthetic, owner and risk evidence stays separate. G01 planning was recovered as a
contemporaneous owner-supplied chat attachment (2026-11-13), not a standalone
original local-development file or runtime PASS. Exact original transmission time
is unestablished; the private V2 package retains the 17-case plan/evidence comparison.

PR #6 merged on **2026-11-15** as
`96f870e789eb26b6eb8a6b8eeb0c79812018b0ab`, completing bounded compatibility
development. Final external review: **APPROVED FOR MERGE – ALL CONDITIONS
FULFILLED**; **OD-PR6-01/02 fulfilled**. Post-merge CI
[run 43660371617](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/43660371617)
passed PHP 7.4/V22.0.5, PHP 8.2/V22.0.5 and PHP 8.2/V24.0.3.
The real runtime reference remains `5a31d4c8d9cfe6733f59efaef430799df60e1902`;
the merge is a development baseline, not a replacement execution reference.
All 71 requirement mappings (53 bounded V24 partial-evidence rows and 18 dedicated
live scenarios NOT EXECUTED) and accepted evidence/risk limits are unchanged.
Version `0.1.0-dev`, temporary ID `584620` and exact profile limits remain;
production/public release remain **NOT QUALIFIED / NOT APPROVED**. Other profiles
are not generally qualified. No new test execution or expanded PASS is implied.

## Historical P2 cross-version evidence (2026-11-15; P3 was pending)

Admission >=21.0.0 is independent of qualification. P1 source analysis covers 15 tags;
P2 local PHP 8.2 runs six existing runners against every target. The workflow includes
21 pinned pairs (15 with 8.2, six V22 additionally 7.4); CI results require their actual
commit/job evidence. None are real integration tests.

New literal assertions in existing runners cover: `open admission`, `R16 boundary`,
`future uses observer, not guessed semantics`, `objective callable mismatch`,
`incompatible observer rejected before declaration`, `incompatible Core hook controlled
without inheritance fatal`, PDF availability/missing-model checks (`run.php`);
`wrong_observed` and existing actual Core R16 equality/overlap/failure fixtures
(`booking.php`); uniform rejected VAT literals, actual Core VAT hook and
`actual Core price hook override is observable` (`invoiceprofile.php`); stored VAT/
price mutation rejection and subscription-only invalid-VAT independence (`invoice.php`).
A separate 99.0.0 label subprocess reuses actual V24 Core methods and existing booking
assertions; it is future-admission evidence, not proof of an actual future Core.

No requirement matrix fields changed: 71 IDs, historical evidence and 18 dedicated live
scenarios remain. Existing real V22/V24 evidence is not retroactively assigned to
other tags or the P2 implementation. See the P2 section in the central validation
for limitations; production and public release remain unqualified.

P3 subsequently closed with Owner acceptance and documented limitations; see the
[P3 qualification guide](p3-qualification-summary.md) for the 21-job CI evidence,
PHP-8.2-only supplemental scope and separate P3-DOC01 reference addendum.
Independent overall code review and merge decisions remain separate.

## MySQL iteration 1 evidence (2026-11-17)

Use the [central MySQL history, profile and private evidence index](phase4-implementation-validation.md#mysql-iteration-1).
This legend does not create requirement assignments; all 71 IDs/18 columns in
`requirements.csv` remain unchanged.

- `database:<label>` refers to `test/unit/database.php`: synthetic server-family,
  native-identity, freshness/event/counter, bootstrap, ownership and completion
  contracts. `request:<label>` refers to `test/unit/request.php`: synthetic F01
  stored-result/readiness/session/replay contracts. Neither is a real outage test.
- FIX01's `BookingReadViewDatabase` and literal `FIX01 ...` assertions in the
  existing booking/invoice runners separate current locking reads from an old
  consistent view, reproduce the old metadata order and check pre-Core rejection,
  actual pinned Core end-date logic and invoice lock order. The unchanged 21 CI
  pairs exercise PHP/Core source combinations, not live MySQL servers.
- `G0-*` denotes technical mysqli/InnoDB observation and separately Owner-executed
  negative windows, before installed Dolibarr business qualification.
- Original I1-05 `T3-*`/`T4-*` references require the **I1-05** campaign context;
  they are not every generic T3/T4 test level or the earlier C3 campaign. Actual
  serial business/fault/PDF/HTTP cases retain their exact profiles and partial
  scopes. Original `T4-09` remains FAIL/1062, 19/20 acceptance, not a new PASS.
- `FIX-D01`–`FIX-D04` are real controlled MySQL concurrency/lock/rollback cases;
  `FIX-E01`–`FIX-E03` are real current-history/R16 cases, and `FIX-E05` includes a
  real Core invoice/customer positive and a rejected competitor with zero DML.
  D03/E01/E02 use predeclared legitimate disjoint periods; changed overlap consent
  is tested by D02/E03. No first-writer retry or new consent rule is implied.
- `FIX-E04` is **read-only** existing SQL-NULL/current-history/MAX/object/storage
  evidence, not a new Core `update_end_date()` invocation or NULL-fixture write.
  Historical real I1-05 T3-15 and current synthetic actual-Core NULL regressions
  remain separate supporting evidence.
- The D01 coordinator/diagnostic STOPs remain original artifacts. Read-only
  reconciliation establishes the normal successful writer's Core Agenda side
  effect and the passed concurrency contract; no booking was repeated.

I1-05 FAILED → FIX01 PASSED (20/20) requalifies only the stated
V24.0.3/PHP 8.2.35/MySQL Community 8.4.13 instrumented mysqli/InnoDB/RR profile.
Historical MariaDB G2/G3/G4/C3 evidence, synthetic cross-version CI and Owner mail
receipt are distinct. Original MySQL post-COMMIT PDF cases do not imply SMTP
qualification; FIX01 generated no new PDF or mail. Unexecuted variants, PostgreSQL
iteration 2 and production/public-release restrictions remain
as recorded in the central validation; no general MySQL PASS is inferred.


## I107 hardening evidence

See [I107 security hardening](phase4-implementation-validation.md#i107-security-hardening)
for the historical initial review/hardening and the completed follow-up/merge.
Existing `database:`, `booking:`, `invoice:` and `request:` assertions prefixed
`I107` cover actual-event isolation policy, known-own versus uncertain rollback,
Agenda option/event reachability, missing/MyISAM table gates and unchanged lock order.
These are synthetic adapters; no live Core-table Engine conversion is implied.
`I107-isolation` is real but read-only (seven own-event cases, no business DML).
`I107-POS-SUB`, `I107-POS-INVOICE`, `I107-AGENDA-ROLLBACK`,
`I107-CONCURRENCY` and `I107-R16-LONG/SHORT` use actual Core/MySQL on new synthetic
fixtures and independent persisted/audit checks. The retained CLI seller-context
warning does not establish other tax profiles. No new PDF or SMTP evidence, live
MariaDB rerun at that I107 checkpoint or requirement remapping; all71 IDs/18 columns remain unchanged. The later MDB campaign is distinct below.
Separate F01/F02 acceptance matrices and the final commit/CI/runtime-hash mapping
are private execution evidence, not a second requirements matrix.

## MDB-TEST-01 real MariaDB evidence and PR #13 closure

The [central closure and per-case limits](phase4-implementation-validation.md#pr-13-post-merge-closure-and-mdb-test-01--2026-11-17)
records actual MariaDB 10.11.19 / V24.0.3 / PHP 8.2.35 / mysqli/InnoDB execution
at `0999efb`, independently confirmed **9/9 PASS**. PR #13 merged on 2026-11-17
as `aaf1f3786a4c9ea7e1707ad41cb821b0bb271e3d` with the identical tested tree.
Both independent external follow-ups are complete: APPROVED FOR MERGE WITH
DOCUMENTED SCOPE; I107-F01/F02/F03 closed, I107-FU-01 remains non-blocking P3 Low.

| Reference | Real evidence; preserve this boundary |
| --- | --- |
| MDB-01 | MariaDB native state and own transaction completion; no MySQL P_S dependency, snapshot continuity only. |
| MDB-02 | Core contribution persistence and confirmed COMMIT. |
| MDB-03 | Agenda gates/events/actor links ON and process-local OFF; no persisted activation-cycle test. |
| MDB-04 | Existing-customer invoice/line/entity/subscription link, 120 EUR/VAT0. |
| MDB-05 | Auto-created customer and validated invoice within the outer transaction. |
| MDB-06 | Injected fault after real invoice/Agenda DML; confirmed atomic rollback, independent 24-table/file equality. |
| MDB-07 | Actual concurrent lock wait/current revalidation; loser no Core subscription/DML/1062. |
| MDB-08 | Actual longer history, earlier shorter period and consented overlap; Core maximum end. No new legacy NULL writer. |
| MDB-09 | Authenticated Chromium flow and identical consumed POST replay; Firefox real result, no invoice/mail. |

These are real Core/MariaDB tests on synthetic business fixtures, not the existing
synthetic `database:`, `booking:` or `request:` doubles or the 21 PHP/Core CI pairs.
The 173 read-only trace assertions and checksum validation are additional evidence
analysis, not 173 live database tests. Protected CLI/browser runs were Owner-executed;
external independent MDB confirmation is Owner-reported. Private artifact names and
integrity references are in the central validation, not public download links.
No new requirement assignment or modification of 71 IDs/18 columns is introduced.
Historical I1-05 failure, read-only E04, I107 warning and all unexecuted scenarios
retain their scopes. No new PDF/SMTP, general DB-version or production/release approval.

## Issue #8 configurable batch limit

`run.php` covers module defaults/invalid/overflow values, 25/100/150 boundaries,
the global minimum and a hook subprocess whose oversized IDs cannot be fetched:
the count-only notice proves early exit before object access. `request.php` covers
stale confirmations after reduction, pre-readiness and pre-orchestration rejection,
no truncation and preserved replay protection. These are synthetic contracts.
Real MariaDB qualification at implementation `72bed7f` passed: 100 contribution-only
bookings, independent 24-table persistence checks, DE/EN early notices, 99/100
previews, persisted module limits25/150 and a stale26-member direct POST after
reduction150→25. Temporary module configuration was restored. A small real
customer/invoice/Agenda path passed separately. See the central Issue8 evidence
section for exact metrics and CLI-versus-Apache boundaries. No blanket performance claim.

### PR #16 independent review and later Owner acceptance

See the [current finding statuses and evidence separation](phase4-implementation-validation.md#pr-16-documentation-follow-up--2026-11-18).
Independent review of `5f71102cc631c619496d0d6f2e002b97073ae644` completed:
**APPROVED FOR MERGE WITH DOCUMENTED SCOPE**. The new documentation follow-up is
separate from that reviewed implementation/evidence HEAD and changes no tests or
requirement assignments. New-HEAD PR/push CI is recorded separately in PR #16.

- F16-01: **CLOSED – OWNER ACCEPTED**. The I105 URL was an assignment error;
  `<PRIVATE_MARIADB_WEB_ENDPOINT>`, V24.0.3/PHP8.2.35/MariaDB10.11.19 is the accepted target.
  No I105 rerun or installation-equivalence claim.
- F16-02: **RESOLVED WITH DOCUMENTED SCOPE – OWNER-CONFIRMED HTTP TEST**.
  A later authenticated manual Owner browser booking created 100/100 subscriptions,
  no invoice/email, approximately 6 seconds confirmation-to-results. This is
  Owner-reported acceptance, not a new automated run, independent reproduction,
  memory measurement, HTTP raw trace or expansion of the original CLI evidence.
- F16-03/F16-04/F16-05 remain **P3, non-merge-blocking**: O(N) raw-ID validation;
  incomplete independent reconstruction of individual Agenda links from hashes;
  safely consumed processing state in a narrow post-consumption limit-change race.
  No code fix or additional evidence execution is claimed.

The original synthetic contracts, automated CLI100, automated browser/negative
POST tests and later manual HTTP100 are distinct evidence levels. No general
HTTP load/replay/parallel-booking/PDF/SMTP or production/release approval. Draft,
merge decision and Issue #8 closure remain with the Owner; no merge performed.

## CF-01 profile/readiness regression (2026-11-18)

`CF01` assertions in `test/unit/profile.php` run through the existing request runner.
They reuse the booking/protocol fixtures and exercise the production profile/CAS
logic with in-memory SQL results, including admin/CSRF, migration ambiguity,
concurrent config updates, confirmation binding, P_S denial, preview isolation,
consumed result/replay and per-member snapshot semantics. These are **synthetic**;
no real admin HTTP, DB or Core-Guarded write qualification is implied. Existing
business fixtures supply server context as the controller now does, rather than
bypassing profile admission. The old F01 preview-readiness expectation is superseded
by CF-01; historical campaign outcomes are not reclassified.

See [CF-01 contract and execution limits](phase4-implementation-validation.md#cf-01-profile-selection-and-preview-boundaries--2026-11-18).
Real CF-01 setup/configuration/race/HTTP and live Enhanced/MariaDB regression remain
**NOT EXECUTED** pending a separately authorized environment. CoreGuarded booking
and D-01 qualification remain disabled/deferred. No new assignment in the sole
71-ID requirements matrix is made by this legend.

## F27 pre-DML batch/runtime contracts (2026-11-19)

`test/unit/terminal.php`, included by existing `postcommit.php`, uses the actual
batch orchestrator, fake services/effect fences and local PHP file sessions.
It covers terminal success/rollback/unknown, E02, E15, qualified continuation,
missing classification, consumed replay, NOT_PROCESSED rendering and real runtime
session/logger/callback refusal. Optional Xdebug monitoring records call order,
never arguments. This is **synthetic service evidence plus real local session I/O**,
not real Core business DML, authenticated HTTP or Apache qualification.

The separate read-only source auditor and twelve pinned-source files document the
bounded standard Core/Agenda path. Actual selected-runtime callback closure and
new real contribution/Agenda tests are **NOT EXECUTED**. See the
[F27 finding/gate matrix and independent delta-review assignment](phase4-implementation-validation.md#f27-pre-cf-02-dml-follow-up--2026-11-19).
Historical AP3/D01 evidence is not rewritten; all six Issue22 gate tasks and
CF02–CF05/release acceptance remain open. No change to the sole71-ID matrix.

### F28-01 actual-controller ordering (2026-11-19)

The request runner includes `test/unit/controller-runtime.php`. Isolated workers
execute the real controller body with synthetic bootstrap/admission/services,
native local file sessions and layout/header observation. They test known runtime
refusal before consumption, localized redirected errors, no false pre-DML UNKNOWN,
post-close binding checks, consumed replay after possible DML, legacy profiles and
CSRF/entity protection. A negative control reproduces the old75a531d9 ordering bug.
These30 assertions and sanitized traces are **instrumented controller evidence**,
not real authenticated HTTP/Core-Guarded business DML. See the dated F28-01 section
in `phase4-implementation-validation.md`; independent final-HEAD review is pending.

### F27-01 selected-runtime evidence addendum (2026-11-19)

The earlier unexecuted-runtime statement describes the pre-addendum evidence.
`docs/evidence/f27/f27-runtime-readonly.json` now records the actual protected
configuration via seven SELECTs, without callback/business/session execution.
Three selected callbacks match the pinned Core: Logevents, ActionsAuto and
Societe ContactRoles. Five actual early-return guard calls use observed flags
and a DB-rejecting spy with no native connection. The standard Agenda write path
remains source-analyzed; no new contribution/Agenda transaction is claimed.

See the [runtime-bound qualification addendum](phase4-implementation-validation.md#f27-01-runtime-bound-qualification-addendum--2026-11-19)
for source pins, reproduction, configuration boundaries and the Stage-B decision:
**NOT EXECUTED**, because this concrete snapshot/source assessment identifies no
remaining concrete callback gap warranting a new lab run before independent
sufficiency review. This assessment does not independently close F27-01 or the
gate. `finalization-validation.json` records ten successful local regression
runners; they are synthetic/controller/session checks, not new real DB/HTTP tests.

## CF-02 Core-Guarded development (2026-11-20)

The independent no-Dolibarr runner test/unit/coreguarded.php initially executed 88 synthetic assertions against the bounded D-01 transaction coordinator, including SET/BEGIN adjacency, NO_CHAIN/CHAIN/RELEASE completion, nesting, 1568, depth/native connection faults and no Performance Schema. The separate test/unit/coreguarded-session.php initially executed 15 local file-session assertions (extended to 19 PASS after the confirmation-origin and replay-latch follow-up) for durable consumed-state readback and direct service admission. Both are included in standard CI and full CI. Neither is live MySQL/Agenda or authenticated HTTP qualification. Existing business/observer tests and the single 71-ID requirements matrix remain authoritative. See [CF-02 validation and remaining gates](cf02-implementation-validation.md). Real restricted-MySQL and Enhanced/MariaDB business regression: **NOT EXECUTED on this branch** at document creation.

### CF-02 Owner-accepted real MySQL review-entry evidence (2026-11-20)

At implementation SHA [BT]c2347f610efc1c4c160dd0b21ffaf095753e7e40[BT], [nine-profile CI #44723124036](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/44723124036) passed 9/9; synthetic CoreGuarded adapter (88), file-session (19) and POS01 witness comparison tests are distinct from the Owner's actual Dolibarr V24.0.3/MySQL 8.4.13 restricted-account HTTP booking. One real empty Core BEGIN/ROLLBACK PASS and two synthetic-member bookings (#65/#66) with standard Agenda were observed in preserved before/after SQL snapshots. A's intended start was not selected; B's contribution, dates, amount, member end and Agenda checks were positive, but both formal witness results remain STOP, B solely for the absent note marker. The Owner **waived a marker-only repeat and accepted bounded positive evidence for an independent external review**. See [CF-02 review handoff](cf02-review-handoff.md) and [evidence disposition](evidence/cf02/pos01-owner-test-acceptance-2026-11-20.md). No real CF-02 negative fault/concurrency/replay or current-source Enhanced/MySQL and MariaDB invoice regression is implied; Issue #22 CF-03 and release gates remain open. Historical CF-01/F27 statements above are time-scoped.

## CF-03 Stage 0/1 A-D shared-site qualification scaffolding (2026-11-20)

The new pure `test/unit/cf03-lab.php` exercises the four environment policy contracts, A/C one shared schema with different manually selected credentials, CoreGuarded contribution-only and normal/no-change/selected invoice-existing witness comparison, one-shot evidence parameter validation, wrong profile/source/conf drift and protected table deltas. It runs in both nine-profile standard PR CI and the optional 21-profile full CI, plus the existing offline runner. It has **no DB, HTTP, mail, PDF, fixture creation or destructive fault execution**.

`tools/cf03-lab.php` provides separate owner-run **read-only** A-D preflight/check-only/baseline/verify. Baseline/verify write only private evidence files, and the actual authenticated UI booking remains a separate owner action. This general witness is not the historical A-only POS01 witness. In particular, preflight's Performance Schema SELECT permission does **not** prove active EnhancedMySQL observer event continuity; selected invoice-row counts do **not** establish full invoice/Agenda/PDF/mail equivalence; and CoreGuarded POS database readback is **not** independent physical event identity or HTTP acknowledgement.

A and C are **one installation and same schema with manual serialized credential/profile switching**; D's separate installed ZIP module copy is admitted only when nine selected runtime source hashes agree with the checked-out CF-03 files (not a full ZIP audit); they are not independent or concurrent DB environments. B's application username and D's concrete runtime coordinates remain to be supplied and independently checked. Issue #22 CF-03 gates (six items), complete real MySQL fault/concurrency/batch/replay, current-source MariaDB and Enhanced invoices, hosted V22 ZIP installation, selected postcommit effects and full-HEAD independent review remain **OPEN / NOT EXECUTED**. The existing requirements.csv remains the sole requirements matrix. See [matrix](cf03-qualification-plan.md), [operator runbook](cf03-operator-runbook.md) and [Stage 0/1 review handoff](cf03-stage01-review-handoff.md).

## CF03-W01 Paket B follow-up regressions (2026-11-20)

The new `test/unit/cf03-source.php` verifies deterministic source inventories, formerly unbound bookingmember/preview/invoiceprofile module dependencies, additional deployed PHP, and mutation of selected Facture/Subscription/Agenda Core files without any DB/HTTP. It runs in standard nine-profile, optional full and targeted PHP 8.3/V22.0.5 CI, alongside `test/unit/cf03-lab.php` newly asserting client 0/2 STOP vs 1/3 PASS, supported goods/service line types, exactly one invoice unit, positive/negative operator identity, and event author/owner/resource attribution with exact STOP reasons. Source inventories are bounded (module production PHP and selected Core critical-path), not a universal installed ZIP or full Core tree proof. No real B/C invoice/Agenda or A–D business test executed on Paket-B source. W01 admission and all six CF-03 Issue #22 gates remain open until independent review.

## CF03-W01 Operator Readiness Hardening regressions (2026-11-21)

- `test/unit/cf03-lab.php`: per-environment PHP 8.2.x (A/B/C) and 8.3.x (D) admission, missing/invalid PHP major/minor, full PHP patch drift between baseline/verify, exact STOP code assertion; runs in the existing PHP 7.4/8.2/8.3 synthetic CI without weakening universal PHP 7.4 tool-compatibility.
- `test/unit/cf03-runbook.php`: parses **published** A contribution Agenda ON, B and C invoice-existing (Member Agenda OFF but actor mandatory), and hosted D contribution command option patterns from the real operator runbook; validates each with `Cf03LabContract::arguments()` and proves missing actor STOP. Pure CLI-argument contract checks only (no DB/HTTP).
- `test/unit/cf03-reader.php`: SQL projection fault now affects **invoice events only** and proves member events remain valid when both Agenda components are ON; expected invoice projection STOP retains its exact reason, avoiding unrelated PHP warnings.

These tests run in the nine-job standard, optional full and focused PHP8.3/V22.0.5 workflows and local pure synthetic script. Their PASS is **not** a live database/HTTP result. Independent review of the changed HEAD and fresh Owner A–D preflights remain prerequisites to any new real test.

## F32-W01 final Invoice-Agenda SQL-reader and mixed-flag regressions

New pure `test/unit/cf03-reader.php` requires the real production `tools/cf03-lab.php` and executes `cf03State()` on a strict synthetic SELECT-only DB interface that returns only columns actually named in each SQL query. Covers eight independent combinations of member Agenda ON/OFF and BillCreate/BillValidate ON/OFF, including member OFF/Bill ON, missing expected events, unexpected events, incorrect projected event entity/author/owner, wrong resource user, and absent selected columns triggering `CF03InvoiceAgendaProjectionIncomplete`. The modified `test/unit/cf03-lab.php` confirms an actual actor is compulsory even for invoice member-Agenda OFF. These run in the normal 9-profile matrix, targeted PHP 8.3/V22 CI, optional 21-profile CI and the local synthetic script. No real SQL server, HTTP or booking action is used in these tests.

The W01 independent review at HEAD `7fc5898d57d7c6bba51cf7cb2c572c5dc3131217` had 9/10 original findings CLOSED with F32-W01-08 PARTIAL and new F32-W01-11 OPEN. Corrective implementation still requires a final SHA-pinned independent delta-review. All six Issue #22 CF-03 qualification gates remain OPEN.

## CF03-W01 test tooling hardening (2026-11-20)

New pure `test/unit/cf03-evidence.php` exercises immutable private 0700 hosted evidence directories, 0600 baseline files, ownership, SHA-256 integrity, no symlinks, invalid roots, unexpected files and no retry, without DB/HTTP. Extended `test/unit/cf03-lab.php` covers explicit invoice expected values, schema-column drift, hosted D evidence-root validation, permitted A–D and B/C scoped invoices with exact member/invoice Agenda actor/count matching, and fail-closed customer/currency/quantity checks. Both run in standard nine-profile and optional full 21-profile CI, plus the new focused PHP 8.3 / Dolibarr V22.0.5 workflow. Existing CI matrix remains nine jobs.

The live read-only `tools/cf03-lab.php` now checks V22/V24 selected `information_schema.COLUMNS` fields with stable sorted hashes before baseline/verify, and includes `tools/cf03-evidence.lib.php` in source SHA-256 binding. B/C existing-customer invoice checks are narrowed to one linked validated invoice and one line with explicit known expected configuration. Enabled line-level `LINEBILL_INSERT` Agenda event attribution remains outside this witness and causes STOP. No creation of a customer, physical rollback event proof, active Enhanced observer identity, SMTP/PDF, live HTTP, ZIP-installation integrity or real fault test is claimed. See [W01 boundaries](cf03-w01-validation.md). All Issue #22 CF-03 completion gates remain open.
