# WPEssential — Definition Repository Physical Schema Alternatives

Status: **Phase 0 paper architecture / Proposed / no DDL or migration authorized**  
Related: ADR-0008, `DEFINITION-REPOSITORY-CANDIDATE-SCHEMA.md`

## 1. Goal
Choose a physical database shape for WPEssential configuration definitions that supports:
- stable portable identity;
- Draft vs Published state;
- immutable revisions;
- optimistic editing;
- dependency graph;
- import/export;
- module lifecycle;
- 10k–100k+ definitions without forcing runtime site data into the same store.

Definition Repository stores **configuration**, not posts/users/membership enrollments/form entries/chat messages/custom-table rows.

---

# 2. Invariants already accepted at paper level

Regardless of exact DDL:
- local numeric primary keys for efficient DB joins are allowed;
- stable UUID is portable/public identity;
- module/type/key identity is explicit;
- revisions are immutable after creation;
- current draft/edit revision and published revision are separate pointers;
- dependency relationships are queryable without parsing every payload;
- module payload is typed/versioned;
- unknown future schema is not silently discarded;
- optimistic concurrency prevents blind editor overwrite;
- definitions can be disabled/archived without deleting historical revisions;
- runtime data is not universal EAV inside Definition Repository.

---

# 3. Alternative A — identity table + immutable revision table + dependency table

## Definitions identity/current-state table
Logical columns:
- `id` numeric PK;
- `uuid` unique;
- `module`;
- `definition_type`;
- `definition_key`;
- `status`;
- `current_revision_id`;
- `published_revision_id` nullable;
- `version_counter` optimistic concurrency;
- `owner_scope` site/network;
- `created_at`;
- `updated_at`;
- `archived_at` nullable;
- selected normalized list/filter fields only if proven.

## Revisions table
- `id` numeric PK;
- `uuid` unique optional/stable revision identity;
- `definition_id`;
- `revision_number` or monotonic version;
- `schema_version`;
- `payload` JSON/LONGTEXT candidate;
- `payload_hash`;
- author/principal;
- reason/message;
- created_at;
- publication metadata optional;
- source/import provenance safe metadata.

## Dependencies table
- source definition/revision;
- target kind definition/module/external resource;
- target UUID/key;
- dependency type hard/soft/runtime/template/etc.;
- path/context;
- created/compiled generation.

### Pros
- clear lifecycle;
- immutable revision history;
- fast current/published lookup;
- dependency impact queries;
- import/export alignment;
- module payload flexibility.

### Cons
- payload-level filtering requires normalized columns/index or generated/index strategy;
- revision growth/retention management;
- pointer integrity needs careful migrations/transactions.

**Current preferred candidate.**

---

# 4. Alternative B — WordPress Custom Post Type for definitions + post revisions/meta

Each definition represented as hidden/non-public CPT.

### Pros
- WordPress post/revision APIs;
- built-in IDs/status/meta ecosystem;
- familiar admin/database structure;
- import/export ecosystem potentially useful.

### Cons
- WordPress post statuses/revisions do not map cleanly to every module publication model;
- wp_posts/wp_postmeta become overloaded with potentially tens of thousands of technical definitions;
- dependency graph/published-current pointers awkward;
- network scope/custom schema versioning less direct;
- meta query performance risks;
- third-party hooks/plugins may interfere;
- internal configuration is not really content.

Not preferred for WPE platform-wide Definition Repository.

---

# 5. Alternative C — single table with mutable JSON payload + revision blob history

Identity and current payload live in one mutable row; old payloads in history table.

### Pros
- simple current reads;
- fewer pointer joins;
- straightforward initial DDL.

### Cons
- draft vs published separation gets messy;
- mutable current row can alter runtime before publish unless duplicated fields are added;
- concurrency/history semantics weaker;
- rollback/publish transactions harder to reason about;
- imports/preview need more state copying.

Rejected as preferred model because WPE builders need safe Draft vs Published behavior.

---

# 6. Alternative D — one fully normalized schema per module

Every module creates custom definition tables/columns.

### Pros
- perfect module-specific indexes/types;
- no JSON parsing for structured fields;
- strong DB constraints.

### Cons
- dozens of migration systems;
- duplicate revision/publish/import/dependency logic;
- hard extension SDK;
- brittle cross-module relationships;
- large maintenance surface;
- module disable/uninstall complexity.

