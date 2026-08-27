# WPEssential — Connections, Webhooks, Event Inbox & SSRF Architecture

Status: **Phase 0 security/runtime architecture / no implementation authorized**  
Related: Webhooks & Connections exhaustive spec, Workflow, Membership billing adapters, REST, Vault, Job Service.

## 1. Shared-service boundary

Connections/Webhooks owns reusable external-I/O primitives:

1. **Connection Definition** — provider/endpoint/auth configuration.
2. **Secrets references** — Vault-owned credentials.
3. **Safe Outbound HTTP Service** — central request policy/SSRF enforcement.
4. **Inbound Webhook Gateway** — route/body/signature/replay verification.
5. **Normalized Event Inbox** — durable verified event receipt/idempotency/replay metadata.
6. **Outbound Webhook Delivery** — signed/retryable delivery jobs.
7. **Provider Adapters** — OAuth, signatures, normalization, health/reconciliation.

Modules do not independently invent HTTP/security/webhook inboxes.

## 2. Connection Definition

Definition Repository stores non-secret configuration:
- UUID/key/name;
- adapter/provider type/version;
- endpoint/origin policy;
- auth method descriptor;
- Vault secret references;
- scopes/permissions;
- timeout/retry limits;
- rate-limit profile;
- redirect policy;
- TLS requirements;
- health-check descriptor;
- privacy/data-classification;
- allowed operations.

Credentials/API tokens/private keys are never ordinary Definition JSON values.

## 3. Endpoint trust modes

### Provider-fixed
Adapter supplies approved service origins/domains. Preferred.

### Explicit allowlist
Admin selects/configures one or more approved public origins/domains under a typed policy.

### Custom public URL
Advanced outbound webhook/API case. Requires strict public HTTP(S) validation.

### Internal/private endpoint
Off by default and not equivalent to normal custom URL. If product later supports on-prem/internal connections, it requires an explicit trusted CIDR/origin policy and separate security warning/capability because standard SSRF protections intentionally reject private/link-local targets.

## 4. Safe outbound HTTP

All WPE modules call registered Safe HTTP/Connection service, not raw arbitrary `wp_remote_request()`.

Baseline for arbitrary/custom URL:
- `http`/`https` only;
- no URL-embedded username/password;
- validate host/port/path according to adapter policy;
- use WordPress safe HTTP validation (`wp_safe_remote_*` / `reject_unsafe_urls`) where applicable;
- reject loopback/private/link-local/reserved destinations in public mode;
- block cloud metadata service ranges/hosts through general non-public-IP policy;
- bounded timeout;
- bounded response bytes;
- allowed methods/headers;
- no local file/socket schemes;
- TLS certificate validation;
- privacy-safe User-Agent/correlation ID.

## 5. Redirect policy

For arbitrary/custom outbound targets:
- redirects **off/0 by default candidate**;
- provider adapter may permit bounded redirects only to validated/approved target origins;
- every redirect hop must pass safety validation;
- protocol downgrade HTTPS→HTTP rejected unless explicitly accepted in an environment-specific adapter policy;
- authentication headers/secrets are not forwarded to unrelated redirected origin.

Connection-time validation alone is insufficient; request-time target validation remains required.

## 6. DNS / rebinding

Do not trust a domain merely because it resolved to public IP during setup.

At request time:
- safe WordPress HTTP URL validation participates;
- provider allowlists compare expected domain/origin;
- redirects revalidate;
- custom network layer, if ever introduced, must validate all A/AAAA results and prevent rebinding to forbidden ranges.

WPE does not claim perfect network isolation against a fully compromised host/DNS stack.

## 7. Request construction

Connection operation descriptor controls:
- method;
- path/template;
- typed query/body parameters;
- allowed headers;
- content type;
- response schema;
- timeout/retry;
- idempotency key support.

User data cannot set arbitrary headers such as `Host`, authorization, proxy or redirect control unless adapter explicitly defines safe behavior.

Secrets are injected only by Connection service at execution time.

## 8. Response handling

