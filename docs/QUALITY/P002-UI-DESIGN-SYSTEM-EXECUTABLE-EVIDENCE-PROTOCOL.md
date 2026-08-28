# WPEssential — P-002 UI / Design System Executable Evidence Protocol

Status: **Phase 0 planning only / EXECUTION NOT AUTHORIZED**  
Work package: `P0-M00-WP09`  
Related: ADR-0002, ADR-0005, ADR-0012, ADR-0014, P-001/CF, P-008/BT, Component Blueprint, Frontend Dashboard, Builder Widgets, Admin surfaces.

## 1. Purpose

Freeze a bounded future evidence contract for WPEssential's shared administration UI runtime and wrapper design system before ADR-0005 can be accepted.

This protocol authorizes no scaffold, dependency, browser run, accessibility test, build or UI implementation.

## 2. Architectural assumptions under test

- React + TypeScript with the **WordPress-provided React runtime**.
- WPEssential-owned wrappers are the domain-facing UI API.
- Stable minimum-version-compatible WordPress primitives are preferred.
- While WordPress 6.9 remains the minimum candidate, WPE must not require WordPress 7.1-only `wp-theme`/ThemeProvider merely to boot/render.
- WordPress 7.1+ stable theme/token capability may enhance the wrapper theme adapter.
- Current experimental `@wordpress/ui` is not a 1.0 foundational dependency.
- Untitled UI is a visual/interaction reference; only separately reviewed MIT material can become candidate source.
- Untitled PRO source is not approved for distribution by default.
- Lucide remains an icon vocabulary behind a WPE icon abstraction.
- No global wp-admin CSS reset.
- Accessibility, RTL, localization, narrow layouts and exact-route asset loading are release requirements.

## 3. Evidence state

Fixtures defined: **UI-01…UI-104**  
Fixtures executed: **0/104**  
P-002 runtime certification: **none**  
ADR-0005: **Proposed**

## 4. Required execution record

Every future authorized run records:
- exact WPE commit/artifact;
- WordPress/PHP/browser profiles;
- WordPress package/runtime versions;
- React/React-DOM/JSX runtime evidence;
- build candidate/version producing the fixture;
- LTR/RTL/locale/admin color scheme/accessibility settings;
- automated + manual evidence actually executed;
- screenshots/video only as supplemental evidence, never sole proof;
- pass/fail/inconclusive per fixture;
- route/chunk/style payload measurements;
- known deviations and cleanup status.

---

# 5. Fixed fixtures

## Group A — Runtime/package capability matrix — UI-01…UI-08

### UI-01 — minimum WordPress profile boot
Representative WPE admin shell boots on the accepted/proposed minimum profile without importing a runtime-only-later package.

### UI-02 — current/reference WordPress profile boot
Same shell boots on the current/reference profile using the same domain wrapper API.

### UI-03 — minimum-vs-current capability inventory
Record which public WP packages/features are actually available in each profile; no latest-doc assumption substitutes for runtime evidence.

### UI-04 — WordPress 7.1+ theme capability detection
When stable `wp-theme`/ThemeProvider exists, WPE theme adapter can use it without changing module API.

### UI-05 — minimum-floor theme fallback
Without 7.1-only theme capability, WPE semantic tokens/components remain usable and visually coherent; no fatal import or missing-style blank screen.

### UI-06 — experimental package absence
Remove/block current experimental `@wordpress/ui`; canonical WPE UI still boots and works.

### UI-07 — unsupported package/API preflight
A fixture using an API unavailable on the supported floor fails during guard/build/test rather than shipping an unhandled production fatal.

### UI-08 — capability upgrade regression
Move fixture from minimum to current profile; enhanced path may activate, but semantic behavior/permissions do not change unexpectedly.

## Group B — React/JSX runtime integrity — UI-09…UI-16

### UI-09 — single React runtime inventory
Inspect loaded scripts/modules/chunks: no second bundled React on normal WPE wp-admin screen.

### UI-10 — single ReactDOM/runtime inventory
No competing ReactDOM/client runtime is bundled when WordPress provides it.

### UI-11 — JSX runtime duplicate scan
Generated chunks do not smuggle incompatible React/JSX runtime helpers as a second framework copy.

### UI-12 — multiple WPE routes
Navigate across multiple WPE admin routes; shared runtime remains single and stable.

### UI-13 — WPE + Gutenberg/editor coexistence
Representative WPE surface coexists with WordPress editor/runtime without hook/dispatcher invalid-call errors.

