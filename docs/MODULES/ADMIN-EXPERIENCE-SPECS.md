# WPEssential — Admin & Experience Detailed Specifications

Status: **Phase 0 — specified with adapter/design-system blockers**

Applies `COMMON-OPTION-CONTRACTS.md` and resolves Dashboard Widgets Manager, Custom Admin Menu Builder, Settings Page Builder, Dashboard Builder and Builder Widgets Builder.

---

# 10. Dashboard Widgets Manager — Pro

## Existing widget inventory
WPEssential discovers registered WordPress Dashboard widgets and records runtime owner hints where possible. It does not claim ownership of third-party widgets.

Per discovered widget options:
- visibility override: Inherit / Show / Hide;
- audience rule;
- order/column preference only where WordPress dashboard layout allows deterministic control;
- restore override.

Hiding never changes the destination capability of links/actions inside the widget.

## Presets
Preset fields:
- name/key/status;
- audience conditions;
- priority;
- widget visibility/order definitions;
- custom widgets attached.

If multiple presets match, highest explicit priority wins, then stable deterministic tie rule; UI shows conflict.

## Custom widget common fields
- title required;
- type required;
- description optional;
- status Draft;
- audience conditions;
- width/layout hint;
- cache settings;
- start/end schedule;
- dismissible false by default.

## Rich text / banner
- sanitized editor;
- heading/body;
- CTA label + validated URL/action;
- icon/image optional;
- severity/style presentation only;
- start/end;
- dismiss state per user default.

## Stat/query card
- Query UUID;
- aggregate/value projection;
- label;
- number formatter;
- comparison optional only when query provides trustworthy comparison context;
- refresh/caching.

## List/table
Uses Listing/Query renderer; max visible rows default 5/10 depending widget type; full-view link optional.

## RSS/remote HTTP
- disabled until URL configured;
- SSRF-safe connection rules;
- timeout/size cap;
- cache required to avoid admin request dependency on remote service;
- failure shows stale-cache timestamp or safe error, not blocks dashboard.

## Iframe
Off by default and marked advanced.
- https required by default;
- host allowlist;
- sandbox attributes restrictive default;
- explicit allow capabilities;
- fixed/responsive height;
- no secrets in URL.

## Dismissal
- per-user by default;
- global dismiss only privileged and explicit;
- optional reset dismissals action;
- expiry can make widget reappear only if definition says so.

## Assets
Custom widget assets enqueue only on Dashboard when at least one matching WPE widget will render.

---

# 11. Custom Admin Menu Builder — Pro

## Principle
Menu presentation and authorization are separate. Menu rules never grant destination access.

## Menu profile
- name/key/status;
- audience Conditions;
- priority;
- default fallback profile optional.

## Discovered menu item reference
Store stable runtime identifier/slug/parent where possible. If plugin removes item, show Missing dependency rather than creating dead clone.

## Visibility
Modes:
- inherit;
- force hide for matching audience;
- force show only if destination itself remains authorized.

`force show` cannot bypass capability callback; if destination unavailable, diagnostic warning.

## Rename
Plain text label only. Blank invalid. Original label retained for restore.

## Icon
Dashicon/sanitized registered icon for top-level custom entries. Submenu icon ignored where WordPress does not support it.

## Ordering
Drag/drop writes logical order. Numeric positions are compiled at runtime with conflict strategy; user does not need to know WP float menu positions unless advanced diagnostics.

## Move
Moving third-party item between parents only supported where runtime hooks can do it without breaking URL/capability; unsupported item shows disabled move action.

## Custom item
Types:
- WPEssential screen;
- existing internal admin URL;
- frontend internal URL;
- validated external URL.

Fields:
- label;
- URL/route;
- capability requirement;
- audience visibility;
- icon/top-level;
- position/parent.

No JavaScript/data URL schemes.

## Group/separator
Presentation only; keyboard navigation/semantics preserved.

## Badge
Optional Query/token with max length and low-cost requirement. Failure hides badge rather than menu item.

## Redirect rules
Login/logout redirect uses separate rule set:
- audience;
- target;
- priority;
- preserve intended destination toggle;
- safe fallback;
- loop detection.

## Recovery
- query-param/admin recovery mode requiring capability/nonce;
- option/constant to temporarily bypass menu profile if misconfiguration hides access;
- Role Manager anti-lockout remains separate.

---

# 12. Settings Page Builder — Pro

## Page definition
- title required;
- slug stable unique WPE admin route;
- status Draft;
- menu title defaults title;
- icon default generic settings icon;
- description optional.

## Placement
- top-level or existing parent menu;
- menu position optional;
- missing parent causes degraded state with fallback under WPEssential, not inaccessible page;
- network admin disabled by default and only available when module/storage supports multisite.

## Navigation layouts
- tabs default when >1 section group and user selects tabs;
- vertical nav optional;
- simple stacked sections universal fallback.

Layout never changes storage keys.

## Section
- title;
- key;
- description;
- order;
- condition;
- capability override optional.

## Fields
Uses linked Custom Field group or inline schema owned by Settings page. Inline fields still use Field Registry types/validation; there is no separate settings-field implementation.

## Storage strategies
1. Single option array — default for ordinary page.
2. Per-field options — advanced interoperability.
3. Network option — network mode.
4. Custom registered storage adapter.

`autoload` default false for WPE page arrays unless values are required on most requests; UI explains memory impact. Secrets always Vault.

## Save scope
Default save current page/tab atomically. Validation failure saves none of the scope unless field-specific partial-save behavior is explicitly designed; default no partial settings save.

