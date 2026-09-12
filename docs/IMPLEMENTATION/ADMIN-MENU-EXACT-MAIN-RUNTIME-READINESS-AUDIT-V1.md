# Admin Menu — Exact-Main Runtime-Readiness Audit V1

Issue: #637  
Parent: #636  
Audit anchor: `35874640a62b9cb08cec5268e949dacd55c8427f`  
Surface: 11 / `admin-menu`

## Decision

**NOT RUNTIME-READY.** Keep `runtime_allowed=false`.

Exact-main machine truth still records Surface 11 as `ATOMIC_INVENTORY_COMPLETE`, not `OPTION_CONTRACT_COMPLETE` or `UX_CONTRACT_COMPLETE`. The exact-main `frameworks/Modules` registry contains no dedicated Admin Menu runtime module. The accepted `ADMIN-MENU-RUNTIME-GAP-MATRIX-V1.md` also states that canonical runtime adapters/services are absent and runtime authorization is false.

The next valid work is product-contract promotion (Bank review/seeding where required, schema-valid per-option contracts, and reviewed UX/effective-state contract), not Admin Menu mutation code.

## Exact-main evidence

- `config/product/atomic-option-contract-progress.json`: Surface 11 = `ATOMIC_INVENTORY_COMPLETE`; next lifecycle gate is schema instances + UX contract review.
- `frameworks/Modules/`: no `AdminMenu` module exists on the audit anchor.
- `docs/IMPLEMENTATION/ADMIN-MENU-RUNTIME-GAP-MATRIX-V1.md`: runtime authorization false; all eight planned families still identify missing canonical runtime ownership.
- Accepted UX/product planning exists, but planning documents do not constitute runtime authority.

## Capability classification

| Family | Exact-main classification | Reason |
|---|---|---|
| Target identity | MISSING / BLOCKED | No canonical Definition/runtime adapter; product-contract gate incomplete. |
| Presentation transform | MISSING / BLOCKED | No deterministic transform/apply layer. |
| Visibility policy | MISSING / BLOCKED | No server-authoritative targeting evaluator. |
| Custom item | MISSING / BLOCKED | No validated route/provider registration path. |
| Profile assignment | MISSING / BLOCKED | No revisioned profile/assignment persistence. |
| Admin bar | MISSING / BLOCKED | No typed node reconciliation layer. |
| Preview actor | MISSING / BLOCKED | No redacted effective-menu diagnostic. |
| Restore/recovery | MISSING / BLOCKED | No revision/reset recovery service. |

## Ownership boundaries

Future runtime must consume shared Ability/Policy authorization and preserve native WordPress menu slugs/hooks. Hiding or transforming navigation must never become an authorization mechanism. Arbitrary PHP/callback/class/script/HTML execution input remains forbidden. Site admin, user admin and network admin graphs must remain explicitly scoped.

## Smallest future bounded runtime slice — only after product gates

Candidate first slice: **read-only Admin Menu target catalog + effective preview**, with no menu mutation.

Candidate files if separately authorized:
- `frameworks/Modules/AdminMenu/AdminMenuModule.php`
- `frameworks/Modules/AdminMenu/AdminMenuDefinition.php`
- `frameworks/Modules/AdminMenu/AdminMenuReadService.php`
- `frameworks/Modules/AdminMenu/AdminMenuAbilityHandler.php`
- focused unit tests under `tests/Unit/Modules/AdminMenu/`
- WordPress integration coverage for site/user/network menu graphs

Required gates before that slice: schema-valid option contracts, reviewed UX/effective-state semantics, explicit Policy abilities, multisite scope contract, and exact-main Supervisor authorization.

## Exit

Issue #637 audit requirement is satisfied by an evidence-backed **BLOCKED / NOT RUNTIME-READY** decision. No runtime/product code, shared-truth files, certification state, destructive mutation, deployment or release is changed by this branch.
