# WPEssential — Open Decisions Register

Status: **Phase 0 / planning-only / no development consent**  
Last reviewed: 2026-08-27

This file contains only decisions that remain unresolved or whose exact implementation profile still requires evidence. Resolved decisions remain preserved in ADRs/Git history.

## Decision states
- **Researching** — static/product evidence still incomplete.
- **Ready for non-executable decision** — paper semantics can likely be accepted without code.
- **Executable evidence required** — bounded spike/benchmark needed; ADR-0014 consent required first.
- **Accepted architecture / profile pending** — architecture accepted, exact library/format/runtime still open.
- **Deferred** — not blocking current implementation phase.

---

# A. Platform technical blockers

## D-001 — Compatibility floor
Related: ADR-0002  
Current candidate: WordPress 6.9 minimum, WordPress 7.1 current target, PHP 8.3 minimum candidate.

Still need:
- executable WP/PHP/DB activation matrix;
- Composer/runtime dependency proof;
- page-builder/WooCommerce integration matrix;
- multisite support declaration per core surface;
- final market/security support-window review.

**State:** Executable evidence required.  
**Consent:** Not granted.

## D-002 — Admin UI/design-system runtime
Related: ADR-0005

Paper direction:
- React + TypeScript;
- WPE wrapper API;
- stable WordPress Design System/components/DataViews/DataForm;
- Untitled UI visual language and only compatibility-reviewed MIT source;
- Lucide behind icon abstraction.

Still need:
- representative list/editor/dialog/form spike;
- React runtime/externalization proof;
- CSS scoping;
- RTL/localization;
- keyboard/screen-reader checks;
- bundle measurements.

**State:** Executable evidence required.  
**Consent:** Not granted.

## D-003 — Job Service concrete adapter
Related: ADR-0006

Paper contract accepted at service-interface level; Action Scheduler preferred implementation candidate.

Need:
- coexistence/version loading;
- claims/concurrency;
- idempotency/retry/cancel;
- pruning;
- multisite;
- WP-CLI/system-cron runner;
- load/recovery evidence.

**State:** Executable evidence required.

## D-004 — Definition Repository physical schema
Related: ADR-0008

Paper candidate:
- stable UUID identity;
- immutable revisions;
- current/published revision pointers;
- dependency edges;
- typed versioned payload;
- normalized proven index fields only.

Need benchmark/DDL evidence:
- table/index types;
- transaction/locking;
- 10k/100k definition behavior;
- multisite;
- import remapping;
- tombstones/deletion.

**State:** Executable evidence required.

## D-005 — Secrets Vault exact cryptographic/key profile
Related: ADR-0009

Accepted principles:
- secrets use references;
- no plaintext fallback;
- external key separation preferred;
- DB-only breach mitigation, not full-server-compromise claim.

Open:
- exact AEAD/envelope format;
- key derivation/wrapping;
- salt-change behavior;
- key rotation;
- multisite isolation;
- lost-key UX;
- staging/domain migration.

**State:** Executable crypto/storage evidence required.

## D-006 — Free ↔ Pro executable compatibility
Related: ADR-0010

Paper state machine exists.
Need:
- exact Platform API version format;
- boot/update order executable matrix;
- downgrade behavior;
- migration ownership;
- shared dependency collision proof;
- rollback fixtures.

**State:** Executable evidence required.

## D-007 — CI/test implementation
Related: ADR-0011

Paper lanes/fixtures exist.
Need executable tooling and final matrix after D-001/D-008.

**State:** Executable evidence required.

## D-008 — Build toolchain
Related: ADR-0012

Current candidate order:
1. `@wordpress/build`;
2. `@wordpress/scripts` comparison/fallback;
3. Vite only for demonstrated unmet need.

Need:
- TS/React build;
- WordPress package/React externalization;
- chunks/manifests;
- CSS Modules/scoping/RTL;
- translation extraction;
- test integration;
- production packaging/bundle budgets.

**State:** Executable evidence required.

---

# B. Remote service / commercial entitlement / Pro update

## S-001 — OAuth account-link exact profile
Paper direction: browser Authorization Code + PKCE; plugin is public client; WPE credentials collected on WPE service domain.

