# WPEssential — Admin Columns Market Audit V1

Status: **RESEARCH_COMPLETE / MACHINE_CERTIFICATION_BLOCKED**  
Surface: **8 — Admin Columns (`columns`)**  
Snapshot: **2026-09-01**  
Base: **`eb61c6bfe9d89af949d70e89ba685597d58e2663`**  
Writer branch: **`planning/options-bank-columns-seed-v1`**

## 1. Scope

This audit checks current primary-market evidence for the Admin Columns surface after the repository-native specification and ownership audit. It is a research artifact, not a `MARKET_AUDITED` machine certificate.

The current shared schema `config/product/options-bank-market-audit.schema.json` is hard-coded to Surface 3 / `fields`. A Surface 8 JSON certificate would therefore be invalid on current `main`. The schema is shared/integrator-owned, so this worker does not alter or bypass it.

## 2. Canonical product boundary

Surface 8 owns **list-table column/view/filter/edit presentation definitions**. It does not own source records, reusable query/search engines, field schemas, relation semantics, roles/capabilities, or WooCommerce commerce truth.

Market features are accepted only when they can bind to canonical Data Source, Renderer, Policy, Query, Fields, Relations and adapter contracts without creating a private engine.

## 3. Primary provider — Admin Columns Pro

Primary evidence:

- https://www.admincolumns.com/features/
- https://www.admincolumns.com/
- https://docs.admincolumns.com/article/25-basics-how-to-use-admin-columns-pro
- https://www.admincolumns.com/export/
- https://www.admincolumns.com/bulk-editing/
- https://www.admincolumns.com/inline-editing/
- https://www.admincolumns.com/advanced-custom-fields/

Verified capability families:

| Family | Evidence-backed market behavior | Bank disposition |
|---|---|---|
| Column sets / table views | Multiple column sets/table views, active views, task/context switching, role/user conditionals | parity records under `columns.view.*` and assignment families |
| Column composition | Reorder/customize columns and custom-field display | parity `columns.column.*`, `columns.source.*` |
| Sorting | Sort list-table columns, including custom-field data | parity `columns.sort.*`; backend truth remains required |
| Smart filtering | Filter posts/users/custom fields and combine with list-table management | parity `columns.filter.*`; Query/Data Source compiles backend semantics |
| Inline editing | Edit list-table values without opening the entity editor | parity `columns.edit.*`; mutation delegates to canonical source owner |
| Bulk editing | Bulk-edit current/filter-selected content | parity `columns.bulk.*`; selection query/background safety stays WPE-governed |
| CSV export | Export filtered/sorted list-table data | parity `columns.export.*`; privacy/formula-injection safeguards are WPE exceed |
| Conditional formatting | Conditional formatting exists as a current product capability | parity conditional-format presentation; accessibility safeguard is WPE exceed |
| Quick Add / horizontal layout | Quick Add, horizontal scrolling and table-view ergonomics are current product features | parity where represented in Surface 8 |
| Integrations | ACF, WooCommerce, Meta Box, JetEngine and other add-ons are advertised | compatibility records; underlying domain truth stays external/canonical |
| Portability | Product advertises settings migration/backups | parity import/export definition portability |
| Performance | Provider marketing includes high-volume operations in some feature pages | treated as provider evidence only; no inference of WPE query-budget/no-N+1 guarantees |

Not adopted as proof:
- marketing scale claims are not treated as independent WPE performance certification;
- provider implementation details do not override WPE canonical ownership or Policy;
- inline/bulk edit UI does not imply authorization by visibility.

## 4. Primary/specialist provider — Meta Box Admin Columns

Evidence:

- https://docs.metabox.io/extensions/mb-admin-columns/

Verified behaviors:
- post-type custom fields can be exposed as admin columns;
- terms/users are supported with the corresponding Meta Box extensions;
- placement supports before/after/replace relative to a target column;
- title, content-before/content-after, width and edit/view links are configurable;
- sorting supports normal/numeric modes;
- searchable and taxonomy-filterable options exist.

WPE disposition:
- these map to `columns.source.field`, column order/title/width, text prefix/suffix, link controls, sorting, search and taxonomy-filtering families;
- Meta Box field definitions/values remain Fields/Data Source-owned;
- the Meta Box documentation explicitly describes sorting by altering the WordPress query, reinforcing the WPE rule that sorting must occur in backend query semantics rather than rendered HTML.

