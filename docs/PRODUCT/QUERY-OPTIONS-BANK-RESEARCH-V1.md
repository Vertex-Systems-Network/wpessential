# WPEssential Surface 6 — Query Builder Options Bank Research V1

Status: **Surface-local planning evidence complete / BANK_REVIEWED candidate / no Query runtime authorized**  
Snapshot: **2026-09-02**  
Base: `main @ 0415b2067c3882841c1359753dd34adcd0602543`  
Branch: `planning/options-bank-query-v1`

## 1. Work mode and boundary

This work is `COORDINATED_PARALLEL` Surface 6 planning. It seeds and reviews Query's Options Bank and derives an implementation-ready contract without implementing production Query runtime.

Runtime dependency order remains:

`Fields → Relations → Query → Admin Columns → Listings → Status`

Relations Gate B is active. Query runtime MUST NOT start until the coordinator accepts the Surface 4 public query-consumer contract and normal development consent is satisfied.

Shared/global files remain integrator-owned: progress registries, root dashboard, shared Data Source interfaces, Policy, cache engine, Composer/lockfiles, CI, global REST/Ability registration and ownership matrices.

## 2. Evidence lifecycle

Surface-local evidence supports:

`UNSEEDED → BANK_SURFACE_SEEDED → NATIVE_AUDITED → MARKET_AUDITED → BANK_REVIEWED`

Evidence:
- 166 unique Bank records across four Query-local shards;
- 0 duplicate record IDs;
- 0 duplicate option paths;
- 0 `UNREVIEWED` records;
- WordPress native audit: 30 dispositions / 0 unresolved;
- market audit: 3 primary providers + 1 specialist / 18 family mappings / 82 Bank references / 0 unresolved;
- review certificate: 166 records / 0 unresolved.

Canonical `config/product/options-bank-progress.json` is intentionally not changed here. Coordinator integration must promote Surface 6 in sequence and recompute shared truth counters.

## 3. WordPress native audit

Target: WordPress 7.1.

Official Core surfaces reviewed:
- `WP_Query`;
- `WP_Meta_Query`;
- `WP_Tax_Query`;
- `WP_Date_Query`;
- `WP_User_Query`;
- `WP_Term_Query`;
- `WP_Comment_Query`.

Key conclusions:
1. WordPress supplies mature structured query APIs for canonical objects. WPE should compile to those APIs first where semantics match.
2. Meta, taxonomy and date clauses have distinct semantics and must remain typed rather than becoming arbitrary argument maps.
3. Posts, users, terms and comments expose materially different include/exclude, search, order and pagination behavior. The Query AST normalizes product intent while providers preserve source-specific truth.
4. WordPress Core does not provide one universal cursor/keyset abstraction across those query APIs. Cursor mode is provider-capability gated.
5. Raw SQL is not a primary user-facing Query engine. Registered custom-table/provider adapters may compile typed AST to prepared/bound SQL internally.
6. Query-level search means source-native structured matching only. Search Surface 34 owns indexing, relevance pipelines, synonyms and dedicated search infrastructure.

## 4. Market audit

Primary providers:
- JetEngine Query Builder;
- Bricks Query Loop;
- Toolset Views.

Specialist:
- Meta Box MB Views.

Normalized market families:
- query sources;
- typed filtering;
- contextual/dynamic inputs;
- sorting and pagination;
- relations/custom data;
- reusable definitions, preview and safety.

Not copied:
- proprietary storage;
- undocumented internals;
- vendor-specific implementation code.

Explicitly rejected:
- arbitrary raw SQL as an authored normal Query definition;
- AI-generated executable SQL as a privileged shortcut;
- arbitrary PHP/callback execution.

JetEngine's merged-query capability is treated as evidence that composition is desirable, not proof that one universal composition engine can preserve sorting/pagination/indexing for every source combination. Provider capability must fail closed.

## 5. Canonical ownership

