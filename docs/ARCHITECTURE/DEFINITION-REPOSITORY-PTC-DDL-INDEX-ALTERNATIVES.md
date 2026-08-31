# WPEssential — Definition Repository PT-C DDL & Index Alternatives

Status: **Phase 0 paper architecture / no DDL or migration authorized**  
Date: 2026-08-28  
Related: ADR-0014, ADR-0049, ADR-0069, ADR-0071, P-001, P-004.

## 1. Purpose

The Definition Repository logical model is already accepted as:

`Definition Identity → Immutable Revisions → Revision-aware Dependencies`.

ADR-0071 classifies it as **PT-C — WPE global scoped control-plane tables**.

This document narrows the physical alternatives enough for future P-004 benchmarking without executing SQL or prematurely locking database-specific optimizations.

## 2. Non-negotiable physical invariants

Regardless of exact SQL type/profile:

1. one stable Definition identity row exists per logical definition;
2. revisions are append-only/immutable after commit;
3. dependencies belong to a source revision;
4. every shared PT-C row resolves explicit network/site scope where applicable;
5. local numeric IDs are physical implementation details, not portable identity;
6. canonical WPE UUID remains portable/import identity;
7. draft/current and published pointers are separate;
8. frequently filtered identity/list fields are normalized columns;
9. arbitrary definition payload properties are **not** turned into generic indexed EAV columns;
10. payload hash is integrity/cache/import metadata, not authorization/signature;
11. scope is present in uniqueness and high-frequency list indexes where semantics require it;
12. Site Backup extracts only target-site Definition rows/revisions/dependencies from shared PT-C tables.

## 3. Logical table family

### A. `definitions`
Stable identity, scope, lifecycle and current/published revision pointers.

### B. `definition_revisions`
Immutable canonical definition documents.

### C. `definition_dependencies`
Revision-aware outgoing dependency edges and reverse-resolution metadata.

No Form Entries, Membership runtime, Chat messages, logs, Jobs or secrets belong here.

## 4. Scope representation alternatives

### S1 — explicit network/site coordinates — preferred paper profile
Conceptual columns:
- `scope_kind` small bounded code;
- `network_id` nullable/normalized integer coordinate;
- `site_id` nullable integer coordinate.

Rules:
- Site scope requires site coordinate;
- Network scope has no site coordinate;
- single-site normalizes to a site logical scope while avoiding fake remote identity;
- exact null/sentinel representation remains P-004 detail.

Advantages:
- matches ADR-0069 directly;
- easy composite scope indexes;
- avoids polymorphic text scope identifiers in hot indexes.

### S2 — generic `scope_type + scope_id`
Simpler generic abstraction, but weak when both network and site coordinates are operationally useful and risks ambiguous remapping.

**Current preference: S1.**

## 5. Physical identifier alternatives

### Internal row IDs
Preferred baseline:
- monotonic integer surrogate primary keys for Definition/Revision/Dependency rows.

Reason:
- compact joins/pointers;
- portable UUID remains separate;
- revision/dependency tables avoid wide UUID joins for every lookup.

Exact signed/unsigned width remains compatibility profile detail.

### Definition UUID
Alternatives:

**U1 — canonical textual UUID (ASCII-compatible, fixed/bounded)**
- simplest diagnostics/export/import;
- larger indexes.

**U2 — 16-byte binary UUID**
- smaller index/storage;
- less transparent;
- conversion/ordering/interoperability implementation burden.

Current paper preference remains **U1 until P-004 proves U2 materially beneficial**.

UUID comparison must be case/collation stable; it must not inherit locale-sensitive behavior.

## 6. `definitions` candidate columns

Required logical fields:
- physical ID;
- canonical UUID;
- `scope_kind`;
- `network_id`;
- `site_id`;
- definition type key;
- machine key/slug;
- lifecycle state;
- current revision ID;
- published revision ID nullable;
- generation/optimistic-lock token;
- created/updated timestamps;
- created/updated actor/source metadata where useful;
- optional safe list label/title summary.

Do **not** put arbitrary module settings into this row merely to avoid reading the revision.

## 7. `definitions` index invariants

