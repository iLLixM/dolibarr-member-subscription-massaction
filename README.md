# Member Subscription Massaction for Dolibarr

Book membership contributions for several members in one run from the Dolibarr Members list. Preview dates, amounts, and options together, then use Dolibarr’s standard booking, invoicing, and document workflows for each member.

Version 0.1.0 implements the workflow below across all three transaction profiles, subject to their prerequisites. Check [GitHub Releases](https://github.com/iLLixM/dolibarr-member-subscription-massaction/releases) for published installation packages, or build the ZIP from source using the instructions below.

## What it does

- Select members and preview their contribution amounts, membership periods, and any overlaps before confirming. Dolibarr applies its normal membership end-date rules, including retaining a later end date when applicable, and records configured Agenda events.
- Optionally create and validate a customer invoice. Reuse the linked third party, or allow the module to create and link one from member data when invoicing requires it and the operator has permission.
- Generate invoice PDFs after confirmed database completion. Send optional confirmation emails through Dolibarr and attach the invoice PDF only after verifying the file.
- Report each member’s booking, invoice, PDF, email, and attachment outcomes separately. The default batch limit is 100 members. Administrators can configure it, and a lower Dolibarr mass-action limit takes precedence.

## Compatibility

| Component | Requirements and coverage |
| --- | --- |
| Dolibarr | Versions 22, 23, and 24 are the compatibility targets. Real installations were qualified on **22.0.5** and **24.0.3** in specific configurations. Version 23 has synthetic test coverage only. Version 21.0.0 is the technical minimum enforced by the module descriptor and is outside release qualification. |
| PHP | **7.4 or later** for the module. Meet the requirements of your installed Dolibarr version as well. |
| Database | **Oracle MySQL 8.4 or later** or **MariaDB 10.11 or later**, using PHP’s `mysqli` driver and InnoDB for the affected tables. |

Select the transaction profile that matches your database and hosting capabilities.

| Profile | Database | Profile prerequisites and checks |
| --- | --- | --- |
| `CoreGuarded` | Oracle MySQL | Uses Dolibarr’s transaction checks without Performance Schema access. Requires a supported PHP file-session checkpoint using a writable local directory, Dolibarr’s standard file-only logging, and a connection that allows setting the next transaction to `REPEATABLE READ`. |
| `EnhancedMySQL` | Oracle MySQL | Requires enabled Performance Schema transaction instrumentation and read access to the relevant transaction event data. Checks that the active transaction uses `REPEATABLE READ`. |
| `MariaDBSnapshot` | MariaDB | Requires access to connection and transaction state. Checks snapshots of that state at defined points during booking. |

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

You need **Git** and **Python 3**. Check out the version tag or exact commit you want to reproduce. This example uses the v0.1.0 source commit.

```bash
git clone https://github.com/iLLixM/dolibarr-member-subscription-massaction.git
cd dolibarr-member-subscription-massaction
git checkout --detach 8841d87e5198a2aced42b3caaac69b7c60138a14

python3 tools/build-release.py "$(git rev-parse HEAD)" ../msm-build
(cd ../msm-build && sha256sum -c SHA256SUMS)
```

The builder reads committed Git blobs and ignores uncommitted working-tree changes. The output directory must be new, with an existing parent directory. It contains `module_membersubscriptionmassaction-0.1.0.zip` and `SHA256SUMS`. The builder writes the JSON build report to standard output.

Install the verified module ZIP using the steps above. GitHub’s automatically generated **Source code** archives are repository snapshots and cannot be used as the installable Dolibarr module package.

## License and documentation

Licensed under GPL-3.0-or-later. See [COPYING](COPYING), the [installation and operating guide](docs/distribution/README.md), the [documentation guide](docs/documentation-guide.md), and [Contributing](CONTRIBUTING.md).
