# MemberSubscriptionMassaction for Dolibarr

Create membership subscriptions for several members in one guided workflow.
Review each member's contribution, dates and warnings before confirming the run;
then see a separate result for every member. The module extends the member list
without modifying Dolibarr Core.

**v0.1.0 release candidate preparation:** metadata and an installable ZIP are being
prepared. Final ZIP integration (R0-04) and independent release review (R0-07) are
still required. This is not a published release or general production approval.

## What you can do

- Select members in the existing list and open **Create member subscriptions (bulk)**.
- Review individual membership types, contribution amounts and proposed periods.
  Amounts come from the type or eligible contribution history; they are not a
  free-form batch price override. Zero contributions are supported in the tested profile.
- Adjust individual start/end dates, reset a calculated end, apply a common start
  date or explicitly apply the configured month-end/year-end rule.
- Use a plain-text description with `{Member type}`, `{Start date}` and `{End date}`.
- Review the separate booking confirmation and consent explicitly to displayed
  overlaps. Missing consent skips the affected member; there is no silent splitting.
- Inspect successful, skipped, failed and uncertain member results separately.
  An uncertain result requires manual reconciliation, never automatic rebooking.

## Optional customer, invoice, PDF and email

Invoice creation and confirmation email are independent choices. When invoicing is
selected, an accessible linked customer is required; creation of a missing customer
requires a separate explicit option and the corresponding Core permissions.
Supported combined bookings create the subscription and validated standard invoice
in one transaction per member. PDF generation and email follow a confirmed commit.
Invoice email requires a verified invoice PDF; a document/mail problem does not
undo an already committed subscription. No payments or bank entries are created.

The real invoice reference profiles are deliberately narrow: entity 1, EUR,
quantity 1, zero membership VAT, no configured subscription product or local taxes,
Mercure numbering and Sponge PDF. Other technically accepted configurations are
not automatically qualified. See [compatibility](docs/compatibility.md) and
[operations and error handling](docs/operations.md) before using optional delivery.

## Batch size

The default maximum is **100 members per run**, including existing activations.
Set `MEMBERSUBSCRIPTIONMASSACTION_MAX_MEMBERS_PER_BATCH` through Dolibarr's constants
administration to choose another positive integer. Missing, invalid or nonpositive
module values fall back to 100. The effective maximum is the smaller of this value
and `MAIN_LIMIT_FOR_MASS_ACTIONS` (unset: 1000; explicitly invalid/nonpositive: zero).
Oversized selections show the selected count, maximum and a return link before
member loading or expensive preview evaluation. No selection is truncated or split.
Pending confirmations are checked against the current limit again.

Start with small batches. One manual Owner-confirmed, contribution-only browser
run created 100/100 subscriptions in about 6 seconds; this is a bounded observation,
not an HTTP timeout, parallel-load, PDF or SMTP throughput guarantee.

## Requirements and compatibility

| Component | Intended v0.1.0 scope and evidence boundary |
| --- | --- |
| Dolibarr | Intended release scope V22–V24. Technical admission starts at V21; V21/future versions are not thereby qualified. |
| PHP | Technical minimum 7.4; real reference runs used PHP 8.2.35. |
| MariaDB | Owner-required minimum 10.11; exact V24 reference: 10.11.19. |
| Oracle MySQL | Owner-required minimum 8.4; exact V24 reference: Community 8.4.13, with the required Performance Schema setup. |
| Database driver/tables | Direct `mysqli`, InnoDB for touched tables; profile-specific transaction prerequisites apply. |
| PostgreSQL | Not supported. |

Minimum policy, technical admission, synthetic CI and actual integration are
separate. See the [detailed compatibility matrix](docs/compatibility.md).
Module ID **584620 is unreserved**, retained by explicit Owner exception; check
for local collisions before installation. Do not install over a conflicting module.

## Installation and use

1. Check the compatibility profile and local module-ID collision prerequisites.
2. On a separately approved test installation, import the candidate ZIP through
   Dolibarr's external module installer or extract its `membersubscriptionmassaction/`
   folder into `htdocs/custom/`. Do not overwrite a working installation blindly.
3. Enable Members, then MemberSubscriptionMassaction. Give internal users the
   existing Core member-read and subscription-create rights; optional actions need
   their own Core modules and rights.
4. Select members, open the bulk action, review dates/options and confirmation,
   then submit the booking once. Inspect every result before leaving the page.

See [installation and configuration](docs/installation.md) for ZIP verification,
rights, settings, invoice/email prerequisites and the separate R0-04 acceptance.

## Safe operation and known limits

Confirmation, permission/entity checks, overlap checks and transaction revalidation
help reject stale or unauthorized work. Each member has its own transaction; the
whole batch is not one atomic transaction. Stored results are session-bound.
There is no durable operations journal, background queue, automatic retry/resume,
rebooking, PDF regeneration or email redelivery. Session loss, crash or an
unconfirmed commit needs manual investigation before any further business action.
Third-party hooks and filesystem/SMTP effects are not generally transactional.

Recommended desktop screen: 1440 × 900; targeted desktop viewport: at least
1024 × 768. Wide member tables can scroll internally. There is no dedicated
smartphone interface or general accessibility/browser qualification.

Read [operations and reconciliation](docs/operations.md) and
[release notes v0.1.0](docs/release-notes-v0.1.0.md) for accepted residual risks,
known P3 observations and the outstanding release gates.

## License and project sources

GPL-3.0-or-later; see [COPYING](COPYING). Derived from the design of
[Dolibarr PR #42620](https://github.com/Dolibarr/dolibarr/pull/42620), with source
attribution retained. No Core patches, KSeF or FacturX code are included.
Development history, test provenance and the authoritative requirements are kept
in the [source repository](https://github.com/iLLixM/dolibarr-member-subscription-massaction),
separately from the user-facing package documentation.
