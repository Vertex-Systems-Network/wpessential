# Dynamic Listings Gate E — Exact-Main Prerequisite Audit V1

Status: **PREREQUISITE AUDIT COMPLETE — LISTINGS RUNTIME NOT YET AUTHORIZED**  
Issue: #289  
Parent: #66  
Exact-main audit anchor: `a6938ea5b272f7f7a52f57f0e7c09e3c9a9169dd`

This audit revalidates the BANK_REVIEWED Surface 9 / Dynamic Listings planning contract against the executable contracts currently present on exact `main` after the bounded Admin Columns Gate D closure evidence was promoted. Planning documents are treated only as requirements; executable exact-head source wins where the two differ.

The result is intentionally fail-closed: several foundational platform seams are ready, but the shared rendering/value/component plane required by Listings is not implemented yet. Therefore a Listing-specific runtime must not invent private substitutes for those missing shared contracts.

## 1. Exact-main prerequisite matrix

| Prerequisite | State | Exact-head evidence | Gate E assessment |
| --- | --- | --- | --- |
| Query read / parameter / result consumer boundary | **READY (bounded V1)** | `frameworks/Contracts/QueryReadConsumerInterface.php`; `frameworks/Modules/Query/QueryReadConsumer.php`; Query authorized planner/executor/result/error types | Public cross-surface Query contract exists, owns validation/authorization/provider execution, exposes bounded semantic requests, hard limits and source capability description. Listings can consume this seam without owning raw provider arguments. |
| Data Source Registry / descriptor plane | **READY** | `frameworks/Contracts/DataSourceRegistryInterface.php`; `frameworks/Platform/DataSources/*` | Canonical registry contract exists. Listings should still prefer Query-owned execution and use direct Data Source binding only where separately certified by the architecture. |
| Shared Renderer core contract | **MISSING** | No Renderer contract/interface or renderer platform/module exists under current `frameworks/Contracts`, `frameworks/Platform` or `frameworks/Modules` | **Hard blocker for canonical Listing SSR.** Listings must not create a private renderer that later becomes incompatible with shared rendering ownership. |
| Dynamic Value resolver contract | **MISSING** | No executable Dynamic Value resolver/interface is present in the exact-head framework tree | **Hard blocker for typed dynamic bindings.** Listings must not directly resolve arbitrary fields/meta/provider values as a substitute. |
| Component Blueprint contract | **MISSING** | No executable Component Blueprint contract/model/registry is present in the exact-head framework tree | **Hard blocker for item-template execution.** Listings may reference a future blueprint identity in planning, but cannot implement a private builder/document model. |
| Policy / capability boundary | **READY** | `frameworks/Platform/Auth/PolicyEngine.php`, `AuthorizationRequest.php`, `PolicyDecision.php`, `ExecutionContext.php`, `frameworks/Contracts/CapabilityCheckerInterface.php` | Authenticated principal + capability authorization is executable. Presentation visibility must remain separate from authorization and Query/source owners must keep resource checks. |
| Asset Registry | **READY** | `frameworks/Platform/Assets/AssetRegistry.php`, `AssetDescriptor.php`, `AssetScope.php`, `AssetLoadStrategy.php` | Registered asset graph, dependency validation and scoped resolution exist. Listings can consume this shared registry after its renderer/component dependencies are established. |
| Definition Repository / identity / revision foundation | **PARTIAL / FOUNDATION READY** | `frameworks/Contracts/DefinitionRepositoryInterface.php`; `frameworks/Platform/Definitions/Definition.php`, `DefinitionStatus.php`, `DefinitionScope.php`, persistent/in-memory repositories/gateways | UUID, owner surface, schema version, status, revision, dependency IDs, checksum and persistence foundations exist. However the generic repository contract does not itself define the complete Listing publish/compiler/revision-compatibility workflow required by the Surface 9 planning contract. |
| Portable configuration / import contract | **MISSING AS SHARED CROSS-SURFACE SEAM** | No generic portability/import interface is present in the exact-head shared contract directory; existing module-specific import/portability implementations are not a shared Listings contract | Listing export/import must remain blocked from inventing a second canonical portability protocol. A shared or explicitly Listing-owned bounded definition portability contract must be designed after canonical definition dependencies are stable. |
| Error taxonomy / observability | **PARTIAL** | `frameworks/Platform/Observability/*`; Query has `QueryExecutionError`, planning/provider exceptions and diagnostics | Trace recording/sanitization and Query-specific error types exist. There is not yet a shared Renderer/Listing failure taxonomy covering missing blueprint/value/render dependencies, so Listing runtime error semantics are not stable enough to claim READY. |
| Multisite scope model | **READY AS EXECUTION FOUNDATION** | `frameworks/Platform/Auth/ExecutionContext.php` carries validated positive `siteId` and optional positive `networkId`; Definition scope types exist | Site/network context is explicit server-side state. Listing portability and cross-site reference remapping remain separate future work and must not allow request input to widen scope. |

