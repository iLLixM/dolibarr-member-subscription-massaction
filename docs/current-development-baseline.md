# Current development baseline, architecture and roadmap

**Effective: 2026-11-21, after squash merges of PR #32, PR #33 and PR #34.** This document is a navigational consolidation of the **binding Owner strategy in [Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22)** and the release decisions in [Issue #20](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/20). If a dated historical document disagrees, the newer Owner decision in Issue #22 controls *prospectively*. This is documentation, **not a change of architecture authority, feature flags or runtime code**.

## What is implemented, what is intended

**Current development state (post-PR35 main; PR36 draft correction not merged):** PR #29 delivered bounded CoreGuarded contribution-only booking; PR #32 read-only CF03-W01 witness/CI/evidence infrastructure; PR #33 the documentation strategy baseline; **PR #34 merged CF05-01/02, the shared active Booking→Invoice guard, customer/invoice Core API checkpoints and individually guarded readbacks**. Earlier EnhancedMySQL and MariaDBSnapshot financial paths retain their historically admitted limits. **PR #36** adds CoreGuarded customer/invoice error provenance and post-commit PDF/mail implementation but is **OPEN/DRAFT / independent follow-up pending**. Its **F36-01/P1** preliminary correction restores a **production-default-closed** feature gate; **F36-02/P2** tracks the conditional Dolibarr V22/V24 `Facture::validate()` PROV-directory rename before the outer Core COMMIT. This is a CF05-06 qualification requirement before financial admission, **not** currently demonstrated impact in the fresh-invoice path. **The full CoreGuarded finance flow is still NOT admitted**; none of the three profiles has fresh complete CF05-normal-case release qualification.

**Release target v0.1.0:** full member bulk-subscription massaction with the **same business capability in CoreGuarded, EnhancedMySQL and MariaDBSnapshot**, under profile-specific observation strengths and exact qualifying environments.

| Capability | Required v0.1.0 target (all three profiles) | Current code / admission boundary |
| --- | --- | --- |
| Preview, eligibility, server-held confirmation and member selection | Required | Existing, read-only preview is not booking permission |
| Contribution, member R16 end and configured standard Agenda | Required | CoreGuarded contribution-only development implementation; POS01-C/POS02-A1/A2 bounded real positives |
| Existing Third Party invoice | Optional when confirmed | Historical B/C path; draft PR #36 implements CoreGuarded finance behind a **server-default-denied** qualification gate (F36-01 correction unreviewed); CF05-06/07 and owner admission still OPEN |
| Create Third Party | Optional **only together with selected invoice** | Shared active guard and before/after `Societe::create_from_member()` boundary now implemented; still requires CF05-03..07 feature admission, qualified Core call graph and real A–D evidence |
| Invoice line, validation and persisted links | Required when invoice selected | Historical B/C subset; profile-spanning CF05 acceptance pending |
| Verified invoice PDF | Optional, strictly after suitable acknowledged DB completion | New CoreGuarded admission/checkpoint required; not DB-atomic |
| Contribution email and verified invoice attachment if applicable | Optional after permitted completion/checkpoint | Delivery is separately evidenced; not DB-atomic or retryable automatically |
| Payments and bank entries | **Not in scope** | No automatic payment/bank booking |

A one-member outer transaction does not make a whole batch atomic. An already committed member is not rolled back because a later member fails.

## Profile and evidence matrix

| Reference environment | Profile | Qualified evidence strength and restrictions |
| --- | --- | --- |
| **A** – Dolibarr V24.0.3, restricted MySQL 8.4.13, PHP 8.2 | `CoreGuarded` | Core ACK/depth/native connection continuity only; **no independently proved physical transaction event**, and no required general Performance Schema SELECT |
| **B** – Dolibarr V24.0.3, MariaDB 10.11.19, PHP 8.2 | `MariaDBSnapshot` | Bounded `@@in_transaction`/native identity snapshots, **not** MySQL P_S own-event equivalence |
| **C** – Dolibarr V24.0.3, P_S-qualified MySQL 8.4.13, PHP 8.2 | `EnhancedMySQL` | Own P_S transaction-event/identity/isolation/freshness observation; missing permissions or instrumentation STOP |
| **D** – restricted hosted Dolibarr V22.0.5, MySQL 8.4.13, PHP 8.3 | `CoreGuarded` | As A, plus host-specific private evidence and mandatory *exact ZIP installation* test before release |

