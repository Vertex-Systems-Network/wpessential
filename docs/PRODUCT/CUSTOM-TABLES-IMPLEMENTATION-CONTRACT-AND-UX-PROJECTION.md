# WPEssential — Surface 7 Custom Tables / Content Tables Implementation Contract & UX Projection

Status: **Planning contract / implementation not authorized**  
Surface: **7 — `tables`**  
Snapshot: **2026-09-02**  
Options Bank candidate: **165 records**

## Purpose

This contract projects the reviewed Surface 7 Options Bank into an implementation-ready product boundary without authorizing executable database schema, DDL, migration runners, shared database abstractions, or production runtime changes.

Surface 7 owns **custom/content table definition** and **table-backed Data Source semantics**. It does not become a generic DBA console.

## Canonical ownership

Surface 7 owns:

- stable table identity and logical definition;
- portable column/storage shape at the table layer;
- primary key, unique and index declaration semantics;
- physical topology requirements and site/network scope;
- desired schema version and observed-schema fingerprint references;
- table-backed CRUD capability declarations;
- bounded pagination/batch capabilities;
- table lifecycle diagnostics and drift state;
- provider compatibility/adoption metadata.

Surface 7 consumes, but does not own:

- **Fields (Surface 3):** field type, validation, presentation and field-value business semantics;
- **Relations (Surface 4):** relation/cardinality/edge identity and relation mutation;
- **Query (Surface 6):** reusable query AST, operators, joins, compilation and execution semantics;
- **Admin Columns (Surface 8):** list-view presentation and column/view configuration;
- **Import / Export:** package/file orchestration, mapping and resumable run semantics;
- **Backup:** snapshot, restore and backup-provider certification;
- **Policy / Roles:** authorization decisions and role/capability definitions;
- **Ledger:** accounting/ledger transaction semantics;
- shared migration framework and global DB provider abstractions.

Any required change in those owners is an **Integration Requirement**, not a Surface 7 implementation shortcut.

## Hard safety boundaries

The product must reject or omit:

1. arbitrary SQL consoles;
2. arbitrary DDL execution;
3. direct DROP/TRUNCATE controls presented as ordinary table actions;
4. unchecked user/request-supplied SQL identifiers;
5. WordPress core-table adoption or mutation;
6. capability/Policy bypass;
7. implicit cross-site data access;
8. unvalidated row mutation;
9. unbounded request-time exports or select-all mutations;
10. destructive migration without a policy-compliant recovery path;
11. arbitrary PHP/eval/raw-SQL migration transforms;
12. arbitrary external database credentials as a generic data-source escape hatch;
13. autonomous destructive AI schema mutation.

Definition deletion must never silently imply physical data destruction.

## Definition contract

A published table definition is desired state and contains, at minimum:

- stable UUID;
- logical key and human label;
- owning module;
- storage mode (`managed_table` or inspected external table);
- schema document format version;
- desired schema version;
- physical-name strategy and resolved identity reference;
- site/network scope;
- charset/collation requirements;
- columns;
- primary key;
- indexes and unique constraints;
- data classification;
- Data Source key/provider capability declaration.

Logical names are portable registry values. Physical identifiers are resolved through the current WordPress/database topology and are never assembled from unchecked request text.

## Column contract

Portable column families are:

`bool`, `int`, `bigint`, `decimal`, `float`, `string`, `text`, `date`, `time`, `datetime`, `json`, `blob`.

A column can declare length, precision, scale, nullability, structured defaults, unsigned numeric semantics, generated-id participation and timestamp roles. Database-native generated columns, foreign keys, CHECK constraints and native ENUM remain compatibility-gated/deferred until the certified MySQL/MariaDB profile proves their semantics and migration safety.

Field bindings reference Surface 3 and do not duplicate Field schema or validation controls.

## Key and index contract

Every managed record model must expose a deterministic identity strategy. Primary keys, unique constraints and indexes use stable logical IDs independent of provider-specific physical names.

Before a unique/index migration can be approved, the future planner must be capable of validating applicable preconditions such as duplicate state, NULL behavior, key-length compatibility, column existence/type, selectivity/redundancy risk and active query dependencies.

## Data Source contract

A table-backed Data Source may declare:

- schema/introspection;
- get/list/exists/count;
- create/update/delete;
- bounded projection;
- deterministic default ordering;
- declared sortable/filterable/searchable fields;
- bounded offset pagination;
- future capability-gated cursor pagination;
- bounded batch read/write;
- provider adapter identity;
- transaction capability facts;
- future optimistic-concurrency token support.

