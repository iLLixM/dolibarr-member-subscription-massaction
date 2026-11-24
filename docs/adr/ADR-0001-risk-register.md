# ADR-0001 — Transaction profile risk register

## Current Owner-accepted residual-risk disposition – post-PR #38

**Authoritative post-PR #38 state (2026-11-24):** [PR #38](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/38) was independently reviewed and **squash-merged** into `main` as `0998fbaf58155cca04d31ab99c6bb47420e53c72`; reviewed HEAD `b15b93580f995fb05a696a125c2a5fd995dd4bb3` has **identical Git tree** `a6e7256fb1572361c4dd4d850124ca7a6afff566`. The independent verdict was **APPROVED FOR DEVELOPMENT MERGE WITH DOCUMENTED LIMITATIONS**, with exact-source Standard 9/9 + Full 21/21 + Hosted 1/1 (**31/31**) synthetic CI PASS. Phase 4 development is merged; **Phase 5 real A–D integration, CF05-07, package, hosted and Owner release acceptance are NOT complete**. All older dated DRAFT/finance-default-deny/core-source-allowlist statements below are historical and not current requirements.

**Binding policy:** [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22) and [Owner Variant B](../owner-core-parity-2026-11-22.md): Dolibarr Core parity plus Massaction-specific safeguards. Full contribution/optional invoice and conditional linked third party, validated line and relation, post-COMMIT PDF and optional mail/verified attachment are implemented and enabled for `CoreGuarded`, `EnhancedMySQL`, `MariaDBSnapshot`. Normal Core numbering/models/Agenda/triggers/extensions are accepted; **do not reintroduce** Core/trigger source SHA allowlists, a separate `PROV` veto or blanket CoreGuarded-finance default deny. Preserve server-owned rights/entity, fresh fingerprint/full confirmation, original consumed one-shot, same active owned transaction guard, transactional-table/readbacks, acknowledged completion, conservative `UNKNOWN/STOP` and no automatic retry/foreign rollback. `F36-02/P2` is OWNER-ACCEPTED **Core filesystem residual risk**, not technically removed.

**Independent disposition:** F38-01–06 `CLOSED`; F38-07–09 `SUPERSEDED BY OWNER VARIANT B` (not technically closed); F38-10–13 `INDEPENDENTLY CLOSED`; F38-14/P2 `INDEPENDENTLY CLOSED` (server-confirmed `manualend` restored only after fresh fingerprint revalidation); F36-01 historically closed. **Separate real incident:** `DOLIENV-ACC-01` in B remains `UNKNOWN/STOP`, diagnostic subscription #3 and rollback unconfirmed; lack of a persisted record does not prove rollback ACK. No replay/compensation. The later local B finance cases are Owner-accepted positive results; the historical incident is unchanged.

| Risk | Treatment after PR #38 |
| --- | --- |
| **F36-02/P2 pre-COMMIT PROV rename / filesystem residual** | **OWNER-ACCEPTED CORE RESIDUAL RISK**, not source-level eliminated; reassess operating consequences in CF05-07 |
| Unknown/late Core transaction effects | Preserve per-profile bounded observation, `UNKNOWN/STOP`, no blind rollback, retry or false reconciliation |
| PDF/Hook/SMTP after-effects | Post-COMMIT checkpoint, finite evidence states, verify attachment/recipient; handoff ≠ delivery |
| B historical `DOLIENV-ACC-01` | Original `UNKNOWN/STOP` and unconfirmed rollback retained; new B finance cases PASS within recorded scope |
| Hosted D Web/CLI and filesystem execution | Focused Web contribution/session/replay PASS on provider defaults; separate CLI readback; finance/PDF/mail, wider preflight/security and exact ZIP OPEN |
| Backups / Recovery | A/B/C backup integrity and permissions verified; D independently scoped |
| Protected operator launcher `F32-MR-01` | Local protected readiness PASS for recorded cases; per-case target-user preflight still required; D focused roles/reader accepted, wider gate OPEN |

**Current Phase 5:** Local accepted real cases on `ba5c9a3121754fcbd4259bb8906ad1dd17f1ce00`: protected readiness 21 checks, initial finance/PDF PASS within scope, additional non-mail 8/8 PASS, local B mail/verified attachments 3/3 PASS (capture only). Hosted D independent checkout and dedicated schema, Dolibarr 22.0.5, Oracle MySQL 8.4.13, Web PHP 8.4.28 CGI/FastCGI / CLI 8.3.37: four roles, two separate contribution-only cases, persisted result reload, consumed replay refusal and separate SQL readback PASS within scope on `0adc259d4739b44f9b1fb3a49574d7e1c318132c`. D finance/PDF/mail, broader live cases and exact ZIP OPEN; A/C mail OPEN. Modified OD-P5-02 accepts authorized summaries within scope, not independent external reproduction. See [current baseline](../current-development-baseline.md) and [environment evidence](../dolienv-environments.md).

