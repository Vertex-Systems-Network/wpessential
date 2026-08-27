# WPEssential — Open Decisions Register

Status: **Phase 0 / planning-only / no development consent**  
Last synchronized: 2026-08-28

This register contains unresolved implementation profiles/evidence only. Accepted architecture/product/security decisions are preserved in ADRs through **ADR-0095**.

All executable work remains blocked by ADR-0014 until explicit owner consent.

## A. Platform executable blockers

| ID | Related ADR | Remaining evidence |
|---|---|---|
| D-001 | ADR-0002 + ADR-0069 + ADR-0075 | WP/PHP/DB compatibility and Multisite activation/scope/site-lifecycle matrix — P-001 |
| D-002 | ADR-0005 | UI runtime/externalization/accessibility/RTL/bundle — P-002 |
| D-003 | ADR-0006/0059/0068/0083 | J1/J2/J3 physical mapping, Action Scheduler coexistence/backend, claims/fairness/backpressure/runners/retention/Multisite — P-003 |
| D-004 | ADR-0008/0049/0073/0092 | Definition D1 vs D2/D3/D4 exact DDL/index/locking/migration under fixed P-004 protocol |
| D-005 | ADR-0009/0048/0085 | Vault V1 vs V2 exact crypto/envelope/DDL/rotation/recovery/redaction/security review — P-005 |
| D-006 | ADR-0010/0070/0072/0076/0091 | Free↔Pro/Product License runtime/API/OAuth/idempotency/ETag/allocation/clone/offline-grace/service persistence — P-006 |
| D-007 | ADR-0011 | executable CI matrix — P-007 |
| D-008 | ADR-0012 | build/externalization/toolchain comparison — P-008 |

## B. Current physical/compiler benchmark baselines

Topology classes remain PT-A native site, PT-B native network/global, PT-C scoped WPE control-plane, PT-D scoped shared high-volume runtime, PT-E per-site custom runtime and PT-F external authority.

Current first profiles/comparisons:
- Definition: **D1/PT-C** first, D2/D3/D4 comparisons — ADR-0073; exact protocol ADR-0092 / P-004;
- Relations: **R1/PT-D** first, R2/PT-E mandatory, R3 exceptional — ADR-0074; exact protocol ADR-0093 / P-010;
- Query: **QP1 WordPress-native** first; QP2 Custom Table + QP3 Relations-assisted workload profiles; QP4 remote separately certified — ADR-0086 / P-009;
- Field Storage: **FS1 native WP default**, FS2 Custom Table escalation, FS3 child rows, FS4 Relations, FS5 Vault refs — ADR-0087;
- Custom Tables: **CT1/PT-E** first for site-owned tables, CT2/PT-D mandatory large-network comparison, CT3 explicit network-owned — ADR-0088;
- Settings: **ST1/PT-A** site, **ST2/PT-B** network, ST3 inheritance; ST4 per-field comparison — ADR-0089;
- Forms: **FRT1/PT-D** first, FRT2/PT-E mandatory — ADR-0077;
- Chat: **CRT1/PT-D** first, CRT2/PT-E mandatory — ADR-0077;
- Membership: **M1/PT-D** first, M2/PT-E mandatory — ADR-0078 / P-012;
- Notification/Email: **NE1/PT-D** first, NE2/PT-E mandatory — ADR-0079;
- Event Inbox: **EI1/PT-D** first, EI2/PT-E mandatory — ADR-0080;
- Audit: **AU1/PT-D** favored — ADR-0081;
- Workflow: **WF1/PT-D** first, WF2/PT-E mandatory — ADR-0082 / P-011;
- JobService history: **J1/PT-D Jobs+Attempts** first, J2 PT-C-current+PT-D-history mandatory, J3 PT-C low-volume control — ADR-0083 / P-003;
- REST: **RE1 WordPress REST + compiled descriptor** first; RE2 gateway future; RI1/PT-D idempotency first candidate vs RI2 atomic service — ADR-0094;
- Import: **IR1/PT-D** first, IR2/PT-E mandatory — ADR-0095;
- Backup Remote Copy: **BR1/PT-D** first, BR2 PT-C-current+PT-D-history mandatory, BR3 PT-E comparison — ADR-0084 / P-013;
- Vault: **V1/PT-C** favored first, V2 per-site + separate network Vault mandatory comparison — ADR-0085 / P-005.

All are paper profiles. **No DDL/index/database/compiler/runtime benchmark has executed.**

## C. Definition — ADR-0073/0092 / P-004

Static baseline and exact evidence contract are now accepted. Remaining work is executable only:
- D1/D2/D3/D4 exact DDL/types/lengths/collations/indexes;
- fixed DF-S/DF-M/DF-L/DF-N datasets;
- Q1–Q10 query plans;
- C1–C7 save/publish/uniqueness/lifecycle races;
- migration/interrupted-upgrade/Backup/Restore;
- wrong-site/normalization attack fixtures;
- supported MySQL/MariaDB matrix.