Surface 7 does not invent its own reusable filter/query language. Query expressions are owned by Surface 6 and compiled only through approved adapters.

## Admin UX projection

### Information architecture

The recommended primary navigation is:

1. **Tables** — definitions and health summary;
2. **Table Designer** — logical schema editor;
3. **Data** — bounded row browser;
4. **Schema Changes** — observed-vs-desired diff and migration-plan projection;
5. **Health** — drift, provider compatibility, size/growth and dependency warnings;
6. **Integrations** — Fields, Relations, Query, Admin Columns, REST/Abilities, Import/Export and Backup references.

### Tables list

Each table card/row should show:

- label and logical key;
- site/network scope;
- managed vs inspected-external state;
- desired/applied schema version status;
- physical health/drift badge;
- approximate row/size telemetry when available;
- Data Source/provider adapter state;
- blocked migration/recovery warnings.

Dangerous actions are not placed in the primary action row.

### Table Designer

Designer editing is logical and typed. It must not expose an SQL textarea. Changes produce a **draft definition revision**, not an immediate database mutation.

Progressive disclosure:

- Essential: label/key, columns, identity, required indexes;
- Advanced: null/default/length/precision, site/network scope, Data Source capabilities;
- Expert: compatibility-sensitive indexes, external adoption, physical storage hints;
- System: observed fingerprints, provider capability details and migration diagnostics.

### Data browser

The row browser is bounded by server-enforced page/batch limits. Row create/edit/delete consumes Policy and Data Source validation. Bulk destructive actions require explicit impact preview and confirmation. “Select all matching records” may only exist when a bounded, resumable job contract owns the operation; it is never an unbounded synchronous request.

### Schema Changes

The future migration UI must display:

- source observed fingerprint/version;
- target definition revision/version;
- deterministic typed operations;
- R0–R4 risk;
- precondition results;
- affected-row/availability estimates where available;
- dependency impact;
- restore-point requirement/reference;
- reversibility/recovery truth;
- post-run verification contract.

For R3/R4 changes, use **Recovery** wording instead of promising transactional rollback.

## Migration model

This contract adopts the existing repository paper architecture:

`observed physical state → typed diff → guarded migration plan → explicit review → future authorized provider compiler → post-operation verification`.

Required characteristics for any later implementation package:

- deterministic and idempotent planning;
- no plan execution against a changed source fingerprint without revalidation;
- expand → migrate/backfill → contract where compatibility demands it;
- chunked/resumable/idempotent large backfills;
- one active conflicting migration per owned physical target;
- typed preconditions;
- no silent truncation or lossy conversion;
- explicit drift response rather than automatic overwrite;
- recovery strategy matched to real DDL transaction limitations.

This document does **not** authorize implementation of that executor/compiler.

## Multisite

Scope is explicit. A table is either site-scoped or specifically designed network-global. Cross-site reads/writes are deny-by-default and require canonical Policy plus topology support. Network migrations must not synchronously iterate an unbounded number of sites in one web request.

## REST and Abilities

REST read and mutation exposure are separate opt-ins and require permission callbacks plus server-side Data Source bounds. Nonces are request-intent/CSRF protection, not authorization.

Future WordPress Abilities integration should expose typed, discoverable operations with read-only/destructive/idempotency metadata where supported. AI may help draft a schema or migration proposal, but destructive actions remain human/policy/recovery gated; autonomous destructive execution is prohibited.

## Provider compatibility

### WordPress / `$wpdb` / `dbDelta()`

Use WordPress prefix/topology APIs and validated identifier handling. `dbDelta()` can be a compiler tool for compatible create/add/change cases, but it is not assumed to cover every rename/drop/constraint/high-risk change.

### MySQL / MariaDB

A future certified capability profile must define supported server floors and facts for DDL algorithms, generated/check/FK behavior, charset/collation, index limits, JSON behavior, transaction boundaries and introspection. Portable logical definitions must not bake in environment-specific generated SQL.

### WooCommerce HPOS

Integrate only through supported WooCommerce CRUD/data-store authority. HPOS authoritative/backup storage and synchronization patterns are useful compatibility evidence, not permission to directly mutate Woo-owned physical tables.

### Meta Box / JetEngine / Pods

Adapters may discover/import compatible definitions or bind existing table-backed entities, but provider field/relation/query semantics remain with their canonical WPE owners. External provider tables begin read-only unless an explicit adoption contract proves ownership and exit/recovery semantics.

