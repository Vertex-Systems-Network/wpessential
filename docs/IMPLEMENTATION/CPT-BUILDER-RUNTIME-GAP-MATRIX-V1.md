# CPT Builder — Runtime Gap Matrix V1

Surface: **1 / CPT Builder**  
Issue: **#473**  
Reference contract: `config/product/option-contracts/cpt.json`  
Current runtime owner: `frameworks/Modules/CustomPostTypes/**`

This matrix compares the existing CPT runtime/admin baseline to the reviewed 107-record Bank + normalized Atomic Option/UX contract. It is an implementation plan, not runtime or product-parity certification.

## Existing baseline that must be preserved

The current module already provides substantial production-shaped behavior:

- canonical Surface 1 Definition ownership;
- `CustomPostTypeDefinitionProjector` -> compiled WordPress post-type registration;
- safe post-type key validation, reserved-key rejection and runtime ownership/collision checks;
- name/singular/description projection and broad WordPress label override support;
- public/hierarchical/show/search/admin/nav visibility controls;
- common editor supports, menu position/icon, capability type/map and taxonomy associations;
- structured archive/rewrite/query-var validation;
- show-in-REST, REST base and namespace;
- `can_export` and `delete_with_user` projection;
- revision-aware save/status Ability flows;
- nonce/Policy-backed admin invocation through shared platform infrastructure;
- baseline admin form, validation region and saved-definition list;
- dedicated CPT Runtime regression workflow.

Full-final work extends this owner. Existing working behavior is not rebuilt speculatively.

## Atomic contract gap status

| Atomic contract | Current state | Gap / next implementation evidence |
|---|---|---|
| `cpt.definition.key` | **BASELINE PRESENT / MIGRATION SEPARATE** | Safe creation, reserved-key and runtime collision checks exist; ordinary edit correctly treats key as immutable. Guarded key migration remains a separate workflow. |
| `cpt.definition.naming` | **BASELINE PRESENT** | Name, singular name and description project. Add full default/reset/help state and browser evidence. |
| `cpt.definition.lifecycle` | **BASELINE PRESENT / UX PARTIAL** | Revisioned Definition status flows exist. Prove runtime emission semantics for draft/published/disabled/archived plus revision/diff UX. |
| `cpt.labels.overrides` | **BASELINE PRESENT / UX PARTIAL** | Projector supports the reviewed label family and WordPress-generated base name/singular values. Full label editor, reset and explicit/default badges remain. |
| `cpt.labels.autogenerate` | **PARTIAL** | `automatic_labels` is accepted/validated by the payload shape but the full adaptive generated/explicit/reset behavior is not certified. Implement deterministic generation semantics and tests. |
| `cpt.visibility.policy` | **BASELINE PRESENT** | Native visibility/query/admin controls compile. Add inherited/default/dormant UI semantics and exhaustive browser evidence. |
| `cpt.admin.presentation` | **BASELINE PRESENT / UX PARTIAL** | Menu position/icon and menu visibility compile. Current admin editor exposes only a subset; add complete Advanced controls and validation/help. |
| `cpt.editor.supports` | **BASELINE PRESENT** | Common string-list supports compile, including autosave. Full UI inventory and explicit `supports=false` semantics need closure. |
| `cpt.editor.supports_config` | **MISSING** | Current projector accepts only allowlisted string supports; structured per-feature argument schemas are absent. Add typed registered schemas only, never arbitrary executable configuration. |
| `cpt.editor.block_template` | **MISSING** | `template` and `template_lock` are not accepted/projected by the current top-level definition contract. Add typed block-template/lock projection and WordPress-runtime tests. |
| `cpt.relationships.taxonomies` | **BASELINE PRESENT** | Taxonomy list projection and missing-taxonomy compatibility warning exist. Add richer dependency health/backlinks and browser UX. |
| `cpt.urls.policy` | **BASELINE PRESENT** | Archive, structured rewrite fields and query-var compile. Add controlled route previews/collision diagnostics and rewrite-flush evidence. |
| `cpt.diagnostics.urls` | **MISSING AS USER-FACING DIAGNOSTIC** | Runtime values can be compiled but no certified URL/rewrite/query-var preview/collision read model exists. |
| `cpt.permissions.policy` | **BASELINE PRESENT / UX PARTIAL** | Capability type/map and `map_meta_cap` compile. Add effective capability map, role-impact/lockout diagnostics and full editor. Roles remains grant owner. |
| `cpt.rest.policy` | **PARTIAL** | REST exposure/base/namespace compile. Add allowlisted REST/autosave/revision controller provider identities and `late_route_registration`; raw class/callback input remains forbidden. |
| `cpt.lifecycle.native` | **BASELINE PRESENT** | `can_export` and `delete_with_user` compile. Add explicit UI/help/reset and WordPress-runtime evidence. |
| `cpt.providers.meta_box` | **MISSING** | `register_meta_box_cb` is not an authored projector field. Add registered provider-ID resolution only; never raw callbacks or arbitrary classes. |
| `cpt.workflow.key_migration` | **MISSING / BLOCKED BY DESIGN** | Existing validation prevents ordinary key rename, which is correct. Future workflow requires dry-run, dependency/content impact, collision and recovery evidence before any authorized execution. |
| `cpt.portability.definition` | **PARTIAL VIA SHARED DEFINITION INFRASTRUCTURE** | Revisioned Definition persistence exists. Add explicit CPT import/export mapping, create-only/update CAS, environment conflict reporting and portability tests. |
| `cpt.compatibility.cpt_ui` | **MISSING** | Add declarative CPT UI import adapter into canonical CPT Definitions; no shadow store and no executable option passthrough. |
| `cpt.diagnostics.effective_args` | **MISSING AS USER-FACING DIAGNOSTIC** | Projector produces registration payload internally; expose a read-only effective-args + authored/default diff with accessibility tests. |
| `cpt.internal.builtin` | **PASS — REJECTED** | `_builtin` is not an accepted authored top-level field. Preserve prohibition and add regression evidence. |
| `cpt.internal.edit_link` | **PASS — REJECTED** | `_edit_link` is not an accepted authored top-level field. Preserve prohibition and add regression evidence. |

