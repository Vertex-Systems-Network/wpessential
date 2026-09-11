# Taxonomy Builder — Runtime Gap Matrix V1

Surface: **2 / Taxonomy Builder**  
Active implementation issue: **#474**  
Reconciliation issue: **#556**  
Planning source: **Issue #468 / merged PR #470**  
Reference contract: `config/product/option-contracts/taxonomy.json`  
Current runtime owner: `frameworks/Modules/Taxonomies/**`  
Reconciled implementation anchor: `main @ f25dfe97825cd5b69ebde7bd35cd8155de6bc8bd`

This matrix compares the Taxonomy runtime to the reviewed 71-record Bank + Atomic Option/UX contract. It is live implementation accounting, not runtime certification. A row being promoted here means the bounded implementation/evidence named in that row is present on `main`; it does not by itself promote Surface 2 to `RUNTIME_CERTIFIED` or `PRODUCT_PARITY_CERTIFIED`.

## Existing baseline that must be preserved

The current module provides production-shaped foundations and the promoted Runtime Gap Closure slices extend them without replacing the canonical owner:

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
- accessible admin form, Essential/Advanced/Expert disclosure, validation region, diagnostics and saved-definition table;
- dedicated Taxonomy Runtime CI regression workflow.

Full-final work continues this owner; it does not create a parallel Taxonomy Definition store, compiler, mutation path or authorization engine.

## Promoted Runtime Gap Closure evidence

### Definition completeness — merged PR #478

- reviewed `menu_name` and `template_name` label support;
- typed `default_term` (`name`, optional `slug`, optional `description`);
- deliberately bounded object-term `args` allowlist (`orderby`, `order`, `fields`);
- explicit rejection of unbounded query structures such as authored `meta_query`;
- focused positive and fail-closed unit coverage.

### Allowlisted runtime provider IDs — merged PR #479

- Definitions author only registered provider ID strings inside `runtime_providers`;
- `TaxonomyDefinitionProjector` validates IDs against the shared trusted registry and compiles only JSON-safe provider evidence;
- compiled registration generations never persist PHP callables or arbitrary executable classes;
- `TaxonomyRuntimeRegistrar` resolves IDs through `TaxonomyRuntimeProviderRegistry` immediately before `register_taxonomy()`;
- missing/unavailable runtime providers fail closed;
- direct `rest_controller_class`, `meta_box_cb`, `meta_box_sanitize_cb` and `update_count_callback` authoring remains rejected.

### Read-only diagnostics backend — merged PR #480

- successful server validation exposes the canonical projector's effective `register_taxonomy()` args;
- an overrides-only view distinguishes authored/effective fields from the broader compiled payload;
- provider evidence remains ID-only and JSON-safe;
- association health classifies canonical/runtime relationships as `healthy`, `missing`, `external`, `disabled`, or technical `unavailable` when the runtime API cannot be observed;
- REST route and rewrite path previews are computed without flushing rules or mutating WordPress state;
- invalid/unprojectable candidates return no diagnostics.

### Diagnostics admin rendering — merged PR #483

- one accessible read-only diagnostics region consumes server-authoritative validation evidence;
- runtime registration state, REST route, rewrite path, provider IDs, association health, effective arguments and explicit overrides are rendered without a browser-side compiler;
- dynamic values are rendered via DOM text, not executable HTML;
- stale diagnostics clear on edits/reset/save;
- packaged Playwright + axe evidence covers the visible diagnostics region.

### Adaptive labels — merged PR #486

- typed `automatic_labels`, default enabled;
- hierarchy-aware category-like versus tag-like deterministic generated labels;
- explicit authored `labels` retain final precedence;
- `automatic_labels=false` suppresses generated convenience labels while preserving canonical names and authored overrides;
- invalid types fail closed through the canonical projector.

### Reviewed label authoring UX — merged PR #489

- complete reviewed 28-label override inventory;
- reviewed adaptive-label authoring toggle;
- truthful `Generated`, `WordPress default`, and `Explicit override` source state;
- reset-one/reset-all, create/edit hydration and server-authoritative effective values;
- separate real WordPress + MySQL CAS persistence evidence for reset behavior;
- no alternate Definition store or executable input channel.

### Tier navigation + native visibility inheritance — merged PR #503

- Essential / Advanced / Expert navigation is keyboard reachable;
- optional native visibility booleans use truthful `Default / inherit`, `Enabled`, and `Disabled` authoring;
- inherited values are omitted rather than materializing browser guesses;
- stored values survive tier switching;
- server diagnostics remain the effective-value authority;
- packaged browser + axe evidence promoted on the accepted exact head.

