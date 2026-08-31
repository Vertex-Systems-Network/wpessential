# WPEssential — Webhooks & Connections Manager Exhaustive Option Specification

Status: **Phase 0 exhaustive product specification / no implementation authorized**

## 1. Purpose

Centralize reusable external-service connections and inbound/outbound webhooks so Forms, Workflow, Backup, Notifications, Membership and REST do not each invent credentials, OAuth, retries or HTTP security.

Separate:
- Connection Definition — destination/provider/auth configuration
- Credential/Vault Secret — secret values
- Outbound Request Profile — reusable request settings
- Inbound Webhook Endpoint — trusted receiving definition
- Delivery/Receipt — operational attempt/history

---

# 2. Screens

- Connections
- Create/Edit Connection
- OAuth Connections
- Inbound Webhooks
- Outbound Deliveries
- Inbound Receipts
- Connection Health
- Settings
- Diagnostics

---

# 3. Connections list

Columns:
- Name
- Key
- Type/provider
- Auth type
- Base host safe summary
- Status Connected/Needs Auth/Unhealthy/Disabled
- Scopes safe summary
- Used by count
- last test/use
- credential expiry indicator
- updated
- actions

Filters:
- enabled/disabled
- provider/type
- OAuth/API key/basic/etc.
- healthy/unhealthy
- credential expiring
- used/unused

Actions:
- Edit
- Test
- Reconnect/Reauthorize
- Rotate credential
- Usage
- Duplicate without secrets
- Export definition without secrets
- Disable
- Archive/Delete

---

# 4. Connection identity

Fields:
- Name required
- Key stable
- Description
- Type/provider adapter
- Status Enabled/Disabled
- Environment tag optional: production/staging/test
- Tags optional

Changing provider type after use should normally create new Connection rather than reinterpret existing secrets/settings.

---

# 5. Generic HTTP connection

Fields:
- Base URL required HTTPS by default
- optional allowed path prefix
- default method restrictions
- default headers non-secret
- default query params non-secret
- content type
- connect timeout
- request timeout
- response max bytes
- redirect policy
- TLS verification required
- proxy handling inherited from WordPress/approved adapter

HTTP plaintext disabled by default; enabling for explicit local development/private use would require high-risk environment policy, not ordinary production toggle.

---

# 6. Authentication types

Provider/connection may support:
- None
- API key header
- API key query — discouraged, warning due URL/log leakage
- Bearer token
- Basic Auth
- OAuth 2.x provider adapter
- HMAC request signing adapter
- AWS SigV4/provider-specific signing
- custom certified auth adapter

Secret fields store Vault references only.

UI after save shows masked/present state, never reads full secret back into browser.

---

# 7. Secret field behavior

Controls:
- Add/replace secret
- Clear secret
- Rotate
- Test after replace
- Last changed timestamp
- Vault health

Rules:
- blank edit field means keep existing unless explicit Clear
- never preload secret value
- secrets excluded from export, revisions diff, logs and AI context
- secret permission separate from normal Connection edit where appropriate

---

# 8. OAuth connection

Provider adapter defines:
- authorization URL
- token URL
- PKCE/client profile where applicable
- scopes
- callback strategy
- refresh semantics
- token revocation
- account/resource identity response

Admin controls:
- Connect
- requested scopes preview
- account/provider consent handoff
- Reauthorize
- Refresh status
- Disconnect

Display:
- connected account safe label
- scopes
- token expiry
- refresh available yes/no
- last refresh

No provider password collected/stored by WPE unless a specific reviewed protocol requires it.

---

# 9. OAuth scope changes

If connection definition requests new scopes:
- mark reauthorization required
- do not silently assume old token has scope
- show affected modules/actions
- preserve old connection until new auth succeeds when safe

Reducing scopes may require reauthorization/revocation depending provider.

---

# 10. Connection test

Provider-specific safe test only.

Examples:
- authenticated account/profile ping
- bucket/list permission probe
- send-to-test endpoint

Test options:
- read-only default
- write test only explicitly and cleanup-capable

