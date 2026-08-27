# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-08-28**  
Branch: `planning/master-architecture`  
Project state: **PARTIALLY COMPLETE — Phase 0 planning only**  
Production development authorization: **NOT GRANTED**

## Hard consent gate

Explicit owner consent is required before runtime/source/build/migration/test implementation, dependencies, executable spikes/benchmarks, queue execution, provider/API interactions, service transmission, SMTP/email sends, Backup/Restore operations or release packaging.

`continue` and planning acceptance do **not** authorize development.

Source of truth: `/DEVELOPMENT-CONSENT.md`, `AGENTS.md`, ADR-0014.

## Product milestone

- **31/31 Exhaustive product-option maturity**
- **31/31 Multisite scope behavior mapped**
- **0/31 Authorized**
- **0 surfaces Multisite runtime-certified at MS1+**
- Implemented: none
- Runtime verified: none

## Accepted architecture

Accepted decisions now extend through **ADR-0075**.

Latest milestones:
- ADR-0069 — unified WordPress Multisite scope/ownership/isolation.
- ADR-0070 — Product License installation/network/site-allocation + clone/staging/migration/transfer semantics.
- ADR-0071 — Multisite physical topology classes PT-A…PT-F.
- ADR-0072 — Product License remote resource/conflict-state model.
- ADR-0073 — Definition Repository PT-C D1 benchmark baseline.
- ADR-0074 — Relations R1/PT-D benchmark baseline with R2/PT-E mandatory comparison.
- **ADR-0075 — Multisite Site Lifecycle Coordinator.**

## Current Multisite state

Authoritative docs:
- `docs/ARCHITECTURE/MULTISITE-SCOPE-OWNERSHIP-MODEL.md`;
- `docs/MODULES/MULTISITE-SCOPE-OPTION-MATRIX.md`;
- `docs/ARCHITECTURE/MULTISITE-SITE-LIFECYCLE-COORDINATOR.md`;
- `docs/QUALITY/MULTISITE-SCOPE-ISOLATION-EVIDENCE-PROTOCOL.md`.

Core accepted rules remain: site scope default, network scope explicit, target-site capability + WPE Policy, cross-site Relations/Queries off by default, bounded JobService network fan-out, cache/Vault/Membership/Backup/Reset/Import isolation.

Future Multisite certification: MS0 → MS1 → MS2 → MS3 → MS4. **0 runtime fixtures executed.**

### Site Lifecycle Coordinator — ADR-0075

WPE now has one paper coordination model for:
- site discovery/initialization/provisioning;
- archive/spam/deleted status transitions/reactivation;
- destructive impact Plan;
- draining Jobs/Workflow/Email/Webhooks;
- access/cache/Membership revoke-to-deny behavior;
- PT-C tombstone/archive;
- PT-D per-domain retention/cleanup;
- PT-E table inventory/cleanup;
- PT-F Product License/Connection/external reconciliation;
- WordPress uninitialize vs site-row deletion distinction;
- clone/migration/network transfer;
- durable lifecycle journal/retry/unknown-remote-outcome state.

WordPress lifecycle hooks/handlers have **not** been implemented or executed.

## Current physical topology state

ADR-0071 topology classes:
- PT-A native WP site/blog storage;
- PT-B native WP network/global primitives;
- PT-C WPE global scoped control-plane;
- PT-D WPE global scoped high-volume runtime;
- PT-E WPE per-site custom runtime;
- PT-F external authority + local scoped references/cache.

Current direction:
- Definition Repository PT-C;
- Job logical history PT-C/PT-D;
- Audit PT-D;
- Relations R1/PT-D future benchmark baseline;
- Membership/Workflow/Notification/Event Inbox PT-D candidates;
- Forms/Chat PT-D vs PT-E evidence-gated.

No DDL, migration, table, index or DB benchmark has been executed.

## Definition Repository — ADR-0073

Future P-004 baseline **D1**:
- numeric physical row IDs;
- transparent textual/ASCII-compatible portable UUID;
- explicit network/site scope;
- bounded normalized identity keys;
- immutable application-validated text revision payload;
- minimal workload indexes;
- application same-definition pointer integrity + diagnostics.

D2 binary UUID, D3 native JSON and D4 FK/constraint profiles remain comparisons. Exact SQL/types/index/locking/performance evidence remains P-004.

**P-004 executed DDL/benchmarks: 0.**

## Relations — ADR-0074

Future P-010 baseline **R1/PT-D**:
- one shared scoped universal typed edge-table family;
- explicit site/network identity;
- forward/reverse lookup;
- concurrency-safe cardinality/duplicate semantics;
- pivot/order support without per-relation DDL by default;
- Policy-aware reads/writes;
- Site Backup scoped extraction;
- cross-site Relations Off by default.

