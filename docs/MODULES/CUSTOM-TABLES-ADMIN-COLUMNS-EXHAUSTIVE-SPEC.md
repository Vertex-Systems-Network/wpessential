# WPEssential — Custom Tables Builder & Admin Columns Builder Exhaustive Specification

Status: **Phase 0 planning only — no development consent**

Modules:
- **Custom Tables Builder — Pro**
- **Admin Columns Builder — Pro**

This document deepens `DATA-QUERY-SPECS.md`, `OPTION-INVENTORY.md`, and the shared field/query contracts.

## Research baseline

Reviewed current WordPress custom-table guidance (`dbDelta()`/Plugin Handbook), WordPress list-table behavior, JetEngine Custom Content Types/Admin Columns, and Admin Columns Pro current feature set.

Key product rule:

- Custom Tables Builder owns **schema/data-source definitions and safe row management**.
- Admin Columns Builder owns **list-table views over WordPress/WPEssential data sources**.
- Dynamic Tables/Listings own frontend rendering.
- Query Builder owns reusable queries.

No module duplicates another module’s engine.

---

# Part A — Custom Tables Builder

# 1. Table definitions list

Columns:

1. Name
2. Logical key
3. Physical table
4. Status
5. Columns count
6. Rows count/estimate
7. Indexes count
8. Schema version
9. Used by
10. Health
11. Updated
12. Actions

Filters:

- Draft / Published / Disabled / Archived
- WPE-owned / inspected external
- empty / has rows
- healthy / migration pending / warning / degraded

Actions:

- Edit schema
- Browse data
- Query
- Duplicate definition
- Dependencies
- Revisions
- Export schema
- Disable/Enable
- Archive
- Delete definition

Physical table deletion is **not** the same action as deleting/archiving a definition.

---

# 2. Table editor sections

1. General
2. Columns
3. Primary Key
4. Indexes & Constraints
5. Relations
6. Admin UI
7. Permissions
8. REST / Abilities
9. Storage / Engine Compatibility
10. Migration Preview
11. Dependencies
12. Diagnostics
13. Revisions
14. Export

---

# 3. Table identity

### name

Human label, required.

### stable UUID

System-generated; canonical WPE dependency identity.

### logical key

- required before Publish
- sanitized namespaced key
- stable after references exist

### physical table name

Default generated from site table prefix + WPE namespace + safe logical identifier.

Rules:

- show exact resulting physical name;
- account for current DB identifier-length constraints in accepted compatibility floor;
- prefix behavior explicit in multisite/site/network context;
- changing physical name after Publish is migration-class;
- external table selector uses inspected schema, not arbitrary unchecked identifier interpolation.

---

# 4. Ownership modes

## WPEssential-managed

WPE owns schema definition/migrations.

## External inspected table

WPE may expose safe Data Browser/Query mapping after explicit configuration.

Default external-table behavior:

- schema read-only;
- no automatic migrations;
- no drop/rename columns;
- write operations off until user deliberately maps writable columns/policy.

Core WordPress tables are never casually adopted as WPE-managed custom tables.

---

# 5. Column common options

Every managed column has:

- stable column UUID
- name/key
- label
- logical Field type mapping optional
- SQL physical type
- length/precision/scale where type supports
- nullable
- default
- unsigned where numeric type supports
- auto increment where supported
- collation/charset inheritance/override according to compatibility policy
- generated/computed mode where supported
- comment/description optional
- position/order in schema UI
- sensitive/privacy classification
- read/write policy
- query/index hint

Physical column name changes are migrations, not cosmetic label edits.

---

# 6. Planned SQL type palette

Exact accepted list depends on WordPress-supported MySQL/MariaDB floor.

Normal UI should present **logical data types**, then map to supported physical SQL type.

Initial planned families:

## Integer

- tiny/small/medium/int/big variants only in Advanced physical mode
- normal default selected by logical range needs
- signed/unsigned

## Decimal

- precision
- scale
- currency-safe decimal use cases

## Floating point

- float/double only when approximate values acceptable
- UI warns not to use for exact money

