# P3 synthetic qualification and evidence guide

Owner decision, 2026-11-15: **P3 SYNTHETIC CROSS-VERSION QUALIFICATION – ACCEPTED
WITH DOCUMENTED LIMITATIONS**. P3 is formally closed. No confirmed implementation
defect or additional real P4 test need was identified for the bounded synthetic scope.
The later independent PR11 review required targeted corrections; see the
[post-P3 correction addendum](phase4-implementation-validation.md#post-p3-pr11-review-correction-addendum--2026-11-15).
P3 acceptance remains historical and closed; follow-up review/merge approval is pending.

Qualified implementation: `a1843e14c71c89f7148aa2e8f6c5352409bca7b1`.
[Run 43731843114](https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/43731843114)
passed 21/21 jobs; P3 independently checked original job logs for module/Core
checkout SHA, installed PHP, required runners and successful steps. A subsequent
documentation-only commit does not imply that new code was qualified in P3;
its automatically triggered CI must be reported separately in the PR.

| Core tag | Immutable Core SHA | Qualified CI PHP |
| --- | --- | --- |
| 22.0.0 | `49b9a6d19f3deb6d410c0e9b3310e95be4ea7710` | 7.4, 8.2 |
| 22.0.1 | `dc00877d940da8326216e51263a85b0a4a418f22` | 7.4, 8.2 |
| 22.0.2 | `f70b6b43ecfcebc69264bc008aafcb9b9de2b23c` | 7.4, 8.2 |
| 22.0.3 | `e57703a6b69ad6da5876692d73defd7e754bb5c6` | 7.4, 8.2 |
| 22.0.4 | `4f6e636e7e2aea1abde2c2b6f6ac4ffd34429f7f` | 7.4, 8.2 |
| 22.0.5 | `88ad132763be45e9620128c1830e41493e1cdd05` | 7.4, 8.2 |
| 23.0.0 | `57a1f05d490a7a80944a8232e9c613e6556d2704` | 8.2 |
| 23.0.1 | `b9f5895c68a9707e5b5616ef279bd7fe83760de6` | 8.2 |
| 23.0.2 | `ccef1102e6850b7545be7bad91cf0cc4c74ac6ea` | 8.2 |
| 23.0.3 | `57d0be7dc97140d7d47e826875ba9256ec3b6357` | 8.2 |
| 23.0.4 | `cb82037066c1c71f7c867482e9e92975222dfc14` | 8.2 |
| 24.0.0 | `769c7db907099643558e77d7002c109cfda919e5` | 8.2 |
| 24.0.1 | `b7958385f00a92219f76a4dfea67dae93df97a25` | 8.2 |
| 24.0.2 | `7e927764767343334aaa067ca5ec054cfea0a26b` | 8.2 |
| 24.0.3 | `5c6c7ff6a5112413b1c27221c8d11750afad56f8` | 8.2 |

## Regular CI evidence

[Workflow](../.github/workflows/ci.yml): lint/whitespace,
[contracts and language parity](../test/unit/run.php),
[loading DOM tests](../test/unit/loading.js),
[booking/R16](../test/unit/booking.php),
[mail adapter](../test/unit/maildelivery.php),
[invoice profile/VAT/price](../test/unit/invoiceprofile.php),
[invoice transactions](../test/unit/invoice.php),
[PDF identity](../test/unit/invoicepdf.php),
[post-commit checkpoints](../test/unit/postcommit.php).
Actual pinned Core calendar/update_end_date/VAT/price functions execute against
inert adapters. Customer/invoice APIs, DB/rights, PDF generation, template and
transport layers are substantially doubled. Included assertion counts overlap and
are not unique totals. F01 keeps independent SQL checks for the frozen 15-release
catalog and observer consistency for every other admitted version. Unknown-label
subprocesses model future behavior; they do not qualify actual future Cores.

## Supplemental P3 evidence and P3-DOC01

The private P3 package and its original 78-checksum manifest remain unchanged.
A separate dated `addenda/P3-DOC01-20261115/ADDENDUM.md` plus corrected JSON mapping
and its own checksum list now identifies the actual supplemental sources:

- Nullable history, native DoliDB transaction semantics and Core subscription
  argument/note/failure contracts: `<tag>/core-contract-probe.php`, `<tag>/result.log`,
  `supplemental-source-probes.json`.
- Additional country-VAT/negative/zero hook results: `p3-vat-supplement.php.txt`,
  `<tag>-vat-supplement.log`, `supplemental-vat-results.json`.

These are package-relative identifiers, **not public repository links**. All
supplements ran only on PHP 8.2 for the 15 tags. Corresponding booking.php and
invoiceprofile.php references identify related regular CI tests, not the external
supplemental program implementations. No native PHP 7.4 supplement execution,
new CI run, test result or real business test is asserted by this pointer correction.
Private packages are available only through Owner-controlled review access and are
not automatically published with this PR.

## Admission, evidence and limits

Admission starts at 21.0.0 without upper bound; V21/future versions are unqualified.
The 15 tags above have bounded synthetic evidence, not full real integration.
Historical V22.0.5 G2/G3/G4 and V24.0.3 C3 evidence remains tied to its original
commits/profiles. No new real V23 integration, general product/tax/entity/PDF/SMTP
qualification, Exactly-once, crash-recovery or universal transaction guarantee.
No real P4 test is needed for this synthetic scope; any later specific live profile
requires separate justification and Owner authority. All 71 requirement mappings,
historical risk decisions and 18 dedicated live gaps remain unchanged.
Version 0.1.0-dev, temporary ID 584620 and production/public-release restrictions remain.