**A and C share the same physical schema and webroot.** Profile and database account switching is manual, explicit and **serial**; never parallelize A/C tests. An unexamined extension, modified Core call path, other server/account configuration or version is not automatically qualified.

The intended support statement for v0.1.0 covers **Dolibarr V22/V23/V24 (Owner-confirmed)**, PHP >=7.4 subject to Core minimum, Oracle MySQL >=8.4 and MariaDB >=10.11. **Real V23 integration is not established by the synthetic V23 CI**, although the Owner-confirmed compatibility statement remains. Technical admission of version 21+ is a capability gate, not a blanket support promise. PostgreSQL is excluded.

## Binding architecture/security contract

1. **One transaction owner + shared active guard per member:** Booking owns a single outer Dolibarr DoliDB transaction; the very same profile-aware guard is supplied to Invoice and any newly reached Third Party/line/validation checkpoint. No second transaction manager, no extra module business SQL writer, no P_S requirement imposed on CoreGuarded.
2. **Core APIs + qualified call graph:** Use standard `Adherent::subscription()` / customer/`Societe` / `Facture` / Agenda/trigger APIs, InnoDB engine gates, required permissions/entity/profile checks and persisted postconditions. D-02 must qualify actual V22/V24 calls including `COMPANY_CREATE`, optional `CONTACT_CREATE`, `BILL_CREATE`, `LINEBILL_INSERT`, `BILL_VALIDATE`, document side effects and dynamic standard/third-party hooks; unsupported combinations STOP.
3. **D-01 order for CoreGuarded:** complete preflight, `SET TRANSACTION ISOLATION LEVEL REPEATABLE READ`, *immediately* Core BEGIN on the same connection, then locks and fresh business reads. No extra SQL between SET and BEGIN, no session/global normalization and no assertion of actual-event identity.
4. **Error classification:** A single Core-owned rollback is allowed for an explicitly qualified business error with demonstrated intact owned context. SQL errors, deadlock/timeout ambiguity, lost completion ACK, connection switch, depth corruption and unclassified Invoice/Third Party errors are **UNKNOWN/STOP**; never perform a foreign rollback, automatic retry, compensating DML or fake success via readback.
5. **Completion and after-effects:** After a suitable acknowledged *nonterminal* Core completion (normally qualified `NO_CHAIN`) and a dependable, DB-independent result checkpoint, independently verify PDF/path/hash/attachment/recipient, then optional email. Known terminal `CHAIN` or `RELEASE` completion immediately STOPs future SQL/module PDF/mail/next members. Invoice, PDF and mail results are separate and must not be described as one atomic outcome.
6. **Web and direct service security:** Profile fixed by server-side admin setting/revision; verify rights/entity, eligibility/fees/R16, CSRF, consumed confirmation, owner/entity/revision/CAS, session/file checkpoint and direct-service rights and feature gates. Missing P_S in Enhanced does not cause automatic fallback.
7. **Known residuals (ADR-08/09/12):** CoreGuarded and MariaDB cannot guarantee physical own-event continuity like Enhanced; no durable module journal, automatic exactly-once/replay recovery or arbitrary-hook/whole-batch atomicity. UNKNOWN requires explicit human reconciliation.

These are **mandatory runtime contracts**, including during the deferred real fault-testing interval. Refer to [ADR-0001](adr/ADR-0001-core-first-transaction-profiles.md) and the [risk register](adr/ADR-0001-risk-register.md) for dated derivation and original evidence.

## Revised binding ADR decisions

- **ADR-06 (revised 2026-11-21):** CoreGuarded is **not permanently contribution-only**. The v0.1.0 financial function remains the target; **CF05-01/02 is implemented and merged**, but current financial gates stay closed pending CF05-03..07, full-feature normal-case qualification and Owner acceptance.
- **ADR-11 (revised 2026-11-21):** full v0.1.0 requires focused CF05 PR(s), retained synthetic security/transaction regressions **including 21 PHP/Core jobs**, independently assessed real **normal/success** A–D workflows, exact-source independent code/security and operational risk review, real hosted D ZIP acceptance and separate release approval. **Real destructive/fault injections are moved to a later CF03-FAULT iteration** and stay OPEN / NOT EXECUTED; safe synthetic fault and authenticated negative tests remain release requirements.
- **ADR-01–05, 07–10 and 12** remain binding. In particular CoreGuarded's weaker physical evidence is an explicit Owner-accepted residual, not a claim of equivalence or a waiver for known P0/P1 data-integrity issues.

