# WPEssential Surface 9 — Dynamic Listings / Template Builder UX Contract

Status: **Planning contract / Bank-reviewed candidate / no runtime implementation authorization**  
Date: **2026-09-02**

## 1. UX principle

The Listings UI authors **presentation configuration** over canonical WPE dependencies. It must make ownership, effective capability and degraded states visible rather than hiding them behind builder-specific convenience controls.

The editor must always distinguish:

1. **Authored configuration** — what the Listing definition stores.
2. **Effective state** — authored values after inherited defaults, capability resolution and compatibility checks.
3. **Runtime data** — current preview/result values returned by Query/Data Source under Policy.
4. **Diagnostics** — timings, cost/risk, dependency health, cache classification, schema compatibility and safe failure evidence.
5. **Provider state** — availability/version/capabilities/rate-limit or adapter health owned by external/shared providers.

Provider/runtime state is never silently persisted back as authored Listing configuration.

## 2. Listing Manager

Primary inventory columns:

- Name;
- stable Listing key/UUID;
- status/revision projection;
- source binding type: Query / compatible direct Data Source / Search-backed;
- item template/Blueprint;
- primary layout;
- dependency health;
- Used By count;
- last modified;
- preview availability.

Primary actions:

- Create Listing;
- Edit;
- Preview;
- Duplicate as new Listing definition;
- Export;
- revision/history view;
- dependency/Used By inspection.

Destructive actions show dependency impact first. Deleting or changing a Listing cannot silently modify source data, Query definitions, Relations or builder documents.

## 3. Create flow

Step 1 — Identity

- Listing name;
- optional stable human key where product contract permits;
- description/category metadata.

Step 2 — Data contract

- Query definition reference is preferred;
- direct Data Source binding is shown only when the provider exposes an equivalent bounded Query capability;
- Search-backed source is shown only when Surface 34 capability exists;
- item/result schema compatibility is evaluated before template authoring proceeds.

Step 3 — Item template

- choose existing Item Component Blueprint;
- create Listing-local composition from registered Blueprint primitives where allowed;
- choose optional header/footer/empty/loading/error/degraded components.

Step 4 — Layout and interactions

- collection layout;
- responsive columns/sizing;
- pagination mode;
- exposed Query/Search controls;
- URL-state behavior;
- progressive enhancement mode.

Step 5 — Validate / Preview / Publish

Publish is blocked for unsafe/incompatible dependencies rather than coercing them.

## 4. Listing Editor information architecture

Recommended left/editor navigation:

### A. General

- identity;
- revision/status projection;
- description/category;
- dependency summary.

### B. Data Source

Shows **references and capabilities**, not raw provider internals.

Controls:

- Source mode;
- Query reference;
- Query revision policy;
- direct Data Source reference when certified;
- Search reference when available;
- result schema summary;
- context requirements;
- source/provider health.

Must not expose:

- raw SQL;
- arbitrary `WP_Query` array;
- arbitrary table/column identifiers from user input;
- PHP callback;
- secrets/credentials.

### C. Query Binding

Read-only Query definition summary plus Listing-owned bindings:

- declared parameter list;
- map static/context/URL/control values to Query parameters;
- exposed sort keys;
- pagination capabilities;
- count/cursor capability;
- result projection/schema fingerprint;
- relation traversal requirements;
- cost/capability warning.

The Listing editor cannot add hidden Query predicates or private sort/filter logic.

### D. Template

Regions:

- Header;
- Item;
- Footer;
- Empty;
- Loading;
- Error;
- Degraded/unsupported dependency.

Each region uses Component Blueprint/registered safe composition. Header/footer are presentation regions, not page-router or global theme-template ownership.

### E. Dynamic Values

Binding browser groups values by canonical owner:

- entity/core property;
- Surface 3 Field;
- taxonomy/term;
- Surface 4 Relation value;
- Query aggregate/result metadata;
- current user safe value;
- route/context value;
- provider-exposed typed value;
- registered resolver.

Every binding shows:

- output type;
- source owner;
- context requirements;
- formatter;
- null/fallback;
- access/cache sensitivity;
- portability state.

### F. Layout

Supported authored presentation semantics include, where compatible:

- Grid;
- List;
- Table for genuinely tabular relationships;
- Masonry;
- card item spacing/gap;
- equal-height behavior where renderer supports it;
- item alignment;
- responsive column counts;
- responsive gap/spacing;
- minimum card width/auto-fit style behavior where supported;
- semantic wrapper selection.

