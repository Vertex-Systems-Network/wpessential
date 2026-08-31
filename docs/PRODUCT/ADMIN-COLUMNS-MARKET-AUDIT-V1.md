# WPEssential — Admin Columns Market Audit V1

Status: **RESEARCH_COMPLETE / MACHINE_CANDIDATE_READY / CERTIFICATION_PENDING**  
Surface: **8 — Admin Columns (`columns`)**  
Snapshot: **2026-09-01**  
Original base: **`eb61c6bfe9d89af949d70e89ba685597d58e2663`**  
Synced main: **`8ed1c0389ef314c79a60a6808d232ef625de7b25`**  
Writer branch: **`planning/options-bank-columns-seed-v1`**

## 1. Scope and current gate

This audit checks current primary-market evidence for the Admin Columns surface after repository-native research and ownership resolution.

The earlier shared-schema blocker is resolved by main commit `8ed1c0389ef314c79a60a6808d232ef625de7b25`, which generalized `config/product/options-bank-market-audit.schema.json` beyond Surface 3.

A Surface 8 machine candidate now exists at:

- `config/product/options-bank-audits/columns-market-ecosystem.json`

Its status is intentionally `MARKET_AUDIT_IN_PROGRESS`, not `MARKET_AUDITED`. Research coverage is complete with zero unresolved dispositions, but canonical promotion still requires an integrator-owned exact-head Surface 8 market-audit gate and shared progress update. Existing generic market-audit smoke coverage remains Fields-specific, while Relations uses a dedicated surface gate.

## 2. Canonical product boundary

Surface 8 owns **list-table column/view/filter/edit presentation definitions**. It does not own source records, reusable query/search engines, field schemas, relation semantics, roles/capabilities, or WooCommerce commerce truth.

Market features are accepted only when they bind to canonical Data Source, Renderer, Policy, Query, Fields, Relations and adapter contracts without creating a private engine.

## 3. Primary provider — Admin Columns Pro

Primary evidence:

- https://www.admincolumns.com/features/
- https://www.admincolumns.com/
- https://docs.admincolumns.com/article/25-basics-how-to-use-admin-columns-pro
- https://www.admincolumns.com/export/
- https://www.admincolumns.com/bulk-editing/
- https://www.admincolumns.com/inline-editing/
- https://www.admincolumns.com/advanced-custom-fields/

Verified capability families include:

- multiple column sets/table views and view switching;
- column composition, order, width and custom-field display;
- backend sorting and smart filtering;
- inline editing and bulk editing;
- CSV export respecting current list-table state;
- conditional formatting;
- quick-add and horizontal table ergonomics;
- ACF, WooCommerce, Meta Box, JetEngine and other integrations;
- definition portability/migration.

Provider scale/performance marketing is not treated as proof of WPE query-budget, batch-hydration or no-N+1 guarantees.

## 4. Primary provider — Meta Box Admin Columns

Evidence:

- https://docs.metabox.io/extensions/mb-admin-columns/

Verified behaviors:

- custom fields exposed as admin columns;
- post targets and corresponding Meta Box user/term support;
- before/after/replace placement relative to a target column;
- configurable title, content-before/content-after, width and edit/view links;
- normal/numeric sorting;
- searchable and taxonomy-filterable controls.

WPE maps these to Surface 8 source/presentation/sort/filter contracts. Field definitions and values remain Fields/Data Source-owned. Meta Box sorting documentation reinforces the WPE requirement that sortable behavior change the backend WordPress query before pagination rather than sorting rendered HTML.

## 5. Primary provider — JetEngine Admin Columns

Evidence:

- https://crocoblock.com/knowledge-base/features/admin-columns-overview/
- https://crocoblock.com/knowledge-base/features/custom-content-type/
- https://crocoblock.com/knowledge-base/jetengine/jetengine-custom-callback-in-admin-columns/
- https://crocoblock.com/knowledge-base/features/relations-overview/

Verified behaviors:

- CPT/CCT admin-column definitions;
- meta value, post terms, post ID and callback-style sources;
- title, field name, order, prefix, suffix and sortable controls;
- CCT sortable/numeric handling;
- Quick Edit for meta fields;
- relation display callbacks and relation-aware admin filtering.

