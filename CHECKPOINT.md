# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-08-27**  
Branch: `planning/master-architecture`  
Project state: **PARTIALLY COMPLETE — Phase 0 planning only**  
Production development authorization: **NOT GRANTED**

## Consent gate
Explicit owner consent is required before any runtime/source/build/migration/test implementation or executable research spike.

Source of truth:
- `/DEVELOPMENT-CONSENT.md`
- `docs/DECISIONS/ADR-0014-development-consent-gate.md`

`continue`, `proceed`, planning approval, ADR acceptance, technical readiness or Phase 0 completion do **not** authorize development.

No production PHP/React runtime source, plugin bootstrap, production DB migration/table, package scaffold, implementation test or executable spike has been created in the target repository.

---

# Current Phase 0 status

## Product-option milestone
- **31/31** planned surfaces have option/screen inventory.
- **31/31** have behavioral specs.
- **31/31** are now at **Exhaustive product-option maturity**.
- **0/31** are Authorized for development.

Authoritative ledger:
- `docs/MODULES/OPTION-COVERAGE-MATURITY.md`

Detailed index:
- `docs/MODULES/README.md`

Readiness:
- `docs/IMPLEMENTATION-READINESS-MATRIX.md`

Open technical decisions:
- `docs/OPEN-DECISIONS-REGISTER.md`

Exhaustive means ordinary screens, fields, toggles, defaults, conditions, list/bulk actions, permissions, failure/degraded states, destructive safeguards, integrations, performance guardrails and future tests are planned. It does **not** mean physical/runtime architecture is proven.

---

# Exhaustive module coverage completed

Dedicated exhaustive specs now cover all modules, including the latest:
- `FORMS-WORKFLOW-EXHAUSTIVE-SPEC.md`
- `CRON-JOB-BUILDER-EXHAUSTIVE-SPEC.md`
- `NOTIFICATION-SYSTEM-EXHAUSTIVE-SPEC.md`
- `EMAILS-BUILDER-EXHAUSTIVE-SPEC.md`
- `MESSAGE-CHAT-EXHAUSTIVE-SPEC.md`
- `REST-API-BUILDER-EXHAUSTIVE-SPEC.md`
- `WEBHOOKS-CONNECTIONS-EXHAUSTIVE-SPEC.md`
- `IMPORT-EXPORT-EXHAUSTIVE-SPEC.md`
- `BACKUP-MANAGER-EXHAUSTIVE-SPEC.md`
- `RESET-MANAGER-EXHAUSTIVE-SPEC.md`
- `PROTECTOR-EXHAUSTIVE-SPEC.md`
- `WATERMARKER-MEDIA-RULES-EXHAUSTIVE-SPEC.md`
- `XML-RPC-MANAGER-EXHAUSTIVE-SPEC.md`

Earlier exhaustive specs cover CPT, Taxonomy, Fields, Relations, Status, Query, Tables, Columns, Listings, Admin/Dashboard/Menu/Settings, Profile/Roles, Membership and Builder Widgets.

---

# Accepted ADRs / product semantics

Accepted:
- ADR-0001 — Free WordPress.org + separate Pro add-on/trial distribution.
- ADR-0003 — WordPress Abilities typed action contract.
- ADR-0004 — no standard arbitrary PHP eval/unrestricted destructive SQL.
- ADR-0007 — Pro expiry preserves data and safe deployed behavior.
- ADR-0013 — Membership/Billing/Role/Entitlement separation.
- ADR-0014 — development consent gate.
- ADR-0015 — Membership access precedence.
- ADR-0016 — Membership Enrollment lifecycle.
- ADR-0017 — product-entitlement verification architecture; exact crypto profile pending.
- ADR-0018 — Pro update supply-chain architecture; exact protocol/client pending.
- ADR-0019 — Membership Plan revision/change/upgrade-downgrade semantics.
- ADR-0020 — teams/seats/role-sync product semantics.
- ADR-0021 — backup encryption/recovery architecture; exact crypto/container pending.

ADR index:
- `docs/DECISIONS/README.md`

---

# Membership status

Accepted semantics:
- User = identity;
- Role/Capability = WordPress authorization primitive;
- Plan = access/product definition;
- Enrollment = lifecycle interval;
- Subscription/Purchase = external source/reference;
- Entitlement = normalized grant/benefit;
- Access Rule = resource/action policy.

