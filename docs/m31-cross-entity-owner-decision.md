# M3.1 — Cross-entity qualification Owner decision paper

**Decision pending.** P5L-A-S05 (MariaDBSnapshot), P5L-B-S05 (CoreGuarded),
P5L-C-S05 (EnhancedMySQL) and D-S06 (Hosted CoreGuarded) remain **BLOCKED**
for real qualification. Local identities occupy one business entity plus
global administrator entity 0, which cannot serve as a second independent
business entity. Multi-entity is disabled on D and no authorized second
entity exists there. [A–C readback](evidence/cf03/phase5-local-completion-2026-11-25.md) ·
[D readback](evidence/cf03/phase5-hosted-d-2026-11-25.md)

## Actual decision boundaries

| Productive admission | Existing synthetic evidence and boundary |
| --- | --- |
| List entry calls `msmCheckMembers()` for Core entity visibility and restricted area; `msmReadRun()` and `msmReadPreview()` bind owner, entity, expiry and server-held selection. The controller reruns checks and profile binding before business work. [Selection](../lib/membersubscriptionmassaction.lib.php) · [Preview](../lib/preview.lib.php) · [Controller](../subscription.php) | [Selection and foreign member cases](../test/unit/run.php) and [request cases](../test/unit/request.php) cover mocked Core scope and mismatched session bindings; actual rights/CSRF are separately covered inside one entity. |
| Profile and checkpoint revalidate original owner/entity/revision; booking refreshes permissions and member/type scope before write. Invoice checks local entity and planned/existing customer identity. [Profile](../class/transactionprofile.class.php) · [Booking](../class/booking.class.php) · [Invoice](../class/invoice.class.php) | [Profile](../test/unit/profile.php), [rights](../test/unit/f29-rights.php), [finance](../test/unit/cf05-finance.php) and [file-session](../test/unit/coreguarded-session.php) tests probe contracts; doubles cannot establish a real cross-entity Core configuration. |
| PDF/attachment path uses invoice entity/output root, current rights and invoice relation; mail binds actual recipient, entity and confirmed row. [PDF](../class/invoicepdf.class.php) · [Mail](../class/mail.class.php) | [PDF identity/path](../test/unit/invoicepdf.php) and [recipient and checkpoint](../test/unit/coreguarded-session.php) tests exercise isolated contracts. Actual MultiCompany visibility, sharing, role mappings and cross-entity effect paths remain unverified. |

## Options

| Variant | Prerequisites and effort | Risk, release implications and acceptance criteria |
| --- | --- | --- |
| A — create an authorized second business entity and test | Owner authorizes isolated environments, supported Core entity configuration and two scoped non-global test roles, fresh synthetic members/types/customers/invoices and known directory/SMTP fences. Pin source and runtime, verify actor/entity and independent baseline, backups and recovery plan; coordinate B/C separately. Planning estimate 1–2 days after environments are available; Hosted provider/configuration lead time is unknown. | Exercise forbidden selection/direct entry, reused confirmation under another entity/user and affected Finance/PDF/recipient boundary with no unintended DB/Document/SMTP effects. Record observed rejection at the intended gate, same-entity positive control, and independent before/after reconciliation in both entities. A/C and B/D results remain separate. No production data, broad role grant or external send. Only tested entity-sharing configurations become eligible for later release qualification. |
| B — accept missing real evidence explicitly | Independent reviewer checks productive gates, existing synthetic tests and evidence limits. Owner specifies and accepts the restricted v0.1.0 qualification scope. Planning estimate less than one day for decision/documentation, excluding review scheduling. | Limit qualification to single-business-entity operation and the validated configurations, excluding real inter-entity or MultiCompany sharing claims. Keep all four cases BLOCKED/NOT EXECUTED, keep safeguards, describe deployment constraint in release documentation and verify it on the exact-package lane. Multi-entity use requires separate evidence and decision. |

**Recommendation** — Choose A if MultiCompany or cross-entity permission
configurations are part of the intended v0.1.0 deployment. Otherwise prefer
B for a bounded single-business-entity release, conditional on documented
independent agreement and separate Owner acceptance. With no decision,
the real cases remain blocked and the qualification scope unresolved.
Synthetic coverage does not establish how arbitrary configuration or
extension-driven sharing behaves. The decision does not grant Release GO.
