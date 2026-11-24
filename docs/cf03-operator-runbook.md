# CF-03 – Operator runbook for stages 0/1 (A–D)

## Current operator safety STOP – post-PR #38 (2026-11-24)

**Authoritative post-PR #38 state (2026-11-24):** [PR #38](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/38) was independently reviewed and **squash-merged** into `main` as `0998fbaf58155cca04d31ab99c6bb47420e53c72`; reviewed HEAD `b15b93580f995fb05a696a125c2a5fd995dd4bb3` has **identical Git tree** `a6e7256fb1572361c4dd4d850124ca7a6afff566`. The independent verdict was **APPROVED FOR DEVELOPMENT MERGE WITH DOCUMENTED LIMITATIONS**, with exact-source Standard 9/9 + Full 21/21 + Hosted 1/1 (**31/31**) synthetic CI PASS. Phase 4 development is merged; **Phase 5 real A–D integration, CF05-07, package, hosted and Owner release acceptance are NOT complete**. All older dated DRAFT/finance-default-deny/core-source-allowlist statements below are historical and not current requirements.

**Current dolienv topology:** A = local Dolibarr 24.0.3/PHP 8.2.35/MariaDB 10.11.19 `MariaDBSnapshot`; B = local Dolibarr 24.0.3/PHP 8.2.35/MySQL 8.4.13 `CoreGuarded`; C = same Dolibarr/PHP/MySQL versions `EnhancedMySQL`; D = hosted Dolibarr 22.0.5/MySQL 8.4.13/CLI PHP 8.3.37 `CoreGuarded`. A/B/C have separate schemas, webroots, accounts, sessions, document roots; B/C share MySQL server and local module source only. Legacy CF03 A/B labels and older shared A/C commands cannot be relabeled/reused. A/C limited invoice/PDF/R16/replay positive evidence was on prior HEAD `46f78d8...`; D has read-only hosted evidence; no exact-current-source full real finance qualification. [Canonical matrix](dolienv-environments.md).

**All historical commands below are archival templates and are NOT executable authorization**; old A=CoreGuarded/B=MariaDB mapping and A/C shared-schema assertions were superseded by new dolienv. Private paths, DB users, credentials, installed checkouts and actual target-user environment variable transfer must be resolved **outside Git**. Validate canonical env-to-source/DB/schema/profile binding with a read-only preflight using a protected least-privilege launcher (F32-MR-01). No global `sudo -E` forwarding or public test artifacts. Schedule B/C source/DB access deliberately.

**Independent disposition:** F38-01–06 `CLOSED`; F38-07–09 `SUPERSEDED BY OWNER VARIANT B` (not technically closed); F38-10–13 `INDEPENDENTLY CLOSED`; F38-14/P2 `INDEPENDENTLY CLOSED` (server-confirmed `manualend` restored only after fresh fingerprint revalidation); F36-01 historically closed. **Separate real incident:** `DOLIENV-ACC-01` in B remains `UNKNOWN/STOP`, diagnostic subscription #3 and rollback unconfirmed; lack of a persisted record does not prove rollback ACK. No replay/compensation. The corrected code has **not been retested in real B finance**.

