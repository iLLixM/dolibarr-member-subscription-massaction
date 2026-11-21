# CF03 RB01-A — Step-by-step operator runbook (controlled real rollback)

**Status: PREPARATION ONLY / NO-GO for actual fault injection.** This document is usable for case planning and read-only preflight, **not** authority to book or change a database.
**Design and gates:** [RB01-A technical contract](cf03-rb01-a-harness-design.md).
**Source analyzed:** `332f87810bdcc4301c5fc174dc7fac2e20dca986`. The actual executable RB01 harness must be separately implemented/reviewed and pinned at its own exact SHA before any `--baseline` or UI action.
**Ownership:** Operator runs all real steps, records actual values and performs one authorized UI action. The independent reviewer evaluates only preserved evidence. Do not reuse POS02-A1/A2 fixtures.

## Phase 0 — STOP/GO admission (nothing destructive yet)

**STOP immediately** unless all boxes can be supported by original evidence:

- [ ] A **separate disposable cloned environment A-RB** exists. The active A/C shared schema, real POS02 environment and hosted D are *not* used for fault injection.
- [ ] The clone is network-isolated, has a distinct MySQL server UUID/volume, no other writers, a tested independent restore and a bounded selected-file inventory.
- [ ] The clone's correct PHP Web/CLI runtime, restricted DB identity, CoreGuarded profile, source/deployment and session/syslog prerequisites are attested; external SMTP/notifications are blocked.
- [ ] A uniquely identified **test-only** injection build has been implemented, synthetic-negative-tested and independently reviewed. It is **not installed on the regular A/C instance**.
- [ ] The reviewed injection point is exactly **after successful parent `verifyStored()` / Core DML and before the first COMMIT attempt**, qualified as `MSMConsistencyError`. It may be armed for one exact member/marker/consumed confirmation only.
- [ ] A canonical non-symlink W01 evidence root with **effective CLI read/write access** and a **separate** protected injection/control-evidence root exist, with no web exposure. The parent need not be owner-only, but group/other write permissions now cause STOP; additional ACL and alias exposure must still be independently ruled out. Individual W01 case directories/files remain 0700/0600.
- [ ] A written **prior case-specific Owner-GO** names actual cloned instance, reviewed harness SHA, member/ref/marker, possible business writes, one permitted UI action and recovery/STOP contact.
- [ ] No other application operation, profile/account switch, PHP source deployment, data cleanup or hook/config change occurs between baseline and final readback.

A failed gate means **NO-GO**. Do not create a trigger, disable an application safety check, issue manual INSERT/UPDATE/DELETE as a substitute for Core DML, call `KILL`, simulate a deadlock or edit the production `subscription.php` on normal A.

## Phase 1 — Record the isolated environment and restore point

1. Manually provision the isolated A-RB test deployment using a synthetic data set or an appropriately sanitized clone. Keep any backup and secrets **outside** the webroot/repository. Rehearse restoration **in the clone** before arming any test.
2. Record the original A server UUID and the clone UUID through independent approved DBA access; prove that the clone's TCP endpoint and persistent volume cannot route to A/C. Note server family/version, Docker/Podman namespace, Dolibarr source and effective PHP-CLI/Web worker version.
3. Configure the **clone** with CoreGuarded and restricted DB grants. Require `autocommit=1` and `completion_type=NO_CHAIN`. Turn **Member** Agenda OFF through Dolibarr administration before baseline; note independently configured invoice-Agenda flags. Do not change the A/C profile or credentials.
4. Prepare one clean test-only source commit: original application code plus *only* reviewed injection subclass/controller wiring, with its own pinned SHA and source/deployed manifest. Reject a dirty/uncommitted controller patch; its SHA must be reviewable.
5. **Important mapping caveat:** `tools/cf03-lab.php --environment=A` expects private operator-supplied A filesystem path, DB name/account/port and module-source identity. In an isolated VM/container namespace those logical paths and port can be mapped to the isolated clone; verify physical separation independently. Otherwise STOP and implement/review a separate `A-RB` read-only witness, **never modify the standard A mapping in-place**.

Record only sanitized inventory in GitHub. Original backup, user data, DB export, conf.php, session and raw traces stay private.

## Phase 2 — Provision a new synthetic fixture

Create one new member **using normal Dolibarr UI** in the isolated clone; do not insert SQL fixture rows manually. Verify:

| Field | Required value |
| --- | --- |
| Member | New numeric rowid and **new Core-generated ref**; never #59/#60 |
| First/last name | `Synthetic` / exact case marker below |
| Email | lowercased marker + `@example.invalid` |
| Entity/status | entity 1, validated member status 1 |
| Member type | active paid `llx_adherent_type.statut=1`, entity 1, contribution `subscription='1'`, amount EUR 120 |
| Prior state | `datefin=NULL`, no existing contribution, no linked customer or invoice |
| Contribution candidate | `2080-01-01` to `2080-12-31`, EUR 120.00, Member Agenda OFF |
| UI options | only one member, invoice OFF, mail OFF, customer auto-create OFF |

