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

Accepted decisions now extend through **ADR-0063**.

Latest planning milestones:
- ADR-0057 — Membership billing source facts/reconciliation + MB0–MB5.
- ADR-0058 — Email delivery truth + ET0–ET5.
- ADR-0059 — JobService at-least-once/idempotency/fairness/backpressure semantics.
- ADR-0060 — Remote Service purpose-scoped privacy/retention.
- ADR-0061 — stable semantic Backup family/provider identity registry.
- ADR-0062 — Manual/Woo Core/Woo Subscriptions/SureCart billing source-truth profiles.
- ADR-0063 — wp_mail/SMTP/SES/SendGrid/Mailgun/Postmark email source-truth profiles.

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

## Current Membership billing state

Canonical path:

`verified source facts → Billing Adapter → reconciliation → Membership policy → Enrollment → Entitlement`

Initial profile keys:
- `billing.manual`;
- `billing.woocommerce-order`;
- `billing.woocommerce-subscriptions`;
- `billing.surecart`.

Static current research maturity:
- four initial profiles: **BE3**;
- **MB-certified profiles: 0**.

Key accepted rules:
- Woo paid truth does not depend on `Completed` alone;
- order + line item identity matters;
- refunds/partial refunds are separate source facts;
- Woo Subscriptions pending-cancel is paid-through cancellation intent;
- failed renewal/on-hold remains policy input, not automatic permanent revoke;
- SureCart Purchase + Subscription + Refund context reconciles together;
- duplicate/out-of-order provider events are expected and reconciled;
- test/live sources remain isolated.

## Current Email provider state

Canonical path:

`Recipient Delivery → Rendered Message → Transport Attempt → Provider Message Reference → verified Provider Event Ledger → derived outcome`

Initial profile keys:
- `email.wordpress-wp-mail`;
- `email.smtp-generic`;
- `email.amazon-ses`;
- `email.twilio-sendgrid`;
- `email.mailgun`;
- `email.postmark`.

Static current research maturity:
- six initial profiles: **EE3**;
- **ET-certified profiles: 0**.

Accepted provider rules:
- `wp_mail()` success means local processing only;
- generic SMTP relay acceptance is not inbox/final-receiving-server proof;
- SES SEND is not DELIVERY;
- SendGrid processed is not delivered;
- Mailgun accepted is not delivered;
- Postmark Delivery means destination server accepted and explicitly does not prove inbox placement;
- SendGrid/Mailgun signed webhook profiles are documented; Postmark is not assigned a fabricated signature capability;
- late bounce/complaint can coexist with earlier delivery evidence;
- provider suppression/unsubscribe does not automatically mutate unrelated WPE channels/access;
- open/click observations never become Read/Human Seen/Inbox Confirmed.

Provider-specific paper source: `docs/ARCHITECTURE/EMAIL-PROVIDER-CAPABILITY-MATRIX.md` + ADR-0063.

## Platform evidence blockers

P-001 compatibility; P-002 UI; P-003 Job backend; P-004 Definition DDL; P-005 Vault implementation; P-006 Free↔Pro; P-007 CI; P-008 build; P-009 Query; P-010 Relations; P-011 Workflow; P-012 Membership; P-013 Backup.

Additional Email/Remote Service provider runtime evidence remains tracked separately.

**None executed.**

## Verification state

Verified planning/documentation only:
- planning branch isolated from `main`;
- **31/31 Exhaustive, 0/31 Authorized**;
- ADR/governance synchronized through ADR-0063;
- Remote Service privacy matrix/ADR-0060 committed;
- Backup family/provider registry + normalized 34-target matrix + ADR-0061 committed;
- Membership provider profiles + ADR-0062 committed;
- Email provider profiles + ADR-0063 committed;
- Job provider-neutral architecture committed;
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
- email sends/webhook tests/bounce simulators;
- WPE service/diagnostics transmission;
- Backup uploads/deletes/restores;
- performance benchmarks;
- releases/deployment.

## Next allowed planning-only priorities

1. Remote Service consent-gated privacy/retention evidence protocol.
2. Refresh remaining low-evidence Backup provider docs.
3. Membership provider version/evidence refinements.
4. Email provider version/evidence refinements.
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
