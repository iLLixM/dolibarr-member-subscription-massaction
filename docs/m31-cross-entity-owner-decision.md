# M3.2 — Cross-entity qualification Owner decision

**OWNER ACCEPTED WITH DOCUMENTED SCOPE LIMITATION.** Following the
[PR #52 independent review](https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/52),
the Owner accepts **Cross-Entity Variant B** for v0.1.0. The independent
follow-up on `1009fe845134f5c1f872c64ed9f191c4970f8277` (2026-11-26) closed
F52-04/P3 and confirmed the Owner decision and all seven R0-04 criteria:
**INDEPENDENT DOCUMENTATION VERIFICATION COMPLETE**. PR #52 integrated the independently verified decision on 2026-11-26. Actual compliance verification on the exact installed release package under R0-04 remains pending. This is a qualification scope decision.
Core-Parity Variant B remains the distinct runtime architecture policy.

 P5L-A-S05 (MariaDBSnapshot), P5L-B-S05 (CoreGuarded),
P5L-C-S05 (EnhancedMySQL) and D-S06 (Hosted CoreGuarded) remain **BLOCKED / NOT EXECUTED**
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

## Historical alternatives considered

| Variant | Prerequisites and effort | Risk, release implications and acceptance criteria |
| --- | --- | --- |
| A — create an authorized second business entity and test | Owner authorizes isolated environments, supported Core entity configuration and two scoped non-global test roles, fresh synthetic members/types/customers/invoices and known directory/SMTP fences. Pin source and runtime, verify actor/entity and independent baseline, backups and recovery plan; coordinate B/C separately. Planning estimate 1–2 days after environments are available; Hosted provider/configuration lead time is unknown. | Exercise forbidden selection/direct entry, reused confirmation under another entity/user and affected Finance/PDF/recipient boundary with no unintended DB/Document/SMTP effects. Record observed rejection at the intended gate, same-entity positive control, and independent before/after reconciliation in both entities. A/C and B/D results remain separate. No production data, broad role grant or external send. Only tested entity-sharing configurations become eligible for later release qualification. |
| B — accept missing real evidence explicitly | Independent reviewer checks productive gates, existing synthetic tests and evidence limits. Owner specifies and accepts the restricted v0.1.0 qualification scope. Planning estimate less than one day for decision/documentation, excluding review scheduling. | Limit qualification to single-business-entity operation and the validated configurations, excluding real inter-entity or MultiCompany sharing claims. Keep all four cases BLOCKED/NOT EXECUTED, keep safeguards, describe deployment constraint in release documentation and verify it on the exact-package lane. Multi-entity use requires separate evidence and decision. |

## Qualified deployment conditions

- One qualified productive business entity with an active positive business
  entity ID. MultiCompany operation and inter-entity business sharing or
  interoperability are outside the qualification.
- Members and membership types must be admitted in the qualified entity's
  effective Core context. Customer and invoice relationships must match the
  qualified financial entity. PDF and document roots must correspond to it.
- Effective Core and module rights, entity consistency, profile binding,
  confirmation, transaction, financial and document safeguards still apply.
  Global administrator entity 0 supplies neither scoped permission nor a
  second business entity.
- Extensions must not introduce unqualified cross-entity behavior.
  Legitimate non-business global configuration and shared Core definitions
  are permitted within the single-business-entity installation.

These conditions define which deployment can rely on the qualification.
They do not introduce a runtime default deny, global entity allowlist or
MultiCompany admission veto, and establish no runtime multi-tenant isolation
guarantee. Any intended installation outside these conditions needs separate
qualification and Owner approval. No real cross-entity PASS is claimed.

See [mandatory R0-04 verification](release-qualification-v0.1.0.md#r0-04-cross-entity-variant-b-compliance).
The M3.2 scope choice is complete with documented limitations. See the [M3.4 Owner decision](m34-integrated-owner-acceptance.md) for the separate CF05-07 and CF03-RISK decisions and formal development closure. Exact-package qualification, operational readiness, independent release review and separate Owner Release GO remain open.
