# WPEssential Surface 9 — Dynamic Listings Implementation Planning Contract

Status: **Planning-only implementation contract / runtime NOT authorized**  
Date: **2026-09-02**  
Base main SHA: `0415b2067c3882841c1359753dd34adcd0602543`

## 1. Scope

This contract converts the reviewed Surface 9 Bank and UX model into a future implementation boundary. It deliberately does **not** select or implement unfinished Query runtime details.

No Listing renderer, block, shortcode, REST transition endpoint, query executor, cache store, hydration engine, builder adapter runtime or database migration is authorized by this document.

## 2. Runtime dependency gate

Critical order remains:

`Relations → Query → Admin Columns → Listings → Status`

Listings runtime work must not begin merely because Surface 9 planning is BANK_REVIEWED.

### Gate L0 — Relations dependency

Before relation-backed Listing runtime:

- Surface 4 endpoint/reference shape is authoritative;
- relation traversal/cardinality/pivot/order behavior is executable and versioned;
- batch relation-read capability is available or the Listing capability is explicitly limited;
- relation authorization/integrity behavior is known.

Listings must not read relation storage directly.

### Gate L1 — Query dependency

Before any Listing runtime implementation:

- Surface 6 published Query definition/reference contract is authoritative;
- Query parameter schema and normalization contract is authoritative;
- result projection/schema descriptor is authoritative;
- sort/filter/search-parameter semantics are authoritative;
- page/cursor/count envelope is authoritative;
- cost/budget/provider-capability reporting is authoritative;
- authorization integration semantics are known;
- Query error/degraded taxonomy is stable enough to consume.

Do not infer these details from the Phase 0 candidate AST where the final Query runtime differs.

### Gate L2 — Admin Columns order gate

The accepted cross-surface critical order requires the Admin Columns gate before Listings runtime starts. Surface 9 must rebase/reverify its shared contracts after that gate instead of bypassing the program sequence.

### Gate L3 — Shared platform exact-head gate

At implementation start re-read exact-head:

- Renderer core contract;
- Data Source registry/core contract;
- Dynamic Value resolver contract;
- Component Blueprint contract;
- Policy/capability engine;
- Asset Registry;
- Definition Repository/revision contract;
- portable configuration/import contract;
- error taxonomy/observability contract;
- multisite scope model.

Planning assumptions lose to authoritative exact-head runtime contracts.

## 3. Canonical future pipeline

The intended architecture remains:

`Listing Definition → Validate/Publish → Compiled Listing Descriptor → Resolve context/principal → Authorized Query execution → Authorized visible result set → Batched hydration → Item Component Blueprint → Shared Renderer SSR → Optional progressive enhancement`

This is a contract direction, not executable code authorization.

## 4. Authored Listing definition boundary

Surface 9 may own authored fields for:

- Listing identity metadata/reference projection;
- source binding mode/reference;
- Query parameter mapping;
- Item/Header/Footer/Empty/Loading/Error/Degraded component references;
- collection layout and responsive presentation;
- exposed filter/sort/search controls bound to declared parameters/capabilities;
- pagination presentation/mode preference within Query capability;
- URL presentation/serialization preferences within Query-owned schema;
- progressive enhancement preference;
- presentation-only conditions;
- safe action/component references;
- portability/adapter extension metadata;
- preview sample configuration.

It must not store source records, relation edges, field values, Search indexes, credentials or runtime caches as Listing definition data.

## 5. Compiled Listing descriptor — required semantics, exact shape deferred

The future compiled descriptor needs equivalent information for:

- Listing UUID/revision;
- Query/source reference and compatible revision/generation;
- declared parameter mapping;
- result-schema compatibility fingerprint;
- item/state Blueprint references/revisions;
- normalized layout descriptor;
- exposed sort/filter/search allowlist;
- pagination capability/mode/limits;
- URL-state namespace/rules;
- Policy/access/cache classification;
- assets/component dependencies;
- progressive-enhancement mode;
- provider/adapter capability fingerprint;
- health/degraded reason metadata.

Exact field names/serialization must be defined only after Query/Renderer/Data Source contracts are authoritative.

Draft editor configuration must never execute as privileged runtime instructions without validation/publish compilation.

## 6. Query binding rules

Listings consumes Surface 6; it never creates a second query language.

Forbidden runtime shortcuts:

- raw SQL stored in Listing;
- raw `WP_Query` argument arrays stored in Listing;
- arbitrary table/column identifiers from public input;
- arbitrary PHP callbacks;
- silently compiled nearest-match fields when result schema changes;
- private Listing-only filtering/sorting/pagination semantics;
- bypassing Query policy/cost limits.

