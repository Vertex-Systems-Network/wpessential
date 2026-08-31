# Relations — Native WordPress Audit V1

Snapshot: 2026-09-01

Surface: 4 — `relations`

Decision: **`NATIVE_AUDITED` candidate — subject to exact-head CI certification**

Candidate count: **142 Relations / 938 total Bank**

## Purpose

This audit closes the current WordPress-native side of Surface 4 without pretending that WordPress has a generic first-class relation-definition API. WordPress exposes multiple relation-like primitives; WPE must classify each material public behavior explicitly, preserve native semantics through adapters, and keep unrelated ownership out of the Relations surface.

The machine certificate is:

`config/product/options-bank-audits/relations-native-wordpress.json`

The dedicated smoke gate is:

`tests/Smoke/options-bank-relations-native-audit-contract.php`

## Audit result

The current audit contains **44 explicit dispositions**:

- 26 `BANK_RECORD` mappings;
- 0 `PROVIDER_MAPPING` items;
- 10 `SYSTEM_RUNTIME` behaviors;
- 6 `OUT_OF_SURFACE` ownership decisions;
- 1 `LEGACY_COMPATIBILITY` item;
- 1 `CORE_INTERNAL` item;
- **0 unresolved**.

`NATIVE_AUDITED` is valid only when this exact source head passes the repository's applicable CI gates.

## Native object ↔ taxonomy-term relationships

WordPress stores object-term edges through its taxonomy relationship model rather than a generic entity graph.

### Mutation semantics

`wp_set_object_terms()` is the primary supported relation-like mutation primitive. The audit explicitly classifies:

- taxonomy-backed relation mode;
- append versus replace behavior;
- duplicate-edge avoidance;
- `term_taxonomy_id` as the relationship identity returned/stored by core rather than treating `term_id` as the edge identity;
- native `term_order` behavior;
- the dependency of effective relationship ordering on the taxonomy's `sort` registration setting;
- runtime object/term operands;
- empty-set replacement behavior;
- missing slug → term creation as Taxonomy-owned rather than Relations-owned.

One important safety boundary is explicit: the low-level setter does not prove that the object's post type is registered to the target taxonomy. WPE's native relation adapter therefore requires endpoint-association validation before mutation rather than blindly mirroring the permissive core call.

### Removal and lifecycle

The audit classifies:

- `wp_remove_object_terms()` as selective runtime disconnect;
- `wp_delete_object_term_relationships()` as bulk runtime unlink;
- native relationship hooks/cache/count maintenance as runtime infrastructure;
- `wp_delete_post()` object-term cleanup as an explicit native Relations lifecycle invariant.

Term deletion itself remains owned by Surface 2 — Taxonomy. Default-term substitution, term hierarchy reparenting and term lifecycle are not duplicated into Relations.

### Query semantics

Native relation traversal is mapped through:

- `wp_get_object_terms()`;
- `WP_Term_Query` with `object_ids`;
- ID/object result projections;
- `get_objects_in_term()` reverse traversal;
- `is_object_in_term()` existence checks;
- count projection;
- `term_order`-aware relationship ordering.

Post-specific wrappers such as `wp_get_post_terms()`, `wp_set_post_terms()` and `wp_add_object_terms()` remain runtime convenience behavior rather than becoming duplicate authored options.

## Native hierarchical post-parent relation

WordPress `post_parent` is treated as a specialized native hierarchy adapter, not as permission to model every WPE relation in the posts table.

The audit explicitly maps:

- `post_parent` persistence through post insert/update;
- hierarchical post-type applicability;
- the current WordPress hierarchy-loop guard exposed through `wp_insert_post_parent` / core loop checks;
- direct parent ID and parent object reads;
- ancestor traversal;
- child traversal;
- `WP_Query` constraints for `post_parent`, `post_parent__in`, and `post_parent__not_in`;
- hierarchical child reparenting when a parent post is deleted.

The WordPress hierarchy loop guard is deliberately separate from the existing WPE-future generic graph `prevent_cycles` option. One is a current native adapter invariant; the other is a broader future relation-graph policy.

Attachment reparenting during post deletion is explicitly assigned to Surface 28 — Media rather than being silently absorbed into Relations.

