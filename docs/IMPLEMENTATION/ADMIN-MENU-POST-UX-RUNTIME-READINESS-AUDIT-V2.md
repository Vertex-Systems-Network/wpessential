# Admin Menu — Post-UX Runtime Readiness Re-Audit V2

Issue: #818  
Parent: #817  
Audit anchor: `8f796aaf95f0957def439586eb1eb8fb2fd49d91`  
Surface: 11 / `admin-menu`

## Decision

**READY_FOR_BOUNDED_RUNTIME_SLICE.**

This is readiness to begin one separately authorized, reversible source tranche only. It is **not** `RUNTIME_CERTIFIED`, `PRODUCT_PARITY_CERTIFIED`, deployment approval, release approval, production migration authority, or permission for destructive/provider/user/role side effects.

The V1 audit was blocked primarily because the Bank / Atomic Option / reviewed UX gates were incomplete. Those product gates are now closed: the Surface 11 machine contract is `UX_CONTRACT_COMPLETE`, its Bank review is accepted, and the UX validator is centrally registered through merged PR #816. The exact-current module registry still has no dedicated `AdminMenu` module, so owner runtime is MISSING rather than certified. Shared Platform module admission, Abilities/Auth, Definitions, Audit/Observability and WordPress substrate are present and are sufficient to start a read-first owner slice without inventing a new cross-cutting foundation.

## V1 blocker reconciliation

- `ADMIN-MENU-EXACT-MAIN-RUNTIME-READINESS-AUDIT-V1.md`: historical `NOT RUNTIME-READY` verdict is superseded as a readiness decision because its product-contract prerequisite is now satisfied.
- `ADMIN-MENU-RUNTIME-GAP-MATRIX-V1.md`: remains authoritative for required runtime families and safety boundaries; absence of owner implementation remains real.
- `config/product/option-contracts/admin-menu.json`: now `UX_CONTRACT_COMPLETE`; missing/unclassified contract debt is not the current blocker.
- `frameworks/Modules/`: no dedicated `AdminMenu` owner module exists at this anchor.

## Runtime-family classification

| Family | V2 classification | Evidence / boundary |
|---|---|---|
| Module admission / lifecycle | REUSABLE_DEPENDENCY | Shared Platform module-admission gate exists and fails closed. |
| Ability / authorization seam | REUSABLE_DEPENDENCY | Platform Abilities/Auth and existing Roles owner runtime are reusable; menu visibility must never substitute for authorization. |
| Target catalog: site/user/network admin menus | MISSING | Requires Surface 11 owner read adapter over native WordPress menu graphs. |
| Definition / revision model | MISSING | Requires an Admin Menu-owned definition before any mutation path. |
| Effective preview | MISSING | Requires a deterministic, redacted read model; actor preview must not impersonate or bypass policy. |
| Presentation transform / ordering / grouping | MISSING | Later owner behavior; not in first bounded slice. |
| Custom menu item registration | MISSING | Later bounded registration path; arbitrary executable callbacks remain prohibited. |
| Restore / recovery | MISSING | Later revision/reset operation; not required for a read-only first slice. |
| Provider-specific/admin-bar integrations | OUT_OF_SURFACE | Must remain delegated/capability-profiled unless separately certified. |

## Smallest bounded first runtime slice

**Read-only Admin Menu target catalog + effective preview diagnostics.**

Candidate source paths for a later explicitly authorized implementation tranche:

- `frameworks/Modules/AdminMenu/AdminMenuModule.php`
- `frameworks/Modules/AdminMenu/AdminMenuDefinition.php`
- `frameworks/Modules/AdminMenu/AdminMenuReadService.php`
- `frameworks/Modules/AdminMenu/AdminMenuAbilityHandler.php`
- focused tests under `tests/Unit/Modules/AdminMenu/`
- real WordPress integration coverage for site admin, user admin and network admin graphs

The slice must not persist transforms, remove/reorder native menu entries, register arbitrary callbacks, change roles/capabilities, or expose hidden routes. It may only enumerate canonical targets and compute an authorization-aware preview from server-derived scope.

## Required exact-head evidence for that future slice

1. Unit tests for canonical target identity, duplicate handling, deterministic ordering of diagnostics and fail-closed malformed definitions.
2. Real WordPress integration for site/user/network menu graphs, including missing-plugin targets and multisite/Super Admin caveats.
3. Policy tests proving hidden/present menu state never grants or revokes authorization.
4. Accessibility/admin diagnostic evidence for clear unavailable/degraded states if UI is introduced.
5. Architecture Guards, PHP quality, Platform Compatibility Matrix and any path-applicable package/browser gates at the implementation PR head.

## Security, portability and scale boundaries

- Menu presentation is not an authorization mechanism.
- Scope is server-derived; client payload cannot select network/site privilege context.
- Arbitrary PHP, callable/class names, scripts and untrusted executable HTML remain prohibited.
- Import/export may carry stable definitions/references only; environment-specific slugs require validation/remap diagnostics.
- Read catalog/preview must be bounded to registered menu graphs and must not trigger remote/provider execution.
- Missing targets/providers must degrade explicitly rather than being silently treated as valid.

## Rollback and degraded behavior

The first slice is read-only and therefore rollback is removal/disablement of the Surface 11 module registration. If native menu state or a referenced provider is unavailable, diagnostics return explicit `unavailable`/`degraded` evidence and do not invent a target or authorization result.

## Non-certifications

This audit does not authorize menu mutation, persistence, custom executable callbacks, user/role/capability changes, production migration, deployment, release, provider execution, or promotion of the Atomic lifecycle beyond `UX_CONTRACT_COMPLETE`.