Rejected as platform default. A module may own separate **runtime** tables when justified; definition lifecycle remains shared.

---

# 7. Current recommendation

Benchmark **Alternative A** as primary:
1. identity/lifecycle table;
2. immutable revision table;
3. revision-aware dependency edge table;
4. optional normalized/index projection only for proven high-value list/filter fields;
5. module compilers maintain derived runtime caches/artifacts outside source definition rows where needed.

Do not create a generic key/value definition-meta table by default; it risks recreating postmeta-style query problems without strong need.

---

# 8. Definition identity constraints

Candidate uniqueness:
- UUID globally unique within site/network repository;
- `(module, definition_type, definition_key, scope)` unique for active/non-archived definitions where DB strategy can enforce;
- normalized key lowercase machine slug;
- display name not unique.

Key rename:
- allowed only with dependency impact preview;
- UUID remains identity;
- aliases/migration map optional where public shortcodes/tokens depend on key;
- do not create a new definition simply because display label changes.

---

# 9. Draft / Published lifecycle

Candidate definition statuses:
- draft;
- published;
- disabled;
- archived;
- degraded/invalid runtime health is separate from authoring status where possible.

Pointers:
- `current_revision_id` = latest saved editor revision;
- `published_revision_id` = runtime revision;
- Draft save updates only current pointer;
- Publish transaction validates + sets published pointer;
- Unpublish/disable semantics are module-specific but do not delete revision;
- Rollback creates/selects a new current revision derived from historical payload, preserving immutable history.

Runtime compilers use published revision only.

---

# 10. Revision write transaction

Candidate save flow:
1. read identity row + version counter;
2. validate client expected version;
3. validate payload against module schema;
4. normalize payload;
5. write immutable revision;
6. extract dependency edges for that revision;
7. update current pointer + version counter;
8. commit;
9. invalidate editor/list caches;
10. emit saved event.

Publish flow adds:
- publish-time validation;
- dependency availability/cycle checks;
- compile preview;
- update published pointer;
- derived artifact/cache generation semantics according module.

No half-written revision/pointer state.

---

# 11. Payload storage format

Candidate: JSON text document with explicit module schema version.

Why:
- flexible typed builder configuration;
- portable packages;
- human/debuggable exports;
- avoids dozens of sparse columns.

Rules:
- canonical serialization/hash strategy for diff/cache only after accepted implementation profile;
- payload size limits by definition type;
- no PHP serialized objects;
- no closures/callback code;
- secret values are Vault refs;
- binary/blob content stored as Media/Object refs;
- unknown future fields preserved in read-only/import states where safe.

MySQL native JSON vs LONGTEXT:
- must consider supported MySQL/MariaDB matrix before accepting physical column type;
- application-level schema remains JSON regardless of physical storage type.

---

# 12. Normalized/index fields

Do not normalize every payload property preemptively.

Potential identity-table indexable fields only if product screens prove need:
- module;
- type;
- status;
- key;
- updated_at;
- published yes/no;
- owner scope/site ID;
- health/dependency state only if not better derived cache.

Module-specific searchable labels/tags may use:
- selected shared columns;
- search index table/service;
- generated projection table;
- not ad-hoc JSON LIKE scans at scale.

Benchmark before adding indexes for hypothetical filters.

---

# 13. Dependency graph physical model

Dependency rows should reference **source revision**, because Draft and Published revisions can depend on different targets.

Fields conceptually:
- source definition ID/UUID;
- source revision ID;
- target type;
- target UUID/key;
- dependency class hard/soft/optional/runtime/display;
- property/path that created dependency;
- target module/type;
- resolution state;
- compiled generation.

Use cases:
- “What breaks if I delete Field Group X?”
- module disable impact;
- import order;
- package dependencies;
- publish validation;
- safe cleanup of old revisions;
- cyclic dependency detection.

---

# 14. Old revision retention

Options can eventually be global/module-specific:
- keep all revisions;
- keep last N;
- keep revisions for duration;
- always keep published revisions referenced by runtime/history;
- keep release/export checkpoints;
- legal/audit hold only if real requirement.

Cleanup cannot delete:
- current revision;
- published revision;
- revision pinned by retained Workflow run or other accepted runtime reference;
- migration rollback checkpoint.

