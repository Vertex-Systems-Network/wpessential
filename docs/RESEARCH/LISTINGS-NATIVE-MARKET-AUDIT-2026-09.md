# WPEssential Surface 9 — Dynamic Listings / Template Builder Research

Status: **Planning-only / Bank research complete / no Listings runtime implementation authorized**  
Snapshot: **2026-09-02**  
Base main SHA: `0415b2067c3882841c1359753dd34adcd0602543`  
Branch: `planning/options-bank-listings-v1`

## 1. Purpose

Normalize native WordPress and mature-market listing/template behavior into WPEssential Surface 9 semantics without copying proprietary formats and without allowing Listings to bypass Query, Data Source, Fields, Relations, Search, Renderer, Assets or Policy.

Canonical critical order remains:

`Relations → Query → Admin Columns → Listings → Status`

This document authorizes planning only. It does not authorize Listing renderer, block, shortcode, REST transition endpoint, cache, hydration, Query execution or builder runtime implementation.

## 2. Canonical ownership

Surface 9 owns reusable listing/template **presentation definitions** and collection composition.

It does not own:

- source records or provider truth;
- Field definitions/storage — Surface 3;
- Relation edges/cardinality/pivot/order — Surface 4;
- Query AST/projection/filter/sort/pagination/execution — Surface 6;
- custom table schema/row truth — Surface 7/Data Source;
- Search indexes/relevance/facets/autocomplete — Surface 34;
- placement/audience semantics — Surface 38;
- experiment assignment — Surface 39;
- geospatial truth — Surface 42;
- user favorites/collections truth — Surface 54;
- frontend Dashboard routes/shell — Surface 13;
- builder-wide Component Blueprint semantics — Surface 16/shared Blueprint;
- Renderer/Data Source/Policy/Asset Registry shared internals.

Visibility/conditional presentation never grants authorization.

## 3. Native WordPress research

Target: WordPress **7.1**, released August 19, 2026.

Relevant native semantics:

1. **Query Loop** — native collection wrapper with inherited/custom query use cases.
2. **Post Template** — repeated item-template concept.
3. **Query Pagination** — page navigation presentation.
4. **Query No Results** — explicit empty state.
5. **Block Bindings API** — dynamic value binding pattern with declared sources.
6. **Dynamic/server rendering** — server-generated markup remains a first-class WordPress model.
7. **Template parts** — reusable presentation-region composition.
8. **Interactivity API** — candidate progressive enhancement for stateful frontend behavior; not a replacement for server authorization/query truth.
9. **WP_Query / WP_User_Query / WP_Term_Query** — native query executors, but ownership belongs to Surface 6/Data Source adapters, not Listings.
10. **WordPress escaping/sanitization** — contextual escaping and safe rich-content sanitization are mandatory Renderer concerns.
11. **WordPress accessibility standards/WCAG direction** — semantic markup, keyboard/focus behavior and state announcements are product requirements.

### Native disposition

Native audit result: **14 items, 0 unresolved**.

Key decision: native Query Loop is useful compatibility evidence but cannot become the universal WPE data/query model. WPE must still bind a typed Query/Data Source contract capable of posts, users, terms, custom tables, Relations and certified provider sources.

## 4. Market research

Official documentation reviewed for:

- Crocoblock JetEngine Listings;
- Elementor Loop Grid;
- Bricks Query Loop;
- WP Grid Builder;
- FacetWP;
- Meta Box Views.

Market audit result: **4 primary providers + 2 specialist providers, 0 unresolved**.

### Repeated market semantics

#### Listing/template identity

Mature systems separate a reusable item/card/template from the collection instance that repeats it. WPE normalizes this into Listing Definition + Item Component Blueprint reference rather than copying builder document serialization.

#### Source/query binding

Providers expose posts/CPTs, terms, users, current/archive queries, related items, custom content and custom/provider query sources. WPE normalizes this as a typed Query reference or a certified direct Data Source binding that compiles to equivalent bounded Query semantics.

No raw SQL, arbitrary `WP_Query` bag or arbitrary PHP callback is accepted as authored Listing source.

#### Layout

