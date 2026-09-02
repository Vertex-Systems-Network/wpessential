# WPEssential — Project State & Adoption Baseline

Status: **Active implementation / Phase 2 dependency-gated module development**  
Last reviewed: **2026-09-03**  
Current reconciliation anchor: **`main @ cccecc08fc1247fa45472ca0ae38ca912cfe1d14`**  
Current project state: **`ACTIVE_EXISTING_PROJECT`**  
Execution mode: **`IMPLEMENTATION_GATED`**  
Active lifecycle: **Phase 2 / Gate C — Surface 6 Query bounded runtime-source development active; provider execution not yet certified**

`GOV-OWNER-CONSENT-001` remains ACTIVE and authorizes milestone-gated source implementation across the accepted 56-surface architecture. Production deployment/release, destructive live-site/customer-data operations, chargeable or irreversible provider side effects, and separately privileged release operations remain outside that grant unless explicitly authorized.

Repository/current-main machine truth and exact-head evidence override stale prose or coordination mirrors.

## Accepted prerequisite gates

- WP113–WP116 — 5,808 exact planning definitions closed.
- WP117 / ADR-0212 — Phase 0 closure audit **PASS**.
- WP118 / ADR-0213 — structural module/option/UI/system audit **PASS** after remediation.
- WP119 / ADR-0214 — Implementation Baseline / Adoption Gate **DONE / PASS**.
- WP120 / ADR-0215 — machine-enforced architecture guards **DONE / PASS**.
- WP121 shared Platform foundation — **DONE / PASS FOR MODULE HANDOFF**.
- Phase 2 Gate A / Surface 3 Fields — **PASS for the certified native V1 scope**.
- Phase 2 Gate B / Surface 4 Relations — **PASS for the certified native V1 baseline** after merged milestone PR #122 and public Query-consumer contract PR #128.
- Query Gate C prerequisite blockers identified by the September audit — **RESOLVED for bounded Query source development**: shared Data Source and shared Cache foundations are promoted, alongside the already accepted Relations public consumer contract, Policy seam, Query Bank and development consent.

These PASS decisions are bounded engineering lifecycle claims. They are not `PRODUCT_PARITY_CERTIFIED`, stable-release, deployment, or provider-parity claims.

## Current Phase 2 dependency state

Canonical dependency order remains:

`Fields → Relations → Query → Admin Columns → Dynamic Listings → Status`.

Current runtime state:

- **Gate A — Fields:** PASS for the explicitly certified native V1 scope.
- **Gate B — Relations:** PASS for the explicitly certified native V1 baseline required by parent #66.
- **Gate C — Query:** **ACTIVE**. PR #137 promoted the typed AST + structural validation foundation V1. Provider compilation/execution, Policy-authorized execution, cache behavior, canonical admin UX and full Gate C reference evidence remain incomplete.
- **Gate D — Admin Columns:** runtime **BLOCKED by incomplete Query Gate C**.
- **Gate E — Dynamic Listings:** runtime **BLOCKED by incomplete Query Gate C plus shared renderer/data-source integration requirements**.
- **Status Manager:** runtime **BLOCKED until Gates A–E are complete**.

The current Query Implementation Contract does not list `OPTION_CONTRACT_COMPLETE` as a runtime-start prerequisite, so that lifecycle state must not be invented as a blocker without new repository evidence.

## Query Gate C current truth

Parent #66 requires typed AST, provider/data-source adapters, validation + Policy boundaries, sort/filter/search/pagination, relation/field predicates, cache/diagnostics, bounded-performance safeguards, canonical admin UX and exact-head evidence.

Current bounded progress:

1. **Query Bank — PASS.** Query remains `BANK_REVIEWED / 169`.
2. **Relations public Query-consumer contract — PASS.** PR #128 provides the storage-opaque `RelationQueryConsumerInterface`; Query must not access Relations-private tables/gateways.
3. **Shared Data Source foundation — PASS.** The canonical `DataSourceRegistryInterface` / `DataSourceDescriptor` foundation is promoted and owns source identity/capabilities used by Query validation.
4. **Shared Cache foundation — PASS.** The shared cache seam required before Query execution is promoted; Query must not create a private cache engine.
5. **Shared Policy seam and development consent — PASS.** Existing `PolicyEngine` / `ExecutionContext` remain the authorization ownership boundary for future execution slices.
6. **Typed AST + structural validation V1 — PASS FOR THIS SLICE.** PR #137 source head `dc74f1151825a2c2f66ca4fbd51398df3bd059f4` was promoted to `main` as `cccecc08fc1247fa45472ca0ae38ca912cfe1d14` after exact-head PHP Quality Toolchain #264, Distributable Package #437, Platform Compatibility Matrix #544 and Architecture Guards #882 all succeeded.

PR #137 establishes a finite typed predicate grammar, source/field/operator capability validation through the canonical Data Source contract, public Relations reference validation, explicit caller-supplied structural budgets, and fail-closed rejection of unknown nodes, malformed types, raw SQL/PHP/callback/unchecked-identifier payloads and unsupported capabilities. It deliberately performs **no provider query execution** and adds no REST/admin execution path.

The next bounded Gate C slice is a native `wordpress.posts` provider compiler V1: compile only supported, already-validated Query semantics to finite public `WP_Query` argument plans without running `WP_Query`, touching `$wpdb`, accepting raw SQL/callbacks, or executing relation traversal. Policy-authorized execution remains a later distinct tranche.