## Import/export and backup

Portable export contains logical desired schema and safe metadata, not environment-specific generated SQL or secrets. Data import/export is delegated to the canonical Import/Export run engine and remains bounded/checkpointable.

High-risk physical changes consume a verified Backup restore-point reference. Surface 7 never implements the generic Backup engine.

## Observability and diagnostics

Future runtime observability should expose identifiers and operational metadata, not arbitrary row values:

- definition/revision/table identifiers;
- desired/applied/observed schema state;
- migration plan/run references;
- operation/risk/precondition state;
- warnings/errors and durations;
- row-count/size bands where safe;
- DB capability profile;
- backup reference;
- resulting schema fingerprint.

## Performance envelope

Any future implementation package must establish measured budgets before runtime release. At minimum, tests must cover:

- bounded list and CRUD behavior;
- no hidden unbounded admin queries;
- pagination determinism;
- index-backed common sort/filter projections;
- 10k / 100k / 1M row planning scenarios;
- chunked backfill behavior;
- migration lock contention;
- large-table recovery/disk-space preconditions;
- multisite provisioning without unbounded synchronous loops.

No performance claim is inferred from competitor marketing.

## Required test matrix for an authorized runtime package

### Definition and schema
- valid/invalid identifiers;
- all portable types;
- null/default combinations;
- PK/unique/index validation;
- stable serialization and fingerprints;
- provider capability rejection.

### CRUD/Data Source
- create/read/update/delete;
- validation failures;
- deterministic pagination;
- page/batch caps cannot be bypassed;
- optimistic conflict behavior when supported;
- cross-site denial.

### Migration/recovery
- clean create from empty DB;
- no-op diff determinism;
- additive schema change;
- safe NOT NULL backfill;
- duplicate block before unique;
- rename dependency detection;
- narrowing/type-loss detection;
- interrupted resumable backfill;
- concurrent migration rejection;
- observed-fingerprint invalidation;
- failed migration recovery;
- manual drift;
- post-run schema fingerprint verification.

### Security
- raw identifiers rejected;
- SQL/DDL console absent;
- Policy enforced server-side;
- nonce without capability is insufficient;
- unbounded export/mutation rejected;
- destructive operation blocked without required recovery;
- core/plugin-owned table adoption blocked by default;
- AI cannot autonomously execute destructive migration.

### Compatibility
- certified WordPress versions;
- certified MySQL/MariaDB matrix;
- single-site and multisite;
- WooCommerce HPOS adapter behavior;
- Meta Box / JetEngine / Pods read/adoption mappings where enabled.

## Rollout / rollback

A future runtime delivery should be milestone-gated:

1. read-only definition/introspection model;
2. table-backed Data Source adapter contract;
3. safe create/additive migration subset;
4. resumable data transition subset;
5. guarded high-risk changes only after Backup/recovery certification;
6. provider adapters and advanced compatibility.

Feature flags must permit disabling mutation/migration execution while retaining diagnostic/read-only access. Rollback plans must distinguish code rollback from physical data recovery.

## Integration Requirements

The following are deliberately outside this Agent 5 branch authority:

1. **Canonical Options Bank progress promotion:** shared `config/product/options-bank-progress.json` must be advanced through the repository lifecycle and ultimately set Surface 7 to `BANK_REVIEWED`, record count `165`, by the authorized integration owner.
2. **Composer / CI registration:** Surface 7 standalone smoke validators require shared test-runner registration by the integration owner if the repository expects automatic execution.
3. **Shared Data Source core:** any new generic capability declaration needed by table-backed adapters must be separately reviewed by its owner.
4. **Shared migration framework / DB abstractions:** no change is authorized by this planning package.
5. **Policy, Backup, Query, Fields, Relations, Admin Columns, Import/Export:** integrations must use their owner contracts; no bypass implementations are permitted.

## Bank review readiness

Surface-local evidence is complete at this candidate:

- Bank: 165 normalized and classified records;
- native audit: `NATIVE_AUDITED`, zero unresolved;
- market audit: `MARKET_AUDITED`, zero unresolved;
- semantic duplicate/effective-derivation expectation: zero;
- unsafe/deferred/WPE-exceed policy consistency: closed;
- runtime implementation: intentionally not authorized.

The review certificate remains `REVIEW_BLOCKED` only until the authorized shared canonical-progress integration is performed. That blocker is administrative/integration ownership, not unresolved Surface 7 research.
