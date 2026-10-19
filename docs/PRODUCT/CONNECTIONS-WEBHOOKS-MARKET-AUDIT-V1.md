# Connections/Webhooks — Market Audit V1

Status: `MARKET_AUDITED` (integrated in `config/product/options-bank-audits/connections-market-ecosystem.json`)

Surface: 23 — Connections/Webhooks
Snapshot: 2026-10-19
Issue: #1448

## 1. Scope and boundary

Reconciles all 65 Connections/Webhooks records. Surface 23 owns connection/provider/auth profiles, Safe HTTP, OAuth lifecycle, webhook transport and delivery/receipt evidence. The shared Vault owns secret values; Forms & Workflows (S17) owns business orchestration; Notifications (S19) routing; Backup (S24) artifacts; REST API (S22) the public endpoint contract; Sync (S41) cursors/conflicts; Import/Export (S26) package movement.

## 2. Evidence

- **E1 WP Webhooks** — https://wordpress.org/plugins/wp-webhooks/ — send/receive webhooks, outgoing data to admin-entered URLs, incoming webhook URLs, logs and automation flows.
- **E2 Uncanny Automator** — https://wordpress.org/plugins/uncanny-automator/, https://automatorplugin.com/ — app connections (account connect via vendor), webhook triggers/actions and recipe logs.
- **E3 n8n** — https://docs.n8n.io/credentials/, https://docs.n8n.io/integrations/builtin/core-nodes/n8n-nodes-base.httprequest/, https://docs.n8n.io/integrations/builtin/core-nodes/n8n-nodes-base.webhook/ — reusable encrypted credentials (API key, OAuth2, provider signing), HTTP Request options (timeouts, redirects, proxy, retries, "ignore SSL issues"), webhook node auth/response modes.
- **E4 Zapier Webhooks** — https://zapier.com/apps/webhook/integrations — catch hooks (unique URL), POST/GET/PUT actions and custom requests.
- **S1 WooCommerce Webhooks** — https://woocommerce.com/document/webhooks/ — HMAC-SHA256 delivery signature, secret, delivery logs and auto-disable after repeated failures.
- **S2 Stripe Webhooks** — https://docs.stripe.com/webhooks — raw-body signature verification, timestamp tolerance, duplicate events, quick 2xx acknowledgment, secret rolling.
- **S3 GitHub Validating Webhook Deliveries** — https://docs.github.com/en/webhooks/using-webhooks/validating-webhook-deliveries — HMAC-SHA256 with constant-time comparison.
- **S4 Standard Webhooks** — https://www.standardwebhooks.com/ — webhook-id/timestamp/signature headers, multiple signatures for rotation, idempotent delivery.
- **S5 OWASP SSRF Prevention Cheat Sheet** — https://cheatsheetseries.owasp.org/cheatsheets/Server_Side_Request_Forgery_Prevention_Cheat_Sheet.html — allowlists, blocked ranges, DNS rebinding, redirect handling.
- **S6 OAuth standards** — RFC 6749, RFC 7636 (PKCE), RFC 7009 (revocation), RFC 9700 (OAuth 2.0 Security BCP).

## 3. Record reconciliation

