# WPEssential — Dynamic Listings / Template Builder Exhaustive Specification

Status: **Phase 0 planning only — no development consent**  
Module: **Dynamic Listings / Template Builder — Pro**

This document deepens `DATA-QUERY-SPECS.md` and defines the reusable frontend/admin display layer that consumes Query Builder/Data Source results.

## Research baseline

Reviewed current WordPress Query Loop/block support/Interactivity API behavior plus current JetEngine Listing Grid and JetSmartFilters patterns.

Planning direction:

**Server-render first → progressively enhance interaction → preserve accessible URL/navigation semantics where possible.**

WPEssential does not require a full React frontend runtime merely to display a listing.

---

# 1. Core definitions

Separate definitions:

1. **Item Template** — how one result/entity is rendered.
2. **Listing View** — query + item template + collection layout + interaction/pagination/filter configuration.
3. **Reusable Partial/Component** — reusable rendering fragment.

This avoids duplicating an item-card template every time the same entity appears in a different query/layout.

---

# 2. Item Template list

Columns:

- Name
- Key
- Expected context/result schema
- Status
- Used by views
- Builder compatibility
- Health
- Updated
- Actions

Actions:

- Edit
- Preview with sample entity
- Duplicate
- Used by
- Revisions
- Export
- Disable/Archive
- Delete definition

---

# 3. Listing View list

Columns:

- Name
- Query
- Item Template
- Layout
- Pagination
- Filters
- Status
- Used by
- Health
- Updated
- Actions

---

# 4. Item template editor sections

1. General
2. Structure
3. Dynamic Bindings
4. Actions/Links
5. Conditions
6. Styles/Tokens
7. Responsive
8. Accessibility
9. Context/Permissions
10. Preview
11. Dependencies
12. Revisions
13. Export

---

# 5. Item-template identity

- name
- UUID
- machine key
- description
- status Draft default
- expected context schema

Expected context may be:

- generic Query result object
- Posts/CPT entity
- User
- Term
- Custom Table record
- Relation row
- Membership/runtime entity
- registered provider result schema

Template editor only offers fields available in selected/declared schema plus registered dynamic resolvers.

---

# 6. Template rendering model

Canonical template representation must be builder-neutral enough for WPE runtime.

Initial primitive component schema candidates:

- Container
- Stack/Flex
- Grid sub-layout
- Text
- Heading
- Rich/Safe content
- Image/Media
- Icon
- Badge
- Button/Link
- Divider
- Spacer
- List
- Key/value group
- Progress/Rating
- Conditional wrapper
- Partial include
- Nested Listing include — bounded
- registered component

Gutenberg/Elementor/Bricks adapters may consume/embed WPE definitions; they do not become canonical storage for every WPE listing.

---

# 7. Container/layout primitive options

Common:

- semantic tag: div/article/section/li/figure/header/footer/aside where valid
- display: block/flex/grid
- direction
- wrap
- justify
- align
- gap
- width/max width
- min/max height
- padding/margin through design tokens/approved values
- border/radius/shadow through design tokens
- background
- overflow
- position only bounded safe presets
- class token
- anchor ID where context permits

Avoid arbitrary raw CSS as normal control. Advanced extension styling goes through registered style schema.

---

# 8. Responsive model

Use shared WPE design tokens and accepted WordPress responsive strategy.

Controls may support:

- desktop/base
- tablet
- mobile

Only properties approved as responsive are overrideable.

Do not hardwire dozens of custom breakpoints into each component.

WordPress/theme responsive values should be reused where practical.

---

# 9. Dynamic value binding

A component property can bind to:

- current result field
- custom field UUID
- relation
- computed Query result field
- current user/context
- site setting
- renderer token
- registered resolver

Binding defines:

- source
- path/field
- expected type
- fallback
- formatter
- missing-value behavior

Missing data never throws raw exception into frontend.

---

# 10. Text/heading component

Options:

- content static/dynamic
- semantic heading level for Heading
- prefix/suffix
- truncate
- line clamp where accessibility/readability permit
- typography design tokens
- alignment
- safe inline link behavior

Do not choose heading level purely for visual size; visual style and semantic level are separate.

---

# 11. Image/media component

Source:

- attachment reference
- dynamic image field
- featured image
- user avatar
- approved remote media URL

Options:

- image size/rendition
- responsive `srcset` use where WordPress media supports
- alt source
- decorative mode
- aspect ratio
- object fit/position
- lazy loading policy
- link
- fallback media

Alt text cannot be silently derived from filename when meaningful content alt is required; editor offers explicit/static/dynamic source.

---

# 12. Link/button component

Destination:

- entity permalink
- edit/account route when authorized
- dynamic URL field
- relation entity
- dashboard route
- registered action
- approved URL template