## Boolean

Logical boolean mapped to compatible integer/boolean representation.

## String

- short string / VARCHAR
- length

## Text

- text size class chosen by expected/max payload

## Binary

- binary/varbinary/blob only Advanced and justified

## Date/time

- DATE
- TIME
- DATETIME
- TIMESTAMP only when semantics/compatibility are explicit

Timezone storage contract belongs to logical field layer.

## JSON

Only if accepted DB compatibility floor supports required semantics. Otherwise WPE may use text-encoded JSON with explicit limited queryability; never silently promise native JSON indexing.

## UUID/reference

Logical reference may map to binary/string/numeric internal identity depending accepted architecture. UI presents logical reference, not implementation encoding by default.

## Enum/set

Not preferred normal schema types because changing allowed values creates schema migrations. Choice semantics normally live in Field Schema + validation. Advanced physical use only if accepted.

---

# 7. Null/default semantics

Controls:

- nullable yes/no
- default none / null / literal / approved DB expression where safely supported

Rules:

- required application field does not automatically equal SQL NOT NULL if migration/existing-data semantics require distinction;
- adding NOT NULL column to populated table requires fill/default migration plan;
- current timestamp/update timestamp behavior is a typed preset, not arbitrary SQL expression textbox;
- no invalid zero-date defaults when target DB SQL modes reject them.

---

# 8. Auto increment

Allowed only when:

- integer-compatible type;
- required index/key semantics satisfied by target DB;
- one valid auto-increment column according to DB behavior.

WPE stable row identity can use numeric internal PK while public API identifiers may use separate UUID when product requires non-enumerable identity.

---

# 9. Primary key

Required for WPE-managed runtime tables unless an explicit advanced append-only schema pattern is accepted.

Modes:

- single-column primary key
- composite primary key — Advanced

WPE recommends stable numeric/UUID surrogate primary key for application records when business keys can change.

Changing primary key after rows exist is high-risk migration.

---

# 10. Indexes

Types planned:

- PRIMARY
- UNIQUE
- normal INDEX/KEY
- FULLTEXT only when DB compatibility/provider supports and use case is explicit
- spatial only future/provider-specific

Index definition:

- stable index UUID
- generated/explicit safe name
- ordered columns
- prefix length where valid/needed
- sort direction only where DB support/benefit proven
- unique
- index type

UI diagnostics:

- duplicate/redundant index
- left-prefix coverage
- index too wide
- highly unselective index warning
- column used in filter/sort without index
- too many indexes/write amplification warning

Do not auto-create indexes for every field.

---

# 11. Unique constraints

Modes:

- one column
- composite columns

Before adding to populated table:

- scan duplicate candidates;
- show sample conflicts/count;
- block migration until resolution.

Null semantics are DB-specific and must be explained where unique nullable column can allow multiple nulls.

---

# 12. Foreign keys vs logical relations

Default WPE cross-module relationship model uses Relations Engine/logical references.

Physical DB foreign keys are Advanced because WordPress plugin environments can have engine/collation/migration/delete compatibility issues.

If offered later:

- referenced managed table/key required;
- matching data type/collation;
- on delete/update actions explicit;
- existing orphan scan;
- migration rollback;
- database engine capability check.

Never create a physical FK to arbitrary external/plugin table without explicit compatibility evidence.

---

# 13. Generated columns

Advanced provider capability.

Controls if supported:

- virtual/stored
- expression through allowlisted schema/expression builder
- result type
- indexability diagnostic

No raw arbitrary SQL expression in normal UI.

---

# 14. Charset/collation

Default inherits current WordPress DB charset/collation strategy.

Advanced override only with compatibility warning.

Per-column collation override allowed only for compatible string columns.

Changing collation on large populated table is a potentially long-lock migration and requires estimate/maintenance strategy.

---

# 15. Engine/storage details

Ordinary user UI does not encourage switching DB storage engines.

WPE requires engine capabilities compatible with selected transactional/index features.

External table inspection reports engine.

---

# 16. Schema revisions

Immutable Definition Repository revisions describe desired schema.

