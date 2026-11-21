# CF-03 — Qualification plan and binding A-D environment matrix

Status updated 2026-11-21: **POS02-A1 and POS02-A2 in environment A TECHNICAL PASS / COMPLETED; F32-W01-01 code finding independently CLOSED**. Later real Owner test results supersede the historical Stage-0/1 `no live test` wording below; other CF03 gates remain OPEN. Source baseline: main `786f271240d230b90fad77fe1c3397d1ec796f81` (2026-11-20). The active PR HEAD must be pinned separately at the time of each test. Binding scope: [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22), [ADR-0001](adr/ADR-0001-core-first-transaction-profiles.md), D-01/D-02 and E01–E15.

## Aktuelle Realtest- und Review-Disposition (21.11.2026)

- **Code F32-W01-01 – CLOSED** durch den [unabhängigen finalen Delta-Review](evidence/cf03/PR32-F32-W01-01-POS02-final-delta-review.md), auf Code-/Test-HEAD `e7b911b707508e439f9acbfad43f5fd55d34cc44`: `llx_adherent_type.statut=1` ist ACTIVE, `0` ist INACTIVE. Standard-CI 9/9 plus Hosted-CI 1/1 PASS auf diesem SHA.
- **POS02-A1 – TECHNICAL PASS** (A, CoreGuarded, Mitglied #59, Subscription #68, EUR 120, Member-Agenda ON, +1 Agenda-Event/-Resource, keine Rechnung).
- **POS02-A2 – TECHNICAL PASS** (A, CoreGuarded, Mitglied #60, Subscription #69, EUR 120, Member-Agenda OFF, +0 Agenda-Events/-Resources, keine Rechnung).
- **POS02-A Positivtestreihe – COMPLETED.** [Technischer Abschlussbericht](evidence/cf03/CF03-POS02-A1-A2-technischer-Abschlussbericht-2026-11-21.md) mit bytebasiert geprüften hochgeladenen Evidence-Kopien, Baseline-SHA256, One-shot-Marker, Kontextkonsistenz und exakt zwölf Tabellenzähler-Deltas. Die Owner-Dispositionsentscheidung priorisiert diesen späteren technischen PASS.
- **Review-Grenze:** Der unverändert archivierte externe Reviewbericht hatte die POS02-Fälle mangels damals zugänglicher Roh-Evidence mit `INSUFFICIENT EVIDENCE` bewertet. Er bestätigte **F32-W01-01 CLOSED**. Die spätere technische Evidence-Prüfung und Owner-Statusfestlegung begründen POS02-PASS; sie dürfen nicht als spätere externe Reviewer-Anerkennung ausgegeben werden. Ein eigenständiger zeitlich vorausgehender Owner-GO-Nachweis wurde nicht vorgelegt.
- **Sonstige Gates:** RB01, ERR01, BATCH01, CONC01, REPLAY01, MDB01, ENH01, HOST01, POST01, vollständige 21-Profil-CI und finaler RV01 bleiben offen. A/C teilen dasselbe Schema und müssen weiterhin seriell betrieben werden. Keine Wiederholung einer realen Buchung oder Verify und keine Roh-Evidence im öffentlichen Repo.

## Owner A–D preflight disposition (2026-11-20)

**Stage 0 environmental readiness: A/B/C/D all PASS on read-only `--preflight`.** The Owner provided a complete console record from corrected PR #32 source, including 171 pure unit assertions PASS on the local and hosted checkouts. A/C use the same MySQL 8.4.13 V24 schema under different manually selected restricted/Enhanced DB users and matching entity-1 profiles; P_S SELECT was denied 1142 for A and allowed for C. B passed MariaDBSnapshot/MariaDB 10.11.19. D passed CoreGuarded/MySQL 8.4.13/Dolibarr 22.0.5 on PHP 8.3.37 and denied P_S SELECT 1142. All eleven recorded module/tool source hashes and nine selected deployed production hashes were consistent across A–D; A/B/C V24 Core fingerprints matched, and D used distinct expected V22 Core file hashes with `filefunc.inc.php`.

The initial D `CF03WrongDolibarrRoot` remains a historical preflight tool STOP, now resolved by the V22 filefunc version detection. **D's `deployment_kind=source_checkout`: no ZIP-origin/installation PASS is claimed.** Real HTTP, positive/negative booking, faults/rollback, invoice, active Enhanced observer, concurrency/replay and hosted private evidence-root qualification remain **NOT EXECUTED**. Full sanitized details in [Owner A–D preflight evidence](evidence/cf03/stage01-owner-preflight-2026-11-20.md). This later evidence supersedes dated `NOT EXECUTED` preflight markers below for current Stage 0 status; all six Issue #22 CF-03 acceptance checkboxes remain OPEN.

## CF03-W01 Operator Readiness Hardening (2026-11-21) – CURRENT STATUS

The original independent W01 tooling review closed all eleven F32-W01-01..11 findings on source HEAD `3511175fc33489e7e8e89e4ae13f36db8e87aa62`. A later independent overall review identified **F32-GR-01** (invalid missing-actor runbook example, P2), **F32-GR-02** (PHP CLI runtime not bound to the case, P3), stale operator documentation and **F32-W01-12** (Reader mock emits PHP warnings, P3). These targeted items are now implemented as one *Operator Readiness Hardening* delta; **the new HEAD is not automatically independently approved**.

Current witness source attestation is a deterministic inventory of module production PHP sources and CF03 tooling, plus a **bounded version-specific critical Core dependency manifest** (including Adherent/Subscription, ActionComm/Agenda, Facture/Product/Societe where applicable). No fixed "four Core / nine module files" statement describes the current implementation; no whole-Core/ZIP or third-party hook attestation is claimed. D permits a verified deployed production-PHP copy with matching selected inventory and hashes, not a proven ZIP install.

Real A/B/C lab environments now bind **PHP CLI 8.2.x** and hosted D binds **PHP CLI 8.3.x**, with full `PHP_VERSION` and selected major/minor persisted in case snapshots and checked between baseline/verify; web-worker PHP is separately Owner-attested. Published A Contribution Agenda ON, B/C Invoice-existing and D hosted Contribution argument examples are parser-tested from the actual runbook. The Invoice Agenda reader supports independent BILL_CREATE / BILL_VALIDATE flags and strict Actor attribution; `LINEBILL_INSERT` remains an unsupported early STOP. The Reader synthetic fault no longer corrupts Member-Agenda event fields while testing Invoice projection.

**No current-HEAD live A–D DB/HTTP business results, active C instrumentation, D private HTTP-exposure proof, full ZIP proof, fault/concurrency/replay proof, Issue #22 closure, merge, release or production GO are implied.** The earlier 4/4 read-only preflights belong to a different source SHA. New independent delta review, fresh A–D read-only preflights, case-specific Owner approvals and immutable evidence remain required. Sections headed "Stage 0/1" below retain historically dated source and progress context; where they conflict, this current status and the operator runbook govern.

## CF03-W01 tool hardening (2026-11-20)

The read-only A–D preflights completed **4/4 Owner PASS** on earlier source. Following that qualification, CF03-W01 adds [private hosted evidence and invoice checks](cf03-w01-validation.md): a strictly explicit, private D evidence root (A/B/C unchanged), one-shot checksum/ownership/mode/file-list integrity, sorted V22/V24 SQL column fingerprints, a stricter single existing-customer invoice header/line/Agenda count witness, pure negative unit tests, and a targeted PHP 8.3/V22.0.5 CI lane without broadening standard PR CI. **No real W01 readback/booking/invoice test has been conducted**; earlier preflight evidence does not automatically qualify changed W01 code and must be rechecked before real scenario admission.

The invoice witness deliberately rejects unsupported `LINEBILL_INSERT` event attribution; separate live invoice/customer/PDF/SMTP/observer tests remain necessary. All six CF-03 Issue #22 checkboxes remain OPEN, the PR remains DRAFT, and Owner merge/release GO remains separate.

## CF03-W01 Paket B remediation (2026-11-20)

Owner-approved bounded runtime-source and business-contract correction in Draft PR #32: F32-W01-02 (deterministic PHP inventory of module production sources + scenario-relevant pinned Dolibarr V22/V24 Core subset; matching deployed module source copy, no ZIP attestation), F32-W01-06 (configured invoice product `fk_product_type` 0/1), F32-W01-07 (only customers `client=1/3`), F32-W01-08 (read-only validation of active entity-1 authenticated operator and Core Agenda event author/owner/resource), and F32-W01-09 (exactly one invoice unit). All five now have targeted positive/negative synthetic regressions. Together with Paket A, this is **implementation evidence only**: independent delta review of **all ten** F32-W01 findings and Owner A–D re-preflight are still pending. Previous Owner A–D preflights belong to pre-W01 source. Realtest families all remain **NO-GO**, PR #32 stays DRAFT, and Issue #22's six CF-03 qualification checkboxes remain OPEN. See [W01 validation](cf03-w01-validation.md).

## 1. Binding environment assignments

| Environment | Profile | Installed Dolibarr / PHP | MySQL / MariaDB | Web / document root | Schema/account | Evidence level |
| --- | --- | --- | --- | --- | --- | --- |
| **A – MySQL restricted / V24** | `CoreGuarded` | V24.0.3; PHP 8.2 in historical POS01-C, verify afresh | MySQL 8.4.13 | `http://i105-v24.localhost:8091/`, `/home/marcel/dolibarr-i105-v24-mysql/htdocs` | `127.0.0.1:3307`, `dolibarr_i105_v24`, `dolibarr_i105_v24_limited` | Historical POS01-C PASS on PR29 source; **new CF-03 NOT EXECUTED** |
| **B – MariaDB / V24** | `MariaDBSnapshot` | V24.0.3; PHP runtime to verify | MariaDB 10.11.19 | `http://doli24.test:8090/`, `/home/marcel/dolibarr-v24/htdocs` | `localhost:3306`, `dolibarr_db_v24`; user to be supplied as CLI expected identity | Historical MDB-TEST-01 on older source, **new CF-03 NOT EXECUTED** |
| **C – MySQL enhanced / V24** | `EnhancedMySQL` | **same Dolibarr install and webroot as A** | **same MySQL 8.4.13 server and same schema as A** | **same web URL, document and data root as A** | **same host/port/schema as A, manually changed application DB user**; enhanced user name to be supplied as CLI expected identity | Available by Owner; technical profile/PS/HTTP preflight **NOT EXECUTED** |
| **D – MySQL hosted / V22** | `CoreGuarded` targeted, must confirm live configuration | V22.0.5 targeted; PHP runtime to verify | MySQL 8.4.13 targeted | Owner confirms environment available; URL, root, data path to supply during preflight | Host, port, schema and restricted user to supply explicitly; no guesses | Available by Owner; hosted ZIP/install/real DB **NOT EXECUTED** |

**A/C single-installation decision:** A and C are two **sequential test configurations** of one actual V24.0.3 site and **one underlying schema**. They are **not two isolated parallel laboratories**. Only the Owner may change `htdocs/conf/conf.php` credentials, via manual maintenance; the Owner also selects the matching module profile in Dolibarr's administration. No harness changes either. The profile is stored per entity in `llx_const`, not selected by the database account. Switching the DB login without selecting the correct profile is **STOP**.

A/C test windows must be mutually exclusive: finish and preserve one case and its independent verification before changing accounts, profile, PHP process state, data or module source. Stop competing HTTP requests during snapshots; do not recycle confirmations or consumed member fixtures after switching. A/C reuse the same global table counts, so separate baselines and new synthetic members are mandatory. On switching accounts: quiesce Apache/PHP users, change credentials manually, ensure new requests actually use the intended config, select the matching profile through the supported UI, then execute fresh preflight. Any error or uncertainty stops the window; never mutate `conf.php` automatically in a test tool.

**Performance Schema:** A must reproduce denied SELECT 1142 for the selected instrumentation table as the restricted effective account; C must be allowed to SELECT it. C additionally requires correctly enabled transaction-event instrumentation, actual own-event continuity and working EnhancedMySQL observer, **not merely an allowed SELECT**. Those active-runtime checks belong to the later real C qualification, not to the preflight success alone. P_S may be globally enabled for both accounts; A must retain its restricted grants and no hidden P_S dependency.

**Environment D V22 version-file regression (2026-11-20):** Owner's first D `--preflight` returned `CF03WrongDolibarrRoot` because the Stage-1 tool incorrectly demanded `htdocs/version.inc.php`, a V24 file absent from pinned Dolibarr V22.0.5. Exact pinned V22.0.5 source defines `DOL_VERSION = '22.0.5'` in `htdocs/filefunc.inc.php`. A strictly pinned, read-only version-file selector and pure regression tests now use `filefunc.inc.php` for V22.0.5 and retain `version.inc.php` for V24.0.3. The original STOP is **tool deficiency, not Dolibarr V22 incompatibility**; a new D PASS is not claimed until an Owner rerun on fixed source. Hosting shell may resolve `htdocs` via a different jail-visible path than the absolute web-server path. No `sudo` is required for a trusted CLI user with file and database access. Hosting private evidence directory portability remains an open Stage-1 follow-up before any D baseline.

**Environment D:** a hosted system is not inherently safe for destructive faults. Use only approved synthetic members, ordinary positive/negative UI flows and test ZIP; no deliberate deadlock/connection kill/global configuration manipulations unless independently approved isolation and recovery exist. The V22 target/source patch must be verified; no assumed document root/port/user.

## Evidence-root permission policy (2026-11-21)

Current W01 test-only witness accepts an existing canonical non-symlink **root** when the effective PHP CLI user has read and write access. Root mode 0700 and owner equality are no longer required; **individual case directories and evidence files still require 0700 / 0600** and one-shot integrity. This deliberately weakens the parent-directory trust guarantee where other users can write to the root; independent Owner isolation/permissions assessment and a bounded security delta review are required before real RB01 injection. A/B/C root path remains fixed; D additionally excludes webroot/checkout overlap and must be protected against public HTTP exposure.

## 2. Safety and evidence contract for Stage 0–1

`tools/cf03-lab.php` implements a bounded **read-only operator witness**:

- `--preflight`: local trusted config and module path, Dolibarr major/minor, effective schema/account, expected server family/version, `autocommit=1`, `completion_type=NO_CHAIN`, profile value in entity 1, required InnoDB tables and P_S permission check; hashes the local config file and critical source files but never prints credentials.
- `--check-only`: preflight plus one never-booked synthetic-member, member type/Agenda/engine/count validation. It writes no files and performs no business SQL.
- `--baseline`: same read-only checks and a one-shot `baseline.json` with `baseline.sha256`, **outside webroot** under `/var/lib/dolibarr-test-results/cf03-[a-d]-...`, mode 0700. Zero booking calls.
- `--verify`: independently queries the DB after the **separate Owner-operated authentic UI operation**, checks baseline integrity, writes a one-time `verify.started` marker, protected `after.json` and `verdict.json`. No retry or automatic cleanup. The tool never performs the web booking.
- All SELECTs are constructed from constant SQL plus validated numeric member ID; no write SQL, `FOR UPDATE`, DDL, migration, account switch, mail, HTTP, PDF, request replay or DB transaction is issued.

**Preflight output is not proof of actual Apache/PHP account selection**; Operator must independently confirm the active site points to the same conf.php and PHP processes were recycled after credential change. It also does not prove P_S instrumentation/event continuity, system-wide configuration isolation, SQL-transport error behavior, or future fault handling.

**Bounded scenario modes (Stage 1 scaffolding, not real-test authorization):**

| Scenario | Eligible environments | Witness coverage and limits |
| --- | --- | --- |
| `contribution` | A/B/C/D with correct profile and isolated fixture | Exactly one Core contribution, marker, start/end, R16 membership end, selected InnoDB/Agenda ON/OFF, no unexpected monitored table count changes |
| `no-change` | A/B/C/D | Exactly unchanged member/subscription/standard Agenda/linked invoice and monitored table counts after a separately authorized negative/rollback action; does **not** establish physical rollback, non-transactional effects, or arbitrary SQL state |
| `invoice-existing` | B/C only | Exactly one linked validated invoice and line, correct existing customer, marker, amount and selected monitored counts; extra invoice-related Agenda actions are **not individually reconstructed** yet. The scoped PASS, if achieved, must not be used as full invoice/Agenda/PDF/SMTP qualification; additional dedicated independent invoice witness/verification required for CF-03 completion |

For `contribution` or `no-change`, `datefin` must be NULL in the fresh baseline; for invoice the member must have an existing linked customer. For every scenario, `Synthetic` given name, a unique `CF03LAB-[A-D]-...` surname marker and marker-derived `@example.invalid` address, a fresh Core-generated reference, entity 1, validated status, active type and expected amount, no prior contribution or linked invoices are required. The selected date range in this initial witness is between 2050 and 2099. Historical members **56/57/58 and subscriptions 65/66/67 must never be reused**.

For A/B/C the installed module must resolve to the current checkout. For D a verified deployed ZIP module **copy** is also admitted: nine selected deployed product PHP file hashes must equal the current CF-03 checkout; the complete package manifest and installation process still require separate HOST01/R0-04 evidence. Each preflight also fingerprints the installed Dolibarr Core `version.inc.php`, `Adherent`, `User` and `DoliDBMysqli` source files; the baseline-to-verify window rejects drift in those Core hashes, not just module files. A matching version string alone is not taken as a source identity proof.

A/C normal one-shot tests require NO_CHAIN. CHAIN/RELEASE, isolation setting variants, 1213/1205, actual concurrent HTTP/SQL, unexpected commits, connection replacement, lost ACK and terminal/partial-batch behavior need **separate test harnesses** with special case admission and stricter controlled injection isolation. Do not change this NO_CHAIN witness to accept those states as a shortcut.

Neither `verdict.json PASS` nor browser text proves the physical identity of a CoreGuarded transaction; observer/event-level evidence remains profile-dependent. A `STOP` never permits repeat booking or rewrite of prior evidence. The test result's `pdf_smtp` and `acknowledgement` remain `not_determined_by_database_readback`.

**Protect against concurrent writes:** snapshots compare global count deltas; execute in a quiescent approved lab. Side effects in unmonitored tables, pre-existing subscriptions in other entities, hooks and filesystems are not universally covered. A broader selected-table/file diff or Core-level observer trace is required for the relevant later fault/invoice tests.

## 3. Stage 0 inventory and Stage 1 engineering findings

Existing reusable components: `tools/cf02-contribution-witness.php` and pure `*.lib.php` (historical POS01), `tools/cf02-lab-readonly.sh`, `tools/cf02-loaded-core-entry.php`, `tools/cf02-run-synthetic.sh`, `test/unit/coreguarded.php`, `test/unit/f29-rights.php`, `test/unit/f29-book-flow.php`, `test/unit/coreguarded-session.php`, `test/unit/terminal.php`, `test/unit/profile.php`, `test/unit/request.php`, `test/unit/invoice.php`, `test/integration/options-http.py` (read-only legacy V22 HTTP smoke).

Limitations: The POS01 witness is pinned to A, a specific restricted account and fixture semantics; it is not a general CI harness or a 4-profile invoice/fault script. The historical no-JS HTTP tool is not a current authenticated CF-03 massaction runner. No existing current-source lab tool covers destructive faults, full real invoice/Agenda/PDF/SMTP reconciliation or authenticated replay; these require distinct later Stage-2/3/4 tools. Earlier POS01-A/B STOP are historic and unaffected, POS01-C PASS is evidence on PR29 SHA, and historical I107/MDB-TEST-01 real integration is on older SHA.

**Stage-1 deliverables:** reusable A-D pure policy `tools/cf03-lab.lib.php`; read-only `tools/cf03-lab.php`; offline `test/unit/cf03-lab.php`; nine-profile plus optional full-CI unit invocation; this matrix, operational runbook, review/coverage documentation. No product booking/transaction implementation change and **no live CF-03 PASS**.

## 4. Planned risk-based CF-03 gates

| Gate | Cases | Environment | Stage-1 evidence |
| --- | --- | --- | --- |
| ENV01 | Read-only runtime/source/account/profile/rights attestation | A–D | Tool prepared; real runs NOT EXECUTED |
| POS02 | New single-member Core contribution, Agenda ON and OFF | A, D / B/C optional | **A1+A2 TECHNICAL PASS / COMPLETED** on pinned test SHA; D and optional B/C outstanding |
| RB01 | Real safe rollback post-Core DML, independent selected-table/file reconciliation | A, B/C | [RB01-A test-harness design](cf03-rb01-a-harness-design.md) and [step-by-step runbook](cf03-rb01-a-operator-runbook.md) prepared; read-only `no-change` witness exists; controlled real injection NOT IMPLEMENTED / NO-GO |
| ERR01 | 1568, 1213, 1205, depth/reconnection, UNKNOWN/STOP | A + synthetics | Existing synthetic contracts; real harness NOT IMPLEMENTED |
| BATCH01 | NO_CHAIN multi-member, CHAIN/RELEASE terminal | A | Requires separate multi-member and terminal witness; NOT IMPLEMENTED |
| CONC01 | Controlled locks/competitor and R16 | A, B/C | Design only; NOT IMPLEMENTED |
| REPLAY01 | Authenticated one-shot confirmed POST replay / CSRF / revision/owner/entity | A, D and B/C selective | Current HTTP tool only historical read-only smoke; NOT IMPLEMENTED |
| MDB01 | Actual current-source MariaDBSnapshot contribution and invoice regression | B | Partial contribution/invoice-existing witness prepared; no real test |
| ENH01 | Actual EnhancedMySQL P_S/event/transaction and invoice regression | C | Read-only P_S SELECT precheck prepared; no actual observer or invoice run |
| HOST01 | V22.0.5 hosted test ZIP install, limited account, UI booking/security | D | Environment parameterized; exact artifact/build/deploy tooling still needed |
| POST01 | Authorized PDF/SMTP after accepted DB booking, never in CG | B/C | Not covered by DB witness; separate file/mail evidence required |
| CI01 | Nine-profile standard PR and later 21-profile full synthetic matrix | CI | CF-03 pure unit linked to both workflows |
| RV01 | Independent review of exact HEAD, evidence and residual risks | All | NOT EXECUTED; Owner merge/release GO separate |

The six Issue #22 CF-03 checkboxes **stay unchecked** until qualified with complete documented boundaries. Stage-1 test-preparation completion is not a gate completion.

## 5. Next safe steps and boundaries

1. On the CF-03 branch, verify nine-profile PR CI (and unit harness under PHP 7.4 and 8.2). No CF-03 real business test at this gate.
2. Owner/operator collects the A-D Preflight JSON independently, never uploads conf.php, credentials or raw member data.
3. Owner sets A and C accounts and matching profiles **manually**, one exclusive test window at a time. Both share unchanged schema; snapshots must be completed before switching.
4. After review of four preflight results, prepare separate authorized targeted UI/fault/invoice/ZIP operator procedures with fresh fixtures and explicit safe action permits. Repeat existing positive case only when it adds evidence.
5. Before final PR merge, rerun affected CI and independently review implementation/evidence; CF-04 final v0.1.0 package/Owner GO remains open.
