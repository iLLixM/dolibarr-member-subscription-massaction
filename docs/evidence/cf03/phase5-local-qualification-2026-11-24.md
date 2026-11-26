# Phase 5 local qualification — consolidated Owner-accepted results

**2026-11-24 · Phase 5 PARTIAL · v0.1.0 NO-GO.** This is a compact result register, not a raw-evidence archive. The Owner approved OD-P5-01 and OD-P5-03 and modified OD-P5-02: the completed, authorized Codex assignments' result summaries and confirmations are sufficient test/validation evidence, with their original outcomes and bounded scope. External reproduction of private real-test artifacts is not required for this acceptance. The external holistic review checked the public documentation and CI, not the underlying private runs. Independent review completed APPROVED WITH DOCUMENTED LIMITATIONS; [PR #44](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/44) squash-merged as `e57ee41248306797678f0c5ccd5251fd13474465`, identical tree `24a3e437a768c7e685e5a732d2602b2cd2680661` to reviewed `52d9ea4bf9799e714d5a2a2238217c4bd2ac4b3f`; 31/31 CI. #41–#43 CLOSED WITHOUT MERGE – SUPERSEDED BY PR #44. Full independent link/anchor recheck incomplete; no concrete broken new link identified.

## Source and authorization

All new local cases below ran on deployed module `ba5c9a3121754fcbd4259bb8906ad1dd17f1ce00`, after the Phase 4 [PR #38](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/38) and documentation [PR #39](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/39) squash merges. Local Core V24.0.3 (`5c6c7ff6a5112413b1c27221c8d11750afad56f8`), PHP 8.2.35; A MariaDB 10.11.19, B/C MySQL 8.4.13. [Canonical environment matrix](../../dolienv-environments.md) defines the profiles and separation. These are not package-candidate or Hosted D runs.

| Dated source checkpoint | Exact source PR HEAD | Owner-authorized scope / recorded status |
| --- | --- | --- |
| [PR #41](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/41) | `3e01c076c06138551712dbf16095ac4ecff0c991` | OD5-01–08 as adapted; initial local readiness and financial/PDF cases, B02 mail STOP; [#22 checkpoint](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22#issuecomment-7132523056) |
| [PR #42](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/42) | `4178aaa9df4ce97972c6ba9ceb499b3cab311987` | OD5-N01–04; isolated B02 diagnosis and controlled configuration return; approved non-mail cases; [#22 follow-up](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22#issuecomment-7133574867) |
| [PR #43](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/43) | `0fad6a06675157904a11ea9de8b90dfe2a4e14b5` | Separate Owner GO for two fresh B mail confirmations (one and two members); [#22 mail checkpoint](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22#issuecomment-7134307772) |

The independent holistic review assessed these exact heads. Each documentation PR had Standard 9/9 CI PASS ([#41](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/45399202206), [#42](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/45408544407), [#43](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/45413589726)); no full/hosted run was reported on those heads. CI is synthetic, not the real-test outcome. The original PRs retain their dated statements and immutable source commits; the current result register below supersedes earlier *status*, not earlier outcomes.

## Checkpoint 1 — initial Phase 5 readiness and finance (PR #41)

| Case/group | Env / profile | Owner-accepted outcome and scope |
| --- | --- | --- |
| 5A local preflight | A/B/C | PASS: 21 protected loaded-Core/reader checks; target identities, source, profile, selected InnoDB tables, file sessions/logger, separate SELECT readers; current DB/Documents backup hash and permissions verified. |
| B01 initial launcher | B / CoreGuarded | STOP before fixtures; narrow shared-worktree Git-index read ACL authorized, unchanged SQL baselines verified before continuation. |
| PHASE5-B01 | B / CoreGuarded | PASS: new member, existing customer, manual end, acknowledged contribution, validated EUR 120 invoice/line/link, Agenda and physical PDF; no mail selected. |
| PHASE5-B02 | B / CoreGuarded | Finance/PDF PASS; mail and attachment UNKNOWN/STOP. Automatic end, new customer, acknowledged completion, validated invoice/line/link, physical PDF and three Agenda pairs. No new capture observed; no retry. |
| PHASE5-A02 / PHASE5-C02 | A / MariaDBSnapshot; C / EnhancedMySQL | PASS each: existing customer, contribution, invoice/line/link, expected Agenda and PDF; invalid CSRF and consumed POST rejected without extra business change. Four-role browser checks passed. |

The reported independent reader reconciled 24 business tables per finance case and the PDF witness checked the physical documents; cross-environment snapshots remained unchanged as recorded. Local readiness is accepted for the tested cases, not blanket F32-MR-01 or A–D acceptance. At that initial checkpoint D's separate test copy was not yet provisioned; later focused evidence is recorded below.

## Checkpoint 2 — B02 diagnosis and non-mail matrix (PR #42)

[Original technical summary](https://github.com/iLLixM/dolibarr-member-subscription-massaction/blob/4178aaa9df4ce97972c6ba9ceb499b3cab311987/docs/phase5-b02-nonmail-followup.md): the historical UTC Core log and offline reproduction report that Core V24.0.3 `SMTPs::_server_connect()` rejects the numeric loopback host at the `is_ip()`/hostname validation branch (error 99 before opening a socket). The later direct FPM socket probe bypassed that check, reached envelope recipient acceptance and ended before DATA. The public Core mechanism was found technically plausible in independent review. The Owner accepts the reported diagnosis. No module code correction was indicated. The historical B02 mail/attachment result stays UNKNOWN/STOP; its finance/PDF PASS is unaffected.

The initial template return stopped on NULL-note normalization by the Core setter. The Owner-approved correction changed only that note. A separate reader confirmed the exact prior setting and 264 unrelated settings unchanged. No booking or mail replay, compensation or retrospective reclassification.

**Eight planned non-mail cases — PASS on the recorded scope.** All used protected preflight, fresh fixtures, 24-table reconciliation, Documents and settings checks; B/C used separate windows. Mail disabled/deselected throughout.

| Case | Env / profile | Confirmations | Contributions | New customers / invoices / PDFs | Agenda pairs | Result |
| --- | --- | ---: | ---: | --- | --- | --- |
| A-auto | A / MariaDBSnapshot | 1 | 1 | 1 / 1 / 1 | Standard effects; individual count not stated | PASS |
| C-auto | C / EnhancedMySQL | 1 | 1 | 1 / 1 / 1 | Standard effects; individual count not stated | PASS |
| A-on | A / MariaDBSnapshot | 2 | 4 | 0 / 0 / 0 | 4 | PASS, corrected continuation |
| A-off | A / MariaDBSnapshot | 1 | 2 | 0 / 0 / 0 | 0 | PASS |
| B-on | B / CoreGuarded | 2 | 4 | 0 / 0 / 0 | 4 | PASS |
| B-off | B / CoreGuarded | 1 | 2 | 0 / 0 / 0 | 0 | PASS |
| C-on | C / EnhancedMySQL | 2 | 4 | 0 / 0 / 0 | 4 | PASS |
| C-off | C / EnhancedMySQL | 1 | 2 | 0 / 0 / 0 | 0 | PASS |
| **Total** | | **11** | **20** | **2 / 2 / 2** | **18** | **8/8 PASS** |

Agenda attribution: ON = 12, OFF = 0; the two auto cases together account for the remaining 6 by subtraction from the reported total. The summaries do not specify the split between A-auto and C-auto; no per-case split is asserted. ON batches cover automatic/manual dates and R16 later-end retention; OFF disables the contribution auto-event, not the Agenda module. B acknowledges nonterminal completion per member.

Invalid CSRF, stale revision and consumed replay rejected without additional changes in the ON cases. Administrator/integration versus read-only/denied role checks passed; expiry and wrong entity are offline checks only. Original A-on harness STOP occurred before a positive submission; unchanged post-fixture baseline verified. The corrected, Owner-approved fresh-session continuation is the PASS above, not a reclassification of the STOP.

## Checkpoint 3 — local B mail and PDF attachments (PR #43)

[Original mail summary](https://github.com/iLLixM/dolibarr-member-subscription-massaction/blob/0fad6a06675157904a11ea9de8b90dfe2a4e14b5/docs/evidence/cf03/phase5-local-b-mail-attachment-2026-11-24.md). Actual B PHP-FPM/Core preflight resolved the configured hostname to the isolated loopback capture and reached recipient acceptance; both envelope probes ended before SMTP DATA. Initial launcher STOP was before any HTTP request; corrected launcher rechecked unused fixtures.

| Fresh authorized bulk run | Confirmations | Members / acknowledged contributions | Validated invoices / physical PDFs | Local captures / matched MIME PDFs | Result |
| --- | ---: | ---: | --- | --- | --- |
| B-mail-single | 1 | 1 | 1 / 1 | 1 / 1 | PASS |
| B-mail-double | 1 | 2 | 2 / 2 | 2 / 2 | PASS |
| **Total** | **2** | **3** | **3 / 3** | **3 / 3** | **3/3 PASS** |

Group labels in this register identify the two documented runs, not private fixture IDs. Each decoded PDF attachment matched the corresponding physical invoice PDF in size and SHA-256; 24 business tables per run reconciled, existing rows and A/C snapshots unchanged. SMTP and template settings returned to exact prior values; 263 unrelated settings checked. Captures remained local; external delivery, A/C mail, and Hosted D are not demonstrated. No extra raw artifacts are included.

## Status and remaining gates

The three checkpoints are successive, disjoint reported campaigns, not additional copies of the same totals. The eight-case subtotal and three-member mail subtotal exclude the initial finance cases. Historical `DOLIENV-ACC-01` stays UNKNOWN/STOP with unconfirmed rollback; later SQL absence is not rollback acknowledgment. B02 mail/attachment UNKNOWN/STOP and both pre-execution STOP records remain separate; no retry or compensation.

Full CF03-NORMAL A–D, A/C module mail/attachments, D finance/PDF/mail/attachments and wider live safety/configuration coverage, CF05-07/CF03-RISK, R0-03/04/07 and Owner Release GO remain open; CF03-FAULT destructive cases remain deferred. F36-02 is an Owner-accepted Core residual risk. See [current baseline](../../current-development-baseline.md) and [release gate map](../../release-qualification-v0.1.0.md).

Draft [PR #40](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/40) at `b19fbe7a80a97a45e8fa5d8aa96cecf5bcf4fa0d` prepared a reproducible candidate, reported two byte-identical builds with 32 source-matched payload files, ZIP SHA-256 `8dce6219baff656edbca48f0fe3d969eaf9f2aa801eacbc11426c0097253a37e` and synthetic CI 31/31 PASS. That is historical candidate identity, not final R0-03 acceptance. The builder embeds source commit/tree; later package source changes require regenerated identity, payload comparison and exact-package qualification. PR #40 remains Draft, unmerged; it lacks #45 and requires source refresh, regenerated package identity and exact-package qualification. No release approval.

## Subsequent Hosted D checkpoint — PR #45 (separate from local totals)

[PR #45](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/45) squash-merged as `b026f1504db27308fc7326e2e88ec2e53b2f48d9`, parent `e57ee41248306797678f0c5ccd5251fd13474465`, identical tree `29873481b9d24aaf56836d46c934f710af24cecc` to independent review HEAD `7e5f64d07ab3830ce355cd745782c4f0c2a955b0`. Verdict: APPROVED FOR DEVELOPMENT MERGE WITH DOCUMENTED LIMITATIONS; exact-head [Standard 9/9](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/45501058019), [Full 21/21](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/45501057921), [Hosted 1/1](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/45501057914), 31/31 SUCCESS. D real implementation `0adc259d4739b44f9b1fb3a49574d7e1c318132c`; the next commit changed only four Markdown files. No real test is relabeled as a run on the squash commit.

Hosted D has a fresh independent checkout/dedicated schema (Dolibarr 22.0.5, Oracle MySQL 8.4.13, Web PHP 8.4.28 CGI/FastCGI, CLI PHP 8.3.37). On `0adc259d4739b44f9b1fb3a49574d7e1c318132c`, four role-dependent UI checks and two distinct contribution-only bookings PASS; focused follow-up persisted result after reload, rejected one consumed replay and separate hosting-local SQL readbacks confirmed unchanged member state after replay. Provider session defaults unchanged. This is not D finance/PDF/mail or exact-ZIP PASS. Accepted under modified OD-P5-02, not independently externally reproduced. The pre-fix refusal on merged #44 was before transaction entry; preserve it as an earlier observation, not retroactive PASS. See [canonical D evidence](../../dolienv-environments.md#hosted-d-checkout-and-pr-45-validation-2026-11-24) and [current baseline](../../current-development-baseline.md).