## 2. Query prerequisite is materially ready

`QueryReadConsumerInterface` is the correct cross-surface boundary for the first Listing data-binding work. It explicitly states that callers provide bounded semantic references while Query remains responsible for canonical validation, authorization, provider planning and execution. It exposes no raw provider argument or mutation surface.

The V1 contract hard-bounds request size, projection count, filters, filter values, ordering, page size, offset and search length. `describe()` exposes source availability/capability metadata and `read()` is the only canonical bounded execution method. The Query module also contains an authorized planner/executor, provider compiler/executor abstractions, typed pagination, execution result/error types and a concrete `QueryReadConsumer`.

Consequences for Listings:

- Listing definitions may bind only declared Query/source references and declared public parameter mappings.
- Listings must not store raw `WP_Query` argument bags, SQL, arbitrary callbacks or provider-native query documents.
- Query remains authoritative for result selection, filtering, search, ordering, pagination semantics, source authorization and provider execution.
- A Listing layer may present controls and map declared parameters, but those controls cannot create new Query semantics.

## 3. The shared render plane is the blocking dependency

The current executable framework has no shared Renderer interface/runtime, no Dynamic Value resolver contract and no Component Blueprint contract. Those three are not optional conveniences: the reviewed Surface 9 architecture explicitly depends on them for the pipeline:

`Listing Definition → Query-owned authorized result set → typed/batched value hydration → Item Component Blueprint → Shared Renderer SSR`.

Starting `frameworks/Modules/Listings` with its own HTML renderer, value resolver or builder-shaped item document now would create exactly the duplicate ownership the planning contract forbids. It would also force a later migration once shared rendering becomes canonical.

Therefore **Gate E runtime remains blocked even though Query, Policy, Assets and Definitions foundations are available.**

## 4. Definition lifecycle assessment

The generic Definition foundation is strong enough to support future Listing identity work:

- canonical RFC 4122 UUID identity;
- stable slug/type;
- schema version and owner surface;
- status and revision;
- typed dependency UUID list;
- canonical checksum support;
- persistent and in-memory repositories.

But `DefinitionRepositoryInterface` currently exposes `save`, `get`, `byType` and `dependentsOf`; it does not itself define the complete compile/publish lifecycle, immutable historical revision lookup, compatibility fingerprints or Listing-specific dependency validation required by the reviewed Surface 9 contract.

Accordingly, Definition infrastructure is **not** the hard blocker, but Listing definition/compiler work must remain a bounded consumer of this platform foundation rather than assuming missing lifecycle semantics.

## 5. Policy, assets, observability and multisite

### Policy

`PolicyEngine` authorizes an authenticated `ExecutionContext` principal through the shared capability checker. Listings can consume this foundation, but it must preserve the stronger cross-surface rule: visibility is not authorization. Query/source resource authorization still occurs before visible rows/count truth; Listing templates or controls do not grant access.

### Assets

