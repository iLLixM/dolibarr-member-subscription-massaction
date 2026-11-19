# AP3 experimental evidence and reproduction

Start with [the technical report](../../docs/ap3-completion-gate-v1.md) and
[evidence/TEST-MATRIX.json](evidence/TEST-MATRIX.json). Base: PR26 normal merge
`c79830976a5f1e1bd57d51005ff34c10c424e36f`. No production includes reference this
folder. The separate Draft-PR identifies the review commit and CI.

## Safe offline review

From repository root:

```bash
python3 experiments/ap3/verify.py
python3 experiments/d01/verify.py
sha256sum --check experiments/ap3/SHA256SUMS
```

The verifier makes no network or DB connections. Core hashes are in
`core-pins.json`; event arrays record actual SQL/native results. Native connection
IDs separate independent auditors from the subject connection. Xdebug monitor
entries expose function/caller only, not arguments. Original STOPs remain visible.

## Authorized run provenance — not a rerun recipe

Prerequisites: pinned Core24.0.3/commit5c6c7ff; PHP8.2.35 with mysqli/mysqlnd,
Xdebug function monitor and shmop; existing isolated MySQL8.4.13 schema/account
`msmsh01lab` with only schema DML rights; no P_S grant; existing artificial
InnoDB `probe(id,label,n)`. Do not create grants or reuse an application database.
Protected app JSON is supplied locally, mode0600, outside Git (`AP3_CONFIG` may
select it). Its password is never printed. No credential example is published.

Executed stages, in order (all one-shot):

1. `inspect.php --inspect <new-output>` used the genuine Core classes and
   recorded the81-row starting hash/rights. Read its argument guard before use.
2. `cli.php --run-approved <new-output>`: corrected CLI-r1 used rows6000–6036,
   exact6 completion/end pairs, observation/profile negatives and foreign1568.
   The failed initial `cli.json` was retained.
3. `lost-ack.py --run-approved COMMIT <new-output>` and separate ROLLBACK:
   one loopback proxy client/end each; rows6400–6405, real OK discarded.
4. Owner HTTP R1, after the failed original starter and a read-only unused-range
   reconciliation. See [OWNER-HTTP-R1.txt](OWNER-HTTP-R1.txt). An existing I105
   synthetic-user password is entered only in the Owner's terminal; Owner invokes
   the protected www-data bridge. It starts its own temporary loopback PHP server,
   actual main.inc.php authentication and actual files sessions. The lab is separate.
5. `audit.php --read-only <new-output>` independently compared old/new lab rows,
   rights and pinned sources. Evidence generation never resets/restores data.

These reserved ranges now contain evidence and cannot be executed again.
To reproduce technically, first review and authorize a **new isolated fixture
range**, update the explicit case definitions/allow fence, capture a fresh baseline
and outputs, and use a fresh authenticated experimental session. Do not remove
markers, delete rows, repeat consumed POSTs as positive work or overwrite results.
No production CF02 adapter is installed. The original `owner-http.py` and router
are retained historical failures, not supported starters.

Initial Completion assignments were separately Owner-approved only on new test
connections. The adapter does no session/global normalization, custom transaction
end SQL or retries. Proxy logic reuses the historical D01/SH01 packet framing but
never logs packet/auth payloads. The only writes are artificial probe values and
normal authenticated session/login audit. No Core business operation is selected.

The HTTP bridge's mode0600 shared memory is overwritten/deleted in finally; its
locator alone is in child environment. Credentials, route capability, cookies,
password and CSRF remain private. A hard process kill can require Owner cleanup;
there is no general crash-proof shared-memory cleanup claim. Protected runtime
results are retained privately, not uploaded. Only sanitized JSON is published.

G1/G6 remain PARTIAL for explicitly unperformed actual late Completion/custom-path
faults. Independent gate review and separate Owner acceptance are mandatory.
