# ADR-0001 — Core-first transaction profiles

## Current ADR-06/11/12 application after PR #38 – 2026-11-24

**Authoritative post-PR #38 state (2026-11-24):** [PR #38](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/38) was independently reviewed and **squash-merged** into `main` as `0998fbaf58155cca04d31ab99c6bb47420e53c72`; reviewed HEAD `b15b93580f995fb05a696a125c2a5fd995dd4bb3` has **identical Git tree** `a6e7256fb1572361c4dd4d850124ca7a6afff566`. The independent verdict was **APPROVED FOR DEVELOPMENT MERGE WITH DOCUMENTED LIMITATIONS**, with exact-source Standard 9/9 + Full 21/21 + Hosted 1/1 (**31/31**) synthetic CI PASS. Phase 4 development is merged; **Phase 5 real A–D integration, CF05-07, package, hosted and Owner release acceptance are NOT complete**. All older dated DRAFT/finance-default-deny/core-source-allowlist statements below are historical and not current requirements.

**Binding policy:** [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22) and [Owner Variant B](../owner-core-parity-2026-11-22.md): Dolibarr Core parity plus Massaction-specific safeguards. Full contribution/optional invoice and conditional linked third party, validated line and relation, post-COMMIT PDF and optional mail/verified attachment are implemented and enabled for `CoreGuarded`, `EnhancedMySQL`, `MariaDBSnapshot`. Normal Core numbering/models/Agenda/triggers/extensions are accepted; **do not reintroduce** Core/trigger source SHA allowlists, a separate `PROV` veto or blanket CoreGuarded-finance default deny. Preserve server-owned rights/entity, fresh fingerprint/full confirmation, original consumed one-shot, same active owned transaction guard, transactional-table/readbacks, acknowledged completion, conservative `UNKNOWN/STOP` and no automatic retry/foreign rollback. `F36-02/P2` is OWNER-ACCEPTED **Core filesystem residual risk**, not technically removed.

**Transaction observation is intentionally profile-specific:** CoreGuarded uses original DoliDB/mysqli connection and bounded Core transaction depth/ACK (no Performance Schema event identity); EnhancedMySQL retains P_S transaction ownership/isolation observation; MariaDBSnapshot retains snapshot/connection/transaction-state semantics. No profile silently inherits the physical assurances of another. The Booking owner is the sole outer transaction coordinator; Invoice gets the identical active owned guard. Only narrowly qualified business errors may use demonstrably owned rollback; unknown ownership/completion stays UNKNOWN/STOP.

**Independent disposition:** F38-01–06 `CLOSED`; F38-07–09 `SUPERSEDED BY OWNER VARIANT B` (not technically closed); F38-10–13 `INDEPENDENTLY CLOSED`; F38-14/P2 `INDEPENDENTLY CLOSED` (server-confirmed `manualend` restored only after fresh fingerprint revalidation); F36-01 historically closed. **Separate real incident:** `DOLIENV-ACC-01` in B remains `UNKNOWN/STOP`, diagnostic subscription #3 and rollback unconfirmed; lack of a persisted record does not prove rollback ACK. No replay/compensation. The later local B finance cases are Owner-accepted positive results; the historical incident is unchanged.

**Current dolienv topology:** A = local Dolibarr 24.0.3/PHP 8.2.35/MariaDB 10.11.19 `MariaDBSnapshot`; B = local Dolibarr 24.0.3/PHP 8.2.35/MySQL 8.4.13 `CoreGuarded`; C = same Dolibarr/PHP/MySQL versions `EnhancedMySQL`; D = hosted Dolibarr 22.0.5/MySQL 8.4.13/Web PHP 8.4.28 CGI/FastCGI and CLI PHP 8.3.37 `CoreGuarded`. A/B/C have separate schemas, webroots, accounts, sessions, document roots; B/C share MySQL server and local module source only. Legacy CF03 A/B labels and older shared A/C commands cannot be relabeled/reused. Earlier evidence on `46f78d8...` remains historical; current local qualification uses the merged PR #39 baseline. D focused contribution/session/replay PASS; broader qualification remains open. [Canonical matrix](../dolienv-environments.md).

**Current Phase 5:** Local accepted real cases on `ba5c9a3121754fcbd4259bb8906ad1dd17f1ce00`: protected readiness 21 checks, initial finance/PDF PASS within scope, additional non-mail 8/8 PASS, local B mail/verified attachments 3/3 PASS (capture only). Hosted D independent checkout and dedicated schema, Dolibarr 22.0.5, Oracle MySQL 8.4.13, Web PHP 8.4.28 CGI/FastCGI / CLI 8.3.37: four roles, two separate contribution-only cases, persisted result reload, consumed replay refusal and separate SQL readback PASS within scope on `0adc259d4739b44f9b1fb3a49574d7e1c318132c`. D finance/PDF/mail, broader live cases and exact ZIP OPEN; A/C mail OPEN. Modified OD-P5-02 accepts authorized summaries within scope, not independent external reproduction. See [current baseline](../current-development-baseline.md) and [environment evidence](../dolienv-environments.md).

**Operational/release boundary:** Phase 5 PARTIAL; per-case Owner GO, protected target-user F32-MR-01 preflight, actual source/profile/role binding, fresh fixtures, controlled backups and independent readbacks required. Full CF03-NORMAL A–D, remaining A/C mail and D finance/PDF/mail, wider live security/configuration, CF05-07/CF03-RISK, R0-03, R0-04, R0-07 and separate Owner Release GO remain OPEN / NO-GO (R0-04 BLOCKED pending exact package qualification). Historical B02 mail/attachment and DOLIENV-ACC-01 UNKNOWN/STOP (latter rollback unconfirmed) unchanged; no replay/compensation. CF03-FAULT destructive tests DEFERRED. No new authorization.


**Current session compatibility application (not a new ADR/Owner decision):** PR #45 removes only the unnecessary directory-listing/readability prerequisite in `MsmFileBookingCheckpoint::requireSession()`. The PHP `files` handler, nonempty session ID, plain absolute existing writable directory, canonical binding and actual reopen/read/write/close/readback remain required. A known session file may be accessible even when the directory cannot be listed (e.g. owner mode `0300`). Preserve the original consumed confirmation, one-shot/processing/result persistence, owned guard and finance/post-COMMIT checks. Do not widen provider permissions to make a test pass. The six concrete Core/model/PDF file readability checks remain a separate contract. PR #45 merged `b026f1504db27308fc7326e2e88ec2e53b2f48d9`; [current baseline](../current-development-baseline.md) records exact provenance and open F45 findings.

---

> **Archived date-bound content below:** previous sources/labels/admission claims reflect their original times; the post-PR38 section above governs current status.

> **CURRENT BINDING OWNER DECISION — 2026-11-22 / PR #38, CORE PARITY + MASSACTION SAFETY (DRAFT, NOT YET INDEPENDENTLY REVIEWED).** The Owner expressly **supersedes the earlier CF05-06 exact-Core/trigger-blob allowlist and PROV-directory prohibition**. The module trusts Dolibarr's normal `Adherent::subscription()`, `Societe::create_from_member()`, `Facture::create()/addline()/validate()`, numbering/PDF models, enabled triggers/Agenda/Logevents and extension behavior as the Core single-member workflow does. **Full `CoreGuarded` invoice, conditional auto Third Party, post-COMMIT PDF and optional verified-attachment email stay enabled**, along with `EnhancedMySQL` and `MariaDBSnapshot`. Removed as normative admission checks: 15-version invoice source hashes, standard trigger hashes, prohibition of custom enabled triggers, Agenda file-copy/Logevents settings and `PROV` existence check. Do not rebuild those allowlists under F38-08/09. Core model lookup supports installed model roots without unsafe model-name paths.
>
> **STILL BINDING:** Massaction's per-member rights/entity, server-confirmed preview/fingerprint and user/profile revision, controlled original DoliDB Guard, effective transactional-table/engine checks, consumed one-shot session, confirmed nonterminal completion, durable post-COMMIT PDF/mail checkpoints, verified attachment/recipient identity, and `UNKNOWN/STOP` on uncertain completion or external effects with **no automatic booking/PDF/mail replay or foreign rollback**. No guarantee that Dolibarr Core SQL and filesystem are jointly atomic or that arbitrary custom extensions are benign. **F36-02/P2 = OWNER-ACCEPTED CORE RESIDUAL RISK** (conditional pre-outer-COMMIT `PROV` rename); it is not proven impossible or technically fixed. **F38-07/08/09 = SUPERSEDED BY OWNER ARCHITECTURE DECISION**, not historically falsified or independently technically CLOSED. F38-01..06 remain CLOSED per prior reviews and regressions. F36-01 remains historically CLOSED on the old default-deny branch, while its later Owner activation is binding.
>
> **EVIDENCE AND RELEASE:** Newly added `test/unit/cf05-core-parity.php`, positive/negative existing/new customer and contribution/mail orchestration, standard/custom model regression and 9-/21-/Hosted synthetic CI are development evidence, **not real A–D**. Real CF03-NORMAL A–D needs separately Owner-authorized cases, F32-MR-01 private launcher/read-only preflight, A/C serialized use, DB/invoice/PDF/file/SMTP evidence, CF05-07/Owner risk review, later exact-ZIP/hosted D and separate release GO. Purposefully destructive CF03-FAULT is DEFERRED. Earlier statements below that require Core source/trigger pinning or productive CoreGuarded default deny are preserved **only as dated history**, not current policy. [Current decision and finding map](../owner-core-parity-2026-11-22.md).


