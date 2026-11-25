# Phase 5 Hosted D — development qualification

## Source and admission

The installed and verified Git checkout was
`c2dc2cfdadf2c4def6bcd2f2ec20473cea3683b3` (merged [PR #46](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/46),
following the PR #45 shared file-session correction). Hosted D ran
Dolibarr 22.0.5, Oracle MySQL 8.4.13 and the stored `CoreGuarded` profile.
The actual Web request used PHP 8.4.28 CGI/FastCGI. The hosting-local
readback used CLI PHP 8.3.37 under the protected hosting operator account.

The Owner confirmed current, integrity-checked database and Documents
backups. The fixed CLI reader checked the deployed commit, profile,
configured database identity and nine selected InnoDB tables. An
administrator-authenticated Web probe confirmed the session checkpoint and
file logger admitted the standard provider settings, the company context
was available and invoice and customer creation were enabled. Its
module-file SHA-256 matched the deployed source. The temporary probe
was removed and its former route returned HTTP 404. The approved cases
used newly created synthetic fixtures and a separately bound browser test
role. Prior [PR #45 D contribution, session and consumed-replay evidence](../../dolienv-environments.md#hosted-d-checkout-and-pr-45-validation-2026-11-24)
retains the original source `0adc259d4739b44f9b1fb3a49574d7e1c318132c`.
No historical operation was replayed.

## Initial Hosted D checkpoint

The following records the original finance/security checkpoint. Pending and
blocked statuses are updated by the later evidence below.

All newly executed cases below use the installed SHA above. Each finance
confirmation selected one synthetic member and deselected mail.

| Case | Expected result | Observed result |
| --- | --- | --- |
| D-F01 | Reuse existing linked customer, compute the automatic end, validate the invoice and generate its PDF after COMMIT | **PASS.** Contribution committed, customer reused and invoice validated. The reader confirmed the EUR 120 contribution and invoice line, dates, final numbering and association with the contribution. Two configured Agenda events observed. |
| D-F02 | Bill a separate, earlier period with an explicit manual end and retain the existing later membership end (R16) | **PASS.** Contribution committed and invoice validated for the earlier period. The persisted later end remained 31 December 2096. The same existing customer remained linked. Two configured Agenda events observed. |
| D-F03 | Create and link a new customer only for the invoice-selected automatic-third-party case | **PASS.** Contribution committed with a new linked customer and validated invoice. Expected dates, line, amount and relation verified. Three configured Agenda events observed, including new customer creation. |
| D-P01–P03 | Compare physical PDFs with authenticated Web access, by invoice | **PASS, 3/3.** All results reopened from the stored Web result. Each authenticated PDF was readable and matched its hosting-local physical file in both size and SHA-256. |
| D-S01 | Authorized test role can use the module; restricted roles are refused | **PASS.** The authorized test role completed the business cases. Both restricted roles lacked the action and were refused direct module access. |
| D-S02/S03 | Reject an authenticated preview POST with missing CSRF and refuse a stale confirmation after editing the invoice option | **PASS.** Both requests were rejected. The independent readback showed the same business post-state as the completed D-F03 case. |
| D-S04 | Show two selected members normally, reject them before preview at limit 1, and recover the original limit and behavior | **PASS.** The temporary setting was restored to 100 and independently verified. Two-member preview worked again, and the independent SQL check found the same business post-state. |
| D-S05 | Reject a naturally expired confirmation in the original authenticated session | **PENDING.** A fresh no-booking observation was started. No natural expiry result is accepted at this checkpoint. |
| D-S06 | Refuse cross-entity access using a separate supported test entity | **BLOCKED.** Multi-entity mode is disabled on the independent D installation and no second authorized test entity exists. |
| D-M01/M02 | One plus two members using an isolated, no-relay SMTP capture with independent MIME/PDF equality | **BLOCKED.** The effective configuration targets a normal SMTP relay and forces an external mailbox. Protected raw MIME export for an isolated capture was unverified. No send or external delivery was attempted. |
| D-S07 | Verify the profile-specific result checkpoint and continuation/STOP evidence | **PASS for acknowledged nonterminal completion and stored result reload** on the finance cases, with prior bounded one-shot/replay evidence. Terminal completion and destructive faults were **NOT EXECUTED** on shared hosting. |

Each finance case used separate hosting-local before and after SQL readbacks
across 12 business tables. Existing rows remained unchanged apart from the
designated member. The confirmed invoice issue dates agreed with the
independent SQL dates. The case results do not imply any external mail
delivery or new guarantees for unsupported completion behavior.

## Hosted D completion addendum (2026-11-25)

These follow-up cases retained the installed PR #46 source
`c2dc2cfdadf2c4def6bcd2f2ec20473cea3683b3` and CoreGuarded profile.
Previously created business objects were preserved. The Owner confirmed fresh
database and Documents backups after their manual tests. The new positive cases
used separate fresh synthetic fixtures and fixed independent before/after
readbacks.

| Case | Observed result |
| --- | --- |
| D-S05 | **PASS.** A valid confirmation was allowed to expire naturally for one hour and then reopened in the same authenticated session. Dolibarr returned its explicit unavailable/expired message over HTTP 200 and displayed no submit control. The harness sent zero booking POSTs; all 11 independently inspected business tables remained unchanged. |
| D-SMTP2 single | **PASS, 1/1.** One contribution with acknowledged nonterminal completion, one validated invoice and verified PDF, one standard SMTP handoff and one Owner-confirmed external test mailbox receipt. |
| D-SMTP2 double | **PASS, 2/2.** One bulk confirmation for two fresh members, with two acknowledged nonterminal contributions, validated invoices, verified PDFs, standard SMTP handoffs and Owner-confirmed external test mailbox receipts. |
| D-AOFF | **PASS.** With only the automatic contribution Agenda event disabled, one contribution was confirmed without invoice or mail. Independent SQL across 11 tables verified zero new Agenda events or resource rows and the expected stored contribution. The original setting and unrelated settings were read back and confirmed after restoration. |

In both SMTP2 cases, the loaded Core settings matched the new authenticated TLS
SMTP route and fixed Owner-controlled recipient, with mail debugging disabled.
The independent readback reconciled 14 tables per case and confirmed route
stability. All three PDFs matched in size and SHA-256 between the hosting-local
physical file and authenticated Web download. All three Owner-downloaded
attachments matched the corresponding PDF SHA-256. This development evidence is
Owner-confirmed external delivery and decoded attachment identity. Raw SMTP
envelope/MIME capture and isolated no-relay qualification were outside these
Hosted D cases. The original isolated-capture block remains part of the
checkpoint history.

The earlier SMTP observations on the previous route remain separate. The Owner
later confirmed receipt, including a case that had reported mail/attachment
UNKNOWN and STOP during the request. That application's historical verdict
retains its original classification. No historical booking or mail was replayed.
PR #49's separately scoped ADR-08A continuation was outside these Hosted D tests.
Destructive faults and exact-release-ZIP qualification remain separate.

## Disposition and independent review

Hosted D **CF03-NORMAL PASS within the approved development case matrix**.
Live **CF03-SECURITY-BASELINE remains PARTIAL** because the cross-entity case
requires an authorized second entity. Prior checkpoint and PR #45 evidence
retain their tested source and scope. Under modified OD-P5-02 these are compact,
Owner-authorized summaries. F45-01/02, Hosted D ADR-08A delta, CF03-RISK,
CF05-07, R0-04 exact release ZIP and final release gates remain open.
[PR #40](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/40)
remains Draft. Release remains **NO-GO**.
