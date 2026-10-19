# Protector — Native WordPress Audit V1

Surface: **27 / Protector**
Issue: **#1456**
Planning parent: **#593 / merged PR #606**
Seed: `config/product/options-bank/protector.json` — **225 records**

## Decision

**NATIVE AUDIT EVIDENCE COMPLETE; WORDPRESS CORE PROVIDES LOGIN/LOST-PASSWORD/LOGOUT URLS, AUTHENTICATE AND LOGIN-FAILED HOOKS, CAPABILITY/SUPER ADMIN CHECKS, NONCES, SAFE REDIRECTS, REQUEST HOOKS, REST AUTHENTICATION/PRE-DISPATCH FILTERS, HEADER EMISSION, PASSWORD HASHING, AUTH COOKIES/SESSION TOKENS, FATAL-ERROR RECOVERY MODE, IP VALIDATION, PRIVACY ERASERS, CRON AND MULTISITE PRIMITIVES BUT NO SITE GATE, RULE ENGINE, FAILED-LOGIN LIMITING, SHARED RATE-LIMIT SERVICE, TRUSTED-PROXY CLIENT-IP RESOLUTION, SECURITY-HEADER PRESETS, LOCKOUT PREFLIGHT OR PRIVACY-AWARE SECURITY LOG.** Native hooks are substrate only and never substitute for Protector preview, recovery or Policy composition. No request, login or header enforcement occurs here.

## Current native sources

- wp_login_url() — https://developer.wordpress.org/reference/functions/wp_login_url/
- wp_lostpassword_url() — https://developer.wordpress.org/reference/functions/wp_lostpassword_url/
- wp_logout_url() — https://developer.wordpress.org/reference/functions/wp_logout_url/
- wp_authenticate() — https://developer.wordpress.org/reference/functions/wp_authenticate/
- authenticate — https://developer.wordpress.org/reference/hooks/authenticate/
- wp_login_failed — https://developer.wordpress.org/reference/hooks/wp_login_failed/
- login_init — https://developer.wordpress.org/reference/hooks/login_init/
- wp_safe_redirect() — https://developer.wordpress.org/reference/functions/wp_safe_redirect/
- wp_validate_redirect() — https://developer.wordpress.org/reference/functions/wp_validate_redirect/
- current_user_can() — https://developer.wordpress.org/reference/functions/current_user_can/
- is_super_admin() — https://developer.wordpress.org/reference/functions/is_super_admin/
- wp_verify_nonce() — https://developer.wordpress.org/reference/functions/wp_verify_nonce/
- is_ssl() — https://developer.wordpress.org/reference/functions/is_ssl/
- nocache_headers() — https://developer.wordpress.org/reference/functions/nocache_headers/
- send_headers — https://developer.wordpress.org/reference/hooks/send_headers/
- wp_headers — https://developer.wordpress.org/reference/hooks/wp_headers/
- status_header() — https://developer.wordpress.org/reference/functions/status_header/
- rest_authentication_errors — https://developer.wordpress.org/reference/hooks/rest_authentication_errors/
- rest_pre_dispatch — https://developer.wordpress.org/reference/hooks/rest_pre_dispatch/
- wp_doing_ajax() — https://developer.wordpress.org/reference/functions/wp_doing_ajax/
- is_multisite() — https://developer.wordpress.org/reference/functions/is_multisite/
- wp_hash_password() — https://developer.wordpress.org/reference/functions/wp_hash_password/
- wp_check_password() — https://developer.wordpress.org/reference/functions/wp_check_password/
- wp_set_auth_cookie() — https://developer.wordpress.org/reference/functions/wp_set_auth_cookie/
- WP_Session_Tokens — https://developer.wordpress.org/reference/classes/wp_session_tokens/
- WP_Recovery_Mode — https://developer.wordpress.org/reference/classes/wp_recovery_mode/
- xmlrpc_enabled — https://developer.wordpress.org/reference/hooks/xmlrpc_enabled/
- template_redirect — https://developer.wordpress.org/reference/hooks/template_redirect/
- wp_die() — https://developer.wordpress.org/reference/functions/wp_die/
- wp_is_application_passwords_available() — https://developer.wordpress.org/reference/functions/wp_is_application_passwords_available/
- wp_get_environment_type() — https://developer.wordpress.org/reference/functions/wp_get_environment_type/
- get_password_reset_key() — https://developer.wordpress.org/reference/functions/get_password_reset_key/
- wp_privacy_personal_data_erasers — https://developer.wordpress.org/reference/hooks/wp_privacy_personal_data_erasers/
- wp_schedule_event() — https://developer.wordpress.org/reference/functions/wp_schedule_event/
- rest_is_ip_address() — https://developer.wordpress.org/reference/functions/rest_is_ip_address/
- wp_robots_no_robots() — https://developer.wordpress.org/reference/functions/wp_robots_no_robots/
- post_password_required() — https://developer.wordpress.org/reference/functions/post_password_required/
- Hardening WordPress (Advanced Administration) — https://developer.wordpress.org/advanced-administration/security/hardening/
- Brute Force Attacks (Advanced Administration) — https://developer.wordpress.org/advanced-administration/security/brute-force/
- In-repo: `docs/MODULES/PROTECTOR-EXHAUSTIVE-SPEC.md`, `docs/MODULES/OPERATIONS-PROTECTION-SPECS.md`, `docs/PRODUCT/PROTECTOR-BANK-ENTRY-READINESS-V1.md`, `config/product/options-bank/rest-api.json` (Surface 22 assigns the shared rate-limit service to Surface 27), `config/product/options-bank/connections.json` (Surface 23 owns outbound SSRF/TLS), `config/product/options-bank/roles.json` (Surface 30 anti-lockout and capability truth)