Important distinction:

- publishing definition revision does not count as physical migration success;
- runtime schema has `applied_schema_version`;
- migration state can be pending/running/failed/applied.

Do not mark definition healthy if DB physical schema does not match published desired schema.

---

# 17. Schema diff classification

## Additive/lower risk

- add nullable column
- add compatible index
- add label/description only

## Medium

- add NOT NULL with safe backfill
- change default
- widen compatible string/numeric type
- add unique after duplicate scan

## High/destructive

- drop column
- narrow type/length
- change signedness when values conflict
- rename physical column where consumers exist
- change primary key
- change collation on large table
- table rename

Every physical migration receives:

- schema diff
- affected rows estimate
- data compatibility scan
- expected lock/online capability note
- dependency impact
- backup/restore-point requirement by risk
- rollback strategy

---

# 18. Migration execution planning

No implementation yet.

Future migration engine may use WordPress `dbDelta()` for appropriate create/add/compatible schema operations, but must not assume `dbDelta()` safely handles every rename/drop/type/constraint migration.

Migration strategies:

- direct DDL for safe small change
- online/copy migration for high-risk large change where supported
- staged backfill
- shadow/new table + copy/swap for selected cases

All destructive paths need verified backup/recovery.

---

# 19. Row Data Browser

Columns automatically derive from table schema, but user can create Admin Column view definitions.

Core Data Browser controls:

- pagination
- page size
- search on approved fields
- typed filters
- multi-sort
- add row
- edit row
- duplicate row
- delete row
- bulk delete/update where policy permits
- export selection
- inspect raw canonical values for authorized developer/admin view

CRUD uses Data Source + Field Schema validators, not direct unsanitized POST→SQL mapping.

---

# 20. Row editor

Uses shared Field Schema mapped to columns.

Each writable column declares:

- editor field type
- required/validation
- read-only/computed
- create default
- update permission

Primary/generated/internal fields may be read-only.

---

# 21. Row version/concurrency

For application tables with concurrent editing, optional/default optimistic locking candidate:

- updated timestamp/version counter
- update carries expected version
- stale edit returns conflict instead of overwriting silently

Exact default depends domain/adapter.

---

# 22. Bulk data actions

Operations:

- set value
- clear/null
- numeric increase/decrease/percentage adjustment
- text prefix/suffix only if type supports
- date shift if explicitly supported
- delete rows

Large selection executes through Job Service after development consent.

Preview:

- filter/query selection
- count
- sample before/after
- permission
- destructive severity

---

# 23. Row delete behavior

Hard delete default for custom application row only when entity schema says records are hard-deletable.

Optional soft-delete/archive support is a schema/domain feature, not magically provided to every table.

Relations/policies checked before delete.

---

# 24. Safe Query Console

Normal modes:

- Query Builder visual
- generated SQL preview
- `SELECT`
- `EXPLAIN`

Developer read-only console candidate:

- one statement
- prepared parameter bindings
- SELECT/EXPLAIN only
- row/time limits
- capability + audit

Destructive arbitrary SQL remains excluded from standard product under ADR-0004.

---

# 25. Table permissions

Separate:

- view definition
- edit schema
- publish schema
- apply migration
- browse rows
- create row
- update own/allowed row
- delete row
- bulk mutate
- export
- run explain/read console
- inspect external table

Schema migration permission is significantly stronger than ordinary row edit.

---

# 26. REST & Abilities

Table does not automatically expose public CRUD.

Potential typed operations:

- schema.get
- rows.list/get
- row.create/update/delete
- migration.preview
- migration.apply — privileged/destructive
- health.explain

User-facing REST API Builder chooses endpoint exposure/policy; Custom Tables only supplies Data Source capabilities.

---

# 27. Table import/export

Schema definition export:

- UUID
- logical schema
- indexes
- policies
- Field mappings
- dependencies

Data export separate.

Import schema never overwrites existing physical table silently.

Conflict modes:

- create new
- map to existing inspected table
- compare schema
- abort

---

# 28. Table diagnostics