Unique suggested marker (use only if absent): `CF03LAB-A-RB01A120261121`.
Suggested W01 evidence path: `/var/lib/dolibarr-test-results/cf03-a-rb01-a1-20261121`.
Separate injection trace path: a **different sibling/private directory**, not inside the W01 case directory.

## Phase 3 — Read-only preparation commands

Run these commands **only inside the isolated clone namespace** after source review, not on the ordinary A/C worktree. Replace the two real fixture placeholders:

~~~bash
cd ${CF03_WORKTREE:?set_private_worktree_path} || exit 1

RB01_REVIEWED_SHA=REPLACE_WITH_REVIEWED_TEST_ONLY_HEAD
RB01_MEMBER_ID=0
RB01_MEMBER_REF=REPLACE_WITH_NEW_CORE_MEMBER_REF
RB01_MARKER=CF03LAB-A-RB01A120261121
RB01_EVIDENCE_DIR=/var/lib/dolibarr-test-results/cf03-a-rb01-a1-20261121

# Do not advance unless HEAD is the separately reviewed test-only source.
test "$RB01_REVIEWED_SHA" != REPLACE_WITH_REVIEWED_TEST_ONLY_HEAD || exit 1
test "$(git rev-parse HEAD)" = "$RB01_REVIEWED_SHA" || exit 1
test -z "$(git status --porcelain)" || exit 1
test ! -e "$RB01_EVIDENCE_DIR" || exit 1
test ! -L "$RB01_EVIDENCE_DIR" || exit 1

rb01_a_witness() {
  if ! [[ "$RB01_MEMBER_ID" =~ ^[1-9][0-9]{0,8}$ ]] ||
     [ "$RB01_MEMBER_ID" -eq 59 ] ||
     [ "$RB01_MEMBER_ID" -eq 60 ] ||
     ! [[ "$RB01_MEMBER_REF" =~ ^[A-Z0-9][A-Z0-9_-]{4,31}$ ]]; then
    echo "STOP: invalid or reused RB01 fixture" >&2
    return 1
  fi
  sudo -u www-data /usr/bin/php -d xdebug.mode=off \
    tools/cf03-lab.php "$@" \
    --environment=A --scenario=no-change \
    --member-id="$RB01_MEMBER_ID" \
    --member-ref="$RB01_MEMBER_REF" \
    --marker="$RB01_MARKER" \
    --start=2080-01-01 --end=2080-12-31 \
    --amount=120.00 --agenda=off
}

# Standard A witness: only if the isolated namespace matches all operator-bound A identities.
sudo -u www-data /usr/bin/php -d xdebug.mode=off \
  tools/cf03-lab.php --preflight --environment=A
rb01_a_witness --check-only
~~~

**Expected:** two sanitized `status=PASS` outputs. `--check-only` must explicitly name `scenario=no-change` and the new member. A passed preflight alone does **not** attest that the endpoint is physically isolated. If the clone's environment mapping differs, STOP; do not pass a fake production identity.

## Phase 4 — Seal evidence only after explicit GO

Confirm all Phase 0 gates and re-check exclusive use of the clone. Store Owner-GO and the injection program/source SHA in the **separate control directory** before this point. Do **not** put such files inside `RB01_EVIDENCE_DIR`.

Only after prior case-specific Owner-GO:

~~~bash
test ! -e "$RB01_EVIDENCE_DIR" &&
test ! -L "$RB01_EVIDENCE_DIR" &&
rb01_a_witness --baseline --evidence-dir="$RB01_EVIDENCE_DIR"
~~~

Expected `{"status":"PASS","mode":"baseline","scenario":"no-change","writes":"no_business_sql"}`. The W01 witness creates its own private case directory with **exactly** `baseline.json` and `baseline.sha256`. Preserve both original file bytes. No preexisting case directory or repair on a failed baseline.

## Phase 5 — Single permitted authentic UI action (not a command)

