# WPEssential — Connection & Integration Adapter Certification Contract

Status: **Phase 0 planning only / no provider implementation authorized**  
Related: ADR-0040, ADR-0048, Remote Service contracts, Webhooks & Connections Exhaustive Spec.

## 1. Purpose

A saved credential or successful `Test Connection` does not prove that an integration is safe and production-ready for every action.

WPEssential integrations are certified per **adapter + provider + capability set + provider version/profile**.

Examples:
- a provider may be certified for read-only data fetch but not write actions;
- OAuth may be certified while webhook signatures are not;
- outbound webhooks may be certified while inbound event reconciliation is experimental;
- storage provider certification follows the stricter Backup Provider contract when used for Backups.

---

## 2. Core composition

`Connection Definition → Credential/Vault refs → Provider Adapter → Capability Profile → Safe HTTP/Webhook Gateway → Typed Ability/Event`

The provider adapter is not allowed to bypass:
- Vault;
- centralized Safe HTTP policy;
- Policy/Capabilities;
- WPE error taxonomy;
- audit/redaction;
- Job Service for operations that require durable async/retry semantics.

---

## 3. Adapter families

### AF-01 OAuth 2.x REST API
Examples: Google/Microsoft/Dropbox/SaaS APIs.

Required semantics:
- Authorization Code + PKCE where WPE is public client;
- exact trusted authorization/token origins;
- least scopes;
- refresh/revocation;
- access token server-side only;
- token expiry handled;
- no arbitrary OAuth issuer from normal admin UI.

Provider-specific OAuth deviations require explicit profile.

### AF-02 API Key / Bearer REST API
- secret in Vault;
- HTTPS;
- fixed or approved host policy;
- Authorization/query secret placement according to provider contract;
- no secrets in logs;
- rotation/test behavior.

API keys in query strings are rejected by default unless provider genuinely requires them and redaction/cache/referrer implications are documented.

### AF-03 Signed Request / HMAC API
- provider signing algorithm/version;
- clock/skew semantics;
- canonical request format;
- key ID/rotation;
- replay behavior;
- secret in Vault.

No custom cryptographic scheme invented by WPE merely to avoid provider SDK/API requirements.

### AF-04 Inbound Webhook
- dedicated random endpoint/reference;
- raw request body retained only as needed for signature verification and minimized thereafter;
- provider signature verification before business dispatch;
- timestamp/replay checks where provider protocol offers them;
- delivery/event ID idempotency;
- normalized Event Inbox;
- async processing after verified receipt when appropriate.

### AF-05 Outbound Webhook
- Safe HTTP destination policy;
- signed payload option/profile;
- idempotency/event ID;
- retry/backoff;
- unknown-outcome semantics;
- delivery attempt history;
- receiver 2xx does not prove downstream business completion.

### AF-06 SMTP / Email Transport
Credentials/config in Vault; delivery semantics owned by Notification/Email transport certification. Provider accepted != inbox delivered.

### AF-07 Storage / Backup
Delegates to `BACKUP-PROVIDER-CERTIFICATION-CONTRACT.md`; Backup support cannot be inferred from generic Connection certification.

### AF-08 SSH/SFTP/WebDAV-like transport
Uses family-specific host/certificate trust, Safe HTTP where applicable and Backup/Import contracts where used.

### AF-09 Custom SDK Adapter
Third-party extension implements typed WPE adapter interfaces and declares dependencies/license/version support. It receives no privileged bypass merely because it is registered.

---

## 4. Capability descriptor

Each profile declares capabilities such as:

### Connection/auth
- connect/test;
- refresh;
- revoke;
- rotate credential;
- scopes/permissions;
- account/tenant identity;
- environment/regional endpoint support.

### Data
- list/read;
- get by ID;
- search/query;
- pagination model;
- create;
- update;
- delete;
- bulk/batch;
- file upload/download;
- rate limits/quotas.

### Events
- inbound webhook;
- outbound webhook;
- event ID;
- event ordering guarantee;
- signature profile;
- replay window;
- reconciliation/list-events API;
- webhook subscription create/renew/delete.

### Reliability
- idempotency key;
- safe retry;
- operation status query;
- async job polling;
- rate-limit headers;
- `Retry-After`;
- webhook retry behavior;
- reconciliation support.

### Security
- trusted hosts;
- custom host allowed/not allowed;
- SSRF class;
- OAuth scopes;
- secret fields;
- sensitive response fields;
- PII classifications;
- data residency/regional concerns informational.

---

## 5. Certification levels

### I0 — Detected / Configurable
Adapter loads and its schema is known. No provider connectivity claim.

### I1 — Authentication Certified
Proves:
- valid connect;
- invalid credential behavior;
- token/key storage/redaction;
- refresh/revoke where applicable;
- correct account/tenant identity.

UI can say `Connected`, not “full integration certified”.

### I2 — Read Certified
Proves intended read/list/query capabilities including pagination, authorization, rate limits and data mapping.

### I3 — Write / Action Certified
Proves mutating capabilities:
- input schema;
- create/update/delete or provider action;
- idempotency/retry;
- unknown-outcome handling;
- authorization/scopes;
- safe error mapping;
- audit.

Each action is certified separately where risk materially differs.

### I4 — Event / Reconciliation Certified
Proves:
- webhook signature;
- duplicates;
- replay;
- out-of-order events;
- subscription renewal;
- Event Inbox normalization;
- reconciliation against provider source of truth;
- Job crash/retry.

### I5 — Production Profile Certified
Highest generic integration label.

