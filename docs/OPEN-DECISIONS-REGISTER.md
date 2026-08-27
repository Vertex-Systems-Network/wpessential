# WPEssential — Open Decisions Register

Status: **Phase 0 / planning-only / no development consent**  
Last synchronized: 2026-08-28

This register contains unresolved implementation profiles/evidence only. Accepted architecture/product/security decisions are preserved in ADRs through **ADR-0091**.

All executable work remains blocked by ADR-0014 until explicit owner consent.

## A. Platform executable blockers

| ID | Related ADR | Remaining evidence |
|---|---|---|
| D-001 | ADR-0002 + ADR-0069 + ADR-0075 | WP/PHP/DB compatibility and Multisite activation/scope/site-lifecycle matrix — P-001 |
| D-002 | ADR-0005 | UI runtime/externalization/accessibility/RTL/bundle — P-002 |
| D-003 | ADR-0006/0059/0068/0083 | J1/J2/J3 physical mapping, Action Scheduler coexistence/backend, claims/fairness/backpressure/runners/retention/Multisite — P-003 |
| D-004 | ADR-0008/0049/0073 | Definition D1 vs D2/D3/D4 exact DDL/index/locking/migration — P-004 |
| D-005 | ADR-0009/0048/0085 | Vault V1 vs V2 exact crypto/envelope/DDL/rotation/recovery/redaction/security review — P-005 |
| D-006 | ADR-0010/0070/0072/0076/0091 | Free↔Pro/Product License runtime/API/OAuth/idempotency/ETag/allocation/clone/offline-grace/service persistence — P-006 |
| D-007 | ADR-0011 | executable CI matrix — P-007 |
| D-008 | ADR-0012 | build/externalization/toolchain comparison — P-008 |

## B. Current physical/compiler benchmark baselines

Topology classes remain PT-A native site, PT-B native network/global, PT-C scoped WPE control-plane, PT-D scoped shared high-volume runtime, PT-E per-site custom runtime and PT-F external authority.

Current first profiles/comparisons:
- Definition: **D1/PT-C** first, D2/D3/D4 comparisons — ADR-0073 / P-004;
- Relations: **R1/PT-D** first, R2/PT-E mandatory, R3 exceptional — ADR-0074 / P-010;
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
- Backup Remote Copy: **BR1/PT-D** first, BR2 PT-C-current+PT-D-history mandatory, BR3 PT-E comparison — ADR-0084 / P-013;
- Vault: **V1/PT-C** favored first, V2 per-site + separate network Vault mandatory comparison — ADR-0085 / P-005.

All are paper profiles. **No DDL/index/database/compiler benchmark has executed.**

## C. Query — ADR-0086 / P-009

Semantic compiler boundaries are now accepted; remaining evidence includes:
- QP1/QP2/QP3 exact provider compiler behavior;
- SQL/identifier/order injection corpus;
- WordPress meta/tax/date and Custom Table query plans;
- relation N+1 prevention;
- cursor vs offset correctness/performance;
- static cost calibration by execution context;
- persistent-cache key/invalidation and revoke-to-deny isolation;
- 10k/100k/1M rows and 100/1k/10k-site network aggregation;
- remote QP4 provider-specific I-certification.

Executed P-009 fixtures: **0**.

## D. Field Storage / Custom Tables — ADR-0087/0088

Field routing semantics are accepted; no universal field store remains open.

Open Field evidence:
- FS1 native adapter compatibility/revision/query behavior;
- FS2/FS3 exact column/child-row physical/index profiles;
- uniqueness/concurrency;
- source→target Field Migration Plan crash/resume/fidelity;
- projection invalidation and privacy/export behavior.

Custom Tables CT1/PT-E vs CT2/PT-D remains a physical evidence comparison:
- exact DDL/types/indexes/UUID/row identity;
- MySQL/MariaDB `ALTER`/locking/algorithm behavior;
- chunked backfill/shadow-copy/swap/recovery;
- observed-schema drift and stale-plan revalidation;
- 100/1k/10k-site table proliferation vs shared scope/noisy-neighbor behavior;
- Backup/site lifecycle/wrong-site destructive attacks.

Executed Field/Custom Table fixtures: **0**.

## E. Settings — ADR-0089

ST1/ST2/ST3 semantics are accepted. Open evidence:
- grouped option vs ST4 per-field performance/atomicity;
- supported WordPress autoload behavior;
- value-version stale-edit/CAS or locking mechanism;
- network-default cache invalidation/versioning at 100/1k/10k sites;
- REST read/write site-network attacks;
- clone/export/delete/Vault-degraded behavior.

Executed Settings fixtures: **0**.

## F. Membership — ADR-0078/0090 / P-012

M1/PT-D vs M2/PT-E remains future runtime comparison. Enrollment authoritative, Entitlements derived, Principal Access Generation supports the paper revoke/cache model.

Protected-file delivery profiles are accepted:
- PD1 private local/PHP correctness baseline;
- PD2 server-accelerated local;
- PD3 private object signed delivery;
- PD4 future stronger-revocation CDN;
- PC0–PC4 certification levels.

Open evidence:
- exact Membership DDL/cache/locking/provider reconciliation/refund/identity/privacy/restore;
- origin-bypass PC1+ tests;
- Range/cache/CDN/path/header abuse;
- signed URL expiry/revocation truth;
- public→private migration;
- Backup/Restore/clone protected assets.