## Relations Gate B exit truth

Parent #66 requires the following Gate B baseline. Current main satisfies each item for the certified native V1 scope:

1. **Canonical relation definitions/lifecycle — PASS.** Canonical Surface 4 `relation` Definitions, revision/checksum behavior, immutable relation keys, publish-time validation and lifecycle operations are merged.
2. **Cardinality + directionality — PASS.** One-to-one, one-to-many, many-to-one and many-to-many bounds plus direction/bidirectional traversal semantics are normalized and enforced on certified paths.
3. **Object-type adapters — PASS for native V1.** Post/media/term/user/comment endpoints are certified; custom-table and registered-entity/provider endpoints remain fail-closed pending owner adapters.
4. **Safe persistence and recovery — PASS.** Scoped durable edge/state storage, per-relation transaction serialization, revision CAS, rollback/uncertain-state handling, configurable tuple uniqueness and guarded policy transitions are merged.
5. **Query/Data Source integration contract — PASS.** PR #128 publishes `RelationQueryConsumerInterface` and a bounded storage-opaque Relations read adapter. Query does not need private `WpdbRelationEdgeGateway`, table names, pivot layout or raw storage knowledge.
6. **Authorization and multisite isolation — PASS for certified paths.** Edge mutation uses endpoint/resource authorization; storage/read paths are explicitly network/site scoped and fail closed on scope mismatch.
7. **Admin editing UX — PASS for native V1.** Relation definition editing and individual connection management route through shared Ability/Policy/nonce boundaries.
8. **Import/export + diagnostics — PASS for definition portability V1.** Create-safe deterministic definition portability and health/endpoint/persistence diagnostics are merged.
9. **Exact-head CI and reference workflow evidence — PASS.** PR #122 exact head `fafa756aa0eedaf445e44309a68ce71fd01d4378` passed Architecture #878, PHP Quality #256, Platform Compatibility #540, Distributable #433 and Relations Edge Persistence #32. PR #128 exact head `de6ff78339a4611f15a2dd865e4aef0ed2385965` passed Architecture #879, PHP Quality #258, Platform Compatibility #541, Distributable #434 and Relations Edge Persistence #33, including the public Query-consumer reference integration on MySQL 8.4 and MariaDB 10.11.

Gate B PASS intentionally does **not** certify every Relations Bank record or every market/provider capability. Unsupported provider/custom-table endpoints, arbitrary pivot metadata, ordering/cascade execution, cross-provider traversal and other richer semantics remain explicit owner-contract-dependent non-goals unless separately certified.

## Current product-planning / Bank truth

Machine authority is `config/product/options-bank-progress.json`:

- canonical surfaces: **56**;
- surfaces with Bank work started: **10**;
- `BANK_REVIEWED` surfaces: **9**;
- total Bank records: **1,890**;
- reviewed surfaces: **Taxonomy 71, Fields 618, Relations 144, Status 129, Query 169, Custom Tables 165, Admin Columns 214, Dynamic Listings 150, Dashboard Widgets 123**;
- CPT remains `BANK_SURFACE_SEEDED / 107`.

Query is therefore **BANK_REVIEWED / 169** and its planning Bank integration prerequisite is satisfied. Listings is **BANK_REVIEWED / 150** but remains runtime dependency-blocked.

Machine authority for the later Atomic Option lifecycle is `config/product/atomic-option-contract-progress.json`:

- capability matrix: **56/56**;
- atomic inventories: **56/56**;
- `OPTION_CONTRACT_COMPLETE`: **1 surface — Relations**;
- `UX_CONTRACT_COMPLETE`: **0**;
- full-parity `RUNTIME_CERTIFIED`: **0**;
- `PRODUCT_PARITY_CERTIFIED`: **0**.

Bank certification, bounded runtime certification, Atomic Option lifecycle, full product parity, release readiness, and production deployment are separate claims and must not be collapsed into one status.

## AUTO multi-agent / integration state

`AUTO-AGENT.md` and `config/coordination/agent-work-queue.json` remain active.

After the typed-AST/validation promotion, the completed `query-typed-ast-validation-v1` slot is historical audit evidence and must not be re-claimed. The current actionable queue contains only the bounded `query-wordpress-posts-compiler-v1` slot on deterministic branch `agent/query-wordpress-posts-compiler-v1`.

Previously promoted deterministic branches remain historical audit evidence and must not be reused or force-moved. Shared/global writes remain single-writer Supervisor/Integrator territory.

## Repository and Linear authority

GitHub repository state, machine-readable configuration, exact-head CI/test evidence, and accepted current-state documentation are canonical. Linear is a coordination mirror and may lag repository truth; it must be synchronized from accepted repository evidence rather than used to override GitHub.

## Resume authority

Current safe state: **claim and execute the bounded Query native WordPress posts compiler V1 from current main**.

Do not run provider queries, add raw SQL, expose REST/admin execution, execute Relations traversal, or start Admin Columns runtime in that compiler slice. After compiler promotion, re-evaluate the next Query execution/Policy/cache tranche from current repository evidence.

Development remains milestone-gated. Production release/deployment and separately privileged destructive/live-provider actions remain **NOT GRANTED** by this checkpoint. Repository evidence overrides conversational memory.
