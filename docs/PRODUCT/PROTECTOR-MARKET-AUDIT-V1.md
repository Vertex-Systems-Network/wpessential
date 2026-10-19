# Protector — Market Audit V1

Status: `MARKET_AUDITED` (integrated in `config/product/options-bank-audits/protector-market-ecosystem.json`)

Surface: 27 — Protector
Snapshot: 2026-10-19
Issue: #1456

## 1. Scope and boundary

Reconciles all 225 Protector records. Surface 27 owns request/access hardening only: Site Gate, Protector rules, login alias/protection, the shared rate-limit service, client-IP/trusted-proxy resolution, network groups, security-header presets, outer REST deny/rate, recovery and privacy-aware security logs; Policy and Roles (S30) own authorization, REST endpoints (S22) keep permission callbacks, XML-RPC Manager (S29) owns XML-RPC semantics, Redirect (S44) generic routing, Security Scanner (S52) malware scanning, Membership (S15) entitlement truth, Connections (S23) outbound SSRF/TLS policy, Notifications (S19) delivery and Cron/Job (S18) retention jobs. Rate-limit ownership matches the Surface 22 REST API Bank (`rest-api.rate.limit` assigns storage/algorithm to Surface 27) and outbound SSRF/TLS stays with the Surface 23 Connections Bank.

## 2. Evidence

- **E1 Wordfence Security** — https://wordpress.org/plugins/wordfence/ — Endpoint firewall, login security (brute-force limits, 2FA, CAPTCHA), country blocking, live traffic and IP source configuration.
- **E2 Solid Security** — https://wordpress.org/plugins/better-wp-security/, https://solidwp.com/security — Brute-force protection, hide backend login alias, user lockouts, security headers/hardening tweaks and lockout recovery messaging.
- **E3 All-In-One Security (AIOS)** — https://wordpress.org/plugins/all-in-one-wp-security-and-firewall/ — Login lockout, rename login page, maintenance/lockout of visitors, IP blacklists, firewall rules, REST/XML-RPC restrictions and audit logs.
- **E4 Limit Login Attempts Reloaded** — https://wordpress.org/plugins/limit-login-attempts-reloaded/ — Failed-login limits with lockout durations, allow/deny lists, trusted IP origin configuration and lockout logs.
- **S1 WPS Hide Login** — https://wordpress.org/plugins/wps-hide-login/ — Login URL alias (Specialist: login alias compatibility; obscurity boundary).
- **S2 Password Protected** — https://wordpress.org/plugins/password-protected/ — Whole-site shared password gate with admin/REST options (Specialist: Site Gate).
- **S3 SeedProd Coming Soon & Maintenance** — https://wordpress.org/plugins/coming-soon/ — Coming-soon/maintenance pages with access bypass (Specialist: maintenance gate).
- **S4 Disable REST API** — https://wordpress.org/plugins/disable-json-api/ — Disables REST API for unauthenticated users with route allowlist (Specialist: REST restriction impact).
- **S5 Headers Security Advanced & HSTS WP** — https://wordpress.org/plugins/headers-security-advanced-hsts-wp/ — Security headers and HSTS presets (Specialist: headers).
- **S6 Members** — https://wordpress.org/plugins/members/ — Role/capability content permissions and private-site option (Specialist: Policy composition).
- **S7 OWASP Authentication Cheat Sheet** — https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html — Generic login errors, account lockout trade-offs and throttling guidance.
- **S8 OWASP Credential Stuffing Prevention Cheat Sheet** — https://cheatsheetseries.owasp.org/cheatsheets/Credential_Stuffing_Prevention_Cheat_Sheet.html — Rate limiting, CAPTCHA and IP-reputation caveats.
- **S9 OWASP Logging Cheat Sheet** — https://cheatsheetseries.owasp.org/cheatsheets/Logging_Cheat_Sheet.html — Never log passwords, session IDs or tokens; retention and minimization.
- **S10 OWASP HTTP Headers Cheat Sheet** — https://cheatsheetseries.owasp.org/cheatsheets/HTTP_Headers_Cheat_Sheet.html — nosniff, Referrer-Policy, Permissions-Policy, frame protection and HSTS guidance.
- **S11 OWASP Content Security Policy Cheat Sheet** — https://cheatsheetseries.owasp.org/cheatsheets/Content_Security_Policy_Cheat_Sheet.html — Report-only rollout and structured CSP directives.
- **S12 MDN Content-Security-Policy-Report-Only** — https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Content-Security-Policy-Report-Only — Monitor policy effects before enforcement.
- **S13 RFC 7239 Forwarded HTTP Extension** — https://www.rfc-editor.org/rfc/rfc7239 — Standardized Forwarded header and proxy-chain trust.
- **S14 MDN X-Forwarded-For** — https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/X-Forwarded-For — Spoofable left-most values; trust only the right-most trusted-proxy entries.
- **S15 RFC 6797 HTTP Strict Transport Security** — https://www.rfc-editor.org/rfc/rfc6797 — HSTS max-age/includeSubDomains semantics.
- **S16 HSTS Preload List Submission** — https://hstspreload.org/ — Preload is hard to undo; requires explicit opt-in.
- **S17 RFC 6585 Additional HTTP Status Codes** — https://www.rfc-editor.org/rfc/rfc6585 — 429 Too Many Requests with Retry-After.
- **S18 NIST SP 800-63B** — https://pages.nist.gov/800-63-4/sp800-63b.html — Rate limiting of failed authentication attempts without permanent lockout as the only control.

