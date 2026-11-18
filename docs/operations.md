# Operation, errors and manual reconciliation

Applies to the [bounded compatibility profiles](compatibility.md), with v0.1.0
release integration/review still pending. The Owner's R0-05 decision accepts the
existing session/checkpoint residual risks for the limited intended release scope;
it does not grant general production, throughput or third-party-hook approval.

## Before and during a run

Use an authenticated internal user and review current rights, amounts, dates,
customer/invoice options and overlaps. Never assume preview eligibility is a lock
or permission to skip confirmation. Each member has its own database transaction;
successful earlier members remain committed if a later member fails or stops.
Keep the browser session until results have been inspected. Do not submit the same
work in multiple tabs or start competing large batches as a workaround for delays.

The default cap of 100 is an admission bound, not a timeout guarantee. The original
CLI100 measured 5.89840 seconds, 20 MiB peak PHP memory (4 MiB increment), 76,164
serialized checkpoint bytes. A later manual Owner-confirmed HTTP100 took about
6 seconds confirmation-to-results, contribution-only; it was not independently
reproduced or newly traced. PDF/SMTP latency and parallel workloads can be much
larger. No background worker, progress guarantee or general load acceptance exists.

## Interpret the result before acting

| Outcome | Meaning / operator action |
| --- | --- |
| Successful contribution / confirmed invoice IDs | Commit was confirmed for that member; inspect document/mail status separately. Do not book it again because delivery failed. |
| Skipped / changed confirmation / missing overlap consent | That row was not accepted for the submitted business action. Inspect current state and the reason before proposing any separately authorized fresh work. |
| Failed with confirmed rollback | The owned database transaction was confirmed rolled back. This is not proof that arbitrary external hooks/filesystem effects were reversed. Inspect the diagnostic stage and relevant external effects. |
| `unknown`, `stop`, unconfirmed rollback/commit or lost connection | The module cannot prove the final outcome/ownership. Stop the batch; later members may be skipped. Never infer rollback from a missing result or automatically retry. |
| Successful booking, PDF failed/missing | Business objects remain. Inspect the authorized invoice/document metadata and physical artifact; do not rebook or automatically regenerate it. |
| Mail `transport`, hook-handled, failed/disabled or uncertain | Transport acceptance, hook takeover and mailbox delivery are different. Inspect the specific result; do not resend automatically. |

A missing ID, contribution, PDF or email alone does **not** prove that no action
occurred. Never roll back a replacement/foreign transaction or repair business
records with ad hoc SQL because the UI timed out.

## Manual reconciliation after uncertainty

1. Stop resubmissions and preserve the session/result, sanitized time window,
   selected member IDs, periods, amounts, options, available object IDs and
   diagnostic stage. Keep credentials/tokens/private documents out of tickets.
2. Ask an authorized operator for independent **read-only** Core/database checks.
   Match the member/entity, contribution period/amount, member end-date history,
   customer identity, validated invoice/status/totals and exact contribution↔invoice
   relation. Also inspect relevant Agenda/resource records. Do not rely on counts
   or a missing row alone; other writers may have acted.
3. Reconcile PDFs using authorized invoice/entity/path, metadata, physical bytes and
   hash/time evidence. Check mail logs/hook outcomes and, where authorized, actual
   recipient receipt separately. SMTP acceptance does not prove delivery.
4. Classify only what the evidence establishes: confirmed committed, confirmed
   rolled back, definitely not started, or still unresolved. Preserve unresolved
   cases as such and seek Owner review before any separately authorized remediation.
   This procedure itself authorizes no new booking, document generation or delivery.

There is **no operations journal, module table, automatic retry, resume, rebook,
reinvoice, PDF regeneration or email redelivery**. Session checkpoints are not a
durable exactly-once protocol. An expired/lost session, server crash or interrupted
session write can leave work unobservable from the wizard. Readability of an
existing authenticated result when readiness fails does not authorize a new run.

## Transactions and external side effects

The module requires provable transaction ownership and supported engines; MySQL
also requires the actual transaction isolation/instrumentation described in
compatibility. Known unsupported isolation can safely roll back a fresh owned
context; malformed/lost ownership stays `unknown/stop`. Do not bypass the observer,
change isolation to force acceptance or roll back an unrelated transaction.
MariaDB status snapshots are not upgraded to MySQL event-continuity guarantees.

Standard Core Agenda handling is covered only when enabled and on relevant paths.
Unknown third-party hooks, numbering modules, filesystem renames and other writers
need their own assessment; they may not follow the member-row lock and may have
nontransactional effects. PDF/ECM and SMTP run after a confirmed commit and have
separate failure/uncertainty windows. No distributed transaction or compensation
mechanism exists. Historical unexecuted interruption, Multicompany, arbitrary
foreign-writer and extended compatibility cases remain unqualified.

## Known non-blocking observations

- **I107-FU-01 (P3 Low):** isolation diagnostics must distinguish last observed
  ACTIVE-event isolation from a completed-transaction observation; no new fix here.
- **F16-03 (P3):** validation of all raw selected IDs is O(N) before count rejection;
  an extreme authenticated payload still costs CPU/memory, despite avoiding member
  loading and expensive preview work.
- **F16-04 (P3):** the original archive's hashes/counts establish added Agenda rows,
  not independent reconstruction of every one of 100 foreign-key assignments.
- **F16-05 (P3):** a configuration reduction between durable consumption and
  orchestration admission can leave a safely stopped `processing` run before DML.
  Do not automatically reopen/retry it; reconcile first.

These remain documented observations, not broadened test passes. Development
merge acceptance and Owner risk acceptance do not replace R0-04/R0-07.