**PR #38 CF05-06 corrective checkpoint — 2026-11-22 (DRAFT / NOT INDEPENDENTLY CLOSED):** The new server-owned `MsmCoreFinanceQualification` implements a bounded supported-configuration gate for the Owner-authorized CoreGuarded invoice/Third-Party/PDF/mail workflow. **15 exact pinned V22.0.0–V24.0.3 Core invoice source blobs** and version-matched standard Agenda/Logevents trigger blobs are checked against installed content before a CoreGuarded invoice's first outer BEGIN. Unknown/patched Core, enabled third-party or unqualified core triggers, Agenda automatic file copying or enabled unqualified invoice Logevents are rejected; an existing provisional `PROV` invoice file/directory is refused immediately before Core `Facture::validate()`. The original rights/confirmation/same guard/one-shot/postcommit/no-retry contracts remain in force. **The finance feature gate remains ON for supported qualified Core configurations; this is not a blanket re-lock.** **F38-04/P2:** five remaining Massaction/CSRF/Admin/Period/Preview UI security entry points added to Full+Hosted CI with broad class/lib/admin fallback and a missing-path negative check. **F36-02/P2 and F38-07/P2:** BOUNDED MITIGATION IMPLEMENTED / INDEPENDENT SOURCE REVIEW PENDING, **NOT CLOSED** and no real A–D DB/FS/SMTP qualification; custom trigger side effects and external filesystem races are not certified. See [the source-qualification report](../cf05-06-standard-core-source-qualification.md). **No Owner operational GO or release GO.**


> **CURRENT OVERRIDING OWNER POLICY — 2026-11-22, PR #38 (UNREVIEWED DEVELOPMENT CHANGE):** The Owner expressly authorizes **re-activation of the complete CoreGuarded financial workflow for Phase 5 preparation**, including invoicing, invoice-dependent new Third Party, invoice PDF generation and optional email with verified attachment, subject to the existing server-side rights/profile/revision/CSRF/confirmation/same-guard/one-shot/nonterminal-COMMIT/checkpoint/UNKNOWN-STOP controls. Production `MsmTransactionProfile::coreGuardedFinanceQualified()` now returns **true**; `booking/invoice/autocreate/sendmail` are admitted for a valid configured CoreGuarded profile, as with the already-enabled EnhancedMySQL and MariaDBSnapshot profiles. PDF is a guarded selected-invoice post-COMMIT effect, **not an independently selectable finance flag**. This explicit newer decision **supersedes** historical paragraphs below that say CoreGuarded finance remains default-denied or that source qualification must precede even admission. Historical **F36-01/P1 CLOSED** remains a valid historical assessment of the previous, closed-gate code; it must **not** be reused as independent authorization/validation of this newly opened gate.
>
> **Risk accepted as policy deviation, NOT safety qualified:** **F36-02/P2 remains OPEN / CF05-06 REQUIRED**: V22/V24 `Facture::validate()` may rename an existing `PROV` invoice document directory **before the outer COMMIT**, and SQL rollback does not undo this filesystem side effect. Actual fresh-module reachability, standard triggers/Agenda/third-party contact/engine behavior and mitigation or supported-configuration exclusions remain **NOT VERIFIED**. The newly reachable productive code **is therefore not yet independently shown safe for real finance use** on A/D. This Owner policy change removes the previous protective default-deny barrier; the previously accepted *development-merge-only* justification for leaving F36-02 open under a closed gate **no longer applies**. It requires an explicit independent exact-HEAD security/architecture review and renewed Owner risk decision for operational deployment/real-write admission. Existing no-retry/UNKNOWN-STOP guarantees do not make pre-COMMIT filesystem side effects rollback-atomic.
>
> **Independent gates retained:** The Owner's full-function admission policy does **not by itself** authorize actual A–D business writes or complete Phase 5 evidence, CF05-06/07, A/B/C private-launcher/read-only preflight, any deployment/ZIP/tag or v0.1.0 release. Fresh, separately Owner-authorized A–D test cases and an exact-source, externally reviewed source/DB/PDF/SMTP qualification remain mandatory. Destructive CF03-FAULT remains DEFERRED/OPEN/NOT EXECUTED. This PR remains DRAFT until independent review.

## PR #36 source-verified implementation addendum (2026-11-21; NOT a new ADR decision)

