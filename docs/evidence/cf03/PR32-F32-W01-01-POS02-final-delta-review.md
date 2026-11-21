# PR #32 – Independent F32-W01-01 Delta-Review & POS02 Evidence Acceptance

**Review date:** 21 November 2026
**Role:** Independent Senior PHP/Dolibarr Developer, Security & Integration Reviewer
**Mode:** Strictly read-only; no repository changes, real DB/HTTP action, replay, cleanup, merge, release or issue transition.

## A. Executive summary and decision

**Final combined decision: `INSUFFICIENT EVIDENCE` for accepting POS02-A1 and POS02-A2 as technically completed.** This is **not** an implementation rejection: **`F32-W01-01: CLOSED`** independently, after correcting the earlier erroneous membership-type interpretation. Source diff and 10/10 exact-HEAD CI checks pass. The original POS02 evidence objects (`baseline.json`, `baseline.sha256`, `verify.started`, `after.json`, `verdict.json`) and a technical POS02 completion report were **not made available in the accessible conversation attachments or GitHub PR/repository material**. Consequently, no independent SHA-256, one-shot, context, row or table-delta check is possible for either real case. The supplied IDs and business observations remain *claims in the review request*, not independently checked evidence.

No new P0–P3 **code** finding was established in the four-file correction. Two **acceptance blockers outside the source diff** remain: missing case-specific raw evidence (`E32-POS02-01`, P1 evidence assurance) and no independently documented *prior case-specific Owner GO* (`G32-POS02-02`, P2 governance, explicitly disclosed in the mandate). Neither authorizes a second booking or Verify attempt. Technical witness suitability is not physical transaction identity or release readiness.

## B. Review identity, exact delta and source scope