### Accessible Find Setting — merged PR #506

- client-side label/id search over existing canonical controls;
- selecting a result reveals the required tier and opens collapsed details before focus;
- Enter navigation and explicit no-match state;
- no payload/runtime mutation semantics are duplicated in search;
- packaged Playwright + axe evidence promoted.

### Default-term + bounded term-query authoring — merged PR #512

- Expert authoring for existing canonical `default_term`;
- authoring for canonical `sort` and bounded `args.orderby`, `args.order`, `args.fields`;
- omit/reset behavior preserves server/WordPress defaults;
- existing projector allowlists remain authoritative;
- Content Order remains the owner of persistent manual term ordering;
- Find Setting integration and packaged browser + axe evidence promoted.

### Redacted provider catalog — merged PR #515

- deterministic provider inventory for the accepted provider slots;
- browser-visible catalog contains only JSON-safe provider IDs plus current-runtime availability;
- implementation descriptors/classes/callables remain redacted;
- catalog health does not execute providers.

### Controlled provider selector UX — merged PR #545

- Expert selectors are sourced only from the canonical registry catalog;
- only provider IDs are persisted;
- stored unknown IDs are preserved as blocked/stale rather than silently discarded;
- reset returns to WordPress defaults;
- packaged Playwright + axe covers search, authoring, diagnostics, persistence, reset and redaction.

### Rewrite + query-var authoring and canonical collision diagnostics — merged PR #547

- Expert `Default / Enabled / Disabled / Custom` authoring for existing typed `rewrite` and `query_var` fields;
- typed rewrite slug, `with_front`, hierarchy and non-negative endpoint-mask controls;
- edit hydration and reset-to-default semantics;
- server-compiled rewrite preview remains authoritative;
- non-blocking warnings detect collisions with other published canonical Surface 2 taxonomies;
- external WordPress rewrite ownership is deliberately not inferred;
- no rewrite-rule flush side effect was added in this slice.

### REST policy authoring and compatibility diagnostics — merged PR #549

- existing Essential `show_in_rest` remains the exposure switch;
- Advanced typed `rest_base` and `rest_namespace` authoring with omit/reset semantics;
- REST controller implementation remains the allowlisted provider-ID path;
- server-compiled REST preview remains authoritative;
- canonical published REST-route collisions produce non-blocking compatibility warnings;
- REST-disabled taxonomies do not claim a route;
- conservative block-editor compatibility warning is based on observable WordPress state;
- no raw REST controller class/callback input.

### Capability-map authoring and lockout diagnostics — merged PR #551

- Expert authoring for the four native taxonomy capability names: `manage_terms`, `edit_terms`, `delete_terms`, `assign_terms`;
- blank values preserve WordPress defaults;
- effective diagnostics materialize the native default capability map without persisting synthetic overrides;
- non-blocking current-user `capability_lockout_risk` warning uses observable `current_user_can()` state;
- saving capability names does not grant capabilities;
- role grants and user-role assignment remain owned by Roles & Capabilities.

### CPT UI compatibility dry-run mapping — merged PR #553

- declarative CPT UI taxonomy export mapping into the canonical Surface 2 payload;
- supported labels, object types, visibility, rewrite/query-var, REST and default-term data normalize through the existing validation/projector owner;
- executable callback/class source fields fail closed;
- unsupported source fields are reported, not copied into canonical payloads;
- preview/apply-to-local-editor remains non-mutating;
- packaged browser + axe evidence proves dry-run/fail-closed behavior.

### CPT UI compatibility commit through canonical save — merged PR #555

- server remaps and revalidates supplied CPT UI source before persistence;
- persistence delegates to the existing canonical Taxonomy save handler instead of adding a second owner;
- create import becomes a Draft Definition;
- update requires immutable Definition `id` plus positive `expected_revision` CAS and preserves lifecycle status;
- import never infers an update target from taxonomy key and never silently overwrites another Definition;
- canonical save now rejects taxonomy-key changes, so import/API paths cannot bypass key immutability;
- explicit UI commit controls appear only after a current valid preview and invalidate when source changes;
- focused server/unit evidence covers create, update CAS, stale revision, key mismatch and executable-source rejection;
- packaged browser + axe covers the packaged request/UI contract without misrepresenting Playground in-memory persistence as durable CAS evidence.

## Atomic contract gap status after `main @ f25dfe97…`

