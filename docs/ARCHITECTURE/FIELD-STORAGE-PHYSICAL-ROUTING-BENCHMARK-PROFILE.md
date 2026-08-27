# WPEssential — Field Storage Physical Routing & Migration Benchmark Profile

Status: **Phase 0 paper profile / no storage adapter, migration or benchmark execution authorized**  
Date: 2026-08-28  
Related: Field Storage Architecture Alternatives, Query P-009, Relations, Custom Tables, Vault, ADR-0022, ADR-0069, ADR-0071.

## Purpose

Define the physical routing defaults for Custom Fields without forcing every value into one EAV, JSON or custom-table model.

Field Definition remains logical/portable. Physical storage is selected by target ownership, queryability, concurrency and data semantics.

## Storage profiles

### FS1 — Native WordPress object storage — default baseline

Use when the value naturally belongs to a WordPress object and expected workload fits native semantics:
- post/CPT scalar → registered post meta;
- user profile scalar → user meta;
- taxonomy scalar → term meta;
- comment-domain scalar → comment meta;
- bounded site/network configuration → Settings/Options profile, not generic business rows.

FS1 preserves WordPress interoperability and existing lifecycle behavior.

FS1 may not claim strong concurrent uniqueness, high-volume aggregate performance or queryability merely because a meta query is technically possible.

### FS2 — WPE typed Custom Table column — escalation profile

Use when one or more are true:
- high row volume;
- Q3/Q4 indexed filtering/sorting/aggregate requirements;
- strong type/index constraints;
- transactional uniqueness/concurrency requirements;
- value belongs to a WPE application entity rather than a natural WP object.

Physical topology follows the owning Custom Table profile; Field Storage does not independently choose PT-D/PT-E after the table is selected.

### FS3 — First-class child rows for queryable structured/repeater data

Queryable repeaters/flexible rows use stable child identity and order in WPE-managed child storage when child-level filters/sorts/updates are real requirements.

Bounded configuration-like groups may remain one structured value when child querying is unnecessary.

A serialized/JSON blob must not be advertised as Q3/Q4 child-queryable without an accepted projection/index adapter.

### FS4 — Relations Engine for relationship semantics

Many-to-many references, reverse lookup, pivot metadata or explicit cardinality belong to Relations runtime rather than comma-separated IDs or serialized field blobs.

A trivial single local reference may remain one typed ID/reference field.

### FS5 — Vault reference for secrets

Persist only opaque Vault reference + safe metadata. Secret plaintext is not Field Storage.

### FS6 — Derived/search projection

Materialized computed/search/facet values are rebuildable projections, not source-of-truth replacements.

Projection invalidation/generation must be explicit.

## Queryability contract

Physical adapter declares truthful capability:
- Q0 not queryable;
- Q1 equality/existence;
- Q2 typed range/order/filter;
- Q3 indexed high-volume;
- Q4 aggregate/join optimized.

Publish-time UI/validation must not promise a higher class than the selected adapter and physical indexes can support.

## Null/empty/default semantics

Every adapter must preserve a defined distinction where schema requires it between:
- no stored value;
- explicit null;
- empty scalar/list/document;
- inherited/default value.

Migration cannot silently collapse these states.

## Uniqueness/concurrency

Guarantee level is part of adapter capability:
- validation-only;
- application/transaction lock;
- DB unique constraint;
- unsupported.

When uniqueness is a business invariant under concurrent writes, FS1 metadata is not accepted unless an explicit proven locking/index strategy exists; FS2 is preferred.

## Revision/value-history semantics

Definition revision and runtime value history remain separate.

Adapter declares one of:
- native WP value revision support;
- WPE value-history profile;
- definition-only revision;
- no value history.

No field type may inherit post-revision claims automatically across user/term/comment/custom storage.

## Storage migration plan

Changing target adapter/type creates a reviewed Field Migration Plan with:
- source/target adapter and schema versions;
- affected scope/record count estimate;
- value compatibility class;
- invalid/lossy cases;
- dependency/query impact;
- write-freeze/dual-write requirement if any;
- resumable cursor/chunk profile for large datasets;
- verification fingerprint/counts;
- recovery/rollback class;
- required verified Backup for destructive classes.

Publishing a new Field Definition never means the value migration completed.

## Migration correctness gates

Future fixtures must prove:
- text→typed numeric/date invalid-value reporting;
- single↔multiple conflict semantics;
- native meta→custom table and custom table→native where supported;
- structured blob→child rows and reverse only when fidelity is defined;
- relation extraction without duplicate edges;
- secret fields never copied as plaintext;
- resume after interruption does not double-transform rows;
- source remains recoverable until accepted cutover point;
- wrong-site records cannot be migrated into target site scope.

## Multisite

Native WP object data follows WordPress site/global ownership.

For WPE Custom Table storage:
- site-owned data follows table's explicit PT-E/PT-D profile;
- network/global fields require explicit network-capable owning entity/table;
- a network definition does not make site runtime values global;
- migration/export of one site cannot silently include another site's field rows.

## Performance evidence

Future benchmark matrix includes:
- 10k/100k/1M object/value rows where relevant;
- equality/range/sort/meta-OR workloads;
- bulk reads/writes;
- queryable repeaters;
- uniqueness races;
- index/storage growth;
- migration chunk throughput;
- 100/1k/10k-site operational cost for custom storage.

FS1 and FS2 should be compared only on equivalent business semantics; native interoperability is an architectural benefit, not merely a latency number.

Executed Field Storage fixtures: **0**.

## Paper recommendation

Use **FS1 native WordPress storage by default when ownership and workload are natural**, escalate to **FS2 typed Custom Tables** only for justified scale/constraint/query needs, use **FS3 child rows** for truly queryable structured values, **FS4 Relations** for relationships and **FS5 Vault references** for secrets.

No one storage format is the product-wide default.