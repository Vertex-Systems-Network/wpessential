# WPEssential — Open Decisions Register

Status: **Phase 0 / planning-only / no development consent**  
Last synchronized: 2026-08-28

This register contains unresolved implementation profiles/evidence only. Accepted architecture/product/security decisions are preserved in ADRs through **ADR-0100**.

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

## B. Current physical/operational baselines

- Definition D1/PT-C; D2/D3/D4 comparisons — ADR-0073/0092 / P-004.
- Relations R1/PT-D; R2/PT-E mandatory — ADR-0074/0093 / P-010.
- Query QP1 native-WP, QP2 Custom Table, QP3 Relations, QP4 remote — ADR-0086 / P-009.
- Field Storage FS1–FS6 — ADR-0087.
- Custom Tables CT1/PT-E vs CT2/PT-D, CT3 network-owned — ADR-0088.
- Settings ST1/PT-A, ST2/PT-B, ST3 inheritance — ADR-0089.
- Forms FRT1/PT-D vs FRT2/PT-E — ADR-0077.
- Chat CRT1/PT-D vs CRT2/PT-E — ADR-0077.
- Membership M1/PT-D vs M2/PT-E — ADR-0078 / P-012.
- Notification/Email NE1/PT-D vs NE2/PT-E — ADR-0079.
- Event Inbox EI1/PT-D vs EI2/PT-E — ADR-0080.
- Audit AU1/PT-D — ADR-0081.
- Workflow WF1/PT-D vs WF2/PT-E — ADR-0082 / P-011.
- JobService J1/J2/J3 — ADR-0083 / P-003.
- REST RE1 + RI1/RI2 — ADR-0094.
- Import IR1/PT-D vs IR2/PT-E — ADR-0095.
- Backup Remote Copy BR1/BR2/BR3 — ADR-0084 / P-013.
- Vault V1/PT-C vs V2 — ADR-0085 / P-005.
- User/Profile UP native-authority profile — ADR-0096.
- Role/Capability RA native-authority + anti-lockout profile — ADR-0097.
- Admin Columns **AC1 batched whole-request plan** — ADR-0098.
- Dynamic Listings **DL1 auth-aware Query + batched hydration + SSR** — ADR-0099.
- Backup artifact profile — **SHA-256 H-B1; CMP0 fallback; CMP1 gzip comparison; ZIP convenience only** — ADR-0100.

All are paper profiles. **No DDL/index/runtime benchmark has executed.**

## C. Query / Fields / Tables / Columns / Listings

- P-009 remains executable blocker for compiler/cost/cache/security/storage adapters.
- Field Storage and Custom Tables physical/index/migration evidence remain open.
- Admin Columns semantic architecture is now accepted in ADR-0098. Remaining evidence: WordPress list-table hooks, batch budgets, lazy mode, sort/filter adapters, inline/bulk write conflicts, export, third-party list compatibility, cross-site isolation.
- Dynamic Listings semantic architecture is now accepted in ADR-0099. Remaining evidence: cursor format, count/refill strategy, cache storage/TTL/invalidation, nested-list budgets, SSR/client parity, SEO/builder adapters and protected-result isolation.

Admin Columns runtime cases: **0**. Dynamic Listings runtime cases: **0**.

## D. Workflow / JobService

P-011 and P-003 remain executable blockers. Exact DDL/indexes, idempotency, waits/joins, enqueue reconciliation, leases, fairness, Action Scheduler coexistence and large-network behavior remain unverified.

Executed P-011: **0**. Executed P-003: **0**.

## E. Backup — ADR-0084/0100 / P-013

Accepted semantics now include:
- manifest-first multipart logical bundle;
- SHA-256 stored-byte integrity baseline;
- AEAD and object hash as separate evidence;
- CMP0 no-compression fallback;
- CMP1 gzip streaming first general compression comparison where runtime supports;
- ZIP convenience adapter only;
- FR1 record stream vs FR2 TAR-compatible stream comparison;
- DB1 typed rows vs DB2 controlled SQL vs DB3 hybrid comparison;
- provider multipart boundaries below WPE logical Part identity.

Open P-013 evidence:
- exact file-record/DB payload byte format;
- exact chunk sizes/compression levels;
- parser limits;
- archive-bomb/path/symlink safety;
- encrypted cross-server restore;
- crash/final-manifest windows;
- provider mappings/checksum semantics;
- exact Backup runtime DDL/indexes;
- C0–C4 provider certification.

Backup targets: **34 / 0 C-certified / 0 C3 Supported**. Archive/hash/compression/restore runtime cases: **0**.

## F. Vault

ADR-0085/P-005 remains evidence-gated: exact DDL/types, XChaCha/AAD/envelope serialization, key stability/rotation/recovery/KMS, grants, clone/lost-key/Backup/Restore, leakage scans, scale and security review.

Executed Vault evidence: **0**.

## G. Membership / Protected files

M1/PT-D vs M2/PT-E remains P-012 comparison. Protected-file PD1–PD4/PC0–PC4 semantics accepted. Billing remains **4 BE3 / 0 MB-certified**; protected delivery **0 PC1+**.

## H. Notification / Email / Event Inbox / Audit

All logical/physical paper profiles remain accepted but runtime/provider evidence is zero: Email **6 EE3 / 0 ET-certified**, Event adapters **0 I4/I5**, Audit physical/integrity evidence **0**.

## I. REST / Import

REST RE1 and Import IR1/IR2 semantics accepted. Route/idempotency/rate/cache/fuzz and Import Run/Map/Journal/crash/archive/source-adapter evidence remain **0 executed**.

## J. User/Profile — ADR-0096

WordPress remains identity/password/session/Application Password authority. Open: core email/re-auth, protected-meta coverage, session invalidation, Application Password secrecy, public-profile IDOR, Multisite and account deletion.

Executed fixtures: **0**.

## K. Role & Capability — ADR-0097

WordPress remains role/capability/Super Admin authority. Open: effective-cap/meta-cap simulation, multi-role overrides, last-recovery races, deletion/reassignment, plugin-owned roles, Multisite/Super Admin, WP-CLI recovery and snapshot conflict handling.

Executed fixtures: **0**.

## L. Multisite / Site Lifecycle

31/31 scopes mapped. 40 lifecycle fixtures documented / **0 executed**. Multisite **0 MS1+**.

## M. Remote service / Product License

Accepted through ADR-0091. Exact OAuth/OpenAPI/TUF/runtime/idempotency/ETag/allocation/clone/offline-grace/retention evidence remains open. Remote privacy **30/0**; Product License API/service **0 fixtures**.

## N. Accepted architecture no longer open semantically

ADRs **0035–0100** preserve accepted core semantics. Evidence can refine implementation/version facts but cannot silently redesign them.

## Decision-processing rule

1. Inspect repository and authoritative evidence.
2. Resolve static semantics in ADR when evidence is sufficient.
3. Document bounded executable protocol when proof is required.
4. **Do not install, compile, migrate, benchmark, test, contact providers/services, send mail, run queues, execute crypto, create archives or transfer data before explicit owner consent.**
5. Synchronize governance after meaningful milestones.

## Next planning-only priorities

1. Product Account OAuth exact client state/token lifecycle/replay evidence protocol.
2. Pro updater TUF metadata/cache/key-custody evidence protocol.
3. Dashboard Widgets + Admin Menu remaining operational evidence profiles.
4. Reset/Protector destructive/runtime evidence protocols.
5. Keep P-001…P-013 executable gates intact.