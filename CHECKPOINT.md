# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-08-27**  
Branch: `planning/master-architecture`  
Project state: **PARTIALLY COMPLETE — Phase 0 planning only**  
Production development authorization: **NOT GRANTED**

## Hard consent gate

Explicit owner consent is required before runtime/source/build/migration/test implementation, dependencies, executable spikes/benchmarks, queue execution, provider/API interactions, service transmission, SMTP/email sends, Backup/Restore operations or release packaging.

`continue` and planning acceptance do **not** authorize development.

Source of truth: `/DEVELOPMENT-CONSENT.md`, `AGENTS.md`, ADR-0014.

## Product milestone

- **31/31 Exhaustive product-option maturity**
- **0/31 Authorized**
- Implemented: none
- Runtime verified: none

## Accepted architecture

Accepted decisions now extend through **ADR-0062**.

Latest planning milestones:
- ADR-0057 — Membership billing source facts/reconciliation + MB0–MB5.
- ADR-0058 — Email delivery truth + ET0–ET5.
- ADR-0059 — JobService at-least-once/idempotency/fairness/backpressure semantics.
- ADR-0060 — Remote Service purpose-scoped privacy/retention.
- ADR-0061 — stable semantic Backup family/provider identity registry.
- ADR-0062 — Manual/Woo Core/Woo Subscriptions/SureCart billing source-truth profiles.

## Current JobService state

Accepted paper semantics:
- Job Type/Schedule/Job/Attempt/Runner separation;
- explicit business idempotency;
- reviewed urgency + starvation protection;
- resource/concurrency keys;
- checkpointed chunking and backpressure;
- cooperative cancellation;
- no business dependency assumption from queue order.

Concrete Action Scheduler backend remains P-003 Proposed/evidence-gated.

## Current Remote Service state

Accepted:
- Free activation sends nothing to WPE service;
- account link is purpose-scoped and not telemetry consent;
- public resources avoid hidden identifiers where possible;
- diagnostics needs separate preview/approval;
- RR0–RR6 retention classes;
- disconnect is distinct from account/support/commercial-history deletion.

Open: exact OpenAPI/OAuth/logging/retention/runtime evidence.

## Current Backup state

Accepted:
- manifest-first bundle;
- encryption/recovery architecture;
- Remote Copy commit/verification lifecycle;
- C0–C4 restore-first provider certification;
- semantic `bf.*` family keys + separately versioned provider profiles;
- numeric PF aliases are legacy/ambiguous;
- static SE0–SE3 evidence never implies C certification.

Current catalog:
- **34 target destinations**;
- **34/34 stable provider/family profiles**;
- **0 certified**.

Important paper overrides include S3 capability negotiation, generic WebDAV non-resumability, Nextcloud/ownCloud provider extensions, Graph Personal/Business profile split and native-provider no-inheritance.

## Current Membership billing state

Canonical path:

`verified source facts → Billing Adapter → reconciliation → Membership policy → Enrollment → Entitlement`

Initial profile keys:
- `billing.manual`;
- `billing.woocommerce-order`;
- `billing.woocommerce-subscriptions`;
- `billing.surecart`.

Accepted provider rules:
- Manual/Free is explicit WPE source, not a fake commerce record.
- Woo one-time uses order + line-item identity and supported paid-state APIs; `Completed` alone is not payment truth.
- Woo partial/full refund truth comes from refund/line evidence, not only order status.
- Woo Subscriptions `pending-cancel` is paid-through cancellation intent; `on-hold`/failed renewal is a policy input and can recover; role changes are not WPE authority.
- SureCart uses Purchase + Subscription + Refund context; period end and cancellation intent are separate source facts.
- SureCart webhook ingress requires verified HMAC/timestamp; duplicates/out-of-order events reconcile against current source objects.
- test/live source environments remain isolated.

Static current research maturity:
- four initial profiles: **BE3**;
- **MB-certified profiles: 0**.

## Email delivery state

Accepted:
- Recipient Delivery and Transport Attempt are separate;
- provider/API/SMTP acceptance is not receiving-server/inbox/read proof;
- provider Event Ledger preserves bounce/complaint/suppression/engagement evidence;
- ET0–ET5 governs future provider claims.

Provider-specific Email capability matrix remains next planning work.

## Platform evidence blockers

P-001 compatibility; P-002 UI; P-003 Job backend; P-004 Definition DDL; P-005 Vault implementation; P-006 Free↔Pro; P-007 CI; P-008 build; P-009 Query; P-010 Relations; P-011 Workflow; P-012 Membership; P-013 Backup.

**None executed.**

## Verification state

Verified planning/documentation only:
- planning branch isolated from `main`;
- ADR/governance synchronized through ADR-0062;
- Remote Service privacy matrix/ADR-0060 committed;
- Backup family/provider registry + normalized 34-target matrix + ADR-0061 committed;
- Membership provider profiles + ADR-0062 committed;
- Job/Email provider-neutral architecture committed;
- no implementation/test/provider certification success claimed.

Not performed:
- installs/package setup;
- production source/bootstrap;
- DB migrations;
- queue runs;
- crypto execution;
- PHPUnit/Playwright;
- provider/API/webhook/SMTP calls;
- billing source objects/transactions;
- Backup uploads/deletes/restores;
- performance benchmarks;
- releases/deployment.

## Next allowed planning-only priorities

1. Email provider-specific capability matrix.
2. Remote Service consent-gated privacy/retention evidence protocol.
3. Refresh remaining low-evidence Backup provider docs.
4. Membership provider version/evidence refinements.
5. P-003/provider evidence plan refinement.
6. Keep governance/Draft PR synchronized.

Before any executable work, explicit owner consent is required.

## Resume order
1. `/DEVELOPMENT-CONSENT.md`
2. `AGENTS.md`
3. `CHECKPOINT.md`
4. `docs/MODULES/OPTION-COVERAGE-MATURITY.md`
5. `docs/IMPLEMENTATION-READINESS-MATRIX.md`
6. `docs/OPEN-DECISIONS-REGISTER.md`
7. `docs/DECISIONS/README.md`
8. relevant architecture/security/module/provider docs

Repository evidence overrides conversational memory.
