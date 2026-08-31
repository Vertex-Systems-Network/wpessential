# ADR-0099 — Dynamic Listings SSR, Pagination & Cache Operational Profile

Status: **Accepted architecture / runtime evidence pending**  
Date: 2026-08-28

## Decision

WPEssential Dynamic Listings uses **DL1 — authorization-aware Query + batched hydration + Component Blueprint SSR** as its first operational architecture.

Accepted invariants:
1. initial render is server-side by default where integration context supports it;
2. source uses published Query/Data Source contract, not raw SQL/PHP or builder-specific query arrays;
3. authorization must be pushed into the source query when possible;
4. bounded post-query authorization is allowed only when pagination/count/cursor semantics remain truthful and non-leaking;
5. protected totals/facet counts cannot expose source-wide data the actor cannot access;
6. page/cursor size is bounded and cursor identity includes the relevant Listing/Query/scope/access context;
7. visible-item hydration is batched across field/media/relation dependencies;
8. nested listings have depth/result/query budgets and cannot devolve into query-per-parent loops;
9. persistent cache is opt-in by cache class; protected/authenticated cache keys include access/scope generations;
10. stale-while-revalidate is prohibited where stale authorization could expose revoked content;
11. client transitions use the same compiled Listing/Query/Policy contract as SSR;
12. displayed item actions are re-authorized at invocation time.

## Why

Listings combine Query, rendering, pagination, caching, access control and third-party builder adapters. Treating them as a visual loop risks protected-row leaks, false totals, unstable pagination and N+1 behavior.

## Rejected defaults

- fetch page then hide unauthorized rows while exposing source total;
- public shared cache for personalized/member data;
- client-side filters as security;
- arbitrary request field/sort names;
- `ORDER BY RAND()` style default for large listings;
- one Relation/Query fetch per rendered item;
- infinite scroll without accessible load-more fallback;
- builder adapter owning a separate Listing schema.

## Evidence pending

Exact cursor format, count strategy, cache storage/TTL/invalidation, refill bounds, nested-list limits, SEO integrations and builder adapters require consent-gated runtime evidence.

Executed Dynamic Listings runtime/benchmark cases: **0**.

## Source

`docs/ARCHITECTURE/DYNAMIC-LISTINGS-SSR-PAGINATION-CACHE-PROFILE.md`

## Development gate

No renderer, block/shortcode, transition endpoint, Query execution, cache, hydration or benchmark is authorized before explicit owner consent under ADR-0014.