[PR #36](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/36) was **SQUASH-MERGED** on 2026-11-21 as `d0b15785c60b74aa7ad59a1c687db94193d159c6`; independently reviewed source `33cb3da3b389f9df2870bc8c6223c86a959cb467` and merge have **identical Git tree `6cc4834b9fe5100aca99126885791b3aeff12fef`**. External verdict **`APPROVED FOR DEVELOPMENT MERGE`**, **F36-01/P1 CLOSED** and **F36-02/P2 OPEN / CF05-06 REQUIRED**, no new P0–P3 delta findings. [standard CI 9/9 PASS](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/44956004981) and [hosted PHP8.3/V22.0.5 CI 1/1 PASS](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/44956004947) refer to the **exact reviewed head**; their positives are **synthetic**, not new real full-finance test evidence.

**ADR-06/11 remain binding as amended by Owner in Issue #22**: full finance on CoreGuarded is a development/release *target*, **not** an authorization to operate it. PR #36 added the complete guarded CoreGuarded invoice/Third-Party, typed business error/owned rollback and checkpointed PDF/mail code without a second transaction owner, a CoreGuarded P_S fallback or a persistent journal. **Production `MsmTransactionProfile::coreGuardedFinanceQualified()` still returns `false`**: contribution booking is admitted, while invoice/autocreate/sendmail remain **server-default-denied**, including Preview/Booking/direct Invoice paths. EnhancedMySQL and MariaDBSnapshot retain their historical admitted scope.

**F36-02/P2 remains OPEN for CF05-06 before any finance admission**: V22/V24 `Facture::validate()` conditionally renames a `PROV` directory before outer COMMIT. Filesystem effects cannot be SQL-rolled back; actual reachability with fresh module invoices and any mitigation/configuration exclusion remain unqualified. See [CF05-06 plan](../cf05-06-standard-core-qualification-plan.md). CF05-07, CF03-NORMAL A–D, 21-job release CI, real D exact ZIP and separate Owner finance/release GO remain OPEN. Earlier PR34 and historical ADR decisions below retain their original evidence scope.

## Post-PR #34 implementation addendum (2026-11-21; no ADR strategy revision)

The **binding Owner architecture remains the revised ADR-06 and ADR-11 below**, with unchanged ADR-01–05/07–10/12 and D-01/D-02 safeguards. This addendum records implementation status, **not a new product or risk-waiver decision**.

**CF05-01/02 is IMPLEMENTED / INDEPENDENTLY APPROVED / SQUASH-MERGED** via [PR #34](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/34), reviewed HEAD `11d7dd3cf4bdcf831aa87782b7466d2378fc733f`, resulting `main` commit `dc7a172bfd476467d7980cbf16a2657cfc1e729c`. The shared minimal `MsmActiveTransactionGuard` binds to the **same original DoliDB**; Booking injects its *same selected active instance* into Invoice. DB-family/transactional-engine metadata stays on a distinct `MsmDatabaseState`; no new transaction owner or CoreGuarded P_S requirement is introduced. `owned()` checks bracket each Core customer/invoice create/line/validate operation and individually secure the customer/member/invoice persisted readbacks.

**Fail-closed risk boundary:** `coreGuardedBusinessRollbackAllowed()` now admits only previously qualified `preflight`/`subscription` stages with previously qualified business error reasons. Customer, invoice, commit and unknown/future stages remain rollback-ineligible/UNKNOWN/STOP until separately qualified by **CF05-04**. Independent delta review: **F34-01/P2 CLOSED**, **F34-02/P3 CLOSED**, **0 new P0–P3**, `APPROVED FOR DEVELOPMENT MERGE`; [CI #44948777072](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/44948777072) **9/9 SUCCESS** on the exact reviewed HEAD. This is synthetic source-specific evidence, **not** new real A–D or COMMIT-event identity proof.

**Feature and release boundaries remain:** CoreGuarded invoice/auto-Third-Party/sendmail (and unqualified PDF path) still **disabled** in server-side admission; **CF05-03..07** and CF03-NORMAL/21-job release matrix/ZIP/D hosted/R0-04/R0-07/Owner release GO remain OPEN. Real CF03-FAULT remains DEFERRED/OPEN. Keep [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22) open. For source-to-design mapping see [CF05 shared guard design](../cf05-shared-guard-design.md).

## Binding 2026-11-21 Owner amendment — ADR-06 and ADR-11

**Current authority: [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22).** This amendment *prospectively supersedes* the 2026-11-18 contribution-only permanent functional target and fault-inclusive release-test timing. The dated CF-00 architectural work below is **historical**; its D-01/D-02 transaction/security mechanisms and ADR-01–05/07–10/12 remain binding. Refer to the [current development baseline](../current-development-baseline.md) for the implementation/evidence matrix.

| Decision | Current binding position | Qualification boundary |
| --- | --- | --- |
| **ADR-06 — REVISED** | The v0.1.0 massaction must offer the **same complete business capability** in `CoreGuarded`, `EnhancedMySQL` and `MariaDBSnapshot`: contribution, member R16/Agenda, optional existing Third Party invoice, explicit new Third Party **only with invoice**, line/validation/link, optional verified PDF and contribution email/attachment. | **Target, not current permission.** CoreGuarded's existing invoice/customer/PDF/mail feature gates stay **disabled until CF05-01..07** implements and qualifies the complete path. No unreviewed UI-only unlock. |
| **ADR-11 — REVISED** | Mandatory focused CF05 implementation and independent exact-head transaction/security review; retained synthetic transaction/rights/session/security cases **including 21 PHP/Core CI jobs**; real Owner-authorized **normal/success** full workflows in A/B/C/D; independent DB/file/SMTP postconditions, Owner risk acceptance and exact new v0.1.0 ZIP/R0-04/R0-07 release gate. | Purposeful **real destructive fault tests** belong to later `CF03-FAULT` and remain **OPEN / NOT EXECUTED**. Synthetic fault safety and safe authenticated negative tests remain mandatory now; an unmitigated known P0/P1 never becomes accepted merely because real injection is deferred. |

**CF05 shared-guard implementation:** exactly **one active profile-specific guard** is carried from `MsmBookingService` through `MsmInvoiceService`/Third Party/line/validation checkpoints, inside one outer Core DoliDB transaction **per member**. Metadata/engine qualification is separate; CoreGuarded must not acquire an Enhanced P_S dependency. Expand the *actual* qualified Dolibarr V22/V24 Core/Agenda/trigger/side-effect call graph (including `COMPANY_CREATE`, optional `CONTACT_CREATE`, `BILL_CREATE`, `LINEBILL_INSERT`, `BILL_VALIDATE`). This is not a universal arbitrary-extension guarantee.

**Error/completion:** D-01 preflight → `SET TRANSACTION ISOLATION LEVEL REPEATABLE READ` → immediate Core BEGIN → locks → fresh reads; no intervening SQL. A single known-owned rollback is available **only** for a specifically qualified business error with demonstrably intact original context; SQL/deadlock/timeout/depth/connection/end uncertainty → **UNKNOWN/STOP**, no foreign rollback, automatic retry or compensation. Terminal `CHAIN`/`RELEASE` stops before module PDF/mail and further members; only a qualified acknowledged **nonterminal** completion plus dependable checkpoint permits separate post-commit document/delivery side effects.

**As-of implementation:** [PR #29](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/29) implemented bounded CoreGuarded contribution-only. [PR #32](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/32) was **squash-merged 2026-11-21** as `425980c43a51a9f27073133212f18825ac6da248` and provides **read-only witnesses/CI/evidence/privacy**, not CF05. Its independent review's `CHANGES REQUIRED` was addressed by explicitly accepted **F32-MR-01/P2 operational** and **F32-MR-02/P3 documentation** limitations, **not** by claiming fixes or independent approval. A/B/C real witness use still requires a privately verified target-user launcher; no such proof is yet recorded.

**Evidence/governance:** POS02-A1/A2 = bounded Owner **TECHNICAL PASS**, F32-W01-01 = independent **CLOSED** at earlier SHA, RB01-A = **INVALID/STOP as rollback proof** (real contribution persisted). Historical source claims and SHA values remain pinned; the new full-function A–D, 21-job, D exact ZIP and Owner release gates are **OPEN**. No physical-event identity claim in CoreGuarded (ADR-12); no whole-batch/SMTP atomicity/exactly-once guarantee. No release or live writes are authorized by this amendment.

---

**CF-00 OWNER-ACCEPTED – APPROVED FOR DOCUMENTATION MERGE (2026-11-18)**

CF-00 closure: [PR #23](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/23),
normal merge `6247704815cf532ec632616011133c646aa00005`, final reviewed HEAD
`d867c19c57b40e286ec08a9a935c4be138601a8a`;
[external verdict and Owner acceptance](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/23#issuecomment-6985938043):
**APPROVED FOR CF-00 WITH DOCUMENTED SCOPE**. F23-01–07 and K23-01 CLOSED.
The original drafting/review-request status below is historical and superseded for
CF-00; decisions, risk boundaries and unexecuted technical gates remain preserved.
CF-01 implementation status is recorded in the central validation; no Core-Guarded
booking, real D-01 integration or release approval follows from this closure.

## D-01 Owner addendum — 2026-11-18

**Evidence ACCEPTED WITH DOCUMENTED LIMITS; Option A — Bounded STOP CONDITIONALLY
APPROVED. Completion/session-checkpoint/logger gate OPEN / NOT EXECUTED.**

Authority: the Owner's D-01 AP1/AP2 decision and complete supplied independent
review, recorded in the [permanent PR #25 review/Owner record](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/25#issuecomment-6993488134)
and [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22).
External verdicts: **D-01 EVIDENCE ACCEPTED WITH DOCUMENTED LIMITS** and
**OPTION A VIABLE WITH CONSTRAINTS**. PR #25 merged as
`3bc9e4653778cdc870d79e2f5b7e025bc9ca036a`; reviewed source
`abb1b5cfea17a744840a744991c4425e639c2376`. The
[immutable experiment report](../d01-loaded-core-feasibility-v1.md) retains its
historical **D-01 CHANGES REQUIRED**, traces, hashes and PARTIAL case. This addendum
records a subsequent bounded Owner decision, not a retrospective technical PASS.

### Member completion and batch continuation

Regular Core transaction methods and the D-01 entry order remain mandatory.
Member outcome and permission to continue the batch are **independent** states:

| Acknowledged Core outcome | Member outcome | Batch / permitted next action |
| --- | --- | --- |
| COMMIT, qualified nonterminal NO_CHAIN context | SUCCESS under the existing application-level contract | Continue only after a successful qualified checkpoint, suitable post-end context and fresh next-member admission; separately permitted post-processing remains feature-gated. |
| ROLLBACK, qualified nonterminal NO_CHAIN context | Existing FAILED/SKIPPED business result, Core-acknowledged rollback | E10 continuation only after the qualified checkpoint/post-end conditions and unchanged per-member policy. |
| COMMIT, known terminal CHAIN/RELEASE | SUCCESS remains recognized | Immediate terminal STOP; only the proved connection-independent result checkpoint/HTTP response and controlled request end may follow. |
| ROLLBACK, known terminal CHAIN/RELEASE | Acknowledged rollback and FAILED/SKIPPED remain recognized | Same immediate terminal STOP; do not process another member. |
| End unacknowledged/uncertain, including lost ACK | UNKNOWN | STOP; no retry, compensation, foreign end or readback redemption. |
| Checkpoint fails after an acknowledged member end | Preserve that acknowledged DB outcome; result persistence is uncertain | STOP / E15 for acknowledged COMMIT; manual reconciliation, no rebooking/resume to reconstruct results. |

For E10/E12 (and E04/E05/E07 paths through E10), **an acknowledged end/depth0 alone
never permits continuation**. The suitable, qualified post-end state above is an
additional condition. E11/E13 remain UNKNOWN/STOP; E15 must not turn an accepted
COMMIT into unknown DB completion or an assumed rollback merely because saving its
result failed. A later expected RELEASE disconnect does not undo an already
acknowledged COMMIT; distinguish it from an unacknowledged end.

### Immediate terminal boundary

Classify the expected completion effect **before** the Core end. A bounded method
may read `@@SESSION.completion_type` during preflight, before the final SET RR;
it is continuation planning, **not a value whitelist**. Prove that the selected
Core/standard-Agenda path does not change the classified effect before completion.
The exact handling of unavailable/inconsistent classification must be defined and
independently assessed by the gate; this addendum invents no new admission bypass.

For known terminal CHAIN/RELEASE, latch STOP **on the acknowledged Core return**,
not at the next failed SET. **Before any further module SQL**, including next-member
profile/configuration reads, no subsequent DML, mail service, PDF processing or
next-member preflight is allowed. No post-end SELECT/ping to diagnose or redeem a
chained/released context. All required business/Agenda DML and postconditions finish
before end. The actual Core post-end `dol_syslog()` call occurs inside the Core end
path and therefore must itself be proved non-endangering; an adapter-side STOP
cannot retroactively protect against SQL already executed by that logger.

Only the qualified connection-independent result/session checkpoint and controlled
HTTP-result/request-end path may follow terminal completion. Do not assume all
session handlers are file-based or independent of the same DB context. A DB-backed
checkpoint could appear saved while joining CHAIN work later lost on disconnect.
Never clean the chained transaction with another Core rollback/BEGIN, drain counters,
or normalize session/global settings. Ordinary connection release at request end
is distinct from an explicit foreign-transaction cleanup attempt and must use the
qualified teardown/logger path. No general third-party/logger certification duty
is added; unknown extension behavior retains the existing compatibility reservation.

### Provisional partial processing and mandatory gate

The Owner explicitly accepts **controlled partial processing** provisionally: under
CHAIN/RELEASE normally at most one transactionally processed member per batch.
Clearly show the acknowledged member result and which selections were **not
processed**; do not label those as successful, unknown or generically failed.
Concrete UI/status representation is a later implementation duty, not a new enum
implemented here. A new attempt needs a new user action, fresh preview/eligibility
and confirmation; never automatically resume the consumed batch.

Safe multi-member booking across MySQL hosting configurations remains the goal.
Permanent CHAIN/RELEASE support/partial-processing acceptance and **Option C** require
later separate Owner decisions and qualification. Option C is not an approved
fallback. No own COMMIT/ROLLBACK commands alongside Core, second transaction manager,
completion whitelist, session/global normalization, extra rights or general P_S
prerequisite are authorized. EnhancedMySQL/MariaDB contracts and ADR-01–ADR-12/D-02
remain unchanged; Core-Guarded is not equivalent to Enhanced.

**Before CF-02 contribution DML**, successfully complete a focused independently
assessed completion/session-checkpoint/logger proof for **NO_CHAIN, CHAIN and
RELEASE**: pre-end classification stability; actual Core/logger end path; immediate
SQL-free terminal decision; independently safe durable result checkpoint and HTTP
readback/controlled end; acknowledged commit/rollback versus lost ACK; checkpoint
failure/E15; no next-member/profile SQL, mail/PDF or automatic retry after STOP.
Use approved isolated synthetic objects. This is a new bounded gate, not a repeat
of the old D-01 campaign or permission to execute tests in AP2. It is **NOT EXECUTED**.
CF-03 business/Agenda ON/OFF, HTTP replay, concurrency, cross-version and Enhanced/
MariaDB regression, exact-artifact and independent release/Owner-GO gates remain
open. Core-Guarded booking remains disabled; this documentary addendum is submitted
for independent documentation review and separate Owner acceptance before AP2 merge.

The following original CF-00 drafting text, historical NOT EXECUTED statements and
E01–E15 matrix are retained. This dated addendum narrows their completion/continuation
permission; it does not rewrite past evidence or alter unrelated contracts.

**Historical drafting/review context:**

Decision owner: repository Owner, 2026-11-18. Detailed design author: Codex;
independent F23 follow-up acceptance: **PENDING**. The initial independent review
of `8fd9ca321a8090ec8c91a084efffc06d943bf9e3` concluded **CHANGES REQUIRED BEFORE
CF-00 APPROVAL** on 2026-11-18; its verdict remains historical, not superseded by
self-approval. The Owner authorized the F23 dispositions and D-01/D-02 below on
2026-11-18 in the supplied CF-00 Follow-up instruction. This record implements CF-00
(documentation), not CF-01/CF-02. Normative future contracts below are design
requirements, not descriptions of deployed Core-Guarded behavior or test results.

## 1. Authority, baseline and problem

[Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22)
is the controlling Owner decision, read in full on 2026-11-18 (body updated
2026-11-18T14:21:48Z; zero comments at inspection). Its ADR-01–ADR-12 are reproduced
in section 2. It supersedes earlier universal physical MySQL proof requirements
**only for the new Core-Guarded profile**. Enhanced MySQL and MariaDB retain their
existing contracts. Owner risk acceptance is neither implementation nor release approval.

`origin/main` was fetched and verified against GitHub at
`48d196cfb04421d775ab4e6757083f6a755121df`, exactly the requested reference.
The documentation branch starts directly there; no release or spike commit is its
base. The existing local main checkout was not advanced or edited.

| Source | Provenance and use |
| --- | --- |
| [Current source](https://github.com/iLLixM/dolibarr-member-subscription-massaction/tree/48d196cfb04421d775ab4e6757083f6a755121df) | Implementation inspected: `class/databasestate.class.php`, `class/booking.class.php`, `class/invoice.class.php`, `lib/booking.lib.php`, `subscription.php`. No Core-Guarded adapter or profile configuration exists at this baseline. |
| [PR #13](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/13) | Merged; tested runtime `0999efb4aafca43d90ce12e38d370662ad0c63a1`, merge `aaf1f3786a4c9ea7e1707ad41cb821b0bb271e3d`. Existing observer/hardening, not Core-Guarded qualification. |
| [Existing qualification](../phase4-implementation-validation.md#pr-13-post-merge-closure-and-mdb-test-01--2026-11-17), [coverage legend](../test-coverage.md#mysql-iteration-1-evidence-2026-11-17) | Historical I1-05 failure, FIX01/I107 correction and MDB-01–09; exact profiles and exclusions remain unchanged. |
| [SH-01 report](https://github.com/iLLixM/dolibarr-member-subscription-massaction/blob/7df49867e001f2f56ce95d04c8ce5c4bfbd582c3/docs/sh01-mysql-restricted-feasibility.md), [evidence](https://github.com/iLLixM/dolibarr-member-subscription-massaction/tree/7df49867e001f2f56ce95d04c8ce5c4bfbd582c3/experiments/sh01) | Separate preserved `docs/sh01-feasibility-evidence` commit `7df49867e001f2f56ce95d04c8ce5c4bfbd582c3`; original spike `409a0f51a99c92155a159b0e12301ee09612230f`. NO-GO for the tested equivalent-observer construction; SH-02 not authorized. |
| [Issue #21](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/21) | Historical requirements and negative evidence retained. Its old implementation/release sequence is superseded by #22, not silently satisfied. |
| [Issue #20](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/20), [PR #19](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/19) | #20 remains the release tracker. #19 is CLOSED WITHOUT MERGE, preserved at `a74edb29ba6ca49af46c389f98c4bfd86cf161b6`; its prepared ZIP is historical, not a Core-first artifact. |
| [Dolibarr Core transaction methods](https://github.com/Dolibarr/dolibarr/blob/5c6c7ff6a5112413b1c27221c8d11750afad56f8/htdocs/core/db/DoliDB.class.php#L216) | V24.0.3 source pin `5c6c7ff6a5112413b1c27221c8d11750afad56f8`; logical nesting described in section 5. SH-01 records identical method bodies across its 15 inspected V22/V23/V24 pins; that is source analysis, not 15 real integrations. |

The current MySQL observer requires an instrumented event row and SELECT on
`performance_schema.events_transactions_current`. Missing SELECT caused the
restricted-host preview failure (1142) in #21. Current readiness also couples a
read-only preview to physical transaction admission. Shared-hosting usability is
therefore blocked even before a booking is attempted.

The Owner chooses a different, explicit risk contract using normal Core transaction
acknowledgements and shared business safeguards. This is a bounded reduction of
physical evidence, not a new proof that counters, markers or autocommit equal an
event observer. The objective is useful contribution booking with honest guarantees,
while retaining optional stronger evidence and previously qualified functions.

## 2. Binding Owner decisions — 2026-11-18

The following decision text is transcribed from #22. The last column is the design
trace, not an additional Owner decision.

| ID | Binding decision | Design trace |
| --- | --- | --- |
| ADR-01 | Core-Guarded, using Dolibarr Core transactions with targeted safeguards, becomes the regular MySQL transaction model, subject to qualification. | Sections 3–5; no current implementation claim. |
| ADR-02 | Keep the existing event-based MySQL Enhanced Observer as an **optional** profile. | Sections 3, 6; preserve observer behavior. |
| ADR-03 | Performance Schema SELECT is **not** a general module requirement; only Enhanced MySQL requires it. | Profile privilege matrix; never request a grant for Core-Guarded. |
| ADR-04 | Select the security profile by **explicit administrative configuration**, fixed before each booking operation. | Section 6; pin configuration identity and feature scope. |
| ADR-05 | **No automatic fallback** from configured Enhanced to Core-Guarded when Enhanced prerequisites fail. | Section 6; fail closed on 1142/instrumentation failure. |
| ADR-06 | Initially permit **contribution-only** in Core-Guarded; invoice/customer creation requires a separate qualification and decision. | Feature matrix; reject unsupported options server-side. |
| ADR-07 | Preserve the existing MariaDB snapshot observer and its qualified behavior. | Sections 3–5; no new event/isolation promise. |
| ADR-08 | Detected uncertainty leads to **UNKNOWN/STOP**, without automatic retries or compensating writes. | Outcome/rollback contract and risk register. |
| ADR-09 | No persistent journal, new module tables or migrations in v0.1.0. | Sections 5–7; session loss needs manual reconciliation. |
| ADR-10 | Decouple read-only preview from physical transaction readiness; booking remains gated. | Section 7; preview is not booking authorization. |
| ADR-11 | Separate focused implementation PR, real DB/HTTP tests, independent security/release review and Owner acceptance. | Section 9; CF-00 approval does not discharge later gates. |
| ADR-12 | The prior physical event-equivalence requirement is **explicitly superseded for the newly accepted Core-Guarded profile only**, not for Enhanced; residual risks must be stated accurately. | Evidence matrix, sections 5/8 and companion risk register. |

### Follow-up Owner decisions D-01 and D-02 — 2026-11-18

Authority: the Owner's supplied **CF-00 Follow-up – PR #23: Architecture Review
Findings F23-01 bis F23-07**, approved 2026-11-18, responding to the supplied
**PR #23 – Independent CF-00 Architecture & Transaction Security Review** at
`8fd9ca321a8090ec8c91a084efffc06d943bf9e3`. These decisions concretize the twelve
unchanged strategic ADRs. They do not claim independent follow-up acceptance.
Their durable GitHub reference is the decision comment in [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22).

| Decision | Owner-approved design and rationale | Safety limits | Future evidence (NOT EXECUTED) |
| --- | --- | --- | --- |
| **D-01 — APPROVED, 2026-11-18** | Preferred first Core-Guarded design: **Preflight → SET TRANSACTION REPEATABLE READ → Core-BEGIN → Locks → business reads**. The full SQL command is `SET TRANSACTION ISOLATION LEVEL REPEATABLE READ`. It serves as a bounded entry check and next-transaction setting without P_S. | Same DoliDB/native connection, no intervening SQL between SET and Core BEGIN. Reject failed SET/1568/known foreign context without foreign rollback. No actual-event isolation/identity/completion proof, no session/global normalization. An incompatible recipe must return to the Owner for an alternative decision. | Limited real loaded-Core/restricted-MySQL entry, ordering and behavioral isolation proof **before production contribution DML**; not required to execute in this documentation PR. Section 5 defines the contract. |
| **D-02 — APPROVED, 2026-11-18** | Support the known Core contribution path including conditionally enabled standard Agenda. Qualify the actual reachable call graph, including `ACTION_CREATE`. This bounds practical compatibility without scanning arbitrary installations. | Demonstrably transaction-endangering extension combinations are incompatible. Unexamined third-party hooks have an explicit compatibility reservation, not a universal inventory/test/block obligation. No general irreversible-side-effect guarantee; optional mail remains separately qualified. | Targeted real standard Agenda ON/OFF, nesting/error/rollback/postcondition evidence; known incompatible examples and operator limitations. Section 5 defines the boundary; no new tests here. |

[Finding dispositions](ADR-0001-review-handoff.md#finding-disposition-f23-01-through-f23-07)
record all seven review actions. ACCEPTED means the correction is adopted in this
draft; it is not a claim that an external reviewer has closed the finding.

## 3. Target boundary and evidence matrix

Working names: `CoreGuarded`, `EnhancedMySQL`, `MariaDBSnapshot`. These are design
names, not new PHP classes or configuration keys delivered by CF-00. Keep one outer
transaction owner in Booking; profile-specific admission/observation must not become
a second transaction manager. Invoice shares the same DoliDB/profile context only in
profiles where the invoice function is admitted. Preserve existing Enhanced/MariaDB
behavior wherever possible; do not reinterpret the old observer's `fresh/owned`
fields as physical guarantees for Core-Guarded.

| Property | Core-Guarded — Oracle MySQL | Enhanced Observer — Oracle MySQL | MariaDB Snapshot |
| --- | --- | --- | --- |
| Role | Future default for new installs, after qualification | Explicit optional profile; existing installs protected | Existing standard retained |
| Privileges | Normal Core/application schema rights; no P_S SELECT or global admin prerequisite | Schema rights plus required P_S SELECT **and working instrumentation** | Existing schema rights; `@@in_transaction` supported |
| Admission / ownership | D-01 successful SET immediately before acknowledged Core begin, expected depth and stable native connection; bounded entry control, no physical own-event proof | Calibrated idle, new own ACTIVE event, native/server/thread/event binding and fresh counter checkpoints | Own begin, depth, stable native handle/ID and active snapshot; no event identity |
| Same-connection replacement | May be invisible; an acknowledged end can refer to a replacement/no-op | Reject mismatching event at checkpoints; not continuous monitoring | COMMIT/BEGIN can evade endpoint snapshots |
| Actual isolation | D-01 ordered next-transaction RR recipe fixed by Owner; real loaded-Core qualification pending, no actual-event proof | Actual own ACTIVE event must report REPEATABLE READ | Existing branch has no equivalent actual-event isolation gate |
| Successful end | Core acknowledgement and application checks, explicitly limited evidence | Ack plus expected original ended event, then fresh idle | Ack/depth plus idle snapshot, not event-bound |
| Detected uncertainty | Latched UNKNOWN/STOP; no rescue by row readback or new connection | Existing latch/stop contract unchanged | Existing snapshot/stop contract unchanged |
| Overall evidence | NOT IMPLEMENTED / NOT QUALIFIED | Historical bounded real and synthetic evidence | Historical bounded real and synthetic evidence |

No profile promises continuous monitoring, exactly-once recovery, arbitrary-hook
atomicity or whole-batch atomicity. One transaction is scoped to **one member**;
earlier successful members remain committed if a later one stops.

## 4. Functional admission is separate from observation strength

| Function | Core-Guarded initial scope | Enhanced MySQL | MariaDB Snapshot |
| --- | --- | --- | --- |
| Read-only selection/preview | Planned without physical readiness; rights/entity/input/limit checks retained | Same separation; missing observer capability shown before booking | Same separation; snapshot readiness still gates booking |
| Contribution + Core end-date/R16 | Planned; CF-03 real restricted-MySQL qualification required | Preserve bounded qualified path | Preserve bounded qualified path |
| Standard automatic Agenda contribution event | Part of contribution-path engine, rollback and persistence qualification; no blanket hook allowance | Preserve conditional engine gates/evidence | Preserve conditional engine gates/evidence |
| Existing customer + invoice | **DISABLED**, including manipulated/server-held options | Preserve supported qualified configuration | Preserve supported qualified configuration |
| Automatic customer + invoice | **DISABLED** | Preserve supported qualified configuration | Preserve supported qualified configuration |
| Invoice PDF/ECM | **DISABLED** with invoice path; no qualification inherited | Existing post-commit path and its limits | Existing post-commit path and its limits |
| Contribution email | Not implicitly qualified by contribution-only DB tests; separate permitted post-commit/mail evidence required | Existing selected post-commit behavior/limits | Existing selected post-commit behavior/limits |

Changing the observer must not silently remove previously qualified MariaDB or
Enhanced invoice functionality. Conversely, their past positive invoice/PDF/SMTP
results do not admit those functions in Core-Guarded. Server-side policy must reject
invoice/autocreate before business calls even with direct POST, old sessions or
injected service options. **F23-06 / CF-02:** enforce this at every relevant module
entry: controller, Booking service, and directly callable Invoice/customer-creation
service methods; require a validated server-owned profile/feature context rather
than a caller-supplied assertion of permission. Missing/old context cannot default
to financial permission. This restricts this module, not general Dolibarr Core
customer/invoice functionality. UI explanation alone is insufficient. CF-05 requires
separate review, real integration and Owner decision before expansion.

## 5. Transaction contract proposed for independent review

### Trust, entry and permitted call graph — F23-01 / D-01

Trust honest server/driver reporting and the known Core path, not arbitrary hostile
PHP that rewrites guards. Keep ordinary Core transactions and one outer owner in
Booking. There is no second transaction manager, event-equivalence observer, mandatory
P_S access or universal physical-isolation monitor in Core-Guarded.

Complete all preflight SQL first: server-owned profile/feature binding, current
permissions/entity/batch limit, Core capabilities, relevant InnoDB engines and
supported connection settings. Require logical depth 0, the same DoliDB object,
actual mysqli handle/connection identity and autocommit=1; these are application
controls, **not physical ownership proofs**. Keep autocommit=1 as an entry
prerequisite, without an additional `completion_type` value whitelist or refusal
solely because that value is unreadable. Read-only settings checks occur before SET;
never normalize session/global settings. Relevant completion behavior remains a
later loaded-Core/MySQL test subject, not a general admission exclusion. Detected
unexpected completion, connection errors or uncertain transaction state still lead
to UNKNOWN/STOP; no completion variant is claimed already qualified.

The preferred first implementation **must** successfully execute
`SET TRANSACTION ISOLATION LEVEL REPEATABLE READ` immediately before
`DoliDB::begin()` on that **same** connection. There must be **no SQL statement of
any kind** between SET and the Core-issued BEGIN: no metadata/settings recheck,
connection-ID query, diagnostic SELECT, hook, SQL-backed logging or lazy loader
that inserts a query. Identity/depth can be compared locally without SQL. Do not
allow a callback/extension intervention in this entry segment. The normal Core
begin/driver path must be source-bound and its actual SQL ordering qualified.

A failed SET, 1568, known foreign context, nonzero/unexpected depth, intervening SQL
or connection mismatch denies entry: no Core BEGIN after failed admission and no
ROLLBACK to clear someone else's transaction. A transport/indeterminate error
latches UNKNOWN/STOP. If SET succeeded but the entry sequence is interrupted, stop
without opportunistic BEGIN/COMMIT/ROLLBACK; discard that request's connection/context
rather than leave a next-transaction setting available for later work. No automatic
retry or fresh-connection rescue of the rejected request. Acknowledged BEGIN must
return the expected outer depth 1 before locks/Core business calls are allowed.

This is a targeted, Core-compatible entry control: [MySQL SET TRANSACTION](https://dev.mysql.com/doc/refman/8.4/en/set-transaction.html)
is scoped to the next transaction and rejects use inside an active one (1568).
It does not establish a durable transaction identity or detect every later
same-connection COMMIT/ROLLBACK/replacement. In particular the accepted ADR-12
blind spots remain, rather than becoming unfulfilled equivalence requirements.
No privilege-driven fallback is allowed; Enhanced/MariaDB retain their own existing
admission and observation contracts.

### Sequence, isolation and nesting — F23-02 / D-01

Normative preferred sequence for the first Core-Guarded implementation:

1. Finish **Preflight**, including settings/engine/configuration SQL.
2. Execute **SET TRANSACTION ISOLATION LEVEL REPEATABLE READ** successfully.
3. Call outer **Core BEGIN immediately**, with no intervening SQL and no changed
   connection. Use the regular DoliDB API, not a parallel native transaction owner.
4. Acquire **member/type locks** (and relevant customer/product locks only in profiles
   where those features are enabled). No snapshot-establishing reads before locks.
5. Revalidate relevant engines **after all locks**, then perform fresh rights/entity/
   eligibility/fingerprint/overlap and **business reads**, Core writes, stored
   subscription/R16 postconditions and the admitted outer end. Keep the batch limit.

Enhanced and MariaDB keep their established ordering/qualification; do not apply
Core-Guarded's SET recipe to them. Enhanced still requires actual own ACTIVE-event
RR. MariaDB receives no new event-isolation requirement.

D-01 now fixes the design choice; it is no longer an undecided candidate. Its
**real loaded-Core/restricted-MySQL entry and ordering qualification is NOT EXECUTED**.
It is a later CF-01/CF-02 gate **before production contribution DML**, not a demand
to run real implementation tests in CF-00. It must cover foreign active transactions
at logical depth 0/1568 with no foreign end, acknowledged begin/depth, all four
session/one-shot isolation settings, intervening SQL/late overrides, relevant completion
variants under autocommit=1 and independent behavioral/read-view checks. Use an approved isolated lab
and actual loaded DoliDB/driver; SH-01's native positives and ordering counterexamples
remain evidence of primitives, not this missing Core integration. Report detected
violations and accepted undetectable post-entry changes honestly. No actual physical
event-isolation proof is required from Core-Guarded.

If this sequence is not sufficiently Core-compatible, stop and present a bounded,
consistent alternative for a **new Owner decision**. Do not replace it silently
with session defaults, global/session setting changes or Enhanced prerequisites.
Do not put SET/BEGIN into preview. Preserve FIX01's lock-before-metadata order and
Core R16 writes; locking reads do not refresh an already stale consistent read view.

At the pinned Core source, outer begin issues BEGIN; deeper begin increments a
counter. Inner commit/rollback only decrement it, without nested SQL savepoints.
Outer rollback resets depth even if SQL fails. Check each nested Core API's return,
error classification and restoration of expected outer depth 1 before further work.
An inner rollback does not undo prior business DML. For a classified ordinary
business failure with intact application context, perform the one admitted **outer**
rollback below. Unbalanced/unknown depth or detected connection/end replacement
latches UNKNOWN/STOP. Never drain counters, start replacement transactions, equate
inner commit with member completion, or let a generic negative Core result hide an
unclassified SQL/connection error.

### Deterministic Core-Guarded error and outcome matrix — F23-03

These are application decisions, not new implemented states or a claim that physical
ownership has been proved. Enhanced keeps fresh event-bound rollback/completion;
MariaDB keeps its snapshot-based contract. Neither is weakened by this matrix.

For Core-Guarded, **RB** (rollback admitted) means: this operation recorded successful
D-01 entry and acknowledged outer BEGIN; same DoliDB/native identity; expected depth
1 restored after nested calls; no detected intervening end/replacement, connection
fault, context-invalidating error or attempted COMMIT. A qualifying failure is either
a known local business/orchestration failure without hidden SQL uncertainty or the bounded **S**
statement-error class. RB permits **exactly one regular DoliDB rollback**. It does
not require or imply an Enhanced physical event/ownership proof.

**S** is a finite implementation mapping of native error + failing Core/SQL call +
qualified settings/behavior that is known to affect only that statement, with error
information retained before later Core calls can overwrite it. A code or unchanged
counter alone never establishes S/RB. Unsupported, missing or masked error details
are unclassified. CF-02 must document/test each supported mapping; **none is claimed
qualified by CF-00**. Until a mapping is qualified, use the unknown branch. No dynamic
catch-all, admin override of uncertainty or automatic repetition of the failed SQL.

Rows are applied by phase and then uncertainty precedence: a detected identity/depth/
transport/end inconsistency always defeats a recoverable business/S classification.
`failed`/`skipped` below describe the business result; continuation never retries the
failed member. A request-wide invalid configuration or session/checkpoint failure
stops the run even if an admitted rollback succeeds; only a handled per-member
failure may continue to the next member.

| Case / detected condition | Decision and allowed action | Batch / side effects |
| --- | --- | --- |
| E01 — Before BEGIN: ordinary per-member rights/eligibility refusal or stale fingerprint, no attempted transaction/write | Refuse/skip; no rollback. | May continue independent eligible members under unchanged request/profile policy; never retry the refused member automatically. |
| E02 — Entry/preflight/configuration rejection, including SET failure/1568, known foreign transaction, interrupted SET→BEGIN or unsupported settings | Reject admission and stop the run; no foreign end, no BEGIN after failed SET. Unknown identity/transport/state uses UNKNOWN/STOP. | No module business DML or delivery; no clearing transaction, default substitution or fresh-connection retry. |
| E03 — BEGIN failed/unacknowledged, even if counter appears unchanged | UNKNOWN/STOP; do not assume a rollback-capable context. | No rollback/retry or later member/PDF/mail. |
| E04 — Ordinary business/local application failure at plausible intact context, RB holds | One Core rollback; E10/E11 determine its outcome. Nested failure must be classified, not just negative return. | On E10: failed/skipped, continue next member as existing business contract permits. No post-commit side effects for this member. |
| E05 — Known bounded SQL statement error, S and RB both hold | Do not repeat statement; one Core rollback, then E10/E11. | Continuation only after E10. Unqualified mappings go to E08. |
| E06 — Deadlock 1213 (server reports transaction abort) | Treat original context as invalidated: UNKNOWN/STOP under this conservative profile; no module rollback, counter draining or replacement BEGIN. | No automatic retry or subsequent member/delivery; diagnostic may record reported server abort without claiming own end proof. |
| E07 — Lock-wait timeout 1205 | If exact call/error is qualified S, `innodb_rollback_on_timeout=OFF` is known for that qualified setting profile, and RB holds: E05. If ON, unknown/unreadable, unqualified or other invalidating evidence: E08. | Never infer statement-only behavior from errno/depth alone; no retry. Continue only through E10. |
| E08 — Other/unclassified SQL error, possible server transaction abort, missing error facts or detected uncertain state | UNKNOWN/STOP; no rollback on a possibly foreign/replaced context. | No automatic retry/compensation, later members or delivery. |
| E09 — Core nesting/depth mismatch, connection loss/replacement or known foreign/premature end | UNKNOWN/STOP; no counter-draining rollback loop or ownership transfer. | No later business/delivery; a plausible later read or new connection cannot redeem this context. |
| E10 — The single admitted outer rollback acknowledged, depth 0 and same identity, no contradictory error | **Core-acknowledged rollback**, not independent event completion; record failed/skipped business result. | May continue next member under unchanged policy. No retry, PDF/mail or claim that arbitrary external effects were undone. |
| E11 — Rollback failed/unacknowledged or E10 checks contradict it | UNKNOWN/STOP even if Core has reset its counter to 0; do not call rollback again. | No continuation, compensation or delivery. |
| E12 — All business checks passed; admitted outer Core COMMIT acknowledged, depth 0, same identity, no contradictory error | **Application-level success under Core-Guarded**; do not require an original physical end event. | Persist accepted result before separately permitted post-commit actions; may continue after checkpoints. |
| E13 — COMMIT attempted but failed/unacknowledged or contradictory completion facts | UNKNOWN/STOP; no fall-through rollback, replacement transaction or readback redemption. | No rebooking, retry, compensation, later member or new delivery. |
| E14 — Session/checkpoint save fails before BEGIN | Stop before writes; never start booking without durable consumed state. If a checkpoint failure occurs in an admitted open pre-COMMIT context, use E04/E10/E11 for its end but still stop the run; after an attempted COMMIT use E13/E15. | No delivery; preserve consumed/uncertain session history, not a reopened confirmation. |
| E15 — Session/checkpoint save fails after E12 accepted COMMIT | DB outcome remains application-accepted; result persistence/delivery state is uncertain, **STOP**. Do not relabel DB as rolled back or retry to reconstruct a result. | No further member or new delivery; manual reconciliation. Already completed external effects cannot be undone or automatically repeated. |

[MySQL's InnoDB error rules](https://dev.mysql.com/doc/refman/8.4/en/innodb-error-handling.html)
explain why 1213 and 1205 need different classes: deadlock aborts the transaction;
timeout scope depends on rollback-on-timeout configuration. The matrix deliberately
forbids the automatic retries often suggested for generic applications. The
[completion-type reference](https://dev.mysql.com/doc/refman/8.4/en/server-system-variables.html#sysvar_completion_type)
documents the autocommit=1 exception. Retain the later bounded test of relevant
completion variants, without a mandatory completion-value restriction or session/
global workaround. These are reference semantics and future test obligations,
not measurements in this PR or qualification of every completion variant.

Core-Guarded still may miss a premature opposite end/same-connection replacement
and accept a later no-op acknowledgement. This accepted ADR-12 residual limitation
is **not** an open requirement to reproduce Enhanced identity/isolation/completion
proofs. E10 therefore cannot prove that no earlier writes were committed; E12 is
application success, not independent original-event success. UNKNOWN/STOP governs
**detected** ambiguity. No later fresh session or plausible row readback may convert
an unknown result into automatic success. No journal, auto-resume or compensation.

### Known Core/Agenda path and extension boundary — F23-04 / D-02

The supported first scope is the known Core contribution path and conditionally
active **standard Agenda**, subject to later targeted qualification. At pinned Core
`5c6c7ff6a5112413b1c27221c8d11750afad56f8`:

- [Adherent::subscription()](https://github.com/Dolibarr/dolibarr/blob/5c6c7ff6a5112413b1c27221c8d11750afad56f8/htdocs/adherents/class/adherent.class.php#L1915)
  calls [Subscription::create()](https://github.com/Dolibarr/dolibarr/blob/5c6c7ff6a5112413b1c27221c8d11750afad56f8/htdocs/adherents/class/subscription.class.php#L167).
  Core updates the member end date in this sequence and propagates success/error;
  retain actual Core R16 observation/postconditions, not replacement SQL writers.
- Subscription creation invokes `MEMBER_SUBSCRIPTION_CREATE`. When Agenda and its
  relevant auto-event are enabled, [ActionsAuto](https://github.com/Dolibarr/dolibarr/blob/5c6c7ff6a5112413b1c27221c8d11750afad56f8/htdocs/core/triggers/interface_50_modAgenda_ActionsAuto.class.php#L1106)
  creates a standard `ActionComm`, event/member linkage and actor/resource assignment.
- [ActionComm::create()](https://github.com/Dolibarr/dolibarr/blob/5c6c7ff6a5112413b1c27221c8d11750afad56f8/htdocs/comm/action/class/actioncomm.class.php#L770)
  in turn dispatches **ACTION_CREATE**; its nested return/error/depth and any actual
  reached effects belong to the qualification boundary, not just the first trigger.

Keep conditional InnoDB checks for `adherent`, `adherent_type`, `subscription` and,
when that standard path is active, `actioncomm`/`actioncomm_resources`, before BEGIN
and after locks. Add path-specific checks only for actually reached additional
writes (for example populated extra fields); do not claim arbitrary extension
coverage. Standard Agenda OFF must not require unrelated Agenda tables. Qualification
must record actual call order, triggers, depths, event/actor links, stored R16 values
and atomic rollback after real contribution/Agenda DML. This is **NOT EXECUTED for
Core-Guarded**, and no live configuration inspection/change is performed here.

**Binding compatibility treatment under D-02:** demonstrably transaction-endangering
extension combinations (known foreign transaction control, implicit-commit DDL or
other established violation of the admitted context) are **incompatible** with this
module path and must not be knowingly run as a supported combination. If detected
before entry, reject; if detected during work, apply E08/E09 without foreign cleanup.
Known irreversible effects are excluded from any rollback/atomicity promise and
require separate authorization/qualification if intentionally supported. Do not
knowingly execute a path with irreversible external effects outside the authorized
run scope: reject it before business calls, or stop for separate disposition if
identified during work (E08/E09 when the DB context is uncertain). This is not a duty
to discover every extension. Unexamined
third-party hooks, including handlers reached via ACTION_CREATE, carry an explicit
**compatibility reservation**: they are not thereby certified safe or universally
blocked. There is **no general inventory, inspection or blocking obligation for all
installed third-party modules**. Operators must heed documented known incompatibilities
and investigate effects relevant to a reported combination; no whole-installation
scanner or mandatory module whitelist is introduced. No guarantee covers arbitrary
foreign filesystem/SMTP effects. Optional contribution email remains separately
qualified and only after an accepted DB result, with independent status/checkpoints;
Core-Guarded invoice/customer/PDF stay disabled. No side effect is repeated automatically.

## 6. Explicit selection, confirmation binding and upgrades

Binding **F23-05 requirements for CF-01** (exact mechanism not implemented here):

1. Detect supported family/capabilities without a privilege-based fallback. New MySQL
   installs explicitly initialize/present Core-Guarded as default **after release
   qualification**. MariaDB continues Snapshot. Unsupported/unknown configured
   profile or family mismatch refuses booking with a clear reason.
2. Existing MySQL installations retain Enhanced. An absent setting must not be
   interpreted as evidence of a new install. Where origin cannot be distinguished,
   require explicit administrator migration choice before booking, preserving old
   Enhanced semantics meanwhile or refusing if its prerequisites fail. No silent
   conversion on upgrade, reactivation, 1142, lost consumer or instrumentation failure.
3. Store normalized family/profile, configuration revision and admitted functional
   scope with each server-held preview/confirmation. Bind them to owner/entity and
   request revision. Re-resolve and compare before consuming a booking and before
   each member; a changed profile invalidates unconsumed confirmations and requires
   fresh eligibility/preview/consent. Old sessions lacking binding must reconfirm.
4. No mid-transaction profile change. If an admin change is detected between members,
   stop remaining work and require a new run; do not relabel prior outcomes. If
   detected while a member is open, freeze further business calls and end only using
   the original context's safe rollback admission, otherwise UNKNOWN/STOP. Precise
   session/config race handling belongs to CF-01; never switch an active adapter.
5. Configuration is server-controlled and admin-only. Neither direct POST, role,
   permission failure nor a saved historical session can enable invoice/autocreate
   in Core-Guarded. Keep a profile/evidence label on completed outcomes so later
   settings do not retrospectively promote their guarantees. No secret SQL/identity
   data should be exposed to users.

CF-01 must implement authoritative server-side configuration revision/compare
semantics for concurrent admin and booking requests. Cached Dolibarr constants are
**not** proof of a current revision. An atomic compare at the admission boundary or
a precisely defined per-member snapshot/linearization point must prevent stale
profile/feature admission, invalidate pending confirmations and preserve the active
member's original context. The implementation must specify and test races, rather
than require global serialization of all administrators/batches. These are deferred
CF-01 implementation/qualification duties, not additional CF-00 blockers.

No new module tables, journal or schema migration is permitted. Existing module
configuration facilities may be designed in the separately authorized implementation;
CF-00 does not write constants or choose a production migration mechanism.

## 7. Read-only preview versus booking readiness

At this baseline `msmRequireBookingDatabase()` calls `beforeBegin()` and engines
for preview/confirm; `subscription.php` invokes it before evaluation and booking.
Done/processing result display already bypasses new-operation readiness. CF-01 must
separate these responsibilities without making a preview into transaction proof.

Read-only preview retains authentication, object/entity access, CSRF on edits,
server-held options, validation/escaping/translations, revision and batch limits.
It must not BEGIN, SET transaction/session settings, acquire business row locks,
consume a booking confirmation or execute writes/side effects. Missing Enhanced
privileges need not suppress readable preview; show a clear booking-unavailable
reason/profile/scope and deny the final booking server-side. A snapshot of capability
is advisory and must be checked afresh at booking. Stale profile/feature bindings
require a new confirmation. Readability of stored completed/processing results and
replay rejection must survive unavailable new-operation readiness; never resubmit
business work to render a result. Retain result-link access checks.

## 8. Risk acceptance, alternatives and rule conflicts

The [risk register](ADR-0001-risk-register.md) is this ADR's threat analysis, not a
second requirement matrix. All 71 existing IDs/evidence assignments and historical
results remain untouched. The Owner accepts weaker physical proof for Core-Guarded:
undetected same-connection replacement, unbound original completion and lack of
actual-event isolation observation. Foreign entry is addressed by D-01's mandatory
bounded SET→BEGIN admission; its loaded-Core proof remains a later gate, not an
Owner waiver or a demand for event equivalence. That acceptance is bounded by
retained business controls and future tested operating assumptions, not approval of arbitrary hooks,
false success after detected faults or unqualified invoices.

| Alternative | Disposition |
| --- | --- |
| Require Enhanced P_S universally | Retained optional stronger profile; rejected as universal rule by ADR-01/03/12 because of restricted-host availability. |
| SH-01 SAVEPOINT/connection/isolation equivalence construction | Historical NO-GO retained. Marker recreation/rotation, isolation ordering and ambiguous completion refute the tested equivalence. No SH-02 implementation authority. |
| Silently switch on 1142 or instrument loss | Rejected by ADR-04/05; no catch-and-retry downgrade. |
| Port MariaDB snapshots directly to MySQL | Not equivalent; MySQL lacks that variable in the tested profile. No inheritance of MariaDB's accepted guarantees. |
| Journal, new tables, XA/proxy/driver controller or duplicate SQL business writes | Outside this authorized design; ADR-09 preserves session architecture and Core APIs. No such implementation supplied. |
| Reject all normal completions | Safe refusal alone is not useful contribution support; not the Core-first goal. |

[AGENTS.md](../../AGENTS.md) currently states universal shared-observer/G0/event
ownership, actual-event isolation, fresh provable rollback and confirmed-COMMIT
requirements. They accurately constrain today's code and Enhanced; they cannot be
copied unqualified into Core-Guarded. The minimal pointer added there scopes the new
design and pending review without changing the existing implementation contract.
The prohibition on pre-lock MySQL read views remains shared. D-01 fixes the preferred
next-transaction isolation setup in section 5; this is
not a general exception allowing session/global workarounds. F23-07 further clarifies
rollback/post-commit evidence wording without changing Enhanced/MariaDB behavior.

The older [invoice architecture](../phase4-invoice-architecture.md),
[implementation plan](../phase4-invoice-implementation-plan.md), validation history,
README and user-facing prerequisites will need a **scoped** consistency pass after
accepted implementation. Do not rewrite them now as if Core-Guarded existed. The
old instruction against parallel architecture documents is satisfied by this Owner-
requested successor ADR with explicit historical/source links, not a competing matrix.

## 9. Compatibility and next gates

Policy from #22/#20: Dolibarr V22/V23/V24, PHP >=7.4 subject to Core's own minimum,
direct mysqli, MariaDB >=10.11 and Oracle MySQL >=8.4; PostgreSQL excluded. Minimums
are intended support boundaries, not blanket version/profile qualification. Current
real profiles include V24.0.3/PHP8.2.35/MySQL Community8.4.13 Enhanced and
V24.0.3/PHP8.2.35/MariaDB10.11.19 Snapshot, with historical V22 evidence recorded
separately. V23 has synthetic/source coverage, **no real integration claim**.
Core-Guarded has **no product synthetic or real Core integration qualification**.

The focused independent F23 follow-up must assess the corrected contracts and
explicit deferrals in the handoff; missing future implementation tests alone do
not prohibit documentation acceptance. Independent detailed architecture acceptance
is still PENDING. No implementation starts in CF-00.
CF-01/CF-02 then require separate commission; CF-03 requires existing synthetic
21-job non-regression plus real restricted-MySQL/Core/HTTP and MariaDB/Enhanced
regression with exact profiles, faults, postconditions and honest blind spots.
Synthetic success cannot substitute for those tests. Contribution mail and any
other side effects require specifically permitted tests and separate outcomes.

#22 remains a v0.1.0 release blocker. Do not reopen/merge #19 or build on its source
branch. After accepted implementation on main, prepare a **new** release branch/PR;
selectively reuse validated builder/allowlist/docs only after comparison, rebuild
ZIP/manifest/SHA-256, execute R0-04 exact-artifact integration and R0-07 independent
release review, then obtain separate Owner GO under #20. Former ZIP SHA-256
`61e4ad4bc40a31d87e265b0a05433403d77bbc177899c5f58b7416d5a3653ed6`
remains historical and unchanged, not Core-first release evidence. No tag, release,
general production approval or new database guarantee is granted by this ADR.
