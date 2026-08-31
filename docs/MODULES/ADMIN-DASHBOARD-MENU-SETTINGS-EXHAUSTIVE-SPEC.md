# WPEssential — Dashboard Widgets, Admin Menu & Settings Pages Exhaustive Specification

Status: **Phase 0 planning only — no development consent**

Modules:
- Dashboard Widgets Manager — Pro
- Custom Admin Menu Builder — Pro
- Settings Page Builder — Pro

This document deepens `ADMIN-EXPERIENCE-SPECS.md`.

## Research baseline

Reviewed current WordPress Dashboard Widgets API, `wp_add_dashboard_widget()`, meta-box removal, `add_menu_page()`, `add_submenu_page()`, page hook suffix behavior, Settings API, `register_setting()`, `add_settings_section()` and `add_settings_field()`.

Core principle:

**Navigation visibility, UI visibility and authorization are separate.**

---

# Part A — Dashboard Widgets Manager

# 1. Dashboard inventory screen

Inventory includes:

- WordPress core widgets
- third-party registered dashboard meta boxes/widgets
- WPEssential-managed widgets
- Welcome panel as a separate special surface
- Site dashboard vs Network dashboard where multisite applies

Columns:

- Widget title
- Widget ID
- Source/owner heuristic
- Screen
- Context/column
- Priority
- Visibility rule
- Status
- Dismissible
- Health
- Actions

Actions depend on ownership:

WPE widget:
- Edit
- Duplicate
- Hide/Show
- Disable/Enable
- Reorder/profile
- Export
- Delete definition

External/core:
- Inspect
- Hide/Show rule
- Move/reorder where compatible
- Reset override

Do not claim ownership of third-party callback/rendering.

---

# 2. Hide vs remove

Distinct modes:

- **User preference hidden** — analogous to screen-options visibility; widget remains registered.
- **WPE visibility rule** — prevent/show according to policy/profile.
- **Runtime remove/unregister** — remove meta box registration for target actor/screen when safe.

Default for third-party/core customization is non-destructive visibility override, not deleting plugin behavior/data.

---

# 3. Dashboard contexts / columns

WordPress Dashboard meta-box contexts can include current supported values such as:

- normal
- side
- column3
- column4

Priority:

- high
- core
- default
- low

UI reads current registered structure rather than assuming all sites expose identical columns.

Responsive WordPress Dashboard may collapse columns; WPE must not promise fixed pixel placement.

---

# 4. WPE custom widget identity

- name/title
- UUID
- machine key
- description
- status Draft default
- target screen: Site Dashboard / Network Dashboard where permitted
- icon optional WPE UI metadata

---

# 5. Custom widget types

Initial types:

## Rich content

- sanitized rich text/block-safe content
- dynamic tokens

## Announcement/Banner

- heading
- body
- icon/image
- CTA
- severity/style token
- start/end schedule
- dismissible

## Stat card

- Query/data binding
- aggregate/value
- label
- comparison/trend optional
- link

## List

- Query
- item renderer/template
- max rows
- empty state

## Table

- Query
- selected columns
- pagination/limit bounded

## Listing

- Dynamic Listing UUID

## RSS/Remote Feed

- Connection/approved URL
- item limit
- cache/timeout
- safe rendered fields

SSRF protections mandatory.

## Iframe/Embed

Off by default, Advanced.

- approved HTTPS origin/URL
- sandbox policy
- allow attributes policy
- height

Never arbitrary unrestricted iframe HTML.

## Shortcode

- approved/registered shortcode
- bounded output
- performance warning

## Block/server renderer

- registered server-renderable block/template

## Diagnostics/Support widget

Platform-generated data only under explicit safe schema.

---

# 6. Widget visibility targeting

Rules can include:

- all allowed dashboard users
- role
- capability
- specific user
- multisite/site/network context
- date schedule
- feature/module state

Server-side registration/rendering enforces visibility.

---

# 7. Dismissible widget

Controls:

- dismissible: false default except Announcement preset may suggest true
- dismiss scope:
  - per user
  - globally by privileged admin
