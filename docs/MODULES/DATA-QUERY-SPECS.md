# WPEssential — Data & Query Detailed Specifications

Status: **Phase 0 — specified with open ADR/compatibility items**

Applies `COMMON-OPTION-CONTRACTS.md` and resolves Custom Query Builder, Custom Tables Builder, Admin Columns Builder and Dynamic Listings / Template Builder.

---

# 6. Custom Query Builder — Pro

## Definition
A query is a typed AST bound to a Data Source. Raw SQL is not the canonical stored representation.

## Identity
- name required;
- key generated/unique;
- status Draft by default;
- description optional;
- public usability disabled until explicit Publish.

## Source
User must explicitly choose one source. Source adapter declares:
- entity schema;
- filter operators;
- sortable fields;
- aggregations;
- pagination modes;
- permissions;
- preview/explain support.

Changing source after clauses exist requires migration/reset preview because field/operator references may become invalid.

## Filter group semantics
- root defaults AND;
- nested groups may be AND/OR;
- empty group is invalid on publish;
- each condition has field, operator, typed value source/value;
- unsupported operator for selected field is rejected server-side.

## Value sources
- literal
- parameter
- current user ID
- current entity reference
- route/query value from explicit allowlist
- current date/time
- related entity
- registered context provider.

Arbitrary PHP/request-global access is forbidden.

## String operators
- equals / not equals
- contains / not contains
- starts / ends
- in / not in
- empty / not empty
- exists / not exists where source semantics support.

Case sensitivity is adapter-specific. Default uses provider/DB normal collation behavior; explicit case-sensitive mode only shown when support is deterministic.

## Number/date operators
- equals/not
- gt/gte/lt/lte
- between/not between
- relative date before/after/within-last/within-next through typed date expression.

Date values are normalized and timezone semantics shown in UI.

## Taxonomy/meta/relation
Special builders emit typed AST nodes rather than smuggling serialized query arrays into generic conditions.

## Joins
Only available to source adapters that expose safe join capabilities.
- choose registered relation/table relation;
- join type limited to supported set;
- alias generated and collision checked;
- ad-hoc raw join expression excluded.

## Projection
Default: source's safe standard fields. User can select fields/aliases/computed expressions.
- duplicate aliases blocked;
- sensitive fields hidden unless actor/policy allows;
- output schema generated from projection.

## Aggregates
- COUNT/SUM/AVG/MIN/MAX when field/provider allows;
- group-by fields explicit;
- HAVING expressed as aggregate filter AST;
- unbounded expensive aggregate preview warnings.

## Sort
- zero or more sort clauses;
- ASC default for each newly added clause;
- random sort available only with strong performance warning and disabled for large/public queries by default.

## Pagination
Default admin preview: 25. Public consumer default: 20.
- offset/page mode universal where source supports;
- cursor mode preferred for adapters that support stable cursor;
- maximum generic user override 100;
- `no limit` unavailable on public render/endpoint.

## Parameters
Each parameter:
- key unique;
- type required;
- optional required flag;
- default typed value;
- validation constraints;
- public caller override disabled unless consumer opts in;
- sensitive parameters redacted from logs.

## Cache
Default disabled during builder preview. Published consumer may enable:
- TTL;
- cache key varies by declared parameters/user/locale/site as needed;
- invalidation events mandatory;
- per-user cache warning;
- never share authorized user-specific result under global key.

## Preview / explain
- preview bounded rows;
- execution duration displayed;
- warnings for full scans/large meta queries/N+1 relation loads where detectable;
- SQL visible only for SQL adapter and diagnostic capability;
- remote source raw secrets/headers never shown.

## Consumers
A consumer references query UUID + parameter mapping. It cannot mutate underlying AST privately.

## Tests
- nested groups
- type mismatch
- injection payload
- pagination limits
- cache authorization isolation
- deleted parameter/dependency
- expensive query warning
- remote source failure.

---

# 7. Custom Tables Builder — Pro

## Scope
Manage WPEssential-owned custom tables. Existing arbitrary WordPress/plugin tables are inspectable only under safe diagnostics/data-browser policies; schema mutation requires explicit ownership or advanced developer mode ADR.