Open:
- exact callback registration/trust model;
- fixed-service-return vs site-specific redirect registration;
- token lifetimes/rotation;
- disconnect/revocation;
- Device Authorization fallback decision.

**State:** Static threat-model + executable integration evidence required.

## S-002 — Product entitlement signature profile
Related: ADR-0017 — architecture Accepted.

Accepted:
- signed;
- site-bound;
- issuer/environment bound;
- freshness-aware;
- anti-rollback;
- outage distinct from expiry;
- grace only if service-signed;
- WPE license separate from Membership access.

Open:
- exact serialization/canonicalization;
- algorithm/library;
- public key distribution;
- key rotation/emergency compromise;
- exact freshness/grace windows;
- tamper/rollback fixtures.

**State:** Accepted architecture / profile pending.

## S-003 — Pro updater exact trust/client profile
Related: ADR-0018 — architecture Accepted.

Accepted:
- Free plugin is not external Pro updater;
- signed update trust;
- package digest/signature;
- rollback/freeze/compromise defenses;
- compatibility gating;
- TUF-compatible design preferred for evaluation.

Open:
- exact client/library;
- metadata role thresholds/key custody;
- release/freshness/root rotation;
- rollback packages;
- CDN/cache semantics;
- update-order/tamper/freeze tests.

**State:** Accepted architecture / protocol pending.

---

# C. Membership technical blockers

Resolved semantics:
- M-002 Access precedence → ADR-0015 Accepted.
- M-004 Enrollment lifecycle → ADR-0016 Accepted.
- M-007 Plan changes/upgrades/downgrades → ADR-0019 Accepted.
- M-008 Team/seat product semantics → ADR-0020 Accepted; concurrency implementation still open.
- M-009 Role-sync semantics → ADR-0020 Accepted; runtime reconciliation still open.

## M-001 — Entitlement/runtime physical schema
Paper model exists in `ARCHITECTURE/MEMBERSHIP-RUNTIME-DATA-CANDIDATE.md`.

Need:
- exact tables/types/indexes;
- current-vs-history split;
- source reference uniqueness;
- entitlement materialization strategy;
- 100k-user/1M-entitlement benchmark;
- multisite scope.

**State:** Executable benchmark required.

## M-003 — Access cache/invalidation
Accepted security requirement: stale allow after revocation/hard deny is a bug.

Need:
- request-local vs persistent cache;
- access generation model;
- object-cache compatibility;
- invalidation transaction ordering;
- stampede control;
- revoke-to-deny latency tests.

**State:** Executable concurrency/load evidence required.

## M-005 — Protected file delivery
Paper architecture: private origin + authorized delivery; ordinary public uploads URL is not protection.

Need certify:
- Apache;
- Nginx/X-Accel style delivery;
- PHP streaming fallback;
- S3/private object storage signed URLs;
- CDN/media-offload adapters;
- Range/large-file/cache headers;
- migration from public media.

**State:** Executable environment certification required.

## M-006 — Billing adapters/reconciliation
Priority candidate:
1. Manual/Free;
2. WooCommerce one-time;
3. WooCommerce Subscriptions;
4. SureCart.

Need:
- exact product/plan mapping;
- webhook/source event verification;
- idempotency/out-of-order handling;
- refunds/disputes/cancellations;
- reconciliation jobs;
- provider API version compatibility;
- support/error UX.

**State:** Executable provider certification required.

## M-010 — Operational privacy/retention defaults
Architecture/classification exists.
Need final operational defaults for:
- Enrollment history;
- provider event receipts;
- invitation/team records;
- protected-download audit;
- erasure/anonymization;
- provider references;
- backup-restoration implications.

**State:** Ready for non-executable decision + later tests.

---

# D. Backup / Reset / Protection / Media technical blockers

## B-001 — Backup archive/container format
Open:
- ZIP/TAR/chunk format;
- streaming manifest layout;
- checksums;
- split-part semantics;
- resumability;
- corruption/missing-part handling.

**State:** Executable evidence required.

## B-002 — Backup encryption exact profile
Related: ADR-0021 — architecture Accepted.

Accepted:
- per-backup DEK;
- independent disaster-recovery wrapping;
- WordPress salts not sole recovery root;
- loss of all recovery material may make restore impossible and must be explicit.

