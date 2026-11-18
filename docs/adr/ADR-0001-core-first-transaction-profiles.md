# ADR-0001 — Core-first transaction profiles

**OWNER-APPROVED ARCHITECTURE STRATEGY – DETAILED DESIGN PENDING INDEPENDENT REVIEW**

Decision owner: repository Owner, 2026-11-18. Detailed design author: Codex;
independent architecture acceptance: **PENDING**. This record implements CF-00
(documentation), not CF-01/CF-02. Normative future contracts below are design
requirements, not descriptions of deployed Core-Guarded behavior or test results.

## 1. Authority, baseline and problem

[Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22)
is the controlling Owner decision, read in full on 2026-11-18 (body updated
2026-11-18T14:21:48Z; zero comments at inspection). Its ADR-01–ADR-12 are reproduced
in section 2. It supersedes earlier universal physical MySQL proof requirements
**only for the new Core-Guarded profile**. Enhanced MySQL and MariaDB retain their
existing contracts. Owner risk acceptance is neither implementation nor release approval.

`origin/main` was fetched and verified against GitHub at
`48d196cfb04421d775ab4e6757083f6a755121df`, exactly the requested reference.
The documentation branch starts directly there; no release or spike commit is its
base. The existing local main checkout was not advanced or edited.

| Source | Provenance and use |
| --- | --- |
| [Current source](https://github.com/iLLixM/dolibarr-member-subscription-massaction/tree/48d196cfb04421d775ab4e6757083f6a755121df) | Implementation inspected: `class/databasestate.class.php`, `class/booking.class.php`, `class/invoice.class.php`, `lib/booking.lib.php`, `subscription.php`. No Core-Guarded adapter or profile configuration exists at this baseline. |
| [PR #13](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/13) | Merged; tested runtime `0999efb4aafca43d90ce12e38d370662ad0c63a1`, merge `aaf1f3786a4c9ea7e1707ad41cb821b0bb271e3d`. Existing observer/hardening, not Core-Guarded qualification. |
| [Existing qualification](../phase4-implementation-validation.md#pr-13-post-merge-closure-and-mdb-test-01--2026-11-17), [coverage legend](../test-coverage.md#mysql-iteration-1-evidence-2026-11-17) | Historical I1-05 failure, FIX01/I107 correction and MDB-01–09; exact profiles and exclusions remain unchanged. |
| [SH-01 report](https://github.com/iLLixM/dolibarr-member-subscription-massaction/blob/7df49867e001f2f56ce95d04c8ce5c4bfbd582c3/docs/sh01-mysql-restricted-feasibility.md), [evidence](https://github.com/iLLixM/dolibarr-member-subscription-massaction/tree/7df49867e001f2f56ce95d04c8ce5c4bfbd582c3/experiments/sh01) | Separate preserved `docs/sh01-feasibility-evidence` commit `7df49867e001f2f56ce95d04c8ce5c4bfbd582c3`; original spike `409a0f51a99c92155a159b0e12301ee09612230f`. NO-GO for the tested equivalent-observer construction; SH-02 not authorized. |
| [Issue #21](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/21) | Historical requirements and negative evidence retained. Its old implementation/release sequence is superseded by #22, not silently satisfied. |
| [Issue #20](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/20), [PR #19](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/19) | #20 remains the release tracker. #19 is CLOSED WITHOUT MERGE, preserved at `a74edb29ba6ca49af46c389f98c4bfd86cf161b6`; its prepared ZIP is historical, not a Core-first artifact. |
| [Dolibarr Core transaction methods](https://github.com/Dolibarr/dolibarr/blob/5c6c7ff6a5112413b1c27221c8d11750afad56f8/htdocs/core/db/DoliDB.class.php#L216) | V24.0.3 source pin `5c6c7ff6a5112413b1c27221c8d11750afad56f8`; logical nesting described in section 5. SH-01 records identical method bodies across its 15 inspected V22/V23/V24 pins; that is source analysis, not 15 real integrations. |

The current MySQL observer requires an instrumented event row and SELECT on
`performance_schema.events_transactions_current`. Missing SELECT caused the
restricted-host preview failure (1142) in #21. Current readiness also couples a
read-only preview to physical transaction admission. Shared-hosting usability is
therefore blocked even before a booking is attempted.

The Owner chooses a different, explicit risk contract using normal Core transaction
acknowledgements and shared business safeguards. This is a bounded reduction of
physical evidence, not a new proof that counters, markers or autocommit equal an
event observer. The objective is useful contribution booking with honest guarantees,
while retaining optional stronger evidence and previously qualified functions.

## 2. Binding Owner decisions — 2026-11-18

The following decision text is transcribed from #22. The last column is the design
trace, not an additional Owner decision.

| ID | Binding decision | Design trace |
| --- | --- | --- |
| ADR-01 | Core-Guarded, using Dolibarr Core transactions with targeted safeguards, becomes the regular MySQL transaction model, subject to qualification. | Sections 3–5; no current implementation claim. |
| ADR-02 | Keep the existing event-based MySQL Enhanced Observer as an **optional** profile. | Sections 3, 6; preserve observer behavior. |
| ADR-03 | Performance Schema SELECT is **not** a general module requirement; only Enhanced MySQL requires it. | Profile privilege matrix; never request a grant for Core-Guarded. |
| ADR-04 | Select the security profile by **explicit administrative configuration**, fixed before each booking operation. | Section 6; pin configuration identity and feature scope. |
| ADR-05 | **No automatic fallback** from configured Enhanced to Core-Guarded when Enhanced prerequisites fail. | Section 6; fail closed on 1142/instrumentation failure. |
| ADR-06 | Initially permit **contribution-only** in Core-Guarded; invoice/customer creation requires a separate qualification and decision. | Feature matrix; reject unsupported options server-side. |
| ADR-07 | Preserve the existing MariaDB snapshot observer and its qualified behavior. | Sections 3–5; no new event/isolation promise. |
| ADR-08 | Detected uncertainty leads to **UNKNOWN/STOP**, without automatic retries or compensating writes. | Outcome/rollback contract and risk register. |
| ADR-09 | No persistent journal, new module tables or migrations in v0.1.0. | Sections 5–7; session loss needs manual reconciliation. |
| ADR-10 | Decouple read-only preview from physical transaction readiness; booking remains gated. | Section 7; preview is not booking authorization. |
| ADR-11 | Separate focused implementation PR, real DB/HTTP tests, independent security/release review and Owner acceptance. | Section 9; CF-00 approval does not discharge later gates. |
| ADR-12 | The prior physical event-equivalence requirement is **explicitly superseded for the newly accepted Core-Guarded profile only**, not for Enhanced; residual risks must be stated accurately. | Evidence matrix, sections 5/8 and companion risk register. |

## 3. Target boundary and evidence matrix

Working names: `CoreGuarded`, `EnhancedMySQL`, `MariaDBSnapshot`. These are design
names, not new PHP classes or configuration keys delivered by CF-00. Keep one outer
transaction owner in Booking; profile-specific admission/observation must not become
a second transaction manager. Invoice shares the same DoliDB/profile context only in
profiles where the invoice function is admitted. Preserve existing Enhanced/MariaDB
behavior wherever possible; do not reinterpret the old observer's `fresh/owned`
fields as physical guarantees for Core-Guarded.

| Property | Core-Guarded — Oracle MySQL | Enhanced Observer — Oracle MySQL | MariaDB Snapshot |
| --- | --- | --- | --- |
| Role | Future default for new installs, after qualification | Explicit optional profile; existing installs protected | Existing standard retained |
| Privileges | Normal Core/application schema rights; no P_S SELECT or global admin prerequisite | Schema rights plus required P_S SELECT **and working instrumentation** | Existing schema rights; `@@in_transaction` supported |
| Admission / ownership | Acknowledged own Core begin, logical depth and stable native connection under reviewed caller assumptions; no independent physical-idle/own-event proof | Calibrated idle, new own ACTIVE event, native/server/thread/event binding and fresh counter checkpoints | Own begin, depth, stable native handle/ID and active snapshot; no event identity |
| Same-connection replacement | May be invisible; an acknowledged end can refer to a replacement/no-op | Reject mismatching event at checkpoints; not continuous monitoring | COMMIT/BEGIN can evade endpoint snapshots |
| Actual isolation | Must qualify an ordered Core-compatible recipe; no actual-event isolation proof | Actual own ACTIVE event must report REPEATABLE READ | Existing branch has no equivalent actual-event isolation gate |
| Successful end | Core acknowledgement and application checks, explicitly limited evidence | Ack plus expected original ended event, then fresh idle | Ack/depth plus idle snapshot, not event-bound |
| Detected uncertainty | Latched UNKNOWN/STOP; no rescue by row readback or new connection | Existing latch/stop contract unchanged | Existing snapshot/stop contract unchanged |
| Overall evidence | NOT IMPLEMENTED / NOT QUALIFIED | Historical bounded real and synthetic evidence | Historical bounded real and synthetic evidence |

No profile promises continuous monitoring, exactly-once recovery, arbitrary-hook
atomicity or whole-batch atomicity. One transaction is scoped to **one member**;
earlier successful members remain committed if a later one stops.

## 4. Functional admission is separate from observation strength

| Function | Core-Guarded initial scope | Enhanced MySQL | MariaDB Snapshot |
| --- | --- | --- | --- |
| Read-only selection/preview | Planned without physical readiness; rights/entity/input/limit checks retained | Same separation; missing observer capability shown before booking | Same separation; snapshot readiness still gates booking |
| Contribution + Core end-date/R16 | Planned; CF-03 real restricted-MySQL qualification required | Preserve bounded qualified path | Preserve bounded qualified path |
| Standard automatic Agenda contribution event | Part of contribution-path engine, rollback and persistence qualification; no blanket hook allowance | Preserve conditional engine gates/evidence | Preserve conditional engine gates/evidence |
| Existing customer + invoice | **DISABLED**, including manipulated/server-held options | Preserve supported qualified configuration | Preserve supported qualified configuration |
| Automatic customer + invoice | **DISABLED** | Preserve supported qualified configuration | Preserve supported qualified configuration |
| Invoice PDF/ECM | **DISABLED** with invoice path; no qualification inherited | Existing post-commit path and its limits | Existing post-commit path and its limits |
| Contribution email | Not implicitly qualified by contribution-only DB tests; separate permitted post-commit/mail evidence required | Existing selected post-commit behavior/limits | Existing selected post-commit behavior/limits |

Changing the observer must not silently remove previously qualified MariaDB or
Enhanced invoice functionality. Conversely, their past positive invoice/PDF/SMTP
results do not admit those functions in Core-Guarded. Server-side policy must reject
invoice/autocreate before business calls even with direct POST, old sessions or
injected service options. UI explanation alone is insufficient. CF-05 requires
separate review, real integration and Owner decision before expansion.

## 5. Transaction contract proposed for independent review

### Trust and entry

Trust the server/driver to report honestly and the reviewed Core call sequence;
arbitrary hostile PHP rewriting guards is outside the model. Unexpected shared-
connection SQL, extension nesting errors, loss/replacement, implicit commits and
unacknowledged ends remain relevant faults. Extensions with uncontrolled transaction,
DDL, filesystem or external effects are not covered by a generic safety assertion.

Before a new member operation, resolve its immutable profile and feature policy,
check permissions/entity/current batch limit, Core capabilities and relevant InnoDB
engines, and reject a nonzero Core nesting depth. Pin the DoliDB object, actual mysqli
handle and connection identity. Known foreign contexts, invalid configuration or
connection errors must refuse entry; never issue an opportunistic rollback to clear
them. Enhanced additionally proves idle using its existing calibration; MariaDB
uses its existing native snapshot. Core-Guarded's zero counter/autocommit=1 and
stable connection are **admission assumptions/checks, not proof of physical idle**;
a hidden foreign transaction with a zero counter remains a risk (R01). Its entry
control is unresolved in Q01: do not infer specific Owner acceptance of unsafe
foreign-transaction entry from the accepted same-connection replacement limitation.
A separately reviewed nonprivileged entry predicate/caller contract is required
before Core BEGIN can be admitted; failure to establish it blocks implementation.

No role-based downgrade: insufficient Enhanced rights/instrumentation denies booking
in that configured profile. Profile administration requires existing administrator
control; business permissions are reloaded independently and cannot select a weaker
profile through a request parameter.

### Sequence and nesting

The common sequence remains: engines outside BEGIN → acknowledged Core BEGIN →
profile admission → member/type and selected customer/product locks → engine
revalidation after **all** locks → fresh rights/entity/eligibility/fingerprint/overlap
checks → standard Core writes → stored subscription/end-date and permitted invoice
checks → accepted outer end. Keep the Issue #8 current batch-limit rule.

Do not perform snapshot-establishing metadata/business reads between MySQL BEGIN
and the locks. Current FIX01 evidence identifies `information_schema.TABLES` engine
reads as the first offending read-view prefix in that tested MySQL configuration.
Locking reads alone must not be mistaken for refreshing a pre-existing consistent
view. Preserve Core R16 writes and verify the actual pinned Core end-date semantics.

At the cited Core pin, `begin()` at depth 0 issues BEGIN; deeper begin increments a
counter. `commit()`/`rollback()` at depth >1 only decrement it. There is no nested
SQL-savepoint rollback. At outer depth commit issues COMMIT and resets depth on
success; outer rollback resets depth even if SQL fails. Thus neither depth=0 after
rollback nor success of an inner rollback proves business changes were undone.

Require each reviewed nested Core API to return to expected outer depth 1 and check
its return/error before further business work. A known business failure at restored
depth 1 requires the admitted outer rollback, even if an inner rollback returned
success. Unbalanced/unknown depth, known physical replacement or lost connection
latches UNKNOWN/STOP. **Never drain counters in a loop**, create an extra transaction
to regain ownership, or treat inner commit success as completion of the member.
Profile evidence checkpoints remain mandatory for Enhanced/MariaDB. Core-Guarded
uses explicit application-context checks with weaker stated evidence.

### Isolation — unresolved acceptance gate Q03

Enhanced retains actual own ACTIVE-event REPEATABLE READ admission and its existing
safe rollback for a known unsupported level. MariaDB retains its current policy;
this ADR introduces no new MariaDB isolation guarantee.

Core-Guarded must establish and test a compatible policy without pretending to observe
actual-event isolation. Proposed candidate for review: complete all preflight/engine
SQL first, set next-transaction REPEATABLE READ **only at booking entry**, then call
outer Core BEGIN immediately with no intervening SQL; lock before metadata/ordinary
reads. This is a **design candidate, not an approved implementation recipe**. Do not
put SET into the preview/readiness API, change session/global isolation as a workaround,
or infer effectiveness from the session default. SH-01's immediate native SET→BEGIN
positive is supporting primitive evidence only; its metadata-prefix/late-override
counterexamples remain valid. Real loaded-Core ordering, one-shot overrides, failure
handling and supported configurations must be reviewed and qualified before DML is
admitted. If that recipe cannot meet the agreed operating assumptions, return the
specific blocker to the Owner; do not silently accept arbitrary isolation. See Q03
in the [handoff](ADR-0001-review-handoff.md#critical-questions-and-design-gates).

### Outcomes, rollback admission and irreversible uncertainty

| Situation | Required decision |
| --- | --- |
| No transaction attempted; unsupported action or stale confirmation | Refuse/skip without business DML; do not call rollback on a foreign context. |
| Own begin acknowledged, expected depth/connection, business checks passed | Allow only profile-admitted Core work; failures must not be ignored. |
| Ordinary business error, known unchanged connection, expected outer depth 1, no uncertainty, no COMMIT attempted | Admit **one** outer rollback under that profile's ownership contract. Enhanced/MariaDB retain their existing fresh rollback-context checks. Core-Guarded requires a locally recorded acknowledged begin, unchanged identity/depth, no known intervening end/replacement and no detected error invalidating context. These are bounded application ownership assumptions, not event proof. |
| Outer rollback acknowledged with expected depth 0 and valid profile completion checks | Report Core-acknowledged rollback for Core-Guarded; snapshot-confirmed for MariaDB; expected own-event completion for Enhanced. Do not imply equivalent physical proof. Error/skip may continue only after this accepted end; ordinary rollback cannot certify nontransactional side effects. |
| Core COMMIT acknowledged, expected depth 0, stable context and required business/profile checks passed | Core-Guarded: application-level success under the accepted contract. Enhanced: expected original event COMMITTED before idle bootstrap. MariaDB: existing ack/idle snapshot contract. Persist result before post-commit actions. |
| Failed/unacknowledged begin, end, lost/swap connection, unbalanced depth, known foreign end or other detected ambiguity | UNKNOWN/STOP; no later member, rollback of a possible replacement, retry, compensation or post-commit PDF/mail. Zero counter or plausible rows cannot redeem uncertainty. |
| COMMIT attempted and outcome uncertain | Never fall through to rollback/rebook. Successful independent readback helps manual reconciliation, not automatic conversion to success. |

Core-Guarded can miss a premature COMMIT/ROLLBACK followed by a same-connection
replacement, or even a successful no-op end after an earlier opposite end. The
Owner accepts this architecture-level limitation, **not a claim that it is always
detected**. An acknowledged rollback therefore cannot prove that no previous writes
were committed. UNKNOWN/STOP applies to *detected* uncertainty; no blanket detection
claim is allowed. Concrete admission predicates and diagnostic vocabulary are review
gates Q01/Q02; implementation tests must demonstrate observable faults as well as
honestly report blind spots.

Session consume/revision checkpoints stay before the first business write, then
before/after each side effect. A failed checkpoint after an acknowledged commit does
not undo it. Preserve the consumed/uncertain result and require manual reconciliation;
never reopen a confirmation. Only an accepted booking outcome permits selected
post-commit actions, which keep separate PDF/mail statuses. A crash or lost session
has no durable module journal/recovery. External delivery and hook side effects
cannot be rolled back and must never be repeated automatically.

## 6. Explicit selection, confirmation binding and upgrades

Proposed administrative rules (exact setting names/storage/UI: Q04, not implemented):

1. Detect supported family/capabilities without a privilege-based fallback. New MySQL
   installs explicitly initialize/present Core-Guarded as default **after release
   qualification**. MariaDB continues Snapshot. Unsupported/unknown configured
   profile or family mismatch refuses booking with a clear reason.
2. Existing MySQL installations retain Enhanced. An absent setting must not be
   interpreted as evidence of a new install. Where origin cannot be distinguished,
   require explicit administrator migration choice before booking, preserving old
   Enhanced semantics meanwhile or refusing if its prerequisites fail. No silent
   conversion on upgrade, reactivation, 1142, lost consumer or instrumentation failure.
3. Store normalized family/profile, configuration revision and admitted functional
   scope with each server-held preview/confirmation. Bind them to owner/entity and
   request revision. Re-resolve and compare before consuming a booking and before
   each member; a changed profile invalidates unconsumed confirmations and requires
   fresh eligibility/preview/consent. Old sessions lacking binding must reconfirm.
4. No mid-transaction profile change. If an admin change is detected between members,
   stop remaining work and require a new run; do not relabel prior outcomes. If
   detected while a member is open, freeze further business calls and end only using
   the original context's safe rollback admission, otherwise UNKNOWN/STOP. Precise
   session/config race handling must pass Q04 review; never switch an active adapter.
5. Configuration is server-controlled and admin-only. Neither direct POST, role,
   permission failure nor a saved historical session can enable invoice/autocreate
   in Core-Guarded. Keep a profile/evidence label on completed outcomes so later
   settings do not retrospectively promote their guarantees. No secret SQL/identity
   data should be exposed to users.

No new module tables, journal or schema migration is permitted. Existing module
configuration facilities may be designed in the separately authorized implementation;
CF-00 does not write constants or choose a production migration mechanism.

## 7. Read-only preview versus booking readiness

At this baseline `msmRequireBookingDatabase()` calls `beforeBegin()` and engines
for preview/confirm; `subscription.php` invokes it before evaluation and booking.
Done/processing result display already bypasses new-operation readiness. CF-01 must
separate these responsibilities without making a preview into transaction proof.

Read-only preview retains authentication, object/entity access, CSRF on edits,
server-held options, validation/escaping/translations, revision and batch limits.
It must not BEGIN, SET transaction/session settings, acquire business row locks,
consume a booking confirmation or execute writes/side effects. Missing Enhanced
privileges need not suppress readable preview; show a clear booking-unavailable
reason/profile/scope and deny the final booking server-side. A snapshot of capability
is advisory and must be checked afresh at booking. Stale profile/feature bindings
require a new confirmation. Readability of stored completed/processing results and
replay rejection must survive unavailable new-operation readiness; never resubmit
business work to render a result. Retain result-link access checks.

## 8. Risk acceptance, alternatives and rule conflicts

The [risk register](ADR-0001-risk-register.md) is this ADR's threat analysis, not a
second requirement matrix. All 71 existing IDs/evidence assignments and historical
results remain untouched. The Owner accepts weaker physical proof for Core-Guarded:
undetected same-connection replacement, unbound original completion and lack of
actual-event isolation observation. Hidden foreign contexts at entry remain a
separate unresolved admission gate, not a newly invented Owner waiver. That acceptance is bounded by retained business
controls and future tested operating assumptions, not approval of arbitrary hooks,
false success after detected faults or unqualified invoices.

| Alternative | Disposition |
| --- | --- |
| Require Enhanced P_S universally | Retained optional stronger profile; rejected as universal rule by ADR-01/03/12 because of restricted-host availability. |
| SH-01 SAVEPOINT/connection/isolation equivalence construction | Historical NO-GO retained. Marker recreation/rotation, isolation ordering and ambiguous completion refute the tested equivalence. No SH-02 implementation authority. |
| Silently switch on 1142 or instrument loss | Rejected by ADR-04/05; no catch-and-retry downgrade. |
| Port MariaDB snapshots directly to MySQL | Not equivalent; MySQL lacks that variable in the tested profile. No inheritance of MariaDB's accepted guarantees. |
| Journal, new tables, XA/proxy/driver controller or duplicate SQL business writes | Outside this authorized design; ADR-09 preserves session architecture and Core APIs. No such implementation supplied. |
| Reject all normal completions | Safe refusal alone is not useful contribution support; not the Core-first goal. |

[AGENTS.md](../../AGENTS.md) currently states universal shared-observer/G0/event
ownership, actual-event isolation, fresh provable rollback and confirmed-COMMIT
requirements. They accurately constrain today's code and Enhanced; they cannot be
copied unqualified into Core-Guarded. The minimal pointer added there scopes the new
design and pending review without changing the existing implementation contract.
The prohibition on pre-lock MySQL read views remains shared. The candidate isolation
setup in section 5 needs explicit review, not a general exception allowing workarounds.

The older [invoice architecture](../phase4-invoice-architecture.md),
[implementation plan](../phase4-invoice-implementation-plan.md), validation history,
README and user-facing prerequisites will need a **scoped** consistency pass after
accepted implementation. Do not rewrite them now as if Core-Guarded existed. The
old instruction against parallel architecture documents is satisfied by this Owner-
requested successor ADR with explicit historical/source links, not a competing matrix.

## 9. Compatibility and next gates

Policy from #22/#20: Dolibarr V22/V23/V24, PHP >=7.4 subject to Core's own minimum,
direct mysqli, MariaDB >=10.11 and Oracle MySQL >=8.4; PostgreSQL excluded. Minimums
are intended support boundaries, not blanket version/profile qualification. Current
real profiles include V24.0.3/PHP8.2.35/MySQL Community8.4.13 Enhanced and
V24.0.3/PHP8.2.35/MariaDB10.11.19 Snapshot, with historical V22 evidence recorded
separately. V23 has synthetic/source coverage, **no real integration claim**.
Core-Guarded has **no product synthetic or real Core integration qualification**.

Independent detailed architecture review must first resolve the handoff gates.
CF-01/CF-02 then require separate commission; CF-03 requires existing synthetic
21-job non-regression plus real restricted-MySQL/Core/HTTP and MariaDB/Enhanced
regression with exact profiles, faults, postconditions and honest blind spots.
Synthetic success cannot substitute for those tests. Contribution mail and any
other side effects require specifically permitted tests and separate outcomes.

#22 remains a v0.1.0 release blocker. Do not reopen/merge #19 or build on its source
branch. After accepted implementation on main, prepare a **new** release branch/PR;
selectively reuse validated builder/allowlist/docs only after comparison, rebuild
ZIP/manifest/SHA-256, execute R0-04 exact-artifact integration and R0-07 independent
release review, then obtain separate Owner GO under #20. Former ZIP SHA-256
`61e4ad4bc40a31d87e265b0a05433403d77bbc177899c5f58b7416d5a3653ed6`
remains historical and unchanged, not Core-first release evidence. No tag, release,
general production approval or new database guarantee is granted by this ADR.