Result:
- DNS/connect/TLS
- auth
- permission/scope
- provider status
- latency
- safe account/resource summary

Do not expose full response headers/body when they may contain secrets.

---

# 11. SSRF protection — outbound

User-configured URL/redirects are untrusted.

Default block targets include:
- loopback
- link-local
- cloud metadata addresses
- RFC1918/private network ranges
- reserved/broadcast/multicast ranges
- unix/file/gopher/etc. non-approved schemes

Validation considers:
- parsed scheme/host/port
- DNS resolution
- IPv4/IPv6
- redirect destinations
- DNS rebinding/change between validation/connect according to accepted HTTP client capability

Provider-certified fixed hosts can use adapter allowlists.

---

# 12. Private network connections — advanced candidate

Some enterprise WordPress sites may genuinely need private APIs.

If supported later:
- disabled by default
- dedicated high-risk capability
- explicit exact CIDR/host allowlist
- no cloud metadata ranges
- no arbitrary redirect
- re-auth + warning
- audit every allowlist change

Not included merely because user is administrator.

---

# 13. Redirect policy

Options:
- no redirects — safest default for sensitive webhook/API profiles where possible
- same-host redirects
- allowlisted hosts
- bounded max count

Every redirect target revalidated through SSRF policy.

Authorization/secret headers are not forwarded to a different host unless provider adapter explicitly defines safe behavior.

---

# 14. TLS

- certificate validation required
- hostname verification required
- no “ignore SSL errors” normal production toggle
- custom CA bundle/enterprise trust only future reviewed option
- TLS errors surfaced distinctly

A Test Connection cannot permanently disable verification as a shortcut.

---

# 15. Reusable outbound request profile

Fields:
- Name/key
- Connection
- relative path
- method
- headers
- query mapping
- body schema/template
- accepted status codes
- response schema/size
- timeout
- retry profile
- idempotency header/key mapping
- log redaction

Used by Workflow/Forms/Notifications without copying credentials.

---

# 16. Dynamic URL/path values

Prefer dynamic values only in:
- path segments under fixed base
- query params
- body

Dynamic host disabled by default.

Path traversal normalized/restricted when Connection defines path prefix.

URL encoding is component-aware; do not concatenate unescaped user input.

---

# 17. Outbound request headers

Header rows:
- name
- value source static/token
- sensitive toggle/reference
- override provider default

Blocked/controlled:
- Host
- Content-Length
- Authorization when auth adapter owns it
- proxy/internal headers

Secrets use Vault/auth adapter, not visible static strings.

---

# 18. Request body

Modes:
- JSON schema/mapping
- form-urlencoded
- multipart certified action
- raw text only bounded explicit

No arbitrary serialized PHP object.

Body preview redacts secrets/PII according to schema.

---

# 19. Response handling

Options:
- ignore body
- parse JSON
- parse text bounded
- file/binary only for certified adapter (backup/download etc.)

Fields:
- max bytes
- response schema
- extract mappings
- success status allowlist
- store selected fields

Never store unbounded external response in workflow logs.

---

# 20. Retry

Profiles:
- none
- fixed
- exponential
- provider recommended/Retry-After aware

Fields:
- attempts
- initial/max delay
- retryable statuses/categories
- jitter candidate

Non-idempotent POST requires idempotency support or retry disabled by default.

---

# 21. Circuit health / repeated failures

Connection health states:
- healthy
- degraded
- auth_expired
- rate_limited
- unavailable
- misconfigured
- disabled

Optional circuit behavior:
- after N failures defer/pause repeated job attempts for cooldown
- does not permanently disable connection automatically without explicit policy
- successful health check/recovery resets

Exact circuit-breaker implementation later.

---

# 22. Outbound delivery logs

Columns:
- delivery ID
- Connection/profile
- caller module/workflow
- method + safe host/path
- status
- attempts
- queued/start/end
- bytes
- error category
- correlation ID

Detail:
- safe request metadata
- redacted headers/body fields
- response status/selected safe excerpt
- retry history

