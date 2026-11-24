# Member Subscription Massaction v0.1.0

Bulk preview and booking for Dolibarr member subscriptions. Each selected member is
processed separately. An invoice can use a linked third party or create one when
explicitly selected and authorized. PDF and optional email with an attachment follow
an acknowledged eligible commit. Payments and bank entries are outside the module.

## Install

1. Import the module ZIP in Dolibarr's external module deployment screen. For a
   manual deployment, extract `membersubscriptionmassaction/` into the configured
   custom module directory.
2. Enable Members and Member Subscription Massaction. For invoicing, configure
   Third Parties, Customer Invoices, numbering and the invoice PDF model.
3. Set the transaction profile on the module setup page. Use a role with only the
   rights needed for the selected actions. Check the status in a separate test
   instance with synthetic members before using it for bookings.

## Database profiles

- `MariaDBSnapshot` uses the MariaDB snapshot contract.
- `CoreGuarded` uses Dolibarr's transaction API with an independently persisted
  PHP file session and supported Core file logger. CHAIN or RELEASE may stop the
  batch after an acknowledged member result.
- `EnhancedMySQL` requires the MySQL Performance Schema current-transaction
  observation documented in the project.

The project targets MariaDB 10.11+, Oracle MySQL 8.4+, and Dolibarr 22–24 with a PHP
version supported by the Core installation (module minimum PHP 7.4). Check the
repository for the exact qualified combinations and limitations.

## Operation

Select members in the member list and choose **Create member subscriptions (bulk)**.
Review dates, totals, warnings and the invoice and mail options before confirming.
The module defaults to 100 members and also honors any lower Dolibarr mass-action
limit. If a booking result is unknown, reconcile it with persisted records before
taking any further action. Verify PDF and mail outcomes separately. SQL rollback
cannot undo every Core filesystem effect. Pay particular attention to provisional
invoice directory moves when reviewing a failed invoice operation.

Project documentation and issue tracking are in the
[repository](https://github.com/iLLixM/dolibarr-member-subscription-massaction).
This package is licensed under GPL-3.0-or-later. See [COPYING](COPYING).
