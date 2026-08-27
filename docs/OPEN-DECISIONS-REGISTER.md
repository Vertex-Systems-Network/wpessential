# WPEssential — Open Decisions Register

Status: **Phase 0 / planning-only / no development consent**  
Last synchronized: 2026-08-28

This register contains unresolved implementation profiles/evidence only. Accepted architecture/product/security decisions are preserved in ADRs through **ADR-0097**.

All executable work remains blocked by ADR-0014 until explicit owner consent.

## A. Platform executable blockers

| ID | Related ADR | Remaining evidence |
|---|---|---|
| D-001 | ADR-0002 + ADR-0069 + ADR-0075 | WP/PHP/DB compatibility and Multisite activation/scope/site-lifecycle matrix — P-001 |
| D-002 | ADR-0005 | UI runtime/externalization/accessibility/RTL/bundle — P-002 |
| D-003 | ADR-0006/0059/0068/0083 | J1/J2/J3 physical mapping, Action Scheduler coexistence/backend, claims/fairness/backpressure/runners/retention/Multisite — P-003 |
| D-004 | ADR-0008/0049/0073/0092 | Definition D1 vs D2/D3/D4 exact DDL/index/locking/migration evidence under fixed P-004 protocol |
| D-005 | ADR-0009/0048/0085 | Vault V1 vs V2 exact crypto/envelope/DDL/rotation/recovery/redaction/security review — P-005 |
| D-006 | ADR-0010/0070/0072/0076/0091 | Free↔Pro/Product License runtime/API/idempotency/ETag/allocation/clone/offline-grace — P-006 |
| D-007 | ADR-0011 | executable CI matrix — P-007 |
| D-008 | ADR-0012 | build/externalization/toolchain comparison — P-008 |

## B. Current physical benchmark baselines

Topology classes remain PT-A native site, PT-B native network/global, PT-C scoped WPE control-plane, PT-D scoped shared high-volume runtime, PT-E per-site custom runtime and PT-F external authority.

Current first profiles/comparisons:
- Definition: **D1/PT-C** first, D2/D3/D4 comparisons — ADR-0073/0092 / P-004;
- Relations: **R1/PT-D** first, R2/PT-E mandatory, R3 exceptional — ADR-0074/0093 / P-010;
- Query: **QP1 WordPress-native** first, QP2 Custom Table, QP3 Relations-assisted, QP4 remote separately certified — ADR-0086 / P-009;
- Field Storage: FS1 native WP default, FS2 typed table, FS3 child rows, FS4 Relations, FS5 Vault, FS6 derived — ADR-0087;
- Custom Tables: **CT1/PT-E** first for site-owned tables, CT2/PT-D mandatory, CT3 network-owned only — ADR-0088;
- Settings: ST1/PT-A site, ST2/PT-B network, ST3 inheritance — ADR-0089;
- Forms: **FRT1/PT-D** first, FRT2/PT-E mandatory — ADR-0077;
- Chat: **CRT1/PT-D** first, CRT2/PT-E mandatory — ADR-0077;
- Membership: **M1/PT-D** first, M2/PT-E mandatory — ADR-0078 / P-012;
- Notification/Email: **NE1/PT-D** first, NE2/PT-E mandatory — ADR-0079;
- Event Inbox: **EI1/PT-D** first, EI2/PT-E mandatory — ADR-0080;
- Audit: **AU1/PT-D** favored — ADR-0081;
- Workflow: **WF1/PT-D** first, WF2/PT-E mandatory — ADR-0082 / P-011;
- JobService history: **J1/PT-D Jobs+Attempts** first, J2 PT-C-current+PT-D-history mandatory, J3 PT-C low-volume control — ADR-0083 / P-003;
- REST: RE1 WordPress REST + immutable descriptor; RI1/PT-D idempotency first, RI2 atomic service comparison — ADR-0094;
- Import: IR1/PT-D first, IR2/PT-E mandatory — ADR-0095;
- Backup Remote Copy: **BR1/PT-D** first, BR2 split mandatory, BR3 PT-E — ADR-0084 / P-013;
- Vault: **V1/PT-C** favored first, V2 per-site + separate network Vault mandatory — ADR-0085 / P-005.

All are paper profiles. **No DDL/index/database benchmark has executed.**

## C. Query / Fields / Tables blockers

- **Q-001 / P-009** — Query AST compiler security/cost budgets/cache invalidation/storage-adapter plans/network aggregation/IDOR/scale. ADR-0086 fixes the profile order, not thresholds/implementation.
- Field Storage routing semantics are accepted in ADR-0087; exact native-meta/custom-table index/projection/migration evidence remains open.
- Custom Tables CT1/CT2/CT3 semantics are accepted in ADR-0088; exact desired-schema compiler, DDL, online migration/backfill/locking/recovery remain open.
- Admin Columns/Listings still require dedicated N+1/batching/sort/filter/write/cache/SSR operational profiles.

## D. Workflow — ADR-0082 / P-011

WF1/PT-D is first future benchmark, WF2/PT-E mandatory comparison.

Open evidence: exact DDL/indexes, trigger idempotency, enqueue reconciliation, duplicate jobs, wait/event races, approvals/joins, external unknown outcome, cancellation/compensation, restore/site lifecycle and large-scale evidence.

Executed P-011 benchmarks: **0**.

## E. JobService — ADR-0083 / P-003

J1/J2/J3 mapping is accepted for future comparison. Backend action rows never become WPE business truth.

Open evidence: exact Job/Attempt DDL/indexes/claims/leases, commit↔enqueue recovery, crash/lease ambiguity, fairness/backpressure, recurring overlap, site lifecycle/Restore, Action Scheduler coexistence and large-network fairness.

Executed P-003 backend/physical evidence: **0**.

