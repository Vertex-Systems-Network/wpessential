# ADR-0136 — Admin Columns Executable Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP19`

## Decision

Accept `docs/QUALITY/ADMIN-COLUMNS-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the canonical future executable-evidence contract for Admin Columns.

The protocol freezes **AC-01…AC-176** and preserves ADR-0098's **AC1 — compiled whole-request Column Execution Plan with batch hydration** as the first operational architecture to test.

## Accepted evidence boundaries

Admin Columns capabilities are certified independently per target adapter:

- `AC-R` read/render;
- `AC-S` server-side sort before pagination;
- `AC-F` server-side filter before pagination;
- `AC-Q` search/query integration;
- `AC-E` inline edit;
- `AC-B` bulk edit/delete;
- `AC-X` export;
- `AC-M` Multisite/scope safety;
- `AC-P` performance/batching.

A visually rendered column does not establish any other capability.

## Required truths

The following remain distinct:

`Column Set Definition ≠ Compiled Column Plan ≠ target adapter capability ≠ hydrated request data ≠ displayed cell ≠ writable source ≠ export schema ≠ certified runtime behavior`

Accepted invariants:

1. row identities are collected before WPE hydration;
2. batching must scale by source/chunk class, not ordinary per-row expensive work;
3. real sort/filter/search semantics execute in the authoritative backend before pagination;
4. protected values are Policy-gated before fetch/return, not merely hidden in presentation;
5. inline and bulk mutation use owning Field/Data Source/Relation/Status APIs and concurrency rules;
6. all-filtered bulk selection persists the authoritative selection query rather than exploding browser IDs;
7. export authorization is separate from screen visibility and must mitigate spreadsheet formula injection;
8. WooCommerce/core/third-party/DataViews compatibility is version/storage-specific evidence, never assumed;
9. Multisite site/scope/actor context participates in hydration/cache identity;
10. missing/unsupported adapters degrade explicitly without raw meta/SQL fallback.

## Evidence matrix

Accepted fixed fixture set:

- target registry/definition/capability honesty — AC-01…AC-16;
- whole-request planning and batch hydration — AC-17…AC-40;
- sorting/filtering/search/pagination truth — AC-41…AC-64;
- views/presentation/list-table behavior — AC-65…AC-80;
- inline edit/auth/concurrency — AC-81…AC-104;
- bulk selection/mutations — AC-105…AC-128;
- export/spreadsheet safety — AC-129…AC-144;
- lazy/remote/compatibility/degradation — AC-145…AC-160;
- Multisite/cache/scale — AC-161…AC-176.

## Current evidence state

- AC documented: **176**.
- AC executed: **0/176**.
- target adapters runtime-certified: **0**.
- `AC-R/AC-S/AC-F/AC-Q/AC-E/AC-B/AC-X/AC-M/AC-P` certifications: **0**.
- WordPress core list-table hook compatibility: **not runtime verified**.
- DataViews compatibility: **not runtime verified**.
- WooCommerce storage adapters: **not runtime verified**.
- exact performance/query/batch thresholds: **OPEN**.

## Rejected shortcuts

- browser-only sorting/filtering marketed as backend semantics;
- one expensive Query/Relation/remote renderer per row as normal behavior;
- fetching unauthorized values then hiding them;
- arbitrary PHP/eval/raw SQL column definitions;
- direct raw meta/SQL mutation bypassing owning APIs;
- hidden-column visibility treated as authorization;
- partial bulk/export completion reported as full success;
- cross-site cache reuse without scope identity.

## Development gate

No WordPress hook, query, REST loader, edit, bulk job, export, Woo adapter probe, browser test or benchmark is authorized by this ADR.

ADR-0014 and the Approval Ledger still require explicit scoped owner consent before executable evidence or implementation.

Current execution count remains **0/176**.