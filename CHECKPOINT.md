# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-08-28**  
Branch: `planning/master-architecture`  
Project state: **PARTIALLY COMPLETE — Phase 0 planning only**  
Production development authorization: **NOT GRANTED**

## Hard consent gate

Explicit owner consent is required before runtime/source/build/migration/test implementation, dependencies, executable spikes/benchmarks, queue execution, OAuth/service/provider/API interactions, TUF/signing-key generation, SMTP/email sends, protected-file operations, Import, Backup/Restore, package staging or release packaging.

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

Accepted decisions now extend through **ADR-0102**.

Latest milestones:
- ADR-0098 — Admin Columns AC1 whole-request batching/N+1-safe profile.
- ADR-0099 — Dynamic Listings DL1 authorization-aware Query + batched hydration + SSR/cache/pagination profile.
- ADR-0100 — Backup artifact/container profile: SHA-256 H-B1, CMP0 fallback, CMP1 gzip comparison, ZIP convenience only.
- ADR-0101 — OAuth Account-Link OA-01…OA-32 future evidence protocol.
- **ADR-0102 — Pro updater TUF TU-01…TU-44 future evidence protocol.**

Earlier current milestones remain active: Definition P-004, Relations P-010, Query/Fields/Custom Tables/Settings, REST, Import, User/Profile, Role/Capability, Membership, Workflow/Job, Backup/Vault and Product License paper architectures.

## Admin Columns — ADR-0098

AC1 first operational baseline:
`Column Set → compiled whole-request execution plan → visible row IDs → batched source hydration → Policy → render`.

Non-negotiable:
- no unbounded per-row Query/Relation/remote/shortcode loop;
- real sort/filter happens in authoritative backend before pagination;
- inline/bulk writes use owning Data Source/Field API + per-row Policy;
- hidden presentation never authorizes hidden data;
- Multisite scope participates in hydration/cache/write identity.

**Runtime cases executed: 0.**

## Dynamic Listings — ADR-0099

DL1 first baseline:
`published Query/Data Source → authorization-aware result semantics → batched hydration → Component Blueprint SSR → optional client transitions`.

Non-negotiable:
- protected totals/facet counts/cursors cannot leak inaccessible data;
- persistent cache is proof-based/opt-in;
- member/authenticated cache includes scope/access generations;
- stale-while-revalidate cannot preserve revoked visibility where fail-closed access is required;
- nested listings have bounded depth/result/query budgets;
- client transitions reuse same server Query/Policy contract.

**Runtime cases executed: 0.**

## Backup artifact profile — ADR-0100

Canonical Backup remains a manifest-first independently verifiable multipart logical bundle, not a ZIP contract.

Accepted:
- SHA-256 over exact stored Part bytes as provider-neutral integrity baseline;
- AEAD authentication remains distinct from object hash;
- provider ETag supplemental unless certified semantics prove equivalence;
- CMP0 no-compression fallback;
- CMP1 gzip/DEFLATE streaming first general compression comparison where available;
- ZIP convenience import/export only;
- FR1 WPE bounded record stream vs FR2 TAR-compatible stream future comparison;
- DB1 typed rows vs DB2 controlled SQL vs DB3 hybrid future comparison;
- provider multipart boundaries remain below WPE logical Part identity.

**P-013 artifact/archive/hash/compression/restore cases executed: 0. Backup C-certified: 0/34.**

## OAuth Account Link — ADR-0101

First profile remains fixed WPE callback + one-time site-bound completion artifact + PKCE S256.

Future OA protocol contains **32 fixtures**, including state/artifact replay, wrong verifier, PKCE downgrade, open redirect, wrong issuer/mix-up, concurrent admins, unknown remote outcome, refresh-token rotation/replay, disconnect outage, DB theft/Vault separation, proxy callback spoof and staging clone.

Public-client refresh credential must use replay-detection semantics such as rotation unless a separately accepted sender-constrained profile supersedes it.

**OA executed: 0/32. No OAuth endpoint/token/redirect/revoke has run.**

## Pro updater TUF — ADR-0102

Automated Pro updates remain blocked until future verifier/repository operations pass **TU-01…TU-44**.

Evidence covers:
- Root/Targets/Snapshot/Timestamp role trust;
- threshold signatures/key custody;
- sequential Root rotation;
- metadata expiry;
- rollback/freeze/mix-and-match;
- consistent snapshots;
- target SHA-256/length;
- channel/product/Platform API/WP/PHP compatibility;
- CDN/API compromise separation;
- malformed ZIP/path traversal/bomb staging;
- replacement/migration failure recovery;
- key-compromise runbook.

TK1 is first paper custody baseline: 2-of-3 offline Root, 2-of-3 controlled Targets, narrowly scoped online Snapshot/Timestamp. Exact production custody remains security/operations evidence.

**TU executed: 0/44. No TUF metadata/key/repository/verifier/package update exists.**

## Current evidence counters

- P-003 Job: 0.
- P-004 Definition: 0.
- P-005 Vault: 0.
- P-009 Query: 0.
- P-010 Relations: 0.
- P-011 Workflow: 0.
- P-012 Membership: 0.
- P-013 Backup: 0.
- Membership Billing: **4 BE3 / 0 MB-certified**.
- Protected files: **0 PC1+**.
- Email: **6 EE3 / 0 ET-certified**.
- Event adapters: **0 I4/I5**.
- Backup: **34 targets / 0 C-certified**.
- Admin Columns: 0 runtime.
- Dynamic Listings: 0 runtime.
- REST/Import: 0 runtime.
- User/Profile: 0 runtime.
- Role/Capability: 0 runtime.
- OAuth OA: **0/32**.
- TUF TU: **0/44**.
- Remote privacy: **0/30**.
- Product License API/service: 0.
- Site Lifecycle: **0/40**.
- Multisite: **0 MS1+**.

## Verification state

Verified planning/documentation only:
- branch `planning/master-architecture`;
- **31/31 Exhaustive / 0/31 Authorized**;
- governance synchronized through **ADR-0102**;
- no implementation/build/test/provider/update success claimed.

Not performed: PHP/React source, package/dependency installation, DB tables/migrations/indexes, Query/List/REST runtime, option/user/role writes, Action Scheduler/queue, OAuth redirects/token calls, TUF key/metadata/repository/package operations, provider/API/webhook/SMTP calls, Email sends, file capture/compression/archive/hash scan, Backup transfer/Restore, crypto/KDF, PHPUnit/Playwright, benchmarks, deployment/release.

## Next allowed planning-only priorities

1. Dashboard Widgets content-source/cache/refresh operational evidence profile.
2. Admin Menu transform/conflict/performance evidence protocol.
3. Protector rate-limit/trusted-proxy execution protocol.
4. Reset durable journal/recovery execution protocol.
5. Watermarker media lifecycle/offload/concurrency evidence protocol.
6. Keep P-001…P-013 and OA/TU executable gates intact.
7. Keep governance/Draft PR synchronized.

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