Billing source profiles remain **4 BE3 / 0 MB-certified**. Protected delivery remains **0 PC1+ certified**.

## G. Workflow — ADR-0082 / P-011

WF1/PT-D first, WF2/PT-E mandatory. Open exact Run/Step/Wait/Approval DDL/indexes, trigger/Job reconciliation, duplicate execution, wait/approval races, external unknown outcome, cancellation/compensation, lifecycle/Restore and large-network evidence.

Executed P-011 benchmarks: **0**.

## H. JobService — ADR-0083 / P-003

J1/J2/J3 mapping accepted. Open exact Job/Attempt DDL, claims/leases, backend enqueue reconciliation, fairness/backpressure/admission, schedule overlap, lifecycle/Restore, Action Scheduler 4.1.0 coexistence and 100/1k/10k-site fairness.

Executed P-003 evidence: **0**.

## I. Notification / Email — ADR-0079

NE1/PT-D vs NE2/PT-E remains future comparison. Occurrence, recipient/read, channel delivery, transport attempt and verified provider evidence remain separate.

Open renderer/client/fan-out/unknown-outcome/provider correlation/retention/Restore evidence. Email profiles remain **6 EE3 / 0 ET-certified**.

## J. Connections / Event Inbox — ADR-0080

EI1/PT-D vs EI2/PT-E remains future comparison. Trusted endpoint/Connection establishes scope; payload IDs do not. Event Inbox dedupe does not replace consumer idempotency.

Open signature/replay/dedupe/claim/routing/retention/large-network/provider evidence. **0 I4/I5 event certifications**.

## K. Audit — ADR-0081

AU1/PT-D favored. Exact DDL/index/retention/fail-closed classes/privacy transforms/scale remain open. Local DB is not claimed tamper-proof; any hash-chain/signed/external checkpoint requires separate attacker-model/key-custody evidence.

Executed Audit evidence: **0**.

## L. Backup — ADR-0084 / P-013

BR1/PT-D first, BR2 split mandatory, BR3 PT-E comparison. Open exact Backup Set/Part/Remote Copy/Object/Attempt DDL, commit-unknown, manifest-last crash windows, checksum/object identity/delete/version-lock truth, only-known-good-copy pruning, re-verification/failover, site lifecycle/fresh-server Restore, encryption/recovery and provider C0–C4.

Backup targets: **34 / 0 C-certified / 0 C3 Supported**. Executed transfers/restores/benchmarks: **0**.

## M. Vault — ADR-0085 / P-005

V1/PT-C favored first; V2 per-site + network Vault mandatory comparison. Open exact DDL, XChaCha20/AAD serialization, external/WP-derived HKDF stability, key slots, replace/rotation races, use-grant revoke, clone/lost-key/Backup/Restore, plaintext leakage scans, large-network comparison and independent security review.

Executed Vault crypto/physical evidence: **0**.

## N. Multisite / Site Lifecycle — ADR-0069/0071/0075

31/31 scopes mapped. Site Lifecycle protocol has **40 fixtures / 0 executed**. Multisite certification remains **0 MS1+**.

Open provision/reactivate/delete/uninitialize, Job/Workflow/Notification/Webhook drain, scoped cleanup/retention, Vault grants, Membership revoke, Product License release, Backup recovery, clone/transfer/DR, crash/restart and 100/1k/10k-site evidence.

## O. Remote service / Product License — ADR-0076/0091

Resource/state/HTTP principles and paper component schemas are now accepted.

Open implementation/service evidence:
- actual OpenAPI YAML/JSON encoding/lint;
- exact string/format/length constraints;
- OAuth scopes/token lifecycle;
- Idempotency-Key body canonicalization/retention;
- ETag/If-Match stale mutation behavior;
- last-seat and release/reallocate races;
- cursor pagination;
- RFC 9457 conformance;
- conflict privacy/resource enumeration;
- clone/transfer/offline-grace flows;
- remote-success/local-persist-failure;
- signed entitlement verification integration;
- service persistence/operations.

Remote privacy: **30 fixtures / 0 executed**. Product License API/service fixtures: **0**.

## P. Accepted architecture no longer open semantically

ADRs **0035–0091** preserve accepted core semantics. Evidence can refine implementation/version facts but cannot silently redesign them.

Benchmark/profile labels D1/R1/QP1/FS1/CT1/ST1/FRT1/CRT1/M1/NE1/EI1/WF1/J1/BR1/V1/PD1 are not final implementation certification. AU1 is not a tamper-proof claim. Backend/provider state cannot replace owning WPE domain truth.

## Decision-processing rule

1. Inspect repository and authoritative evidence.
2. Resolve static semantics in ADR when evidence is sufficient.
3. Document bounded executable protocol when proof is required.
4. **Do not install, compile, migrate, benchmark, test, contact providers/services, send mail, run queues, execute crypto, move protected files or transfer data before explicit owner consent.**
5. Synchronize governance after meaningful milestones.

## Next planning-only priorities

1. Definition P-004 exact fixture/query-plan/locking protocol without DDL execution.
2. Relations P-010 exact endpoint/cardinality/concurrency benchmark protocol without tables.
3. REST API Builder compiled endpoint auth/rate/CORS/cache physical profile.
4. Import/Export Run + Identity Map + Journal physical topology/recovery profile.
5. User Profile + Role/Capability runtime security/anti-lockout evidence profiles.
6. Keep P-001…P-013 executable gates intact.