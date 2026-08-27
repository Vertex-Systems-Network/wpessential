# WPEssential — Open Decisions Register

Status: **Phase 0 / planning-only / no development consent**  
Last synchronized: 2026-08-27

This register contains unresolved implementation profiles/evidence only. Accepted architecture/product/security decisions are preserved in ADRs through **ADR-0052**.

All executable work remains blocked by ADR-0014 until explicit owner consent.

## A. Platform executable blockers

| ID | Related ADR | Remaining evidence |
|---|---|---|
| D-001 | ADR-0002 | WP/PHP/DB activation/dependency/integration/multisite matrix — P-001 |
| D-002 | ADR-0005 | React/WP externalization, CSS scoping, accessibility, RTL/i18n, bundle — P-002 |
| D-003 | ADR-0006 | Job adapter coexistence/load/idempotency/cancel/runner/multisite — P-003 |
| D-004 | ADR-0008 + ADR-0049 | Definition Repository exact DDL/indexes, locking, uniqueness, multisite scope, tombstones — P-004 |
| D-005 | ADR-0009 + ADR-0048 | Vault exact envelope bytes, VRK/DEK slot implementation, rotation/loss/restore/interoperability — P-005 |
| D-006 | ADR-0010 | Free↔Pro boot/update/downgrade/migration/dependency collision — P-006 |
| D-007 | ADR-0011 | Executable CI/tooling matrix — P-007 |
| D-008 | ADR-0012 | Build/externalization/chunks/CSS/RTL/i18n/package comparison — P-008 |

## B. Data/runtime blockers

- **Q-001 / P-009** — Query AST compiler security, identifiers/parameters, cost budgets, cache and scale.
- **R-001 / P-010** — Relation indexes/cardinality/concurrency/orphan/delete behavior.
- **WF-001 / P-011** — Workflow waits/parallel/retries/idempotency/cancel/worker-crash + Job integration.
- **F-001** — Field storage adapters, native-meta vs Custom Table scale, repeaters, revisions, migrations.
- **T-001** — Custom Tables compiler, MySQL/MariaDB matrix, large-table locking/copy/backfill/recovery.
- **FE-001** — Form Entry exact schema/indexes/projections/files/idempotency/retention.
- **N-001** — Notification indexes/fan-out/dedupe/digests/preferences/channel adapters.
- **C-001** — Chat indexes/search projection/polling-realtime/private attachments/moderation scale.
- **REST-001** — compiled endpoint runtime, rate-limit/idempotency/cache/CORS/auth attack fixtures.
- **E-001 / ADR-0029** — Email renderer/inliner/client compatibility/sanitizer/provider delivery.
- **DA-001 / ADR-0031** — Dashboard routing/permalinks/multisite/cache/assets/builder adapters.
- **BW-001 / ADR-0035** — Component Blueprint renderer/nesting/bindings/cache/assets/builder certification.
- **SET-001 / ADR-0036** — Settings physical storage/autoload/multisite inheritance/concurrency/Vault/REST.
- **AM-001 / ADR-0037** — Admin Menu hook order/plugin conflicts/site-network recovery.
- **ST-001 / ADR-0038** — Post Status UI/migration + generic state storage/concurrent transitions/history.
- **LIST-001 / ADR-0039** — protected pagination/count correctness, invalidation, enhanced navigation, large datasets.
- **IMP-001 / ADR-0041** — Import Run indexes, crash/resume, identity mapping/upserts, rollback conflicts, source fixtures.

## C. Membership blockers

- **M-001 / P-012** — Enrollment/Entitlement physical schema/indexes/materialization/scale/multisite.
- **M-003 / P-012** — access generation/cache/invalidation and revoke-to-deny latency.
- **M-005** — private file delivery across Apache/Nginx/PHP/private object storage/CDN/Range.
- **M-006** — Manual/WooCommerce/Woo Subscriptions/SureCart mapping, webhooks, reconciliation and certification.
- **M-010** — privacy exporter/eraser cleanup/runtime/restore verification.

## D. Remote service / commercial distribution

### S-001 — OAuth integration — ADR-0034
Accepted profile. Still open: endpoint schemas, completion artifact format/lifetime, access/refresh lifetimes, rotation/revocation, site transfer and replay/open-redirect/issuer/concurrency tests.

### S-002 — Product entitlement — ADR-0017 + ADR-0042
Accepted: Ed25519, RFC 8785 JCS, domain separation, signer `kid`, root-authorized keysets, freshness/site binding.