## F. Backup — ADR-0084 / P-013

BR1/PT-D first, BR2 split mandatory comparison, BR3 PT-E isolation comparison.

Open evidence:
- exact Backup Set/Part/Remote Copy/Object/Attempt DDL/indexes;
- final manifest/chunk/compression/hash artifact profile;
- commit-unknown reconciliation;
- manifest-last crash windows;
- provider checksum/object identity semantics;
- delete/trash/version/object-lock truth;
- only-known-good-copy prune protection;
- re-verification/alternate-copy failover;
- site lifecycle/fresh-server restore;
- encryption/recovery integration;
- provider C0–C4 certification.

Backup targets: **34 / 0 C-certified / 0 C3 Supported**. Executed transfers/restores/benchmarks: **0**.

## G. Vault — ADR-0085 / P-005

V1/PT-C favored first; V2 per-site + separate network Vault mandatory comparison.

Open evidence: exact DDL/types/indexes, AEAD/AAD/envelope serialization, external/WP-derived key stability/rotation, recovery/KMS slots, concurrent replace, VRK rotation, use-grant revoke race, clone/staging/lost-key/Backup/Restore, leakage scans, scale and independent security review.

Executed Vault crypto/physical evidence: **0**.

## H. Membership — ADR-0078/0090 / P-012

M1/PT-D vs M2/PT-E remains future comparison. Enrollment authoritative, Entitlements derived, principal generation revoke-safe paper model.

Protected-file PD1–PD4/PC0–PC4 semantics accepted; exact local streaming/server acceleration/object-storage/CDN evidence remains open.

Billing source profiles remain **4 BE3 / 0 MB-certified**. Protected delivery **0 PC1+**.

## I. Notification / Email — ADR-0079

NE1/PT-D vs NE2/PT-E remains future comparison. Occurrence, recipient/read, channel delivery, transport attempt and verified provider evidence remain separate.

Open renderer/client/fan-out/unknown-outcome/provider correlation/retention/Restore evidence. Email profiles remain **6 EE3 / 0 ET-certified**.

## J. Connections / Event Inbox — ADR-0080

EI1/PT-D vs EI2/PT-E future comparison. Trusted endpoint/Connection establishes scope; payload IDs do not. Event Inbox dedupe does not replace consumer idempotency.

Open signature/replay/dedupe/claim/routing/retention/large-network/provider evidence. **0 I4/I5 certifications**.

## K. Audit — ADR-0081

AU1/PT-D favored. Exact DDL/index/retention/fail-closed classes/privacy transforms/scale remain open. Local DB is not claimed tamper-proof.

## L. REST / Import — ADR-0094/0095

REST RE1 semantics are accepted. Open evidence: route conflicts, cookie+nonce/Application Passwords, schema fuzzing, rate-limit atomicity/proxy handling, idempotency unknown outcomes, cache leakage, CORS, large-source performance.

Import IR1/PT-D vs IR2/PT-E is accepted. Open evidence: exact Run/Checkpoint/Map/Journal DDL, crash-after-write reconciliation, archive/SSRF safety, rollback conflict behavior, high-volume chunk/resume, source adapters and Multisite.

Executed REST/Import runtime fixtures: **0**.

## M. User/Profile — ADR-0096

WordPress remains identity/password/session/Application Password authority. WPE adds bounded profile data mappings and dedicated identity/security actions.

Open evidence:
- core email confirmation/re-auth behavior;
- protected-meta registry coverage;
- session invalidation;
- Application Password secrecy/revocation;
- public profile IDOR/privacy;
- Multisite target-user capability behavior;
- account-delete/reassignment policy.

Executed User/Profile fixtures: **0**.

## N. Role & Capability — ADR-0097

WordPress remains role/capability/Super Admin authority. WPE adds Change Plan, impact simulation, anti-lockout invariant, snapshot/reconciliation and non-authenticating recovery mode.

Open evidence:
- effective capability/meta-cap simulation;
- multi-role/user overrides;
- last recovery principal/self-lockout races;
- role deletion/reassignment;
- plugin-owned roles;
- Multisite/Super Admin boundaries;
- WP-CLI recovery;
- snapshot reverse-diff conflicts.

Executed Role/Capability fixtures: **0**.

## O. Multisite / Site Lifecycle — ADR-0069/0071/0075

31/31 scopes mapped. Site Lifecycle protocol has **40 fixtures / 0 executed**. Multisite certification remains **0 MS1+**.

## P. Remote service / Product License

Accepted OAuth/resource/state/HTTP/component-schema principles through ADR-0091. Open exact OAuth/OpenAPI/TUF/service runtime/idempotency/ETag/allocation/clone-transfer/offline-grace/retention evidence.

Remote privacy: **30 fixtures / 0 executed**. Product License API/service fixtures: **0**.

## Q. Accepted architecture no longer open semantically

ADRs **0035–0097** preserve accepted core semantics. Evidence can refine implementation/version facts but cannot silently redesign them.

Benchmark labels are not final DDL/runtime certification. Provider/backend state cannot replace owning WPE domain truth.

## Decision-processing rule

1. Inspect repository and authoritative evidence.
2. Resolve static semantics in ADR when evidence is sufficient.
3. Document bounded executable protocol when proof is required.
4. **Do not install, compile, migrate, benchmark, test, contact providers/services, send mail, run queues, execute crypto or transfer data before explicit owner consent.**
5. Synchronize governance after meaningful milestones.

## Next planning-only priorities

1. Admin Columns N+1/write/sort/filter operational profile.
2. Dynamic Listings authorization/pagination/cache/SSR operational profile.
3. Backup manifest/chunk/compression/hash artifact profile.
4. Product Account OAuth/TUF/service evidence protocols where static review removes ambiguity.
5. Keep P-001…P-013 executable gates intact.