| Item | Verified result |
|---|---|
| Repository | [`iLLixM/dolibarr-member-subscription-massaction`](https://github.com/iLLixM/dolibarr-member-subscription-massaction) |
| PR | [#32](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/32), **OPEN/DRAFT** |
| Issue | [#22](https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22), **OPEN** |
| Exact PR and reviewed HEAD | **`e7b911b707508e439f9acbfad43f5fd55d34cc44`** |
| Prior independent W01 HEAD | `3f2c08024b88d031055c27aba980e7156b4a2957` |
| GitHub comparison | HEAD is **4 commits ahead / 0 behind** prior; exact **4 files changed**, **23 additions / 6 deletions** |
| PR base | `786f271240d230b90fad77fe1c3397d1ec796f81` |
| Core reference V22.0.5 | `Dolibarr/dolibarr@88ad132763be45e9620128c1830e41493e1cdd05` |
| Core reference V24.0.3 | `Dolibarr/dolibarr@5c6c7ff6a5112413b1c27221c8d11750afad56f8` |

**Exactly changed:**

1. `tools/cf03-lab.lib.php`: required member-type status from `0` to `1`, corresponding inline comment corrected.
2. `test/unit/cf03-lab.php`: active fixture from `0` to `1`; negative inactive fixture from `1` to `0`.
3. `test/unit/cf03-reader.php`: actual-Reader fake type status now injectable, default `1`; new actual-Reader -> baseline STOP for `0`.
4. `docs/cf03-w01-validation.md`: dated clarification and correction of historical status claim.

GitHub comparison: [prior...HEAD](https://github.com/iLLixM/dolibarr-member-subscription-massaction/compare/3f2c08024b88d031055c27aba980e7156b4a2957...e7b911b707508e439f9acbfad43f5fd55d34cc44). **No production `class/*` PHP, booking, transaction, invoicing, permissions, PDF, mail or other deployment file changed.** The three successive alignment commits following the initial fix do not introduce any additional file beyond the four listed.

## C. Independently verified Dolibarr semantics

### Official V22.0.5

- [`htdocs/adherents/class/adherent_type.class.php:589–606`](https://github.com/Dolibarr/dolibarr/blob/88ad132763be45e9620128c1830e41493e1cdd05/htdocs/adherents/class/adherent_type.class.php#L589-L606): `fetch()` selects `d.statut as status` and assigns it to `$this->status`. No separate inverted status translation occurs.
- [Same source, `LibStatut():895–913`](https://github.com/Dolibarr/dolibarr/blob/88ad132763be45e9620128c1830e41493e1cdd05/htdocs/adherents/class/adherent_type.class.php#L895-L913): `0` is `ActivityCeased`, `1` is `InActivity` (meaning **in activity**, not inactive); status `0` has stopped/disabled visual semantics.

### Official V24.0.3

- [`AdherentType::fetch():619–636`](https://github.com/Dolibarr/dolibarr/blob/5c6c7ff6a5112413b1c27221c8d11750afad56f8/htdocs/adherents/class/adherent_type.class.php#L619-L636): identical `d.statut AS status` mapping to `$this->status`.
- [`AdherentType::LibStatut():1134–1152`](https://github.com/Dolibarr/dolibarr/blob/5c6c7ff6a5112413b1c27221c8d11750afad56f8/htdocs/adherents/class/adherent_type.class.php#L1134-L1152): `0` maps to `ActivityCeased`, `1` to `InActivity`.

### Productive module

- [`class/preview.class.php:69–95`](https://github.com/iLLixM/dolibarr-member-subscription-massaction/blob/e7b911b707508e439f9acbfad43f5fd55d34cc44/class/preview.class.php#L69-L95): `subscriptionTypeForBatch()` fetches `AdherentType` and `validateEligibility()` requires `$type->status == 1` plus a valid member and paid type. This productive implementation was **not modified** by the delta.

**Independent status verdict: `llx_adherent_type.statut=1` ACTIVE; `statut=0` INACTIVE, in both supported reference versions.** Prior `statut=0 ACTIVE` reviews/documentation were demonstrably wrong; their historical decisions are not evidence for the corrected predicate.

## D. Finding disposition – F32-W01-01

**`F32-W01-01: CLOSED` – source and synthetic regression independently verified.**

- [`tools/cf03-lab.lib.php:291–308`](https://github.com/iLLixM/dolibarr-member-subscription-massaction/blob/e7b911b707508e439f9acbfad43f5fd55d34cc44/tools/cf03-lab.lib.php#L291-L308): the real baseline checks `type.id == member.type_id`, `type.status === 1`, entity `1`, paid `subscription === '1'` and expected amount; incorrect type status reaches exact `CF03MemberTypeMismatch`. A valid member itself independently retains member `status === 1`.
- [`tools/cf03-lab.php:185–192,356–364`](https://github.com/iLLixM/dolibarr-member-subscription-massaction/blob/e7b911b707508e439f9acbfad43f5fd55d34cc44/tools/cf03-lab.php#L185-L192): unchanged real `cf03State()` reads `llx_adherent_type.statut` from SQL and casts it to the numeric `type.status` used in the baseline rule; not a hard-coded successful fixture.
- [`test/unit/cf03-lab.php:41–58,225–236`](https://github.com/iLLixM/dolibarr-member-subscription-massaction/blob/e7b911b707508e439f9acbfad43f5fd55d34cc44/test/unit/cf03-lab.php#L225-L236): A/B/C/D contribution and no-change, Member-Agenda ON/OFF, now use active `1`; an explicit negative status `0` must fail with the **exact** `CF03MemberTypeMismatch`, not merely any `CF03...` failure. Entity-2/unpaid fixtures remain independently negative.
- [`test/unit/cf03-reader.php:33–40,99–105,285–295`](https://github.com/iLLixM/dolibarr-member-subscription-massaction/blob/e7b911b707508e439f9acbfad43f5fd55d34cc44/test/unit/cf03-reader.php#L285-L295): strict SELECT-only fake DB returns injectable type status through **real** `cf03State()` call; inactive `0` is then rejected by the real `Cf03LabContract::baseline()` with the specific expected reason. This additionally exercises the SQL mapping boundary, not a synthetic prefilled final state alone.

Scope: The Reader uses a **synthetic resultset**, not a live MySQL/MariaDB server. The preexisting Contribution/Invoice-existing/No-change contracts are unchanged apart from the corrected type status predicate; no concrete regression identified. The new Reader assertion count increases from **74 to 75**.

## E. Exact-HEAD CI assessment

| Evidence | Run / job | HEAD / outcome |
|---|---|---|
| Standard nine-profile | [Run `44846591125`](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/44846591125) | Exact `e7b911b...`, **9/9 SUCCESS** |
| Hosted PHP 8.3 / Dolibarr V22.0.5 | [Run `44846591170`](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/44846591170) | Exact `e7b911b...`, **1/1 SUCCESS** |

All **10 job logs** were fetched and checked, including PHP **7.4**, **8.2**, **8.3**, Dolibarr **V22.0.0/V22.0.5/V23.0.0/V23.0.4/V24.0.0–V24.0.3** according to the configured profiles. In **each** job:

- `CF03 A-D pure harness contracts: 312 assertions PASS`.
- `CF03 invoice actual reader and independent Agenda flags: 75 assertions PASS`.
- `CF03 documented operator CLI contracts: 65 assertions PASS`.
- `CF03 Evidence filesystem contracts: 42 assertions PASS`.
- `CF03 W01 source manifest: 94 assertions PASS`.

No CF03 Reader warning was found. Two PHP 7.4 jobs log **preexisting unrelated** `D01Native::query` / `Ap3Native::query` signature warnings under `experiments/d01/core-lab.php:29` and `experiments/ap3/core.php:36`, respectively; both jobs still conclude SUCCESS. These warnings are not an effect of the F32-W01-01 delta. Optional full 21-profile CI and actual business integration tests were not part of these two runs.

## F. POS02 real-case evidence admission contract

**Actual raw files were not accessible.** The current GitHub [CF03 evidence directory at exact HEAD](https://github.com/iLLixM/dolibarr-member-subscription-massaction/tree/e7b911b707508e439f9acbfad43f5fd55d34cc44/docs/evidence/cf03) contains only `stage01-owner-preflight-2026-11-20.md`. There is a [PR discussion of initial POS02-A1 check-only STOP](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/32#issuecomment-7054560879) and of the source fix, but **no subsequently inspectable A1/A2 raw baseline, post-state, verdict or operator report**. The conversation's accessible file inventory contains earlier W01 mandates/reviews, not the two requested POS02 evidence bundles. Neither CI run publishes artifacts for POS02.

The source-defined one-shot contract, **not independently established for the purported actual cases**, requires:

1. A genuine new synthetic member, accepted `--check-only` and `--baseline` on the exact reviewed source; `baseline.json` and `baseline.sha256` stored under a protected case-specific Evidence directory.
2. `baseline.sha256` must match the actual **bytes** of `baseline.json` after independent SHA-256 recomputation; JSON parsed as expected environment/case/fixture/scenario. A displayed hash without the corresponding file is insufficient.
3. A single successful `--verify` must have created **`verify.started`** before further database/source inspection. Preserve original marker timestamps and file modes; never rerun or synthesize the marker. Source: [`tools/cf03-evidence.lib.php:121–155`](https://github.com/iLLixM/dolibarr-member-subscription-massaction/blob/e7b911b707508e439f9acbfad43f5fd55d34cc44/tools/cf03-evidence.lib.php#L121-L155).
4. Independent before/after comparison: database schema/account/version, `CoreGuarded` profile and raw-profile hash, config SHA-256, selected deployed/module/Core source manifests, schema columns, PHP CLI full/major-minor version, Agenda configuration **within the case**, engines, fixture/member/type, and all monitored counts. Source: [`tools/cf03-lab.lib.php:373–385`](https://github.com/iLLixM/dolibarr-member-subscription-massaction/blob/e7b911b707508e439f9acbfad43f5fd55d34cc44/tools/cf03-lab.lib.php#L373-L385).
5. Real member postconditions: exactly one new subscription bound to the right member and type, gross EUR 120, planned start/end and note marker, R16 member `datefin`, no linked invoice, correct member Agenda behavior, no unexpected monitored-table changes. Source: [`tools/cf03-lab.lib.php:386–420,485–505`](https://github.com/iLLixM/dolibarr-member-subscription-massaction/blob/e7b911b707508e439f9acbfad43f5fd55d34cc44/tools/cf03-lab.lib.php#L385-L505).
6. A separate authenticated Web-UI acknowledgement can support the Owner's account of an accepted Core booking but is **not** produced by the read-only SQL witness and cannot prove an exact physical COMMIT event.

### Expected count delta table (requirements, **not measured outcomes**)

The selected global counts must match `after.counts[t] - before.counts[t]` exactly. `llx_adherent` changes a member's end-date but contributes no new row; `COUNT(*)` remains unchanged.

| Monitored table | POS02-A1 Member Agenda ON | POS02-A2 Member Agenda OFF |
|---|---:|---:|
| `adherent` | 0 | 0 |
| `subscription` | **+1** | **+1** |
| `actioncomm` | **+1** | **0** |
| `actioncomm_resources` | **+1** | **0** |
| `societe` | 0 | 0 |
| `socpeople` | 0 | 0 |
| `facture` | 0 | 0 |
| `facturedet` | 0 | 0 |
| `element_element` | 0 | 0 |
| `paiement` | 0 | 0 |
| `paiement_facture` | 0 | 0 |
| `bank` | 0 | 0 |

Source: `Cf03LabContract::COUNTS` and [`verify()` delta loop:485–499](https://github.com/iLLixM/dolibarr-member-subscription-massaction/blob/e7b911b707508e439f9acbfad43f5fd55d34cc44/tools/cf03-lab.lib.php#L485-L499). Global-count reconciliation requires a quiescent lab; it does not detect all possible changes to pre-existing rows or unrelated tables.

## G. POS02-A1 – separate evidence disposition

**Case as described by the requester, NOT independently measured:** A / CoreGuarded, synthetic member **#59**, subscription **#68**, **EUR 120**, member Agenda **ON**, one event + user-resource assigned to authenticated actor **#4**, no invoice.

**Evidence disposition: `INSUFFICIENT EVIDENCE / NOT ACCEPTED`** as a formally demonstrated real case. Without original `baseline.json`, `baseline.sha256`, `verify.started`, `after.json`, `verdict.json` and the associated operator UI record, none of these key assertions can be verified from actual bytes:

- Hash equality/immutability of the baseline; exact checked-out and deployed-source SHA, profile/config/CLI PHP and DB identity; historical vs current user/fixture identities.
- One-shot Verify marker presence and creation order, no repeat attempt, protected file modes and ownership.
- Subscription #68 member-FK, type-FK, dates `2080-01-01..2080-12-31`, amount/marker, new `datefin`, event `AC_MEMBER_SUBSCRIPTION_CREATE` author/owner/resource actor #4 and no linked invoice.
- **12 actual table before/after count pairs**; expected are listed above but no observed values were supplied.
- Any reported Web-UI acknowledgement or preceding case-specific Owner GO.

The [PR comment](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/32#issuecomment-7054560879) independently documents **an earlier A1 `--check-only` STOP** on member #59 *before* the fix. It explicitly says no baseline or booking was performed at that earlier point. This does **not** disprove later success on the corrected HEAD, but cannot replace later raw records.

**Remedy:** supply the already-preserved case evidence and sanitized UI/console report without altering any file, replaying the booking or rerunning verify. Perform independent byte-level comparison and report whether the A1 case was actually run on `e7b911b...` with valid preflight/check-only admission.

## H. POS02-A2 – separate evidence disposition

**Case as described by the requester, NOT independently measured:** A / CoreGuarded, synthetic member **#60**, subscription **#69**, **EUR 120**, member Agenda **OFF**, **zero** member Agenda event/resources and no invoice.

**Evidence disposition: `INSUFFICIENT EVIDENCE / NOT ACCEPTED`**, for the same absence of the original five Evidence objects and operator record. No independent proof of source/DB/profile continuity, single Verify, correct R16/subscription persistence or the **12 actual table-delta pairs** is available.

**Important scope qualification:** It is explicitly reported that A2 had *different effective Invoice-Agenda flags than A1*. This alone is **not a defect** in A2's regular `contribution` scenario: `--agenda=off` specifies the **Member** trigger, and no invoice is created. **Cross-case equality of invoice flags is not required.** However, `Cf03LabContract::verify()` **does require invoice flags to remain unchanged within each case** (`before.invoice_agenda_flags === after.invoice_agenda_flags`). Without both raw states this cannot be confirmed. The A2 result, even if verified PASS, would demonstrate only a single regular contribution with the Member-Agenda action effectively disabled; it would **not** prove full agenda-disabled environment, enabled Invoice-Agenda behavior or absence of arbitrary unrelated hooks.

**Remedy:** supply original A2 bundle plus user-visible operator result; independently compare A2 **within its own** baseline/verify window and report its invoice-flag configuration precisely without retroactively changing the A1 contract.

## I. New findings, severity and scope

**New code findings in four-file delta: none (P0=0, P1=0, P2=0, P3=0).** Existing wider-scope W01/operating restrictions are neither reopened nor silently declared closed by this review.

| ID | Category / priority | Exact evidence location | Disposition and minimal measure |
|---|---|---|---|
| `E32-POS02-01` | **P1 evidence-acceptance blocker, not code defect** | No A1/A2 raw files in available conversation; GitHub [docs/evidence/cf03](https://github.com/iLLixM/dolibarr-member-subscription-massaction/tree/e7b911b707508e439f9acbfad43f5fd55d34cc44/docs/evidence/cf03) has only historical Stage-01 preflight | Provide unchanged original five files **per** case, private/securely, plus technical results; verify SHA, one-shot, 12 count pairs and source/config states. **Do not rerun** |
| `G32-POS02-02` | **P2 Owner-governance proof gap, not code defect** | Review mandate expressly states that a separate pre-booking case-specific Owner GO is absent from the evidence | Obtain the independently dated original prior approval if it exists, else document `NOT VERIFIED`; do not manufacture retrospective authorization or equate observed success to prior approval |

A missing file is **not** evidence that a booking failed or that data were changed incorrectly. It prevents an independent successful acceptance decision. Likewise, the request's stated positive outcomes should not be converted to a failed runtime test merely because proof was not provided.

## J. Decision boundaries and next acceptance step

- **Code/technical delta:** `F32-W01-01 CLOSED` at **exact** `e7b911b707508e439f9acbfad43f5fd55d34cc44`. Correct Dolibarr semantics, actual `cf03State()` reader, exact-reason negative case and ten successful associated CI jobs confirmed. The bounded W01 witness code remains technically suitable for approved real contribution tests subject to operating gates; this is **not** an unqualified approval of any execution.
- **POS02-A1:** `INSUFFICIENT EVIDENCE`, not accepted as a completed independently reconciled real case.
- **POS02-A2:** `INSUFFICIENT EVIDENCE`, same; Invoice-Agenda cross-case flag variation does not by itself invalidate the bounded Member-Agenda-OFF contribution test.
- **Owner/Web UI:** no accessible original UI acceptance output or prior case-specific approval. Web acknowledgement must be considered separately from SQL persistence; it does not prove physical event identity.
- **Project governance:** Issue #22 and PR #32 remain open/draft. No CF03-wide gate, physical COMMIT identity, invoice, PDF, SMTP, production, merge, ZIP or release approval is implied.

**Exactly one final verdict for this combined review: `INSUFFICIENT EVIDENCE`.** A positive independent POS02 evidence disposition can be reconsidered **on the original existing files only**, without generating new business operations or reusing a case ID.

---

**Review provenance:** GitHub connector verified exact PR metadata, commit comparison, four-file source patch, pinned Dolibarr V22/V24 `AdherentType` source, productive preview/eligibility source, actual witness SQL mapping, synthetic unit tests, real verifier contracts, both workflow-run commit bindings, all ten job logs, repository evidence directory and PR/issue discussions. Conversation file inventory was checked for named POS02 evidence. **No independent access to the Owner's A database, HTTP session, hosted infrastructure or raw POS02 evidence was available.** No local exact-source PHP unit execution is claimed; CI execution and code inspection are clearly distinguished.