`AssetRegistry` provides canonical registration, dependency validation, cycle detection and ordered resolution. This is sufficient shared infrastructure for future Listing/component asset declarations. A Listing module must reference registered handles rather than enqueue arbitrary script/style channels from authored configuration.

### Observability

The platform has bounded trace recording and metadata sanitization, while Query has execution/planning error types and diagnostics. This is useful infrastructure but does not yet define canonical render/value/blueprint failure semantics. The future shared render-plane tranche should establish safe error categories before Listing SSR exposes user-facing degraded/error states.

### Multisite

`ExecutionContext` carries explicit validated `siteId` and optional `networkId`. That is an executable scope foundation, not permission to implement automatic cross-site remapping. Listing definitions must retain scope confinement and fail closed when referenced dependencies are unavailable in the current authorized scope.

## 6. Smallest dependency-safe implementation sequence

### Serialized upstream prerequisite — must happen first

**Shared render/value/component contract V1**

Recommended scope:

- a small shared Renderer consumer/contract with typed render input/context/output and explicit escaping/sanitization responsibilities;
- Dynamic Value resolver consumer contract with typed source/value evidence and no direct storage ownership;
- Component Blueprint descriptor/consumer contract using stable identity/revision/dependency references;
- safe shared failure/degraded categories for missing/unsupported render dependencies;
- contract tests proving authored configuration is not an arbitrary PHP/HTML/script execution channel;
- no Listing-specific query engine, persistence model or builder document.

This is a shared platform boundary and should be serialized because several surfaces will consume it. No Listings SSR worker should start before it is promoted.

### Parallel-safe consumers after the shared contract is promoted and Gate D shared truth is closed

Once the shared render/value/component contract is exact-head green, the following can be split into disjoint workers:

1. **Listings definition + compiler descriptor V1**  
   Own only Listing definition validation/compilation over the existing Definition foundation. Store stable Query + Blueprint references, revisions/fingerprints, layout/state configuration and registered asset handles. No Query execution and no rendering.

2. **Listings Query binding / authorized result envelope V1**  
   Consume `QueryReadConsumerInterface` only. Validate declared parameter mappings, bounded page size/offset, projection and result-schema compatibility; preserve Query errors/authorization. No template rendering, no Definition persistence ownership and no raw provider arguments.

After both are promoted, a third serialized/consumer tranche can compose **server-first Listing SSR V1** through the shared Renderer + Component Blueprint + Dynamic Value contracts. Progressive filters/load-more/infinite-scroll should remain later enhancements over the same server contract.

## 7. Work that must remain blocked

Until the shared render plane is promoted, do not implement:

- a Listing-private HTML/template renderer;
- arbitrary executable HTML/script/PHP authored templates;
- direct Field/meta/provider value reads inside Listings;
- a private Query language or raw `WP_Query`/SQL source path;
- provider/builder documents as canonical Listing storage;
- async/infinite-scroll runtime that bypasses the canonical server render and Query authorization path;
- Listing portability that guesses/remaps dependencies automatically;
- Status runtime;
- product/full-parity certification or deployment/release.

## 8. Gate E decision from this audit

**Gate E implementation readiness: BLOCKED ON SHARED RENDER/VALUE/COMPONENT CONTRACT.**

The blocking gap is architectural, not a lack of Query capability. Query, Data Source Registry, Policy, Assets and multisite execution foundations are usable now; Definitions are sufficiently established for later bounded consumer work. The missing shared Renderer + Dynamic Value + Component Blueprint contracts must be promoted first so Listings does not create duplicate system ownership.

Issue #289 may close when this exact-head evidence is promoted. The Supervisor should then use this audit to authorize the next deterministic queue slot only after Issue #288 completes Gate D shared-truth/PASS reconciliation. Gate E runtime must remain blocked until both conditions are true:

1. Gate D bounded V1 PASS is promoted in shared repository truth; and
2. the shared render/value/component prerequisite is implemented and exact-head green.

This audit makes no `RUNTIME_CERTIFIED`, `PRODUCT_PARITY_CERTIFIED`, provider-parity, Status, deployment or release claim.
