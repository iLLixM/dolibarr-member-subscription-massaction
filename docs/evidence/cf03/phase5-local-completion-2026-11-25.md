# Phase 5 local qualification delta — 2026-11-25

## Source and acceptance boundary

The Owner squash-merged [PR #46](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/46)
as `c2dc2cfdadf2c4def6bcd2f2ec20473cea3683b3`. Its sole parent
`b026f1504db27308fc7326e2e88ec2e53b2f48d9` and tree
`201acf36d503e865b20ac2b7d0b195eb1305349f` match the review handoff for
`9c7a06f10e48c581a5a96a3435261528a372f0f4`. The squash changed 31 Markdown
files. F45-03 is **INDEPENDENTLY CLOSED**. F45-01 and F45-02 remain open
test-hardening findings.

Every new local case below is pinned to the full squash SHA above. A used
MariaDB 10.11.19 and `MariaDBSnapshot`. B and C used Oracle MySQL 8.4.13
with `CoreGuarded` and `EnhancedMySQL` respectively. All used Dolibarr
24.0.3 and PHP 8.2.35. Protected runtime-identity, profile, local file-session
checkpoint and logger checks passed. Three fresh database/Documents
backup archive integrity checks passed. Restore execution was outside this
run. B and C ran in separate business-test windows. The public summary
follows modified OD-P5-02. The [prior register](phase5-local-qualification-2026-11-24.md)
retains its original source and all historical PASS and UNKNOWN/STOP outcomes.

## Real local cases

| Case ID and profile | Expected result | Observed result |
| --- | --- | --- |
| P5L-A-M01, MariaDBSnapshot | One fresh contribution, validated invoice, physical PDF and byte-matched local MIME attachment | **PASS, 1/1.** Acknowledged contribution, validated invoice, physical PDF and fixed local recipient. Independent SQL and MIME checks passed for the already completed run. |
| P5L-A-M02, MariaDBSnapshot | Two fresh members in one confirmation with the same finance, PDF and capture checks | **PASS, 2/2.** The controlled continuation booked only the two unused fixtures. SQL, physical PDFs and capture/MIME checks confirmed both. The A single run was preserved. |
| P5L-C-M01/M02, EnhancedMySQL | One and two fresh members with acknowledged outcomes and capture-only delivery | **PASS, 3/3.** Two authenticated confirmations. Three contributions, validated invoices and physical PDFs. The isolated capture accepted three messages for the configured test recipient, and all decoded PDF sizes and SHA-256 matched the physical files. |
| P5L-A/B/C-S01 | Restricted roles lack the bulk action and cannot use the module entry point | **PASS on A, B and C.** Both restricted roles were rejected. The authorized test role reached the confirmation page. |
| P5L-A/B/C-S02 | An old confirmation becomes invalid after the invoice option and description change and the confirmation revision advances | **PASS on A, B and C.** The old revision was rejected while the fresh confirmation was still in that session. All 24 inspected business-table snapshots remained unchanged. |
| P5L-A/B/C-S03 | Two selected members reach preview with the original limit. Temporary limit 1 rejects the selection early. Restoration reopens normal preview | **PASS on A, B and C.** The UI reported the selected and permitted counts. There were zero booking POSTs. The protected readback confirmed the original module setting and unchanged other setting values. Business tables remained unchanged. |
| P5L-A/B/C-S04 | Expired confirmation cannot be opened in a still-authenticated session | **PASS on A, B and C with an aged fixture.** A protected helper aged only one fresh synthetic confirmation per session. The authenticated Web controller refused it without a booking POST. The selected member and its contribution readback remained unchanged. Natural one-hour waiting was outside the completed evidence. |
| P5L-A/B/C-S05 | Wrong-entity selection or confirmation denied on an independent, authorized second entity | **BLOCKED for real qualification.** The local test identities occupy a single business entity. The protected account inventory contains entity 1 and global administrator entity 0. A distinct supported fixture for this test was unavailable. Existing synthetic coverage remains recorded separately. |
| P5L-B-R45, CoreGuarded | Acknowledge a fresh contribution, retain the saved result after reload and refuse the identical consumed confirmation | **PASS.** One contribution without invoice or mail. Independent SQL confirmed 24 tables, the expected contribution and Agenda changes, and identical post-booking business state after the refused replay. |

Actual Web PHP/Core probes for A and C resolved the configured SMTP hostname
to the isolated loopback capture. They reached the fixed recipient and
stopped before SMTP DATA. The booking path checked the module's transport
outcome and the capture independently. The sink accepts only the single
configured test envelope recipient for the corresponding environment and
has no relay. No external delivery was attempted. Settings were restored;
the batch-limit test changed the database row's maintenance timestamp
while restoring its value and metadata.

File reconciliation attributed four changed entries in each A/C Documents
inventory to standard Core statistics caches. Existing business documents
and the case PDFs were checked independently. The original A harness STOP
expected a CoreGuarded-only attachment label after the first transaction
had committed. The original C strict inventory STOP and A continuation
prechecks remain part of the record. Their follow-ups read the existing
results and used only reserved, still-unbooked fixtures for any continuation.

## Coverage disposition

Prior accepted runs cover existing and newly created customers, validated
invoice lines and links, PDFs, Agenda ON/OFF, R16, small batches and B mail.
Prior A/B/C live negative cases cover CSRF and consumed-confirmation refusal
on their recorded source. Those cases were carried forward without broad
repetition. The new B case specifically exercises the merged file-session
change in the live controller. The local unprivileged synthetic regression
also ran 96 file-session assertions, plus controller, transaction,
terminal STOP and finance suites. Neither this nor the live result closes
F45-01 or F45-02.

**Local CF03-NORMAL A/B/C:** PASS within the accepted case matrix, combining the dated prior positive cases with the six new mail/attachment outcomes. **Local CF03-SECURITY-BASELINE:** PARTIAL because the cross-entity Web case remains blocked. The local Phase 5 scope therefore remains **PARTIAL**. Full A–D and release security acceptance remain open.

Hosted D follow-up beyond the focused PR #45 proof, CF03-RISK/CF05-07, the
final exact ZIP and its Hosted D qualification, final independent release
review and separate Owner Release GO remain open. [PR #40](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/40)
remains Draft. Release remains **NO-GO**.
