# Issue #21 — binding safety-contract snapshot

Source: https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/21
Retrieved 2026-11-18. The following source section is retained verbatim.

## Non-negotiable safety and scope contracts

- **No bypass or permissive fallback** when the transaction observer cannot establish safe ownership; unknown state must remain `UNKNOWN/STOP` without automatic retry, replay, rebooking, reinvoicing, PDF regeneration or mail resend.
- Do **not** treat Dolibarr `transaction_opened`, `@@autocommit`, or a successful `begin()/commit()` return alone as proof of physical transaction identity or continuity. Dolibarr Core nesting uses logical counters, not independent SQL savepoints.
- The new backend must detect (or fail closed for) foreign/premature COMMIT or ROLLBACK, foreign transaction ownership, reconnect/handle replacement, a new transaction on the same connection, unexpected implicit commits, misleading isolation state, nested Core begin/commit, failed acknowledgements and uncertain completion.
- Support validated `REPEATABLE READ` semantics; distinguish actual transactional isolation from session defaults and one-shot overrides. A proposed `SET TRANSACTION ISOLATION LEVEL ...` check must be proved safe within the real Core call sequence.
- Reuse standard Dolibarr Core APIs for all membership, customer and invoice writes. Retain one owned transaction per member, eligibility/permission/fingerprint/overlap rechecks, InnoDB engine checks, CSRF/session replay constraints and post-commit PDF/mail boundaries.
- **No operations journal, persistent module tables, schema migrations, new admin privileges, unmanaged database grants or server configuration changes.** No custom PHP extension or privileged server access as a prerequisite.
- Retain the existing qualified MariaDB and Performance-Schema-based MySQL code paths unchanged in behavior. Backend selection must be deterministic, explicitly documented and fixed **before** a business transaction; never switch backends mid-transaction or after uncertainty.
- Support remains bounded: Dolibarr V22/V23/V24 is the Owner-confirmed release compatibility scope (V23 synthetic coverage must not be presented as real integration); Oracle MySQL >=8.4, MariaDB >=10.11; PostgreSQL remains out of scope.
- Do not alter or merge Draft PR #19 or publish/tag v0.1.0 before the new backend, rebuilt ZIP, R0-04 integration and independent R0-07 review are complete. Implement in a separate focused branch/PR and rebase/rebuild the release package only after approval.


