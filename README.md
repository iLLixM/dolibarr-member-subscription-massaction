# Member Subscription Massaction for Dolibarr

Manage and book membership contributions for multiple members in a single workflow directly from the Dolibarr Members list.

The module reduces repetitive manual work by combining subscription preview, confirmation, booking, optional invoicing, PDF generation, and email communication. It integrates with Dolibarr's standard business processes without modifying the Dolibarr Core.

The interface is available in German and English. All three transaction profiles support the same business workflow when their runtime requirements are met.

Visit [GitHub Releases](https://github.com/iLLixM/dolibarr-member-subscription-massaction/releases) for available installation packages, or build an installation ZIP directly from source.

## What it does

- Select members and preview their contribution amounts, membership periods, and any overlaps before confirming. Approve each overlapping period individually. Dolibarr applies its normal membership end-date rules, including retaining a later end date when applicable, and records configured Agenda events.
- Optionally create and validate a customer invoice. Reuse the linked third party, or allow the module to create and link one from member data when invoicing requires it and the operator has permission.
- Generate invoice PDFs after confirmed booking. Send optional confirmation emails with or without an invoice. When both options are selected, the email requires a verified invoice PDF attachment.
- See separate booking, invoice, PDF, email, and attachment outcomes for each member, with links to saved contributions and invoices. The default batch limit is 100 members. Administrators can configure it, and a lower Dolibarr mass-action limit takes precedence.

## Compatibility

| Component | Requirements and coverage |
| --- | --- |
| Dolibarr | Target versions: 22, 23 and 24. Real integration has been verified in selected Dolibarr 22 and 24 environments. Dolibarr 23 is covered by synthetic tests. |
| Minimum Dolibarr version | 21 — technical admission only, not qualified compatibility. |
| PHP | 7.4 or later, subject to the requirements of the installed Dolibarr version. |
| Oracle MySQL | 8.4 or later. |
| MariaDB | 10.11 or later. |
| Database driver and storage engine | mysqli and InnoDB for affected tables. |
| PostgreSQL | Not supported. |

Choose the transaction profile that matches your database system and hosting capabilities.

| Profile | Database | Requirements |
| --- | --- | --- |
| `CoreGuarded` | Oracle MySQL | Suitable for environments without Performance Schema access, including restricted hosting. Requires supported PHP file sessions in a writable local directory, standard Dolibarr file-only logging, and the ability to set the next transaction to `REPEATABLE READ`. |
| `EnhancedMySQL` | Oracle MySQL | Requires enabled Performance Schema transaction instrumentation, corresponding read permissions and REPEATABLE READ transaction isolation. |
| `MariaDBSnapshot` | MariaDB | Uses sampled connection and transaction state to provide transaction safeguards. |

Compatibility and qualification details for individual releases are available in the corresponding release and project documentation.

## Transaction safety

Each member's financial operations run in a separate guarded database transaction. Each profile checks the database state using the capabilities of its platform. Uncertain database completion stops further bookings.

PDF generation and email are separate effects after booking. A confirmed booking remains saved if either outcome is uncertain. A database rollback may leave filesystem changes, including moves of provisional invoice directories. Mail transport acceptance does not establish delivery.

CoreGuarded can stop after a terminal database completion or an uncertain PDF outcome. It continues after uncertain mail only when the transport problem can be isolated and the checkpoint remains valid.

For technical details, see the [transaction architecture](https://github.com/iLLixM/dolibarr-member-subscription-massaction/blob/main/docs/adr/ADR-0001-core-first-transaction-profiles.md).

## Install and configure

1. Verify the SHA-256 of the installation ZIP against its `SHA256SUMS`, then check your Dolibarr installation for a collision with the unreserved module ID `584620`.
2. Import the verified ZIP via **Setup → Modules/Applications → Deploy/install external module**. For a manual installation, extract the archive’s `membersubscriptionmassaction/` directory into your configured Dolibarr custom modules directory.
3. Enable **Members** and **Member Subscription Massaction**. Open the module setup and select the matching transaction profile.
4. Use internal Dolibarr accounts with permission to read members and create subscriptions. Grant the applicable third-party and invoice permissions for invoicing, including creation, validation, and sending where required by the selected options and Dolibarr’s permission settings.
5. For invoicing, enable **Third Parties** and **Customer Invoices**, configure invoice numbering and a PDF model, and ensure Dolibarr can write to its document storage. For email, configure Dolibarr’s mail transport and a valid sender, and verify the intended recipients.

## Book subscriptions

1. Open **Members**, select eligible members, and choose **Create member subscriptions (bulk)**.
2. Review the read-only preview. Check dates, amounts, and invoice, customer-creation, and email options. Confirm each intended overlap. Unchecked overlaps are skipped.
3. Confirm the run once and review the per-member results.

**Successful** means the contribution was saved with confirmed completion. **Skipped** includes the reason, such as an unchecked overlap. **Failed** indicates a definite failure, and **Unknown** indicates an outcome that could not be confirmed. A stopped batch preserves the results already reached. **Not processed** identifies members it never reached.

## Operating limits

Use this version with a single active Dolibarr business entity and consistent member, customer, invoice, and document access. MultiCompany and cross-entity business sharing are outside the qualified scope. The module creates neither standalone third parties nor payments or bank entries.

For an **Unknown** result or unexpected stop, check saved contributions, membership dates, invoices, documents, and mail evidence before taking further action. The module performs no automatic retries, resends, or compensation.

## Build the installation ZIP

You need **Git** and **Python 3**. Clone the repository and run the builder from the checkout.

```bash
git clone https://github.com/iLLixM/dolibarr-member-subscription-massaction.git
cd dolibarr-member-subscription-massaction
python3 tools/build-release.py
```

The builder uses committed files at `HEAD`. For a release, download the published ZIP, check out its local release tag before building, or pass that tag to the builder with `python3 tools/build-release.py TAG`. The builder checks explicitly selected versioned tags against the module metadata, including versions ending in `-dev` or `-beta`. Builds from development commits may contain unreleased changes.

The default output is `build-output/<module-version>/<full-source-commit-sha>/` under the repository root. Git ignores this directory. The builder creates the directory when needed and replaces the versioned module ZIP and `SHA256SUMS` on subsequent builds in the same directory. The JSON report on standard output includes the exact source and output path. From that directory, run `sha256sum -c SHA256SUMS` before installing the ZIP.

Use `--output-dir ../msm-build` for a custom output directory. Its parent must exist. Other files in the output directory stay in place. You can also pass an exact local commit as the source. The builder leaves the checkout unchanged and reads only local Git data.

Install the generated module ZIP using the steps above. GitHub's automatically generated **Source code** archives are repository snapshots and cannot be installed as Dolibarr modules.

## License and documentation

Licensed under GPL-3.0-or-later. See [COPYING](COPYING), the [documentation guide](https://github.com/iLLixM/dolibarr-member-subscription-massaction/blob/main/docs/documentation-guide.md) or [Contributing](https://github.com/iLLixM/dolibarr-member-subscription-massaction/blob/main/CONTRIBUTING.md).
