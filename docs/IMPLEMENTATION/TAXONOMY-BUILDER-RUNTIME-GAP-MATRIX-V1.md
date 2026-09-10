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

### Diagnostics admin rendering slice — merged PR #483

Promoted after exact-head Architecture Guards, Distributable Package and Browser E2E Accessibility all passed:

- `admin-ui/src/taxonomy.ts` parses diagnostics fail-closed and creates one accessible read-only diagnostics region inside the existing Taxonomy editor;
- successful validation renders runtime registration state, REST route, rewrite path, provider IDs, association-health states, effective arguments and explicit overrides;
- all dynamic values are rendered through DOM `textContent`; no diagnostic value is executed or injected as HTML;
- invalid candidates keep diagnostics hidden, and any editor change or successful save clears stale diagnostics;
- packaged Playwright coverage proves valid/invalid visibility behavior, healthy/missing associations, deterministic route/path/effective-args output and stale-state clearing;
- the axe run exercises the diagnostics region while visible and reports zero violations in the WPE-owned Taxonomy Builder region.

### Adaptive label generation slice — merged PR #486

Promoted after six applicable exact-head workflows passed:

- `TaxonomyDefinitionProjector` accepts typed boolean `automatic_labels`, defaulting to enabled;
- hierarchical definitions receive deterministic category-like generated labels, while flat definitions receive tag-like generated labels;
- authored `labels` overrides retain final precedence over every generated value;
- `automatic_labels=false` suppresses generated convenience labels while retaining canonical `name`, `singular_name` and authored overrides;
- non-boolean `automatic_labels` values fail closed through the canonical projector;
- dedicated unit coverage proves hierarchical/flat generation, override precedence, explicit opt-out and type rejection;
- packaged Playwright coverage drives the existing Hierarchical editor control through server-authoritative validation and proves the effective args switch between category-like and tag-like label families;
- no new mutation route, callback/class input, DOM compiler or secondary runtime owner was introduced.

### Reviewed label-authoring UX slice — merged PR #489

Promoted after all six applicable exact-head workflows passed:

- the editor renders a collapsed-by-default `Customize labels` section with the complete reviewed 28-label override inventory;
- a reviewed `Generate adaptive labels automatically` toggle authors only the typed canonical `automatic_labels` field;
- per-label state is explicit as `Generated`, `WordPress default`, or `Explicit override` without duplicating server label compilation in the browser;
- per-label reset and reset-all clear authored overrides and reuse the existing form change path so stale validation/diagnostics are removed;
- edit hydration preserves and exposes stored `automatic_labels` plus explicit label overrides; create/reset returns to adaptive generation enabled with zero explicit overrides;
- exact generated values continue to come only from server-authoritative Validate diagnostics and the canonical projector;
- packaged Playwright evidence exercises collapsed/default state, hierarchy family preview, explicit override validation, reset-one/reset-all, adaptive opt-out, create/edit hydration, post-reset effective args and axe accessibility;
- durable label reset persistence is enforced separately in real WordPress 7.1 + MySQL 8.4 evidence through canonical create → `expected_revision=1` CAS update → revision 2 read-back with `automatic_labels=false` and an empty labels map;
- WordPress Playground's SQLite/in-memory Definition fallback is not misrepresented as cross-request durability;
- no raw callbacks/classes, HTML injection, alternate Definition store or new mutation route were added.

### Tier navigation + native visibility inheritance slice — current #474 branch

Current bounded implementation, pending exact-head promotion gates:

- adds keyboard-reachable native button controls for Essential / Advanced / Expert disclosure without changing authorization;
- Essential is the default tier; Advanced and Expert sections are progressively revealed while stored hidden values remain preserved;
- authors the existing canonical optional booleans `show_ui`, `publicly_queryable`, `show_in_menu`, `show_in_nav_menus`, `show_tagcloud` and `show_in_quick_edit` through explicit `Default / inherit`, `Enabled`, and `Disabled` choices;
- `Default / inherit` removes the optional key from the outgoing JSON payload so WordPress/projector inheritance remains authoritative instead of materializing guessed values;
- source state is visible as `Default / inherited`, `Explicit: enabled`, or `Explicit: disabled`;
- no secondary visibility compiler is added: Validate diagnostics continue to prove the canonical projector's effective `register_taxonomy()` arguments;
- packaged Playwright coverage exercises tier state, hidden-value preservation, explicit/inherited source state, effective-args inclusion/omission and axe accessibility with the Expert boundary visible;
- Expert disclosure is informational in this slice; provider selectors, portability and key-migration execution are not silently introduced;
- dormant-parent diagnostics beyond preservation of stored explicit values remain a later UX gap.

This slice does not complete broader Advanced/Expert option authoring and does not certify runtime/product parity.

## Atomic contract gap status

