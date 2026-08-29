# WPEssential — Redux-class Settings / Options Parity Executable Evidence Protocol

Status: **Planning-only exact executable-evidence specification / NOT EXECUTED**  
Namespace: `RDX-001…RDX-176`  
Documented: **176/176**  
Executed: **0/176**

## Truth boundaries

- RDX is a declarative settings/options compiler over WPE Settings/Fields/Policy; it is not a PHP callback/eval framework.
- UI visibility/dependency does not authorize reading/writing settings; server-side capability/Policy and validation remain authoritative.
- Defaults, inherited values, stored values and effective values are distinct states and must remain explainable.
- Typed CSS output is generated from validated declarative values only; no arbitrary PHP/JS/shell/SQL execution path is introduced.
- Typography/font controls delegate to Font Library; assets delegate to Asset Registry; Customizer integration delegates to supported WordPress APIs.
- Import/export never carries Vault secrets unless an explicit protected secret-transfer contract exists; normal packages use references/placeholders.
- Multisite/network inheritance is explicit and cannot let site admins mutate network-enforced values.
- AI/MCP may draft schemas/options but cannot publish settings, execute code or bypass capability/approval gates.

## Exact fixtures

### Group 1 — declarative settings compiler
- `RDX-001` Compile a valid settings schema with stable page/section/field keys into deterministic normalized definition.
- `RDX-002` Reject duplicate field key in the same settings namespace.
- `RDX-003` Reject arbitrary PHP callback/eval/code strings as executable schema behavior.
- `RDX-004` Schema revision update uses expected revision and preserves prior diff.
- `RDX-005` Stale schema update fails instead of overwriting newer definition.
- `RDX-006` Draft schema compiles for preview but does not publish/register live settings routes.
- `RDX-007` Unknown future schema version fails typed or migrates explicitly.
- `RDX-008` Compiler output records owning module/site/scope so definitions cannot collide across tenants.
- `RDX-009` Capability/Policy denial blocks schema publish even if preview is allowed.
- `RDX-010` Import of Redux-like source callbacks preserves them only as unsupported provenance, never executable runtime.
- `RDX-011` AI/MCP can draft declarative schema but cannot publish it without same validation/approval.

### Group 2 — field/control catalog
- `RDX-012` Compile text/textarea/number controls with typed validation/defaults.
- `RDX-013` Compile select/radio/checkbox/multiselect choices with stable typed values and duplicate-key detection.
- `RDX-014` Compile color/date/time/range controls through corresponding typed field validators.
- `RDX-015` Compile media/file control as Asset Registry reference rather than raw arbitrary filesystem path.
- `RDX-016` Compile code-like text control only as non-executable text/CSS/JSON profile where explicitly supported.
- `RDX-017` Unknown control type remains unsupported/unresolved instead of falling back silently to raw text.
- `RDX-018` Field help/description HTML is sanitized and cannot execute scripts.
- `RDX-019` Secret/token/password setting delegates to Vault/protected setting type and is not rendered/exported as plain value.
- `RDX-020` Required/nullable/empty distinctions remain explicit per field type.
- `RDX-021` Field capability override can restrict read/write beyond page capability and is enforced server-side.
- `RDX-022` Catalog metadata does not imply runtime browser/widget compatibility until adapter evidence exists.

### Group 3 — sections/tabs/accordion
- `RDX-023` Organize fields into sections/tabs without changing storage keys/authorization semantics.
- `RDX-024` Reordering sections is presentation-only and does not reorder save/validation dependency incorrectly.
- `RDX-025` Nested accordion/tab depth is bounded and invalid cycles are rejected.
- `RDX-026` Empty section is rendered/omitted according to explicit UX rule and not used as hidden authorization boundary.
- `RDX-027` Section-level capability hides presentation and also server-denies inaccessible field endpoints where applicable.
- `RDX-028` Deep-link/tab URL cannot expose a field the viewer is not authorized to read.
- `RDX-029` Section ID collision is rejected within page namespace.
- `RDX-030` Keyboard navigation reaches tab/accordion controls and preserves focus/ARIA semantics.
- `RDX-031` Mobile/narrow layout preserves all required controls and validation messages.
- `RDX-032` Import/export preserves section hierarchy/ordering without relying on transient numeric IDs.
- `RDX-033` UI grouping changes do not mutate stored setting values.

