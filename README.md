# Member Subscription Massaction for Dolibarr

**Database development status — 2026-11-17:** MariaDB and Oracle MySQL are
implemented through the shared `MsmDatabaseState` observer. The original I1-05
campaign remains **FAILED — CORRECTIONS REQUIRED** (19/20); the first targeted
I105-FIX01 correction **PASSED — CONCURRENCY AND R16 REQUALIFIED** (20/20).
Together they requalify only the bounded MySQL reference profile below.
[PR #13](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/13)
remains Draft for independent I1-07 review; no merge, production or release approval.

**Cross-version admission and P3 baseline — 2026-11-15:** admission now starts at Dolibarr 21.0.0,
with no upper version or exact-patch allowlist. V21 and future releases remain
unqualified. P1 analyzed all 15 tags V22.0.0–22.0.5, V23.0.0–23.0.4 and
V24.0.0–24.0.3. Local PHP 8.2 synthetic runners passed against all 15 pinned
sources; the configured CI matrix has 21 PHP/Core combinations (PHP 8.2 on all
15, PHP 7.4 additionally on all six V22 tags). P3 is formally Owner-accepted with
documented limitations. Independent overall code review remains outstanding;
admission and source analysis are not real integration evidence.

See the [P3 qualification and evidence guide](docs/p3-qualification-summary.md) for
the closed qualification, pinned matrix, supplemental-source correction and limits.

R16 is written only by Core and checked through an observed Core end value plus
independent known-contract postconditions (boundary V24.0.2). Unsupported callable,
inheritance, transaction or configuration contracts are rejected by capability,
not an unknown version number. PDF fallback chooses an available `crabe`, then
`sponge`; an explicitly invalid configured model is never silently replaced.

**Invoice configuration change:** all versions accept only unset/empty, `0` and
`defaultforfoundationcountry` for `ADHERENT_VAT_FOR_SUBSCRIPTIONS`. The latter
continues to call the actual Core VAT function, including its V23+ hooks. Other
values now fail invoice preflight even on V22; its former silent VAT-0 fallback
is intentionally removed. Subscription-only booking is unaffected. Existing
invoice/tax/profile restrictions, version `0.1.0-dev`, temporary ID `584620`,
production/public-release restrictions and historical evidence remain unchanged.

**Historical PR #6 baseline — 2026-11-15: unreleased development module for the exact
Dolibarr V22.0.5 and V24.0.3 targets. Historical bounded V22 G2/G3/G4 approvals
remain valid. The real V24.0.3 C3 campaign is complete for its tested PHP 8.2,
entity-1 profile, including owner-confirmed mail receipt. PR #6 merged on
2026-11-15 as `96f870e789eb26b6eb8a6b8eeb0c79812018b0ab`, the completed
V22.0.5/V24.0.3 compatibility development baseline. Final independent review:
APPROVED FOR MERGE – ALL CONDITIONS FULFILLED; OD-PR6-01/02 fulfilled.
Post-merge CI passed PHP 7.4/V22.0.5, PHP 8.2/V22.0.5 and PHP 8.2/V24.0.3
([run 43660371617](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/43660371617)).
Other versions/profiles are not generally qualified;
production and public module release remain NOT QUALIFIED / NOT APPROVED.**

See [Phase 4 implementation validation](docs/phase4-implementation-validation.md)
for the accepted scope, separate owner/automated evidence and retained limitations.
The completed Phase 4 implementation baseline is merge commit
`dd68f2a4c2601f03b4588c0cca965292a2a1cb60`.
[PR #4](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/4)
is **MERGED** following the final external review **APPROVED FOR MERGE**.
FS4-01, FS4-02, FS4-02-R1 and FS4-03 are **CLOSED**.
GitHub CI on the merge commit passed on PHP 7.4/8.2
([workflow 43410265664](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/43410265664)).
The historical UI-cleanup baseline is `89b40b39337b66f7263ea1b629ee83ac667dd7a4`.
G4-LAYOUT-01 and G4-UX-01 are closed, and subsequent UI cleanups passed targeted
regressions. The Phase 3B
acceptance below is historical and does not replace final Phase 4 acceptance.

Historical V22 Phase 3B booking/database integration and authenticated HTTP replay
tests were performed; manual browser acceptance passed 9/9. The owner confirmed receipt of the
ten original emails. The historical Phase 3B independent final review concluded
**APPROVED WITH CONDITIONS**; its documentation and mail-comment findings were
addressed by the subsequent follow-up. This historical acceptance is not
unrestricted production approval.
See [Phase 3B validation](docs/phase3b-validation.md) for evidence, limitations and
the owner's scoped residual-risk acceptance.

The module adds an authenticated member-list preview, an explicit confirmation and
per-member booking results. It can optionally create a linked third party, a validated
standard invoice and its PDF through Dolibarr Core APIs. It never creates payments or
bank entries. Historical invoice evidence covers the V22.0.5 profile and the separately
tested V24.0.3 C3 profile: standard EUR invoices, quantity 1, VAT 0, no configured
product, Mercure numbering and Sponge PDF. See the
[C3 evidence and limits](docs/phase4-implementation-validation.md#c3-v2403-integration-campaign-2026-11-14);
the completed development merge and fulfilled owner decisions do not broaden the tested profile.
The separate MySQL iteration below adds its explicitly bounded invoice evidence.

## Origin and scope

Derived from the architecture of [Dolibarr PR #42620](https://github.com/Dolibarr/dolibarr/pull/42620),
feature commit `14c4b07ba86e7a320d61e75b28cb88a50ce54f77`.
The module does not import the core history or change core PHP/language files.
The descriptor follows the Dolibarr 22.0.5 ModuleBuilder template; its original
copyright notices are retained. Preview rules are ported by composition into module-owned services; no Core classes are patched.
Code is GPL-3.0-or-later; see COPYING. No KSeF or FacturX code is included.

## Development baseline

Dolibarr **22.0.5** remains the primary implementation reference; **24.0.3** is the
separately tested compatibility target. Local real integration used PHP **8.2**.
PHP 7.4 remains the module syntax minimum. The P2 CI matrix covers the explicit
21 PHP/Core pairs above; it is not a general runtime/PHP qualification claim.
Historically, C2.1 introduced a common codebase with exact policies for
V22.0.5 and V24.0.3: version-correct R16 and PDF fallbacks, explicit rejection of
positive V24 membership VAT IDs for invoices, and both Core mail-hook account keys.
P2 replaces that historical admission restriction with the capability/observer
contract above; unknown version numbers alone no longer reject booking.
[C2.1 and its follow-up](docs/phase4-implementation-validation.md#c21-compatibility-corrections-2026-11-13)
retain the original synthetic evidence; PR6-01–PR6-03 were subsequently closed by
independent follow-up (owner-reported). The later real C3 campaign covers booking,
invoice/customer creation, zero fees, R16, controlled rollback/COMMIT acknowledgement
loss, PDF/ECM, authenticated replay, Chromium/Firefox and one real SMTP delivery.
V23, other V24 patches, V25 and additional profiles have no blanket qualification.
Production remains NOT QUALIFIED. The development merge is complete; its approval
does not authorize release or public distribution.

Module ID **584620 is temporary**, checked against local descriptors and the
[official ID rules](https://wiki.dolibarr.org/index.php?title=List_of_modules_id&oldid=68837).
It is an unreserved development value above 500000, only for approved isolated
tests. Reserve and replace it before distributing a module release. Public source
availability does not imply a released, installable production module.

## Database compatibility

| Family / profile | Evidence and qualification boundary |
| --- | --- |
| MariaDB 10.11, mysqli/InnoDB | Historical bounded V22.0.5 G2/G3/G4 and V24.0.3 C3 runtime evidence remains separate; current branch retains synthetic regression coverage. No new live MariaDB qualification in this iteration. |
| Oracle MySQL Community Server 8.4.13, Dolibarr V24.0.3, PHP 8.2.35 | Real I1-05 plus targeted FIX01 business/fault/concurrency/R16 qualification in the exact instrumented reference profile; independent I1-07 review pending. |
| Other MySQL versions, PHP/Core combinations or configurations | Not generally qualified. The 21 pinned PHP/Core CI pairs are synthetic cross-version evidence, not 21 real MySQL integrations. |
| PostgreSQL | Not implemented; reserved for iteration 2. |

MySQL requires direct `mysqli`, InnoDB for all touched tables, `REPEATABLE READ`,
`autocommit=1` outside explicit transactions and active transaction instrumentation
in Performance Schema. In addition to the application's existing schema rights,
the proven observer needs `SELECT` on
`performance_schema.events_transactions_current`, with the required identity and
savepoint/release-counter fields and `PS_CURRENT_THREAD_ID()` available. No
`PROCESS`, `threads`/`setup_*` SELECT or new global administrative grant is needed
for this profile. Observer queries filter to the current thread; the table-level
SELECT permission itself does not restrict access to that thread's rows.
Missing rights/events/freshness fail closed. Idle calibration reads an existing
InnoDB member row; an empty member table is not silently seeded or accepted.

Booking and Invoice share one DoliDB-bound observer and one outer transaction per
member. MySQL checks native connection/server/thread/event identity, active
SAVEPOINT/RELEASE counter freshness and the expected own completion before a new
idle proof. MariaDB retains `@@in_transaction`, logical depth and InnoDB checks;
its status-snapshot continuity is not upgraded to MySQL event continuity.
FIX01 checks engines before BEGIN and again after member/type and optional
customer/product locks, before ordinary business reads. This prevents an engine
metadata query from creating a pre-lock MySQL read view that preview and unchanged
Core R16 reads would retain. No isolation change, extra COMMIT or Core end-date rewrite.
Uncertain ownership/completion yields `unknown/stop`, without retry or rollback of
a replacement transaction. Stored results remain readable under F01's authenticated
session contract when readiness is unavailable; this does not permit new bookings.

Core-version admission (>=21.0.0) and backend capability checks do not qualify a
runtime profile. V21/future versions, other MySQL editions/configurations, proxies,
session multiplexing/reconnection and arbitrary foreign writers remain unqualified.
See the [MySQL qualification and evidence](docs/phase4-implementation-validation.md#mysql-iteration-1)
for exact tested commits, historical failures, read-only E04 and PDF/mail limits.
Version `0.1.0-dev`, temporary ID `584620` and production/public-release status
**NOT QUALIFIED / NOT APPROVED** remain unchanged.

## Development installation

1. Use an explicitly approved, version-matched Dolibarr 22.0.5 or 24.0.3 test installation. Do not point older
   core code at an upgraded database. Activation writes module configuration.
2. Clone this repository separately. Its root is the module root (no `htdocs` layer).
3. Link or copy that root to `htdocs/custom/membersubscriptionmassaction`.
   Never replace an existing installation blindly. Apache needs traversal/read
   access and permission to follow the link, not write access to module sources.
4. Ensure Dolibarr already recognizes `custom` as an alternative module directory.
5. In Setup → Modules, enable Members first, then Member Subscription Massaction.
   No new tables, document directories, menus or redundant permissions are created.
6. Internal users need both member read and subscription creation permissions.
7. Select members in the member list, choose **Create member subscriptions (bulk)**
   and press the existing **Confirm** button. The hook opens the financial preview.
   Change individual dates, reset one end, or explicitly apply a common start/calendar
   rule. Select invoice and confirmation email independently. Missing linked third
   parties can be created only after selecting the separate explicit option. Then review the confirmation.
   Confirm each displayed overlap explicitly and use **Book confirmed subscriptions now**.
   Missing overlap consent skips that row. An invoice email requires the verified PDF attachment.
8. The preview provides a Back link to the member list without invalidating the
   preview. It remains available until normal expiry. No separate Cancel button is
   rendered; an internal cancel route is retained. Deactivation removes the list action.

V22 clears `massaction` without `confirmmassaction`. The module uses that existing
list contract and `doMassActions` before response output; it does not introduce a
second confirmation form into the core list. It appends an option with return 0.

## Display conditions

Recommended desktop screen resolution: **1440 × 900 pixels or higher**.
The targeted minimum browser viewport for regular desktop use is **1024 × 768 pixels**, qualified
by focused headless Chromium/Firefox layout checks on Dolibarr V22.0.5.
V24.0.3 C3.3-C also checked the normal confirmation at 1440/1024 px; C3.3-B-R1
adds actual result/invoice navigation in both engines. These are automated,
profile-limited observations, not a new V24 manual BFCache or complete UX acceptance.
The owner's completed V22 visual/UX acceptance remains separate evidence.

These are module-specific conditions, not official Dolibarr minimum requirements.
The usable browser viewport can be smaller than the screen resolution. Wide member
tables scroll horizontally within their own table area when space is limited.
The module does not provide a dedicated smartphone interface. Checks at 768 and
390 px viewport widths cover layout regression only; unchanged Core layout
limitations are assessed separately.

## Security and limitations

Selections are strictly positive unique IDs, bounded by `MAIN_LIMIT_FOR_MASS_ACTIONS`
(default 1000). Malformed, duplicate, missing and inaccessible IDs reject the whole
selection. Shared entities follow `getEntity('adherent')` and object access checks.
Permissions and access are rechecked when opening the assistant.
The original selection store keeps IDs for 15 minutes. A separate `msm_previews`
namespace holds at most five owner/entity-bound previews for one hour, with random
128-bit identifiers. Each form carries a revision to reject stale POSTs. Every
refresh reloads business values; POST cannot override member IDs, amounts or types. A run identifier is not a
replacement for authentication or CSRF. POST actions validate the session token;
redirect destinations are fixed internal paths. No untrusted return URL is used.

GET never books. Confirmation is consumed and saved before processing. Member rows
are locked and revalidated within independent InnoDB transactions; the version-specific end-date
update return and actual saved values are checked before commit (V22 latest-start
contribution end; V24.0.3 maximum contribution end). Only confirmed
commits can proceed to PDF generation and mail. Results survive reload within the session; uncertain
outcomes require manual database inspection, never an automatic retry.

Booking implements the mysqli driver with MariaDB or Oracle MySQL and InnoDB only;
see the profile-specific prerequisites and qualification limits above.
At most 50 members may be confirmed together; use smaller batches when transport is
slow. This cap is not a timeout guarantee. A crash leaves pending rows uncertain.
There is no durable operation journal, SMTP queue or exactly-once claim. Core and
third-party actions need not honor the module's member-row lock. Core triggers still
run and can have nontransactional side effects; inventory them before real tests.

The results page uses escaped HTML and remains available without a timed toast. Its
final-submit status indicator resets on BFCache restoration; the complete form remains
usable without JavaScript. See [Phase 4 implementation validation](docs/phase4-implementation-validation.md) and [Phase 3B validation](docs/phase3b-validation.md) for
transaction, crash, hook and unexecuted integration-test boundaries.

## Checks and next phase

Run `php test/unit/run.php` and
`DOLIBARR_TEST_ROOT=/path/to/dolibarr php test/unit/booking.php` and
`php test/unit/maildelivery.php`, `php test/unit/invoiceprofile.php`,
`php test/unit/invoice.php`, `php test/unit/invoicepdf.php`,
`php test/unit/postcommit.php` and `node test/unit/loading.js` (no dependencies
or database bootstrap). The booking runner includes the preview runner and accepts either pinned Core
source checkout; it does not bootstrap the application/database.
See [Phase 4 implementation validation](docs/phase4-implementation-validation.md) for current acceptance and limitations;
[Phase 3B validation](docs/phase3b-validation.md) retains historical subscription-only evidence;
[Phase 3A validation](docs/phase3a-validation.md) preserves historical evidence.
Lint PHP files and run `git diff --check`; CI also checks the initial commit.
Synthetic tests alone do not prove browser integration; the separate owner-performed
UI-01–UI-09 acceptance is recorded in Phase 3B validation. For future checks, follow
[test/integration/smoke.md](test/integration/smoke.md) on an approved installation.
The complete phase-1 requirements and open risks are retained in
[docs/porting-plan.md](docs/porting-plan.md) and [docs/requirements.csv](docs/requirements.csv).
Do not publish a release until a permanent ID, final independent acceptance and
all required operational/release decisions are complete. The bounded C3 PASS is
not a public release or production authorization.

Future changes follow [the test strategy](docs/test-strategy.md). Precise coverage
references remain in [the requirement matrix](docs/requirements.csv); see
[the coverage legend](docs/test-coverage.md). Legacy Phase 3A previews are invalidated for confirmation and normalized to disabled
invoice/third-party options. Confirmation mail remains independent of invoices.
