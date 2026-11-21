# v0.1.0 release qualification – current gate map

**Policy as of 2026-11-21.** This is a documentation index for the binding [Issue #22 full-function strategy](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22) and the [Issue #20 release tracker](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/20). Issue #20 contains historical contribution-only/pre-refresh statements; the newer Owner decision in Issue #22 prevails. **Release is NOT APPROVED.**

## Release target

One installable `v0.1.0` ZIP, without any release-candidate suffix in final package metadata or shipped docs, delivering the full contribution, optional invoice/Third Party, PDF and mail workflow on **CoreGuarded, EnhancedMySQL and MariaDBSnapshot**. Third Party auto-creation is allowed only with an invoice; no automatic payment or bank booking. Profile observation strengths remain intentionally different.

**Dolibarr V22/V23/V24 compatibility remains Owner-confirmed**, not universal real integration proof. Synthetic V23 is available; no completed real V23 full-feature test is asserted. MySQL >=8.4 / MariaDB >=10.11; PHP >=7.4 subject to Core prerequisites; PostgreSQL excluded. The unreserved module ID `584620` is retained for this bounded release by Owner decision; no permanent registration is implied.

## Outstanding gates – ordered

| Gate | Evidence to be produced | Status |
| --- | --- | --- |
| CF05-01..07 | Focused profile-shared active guard, full financial feature gates, classified errors/rollback, controlled post-commit PDF/mail, Core call-graph/engine qualification, synthetic regressions, exact-head independent security review, Owner acceptance | **OPEN: full feature not implemented** |
| CF03-NORMAL | New, individually Owner-authorized real success cases A–D across Agenda ON/OFF, invoice-existing/auto-Third-Party, line/validation/link, PDF, selected attachment/isolated SMTP, small successful batch and independent DB/FS/delivery reconciliation | **OPEN: not inherited from POS01/POS02** |
| CF03-SECURITY-BASELINE | Retained negative/security/fault synthetic regression, authenticated safe replay/rights tests, **21-job PHP/Core CI**, profile/guard/NO_CHAIN/terminal STOP assertions, independent exact-HEAD code/evidence review | **OPEN for full-feature code** |
| CF03-RISK | Independent comparison of detectable/undetectable transaction outcomes, weaker CoreGuarded financial-object risk, environment/extension assumptions and **separate Owner acceptance** | **OPEN** |
| R0-03/new package | Fresh source-matched deterministic ZIP, manifest, checksum, sanitized deliverable and correct 0.1.0/version metadata | **OPEN; PR #19 bytes invalid for this baseline** |
| R0-04 | Actual Dolibarr **D** restricted-hosted **exact ZIP** installation plus required package regression/functional security checks | **OPEN** |
| R0-07 | Independent final release/artifact assessment followed by separate Owner release GO, tag/publication control | **OPEN** |
| CF03-FAULT | Purposeful destructive real rollback, reconnection/lost ACK, hidden implicit completion, SQL faults, concurrency and destructive replay | **DEFERRED TO LATER ITERATION; OPEN/NOT EXECUTED, not release PASS** |

Real destructive tests remain deferred **without relaxing runtime safety**; the release still requires synthetic security/transaction fault coverage and safe authenticated negative tests.

## Reusable but strictly historical evidence

- [PR #29](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/29) = CoreGuarded contribution-only implementation; owner-operated POS01-C PASS, not invoice-qualified.
- [PR #32](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/32) = read-only witness/privacy/CI infrastructure; **squash merged 2026-11-21** to `425980c43a51a9f27073133212f18825ac6da248`, reviewed head `cbc97843fb017135739a39ea12ef26c7fa66dfdb`; 9/9 + 1/1 CI PASS. Accepted F32-MR-01 launcher prerequisite **not yet tested**, accepted F32-MR-02 historic prose deviation. POS02-A1/A2 Owner TECHNICAL PASS; RB01-A INVALID as rollback proof.
- [PR #19](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/19) = **closed without merge**. Its earlier `v0.1.0` ZIP and hash are historical preparation only and must never be treated as the qualified new release artifact.
- [Historical validation](phase4-implementation-validation.md) = bounded V22/V24 invoice, browser, database and SMTP evidence on other source/profile combinations. Neither a production qualification nor a substitute for new CF05 full workflow acceptance.

## Operational constraints inherited by release

A/C cannot be operated concurrently because they share schema/webroot and use separately selected DB profiles. For any further A/B/C CF03 witness runs, a private target-user-environment launcher passing mandatory `CF03_*` values must pass read-only preflight before a new real case. D needs explicit trusted evidence isolation outside HTTP aliases. Use distinct fresh one-shot test cases with prior Owner GO, and never retry consumed or unknown cases.

No automatic retry, compensation, foreign rollback, journal, exactly-once guarantee or post-commit PDF/mail after unknown/terminal completion. Accept only configuration and Core/trigger paths shown to be qualified in new evidence. A release tag or package distribution requires a separate decision after all mandatory gates.