Masonry is presentation-only and cannot change result order/query truth.

### G. Interactions

Controls are enabled from dependency capabilities, not assumed universally.

#### Pagination

- server numbered/page navigation;
- previous/next;
- cursor/next where Query provider supports it;
- Load More progressive enhancement;
- Infinite Scroll progressive enhancement.

Rules:

- bounded page size;
- stable ordering required;
- invalid/stale cursor handled safely;
- unique URL namespace for multiple Listings;
- Infinite Scroll must retain accessible Load More/server fallback and must not trap footer/focus;
- protected totals/counts only display when Query/Policy can truthfully expose them.

#### Filters

Listing authors filter **controls** bound to declared Query parameters. Supported control presentation may include select, radio, checkbox, range/date controls, active-filter chips and reset/apply actions when parameter schema supports them.

No UI control may generate ad-hoc SQL/meta keys/identifiers.

#### Sorting

Only declared Query sort keys. The editor may author labels/default presentation for those keys but cannot create hidden SQL order expressions.

#### Search

Two explicit modes:

- Query parameter search — consumes Surface 6 declared search parameter semantics;
- Search-index mode — consumes Surface 34 when available.

If a Search-backed Listing loses Surface 34 capability, state becomes `DEGRADED_DEPENDENCY`; it does not fall back silently to Query text matching.

#### Facets

Facet/index semantics belong to Surface 34. Listings owns placement/layout/state presentation only.

## 5. Conditional visibility

Conditions may hide/show presentation primitives using shared Conditional Logic.

UI must state: **Visibility is presentation, not authorization.**

Protected data must be removed/denied by Policy/Data Source/Query before rendering. A hidden element must never be the security boundary.

## 6. URLs and navigation state

For every public filter/sort/page state exposed in URLs, editor shows the effective Query-owned schema:

- namespaced key;
- type;
- allowed values/range;
- canonical encoding;
- default omission;
- list encoding;
- URL/privacy safety.

Sensitive values cannot be made URL parameters from Listings.

Multiple Listings on one page must not collide in parameter names.

## 7. Actions inside items

Listing can present:

- links/navigation;
- registered Ability actions;
- dashboard/detail route actions where host contract permits.

Authoring controls select registered actions and map typed arguments. Invocation is independently re-authorized. Conditional button visibility is not permission.

## 8. Preview contract

Preview toolbar:

- sample entity/result context;
- Query parameter overrides limited to declared parameters;
- desktop/tablet/mobile viewport;
- locale where supported;
- optional future actor/Policy simulation clearly labeled as simulation;
- dependency-degraded simulation;
- server-rendered vs enhanced state comparison when implementation exists.

Preview displays two separate panels:

### Preview result
Actual safe rendered output for the selected preview context.

### Preview diagnostics
- Listing revision;
- Query reference/revision;
- result-schema compatibility;
- item Blueprint revision;
- provider state;
- row count/page;
- query/render duration where executable evidence exists;
- cache class;
- nested query count;
- asset dependency footprint;
- safe failure category/correlation ID.

Preview must never display SQL, secrets, protected rows or provider credentials.

## 9. Dependencies / Used By

Dependency panel distinguishes:

### Requires

- Query;
- Data Source/provider;
- Item/Header/Footer/State Blueprints;
- Fields used by bindings;
- Relations traversed;
- Search index/config when search-backed;
- registered assets;
- Policy/Ability references;
- builder adapter requirements.

### Used By

Possible consumers include:

- Gutenberg/block placement;
- Frontend Dashboard component;
- Builder Widgets/adapters;
- shortcodes;
- Solution Blueprints;
- other registered compositions.

Used By is read-only dependency evidence. It does not move ownership into Listings.

## 10. Loading / empty / error / degraded states

The editor must differentiate:

- **Loading** — transition is in progress;
- **Empty** — authorized query result is genuinely empty;
- **Validation error** — authored parameter/config invalid;
- **Permission/concealed** — Policy denies access according to disclosure contract;
- **Query invalid** — Surface 6 contract invalid/unavailable;
- **Provider unavailable** — timeout/rate/capability problem;
- **Missing template/component**;
- **Unsupported result schema**;
- **Degraded dependency** — previously valid dependency is missing/incompatible.

