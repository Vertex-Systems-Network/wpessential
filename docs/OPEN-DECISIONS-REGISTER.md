# WPEssential — Open Decisions Register

Status: **Phase 0 / planning-only / no development consent**  
Last synchronized: 2026-08-28

This register contains unresolved implementation profiles/evidence only. Accepted architecture/product/security decisions are preserved in ADRs through **ADR-0085**.

All executable work remains blocked by ADR-0014 until explicit owner consent.

## A. Platform executable blockers

| ID | Related ADR | Remaining evidence |
|---|---|---|
| D-001 | ADR-0002 + ADR-0069 + ADR-0075 | WP/PHP/DB compatibility and Multisite activation/scope/site-lifecycle matrix — P-001 |
| D-002 | ADR-0005 | UI runtime/externalization/accessibility/RTL/bundle — P-002 |
| D-003 | ADR-0006/0059/0068/0083 | J1/J2/J3 physical mapping, Action Scheduler coexistence/backend, claims/fairness/backpressure/runners/retention/Multisite — P-003 |
| D-004 | ADR-0008/0049/0073 | Definition D1 vs D2/D3/D4 exact DDL/index/locking/migration — P-004 |
| D-005 | ADR-0009/0048/0085 | Vault V1 vs V2 exact crypto/envelope/DDL/rotation/recovery/redaction/security review — P-005 |
| D-006 | ADR-0010/0070/0072/0076 | Free↔Pro/Product License runtime/API/idempotency/ETag/allocation/clone/offline-grace — P-006 |
| D-007 | ADR-0011 | executable CI matrix — P-007 |
| D-008 | ADR-0012 | build/externalization/toolchain comparison — P-008 |

## B. Current physical benchmark baselines

Topology classes remain PT-A native site, PT-B native network/global, PT-C scoped WPE control-plane, PT-D scoped shared high-volume runtime, PT-E per-site custom runtime and PT-F external authority.

Current first profiles/comparisons:
- Definition: **D1/PT-C** first, D2/D3/D4 comparisons — ADR-0073 / P-004;
- Relations: **R1/PT-D** first, R2/PT-E mandatory, R3 exceptional — ADR-0074 / P-010;
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

All are paper profiles. **No DDL/index/database benchmark has executed.**

## C. Query / Fields / Tables blockers

- **Q-001 / P-009** — Query AST compiler security/cost budgets/cache invalidation/storage-adapter plans/network aggregation/IDOR/scale.
- Field storage adapter exact physical/index/projection/migration rules remain open.
- Custom Tables desired-schema compiler, PT-D/PT-E selection, online migration/backfill/locking/recovery remain open.
- Admin Columns/Listings depend on Query/storage evidence and N+1/cache policy.

## D. Workflow — ADR-0082 / P-011

WF1/PT-D is first future benchmark, WF2/PT-E mandatory comparison.

Open evidence:
- exact Run/Step/Wait/Approval/branch/context DDL/indexes;
- trigger idempotency and Job enqueue reconciliation;
- two-worker/duplicate Job handling;
- wait registration/event races and duplicate resume;
- concurrent approval/join;
- external unknown outcome/reconciliation;
- cancellation/compensation;
- site lifecycle/Restore;
- 100k/1M Runs, 10k/100k waits and large-network/noisy-neighbor evidence.

Executed P-011 benchmarks: **0**.

## E. JobService — ADR-0083 / P-003

J1/J2/J3 mapping is accepted for future comparison. Backend action rows never become WPE business truth.

Open evidence:
- exact Job/Attempt DDL/indexes/claims/leases;
- WPE commit ↔ backend enqueue failure recovery in both directions;
- worker crash/lease expiry after possible side effect;
- fairness/starvation/backpressure/admission;
- recurring schedule occurrence/overlap;
- site lifecycle/Restore;
- Action Scheduler 4.1.0 load order/coexistence/schema/version/API/cleanup/runner behavior;
- 100/1k/10k-site fairness and queue isolation.

Executed P-003 backend/physical evidence: **0**.

## F. Backup — ADR-0084 / P-013

BR1/PT-D first, BR2 split mandatory comparison, BR3 PT-E isolation comparison.

Open evidence:
- exact Backup Set/Part/Remote Copy/Object/Attempt DDL/indexes;
- commit-unknown reconciliation;
- manifest-last crash windows;
- provider checksum/object identity semantics;
- delete/trash/version/object-lock truth;
- only-known-good-copy prune protection;
- re-verification and alternate-copy failover;
- site lifecycle and fresh-server Restore;
- encryption/recovery integration;
- provider C0–C4 certification.