- desired/applied schema mismatch
- migration pending/failed
- missing table
- unexpected external column/index
- charset/collation mismatch
- missing primary key
- duplicate unique candidates
- orphan logical references
- unindexed heavy query field
- large table no pagination/index
- external table owner unknown
- DB version lacks requested type/index feature
- row count estimate stale

---

# 29. Custom Tables acceptance tests

Planning minimum:

- create each accepted logical/physical type
- nullable/default combinations
- auto increment
- primary/composite key
- normal/unique/composite indexes
- duplicate unique migration block
- add/widen/narrow/drop/rename classification
- populated NOT NULL backfill
- external table read-only
- stale concurrent row update
- authorization CRUD
- relation-restricted delete
- safe SELECT/EXPLAIN console injection tests
- schema export/import
- migration failure recovery
- 10k/100k/large performance fixtures only after development consent

---

# Part B — Admin Columns Builder

# 30. Target list-table registry

Initial targets:

- Posts/Pages/CPTs
- Users
- Media
- Comments
- Terms per taxonomy
- WPE Custom Tables/Data Browser
- WPE module list adapters
- WooCommerce products/orders/customers only through supported Woo adapter/storage APIs
- registered third-party list target

Each target adapter declares:

- row entity source
- native columns
- sortable hooks/API
- filter hooks/API
- editable fields
- bulk-operation support
- primary-column/row-action constraints
- pagination/query integration

---

# 31. Column Set / Table View

A view definition contains:

- name
- UUID
- target list table
- active
- columns/order
- default sort
- default filters/segment
- visible user/role/capability condition
- sticky/header/horizontal preferences
- screen density preferences
- export settings

Multiple views per target.

User may switch views without reconfiguring definitions.

Optional personal vs shared view semantics must be explicit.

---

# 32. Column common controls

Every column:

- stable UUID
- title
- source type
- source configuration
- width
- min width
- alignment
- responsive priority/hidden-small setting
- sticky left/right where supported
- active
- primary-column eligibility
- row-action behavior where target permits
- formatting
- sorting enabled/mode
- filter enabled/mode
- search inclusion
- inline edit enabled
- bulk edit enabled
- export enabled
- condition formatting
- visibility policy
- expensive/lazy-load policy

---

# 33. Column sources

## Core object property

Examples based on target schema.

## Custom Field

WPE field UUID or discovered external meta key.

## Taxonomy terms

- taxonomy
- count/list
- separator
- links

## Relation

- related items
- count
- pivot field
- aggregate

## Query

- saved query UUID
- parameter mapping from row context
- result extraction
- aggregate

N+1 diagnostics mandatory.

## Image/media

- featured image/avatar/media field/reference

## Status

- status label/badge

## Computed token/expression

Allowlisted renderer expression over row data; not arbitrary PHP.

## Shortcode

Explicit advanced source.

- shortcode allowlist/registered shortcode selector where possible
- bounded/sanitized output
- sorting/filter/edit generally unavailable unless separate source semantics defined
- performance warning

## Server-rendered Block

Only registered blocks that safely server-render with row context.

## Registered callback/Ability

SDK extension descriptor with type/schema/performance classification; no arbitrary PHP textarea.

---

# 34. Display formats

- plain text
- sanitized rich text
- number
- currency
- percentage
- date/time/datetime/relative
- boolean yes/no/icon
- status badge
- image/avatar/thumbnail
- media/file link
- URL/link
- email link only when actor authorized
- list/chips
- taxonomy terms
- entity links
- relation count/items
- JSON/code preview
- progress/rating
- custom registered formatter

Formatting never changes canonical source value.

---

# 35. Text formatting controls

- truncate length
- word/character mode
- ellipsis
- tooltip/full preview
- empty value fallback
- prefix/suffix
- separator for list values
- strip HTML / safe HTML mode

Prefix/suffix are escaped presentation strings, not raw scriptable HTML.

---

# 36. Link controls

Link target modes:

- none
- edit entity
- view frontend
- source URL field
- relation target
- approved custom URL template

Options:

- new tab
- rel flags
- capability-dependent link visibility

