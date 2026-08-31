# WPEssential — Builder Integration & Certification Matrix

Status: **Phase 0 planning — no builder adapter implementation authorized**  
Research date: 2026-08-27.

## Goal
Support Gutenberg/WordPress Blocks, Elementor, Bricks, WPBakery and Visual Composer without pretending their extension models are interchangeable.

WPEssential uses a shared **Component Blueprint** for portable concepts, then a builder-specific adapter exposes only capabilities that builder can faithfully represent.

---

# Core rule

A builder adapter page must show three things explicitly:

1. **Shared capabilities** available from Component Blueprint.
2. **Builder-specific capabilities** supported by that adapter.
3. **Unsupported / degraded capabilities** that cannot be represented faithfully.

“No code” for the end user does not mean WPE generates arbitrary unreviewed PHP/JS. It compiles approved blueprint primitives through reviewed adapter code.

---

# Shared Component Blueprint

Portable candidate properties:
- stable component UUID/key;
- title/description/category/icon abstraction;
- content/control schema;
- default values;
- field/query/relation/dynamic-value bindings;
- safe server renderer/template primitive;
- semantic style token groups;
- responsive settings;
- conditional visibility;
- repeatable controls;
- actions/links;
- required assets declared through Asset Registry;
- cache/SSR hints;
- accessibility metadata;
- empty/loading/error states;
- version/dependencies.

Builder-specific internal document data is **not** the canonical WPE Component Blueprint.

---

# 1. Gutenberg / WordPress Blocks

## Official extension model
Current WordPress guidance recommends `block.json` metadata with server-side registration; block registration on server + client enables dynamic rendering, Block Supports, hooks and related platform behavior.

## WPE adapter target
- generated/registered block metadata from approved blueprint;
- server-side/dynamic render where WPE data/query/policy is involved;
- Inspector controls mapped from supported control schema;
- block attributes mapped only for presentation/user configuration that belongs in document content;
- dynamic WPE entity data resolved at render/runtime rather than copied into post block attributes unnecessarily;
- block supports exposed by explicit compatibility map;
- style/editor/front assets registered through WordPress/WPE asset system;
- variations/patterns only after separate semantics;
- InnerBlocks only for blueprints explicitly designed as containers.

## Capability classes
- Static display component — target support.
- Dynamic WPE component — target support via server renderer.
- Container/nested component — later certified class.
- Editor-only data inspector — possible through dedicated block/sidebar integration.

## Non-negotiable
Do not generate dozens of unmanaged PHP files at runtime inside uploads. Generated configuration remains definitions; production registration is handled by installed trusted WPE adapter/runtime.

---

# 2. Elementor

## Official extension model
Elementor custom widgets extend `\Elementor\Widget_Base`, register through Elementor's widget manager, define controls/dependencies, and render from widget settings.

Elementor Dynamic Tags use a separate tag class/registry. Current official docs state the Dynamic Tags system exists in Core infrastructure but active dynamic tags are an Elementor Pro feature.

## WPE adapter target
### Widgets
- WPE Component Blueprint → registered WPE Elementor widget class/adapter instance;
- WPE controls mapped into Elementor supported controls;
- content/style/advanced groups separated where meaningful;
- dynamic WPE data source controls can be implemented inside WPE widget even without promising Elementor's native Dynamic Tag UX;
- render uses WPE Renderer/Policy/Data Source contract;
- widget dependencies/assets declared through adapter.

### Native Elementor Dynamic Tags integration
Certification requires **Elementor Pro** when current Elementor behavior requires it.

Expose selected WPE typed values as native Elementor Dynamic Tags by category:
- text;
- number;
- URL;
- color;
- image;
- media;
where WPE type semantics match.

A site with Elementor Free only must not see a broken WPE “native dynamic tags supported” claim.

### Elementor Templates
Dashboard/Email/etc. may reference selected Elementor template IDs only through supported render APIs. WPE never rewrites Elementor's private document JSON as its canonical format.

## Certification dimensions
- Elementor Free widget registration/render;
- Elementor Pro dynamic-tag bridge;
- editor rendering;
- frontend render;
- responsive settings;
- CSS/assets;
- query/field bindings;
- widget output caching compatibility;
- editor experimental/features compatibility.

---

# 3. Bricks

## Official extension model
Current Bricks developer docs expose custom Elements by extending `Bricks\Element`, documented controls/hooks/functions and dynamic-data extension points.

