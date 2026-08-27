# WPEssential — Definition Repository Candidate Physical Model

Status: **Phase 0 paper design / Proposed / no migration or table created**  
Related: ADR-0008, D-004

The Definition Repository stores WPEssential **configuration definitions**, not arbitrary site/runtime content.

Examples of definitions:
- CPT configuration;
- taxonomy configuration;
- field group;
- relation definition;
- query;
- listing/template;
- form/workflow;
- dashboard/menu/profile;
- membership plan/access rule;
- REST endpoint;
- backup profile;
- notification/email template.

It must support stable identity, drafts, publishing, revisions, dependencies, import/export and safe module disable/Pro expiry.

No SQL/migration exists yet. Table and index choices require an executable benchmark after explicit owner consent.

---

# 1. Design principles

1. **Stable external identity ≠ database primary key.**
2. Definitions have immutable revisions; identity remains stable across revisions.
3. Draft changes must not silently alter the published runtime definition.
4. Published runtime can continue when Pro/license editing becomes unavailable.
5. Module payloads are typed/versioned; Repository does not understand every module field.
6. Frequently queried list/index properties are normalized; module-specific configuration stays in validated payload.
7. Dependency edges are explicit, not rediscovered through expensive full payload scans on every request.
8. Runtime compiled objects are caches/artifacts, not the source of truth.
9. Import/export operates on stable UUIDs and logical references, never environment-specific auto-increment IDs.
10. Repository is not a universal EAV database.

---

# 2. Candidate logical tables

## A. `wpe_definitions`

Purpose: stable identity and lifecycle pointers.

Candidate columns:

| Column | Purpose |
|---|---|
| `id` | internal `BIGINT UNSIGNED` primary key |
| `uuid` | stable export/import identity, generated through approved UUID strategy |
| `module` | owning module slug |
| `definition_type` | typed definition kind, e.g. `query`, `field_group` |
| `object_key` | human/developer stable local key/slug |
| `title` | list/display title |
| `description` | short optional list metadata, not full payload |
| `lifecycle_status` | draft/published/archived/disabled as defined below |
| `current_revision_id` | latest saved working revision |
| `published_revision_id` | active runtime revision, nullable until first publish |
| `created_by` | creator user ID where applicable |
| `updated_by` | latest editor user ID where applicable |
| `created_at` | UTC created time |
| `updated_at` | UTC identity/working update time |
| `published_at` | latest publish timestamp |
| `archived_at` | archive timestamp if archived |

### Why revision pointers

A published Query/Form/etc. may be edited without changing production behavior until Publish.

Example:

- revision 7 is published;
- admin edits and saves revision 8 as draft;
- `current_revision_id = 8`;
- `published_revision_id = 7`;
- runtime continues compiling revision 7;
- Publish validates revision 8 and atomically moves published pointer.

This avoids storing one mutable JSON blob as both draft and production source.

---

## B. `wpe_definition_revisions`

Purpose: immutable configuration snapshots.

Candidate columns:

| Column | Purpose |
|---|---|
| `id` | internal primary key |
| `definition_id` | parent identity FK-like reference |
| `revision_number` | monotonic per-definition revision |
| `schema_version` | module definition schema version |
| `payload` | validated serialized JSON/document payload |
| `payload_checksum` | integrity/change detection hash |
| `change_type` | create/save/import/migrate/restore/etc. |
| `change_summary` | optional safe human summary |
| `created_by` | actor user ID where applicable |
| `created_at` | UTC timestamp |
| `source_package_uuid` | optional import/migration trace |

### Immutability

Once created, a revision is not edited in place.

Corrections create a new revision.

Benefits:
- deterministic rollback;
- published/draft separation;
- auditability;
- reliable package diff;
- easier concurrency conflict detection.

---

## C. `wpe_definition_dependencies`

Purpose: fast dependency/usage graph.

Candidate columns:

| Column | Purpose |
|---|---|
| `id` | internal primary key |
| `source_definition_id` | definition owning the dependency |
| `source_revision_id` | exact revision that declared it |
| `target_kind` | `definition`, `wp_object`, `module`, `external_adapter`, etc. |
| `target_uuid` | WPEssential target UUID when target is a definition |
| `target_ref` | typed external reference when not UUID |
| `dependency_type` | hard/soft/runtime/optional/visual/etc. |
| `payload_path` | JSON pointer/field path that created dependency where useful |
| `created_at` | UTC extraction timestamp |

### Revision-aware edges

Dependencies should be derived when a revision is saved/validated, not scanned from every payload at page render time.

Published usage graph queries use `published_revision_id`; draft impact analysis can use `current_revision_id`.

