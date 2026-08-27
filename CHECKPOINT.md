# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-08-28**  
Branch: `planning/master-architecture`  
Project state: **PARTIALLY COMPLETE — Phase 0 planning only**  
Production development authorization: **NOT GRANTED**

## Hard consent gate

Explicit owner consent is required before runtime/source/build/migration/test implementation, dependencies, executable spikes/benchmarks, queue execution, provider/API interactions, service transmission, SMTP/email sends, protected-file moves/downloads, Import execution, Backup/Restore operations or release packaging.

`continue` and planning acceptance do **not** authorize development.

Source of truth: `/DEVELOPMENT-CONSENT.md`, `AGENTS.md`, ADR-0014.

## Product milestone

- **31/31 Exhaustive product-option maturity**
- **31/31 Multisite scope behavior mapped**
- **0/31 Authorized**
- **0 surfaces Multisite runtime-certified at MS1+**
- Implemented: none
- Runtime verified: none

## Accepted architecture

Accepted decisions now extend through **ADR-0095**.

Latest milestones:
- ADR-0092 — exact Definition P-004 deterministic evidence protocol.
- ADR-0093 — exact Relations P-010 deterministic graph/concurrency evidence protocol.
- ADR-0094 — REST RE1 compiled WordPress REST operational profile; idempotency/rate/cache state separated.
- **ADR-0095 — Import IR1/PT-D vs IR2/PT-E physical/recovery profile.**

Immediately preceding static decisions:
- ADR-0086 Query compiler matrix;
- ADR-0087 Field Storage routing;
- ADR-0088 Custom Tables CT1/CT2/CT3;
- ADR-0089 Settings ST1/ST2/ST3;
- ADR-0090 Membership protected file PD/PC profile;
- ADR-0091 Product License API component schemas.

## P-004 Definition evidence — ADR-0092

Future P-004 is now constrained before execution:
- D1/D2/D3/D4 are compared on identical deterministic data;
- DF-S/DF-M/DF-L/DF-N dataset classes;
- Q1–Q10 lookup/list/dependency/Backup/lifecycle workloads;
- C1–C7 save/publish/uniqueness/lifecycle races;
- exact query-plan/index/storage/migration measurements;
- wrong-site/normalization security attacks;
- correctness/security gates precede speed/storage.

Final P-004 output must include exact DDL/types/lengths/collations/indexes, supported engine matrix, locking/retry policy and migration strategy.

**Executed P-004 cases: 0.**

## P-010 Relations evidence — ADR-0093

Future P-010 is also constrained:
- RF-S/RF-M/RF-L/RF-N/RF-H graph classes;
- RQ1–RQ11 forward/reverse/pair/order/pivot/nested/Backup/network reads;
- RC1–RC8 duplicate/cardinality/reassignment/detach/reorder/lifecycle races;
- E1/E2/E3 endpoint and PV1/PV2/PV3 pivot subtests;
- explicit N+1 rejection;
- R1 vs R2 wrong-scope/prefix/security fixtures;
- 100/1k/10k-site operational comparison.

**Executed P-010 cases: 0.**

## REST runtime — ADR-0094

Accepted paper profile:
- RE1 WordPress REST + immutable compiled descriptor first;
- RE2 gateway future comparison only;
- RI1/PT-D scoped idempotency first persistence candidate vs RI2 atomic service implementation comparison;
- one shared WPE Rate Limit Service;
- read response cache only when Policy/scope/generation dependencies are safely representable.

Security invariants:
- CORS ≠ authentication;
- response projection ≠ authorization;
- anonymous is explicit, not missing permission callback;
- request site/resource IDs are untrusted selectors;
- mass assignment is blocked by explicit request mapping;
- unknown external mutation outcome reconciles rather than blindly retries.

**REST operational fixtures executed: 0. No route registered.**

## Import runtime — ADR-0095

Accepted paper profile:
- IR1/PT-D shared scoped Run/Checkpoint/Identity Map/Change Journal first;
- IR2/PT-E per-site mandatory comparison;
- source/archive bytes in protected bounded temp storage, not DB Run blobs;
- JobService schedules execution opportunity but does not own Import truth.

Critical recovery invariant:
`target mutation committed → Map/Checkpoint not yet committed` must reconcile deterministic source identity + target fingerprint before retry; duplicate target creation is not acceptable.

Rollback remains truthful R0–R3. Full rollback cannot be reported over irreversible/external/newer conflicting changes. Restored copied active Runs require revalidation before resume.

