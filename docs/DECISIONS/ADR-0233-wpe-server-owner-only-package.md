# ADR-0233 — WPE Server as an owner-only package

Status: **ACCEPTED**
Date: **2026-10-23**
Approval: `GOV-OWNER-CONSENT-002` (full autonomy; decision recorded instead of asked)
Builds on: ADR-0231, ADR-0232

## Context

The owner asked to copy the hi-wpessential "WPE Server" plugin into this repository, improve it, and align it with the plugin. It must run only on wpessential.org or vertexsystemsnetwork.com, and users of the WPEssential plugin must never receive it, "even through files".

The original runs on any WooCommerce site: its owner-only rule was a comment.

## Decision

1. **Location.** The code lives in `server/wpessential-server/` as a separate WordPress plugin: namespace `WPEssential\Server`, text domain `wpessential-server`, PHP 7.4+ and WooCommerce. It is outside `frameworks/`, so the Free/Pro autoloaders, PHPStan, PHPCS and the architecture validators do not load or scan it. It keeps its own `phpcs.xml.dist`.
2. **Host lock** (`src/HostLock.php`).
   - **Allowed hosts:** `wpessential.org` and `vertexsystemsnetwork.com`, with or without `www.`. The check runs first in the plugin bootstrap, before any class, hook, route, post type, cron job or secret.
   - **Foreign host:** activation is refused with `wp_die()`, a copy that is already active deactivates itself on `admin_init`, and nothing else is registered.
   - **Local development:** `localhost`, `*.local` and `*.test` are allowed only when `WPES_SERVER_DEV` is true and the WordPress environment type is `local`.
3. **Never shipped.** The Free/Pro builder (`tools/release/build-free-pro-distributables.php`) has a guard, `assertNoServerCode()`. It fails the build if either zip contains a `server/` or `wpessential-server` path, or any PHP file referencing `WPEssential\Server\` or `WPES_SERVER_`. The owner's package is built separately by `tools/release/build-wpe-server.php` into `artifacts/wpessential-server.zip`.
4. **Login-only licensing** (ADR-0232). New Bearer-authenticated `POST /account/connect {site}` endpoint:
   - It keeps the customer's licence already on that domain. Otherwise it takes a free slot on the best valid licence (Pro first, then most free slots; staging domains are always free).
   - It returns the owner shape, or `no_slot` (409).
   - The plugin calls it after sign-in and sign-up, and falls back to choosing the licence itself when the server lacks the route.
5. **Hardening.** The public `license/activate` and `license/deactivate` routes are now rate-limited per IP: the same limiter as sign-in, 10 per 15 minutes.
6. **Known limits, kept as follow-up work:**
   - Anyone holding a key can still remove domains from it through `license/deactivate`. Requiring an owner token there would break key-only EDD/Woo clients.
   - Token lookup still scans serialized user meta.
   - The server's settings (grace days, token lifetime and so on) are not yet in the options bank, and it exposes no AI abilities. It is a separate plugin outside the Platform ability registry.

## Consequences

- WPE Server is developed here, next to the client that uses it, but it is a different deliverable.
- Users of WPEssential can neither receive it in a package nor run a copied file on their own site.
