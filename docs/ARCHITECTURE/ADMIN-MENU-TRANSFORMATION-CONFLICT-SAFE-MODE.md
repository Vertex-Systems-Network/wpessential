# WPEssential — Admin Menu Transformation, Conflict & Safe-Mode Model

Status: **Phase 0 paper architecture / no implementation authorized**  
Related: Custom Admin Menu exhaustive spec, Policy, Role Manager, Settings/Dashboard pages, ADR-0014.

## 1. Product boundary

Custom Admin Menu Builder transforms the **wp-admin navigation presentation** and can register WPE-owned pages/links.

It does not make a screen secure merely by hiding/removing its menu item.

Three distinct concepts:
1. menu visibility/order/label;
2. page registration/existence;
3. capability/Policy authorization.

Never conflate them.

## 2. Runtime-discovery model

WordPress/plugins register admin menus dynamically.

WPE therefore stores transformation rules, not a brittle full cloned `$menu` snapshot as source of truth.

Runtime flow candidate:
1. WordPress/core/plugins register menus;
2. WPE builds normalized discovered menu registry;
3. match published WPE rules to discovered entries;
4. validate capability/scope/recovery constraints;
5. apply label/icon/order/visibility/group/add transformations;
6. expose health/conflict diagnostics.

## 3. Discovered menu identity

Normalized entry fields candidate:
- top/submenu;
- menu/page slug;
- parent slug;
- hook suffix where available;
- callback/page registration hint;
- required capability;
- label/menu title;
- icon/position;
- source/owner heuristic;
- current URL;
- network/site admin context.

Stable matching prefers page/menu slug + parent/context. Numeric array position is never canonical identity.

## 4. Rule types

### Rename presentation
- menu label;
- optional page heading only if owning adapter supports; renaming sidebar label alone does not mutate plugin page content.

### Reorder
- absolute group/order token;
- before/after stable target;
- top-level/submenu order.

### Hide
- always;
- role/capability/Policy condition;
- user-specific profile where accepted.

Hide affects navigation presentation only.

### Move
Move existing submenu/top-level only when WordPress screen semantics remain valid. If moving changes plugin expectations, mark unsupported/degraded rather than force array surgery.

### Add WPE page
Register WPE-owned Settings/Dashboard/utility page with explicit capability and renderer.

### Add link
Candidate types:
- existing admin screen;
- WPE page;
- WordPress content/edit URL resolver;
- external URL advanced/explicit.

External links require validated scheme/URL and clear external behavior; they do not receive WordPress page callback authority.

### Separator/group
Presentation-only grouping where WP admin menu model permits.

## 5. Native menu depth

Native WordPress admin sidebar is fundamentally top-level + submenu.

WPE Custom Admin Menu Builder does not pretend arbitrary 5-level native WordPress menu nesting exists.

Deeper application navigation belongs inside WPE Dashboard/custom admin page UI, not hacks to core sidebar.

## 6. Ordering

When WordPress custom ordering is used, WPE composes with current `custom_menu_order` and `menu_order` behavior rather than relying only on numeric `position` values.

Rule order semantics:
- explicit WPE before/after constraints;
- fixed WPE-owned parent grouping;
- preserve relative order of unmatched entries;
- unresolved target produces warning and deterministic fallback.

Do not drop menu items simply because another plugin registered them after configuration was created.

## 7. Conflict model

Potential conflicts:
- target menu missing/renamed;
- same slug from different context/source;
- two WPE rules target same property;
- another plugin reorders/renames later;
- role/profile rules disagree;
- network admin vs site admin mismatch;
- plugin update changes parent/page slug.

Conflict status:
- healthy;
- target missing;
- ambiguous match;
- overwritten by later hook;
- invalid destination;
- authorization mismatch;
- recovery-critical.

No silent arbitrary winner for ambiguous external target.

## 8. Rule precedence

Candidate precedence within WPE:
1. hard recovery/safety invariants;
2. explicit user-specific rule if product allows;
3. specific capability/Policy rule;
4. role/profile-specific rule;
5. shared/global rule;
6. original WordPress menu.

At same specificity, explicit priority/order + deterministic definition UUID tie-break; diagnostics show winning rule.

## 9. Capability preservation

When adding WPE page, registration has explicit capability/Policy.

