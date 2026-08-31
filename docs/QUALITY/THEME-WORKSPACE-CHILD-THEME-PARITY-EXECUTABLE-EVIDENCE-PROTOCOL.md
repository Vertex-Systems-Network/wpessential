# WPEssential — Theme Workspace & Child Theme Parity Executable Evidence Protocol

Status: **Planning-only exact executable-evidence specification / NOT EXECUTED**  
Namespace: `THM-001…THM-176`  
Documented: **176/176**  
Executed: **0/176**

## Truth boundaries

- Theme Workspace is declarative/theme-source tooling; it must not become an arbitrary live PHP editor/eval console.
- Child theme creation never silently mutates the parent theme; parent remains external upstream dependency.
- Static analysis reports evidence/findings; it does not prove runtime compatibility or security by itself.
- Preview/compare is not activation; activation remains WordPress theme lifecycle truth and must be recoverable.
- `theme.json`, CSS, templates, assets and fonts use their owning registries/adapters and do not bypass Policy or licensing.
- Package import/export cannot smuggle executable arbitrary server code into an auto-activated path.
- Multisite theme enablement/network availability is Network Admin authority, distinct from site activation.
- AI/MCP may analyze/draft/scaffold theme assets only through same capability/approval boundaries; no hidden filesystem/server-code mutation.

## Exact fixtures

### Group 1 — theme identity/parent dependency
- `THM-001` Register a Theme Workspace definition with stable key, theme slug, type parent/child, source provenance, status and revision.
- `THM-002` Child theme definition requires explicit parent slug/version/dependency evidence and cannot point to itself.
- `THM-003` Missing parent theme places child in unresolved/degraded state rather than falsely activation-ready.
- `THM-004` Parent/child slug collision in the same installation is rejected.
- `THM-005` Theme definition update uses expected revision and preserves prior diff.
- `THM-006` Source directory identity is bounded to approved theme roots and rejects traversal/absolute foreign paths.
- `THM-007` Imported theme identity retains original package/source provenance and is not relabeled as WPE-authored.
- `THM-008` Parent version compatibility range is stored separately from detected installed version.
- `THM-009` Site/user-visible theme label is presentation only; canonical slug/source identity remains stable.
- `THM-010` Capability/Policy denial blocks create/edit/package/activate planning action even if UI is visible.
- `THM-011` AI/MCP may draft workspace metadata but cannot create filesystem theme files or activate without authorized runtime scope.

### Group 2 — static analyzer
- `THM-012` Analyzer inventories theme headers, templates, CSS, theme.json and registered asset references without executing PHP.
- `THM-013` PHP files are treated as source text for bounded static inspection only; no include/eval/lint process execution pre-consent.
- `THM-014` Analyzer flags parent-file overrides and child custom files with provenance/path.
- `THM-015` Unsafe relative path or symlink escaping theme root is reported and excluded from analysis package.
- `THM-016` Unknown syntax/parser construct produces typed unknown instead of falsely “safe”.
- `THM-017` Analyzer reports deprecated WordPress/theme APIs only when static evidence identifies them; runtime-only behavior remains unverified.
- `THM-018` Potential secret/API key patterns are redacted in reports and never copied to AI prompts automatically.
- `THM-019` Analyzer distinguishes executable PHP, declarative JSON, CSS, templates and binary assets so controls can differ.
- `THM-020` Large file/deep AST analysis obeys size/depth/time budgets and degrades safely.
- `THM-021` Analyzer version/ruleset is recorded so future finding changes are explainable.
- `THM-022` Static analyzer result never upgrades runtime/compatibility/security certification by itself.

