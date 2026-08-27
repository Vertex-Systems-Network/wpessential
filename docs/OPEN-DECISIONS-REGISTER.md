# WPEssential — Open Decisions Register

Status: **Phase 0 / planning-only / no development consent**  
Last reviewed: 2026-08-27

This file lists only unresolved decisions or accepted architectures whose exact implementation profile still requires evidence. Resolved semantics live in ADRs.

All executable protocols are consent-gated by ADR-0014. None is authorized.

## States
- **Executable evidence required** — implementation/benchmark needed.
- **Accepted architecture / profile pending** — principle accepted, exact format/library/runtime open.
- **Deferred** — not blocking current implementation phase.

---

# A. Platform blockers

## D-001 Compatibility floor — ADR-0002
WordPress 6.9 and PHP 8.3 remain current candidates.

Need activation/dependency/integration/multisite/support-window matrix.  
**State:** Executable evidence required — P-001.

## D-002 UI/design-system runtime — ADR-0005
Need React/WP package externalization, CSS scoping, keyboard/screen reader, RTL/i18n and bundle evidence.  
**State:** Executable evidence required — P-002.

## D-003 Job Service adapter — ADR-0006
Action Scheduler remains preferred candidate. Need coexistence, claims, retries, idempotency, cancellation, pruning, multisite, runner and load/recovery proof.  
**State:** Executable evidence required — P-003.

## D-004 Definition Repository physical schema — ADR-0008
Need exact DDL/index/locking/import/multisite/tombstone benchmark.  
**State:** Executable evidence required — P-004.

## D-005 Secrets Vault crypto/key profile — ADR-0009
Need exact AEAD/envelope, key separation/rotation/loss, multisite, staging/domain migration and restore behavior.  
**State:** Executable evidence required — P-005.

## D-006 Free↔Pro compatibility — ADR-0010
Need Platform API format, boot/update/downgrade order, migration ownership, dependency collision and rollback fixtures.  
**State:** Executable evidence required — P-006.

## D-007 CI/test implementation — ADR-0011
Need executable CI/tooling after compatibility/build choices.  
**State:** Executable evidence required — P-007.

## D-008 Build toolchain — ADR-0012
Need TS/React build, externalization, chunks/manifests, CSS/RTL, translations, tests and package evidence.  
**State:** Executable evidence required — P-008.

---

# B. Remote service / licensing / distribution

## S-001 OAuth account-link integration — ADR-0034
Accepted profile:
- public client;
- Authorization Code + PKCE S256;
- fixed WPE-owned OAuth callback;
- one-time site-bound completion artifact;
- no reusable token/password in browser return URL;
- Device Authorization fallback.

Still need:
- exact service endpoint/schema;
- token/access/refresh lifetimes;
- rotation/revocation;
- return artifact lifetime/encoding/storage;
- site migration/activation transfer;
- reverse proxy/callback compatibility;
- replay/open-redirect/wrong-issuer/concurrent-flow tests.

**State:** Accepted security profile / executable integration required.

## S-002 Product entitlement signature — ADR-0017
Need exact serialization/canonicalization, algorithm/library, key distribution/rotation, freshness/grace windows and tamper/rollback fixtures.  
**State:** Accepted architecture / profile pending.

## S-003 Pro updater trust/client — ADR-0018
Need exact TUF-compatible client/library, role thresholds/key custody, root/release/freshness rotation, rollback packages and update-order/tamper/freeze tests.  
**State:** Accepted architecture / protocol pending.

---

# C. Membership blockers

Resolved semantics/defaults: ADR-0013, 0015, 0016, 0019, 0020, 0024.

## M-001 Enrollment/Entitlement physical schema
Need exact tables/indexes, source uniqueness, current/history strategy, materialization, scale and multisite evidence.  
**State:** Executable benchmark — P-012.

## M-003 Access cache/revocation
Need generation/cache model, object-cache behavior, transaction ordering, stampede control and revoke-to-deny latency proof.  
**State:** Executable concurrency/load evidence — P-012.

## M-005 Protected file delivery
Need Apache/Nginx/PHP streaming/private-object/CDN/Range/large-file/public-media migration certification.  
**State:** Executable environment certification.

## M-006 Billing adapters/reconciliation
Manual/Free → WooCommerce one-time → Woo Subscriptions → SureCart remains initial priority. Need mapping, event verification, idempotency/order, refunds/disputes/cancellation and reconciliation fixtures.  
**State:** Executable provider certification.

## M-010 Privacy runtime verification
Defaults accepted in ADR-0024; exporter/eraser batching, cleanup races, team ownership, provider references and restore implications remain to test.  
**State:** Accepted defaults / executable verification.

---

# D. Data / Query / Workflow

## Q-001 Query AST compiler/cost budgets
Paper AST exists. Need WP/custom-table compiler security, identifier/parameter handling, cost/explain guards, caching and scale.  
**State:** Executable evidence — P-009.

## R-001 Relations physical schema
Need indexes/cardinality/concurrency/orphan/delete benchmarks and proof whether per-relation tables are ever justified.  
**State:** Executable evidence — P-010.

## WF-001 Workflow runtime/Job integration
Need waits/parallel/retry/idempotency/cancel/unknown-outcome/worker-crash evidence.  
**State:** Executable evidence — P-011.

## F-001 Field storage adapters
ADR-0022 accepted. Need native meta vs custom table scale, repeaters/queryability/revisions/migration/privacy-tool fixtures.  
**State:** Executable evidence.

