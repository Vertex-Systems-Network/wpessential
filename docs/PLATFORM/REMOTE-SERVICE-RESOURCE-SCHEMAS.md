# WPEssential — Remote Service Resource Schemas

Status: **Phase 0 planning only / no service or client implementation authorized**  
Related: `REMOTE-SERVICE-API-CONTRACT.md`, ADR-0034, ADR-0042, ADR-0044, ADR-0050.

## 1. Purpose

This document narrows the logical resource/schema contract between a WPEssential WordPress installation and optional WPE-controlled services.

It does **not** freeze final URL paths, database schemas, deployment topology or implementation framework.

Remote services are optional to WPE Free. They must never become a remote executable-code channel for the WordPress.org Free plugin.

---

## 2. API design baseline

### Versioning

The API has an explicit major contract version.

Conceptual media/API version:
- `v1` resource contract;
- additive compatible fields may appear according to schema rules;
- breaking field/semantic changes require new major contract or negotiated version.

Client ignores only explicitly forward-compatible unknown fields. Security-sensitive enums/algorithms are fail-closed when unknown.

### Schema source

Future executable service/client work should maintain a machine-readable OpenAPI contract and generated/validated JSON Schemas where practical.

OpenAPI document is documentation/validation input, not a substitute for semantic tests.

### Error representation

HTTP/API errors use **RFC 9457 Problem Details** (`application/problem+json`) as the baseline envelope.

Candidate WPE extensions:
- `code` — stable WPE machine error code;
- `request_id` — correlation ID;
- `retryable` — boolean/advisory from service;
- `retry_after_seconds` only when meaningful;
- `field_errors` for validation failures;
- `minimum_client_version` when client upgrade is genuinely required.

`detail` is human-readable and never parsed by client business logic.

Never include:
- stack traces;
- SQL;
- secrets/tokens;
- signing private-key information;
- internal host topology.

---

## 3. Common resource conventions

### IDs

Remote resource IDs are opaque strings/UUID-like identifiers and are never trusted as authorization by themselves.

Examples:
- `account_id`;
- `site_activation_id`;
- `ticket_id`;
- `message_id`;
- `attachment_id`;
- `plan_id`.

### Time

All service timestamps:
- UTC;
- RFC 3339/ISO 8601 compatible string;
- explicit offset/`Z`;
- server clock is authoritative for service-issued freshness values.

### Request correlation

Service responses expose a safe request/correlation ID.

Client logs correlation ID without full sensitive payload.

### Pagination

Collection resources use one consistent cursor-based contract where practical:
- `items`;
- `next_cursor` nullable;
- `has_more` optional;
- bounded `limit`;
- stable ordering defined per collection.

Offset pagination may be used only where domain semantics make it more appropriate.

### Idempotency

Mutating create/action requests that may be retried use a client-generated idempotency key where service supports it.

Examples:
- create support ticket;
- support reply;
- create site-link/activation transaction;
- request attachment upload session;
- commercial activation transfer.

Idempotency key is scoped to account/site/action and has documented expiry.

---

## 4. Authentication boundary

The WordPress plugin is a **public OAuth client**.

Accepted account-link profile is ADR-0034:
- Authorization Code;
- PKCE S256;
- fixed WPE-owned OAuth callback;
- one-time site-bound completion artifact;
- no reusable access/refresh token in browser return URL;
- Device Authorization fallback candidate.

OAuth account passwords are never collected/stored by normal WPE WordPress UI.

Authenticated service API access token:
- server-to-server from WordPress;
- short-lived;
- scope-limited;
- never localized to frontend React/JS;
- never logged.

Refresh credential:
- Vault P3 secret;
- rotatable/revocable;
- never returned by generic diagnostics/export.

---

## 5. Logical service domains

Resource domains:

1. Account identity summary
2. Site connections / activations
3. Product entitlement
4. Plans / catalog
5. Support tickets / messages / attachments
6. Documentation metadata/search
7. Changelog / release-note metadata
8. Service status
9. Update trust metadata — **separate TUF repository/protocol**, not ordinary REST authority

