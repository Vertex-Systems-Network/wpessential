# Settings Pages — Post-UX Runtime Readiness Re-Audit V2

Issue: #819  
Parent: #817  
Audit anchor: `8f796aaf95f0957def439586eb1eb8fb2fd49d91`  
Surface: 12 / `settings`

## Decision

**READY_FOR_BOUNDED_RUNTIME_SLICE.**

This decision authorizes no runtime by itself. It only establishes that a later, separately approved read-first source tranche can start without an unresolved product-contract blocker. Surface 12 is now `UX_CONTRACT_COMPLETE`; owner runtime remains absent from `frameworks/Modules`.

The historical V1 `NOT RUNTIME-READY` decision was tied to incomplete Bank/Atomic/UX gates. Merged PR #816 closed those prerequisites. Current shared Platform provides Definitions, Abilities/Auth, Secrets, Audit/Observability and WordPress substrate; Fields remains the canonical reusable control/schema owner. No separate cross-cutting foundation is required for a read-only first slice.

## Runtime-family classification

| Family | V2 classification | Boundary |
|---|---|---|
| Module admission | REUSABLE_DEPENDENCY | Shared fail-closed module gate. |
| Definition/revision substrate | REUSABLE_DEPENDENCY | Platform Definitions/Audit may be consumed; Surface 12 owns settings-page definitions. |
| Field/control semantics | REUSABLE_DEPENDENCY | Fields owns reusable schema/control semantics. |
| Secret references | REUSABLE_DEPENDENCY | Platform Secrets owns secret material; Settings may store references only. |
| Native option/site-network-user scope inspection | MISSING | Requires Surface 12 owner read adapter. |
| Effective settings structure/validation diagnostics | MISSING | Requires owner read service with server-derived scope. |
| Save/reset/migration | MISSING | High-impact later tranche; excluded from first slice. |
| Provider-backed settings | OUT_OF_SURFACE | Provider-specific values/health remain delegated. |

## Smallest bounded first runtime slice

**Read-only Settings Page Definition + effective scope/structure diagnostics.**

Candidate later implementation paths:
- `frameworks/Modules/Settings/SettingsModule.php`
- `frameworks/Modules/Settings/SettingsDefinition.php`
- `frameworks/Modules/Settings/SettingsReadService.php`
- `frameworks/Modules/Settings/SettingsAbilityHandler.php`
- `tests/Unit/Modules/Settings/`
- real WordPress integration for site/network/user option-context inspection

The slice may validate references, schema ownership, scope, option existence/autoload diagnostics and provider/secret-reference health. It must not update/delete options, reveal secret material, run migrations, reset settings or execute providers.

## Required future exact-head evidence

- deterministic definition validation and effective-state unit tests;
- site/network/user scope integration with server-derived context;
- proof that secret values are never rendered, logged, exported or copied into diagnostics;
- native option/autoload read behavior across supported WordPress/PHP matrix;
- fail-closed malformed/unknown field and provider references;
- Architecture Guards, PHP quality, Platform Compatibility Matrix and applicable package/browser checks.

## Boundaries and degraded behavior

Fields owns reusable field schema; Platform Secrets/Vault-equivalent storage owns credentials. Unknown option/provider/reference state is explicit `unavailable` or `degraded`, never silently defaulted into an authored value. Portability is reference/definition-only and must remap environment-sensitive references. Large settings sets require bounded reads and no unbounded autoload scans.

Rollback of the first slice is module disablement/removal; because it is read-only it creates no recovery migration.

## Non-certifications

No save/reset/migration, provider execution, secret handling, destructive mutation, runtime/product certification, production migration, deployment or release authority is granted. Atomic lifecycle remains `UX_CONTRACT_COMPLETE` until separate implementation and certification gates pass.
