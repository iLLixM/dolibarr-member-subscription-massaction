# ADR-0001 — Transaction profile risk register

**CF-00 OWNER-ACCEPTED – APPROVED FOR DOCUMENTATION MERGE (2026-11-18)**

CF-00 closure: [PR #23](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/23),
normal merge `6247704815cf532ec632616011133c646aa00005`, final reviewed HEAD
`d867c19c57b40e286ec08a9a935c4be138601a8a`;
[external verdict and Owner acceptance](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/23#issuecomment-6985938043):
**APPROVED FOR CF-00 WITH DOCUMENTED SCOPE**. F23-01–07 and K23-01 CLOSED.
The original drafting/review-request status below is historical and superseded for
CF-00; decisions, risk boundaries and unexecuted technical gates remain preserved.
CF-01 implementation status is recorded in the central validation; no Core-Guarded
booking, real D-01 integration or release approval follows from this closure.

## D-01 completion risk addendum — 2026-11-18

Owner evidence acceptance: **ACCEPTED WITH DOCUMENTED LIMITS**. Option A:
**CONDITIONALLY APPROVED**, technically unqualified pending the mandatory
completion/session-checkpoint/logger gate **before CF-02 contribution DML**.
Authority and complete decision: [PR #25 review/Owner record](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/25#issuecomment-6993488134).
The [ADR Owner addendum](ADR-0001-core-first-transaction-profiles.md#d-01-owner-addendum--2026-11-18)
is the current scoped contract for the existing risks below; this adds no new
risk model and does not change EnhancedMySQL/MariaDB evidence or guarantees.

- **R01/R07 — Entry versus completion.** The real restricted MySQL8.4.13/V24.0.3
  library experiment supports ordered SET RR → Core BEGIN and behavioral RR/1568,
  not durable physical identity or actual-event isolation. Six completion cases
  show CHAIN/RELEASE effects despite pre-entry autocommit1: next SET1568/2006.
  Waiting for that next SET would allow unsafe earlier SQL. Preserve preflight
  classification plus immediate terminal STOP, without post-end SQL probes,
  completion whitelist or session/global normalization. Classification stability
  across the supported Core/Agenda path remains **U/G**, not proven by a settings read.
- **R05/R06 — Acknowledged end versus lost ACK.** Member result and batch continuation
  are independent. Acknowledged CHAIN/RELEASE commit keeps SUCCESS while the batch
  stops; acknowledged rollback keeps its business failed/skipped result and stops.
  NO_CHAIN continuation under E10/E12 additionally needs qualified post-end context
  and checkpoint. Core false rollback can reset depth0; failed ends may leave stale
  Core error fields. Lost/unacknowledged/uncertain ends remain UNKNOWN/STOP without
  retry, compensation, another rollback or readback-based success. Checkpoint failure
  after accepted COMMIT is E15: preserve DB outcome, mark result persistence uncertain
  and stop. No claim of original physical completion or crash-safe recovery.
- **R11 — Core/logging and forbidden post-processing.** Core `dol_syslog()` after
  commit/rollback may run before the adapter receives the acknowledged return.
  Prove the actual selected logger path cannot already query a chained/released
  context. Terminal STOP must precede any additional module SQL, profile/config
  read, mail/PDF or next member. Finish standard Agenda/business postconditions
  before end. Unknown extensions/loggers retain the bounded compatibility
  reservation, not a new universal inventory/certification obligation.
- **R12 — Checkpoint/HTTP handler dependency.** Qualify durable, readable result
  persistence and request teardown independent of the terminal DB context. Do not
  assume every configured PHP session handler is file-based. A checkpoint in the
  chained context could report success then disappear at connection release; a
  RELEASE connection cannot serve DB-backed persistence. Only proved independent
  checkpoint/HTTP-result actions may follow terminal end; failure stops and never
  reopens/resumes consumed consent. Test both acknowledged outcomes, E15 and lost ACK.
- **R09/R13/R15 — Scope, partial batches and rollout.** The Owner provisionally accepts
  controlled CHAIN/RELEASE partial processing (normally one transactionally processed
  member). Clearly distinguish unprocessed selections from success/failure/unknown.
  Safe multi-member operation remains the target; permanent support and Option C
  await separate decisions. No general qualification follows from the37 completed
  measurements/controls,1 PARTIAL or synthetic CI. No silent profile downgrade,
  new transaction manager, general P_S prerequisite or release approval.

The evidence is **A** only for the bounded measured library/driver conditions;
Option A's implementation, session/logger and HTTP guarantees remain **U/G**. Required
new proof: actual pre-end classification/no intervening SET→BEGIN SQL; all three
completion modes; supported Core/standard-Agenda/logger call path; independent
session checkpoints and controlled response/end; no terminal follow-on actions;
lost ACK and checkpoint failure. Independent assessment must close this gate before
contribution DML; **NOT EXECUTED** in AP2. Later CF-03 and release gates remain open.

[Historical experiment](../d01-loaded-core-feasibility-v1.md) retains D-01 CHANGES
REQUIRED, instrumentation/factory scope limits, raw synthetic traces, checksums and
PARTIAL. The original CF-00 register and time-specific pending/NOT EXECUTED statements
below are preserved; apply this dated addendum to current completion/continuation
risk handling. Owner risk acceptance is not technical implementation approval.

**Historical drafting/review context:**

Companion to the [ADR](ADR-0001-core-first-transaction-profiles.md) and
[external handoff](ADR-0001-review-handoff.md). Owner: repository Owner;
architecture-level risk acceptance date: 2026-11-18, [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22),
ADR-01–ADR-12. Baseline: `48d196cfb04421d775ab4e6757083f6a755121df`.
Follow-up authority: Owner-approved F23 dispositions and D-01/D-02, 2026-11-18,
recorded in the [ADR](ADR-0001-core-first-transaction-profiles.md#follow-up-owner-decisions-d-01-and-d-02--2026-11-18).
Initial external review at `8fd9ca321a8090ec8c91a084efffc06d943bf9e3` remains
CHANGES REQUIRED BEFORE CF-00 APPROVAL; independent F23 confirmation is pending.
This is a design risk matrix, not another requirement-to-test matrix. Historical
71-ID assignments and evidence stay unchanged. No tests were run for this register.

## Reading acceptance and evidence

- **A — accepted architecture residual:** Owner expressly accepts reduced physical
  proof in Core-Guarded (ADR-12), or retained snapshot/session limitations. This does
  not accept a faulty implementation or broaden prior profile qualification.
- **U — unproved control:** proposed Core-Guarded technical behavior has no product
  implementation/qualification. An existing Enhanced/MariaDB control is source/
  historical evidence only, within its documented profile; not a fresh PASS here.
- **G — gate:** unresolved detailed contract blocks architecture acceptance where
  material; missing implementation/real evidence blocks later qualification/release.
  A and G can coexist. Owner risk approval cannot turn U into proven behavior.

D-01/D-02 resolve the design choices, not their technical proof. Required tests
below are **future / NOT EXECUTED for Core-Guarded**, with loaded-Core entry/order
proof before production contribution DML. That test gate is not a requirement to
run DB tests in the documentation-only CF-00 PR. Preserve
historical SH-01 counterexamples and I1-05 failures; do not count proposed negative
cases as completed. Q numbers refer to the handoff's review questions.

| Risk ID | Scenario/cause | Impact | Core-Guarded control | Enhanced control | MariaDB control | Residual risk | Mitigation | Required test/evidence | Owner acceptance | Open question |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| R01 | Foreign transaction hidden at logical depth 0; premature/implicit end | Core BEGIN could commit foreign work; partial result | U, D-01: successful SET TRANSACTION ISOLATION LEVEL REPEATABLE READ immediately before Core BEGIN on same connection; zero intervening SQL; refuse 1568/failed entry/known foreign state without rollback. Depth/identity are application controls | Existing fresh idle/event-bound admission/completion | Existing native/depth/active-idle snapshots | Bounded entry check is not later transaction identity; CG can still miss later same-connection replacement | D-01 preferred recipe; autocommit=1 entry prerequisite, no completion-value whitelist or session/global changes; actual invalid entry stops | Later loaded-Core/restricted MySQL foreign-BEGIN/depth0/1568 and no foreign end, exact query order and relevant completion behavior under autocommit=1 | D-01 approved 2026-11-18; ADR-12 accepts later physical-proof reduction, not unsafe foreign entry. U/G: implementation evidence pending | Q01: confirm bounded admission is precise, without demanding Enhanced proof? |
| R02 | Same native connection COMMIT/BEGIN or ROLLBACK/BEGIN; marker recreation | Original transaction replaced invisibly, rollback targets replacement | U: latch known replacement; no nonce-as-identity claim | Same own event + fresh save/release progression at checkpoints | Active snapshot can look unchanged | CG/MariaDB may accept indistinguishable endpoints; no continuous tracking in any profile | State limitation plainly; do not revive SH-01 equivalence | Preserve SH POC-05/06/10; later real Core injections report both detected and undetected cases | A: explicit CG same-connection limitation; U/G for guard correctness | Q01/Q02: how is evidence level carried without reusing physical `owned` semantics? |
| R03 | Reconnection, handle/server replacement or network loss | Writes continue on a new context; outcome ambiguity | U: immutable DoliDB/native handle/ID binding, errors latch stop | Existing native/server/thread/event identity + latch | Existing native handle/ID + latch | In-flight server result can remain unknown; no recovery by reconnect | No transparent reconnect/retry or ownership transfer; manual reconcile | Real own-connection loss, replacement and delayed errors; zero later business/PDF/mail calls | ADR-08 mandates stop; no acceptance of continuing after detected swap. U/G | Q02: exact error and identity checkpoints? |
| R04 | Core nesting, inner rollback only decrements depth, or hidden SQL error in negative Core result | Partial writes treated as undone or completed | U: expected outer depth 1 at returns, retain classified errors; E04/E05 admit one outer rollback under RB, E09 stops mismatch; never drain counters | Existing depth/event checkpoints retained | Existing depth/status checks retained | Balanced counter is not proof of semantic success or event ownership | Check Adherent::subscription, Subscription::create, update_end_date and standard Agenda nested paths; ordinary business errors need outer rollback | Future real nested positives/business errors/unbalanced depth; retained error code/call and independent all-table postconditions | F23-03 accepted with limits; no waiver for ignored errors. U/G at CF-02/03 | Q01/Q02: do RB and error precedence close the documented nesting contract? |
| R05 | Failed/unacknowledged COMMIT or later session-save failure | Persistent booking uncertain to caller; unsafe duplicate | U: E12 normal ack/depth/identity + business checks = application success; E13 uncertain attempted end = UNKNOWN/STOP; E15 separates accepted DB end from uncertain result persistence | Existing ack + original COMMITTED event before idle probe | Existing ack + idle snapshot | CG acknowledgement may relate to invisible replacement/no-op; no durable crash recovery | No rollback/retry after attempted uncertain commit; no readback redemption; checkpoint before side effects, manual reconciliation | Future real forwarded end/reply loss and checkpoint failure after acknowledged commit; zero later members/delivery | A: CG unbound physical completion; ADR-08 no automatic recovery. F23-03 adopted, U/G for implementation | Q02: is DB outcome distinct from session/delivery uncertainty? |
| R06 | Failed/lost rollback, Core resets depth despite SQL failure, foreign context | Partial result or end of foreign replacement | U: RB requires admitted acknowledged begin, stable context/depth1, classified business/S error, no COMMIT attempt or detected uncertainty; one Core rollback only. E10 ack/depth0 = application rollback; E11 otherwise stops | Existing fresh owned context + original ROLLED BACK event | Existing fresh logical/active context then ack/idle snapshot | CG acknowledged rollback is no original-event proof or guarantee against earlier invisible commit | No physical proof requirement for CG; no counter draining, repeated rollback or cleanup of foreign context; E08 uncertainty dominates | Future known business/S rollback, false/lost ACK, depth0 despite failure and detected replacement; independent rows, no follow-on DML | A: weaker CG end evidence; F23-03 ACCEPTED WITH LIMITS. U/G: qualified behavior still required | Q02: is RB deterministic under the E01–E15 matrix? |
| R07 | One-shot/session isolation differs; SQL consumes next setting; completion behavior under relevant settings | Stale read view, unsafe end assumptions | U, D-01: Preflight → SET RR → immediate Core BEGIN → Locks → business reads; no intervening SQL; autocommit=1 checked before SET; no completion-value admission exclusion; no actual-event isolation requirement | Actual own ACTIVE-event RR admission unchanged | Existing snapshot policy, no new event/isolation gate | CG cannot universally detect later overrides; completion variants remain to be tested, not generally excluded; detected unexpected ends/connection uncertainty still stop | D-01 normative preferred design; no session-default substitution or global/session rewriting; incompatible recipe returns to Owner | Later real loaded-Core restricted account, four session/one-shot levels, metadata/late override negatives, relevant completion variants under autocommit=1 and behavioral reads before contribution DML | D-01 APPROVED 2026-11-18; A: no universal event proof. U/G: later technical gate, not new CF-00 DB-test requirement | Q03: confirm fixed recipe and fallback-to-Owner rule, not equivalent event control? |
| R08 | 1213 transaction abort versus 1205 statement/transaction timeout | Unsafe rollback/continuation or lost work | U: E06 1213 invalidates context, UNKNOWN/STOP, no module rollback/retry. E07 1205 uses E05 only with qualified S/RB and known rollback_on_timeout OFF; ON/unknown/unqualified uses E08 | Existing observer must still admit rollback, otherwise stop | Existing snapshot context contract retained | Numeric error/depth alone cannot prove surviving context; missing native error facts stay unknown | Finite code + call + settings mapping; no catch-all classification; successful E10 alone permits next member; never repeat failed one | Future separate deadlock/timeout profiles, ON/OFF or unknown configuration, masked errors, no later SQL/delivery after uncertainty | ADR-08; F23-03 ACCEPTED WITH LIMITS. No claim tests or mappings already qualified | Q02: are conservative defaults and bounded recoverable branch precise? |
| R09 | Concurrent selection/duplicate or stale confirmation | Duplicate contribution/1062, wrong end date, lost consent | U: member/type locks then fresh rights/entity/fingerprint/overlap and Core R16 checks; batch bound | Preserve FIX01 lock-before-metadata and I107 predicates | Preserve MDB-07/08 controls | No whole-batch serialization or exactly-once across lost sessions; arbitrary out-of-band writers unqualified | One member per tx; no selection truncation/retry; new consent on changed fingerprint | Real waited-lock winner/loser, overlap/disjoint/history/NULL, loser zero DML, independent SQL; Enhanced/MDB regression | Existing bounded risk unchanged; U/G for new CG path; data-integrity regression blocks qualification | Q03/Q06: snapshot ordering and confirmation invalidation races? |
| R10 | Non-InnoDB actual table or omitted standard Agenda path | Persisted partial rows after rollback | U, D-02: conditional actual Core table checks before BEGIN/after locks; include MEMBER_SUBSCRIPTION_CREATE → ActionsAuto → ActionComm::create → ACTION_CREATE effects | Existing conditional Core/Agenda and invoice/customer engine gates | Existing conditional gates retained | Extensions can add writes not in ordinary path; no universal scanner | Target actually reached paths/tables/extra fields; Agenda OFF must not fail on unrelated tables | Future standard Agenda ON/OFF, event/member/actor/resource links, nested errors and rollback; path-specific extra writes only if reached | D-02 APPROVED 2026-11-18; U/G: targeted evidence later; no destructive table conversion authorized | Q05: is the standard and indirect ACTION_CREATE boundary sufficiently defined? |
| R11 | Extension transaction control, irreversible filesystem/SMTP or extra hooks | Effects survive rollback; premature/duplicate delivery | U, D-02: known transaction-endangering combinations incompatible, reject before entry or E08/E09 if detected during work; unexamined hooks carry documented compatibility reservation | Existing post-commit boundary; arbitrary hooks not atomically covered | Existing bounded post-commit behavior | No general guarantee for unknown foreign effects; CG can miss later end; optional mail not qualified here | No universal third-module inventory/test/block duty or mandatory whitelist. Reject known unauthorized irreversible paths; document known incompatibilities, no auto resend/regeneration; mail separately qualified | Later targeted standard hook/Agenda contract; controlled known incompatible examples, no universal campaign; optional mail only under separate authorization | D-02 APPROVED 2026-11-18; F23-04 ACCEPTED WITH LIMITS; A: bounded irreversible-effect risk, not arbitrary-hook safety | Q05: confirm compatibility reservation versus known incompatibility, with no universal module ban? |
| R12 | Session loss, crash, consumed POST replay, no persistent journal | Outcome lost, duplicate attempt or lost side-effect status | U: retain owner/entity/revision, strict server-held options, consumed request and checkpoints | Existing replay/session behavior | Existing replay/session behavior | No durable exactly-once recovery; session loss needs manual reconciliation | Never reopen/resume/rebook automatically; result display independent of new admission | Authenticated real positive/replay, stale/direct POST, session-save failure and lost result; unchanged postconditions | A: ADR-09 session architecture; ADR-08 no retry. U/G for changed boundaries | Q06: profile binding before consume and readable old results? |
| R13 | Silent downgrade, admin race, stale profile revision or ambiguous upgrade | Unintended weaker/financial permission | U: CF-01 authoritative server-side revision and atomic comparison or defined per-member snapshot; cached constants not freshness evidence; original context fixed, stale confirmations rejected | P_S/instrumentation failure denies configured Enhanced; no fallback | Family-bound Snapshot default retained | Concurrent config/request effects require concrete revision semantics, not global serialization | Explicit admin decision for unknown legacy origin; no active adapter switch; record outcome profile | Future concurrent admin/booking requests, cached constants, absent/invalid config, reactivation/migration, mid-run change and old sessions | ADR-04/05/07 mandatory; F23-05 DEFERRED TO CF-01, not an additional CF-00 blocker | Q04/Q06: implementation must state/test its linearization boundary without global locking? |
| R14 | Controller/service/direct Invoice or customer entry bypass of CG feature limit | Unqualified financial/multi-object writes | U: CF-02 server-owned profile/feature context required at all relevant module controller, Booking, Invoice/customer service entries; missing context denies invoice/autocreate/PDF | Preserve existing admitted financial scope | Preserve existing admitted financial scope | Contribution tests cannot qualify invoice/tax/customer/PDF | No general Core API changes; module-side guard before any prohibited business call; separate CF-05 gate | Future direct service/HTTP/old-context negatives, zero prohibited DML and small Enhanced/MDB invoice regression | ADR-06: feature not enabled. F23-06 DEFERRED TO CF-02; not an additional CF-00 blocker | Q07: all relevant module call surfaces covered without altering Core? |
| R15 | Old artifact/CI or version range promoted to new profile/release proof | Unsupported operational promises or unsafe rollout | No implementation evidence yet | Exact historical MySQL8.4.13/V24/PHP8.2.35 only | Exact historical MariaDB10.11.19/V24/PHP8.2.35 plus separately recorded history | Source/synthetic coverage is not real DB/ZIP qualification; V23 real unproven | Pin code/profile/config/artifact, preserve histories; new release PR and R0-04/R0-07/Owner GO | CF-03 real restricted Core/HTTP, existing 21-job CI, non-regression, exact future ZIP | ADR-11; G. No release or blanket version approval | Q08: evidence and release-gate completeness? |

## Evidence boundaries and disposition

Existing source and historical tests are indexed in the ADR and
[coverage legend](../test-coverage.md). SH-01's real restricted MySQL8.4.13 native
SQL, reply-loss and isolation counterexamples are applicable negative evidence;
its nesting model is not loaded Core, and it ran **no real Dolibarr business
integration**. Neither historical P_S tests nor MariaDB 9/9 qualify Core-Guarded.

The focused F23 follow-up reviews the adopted D-01/D-02 admission/order, E01–E15
classification and bounded extension contract. Its verdict is pending. The initial
HIGH findings remain historical review facts, not external closures by this author.
The [disposition matrix](ADR-0001-review-handoff.md#finding-disposition-f23-01-through-f23-07)
separates corrections from CF-01/CF-02 implementation duties; F23-05/06 are not
additional CF-00 blockers. Future unexecuted tests alone do not prevent acceptance
of a precise documentation contract. They remain mandatory before the applicable
implementation/production-DML/qualification/release gate. No Owner acceptance is
converted into an implemented or independently proven control.