**Operational/release boundary:** Phase 5 PARTIAL; per-case Owner GO, protected target-user F32-MR-01 preflight, actual source/profile/role binding, fresh fixtures, controlled backups and independent readbacks required. Full CF03-NORMAL A–D, remaining A/C mail and D finance/PDF/mail, wider live security/configuration, CF05-07/CF03-RISK, R0-03, R0-04, R0-07 and separate Owner Release GO remain OPEN / NO-GO (R0-04 BLOCKED pending exact package qualification). Historical B02 mail/attachment and DOLIENV-ACC-01 UNKNOWN/STOP (latter rollback unconfirmed) unchanged; no replay/compensation. CF03-FAULT destructive tests DEFERRED. No new authorization.


**PR #45 risk/test scope:** PR #45 removes only the unnecessary directory-listing/readability prerequisite in `MsmFileBookingCheckpoint::requireSession()`. The PHP `files` handler, nonempty session ID, plain absolute existing writable directory, canonical binding and actual reopen/read/write/close/readback remain required. A known session file may be accessible even when the directory cannot be listed (e.g. owner mode `0300`). Preserve the original consumed confirmation, one-shot/processing/result persistence, owned guard and finance/post-COMMIT checks. Do not widen provider permissions to make a test pass. The six concrete Core/model/PDF file readability checks remain a separate contract. Independent review identified no new P0–P2; F45-01/02 remain non-blocking test precision work; F45-03 documentation follow-up awaits merge. [current baseline](../current-development-baseline.md).

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

> **Post-PR #38 corrective review checkpoint (2026-11-22):** The independent initial PR #38 review on `2ef26893ec9aca5dd78d9946a64e7f3ee8c41de8` found **F38-01..04/P2 + F38-05/P3** and issued `CHANGES REQUIRED BEFORE DEVELOPMENT MERGE`. Focused fail-closed PDF/Hooks/Direct-Evidence/CI-trigger/status-matrix changes are being implemented under the still-closed CoreGuarded finance gate; **independent finding closure PENDING**. See [Phase-4 corrective record](../cf05-phase4-postcommit-offline-evidence.md). **F36-02/P2 remains OPEN / CF05-06**; no real-test/production/release authorization or ADR revision is implied.

## PR #36 merged – current CF05 financial risk disposition (2026-11-21)

