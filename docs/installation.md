# Installation and configuration — v0.1.0

Install on a compatible Dolibarr environment with the documented database and
PHP prerequisites. Assess the site's configuration and operating limits before use.

## Before installing

1. Check [compatibility](compatibility.md), Core/PHP/backend profile, required
   transaction instrumentation and the supported invoice options. Do not connect
   older Core code to a migrated database.
2. Check module ID **584620** against all installed Core/custom module descriptors,
   including inactive modules. Inspect descriptor `$this->numero` assignments or
   the administrator's module inventory without changing registrations. No other
   module may use the same ID; if a collision exists, stop and seek an Owner
   decision. Do not assign a replacement ID or alter rights/constants yourself.
   This community/shared ID is **not reserved**; the Owner explicitly retains it
   for the bounded release scope and accepts collision risk. No global uniqueness
   or permanent reservation is claimed.
3. Preserve existing installation data and follow the site's approved deployment
   procedure. This guide does not order backup/restore tests or overwrite an
   existing module tree. Module activation/deactivation is an explicit admin action.
4. Obtain the v0.1.0 ZIP, its `.sha256` and `PACKAGE-MANIFEST.json` from the
   same distribution. Verify the ZIP SHA-256 locally before import.

## ZIP import or manual extraction

The package filename is `module_membersubscriptionmassaction-0.1.0.zip`.
A source build writes it and its manifest/checksum to `build-output/v0.1.0/`.
Keep the numeric filename and module root for the Dolibarr external-module installer.
A checksum is an integrity check, not a release signature or independent trust decision.

In Dolibarr, use Setup → Modules/Applications → external module
installation and upload the verified ZIP. If the site disables ZIP upload, extract
the single root folder `membersubscriptionmassaction/` into `htdocs/custom/`.
The result must include:

```
htdocs/custom/membersubscriptionmassaction/subscription.php
htdocs/custom/membersubscriptionmassaction/core/modules/modMemberSubscriptionMassaction.class.php
```

Ensure Dolibarr's configured alternative module directory includes `custom` and the
web-server account can traverse/read the module files. Do not grant it blanket
write access to source code. Core document generation needs the usual separate
Documents permissions. Do not replace other module folders or a live symlink blindly.

## Activation and Core rights

Enable **Members**, then **MemberSubscriptionMassaction**. The module creates no
business tables, new permission IDs, menu or document directory. It registers the
member-list hook and its module constant; existing business records remain Core's.
Internal users require Core member read and subscription creation rights. External
users cannot run the wizard. Optional invoicing also requires Third Parties and
Invoices plus read/create and applicable advanced validation/send rights. Customer
autocreation additionally requires third-party create permission and explicit
consent. Document/customer links are still protected by the current Core rights.
The module adds no separate permissions or settings page.

## Configuration

| Setting | Effect |
| --- | --- |
| `MEMBERSUBSCRIPTIONMASSACTION_MAX_MEMBERS_PER_BATCH` | Positive integer, default 100; missing/invalid/nonpositive values fall back to 100, including previously activated modules. |
| `MAIN_LIMIT_FOR_MASS_ACTIONS` | Caps the module maximum; absent means 1000; configured invalid/nonpositive values fail closed at zero. |
| `ADHERENT_BANK_USE=invoiceonly` | Preselects invoices for a new run when rights/configuration permit; user choice remains explicit and email remains independent. |
| Membership type amount/duration; existing calendar constants | Drive individual period/amount suggestions; use the preview to review changes. |
| `ADHERENT_VAT_FOR_SUBSCRIPTIONS` / product / invoice numbering and PDF settings | Must satisfy the existing supported invoice profile; see compatibility, not every Core configuration is qualified. |
| Core company/member mail sender and template configuration | Must yield a valid sender/template/recipient before selecting email. An existing empty `ADHERENT_MAIL_FROM` may suppress a general sender fallback; the admin mail test alone does not prove subscription-mail configuration. |

Use the Core constants administration for the batch constant. No automatic
truncation/splitting is performed. A reduced limit invalidates oversized pending
confirmations; retain and investigate consumed/uncertain results instead of rebooking.

## First use and removal

Start with a few synthetic members on an approved installation. Select them in the
member list, choose the bulk subscription action and Confirm; review individual
periods, amount, description and optional invoice/customer/mail choices. Review the
separate confirmation, explicitly accept any intended overlaps, then submit once.
Inspect all per-member outcomes. PDF/mail failures and `unknown/stop` need different
handling; see [operations](operations.md).

Disabling the module removes its registration/list action without deleting Core
subscriptions, customers or invoices. Verify the site's module detection,
permissions and intended workflow with synthetic members before routine use.
The package builder itself performs no admin or business operation.