Open:
- exact AEAD/container;
- KDF/passphrase thresholds;
- recovery-key format/export/rotation;
- optional KMS adapter;
- streaming encryption/decryption;
- cross-server disaster restore certification.

**State:** Accepted architecture / crypto profile pending.

## B-003 — Backup provider certification
34 named destinations are catalog targets; shared protocol adapters preferred.
Need actual provider acceptance for each marketed support claim.

**State:** Executable provider certification required.

## B-004 — Reset atomicity/recovery limits
Product options/safety exhaustive.
Need:
- actual owner adapters;
- restore-point integration;
- mid-run failure recovery;
- current-admin protections;
- multisite certification.

**State:** Executable evidence required.

## P-001 — Protector interception/rate/recovery
Product options exhaustive.
Need:
- hook/request interception ordering;
- rate-limit storage/atomicity;
- trusted proxy resolution;
- login alias compatibility;
- recovery constant/token design;
- CSP/header conflict tests.

**State:** Executable security evidence required.

## W-001 — Watermark media pipeline
Product options exhaustive.
Need:
- derivative naming/storage;
- active image-editor capabilities;
- output format matrix;
- animation/SVG/EXIF behavior;
- offload adapters;
- large image memory/load tests.

**State:** Executable certification required.

## X-001 — XML-RPC enforcement profile
Product options exhaustive.
Accepted planning distinction: authenticated XML-RPC method enablement is not identical to disabling pingbacks/all custom methods.

Need:
- exact hook ordering;
- full method inventory fixture;
- complete-deny evidence;
- parser/element-limit hook compatibility;
- mobile/Jetpack/legacy integration fixtures;
- network/multisite behavior.

**State:** Executable compatibility/security evidence required.

---

# E. Other module technical queues

## Content/Data
- Field storage schema and migration language;
- Relation storage/index/cardinality transactions;
- Status domain-state physical model;
- Query AST/compiler/cost budget;
- Custom-table DDL planner;
- list-table adapter API;
- Listing renderer/cache schema.

## Admin/Identity
- admin menu conflict/recovery implementation;
- frontend Dashboard route/authorization physical model;
- protected user-meta denylist runtime contract;
- Component Blueprint renderer;
- role anti-lockout/recovery implementation.

## Automation/Communication
- Form/entry physical schema;
- Workflow graph/run schema;
- notification persistence/deduping;
- email renderer/provider certification;
- Chat runtime/index/transport model.

## Integration/Data movement
- REST endpoint compiler/runtime schema;
- connection OAuth provider adapters;
- SSRF/DNS/redirect executable defenses;
- source migration adapter fixtures;
- package compatibility/import rollback evidence.

---

# Resolved semantic/architecture decisions

Preserved in ADRs rather than remaining “open”:
- Free/Pro distribution — ADR-0001;
- Abilities contract — ADR-0003;
- arbitrary PHP/raw destructive SQL prohibition — ADR-0004;
- license expiry runtime continuity — ADR-0007;
- Membership domain separation — ADR-0013;
- development consent gate — ADR-0014;
- Membership access precedence — ADR-0015;
- Enrollment lifecycle — ADR-0016;
- product-entitlement architecture — ADR-0017;
- Pro update supply-chain architecture — ADR-0018;
- Plan revisions/upgrades/downgrades — ADR-0019;
- teams/seats/role-sync product semantics — ADR-0020;
- backup encryption/recovery architecture — ADR-0021.

---

# Decision-processing rule

For every unresolved item:
1. inspect current source-of-truth docs/ADRs;
2. research current primary sources where external behavior matters;
3. document alternatives/tradeoffs;
4. identify security/data/performance/compatibility impact;
5. decide whether static evidence is enough;
6. if executable evidence is required, prepare protocol only and **do not execute without explicit owner consent**;
7. record accepted decision in ADR/spec;
8. update readiness, checkpoint and PR.

# Current next planning-only priorities
1. move remaining non-executable semantic decisions to ADR where evidence is sufficient;
2. specify Definition/Query/Relation/Workflow physical-schema alternatives before benchmarks;
3. finalize OAuth/signing/updater protocol candidates on paper;
4. finalize Membership privacy/retention defaults;
5. refine backup archive/provider certification protocols;
6. finalize consent-gated spike protocols without executing them;
7. synchronize checkpoint/PR after every meaningful planning unit.