### UI-14 — WPE + third-party React plugin coexistence
Synthetic coexistence fixture proves WPE does not globally replace React or mutate shared globals.

### UI-15 — lazy chunk runtime
Dynamic WPE chunk loads against the same externalized runtime and does not bundle a new framework copy.

### UI-16 — runtime mismatch stop gate
Deliberate incompatible/duplicate runtime fixture must be detected and classified as failure.

## Group C — Wrapper contract and substitution — UI-17…UI-24

### UI-17 — core wrapper surface
Button, field, select, checkbox/radio, notice, spinner, tooltip, dialog and layout wrappers expose WPE-owned domain API.

### UI-18 — underlying primitive swap
Swap one representative underlying stable primitive/test double without changing consuming module contract beyond adapter implementation.

### UI-19 — validation normalization
Field/form wrappers expose consistent error/help/disabled/required semantics across minimum/current profiles.

### UI-20 — async action normalization
Loading/success/error/retry behavior is consistent across representative action buttons/forms.

### UI-21 — permission-aware disabled/hidden grammar
Wrappers support explicit denied/disabled/read-only states without pretending UI hiding is authorization.

### UI-22 — destructive confirmation grammar
Shared destructive pattern gives clear consequence, focus behavior and irreversible/recovery wording.

### UI-23 — wrapper escape-hatch control
Raw vendor primitive use in domain code is lint/review detectable or explicitly justified; wrapper boundary is enforceable.

### UI-24 — wrapper API compatibility regression
Representative modules compile/render after underlying WP package patch/minor change allowed by support policy.

## Group D — DataViews/DataForm and data-heavy admin — UI-25…UI-32

### UI-25 — list rendering
Representative list renders bounded data through wrapper-compatible DataViews/native primitive profile.

### UI-26 — search/filter
Search/filter UI is keyboard-accessible, localized and maps to server-side semantics without client-only false filtering claims.

### UI-27 — sorting/pagination
Sort/pagination state is visible, operable and consistent with backend result order/page truth.

### UI-28 — bulk selection/actions
Bulk selection and action UX expose scope/count/permission clearly and remain keyboard operable.

### UI-29 — field/edit integration
Representative row/edit form uses DataForm/field wrappers where applicable without bypassing server validation.

### UI-30 — empty/loading/error states
Data-heavy surface has distinct empty, loading, error, degraded and permission-denied states.

### UI-31 — large result UI behavior
Representative larger dataset does not render unbounded DOM merely because backend supports more rows.

### UI-32 — minimum/current parity
DataView/DataForm capability differences are adapter-controlled; supported floor does not silently lose required product behavior.

## Group E — Theme tokens, CSS isolation and portals — UI-33…UI-40

### UI-33 — semantic token contract
WPE tokens cover required surface/text/border/focus/state/spacing/radius semantics without modules hardcoding vendor token names.

### UI-34 — 6.9-compatible token path
Minimum profile produces complete readable styling without 7.1 `wp-theme` dependency.

### UI-35 — 7.1+ token adapter
Stable WordPress theme tokens can be mapped/enhanced without changing semantic module contract.

### UI-36 — no global reset
Visit unrelated wp-admin screens before/after WPE asset load; computed styles/layout show no material WPE leakage.

### UI-37 — selector/root scoping
WPE styles cannot broadly target generic `.button`, headings, tables or body outside approved WPE roots.

### UI-38 — portal/dialog/popover styling
Portal-based UI remains styled/scoped and does not require unsafe global CSS leakage.

### UI-39 — iframe/editor context
Representative embedded/editor context gets only intended WPE styles/assets; parent/iframe leakage is bounded.

### UI-40 — third-party adapter style isolation
Builder/integration-specific UI stylesheet loads only in its adapter context.

## Group F — Accessibility: keyboard, focus, semantics — UI-41…UI-48

### UI-41 — complete keyboard navigation
Representative shell/list/form/dialog flow is operable without pointer.

### UI-42 — visible focus
All actionable controls expose visible focus with sufficient differentiation across themes/states.

### UI-43 — dialog focus trap/restore
Opening/closing dialog manages initial focus, containment where appropriate and restoration correctly.

### UI-44 — popover/menu keyboard semantics
Arrow/Escape/Tab behavior and roles/names match the chosen accessible primitive contract.

### UI-45 — labels/descriptions/errors
Inputs have accessible names; descriptions/errors are associated programmatically.

### UI-46 — drag/reorder keyboard alternative
Every required reorder interaction has an equivalent keyboard/non-drag mechanism with announced result.

