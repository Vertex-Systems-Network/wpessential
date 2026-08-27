# WPEssential — Builder Widgets Builder Exhaustive Specification

Status: **Phase 0 planning only — no development consent**  
Module: **Builder Widgets Builder — Pro**

This document deepens `ADMIN-EXPERIENCE-SPECS.md` and defines a shared Component Blueprint plus separate adapter semantics for Gutenberg, Shortcodes, Elementor, Bricks, WPBakery, Visual Composer and future builders.

## Research baseline

Reviewed current official documentation for WordPress block metadata, Elementor widgets/controls, Bricks custom elements/controls, and WPBakery element/parameter APIs.

Core rule:

**Canonical WPE Component Blueprint is not identical to any third-party builder’s document format.**

Each adapter declares what subset/superset can be represented safely.

---

# 1. Component definitions list

Columns:

- Name
- Key
- Category
- Status
- Render mode
- Controls count
- Dynamic bindings
- Builder adapters/status
- Used by
- Health
- Updated
- Actions

Actions:

- Edit Blueprint
- Preview
- Duplicate
- Builder compatibility
- Revisions
- Export
- Disable/Archive
- Delete definition

---

# 2. Component editor sections

1. General
2. Controls
3. Data Bindings
4. Structure/Render Template
5. Styles
6. Responsive Controls
7. Conditions
8. Assets
9. Builder Adapters
10. Preview
11. Dependencies
12. Diagnostics
13. Revisions
14. Export

---

# 3. Component identity

- name
- UUID
- machine key
- description
- category
- icon
- keywords
- status Draft default
- component type:
  - leaf
  - container
  - collection/query

Machine key changes after external builder documents reference it are breaking/migration-class.

---

# 4. Canonical control schema

Every Blueprint control:

- UUID
- machine key
- label
- description/help
- logical type
- UI control preference
- default
- required
- validation
- options source
- responsive behavior
- dynamic binding allowed
- condition/dependency
- group/section/tab
- visibility Policy
- style target mapping optional
- export/import behavior

The server understands every persisted value. Builder UI is not authoritative validation.

---

# 5. Canonical control types

Initial shared palette:

- text
- textarea
- rich text
- number
- range
- boolean/switch
- select
- multi-select
- radio
- checkbox list
- color
- date/time/datetime
- URL/link
- image
- gallery
- media/file
- icon
- entity reference
- Query selector
- Listing/Template selector
- repeater/group
- dimensions
- spacing
- typography token/settings
- border
- shadow
- background
- alignment
- responsive visibility
- code/JSON only for safe stored data, never executable code
- custom registered control

Each adapter maps only supported controls and reports degradation/unsupported behavior.

---

# 6. Control grouping

Canonical UI groups:

- Content
- Style
- Advanced

Subsections:

- name/title
- order
- description
- collapsed/default state
- condition

Adapter may translate to builder-native sections/tabs/groups.

---

# 7. Control conditional logic

Control visibility uses shared Conditions Engine over other component settings/context.

Requirements:

- cycle detection
- typed compare
- missing dependency fallback

Adapter can use builder-native dependency UI if semantics match; otherwise WPE adapter runtime/editor handles condition.

---

# 8. Responsive values

Canonical responsive object candidate:

- base/desktop
- tablet
- mobile

Optional inherited values.

Adapters map to native responsive controls only when builder semantics match. Unsupported extra builder breakpoints require adapter-specific extension metadata, not mutation of core Blueprint unless product accepts broader breakpoint schema.

---

# 9. Dynamic data binding

A control may permit:

- static value
- current entity field
- WPE Custom Field
- Query result/context
- relation
- site setting
- user/profile
- Membership/Entitlement
- builder native dynamic tag when adapter can translate
- registered resolver

Canonical saved WPE binding remains WPE-resolvable unless explicit builder-native binding mode is selected.

Builder-native dynamic tag references can be stored as adapter extension data but are not assumed portable to other builders.

---

# 10. Render model

Modes:

## WPE server renderer — recommended default

Builder adapter stores/selects component + settings; WPE PHP renderer produces frontend markup.

Advantages:

- same security/output across builders
- no duplicated render implementation
- SEO/SSR

## Builder-native generated renderer

Only when builder requires/benefits and adapter can guarantee behavior.

## Hybrid

Server initial markup + approved WordPress Interactivity/API behavior.

No user-entered arbitrary PHP/JS renderer.

---

# 11. Structure/template primitives

Reuse Dynamic Listings/component primitive schema where possible:

- container
- text/heading
- media
- icon
- link/button
- conditional
- list
- child slot
- repeater loop
- partial
- nested registered component

Container Blueprint declares allowed child components/slots.

---

# 12. Container / nested component

Options:

- child slot(s)
- allowed child component categories/keys
- minimum/maximum children
- child ordering
- default children/template

