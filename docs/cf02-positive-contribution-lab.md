# Historical POS01-A/B runbook — tests completed (2026-11-20)

**Do not rerun these instructions for members 56 or 57 or reuse their consumed baseline directories.** The original read-only witness states remain STOP (A: incorrect selected dates and absent marker; B: absent description marker only). The Owner accepted the positive CoreGuarded contribution/Agenda evidence with a marker-only waiver for *external review entry*, not as a rewritten witness PASS. See [the Owner-acceptance evidence](evidence/cf02/pos01-owner-test-acceptance-2026-11-20.md) and [review handoff](cf02-review-handoff.md). The numbered steps below are preserved as the **historical original test protocol**, not a new instruction to book again.

# CF-02 POS01 – restricted MySQL real-HTTP contribution witness

**Status: witness prepared; real business DML NOT EXECUTED.** This is a
source-bound, operator-run test in the isolated Dolibarr V24.0.3/MySQL 8.4.13
laboratory. The witness CLI is read-only: it never INSERTs, UPDATEs,
ROLLBACKs, sends mail or performs HTTP requests. The single approved business
booking happens separately through Dolibarr's genuine authenticated UI.

## Exact safety contract

- Only the local environment at 127.0.0.1:3307 with database
  `dolibarr_i105_v24`, effective account
  `dolibarr_i105_v24_limited@10.0.2.100`, and CoreGuarded profile is admitted.
- Select one **newly created synthetic member**, never an existing real member:
  **technical ID `56` and Core-generated reference `MEM2611-0056`**.
  A member login is **not required**. For an independent synthetic marker,
  the surname must be `CF02LAB-20261120A1`, first name `Synthetic`,
  email `cf02lab-20261120a1@example.invalid`, entity 1, validated status,
  no linked third party and no existing subscriptions.
  If one of these fields differs, update **only the synthetic test member**
  through the regular Dolibarr UI before running the witness.
- Use an existing active, subscription-required member type with configured
  amount **120.00**; do not modify existing non-synthetic types.
- Book exactly `2080-01-01` to `2080-12-31` with this exact marker in
  description, no invoice/customer auto-create/mail, and no overlap.
- In POS01, leave standard Agenda MEMBER_SUBSCRIPTION_CREATE enabled and use
  `--agenda=on`. If the selected module/Agenda configuration differs, STOP.
- Preserve evidence exclusively under
  `/var/lib/dolibarr-test-results/cf02-pos01-20261120a1` (private mode 0700).
  The separate baseline digest and a one-time `verify.started` marker block
  accidental evidence overwrite/repeated verification.

## Step 1 – read-only gate (no booking permission implied)

From the CF-02 worktree after a clean git checkout and exact HEAD check:

```sh
cd ~/dolibarr-member-subscription-massaction-cf02
git rev-parse HEAD
git status --short
MEMBER_ID=56
MEMBER_REF=MEM2611-0056
MARKER=CF02LAB-20261120A1
EVIDENCE_DIR=/var/lib/dolibarr-test-results/cf02-pos01-20261120a1

sudo -u www-data env CF02_DOLIBARR_ROOT=/home/marcel/dolibarr-i105-v24-mysql \
  /usr/bin/php -d xdebug.mode=off tools/cf02-contribution-witness.php \
  --check-only --member-id="$MEMBER_ID" --member-ref="$MEMBER_REF" --marker="$MARKER" \
  --start=2080-01-01 --end=2080-12-31 --amount=120.00 --agenda=on
```

Expected: `status=PASS`. STOP means **no business booking**. `CF02FixtureNotIsolated` indicates that member ID/reference or another mandatory synthetic marker/status/entity/third-party field differs. An empty member login is permitted. The witness checks
the exact synthetic identity, database/privilege/context, profile, member
type and status, empty prior contribution/Agenda history, InnoDB engines,
Agenda event configuration, and key protected-table counts.

## Step 2 – one immutable pre-booking snapshot

Only after reviewing Step 1 and separately approving the single synthetic
business write, run **once** with all previous parameters unchanged:

```sh
sudo -u www-data env CF02_DOLIBARR_ROOT=/home/marcel/dolibarr-i105-v24-mysql \
  /usr/bin/php -d xdebug.mode=off tools/cf02-contribution-witness.php \
  --baseline --member-id="$MEMBER_ID" --member-ref="$MEMBER_REF" --marker="$MARKER" \
  --start=2080-01-01 --end=2080-12-31 --amount=120.00 --agenda=on \
  --evidence-dir="$EVIDENCE_DIR"
```

Expected `status=PASS`, writing only protected `baseline.json` and
`baseline.sha256` outside the web root.

## Step 3 – exactly one Owner-operated authenticated UI action

In `http://i105-v24.localhost:8091/`, select **only** the newly created
synthetic member, open the existing module mass action, review the date
range, amount, marker-containing description and off invoice/customer/mail
options, and manually confirm **once**. Record the UI result without
replaying the POST, reopening the confirmation or cleaning up the fixture.
STOP on unexpected preview, warning, profile switch or inconsistent state.

## Step 4 – one independent readback

```sh
sudo -u www-data env CF02_DOLIBARR_ROOT=/home/marcel/dolibarr-i105-v24-mysql \
  /usr/bin/php -d xdebug.mode=off tools/cf02-contribution-witness.php \
  --verify --member-id="$MEMBER_ID" --member-ref="$MEMBER_REF" --marker="$MARKER" \
  --start=2080-01-01 --end=2080-12-31 --amount=120.00 --agenda=on \
  --evidence-dir="$EVIDENCE_DIR"
```

The witness preserves `verify.started`, `after.json`, `verdict.json`.
It requires one persisted Core subscription with exact member/type/amount/
dates/marker, matching member end date (Core R16), one standard Agenda event
and actor resource, unchanged source/profile, and **no unexpected customer,
invoice, financial or payment table count changes**. A separate read-only
connection is used. Any mismatch is STOP and must not trigger a rebooking.

Readback is not a physical-event proof or proof that the HTTP client received
a successful Core-COMMIT acknowledgement. Report the frontend result
separately; preserved raw evidence remains private until sanitized.
Do not share credentials, CSRF tokens, cookies, sessions or private member
records.

**Deferred:** separate qualified rollback fault injection (RB01),
multi-member NO_CHAIN replay and checkpoint (BATCH01), CHAIN/RELEASE,
concurrency and real EnhancedMySQL/MariaDB invoice regressions.
Independent code/transaction review and the Owner's merge/release decisions
remain mandatory; this procedure is not a production authorization.