### UI-47 — no color-only meaning
Status/error/success/required/selected states include text/icon/shape/semantics beyond color.

### UI-48 — automated accessibility baseline
Run agreed automated accessibility checks; zero known critical/serious violations in representative fixtures unless explicitly accepted/blocking.

## Group G — Accessibility: assistive/async/motion/text — UI-49…UI-56

### UI-49 — screen-reader route title/context
Route/screen changes expose meaningful heading/title/context without duplicate confusing landmarks.

### UI-50 — async announcements
Save/delete/load/error completion states are announced where meaningful without noisy repeated live regions.

### UI-51 — validation summary/focus
Failed form submission identifies errors and moves/focuses context predictably when appropriate.

### UI-52 — reduced motion
Non-essential animation respects reduced-motion preference or has no required motion dependency.

### UI-53 — 200% zoom/larger text
Representative surfaces remain usable without clipped essential actions/text.

### UI-54 — high-contrast/user scheme readability
Focus, text, state and controls remain understandable under supported high-contrast/admin color conditions.

### UI-55 — disabled vs read-only semantics
Assistive technology can distinguish disabled/read-only/permission-blocked states where product behavior differs.

### UI-56 — accessible destructive flow
Confirmation names target/consequence and does not rely on visual placement alone.

## Group H — RTL and localization — UI-57…UI-64

### UI-57 — RTL shell/list/form
Representative UI mirrors/alignment behaves correctly under RTL locale.

### UI-58 — directional icon review
Directional icons/arrows mirror only where semantically appropriate; non-directional icons do not flip blindly.

### UI-59 — logical CSS properties
Layout evidence detects avoidable left/right assumptions that break RTL.

### UI-60 — long translation expansion
Use synthetic 30–60% expanded strings; controls/headings/tables remain usable.

### UI-61 — plural/context translation
Representative counts/actions use translatable plural/context patterns rather than string concatenation.

### UI-62 — no untranslated build strings
Extractable user-facing fixture strings appear in translation catalog/domain as expected.

### UI-63 — locale-specific number/date direction
UI renders server-defined/localized values without hardcoded English formatting where product contract requires locale.

### UI-64 — mixed LTR/RTL content
IDs/URLs/code/provider values inside RTL UI remain legible and do not destroy layout.

## Group I — Responsive/narrow/admin environment — UI-65…UI-72

### UI-65 — narrow wp-admin
Core shell/list/form works at representative narrow admin width without inaccessible offscreen primary actions.

### UI-66 — responsive navigation
Module navigation collapses/scrolls predictably and remains keyboard accessible.

### UI-67 — table/list narrow behavior
Data table has explicit responsive strategy; required fields/actions are not silently lost.

### UI-68 — modal/popover viewport bounds
Overlays remain within viewport and allow content/action access at narrow dimensions.

### UI-69 — WordPress admin bar/sidebar changes
WPE layout tolerates core admin chrome width/state changes.

### UI-70 — browser text scaling
Text scaling does not overlap fixed-height controls essential to operation.

### UI-71 — touch target sanity
Critical actions meet agreed target-size/accessibility guidance where touch use is supported.

### UI-72 — horizontal overflow audit
Any intentional overflow is contained to the component, not whole admin page.

## Group J — State, permission and failure UX — UI-73…UI-80

### UI-73 — loading vs empty distinction
Loading never masquerades as empty/no-data.

### UI-74 — degraded dependency state
Unavailable optional provider/module displays bounded degraded state and safe next action.

### UI-75 — permission denied
Denied resource/action does not leak protected values in labels/counts/previews while explaining allowed next step.

### UI-76 — stale edit/conflict
Representative stale form shows conflict/reload/reconcile behavior instead of silent overwrite.

### UI-77 — unsaved changes
Navigation away from meaningful unsaved edits follows accepted warning/discard grammar without trapping user unnecessarily.

### UI-78 — retryable vs terminal error
UI distinguishes retryable transport failure from validation/permission/terminal error truthfully.

### UI-79 — destructive in-progress state
Prevent duplicate destructive submit while preserving observable recovery/error state.

### UI-80 — offline/network interruption
Transient request loss leaves coherent UI and does not claim a mutation succeeded without server evidence.

## Group K — Icon, content safety and visual consistency — UI-81…UI-88

### UI-81 — icon registry abstraction
Representative module uses WPE icon key/registry, not direct scattered vendor imports.

### UI-82 — accessible icon labeling
Icon-only controls have accessible names/tooltips as needed; decorative icons are hidden semantically.