WPE maps these to source/format/sort/quick-edit/integration records. Relation truth remains Surface 4-owned and reusable query/filter execution remains Surface 6/Data Source-owned. Callback extensibility is represented only through registered/allowlisted typed providers; arbitrary executable PHP configuration is explicitly rejected.

## 6. Machine-audit coverage

`config/product/options-bank-audits/columns-market-ecosystem.json` declares six Surface 8 market families:

1. `column_views_layout`
2. `source_formatting`
3. `sorting_filtering`
4. `editing_workflows`
5. `export_portability`
6. `ecosystem_integrations`

Coverage:

- primary providers: **3**;
- specialist providers: **1** (`Admin Columns ACF Integration`);
- family mappings: **15**;
- explicit non-applicable family cells: **3**;
- Bank record references: **57**;
- extra dispositions: **4**;
- unresolved research dispositions: **0**.

## 7. WPE exceed / safety boundary

The reviewed market evidence does not establish the following as reliable cross-provider guarantees, so they remain explicit WPE-exceed/safety contracts rather than being mislabeled as parity:

- backend-truth explanation when sort/filter is unavailable;
- explicit cost class and expensive-column diagnostics;
- no-N+1 and batch-hydration contract;
- query-budget/pathological-plan blocking;
- optimistic conflict protection for inline edits;
- audit evidence for edits;
- all-matching bulk selection represented as a selection query rather than a client-side ID explosion;
- export privacy redaction and spreadsheet-formula injection mitigation;
- conditional-format accessibility that is not color-only;
- visibility never being authorization;
- cache invalidation guarantees;
- degraded import behavior for missing Field/Query/Relation/adapter references.

These are isolated in `columns--wpe-exceed-market-v1.json` and remain `WPE_FUTURE / WPE_EXCEED / P1_EXCEED`.

## 8. Duplicate / ownership resolution

| Capability | Surface 8 owns | Canonical delegate / owner |
|---|---|---|
| Column/view definitions | target, layout, source binding, formatter, interaction presentation | Surface 8 |
| Reusable structured query semantics | view-level binding only | Surface 6 Query |
| Full-text indexing/relevance | optional search binding only | Surface 34 Search |
| Field schema/editor/validation | editor/source reference | Surface 3 Fields / Field Schema |
| Relation semantics | related-item/count presentation reference | Surface 4 Relations |
| Source records/mutations | edit intent and UI | owning Data Source/entity surface |
| Roles/capabilities | assignment/visibility references | Surface 30 + shared Policy |
| Persistent manual content order | display/bind order only | Surface 51 Content Order |
| Woo product/order/customer truth | adapter presentation | WooCommerce through A01 |
| Rendering | formatter selection/composition | shared Renderer |
| Authorization | hidden/visible state grants no authority | shared Policy |

No duplicate Bank IDs or option paths exist in the Surface 8 candidate. No Surface 8 semantic alias/effective-derivation entry is required by current evidence.

## 9. Research conclusion

Market research is complete and has been normalized into the generalized machine-audit schema. There is no unresolved product decision.

The remaining blocker is shared certification infrastructure, not evidence coverage:

- Surface 8 canonical progress is not promoted;
- a Surface 8 exact-head market-audit test is not wired;
- therefore `MARKET_AUDITED` is not claimed;
- Bank Review, UX projection and implementation-contract finalization remain downstream-blocked.

## 10. Integration Requirements

`IR-COLUMNS-002` — **RESOLVED UPSTREAM** by main `8ed1c0389ef314c79a60a6808d232ef625de7b25`: the shared market-audit schema now supports canonical surfaces 1–56 without weakening the existing Fields contract.

`IR-COLUMNS-003` — **OPEN**: designated integrator must add or generalize exact-head Surface 8 gates for native audit, market audit and Bank Review, and wire them into applicable CI without replacing or weakening existing Fields/Relations coverage.

Until IR-COLUMNS-003 and canonical progress promotion are resolved, keep `columns-market-ecosystem.json` at `MARKET_AUDIT_IN_PROGRESS` and do not mark Surface 8 `BANK_REVIEWED`.