**Incident rule:** original B `DOLIENV-ACC-01` is UNKNOWN/STOP (subscription #3, rollback unconfirmed); independent readback no persistent delta does not prove rollback ACK. Do **not** rerun/compensate or claim final success. Any new B finance fixture must be explicitly authorized and separately reconciled. No new A–D real writes/SMTP/deployments under this documentation PR.

**Operational/release boundary:** `F32-MR-01` protected private target-user launcher and read-only preflight, independently reconciled **new** one-shot A–D CF03-NORMAL cases (priority B finance, never replay old UNKNOWN), per-case Owner write GO, controlled snapshots/backups and restore readiness, actual module mail/verified attachments, `CF05-07` risk acceptance, `R0-03` exact v0.1.0 ZIP/SHA256, `R0-04` hosted D package/Web-FPM/business qualification, `R0-07` independent final acceptance and separate Owner Release GO remain **OPEN / NO-GO**. SMTP-capture probes are infrastructure-only, backup integrity is not restore, and CF03-FAULT destructive cases remain DEFERRED/NOT EXECUTED. No real writes authorized by this documentation.

---

> **Archived date-bound content below:** previous sources/labels/admission claims reflect their original times; the post-PR38 section above governs current status.

> **Current environment convention — 2026-11-24:** dolienv **A = MariaDBSnapshot**,
> **B = CoreGuarded**, **C = EnhancedMySQL**, **D = hosted CoreGuarded**.
> [Current topology, bounded acceptance and privacy rules](dolienv-environments.md) control
> environment naming/readiness. Earlier CF03 labels, shared A/C bindings and dated
> evidence below retain their historical meaning; do not relabel/replay old tools.
> Current B finance acceptance is **BLOCKED (UNKNOWN/STOP)**; D has read-only
> browser/hosting-CLI evidence, not business-write qualification.


> **Latest operator STOP after PR #36 merge (2026-11-21):** [PR #36](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/36) merged as `d0b15785c60b74aa7ad59a1c687db94193d159c6` after `APPROVED FOR DEVELOPMENT MERGE` on `33cb3da3b389f9df2870bc8c6223c86a959cb467`. CoreGuarded invoice/autocreate/sendmail is **production-default-denied** despite implementation. **F36-02/P2 OPEN / CF05-06 REQUIRED**: V22/V24 `Facture::validate()` conditional `PROV` directory rename before outer DB commit must be qualified before any finance unlock. This runbook authorizes **no new real invoice/auto-customer/PDF/mail or ZIP finance test**, no replay, and no circumvention via admin, CLI or test fixtures. Existing F32-MR-01 private launcher/read-only preflight and serial A/C requirements remain. Begin with [source-only CF05-06 plan](cf05-06-standard-core-qualification-plan.md), not historical commands.

> **Current operator restriction after PR #32 merge (2026-11-21):** F32-MR-01/P2 was **Owner-accepted as an operational limitation, NOT fixed**. The A/B/C examples below export private `CF03_*` in a calling shell but use `sudo -u www-data`; ordinary `sudoers env_reset` can strip them and cause fail-closed `CF03MissingEnvironmentParameter`. **Do not run new real A/B/C witness cases using those examples** until a separately protected launcher sets **only approved required environment variables in the actual target PHP-CLI user context** and passes an Owner-observed **read-only preflight**. Never commit the launcher/private values, put DB passwords into arguments or enable blanket environment forwarding. Source/head must be pinned and A/C operated serially. No launcher smoke PASS is currently recorded.

> The independent review's F32-MR-02/P3 was separately **Owner-accepted as a historical documentation deviation**. Normative filesystem boundary: canonical readable/writable parent with **no group/other write**, A/B/C root from `CF03_EVIDENCE_ROOT`, case 0700 and file 0600; root/site/checkout exclusion A–D, operator review of aliases, ancestors and mounts. [PR #32](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/32) is **squash-merged**; earlier Draft/runbook approval instructions are historical. See [current plan](cf03-qualification-plan.md) and [development baseline](current-development-baseline.md). These notes do not authorize further real bookings, fault injections, rollback tests or fixture reuse.

> **Development update after PR #34 (2026-11-21):** Shared active Booking→Invoice guard CF05-01/02 is **implemented / reviewed / merged** as `dc7a172bfd476467d7980cbf16a2657cfc1e729c` ([PR #34](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/34)); **no new CF03 real normal-case, invoice/customer/PDF/mail authorization, private launcher PASS or exact D ZIP integration is implied**. The F32-MR-01 A/B/C launcher/read-only-preflight STOP above stays mandatory; CF05-03..07 and full CF03-NORMAL A–D remain pending. Historical Stage-1 examples below are not instructions to replay consumed one-shot cases.

**This runbook is read-only.** The original statement that no live test had run is historical: **POS02-A1/A2 in environment A later completed TECHNICAL PASS** on source/test HEAD `e7b911b707508e439f9acbfad43f5fd55d34cc44` ([documentation](evidence/cf03/CF03-POS02-A1-A2-technischer-Abschlussbericht-2026-11-21.md)). **No additional real booking or invoice is authorized by this document**, and no application database user or transaction profile is changed by the provided commands. Only an authorized operator can operate the lab. Source attestation covers the bounded deterministic module production-PHP inventory and the selected version-specific Dolibarr Core runtime dependency manifest; preserve the reported hashes alongside the config fingerprint, without claiming a complete ZIP/Core-tree audit. Stage 2/3/4 business and controlled destructive cases require separately approved case-specific procedures.

Tools: `tools/cf03-lab.php` and `tools/cf03-lab.lib.php`. They do **not** reuse or modify the historical POS01-A/B/C evidence. The standard CF-02 positive witness remains unchanged.

## 0. Source and operator prerequisites

**Private operator setup (not committed):** export `CF03_WORKTREE` with the actual local checkout path. For A/B/C, export `CF03_EVIDENCE_ROOT` to the actual previously provisioned trusted evidence parent (do not commit its value). For A, export `CF03_A_DOCUMENT_ROOT`, `CF03_A_DATA_ROOT`, `CF03_A_DB_HOST`, `CF03_A_DB_PORT`, `CF03_A_DB_NAME`, `CF03_A_DB_USER`, `CF03_A_WEB_URL`. For B, export the corresponding `CF03_B_*` fields (including `CF03_B_DB_USER` if not passed as `--db-user`). For C, use the same A webroot/schema/host with the explicitly different `CF03_C_DB_USER`. Never commit actual values; use a locally protected launcher with restricted permissions, and run as the effective PHP CLI identity. The witness stops if required values are missing. Versions/profiles remain pinned and cannot be overridden by those variables.



Use the new CF-03 worktree from the GitHub PR. Verify the exact checked-out 40-character SHA and installed module source. For A/B/C, the PHP tool requires the live module to resolve to this exact worktree; **D additionally permits a separately deployed module copy only when the complete selected production-PHP inventory and SHA-256 values match this checkout**. This is not a complete package-manifest or install audit. Complete the entire baseline–manual-operation–verify window on the same SHA and config; no Git checkout, module update, profile switch, PHP-process credentials change, or concurrent business writes in between.

```sh
cd ${CF03_WORKTREE:?set_private_worktree_path} || exit 1
git rev-parse HEAD
git status --short
/usr/bin/php -v
/usr/bin/php test/unit/cf03-lab.php
```

The source checkout is selected exclusively through the operator-owned `CF03_WORKTREE`. The tool only accesses the current checkout, the Dolibarr `conf.php` for the selected local environment and one independent read-only mysqli connection. It does not log DB passwords, CSRF tokens or cookies.

All operator calls should use the same trusted PHP binary and the actual Apache/PHP execution identity where possible. The POS01-C reference used `sudo -u www-data` with PHP 8.2. Do not treat a PHP-CLI check as proof that the Apache worker has already reloaded credentials. Privileged output directories must be created/owned in advance by the operator, not by a web-accessible process.

## 1. Independent read-only environment preflight

### A — restricted MySQL V24 (existing reference)

The Owner must manually configure the restricted schema-DML application DB login in the site `conf.php`, choose `CoreGuarded` through module Setup, and ensure no old PHP request/connection still uses C's credentials.

```sh
sudo -u www-data /usr/bin/php -d xdebug.mode=off \
  tools/cf03-lab.php --preflight --environment=A
```

Expect `status=PASS`, profile `CoreGuarded`, effective MySQL account starting with `<RESTRICTED_DB_USER>@`, server `8.4.13`, `performance_schema=select_denied_1142`, and no data writes. A STOP means no booking and no account auto-fix.

### B — MariaDB V24

The exact DB user (without password) comes from the operator's own `<PRIVATE_MARIADB_V24_DOCROOT>/conf/conf.php`.

```sh
sudo -u www-data /usr/bin/php -d xdebug.mode=off \
  tools/cf03-lab.php --preflight --environment=B \
  --db-user=REPLACE_WITH_ACTUAL_B_DB_USERNAME
```

Expect `MariaDBSnapshot`, 10.11.19, `performance_schema=not_required`. Missing/invalid profile, wrong username, missing InnoDB table or wrong version => STOP.

### C — EnhancedMySQL V24 (SAME site and database as A)

**Never run A and C concurrently.** Finish A's entire test window first, including its one-time verify and evidence preservation. Only then, during a controlled maintenance period:

1. Ensure no web/CLI business operation or pending confirmation is running.
2. Owner manually changes the DB username/password in the shared `<PRIVATE_MYSQL_V24_DOCROOT>/conf/conf.php` to the independently approved EnhancedMySQL account.
3. Ensure new Apache/PHP connections actually use that configuration.
4. Owner explicitly selects `EnhancedMySQL` via module Setup (never edit the `llx_const` row directly). Old confirmation sessions are stale.
5. Run the preflight with the actual Enhanced username.

```sh
sudo -u www-data /usr/bin/php -d xdebug.mode=off \
  tools/cf03-lab.php --preflight --environment=C \
  --db-user=REPLACE_WITH_ACTUAL_C_ENHANCED_DB_USERNAME
```

Expect profile `EnhancedMySQL`, the different C account and `performance_schema=select_allowed` in the **same schema** `<PRIVATE_MYSQL_SCHEMA>`. This establishes SELECT permission only; it **does not** establish enabled event consumers, actually advancing event IDs, savepoint/release counters or end-event continuity. Qualify the real active observer separately before any invoice business action.

If returning to A, repeat the manual inverse switch, ensure a fresh effective limited user/profile and execute A preflight again.

### D — hosted MySQL V22.0.5

The Owner must provide the actual host/port/schema/account, document root and install source; no values are guessed. The expected DB release is MySQL 8.4.13. `--document-root` must point to `.../htdocs` (not the parent directory).

**V22.0.5 version source correction (2026-11-20):** The pinned V22.0.5 Core defines `DOL_VERSION = '22.0.5'` inside `htdocs/filefunc.inc.php`; there is no `htdocs/version.inc.php`. The initial D preflight incorrectly required the V24 file, returning `CF03WrongDolibarrRoot` before DB activity. The version-aware implementation now requires and hashes `filefunc.inc.php` for D; V24 continues to require and hash `version.inc.php`. The initial Owner-reported D STOP is a **preflight tool finding, not a V22 qualification FAIL**, and remains historical. Verify the fixed script's new SHA/CI before re-running a read-only preflight.

Shared hosting may have no `sudo` and may expose a *shell-logical* document root different from the real host path shown by the Dolibarr web administration screen. Use the path actually accessible to **CLI PHP's `realpath()`**, with readable `conf/conf.php` and `filefunc.inc.php`. Never modify `conf.php` to fake a web-visible root.

```sh
/usr/bin/php -d xdebug.mode=off \
  tools/cf03-lab.php --preflight --environment=D \
  --document-root=/ACTUAL/PATH/TO/V22/htdocs \
  --db-host=ACTUAL_DB_HOST --db-port=ACTUAL_PORT \
  --db-name=ACTUAL_SCHEMA --db-user=ACTUAL_RESTRICTED_DB_USER
```

Expect actual Dolibarr 22.0.5 source, actual CoreGuarded profile in entity 1, InnoDB, autocommit 1, NO_CHAIN, denied P_S SELECT 1142. The full ZIP packaging/manifest and install validation, permission configuration and business tests remain later separate cases. A D preflight can attest a deployed module copy only when its selected product file hashes match the current CF-03 checkout; it does not require the ZIP to ship test tools. No controlled destructive fault is authorized on a shared hosting provider. **Warning:** this fixes only the early V22 version-file assumption; DB settings, source-copy hashes, P_S denial, and a private hosting-safe evidence directory remain separate checks and may surface their own STOP conditions.

## CF03-W01 hardened evidence and invoice operator prerequisites

The W01 library requires an **explicit operator-owned `CF03_EVIDENCE_ROOT` for A/B/C** and an existing canonical, non-symlink evidence root readable and writable by the effective PHP CLI user, **without group/other write permission**. A/B/C use the explicitly configured private `CF03_EVIDENCE_ROOT` parent. D's `--baseline` and `--verify` require **both** `--evidence-root=/ACTUAL/SAFE/PATH` and `--evidence-dir=/ACTUAL/SAFE/PATH/cf03-d-unique-case-01` with identical values per call. **The root has no mandatory ownership or mode-0700 check; group/other read permissions are accepted, but group/other write permissions are rejected.** The tool still creates the individual case directory at 0700 and its evidence files at 0600 and preserves strict one-shot integrity checks. For D, the root must remain outside the webroot/checkout and their ancestors; hosting operators must separately exclude HTTP alias/public backup exposure. The tool does not create or chmod/chown the parent or switch accounts. Evidence options remain forbidden for `--preflight` and `--check-only`. **Security requirement:** the tool rejects group/other-writable parents because untrusted users could replace private case entries. Hosting ACLs/mount permissions and public aliases require additional operator assessment.

**Only after specific Owner authorization of a real business case**: check-only PASS → baseline once → one authenticated UI action → verify once. Verification enforces exactly the two original baseline files, 0600 file modes, checksum integrity, stable DB/profile/conf/module/Core/SQL schema context, and creates `verify.started` before readback. No replay or cleanup even after a partial write.

For `--scenario=invoice-existing` (B/C only), supply the actual UI-confirmed `--invoice-date`, `--invoice-quantity`, `--invoice-product`, `--invoice-vat`, `--invoice-term`, `--invoice-payment-mode`, `--invoice-bank` and `--invoice-currency`. Missing/invalid fields STOP. This bounded witness requires exactly one linked validated invoice/line and checks the selected payment/customer, amount, VAT and attribution to `BILL_CREATE`/`BILL_VALIDATE` Agenda actions. An enabled `LINEBILL_INSERT` Agenda action STOPS until a separate line-specific witness is qualified; do not reconfigure legitimate events to bypass it.

See [CF03-W01 implementation and assurance limits](cf03-w01-validation.md). The operator is **not authorized to book an invoice or contribution solely by the existence of these CLI options**.

### Phase 1 shared evidence-root security delta (2026-11-21)

The parent policy allows read/traverse but forbids group/other write. In
**all four** environments, the evidence root must be strictly outside the
Dolibarr webroot/site root and outside the checked-out source (neither
ancestor nor descendant). The operator must separately exclude aliases,
symlinks in untrusted path ancestors, network mounts and HTTP exposure;
ordinary path checks cannot prove arbitrary web-server routing.

Evidence creation uses a temporary 0077 process umask. Each new file is
checked as private BEFORE any contents are written, and the previous
umask is restored after opening. The existing mode 0600 file and mode
0700 case-directory conditions and one-shot/no-retry guard remain.
No existing case, in particular POS02-A1/A2 or RB01-A, may be reused.
This documentation does not approve further live execution.

## Paket A gate — accessible evidence parent and private one-shot case

Current contract for **A/B/C/D**: the already existing root must be canonical, non-symlink, and **effectively readable and writable** by the CLI user; no root owner/mode-0700 restriction is enforced, but group/other write is forbidden. A/B/C use the operator-configured `CF03_EVIDENCE_ROOT`; D supplies an explicit root and retains the no-web-exposure restrictions. **The newly created case directory and every evidence file remain owner-restricted (0700 / 0600), with exclusive writes and single-use verify.** The Owner provisions and assesses the parent separately; the tool never modifies it.

A valid, admitted case's `--verify` now creates `verify.started` **before database connection, profile inspection or source hash checks**. After that point, any STOP is terminal for the Case ID, including database or configuration failure; when possible `verdict.json` carries the STOP, but the exclusive marker remains authoritative. No retry, file deletion, directory replacement, rebooking or automatic cleanup. An invalid baseline is not admitted as a valid case.

**Historical Paket A restriction (superseded):** Later independent reviews approved W01 test-tool admission on pinned HEAD `3511175fc33489e7e8e89e4ae13f36db8e87aa62` under operator-specific conditions. Any subsequent code change needs a fresh SHA-specific review and separate Owner authorization before a real case.

## Paket B operator requirements — source and actor-bound invoice/Agenda

The preflight now evaluates a wider deterministic PHP source inventory, and each case binds the configured product (invoice only) plus the real authenticated operator (Agenda ON). For **every** positive `invoice-existing` case, specify `--actor-id=<ACTUAL_AUTHENTICATED_DOLIBARR_USER_ROWID>` regardless of Member-Agenda ON/OFF; for positive `contribution`, specify it only with Member-Agenda ON. Omit the option for contribution Agenda OFF and for no-change. The ID must identify the authenticated browser operator executing exactly one approved action, and must stay fixed through check-only/baseline/verify. Invoice-existing on B/C uses the actual configured `ADHERENT_PRODUCT_ID_FOR_SUBSCRIPTIONS`, product type (0 goods / 1 service), existing eligible customer (1 or 3), and `--invoice-quantity=1` only. Do not invent an invoice product or change legitimate settings merely to produce a PASS.

At preflight and each baseline/verify readback, module PHP files and selected Dolibarr V22/V24 Core critical-path files are hashed and compared. A hosted D deployment may be a checkout, not an installed ZIP, and this hash contract alone does not attest the complete ZIP. Independent W01 technical admission has been granted on the original pinned SHA only; actual real-case execution remains conditional on current-HEAD admission, fresh preflight, private evidence and explicit case-specific Owner approval.

## F32-W01-08/-11 — independently configured invoice Agenda (independently CLOSED on reviewed HEAD)

For `--scenario=invoice-existing` (B/C only), supply **`--actor-id=<ACTUAL_AUTHENTICATED_DOLIBARR_USER_ROWID>` on check-only, baseline and verify regardless of `--agenda=on/off`**. The latter option means **member subscription Agenda ON/OFF only**. Separately configured Core `BILL_CREATE`/`BILL_VALIDATE` events are read and reconciled independently, including the mixed case member OFF + invoice VALIDATE ON. The selected invoice Resultset must contain event entity, author and owner; missing columns STOP before attributing an event.

Do not disable legitimate Core invoice Agenda flags to force a PASS; `LINEBILL_INSERT` effective ON remains a documented early Admission STOP for the bounded invoice witness. Check-only must confirm a new synthetic fixture, actual authenticated actor, effective agenda flags and relevant source/profile/DB identity before the Owner performs any single UI action.

The independent final delta review subsequently CLOSED F32-W01-08 and F32-W01-11 on `3511175fc33489e7e8e89e4ae13f36db8e87aa62` and admitted bounded B/C invoice-existing witness use under the prescribed operator conditions. A changed HEAD is **not** automatically approved: require independent follow-up of the new delta and explicit Owner case authorization. PR #32 remains Draft; no real W01 bookings have yet been executed.

## 2. Stage-1 snapshot witness (not an authorization to run a booking)

`--check-only`, `--baseline`, and `--verify` additionally require a freshly created synthetic member. The following *generic* A example uses placeholders and is subject to the documentation argument-regression test; replace with values of an **entirely new** member (do not reuse 56, 57, 58 or a historical CF02 marker).

<!-- CF03-CASE-A -->
```sh
MEMBER_ID=REPLACE_WITH_NEW_NUMERIC_ID
MEMBER_REF=REPLACE_WITH_CORE_REFERENCE
ACTOR_ID=REPLACE_WITH_AUTHENTICATED_UI_ROWID
MARKER=CF03LAB-A-20261120A1
EVIDENCE_DIR=<PRIVATE_EVIDENCE_ROOT>/cf03-a-20261120a1

common() {
  sudo -u www-data /usr/bin/php -d xdebug.mode=off \
    tools/cf03-lab.php "$@" --environment=A \
    --scenario=contribution \
    --member-id="$MEMBER_ID" --member-ref="$MEMBER_REF" \
    --marker="$MARKER" --start=2080-01-01 --end=2080-12-31 \
    --amount=120.00 --agenda=on --actor-id="$ACTOR_ID"
}
common --check-only
```

Do **not** execute `--baseline` and a business action merely because this example exists. The later Stage-2/3 authorization must first name exact fixture, expected UI option settings, intended DB writes, permitted method, browser acknowledgement capture, failure protocol and immutable directory.

When authorized, sequence:

1. **Read-only check-only PASS.**
2. **Read-only baseline PASS once**: `common --baseline --evidence-dir="$EVIDENCE_DIR"`.
3. **Owner executes exactly one separately authorized authenticated UI action**. Never have the CLI perform any booking.
4. **Read-only verify once**: `common --verify --evidence-dir="$EVIDENCE_DIR"`.
5. Preserve `baseline.json`, `baseline.sha256`, `verify.started`, `after.json`, `verdict.json` privately. No rerun, cleanup or result rewrite after STOP.

For B and C, use `--environment=B/C` and the corresponding `--db-user` as in Step 1, with a **new environment-specific marker and directory**. For D, use the complete Step-1 root/host/port/schema/user parameters in every witness call. C/A must not change account/profile between baseline and verify; source hashes and `conf.php` hash are verified. Only `--scenario=contribution` and `--scenario=no-change` apply to CoreGuarded. `--scenario=invoice-existing` is restricted to B/C, needs an existing synthetic linked customer, and provides **only selected invoice-row/line/link evidence**, not full invoice/Agenda/PDF/SMTP qualification. Never mix case profiles, fixture IDs or evidence directories.

## 2.1 Contract-validated B/C Invoice-existing operator pattern

For B and C, use **one case at a time** with the actual profile and application DB user. C must follow the documented A/C switch and verified active EnhancedMySQL instrumentation; do not reuse any earlier case directory or marker. Populate values from a new *synthetic* member and its existing eligible customer. The chosen invoice plan must match the actual UI/module configuration (one invoice line, quantity 1); do **not** change genuine Agenda settings just to pass. `LINEBILL_INSERT` effective ON remains an admission STOP.

<!-- CF03-CASE-INVOICE -->
```sh
ENVIRONMENT=B                   # or C, only after exclusive switch
DB_USER=REPLACE_WITH_ACTUAL_B_OR_C_DB_USER
MEMBER_ID=REPLACE_WITH_NEW_NUMERIC_ID
MEMBER_REF=REPLACE_WITH_CORE_REFERENCE
ACTOR_ID=REPLACE_WITH_AUTHENTICATED_UI_ROWID
MARKER=CF03LAB-B-20261121B1     # environment-specific new marker (C if C)
INVOICE_DATE=REPLACE_WITH_CONFIRMED_INVOICE_DATE
INVOICE_PRODUCT=REPLACE_WITH_CONFIRMED_PRODUCT_ID_OR_0
INVOICE_VAT=REPLACE_WITH_CONFIRMED_VAT_RATE
INVOICE_TERM=REPLACE_WITH_CONFIRMED_PAYMENT_TERM_ID
INVOICE_PAYMENT_MODE=REPLACE_WITH_CONFIRMED_PAYMENT_MODE_ID_OR_0
INVOICE_BANK=REPLACE_WITH_CONFIRMED_BANK_ID_OR_0
INVOICE_CURRENCY=REPLACE_WITH_CONFIRMED_CURRENCY
EVIDENCE_DIR=<PRIVATE_EVIDENCE_ROOT>/REPLACE_WITH_UNIQUE_CASE_DIR
invoice_case() {
  sudo -u www-data /usr/bin/php -d xdebug.mode=off tools/cf03-lab.php "$@" \
    --environment="$ENVIRONMENT" --db-user="$DB_USER" \
    --scenario=invoice-existing --member-id="$MEMBER_ID" \
    --member-ref="$MEMBER_REF" --marker="$MARKER" \
    --start=2080-01-01 --end=2080-12-31 --amount=120.00 \
    --agenda=off --actor-id="$ACTOR_ID" \
    --invoice-date="$INVOICE_DATE" --invoice-quantity=1 \
    --invoice-product="$INVOICE_PRODUCT" --invoice-vat="$INVOICE_VAT" \
    --invoice-term="$INVOICE_TERM" --invoice-payment-mode="$INVOICE_PAYMENT_MODE" \
    --invoice-bank="$INVOICE_BANK" --invoice-currency="$INVOICE_CURRENCY"
}
invoice_case --check-only
```

Before any `invoice_case --baseline --evidence-dir="$EVIDENCE_DIR"` and authenticated browser action, obtain explicit per-case Owner approval. After the single approved action, invoke `invoice_case --verify --evidence-dir="$EVIDENCE_DIR"` **once**. Invoice-specific Agenda flags are independent of `--agenda=off`, which concerns only the Member trigger. For a Member-Agenda ON invoice case, use `--agenda=on` without dropping the mandatory actor.

## 2.2 Contract-validated hosted D contribution pattern

This is a **placeholder** pattern, not a disclosure of hosting credentials or an authorization to book. The pre-provisioned evidence **root** must be effectively readable/writable by the CLI PHP user, with canonical non-symlink path outside all public HTTP paths; parent owner/mode are not checked. Each **case directory** and its files remain 0700/0600. Hosting operators must attest no alias/backup exposure or untrusted-writer replacement risk. The source-checkout/preflight evidence does **not** prove ZIP installation.

<!-- CF03-CASE-D -->
```sh
DOCUMENT_ROOT=/ACTUAL/HOSTED/V22/htdocs
DB_HOST=REPLACE_WITH_ACTUAL_DB_HOST
DB_PORT=3306
DB_NAME=REPLACE_WITH_ACTUAL_DB_SCHEMA
DB_USER=REPLACE_WITH_ACTUAL_DB_USER
MEMBER_ID=REPLACE_WITH_NEW_NUMERIC_ID
MEMBER_REF=REPLACE_WITH_CORE_REFERENCE
ACTOR_ID=REPLACE_WITH_AUTHENTICATED_UI_ROWID
MARKER=CF03LAB-D-20261121D1
EVIDENCE_ROOT=/ACTUAL/PRIVATE/EVIDENCE
EVIDENCE_DIR="$EVIDENCE_ROOT/cf03-d-unique-case-01"
hosted_case() {
  /usr/bin/php -d xdebug.mode=off tools/cf03-lab.php "$@" \
    --environment=D --document-root="$DOCUMENT_ROOT" \
    --db-host="$DB_HOST" --db-port="$DB_PORT" \
    --db-name="$DB_NAME" --db-user="$DB_USER" \
    --scenario=contribution --member-id="$MEMBER_ID" \
    --member-ref="$MEMBER_REF" --marker="$MARKER" \
    --start=2080-01-01 --end=2080-12-31 --amount=120.00 \
    --agenda=on --actor-id="$ACTOR_ID"
}
hosted_case --check-only
```

Only after successful private-root/HTTP-exposure checks and separate Owner admission: `hosted_case --baseline --evidence-root="$EVIDENCE_ROOT" --evidence-dir="$EVIDENCE_DIR"`, one permitted UI action, then `hosted_case --verify --evidence-root="$EVIDENCE_ROOT" --evidence-dir="$EVIDENCE_DIR"` exactly once. No retry or cleanup after STOP.

## RB01-A — real rollback qualification (preparation only)

The existing `--scenario=no-change` snapshot is **read-only selected-state evidence, not proof of a physical rollback or that Core DML occurred**. A real after-Core-DML business rollback requires an **independently reviewed test-only injection build on an isolated disposable A clone**, a unique fixture, a reviewed one-shot trace, separately admitted real UI operation and fresh independent row/file readback. **No RB01-A injection harness is implemented or approved by this W01 runbook.** See [RB01-A architecture and safety contract](cf03-rb01-a-harness-design.md) and [step-by-step operator procedure](cf03-rb01-a-operator-runbook.md).

## 3. Interpretation and incident handling

- `PASS preflight`: bounded runtime/privilege/config/source inspection. **No business case ran.**
- `PASS check-only/baseline`: independent initial fixture and configuration checks; baseline writes protected files only.
- `PASS verify`: selected real persisted database rows and table-count deltas match the case; **not physical transaction event identity or HTTP acknowledgement**.
- `STOP`: preserve evidence, stop further operations, no second booking, no compensating SQL, and no account/profile auto-switch.
- Check-only/baseline/verify do not inject 1213, 1205, foreign transactions, reconnections or CHAIN/RELEASE. They are not substitute for later dedicated fault harnesses.
- Do not attach `conf.php`, passwords, database exports, tokens, cookies, real member data, host secrets or raw private snapshots to public PRs/issues. The test marker, exact source SHA, sanitized verdict and hash may be used in the review dossier.

**Operator input needed for later test scheduling:** B DB username; C Enhanced DB username; D document root, data path, URL, DB host/port/schema/user and actual PHP version. Supplying them is not a prerequisite for the repository's offline Stage-1 CI.
