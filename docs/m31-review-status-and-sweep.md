# PR #52 corrective status register and sweep

## Expected current status

This register applies to the corrective documentation reviewed against
[PR #52](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/52)
original HEAD `c4ec6a02cda14a8aab0e253ff6485df9e2fced10`, tree
`b2e027762268d71942f6a4044326f35f3a3298ba`, target
`8ab803d135ac534dd2d08d28f47a8e427cd23302`. The independent integrated
review dated 2026-11-26 and the subsequent Owner instruction govern the delta.

| Gate | Binding current disposition |
| --- | --- |
| PR #51 / Pre-Release Test-Hardening | MERGED, verified identical approved and squash tree. COMPLETE / CLOSED WITH DOCUMENTED LIMITATIONS. F45-01/02, F49-03, F50-02/03 and F51-01/02 INDEPENDENTLY CLOSED. |
| M3.1 documentation | Original independent verdict CHANGES REQUIRED BEFORE DOCUMENTATION MERGE. F52-01/P2 and F52-02/P2 block documentation merge, F52-03/P3 is a non-blocking documentation improvement, F52-04/P3 is a scope acceptance condition. All four corrective dispositions are FIX IMPLEMENTED – INDEPENDENT DELTA REVIEW PENDING. |
| CF05-07 | Independent verdict APPROVED FOR DEVELOPMENT QUALIFICATION CLOSURE WITH DOCUMENTED LIMITATIONS. INDEPENDENT TECHNICAL QUALIFICATION ACCEPTED WITH DOCUMENTED LIMITATIONS – FORMAL OWNER ACCEPTANCE PENDING. No new productive blocker. |
| CF03-RISK | INDEPENDENT RESIDUAL RISK ASSESSMENT COMPLETE – OWNER ACCEPTANCE PENDING. Existing individual dispositions remain binding. |
| M3.2 Cross-Entity Variant B | Original independent verdict VARIANT B ACCEPTABLE SUBJECT TO SPECIFIC ADDITIONAL CONDITIONS. Subsequent binding Owner decision OWNER ACCEPTED WITH DOCUMENTED SCOPE LIMITATION. Documentation verification and exact-package R0-04 compliance pending. Distinct from Core-Parity Variant B. |
| CF03-NORMAL | PASS within recorded development matrices and tested sources. No broad real requalification. |
| CF03-SECURITY-BASELINE | PARTIAL. P5L-A-S05, P5L-B-S05, P5L-C-S05 and D-S06 remain BLOCKED / NOT EXECUTED. |
| CF03-FAULT | DEFERRED / NOT EXECUTED in the destructive real full-workflow scope. |
| Phase 5 / release | PARTIAL / v0.1.0 NO-GO. M3.4 stays separate. R0-04, R0-07 and Release GO remain separate. |

## Per-document status sweep

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

| Finding and original condition | Correction and verification | Remaining boundary |
| --- | --- | --- |
| F52-01/P2 — inconsistent active roadmap, risk checkpoint and six CSV notes | Canonical expected register above; inspected the 27 original paths and updated the directly affected active status and historical labels. Verified PR #51 provenance retained and all seven closures reflected. No current F51 pending follow-up. | Independent documentation delta review pending. |
| F52-02/P2 — ambiguous 48-row limitation category | All 71 rows have individually scoped implementation, evidence, risk and release status. Verified exact first 17 fields, ID ordering, 18 columns, aggregate counts and exceptional cases. [Classification results](m31-integrated-development-qualification.md#requirement-dispositions-and-remaining-work) | Package/conditional evidence and separate Owner decisions remain as recorded. |
| F52-03/P3 — apparent equivalence of detection and STOP triggers | Compared [observer](../class/databasestate.class.php), [Core guard](../class/coreguardedtransaction.class.php) and [post-COMMIT path](../lib/booking.lib.php); matched risk, report, architecture, README and runbook wording. | No continuous observation, atomic side-effect or expanded deployment claim. |
| F52-04/P3 — incomplete definition of the accepted scope | Recorded accepted M3.2 decision and operational conditions; cross-linked requirements, current guidance and [R0-04 checks](release-qualification-v0.1.0.md#r0-04-cross-entity-variant-b-compliance). No runtime changes or reclassification of four blocked cases. | Documentation verification pending; compliance remains to be verified on actual package at R0-04. |

All four are **FIX IMPLEMENTED – INDEPENDENT DELTA REVIEW PENDING**.
