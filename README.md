# Member Subscription Massaction for Dolibarr

Manage and book membership contributions for multiple members in a single workflow directly from the Dolibarr Members list.

The module reduces repetitive manual work by combining subscription preview, confirmation, booking, optional invoicing, PDF generation, and email communication. It integrates with Dolibarr's standard business processes without modifying the Dolibarr Core.

The full business workflow is available across all three supported transaction profiles, subject to their respective runtime requirements.

Visit [GitHub Releases](https://github.com/iLLixM/dolibarr-member-subscription-massaction/releases) for available installation packages, or build an installation ZIP directly from source.

## What it does

- Select members and preview their contribution amounts, membership periods, and any overlaps before confirming. Dolibarr applies its normal membership end-date rules, including retaining a later end date when applicable, and records configured Agenda events.
- Optionally create and validate a customer invoice. Reuse the linked third party, or allow the module to create and link one from member data when invoicing requires it and the operator has permission.
- Generate invoice PDFs after confirmed database completion. Send optional confirmation emails through Dolibarr and attach the invoice PDF only after verifying the file.
- Report each member’s booking, invoice, PDF, email, and attachment outcomes separately. The default batch limit is 100 members. Administrators can configure it, and a lower Dolibarr mass-action limit takes precedence.

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
| `CoreGuarded` | Oracle MySQL | Suitable for environments without Performance Schema access, including restricted hosting. Requires supported PHP file sessions, Dolibarr file logging and REPEATABLE READ transaction isolation. |
| `EnhancedMySQL` | Oracle MySQL | Requires enabled Performance Schema transaction instrumentation, corresponding read permissions and REPEATABLE READ transaction isolation. |
| `MariaDBSnapshot` | MariaDB | Uses sampled connection and transaction state to provide transaction safeguards. |

Compatibility and qualification details for individual releases are available in the corresponding release and project documentation.

## Transaction safety

The module processes each member's financial operations within an individually guarded database transaction.

Database-specific transaction monitoring helps detect unexpected transaction states and prevents further bookings when database completion is uncertain.

All three transaction profiles support the same business functionality while using monitoring mechanisms appropriate to their database system and hosting capabilities.

Database transactions, PDF generation and email delivery are handled as separate effects. Uncertain outcomes require reconciliation rather than automatic retries.

For technical details, see the [transaction architecture](docs/adr/ADR-0001-core-first-transaction-profiles.md).

## Install and configure

1. Verify the SHA-256 of the installation ZIP against its `SHA256SUMS`, then check your Dolibarr installation for a collision with the unreserved module ID `584620`.
2. Import the verified ZIP via **Setup → Modules/Applications → Deploy/install external module**. For a manual installation, extract the archive’s `membersubscriptionmassaction/` directory into your configured Dolibarr custom modules directory.
3. Enable **Members** and **Member Subscription Massaction**. Open the module setup and select the matching transaction profile.
4. Use internal Dolibarr accounts with permission to read members and create subscriptions. Grant the applicable third-party and invoice permissions for invoicing, including creation, validation, and sending where required by the selected options and Dolibarr’s permission settings.
5. For invoicing, enable **Third Parties** and **Customer Invoices**, configure invoice numbering and a PDF model, and ensure Dolibarr can write to its document storage. For email, configure Dolibarr’s mail transport and a valid sender, and verify the intended recipients.

## Book subscriptions

1. Open **Members**, select eligible members, and choose **Create member subscriptions (bulk)**.
2. Review the read-only preview. Check dates, amounts, and invoice, customer-creation, and email options. Review and confirm each intended overlap.
3. Confirm the run once and review the per-member results. If the batch stops, members marked **Not processed** were never reached.

## Operating limits

Use this version with a single active Dolibarr business entity and consistent member, customer, invoice, and document access. MultiCompany and cross-entity business sharing are outside the qualified scope. The module creates neither standalone third parties nor payments or bank entries.

Each member has a separate database transaction. PDF generation and email are separate effects. If the database outcome becomes uncertain, the module stops the batch. Later PDF or mail uncertainty follows the selected profile’s safeguards. For example, `CoreGuarded` stops on an uncertain PDF outcome and continues after uncertain mail only when it can establish that the problem is isolated. A transport handoff records acceptance by the mail transport. Delivery still needs to be checked separately.

If a result is **Unknown** or the run stops unexpectedly, check saved contributions, membership dates, invoices, documents, and mail evidence before taking further action. The module performs no automatic retries, resends, or compensation. A database rollback may leave filesystem effects, such as changes to provisional invoice folders.

## Build the installation ZIP

The module includes a deterministic build script for creating an installable Dolibarr package from committed source code.

### Requirements

- Git
- Python 3

### Build

Clone the repository:

```bash
git clone https://github.com/iLLixM/dolibarr-member-subscription-massaction.git
cd dolibarr-member-subscription-massaction
```

To build a specific release, check out its corresponding Git tag before proceeding. Available release versions can be found on [GitHub Releases](https://github.com/iLLixM/dolibarr-member-subscription-massaction/releases).

Build the currently checked-out source:

```bash
python3 tools/build-release.py "$(git rev-parse HEAD)" ../msm-build
```

The output directory must not already exist. It will contain:

- The installable module ZIP, named according to the module version.
- `SHA256SUMS`, containing the package checksum.

The build script also writes a JSON build report to standard output.

Verify the package checksum:

```bash
(cd ../msm-build && sha256sum -c SHA256SUMS)
```

The builder uses committed Git contents and does not include uncommitted working-tree changes, development tests or internal documentation.

For production installations, prefer a published release or a package built from its corresponding release tag. Builds from the development branch may contain unreleased changes.

Install the generated ZIP using Dolibarr's external module installation function.

GitHub's automatically generated source code archives are repository snapshots, not installable Dolibarr module packages.

## License and documentation

Licensed under GPL-3.0-or-later. See [COPYING](COPYING), the [installation and operating guide](docs/distribution/README.md), the [documentation guide](docs/documentation-guide.md), and [Contributing](CONTRIBUTING.md).
