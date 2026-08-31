# WPEssential — Remote Service & Account API Contract

Status: **Phase 0 planning — no remote API/client implementation authorized**  
Research date: 2026-08-27.

## Purpose
Define how WPEssential WordPress installations may communicate with WPEssential-controlled services for optional account linking, Pro entitlements, plans/catalog metadata, support, documentation lookup and service status without making Free functionality dependent on the service or turning the WordPress.org plugin into a remote-code loader/storefront.

## Non-negotiable WordPress.org boundaries

Current WordPress.org plugin guidelines permit documented serviceware and contextual upsells, but prohibit:
- locked trialware in the directory plugin;
- a service whose only substance is license/key validation for locally included paid functionality;
- unconsented external tracking/data transmission;
- external systems sending executable code into a directory plugin;
- the directory plugin serving/installing/updating premium plugins/add-ons from non-WordPress.org servers inside wp-admin.

Therefore:
- Free CPT/Taxonomy remain fully local and account-free;
- account/service use is opt-in and clearly disclosed;
- plan/support/docs APIs return data, never executable PHP/JS;
- WPEssential Free does **not** auto-download/install/update WPEssential Pro from WPE servers;
- purchase/trial acquisition hands the user to the trusted WPE service/download flow;
- normal WordPress plugin upload/manual installation remains the initial Pro install path unless a separately compliant management-service architecture is later approved;
- the Free plugin must remain a useful plugin, not a storefront wrapper.

Primary reference: WordPress.org Detailed Plugin Guidelines, especially guidelines 5–8.

---

# Service domains

Candidate logical services:
- Account/Auth
- Entitlements/Site Activations
- Plans/Catalog
- Support Tickets
- Docs Search/Link Resolver
- Changelog/Release Metadata
- Service Health

They may be deployed behind one API host, but contracts remain logically separated.

## Trusted origin policy

Client has a compiled/configured allowlist of official HTTPS service origins. A normal API response cannot replace the trusted base origin with an arbitrary host.

Environment switching (production/staging/development) is developer/build configuration, not an end-user arbitrary URL field in production.

---

# Site identity

A WPE installation may generate a random local installation UUID before connection.

On explicit account-link action, the user is shown what will be transmitted, potentially including:
- site URL/domain needed for activation;
- local installation UUID;
- WordPress/WPE versions;
- site/network scope;
- locale/timezone if needed for service UX;
- requested product/linking operation.

Do not silently register every Free activation with WPE servers.

A domain/site fingerprint is not authentication by itself.

---

# Account linking — preferred candidate

## Browser-based Authorization Code + PKCE

WPE WordPress plugin behaves as a **public client**. It must not ship a reusable confidential OAuth client secret, because distributed plugin source/code cannot keep such a secret confidential.

Preferred flow:

1. Authorized admin selects **Connect WPEssential Account**.
2. WordPress creates a one-time local linking transaction:
   - high-entropy `state`;
   - PKCE `code_verifier`;
   - `S256` challenge;
   - exact local callback URL;
   - local expiry;
   - initiating admin/user ID.
3. WordPress requests/opens a trusted WPE authorization URL bound to the transaction and callback/site-link record.
4. User signs in/signs up/recovers account **on the WPE service domain**.
5. Service authorizes the site connection and redirects only to the exact callback bound to that transaction.
6. Local callback verifies state/transaction/initiating context and exchanges authorization code + PKCE verifier server-to-server.
7. Service returns short-lived access token + refresh credential/site-connection metadata according to final OAuth profile.
8. Refresh credential is stored in Secrets Vault; access token is short-lived/cache-only where practical.
9. Plugin fetches signed/verified entitlement state.
10. Local transaction verifier/state/code material is destroyed after success/failure/expiry.

Current OAuth Security BCP requires PKCE for public clients and recommends `S256`; CSRF/code-injection/mix-up protections must be implemented according to the accepted profile.

## Callback registration blocker

Dynamic WordPress site callback URLs require an exact service-side trust/registration design. Before implementation we must choose and security-review one of:
- service-created ephemeral/site-specific public client registration with exact callback;
- fixed service callback plus cryptographically bound one-time site return transaction;
- another standards-compliant profile.

No arbitrary unchecked `return_to` open redirect is allowed.

## Device Authorization fallback candidate

OAuth Device Authorization Grant may be used if browser callback registration proves operationally problematic. Plugin displays verification URL/code and polls only after user starts the flow, respecting server interval/slow-down semantics.

Device flow is a fallback, not automatically preferred on browser-capable wp-admin.

## Password-form correction

The initial product sketch included login/signup/forgot forms inside WordPress. Current security recommendation is **not** to make the distributed WordPress site a password-collection proxy for WPE account credentials. The local wizard can show Connect / Create Account / Recover Account actions, but authentication UI runs on the trusted WPE account domain.

A future direct credential API would require separate security/privacy justification and must never persist/log WPE account passwords locally.

---

# Token model

Candidate token types:

## Access token
- short lifetime;
- least scopes;
- used server-to-server only;
- never localized into browser JS;
- never written to logs/support bundles.

