# Taxonomy Builder — Runtime Gap Matrix V1

Surface: **2 / Taxonomy Builder**  
Active implementation issue: **#474**  
Planning source: **Issue #468 / merged PR #470**  
Reference contract: `config/product/option-contracts/taxonomy.json`  
Current runtime owner: `frameworks/Modules/Taxonomies/**`

This matrix compares the Taxonomy runtime to the reviewed 71-record Bank + Atomic Option/UX contract. It is live implementation accounting, not runtime certification.

## Existing baseline that must be preserved

The current module provides production-shaped foundations and bounded Runtime Gap Closure slices extend them:

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

## Runtime Gap Closure evidence

### Definition Completeness slice — merged PR #478

Promoted on exact-head green evidence:

- reviewed `menu_name` and `template_name` label support;
- typed `default_term` (`name`, optional `slug`, optional `description`);
- deliberately bounded object-term `args` allowlist (`orderby`, `order`, `fields`);
- explicit rejection of unbounded query structures such as authored `meta_query`;
- focused positive and fail-closed unit coverage;
- all applicable exact-head workflows green, including Taxonomy Runtime and Browser E2E Accessibility.

### Runtime provider-ID slice — merged PR #479

Promoted after exact-head repair and seven green applicable workflows:

- Definitions author only registered provider ID strings inside `runtime_providers`;
- `TaxonomyDefinitionProjector` validates IDs against the shared trusted registry and compiles only JSON-safe `provider_ids`;
- compiled registration generations never persist PHP callables or executable class input;
- `TaxonomyRuntimeRegistrar` resolves IDs through `TaxonomyRuntimeProviderRegistry` immediately before `register_taxonomy()`;
- missing/unavailable runtime providers fail closed and prevent that taxonomy registration;
- direct `rest_controller_class`, `meta_box_cb`, `meta_box_sanitize_cb` and `update_count_callback` authoring remains rejected by the canonical Definition field allowlist;
- provider identifier parsing is deterministic and regression-tested after the original malformed-regex CI failure;
- only lifecycle-safe built-ins are registered by default; trusted PHP modules may register explicit editor/sanitizer/count implementations into the shared registry.

### Read-only diagnostics backend slice — merged PR #480

Promoted on six green applicable exact-head workflows:

- successful server validation exposes the canonical projector's effective `register_taxonomy()` args;
- an overrides-only view distinguishes authored/effective fields from the broader compiled payload;
- provider evidence remains ID-only and JSON-safe in diagnostics;
- association health classifies canonical/runtime relationships as `healthy`, `missing`, `external`, `disabled`, or technical `unavailable` when the runtime API cannot be observed;
- REST route and rewrite path previews are computed without flushing rules or mutating WordPress state;
- invalid/unprojectable candidates return no diagnostics rather than presenting misleading effective state;
- focused tests prove diagnostics remain non-mutating and canonical disabled CPT associations remain visible.

### Diagnostics admin rendering slice — current #474 branch

This bounded UI slice consumes the promoted validation diagnostics without adding another writer or compiler:

- `admin-ui/src/taxonomy.ts` parses diagnostics fail-closed and creates one accessible read-only diagnostics region inside the existing Taxonomy editor;
- successful validation renders runtime registration state, REST route, rewrite path, provider IDs, association-health states, effective arguments and explicit overrides;
- all dynamic values are rendered through DOM `textContent`; no diagnostic value is executed or injected as HTML;
- invalid candidates keep diagnostics hidden, and any editor change or successful save clears stale diagnostics;
- packaged Playwright coverage proves valid/invalid visibility behavior, healthy/missing associations, deterministic route/path/effective-args output and stale-state clearing;
- the axe run now exercises the diagnostics region while it is visible rather than testing only the baseline hidden state.

This diagnostics UI slice is not promoted until its own exact-head CI and review gates pass.

## Atomic contract gap status

