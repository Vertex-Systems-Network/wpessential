# WPEssential — Dynamic Listings SSR, Pagination & Cache Operational Profile

Status: **Phase 0 paper architecture / no renderer/runtime implementation authorized**  
Date: 2026-08-28  
Related: Dynamic Listings spec, Query AST, Component Blueprint, Policy, Membership, Multisite, ADR-0014.

## 1. Purpose

Define how a Dynamic Listing renders secure, SEO-capable, paginated data without leaking protected rows through counts/cursors/cache, creating N+1 relation reads or letting client-side filtering substitute for authorization.

## 2. Runtime pipeline

`Listing Definition → Validate/Publish → Compiled Listing Descriptor → Resolve Scope/Principal → Authorized Query plan → Candidate/visible result semantics → Batched field hydration → Component Blueprint SSR → optional client transitions`

Draft builder configuration is never interpreted directly as privileged runtime code.

## 3. Listing source model

Preferred source is published Query UUID/revision.

A direct simple Data Source listing compiles internally into an equivalent bounded Query descriptor so WPE has one filter/sort/pagination/security language.

No raw WP_Query array, SQL fragment or PHP callback is stored as listing source.

## 4. Authorization order

The Query/Data Source adapter must classify one of these strategies:

### DL-A1 — authorization pushed into source query
Preferred when Policy can be represented safely as backend predicates.

### DL-A2 — bounded candidate query + server authorization filter
Allowed only when:
- candidate set is bounded;
- pagination/count semantics are corrected truthfully;
- inaccessible rows cannot leak through IDs/counts/cursors/timing metadata beyond accepted risk;
- refill strategy cannot become unbounded.

### DL-A3 — post-query authorization unsupported for public pageable list
If correct page/count semantics cannot be preserved, publish is blocked for that context.

WPE never simply fetches page 1, removes unauthorized rows and calls the short page a complete page while still exposing unfiltered totals.

## 5. Pagination truth

Modes:
- numbered/page;
- prev/next;
- cursor/keyset where provider supports stable order;
- load more;
- infinite scroll with accessible load-more fallback.

Required properties:
- bounded page size;
- stable deterministic ordering;
- unique tie-breaker for cursor mode;
- cursor bound to listing/query revision + normalized filters + scope + authorization context/generation where needed;
- invalid/stale cursor fails safely;
- no unbounded `all` mode on public runtime.

## 6. Total-count semantics

Listing declares one count mode:
- exact authorized total;
- bounded/estimated safe total;
- no total;
- provider-native total only when it represents authorized result semantics.

If authorization cannot be pushed into count query, WPE does not display source-wide protected total as if it were visible total.

UI can show `More results available` instead of a false/exposing count.

## 7. Protected pagination leakage

Potential leak channels:
- total count;
- page count;
- cursor progression;
- predictable missing positions;
- filter facet counts;
- cache keys/ETags;
- error differences.

Every protected listing must specify what metadata is safe to expose.

Membership/access revocation invalidates or versions any cached allow/result path according to principal access generation.

## 8. SSR baseline

Initial page uses server rendering by default when the hosting/integration context supports it.

Reasons:
- SEO/indexable public content;
- no blank JS-dependent initial state;
- consistent Policy before output;
- accessible form/pagination fallback;
- deterministic first paint.

SSR output uses Component Blueprint renderer; builder-specific adapters embed the Listing rather than reimplementing it.

## 9. Client transitions

Client enhancement can handle filters/load-more/infinite scroll, but server remains source of truth.

Client request must carry only declared filter/sort/page parameters. It cannot provide:
- arbitrary field names;
- raw AST;
- target site IDs outside route contract;
- authorization decisions;
- unrestricted projection fields.

Transition response uses same compiled Listing/Query/Policy contract as SSR.

## 10. Batched item hydration

For visible result IDs, renderer builds a dependency plan:
- requested fields;
- media;
- relations;
- computed values;
- nested listing references.

Hydration groups reads by adapter and batch. Per-item relation/query/media remote fetch loops are rejected where batch path exists.

Nested Listings have explicit maximum depth and result budget. Nested child listing cannot trigger unbounded query-per-parent behavior.

## 11. Cache classes

### LC0 — cache off
Default for personalized/high-risk listings.

### LC1 — request-local
Safe request memoization only.

### LC2 — public shared persistent
Only for genuinely public deterministic results with known invalidation.

