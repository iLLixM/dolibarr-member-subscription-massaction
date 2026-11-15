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

Arbitrary external hooks, actual server/connection loss with uncertain COMMIT replies,
complete session loss and other Dolibarr versions remain unvalidated. No permanent
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
synthetic, owner and risk evidence stays separate. C3.1 planning has no separately
located raw plan artifact; no inferred runtime PASS is assigned to it. C3 completes
the stated real campaign; final independent V24 acceptance, other profiles and
production/public release remain open.
