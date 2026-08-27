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
| D-004 | ADR-0008/0049/0073/0092 | Definition D1 vs D2/D3/D4 exact DDL/index/locking/migration under fixed P-004 protocol |
| D-005 | ADR-0009/0048/0085 | Vault V1 vs V2 exact crypto/envelope/DDL/rotation/recovery/redaction/security review — P-005 |
| D-006 | ADR-0010/0070/0072/0076/0091 | Free↔Pro/Product License runtime/API/OAuth/idempotency/ETag/allocation/clone/offline-grace/service persistence — P-006 |
| D-007 | ADR-0011 | executable CI matrix — P-007 |
| D-008 | ADR-0012 | build/externalization/toolchain comparison — P-008 |

## B. Accepted first profiles / executable evidence still absent

- Definition: D1/PT-C + exact P-004 protocol ADR-0092.
- Relations: R1/PT-D vs R2/PT-E + exact P-010 protocol ADR-0093.
- Query: QP1 WordPress-native; QP2 table; QP3 Relations; QP4 remote — ADR-0086.
- Field Storage: FS1 native WP default; FS2 table; FS3 child; FS4 Relations; FS5 Vault — ADR-0087.
- Custom Tables: CT1/PT-E first; CT2/PT-D mandatory; CT3 network-owned — ADR-0088.
- Settings: ST1/PT-A, ST2/PT-B, ST3 inheritance — ADR-0089.
- Forms/Chat: FRT1/CRT1 PT-D first; PT-E mandatory — ADR-0077.
- Membership: M1/PT-D vs M2/PT-E; protected delivery PD1–PD4/PC0–PC4 — ADR-0078/0090.
- Notification/Email: NE1/PT-D vs NE2/PT-E — ADR-0079.
- Event Inbox: EI1/PT-D vs EI2/PT-E — ADR-0080.
- Audit: AU1/PT-D favored — ADR-0081.
- Workflow: WF1/PT-D vs WF2/PT-E — ADR-0082.
- Job: J1/PT-D vs J2 split/J3 control — ADR-0083.
- REST: RE1 WordPress REST + compiled descriptor; RI1/PT-D idempotency vs RI2 atomic service — ADR-0094.
- Import: IR1/PT-D vs IR2/PT-E — ADR-0095.
- Backup Remote Copy: BR1/PT-D vs BR2 split/BR3 PT-E — ADR-0084.
- Vault: V1/PT-C vs V2 per-site/network — ADR-0085.
- User Profile: UP1 native WP identity/auth; UP2 Field Storage; UP3 minimal security-action state; UE1 core-compatible email confirmation — ADR-0096.
- Role/Capability: RA1 native WP authorization + WPE Change Plan/anti-lockout/recovery; RA2 third-party compatibility — ADR-0097.

All remain paper-only. **No DDL/compiler/runtime/security benchmark executed.**

## C. Definition — P-004

Static baseline and exact evidence contract accepted. Remaining work is executable: exact DDL/types/indexes/collations, fixed DF datasets, Q1–Q10 plans, C1–C7 races, migrations, Backup/Restore, scope attacks and supported DB matrix.

Executed P-004: **0**.

## D. Relations — P-010

Static baseline and exact evidence contract accepted. Remaining work is executable: exact R topology/DDL, RF graphs, RQ1–RQ11, RC1–RC8, endpoint/pivot representations, N+1, cache/lifecycle/Backup, scope attacks and large-network comparison.

Executed P-010: **0**.

## E. Query / Field / Custom Tables / Settings

Semantics are accepted. Remaining executable evidence:
- Query compiler/plans/security/cost/cache/large-network — P-009;
- Field adapter compatibility/queryability/uniqueness/migration;
- CT1 vs CT2 DDL/locking/backfill/shadow/large-network/lifecycle;
- Settings option/autoload/stale-write/inheritance-cache/REST/clone behavior.

Executed fixtures: **0**.

## F. Membership / protected files — P-012

M1/M2 and PD/PC semantics accepted. Open exact schema/cache/locking/billing/provider/privacy/restore plus PC1+ origin isolation, signed delivery, Range/cache/CDN/path/header, public→private migration and Backup/Restore.

Billing: **4 BE3 / 0 MB-certified**. Protected delivery: **0 PC1+**.

## G. REST — ADR-0094

Open executable evidence: route/auth, schema fuzz/mass assignment, RI1 vs RI2 atomicity/crash, rate limiter backend/proxy/noisy-site, cache invalidation/leakage, CORS attacks and large-network namespace isolation.