## 3. Record reconciliation

| Record | Disposition |
|---|---|
| `protector.overview.cards` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.overview.quick-actions` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.overview.lockout-risk` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rules.list.columns` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rules.list.filters` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rules.list.actions` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rules.list.bulk` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.site-gate.mode` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.site-gate.maintenance-renderer` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.site-gate.scope.frontend` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.site-gate.scope.post-types` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.site-gate.scope.paths` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.site-gate.scope.rest` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.site-gate.scope.feeds-search-sitemaps` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.site-gate.scope.media-honesty` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.site-gate.exclusions.login-recovery` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.site-gate.exclusions.health-loopback` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.site-gate.exclusions.callbacks` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.site-gate.exclusions.robots-sitemap` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.site-gate.exclusions.networks-paths` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.site-gate.exclusions.preview` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.site-gate.noindex` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.site-gate.response-code` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.gate-password.set` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.gate-password.hash` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.gate-password.hint` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.gate-password.session-duration` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.gate-password.cookie-flags` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.gate-password.invalidate` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.gate-password.failure-limits` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.gate-password.captcha` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.gate-password.admin-bypass` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.gate-password.branding` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.identity` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.priority` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.tags` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.scope.path` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.scope.host-normalization` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.scope.wp-resource` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.scope.dashboard-route` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.scope.admin-screen` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.scope.login` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.scope.rest` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.scope.ability` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.scope.xmlrpc` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.scope.sdk-resource` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.subject.auth-state` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.subject.users-roles-caps` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.subject.membership` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.subject.ip-network` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.subject.policy-group` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.condition.method` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.condition.site` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.condition.schedule` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.condition.headers` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.condition.environment` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.condition.gate-session` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.effect.deny` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.effect.allow-layer` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.effect.require-auth` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.effect.require-gate` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.effect.redirect` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.effect.conceal` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.effect.renderer` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.effect.challenge` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.response.status` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.response.external-redirect` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.response.rest-json` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.response.retry-after` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.response.cache-control` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.response.no-leak` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.schedule.windows` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.schedule.dst-preview` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rule.schedule.expiry-visible` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.precedence.order` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.precedence.deny-wins` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.simulate.request` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.simulate.outer-policy` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.admin.mode` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.admin.screen-restrictions` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.admin.menu-delegation` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.admin.ajax-adapter` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.admin.compat-warnings` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.admin.recovery-exemption` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.admin.network` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.admin.audit-denied` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.login-alias.enable` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.login-alias.validation` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.login-alias.original` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.login-alias.rewrites` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.login-alias.multisite` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.login-alias.integrations` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.login-alias.change-reauth` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.login-alias.compat-test` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.login.limit.enable` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.login.limit.thresholds` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.login.limit.key` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.login.enumeration` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.login.trusted-bypass` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.login.admin-lockout` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.login.challenge` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.login.notify` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.login.block-list` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.login.retention` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rate.shared-service` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rate.targets` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rate.key` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rate.limit-window` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rate.block-duration` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rate.response` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rate.bypass` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rate.log-sampling` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rate.backend-health` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rate.concurrency` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.ip.direct-default` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.ip.trusted-proxies` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.ip.forward-header` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.ip.hop-strategy` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.ip.diagnostics` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.ip.spoof-ignore` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.networks.groups` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.networks.entries` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.networks.expiration` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.networks.import` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.headers.nosniff` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.headers.referrer` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.headers.permissions` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.headers.frame` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.headers.hsts` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.headers.csp` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.headers.csp-report` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.headers.scope` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.headers.duplicates` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.headers.preview` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rest.outer-deny` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rest.extra-policy` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rest.rate` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rest.denied-log` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rest.discovery` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.rest.impact-preview` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.xmlrpc.delegated` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.recovery.capability` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.recovery.safe-mode-constant` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.recovery.instructions` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.recovery.deactivation` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.recovery.preflight` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.recovery.reauth` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.recovery.site-health` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.recovery.license-degradation` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.logs.fields` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.logs.defaults` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.logs.retention` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.logs.ip-anonymize` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.logs.redaction` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.logs.ua` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.logs.export-erase` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.logs.no-third-party-lookup` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.perm.capabilities` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.perm.separation` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.abilities.registry` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.ai.read-only` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.events.catalog` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.states.degraded` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.perf.compiled` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.perf.no-remote` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.perf.log-independent` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.multisite.scope` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.ownership.policy-composition` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.ownership.boundaries` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.portability.package` | MARKET_EVIDENCED / WPE_HARDENED |
| `protector.native.login-urls` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `protector.native.authenticate` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `protector.native.capabilities` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `protector.native.nonce` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `protector.native.safe-redirect` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `protector.native.request-hooks` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `protector.native.rest-auth` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `protector.native.headers` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `protector.native.password-hash` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `protector.native.auth-sessions` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `protector.native.recovery-mode` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `protector.native.ssl` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `protector.native.multisite` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `protector.native.ajax` | MARKET_EVIDENCED_WITH_BOUNDS |
| `protector.native.app-passwords` | MARKET_EVIDENCED_WITH_BOUNDS |
| `protector.native.environment` | MARKET_EVIDENCED_WITH_BOUNDS |
| `protector.native.no-robots` | MARKET_EVIDENCED_WITH_BOUNDS |
| `protector.native.post-password` | MARKET_EVIDENCED_WITH_BOUNDS |
| `protector.native.ip-validate` | MARKET_EVIDENCED_WITH_BOUNDS |
| `protector.native.privacy-erasers` | MARKET_EVIDENCED_WITH_BOUNDS |
| `protector.native.cron` | MARKET_EVIDENCED_WITH_BOUNDS |
| `protector.native.xmlrpc-enabled` | MARKET_EVIDENCED_WITH_BOUNDS |
| `protector.native.wp-die` | MARKET_EVIDENCED_WITH_BOUNDS |
| `protector.native.reset-key` | MARKET_EVIDENCED_WITH_BOUNDS |
| `protector.exceed.lockout-proof` | KEEP WPE_EXCEED — standards/specialist-evidenced, not common in WordPress security plugins |
| `protector.exceed.decision-trace` | KEEP WPE_EXCEED — standards/specialist-evidenced, not common in WordPress security plugins |
| `protector.exceed.proxy-chain` | KEEP WPE_EXCEED — standards/specialist-evidenced, not common in WordPress security plugins |
| `protector.exceed.csp-assistant` | KEEP WPE_EXCEED — standards/specialist-evidenced, not common in WordPress security plugins |
| `protector.deferred.recovery-token` | KEEP DEFERRED — market demand or future need exists; separate design/certification required |
| `protector.deferred.geo` | KEEP DEFERRED — market demand or future need exists; separate design/certification required |
| `protector.deferred.regex` | KEEP DEFERRED — market demand or future need exists; separate design/certification required |
| `protector.deferred.threat-feeds` | KEEP DEFERRED — market demand or future need exists; separate design/certification required |
| `protector.deferred.distributed-rate` | KEEP DEFERRED — market demand or future need exists; separate design/certification required |
| `protector.deferred.provider-ip-adapters` | KEEP DEFERRED — market demand or future need exists; separate design/certification required |
| `protector.unsafe.trust-xff` | REJECTED_UNSAFE — market pattern rejected |
| `protector.unsafe.permanent-lockout` | REJECTED_UNSAFE — market pattern rejected |
| `protector.unsafe.log-secrets` | REJECTED_UNSAFE — market pattern rejected |
| `protector.unsafe.disable-rest-default` | REJECTED_UNSAFE — market pattern rejected |
| `protector.unsafe.obscurity-as-auth` | REJECTED_UNSAFE — market pattern rejected |
| `protector.unsafe.ui-hiding-auth` | REJECTED_UNSAFE — market pattern rejected |
| `protector.unsafe.ai-enforcement` | REJECTED_UNSAFE — market pattern rejected |
| `protector.unsafe.allow-bypass` | REJECTED_UNSAFE — market pattern rejected |
| `protector.unsafe.php-conditions` | REJECTED_UNSAFE — market pattern rejected |
| `protector.unsafe.blanket-admin-block` | REJECTED_UNSAFE — market pattern rejected |
| `protector.unsafe.query-bypass` | REJECTED_UNSAFE — market pattern rejected |
| `protector.unsafe.auto-hsts-csp` | REJECTED_UNSAFE — market pattern rejected |
| `protector.unsafe.remote-auth-path` | REJECTED_UNSAFE — market pattern rejected |
| `protector.unsafe.third-party-ip-lookup` | REJECTED_UNSAFE — market pattern rejected |
| `protector.unsafe.plaintext-gate-password` | REJECTED_UNSAFE — market pattern rejected |
| `protector.unsafe.open-redirect` | REJECTED_UNSAFE — market pattern rejected |
| `protector.unsafe.static-claim` | REJECTED_UNSAFE — market pattern rejected |
| `protector.unsafe.raw-headers` | REJECTED_UNSAFE — market pattern rejected |
| `protector.unsafe.waf-claim` | REJECTED_UNSAFE — market pattern rejected |
| `protector.unsafe.host-header` | REJECTED_UNSAFE — market pattern rejected |
| `protector.unsafe.enumeration` | REJECTED_UNSAFE — market pattern rejected |

Coverage: **225 / 225**; 149 Bank references; 7 extra dispositions; unresolved **0**; record delta after market audit **0**.

## 4. Rejected market patterns

- Reading the client IP from `X-Forwarded-For` without a trusted-proxy list (E4, S14, S13) → `protector.unsafe.trust-xff`, `protector.ip.trusted-proxies`, `protector.ip.hop-strategy`.
- Permanent or indefinitely escalating automatic lockouts without a recovery path (E3, S8, S18) → `protector.unsafe.permanent-lockout`, `protector.recovery.safe-mode-constant`.
- Logging submitted passwords, cookies, auth headers or tokens (S9) → `protector.unsafe.log-secrets`, `protector.logs.redaction`.
- Disabling the REST API wholesale by default (S4) → `protector.unsafe.disable-rest-default`, `protector.rest.impact-preview`.
- Hidden login URLs or hidden menus presented as protection (S1, S6, E2) → `protector.unsafe.obscurity-as-auth`, `protector.unsafe.ui-hiding-auth`.
- One-click HSTS preload or strict CSP enforcement (S16, S11) → `protector.unsafe.auto-hsts-csp`, `protector.headers.csp`, `protector.headers.preview`.
- AI agents changing enforcement, recovery, headers or login settings → `protector.unsafe.ai-enforcement`, `protector.ai.read-only`.
- Default third-party IP/geo lookups for country blocking (E1) are deferred to an explicit provider integration → `protector.deferred.geo`, `protector.unsafe.third-party-ip-lookup`.

No request blocking, login/admin route change, header emission, rate-limit execution, runtime or product parity is authorized.
