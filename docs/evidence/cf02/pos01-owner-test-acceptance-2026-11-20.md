# CF-02 — POS01 owner test acceptance and preserved exceptions (2026-11-20)

**Provenance:** Owner-supplied console output, a manual authenticated Dolibarr browser result, and independently compared sanitized witness JSON artifacts in the project conversation. The original private evidence files remain with the operator. This document is a **sanitized reconciliation**, not a replacement for their preserved bytes, a new script execution, or a claim of physical transaction/event identity.

**Exact POS01-A/B tested source HEAD:** `c2347f610efc1c4c160dd0b21ffaf095753e7e40` (operator ran `git rev-parse HEAD` before B; eight witness-recorded source SHA-256 fields were identical before and after B). Target was Dolibarr V24.0.3/PHP 8.2/MySQL 8.4.13, isolated `<PRIVATE_MYSQL_SCHEMA>`, restricted `<RESTRICTED_ACCOUNT>`, configured CoreGuarded and `NO_CHAIN`.

## Scope and chronology

1. Earlier synthetic B/C/D gate: adapter 88 assertions PASS, local file-session 19 PASS and existing synthetic suites PASS; restricted-MySQL privileges, five InnoDB tables, autocommit=1, NO_CHAIN and P_S SELECT refusal with error 1142 were Owner-confirmed. See [Stage-0 report](stage0-owner-reported-2026-11-20.md).
2. Separate loaded-Core `--empty-transaction` on the limited account reported PASS, NO_CHAIN and `can_continue=true` for a real empty Core BEGIN/ROLLBACK; no business DML. See [empty transaction record](empty-core-transaction-2026-11-20.md).
3. **POS01-A**: owner first ran the real read-only check/baseline on synthetic member **56**, then performed exactly one manual web booking. Browser result was successful; witness persisted subscription **#65**, 120.00 EUR, actual dates **2026-11-20–2027-11-19**, same member end, one standard Agenda event **#99** and one actor resource, with no unexpected monitored global count changes. The Owner confirmed the start date was not adjusted to the required 2080-01-01. Description marker was also absent. The original witness **STOP `CF02MemberEndMismatch`** is preserved; **A is not a formal POS01 PASS**.
4. **POS01-B**: owner supplied `git rev-parse HEAD` = `c2347f610efc1c4c160dd0b21ffaf095753e7e40`, completed check-only and baseline each with PASS on synthetic member **57** / reference **MEM2611-0057**, then performed exactly one authenticated frontend booking under **CoreGuarded**. The UI reported "1 successful · 0 skipped · 0 failed · 0 unknown · booked total: 120.00 EUR" and "commit confirmed" for subscription **#66**. Invoice/customer creation and mail were off.
5. Independent POS01-B readback persisted subscription **#66** for member 57, type 2, amount 120.00 EUR, start **2080-01-01 00:00:00**, end **2080-12-31 00:00:00**, matching member `datefin`. Standard Agenda `AC_MEMBER_SUBSCRIPTION_CREATE` **#101** and one user resource with actor ID 4 were present. Counts changed solely in subscription **61→62**, actioncomm **95→96**, actioncomm_resources **95→96**; the other nine monitored table counts were unchanged. The native DB identity, CoreGuarded configuration, five InnoDB engine values, other selected member fields and eight recorded source hashes were unchanged.
6. **POS01-B witness result remains `STOP / CF02SubscriptionPostconditionMismatch`**, because the persisted note/description did **not** contain the expected marker `CF02LAB-20261120B1`. The webpage displayed the normal generated label "Contribution, MSM-I105-YEAR, 01/01/2080 - 12/31/2080". This **single remaining failed comparison** was not reclassified as a script PASS and was not repaired by changing the witness or the stored subscription.

## Integrity and source boundaries

| Item | Recomputed SHA-256 / state |
| --- | --- |
| POS01-A supplied baseline.json | `65cde9dfb72896ced815d0451331a9aeadd91f1363ee356930b544c5cda24f65`, matching supplied baseline.sha256 |
| POS01-B supplied baseline.json | `f4f9e11fa1424f99fe8ea7d2acb9f3a8efe8ef4747750cc685a4597356c4d0c2`, matching supplied baseline.sha256 |
| POS01-A witness verdict | `STOP / CF02MemberEndMismatch` |
| POS01-B witness verdict | `STOP / CF02SubscriptionPostconditionMismatch` |
| POS01-A/B source and config | Before/after selected source hashes and configuration match, not a complete deployment attestation |

Source checksum comparison and readback are stronger than an isolated browser success message, but do **not** independently prove the physical COMMIT event, identify every possible hidden hook effect, or prove unchanged values in tables whose row counts stayed constant. Real error/replay/concurrency testing is separate.

## Binding Owner decision — review-entry scope only

On **2026-11-20**, after receiving the POS01-B comparison, the Owner **explicitly waived any additional rerun solely to remedy the absent description test marker** and accepted the remaining executed tests/validations as **sufficient evidence for an independent external CF-02 review**.

- **Disposition `CF02-POS01-MARKER`: OWNER ACCEPTED WITH DOCUMENTED EXCEPTION — NON-BLOCKING FOR EXTERNAL REVIEW ENTRY ONLY.**
- Original `STOP`, original snapshots and readback mismatch remain intact. This is an accepted test-procedure/expected-label discrepancy, not proof of a program defect and not a full POS01 witness PASS.
- The Owner does **not** hereby waive future independent source/transaction/security findings, outstanding CF-03 real tests, real Enhanced/MariaDB non-regression, merge approval or public release qualification.
- No rebooking of members 56 or 57, manual correction of contributions or overwrite of existing `verify.started` / baseline files is authorized.

**Conclusion:** Bounded real CoreGuarded contribution-only + standard Agenda positive integration is **OWNER-ACCEPTED WITH DOCUMENTED LIMITS**; CF-02 may enter independent review. The reviewer's eventual verdict and a separate Owner development-merge decision remain OPEN.
