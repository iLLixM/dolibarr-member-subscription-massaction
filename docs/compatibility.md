# v0.1.0 compatibility and operating prerequisites

Status: **release candidate preparation; R0-04 and R0-07 pending**.
This is the single current compatibility overview. The original campaigns and
source pins remain in the repository's
[validation history](https://github.com/iLLixM/dolibarr-member-subscription-massaction/blob/release/prepare-v0.1.0/docs/phase4-implementation-validation.md) and
[P3 synthetic evidence guide](https://github.com/iLLixM/dolibarr-member-subscription-massaction/blob/release/prepare-v0.1.0/docs/p3-qualification-summary.md); those historical
qualifications do not assert a test of the final v0.1.0 ZIP.

## Policy, admission and evidence are different

| Layer | Scope | What it does not mean |
| --- | --- | --- |
| Owner's intended release scope | Dolibarr V22–V24; MariaDB >=10.11 or Oracle MySQL >=8.4; PHP >=7.4 | Not every patch, server edition or configuration has real evidence. |
| Existing technical admission | Core >=21.0.0, no upper-version allowlist; runtime capability/profile checks | V21, future Cores and every accepted backend version are not automatically supported/qualified. No new version locks were added for R0. |
| Synthetic CI | 21 pinned PHP/Core pairs below | No live DB, installer, SMTP, PDF-model or full browser/load qualification. |
| Real integration | Only the exact historical profiles below and each campaign's tested portions | Not a final ZIP installation test or blanket production certification. |
| Final release gates | R0-04 integration and R0-07 independent release review: NOT EXECUTED in R0 | Metadata `0.1.0` and a structurally valid ZIP are not release approval. |

Database minimums are Owner deployment requirements, not new numeric version
checks in the product. Administrators must enforce them. Existing capability,
engine, transaction-ownership and supported-invoice checks remain unchanged.
PostgreSQL is not supported; it remains a separate later iteration.

## Synthetic PHP/Core matrix

| Core sources | PHP | Jobs |
| --- | --- | --- |
| V22.0.0, .1, .2, .3, .4, .5 | 7.4 and 8.2 for each | 12 |
| V23.0.0, .1, .2, .3, .4 | 8.2 | 5 |
| V24.0.0, .1, .2, .3 | 8.2 | 4 |

Pins and runners are authoritative in `.github/workflows/ci.yml` in the source
repository. PHP 7.4 has synthetic V22 coverage, not a real runtime acceptance.
No V23 live integration or V24/PHP-7.4 CI run is claimed.
Respect the chosen Core's own PHP requirements; the module minimum does not lower
Core's minimum.

## Real reference profiles (historical integration, not final ZIP)

| Core / PHP / backend | Completed evidence | Limits |
| --- | --- | --- |
| V22.0.5 / PHP 8.2.35 / MariaDB 10.11, mysqli/InnoDB | G2 FINAL APPROVED – PASS WITH DOCUMENTED LIMITATIONS; G3 APPROVED – PASS WITH DOCUMENTED LIMITATIONS; G4 PASS – OWNER ACCEPTED | Historic V22 campaign; the original G2 entry records the 10.11 series, not an exact patch. Do not infer a newer patch or a final v0.1.0 rerun. |
| V24.0.3 / PHP 8.2.35 / MariaDB 10.11.19, mysqli/InnoDB, entity 1 | C3 campaign; later MDB-01–09 9/9 PASS at `0999efb`, independently confirmed; Issue #8 automated CLI100 and later Owner-confirmed HTTP100 | Campaigns/test SHAs remain distinct. Owner HTTP100 has no new independent reproduction/raw traces; no general load/PDF/SMTP/parallel qualification. |
| V24.0.3 / PHP 8.2.35 / Oracle MySQL Community 8.4.13, direct mysqli, InnoDB, REPEATABLE READ | I1-05 historical failure; FIX01 20/20 PASS; targeted I107 hardening and positive independent follow-up; I107-F01/F02/F03 closed | Only the instrumented isolated profile; historical PDF/mail evidence is separate. No inference to other MySQL versions, editions, proxies or connection pools. |

The independently reviewed development baseline was merged through PR #13 and
PR #16; see the validation history. R0 changes metadata/documentation/build tooling,
not those business paths. R0-04 must test the exact packaged candidate separately.

## Required database contracts

- Use direct `mysqli`; all actually touched tables must be InnoDB, including
  standard Core Agenda event/resource tables when relevant auto-events are enabled.
- Keep one owned Core transaction per member, including optional customer/invoice
  work. Do not introduce connection switching, multiplexing or automatic reconnect.
- MariaDB uses `@@in_transaction` plus logical depth and engine checks. This is
  status-snapshot continuity, not MySQL event-level continuity.
- MySQL requires `autocommit=1` outside explicit transactions, actual own ACTIVE
  transaction isolation `REPEATABLE READ`, Performance Schema transaction
  instrumentation and active `transaction`, `events_transactions_current`,
  `thread_instrumentation` and `global_instrumentation` support. Session defaults
  alone are insufficient when the next transaction has a one-shot override.
- The MySQL application account needs its normal schema rights plus `SELECT` on
  `performance_schema.events_transactions_current`. Required observation includes
  identity, SAVEPOINT/RELEASE counters, `ISOLATION_LEVEL` and
  `PS_CURRENT_THREAD_ID()`. The qualified profile did not need PROCESS or SELECT
  on `threads`/`setup_*`. The table-level SELECT grant can expose other sessions'
  rows even though the observer filters its own thread; the DBA must assess this.
- MySQL fresh-idle calibration reads an existing InnoDB member row. An empty
  member table is rejected, not silently seeded. Missing rights, instrumentation,
  freshness or ownership stop new work safely; do not bypass the gate.

These are administrator prerequisites, not authorization for automatic grants,
engine changes, isolation changes or seeding. R0 makes none of those changes.

## Invoice, document and delivery limits

Real invoice references use standard invoices, entity 1, EUR, quantity 1, VAT 0,
no subscription product/local taxes, Mercure numbering and Sponge PDF. Positive
and zero fees were exercised within the named campaigns. Product/packaging/stock,
nonzero/local taxes, other currencies/models, arbitrary hooks and shared-entity
configurations have no blanket real qualification.

The existing invoice gate accepts only empty/unset, `0` or
`defaultforfoundationcountry` for `ADHERENT_VAT_FOR_SUBSCRIPTIONS`; the latter calls
Core VAT logic but does not extend the VAT-0 real qualification. Unsupported
positive record IDs and malformed values are rejected, including on V22.
Explicitly invalid PDF models are rejected; absent models use available `crabe`,
then `sponge`. Availability is not qualification of every model.

Historical V22 SMTP Core diagnostic 121 remains documented despite confirmed
receipt. V24 controlled SMTP acceptance and Owner receipt are separate observations,
not a general delivery guarantee. G3-W02 is template configuration, not an open
module fix. See [operations](operations.md) for manual recovery and side effects.