These domains may share one service origin, but their trust semantics remain separate.

---

# 6. Account Summary resource

Purpose: display safe account identity after explicit connection.

Candidate fields:
- `account_id`;
- `display_name` nullable;
- `email_masked` or safe contact identity;
- `organization_name` nullable;
- `account_status` safe enum;
- `created_at` optional;
- `support_access` summary;
- `billing_portal_available` boolean;
- `links` limited trusted relations.

Do not return:
- password state/hash;
- payment card data;
- unrelated account profile data;
- global site list when client scope only allows current site.

Scope candidate: `account:read`.

---

# 7. Site Connection / Activation resource

Site activation is server-side commercial/account state, not authentication itself.

Candidate fields:
- `site_activation_id`;
- `account_id`;
- `installation_id` reference;
- normalized `site_origin`;
- `scope`: single-site/network;
- `environment_class`: production/staging/development/unknown according to future policy;
- `product`;
- `activation_status`;
- `created_at`;
- `last_seen_at` optional/minimized;
- `revoked_at` nullable;
- `transfer_required` boolean;
- `entitlement_document_id` optional safe reference.

Activation status candidate enum:
- pending;
- active;
- suspended;
- transfer_pending;
- revoked;
- disconnected.

A local installation UUID alone cannot create/reactivate an activation without authenticated service flow.

### Site migration/clone

Service action must distinguish:
- URL/domain changed on same intended site;
- staging clone;
- cloned production copy;
- activation transfer to new installation.

No silent clone activation merely because DB contains prior `site_activation_id`.

---

# 8. Signed Product Entitlement resource/artifact

Entitlement is not a normal unsigned JSON boolean.

ADR-0042 governs crypto:
- Ed25519;
- RFC 8785 JCS;
- domain separation;
- signer `kid`;
- root-authorized signer keysets;
- freshness/site binding.

Logical signed payload fields include:
- schema/profile version;
- document ID;
- issuer/environment;
- audience/product;
- site activation ID;
- installation/site binding fields defined by profile;
- account/plan safe references;
- module/feature entitlement claims;
- commercial status facts;
- monotonic sequence;
- `issued_at`;
- `not_before`;
- refresh/freshness boundary;
- validity boundary;
- optional signed grace boundary;
- signer key ID.

Transport endpoint may return the signed artifact, but API/TLS is **not** the artifact trust root. Client verifies signature/freshness/binding locally.

Unknown algorithm/profile = fail closed for Pro management, while Free remains functional and safe deployed runtime follows ADR-0007.

---

# 9. Entitlement signer keyset resource

Signer rotation is root-authorized.

Logical keyset contains:
- keyset schema version;
- keyset generation/sequence;
- valid-from/expiry if profile requires;
- signer public keys by `kid`;
- key status/use;
- root authorization/signature metadata.

A normal account API response cannot add an arbitrary trusted public key without accepted root-chain verification.

No private key material ever enters WordPress.

---

# 10. Plans / Catalog resource

Plans are commercial display/config data, not executable feature code.

Catalog fields:
- `catalog_version`;
- `generated_at`;
- locale/market identifier where real;
- `plans[]`;
- optional promotion metadata;
- trusted checkout/manage destination identifiers.

Plan candidate fields:
- `plan_id` stable;
- `slug`;
- `display_name`;
- `description` structured/plaintext-safe;
- billing interval unit/count;
- display price amount/minor units;
- currency;
- tax-display note/flag from commerce service;
- site allowance;
- entitlement group identifiers;
- trial metadata/eligibility summary;
- current-plan marker when authenticated;
- promotion start/end;
- trusted checkout/manage-plan URL/link relation.

Rules:
- remote HTML/JS/CSS prohibited;
- plan feature copy cannot enable a local module by itself;
- entitlement remains signed authority;
- stale catalog may be displayed with safe cache policy;
- catalog outage never disables Free.

