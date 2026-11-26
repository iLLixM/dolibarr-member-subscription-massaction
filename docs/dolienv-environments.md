# dolienv environments and bounded acceptance

**CPC-02 (C04-04).** Once a profile has decided to stop, the shared batch path preserves preceding and current outcomes, marks every following selection NOT_PROCESSED and persists the explicit STOP flag even on the final member. Genuine admission or business skips remain SKIPPED. Business outcomes and profile-specific completion evidence remain separate. See [CPC-02 qualification](test-coverage.md#cpc-02-cross-profile-batch-results). The CPC-02 live delta is limited to A/B/C.

**2026-11-26 ADR-08A status:** ADR-08A technical implementation ACCEPTED on `8bc36c4d4aab423e644276999066339ec1a5fd3c` (independent review 2026-11-26); local controlled B and positive A/C evidence on `1ac1a8f2dfd7384829fa5b85235f1e4884d689db`; Hosted D delta PASS WITH DOCUMENTED LIMITATIONS on the reported integrated checkout `8bc36c4d4aab423e644276999066339ec1a5fd3c` (positive SMTP/replay and separate Owner-run natural-error observation). Formal development qualification CLOSED WITH DOCUMENTED LIMITATIONS; F49-01/P2 and F49-02/P3 independently closed before the verified #49 squash. F49-03/P3 OPEN, NON-BLOCKING TEST HARDENING. No additional real tests required for this bounded acceptance. See [Hosted D delta evidence](evidence/cf03/adr-08a-hosted-d-2026-11-26.md). The [current development baseline](current-development-baseline.md) governs current qualification and supersedes earlier dated checkpoints.

**2026-11-25 local Phase 5 delta:** See the [current A/B/C results and exact source](evidence/cf03/phase5-local-completion-2026-11-25.md). A and C mail/verified PDF attachments each passed 3/3 on `c2dc2cfdadf2c4def6bcd2f2ec20473cea3683b3`; B's earlier 3/3 remains on `ba5c9a3121754fcbd4259bb8906ad1dd17f1ce00`. These are capture-only outcomes, not external delivery or nine cases on a single source. Local CF03-NORMAL A/B/C: **PASS within the accepted case matrix**. Local CF03-SECURITY-BASELINE and local Phase 5: **PARTIAL**; P5L-A/B/C-S05 real cross-entity qualification **BLOCKED** (no distinct authorized second business entity). The live Web aged-fixture expiry result does not establish natural elapsed-time expiry. Earlier results retain their exact source and scope. Further local tests are limited to outstanding cases and change-induced regression; do not repeat accepted cases without a concrete reason.

**2026-11-25 Hosted D completion:** [Bounded development qualification and live-security evidence](evidence/cf03/phase5-hosted-d-2026-11-25.md). Earlier D contribution/session/replay PASS remains on `0adc259d4739b44f9b1fb3a49574d7e1c318132c`. The new cases on merged PR #46 `c2dc2cfdadf2c4def6bcd2f2ec20473cea3683b3` establish D-F01–F03 finance/third party and D-P01–P03 PDF 3/3 PASS; D-S01–S04 live role/CSRF/stale confirmation/batch-limit PASS; D-S05 natural one-hour expiry in the original authenticated session PASS (zero booking POSTs, 11 unchanged business tables); D-S07 bounded nonterminal completion/result reload PASS; D-AOFF PASS (automatic contribution Agenda event disabled, original setting restored). D-SMTP2: three successful SMTP handoffs and Owner-confirmed external test-mail receipts with matching PDF attachment hashes (one plus two members); original D-M01/M02 isolated no-relay capture remains historically BLOCKED, zero sends in those cases. Independent raw SMTP/MIME capture is not established by SMTP2, and SMTP acceptance alone is not delivery. Historical SMTP UNKNOWN/STOP remains unchanged despite later receipt confirmation. Hosted D CF03-NORMAL: **PASS within the approved development case matrix**. Live CF03-SECURITY-BASELINE: **PARTIAL**; D-S06 real cross-entity **BLOCKED** (no authorized second entity). ADR-08A delta and exact-release-ZIP R0-04 remain separate. Earlier dated evidence retains its actual source and scope. The independent documentation review accepted the reported evidence under modified OD-P5-02; it did not repeat real tests.

## Post-PR #38 source and acceptance handoff

**Phase 4 development provenance (2026-11-24):** [PR #38](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/38) was independently reviewed and **squash-merged** into `main` as `0998fbaf58155cca04d31ab99c6bb47420e53c72`; reviewed HEAD `b15b93580f995fb05a696a125c2a5fd995dd4bb3` has **identical Git tree** `a6e7256fb1572361c4dd4d850124ca7a6afff566`. The independent verdict was **APPROVED FOR DEVELOPMENT MERGE WITH DOCUMENTED LIMITATIONS**, with exact-source Standard 9/9 + Full 21/21 + Hosted 1/1 (**31/31**) synthetic CI PASS. Phase 4 development is merged; **Phase 5 real A–D integration, CF05-07, package, hosted and Owner release acceptance are NOT complete**. All older dated DRAFT/finance-default-deny/core-source-allowlist statements below are historical and not current requirements.

**Independent disposition:** F38-01–06 `CLOSED`; F38-07–09 `SUPERSEDED BY OWNER VARIANT B` (not technically closed); F38-10–13 `INDEPENDENTLY CLOSED`; F38-14/P2 `INDEPENDENTLY CLOSED` (server-confirmed `manualend` restored only after fresh fingerprint revalidation); F36-01 historically closed. **Separate real incident:** `DOLIENV-ACC-01` in B remains `UNKNOWN/STOP`, diagnostic subscription #3 and rollback unconfirmed; lack of a persisted record does not prove rollback ACK. No replay/compensation. New B01/B02 and B-mail cases on the later merged baseline have accepted positive finance outcomes; the historical incident is unchanged.

**Source identities:** A/B/C previous real acceptance used module `46f78d8f8f2c6f70424754d82171697e74ac4448`; last independent reviewed fix `b15b93580f995fb05a696a125c2a5fd995dd4bb3` was merged tree-identically as `0998fbaf58155cca04d31ab99c6bb47420e53c72`. The accepted local campaign ran on the merged PR #39 baseline `ba5c9a3121754fcbd4259bb8906ad1dd17f1ce00`; accepted results are recorded below. The old DOLIENV-ACC-01 rollback remains unconfirmed despite independent SQL absence.

**Historical accepted Phase 5 checkpoint (2026-11-24; original source scope):** Owner-accepted local cases on `ba5c9a3121754fcbd4259bb8906ad1dd17f1ce00`: A/B/C protected readiness 21 checks PASS; B01/B02 and A02/C02 bounded finance/PDF PASS; additional non-mail 8/8 PASS (11 confirmations, 20 contributions, two invoices/PDFs, 18 Agenda pairs); B mail/verified attachment 3/3 PASS in two bulk confirmations, capture-only. Hosted D has a fresh independent checkout/dedicated schema (Dolibarr 22.0.5, Oracle MySQL 8.4.13, Web PHP 8.4.28 CGI/FastCGI, CLI PHP 8.3.37). On `0adc259d4739b44f9b1fb3a49574d7e1c318132c`, four role-dependent UI checks and two distinct contribution-only bookings PASS; focused follow-up persisted result after reload, rejected one consumed replay and separate hosting-local SQL readbacks confirmed unchanged member state after replay. Provider session defaults unchanged. That 24 November checkpoint did not cover D finance/PDF/mail; the later 25 November cases above do. Neither is exact-ZIP acceptance. Modified OD-P5-02 accepts authorized summaries within their recorded scope; no independent external reproduction claimed. See [local qualification register](evidence/cf03/phase5-local-qualification-2026-11-24.md) and [environment evidence](dolienv-environments.md).

**Post-PR #45:** Development fix squash-merged as `b026f1504db27308fc7326e2e88ec2e53b2f48d9`; see [current baseline](current-development-baseline.md) for exact source/merge provenance and F45 follow-up.

**Operational/release boundary:** Phase 5 PARTIAL. Per-case Owner GO, protected F32-MR-01 target-user preflight, actual source/profile/role binding, fresh fixtures, controlled backups, read-only baselines and independent after-state remain required. CF03-NORMAL A–D passes within the respective accepted source-bound development matrices, not one final source or release artifact. A/B/C mail/attachments 3/3 each are capture-only on their respective sources; D-SMTP2 3/3 is Owner-confirmed external receipt and verified attachment identity on `c2dc2cfdadf2c4def6bcd2f2ec20473cea3683b3`, not independent raw MIME or isolated no-relay capture. Live security/configuration remains PARTIAL: P5L-A/B/C-S05 and D-S06 real cross-entity BLOCKED; synthetic entity coverage remains separate. Only outstanding or change-induced development qualification remains; do not blanket-repeat accepted cases or automatically repeat the historical D-M01/M02 blocked capture campaign. [PR #48](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/48) documentation is independently accepted and squash-merged as `316e8a8d196fe908d11cf3d4409d0a5e541e6c01`. [ADR-08A / D-MAIL-01](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22) is technically accepted in [PR #49](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/49); local and Hosted D delta PASS within their separate evidence limits; formal documentation follow-up closed by the verified #49 squash. See [Hosted D delta evidence](evidence/cf03/adr-08a-hosted-d-2026-11-26.md). F38-02 retains historical closure; only mandatory batch termination solely for isolated mail transport uncertainty is partially superseded, with UNKNOWN/no-resend and all safety-critical STOP safeguards retained. No new behavior is attributed to earlier tested sources. Full final A–D acceptance, CF05-07/CF03-RISK, R0-03 final ZIP, R0-04 exact-package integration (OPEN, execution blocked until final ZIP is available), R0-07 independent release review and separate Owner Release GO remain OPEN / NO-GO. B02 mail/attachment, DOLIENV-ACC-01 (rollback unconfirmed), and historical D SMTP UNKNOWN/STOP remain unchanged; no replay, resend or compensation. CF03-FAULT destructive tests remain DEFERRED / NOT EXECUTED. No operational or release GO follows from documentation.

Current Owner-requested environment convention, **2026-11-24**. This document is
public and pseudonymized. It describes deployment/test topology and observed
results; it introduces no transaction policy, product change or release approval.

## Current matrix

| Environment | Location / Core | Database server | Transaction profile | PHP evidence |
| --- | --- | --- | --- | --- |
| **A** | Local, Dolibarr 24.0.3 | MariaDB 10.11.19 (`10.11.19-MariaDB-0+deb12u1`) | **`MariaDBSnapshot`** | PHP-FPM / CLI 8.2.35 |
| **B** | Local, Dolibarr 24.0.3 | Oracle MySQL Community Server 8.4.13 | **`CoreGuarded`** | PHP-FPM / CLI 8.2.35 |
| **C** | Local, Dolibarr 24.0.3 | Oracle MySQL Community Server 8.4.13 | **`EnhancedMySQL`** | PHP-FPM / CLI 8.2.35 |
| **D** | Restricted shared hosting, Dolibarr 22.0.5 | Oracle MySQL Community Server 8.4.13 | **`CoreGuarded`** | PHP 8.4.28 (Web, `cgi-fcgi`), PHP/mysqlnd 8.3.37 (CLI) |

The MariaDB-branded command-line client used on D does **not** make its server
MariaDB. D's server family/version was confirmed by hosting-local SQL. There are
four environments but only three transaction profiles; `CoreGuarded-Hosting` is
not a fourth profile or a module setting.

A/B/C have separate webroots, runtime identities, application accounts, schemas,
Documents, sessions and logs. They link the **same local module worktree**. B/C
share a MySQL server/container but have **different schemas and accounts**; they
no longer switch users/profiles in one shared application configuration. B has no
application SELECT grant on the transaction-event Performance Schema table; C has
the targeted grant/instrumentation required by EnhancedMySQL. A keeps its bounded
MariaDB snapshot contract. These observation strengths are not equivalent.

## Hosted D checkout and PR #45 validation (2026-11-24)

D is a fresh, independent shared-hosting installation with a dedicated
database schema and an active module from a direct Git checkout. The CLI
reader confirmed the configured database identity, MySQL 8.4.13 and the
saved `CoreGuarded` profile. A Chromium preflight authenticated all four
test roles. Two authorized roles saw the bulk action and two restricted
roles did not.

The PR #44 baseline refused the first contribution-only confirmation before
transaction entry. On D, Web PHP could resolve and write to the provider's
standard file-session directory, but the directory was not readable for
listing. [PR #45](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/45)
removes that directory listing requirement. The durable session checkpoint,
replay refusal and STOP rules remain in force.

| Hosted D evidence | Result on module commit `0adc259d4739b44f9b1fb3a49574d7e1c318132c` |
| --- | --- |
| Owner browser booking | **PASS.** One contribution without invoice or mail. Independent hosting-local SQL readback confirmed exactly one contribution in the selected period, the expected amount and type, and the stored membership end. |
| Focused browser and replay follow-up | **PASS.** A fresh period for the synthetic member started with zero matching or overlapping contributions. One confirmation recorded an acknowledged commit and the saved result remained visible after reloading. The browser resubmitted the identical consumed request once and the module rejected it. Independent SQL readbacks after booking and after replay found the same one matching contribution and identical member-state SHA-256. |

**Merged source provenance:** [PR #45](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/45) squash-merged as `b026f1504db27308fc7326e2e88ec2e53b2f48d9`, parent `e57ee41248306797678f0c5ccd5251fd13474465`, identical tree `29873481b9d24aaf56836d46c934f710af24cecc` to independent review HEAD `7e5f64d07ab3830ce355cd745782c4f0c2a955b0`. Verdict: APPROVED FOR DEVELOPMENT MERGE WITH DOCUMENTED LIMITATIONS; exact-head [Standard 9/9](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/45501058019), [Full 21/21](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/45501057921), [Hosted 1/1](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/45501057914), 31/31 SUCCESS. D real implementation `0adc259d4739b44f9b1fb3a49574d7e1c318132c`; the next commit changed only four Markdown files. No real test is relabeled as a run on the squash commit.

The tested server used its standard session configuration. These results
cover contribution booking, result persistence and replay refusal for the
recorded cases. At this 24 November PR #45 checkpoint, broader Hosted D
integration, finance, PDF and mail were still outstanding. The separately
source-bound 25 November development cases above subsequently passed within
the approved matrix; their evidence does not change the original PR #45 scope.
**R0-04 remains OPEN, with execution blocked pending the final ZIP**, not
initial D provisioning. Live cross-entity remains open; ADR-08A delta is
separately documented above as PASS with limitations. Historical D results below describe the earlier hosted reference.

---

> **Archived date-bound content below:** previous sources/labels/admission claims reflect their original times; the Phase 5 section above and its linked register govern current status.

## Historical labels are not rewritten

| Earlier CF03 label / profile | Current dolienv label |
| --- | --- |
| Earlier A, local MySQL CoreGuarded | **B**, now separately provisioned |
| Earlier B, local MariaDBSnapshot | **A** |
| Earlier C, local EnhancedMySQL | **C**, now separately provisioned |
| Earlier D, hosted CoreGuarded | **D** |

This is a mapping of **profile meaning**, not evidence or fixture migration.
Earlier A/C shared-schema, serial-use instructions, private environment bindings,
case IDs, source pins, hashes and original PASS/INVALID/STOP outcomes remain true
for their dated installations. They must not be renamed or replayed as new dolienv
results. The archived CF03 tools retain their original bindings. **Never merely
swap letters in an old command or retarget an old one-shot fixture.**

For current operations use environment-bound protected launchers and a fresh
read-only identity/profile/source preflight. Independent B/C schemas remove the
old shared-schema reason for serial use, but do not qualify concurrent execution:
the server and local module source are still shared. Freeze the source for each
campaign; protect concurrent activity and never infer a parallel-test PASS.

## Observed bounded evidence, 2026-11-23 through 2026-11-24

The A/B/C acceptance and D reader used module implementation
`46f78d8f8f2c6f70424754d82171697e74ac4448`. Local Core was pinned to Dolibarr 24.0.3
`5c6c7ff6a5112413b1c27221c8d11750afad56f8`; D reported Core source 22.0.5 and the
same module HEAD, **without a dirty-worktree attestation**. A later documentation
commit is not a new deployment, independent review or real-test source.

| Check | A: MariaDBSnapshot | B: CoreGuarded | C: EnhancedMySQL | D: hosted CoreGuarded |
| --- | --- | --- | --- | --- |
| Real authentication / role-dependent bulk UI | PASS, bounded | PASS, bounded | PASS, bounded | PASS, four roles |
| Regular contribution and persisted end | PASS | PASS | PASS | NOT EXECUTED in this setup campaign |
| Deliberate outer Core rollback after actual contribution / standard Agenda DML | PASS | PASS | PASS | NOT EXECUTED |
| Invoice for existing synthetic customer, validation, links and actual PDF | PASS | **UNKNOWN/STOP; finance acceptance BLOCKED** | PASS, separately completed after B stopped | NOT EXECUTED |
| R16 retains later end after shorter invoiced period | PASS | NOT QUALIFIED for failed invoice attempt | PASS | NOT EXECUTED |
| Executed identical-request replay checks | PASS, invoice | PASS, contribution | PASS, contribution and invoice | NOT EXECUTED |
| Local-only SMTP capture infrastructure probe | PASS | PASS | PASS | NOT EXECUTED; local capture is not hosted delivery |
| Module email / verified attachment transport | NOT EXECUTED | NOT EXECUTED | NOT EXECUTED | NOT EXECUTED |

A/B/C independently checked 24 selected business-table snapshots and relevant
files. Six invalid-CSRF requests were rejected without business changes; four
identical consumed-POST replays were rejected without additional business-table
changes. Three controlled Core rollback cases left their inspected tables/files
unchanged, with independent SELECT confirmation. These are **Core API rollback
checks, not a complete module-exception, SQL-fault or Lost-ACK campaign**.

There were five acknowledged successful persisted contributions and two validated
invoices/physical PDFs across A/B/C. The first A contribution's original replay
was **NOT EXECUTED** after a harness STOP on blocked background pings; it was not
rebooked. A's separate invoice replay passed. The original harness STOP and its
reconciliation are retained privately, not silently turned into replay evidence.

The later C invoice/CSRF/replay/R16/PDF workflow passed independently of B. Fresh
SELECT and physical-file comparison showed A/B unchanged during that follow-up.
Each local SMTP probe was accepted by the loopback capture service with matching
capture metadata and no external delivery. **This is not a module-email, inbox,
attachment or SMTP-provider qualification.**

### Open local B finance observation

`DOLIENV-ACC-01` blocks complete B finance acceptance on the tested implementation:
`UNKNOWN/STOP`, `MSMTransactionUnknown`, stage `finance_precheck`, rollback
**unconfirmed**. The independent readback found no persisted business delta from
that attempt; **absence of a persisted delta does not supply a missing rollback
ACK or rewrite UNKNOWN as FAILED/safe**. No retry, compensation, profile change
or product fix was performed during the acceptance campaign. Its relationship to
earlier reports is not established.

**2026-11-24 source analysis and offline correction (F38-14 candidate).**
The controller appends the boolean `manualend` to the confirmed row after the
preview fingerprint is computed. The fresh preview under the member lock has
matching business data and fingerprint but lacks this presentation field.
`MsmInvoiceService::create()` rechecks profile admission and the active guard,
then hands the fresh row to `requireFinanceContinuation()`. The exact row
comparison denies the otherwise valid continuation with
`RuntimeException(0), MSMCoreGuardedSessionUnsupported`. The synthetic
file-session/Booking/Invoice test observed that exception before any Invoice
Core call. The historical browser result stores the generic UNKNOWN code,
so the original exception was inferred from the source and reproduction.

Booking now carries that single field from the verified confirmation when
handing the fresh row to CoreGuarded Invoice. Finance parameters still come
from the fresh row, and the existing exact row, same-guard and consumed
capability checks still run. The exception does not qualify for a controlled
rollback, which explains the reproduced `UNKNOWN/STOP` classification.
The original live result stays `UNKNOWN/STOP`. A fresh independent SELECT still
found no row for diagnostic Subscription #3 and no invoice. Real testing of
the correction, independent delta review and renewed Owner approval remain
open. Development merge is blocked.

This does not cancel the separately passed A/C cases, and does not establish a D
finance failure or success. A–D complete finance/mail/attachment qualification,
CF05-07, later exact-package hosting acceptance and Owner release gates remain
open. This documentation update performs no new business tests.

## D hosting-local reader and protection

The Owner deployed a separate **CLI-only PHP reader outside the module repository**.
It tokenizes configuration without executing it, accepts fixed modes/queries only,
and emits sanitized metadata. No additional DB grants, public DB connection,
Core bootstrap, session start, BEGIN/SET/business DML, PDF or mail was used.

The real hosting-shell run confirmed PHP/mysqlnd 8.3.37, MySQL 8.4.13, configured
account/schema agreement, Core source version/module HEAD, eleven selected InnoDB
tables, CoreGuarded, autocommit 1 and session-default REPEATABLE-READ. Module/Agenda
and file-logger declarations were observed; actual Web logger/session/`$mysoc`
contracts, exact grant scope and business postconditions remain unqualified.

Reader-specific caveats:

- Its native connection reported `latin1`; it did not initialize a Core charset.
  This is not evidence of Dolibarr Web connection charset or an automatic reason
  to alter schema/session/global configuration.
- Its CLI file-session path was not writable. The reader needs no session;
  this does not establish an unusable Web handler or checkpoint. The report's
  uninitialized `$mysoc` and `ready_for_business_writes=false` are intentional.
- `USAGE` at global scope is not global administration. An `ALL PRIVILEGES` entry
  was at an unclassified redacted scope; exact schema-pattern/role coverage needs
  a bounded follow-up. No extra Performance Schema privileges are required.
- D has existing business and financial data. Do not treat it as empty or reuse
  existing members/history; later tests need expressly allocated synthetic objects.

The module's initially exposed Git metadata was blocked by an Owner-local hosting
rule. An external recheck of six Git paths with HEAD and GET returned **403 in all
12 cases**. PHP and static reader-directory probes also returned **403 in all six
checks**. No Git configuration content or response bodies were exported. Protection
is evidenced for those paths, not a universal hosting/security certification.

D Documents must remain under the permitted Web root according to the hosting
restriction: require direct HTTP denial and authenticated Core downloads. Virtual
paths do not turn PHP `==` into assignment `=`; the reported configuration typo
requires an independently approved valid-path correction, not a silent agent edit.
The current private profile label is canonical `CoreGuarded`.

## Operations, backups and privacy

Current local protected readers/runners, browser smoke helpers and backup **all**
cover A/B/C only. D's working reader is hosting-local and Owner-operated; no direct
agent SSH path, generalized remote SQL endpoint or writable integration runner has
been installed. Qualify Web/CLI differences and controlled transport separately.

Timestamped local A/B/C logical database and Documents backups were created and
independently checked for SQL compression/content, archive member hashes and
private permissions. They use native server-family dump tools and source stability
checks, not a cross-resource atomic snapshot under concurrent writers. No restore
was executed; these local backups do not include D or supply hosted package/restore
acceptance. The MySQL container currently requires explicit start after reboot;
no boot-autostart claim is made.

Public documentation contains only generic environment labels, versions, profiles,
aggregate synthetic-case outcomes and limitations. **Never publish** public hosting
origins, routes to private tools, IPs/DB endpoints, schema/account/login identities,
real filesystem paths, credential/session stores, nonce/token values, raw logs,
dumps, Documents/PDF/mail contents or private operational inventory/counts. Use
operator-maintained private bindings; do not commit a private-term blacklist.
Private evidence and original integrity manifests remain operator-held. The public
summary is **not independently reproduced or a hash attestation of redacted copies**.

## Source, review and release boundary

[PR #38](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/38)
was open and Ready for review when checked on 2026-11-24. Its prior independent
F38 review and Owner development-merge approval concern the implementation HEAD
`46f78d8f8f2c6f70424754d82171697e74ac4448`; do not silently apply them or old CI to a
new documentation HEAD. Findings and architecture decisions keep their recorded
provenance. See [current baseline](current-development-baseline.md),
[coverage legend](test-coverage.md) and [privacy policy](privacy-sanitization.md).
No merge, release, issue closure, financial feature/policy change or universal
support statement is part of this update.
