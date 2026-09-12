# Taxonomy Builder — Exact-Main Residual & Runtime Certification Audit V1

Surface: **2 / Taxonomy Builder**  
Parent runtime program: **Issue #474**  
Audit issue: **#626**  
Audit authorization: **merged PR #627**  
Exact-main audit anchor: `81d26a8677ec027736bca2f1a6380faa1d4e7631`  
Product/runtime code anchor immediately before coordination-only PR #627: `ab383c086d125b9ce4c8f5155ac993d1d717e56b`

## Decision

**NOT RUNTIME CERTIFIED. Keep Issue #474 OPEN.**

The exact-main audit proves that the large majority of accepted Taxonomy V1 runtime behavior and previously named residuals are implemented/evidence-accounted. The former dependency/usage and role-impact backend blockers are no longer valid implementation blockers. However, the reviewed V1 UX contract still has a small set of concrete admin-interaction gaps that are observable on exact main and are not covered by the existing green regression evidence.

Therefore this audit does **not** change `config/product/atomic-option-contract-progress.json`; Surface 2 remains `UX_CONTRACT_COMPLETE`. It does not promote `RUNTIME_CERTIFIED` or `PRODUCT_PARITY_CERTIFIED`.

## Audit inputs

The decision reconciles exact current main against:

- `config/product/option-contracts/taxonomy.json`;
- `docs/UI/TAXONOMY-BUILDER-UX-CONTRACT-V1.md`;
- `docs/IMPLEMENTATION/TAXONOMY-BUILDER-RUNTIME-GAP-MATRIX-V1.md`;
- `docs/IMPLEMENTATION/TAXONOMY-BUILDER-POST-OBJECT-DISCOVERY-RESIDUAL-AUDIT-V1.md`;
- `docs/IMPLEMENTATION/TAXONOMY-CAPABILITY-ROLE-IMPACT-DEPENDENCY-AUDIT-V1.md`;
- `docs/IMPLEMENTATION/TAXONOMY-ROLE-IMPACT-CONSUMER-V1.md`;
- current `frameworks/Modules/Taxonomies/**` and `admin-ui/src/taxonomy*.ts` code;
- current packaged Taxonomy browser/accessibility specs;
- exact PR #623 head `2dfa5cb8f2a0c7dfc38d49eef67e3a387cc552ce` workflow evidence.

PR #623 exact head passed all seven applicable workflows: PHP Quality Toolchain, Distributable Package, Taxonomy Runtime, Taxonomy Role Impact, Platform Compatibility Matrix, Architecture Guards, and Browser E2E Accessibility. Those runs are accepted regression evidence for the behavior they exercise; they are not treated as proof for UX behavior that is absent from code/tests.

## Residual reconciliation matrix

