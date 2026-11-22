# PR #38 – Core parity and Massaction regression acceptance (Owner 2026-11-22)

**Development-only evidence plan.** Read with [binding Owner decision](owner-core-parity-2026-11-22.md), [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22) and ADR-06/11/12. **No real A–D or release claim.**

| Scenario / security property | Executable regression | Evidence type |
| --- | --- | --- |
| Member contribution without invoice, amount=0 and regular amount | `test/unit/booking.php`, `test/unit/cf05-finance.php` | Synthetic Core adapter and original guarded session |
| Invoice existing linked Third Party, line, validation, subscription link | `test/unit/invoice.php`, CF05 `existing` | Synthetic production service call shape/readback |
| Invoice auto-create Third Party only if selected with invoice | `test/unit/invoice.php`, CF05 `new`, `test/unit/invoiceprofile.php` | Synthetic Core API calls plus rights |
| Contribution-only mail success and exception without fictional attachment | CF05 `contribution_mail_success`, `contribution_mail_throws` | Postcommit adapter, no SMTP |
| PDF verified, attached and mail transport after suitable CoreGuarded completion | CF05 `existing` / `new`, PDF/mail/postcommit unit families | Synthetic file/hash and mail evidence |
| Standard Agenda and Logevents settings no longer rejected by source whitelist | CF05 `core_agenda_copy_parity`, `test/unit/cf05-core-parity.php` | Synthetic settings + read-only pinned Core inspection; **does not execute real Agenda trigger** |
| Installed Core/extension invoice numbering/PDF model lookup | `test/unit/invoiceprofile.php` custom directory fixture; `test/unit/cf05-core-parity.php` | Production model admission via injected directory seam; no arbitrary PHP execution |
| Real checked-out V22/V23/V24 Core business entrypoint availability | `test/unit/cf05-core-parity.php` on each pinned CI Core | Read-only source/method availability, **not real API integration** |
| Privilege/entity/revision/fingerprint/direct service and one-shot | `test/unit/profile.php`, `coreguarded-session.php`, `f29-rights.php`, `preview.php` | Negative synthetic; no real HTTP |
| Modified confirmation, duplicate/replay, lost/unknown Core transaction | `test/unit/booking.php`, `coreguarded.php`, CF05 `depth` and negative cases | Synthetic stop/rollback staging |
| Postcommit PDF/SMTP unknown/exception and no following member | CF05 `pdf_unknown`, `mail_unknown`, `pdf_throws`, `mail_throws`, `pdf_invalid_written`, negative transport/hook | Synthetic two-member STOP; no external transport |
| Full + Hosted security-relevant path trigger | `test/unit/ci-triggers.php`, Full/Hosted workflow filters | Static workflow regression |

**Acceptance:** Full 9-job standard / 21-job Full / 1-job hosted checks must succeed on the *identical final PR-HEAD*. Passing is not an assertion that arbitrary external Core plugins are benign. Source hash/pinning tests were removed by Owner policy, *not silently skipped*. Previously existing secured business scope exclusions and ADR-12 transaction observer distinction remain unchanged.

**Known gap:** Real V22–V24 database transaction, Core trigger/Agenda, customer-code numbering, PDF filesystem, hook/SMTP interactions, deployment ZIP and exact final-source installation have not been tested in this work package. They belong to Owner-authorized Phase 5 and release evidence.
