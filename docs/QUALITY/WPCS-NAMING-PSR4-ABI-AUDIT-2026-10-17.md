# WPCS naming, PSR-4 and public-ABI audit — 2026-10-17

## Purpose and boundary

This audit addresses the naming-related WPCS findings identified during Issue #1417's quality-gate reconciliation. It is diagnostic evidence and a governance input; it does not change `phpcs.xml.dist`, waive a required check, or authorize a merge.

The current PR #1416 source head is `21fa582724250c6d3fd40aa299447d872a661f39`. Its latest exact-head CI completed with the full-tree WPCS outcome still failing, while the separately executed PHPStan, PHPUnit, syntax, architecture, engineering and focused runtime steps passed. The current guarded evidence reports **55,287 PHPCS errors / 5,104 warnings** across **578 framework PHP files**.

## Ruleset and loader facts

The repository currently has these deliberate contracts:

| Contract | Repository evidence | Consequence |
|---|---|---|
| Root Composer namespace | `WPEssential\\` → `frameworks/` in `composer.json` | A class filename/path change can break PSR-4 resolution. |
| Function bootstrap | `frameworks/functions.php` is Composer-autoloaded as a file | It is not a PSR-4 class file and must remain separately handled. |
| WPCS class filename exception | `phpcs.xml.dist` excludes only `WordPress.Files.FileName.InvalidClassFileName` | The exception protects the PSR-4 class-file contract; it is not a general naming waiver. |
| Public application surface | Contracts, promoted properties, methods, ability IDs, AJAX IDs and REST payload keys are present under `frameworks/` | Blind snake-case renaming can change PHP access, serialized payloads or external WordPress identifiers. |

ADR-0214 and ADR-0012 both establish Composer PSR-4 as the implementation baseline. The architecture documentation also requires one coherent `WPEssential\\` source root rather than drifting module-specific mappings.

## Relevant diagnostic families

The latest retained guarded PHPCS report identifies these naming families as high-volume findings:

| Findings | PHPCS source | Initial disposition |
|---:|---|---|
| 7,093 | `WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase` | Do not mass-rename; classify local variables separately from public/input/output names. |
| 2,055 | `WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase` | Do not mass-rename; review promoted/public properties against ABI and serialization contracts. |

These counts come from the retained exact-toolchain diagnostic artifact documented in `docs/QUALITY/WPCS-GUARDED-BATCH-2026-10-16.md`. They are evidence for classification, not proof that every finding is equally unsafe or that the current full-tree total is unchanged after later source commits.

## Concrete compatibility evidence

The source tree contains naming forms that are contract-bearing rather than accidental formatting:

- `frameworks/Platform/Assets/AssetDescriptor.php` exposes promoted properties such as `$ownerSurfaceId`, `$loadStrategy`, `$adminRoutes` and `$dependencies`.
- `frameworks/Platform/Assets/TrustedAssetBuildEntry.php` exposes `$assetHandle` and `$scriptEntry` as constructor-promoted data fields.
- Interfaces expose camelCase methods such as `registerAdminEnqueue()`, `routeForAdminHook()`, `setScriptStrategy()`, `authorizeInput()` and `capabilityImpact()`.
- WordPress-facing identifiers intentionally use stable strings such as `wpessential/admin-columns/personal-preference-load`, `admin-columns.personal.preference.load` and `wpessential_relations_save`.
- Payload schemas intentionally use machine-facing keys such as `relation_definition_id`, `from_object_id` and `to_object_id`; these must not be inferred from PHP variable-style rules.

Renaming any of these categories without an explicit compatibility map could change one or more of:

1. Composer class loading or reflection/property access;
2. constructor and DTO call sites;
3. serialized or REST response keys;
4. WordPress ability, AJAX, hook or capability identifiers;
5. test fixtures and stored contract snapshots.

## Safe classification for future remediation

The naming debt should be split before any source edit:

| Class | Examples | Allowed next action |
|---|---|---|
| Local-only variables | Variables proven not to cross a method, callback, array payload or reflected boundary | Small manually reviewed rename with unit/static/runtime evidence. |
| Internal private properties | Properties with no reflection, serialization, subclass or external access contract | One class at a time, with PHPStan/PHPUnit and contract checks. |
| Public/promoted DTO properties | `AssetDescriptor` and similar value objects | Preserve names unless a versioned compatibility/accessor migration is explicitly approved. |
| Interface methods and public methods | Contract/interface implementations and callers | No rename in a WPCS cleanup batch; requires an API migration decision. |
| WordPress/external identifiers | Ability, AJAX, REST, hook, capability and storage keys | Never rename as style remediation; preserve exact identifier and document the external contract. |
| Class files/namespaces | PSR-4 classes under `frameworks/` | Preserve the ADR-accepted mapping; validate with Composer/autoload and representative runtime fixtures. |

## Governance recommendation

No repository-wide naming ruleset change is adopted by this audit. The defensible options remain:

- **Option A — full remediation:** fix only classified local/private names in small reviewed batches, preserving public and external contracts with explicit compatibility tests;
- **Option B — immutable baseline ratchet:** retain the existing full-tree WPCS gate as required, while separately enforcing zero new naming findings on added/edited lines and preserving the current baseline as reviewed debt.

Option B is a proposal only. It is not an active bypass or merge permission. Any adoption requires an explicit governance decision, provenance for the immutable baseline, exact-toolchain evidence, changed-line enforcement, and protected review.

## Current decision

- Keep full-tree WPCS required and failing.
- Do not mass-rename camelCase properties, methods, DTO fields or external identifiers.
- Do not alter the PSR-4 exception or introduce a broad naming exclusion.
- Treat local-only variable remediation as the only immediately safe candidate, subject to a bounded diff and exact-head CI.
- Keep PR #1416 open and unmerged until all required checks are green.

This audit does not certify product milestones, release readiness, production deployment or GA.