Native classification of the readiness checklist: authentication/login/lost-password/reset/logout URLs and hooks (`wp_login_url()`, `wp_lostpassword_url()`, `wp_logout_url()`, `get_password_reset_key()`, `login_init`, `authenticate`, `wp_authenticate()` and `wp_login_failed` are the alias and limiting substrate; core has no failed-login limit, so throttling, block lists and anti-enumeration normalization are WPE), current-user capabilities and network/Super Admin context (`current_user_can()`, `is_super_admin()`, `is_multisite()`; Protector composes and never grants), admin-ajax/admin-post and REST/Gutenberg dependencies (`wp_doing_ajax()`, `rest_authentication_errors`, `rest_pre_dispatch`; blanket blocking is rejected), request/redirect helpers (`template_redirect`, `wp_safe_redirect()`, `wp_validate_redirect()`, `wp_die()`), nonce/session/application-password semantics (`wp_verify_nonce()`, `wp_set_auth_cookie()`, `WP_Session_Tokens`, `wp_is_application_passwords_available()`; untouched by Protector), HTTP/server/CDN header ownership (`send_headers`, `wp_headers`, `status_header()`, `nocache_headers()`, `is_ssl()`; core emits no CSP/HSTS presets and upstream duplicates must be detected), client IP/server variables and reverse-proxy assumptions (core uses the direct remote address and `rest_is_ip_address()` validation only; trusted-proxy resolution is WPE), multisite/network login/admin behavior, native login error/enumeration characteristics (core login errors can distinguish unknown users; WPE normalizes), recovery (`WP_Recovery_Mode` is the fatal-error precedent; Protector adds a documented wp-config safe-disable constant), privacy (`wp_privacy_personal_data_erasers`) and retention jobs (`wp_schedule_event()`, owned by Surface 18).

## Seed-record disposition