## T-001 Custom Tables DDL compiler
ADR-0023 accepted. Need supported MySQL/MariaDB profile, `dbDelta()` boundaries, large-table locking/copy/backfill/recovery.  
**State:** Executable evidence.

## FE-001 Form Entry physical schema
ADR-0025 accepted architecture. Need exact columns/indexes, typed-value encoding, projection strategy, file linkage, retention, duplicate/idempotency and scale evidence.  
**State:** Executable evidence.

---

# E. Admin / Identity / UX runtime

## DA-001 Dashboard router/component runtime — ADR-0031
Architecture accepted. Need WordPress rewrite/router implementation, permalink/multisite collisions, direct-route IDOR, safe return redirects, cache isolation, assets and builder adapters.  
**State:** Accepted architecture / executable evidence.

## UP-001 User/Profile identity security — ADR-0030
Architecture accepted. Need protected-meta registry, email-change confirmation/replay, password/session behavior, Application Password secrecy and public-profile privacy fixtures.  
**State:** Accepted security architecture / executable evidence.

## RC-001 Role anti-lockout/recovery — ADR-0032
Architecture accepted. Need effective-cap classifier fixtures, self-lockout/last-recovery-principal, multisite/Super Admin, role deletion, WP-CLI repair and recovery-mode validation.  
**State:** Accepted security architecture / executable evidence.

## AM-001 Admin menu conflict/recovery
Product spec exists; exact hook/order/conflict behavior and safe-mode compatibility still need executable proof.  
**State:** Executable evidence.

## BW-001 Component Blueprint/builder renderer
Need canonical component descriptor/version, server-render/security model and builder certification fixtures.  
**State:** Paper architecture still needs further non-executable narrowing + later evidence.

---

# F. Automation / Communication

## N-001 Notification runtime/adapters — ADR-0026
Need exact persistence indexes, recipient fan-out/dedupe, digest/preference races and provider channel adapters.  
**State:** Accepted architecture / executable evidence.

## E-001 Email renderer/delivery — ADR-0029
Need exact renderer/CSS-inliner choice, client compatibility, sanitizer/header/link/attachment security, WordPress/third-party email adapters and provider delivery events.  
**State:** Accepted architecture / executable evidence.

## C-001 Chat runtime — ADR-0027
Need exact indexes/search projection, polling/realtime transport, private attachment storage, moderation/retention and high-volume tests.  
**State:** Accepted architecture / executable evidence.

---

# G. Integration / Data movement

## REST-001 Compiled REST runtime — ADR-0028
Need compiler/runtime schema, endpoint registration, rate-limit/idempotency stores, cache isolation, CORS/auth and attack fixtures.  
**State:** Accepted architecture / executable evidence.

## CONN-001 Connections/Webhooks security
Need provider OAuth adapters plus SSRF/DNS rebinding/redirect/signature/replay executable defenses.  
**State:** Executable evidence.

## MIG-001 Source migration adapters
Need certified fixtures for CPT UI, ACF/SCF, Meta Box, JetEngine and Membership sources.  
**State:** Executable evidence.

---

# H. Backup / Reset / Protection / Media

## B-001 Backup physical bundle profile — ADR-0033
Accepted logical architecture: manifest-first independently verifiable multipart bundle.

Still open:
- exact file-record container;
- exact DB artifact encoding;
- chunk-size defaults;
- compression profile;
- hash/checksum profile finalization;
- provider finalization/commit mechanics.

**State:** Accepted logical architecture / executable evidence — P-013.

## B-002 Backup encryption profile — ADR-0021
Need exact AEAD/KDF/recovery-key/KMS/streaming profile and cross-server restore.  
**State:** Accepted architecture / crypto profile pending.

## B-003 Provider certification
34 destinations are targets, not claims. Each marketed provider requires upload/resume/download/integrity/error/restore certification.  
**State:** Executable evidence — P-013.

## B-004 Reset recovery limits
Need restore-point integration, mid-run failure, current-admin and multisite proof.  
**State:** Executable evidence.

## P-001 Protector runtime
Need hook ordering, atomic rate limits, proxy resolution, login alias/recovery and security-header conflicts.  
**State:** Executable security evidence.

## W-001 Watermark pipeline
Need derivative storage/naming, active image-editor matrix, animation/SVG/EXIF/offload and load tests.  
**State:** Executable certification.

## X-001 XML-RPC profile
Need actual method inventory/hook ordering/parser limits/complete-deny/Jetpack/mobile/multisite fixtures.  
**State:** Executable compatibility/security evidence.

---

# Resolved architecture retained in ADRs

Accepted decisions now extend through **ADR-0034**, including Email, Profile identity security, Frontend Dashboard route runtime, Role anti-lockout/recovery, Backup logical bundle and OAuth account-link profile.

# Decision-processing rule

For unresolved work:
1. inspect repo source of truth;
2. research primary/current sources where needed;
3. document alternatives/tradeoffs;
4. separate static decision from executable evidence;
5. never run code/benchmark/install/build without owner development consent;
6. persist accepted decisions in ADR;
7. synchronize readiness/checkpoint/PR.

# Next planning-only priorities

1. Component Blueprint/runtime schema shared by Dashboard/Builder Widgets/Listings;
2. Settings Page storage/scope/runtime model;
3. Admin Menu mutation/conflict/safe-mode model;
4. Status Manager generic state-machine runtime model;
5. Dynamic Listing renderer/cache model;
6. Connections/Webhooks normalized event inbox and SSRF policy paper architecture;
7. importer run/checkpoint/rollback runtime model;
8. refine entitlement/update crypto profiles without executable work.