Options:

- label
- target
- rel
- icon
- disabled state
- confirmation for destructive action

A visually hidden button does not protect an unauthorized action; action Policy is server-side.

---

# 13. Dynamic action component

Potential actions:

- navigate
- open modal/details
- favorite/store adapter later
- form action
- relation attach/detach
- workflow ability
- membership action where permitted

Each action binds to an Ability/registered action with typed input mapping.

No arbitrary JS snippet action.

---

# 14. Conditional visibility

Uses shared Conditions Engine.

Conditions over:

- field values
- result schema
- current user
- role/capability
- membership/entitlement
- relation/query
- date/time
- device preference only when semantically justified

Modes:

- show when true
- hide when true

Fallback:

- render nothing
- render alternate partial/component

Server must enforce any sensitive visibility decision that protects data; client-only hiding is presentation, not authorization.

---

# 15. Reusable partials

Partial has:

- UUID
- input schema
- component tree
- styles

Invocation maps parent context to partial inputs.

Cycle detection prevents recursive partial loops.

---

# 16. Nested listings

Allowed with strict bounds.

Controls:

- child Listing View
- parameter mapping from current parent result
- max nesting depth
- empty behavior

N+1 diagnostics mandatory.

Prefer batch relation/query preloading when provider supports.

---

# 17. Listing View editor sections

1. General
2. Data Query
3. Parameters
4. Item Template
5. Layout
6. Pagination
7. Sorting Controls
8. Filters
9. Loading
10. Empty/Error States
11. Access Policy
12. Cache
13. SEO/URLs
14. Performance
15. Integration/Embed
16. Preview
17. Dependencies/Revisions

---

# 18. Query binding

Required:

- saved Query UUID or approved context query mode

Parameter mapping sources:

- static
- shortcode/block attributes
- current entity
- current user
- URL parameter with schema validation
- filter state
- builder/context input

All map to Query declared parameter schema.

---

# 19. Item template binding

- template UUID required
- result schema compatibility check
- optional template selector condition for polymorphic rows

If query result schema changes incompatibly, Listing becomes Degraded until mappings fixed; it does not silently render wrong fields.

---

# 20. Collection layouts

Initial:

## Grid

- columns base/tablet/mobile
- min item width option where fluid grid accepted
- row/column gap
- equal height
- alignment

## List

- vertical gap
- separators
- item full width

## Masonry

Advanced because accessibility/layout shift/performance differ.

- column count
- gap
- image-size stability warning

## Table

For semantic tabular result data only.

Dynamic Tables may use a specialized renderer; Listing Table mode should not duplicate full table-builder features.

## Carousel/Slider

Not required for first implementation. If added, must have keyboard, focus, reduced-motion and screen-reader behavior and not become a generic third-party slider dependency by default.

---

# 21. Item wrapper

Options:

- semantic tag
- item classes
- item ID/anchor pattern
- alternating/position context only as presentation
- animation: off default; reduced-motion compliance required

---

# 22. Items per page

- comes from Query default
- Listing override only if query parameter permits
- bounded maximum
- responsive page-size changes generally avoided because they destabilize pagination/URLs

---

# 23. Pagination modes

Supported based on Query provider:

1. None / bounded first results
2. Standard page links
3. Previous/Next
4. Page numbers
5. Load More
6. Infinite Scroll
7. Cursor/Load More for cursor providers

Standard page navigation is baseline accessible/SEO behavior.

---

# 24. Standard pagination controls

- page parameter/key
- previous label
- next label
- page-number window
- first/last optional
- preserve approved filter/sort parameters
- scroll/focus after navigation
- canonical URL strategy

Page parameter collisions across multiple listings require unique listing instance/query IDs.

---

# 25. Enhanced/AJAX pagination

Planning preference on compatible WordPress floor: use accepted WordPress Interactivity API/server-rendered response strategy where it reduces custom runtime and remains builder-compatible.

Controls:

- enhanced navigation on/off
- update browser history
- scroll behavior
- focus target
- loading announcement

JavaScript failure must leave functional link/page navigation where feasible.

---

# 26. Load More

- button label
- loading label
- done behavior hide/disabled/message
- number added per request
- scroll/focus behavior
- preserve history optional

Button remains keyboard-operable.

---

# 27. Infinite scroll

Off by default.

Options:

- trigger threshold
- max automatic pages before requiring manual continuation candidate
- history URL updates
- end message

Accessibility/usability concerns:

- preserve footer reachability;
- announce loaded items;
- focus is not moved unexpectedly;
- browser back restores state if claimed supported.

---

# 28. Lazy initial listing load

Default false for primary SEO/content listings.

When enabled:

- intersection/offset threshold
- placeholder/skeleton
- no-JS fallback decision