- dismiss duration:
  - forever until definition revision/reset
  - until date
  - session not primary/default
- show again action for admin/user where permitted

Changing important content can optionally increment dismissal-version so previously dismissed widget reappears; this must be explicit.

---

# 8. Widget scheduling

- start date/time
- end date/time
- site timezone semantics
- active outside schedule false

Scheduling controls visibility evaluation; no exact Cron job needed merely to hide/show because request-time condition can evaluate schedule.

---

# 9. Widget configuration form

WordPress supports dashboard widget control callbacks, but WPE-managed widgets use shared React/Field Schema editor in WPE settings.

Optional direct-dashboard configure action links back to definition or opens approved control surface.

No arbitrary PHP callback UI.

---

# 10. Dashboard presets/profiles

Profile definition:

- name
- UUID
- audience rule
- widget visibility
- ordering/context
- default column layout preference where WordPress supports

Priority/order resolution:

1. security/capability
2. explicit user-specific profile
3. most specific role/capability profile according to documented priority
4. global WPE profile
5. native WordPress/default

Conflicting profiles must be explainable.

---

# 11. Welcome panel

Separate controls:

- show/hide by audience
- replace with WPE onboarding panel only if product strategy accepts
- restore WordPress welcome panel

Do not treat it as ordinary meta-box ID when runtime mechanism differs.

---

# 12. Dashboard asset scope

Custom widget assets load only:

- Dashboard screen that renders them
- relevant WPE widget editor

Remote/listing/chart libraries lazy/load only when used.

---

# 13. Dashboard widget diagnostics/tests

Diagnostics:

- external widget disappeared/changed ID
- context unavailable
- source module unavailable
- Query/Listing missing
- remote feed failed
- iframe origin blocked
- visibility rule matches nobody

Tests:

- core widget hide/restore
- third-party widget retained after WPE disable
- all contexts/priorities
- per-user/role/capability
- dismissal/version reset
- schedule timezone
- remote SSRF/timeout
- network dashboard
- assets isolated

---

# Part B — Custom Admin Menu Builder

# 14. Menu inventory

Inventory scopes:

- Site Admin (`admin_menu`)
- Network Admin (`network_admin_menu`)
- User Admin (`user_admin_menu`) only if product requirement exists

For each top-level/submenu entry show:

- menu/page title
- menu slug
- parent slug
- required capability
- current position
- icon
- hook suffix where known
- source/owner heuristic
- destination type
- current visibility

External/core menu entries remain externally owned.

---

# 15. Menu Profile definition

A profile:

- name
- UUID
- target admin scope
- audience
- ordered tree
- rename/icon overrides
- visibility overrides
- custom WPE entries
- custom validated links
- separators/groups
- default landing/redirect rules reference

Profiles are role/user/capability targeted.

---

# 16. Menu tree depth

WordPress core admin menu natively models top-level + submenu.

WPE Admin Menu Builder therefore does **not** pretend native wp-admin supports arbitrary multi-level nested submenus.

Depth:

- top level
- one native submenu level

Additional visual grouping inside WPE application shell is separate from WordPress core admin menu.

---

# 17. Add custom top-level page

Exact planned controls based on `add_menu_page()` semantics:

- page title
- menu title
- capability
- menu slug
- page content source/render definition
- icon
- numeric position or automatic

### menu slug

- unique
- lowercase alphanumeric/dash/underscore compatible
- stable after external links exist

### icon

- Dashicon
- approved sanitized base64 SVG/data URI generated by WPE
- validated URL only if policy accepts
- no-icon/CSS mode Advanced only

### position

- blank automatic
- integer/float numeric
- UI shows WordPress conventional positions as hints
- collisions are not treated as fatal; WordPress supports non-unique positions

---

# 18. Add submenu page

Controls from `add_submenu_page()` semantics:

- parent slug selected from current valid parents
- page title
- menu title
- capability
- menu slug
- content source
- position numeric/automatic

Parent scope may include CPT menus (`edit.php?post_type=...`) and core menus.

If parent disappears, item becomes Degraded; do not silently move to Settings.

---

# 19. Menu destination types

