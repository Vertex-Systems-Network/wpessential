# WPEssential — Custom Tables DDL Migration Language

Status: **Phase 0 paper architecture / no migration engine authorized**  
Related: Custom Tables exhaustive spec, ADR-0004, ADR-0008, Backup/Restore semantics, Query AST v1.

## Goal

Define a typed, reviewable and recoverable schema-migration language for WPEssential-managed custom tables without exposing arbitrary DDL/SQL as the normal product primitive.

This is a **desired-state + migration-plan contract**, not executable SQL and not an implementation.

## Core principles

1. The published Table Definition describes desired logical/physical schema.
2. The physical database has an independently observed/applied schema state.
3. A Migration Plan is generated from `observed → desired`, reviewed, risk-classified and then—only after future development authorization—compiled to provider-specific operations.
4. Raw user-entered DDL is not the standard migration format.
5. `dbDelta()` may be used for compatible create/add/change cases, but WPE must not assume it safely handles every rename/drop/constraint/high-risk operation.
6. Migration success means post-operation introspection matches expected schema and required verification passes.
7. Publishing a Definition revision never implies a database migration succeeded.

## Schema document

A managed table schema contains:

- table stable UUID;
- logical key;
- physical table identity;
- schema format version;
- desired schema version;
- columns;
- primary key;
- indexes;
- supported constraints;
- storage/charset/collation requirements;
- optional logical relation references;
- data classification;
- owner/module metadata.

Identifiers are structured values. They are never concatenated from unchecked request strings.

## Migration Plan envelope

Every generated plan includes:

- plan UUID;
- table UUID;
- source observed fingerprint;
- source applied schema version;
- target Definition revision UUID;
- target schema version;
- generated-at;
- operations in deterministic order;
- risk level;
- affected-row estimate/range;
- expected lock/availability class;
- required preconditions;
- backup/restore-point requirement;
- rollback/recovery strategy;
- unsupported/drift findings;
- dependency impact;
- confirmation level;
- plan expiry/revalidation rule.

A plan cannot execute later against a materially changed observed fingerprint without regeneration/revalidation.

## Operation vocabulary

Allowed typed operation families:

### Table lifecycle
- `create_table`
- `rename_table`
- `archive_table_reference`
- `drop_table` — highest destructive class, never implicit

### Columns
- `add_column`
- `rename_column`
- `alter_column_type`
- `alter_column_nullability`
- `alter_column_default`
- `alter_column_collation`
- `drop_column`

### Keys/indexes
- `add_primary_key`
- `replace_primary_key`
- `add_index`
- `rename_index` where target DB semantics justify
- `drop_index`
- `add_unique_constraint`
- `drop_unique_constraint`

### Advanced constraints
- `add_foreign_key`
- `drop_foreign_key`
- `add_generated_column`
- `alter_generated_column`

These remain unavailable unless the accepted DB compatibility profile proves support.

### Data transition helpers
- `backfill_column`
- `copy_column_values`
- `transform_column_values`
- `deduplicate_for_unique`
- `verify_data_compatibility`

Transforms use registered typed operations/expressions. No arbitrary PHP/eval/raw SQL transform box.

### Shadow/copy strategy operations
- `create_shadow_table`
- `copy_rows_chunked`
- `verify_shadow`
- `swap_tables`
- `retain_old_table_for_recovery`
- `drop_recovery_table_after_retention`

These are conceptual planner operations; exact MySQL/MariaDB mechanics require later evidence.

## Risk classification

### R0 — metadata/no physical change
Examples: label/help text only.

### R1 — additive / normally reversible
Examples: nullable column, compatible non-unique index.

### R2 — data-affecting but controlled
Examples: NOT NULL after safe backfill, widening type, unique index after duplicate scan.

### R3 — high-impact/destructive
Examples: column/table rename with consumers, narrowing type, dropping index critical to production query, collation change on large table, primary-key replacement.

### R4 — destructive/data-loss capable
Examples: drop column/table, incompatible transform, irreversible merge/deduplication.

R3/R4 require explicit impact preview, stronger capability/re-auth, verified restore point according to policy, and no unattended automatic execution by default.

## Precondition language

Operations can declare machine-checkable preconditions:

- table exists/does not exist;
- column type matches observed fingerprint;
- row count under/over threshold;
- no NULL values;
- no duplicate values for candidate unique key;
- max value fits new type;
- max string length fits target length;
- no orphan references;
- DB feature available;
- enough disk/temp capacity estimate;
- no active incompatible migration;
- required Backup verification tier available.

Failed precondition means plan blocked, not best-effort execution.

## Backfill model

A backfill definition specifies:

- target column;
- typed source expression/default;
- row selection condition through safe Query/Expression primitives;
- chunk size candidate;
- ordering key;
- resume cursor;
- idempotency rule;
- validation after chunk;
- failure/retry policy.