### LC3 — scoped authenticated persistent
Requires cache key dimensions for principal/access group/generation/site/network/locale/query params and a proven invalidation model.

### LC4 — stale-while-revalidate
Only if stale visibility is acceptable. Never use SWR where access revocation must fail closed immediately.

## 12. Cache key minimum dimensions

Where relevant:
- Listing published revision;
- Query published revision/generation;
- normalized parameters/filter/sort;
- page/cursor;
- site/network scope;
- locale;
- principal/access-generation or audience segment;
- dependent Data Source generation;
- renderer/Blueprint revision when output HTML cached.

Never share an authenticated protected-result cache under a public key.

## 13. Cache invalidation

Sources emit/declare generation tags for:
- entity create/update/delete;
- relation attach/detach/pivot/order;
- field value changes;
- Membership/Policy access generation;
- Listing/Query/Blueprint publish;
- relevant taxonomy/meta changes.

If adapter cannot provide correctness-safe invalidation, persistent caching for that dependency is disabled or TTL-limited according to accepted semantics.

## 14. Filters and facets

Each control maps to a declared Query parameter.

Facet count rules:
- must respect same authorization/scope as listing;
- expensive counts can be disabled/approximate only when labeled truthfully;
- option values are allowlisted/typed;
- no arbitrary database field exposure;
- dependent filters cannot generate an unbounded query cascade.

## 15. Sorting

Only declared Query sort keys.

Random sorting:
- disabled for large/public datasets by default;
- incompatible with stable cursor pagination unless provider has a deterministic seeded strategy explicitly certified;
- no `ORDER BY RAND()` convenience default at scale.

## 16. Search

Search is a Query parameter with:
- min/max input length;
- allowed fields/mode;
- wildcard behavior;
- cost budget;
- optional debounce client-side;
- server submit fallback.

Search cannot bypass Policy or select hidden fields.

## 17. Empty/error/loading states

Published Listing must have safe defaults for:
- empty authorized result;
- validation error;
- dependency unavailable;
- permission denied/concealed;
- rate/cost budget exceeded;
- client transition failure.

Do not reveal `3 records hidden because you lack permission` unless product policy explicitly permits that disclosure.

## 18. SEO/canonical behavior

WPE does not hardcode one SEO plugin policy.

Listing exposes integration metadata:
- canonical route/page reference;
- pagination mode;
- filter parameter indexability policy;
- public/private classification;
- structured data component only through validated schema renderer.

Authenticated/member-only listings default no public indexing assumptions.

## 19. Actions inside items

Buttons can:
- navigate;
- invoke registered Ability through secure handler;
- open detail/dashboard route.

A displayed action is re-authorized at invocation time. Visibility condition is not authorization.

Mutation action must preserve Ability idempotency/re-auth/confirmation rules.

## 20. Multisite

- Listing scope resolved server-side from route/context/definition;
- request `site_id` is never trusted to widen scope;
- network aggregate Listing requires explicit network-capable Query/Data Source;
- cache keys include scope;
- cross-site result links carry safe site-aware routing rather than ambiguous numeric IDs.

## 21. Adapter degradation

If builder adapter missing, core shortcode/block/dashboard renderer can still render by UUID where supported.

If a Data Source loses required Query capability:
- published descriptor becomes degraded;
- do not silently reinterpret unsupported filter/sort;
- diagnostics names dependency/version issue.

## 22. Future evidence — NOT AUTHORIZED

After consent, fixtures include:
- public 10k/100k rows;
- member/public mixed visibility;
- revoke during cached session;
- exact/no-total modes;
- offset vs cursor;
- stable tie-breaker/delete-between-pages;
- filter/facet count authorization;
- 1/2/3-level nested listings;
- high-degree relation hydration;
- SSR vs client transition parity;
- cache cross-user/cross-site attacks;
- Builder adapters;
- infinite scroll keyboard/screen-reader fallback;
- SEO filtered URL cases.

Metrics: DB queries, batch calls, p50/p95 render/transition latency, cache hit ratio, memory, rows examined and authorization refill work.

## 23. Paper recommendation

Accept **DL1 — authorization-aware Query + batched hydration + Component Blueprint SSR** as the first operational baseline.

Persistent cache is opt-in by proof class, not default. Protected pagination/count metadata must be truthful and non-leaking.

## 24. Development gate

No Listing renderer, block/shortcode, REST transition endpoint, cache, query, hydration, SEO integration or benchmark is authorized before explicit owner consent under ADR-0014.