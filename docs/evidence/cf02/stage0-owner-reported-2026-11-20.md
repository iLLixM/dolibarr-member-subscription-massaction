# CF-02 – Owner-reported Stage-0 lab evidence (2026-11-20)

**Provenance:** Owner ran the repository scripts in the separate Debian/Dolibarr
development installation and supplied the sanitized console outputs in the
project conversation. This report preserves the observed status and is
**not** an independently replayed laboratory experiment. No passwords,
session tokens or business records were supplied or stored.

**Exact tested source HEAD:** `f205f9a7c2954670fe6f0e42fb78051a9ba6fa04`.
**GitHub synthetic PR CI:** [44712305851](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/44712305851)
at the same HEAD, nine profiles completed successfully. The reported local
checks also consistently printed the exact HEAD.

## B – Synthetics with local pinned Dolibarr V24.0.3 source

Runner: `DOLIBARR_TEST_ROOT=<PRIVATE_MYSQL_INSTALLATION> sh tools/cf02-run-synthetic.sh`.

- CoreGuarded bounded adapter: **88 assertions PASS** (synthetic, not live MySQL).
- Durable file-session admission: **19 assertions PASS** (real local file I/O; no HTTP/DB).
- No-Core baseline: **141** generic contracts, **188** DB observer, **6** JS loading PASS.
- Pinned V24.0.3 synthetic contracts included calendar APIs (388 assertions),
  booking/mail (369), invoices (236), postcommit (18), terminal STOP (135 with
  operator's local monitoring), CF01 profiles (88), controller F28 (30),
  request (51), user reload (46), plus identified issue-8/version checks.
  Several runners import and repeat the same tests: these figures are **not
  an additive count** of independent assertions.
- Runner terminated normally with its `CF-02 synthetics complete` marker.

**B result:** PASS for synthetic source contracts; real business DB, mail,
Agenda, rollback and concurrent HTTP paths remain NOT EXECUTED.

## C – Restricted MySQL access / metadata (read-only)

Runner: `tools/cf02-lab-readonly.sh --check-only`.

| Observed field | Owner-provided value |
| --- | --- |
| Git HEAD | `f205f9a7c2954670fe6f0e42fb78051a9ba6fa04` |
| Server | MySQL 8.4.13, TCP localhost:3307 |
| Database | `<PRIVATE_MYSQL_SCHEMA>` |
| Effective account | `<RESTRICTED_ACCOUNT>@<PRIVATE_HOST>` |
| Roles | NONE |
| Autocommit / completion | 1 / NO_CHAIN |
| Global grants | USAGE only |
| Schema grants | SELECT, INSERT, UPDATE, DELETE only on the Dolibarr schema |
| Performance Schema | SELECT on `events_transactions_current` refused with ERROR 1142 (42000) |
| Engines | `llx_adherent`, `llx_adherent_type`, `llx_subscription`, `llx_actioncomm`, `llx_actioncomm_resources`: all InnoDB |

**C result:** PASS for these five table-engine and restricted-privilege
checks. This is not a scan of every reachable trigger or third-party table.

## D – Loaded Dolibarr Core, real restricted MySQL preflight

Runner: `sudo -u www-data env CF02_DOLIBARR_ROOT=<PRIVATE_MYSQL_INSTALLATION> /usr/bin/php -d xdebug.mode=off tools/cf02-loaded-core-entry.php --check-only`.

Owner-reported JSON:
```json
{"status":"PASS","mode":"--check-only","type":"bounded loaded-Core empty-transaction test; no business DML","failure":null,"source":"development branch","core":"24.0.3","mysql":"","completion":"NO_CHAIN"}
```

**D result:** PASS for a loaded Core / restricted MySQL admission preflight.
The `mysql` field is empty in the current script and is **not** an independent
version attestation; step C verified MySQL 8.4.13. `--check-only` does NOT
execute SET RR, Core BEGIN, COMMIT, ROLLBACK or business/Agenda DML, so none
of those behaviors is qualified by D.

## Next gated test and non-claims

- The user may run **once** the pre-existing, explicitly opted-in
  `--empty-transaction` mode of `tools/cf02-loaded-core-entry.php` against
  this same restricted-MySQL lab. It performs D-01 SET RR and **one real outer
  Core BEGIN/ROLLBACK with no business writes**. An unexpected STOP must be
  preserved without an automated retry, reconnection or foreign rollback.
- Actual contribution/Agenda DML, HTTP replay, concurrency, MySQL
  CHAIN/RELEASE end handling and real EnhancedMySQL/MariaDB invoice regression
  are **NOT EXECUTED on CF-02**.
- This evidence authorizes **neither production operation nor merge/release**.
  Independent review and Owner decisions remain separate.
