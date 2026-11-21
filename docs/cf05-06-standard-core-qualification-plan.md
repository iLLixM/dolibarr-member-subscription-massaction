# CF05-06 – V22/V24 standard-Core, transaction and filesystem qualification plan

**Status: OPEN / NOT QUALIFIED / NOT A REAL-TEST OR FINANCE-ACTIVATION AUTHORIZATION (2026-11-21).** Binding references: [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22), [ADR-06/11 and D-01/D-02](adr/ADR-0001-core-first-transaction-profiles.md), the [risk register](adr/ADR-0001-risk-register.md) and the [Phase-3 implementation record](cf05-phase3-finance-admission.md). This is a source-led qualification work plan, not a new Owner architecture decision or proof of completed tests.

## Current exact-source development baseline

- [PR #36](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/36) was **SQUASH-MERGED** on 2026-11-21 into `main` as `d0b15785c60b74aa7ad59a1c687db94193d159c6`. The independently reviewed original HEAD `33cb3da3b389f9df2870bc8c6223c86a959cb467` and the squash merge have the **same Git tree** `6cc4834b9fe5100aca99126885791b3aeff12fef`.
- Independent exact-HEAD final verdict: **`APPROVED FOR DEVELOPMENT MERGE`**; **F36-01/P1 CLOSED**, **F36-02/P2 OPEN / CF05-06 REQUIRED**, no new delta P0–P3. [Standard CI 9/9 SUCCESS](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/44956004981) and [hosted PHP 8.3/V22.0.5 CI 1/1 SUCCESS](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/44956004947) are **synthetic** development evidence, with selected independently observed CF01 101, Invoice 303 and CF05 78 assertions.
- **Operative CoreGuarded finance is not enabled.** `MsmTransactionProfile::coreGuardedFinanceQualified()` returns `false` in production. CoreGuarded `booking` contribution remains admitted; `invoice`, `autocreate`, `sendmail` remain server-default-denied through Preview/Booking/direct Invoice. Positive finance workflow tests use only isolated test-only qualified-profile subclasses. The historically admitted B/C finance paths are not requalified by these results.
- No new exact-source real A/D invoice/customer/trigger/PDF/SMTP evidence, no actual Dolibarr-Core finance replay, 21-job release matrix or new exact v0.1.0 ZIP/hosted-D installation has been produced. The Owner has issued **no** separate finance-admission, real-witness or release GO.

## F36-02/P2 – conditional provisional invoice directory rename

**Verified Dolibarr source behavior:** `Facture::validate()` in **V22.0.5** (`htdocs/compta/facture/class/facture.class.php`, around lines **3918–3948**) and **V24.0.3** (around lines **4043–4073**) conditionally renames an existing provisional invoice document directory (`PROV...`) to a final-reference directory. The file operation occurs before the module's **outer** Booking DoliDB COMMIT and cannot be undone by SQL ROLLBACK.

**NOT YET PROVEN:** Whether the module's actual fresh invoice workflow can create or encounter the provisional source directory under a supported standard-Core/Agenda/trigger/file configuration, and whether a failure following rename can leave a visible inconsistency. Do not label this path safe, impossible, or already producing damage without evidence.

### Required read-only qualification work

1. **Pin source and environment:** Exact Dolibarr V22.0.5/V24.0.3 Core source hashes, exact module SHA, selected module/profile, relevant conf for output directories, existing verified Core/trigger/Agenda modules, filesystem permissions and document model. Record the original source lines and their branch conditions.
2. **Trace invoice lifecycle:** `Facture::create()` → `addline()` → `validate()` → stored readbacks and optional `Facture::fetch()`, identifying provisional folder creation, document generation, internal rename, invoice numbering, nested Core BEGIN/COMMIT/ROLLBACK and any embedded SQL errors.
3. **Qualify both Third-Party paths:** Existing linked customer and `Societe::create_from_member()` with optional `CONTACT_CREATE`; include `COMPANY_CREATE`, `BILL_CREATE`, `LINEBILL_INSERT`, `BILL_VALIDATE`, subscription events and standard Agenda ON/OFF. Determine possible pre-outer-COMMIT file, mail or audit side effects separately from database rollback.
4. **Qualify InnoDB:** Enumerate all reached tables and transaction behavior (including optional contact/Agenda/extrafields/numbering/link tables), standard SQL error propagation, transaction depth/connection identity, applicable `NO_CHAIN`, `CHAIN`, `RELEASE` results and unsupported extension/trigger boundaries. Do not infer MySQL physical transaction-event identity for CoreGuarded or demand unrestricted P_S.
5. **Classify reachability by version/configuration:** `QUALIFIED`, `CONDITIONALLY QUALIFIED`, `NOT VERIFIED`, or `INCOMPATIBLE` with source lines, positive/negative prerequisite paths and visible pre-/postcondition evidence; explicitly differentiate reachability from actual observed inconsistency.
6. **If reachable:** Propose the smallest independently reviewed *pre-admission* mitigation or explicit supported-configuration exclusion. Do **not** use an automatic filesystem rename-back as compensation, blind rollback of unknown Core state, uncontrolled retry or new persistent transaction journal.

## Finite error-provenance and after-commit verification

- **One DoliDB transaction and one active guard per member.** Continue to verify original-owned active/fresh `MsmCoreGuardedTransaction` context immediately before any single owned rollback. Only previously qualified preflight/subscription reasons and locally typed *pre-financial-Core* `finance_precheck` business refusals may qualify. Unclassified Customer/Invoice/SQL/Core/connection/depth/ACK failures remain **`UNKNOWN/STOP`** with no foreign rollback/compensation or automatic new member.
- **PDF/SMTP are not DB-atomic:** Require suitable acknowledged nonterminal completion (normally `NO_CHAIN`) plus independently re-read durable file-session result, single-effect handling, verified invoice PDF path/hash/attachment and actor/recipient checks. Terminal `CHAIN`/`RELEASE` or ambiguous completion prevents new SQL, PDF, mail and next-member work.
- **No automatic physical-transaction or arbitrary-hook guarantee:** ADR-12's weaker CoreGuarded observability is a separate accepted architectural limit, not permission to ignore an actually reachable new file/trigger side effect.

## Safe operator sequencing and release gates

**Without additional Owner case GO:** perform read-only pinned Core/implementation review, static dependency/call-graph analysis, read-only configuration/engine evidence where already authorized, safe isolated synthetic regressions and independent findings. This plan **does not authorize** production feature unlock, database writes, real invoice/Third-Party creation, SMTP delivery, grants/account changes, destructive fault injections or replay of used fixtures.

**Before new real A/B/C witness:** F32-MR-01/P2 remains **OWNER ACCEPTED / NOT FIXED**. A protected, least-privilege launcher under the actual target PHP-CLI user, correct private `CF03_*` parameters and a successful **read-only preflight** must be independently verified. A/C use one schema/webroot and operate strictly **serially**. D requires authorized restricted-hosting evidence isolation and an **exact source-matched new ZIP**. Each new one-shot case requires a *separate* Owner GO and fresh synthetic members/subscriptions; historical POS02/RB01 cases must never be replayed.

**CF05-06 exit:** independent source-grounded V22/V24 Core/standard-trigger/Agenda/engine/FS path qualification and **F36-02 CLOSED or explicitly supported safe restriction with independent review**. Thereafter CF05-07/Owner risk acceptance, separately reviewed server-side finance admission, authorized fresh CF03-NORMAL A–D, full 21-job PHP/Core release CI, new exact v0.1.0 ZIP, hosted D, R0-04/R0-07 and a *separate* Owner release GO remain mandatory. Purposeful destructive real CF03-FAULT remains **DEFERRED / OPEN / NOT EXECUTED**; runtime UNKNOWN/STOP safety is not waived.

**No Issue #22/#20 acceptance checkbox becomes automatically complete by writing or merging this document.**
