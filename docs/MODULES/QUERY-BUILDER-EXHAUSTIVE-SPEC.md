# WPEssential — Custom Query Builder Exhaustive Specification

Status: **Phase 0 planning only — no development consent**  
Module: **Custom Query Builder — Pro**

This document deepens `DATA-QUERY-SPECS.md` and `OPTION-INVENTORY.md` and defines the query model that future Listings, Admin Columns, Forms, REST, Membership, Reports, Workflows and AI abilities will reuse.

## Research baseline

Verified against current WordPress query APIs and current JetEngine Query Builder market behavior.

WordPress primitives reviewed:

- `WP_Query`
- `WP_Meta_Query`
- `WP_Tax_Query`
- `WP_Date_Query`
- `WP_Term_Query`
- `WP_User_Query`
- `WP_Comment_Query`

Current JetEngine market bar includes Posts, Terms, Users, Comments, SQL/AI, Repeater, Current WP Query, Merged, Relations, WooCommerce products, CCT, REST API and several plugin-specific query adapters.

WPEssential must not imitate these as isolated form screens. It uses one typed AST with provider-specific capability declarations.

---

# 1. Query definition model

Every saved query has:

- stable UUID
- name
- machine key
- description
- status: Draft / Published / Disabled / Archived
- provider/source type
- source subtype/config
- parameter schema
- filter AST
- projection/fields
- grouping/aggregates where supported
- sorting
- pagination
- cache policy
- permission/policy
- result schema
- diagnostics/performance metadata
- revisions
- dependencies

Draft query never replaces the published revision used by production consumers.

---

# 2. Query list screen

Columns:

1. Name
2. Key
3. Provider
4. Source
5. Status
6. Parameters count
7. Used by count
8. Cache
9. Last preview duration
10. Health
11. Updated
12. Actions

Filters:

- status
- provider family
- has parameters
- cached/not cached
- used/unused
- healthy/warning/degraded

Actions:

- Edit
- Preview/Test
- Duplicate
- Used by
- Revisions
- Export
- Disable/Enable
- Archive/Restore
- Delete definition

---

# 3. Query editor sections

1. General
2. Source
3. Parameters
4. Filters
5. Relations / Joins
6. Fields / Projection
7. Grouping & Aggregates
8. Sorting
9. Pagination
10. Cache
11. Permissions
12. Preview
13. Explain / Diagnostics
14. Dependencies
15. Revisions
16. Export

Sections appear/disable based on provider capabilities.

---

# 4. Provider capability contract

Every query provider declares booleans/capabilities such as:

- filter
- nested boolean filters
- full-text/search
- meta filters
- taxonomy filters
- date filters
- joins
- relation traversal
- group by
- aggregates
- computed projection
- distinct
- sorting
- multi-sort
- offset pagination
- page pagination
- cursor pagination
- total count
- cacheability
- streaming
- writeability: Query Builder v1 remains read-oriented; writes belong to Data Sources/Abilities
- explain plan
- timeout control
- field schema discovery
- dynamic parameter binding

UI never shows an unsupported option as if it will work.

---

# 5. Initial provider/source catalog

## 5.1 WordPress Posts

Backed by approved `WP_Query` compiler semantics.

Source controls:

- post type: one/many
- post status: one/many
- include IDs
- exclude IDs
- parent
- parent include/exclude
- author / author include/exclude
- slug/name where supported
- search
- password state
- sticky handling where relevant
- attachment/media subtype when applicable

Advanced WordPress-specific query flags are only exposed when their behavior is understood and reusable; no raw serialized `WP_Query` args textarea.

## 5.2 Terms

Backed by `WP_Term_Query` semantics.

Controls:

- taxonomy one/many
- object IDs
- include/exclude
- exclude tree
- name/slug/term taxonomy ID
- search
- name-like
- description-like
- hide empty
- hierarchical
- parent
- child_of
- childless
- pad counts
- field/projection mode
- meta filters
- cache/meta-cache behavior only in Advanced diagnostics/performance settings

## 5.3 Users

Backed by `WP_User_Query` semantics.

Controls:

- roles: all-of (`role`) and any-of (`role__in`) semantics must be represented distinctly
- role exclusion
- capability filters with warning that WordPress capability query behavior does not represent every dynamically mapped meta capability
- include/exclude IDs
- login/nicename/email/search controls only as permitted by privacy/policy
- has published posts
- registered-date filters
- meta filters
- site/blog scope in multisite

Sensitive user fields are not automatically projectable publicly.

## 5.4 Comments

Backed by `WP_Comment_Query` semantics.

Controls include:

- comment include/exclude
- author include/exclude
- user ID
- author email/URL only for authorized internal uses
- parent / parent include/exclude
- post ID/include/exclude
- post author include/exclude
- post type/status/name/parent where supported
- comment status
- comment type/include/exclude
- search
- karma
- approved/unapproved handling under strict permission
- meta query
- date query
- count result mode

## 5.5 Media

Specialized Posts provider preset over attachments with typed media filters:

- MIME family
- MIME type
- parent post
- date
- author
- metadata/attachment fields where safe

## 5.6 Custom Tables

Backed by Custom Tables schema/provider.

Controls:

- table definition UUID
- alias
- field selection
- typed WHERE
- joins limited to declared safe tables/relations
- group/aggregates
- order
- offset/cursor depending key/index

Raw table names from user text are not normal configuration; select registered tables/schema.

## 5.7 WPEssential Relations

Source:

- relation UUID
- direction
- starting entity parameter/context
- target entity
- pivot-field filters
- relation order
- relation count

Can return target entities, relation rows or aggregates according to result mode.

## 5.8 Repeater / Structured Field Rows

Source:

- field UUID
- parent entity source
- parent entity parameter
- nested row path

Provider exposes limitations when structured field storage is not efficiently queryable across all entities.

A Repeater Query should not pretend serialized post-meta rows are an indexed relational table.

## 5.9 Current WordPress Query

Read-only context provider representing the current main query/archive.

Options:

- use current results exactly
- optionally apply safe post-processing only if semantics are explicit

Do not mutate global main query from a reusable saved query definition by surprise.

## 5.10 Merged Query

Combines results from saved queries.

Controls:

- component queries
- same result-schema requirement or explicit mapping schema
- duplicate policy
- source precedence
- max results

Two strategies are distinct:

1. **Application merge** — execute children separately then merge; global database sorting/pagination may be impossible/inexact.
2. **Provider-native UNION/merge** — only when a provider explicitly supports compatible result schemas and can compile safely.

UI must surface pagination/sort limitations instead of hiding them.

## 5.11 Remote REST/API

Uses Connections Manager.

Controls:

- connection UUID
- endpoint/path template
- HTTP method: GET by default for Query Builder
- query parameter mapping
- headers from connection/allowlisted static values
- pagination strategy
- response path to items
- total-count path/header
- item schema
- timeout
- cache
- error mapping

SSRF, credentials and auth are delegated to Connections policy. Query definition never stores raw secrets.

## 5.12 WooCommerce Products

Adapter loaded only when WooCommerce supported version active.

Typed product controls can include:

- product type
- status/visibility
- price range
- stock status/quantity
- SKU
- featured/on sale
- catalog taxonomy/attributes
- rating/order fields according to supported APIs

Implementation must use supported WooCommerce data/query APIs where required; do not assume direct `wp_posts`/postmeta remains canonical for all WooCommerce storage generations.

## 5.13 Form/Workflow Records

Provider supplied by Forms module:

- form UUID
- record status
- date/user
- submitted fields according to schema/policy

## 5.14 Membership runtime

Provider supplied by Membership module:

- plans/enrollments/entitlements/team records according to Membership privacy/access rules
- membership query must not expose external billing payloads/secrets as generic fields

## 5.15 Registered external provider

SDK query provider must declare schema, filters, sort, pagination, auth and failure semantics.

---

# 6. Parameters / macros

Queries can declare runtime parameters rather than interpolate arbitrary strings.

Each parameter:

- name/key
- label
- logical type
- required
- default
- allowed source contexts
- validation
- normalization
- nullable
- array/cardinality
- sensitive flag

