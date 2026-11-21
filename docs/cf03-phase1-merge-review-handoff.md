# PR #32 – CF03 Phase 1 consolidated evidence and independent merge-review handoff

**Date:** 2026-11-21
**Branch:** `feature/cf03-qualification`; **base:** `main`.
**Review subject:** final PR #32 HEAD as fetched live at review time, including all privacy and evidence-root commits. This handoff deliberately does **not** freeze or pre-approve an intermediate SHA.

## Purpose, governance and excluded scope

This is a read-only witness and repository privacy/evidence consolidation PR, **not** the CF05 full-function implementation. Review current changed-file and commit delta, not only the earlier narrow 23-file W01 design. It includes historical redaction and CI/workflow changes as well as tool-security changes. The final merge requires an external independent review and a separate Owner decision; a green synthetic workflow is not a merge approval. No business database writes, booking retries, actual fault-injection, profile/account switches or release actions are performed by this branch work.

## Stable evidence – do not restamp or repeat

- **F32-W01-01 CLOSED:** independently reviewed on `e7b911b707508e439f9acbfad43f5fd55d34cc44`; active member type = `llx_adherent_type.statut=1`. The historical inverted assertion must not recur.
- **POS02-A1 / POS02-A2 TECHNICAL PASS:** the Owner accepted the evidence for two separate successful CoreGuarded contribution-only transactions, with Member Agenda ON/OFF and bounded DB postconditions, on the pinned earlier witness SHA. This is **not** full-function, physical event identity or release acceptance.
- **RB01-A INVALID / STOP:** the intended rollback injection was not performed; the real contribution persisted. Do not repeat the used member/subscription fixture, redo verify or silently relabel as ROLLBACK PASS.
- Redacted D01/AP3/CF02 historical file bytes differ from earlier reviewed bytes. Historic immutable commit-based hashes and acceptance do not automatically attest the newly redacted worktree copy. Original evidence is private; old public Git commits, links and caches have not been purged.

## Focused review targets

1. **Production scope:** Check all 28 production module paths changed in the full PR; these should have only copyright/contact text differences and no runtime statement changes. Review test/experiment PHP changes separately, including whether sanitized archived scripts are marked unqualified for actual execution.
2. **Privacy completeness:** Inspect the current tracked PHP, Markdown, TXT, experimental tools and historical evidence for residual personal or local-environment data without committing any list of actual identifiers. Check Linux/macOS/Windows home-path shapes manually, and explicitly distinguish current-tree sanitization from retained Git history. Any discovered identity must be replaced with a neutral placeholder, not stored in a source blacklist.
3. **Evidence parent model:** Confirm read/traverse for group/other is allowed by Owner; group/other **write must be denied**. Current CLI identity must be able to access the root. No mandatory parent ownership/0700 reintroduction by assumption. Confirm webroot/site-root/checkout ancestor/descendant exclusion applies to **A/B/C/D**, and alias/network-mount/same-UID risk stays explicitly in operator acceptance.
4. **One-shot filesystem:** Verify `mkdir(0700)`, no symlink, canonical path and case UID ownership; baseline file mode 0600; `fopen('x')` under temporary umask 0077 plus `fstat` mode check **before writing**, restored umask, exclusive markers, SHA256, finite directory inventory and permanent no-retry after attempted verify. Check safe failure/partial-write behavior.
5. **Witness provenance:** Check mandatory operator-only `CF03_* / CF03_EVIDENCE_ROOT` values, exact source manifest and bounded Core fingerprints, profile/account/DB/CLI PHP identity, V22/V24 source behavior, strict actual SQL-reader projection and mixed Member/Bill Agenda actor attribution, fail-closed unsupported line trigger.
6. **CI:** Verify exact latest PR HEAD standard **9/9** and hosted PHP8.3/V22 **1/1**, including focused offline evidence-root assertions. The optional 21-profile matrix remains required before full-function release; no persistent sensitive-term dictionary or scanner is a CI dependency. Recheck workflows and results on the new final HEAD.
7. **Documentation:** Preserve dated PASS/STOP and external reviewer access limitations. Explicitly distinguish independent code finding closure, later Owner technical POS02 disposition, no successful rollback, old SHA vs current, no ZIP qualification, and the newer Issue #22 ADR-06/11 decisions superseding older historical prose.

## Decision requested

The independent reviewer should return **APPROVED FOR DEVELOPMENT MERGE WITH DOCUMENTED SCOPE**, **CHANGES REQUIRED**, or **INCONCLUSIVE – ADDITIONAL EVIDENCE REQUIRED**, with exact HEAD and source/CI pins, P0–P3 findings and file/line references. Include a separate judgment on the evidence-root trust boundary and privacy current-tree scan. No reviewer acceptance or Owner GO is asserted by this handoff.

## After an affirmative review

Confirm HEAD/CI unchanged, reconcile review findings, obtain explicit Owner development-merge GO, then merge normally. Only afterward start a separate focused CF05 financial/guard PR. Do not close Issue #22 or claim CF03 integration/release qualification because this test-tool PR was merged.
