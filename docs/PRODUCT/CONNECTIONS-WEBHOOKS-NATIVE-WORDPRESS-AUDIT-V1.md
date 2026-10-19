# Connections/Webhooks — Native WordPress Audit V1

Surface: **23 / Connections/Webhooks**
Issue: **#1448**
Planning parent: **#589 / merged PR #602**
Seed: `config/product/options-bank/connections.json` — **65 records**

## Decision

**NATIVE AUDIT EVIDENCE COMPLETE; WORDPRESS CORE PROVIDES HTTP TRANSPORT, URL VALIDATION, NONCE, REST ROUTE, CRON AND OPTION PRIMITIVES BUT NO CONNECTION MANAGER, VAULT, OAUTH CLIENT, SSRF CONTRACT OR WEBHOOK VERIFIER.** Native primitives are transport substrate only and never a complete security contract. No outbound request, OAuth flow, credential storage, webhook route registration or runtime promotion occurs here.

## Current native sources

- HTTP API (Making HTTP Requests) — https://developer.wordpress.org/apis/making-http-requests/
- `WP_Http` (`timeout`, `redirection`, `sslverify`, `limit_response_size`, `reject_unsafe_urls`) — https://developer.wordpress.org/reference/classes/wp_http/
- `wp_safe_remote_request()` — https://developer.wordpress.org/reference/functions/wp_safe_remote_request/
- `wp_http_validate_url()` — https://developer.wordpress.org/reference/functions/wp_http_validate_url/
- `http_request_host_is_external` — https://developer.wordpress.org/reference/hooks/http_request_host_is_external/
- `http_request_args` — https://developer.wordpress.org/reference/hooks/http_request_args/
- `wp_create_nonce()` (OAuth state/admin callback substrate) — https://developer.wordpress.org/reference/functions/wp_create_nonce/
- `register_rest_route()` (inbound receiving route substrate) — https://developer.wordpress.org/reference/functions/register_rest_route/
- `wp_schedule_single_event()` (retry/health job substrate) — https://developer.wordpress.org/reference/functions/wp_schedule_single_event/
- `update_network_option()` (site/network scope) — https://developer.wordpress.org/reference/functions/update_network_option/
- `wp-config.php` (`WP_PROXY_*` constants) — https://developer.wordpress.org/apis/wp-config-php/
- PHP `hash_equals()` (constant-time comparison) — https://www.php.net/manual/en/function.hash-equals.php
- In-repo: `docs/MODULES/WEBHOOKS-CONNECTIONS-EXHAUSTIVE-SPEC.md`, `docs/PRODUCT/CONNECTIONS-WEBHOOKS-BANK-ENTRY-READINESS-V1.md`

Native classification of the readiness checklist: WP HTTP API request/redirect/TLS behavior (native `WP_Http` arguments; WPE keeps `sslverify` on and revalidates every redirect hop), `wp_safe_remote_*` (rejects some unsafe URLs/hosts but is not a complete SSRF policy: no full metadata/reserved-range, IPv6, DNS-rebinding or cross-host credential-stripping contract), nonce/admin callback for OAuth state (native nonce + capability substrate; PKCE and token lifecycle are WPE/adapter-owned), cron/job primitives (WP-Cron substrate only through the canonical Job service), site/network option storage (native options never hold secret values; Vault references only), REST/AJAX route primitives for inbound endpoints (native substrate; raw-body capture and signature-before-parse ordering are WPE policy), proxy constants (native `WP_PROXY_*` inherited), multisite (per-site/network option scope with secret isolation).

## Seed-record disposition

