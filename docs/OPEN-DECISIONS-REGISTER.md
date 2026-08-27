# WPEssential — Open Decisions Register

Status: **Phase 0 / planning-only / no development consent**  
Last synchronized: 2026-08-28

This register contains unresolved implementation/evidence only. Accepted decisions are preserved in ADRs through **ADR-0102**.

All executable work remains blocked by ADR-0014 until explicit owner consent.

## A. Platform executable blockers

| ID | Related ADR | Remaining evidence |
|---|---|---|
| D-001 | ADR-0002/0069/0075 | WP/PHP/DB compatibility + Multisite/site lifecycle — P-001 |
| D-002 | ADR-0005 | UI runtime/externalization/accessibility/RTL/bundle — P-002 |
| D-003 | ADR-0059/0068/0083 | Job physical/backend/Action Scheduler/fairness/claims/Multisite — P-003 |
| D-004 | ADR-0073/0092 | Definition D1/D2/D3/D4 exact DDL/index/locking/migration — P-004 |
| D-005 | ADR-0048/0085 | Vault crypto/envelope/DDL/rotation/recovery/security review — P-005 |
| D-006 | ADR-0070/0072/0076/0091/0101 | Free↔Pro/Product License/OAuth runtime/service — P-006 + OA protocol |
| D-007 | ADR-0011 | executable CI matrix — P-007 |
| D-008 | ADR-0012 | build/externalization/toolchain comparison — P-008 |
| D-009 | ADR-0086 | Query compiler/cost/cache/security/storage-adapter evidence — P-009 |
| D-010 | ADR-0074/0093 | Relations DDL/cardinality/concurrency/scale — P-010 |
| D-011 | ADR-0082 | Workflow runtime/concurrency/recovery — P-011 |
| D-012 | ADR-0078/0090 | Membership runtime/cache/files/provider evidence — P-012 |
| D-013 | ADR-0084/0100 | Backup physical/artifact/provider/restore evidence — P-013 |
| D-014 | ADR-0044/0102 | Pro updater TUF verifier/key custody/metadata/package staging — TU protocol |

## B. Current accepted paper baselines

- Definition D1/PT-C; D2/D3/D4 comparisons.
- Relations R1/PT-D; R2/PT-E mandatory.
- Query QP1 native-WP; QP2 Custom Table; QP3 Relations; QP4 remote.
- Field Storage FS1–FS6.
- Custom Tables CT1/PT-E vs CT2/PT-D; CT3 network-owned.
- Settings ST1/PT-A; ST2/PT-B; ST3 inheritance.
- Forms FRT1/PT-D vs FRT2/PT-E.
- Chat CRT1/PT-D vs CRT2/PT-E.
- Membership M1/PT-D vs M2/PT-E.
- Notification/Email NE1/PT-D vs NE2/PT-E.
- Event Inbox EI1/PT-D vs EI2/PT-E.
- Audit AU1/PT-D.
- Workflow WF1/PT-D vs WF2/PT-E.
- JobService J1/J2/J3.
- REST RE1 + RI1/RI2.
- Import IR1/PT-D vs IR2/PT-E.
- Backup Remote Copy BR1/BR2/BR3.
- Vault V1/PT-C vs V2.
- User/Profile native WordPress identity authority + WPE security workflows.
- Role/Capability native WordPress authorization authority + WPE anti-lockout/recovery.
- Admin Columns AC1 whole-request batching.
- Dynamic Listings DL1 auth-aware Query + batched hydration + SSR.
- Backup artifact H-B1 SHA-256; CMP0 fallback; CMP1 gzip comparison; ZIP convenience only.
- OAuth Account Link fixed callback + one-time site return + PKCE S256 evidence protocol.
- Pro updater TUF Root/Targets/Snapshot/Timestamp evidence protocol.

All remain paper-only unless separately certified.

## C. Admin Columns / Listings