Additional accepted rules:
- outer security denial cannot be overridden by Membership;
- most-specific resource/action rule wins;
- same-specificity deny wins;
- simultaneous Membership grants union unless applicable deny/exclusion exists;
- `cancel_at_period_end` is intent, not inactive state;
- canonical states pending/trialing/active/grace/paused/expired/revoked;
- provider payment statuses are source facts;
- Draft Plan edits never alter live access;
- published benefit changes choose follow-current / grandfather / scheduled behavior;
- provider owns billing math/proration where possible;
- team roles are Membership-domain roles;
- WordPress role sync is optional/off by default and provenance-safe.

Technical blockers:
- physical Enrollment/Entitlement schema/index benchmark;
- access cache/invalidation and revoke-to-deny proof;
- seat/group concurrency;
- protected-file environment certification;
- WooCommerce / Woo Subscriptions / SureCart adapter certification;
- migration/provider fixtures.

---

# Remote service / licensing / Pro updates

Current architecture:
- Free remains locally useful/account-free;
- preferred account-link direction: browser Authorization Code + PKCE;
- WPE account password is not collected by a local WP proxy by default;
- WordPress.org Free does not auto-install/update external Pro ZIPs;
- product entitlement is signed, site-bound, freshness-aware and anti-rollback;
- service outage is distinct from expiry;
- WPE product license is separate from site Membership access;
- commercial grace is service-authorized;
- Pro updates require signed anti-rollback/freeze/key-compromise-aware trust;
- TUF-compatible updater architecture is preferred for future evaluation.

Open exact profiles:
- OAuth callback registration;
- entitlement canonicalization/algorithm/library;
- key rotation/freshness windows;
- updater client/metadata/key custody.

---

# Operations/security planning

## Backup
- 34 catalog target destinations mapped to protocol/provider adapters;
- V0 Generated / V1 Local Verified / V2 Remote Verified / V3 Restore Tested confidence tiers;
- exhaustive Plan/Backup/Destination/Restore UI;
- Required vs Optional destination semantics;
- restore preflight/recovery;
- per-backup DEK + independent disaster-recovery wrapping accepted;
- WordPress salts are not the sole recovery root.

Open: exact archive/chunk/KDF/AEAD/container/provider implementation and restore tests.

## Reset
Exhaustive scope/preservation/restore-point/impact/confirmation/recovery semantics complete. No destructive implementation.

## Protector
Exhaustive Site Gate, path/resource, wp-admin/login, rate-limit, trusted proxy, security header, REST/XML-RPC delegation and recovery controls complete.

## Watermark
Original media remains unchanged; WPE derivatives only in standard flow. Format/image-editor/offload support must be capability-certified.

## XML-RPC
Core `xmlrpc_enabled` authenticated-method behavior is not treated as full endpoint/pingback/custom-method disable. WPE models method exposure explicitly.

Official WordPress references were checked for XML-RPC method/filter behavior and image-editor/MIME capabilities.

---

# Migration/import planning

Sources researched/planned:
- ACF/ACF Pro;
- SCF;
- Meta Box;
- JetEngine;
- CPT UI;
- WooCommerce Memberships/Subscriptions;
- PMPro;
- MemberPress.

Pipeline:
**Discover → Snapshot → Parse → Normalize → Map → Validate → Dry Run → Review → Execute → Verify → Reconcile → Optional Source-Deactivation Readiness**

Fidelity:
- exact;
- convertible;
- lossy;
- external-reference;
- unsupported;
- conflict.

Source adapters normalize into a neutral IR and never write target DB tables directly.

---

# New technical paper architecture candidates

The next maturity layer has started **without executable work**.

## Definition Repository physical alternatives
New:
- `docs/ARCHITECTURE/DEFINITION-REPOSITORY-SCHEMA-ALTERNATIVES.md`

Current paper recommendation:
- Alternative A: identity/lifecycle table + immutable revision table + revision-aware dependency table;
- separate current vs published revision pointers;
- typed JSON application payload with physical JSON-vs-LONGTEXT left to compatibility evidence;
- normalize/index only proven shared list/filter fields;
- no generic definition meta table by default;
- runtime data remains outside repository.