A direct Data Source Listing is permitted only if the authoritative Data Source/Query contract certifies equivalent bounded structured semantics. It must not create a separate executor path with weaker Policy/limits.

## 7. Search integration

Two cases must remain distinct:

### Structured Query search parameter

Surface 6 owns the typed search parameter and provider support.

### Indexed Search listing

Surface 34 owns index/analyzer/relevance/facet/autocomplete semantics.

If a published Search-backed Listing loses Search capability:

- mark dependency degraded;
- render configured safe degraded state or fail closed as product contract requires;
- do not silently reinterpret as WP_Query/search `s` or generic text matching.

## 8. Field / Relation / custom-table/provider bindings

### Fields

Surface 3 owns field schema/storage/null/value semantics. Renderer/Dynamic Value resolver consumes typed field references. Listings must not read arbitrary meta keys because a template asked for them.

### Relations

Surface 4 owns edges, direction, cardinality, pivot metadata and relation order. Query owns relation traversal semantics. Renderer hydration must batch related data where supported.

### Custom Tables

Surface 7/Data Source owns schema and rows. Listing can only consume registered result schemas; no direct arbitrary `$wpdb` table access.

### Provider sources

Adapter declares:

- source/result schema;
- Query capabilities;
- pagination/count/cursor capabilities;
- authorization strategy;
- batch/hydration support;
- cache/invalidation evidence;
- failure/rate-limit behavior;
- version/capability fingerprint.

Unsupported semantics must degrade explicitly.

## 9. Authorization and presentation

Order is non-negotiable:

1. resolve Listing/context;
2. authorize Listing/resource access;
3. validate declared public parameters;
4. execute Query under source/resource Policy;
5. obtain authorized visible result semantics;
6. render.

Never fetch protected rows and rely on CSS/conditional visibility to hide them.

A displayed action is re-authorized when invoked. Item/button visibility is not permission.

## 10. Pagination truth

Future runtime must consume the authoritative Query pagination model and preserve:

- bounded page size;
- stable deterministic ordering;
- unique tie-breaker for cursor mode;
- truthful authorized count semantics;
- safe invalid/stale cursor handling;
- URL namespace isolation when multiple Listings coexist;
- no unbounded public `all` mode.

When safe exact totals are unavailable, UI may expose no total or a certified bounded/estimated semantic. It must not leak protected source-wide totals.

## 11. Progressive enhancement

SSR is the canonical initial meaningful render where host context supports it.

Enhancements may include:

- async filter submit;
- async page transition;
- Load More;
- Infinite Scroll;
- history/URL synchronization;
- loading announcements.

All enhancement requests go through the same compiled Listing + Query + Policy contracts as SSR.

Infinite Scroll is never the only accessible navigation path. Load-more/server fallback and focus/footer reachability are required.

WordPress Interactivity API is a candidate adapter mechanism, not a planning-time mandate.

## 12. Template / Renderer contract

Item and state templates use shared Component Blueprints/registered primitives.

Renderer requirements:

- typed render context;
- batched Dynamic Value resolution where possible;
- contextual escaping;
- sanitized rich HTML only through typed safe content;
- semantic wrappers/list/table output;
- cycle/depth guards;
- safe link/action rendering;
- registered scoped assets only.

No arbitrary executable PHP/JS primitive is introduced by Listings.

Builder-native rendering is allowed only through future certified adapter equivalence. Canonical fallback remains shared WPE renderer where host compatibility permits.

## 13. Nested listings / repeaters

Nested collection behavior requires:

- explicit maximum nesting depth;
- cycle detection;
- max child row budget;
- parent-item context mapping through declared parameters;
- batched/preloaded relation/provider reads where possible;
- query-count diagnostic.

One query/read per parent result is an N+1 defect where a batch path exists.

## 14. Loading, empty, error and degraded runtime states

Future runtime uses stable categories including:

- invalid authored/runtime parameter;
- empty authorized result;
- permission denied/concealed;
- Query invalid/unavailable;
- provider unavailable/timeout/rate limit;
- missing Blueprint/component;
- incompatible result schema;
- missing Search capability;
- budget/cost exceeded;
- client transition failure.

Generic frontend output must never expose SQL, stack trace, raw provider payload, secrets or protected hidden-row counts.

## 15. Cache planning boundary

Cache safety is derived, not granted by a Listing checkbox.

Inputs include:

- Query/source revision/generation;
- normalized parameters/page/cursor;
- Listing/Blueprint revision;
- entity/relation/field generations;
- locale;
- site/network scope;
- principal/access generation where output differs;
- provider invalidation capability.

