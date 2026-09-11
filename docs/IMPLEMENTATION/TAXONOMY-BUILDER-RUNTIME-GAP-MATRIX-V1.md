# Taxonomy Builder — Runtime Gap Matrix V1

Surface: **2 / Taxonomy Builder**  
Active implementation issue: **#474**  
Current reconciliation issue: **#581**  
Planning source: **Issue #468 / merged PR #470**  
Reference contract: `config/product/option-contracts/taxonomy.json`  
Current runtime owner: `frameworks/Modules/Taxonomies/**`  
Reconciled implementation anchor: `main @ 55fb91dde92abdf2f5c2b9a22b488131ff85578e`

This document is implementation accounting, not certification. A row marked promoted means the bounded implementation and evidence named here are present on the reconciled `main`; it does **not** promote Surface 2 to `RUNTIME_CERTIFIED` or `PRODUCT_PARITY_CERTIFIED`.

## Canonical ownership baseline

The current Taxonomy module remains the single Surface 2 owner for:

- revisioned canonical Taxonomy Definitions;
- Definition validation and `TaxonomyDefinitionProjector` compilation;
- WordPress taxonomy registration through the shared runtime registrar;
- taxonomy-key safety, reserved-key rejection and immutable-key enforcement;
- object-type associations and association-health diagnostics;
- native labels, visibility, rewrite/query-var, REST, capability-map, default-term and bounded term-query policy;
- allowlisted runtime provider IDs resolved at the last responsible runtime moment;
- revision-safe save/status/import mutation paths;
- deferred soft rewrite refresh scheduling for routing-relevant changes;
- read-only taxonomy-key migration impact/recovery planning;
- nonce/Policy-backed admin invocation and the WordPress Ability bridge.

Runtime Gap Closure must continue this owner. It must not create a parallel Definition store, compiler, authorization engine, rewrite engine, role-grant engine, term-order engine or generic package orchestrator inside Surface 2.

## Promoted Runtime Gap Closure evidence

| Slice | Promoted evidence |
|---|---|
| **Definition completeness — PR #478** | Reviewed `menu_name`/`template_name` labels, typed `default_term`, bounded `args.orderby/order/fields`, fail-closed rejection of unbounded query structures. |
| **Allowlisted runtime providers — PR #479** | JSON-safe provider IDs only; runtime registry resolution immediately before registration; raw callbacks/classes remain rejected. |
| **Read-only diagnostics — PRs #480/#483** | Server-authoritative effective args, explicit overrides, association health, REST/rewrite previews and accessible packaged UI rendering without a browser-side compiler. |
| **Adaptive + reviewed labels — PRs #486/#489** | Hierarchy-aware generated labels, full reviewed override inventory, source-state UX, reset/hydration and persistence evidence. |
| **Tier navigation / visibility inheritance — PR #503** | Essential/Advanced/Expert authoring, truthful inherit/default semantics and packaged accessibility evidence. |
| **Find Setting — PR #506** | Accessible setting search that reveals the canonical control/tier without duplicating payload semantics. |
| **Default-term + bounded term-query authoring — PR #512** | Expert controls for typed `default_term`, `sort` and bounded object-term args with omit/reset semantics. |
| **Provider catalog + selector — PRs #515/#545** | Redacted provider inventory, current-runtime availability, controlled provider-ID selectors and stale-ID preservation. |
| **Rewrite/query-var authoring — PR #547** | Typed modes, rewrite details, hydration/reset, server preview and canonical collision diagnostics. |
| **REST authoring/compatibility — PR #549** | `rest_base`/`rest_namespace`, canonical route warnings, observable block-editor compatibility diagnostics and provider-ID controller ownership. |
| **Capability-map authoring — PR #551** | Four native capability names, effective native defaults and current-user lockout warning without role-grant ownership. |
| **CPT UI compatibility — PRs #553/#555** | Fail-closed preview mapping plus canonical create/update-CAS commit; executable callback/class source input remains rejected. |
| **Deferred soft rewrite refresh — PR #559** | Site-scoped one-shot pending marker, routing-relevant scheduling only, `wp_loaded` soft flush, retry on failure and real-WordPress lifecycle evidence. No hard/unconditional flush path. |
| **Native default-term lifecycle — PRs #561/#569** | Real WordPress create/reuse, option tracking, insert/publish defaulting, protected default deletion, sole-term deletion fallback, direct-remove termless semantics and non-destructive retention after Definition disable. |
| **Bounded native term-query semantics — PR #563** | Real WordPress term-order persistence, registered-args precedence and bounded `fields=ids` behavior without adding a private ordering engine. |
| **Dormant visibility/query diagnostics — PR #570** | Non-blocking warnings for explicit `show_in_menu` under effective `show_ui=false` and enabled/custom `query_var` under effective `publicly_queryable=false`; collision ownership follows effective public-queryability while authored values remain preserved. |
| **Owner-specific Definition portability seam — PR #572** | `wpessential/taxonomy/import-definition` accepts canonical serialized Taxonomy Definitions, preserves portable UUID/slug/status/dependencies, validates checksum/metadata, supports create-only/no-change plus explicit update-CAS, blocks identity/key/slug conflicts, and persists through the same canonical mutation implementation with rewrite-refresh scheduling. |
| **Bounded term-query performance evidence — PR #576** | Real WordPress SQL-query-count evidence compares 3 vs 35 relationships, stays within a fixed four-statement budget, proves stable query-count shape and confirms bounded `fields=ids` reads introduce no term-meta SQL. No wall-clock SLA or private query engine was added. |
| **Guarded taxonomy-key migration planning preview — PR #580** | Read-only `wpessential/taxonomy/key-migration-preview` validates proposed target ownership/schema, reports term URL/rewrite, REST, query-var, object-association and dependent-Definition impact, supplies recovery guidance, and real WordPress evidence proves Definition/term/relationship/rewrite-rule state is unchanged. **Execution remains unavailable and separately safety-gated.** |