### Group 3 — child creation metadata
- `THM-023` Create child-theme scaffold plan with Theme Name, Template parent slug, Version, Text Domain and optional metadata validated.
- `THM-024` Child scaffold plan does not write parent files or rename parent theme.
- `THM-025` Invalid/missing `Template` parent slug blocks activation-ready status.
- `THM-026` Child slug is sanitized/unique and cannot traverse theme directories.
- `THM-027` Optional stylesheet/header generation uses deterministic declarative template and escapes user metadata safely.
- `THM-028` Optional functions/bootstrap file is generated only from reviewed static scaffold profile, never arbitrary UI-entered PHP.
- `THM-029` Existing destination directory conflict requires diff/create-new/abort and is never overwritten silently.
- `THM-030` Scaffold creation plan lists files to create and hashes/template versions before runtime execution.
- `THM-031` License/author metadata is descriptive and does not claim legal ownership of parent assets.
- `THM-032` Child creation can be exported as package plan without creating local filesystem files.
- `THM-033` AI/MCP generated child metadata/code remains draft source artifact subject to normal review/consent.

### Group 4 — stylesheet enqueue profiles
- `THM-034` Define child stylesheet loading profile using supported WordPress enqueue strategy rather than hard-coded HTML injection.
- `THM-035` Parent stylesheet load can be Core/parent-owned/child-enqueued according to detected theme behavior and profile.
- `THM-036` Duplicate parent stylesheet enqueue is detected to prevent double CSS requests.
- `THM-037` Child stylesheet depends on parent handle only when that handle is actually registered/certified.
- `THM-038` RTL stylesheet behavior follows WordPress/theme support and does not invent nonexistent parent file.
- `THM-039` Cache/version argument uses file/theme revision fingerprint rather than unstable current timestamp in production profile.
- `THM-040` External stylesheet origins are validated and subject to Asset/CSP/privacy rules.
- `THM-041` Enqueue ordering conflict with plugin/theme is reported rather than solved via global `!important` injection automatically.
- `THM-042` Removing child stylesheet profile does not dequeue unrelated plugin/theme styles.
- `THM-043` Preview can model expected enqueue graph but cannot claim runtime request order without executed evidence.
- `THM-044` Arbitrary hook names/PHP callbacks are not accepted as an escape hatch for stylesheet enqueue.

### Group 5 — CSS parsing/selector explorer
- `THM-045` Parse valid CSS into bounded stylesheet/selector/declaration representation without executing browser code.
- `THM-046` Invalid CSS reports line/rule diagnostic and preserves original source bytes for review.
- `THM-047` Selector explorer lists selectors/source locations and does not imply they match runtime DOM until verified.
- `THM-048` At-rules/media/container/supports blocks retain nesting/context in analysis.
- `THM-049` URLs inside CSS are identified and validated for traversal/privacy/external-origin policy.
- `THM-050` CSS custom properties are indexed without evaluating arbitrary `var()` runtime cascade as static certainty.
- `THM-051` Large selector count/deep nesting is bounded and performance warning is evidence-based.
- `THM-052` CSS edits/scaffold suggestions produce source diff only; no live parent/child file mutation pre-consent.
- `THM-053` Selector rename suggestion reports potential references but does not automatically rewrite templates/JS blindly.
- `THM-054` Malicious CSS constructs/legacy unsafe expressions are flagged/rejected by declared compatibility/security profile.
- `THM-055` Analyzer distinguishes source selector existence from actual rendered usage and does not claim dead CSS without runtime/coverage evidence.

### Group 6 — theme.json/Global Styles
- `THM-056` Parse supported theme.json schema/version and expose settings/styles/custom templates safely.
- `THM-057` Unknown future schema keys are preserved/reported and not silently dropped on round-trip.
- `THM-058` Child theme.json merge semantics are modeled against parent/Core rules for detected WordPress profile.
- `THM-059` Color/typography/spacing token edits use valid schema values and reject malformed structures.
- `THM-060` Font family references delegate to Font/Asset Registry and do not embed unauthorized private font binaries.
- `THM-061` Block-specific style settings remain scoped and do not become global CSS unintentionally.
- `THM-062` Custom CSS/theme.json fields cannot carry PHP/eval/server code.
- `THM-063` Preview identifies parent vs child vs user Global Styles source of effective value where model supports it.
- `THM-064` Import/export retains schema/version and unresolved unsupported keys.
- `THM-065` Site Editor/user Global Styles data is separate from theme source files and is not overwritten by source edit plan silently.
- `THM-066` Static theme.json comparison is not proof of final frontend rendering without runtime evidence.

