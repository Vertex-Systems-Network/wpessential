# REST API plugins: source and readme audit (2026-11-20)

Packages read from `downloads.wordpress.org` (latest stable):

| Plugin | Version | What it does |
|---|---|---|
| [Advanced WP REST API](https://wordpress.org/plugins/advanced-wp-rest-api/) | 1.4 | Fixed POST-only routes for user validation, posts, users and WooCommerce products, switched on in a settings panel (`apis/class-gwl-register-route-api.php`). |
| [WPGetAPI](https://wordpress.org/plugins/wpgetapi/) | 2.25.5 | Outbound client: APIs and endpoints with base URL, method, query/header/body parameters, timeout, cache time and result format; output by shortcode, template tag or block. PRO: actions on site events, chaining, tokens, nested data, XML. Add-ons: API to Posts (import and scheduled sync), OAuth 2.0. |
| [WordPress REST API Authentication](https://wordpress.org/plugins/wp-rest-api-authentication/) (miniOrange) | 5.0.1 | Inbound protection: JWT (token, refresh, revoke, expiry, custom header), API keys (universal and per user), Basic auth, OAuth 2.0, third-party provider tokens, selective route protection, logged-in only, disable REST, role rules, audit log, Postman collection. |

## Coverage against the Options Bank

Most capabilities were already planned in Surface 22 (REST API Builder) and Surface 23 (Connections): endpoint builder, OAuth/JWT adapter slot, Application Passwords, CORS, rate limits, caching, request logs, OpenAPI export, outbound HTTP with SSRF policy, secrets vault, OAuth client and webhooks.

New records (`*--rest-plugins-source-audit-2026-11.json`, with one-to-one contract projections):

| Record | Status | Source |
|---|---|---|
| `rest-api.core.access-control` | PARITY (P0) | miniOrange selective protection, logged-in only, disable REST, roles |
| `rest-api.auth.jwt-tokens` | PARITY | miniOrange JWT login / refresh / revoke |
| `rest-api.auth.user-api-keys` | PARITY | miniOrange per-user API keys (hashed, shown once) |
| `rest-api.auth.external-idp-tokens` | DEFERRED | miniOrange third-party provider tokens |
| `rest-api.presets.entity-endpoints` | PARITY | Advanced WP REST API post / user / product routes |
| `rest-api.docs.postman-collection` | PARITY (P3) | miniOrange Postman collection |
| `rest-api.safety.account-password-basic-auth` | REJECTED_UNSAFE | Basic auth with the account password; Application Passwords replace it |
| `rest-api.safety.post-only-convention` | REJECTED_UNSAFE | POST for every operation |
| `connections.output.display` | PARITY | WPGetAPI shortcode / template tag / block |
| `connections.outbound.response-format` | PARITY | WPGetAPI result formats, nested data, XML |
| `connections.outbound.cache` | PARITY | WPGetAPI cache time |
| `connections.outbound.chaining` | PARITY | WPGetAPI PRO chaining and tokens |
| `connections.outbound.event-triggers` | PARITY | WPGetAPI PRO actions |
| `connections.import.api-to-posts` | PARITY | WPGetAPI API to Posts add-on |

Totals: REST API 46 → 54 records, Connections 65 → 71.

## Build plan (roadmap step 3, after Fields and Custom Tables)

Systems used, per `config/product/system-dependency-map.json`: REST API requires Platform and Roles; Fields, Custom Tables, Relations, Query and Forms attach to it.

1. **Site REST access** (`core.access-control`): logged-in only, namespace/route allow and deny lists, role rules. Smallest piece with the most user value; no new endpoints needed.
2. **Authentication**: Application Passwords guidance, per-user API keys, then the JWT adapter (login, refresh, revoke). Secrets in the vault; tokens and keys never logged.
3. **Endpoint builder** on existing Abilities: identity, route, methods, explicit policy, inputs, response schema, pagination; presets for posts, users, WooCommerce products and CCT items.
4. **Operations**: rate limits, CORS, response cache, request log, OpenAPI and Postman export, test console.
5. **Outbound (Connections)**: request profiles with SSRF policy, response format and data path, cache, display block/shortcode, event triggers, chaining, then API to Posts with Cron sync.
