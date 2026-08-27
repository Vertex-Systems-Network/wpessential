# WPEssential — Dynamic Listing Renderer, Query & Cache Runtime

Status: **Phase 0 paper architecture / no implementation authorized**  
Related: Dynamic Listings exhaustive spec, Query AST, Component Blueprint ADR-0035, Policy, builder adapters.

## 1. Architecture

Canonical path:

**Listing View Definition → Compiled Listing Descriptor → Authorized Query Execution → Visible Result Set → Item Component Blueprint → Server Render → Optional Progressive Enhancement**

Listing does not own query semantics or duplicate item-template logic.

## 2. Definition split

### Item Template
A Component Blueprint expecting a declared result/entity context.

### Listing View
Defines:
- Query reference + parameter mapping;
- Item Template/Blueprint;
- collection layout;
- pagination;
- sort/filter exposure;
- loading/empty/error states;
- access Policy;
- cache policy;
- SEO/URL behavior;
- embed/adapter behavior.

### Filter definitions
Typed public/admin parameter controls bound to declared Query parameters.

## 3. Publish-time compile

Compiled Listing descriptor contains:
- Listing/revision UUID;
- Query revision/reference + parameter schema;
- result schema fingerprint;
- Item Blueprint/revision policy;
- layout descriptor;
- pagination mode/limits;
- exposed sort/filter parameter allowlist;
- URL serialization rules;
- access/cache classification;
- component/asset dependencies;
- enhancement mode;
- health/compatibility fingerprint.

Incompatible Query result schema vs Item Blueprint blocks publish or marks explicit degraded draft; it never silently binds nearest field names.

## 4. Request pipeline

1. resolve Listing descriptor;
2. authorize Listing execution/view;
3. parse only declared public parameters;
4. validate/normalize parameter types/ranges;
5. compile/execute Query under source/resource Policy;
6. obtain **authorized visible result set** and count/cursor semantics;
7. render each item through shared Component Blueprint renderer;
8. render collection/pagination/filter states;
9. attach scoped assets/enhancement state;
10. return SSR markup plus optional interaction metadata.

## 5. Authorization before pagination truth

If actor cannot view some records, policy filtering must occur before final pagination/count whenever provider can enforce it.

Do not:
- fetch 20 protected records;
- hide unauthorized 15 in HTML/JS;
- still report total/page counts as if all were visible.

Provider unable to push authorization into query needs a defined bounded post-filter/requery strategy or listing is unsupported for secure paginated use.

## 6. Parameter model

Public/listing parameters reference Query-declared inputs only.

Sources:
- static;
- block/shortcode/builder attributes;
- route context;
- current entity;
- current user safe context;
- URL filter state;
- registered resolver.

No arbitrary `orderby`, meta key, SQL clause or Data Source property from unvalidated public input.

## 7. URL state

Every public filter/sort/page parameter has:
- namespaced key;
- type;
- canonical encoding;
- allowed values/range;
- default omission behavior;
- multiple/list encoding;
- privacy/URL-safe classification.

Canonical URL serialization is deterministic so back/forward/cache/SEO behavior can be tested.

Sensitive internal values never enter URL.

## 8. Pagination

Baseline: standard server page/cursor navigation.

Enhanced modes (Load More, async pagination, infinite scroll) use same server query contract.

Rules:
- page sizes bounded;
- public page number/cursor validated;
- unique parameter namespace for multiple listings;
- enhanced navigation preserves direct-link/fallback where feasible;
- infinite scroll off by default and must not trap footer/focus;
- cursor provider does not fake random page-number semantics if provider cannot support them.

## 9. Filters

Filters compile to Query parameter values; they do not create ad-hoc SQL.

Filter UI can be:
- server submit;
- progressively enhanced async submit;
- auto/debounced only when cost permits.

Active-filter chips/reset derive from canonical typed state.

## 10. Facet counts

Facet/count computation is optional separate capability.

Counts must obey same access/filter context.

Expensive counts may require:
- provider-native aggregate;
- cached aggregate;
- future index/projection service.

No synchronous N+1 count per filter option by default.

## 11. Query result cache

Cache key candidate includes:
- Query revision;
- Data Source/provider version/generation;
- normalized parameters;
- pagination/cursor;
- sort/filter;
- locale where relevant;
- access/principal generation if result set differs by user/policy.

