# Documentation guide and evidence provenance

**Current orientation (2026-11-21):** The authoritative product and architecture strategy is the newest Owner decision in [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22). This guide classifies documentation by **current policy** versus **historical evidence**, so earlier decisions can be audited without being mistaken for current implementation or release approval.

## Read these first

| Purpose | Primary document | Interpretation |
| --- | --- | --- |
| Product status, capability matrix, safety, CF05/CF03 roadmap | [Current development baseline](current-development-baseline.md) | Current cross-document summary; Issue #22 remains the authority |
| Intro, supported compatibility target and development setup | [README](../README.md) | Current user/developer-facing overview; no release approval |
| Transaction design and accepted observation limits | [ADR-0001](adr/ADR-0001-core-first-transaction-profiles.md) | **2026-11-21 ADR-06/11 amendment first**; dated 2026-11-18 text retained as history |
| Risks and owner acceptance | [ADR risk register](adr/ADR-0001-risk-register.md) | Current addendum plus historical technical R01–R15 |
| CF03 one-shot witness and safe operating conditions | [CF03 qualification plan](cf03-qualification-plan.md) and [runbook](cf03-operator-runbook.md) | Current merged status + F32-MR-01 launcher restriction precede old commands |
| Implemented invoice/third-party source architecture | [Phase 4 invoice architecture](phase4-invoice-architecture.md) | Historical implementation and version-specific proof; new three-profile CF05 target not yet delivered |
| Testing | [Test strategy](test-strategy.md), [coverage legend](test-coverage.md), [requirements.csv](requirements.csv) | Original **71 Cxx rows** preserved; new CF05-01..07 / CF03 work tracked in Issue #22 until mapped in implementation |
| Release gates | [Issue #20](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/20), [release qualification](release-qualification-v0.1.0.md) | Historical #19 artifacts are not the future v0.1.0 ZIP |

## Historical / immutable evidence – retain but do not upgrade

- [CF-00 review handoff](adr/ADR-0001-review-handoff.md): 2026-11-18 architecture review, original F23 findings and source-specific conclusions.
- [Phase 4 implementation validation](phase4-implementation-validation.md), [Phase 4 original implementation plan](phase4-invoice-implementation-plan.md), [Phase 3B](phase3b-validation.md) and [Phase 3A](phase3a-validation.md): historical V22/V24 and owner-operated test provenance.
- [CF02 validation](cf02-implementation-validation.md), [positive witness](cf02-positive-contribution-lab.md), [CF03 W01 review](cf03-w01-validation.md), [PR32 review handoff](cf03-phase1-merge-review-handoff.md): distinct pinned program versions and evidence. POS02-A1/A2 = Owner TECHNICAL PASS only; RB01-A = INVALID/STOP as rollback proof.
- `docs/evidence/` and `experiments/`: original dated snapshots and pseudonymized historical scripts. Redaction changes bytes; original fingerprints are not automatically valid for the new redacted files. Stored examples are not new real-test authorization.
- [Porting plan](porting-plan.md), [P3 qualification](p3-qualification-summary.md), [CHANGELOG](../CHANGELOG.md): preserve earlier milestones with historical dating.

**Evidence categories:** IMPLEMENTED code; SYNTHETIC PASS; bounded real Owner PASS; externally independently reviewed status; historical/reclassified INVALID; NOT EXECUTED; and future qualification gates are different things. A merge or CI pass never automatically upgrades another category.

## Work and acceptance boundaries

1. Document current plan separately from proven feature flags; do not call the as-yet unimplemented CoreGuarded invoice/PDF/mail flow live.
2. Keep one-shot case evidence private and immutable. Never reuse members or contribution fixtures already consumed in POS01/POS02/RB01.
3. For A/B/C witness operation, F32-MR-01's private-launcher/read-only preflight restriction is mandatory; F32-MR-02 is an accepted *historical* documentation finding, not permission for writable evidence parents.
4. A/C share the same reference schema and must remain serial. No new profile, grant, business write, restart or destructive test follows from documentation changes.
5. Review the final exact commit, CI and independent findings before Owner development merges. CF05 implementation, normal real integration, release and risk acceptance each have separate gates.
