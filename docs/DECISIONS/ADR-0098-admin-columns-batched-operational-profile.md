# ADR-0098 — Admin Columns Batched Operational Profile

Status: **Accepted architecture / runtime evidence pending**  
Date: 2026-08-28

## Decision

WPEssential Admin Columns uses **AC1 — compiled whole-request Column Execution Plan with batch hydration** as its first operational architecture.

Accepted invariants:
1. visible row identities are collected before WPE column hydration;
2. source adapters expose bounded batch-read semantics where data is not already present;
3. per-row expensive Query/Relation/remote/shortcode execution is rejected or converted to bounded batch strategy;
4. real sorting/filtering must execute in the authoritative backend before pagination;
5. client-only visible-row sorting/filtering is never advertised as real list-table semantics;
6. inline/bulk edits use the owning Field/Data Source API, per-row Policy and canonical validators;
7. hidden columns do not authorize or prefetch protected data;
8. exports have independent Policy and raw/formatted schemas;
9. Multisite scope is explicit in hydration, cache and mutation;
10. missing/unsupported adapters degrade safely rather than breaking wp-admin.

## Why

Admin Columns can become an invisible performance/security hotspot because a visually simple table may otherwise execute one query, relation lookup or shortcode per row. WPE needs request-level planning and adapter capabilities rather than trusting renderer convenience APIs.

## Rejected defaults

- one Query execution per row;
- one Relation target query per row;
- arbitrary `do_shortcode()` per row;
- browser-only sort/filter marketed as database sorting/filtering;
- inline write directly to raw meta/SQL;
- fetching unauthorized values then hiding them with presentation rules;
- global cache that ignores site/actor/access context.

## Evidence pending

Exact batch sizes, query budgets, hook integration, list-table compatibility, lazy REST mode, inline concurrency and scale thresholds require future consent-gated evidence.

Executed Admin Columns runtime/benchmark cases: **0**.

## Source

`docs/ARCHITECTURE/ADMIN-COLUMNS-OPERATIONAL-PROFILE.md`

## Development gate

This decision does not authorize WordPress hooks, queries, REST loaders, writes, exports or benchmarks. ADR-0014 explicit owner consent remains required.