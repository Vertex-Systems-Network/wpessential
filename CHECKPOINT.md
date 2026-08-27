# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-08-28**  
Branch: `planning/master-architecture`  
Project state: **PARTIALLY COMPLETE — Phase 0 planning only**  
Production development authorization: **NOT GRANTED**

## Hard consent gate

Explicit owner consent is required before runtime/source/build/migration/test implementation, dependencies, executable spikes/benchmarks, queue execution, provider/API interactions, service transmission, SMTP/email sends, protected-file moves/downloads, Backup/Restore operations or release packaging.

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

Accepted decisions now extend through **ADR-0091**.

Latest milestones:
- ADR-0086 — Query QP1/QP2/QP3/QP4 compiler/cost/cache/security benchmark matrix.
- ADR-0087 — Field Storage FS1–FS6 physical routing profile.
- ADR-0088 — Custom Tables CT1/PT-E vs CT2/PT-D physical/migration baseline.
- ADR-0089 — Settings ST1/PT-A, ST2/PT-B, ST3 inheritance runtime profile.
- ADR-0090 — Membership Protected File PD1–PD4 + PC0–PC4 certification profile.
- **ADR-0091 — Product License field-level OpenAPI component schema profile.**

Earlier active baselines include Definition D1, Relations R1, Forms FRT1, Chat CRT1, Membership M1, Notification/Email NE1, Event Inbox EI1, Audit AU1, Workflow WF1, Job J1, Backup BR1 and Vault V1.

## Physical/compiler profile map

- Definition: D1/PT-C first; D2/D3/D4 comparisons — **0 executed**.
- Relations: R1/PT-D first; R2/PT-E mandatory; R3 exceptional — **0 executed**.
- Query: QP1 WordPress-native first; QP2 Custom Table + QP3 Relations-assisted; QP4 remote separately certified — **0 executed**.
- Field Storage: FS1 native WP default; FS2 typed table; FS3 child; FS4 Relations; FS5 Vault; FS6 derived projection — **0 executed**.
- Custom Tables: CT1/PT-E first for site-owned; CT2/PT-D mandatory; CT3 network-owned only — **0 executed**.
- Settings: ST1/PT-A site; ST2/PT-B network; ST3 inheritance; ST4 per-field comparison — **0 executed**.
- Forms: FRT1/PT-D first; FRT2/PT-E mandatory — **0 executed**.
- Chat: CRT1/PT-D first; CRT2/PT-E mandatory — **0 executed**.
- Membership: M1/PT-D first; M2/PT-E mandatory — **0 executed**.
- Protected files: PD1 correctness baseline; PD2 accelerated; PD3 object signed delivery; PC0–PC4 certification — **0 PC1+**.
- Notification/Email: NE1/PT-D first; NE2/PT-E mandatory — **0 executed**.
- Event Inbox: EI1/PT-D first; EI2/PT-E mandatory — **0 executed**.
- Audit: AU1/PT-D favored — **0 executed**.
- Workflow: WF1/PT-D first; WF2/PT-E mandatory — **0 executed**.
- JobService: J1/PT-D Jobs+Attempts first; J2 split mandatory; J3 PT-C control — **0 executed**.
- Backup Remote Copy: BR1/PT-D first; BR2 split mandatory; BR3/PT-E — **0 executed**.
- Vault: V1/PT-C favored first; V2 per-site + network Vault mandatory — **0 executed**.

No DDL, migration, table, index, compiler, cache, cryptographic fixture or database benchmark has been executed.

## Query — ADR-0086

Query AST remains one typed product contract; provider compilers remain capability-scoped.

Accepted future profiles:
- QP1 WordPress-native;
- QP2 WPE Custom Table;
- QP3 Relations-assisted/two-phase;
- QP4 remote adapter separately certified.

Performance cannot override SQL/identifier safety, field/row Policy, site scope, cache isolation or unsupported-node truth. Persistent cache is disallowed when authorization dependencies cannot be represented safely.

**P-009 executed: 0.**

## Field Storage / Custom Tables — ADR-0087/0088

Field values do not use one universal store.

- ordinary natural WP values stay native;
- typed Custom Tables are escalation for scale/constraints/Q3–Q4 queryability;
- queryable repeaters use child rows;
- relationships use Relations;
- secrets use Vault references.

