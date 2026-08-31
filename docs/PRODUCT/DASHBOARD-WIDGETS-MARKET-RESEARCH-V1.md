# Dashboard Widgets — Market Research V1

Status: **research complete enough for seed; formal market-audit certification blocked by shared schema**
Snapshot: **2026-09-01**
Surface: **10 — `dashboard-widgets`**

## Accepted benchmark set

The repository's competitor-parity matrix names native WordPress, Ultimate Dashboard, WP Adminify and White Label CMS for this surface. Current primary/public evidence was refreshed on 2026-09-01.

### Ultimate Dashboard

Evidence:
- https://ultimatedashboard.io/docs/
- https://ultimatedashboard.io/docs/user-and-role-access/
- https://ultimatedashboard.io/docs/global-widget-order/
- https://ultimatedashboard.io/docs/multisite-support/

Material capabilities:
- custom Dashboard widgets;
- user and role access targeting;
- native per-user widget order plus a Pro global-order override based on a selected user's order;
- multisite blueprint site, excluded sites, subsite override precedence, global widget order and capability controls.

### WP Adminify

Evidence:
- https://wpadminify.com/docs/adminify/productivity/create-custom-dashboard-widget
- https://wpadminify.com/features/custom-dashboard-widget
- https://wpadminify.com/docs/adminify/productivity

Material capabilities:
- Normal/Side placement;
- Editor text/HTML, Icon, Video, Shortcode, RSS Feed and Script content types;
- RSS item count plus excerpt/date/author display controls;
- role-based widget settings;
- removal/management of default WordPress widgets.

### White Label CMS

Evidence:
- https://wordpress.org/plugins/white-label-cms/

Material capabilities:
- clear/default-dashboard cleanup;
- custom dashboard/welcome panel;
- custom RSS feed;
- welcome content backed by supported builder templates;
- client-focused dashboard customization.

## Capability-family mapping

| Family | Native WP | Ultimate Dashboard | WP Adminify | White Label CMS | WPE disposition |
| --- | --- | --- | --- | --- | --- |
| registered widget inventory/hide | strong | strong | strong | strong | Surface 10 |
| custom text/info | callback provider | strong | strong | strong | Surface 10 renderer |
| video/icon/links | provider | strong | strong | partial | Surface 10 typed widget types |
| shortcode/block/builder output | provider | supported patterns | shortcode | builder welcome | registered Renderer/provider only |
| RSS/remote | native examples/caching | provider-dependent | strong | RSS | Surface 23 transport + Surface 10 presentation |
| user/role visibility | native user hide only | strong | role-based | client-focused | presentation rule; Policy remains authority |
| per-user order | native | strong | native drag | native behavior | preserve native state |
| global/role preset order | limited | strong | product-level | product-level | WPE preset semantics |
| multisite blueprint | network hook only | strong | product-level | product-level | site/network preset semantics |
| import/export | no first-class widget package | product feature | product-level | product settings | WPE definition portability |
| raw script | no authored script type | not required | yes | not benchmark floor | reject in Surface 10; Surface 50 owns safe scripts |

## Safety and owner resolution

Market parity is use-case parity, not copying unsafe mechanics.

- raw JavaScript/PHP: rejected in this Bank; typed renderer/Ability/Safe Script alternatives only;
- remote fetch, credentials, retries, signatures, SSRF: Surface 23;
- structured query semantics: Surface 6;
- listings/render composition: Surface 9;
- scheduling engine: Surface 18/Job Service;
- authorization: shared Policy / Surface 30 capability definitions;
- global admin branding/theme: Surface 49;
- generic experience placement: Surface 38.

## Formal certification blocker

`config/product/options-bank-market-audit.schema.json` is currently hard-coded to Surface 3 (`id = 3`, `key = fields`). A Surface 10 market audit cannot truthfully satisfy that shared schema.

This worker therefore does **not** create a fake `MARKET_AUDITED` artifact or weaken the schema. The integrator must generalize the shared market-audit schema/validator (or introduce an approved surface-specific contract) before formal Dashboard Widgets market certification.

No proprietary implementation source was copied.
