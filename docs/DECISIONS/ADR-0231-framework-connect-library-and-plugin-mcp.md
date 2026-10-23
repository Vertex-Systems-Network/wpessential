# ADR-0231 — Framework Connect, Library, Dark Mode and the WPEssential MCP

Status: **ACCEPTED**
Date: **2026-10-22**
Approval: `GOV-OWNER-CONSENT-002` (full autonomy; decision recorded instead of asked)
Design source: Figma file `2OA6jVyDY19UmMIuC4f4Zb`, section "Framework · Connect & Library"
Builds on: ADR-0230

## Context

The owner asked for the "wpessential-core" features from `Vertex-Systems-Network/hi-wpessential` (`core/`) to be brought into the plugin. Those features are AI Connect, Login/Register, License, Demo Library, Docs, light/dark mode, an icon on every menu item, and the Elements screen. Elements covers Gutenberg and Elementor, and no source elements are imported. Every screen must use our toolkit and must also be designed in Figma.

The owner made five further requests:

- The product is a **Framework**, not "Core".
- The Docs link sits beside the light/dark toggle.
- The plugin has its own MCP, which AI can use to change WPEssential settings for the user.
- The UI shows a version tag.
- The version is **3.0.0** until launch.

## Decision

1. **Version.** Free and Pro are `3.0.0` until launch. The header, `WPE_VERSION`, the readme stable tag, the Pro range and the package/validator markers all use it. Frozen P006 evidence keeps its historical `0.1.0-dev` identities. The top bar shows `v{WPE_VERSION}` next to the "Framework" tag.
2. **Icons.** `Ui\Icon` is an inline, flat (stroke) SVG set using the source names plus our own. Every top-bar tab, module card and new screen uses it, and no 3D icons are used anywhere. The WP sidebar keeps its data-URI mark.
3. **Dark mode.**
   - Per-user meta `wpessential_ui_mode` holds `system`, `light` or `dark`.
   - It is rendered server-side as body classes `wpe-ui-screen wpe-mode-*`, so the page does not flash.
   - The dark tokens in `ui/_dark.scss` remap the `--wpe-*` variables.
   - In dark mode, buttons on a light accent fill switch to dark text to keep AA contrast.
4. **Screens** implement `Core\AdminScreen` and are registered through `CoreAdminController`:
   - **Demo Library** (`wpessential-demos`): the remote manifest only (`DemoCatalog`). It is cached for 12 hours, or 15 minutes when offline, and only `https` images and previews are kept. This change ships browse, filter and preview. The resumable importer and wizard follow in a later change, and the screen says so.
   - **Elements** (`wpessential-elements`): an `ElementRegistry` filter (`wpessential_elements`) with Gutenberg and Elementor tabs, saving on/off state in `wpessential_elements_state`. No elements ship.
   - **AI Connect** (`wpessential-ai`): see 6.
   - **License** (`wpessential-license`):
     - Licence adapters (custom/EDD/Woo) are ported with a daily re-check.
     - The wpessential.org account supports sign in, create account, plans, licences, "Use here", auto-renew and renewing through checkout. A checkout URL is followed only when its host matches the licence server.
     - Keys and tokens are masked in the UI and never sent to the browser. They are stored in non-autoloaded options **without encryption**, and the screen says so.
   - **Docs** (`wpessential-docs`): topic cards and a docs search, opening wpessential.org/docs.
5. **Premium source of truth.** The licence does not yet feed the kernel activation policy. `Shell::configure()` stays driven by `ProductEntitlement`, so the UI never unlocks a module the kernel would refuse to boot. Connecting the licence to the entitlement provider is follow-up work.
6. **WPEssential MCP (ARCHITECTURE §16).**
   - **Server.** `McpServer` serves JSON-RPC 2.0 at `wpessential/v1/mcp` and implements `initialize`, `ping`, `tools/list` and `tools/call`; GET returns 405.
   - **Access.** It is **off by default**. A request must come from an administrator who authenticated with one of this site's AI Connect Application Passwords.
   - **Tools.** The tools are the abilities whose descriptor allows `ExecutionChannel::Ai`, plus `site_info`. They run through `AbilityRegistry::authorize/execute`, so capability policy applies, and every call is recorded in the audit log under surface 43 (AI).
   - **New settings abilities** (surface 43, `manage_options`):

     | Kind | Abilities |
     |---|---|
     | Read | `wpessential/framework-modules/list`, `wpessential/framework-elements/list`, `wpessential/framework-license/status` (masked key), `wpessential/framework-settings/get` |
     | Write | `wpessential/framework-modules/toggle` (Premium-locked modules refused), `wpessential/framework-elements/toggle`, `wpessential/framework-settings/update` (whitelisted: `ui_mode`) |

   - **Writes.** Write tools are listed and callable only when **"Allow AI to change settings"** is on. It is off by default, and turning AI Connect off also turns it off and revokes every client. Write results return a `changed` summary (from/to) so the assistant can confirm the change with the user.
   - **What the AI can never change.** No AI ability can change AI Connect itself, the licence, or the account.
   - **Not ported.** The source's `wp_rest`, content, plugin-install and demo-import tools are not ported.
   - **Clients.** The screen creates one connection per client: Claude Code, Claude Desktop, Cursor, VS Code, Windsurf, Gemini CLI or generic. The snippet is shown once (5-minute transient) and connections can be revoked. Connecting requires HTTPS, or a `local` environment.

## Consequences

- The five tabs added to the top bar each lead to a real screen.
- AI assistants can manage WPEssential settings, but only through audited, capability-checked abilities, and writes need an explicit opt-in.
- Demo import, the licence-to-entitlement link and Elementor/Gutenberg elements remain open work.

## Amendment (2026-10-23)

The Framework abilities use the `framework-*` domains (`wpessential/framework-settings/get`, …). The first names clashed with the Pro Settings module's `wpessential/settings/get`, and Pro failed to boot. Ability names must be unique across Free and Pro. A unit test now registers the Free and Pro ability sets together to check this.