| Atomic contract | Current state | Remaining evidence / boundary |
|---|---|---|
| `taxonomy.definition.key` | **SAFE BASELINE + IMMUTABILITY HARDENED** | Ordinary save/import key changes are blocked, including the #555 canonical-save guard. A separate guarded key-migration workflow with impact preview, reversible plan and recovery evidence remains unimplemented and separately safety-gated. |
| `taxonomy.definition.naming` | **BASELINE PRESENT / UX PARTIAL** | Core name/singular/description projection exists. Broader help/default-state and generic field/section reset semantics remain UX work if required by the reviewed contract. |
| `taxonomy.definition.lifecycle` | **BASELINE PRESENT / EVIDENCE PARTIAL** | Status Ability/UI and revision-aware mutation exist. Revision history/diff presentation and deeper dependency-impact behavior across lifecycle states remain open. |
| `taxonomy.definition.object_types` | **BASELINE + HEALTH DIAGNOSTICS PROMOTED** | Canonical list, external-key preservation and health rendering are present. Deeper object-type search/grouping and dependency-impact UX remain open. |
| `taxonomy.diagnostics.association_health` | **PROMOTED BACKEND + UI** | PRs #480/#483 provide server classification and accessible rendering. Deeper dependency-impact UX is separate remaining work. |
| `taxonomy.labels.overrides` | **PROMOTED REVIEWED AUTHORING UX** | PR #489 closes the reviewed labels-family authoring/reset/hydration gap with server-authoritative compilation. |
| `taxonomy.labels.autogenerate` | **PROMOTED RUNTIME + AUTHORING UX** | PRs #486/#489 provide hierarchy-aware generation and reviewed authoring semantics. |
| `taxonomy.visibility.policy` | **PROMOTED NATIVE INHERITANCE UX** | PR #503 provides tiered authoring and truthful optional-key inheritance. Cross-field dormant-parent diagnostics beyond simple stored-value preservation remain an optional deeper UX/evidence gap. |
| `taxonomy.rewrite.policy` | **PROMOTED AUTHORING + CANONICAL COLLISION DIAGNOSTICS** | PR #547 closes the editor/preview/canonical-collision gap. Controlled rewrite-rule refresh/flush lifecycle evidence remains open; external rewrite ownership is intentionally not inferred. |
| `taxonomy.permissions.capabilities` | **PROMOTED AUTHORING + CURRENT-USER LOCKOUT DIAGNOSTICS** | PR #551 closes native map authoring/default-effective-state/current-user lockout. A broader read-only role-impact view, if required, must consume Roles & Capabilities truth and must not grant capabilities from Taxonomy. |
| `taxonomy.rest.policy` | **PROMOTED AUTHORING + CANONICAL COMPATIBILITY DIAGNOSTICS** | PR #549 closes base/namespace authoring, canonical route collision and block-editor warning gaps while preserving provider-ID controller ownership. External route ownership is intentionally not inferred. |
| `taxonomy.default_term.policy` | **PROMOTED AUTHORING** | Projection from #478 and editor/reset/find-setting behavior from #512 are present. Deeper real-WordPress default-term behavior evidence remains open if required for certification. |
| `taxonomy.runtime.term_query_policy` | **PROMOTED BOUNDED AUTHORING** | PR #512 exposes only the accepted bounded defaults and keeps Content Order ownership explicit. Additional cost/performance guidance remains evidence/UX work, not permission to add a private ordering engine. |
| `taxonomy.providers.editor` | **PROMOTED PROVIDER-ID RUNTIME + CATALOG + SELECTOR UX** | PRs #479/#515/#545 provide allowlisted runtime resolution, redacted availability catalog and selector/reset/hydration UX. No raw callbacks/classes are authorized. |
| `taxonomy.providers.term_count` | **PROMOTED PROVIDER-ID RUNTIME + CATALOG + SELECTOR UX** | PRs #479/#515/#545 provide the same bounded provider-ID path for term-count behavior. |
| `taxonomy.workflow.key_migration` | **MISSING / BLOCKED BY DESIGN** | Key immutability is intentionally stronger after #555. No rename execution is authorized. Any later workflow requires a separately approved dry-run impact/recovery design before reversible execution is considered. |
| `taxonomy.portability.definition` | **PARTIAL / GENERIC PACKAGE FLOW STILL OPEN** | Canonical Definition persistence and CPT UI compatibility mapping/commit exist, but the earlier Configuration Packages PR #11 was closed unmerged and current `main` does not contain the proposed `wpessential/taxonomy/import-definition` package route. Generic Taxonomy Definition export/import orchestration, environment conflict reporting and portability evidence therefore remain open under the proper package owner. |
| `taxonomy.diagnostics.effective_args` | **PROMOTED BACKEND + UI** | PRs #480/#483 expose/render effective args, authored overrides, provider IDs, association health and route/path previews without adding a second compiler. |
| `taxonomy.compatibility.cpt_ui_import` | **PROMOTED PREVIEW + CANONICAL COMMIT** | PR #553 provides fail-closed dry-run mapping; PR #555 provides create/update-CAS commit through the canonical save owner. |
| `taxonomy.internal.builtin` | **PASS — REJECTED** | `_builtin` remains outside accepted top-level Definition keys and must stay non-authorable. |

