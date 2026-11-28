# Member Subscription Massaction v0.1.0

Preview, confirm and book member subscriptions in bulk from the Dolibarr Members list. The module uses the normal Dolibarr business process for each member. It supports optional invoice creation, conditional creation of a linked third party, invoice PDF generation and optional confirmation email. It creates no standalone third parties, payments or bank entries.

## Compatibility and scope

- Target Dolibarr versions are 22, 23 and 24. Real development qualification is bounded to recorded V22/V24 configurations. V23 evidence is synthetic only. The descriptor's V21 technical admission floor does not establish V21 release qualification.
- The module requires PHP 7.4 or later, subject to the requirements of the installed Dolibarr Core. Use mysqli with MariaDB 10.11 or later or Oracle MySQL 8.4 or later and InnoDB for the affected tables. PostgreSQL is outside scope.
- Use one active business entity with consistent access rights and member, third-party, invoice and document scope. MultiCompany and inter-entity business sharing are outside the qualified scope.

## Installation and configuration

1. Verify the SHA-256 checksum of the downloaded `module_membersubscriptionmassaction-0.1.0.zip` against the supplied `SHA256SUMS`. Proceed only if it matches.
2. Verify the target installation has no module-ID collision with unreserved ID `584620`.
3. Import the verified ZIP from **Setup → Modules/Applications → Deploy/install external module**. For manual installation, extract the top-level `membersubscriptionmassaction/` directory into the configured Dolibarr custom directory.
4. Enable **Members** and **Member Subscription Massaction**.
5. In the module setup, explicitly select a profile for the database and grant operators the appropriate Core rights for the selected actions. The module defaults to a 100-member batch limit and honors a lower Core mass-action limit.
6. For invoices, enable and configure Third Parties and Customer Invoices, including numbering and an invoice PDF model. For confirmations, configure Core mail and a valid sender. Use only the intended recipients and validated configuration.

| Profile | Database prerequisites |
| --- | --- |
| `MariaDBSnapshot` | MariaDB. Uses sampled connection and transaction state. |
| `CoreGuarded` | Oracle MySQL. Requires a supported PHP file-session checkpoint and supported Core file logging. Performance Schema access is unnecessary for this profile. |
| `EnhancedMySQL` | Oracle MySQL. Requires configured Performance Schema transaction observation and SELECT access to the relevant transaction instrumentation. |

## Booking and reconciliation

1. Select eligible members and choose **Create member subscriptions (bulk)**. Check the read-only preview for dates, amounts, overlap warnings, and the exact options before confirming. When an invoice is selected, a linked third party is required unless automatic creation was explicitly selected and authorized.
2. Review the per-member business, PDF, mail and attachment outcomes. A confirmed booking remains saved when a later PDF or mail outcome is uncertain. Mail transport acceptance does not establish delivery.
3. A **STOP** preserves the results already reached. **NOT_PROCESSED** identifies subsequent selections that were never reached. **SKIPPED** has its own eligibility or selection reason. CoreGuarded can stop after an acknowledged CHAIN/RELEASE or an uncertain PDF effect. Only positively isolated mail uncertainty can qualify for CoreGuarded continuation under its checkpoint and transaction safeguards. All profiles block sending an unverified invoice attachment.
4. For **UNKNOWN**, reconcile the persisted contribution, invoice and related records, Documents and mail evidence as applicable before deciding what to do next. Do not repeat the operation or resend automatically. A database rollback does not undo every filesystem effect, including Core moves of provisional invoice directories.

## Release notes

The initial version adds the guarded subscription workflow, optional finance/PDF/mail, DE/EN interface and per-member outcome reporting across the three transaction profiles. Checkout-based development evidence and installed-package verification have separate scopes. The [project repository](https://github.com/iLLixM/dolibarr-member-subscription-massaction) records supported configurations and qualification results.

This module is distributed under GPL-3.0-or-later. See [COPYING](COPYING).