Never log Authorization/cookies/secrets.

---

# 23. Inbound Webhooks list

Columns:
- Name
- Key
- Status
- path/endpoint safe display
- auth/signature mode
- event mapping
- last receipt
- recent failures/replays
- rate profile
- usage

Actions:
- Edit
- Copy endpoint URL
- Rotate signing secret
- Test fixture
- Receipts
- Enable/Disable
- Revisions
- Export without secret
- Archive/Delete

---

# 24. Inbound webhook identity

Fields:
- Name
- Key
- path generated/randomized readable key
- Status Draft/Enabled/Disabled
- provider adapter or Generic
- description

Endpoint obscurity is not authentication.

---

# 25. Inbound authentication/signature

Modes by adapter:
- HMAC header signature
- provider-specific signature
- asymmetric signature
- Basic/Bearer only if reviewed provider requires
- secret URL token discouraged as sole auth
- no auth allowed only for explicit low-risk public event with strict abuse controls

Fields:
- secret/Vault reference
- timestamp header
- signature header
- allowed algorithms fixed by adapter
- replay window

Never allow user-selected weak hash algorithm when provider profile defines secure one.

---

# 26. Signature verification ordering

Preferred:
1. enforce method/content-length limit
2. capture exact raw body bytes needed for provider signature
3. identify Connection/provider
4. validate timestamp/replay window
5. verify signature constant-time/approved library
6. reject before JSON/action processing on failure
7. parse/schema validate
8. derive provider event ID/idempotency
9. persist normalized receipt
10. enqueue consumer event/action

Do not reserialize JSON before verifying a provider signature that signs raw bytes.

---

# 27. Replay/idempotency

Store bounded receipt identity:
- provider event ID
- endpoint
- signature/timestamp metadata hash safe subset
- first received
- processing state

Duplicate:
- return provider-appropriate success/duplicate response where needed
- do not re-run business mutation
- surface in receipts count

Replay retention must cover provider retry/replay expectations.

---

# 28. Inbound body schema

Generic webhook can define:
- JSON max depth/bytes
- required fields
- types
- array max items
- event type selector path
- event ID path
- timestamp path

Unknown events:
- ignore acknowledged
- store unhandled metadata
- reject

Choice depends provider retry behavior; avoid accidental retry storms.

---

# 29. Inbound event mapping

Map verified webhook to:
- normalized WPE Event
- Workflow trigger
- Membership billing adapter consumer
- custom registered handler

Fields:
- source event type → WPE event type
- safe payload mappings
- transformation allowlist
- privacy classification

Raw provider payload is not the canonical domain event.

---

# 30. Inbound response

Provider adapter defines:
- expected status code
- response body/schema
- synchronous acknowledgment deadline

Heavy work queued asynchronously after valid receipt; webhook HTTP request should acknowledge promptly.

Do not make provider wait for full backup/import/workflow completion.

---

# 31. Inbound rate/size controls

- method allowlist
- max body bytes
- request rate
- source IP advisory/allowlist only if provider publishes reliable ranges
- concurrent receipt cap
- JSON depth
- attachment/binary not accepted unless certified provider protocol

Signature is still required even with IP allowlist when provider supports signatures.

---

# 32. Receipt states

- received
- signature_failed
- replay_rejected
- schema_rejected
- accepted
- queued
- processed
- processing_failed_retryable
- processing_failed
- ignored_unknown_event

HTTP acknowledgment and downstream business processing states remain separate.

---

# 33. Receipt logs

Default store:
- receipt ID
- endpoint/provider
- event ID/type
- received time
- verification result
- payload bytes size
- processing result/correlation

Raw body retention:
- off or short bounded encrypted/sensitive retention candidate
- only when reconciliation/debugging needs it
- PII classification and erase/retention rules

Do not retain payment/provider payload forever by default.

---

# 34. Secret rotation

Outbound credential rotation:
- replace secret/token
- test
- rollback old credential only during bounded overlap if provider supports

