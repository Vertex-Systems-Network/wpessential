# WPEssential — Project State & Adoption Baseline

Status: **Active implementation / Phase 2 dependency-gated module development**  
Last reviewed: **2026-09-03**  
Current reconciliation anchor: **`main @ 60c837c485d0a1adbc04f22f351e53a717be01ff`**  
Current project state: **`ACTIVE_EXISTING_PROJECT`**  
Execution mode: **`IMPLEMENTATION_GATED`**  
Active lifecycle: **Phase 2 / Gate C — Surface 6 Query prerequisite reconciliation; runtime not started**

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

These PASS decisions are bounded engineering lifecycle claims. They are not `PRODUCT_PARITY_CERTIFIED`, stable-release, deployment, or provider-parity claims.

## Current Phase 2 dependency state

Canonical dependency order remains:

`Fields → Relations → Query → Admin Columns → Dynamic Listings → Status`.

Current runtime state:

- **Gate A — Fields:** PASS for the explicitly certified native V1 scope.
- **Gate B — Relations:** PASS for the explicitly certified native V1 baseline required by parent #66.
- **Gate C — Query:** prerequisite reconciliation **ACTIVE**; Query runtime is still **BLOCKED until its runtime-start gate is explicitly resolved on current main**.
- **Gate D — Admin Columns:** runtime **BLOCKED by Query runtime**.
- **Gate E — Dynamic Listings:** runtime **BLOCKED by Query plus shared renderer/data-source dependencies**.
- **Status Manager:** runtime **BLOCKED until Gates A–E are complete**.

Query prerequisite reconciliation must confirm the accepted Relations public contract, Query Bank integration, required shared Data Source/Policy/cache seams, development consent, and a reviewable first runtime slice. The current Query Implementation Contract does not list `OPTION_CONTRACT_COMPLETE` as a runtime-start prerequisite, so that lifecycle state must not be invented as a blocker without new repository evidence.

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

Current actionable queue after PR #129 contains only:

- priority 5 Supervisor-only `gate-b-exit-reconciliation-v1`, claimed on `agent/gate-b-exit-reconciliation-v1` from exact `main @ 60c837c485d0a1adbc04f22f351e53a717be01ff`;
- priority 10 Supervisor-only `query-gate-c-prerequisite-reconciliation-v1`, dependency-blocked until the Gate B exit reconciliation is promoted.

Previously promoted deterministic branches remain historical audit evidence and must not be reused or force-moved. Shared/global writes remain single-writer Supervisor/Integrator territory.

An accidental unclaimed helper ref, if observed outside the declared queue, carries no work authorization and must not be treated as a valid AUTO claim or merge candidate.

## Repository and Linear authority

GitHub repository state, machine-readable configuration, exact-head CI/test evidence, and accepted current-state documentation are canonical. Linear is a coordination mirror and may lag repository truth; it must be synchronized from accepted repository evidence rather than used to override GitHub.

## Resume authority

Current safe state: **finish serialized Gate B exit truth promotion, then execute the declared Query Gate C prerequisite reconciliation.**

Do not start Query runtime until that prerequisite reconciliation is accepted. When it is accepted, the first Query runtime tranche must be a new bounded queue slot/branch with explicit scope and exact-head verification.

Development remains milestone-gated. Production release/deployment and separately privileged destructive/live-provider actions remain **NOT GRANTED** by this checkpoint. Repository evidence overrides conversational memory.
