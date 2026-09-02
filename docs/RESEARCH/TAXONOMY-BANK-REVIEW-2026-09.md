# Taxonomy Master Options Bank Review — 2026-09-02

Status: **module-local BANK_REVIEWED candidate**  
Surface: **2 — Taxonomy**  
Current `main` base: `1687d7f0412051d5a5d8fbbcc1f9e7af64114a15`  
Claim branch: `agent/taxonomy-bank-reconciliation-v1`  
Recovered from: `planning/options-bank-taxonomy-completion-v1` (stale/diverged history was not merged)

## Scope

This review advances only Surface 2 planning/research. It does not implement Taxonomy runtime, change shared architecture/governance, or race the active Relations runtime critical path.

The current 71-record Taxonomy Bank already contains the complete authored WordPress `register_taxonomy()` argument surface, the current taxonomy label set, and the four native taxonomy capability mappings. Native and market research found additional workflows and diagnostics, but no evidence-backed need for another independent stored Taxonomy option. Therefore the Bank count remains **71** and no count-padding shard is added.

## Native baseline

WordPress target: **7.1**, released 2026-08-19.

Primary native evidence:
- `register_taxonomy()` / `WP_Taxonomy`;
- `get_taxonomy_labels()`;
- object-type bind/unbind functions;
- term insert/update/delete/count APIs;
- `register_term_meta()`;
- `WP_REST_Terms_Controller`;
- `WP_Term_Query`;
- `wp_set_object_terms()`;
- `wpdb` multisite table-prefix behavior.

Resolved native semantics:
- taxonomy keys are validated against current core constraints and reserved/conflict-prone names;
- public/query/admin defaults are effective derivations, not duplicate authored controls;
- hierarchical vs non-hierarchical behavior affects default meta-box and parent semantics;
- `show_in_nav_menus`, `show_tagcloud`, `show_in_quick_edit`, and `show_admin_column` remain separate native registration controls;
- `update_count_callback` is a controlled provider seam; term counts are runtime state;
- `sort` preserves object-term assignment order and does not authorize a second persistent ordering engine;
- `args` is bounded expert configuration for native object-term retrieval defaults;
- configured default terms are materialized by core, but the runtime term row is not another Bank option;
- REST mutation permissions remain capability-authoritative;
- term deletion follows core cleanup/reparent/default-term semantics;
- multisite definition deployment must not imply network-global term data.

## Market normalization

Primary providers:
- Advanced Custom Fields (ACF);
- Secure Custom Fields (SCF);
- Meta Box;
- JetEngine;
- Pods;
- Toolset Types;
- Custom Post Type UI / CPTUI Extended.

Specialist:
- TaxoPress.

The market audit normalizes capabilities into eight semantic families rather than reproducing vendor screens.

Important resolutions:
- ACF-style active/deactivate controls are definition lifecycle workflow, not a second `register_taxonomy()` option.
- CPT UI taxonomy-slug migration with term preservation proves the need for a guarded key-migration workflow over `taxonomy.identity.key`; it is not an alternate key field.
- CPTUI Extended network-wide registration is a deployment/context pattern. Term content and object-term relationships remain site scoped.
- ACF/Meta Box/JetEngine taxonomy fields and term-meta controls remain Fields-owned.
- TaxoPress manual term ordering remains Content Order-owned beyond native object-term assignment order.
- TaxoPress automatic term assignment remains Decision/rules automation.
- TaxoPress synonym/search consumption remains Search-owned; auxiliary term metadata uses Fields.
- Toolset taxonomy archive rendering remains Listings-owned.

## Downstream implementation requirements

These are required behaviors derived from the reviewed Bank; they are not additional authored options:

1. **Definition lifecycle** — safe enable/disable/delete around the canonical Taxonomy definition, with built-in taxonomies protected.
2. **Key migration** — changing `taxonomy.identity.key` must be an explicit migration workflow with term preservation, rollback/recovery strategy, dependency impact and warnings for external references.
3. **Effective previews** — compute inherited/default `register_taxonomy()` arguments rather than storing duplicate values.
4. **URL/route diagnostics** — preview term URLs and REST routes and detect rewrite/query/REST collisions before save.
5. **Capability diagnostics** — show effective taxonomy capabilities and fail closed on lockout-risk configurations; Roles remains the capability-definition/grant owner.
6. **Association health** — preserve temporarily missing object-type keys and report degraded references rather than deleting them.
7. **Deletion impact** — show dependent object types, Fields schemas, Relations, Query/Listings consumers and term-data consequences before destructive lifecycle operations.
8. **Multisite** — use the shared site/network context resolver for definition deployment; never create a Taxonomy-local parallel tenancy selector or network-global term-value store.
9. **Ordering boundary** — native `sort` may preserve object-term edge order; independent/manual persistent ordering references Surface 51 Content Order.
10. **Field boundary** — term metadata and term-selector value/schema behavior reference Surface 3 Fields.

No downstream runtime implementation is authorized by this review artifact.

## Reconciliation notes

The recovered source branch was intentionally not merged. Its Taxonomy-local native audit, market audit, review certificate, review handoff, and standalone review validator were transplanted onto the current claim branch after rechecking current Bank shards, schemas, semantic registry, ownership/dependency contracts, and current official evidence. Shared lifecycle truth remains integration-owned.

The source branch also carried no Taxonomy Bank count expansion: the current candidate still resolves at 71 records with zero unresolved native items, zero unresolved market items, and zero Taxonomy semantic overlap rows.

## Integration Requirements

The Taxonomy worker does not modify global/shared truth. After this candidate head is reviewed and exact-head validation is accepted, the integrator must:

1. Update `config/product/options-bank-progress.json` Surface 2 from `BANK_SURFACE_SEEDED` to `BANK_REVIEWED`.
2. Keep Surface 2 record count at `71`.
3. Recompute the shared lifecycle truth counters exactly from repository state.
4. Run the global `options-bank-progress-contract.php` and the full applicable exact-head CI suite on the integrated SHA.
5. Do not add Taxonomy semantic relationships unless a later independent duplicate/derived authored control is actually introduced.
6. If the standalone Taxonomy review validator is promoted into the aggregate Composer smoke suite, make that shared registration as an integrator-owned change rather than expanding this worker's shared write scope.

No change is required to the canonical 56-surface registry, ownership/no-bypass contract, dependency matrix, generic Bank schemas, manifests, lockfiles, or shared CI.