Inbound webhook secret rotation:
- provider may allow old+new overlap
- WPE supports multiple active verification secret versions only when adapter requires
- each has version/created/retire timestamp
- retire old after provider cutover

No secret shown after save.

---

# 35. Connection usage/dependency graph

Shows every:
- workflow action
- form
- webhook endpoint
- backup destination
- notification channel
- membership billing adapter
- REST endpoint
- SDK extension

Deleting/disable warns exact breakage.

---

# 36. Disable/delete

Disable:
- blocks new outbound use/inbound acceptance according safe status
- does not delete secret/data
- dependent definitions degraded

Delete:
- requires no hard dependencies or mapping
- secret deletion separate but normally included after dependency resolution
- delivery/receipt history retained by retention policy

---

# 37. Import/export

Connection definition export includes:
- provider/type
- non-secret settings
- credential placeholder ID/name
- required scopes/capabilities

Never exports secret/token by default.

Import creates `credential_required` degraded state until admin binds new Vault credential.

---

# 38. Settings

- default HTTP timeouts
- maximum timeout cap
- response max bytes
- redirect max
- default retry profile
- log retention
- raw receipt retention default off/short
- private-network connections disabled
- webhook body max
- replay retention
- rate-limit default
- health-check cadence
- sensitive log redaction strictness

Safety caps cannot be increased without higher capability where abuse/security risk exists.

---

# 39. Permissions

- read Connections safe metadata
- create/update/delete
- manage credentials
- OAuth reconnect
- test connection
- view delivery logs
- view sensitive receipt diagnostics
- manage inbound endpoints
- rotate secrets
- allow private-network destinations — high-risk dedicated capability

Ordinary Workflow editor can select an allowed Connection without seeing its secrets.

---

# 40. Abilities

- connection list/get/create/update/validate/test/enable/disable
- credential replace/clear only high-risk, not AI exposed
- outbound request run via registered profile/action
- webhook list/get/create/update/enable/disable
- delivery/receipt list/get/retry where safe
- health explain/test

AI default:
- read/explain/test safe metadata
- no credential retrieval
- no secret rotation
- no arbitrary outbound request/private-network widening.

---

# 41. Events

- connection connected/disconnected/auth-expiring/unhealthy/recovered
- outbound delivery succeeded/failed after dedupe
- webhook receipt accepted/rejected/processed/failed
- credential rotated safe metadata event

Never emit secret value in event.

---

# 42. Error/degraded states

- DNS failure
- blocked SSRF destination
- TLS failure
- auth expired
- insufficient scope
- rate limit
- provider outage
- malformed response
- redirect blocked
- Vault unavailable
- webhook signature failed/replay
- provider adapter missing
- Pro expiry → management read-only; security verification of already deployed integration must not weaken silently

---

# 43. Performance

- no external calls on unrelated wp-admin requests
- async slow calls in Workflow/Job where possible
- response/body size caps
- connection pooling depends WP HTTP implementation; do not promise
- paginate logs/receipts
- cleanup asynchronously
- dedupe health failures
- batch provider operations only through certified APIs

---

# 44. Assets

Connections admin JS/CSS only on module/integration chooser screens needing it.
OAuth popup/callback uses minimal assets.
No frontend assets for server-side HTTP engine unless a user-facing connect component explicitly rendered.

---

# 45. Future tests

After consent:
- SSRF IPv4/IPv6/redirect/DNS rebinding cases
- metadata IP blocks
- auth header redirect stripping
- TLS verification
- OAuth state/PKCE/refresh/revoke
- secret never returned/logged/exported
- retry idempotency
- provider 429 Retry-After
- webhook raw-body signature
- timestamp/replay
- duplicate provider event
- oversized/deep JSON
- unknown event handling
- final-seat Membership billing duplicate webhook mapping later
- private-network high-risk policy
- connection dependency disable
- Pro/dependency degraded security behavior

## Maturity

Webhooks & Connections Manager is now **Exhaustive option spec** at Phase 0 product level. HTTP client hardening, OAuth provider implementations, Vault runtime and webhook adapters remain technical/consent-gated.