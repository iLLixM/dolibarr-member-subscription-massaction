# CF-03 – Operator runbook for stages 0/1 (A–D)

**This runbook is read-only.** No live CF-03 test has been executed, no real booking or invoice is authorized by this document, and no application database user or transaction profile is changed by the provided commands. Only an authorized operator can operate the lab. Source attestation includes four actual installed Dolibarr Core file SHA-256 values; preserve them alongside the module and config hashes. Stage 2/3/4 business and controlled destructive cases require separately approved case-specific procedures.

Tools: `tools/cf03-lab.php` and `tools/cf03-lab.lib.php`. They do **not** reuse or modify the historical POS01-A/B/C evidence. The standard CF-02 positive witness remains unchanged.

## 0. Source and operator prerequisites

Use the new CF-03 worktree from the GitHub PR. Verify the exact checked-out 40-character SHA and installed module source. For A/B/C, the PHP tool requires the live module to resolve to this exact worktree; **D explicitly allows a separate module copy installed from a test ZIP, but checks matching hashes of nine runtime product PHP files against this worktree**. This is not a complete package-manifest or install audit. Complete the entire baseline–manual-operation–verify window on the same SHA and config; no Git checkout, module update, profile switch, PHP-process credentials change, or concurrent business writes in between.

```sh
cd /home/marcel/dolibarr-member-subscription-massaction-cf03 || exit 1
git rev-parse HEAD
git status --short
/usr/bin/php -v
/usr/bin/php test/unit/cf03-lab.php
```

If the project uses a different CF-03 worktree location, adjust the first `cd` line. The tool only accesses the current checkout, the Dolibarr `conf.php` for the selected local environment and one independent read-only mysqli connection. It does not log DB passwords, CSRF tokens or cookies.

All operator calls should use the same trusted PHP binary and the actual Apache/PHP execution identity where possible. The POS01-C reference used `sudo -u www-data` with PHP 8.2. Do not treat a PHP-CLI check as proof that the Apache worker has already reloaded credentials. Privileged output directories must be created/owned in advance by the operator, not by a web-accessible process.

## 1. Independent read-only environment preflight

### A — restricted MySQL V24 (existing reference)

The Owner must manually configure the restricted schema-DML application DB login in the site `conf.php`, choose `CoreGuarded` through module Setup, and ensure no old PHP request/connection still uses C's credentials.

```sh
sudo -u www-data /usr/bin/php -d xdebug.mode=off \
  tools/cf03-lab.php --preflight --environment=A
```

Expect `status=PASS`, profile `CoreGuarded`, effective MySQL account starting with `dolibarr_i105_v24_limited@`, server `8.4.13`, `performance_schema=select_denied_1142`, and no data writes. A STOP means no booking and no account auto-fix.

### B — MariaDB V24

The exact DB user (without password) comes from the operator's own `/home/marcel/dolibarr-v24/htdocs/conf/conf.php`.

```sh
sudo -u www-data /usr/bin/php -d xdebug.mode=off \
  tools/cf03-lab.php --preflight --environment=B \
  --db-user=REPLACE_WITH_ACTUAL_B_DB_USERNAME
```

Expect `MariaDBSnapshot`, 10.11.19, `performance_schema=not_required`. Missing/invalid profile, wrong username, missing InnoDB table or wrong version => STOP.

### C — EnhancedMySQL V24 (SAME site and database as A)

**Never run A and C concurrently.** Finish A's entire test window first, including its one-time verify and evidence preservation. Only then, during a controlled maintenance period:

1. Ensure no web/CLI business operation or pending confirmation is running.
2. Owner manually changes the DB username/password in the shared `/home/marcel/dolibarr-i105-v24-mysql/htdocs/conf/conf.php` to the independently approved EnhancedMySQL account.
3. Ensure new Apache/PHP connections actually use that configuration.
4. Owner explicitly selects `EnhancedMySQL` via module Setup (never edit the `llx_const` row directly). Old confirmation sessions are stale.
5. Run the preflight with the actual Enhanced username.