### Group 4 — repeaters/sorters
- `RDX-034` Repeater schema creates bounded ordered rows with stable child field keys.
- `RDX-035` Add/remove/reorder row preserves values and row identity according to schema.
- `RDX-036` Maximum row limit is enforced server-side as well as UI.
- `RDX-037` Nested repeater depth is bounded to prevent pathological payloads.
- `RDX-038` Sorter control only accepts registered items and rejects arbitrary injected keys.
- `RDX-039` Duplicate sorter item handling follows explicit uniqueness rule.
- `RDX-040` Repeater row validation reports row+field errors and does not discard unrelated valid rows silently.
- `RDX-041` Protected/secret child field is redacted in preview/export.
- `RDX-042` Concurrent edit uses revision/fingerprint and detects stale whole-repeater save.
- `RDX-043` Large repeater uses bounded payload/processing limits.
- `RDX-044` AI/MCP generated repeater rows remain draft/validated data and cannot exceed schema/policy limits.

### Group 5 — style controls
- `RDX-045` Compile spacing/border/radius/dimension style control into typed normalized values/units.
- `RDX-046` Reject unsupported CSS unit or malformed numeric expression.
- `RDX-047` Responsive value control stores explicit breakpoint/profile values without inventing device truth.
- `RDX-048` Background color/image/gradient control validates Asset refs and CSS-safe values.
- `RDX-049` Box-shadow control emits bounded typed CSS and prevents declaration injection.
- `RDX-050` Link/hover/focus style states remain distinct and preserve accessibility focus visibility rules.
- `RDX-051` Global style control delegation respects Theme/Admin Theme owner and does not overwrite unrelated theme settings silently.
- `RDX-052` Style preview does not persist until save/publish succeeds.
- `RDX-053` Invalid style value retains last valid stored/effective value according to error policy.
- `RDX-054` CSS escaping prevents user value from closing declaration/block and injecting arbitrary stylesheet content.
- `RDX-055` Style control does not imply browser visual correctness without later executed rendering evidence.

### Group 6 — dependencies/required logic
- `RDX-056` Simple equality dependency shows/hides field deterministically using typed source value.
- `RDX-057` AND/OR nested dependencies preserve explicit precedence.
- `RDX-058` Dependency cycle is detected before publish.
- `RDX-059` Hidden dependent field still follows declared save policy preserve/reset/ignore explicitly.
- `RDX-060` UI dependency does not bypass server validation/authorization for submitted hidden field.
- `RDX-061` Unknown dependency source yields typed unknown/fallback behavior rather than silently true.
- `RDX-062` Protected source value cannot leak through dependency explanation to unauthorized viewer.
- `RDX-063` Dependency on environment/site/role is treated as presentation/effective-setting logic, not capability authority.
- `RDX-064` Import with missing dependency key remains unresolved and blocks strict publish.
- `RDX-065` Dependency evaluation is bounded and cannot invoke arbitrary callbacks/eval.
- `RDX-066` AI/MCP condition draft is compiled to shared declarative grammar only.

### Group 7 — validation/sanitization
- `RDX-067` Text setting sanitizes/normalizes according to field schema and preserves valid Unicode.
- `RDX-068` Number setting rejects out-of-range/non-numeric input instead of silent dangerous coercion.
- `RDX-069` URL setting validates scheme/origin profile and blocks javascript/data/file where unsupported.
- `RDX-070` HTML setting uses configured sanitizer and strips scripts/event handlers.
- `RDX-071` JSON setting parses/schema-validates and never evaluates JavaScript expressions.
- `RDX-072` CSS setting uses CSS parser/profile and rejects unsafe unsupported constructs.
- `RDX-073` Secret setting never returns raw value in validation error/log.
- `RDX-074` Cross-field validation returns bounded field/global errors and performs no partial save unless transaction policy allows it.
- `RDX-075` Server validation runs for REST/Ability/import paths identically to wp-admin.
- `RDX-076` Failed validation leaves previous stored value intact.
- `RDX-077` Validation rules cannot reference arbitrary PHP functions from imported Redux config.

### Group 8 — defaults/section reset
- `RDX-078` Distinguish schema default, network inherited value, site stored value and effective value.
- `RDX-079` “Reset field” removes/sets local override according to storage semantics and reveals resulting inherited/default value.
- `RDX-080` “Reset section” affects only fields owned by selected section and not hidden unrelated settings.
- `RDX-081` “Reset page” requires explicit destructive preview and does not delete Vault secrets owned elsewhere unless target type defines safe reset.
- `RDX-082` Required field with no valid default blocks invalid reset.
- `RDX-083` Network-enforced value cannot be reset by site admin.
- `RDX-084` Reset operation is revisioned/audited and recoverable through prior settings revision where supported.
- `RDX-085` Reset success is not reported if storage write failed/unknown.
- `RDX-086` Default change in schema does not silently overwrite an explicit stored value.
- `RDX-087` Import can choose preserve local overrides vs apply imported defaults explicitly.
- `RDX-088` AI/MCP cannot bulk reset settings without high-risk approval/Policy.

