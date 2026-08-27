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

All future executable evidence protocols are defined in `docs/QUALITY/CONSENT-GATED-TECHNICAL-SPIKE-PROTOCOLS.md`; none are authorized.

---

# A. Platform technical blockers

## D-001 — Compatibility floor
Related: ADR-0002. Candidate: WordPress 6.9 minimum, WordPress 7.1 current target, PHP 8.3 minimum candidate.

Need executable WP/PHP/DB activation/dependency/integration matrix, multisite declaration and final support-window review.

**State:** Executable evidence required — P-001.  
**Consent:** Not granted.

## D-002 — Admin UI/design-system runtime
Related: ADR-0005.

Paper direction: React + TypeScript, WPE wrapper API, stable WordPress components/DataViews/DataForm, Untitled-inspired visual language, Lucide behind abstraction.

Need runtime externalization, CSS scoping, accessibility/keyboard, RTL/i18n and bundle evidence.

**State:** Executable evidence required — P-002.

## D-003 — Job Service concrete adapter
Related: ADR-0006.

Action Scheduler remains preferred candidate behind WPE Job Service. Need coexistence, claims/concurrency, idempotency/retry/cancel, pruning, multisite, runner and load/recovery proof.

**State:** Executable evidence required — P-003.

## D-004 — Definition Repository physical schema
Related: ADR-0008.

Paper preference: stable UUID identity + immutable revisions + current/published pointers + dependency edges + normalized proven indexes.

Need exact DDL/index/locking/import/multisite/tombstone benchmark.

**State:** Executable evidence required — P-004.

## D-005 — Secrets Vault exact cryptographic/key profile
Related: ADR-0009.

Accepted principles: references, no plaintext fallback, external key separation preferred, honest full-server-compromise limits.

Need exact AEAD/envelope, rotation, salt/key loss, multisite and staging/restore behavior.

**State:** Executable evidence required — P-005.

## D-006 — Free ↔ Pro executable compatibility
Related: ADR-0010.

Need exact Platform API version format, boot/update/downgrade matrix, migration ownership, dependency collision and rollback fixtures.

**State:** Executable evidence required — P-006.

## D-007 — CI/test implementation
Related: ADR-0011.

Paper lanes exist; executable CI/tooling still required after compatibility/build choices.

**State:** Executable evidence required — P-007.

## D-008 — Build toolchain
Related: ADR-0012.

Candidate order: `@wordpress/build` → `@wordpress/scripts` comparison/fallback → Vite only for proven unmet need.

Need TS/React externalization, chunks/manifests, CSS/RTL, translations, tests and production artifact evidence.

**State:** Executable evidence required — P-008.

---

# B. Remote service / commercial entitlement / Pro update

## S-001 — OAuth account-link exact profile
Paper direction: browser Authorization Code + PKCE; plugin is public client; credentials handled on WPE service domain.

Open: callback trust/registration, token lifetimes/rotation/revocation and Device Authorization fallback.

**State:** Static threat-model + executable integration evidence required.

## S-002 — Product entitlement signature profile
Related: ADR-0017 — architecture Accepted.

Open: serialization/canonicalization, algorithm/library, key distribution/rotation, exact freshness/grace windows and tamper/rollback fixtures.

**State:** Accepted architecture / profile pending.

## S-003 — Pro updater exact trust/client profile
Related: ADR-0018 — architecture Accepted.

Open: exact TUF-compatible/client/library choice, role thresholds/key custody, root/release/freshness rotation, rollback packages and update-order/tamper/freeze tests.

**State:** Accepted architecture / protocol pending.

---

# C. Membership technical blockers

Resolved product semantics/defaults:
- access precedence → ADR-0015;
- Enrollment lifecycle → ADR-0016;
- Plan revisions/upgrades/downgrades → ADR-0019;
- team/seat + role-sync semantics → ADR-0020;
- privacy/retention product defaults → ADR-0024.

## M-001 — Entitlement/runtime physical schema
Need exact tables/types/indexes, current/history split, source reference uniqueness, materialization and scale/multisite evidence.

**State:** Executable benchmark required — P-012.

## M-003 — Access cache/invalidation
Accepted security requirement: stale allow after revoke/hard deny is a defect.

Need generation/cache model, object-cache behavior, transaction ordering, stampede control and revoke-to-deny tests.

**State:** Executable concurrency/load evidence required — P-012.

## M-005 — Protected file delivery
Paper architecture: private origin + authorized delivery.

Need Apache/Nginx/PHP streaming/private object storage/CDN/Range/large-file/public-media migration certification.

**State:** Executable environment certification required.

## M-006 — Billing adapters/reconciliation
Priority candidate: Manual/Free → WooCommerce one-time → Woo Subscriptions → SureCart.

Need source mapping, signatures/events, idempotency/out-of-order handling, refund/dispute/cancel semantics, reconciliation and provider-version certification.

**State:** Executable provider certification required.

## M-010 — Privacy/retention runtime verification
Product defaults accepted in ADR-0024.

Still need exporter/eraser batching, cleanup races, user deletion/team ownership, provider-reference and backup-restore privacy tests.

**State:** Accepted product defaults / executable verification required.

---

