# CF-02 POS01-C — Owner-operated real HTTP contribution: PASS (2026-11-20)

**Status: POS01-C PASS with independently reconciled Owner-supplied witness artifacts.** This is real authenticated CoreGuarded contribution + standard Agenda integration on the exact independently reviewed PR #29 source, **not** real fault injection, independent HTTP replay, physical transaction-event identity, general production qualification or v0.1.0 release approval.

## Identity and provenance

- **Source HEAD tested:** `290782dfdf6d6048b801843b5e1c4e464ac3bfdc`; PR #29 independently reviewed at this exact HEAD and [normally merged](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/29) by merge commit `f0b1e6d40ff55abb1e7555c800caea0fdd1fe90d`.
- **Reference runtime:** Dolibarr V24.0.3, PHP 8.2, Oracle MySQL 8.4.13, isolated `dolibarr_i105_v24`, restricted effective account `dolibarr_i105_v24_limited@10.0.2.100`, `CoreGuarded`, `NO_CHAIN`, `autocommit=1`, selected InnoDB tables; Agenda ON, invoice/customer/mail OFF.
- **Fixture:** new synthetic member ID **58**, Core reference `MEM2611-0058`, marker `CF02LAB-20261120C1`, active member type ID **2** / EUR **120.00**, no prior subscriptions, no linked customer.
- **Procedure:** Owner-operated `tools/cf02-contribution-witness.php --check-only` and `--baseline`, each `PASS`; exactly one authenticated manual Dolibarr massaction confirmation; then a single `--verify` producing **PASS**. The witness reads on an independent connection and does not execute business DML or HTTP.
- **Browser result, Owner-reported:** `Transaction profile used: CoreGuarded`, **1 successful · 0 skipped · 0 failed · 0 unknown**, total EUR **120.00**, subscription **#67**, “Successful: Subscription saved; commit confirmed.” Confirmation email not selected.
- **Selected dates and generated label:** 2080-01-01 to 2080-12-31; persisted subscription note contains the **actual marker**. This corrects the *new test procedure*, not historical POS01-A/B artifacts.

## Evidence integrity

The Owner uploaded the five private artifacts `baseline.json`, `baseline.sha256`, `after.json`, `verdict.json` and `verify.started`. The reviewing assistant parsed the complete supplied bytes, independently recomputed the baseline SHA-256 and compared all keys of the snapshots; private artifacts remain with the Owner and are **not** committed to Git.

| Artifact / observation | Independently reconciled value |
| --- | --- |
| Baseline creation UTC | `2026-11-20T16:16:18+00:00` |
| Witness post-readback UTC | `2026-11-20T16:18:39+00:00` |
| `baseline.json` SHA-256 | `1546d1cba936270859b4dfa1011641e3f89b614ee49cf8180bda67cd3ae952bb` — matches stored `baseline.sha256` |
| `after.json` SHA-256 of supplied bytes | `8cf14e2d9ee6899be4c8aaca4ac970b9066daf8439279cc92d1a2bc83b4e32d7` |
| `verdict.json` SHA-256 of supplied bytes | `3ad9fa91a9377b8ca4c72980e426a625b2bccdb99a9ef18434382422f2ef2511` |
| `verify.started` SHA-256 of supplied bytes | `bd6af41a779369eddf33f55beda7c47faafc2d2d333d65c7100a1306dfb79704` |
| Witness verdict | `PASS`, subscription `67`, Agenda count `1`, member end `2080-12-31 00:00:00`, `persisted_core_contribution_observed` |
| Additional F29 proxy file `class/coreguardedrightsdb.class.php` | SHA-256 `8f770411d4c2989d7e93dccfd85599bb31053b3a80986fff70ad24249558f948` before and after (Owner console; outside the eight snapshot hashes) |

## Before/after verification

| Checked value | Before | After | Verdict |
| --- | --- | --- | --- |
| Member `datefin` | NULL | `2080-12-31 00:00:00` | Expected Core R16 outcome |
| Selected member other fields | Baseline | Unchanged | PASS |
| Subscription rows for member 58 | 0 | One: **#67**, EUR 120.00, `2080-01-01 00:00:00` to `2080-12-31 00:00:00`, marker present | PASS |
| Standard member subscription Agenda event | 0 | One: `AC_MEMBER_SUBSCRIPTION_CREATE` **#103**, member 58 | PASS |
| Agenda user resource | 0 | One linked resource, `element_type=user`, `actor_id=4` | PASS |
| Global `llx_subscription` count | 62 | 63 | Expected +1 |
| Global `llx_actioncomm` count | 97 | 98 | Expected +1 |
| Global `llx_actioncomm_resources` count | 97 | 98 | Expected +1 |
| Global `adherent`, `societe`, `socpeople`, `facture`, `facturedet`, `element_element`, `paiement`, `paiement_facture`, `bank` | Baseline counts | Unchanged | PASS |
| DB identity/config/profile, member type, engine values, eight recorded source SHA-256 | Baseline | Unchanged | PASS |

The Owner-console source HEAD and module-symlink validation passed before execution, with a clean `git status --short` and the additional proxy-file hash unchanged afterward. The eight witness-recorded hashes are equal before and after. The checked proxy SHA-256 was Owner-reported rather than included in those eight witness snapshot hashes.

## Acceptance, historical boundaries and follow-up

**Independent evidence-reconciliation outcome:** **POS01-C PASS**, with no marker, date, amount, Core R16, Agenda, source-context or protected-count discrepancy. The separate authenticated frontend message indicates an acknowledged Core commit under the accepted application contract; independent readback **does not** infer actual physical event identity. The witness explicitly records `acknowledgement=not_determined_by_database_readback`.

The original [POS01-A/B Owner evidence](pos01-owner-test-acceptance-2026-11-20.md) remains intact: **A: STOP / CF02MemberEndMismatch; B: STOP / CF02SubscriptionPostconditionMismatch**. The original Owner waiver was valid **for review entry only**, and those artifacts were not reclassified, altered or replayed. POS01-C is a new distinct successful fixture.

**External independent review:** F29-01/P1 **CLOSED**, F29-02/P1 **CLOSED**, no remaining P0/P1 development-merge blocker; final verdict **APPROVED FOR DEVELOPMENT MERGE WITH DOCUMENTED LIMITS** for `290782dfdf6d6048b801843b5e1c4e464ac3bfdc`. The Owner separately accepted the completed POS01-C PASS and authorized the normal PR #29 development merge; see the [PR #29 Owner record](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/29#issuecomment-7044837353).

**Remaining gates:** CF-03 real negative/rollback, 1213/1205, replay, multi-member, concurrency, CHAIN/RELEASE, Agenda OFF and current-HEAD EnhancedMySQL/MariaDBSnapshot invoice regression remain open. CF-04 new release branch, exact ZIP/artifact and independent final release review plus separate Owner release GO are open; CF-05 expansion open. This evidence and the development merge are not production or public v0.1.0 release approval.