## Table identity
- logical name required;
- physical name generated with `$wpdb->prefix` strategy by default;
- only safe identifier characters;
- reserved/core table names blocked;
- charset/collation inherit WordPress DB defaults unless advanced compatibility-approved override.

## Primary key
New application tables strongly require primary key.
- default generated column: `id` BIGINT UNSIGNED AUTO_INCREMENT;
- user may choose another valid key before table creation;
- removing/changing primary key after data exists is high-impact migration.

## Column fields
- name unique in table;
- label optional UI metadata;
- type required;
- nullable default false for primary, true/false selected explicitly elsewhere;
- default `none` unless specified;
- unsigned shown only numeric types;
- auto increment only integer key-compatible type;
- sensitive/PII classification optional but encouraged.

## Types
Exact physical mapping depends on DB compatibility ADR. UI exposes portable logical types first:
- integer
- bigint
- decimal
- boolean
- short text
- text
- long text
- date
- time
- datetime
- timestamp
- JSON/structured
- binary advanced.

Provider maps logical to MySQL/MariaDB physical type. UI can reveal physical preview.

## Decimal
- precision/scale validated;
- scale <= precision;
- recommended for money-like exact quantities, but currency semantics remain application layer.

## String
- length required for varchar-like short text;
- sensible default 255;
- index-length compatibility warning.

## Null/default
Invalid combinations blocked, e.g. NOT NULL with default NULL unless DB semantics deliberately permit/normalize.

## Index
- name generated/editable;
- columns ordered;
- duplicate equivalent index warned;
- unique index before creation runs duplicate-data preflight;
- index width/DB compatibility checked.

## Foreign keys
V1 default uses logical relations through Relations Engine. Physical FK option hidden behind compatibility ADR because WordPress ecosystem often lacks consistent FK usage and table engines/upgrade order may conflict.

## Migration planner
Every schema change compiles to migration operations with:
- current schema fingerprint;
- target schema;
- SQL preview for authorized diagnostics;
- risk level;
- estimated affected rows;
- reversible flag;
- preflight;
- restore point recommendation/requirement;
- background path for large change.

Dropping table/column = confirmation Level 3 if data exists.

## Data browser
- server pagination 25 default;
- filter controls typed from schema;
- search only compatible columns with warning for expensive full-text-like scan;
- sort indexed hints;
- create/edit uses Field Schema generated form;
- row delete Level 1 single, Level 2 bulk;
- protected/system columns may be read-only.

## Query console
Safe mode only:
- SELECT single statement;
- EXPLAIN single statement;
- prepared named parameters;
- result cap 1000 hard planning maximum;
- execution timeout/budget;
- no `INTO OUTFILE`, locking/destructive side effects or stacked statements.

Developer destructive console is not part of v1 acceptance.

## Import/export
- CSV/JSON;
- schema-aware validation;
- upsert key explicit;
- chunked run;
- errors downloadable;
- export formula injection protection.

## Diagnostics
- schema drift
- missing/extra index
- table engine/collation mismatch
- invalid serialized legacy data warning only if detected
- row count/size estimates
- orphan logical relation records.

## Tests
- fresh create
- migration rollback fixture
- duplicate unique preflight
- large import resume
- SQL injection console parameters
- drop safeguards.

---

# 8. Admin Columns Builder — Pro

## Column Set
One definition targets one list-table adapter + optional audience conditions.

Defaults:
- status Draft;
- view applies to all permitted users unless audience rules added;
- native columns imported as references rather than copied values where possible.

## Column identity
- label required except icon-only accessibility-safe column where aria label required;
- key immutable;
- width auto by default;
- alignment inherit/left;
- sticky false;
- responsive priority auto.

## Source contract
Each source adapter declares:
- output type;
- sortability;
- filterability;
- editability;
- expense score;
- required permissions.

## Meta/field
- choose field definition or raw meta key advanced;
- raw protected meta hidden unless privileged and explicitly safe;
- multiple values rendering strategy select first/all/count.

## Taxonomy
- taxonomy required;
- display names/slugs/count;
- separator default comma;
- links to filtered list optional.

## Relation
- relation + direction;
- field displayed from target;
- max items default 3;
- overflow `+N` optional;
- count-only optimized mode.

## Query
- query UUID;
- parameter map;
- aggregate/single/multiple output;
- expensive query warning;
- per-row execution is rejected or converted to batch strategy if it would cause N+1.