## Refresh credential
- P3 secret;
- stored only via Secrets Vault;
- rotation/revocation supported;
- bound to site/account connection where service supports it;
- disconnect revokes remotely when reachable and deletes local secret.

## Site activation reference
- opaque non-secret reference may be stored in ordinary configuration if it grants no authentication by itself.

## Scope candidates
Separate permissions such as:
- `account:read`
- `entitlements:read`
- `support:read`
- `support:write`
- `sites:manage-self`

Plan/catalog/docs public metadata should not require broad account scopes merely for display where unauthenticated API is appropriate.

---

# Signed entitlement document

Local runtime must not depend on WPE service availability on every request.

Candidate service response is a signed entitlement document containing at minimum:
- schema/version;
- site activation ID;
- product/plan ID;
- entitlement/module claims;
- trial/subscription status facts;
- `issued_at`;
- `valid_until`;
- optional `grace_until` according to commercial policy;
- service environment/issuer;
- key ID (`kid`);
- document ID/version;
- signature.

## Verification

Preferred candidate: asymmetric signature with public verification key(s) shipped in trusted WPE code and private signing key held only by service infrastructure.

Exact JWS/detached-signature format and algorithm remain implementation ADR work. Ed25519 is a candidate because PHP Sodium support is already under review, but no algorithm is Accepted yet.

## Key rotation
- multiple verification keys by `kid`;
- new public key distributed before signing-key cutover;
- old keys retained for bounded verification window;
- compromised-key emergency process documented;
- API cannot simply send a new trusted verification key and ask the plugin to trust it without an existing trust chain.

---

# Entitlement state machine

Local client distinguishes:
- `free`
- `trial_active`
- `pro_active`
- `grace`
- `expired`
- `suspended`
- `verification_stale`
- `verification_unavailable`
- `incompatible_version`
- `disconnected`

Network outage is **not** `expired`.

## Offline behavior

Candidate principles:
- last verified signed entitlement is cached;
- bounded offline/stale windows are explicit product policy;
- safe deployed runtime/security enforcement continues according to ADR-0007;
- creation/editing/cloud-service operations can degrade when freshness requirements fail;
- Free remains fully functional;
- admin shows `last_verified_at` and reason;
- retry uses backoff and never blocks every wp-admin page request.

Exact grace durations are commercial/security decisions, not hardcoded in this planning contract.

---

# Plans / Catalog API

Purpose: display contextual plan/trial metadata, not deliver code.

Candidate response schema:
- catalog schema version;
- plan ID/stable slug;
- display name;
- description/feature groups;
- billing interval;
- display price/currency/tax-display metadata supplied by commerce service;
- site allowance;
- module entitlement groups;
- trial eligibility summary;
- current-plan marker when authenticated;
- trusted checkout/manage-plan URL identifier/link;
- effective/start/end metadata for promotions;
- locale/region where service genuinely supports them.

Client validates an allowlisted schema and renders with local components. Remote HTML/JS/CSS is not injected into wp-admin.

Plans endpoint failure does not affect local Free functionality.

---

# Purchase / Trial / Pro package acquisition

## Allowed planning flow for WordPress.org Free

1. User explicitly selects Start Trial / Buy / Upgrade.
2. Free plugin opens a trusted WPE service page/session.
3. Service handles authentication/checkout/trial activation on its domain.
4. User obtains Pro package/download instructions through the service/customer account.
5. User installs Pro using WordPress's normal plugin upload/manual administrator flow.
6. Once Pro is active, it detects compatible Free and retrieves entitlement through the approved account connection.

The Free directory plugin does **not** call `Plugin_Upgrader` with an external WPE package URL as a hidden one-click Pro installer.

## Future management service
WordPress.org guidelines allow management services that push software when the interaction is handled on the management service's own domain. If WPE later builds such a service, it requires separate architecture/compliance/security review and must not be silently conflated with the Free plugin's local dashboard.

---

# Pro update architecture

WPEssential Pro is externally distributed, so its update channel requires a separate trusted update design. It must not make the WordPress.org Free plugin responsible for external executable-code delivery.

Candidate requirements for Pro package/update metadata:
- authenticated/trusted update origin;
- version + compatibility metadata;
- package checksum;
- asymmetric package signature/trust chain;
- rollback package/reference where policy supports;
- signing key rotation and compromise response;
- TLS;
- update-order compatibility with Free Platform API;
- no executable snippets in metadata.

Exact external updater implementation remains development work and must be reviewed against current WordPress/plugin distribution rules at implementation time.

---

# Support API

Remote support service is source of truth; WordPress UI is client/cache.

## Ticket model candidate
- ticket ID/reference;
- account/site activation reference;
- subject;
- category;
- service-defined priority if used;
- state/status;
- created/updated timestamps;
- unread/reply metadata;
- related module/version metadata;
- thread messages;
- attachment metadata;
- service retention/privacy metadata/link.

## Status candidates
Keep service-level statuses explicit rather than only Open/Closed, e.g.:
- `open`
- `waiting_for_support`
- `waiting_for_customer`
- `resolved`
- `closed`

Exact service states remain product/support operations design.