Revision retention is background/chunked.

---

# 15. Diff model

Diff is computed from schema-aware JSON, not plain text only.

Desired output:
- field added/removed/changed;
- array item reorder vs edit;
- dependency change;
- security/capability change highlighted;
- destructive schema change highlighted;
- secret reference changed without exposing secret;
- derived impact summary.

Do not persist giant text diffs for every revision unless benchmark/product need justifies; immutable payloads can be diff source.

---

# 16. Import/export

Portable package includes:
- definition UUID;
- type/module;
- key/name;
- schema version;
- payload;
- dependency refs;
- source revision/provenance metadata;
- checksums;
- no local numeric DB IDs.

Import modes:
- create;
- update same UUID;
- clone with new UUID;
- defer until dependency available;
- conflict/manual mapping.

Imported revision does not automatically publish unless selected/validated.

Future-version payload:
- preserve/read-only/deferred where possible;
- never drop unknown required semantics and claim success.

---

# 17. Multisite

Physical alternatives:
A. per-site WPE definition tables;
B. network-global tables with `site_id/network_id` scope.

Current paper preference depends on whether WPE uses custom tables via `dbDelta`/prefix per site and expected network definition sharing.

Product direction:
- site-local definitions by default;
- network-shared definition library only if explicitly designed;
- identical UUID across sites does not imply shared mutable object unless network mode says so.

Multisite table strategy is part of benchmark/compatibility blocker.

---

# 18. Deletion/tombstones

Delete options:
- archive definition default safer;
- permanent delete only after dependency impact + capability;
- revisions may remain per retention/audit;
- tombstone with UUID/key/type can prevent silent dependency remap and explain missing target;
- portable import can explicitly restore/recreate.

Never reuse a deleted UUID for unrelated definition.

---

# 19. Health / compiled artifacts

Source definition status must not be overloaded with every derived artifact error.

Potential separate derived state/cache:
- last compile revision;
- compile status;
- validation error summary;
- dependency health;
- runtime artifact version;
- last successful publish.

If compiled cache missing:
- rebuild from published revision;
- source definition remains authoritative.

---

# 20. Permissions

Repository service enforces module capabilities/policies supplied by module contract.

Generic repository does not expose “edit any definition” to callers simply because they know UUID.

Checks:
- module read/edit/publish/delete;
- resource ownership/policy;
- Pro/license management state for editing;
- imports;
- revision history visibility;
- sensitive payload annotations.

Published runtime reads may use compiled artifacts rather than granting generic repository access.

---

# 21. Failure/recovery

States:
- payload schema invalid;
- current pointer missing/corrupt;
- published pointer invalid;
- dependency unresolved;
- optimistic conflict;
- revision write succeeded but pointer update failed → transaction rollback required;
- future schema unsupported;
- module unavailable;
- migration partially complete;
- DB/index corrupted.

Recovery tools:
- validate identity/revision pointers;
- rebuild dependencies;
- rebuild compiled artifacts;
- roll current pointer to known revision by creating recovery revision;
- export raw safe package for support;
- never auto-delete corrupted history without explicit repair plan.

---

# 22. Future benchmark protocol — NOT AUTHORIZED

Compare at least:
- Alternative A custom identity/revision/dependency tables;
- hidden CPT/revisions baseline where practical;
- mutable-current-row variant for read/write cost reference.

Fixtures:
- 10k definitions / 100k revisions;
- 100k definitions / 1M revisions stress candidate;
- dependencies average 5–20/definition;
- list/filter/search;
- save/publish;
- optimistic concurrent edits;
- dependency impact traversal;
- import 1k definitions;
- cleanup old revisions;
- module disable impact;
- multisite fixture if supported.

Measure:
- DB/storage size;
- list/read/save/publish latency;
- query count;
- dependency traversal;
- index size;
- cleanup throughput;
- lock contention;
- migration cost.

No DDL/migration/benchmark implementation is authorized before owner consent.

## Current recommendation
Proceed to future benchmark with **Alternative A** as primary candidate. The paper architecture is strong enough to design benchmark fixtures, but exact table names/types/indexes and MySQL JSON/LONGTEXT choice remain Proposed until compatibility/benchmark evidence exists.