Surface 6 Query owns:
- typed/versioned query AST semantics;
- filtering and nested AND/OR groups;
- source-native search semantics;
- ordered single/multi sort;
- includes/excludes and IDs;
- date/time/range/existence/null predicates;
- taxonomy predicates;
- field predicates through Surface 3 references;
- relation predicates through Surface 4 public contracts;
- status predicates through owner contracts;
- pagination semantics;
- provider capability validation;
- saved/reusable Query definitions;
- contextual typed values;
- Query preview/explain/diagnostics intent;
- Query-specific execution budgets and anti-N+1 requirements.

Query MUST NOT own:
- Surface 34 search index/ranking engine;
- Relations definition/edge storage;
- Fields schema/value storage;
- Status transitions;
- Listings rendering;
- Admin Columns presentation;
- shared Data Source registry;
- Policy engine;
- shared cache engine;
- global REST or Ability registries.

## 6. Relations-dependent planning elements

The following are deliberately provisional until Relations Gate B publishes the consumer contract:
- stable relation definition reference shape;
- direction vocabulary consumed by Query;
- authorized traversal/read API;
- batched ID/read contract;
- relation `exists`, `any`, `all`, `count`;
- nested related-side predicates;
- pivot metadata and edge ordering queryability;
- relation generation/invalidation token;
- cross-site relation traversal policy;
- direct-join certification vs bounded two-phase execution.

Query must not persist physical relation table names or infer private edge schema.

## 7. Fields dependency

Query consumes Fields-owned stable field identity and logical type. Operator compatibility is derived from logical type and provider capability, never from guessed storage.

Secrets are not queryable through generic field predicates. Repeatable/structured field semantics must be provider-declared rather than flattened silently.

## 8. Performance and safety conclusions

Planning protections:
- bounded public page size;
- bounded predicate and nesting depth;
- bounded IN lists;
- bounded relation traversal;
- deterministic tie-breaker for cursor mode;
- no unbounded public "all results";
- no per-result relation/field N+1 pattern;
- no silent fallback for unsupported operators;
- static cost class before execution;
- runtime benchmark evidence required before numeric production budgets are certified;
- total-count may be disabled when expensive;
- remote adapters obey their own rate/timeout/capability contract.

## 9. Caching and invalidation

Query owns cache intent and dependency declaration only.

Shared cache infrastructure remains canonical. Cache identity must include, as applicable:
- immutable Query revision;
- normalized typed parameters;
- provider/compiler capability version;
- site/network scope;
- authorization/principal context when visibility differs;
- source generation;
- relation/policy generations;
- locale;
- projection/sort/pagination.

A cache result that cannot safely encode authorization dependencies must not be shared persistently.

## 10. Portability

Saved Query definitions use stable WPE/provider references. Local numeric IDs are not portable identity. Import performs dependency mapping for Fields, Relations, Taxonomy, Status, Tables and registered Data Sources.

Missing optional providers produce an inspectable/exportable degraded definition; semantic clauses are never silently removed.

## 11. REST, Ability and AI boundary

REST and Abilities must invoke the same Query validation, Data Source and Policy boundaries as admin/runtime calls. Registration itself is global/integrator-owned.

AI may draft typed AST or suggest changes. AI does not get a raw SQL lane, arbitrary identifiers, secret access or Policy bypass.

## 12. Integration Requirements

- **IR-QUERY-001:** coordinator promotes Surface 6 shared Options Bank progress from `UNSEEDED` through the four lifecycle stages using integrated truth; accepted record count is 166.
- **IR-QUERY-002:** coordinator recomputes shared Options Bank counters/reporting after each serialized promotion.
- **IR-QUERY-003:** coordinator confirms zero Surface 6 semantic-registry entries are needed, or adds only evidence-backed alias/effective-derivation mappings.
- **IR-QUERY-004:** Relations Gate B publishes the Query consumer seam before any relation predicate runtime implementation.
- **IR-QUERY-005:** shared Data Source, Policy and cache contracts are reused rather than forked.
- **IR-QUERY-006:** global REST/Ability registration and Composer/CI aggregation remain coordinator-owned.
- **IR-QUERY-007:** runtime benchmark/security work from the existing P-009 profile requires separate implementation authorization and executed evidence.

## 13. Runtime blocker

This planning package does not authorize Query runtime. The blocking dependency is Relations Gate B plus ordinary repository development consent. Surface-local Bank completion is not runtime completion.