### Group 7 — templates/template parts
- `THM-067` Inventory block-theme templates/template parts from parent and child with source precedence metadata.
- `THM-068` Child override plan creates a new child file/reference and never edits parent template directly.
- `THM-069` Missing parent template referenced by child is reported unresolved.
- `THM-070` Template markup is parsed/sanitized as block markup/text; embedded PHP in block template context is not executed.
- `THM-071` Classic PHP templates remain static-source artifacts and are not edited via unrestricted live code console.
- `THM-072` Template-part slug collision follows WordPress precedence and is explainable in compare view.
- `THM-073` Renaming/removing a template part reports dependent references before source-plan mutation.
- `THM-074` User-customized database template overrides are shown separately from theme file source.
- `THM-075` Export package includes only selected child-owned template files and allowed assets.
- `THM-076` Translation/localization references in templates are preserved without executing PHP translation callbacks.
- `THM-077` Preview of template override is labeled modeled/static unless executed through later authorized WordPress runtime.

### Group 8 — assets/fonts integration
- `THM-078` Theme asset inventory records CSS/JS/images/fonts with paths/checksums/ownership and blocks traversal.
- `THM-079` Child asset plan can reference parent asset without copying binary when reuse is valid.
- `THM-080` Copied/replaced asset gets child-owned identity and source provenance.
- `THM-081` Font assets delegate to Font Library licensing/provenance policy; theme workspace does not infer redistribution rights.
- `THM-082` External asset URL obeys HTTPS/origin/CSP/privacy policy and cannot be arbitrary file/data/javascript scheme.
- `THM-083` Asset filename collision requires explicit overwrite/new-name/map strategy.
- `THM-084` Missing asset reference is unresolved and not replaced with arbitrary similarly named file.
- `THM-085` Image/SVG validation/sanitization uses Asset/Media owner before package publication.
- `THM-086` Generated cache/fingerprint changes when asset bytes change and old revision remains traceable.
- `THM-087` Asset removal checks theme-source references before delete plan.
- `THM-088` AI/MCP asset suggestions cannot download/license/use remote assets automatically without approved provider/privacy/licensing flow.

### Group 9 — parent update/drift
- `THM-089` Record parent theme version/fingerprint baseline used when child override was authored.
- `THM-090` Parent update changes fingerprint and marks affected child overrides for drift review without editing them.
- `THM-091` Parent removed selector/template produces explicit obsolete/orphan override finding.
- `THM-092` Parent changed template can be diffed against child override/base snapshot when available.
- `THM-093` Child override remains active source artifact until operator resolves drift; parent update does not overwrite it.
- `THM-094` Drift analyzer distinguishes identical, upstream-changed, child-changed, both-changed and unknown base states.
- `THM-095` Missing historical parent version limits three-way diff and is labeled unavailable rather than guessed.
- `THM-096` Parent security update is not blocked automatically by child drift; risk is surfaced separately.
- `THM-097` Parent update compatibility finding is static/planning evidence until runtime test executes later.
- `THM-098` Network-shared parent update reports affected child themes/sites without cross-site data leak.
- `THM-099` AI/MCP can propose rebase/merge diff but cannot overwrite child/parent source without explicit code-development authorization.

