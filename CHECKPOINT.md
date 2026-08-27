# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-08-27**  
Branch: `planning/master-architecture`  
Project state: **PARTIALLY COMPLETE — Phase 0 planning only**  
Production development authorization: **NOT GRANTED**

## Hard consent gate

Explicit owner consent is required before any runtime/source/build/migration/test implementation or executable research spike.

Source of truth:
- `/DEVELOPMENT-CONSENT.md`
- `docs/DECISIONS/ADR-0014-development-consent-gate.md`

`continue`, `proceed`, planning approval, ADR acceptance, technical readiness or Phase 0 completion do **not** authorize development.

No production PHP/React source, plugin bootstrap, DB migration/table, package scaffold, implementation test, benchmark or executable spike has been created/run in the target repository.

---

# Product specification milestone

- **31/31** planned module/platform surfaces have screen/option inventory.
- **31/31** have behavioral specification.
- **31/31** are at **Exhaustive product-option maturity**.
- **0/31** are Authorized for development.

Authoritative planning:
- `docs/MODULES/OPTION-COVERAGE-MATURITY.md`
- `docs/MODULES/README.md`
- `docs/IMPLEMENTATION-READINESS-MATRIX.md`
- `docs/OPEN-DECISIONS-REGISTER.md`

Exhaustive product specification does not imply physical schema/provider/performance/security verification.

---

# Accepted ADR state

Accepted architecture/product/governance decisions now include:

- ADR-0001 Free WordPress.org + separate Pro distribution.
- ADR-0003 WordPress Abilities typed action contract.
- ADR-0004 no standard arbitrary PHP eval/unrestricted destructive SQL.
- ADR-0007 safe Pro-expiry runtime/data continuity.
- ADR-0013 Membership/Billing/Role/Entitlement separation.
- ADR-0014 explicit owner development-consent gate.
- ADR-0015 Membership access precedence.
- ADR-0016 Enrollment lifecycle.
- ADR-0017 signed/site-bound/freshness-aware WPE product entitlement architecture.
- ADR-0018 signed anti-rollback/freeze-aware Pro update architecture.
- ADR-0019 Membership Plan revision/upgrade-downgrade semantics.
- ADR-0020 Membership teams/seats/role-sync semantics.
- ADR-0021 per-backup DEK + independent recovery wrapping architecture.
- ADR-0022 plural Custom Fields storage architecture.
- ADR-0023 typed Custom Tables desired-schema/Migration Plan architecture.
- ADR-0024 Membership privacy/retention product defaults.
- ADR-0025 Form Entry runtime architecture.
- ADR-0026 Notification occurrence/recipient/delivery domain model.
- ADR-0027 Chat runtime/authorization architecture.
- ADR-0028 REST compiled runtime descriptor architecture.

Full index: `docs/DECISIONS/README.md`.

Exact implementation profiles remain pending where each ADR says so.

---

# Platform Proposed blockers still requiring executable evidence

- ADR-0002 compatibility floor — P-001.
- ADR-0005 UI/design system runtime — P-002.
- ADR-0006 Job Service concrete adapter — P-003.
- ADR-0008 Definition Repository exact physical schema/indexes — P-004.
- ADR-0009 Secrets Vault exact crypto/key/recovery profile — P-005.
- ADR-0010 Free↔Pro executable boot/update compatibility — P-006.
- ADR-0011 CI/test implementation — P-007.
- ADR-0012 build toolchain/externalization — P-008.

All future protocols live in:
- `docs/QUALITY/CONSENT-GATED-TECHNICAL-SPIKE-PROTOCOLS.md`

**None has been executed.**

---

# Technical paper architecture now documented

## Definition Repository
- `docs/ARCHITECTURE/DEFINITION-REPOSITORY-SCHEMA-ALTERNATIVES.md`

Paper preference: identity/lifecycle + immutable revisions + revision-aware dependencies; current/published pointers separated; runtime data excluded.