Do not lazy-load above-the-fold primary content by default.

---

# 29. Loading state

Types:

- spinner/status
- skeleton matching item layout
- subtle progress

Requirements:

- `aria-busy`/status semantics where applicable
- no layout shift where practical
- timeout/error transition

---

# 30. Empty state

Definition supports component/partial tree, not only one text string.

Options:

- title
- message
- action/reset filters
- image/icon
- different message for “no data exists” vs “filters returned none”

---

# 31. Error state

Types:

- query invalid
- permission denied
- provider timeout
- rate limit
- dependency unavailable

User-facing frontend does not reveal SQL/stack/internal details.

Actions:

- retry where safe
- reset filters
- sign in/request access where policy says

---

# 32. Sort controls

A Listing may expose selected Query sort presets.

Each preset:

- label
- Query sort mapping
- URL parameter value
- default

Do not let public input specify arbitrary field/orderby beyond allowlisted presets/schema.

---

# 33. Filters

Listing consumes shared Filter definitions/Query parameter bindings.

Initial control types:

- text search
- select
- multi-select
- checkboxes
- radio
- range
- date/date range
- taxonomy/hierarchy
- entity selector
- boolean
- active filter chips
- reset

Filter logic belongs to Query AST/parameter schema.

---

# 34. Filter application modes

- Submit + page reload — robust baseline
- Enhanced/server-rendered async update
- Automatic as changed — only when debounce/performance is appropriate

Controls:

- apply button
- reset
- debounce
- history URL synchronization
- preserve unrelated query parameters

Back/forward navigation restoration is part of acceptance if URL-sync mode is advertised.

---

# 35. Filter URL schema

Every exposed filter parameter:

- namespaced URL key
- value type
- repeated/list encoding
- default omission behavior
- validation
- canonical ordering/serialization

Sensitive/internal parameters never leak into public URL.

---

# 36. Filter counts/indexing

Optional counts/indexer is a separate performance capability.

Do not synchronously compute expensive facet counts on every request without indexing/cache.

For each filter:

- show counts
- hide zero options
- index/source capability
- stale/invalidation behavior

Exact indexing engine requires later architecture evidence.

---

# 37. Access policy

Listing has policy on:

- execute query
- view collection
- view item field/component
- perform item action

If query returns records but actor cannot view some records:

- provider/policy should filter unauthorized records before final pagination/count where practical;
- do not fetch 20 rows, hide 15 client-side, then claim page has 20 items;
- total count must respect visible result semantics.

This is especially important for Membership/User/Private content.

---

# 38. Cache

Separate:

- Query result cache
- rendered item/template cache
- rendered listing fragment cache

Cache varies by:

- query parameters
- page/cursor
- sort/filter state
- locale
- policy/membership/user context where relevant
- template revision

Public fragment cache must not contain personalized/private content.

---

# 39. Item render cache

Candidate only for stable public entities.

Invalidate when:

- entity fields change
- related field/relation dependencies change
- template revision publishes
- relevant site settings change

Dynamic current-user components may render outside shared cached fragment or require user-scoped cache.

---

# 40. SEO semantics

SSR-first goals:

- meaningful initial HTML
- semantic links
- headings structured by page/template context
- image alt
- page URLs for paginated indexable collections

Load More/Infinite Scroll must not be the only discoverability path when SEO/indexability matters.

Canonical/noindex strategy is site/page SEO concern; Listing should expose relevant pagination/filter state without pretending to replace SEO plugin policy.

---

# 41. Structured data

Not automatic merely because listing contains products/events/etc.

Future Schema/SEO adapter can use typed item data. Avoid duplicate/conflicting JSON-LD generation by default.

---

# 42. Server/client render consistency

If WordPress Interactivity API is selected after evidence:

- server HTML is canonical first render;
- client behavior enhances existing markup;
- hydration/directive output must match server state;
- process directives once at correct boundary;
- WPE namespace isolates state/actions.

No custom duplicated React hydration for simple listing interactions unless strong evidence requires it.

---

# 43. Embed surfaces

Every published Listing View can expose approved embeds:

- Gutenberg dynamic block
- shortcode
- Elementor widget adapter
- Bricks adapter
- WPBakery/Visual Composer adapter
- Dashboard Builder component
- admin widget/list adapter
- PHP developer API/renderer

Adapters pass Listing UUID + typed parameter overrides; they do not duplicate query/template configuration into builder documents unless unavoidable and documented.

---

# 44. Shortcode contract

Generated stable shortcode references listing UUID/key.

Attributes only from declared public parameters.

Unknown attributes ignored/rejected according to schema; no arbitrary query arguments.

Output uses same server renderer as block/adapters.

---

# 45. Gutenberg block

Dynamic/server-rendered WPE Listing block candidate.

Inspector controls:

