# Functional porting baseline

## Current porting outcome – after PR #38 (2026-11-24)

The original Phase 1/3B porting analysis and 71-ID requirement matrix are **historical source-bound evidence**. The member contribution + optional Finance/Third Party/invoice/PDF/mail implementation for all three transaction profiles was **completed and squash-merged in PR #38** as `0998fbaf58155cca04d31ab99c6bb47420e53c72`; reviewed tree identical to `b15b93580f995fb05a696a125c2a5fd995dd4bb3`. Older descriptions of these features as future work are not current delivery status. [Current baseline](current-development-baseline.md) and [Owner Variant B](owner-core-parity-2026-11-22.md) govern. Local bounded A/B/C cases, B mail and focused D contribution/session/replay are accepted; remaining A/C mail, D finance/PDF/mail and full integration are OPEN, release NO-GO. Never re-run the historical Env B UNKNOWN operation.


**Historical PR #45 checkpoint:** development squash `b026f1504db27308fc7326e2e88ec2e53b2f48d9`, reviewed tree identical; #44 consolidation completed and #41–#43 closed without merge as superseded. [Current baseline](current-development-baseline.md) governs source identity, F45-01/02 open test precision, F45-03 documentation follow-up and remaining gates.

---

> Historical record follows. Current status is defined above.

> **Current scope supersession (2026-11-21):** This is an archived Phase 1/3B porting report. Its statements that booking or invoice/PDF/email are *future* or *permanently deferred* are historical. [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22) requires the complete contribution/optional invoice/Third Party/PDF/mail massaction for v0.1.0 across all three profiles; CF05 is the outstanding implementation gate. There is **no** persistent module journal, exactly-once recovery or automatic retries in this scope. The original 71 requirement IDs are preserved in [requirements.csv](requirements.csv); use the [current baseline](current-development-baseline.md) for active statuses, not this historical stage snapshot.

Phase 1 revision 2 is the accepted architecture: an independent wizard entered through
`memberlist` / `addMoreMassActions` / `doMassActions`. The future result notice uses
`printFieldPreListTitle` when needed. No core hooks are patched. Phase 3B adds a module-owned Adherent subclass solely to observe update_end_date failures (R16).

The accompanying `requirements.csv` preserves all **71** requirements from that audit.
Original porting-assessment columns are retained. Added phase/status/evidence columns
record actual implementation and distinguish read-only portions from deferred booking.
The source remains PR #42620, commit `14c4b07ba86e7a320d61e75b28cb88a50ce54f77`.
Phase 3A adds read-only calculations and interactive previews. Booking, invoice/PDF
creation and email remain future scope; preview success does not authorize writes.

Mandatory later risk work:

- R04: PDF generation is not atomic with a database transaction; verify failures and remnants.
- R05: concurrent individual and bulk processing do not necessarily share a locking protocol.
- R06: core nested transactions use counters, not independent savepoints.
- R07: session replay protection does not provide atomic database/SMTP crash recovery.
- R08: preserve sendMail hook return semantics, object replacement and context restoration.
- R09: historical synthetic scenarios are not proof of real database/filesystem integration.
- R16: V22 `Adherent::subscription()` does not fully propagate an `update_end_date()` failure;
  design and verify adapter consistency handling before accepting business writes.

Future scope includes per-member transactions, optional invoices/third parties/PDF/mail,
persistent results, atomic replay handling and V22–V25 qualification. Preview rows
include a fingerprint of computed values and relevant source/configuration fields,
and an overlap snapshot. These prepare the confirmation design but are NOT a booking
lock, durable journal, atomic replay guard or permission to skip revalidation.

See phase3a-validation.md for calendar compatibility details and current evidence.

## Phase 3B implementation (supersedes the earlier future-scope statements)

Booking and independent confirmation mail are implemented on V22.0.5. Invoice,
third-party, PDF and payment workflows remain deferred and disabled in the UI.
The adapter observes the actual return of the core end-date update and validates
the subscription and latest-start history before committing its own transaction.
There is no new table, operations journal, queue, cron job or core modification.
The existing selection token plus revision becomes a consumed confirmation; session
checkpoints precede booking and mail. This is not crash-safe exactly-once delivery.

See phase3b-validation.md for precise risk boundaries and the real integration gate.