## Query AST
- `docs/ARCHITECTURE/QUERY-AST-V1-CANDIDATE-SCHEMA.md`

Typed/provider-neutral; no raw SQL/PHP node; typed parameters, filters, joins/relations, aggregates, pagination, cost/security budgets.

## Relations Runtime
- `docs/ARCHITECTURE/RELATION-RUNTIME-SCHEMA-ALTERNATIVES.md`

Paper preference: universal typed edge-table family; reverse lookup first-class; concurrency-safe cardinality; per-relation tables only if evidence justifies.

## Workflow Runtime
- `docs/ARCHITECTURE/WORKFLOW-RUNTIME-DATA-CANDIDATE.md`

`Definition Repository → Workflow Runtime → Job Service`; runs pin published revision; waits/approvals/steps durable; at-least-once/idempotency/unknown-outcome semantics.

## Custom Fields storage
- `docs/ARCHITECTURE/FIELD-STORAGE-ARCHITECTURE-ALTERNATIVES.md`
- ADR-0022.

Native WP meta/options where natural; Custom Tables for high-scale/query/constraint application data; Relations for relationships; Vault for secrets; structured blobs only for bounded non-query-heavy structures.

## Custom Tables migration
- `docs/ARCHITECTURE/CUSTOM-TABLES-DDL-MIGRATION-LANGUAGE.md`
- ADR-0023.

Desired schema and physical observed schema are separate. Generated typed Migration Plan has fingerprints, risk classes, preconditions, dependency impact, backup/recovery and verification. `dbDelta()` may be a compiler tool for compatible operations, not the universal migration model.

## Form Entry runtime
- `docs/ARCHITECTURE/FORM-ENTRY-RUNTIME-STORAGE-CANDIDATE.md`
- ADR-0025.

Normalized Entry core + pinned Form revision + canonical typed value document + selected derived query projections + protected file refs. Workflow state separate. Passwords/tokens never stored in Entry values.

## Notifications
- `docs/ARCHITECTURE/NOTIFICATION-PERSISTENCE-DELIVERY-MODEL.md`
- ADR-0026.

Notification occurrence, per-recipient/read state and per-channel delivery attempts separated. Provider handoff is not automatically Delivered.

## Chat
- `docs/ARCHITECTURE/CHAT-RUNTIME-STORAGE-INDEX-ALTERNATIVES.md`
- ADR-0027.

Dedicated conversations/participants/messages/moderation/private assets; server-authoritative message ordering; search reauthorizes; transport is not source of truth; Membership/team revoke affects access.

## REST Builder
- `docs/ARCHITECTURE/REST-ENDPOINT-COMPILED-RUNTIME-MODEL.md`
- ADR-0028.

Published Endpoint Definitions compile to validated descriptors using WordPress REST + WPE Policy + Query/Data Source/Abilities. Explicit permission, bounded pagination, restrictive CORS, typed mapping, idempotency/rate/cache descriptors.

## Backup container
- `docs/ARCHITECTURE/BACKUP-ARCHIVE-CONTAINER-ALTERNATIVES.md`

Current paper preference: provider-neutral manifest + independently verifiable/resumable parts/chunks. Single ZIP may remain small/manual convenience; deduplicated content-addressed store deferred. Exact physical format pending benchmark.

## OAuth account linking
- `docs/SECURITY/OAUTH-ACCOUNT-LINK-THREAT-MODEL-ALTERNATIVES.md`

Current paper preference for first evaluation: fixed WPE OAuth callback + one-time transaction-bound return to exact site + PKCE S256. Direct dynamic site redirect registration remains alternative; Device Authorization remains fallback candidate. No final OAuth ADR yet.

---

# Membership status

Accepted product semantics/defaults:
- Role ≠ Membership ≠ Billing Subscription/Purchase ≠ Entitlement.
- outer WordPress/security deny cannot be bypassed.
- resource/action specificity; same-specificity deny wins.
- multiple valid memberships union grants unless applicable deny/exclusion.
- canonical Enrollment lifecycle pending/trialing/active/grace/paused/expired/revoked.
- cancellation-at-period-end is intent.
- provider statuses are source facts translated by policy.
- Plan draft edit never changes live access.
- follow-current / grandfather / scheduled Plan benefit change modes.
- team roles separate from WP roles; role sync off by default/provenance-safe.
- category-level privacy/retention defaults.