### Group 9 — import/export
- `RDX-089` Export settings schema/allowed values with schema version/scope and no plaintext secrets.
- `RDX-090` Export can include current nonsecret local values only when authorized.
- `RDX-091` Import validates schema/version before values and reports incompatible fields.
- `RDX-092` Conflict options create/map/merge/replace/skip are explicit and destructive replace shows diff.
- `RDX-093` Imported unknown field/control remains unresolved rather than silently dropped.
- `RDX-094` Secret placeholders require destination Vault mapping and never restore source secret from package.
- `RDX-095` Site/network scope is remapped explicitly and cannot convert site setting into network-enforced value accidentally.
- `RDX-096` Import is idempotent by package/item identity and retry does not duplicate definitions.
- `RDX-097` Partial import reports per-field/section result and no false all-success.
- `RDX-098` Legacy Redux import never executes framework callbacks/field render PHP from package/config.
- `RDX-099` Post-import verification compares effective values/schema/permissions before publishing migrated panel.

### Group 10 — Customizer adapter
- `RDX-100` Map supported setting/control into WordPress Customizer through registered API adapter where current WP/theme supports it.
- `RDX-101` Customizer preview value is temporary and does not persist before publish/save.
- `RDX-102` Capability check applies to Customizer setting as well as settings page.
- `RDX-103` Unsupported custom control remains Settings-only/unresolved rather than injecting arbitrary Customizer PHP.
- `RDX-104` `theme_mod` vs option storage mapping is explicit and not switched silently.
- `RDX-105` Selective refresh/preview adapter is enabled only where supported and cannot execute imported arbitrary callbacks.
- `RDX-106` Customizer removed/deprecated profile falls back to settings owner and reports compatibility state.
- `RDX-107` Network settings are not exposed through site Customizer when scope is incompatible.
- `RDX-108` Customizer JS assets delegate to safe registered asset/build pipeline and no raw arbitrary script from field config.
- `RDX-109` Preview cache does not leak another user’s unsaved settings.
- `RDX-110` Static adapter planning does not certify runtime Customizer compatibility.

### Group 11 — typography/font delegation
- `RDX-111` Typography control references canonical Font Library family/face rather than private Redux font store.
- `RDX-112` System font preset remains local declarative value without remote provider call.
- `RDX-113` Custom uploaded font requires authorized Font Registry asset/provenance.
- `RDX-114` Google/Adobe/provider font selection delegates to certified provider adapter and Vault where credentials apply.
- `RDX-115` Weight/style/line-height/letter-spacing values use typed validation.
- `RDX-116` Missing/archived font reference yields fallback/degraded state and not broken CSS silently.
- `RDX-117` Font license/local hosting status is inherited as provenance; RDX cannot mark it legally safe.
- `RDX-118` Typography preview cannot fetch unauthorized remote font before consent/privacy policy.
- `RDX-119` Import maps font refs by portable identity/checksum/provenance and not attachment ID alone.
- `RDX-120` Typography CSS output uses Font owner’s final family/face descriptors and cache fingerprint.
- `RDX-121` AI/MCP cannot download/publish an unlicensed font through typography setting request.

### Group 12 — typed CSS output compiler
- `RDX-122` Generate CSS from allowlisted typed settings/selectors/properties through deterministic compiler.
- `RDX-123` Selector templates are registered/bounded and user values cannot inject arbitrary selector/code unless explicit safe selector field exists.
- `RDX-124` CSS property names come from schema and cannot be replaced by arbitrary user string.
- `RDX-125` Values are escaped/validated by property type and prevent declaration/block breakout.
- `RDX-126` Empty/default values follow explicit omit/output semantics.
- `RDX-127` Responsive/media output uses registered breakpoint/profile and valid nested CSS.
- `RDX-128` Generated CSS fingerprint changes only when relevant effective settings/compiler version changes.
- `RDX-129` CSS cache invalidation targets affected artifact/site and avoids cross-site bleed.
- `RDX-130` Compiler never evaluates PHP/JS expressions or arbitrary callbacks.
- `RDX-131` CSP/security policy is not silently weakened to accommodate generated output.
- `RDX-132` Generated CSS correctness remains planning/static until browser/runtime evidence later executes.

