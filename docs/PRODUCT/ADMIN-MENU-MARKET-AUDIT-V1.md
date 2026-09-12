# Admin Menu — Market Audit V1

Status: `RESEARCH_COMPLETE / SUPERVISOR_INTEGRATION_PENDING`

Surface: 11 — Admin Menu  
Snapshot: 2026-09-12  
Authorized parent: #713  
Worker issue: #714  
Base / queue-authorized main: `54e4e70095f74a6ebff15da83cc2bbf3d447a49c`  
Worker branch: `agent/admin-menu-market-audit-v1`

## 1. Scope and gate

This audit reconciles the 16-record `NATIVE_AUDITED` Admin Menu Options Bank against current WordPress admin-menu customization products. It is evidence only. It does **not** mutate `config/product/options-bank/admin-menu.json`, promote `MARKET_AUDITED`, authorize role/capability mutation, or claim runtime/product parity.

Canonical boundary retained: this surface owns presentation/composition of registered admin-menu and Admin Bar entries. Authorization truth remains owned by Roles/Policy; hiding a menu item must never be treated as revoking access to the underlying operation.

## 2. Current market evidence

### E1 — Admin Menu Editor Pro
Official evidence:
- https://adminmenueditor.com/home/
- https://adminmenueditor.com/free-version-docs/how-to-add-new-menu/
- https://adminmenueditor.com/notes/

Verified capabilities: drag/drop order; rename; URL/icon editing; create custom items; move/reparent submenus; show/hide by roles, capabilities or user names; import/export configuration; multisite support; per-user restrictions; emergency reset. Its own documentation also distinguishes menu visibility from the authorization enforced by the target admin page.

### E2 — WP Adminify Admin Menu Editor
Official evidence:
- https://wpadminify.com/features/admin-menu-editor
- https://wpadminify.com/docs/adminify/admin-menu/admin-menu-editor

Verified capabilities as of Aug 2026: reorder/rename; URL/icon changes; role/user visibility rules; separators; submenu customization; custom menu items; grouped sections; Admin Bar editing; styling. Documentation explicitly states that hiding an item does not itself block direct access.

### E3 — Ultimate Dashboard PRO Admin Menu Editor
Official evidence:
- https://ultimatedashboard.io/docs/admin-menu-editor/

Verified capabilities: reorder main/submenu items; add items; per-user/per-role hiding; rename/icon changes; dynamic placeholder tags; WordPress Multisite support. Documentation explicitly leaves WordPress capabilities untouched and treats Super Admin separately.

## 3. Capability-family findings

1. **Transformations are established market parity.** Rename, order, reparenting, icon/URL changes, main/submenu composition and visibility rules are mainstream.
2. **Per-role/per-user presentation is established, but authorization is separate.** This validates the current WPE split between presentation assignment and canonical role/capability truth.
3. **Custom links and separators/groups are established.** WPE should retain safe registered-route / validated-URL boundaries rather than copying arbitrary executable menu callbacks.
4. **Admin Bar editing is competitive parity.** WP Adminify explicitly covers the toolbar; WordPress native Admin Bar remains the substrate.
5. **Recovery/import-export exists in market products.** Admin Menu Editor Pro exposes import/export and an administrator recovery reset. This supports WPE recovery/conflict diagnostics but does not justify unsafe force-overwrite behavior.
6. **Named menu profiles and safe preview remain WPE-level composition.** Competitors support per-role/per-user configurations, but the reviewed official evidence does not establish a portable named-profile + non-impersonating preview contract as a universal market primitive.

## 4. Record-by-record reconciliation

| Bank record | Market disposition | Evidence / later Supervisor action |
|---|---|---|
| `admin-menu.transform.target` | KEEP native | E1–E3 operate on existing menu/submenu targets; retain missing-target diagnostics. |
| `admin-menu.transform.rename` | MARKET_EVIDENCED | E1/E2/E3 all expose rename. Later Bank integration may classify/adopt as market parity. |
| `admin-menu.transform.order` | KEEP native + market parity | E1/E2/E3 confirm drag/drop ordering. |
| `admin-menu.transform.parent` | KEEP native + market parity | E1 explicitly moves submenu/top-level items; E2/E3 expose submenu composition. |
| `admin-menu.transform.visibility` | KEEP native + market parity | E1–E3 all expose hide/show presentation behavior. |
| `admin-menu.transform.role-capability` | MARKET_EVIDENCED_WITH_OWNER_BOUNDARY | E1/E2/E3 expose role/capability/user targeting. Keep role/capability truth delegated to Surface 30/Policy. |
| `admin-menu.transform.network` | KEEP native + market parity | E1/E3 document multisite; E3 documents Super Admin caveat. |
| `admin-menu.custom.route` | MARKET_EVIDENCED_WITH_SAFETY_BOUNDARY | Custom menu links/items are common; WPE must expose only registered internal routes. |
| `admin-menu.custom.safe-url` | MARKET_EVIDENCED_WITH_SAFETY_BOUNDARY | E1/E2 expose internal/external custom URLs; WPE must validate scheme/host/target. |
| `admin-menu.custom.group` | MARKET_EVIDENCED | E2 exposes separators/grouped sections; E1/E3 cover submenu composition. |
| `admin-menu.profile.definition` | KEEP_WPE_COMPOSITION | Per-role/user configurations exist, but named portable profile semantics remain a WPE abstraction. |
| `admin-menu.profile.assignment` | MARKET_EVIDENCED_WITH_OWNER_BOUNDARY | E1–E3 support role/user-specific menus; site/network assignment remains bounded references only. |
| `admin-menu.profile.preview` | KEEP_WPE_EXCEED | No reviewed provider establishes a canonical non-impersonating preview contract; retain diagnostic-preview design without account mutation. |
| `admin-menu.admin-bar.transform` | KEEP native + market parity | E2/WP Adminify exposes Admin Bar editing; preserve native node graph and safe metadata. |
| `admin-menu.safety.recovery` | MARKET_EVIDENCED / WPE_HARDENED | E1 exposes emergency reset and import/export. WPE should add deterministic missing-target/conflict/recovery diagnostics rather than arbitrary reset execution. |
| `admin-menu.safety.unsafe-url` | KEEP `REJECTED_UNSAFE` | Market custom-URL flexibility is not evidence to accept unsafe schemes. |

Coverage: **16 / 16 current records reconciled**.  
Unresolved research dispositions: **0**.  
Bank mutation performed by this worker: **0**.

## 5. WPE-exceed / safety conclusions

- Preserve the stronger WPE guarantee that menu visibility is presentation only; effective authorization comes from Policy/Roles and the target Ability/page gate.
- Prefer registered internal route identities over arbitrary callbacks or executable menu configuration.
- Validate external/admin URLs and keep `javascript:`, executable HTML and unsafe schemes rejected.
- Named profiles, deterministic conflict diagnostics, safe preview and explicit site/network assignment can exceed common competitor UX without duplicating permission storage.

## 6. Supervisor integration requirements

A later Supervisor-only integration may:
1. add E1–E3 to `snapshot.market_sources` with verified capability notes;
2. promote the evidenced `UNREVIEWED` families to market classifications/adoption where policy permits;
3. retain `profile.definition` / `profile.preview` as WPE composition/differentiation unless stronger market evidence is added;
4. keep `admin-menu.safety.unsafe-url` rejected;
5. preserve current native provenance and the Roles/Policy owner boundary;
6. update central progress to `MARKET_AUDITED` only after exact-head Bank integration, count validation and applicable CI are green.

This document does not claim `BANK_REVIEWED`, implementation readiness, runtime certification, product parity, deployment or release.