### Group 10 — preview/compare
- `THM-100` Compare parent vs child file inventory with added/overridden/missing states.
- `THM-101` Compare CSS/theme.json/template source revisions and retain exact source fingerprints.
- `THM-102` Preview does not activate the child theme or change current theme setting.
- `THM-103` Static preview explicitly labels unsupported runtime hooks/PHP/plugin interactions as unknown.
- `THM-104` Visual screenshot claim is not made unless later authorized runtime/browser evidence exists.
- `THM-105` Compare view redacts secrets accidentally found in source and restricts sensitive file access by Policy.
- `THM-106` Large diff is paginated/bounded and binary files show metadata/hash rather than unreadable raw dump.
- `THM-107` User can compare two child revisions without changing active revision.
- `THM-108` Preview URL/token, if later implemented, must be scoped/expiring and cannot grant broader admin/theme activation rights.
- `THM-109` Network/site compare respects theme availability/ownership scope.
- `THM-110` AI/MCP explanation references source diff evidence and avoids claiming runtime equivalence.

### Group 11 — package export/import security
- `THM-111` Export child-theme package manifest with selected files, hashes, metadata, parent requirement and no secrets.
- `THM-112` ZIP/package paths reject traversal, absolute paths, symlinks escaping root and dangerous special files.
- `THM-113` Import validates archive size/file-count/decompression limits before extraction.
- `THM-114` Existing destination theme directory collision requires explicit create-new/replace-with-diff/abort.
- `THM-115` Imported PHP source is treated as reviewed source code requiring normal development/release gate; import does not auto-execute/activate it.
- `THM-116` Package cannot include `.env`, credentials, Vault material or arbitrary filesystem locations.
- `THM-117` Unsupported/binary executable files are rejected/quarantined by policy.
- `THM-118` Parent dependency is checked before imported child can be activation-ready.
- `THM-119` Package signature/checksum validates artifact integrity only and does not imply source is secure/trusted legally.
- `THM-120` Import remains Draft/inactive until validation/approval.
- `THM-121` AI/MCP cannot import/activate arbitrary remote theme package under planning-only mode.

### Group 12 — permissions/source-code boundary
- `THM-122` Read theme inventory/source requires dedicated capability/Policy distinct from theme activation.
- `THM-123` Source modification/package creation requires higher privilege than read/preview.
- `THM-124` Parent theme files are read-only to Theme Workspace standard flow; attempted parent write is rejected.
- `THM-125` Arbitrary PHP text editor/eval endpoint does not exist in Theme Workspace.
- `THM-126` User-entered shell/SQL/command fields are not accepted as theme operations.
- `THM-127` Extension/custom PHP need routes to reviewed Extension SDK/VCS workflow, not database-stored executable snippet.
- `THM-128` Files outside allowed theme root cannot be read merely by path parameter.
- `THM-129` REST/Ability source operations enforce same path/capability checks as admin UI.
- `THM-130` Download/export of source can be restricted separately from on-screen read.
- `THM-131` Audit records actor/action/file path within theme scope without dumping protected source/secrets unnecessarily.
- `THM-132` AI/MCP cannot elevate from analysis to filesystem/source mutation without explicit authorized implementation scope.

### Group 13 — activation/recovery
- `THM-133` Activation plan verifies child metadata, parent availability and compatibility blockers before calling WordPress theme activation API later.
- `THM-134` Activation is distinct from package creation/import and remains inactive until explicit authorized action.
- `THM-135` Site-health/recovery mode path remains available if activated theme causes fatal error.
- `THM-136` Previous active theme identity is recorded as rollback/recovery candidate before activation.
- `THM-137` Activation failure does not claim child is active and preserves previous theme state where WordPress does.
- `THM-138` Theme switch cannot be executed by operator lacking `switch_themes`/Policy even if they can edit workspace metadata.
- `THM-139` Network-enabled vs site-active states are distinct in Multisite.
- `THM-140` Parent missing after later deletion degrades child and recovery path is surfaced.
- `THM-141` Rollback restores prior active theme selection only; it does not roll back unrelated content/plugin/database state.
- `THM-142` Recovery action is audited and cannot silently modify parent source.
- `THM-143` Preview/analysis success never substitutes for actual post-activation runtime verification.

