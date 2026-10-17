## Exact source naming improvement — 33 CSV selection findings removed

The [read-only audit run 39051322816](https://github.com/Vertex-Systems-Network/wpessential/actions/runs/39051322816) succeeded on `f5ab9368a52c6c2198d28757fc3ec28959307908`; artifact `12108046642` has SHA-256 `9d5c0a070f4a6edbf73454524468ec7a7a51544d5de88d51e7fba59be17194d8`. Naming findings **9,499 → 9,466**, `VariableNotSnakeCase` **6,385 → 6,352**, framework PHPCS errors **54,575 → 54,542**, warnings stable **5,103**. All 33 reductions were method-local names in `AdminColumnsCsvExportService::exactSelectedRows()` and `selectColumns()`. The full-tree quality gate remains separate and red.

Next candidate normalizes seven locals across `exportScoped()`, `exportCurrentPage()`, `exportSelectedRows()` and `exportAllMatchingScope()` (29 prior findings). Public signatures, private method parameters, selection keys, CSV encoding, row membership and ordering stay unchanged. `RB-0144` requires fresh exact-head audit and CI before any reduction is claimed.

## Exact source naming improvement — 24 CSV collection findings removed

The [read-only audit run 39050644066](https://github.com/Vertex-Systems-Network/wpessential/actions/runs/39050644066) succeeded on `d6d9964774160ce998d1c0857045f152c4e3f2ac`; artifact `12107503073` has SHA-256 `da455b19b56dae2536a6f93f471bdc0afa2ad3eb6f3d091a9cc65f11af7980b9`. Naming findings **9,523 → 9,499**, `VariableNotSnakeCase` **6,409 → 6,385**, framework errors **54,599 → 54,575**, warnings stable at **5,103**. All 24 reductions were selected locals in `AdminColumnsCsvExportService::collectAllMatching()`. These framework-scope counts do not establish a full-tree quality pass.

Next candidate changes seven local identifiers across private `exactSelectedRows()` and `selectColumns()` (33 prior findings), preserving four method parameter names, CSV column keys, row membership and output ordering. `RB-0143` tracks fresh audit/full CI; no new reduction is claimed before that result.

## Next bounded source candidate — Admin Columns CSV collection locals

The exact `9c87fc8` read-only inventory reports 34 `VariableNotSnakeCase` findings in `AdminColumnsCsvExportService::collectAllMatching()`. Ten are references to the private method's existing `viewId` and `expectedViewRevision` parameters; those parameter names remain unchanged. The candidate normalizes **24 findings** across six method-local symbols (`baselineRevision`, `baselineColumns`, `encoderColumns`, `pageSize`, `pageRows`, `probeRevision`) and synchronizes the `pageRows` PHPDoc local annotation. A reverse-mapping check must preserve every other line and all CSV output/contract identifiers. `RB-0142` requires fresh source audit and exact-head CI before any reduction is claimed.

## Exact warning correction — four alignment findings removed

The [read-only audit run 39049801229](https://github.com/Vertex-Systems-Network/wpessential/actions/runs/39049801229) succeeded on `9c87fc8fec05879a01a9e4b89cc28a5499455f43`; artifact `12107032077` has SHA-256 `ef9fa8e6e599d157197031e2964cfdb2ae9e804e6f0c9262746a25878659ed10`. Framework-scope warnings **5,107 → 5,103**, with naming findings stable **9,523** and framework errors stable **54,599**. All four new assignment-alignment warnings introduced at the prior source were removed. The full-tree WPCS merge gate remains red; this report is read-only framework-scope evidence.

## Exact source naming improvement — 28 more predicate findings removed; alignment correction pending

The [read-only audit run 39049530316](https://github.com/Vertex-Systems-Network/wpessential/actions/runs/39049530316) succeeded on `e7a16d2b152cd96819649381d73c4538cd4d6e07`; artifact `12106079996` has SHA-256 `43882c08edeb09b21fb70b38b285596b8dcd151f284c5179c787513f115cae62`. Naming findings **9,551 → 9,523**, `VariableNotSnakeCase` **6,437 → 6,409**, framework errors **54,627 → 54,599**, but warnings **5,103 → 5,107**. Four new `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` findings are at `QueryAstValidator.php` lines 482, 501, 541 and 643. A four-line assignment-spacing correction is the next `RB-0141` candidate; its outcome requires a fresh audit. Full-tree WPCS remains red.

## Exact source naming improvement — 26 more Query findings removed

The [read-only audit run 39049237625](https://github.com/Vertex-Systems-Network/wpessential/actions/runs/39049237625) succeeded on `d0c2c0ad49c214d4f6daf78bddfa5ac1e4640744`; artifact `12107490225` has SHA-256 `2f0acbd76cd938b6ebc46c9a8902dca0c68254c371e160cb40c8a958a5a4641a`. Naming findings **9,577 → 9,551**, `VariableNotSnakeCase` **6,463 → 6,437**, framework PHPCS errors **54,653 → 54,627**, warnings stable **5,103**. The 26 reductions were local variables in `QueryAstValidator::validate()`, `parseProjection()` and `parsePagination()`. No full-tree WPCS success is implied.

Next bounded candidate changes only `$fieldRef` and `$searchScope` method-local names across private comparison, existence, range, set, text, taxonomy, datetime and field predicate parsers (28 prior findings). Query AST keys, method signatures, descriptor properties and issue codes remain unchanged. `RB-0140` tracks fresh audit and required CI; no reduction is claimed before that artifact.

## Exact source naming improvement — 19 more Query findings removed

The [read-only audit run 39039355431](https://github.com/Vertex-Systems-Network/wpessential/actions/runs/39039355431) succeeded on `81cfe04be23edf3337cb7cafb3ed911fd6169d23`; artifact `12103750714` has SHA-256 `9398fae1516da4dd5c198e4a7baeac0ddc34614b5fd1a47258dc492a0c259d13`. Naming findings **9,596 → 9,577**, `VariableNotSnakeCase` **6,482 → 6,463**, framework PHPCS errors **54,672 → 54,653**, and warnings remain **5,103**. All 19 reductions were local variables in private `QueryAstValidator::parseSource()`; no full-tree quality pass is implied.

The next bounded candidate changes nine local names (26 prior findings) in `QueryAstValidator::validate()`, `parseProjection()` and `parsePagination()`. Signatures, QueryDefinition constructor named labels, AST keys, budget/descriptor properties and issue codes remain intact. `RB-0139` tracks fresh audit and full CI; no result is claimed before that artifact.

## Exact source naming improvement — 19 more verified findings removed

The [read-only audit run 39039076369](https://github.com/Vertex-Systems-Network/wpessential/actions/runs/39039076369) succeeded on `a0a2620027eae33057bb3d541baa1f5182c06068`; artifact `12103665548` has SHA-256 `e6295123ce4c655dd2669ec5a9bbb5b1a74d0eb15b204978401a4c6509593db3`. Naming findings **9,615 → 9,596**, `VariableNotSnakeCase` **6,501 → 6,482**, framework PHPCS errors **54,691 → 54,672**, with warnings stable at **5,103**. The 19 reductions were four locals in the Dashboard Widget registration Ability and Cron reference methods. These diagnostic totals do not establish a green full-tree quality gate.

Next bounded candidate changes only three locals in private `QueryAstValidator::parseSource()` (19 prior findings). The AST keys, descriptor properties and validation issue codes remain unchanged. Existing `QueryAstValidatorTest` covers source validation. `RB-0138` tracks fresh audit and required CI; no reduction is claimed before the new artifact.

## Exact source naming improvement — 36 more verified findings removed

The [read-only audit run 39038797537](https://github.com/Vertex-Systems-Network/wpessential/actions/runs/39038797537) succeeded on `2c62f3f0202b7a3c86666ff5683fda50108c23ee`; artifact `12103386238` has SHA-256 `0950ce512c00c3460de991febabe79d60498fa986ccc0ecca8793d9a9082755b`. Naming findings **9,651 → 9,615**, `VariableNotSnakeCase` **6,537 → 6,501**, framework PHPCS errors **54,727 → 54,691**, and warnings **5,104 → 5,103**. All 36 naming reductions were local names in public `compile()`; the previous line-length warning was removed by the scoped condition wrap. These are framework-scope diagnostic totals; full-tree merge enforcement remains separate.

Next bounded candidate normalizes four local identifiers (19 prior findings) in private `compileFormsActionAbilityReference()` and `compileBackgroundJobReference()`. Property/callable names, payload keys and public API stay intact. `RB-0137` requires a fresh exact-head audit and full CI before a reduction is claimed.

## Exact source naming improvement — 44 more verified findings removed

The [read-only audit run 39037663759](https://github.com/Vertex-Systems-Network/wpessential/actions/runs/39037663759) succeeded on `51991d75354cfe33068ddbfe46e92a593d38d16a`; artifact `12102238683` has SHA-256 `09ffa45df4dd26ea385e8a3754c67a44a3d11477ff20b0f912e7a0690c30547a`. Naming findings **9,695 → 9,651**, `VariableNotSnakeCase` **6,581 → 6,537**, framework PHPCS errors **54,771 → 54,727**, and warnings **5,103 → 5,104**. All 44 removed naming findings were in the private `compileTarget()` method. The extra warning is `Generic.Files.LineLength.TooLong` at its renamed network-target condition, line 579. A scoped line wrap accompanies the next candidate and requires fresh verification.

The next bounded candidate changes 14 method-local names in `DashboardWidgetRegistrationCompiler::compile()` (36 prior findings); named argument labels in the descriptor constructor, properties and payload keys remain unchanged. `RB-0136` tracks the exact-head audit and full CI. No improvement or warning correction is claimed until fresh results.

## Exact source naming improvement — 211 more verified findings removed

The [read-only audit run 39037320723](https://github.com/Vertex-Systems-Network/wpessential/actions/runs/39037320723) succeeded on `aaccde6540e4ec619aeb310a69c71760f6446195`; artifact `12102517969` has SHA-256 `8f6ab7e5dd6fe4ad474c9bf688f60c843ed464a6fdbc1a79c551b0653429320e`. Naming findings **9,906 → 9,695**, `VariableNotSnakeCase` **6,792 → 6,581**, and framework PHPCS errors **54,982 → 54,771**: exactly 211 fewer. Framework warnings **5,105 → 5,103** after the two compiler alignment corrections. `DashboardWidgetsModule.php` has eight remaining naming findings tied to constructor/API and member naming; those contracts are preserved. This audit is framework-scope diagnostic evidence, not a full-tree WPCS pass.

Next bounded candidate changes ten method-local names in `DashboardWidgetRegistrationCompiler::compileTarget()` (44 prior `VariableNotSnakeCase` findings). Public methods, constructor parameters, target payload keys and descriptor values stay intact. The two PHPDoc local-variable annotations follow the renamed locals. `RB-0135` tracks fresh audit and required CI; the proposed 44 reduction is unverified.

## Exact source naming improvement — 104 more verified findings removed

The [read-only audit run 39032138538](https://github.com/Vertex-Systems-Network/wpessential/actions/runs/39032138538) succeeded on `ad53f5e75a6965839b43a2f1276b9827596623fa`; artifact `12100593982` has SHA-256 `d38822bd5d7ae6bda7dae1f0f07e0628b3c3c96e4a70c89445f615faae11b2a7`. Compared with `20af6758`, naming findings are **10,010 → 9,906** and `VariableNotSnakeCase` **6,896 → 6,792**, exactly 104 fewer. Framework-scope PHPCS errors are **55,086 → 54,982**; warnings **5,104 → 5,105**. The warning increase is an assignment-alignment effect in two compiler lines after the renames; a scoped alignment correction is included in the next candidate and requires fresh audit verification. These totals do not certify whole-repository WPCS.

Next bounded candidate: `DashboardWidgetsModule::register()` has 211 local-variable naming findings across 56 distinct identifiers in the prior artifact. Only those method-local names change; constructor/API parameters, properties, serialized keys, named labels and service identifiers remain intact. A reverse-mapping check verifies all 865 source lines, including untouched lines outside `register()`. Existing module tests cover registration contracts. `RB-0134` tracks exact-head audit and required CI; no reduction is claimed before that evidence.

## Exact source naming improvement — 96 verified findings removed

The [read-only audit run 39031820547](https://github.com/Vertex-Systems-Network/wpessential/actions/runs/39031820547) succeeded on `20af6758c7d0ae0b30fa8a5be544577a0625c293`; artifact `12100573107` has SHA-256 `1709c4056c090642714d1d8b8c1414d0f79612ecd524522ff79f01b696e29788`. The same pinned workflow and ruleset report:

| Exact source | Framework PHPCS errors | Warnings | Naming findings | `VariableNotSnakeCase` |
|---|---:|---:|---:|---:|
| `5aa3b516` before | 55,182 | 5,104 | 10,106 | 6,992 |
| `20af6758` after | 55,086 | 5,104 | 10,010 | 6,896 |

All **96** removed findings were in the three private loading/error/empty state compiler methods; other naming sniff totals were unchanged. These are `frameworks/` PHPCS totals, not a whole-repository gate result or merge authorization.

The next bounded candidate is **104** local-variable findings in four other private methods of the same file (`compile`, `compileQuery`, `filters`, `orderBy`). Four constructor parameter findings tied to the class's property/API names are excluded. The proposed source change preserves output keys, public properties, method names and named argument labels, and requires fresh audit plus required CI before any result is claimed.

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
