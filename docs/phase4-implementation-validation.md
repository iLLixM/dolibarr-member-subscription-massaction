# Phase 4 implementation validation

> **2026-11-21 current-versus-historical evidence note:** The dated V22/V24/G2/G3/G4/C3/I107/MDB/CF01/CF02 results in this file retain their exact source profiles and historical validity. [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22) now requires **full financial contribution/Third Party/invoice/PDF/mail capabilities** on CoreGuarded, EnhancedMySQL and MariaDBSnapshot for v0.1.0. PR #32 was **squash-merged** as `425980c43a51a9f27073133212f18825ac6da248` for **read-only witnesses/evidence/privacy**, not CF05. CF05-01..07, complete real **CF03-NORMAL A/B/C/D**, retained 21-job security CI, exact D ZIP and release gates are **OPEN**. Destructive real CF03-FAULT tests are **deferred OPEN/NOT EXECUTED**, without weakening runtime safety. Older contribution-only statements are history, not current feature target; current CoreGuarded invoice gates remain disabled. See [current baseline](current-development-baseline.md).

## D-01 evidence and conditional Option A closure — 2026-11-18

AP2 documentary base: accepted main `3bc9e4653778cdc870d79e2f5b7e025bc9ca036a`,
normal merge of [PR #25](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/25).
Parents: accepted CF-01 `e357e93bea7f4b18730596245484a6ab02c6c634` and unchanged D-01
review HEAD `abb1b5cfea17a744840a744991c4425e639c2376`. Exactly32 additive evidence
files, no product changes/rebase. All30 payload hashes plus original manifest and
checksum bytes preserved. Reviewed push/PR CI:21/21 PASS each; [post-merge main CI](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/44412022686):
**21/21 PASS**. CI is synthetic evidence, not completion/session/logger qualification.

The supplied independent review concludes **D-01 EVIDENCE ACCEPTED WITH DOCUMENTED
LIMITS** and **OPTION A VIABLE WITH CONSTRAINTS**; it independently checked all31
SHA-256 entries, sizes and matrix consistency. Owner acceptance is **ACCEPTED WITH
DOCUMENTED LIMITS**; Option A — Bounded STOP is **CONDITIONALLY APPROVED**, per the
[complete durable review/Owner record](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/25#issuecomment-6993488134)
and [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22).
F-D01-01 is conditionally decided architecturally, not technically qualified.
F-D01-02 keeps unacknowledged/uncertain ends at **UNKNOWN/STOP**. This records the
external review rather than a formal GitHub vote. The immutable
[experiment report](d01-loaded-core-feasibility-v1.md), **D-01 CHANGES REQUIRED**,
traces, matrix, hashes and PARTIAL classification are unchanged historical evidence.

### Actual D-01 evidence and limits

Real loaded V24.0.3 Core `5c6c7ff6a5112413b1c27221c8d11750afad56f8` database libraries,
PHP8.2.35/mysqli/mysqlnd8.2.35, MySQL Community8.4.13, InnoDB and restricted
`<PRIVATE_RESTRICTED_LAB>` DML-only account (P_S SELECT denied1142):37 completed measurements/
controls plus1 PARTIAL. Ordered SET RR → genuine Core BEGIN works;16 behavioral
isolation combinations,1568 foreign refusal,2 coordinated locking/read-view cases,
6 completion cases and real lost COMMIT/ROLLBACK replies are documented. There are
54 new retained synthetic rows and27 unchanged historical lab rows.

Most cases instrument native mysqli while inheriting actual Core transaction/query
methods. The2 exact factory/driver/native controls cover ordinary entry/end only,
**not all completion variants**. PARTIAL lacks a fully retained exception/return;
read-only reconciliation does not turn it into PASS. No full main.inc.php,
authenticated business/HTTP/session, real Agenda or configured DB logger integration
was qualified. Same-connection replacements can remain invisible; no Enhanced
identity/isolation/completion equivalence follows. Historical SH-01 NO-GO stands.

### New pre-DML gate — OPEN / NOT EXECUTED

Apply the [ADR Owner addendum](adr/ADR-0001-core-first-transaction-profiles.md#d-01-owner-addendum--2026-11-18)
and [risk addendum](adr/ADR-0001-risk-register.md#d-01-completion-risk-addendum--2026-11-18):
classify completion before end, then terminal STOP immediately after acknowledged
CHAIN/RELEASE, **before further module SQL, mail/PDF or another member**. Preserve
acknowledged member result independently of batch STOP; visibly identify unprocessed
selections. Only qualified connection-independent checkpoint/HTTP-result/end steps
may follow. Controlled partial processing is provisionally accepted, not a guarantee
of full multi-member operation on every hosting configuration. E10/E12 continuation
needs a suitable qualified post-end state; E11/E13 remain UNKNOWN/STOP and E15
preserves accepted DB outcome despite checkpoint uncertainty.

**Mandatory before CF-02 contribution DML:** focused synthetic and approved isolated
real completion/session-checkpoint/logger proof for NO_CHAIN/CHAIN/RELEASE, independently
assessed. Prove pre-classification stability, actual Core post-end logging, SQL-free
terminal decision, configured independent durable checkpoints/result display and
controlled HTTP teardown; lost ACK, checkpoint failure and zero forbidden follow-on
SQL/member/mail/PDF. No dangerous post-end SQL diagnosis, retry, compensation,
foreign rollback, completion whitelist, session/global normalization or new
transaction manager. This task executes **no new real test**; the gate is still
**NOT EXECUTED**, not closed by architecture acceptance or CI.

CF-03 contributions/standard Agenda ON/OFF/HTTP replay/concurrency/V22/V23 and
EnhancedMySQL/MariaDB regressions, later exact artifact/security/release/Owner GO
remain open. CF-01 itself was independently/Owner-accepted and merged in PR #24;
its real DB/HTTP/CAS persistence/negative and new-source Enhanced/MariaDB evidence
remains NOT EXECUTED. Core-Guarded booking stays disabled; AP2 is a separate
unmerged documentation proposal pending independent review and Owner acceptance.

All earlier dated CF-00/CF-01/other campaign pending or NOT EXECUTED statements below
are preserved as their original checkpoints; this addendum records subsequent
bounded evidence/acceptance without retroactive qualification or remapping the71
requirements. No historical experiment or requirement matrix changes.

## CF-01 profile selection and preview boundaries — 2026-11-18

Base: fetched `origin/main` exactly `6247704815cf532ec632616011133c646aa00005`
(PR #23 normal merge). CF-00 independently approved, Owner-accepted, F23-01–07 and
K23-01 CLOSED; its old pending-review prefaces are explicitly historical. Separate
CF-01 task authorizes this implementation, not CF-02/Core-Guarded booking or release.

### Minimal review follow-up F24-01–F24-03

External independent review of `4c7d1a1ed546f0a4992c2ecefcd3af29c00bebfc`:
**APPROVED FOR DEVELOPMENT MERGE WITH DOCUMENTED SCOPE**, with no mandatory
development-merge blockers reported. This follow-up documents the CAS assumption
(F24-01), scopes README P_S requirements to EnhancedMySQL booking (F24-02), and
moves the single POST-CSRF check before preview read/upgrade/profile synchronization
and session writes (F24-03). The focused request regression executes the controller
admission block with a stale profile binding: invalid tokens leave the session and
profile-query count unchanged; valid POST/GET still synchronize, and consumed
states remain unchanged. This is synthetic controller-block evidence, not HTTP.
Finding dispositions are implementation responses **pending targeted independent
verification**, not independent finding closure or Owner acceptance. The new
review HEAD and push/PR CI are recorded in PR #24; Issue #22 checkboxes stay open.

### Configuration and migration contract

One entity-specific Core `const` row,
`MEMBERSUBSCRIPTIONMASSACTION_TRANSACTION_PROFILE`, contains JSON schema version 1,
server family, allowed profile and random revision nonce. Configuration reads use
DoliDB directly, never cached `conf->global`. Core `encrypt`/`decrypt`, table prefix
and entity predicates are retained. No new table, journal or schema migration.

Admin-only setup uses Core authenticated bootstrap plus mandatory CSRF, strict
POST/field validation and server-family selection. The ordinary constant setter's
DELETE+INSERT is unsuitable for revision CAS: use one conditional byte-exact UPDATE
(or first INSERT protected by Core's unique name/entity key), require one affected
row, and never auto-retry a conflict/unclear save. Depth 0 and autocommit=1 are
required for this single-statement config write; no transaction cleanup or global/
session setting change. The private setting is not an activation default/deletion
entry in the descriptor, so activation cannot overwrite a choice or infer fresh
history. Its actual live activation/reactivation behavior remains to be qualified.

**F24-01 — conditional CAS persistence:** independent persistence assumes the
ordinary administrative call path with no already active foreign explicit SQL
transaction. `transaction_opened=0` and `@@autocommit=1` are necessary checks here,
not a complete physical transaction-absence or ownership proof. An explicit foreign
transaction can exist despite both values; a successful CAS statement could then
remain part of that transaction rather than independently persisted. No physical
observer, extra rights, foreign COMMIT/ROLLBACK or alternative persistence is added.
A targeted later real test must verify visibility/durability from an independent
connection on the normal admin path and the negative case with a foreign explicit
transaction at logical depth0/autocommit1. End that injected context only through
its test originator, and do not reinterpret affected_rows as independent commit
proof. **NOT EXECUTED:** this focused real persistence/negative test needs separate
authorization; the in-memory CAS model does not establish it.

No trustworthy fresh-install discriminator is available in the previous module
metadata. Therefore missing MySQL config on fresh/legacy/reactivated/unknown installs
requires an explicit administrator choice; it is never treated as permission to
select a weaker profile automatically. Setup recommends future CoreGuarded for new
MySQL, but denies **all** its bookings now. Explicit EnhancedMySQL preserves the
existing observer; missing/invalid config or P_S failure cannot downgrade it.
MariaDB's unconfigured default remains the existing Snapshot contract. Family mismatch,
unknown profile or malformed config denies booking; no server version scope is widened.

### Confirmation and admission boundary

Server-held state binds actual family/profile, SHA-256 of raw config (including
nonce), feature policy, owner/entity and confirmation revision. A profile save,
including same-profile reselection, changes the revision. Old unconsumed sessions
without binding and changed bindings return to preview with new revision/consent;
processing/done sessions bypass profile reads and remain consumed/readable.
Strict POST, CSRF, expiry, permissions/entity and current batch limit are retained.

The linearization point for **each member** is the fresh config SELECT in
`MsmBookingService::book()`, after Core capability/nonforeign logical-depth checks
and before observer admission/BEGIN. The member admitted under that snapshot retains
its original profile/context even if an administrator saves afterward. The next
member reads current config and stops on mismatch, without starting another BEGIN.
This is an explicit per-member snapshot rule, not global admin/batch serialization.
The batch remains per-member transactional, not all-or-nothing. Direct Booking
service calls now require the same server-owned `_profile` binding; old external
harnesses must supply it explicitly rather than inheriting an unsafe default.

Read-only profile sync and business preview never call physical readiness, BEGIN,
SET or transaction row locks. Confirmation is advisory; only the final book POST
runs readiness, and Booking repeats profile admission immediately per member.
Enhanced readiness diagnostics explain P_S/instrumentation requirements without
exposing raw SQL and without fallback. Existing ownership/isolation/engine/rollback/
completion code is unchanged apart from a family-only discovery accessor. No D-01
SET recipe or Core-Guarded transaction adapter is implemented. Existing financial
paths still require their established profile/tax/entity/rights capabilities.

### Executed evidence and missing qualification

All new `CF01` assertions are synthetic, reusing existing protocol/booking/preview
fixtures with a strict in-memory config/CAS result model. They do not connect to a
DB or bootstrap a real Dolibarr request. Existing business fixtures now supply the
same server profile context as the controller, without bypassing production
admission logic. The dedicated negative tests exercise production config SQL and
admission with missing/wrong bindings, not an always-allow stub.

| Required area | Actual evidence / boundary |
| --- | --- |
| Install/upgrade states | Synthetic fresh/legacy/reactivated/unknown missing MySQL refuses until explicit admin choice; missing MariaDB preserves Snapshot. No live activation history inferred. |
| Config validity and 1142 | Missing/malformed/family mismatch rejects; configured Enhanced P_S-denied model fails with dedicated message, no fallback. CoreGuarded blocks before P_S/BEGIN. |
| Admin security | Synthetic admin/external-user checks, CSRF, method, injected entity/unknown fields, invalid family and non-autocommit refusal; zero unauthorized writes. |
| Concurrent admin writes | Synthetic first-INSERT and UPDATE CAS races preserve the winner; same-profile save rotates revision; stale request is not retried. |
| Binding/races/replay | Wrong owner/entity/revision/family/features, unbound old sessions, changed confirmations and consumed POST replay reject; saved results readable even when config is unavailable. Simulated post-admission admin change lets the admitted member finish, then rejects the next member before BEGIN. |
| Read-only preview | Profile sync with denied P_S emits only nonlocking reads; controller has a single physical-readiness call, solely in book. Existing preview/request/calendar/render/escaping regressions retained. This is not live HTTP trace evidence. |
| Profile non-regression | Real production admission over synthetic SQL + existing actual-Core calendar/API source fixtures: Enhanced and MariaDB contributions pass; CoreGuarded/direct-unbound booking refuses. Existing invoice/Agenda/ownership/R16 runners remain passing with server context. |
| Local T1/T2 | PHP 8.2 lint, standalone/language parity, 188 observer and six loading contracts; eight existing runners on pinned V22.0.5 and V24.0.3 source PASS. Overlapping assertion counts are not unique test totals. Full 21-pair CI is recorded against the final HEAD in the Draft PR. |
| Real T3/T4 | **NOT EXECUTED:** setup GET/POST/rights/CSRF and config CAS against real DB; live upgrade/reactivation, concurrent admin/member requests, real preview/replay and Enhanced/MariaDB contribution/invoice regression on this new source. No separately approved CF-01 installation/deployment or runtime operations were provided. |
| Core-Guarded/D-01 | **NOT EXECUTED / NOT QUALIFIED:** loaded-Core/restricted-MySQL D-01 sequence and contribution booking remain later separately authorized pre-DML gates; all CoreGuarded bookings are intentionally blocked in CF-01. |

No live deployment/symlink, Core, DB/configuration, Documents, SMTP, release artifact
or 71-ID/18-column requirements mapping was changed. CF-01 checkboxes in Issue #22
remain open until independent review and Owner acceptance. Production/release and
later CF-02–CF-05 gates remain open.

## PR #16 documentation follow-up — 2026-11-18

**Current review status: APPROVED FOR MERGE WITH DOCUMENTED SCOPE.** The external
independent implementation/regression/evidence review of
`5f71102cc631c619496d0d6f2e002b97073ae644` is complete. That reviewed HEAD contains
implementation `72bed7fe9f61ec7488bf29a5388f9f1a777c7910` and its original evidence
documentation. This subsequent documentation-only follow-up records later Owner
acceptances; it is not a new independently reviewed implementation or live campaign.
Its new commit SHA and separate PR/push CI results are recorded in PR #16.
PR #16 remains **Draft**, pending the separate Owner merge decision; Issue #8 is open.
The external review conclusion is not a formal GitHub review vote.

| Finding | Current status and scope |
| --- | --- |
| F16-01 | **CLOSED – OWNER ACCEPTED.** The Owner confirms that the originally intended I105 URL reference was an error in preparing the assignment. The actually used `http://<PRIVATE_MARIADB_WEB_ENDPOINT>` / Dolibarr V24.0.3 / PHP 8.2.35 / MariaDB 10.11.19 environment is correct and expressly accepted. No I105 requalification is required. This accepts the target; it does not assert that I105 and this installation are identical. |
| F16-02 | **RESOLVED WITH DOCUMENTED SCOPE – OWNER-CONFIRMED HTTP TEST.** In a later authenticated manual browser run, the Owner reports 100/100 subscriptions created without invoices or email, approximately 6 seconds from booking confirmation to result display. This is a distinct Owner-confirmed HTTP observation, not the earlier automated CLI campaign or an independent reproduction. No new HTTP raw traces, HAR, session-write instrumentation or independent SQL reconciliation of that manual run is claimed. |
| F16-03 | **P3, non-merge-blocking; remains open.** Raw selection IDs are strictly validated before the size check, so extremely large authenticated input still incurs O(N) validation/memory cost before rejection. Member/preview business queries remain avoided. No robustness change in this follow-up. |
| F16-04 | **P3, non-merge-blocking; remains open.** The original archive proves 100 added Agenda events/resources by counts/hashes, but does not independently reconstruct every individual foreign-key assignment. Runtime checks, original evidence and this reconstruction limit remain distinct; no additional SQL check was executed. |
| F16-05 | **P3, non-merge-blocking; remains open.** A limit reduction between persisted session consumption and orchestration admission can leave a safely stopped consumed `processing` run even before business DML. Do not automatically retry, reopen or rebook it; reconcile the outcome first. No recovery-code change or new race test is claimed. |

The review independently checked source/CI/evidence, including 22 runtime file
hashes and the original archive integrity; it did not independently rerun the live
campaign. Original implementation CI (PR44188974734/push44188923649) and reviewed
HEAD CI (PR44190437337/push44190428982) each completed **21/21 PASS**. The formerly
pending push44190428982 is now historical completed success. New documentation-HEAD
CI must be assessed separately; synthetic CI is not a live database/load test.

**Evidence separation:** original automated CLI100: 5.89840 seconds with measured
PHP memory and serialized checkpoints; original automated browser tests: previews,
localized early rejection and a reduced-limit negative POST; later manual Owner
HTTP100: 100/100 success and approximately 6 seconds, confirmation to results.
These observations are not interchangeable. The accepted manual run adds bounded
HTTP booking evidence without retroactively changing the original CLI report or
its independently reviewed traces. The supplied Owner report does not state an
additional test timestamp, browser version or new measured memory value; none is
invented here. The date above is the documentation/acceptance recording date.

No general HTTP-load, replay, parallel-booking, PDF/SMTP throughput, database-version,
production or release qualification follows. The manual run does not extend existing
replay tests to a positive HTTP100 replay campaign, prove all timeout settings or
qualify simultaneous large runs. Prior restrictions and original evidence remain.
This task changes documentation only; no test, configuration or DB operation.

## Issue #8 configurable bulk limit — 2026-11-17

**Historical automated-campaign snapshot:** review and HTTP limitations below describe
that original stage. Later independent review and Owner acceptances are recorded above;
the original measured results and artifacts are unchanged.

Implementation `72bed7fe9f61ec7488bf29a5388f9f1a777c7910`, based on main
`4fdeb862a9a63ffe9126fbb42e95005b049a0ee8`, was tested on actual Dolibarr V24.0.3 /
PHP 8.2.35 / MariaDB 10.11.19-MariaDB-0+deb12u1 / mysqli / InnoDB. The target
was positively identified as MariaDB before any writes. No Oracle-MySQL
integration requalification is inferred from this campaign.

| Group | Result / evidence level |
| --- | --- |
| Module configuration | PASS synthetic: missing/empty/malformed/nonpositive/overflow module values fall back to100; configured25/100/150 below/at/above boundaries; smaller global limit caps selection. Explicit invalid/nonpositive global limit fails closed at zero. Existing activation needs no reactivation. |
| Early entry | PASS synthetic hook subprocess: oversized valid IDs with no accessible fixture objects exit to a count-only notice before member access; no preview evaluation or selected-ID session copy. |
| Security | PASS existing/request regressions: malformed/mismatched IDs, CSRF/replay, reduced stale confirmations, pre-readiness and pre-orchestration rejection without writes or truncation. |
| Real UI | PASS authenticated Chromium158.0.8127.18: 99/100 previews at default100; 101 early rejection in DE and EN with selected/max counts and return link; configured150 preview/151 rejection and25 preview/26 rejection. |
| Real stale confirmation | PASS:26-member confirmation under150, then module configuration reduced to25; direct booking POST rejected, independent24-table hashes unchanged. Only the module constant was changed via Core admin actions and restored to its original absent state in finally. No global setting changes. |
| Real bulk100 | PASS:100 new synthetic members,120 EUR each for2090, contribution-only;100 success results and independently reconciled Core persistence, member ends and standard Agenda rows. No invoice/mail selected. |
| Small regression | PASS real direct transactional Core customer/invoice/Agenda path on one other new member,120 EUR/VAT0, validated invoice and link. PDF remains pending; no generation or mail. Existing preview/booking/invoice runners also passed synthetically. |
| CI | Implementation push44188923649 and PR44188974734 each21/21 SUCCESS at exact72bed7f; six PHP7.4 and fifteen PHP8.2 pinned Core pairs. These are synthetic CI, not a real DB-version matrix. |

The synchronous100-member CLI run measured0.69046s preview,5.19836s booking and
5.89840s total;20 MiB peak PHP memory,4 MiB additional peak and76164 bytes maximum
serialized session state.202 checkpoints serialized state and wrote small evidence
records. This supports practical synchronous contribution-only use in this exact
local CLI profile, below predeclared60s/128MiB-additional stop thresholds. It does
**not** qualify an Apache100-member booking request, web/PHP/proxy timeouts, heavy
third-party triggers, invoice/PDF/SMTP throughput or simultaneous load. Use smaller
batches where deployment limits require it; changing the configured count alone
is not a performance qualification. No automatic splitting/truncation is introduced.

151 new synthetic members were created;100 bulk contributions and one separate
invoice contribution persisted. All original rows across24 monitored business/audit
tables and existing invoice files were preserved. No payments, new PDFs or external
mail. Tests were Owner-executed under www-data/interactive test login because
protected application access remains unavailable to the agent; persisted evidence
and runtime hashes were then independently inspected. No independent external
Issue8 review or merge approval is claimed.

Private evidence set `dolibarr-issue8-20261117` / shared `issue8-20261117` retains
runtime mapping, baseline/prepared fixtures, all202 checkpoints, bulk observed/
result traces, browser result with restoration flag, small-invoice/final snapshots,
and a reproducible report/checksum bundle. No credentials, tokens, raw sessions or
private business contents are public artifacts. The71-ID/18-column matrix and all
historical qualification boundaries remain unchanged; no production/release approval.

## PR #13 post-merge closure and MDB-TEST-01 — 2026-11-17

**Current status: development merge COMPLETE.** PR [#13](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/13)
merged as `aaf1f3786a4c9ea7e1707ad41cb821b0bb271e3d`. Its Git tree is identical
to tested HEAD `0999efb4aafca43d90ce12e38d370662ad0c63a1`; retain that runtime
reference separately from the merge/development baseline. Both independent external
follow-ups are complete and positive (MDB confirmation Owner-reported):
**APPROVED FOR MERGE WITH DOCUMENTED SCOPE**. I107-F01/F02/F03 are **CLOSED**.
External review conclusions are not formal GitHub review votes.

The complete sequence is **I1-05 FAILED (19/20) → FIX01 PASSED (20/20) → I1-06 →
I1-07 findings → I107 correction/requalification → positive I107 follow-up →
MDB-TEST-01 9/9 PASS and positive independent follow-up → PR #13 merged**.
Original failures, CLI-warning evidence, source references and limits below remain
historical facts, not retrospectively changed outcomes.

Real MDB profile: **MariaDB 10.11.19-MariaDB-0+deb12u1 / Dolibarr V24.0.3 /
PHP 8.2.35 / mysqli / InnoDB / entity 1**, at the exact tested HEAD above.
Owner-executed protected CLI and interactive browser programs used new synthetic
business fixtures with real Core/DB operations and independent SQL postconditions.

| Case | Actual real result / scope |
| --- | --- |
| MDB-01 PASS | MariaDB detection, `@@in_transaction`, own BEGIN/COMMIT/ROLLBACK and fresh idle; no Oracle Performance Schema dependency in traced calls. MariaDB continuity remains snapshot-based. |
| MDB-02 PASS | Regular Core contribution, independently stored values and confirmed COMMIT. |
| MDB-03 PASS | Real Agenda ON/OFF contributions: conditional engine gates, standard events and actor links. OFF was process-local `conf.modules`, not persisted module deactivation; no false rejection. |
| MDB-04 PASS | Existing dedicated synthetic customer, validated 120-EUR/zero-VAT invoice, one line and correct entity/customer/subscription links. |
| MDB-05 PASS | Automatically created customer, contribution and validated invoice in one outer transaction. |
| MDB-06 PASS | Controlled exception after actual customer/contribution/validated invoice/Agenda DML, before COMMIT; confirmed rollback and equality of all 24 monitored table hash sets and invoice files. |
| MDB-07 PASS | Two real connections: A succeeds, B waits about 0.888 seconds then skips changed confirmation before any subscription call/business DML; confirmed own rollback, no 1062. |
| MDB-08 PASS | Longer Core history, earlier shorter contribution and consented overlapping extension; stored end remains 2095 then extends to 2097. No new SQL-NULL fixture or direct end-date repair. |
| MDB-09 PASS | Authenticated Chromium preview/confirmation/booking/result, one identical consumed POST replay rejected in the same session; 24-table hashes unchanged on replay. Firefox reads the same real completed result. No invoice/mail selected. |

Nine new synthetic members, ten persisted contributions, two customers (including
the pre-created MDB-04 customer), two validated invoices and twelve Agenda events/
assignments were reconciled. All original rows in the 24 monitored business/audit
tables and physical invoice files were preserved. ECM, payments and bank records
were unchanged. No captured PHP warnings in successful MDB cases. Standard Core
bootstrap initialized `$mysoc`; this does not erase the separate I107 CLI warning.
Chromium 158.0.8127.18 executed MDB-09; Firefox 159.0 inspected its actual result.

Private evidence bundle `dolibarr-mdb-test01-20261117` retains `TEST-MATRIX.json`,
`evidence/*.json`, original no-effect starter STOPs and corrected harness versions,
`RUNTIME-ASSIGNMENT.json`, final report and independent SQL/filesystem evidence.
`MDB-TEST01-EVIDENCE-v1.zip` SHA-256:
`66c7867bc26463faac457e0eb7c83bb1a0274c84c8547401f14ebe31cae57611`.
Separate verification passed ZIP/manifest, 83 checksums and 21 runtime Git blobs;
173 additional read-only trace assertions passed. Those assertions and independent
reviews are not additional live campaigns. No private artifacts are public downloads.

No regression was identified within this profile. No new PDF generation/SMTP,
physical crash/network loss, arbitrary third-party writers, persisted activation
cycle or full role/Multicompany qualification is implied. MDB HTTP used the existing
test administrator. Initial class-load/browser-path harness interruptions preceded
business requests and were reconciled without repeating a booking. Owner statements
on mail routing and absence of SQL triggers were accepted; no new mail configuration,
trigger or backup/restore checks were performed. Requirement mappings remain unchanged.

**I107-FU-01 OPEN — P3 Low, non-merge-blocking:** the diagnostic `isolation` field
after completion/idle probing may describe the latest observed event, not the
completed transaction. Actual ACTIVE admission and safe rollback are unaffected.
No correction or closure of this diagnostic observation is claimed.

The development merge closes the Oracle-MySQL iteration handoff. Exact MySQL
8.4.13 and MariaDB 10.11.19 profiles remain separate; other versions, PostgreSQL
iteration 2 and unexecuted scenarios remain unqualified. Version `0.1.0-dev`, ID
`584620` and production/public release **NOT QUALIFIED / NOT APPROVED** remain.

## I107 security hardening

**Follow-up status — 2026-11-17.** Initial independent I1-07 review of
`902697d510e9e75944e2743dacbff5214f0bebea` concluded **APPROVED FOR MERGE WITH
NON-BLOCKING FINDINGS**: I107-F01/F02 (P2) and stale Draft wording I107-F03 (P3).
That external source/trace review did not execute new runtime tests and is not a
formal GitHub approval or Owner merge authorization. The Owner commissioned the
bounded corrections before deciding on merge. At that historical checkpoint PR #13
was open and **Ready for review**, with targeted confirmation outstanding. The
completed follow-ups and actual merge are recorded in the current closure above.

The retained sequence is **I1-05 FAILED → I105-FIX01 PASSED → I1-06 PASSED →
I1-07 with findings → targeted I107 correction/requalification**. Original reports,
19/20 and 20/20 gates, previous CI and accepted limits are unchanged.

### Bounded implementation and architecture checkpoint

The pre-implementation analysis did not trigger the Owner's architecture stop:
F01 covers a fixed set of regular Core events/two tables; F02 adds an observed
fact and a separate business-admission predicate. No generic trigger scanner,
new transaction controller or change to ownership/continuity/completion states.

- **F01:** Core `ActionsAuto` first checks Agenda and truthy
  `MAIN_AGENDA_ACTIONAUTO_<event>`. Contribution creation reaches
  `MEMBER_SUBSCRIPTION_CREATE`; invoicing can reach `BILL_CREATE`,
  `LINEBILL_INSERT` and `BILL_VALIDATE`. Selected customer creation can reach
  `COMPANY_CREATE`, plus `CONTACT_CREATE` when automatic contact creation is enabled.
  Internal company initialization suppresses `COMPANY_MODIFY`; customer classification
  uses a direct Core update without that trigger. A fresh standard `ActionComm`
  writes `actioncomm`/`actioncomm_resources`; its empty extra-field options do not
  enter the extra-field write path. Custom hooks populating additional data remain
  outside this bounded contract. `checkTables()` checks both engines only when a
  relevant enabled event can occur under the selected options, before BEGIN and
  after **all** member/type/customer/product locks. No metadata read was inserted
  into the protected pre-lock interval.
- **F02:** the same existing own-thread Performance-Schema query now selects
  `ISOLATION_LEVEL`; all expected fields/valid enum values must be present. After
  fresh owned ACTIVE verification, a SQL-free admission predicate requires
  `REPEATABLE READ`. Session defaults cannot replace the actual event, including
  one-shot next-transaction overrides. Other valid levels are configuration errors
  and retain the existing confirmed-own rollback path. Missing/invalid facts follow
  the existing UNKNOWN latch and cannot redeem uncertain ownership. `sameActive`,
  BEGIN ownership, G0 counter probes, rollback-context and completion transitions
  remain unchanged. MariaDB neither queries nor requires this MySQL field. No
  additional observation round trip, production SET or isolation workaround.

### Executed evidence and limitations

Existing synthetic runners cover Agenda module/events off, reachable invoice/
customer/contact options, InnoDB positives, missing/MyISAM tables and initial/final
engine rejection before business APIs. Both MariaDB and MySQL models are covered.
Isolation vectors include all four actual event levels, one-shot/session distinction,
malformed/missing fields, fresh-owned rollback versus unknown identity, invoice
admission and combined Agenda preflight. Existing G0/R16/NULL/request/replay/
post-COMMIT assertions are retained. Local PHP 8.2/V24.0.3 passed: 188 adapter,
369 booking/mail, 236 invoice and 59 request assertions plus all other existing
runners, language parity, PHP lint and whitespace checks. These overlapping counts
are not a unique scenario total or new live MariaDB qualification. The unchanged
21-pair CI on the final correction commit is recorded separately in PR #13 and
`CI-RESULTS.json`, with distinct push/PR run URLs.

| Real case | Actual result / scope |
| --- | --- |
| `I107-isolation` | Seven read-only cases: four next-transaction settings while the session default remains RR, plus three other session defaults. Actual own event read correctly; non-RR rejects before locks/Core/DML; RR reaches deliberate stale-fingerprint rejection without writing. Seven confirmed own rollbacks; business/files identical. |
| `I107-POS-SUB` | Real Core contribution and regular Agenda event/resource creation. |
| `I107-POS-INVOICE` | Real new customer, validated standard zero-VAT invoice and subscription link; active company/invoice Agenda paths verified. |
| `I107-AGENDA-ROLLBACK` | Controlled application fault after real contribution and Agenda insertion, before outer COMMIT; own rollback confirmed and all monitored table/file hashes equal. |
| `I107-CONCURRENCY` | Two physical connections, A success; B waits about 0.826 seconds, then changed-confirmation skip before any subscription/DML, confirmed own rollback, no 1062. |
| `I107-R16-LONG/SHORT` | New longer history then earlier disjoint shorter contribution; unchanged Core retains the maximum end. |

The actual configuration had subscription-create, bill-validate and company-create
Agenda events enabled; bill-create and line-insert were disabled. Contact-create was
enabled but automatic contact creation was off. The tests preserved these settings.
Five new synthetic members/five persisted contributions, one customer/invoice and
seven Agenda events/resources were independently reconciled; all prior business/
audit rows and PDFs were unchanged. No new PDF/ECM generation, payments or mail.

The invoice CLI run emitted a Core `country_code` warning in
`getLocalTaxesFromRate()`: the existing `NOREQUIRESOC` CLI bootstrap leaves the
global seller object unset. Its zero-VAT invoice/amount/link and Agenda postconditions
passed. This test-context limitation is retained; no Core tax fix, new tax-profile
qualification or repeat invoice was introduced. Raw Xdebug argument/object output
is not part of the public documentation or sanitized evidence package.

Negative Engine conversion/missing-table cases are **synthetic**, not a claim of
real MyISAM execution. No existing Core table was converted and no new privilege,
global variable, instrumentation or infrastructure change occurred. Missing/invalid
isolation and lost-identity negatives are synthetic; actual valid isolation-level
rejection is real. Historical I1-05/FIX01/G0, PDF/mail and MariaDB evidence is not
re-executed or broadened by this follow-up.

Private evidence set `dolibarr-db-iter1-i107-fix01-20261117` retains
`PREIMPLEMENTATION-ANALYSIS.txt`, `RUNTIME-MANIFEST.json`, local results, real
inspection/isolation/business/SQL traces, independent postconditions, separate
`F01-ACCEPTANCE.json` and `F02-ACCEPTANCE.json` (ten criteria each), CI evidence,
final report and checksums. Real execution used a hash-pinned runtime overlay;
the final commit was created afterward, with identical runtime contents verified.
I107-F01/F02 are technically corrected/requalified for the same bounded V24.0.3/
PHP 8.2.35/MySQL Community 8.4.13 profile. I107-F03 status wording is corrected;
independent follow-up and the Owner merge decision were separate subsequent gates,
now completed as recorded above. Production/
public release stay **NOT QUALIFIED / NOT APPROVED**; other profiles, arbitrary
hooks/writers, PostgreSQL iteration 2 and prior unexecuted scenarios remain limited.

## MySQL iteration 1

**Historical I1-06 database handoff — 2026-11-17.** The Owner accepted
DB-FIX01-DEC-01–07: FIX01 complete, I105-FIND-01 technically resolved subject to
independent I1-07 confirmation, no full I1-05 rerun, I1-06 documentation/review
preparation authorized, no merge/production/release approval. PR #13 was Draft
at that checkpoint; its later Ready status and I1-07 findings are recorded above.
The bounded conclusion is **I1-05 FAILED → I105-FIX01 PASSED → bounded MySQL profile
requalified**. It does not rewrite any original test status or historical MariaDB
approval. The Phase 4 gate table below concerns its earlier profiles.

### History and tested commits

| Stage | Actual result / evidence level |
| --- | --- |
| DB-P1 / DB-ITER1, 2026-11-16 | Conditional technical feasibility; naive event snapshots failed freshness cases. Not an installed-module qualification. |
| G0, 2026-11-16 | **PASSED – IMPLEMENTATION PLANNING RELEASED**, 15/15 criteria for the composed technical freshness/continuity mechanism; no business-module qualification at that stage. |
| I1-01–04 | Shared observer, Booking/Invoice and authenticated preflight implemented at `40a262589de0c16d5482839800ac3c805a6cb9bd`; local PHP 8.2 synthetic qualification. Initial PHP 7.4 gap was historical. |
| F01 / F02 | F01 **RESOLVED** at `e9c4cac022f9a4b5a1b700e6aea0c26c1ff7f051`: stored authenticated results bypass new-operation readiness without bypassing identity/entity/CSRF/consumption/replay. F02 closed by actual 21/21 CI, including six PHP 7.4 jobs. |
| Original I1-05, 2026-11-16 | **FAILED – CORRECTIONS REQUIRED**, 19/20 criteria; T4-09/I105-AC14 failed. Other recorded passes retain only their measured scope. No correction was made during that campaign. |
| I105-FIX01, 2026-11-17 | **PASSED – CONCURRENCY AND R16 REQUALIFIED**, 20/20 criteria, tested/installed/published correction `d6968c4581ea08169ebc2e8074a959953d76b568`. |
| I1-06 | **PASSED – DOCUMENTATION COMPLETE AND CI GREEN** at `902697d510e9e75944e2743dacbff5214f0bebea`; separate push/PR CI each 21/21. Original Draft handoff was later moved to Ready by the Owner. |
| I1-07 | Initial independent review completed with non-blocking F01/F02/F03; targeted corrections and the subsequently completed follow-up/merge are recorded above. |

F02 CI: [run 43867437518](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/43867437518).
FIX01 CI: [PR run 44040639439](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/44040639439)
and [push run 44040634394](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/44040634394),
each 21/21 at `d6968c4`. These are synthetic adapter/Core/API/PHP regressions:
six PHP 7.4/V22 and fifteen PHP 8.2/V22–V24 pairs, with unchanged Core SHA pins.
They do not establish 21 real MySQL runtime profiles. The new documentation
commit and its separate PR/push run URLs are recorded in PR #13 and the I1-06
completion report; earlier runs are not evidence for that new HEAD.

### Reference profile and transaction contract

Real qualification applies only to Dolibarr V24.0.3, Core
`5c6c7ff6a5112413b1c27221c8d11750afad56f8`, PHP 8.2.35, Oracle MySQL Community
Server 8.4.13, direct `mysqli`, InnoDB, entity 1, `REPEATABLE READ` and
`autocommit=1` outside explicit transactions. Business fixtures were synthetic;
Core business APIs and MySQL connections were real. Invoice scope was standard
EUR/quantity-one/zero-VAT/free-text, Mercure numbering and Sponge PDF.

Performance Schema transaction instrumentation must be active and fresh, with
`PS_CURRENT_THREAD_ID()` and the event/identity/savepoint/release-counter fields
used by `MsmDatabaseState`: `performance_schema=ON`, the `transaction` instrument
and `events_transactions_current`, `thread_instrumentation` and
`global_instrumentation` consumers enabled in the reference profile. Existing
application-schema rights plus the single
`SELECT` on `performance_schema.events_transactions_current` were sufficient;
no `PROCESS`, `threads`/`setup_*` SELECT or extra global admin grant was required.
Observer SQL filters to its own thread, although the table grant is not row-scoped.
Missing/partial/stale/denied observations never imply idle. Idle calibration reads
an existing InnoDB member row; an empty member table is rejected without seeding.

Booking owns one outer DoliDB transaction per member. Every Invoice factory shares
the same DoliDB-bound observer. MySQL binds native handle, connection/server/thread/
event and exact active SAVEPOINT/RELEASE counters; it checks the expected own
COMMIT/ROLLBACK event before obtaining a separate fresh-idle proof. MariaDB retains
`@@in_transaction`, depth and InnoDB checks with **snapshot** continuity, not a
claim of MySQL event continuity. Only a fresh owned open context can be rolled back;
uncertain completion/ownership remains `unknown/stop`, with no retry or compensation.

### I105-FIND-01 and the targeted correction

Original T4-09's losing process acquired the member lock after the winner's COMMIT,
but ordinary post-lock validation retained an older view and reached the contribution
INSERT. MySQL 1062/unique-index rejection and confirmed rollback prevented a stored
duplicate; they did **not** satisfy the required pre-write business revalidation.
No actual duplicate or R16 corruption was observed in that original campaign.

The FIX01 prefix experiment isolated the first snapshot-effective statement in the
tested server: `SELECT ENGINE AS engine FROM information_schema.TABLES ...`.
BEGIN, identity/event queries and the full G0 after-BEGIN checkpoint did not reproduce
the stale view; adding that first engine query did. A later `FOR UPDATE` could see
newly committed rows while ordinary SELECTs still missed them. This is an observed
MySQL 8.4.13 boundary, not a universal claim about all metadata implementations.

FIX01 performs engine preflight before BEGIN, then acquires member → member type →
optional customer/product locks, then repeats engine checks **after all those locks**
and before actor reload, preview and Core business reads. Core's ordinary history
reads and R16 end calculation therefore share the post-wait view. Lock order, G0
checks, fingerprint/overlap-consent policy and Core-only writes remain intact.
No extra production BEGIN/COMMIT, isolation change, blanket locking preview or
independent end-date recalculation was introduced.

### Real targeted qualification

| Case | Executed scope / outcome |
| --- | --- |
| FIX-D01 | Same start: A success; B genuinely waits, then `skipped/MSMChangedConfirmation` before any subscription call or DML; own rollback confirmed, no 1062, one persisted contribution. |
| FIX-D02 | Different overlapping starts: old consent does not authorize the new overlap; B skips before Core/DML. |
| FIX-D03 | Disjoint fixed-amount periods: both serialize successfully. This outcome was declared before execution because displayed/source fingerprint inputs remain unchanged; full history/datefin alone are not consent inputs. |
| FIX-D04 | Controlled failure after A's locks: confirmed own rollback before Core; B waits and succeeds. Original timeout case retained, not rerun. |
| FIX-E01 | New longer period first, then earlier disjoint shorter period; both succeed, Core preserves the longer end and B's ordinary post-lock history sees A. |
| FIX-E02 | Shorter period first, then later disjoint longer period; both succeed with current-history evidence, not only a coincidentally correct final end. |
| FIX-E03 | Two new historical contributions stay unchanged; current consent permits A, while B's now-stale overlap consent is rejected. Core MAX end retained. |
| FIX-E04 | **Read-only** recheck of the existing legacy SQL-NULL fixture through the corrected locked read path; history/MAX, Core-fetched object and storage agree. Zero new subscription/end-update calls or NULL mutations. Historical real T3-15 and current synthetic actual-Core end-update tests remain distinct. |
| FIX-E05 | One real validated Core invoice/customer path succeeds; rejected competitor performs no subscription/customer/invoice DML. Direct transactional API only: PDF remains pending, no new PDF generation or mail. |

Independent connections, identities/completions, SQL order, bounded waits and before/
after row/file hashes support these results. All initial business/audit rows and
existing PDFs were preserved. The D01 coordinator originally stopped on an overly
broad no-Agenda assertion despite the correct A/B outcome. Separate read-only
reconciliation verified the normal Core Agenda event/assignment for the winner;
a first diagnostic's wrong subscription-link assumption was corrected to the
Core's explicit member mapping. Both STOP records remain; no D01 booking was repeated
and no second product correction was needed.

Original I1-05 additionally retains real serial subscription/invoice/customer/zero-fee,
R16/NULL, nested transaction, pre-COMMIT rollback, foreign/replacement/own-connection
loss, withheld-COMMIT-acknowledgement, batch-stop and bounded timeout evidence.
Physical own-connection closure and deliberately withheld acknowledgement are
specific fault mechanisms, not blanket network/packet-loss qualification. Its real
PDF/ECM and controlled post-COMMIT PDF-failure checks remain separate from FIX01's
no-generation scope. Its consumed HTTP replay and F01 observation-outage/result
checks, followed by real Chromium/Firefox completion, retain their exact boundaries.
No external SMTP delivery occurred in I1-05/FIX01. Historical MariaDB C3.3-D owner-
confirmed delivery and V22 Core diagnostic 121 despite receipt do not qualify MySQL
SMTP delivery. PDF/mail remain permitted only after confirmed successful COMMIT.

### Evidence index and remaining gates

The private evidence sets are **not** public repository artifacts. Their report,
acceptance, findings and checksum/manifest files identify the actual executions:

- `dolibarr-db-iter1-20261116`: feasibility/architecture/conditional-plan reports,
  `DECISION.json`, application results, Owner controls and `checksums.sha256`.
- `dolibarr-db-iter1-g0-20261116`: `FINAL-REPORT.txt`, `ACCEPTANCE.json` (15 criteria),
  `TEST-MATRIX.json`, Owner windows, restoration and `checksums.sha256`.
- `dolibarr-db-iter1-impl-20261116`: `IMPLEMENTATION-REPORT.txt`, `ACCEPTANCE.json`,
  final synthetic results, self-review, historical inputs and `checksums.sha256`.
- `dolibarr-db-iter1-followup-20261116`: `FINAL-REPORT.txt`, `FOLLOWUP-STATUS.json`,
  `github-ci.json` (F01/F02) and `checksums.sha256`; environment preparation is
  distinct from later business qualification.
- `dolibarr-db-iter1-i105-20261116`: `FINAL-REPORT.txt`, `ACCEPTANCE.json`,
  `TEST-MATRIX.json`, `FINDINGS.json`, `MANIFEST.json`/`checksums.sha256` and
  `I105-EVIDENCE-v1.zip`. Original 19/20 and T4-09 FAIL remain immutable.
- `dolibarr-db-iter1-i105-fix01-20261117`: `FINAL-REPORT.txt`, `ROOT-CAUSE.txt`,
  `REAL-CONCURRENCY-RESULTS.json`, `R16-QUALIFICATION.json`, `TEST-MATRIX.json`,
  `ACCEPTANCE.json` (20/20), `CI-RESULTS.json`, `MANIFEST.json`, `checksums.sha256`,
  `INTEGRITY-RESULT.json` and `I105-FIX01-EVIDENCE-v1.zip`. ZIP structure and all
  148 inner checksums were independently verified; original 251 I1-05 checksums
  remained unchanged. Original and follow-up packages are separate.

No new requirement or mapping is introduced: `requirements.csv` stays byte-identical
(71 IDs, 18 columns including all historical fields). This evidence index distinguishes
test levels; it does not expand a requirement's PASS beyond its measured aspect.
Unexecuted variants retain their original NOT EXECUTED status, including physical
packet-loss/half-open networking, arbitrary administrative races, optional deadlock,
foreign writers ignoring member locks and unqualified custom hook/trigger effects.
MySQL 8.0/other versions or editions, V21/future Cores, other real PHP/Core profiles,
proxies/session multiplexing/reconnect and PostgreSQL are not generally qualified.
PostgreSQL remains iteration 2. Checkpoints are endpoint evidence, not continuous
tracking, crash recovery or an exactly-once guarantee. New MySQL SMTP delivery,
full browser/role/Multicompany matrices and broad performance qualification remain
NOT EXECUTED; historical approvals are not transferred to these scenarios.

The Owner accepts the bounded combined I1-05/FIX01 qualification. At the historical
I1-06 handoff, independent I1-07 and merge decisions were pending; I1-06 itself
performed no review, real tests or installation changes. The later initial review
and targeted correction/follow-up status are recorded above.
Version `0.1.0-dev` and temporary module ID `584620` remain unchanged;
production/public release remain **NOT QUALIFIED / NOT APPROVED**.

## Post-P3 PR11 review correction addendum — 2026-11-15

P3 remains formally closed: **ACCEPTED WITH DOCUMENTED LIMITATIONS**, originally
qualified at `a1843e14c71c89f7148aa2e8f6c5352409bca7b1` and documented at
`17d9b10156538921b2b707cbfef849edc66d24b1`. The later independent PR11 review
identified PR11-F01 (merge-blocking nullable R16 observation) and PR11-F02
(incomplete abstract inheritance checks). Earlier statements about no confirmed
implementation defect describe the then-current P3 assessment, not this later review.

- **PR11-F01:** retain the positive Core call result independently of the nullable
  observed end. Normalize only SQL/Core `NULL` and `''` to the absent-end state;
  compare observer, timestamp object and stored end. Missing/failed calls and
  contradictions still fail with confirmed rollback. All 15 exact historical SQL
  postconditions remain; only Core calculates/writes the end (E2, D-P1-02).
- **PR11-F02:** before declaring an extension, enumerate abstract obligations and
  require a module implementation with compatible checked shape. The hook adapter
  supplies its constructor and both hook methods. Implementable abstract bases
  remain allowed; extra obligations disable the hook or reject the member adapter
  before PHP can raise a declaration fatal. Admission policy remains unchanged.
- **PR11-DOC01:** VAT policy comment now references D-P1-01; no VAT logic change.

Added existing-runner regressions execute the actual pinned Core member-end method
with nullable in-memory histories: latest-start NULL, MAX with mixed/all-empty ends,
NULL/empty equivalence, missing/failed observations, contradictory object/observer/
storage and confirmed rollback. They also execute under the existing unknown-label
subprocesses. Isolated declaration subprocesses test supported abstract hooks and
unsupported extra hook/member obligations. Existing positive R16 cases remain.

Follow-up evidence is separate from the immutable P1/P2/P3 packages. The correction
commit and its automatic 21-pair CI results are recorded in PR #11 and the separate
private `dolibarr-pr11-followup-20261115` evidence set, after CI completion. This is
post-P3 correction regression evidence, not a retroactive rewrite or new general P3
qualification. Original 78 P3 checksums and logs remain unchanged.

Real DB/business/PDF/SMTP/browser tests: **NOT EXECUTED** in this correction task.
V21/future Cores, broader installed profiles, production and public release remain
unqualified. Existing risk limits and historical real evidence remain unchanged.
At that historical correction checkpoint, PR #11 preceded PR #10 by Owner
decision; independent follow-up review and merge approval were still pending.
That retained intermediate status is separate from the current PR #13 handoff.

## P3 formal closure and P3-DOC01 — 2026-11-15

Owner decision: **P3 SYNTHETIC CROSS-VERSION QUALIFICATION – ACCEPTED WITH
DOCUMENTED LIMITATIONS**. P3 is closed on implementation HEAD
`a1843e14c71c89f7148aa2e8f6c5352409bca7b1`; all 21 CI pairs passed in
[run 43731843114](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/43731843114).
At that P3 closure checkpoint, no confirmed implementation defect or real P4 test
need had been found for the agreed synthetic scope; independent overall code review
and merge decision were still pending. The later PR11 findings above remain separate.

The [public qualification guide](p3-qualification-summary.md) records all 15 Core
pins, regular CI test links, precise supplemental P3 program/result identifiers
and the PHP-8.2-only supplemental scope. P3-DOC01 is corrected in a separate
private addendum with its own checksums; the original completed P3 package and
78 checksums remain unchanged. No private evidence is imported into the repository.

This follow-up is documentation-only and must have its own CI attributed to its
new HEAD. It does not change or rerun the P3-qualified implementation. The earlier
P2/P2-F01 "P3 pending" entries below are historical intermediate statuses superseded
by this closure, not erased. Existing real baselines, 71 requirements, risk limits,
module metadata and production/release restrictions remain unchanged.

## P2-F01 R16 evidence boundary — 2026-11-15

The numeric interval used in the initial P2 implementation also classified
unexamined releases (22.0.6, 23.0.5, 23.1.0) as historical R16 contracts. P2-F01
replaces that inference with a frozen catalog of the 15 P1-source-verified releases.
The catalog selects independent historical SQL postconditions, never admission.
No future patch/minor/major needs to be added: every other admitted label uses
actual Core return/object/stored-end consistency, without an inferred old policy.
Admission >=21.0.0 and its lack of an upper bound remain unchanged. Actual Core
remains the only end-date writer; no transaction/rollback/unknown-stop code changed.

Regression coverage: all 15 historical property selections, 24.0.1/24.0.2 boundary,
and unknown 22.0.6/23.0.5/23.1.0/24.0.4/25.0.0 labels. Separate booking processes
reuse pinned Core methods and in-memory transaction fixtures. A clearly synthetic
novel Core policy whose observer/object/storage agree commits for unknown labels;
the same deviation still fails independent SQL checks for the 15 historical labels.
Stored-end disagreement still fails and rolls back in both modes. These are not
integration tests of unreleased or unexamined Cores. CI retains all 21 pinned pairs;
P3 evaluation, arbitrary future algorithm correctness and historical real-evidence
limits remain unchanged. No new real tests, qualification or merge approval.

## P2 cross-version implementation — 2026-11-15 (P3 qualification pending)

Baseline main `5b2ce06124cd2d839a0b0005f1edc582cccbb39b`; work is isolated on
`feature/cross-version-compatibility`. Owner E1–E5 and D-P1-01/02/03 supersede the
historical exact-patch admission policy for this change, not historical results.

- **Admitted:** >=21.0.0, no upper bound/exact patch/SHA allowlist. Capability
  failures are explicit; unsupported hook inheritance disables the adapter safely
  before class declaration. V21 and future Cores are not thereby qualified.
- **Source analyzed:** all 15 official V22.0.0–22.0.5, V23.0.0–23.0.4,
  V24.0.0–24.0.3 pins from private P1 reports. Independent R16 SQL checks remain
  only for the 15 explicitly source-verified releases, with the 24.0.2 switch; all successful operations
  also require actual parent update success and observer/object/stored-end equality.
  Beyond analyzed versions, only the actual Core observation/invariants are used;
  unknown numbers do not cause rejection or imply verified future semantics.
- **Locally synthetic tested:** PHP 8.2 against all 15pinned Core sources, six existing
  runners per target (90 runner invocations, overlapping included assertions are
  not summed). Actual Core calendar/update_end_date/VAT/price functions execute
  with inert in-memory adapters; invoice/PDF/mail services use existing isolated
  doubles. No real DB/config/bootstrap, PDF generation or SMTP was executed.
  `run.php`:115 contracts/language parity; Node loading:6 assertions; PHP lint and
  whitespace passed. The future-label 99.0.0 booking subprocess on real V24 methods
  passes and remains synthetic/unqualified. Typed/final inheritance failures,
  missing methods/PDF files and changed stored invoice VAT/amount fail safely.
- **CI configured:** 21 unique pinned pairs, PHP 8.2 on all 15 tags plus PHP 7.4 on
  six V22 tags. Each actual GitHub result must be reported by commit/job; this
  configuration statement is not a CI-pass claim. P3 independent evaluation is open.
- **Real integration:** only the historical bounded V22.0.5/V24.0.3 campaigns below.
  No P2 live tests. The 71 requirements, 53 partial V24 rows/18 dedicated live gaps and
  private V2 artifacts remain unchanged; no blanket 15-version runtime PASS.

Uniform VAT policy is the literal set empty/unset, `0`, `defaultforfoundationcountry`;
actual Core determines the foundation-country rate. Unsupported values are rejected
on V22 as well as V23/V24, intentionally removing prior silent-zero behavior.
Subscription-only booking, existing strict invoice profile, transaction ownership,
rollback/unknown-stop, CSRF/permission/entity/revision guards and post-COMMIT
PDF/mail orchestration remain. Explicit PDF models are never replaced; empty
configuration selects available crabe then sponge or fails on missing capability.

Residual risks: unreviewed future contracts cannot be universally proven by
reflection/observer checks; arbitrary third-party handlers, external writers,
physical connection loss and general crash recovery remain outside qualification.
P3 must evaluate all 21 CI combinations and decide from concrete failures whether
any separately authorized P4 real boundary check is needed. No new live test is
requested solely because a tag number differs. Module 0.1.0-dev/temporary ID 584620
and production/public release **NOT QUALIFIED / NOT APPROVED** remain unchanged.

The remaining document is the retained historical PR4/PR6 baseline. Its exact-target
and tested-commit statements describe those campaigns and do not qualify P2.

## Current gate status — 2026-11-15

| Gate | Current status / provenance | Scope / next step |
| --- | --- | --- |
| G2 | **FINAL APPROVED – PASS WITH DOCUMENTED LIMITATIONS** | Completed for the bounded Dolibarr V22.0.5 profile below |
| G3 | **APPROVED – PASS WITH DOCUMENTED LIMITATIONS** | Completed for the controlled V22 PDF/mail profile below; SMTP status uncertainty retained |
| G4 | **PASS – OWNER ACCEPTED** | Owner-reported manual acceptance on 2026-11-12; subsequent targeted UI regressions passed |
| V24.0.3 | **REAL C3 CAMPAIGN COMPLETE — LIMITED PROFILE** | Development merge COMPLETE (`96f870e`); final review APPROVED FOR MERGE – ALL CONDITIONS FULFILLED; OD-PR6-01/02 fulfilled; bounded profiles only |
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
the current gate table, the C3 section and the G2/G3/G4 closure sections supersede
earlier pending-gate statements only within their expressly tested scopes.
PR6-01–PR6-03 are closed by independent follow-up, as reported by the owner;
that earlier follow-up is distinct from the final conditional external review below
and is not a formal GitHub review assertion.

## PR #6 post-merge development closure — 2026-11-15

[PR #6](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/6)
merged on **2026-11-15**, merge commit
**`96f870e789eb26b6eb8a6b8eeb0c79812018b0ab`**. This is the completed
V22.0.5/V24.0.3 compatibility development baseline for further work on `main`.
The final independent external follow-up reviewed documentation closure
`9897c91a36631c3066ad93c4d3a29b86232b8d0e` and concluded
**APPROVED FOR MERGE – ALL CONDITIONS FULFILLED**, as reported by the owner.
**OD-PR6-01 and OD-PR6-02 are fulfilled and closed** for this bounded development
merge. No confirmed blocking implementation finding remained. This external
review is not represented as a formal GitHub approval.

Post-merge [CI run 43660371617](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/43660371617)
passed all three jobs on that merge: **PHP 7.4/V22.0.5**, **PHP 8.2/V22.0.5** and
**PHP 8.2/V24.0.3**. These are synthetic CI checks, not repeated real integration.

Historical real V24 runtime reference remains
`5a31d4c8d9cfe6733f59efaef430799df60e1902`, distinct from the merge and this later
documentation-only update. The private `PR6-C3-Review-Evidence-v2.zip`, recovered
C3.1 chat plan and their provenance remain separately referenced below, not
published or modified. The **71 requirements, 53 bounded V24 partial-evidence
rows and 18 dedicated live scenarios NOT EXECUTED** remain unchanged. Accepted
plan deviations, missing originals, residual risks and all profile limits remain.

The development compatibility work is complete only for the documented exact
V22.0.5/V24.0.3 profiles. Version **0.1.0-dev**, temporary module ID **584620**,
release gates and **production/public release NOT QUALIFIED / NOT APPROVED**
remain unchanged. No tag, release, new business test or general version approval
is implied by this merge or its documentation closure.

## PR #6 final external review and owner decisions — 2026-11-15

**Historical pre-merge decision record.** The pending-merge/review-transition
statements in this section describe that intermediate stage and are superseded
by the post-merge closure above; original decisions and evidence limits remain.

The owner reports the independent external verdict **APPROVED FOR MERGE WITH
EXPLICIT CONDITIONS** on `eb87e181e0c83fc3e9b8a24139c441e8e62528e3`.
No confirmed blocking implementation defect was identified. This is an external
review reported by the owner, not a formal GitHub approval. **PR6-FINAL-01,
PR6-FINAL-02, PR6-FINAL-03 and PR6-FINAL-04** are the final review finding references;
the conditional documentation disposition is recorded through OD-PR6-01/02 below.
No additional per-finding technical conclusions or new test results are inferred.

### OD-PR6-01 — ACCEPTED (owner decision, 2026-11-15)

For a bounded development merge to `main`, the owner accepts the documented
C3.1 plan deviations using existing V22 evidence, synthetic regressions and real
V24 partial evidence. In particular:

- C32-08 covers the member-19 rollback only; the planned successful member-10
  row and mixed V24 success/failure batch were **NOT EXECUTED**.
- C32-07 lacks final negative booking POSTs and the live unvalidated-member-9
  case. Role filtering/direct access checks do not become those missing tests.
- Missing-overlap-consent rejection remains an isolated guard check, not a
  negative final HTTP booking POST. The original replay was unavailable; B-R1
  later used member 18/customer 3, a 2036 period and invoice/PDF instead of the
  planned member-20 subscription-only 2034 case, with mail off.
- The V24 browser matrix is limited: 1440/1024 geometry and retained live flows
  do not establish the planned 768/390, complete DE/EN, no-JS, keyboard/reset or
  manual BFCache acceptance. Real success views and synthetic error views remain
  distinct. V22 owner acceptance is not transferred to V24.
- Separate C3.3-D proves one invoice-mail transport with owner-confirmed receipt;
  the without-invoice SMTP variant and full language/handler matrix remain untested.

The authoritative matrix is unchanged: **53 bounded V24 partial evidence rows**,
**18 dedicated live scenarios NOT EXECUTED**, all 71 IDs and historical fields
retained. No original PASS is expanded and no missing scenario becomes executed.

### OD-PR6-02 — ACCEPTED (owner decision, 2026-11-15)

For the same bounded development merge, the owner accepts:

- **G02:** contextual Core/module attribution without original digests for every run.
- **G05:** actual SMTP transport evidence plus **owner-confirmed** receipt and PDF;
  no independent agent mailbox or received-MIME inspection is asserted.
- **G06:** retained PDF/HTTP/ECM/hash records without independent access to all
  confidential original PDF binaries.

Acceptance does not supply missing originals retrospectively. Remaining
provenance limits and original records stay visible.

### Recovered planning and private review package

G01 is **FOUND_AS_CONTEMPORANEOUS_CHAT_ATTACHMENT**: the owner supplied the complete
C3.1 chat planning attachment identified as contemporaneous 2026-11-13. The private
`c31_testplan.md` archive is byte-identical to that attachment. Exact original
transmission time is unavailable; the filename and local download times do not
prove it. A standalone original local-development plan file remains unestablished.

The private **`PR6-C3-Review-Evidence-v2.zip`** contains sanitized evidence,
provenance, checksums and the 17-case `plan-vs-actual.md`/CSV/JSON comparison. Its
manifest, ZIP structure and all 166 checksum entries were independently validated.
It and confidential originals are **not published in this repository**; V1 remains
unchanged. Planning is not executed business evidence or new test authorization.

### Documentation delta and remaining gates

This subsequent documentation-only closure follows reviewed HEAD `eb87e181`.
The external reviewer has **not** been represented as having reviewed this new
commit. Its separate CI must pass PHP 7.4/V22.0.5, PHP 8.2/V22.0.5 and
PHP 8.2/V24.0.3 before PR #6 is moved to Ready for review.

Both owner decisions apply **only to consideration of a development merge into
main**. The actual merge decision remains outstanding. No merge, production,
release, public distribution or general version/profile approval is granted.
Temporary module ID, development version, all unexecuted infrastructure/business
scenarios and existing release restrictions remain unchanged. No new business,
browser, PDF, SMTP or fault-injection test accompanies this documentation update.

## C3 V24.0.3 integration campaign (2026-11-14)

**Historical campaign snapshot (2026-11-14).** Pending-review and missing-plan
statements in this retained section describe that date. The 2026-11-15 closure
above supersedes their current status, without changing historical results.


**C3.2 and C3.3 complete — PASS in the stated profile; ready for final independent
V24 review, not merge, release or production approval.** C3.1 supplied planning and
readiness boundaries; this update only reconciles existing evidence and runs no
business, browser, PDF, fault or SMTP tests.

### Exact targets, profile and provenance

- **D24:** Dolibarr V24.0.3 Core `5c6c7ff6a5112413b1c27221c8d11750afad56f8`.
- **M24:** campaign module HEAD `5a31d4c8d9cfe6733f59efaef430799df60e1902`.
  All rows below use **D24 / M24**. The present documentation/comment-only commit
  does not replace this tested runtime reference. Some original per-case logs do
  not embed both SHAs: attribution uses the pinned campaign baseline, existing
  preparation/continuity records and the retained unchanged implementation, not
  a fabricated retrospective per-run source digest.
- Real runtime: PHP 8.2.35, MariaDB 10.11.19, mysqli/InnoDB, entity 1, synthetic
  members; standard EUR invoices, quantity 1, VAT setting `0`, no configured
  product/service, `invoiceonly`, Mercure numbering, Sponge PDF. No payment created.
- Automated browser evidence: Chromium 158.0.8127.18 and Playwright Firefox 159.0.
  The later true result view uses the original authenticated session, not a fixture.
- C2.1 C1-01–C1-05 and PR6-01–PR6-03 corrections are complete. All three jobs on
  campaign HEAD M24 passed in [PR run 43506801577](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/43506801577):
  PHP 7.4/V22.0.5, PHP 8.2/V22.0.5 and PHP 8.2/V24.0.3. CI remains synthetic;
  it does not itself reproduce the real C3 cases.

### Existing evidence index

The named bundles below are **private local evidence**, not repository downloads.
Only sanitized results/technical test identifiers are published here; no credentials,
recipient addresses, sessions, document contents or raw dumps are imported.
The requirement matrix remains the sole authoritative requirement-to-test mapping.

| Bundle | Existing artifact location (bundle-relative) |
| --- | --- |
| AB | `dolibarr-c32-ab-evidence-20261113/`: `completion-20261114.json`, `access-denial-followup.json`, `C32-03-completion.json`, `C32-04-05-06-completion.json`, `C32-05-06-completion.json`; corresponding original result/reconciliation files retained |
| TX | `dolibarr-c32-d-20261114/completion.json`; saved `c32-d-20261114/C32-08-result.json` and `C32-09-result.json` |
| PC | `AB/c33-20261114/C33-final.json`, initial `20261114T172218Z/final.json`, separate `20261114T172541Z/final.json`; `c33-20261114/A-adapter-interim-20261114171712.json` |
| R1 | `AB/c33-b-r1-20261114/C33-B-R1-final.json`, `result.json`, `completion-read-only.json`, real masked Chromium/Firefox result screenshots; `c33-b-r1-files-20261114.json` |
| MAIL | `AB/c33-d-20261114/C33-D-final.json`, `owner-delivery-confirmation.json`, `independent-read-only.json`; saved `c33-d-20261114/result.json` |

`AB/...` abbreviates the AB bundle root. Saved adapter/transaction/filesystem outputs
outside AB use the existing shared test-result directory, not a public artifact URL.

| Test / result | Operation / relevant requirement aspect | Core / module | Mode / existing evidence | Essential limit |
| --- | --- | --- | --- | --- |
| C3.1 COMPLETE (owner-reported planning) | Test planning/readiness; not a business PASS | D24 / M24 campaign references | Readiness carried into AB `completion-20261114.json` | Standalone original C3.1 plan artifact was not located in the inspected campaign bundles; no reconstructed raw plan or execution claimed |
| C32-01 PASS | Member-list action, selection, preview; C01-01/C07-01 | D24 / M24 | Authenticated Chromium, AB `access.json` plus completion | Normal entry only, not malformed/maximum-size selection proof |
| C32-07 PASS | Role filtering/direct wizard access; C01-06/C07-01 | D24 / M24 | Separate restricted sessions, AB `access-denial-followup.json` | HTTP 200 contains localized denial; no negative final booking POST or full advanced-permission matrix |
| C32-02 PASS | Subscription-only Core booking/end; C04-01/02/09 | D24 / M24 | Real HTTP/SQL; AB `completion-20261114.json` | One-day case, 21-table comparison; no invoice or independent filesystem inventory |
| C32-03 PASS | Existing customer, validated invoice/link/PDF; C05-02/03/07/08/09 | D24 / M24 | Real HTTP/SQL/PDF; AB `C32-03-completion.json` | Original result anchor not separately captured; target card verified; R1 later closes the actual-result-link gap |
| C32-04 PASS | Explicit new-customer consent and invoice; C05-03/04 | D24 / M24 | Real HTTP/SQL/PDF; AB `C32-04-05-06-completion.json` | Missing-customer negative preview only; no new contact, shared entity or classification matrix |
| C32-05 PASS | Zero-EUR invoice/line/link/PDF; C02-02/C05-05/10 | D24 / M24 | Real HTTP/SQL/PDF; AB `C32-05-06-completion.json` | Standard unpaid zero invoice in this profile only |
| C32-06 PASS | Consented overlap and maximum member end (R16); C02-13/C04-01 | D24 / M24 | Positive real HTTP/SQL; same AB completion | Missing-consent rejection is a separate isolated production-function check, not a negative booking POST; no competing writers |
| C32-08 PASS | Owned rollback after real invoice validation; C04-03/07/08/C05-11 | D24 / M24 | Real CLI/DB, process-local injected fault; TX | 22-table/observer/file comparison; no PDF/mail call; one controlled fault point, not all external effects |
| C32-09 PASS | Ambiguous acknowledgement and batch stop; C04-04/08/C06-06 | D24 / M24 | Real CLI COMMIT, adapter withholds success; TX | Contribution persisted, original module result remains `unknown`; next row skipped, no PDF/mail; **no physical network outage** |
| C3.3-A PASS | Canonical PDF/ECM/attachment identity; disabled mail; C05-09/C06-01/05 | D24 / M24 | Actual PDF/mail adapters, authenticated downloads and anonymous denial; PC | Wrong-invoice descriptor rejected; selected mail disabled with/without invoice; downstream hooks/transport NOT EXECUTED here |
| C3.3-B-R1 PASS | Exact consumed authenticated POST replay; C03-09/10 | D24 / M24 | One real invoice booking, one byte-identical replay in same session; R1 | Original token/revision reused; 22 tables and physical PDFs unchanged on replay; **mail was not selected**; no crash/session-loss or mail-enabled replay claim |
| C3.3-C PASS within profile | Preview/options/overlap/confirmation/revision/access/results; C03/C07 named portions in matrix | D24 / M24 | Real Chromium/Firefox read-only flows, PC; actual completed result/link in both browsers, R1 | Initial unavailable-result fixtures remain synthetic; R1 supplies new real evidence, not retroactive reclassification; no new V24 manual BFCache/keyboard acceptance |
| C3.3-D PASS | Standard mail adapter/dispatcher/SMTPS and PDF receipt; C06-02/03/04/05/07 | D24 / M24 | Existing contribution/invoice, exactly one actual send, MAIL; receipt/hash/content **owner-reported** | No new booking. Core SMTP `250`, no application errors. Real dispatch with **zero additional hook handlers**, not a third-party hook test. No agent mailbox/raw received-MIME audit |

### Integrity, historical stops and retained limits

Later business/mail/replay comparisons covered 22 business tables and the known
PDF inventory; C32-02 retained its own 21-table scope. C3.3-D left the completed
dataset unchanged: 36 contributions, 10 invoices, 7 customers, 10 ECM rows, no
payments and the preexisting one bank row unchanged. This is a historical test
snapshot, not an assertion about every future local database state.

C3.3-D found mail unexpectedly enabled before preparation, secured it, then opened
an owner-forced-only test window for six seconds. It restored the required safe
`MAIN_DISABLE_ALL_MAILS=1` and the previously absent `MAIN_MAIL_FORCE_SENDTO`;
independent SELECT checks confirmed both. The actual generic configured message
has no period/amount tokens; those values were owner-verified in the matching PDF,
not invented as text fields. No template change or new template-development task.
V22 G3's diagnostic 121 remains historical uncertainty despite confirmed receipt;
the single V24 C3.3-D send had `transport`, SMTP `250` and no error codes.

Original NO-GO/selector/localization/Core-normalization and cleanup failures remain
in the local history. Later separately named, read-only reconciliations resolve
only their stated points: no hidden automatic rebooking/resend. The initial C3.3-B
could not reuse its closed session; it remains NOT EXECUTED in its original report.
R1 independently closes that gap through one newly authorized positive operation
and its exact consumed replay. Its post-test Playwright cleanup error occurred
after six persisted PASS checks; separate read-only reconciliation confirmed the
unchanged completed state. No successful transport is relabeled failed because of
a later harness issue. The missing standalone C3.1 raw plan and absent per-case
source digests remain evidence limitations, not newly generated artifacts.

**Still NOT EXECUTED / NOT QUALIFIED:** physical connection loss, exhaustive
crash/session-loss recovery, non-cooperating external writers or real V24 lock
contention, foreign outer transactions, arbitrary custom hook takeover/failure,
live PDF/SMTP failure injection, full advanced-permission/expiry/selection-bound
matrices, Multicompany/shared entities, additional currencies/taxes/products,
packaging/stock, alternative PDF/invoice profiles and other Core versions/patches.
No mail-enabled consumed-run replay was performed: R1 is mail-off and D is a
separate one-call transport test. No Exactly-once, durable journal, broad
crash-recovery or production guarantee. V24 final independent acceptance is
**PENDING**; production/public release remain **NOT APPROVED**. Temporary ID
`584620`, version `0.1.0-dev`, minimum Core version and all release gates are unchanged.

## C2.1 compatibility corrections (2026-11-13)

**Historical C2.1 execution snapshot.** Pending-review and NOT EXECUTED statements
in this section describe that stage; the current C3 status above/below supersedes
them only for the subsequently tested profile. E4 remains the original C2.1 grant,
not authorization for the later separately approved real tests.

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
`php test/unit/<runner>.php`. V22 used `<PRIVATE_OPERATOR_HOME>/dolibarr-bulk-test`; V24 used
`<PRIVATE_MARIADB_INSTALLATION>`. The final sequence per Core was `invoice`,
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
are retained locally in `<PRIVATE_OPERATOR_HOME>/dolibarr-c21-validation-20261113/results.json`.
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

Historical correction/CI report; the owner subsequently reported all three findings
closed by independent follow-up. Final independent V24 acceptance remains separate.

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
in `<PRIVATE_OPERATOR_HOME>/dolibarr-c21-validation-20261113/pr6-followup-local.json`.
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
| `DOLIBARR_TEST_ROOT=<PRIVATE_OPERATOR_HOME>/dolibarr php test/unit/booking.php` | PASS — 387 preview/calendar assertions and 87 booking/mail assertions |
| `DOLIBARR_TEST_ROOT=<PRIVATE_OPERATOR_HOME>/dolibarr php test/unit/maildelivery.php` | PASS — 17 mail-adapter assertions |
| `DOLIBARR_TEST_ROOT=<PRIVATE_OPERATOR_HOME>/dolibarr php test/unit/invoiceprofile.php` | PASS — 387 included preview/calendar assertions and 24 profile assertions |
| `DOLIBARR_TEST_ROOT=<PRIVATE_OPERATOR_HOME>/dolibarr php test/unit/invoice.php` | PASS — 387 included preview/calendar, 87 included booking/mail and 34 invoice assertions |
| `DOLIBARR_TEST_ROOT=<PRIVATE_OPERATOR_HOME>/dolibarr php test/unit/invoicepdf.php` | PASS — 387 included preview/calendar, 87 included booking/mail and 16 isolated PDF assertions |
| `DOLIBARR_TEST_ROOT=<PRIVATE_OPERATOR_HOME>/dolibarr php test/unit/postcommit.php` | PASS — 387 included preview/calendar, 87 included booking/mail and 13 checkpoint assertions |
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

## F27 pre-CF-02-DML follow-up — 2026-11-19

Historical preparation at75a531d9: the later Owner dispositions, F28 correction
and actual runtime snapshot are recorded in the dated finalization sections below.

**PRE-DML FOLLOW-UP PREPARED — INDEPENDENT GATE RE-REVIEW PENDING.**
This section is an additive finding disposition, not CF-02 booking implementation,
independent closure or a new requirements matrix. ADR-01–ADR-12, D-01/D-02 and
E01–E15 remain binding. Core-Guarded's `current()` still returns booking/invoice/
autocreate/sendmail disabled with `MSMCoreGuardedPending`; no transaction adapter,
new DB rights, normalization, business SQL, real fixture or Core patch is added.

Dependency: [Draft PR #27](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/27),
original reviewed HEAD `8c777340da4cdafae7b91bbe4366fc8aad525945`, erratum HEAD
`2e794a87c5b49929fd2c80026e08333619adaa5f`. External Owner-supplied review:
**AP3 EVIDENCE ACCEPTED WITH DOCUMENTED LIMITS**;
**COMPLETION GATE INCOMPLETE – TARGETED FOLLOW-UP REQUIRED**.
The separate follow-up branch starts at current main
`c79830976a5f1e1bd57d51005ff34c10c424e36f`, does not merge/cherry-pick AP3 or
rewrite its sources/results/hashes, and records its own review HEAD/CI in its PR.
Neither Draft has Owner merge authorization from this follow-up task.

| Finding | Change and concrete evidence | Status for independent review |
| --- | --- | --- |
| F27-01 BLOCKER | Pinned call chain/configuration envelope below; `tools/audit-coreguarded-path.py` and `docs/evidence/f27/core-path-source.json` | Static boundary documented. Actual selected runtime callback closure/new real path test NOT EXECUTED; no self-closure. Reviewer must decide whether the bounded source/configuration argument suffices or require the limited lab follow-up. |
| F27-02 BLOCKER | `msmProcessBooking()` in `lib/booking.lib.php`, fixed file checkpoint, renderer, `test/unit/terminal.php` | Non-DML patch and actual-orchestrator spy tests implemented; targeted independent verification pending. |
| F27-03 MEDIUM | Dated E02 erratum in PR #27; original UNKNOWN JSON untouched | Interpretation corrected; independent erratum verification pending. No new Core-Guarded SET-entry adapter implemented here. |
| F27-04 MEDIUM | `MsmFileBookingCheckpoint::requireRuntime()`, controller binding and actual-orchestrator admission | Server-side preparation enforced before service calls; negative tests pass. Scope: supported file session/logger only; no full Core-Guarded booking admission enabled. |

### F27-01 — pinned standard contribution/Agenda path

Source baseline: Dolibarr V24.0.3 at
`5c6c7ff6a5112413b1c27221c8d11750afad56f8`. The read-only auditor compares12
source files byte-for-byte to those Git objects and tokenizes selected methods as
data. It does not include Core/config, inspect a live database or run business
methods. The JSON records file/method hashes, source lines and direct nested
transaction call counts. Absence of a direct `completion_type` reference is a
source observation, **not a transitive proof for arbitrary dynamic callbacks**.

| Pinned call boundary | State/side effect and selected-path constraint |
| --- | --- |
| Outer future adapter | D-01 preflight → SET RR → Core-BEGIN at depth1; locks before business reads. Only the outer completion may end the admitted member transaction. No adapter is supplied here. |
| `Adherent::subscription()` (`adherent.class.php:1915`) | Nested depth2, creates a fresh Subscription on the same DoliDB; accountid0. No invoice/customer, payment/bank API or mail call in this method. |
| `Subscription::create()` (`subscription.class.php:167`) | Nested depth3; subscription INSERT, then `Adherent::update_end_date()` at depth4, returning to3. Fresh object has no linked-object mutations in the bounded profile. |
| `CommonTrigger::call_trigger()` (`commontrigger.class.php:63`) → `Interfaces::run_triggers()` (`interfaces.class.php:78`) | Dispatches MEMBER_SUBSCRIPTION_CREATE. Scans Core plus configured trigger directories, tests module activation and checks Core depth before/after callbacks. Counter balance cannot detect a foreign end/replacement returning to the same depth. |
| `InterfaceActionsAuto::runTrigger()` (`interface_50_modAgenda_ActionsAuto.class.php:80`) | Agenda disabled or auto-event off: early return. Enabled MEMBER_SUBSCRIPTION_CREATE uses the fetched member context and creates the standard ActionComm. `ACTION_*` is excluded from Agenda recursion. |
| `ActionComm::create()` (`actioncomm.class.php:500`) | Nested depth4; actioncomm/resource INSERTs, optional extrafields, ACTION_CREATE dispatch, then nested commit/rollback to3. Standard event association uses the member, not the subscription row. |
| Return through Subscription / Adherent | Subscription returns depth3→2; Adherent repeats its own end-date update at depth3→2, then returns2→1. `MsmBookingMember` observes the parent R16 update; it does not replace its transaction control. |
| Outer Core end (`DoliDB.class.php:241/265`) | At depth1, actual COMMIT/ROLLBACK, then Core logging. CHAIN/RELEASE affect this outer completion; stable nested counter calls do not emit physical end SQL. Uncertain returns are not redeemed by depth0. |

The source argument for stable Completion is limited to this selected configuration:
known standard same-connection methods; fresh Subscription/ActionComm objects;
no supplied linked objects or populated/computed ActionComm extrafields; no automatic
file copies (`MAIN_COPY_FILE_IN_EVENT_AUTO` off); no extension rewriting the session,
connection or Core transaction depth. The standard Logevents trigger must either
return early for both MEMBER_SUBSCRIPTION_CREATE/ACTION_CREATE (event flags off)
or receive its own explicit path/engine qualification. This source envelope uses
the early-return path. Notification/Blockedlog are **not silently included**:
Notification can dispatch hooks/Notify delivery; Blockedlog explicitly handles
MEMBER_SUBSCRIPTION_CREATE. Their active effects need separate consideration.

Known incompatible interventions include native COMMIT/ROLLBACK/BEGIN inside a
nested callback, DDL/implicit commits, changing completion_type after its observation,
reconnecting/replacing DoliDB, draining counters and computed/third-party callbacks
that do these things. For the selected pure standard path, nested counters remain
above1 until the outer end; selected methods do not rewrite Completion. Outside
that path, equal counters/two samples do not prove physical continuity. No universal
extension inventory, whitelist or third-party certification is imposed: D-02's
compatibility reservation remains, and known dangerous combinations are incompatible.

**Configuration binding limitation:** the active protected I105 callback inventory
was not read in this follow-up, and AP3's published HTTP profile does not contain
it. No claim is made that the existing I105 installation matches every condition
above. No new real contribution/Agenda ON/OFF, nested fault or rollback case was
executed. Old AP3 probes are not repurposed as such evidence. If independent review
finds this bounded static argument insufficient, retain F27-01 as a blocker and
obtain a narrowly authorized **new isolated Core test copy and new fixtures**:
standard Agenda OFF/ON, both outer ends under3 initial modes, nested error with
counter-return checks, two Completion samples before outer end and forbidden
post-end SQL/side-effect fences. No reuse of old AP3/SH-01/D-01 fixtures or writes
to the existing I105 business database; no general campaign rerun.

### F27-02/F27-04 — actual non-DML runtime and batch boundary

The production orchestrator reads `stop` immediately after `book()`. All profiles
skip PDF/mail for such a result. For Core-Guarded, only a service-supplied
`completion=nonterminal_acknowledged` with SUCCESS/FAILED/SKIPPED and no STOP
permits continuation. Missing/malformed classification or UNKNOWN always stops.
This is an **internal outcome contract**, not a MySQL completion-value admission
whitelist, physical event proof or request parameter. Its future adapter producer
is still unimplemented and must be independently reviewed.

At batch entry, Core-Guarded checks the actual PHP session module/path and actual
logger objects/settings, then requires the **final** `MsmFileBookingCheckpoint`
object as callback, checking its bound session ID/path before the first service call. Arbitrary closures/handlers cannot be designated qualified by
a boolean. The controller selects this object for Core-Guarded; direct current
booking service calls remain blocked by existing profile admission. No local lab
path is hardcoded. Supported preparatory runtime:

- PHP `files`, nonempty unchanged session ID, existing readable/writable absolute
  flat local directory; wrappers, missing/unwritable paths, sharded syntax and user
  handlers refused. No session/global setting is rewritten in production.
- Enabled standard `mod_syslog_file` objects whose export implementation belongs
  to the installed standard Core source path. No exporter is called by admission.
  Only a local regular/default logfile with usable parent, no wrapper/symlink or
  additional per-session/IP/suffix naming in this bounded profile. Unknown logger,
  disabled/unsupported handler configuration or failed control refuses work.
- These object/path checks are **not source-attestation of arbitrary modified Core**
  or blanket cross-version qualification. The source argument is pinned V24.0.3;
  normal CI across other tags is synthetic regression only.

The fixed checkpoint contains no DB/service/logger/hook reference. It checks the
same file-session path/ID, reopens once, verifies the consumed processing revision,
saves the complete state and closes once. Session warnings are failures too; it
never retries. After a terminal result the orchestrator marks all following slots
`not_processed`, preserves the current result and performs only this checkpoint,
then returns. No later service/profile/preflight, mail, PDF or arbitrary save is
called. A failure retains the acknowledged result **in the returned state**, sets
checkpoint-failed/processing/STOP, and makes no further persistence attempt. The
last durable consumed record may remain UNKNOWN; it is not fabricated into durable
success. Fresh consumed POST validation rejects replay. A later GET is a new
request/connection; same-request unsafe SQL is not justified by a result link.

Existing Enhanced/MariaDB service behavior and historical stored `skipped` results
remain unchanged. Their legacy stopped followers retain the existing classification;
Core-Guarded's new stopped followers use the explicit NOT_PROCESSED status. The
renderer counts it separately and displays the terminal/persistence warning;
unsent terminal mail/PDF are not reported as attempted failures. Actual business
writers/observers and the 71 requirements/evidence mappings are untouched.

| Contract | Precise preparatory behavior |
| --- | --- |
| E02 | Proven SET refusal1568 before BEGIN/DML = entry refused / NOT_PROCESSED + STOP. The AP3 original UNKNOWN label is historical; no foreign end or retry. Transport/entry ambiguity remains UNKNOWN. |
| E10/E12 | Acknowledged nonterminal outcome plus the fixed successful checkpoint is required before continuation; STOP takes precedence even if the member result is success. |
| E11/E13 | Unknown/unacknowledged result forces STOP, including an inconsistent service `stop=false`; no later readback/retry/compensation. |
| E15 | Acknowledged COMMIT remains SUCCESS; checkpoint failure yields processing/persistence warning + STOP. Durable consumption remains closed; no automatic resume. |

### Tests and evidence levels

`test/unit/terminal.php` is included by the existing `postcommit.php` CI runner;
workflow/matrix are unchanged. Actual `msmProcessBooking()` is exercised with spy
services, effect fences and **real local PHP file-session checkpoints**. Cases:
SUCCESS+STOP, acknowledged ROLLBACK+STOP, UNKNOWN+STOP, E02 NOT_PROCESSED, E15,
explicit qualified SUCCESS/ROLLBACK continuation, missing classification and
UNKNOWN with a contradictory continue marker. Tests assert one service call and
no PDF/mail for terminal cases, fixed checkpoint/readback, non-resumable consumed
state, local E15 versus durable uncertainty, output escaping/new counts/historical
skipped rendering, custom callback/logger/session/path refusal before service calls.

`docs/evidence/f27/terminal-calls.json` records sanitized function-monitor names,
caller basenames/lines and spy-call order; no arguments, credentials or real
business rows. Xdebug monitoring is optional in normal CI; service/effect/count
assertions always run. `synthetic-validation.json` records11 successful local
runners on PHP8.2.35, using pinned Core24.0.3 source where required by the runner. This is neither authenticated
HTTP nor new real business integration. AP3's historical real proof remains distinct.

### Mapping to Issue #22's six open Completion-gate checkboxes

| Checkbox (in existing order) | Evidence and remaining condition |
| --- | --- |
| 1 Classification stable through actual Core/Agenda path | F27-01 source/configuration envelope established; selected runtime closure/new real path proof NOT EXECUTED; independent sufficiency decision open. |
| 2 Actual Core end/logger, no forbidden SQL | Historical AP3 bounded file-logger evidence plus new server-side file-logger refusal; no arbitrary/custom logger certification. Independent acceptance open. |
| 3 Durable independent session checkpoints | Historical AP3 HTTP/file-handler proof plus actual fixed production checkpoint tested locally; no new Apache/authenticated HTTP test, source/runtime integration review open. |
| 4 STOP before SQL/next-member/delivery; clear untouched members | Actual orchestrator patch and call-order spies/fences, NOT_PROCESSED renderer; no productive CF02 adapter run. Independent delta verification open. |
| 5 Lost ACK/E15, acknowledged result retained, no retry/foreign end | Historical real Lost-ACK/E15 plus new actual-orchestrator synthetic states/real local file failure controls; no new fault injection on a database. |
| 6 Independent bounded gate assessment before CF02 DML | NOT COMPLETED. All six checkboxes remain open; neither Codex nor this draft closes findings or accepts the gate. |

### Targeted independent delta-review assignment

**Historical pre-F28 assignment; superseded by the single final common review
request in PR28, which records the final exact HEAD.**

Review the exact follow-up PR HEAD and PR #27's erratum HEAD separately, against
base `c79830976a5f1e1bd57d51005ff34c10c424e36f`:

1. Confirm F27-03's E02 interpretation and3-file hash update without rewriting any
   original AP3 source, trace, output or STOP.
2. Decide explicitly whether **F27-01** can close for the stated pinned source/
   configuration envelope; if the missing selected-runtime closure prevents it,
   name the minimal read-only configuration proof/new isolated lab case required.
3. Assess **F27-02** against the actual orchestrator: early STOP, no terminal effects
   or follower calls, single fixed checkpoint, acknowledgment preserved under E15,
   consumed replay blocked and historical Enhanced/MariaDB behavior retained.
4. Assess **F27-04**: actual files/logger checks and final callback identity are
   enforceable before work, control failures stop, unknown exporters/user handlers
   are never invoked, no lab path/Completion whitelist/normalization is introduced.
5. Issue a separate decision for each finding and each of the six gate checkboxes.
   No automatic CF02 contribution-DML/merge/release approval. If accepted, request
   the separate Owner decision; otherwise list concrete remaining blockers.

No merge, real destructive test, productive Core-Guarded contribution DML, CF03
business/Agenda/HTTP/replay/concurrency/V22/V23/Enhanced/MariaDB regression, ZIP,
tag or release is performed or qualified by this follow-up.

## F28-01 controller ordering correction — 2026-11-19

Owner's finalization task accepts the external review at PR27 `2e794a87` /
PR28 `75a531d9`: F27-02 **CLOSED WITH DOCUMENTED LIMITS**, F27-03 **CLOSED**;
F27-04's bounded file-session/file-logger support provisionally accepted, with
**direct service admission still a CF02 duty**. F27-01 remains a pre-DML obligation.
PR27 is normally merged as `56ab0784be05ab866769a9674c961bd4198588a9` with its
reviewed tree unchanged; reviewed push/PR and post-merge CI each21/21 PASS.
The earlier finding-status statements above are historical review stages.

F28-01 now checks CoreGuarded's actual runtime while the original confirmation
session is still active. `msmConsumeConfirmation()` first validates replay/request
shape on a **copy**; no consumed state is assigned or saved yet. Known session/
logger refusal therefore retains confirmation, revision and rows, with the precise
localized error flash. Only after that check and normal profile/database admission
does the controller persist processing state and close the session. The same
checkpoint object, bound before close, then rechecks the actual ID/path after close.
No catch path restores a consumed confirmation after close or possible member DML.
CSRF/profile/revision/entity/permission checks and disabled CoreGuarded features
are unchanged; Enhanced/MariaDB do not acquire the CoreGuarded runtime restriction.

`test/fixtures/controller-runtime.php` executes the **actual subscription.php
body** in isolated CLI requests. It instruments the bootstrap, three constructed
admission/service objects, header/session-close observation and layout helpers;
it preserves the controller's branches, state assignments, catch and exit. Its
synthetic admission permits exercising the future CoreGuarded handoff only inside
the test process, without changing production feature flags. Native local PHP
sessions are reopened by a separate GET/request. No protected config, DB or HTTP
transport is used. Actual module EN labels are used for the visible-error check.

`test/unit/controller-runtime.php` runs from the existing request CI runner:
unsupported session handler/logger retain confirm/revision5 without UNKNOWN or
service calls; redirected GET displays the specific diagnosis. Changed binding
after close and a service fault after possible DML retain processing/revision6;
replay with even a current revision cannot invoke the service. Legacy MariaDB and
Enhanced success, CSRF and entity rejection remain covered. The existing
`75a531d9` controller is also executed as a negative control: its known logger
refusal incorrectly persists processing/UNKNOWN with no specific flash; corrected
source retains confirmation and the precise flash. Evidence:
`docs/evidence/f27/f28-controller-calls.json` and `f28-controller-regression.json`.
This is reliable controller instrumentation, **not authenticated HTTP or real
business-DML qualification**. Targeted independent verification of the new HEAD
is still required; F28-01 is not independently self-closed.

## F27-01 runtime-bound qualification addendum — 2026-11-19

**QUALIFICATION PREPARED; INDEPENDENT SUFFICIENCY VERDICT / OWNER GATE ACCEPTANCE
PENDING.** This addendum supplies the previously missing concrete configuration
binding; it does not relabel source inspection as a real contribution transaction.

### Stage A — actual protected configuration, read-only

The Owner executed `tools/f27-runtime-read.php --read-only` as www-data after the
agent's noninteractive sudo preflight required a password. Sanitized original
result: `docs/evidence/f27/f27-runtime-readonly.json`, observed
`2026-11-19T10:04:33+00:00`. The reader loaded actual protected I105 configuration
and genuine `Conf::setValues()` through a query-fenced native driver. It executed 7
SELECTs, blocked 0 statements, changed 0 charset statements, started/read 0 sessions,
called 0 business APIs/triggers and performed 0 SQL-trigger checks. No business
rows, password, SMTP data, cookies, session files or configuration contents are
published. Only the sanitized snapshot was created; no test fixture was written.
The reader source exposes the query templates; the secret-bearing full Core
constant/decryption SELECT is intentionally not exported as a raw SQL log.

| Observation | Actual snapshot / consequence for the bounded path |
| --- | --- |
| Identity | Dolibarr24.0.3, Core `5c6c7ff6a5112413b1c27221c8d11750afad56f8`, PHP8.2.35, mysqlnd8.2.35, MySQL8.4.13, `<PRIVATE_MYSQL_SCHEMA>`; reader's fresh connection autocommit1 / NO_CHAIN. This is observation, not an admission whitelist. |
| Agenda | Enabled; MEMBER_SUBSCRIPTION_CREATE automatic event on; automatic file copying off. Standard contribution dispatch therefore selects the known Agenda-create branch. |
| Logevents | Both MEMBER_SUBSCRIPTION_CREATE and ACTION_CREATE flags off; the all-events trigger returns before event DML. |
| Notification / Blockedlog / Workflow | Disabled; not selected by the current Core dispatcher. No claim that their enabled effects have been qualified. |
| Extensions | Zero additional trigger directories. The real dispatcher selects exactly Logevents, ActionsAuto and Societe ContactRoles from Core. This examines the two relevant dispatches, not arbitrary installed modules. |
| Extrafields |0 definitions and0 computed fields for adherent, subscription and actioncomm. No computed extension branch is introduced by this snapshot. |
| Engines | All7 relevant inspected tables are InnoDB, including member/type/subscription, Agenda/resource, events and extrafields. No engine/schema modification. |
| Logger | Enabled standard mod_syslog_file at level4; implementation hash equals AP3/pinned Core; local destination and writable parent. |
| Session | Apache ini/VHost declares files and a local readable/writable existing path for www-data. No session is opened/read. This is a CLI reader of actual configuration/declarations, not a new Apache request or authenticated HTTP qualification. |

`f27-runtime-source-pins.json` independently compares the three selected callback
files plus Conf/dispatcher source with the pinned Git objects. The earlier 12-file
analysis remains unchanged; ContactRoles is the one newly relevant selected file.
The VHost source check in `f27-apache-declarations.json` records the relevant
override-name/AllowOverride boundary without publishing unrelated settings. This
is a point-in-time snapshot, not global Apache/process-environment attestation or
continuous config monitoring. It must not be promoted to arbitrary future installs.

### Selected and reached guards — source plus no-DML execution

`tools/f27-callback-guards.php` checks the observed callback hashes, loads the actual
pinned methods and supplies observed flags, ordinary synthetic objects and a
DoliDB-typed **DB-rejecting spy with no native connection**. Its five recorded
cases in `f27-callback-guards.json` each return 0 with 0 DB calls:

- Logevents for MEMBER_SUBSCRIPTION_CREATE and ACTION_CREATE: flag-controlled early
  return, exactly matching the two observed off flags.
- ContactRoles for both actions: neither action matches its supported document/
  project/ticket creation list (`runTrigger`, pinned file lines 78–140); its constructor
  only stores the DB and metadata. No contact-role callback/DML is reached.
- ActionsAuto for ACTION_CREATE: the explicit ACTION-prefix guard returns before
  recursive Agenda creation.

These are actual guard-method executions with observed inputs, **not a native DB
or real contribution/Agenda-write run**. The remaining selected write branch is
only the already analyzed standard MEMBER_SUBSCRIPTION_CREATE Agenda branch:
Subscription → CommonTrigger/Interfaces → ActionsAuto → ActionComm → ACTION_CREATE.
The logical depth path remains 1→2→3→4→3, Agenda4→3, Subscription3→2,
Adherent end update3→2, Adherent2→1, then the outer Core end. No selected source
method rewrites Completion, replaces the connection or drains the outer depth.
The standard file-logger's Core-end behavior is separately established by AP3;
this addendum neither repeats nor enlarges those historical measurements.

### Stage B decision and residual risk

For this **concrete snapshot**, the formerly unknown candidate/callback closure is
now bounded: no extra directories, inactive side-effect modules, no computed fields,
Logevents off for both events, the extra ContactRoles handler proven no-op, and the
only active business branch is pinned standard Agenda. The technical assessment is
that no remaining **concrete** callback/Completion-intervention gap justifies a new
destructive Lab run before the independent sufficiency review. Stage B is therefore
**NOT EXECUTED (not required by this snapshot/source assessment)**; no old/new
business fixtures or existing I105 rows are touched, and no real contribution,
Agenda ON/OFF, late-rewrite or new native COMMIT/ROLLBACK case is claimed.

The external reviewer must explicitly accept or reject this bounded F27-01 argument.
If a concrete gap remains, name the minimal new isolated-copy/fixture test; the
Owner authorized such targeted tests, not a full campaign repeat or production DML.
This is **not Codex closing F27-01 or accepting the Completion gate**.

Limitations remain: two Completion observations cannot detect every later physical
replacement; adversarial/unexamined hooks, changed config/source and additional
Notification/Blockedlog/Logevents/extra-field effects are outside this snapshot.
The source-bound assumption is that the selected standard code/configuration stays
stable during the member operation. A future active CoreGuarded service still must
perform its own admission and application-context checks before DML; F27-04's direct
service requirement remains CF02 work. Known foreign ends, connection loss or
uncertain ACK stay UNKNOWN/STOP without retry, compensation or foreign rollback.
No general third-party inventory/certification/blocklist, completion whitelist,
Performance-Schema requirement, session normalization or second transaction manager
is introduced. CF03 real business/HTTP/Agenda/error/regression and release gates stay
separate. Production CoreGuarded booking remains disabled.

### Reproduction and current gate handoff

The Stage-A reader was executed once with `sudo -u www-data php -d xdebug.mode=off`
and external mail/network transport functions disabled. Its fixed destination
refuses overwriting the preserved result. A deliberately new snapshot needs a new
reviewed output destination; do not delete or silently replace the original. The
reader may perform normal Core file logging but no business/configuration writes.
Protected credentials remain only in process memory. To reproduce the no-DML
guard probe against pinned source and the published observed flag inputs:

```bash
php -d xdebug.mode=off tools/f27-callback-guards.php --check /path/to/pinned-core /tmp/new-f27-guards.json
```

Current dispositions and six-checkbox mapping for the **single final independent
review request in PR28** (it names the exact final HEAD; no self-referential source
commit/hash in this sealed report):

| Item | Current evidence / required independent decision |
| --- | --- |
| F28-01 | Controller correction and 30 actual-flow assertions; old-code failure reproduced. Independently verify the final HEAD before any PR28 merge. |
| F27-01 / gate1 | Actual selected configuration + 13th ContactRoles source + 5 no-op guard checks bound the previously unknown closure. Decide whether this suffices for the supported standard path; no new native contribution/Agenda transaction is claimed. |
| F27-02 / gates4–5 | Owner/external CLOSED WITH DOCUMENTED LIMITS; verify the early STOP/checkpoint/E15 result remains intact after the controller fix. |
| F27-03 / gate5 | Owner/external CLOSED; normally merged PR27 retains the E02 erratum and original measured UNKNOWN label, without rewriting raw results. |
| F27-04 / gates2–3 | Bounded files/file-logger support provisionally Owner-accepted; pre-consumption runtime refusal now corrected. Direct active-service admission is still CF02 work. |
| gate6 | Final independent review and **separate formal Owner Completion-gate acceptance** pending. Conditional development-merge permission is not productive DML authorization. |

All six Issue22 gate checkboxes stay open until documented independent assessment
and Owner gate decision. The final reviewer must state F28-01 disposition, F27-01
bounded sufficiency or the exact remaining concrete test, retained F27-02/F27-03
closure, F27-04 limits and each gate result. A final positive independent review
plus CI/scope permits the Owner-conditionally authorized development merge only.
Without that review PR28 remains open for review; Codex cannot supply its own
independent approval. No production CF02 adapter, invoice/customer extension,
Enhanced/MariaDB observer change or release/ZIP/tag action is included.