## Reset
- reset field: Level 1;
- reset section: Level 1/2 depending impact;
- reset page: Level 2 with revision restore available.

Reset returns defaults, not deletion of shared Vault secrets without explicit secret cleanup.

## Frontend exposure
Off by default. To expose selected setting through shortcode/block/API:
- field must explicitly allow read;
- renderer output sanitized;
- secrets cannot be exposed;
- capability/public policy explicit.

## Dynamic options
Query result bounded; failed Query shows validation/degraded state while preserving stored current value if still valid string/reference.

---

# 13. Dashboard Builder — Pro

## Concept
Frontend application shell/portal, not a replacement for general marketing page builders.

## Dashboard definition
- name/key/status;
- audience/access policy required before Publish;
- public/guest dashboard allowed only explicit;
- default route required before Publish.

## Navigation hierarchy
Data maximum 5 levels. UX warnings:
- >2 levels: warning;
- >3 levels: strong warning;
- 5 accepted only for advanced use.

Each route:
- stable UUID;
- label;
- slug/path segment;
- parent;
- order;
- icon;
- badge optional;
- visibility condition;
- access policy;
- content source.

Sibling slugs unique. Full route collision rejected.

## Visibility vs access
A hidden nav route can still be direct-linked; route access policy is always server checked. Visibility rule does not secure route.

## Guest/auth
Dashboard-level default login required = true. Route may loosen only if Dashboard allows guest routes.

Unauthenticated outcomes:
- login redirect with safe return path;
- custom public fallback if configured.

Unauthorized authenticated outcomes:
- 403 component by default;
- route-specific safe redirect optional.

## Content adapters
- WPE component/listing;
- Gutenberg reusable/template content;
- shortcode;
- Elementor template;
- Bricks template;
- WPBakery/Visual Composer template;
- registered renderer.

Adapter must declare whether content is server-rendered, cacheable, requires global context or assets.

## Shell
Defaults:
- header on;
- sidebar on desktop;
- responsive drawer mobile;
- collapsible sidebar true;
- content max width uses design-system application token;
- account/logout menu enabled.

Brand settings use design tokens, not unrestricted CSS text.

## Breadcrumbs
Enabled by default for nested routes >1; route can hide. Must reflect accessible hierarchy, not clickable link to unauthorized parent.

## Badges
Query/token bounded and permission-aware. Badge failure does not fail route.

## Error states
Per dashboard reusable components:
- 403;
- 404;
- error;
- empty;
- maintenance/dependency unavailable.

## SEO
Private/member dashboards default `noindex` through appropriate hooks/meta where feasible. Public dashboards can opt into indexability. Security never relies on noindex.

## Assets
Shell bundle only on dashboard routes. Builder adapter assets only on route using adapter.

---

# 16. Builder Widgets Builder — Pro

## Model
One Shared Component Blueprint can map to several builders, but each adapter exposes support matrix. A blueprint is not promised to represent proprietary features absent from the canonical schema.

## Blueprint identity
- name/key/category required;
- icon optional;
- description;
- status Draft;
- builder targets multi-select.

## Controls
Every control uses Field-like schema:
- key/label/type;
- default;
- required;
- options;
- responsive flag;
- dynamic binding allowed;
- visibility condition;
- group/tab.

Machine key becomes immutable after Publish because builder documents may reference it.

## Dynamic binding
Value sources:
- current entity field;
- token;
- Query single value;
- relation;
- context provider.

No arbitrary PHP callback selected by text.

## Render schema
Approved nodes:
- element/container;
- text;
- attribute;
- dynamic token;
- condition;
- repeat over bounded collection;
- child slot where adapter supports.

Every dynamic output has escaping context: HTML text, attribute, URL, class/token.

Raw HTML block is advanced, capability-gated, sanitized, and cannot include scripts.

## Style controls
Canonical categories:
- typography;
- color/background;
- spacing;
- border/radius;
- size/min/max;
- alignment/flex/grid subset;
- visibility/responsive.

Values compile to scoped generated CSS or builder-native controls. Arbitrary CSS textarea is not required for ordinary operation and is excluded from v1 canonical blueprint.

## Responsive
Canonical breakpoints map to builder adapter breakpoints. Adapter shows mismatch when target builder has custom breakpoint set that cannot map one-to-one.

## Assets
Blueprint may reference registered asset entries, not remote arbitrary JS URL.
- frontend/editor context;
- dependency handles;
- enqueue only when component instance renders/editor opens relevant widget.

## Gutenberg
- block name derived from immutable key;
- `block.json`-equivalent metadata generated/registered through supported WP APIs;
- attributes schema from controls;
- dynamic/server render preferred when data/policy involved;
- static save only for safe deterministic content.

## Elementor
- category selection;
- controls map to supported Elementor controls;
- dynamic tag compatibility where registered;
- server PHP widget render generated from approved renderer, not eval.

## Bricks
Same canonical blueprint, adapter-specific control mapping and renderer registration.

## WPBakery / Visual Composer
Map to shortcode/element parameter API as documented. Unsupported responsive/dynamic feature displays warning before enabling adapter.

## Preview
- sample context entity;
- responsive widths;
- missing dynamic data states;
- output sanitization warnings;
- adapter comparison.

## Delete
Blueprint with existing builder-document usage cannot be silently deleted. Used-by graph warns and archive recommended.

---

# Admin & Experience specification status

These modules are **Specified at Phase 0 behavioral level**. Builder-specific APIs and Untitled/WordPress design-system choices remain subject to the accepted UI/toolchain ADR and adapter compatibility tests.