| Atomic contract | Current state | Gap / next implementation evidence |
|---|---|---|
| `taxonomy.definition.key` | **PARTIAL** | Ordinary edits correctly block key changes. Add separate guarded key-migration workflow, impact preview and recovery evidence before any rename execution. |
| `taxonomy.definition.naming` | **BASELINE PRESENT** | Core name/singular/description project. Add full UX reset/default state and validation/help contract. |
| `taxonomy.definition.lifecycle` | **BASELINE PRESENT / UX PARTIAL** | Status UI and status Ability exist. Prove revision history/diff and deeper dependency behavior across lifecycle states. |
| `taxonomy.definition.object_types` | **BASELINE PRESENT / HEALTH UI CURRENT SLICE** | Canonical list + external-key preservation exist; promoted diagnostics classify association health and the current UI slice renders it. Search/grouping and deeper dependency impact remain. |
| `taxonomy.diagnostics.association_health` | **CURRENT SLICE — UI + BROWSER EVIDENCE PENDING CI** | Backend health classification is promoted; current UI renders healthy/missing/external/disabled states with packaged browser + axe coverage pending exact-head promotion. |
| `taxonomy.labels.overrides` | **BASELINE PRESENT / UX PARTIAL** | Complete reviewed label family projects after PR #478. Full label editor, reset/default badges and browser evidence remain. |
| `taxonomy.labels.autogenerate` | **PARTIAL / WORDPRESS DEFAULT ONLY** | Current projector supplies name/singular and lets WordPress fill defaults. Add WPE adaptive tag/category-style generation, explicit override badges and reset behavior. |
| `taxonomy.visibility.policy` | **BASELINE PRESENT** | Native values compile. Add inherited/default/explicit/dormant UI semantics and full browser tests. |
| `taxonomy.rewrite.policy` | **BASELINE PRESENT / PREVIEW UI CURRENT SLICE** | Structured rewrite/query_var compile and promoted diagnostics compute a read-only path preview; current UI renders it. Reserved/collision diagnostics and controlled rewrite-flush evidence remain. |
| `taxonomy.permissions.capabilities` | **BASELINE PRESENT** | Four native capability names compile. Add effective map, role-impact read model, lockout diagnostics and reset UX; Roles remains grant owner. |
| `taxonomy.rest.policy` | **PROVIDER-ID PATH PRESENT / PREVIEW UI CURRENT SLICE** | REST exposure/base/namespace and allowlisted controller IDs compile; promoted diagnostics provide route preview and current UI renders it. Route-collision/block-editor diagnostics remain. |
| `taxonomy.default_term.policy` | **BASELINE PRESENT** | Typed default-term projection merged in PR #478. Full editor/help/reset and deeper WordPress behavior evidence remain. |
| `taxonomy.runtime.term_query_policy` | **BOUNDED BASELINE PRESENT** | `sort` plus allowlisted `orderby`/`order`/`fields` defaults compile. Add cost/help UX and ownership guidance with Content Order. |
| `taxonomy.providers.editor` | **PROVIDER-ID RUNTIME PATH PRESENT / UX MISSING** | Shared trusted registry + JSON-safe meta-box/sanitizer IDs + runtime resolution promoted in PR #479. Provider selector/health UX remains. |
| `taxonomy.providers.term_count` | **PROVIDER-ID RUNTIME PATH PRESENT / UX MISSING** | Shared trusted registry + JSON-safe term-count provider IDs + runtime resolution promoted in PR #479. Selector/compatibility UX remains. |
| `taxonomy.workflow.key_migration` | **MISSING / BLOCKED BY DESIGN** | Existing validation makes keys immutable, which is safe. A separate previewed migration workflow is required; ordinary save must remain unable to rename. |
| `taxonomy.portability.definition` | **PARTIAL VIA SHARED DEFINITION INFRASTRUCTURE** | Canonical Definition persistence exists. Add explicit Taxonomy export/import mapping, create-only/update CAS, environment conflict report and portability tests. |
| `taxonomy.diagnostics.effective_args` | **CURRENT SLICE — UI + BROWSER EVIDENCE PENDING CI** | Backend effective args/overrides/provider evidence is promoted; current UI renders it through a read-only panel with packaged browser + axe coverage pending exact-head promotion. |
| `taxonomy.compatibility.cpt_ui_import` | **MISSING** | Add declarative CPT UI import adapter into canonical Taxonomy Definition; no provider shadow storage. |
| `taxonomy.internal.builtin` | **PASS — REJECTED** | `_builtin` is not in accepted top-level Definition keys. Preserve this prohibition and explicit regression evidence. |

## UX gap summary

Current admin UI is a safe baseline plus the current read-only diagnostics rendering slice; it is not the reviewed full UX contract.

Remaining UX families include:

- Essential / Advanced / Expert modes;
- complete label editor + adaptive generation;
- inheritance/default/dormant badges;
- complete visibility controls;
- rewrite/REST collision and dependency diagnostics beyond the current previews;
- capabilities editor/effective map;
- default-term editor;
- controlled provider selectors;
- term-query defaults;
- Find Setting search;
- reset field/section/all;
- guarded key-migration wizard;
- portability/compatibility workflows;
- browser/accessibility evidence for the complete reviewed contract beyond the current diagnostics panel.

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

After the current diagnostics admin rendering slice promotes, Runtime Gap Closure remains dependency-safe:

1. **Tiered editor + complete option UX** — Essential/Advanced/Expert controls, adaptive labels, inheritance/reset/search/help, provider selectors, default term, capabilities and bounded term-query controls.
2. **Portability & Compatibility** — declarative Definition import/export + CPT UI adapter with revision/CAS conflict reporting.
3. **Guarded Key Migration** — separately safety-gated planning/workflow with dry-run, dependency impact and recovery evidence; destructive term mutation remains unauthorized without an explicit later gate.
4. **Runtime certification audit** — exact-head PHP/architecture/WordPress runtime/browser/accessibility/security/compatibility/portability/performance evidence and explicit remaining-gap zeroing before any `RUNTIME_CERTIFIED` promotion.
5. **Product-parity acceptance** — separate competitor-parity evidence and machine promotion after runtime certification.

No line in this matrix authorizes destructive taxonomy-key migration or claims `RUNTIME_CERTIFIED` / `PRODUCT_PARITY_CERTIFIED`.