```sh
sudo -u www-data /usr/bin/php -d xdebug.mode=off \
  tools/cf03-lab.php --preflight --environment=C \
  --db-user=REPLACE_WITH_ACTUAL_C_ENHANCED_DB_USERNAME
```

Expect profile `EnhancedMySQL`, the different C account and `performance_schema=select_allowed` in the **same schema** `dolibarr_i105_v24`. This establishes SELECT permission only; it **does not** establish enabled event consumers, actually advancing event IDs, savepoint/release counters or end-event continuity. Qualify the real active observer separately before any invoice business action.

If returning to A, repeat the manual inverse switch, ensure a fresh effective limited user/profile and execute A preflight again.

### D — hosted MySQL V22.0.5

The Owner must provide the actual host/port/schema/account, document root and install source; no values are guessed. The expected DB release is MySQL 8.4.13. `--document-root` must point to `.../htdocs` (not the parent directory).

```sh
sudo -u www-data /usr/bin/php -d xdebug.mode=off \
  tools/cf03-lab.php --preflight --environment=D \
  --document-root=/ACTUAL/PATH/TO/V22/htdocs \
  --db-host=ACTUAL_DB_HOST --db-port=ACTUAL_PORT \
  --db-name=ACTUAL_SCHEMA --db-user=ACTUAL_RESTRICTED_DB_USER
```

Expect actual Dolibarr 22.0.5 source, actual CoreGuarded profile in entity 1, InnoDB, autocommit 1, NO_CHAIN, denied P_S SELECT 1142. The full ZIP packaging/manifest and install validation, permission configuration and business tests remain later separate cases. A D preflight can attest a deployed module copy only when its selected product file hashes match the current CF-03 checkout; it does not require the ZIP to ship test tools. No controlled destructive fault is authorized on a shared hosting provider.

## 2. Stage-1 snapshot witness (not an authorization to run a booking)

`--check-only`, `--baseline`, and `--verify` additionally require a freshly created synthetic member. The following *generic* A example uses placeholders; replace with values of an **entirely new** member (do not reuse 56, 57, 58 or a historical CF02 marker).

```sh
MEMBER_ID=REPLACE_WITH_NEW_NUMERIC_ID
MEMBER_REF=REPLACE_WITH_CORE_REFERENCE
MARKER=CF03LAB-A-20261120A1
EVIDENCE_DIR=/var/lib/dolibarr-test-results/cf03-a-20261120a1

common() {
  sudo -u www-data /usr/bin/php -d xdebug.mode=off \
    tools/cf03-lab.php "$@" --environment=A \
    --scenario=contribution \
    --member-id="$MEMBER_ID" --member-ref="$MEMBER_REF" \
    --marker="$MARKER" --start=2080-01-01 --end=2080-12-31 \
    --amount=120.00 --agenda=on
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

## 3. Interpretation and incident handling

- `PASS preflight`: bounded runtime/privilege/config/source inspection. **No business case ran.**
- `PASS check-only/baseline`: independent initial fixture and configuration checks; baseline writes protected files only.
- `PASS verify`: selected real persisted database rows and table-count deltas match the case; **not physical transaction event identity or HTTP acknowledgement**.
- `STOP`: preserve evidence, stop further operations, no second booking, no compensating SQL, and no account/profile auto-switch.
- Check-only/baseline/verify do not inject 1213, 1205, foreign transactions, reconnections or CHAIN/RELEASE. They are not substitute for later dedicated fault harnesses.
- Do not attach `conf.php`, passwords, database exports, tokens, cookies, real member data, host secrets or raw private snapshots to public PRs/issues. The test marker, exact source SHA, sanitized verdict and hash may be used in the review dossier.

**Operator input needed for later test scheduling:** B DB username; C Enhanced DB username; D document root, data path, URL, DB host/port/schema/user and actual PHP version. Supplying them is not a prerequisite for the repository's offline Stage-1 CI.
