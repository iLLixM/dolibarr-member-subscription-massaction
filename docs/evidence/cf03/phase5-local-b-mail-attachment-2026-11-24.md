# Phase 5 local B mail and attachment qualification

**Result on 24 November 2026: PASS for the three tested member outcomes.**
The Owner-authorized real browser campaign used the clean deployed module
commit `ba5c9a3121754fcbd4259bb8906ad1dd17f1ce00` in local dolienv B
(`CoreGuarded`, Dolibarr 24.0.3, PHP-FPM 8.2.35, MySQL Community Server 8.4.13).
The fixtures were freshly created synthetic members.

## Bound runs and observed outcomes

| Authenticated bulk confirmation | Selected members | Acknowledged contributions | Validated invoices and physical PDFs | Local SMTP captures | MIME attachments matched |
| --- | ---: | ---: | ---: | ---: | ---: |
| Single member | 1 | 1 | 1 | 1 | 1 |
| Two members in the same run | 2 | 2 | 2 | 2 | 2 |
| **Total across two runs** | **3** | **3** | **3** | **3** | **3** |

Before any booking, two protected probes ran in the actual B PHP-FPM pool,
first against the proposed hostname and then with that hostname configured.
The hostname resolved to the isolated loopback SMTP capture. Dolibarr's
own SMTP connection routine reached the expected peer. The capture returned
`250` for the controlled envelope recipient. The probes ended with
`RSET` and `QUIT` before SMTP `DATA`.

Each member showed an acknowledged nonterminal Core completion, a verified
PDF and standard transport acceptance after the durable result checkpoint.
An independent readback reconciled 24 business tables per run, including
new synthetic customers, contributions, validated EUR 120 invoices, invoice
lines and contribution links. Existing business rows and the A/C snapshots
were unchanged. The Documents witness verified each physical invoice PDF.
A separate protected capture verifier decoded one PDF attachment per message.
Each matched the corresponding physical invoice PDF in size and SHA-256.

Both temporary configuration values, the SMTP hostname and the synthetic
template selection, were restored to their exact prior values. The settings
readback verified 263 other settings, and a final FPM check confirmed the
restored route. The initial browser launcher stopped during initialization
before any HTTP request. The corrected launcher rechecked the unused fixtures
before the two successful confirmations.

## Evidence and scope

The private evidence manifest contains 20 entries. Its SHA-256 is
`bd89acd5c59d032a6238a885140b066992c6ed47ccda3af844f85207bba15ca7`.
Manifest verification passed. It covers the FPM probes, before/after
snapshots, run results, independent readback, MIME proof, case matrix and
tool checksums. Raw captures and identifying fixture details stay private.

The three sends were accepted by the isolated local capture. External delivery,
other profiles or hosts, and Hosted D exact-package mail are outside this
evidence. The historical B02 mail/attachment and `DOLIENV-ACC-01` outcomes
retain their `UNKNOWN/STOP` dispositions. The
[non-mail review in PR #42](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/42)
has a separate scope and review head. Independent acceptance of these mail
results, broader mail integration and the separate
[release gates](../../release-qualification-v0.1.0.md) remain open.