This allows:
- `Used by…`;
- block delete if hard published dependencies exist;
- preview impact of draft changes;
- import dependency ordering;
- archive/purge safety.

---

## D. Optional future `wpe_definition_tags` / metadata

Do **not** create by default unless real search/organization use requires it.

Candidate capabilities such as tags/folders/favorites should first be evaluated against:
- payload metadata;
- WordPress user preferences;
- a compact normalized relation table only if querying requires it.

Avoid speculative tables.

---

# 3. Candidate lifecycle states

## `draft`

Definition has no published revision yet.

Runtime does not consume it unless explicit Preview/Test mode.

## `published`

`published_revision_id` points to the active runtime revision.

A newer `current_revision_id` may still exist as an unpublished working revision.

## `disabled`

Definition remains published/configured but its runtime behavior is intentionally disabled where module semantics support disable.

Do not conflate module-disabled and definition-disabled; both states may exist.

## `archived`

Hidden from normal active use/edit lists but retained for recovery/history/dependency handling.

Runtime normally does not consume archived definitions except preserved compiled/public behavior explicitly required by license/module lifecycle contracts.

## hard-deleted/purged

Not a normal lifecycle status.

Purge is a separate destructive operation after:
- dependency check;
- revision/history warning;
- export/backup option;
- permission/re-auth where needed.

---

# 4. Stable IDs

## Internal ID

Use auto-increment numeric IDs for local joins/index efficiency unless benchmark disproves.

Never expose them as portable identity.

## UUID

Use a stable UUID string in packages/APIs/dependency references.

Candidate v1 choice: WordPress-native UUIDv4 generation for dependency simplicity.

Do not add a UUID library unless a justified benefit exists.

Potential future UUIDv7/time-sort advantages do not justify dependency/compatibility complexity by themselves.

## Object key

Human/developer key used in APIs/tokens where appropriate.

Rules:
- normalized lowercase slug-like form unless module requires another grammar;
- unique within an explicitly defined module/type scope;
- changing a key is a migration-impact operation;
- UUID remains stable when key changes.

---

# 5. Payload format

Candidate source format: validated JSON document stored as `LONGTEXT` rather than relying on database-specific native JSON behavior.

Reasons:
- consistent behavior across MySQL/MariaDB support matrix;
- module controls JSON Schema/application validation;
- preserves complete versioned document;
- avoids making DB JSON function behavior part of public architecture.

The benchmark/compatibility decision can revisit native JSON only if it delivers material benefit without harming portability.

Payload contains module-specific details; list-critical fields should be normalized into identity table only when they have proven query value.

Do not duplicate every payload field into columns.

---

# 6. Schema versioning

Each revision records `schema_version` belonging to its definition type/module.

Migration model:

`stored payload version → registered migrator chain → current normalized in-memory schema → optional persisted migrated revision`

Rules:
- never silently mutate historical revisions;
- upgrade can create a new migrated working/published revision according to migration policy;
- migration must be idempotent or version-gated;
- downgrade behavior must be defined before irreversible schema changes;
- missing migrator = degraded/read-only state, not corrupted overwrite.

---

# 7. Save/publish concurrency

Candidate optimistic concurrency:

Editor loads:
- definition UUID;
- current revision ID/number/checksum.

On save:
- server verifies expected current revision;
- if changed by another actor/session, return conflict;
- do not last-write-wins overwrite silently;
- user can reload/compare/merge where module UI supports it.

Publish:
- validates target current revision;
- validates dependencies;
- compiles/validates runtime representation;
- atomically moves published pointer where possible;
- emits definition-published event after commit.

---

# 8. Dependency semantics

## Hard dependency

Source cannot safely function/publish without target.

Examples:
- Listing requires Query definition;
- REST endpoint requires bound Query/Ability;
- Membership rule requires an entitlement/plan definition.

Delete/archive target must show blockers.

## Soft dependency

Feature degrades but definition remains meaningful.

Example:
- optional Email branding layout.

## Runtime/external dependency

References:
- registered post type owned by another plugin;
- page builder template;
- provider connection;
- WooCommerce product.

Repository cannot guarantee external target lifetime; diagnostics must surface missing references.

---

# 9. Import/export behavior

Package identity uses UUIDs.

On import:

### UUID does not exist
Create new identity using package UUID, subject to security/type validation.

### UUID exists
Treat as same logical definition candidate; show conflict/version strategy.

### UUID differs but object key collides
Do not overwrite automatically. Options:
- map to existing;
- generate new key;
- skip;
- replace only with explicit confirmation and compatibility check.

### Internal DB IDs
Never imported as portable references.

