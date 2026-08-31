# Dashboard Widgets — Market Research V1

Status: **research complete enough for a schema-valid market-audit candidate; shared registration/certification pending integrator**
Snapshot: **2026-09-01**
Surface: **10 — `dashboard-widgets`**

## Accepted benchmark set

The repository's competitor-parity matrix names native WordPress, Ultimate Dashboard, WP Adminify and White Label CMS for this surface. Current primary/public evidence was refreshed on 2026-09-01. Specialist coverage was also checked against Dashboard Widgets Suite and Dashboard Welcome for Elementor.

### Ultimate Dashboard

Evidence:
- https://wordpress.org/plugins/ultimate-dashboard/
- https://ultimatedashboard.io/docs-category/widgets/
- https://ultimatedashboard.io/docs/user-and-role-access/
- https://ultimatedashboard.io/docs/position-priority/
- https://ultimatedashboard.io/docs/global-widget-order/
- https://ultimatedashboard.io/docs/multisite-support/
- https://ultimatedashboard.io/docs/export-import/

Material capabilities include custom icon/text/HTML/video/contact widgets, default/third-party widget cleanup, user/role targeting, native/per-user ordering plus a global-order override, multisite blueprint/exclusion/override/capability rules and import/export.

### WP Adminify

Evidence:
- https://wpadminify.com/docs/adminify/productivity/create-custom-dashboard-widget
- https://wpadminify.com/features/custom-dashboard-widget
- https://wpadminify.com/docs/adminify/productivity/create-custom-welcome-widget

Material capabilities include Normal/Side placement, Editor text/HTML, Icon, Video, Shortcode, RSS Feed and Script content types, RSS item/display controls, role targeting, reorder and welcome-widget height/dismissibility.

### White Label CMS

Evidence:
- https://wordpress.org/plugins/white-label-cms/

Material capabilities include dashboard cleanup, a custom dashboard/welcome panel, custom RSS content and builder-template-backed welcome content.

### Specialist evidence

- Dashboard Widgets Suite: https://wordpress.org/plugins/dashboard-widgets-suite/ — current dashboard widget pack covering notes, RSS, lists, system/debug information and enable/disable controls.
- Dashboard Welcome for Elementor: https://wordpress.org/plugins/dashboard-welcome-for-elementor/ — builder-template welcome content with per-role selection.

## Machine-readable candidate

`config/product/options-bank-audits/dashboard-widgets-market-ecosystem.json` maps three primary providers and two specialist providers across eight capability families. Current coverage is **17 family mappings, 7 non-applicable family cells, 47 Bank references, four explicit extra dispositions and zero unresolved items**.

The provider-neutral arbitrary-PHP rejection uses provider `ecosystem`, matching the existing shared market-audit convention rather than introducing a synthetic provider identity.

The surface-local validator now exists at:

`tests/Smoke/dashboard-widgets-market-audit-contract.php`

It verifies the exact primary/specialist roster, complete eight-family disposition matrix, real Bank references, evidence URLs, out-of-surface canonical ownership, four reviewed extra dispositions, exact coverage counters and zero unresolved. It accepts both `MARKET_AUDIT_IN_PROGRESS` and the later `MARKET_AUDITED` state without performing the promotion itself.

Exact-head Architecture Guards at `4aff0c0ae00c59447fe010ce111115bec8c9d694` include this file in the PHP syntax scan and report no syntax errors.

The audit remains `MARKET_AUDIT_IN_PROGRESS`: normal smoke registration and lifecycle truth are integrator-owned, and shared progress still declares Surface 10 as `UNSEEDED / 0`.

## Safety and owner resolution

Market parity is use-case parity, not copying unsafe mechanics.

- raw JavaScript/PHP: rejected in Surface 10; typed renderer/Ability/Safe Script alternatives only;
- remote fetch, credentials, retries, signatures and SSRF policy: Surface 23 Connections/Safe HTTP;
- structured query semantics: Surface 6 Query;
- listings/render composition: Surface 9 Listings;
- scheduling engine: Surface 18 Cron / shared Job Service;
- authorization: shared Policy / Surface 30 capability definitions;
- platform diagnostics source: Surface 31 Platform;
- global admin branding/theme: Surface 49 Admin Theme;
- browser script placement: Surface 50 Safe Script.

No proprietary implementation source was copied.

## Next gate

The integrator must register the prepared market validator in shared smoke/CI wiring, reconcile Surface 10's 123-record seed in canonical progress, execute the exact integrated head, and only then promote the audit if green. Formal Bank Review must remain blocked until both native and market audits are certified.