## API operations
- list tickets;
- get ticket/thread;
- create;
- reply;
- close;
- reopen if service policy allows;
- attachment upload session;
- download attachment through authenticated short-lived link/session.

Permanent ticket deletion is not promised by plugin UI.

## Idempotency
Create-ticket and reply operations use client request IDs / `Idempotency-Key` if service supports it to avoid duplicate tickets/replies after retry.

---

# Attachment upload

Before requesting upload session:
- local MIME/extension/size checks;
- service returns allowed constraints;
- executable/script types rejected;
- diagnostics bundle explicitly selected;
- filename sanitized;
- upload metadata is authenticated to ticket/draft.

Service may perform malware/content scanning, but local UI must not claim scanning passed unless service reports a defined status.

Private attachments are never permanent public URLs.

---

# Diagnostics support bundle

Bundle API never pulls arbitrary site data on request from the service.

Local process:
1. select/generate diagnostic categories;
2. redact locally;
3. preview exact categories/files/fields;
4. admin explicitly approves upload;
5. create support upload session;
6. transmit over HTTPS;
7. record safe upload metadata.

Default excludes:
- DB dumps;
- user/customer records;
- form/chat content;
- protected files;
- raw `wp-config.php`;
- passwords/salts/tokens;
- plugin/theme source;
- unrestricted logs.

---

# Docs API

Remote docs service may return structured search data:
- article ID;
- title;
- short safe excerpt;
- module/category;
- version applicability;
- language;
- trusted docs URL;
- updated-at.

The plugin does not inject arbitrary docs-site HTML/scripts into wp-admin. Full article can open on trusted docs domain or use a separately defined sanitized structured-content schema.

Local bundled quick-start/version-critical docs remain available offline.

---

# Changelog / release metadata API

Remote release metadata can expose:
- product/package;
- version;
- release date;
- compatibility ranges;
- categories Added / Changed / Fixed / Security / Deprecated / Removed;
- migration notes;
- breaking changes;
- known issues;
- trusted release-notes URL;
- update availability metadata where appropriate for package ownership.

Installed package's bundled changelog remains the offline source of truth for what that artifact claims to contain.

Remote service cannot rewrite history inside the installed package.

---

# Service health

Optional endpoint may report WPE-controlled service incidents/status for account/support/docs/entitlement APIs. A service incident is advisory and must not masquerade as local WordPress failure.

Do not send site data merely to display public service status.

---

# API transport contract

All authenticated service calls require:
- HTTPS;
- certificate validation;
- bounded connect/request timeouts;
- versioned media/API schema;
- safe user-agent identifying WPE version, not user content;
- correlation/request ID;
- response body/size limits;
- schema validation;
- JSON parsing limits;
- retry only idempotent/safely keyed operations;
- exponential/backoff honoring `Retry-After` where applicable;
- no redirect to untrusted hosts for authenticated requests;
- privacy-safe logs.

## Error normalization
Service errors map into WPE error taxonomy:
- auth required/expired;
- entitlement unavailable;
- validation;
- rate-limited;
- service unavailable;
- conflict/idempotency;
- unsupported client version;
- malformed/untrusted response.

Raw stack traces/provider secrets are never shown in normal wp-admin.

---

# Caching

Cache only data whose freshness semantics are explicit:
- plans/catalog: bounded cache, stale display acceptable with timestamp where appropriate;
- docs search: bounded cache;
- public changelog metadata: bounded cache;
- entitlement: signed/freshness-aware special cache;
- support ticket data: short cache + explicit refresh;
- access/refresh tokens: credential handling, not generic transients.

Cache key includes service environment/account/site context as needed to prevent cross-account leakage.

---

# Privacy & consent

Before first account connection/service data transmission, disclose:
- service used;
- categories transmitted;
- purpose;
- Terms/Privacy links.

Free activation alone does not transmit registration/telemetry data.

Disconnect behavior:
- revoke tokens remotely when possible;
- delete local refresh/access credentials;
- keep local Free configuration;
- retain only non-secret historical account/support metadata if policy requires and user is informed;
- Pro definitions/data are not deleted.

---

# Abuse / rate-limit considerations

Client must not:
- poll entitlement on every request/page;
- automatically initiate account/device auth without user action;
- retry non-idempotent support writes blindly;
- hammer service during outage;
- repeatedly refresh catalog/docs when cached copy is valid.

Service should be able to return client minimum-version / deprecation notices as data, but cannot push executable remediation code.

---

# Future API contract artifacts

Before implementation, service/client design still needs:
- OpenAPI/schema definitions;
- OAuth/account-link exact profile and redirect registration model;
- token lifetimes/rotation/revocation;
- entitlement signing format/key rotation/offline grace ADR;
- plan/catalog schema;
- support status/category/attachment schemas;
- privacy/retention policy;
- package/update signing and Pro updater architecture;
- threat model for compromised account/service/signing key;
- rate-limit budgets;
- integration/environment test plan.

---

# Development gate

This file is architecture planning only. No OAuth client, account API, remote token storage, package downloader/updater, support client, signed-entitlement verifier or service endpoint implementation is authorized before explicit owner development consent under ADR-0014.