Before consumer receives response:
- enforce maximum bytes;
- verify status/content type;
- decode with depth/item limits;
- schema validate where adapter declares schema;
- redact secrets in logs/errors;
- normalize provider errors;
- retry only safe/idempotent operations.

No remote response can inject executable PHP/JS/config automatically.

---

# Inbound Webhooks

## 9. Webhook endpoint identity

Each inbound endpoint has:
- Connection/provider UUID;
- adapter event family;
- endpoint route/reference;
- active/paused state;
- expected method/content type;
- body size limit;
- signature profile;
- replay window/idempotency rules;
- optional IP/network supplemental policy;
- rate-limit profile.

A random endpoint URL alone is not strong authentication.

## 10. Verification order

Candidate inbound flow:
1. route/method/rate/size precheck;
2. read bounded raw request bytes;
3. resolve Connection/signature key ref;
4. verify provider signature/MAC over exact required raw representation;
5. verify timestamp/replay window where provider supports;
6. derive/check provider event ID/idempotency identity;
7. record verified/rejected receipt safely;
8. only then parse/normalize business payload;
9. publish normalized event to Event Inbox/consumers;
10. respond provider-appropriate success/failure quickly;
11. perform non-critical business work asynchronously.

Do not JSON-decode/re-serialize before a signature algorithm that requires raw body verification.

## 11. Signature profiles

Provider adapter declares exact algorithm/headers/canonicalization.

Supported conceptual classes:
- provider HMAC/signature;
- asymmetric signature;
- shared webhook secret token where no better provider support;
- mTLS/IP allowlist only as supplemental/provider-specific mechanism.

No universal “one HMAC parser for every provider”.

Signature secret/private credential is Vault-owned.

## 12. Secret rotation

Webhook connection can support current + previous secret/key overlap for bounded provider rotation.

Each verification records which key ID/version matched without logging secret.

Expired previous key removed after safe overlap.

## 13. Replay/idempotency

Where provider supplies event ID:
- unique Connection/provider event identity persisted;
- duplicate receipt does not repeat non-idempotent domain transition.

Where no event ID:
- adapter may derive bounded hash identity over provider-specified stable fields/raw body + time window;
- weaker idempotency is documented.

Timestamp/replay-window checks cannot replace permanent idempotency for legitimately retried events.

## 14. Normalized Event Inbox

Runtime record separates **receipt** from **consumer processing**.

Candidate fields:
- inbox event UUID;
- Connection UUID;
- provider/adapter/version;
- provider event ID;
- event type;
- received/verified timestamps;
- provider event timestamp;
- signature verification result/key ID;
- idempotency key;
- normalized schema version;
- normalized safe payload/reference;
- raw payload retention reference optional;
- status;
- dispatch/consumer counts;
- correlation ID.

## 15. Event states

Candidate:
- received;
- rejected_unverified;
- verified;
- duplicate;
- normalization_failed;
- ready;
- dispatched;
- processed;
- processed_with_errors;
- dead_letter/manual_review.

One consumer failing does not rewrite cryptographic fact that webhook was verified.

## 16. Raw payload retention

Minimize by default.

Options depend on provider/support needs:
- do not retain after successful normalization;
- retain bounded encrypted/private raw payload for short troubleshooting/replay period;
- retain hash + normalized event only.

Never retain payment secrets/card data merely because provider webhook included fields WPE did not need.

Privacy classification/retention is adapter + site policy.

## 17. Consumer dispatch

Consumers subscribe to normalized typed event families, e.g.:
- `commerce.order.paid`;
- `billing.subscription.updated`;
- `connection.webhook.received`;
- provider-specific event where semantic normalization impossible.

Dispatch is at-least-once. Consumers require idempotency.

Membership adapter consumes normalized event; raw provider webhook receipt never directly authorizes protected access.

## 18. Reconciliation

Webhook delivery can be lost/out of order.

Provider adapters may expose reconciliation operation:
- query remote source of truth;
- compare latest subscription/order/connection state;
- repair normalized domain state safely;
- record reconciliation reference/result.

Webhook is event signal, not universal source of truth for every provider.

---

# Outbound Webhooks

## 19. Outbound webhook definition

