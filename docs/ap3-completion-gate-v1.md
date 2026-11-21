# AP3 — D-01 Completion, checkpoint and logger evidence v1

Status: **EVIDENCE PREPARED — INDEPENDENT COMPLETION GATE REVIEW PENDING**.
No independent acceptance, CF-02 contribution implementation or release approval.

## Provenance and sequence

Owner-authorized task: PR #26 merge, then AP3 experimental proof (2026-11-18).
PR #26 was normally merged as `c79830976a5f1e1bd57d51005ff34c10c424e36f`,
parents `3bc9e4653778cdc870d79e2f5b7e025bc9ca036a` and
`5deb984d2ad0d39e8a83b2965715f3760b49e614`; its tree equals the approved
four-Markdown-file tree. Push/PR CI were each 21/21; post-merge
[44416906582](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/44416906582)
was 21/21. The [permanent acceptance comment](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/26#issuecomment-6994149240)
records the external judgment and Owner authorization, without impersonating a review vote.

AP3 branch `spike/ap3-completion-gate` starts at that merge. Only this report and
`experiments/ap3/` are added. The AP3 review commit is identified in its Draft-PR;
artifact hashes fix the actual harness/evidence contents without a self-referential
commit SHA in this document. Historical D-01/SH-01 artifacts are unchanged.
Original D-01 `CHANGES REQUIRED` and Option A `CONDITIONALLY APPROVED` remain valid.
See the [ADR contract](adr/ADR-0001-core-first-transaction-profiles.md).

## Actual profiles

| Component | Observed profile |
| --- | --- |
| Core | Dolibarr V24.0.3, `5c6c7ff6a5112413b1c27221c8d11750afad56f8` |
| PHP / native driver | PHP 8.2.35; mysqli / mysqlnd 8.2.35; Xdebug 3.2 function monitor |
| MySQL | Community Server 8.4.13, TCP loopback 3307, autocommit=1 |
| Restricted test account/schema | `<PRIVATE_RESTRICTED_LAB>`, only schema SELECT/INSERT/UPDATE/DELETE plus USAGE |
| P_S denial | Direct negative inspection returns1142 for events_transactions_current; not an application prerequisite |
| Artificial table | Existing isolated InnoDB `probe`; only new AP3 rows6000–6999 eligible |
| Test-module source baseline | `c79830976a5f1e1bd57d51005ff34c10c424e36f`; experimental functions only |
| Referenced live I105 module | `0999efb4aafca43d90ce12e38d370662ad0c63a1`, unchanged; not deployed to current main |
| Actual HTTP bootstrap | Protected I105 `main.inc.php`, authenticated synthetic user `msm-i105` (actor2) |
| Actual HTTP reference | I105 V24.0.3, `<PRIVATE_MYSQL_SCHEMA>`;13 business-table hashes/counts equal before/after |
| HTTP SAPI | Temporary PHP `cli-server`, loopback only; **not Apache SAPI qualification** |
| HTTP sessions | Actual I105 `files` handler/path under `<PRIVATE_SESSION_ROOT>` |
| HTTP logger | Actual configured pinned `mod_syslog_file`, SYSLOG_LEVEL4 |
| CLI logger | Same genuine pinned file exporter, explicit DEBUG7 to exercise post-end export |

[Inspection](../experiments/ap3/evidence/inspection.json),
[HTTP profile](../experiments/ap3/evidence/HTTP-R1-profile.json) and
[Core pins](../experiments/ap3/core-pins.json) record the exact sources and rights.
No administrator credentials, additional grants, global settings or existing
application-session settings were used/changed by the adapter. Owner separately
approved the **initial** NO_CHAIN/CHAIN/RELEASE assignment once on each new lab
connection. This test stimulus is outside the adapter; there is no normalization.

## Results and trace interpretation

24 controlled cases passed:13 CLI cases,2 real Lost-ACK cases,9 authenticated HTTP
cases. These are experimental cases, not24 product business integration tests.
The [machine-readable matrix](../experiments/ap3/evidence/TEST-MATRIX.json) includes
case-level evidence and honest aggregate limits:

| Gate group | Result | Observations and limits |
| --- | --- | --- |
| G1 Completion | **PARTIAL** | All6 actual mode/end pairs in CLI and HTTP; stable preflight/pre-end observations and immediate terminal STOP. Unreadable/malformed/contradictory observations injected explicitly. Actual unexpected late session rewrite NOT EXECUTED. |
| G2 Core logging | **PASS, bounded** | Inherited Core methods, genuine dol_syslog and DEBUG file exports; zero terminal SQL attempts. HTTP uses actual level4, so DEBUG export is filtered. No arbitrary third-party logger qualification. |
| G3 Session/HTTP | **PASS, bounded** | Nine genuine authenticated-bootstrap cases, file-handler checkpoints, fresh GET readbacks, consumed replays and request shutdown. Real files-handler failure tested. No Apache/custom-handler equivalence. |
| G4 Batch | **PASS, experimental** | Independent member/continuation states; NO_CHAIN may continue; terminal/unknown/failure stop before next preflight; remaining slots NOT_PROCESSED. |
| G5 Side effects | **PASS, experimental** | No attempted SQL on terminal lab connection, follower DML, retry, compensation, mail or PDF invocation. Next preflight is an explicit surrogate, not product profile code. |
| G6 Faults | **PARTIAL** | Real lost COMMIT/ROLLBACK OK packets, errno2006, foreign1568, own connection close and checkpoint failure. Unsupported handler declarations/classifier mismatch are injected; arbitrary real custom handlers/late physical drift NOT EXECUTED. |

`Ap3Core` inherits `DoliDB::begin()/commit()/rollback()` and the normal mysqli
query path. Only native connection creation is instrumented. `Ap3Native::query()`
records actual SQL/replies and rejects an attempted terminal follow-up **before**
forwarding it; successful traces contain **zero such attempts**. The fence alone
is not evidence that a product orchestrator has been corrected.

A successful `SET TRANSACTION ISOLATION LEVEL REPEATABLE READ` is immediately
followed by Core BEGIN on the same connection. Completion is sampled before this
SET and before end. No SQL follows a terminal acknowledgment on that connection,
including during the genuine `dol_shutdown` close/logging path. Independent audit
SELECTs use a different connection and never authorize continuation or rescue an
unconfirmed outcome. Native close is ordinary request resource release, not an
explicit rollback of the CHAIN-created transaction.

Core `commit()`/`rollback()` call `dol_syslog()` after their native end and before
returning. Function-monitor entries record only function/caller/line, never args.
CLI DEBUG7 proves the standard file export is really executed; HTTP level4 proves
the configured callback/close path with filtered DEBUG messages. The pinned file
exporter is file I/O, not DB logging. A nonqualified logger/session declaration
causes a pretransaction STOP in this scaffold. No unknown custom exporter was run.

### Member result versus continuation

| Observed completion | Member result | Batch |
| --- | --- | --- |
| Acknowledged COMMIT / NO_CHAIN | SUCCESS | CONTINUE after qualified checkpoint |
| Acknowledged ROLLBACK / NO_CHAIN | ROLLBACK_ACKNOWLEDGED | CONTINUE after qualified checkpoint |
| Acknowledged COMMIT / CHAIN or RELEASE | SUCCESS | STOP |
| Acknowledged ROLLBACK / CHAIN or RELEASE | ROLLBACK_ACKNOWLEDGED | STOP |
| Lost end ACK / closed connection | UNKNOWN | STOP |
| Acknowledged COMMIT, failed checkpoint | SUCCESS remains acknowledged; result persistence uncertain | STOP/E15 |
| Unreadable/contradictory classification, end acknowledged | Acknowledged result retained | STOP |

The E15 test makes a **new private directory** inaccessible and selects it only
for that request's checkpoint reopen. This is an injected actual files-handler
failure, not a new session implementation or configuration workaround. Previously
durable consumption stays `processing/consumed=true`; all durable slots remain
`PENDING_RESULT_RECORD`. The HTTP response and sanitized evidence retain SUCCESS
and failed-checkpoint STOP. A new GET/replay cannot resume or repeat this case.
Normal stored results have `checkpoint:null` because the write occurs before its
own return; successful fresh-GET readback, not that placeholder, proves durability.

Lost-ACK tests forward one actual COMMIT/ROLLBACK to MySQL, discard its real OK and
close only that proxy connection. Both produce Core false/0, errno2006 and
UNKNOWN/STOP. Independent values are1/0 respectively, solely diagnostic. The
adapter does not turn these readbacks into success, retry or compensation.

## Preservation, failures and provenance

Final independent [audit](../experiments/ap3/evidence/audit-final.json):81 old
SH-01/D-01 rows unchanged,70 new artificial AP3 rows; grants/source pins unchanged;
P_S denial remains1142. CLI rows6000–6036, HTTP6200–6217/6220–6228 and Lost-ACK
6400–6405 are disjoint. No contribution/customer/invoice/Agenda API, mail or PDF
was invoked. The13 protected I105 business tables are unchanged. Normal Core
login/session/security-audit state is outside that business hash promise.

Two initial harness failures are preserved, not relabeled PASS:

- `cli.json`: synthetic label validation stopped before INSERT. `cli-r1.json`
  follows a corrected label normalization, without repeating successful work.
- `HTTP-OWNER-result.json`: unavailable inherited-FD transport stopped before
  authentication/test POST. Its empty collection also prevented confirmation of
  the original cleanup/reference comparison; these fields remain null. R1 checks
  for an exact stranded old test-server process, uses mode0600 ephemeral shared
  memory, and independently confirms its own server stop/reference equality.
  `audit-before-http-r1.json` confirmed an empty reserved HTTP range.

Original sources/snapshots are retained. `core-before-monitor-fix.php.txt` captures
CLI-r1's executed monitor setup; its harmless repeated-start notice was corrected
before later cases. Real CLI-r1 monitored calls remain in evidence. R1's console
accidentally printed the old HTTP-result filename; the actual PASS file is
`HTTP-OWNER-R1-result.json`. `owner-http-r1.py.executed.txt` preserves that version;
current source only corrects the final printed path. No old result is overwritten.

## Remaining review questions and boundaries

- No guarantee of continuous physical transaction identity or detection of a
  change after the last completion observation. No Enhanced equivalence.
- No actual mid-transaction Completion rewrite: Owner authorized initial lab
  settings only. Contradictory classifier observations are explicitly artificial.
- Real unsupported custom session/DB logger implementations were not invoked;
  safe profile refusal is an injected negative. The eventual product boundary
  still needs review/implementation; this scaffold does not enforce it in the module.
- Actual Apache SAPI, multiple browser tabs, process kill between consumption and
  checkpoint, arbitrary hooks/loggers/session handlers are not qualified.
- No product booking orchestration, contributions, invoice/customer/Agenda DML,
  SMTP, PDF, cross-version or MariaDB/Enhanced regression qualification in AP3.
- This proof does not promise successful full multi-member batches under
  CHAIN/RELEASE. Acknowledged first result plus visible partial processing is
  the bounded contract, subject to independent review and Owner acceptance.
- CF-02 contribution DML, CF-03–CF-05, final independent Completion-gate acceptance
  and all release gates remain open. No AP3 merge authorization is implied.

## Reproduction and integrity

Use [reproduction instructions](../experiments/ap3/README.md). Existing numeric
ranges and one-shot markers must **never be reused**. Review first; do not execute
historical failing starters. A fresh experiment requires separate unused fixtures
and deliberately regenerated case definitions/baseline, not deleting markers or
resetting old rows.

`experiments/ap3/MANIFEST.json` lists every payload path, size and SHA-256, including
this report. `SHA256SUMS` additionally seals the manifest itself. Neither includes
credentials, private config, session files, raw packet payloads, HTML, cookies or
CSRF tokens. `verify.py` checks completeness/hashes plus independent SQL/order,
terminal-connection, persisted-values, HTTP/readback and historical-state
consistency offline. Integrity verification is not independent architectural approval.

## Erratum and independent follow-up — 2026-11-19 (F27-03)

Independent review at `8c777340da4cdafae7b91bbe4366fc8aad525945`:
**AP3 EVIDENCE ACCEPTED WITH DOCUMENTED LIMITS**;
**COMPLETION GATE INCOMPLETE – TARGETED FOLLOW-UP REQUIRED**.
This records the supplied external judgment, not a formal GitHub review vote.

**E02 correction:** in `AP3-FOREIGN-1568`, the SET admission fails before
Core-BEGIN and before any member DML. This known refusal means **ENTRY REFUSED /
NOT_PROCESSED + STOP**, not an unknown booking outcome. The fault-originator's
pre-existing physical transaction is untouched by admission. CF-02 must use that
E02 classification only when the trace proves this pre-BEGIN/pre-DML boundary.
Unconfirmed BEGIN/end or uncertain in-flight outcomes remain UNKNOWN/STOP under
E11/E13; a later readback never rescues an unacknowledged end.

The original harness returned UNKNOWN/STOP for this case. Its source, JSON,
SQL trace, original STOPs and verifier assertions are preserved exactly; this
erratum corrects their interpretation, not their measured contents. The original
report SHA-256 was `f01a0e611aba1097a386014f89ddd18fdc2c4bd9a3aa85380a510b515e28c4e4`. Only this additive
report correction and the corresponding manifest/hash entries change; the55
payload paths and56 checksum entries remain complete.

| Finding | Disposition and remaining obligation |
| --- | --- |
| F27-01 BLOCKER | Actual pinned contribution/standard-Agenda call-path stability remains a pre-DML obligation in the separate follow-up. |
| F27-02 BLOCKER | Product batch terminal STOP/checkpoint/result distinction remains a separate non-DML patch and spy-test obligation. |
| F27-03 MEDIUM | E02 interpretation corrected here; targeted independent verification pending. Historical output unchanged. |
| F27-04 MEDIUM | Supported actual session/logger configuration must be enforced server-side before DML in the separate follow-up. |

PR #27 remains Draft and requires separate Owner merge authorization. Its evidence
acceptance does not close the Completion gate or authorize CF-02 contribution DML.
Issue #22's six gate tasks, CF-02–CF-05 and release gates remain open.
