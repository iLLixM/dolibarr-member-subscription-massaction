# CF-03 — Qualification plan and binding A-D environment matrix

Status: **Stage 0 inventory and Stage 1 read-only witness scaffolding committed; no live CF-03 operator test executed or authorized by this document**. Source baseline: main \`786f271240d230b90fad77fe1c3397d1ec796f81\` (2026-11-20). The active PR HEAD must be pinned separately at the time of each test. Binding scope: [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22), [ADR-0001](adr/ADR-0001-core-first-transaction-profiles.md), D-01/D-02 and E01–E15.

## 1. Binding environment assignments

| Environment | Profile | Installed Dolibarr / PHP | MySQL / MariaDB | Web / document root | Schema/account | Evidence level |
| --- | --- | --- | --- | --- | --- | --- |
| **A – MySQL restricted / V24** | \`CoreGuarded\` | V24.0.3; PHP 8.2 in historical POS01-C, verify afresh | MySQL 8.4.13 | \`http://i105-v24.localhost:8091/\`, \`/home/marcel/dolibarr-i105-v24-mysql/htdocs\` | \`127.0.0.1:3307\`, \`dolibarr_i105_v24\`, \`dolibarr_i105_v24_limited\` | Historical POS01-C PASS on PR29 source; **new CF-03 NOT EXECUTED** |
| **B – MariaDB / V24** | \`MariaDBSnapshot\` | V24.0.3; PHP runtime to verify | MariaDB 10.11.19 | \`http://doli24.test:8090/\`, \`/home/marcel/dolibarr-v24/htdocs\` | \`localhost:3306\`, \`dolibarr_db_v24\`; user to be supplied as CLI expected identity | Historical MDB-TEST-01 on older source, **new CF-03 NOT EXECUTED** |
| **C – MySQL enhanced / V24** | \`EnhancedMySQL\` | **same Dolibarr install and webroot as A** | **same MySQL 8.4.13 server and same schema as A** | **same web URL, document and data root as A** | **same host/port/schema as A, manually changed application DB user**; enhanced user name to be supplied as CLI expected identity | Available by Owner; technical profile/PS/HTTP preflight **NOT EXECUTED** |
| **D – MySQL hosted / V22** | \`CoreGuarded\` targeted, must confirm live configuration | V22.0.5 targeted; PHP runtime to verify | MySQL 8.4.13 targeted | Owner confirms environment available; URL, root, data path to supply during preflight | Host, port, schema and restricted user to supply explicitly; no guesses | Available by Owner; hosted ZIP/install/real DB **NOT EXECUTED** |

**A/C single-installation decision:** A and C are two **sequential test configurations** of one actual V24.0.3 site and **one underlying schema**. They are **not two isolated parallel laboratories**. Only the Owner may change \`htdocs/conf/conf.php\` credentials, via manual maintenance; the Owner also selects the matching module profile in Dolibarr's administration. No harness changes either. The profile is stored per entity in \`llx_const\`, not selected by the database account. Switching the DB login without selecting the correct profile is **STOP**.

A/C test windows must be mutually exclusive: finish and preserve one case and its independent verification before changing accounts, profile, PHP process state, data or module source. Stop competing HTTP requests during snapshots; do not recycle confirmations or consumed member fixtures after switching. A/C reuse the same global table counts, so separate baselines and new synthetic members are mandatory. On switching accounts: quiesce Apache/PHP users, change credentials manually, ensure new requests actually use the intended config, select the matching profile through the supported UI, then execute fresh preflight. Any error or uncertainty stops the window; never mutate \`conf.php\` automatically in a test tool.

**Performance Schema:** A must reproduce denied SELECT 1142 for the selected instrumentation table as the restricted effective account; C must be allowed to SELECT it. C additionally requires correctly enabled transaction-event instrumentation, actual own-event continuity and working EnhancedMySQL observer, **not merely an allowed SELECT**. Those active-runtime checks belong to the later real C qualification, not to the preflight success alone. P_S may be globally enabled for both accounts; A must retain its restricted grants and no hidden P_S dependency.

**Environment D:** a hosted system is not inherently safe for destructive faults. Use only approved synthetic members, ordinary positive/negative UI flows and test ZIP; no deliberate deadlock/connection kill/global configuration manipulations unless independently approved isolation and recovery exist. The V22 target/source patch must be verified; no assumed document root/port/user.

## 2. Safety and evidence contract for Stage 0–1

\`tools/cf03-lab.php\` implements a bounded **read-only operator witness**:

- \`--preflight\`: local trusted config and module path, Dolibarr major/minor, effective schema/account, expected server family/version, \`autocommit=1\`, \`completion_type=NO_CHAIN\`, profile value in entity 1, required InnoDB tables and P_S permission check; hashes the local config file and critical source files but never prints credentials.
- \`--check-only\`: preflight plus one never-booked synthetic-member, member type/Agenda/engine/count validation. It writes no files and performs no business SQL.
- \`--baseline\`: same read-only checks and a one-shot \`baseline.json\` with \`baseline.sha256\`, **outside webroot** under \`/var/lib/dolibarr-test-results/cf03-[a-d]-...\`, mode 0700. Zero booking calls.
- \`--verify\`: independently queries the DB after the **separate Owner-operated authentic UI operation**, checks baseline integrity, writes a one-time \`verify.started\` marker, protected \`after.json\` and \`verdict.json\`. No retry or automatic cleanup. The tool never performs the web booking.
- All SELECTs are constructed from constant SQL plus validated numeric member ID; no write SQL, \`FOR UPDATE\`, DDL, migration, account switch, mail, HTTP, PDF, request replay or DB transaction is issued.

**Preflight output is not proof of actual Apache/PHP account selection**; Operator must independently confirm the active site points to the same conf.php and PHP processes were recycled after credential change. It also does not prove P_S instrumentation/event continuity, system-wide configuration isolation, SQL-transport error behavior, or future fault handling.

**Bounded scenario modes (Stage 1 scaffolding, not real-test authorization):**

| Scenario | Eligible environments | Witness coverage and limits |
| --- | --- | --- |
| \`contribution\` | A/B/C/D with correct profile and isolated fixture | Exactly one Core contribution, marker, start/end, R16 membership end, selected InnoDB/Agenda ON/OFF, no unexpected monitored table count changes |
| \`no-change\` | A/B/C/D | Exactly unchanged member/subscription/standard Agenda/linked invoice and monitored table counts after a separately authorized negative/rollback action; does **not** establish physical rollback, non-transactional effects, or arbitrary SQL state |
| \`invoice-existing\` | B/C only | Exactly one linked validated invoice and line, correct existing customer, marker, amount and selected monitored counts; extra invoice-related Agenda actions are **not individually reconstructed** yet. The scoped PASS, if achieved, must not be used as full invoice/Agenda/PDF/SMTP qualification; additional dedicated independent invoice witness/verification required for CF-03 completion |

For \`contribution\` or \`no-change\`, \`datefin\` must be NULL in the fresh baseline; for invoice the member must have an existing linked customer. For every scenario, \`Synthetic\` given name, a unique \`CF03LAB-[A-D]-...\` surname marker and marker-derived \`@example.invalid\` address, a fresh Core-generated reference, entity 1, validated status, active type and expected amount, no prior contribution or linked invoices are required. The selected date range in this initial witness is between 2050 and 2099. Historical members **56/57/58 and subscriptions 65/66/67 must never be reused**.

A/C normal one-shot tests require NO_CHAIN. CHAIN/RELEASE, isolation setting variants, 1213/1205, actual concurrent HTTP/SQL, unexpected commits, connection replacement, lost ACK and terminal/partial-batch behavior need **separate test harnesses** with special case admission and stricter controlled injection isolation. Do not change this NO_CHAIN witness to accept those states as a shortcut.

Neither \`verdict.json PASS\` nor browser text proves the physical identity of a CoreGuarded transaction; observer/event-level evidence remains profile-dependent. A \`STOP\` never permits repeat booking or rewrite of prior evidence. The test result's \`pdf_smtp\` and \`acknowledgement\` remain \`not_determined_by_database_readback\`.

**Protect against concurrent writes:** snapshots compare global count deltas; execute in a quiescent approved lab. Side effects in unmonitored tables, pre-existing subscriptions in other entities, hooks and filesystems are not universally covered. A broader selected-table/file diff or Core-level observer trace is required for the relevant later fault/invoice tests.

## 3. Stage 0 inventory and Stage 1 engineering findings

Existing reusable components: \`tools/cf02-contribution-witness.php\` and pure \`*.lib.php\` (historical POS01), \`tools/cf02-lab-readonly.sh\`, \`tools/cf02-loaded-core-entry.php\`, \`tools/cf02-run-synthetic.sh\`, \`test/unit/coreguarded.php\`, \`test/unit/f29-rights.php\`, \`test/unit/f29-book-flow.php\`, \`test/unit/coreguarded-session.php\`, \`test/unit/terminal.php\`, \`test/unit/profile.php\`, \`test/unit/request.php\`, \`test/unit/invoice.php\`, \`test/integration/options-http.py\` (read-only legacy V22 HTTP smoke).

Limitations: The POS01 witness is pinned to A, a specific restricted account and fixture semantics; it is not a general CI harness or a 4-profile invoice/fault script. The historical no-JS HTTP tool is not a current authenticated CF-03 massaction runner. No existing current-source lab tool covers destructive faults, full real invoice/Agenda/PDF/SMTP reconciliation or authenticated replay; these require distinct later Stage-2/3/4 tools. Earlier POS01-A/B STOP are historic and unaffected, POS01-C PASS is evidence on PR29 SHA, and historical I107/MDB-TEST-01 real integration is on older SHA.

**Stage-1 deliverables:** reusable A-D pure policy \`tools/cf03-lab.lib.php\`; read-only \`tools/cf03-lab.php\`; offline \`test/unit/cf03-lab.php\`; nine-profile plus optional full-CI unit invocation; this matrix, operational runbook, review/coverage documentation. No product booking/transaction implementation change and **no live CF-03 PASS**.

## 4. Planned risk-based CF-03 gates

| Gate | Cases | Environment | Stage-1 evidence |
| --- | --- | --- | --- |
| ENV01 | Read-only runtime/source/account/profile/rights attestation | A–D | Tool prepared; real runs NOT EXECUTED |
| POS02 | New single-member Core contribution, Agenda ON and OFF | A, D / B/C optional | Single-member witness prepared; execution NOT EXECUTED |
| RB01 | Real safe rollback post-Core DML, independent selected-table/file reconciliation | A, B/C | \`no-change\` snapshot partial tool ready; controlled injection NOT IMPLEMENTED |
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

