# Phase 5 follow-up for B02 and local non-mail qualification

## B02 technical finding and restoration

Source identity for all local runs was
`ba5c9a3121754fcbd4259bb8906ad1dd17f1ce00` with Dolibarr V24.0.3 and
PHP 8.2.35. The Core reference was `5c6c7ff6a5112413b1c27221c8d11750afad56f8`.
A uses MariaDB 10.11.19, while B and C use MySQL 8.4.13 in separate schemas.
The checks used the actual protected runtime role and independent SELECT accounts.

The protected historical B02 session contained an acknowledged nonterminal
commit with a verified physical invoice PDF. Its durable `mail` and
`attachment` values remain `unknown`. The case had no new recorded capture.
The evidence establishes the diagnostic failure discussed below. The mail
and attachment disposition remains **UNKNOWN/STOP**. The unrelated
`DOLIENV-ACC-01` result retains its previously recorded unknown state and
unconfirmed rollback. Neither confirmation was replayed and no new mail
transmission took place.

**Verified failure mechanism.** The historical UTC Core log identifies the
SMTP library reporting the configured numerical loopback host as offline
or invalid. In the installed Core, `SMTPs::_server_connect()` uses the helper
`is_ip()` and a hostname lookup. For that reserved IPv4 loopback literal,
the helper returns 0 and the lookup returns the input unchanged. The
combination selects the error-99 branch before the socket is opened.
An isolated CLI test invoked the same Core method with the PHP socket
functions disabled and reproduced the exact pre-connect error. The later
FPM probe used a direct socket to the capture endpoint and accepted the
SMTP envelope through `RCPT TO`, then issued `RSET` and `QUIT`. That explains
the different observations. This follow-up submitted no SMTP DATA.

The module's post-commit checkpoint and mail adapter remain as deployed.
The selected synthetic template, sender and recipient settings and
physical attachment were checked within the available evidence. The
identified Core host validation is sufficient to explain the logged
failure without a module change. A separate future test can evaluate
a hostname resolving to the same isolated capture listener, subject to
an Owner decision and a fresh confirmation. No environment SMTP setting
was changed in this follow-up.

**Restoration.** The initial protected restore got as far as restoring the
mail selection, then stopped on exact readback because the Core settings
API changed a previously NULL note to an empty string. With the Owner's
CORRECT decision, the continuation restored only the note on that one
setting, conditional on all other fields and the captured configuration
digest. A separate process verified the saved value, type, visibility and
NULL note, plus hashes of all 264 other settings. Independent business
table evidence remained equal before the B non-mail cases. The original
STOP and session result were preserved.

## Local non-mail matrix

The cases required a protected F32-MR-01 launcher, installed-source equality,
profile-specific preflight, authenticated sessions, fresh members and a separate
SELECT baseline. Shared B/C resources were used in serialized windows. Mail
was disabled through the test setting and deselected in every browser run.
After independent reconciliation, the protected helper verified the Documents
inventory and restored the previous test settings. The observations cover this
build, these fixtures and the standard Agenda path.

| Case | Profile | Status | Independently reconciled result |
| --- | --- | --- | --- |
| A-auto | MariaDBSnapshot | PASS | One new customer, contribution, validated invoice and line, subscription link, standard Agenda effects and verified physical PDF |
| C-auto | EnhancedMySQL | PASS | Same covered business effects on the Enhanced profile |
| A-on | MariaDBSnapshot | PASS in corrected continuation | Two two-member batches, automatic and manual ends, later R16 end retained. Four contribution Agenda events with resources. |
| A-off | MariaDBSnapshot | PASS | Two contributions without new events or resources while the contribution auto-event was disabled |
| C-on | EnhancedMySQL | PASS | Two two-member batches, automatic and manual ends, later R16 end retained. Four contribution Agenda events with resources. |
| C-off | EnhancedMySQL | PASS | Two contributions without new events or resources while the contribution auto-event was disabled |
| B-on | CoreGuarded | PASS | Two two-member batches with acknowledged nonterminal completion per member, later R16 end retained and four contribution Agenda event/resource pairs |
| B-off | CoreGuarded | PASS | Two acknowledged nonterminal member completions and zero contribution Agenda events/resources |

**Totals.** Eight planned cases passed in 11 positive confirmations with
20 contributions, two automatic customers, two validated invoices, two
independently verified PDFs and 18 Agenda event/resource pairs. Each case
covered hashes of 24 business tables, existing Documents entries and
settings restoration. No mail capture was added.

Each ON case independently rejected an invalid CSRF token, a stale
confirmation revision and a consumed confirmation replay without any
additional business change. The original A-on harness stopped before
a positive submission because it expected the current valid form to
disappear after rejection of a stale revision. The controller can
render that form again. All 24 business tables matched the post-fixture
baseline at the STOP. The Owner accepted a corrected continuation with
a fresh session, which proved the error response and unchanged business
state before any positive request. The original execution remains
**STOP** and is excluded from the eight PASS cases.

Authenticated role checks on the same baseline showed the mass action
for administrator and integration roles and withheld it from read-only
and denied roles. The existing offline contracts cover expired runs
and a mismatched entity, which were not exercised with live cross-entity
business data. The OFF runs disabled only the contribution auto-event.
The Agenda module remained installed and active.

The mail qualification, CF05-07 and downstream release gates remain
open. This follow-up uses no Hosted D or release activity and makes no
development-code change. Destructive CF03-FAULT remains deferred.