Frontend error messages use safe categories; admin diagnostics may show stable IDs/versions but not secrets, SQL or stack traces.

## 11. Accessibility contract

The Listing editor must expose or derive:

- semantic root/list/table structure;
- accessible names for interactive controls;
- heading semantics where template contains headings;
- image alternative/decorative semantics from the binding/component contract;
- focus-visible behavior;
- keyboard-operable filters/sort/pagination/actions;
- live-region/state announcement behavior for enhanced updates;
- focus destination after Load More/async page changes;
- reduced-motion behavior;
- Infinite Scroll accessible fallback;
- table headers/scope semantics for Table layout.

An adapter cannot claim exact/high-fidelity support if it loses required accessibility behavior.

## 12. Responsive contract

Authored responsive values are presentation settings only.

The editor supports breakpoint-aware:

- columns;
- minimum item width where allowed;
- gaps/spacing;
- alignment;
- optional presentation hide/show conditions when accessible and not security-sensitive.

Responsive changes cannot alter Query result membership unless a separately declared Query parameter is intentionally bound; implicit device-based data divergence is not a layout option.

## 13. Performance UX

Warnings/diagnostics are derived from canonical dependencies:

- Query cost class;
- page/result limits;
- nested listing depth;
- relation expansions;
- number of dynamic bindings;
- batch capability;
- N+1 risk;
- cache classification;
- estimated/observed asset footprint;
- provider limits.

The UI must not imply that enabling a cache toggle makes protected output safe. Cache eligibility is derived from Query/Policy/bindings/components/provider behavior.

## 14. Caching UX

Show effective cache class and reason:

- off/personalized;
- request-local;
- public shared only when proven identical/public;
- authenticated/scoped only when principal/access dimensions and invalidation are certified;
- stale-while-revalidate only where stale visibility is acceptable.

Author controls may express preference/TTL only within allowed runtime policy; they cannot override a more restrictive effective class.

## 15. Import / export / revisions

### Export

Includes:

- Listing authored definition/revision metadata;
- layout/template references;
- Query/Search references and declared parameter mappings;
- Dynamic Value bindings;
- dependency manifest;
- provider/builder extension namespaces where portable.

Excludes:

- runtime result rows;
- secrets;
- provider credentials;
- current cache entries;
- logs;
- private builder documents unless an adapter export explicitly owns them outside canonical Listing format.

### Import

Must provide:

- dry-run/impact when available;
- reference mapping;
- missing dependency report;
- incompatible provider/adapter report;
- safe degraded or blocked result rather than silent field-name guessing.

### Revisions

Revision UI is a projection of Definition Repository lifecycle. Listing UX may compare and restore through canonical revision abilities but does not invent independent revision storage.

## 16. Multisite

Editor always shows effective site/network scope.

Rules:

- request/URL `site_id` cannot widen scope;
- network aggregate Listing requires a network-capable Query/Data Source;
- cross-site links use safe site-aware routing;
- imports remap source/site references explicitly;
- caches/diagnostics include scope dimensions where relevant.

## 17. Builder adapters

Builder UI should primarily expose:

- Listing reference;
- allowed instance parameter overrides;
- safe wrapper/presentation overrides;
- adapter fidelity/health.

Canonical Listing query/template configuration remains in Surface 9.

Fidelity states:

- Exact;
- Adapted;
- Degraded;
- Unsupported.

Missing builder adapter may fall back to the shared server renderer only where the host contract supports it; otherwise show explicit unsupported state.

## 18. Publish blockers

Publish is blocked when any applicable condition is true:

- missing required Query/Data Source;
- Query/result schema incompatible with item template;
- required Query parameter unmapped;
- public pageable protected data cannot provide safe authorization/pagination semantics;
- unbounded result/page mode;
- recursive/cyclic template/listing composition;
- nesting/result budget violation;
- missing required Blueprint/component;
- unsupported provider capability silently required by authored config;
- invalid public URL parameter exposure;
- unsafe executable source/template configuration;
- required accessibility contract cannot be represented by selected adapter/layout.

Warnings that do not block publish must be clearly distinguished from blockers.

## 19. UX acceptance state

This contract is complete for planning. Concrete visual implementation, control components and runtime behavior remain gated by the authoritative Query/Renderer/Data Source/Policy/Asset contracts at implementation time.