[PR #36](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/36) was **SQUASH-MERGED** on 2026-11-21 as `d0b15785c60b74aa7ad59a1c687db94193d159c6`; independently reviewed source `33cb3da3b389f9df2870bc8c6223c86a959cb467` and merge have **identical Git tree `6cc4834b9fe5100aca99126885791b3aeff12fef`**. External verdict **`APPROVED FOR DEVELOPMENT MERGE`**, **F36-01/P1 CLOSED** and **F36-02/P2 OPEN / CF05-06 REQUIRED**, no new P0–P3 delta findings. [standard CI 9/9 PASS](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/44956004981) and [hosted PHP8.3/V22.0.5 CI 1/1 PASS](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/44956004947) refer to the **exact reviewed head**; their positives are **synthetic**, not new real full-finance test evidence.

| Risk | Current disposition | Mandatory follow-up |
| --- | --- | --- |
| **F36-01/P1 premature CoreGuarded finance enablement** | **CLOSED** by independent exact-HEAD review: actual production profile retains `booking=true` and **`invoice/autocreate/sendmail=false`**, not overrideable by ordinary admin/HTTP; positive finance tests are isolated synthetic fixtures | Future separate, independently reviewed and Owner-authorized admission change only after qualification |
| **F36-02/P2 conditional `Facture::validate()` `PROV` filesystem rename** | **OPEN / CF05-06 REQUIRED**, expressly non-blocking for *development merge under closed finance gate*, NOT a safety PASS | V22/V24 source/configuration/reachable Core, Agenda, customer/contact/trigger and InnoDB/FS effects; mitigate or exclude unsafe supported configurations **before finance admission** |
| Typed pre-Core finance business rollback | Bounded `MsmQualifiedCoreFinanceBusinessError` from `finance_precheck` and previously qualified reasons only with demonstrated original owned/fresh transaction; arbitrary Core/SQL/hook/transport errors stay `UNKNOWN/STOP` | Real supported Core call-graph qualification, no automatic compensation/retry |
| Post-COMMIT PDF/mail | Acknowledged suitable nonterminal outcome and independently re-read file-session checkpoints; terminal CHAIN/RELEASE and uncertainty STOP | Real PDF/hash/path/recipient/SMTP checks, explicit non-DB-atomicity; no resend |

**Historical addenda below preserve their point-in-time status.** In particular, earlier text describing PR #36 as 'independent review pending' was accurate only before the independently reviewed and merged final head. F32-MR-01 private launcher/read-only preflight and ADR-12 non-equivalence remain binding; no Owner release GO.

## PR #36 corrective F36-01 and deferred F36-02 risk status (2026-11-21)

**Development correction, not yet independently approved or merged:** [PR #36](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/36) implements the CoreGuarded customer/invoice/rollback and post-COMMIT PDF/mail paths, but its original independent Security/Transaction review yielded **`CHANGES REQUIRED`** on HEAD `ea25de75a6277e1d054bed3f622551a3d58c3d1b`: **F36-01/P1** prematurely opened actual server financial admission; **F36-02/P2** left the conditional standard-Core filesystem effect unqualified. A focused corrective commit now makes `MsmTransactionProfile::current()` **default-deny** invoice, auto-Third-Party and mail for **all production CoreGuarded profiles**. Contribution remains available; EnhancedMySQL and MariaDBSnapshot retain the existing financial scope. Positive CoreGuarded financial logic is covered solely by a named synthetic test fixture and **is not** reachable via runtime admin configuration or client options. F36-01 is **FIX IMPLEMENTED / INDEPENDENT VERIFICATION PENDING**, not claimed CLOSED.

**F36-02/P2 remains OPEN (CF05-06 qualification):** Dolibarr V22.0.5 and V24.0.3 contain a conditional `Facture::validate()` rename of a provisional `PROV` invoice document directory before nested Core COMMIT and the outer Booking COMMIT. This is a **non-transactional filesystem side effect**. The review has **not** proved actual reachability in the module's fresh-invoice path. Before any server finance activation, separately qualify V22/V24 PROV-directory reachability and all standard Core/Agenda/contact/trigger/engine paths and apply a targeted mitigation/configuration exclusion if necessary. Do not automatically compensate/retry or describe the path as already safe.

**No finance, real testing, production, release or Owner merge authorization is implied.** Full CF05-06/07, CF03-NORMAL A–D, 21-job release CI, fresh v0.1.0 ZIP and hosted D acceptance remain open; F32-MR-01 private target-user launcher/read-only preflight remains binding. Historical PR34 merged state and its evidence below are unchanged.
## CF05-01/02 post-merge risk disposition (2026-11-21)

[PR #34](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/34) implemented and independently validated the **shared active guard and readback boundaries** on reviewed HEAD `11d7dd3cf4bdcf831aa87782b7466d2378fc733f`, squash-merged in `dc7a172bfd476467d7980cbf16a2657cfc1e729c`; [standard CI 9/9 PASS](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/44948777072). F34-01/P2 (Rollback-stage/guard/profile negatives) and F34-02/P3 (per-readback ownership checks) are **CLOSED**, with **no new P0–P3 findings** in the targeted final review. The effective technical protections were improved; the original risks are not erased or called release-qualified.

| Risk boundary | After PR #34 | Remaining mandatory gate |
| --- | --- | --- |
| Multiple Invoice/Third Party Core APIs under the wrong context | **Same** DoliDB-bound profile-active guard injected by Booking; before/after ownership checks at Core create/line/validate and persisted customer/invoice readbacks | **CF05-03/06** admission and real V22/V24/Agenda/trigger/InnoDB call-graph verification; CF03-NORMAL A–D |
| Incorrect CoreGuarded financial rollback classification | Positive allowlist only for qualified `preflight`/`subscription` stages, other stages `UNKNOWN/STOP` with no blind rollback; negative tests independently accepted | **CF05-04** finite source-qualified financial rollback origins and integrated synthetic negative tests |
| Unverified PDF/SMTP or ambiguous completion | **Not enabled** by guard refactor; terminal `CHAIN`/`RELEASE` and unknown remain STOP | **CF05-05** correct post-commit checkpoint and independent file/mail acceptance |
| Observer/evidence asymmetry | No second transaction manager and no CoreGuarded P_S requirement. Enhanced and MariaDB retain their distinctive observation strengths | ADR-12 residual remains accepted, not physically equivalent; separate risk and release acceptance |

**Inherited operations:** F32-MR-01/P2 remains **OWNER ACCEPTED / NOT FIXED**; future real A/B/C witness use remains prohibited before private target-user launcher and verified read-only preflight. F32-MR-02/P3 and F33-DOC-03/P3 remain separately recorded historical documentation limitations. No old POS/RB case replay, live finance or release GO follows from this development merge.

## Current risk disposition and new financial scope (2026-11-21)

**Current authority: [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22)** and the [current development baseline](../current-development-baseline.md). ADR-06 and ADR-11 were revised *prospectively*: full contribution/Third Party/invoice/PDF/mail is a **v0.1.0 delivery objective on all three profiles**, not current CoreGuarded implementation. The historical R01–R15 analyses below remain valuable but their `U` and `G` labels, prior contribution-only limit and 2026-11-18 fault timing must not be read as newer Owner policy. None is silently upgraded to PASS.

**Additional financial/operational exposure requiring CF05 qualification:**

| Risk | New/expanded impact | Mandatory countermeasure and acceptance |
| --- | --- | --- |
| Multi-object DB chain | Contribution succeeds but Third Party, invoice, line/link or validation is incomplete or from a different transaction | Exactly one outer DoliDB owner and **one shared active profile guard** for each member; independent persisted DB postconditions; qualified Core/Agenda/trigger/InnoDB call graph |
| Weak CoreGuarded physical evidence | ACK/context cannot independently prove same physical transaction event; multiple financial rows amplify residual uncertainty | Retain ADR-12's explicit limit; finite error provenance; only specifically qualified owned rollback; all other ambiguity UNKNOWN/STOP without compensating DML |
| Premature or terminal completion | `CHAIN`/`RELEASE`, lost ACK or changed connection could cause unsafe new SQL, PDF/mail or next-member work | Known terminal/unknown → STOP; post-commit only after suitable acknowledged nonterminal Core completion and DB-independent checkpoint; no forced session DB settings |
| PDF/SMTP side effects | Files/mail can outlive failed DB transaction or be sent twice | Non-atomic separate statuses; PDF/path/hash/attachment/recipient validation; no automatic regeneration/redelivery, manual reconciliation |
| Profile/call-graph bypass | Direct Invoice/Third Party service or client feature flags could enable unqualified writes | Server-owned profile, revision/CAS, member/entity/rights/consent/CSRF, direct service guards, no Enhanced→CoreGuarded fallback; fail-closed unsupported Core/hooks |
| Operator witness use – F32-MR-01/P2 | `sudo env_reset` can omit required A/B/C private `CF03_*` variables | **Owner ACCEPTED limitation**, but **new real A/B/C witness use blocked** until protected, least-privilege target-user launcher passes read-only preflight; no private values committed |
| Evidence parent text – F32-MR-02/P3 | Old prose could misstate file trust boundary | **Owner ACCEPTED historical documentation deviation**. Code and current runbook normative: canonical evidence parent without group/other write; A/B/C private environment root; case 0700/file 0600 and one-shot |
| Qualification/release status | Old contribution-only POS01/POS02 or synthetic CI mistaken for full invoice/ZIP approval | Independent exact-source review, full real **CF03-NORMAL** A–D, retained synthetic fault/security and **21-job** CI, new exact v0.1.0 ZIP, real D package install, R0-04/R0-07 and separate Owner release GO |

**Test-timing distinction:** real destructive fault/rollback, lost ACK/connection, deadlock/timeout, destructive concurrency/replay and controlled PDF/SMTP fault injection are deferred to later `CF03-FAULT` (**OPEN/NOT EXECUTED**) by revised ADR-11; this **does not waive** any runtime UNKNOWN/STOP, safe synthetic negative or known P0/P1 safety gate. RB01-A's observed persisted contribution is **INVALID/STOP** as rollback proof, not an executed rollback. A/C share one database/webroot and require strictly serial owner profile/account switching.

**PR32 current history:** final reviewed head `cbc97843fb017135739a39ea12ef26c7fa66dfdb`, squash commit `425980c43a51a9f27073133212f18825ac6da248`; 9/9 standard and 1/1 hosted CI PASS, **not** new A–D real financial or physical-event proof. Earlier archived privacy-redacted evidence bytes cannot inherit original SHA claims. The independently reviewed 0×P0/P1, P2+P3 findings are Owner-accepted exceptions, not external approval.

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

## D-01 completion risk addendum — 2026-11-18

Owner evidence acceptance: **ACCEPTED WITH DOCUMENTED LIMITS**. Option A:
**CONDITIONALLY APPROVED**, technically unqualified pending the mandatory
completion/session-checkpoint/logger gate **before CF-02 contribution DML**.
Authority and complete decision: [PR #25 review/Owner record](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/25#issuecomment-6993488134).
The [ADR Owner addendum](ADR-0001-core-first-transaction-profiles.md#d-01-owner-addendum--2026-11-18)
is the current scoped contract for the existing risks below; this adds no new
risk model and does not change EnhancedMySQL/MariaDB evidence or guarantees.

- **R01/R07 — Entry versus completion.** The real restricted MySQL8.4.13/V24.0.3
  library experiment supports ordered SET RR → Core BEGIN and behavioral RR/1568,
  not durable physical identity or actual-event isolation. Six completion cases
  show CHAIN/RELEASE effects despite pre-entry autocommit1: next SET1568/2006.
  Waiting for that next SET would allow unsafe earlier SQL. Preserve preflight
  classification plus immediate terminal STOP, without post-end SQL probes,
  completion whitelist or session/global normalization. Classification stability
  across the supported Core/Agenda path remains **U/G**, not proven by a settings read.
- **R05/R06 — Acknowledged end versus lost ACK.** Member result and batch continuation
  are independent. Acknowledged CHAIN/RELEASE commit keeps SUCCESS while the batch
  stops; acknowledged rollback keeps its business failed/skipped result and stops.
  NO_CHAIN continuation under E10/E12 additionally needs qualified post-end context
  and checkpoint. Core false rollback can reset depth0; failed ends may leave stale
  Core error fields. Lost/unacknowledged/uncertain ends remain UNKNOWN/STOP without
  retry, compensation, another rollback or readback-based success. Checkpoint failure
  after accepted COMMIT is E15: preserve DB outcome, mark result persistence uncertain
  and stop. No claim of original physical completion or crash-safe recovery.
- **R11 — Core/logging and forbidden post-processing.** Core `dol_syslog()` after
  commit/rollback may run before the adapter receives the acknowledged return.
  Prove the actual selected logger path cannot already query a chained/released
  context. Terminal STOP must precede any additional module SQL, profile/config
  read, mail/PDF or next member. Finish standard Agenda/business postconditions
  before end. Unknown extensions/loggers retain the bounded compatibility
  reservation, not a new universal inventory/certification obligation.
- **R12 — Checkpoint/HTTP handler dependency.** Qualify durable, readable result
  persistence and request teardown independent of the terminal DB context. Do not
  assume every configured PHP session handler is file-based. A checkpoint in the
  chained context could report success then disappear at connection release; a
  RELEASE connection cannot serve DB-backed persistence. Only proved independent
  checkpoint/HTTP-result actions may follow terminal end; failure stops and never
  reopens/resumes consumed consent. Test both acknowledged outcomes, E15 and lost ACK.
- **R09/R13/R15 — Scope, partial batches and rollout.** The Owner provisionally accepts
  controlled CHAIN/RELEASE partial processing (normally one transactionally processed
  member). Clearly distinguish unprocessed selections from success/failure/unknown.
  Safe multi-member operation remains the target; permanent support and Option C
  await separate decisions. No general qualification follows from the37 completed
  measurements/controls,1 PARTIAL or synthetic CI. No silent profile downgrade,
  new transaction manager, general P_S prerequisite or release approval.

The evidence is **A** only for the bounded measured library/driver conditions;
Option A's implementation, session/logger and HTTP guarantees remain **U/G**. Required
new proof: actual pre-end classification/no intervening SET→BEGIN SQL; all three
completion modes; supported Core/standard-Agenda/logger call path; independent
session checkpoints and controlled response/end; no terminal follow-on actions;
lost ACK and checkpoint failure. Independent assessment must close this gate before
contribution DML; **NOT EXECUTED** in AP2. Later CF-03 and release gates remain open.

[Historical experiment](../d01-loaded-core-feasibility-v1.md) retains D-01 CHANGES
REQUIRED, instrumentation/factory scope limits, raw synthetic traces, checksums and
PARTIAL. The original CF-00 register and time-specific pending/NOT EXECUTED statements
below are preserved; apply this dated addendum to current completion/continuation
risk handling. Owner risk acceptance is not technical implementation approval.

**Historical drafting/review context:**

Companion to the [ADR](ADR-0001-core-first-transaction-profiles.md) and
[external handoff](ADR-0001-review-handoff.md). Owner: repository Owner;
architecture-level risk acceptance date: 2026-11-18, [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22),
ADR-01–ADR-12. Baseline: `48d196cfb04421d775ab4e6757083f6a755121df`.
Follow-up authority: Owner-approved F23 dispositions and D-01/D-02, 2026-11-18,
recorded in the [ADR](ADR-0001-core-first-transaction-profiles.md#follow-up-owner-decisions-d-01-and-d-02--2026-11-18).
Initial external review at `8fd9ca321a8090ec8c91a084efffc06d943bf9e3` remains
CHANGES REQUIRED BEFORE CF-00 APPROVAL; independent F23 confirmation is pending.
This is a design risk matrix, not another requirement-to-test matrix. Historical
71-ID assignments and evidence stay unchanged. No tests were run for this register.

## Reading acceptance and evidence

- **A — accepted architecture residual:** Owner expressly accepts reduced physical
  proof in Core-Guarded (ADR-12), or retained snapshot/session limitations. This does
  not accept a faulty implementation or broaden prior profile qualification.
- **U — unproved control:** proposed Core-Guarded technical behavior has no product
  implementation/qualification. An existing Enhanced/MariaDB control is source/
  historical evidence only, within its documented profile; not a fresh PASS here.
- **G — gate:** unresolved detailed contract blocks architecture acceptance where
  material; missing implementation/real evidence blocks later qualification/release.
  A and G can coexist. Owner risk approval cannot turn U into proven behavior.

D-01/D-02 resolve the design choices, not their technical proof. Required tests
below are **future / NOT EXECUTED for Core-Guarded**, with loaded-Core entry/order
proof before production contribution DML. That test gate is not a requirement to
run DB tests in the documentation-only CF-00 PR. Preserve
historical SH-01 counterexamples and I1-05 failures; do not count proposed negative
cases as completed. Q numbers refer to the handoff's review questions.

| Risk ID | Scenario/cause | Impact | Core-Guarded control | Enhanced control | MariaDB control | Residual risk | Mitigation | Required test/evidence | Owner acceptance | Open question |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| R01 | Foreign transaction hidden at logical depth 0; premature/implicit end | Core BEGIN could commit foreign work; partial result | U, D-01: successful SET TRANSACTION ISOLATION LEVEL REPEATABLE READ immediately before Core BEGIN on same connection; zero intervening SQL; refuse 1568/failed entry/known foreign state without rollback. Depth/identity are application controls | Existing fresh idle/event-bound admission/completion | Existing native/depth/active-idle snapshots | Bounded entry check is not later transaction identity; CG can still miss later same-connection replacement | D-01 preferred recipe; autocommit=1 entry prerequisite, no completion-value whitelist or session/global changes; actual invalid entry stops | Later loaded-Core/restricted MySQL foreign-BEGIN/depth0/1568 and no foreign end, exact query order and relevant completion behavior under autocommit=1 | D-01 approved 2026-11-18; ADR-12 accepts later physical-proof reduction, not unsafe foreign entry. U/G: implementation evidence pending | Q01: confirm bounded admission is precise, without demanding Enhanced proof? |
| R02 | Same native connection COMMIT/BEGIN or ROLLBACK/BEGIN; marker recreation | Original transaction replaced invisibly, rollback targets replacement | U: latch known replacement; no nonce-as-identity claim | Same own event + fresh save/release progression at checkpoints | Active snapshot can look unchanged | CG/MariaDB may accept indistinguishable endpoints; no continuous tracking in any profile | State limitation plainly; do not revive SH-01 equivalence | Preserve SH POC-05/06/10; later real Core injections report both detected and undetected cases | A: explicit CG same-connection limitation; U/G for guard correctness | Q01/Q02: how is evidence level carried without reusing physical `owned` semantics? |
| R03 | Reconnection, handle/server replacement or network loss | Writes continue on a new context; outcome ambiguity | U: immutable DoliDB/native handle/ID binding, errors latch stop | Existing native/server/thread/event identity + latch | Existing native handle/ID + latch | In-flight server result can remain unknown; no recovery by reconnect | No transparent reconnect/retry or ownership transfer; manual reconcile | Real own-connection loss, replacement and delayed errors; zero later business/PDF/mail calls | ADR-08 mandates stop; no acceptance of continuing after detected swap. U/G | Q02: exact error and identity checkpoints? |
| R04 | Core nesting, inner rollback only decrements depth, or hidden SQL error in negative Core result | Partial writes treated as undone or completed | U: expected outer depth 1 at returns, retain classified errors; E04/E05 admit one outer rollback under RB, E09 stops mismatch; never drain counters | Existing depth/event checkpoints retained | Existing depth/status checks retained | Balanced counter is not proof of semantic success or event ownership | Check Adherent::subscription, Subscription::create, update_end_date and standard Agenda nested paths; ordinary business errors need outer rollback | Future real nested positives/business errors/unbalanced depth; retained error code/call and independent all-table postconditions | F23-03 accepted with limits; no waiver for ignored errors. U/G at CF-02/03 | Q01/Q02: do RB and error precedence close the documented nesting contract? |
| R05 | Failed/unacknowledged COMMIT or later session-save failure | Persistent booking uncertain to caller; unsafe duplicate | U: E12 normal ack/depth/identity + business checks = application success; E13 uncertain attempted end = UNKNOWN/STOP; E15 separates accepted DB end from uncertain result persistence | Existing ack + original COMMITTED event before idle probe | Existing ack + idle snapshot | CG acknowledgement may relate to invisible replacement/no-op; no durable crash recovery | No rollback/retry after attempted uncertain commit; no readback redemption; checkpoint before side effects, manual reconciliation | Future real forwarded end/reply loss and checkpoint failure after acknowledged commit; zero later members/delivery | A: CG unbound physical completion; ADR-08 no automatic recovery. F23-03 adopted, U/G for implementation | Q02: is DB outcome distinct from session/delivery uncertainty? |
| R06 | Failed/lost rollback, Core resets depth despite SQL failure, foreign context | Partial result or end of foreign replacement | U: RB requires admitted acknowledged begin, stable context/depth1, classified business/S error, no COMMIT attempt or detected uncertainty; one Core rollback only. E10 ack/depth0 = application rollback; E11 otherwise stops | Existing fresh owned context + original ROLLED BACK event | Existing fresh logical/active context then ack/idle snapshot | CG acknowledged rollback is no original-event proof or guarantee against earlier invisible commit | No physical proof requirement for CG; no counter draining, repeated rollback or cleanup of foreign context; E08 uncertainty dominates | Future known business/S rollback, false/lost ACK, depth0 despite failure and detected replacement; independent rows, no follow-on DML | A: weaker CG end evidence; F23-03 ACCEPTED WITH LIMITS. U/G: qualified behavior still required | Q02: is RB deterministic under the E01–E15 matrix? |
| R07 | One-shot/session isolation differs; SQL consumes next setting; completion behavior under relevant settings | Stale read view, unsafe end assumptions | U, D-01: Preflight → SET RR → immediate Core BEGIN → Locks → business reads; no intervening SQL; autocommit=1 checked before SET; no completion-value admission exclusion; no actual-event isolation requirement | Actual own ACTIVE-event RR admission unchanged | Existing snapshot policy, no new event/isolation gate | CG cannot universally detect later overrides; completion variants remain to be tested, not generally excluded; detected unexpected ends/connection uncertainty still stop | D-01 normative preferred design; no session-default substitution or global/session rewriting; incompatible recipe returns to Owner | Later real loaded-Core restricted account, four session/one-shot levels, metadata/late override negatives, relevant completion variants under autocommit=1 and behavioral reads before contribution DML | D-01 APPROVED 2026-11-18; A: no universal event proof. U/G: later technical gate, not new CF-00 DB-test requirement | Q03: confirm fixed recipe and fallback-to-Owner rule, not equivalent event control? |
| R08 | 1213 transaction abort versus 1205 statement/transaction timeout | Unsafe rollback/continuation or lost work | U: E06 1213 invalidates context, UNKNOWN/STOP, no module rollback/retry. E07 1205 uses E05 only with qualified S/RB and known rollback_on_timeout OFF; ON/unknown/unqualified uses E08 | Existing observer must still admit rollback, otherwise stop | Existing snapshot context contract retained | Numeric error/depth alone cannot prove surviving context; missing native error facts stay unknown | Finite code + call + settings mapping; no catch-all classification; successful E10 alone permits next member; never repeat failed one | Future separate deadlock/timeout profiles, ON/OFF or unknown configuration, masked errors, no later SQL/delivery after uncertainty | ADR-08; F23-03 ACCEPTED WITH LIMITS. No claim tests or mappings already qualified | Q02: are conservative defaults and bounded recoverable branch precise? |
| R09 | Concurrent selection/duplicate or stale confirmation | Duplicate contribution/1062, wrong end date, lost consent | U: member/type locks then fresh rights/entity/fingerprint/overlap and Core R16 checks; batch bound | Preserve FIX01 lock-before-metadata and I107 predicates | Preserve MDB-07/08 controls | No whole-batch serialization or exactly-once across lost sessions; arbitrary out-of-band writers unqualified | One member per tx; no selection truncation/retry; new consent on changed fingerprint | Real waited-lock winner/loser, overlap/disjoint/history/NULL, loser zero DML, independent SQL; Enhanced/MDB regression | Existing bounded risk unchanged; U/G for new CG path; data-integrity regression blocks qualification | Q03/Q06: snapshot ordering and confirmation invalidation races? |
| R10 | Non-InnoDB actual table or omitted standard Agenda path | Persisted partial rows after rollback | U, D-02: conditional actual Core table checks before BEGIN/after locks; include MEMBER_SUBSCRIPTION_CREATE → ActionsAuto → ActionComm::create → ACTION_CREATE effects | Existing conditional Core/Agenda and invoice/customer engine gates | Existing conditional gates retained | Extensions can add writes not in ordinary path; no universal scanner | Target actually reached paths/tables/extra fields; Agenda OFF must not fail on unrelated tables | Future standard Agenda ON/OFF, event/member/actor/resource links, nested errors and rollback; path-specific extra writes only if reached | D-02 APPROVED 2026-11-18; U/G: targeted evidence later; no destructive table conversion authorized | Q05: is the standard and indirect ACTION_CREATE boundary sufficiently defined? |
| R11 | Extension transaction control, irreversible filesystem/SMTP or extra hooks | Effects survive rollback; premature/duplicate delivery | U, D-02: known transaction-endangering combinations incompatible, reject before entry or E08/E09 if detected during work; unexamined hooks carry documented compatibility reservation | Existing post-commit boundary; arbitrary hooks not atomically covered | Existing bounded post-commit behavior | No general guarantee for unknown foreign effects; CG can miss later end; optional mail not qualified here | No universal third-module inventory/test/block duty or mandatory whitelist. Reject known unauthorized irreversible paths; document known incompatibilities, no auto resend/regeneration; mail separately qualified | Later targeted standard hook/Agenda contract; controlled known incompatible examples, no universal campaign; optional mail only under separate authorization | D-02 APPROVED 2026-11-18; F23-04 ACCEPTED WITH LIMITS; A: bounded irreversible-effect risk, not arbitrary-hook safety | Q05: confirm compatibility reservation versus known incompatibility, with no universal module ban? |
| R12 | Session loss, crash, consumed POST replay, no persistent journal | Outcome lost, duplicate attempt or lost side-effect status | U: retain owner/entity/revision, strict server-held options, consumed request and checkpoints | Existing replay/session behavior | Existing replay/session behavior | No durable exactly-once recovery; session loss needs manual reconciliation | Never reopen/resume/rebook automatically; result display independent of new admission | Authenticated real positive/replay, stale/direct POST, session-save failure and lost result; unchanged postconditions | A: ADR-09 session architecture; ADR-08 no retry. U/G for changed boundaries | Q06: profile binding before consume and readable old results? |
| R13 | Silent downgrade, admin race, stale profile revision or ambiguous upgrade | Unintended weaker/financial permission | U: CF-01 authoritative server-side revision and atomic comparison or defined per-member snapshot; cached constants not freshness evidence; original context fixed, stale confirmations rejected | P_S/instrumentation failure denies configured Enhanced; no fallback | Family-bound Snapshot default retained | Concurrent config/request effects require concrete revision semantics, not global serialization | Explicit admin decision for unknown legacy origin; no active adapter switch; record outcome profile | Future concurrent admin/booking requests, cached constants, absent/invalid config, reactivation/migration, mid-run change and old sessions | ADR-04/05/07 mandatory; F23-05 DEFERRED TO CF-01, not an additional CF-00 blocker | Q04/Q06: implementation must state/test its linearization boundary without global locking? |
| R14 | Controller/service/direct Invoice or customer entry bypass of CG feature limit | Unqualified financial/multi-object writes | U: CF-02 server-owned profile/feature context required at all relevant module controller, Booking, Invoice/customer service entries; missing context denies invoice/autocreate/PDF | Preserve existing admitted financial scope | Preserve existing admitted financial scope | Contribution tests cannot qualify invoice/tax/customer/PDF | No general Core API changes; module-side guard before any prohibited business call; separate CF-05 gate | Future direct service/HTTP/old-context negatives, zero prohibited DML and small Enhanced/MDB invoice regression | ADR-06: feature not enabled. F23-06 DEFERRED TO CF-02; not an additional CF-00 blocker | Q07: all relevant module call surfaces covered without altering Core? |
| R15 | Old artifact/CI or version range promoted to new profile/release proof | Unsupported operational promises or unsafe rollout | No implementation evidence yet | Exact historical MySQL8.4.13/V24/PHP8.2.35 only | Exact historical MariaDB10.11.19/V24/PHP8.2.35 plus separately recorded history | Source/synthetic coverage is not real DB/ZIP qualification; V23 real unproven | Pin code/profile/config/artifact, preserve histories; new release PR and R0-04/R0-07/Owner GO | CF-03 real restricted Core/HTTP, existing 21-job CI, non-regression, exact future ZIP | ADR-11; G. No release or blanket version approval | Q08: evidence and release-gate completeness? |

## Evidence boundaries and disposition

Existing source and historical tests are indexed in the ADR and
[coverage legend](../test-coverage.md). SH-01's real restricted MySQL8.4.13 native
SQL, reply-loss and isolation counterexamples are applicable negative evidence;
its nesting model is not loaded Core, and it ran **no real Dolibarr business
integration**. Neither historical P_S tests nor MariaDB 9/9 qualify Core-Guarded.

The focused F23 follow-up reviews the adopted D-01/D-02 admission/order, E01–E15
classification and bounded extension contract. Its verdict is pending. The initial
HIGH findings remain historical review facts, not external closures by this author.
The [disposition matrix](ADR-0001-review-handoff.md#finding-disposition-f23-01-through-f23-07)
separates corrections from CF-01/CF-02 implementation duties; F23-05/06 are not
additional CF-00 blockers. Future unexecuted tests alone do not prevent acceptance
of a precise documentation contract. They remain mandatory before the applicable
implementation/production-DML/qualification/release gate. No Owner acceptance is
converted into an implemented or independently proven control.