Allowed binding sources:

- explicit caller argument
- current user/entity/context through approved resolver
- URL/query request only when definition explicitly permits and validation applies
- shortcode/block attribute
- REST endpoint parameter through endpoint schema
- workflow input
- Ability input

Never concatenate parameter strings into SQL.

Missing required parameter returns typed query error before execution.

---

# 7. Filter AST

Canonical nodes:

- `group`
- `comparison`
- `meta`
- `taxonomy`
- `date`
- `search`
- `relation`
- `exists`
- provider extension node

Group:

- relation `AND` / `OR`
- nested groups
- maximum nesting safety limit

Each clause has stable node UUID for revisions, diagnostics and named sorting references.

---

# 8. Generic comparison operators

Provider/type-dependent subset from:

- equals
- not equals
- greater than
- greater or equal
- less than
- less or equal
- in
- not in
- between
- not between
- contains / LIKE semantics
- not contains
- starts with
- ends with where provider can safely compile
- exists
- not exists
- regex/not regex only Advanced and provider-supported
- is null / is not null where source semantics genuinely distinguish SQL null
- is empty / not empty as separate semantics when meaningful

UI does not present a string operator for incompatible numeric/entity field types without explicit cast.

---

# 9. WordPress Meta Query clause

Fields:

- meta key: preferably select typed Field definition; external raw key allowed Advanced with validation
- compare key operator where supported
- value
- compare
- type/cast
- key type/cast where supported
- named clause key for ordering

Supported WordPress-style value compare set:

- `=`
- `!=`
- `>`
- `>=`
- `<`
- `<=`
- `LIKE`
- `NOT LIKE`
- `IN`
- `NOT IN`
- `BETWEEN`
- `NOT BETWEEN`
- `EXISTS`
- `NOT EXISTS`
- `REGEXP`
- `NOT REGEXP`
- `RLIKE`

Cast types tracked:

- CHAR
- NUMERIC
- BINARY
- DATE
- DATETIME
- DECIMAL
- SIGNED
- TIME
- UNSIGNED

Typed WPEssential Field selection can preselect appropriate cast/operator set.

No value input shown for EXISTS/NOT EXISTS when provider semantics do not require one.

---

# 10. Taxonomy Query clause

Fields:

- taxonomy
- field: term ID / slug / name / term taxonomy ID
- terms
- operator
- include children

Operators:

- IN
- NOT IN
- AND
- EXISTS
- NOT EXISTS

Taxonomy optional only when provider semantics allow `term_taxonomy_id` usage.

Nested taxonomy groups use AND/OR.

---

# 11. Date Query clause

Canonical date-clause controls based on `WP_Date_Query`-style capability:

- column
- compare
- before
- after
- inclusive
- year
- month/monthnum
- week
- day of year
- day
- day of week
- ISO day of week
- hour
- minute
- second

Compare operators:

- `=`
- `!=`
- `>`
- `>=`
- `<`
- `<=`
- `IN`
- `NOT IN`
- `BETWEEN`
- `NOT BETWEEN`

Values accept arrays only where operator semantics allow.

Date parser UI prefers typed date/time controls and dynamic value resolver rather than raw `strtotime()` text for ordinary users.

Timezone policy is explicit per provider/source column.

---

# 12. Search clause

Controls:

- search term parameter/static value
- included fields/provider search scope
- exact vs partial where supported
- token exclusion where provider supports
- case sensitivity only when source supports deterministic behavior

Public free-text search gets length limits and performance diagnostics.

---

# 13. Relation traversal

Controls:

- relation UUID
- direction
- from entity input
- min/max depth
- pivot filters
- target filter subquery
- include relation metadata

Arbitrary recursive traversal is not unlimited; cycles/depth are bounded.

---

# 14. Joins

Joins only for providers that explicitly support them.

Join definition:

- left source/table/schema field
- right registered source/table/schema field
- join type: INNER / LEFT initially
- comparison keys/types
- alias
- cardinality expectation

No free-form arbitrary table/column SQL in normal mode.

Cross-WordPress-table joins get compatibility warnings if they depend on third-party private schema.

---

# 15. Projection / fields