Dependencies are remapped by UUID/logical refs.

---

# 10. Revision retention

Do not store unlimited revisions forever without policy, but do not prune critical history blindly.

Candidate default retention model:
- always keep currently published revision;
- always keep current working revision;
- keep pinned/release/migration restore revisions;
- keep configurable recent N revisions;
- prune older unpinned revisions via background maintenance after Backup/Job services are available;
- never prune during a request synchronously at scale.

Exact defaults remain open.

---

# 11. License expiry and module disable

Definition Repository stays readable independent of Pro entitlement so:
- existing safe runtime can resolve last published configuration;
- export remains available;
- diagnostics can explain locked modules;
- reactivation does not recreate configuration.

Editing/publishing is controlled by module entitlement/policy, not by deleting Repository rows.

Module disable:
- unregisters runtime hooks/assets/jobs;
- preserves definitions/revisions/dependencies;
- re-enable resumes from preserved configuration after health/version validation.

---

# 12. Runtime read path

Normal runtime should not repeatedly decode every definition/revision.

Candidate sequence:

1. module asks Definition Registry for published object by UUID/key;
2. Registry fetches identity + published revision;
3. validates/normalizes schema version;
4. compiles into typed immutable runtime object;
5. request-local/object-cache layer caches by definition UUID + published revision/checksum;
6. publish/migrate/disable events invalidate relevant compiled cache.

Cache is derivative. Database revision remains source of truth.

---

# 13. Index candidates

Exact DDL waits for benchmark.

Queries requiring indexes:
- UUID lookup;
- module + definition type + status list;
- module/type/object-key lookup;
- updated/published ordering;
- revisions by definition + revision number;
- dependency edges by source revision;
- reverse dependency lookup by target UUID/ref.

Potential index shapes should be benchmarked against:
- 10k definitions;
- 100k definitions stress fixture;
- 10 revisions/definition;
- high dependency-edge fan-out.

Do not add speculative indexes without query evidence.

---

# 14. Multisite candidate

Default direction: standard site-local tables using each site's WordPress prefix, unless a module explicitly provides network-scoped definitions.

Reasons:
- strongest tenant/site isolation;
- simpler site export/delete;
- avoids implicit cross-blog data leakage.

Network-shared definitions are a separate feature requiring:
- network capabilities;
- inheritance/override semantics;
- global tables or explicit network storage;
- site mapping;
- cache invalidation across blogs.

Do not silently put all definitions in one network-global table.

---

# 15. Failure/degraded behavior

Examples:

### Current revision missing
Definition unhealthy; do not overwrite. Diagnostics + recovery from revision history/backup.

### Published revision missing
Runtime must fail safe for that definition; alert diagnostics. Never silently publish current draft as replacement.

### Unknown schema version
Read-only/degraded; require compatible module/migrator.

### Broken dependency
Show unhealthy/degraded state and exact missing target.

### Invalid payload checksum/parse
Do not execute; mark corruption/error, preserve raw data for recovery.

---

# 16. Candidate table ownership

Kernel owns Repository tables and contracts.

Individual modules:
- register definition types/schema/migrators;
- validate payload;
- extract dependencies;
- compile runtime object;
- provide import/export transformations.

Modules must not create their own private definition/revision system.

Runtime business data still belongs to the module's appropriate data store.

Examples:
- Form definition → Repository;
- Form submissions → Forms runtime tables;
- Membership Plan definition → Repository;
- Membership Enrollments → Membership runtime tables;
- Chat settings → Repository;
- Chat messages → Chat runtime tables.

---

# 17. Benchmark plan — NOT AUTHORIZED

Future executable spike should create candidate schemas and compare:
- identity lookup;
- module/type listing pagination;
- revision save/publish;
- reverse dependency query;
- import UUID collision lookup;
- 10k / 100k definitions;
- revision growth;
- object-cache on/off;
- MySQL/MariaDB supported matrix.

Measure:
- query count;
- latency percentiles;
- storage/index size;
- publish transaction duration;
- reverse dependency performance;
- migration behavior.

Under ADR-0014 this benchmark/migration spike requires explicit owner consent and must not be executed yet.

---

# 18. Current recommendation

The strongest paper model is:

- numeric local primary key;
- stable UUID external identity;
- identity/lifecycle table;
- immutable revision table;
- revision-aware dependency edge table;
- `current_revision_id` + `published_revision_id` pointers;
- validated versioned JSON/LONGTEXT module payload;
- normalized only for proven index/list fields;
- site-local storage by default;
- typed module registries/migrators/compilers around the Repository.

This remains **Proposed** until D-004 benchmark evidence is authorized and completed.