Do not expose edit links to actors without destination permission.

---

# 37. Sorting

Modes by source:

- lexical
- natural
- numeric
- date/time
- boolean
- source-native
- relation count
- aggregate

Controls:

- enabled
- default direction
- initial active sort
- null/empty placement where provider supports

Source adapter provides query compiler. Renderer text is never used as sorting source unless explicitly defined.

Expensive meta/relation/query sorting gets performance warning.

---

# 38. Smart Filtering

Filter operators by logical type:

Text:
- equals/not equals
- contains/not contains
- starts/ends with where supported
- empty/not empty

Numeric/date:
- equal/not
- greater/less
- range
- empty/not empty

Choice/status/taxonomy/entity:
- is/is not
- any of
- all of where semantics support
- none
- empty/not empty

Boolean:
- true/false/empty

Relation:
- related to
- not related to
- has relation
- relation count comparison

Filter UI source can be static or dynamic Query.

Filters compile through target/query adapter rather than filtering only current HTML page.

---

# 39. Saved segments

A segment stores:

- filters
- search
- sorting
- optional view/column set reference

Visibility:

- private to user
- shared to roles/capabilities
- global admin

URL sharing must not leak private values or bypass permission.

---

# 40. Inline edit

Availability only if source declares writable semantics.

Editor comes from Field Schema/source adapter.

Flow:

1. open editor
2. fetch current canonical value/version if needed
3. edit
4. server authorization
5. validate/sanitize
6. optimistic conflict check
7. save
8. update row/rendered value
9. audit

Options:

- enabled
- edit action style
- require confirmation for risky fields
- undo candidate only when source can safely restore previous value

No editing a displayed computed/shortcode/query result unless a distinct writable underlying source is mapped.

---

# 41. Bulk edit

Per column source declares supported operations.

Generic:

- replace/set
- clear/null

Numeric:

- increase/decrease absolute
- increase/decrease percentage
- multiply where explicitly allowed

Taxonomy/list:

- replace
- add
- remove

Date:

- set
- shift duration if supported

Boolean/status:

- set state/value

Bulk selection:

- checked current page
- all matching current filtered query

The second mode stores/executes the **selection query**, not thousands of client-side IDs.

Large jobs use Job Service with progress/cancel/failure log after consent.

---

# 42. Bulk delete

Target-specific and disabled by default in view configuration.

Options:

- selected rows
- all matching filtered query

Requires:

- strong capability
- exact count
- impact/dependency preview
- confirmation/re-auth depending severity
- background job for large selection
- audit/recovery strategy

---

# 43. Quick Add

Optional target capability to create new row/entity directly from list.

Uses approved minimal Field Schema form.

Not all required business fields may fit Quick Add; target may disable it.

No bypass of normal create authorization/validation.

---

# 44. Conditional formatting

Rules:

- condition groups over canonical column/source values
- apply badge/text/background/icon emphasis tokens
- multiple rule precedence/order
- stop on first match option

Accessibility:

- never color-only meaning;
- contrast requirements;
- status text/icon/ARIA remains understandable.

No arbitrary CSS declaration input in normal UI.

---

# 45. Column visibility conditions

Conditions:

- user
- role
- capability
- screen context
- query/segment context where deterministic

Hiding a column does not hide/protect underlying data through other APIs/screens. Source Policy still governs data access.

---

# 46. Width / table layout

Controls:

- auto/fixed width
- min width
- sticky column
- sticky header
- horizontal scroll
- row density
- text wrap/no-wrap

Do not force global CSS onto unrelated list tables.

---

# 47. Primary column & row actions

WordPress list tables have a primary column that hosts responsive row actions.

WPE view must preserve a valid primary column.

If user removes/hides it, UI requires choosing another valid primary column or retains native safe fallback.

---

# 48. Export CSV

Scopes:

- current page
- selected rows
- all matching filtered/sorted query

Options:

- included export-enabled columns
- raw canonical vs displayed formatted value per column
- delimiter/encoding policy
- include headings
- date/time timezone

Security:

- export permission separate;
- hidden/unauthorized fields excluded;
- CSV/spreadsheet formula injection mitigated for values starting with formula-significant characters;
- large export background/streaming;
- do not export secrets.

---

# 49. Search integration

A column can offer inclusion in target search only if source adapter supplies efficient searchable semantics.

Do not implement “search every column” by loading/rendering every row in PHP.

---

# 50. Lazy/async columns

For expensive remote/query-derived values:

Modes:

- eager — only within budget
- batched preload
- lazy per visible page

Never fire one network/DB query per row if batch provider exists.

UI shows loading/error state per cell.

Sorting/filtering unavailable when lazy result cannot be server-queryable.

---

# 51. N+1/performance diagnostics

Column definition gets cost class:

- native
- cached
- batched
- expensive
- remote

Preview/table diagnostics show:

- DB queries added
- remote calls
- render duration
- cache hit
- batchability
- row count/page size

Warn/block pathological combination such as 100 rows × 5 per-row remote calls.

---

# 52. User preferences vs shared definition

Shared view owns canonical configuration.

Per-user preferences may store:

- chosen view
- temporary sort
- temporary filters
- column visibility if allowed
- density

User preference must not mutate shared view unless user has edit permission.

---

# 53. Admin Columns import/export

Exports view definitions, sources, formats, policies and dependencies—not row data.

Import maps:

- Field UUID
- Query UUID
- Relation UUID
- target list-table adapter

Missing sources remain degraded and clearly flagged.

---

# 54. Admin Columns diagnostics

- source missing
- source not sortable/filterable/editable despite option enabled
- expensive/N+1
- invalid primary column
- duplicate column key
- inaccessible field in current role view
- stale external plugin adapter
- Woo storage incompatibility
- remote source failure
- unsupported bulk operation
- CSV export field sensitive

---

# 55. Admin Columns acceptance tests

- each target list table
- order/width/sticky/primary column
- multiple views/user-role conditions
- each source type
- each formatter
- sorting type correctness
- filtering operators
- saved segment privacy
- inline edit valid/invalid/unauthorized/conflict
- bulk set/add/remove/numeric transform
- all-filtered selection without client ID explosion
- CSV formula-injection payload
- hidden column not treated as authorization
- lazy remote source timeout
- N+1 budget
- Woo adapter storage version compatibility
- no global optional assets on unrelated admin screens

---

# 56. Differentiators

## Custom Tables

1. Typed schema, not raw SQL-first setup.
2. Definition revisions separated from applied DB schema.
3. Migration impact/data compatibility is first-class.
4. Query/index diagnostics integrate with Query Builder.
5. External table inspection is not confused with ownership.
6. Dangerous phpMyAdmin-like powers remain separately gated/not standard.

## Admin Columns

1. Same Data Source/Field/Query/Relation schemas as entire platform.
2. Read/write capability is source-declared; no fake editable computed columns.
3. Server-query filtering/sorting, not browser-only illusions.
4. N+1/remote cost visible during design.
5. Bulk “all filtered” uses query selection + Job Service.
6. Views/segments/policies are versioned definitions.
7. CSV security and permission isolation are built into export contract.

---

# 57. Open decisions before implementation

Custom Tables:

- accepted MySQL/MariaDB floor and exact physical type matrix;
- physical schema migration library/strategy beyond safe `dbDelta()` cases;
- online migration/large-table thresholds;
- canonical row public UUID strategy;
- external-table writable mapping boundary;
- charset/collation compatibility matrix;
- transaction engine expectations;
- physical FK support policy.

Admin Columns:

- precise hooks/adapters for each WordPress core list table at chosen WP floor;
- WordPress DataViews migration/compatibility impact on admin screens;
- WooCommerce product/order storage adapter proof;
- bulk job persistence/cancellation contract;
- export streaming/temp-file strategy;
- per-user view preference storage;
- inline undo retention policy.

Global:

- explicit user development consent;
- accepted compatibility/UI/build/Definition/Job/CI/Secrets/Free-Pro ADRs.

**Development authorization remains NO.**