| Atomic contract | Current state | Gap / next implementation evidence |
|---|---|---|
| `taxonomy.definition.key` | **PARTIAL** | Ordinary edits correctly block key changes. Add separate guarded key-migration workflow, impact preview and recovery evidence before any rename execution. |
| `taxonomy.definition.naming` | **BASELINE PRESENT** | Core name/singular/description project. Add broader field reset/default state and validation/help contract. |
| `taxonomy.definition.lifecycle` | **BASELINE PRESENT / UX PARTIAL** | Status UI and status Ability exist. Prove revision history/diff and deeper dependency behavior across lifecycle states. |
| `taxonomy.definition.object_types` | **BASELINE PRESENT / HEALTH UI PROMOTED** | Canonical list + external-key preservation exist; promoted diagnostics classify and render association health. Search/grouping and deeper dependency impact remain. |
| `taxonomy.diagnostics.association_health` | **PROMOTED BACKEND + UI** | Backend health classification and read-only admin rendering are promoted through PRs #480/#483 with packaged browser + axe evidence. Deeper dependency-impact UX remains. |
| `taxonomy.labels.overrides` | **PROMOTED REVIEWED AUTHORING UX** | PR #489 promotes all reviewed override fields, source-state display, reset-one/reset-all, create/edit hydration, reset-effective-args behavior and separate MySQL CAS persistence evidence. |
| `taxonomy.labels.autogenerate` | **PROMOTED RUNTIME + AUTHORING UX** | PR #486 promotes hierarchy-aware runtime generation and PR #489 promotes the reviewed authoring toggle/source/reset semantics while exact values remain server-authoritative. |
| `taxonomy.visibility.policy` | **CURRENT SLICE — NATIVE INHERITANCE UX PENDING CI** | Native values already compile. Current branch adds tiered authoring and truthful optional-key inheritance/explicit semantics with packaged browser/axe evidence; dormant-parent diagnostics and other visibility help remain later gaps. |
| `taxonomy.rewrite.policy` | **BASELINE PRESENT / PREVIEW UI PROMOTED** | Structured rewrite/query_var compile and promoted diagnostics compute/render a read-only path preview. Full editor controls, reserved/collision diagnostics and controlled rewrite-flush evidence remain. |
| `taxonomy.permissions.capabilities` | **BASELINE PRESENT** | Four native capability names compile. Add effective map, role-impact read model, lockout diagnostics and reset UX; Roles remains grant owner. |
| `taxonomy.rest.policy` | **PROVIDER-ID PATH + PREVIEW UI PROMOTED** | REST exposure/base/namespace and allowlisted controller IDs compile; diagnostics provide and render route preview. Full authoring controls and route-collision/block-editor diagnostics remain. |
| `taxonomy.default_term.policy` | **BASELINE PRESENT** | Typed default-term projection merged in PR #478. Full editor/help/reset and deeper WordPress behavior evidence remain. |
| `taxonomy.runtime.term_query_policy` | **BOUNDED BASELINE PRESENT** | `sort` plus allowlisted `orderby`/`order`/`fields` defaults compile. Add cost/help UX and ownership guidance with Content Order. |
| `taxonomy.providers.editor` | **PROVIDER-ID RUNTIME PATH PRESENT / UX MISSING** | Shared trusted registry + JSON-safe meta-box/sanitizer IDs + runtime resolution promoted in PR #479. Provider selector/health UX remains. |
| `taxonomy.providers.term_count` | **PROVIDER-ID RUNTIME PATH PRESENT / UX MISSING** | Shared trusted registry + JSON-safe term-count provider IDs + runtime resolution promoted in PR #479. Selector/compatibility UX remains. |
| `taxonomy.workflow.key_migration` | **MISSING / BLOCKED BY DESIGN** | Existing validation makes keys immutable, which is safe. A separate previewed migration workflow is required; ordinary save must remain unable to rename. |
| `taxonomy.portability.definition` | **PARTIAL VIA SHARED DEFINITION INFRASTRUCTURE** | Canonical Definition persistence exists. Add explicit Taxonomy export/import mapping, create-only/update CAS, environment conflict report and portability tests. |
| `taxonomy.diagnostics.effective_args` | **PROMOTED BACKEND + UI** | Effective args/overrides/provider evidence is promoted through PRs #480/#483 and rendered through a read-only panel with packaged browser + axe evidence. |
| `taxonomy.compatibility.cpt_ui_import` | **MISSING** | Add declarative CPT UI import adapter into canonical Taxonomy Definition; no provider shadow storage. |
| `taxonomy.internal.builtin` | **PASS — REJECTED** | `_builtin` is not in accepted top-level Definition keys. Preserve this prohibition and explicit regression evidence. |

## UX gap summary

Promoted UX now includes read-only diagnostics, adaptive labels and the reviewed complete Labels-family authoring controls. The current slice adds the first broader tiered editor behavior and native visibility inheritance controls, pending exact-head promotion evidence.

Remaining UX families after the current slice include:

- dormant-parent state diagnostics beyond preserved stored values;
- rewrite/query-var authoring and collision/help UX beyond current previews;
- REST authoring controls and collision/block-editor diagnostics;
- capabilities editor/effective map/lockout diagnostics;
- default-term editor;
- controlled provider selectors and health/compatibility UX;
- bounded term-query defaults authoring;
- Find Setting search;
- reset field/section/all outside the Labels/current visibility families;
- deeper object-type search/grouping and dependency impact;
- guarded key-migration wizard;
- portability/compatibility workflows;
- browser/accessibility evidence for the remaining reviewed contract.

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

After the current tier/visibility slice promotes, Runtime Gap Closure remains dependency-safe:

1. **Remaining Advanced/Expert option UX** — rewrite/query-var, REST, capabilities, default term, controlled providers, bounded term-query controls, Find Setting/help and deeper dependency/dormant diagnostics.
2. **Portability & Compatibility** — declarative Definition import/export + CPT UI adapter with revision/CAS conflict reporting.
3. **Guarded Key Migration** — separately safety-gated planning/workflow with dry-run, dependency impact and recovery evidence; destructive term mutation remains unauthorized without an explicit later gate.
4. **Runtime certification audit** — exact-head PHP/architecture/WordPress runtime/browser/accessibility/security/compatibility/portability/performance evidence and explicit remaining-gap zeroing before any `RUNTIME_CERTIFIED` promotion.
5. **Product-parity acceptance** — separate competitor-parity evidence and machine promotion after runtime certification.

No line in this matrix authorizes destructive taxonomy-key migration or claims `RUNTIME_CERTIFIED` / `PRODUCT_PARITY_CERTIFIED`.