Required logical indexes:

### Primary identity
- PK physical ID.

### Portable identity
- UNIQUE canonical UUID.

### Machine identity within scope
Logical uniqueness:

`scope + definition_type + machine_key`

This means a site can use a key also used on another site without collision.

### Active/list query
Candidate:

`scope + definition_type + lifecycle_state + physical_id`

Use cursor/ID pagination where appropriate instead of large OFFSET assumptions.

### Updated sorting
Do not add standalone `updated_at` index by default. Add only if real list/query benchmark justifies it.

## 8. Key string profile

Definition type and machine keys are identifiers, not arbitrary translated labels.

Paper direction:
- bounded length;
- restricted normalized character grammar;
- stable ASCII-compatible comparison where practical;
- human labels stored separately and not used as identity.

This avoids oversized locale-sensitive composite indexes and accidental case/collation identity changes.

Exact lengths remain P-001/P-004 evidence. Do not blindly use `VARCHAR(255)` for every indexed identifier.

## 9. `definition_revisions` candidate columns

Required logical fields:
- revision physical ID;
- definition physical ID;
- monotonic revision number within Definition;
- definition-type schema version;
- payload encoding/canonicalization version;
- immutable payload document;
- payload fingerprint/hash;
- created timestamp;
- actor/source type;
- optional source/import/migration correlation;
- safe change summary metadata.

No `updated_at` is needed for immutable rows except migration-level physical metadata if a later profile proves it necessary.

## 10. Revision payload storage alternatives

### P1 — text document (`LONGTEXT`-class) — preferred compatibility baseline
Pros:
- broad MySQL/MariaDB compatibility;
- application owns validation/canonicalization;
- no dependency on vendor-specific JSON indexing semantics.

Cons:
- DB does not validate JSON type itself;
- no native property querying.

### P2 — native JSON type
Pros:
- DB-level JSON validity/functions.

Cons:
- compatibility/behavior differs across supported engines/versions;
- encourages accidental property-query/index coupling.

Because the Repository treats payload as a versioned document, **P1 is current paper preference** until P-001/P-004 prove a material reason for P2.

Runtime/list queries should use normalized columns/compiled descriptors rather than arbitrary JSON scans.

## 11. Payload fingerprint alternatives

Logical algorithm candidate remains SHA-256 over canonical versioned payload bytes.

Physical forms:
- **H1** — 32-byte binary digest;
- **H2** — 64-character lowercase hex.

H1 is compact; H2 is easier to inspect. No decision until P-004 storage/index/diagnostics evidence.

Fingerprint is not a digital signature.

## 12. Revision indexes

Required:
- PK revision ID;
- UNIQUE `(definition_id, revision_number)`;
- index supporting recent/history lookup per Definition, e.g. `(definition_id, revision_number)` already covers ordered history in common B-tree engines;
- optional payload fingerprint index **only** if no-op/dedupe workload proves value.

Do not add duplicate indexes that are left-prefix-equivalent without evidence.

## 13. Definition pointer integrity

`current_revision_id` and `published_revision_id` must reference revisions owned by the same Definition.

Two enforcement alternatives:

### I1 — DB foreign keys/constraints
Potential stronger physical integrity, but requires compatibility/migration/delete/restore validation.

### I2 — application-enforced invariant + repair diagnostics
Closer to common WordPress plugin portability patterns; easier staged migrations/backups, but bugs can create inconsistent rows if validation fails.

Current baseline does **not** require foreign keys until P-001/P-004 proves they are safe across supported MySQL/MariaDB/WordPress lifecycle. Application-level same-definition validation and Site Health repair diagnostics are mandatory regardless.

## 14. `definition_dependencies` candidate columns

Required logical fields:
- physical edge ID;
- source definition ID;
- source revision ID;
- dependency kind;
- semantic path/reference key;
- required/optional flag;
- target canonical UUID;
- resolved target definition ID nullable;
- expected target definition type nullable;
- optional version/compatibility constraint;
- safe edge metadata/schema version.

Target scope is resolved through target Definition identity; do not duplicate stale target scope data unless benchmark/use-case requires it.

## 15. Dependency index invariants

