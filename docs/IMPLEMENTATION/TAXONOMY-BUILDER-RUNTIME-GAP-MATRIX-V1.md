# Taxonomy Builder — Runtime Gap Matrix V1

Surface: **2 / Taxonomy Builder**  
Active implementation issue: **#474**  
Planning source: **Issue #468 / merged PR #470**  
Reference contract: `config/product/option-contracts/taxonomy.json`  
Current runtime owner: `frameworks/Modules/Taxonomies/**`

This matrix compares the Taxonomy runtime to the reviewed 71-record Bank + Atomic Option/UX contract. It is live implementation accounting, not runtime certification.

## Existing baseline that must be preserved

The current module provides production-shaped foundations and the first Runtime Gap Closure slices now extend them:

- canonical Surface 2 Definition ownership;
- `TaxonomyDefinitionProjector` → compiled WordPress registration;
- taxonomy-key safety, reserved-key rejection and runtime collision validation;
- object-type association normalization and missing-object diagnostics;
- name/singular/description projection;
- complete reviewed WordPress label override projection;
- public/hierarchical/show_* visibility controls;
- structured rewrite + query_var validation;
- show_in_rest / rest_base / rest_namespace;
- native capability-map projection;
- `sort` plus bounded object-term `args` projection;
- typed `default_term` projection;
- JSON-safe allowlisted runtime provider IDs with last-responsible runtime resolution;
- revision-aware save/status Ability flows;
- nonce/Policy-backed admin invocation through the shared platform;
- object-type catalog that preserves external/missing keys;
- accessible baseline admin form, validation region and saved-definition table;
- dedicated Taxonomy Runtime CI regression workflow.

Full-final work extends this owner; it does not replace it.

## Runtime Gap Closure evidence promoted / in current provider slice

### Definition Completeness slice — merged PR #478

Promoted on exact-head green evidence:

- reviewed `menu_name` and `template_name` label support;
- typed `default_term` (`name`, optional `slug`, optional `description`);
- deliberately bounded object-term `args` allowlist (`orderby`, `order`, `fields`);
- explicit rejection of unbounded query structures such as authored `meta_query`;
- focused positive and fail-closed unit coverage;
- all six applicable workflows green, including Taxonomy Runtime and Browser E2E Accessibility.

### Runtime provider-ID slice — current #474 branch

The provider extension path is intentionally split across persistence and runtime boundaries:

- Definitions author only registered provider ID strings inside `runtime_providers`;
- `TaxonomyDefinitionProjector` validates IDs against the shared trusted registry and compiles only JSON-safe `provider_ids`;
- compiled registration generations therefore never persist PHP callables or executable class input;
- `TaxonomyRuntimeRegistrar` resolves IDs through `TaxonomyRuntimeProviderRegistry` immediately before `register_taxonomy()`;
- missing/unavailable runtime providers fail closed and prevent that taxonomy registration;
- direct `rest_controller_class`, `meta_box_cb`, `meta_box_sanitize_cb` and `update_count_callback` authoring remains rejected by the canonical Definition field allowlist;
- only lifecycle-safe built-ins are registered by default; trusted PHP modules may register explicit editor/sanitizer/count implementations into the shared registry.

This provider slice must still pass exact-head CI before it is considered promoted.

## Atomic contract gap status