Still open: canonicalizer/library interoperability, root threshold/custody, exact envelope/keyset bytes, TTL/skew policy, malformed/rotation/replay fixtures.

### S-003 — Pro updater — ADR-0018 + ADR-0044
Accepted: TUF 1.0-compatible Root/Targets/Snapshot/Timestamp semantics and rollback/freeze/key-rotation defenses.

Still open: production-worthy PHP verifier/client, exact repository metadata policy, hardware/key custody, expiry/renewal runbooks, conformance vectors and Free↔Pro update-order fixtures.

### S-004 — Support service — ADR-0050
Accepted authority model. Still open: concrete ticket/message/attachment schemas, idempotency keys, pagination/cursors, retention/deletion API, attachment malware/content scanning profile, diagnostics limits and service outage behavior.

## E. Connections/Webhooks — ADR-0040

Evidence still required for SSRF IPv4/IPv6/private/link-local/DNS rebinding/redirect behavior, provider-fixed/custom URL policies, raw-body signature/key rotation, replay/idempotency/out-of-order events, Event Inbox indexes, Job crash/reconciliation, OAuth adapters, outbound webhook unknown-outcome handling and multisite isolation.

## F. Backup/operations

### B-001 — Backup physical bundle — ADR-0033 / P-013
Open: file-record/DB artifact formats, part/chunk sizing, compression/hash/finalization/resume semantics.

### B-002 — Backup crypto — ADR-0021 + ADR-0043
Accepted crypto profile. Open: exact frame/AAD bytes, Argon2id performance floor, recovery-kit encoding, chunk/resume boundaries, KMS and fresh-server restore fixtures.

### B-003 — Backup provider certification — P-013
34 target destinations remain **targets, not support claims**. Need protocol-family contract + per-provider capability profile + upload/resume/download/integrity/error/restore certification.

### B-004 — Reset — ADR-0047
Open: recovery-store schema/location, DB/DDL/filesystem crash behavior, plugin/theme/uploads/offload adapters, multisite and Backup restore integration.

### P-001 — Protector — ADR-0045
Open: hook order, proxy fixtures, gate sessions, atomic limiter adapters, login/reset/logout, multisite and security-header conflicts.

### W-001 — Watermark — ADR-0046
Open: derivative registry/indexes, GD/Imagick/format matrix, EXIF/orientation/animation/SVG policy, concurrency, offload/CDN/private assets and load evidence.

### X-001 — XML-RPC — ADR-0052
Accepted layered semantics. Open: actual method inventory/hook priority, complete-deny behavior, `xmlrpc_element_limit` compatibility, Jetpack/mobile/app profiles, Protector endpoint interaction and multisite certification.

## G. Identity/Admin security

- **UP-001 / ADR-0030** — protected-meta registry, email-change replay, password/session/Application Password/public-profile fixtures.
- **RC-001 / ADR-0032** — effective-cap classifier, self/last-recovery lockout, role delete, multisite/Super Admin, WP-CLI recovery.
- **DW-001 / ADR-0051** — Dashboard Widget structured remote-data schemas, iframe profile, wp-admin XSS/CSP/asset isolation fixtures.

## H. Accepted architecture no longer open semantically

ADRs **0035–0052** preserve the accepted core for Component Blueprint, Settings, Admin Menu, Status, Listings, Connections, Import, entitlement crypto, Backup crypto, Pro update protocol, Protector, Watermark, Reset, Vault hierarchy, Definition Repository shape, Support Tickets, Dashboard Widget content trust and XML-RPC layered enforcement.

Remaining items above require evidence; future work must not silently redesign accepted cores without a superseding ADR.

## Decision-processing rule

1. Inspect repository source of truth and current official standards/docs.
2. Resolve static product/security semantics in ADR where evidence is sufficient.
3. If runtime evidence is required, prepare/extend a bounded protocol only.
4. **Do not install, compile, migrate, benchmark, test, generate crypto keys or integrate providers before explicit owner consent.**
5. Synchronize ADR index, Readiness, Checkpoint and Draft PR after meaningful planning milestones.

## Next planning-only priorities

1. Backup provider certification contract by protocol family.
2. Provider capability profiles for S3-compatible, Google Drive, OneDrive/SharePoint, Dropbox, SFTP and WebDAV.
3. Remote Service API schema set for account/site/entitlement/plans/support/release metadata.
4. Provider-neutral Connection certification levels.
5. Backup remote-finalization + retention/deletion + restore-source semantics.
6. Extend consent-gated evidence protocols without executing them.
