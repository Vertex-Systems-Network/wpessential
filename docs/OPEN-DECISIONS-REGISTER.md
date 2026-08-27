# WPEssential — Open Decisions Register

Status: **Phase 0 / planning-only / no development consent**  
Last synchronized: 2026-08-27

This register contains unresolved implementation profiles/evidence only. Product/runtime principles already accepted are preserved in ADRs through **ADR-0047**.

All executable work is blocked by ADR-0014 until explicit owner consent.

## A. Platform executable blockers

| ID | Related ADR | Remaining evidence |
|---|---|---|
| D-001 | ADR-0002 | WP/PHP/DB activation/dependency/integration/multisite matrix — P-001 |
| D-002 | ADR-0005 | React/WP externalization, CSS scoping, accessibility, RTL/i18n, bundle — P-002 |
| D-003 | ADR-0006 | Action Scheduler coexistence/load/idempotency/cancel/runner/multisite — P-003 |
| D-004 | ADR-0008 | Definition Repository exact DDL/index/locking/import/multisite/tombstones — P-004 |
| D-005 | ADR-0009 | Vault exact AEAD/envelope/key separation/rotation/loss/restore — P-005 |
| D-006 | ADR-0010 | Free↔Pro boot/update/downgrade/migration/dependency collision — P-006 |
| D-007 | ADR-0011 | Executable CI/tooling matrix — P-007 |
| D-008 | ADR-0012 | Build/externalization/chunks/CSS/RTL/i18n/package comparison — P-008 |

## B. Data/runtime blockers

- **Q-001 / P-009** — Query AST compiler security, identifiers/parameters, cost budgets, cache and scale.
- **R-001 / P-010** — Relation edge physical indexes, cardinality concurrency, orphan/delete behavior.
- **WF-001 / P-011** — Workflow wait/parallel/retry/idempotency/cancel/worker-crash and Job integration.
- **F-001** — Field storage adapters: native meta vs Custom Tables scale, repeaters, revisions and migrations.
- **T-001** — Custom Tables DDL compiler, MySQL/MariaDB capability matrix, large-table locking/copy/backfill/recovery.
- **FE-001** — Form Entry exact schema/indexes/projections/files/idempotency/retention.
- **N-001** — Notification indexes/fan-out/dedupe/digests/preferences/provider adapters.
- **C-001** — Chat indexes/search projection/polling-realtime/private attachments/moderation scale.
- **REST-001** — compiled endpoint runtime/rate-limit/idempotency/cache/CORS/auth attack fixtures.
- **E-001 / ADR-0029** — email renderer/inliner/client compatibility/sanitizer/header/link/attachment/provider delivery.
- **DA-001 / ADR-0031** — WordPress Dashboard routing/permalinks/multisite/cache/assets/builder adapters.
- **BW-001 / ADR-0035** — Component Blueprint descriptor/renderer/nesting/binding/cache/assets/builder certification.
- **SET-001 / ADR-0036** — Settings physical grouped storage/autoload/multisite inheritance/concurrency/REST/Vault integration.
- **AM-001 / ADR-0037** — admin-menu hook/order/plugin conflicts/site-network safe mode and recovery invariants.
- **ST-001 / ADR-0038** — WordPress post-status UI/migration plus generic state storage/concurrent transitions/history.
- **LIST-001 / ADR-0039** — Query integration, protected pagination/count correctness, cache invalidation, enhanced navigation and large datasets.
- **IMP-001 / ADR-0041** — Import Run physical indexes, crash/resume, identity mapping/upserts, rollback conflicts and large source fixtures.

## C. Membership blockers

- **M-001 / P-012** — Enrollment/Entitlement physical schema/indexes/materialization/scale/multisite.
- **M-003 / P-012** — access generation/cache/invalidation and revoke-to-deny latency.
- **M-005** — private file delivery across Apache/Nginx/PHP/private object storage/CDN/Range.
- **M-006** — Manual/WooCommerce/Woo Subscriptions/SureCart mapping, webhooks, reconciliation and provider certification.
- **M-010** — privacy exporter/eraser cleanup/runtime/restore verification.

## D. Remote service / commercial distribution

### S-001 — OAuth integration — ADR-0034
Security profile accepted. Still need exact endpoint schemas, completion-artifact encoding/lifetime, access/refresh lifetimes, rotation/revocation, site migration/transfer and replay/open-redirect/issuer/concurrency tests.

### S-002 — Product entitlement crypto — ADR-0017 + ADR-0042
Algorithm/canonicalization profile is now accepted:
- Ed25519;
- RFC 8785 JCS;
- WPE domain separation;
- `kid` signer selection;
- root-authorized signer keysets;
- monotonic sequence/freshness semantics.

Still open:
- exact canonicalizer/library dependency choice;
- root threshold/custody and keyset manifest byte schema;
- exact signed envelope field encoding;
- freshness/grace/clock-skew operational windows;
- native Sodium vs approved signature-only compatibility fallback;
- malformed/parser/rotation/replay interoperability fixtures.

