# PR #52 corrective status register and sweep

## Integrated status and review provenance

PR #52's final independent verdict was **APPROVED FOR DOCUMENTATION MERGE**. F52-01/P2, F52-02/P2, F52-03/P3, F52-04/P3 and F52-05/P3 are independently closed. F52-04 was closed on the first corrective HEAD and reaffirmed on final HEAD. The verified squash, approved HEAD and PR CI integration have identical trees. Approved-HEAD PR CI was 31/31 and separate post-merge Standard push CI was 9/9. [Verified identities and runs](m34-integrated-owner-acceptance.md#1-decision-and-review-provenance)

The Owner granted CF05-07 and CF03-RISK acceptance with documented limitations on 2026-11-26. Formal closure of M3, CF05-07, CF03-RISK and Phase 5 development qualification takes effect upon Owner integration of the independently approved closure documentation. M3.2 is integrated with scope limitation, and actual R0-04 compliance remains pending. [Canonical transition rules](m34-integrated-owner-acceptance.md#9-canonical-status-and-transition-rules)

## Historical expected status before PR #52's final approval and squash (2026-11-26)

This register applies to the corrective documentation reviewed against
[PR #52](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/52)
original HEAD `c4ec6a02cda14a8aab0e253ff6485df9e2fced10`, tree
`b2e027762268d71942f6a4044326f35f3a3298ba`, target
`8ab803d135ac534dd2d08d28f47a8e427cd23302`. The independent integrated
review dated 2026-11-26 and the subsequent Owner instruction govern the delta.

| Gate | Disposition at the recorded corrective handoff |
| --- | --- |
| PR #51 / Pre-Release Test-Hardening | MERGED, verified identical approved and squash tree. COMPLETE / CLOSED WITH DOCUMENTED LIMITATIONS. F45-01/02, F49-03, F50-02/03 and F51-01/02 INDEPENDENTLY CLOSED. |
| M3.1 documentation | Original and first corrective follow-up verdict CHANGES REQUIRED BEFORE DOCUMENTATION MERGE. On `1009fe845134f5c1f872c64ed9f191c4970f8277`, F52-01/P2 and F52-02/P2 PARTIALLY RESOLVED (merge blockers), F52-03/P3 PARTIALLY RESOLVED, F52-04/P3 INDEPENDENTLY CLOSED, F52-05/P3 OPEN. F52-01/02/03/05 final corrections FIX IMPLEMENTED – INDEPENDENT DELTA REVIEW PENDING; F52-04 remains independently closed. |
| CF05-07 | Independent verdict APPROVED FOR DEVELOPMENT QUALIFICATION CLOSURE WITH DOCUMENTED LIMITATIONS. INDEPENDENT TECHNICAL QUALIFICATION ACCEPTED WITH DOCUMENTED LIMITATIONS – FORMAL OWNER ACCEPTANCE PENDING. No new productive blocker. |
| CF03-RISK | INDEPENDENT RESIDUAL RISK ASSESSMENT COMPLETE – OWNER ACCEPTANCE PENDING. Existing individual dispositions remain binding. |
| M3.2 Cross-Entity Variant B | Original independent verdict VARIANT B ACCEPTABLE SUBJECT TO SPECIFIC ADDITIONAL CONDITIONS. Subsequent binding Owner decision OWNER ACCEPTED WITH DOCUMENTED SCOPE LIMITATION. Independent documentation verification complete, F52-04/P3 independently closed on `1009fe845134f5c1f872c64ed9f191c4970f8277`. Formal integration and exact-package R0-04 compliance pending. Distinct from Core-Parity Variant B. |
| CF03-NORMAL | PASS within recorded development matrices and tested sources. No broad real requalification. |
| CF03-SECURITY-BASELINE | PARTIAL. P5L-A-S05, P5L-B-S05, P5L-C-S05 and D-S06 remain BLOCKED / NOT EXECUTED. |
| CF03-FAULT | DEFERRED / NOT EXECUTED in the destructive real full-workflow scope. |
| Phase 5 / release | PARTIAL / v0.1.0 NO-GO. M3.4 stays separate. R0-04, R0-07 and Release GO remain separate. |

## Per-document status sweep

**Historical implementer sweep for the first correction (not the final independent result).** The subsequent independent review of all 28 paths at `1009fe845134f5c1f872c64ed9f191c4970f8277` identified remaining contradictions. Its dispositions and the final corrective changes are reconciled below; this earlier table is retained only for traceability.

All 27 paths in the original reviewed diff were checked for active
headings, scope and source summaries, qualification tables, finding
statuses, checklists, reading order and next actions. Dated observations
retain their original source and outcomes. This table records the
disposition of each path in the corrective delta.

| Original path | Finding and result of status sweep |
| --- | --- |
| [AGENTS.md](../AGENTS.md) | Corrected current authority summary, split technical and Owner gates, added binding M3.2 operational scope and link to R0-04. Dated #38/#45 entries remain historical. |
| [CHANGELOG.md](../CHANGELOG.md) | Corrected current 26 November entry and dated wording for original #45 finding state. Earlier release notes retained. |
| [README.md](../README.md) | Updated opening development checkpoint and all three profile/evidence rows; retained current A/B/C/D mapping. Added separate deployment scope and profile-specific STOP. |
| [ADR-0001](adr/ADR-0001-core-first-transaction-profiles.md) | Labeled former F36-02/default-deny pre-admission statements as historical. Current ADR-12 and Owner Core-Parity scope retained. |
| [Risk register](adr/ADR-0001-risk-register.md) | Corrected independent gate status, former #45 pending closures and historical #38 table; added sampled observer/continuation matrix and accepted M3.2 disposition. |
| [CF03 operator runbook](cf03-operator-runbook.md) | Added current operating scope, R0-04 and STOP guidance; prior A/C commands and original checkpoint remain historically bounded. |
| [CF03 plan](cf03-qualification-plan.md) | Corrected M3.1/CF05-07/CF03-RISK status and M3.2 cross-entity real gap; historical environment assignment remains fenced. |
| [CF05-06 qualification plan](cf05-06-standard-core-qualification-plan.md) | No further edit. The dated pre-#38 default-deny/source qualification sequence is explicitly historical; current navigation leads to the integrated assessment. |
| [CF05-06 source qualification](cf05-06-standard-core-source-qualification.md) | No further edit. Draft source and trigger-boundary sections already identify their historical status. Owner variant and current navigation are separated. |
| [CF05 Core-parity regression](cf05-core-parity-regression.md) | Updated legacy #45 cross-reference to closed findings. Regression scope and old reported results retained. |
| [CF05 Phase 3 finance](cf05-phase3-finance-admission.md) | Updated #45 cross-reference; identify the 21 November default-deny development status as historical below. |
| [CF05 post-COMMIT evidence](cf05-phase4-postcommit-offline-evidence.md) | Historical F38 and F36-02 checkpoint labels clarified; current ADR-08A link and limited PDF STOP behavior retained. |
| [CF05 shared guard](cf05-shared-guard-design.md) | Protected sequence under #34/#36 labeled historical; current code navigation and F45 closure reference retained. |
| [Development baseline](current-development-baseline.md) | Replaced stale numbered #51 roadmap, integrated independent verdicts and M3.2, kept closed finding rows and separate remaining Owner/release gates. |
| [Documentation guide](documentation-guide.md) | Reading order points to canonical statuses and M3.2; updated resolved #45 references and retained archived original sequencing. |
| [Environment matrix](dolienv-environments.md) | M3.2 scope linked to the source-bound matrix; real cross-entity remains BLOCKED / NOT EXECUTED. No reassignment or reproduction. |
| [Cross-entity decision](m31-cross-entity-owner-decision.md) | Historical alternatives separated from binding Owner decision, operating conditions and R0-04 verification. |
| [Integrated qualification](m31-integrated-development-qualification.md) | Positive independent verdicts recorded, source continuity and distinct evidence scope retained. Four-dimensional register and open acceptance actions added. |
| [Owner Core-parity](owner-core-parity-2026-11-22.md) | Updated #45 historical cross-reference, distinguished the earlier Core-Parity policy from the M3.2 operating scope via current navigation. |
| [Invoice architecture](phase4-invoice-architecture.md) | Added three observation contracts and profile-specific effect/STOP decisions with canonical risk matrix. |
| [Invoice implementation plan](phase4-invoice-implementation-plan.md) | Updated dated #45 finding link. Old phase ordering remains historical. |
| [Porting plan](porting-plan.md) | Updated dated #45 finding link; earlier source/porting checkpoints retained. |
| [Privacy guidance](privacy-sanitization.md) | Updated dated #45 finding link; privacy boundaries unchanged. |
| [Release qualification](release-qualification-v0.1.0.md) | Split independent and formal Owner gates, added M3.2 and seven mandatory R0-04 checks; release NO-GO retained. |
| [Requirements CSV](requirements.csv) | Replaced 71 single-dimensional dispositions; corrected six stale F51 notes in last field only. Counts and remaining items appear in the integrated report. |
| [Test coverage](test-coverage.md) | Retained #51 exact source and 31/31 historical CI; refreshed finding/navigation references and M3.2 application to security gaps. |
| [Test strategy](test-strategy.md) | Corrected active integrated verdicts and historical #45 finding references. Preserve scope of synthetic versus real cases and deferred FAULT. |

## Finding-to-correction verification

**Historical first corrective handoff:** the following four rows reflect the previous implementer disposition; the independent follow-up and final correction below supersede them.

| Finding and original condition | Correction and verification | Remaining boundary |
| --- | --- | --- |
| F52-01/P2 — inconsistent active roadmap, risk checkpoint and six CSV notes | Canonical expected register above; inspected the 27 original paths and updated the directly affected active status and historical labels. Verified PR #51 provenance retained and all seven closures reflected. No current F51 pending follow-up. | Independent documentation delta review pending. |
| F52-02/P2 — ambiguous 48-row limitation category | All 71 rows have individually scoped implementation, evidence, risk and release status. Verified exact first 17 fields, ID ordering, 18 columns, aggregate counts and exceptional cases. [Classification results](m31-integrated-development-qualification.md#requirement-dispositions-and-remaining-work) | Package/conditional evidence and separate Owner decisions remain as recorded. |
| F52-03/P3 — apparent equivalence of detection and STOP triggers | Compared [observer](../class/databasestate.class.php), [Core guard](../class/coreguardedtransaction.class.php) and [post-COMMIT path](../lib/booking.lib.php); matched risk, report, architecture, README and runbook wording. | No continuous observation, atomic side-effect or expanded deployment claim. |
| F52-04/P3 — incomplete definition of the accepted scope | Recorded accepted M3.2 decision and operational conditions; cross-linked requirements, current guidance and [R0-04 checks](release-qualification-v0.1.0.md#r0-04-cross-entity-variant-b-compliance). No runtime changes or reclassification of four blocked cases. | Documentation verification pending; compliance remains to be verified on actual package at R0-04. |

All four were **FIX IMPLEMENTED – INDEPENDENT DELTA REVIEW PENDING** at that earlier handoff.

## Final focused corrective pass after independent follow-up (2026-11-26)

The disposition below records the corrective handoff before final independent approval and squash.

The independent follow-up on `1009fe845134f5c1f872c64ed9f191c4970f8277` / tree `62c92dd14d382e84dd19085fe82308b13e3a6b51` inspected all 28 documentation paths. It accepted the canonical profile matrix, four-dimensional requirements method and M3.2 scope. F52-01/02 remained partly resolved P2, F52-03 partly resolved P3, F52-05 was new P3; F52-04 independently closed. This focused correction addresses the specific remaining observations, not a new technical qualification. The initial sweep's assertions are not used as independent proof.

| Finding / original location (reviewed HEAD) | Final correction and governing authority | Verification boundary |
| --- | --- | --- |
| F52-01: release qualification L144–148 and guide L74–80: A/C shared schema and old finance denial | [Release operational constraints](release-qualification-v0.1.0.md#operational-constraints-inherited-by-release) and [guide work boundaries](documentation-guide.md#work-and-acceptance-boundaries) use separate A/B/C roots/accounts and coordinated B/C windows; link to [environment matrix](dolienv-environments.md) and [Owner policy](owner-core-parity-2026-11-22.md). | Current rules corrected; archived old commands remain historical. |
| F52-01: guide L5–15, 22–40 | [Reading order](documentation-guide.md#current-source-of-truth-and-historical-document-reading-order) gives current canonical sources; obsolete Phase 5/PR #38 snapshots and quoted draft instructions explicitly historical. | Completed A/C and D cases remain source-bound; no new real PASS. |
| F52-01/03: baseline L196–212 | [Current contracts](current-development-baseline.md#binding-architecturesecurity-contract) reference Owner Core-parity and the [profile matrix](adr/ADR-0001-risk-register.md#profile-specific-observation-and-continuation); [old ADR revisions](current-development-baseline.md#revised-binding-adr-decisions) explicitly dated and superseded where applicable. Roadmap puts M3.4 before final freeze. | Former default-deny and old D-02 source qualification not current; CoreGuarded rollback/terminal contract scoped. |
| F52-01: postcommit evidence L8–18 | [Current effect qualification](cf05-phase4-postcommit-offline-evidence.md) refers to accepted source-bound later deltas and profile-specific behavior; old pending campaigns dated. | No new effect execution or upgraded historical outcome. |
| F52-01: architecture L38 | [Session compatibility](phase4-invoice-architecture.md) records all F45 findings independently closed (#51/#46). | Permission/file admission contract unchanged. |
| F52-01: porting plan L3–5 | [Current outcome](porting-plan.md) records bounded A–D completion, independent verdicts and pending formal gates. | Original porting history retained. |
| F52-01: Owner O7/Core-parity historical table/delivery, invoice plan L20, privacy L34–39 | [Owner historical disposition](owner-core-parity-2026-11-22.md#finding-disposition-under-the-changed-acceptance-contract), [historical delivery](owner-core-parity-2026-11-22.md#delivery-and-review-gates), [invoice plan](phase4-invoice-implementation-plan.md) and [privacy](privacy-sanitization.md#current-working-tree) mark old planning/launcher/topology as historical and link current rules. | Binding O1–O7, Core-parity, F36-02, ADR-08A and privacy safeguards unchanged. |
| F52-02/05: CSV C07-03, C07-04/05/07, C08-02 | [Requirements](requirements.csv) last-field refinements only: G4-06/M01 BFCache/navigation separate from synthetic final-submit/duplicate-submit, explicit [O7](phase4-invoice-architecture.md#o7--decided-retain-and-extend-the-session-results-page), no-product/service-line VAT-zero scope. [Counts and vocabulary](m31-integrated-development-qualification.md#requirement-dispositions-and-remaining-work) unchanged labels, precise evidence. | All 71 IDs/order, 18 columns, first 17 fields and historical last-field prefixes retained. No general reclassification. |
| F52-03: baseline L201–202, release L148 | Explicit unknown DB completion STOP in all profiles, CoreGuarded finite rollback and terminal suppression only as applicable, profile-specific post-COMMIT outcomes; CPC-02 consequences only. | No code or new Owner architecture decision. |
| F52-04 (closed): M3.2 | [Owner decision](m31-cross-entity-owner-decision.md), [R0-04 seven criteria](release-qualification-v0.1.0.md#r0-04-cross-entity-variant-b-compliance), current baseline and gate summaries record independent documentation verification complete. | All four cases BLOCKED / NOT EXECUTED; actual package compliance pending, no runtime veto. |

The independent sweep's PASS results for other documents, including the two unchanged CF05-06 historical plans, remain applicable; this focused correction does not reopen them. The original 109 historical evidence/experiment files and the positive CF05-07 and CF03-RISK verdicts are preserved. F52-01/02/03/05: **FIX IMPLEMENTED – INDEPENDENT DELTA REVIEW PENDING**. F52-04: **INDEPENDENTLY CLOSED**. Formal M3.1 merge approval, integration, M3.4 Owner acceptances, actual R0-04 compliance and release gates remain pending. Phase 5 PARTIAL; release NO-GO.

## M3.4 repository-wide status sweep (2026-11-26)

The baseline scan covered all 245 tracked blobs. Case-insensitive searches for `CF05-07`, `CF03-RISK`, `M3.[1–4]`, Phase 5 (space or hyphen), `F52`, PR #52, #40, `R0-00`–`R0-09` and release NO-GO/freeze/pending wording identified 42 paths. A second pass reviewed the affected current tables, roadmap, acceptance wording and historical section labels. The [binding status transitions](m34-integrated-owner-acceptance.md#9-canonical-status-and-transition-rules) govern the result.

| Disposition | Paths examined |
| --- | --- |
| Active wording or traceability corrected (18) | `AGENTS.md`, `CHANGELOG.md`, `README.md`, `docs/adr/ADR-0001-risk-register.md`, `docs/cf03-operator-runbook.md`, `docs/cf03-qualification-plan.md`, `docs/cf05-phase4-postcommit-offline-evidence.md`, `docs/current-development-baseline.md`, `docs/documentation-guide.md`, `docs/dolienv-environments.md`, `docs/m31-cross-entity-owner-decision.md`, `docs/m31-integrated-development-qualification.md`, `docs/m31-review-status-and-sweep.md`, `docs/porting-plan.md`, `docs/release-qualification-v0.1.0.md`, `docs/requirements.csv`, `docs/test-coverage.md`, `docs/test-strategy.md` |
| Historical context or unaffected source reference retained (16) | `class/transactionprofile.class.php`, `docs/adr/ADR-0001-core-first-transaction-profiles.md`, `docs/adr/ADR-0001-review-handoff.md`, `docs/cf02-implementation-validation.md`, `docs/cf03-rb01-a-operator-runbook.md`, `docs/cf03-stage01-review-handoff.md`, `docs/cf03-w01-validation.md`, `docs/cf05-06-standard-core-qualification-plan.md`, `docs/cf05-06-standard-core-source-qualification.md`, `docs/cf05-core-parity-regression.md`, `docs/cf05-phase3-finance-admission.md`, `docs/cf05-shared-guard-design.md`, `docs/owner-core-parity-2026-11-22.md`, `docs/phase4-implementation-validation.md`, `docs/phase4-invoice-architecture.md`, `docs/phase4-invoice-implementation-plan.md` |
| Archived evidence or experiment matches preserved (8) | `docs/evidence/cf03/adr-08a-hosted-d-2026-11-26.md`, `docs/evidence/cf03/phase5-hosted-d-2026-11-25.md`, `docs/evidence/cf03/phase5-local-completion-2026-11-25.md`, `docs/evidence/cf03/phase5-local-qualification-2026-11-24.md`, `docs/evidence/f27/core-path-source.json`, `experiments/ap3/MANIFEST.json`, `experiments/ap3/SHA256SUMS`, `experiments/d01/locks.php` |

The corrections record #52's final independent approval and integration, both granted M3.4 decisions, and the condition for formal development closure. They replace earlier pending-Owner or pending-#52 wording in active summaries, checklists and roadmaps. The release map tracks R0-05 operational acceptance separately and retains the seven M3.2 checks under R0-04. The ten CSV replacements affect residual-risk dispositions only, with C06-04 conditional for novel extensions. The Owner record is the sole new file.

The earlier independent verdict with “OWNER ACCEPTANCE PENDING”, interim F52 handoffs and source-bound Phase 5 snapshots remain as history. The 109 evidence and experiment blobs remain unchanged. The A–D NORMAL PASS, PARTIAL security, four blocked real cross-entity cases and deferred destructive faults retain their classifications. Modified OD-P5-02 and the profile-specific STOP matrix remain binding. Draft PR #40 and the separate release gates retain their own lifecycle.
