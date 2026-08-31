# WPEssential — Content Order & Sequence Manager

Status: **Phase 0 exhaustive planning / no development authorization**  
Edition: **Pro**  
Surface: **51**

## 1. Purpose

Provide deterministic, reusable editorial ordering for WordPress and WPE entities without globally hijacking every query. The module must meet and exceed Post Types Order / Intuitive Custom Post Order style workflows while preserving query ownership and explicit context.

## 2. Owned concepts

- Sequence Definition;
- Ordered Item Membership;
- Scope/Context;
- Ordering Adapter;
- Revision;
- Conflict/Drift Report.

`menu_order` is one adapter/storage target, not the universal canonical model.

## 3. Screens

- Overview
- Sequences
- Reorder
- Context Rules
- Adapters
- Conflicts
- Revisions
- Import / Export
- Diagnostics
- Settings

## 4. Supported targets

- posts/pages/CPTs;
- hierarchical sibling groups;
- taxonomy terms;
- media where owner enables it;
- users only through explicit safe adapter;
- Multisite sites for Network Admin only;
- WPE custom-table entities;
- registered third-party entities through SDK adapter.

## 5. Sequence modes

- native `menu_order` compatible;
- WPE independent sequence;
- taxonomy-term order adapter;
- relation-scoped child order;
- query/listing-local order;
- manual pinned subset + deterministic fallback;
- scheduled/effective-date sequence;
- language/locale-specific sequence via certified translation adapter.

## 6. Context

A sequence can apply by:
- post/entity type;
- taxonomy/term;
- parent entity;
- relation;
- archive/query profile;
- site/network;
- language;
- membership/role only for presentation contexts;
- Woo catalog context through adapter;
- custom registered context.

The admin UI must explain exactly where the order is consumed.

## 7. Reorder UX

- drag/drop;
- keyboard move up/down/top/bottom;
- multi-select move;
- numeric position input;
- search/filter while preserving global sequence;
- paginated virtualized lists for large data sets;
- thumbnails/status/parent/context columns;
- unsaved-change indicator;
- optimistic UI only with verified server acknowledgement;
- clear failure rollback;
- accessible announcements.

Moving an item across pages must not require increasing Screen Options to an unsafe size.

## 8. Hierarchical ordering

- parent/child tree mode;
- sibling-only reorder default;
- optional reparent action separately permissioned;
- preserve children when parent moves;
- cycle prevention;
- max-depth validation;
- orphan diagnostics.

Reordering and changing parent are distinct operations.

## 9. Query integration

Consumers may explicitly request:
- canonical sequence;
- sequence + secondary tie-break;
- pinned-first sequence;
- sequence fallback when item absent.

Compatibility adapter may apply native `menu_order` to standard WordPress main queries only when the sequence/profile explicitly enables auto-apply.

Never override a query with an explicit incompatible `orderby` silently.

## 10. Conflict detection

Detect:
- Post Types Order / Intuitive CPO / similar query hooks;
- theme/plugin custom `pre_get_posts` ordering;
- Woo catalog ordering ownership;
- translation plugins with per-language order;
- duplicate order values;
- stale IDs;
- query profile ignoring sequence;
- multiple WPE sequences matching same context with equal precedence.

Provide explain trace, not guesswork.

## 11. Ordering invariants

- stable deterministic tie-break;
- unique sequence position within scope where required;
- atomic or version-checked reorder batch;
- concurrency conflict detection;
- no silent loss when two editors reorder simultaneously;
- deleted item produces tombstone/cleanup event;
- imported order validates all references.

## 12. Scale

Profiles:
- 100;
- 1K;
- 10K;
- 100K ordered items.

Use sparse/fractional/rank-key or equivalent evidence-backed storage to avoid O(N) full rewrites for every move at scale. Exact algorithm remains implementation evidence-gated.

## 13. Import / Export / Migration

- import native `menu_order`;
- detect common ordering plugins;
- map taxonomy order metadata when source is recognized;
- preview conflicts;
- preserve source until verification;
- export portable sequence definitions + item stable references.

## 14. Permissions

Candidate capabilities:
- `wpe_order_read`
- `wpe_order_create`
- `wpe_order_update`
- `wpe_order_reorder`
- `wpe_order_publish`
- `wpe_order_network_manage`
- `wpe_order_import_export`

## 15. Abilities / AI

Abilities: list/get/create/update/validate/reorder-preview/publish/compare/import-plan/export.

AI may propose sequence rules, detect anomalies and draft reorder plans. It must not publish broad catalog/network ordering changes without approval.

## 16. Multisite

- site sequences site-owned by default;
- network site-ordering only from Network Admin policy;
- network templates may instantiate site sequences;
- cross-site aggregate listing can consume a network sequence only if target set is explicit;
- clone copies definitions but revalidates item identities.

## 17. MUST NOT

- no global unconditional `pre_get_posts` hijack;
- no assumption that `menu_order` is safe for every entity/query;
- no UI-only success before server persistence;
- no automatic reparent during reorder;
- no translation synchronization without adapter contract;
- no hidden query rewrite when consumer explicitly supplied another ordering.

## 18. Evidence

Reserved namespace: **ORD-001…ORD-176**, executed **0/176**.

Evidence groups cover schema/context, drag/keyboard UX, native storage, independent storage, hierarchy, query integration, conflicts, concurrency, imports, Multisite, translation/Woo adapters, scale and golden regression scenarios.