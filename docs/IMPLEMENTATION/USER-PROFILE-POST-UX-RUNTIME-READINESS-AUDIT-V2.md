# User Profiles — Post-UX Runtime Readiness Re-Audit V2

Issue: #821  
Parent: #817  
Audit anchor: `8f796aaf95f0957def439586eb1eb8fb2fd49d91`  
Surface: 14 / `profiles`

## Decision

**READY_FOR_BOUNDED_RUNTIME_SLICE.**

Surface 14 is `UX_CONTRACT_COMPLETE`; the historical V1 product-gate blocker is closed. Dedicated Profiles owner runtime is still absent. Existing Fields, Query and Roles modules plus Platform Abilities/Auth, Definitions, Audit/Observability and WordPress substrate are sufficient for a separately authorized read-only definition/effective-state tranche.

Readiness does not authorize user mutation or certify runtime.

## Runtime-family classification

| Family | V2 classification | Boundary |
|---|---|---|
| Module admission / definitions | REUSABLE_DEPENDENCY | Shared Platform module and definition substrate. |
| Field schema | REUSABLE_DEPENDENCY | Fields remains canonical reusable field owner. |
| Role/capability / access checks | REUSABLE_DEPENDENCY | Roles + Platform Abilities/Auth remain canonical. |
| Query-backed references | REUSABLE_DEPENDENCY | Query owner is present; Profiles consumes references only. |
| Profile definition / layout | MISSING | Requires Surface 14 owner definition. |
| Effective field/privacy preview | MISSING | Requires owner read service with actor/resource context. |
| Profile save / media / password flows | MISSING / OUT_OF_SURFACE | Later bounded owner/delegated operations; excluded now. |
| Privacy export/erasure execution | OUT_OF_SURFACE | Must remain canonical privacy-owner flow. |

## Smallest bounded first runtime slice

**Read-only Profile Definition + field/privacy/effective-state diagnostics.**

Candidate later paths:
- `frameworks/Modules/Profiles/ProfilesModule.php`
- `frameworks/Modules/Profiles/ProfileDefinition.php`
- `frameworks/Modules/Profiles/ProfileReadService.php`
- `frameworks/Modules/Profiles/ProfileAbilityHandler.php`
- `tests/Unit/Modules/Profiles/`
- real WordPress integration for actor/resource visibility, registered meta and multisite scope

The slice may validate Fields/Query references and calculate authorized effective visibility/readability. It must not update users, user meta, passwords, sessions, roles/capabilities, media or privacy state.

## Required future exact-head evidence

- deterministic definition and reference validation;
- IDOR/actor-resource authorization tests;
- explicit allow/deny/absent and degraded reference states;
- proof password hashes, reset tokens, sessions and credentials never enter diagnostics/export/logs;
- multisite and Super Admin caveat tests;
- bounded profile/field collection performance tests;
- Architecture Guards, PHP quality, Platform Compatibility Matrix and applicable package/browser checks.

## Portability, rollback and degraded behavior

Exports contain definitions and stable references only, never credential/session material. Imports validate/remap Fields/Query references. Missing dependencies degrade explicitly. The first slice is read-only, so rollback is disabling/removing the module with no data migration.

## Non-certifications

No user/profile write, password/session operation, role/capability mutation, privacy execution, provider execution, runtime/product certification, production migration, deployment or release is authorized. Atomic lifecycle remains `UX_CONTRACT_COMPLETE`.