# D. Backup / Reset / Protection / Media technical blockers

## B-001 — Backup archive/container format
Need exact ZIP/TAR/chunk/manifest/checksum/split/resume/corruption handling.

**State:** Executable evidence required.

## B-002 — Backup encryption exact profile
Related: ADR-0021.

Open: exact AEAD/container/KDF, recovery-key format/rotation, optional KMS, streaming encryption and cross-server restore certification.

**State:** Accepted architecture / crypto profile pending.

## B-003 — Backup provider certification
34 destinations are targets, not claims. Each marketed provider requires upload/resume/download/integrity/error/restore certification.

**State:** Executable evidence required — P-013.

## B-004 — Reset atomicity/recovery limits
Need restore-point integration, mid-run recovery, admin protection and multisite proof.

**State:** Executable evidence required.

## P-001 — Protector runtime enforcement
Need hook ordering, atomic rate limits, proxy resolution, login alias compatibility, recovery mechanism and header/CSP conflict tests.

**State:** Executable security evidence required.

## W-001 — Watermark media pipeline
Need derivative naming/storage, image-editor format capabilities, animation/SVG/EXIF/offload and memory/load certification.

**State:** Executable evidence required.

## X-001 — XML-RPC enforcement profile
Need actual method inventory, hook ordering, parser/request-limit compatibility, complete-deny and Jetpack/mobile/multisite fixtures.

**State:** Executable compatibility/security evidence required.

---

# E. Data / Query / Workflow technical queues

Resolved architecture:
- plural Field storage architecture → ADR-0022;
- typed Custom Tables migration language → ADR-0023.

Still open:

## Q-001 — Query AST compiler/cost budgets
Paper AST exists. Need WP/custom-table compiler security, parameter/identifier handling, explain/cost guard, caching and scale evidence.

**State:** Executable evidence required — P-009.

## R-001 — Relations physical runtime schema
Paper preference: universal typed edge-table family with first-class reverse lookup/cardinality enforcement.

Need index/cardinality/concurrency/orphan/delete benchmarks and proof whether per-relation tables are ever necessary.

**State:** Executable evidence required — P-010.

## WF-001 — Workflow runtime/Job integration
Paper architecture: Definition Repository → Workflow Runtime → Job Service; runs pin published revision.

Need retry/idempotency/waits/parallel/cancel/unknown-outcome/worker-crash evidence.

**State:** Executable evidence required — P-011.

## F-001 — Field storage adapter performance/migration
ADR-0022 accepted architecture. Need native meta vs custom table scale, queryability, repeaters, revisions, migration and privacy-tool evidence.

**State:** Executable evidence required.

## T-001 — Custom Tables DDL compiler/provider profile
ADR-0023 accepts typed Migration Plan semantics. Need exact MySQL/MariaDB compiler, `dbDelta()` boundaries, large-table locking/copy/backfill/recovery behavior.

**State:** Executable evidence required.

---

# F. Other module technical queues

## Admin/Identity
- admin menu conflict/recovery implementation;
- frontend Dashboard route/authorization physical model;
- protected user-meta denylist runtime contract;
- Component Blueprint renderer;
- role anti-lockout/recovery implementation.

## Automation/Communication
- Form/entry physical schema;
- notification persistence/deduping;
- email renderer/provider certification;
- Chat runtime/index/transport model.

## Integration/Data movement
- REST endpoint compiler/runtime schema;
- OAuth provider adapters;
- SSRF/DNS/redirect executable defenses;
- source migration adapter fixtures;
- package import rollback evidence.

---

# Resolved semantic/architecture decisions

Preserved in ADRs:
- ADR-0001 Free/Pro distribution;
- ADR-0003 Abilities;
- ADR-0004 unsafe arbitrary code/SQL boundary;
- ADR-0007 license expiry runtime continuity;
- ADR-0013 Membership domains;
- ADR-0014 development consent;
- ADR-0015 access precedence;
- ADR-0016 Enrollment lifecycle;
- ADR-0017 product entitlement architecture;
- ADR-0018 Pro update supply-chain architecture;
- ADR-0019 Plan revision/change semantics;
- ADR-0020 teams/seats/role sync;
- ADR-0021 backup encryption/recovery architecture;
- ADR-0022 plural Field storage architecture;
- ADR-0023 typed Custom Tables migration language;
- ADR-0024 Membership privacy/retention defaults.

---

# Decision-processing rule

For every unresolved item:
1. inspect current source-of-truth docs/ADRs;
2. research current primary sources where external behavior matters;
3. document alternatives/tradeoffs;
4. identify security/data/performance/compatibility impact;
5. decide whether static evidence is enough;
6. if executable evidence is required, use the pre-written spike protocol and **do not execute without explicit owner consent**;
7. record accepted decision in ADR/spec;
8. update readiness, checkpoint and Draft PR.

# Current next planning-only priorities
1. Form Entry physical storage/privacy schema;
2. Notification persistence/dedupe model;
3. Chat runtime/index/attachment storage alternatives;
4. REST endpoint compiled-runtime model;
5. Backup archive/container paper alternatives;
6. OAuth exact static threat-model alternatives;
7. keep checkpoint/PR synchronized.
