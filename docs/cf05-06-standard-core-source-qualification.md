# CF05-06 – Bounded standard-Core source and filesystem qualification (PR #38)

> **CURRENT BINDING OWNER DECISION — 2026-11-22 / PR #38, CORE PARITY + MASSACTION SAFETY (DRAFT, NOT YET INDEPENDENTLY REVIEWED).** The Owner expressly **supersedes the earlier CF05-06 exact-Core/trigger-blob allowlist and PROV-directory prohibition**. The module trusts Dolibarr's normal `Adherent::subscription()`, `Societe::create_from_member()`, `Facture::create()/addline()/validate()`, numbering/PDF models, enabled triggers/Agenda/Logevents and extension behavior as the Core single-member workflow does. **Full `CoreGuarded` invoice, conditional auto Third Party, post-COMMIT PDF and optional verified-attachment email stay enabled**, along with `EnhancedMySQL` and `MariaDBSnapshot`. Removed as normative admission checks: 15-version invoice source hashes, standard trigger hashes, prohibition of custom enabled triggers, Agenda file-copy/Logevents settings and `PROV` existence check. Do not rebuild those allowlists under F38-08/09. Core model lookup supports installed model roots without unsafe model-name paths.
>
> **STILL BINDING:** Massaction's per-member rights/entity, server-confirmed preview/fingerprint and user/profile revision, controlled original DoliDB Guard, effective transactional-table/engine checks, consumed one-shot session, confirmed nonterminal completion, durable post-COMMIT PDF/mail checkpoints, verified attachment/recipient identity, and `UNKNOWN/STOP` on uncertain completion or external effects with **no automatic booking/PDF/mail replay or foreign rollback**. No guarantee that Dolibarr Core SQL and filesystem are jointly atomic or that arbitrary custom extensions are benign. **F36-02/P2 = OWNER-ACCEPTED CORE RESIDUAL RISK** (conditional pre-outer-COMMIT `PROV` rename); it is not proven impossible or technically fixed. **F38-07/08/09 = SUPERSEDED BY OWNER ARCHITECTURE DECISION**, not historically falsified or independently technically CLOSED. F38-01..06 remain CLOSED per prior reviews and regressions. F36-01 remains historically CLOSED on the old default-deny branch, while its later Owner activation is binding.
>
> **EVIDENCE AND RELEASE:** Newly added `test/unit/cf05-core-parity.php`, positive/negative existing/new customer and contribution/mail orchestration, standard/custom model regression and 9-/21-/Hosted synthetic CI are development evidence, **not real A–D**. Real CF03-NORMAL A–D needs separately Owner-authorized cases, F32-MR-01 private launcher/read-only preflight, A/C serialized use, DB/invoice/PDF/file/SMTP evidence, CF05-07/Owner risk review, later exact-ZIP/hosted D and separate release GO. Purposefully destructive CF03-FAULT is DEFERRED. Earlier statements below that require Core source/trigger pinning or productive CoreGuarded default deny are preserved **only as dated history**, not current policy. [Current decision and finding map](owner-core-parity-2026-11-22.md).


**Date:** 2026-11-22. **Status:** SOURCE-LED BOUNDED MITIGATION IMPLEMENTED / OFFLINE TEST PENDING / INDEPENDENT FOLLOW-UP PENDING. **NOT** real A–D DB/FS/SMTP acceptance, not general third-party-module certification and **NOT** an unconditional F36-02 closure. [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22), ADR-06/11/12 and D-01/D-02 govern this report.

## Owner product scope and actual admission

The Owner requires **full** CoreGuarded, EnhancedMySQL and MariaDBSnapshot contribution/Third-Party/invoice/PDF/mail functionality. PR #38 retains `MsmTransactionProfile::coreGuardedFinanceQualified() === true` **for qualified configurations**. The runtime finance gate is not reclosed or substituted with an unreviewed test-only toggle. Instead the CoreGuarded Invoice service now checks a **supported Core-source/trigger/configuration boundary**:

- **Before the FIRST Booking BEGIN/business write:** `MsmInvoiceService::checkTables()` calls `MsmCoreFinanceQualification::requireReady()` only for CoreGuarded invoice work. If unsupported, `MSMCoreFinanceUnqualified` rejects entry before contribution/Third-Party/invoice DML; old feature rights, CSRF, original file-session and guard admission still apply.
- **At directly callable invoice service:** `MsmInvoiceService::create()` also requires the bounded qualification, the authoritative profile and the original active guard/file-session capability, before its first customer/invoice API.
- **Immediately before Core `Facture::validate()`:** `requireProvisionalPathSafe()` ensures a freshly created standard `(PROV<id>)` reference and no existing provisional file/directory/symlink in `$conf->facture->dir_output`. A preexisting directory could be renamed by Core before the outer Booking COMMIT, so the module refuses that case **without** touching the directory. Because earlier Core SQL business calls already occurred, CoreGuarded still applies its strict **UNKNOWN/STOP**, not a blind compensating rollback.

**Safety limitation:** A pre-`validate()` check **alone** cannot prevent a trigger *inside* `BILL_VALIDATE` from creating a new provisional directory after the check; therefore this control is explicitly paired with the restrictive source/trigger qualification described next. No guarantee for arbitrary triggers, unknown modifications, filesystem races from external actors or extra Core event behaviors is claimed.

## Exact pinned Core source evidence

Dolibarr `Facture::create()` inserts the invoice as `(PROV)`, then sets the stored reference to `(PROV<id>)`. The fresh module flow sets normal type 0, creates a single subscription-linked invoice and line, and calls `Facture::validate()`. The validator assigns `oldref` from that provisional reference, invokes the standard `BILL_VALIDATE` trigger, then conditionally performs a **non-SQL** source-directory rename and contained-file renames if `file_exists($dirsource)`. Only afterwards does its nested Core COMMIT occur. The module's outer Booking COMMIT occurs later. A later SQL rollback cannot reverse such a rename.

**Read-only source verification performed on all 15 Full-CI pinned Core versions (V22.0.0–V22.0.5, V23.0.0–V23.0.4, V24.0.0–V24.0.3)**. All 15 have the conditional pre-outer-COMMIT rename and the same preconditions, although the full source bytes differ by minor version. Every `htdocs/compta/facture/class/facture.class.php` Git blob is pinned exactly in `MsmCoreFinanceQualification`. Git blob identity is `sha1("blob ".strlen($bytes)."\\0".$bytes)` (with a real NUL byte in PHP). A patched file, unknown version or unreadable/symlinked Core source fails before the transaction. The matching source-blob assertion executes in offline CI against the actual checked-out Dolibarr Core under `DOLIBARR_TEST_ROOT` (not solely a synthetic mock).

Representative immutable Core references:

| Dolibarr | Version/pinned source | Core validator and rename |
| --- | --- | --- |
| V22.0.5 | `88ad132763be45e9620128c1830e41493e1cdd05` | [`Facture::validate()` around L3918–3949](https://github.com/Dolibarr/dolibarr/blob/88ad132763be45e9620128c1830e41493e1cdd05/htdocs/compta/facture/class/facture.class.php#L3918-L3949) |
| V23.0.4 | `cb82037066c1c71f7c867482e9e92975222dfc14` | [around L3978–4009](https://github.com/Dolibarr/dolibarr/blob/cb82037066c1c71f7c867482e9e92975222dfc14/htdocs/compta/facture/class/facture.class.php#L3978-L4009) |
| V24.0.3 | `5c6c7ff6a5112413b1c27221c8d11750afad56f8` | [around L4043–4074](https://github.com/Dolibarr/dolibarr/blob/5c6c7ff6a5112413b1c27221c8d11750afad56f8/htdocs/compta/facture/class/facture.class.php#L4043-L4074) |

## Explicitly supported trigger and configuration boundary

Core's `CommonObject::call_trigger()` invokes `Interfaces::run_triggers()`, which enumerates `/core/triggers` and `$conf->modules_parts['triggers']` and runs enabled `interface_*_<module>_*.class.php` files. It does not certify the actions as DB-atomic, so merely checking the SQL tables is insufficient.

The **qualified** default standard scope is:

1. The **exact pinned** `Facture` source for the selected supported minor version; no modified or unknown source.
2. Standard Dolibarr `Logevents` and `Agenda` triggers only, with their **exact pinned Git blob** source content. No other enabled Core or third-party trigger file in the executable registry; disabled third-party trigger modules do not block default operation. Any custom/modified/unreviewed enabled trigger is **INCOMPATIBLE pending separate source review**, not assumed harmless.
3. Auto-Agenda ON/OFF is allowed through existing database engine/ownership/readback checks, but **`MAIN_COPY_FILE_IN_EVENT_AUTO` must be OFF** to exclude pre-COMMIT Agenda file copies.
4. `MAIN_LOGEVENTS_<event>` must be inactive (or all Logevents disabled) for the reached subscription, customer/contact and BILL CREATE/LINE/VALIDATE events to avoid unqualified nonstandard audit-table effects.
5. Core invoice output root must be defined; a preexisting `PROV` source path is rejected before `validate()` without rename or cleanup. New invoice PDF/attachment is still generated **only after suitable acknowledged nonterminal outer COMMIT and the original persisted one-shot checkpoint**.
6. Existing invoice/Third-Party, product, Agenda and contact engine checks remain independent; stock-on-validation, unusual local taxes/packaging and unsupported plan/model configurations remain rejected by the existing InvoiceProfile.

**Qualified vs excluded:** This guard does **not** reject CoreGuarded finance globally: standard pinned V22–V24 with the above supported trigger/file configuration can execute contribution, invoice (existing/new customer), the post-COMMIT PDF and optional mail. It **does** fail closed on unsupported Core/source/trigger/FS combinations *before beginning* the invoice transaction. In contrast, an unexpected provisional directory discovered just before Core validate yields STOP because partial SQL work may already have happened.

**Known limits:** External processes can concurrently create files and unrecognized custom hooks can produce non-DB-atomic effects. No physical CoreGuarded transaction-event identity is asserted; ADR-12 weaker observability is unchanged. External file-creation races are **not** proven impossible by these checks; actual real file/trigger integration still must be tested. Therefore F36-02 and F38-07 are **MITIGATION IMPLEMENTED / INDEPENDENT REVIEW PENDING**, not independently CLOSED.

## Additional read-only Core path evidence and open qualification

- `Facture::create()` uses nested Core BEGIN/COMMIT and optional `BILL_CREATE`; `addline()` is a nested Core operation; `validate()` invokes `BILL_VALIDATE` before the filesystem rename and nested Core COMMIT. Same original DoliDB remains required and engine/readback checks remain mandatory.
- `Societe::create_from_member()` and optional `CONTACT_CREATE` are reachable for the new Third Party branch. The pinned standard Agenda trigger runs actioncomm persistence when configured. Full live ON/OFF postconditions, actual contact/Agenda triggered writes and filesystem outcomes are **NOT VERIFIED**.
- Standard `ActionsAuto` contains optional `MAIN_COPY_FILE_IN_EVENT_AUTO` file-copy behavior; this setting is intentionally unsupported while CoreGuarded finance runs.
- The guard rejects unqualified active third-party triggers even if they happen not to touch invoices; support for an additional extension requires **source-based review and explicit additional configuration qualification**, rather than an arbitrary user-controlled whitelist switch.
- No destructive CF03-FAULT real test, target DB write, actual SMTP send or exact packaged ZIP was executed in this CF05-06 step.

## Offline negative tests and remaining gates

`test/unit/cf05-core-qualification.php` checks pinned source acceptance, unknown or modified source rejection, qualified standard trigger bytes, unqualified extension enabled/disabled behavior, Agenda file-copy and Logevents settings, valid missing-PROV path, unsafe existing PROV file/directory and invalid provisional references. All standard/full/hosted CI pipelines invoke it with their actual exact pinned Core checkout. **Offline CI passing is a development requirement and not real integration evidence.**

Independent reviewer MUST examine whether the source/trigger allowlist is actually sufficient for the real V22/V23/V24 Core and standard Agenda/Contact paths, whether its `requireReady()` executes before *any* member DML, whether the path check precedes every `Facture::validate()`, and whether a file can be created after that check by a still-enabled trusted trigger or by external concurrency. Reassess F36-02 and F38-07 **separately** for Development Merge, real A/D writes, operational deployment and release. A positive synthetic answer alone is not F36-02 CLOSED.

The normal sequence remains: independent exact-source review of this mitigation → revised Owner risk/go-no-go → new case-specific authorized A/D real integration with separated DB/FS/mail/attachment postconditions and F32-MR-01 protected launcher → CF05-07 and Owner financial risk acceptance → release-time final CI / exact v0.1.0 ZIP / hosted D / R0-04 and R0-07 / distinct Owner release GO. CF03-FAULT destructive tests remain DEFERRED/OPEN/NOT EXECUTED.