## UX gap summary

The following reviewed UX/runtime families are already promoted on `main` and must not be re-opened as duplicate implementation work:

- Essential / Advanced / Expert navigation and native visibility inheritance;
- complete reviewed label authoring + adaptive labels;
- read-only effective diagnostics and association health;
- accessible Find Setting search;
- default-term and bounded term-query controls;
- controlled provider selectors with redacted availability;
- rewrite/query-var authoring and canonical collision warnings;
- REST base/namespace authoring, canonical route warnings and block-editor diagnostics;
- capability-map authoring and current-user lockout warning;
- CPT UI compatibility preview and canonical create/update-CAS import commit.

Genuinely remaining UX/evidence areas after this reconciliation are narrower:

- generic naming/help/default-state and broader reset semantics outside already-promoted families;
- revision history/diff and deeper lifecycle dependency impact;
- deeper object-type search/grouping/dependency-impact UX;
- dormant-parent/cross-field diagnostics where stored explicit values become currently ineffective;
- broader read-only role-impact presentation if the reviewed contract still requires it, without moving grant ownership out of Roles & Capabilities;
- controlled rewrite-rule refresh/flush lifecycle evidence;
- deeper real-WordPress default-term and bounded term-query performance/behavior evidence where required for final certification;
- generic Taxonomy Definition portability through the proper Configuration Packages / Import-Export owner;
- separately safety-gated taxonomy-key migration planning/workflow;
- final exact-main runtime certification audit.

## Cross-surface ownership constraints

Do not absorb these into Taxonomy:

- term fields/meta → **Fields**;
- persistent manual term ordering → **Content Order**;
- automatic term assignment/rules → **Decision**;
- synonym/index/search behavior → **Search**;
- taxonomy archive rendering → **Listings**;
- role grants / role membership → **Roles & Capabilities**;
- generic package envelope/orchestration → **Configuration Packages / Import-Export / Platform owner**.

Taxonomy owns the Definition, its validation/projection, native registration semantics and owner-specific canonical mutation path. Cross-surface integrations must reference or invoke that owner instead of writing Surface 2 storage directly.

## Remaining implementation lane shape

After this reconciliation, dependency-safe Runtime Gap Closure should not repeat already-promoted authoring families. The remaining lane is:

1. **Narrow residual UX/runtime evidence** — naming/reset/help, lifecycle history/diff, object-type dependency impact, dormant-parent diagnostics, optional broader role-impact read model, rewrite-rule refresh lifecycle, and deeper default-term/term-query WordPress evidence where the reviewed contract requires them.
2. **Generic Definition portability** — Taxonomy owner-specific import/export mutation seam plus package-owner orchestration, dry-run conflict reporting, create-only default, explicit revision-safe update mode, and portability tests. CPT UI compatibility import is already separate and promoted through #553/#555.
3. **Guarded key migration** — separately safety-gated planning/workflow only; dry-run dependency impact and recovery evidence are prerequisites. Destructive taxonomy/term mutation remains unauthorized without an explicit later gate.
4. **Runtime certification audit** — exact-main PHP/architecture/WordPress runtime/browser/accessibility/security/compatibility/portability/performance evidence plus explicit remaining-gap accounting before any `RUNTIME_CERTIFIED` promotion.
5. **Product-parity acceptance** — separate competitor-parity evidence and machine promotion only after runtime certification.

## Explicit non-claims

- Issue #474 remains open after this reconciliation.
- This document does not promote `RUNTIME_CERTIFIED`.
- This document does not promote `PRODUCT_PARITY_CERTIFIED`.
- No line authorizes destructive taxonomy-key migration, bulk term mutation, deployment or release.
- Green regression workflows for individual bounded slices are accepted evidence for those slices only; they are not substituted for the later exact-main full runtime certification audit.