## WPE adapter target
- Component Blueprint → WPE Bricks Element adapter;
- common controls → Bricks controls;
- renderer delegates to WPE dynamic data/query/policy;
- style groups mapped to supported Bricks CSS/property contracts;
- Bricks dynamic-data tags adapter considered separately from element adapter;
- nested/component-child support only after dedicated certification;
- avoid relying on undocumented internal builder data structures.

## Special risk
Bricks is a theme/builder ecosystem rather than a WordPress.org core API. Adapter version range must be explicit and compatibility tested before every supported release range change.

---

# 4. WPBakery Page Builder

## Official extension model
WPBakery elements are fundamentally WordPress shortcodes. Official docs use `vc_map()` to expose/edit shortcode attributes in the builder UI.

## WPE adapter target
- WPE Component Blueprint compiles to a stable WPE shortcode contract;
- WPBakery `vc_map()` adapter exposes supported parameters;
- WPE Renderer owns final safe output;
- nested/container elements only for blueprints certified for WPBakery content/container semantics;
- builder UI parameter dependencies mapped where possible;
- frontend can still render the WPE shortcode even when WPBakery editor is unavailable, if the component itself has no hard builder dependency.

## Advantage
This adapter should reuse the universal WPE Shortcode surface rather than creating a second renderer.

## Boundary
WPBakery shortcode attribute storage/document markup remains builder/content behavior; WPE does not make `vc_map()` arrays the platform Component Blueprint.

---

# 5. Visual Composer Website Builder

## Official extension model
Visual Composer's current developer API uses custom element manifests/settings, React editor components, API hooks and a builder-specific asset/build structure. Public-view output is compiled HTML while editor components are React based.

This is materially different from WPBakery despite historic naming confusion.

## WPE adapter target
- separate **Visual Composer Website Builder** adapter page and detection;
- WPE component manifest mapping only where VC element schema supports it;
- VC editor component glue generated/owned by trusted installed adapter code, not arbitrary runtime user JS;
- element `manifest.json` / attributes map from supported blueprint controls;
- public rendering delegates to WPE safe renderer where possible;
- required VC assets declared via documented element API;
- dynamic-content bridge separately certified;
- nested/inner element support only after specific certification.

## Toolchain concern
VC's official custom element model assumes its own React/Webpack element build workflow. WPE must decide whether to ship generic prebuilt adapter component runtime rather than create per-user webpack builds on production sites.

**Preferred direction:** no Node/npm build is required on customer WordPress site. WPE ships trusted prebuilt adapter runtime capable of interpreting safe Component Blueprints.

This requires an authorized prototype before acceptance.

---

# 6. WPE Shortcode adapter — universal fallback

Even without a visual builder, a published component can expose a typed WPE shortcode where appropriate.

Candidate syntax uses stable component key/UUID and allowlisted attributes, e.g. conceptual:
- component identity;
- variant;
- explicit parameter values.

Do not allow arbitrary shortcode attributes to become raw PHP/template expressions.

Shortcode render:
- validates attributes;
- resolves current principal/context;
- applies Policy;
- invokes WPE Renderer;
- loads only declared assets;
- honors cache/privacy rules.

---

# Builder capability matrix

Legend:
- **Core** — intended first certification
- **Later** — explicit later sub-certification
- **Conditional** — requires paid/additional builder capability
- **No claim** — not promised by generic blueprint

| Capability | Gutenberg | Elementor | Bricks | WPBakery | Visual Composer |
|---|---|---|---|---|---|
| Basic custom component/widget | Core | Core | Core | Core | Core after prototype |
| WPE server-rendered dynamic data | Core | Core | Core | Core | Core candidate |
| Common content controls | Core | Core | Core | Core | Core candidate |
| Common style controls | Core | Core | Core | Core subset | Core subset |
| Responsive controls | Core/Block Supports | Core | Core | Builder-dependent subset | Builder-dependent subset |
| Repeater controls | Later/Core per block UI | Core where Elementor control supports | Core where controls support | Later/param limitations | Later |
| Nested/container components | Later | Later | Later | Later via container semantics | Later via inner elements |
| Native builder dynamic-tag system | WPE dynamic block/data binding | Conditional: Elementor Pro | Later adapter | N/A/shortcode params | Later dynamic-content bridge |
| Query/listing binding | Core via WPE renderer | Core | Core | Core shortcode | Core candidate |
| Role/entitlement conditional render | Core server policy | Core server policy | Core server policy | Core server policy | Core server policy |
| Builder-native template migration | No claim | No claim | No claim | No claim | No claim |
| Runtime user-supplied PHP/JS | No | No | No | No | No |