---

# 11. Support Ticket resource — ADR-0050

Remote WPE service is authoritative.

Ticket candidate fields:
- `ticket_id`;
- human-readable reference;
- account/site activation refs;
- subject;
- category;
- priority if service exposes it;
- status;
- created/updated timestamps;
- requester safe identity;
- last_message_at;
- unread/customer-action metadata;
- module/version context;
- retention/deletion status;
- allowed actions.

Status candidate enum:
- open;
- waiting_for_support;
- waiting_for_customer;
- resolved;
- closed;
- deletion_requested where applicable.

Client must render allowed actions from service policy but still enforce local capability.

---

# 12. Support Message resource

Fields:
- `message_id`;
- `ticket_id`;
- actor type: customer/support/system;
- display identity safe subset;
- body in accepted safe structured/plaintext format;
- created/edited timestamp;
- attachment metadata refs;
- message state;
- client idempotency reference optional.

No arbitrary support-origin HTML/script is injected into wp-admin.

If rich formatting exists, service stores/returns a documented safe AST/Markdown-like schema; client locally renders/sanitizes it.

---

# 13. Support Attachment resource

Metadata:
- `attachment_id`;
- `ticket_id`/message ref;
- sanitized display filename;
- MIME/content type;
- bytes;
- cryptographic digest where service supports;
- scan state if actual scanning exists;
- created_at;
- download authorization state;
- retention/deletion state.

Private download uses authenticated or short-lived single-purpose URL/session.

Permanent public attachment URLs prohibited.

### Upload session

Upload-session response can include:
- upload/session ID;
- short-lived upload target/token;
- expiry;
- maximum bytes;
- allowed MIME/extensions;
- required digest/headers;
- finalize action/state.

Preauthenticated upload target is a secret and is not logged.

Client does not claim malware scan success until service reports a defined terminal scan state.

---

# 14. Diagnostics Bundle resource

Local site prepares diagnostics before service upload.

Manifest sent with approved bundle:
- bundle schema version;
- category list;
- redaction profile/version;
- WPE/WP versions;
- site activation ref;
- file list with sizes/digests;
- explicit admin approval timestamp/actor local audit ref.

Excluded by default:
- DB dump;
- user/customer content;
- Forms/Chat messages;
- wp-config raw content;
- secrets/tokens/salts;
- plugin/theme source;
- arbitrary full logs.

Service can reject oversized/prohibited bundles even after local preflight.

---

# 15. Documentation resource

Search/list item fields:
- article ID;
- title;
- safe short excerpt;
- category/module;
- language;
- version applicability;
- updated_at;
- trusted docs link relation;
- deprecation flag.

Full remote website HTML is not injected into wp-admin.

Version-critical onboarding/help can have bundled offline docs.

---

# 16. Changelog / Release Note resource

This resource communicates human/product release information, not package authenticity.

Fields:
- product/package;
- version;
- release date;
- compatibility range;
- release categories;
- breaking/migration notes;
- known issues;
- security-note disclosure level;
- trusted release-note link.

Actual Pro package authenticity/update authorization belongs to **ADR-0044 TUF metadata**.

A REST `latest_version` field must never bypass TUF metadata for automated Pro install/update.

---

# 17. TUF update repository separation

Pro update trust is a separate signed metadata system:
- Root;
- Targets;
- Snapshot;
- Timestamp;
- signed target hash/length/path/custom compatibility metadata.

REST account/catalog API may indicate that an update exists for UX, but executable acceptance must be derived from verified TUF metadata and Free↔Pro compatibility checks.

If production-quality verifier is unavailable, automated updates remain disabled rather than falling back to weak REST-manifest trust.

---

# 18. Service Status resource

Public status data can include:
- service/component ID;
- operational/degraded/outage/maintenance state;
- message;
- started/updated timestamp;
- trusted incident URL.

No site telemetry required merely to read public status.

Service status is advisory and does not override local module health.