| Record | Disposition |
|---|---|
| `connections.connection.identity` | MARKET_EVIDENCED / WPE_HARDENED |
| `connections.connection.provider-adapter` | MARKET_EVIDENCED / WPE_HARDENED |
| `connections.connection.status-environment` | MARKET_EVIDENCED / WPE_HARDENED |
| `connections.connection.list-filters` | MARKET_EVIDENCED / WPE_HARDENED |
| `connections.http.base-url` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `connections.http.path-prefix-dynamic` | MARKET_EVIDENCED / WPE_HARDENED |
| `connections.http.methods-headers-query` | MARKET_EVIDENCED / WPE_HARDENED |
| `connections.http.timeouts-size` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `connections.http.proxy` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `connections.auth.type` | MARKET_EVIDENCED / WPE_HARDENED |
| `connections.auth.provider-signing` | KEEP DEFERRED — market demand exists; separate security review/certification required |
| `connections.secret.vault-reference` | MARKET_EVIDENCED / WPE_HARDENED |
| `connections.secret.lifecycle` | MARKET_EVIDENCED / WPE_HARDENED |
| `connections.oauth.provider-profile` | MARKET_EVIDENCED / WPE_HARDENED |
| `connections.oauth.state-callback` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `connections.oauth.lifecycle` | MARKET_EVIDENCED / WPE_HARDENED |
| `connections.oauth.scope-changes` | MARKET_EVIDENCED / WPE_HARDENED |
| `connections.connection.test` | MARKET_EVIDENCED / WPE_HARDENED |
| `connections.ssrf.destination-policy` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `connections.ssrf.dns-rebinding` | KEEP WPE_EXCEED — standards/specialist-evidenced, not common in WordPress integration plugins |
| `connections.private-network.allowlist` | KEEP DEFERRED — market demand exists; separate security review/certification required |
| `connections.redirect.policy` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `connections.tls.verification` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `connections.outbound.request-profile` | MARKET_EVIDENCED / WPE_HARDENED |
| `connections.outbound.body-modes` | MARKET_EVIDENCED / WPE_HARDENED |
| `connections.outbound.response-handling` | MARKET_EVIDENCED / WPE_HARDENED |
| `connections.outbound.retry` | MARKET_EVIDENCED / WPE_HARDENED |
| `connections.outbound.idempotency` | KEEP WPE_EXCEED — standards/specialist-evidenced, not common in WordPress integration plugins |
| `connections.health.states-circuit` | KEEP WPE_EXCEED — standards/specialist-evidenced, not common in WordPress integration plugins |
| `connections.outbound.delivery-logs` | MARKET_EVIDENCED / WPE_HARDENED |
| `connections.inbound.identity` | MARKET_EVIDENCED / WPE_HARDENED |
| `connections.inbound.route` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `connections.inbound.signature-auth` | MARKET_EVIDENCED / WPE_HARDENED |
| `connections.inbound.verification-order` | MARKET_EVIDENCED / WPE_HARDENED |
| `connections.inbound.replay-idempotency` | MARKET_EVIDENCED / WPE_HARDENED |
| `connections.inbound.body-schema` | MARKET_EVIDENCED / WPE_HARDENED |
| `connections.inbound.event-mapping` | MARKET_EVIDENCED / WPE_HARDENED |
| `connections.inbound.response-ack` | MARKET_EVIDENCED / WPE_HARDENED |
| `connections.inbound.rate-size` | MARKET_EVIDENCED / WPE_HARDENED |
| `connections.inbound.receipts` | MARKET_EVIDENCED / WPE_HARDENED |
| `connections.secret.rotation-overlap` | KEEP WPE_EXCEED — standards/specialist-evidenced, not common in WordPress integration plugins |
| `connections.usage.dependency-graph` | MARKET_EVIDENCED / WPE_HARDENED |
| `connections.portability.import-export` | MARKET_EVIDENCED / WPE_HARDENED |
| `connections.settings.defaults-caps` | MARKET_EVIDENCED / WPE_HARDENED |
| `connections.permissions.builder` | MARKET_EVIDENCED / WPE_HARDENED |
| `connections.events.diagnostics` | MARKET_EVIDENCED / WPE_HARDENED |
| `connections.performance.assets` | MARKET_EVIDENCED / WPE_HARDENED |
| `connections.scope.multisite` | MARKET_EVIDENCED_WITH_BOUNDS |
| `connections.health.check-cadence` | MARKET_EVIDENCED_WITH_BOUNDS |
| `connections.safety.plaintext-secrets` | KEEP `REJECTED_UNSAFE` |
| `connections.safety.http-plaintext-default` | KEEP `REJECTED_UNSAFE` |
| `connections.safety.ignore-tls` | KEEP `REJECTED_UNSAFE` |
| `connections.safety.private-destinations-default` | KEEP `REJECTED_UNSAFE` |
| `connections.safety.cross-host-auth-redirect` | KEEP `REJECTED_UNSAFE` |
| `connections.safety.dynamic-host` | KEEP `REJECTED_UNSAFE` |
| `connections.safety.static-auth-header` | KEEP `REJECTED_UNSAFE` |
| `connections.safety.unbounded-storage` | KEEP `REJECTED_UNSAFE` |
| `connections.safety.retry-non-idempotent` | KEEP `REJECTED_UNSAFE` |
| `connections.safety.unsigned-webhook-processing` | KEEP `REJECTED_UNSAFE` |
| `connections.safety.weak-signature-algorithm` | KEEP `REJECTED_UNSAFE` |
| `connections.safety.reserialize-before-verify` | KEEP `REJECTED_UNSAFE` |
| `connections.safety.replay-rerun` | KEEP `REJECTED_UNSAFE` |
| `connections.safety.raw-body-indefinite` | KEEP `REJECTED_UNSAFE` |
| `connections.safety.credential-logging` | KEEP `REJECTED_UNSAFE` |
| `connections.safety.ai-credential-access` | KEEP `REJECTED_UNSAFE` |

Coverage: **65 / 65**; 140 Bank references; 5 extra dispositions; unresolved **0**; record delta after market audit **0**.

## 4. Rejected market patterns

- Unique catch-hook URL as the only inbound authentication (E4) → `connections.safety.unsigned-webhook-processing`.
- "Ignore SSL issues" request option (E3) → `connections.safety.ignore-tls`.
- Sending data to any admin-entered URL without destination policy (E1) → `connections.safety.private-destinations-default`, `connections.safety.dynamic-host`.
- Vendor-hosted OAuth account brokers (E2) are an implementation pattern for provider adapters, not a Surface 23 record; tokens still resolve to Vault references.
- Multi-step workflow orchestration (E3) is out of surface and owned by Surface 17 Forms & Workflows.

No outbound HTTP execution, OAuth flow, credential storage, webhook route registration, runtime or product parity is authorized.
