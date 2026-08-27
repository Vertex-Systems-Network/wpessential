# WPEssential — Builder Widgets Adapter Certification Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Related: ADR-0035, `docs/MODULES/BUILDER-INTEGRATION-CERTIFICATION.md`, Component Blueprint, Asset Registry, Query, Policy, ADR-0014.

## 1. Purpose

Define a single evidence contract for certifying WPEssential Component Blueprint adapters across Gutenberg/WordPress Blocks, Elementor, Bricks, WPBakery Page Builder and Visual Composer Website Builder.

The portability rule is fixed:

**WPE Component Blueprint is canonical. Builder documents, private serialized structures and editor manifests are adapter-owned representations, never WPE’s canonical component format.**

## 2. Certification profile

Every future adapter certification records:
- builder product identity;
- exact builder/plugin/theme version;
- Free/Pro edition where relevant;
- WordPress/PHP versions;
- WPE Platform/API/Blueprint versions;
- adapter version;
- editor feature/experiment flags that affect behavior;
- supported capability class;
- server-render/client-render mode;
- asset/runtime dependencies;
- tested browser/editor profile;
- known degraded/unsupported features.

Unknown newer versions are not silently promoted to certified support.

## 3. Certification levels

- **BC0 — Detected:** product/version recognized only.
- **BC1 — Registration:** WPE component appears/registers and basic save/load works.
- **BC2 — Render Certified:** editor + frontend output, dynamic WPE data, assets and responsive behavior pass required fixtures.
- **BC3 — Advanced:** builder-native dynamic data/tag bridge, nested/container/repeater or other named advanced capabilities pass dedicated fixtures.
- **BC4 — Upgrade/Regression Certified:** supported version transition plus stored-document backward compatibility and safe failure behavior pass.

Marketing support must name the certified level/capability; BC0 is not support.

## 4. Shared reference blueprints

All adapters use the same semantic fixture set:
1. Text Card;
2. Entity Card;
3. Query List;
4. Conditional CTA;
5. Responsive Component;
6. Asset Component;
7. Empty/Error Component;
8. Repeater Component;
9. Nested/Container Component;
10. Dynamic value/tag reference where supported.

## 5. Shared fixtures

### BW-01 — Product identity detection
WPBakery and Visual Composer are distinguished correctly; false-positive builder detection is a failure.

### BW-02 — Version/edition profile
Exact version/edition is recorded and the adapter declares certified/uncertified/incompatible state truthfully.

### BW-03 — Registration lifecycle
Enable/disable/reload registers/unregisters only WPE adapter surfaces without deleting builder-owned content.

### BW-04 — Canonical Blueprint independence
Builder document can reference WPE component, but WPE export/import of the Component Blueprint does not depend on copying proprietary builder page serialization.

### BW-05 — Dynamic data authorization
Entity/Query values are resolved through WPE Data Source/Query/Policy contracts; forged builder attributes cannot bypass resource Policy.

### BW-06 — Asset isolation
Builder/editor adapter assets load only in required editor/frontend contexts; no global asset pollution when builder/adapter/component is inactive.

### BW-07 — No arbitrary runtime code generation
User configuration cannot become arbitrary PHP/JS eval or uncontrolled executable source generation.

### BW-08 — Missing builder safe degradation
Removing/deactivating builder cannot fatal WPE globally or delete Component Blueprint definitions.

### BW-09 — Stored content backward compatibility
Adapter upgrade/downgrade fixture verifies prior stored WPE component references either render compatibly or enter explicit degraded state without data loss.

### BW-10 — Accessibility/common semantic output
Reference components preserve required labels, headings, links, focus semantics and no color-only state where WPE owns frontend markup.

## 6. Gutenberg / WordPress Blocks fixtures

### BW-11 — Server registration metadata
Approved component registration uses certified WordPress block metadata/registration semantics for the tested WordPress version.

### BW-12 — Editor insertion/save/reload
Reference block inserts, persists configuration and reloads without attribute/schema loss.

### BW-13 — Dynamic server render
WPE dynamic data renders through authorized server path; protected values are not persisted into public block attributes unnecessarily.

### BW-14 — Block Supports mapping
Only explicitly supported typography/spacing/color/layout capabilities map to Block Supports; unsupported controls are not falsely exposed.

### BW-15 — InnerBlocks/container class
Nested support is enabled only for a blueprint certified as a container; invalid child relationships are rejected/degraded safely.

### BW-16 — Editor/frontend asset separation
Editor-only assets do not leak to public output and frontend does not require editor packages.

### BW-17 — Theme/block-style compatibility
Reference fixtures survive certified classic/block theme contexts without overriding unrelated theme styles globally.

### BW-18 — WordPress major-version regression
Registration/render/save fixtures pass for each version in the advertised WordPress support range.

## 7. Elementor fixtures

### BW-19 — Widget registration
WPE widget appears in intended category and basic controls save/reload under certified Elementor version.

### BW-20 — Editor/frontend parity
Editor preview and frontend render represent the same WPE semantic component within documented builder-preview limitations.

### BW-21 — Content/style control mapping
Common control schema maps to supported Elementor controls without making Elementor private document data canonical.

### BW-22 — WPE dynamic data without native Dynamic Tags
WPE widget resolves its own authorized dynamic bindings even when native Elementor Dynamic Tags integration is unavailable.

