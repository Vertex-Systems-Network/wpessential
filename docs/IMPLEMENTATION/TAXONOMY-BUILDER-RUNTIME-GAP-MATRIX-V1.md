# Taxonomy Builder — Runtime Gap Matrix V1

Surface: **2 / Taxonomy Builder**  
Issue: **#468**  
Reference contract: `config/product/option-contracts/taxonomy.json`  
Current runtime owner: `frameworks/Modules/Taxonomies/**`

This matrix compares the existing Taxonomy baseline to the reviewed 71-record Bank + Wave-1 atomic/UX contract. It is an implementation plan, not a runtime certification.

## Existing baseline that must be preserved

The current module already provides meaningful production-shaped foundations:

- canonical Surface 2 Definition ownership;
- `TaxonomyDefinitionProjector` → compiled WordPress registration;
- taxonomy-key safety, reserved-key rejection and runtime collision validation;
- object-type association normalization and missing-object diagnostics;
- name/singular/description projection;
- substantial WordPress label override projection;
- public/hierarchical/show_* visibility controls;
- structured rewrite + query_var validation;
- show_in_rest / rest_base / rest_namespace;
- native capability-map projection;
- `sort` projection;
- revision-aware save/status Ability flows;
- nonce/Policy-backed admin invocation through the shared platform;
- object-type catalog that preserves external/missing keys;
- accessible baseline admin form, validation region and saved-definition table;
- dedicated Taxonomy Runtime CI regression workflow.

Full-final work extends this owner; it does not replace it.

## Atomic contract gap status

| Atomic contract | Current state | Gap / next implementation evidence |
|---|---|---|
| `taxonomy.definition.key` | **PARTIAL** | Ordinary edits correctly block key changes. Add separate guarded key-migration workflow, impact preview and recovery evidence before any rename execution. |
| `taxonomy.definition.naming` | **BASELINE PRESENT** | Core name/singular/description already project. Add full UX reset/default state and validation/help contract. |
| `taxonomy.definition.lifecycle` | **BASELINE PRESENT / UX PARTIAL** | Status UI and status Ability exist. Prove draft/published/disabled/archived runtime emission semantics, revision history/diff and dependency behavior. |
| `taxonomy.definition.object_types` | **BASELINE PRESENT** | Canonical list + external-key preservation exist. Add search/grouping/health UX and deeper dependency impact evidence. |
| `taxonomy.diagnostics.association_health` | **PARTIAL** | Missing-object warning exists; add explicit healthy/missing/external/disabled read model and browser UI. |
| `taxonomy.labels.overrides` | **PARTIAL** | Most reviewed labels are supported. `menu_name` and `template_name` are not currently accepted by `TaxonomyDefinitionProjector::LABEL_KEYS`; close the complete 30-label contract and reset semantics. |
| `taxonomy.labels.autogenerate` | **PARTIAL / WORDPRESS DEFAULT ONLY** | Current projector supplies name/singular and lets WordPress fill defaults. Add WPE adaptive tag/category-style generation, explicit override badges and reset behavior. |
| `taxonomy.visibility.policy` | **BASELINE PRESENT** | Native values compile. Add inherited/default/explicit/dormant UI semantics and full browser tests. |
| `taxonomy.rewrite.policy` | **BASELINE PRESENT** | Structured rewrite/query_var compile. Add URL previews, reserved/collision diagnostics and controlled rewrite-flush evidence. |
| `taxonomy.permissions.capabilities` | **BASELINE PRESENT** | Four native capability names compile. Add effective map, role-impact read model, lockout diagnostics and reset UX; Roles remains grant owner. |
| `taxonomy.rest.policy` | **PARTIAL** | show_in_rest/base/namespace compile. Add registered/allowlisted REST controller provider, route preview/collision checks and block-editor dependency diagnostics. |
| `taxonomy.default_term.policy` | **MISSING** | `default_term` is not an accepted/projected top-level field. Add typed default-term contract and WordPress-runtime tests. |
| `taxonomy.runtime.term_query_policy` | **PARTIAL** | `sort` compiles. Bounded structured `args` is missing; add allowlist, cost warnings and ownership boundary with Content Order. |
| `taxonomy.providers.editor` | **MISSING** | `meta_box_cb` and `meta_box_sanitize_cb` provider mappings are absent. Add registered-provider registry/selection, no arbitrary callable input. |
| `taxonomy.providers.term_count` | **MISSING** | `update_count_callback` provider mapping absent. Add allowlisted provider boundary and compatibility tests. |
| `taxonomy.workflow.key_migration` | **MISSING / BLOCKED BY DESIGN** | Existing validation makes keys immutable, which is safe. A separate previewed migration workflow is required; ordinary save must remain unable to rename. |
| `taxonomy.portability.definition` | **PARTIAL VIA SHARED DEFINITION INFRASTRUCTURE** | Canonical Definition persistence exists. Add explicit Taxonomy export/import mapping, create-only/update CAS, environment conflict report and portability tests. |
| `taxonomy.diagnostics.effective_args` | **MISSING AS USER-FACING DIAGNOSTIC** | Projector can produce registration payload, but no complete effective-args/overrides diagnostic UX is certified. |
| `taxonomy.compatibility.cpt_ui_import` | **MISSING** | Add declarative CPT UI import adapter into canonical Taxonomy definition; no provider shadow storage. |
| `taxonomy.internal.builtin` | **PASS — REJECTED** | `_builtin` is not in accepted top-level definition keys. Preserve this prohibition and add explicit regression evidence. |

## UX gap summary

Current admin UI is a safe baseline, not the reviewed full UX contract. It currently exposes identity, object types, a small behavior subset, lifecycle status, validation and saved definitions.

Missing/partial UX families include:

- Essential / Advanced / Expert modes;
- complete label editor;
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
- browser/accessibility evidence for the complete contract.

## Cross-surface ownership constraints

Do not absorb these into Taxonomy:

- term fields/meta → **Fields**;
- persistent manual term ordering → **Content Order**;
- automatic term assignment/rules → **Decision**;
- synonym/index/search behavior → **Search**;
- taxonomy archive rendering → **Listings**;
- role grants → **Roles & Capabilities**;
- generic package orchestration → **Import/Export / Platform**.

Taxonomy owns the definition and its native registration semantics; integrations reference that owner.

## Next implementation lane shape

After this planning PR promotes and an exact-main Supervisor preflight authorizes implementation, the first runtime closure should be split by dependency-safe ownership rather than one uncontrolled mega-patch:

1. **Definition Completeness** — complete labels, default term, bounded `args`, provider IDs and REST provider contract in the canonical projector/validator.
2. **Diagnostics & UX** — effective state, association health, tiered editor, full controls, reset/search/preview/accessibility.
3. **Portability & Compatibility** — declarative import/export/CPT UI adapter.
4. **Guarded Key Migration** — separately safety-gated migration workflow with recovery evidence.
5. **Final runtime/parity audit** — exact-head PHP/architecture/WordPress runtime/browser/accessibility/security/compatibility evidence, then machine-state promotion only if all contract requirements pass.

No line in this matrix by itself authorizes destructive taxonomy-key migration or claims `RUNTIME_CERTIFIED` / `PRODUCT_PARITY_CERTIFIED`.