- WPE Settings/Page definition
- WPE Dashboard/Admin application route
- existing WordPress admin page
- CPT/list screen
- validated internal admin URL
- validated external HTTPS URL — clearly marked external
- documentation/support/account route

External URL does not get fake WordPress capability protection once user leaves site; capability controls whether link is shown/clickable in wp-admin.

---

# 20. Content callback/rendering

WPE custom pages render through registered Page Renderer/Settings/Dashboard components.

No arbitrary PHP callback name/editor.

Page callback always rechecks required capability/resource Policy even if menu visibility already checked.

---

# 21. Hook suffix and scoped assets

Created admin page captures returned hook suffix.

Asset Registry scopes WPE CSS/JS to that hook/page.

No broad `admin_enqueue_scripts` load merely because a custom menu exists.

---

# 22. Existing menu operations

Allowed planning operations:

- reorder
- rename display title
- change WPE-local icon presentation where compatible
- hide/show by profile
- move compatible menu entry top-level order
- move submenu among compatible parents only after impact warning

Not guaranteed:

- rewriting third-party callback/capability
- changing third-party internal routing
- forcing plugin page under arbitrary parent when its code assumes original parent

Move operations get compatibility-risk classification.

---

# 23. Hide vs permission

Hiding menu:

- removes navigation entry for matching actor/profile
- **does not remove destination access**

If true access restriction desired:

- configure Role/Capability/Policy at destination/module
- or Protector/admin access rule where appropriate

UI explicitly states this distinction.

---

# 24. Separators / groups

WordPress top-level separators can be represented, but WPE avoids excessive arbitrary separators.

Controls:

- label for WPE visual admin grouping only where supported
- position
- profile visibility

Native WordPress separators have no page destination.

---

# 25. Per-profile ordering resolution

Menu is built after normal registrations at a documented safe priority.

Order algorithm:

- preserve unknown/new external entries unless profile explicitly positions/hides them
- avoid dropping newly installed plugin menus
- stable known-entry ordering
- unknown entries appended or placed according to configured policy

Profile can choose:

- Strict known order + append unknown
- Relative anchors (“after Posts”, “before Settings”)

Relative ordering is more resilient to plugin additions.

---

# 26. Recovery / anti-lockout

Mandatory:

- Recovery/safe-mode URL/mechanism or configuration constant to bypass custom menu profiles
- current administrator cannot hide every route to WPE Menu Builder without recovery warning
- Account/Modules/diagnostics recovery route protected
- reset profile action

Menu misconfiguration must not require DB editing for ordinary recovery.

---

# 27. Login/logout/admin redirects

Separate Rules subsection, not mixed with menu ordering.

Potential triggers:

- login success
- logout
- accessing `/wp-admin/`
- role/user first landing

Destination:

- admin page
- frontend Dashboard route
- account page
- original intended URL preserved when security-sensitive flow requires

Critical:

Do not override password reset, email verification, Woo checkout, OAuth callback, admin-post/action return URLs or explicit redirect flows blindly.

Redirect loops detected/prevented.

---

# 28. Menu diagnostics/tests

Diagnostics:

- missing parent
- slug collision
- target page missing
- capability nonexistent
- profile never matches
- actor has link but destination denies
- recovery route unavailable
- redirect loop risk

Tests:

- site/network menu scopes
- top/submenu exact args
- role-specific order/visibility
- unknown third-party menu preserved
- hidden destination remains authorization-separated
- page callback unauthorized direct URL denied
- hook-suffix assets only
- redirect intended URL preservation
- recovery bypass

---

# Part C — Settings Page Builder

# 29. Settings page list

Columns:

- Name
- Menu location
- Capability
- Tabs
- Fields
- Storage
- Status
- REST
- Used by
- Health
- Actions

---

# 30. Settings page editor sections

1. General
2. Menu Placement
3. Tabs
4. Sections
5. Fields
6. Storage
7. Permissions
8. REST / Abilities
9. Validation
10. Presentation
11. Import/Export
12. Dependencies
13. Diagnostics
14. Revisions

---

# 31. Settings page identity

- name
- UUID
- machine key/page slug
- page title
- menu title
- description
- status Draft default