### Group 14 — Multisite/network enable
- `THM-144` Theme package/install availability across network requires Network Admin authority.
- `THM-145` Site admin may activate only network-enabled/available themes according to WordPress rules.
- `THM-146` Site-specific child workspace metadata remains isolated while theme files may be installation-shared.
- `THM-147` Same child theme source used by multiple sites does not share site-specific Global Styles/user customizations automatically.
- `THM-148` Network disable prevents new site activation and follows WordPress behavior for already active sites explicitly.
- `THM-149` Network update/drift report lists affected sites without exposing site-private content/settings.
- `THM-150` Site clone does not duplicate shared theme files unnecessarily and remaps environment-specific config separately.
- `THM-151` Site deletion removes site-owned workspace/preview metadata but not shared theme files used by other sites.
- `THM-152` Network admin source read/export does not automatically grant all site content access.
- `THM-153` Network-wide child source change is classified broad/high-risk and requires explicit development/release authority.
- `THM-154` AI/MCP site context cannot network-enable/modify shared theme source by supplying network flags.

### Group 15 — large stylesheet/theme performance
- `THM-155` Analyze theme with 10K CSS selectors under bounded parser memory/time and report truncation/degraded state if budget exceeded.
- `THM-156` Large theme file inventory uses streaming/batched hashing and avoids loading all binaries into memory.
- `THM-157` Parent/child diff caches are keyed by file fingerprints/revisions and invalidated selectively.
- `THM-158` Selector/reference index creation remains bounded for deeply nested/large stylesheets.
- `THM-159` Package creation streams large assets and enforces output size/file-count limits.
- `THM-160` Import decompression protects against ZIP bombs and nested pathological archives.
- `THM-161` Multisite report over many sites uses batched site/theme metadata and avoids per-site full source reparse where shared fingerprint is identical.
- `THM-162` Concurrent workspace analyses do not write shared source and respect Job Service fairness/backpressure.
- `THM-163` Performance evidence records theme size/parser/version/environment.
- `THM-164` Runtime frontend performance is not inferred solely from source file count/size.
- `THM-165` Static estimates do not certify runtime performance; later executed benchmarks are required.

### Group 16 — end-to-end child-theme lifecycle regression
- `THM-166` Golden: analyze parent, scaffold child metadata, add child CSS override plan and verify parent source fingerprint unchanged.
- `THM-167` Golden: child theme.json/template override shows explicit parent precedence and no mutation of user Global Styles.
- `THM-168` Golden: parent update produces drift report and preserves child files unchanged for manual resolution.
- `THM-169` Golden: package export/import validates traversal/security, parent dependency and remains inactive after import.
- `THM-170` Golden: source containing PHP is inspectable as static code but no arbitrary live editor/eval path is available.
- `THM-171` Golden: activation plan records prior theme/recovery route and refuses when parent dependency missing.
- `THM-172` Golden: Multisite network-enabled theme can be activated by allowed site while site cannot edit network/shared source.
- `THM-173` Golden: large CSS/theme analysis remains bounded and labels static/runtime unknowns correctly.
- `THM-174` Golden: restricted font/asset is excluded or flagged by package policy instead of silently redistributed.
- `THM-175` Golden: competitor child-theme import/coexistence retains source provenance and does not auto-delete/overwrite existing theme directory.
- `THM-176` Golden: AI/MCP adversarial request to edit parent PHP, inject eval, activate network-wide or bypass recovery is denied/draft-only.

## Runtime truth

This protocol is documentation-only. `THM-001…THM-176` are **176/176 documented, 0/176 executed**. No theme files were read through runtime filesystem execution, created, edited, packaged, imported, activated, network-enabled, benchmarked or processed by AI/MCP runtime. Development authorization remains **NOT GRANTED / 0/56**.