1. Verify one-shot injection is armed **only for this exact fixture** in the reviewed isolated build. Confirm the private injection marker destination is empty and ready.
2. In the clone's authenticated Dolibarr browser session, choose **only** this synthetic member, contribution of EUR 120 for the planned dates, Member Agenda OFF, invoice/customer/mail/PDF OFF.
3. Perform standard preview and confirmation; execute **exactly one** booking POST using the normal UI. **No crafted request or CLI calling `book()`**.
4. Expected injected sequence: regular Core `begin()` → Core `subscription()` and member end-date DML → successful `parent::verifyStored()` → exclusive post-DML marker → qualified `MSMConsistencyError` → existing CoreGuarded `rollbackOwned(true)` → single normal Core rollback ACK. **No COMMIT attempt, no success response.**
5. Save UI result and sanitized controller diagnostic separately: expected failed/skipped as per qualified business path, ideally `rollback=confirmed`, no retry/secondary action. A rolled-back generated subscription ID in diagnostics is **not** persisted.
6. If UI reports success, unknown, unexpected SQL error, no injection marker, uncertain connection, missing savepoint/rollback trace or any second request: STOP and **do not repeat**. Preserve original evidence and start a separate incident investigation, not a new booking.

## Phase 6 — Independent reconciliation and exactly one verify

Before W01 `--verify`, obtain an **independent read-only view using a fresh DB connection** outside the application's aborted transaction. The separately reviewed RB01 observer must compare:

- Fixture identity, original `datefin=NULL`, type and other selected member fields.
- Zero subscription rows for the fixture and marker; zero member Agenda event/resource/invoice links.
- All 12 selected global table-count deltas equal zero **within this case window**.
- Row-level before/after integrity for selected affected tables, not just counts; no unexplained files in selected business document trees, no external SMTP/PDF artifact.
- Injection marker exists with bound case/fixture/source/connection evidence; exactly one qualified business failure; one Core rollback acknowledgement and expected depth/identity, **no COMMIT attempt**.
- Core/module/deployed source, profile, conf hash, PHP, DB identity/UUID, Engine and Agenda settings unchanged between baseline and after observation.

Only if the case remains unambiguous and it is appropriate to seal the W01 evidence: run **once**:

~~~bash
rb01_a_witness --verify --evidence-dir="$RB01_EVIDENCE_DIR"
~~~

Expected W01 verdict core fields:

~~~json
{
  "status": "PASS",
  "environment": "A",
  "scenario": "no-change",
  "subscription_id": null,
  "invoice_id": null,
  "agenda_count": 0,
  "outcome": "selected_database_state_unchanged",
  "acknowledgement": "not_determined_by_database_readback"
}
~~~

This means **selected observed state unchanged**, not physical ROLLBACK proof by itself. The independent post-Core-DML/injection/Core-ACK evidence must be present to decide **RB01-A PASS**. In the case of any STOP, the existing one-shot marker `verify.started` must never be deleted or reused and `--verify` must never be retried.

## Phase 7 — Decision, preservation and recovery

| Observation | Final disposition |
| --- | --- |
| Exact real post-Core-DML injection + single acknowledged Core rollback + one-shot `no-change PASS` + independent selected-row/count/file reconciliation | **RB01-A TECHNICAL PASS** (bounded Core-acknowledged rollback, not physical transaction ID) |
| Persisted unexpected business row, demonstrated COMMIT, or escaped business artifact | **FAIL / incident** |
| No injection marker, wrong isolation, wrong source, ambiguous ACK/depth/connection, missing evidence, unexpected SQL/transport exception, incomplete file boundary | **INSUFFICIENT EVIDENCE / STOP** |

Keep private originals immutable: `baseline.json`, `baseline.sha256`, `verify.started`, `after.json`, `verdict.json`, injection trace, dedicated selected-row/file diffs, full test source/instrumentation hash, sanitized UI report and prior Owner-GO. Publish only a sanitized Markdown summary with no credentials, conf.php, tokens, real member data or private raw evidence.

Do **not** restore, reset or delete the failed attempt automatically as part of the test. A manual restore may be done **later**, only in the disposable clone, after full forensic preservation and a separate operator decision; never restore the active A/C schema. A new attempt (if ever justified) requires an entirely new reviewed case, fixture, identifier, baseline, GO and risk evaluation; no retry of the consumed case.

## Phase 8 — Required RB01-specific implementation/review deliverables

1. Reviewable test-only subclass/controller patch plus strict isolation allowlist, exactly-once injection flag, private marker and zero bypass of Core session/CSRF.
2. Automated **offline** positive/negative harness tests: injection happens only after the real `verifyStored()` seam, no COMMIT can be attempted, wrong fixture/profile/session/source fail closed, marker is one-shot and leaves no file in the W01 evidence directory.
3. Environment/restore/egress and separately checked DBA preflight; strict source/deployed build attestation and exact SHA.
4. Independent review of new fault-harness code and one explicit RB01-A Owner-GO.
5. One controlled real test, one W01 no-change baseline/verify, independent row/file/transaction trace reconciliation, and a separate result report.

**Until all these prerequisites are done, RB01-A remains NOT EXECUTED / NO-GO.** The existing W01 scripts cannot themselves perform a real rollback injection.
