# M3.4 — Integrated Owner Acceptance and Development Qualification Closure

- **Decision date:** 2026-11-26
- **Decision authority:** Product Owner
- **Product:** `membersubscriptionmassaction` v0.1.0
- **Decision status:** BOTH OWNER ACCEPTANCES GRANTED WITH DOCUMENTED LIMITATIONS
- **Release status:** NO-GO

This record is the canonical statement of the two binding M3.4 Owner decisions. The Owner explicitly accepted both decision proposals in full, including all ten integrated residual-risk groups and all eight documented acceptance conditions. No additional Owner conditions were imposed. The decisions are effective as of the Owner's declaration; formal repository closure requires controlled documentation, independent documentation approval and Owner integration of the closure record. The presence of this record in a Draft PR does not establish that the PR has been approved or merged.

## 1. Decision and review provenance

| Identity | Verified value |
| --- | --- |
| Accepted development baseline / actual PR #52 squash | `a19d7459550b9aebcb0f551925d8dfd0825c5ebe` |
| Sole parent of actual squash | `8ab803d135ac534dd2d08d28f47a8e427cd23302` |
| Independently approved PR #52 HEAD | `6bb2d38eecaf38fe1e3a9507695257759cb395cd` |
| Actual PR CI integration commit | `f021f62a23a097555d060a7eaaea5b3fcac9140e` |
| Parents of PR CI integration commit (target, approved HEAD) | `8ab803d135ac534dd2d08d28f47a8e427cd23302`, `6bb2d38eecaf38fe1e3a9507695257759cb395cd` |
| Identical approved, CI integration and actual squash tree | `b70d60455d5c5bbc255ec0934ca26bdfa73037ce` |