Common patterns include grid, list, masonry, equal-height/card layouts and responsive column counts. Table semantics are retained where a tabular information relationship exists; tables are not treated as visual grids.

#### Pagination/interactions

Common modes are numbered pagination, previous/next, load more and infinite scroll. WPE baseline remains server navigation; load-more/async/infinite behavior is progressive enhancement over the same Query contract. Infinite scroll requires accessible fallback and must not trap focus or footer access.

#### Filtering/search/sorting

Market products commonly expose filters, search, facets, result counts, per-page and sorting controls. WPE normalizes only the **presentation/binding** into Listings. Query owns typed parameters/filter/sort/page semantics; Search owns indexed relevance/facet semantics.

#### Dynamic values

Market templates bind post properties, custom fields, taxonomy, user fields, relation-like values and provider data. WPE routes these through shared Dynamic Value + Field/Relation/Data Source contracts.

#### Empty/loading/degraded states

Empty/not-found and loading states are widespread. WPE adds explicit dependency-degraded state as a distinct semantic so missing Query/Search/provider/Blueprint capability is not silently reinterpreted.

## 5. Explicit market rejections / reassignment

1. **Private Listing Query engine** — rejected. Bind Surface 6.
2. **Raw SQL source** — rejected as a Query/Data Source bypass.
3. **Raw `WP_Query` argument bag** — rejected as a private query language.
4. **Arbitrary PHP source/template callback** — rejected.
5. **Executable raw HTML/script authored primitive** — rejected; sanitized rich HTML is content, not execution.
6. **Builder full document as canonical WPE Listing model** — rejected; adapters store stable references + allowed overrides.
7. **Index/facet truth inside Listings** — reassigned to Surface 34 Search.
8. **Placement rules inside Listing definition** — reassigned to Surface 38 when placement semantics are needed.
9. **Visibility as access control** — rejected; Policy independently authorizes data and actions.
10. **Meta Box-style arbitrary template CSS/JS/PHP escape hatches as parity requirement** — market evidence only, not copied into the safe canonical model.

## 6. Bank result

Five Surface 9 Bank shards contain **150 unique semantic records**:

- `listings.json` — 28 identity/source/context/source-type records;
- `listings--templates-rendering.json` — 30 template/Dynamic Value/render records;
- `listings--layout-interactions.json` — 32 layout/pagination/filter/search/interaction records;
- `listings--security-performance.json` — 30 security/accessibility/performance/cache records;
- `listings--portability-diagnostics.json` — 30 preview/revision/portability/adapter/diagnostic records.

Count was frozen after market normalization. No market-audit record inflation is permitted.

## 7. Runtime architecture accepted for future implementation planning

Existing WPE paper architecture is consistent with this research:

`Listing Definition → Compiled Listing Descriptor → Authorized Query → Visible Result Set → Item Component Blueprint → shared Renderer → optional progressive enhancement`

Required properties:

- Policy before protected pagination/count truth;
- declared parameters only;
- stable ordering and bounded page sizes;
- batched field/relation/provider hydration;
- nested-loop depth/row budgets and cycle guards;
- shared Renderer escaping/sanitization;
- dependency-derived cache safety;
- scoped registered assets;
- safe empty/error/degraded states;
- SSR meaningful initial markup;
- builder adapters that reference rather than clone canonical Listing configuration.

## 8. Dependency gates

Planning may be BANK_REVIEWED before Query runtime is complete, but Listings runtime must not outrun:

1. Relations runtime/contract gate relevant to relation traversal;
2. Surface 6 authoritative Query runtime contract and published-definition shape;
3. Admin Columns gate in the accepted critical order;
4. exact shared Renderer/Data Source/Policy/Asset/Blueprint contracts at implementation start.

Search-backed listing behavior must degrade explicitly if Surface 34 capability is absent. It must never silently reinterpret search as ordinary structured Query.

## 9. Research conclusion

Surface 9 can safely reach planning `BANK_REVIEWED` now because its semantic ownership and UX contract can be defined independently of unfinished Query implementation details. Runtime remains blocked until the dependency chain provides authoritative executable contracts.