Adapters that cannot faithfully implement arbitrary nested child builder objects show unsupported or use one server-rendered WPE child content representation.

---

# 13. Style bindings

Canonical style rule:

- control UUID
- target element selector token/name
- CSS property from allowlist
- transform/formatter
- responsive mapping

Component template defines named style targets; user does not type arbitrary global selector by default.

Use design tokens wherever possible.

---

# 14. Custom CSS

Not a standard component requirement.

If future Developer Mode permits scoped CSS:

- scope to unique component instance/root
- sanitize/parse policy
- capability
- size limit
- no arbitrary JS

Separate ADR/security review required before normal product support.

---

# 15. Assets

Blueprint asset declaration:

- style entry
- script/script-module entry
- runtime dependency handle/module
- condition: editor/frontend/both
- load only when component present

No remote arbitrary CDN JS dependency field in normal UI.

External library dependencies require package/security/license review.

---

# 16. Frontend JS behavior

Prefer:

- WordPress Interactivity API for portable WPE dynamic blocks/components when accepted
- small scoped registered modules

Builder native frontend hook can adapt only when needed.

No inline `eval()`/user JavaScript actions.

---

# 17. Accessibility contract

Every Blueprint declares/validates:

- semantic root role/tag
- focusable elements
- keyboard behavior
- accessible names
- state announcements
- reduced motion
- contrast/style tokens

Adapter cannot mark component “supported” if it loses essential accessibility behavior.

---

# Part B — Gutenberg Block Adapter

# 18. Adapter strategy

Preferred: generated/runtime registered dynamic block whose metadata is derived from accepted Component Blueprint and WordPress-supported block metadata.

Canonical external representation should align with `block.json` where a distributable generated block package is created.

---

# 19. Gutenberg metadata mapping

Track relevant current block metadata fields:

- `apiVersion`
- `name`
- `title`
- `category`
- `parent`
- `ancestor`
- `allowedBlocks`
- `icon`
- `description`
- `keywords`
- `version`
- `textdomain`
- `attributes`
- `providesContext`
- `usesContext`
- `selectors`
- `supports`
- `styles`
- `example`
- `variations`
- `blockHooks`
- `editorScript`
- `script`
- `viewScript`
- `viewScriptModule`
- `editorStyle`
- `style`
- `viewStyle`
- `render`

Not every Blueprint exposes every metadata field as ordinary UI; adapter Advanced panel owns WordPress-specific options.

---

# 20. Gutenberg attributes

Controls map to typed block attributes only when data belongs to block instance.

Attribute options:

- type
- default
- enum where appropriate
- source/selector mappings only if static saved markup architecture uses them

WPE dynamic/server blocks can store settings attributes and render server-side.

Sensitive/data-source values are fetched server-side rather than embedded unnecessarily in saved post markup.

---

# 21. Block supports mapping

Expose supported subset based on component:

- align
- anchor
- color
- spacing
- typography
- layout
- dimensions
- border
- shadow
- position
- visibility
- lock
- reusable/multiple where relevant
- interactivity support

Prefer WordPress standard supports over duplicate WPE style controls when semantics are equivalent.

---

# 22. Context

Map Blueprint context to:

- `providesContext`
- `usesContext`

Only stable, non-sensitive values passed through block context.

---

# 23. Gutenberg asset distinction

Current WordPress distinguishes:

- classic scripts (`script`, `viewScript`)
- script modules (`viewScriptModule`)

They are not blindly interchangeable.

If Interactivity API is used, adapter follows current script-module dependency model.

---

# 24. Dynamic render

`render`/server callback uses shared WPE renderer.

Editor preview:

- ServerSideRender or purpose-built editor preview depending performance/UX
- avoid network call on every keystroke without debounce

---

# 25. Variations

Optional WPE component presets can map to block variations when useful.

Variation defines:

- name
- title
- description
- attributes/settings preset
- scope/conditions according to supported WordPress behavior

Variations are not separate component implementations.

---

# 26. Gutenberg acceptance

- block registers from metadata
- inserter
- attributes round-trip
- server render
- standard supports
- responsive styles
- context
- variations
- editor/frontend asset isolation
- script vs script-module compatibility
- block deactivation preserves post content gracefully

---

# Part C — Universal Shortcode Adapter

# 27. Shortcode identity

Stable shortcode tag derived from namespaced component key.

Options:

- tag
- declared attributes only
- content/enclosing mode if component supports slot/text content

No arbitrary attribute forwarded to Query/HTML.

---

# 28. Attribute mapping

Each public shortcode attribute:

- name
- Blueprint control UUID
- scalar/list encoding
- validation
- default/inherit

Complex structured component settings are better referenced via saved component/preset UUID than encoded into huge shortcode strings.

---