## Seven evidence-backed Bank additions

The V1 seed already represented native taxonomy and post-parent adapters broadly, but the audit proved seven current-core behaviors needed atomic records:

1. `relations.nativeaudit.taxonomy_term_taxonomy_identity` — preserve `term_taxonomy_id` relationship identity.
2. `relations.nativeaudit.taxonomy_sort_dependency` — expose taxonomy `sort` dependency for native edge order.
3. `relations.nativeaudit.taxonomy_object_type_validation` — validate object↔taxonomy compatibility before connecting.
4. `relations.nativeaudit.post_parent_hierarchy_requirement` — constrain native post-parent mode to the hierarchy contract.
5. `relations.nativeaudit.post_parent_loop_guard` — preserve current WordPress hierarchy-loop protection.
6. `relations.nativeaudit.post_parent_delete_reparent` — preserve hierarchical child reparent semantics on deletion.
7. `relations.nativeaudit.taxonomy_object_delete_cleanup` — preserve object-term edge cleanup when a post is deleted.

All seven are canonical current-native records:

- `NATIVE_HARD`;
- `HARD`;
- `CURRENT_NATIVE`;
- `MUST_HAVE`;
- `P0_NATIVE`.

No record was added merely to increase coverage counts.

## Out-of-surface ownership

The audit prevents Relations from becoming an ownership sink. Explicit non-Relations ownership includes:

- term creation from missing slugs → Taxonomy;
- default-category substitution → Taxonomy;
- term deletion/default replacement/term-child reparenting → Taxonomy;
- taxonomy-resource ancestor traversal → Taxonomy;
- attachment reparenting → Media;
- direct core `term_relationships` table access → Platform/core-internal only, not an authored Relations contract.

## Direct SQL policy

Direct writes to `$wpdb->term_relationships` are classified `CORE_INTERNAL`. WPE native adapters use supported WordPress APIs and normalize native behavior behind the Relations contract. The Bank does not expose arbitrary core-table SQL as a user-configurable relation mode.

## Machine contract

The Relations native-audit smoke gate verifies:

- the shared native-audit schema remains parseable;
- canonical Surface 4 ownership;
- primary evidence comes from Developer.WordPress.org;
- unique `relations.native.*` disposition IDs;
- every `BANK_RECORD` reference resolves to a real Relations Bank record;
- out-of-surface/internal items name a valid different canonical owner;
- mandatory taxonomy/post-parent/query/delete dispositions are present;
- coverage counters match actual items;
- unresolved count is zero;
- all seven new native records have the exact canonical native lifecycle classification;
- Relations contains exactly **142** records after native gap closure;
- progress truth reports Surface 4 as `NATIVE_AUDITED` with 142 records.

The contract intentionally does not pin the global Bank total. Global counts are derived across all surfaces by `options-bank-progress-contract.php`, avoiding the stale cross-surface coupling previously found during the Relations seed.

## Lifecycle decision

| Gate | Result |
| --- | --- |
| Relations seed integrity | PASS dependency |
| Native object-term mutation/query lifecycle | CLOSED candidate |
| Native post-parent hierarchy/query/delete lifecycle | CLOSED candidate |
| Native ownership boundaries | CLOSED candidate |
| Native core-table/internal paths | Explicitly classified |
| Unresolved native dispositions | 0 |
| Relations records | 142 |
| Global Bank records | 938 candidate |
| `NATIVE_AUDITED` | **YES only after exact-head CI** |
| `MARKET_AUDITED` | NO — next gate |
| `BANK_REVIEWED` | NO |

## Non-claims

- This audit does not claim a Relations runtime implementation exists.
- It does not make WordPress taxonomy relationships equivalent to a generic graph.
- It does not claim current competitor parity.
- It does not promote market-only or WPE-future possibilities to native behavior.
- It does not certify migrations or production rollout.

## Next gate

After exact-head certification/merge, run **Relations Market Audit V1**. It must map current material capabilities provider-by-provider for JetEngine Relations, Meta Box Relationships, ACF bidirectional relationship fields, Pods, Toolset, ACPT, and any justified specialist ecosystems. Only evidence-backed gaps should add Bank records before `MARKET_AUDITED` is considered.