Public shared cache only for truly public identical result set.

## 12. Item render cache

Candidate for stable public content.

Key includes:
- Blueprint revision;
- entity stable ID + revision/generation;
- relevant settings/relation generations;
- locale;
- static instance config.

If item contains current-user/Membership/private binding, shared public item cache is disabled or split into public shell + authorized dynamic regions after separate evidence.

## 13. Listing fragment cache

Full rendered listing fragment may cache only if both Query result and all item components are cache-safe for same audience.

Cache safety compiler should derive **most restrictive** classification from dependencies rather than rely on a manual “cache this” toggle.

## 14. Invalidation

Potential invalidation signals:
- relevant entity create/update/delete/status;
- Query revision;
- Listing revision;
- Item Blueprint revision;
- relation update;
- field/settings update;
- policy/Membership access change where cached audience affected;
- provider-specific data generation.

Prefer generation keys/dependency-aware invalidation over global `flush all`.

Exact cache store/invalidator remains evidence-gated.

## 15. Nested listings

Nested listing receives parent item context through typed parameter mapping.

Guards:
- max nesting depth;
- cycle detection;
- max child rows;
- batch/preload related data when provider supports;
- query-count diagnostic.

Unbounded `one query per parent row` is an N+1 defect, not accepted default behavior.

## 16. Server-first rendering

Initial meaningful markup is SSR for normal content/SEO/accessibility.

Progressive enhancement may add:
- async filters/pagination;
- Load More;
- state/history updates;
- loading announcements.

Client failure should preserve workable server navigation where mode claims progressive enhancement.

No React app required just to print a Listing.

## 17. Interactivity adapter

WordPress Interactivity API is a candidate for portable lightweight enhanced behavior after compatibility evidence.

If selected:
- WPE namespace state/actions;
- server render remains canonical;
- no duplicated business authorization in client;
- directives processed at correct boundary;
- builder adapters do not ship competing copies/runtime without need.

## 18. Builder embeds

Gutenberg/Elementor/Bricks/WPBakery/VC adapters store primarily:
- Listing UUID;
- allowed instance parameter overrides;
- safe wrapper/presentation overrides.

Central Listing query/template configuration stays in WPE definition.

Builder document is not another copy of full Listing schema.

## 19. Shortcode

Shortcode supports only declared parameters.

Unknown attributes rejected/ignored by explicit policy; no arbitrary query args.

Same server renderer as block/builder/dashboard.

## 20. Empty/error/loading states

All are Component Blueprint/registered safe render states.

Error categories:
- invalid parameter;
- no permission;
- query invalid;
- provider unavailable/timeout/rate limit;
- missing template/component;
- unsupported schema/dependency.

Frontend never exposes SQL/stack/provider secret.

## 21. SEO

Indexable content listing should have:
- meaningful SSR HTML;
- semantic links;
- stable paginated URLs where provider/page policy supports;
- no dependence on infinite-scroll-only discovery.

Filtered combinations do not automatically become indexable; canonical/noindex remains page/SEO policy.

Private/authenticated listings default non-indexable.

## 22. Performance budgets

Per Listing publish/diagnostics can estimate:
- Query cost class;
- item count;
- nested query depth;
- number of dynamic bindings;
- relation expansions;
- cache classification;
- initial HTML/asset size.

Expensive public Query + no cache + unbounded nested data can block publish or require explicit high-risk override once thresholds are evidence-based.

## 23. Observability

Safe diagnostics:
- Listing/Query/Blueprint revisions;
- normalized parameter summary;
- execution duration;
- row count/page;
- query/cache hit classification;
- item render time;
- nested query counts;
- provider error category;
- correlation ID.

No sensitive row data in generic performance log.

## 24. Future executable evidence — NOT AUTHORIZED

After explicit consent:
- WP_Query/custom-table Query compiler integration;
- private result pagination correctness;
- cache/principal isolation;
- filter URL/back-forward behavior;
- Load More/infinite accessibility;
- nested relation N+1 fixtures;
- Interactivity API comparison;
- builder embeds;
- 10k/100k/large result datasets;
- multi-listing page parameter collisions;
- SEO/no-JS fallback.

## Paper recommendation

Accept server-first **Compiled Listing → Authorized Query → Visible Result Set → Component Blueprint Renderer**, with cache safety derived from dependencies and progressive enhancement layered on top.