Exact DDL/indexes/multisite layout require consent-gated benchmark.

## Query AST v1
New:
- `docs/ARCHITECTURE/QUERY-AST-V1-CANDIDATE-SCHEMA.md`

Paper rules:
- typed/versioned AST;
- no raw SQL/PHP callback node;
- source/provider capability contract;
- typed parameters;
- predicates/meta/tax/date/relations/joins/aggregates/sort/pagination;
- public/admin execution budgets;
- permission-aware cache keys;
- unsupported nodes fail validation rather than being ignored.

Exact compilers/performance require future benchmark.

## Relations runtime storage
New:
- `docs/ARCHITECTURE/RELATION-RUNTIME-SCHEMA-ALTERNATIVES.md`

Current paper preference:
- universal typed edge-table family;
- per-relation tables only for evidence-backed exceptional scale;
- reverse lookup first-class;
- cardinality enforced under concurrency, not UI-only;
- pivot data typed/versioned;
- relation definition remains in Definition Repository;
- runtime edges separate.

Exact columns/indexes/locks require benchmark.

## Workflow runtime
New:
- `docs/ARCHITECTURE/WORKFLOW-RUNTIME-DATA-CANDIDATE.md`

Paper architecture:
**Definition Repository → Workflow Runtime → Job Service**

Rules:
- run pins published workflow revision;
- in-flight run is not mutated by new draft/publish;
- run/step/wait/approval state durable;
- at-least-once job/event signals expected;
- side effects require idempotency/unknown-outcome/reconciliation;
- no fake universal rollback/exactly-once guarantee.

Exact tables/indexes/Job adapter require benchmark.

---

# Remaining platform blockers requiring executable evidence

- ADR-0002 compatibility floor;
- ADR-0005 UI/design-system runtime;
- ADR-0006 Job Service adapter;
- ADR-0008 Definition Repository exact physical schema/indexes;
- ADR-0009 Secrets Vault exact crypto/key profile;
- ADR-0010 Free↔Pro boot/update compatibility;
- ADR-0011 CI implementation;
- ADR-0012 build toolchain/externalization.

All executable evidence remains **not authorized**.

---

# Verification

## Verified
- 31/31 Exhaustive ledger synchronized;
- 0/31 Authorized in readiness matrix;
- ADR index synchronized through ADR-0021;
- Open Decisions Register synchronized;
- latest exhaustive module docs created;
- Definition/Query/Relation/Workflow paper architecture docs created;
- Draft PR updated;
- public research recorded where used;
- no implementation/test success claimed.

## Not performed / intentionally blocked
- Composer/npm install;
- runtime PHP/TS source;
- plugin activation/bootstrap;
- DB tables/migrations;
- PHPUnit/Playwright;
- compatibility/UI/build spikes;
- Definition/Query/Relation/Workflow benchmarks;
- Job/Vault/Free↔Pro executable tests;
- Membership cache/provider/protected-file tests;
- Backup/Reset execution;
- Protector/Watermark/XML-RPC runtime fixtures;
- release packaging/deployment.

Reason: explicit owner development/executable-spike consent has not been granted.

---

# Next allowed planning-only work

Highest-value next work:
1. formalize Query AST/Relation/Workflow candidates against module exhaustive specs and mark paper decisions Ready for non-executable ADR where justified;
2. define Custom Tables DDL migration-language candidate;
3. define Field Schema/storage adapter physical alternatives;
4. define Membership operational privacy/retention defaults;
5. define exact consent-gated benchmark/spike protocols for ADR-0002/0005/0006/0008/0009/0010/0011/0012;
6. refine OAuth/signing/updater exact candidate profiles without implementing;
7. keep Open Decisions/readiness/checkpoint/PR synchronized.

Before **any executable work**, obtain explicit owner consent.

## Resume order
1. `/DEVELOPMENT-CONSENT.md`
2. `AGENTS.md`
3. `CHECKPOINT.md`
4. `docs/MODULES/OPTION-COVERAGE-MATURITY.md`
5. `docs/IMPLEMENTATION-READINESS-MATRIX.md`
6. `docs/OPEN-DECISIONS-REGISTER.md`
7. relevant ADR/module/architecture/security docs

Repository evidence overrides conversational memory.