[PR #52](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/52) was Owner squash-merged on 2026-11-26 at 14:59:36 UTC. The actual squash SHA, sole parent and identical tree were verified directly against GitHub. Its independent verdict is **APPROVED FOR DOCUMENTATION MERGE**. All five findings F52-01/P2, F52-02/P2, F52-03/P3, F52-04/P3 and F52-05/P3 are independently closed. F52-04 was independently closed on `1009fe845134f5c1f872c64ed9f191c4970f8277` and reaffirmed on the final approved HEAD. The accepted documentation-only PR contains four commits, 28 documentation files, +806/-258 lines; no production, test, workflow or build-tool changes and no changes to the 109 historical evidence/experiment files. The seven independent PR #51 finding closures remain valid.

Exact-HEAD PR CI: [Standard 9/9](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/45840460364), [Full Regression 21/21](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/45840460321), [Hosted Simulation 1/1](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/45840460363), total **31/31 SUCCESS**. These runs use the tree-identical PR CI integration commit above. Separately, the actual squash has its own [post-merge Standard push CI](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/45844543139), **9/9 SUCCESS**. The push run is not a separate 31-job full regression. Hosted Simulation is synthetic, not actual Hosted D or package-installation qualification. No new real test evidence follows from merging documentation or from this Owner decision.

The substantive review basis is the [M3.1 integrated assessment](m31-integrated-development-qualification.md), the [integrated CF03-RISK review matrix](adr/ADR-0001-risk-register.md#integrated-cf03-risk-review-matrix), the [M3.2 Cross-Entity Owner decision](m31-cross-entity-owner-decision.md), the [requirements matrix](requirements.csv) and the [review status and sweep](m31-review-status-and-sweep.md). The positive independent technical and risk-assessment verdicts are retained, not replaced with a new independent review by the document author.

## 2. Explicit Owner declaration

The binding decision portion of the Owner's declaration, given on 2026-11-26 in response to the two M3.4 proposals, is recorded below in the original German for traceability (not a new decision):

> Meine formelle Owner-Entscheidung zu M3.4 auf Grundlage der vorstehenden Entscheidungsvorlagen:
>
> M3.4-A / CF05-07: Ich akzeptiere Beschlussvorschlag A vollständig und verbindlich.
>
> M3.4-B / CF03-RISK: Ich akzeptiere Beschlussvorschlag B einschließlich aller zehn Risikogruppen und dokumentierten Bedingungen vollständig und verbindlich.
>
> Release v0.1.0 bleibt NO-GO.

The adopted English decision texts follow. They reproduce the substantive accepted proposals; only the proposal-status heading is normalized to **Adopted decision**. References to closure following explicit adoption and controlled documentation are retained from the proposals: the adoption prerequisite has now been satisfied; the documentation/integration prerequisite must be completed through the normal independent review and Owner merge procedure.

## 3. M3.4-A — CF05-07 Owner Development Qualification Acceptance

**Adopted decision: OWNER ACCEPTED — CLOSED WITH DOCUMENTED LIMITATIONS**

The Product Owner formally accepts the integrated technical development qualification of `membersubscriptionmassaction` v0.1.0, covering CF05-01 through CF05-06 and their integrated operation across MariaDBSnapshot, CoreGuarded and EnhancedMySQL.

This acceptance is based on the independent verdict **APPROVED FOR DEVELOPMENT QUALIFICATION CLOSURE WITH DOCUMENTED LIMITATIONS**, the final independent documentation approval of PR #52, and its verified integration into `main`.

**Accepted development baseline:**

- Integrated main: `a19d7459550b9aebcb0f551925d8dfd0825c5ebe`
- Independently approved HEAD: `6bb2d38eecaf38fe1e3a9507695257759cb395cd`
- Identical approved and integrated tree: `b70d60455d5c5bbc255ec0934ca26bdfa73037ce`

The Owner accepts the independently assessed implementation satisfaction of all 71 requirements within the binding v0.1.0 scope, the source-bound CF03-NORMAL A–D results, and the accepted focused regression and test-hardening evidence.

All documented evidence boundaries, existing architecture and Owner decisions, and the M3.2 Single-Business-Entity scope limitation remain binding.

CF03-SECURITY-BASELINE remains PARTIAL. The four real cross-entity cases remain BLOCKED / NOT EXECUTED. Deferred destructive CF03-FAULT cases remain DEFERRED / NOT EXECUTED. No historical result is reclassified, and no broader real qualification is inferred.

This decision does not constitute integrated CF03-RISK acceptance, operational readiness acceptance, exact-package qualification or release authorization.

CF05-07 may be formally closed with documented limitations following explicit Owner adoption and controlled documentation of this decision. All applicable release gates remain open. Release v0.1.0 remains NO-GO.

## 4. M3.4-B — CF03-RISK Integrated Owner Residual Risk Acceptance

**Adopted decision: OWNER ACCEPTED — CLOSED WITH DOCUMENTED LIMITATIONS**

The Product Owner formally accepts the integrated technical residual risks of the complete `membersubscriptionmassaction` v0.1.0 implementation within its binding qualified scope.

This decision is based on the independent verdict **RESIDUAL RISK ASSESSMENT COMPLETE — OWNER ACCEPTANCE PENDING**, the integrated CF03-RISK review matrix in the ADR-0001 risk register, the independent development qualification assessment, and the verified integration of PR #52.

The accepted development baseline is main `a19d7459550b9aebcb0f551925d8dfd0825c5ebe`, with independently approved and integrated tree `b70d60455d5c5bbc255ec0934ca26bdfa73037ce`.

The Owner explicitly accepts the cumulative significance of the following documented residual risks:

1. Profile-specific transaction observation limitations, including potentially undetected physical transaction replacement and the bounded CoreGuarded observation contract under ADR-12.
2. Uncertain COMMIT, ROLLBACK and database completion, including potentially absent, committed or partially persisted business effects.
3. Multi-object financial and business effects that cannot be exhaustively observed or reconciled automatically.
4. F36-02 pre-COMMIT filesystem effects and the absence of joint database/filesystem atomicity.
5. F38-01 and CPC-01 Variant A, including profile-specific PDF UNKNOWN and STOP behavior; ADR-08B remains deferred.
6. ADR-08A positively isolated SMTP uncertainty and its strictly bounded continuation contract, without guaranteed delivery or automatic resend.
7. Bounded Core hooks, triggers, models and extension risks under Core-Parity Variant B, without blanket acceptance of unqualified extensions.
8. Authorization, entity and competing-selection risks within the accepted M3.2 Single-Business-Entity scope.
9. Concurrency, process interruption, session loss, checkpoint failure and the absence of a durable operations journal or system-wide exactly-once guarantee.
10. The remaining evidence limitations of deferred destructive real CF03-FAULT and full-workflow recovery and restore qualification.

All existing architecture decisions and runtime safeguards remain binding. CPC-02 standardizes the consequences of STOP without establishing identical STOP triggers across profiles.

Uncertain business effects require controlled manual reconciliation. No automatic retry, replay, resend, foreign rollback or compensation of uncertain operations is authorized.

This acceptance is limited to the documented v0.1.0 scope and qualified configurations. Materially different extension, financial, product, tax, stock or entity-sharing configurations require separate assessment and acceptance where applicable.

CF03-SECURITY-BASELINE remains PARTIAL. The four real cross-entity cases remain BLOCKED / NOT EXECUTED. Deferred destructive CF03-FAULT cases remain DEFERRED / NOT EXECUTED. All historical evidence and classifications remain unchanged.

This integrated technical risk acceptance does not constitute R0-05 operational readiness acceptance. Actual deployment suitability, usable reconciliation and recovery procedures, responsibilities, backup arrangements and operational acceptance remain subject to the applicable release gates.

CF03-RISK may be formally closed with documented limitations following explicit Owner adoption and controlled documentation of this decision.

No package installation, production operation, deployment, release publication or Owner Release GO is authorized by this decision. Release v0.1.0 remains NO-GO.

## 5. Binding acceptance conditions B1–B8

These conditions were explicitly accepted together with Decision B. The following English rendering preserves the accepted German decision conditions and consolidates existing safeguards rather than creating additional product requirements.

| ID | Condition | Binding scope |
| --- | --- | --- |
| B1 | Deployment scope | Only the qualified v0.1.0 functional scope and Single-Business-Entity operation. |
| B2 | Runtime protection | No weakening of the existing transaction, permission, entity, confirmation or effect safeguards. |
| B3 | Truthful outcomes | UNKNOWN, STOP, SKIPPED and NOT_PROCESSED remain distinct in their business and technical meaning; no unsupported reclassification. |
| B4 | No uncontrolled repetition | No automatic retry, replay, resend, foreign rollback or automatic compensation of uncertain operations. |
| B5 | Reconciliation | Uncertain business operations require separate verification of the database, financial objects, Documents and SMTP evidence where applicable before any further corrective decision. |
| B6 | Configuration and extensions | No transfer of this acceptance to materially different or unqualified configurations without the necessary separate assessment. |
| B7 | Backup and recovery | Operational procedures, responsibilities and recovery limitations must be addressed under R0-05; backup integrity alone does not establish a restore guarantee. |
| B8 | Release governance | R0-04, R0-05, R0-06, R0-07 and separate Owner Release GO remain fully mandatory; all other applicable R0 gates remain in force. |

## 6. Integrated risk disposition and traceability

All ten risk groups are OWNER ACCEPTED WITH DOCUMENTED LIMITATIONS **within the accepted v0.1.0 scope**. The following dispositions trace the adopted Decision B to the existing integrated risk matrix. They preserve the original risk descriptions, mitigations, evidence and individual decisions, not replace them with a new risk model or new evidence. No quantitative likelihood or severity assessment is inferred.

| Group | Risk-register reference | Integrated disposition / retained boundary |
| --- | --- | --- |
| 1 | R01/R02/R07; ADR-12 | Accept bounded observation and ownership limits across the actual profiles. Preserve ADR-12 for CoreGuarded only; MariaDBSnapshot's sampled state and EnhancedMySQL's event/SAVEPOINT checks retain their distinct limits. No physical non-replacement guarantee. |
| 2 | R03/R05/R06/R08 | Accept uncertain COMMIT/ROLLBACK and partial/absent/committed state with mandatory manual reconciliation. DB UNKNOWN stops all profiles; no foreign rollback, blind retry or inference of rollback from absent rows/zero depth. |
| 3 | R04/R10/R14 | Accept bounded multi-object financial and business effects, subject to qualified configuration, existing guard/rights/engine/locking/postcondition controls and separate reconciliation. No blanket claim for unseen trigger effects. |
| 4 | F36-02/R11 | Reaffirm the accepted pre-COMMIT PROV rename residual; no joint SQL/filesystem atomicity, blanket PROV veto or automatic filesystem compensation. Address Documents/backup/recovery implications in R0-05. |
| 5 | F38-01/R11; CPC-01 Variant A | Reaffirm the profile-specific PDF UNKNOWN contract. CoreGuarded keeps acknowledged Business SUCCESS but stops before SMTP and later members. A/C retain existing post-COMMIT handling; no unverified attachment in any profile. ADR-08B remains DEFERRED. |
| 6 | ADR-08A/R11 | Accept strictly isolated regular SMTP uncertainty within the existing CoreGuarded continuation contract: qualified nonterminal COMMIT, positively witnessed native transport isolation, original durable checkpoint/readback, effect authorization, fresh context and separate next-member admission. Mail remains UNKNOWN; no resend. Unqualified/contradictory evidence, ambiguous hooks/OAuth, or failed required persistence/readback do not qualify. |
| 7 | R11; Core-Parity Variant B | Accept ordinary Core extension behavior only within the qualified scope and existing safeguards. Novel materially different hooks/models/extension configurations retain conditional assessment and Owner-decision requirements; no universal extension safety claim or reintroduction of source/trigger allowlists. |
| 8 | R09/R13/R14; M3.2 | Reaffirm Single-Business-Entity scope and preserve every authorization, entity and confirmation safeguard. The four real cross-entity cases remain BLOCKED / NOT EXECUTED. Actual compliance with all seven M3.2 criteria remains mandatory under R0-04. No new runtime veto or multi-tenant isolation claim. |
| 9 | R09/R12 | Accept concurrency, crash/session-loss, checkpoint failure and no-journal residuals with existing scoped locking, revalidation, one-shot and terminal controls. Reconcile before any further action; no cross-session or system-wide exactly-once guarantee. |
| 10 | R03/R05/R06/R08/R12/R15; ADR-11 | Accept the evidence gap from deferred destructive full-workflow faults and restore scenarios, not a claim of their proven safety. Preserve DEFERRED / NOT EXECUTED, separate backup/restore limitations and operational escalation. |

CPC-02 remains binding: a decided STOP retains preceding/current results, assigns NOT_PROCESSED to unreached following selections, preserves genuine SKIPPED outcomes and exposes the batch STOP flag even on the last member. It does not grant new continuation rights. All profiles preserve acknowledged business results when later PDF/mail effects are uncertain; retain separate business/PDF/mail outcomes and profile-specific safeguards. The binding [profile-specific observation and continuation matrix](adr/ADR-0001-risk-register.md#profile-specific-observation-and-continuation) is unchanged in substance.

## 7. Evidence boundaries that survive M3.4

- CF03-NORMAL A–D: PASS WITH DOCUMENTED LIMITS in the respective source-bound case matrices. No single final-source A–D campaign and no exact ZIP qualification are claimed. The 29 compared productive blobs and scoped delta evidence support attribution, not retroactive expansion of individual cases.
- CF03-SECURITY-BASELINE: PARTIAL. P5L-A-S05, P5L-B-S05, P5L-C-S05 and D-S06 remain BLOCKED / NOT EXECUTED. M3.2 Cross-Entity Variant B is distinct from Core-Parity Variant B; one business entity, no qualified MultiCompany/inter-entity business sharing, no new blanket runtime veto. All [seven R0-04 compliance criteria](release-qualification-v0.1.0.md#r0-04-cross-entity-variant-b-compliance) require actual package/configuration verification.
- CF03-FAULT: DEFERRED / NOT EXECUTED for the destructive full-workflow real faults. No restoration or physical rollback proof is inferred. Historical RB01-A INVALID/STOP, B incident UNKNOWN/STOP and other prior invalid, uncertain, stopped or blocked observations remain unchanged.
- V22/V24 real evidence remains bounded to the recorded configurations; V23 remains synthetic-only. Technical admission from V21 does not establish V21 release qualification.
- A/B/C local SMTP capture is not external delivery. The controlled B SMTP case, D positive receipt/replay and separate natural-error observations retain their distinct evidence limits. CPC-02 live A/B/C evidence is contribution-only (invoice/mail deselected, no live induced STOP or D case); synthetic STOP coverage stays synthetic. Modified OD-P5-02 remains binding.
- C04-02, C05-06, C06-04 and C08-02 retain conditional targeted evidence needs only when materially different configurations are claimed. C08-02 real evidence remains limited to ordinary no-product/service-line VAT-zero configuration. No unrestricted product, tax, currency, stock or third-party extension qualification.
- No new source/trigger allowlist, PROV veto, general CoreGuarded finance denial, broad PDF continuation, payment/bank posting, isolated automatic Third-Party creation, recovery journal or automatic retry/resend/replay/compensation. O7 and all accepted deviations remain binding.
- M3.4 does not authorize production, new live tests, destructive fault injections, SMTP sends, profile/server changes, deployment or release. R0-05 must separately establish usable reconciliation procedures, suitable deployment responsibilities/permissions, backup and recovery arrangements and Owner operational readiness acceptance. If operational suitability cannot be demonstrated, withhold the applicable release decision.

## 8. Requirements dispositions — normative implementation mapping

This table is the documentation mapping derived from the Owner's adopted decisions, not a new Owner decision or evidence claim. In `docs/requirements.csv`, replace only the `Residual Risk:` segment of the final, four-dimensional M3.1 disposition for the ten listed IDs with the exact text below (without the table formatting). Keep the first 17 fields, row order, all historical final-field prefixes, the other three dimensions and all 61 other rows unchanged. The earlier individual Owner acceptances remain explicit.

| ID | Replacement text after `Residual Risk:` |
| --- | --- |
| C03-09 | OWNER ACCEPTED (M3.4-B integrated recovery/no-journal and replay residual within scope; mandatory reconciliation) |
| C03-11 | OWNER ACCEPTED (M3.4-B integrated competing-writer/concurrency residual within scope; existing revalidation retained) |
| C04-03 | OWNER ACCEPTED (ADR-12 bounded CoreGuarded observation only; M3.4-B integrated profile-specific transaction ownership/observation residual within scope) |
| C04-05 | OWNER ACCEPTED (M3.4-B integrated locking/competing-writer residual within scope; existing scoped locks and revalidation retained) |
| C04-06 | OWNER ACCEPTED (M3.4-B integrated duplicate/overlap and competing-writer residual within scope; existing locking checks retained) |
| C04-07 | OWNER ACCEPTED (M3.4-B integrated full-workflow rollback/recovery and uncertainty residual within scope; mandatory reconciliation and R0-05 remain) |
| C04-08 | OWNER ACCEPTED (ADR-12 bounded CoreGuarded observation; M3.4-B integrated foreign-context/ownership/completion residual within scope; R0-05 remains) |
| C05-11 | OWNER ACCEPTED (F36-02 and CPC-01 Variant A retained; M3.4-B integrated financial rollback and profile-specific PDF/SMTP post-COMMIT residual within scope including ADR-08A) |
| C06-04 | OWNER ACCEPTED (M3.4-B existing qualified Core hook/extension scope only) / OWNER DECISION REQUIRED (novel extension effects require separate disposition if included) |
| C06-08 | OWNER ACCEPTED (M3.4-B integrated replay/no-journal and transport uncertainty residual within scope; no automatic resend) |

For C04-07 and C04-08, the existing `OTHER APPLICABLE GATE` release clause includes a reference to CF03-RISK. Retain that clause as traceability to the now-accepted development risk gate; R0-05 remains OPEN. Do not treat the reference as an outstanding M3.4 Owner decision. Do not delete the conditional C06-04 decision or change a synthetic, blocked, deferred or conditional evidence classification.

Expected row-label counts (multiple scoped labels per row are intentional):

| Dimension | Before M3.4 | Expected after applying the ten replacements |
| --- | --- | --- |
| Implementation | SATISFIED WITHIN SCOPE 71; GAP 0; NOT APPLICABLE 0; SUPERSEDED 0 | Unchanged |
| Development Evidence | QUALIFIED WITHIN SCOPE 53; SYNTHETIC ONLY 62; TARGETED EVIDENCE OUTSTANDING 4; DEFERRED 13; BLOCKED 7 | Unchanged |
| Residual Risk | NONE IDENTIFIED 51; OWNER ACCEPTED 13; OWNER DECISION REQUIRED 10 | NONE IDENTIFIED 51; OWNER ACCEPTED 20; OWNER DECISION REQUIRED 1 (C06-04 only, conditional) |
| Release Qualification | PENDING R0-04 64; OTHER APPLICABLE GATE 7; NOT APPLICABLE 0 | Unchanged |

These expected values were derived from the actual 71-row, 18-column accepted CSV at the baseline and must be independently recalculated on the resulting file. They are not counts of new tests or of mutually exclusive classifications. Nine integrated decision-pending entries are resolved; C06-04 remains conditional outside the accepted scope and does not block bounded development closure.

## 9. Canonical status and transition rules

Separate binding decisions already taken from the independent approval and integration of their documentation. The stable closure criterion is integration of this independently approved documentation by the Owner. Use the canonical record and actual GitHub state rather than accumulating conflicting time-dependent active status blocks.

| Gate | Current substantive state following Owner declaration | Target upon independently approved closure documentation integration |
| --- | --- | --- |
| M1 — functional implementation | COMPLETE | COMPLETE |
| M2 — test hardening / PR #51 | COMPLETE WITH DOCUMENTED LIMITATIONS; seven findings independently closed | Unchanged |
| M3.1 — consolidated documentation | INDEPENDENTLY APPROVED AND INTEGRATED (PR #52); five findings closed | COMPLETE |
| M3.2 — Cross-Entity Variant B | OWNER ACCEPTED WITH DOCUMENTED SCOPE LIMITATION; independently verified and integrated (PR #52) | COMPLETE WITH SCOPE LIMITATION; actual R0-04 compliance pending |
| M3.3 — independent integrated review | COMPLETE | COMPLETE |
| CF05-07 | OWNER ACCEPTED WITH DOCUMENTED LIMITATIONS; independent technical verdict retained | CLOSED WITH DOCUMENTED LIMITATIONS |
| CF03-RISK | OWNER ACCEPTED WITH DOCUMENTED LIMITATIONS; independent assessment complete | CLOSED WITH DOCUMENTED LIMITATIONS |
| M3.4 | BOTH OWNER ACCEPTANCES GRANTED; documentation integration pending | COMPLETE |
| M3 — integrated development closure | TECHNICALLY AND OWNER ACCEPTED; formal documentation integration pending | COMPLETE WITH DOCUMENTED LIMITATIONS |
| Phase 5 — development qualification | OWNER ACCEPTED WITH DOCUMENTED LIMITATIONS; formal closure pending integration | DEVELOPMENT QUALIFICATION CLOSED WITH DOCUMENTED LIMITATIONS (development scope only) |
| CF03-NORMAL A–D | PASS WITH DOCUMENTED LIMITS | Unchanged |
| CF03-SECURITY-BASELINE | PARTIAL | PARTIAL |
| Four real cross-entity cases | BLOCKED / NOT EXECUTED | Unchanged |
| Deferred destructive CF03-FAULT | DEFERRED / NOT EXECUTED | Unchanged |
| PR #40 and R0-00–09 | Draft source refresh and remaining release work pending | All applicable remaining release gates stay OPEN; no release authorization |
| v0.1.0 release | NO-GO | NO-GO |

The original CF03-RISK verdict includes the historical words OWNER ACCEPTANCE PENDING. Preserve it as a dated independent verdict, not an active statement of the now-granted Owner decision. Similarly preserve the historical F52 corrective handoffs and any earlier gate/status snapshots with their original attribution. Update genuinely active tables, checkpoints and roadmaps, rather than reopening closed findings or rewriting historical evidence. In the closure PR, use condition-based wording for formal completion; do not pre-claim independent approval or merge. After the Owner's actual squash, verify the approved/integrated tree and record post-merge provenance in GitHub metadata; do not create a perpetual sequence of commits solely to update pre-merge time-dependent wording.

## 10. Remaining roadmap and release boundary

- M4 — Final release freeze: refresh [Draft PR #40](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/40) against the final accepted development baseline; R0-00/01/02, R0-03 reproducible ZIP and draft R0-06. Preparatory work may proceed; binding final source/package freeze follows formal M3 integration. PR #40 is not the M3.4 documentation PR and is not a final qualified artifact.
- Before final freeze, settle source/merge/build/tag provenance and exact artifact identity. The builder on PR #40 includes `source_commit` and `source_tree` in the embedded manifest, so a rebuild after squash can change the ZIP bytes even with the same tree. Qualify and publish exactly the approved bytes; if bytes change, obtain the required renewed exact-artifact assessment before publication. No release-procedure choice is made here.
- M5 — R0-04 targeted exact-ZIP installation/integration, including all seven M3.2 criteria, R0-05 operational readiness and Owner operating-risk acceptance, R0-06 final documentation. Targeted release qualification is not a blanket rerun of the accepted development cases. The concrete RC-01–11 allocation remains to be commissioned.
- M6 — R0-07 independent final release assessment and separate explicit Owner Release GO.
- M7 — R0-08 authorized release merge/tag/publication and R0-09 exact published asset/manifest/tag/hash verification and closure. Do not close [Issue #20](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/20) before verified publication and the applicable final Owner closure. Maintain [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22) with the actual integration/qualification state; this decision does not close the issue automatically.

M3.4 is development governance, not operational approval, a release freeze or release authorization. Release v0.1.0 remains **NO-GO** until the separate explicit Owner Release GO and all applicable gates are satisfied.
