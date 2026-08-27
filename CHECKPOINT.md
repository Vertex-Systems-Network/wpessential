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

Accepted paper semantics: Job Type/Schedule/Job/Attempt/Runner separation, explicit business idempotency, reviewed urgency + starvation protection, resource/concurrency keys, checkpointed chunking/backpressure, cooperative cancellation and no business dependency assumption from queue order.

Concrete Action Scheduler backend remains P-003 Proposed/evidence-gated.

## Current Remote Service state

Accepted: Free activation sends nothing to WPE service; account link is purpose-scoped and not telemetry consent; public resources avoid hidden identifiers where possible; diagnostics needs separate preview/approval; RR0–RR6 retention classes; disconnect is distinct from account/support/commercial-history deletion.

Future executable verification is bounded by `docs/QUALITY/REMOTE-SERVICE-PRIVACY-RETENTION-EVIDENCE-PROTOCOL.md`, containing 30 consent-gated fixtures. **No fixture has been executed.**

## Current Backup state

Accepted manifest-first bundle, encryption/recovery architecture, Remote Copy lifecycle, C0–C4 restore-first certification, semantic `bf.*` family keys, separately versioned provider profiles, legacy/non-canonical PF aliases, and SE0–SE3 static-evidence separation.

Current catalog: **34 target destinations, 34/34 stable profiles, 0 certified**.

## Current Membership billing state

Canonical path: `verified source facts → Billing Adapter → reconciliation → Membership policy → Enrollment → Entitlement`.

Initial profiles: `billing.manual`, `billing.woocommerce-order`, `billing.woocommerce-subscriptions`, `billing.surecart`.

Static maturity: **4 BE3 profiles; 0 MB-certified**.

## Current Email provider state

Canonical path: `Recipient Delivery → Rendered Message → Transport Attempt → Provider Message Reference → verified Provider Event Ledger → derived outcome`.

Initial profiles: `email.wordpress-wp-mail`, `email.smtp-generic`, `email.amazon-ses`, `email.twilio-sendgrid`, `email.mailgun`, `email.postmark`.

Static maturity: **6 EE3 profiles; 0 ET-certified**.

Accepted provider rules: `wp_mail()` success means local processing only; SMTP relay acceptance is not inbox/final receiving-server proof; SES SEND ≠ DELIVERY; SendGrid processed ≠ delivered; Mailgun accepted ≠ delivered; Postmark Delivery means destination server accepted, not inbox placement; webhook security capabilities are provider-specific and never fabricated; late bounce/complaint can coexist with earlier delivery evidence; open/click never becomes Read/Human Seen/Inbox Confirmed.

## Platform evidence blockers

P-001 compatibility; P-002 UI; P-003 Job backend; P-004 Definition DDL; P-005 Vault implementation; P-006 Free↔Pro; P-007 CI; P-008 build; P-009 Query; P-010 Relations; P-011 Workflow; P-012 Membership; P-013 Backup.

Additional Email and Remote Service runtime evidence remains separately tracked. **None executed.**

## Verification state

Verified planning/documentation only:
- planning branch isolated from `main`;
- **31/31 Exhaustive, 0/31 Authorized**;
- ADR/governance synchronized through ADR-0063;
- Remote Service privacy matrix + ADR-0060 + future evidence protocol committed;
- Backup family/provider registry + normalized 34-target matrix + ADR-0061 committed;
- Membership provider profiles + ADR-0062 committed;
- Email provider profiles + ADR-0063 committed;
- Job provider-neutral architecture committed;
- no implementation/test/provider certification success claimed.

Not performed: installs/package setup, production source/bootstrap, DB migrations, queue runs, crypto execution, PHPUnit/Playwright, provider/API/webhook/SMTP calls, billing source objects/transactions, email sends/webhook tests/bounce simulators, WPE service/account-link/diagnostics transmission, remote privacy-retention fixtures, Backup uploads/deletes/restores, performance benchmarks, releases/deployment.

## Next allowed planning-only priorities

1. Refresh low-evidence Backup provider profiles from official docs.
2. Membership provider version/evidence refinements.
3. Email provider version/evidence refinements.
4. P-003/provider evidence plan refinement.
5. Keep governance/Draft PR synchronized.

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
