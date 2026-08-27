# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-08-28**  
Branch: `planning/master-architecture`  
Project state: **PARTIALLY COMPLETE — Phase 0 planning only**  
Production development authorization: **NOT GRANTED**

## Hard consent gate

Explicit owner consent is required before runtime/source/build/migration/test implementation, dependency/package setup, executable spikes/benchmarks, WordPress hook execution, queues, OAuth/service/provider/API calls, TUF/signing-key generation, SMTP/email sends, media/file processing, Backup/Restore, Reset/Protector execution, package staging or release packaging.

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

Accepted decisions now extend through **ADR-0107**.

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
- **ADR-0107 — Watermarker/Media WM-01…WM-48 evidence protocol.**

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

OA-01…OA-32 cover replay, wrong verifier, PKCE downgrade, open redirect, issuer mix-up, simultaneous flows, unknown outcome, refresh replay, disconnect, Vault/DB separation, proxy spoof, clone/staging and Device fallback.

**OA executed: 0/32.**

### TUF updater — ADR-0102
TU-01…TU-44 cover Root/Targets/Snapshot/Timestamp trust, threshold signatures, Root rotation, expiry, rollback/freeze/mix-and-match, consistent snapshots, package hash/length, compatibility, CDN/API compromise, archive staging/recovery and key-compromise drills.

TK1 is only first paper custody baseline: 2-of-3 offline Root, 2-of-3 controlled Targets, narrowly scoped online Snapshot/Timestamp. Exact production custody remains security/operations evidence.

**TU executed: 0/44.**

## Dashboard/Admin navigation evidence

### Dashboard Widgets — ADR-0103
DW-01…DW-36 cover Site vs Network Dashboard registration, core/third-party coexistence, per-user layout/dismiss state, XSS/SSRF, remote structured data, shortcode/block behavior, user/site cache isolation, async/Job refresh, iframe sandbox/CSP, asset scoping, Multisite and failure isolation.

**DW executed: 0/36.**

### Admin Menu — ADR-0104
AM-01…AM-40 cover Site/Network/User Admin context, current WordPress ordering composition, late/unmentioned menu items, rename/reorder/hide/move/add/link, conflicts, direct URL authorization independence, recovery-safe mode, role/user precedence, Multisite same-slug isolation and every-admin-request overhead.

**AM executed: 0/40.**

## Security/destructive/media evidence

### Protector — ADR-0105
PR-01…PR-44 cover trusted proxies, spoofed forwarded headers, atomic rate limits, login/password/XML-RPC/REST throttling, path normalization, redirects, login aliases, security headers, recovery mode, Multisite/network floors and privacy. Protector remains application-layer helper, not a full WAF/DDoS claim.

**PR executed: 0/44.**

### Reset — ADR-0106
RM-01…RM-48 cover impact fingerprints, recovery principal, mandatory verified restore point, destructive-operation locking, durable stage journal, duplicate Jobs, crash/DB/filesystem/plugin/theme failures, truthful recovery, post-health, Multisite and restored copied active Runs. WordPress Recovery Mode is not treated as data rollback.

**RM executed: 0/48.**

### Watermarker — ADR-0107
WM-01…WM-48 cover original checksum immutability, runtime `WP_Image_Editor` capability, formats/alpha/EXIF/orientation, font/SVG/huge-image safety, deterministic derivative identity, Jobs/concurrency, offload/CDN, protected media and Multisite.

**WM executed: 0/48.**

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

## Verification state

Verified planning/documentation only:
- branch `planning/master-architecture`;
- latest directly verified branch head after ADR-0104 was `590c1bbf27896b7049a6fd3753641ae37fcb533c`; subsequent ADR-0105/0106/0107 commits advanced it further;
- **31/31 Exhaustive / 0/31 Authorized**;
- accepted architecture/evidence contracts through **ADR-0107**;
- no implementation/build/test/provider/update success claimed.

Not performed: PHP/React source, package installation, DB tables/migrations/indexes, WordPress list/dashboard/menu/security/reset/media hooks, Query/REST runtime, option/user/role writes, Action Scheduler/queue, OAuth/TUF operations, provider/API/webhook/SMTP calls, Email sends, image decode/render/save, archive/hash/compression scan, Backup transfer/Restore, Reset, crypto/KDF, PHPUnit/Playwright, benchmarks or deployment.

## Next allowed planning-only priorities

1. Synchronize ADR index/Open Decisions/Readiness/Draft PR through ADR-0107.
2. Dashboard Builder route/navigation/component permission evidence protocol.
3. Builder Widgets adapter certification evidence protocol across Gutenberg/Elementor/Bricks/WPBakery/Visual Composer.
4. Status Manager WP-post-status vs domain-state execution protocol.
5. XML-RPC exact method/parser/compatibility evidence protocol.
6. Keep P-001…P-013 + OA/TU/DW/AM/PR/RM/WM gates intact.

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