# 29. Shortcode rendering

Same WPE server renderer.

Output:

- escaped/sanitized
- no direct echo from user callback
- contextual Policy

Shortcode inside email/admin/listing contexts only when target renderer explicitly permits.

---

# Part D — Elementor Adapter

# 30. Elementor widget metadata

Adapter maps:

- internal widget name
- title
- icon
- categories
- keywords
- help URL/description if supported
- widget dependencies

Registration follows current supported Elementor widget manager hook/API.

---

# 31. Elementor control sections

Canonical Content/Style/Advanced groups map to Elementor control sections/tabs.

Controls can use:

- regular controls
- responsive controls
- group controls

WPE adapter chooses native control type by logical Blueprint control.

---

# 32. Elementor control map

Planned common mapping:

- text → TEXT
- textarea → TEXTAREA
- rich text → WYSIWYG
- number → NUMBER
- range → SLIDER
- boolean → SWITCHER
- select → SELECT
- multi choice → SELECT2/appropriate control
- color → COLOR
- media → MEDIA
- gallery → GALLERY
- icon → ICONS
- URL/link → URL
- repeater → REPEATER
- typography/border/shadow/background → native group controls where faithful
- responsive spacing/number → responsive controls

Exact control constants/options require adapter version certification before acceptance.

---

# 33. Elementor dynamic tags

Two directions:

1. WPE widget controls may support Elementor dynamic values where Elementor control API permits.
2. WPE may register its own Dynamic Tags separately through Page Builder integration layer.

Builder-native dynamic tag setting is adapter data and may not be portable to Gutenberg/Bricks.

---

# 34. Elementor rendering

Default widget `render()` delegates canonical settings/bindings to WPE renderer.

Inline editing support only for text controls where mapping is safe and explicit.

Frontend dependencies declared through current supported widget dependency methods/assets.

Output caching uses Elementor feature only when compatible with user/policy-dynamic content; WPE does not cache personalized output globally by accident.

---

# 35. Elementor controls injection

Separate future capability: injecting WPE controls into existing Elementor widgets.

Not the same as Builder Widgets Builder-generated widgets.

If offered:

- target widget allowlist
- documented injection hooks
- namespace controls
- compatibility matrix

Do not automatically inject WPE controls into every third-party widget.

---

# Part E — Bricks Adapter

# 36. Bricks element mapping

Current custom element model maps Blueprint to a Bricks `Element` implementation with:

- element name/label/category/icon/keywords
- control groups
- controls
- render
- element-specific assets

---

# 37. Bricks controls

Map canonical controls to documented Bricks control families:

- content controls
- styling controls
- media
- advanced/query/repeater where semantics fit

CSS-enabled Bricks controls can target component style slots/selectors only through adapter-owned safe mapping.

---

# 38. Bricks dynamic data

Bricks dynamic data tags may be accepted in adapter mode.

WPE native dynamic bindings remain canonical for cross-builder portability.

Render uses supported dynamic-data helpers when adapter-native tags are selected.

---

# 39. Bricks assets

Element-specific scripts/styles load only where element is present through documented element asset mechanism.

This aligns with WPE asset isolation requirement.

---

# Part F — WPBakery Adapter

# 40. Architecture

WPBakery elements are fundamentally WordPress shortcodes with builder UI mapping.

Therefore:

- WPE Shortcode adapter is rendering foundation;
- WPBakery `vc_map()` supplies element metadata and parameter editor.

---

# 41. WPBakery element metadata

Map current documented element settings such as:

- `name`
- `base` / shortcode tag
- `description`
- `category`
- `icon`
- `class`
- `show_settings_on_create`
- content/container/nested semantics through supported API
- `params`

Advanced native-only fields remain adapter extension data.

---

# 42. WPBakery parameter common options

For each mapped param, current API concepts include:

- `type`
- `holder`
- `class`
- `heading`
- `param_name`
- `value`
- `description`
- `group`
- `weight`
- `dependency`
- `admin_label`
- `edit_field_class`
- `param_holder_class`
- `save_always`
- type-specific `settings`

WPE does not expose arbitrary JavaScript `callback` or arbitrary backend `custom_markup` as ordinary no-code Blueprint options because these create unsafe/non-portable extension behavior.

Developer SDK can implement reviewed adapter plugins.

---

# 43. WPBakery repeater/group

Canonical Repeater may map to `param_group` when its data semantics fit.

Because WPBakery encodes param groups into shortcode attributes, adapter must define:

- serialization size limits
- nested group depth
- decoding/validation
- portability implications

Large relational/query data remains referenced by UUID, not serialized into shortcode params.

---

# 44. Nested WPBakery content

Container components can map to supported nested shortcode/container model only when child semantics are compatible.

Otherwise WPE renders internal children server-side as one shortcode component.

---

