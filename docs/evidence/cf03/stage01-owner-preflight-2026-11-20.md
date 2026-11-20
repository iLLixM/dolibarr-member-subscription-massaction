# CF-03 Stage 0/1 — Owner-operated A–D read-only preflight: 4/4 PASS (2026-11-20)

**Scope:** independent evaluation of Owner-supplied CLI output for **four read-only** environment checks, after the F03-D01 Dolibarr V22.0.5 version-source fix. Original console log remains with the Owner; this is a **sanitized repository record**, not an assertion that the reviewer ran commands on the Owner's private machines. No passwords, hosted database account details, private config fingerprints or raw config contents are included.

**Pinned source:** Owner's D hosting console explicitly returned checkout HEAD `3e7d87f633813131198697591666d706f8eb64a3`. A/B/C outputs reported the **same SHA-256 for each of eleven enumerated module/tool source files**, identical to D's results. This strongly binds the observed source files across A–D; A/B/C did not separately print their git HEAD. The same nine selected deployed product-file hashes equal their respective checkout hashes in all four preflights. Standard CI at the pinned PR HEAD: [run #44815055217](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/44815055217), **9/9 PASS**, including PHP 7.4 / V22.0.5 and PHP 8.2 / V24.0.3.

## Recorded output and bounded interpretation

| Environment | Owner `--preflight` | Confirmed host/profile/runtime | Selected rights/source result |
| --- | --- | --- | --- |
| A – MySQL restricted / V24 | **PASS** | Dolibarr 24.0.3, PHP 8.2.35, Oracle MySQL 8.4.13, `CoreGuarded` | Restricted application DB account; `performance_schema` SELECT **denied with 1142**; deployed module checkout matched |
| B – MariaDB / V24 | **PASS** | Dolibarr 24.0.3, PHP 8.2.35, MariaDB 10.11.19, `MariaDBSnapshot` | Effective DB identity and configured profile recognized; selected InnoDB/source checks passed; P_S **not required** |
| C – MySQL enhanced / V24 | **PASS** | Dolibarr 24.0.3, PHP 8.2.35, Oracle MySQL 8.4.13, `EnhancedMySQL` | Same installation/schema as A but different manually configured effective DB user; P_S SELECT **allowed**; module checkout matched |
| D – MySQL hosted / V22 | **PASS** | Dolibarr 22.0.5, PHP **8.3.37**, Oracle MySQL 8.4.13, `CoreGuarded` | Hosting-restricted effective DB identity; P_S SELECT **denied with 1142**; version read from `htdocs/filefunc.inc.php`; selected module/Core sources checked |

All checks returned `writes=no_business_sql`. The chosen profiles and source/config fingerprints were checked within each respective CLI preflight, not assumed to persist across later manual A/C account or profile changes. The current active A/C profile after the final check is not inferred.

Owner ran the pure CLI contract tests locally at the prepared CF-03 source: **171 assertions PASS (no DB/HTTP)**, including on D's hosted PHP 8.3.37 environment. These runs are neither authenticated HTTP acceptance tests nor independent real database/transaction tests.

**Hashes:** All eleven `source_hashes` objects are equal across A–D. All nine selected `deployed_source_hashes` entries match the corresponding source entries for each environment and agree across A–D. A/B/C `core_source_hashes` agree across the four sampled Dolibarr 24.0.3 files; D correctly differs for the Dolibarr V22.0.5 Core and reports `filefunc.inc.php` instead of `version.inc.php`. Private `conf.php` SHA-256 values and deployment coordinates were inspected in the Owner evidence and omitted from this public summary.

**F03-D01 disposition:** Original D `STOP / CF03WrongDolibarrRoot` was caused by the Stage-1 tool requiring the V24 `version.inc.php` file on V22.0.5. The fix in the pinned PR HEAD uses V22 `filefunc.inc.php`; **D now PASS in the read-only preflight**. Preserve the original historical STOP as a resolved tool issue, not a failed Dolibarr compatibility test.

## Limits and next qualification gates

1. **D reports `deployment_kind=source_checkout`, not `verified_deployed_copy`.** This confirms the installed module resolves to the CF-03 checkout under the checker's view. It does **not** prove installing the module from a version-bound ZIP or a complete distribution-manifest audit. HOST01 remains OPEN.
2. **D uses PHP 8.3.37.** The hosting CLI successfully ran 171 offline assertions and a real read-only preflight on PHP 8.3.37, but the standard nine-profile CI matrix exercises PHP 7.4/8.2 only. Real hosted HTTP/booking compatibility remains to be tested; consider a narrowly targeted PHP 8.3/V22.0.5 regression addition.
3. A/C share **one** installed site and database, with manual sequential profile/account switching. These PASS records are **time-specific**, not proof of simultaneous independent installations. Re-run a preflight at the start of each later serialized window.
4. The EnhancedMySQL P_S SELECT PASS is **not** evidence of enabled transaction-event instrumentation or actual observer ownership/continuity. Its current-source real invoice/commit/rollback non-regression is OPEN.
5. The read-only preflight is **not** proof of physical transaction identity, actual paid contribution, linked invoice, Agenda action, PDF/email side effects, fault classification, replay, concurrency or batch behavior.
6. The existing witness evidence-parent path `/var/lib/dolibarr-test-results` may not be writable/available under hosted D. A separate **strictly validated private hosted evidence root** is required before D `--baseline`/`--verify`; no webroot or arbitrary fallback.
7. This report is **Stage 0/1 environmental readiness PASS only**. All six Issue #22 CF-03 qualification items remain OPEN, PR #32 remains DRAFT and no merge/release/production GO is implied.

**Next:** independently review Stage-1 tool safety and evidence semantics; prepare bounded private-hosting evidence directory support, authorized synthetic fixtures, and separate case-specific Stage-2/3/4 witnesses. Preserve exact tested source/config/profile hashes on each future baseline/verify.
