# Functional porting baseline

**Historical porting plan:** the Phase 1/3A/3B deferred-function statements below
are preserved at their original stages. Optional customer/invoice/PDF/mail and
later Core/database compatibility development were subsequently delivered and
reviewed; see [current validation](phase4-implementation-validation.md).
R0 prepares v0.1.0 with the [documented compatibility limits](compatibility.md),
not a claim that all originally planned V22–V25 scenarios were executed.
R0-04 integration and R0-07 release review remain pending.

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