## CF05-01/02 merged implementation checkpoint

- **[PR #34](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/34): SQUASH-MERGED** on 2026-11-21 as `dc7a172bfd476467d7980cbf16a2657cfc1e729c` into `main`. Independently reviewed source `11d7dd3cf4bdcf831aa87782b7466d2378fc733f`, base `d0d0fddd5797a1424eb004d541d8036511193ec6` after [PR #33](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/33). Five PR commits, eight changed files; this merge is the reviewed **CF05-01/02 development stage**, not CF05-03..07 or full v0.1.0 admission.
- **Implemented:** `MsmActiveTransactionGuard` (`isFor`, `active`, `requireSupportedIsolation`) is implemented by `MsmCoreGuardedTransaction` and `MsmDatabaseState`; Booking passes **the same active instance and original DoliDB** into Invoice. The original `MsmDatabaseState` remains separate for DB-family/InnoDB metadata; there is **no second transaction manager or general Performance Schema requirement** for CoreGuarded. Active guard checks surround `Societe::create_from_member()`, `Facture::create()`, `Facture::addline()`, `Facture::validate()` and the individual customer/member/invoice readbacks.
- **Fail-closed stage classification:** CoreGuarded business rollback eligibility is restricted to previously qualified `preflight`/`subscription` stages **and** existing qualified error reasons; `customer`, all `invoice_*`, unknown/future stages and unclassified errors remain `UNKNOWN/STOP` without automatic foreign rollback/retry. **CF05-04** must separately qualify any newly permitted invoice/customer rollback origins with intact owned context.
- **Independent follow-up verdict:** `APPROVED FOR DEVELOPMENT MERGE`; **F34-01 / P2 CLOSED**, **F34-02 / P3 CLOSED**, no new P0–P3. [CI #44948777072](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/44948777072) **9/9 PASS** on the exact reviewed PR HEAD (PHP 7.4/8.2, pinned Dolibarr V22/V23/V24). `test/unit/f29-rights.php`: **113** assertions, `test/unit/invoice.php`: **301** assertions, synthetic only. No post-squash real database/HTTP qualification or physical transaction event identity is claimed.
- **Feature gates unchanged:** `class/transactionprofile.class.php`, Booking and direct Invoice admission still **disable** CoreGuarded invoice, optional automatic third party and mail; PDF/mail is not now qualified or activated by the interface alone. **Next work starts at CF05-03/04**, then CF05-05/06 and CF05-07, real CF03-NORMAL A–D and release prerequisites. See [implementation architecture](cf05-shared-guard-design.md).
- **Historical PR #33 documentation:** merged in `d0d0fddd5797a1424eb004d541d8036511193ec6`; F33-DOC-01/02 CLOSED, F33-DOC-03 PARTIAL / OWNER ACCEPTED as two non-blocking old-prose inconsistencies. Historic evidence and text exceptions are not runtime behavior changes.

## PR #32 merge and operator restrictions

- **[PR #32](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/32): SQUASH-MERGED** on 2026-11-21, resulting `main` commit `425980c43a51a9f27073133212f18825ac6da248`. Reviewed PR source `cbc97843fb017135739a39ea12ef26c7fa66dfdb`. The independent review had **0 P0/P1; F32-MR-01/P2 and F32-MR-02/P3** and concluded **CHANGES REQUIRED for unconditional approval**. Owner expressly accepted both as limited exceptions; the external review verdict was **not** silently changed to APPROVED.
- **F32-MR-01 — ACCEPTED OPERATIONAL LIMITATION, not fixed:** A/B/C example invocations under `sudo -u www-data` may lose required private `CF03_*` bindings with `env_reset`. **No working private launcher smoke test is evidenced.** All further real A/B/C witness use is **blocked** until a separately protected, narrowly scoped target-CLI-user launcher passes read-only preflight; the old examples cannot be presumed executable. Never pass secrets through arbitrary sudo environment forwarding or in command-line arguments; keep configuration and raw evidence outside Git.
- **F32-MR-02 — ACCEPTED HISTORICAL DOCUMENTATION DEVIATION, not a runtime bypass:** The earlier CF03 qualification-plan wording incorrectly suggested a fixed A/B/C root and a group/other-writable evidence parent. The runtime policy requires a canonical parent without group/other write, A/B/C operator-provided `CF03_EVIDENCE_ROOT`, case 0700, file 0600 and one-shot no-retry; current runbook/code are normative. This documentation-refresh PR corrects the current-facing statement while keeping the original Owner acceptance/audit fact.
- The parent must also be outside the webroot/site-root/checkout for A/B/C/D, but host/operator must verify HTTP aliases, ACLs/ancestors, mount and same-UID/trusted-principal assumptions. No external/physical transaction observer qualification is implied.
- Privacy remediation pseudonymized the *current file tree*, but **old Git commits, GitHub discussions and external mirrors were not purged**. Historic byte hashes are valid only for their original pinned bytes. No persistent dictionary of prohibited identifiers or dedicated blacklist CI is retained.

## Historical evidence: preserve exact scope

| Evidence | Accepted disposition | Not thereby established |
| --- | --- | --- |
| CF02 POS01-C | Real Owner PASS at earlier PR #29 source: subscription contribution + normal Agenda/R16 | No universal physical COMMIT-event proof |
| CF03 POS02-A1/A2 | Owner TECHNICAL PASS at earlier source, Member Agenda ON/OFF | No independent physical event identity or complete financial workflow |
| F32-W01-01 | Independent CLOSED at earlier pinned source (active member type status `1`) | Not an endorsement of every later source change |
| RB01-A | **INVALID / STOP as rollback evidence**; a normal contribution persisted | No successful rollback/fault injection, no replay of used members or subscriptions |
| A–D preflights | Earlier 4/4 Owner PASS at earlier source | No new exact-HEAD preflight or current real integration gate |
| PR32 CI | 9/9 standard + 1/1 hosted SUCCESS on reviewed PR head | Not full 21-profile release matrix or real DB/ZIP test |

A reviewer's earlier inability to access raw POS02 bytes and the Owner's later technical PASS are different evidence types; one must not be repackaged as the other. All historic used cases are permanently one-shot.

## Next deliverables and release gates

| Order | Work package | Required output |
| --- | --- | --- |
| 1 – COMPLETE (dev) | **CF05-01/02** | **MERGED / independently approved** via PR #34: minimum active guard, same-instance Booking→Invoice injection, Core API/readback checks, B/C synthetic regression. **Not** full financial feature admission |
| 2 | **CF05-03/04** | Consistent server-authoritative enablement on all modules/services, finite qualified rollback/error-provenance classification |
| 3 | **CF05-05/06** | Safe PDF/mail after acknowledged nonterminal end with checkpoints, exact Core/standard trigger and InnoDB/side-effect audit |
| 4 | **CF05-07** | Focused PHP >=7.4-compatible implementation/security tests, exact-head independent review and Owner acceptance |
| 5 | **CF03-NORMAL** | Unique Owner-authorized real full-workflow normal cases A/B/C/D: contribution+Agenda ON/OFF, invoice existing/new Third Party, validated line/link, PDF, isolated SMTP including attachment, small successful multi-member flow and independent DB/file/delivery postconditions |
| 6 | **CF03-SECURITY-BASELINE** | Synthetic transaction/security/rights/CAS/CSRF/replay/NO_CHAIN/CHAIN/RELEASE regressions, safe authenticated negatives and **21-job** PHP/Core matrix; independent evidence/risk review |
| 7 | **Release / Issue #20** | Fresh v0.1.0 ZIP **without RC suffix**, source-matched manifest/checksum, **real D ZIP installation**, R0-04 exact-package integration, R0-07 independent review, explicit separate Owner release GO |

**CF03-FAULT (deferred):** real destructive post-DML rollback, implicit end, lost ACK/connection, SQL 1568/1213/1205, nested-depth, concurrency, destructive replay/interruption and controlled PDF/SMTP fault campaigns are **OPEN/NOT EXECUTED**. They require later isolated harness review and case-specific Owner GO; this deferral never relaxes runtime safety or permits treating an UNKNOWN as success.

[Issue #22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22) remains **OPEN** after the PR34 development merge. Historical [PR #19](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/19) closed **without merge**; its prepared ZIP/hash is not current release evidence. [Issue #20](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/20) contains dated pre-refresh wording; the revised 2026-11-21 Issue #22 scope takes precedence. The Owner retains the unreserved module ID `584620` for v0.1.0 and the Owner-confirmed V22/V23/V24 compatibility statement, without inventing real V23 test evidence.

**No code admission, real business write, package or release is authorized by this documentation-only update.**