| Seed record | Native disposition | Notes |
|---|---|---|
| `protector.overview.cards` | **WPE PRODUCT/POLICY** | Computed summaries only; block counts are aggregate and never expose raw IPs, usernames or request bodies to the card. |
| `protector.overview.quick-actions` | **WPE PRODUCT/POLICY** | Each action opens its own preview-first flow behind capability checks; no quick action publishes enforcement directly. |
| `protector.overview.lockout-risk` | **WPE PRODUCT/POLICY** | Runs the publish preflight against the current operator, known administrators, login and recovery routes and reports risk before any change. |
| `protector.rules.list.columns` | **WPE PRODUCT/POLICY** | Hit count is optional and retention-aware; conflict/health column surfaces precedence conflicts. |
| `protector.rules.list.filters` | **WPE PRODUCT/POLICY** | Filters cover allow/deny/challenge/redirect effects and site/frontend/admin/login/REST/custom resource classes. |
| `protector.rules.list.actions` | **WPE PRODUCT/POLICY** | Enable and publish go through the lockout preflight; delete is capability-separated. |
| `protector.rules.list.bulk` | **WPE PRODUCT/POLICY** | Bulk enable/disable only for safe groups; no bulk high-risk recovery or login-alias action. |
| `protector.site-gate.mode` | **WPE PRODUCT/POLICY** | Off by default; enabling any mode runs the lockout preflight and shows recovery instructions first. |
| `protector.site-gate.maintenance-renderer` | **WPE PRODUCT/POLICY** | Renderer content is local and escaped; gate page carries noindex and private/no-store cache headers. |
| `protector.site-gate.scope.frontend` | **WPE PRODUCT/POLICY** | Whole-frontend gating; admin and recovery routes stay reachable. |
| `protector.site-gate.scope.post-types` | **WPE PRODUCT/POLICY** | Uses registered post types only. |
| `protector.site-gate.scope.paths` | **WPE PRODUCT/POLICY** | Normalized path matching shared with the rule engine. |
| `protector.site-gate.scope.rest` | **WPE PRODUCT/POLICY** | REST is excluded unless explicitly included with an impact preview, so Gutenberg/builders keep working. |
| `protector.site-gate.scope.feeds-search-sitemaps` | **WPE PRODUCT/POLICY** | Each is an explicit scope toggle shown in preview. |
| `protector.site-gate.scope.media-honesty` | **WPE PRODUCT/POLICY** | Never claims protection of public static files it cannot intercept; the limitation is shown in the UI. |
| `protector.site-gate.exclusions.login-recovery` | **WPE PRODUCT/POLICY** | Login, lost-password and the current admin/recovery session are excluded by default so recovery is always possible. |
| `protector.site-gate.exclusions.health-loopback` | **WPE PRODUCT/POLICY** | WordPress loopback/health endpoints stay reachable when safely identifiable. |
| `protector.site-gate.exclusions.callbacks` | **WPE PRODUCT/POLICY** | Never a blanket bypass; owner adapters (Surface 23 Connections, payment providers) declare callback routes. |
| `protector.site-gate.exclusions.robots-sitemap` | **WPE PRODUCT/POLICY** | Optional and visible in preview. |
| `protector.site-gate.exclusions.networks-paths` | **WPE PRODUCT/POLICY** | IP exclusions are supplemental and resolve client IP through the trusted-proxy resolver. |
| `protector.site-gate.exclusions.preview` | **WPE PRODUCT/POLICY** | Preview lists all effective exclusions before the gate is enabled. |
| `protector.site-gate.noindex` | **WPE PRODUCT/POLICY** | On by default while gated. |
| `protector.site-gate.response-code` | **WPE PRODUCT/POLICY** | 401/403/503 by mode semantics; maintenance may send Retry-After. |
| `protector.gate-password.set` | **WPE PRODUCT/POLICY** | Existing password is never revealed; replacement requires confirmation. |
| `protector.gate-password.hash` | **WPE PRODUCT/POLICY** | Hashed with the WordPress password library; plaintext never stored in options, logs or exports. |
| `protector.gate-password.hint` | **WPE PRODUCT/POLICY** | Warns against password-derived hints. |
| `protector.gate-password.session-duration` | **WPE PRODUCT/POLICY** | Bounded duration; remember toggle optional. |
| `protector.gate-password.cookie-flags` | **WPE PRODUCT/POLICY** | Safest automatic path/domain scope; flags not weakenable in beginner mode. |
| `protector.gate-password.invalidate` | **WPE PRODUCT/POLICY** | Bumping the generation invalidates every remembered gate session. |
| `protector.gate-password.failure-limits` | **WPE PRODUCT/POLICY** | Temporary blocks only; shared rate service storage. |
| `protector.gate-password.captcha` | **WPE PRODUCT/POLICY** | Optional registered adapter; no provider is called by default. |
| `protector.gate-password.admin-bypass` | **WPE PRODUCT/POLICY** | On by default via wpe_protector_bypass so admins are never gated out. |
| `protector.gate-password.branding` | **WPE PRODUCT/POLICY** | Safe local media only; escaped output. |
| `protector.rule.identity` | **WPE PRODUCT/POLICY** | Stable key for portability and audit. |
| `protector.rule.priority` | **WPE PRODUCT/POLICY** | Feeds specificity-aware precedence. |
| `protector.rule.tags` | **WPE PRODUCT/POLICY** | Organizational only. |
| `protector.rule.scope.path` | **WPE PRODUCT/POLICY** | Normalized path matching; no unbounded patterns. |
| `protector.rule.scope.host-normalization` | **WPE PRODUCT/POLICY** | Untrusted Host header never selects scope. |
| `protector.rule.scope.wp-resource` | **WPE PRODUCT/POLICY** | Registered WordPress resources. |
| `protector.rule.scope.dashboard-route` | **WPE PRODUCT/POLICY** | Surface 13 routes as registered. |
| `protector.rule.scope.admin-screen` | **WPE PRODUCT/POLICY** | Route-aware; see admin compatibility. |
| `protector.rule.scope.login` | **WPE PRODUCT/POLICY** | Never blocks the recovery path. |
| `protector.rule.scope.rest` | **WPE PRODUCT/POLICY** | Outer deny/rate only; endpoint permission callbacks stay canonical (Surface 22). |
| `protector.rule.scope.ability` | **WPE PRODUCT/POLICY** | Registered Abilities only. |
| `protector.rule.scope.xmlrpc` | **WPE PRODUCT/POLICY** | Reference into Surface 29; no duplicate truth. |
| `protector.rule.scope.sdk-resource` | **WPE PRODUCT/POLICY** | Registered resource types only. |
| `protector.rule.subject.auth-state` | **WPE PRODUCT/POLICY** | Core authentication state. |
| `protector.rule.subject.users-roles-caps` | **WPE PRODUCT/POLICY** | Composes with Policy and Surface 30 Roles truth. |
| `protector.rule.subject.membership` | **WPE PRODUCT/POLICY** | Surface 15 truth. |
| `protector.rule.subject.ip-network` | **WPE PRODUCT/POLICY** | Supplemental, not identity; resolved through the trusted-proxy resolver. |
| `protector.rule.subject.policy-group` | **WPE PRODUCT/POLICY** | Only if bounded. |
| `protector.rule.condition.method` | **WPE PRODUCT/POLICY** | Safe fact. |
| `protector.rule.condition.site` | **WPE PRODUCT/POLICY** | Multisite-aware. |
| `protector.rule.condition.schedule` | **WPE PRODUCT/POLICY** | UTC stored, local rendered. |
| `protector.rule.condition.headers` | **WPE PRODUCT/POLICY** | No free-form header expressions. |
| `protector.rule.condition.environment` | **WPE PRODUCT/POLICY** | From configured environment type. |
| `protector.rule.condition.gate-session` | **WPE PRODUCT/POLICY** | Gate session generation aware. |
| `protector.rule.effect.deny` | **WPE PRODUCT/POLICY** | Explicit deny wins at same specificity. |
| `protector.rule.effect.allow-layer` | **WPE PRODUCT/POLICY** | An Allow never bypasses an earlier WordPress/core/WPE capability denial. |
| `protector.rule.effect.require-auth` | **WPE PRODUCT/POLICY** | Redirects to login with a validated local return URL. |
| `protector.rule.effect.require-gate` | **WPE PRODUCT/POLICY** | Shared gate semantics. |
| `protector.rule.effect.redirect` | **WPE PRODUCT/POLICY** | Local targets validated; loop detection. |
| `protector.rule.effect.conceal` | **WPE PRODUCT/POLICY** | Presentation choice, not authorization. |
| `protector.rule.effect.renderer` | **WPE PRODUCT/POLICY** | Escaped local content. |
| `protector.rule.effect.challenge` | **WPE PRODUCT/POLICY** | Adapters registered explicitly. |
| `protector.rule.response.status` | **WPE PRODUCT/POLICY** | 429 for rate limits. |
| `protector.rule.response.external-redirect` | **WPE PRODUCT/POLICY** | Allowlisted hosts only; prevents open redirects. |
| `protector.rule.response.rest-json` | **WPE PRODUCT/POLICY** | Standard WP_Error-shaped JSON. |
| `protector.rule.response.retry-after` | **WPE PRODUCT/POLICY** | Where applicable. |
| `protector.rule.response.cache-control` | **WPE PRODUCT/POLICY** | Prevents caching of gated content. |
| `protector.rule.response.no-leak` | **WPE PRODUCT/POLICY** | Generic messages only. |
| `protector.rule.schedule.windows` | **WPE PRODUCT/POLICY** | Rendered in site timezone. |
| `protector.rule.schedule.dst-preview` | **WPE PRODUCT/POLICY** | Shows DST transitions. |
| `protector.rule.schedule.expiry-visible` | **WPE PRODUCT/POLICY** | Never silently deleted. |
| `protector.precedence.order` | **WPE PRODUCT/POLICY** | Deterministic order. |
| `protector.precedence.deny-wins` | **WPE PRODUCT/POLICY** | Unless a dedicated recovery override applies. |
| `protector.simulate.request` | **WPE PRODUCT/POLICY** | Read-only simulation; no enforcement side effects. |
| `protector.simulate.outer-policy` | **WPE PRODUCT/POLICY** | Where available. |
| `protector.admin.mode` | **WPE PRODUCT/POLICY** | No additional restriction by default. |
| `protector.admin.screen-restrictions` | **WPE PRODUCT/POLICY** | Capability-based. |
| `protector.admin.menu-delegation` | **WPE PRODUCT/POLICY** | Presentation owned by Surface 11; hiding is not authorization. |
| `protector.admin.ajax-adapter` | **WPE PRODUCT/POLICY** | admin-ajax.php and admin-post.php are never disabled globally. |
| `protector.admin.compat-warnings` | **WPE PRODUCT/POLICY** | Shown before publish. |
| `protector.admin.recovery-exemption` | **WPE PRODUCT/POLICY** | Recovery admin always retains access. |
| `protector.admin.network` | **WPE PRODUCT/POLICY** | Super Admin context respected. |
| `protector.admin.audit-denied` | **WPE PRODUCT/POLICY** | Privacy-aware log fields. |
| `protector.login-alias.enable` | **WPE PRODUCT/POLICY** | Positioned as noise reduction only, never as authentication. |
| `protector.login-alias.validation` | **WPE PRODUCT/POLICY** | Alias cannot equal blocked original route semantics. |
| `protector.login-alias.original` | **WPE PRODUCT/POLICY** | POST/auth mechanics preserved. |
| `protector.login-alias.rewrites` | **WPE PRODUCT/POLICY** | All core flows keep working. |
| `protector.login-alias.multisite` | **WPE PRODUCT/POLICY** | Network-aware. |
| `protector.login-alias.integrations` | **WPE PRODUCT/POLICY** | Unless explicitly integrated by owner. |
| `protector.login-alias.change-reauth` | **WPE PRODUCT/POLICY** | Recovery instructions shown before save. |
| `protector.login-alias.compat-test` | **WPE PRODUCT/POLICY** | Reported before activation. |
| `protector.login.limit.enable` | **WPE PRODUCT/POLICY** | Temporary blocks only; never a permanent automatic ban. |
| `protector.login.limit.thresholds` | **WPE PRODUCT/POLICY** | Conservative defaults. |
| `protector.login.limit.key` | **WPE PRODUCT/POLICY** | Account-sensitive keys avoid locking a real user out because of attackers; IP from trusted-proxy resolver. |
| `protector.login.enumeration` | **WPE PRODUCT/POLICY** | Same response for unknown and wrong-password. |
| `protector.login.trusted-bypass` | **WPE PRODUCT/POLICY** | Off by default. |
| `protector.login.admin-lockout` | **WPE PRODUCT/POLICY** | Administrators are never locked out without a documented recovery path. |
| `protector.login.challenge` | **WPE PRODUCT/POLICY** | Optional adapter. |
| `protector.login.notify` | **WPE PRODUCT/POLICY** | Delivered via Surface 19 Notifications. |
| `protector.login.block-list` | **WPE PRODUCT/POLICY** | Reset requires capability. |
| `protector.login.retention` | **WPE PRODUCT/POLICY** | Bounded. |
| `protector.rate.shared-service` | **WPE PRODUCT/POLICY** | Surface 27 owns rate storage/algorithm; Surface 22 REST profiles (rest-api.rate.limit), Surface 29 XML-RPC and Surface 17/21/23 owners consume it. |
| `protector.rate.targets` | **WPE PRODUCT/POLICY** | Webhook receive only where the owner adapter permits. |
| `protector.rate.key` | **WPE PRODUCT/POLICY** | Principal/IP/route keys; IP via trusted-proxy resolver; keys hashed in logs. |
| `protector.rate.limit-window` | **WPE PRODUCT/POLICY** | Burst only if algorithm supports it. |
| `protector.rate.block-duration` | **WPE PRODUCT/POLICY** | Always bounded. |
| `protector.rate.response` | **WPE PRODUCT/POLICY** | RFC 6585 semantics. |
| `protector.rate.bypass` | **WPE PRODUCT/POLICY** | Explicit and logged. |
| `protector.rate.log-sampling` | **WPE PRODUCT/POLICY** | Bounded writes. |
| `protector.rate.backend-health` | **WPE PRODUCT/POLICY** | UI never promises strict distributed limits unless the backend supports them. |
| `protector.rate.concurrency` | **WPE PRODUCT/POLICY** | Required after implementation consent. |
| `protector.ip.direct-default` | **WPE PRODUCT/POLICY** | Forwarded headers ignored by default. |
| `protector.ip.trusted-proxies` | **WPE PRODUCT/POLICY** | Forwarded headers parsed only from listed proxies. |
| `protector.ip.forward-header` | **WPE PRODUCT/POLICY** | One selected header per deployment. |
| `protector.ip.hop-strategy` | **WPE PRODUCT/POLICY** | Walks the chain from the nearest trusted proxy; untrusted left-most values ignored. |
| `protector.ip.diagnostics` | **WPE PRODUCT/POLICY** | Displayed safely to authorized admins. |
| `protector.ip.spoof-ignore` | **WPE PRODUCT/POLICY** | Malformed values fall back to the direct address. |
| `protector.networks.groups` | **WPE PRODUCT/POLICY** | Reusable across rules. |
| `protector.networks.entries` | **WPE PRODUCT/POLICY** | Validated on save. |
| `protector.networks.expiration` | **WPE PRODUCT/POLICY** | Expired entries stop matching. |
| `protector.networks.import` | **WPE PRODUCT/POLICY** | Manual import only; no remote feed. |
| `protector.headers.nosniff` | **WPE PRODUCT/POLICY** | Low-risk preset. |
| `protector.headers.referrer` | **WPE PRODUCT/POLICY** | Advisory preset. |
| `protector.headers.permissions` | **WPE PRODUCT/POLICY** | Structured, not raw. |
| `protector.headers.frame` | **WPE PRODUCT/POLICY** | Compatibility note shown. |
| `protector.headers.hsts` | **WPE PRODUCT/POLICY** | Never auto-enabled; preload requires explicit acknowledgement. |
| `protector.headers.csp` | **WPE PRODUCT/POLICY** | Report-Only precedes enforcement; beginner mode never concatenates arbitrary strings. |
| `protector.headers.csp-report` | **WPE PRODUCT/POLICY** | No dangling report-uri. |
| `protector.headers.scope` | **WPE PRODUCT/POLICY** | Per-scope presets. |
| `protector.headers.duplicates` | **WPE PRODUCT/POLICY** | Warns when headers already set upstream. |
| `protector.headers.preview` | **WPE PRODUCT/POLICY** | Preview of the exact headers before publish. |
| `protector.rest.outer-deny` | **WPE PRODUCT/POLICY** | Cannot grant what the route permission callback denies. |
| `protector.rest.extra-policy` | **WPE PRODUCT/POLICY** | Additive only. |
| `protector.rest.rate` | **WPE PRODUCT/POLICY** | Consumes the shared rate service. |
| `protector.rest.denied-log` | **WPE PRODUCT/POLICY** | Privacy-aware. |
| `protector.rest.discovery` | **WPE PRODUCT/POLICY** | Impact-reviewed. |
| `protector.rest.impact-preview` | **WPE PRODUCT/POLICY** | Warns on Gutenberg-heavy sites. |
| `protector.xmlrpc.delegated` | **WPE PRODUCT/POLICY** | Surface 29 owns XML-RPC truth. |
| `protector.recovery.capability` | **WPE PRODUCT/POLICY** | Required before high-risk activation. |
| `protector.recovery.safe-mode-constant` | **WPE PRODUCT/POLICY** | Safe mode disables enforcement without database access; documented in recovery instructions. |
| `protector.recovery.instructions` | **WPE PRODUCT/POLICY** | Shown before every high-risk publish. |
| `protector.recovery.deactivation` | **WPE PRODUCT/POLICY** | Deactivation is a valid recovery path. |
| `protector.recovery.preflight` | **WPE PRODUCT/POLICY** | Blocks publish on detected lockout. |
| `protector.recovery.reauth` | **WPE PRODUCT/POLICY** | Recent re-auth required. |
| `protector.recovery.site-health` | **WPE PRODUCT/POLICY** | Recovery readiness reported. |
| `protector.recovery.license-degradation` | **WPE PRODUCT/POLICY** | Protection does not silently disappear. |
| `protector.logs.fields` | **WPE PRODUCT/POLICY** | Minimal fields. |
| `protector.logs.defaults` | **WPE PRODUCT/POLICY** | Bounded retention. |
| `protector.logs.retention` | **WPE PRODUCT/POLICY** | Purge via Surface 18 jobs. |
| `protector.logs.ip-anonymize` | **WPE PRODUCT/POLICY** | Privacy policy aware. |
| `protector.logs.redaction` | **WPE PRODUCT/POLICY** | Rate-limit keys hashed. |
| `protector.logs.ua` | **WPE PRODUCT/POLICY** | Minimization. |
| `protector.logs.export-erase` | **WPE PRODUCT/POLICY** | WordPress privacy erasers. |
| `protector.logs.no-third-party-lookup` | **WPE PRODUCT/POLICY** | Geo only via explicit provider integration; no remote call in the request path. |
| `protector.perm.capabilities` | **WPE PRODUCT/POLICY** | Twelve candidate capabilities. |
| `protector.perm.separation` | **WPE PRODUCT/POLICY** | Separate from ordinary rule editing. |
| `protector.abilities.registry` | **WPE PRODUCT/POLICY** | Policy-gated. |
| `protector.ai.read-only` | **WPE PRODUCT/POLICY** | Publish/recovery/network/header/login changes disabled for AI. |
| `protector.events.catalog` | **WPE PRODUCT/POLICY** | Sampled audit class. |
| `protector.states.degraded` | **WPE PRODUCT/POLICY** | Includes misconfigured proxy, backend unavailable, lockout risk. |
| `protector.perf.compiled` | **WPE PRODUCT/POLICY** | No per-request scan of all rules. |
| `protector.perf.no-remote` | **WPE PRODUCT/POLICY** | Fail-safe local decisions. |
| `protector.perf.log-independent` | **WPE PRODUCT/POLICY** | Logs async/sampled where safe. |
| `protector.multisite.scope` | **WPE PRODUCT/POLICY** | Per-site default; network separately governed. |
| `protector.ownership.policy-composition` | **WPE PRODUCT/POLICY** | Protector never grants missing permission. |
| `protector.ownership.boundaries` | **WPE PRODUCT/POLICY** | Protector is not a WAF or malware scanner; outbound HTTP safety stays with Surface 23. |
| `protector.portability.package` | **WPE PRODUCT/POLICY** | Gate password hashes and secrets excluded. |
| `protector.native.login-urls` | **NATIVE SUBSTRATE + WPE POLICY** | Alias rewrites build on native URL filters. |
| `protector.native.authenticate` | **NATIVE SUBSTRATE + WPE POLICY** | Login limiting hooks native auth flow. |
| `protector.native.capabilities` | **NATIVE SUBSTRATE + WPE POLICY** | Authorization stays native/Policy. |
| `protector.native.nonce` | **NATIVE SUBSTRATE + WPE POLICY** | All admin mutations nonce-protected. |
| `protector.native.safe-redirect` | **NATIVE SUBSTRATE + WPE POLICY** | Local redirect validation. |
| `protector.native.request-hooks` | **NATIVE SUBSTRATE + WPE POLICY** | Request interception points. |
| `protector.native.rest-auth` | **NATIVE SUBSTRATE + WPE POLICY** | Outer REST deny/rate hooks. |
| `protector.native.headers` | **NATIVE SUBSTRATE + WPE POLICY** | Header emission substrate. |
| `protector.native.password-hash` | **NATIVE SUBSTRATE + WPE POLICY** | Gate password hashing. |
| `protector.native.auth-sessions` | **NATIVE SUBSTRATE + WPE POLICY** | Core session semantics untouched. |
| `protector.native.recovery-mode` | **NATIVE SUBSTRATE + WPE POLICY** | Precedent for emailed recovery; Protector safe mode is separate. |
| `protector.native.ssl` | **NATIVE SUBSTRATE + WPE POLICY** | Secure cookie and HSTS gating. |
| `protector.native.multisite` | **NATIVE SUBSTRATE + WPE POLICY** | Network context. |
| `protector.native.ajax` | **NATIVE OPTIONAL SUBSTRATE, WPE-BOUNDED** | AJAX-aware admin rules. |
| `protector.native.app-passwords` | **NATIVE OPTIONAL SUBSTRATE, WPE-BOUNDED** | Application Passwords unaffected. |
| `protector.native.environment` | **NATIVE OPTIONAL SUBSTRATE, WPE-BOUNDED** | Environment condition. |
| `protector.native.no-robots` | **NATIVE OPTIONAL SUBSTRATE, WPE-BOUNDED** | Gate noindex. |
| `protector.native.post-password` | **NATIVE OPTIONAL SUBSTRATE, WPE-BOUNDED** | Native per-post password; Site Gate is distinct. |
| `protector.native.ip-validate` | **NATIVE OPTIONAL SUBSTRATE, WPE-BOUNDED** | IP validation helper. |
| `protector.native.privacy-erasers` | **NATIVE OPTIONAL SUBSTRATE, WPE-BOUNDED** | Log erasure integration. |
| `protector.native.cron` | **NATIVE OPTIONAL SUBSTRATE, WPE-BOUNDED** | Retention purge via Surface 18. |
| `protector.native.xmlrpc-enabled` | **NATIVE OPTIONAL SUBSTRATE, WPE-BOUNDED** | Owned by Surface 29. |
| `protector.native.wp-die` | **NATIVE OPTIONAL SUBSTRATE, WPE-BOUNDED** | Deny/gate response rendering. |
| `protector.native.reset-key` | **NATIVE OPTIONAL SUBSTRATE, WPE-BOUNDED** | Reset-key URLs preserved under alias. |
| `protector.exceed.lockout-proof` | **WPE EXCEED / NO NATIVE EQUIVALENT** | Beyond market lockout warnings. |
| `protector.exceed.decision-trace` | **WPE EXCEED / NO NATIVE EQUIVALENT** | Winning rule and outer policy explained. |
| `protector.exceed.proxy-chain` | **WPE EXCEED / NO NATIVE EQUIVALENT** | Standards-driven (RFC 7239). |
| `protector.exceed.csp-assistant` | **WPE EXCEED / NO NATIVE EQUIVALENT** | Proposes directives; human publishes. |
| `protector.deferred.recovery-token` | **DEFERRED / CROSS-OWNER** | Only if an accepted design exists. |
| `protector.deferred.geo` | **DEFERRED / CROSS-OWNER** | Not assumed built-in. |
| `protector.deferred.regex` | **DEFERRED / CROSS-OWNER** | Only if separately accepted/validated. |
| `protector.deferred.threat-feeds` | **DEFERRED / CROSS-OWNER** | Requires separately accepted integration. |
| `protector.deferred.distributed-rate` | **DEFERRED / CROSS-OWNER** | Implementation decision. |
| `protector.deferred.provider-ip-adapters` | **DEFERRED / CROSS-OWNER** | Certification required. |
| `protector.unsafe.trust-xff` | **REJECTED-UNSAFE CONFIRMED** | Rejected: client IP is spoofable. |
| `protector.unsafe.permanent-lockout` | **REJECTED-UNSAFE CONFIRMED** | Rejected. |
| `protector.unsafe.log-secrets` | **REJECTED-UNSAFE CONFIRMED** | Rejected. |
| `protector.unsafe.disable-rest-default` | **REJECTED-UNSAFE CONFIRMED** | Rejected: breaks Gutenberg/builders. |
| `protector.unsafe.obscurity-as-auth` | **REJECTED-UNSAFE CONFIRMED** | Rejected. |
| `protector.unsafe.ui-hiding-auth` | **REJECTED-UNSAFE CONFIRMED** | Rejected. |
| `protector.unsafe.ai-enforcement` | **REJECTED-UNSAFE CONFIRMED** | Rejected. |
| `protector.unsafe.allow-bypass` | **REJECTED-UNSAFE CONFIRMED** | Rejected. |
| `protector.unsafe.php-conditions` | **REJECTED-UNSAFE CONFIRMED** | Rejected. |
| `protector.unsafe.blanket-admin-block` | **REJECTED-UNSAFE CONFIRMED** | Rejected. |
| `protector.unsafe.query-bypass` | **REJECTED-UNSAFE CONFIRMED** | Rejected. |
| `protector.unsafe.auto-hsts-csp` | **REJECTED-UNSAFE CONFIRMED** | Rejected. |
| `protector.unsafe.remote-auth-path` | **REJECTED-UNSAFE CONFIRMED** | Rejected. |
| `protector.unsafe.third-party-ip-lookup` | **REJECTED-UNSAFE CONFIRMED** | Rejected as a default. |
| `protector.unsafe.plaintext-gate-password` | **REJECTED-UNSAFE CONFIRMED** | Rejected. |
| `protector.unsafe.open-redirect` | **REJECTED-UNSAFE CONFIRMED** | Rejected. |
| `protector.unsafe.static-claim` | **REJECTED-UNSAFE CONFIRMED** | Rejected. |
| `protector.unsafe.raw-headers` | **REJECTED-UNSAFE CONFIRMED** | Rejected. |
| `protector.unsafe.waf-claim` | **REJECTED-UNSAFE CONFIRMED** | Rejected; Surface 52 owns scanning. |
| `protector.unsafe.host-header` | **REJECTED-UNSAFE CONFIRMED** | Rejected. |
| `protector.unsafe.enumeration` | **REJECTED-UNSAFE CONFIRMED** | Rejected as a default. |

## Native completeness / unresolved items

No additional Protector V1 Bank family is required. Native unresolved items: **0**. Site Gate, the rule engine and precedence, failed-login limiting, the shared rate-limit service, trusted-proxy client-IP resolution, network groups, header presets with Report-Only CSP, lockout preflight with safe-mode recovery and privacy-aware security logs have no native WordPress equivalent and remain WPE/market-owned.

## Gate boundary

Not promoted here: request blocking, login/admin route changes, header emission, rate-limit state mutation, runtime, Atomic Option Contracts, UX contract closure or product parity.