Large backfills must be resumable and observable rather than one unbounded request.

## Type-change compatibility

Planner classifies:

- lossless compatible;
- conditionally compatible after scan;
- conversion required;
- lossy;
- unsupported.

Examples:
- VARCHAR widening: usually compatible;
- VARCHAR narrowing: requires max-length scan;
- signed→unsigned: requires negative-value scan;
- integer→smaller integer: range scan;
- string→numeric: parse/invalid-value strategy;
- decimal precision/scale reduction: rounding/loss preview;
- datetime/timestamp conversion: timezone/zero-date semantics review.

No silent truncation policy.

## Rename semantics

Logical label/key rename and physical SQL identifier rename are different operations.

Physical rename requires dependency impact on:
- Query definitions;
- Field storage mappings;
- Admin Columns;
- REST/Data Source bindings;
- Relations/pivots;
- external consumer registrations;
- exports/import mappings.

Where external consumers cannot be proven safe, WPE can recommend staged compatibility mapping rather than immediate rename.

## Drop semantics

Dropping a field/table requires:

- dependency graph check;
- data-size/count report;
- export/backup opportunity;
- explicit retention/recovery plan;
- confirmation phrase for highest class;
- tombstone/Definition history retained even if physical data is removed.

Definition deletion never automatically drops physical data.

## Index migration rules

Before adding index:
- validate columns/types/prefix lengths;
- estimate selectivity where practical;
- identify redundant/overlapping indexes;
- estimate table size and write cost.

Before dropping:
- dependency/query usage analysis;
- warn when active Query/Column/REST sort/filter relies on it.

Unique index additionally requires duplicate scan and conflict-resolution plan.

## Drift detection

Observed DB schema can diverge from WPE desired/applied state because of manual DBA changes or other plugins.

Drift categories:
- expected additive external change;
- unknown column/index;
- missing expected column/index;
- changed type/default/collation;
- table renamed/missing;
- ownership conflict.

Response options:
- accept/adopt into new Definition revision after review;
- ignore external additive drift if harmless and ownership policy allows;
- generate corrective plan;
- mark degraded/read-only;
- detach WPE management.

Never automatically overwrite unknown external drift merely to make schema match.

## External inspected tables

External tables are read-only schema by default.

A future explicit adoption flow requires:
- ownership confirmation;
- full snapshot/introspection;
- compatibility review;
- dependency warning;
- first WPE baseline fingerprint;
- rollback/exit strategy.

Core WordPress/plugin tables are not casually adoptable.

## Provider compiler boundary

Migration language compiles through a DB capability profile.

Compiler owns:
- identifier quoting/preparation;
- exact DDL syntax;
- WordPress `dbDelta()` usage where appropriate;
- MySQL/MariaDB differences;
- online/in-place/copy algorithm hints where supported;
- transaction limitations of DDL;
- introspection queries.

Values use parameterized operations where possible. Dynamic identifiers come only from validated registry values and compatible safe identifier handling.

## Rollback vs recovery

Do not promise universal transactional rollback for DDL.

Each operation declares one of:
- trivially reversible;
- reversible while recovery copy retained;
- recoverable only from verified Backup;
- irreversible after recovery window.

UI uses **Recovery** wording when rollback cannot be guaranteed.

## Migration run states

- planned
- awaiting_review
- blocked_precondition
- approved
- queued
- revalidating
- running
- paused_safe_point
- verifying
- applied
- applied_with_warnings
- failed_recoverable
- failed_recovery_required
- superseded
- cancelled_before_mutation

Cancellation may not be available during an unsafe DDL/swap boundary.

## Observability

Record:
- run/plan/table/revision IDs;
- operation number/type;
- start/end/duration;
- affected rows estimate/actual where available;
- warnings/errors;
- DB capability profile;
- backup reference;
- resulting schema fingerprint.

Never log row values merely because migration touched them.

## Multisite

Physical naming and ownership are explicit:
- per-site managed table;
- network-global managed table only for modules designed for it.

Network migration requires dedicated network capability and must not loop over unbounded sites synchronously.

## Import/export interaction

Portable package carries logical desired schema, not environment-specific generated SQL.

Target site:
1. validates compatibility;
2. resolves physical identifiers;
3. introspects conflicts;
4. generates local Migration Plan;
5. requires review according to risk.

## Future acceptance protocol — NOT AUTHORIZED

After explicit development consent, fixtures must prove:
- clean create from empty DB;
- no-op diff determinism;
- safe additive changes;
- NOT NULL backfill;
- duplicate block before unique;
- rename dependency detection;
- narrowing/type-loss detection;
- failed migration recovery;
- manual schema drift;
- 10k/100k/1M row planning/execution behavior;
- MySQL/MariaDB supported-version matrix;
- multisite scoping;
- concurrency/second migration rejection;
- post-run schema fingerprint verification.

No executable migration/compiler/DDL has been created or run.