Page slug follows stable sanitized admin slug rules.

---

# 32. Menu placement

Modes:

- WPEssential parent menu
- WordPress Settings
- Tools
- Appearance
- Users
- Plugins only when semantically justified
- CPT submenu
- custom existing parent
- top-level
- hidden/no menu — accessible by approved route/link

Uses Custom Admin Menu registration contract; Settings Page Builder does not duplicate menu engine.

---

# 33. Page capability

Required capability/Policy.

Default for administrative settings page candidate: `manage_options` unless user chooses a safer custom capability/policy.

Important Settings API nuance:

- default Options API `options.php` handling expects strong capability and multisite has stricter behavior;
- if page is intended for non-admin roles, capability strategy must be explicit and validated through appropriate Settings API capability hook or WPE controlled save endpoint.

Displaying page and saving values both reauthorize.

---

# 34. Native Settings API vs WPE settings app mode

Two implementation adapters may exist:

## WordPress Settings API mode

Best for conventional options pages/integration with native APIs.

Maps to:

- `register_setting()`
- `add_settings_section()`
- `add_settings_field()`
- settings nonce/form machinery

## WPE application mode

React/WPE UI but server still uses registered Field/Option Data Source and capability/nonce/REST/Ability validation.

Canonical setting definitions remain shared; UI mode does not alter security/storage semantics.

---

# 35. Setting registration contract

For WordPress option-backed field/group, map current `register_setting()` concepts:

- option group
- option name
- type: string/boolean/integer/number/array/object
- label
- description
- sanitize policy
- REST exposure/schema
- default

Critical rule:

Declared `type` does not make ordinary admin option submission automatically type-safe. WPE server normalizer/sanitizer from Field Schema remains authoritative.

Array REST exposure requires item schema.

---

# 36. Settings storage strategies

Modes:

## One option per field

Pros:
- simple targeted reads/updates
- native setting registration

Cons:
- many rows/options

## Grouped option object

Pros:
- atomic group update
- namespaced configuration

Cons:
- partial update/concurrency and autoload payload need care

## Network option

Explicit multisite/network settings only.

## Custom Table / external storage

For application-scale configuration only when justified; not default.

Secrets store only Vault reference.

---

# 37. Autoload policy

For option-backed WPE settings, autoload decision is explicit platform/storage policy.

Do not autoload large configuration blobs merely because page is a Settings Page.

Candidate classification:

- required on most frontend requests → autoload may be appropriate
- admin/integration-only → no autoload
- large structured data → no autoload

Exact WordPress option API behavior/values at chosen compatibility floor requires implementation evidence.

---

# 38. Tabs

Each tab:

- UUID/key
- label
- icon optional
- description
- visibility Policy
- URL/tab parameter
- order

Deep-linkable accessible navigation.

Missing/unauthorized default tab resolves to first allowed tab without exposing hidden tab label/data.

Tabs are presentation; they do not automatically create separate option groups unless storage config says so.

---

# 39. Sections

Each section maps conceptually to Settings API section:

- id/key
- title
- description/content
- collapsible optional WPE app UI
- default expanded
- class/style token
- visibility condition
- order

Native adapter may map safe wrappers to `before_section`, `after_section`, `section_class`, but arbitrary unsanitized HTML wrappers are not normal user controls.

---

# 40. Fields

Uses Custom Fields Builder schema or a Free/core subset when platform needs fields independent of Pro editor.

Each placement references field UUID/schema; it does not clone an incompatible copy.

Native adapter accessibility maps field title/control ID through `label_for` where appropriate.

Row class is generated/scoped from approved style tokens, not arbitrary global CSS.

---

# 41. Field dependencies / conditional logic

Conditions can use:

- other settings on same page/group
- site capability/context
- module state
- external connection state

Server validates hidden dependent setting changes; hidden UI does not authorize stale/malicious input.

Option to preserve/clear hidden value follows common `clear_when_hidden` destructive warning, default preserve.

---

# 42. Save behavior

Modes:

- Save whole page/tab
- Autosave individual field — off by default; only when UX/atomicity appropriate

