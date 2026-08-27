# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-08-28**  
Branch: `planning/master-architecture`  
Project state: **PARTIALLY COMPLETE — Phase 0 planning only**  
Production development authorization: **NOT GRANTED**

## Hard consent gate

Explicit owner consent is required before runtime/source/build/migration/test implementation, dependency/package setup, executable spikes/benchmarks, WordPress hook execution, queues, OAuth/service/provider/API calls, TUF/signing-key generation, SMTP/email sends, media/file processing, Backup/Restore, Reset/Protector execution, XML-RPC execution, status/state mutation, builder/editor execution, package staging or release packaging.

`continue` and planning acceptance do **not** authorize development.

Source of truth: `/DEVELOPMENT-CONSENT.md`, `AGENTS.md`, ADR-0014.

## Product milestone

- **31/31 Exhaustive product-option maturity**
- **31/31 Multisite scope behavior mapped**
- **0/31 Authorized**
- **0 MS1+ runtime-certified surfaces**
- Implemented: none
- Runtime verified: none

## Accepted architecture

Accepted decisions now extend through **ADR-0111**.

Latest planning/evidence milestones:
- ADR-0098 — Admin Columns AC1 whole-request batching/N+1-safe profile.
- ADR-0099 — Dynamic Listings DL1 authorization-aware Query + batched hydration + SSR/cache/pagination.
- ADR-0100 — Backup artifact/container SHA-256/compression/profile architecture.
- ADR-0101 — OAuth Account-Link OA-01…OA-32 evidence protocol.
- ADR-0102 — Pro updater TUF TU-01…TU-44 evidence protocol.
- ADR-0103 — Dashboard Widgets DW-01…DW-36 evidence protocol.
- ADR-0104 — Admin Menu AM-01…AM-40 evidence protocol.
- ADR-0105 — Protector PR-01…PR-44 evidence protocol.
- ADR-0106 — Reset Manager RM-01…RM-48 evidence protocol.
- ADR-0107 — Watermarker/Media WM-01…WM-48 evidence protocol.
- **ADR-0108 — Frontend Dashboard FD-01…FD-48 evidence protocol.**
- **ADR-0109 — Builder Widgets BW-01…BW-50 adapter certification protocol, BC0…BC4.**
- **ADR-0110 — Status Manager SM-01…SM-48 evidence protocol.**
- **ADR-0111 — XML-RPC Manager XR-01…XR-48 evidence protocol.**

Earlier accepted physical/compiler/security baselines remain active: Definition D1, Relations R1, Query QP1–QP4, Field Storage FS1–FS6, Custom Tables CT1/CT2/CT3, Settings ST1/ST2/ST3, Forms/Chat, Membership, Notification/Email, Event Inbox, Audit, Workflow, JobService, REST, Import, Backup Remote Copy, Vault, User/Profile and Role/Capability.

## Latest operational contracts

### Admin Columns — ADR-0098
`Column Set → compiled whole-request plan → visible row IDs → batched source hydration → Policy → render`.

No unbounded per-row Query/Relation/remote/shortcode loop. Real sort/filter must occur in the authoritative backend before pagination. Writes use owning Data Source/Field API + Policy.

**Runtime: 0.**

### Dynamic Listings — ADR-0099
`published Query/Data Source → authorization-aware result → batched hydration → Component Blueprint SSR → optional client transitions`.

Protected totals/facets/cursors/cache must not leak inaccessible data. Persistent caching is proof-based; SWR cannot preserve revoked visibility where fail-closed access is required.

**Runtime: 0.**

### Backup artifact — ADR-0100
Canonical Backup remains manifest-first multipart, not ZIP-first.

Accepted paper rules:
- SHA-256 over exact stored Part bytes;
- AEAD remains separate cryptographic evidence;
- provider ETag supplemental unless certified;
- CMP0 no-compression fallback;
- CMP1 gzip/DEFLATE streaming first general compression comparison;
- ZIP convenience adapter only;
- FR1 record stream vs FR2 TAR-compatible stream;
- DB1 typed rows vs DB2 controlled SQL vs DB3 hybrid;
- provider multipart boundaries below WPE logical Part identity.

**P-013 archive/hash/compression/restore evidence: 0. Backup C-certified: 0/34.**

## Account/update evidence contracts

### OAuth — ADR-0101
First profile: fixed WPE callback + one-time site-bound completion artifact + PKCE S256 + short-lived access token + refresh-token replay detection/rotation profile + signed entitlement retrieval.

**OA-01…OA-32 executed: 0/32.**

### TUF updater — ADR-0102
TU-01…TU-44 cover Root/Targets/Snapshot/Timestamp trust, threshold signatures, Root rotation, expiry, rollback/freeze/mix-and-match, consistent snapshots, package hash/length, compatibility, CDN/API compromise, archive staging/recovery and key-compromise drills.

**TU executed: 0/44.**

## Admin/security/media evidence

### Dashboard Widgets — ADR-0103
DW-01…DW-36 cover Site/Network Dashboard registration, content trust, XSS/SSRF, remote structured data, user/site cache isolation, async/Job refresh, iframe sandbox/CSP, asset scoping, Multisite and failure isolation.

**DW executed: 0/36.**

### Admin Menu — ADR-0104
AM-01…AM-40 cover Site/Network/User Admin context, WordPress ordering composition, third-party conflicts, direct URL authorization independence, recovery-safe mode, role/user precedence, Multisite and every-admin-request overhead.