Modes:

- Full entity
- IDs only
- selected fields
- scalar count
- aggregate row
- mapped/custom result schema

Each projected field:

- source field
- alias
- output type
- nullable
- formatter is **not** part of query canonical value; rendering happens downstream

Sensitive fields may be hidden by Policy even if underlying provider technically exposes them.

---

# 16. Computed fields

Only provider-supported safe expression engine.

Functions/operators are allowlisted.

No PHP eval, no arbitrary SQL expression in normal query mode.

Computed field declares:

- alias
- output type
- expression AST
- dependencies

---

# 17. Grouping & aggregates

Provider-supported options:

- group by selected field(s)
- COUNT
- COUNT DISTINCT
- SUM
- AVG
- MIN
- MAX

Potential later functions require explicit provider support.

Aggregate input must be numeric/type-compatible where appropriate.

HAVING-like filtering may be represented as post-aggregate filter node if provider supports.

---

# 18. Sorting

Multiple ordered sort rules.

Each:

- field/named clause/computed field
- direction ASC/DESC
- null ordering when provider supports
- case/collation mode only Advanced and source-supported

Stable pagination requires deterministic tie-breaker; diagnostics warns when sorting is non-unique and cursor pagination is selected.

Random ordering:

- considered expensive/non-deterministic;
- explicit Advanced option only where provider supports;
- incompatible with stable cursor/caching assumptions unless seed semantics exist.

---

# 19. Pagination

Modes by provider:

- none/bounded
- page + per page
- offset + limit
- cursor/keyset

Options:

- page size
- maximum page size
- page parameter name for public/embed callers
- offset
- total count enabled
- cursor fields/direction

Defaults are bounded. Public query cannot request unbounded “all rows” unless policy explicitly permits safe use.

Preview has stricter limit than runtime.

---

# 20. Result count

Modes:

- exact total
- approximate/provider total
- unknown/not requested

Because exact counts can be expensive, consumers declare whether total pages/count are required.

---

# 21. Cache policy

Default: provider/product chooses conservative safe behavior; no generic “cache everything”.

Controls:

- disabled
- request-local
- object cache/transient adapter
- duration
- vary-by parameters
- vary-by current user/role/policy context
- stale-while-revalidate candidate only where safe
- manual invalidation dependencies

Security rule:

Never reuse a cached privileged result for a less-privileged viewer. Permission context participates in cache key or protected fields are evaluated after safe data retrieval according to provider design.

Invalidation sources can include:

- entity create/update/delete
- meta field update
- relation change
- taxonomy change
- membership entitlement change
- manual purge
- TTL

Diagnostics show cache hit/miss and invalidation strategy.

---

# 22. Permissions

Separate capabilities/policies:

- list query definitions
- read definition
- create
- edit
- publish
- delete
- preview
- view Explain/diagnostics
- execute in admin
- expose on frontend
- execute from REST/Ability

A caller permitted to execute a query still does not automatically receive fields/entities they are not authorized to see.

Provider-level object authorization and result projection policy remain mandatory.

---

# 23. Preview/Test

Preview form dynamically renders declared parameters.

Controls:

- parameter inputs
- context simulation only for authorized admins
- row limit
- timeout budget
- bypass cache toggle for diagnostics

Results show:

- rows
- result schema
- total if requested
- duration
- cache status
- provider
- warnings
- executed plan summary

Never expose raw secrets or sensitive provider request headers.

---

# 24. Explain / performance diagnostics

Where supported:

- provider/compiler
- normalized AST
- estimated/actual rows
- query count
- duration
- memory approximate
- cache status
- indexes used/missing for registered custom tables
- meta-query cost warning
- N+1 risk
- unbounded result warning
- remote API latency
- nested relation depth

Custom-table provider may support database `EXPLAIN` read-only diagnostic through safe compiler.

For WP_Query/metadata, diagnostic can classify known expensive patterns even when MySQL EXPLAIN is not surfaced in normal UI.

---

# 25. SQL / Developer query boundary

Standard Query Builder does not use arbitrary SQL as its canonical model.

Normal Custom Table query uses typed schema/AST.

