## Fresh exact-head naming audit — verified

The repaired [GitHub Actions run 39031345857](https://github.com/Vertex-Systems-Network/wpessential/actions/runs/39031345857) finished successfully on source `5aa3b516a94474c0734a6f48cfb1ef53327f2806`. Artifact `12100651533` has SHA-256 `a4a9aaa3d691d7ceb8b099620b01852abb36fbe624246dec7ee871c78ed485b8` and contains the original PHPCS JSON, a naming-only JSON classification, and a summary. The source remained clean after the read-only scan.

| Findings | Naming sniff | Disposition |
|---:|---|---|
| 6,992 | `VariableNotSnakeCase` | Classify method locals separately from constructor/API parameters. |
| 2,055 | `UsedPropertyNotSnakeCase` | Preserve public and promoted property contracts. |
| 908 | `MethodNameInvalid` | Preserve public/interface methods pending explicit migration. |
| 127 | `PropertyNotSnakeCase` | Review visibility and reflection/serialization before any change. |
| 24 | `InterpolatedVariableNotSnakeCase` | Review string semantics before changing. |

These **10,106 naming findings** are a subset of WPCS; they are not a current whole-repository PHPCS total. `DashboardWidgetRenderSourceCompiler.php` has 238 naming findings. The three private loading/error/empty state compiler methods each contain 32 `VariableNotSnakeCase` findings on eight method-local symbols. A bounded next batch changes only those locals, preserving the `blueprintId`/`blueprintRevision` named argument labels, response keys, public properties and class filenames. The 96 findings are a candidate reduction; it remains unverified until a fresh source audit.

## Earlier re-audit failure — YAML parse repair

The previous retained naming report is from source head `21fa582724250c6d3fd40aa299447d872a661f39` (55,287 full-tree PHPCS errors / 5,104 warnings). Subsequent PR commits applied bounded local-only naming fixes in `AdminAssetManifest.php`, `RuntimeDiagnosticsSnapshot.php`, and `WpdbJobAttemptStore.php`, plus whitespace-only `AssetDescriptor.php`; therefore the old finding inventory is stale for selecting another edit. The latest integrated branch head before this diagnostic refresh is `78660165dcc8be6c9259ada5f060ee3c32367f91`, based on current main `6fd97dc2f8fc1c2a8abe7d1cb0be080f34803c30`.

Runner `RB-0127` was triggered at exact head `423fde866f963d9f5ca21b39f0efb3f0388fdfb1`, but run `39026004530` failed before creating a job or artifact. Therefore it supplies no new finding inventory. The subsequent `$slug_order` → `$slugOrder` comparator edits were in the wrong direction for the recorded `VariableNotSnakeCase` WPCS sniff. They are restored at the next source head. The new candidate changes three constructor-only camelCase locals in `DashboardWidgetEmptyStateDescriptor.php` to snake_case. Public/promoted properties, interfaces, APIs, serialized keys, WordPress identifiers and WPCS rules are preserved.

The zero-job failure is reproducible as invalid workflow YAML: a bare SHA at the end of `.github/workflows/wpcs-naming-abi-audit.yml` caused a parser error at line 160. The single stray line has been removed. The repaired file parses with the original read-only `naming-abi-audit` job and artifact steps; `RB-0131` will provide the first fresh naming inventory if GitHub executes it successfully.

The next separate source batch changes five method-local names in `DashboardWidgetDiagnosticsAbilityHandler.php` to snake_case. It leaves the response keys `checksum_status`, `runtime_state` and `content_type`, the input key `definition_id`, and constructor properties unchanged. Existing diagnostics unit tests exercise those output fields and the blocked/error states. The batch requires its own exact PR checks; it is not a fresh full-tree WPCS count.

# WPCS naming, PSR-4 and public-ABI audit — 2026-10-17

## Purpose and boundary

This audit addresses the naming-related WPCS findings identified during Issue #1417's quality-gate reconciliation. It is diagnostic evidence and a governance input; it does not change `phpcs.xml.dist`, waive a required check, or authorize a merge.

The retained report source head is `21fa582724250c6d3fd40aa299447d872a661f39`; current PR work continues after exact-head `8d64df8966e1ef602a78d80b8b6a6cacc063edc9`. Its latest exact-head CI completed with the full-tree WPCS outcome still failing, while the separately executed PHPStan, PHPUnit, syntax, architecture, engineering and focused runtime steps passed. The current guarded evidence reports **55,287 PHPCS errors / 5,104 warnings** across **578 framework PHP files**.

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
- The two comparator callbacks retain their originally compliant `$slug_order` local. Three empty-state constructor locals are normalized to snake_case in a bounded change.
- Keep PR #1416 open and unmerged until all required checks are green.

This audit does not certify product milestones, release readiness, production deployment or GA.
