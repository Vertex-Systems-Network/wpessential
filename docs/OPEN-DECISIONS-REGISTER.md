# WPEssential — Open Decisions Register

Status: **Phase 0 / planning-only / no development consent**  
Last synchronized: 2026-08-27

This register contains only unresolved implementation profiles/evidence. Product/runtime principles already accepted are preserved in ADRs through **ADR-0041**.

All executable work is blocked by ADR-0014 until explicit owner consent.

## A. Platform executable blockers

| ID | Related ADR | Remaining evidence |
|---|---|---|
| D-001 | ADR-0002 | WP/PHP/DB activation/dependency/integration/multisite matrix — P-001 |
| D-002 | ADR-0005 | React/WP externalization, CSS scoping, accessibility, RTL/i18n, bundle — P-002 |
| D-003 | ADR-0006 | Action Scheduler coexistence/load/idempotency/cancel/runner/multisite — P-003 |
| D-004 | ADR-0008 | Definition Repository exact DDL/index/locking/import/multisite/tombstones — P-004 |
| D-005 | ADR-0009 | Vault AEAD/envelope/key separation/rotation/loss/restore — P-005 |
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
Security profile is accepted. Still need exact endpoint schemas, transaction/completion artifact encoding/lifetime, access/refresh lifetimes, rotation/revocation, site migration/transfer and end-to-end replay/open-redirect/issuer/concurrency tests.

### S-002 — Product entitlement signature — ADR-0017
Still open: exact envelope/serialization, signature algorithm/library, key distribution/rotation, freshness/grace windows and anti-rollback fixtures.

### S-003 — Pro updater — ADR-0018
Still open: exact TUF-compatible client/library, key-role thresholds/custody, root/targets/snapshot/timestamp metadata policy, rollback packages and tamper/freeze/update-order evidence.

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

- **B-001 / ADR-0033 / P-013** — physical file-record and DB artifact formats, chunk defaults, compression/hash/finalization/resume.
- **B-002 / ADR-0021** — exact AEAD/KDF/recovery-key/KMS/streaming profile.
- **B-003 / P-013** — provider certification for every marketed destination.
- **B-004** — Reset restore-point/partial-failure/admin/multisite proof.
- **P-001** — Protector hook ordering, atomic rate limit, proxy, login alias/recovery/header conflicts.
- **W-001** — Watermark derivative/output/editor/animation/SVG/EXIF/offload/load certification.
- **X-001** — XML-RPC method inventory/hook/parser/complete-deny/Jetpack/mobile/multisite certification.

## G. Identity/admin security

- **UP-001 / ADR-0030** — protected meta registry, email-change replay, password/session/Application Password/public-profile fixtures.
- **RC-001 / ADR-0032** — effective-cap classifier, self/last-recovery lockout, role delete, multisite/Super Admin, WP-CLI recovery.

## H. Accepted architecture no longer open semantically

ADR-0035 Component Blueprint, ADR-0036 Settings scope/storage, ADR-0037 Admin Menu transformation, ADR-0038 Status split, ADR-0039 Listings runtime, ADR-0040 Connections/Webhooks and ADR-0041 Import runtime are **accepted architecture**. Their exact executable profiles remain above as evidence items; future AI must not reopen the core decisions without a superseding ADR.

## Decision processing rule

1. Inspect repo source of truth and current official standards/docs.
2. Resolve static product/security semantics in ADR where evidence is sufficient.
3. If runtime evidence is required, prepare/extend a bounded protocol only.
4. **Do not install, compile, migrate, benchmark, test or integrate before explicit owner consent.**
5. After any accepted decision, synchronize ADR index, Readiness, Checkpoint and Draft PR.

## Next planning-only priorities

- narrow product-entitlement signature envelope/profile;
- narrow Backup AEAD/KDF/recovery-key profile;
- narrow Pro updater TUF metadata/key-role policy;
- paper-design Protector rate-limit/recovery store;
- paper-design Watermark derivative identity/storage;
- paper-design Reset execution journal/recovery;
- synchronize repo after each planning milestone.