When hiding/reordering external page, WPE does not rewrite its required capability unless a certified integration explicitly owns that capability change.

Role/Capability Manager owns authorization changes.

## 10. Removing external menu item

Terminology in UI:
- **Hide menu item** — remove from sidebar for matched audience.
- **Disable screen** — separate security/product operation only if owning module/Protector/certified adapter can actually deny access.

WPE must not label `remove_menu_page()`-style presentation removal as screen disabled.

## 11. WPE parent menu invariant

WPEssential modules remain under one canonical WPEssential parent according to platform IA.

Custom Admin Menu Builder may:
- rename WPE parent label presentation if allowed;
- reorder it;
- control child visibility according to module access;
- add user-created WPE pages beneath it.

It cannot hide every WPE recovery/settings entry from all authorized recovery principals without explicit safe-mode/recovery protection.

## 12. Recovery-critical pages

Examples:
- WPE Home/Modules;
- Role/Capability recovery;
- Admin Menu Builder safe-mode/diagnostics;
- Account/license only where relevant;
- core WordPress Users/Plugins/Settings according to capability context.

WPE warns/blocks rules that would eliminate all practical navigation paths to required recovery functions for current scope, while direct URL remains authorization-controlled.

## 13. Safe mode

Candidate config constant: `WPESSENTIAL_SAFE_ADMIN_MENU` or shared WPE recovery mode.

When active:
- skip custom menu transformations;
- show original WordPress/plugin menu registration;
- keep WPE diagnostics/recovery page if it can be safely registered;
- no WordPress capability bypass;
- no plugin page activation that was otherwise disabled;
- visibly indicate safe mode to authorized admins.

Purpose: recover from broken navigation rules, not bypass security.

## 14. Preview

Preview modes:
- current user;
- selected role/capability simulation;
- multisite site/network context;
- safe before/after tree diff.

Simulation does not impersonate target user or grant access.

Show:
- original tree;
- transformed tree;
- hidden items;
- missing/conflicting rules;
- each item's original capability;
- WPE winning rule.

## 15. Adding custom WPE admin pages

Page definition can reference:
- Settings Page;
- admin Dashboard/utility component;
- documentation/help page;
- registered Component Blueprint/admin renderer.

Page has:
- title/menu label;
- slug;
- parent;
- capability/Policy;
- icon/order;
- renderer reference;
- asset dependencies.

No arbitrary PHP callback typed into UI.

## 16. Per-user personalization

Personal menu preferences, if supported, are separate from administrator-defined policy.

User may be allowed to:
- collapse groups;
- favorite/shortcut allowed pages;
- reorder within permitted personal zone.

Personal preference cannot reveal or authorize admin screens hidden/denied by policy.

## 17. Import/export

Menu rules export stable match descriptors, not raw numeric `$menu` array indexes.

Import dry-run reports:
- matched target;
- missing target;
- ambiguous match;
- different capability/source;
- network/site mismatch;
- potential recovery lockout.

Unmatched external rules import disabled/deferred rather than targeting nearest-looking page.

## 18. Asset loading

Admin Menu runtime transformation should require minimal/no React bundle on every admin page.

Builder/editor assets load only on WPE Admin Menu screens.

Menu runtime ideally uses small server-side transformation logic; exact implementation pending.

## 19. Failure/degraded behavior

- rule store corrupt → fall back original WP menu;
- external target missing → ignore that rule + diagnostics;
- WPE module disabled → its menu child absent; definition remains;
- capability rule invalid → fail safe/no unauthorized link exposure;
- safe mode → all transformations bypassed;
- Pro expiry → deployed menu transformation can continue if safe under ADR-0007, editing read-only/restricted.

A menu-rule failure must not take down wp-admin.

## 20. Future executable evidence — NOT AUTHORIZED

After explicit consent:
- hook/priority ordering with core + Woo + common plugins;
- `custom_menu_order` composition;
- site/network admin;
- missing/renamed plugin pages;
- role/user conditional trees;
- safe mode;
- recovery-page invariant;
- direct URL authorization unaffected;
- performance on every admin request;
- plugin update conflicts.

## Paper recommendation

Accept **runtime-discovered menu registry + stable transformation rules + fail-open-to-original-navigation safe mode**, while keeping screen authorization separate from menu visibility.