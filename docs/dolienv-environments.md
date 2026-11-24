# dolienv environments and bounded acceptance

## Post-PR #38 source and acceptance handoff

**Authoritative post-PR #38 state (2026-11-24):** [PR #38](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/38) was independently reviewed and **squash-merged** into `main` as `0998fbaf58155cca04d31ab99c6bb47420e53c72`; reviewed HEAD `b15b93580f995fb05a696a125c2a5fd995dd4bb3` has **identical Git tree** `a6e7256fb1572361c4dd4d850124ca7a6afff566`. The independent verdict was **APPROVED FOR DEVELOPMENT MERGE WITH DOCUMENTED LIMITATIONS**, with exact-source Standard 9/9 + Full 21/21 + Hosted 1/1 (**31/31**) synthetic CI PASS. Phase 4 development is merged; **Phase 5 real A–D integration, CF05-07, package, hosted and Owner release acceptance are NOT complete**. All older dated DRAFT/finance-default-deny/core-source-allowlist statements below are historical and not current requirements.

**Independent disposition:** F38-01–06 `CLOSED`; F38-07–09 `SUPERSEDED BY OWNER VARIANT B` (not technically closed); F38-10–13 `INDEPENDENTLY CLOSED`; F38-14/P2 `INDEPENDENTLY CLOSED` (server-confirmed `manualend` restored only after fresh fingerprint revalidation); F36-01 historically closed. **Separate real incident:** `DOLIENV-ACC-01` in B remains `UNKNOWN/STOP`, diagnostic subscription #3 and rollback unconfirmed; lack of a persisted record does not prove rollback ACK. No replay/compensation. The corrected code has since passed the fresh B finance cases in the [local B mail and attachment qualification](evidence/cf03/phase5-local-b-mail-attachment-2026-11-24.md).

**Source identities:** A/B/C previous real acceptance used module `46f78d8f8f2c6f70424754d82171697e74ac4448`; last independent reviewed fix `b15b93580f995fb05a696a125c2a5fd995dd4bb3` was merged tree-identically as `0998fbaf58155cca04d31ab99c6bb47420e53c72`. The clean deployed baseline `ba5c9a3121754fcbd4259bb8906ad1dd17f1ce00` has bounded real B finance, mail and attachment evidence. Current B rollback remains unconfirmed despite independent SQL absence; this document retains immutable old-run identity.

**Operational/release boundary:** `F32-MR-01` protected private target-user launcher and read-only preflight, independently reconciled **new** one-shot A–D CF03-NORMAL cases (priority B finance, never replay old UNKNOWN), per-case Owner write GO, controlled snapshots/backups and restore readiness, broader module mail/attachment coverage beyond the tested local B cases, `CF05-07` risk acceptance, `R0-03` exact v0.1.0 ZIP/SHA256, `R0-04` hosted D package/Web-FPM/business qualification, `R0-07` independent final acceptance and separate Owner Release GO remain **OPEN / NO-GO**. Envelope-only SMTP probes cover capture connectivity. The later browser runs exercised actual module transport and attachments. Backup integrity is not restore, and CF03-FAULT destructive cases remain DEFERRED/NOT EXECUTED. No real writes authorized by this documentation.

**Local B mail/attachment status (2026-11-24): PASS for three members in two real bulk confirmations (one member and two members).** The [local B mail and attachment qualification](evidence/cf03/phase5-local-b-mail-attachment-2026-11-24.md) records three local captures and three decoded MIME attachments matching the physical invoice PDFs. Wider profile, hosted and external delivery coverage remain outside this result. The historical B02 and `DOLIENV-ACC-01` outcomes remain `UNKNOWN/STOP`.


---

> **Archived date-bound content below:** previous sources/labels/admission claims reflect their original times; the post-PR38 section above governs current status.

Current Owner-requested environment convention, **2026-11-24**. This document is
public and pseudonymized. It describes deployment/test topology and observed
results; it introduces no transaction policy, product change or release approval.

## Current matrix

| Environment | Location / Core | Database server | Transaction profile | PHP evidence |
| --- | --- | --- | --- | --- |
| **A** | Local, Dolibarr 24.0.3 | MariaDB 10.11.19 (`10.11.19-MariaDB-0+deb12u1`) | **`MariaDBSnapshot`** | PHP-FPM / CLI 8.2.35 |
| **B** | Local, Dolibarr 24.0.3 | Oracle MySQL Community Server 8.4.13 | **`CoreGuarded`** | PHP-FPM / CLI 8.2.35 |
| **C** | Local, Dolibarr 24.0.3 | Oracle MySQL Community Server 8.4.13 | **`EnhancedMySQL`** | PHP-FPM / CLI 8.2.35 |
| **D** | Restricted shared hosting, Dolibarr 22.0.5 | Oracle MySQL Community Server 8.4.13 | **`CoreGuarded`** | Hosting CLI PHP / mysqlnd 8.3.37; actual Web/FPM contract not yet qualified |

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

D is an independent hosted installation with its own module checkout and data.
Changing the local worktree does not deploy D. Provider `open_basedir` restrictions
apply to the Web environment; D CLI reported no configured `open_basedir` or main
ini file. Those CLI observations do not disprove the Web restrictions.

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
