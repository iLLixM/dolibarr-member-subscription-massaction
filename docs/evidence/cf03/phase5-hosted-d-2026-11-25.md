# Phase 5 Hosted D — finance, document and security delta

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

## Hosted D result matrix

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

## Disposition and independent review

Hosted D CF03-NORMAL is **PARTIAL** pending real mail and attachment
qualification. The applicable live CF03-SECURITY-BASELINE is **PARTIAL**
pending the expiry outcome and supported cross-entity evidence. Preserve the
previous accepted scopes and all UNKNOWN/STOP results. Under modified
OD-P5-02 these are compact, Owner-authorized summaries. F45-01/02 remain
open test-hardening findings. CF03-RISK, CF05-07, R0-04 exact release ZIP
and the final release gates are separate. [PR #40](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/40)
remains Draft. Release remains **NO-GO**.