**AM executed: 0/40.**

### Protector — ADR-0105
PR-01…PR-44 cover trusted proxies, spoofed forwarded headers, atomic rate limits, login/password/XML-RPC/REST throttling, path normalization, redirects, security headers, recovery mode, Multisite/network floors and privacy.

**PR executed: 0/44.**

### Reset — ADR-0106
RM-01…RM-48 cover impact fingerprints, recovery principal, mandatory verified restore point, destructive-operation locking, durable stage journal, duplicate Jobs, crash/DB/filesystem/plugin/theme failures, truthful recovery, post-health and Multisite.

**RM executed: 0/48.**

### Watermarker — ADR-0107
WM-01…WM-48 cover original checksum immutability, runtime `WP_Image_Editor` capability, formats/alpha/EXIF/orientation, font/SVG/huge-image safety, deterministic derivative identity, Jobs/concurrency, offload/CDN, protected media and Multisite.

**WM executed: 0/48.**

## Newly accepted site/integration/state/security evidence

### Frontend Dashboard — ADR-0108
FD-01…FD-48 cover server-side route resolution, route/path normalization, direct-route IDOR, safe intended-return handling, authorization-aware navigation/counts/breadcrumbs, Component Blueprint action boundaries, principal/site/revision/access cache isolation, server/client navigation parity, permalink/collision behavior, SEO/noindex, asset scoping, accessibility/mobile/RTL and Multisite.

**FD executed: 0/48.**

### Builder Widgets adapters — ADR-0109
BW-01…BW-50 cover shared Component Blueprint invariants plus separate Gutenberg, Elementor, Bricks, WPBakery and Visual Composer Website Builder adapter fixtures. Certification levels are BC0 Detected, BC1 Registration, BC2 Render Certified, BC3 Advanced, BC4 Upgrade/Regression Certified.

**BW executed: 0/50. Builder runtime certifications: 0.**

### Status Manager — ADR-0110
SM-01…SM-48 preserve the split between WordPress Post Status Adapter and Generic Domain State Machine. Evidence covers core/third-party status preservation, editor/quick/bulk/REST/Form/Dashboard integration, migration-first key changes, concurrency, history/reconciliation, idempotency, storage adapters, Workflow/Job, import and Multisite.

**SM executed: 0/48.**

### XML-RPC Manager — ADR-0111
XR-01…XR-48 preserve layered semantics: host/WAF → Protector → effective method registry → authenticated-method state → native method authorization. Evidence covers Complete Deny, plugin-added methods, pingback, trusted-proxy rate limiting, parser-element limits, compatibility profiles, logging redaction and Multisite.

**XR executed: 0/48.**

## Core evidence counters

- P-001 compatibility/Multisite: 0 executable evidence.
- P-002 UI: 0.
- P-003 Job: 0.
- P-004 Definition: 0.
- P-005 Vault: 0.
- P-006 Free↔Pro/Product License: 0.
- P-007 CI: 0.
- P-008 build: 0.
- P-009 Query: 0.
- P-010 Relations: 0.
- P-011 Workflow: 0.
- P-012 Membership: 0.
- P-013 Backup: 0.
- Membership Billing: **4 BE3 / 0 MB-certified**.
- Protected files: **0 PC1+**.
- Email: **6 EE3 / 0 ET-certified**.
- Event adapters: **0 I4/I5**.
- Backup: **34 / 0 C-certified**.
- Remote privacy: **0/30**.
- Site Lifecycle: **0/40**.
- Multisite: **0 MS1+**.
- OA: **0/32**.
- TU: **0/44**.
- DW: **0/36**.
- AM: **0/40**.
- PR: **0/44**.
- RM: **0/48**.
- WM: **0/48**.
- FD: **0/48**.
- BW: **0/50**.
- SM: **0/48**.
- XR: **0/48**.

## Verification state

Verified planning/documentation only:
- branch `planning/master-architecture`;
- **31/31 Exhaustive / 0/31 Authorized**;
- accepted architecture/evidence contracts through **ADR-0111**;
- new quality protocols exist for FD/BW/SM/XR;
- Open Decisions and Implementation Readiness synchronized through ADR-0111;
- no implementation/build/test/provider/update success claimed.

Not performed: PHP/React source, package installation, DB tables/migrations/indexes, WordPress list/dashboard/menu/security/reset/media/status/XML-RPC hooks, builder registration/editor runs, Query/REST runtime, option/user/role writes, Action Scheduler/queue, OAuth/TUF operations, provider/API/webhook/SMTP calls, Email sends, image decode/render/save, archive/hash/compression scan, Backup transfer/Restore, Reset, crypto/KDF, PHPUnit/Playwright, benchmarks or deployment.

## Next allowed planning-only priorities

1. Synchronize ADR index and Draft PR through ADR-0111.
2. Settings Page ST1/ST2/ST3 executable evidence protocol.
3. User Profile identity/security executable evidence protocol.
4. Role & Capability mutation/anti-lockout executable evidence protocol.
5. REST API Builder operational/fuzz evidence protocol.
6. Import/Export Run/Map/Journal/recovery evidence protocol.
7. Keep P-001…P-013 + OA/TU/DW/AM/PR/RM/WM/FD/BW/SM/XR gates intact.

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
9. relevant architecture/security/quality/module/provider docs

Repository evidence overrides conversational memory.