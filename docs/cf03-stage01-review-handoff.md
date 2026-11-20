# CF-03 – Stage 0/1 engineering status and review handoff

**Scope:** Preparation-only Draft PR for Issue #22. **Do not merge as the completed CF-03 qualification.** This submission implements the bound A-D environment contract, read-only witnesses and offline unit coverage, **not** real DB/HTTP/fault/ZIP/SMTP tests or new business code. Review current PR source HEAD, do not equate historical POS01-C, MDB-TEST-01 or I107 source with current CF-03 runtime proof.

## Decisions and scope

- User-approved A/C deployment: **one V24.0.3 webroot, one MySQL 8.4.13 instance and one schema; sequential operator-controlled changes of DB credentials in `conf.php` plus matching Dolibarr administrative profile selection**. No automatic user/profile switch and never two concurrent A/C test windows.
- B: independent V24.0.3/MariaDB 10.11.19 reference, MariaDBSnapshot; application DB username to be supplied for accurate identity matching.
- D: Owner-available hosted V22.0.5 / MySQL 8.4.13 reference, actual URL, root/schema/account/port to be supplied; only safe positive and ordinary negative tests until explicitly authorized.
- Only new CF03LAB synthetic fixtures, never historical members 56, 57, 58 or subscription artifacts 65–67. Operator-only authenticated business writes occur in later separately authorized stages; no source or CI process opens live production database sessions.

## Stage-0 inventory

| Contract / source | Reuse decision | Missing real evidence |
| --- | --- | --- |
| CF-02 `MsmCoreGuardedTransaction`, `MsmBookingService`, F29-01/F29-02 rights proxy and test suite | Reuse unchanged; no DB/transaction code modification in this PR | Real 1213/1205, foreign BEGIN, ACK-loss, post-DML rollback, CHAIN/RELEASE |
| `MsmTransactionProfile` and profile/confirmation revisions | Pin selected profile and hash raw configuration, no edits to authoritative `llx_const` except via operator UI | Manual A/C switch, effective Apache credentials, stale confirmation behavior |
| CF-02 POS01-C witness | Preserve A-only historical test, implement new distinct read-only multi-profile source-bound witness | Fresh A/B/C/D business cases on current source |
| Existing `test/unit/coreguarded.php`, `f29-rights.php`, `f29-book-flow.php`, `coreguarded-session.php` | Leave tests intact; add separate `cf03-lab.php` pure tests | Live MySQL server, HTTP, Agenda business DML |
| Existing `test/integration/options-http.py` | Preserve as historical read-only HTTP fixture, do not treat as updated CF-03 massaction replay test | Authenticated replay, CSRF, profile switching against current implementation |
| `test/integration` and historical MDB/I107 test documentation | Reuse test design/requirements, do not transplant old PASS to new SHA | Current-source MariaDB/Enhanced invoicing and hosted V22 ZIP |
| Nine-profile `.github/workflows/ci.yml` and 21-profile `ci-full.yml` | Add one pure offline CF03 fixture runner; don't broaden PR matrix | Live per-profile integration and full optional workflow execution |
| Original 71-ID requirements CSV | Remains single authority; no duplicate requirements matrix or changed assignment | CF03 risk-to-test matrix only |

## Files for independent Stage-1 review

- `tools/cf03-lab.lib.php`: pure immutable environment contracts, scenario validation and bounded before/after comparator.
- `tools/cf03-lab.php`: operator-only, read-only preflight, fresh fixture check, immutable baseline and one-time verification; independently opened mysqli connection and strict module/config/profile/rights/source match.
- `test/unit/cf03-lab.php`: isolated pure contract tests for A/C shared schema, dynamic B/C/D configuration, supported contribution/no-change and scoped invoice-existing readback and negative drift cases.
- `docs/cf03-qualification-plan.md`: A-D matrix, remaining risk gates, source and architectural limits.
- `docs/cf03-operator-runbook.md`: no-writing preflight commands and only provisionally described future booking sequence.
- Nine/21 profile CI and existing offline runner: include one new no-DB unit file.

## Specific review questions

1. Is the single-schema A/C model represented correctly, with distinct application DB identities and explicit per-entity profile selection, one change window at a time?
2. Does every preflight stop on wrong Dolibarr version, module symlink, schema/account, server family/version, profile, completion/autocommit, relevant InnoDB engine or missing/wrong Performance-Schema privilege?
3. Does pure PHP and CLI code execute under the supported PHP 7.4 syntax/runtime and V22/V24 Core sources? Is each SELECT genuinely read-only, with no connection reset, setup write, commit, rollback, booking or HTTP?
4. Are check-only, baseline and verify bound to member ID/ref/marker, stored profile hash, config SHA-256, source SHA-256 and the exact installed Core file hashes and source/DB family, and are file writes one-shot and outside the webroot?
5. Are successful snapshot comparisons correctly limited to selected Core contribution/Agenda/invoice/unchanged table states, without false physical-event/HTTP/SMTP/PDF certainty? Note that invoice-existing checks only one linked invoice/line and coarse Agenda global deltas; this is not a complete invoice acceptance.
6. Does a stopped/ambiguous test preserve original evidence, prevent accidental redo and avoid automatic account switch or cleanup? Does any test allow a real non-synthetic member or existing historical fixture?
7. Are Stage 2–4 explicit gaps kept open: active Enhanced event instrumentation/owned transaction, real mysql fault/rollback/parallel/replay, real MariaDB invoice, hosted V22 ZIP, PDF/SMTP and full CI?

**Acceptance request for this particular PR checkpoint:** suitability to begin owner-operated read-only A–D preflights and then design the separate case-specific real fault/HTTP and invoice harnesses. No request for CF-03 gate closure, full development merge, production compatibility or v0.1.0 release. Issue #22 remains OPEN, CF-03 six checkboxes remain unchecked. Any finding must be resolved on an exact final PR head with an independent scoped review; Owner merge decision is separate.