| Reviewed / historical area | Exact-main classification | Evidence / boundary | Certification effect |
|---|---|---|---|
| Canonical Definition identity, naming and description | **PROMOTED / PRESENT** | Revisioned Definition owner, validation/projector and existing editor inputs remain present; no exact-main evidence identifies a missing standalone naming runtime semantic. | Not blocking. |
| Lifecycle status + revision/CAS | **PROMOTED / PRESENT** | Canonical draft/published/disabled/archived mutation, revision-aware save/status/import and list revision are already promoted. | Not blocking. |
| Lifecycle history/diff browser | **OPTIONAL / NON-BLOCKING FOR V1** | Post-#578 residual audit explicitly classifies a full history/diff browser as not a V1 exit requirement by itself. | Not blocking. |
| Object-type search/grouping | **PROMOTED / CLOSED** | Issue #577 / merged PR #578 plus packaged browser/axe evidence. | Do not reopen. |
| Association health | **PROMOTED / CLOSED** | Server-authoritative health read model and diagnostics/UI evidence remain present. | Not blocking. |
| Dependency/usage summary | **PROMOTED / CLOSED** | Issue #609 / merged PR #610 provides canonical read-only summary and diagnostics presentation. | Do not reopen. |
| Definition-list runtime health | **PROMOTED / CLOSED** | Issue #609 / merged PR #610; current packaged `taxonomy-builder.spec.mjs` asserts the Runtime health column and row state. | Not blocking. |
| Definition-list dependency count | **PROMOTED / CLOSED** | Issue #609 / merged PR #610; packaged browser spec asserts Dependencies column/count. | Not blocking. |
| Adaptive/reviewed labels | **PROMOTED / CLOSED** | PRs #486/#489 and dedicated browser/accessibility evidence cover reviewed labels/reset/hydration. | Not blocking. |
| Visibility/inheritance | **PROMOTED / CLOSED** | PR #503 plus dormant-parent diagnostics PR #570. | Not blocking. |
| Rewrite/query-var + soft refresh | **PROMOTED / CLOSED** | PRs #547/#559; no ordinary per-request hard flush path. | Not blocking. |
| Native capability-map authoring | **PROMOTED / CLOSED** | PR #551 and current packaged capability spec cover authored/effective values, hydration/default reset and current-user warning. | Not blocking by itself. |
| Canonical Surface 30 role-impact backend/read model | **PROMOTED / CLOSED BACKEND** | Issue #618 / PR #620 provides canonical Surface 30 seam; Issue #621 / PR #623 adds `TaxonomyRoleImpactReadModel`, Definition `read_model.role_impact`, validation `diagnostics.role_impact`, allow/deny/absent/meta-context/unavailable semantics and no-mutation evidence. | Backend dependency is closed. |
| Reviewed **visual role-impact preview** | **GENUINE V1 UX GAP** | `admin-ui/src/taxonomy.ts` does not type/parse/render `role_impact`; `TaxonomyReadModel` and `TaxonomyDiagnostics` client shapes omit it, `parseDiagnostics()` reconstructs diagnostics without it, and `renderDiagnostics()` has no role-impact view. Current `taxonomy-capabilities.spec.mjs` covers current-user warning/effective args but contains no role-impact preview assertion. | **Blocks runtime certification.** |
| REST authoring/controller safety | **PROMOTED / CLOSED** | PR #549 plus allowlisted provider-ID ownership; no arbitrary executable class/callback input. | Not blocking. |
| Default-term authoring + native lifecycle | **PROMOTED / CLOSED** | PRs #478/#512/#561/#569. | Not blocking. |
| Bounded term-query semantics + performance | **PROMOTED / CLOSED** | PRs #512/#563/#576, including deterministic SQL-budget evidence. | Not blocking. |
| Controlled providers | **PROMOTED / CLOSED** | PRs #479/#515/#545 with provider-ID allowlisting, redaction, stale-ID preservation and UI evidence. | Not blocking. |
| CPT UI compatibility import | **PROMOTED / CLOSED** | PRs #553/#555 use canonical create/update-CAS mutation and reject executable source input. | Not blocking. |
| Owner-specific Definition portability | **PROMOTED / CLOSED OWNER SEAM** | PR #572 provides canonical serialized Definition import with checksum/identity/revision rules. Generic package envelope/orchestration remains Surface 26/Platform-owned. | Not blocking Surface 2. |
| Guarded taxonomy-key migration planning | **PROMOTED / CLOSED PLANNING** | PR #580 provides read-only impact/conflict/recovery preview and proves no mutation. | Not blocking planning requirement. |
| Taxonomy-key migration execution | **BLOCKED BY DESIGN / SEPARATELY GATED** | Ordinary save/import keeps taxonomy key immutable. No executor is authorized. This audit does not reinterpret the safety gate or create mutation work. | No execution authorization; cannot support product-parity claims. Re-evaluate only under a separate safety issue. |
| Generic package orchestration | **CROSS-SURFACE / OUT OF SURFACE 2** | Configuration Packages / Import-Export owner responsibility; Surface 2 owner seam exists. | Not blocking Surface 2 owner runtime. |
| Validation + field/severity diagnostics | **PROMOTED / PRESENT** | Server-authoritative validation and packaged diagnostics browser evidence remain present. | Not blocking. |
| Accessibility of promoted Taxonomy UI | **PROMOTED FOR TESTED PATHS** | Packaged Taxonomy browser specs include axe checks; PR #623 Browser E2E Accessibility is green. | Existing paths healthy; new closure UI still needs its own axe evidence. |
| **Unsaved-change guard** | **GENUINE V1 UX GAP** | Reviewed UX contract explicitly requires an unsaved-change guard. Exact-main repository search has no `beforeunload`/unsaved-change implementation, and current Taxonomy browser specs do not exercise navigation-away dirty-state protection. | **Blocks runtime certification.** |
| **Sticky Validate / Save commands** | **GENUINE V1 UX GAP** | Reviewed UX contract explicitly requires sticky Validate and Save commands. Exact-main `admin-ui/src/admin.scss` contains no sticky positioning for the Taxonomy command area, and current browser specs only assert button presence, not sticky behavior. | **Blocks runtime certification.** |
| Broad reset field/section/all wording | **NO OPEN-ENDED GAP AUTHORIZED** | Family-specific reset/default behavior is already promoted across labels, visibility, capabilities and provider/rewrite controls. Per the post-#578 audit, do not infer a generic reset project unless a specific reviewed control is proven missing. | Not independently blocking from this audit. |