- select Listing
- approved parameter overrides
- alignment/layout wrapper settings
- preview

Use standard block supports where practical so WordPress responsive/styles work without parallel proprietary system.

Do not expose every Listing design setting again inside block if it belongs to central definition; support instance overrides deliberately.

---

# 46. Builder adapter rules

Elementor/Bricks/WPBakery etc. adapter:

- selects Listing definition
- maps allowed parameters
- exposes only safe instance presentation overrides
- respects builder preview/context
- loads assets only when used

WPE does not convert its canonical item schema into proprietary builder documents automatically unless separate import/export adapter is designed.

---

# 47. Preview

Preview chooses:

- sample Query parameters
- user/context simulation if privileged
- responsive viewport
- pagination/filter state

Show diagnostics:

- results returned
- hidden by policy
- Query duration
- DB queries
- cache
- nested listing count
- per-item render duration
- assets
- remote calls

---

# 48. Performance budgets

Potential warnings:

- item count/page too high
- large images
- nested Listing N+1
- shortcode per item
- remote query per item
- unindexed sort/filter
- many dynamic actions
- oversized template DOM
- repeated rich editor content

Batch preloading and Query provider capabilities should be favored over per-item calls.

---

# 49. Failure isolation

One malformed item should not necessarily blank entire listing.

Policy:

- renderer catches typed item-render error;
- logs correlation without sensitive data;
- admin preview identifies item/index;
- frontend can skip item or render safe fallback according to configured error policy.

Systemic query/provider failure triggers Listing Error state.

---

# 50. Revisions

Semantic diff:

- Query changed
- parameter mapping changed
- template changed
- layout changed
- pagination/filter URL changed
- access policy changed
- cache changed

URL/filter/pagination schema changes are integration-impacting.

---

# 51. Import/export

Listing View export:

- UUID
- Query UUID
- Template UUID
- parameter mapping
- layout/pagination/filter/cache/policy

Item Template export:

- UUID
- component tree
- bindings
- partial refs
- design tokens

Import preview maps missing Query/Field/Partial/action dependencies.

---

# 52. Diagnostics

- query missing/degraded
- result schema incompatible
- field binding missing
- partial recursion
- nested listing recursion/depth
- unauthorized field binding
- pagination unsupported by provider
- infinite scroll without stable ordering
- filter not mapped to declared parameter
- URL key collision
- cache leaks personalized content risk
- N+1
- no accessible empty/loading state
- invalid heading hierarchy warning
- missing image alt source
- builder adapter unsupported version

---

# 53. Acceptance-test inventory

## Rendering

- each primitive
- escaping/sanitization
- missing value/fallback
- private field denial
- nested partial
- nested listing depth

## Layout/responsive

- grid/list/table
- responsive columns/gaps
- equal-height/masonry if offered
- RTL

## Pagination

- page links
- prev/next
- enhanced pagination JS/no-JS
- load more
- infinite scroll
- browser back/forward
- multiple listings same page parameter isolation

## Filters

- each control
- AND/OR/multiple filters
- URL encoding
- invalid public value
- reset
- zero results
- history

## Security

- private result excluded before visible pagination where policy requires
- unauthorized action
- shortcode attribute injection
- dynamic URL XSS
- remote image/URL escaping
- cache permission isolation

## Performance

- nested relation/query batching
- remote provider
- page-size ceiling
- render cache invalidation
- asset loading only when listing used

## Accessibility

- keyboard pagination/filter
- loading announcement
- infinite-scroll announcements
- focus after async update
- heading semantics
- image alt
- reduced motion

---

# 54. Differentiators

1. Query and Item Template are reusable separate definitions.
2. SSR-first instead of JavaScript-only grid.
3. WordPress Interactivity API is preferred candidate for progressive enhancements on supported floor rather than a proprietary frontend runtime.
4. Policy-aware pagination/counts, not client-side hiding.
5. Query/result schema compatibility checked before Publish.
6. Builder adapters reuse one canonical Listing definition.
7. N+1 and remote-call cost visible during design.
8. Filter URL schema is typed/versioned.
9. Accessibility/no-JS behavior is part of feature definition, not afterthought.
10. AI may draft component/query mappings but cannot bypass renderer/policy/action schemas.

---

# 55. Open decisions before implementation

- explicit user development consent;
- accepted UI/build/compatibility architecture;
- final canonical component-tree schema;
- WordPress Interactivity API acceptance proof across builders/themes;
- filter/indexer physical architecture;
- rendered-fragment cache strategy;
- pagination compatibility for remote/merged providers;
- exact responsive token integration with WordPress 7.1+ responsive styles;
- nested-listing batching API;
- SEO pagination defaults;
- builder-adapter version matrix.

**Development authorization remains NO.**
