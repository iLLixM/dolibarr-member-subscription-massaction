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
with its source commit. Retain ID 584620 under the explicit R0-02 Owner exception;
document its unreserved collision risk and require local checking before installation. Final ZIP integration
and independent release review remain mandatory before a release decision.
Do not treat upstream community inclusion as guaranteed.