### UI-83 — missing icon fallback
Unknown/deprecated icon key fails visibly/safely in development and does not crash production screen.

### UI-84 — SVG/content safety
No unsafe arbitrary SVG/HTML injection is introduced through generic icon/content wrappers.

### UI-85 — visual token consistency
Same semantic action/state renders consistently across list/form/settings/dashboard fixtures.

### UI-86 — density/spacing consistency
Representative screens respect shared spacing/layout grammar rather than module-specific arbitrary systems.

### UI-87 — external visual reference independence
Removing Untitled candidate source does not destroy canonical WPE wrapper API/product function.

### UI-88 — license inventory
Any third-party UI/icon source in the future fixture has explicit license/provenance; restricted PRO source is absent from distributable artifact unless separately approved.

## Group L — Assets, performance and route isolation — UI-89…UI-96

### UI-89 — exact-route enqueue
Opening one WPE module loads only shell/shared plus required module assets.

### UI-90 — unrelated wp-admin absence
Core unrelated admin page loads zero WPE module UI bundle/style except any explicitly justified tiny global bootstrap.

### UI-91 — shared chunk once
Navigate/load multiple modules; common chunk/runtime is not duplicated.

### UI-92 — lazy failure UX
Injected lazy-chunk failure produces bounded recoverable/degraded message, not permanent blank screen.

### UI-93 — route payload budget
Measure JS/CSS transfer and parsed size for shell + representative module against declared budgets.

### UI-94 — no duplicate vendor packages
Bundle analysis flags duplicate React/WordPress/common vendors and unjustified component-library duplication.

### UI-95 — stylesheet budget/leak audit
Module CSS size/selectors remain bounded and scoped.

### UI-96 — performance regression baseline
Record representative route first-render/interaction metrics for future regression comparisons without inventing universal performance guarantees.

## Group M — Cross-version regression and release gate — UI-97…UI-104

### UI-97 — minimum/current visual regression
Core semantic states remain present/readable on minimum and current profiles; screenshot diff is supplemental to behavioral assertions.

### UI-98 — package capability downgrade
Remove optional later-version package capability; fallback remains functional.

### UI-99 — WordPress package patch/minor update
Allowed package/core update does not break wrapper contracts or silently introduce experimental dependency.

### UI-100 — third-party admin coexistence
Representative plugin/admin notices/styles/scripts do not materially break WPE root or get broken by it.

### UI-101 — security/permission visual regression
Protected values/actions remain absent/denied after UI refactors; visual component changes cannot weaken server authorization.

### UI-102 — accessibility regression gate
Representative release artifact reruns agreed keyboard/automated/assistive evidence before UI production-ready claim.

### UI-103 — experimental dependency scan
Built/source dependency graph contains no unapproved hard dependency on current experimental `@wordpress/ui` or experimental route/widget framework.

### UI-104 — P-002 production-readiness decision
ADR-0005 cannot be accepted until mandatory fixtures pass on selected P-001 profiles, P-008 build evidence is compatible, critical accessibility/runtime/style-isolation failures are closed and deviations are explicitly recorded.

---

## 6. Stop-the-line conditions

P-002 fails immediately for any supported profile if evidence finds:
- a second/incompatible React/ReactDOM/JSX runtime bundled by WPE;
- minimum WordPress fatal/blank UI caused by a later-only Design System dependency;
- unapproved experimental `@wordpress/ui` or experimental route/widget API as a required foundation;
- global CSS leakage materially breaking unrelated wp-admin;
- keyboard-inaccessible required workflow with no alternative;
- critical protected-data/permission leakage through UI state;
- destructive action that can be triggered ambiguously/duplicated due shared UI behavior;
- RTL/localization failure making required control inaccessible;
- route asset architecture loading large module bundles globally without accepted justification.

## 7. Future report

Authorized P-002 execution report must include:
- UI-01…UI-104 result table;
- exact environment/package/runtime matrix;
- React/JSX duplicate analysis;
- minimum-vs-current capability mapping;
- wrapper substitution evidence;
- accessibility automated/manual evidence;
- RTL/localization/narrow-layout evidence;
- route asset/bundle metrics;
- license/provenance inventory;
- known limitations;
- recommendation: accept ADR-0005, revise it, or remain inconclusive.

## 8. Development gate

No UI source, package manifest, dependency install, React render, browser automation, accessibility scanner, build, screenshot regression, CSS generation or runtime fixture is authorized by this protocol. Explicit owner consent under ADR-0014 remains required.