**Import physical/recovery fixtures executed: 0. No import/source fetch/archive extraction/target mutation run.**

## Current physical/compiler profile map

- Definition: D1/PT-C first; D2/D3/D4 comparisons — protocol fixed, 0 executed.
- Relations: R1/PT-D first; R2/PT-E mandatory — protocol fixed, 0 executed.
- Query: QP1 WordPress-native first; QP2 Custom Table; QP3 Relations-assisted; QP4 remote — 0 executed.
- Field Storage: FS1 native WP default; FS2 typed table; FS3 child; FS4 Relations; FS5 Vault; FS6 derived — 0 executed.
- Custom Tables: CT1/PT-E first for site-owned; CT2/PT-D mandatory; CT3 network-owned only — 0 executed.
- Settings: ST1/PT-A site; ST2/PT-B network; ST3 inheritance — 0 executed.
- Forms: FRT1/PT-D vs FRT2/PT-E — 0 executed.
- Chat: CRT1/PT-D vs CRT2/PT-E — 0 executed.
- Membership: M1/PT-D vs M2/PT-E — 0 executed; protected file PC1+ = 0.
- Notification/Email: NE1/PT-D vs NE2/PT-E — 0 executed.
- Event Inbox: EI1/PT-D vs EI2/PT-E — 0 executed.
- Audit: AU1/PT-D favored — 0 executed.
- Workflow: WF1/PT-D vs WF2/PT-E — 0 executed.
- JobService: J1/PT-D vs J2 split/J3 control — 0 executed.
- REST: RE1 + RI1/RI2 operational profile — 0 executed.
- Import: IR1/PT-D vs IR2/PT-E — 0 executed.
- Backup Remote Copy: BR1/PT-D vs BR2 split/BR3 PT-E — 0 executed.
- Vault: V1/PT-C vs V2 per-site/network — 0 executed.

No DDL, migration, table, index, compiler, route, option write, Import, file move, cryptographic fixture or database benchmark has been executed.

## Provider/runtime state

- Membership Billing: **4 BE3 / 0 MB-certified**.
- Protected file delivery: **0 PC1+**.
- Email: **6 EE3 / 0 ET-certified**.
- Event adapters: **0 I4/I5 certified**.
- Backup: **34 targets / 0 C-certified**.
- Remote privacy: **30 fixtures / 0 executed**.
- Product License API/service: **0 fixtures**.
- Site Lifecycle: **40 fixtures / 0 executed**.
- Multisite: **0 MS1+**.

## Platform evidence blockers

P-001 compatibility/Multisite; P-002 UI; P-003 Job; P-004 Definition; P-005 Vault; P-006 Free↔Pro/Product License; P-007 CI; P-008 build; P-009 Query; P-010 Relations; P-011 Workflow; P-012 Membership; P-013 Backup remain executable blockers.

P-004 and P-010 now have accepted exact paper execution protocols; that narrows how they can be tested but does not mean evidence exists.

## Verification state

Verified planning/documentation only:
- branch `planning/master-architecture`;
- **31/31 Exhaustive / 0/31 Authorized**;
- **31/31 Multisite scopes mapped / 0 MS1+**;
- governance synchronized through **ADR-0095**;
- Definition/Relations evidence protocols + REST/Import paper profiles committed;
- all executable evidence remains zero where not separately certified;
- no implementation/build/test/provider-certification success claimed.

Not performed: dependency installation, Multisite runtime setup, lifecycle hooks, Action Scheduler bootstrap, PHP/React source, DB tables/migrations/indexes, Definition/Relations fixtures, Query compiler/cache, REST route/idempotency/rate limiter, option writes, Import/source fetch/archive extraction/target mutations, protected file moves/server rules/signed URLs/downloads, OpenAPI server/client, provider/API/webhook/SMTP calls, commerce transactions, Email sends, Backup transfer/Restore/prune, crypto/KDF/key generation, PHPUnit/Playwright, DB/performance benchmarks, release/deployment.

## Next allowed planning-only priorities

1. User Profile identity/session/change runtime security profile.
2. Role & Capability anti-lockout/Multisite/Super Admin runtime evidence profile.
3. Admin Columns + Dynamic Listings N+1/write/cache operational profiles.
4. Backup archive/container exact artifact/chunk/compression/hash paper protocol.
5. Product Account OAuth/TUF/service evidence protocols where static detail removes ambiguity.
6. Keep P-001…P-013 executable gates intact.
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
9. relevant architecture/security/module/provider docs

Repository evidence overrides conversational memory.