**State:** Accepted crypto profile / executable interoperability required.

### S-003 — Pro updater — ADR-0018 + ADR-0044
TUF 1.0-compatible protocol profile is accepted:
- Root/Targets/Snapshot/Timestamp;
- 2-of-3 Root candidate;
- 2-of-3 stable Targets candidate;
- consistent snapshots direction;
- signed target length/hash/compatibility metadata.

Still open:
- production-worthy PHP TUF client/verifier;
- exact repository metadata schemas/paths;
- hardware/key custody runbooks;
- exact expiry values/renewal operations;
- conformance vectors;
- rollback/freeze/key-compromise/update-order/staging fixtures.

Current PHP-TUF is not production-selected while its upstream warning remains in place.

**State:** Accepted protocol / production client evidence required.

## E. Connections/Webhooks — ADR-0040
Architecture accepted. Evidence still required for:
- SSRF IPv4/IPv6/private/link-local/DNS rebinding/redirect behavior;
- provider-fixed/custom URL policies;
- raw-body signature profiles and key rotation;
- replay/idempotency/out-of-order events;
- normalized Event Inbox physical schema/indexes;
- Job crash/replay/reconciliation;
- OAuth provider adapters;
- outbound webhook retry/unknown outcome;
- multisite isolation.

## F. Backup/operations

### B-001 — Backup physical bundle — ADR-0033 / P-013
Still need file-record/DB artifact formats, chunk defaults, compression/hash/finalization/resume evidence.

### B-002 — Backup crypto — ADR-0021 + ADR-0043
Accepted profile:
- random 256-bit Backup Set DEK;
- Sodium secretstream XChaCha20-Poly1305 for part encryption;
- XChaCha20-Poly1305 AEAD DEK wrapping;
- Argon2id passphrase KEK;
- independent recovery-key slots;
- native `ext-sodium` required; no plaintext/weak fallback.

Still open:
- exact frame/AAD byte profile;
- accepted Argon2id memory/time floor after host benchmark;
- recovery-kit human encoding/checksum;
- chunk/part sizing/resume boundaries;
- KMS adapter;
- fresh-server/cross-version encrypted restore fixtures.

**State:** Accepted crypto profile / executable performance/interoperability required.

### B-003 — Provider certification — P-013
34 target destinations are not support claims until certified.

### B-004 — Reset — ADR-0047
Architecture accepted: reviewed Plan + verified restore point + durable recovery journal + recovery-principal invariant + post-health verification.

Still need recovery-store schema/location, DB/DDL/filesystem crash behavior, plugins/themes/uploads/offload adapters, multisite and Backup restore integration.

### P-001 — Protector — ADR-0045
Architecture accepted: trusted-proxy-aware request identity + shared atomic Rate Limit service + non-authenticating recovery mode.

Still need exact hooks/order, proxy fixtures, gate sessions, atomic DB/cache limiter adapters, login/reset/logout, multisite and header/CSP conflict tests.

### W-001 — Watermark — ADR-0046
Architecture accepted: non-destructive derivative identity/storage with source fingerprint + Rule revision + output/engine profile.

Still need registry schema/indexes, GD/Imagick/format matrix, EXIF/orientation/animation/SVG policy verification, concurrency, offload/CDN/private assets and large-image load evidence.

### X-001 — XML-RPC
Need actual method inventory/hook/parser/complete-deny/Jetpack/mobile/multisite certification.

## G. Identity/admin security

- **UP-001 / ADR-0030** — protected meta registry, email-change replay, password/session/Application Password/public-profile fixtures.
- **RC-001 / ADR-0032** — effective-cap classifier, self/last-recovery lockout, role delete, multisite/Super Admin, WP-CLI recovery.

## H. Accepted architecture no longer open semantically

ADRs 0035–0047 preserve accepted Component, Settings, Menu, Status, Listings, Connections, Import, entitlement crypto, Backup crypto, Pro update protocol, Protector, Watermark and Reset architecture. Their remaining items above are executable evidence, not invitations to redesign the accepted core without a superseding ADR.

## Decision processing rule

1. Inspect repository source of truth and current official standards/docs.
2. Resolve static product/security semantics in ADR where evidence is sufficient.
3. If runtime evidence is required, prepare/extend a bounded protocol only.
4. **Do not install, compile, migrate, benchmark, test, generate crypto keys or integrate providers before explicit owner consent.**
5. After any accepted decision, synchronize ADR index, Readiness, Checkpoint and Draft PR.

## Next planning-only priorities

- narrow Secrets Vault exact envelope/key hierarchy profile without executing crypto;
- narrow Definition Repository physical schema candidates against WP/MySQL constraints without creating tables;
- define Support Ticket runtime/storage/attachment/privacy model;
- define Dashboard Widgets remote-content/iframe trust model;
- define XML-RPC complete-deny/compatibility paper matrix;
- define Backup provider certification contract per protocol family;
- keep repo synchronization current.