Privacy defaults:
- raw provider payload retention off/minimized by default;
- detailed successful protected-download logging off;
- IP/device logging off unless explicit purpose;
- terminal invitation cleanup candidate 30 days;
- terminal Enrollment/transition history retained by default for explainability, configurable;
- WordPress exporter/eraser integration required.

Technical blockers:
- physical Enrollment/Entitlement schema/indexes;
- cache/revoke-to-deny proof;
- protected-file environment certification;
- team/seat concurrency;
- WooCommerce/Woo Subscriptions/SureCart adapters/reconciliation;
- migration/provider fixtures;
- exporter/eraser/retention runtime verification.

---

# Backup / recovery state

Planning includes:
- 34 catalog destination targets through shared protocol/provider adapters;
- V0 Generated / V1 Local Verified / V2 Remote Verified / V3 Restore Tested levels;
- exhaustive Backup/Plan/Destination/Restore options;
- restore preflight/recovery;
- ADR-0021 encryption/recovery architecture;
- manifest/chunk container alternatives.

Open:
- exact archive/container/DB artifact format;
- exact AEAD/KDF/recovery key profile;
- provider certification;
- large-site streaming/memory performance;
- actual restore/disaster-recovery proof.

---

# Remote service / Pro distribution

Accepted boundaries:
- Free stays locally useful/account-free;
- WP site is not default WPE password proxy;
- Free does not auto-install/update external Pro ZIPs;
- WPE product entitlement separate from Membership access;
- service outage != expiry;
- Pro updater has signed supply-chain trust requirement.

Open:
- exact OAuth return/callback profile;
- exact entitlement signature/canonicalizer/library;
- freshness/grace windows;
- updater client/TUF-compatible profile/key custody.

---

# Verification state

## Verified
- planning branch isolated from `main`;
- 31/31 Exhaustive product-option maturity;
- 0/31 Authorized;
- ADR index synchronized through ADR-0028;
- Open Decisions synchronized through latest accepted architecture;
- technical paper docs listed above created;
- static research from primary sources recorded where used;
- no implementation/build/test success claimed.

## Not performed / intentionally blocked
- Composer/npm install;
- runtime PHP/React source;
- plugin bootstrap/activation;
- DB tables/migrations;
- PHPUnit/Playwright;
- all P-001…P-013 technical spikes;
- provider/API integrations;
- benchmark fixtures;
- Backup/Restore/Reset execution;
- release packaging/deployment.

Reason: explicit owner development/executable-spike consent has not been granted.

---

# Next allowed planning-only priorities

1. exact Form/Notification/Chat/REST physical-schema alternatives and indexes only to benchmark-ready level;
2. Backup manifest/chunk format and DB artifact alternatives further narrowed without code;
3. OAuth fixed-callback vs dynamic-registration static security decision/ADR if evidence becomes sufficient;
4. Email rendering/delivery provider architecture;
5. User/Profile protected-meta and identity-change security matrix;
6. Dashboard/Portal route/component runtime model;
7. Role anti-lockout/recovery runtime contract;
8. update Implementation Readiness/PR after each meaningful planning unit.

Before **any executable work**, obtain explicit owner consent.

## Resume order
1. `/DEVELOPMENT-CONSENT.md`
2. `AGENTS.md`
3. `CHECKPOINT.md`
4. `docs/MODULES/OPTION-COVERAGE-MATURITY.md`
5. `docs/IMPLEMENTATION-READINESS-MATRIX.md`
6. `docs/OPEN-DECISIONS-REGISTER.md`
7. `docs/DECISIONS/README.md`
8. relevant module/architecture/security docs

Repository evidence overrides conversational memory.