# Part G — Visual Composer Adapter

# 45. Separate from WPBakery

Visual Composer Website Builder and WPBakery are treated as separate products/adapters despite historical naming overlap.

No assumption that `vc_map()` is the correct Visual Composer Website Builder API.

---

# 46. Visual Composer acceptance gate

Before adapter can become Accepted, current official API documentation must be refreshed and mapped for:

- element registration
- attributes/controls
- container/nesting
- dynamic content
- frontend/editor rendering
- assets
- compatibility/versioning

Until then adapter status remains **Planned / Not Accepted**.

WPE never labels Visual Composer support complete based only on WPBakery compatibility.

---

# Part H — Adapter Capability Matrix

# 47. Required capability flags

Per builder/version record:

- leaf component
- container/nesting
- text
- rich text
- numeric
- choices
- media
- icon
- repeater
- responsive
- style groups
- dynamic data
- conditions
- query selector
- WPE server render
- editor preview
- inline editing
- asset isolation
- frontend interactivity
- global styles/design tokens
- child slots

Values:

- Native
- Adapted
- WPE-rendered only
- Unsupported
- Experimental

---

# 48. Unsupported feature behavior

When enabling adapter:

- show unsupported Blueprint controls/features
- provide safe fallback if available
- block Publish to that adapter only when loss changes meaning/security
- preserve definition for future adapter version

Never silently drop settings.

---

# 49. Adapter version certification

Each adapter manifest declares:

- builder plugin ID
- minimum version
- tested versions
- last certified date
- incompatible versions
- required WPE version

Unknown newer builder version:

- continue best-effort only if public API compatibility policy allows
- diagnostics “Not yet certified”
- never claim verified compatibility.

---

# 50. Missing builder behavior

If builder deactivated:

- WPE Component definitions remain intact
- builder-specific editor widgets unavailable
- existing shortcode/dynamic block output behavior follows adapter graceful degradation
- no data deletion

If page contains missing builder document, WPE does not rewrite it automatically.

---

# 51. Builder-specific extension data

Schema:

- adapter ID
- adapter schema version
- builder-specific settings

Core Blueprint remains portable.

Extension data cannot weaken core authorization/sanitization.

---

# 52. Preview matrix

WPE component preview modes:

- canonical WPE render
- Gutenberg preview
- Elementor preview
- Bricks preview
- WPBakery preview
- Visual Composer when supported

Comparison diagnostics can identify styling/control loss.

---

# 53. Import/export

Component export contains:

- Blueprint
- render schema
- controls
- style mappings
- assets refs
- adapter extension data

Import:

- checks builder availability
- maps dependencies
- preserves unsupported adapter data

No proprietary builder source/code bundled if license forbids redistribution.

---

# 54. Security

- no arbitrary PHP renderer
- no arbitrary JS callback
- no unsafe raw SVG/icon
- no arbitrary remote scripts
- URL/HTML escaped
- server validates all builder-saved settings
- builder editor privilege cannot bypass component action/data Policy
- third-party dynamic data treated as untrusted input

---

# 55. Performance

- component assets only where used
- avoid registering/enqueueing heavy frontend assets globally
- batch dynamic bindings/Query access
- no per-widget duplicate runtime library
- render cache only for non-personalized content

---

# 56. Acceptance-test matrix

Every certified adapter:

- component appears in builder
- control values save/reload
- default values
- conditional controls
- responsive values
- media/repeater
- dynamic binding
- frontend render matches canonical semantics
- unauthorized action/data blocked
- editor preview
- asset isolation
- builder deactivation/reactivation
- upgrade across supported builder versions
- import/export
- RTL/accessibility where builder permits

Cross-builder:

- same Blueprint produces semantically equivalent output
- unsupported feature clearly reported
- adapter-specific data does not corrupt other adapters

---

# 57. Differentiators

1. One typed Blueprint, explicit adapter capability matrix.
2. Shared server renderer prevents six independent insecure render engines.
3. Gutenberg native metadata/supports are used rather than wrapped as fake Elementor-style controls.
4. Elementor/Bricks/WPBakery keep native editing UX where semantics match.
5. Visual Composer is not falsely treated as WPBakery.
6. Builder-specific settings are extension data, not core lock-in.
7. Adapter certification is versioned/testable.
8. Assets remain component-scoped.

---

# 58. Open decisions before implementation

- explicit user development consent;
- accepted Component Blueprint JSON schema;
- accepted UI/build toolchain;
- exact current control mapping/version matrix for Elementor/Bricks/WPBakery;
- Visual Composer official API mapping;
- generated block package vs runtime block registration strategy;
- dynamic tag portability policy;
- nested component portability rules;
- style-token translation across builders;
- builder editor preview infrastructure;
- generated adapter code packaging/distribution model and licenses.

**Development authorization remains NO.**
