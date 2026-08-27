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

Accepted decisions now extend through **ADR-0073**.

Latest milestones:
- ADR-0069 — unified WordPress Multisite scope/ownership/isolation.
- ADR-0070 — Product License installation/network/site-allocation + clone/staging/migration/transfer semantics.
- ADR-0071 — Multisite physical topology classes PT-A…PT-F.
- ADR-0072 — Product License remote resource/conflict-state model.
- **ADR-0073 — Definition Repository PT-C D1 benchmark baseline.**

## Current Multisite state

Authoritative docs:
- `docs/ARCHITECTURE/MULTISITE-SCOPE-OWNERSHIP-MODEL.md`;
- `docs/MODULES/MULTISITE-SCOPE-OPTION-MATRIX.md`;
- `docs/QUALITY/MULTISITE-SCOPE-ISOLATION-EVIDENCE-PROTOCOL.md`.

Core accepted rules remain: site scope default, network scope explicit, target-site capability + WPE Policy, cross-site Relations/Queries off by default, bounded JobService network fan-out, cache/Vault/Membership/Backup/Reset/Import scope isolation.

Future Multisite certification: MS0 → MS1 → MS2 → MS3 → MS4. **0 runtime fixtures executed.**

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
- Relations/Membership/Workflow/Notification/Event Inbox PT-D candidates where documented;
- Forms/Chat PT-D vs PT-E evidence-gated.

No DDL, migration, table, index or DB benchmark has been executed.

## Definition Repository — ADR-0073

Future P-004 baseline **D1** is accepted for comparison only:
- numeric physical row IDs;
- transparent textual/ASCII-compatible portable UUID;
- explicit network/site scope coordinates;
- bounded normalized Definition Type and Machine Key identifiers;
- immutable application-validated text revision payload;
- SHA-256 payload fingerprint representation still evidence-gated;
- minimal workload-driven indexes;
- no arbitrary payload/EAV indexes;
- application same-definition pointer integrity + diagnostics;
- WordPress-derived charset/collation direction.

Comparison profiles remain:
- D2 binary UUID;
- D3 native JSON;
- D4 constraint/FK-enhanced.

Exact SQL types, lengths, collation, index ordering/prefixes, locking, foreign keys, JSON/UUID/hash representation and performance remain P-001/P-004 evidence.

**P-004 executable DDL/benchmark count: 0.**

## Product License / remote resource state — ADR-0070 + ADR-0072

Product licensing remains separate from WordPress authority and Membership authorization.

Remote resource classes are now explicitly separated:
- Account;
- Product Contract;
- Installation Activation;
- Network Activation;
- Site Allocation;
- independently signed Product Entitlement.

Contract, activation, allocation, environment and conflict state are separate dimensions.

Accepted safety rules:
- hostname/site ID are metadata, not sole identity;
- allocation/site mutations require idempotent/concurrency-safe future semantics;
- unknown remote result enters reconciliation rather than guessing;
- clone/migration conflict never silently grants second production allocation;
- service outage ≠ expiry;
- local option cannot manufacture Pro rights;
- product expiry/revocation never disables Membership protection or deletes data.

**0 product-license service/OpenAPI/allocation/clone/transfer fixtures executed.**

## Current JobService / Action Scheduler state

Action Scheduler 4.1.0 remains reviewed candidate only. WPE JobService owns business idempotency/fairness/history semantics. **P-003 unexecuted.**

## Current Remote Service state

Purpose-scoped privacy/retention accepted; 30 future privacy fixtures documented, **0 executed**. ADR-0072 adds exact future resource/idempotency/conflict/concurrency evidence requirements.

## Current Backup state

- 34 target destinations / 34 stable profiles;
- **0 C-certified / 0 C3 Supported**;
- scoped site-row extraction from shared WPE tables required;
- P-013 unexecuted.

## Current Membership billing state

Manual, WooCommerce 11.0.1, Woo Subscriptions 9.1.0, SureCart WP 4.7.0 + hosted API remain paper profiles. **4 BE3 / 0 MB-certified**.

## Current Email state

`wp_mail`, SMTP, SES, SendGrid, Mailgun, Postmark remain paper profiles. **6 EE3 / 0 ET-certified**.

## Platform evidence blockers

P-001 compatibility/Multisite; P-002 UI; P-003 Job backend; P-004 Definition D1-vs-D2/D3/D4; P-005 Vault; P-006 Free↔Pro/Product Allocation runtime; P-007 CI; P-008 build; P-009 Query; P-010 Relations topology; P-011 Workflow; P-012 Membership; P-013 Backup.

No executable evidence has run.

## Verification state

Verified planning/documentation only:
- planning branch isolated from `main`;
- **31/31 Exhaustive, 0/31 Authorized**;
- **31/31 Multisite scopes mapped; 0 MS1+ certified**;
- ADR index/Open Decisions/Readiness/Checkpoint synchronized through **ADR-0073**;
- Product License remote state model + ADR-0072 committed;
- Definition PT-C alternatives + ADR-0073 committed;
- P-003/P-004/P-012/P-013 unexecuted;
- Membership 0 MB-certified;
- Email 0 ET-certified;
- Backup 0 C-certified;
- Remote Service fixtures 0 executed;
- no implementation/test/provider-certification success claimed.

Not performed: dependency installation, Multisite runtime setup, Action Scheduler bootstrap, production source, DB tables/migrations/indexes, queue runs, crypto execution, PHPUnit/Playwright, provider/API/webhook/SMTP calls, commerce transactions, Email sends, WPE account/license calls, Backup/Restore, DB/performance benchmarks, release/deployment.

## Next allowed planning-only priorities

1. Relations PT-D vs PT-E/per-site physical comparison.
2. Site lifecycle coordinator across PT-C/PT-D/PT-E.
3. Product License OpenAPI candidate shapes without service implementation.
4. P-004 Definition benchmark fixture design without execution.
5. Keep P-003/P-012/P-013 gates intact.
6. Keep governance/Draft PR synchronized.

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