Executed P-004 cases: **0**.

## D. Relations — ADR-0074/0093 / P-010

Static baseline and exact evidence contract are now accepted. Remaining work is executable only:
- R1/R2/R3 exact DDL + endpoint/pivot representation;
- RF-S/RF-M/RF-L/RF-N/RF-H graph fixtures;
- RQ1–RQ11 query plans/N+1 behavior;
- RC1–RC8 cardinality/concurrency races;
- cache/lifecycle/Backup/Restore;
- wrong-site/prefix/cross-site attacks;
- large-network/noisy-neighbor vs table-proliferation evidence.

Executed P-010 cases: **0**.

## E. Query — ADR-0086 / P-009

Semantic compiler boundaries are accepted. Open executable evidence:
- QP1/QP2/QP3 exact provider compilers/query plans;
- SQL/identifier/order injection;
- cursor vs offset;
- static cost calibration;
- cache invalidation/revoke isolation;
- 10k/100k/1M rows and 100/1k/10k-site aggregation;
- QP4 provider certification.

Executed P-009 fixtures: **0**.

## F. Field Storage / Custom Tables — ADR-0087/0088

Field routing semantics are accepted; no universal field store remains open.

Open executable evidence:
- native adapter compatibility/revisions/queryability;
- exact Custom Table column/child-row DDL/indexes;
- uniqueness races;
- Field Migration Plan crash/resume/fidelity;
- CT1/PT-E vs CT2/PT-D MySQL/MariaDB migration/locking/shadow/backfill;
- large-network table proliferation vs shared isolation/noisy-neighbor;
- Backup/lifecycle/wrong-site cases.

Executed Field/Custom Table fixtures: **0**.

## G. Settings — ADR-0089

ST1/ST2/ST3 semantics accepted. Open executable evidence:
- grouped option vs ST4 performance/atomicity;
- supported WordPress autoload behavior;
- stale-edit/CAS or locking mechanism;
- network-default cache invalidation at 100/1k/10k sites;
- REST scope attacks;
- clone/export/delete/Vault degradation.

Executed Settings fixtures: **0**.

## H. Membership — ADR-0078/0090 / P-012

M1/PT-D vs M2/PT-E remains future runtime comparison. Enrollment authoritative, Entitlements derived, Principal Access Generation supports revoke/cache semantics.

Protected delivery profiles PD1–PD4 and PC0–PC4 are accepted.

Open executable evidence:
- exact Membership DDL/cache/locking/provider reconciliation/refund/identity/privacy/restore;
- origin-bypass PC1+ tests;
- Range/cache/CDN/path/header abuse;
- signed URL expiry/revocation truth;
- public→private migration;
- Backup/Restore/clone protected assets.

Billing source profiles remain **4 BE3 / 0 MB-certified**. Protected delivery remains **0 PC1+ certified**.

## I. REST — ADR-0094

RE1 compiled WordPress REST runtime semantics are accepted. Operational state stays separate from Definition Repository.

Open executable evidence:
- route registration/conflicts/auth modes;
- request schema fuzz/mass assignment/IDOR/wrong-site attacks;
- RI1/PT-D vs RI2 atomic idempotency implementation;
- crash/unknown-outcome reconciliation;
- shared Rate Limit Service backend/concurrency/proxy behavior;
- permission-aware cache isolation/invalidation;
- CORS/preflight attacks;
- 100/1k/10k-site operational namespace isolation.

Executed REST operational fixtures: **0**.

## J. Import/Export — ADR-0095

IR1/PT-D first, IR2/PT-E mandatory comparison. Plan/Run/Checkpoint/Identity Map/Journal/Job boundaries are semantically fixed.

Open executable evidence:
- exact Run/Checkpoint/Identity Map/Journal DDL/indexes;
- 100k/1M record chunk/resume;
- crash before/after target/Map/Checkpoint/enqueue boundaries;
- duplicate Job/concurrent same-source import;
- target-edit/rollback conflicts;
- source archive path/size/MIME/SSRF security;
- IR1 noisy-neighbor vs IR2 table/version cost at 100/1k/10k sites;
- Backup/Restore/retention cleanup;
- source-specific migration adapters.

Executed Import physical/recovery fixtures: **0**.

## K. Workflow — ADR-0082 / P-011

WF1/PT-D first, WF2/PT-E mandatory. Open exact Run/Step/Wait/Approval DDL/indexes, trigger/Job reconciliation, duplicate execution, wait/approval races, external unknown outcome, cancellation/compensation, lifecycle/Restore and large-network evidence.

Executed P-011 benchmarks: **0**.

## L. JobService — ADR-0083 / P-003