Fields:
- destination Connection/URL policy;
- event trigger;
- condition;
- payload schema/mapping;
- headers allowlist;
- WPE signature profile;
- timeout;
- retry/backoff;
- idempotency key;
- response-success criteria;
- pause/failure policy.

## 20. WPE outbound signature

Candidate WPE-controlled profile can sign:
- event/delivery ID;
- timestamp;
- raw payload digest/body according to canonical profile;
- key ID.

Receiver secret/key is Vault-owned.

Exact HMAC/asymmetric profile remains crypto implementation work.

## 21. Delivery state

Separate attempt records:
- queued;
- sending;
- accepted/success HTTP;
- retry scheduled;
- failed terminal;
- cancelled;
- unknown outcome.

HTTP 2xx is receiver HTTP acceptance, not proof downstream business processing succeeded unless receiver contract explicitly says so.

## 22. Retry

Retry only after classifying outcome:
- safe retry on network timeout/selected 5xx/429 according to policy;
- honor Retry-After where applicable;
- use same idempotency/delivery ID;
- bounded attempts/backoff/jitter;
- no infinite retry storm.

Unknown outcome stays visible; do not automatically generate a new semantic event ID.

## 23. Test connection / test webhook

Test is explicitly non-production:
- synthetic marked payload;
- dedicated capability;
- does not trigger real business workflow state;
- target safety policy still enforced;
- response/logs redacted.

## 24. OAuth connections

OAuth provider adapters use platform account/connection auth standards but remain provider-specific.

Rules:
- no static confidential secret pretending secrecy in distributed plugin when architecture cannot protect it;
- access token server-side only;
- refresh token Vault;
- scopes least privilege;
- refresh/revoke state machine;
- disconnected credential not kept usable locally;
- provider callback exact/trusted.

## 25. Connection health

Health states candidate:
- unconfigured;
- healthy;
- degraded;
- auth_expiring/refresh_failed;
- revoked;
- rate_limited;
- endpoint_invalid;
- provider_unavailable;
- permission_scope_insufficient;
- disabled.

Health check itself respects rate/cost and does not run on every admin page.

## 26. Permissions

Separate capabilities:
- view connection metadata;
- create/edit connection;
- manage credentials;
- authorize OAuth;
- test;
- inspect webhook event metadata;
- view retained raw payload (higher privilege);
- replay event;
- delete/prune event;
- manage outbound webhooks.

Raw payload/secrets are not exposed to ordinary module managers.

## 27. Replay tooling

Replay means **re-dispatch an already verified normalized event** by default, not pretend provider sent a new event.

Rules:
- preserve original event ID;
- new replay-run ID;
- show consumer effects;
- permission + confirmation;
- consumers still idempotent;
- raw unverified payload cannot be promoted to verified by replay.

## 28. Observability

Log safe metadata:
- connection/provider;
- request/event/delivery ID;
- endpoint origin masked/path class;
- method/status;
- duration/bytes;
- retry;
- signature result;
- normalized error;
- correlation ID.

Never log Authorization headers, API keys, webhook secret, full sensitive bodies.

## 29. Failure isolation

- one broken connection does not block unrelated modules;
- webhook consumer failure does not make endpoint keep retrying synchronously until PHP timeout;
- queue/job outage leaves verified event durable for later dispatch;
- provider outage surfaces degraded health;
- Vault unavailable blocks secret-dependent calls but non-secret WPE features continue.

## 30. Future executable evidence — NOT AUTHORIZED

After explicit consent:
- SSRF private/link-local/IPv6/redirect/DNS rebinding fixtures;
- `wp_safe_remote_request` behavior across supported WP versions;
- provider-fixed and custom URL policies;
- body/response size limits;
- signature raw-body fixtures;
- timestamp/replay/duplicate/out-of-order;
- secret rotation;
- queue crash/replay;
- Woo/SureCart/provider webhook adapters;
- outbound idempotency/retry/unknown outcome;
- OAuth refresh/revoke;
- multisite isolation.

## Paper recommendation

Accept a centralized **Safe HTTP + Verified Webhook Gateway + Normalized Event Inbox** as the only standard external-I/O path for WPE modules, with provider adapters and strict public-network defaults.