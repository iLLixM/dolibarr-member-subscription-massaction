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
Real 100-member timing, memory, persistence and DE/EN HTTP checks remain pending
until the separately guarded integration run; no blanket performance claim.