### BW-23 — Native Dynamic Tags edition gate
Native Dynamic Tags bridge is exposed only when the certified Elementor edition/version actually provides the required capability; no Free-site false claim.

### BW-24 — Responsive controls/assets
Certified responsive controls and widget dependencies render correctly without duplicate/global asset registration.

### BW-25 — Elementor template reference
Referenced builder template uses supported render APIs; missing template/plugin degrades safely without arbitrary unserialize logic.

### BW-26 — Elementor feature/version drift
Changed experimental/feature flags or unsupported newer versions surface uncertified/degraded diagnostics instead of silently claiming support.

## 8. Bricks fixtures

### BW-27 — Bricks element registration
WPE element appears and persists supported controls using documented certified extension APIs.

### BW-28 — Editor/frontend parity
Reference component renders coherently in builder and frontend with WPE Policy/Data Source semantics intact.

### BW-29 — Control/style mapping
Only supported Bricks control/style properties are mapped; unsupported Blueprint properties are surfaced as degraded/unsupported.

### BW-30 — Dynamic data bridge
If Bricks native dynamic-data bridge is claimed, typed WPE values pass dedicated read/authorization fixtures.

### BW-31 — Nested element support
Nested/component-child behavior is disabled until the specific adapter class passes nesting fixtures; no generic assumption.

### BW-32 — Theme/builder context isolation
Bricks-specific adapter behavior does not alter WPE component output on non-Bricks contexts.

### BW-33 — Builder absent/changed API
Deactivation or removed documented API disables affected adapter safely without fataling site/admin.

### BW-34 — Bricks version regression
Stored reference components and editor/frontend render pass across the explicit certified range.

## 9. WPBakery Page Builder fixtures

### BW-35 — Shortcode contract
Canonical WPE shortcode renderer remains stable and validated independently from WPBakery editor mapping.

### BW-36 — `vc_map` editor mapping
Certified parameters appear, save and reload through WPBakery’s supported element mapping path.

### BW-37 — Shortcode attribute validation
Forged/raw shortcode attributes cannot become PHP/template expressions or bypass typed validation/Policy.

### BW-38 — Frontend render without editor
Where semantics permit, WPE shortcode still renders through WPE Renderer when WPBakery editor UI is unavailable.

### BW-39 — Nested/container mapping
Container semantics are enabled only for certified Blueprint classes; invalid nesting is rejected predictably.

### BW-40 — Parameter dependency mapping
Builder UI dependencies do not become security conditions; server renderer independently validates final values.

### BW-41 — Existing shortcode content regression
Upgrade preserves stored WPE shortcode contract or provides explicit migration/degraded diagnostics.

### BW-42 — WPBakery version regression
BC-level remains scoped to explicitly tested versions; unsupported new version becomes uncertified rather than assumed compatible.

## 10. Visual Composer Website Builder fixtures

### BW-43 — Separate product detection
Visual Composer Website Builder is never treated as WPBakery based on historical naming.

### BW-44 — Element manifest/settings mapping
Approved Blueprint controls map through documented VC element APIs in the certified adapter profile.

### BW-45 — Prebuilt adapter runtime
Customer production site requires no per-user Node/npm/Webpack build to render ordinary published WPE components under the accepted profile.

### BW-46 — Editor component glue
Editor-side React/glue remains trusted shipped adapter code; user definitions are interpreted data, not arbitrary generated JS.

### BW-47 — Public render contract
Frontend rendering uses certified VC/WPE render path and does not require editor runtime assets.

### BW-48 — Dynamic content bridge
Native VC dynamic-content support is claimed only after typed authorization-aware fixtures pass.

### BW-49 — Inner/nested element support
Nested behavior remains separate advanced certification; unsupported nesting fails/degrades without corrupting page data.

### BW-50 — VC version/toolchain regression
Builder major/toolchain/API change forces research/certification refresh before support range expansion.

## 11. Cross-builder parity evidence

For each BC2+ adapter compare the same reference Blueprint for:
- semantic text/content;
- dynamic value correctness;
- authorization outcome;
- empty/error behavior;
- responsive intent;
- declared asset dependencies;
- accessibility-critical semantics.

Pixel-identical builder output is not required because builder rendering systems differ. Semantic and security parity is required.

## 12. Pass gates

An adapter cannot reach BC2+ if:
- builder private serialized data becomes canonical WPE format;
- protected dynamic data bypasses WPE Policy;
- arbitrary runtime code generation/eval is introduced;
- frontend requires editor-only runtime unexpectedly;
- missing builder fatals WPE globally;
- adapter assets load globally while inactive;
- unsupported newer version is marketed certified without evidence;
- stored WPE component references are silently destroyed on adapter disable/upgrade.

## 13. Required future evidence report

Include:
- builder identity/version/edition;
- BC level;
- BW-01…BW-50 applicable pass/fail/NA with rationale;
- reference Blueprint snapshots/configs;
- editor/frontend render evidence;
- dynamic data/Policy evidence;
- asset and bundle observations;
- upgrade/regression results;
- known unsupported/degraded capability map.

## 14. Current state

**BW fixtures executed: 0/50.**  
**Gutenberg/Elementor/Bricks/WPBakery/Visual Composer runtime certifications: 0.**

No builder package install, block/widget/element registration, editor run, Node build, browser fixture or frontend render has been executed.

## 15. Development gate

Execution requires explicit owner consent under ADR-0014.