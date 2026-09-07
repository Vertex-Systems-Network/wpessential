# Dynamic Listings Gate E — Post-SSR Exact-Main Audit V1

Status: **Gate E ACTIVE — bounded V1 follow-on lanes identified**  
Exact executable base: `main@a103f0eba825843402cda1dd01777b55259fd1ad`  
Parent: #66  
Audit issue: #300

## Executive decision

PR #308 and PR #309 promote a bounded server-first Listing render path and canonical public filter/search/offset state, but Gate E is **not PASS**. Exact source still has one correctness prerequisite and several closure gaps that can be developed safely in parallel.

Four path-disjoint workers are dependency-valid now:

1. #302 — explicit compiled render-binding plan V1 (`Definition/**`)
2. #310 — portability dependency manifest V1 (`Portability/**`)
3. #311 — multisite scope confinement V1 (`Scope/**`)
4. #312 — semantic/accessibility presentation V1 (`Presentation/**`)

#313 is intentionally BLOCKED on #302 because it must update the already-promoted `Rendering/**` path to consume the compiled binding plan and `DynamicValueResolverInterface`. #314 reference-application evidence is blocked until the composed runtime is coherent.

## Exact-main evidence classification

### READY — bounded foundations

- **Published Listing definition/compiler foundation:** `frameworks/Modules/Listings/Definition/ListingDefinitionCompiler.php` compiles Surface 9 Published definitions, exact Blueprint id/revision, list/grid layout and registered asset handles into `ListingCompiledDescriptor`.
- **Authorized Query consumer seam:** `frameworks/Modules/Listings/QueryBinding/ListingQueryBinding.php`, `ListingQueryReader.php` and `ListingQueryResultEnvelope.php` constrain source/projection/filter/search/order/page/offset semantics to `QueryReadConsumerInterface`, validate source capability/schema and fail closed on result drift.
- **Server-first composed SSR baseline:** `frameworks/Modules/Listings/Rendering/ListingServerRenderer.php` consumes the Query reader, exact Component Blueprint registry and shared `RendererInterface`, with deterministic list/grid wrappers, empty state, bounded typed values and safe failure output.
- **Canonical public state baseline:** `frameworks/Modules/Listings/State/ListingPublicStateCodec.php` provides namespaced declared filter/search/offset state, deterministic RFC3986 serialization and Query-owned request-size bounds.
- **Site/network execution authority exists:** `frameworks/Platform/Auth/ExecutionContext.php` already carries authoritative positive `siteId` and optional `networkId`; Listings must consume it rather than invent public scope selectors.
- **Definition revision authority exists:** `frameworks/Contracts/DefinitionRepositoryInterface.php` remains canonical persistence/revision ownership.

### PARTIAL — requires bounded follow-on

- **Render-value mapping:** current compiler payload keys are only query source, Blueprint, layout and assets. `ListingServerRenderer` currently relies on Query projection field names matching Blueprint binding names. This is not sufficient for the planning contract’s explicit mapping requirement and cannot support owner-resolved Dynamic Values safely. Issue #302 must compile an immutable binding plan; #313 must later consume it.
- **Semantic/accessibility presentation:** current SSR has semantic list output and an explicit empty status, but no canonical table descriptor, no explicit responsive presentation contract, no no-JS control presenter, and no bounded public error/degraded message model. #312 owns this without editing the Renderer.
- **Multisite confinement:** `ExecutionContext` is authoritative, but there is no Listings-owned scope guard/descriptor proving public scope injection cannot widen access. #311 owns only that boundary.
- **Error/degraded closure:** current SSR failure codes are safe and fail closed, but broader public state messaging must be composed later without provider/internal leakage. #312 covers bounded presentation metadata; any remaining shared runtime taxonomy is re-audited after the parallel lanes.

### MISSING — independent closure lanes

- **Portability/import-export dependency mapping:** no Listings portability implementation exists on exact main. Unknown dependencies must not be rebound by labels/names, source data must never be exported, and Definition Repository remains revision authority. #310 owns a new isolated `Portability/**` path.
- **Real WordPress reference application evidence:** current Listings coverage is focused unit/contract evidence. A final real WordPress common-path proof is still required and is deliberately blocked as #314 until #313 and relevant closure lanes promote.

### OUT OF BOUNDED V1 / still blocked

- Infinite Scroll as a required runtime path, private builder canonical documents, new Query/provider language, arbitrary SQL/WP_Query/callbacks, cache storage/invalidation engines, provider-wide parity, Status runtime, deployment/release and `PRODUCT_PARITY_CERTIFIED` claims remain outside this Gate E bounded V1 closure.

## Security and negative requirements

- Public input never chooses `site_id`/`network_id`, provider identifiers, raw fields/meta keys, SQL, callbacks or arbitrary sort/predicate semantics.
- Query remains authorization/filter/search/order/pagination owner; Listings consumes the contract only.
- Renderer remains escaping/sanitization/render owner; Listings does not echo raw values as template HTML.
- Unknown Blueprint/Query/portability dependencies fail explicitly.
- No protected totals, hidden row counts, provider payloads, stack traces, SQL or secrets may enter public state/error output.
- No per-parent read loop may be introduced where an owner batch path exists; bounded Query page size remains the item-count ceiling.

## Parallel merge plan

The four open workers are path-disjoint and may run concurrently after Supervisor queue reconciliation:

- #302 `Definition/**`
- #310 `Portability/**`
- #311 `Scope/**`
- #312 `Presentation/**`

After #302 promotes, unlock #313 `Rendering/**` to consume the binding plan + Dynamic Value resolver. After the composed runtime and relevant closure lanes promote, run #314 real WordPress reference evidence, then #316 exact-main closure audit. Gate E remains ACTIVE until that later evidence-based reconciliation.