No market inference is made for multiple views, generic bulk edit/export, query budgets, batched hydration or cross-provider performance where the cited Meta Box page does not establish them.

## 5. Primary/specialist provider — JetEngine Admin Columns

Evidence:

- https://crocoblock.com/knowledge-base/features/admin-columns-overview/
- https://crocoblock.com/knowledge-base/features/custom-content-type/
- https://crocoblock.com/knowledge-base/jetengine/jetengine-custom-callback-in-admin-columns/
- https://crocoblock.com/knowledge-base/features/relations-overview/

Verified behaviors:
- Admin Columns can be defined for JetEngine CPT/CCT data;
- source types include meta value, post terms, post ID and registered/custom callbacks;
- title, field name, order, prefix, suffix and sortable controls exist;
- CCT columns expose sortable/numeric handling;
- Quick Edit can edit meta fields;
- relation callbacks can display related items and relation-based admin filters can filter by related item.

WPE disposition:
- source/format/sort/quick-edit mappings are parity;
- relation display/filter delegates to Surface 4 Relations and Surface 6 Query/Data Source contracts;
- a callback capability is represented only as a **registered/allowlisted provider extension**, not arbitrary PHP entered into product configuration.

## 6. Market gaps WPE must intentionally exceed

The reviewed market evidence does not establish the following as reliable cross-provider guarantees, so WPE keeps them as explicit exceed/safety contracts rather than pretending they are parity facts:

- backend-truth explanation when sort/filter is unavailable;
- explicit cost class and expensive-column diagnostics;
- no-N+1 and batch-hydration contract;
- query-budget/pathological-plan blocking;
- optimistic conflict protection for inline edits;
- audit evidence for edits;
- all-matching bulk selection stored as a selection query instead of client-side ID explosion;
- export privacy redaction and spreadsheet-formula injection mitigation;
- conditional-format accessibility that is not color-only;
- visibility never being authorization;
- cache invalidation guarantees;
- degraded import behavior for missing Field/Query/Relation/adapter references.

These records are isolated in `columns--wpe-exceed-market-v1.json` and remain `WPE_FUTURE / WPE_EXCEED / P1_EXCEED`.

## 7. Duplicate / ownership resolution

| Capability | Surface 8 owns | Delegate / canonical owner |
|---|---|---|
| Column/view definitions | target, layout, source binding, formatter, interaction presentation | Surface 8 |
| Reusable structured query semantics | view-level reference/binding only | Surface 6 Query |
| Full-text indexing/relevance | optional search binding only | Surface 34 Search |
| Field schema/editor/validation | editor/source reference | Surface 3 Fields / shared Field Schema |
| Relation semantics | related-item/count presentation reference | Surface 4 Relations |
| Source records/mutations | edit intent and UI | owning Data Source/entity surface |
| Roles/capabilities | assignment references/visibility conditions | Surface 30 + shared Policy |
| Persistent manual content order | display/bind order only | Surface 51 Content Order |
| Woo product/order/customer truth | adapter presentation | WooCommerce through A01 |
| Rendering | formatter selection/composition | shared Renderer |
| Authorization | no authority from hidden/visible state | shared Policy |

No duplicate Bank IDs or option paths exist in the Surface 8 candidate. No cross-surface semantic alias/effective-derivation entry is required at this seed based on current evidence.

## 8. Research conclusion

Market research is complete enough to proceed to a machine market audit **after** the shared market-audit schema is generalized or a sanctioned per-surface schema strategy is introduced.

There is no unresolved product decision in this research. The blocker is repository integration infrastructure, not missing provider evidence.

## 9. Integration Requirement

`IR-COLUMNS-002`: designated integrator must make the shared market-audit contract capable of validating Surface 8 without weakening the existing Fields audit, then a Surface 8 `MARKET_AUDITED` JSON certificate and exact-head test may be added.

Until then:
- do not mark Surface 8 `MARKET_AUDITED`;
- do not mark Surface 8 `BANK_REVIEWED`;
- do not finalize UX projection or implementation contract.