Standard save flow:

1. authorize
2. CSRF/nonce
3. load expected/current version if optimistic locking used
4. normalize/validate submitted fields
5. enforce conditional/policy rules
6. transaction/atomic grouped update where available
7. persist
8. emit change events
9. audit
10. show field/global errors

Partial validation failure does not silently save unrelated submitted values unless page explicitly defines partial-save behavior.

Default preference: atomic logical form/group save.

---

# 43. Unsaved changes

- dirty-state indicator
- navigation warning
- tab switch behavior preserves local edits or warns
- browser unload warning only when needed

No fake “Saved” toast before server confirmation.

---

# 44. Reset values

Levels:

- reset one field to default
- reset section
- reset tab
- reset page

Section/tab/page reset is destructive confirmation.

Preview affected keys/current/default values.

Secrets reset removes Vault reference/credential according to Secrets policy; it never reveals old secret.

---

# 45. Defaults

Default is schema value returned when explicit option absent, not necessarily a physically persisted row.

UI distinguishes:

- Using default
- Explicitly saved value equal to default

Migration changes to defaults should not overwrite explicitly saved old values automatically.

---

# 46. REST exposure

Off by default unless required.

Per setting/group:

- REST read
- REST write
- schema
- capability/Policy

Native `wp/v2/settings` exposure only when compatible with desired security/schema. WPE custom REST/Ability may be used for richer page APIs.

Secret fields never expose plaintext REST values.

---

# 47. Abilities

Potential:

- settings_page.get_schema
- settings_page.get_values
- settings_page.validate
- settings_page.update
- settings_page.reset

Typed field schema + Policy applies.

AI-generated setting changes require preview/diff for security/high-impact groups.

---

# 48. Import/export

Definition export:

- page/tabs/sections/field refs/storage/policy

Configuration values export:

- separate optional package scope
- privacy/sensitive classification
- secrets excluded/redacted

Import preview:

- field dependency mapping
- unsupported settings
- value validation
- overwrite/default/conflict choice

---

# 49. Revisions

Settings **definition** revisions always supported by Definition Repository.

Settings **value** history depends sensitivity/storage policy.

Candidate value-history modes:

- none
- audit old/new redacted
- reversible snapshots for selected non-sensitive configuration

Secrets never store plaintext history.

---

# 50. Settings diagnostics

- page/menu slug collision
- capability invalid/no actors
- field missing
- option name conflict
- REST array schema missing
- secret stored in normal option misconfiguration
- oversized autoload candidate
- grouped option too large
- network/site scope mismatch
- conditional cycle
- native Settings API capability mismatch

---

# 51. Settings acceptance tests

- each menu placement
- page direct URL unauthorized
- site/network scope
- native/app UI same storage semantics
- each register_setting type
- sanitize/validation invalid data
- array REST schema
- default vs explicit value
- grouped/individual options
- secret Vault reference
- tab/section permission
- atomic save validation failure
- reset levels
- import/export redaction
- no global settings-page assets

---

# 52. Cross-module differentiators

1. Dashboard widgets can consume Query/Listing/Diagnostics instead of bespoke widget code.
2. Admin Menu profiles never confuse hidden navigation with authorization.
3. Page hook suffix drives exact asset scoping.
4. Settings pages reuse Field Schema and Secrets Vault.
5. Native WordPress Settings API remains an adapter, not a parallel field system.
6. Menu/Settings direct URLs remain server-authorized.
7. Role/user profiles are recoverable and cannot casually lock admin out.
8. External/core items are inspected/overridden safely rather than re-owned.

---

# 53. Open decisions before implementation

- explicit user development consent;
- accepted UI/build/compatibility/Definition/CI architecture;
- exact Dashboard context/order persistence strategy without fighting WordPress per-user screen settings;
- external widget source/owner identification limits;
- Admin Menu profile conflict priority;
- exact safe menu recovery mechanism;
- Settings native-vs-WPE app adapter boundaries;
- option autoload API behavior at supported WordPress floor;
- Settings value-history retention;
- multisite network-option/page capability contract.

**Development authorization remains NO.**