## Media/image
- source media ID;
- thumbnail size selected from registered sizes;
- default 40px display;
- alt fallback rules.

## Computed/token
Only allowlisted expression/token engine, no PHP.

## Shortcode/block
Disabled by default for new columns because potentially expensive/side-effectful. Enabling shows performance/sanitization warning and only server-renderable registered content accepted.

## Format
Default inferred from source type.
- text escaped;
- HTML uses allowed sanitized renderer;
- dates site timezone/display format configurable;
- currency requires currency source or selected code;
- percentage input semantics raw fraction vs percent explicitly selected;
- boolean has text/accessible tooltip alongside icon if icon-only.

## Sorting
Only enabled when source adapter declares stable backend sort implementation. UI never marks a client-only sort as true list-table sorting.

## Filtering
Control type inferred:
- text
- select
- multi-select
- date range
- number range
- boolean.

Filter query validated and delegated to source adapter.

## Inline edit
Default false.
- source must declare writable;
- actor must be authorized for each row;
- edited field uses same validator as canonical field/data source;
- bulk edit preview affected count.

## Conditional formatting
Conditions use shared engine; presentation only. It must not hide underlying unauthorized data because authorization belongs to source/policy.

## Export
Raw vs formatted explicit. Default raw for machine data, human-formatted optionally.

## Performance budget
Builder calculates risk score. Columns may choose lazy REST fetch only if list UX stays accessible and per-row authorization remains correct.

## Tests
- sorting/filtering
- inline permission
- CSV injection
- N+1 detection
- user-role view isolation
- unsupported third-party table degradation.

---

# 9. Dynamic Listings / Template Builder — Pro

## Definition
A Listing = Query/Data Source + item renderer + interaction controls. It is not a full page builder.

## Source
Required before Publish.
- query UUID preferred;
- direct simple Data Source query allowed through generated internal Query definition/reference rather than a separate query language.

## Layout defaults
- Grid default;
- desktop columns 3, tablet 2, mobile 1 as initial UX defaults subject to design-system review;
- gap uses design token default;
- semantic wrapper `div` unless selected list/table requires proper semantics.

## Table mode
- column definitions use renderer fields;
- semantic `<table>` only when data is tabular;
- responsive horizontal scroll or card transformation must preserve header associations.

## Item blocks
Initial approved primitives:
- text/heading
- image/media
- field/token
- link/button
- badge
- icon
- divider
- group/container
- conditional wrapper
- nested listing limited depth.

No arbitrary raw PHP/JS.

## Link/action
Target modes:
- current entity permalink
- dynamic URL field
- internal route
- registered Ability action through secure handler.

External URL validated; target blank automatically receives safe rel attributes.

## Conditions
- show/hide primitive based on current entity/context;
- server evaluated for secure/SEO rendering;
- client-only conditions only for non-sensitive UX state.

## Filters
Listing may attach Query parameter controls.
- each control maps to declared query parameter;
- URL sync default on for shareable search/filter pages, configurable off;
- unknown URL values validated/ignored safely.

## Search
- explicit query parameter binding;
- debounce;
- min characters optional;
- accessible submit fallback where needed.

## Pagination
Default numbered for server render.
- numbered
- prev/next
- load more
- infinite scroll.

Infinite scroll requires accessible load-more fallback and history/URL strategy.

## Loading
SSR initial page should not show unnecessary skeleton. Skeleton used on client transitions. Empty and error templates required before Publish or inherit safe defaults.

## Caching
Cache off by default for personalized/authorized listings. Public deterministic listings can opt into cache with query-context key/invalidation.

## Builder adapters
Gutenberg/shortcode initial. Elementor/Bricks/etc. embed listing by UUID and controls; they do not duplicate listing schema.

## SEO
- default server-rendered initial content;
- pagination canonical/index rules follow site SEO integration, not hardcoded assumptions;
- schema markup only via validated registered schema component.

## Tests
- SSR unauthorized data leak
- filter parameter validation
- pagination limits
- responsive/accessibility
- nested depth
- cache user isolation
- adapter missing.

---

# Data & Query specification status

These four modules are **Specified at Phase 0 behavioral level**. Implementation remains blocked by global compatibility/storage/build/CI decisions and benchmark spikes.