## Atomic contract status at `main @ 55fb91dd…`

| Atomic contract | Current state | Remaining boundary |
|---|---|---|
| `taxonomy.definition.key` | **SAFE BASELINE + IMMUTABILITY + READ-ONLY MIGRATION PLANNING** | Ordinary save, CPT UI import and portable Definition import cannot rename a taxonomy key. PR #580 adds impact/recovery preview only; migration execution remains separately safety-gated and unavailable. |
| `taxonomy.definition.naming` | **BASELINE PRESENT / UX RESIDUAL** | Name/singular/description projection and editing exist. Broader help/default-state and generic reset semantics are potential UX work only where the reviewed contract demonstrably requires them. |
| `taxonomy.definition.lifecycle` | **BASELINE PRESENT / UX RESIDUAL** | Revision-aware save/status mutation is present. Revision-history/diff presentation and deeper dependency-impact UX remain separate potential work rather than a registration gap. |
| `taxonomy.definition.object_types` | **BASELINE + HEALTH DIAGNOSTICS PROMOTED** | Canonical associations, missing/external preservation and health rendering exist. Deeper search/grouping/dependency-impact UX remains a residual, not a runtime registration gap. |
| `taxonomy.diagnostics.association_health` | **PROMOTED BACKEND + UI** | PRs #480/#483 remain authoritative; only deeper dependency-impact presentation is potentially open. |
| `taxonomy.labels.overrides` | **PROMOTED** | Reviewed authoring/reset/hydration and server-authoritative compilation are present. |
| `taxonomy.labels.autogenerate` | **PROMOTED** | Hierarchy-aware generated labels and explicit override precedence are present. |
| `taxonomy.visibility.policy` | **PROMOTED + DORMANT DIAGNOSTICS** | PR #503 authoring plus PR #570 native dormant-parent diagnostics close the reviewed WordPress-forced normalization gap. |
| `taxonomy.rewrite.policy` | **PROMOTED AUTHORING + COLLISION + REFRESH LIFECYCLE** | PR #547 authoring/collision plus PR #559 deferred soft refresh evidence are present. External rewrite ownership remains intentionally not inferred. |
| `taxonomy.permissions.capabilities` | **PROMOTED AUTHORING + CURRENT-USER WARNING** | Broader read-only role-impact presentation is optional residual work only if the reviewed contract still requires it; grants stay with Roles & Capabilities. |
| `taxonomy.rest.policy` | **PROMOTED** | Authoring, canonical route collisions and block-editor compatibility diagnostics are present. External route ownership remains intentionally not inferred. |
| `taxonomy.default_term.policy` | **PROMOTED AUTHORING + REAL WP LIFECYCLE EVIDENCE** | PRs #478/#512/#561/#569 cover typed projection, UX and native behavior. No custom auto-assignment layer was introduced. |
| `taxonomy.runtime.term_query_policy` | **PROMOTED AUTHORING + REAL WP SEMANTICS + PERFORMANCE EVIDENCE** | PRs #512/#563/#576 cover bounded policy, native precedence/order behavior and deterministic SQL-budget/no-termmeta evidence. Persistent manual ordering stays with Content Order. |
| `taxonomy.providers.editor` | **PROMOTED** | Allowlisted provider-ID runtime, catalog, selector/reset/hydration and redaction are present. |
| `taxonomy.providers.term_count` | **PROMOTED** | Same bounded provider-ID ownership path is present for term-count behavior. |
| `taxonomy.workflow.key_migration` | **READ-ONLY PLANNING PREVIEW PROMOTED / EXECUTION BLOCKED** | PR #580 provides target validation, conflict/impact reporting and recovery planning without mutation. No rename executor is authorized; any future execution requires separate explicit safety approval and migration/recovery evidence. |
| `taxonomy.portability.definition` | **OWNER SEAM PROMOTED / PACKAGE ORCHESTRATION OUTSIDE SURFACE 2** | PR #572 closes the Taxonomy owner-specific import mutation seam. Canonical list/get already expose portable Definition records. Generic package envelope, multi-owner preflight, environment conflict planning and import/export UI remain the Configuration Packages / Import-Export owner’s responsibility. |
| `taxonomy.diagnostics.effective_args` | **PROMOTED** | Server-authoritative effective args/overrides/route previews are rendered without a second compiler. |
| `taxonomy.compatibility.cpt_ui_import` | **PROMOTED** | Preview plus canonical create/update-CAS commit are present. |
| `taxonomy.internal.builtin` | **PASS — REJECTED** | `_builtin` remains non-authorable. |