Backup targets: **34 / 0 C-certified / 0 C3 Supported**. Executed transfers/restores/benchmarks: **0**.

## G. Vault — ADR-0085 / P-005

V1/PT-C is favored first physical profile; V2 per-site + separate network Vault is mandatory comparison.

Accepted store boundaries: Secret Identity/current metadata, immutable Secret Versions, VRK Generations, VRK Key Slots and explicit network-secret Use Grants.

Open evidence:
- exact DDL/types/indexes and same-scope pointer integrity;
- XChaCha20-Poly1305/AAD/envelope serialization;
- external key and WordPress-derived HKDF stability/rotation;
- recovery/KMS slots;
- current-secret replace concurrency;
- resumable VRK rotation crash recovery;
- use-grant revoke race;
- clone/staging/lost-key/Backup/Restore;
- DB/REST/log/support/AI plaintext leakage scans;
- V1 vs V2 on 100/1k/10k sites;
- independent security review.

Executed Vault crypto/physical evidence: **0**.

## H. Membership — ADR-0078 / P-012

M1/PT-D vs M2/PT-E remains future comparison. Enrollment authoritative, Entitlements derived, Principal Access Generation provides the paper revoke/cache generation model.

Open exact DDL/cache/locking/files/provider reconciliation/refund/identity/privacy/restore evidence. Billing source profiles remain **4 BE3 / 0 MB-certified**.

## I. Notification / Email — ADR-0079

NE1/PT-D vs NE2/PT-E remains future comparison. Occurrence, recipient/read, channel delivery, transport attempt and verified provider evidence remain separate.

Open renderer/client/fan-out/unknown-outcome/provider correlation/retention/Restore evidence. Email profiles remain **6 EE3 / 0 ET-certified**.

## J. Connections / Event Inbox — ADR-0080

EI1/PT-D vs EI2/PT-E remains future comparison. Trusted endpoint/Connection establishes scope; payload IDs do not. Event Inbox dedupe does not replace consumer idempotency.

Open signature/replay/dedupe/claim/routing/retention/large-network/provider evidence. **0 I4/I5 event certifications**.

## K. Audit — ADR-0081

AU1/PT-D favored. Exact DDL/index/retention/fail-closed classes/privacy transforms/scale remain open. Local DB is not claimed tamper-proof; any hash-chain/signed/external checkpoint requires separate attacker-model/key-custody evidence.

Executed Audit physical/integrity evidence: **0**.

## L. Multisite / Site Lifecycle — ADR-0069/0071/0075

31/31 scopes mapped. Site Lifecycle protocol has **40 fixtures / 0 executed**. Multisite certification remains **0 MS1+**.

Open: provision/reactivate/delete/uninitialize, Job/Workflow/Notification/Webhook drain, scoped cleanup/retention, Vault grants, Membership revoke, Product License release, Backup recovery, clone/transfer/DR, crash/restart and 100/1k/10k-site evidence.

## M. Remote service / Product License

Accepted OAuth/resource/state/HTTP principles through ADR-0076. Open exact OAuth/OpenAPI/TUF/idempotency/ETag/allocation/clone-transfer/offline-grace/retention/runtime evidence.

Remote privacy: **30 fixtures / 0 executed**. Product License API/service fixtures: **0**.

## N. Accepted architecture no longer open semantically

ADRs **0035–0085** preserve accepted core semantics. Evidence can refine implementation/version facts but cannot silently redesign them.

Benchmark labels D1/R1/FRT1/CRT1/M1/NE1/EI1/WF1/J1/BR1/V1 are not final DDL or runtime certification. AU1 is not a tamper-proof claim. Backend/provider state cannot replace owning WPE domain truth.

## Decision-processing rule

1. Inspect repository and authoritative evidence.
2. Resolve static semantics in ADR when evidence is sufficient.
3. Document bounded executable protocol when proof is required.
4. **Do not install, compile, migrate, benchmark, test, contact providers/services, send mail, run queues, execute crypto or transfer data before explicit owner consent.**
5. Synchronize governance after meaningful milestones.

## Next planning-only priorities

1. Query P-009 storage-adapter cost/cache/security benchmark profile without execution.
2. Field storage + Custom Tables PT-D/PT-E physical/migration profiles without DDL.
3. Settings PT-A/PT-B runtime inheritance/autoload/concurrency profile.
4. Membership protected-file delivery topology and authorization/cache evidence paper protocol.
5. Product License exact OpenAPI component schemas only where static review removes ambiguity.
6. Keep P-001…P-013 executable gates intact.