| Seed record | Native disposition | Notes |
|---|---|---|
| `connections.connection.identity` | **WPE PRODUCT/POLICY** | Required name and stable generated key; changing provider type after use creates a new Connection instead of reinterpreting existing secrets/settings. |
| `connections.connection.provider-adapter` | **WPE PRODUCT/POLICY** | Adapter fixes allowed auth modes, fixed hosts, signature algorithms and OAuth endpoints; Generic HTTP stays bounded by Safe HTTP policy. |
| `connections.connection.status-environment` | **WPE PRODUCT/POLICY** | Enabled/Disabled is stored; Connected/Needs Auth/Unhealthy is derived health; optional production/staging/test environment tag. |
| `connections.connection.list-filters` | **WPE PRODUCT/POLICY** | Safe host/scope summaries, used-by count, credential expiry indicator; duplicate/export always without secrets. |
| `connections.http.base-url` | **NATIVE SUBSTRATE + WPE POLICY** | wp_http_validate_url()/WP_Http substrate parses the URL; WPE adds HTTPS-by-default, scheme/host/port validation and Safe HTTP destination policy. |
| `connections.http.path-prefix-dynamic` | **WPE PRODUCT/POLICY** | Dynamic values only in path segments under a fixed base, query or body; component-aware encoding; traversal normalized against the prefix. |
| `connections.http.methods-headers-query` | **WPE PRODUCT/POLICY** | Header rows name/value source/sensitive reference; Host, Content-Length, adapter-owned Authorization and proxy/internal headers are blocked. |
| `connections.http.timeouts-size` | **NATIVE SUBSTRATE + WPE POLICY** | Maps to WP_Http timeout and limit_response_size arguments; global maximum caps from Settings apply. |
| `connections.http.proxy` | **NATIVE SUBSTRATE + WPE POLICY** | WP_PROXY_* wp-config constants are the native substrate; no per-connection arbitrary proxy definition. |
| `connections.auth.type` | **WPE PRODUCT/POLICY** | API key in query string is discouraged with a leakage warning; every secret-bearing auth type stores Vault references only. |
| `connections.auth.provider-signing` | **DEFERRED / CROSS-OWNER** | Deferred to separate adapter certification and security review; not a generic configurable signer. |
| `connections.secret.vault-reference` | **WPE PRODUCT/POLICY** | Secret values live in the shared Vault; Surface 23 stores references only; UI never reads the full secret back into the browser. |
| `connections.secret.lifecycle` | **WPE PRODUCT/POLICY** | Blank edit keeps existing unless explicit Clear; never preloaded; excluded from export, revision diff, logs and AI context; secret permission separate from Connection edit. |
| `connections.oauth.provider-profile` | **WPE PRODUCT/POLICY** | Defined by the provider adapter, not free-form admin input; no provider password collected unless a reviewed protocol requires it. |
| `connections.oauth.state-callback` | **NATIVE SUBSTRATE + WPE POLICY** | WordPress nonce + capability-checked admin callback is the substrate for state binding; PKCE (RFC 7636) required where the provider supports it. |
| `connections.oauth.lifecycle` | **WPE PRODUCT/POLICY** | Displays connected account safe label, scopes, token expiry, refresh availability and last refresh; disconnect revokes per RFC 7009 where supported. |
| `connections.oauth.scope-changes` | **WPE PRODUCT/POLICY** | Never silently assume an old token has new scopes; show affected modules; keep old connection until new auth succeeds when safe. |
| `connections.connection.test` | **WPE PRODUCT/POLICY** | Read-only by default; write test explicit and cleanup-capable; reports DNS/connect/TLS, auth, scope, provider status, latency; never exposes full headers/body. |
| `connections.ssrf.destination-policy` | **NATIVE SUBSTRATE + WPE POLICY** | wp_safe_remote_request()/http_request_host_is_external substrate is incomplete; WPE blocks loopback, link-local, metadata, RFC1918, reserved/multicast and non-approved schemes after DNS resolution; adapter allowlists for certified fixed hosts. |
| `connections.ssrf.dns-rebinding` | **WPE-EXCEED (no native equivalent)** | No native equivalent; revalidate/pin the resolved address between validation and connect according to accepted HTTP client capability. |
| `connections.private-network.allowlist` | **DEFERRED / CROSS-OWNER** | Later only: disabled by default, dedicated high-risk capability, exact allowlist, metadata ranges always blocked, no arbitrary redirect, re-auth, audited changes. |
| `connections.redirect.policy` | **NATIVE SUBSTRATE + WPE POLICY** | WP_Http redirection argument substrate; every hop revalidated through SSRF policy; Authorization/secret headers stripped on cross-host redirect unless adapter defines safe behavior. |
| `connections.tls.verification` | **NATIVE SUBSTRATE + WPE POLICY** | WP_Http sslverify substrate stays on; TLS errors surfaced distinctly; custom CA bundle is a future reviewed option only. |
| `connections.outbound.request-profile` | **WPE PRODUCT/POLICY** | Connection, relative path, method, headers, query mapping, body schema, accepted statuses, response schema/size, timeout, retry profile, idempotency mapping, log redaction; consumers never copy credentials. |
| `connections.outbound.body-modes` | **WPE PRODUCT/POLICY** | No serialized PHP objects; body preview redacts secrets/PII per schema. |
| `connections.outbound.response-handling` | **WPE PRODUCT/POLICY** | Max bytes, response schema, success status allowlist and selected-field storage; unbounded external responses never stored in workflow logs. |
| `connections.outbound.retry` | **WPE PRODUCT/POLICY** | Attempts, initial/max delay, retryable categories, jitter candidate; scheduling delegated to the canonical Job service (WP-Cron substrate). |
| `connections.outbound.idempotency` | **WPE-EXCEED (no native equivalent)** | Non-idempotent POST requires idempotency support or retry is disabled by default; not a native WordPress HTTP feature. |
| `connections.health.states-circuit` | **WPE-EXCEED (no native equivalent)** | healthy/degraded/auth_expired/rate_limited/unavailable/misconfigured/disabled; cooldown after N failures; never auto-disables permanently without explicit policy; recovery resets. |
| `connections.outbound.delivery-logs` | **WPE PRODUCT/POLICY** | Delivery ID, profile, caller, method + safe host/path, status, attempts, timing, bytes, error category, correlation ID; never Authorization, cookies or secrets. |
| `connections.inbound.identity` | **WPE PRODUCT/POLICY** | Draft/Enabled/Disabled; generated randomized readable path; endpoint obscurity is not authentication. |
| `connections.inbound.route` | **NATIVE SUBSTRATE + WPE POLICY** | register_rest_route() with explicit permission semantics is the substrate; the public REST contract stays with Surface 22; route registration is not authorized by this Bank. |
| `connections.inbound.signature-auth` | **WPE PRODUCT/POLICY** | Secret Vault reference, timestamp/signature headers, adapter-fixed algorithms, replay window; Basic/Bearer only if provider requires; secret URL token never sole auth. |
| `connections.inbound.verification-order` | **WPE PRODUCT/POLICY** | Limit → raw bytes → identify → timestamp/replay → constant-time verify (hash_equals) → reject → parse/schema → event ID → receipt → enqueue. |
| `connections.inbound.replay-idempotency` | **WPE PRODUCT/POLICY** | Bounded receipt identity (provider event ID, endpoint, safe metadata hash, first received, state); duplicates acknowledged without re-running business mutation; retention covers provider retry windows. |
| `connections.inbound.body-schema` | **WPE PRODUCT/POLICY** | Max depth/bytes, required fields, types, array max items; unknown events ignore-acknowledged/store metadata/reject chosen to avoid retry storms. |
| `connections.inbound.event-mapping` | **WPE PRODUCT/POLICY** | Source event → WPE event type, safe payload mappings, transformation allowlist, privacy classification; raw provider payload is not the canonical domain event; Workflow (S17) owns orchestration. |
| `connections.inbound.response-ack` | **WPE PRODUCT/POLICY** | Adapter-defined status and body; prompt acknowledgment with heavy work queued asynchronously. |
| `connections.inbound.rate-size` | **WPE PRODUCT/POLICY** | Source IP allowlist advisory only where provider publishes reliable ranges; signature still required; binary only for certified protocols. |
| `connections.inbound.receipts` | **WPE PRODUCT/POLICY** | HTTP acknowledgment and processing states stay separate; metadata receipts by default; raw body off or short bounded encrypted retention with PII erase rules. |
| `connections.secret.rotation-overlap` | **WPE-EXCEED (no native equivalent)** | Outbound rollback only during bounded overlap; inbound multiple active verification secret versions with created/retire timestamps only when adapter requires; no secret shown after save. |
| `connections.usage.dependency-graph` | **WPE PRODUCT/POLICY** | Workflows, forms, webhooks, backup destinations, notification channels, membership billing, REST endpoints, SDK extensions; disable keeps secrets/data; delete requires no hard dependencies; history follows retention. |
| `connections.portability.import-export` | **WPE PRODUCT/POLICY** | Exports provider/type, non-secret settings, credential placeholder and required scopes; import stays degraded until an admin binds a new Vault credential; package movement owned by Surface 26. |
| `connections.settings.defaults-caps` | **WPE PRODUCT/POLICY** | Timeouts, caps, redirect max, retry, log/raw receipt/replay retention, private network disabled, webhook body max, rate default, health cadence, redaction strictness; caps raised only with higher capability. |
| `connections.permissions.builder` | **WPE PRODUCT/POLICY** | Separate read/manage/credentials/OAuth/test/logs/sensitive receipts/inbound/rotate capabilities plus dedicated private-network capability; Workflow editors select Connections without seeing secrets; AI never retrieves or rotates credentials. |
| `connections.events.diagnostics` | **WPE PRODUCT/POLICY** | Connected/disconnected/auth-expiring/unhealthy/recovered, deduped delivery and receipt events; DNS/SSRF/TLS/auth/scope/rate/provider/Vault/signature/adapter-missing states; Pro expiry never weakens deployed verification. |
| `connections.performance.assets` | **WPE PRODUCT/POLICY** | No external calls on unrelated admin requests; async slow calls; paginated logs; async cleanup; admin assets only on connection screens; no frontend assets for the server-side HTTP engine. |
| `connections.scope.multisite` | **NATIVE OPTIONAL SUBSTRATE, WPE-BOUNDED** | Site vs network option storage (update_network_option substrate); per-site secret isolation; network-shared connections only via explicit network policy. Readiness: docs/PRODUCT/CONNECTIONS-WEBHOOKS-BANK-ENTRY-READINESS-V1.md native audit list. |
| `connections.health.check-cadence` | **NATIVE OPTIONAL SUBSTRATE, WPE-BOUNDED** | WP-Cron/Job substrate only where the canonical Job service allows; deduped health failures; no checks on unrelated requests. |
| `connections.safety.plaintext-secrets` | **REJECTED-UNSAFE CONFIRMED** | Rejected: Vault references only; secrets never stored in plain options or exported. |
| `connections.safety.http-plaintext-default` | **REJECTED-UNSAFE CONFIRMED** | Rejected: plaintext only under a high-risk environment policy, never a normal toggle. |
| `connections.safety.ignore-tls` | **REJECTED-UNSAFE CONFIRMED** | Rejected: a connection test cannot disable verification as a shortcut. |
| `connections.safety.private-destinations-default` | **REJECTED-UNSAFE CONFIRMED** | Rejected: blocked by default; private networks only via the deferred high-risk allowlist; metadata never. |
| `connections.safety.cross-host-auth-redirect` | **REJECTED-UNSAFE CONFIRMED** | Rejected: every hop revalidated, credentials stripped cross-host. |
| `connections.safety.dynamic-host` | **REJECTED-UNSAFE CONFIRMED** | Rejected: fixed base host; dynamic values only in path/query/body. |
| `connections.safety.static-auth-header` | **REJECTED-UNSAFE CONFIRMED** | Rejected: auth adapter/Vault references only. |
| `connections.safety.unbounded-storage` | **REJECTED-UNSAFE CONFIRMED** | Rejected: explicit byte caps and selected-field storage only. |
| `connections.safety.retry-non-idempotent` | **REJECTED-UNSAFE CONFIRMED** | Rejected: retry disabled by default unless idempotency is supported. |
| `connections.safety.unsigned-webhook-processing` | **REJECTED-UNSAFE CONFIRMED** | Rejected where the provider supports signatures; IP allowlists never replace signatures. |
| `connections.safety.weak-signature-algorithm` | **REJECTED-UNSAFE CONFIRMED** | Rejected: algorithms fixed by the provider adapter. |
| `connections.safety.reserialize-before-verify` | **REJECTED-UNSAFE CONFIRMED** | Rejected: verify exact raw bytes first. |
| `connections.safety.replay-rerun` | **REJECTED-UNSAFE CONFIRMED** | Rejected: duplicates acknowledged and counted only. |
| `connections.safety.raw-body-indefinite` | **REJECTED-UNSAFE CONFIRMED** | Rejected: off or short bounded encrypted retention only. |
| `connections.safety.credential-logging` | **REJECTED-UNSAFE CONFIRMED** | Rejected: metadata-first redacted logs; events carry safe metadata only. |
| `connections.safety.ai-credential-access` | **REJECTED-UNSAFE CONFIRMED** | Rejected: AI limited to read/explain/test safe metadata. |

## Native completeness / unresolved items

No additional Connections/Webhooks V1 Bank family is required. Native unresolved items: **0**. Vault secret storage, OAuth client lifecycle, SSRF/DNS-rebinding policy, webhook signature verification, replay receipts, idempotency, health circuit and delivery/receipt logs have no native WordPress equivalent and remain WPE/market-owned.

## Gate boundary

Not promoted here: outbound HTTP execution, OAuth, credential storage, webhook route registration, runtime, Atomic Option Contracts, UX contract closure or product parity.
