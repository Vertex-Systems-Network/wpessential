# ADR-0232 — Site Kit, the WPEssential controls, login-only licensing and admin UI polish

Status: **ACCEPTED**
Date: **2026-10-23**
Approval: `GOV-OWNER-CONSENT-002` (full autonomy; decision recorded instead of asked)
Builds on: ADR-0230, ADR-0231

## Context

The owner asked for eight things:

1. Bring in the hi-wpessential site settings as a Premium **Site Kit** module, with an on/off switch and a place in the Modules list. It must align with the options bank and the AI plan.
2. Bring in the hi-wpessential **Customizer controls** as well.
3. Rebuild the **Custom Post Types and Taxonomies** screens on the toolkit.
4. Use **AJAX** where it belongs.
5. Fix the **spacing**.
6. Add **animations** to fields, toggles, appearance and tab switches.
7. Replace licence keys with **login-only licensing**: signing in finds a subscription and activates this site, or assigns a free slot.
8. Make **AI Connect a Premium module** with its own on/off switch.

## Decision

1. **Site Kit** is a Premium module: `frameworks/Modules/SiteKit`, `ModuleManifest(id: site-kit, edition: pro)`, listed in `wpessential-pro.php`.
   - **Placement.** The surface set is fixed at 56, so Site Kit lives inside surface **49 `admin-theme`** (contract `config/product/option-contracts/admin-theme.json`, 157 atomic options).
   - **What it ports, made theme-agnostic:**
     - Every group from the hi-wpessential Site Settings panel: colours, dark mode, typography, global typography, background, buttons, forms, shape, layout, gradients, links, shadows, header & footer, accessibility and motion.
     - Custom CSS/JS snippets.
     - Opt-in SEO tags.
     - Google Fonts, downloaded locally by default.
     - Elementor kit sync.
   - **How values reach the site:** a `wp_theme_json_data_user` filter (so the editor and Elementor share them), plus a CSS layer with tablet/phone media queries.
   - **Theme-agnostic changes:**
     - Header and footer selectors cover block template parts and common theme markup.
     - Dark mode uses `data-wpe-scheme`, set before first paint.
     - Dark mode is active only when dark colours are set; an optional floating switch lets visitors change it.
   - **Saving:** one option, `wpessential_site_kit`, holding only non-default values. The module's on/off switch skips every hook.
   - **AI plan:** abilities `wpessential/site-kit/{get,schema,update,reset-group}` on the Internal, UI, REST and AI channels, owner surface 49, capability `edit_theme_options`. Writes return a `changed` summary. Snippets are never AI-writable, because JavaScript needs `unfiltered_html`.
2. **Controls.** `admin-ui/src/core/controls.ts` is a TypeScript port of `wpe-controls.js`:
   - Types: colour (palette and custom with opacity), slider (units, per-device), switch, button group/choose, select, font picker (1,771 Google Fonts plus theme fonts), font list, dimensions, typography popover, box shadow, gradient, media and background.
   - The controls are restyled on the toolkit tokens in `_controls.scss`, so they follow light and dark mode.
   - PHP renders a `.wpec` wrapper with a hidden value input, so a value is a string (JSON for composite controls), as in the source.
3. **CPT and Taxonomies** now use the builder layout:
   - A page header, a list panel beside a sectioned editor, check chips, status pills (`core/status-pill.ts`) and a sticky action bar.
   - Every ID, data hook and button name the scripts and e2e tests use is unchanged.
4. **AJAX.** One shared client, `core/ajax.ts`, uses the existing gateway.
   - In-place routes: `core.module.toggle`, `core.element.toggle`, `core.ui.mode`, `core.ai.switch` and `site-kit.*`.
   - The admin-post forms remain the no-JS fallback.
   - Navigation, sign-in and checkout still reload the page.
   - AI Connect's own switches use a dedicated handler, never an ability, so AI can never widen its own access.
5. **Spacing and motion.**
   - `--wpe-space-1..8` (a 4px base) replaces hard-coded spacing, with one 20px rhythm between blocks.
   - `_motion.scss` adds control transitions, entrance fades, tab and filter fades, card lift and a colour-mode cross-fade. All of it is off under `prefers-reduced-motion`.
   - e2e axe runs use reduced motion, so contrast is measured on the settled page.
6. **Login-only licensing.**
   - The licence-key field is gone, and the screen is now **Account**.
   - After sign-in or sign-up, `Account::connectSite()` picks one subscription: the one already on this domain; else the best active subscription with a free slot (Premium first, then most free slots); else a clear "no free slot" message.
   - "Connect this site" and "Disconnect this site" replace activate and deactivate.
   - The licence key still exists inside the client, as transport only.
7. **AI Connect** is the Premium catalog module `ai-connect`, in the Developer & AI category, with option contract `ai.json` (surface 43).
   - **While locked or switched off:** the MCP server refuses requests, AI writes are off, and the screen shows the locked or off state.
   - **Switching it back on:** the module's own toggle on the AI Connect screen.
8. **Fix to ADR-0231.** The licence still does not feed the kernel entitlement policy. Linking them needs a change to the Pro bootstrap (P006 evidence), which is follow-up work.

## Consequences

- Site settings work with any theme and are shared by the block editor and Elementor.
- The controls library is reusable by any later screen.
- Switches and saves no longer reload the page.
- Remaining follow-ups: the WPE Server port (ADR-0233), the Figma UI kit for every form control, and demo import.

## Amendment — no option may leave a screen (2026-10-23)

The owner's rule: no option the plugin supports may be removed from its screen.

An audit found that the CPT and Taxonomies rebuilds removed nothing. The CPT editor, though, had never shown most of the options `CustomPostTypeDefinitionProjector` accepts.

The CPT editor now covers all of them:
- every top-level key, including visibility and menu, rewrite and query, REST, capabilities, taxonomies and labels
- all 12 editor supports
- all 34 labels

The advanced options sit in collapsible groups. "Default" leaves a key out, so WordPress defaults still apply. `CptEditorCoverageTest` fails if any projector key, support or label loses its control, and the Taxonomies editor already covers its projector keys.