Required:
- source revision index for compile/validation;
- target resolved Definition index for `Used by` reverse lookup;
- target UUID index for unresolved/import remap;
- optional source-definition index only if current-vs-historical reverse queries prove it useful.

Reverse dependency UI must distinguish draft/current/published/historical source revisions.

## 16. Delete/tombstone behavior

No physical cascade from Definition identity to immutable history as ordinary delete.

Normal lifecycle:
- active;
- disabled/archive;
- tombstone;
- separate destructive purge.

Purge requires:
- reverse dependency scan;
- runtime/compiled descriptor references;
- retention policy;
- Backup/recovery readiness;
- audit;
- explicit destructive authorization.

## 17. Publish transaction profile

Single Definition publish should conceptually be one transaction where supported:
1. lock/compare expected Definition generation;
2. verify target revision belongs to Definition;
3. validate required dependencies;
4. update published pointer/lifecycle/generation;
5. commit;
6. emit event/invalidate compiled caches after commit.

Multi-definition coordinated release is a different future release-set problem and must not be falsely represented as atomic merely because each pointer update is transactional.

## 18. Optimistic concurrency

Editor save/publish carries expected current revision/generation.

Conflict response contains safe current metadata/diff reference and never silently overwrites another save.

Exact DB locking primitive remains evidence-gated.

## 19. Table charset/collation

Future DDL should derive supported WordPress DB charset/collation through WordPress database APIs rather than hardcoding a server collation.

Identity fields still require deterministic comparison semantics compatible with their normalized grammar.

Exact table/column collations are P-001/P-004 evidence and must be tested across supported MySQL/MariaDB profiles.

## 20. Index-size discipline

Because composite index byte limits vary with engine/page/charset profile:
- identifier fields remain bounded;
- avoid indexing human title/labels as part of identity;
- avoid `VARCHAR(255)` everywhere by habit;
- do not index full payload text;
- do not create one index per builder property;
- benchmark actual list/lookup/reverse-dependency query plans.

## 21. P-004 candidate profiles

Future executable comparison, after consent:

### D1 — Maintainability baseline
- textual UUID;
- explicit scope coordinates;
- normalized bounded ASCII-like identity keys;
- text payload;
- binary or hex SHA-256 comparison;
- no mandatory DB foreign keys;
- minimal indexes above.

### D2 — Compact identity profile
- binary UUID;
- otherwise D1 semantics.

### D3 — Native-JSON profile
- D1 identity;
- native JSON payload where engine permits.

### D4 — Constraint-enhanced profile
- D1/D2 plus validated DB foreign keys/check constraints where compatible.

Do not combine every optimization into one benchmark; isolate impact.

## 22. P-004 workload matrix

Required future fixtures:
- 10k, 100k and larger synthetic Definitions;
- many sites sharing PT-C table family;
- one-site hot list;
- network aggregate list;
- UUID lookup;
- machine-key lookup;
- draft save conflict;
- publish pointer update;
- revision history;
- dependency compile;
- reverse `Used by`;
- unresolved import remap;
- archive/tombstone filtering;
- site deletion extraction/cleanup;
- Site Backup scoped export;
- network Backup/restore;
- migration/index change;
- DB crash/transaction recovery where test environment supports it.

Measure query plans, latency distribution, storage/index size, lock contention and migration time. No benchmark has run yet.

## 23. Compatibility facts to verify later

Before final DDL:
- WordPress/PHP compatibility floor from P-001;
- MySQL/MariaDB minimum and maximum tested versions;
- InnoDB index/key limits under supported page/row formats;
- charset/collation behavior;
- `dbDelta()` suitability for each additive change;
- operations requiring explicit migration SQL instead;
- transaction/DDL behavior;
- large-network table/index size.

## 24. Current recommendation

Paper recommendation for first P-004 baseline is **D1**.

This is not implementation approval and not a claim that D1 is fastest. It is the simplest portable profile against which more compact/DB-specific variants can be measured.

## 25. Development gate

No SQL, table, migration, index, benchmark, fixture database or plugin source is authorized by this document. ADR-0014 explicit owner consent remains mandatory.