A future developer SQL mode, if separately accepted, is governed by ADR-0004:

- read-only SELECT/EXPLAIN baseline
- one statement
- no arbitrary PHP
- capability/re-auth/audit
- prepared parameters
- bounded rows/time
- explicit unsafe/developer classification

AI-generated SQL cannot bypass this boundary.

---

# 26. Remote/API failures

Result states:

- success
- empty
- timeout
- auth failed
- rate limited
- provider unavailable
- malformed schema
- partial/stale cached

Retry belongs to caller/connection/job policy; ordinary page render must not synchronously hammer failing provider.

---

# 27. Dependency graph

Consumers registered as dependencies:

- Listing
- Admin Column
- Dynamic form choices
- Dashboard widget/card
- REST endpoint
- Workflow
- Notification digest
- Membership rule/segment
- Builder widget
- shortcode/block
- another query (merged/subquery)

Delete query definition is blocked/warned based on hard dependencies.

---

# 28. Import/export

Exports:

- UUID
- provider type
- source references
- parameter schema
- AST
- projection
- sort/pagination/cache/policy
- dependency references

No connection secrets.

Import preview maps:

- Field UUIDs
- Relation UUIDs
- Custom Table UUIDs
- Connection UUIDs
- missing provider adapters

---

# 29. Query revisions

Revision diff should be semantic, not only JSON text:

- source changed
- parameter added/removed/type changed
- filter clause added/removed/operator/value changed
- sort order changed
- cache policy changed
- permission changed

Breaking changes to parameter/result schema show consumer impact before Publish.

---

# 30. Acceptance-test inventory

## AST

- nested AND/OR
- depth limit
- invalid operator for type
- missing parameter
- null/empty distinction
- import/export stable UUIDs

## WordPress posts

- post type/status/include/exclude
- author
- meta query every supported operator/cast class
- nested taxonomy groups/operators
- date query range/inclusive/unit comparisons
- pagination/count
- search
- deterministic sort

## Terms

- taxonomy
- hide empty
- parent/child/childless
- include/exclude tree
- meta
- ordering

## Users

- role all/any/exclude semantics
- capability warning behavior
- search privacy
- date registered
- meta
- multisite scope

## Comments

- status/type/post/author/parent
- unapproved access policy
- meta/date
- count

## Custom Tables

- indexed/unindexed filters
- joins
- aggregates
- cursor
- EXPLAIN diagnostic

## Remote

- timeout
- invalid auth
- rate limit
- malformed JSON
- pagination mapping
- cache isolation
- SSRF boundary delegated to Connection

## Security

- parameter injection strings
- SQL metacharacters remain values
- unauthorized query preview
- public caller cannot expose private user/email/meta
- cache does not leak privileged results
- hidden query definition does not imply public execution

## Performance

- 10k/100k/large fixtures after development consent
- page-size ceiling
- expensive meta sort warning
- relation depth
- merged query pagination limitation

---

# 31. Differentiators

1. One AST across providers.
2. Provider capability matrix prevents fake unsupported controls.
3. Typed Field/Relation/Table references instead of raw keys everywhere.
4. Runtime parameters are schema-validated, never string interpolation.
5. Query cost/queryability surfaced during design.
6. Security policy applies to execution **and** returned objects/fields.
7. Query result schema is reusable by Listings, Forms, REST, Membership and AI.
8. Draft/published revisions protect production consumers.
9. Cache invalidation is dependency-aware.
10. AI may draft AST, but cannot invent privileged raw SQL execution.

---

# 32. Open decisions before implementation

- user development consent;
- accepted global platform ADRs;
- exact AST JSON schema/versioning;
- compiler interface between generic AST and provider-native nodes;
- subquery support scope;
- provider-native vs application merged-query rules;
- exact public execution rate/row/time budgets;
- cursor abstraction across heterogeneous providers;
- cache adapter and invalidation event design;
- WooCommerce HPOS/product-storage compatibility proof;
- remote REST streaming/pagination adapter contract;
- full projection-level resource authorization strategy;
- Explain detail safe to expose to non-super-admin roles.

**Development authorization remains NO.**
