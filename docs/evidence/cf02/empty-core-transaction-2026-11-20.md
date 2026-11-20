# CF-02 – Loaded-Core empty transaction: Owner-reported result

**Date:** 2026-11-20
**Status:** PASS, as reported by the test environment owner; independent repetition NOT EXECUTED.

## Invocation

```sh
sudo -u www-data env \
  CF02_DOLIBARR_ROOT=/home/marcel/dolibarr-i105-v24-mysql \
  CF02_ACK_EMPTY_TRANSACTION=YES \
  /usr/bin/php -d xdebug.mode=off \
  tools/cf02-loaded-core-entry.php --empty-transaction
```

## Original Owner-reported sanitized JSON

```json
{"status":"PASS","mode":"--empty-transaction","type":"bounded loaded-Core empty-transaction test; no business DML","failure":null,"source":"development branch","core":"24.0.3","mysql":"","completion":"NO_CHAIN","can_continue":true}
```

## Narrow technical interpretation

- A real Dolibarr Core v24.0.3 database adapter and restricted MySQL 8.4.13 environment accepted the staged D-01 entry and acknowledged a *single empty Core ROLLBACK*. The helper invokes adapter preflight, `SET TRANSACTION ISOLATION LEVEL REPEATABLE READ`, Core `begin()`, active-state checks, pre-end completion read, and Core `rollback()`.
- `completion=NO_CHAIN` and `can_continue=true` are the measured and module-derived post-end conditions. No `performance_schema` permission was necessary; prior Owner-run restricted-account check explicitly observed error 1142.
- This output does **not** provide a separate SQL trace verifying all intervening SQL statements; adjacency is supported by the checked script and adapter source.
- This run performed NO member, subscription, customer, invoice or Agenda business DML; did not exercise a Core COMMIT, authenticated HTTP, persistent checkpoint, CHAIN/RELEASE, concurrency or business error rollback.
- The output has an empty `mysql` diagnostic field. Version 8.4.13 was established in the separate preceding read-only SQL check, not by this specific JSON.

## Source provenance and evidence limits

- Prior operator command B/C reported source HEAD `f205f9a7c2954670fe6f0e42fb78051a9ba6fa04`. The new D invocation did not itself print `git rev-parse HEAD`; therefore the exact working-copy commit is not independently proven from this JSON.
- The test script Git blob on the prior tested source HEAD and the following documentation-only GitHub HEAD `5e146502f4b7ebba0c2ac1d24f88879ef7a57072` is identical (`a6b62e5f23864d4a402e3b371ea5fb5b2b9c028f`). The adapter PHP file also remained unchanged by the intervening documentation-only commit.
- This record is a faithful transcription of the Owner-reported output, not a cryptographic attestation of the test machine or independently witnessed SQL session.

**Decision:** PASS for the restricted-MySQL, loaded-Core empty D-01 transaction gate only. No production or CF-02 full-implementation approval; continue with separately gated synthetic member contribution/Agenda and real HTTP testing after reviewing the lab fixture/rollback safeguards.
