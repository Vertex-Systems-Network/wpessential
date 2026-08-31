# ADR-0074 — Relations PT-D Benchmark Baseline

Status: **Accepted paper benchmark profile / P-010 evidence pending**  
Date: 2026-08-28

## Context

WPE Relations already uses one typed relation service as the paper default. ADR-0069 requires explicit Multisite scope and cross-site deny-by-default. ADR-0071 classifies Relations as a PT-D candidate but requires comparison against per-site physical storage before implementation.

## Decision

The first future P-010 benchmark baseline is **R1 — PT-D shared scoped universal edge-table family**.

It must preserve:
- explicit site/network scope;
- typed endpoints through Data Source Registry;
- forward and reverse lookup;
- directed/undirected semantics;
- concurrency-safe cardinality and duplicate enforcement;
- optional ordering/pivot metadata;
- Policy-aware reads/writes;
- deterministic import/export remapping;
- site-row Backup extraction;
- cross-site relations Off by default.

Required comparison profiles remain:
- **R2** — PT-E per-site universal edge table;
- **R3** — per-relation physical table only as exceptional high-scale candidate;
- native/meta storage only as interoperability/reference baseline, not universal WPE engine.

## Why R1 first

R1 best matches WPE's one Relation Service, reverse-query requirement, network diagnostics/migration, Query Builder integration and future explicit cross-site mode without multiplying tables by site/relation count.

This is not proof that R1 is fastest or safest under every workload. A missing scope predicate in a shared table is a critical failure, so P-010 must deliberately attack scope isolation. R2 must also be tested because physical site separation can reduce some blast radius while introducing blog-context/table-version risks.

## Evidence still required

After explicit owner consent:
- R1 vs R2 table/index/storage footprint;
- 100k/1M/10M-edge workloads where practical;
- high-degree endpoints;
- forward/reverse/exact-pair lookup;
- one-to-one/one-to-many concurrency conflicts;
- deadlock/retry behavior;
- pivot/order performance;
- Query Builder predicates;
- orphan/entity-delete cleanup;
- 10/100/1k/10k-site topology cost;
- site creation/deletion/transfer;
- Site Backup scoped extraction/network restore;
- cross-site IDOR/scope-leak fixtures;
- R3 exceptional profile only where R1/R2 fail a proven scale requirement.

Executed Relation DDL/benchmarks: **0**.

## Development gate

This ADR chooses a future benchmark baseline only. It authorizes no Relation table, migration, repository code, fixture database, benchmark or query execution. ADR-0014 explicit owner consent remains required.