Requires all provider capabilities that WPE publicly advertises for that adapter to be certified, version ranges recorded, failure/recovery runbook documented and relevant security/privacy fixtures passed.

A provider can be I5 for a narrow capability set; unsupported capabilities remain explicit.

---

## 6. Operation certification is granular

Example:

A WooCommerce-like billing adapter could have:
- authentication/read: certified;
- purchase lookup: certified;
- webhook renewal/refund: certified;
- direct refund creation: unsupported;

It must not surface an unsupported write action simply because the provider is “Connected”.

Every Ability/action references a certified adapter capability key.

---

## 7. Provider version/profile

Certification record includes:
- adapter version;
- provider API version;
- provider product/plan restrictions;
- plugin/integration version range where local plugin adapter is involved;
- regional cloud variant;
- tested auth profile;
- certified capability set;
- certification timestamp;
- known limitations;
- deprecation status.

Provider silently changing behavior triggers health warning/re-certification when detected.

---

## 8. Safe HTTP contract

All outbound HTTP uses centralized Safe HTTP service from ADR-0040.

Provider adapter declares destinations as one of:
- fixed trusted provider host(s);
- provider-discovered host constrained to signed/verified discovery contract;
- admin-entered custom URL requiring stricter SSRF profile.

Rules:
- HTTPS by default;
- validate every redirect;
- private/link-local/loopback/cloud-metadata destinations blocked unless an explicit specialized local-network product profile is separately approved;
- DNS rebinding considered;
- redirect cannot move bearer credential to untrusted host;
- response bytes/time bounded;
- decompression bombs/oversized payloads guarded.

---

## 9. Webhook verification order

Inbound flow:

1. identify endpoint/provider profile;
2. enforce request-size/content-type limits;
3. capture exact raw bytes required for signature verification;
4. verify signature/key/timestamp according to provider profile;
5. reject invalid/replayed request;
6. derive provider event/delivery ID;
7. idempotently create/update Event Inbox record;
8. acknowledge according to provider timing contract;
9. process asynchronously/durably when needed;
10. reconcile uncertain/out-of-order business facts against provider API where supported.

No workflow/business mutation occurs before required signature verification.

---

## 10. Event normalization

Normalized Event Envelope includes:
- WPE event UUID;
- provider/connection/profile;
- provider event ID;
- provider event type;
- observed/occurred timestamps;
- verified signature state;
- source subject/reference;
- normalized typed payload;
- raw payload retention reference only when policy requires;
- processing state;
- dedupe key;
- reconciliation state;
- correlation/request IDs.

Provider raw JSON is not directly treated as a WPE business object without adapter validation.

---

## 11. Retry / unknown outcome

Outbound mutation statuses distinguish:
- not sent;
- sent outcome known success;
- sent known failure;
- sent outcome unknown;
- retry scheduled;
- reconciliation required;
- terminal failure.

`network timeout` after send does not automatically mean provider action failed.

If provider exposes idempotency key or operation-status API, adapter uses it according to certified profile.

---

## 12. Connection test semantics

`Test Connection` validates only declared checks:
- DNS/TLS/host policy;
- credentials/auth;
- expected provider identity;
- required scopes if discoverable;
- minimal safe read/health call.

It does **not**:
- perform destructive writes by default;
- send real business email/payment/refund;
- create webhook subscription unless test explicitly says so;
- prove every advertised capability works.

Result displays what was tested and what remains unverified.

---

## 13. Secrets / UI

Credentials are write-only after save by default.

UI shows:
- configured/not configured;
- safe identity/account/tenant;
- granted/requested scopes when known;
- last successful auth/test;
- expiry/refresh health;
- certification level/capabilities;
- reauthorize/replace/revoke.

No generic “Reveal API Key” in v1.

---

## 14. Privacy / logging

Per adapter declares:
- fields sent;
- fields received;
- PII categories;
- raw payload logging policy;
- retention;
- exporter/eraser implications where local runtime stores personal data.

Logs default to:
- provider;
- operation;
- status;
- safe resource reference;
- latency;
- request/correlation ID;
- normalized error.

Authorization headers, tokens, signed URLs, webhook secrets and full sensitive bodies are redacted.

---

## 15. Degradation

Adapter states:
- healthy;
- auth_expiring;
- reauthorization_required;
- permission_missing;
- rate_limited;
- provider_degraded;
- unsupported_api_version;
- certificate/host_trust_failed;
- configuration_error;
- adapter_dependency_missing;
- certification_outdated.

Failure of one Connection does not disable unrelated modules.

---

## 16. Marketing/support truth

Provider integration labels:
- Planned;
- Experimental;
- Authentication Certified;
- Read Certified;
- Action Certified;
- Event/Reconciliation Certified;
- Production Profile Certified;
- Deprecated/Blocked.

“Supports Provider X” must be accompanied internally by exact certified capability profile. Marketing must not imply write/event/billing/backup capability just from successful OAuth.

---

## 17. Future certification fixtures — NOT AUTHORIZED

- invalid/expired/revoked auth;
- token rotation;
- least-scope denial;
- API pagination;
- rate limits;
- provider 5xx/timeouts;
- malformed response;
- redirect/SSRF/DNS rebinding;
- idempotent duplicate mutation;
- unknown mutation outcome;
- webhook signature invalid/valid;
- duplicate/replayed/out-of-order event;
- webhook subscription expiration/renewal;
- reconciliation;
- Job crash/resume;
- schema/version drift;
- PII/log redaction.

No fixture has been executed because development consent has not been granted.