---

# 19. HTTP semantics

### Success
Use standard HTTP semantics rather than putting all outcomes inside HTTP 200.

Examples:
- 200 resource/read/action result;
- 201 created;
- 202 accepted for genuine async service operation;
- 204 successful no-content delete/action where appropriate.

### Client errors
- 400 malformed;
- 401 missing/invalid authentication;
- 403 authenticated but forbidden/scope denied;
- 404 resource not found within caller scope;
- 409 conflict/idempotency/state transition conflict;
- 410 expired one-time resource where semantically useful;
- 413 payload too large;
- 415 media type unsupported;
- 422 schema/domain validation;
- 429 rate limited.

### Server/service errors
- 500 safe generic service failure;
- 502/503/504 as infrastructure/gateway semantics genuinely apply.

Errors use RFC 9457 Problem Details.

---

# 20. Retry semantics

Client classifies operations:

### Safe/idempotent reads
Retry with bounded exponential backoff + jitter; honor `Retry-After`.

### Idempotency-key writes
Can retry according to service contract using same idempotency key.

### Non-idempotent/unknown outcome
Do not blindly retry. Query resource/operation state first where possible.

### OAuth/token calls
Follow OAuth-specific semantics; never repeatedly hammer token endpoint during invalid/revoked credential state.

---

# 21. Rate limits

Service can return:
- HTTP 429;
- `Retry-After`;
- optional documented rate-limit metadata.

WordPress client:
- does not poll entitlement on page load;
- does not refetch catalog/docs while valid cache exists;
- batches/supports pagination;
- applies circuit/backoff after outage;
- surfaces stale state separately from expiry.

---

# 22. Cache policy by resource

- Account summary: short authenticated cache.
- Site activation: short cache + explicit refresh after mutation.
- Signed entitlement: special signed freshness semantics, not normal cache TTL only.
- Catalog/plans: medium bounded cache; stale display allowed where labeled.
- Support tickets: short cache + user refresh.
- Docs/changelog: bounded public cache.
- TUF metadata: governed by signed expiry/version rules, not generic HTTP cache alone.
- OAuth credentials: not cache data; Vault/credential lifecycle.

Cache key includes environment/account/site activation where relevant.

---

# 23. Privacy / data minimization

Every endpoint has a documented transmitted-field list and purpose.

Default rules:
- Free activation sends nothing to WPE merely because plugin activated;
- site URL/installation ID sent only during explicit account/site link or service use requiring it;
- no analytics/telemetry piggyback on account API;
- support diagnostics require explicit local preview/approval;
- IP/device fingerprinting only if separately justified and disclosed;
- service logs must not retain bearer tokens/request secrets.

---

# 24. Client compatibility

Service may advertise:
- minimum supported client version;
- deprecation date;
- feature capability/version.

It cannot:
- send executable remediation code;
- force Free to disable local free features because API unavailable;
- bypass signed entitlement/update trust;
- silently rewrite local configuration.

Unsupported client state becomes explicit degraded UX with safe upgrade guidance.

---

# 25. Future OpenAPI contract

After explicit development consent, exact service contracts should be formalized into versioned OpenAPI documents covering:
- schemas;
- auth/scopes;
- request/response examples;
- RFC 9457 problem types;
- pagination;
- idempotency;
- attachment upload sessions;
- rate-limit semantics;
- callback/account-link endpoints;
- signed entitlement transport;
- service test environments.

The OpenAPI file itself is not yet created because service implementation/executable contract work remains consent-gated.

## 26. Evidence still required

- OAuth end-to-end implementation/replay tests;
- token rotation/revocation;
- schema compatibility tests;
- signed entitlement cross-language interoperability;
- keyset rotation/compromise fixtures;
- support attachment scanning/privacy behavior;
- service idempotency/rate-limit tests;
- account/site clone/transfer fixtures;
- API outage/offline behavior;
- TUF repository/client conformance.

No service endpoint/client has been implemented or executed.