| Atomic contract | Current state | Gap / next implementation evidence |
|---|---|---|
| `taxonomy.definition.key` | **PARTIAL** | Ordinary edits correctly block key changes. Add separate guarded key-migration workflow, impact preview and recovery evidence before any rename execution. |
| `taxonomy.definition.naming` | **BASELINE PRESENT** | Core name/singular/description project. Add full UX reset/default state and validation/help contract. |
| `taxonomy.definition.lifecycle` | **BASELINE PRESENT / UX PARTIAL** | Status UI and status Ability exist. Prove draft/published/disabled/archived runtime emission semantics, revision history/diff and dependency behavior. |
| `taxonomy.definition.object_types` | **BASELINE PRESENT** | Canonical list + external-key preservation exist. Add search/grouping/health UX and deeper dependency impact evidence. |
| `taxonomy.diagnostics.association_health` | **PARTIAL** | Missing-object warning exists; add explicit healthy/missing/external/disabled read model and browser UI. |
| `taxonomy.labels.overrides` | **BASELINE PRESENT / UX PARTIAL** | Complete reviewed label family projects after PR #478. Full label editor, reset/default badges and browser evidence remain. |
| `taxonomy.labels.autogenerate` | **PARTIAL / WORDPRESS DEFAULT ONLY** | Current projector supplies name/singular and lets WordPress fill defaults. Add WPE adaptive tag/category-style generation, explicit override badges and reset behavior. |
| `taxonomy.visibility.policy` | **BASELINE PRESENT** | Native values compile. Add inherited/default/explicit/dormant UI semantics and full browser tests. |
| `taxonomy.rewrite.policy` | **BASELINE PRESENT** | Structured rewrite/query_var compile. Add URL previews, reserved/collision diagnostics and controlled rewrite-flush evidence. |
| `taxonomy.permissions.capabilities` | **BASELINE PRESENT** | Four native capability names compile. Add effective map, role-impact read model, lockout diagnostics and reset UX; Roles remains grant owner. |
| `taxonomy.rest.policy` | **PROVIDER-ID PATH PRESENT / DIAGNOSTICS PARTIAL** | REST exposure/base/namespace compile and controller provider IDs are JSON-safe/allowlisted. Route preview/collision and block-editor dependency diagnostics remain. |
| `taxonomy.default_term.policy` | **BASELINE PRESENT** | Typed default-term projection merged in PR #478. Full editor/help/reset and WordPress behavior evidence remain. |
| `taxonomy.runtime.term_query_policy` | **BOUNDED BASELINE PRESENT** | `sort` plus allowlisted `orderby`/`order`/`fields` defaults compile. Add cost/help UX and ownership guidance with Content Order. |
| `taxonomy.providers.editor` | **PROVIDER-ID RUNTIME PATH PRESENT / UX MISSING** | Shared trusted registry + JSON-safe meta-box/sanitizer IDs + runtime resolution are implemented in current slice. Add provider selector UX and exact-head compatibility evidence. |
| `taxonomy.providers.term_count` | **PROVIDER-ID RUNTIME PATH PRESENT / UX MISSING** | Shared trusted registry + JSON-safe term-count provider IDs + runtime resolution are implemented in current slice. Add selector/compatibility UX and exact-head evidence. |
| `taxonomy.workflow.key_migration` | **MISSING / BLOCKED BY DESIGN** | Existing validation makes keys immutable, which is safe. A separate previewed migration workflow is required; ordinary save must remain unable to rename. |
| `taxonomy.portability.definition` | **PARTIAL VIA SHARED DEFINITION INFRASTRUCTURE** | Canonical Definition persistence exists. Add explicit Taxonomy export/import mapping, create-only/update CAS, environment conflict report and portability tests. |
| `taxonomy.diagnostics.effective_args` | **MISSING AS USER-FACING DIAGNOSTIC** | Projector produces registration payload, but no complete effective-args/overrides diagnostic UX is certified. |
| `taxonomy.compatibility.cpt_ui_import` | **MISSING** | Add declarative CPT UI import adapter into canonical Taxonomy Definition; no provider shadow storage. |
| `taxonomy.internal.builtin` | **PASS — REJECTED** | `_builtin` is not in accepted top-level Definition keys. Preserve this prohibition and explicit regression evidence. |

## UX gap summary

Current admin UI is a safe baseline, not the reviewed full UX contract. It exposes identity, object types, a small behavior subset, lifecycle status, validation and saved definitions.

Remaining UX families include:

- Essential / Advanced / Expert modes;
- complete label editor + adaptive generation;
- inheritance/default/dormant badges;
- complete visibility controls;
- rewrite and REST previews;
- capabilities editor/effective map;
- default-term editor;
- controlled provider selectors;
- term-query defaults;
- effective args/override diff;
- association health panel;
- Find Setting search;
- reset field/section/all;
- guarded key-migration wizard;
- portability/compatibility workflows;
- browser/accessibility evidence for the complete reviewed contract.

## Cross-surface ownership constraints

Do not absorb these into Taxonomy:

- term fields/meta → **Fields**;
- persistent manual term ordering → **Content Order**;
- automatic term assignment/rules → **Decision**;
- synonym/index/search behavior → **Search**;
- taxonomy archive rendering → **Listings**;
- role grants → **Roles & Capabilities**;
- generic package orchestration → **Import/Export / Platform**.

Taxonomy owns the Definition and its native registration semantics; integrations reference that owner.

## Remaining implementation lane shape

After the current provider-ID slice promotes, Runtime Gap Closure remains open and dependency-safe:

1. **Diagnostics & UX** — effective state, association health, tiered editor, complete controls, adaptive labels, reset/search/preview/accessibility and provider selectors.
2. **Portability & Compatibility** — declarative Definition import/export + CPT UI adapter with revision/CAS conflict reporting.
3. **Guarded Key Migration** — separately safety-gated planning/workflow with dry-run, dependency impact and recovery evidence; destructive term mutation remains unauthorized without an explicit later gate.
4. **Runtime certification audit** — exact-head PHP/architecture/WordPress runtime/browser/accessibility/security/compatibility/portability/performance evidence and explicit remaining-gap zeroing before any `RUNTIME_CERTIFIED` promotion.
5. **Product-parity acceptance** — separate competitor-parity evidence and machine promotion after runtime certification.

No line in this matrix authorizes destructive taxonomy-key migration or claims `RUNTIME_CERTIFIED` / `PRODUCT_PARITY_CERTIFIED`.
