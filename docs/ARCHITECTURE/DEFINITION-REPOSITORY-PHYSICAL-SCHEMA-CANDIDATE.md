# WPEssential — Definition Repository Physical Schema Candidate

Status: **Phase 0 paper architecture / no table or migration authorized**  
Related: ADR-0008, Definition Repository schema alternatives, ADR-0014.

## Goal

Narrow the Definition Repository from generic “identity + revisions + dependencies” into a concrete relational shape that can later be benchmarked without prematurely creating database tables.

## Accepted logical table family

Use three logical stores:

1. **Definitions** — stable identity/lifecycle/current pointers.
2. **Definition Revisions** — immutable versioned payloads.
3. **Definition Dependencies** — revision-aware dependency edges.

Runtime business data is not stored in these tables.

---

# 1. Definitions table

Purpose: one row per stable WPE definition identity.

Candidate columns:
- internal numeric primary key;
- canonical UUID string;
- scope type (`site`, `network`, future registered scope);
- scope ID/site/network ID;
- definition type/module namespace;
- machine key/slug;
- human label/title summary where useful for list UI;
- lifecycle state (`draft`, `published`, `disabled`, `archived`, `tombstoned` according to type rules);
- `current_revision_id`;
- `published_revision_id` nullable;
- created/updated timestamps;
- created/updated actor IDs where applicable;
- optional optimistic-lock generation/version.

Candidate constraints/indexes:
- PK internal ID;
- UNIQUE canonical UUID;
- UNIQUE `(scope_type, scope_id, definition_type, machine_key)`;
- index `(scope_type, scope_id, definition_type, lifecycle_state)`;
- index on `updated_at` only if list/query evidence needs it.

Do not add indexes for every possible JSON property.

## Stable identity rule

Definition UUID survives:
- label change;
- draft edit;
- publish;
- export/import remapping where explicitly mapped;
- module disable/re-enable.

Machine-key rename after external references exist is migration/reference-impacting, not ordinary label edit.

---

# 2. Definition Revisions table

Purpose: immutable snapshot of one definition revision.

Candidate columns:
- internal revision primary key;
- definition ID;
- monotonically increasing revision number per definition;
- definition/schema version;
- payload encoding version;
- canonical payload document;
- payload hash/fingerprint;
- created timestamp;
- actor/source (`admin`, `import`, `migration`, `system`);
- optional change summary safe metadata;
- optional source/import reference.

Candidate constraints/indexes:
- PK revision ID;
- UNIQUE `(definition_id, revision_number)`;
- index `(definition_id, revision_id)` or `(definition_id, created_at)` for history;
- optional unique/hash index only if benchmark shows dedupe value.

## Payload storage

Current paper preference: application-validated JSON stored in a broadly compatible text representation rather than relying on vendor-specific native JSON semantics as a requirement.

Reasons:
- definition payload is read/written as a versioned document;
- cross-MySQL/MariaDB JSON behavior differs;
- only proven common list/filter keys belong in normalized Definition columns;
- Definition Repository is not a generic queryable EAV store.

Exact `LONGTEXT` vs native JSON decision remains compatibility/benchmark evidence.

## Immutability

Published/draft revision rows are never edited in place.

An edit creates a new revision. Pointer changes in Definitions decide current/published visibility.

---

# 3. Definition Dependencies table

Purpose: store the dependencies declared/resolved for a particular Definition Revision.

Candidate columns:
- dependency-edge ID or composite key;
- `from_revision_id`;
- `from_definition_id` convenience/index;
- dependency kind/type;
- target canonical UUID;
- resolved target definition ID nullable;
- target expected definition type;
- required/optional flag;
- semantic path/reference key within source definition;
- compatibility/version constraint where supported;
- safe metadata/version.

Candidate indexes:
- `(from_revision_id)`;
- `(target_definition_id)` for reverse “Used by”;
- `(target_uuid)` for unresolved/import remapping;
- optional `(from_definition_id, target_definition_id)` only if needed.

## Revision-aware rule

Dependencies belong to a revision, because a draft can introduce/remove dependencies without changing currently published runtime.

“Used by” UI must distinguish:
- current draft references;
- published runtime references;
- archived historical references.

---

# 4. Publish transaction semantics

Publishing one definition conceptually requires:
1. validate target draft revision;
2. validate required dependencies;
3. ensure compatible referenced published targets where required;
4. atomically update `published_revision_id`/lifecycle pointer if DB transaction capability allows;
5. emit Definition Published event only after pointer commit;
6. invalidate dependent compiled runtime caches.

