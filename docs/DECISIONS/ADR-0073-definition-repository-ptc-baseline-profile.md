# ADR-0073 — Definition Repository PT-C Benchmark Baseline

Status: **Accepted paper benchmark profile / exact DDL evidence pending**  
Date: 2026-08-28

## Context

ADR-0049 accepts Definition Identity + immutable Revisions + revision-aware Dependencies. ADR-0069 fixes explicit site/network logical scope, and ADR-0071 places the Definition Repository in PT-C global scoped control-plane storage. P-004 still needs a concrete baseline profile to compare against compact/database-specific alternatives without prematurely implementing SQL.

## Decision

The first future P-004 benchmark baseline is **D1 — Maintainability baseline**:
- compact numeric physical row IDs;
- canonical portable Definition UUID stored in a transparent textual/ASCII-compatible representation;
- explicit network/site scope coordinates;
- bounded normalized Definition Type and Machine Key identifiers;
- separate human label/title fields;
- immutable revision payload stored as application-validated text document baseline;
- SHA-256 payload fingerprint with exact binary-vs-hex representation still benchmarked;
- minimal workload-driven indexes only;
- no arbitrary payload-property/EAV indexes;
- no mandatory DB foreign-key dependency in the baseline;
- application-enforced same-definition pointer integrity plus repair/diagnostic capability;
- WordPress-derived charset/collation rather than hardcoded server collation.

Alternative profiles remain evidence candidates:
- D2 binary UUID identity;
- D3 native JSON payload;
- D4 constraint/foreign-key-enhanced profile.

## Accepted index invariants

Logical indexes must support:
- physical PK;
- unique portable UUID;
- scope + definition type + machine-key uniqueness;
- scope/type/lifecycle active-list access;
- unique Definition revision number;
- revision history;
- source-revision dependencies;
- reverse target-definition `Used by`;
- unresolved target-UUID import remapping.

Human titles and full revision payloads are not identity indexes.

Exact SQL types, lengths, collation, index order/prefix lengths and duplicate-index elimination remain P-001/P-004 evidence.

## Accepted integrity semantics

- revisions are immutable;
- current/published pointers must resolve to revisions owned by the same Definition;
- publishing uses optimistic concurrency and transactional pointer update where supported;
- ordinary archive/tombstone does not cascade-delete history;
- physical purge is a separate destructive operation;
- Site Backup extracts only target-site scoped rows from shared PT-C tables;
- scope is product/security truth independent of physical table name.

## Why D1 first

D1 optimizes for portability, diagnostics and maintainability rather than speculative storage micro-optimization. It gives P-004 a stable baseline against which binary UUID, native JSON and stronger DB constraints can prove measurable benefit/cost.

## Evidence still required

After explicit owner consent:
- supported MySQL/MariaDB/version matrix;
- exact integer/string/hash types;
- charset/collation;
- index byte/plan behavior;
- 10k/100k+/large-network workloads;
- locking/deadlocks/concurrency;
- `dbDelta()` additive-migration boundaries;
- explicit migration SQL for operations `dbDelta()` cannot safely represent;
- foreign-key/no-FK comparison;
- text-vs-native JSON;
- textual-vs-binary UUID;
- Site Backup/network restore;
- migration/rollback/recovery.

Executed DDL/benchmarks: **0**.

## Development gate

This ADR selects a future benchmark baseline only. It authorizes no table creation, SQL, migration, benchmark, fixture DB or runtime repository implementation. ADR-0014 remains mandatory.