## Current admin UX gap

The current admin screen is a safe baseline, not the reviewed complete UX. It presently exposes identity/naming, a small Behavior group, common editor supports, lifecycle state, validation and saved definitions.

Missing/partial UX families include:

- Essential / Advanced / Expert tier navigation;
- full labels editor with generated/default/explicit state;
- complete visibility/admin controls;
- block template/lock editor;
- taxonomy association health;
- rewrite/archive/query-var preview and collisions;
- capability editor/effective map;
- allowlisted REST/meta-box provider selectors;
- lifecycle native options;
- effective compiled args + override diff;
- Find Setting search;
- field/section/all reset semantics;
- import/export + CPT UI compatibility workflows;
- guarded key-migration wizard;
- complete browser/accessibility/security/compatibility evidence.

## Cross-surface ownership constraints

Do not absorb these into CPT:

- field/meta schema and storage -> **Fields**;
- taxonomy definition ownership -> **Taxonomy Builder**;
- role grants -> **Roles & Capabilities**;
- archive/list rendering -> **Listings**;
- generic package orchestration -> **Import/Export / Platform**;
- content ordering -> **Content Order**;
- search indexing/synonyms -> **Search & Indexing**.

CPT owns post-type Definition + native registration semantics; integrations reference that owner.

## Runtime closure shape after this planning gate

The next CPT implementation phase should remain dependency-safe and reviewable:

1. **Definition Completeness** — structured supports, block template/lock, provider IDs and remaining REST fields in the canonical projector/validator.
2. **Diagnostics & UX** — complete tiered editor, generated/default state, URL/effective-args/capability/taxonomy diagnostics, reset/search/accessibility.
3. **Portability & Compatibility** — explicit CPT Definition import/export and CPT UI adapter.
4. **Guarded Key Migration** — separate safety-gated workflow; ordinary Save remains immutable.
5. **Runtime certification audit** — exact-head unit/integration/WordPress runtime/browser/accessibility/security/compatibility/portability/performance evidence before any `RUNTIME_CERTIFIED` promotion.
6. **Product-parity acceptance** — competitor-parity evidence and final machine promotion are separate from runtime closure.

## Planning closure result

At the planning boundary represented by Issue #473:

- Options Bank: **BANK_REVIEWED / 107 records**;
- normalized Atomic Options: **23**;
- deterministic Bank projection: **107/107**;
- parity classification: **15 PARITY / 6 EXCEEDS / 2 REJECTED_UNSAFE**;
- `missing=0`;
- `unclassified=0`;
- reviewed UX contract: `docs/PRODUCT/CPT-BUILDER-UX-CONTRACT-V1.md`.

These facts justify `UX_CONTRACT_COMPLETE` only after machine validation. They do **not** justify `RUNTIME_CERTIFIED`, `PRODUCT_PARITY_CERTIFIED`, deployment or release approval.