Custom Tables:
- CT1/PT-E first for ordinary site-owned tables;
- CT2/PT-D mandatory large-network comparison;
- CT3 only for genuinely network-owned data.

Definition publish does not mean physical migration applied. Migration requires observed fingerprint → reviewed typed Plan → revalidation → future execution → verification.

**Field/Custom Table fixtures executed: 0.**

## Settings — ADR-0089

- ST1/PT-A grouped site document;
- ST2/PT-B grouped network document;
- ST3 `site override → network default → Definition default`;
- non-autoload default;
- stale high-risk/page-document edit conflict must be visible rather than blind last-write-wins;
- Vault plaintext never enters Settings storage/history/cache/REST.

**Settings fixtures executed: 0.**

## Membership Protected Files — ADR-0090

A protected label requires origin isolation, not merely a hidden/gated link.

Delivery profiles:
- PD1 private local + PHP stream correctness baseline;
- PD2 certified server-accelerated local;
- PD3 private object + short-lived signed delivery with truthful bearer-expiry limits;
- PD4 future provider-specific stronger revocation.

Certification PC0–PC4 covers origin bypass, current authorization, transfer/cache/Range semantics and lifecycle/recovery.

**PC1+ certified: 0. No file has been moved/downloaded/signed.**

## Product License API — ADR-0091

Paper component schemas now define:
- Account/Contract/Capacity;
- Installation/Network/Site Allocation;
- reconciliation/review/transfer;
- signed entitlement/keyset envelope;
- Problem Details/Field Error;
- cursor pagination;
- Idempotency-Key + ETag/If-Match/Retry-After/correlation semantics.

Client cannot directly set server-owned capacity/counting/lifecycle authority. Domain/blog ID remain metadata. API transport still does not replace independent signed entitlement verification.

**0 OpenAPI/server/client/API/service fixtures executed. No YAML/JSON OpenAPI artifact created.**

## Existing provider/runtime state

- Membership Billing: **4 BE3 / 0 MB-certified**.
- Protected file delivery: **0 PC1+**.
- Email: **6 EE3 / 0 ET-certified**.
- Event adapters: **0 I4/I5 certified**.
- Backup: **34 / 0 C-certified**.
- Remote privacy: **30 fixtures / 0 executed**.
- Product License API/service: **0 fixtures**.
- Site Lifecycle: **40 fixtures / 0 executed**.
- Multisite: **0 MS1+**.

## Platform evidence blockers

P-001 compatibility/Multisite; P-002 UI; P-003 Job; P-004 Definition; P-005 Vault; P-006 Free↔Pro/Product License; P-007 CI; P-008 build; P-009 Query; P-010 Relations; P-011 Workflow; P-012 Membership; P-013 Backup remain executable blockers.

## Verification state

Verified planning/documentation only:
- branch remains `planning/master-architecture`;
- **31/31 Exhaustive / 0/31 Authorized**;
- **31/31 Multisite scopes mapped / 0 MS1+**;
- governance synchronized through **ADR-0091**;
- Query/Field/Custom Table/Settings/Protected File/Product License component paper profiles committed;
- all newly described executable evidence remains zero;
- no implementation/build/test/provider-certification success claimed.

Not performed: dependency installation, Multisite runtime setup, lifecycle hooks, Action Scheduler bootstrap, PHP/React source, DB tables/migrations/indexes, Query compiler/cache, option writes, protected file moves/server rules/signed URLs/downloads, OpenAPI YAML/JSON/server/client/mock, provider/API/webhook/SMTP calls, commerce transactions, Email sends, Backup transfer/Restore/prune, crypto/KDF/key generation, PHPUnit/Playwright, DB/performance benchmarks, release/deployment.

## Next allowed planning-only priorities

1. Definition P-004 exact fixture/query-plan/locking protocol without DDL execution.
2. Relations P-010 exact endpoint/cardinality/concurrency benchmark protocol without tables.
3. REST API Builder compiled endpoint auth/rate/CORS/cache physical profile.
4. Import/Export Run + Identity Map + Journal physical topology/recovery profile.
5. User Profile + Role/Capability runtime security/anti-lockout evidence profiles.
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