ADR-0098/0099 resolve static semantics. Remaining:
- WordPress/third-party list-table hooks;
- exact batch/query budgets;
- backend sorting/filtering adapters;
- inline/bulk edit concurrency/authorization;
- export/lazy mode;
- Listing cursor/count/refill semantics;
- protected cache storage/invalidation;
- nested-list budgets;
- SSR/client parity;
- SEO/builder adapters.

Admin Columns runtime: **0**. Listings runtime: **0**.

## D. Backup — ADR-0084/0100 / P-013

Accepted artifact semantics:
- manifest-first multipart bundle;
- SHA-256 stored-byte integrity;
- AEAD distinct from object hash;
- CMP0 no-compression fallback;
- CMP1 gzip streaming first compression comparison;
- ZIP convenience only;
- FR1 vs FR2 file stream;
- DB1/DB2/DB3 DB payload comparison;
- provider multipart below WPE Part identity.

Open:
- exact byte format/chunk sizes/compression levels/parser limits;
- archive bomb/path/symlink safety;
- encrypted disaster restore;
- crash/final-manifest windows;
- exact runtime DDL/indexes;
- provider C0–C4 certification.

Backup: **34 targets / 0 C-certified / 0 C3 Supported**. P-013 runtime: **0**.

## E. OAuth Account Link — ADR-0034/0101

OA-01…OA-32 are fixed future fixtures.

Open evidence:
- exact client/service endpoints and transaction store;
- PKCE/state/issuer binding;
- return artifact redemption;
- refresh token rotation/replay behavior;
- token lifetimes/scopes;
- proxy/callback canonicalization;
- clone/domain migration;
- disconnect/outage;
- Vault integration;
- privacy/log leakage.

**OA executed: 0/32.**

## F. Pro updater TUF — ADR-0044/0102

TU-01…TU-44 are fixed future fixtures.

Open evidence:
- production-grade PHP verifier or audited equivalent;
- official TUF conformance;
- Root/Targets thresholds/custody operations;
- Snapshot/Timestamp online isolation;
- metadata expiry/rollback/freeze/mix-and-match;
- consistent snapshots;
- target hash/length/custom compatibility;
- key compromise/rotation runbooks;
- ZIP staging/path/bomb/recovery;
- Free↔Pro update order/schema recovery.

**TU executed: 0/44.**

Automated Pro updates stay blocked if this evidence cannot meet the accepted bar.

## G. Other current evidence state

- Definition P-004: **0 executed**.
- Relations P-010: **0 executed**.
- Query P-009: **0 executed**.
- Job P-003: **0 executed**.
- Vault P-005: **0 executed**.
- Workflow P-011: **0 executed**.
- Membership P-012: **0 executed**; billing **4 BE3 / 0 MB-certified**; protected file **0 PC1+**.
- Email: **6 EE3 / 0 ET-certified**.
- Event adapters: **0 I4/I5**.
- REST/Import: **0 runtime fixtures**.
- User/Profile: **0 runtime fixtures**.
- Role/Capability: **0 runtime fixtures**.
- Site Lifecycle: **0/40**.
- Multisite: **0 MS1+**.
- Remote privacy: **0/30**.
- Product License API/service: **0**.

## H. Accepted architecture no longer open semantically

ADRs **0035–0102** preserve accepted core semantics. Evidence can refine exact implementation/version facts but cannot silently redesign them.

## Decision-processing rule

1. Inspect repository and authoritative evidence.
2. Resolve static semantics in ADR when sufficient.
3. Predefine bounded executable protocol when proof is required.
4. **Do not install, compile, migrate, benchmark, test, contact services/providers, send mail, run queues, generate signing keys/TUF metadata, execute OAuth, create archives or transfer data before explicit owner consent.**
5. Keep governance/Draft PR synchronized.

## Next planning-only priorities

1. Dashboard Widgets content-source/cache/refresh operational evidence profile.
2. Admin Menu transform conflict/performance evidence protocol.
3. Protector rate-limit/trusted-proxy execution protocol.
4. Reset journal/recovery execution protocol.
5. Watermarker media lifecycle/offload concurrency evidence protocol.
6. Keep P-001…P-013 and OA/TU gates intact.