---

# Separate adapter screens

Builder Widgets Builder uses distinct screens/tabs:

1. **Blocks / Gutenberg**
2. **Shortcodes**
3. **Elementor**
4. **Bricks**
5. **WPBakery Page Builder**
6. **Visual Composer Website Builder**
7. Future certified builders

Each adapter screen shows:
- builder detected/version;
- certification range/status;
- available WPE components;
- supported capability matrix;
- missing dependency (e.g. Elementor Pro for native Dynamic Tags);
- register/unregister state;
- compatibility diagnostics;
- docs link;
- adapter assets/health;
- deprecated/unsupported warnings.

---

# Builder version certification

Adapter manifest declares:
- builder product identity (avoid WPBakery vs Visual Composer confusion);
- minimum/maximum certified versions;
- tested WordPress/PHP range;
- Free/Pro requirement where relevant;
- WPE Platform API range;
- feature flags certified;
- known builder experiments/features affecting adapter;
- fixture version.

Unknown newer builder version:
- adapter may operate in `uncertified` compatibility state if APIs are still present;
- do not automatically claim supported;
- diagnostics warns administrator;
- critical removed API → disable affected adapter safely, not entire WPE.

---

# Certification levels

## C0 — Detected
Builder/version detected only.

## C1 — Registration
Component appears in builder/editor and basic control save/load works.

## C2 — Render Certified
Editor + frontend rendering, assets, dynamic WPE values and responsive behavior pass fixtures.

## C3 — Advanced Integration
Dynamic tags/native data bridge, nested components, query/listing integrations or builder-specific advanced features pass dedicated fixtures.

Marketing “WPEssential supports Builder X” should state the meaningful feature scope. A builder detected at C0 is not support.

---

# Required test fixture component set

Every builder adapter certification should test the same reference blueprints:

1. **Text Card** — text, icon, link, style controls.
2. **Entity Card** — field/image/reference dynamic bindings.
3. **Query List** — WPE Query + repeated child rendering/pagination boundary.
4. **Conditional CTA** — role/membership entitlement visibility.
5. **Responsive Component** — typography/spacing/layout at breakpoints.
6. **Asset Component** — JS/CSS dependency scoped loading.
7. **Error/Empty Component** — editor/front empty/error states.

Advanced certification adds:
- repeater control;
- nested/container;
- builder-native dynamic tag/data binding.

---

# Asset/performance contract

- builder adapter JS/CSS loads only when that builder/editor or WPE component requires it;
- no global Elementor/Bricks/WPBakery/VC assets on sites where builder inactive;
- no duplicate React runtime bundled when builder/WordPress host provides required public runtime unless adapter architecture specifically isolates it safely;
- public frontend render should not require builder editor assets;
- component dependencies declared deterministically;
- unsupported third-party library dependencies blocked or registered through Asset Registry after license/security review.

---

# Accessibility

Component Blueprint must include enough semantics to render accessibly across builders, but builder UI accessibility is partly builder-owned.

WPE certification checks:
- frontend semantic HTML;
- keyboard interaction for WPE interactive output;
- focus states;
- labels/ARIA when necessary;
- no color-only state;
- editor WPE controls usable with keyboard where adapter controls that surface.

---

# Import/export portability

WPE Component Blueprint exports independently of a specific builder.

Builder document references are separate:
- WPE can export the component definition;
- an Elementor/Bricks/WPBakery/VC page document containing it remains builder-owned content;
- WPE does not promise page document conversion between builders.

This prevents “Elementor page → Bricks page” from accidentally becoming a hidden impossible requirement of Builder Widgets Builder.

---

# Removal/disable behavior

When builder plugin disappears:
- WPE Component Blueprint remains;
- adapter unregisters safely;
- shortcode/server WPE rendering can remain where builder document stores a WPE shortcode and semantics permit;
- builder-native proprietary element data remains in builder document and may show missing-element behavior controlled by builder;
- WPE does not delete builder content automatically;
- diagnostics explains affected components.

---

# Research refresh triggers

Refresh official adapter research when:
- builder major release;
- public API deprecation;
- React/runtime shift;
- editor architecture change;
- Elementor Pro dynamic tags behavior changes;
- Bricks control/element API changes;
- WPBakery shortcode API changes;
- Visual Composer manifest/build/API changes.

---

# Development gate

This is planning only. No block/widget/element registration, builder package install, Node build, generated adapter source or compatibility fixture execution is authorized before explicit owner development consent under ADR-0014.