### Group 13 — extension SDK
- `RDX-133` Extension registers a custom declarative control through versioned SDK interface rather than arbitrary global callback execution.
- `RDX-134` Extension declares schema, validation, render adapter and capabilities explicitly.
- `RDX-135` Unsupported SDK version is rejected/degraded with migration guidance.
- `RDX-136` Extension cannot bypass core Policy/validation/storage by writing raw options directly through RDX contract.
- `RDX-137` Server-side custom implementation belongs reviewed plugin/SDK source under VCS, not database-evaluated PHP.
- `RDX-138` Extension assets are registered/scoped through Asset Registry/build pipeline.
- `RDX-139` Extension field import/export defines portable serialization explicitly.
- `RDX-140` Extension removal leaves unknown stored values preserved/archived according to lifecycle and not interpreted by another field type silently.
- `RDX-141` SDK event/Ability names are namespaced/versioned to avoid collision.
- `RDX-142` Third-party extension is not marked certified until compatibility/security/runtime evidence exists.
- `RDX-143` AI/MCP may generate an Extension Plan/source proposal but cannot install/execute it without development consent.

### Group 14 — Multisite/scope inheritance
- `RDX-144` Site setting stores/reads only site scope by default.
- `RDX-145` Network default can provide inherited effective value to sites without creating local copies unnecessarily.
- `RDX-146` Network-enforced value blocks site override and explain output names network source.
- `RDX-147` Site override of allowed inherited value remains site-owned and does not change network default.
- `RDX-148` Same setting key on different sites remains isolated.
- `RDX-149` Network admin can target selected sites only through explicit bulk/template operation.
- `RDX-150` Site admin cannot write network options by sending scope parameter.
- `RDX-151` Site clone copies local settings only according to clone policy and quarantines environment/provider refs.
- `RDX-152` Site deletion removes site-owned settings while preserving network schema/defaults.
- `RDX-153` Shared cache key includes site/network revision/inheritance state.
- `RDX-154` AI/MCP cross-site/network settings change requires corresponding network/site authority.

### Group 15 — rendering/performance
- `RDX-155` Large settings page with 1K fields uses bounded server/client rendering and does not load unrelated sections eagerly where lazy profile exists.
- `RDX-156` Settings read avoids N+1 option/meta queries through batched owner APIs.
- `RDX-157` Autoload policy prevents large/high-cardinality settings blobs from being autoloaded blindly.
- `RDX-158` Repeater/large JSON payload has max size/row/depth limits.
- `RDX-159` Dependency evaluation scales with indexed dependency graph and avoids quadratic loops beyond declared budget.
- `RDX-160` Generated CSS cache prevents recompiling unchanged effective settings on every request.
- `RDX-161` Concurrent saves use revision/expected state and detect lost update.
- `RDX-162` Network-wide template apply uses bounded per-site jobs/backpressure.
- `RDX-163` Error/degraded rendering preserves access to recovery/reset controls.
- `RDX-164` Performance evidence records WP/PHP/browser/schema size/environment.
- `RDX-165` Static estimates do not count as runtime performance certification.

### Group 16 — end-to-end options-panel regression
- `RDX-166` Golden: compile a multi-section settings panel with typed fields/dependencies and save valid values through server validation.
- `RDX-167` Golden: invalid/unauthorized submission changes nothing and returns field/policy errors without secret leak.
- `RDX-168` Golden: defaults/network inheritance/site override/effective value remain distinguishable and explainable.
- `RDX-169` Golden: repeater/sorter edits preserve row identity/order and stale concurrent save conflicts safely.
- `RDX-170` Golden: typography/style settings delegate to Font/Asset owners and generate safe deterministic CSS.
- `RDX-171` Golden: Customizer preview is temporary and publish path respects same validation/Policy.
- `RDX-172` Golden: Redux-like import maps supported controls, flags unsupported callbacks and imports no executable PHP.
- `RDX-173` Golden: Multisite network-enforced setting cannot be overridden by site admin and cache isolation holds.
- `RDX-174` Golden: extension SDK custom control works declaratively while direct arbitrary code/eval path remains absent.
- `RDX-175` Golden: large panel remains recoverable/accessibly usable with bounded performance and no autoload explosion.
- `RDX-176` Golden: AI/MCP adversarial request to inject PHP/raw CSS escape, reveal Vault secret or network-enforce without authority is denied/draft-only.

## Runtime truth

This protocol is documentation-only. `RDX-001…RDX-176` are **176/176 documented, 0/176 executed**. No settings/schema publication, Customizer/runtime render, CSS generation, option mutation, SDK installation, provider/font call or AI/MCP execution occurred. Development authorization remains **NOT GRANTED / 0/56**.