Most restrictive dependency wins.

Potential classes remain planning concepts:

- cache off;
- request-local memoization;
- public shared persistent only with proof;
- scoped authenticated persistent only with certified isolation/invalidation;
- stale-while-revalidate only when stale authorization/content is acceptable.

Do not implement cache storage/invalidation from this planning contract.

## 16. Asset loading

Listings declares component/interaction asset dependencies by registered handles/modules.

Global Asset Registry owns:

- registration;
- dependency ordering;
- deduplication;
- version/fingerprint;
- editor/frontend scope.

Listings runtime must not ship private duplicate library copies or arbitrary remote CDN dependencies as authored Listing configuration.

## 17. Performance budgets and diagnostics

Future publish/runtime diagnostics should consume canonical evidence for:

- Query cost class;
- page/result limits;
- nested depth;
- relation expansions;
- number of dynamic bindings;
- batch capability;
- nested query/read count;
- render duration;
- row count/page;
- cache hit/classification;
- asset footprint;
- provider error category;
- correlation ID.

Exact thresholds are evidence-gated. No fabricated numeric performance limits are frozen by this contract.

Representative future tests should include 10k/100k or otherwise evidence-based datasets only after runtime authorization.

## 18. Accessibility implementation requirements

Runtime must preserve:

- semantic list/table/grid-as-layout markup decisions;
- keyboard-operable controls;
- programmatic names;
- visible focus;
- live announcements for async changes;
- stable/friendly focus movement on page/load-more transitions;
- reduced-motion support;
- no footer trap from infinite scrolling;
- table header/scope semantics;
- no-JS/server navigation where progressive enhancement claims fallback.

Accessibility loss is an adapter degradation, not a cosmetic warning.

## 19. Portability / revisions

Listing package contains authored configuration and stable dependency references only.

Import must map or explicitly report:

- Query reference;
- Search reference;
- Blueprint references;
- provider adapter requirements;
- builder adapter extensions;
- site/network scope.

Unknown dependencies do not silently bind by matching display labels/field names.

Definition Repository remains revision authority.

## 20. Multisite

Server resolves Listing scope from definition/host context.

Never trust arbitrary public `site_id` to widen data scope.

Network aggregate Listing requires explicit Query/Data Source support. Cache keys and diagnostics include scope where applicable.

## 21. Builder / Dashboard / Placement integrations

### Builder Widgets / third-party builders

Store Listing UUID + allowed instance overrides. Do not clone full Listing schema into builder documents.

### Frontend Dashboard

Dashboard owns routes/shell/navigation. It may embed a Listing component by stable reference and mapped context.

### Placement

Placement owns where/when reusable experiences are inserted. A Listing can be the rendered payload but does not own placement rules.

### Experiments

Experiment assignment/variant semantics remain Surface 39. A variant can reference Listing definitions/components without moving experiment truth into Listings.

### User Stores

Favorites/compare/recent/custom collection state remains Surface 54. Listing templates may present safe actions/values through registered abilities/data sources.

### Geo

Distance/territory/geocoding semantics remain Surface 42. Listings may bind Query/Geo-derived typed results but must not calculate private geo truth.

## 22. Definition of future runtime readiness

Surface 9 runtime may be proposed only when an integration owner can show:

- Relations gate satisfied for relation-backed behavior;
- Query runtime contract authoritative and executable;
- Admin Columns sequence gate satisfied;
- exact-head shared contracts re-reviewed;
- no shared-file ownership violations;
- proposed Listing descriptor maps cleanly to authoritative Query/Data Source/Renderer contracts;
- security/accessibility/performance test plan accepted;
- explicit implementation authorization granted.

Until then runtime status remains **BLOCKED BY DEPENDENCY GATES**, while Surface 9 planning may remain BANK_REVIEWED.

## 23. Integration Requirements

The integration owner, not Surface 9 planning agent, must:

1. promote the shared/global progress registry after exact-head review;
2. update shared dependency/progress dashboards if governance requires it;
3. reconcile this plan against the final Surface 6 Query implementation contract;
4. resolve any shared Renderer/Data Source/Policy/Asset contract changes introduced after this branch base;
5. schedule executable runtime work only after explicit authorization;
6. record any Bank drift separately instead of silently changing the reviewed 150-record count.

## 24. Planning acceptance

This document completes the implementation **contract**, not implementation. Any statement that Surface 9 planning is complete must continue to identify Listings runtime as dependency-blocked and unimplemented.