Publishing does **not** mutate the immutable revision row.

Cross-definition coordinated publish may require a higher-level transaction/release set later; do not pretend one row-pointer update guarantees multi-definition atomic rollout.

---

# 5. Draft/current pointer semantics

`current_revision_id` = latest working revision visible to authorized editors.

`published_revision_id` = runtime revision.

Therefore:
- Draft changes do not alter runtime;
- Publish can choose an older reviewed current revision if product UX supports explicit rollback/re-publish;
- disabling a definition does not delete revisions;
- restoring previous revision creates/points according to explicit history semantics, not mutable row rewrite.

---

# 6. Tombstone/purge

## Archive
Definition hidden from ordinary active lists but retained with revisions/dependencies.

## Tombstone
Stable identity intentionally removed from active resolution while preserving enough history/reference diagnostics.

## Physical purge
Separate destructive maintenance operation requiring:
- reverse dependency scan;
- runtime ownership check;
- retention policy;
- import/migration/audit impact;
- Backup/recovery policy.

Do not cascade-delete historical definitions merely because module is disabled.

---

# 7. Optimistic concurrency

Definition editor save carries expected generation/current revision ID.

If another actor saved since page load:
- return conflict;
- show diff/current owner/timestamp where available;
- no last-write-wins silent overwrite.

Exact concurrency token can be `current_revision_id` + generation field.

---

# 8. Scope / multisite alternatives

Two physical deployment candidates remain evidence-gated:

## A — per-site WPE tables
Use `$wpdb->prefix` for each site.

Pros:
- simple site isolation;
- follows common plugin table convention.

Costs:
- network-scale table proliferation;
- network-level definitions/sharing harder;
- network-wide migrations multiplied.

## B — network/global WPE tables with explicit scope columns
Use network/base prefix plus `scope_type/scope_id`.

Pros:
- one Definition Repository per network;
- network-shared definitions feasible;
- centralized migration/index management.

Risks:
- every query must enforce scope correctly;
- cross-site IDOR/data leakage impact higher;
- large-network indexes need evidence.

Current paper preference for WPE's cross-module platform model is **global/network table family with explicit scope**, but this is not Accepted until multisite security/performance benchmark under P-004.

Single-site naturally uses one scope.

---

# 9. UUID representation

Product-level identity is canonical UUID string.

Physical candidates:
- `char(36)` for transparency/debug/migration simplicity;
- binary 16-byte encoding for compact index/storage.

Do not optimize to binary before benchmark proves material benefit. Current paper preference is `char(36)`/ASCII-compatible representation for maintainability.

---

# 10. Payload hash

Revision gets cryptographic content fingerprint over canonical versioned payload bytes.

Use cases:
- import/diff/conflict detection;
- corruption diagnostics;
- compiled descriptor cache key;
- no-op save detection.

Hash is integrity/dedupe metadata, not a signature or authorization primitive.

Exact SHA-256 byte/hex representation can be finalized in physical DDL profile.

---

# 11. Migration/versioning

Separate versions:
- plugin Product Version;
- Definition Repository DB schema version;
- Definition payload schema version per type;
- individual Definition revision number.

Database migration can change physical columns/indexes without rewriting every definition payload if schema is backward-readable.

Payload migrations create explicit new revision or controlled canonical migration history according to type-specific ADR—not silent mutation of historical revisions.

---

# 12. Query patterns to benchmark

P-004 must exercise:
- list 10k/100k definitions by type/state/scope;
- load one published definition by UUID/key;
- load revision history;
- reverse `Used by` dependency lookup;
- import UUID remapping/unresolved dependency resolution;
- concurrent edit/save conflict;
- publish pointer update;
- archive/tombstone filtering;
- network scope isolation;
- compiled descriptor cache invalidation.

No benchmark has been run yet.

---

# 13. Explicit non-goals

Definition Repository does not become:
- generic runtime business data table;
- unlimited EAV metadata store;
- log/event table;
- Workflow run state;
- Form Entries;
- Membership Enrollments;
- Chat messages;
- Vault secrets.

Those domains have dedicated runtime ownership.

## Future executable evidence — NOT AUTHORIZED

- exact DDL/types/index lengths;
- charset/collation;
- per-site vs network-global multisite profile;
- 10k/100k benchmarks;
- deadlock/transaction behavior;
- JSON LONGTEXT/native compatibility;
- UUID physical format;
- migration/rollback;
- import remap/tombstone/concurrency fixtures.

No table, migration or benchmark has been created/run.