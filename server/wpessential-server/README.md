# WPEssential Server

Runs on the seller site (wpessential.org) with WooCommerce. It is the online demo library, license server and subscription system for every site that uses the WPEssential theme. It is **not** shipped inside the theme.

## What it does

| Area | Details |
| --- | --- |
| Plans | Free, Monthly, Quarterly, Yearly, Lifetime, Enterprise, White Label — created as hidden virtual WooCommerce products on activation. Price, billing period, website limit, Pro access and white label are editable on each product (General tab). |
| Licenses | Created when a plan order is paid; key `WPE-XXXX-XXXX-XXXX-XXXX`. Status active / expired / cancelled / revoked. Refunds revoke. |
| Domain limits | Each plan allows N production websites (0 = unlimited). localhost, `*.local`, `*.test`, `staging.*` and common host staging domains are free. |
| Auto-renewal | Daily cron creates a renewal order `remind_days` before the end date. A gateway with saved cards charges it through the `wpes_charge_renewal` filter; otherwise the customer gets the WooCommerce invoice with a pay link. Paid renewals extend from the old end date. A grace period keeps sites working while the renewal is paid. |
| Reminders / expiry | Customers with auto-renew off get one reminder; licenses past end + grace expire and the customer is emailed. |
| Upgrades | Upgrade to any higher plan; the unused part of the current plan is credited at checkout. |
| Customer actions | My Account → Licenses: copy key, see websites, remove a website, renew now, auto-renew on/off (cancel), upgrade, new key. |
| Dashboard sign-in | Theme dashboards sign up / sign in with the REST API, get a token (30 days), list licenses, use a license on the site, toggle auto-renew and open a one-time signed-in checkout link. New accounts receive a Free license. |
| Demo library | Demos post type (WPE Server → Demos) with niches, pages, highlights, preview URL, thumbnail and a private zip per builder. Free packages download directly; Pro packages need an active Pro license on an activated domain and get a 15-minute signed link. Premium plugin zips use the same private folder (`plugin-<slug>.zip`). |

## REST API (`/wp-json/wpessential-server/v1`)

| Route | Auth | Purpose |
| --- | --- | --- |
| `POST license/activate` `license, domain` | — | Activate a domain |
| `POST license/deactivate` | — | Release a domain |
| `POST license/check` | — | Daily check; fails when the domain was removed in the account |
| `POST license/package` `license, domain, item, context` | — | Signed download URL (demo builder or `plugin`) |
| `GET demos` | — | Manifest read by the theme |
| `GET download` | signed | Package stream |
| `GET plans` | — | Plans for pricing tables |
| `POST account/register` `name, email, password, site` | — | New customer + Free license, returns token |
| `POST account/login` `login, password, site` | — | Returns token (rate-limited 10 / 15 min / IP) |
| `GET account/me` | Bearer | User, licenses (with keys and domains), plans |
| `POST account/license` `license_id, action, domain` | Bearer | `auto_renew_on`, `auto_renew_off`, `cancel`, `deactivate_domain`, `regenerate` |
| `POST account/checkout` `plan, license_id, mode` | Bearer | One-time link that signs in and opens checkout (`buy`, `renew`, `upgrade`) |
| `POST account/logout` | Bearer | Revoke the token |

The theme points at this server with `wpessential_core_license_config` (`api`, `endpoint`) and `wpessential_core_manifest_url`.

## Automatic releases from GitHub

`POST /release` (administrator, application password) accepts the theme zip as `theme`, the
`version`, and demo packages named `<demo>-<builder>` (unknown demos are created as drafts).
The *Build zips* workflow calls it when a `v*` tag is pushed:

1. On wpessential.org: Users → your admin → Application Passwords → add "GitHub".
2. On GitHub: Settings → Secrets and variables → Actions → add `WPES_SERVER_URL`
   (`https://wpessential.org`) and `WPES_SERVER_AUTH` (`username:application password`).
3. Release: `git tag v1.0.1 && git push origin v1.0.1`. The tag sets the theme version.

Raise `upload_max_filesize` / `post_max_size` to at least 16M; failed files are listed in `failed`.

## Hooks

`wpes_charge_renewal` (filter, return true when the gateway charged the token), `wpes_license_created`, `wpes_license_activated`, `wpes_license_action` (actions), `wpes_staging_patterns`, `wpes_rate_limit` (filters).

## Live demos (WordPress Playground)

There are no demo sites to host. The library's **Live demo** opens
[WordPress Playground](https://wordpress.org/playground/) (WordPress running in the visitor's
browser) with a blueprint from `GET /wp-json/wpessential-server/v1/playground?demo=<id>&builder=<builder>`.

The blueprint does the following:

1. Installs the theme from `GET …/theme`, which is the zip uploaded under **Settings → Live demos**.
2. Installs the demo's wordpress.org plugins.
3. Unzips the same demo package customers import. Pro packages use a short-lived signed link.
4. Runs the WPEssential importer.

Clicks, hovers, carts and page visits all happen on a real (throw-away) site.

Setup:

- Upload the theme zip after every release.
- Keep **Live demos** ticked.

The `/playground`, `/theme` and `/download` responses send `Access-Control-Allow-Origin: *`,
so Playground can fetch them.

The demo manifest exposes the links as `playground: { gutenberg: url, elementor: url }`.

## Owner licence

For the site owner's own websites: every free and Pro feature, unlimited websites, never expires.

1. Sign in to wpessential.org as a site administrator. Shop managers do not see this option.
2. Go to **WPEssential → Add license**, enter your own account email and choose **Owner**.
3. On your website, open **WPEssential → Account** and sign in. The Owner licence is picked up like any other licence.

Why it stays private:
- The Owner plan is a private product. It is not listed in the plans API, the shop or upgrades, and it cannot be bought.
- The plugin has no special code for it: the licence just reports `pro: true`.
- Every Owner licence issued is logged under the `license.owner_issued` event.

To stop it, revoke the licence on the server.
