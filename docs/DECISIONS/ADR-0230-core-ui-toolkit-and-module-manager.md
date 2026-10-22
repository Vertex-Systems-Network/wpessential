# ADR-0230 — WPEssential Core UI Toolkit and Module Manager

Status: **ACCEPTED**
Date: **2026-10-22**
Approval: `GOV-OWNER-CONSENT-002` (full autonomy; decision recorded instead of asked)
Design source: Figma file `2OA6jVyDY19UmMIuC4f4Zb`, pages "WPEssential Core" (screens) and "WPEssential Core · UI Toolkit" (components)

## Context

The owner asked for a branded "WPEssential Core" admin UI, built in three steps:

1. Design it in Figma first.
2. Build it as a reusable component toolkit in the plugin.
3. Apply it to every module that is 100% complete.

The UI must show Free and Premium modules with an on/off switch. It uses the WPEssential.org identity: orange `#ec7d1a`, blue `#1f48a6` / `#2f6be0`, navy `#0b1a3d`, and the light (non-inverted) logo. Icons are flat line icons only, never 3D.

## Decision

1. **Toolkit.** The PHP components live in `WPEssential\Platform\Admin\Ui`: `Shell`, `PageHeader`, `Button`, `Badge`, `StatusPill`, `Toggle`, `Notice` and `StatTile`. Each returns escaped HTML and holds no business logic. The SCSS lives in `admin-ui/src/ui/*` and builds as the `ui` asset entry. Tokens are CSS custom properties under `.wpe-ui` that mirror the Figma `WPE.org` variables. Every Figma `Core/*` component description names its `wpe-ui-*` code class.
2. **Bridge styling.** `_wp-bridge.scss` restyles the WordPress markup the existing screens already output: panels, `widefat` tables, `.button`, `.notice` and form fields. Module screens adopt the toolkit by adding the `wpe-ui` wrap class and `Shell::topbar()`. They keep every ID, `data-*` attribute and JS hook.
3. **"100% complete" scope.** This is a README module table row marked `PASS … 100%` that has an admin screen: Taxonomies, Custom Fields, Relations, Query and Admin Columns. Status and Listings are 100% but have no admin screen, so they appear only in the module manager. CPT and Custom Tables are not 100%; they inherit only the toolkit stylesheet and the top bar is not added to them.
4. **Menu.** "WPEssential Core" owns the top-level `wpessential` menu, with Dashboard and Modules entries. The read-only Runtime Observatory moves to the "System Status" submenu (`wpessential-status`) without changing its contract. Per-module overview pages (`wpessential-module-{id}`) are kept out of the sidebar and opened from the Modules screen.
5. **Module on/off.** `ModuleToggleStore` keeps the ids of modules switched off in option `wpessential_module_toggles` (not autoloaded); every other module is on. `Plugin::boot` does not register a switched-off module with the kernel. The kernel activation policy object is not wrapped, so `RuntimeDiagnosticsSnapshot` keeps its entitlement view. Premium modules cannot be switched on without Premium: they show a locked "Premium" control, and the server refuses the toggle (`locked` notice). The toggle is an `admin-post` form with nonce and `manage_options` checks.
6. **Catalog editions.** `ModuleCatalog` mirrors the runtime `ModuleManifest` editions, and a unit test enforces this. Contract-only surfaces without a runtime module get a decided edition: Import/Export, Reset and REST API are Free; Backup, Media, Protector and Connections are Premium.

## Consequences

- One stylesheet and one set of components now cover every WPEssential admin screen. Later modules adopt the toolkit by adding the wrap class and the top bar.
- Switching off a module also degrades any module that depends on it. `ModuleRegistry` already handles this.
- The module overview pages present the option contracts as read-only. Persisting option values remains each module's own runtime work.