## Closed residuals that must not be re-opened as duplicate work

The following are no longer valid “remaining gap” claims on this anchor:

- controlled rewrite-rule refresh/flush lifecycle evidence;
- deeper native default-term behavior evidence for create/reuse/defaulting/deletion/disable retention;
- native bounded term-query ordering/precedence evidence;
- bounded term-query deterministic performance evidence for the reviewed allowlisted path;
- dormant-parent diagnostics for WordPress-forced `show_in_menu` and front-end `query_var` normalization;
- the Surface 2 owner-specific portable Definition import mutation seam;
- guarded taxonomy-key migration **planning/preview** with impact and recovery evidence.

## Genuine remaining implementation areas / boundaries

At this anchor the remaining Surface 2 lane is materially narrower:

1. **Naming/help/reset UX residuals** — only where the reviewed contract demonstrably requires semantics not already covered by promoted family-specific resets.
2. **Lifecycle history/diff and dependency-impact presentation** — potential read-model/UX work around revision and lifecycle consequences; canonical mutation semantics already exist.
3. **Object-type discovery/dependency-impact UX** — potential deeper search/grouping or impact presentation without changing canonical association ownership.
4. **Optional broader capability role-impact read model** — only by consuming Roles & Capabilities truth; Taxonomy must not grant or assign roles.
5. **Taxonomy-key migration execution boundary** — planning is promoted, but execution is not an active authorized lane. It remains blocked unless separately and explicitly safety-approved.
6. **Generic package orchestration** — package envelope, multi-owner preflight/conflict plan and import/export UI belong to Configuration Packages / Import-Export, not Surface 2. Surface 2 exposes its owner mutation seam through PR #572.
7. **Final exact-main certification audit** — a later accounting/evidence exercise only; no certification claim is made by this matrix.

## Cross-surface ownership constraints

Do not absorb these into Taxonomy:

- term fields/meta → **Fields**;
- persistent manual term ordering → **Content Order**;
- automatic term assignment/rules → **Decision**;
- synonym/index/search behavior → **Search**;
- taxonomy archive rendering → **Listings**;
- role grants / role membership → **Roles & Capabilities**;
- generic package envelope/orchestration → **Configuration Packages / Import-Export / Platform owner**.

Taxonomy owns the Definition, validation/projection, native registration semantics and owner-specific canonical mutation path. Cross-surface integrations must invoke that owner instead of writing Surface 2 storage directly.

## Safety boundary

Taxonomy-key migration remains intentionally stronger than ordinary editing: current save/import paths reject key changes. PR #580 adds a read-only planner only. No destructive rename, term rewrite, relationship rewrite, option rewrite, rewrite flush or recovery execution is authorized by this reconciliation. Any future migration execution requires its own separately approved safety scope and exact evidence plan.
