# Dashboard Widgets — Market Research V1

Status: **research complete enough for a schema-valid market-audit candidate; executable certification pending shared gate**
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

`config/product/options-bank-audits/dashboard-widgets-market-ecosystem.json` maps three primary providers and two specialist providers across eight capability families. It contains four explicit extra dispositions and zero unresolved items.

The file is intentionally `MARKET_AUDIT_IN_PROGRESS`, not `MARKET_AUDITED`: the latest `main` generalized the shared market-audit schema to Surfaces 1–56, but Dashboard Widgets still lacks its dedicated executable audit contract/shared smoke wiring and shared lifecycle promotion.

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