J1/J2/J3 mapping accepted. Open exact Job/Attempt DDL, claims/leases, backend enqueue reconciliation, fairness/backpressure/admission, schedule overlap, lifecycle/Restore, Action Scheduler 4.1.0 coexistence and 100/1k/10k-site fairness.

Executed P-003 evidence: **0**.

## M. Notification / Email — ADR-0079

NE1/PT-D vs NE2/PT-E remains future comparison. Occurrence, recipient/read, channel delivery, transport attempt and verified provider evidence remain separate.

Open renderer/client/fan-out/unknown-outcome/provider correlation/retention/Restore evidence. Email profiles remain **6 EE3 / 0 ET-certified**.

## N. Connections / Event Inbox — ADR-0080

EI1/PT-D vs EI2/PT-E remains future comparison. Trusted endpoint/Connection establishes scope; payload IDs do not. Event Inbox dedupe does not replace consumer idempotency.

Open signature/replay/dedupe/claim/routing/retention/large-network/provider evidence. **0 I4/I5 event certifications**.

## O. Audit — ADR-0081

AU1/PT-D favored. Exact DDL/index/retention/fail-closed classes/privacy transforms/scale remain open. Local DB is not claimed tamper-proof; any hash-chain/signed/external checkpoint requires separate attacker-model/key-custody evidence.

Executed Audit evidence: **0**.

## P. Backup — ADR-0084 / P-013

BR1/PT-D first, BR2 split mandatory, BR3 PT-E comparison. Open exact Backup Set/Part/Remote Copy/Object/Attempt DDL, commit-unknown, manifest-last crash windows, checksum/object identity/delete/version-lock truth, only-known-good-copy pruning, re-verification/failover, site lifecycle/fresh-server Restore, encryption/recovery and provider C0–C4.

Backup targets: **34 / 0 C-certified / 0 C3 Supported**. Executed transfers/restores/benchmarks: **0**.

## Q. Vault — ADR-0085 / P-005

V1/PT-C favored first; V2 per-site + network Vault mandatory comparison. Open exact DDL, XChaCha20/AAD serialization, external/WP-derived HKDF stability, key slots, replace/rotation races, use-grant revoke, clone/lost-key/Backup/Restore, plaintext leakage scans, large-network comparison and independent security review.

Executed Vault crypto/physical evidence: **0**.

## R. Multisite / Site Lifecycle — ADR-0069/0071/0075

31/31 scopes mapped. Site Lifecycle protocol has **40 fixtures / 0 executed**. Multisite certification remains **0 MS1+**.

Open provision/reactivate/delete/uninitialize, Job/Workflow/Notification/Webhook/REST/Import drain, scoped cleanup/retention, Vault grants, Membership revoke, Product License release, Backup recovery, clone/transfer/DR, crash/restart and 100/1k/10k-site evidence.

## S. Remote service / Product License — ADR-0076/0091

Resource/state/HTTP principles and component schemas are accepted.

Open implementation/service evidence:
- actual OpenAPI YAML/JSON + lint;
- OAuth scopes/token lifecycle;
- Idempotency-Key canonicalization/retention;
- ETag/If-Match behavior;
- allocation/release/transfer races;
- cursor pagination/RFC 9457;
- conflict privacy/resource enumeration;
- offline/clone/transfer persistence;
- signed entitlement verification integration;
- service operations.

Remote privacy: **30 fixtures / 0 executed**. Product License API/service fixtures: **0**.

## T. Accepted architecture no longer open semantically

ADRs **0035–0095** preserve accepted core semantics. Evidence can refine implementation/version facts but cannot silently redesign them.

Benchmark/profile labels D1/R1/QP1/FS1/CT1/ST1/FRT1/CRT1/M1/NE1/EI1/WF1/J1/RE1/IR1/BR1/V1/PD1 are not final implementation certification. AU1 is not a tamper-proof claim. Backend/provider state cannot replace owning WPE domain truth.

## Decision-processing rule

1. Inspect repository and authoritative evidence.
2. Resolve static semantics in ADR when evidence is sufficient.
3. Document bounded executable protocol when proof is required.
4. **Do not install, compile, migrate, benchmark, test, contact providers/services, send mail, run queues, execute crypto, move protected files, run imports or transfer data before explicit owner consent.**
5. Synchronize governance after meaningful milestones.

## Next planning-only priorities

1. User Profile identity/session/change runtime security profile.
2. Role & Capability anti-lockout/Multisite/Super Admin runtime evidence profile.
3. Admin Columns + Dynamic Listings N+1/write/cache operational profiles.
4. Backup archive/container exact artifact/chunk/compression/hash paper protocol.
5. Product Account OAuth/TUF/service evidence protocols where static detail still reduces ambiguity.
6. Keep P-001…P-013 executable gates intact.