Required comparison:
- R2 PT-E per-site universal edge table.

Exceptional only:
- R3 per-relation physical table after evidence-backed scale need.

**P-010 Relation DDL/benchmarks: 0.**

## Product License / remote resource state — ADR-0070 + ADR-0072

Remote resources remain separated: Account, Product Contract, Installation Activation, Network Activation, Site Allocation and independently signed Product Entitlement.

Contract/activation/allocation/environment/conflict state are separate. Mutations must eventually be idempotent/concurrency-safe; unknown remote outcome reconciles instead of guessing; clone conflict never silently grants second production allocation; service outage ≠ expiry; Product License never becomes Membership authorization.

**0 product-license service/OpenAPI/allocation/clone/transfer fixtures executed.**

## Current JobService / Action Scheduler state

Action Scheduler 4.1.0 remains reviewed candidate only. WPE JobService owns business idempotency/fairness/history and Site Lifecycle draining semantics. **P-003 unexecuted.**

## Current Remote Service state

Purpose-scoped privacy/retention accepted; 30 future privacy fixtures documented, **0 executed**. Product License OpenAPI/resource implementation remains pending.

## Current Backup state

- 34 target destinations / 34 stable profiles;
- **0 C-certified / 0 C3 Supported**;
- scoped site-row extraction from shared WPE tables required;
- lifecycle destructive actions can require verified recovery point;
- P-013 unexecuted.

## Current Membership billing state

Manual, WooCommerce 11.0.1, Woo Subscriptions 9.1.0, SureCart WP 4.7.0 + hosted API remain paper profiles. **4 BE3 / 0 MB-certified**. Site teardown must stop local authorization without automatically cancelling external billing unless explicit reviewed policy says so.

## Current Email state

`wp_mail`, SMTP, SES, SendGrid, Mailgun, Postmark remain paper profiles. **6 EE3 / 0 ET-certified**. Site lifecycle drain semantics are paper-only.

## Platform evidence blockers

P-001 compatibility/Multisite; P-002 UI; P-003 Job backend; P-004 Definition D1-vs-D2/D3/D4; P-005 Vault; P-006 Free↔Pro/Product Allocation; P-007 CI; P-008 build; P-009 Query; **P-010 Relations R1-vs-R2**; P-011 Workflow; P-012 Membership; P-013 Backup; plus Site Lifecycle runtime evidence.

No executable evidence has run.

## Verification state

Verified planning/documentation only:
- planning branch isolated from `main`;
- **31/31 Exhaustive, 0/31 Authorized**;
- **31/31 Multisite scopes mapped; 0 MS1+ certified**;
- ADR index/Open Decisions/Readiness/Checkpoint synchronized through **ADR-0075**;
- Product License remote state model + ADR-0072 committed;
- Definition PT-C alternatives + ADR-0073 committed;
- Relations PT-D/PT-E profile + ADR-0074 committed;
- Site Lifecycle Coordinator + ADR-0075 committed;
- P-003/P-004/P-010/P-012/P-013 unexecuted;
- Membership 0 MB-certified;
- Email 0 ET-certified;
- Backup 0 C-certified;
- Remote Service fixtures 0 executed;
- no implementation/test/provider-certification success claimed.

Not performed: dependency installation, Multisite runtime setup, WordPress lifecycle hooks, Action Scheduler bootstrap, production source, DB tables/migrations/indexes, queue runs, crypto execution, PHPUnit/Playwright, provider/API/webhook/SMTP calls, commerce transactions, Email sends, WPE account/license calls, Backup/Restore, DB/performance benchmarks, release/deployment.

## Next allowed planning-only priorities

1. Product License exact OpenAPI candidate shapes without service implementation.
2. P-004 Definition benchmark fixture/query-plan design without execution.
3. P-010 Relations benchmark fixture/query-plan design without execution.
4. Site Lifecycle Coordinator evidence protocol refinement without hooks/tests.
5. Forms/Chat PT-D-vs-PT-E paper comparison where useful.
6. Keep P-003/P-012/P-013 gates intact.
7. Keep governance/Draft PR synchronized.

Before any executable work, explicit owner consent is required.

## Resume order
1. `/DEVELOPMENT-CONSENT.md`
2. `AGENTS.md`
3. `CHECKPOINT.md`
4. `docs/MODULES/OPTION-COVERAGE-MATURITY.md`
5. `docs/MODULES/MULTISITE-SCOPE-OPTION-MATRIX.md`
6. `docs/IMPLEMENTATION-READINESS-MATRIX.md`
7. `docs/OPEN-DECISIONS-REGISTER.md`
8. `docs/DECISIONS/README.md`
9. relevant architecture/security/module/provider docs

Repository evidence overrides conversational memory.