Executed: **0**.

## H. Import/Export — ADR-0095

Open executable evidence: exact Run/Checkpoint/Map/Journal DDL, 100k/1M resume, all crash windows, duplicate Job/concurrent source, target-edit rollback conflicts, archive/SSRF safety, IR1 vs IR2 large-network behavior, Backup/Restore/retention and source-specific adapters.

Executed: **0**.

## I. User Profile — ADR-0096

Static authority boundary is accepted. Remaining executable/security evidence:
- protected-meta registry and mass-assignment attacks;
- self vs administrative object capability behavior;
- current WordPress email confirmation adapter/replay/race/expiry;
- recent-auth mechanism;
- password/session/Application Password actions and secrecy;
- public profile IDOR/privacy/exporter/eraser;
- site-scoped custom fields vs global identity;
- Multisite site removal vs network-user deletion/Super Admin.

Unauthorized credential/role/protected-meta mutation or public sensitive exposure required: **0**.

Executed User Profile security fixtures: **0**.

## J. Role & Capability — ADR-0097

Static authority/anti-lockout boundary is accepted. Remaining executable/security evidence:
- native core/custom/third-party role adapters;
- meta/object capability mapping;
- multiple roles/user overrides;
- stale Change Plan/recovery-principal simulation;
- self-lockout/last-admin scenarios;
- native mutation success + WPE metadata failure reconciliation;
- site vs network/Super Admin behavior;
- WP-CLI/recovery-mode/snapshot reverse-diff recovery;
- capability-cache revoke propagation across Profile/REST/Dashboard/Listings.

Zero-recovery-principal ordinary UI commits required: **0**. Unauthorized Super Admin/network grants required: **0**.

Executed Role/Capability fixtures: **0**.

## K. Workflow / Job / Notification / Email / Event Inbox / Audit

- Workflow P-011: exact Run/Step/Wait/Approval DDL, races, unknown outcomes, lifecycle/Restore/scale — **0**.
- Job P-003: exact Job/Attempt DDL, claims/leases, Action Scheduler, fairness/backpressure/Multisite — **0**.
- Notification/Email: renderer/fan-out/unknown outcome/provider/retention; **6 EE3 / 0 ET-certified**.
- Event Inbox: signature/replay/dedupe/claim/provider; **0 I4/I5**.
- Audit: exact DDL/retention/integrity/privacy/scale; local DB not tamper-proof; **0**.

## L. Backup / Vault

Backup P-013 remains open for exact bundle/crypto/provider/restore evidence. **34 / 0 C-certified**.

Vault P-005 remains open for exact DDL/AEAD/AAD/HKDF/key slots/rotation/grants/clone/recovery/plaintext leakage/security review. **0 executed**.

## M. Multisite / Site Lifecycle

31/31 scopes mapped. Site Lifecycle protocol: **40 / 0 executed**. Multisite: **0 MS1+**.

Open provisioning/drain/cleanup/clone/transfer/DR/Backup and 100/1k/10k-site runtime evidence, now also including REST/Import/Profile/Role interactions.

## N. Remote service / Product License

Resource/state/HTTP/component semantics accepted. Open actual OpenAPI, OAuth scopes/tokens, idempotency/ETag/races/cursors/Problem Details/privacy/offline/clone/transfer/signed entitlement/service persistence.

Remote privacy: **30 / 0 executed**. Product License API/service: **0**.

## O. Accepted architecture no longer open semantically

ADRs **0035–0097** preserve accepted core semantics. Evidence can refine implementation/version facts but cannot silently redesign them.

WordPress remains authority for core user identity/auth and role/capability/Super Admin semantics. WPE safety layers must not become a parallel weaker authority.

## Decision-processing rule

1. Inspect repository/authoritative evidence.
2. Resolve static semantics in ADR.
3. Define bounded future evidence when runtime proof is required.
4. **No installs, code, migrations, benchmarks, user/auth mutations, role changes, provider/service calls, mail, queue, crypto, protected files, imports or Backup operations before explicit owner consent.**
5. Synchronize governance.

## Next planning-only priorities

1. Admin Columns N+1/write/sort/filter renderer/source operational profile.
2. Dynamic Listings protected pagination/cache/SSR operational profile.
3. Backup archive/container exact artifact/chunk/compression/hash paper protocol.
4. Product Account OAuth/TUF/service evidence contracts where static detail reduces ambiguity.
5. Dashboard/Admin Menu/Status remaining conflict/recovery runtime profiles.
6. Keep P-001…P-013 executable gates intact.