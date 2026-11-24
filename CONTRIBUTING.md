# Contributing

Use short-lived feature branches and pull requests against `main`. Keep each change
small and explain its user-visible behavior and validation. Core changes are outside
this repository. Write identifiers/comments and review descriptions in English.
Use Dolibarr V22 conventions: tabs, established object APIs, translation keys, output
escaping, standard authenticated bootstrap, CSRF and object/entity access checks.
Do not widen PHP requirements without an explicit compatibility decision.

Run the standalone contracts, PHP syntax checks and whitespace validation before
submitting. For hooks, also document the actual UI flow on an approved test instance;
stubs do not substitute for that evidence. Installation and deactivation need tests.
Never run migrations or business processes against unapproved historical data.
Use synthetic fixtures and test mail sinks; never commit credentials, real member
records, database dumps, documents, sessions or local configuration.

Preserve source attribution and GPL-3.0-or-later licensing. Document borrowed code
with its source commit. The Owner elected to retain unreserved module ID 584620 for bounded v0.1.0 (see [Issue #20](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/20)); verify final metadata and collision checks, not a permanent reservation.
Do not treat upstream community inclusion as guaranteed.

Use [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22), [Owner Variant B](docs/owner-core-parity-2026-11-22.md) and [current baseline](docs/current-development-baseline.md) for current architecture, provenance and release gates. Preserve historical requirements/evidence. New real tests require explicit authorization and protected per-case preflight. Development merge is separate from release acceptance.