## Why green CI does not change the decision

The exact PR #623 head is regression-clean, including the dedicated WP 6.9 / 7.1 × PHP 8.2 / 8.5 role-impact workflow. That proves the canonical backend consumer and no-mutation boundary across supported versions. It does **not** prove that the admin client renders the returned role-impact payload, because the current TypeScript parser/render path and browser assertions do not consume that field.

Likewise, existing Browser E2E/Accessibility coverage proves the exercised Taxonomy screens are functional and axe-clean. It does not prove unsaved-change protection or sticky command behavior when neither behavior is implemented/asserted.

## Minimum next bounded slice

Open one implementation child under #474:

**Taxonomy Builder — Final Admin UX Closure V1**

Scope only:

1. Add a read-only, accessible role-impact preview to the existing Taxonomy diagnostics/permissions UX using the already-promoted `diagnostics.role_impact` / `read_model.role_impact` payload. No role engine, role-store scan or mutation.
2. Add a deterministic unsaved-change guard for dirty Taxonomy editor state, including safe reset after successful save/cancel/hydration and browser evidence.
3. Make Validate / Save commands sticky in the reviewed Taxonomy editor context without hiding content or breaking keyboard/mobile/admin accessibility.
4. Extend packaged browser/accessibility evidence specifically for all three behaviors.
5. Preserve current backend, Ability/Policy, revision/CAS, role-impact ownership and migration-safety boundaries.

Forbidden in that slice:

- role/capability/user mutation;
- direct `wp_roles()` / `get_role()` / raw role-option inspection in Surface 2;
- taxonomy-key migration execution;
- generic package orchestration;
- unrelated editor redesign;
- runtime/product certification promotion from implementation alone.

After that bounded slice merges, run a fresh exact-main certification audit. Do not automatically certify from the implementation PR itself.

## Parent #474 decision

Issue #474 remains **OPEN**.

Current status is best described as:

**V1 runtime/backend residuals substantially closed; final reviewed admin-UX closure still required; runtime certification not yet justified.**

The next implementation lane must be the narrow final admin-UX closure above. No other historical residual should be reopened unless new exact-main evidence proves a regression or a specific accepted V1 contract requirement is still absent.

## Lifecycle decision

- Surface 2 machine status remains: `UX_CONTRACT_COMPLETE`.
- `RUNTIME_CERTIFIED`: **NOT PROMOTED**.
- `PRODUCT_PARITY_CERTIFIED`: **NOT PROMOTED**.
- Taxonomy-key migration execution: **NOT AUTHORIZED**.
- Deployment/release: **NOT AUTHORIZED**.
