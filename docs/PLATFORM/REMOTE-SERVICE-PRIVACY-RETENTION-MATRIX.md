# WPEssential — Remote Service Field-Level Privacy & Retention Matrix

Status: **Phase 0 planning only / no service/client implementation authorized**  
Related: `PRIVACY-DATA-CLASSIFICATION-RETENTION.md`, `REMOTE-SERVICE-API-CONTRACT.md`, `REMOTE-SERVICE-RESOURCE-SCHEMAS.md`, ADR-0034, ADR-0042, ADR-0050, ADR-0054.

## 1. Purpose

Define what a WPEssential WordPress installation may transmit to WPE-controlled remote services, why each field exists, which side is authoritative, what may be cached locally, and what retention/deletion boundary must be disclosed.

This is a technical/product privacy contract, not jurisdiction-specific legal advice.

Core rule:

**Connecting a WPE account authorizes only the data needed for the explicitly requested account/service functions. It does not authorize hidden analytics, telemetry, unrelated site inventory collection or open-ended diagnostics upload.**

---

# 2. Governing principles

1. Free activation alone does not contact WPE service.
2. External service use is explicit, documented and purpose-scoped.
3. Send the minimum field set required for the requested operation.
4. Prefer opaque IDs/references over duplicated personal/business data.
5. Do not transmit P3 secrets except narrowly required authentication/transport credentials to their intended trusted endpoint.
6. Never send WordPress passwords, password hashes, salts, Application Passwords, card number/CVC or unrelated plugin secrets.
7. No telemetry piggybacks on entitlement/account/support requests.
8. Public Catalog/Docs/Status resources should not require site/account identity merely to render public information.
9. Diagnostics require separate preview + explicit approval.
10. Remote service retention and local WordPress retention are separate and must be described truthfully.
11. Disconnect is not automatically account deletion, support-ticket deletion or commercial-record erasure.
12. Provider/service logs must not become a covert long-term copy of request bodies, bearer tokens or user content.

---

# 3. Existing WPE privacy classes

Use shared classifications:
- **P0** public/non-sensitive configuration;
- **P1** internal configuration/operational metadata;
- **P2** personal data;
- **P3** credential/security secret;
- **P4** high-impact business/private content.

A field may have a lower intrinsic class but become more sensitive when correlated with account/site identity.

---

# 4. Remote retention classes

These are lifecycle categories, not fixed legal durations.

## RR0 — No intentional persistence
Needed only to process the current request; not stored as application data after completion. Infrastructure/security logs may still need separately documented minimal metadata.

## RR1 — One-time transaction
Short-lived OAuth/link/upload/idempotency transaction data. Deleted/expired after bounded transaction/replay window.

## RR2 — Bounded cache / freshness
Data may be cached locally/remotely for performance/freshness and expires/revalidates on a documented bounded policy.

## RR3 — Active connection state
Needed while site/account connection remains active, plus bounded post-disconnect reconciliation/security tombstone if required.

## RR4 — Operational reconciliation/history
Needed to prevent duplicate activation/billing/support/action effects, investigate lifecycle errors or preserve signed/commercial state. Retention is purpose-specific and disclosed.

## RR5 — User-created service record
Support ticket/message/attachment or other explicit remote content whose lifecycle follows the service's retention/deletion policy rather than the WordPress cache TTL.

## RR6 — Security/abuse/audit evidence
Minimal metadata needed for authentication abuse, replay prevention, key/token revocation, security investigation or mandated operational audit. Must have separately defined access/retention controls.

No class means `keep forever by default`.

---

# 5. Consent / disclosure moments

## 5.1 Plugin activation

Remote transmission: **none**.

Do not silently register installation, fetch personalized offers, send usage counters or ping WPE merely because Free was activated.

## 5.2 Public remote resource use

Examples: public docs search, public service status, public catalog if configured as remote service.

Before first use where WordPress.org/service-disclosure policy requires it, UI/readme explains that the requested content comes from WPE servers.

Do not attach account/site IDs to a public request unless the resource genuinely needs them.

## 5.3 Connect WPEssential Account

Before redirect/authorization, show categories expected to be transmitted, for example:
- site origin/domain needed for activation;
- random installation ID;
- site vs network scope;
- WPE/WordPress version where needed for compatibility;
- requested product/account-link operation;
- locale only when needed for account/service UX.

Account connection does **not** opt in to analytics/telemetry.

## 5.4 Support / Diagnostics

Support ticket submission is an explicit service action.

Diagnostics upload is an additional explicit action with local category/file/field preview and redaction.

Do not pre-check `include diagnostics` merely because user opened Support.

---

# 6. Account Summary matrix

| Field/category | Class | Transmission purpose | Authority | Local handling | Retention |
|---|---|---|---|---|---|
| account ID | P1/P2 correlated | identify connected account | service | opaque reference/cache | RR3/RR4 |
| display name | P2 | account UX | service | optional bounded cache | RR2 |
| masked email/contact label | P2 | identify account safely in wp-admin | service | masked only where full value unnecessary | RR2 |
| organization name | P2/P1 | account UX if configured | service | optional cache | RR2 |
| account status | P1 | connection UX/service eligibility | service | bounded cache | RR2 |
| support-access summary | P1 | enable support UI | service | bounded cache | RR2 |
| billing portal availability/link relation | P1 | account management navigation | service | no payment details | RR2 |

Do not return/store in WordPress:
- password/security answers;
- payment instruments/card metadata beyond a separately justified display need;
- full account site inventory unrelated to current site;
- internal fraud/risk model;
- unrelated personal profile attributes.

---

# 7. Site Connection / Activation matrix

| Field/category | Class | Purpose | Minimization rule | Retention |
|---|---|---|---|---|
| random installation UUID | P1 correlated | distinguish installation/clone | random opaque, not device fingerprint | RR3/RR4 |
| normalized site origin | P1/P2 correlated | activation/site binding | origin only; no arbitrary path/query | RR3/RR4 |
| site/network scope | P1 | license/activation scope | enum only | RR3/RR4 |
| environment class | P1 | staging/production policy | coarse enum; no hosting inventory | RR3 |
| product ID | P0/P1 | requested entitlement | stable product key only | RR3/RR4 |
| WP/WPE version | P1 | compatibility/support | exact version only when needed | RR2/RR3 |
| locale/timezone | P1 | service UX/time semantics | send only when required | RR2 |
| activation status/timestamps | P1 | lifecycle/reconciliation | service-derived | RR4 |
| last-seen timestamp | P1 | operational connection state if needed | derive from legitimate service use; no dedicated heartbeat solely for tracking | RR3/RR4 |

Not collected by default:
- full plugin/theme list;
- database/table inventory;
- active user counts;
- page/post URLs;
- traffic metrics;
- host IP/device fingerprint;
- server filesystem paths;
- PHP extension inventory except explicit diagnostics/support preflight.

### Clone/migration

A restored/cloned DB containing activation IDs must not silently cause the clone to impersonate the original site. Clone/transfer resolution uses the minimum activation/install/origin facts required by accepted service policy.

---

# 8. OAuth / token data

| Data | Class | Storage owner | Retention |
|---|---|---|---|
| state / PKCE verifier | P3 | local short-lived transaction | RR1-equivalent local transaction only |
| auth code | P3 | transient local/service | single exchange / RR1 |
| access token | P3 | local Vault/cache credential | short lifetime; never generic log |
| refresh credential | P3 | local Vault | active connection + revocation/deletion on disconnect |
| service-side token/revocation record | P3/security | service auth system | RR3/RR6 per security policy |
| completion artifact | P3 | service + local one-time transaction | RR1 |

Rules:
- no reusable credential in browser return URL;
- no tokens in analytics, support bundles, request logs or JS localization;
- disconnect attempts remote revocation then deletes local refresh/access credentials even if remote revocation is temporarily unreachable; unresolved revocation state is surfaced/audited safely.

---

# 9. Signed Product Entitlement matrix

Signed artifact should contain only what local verification/runtime needs:
- document/schema/profile IDs;
- issuer/audience/product;
- site activation/install binding;
- plan/account **opaque references** if required;
- entitlement/module claims;
- commercial status facts;
- sequence/freshness/validity/grace timestamps;
- signer `kid`/signature data.

Avoid embedding:
- account email/name;
- billing address;
- invoice/payment details;
- support data;
- arbitrary site telemetry.

Class: primarily P1, potentially P2 when correlated with account/site.

Retention:
- service signing/history: RR4/RR6 according to entitlement/key/audit policy;
- local verified artifact: bounded by signed freshness/validity + safe-runtime/disconnect policy, not indefinite generic log storage.

Signed entitlement is a security/commercial artifact, not telemetry.

---

# 10. Catalog / Plans matrix

Public catalog candidate request should need only:
- locale/market if actually supported;
- product/client contract version if needed for representation.

Do not send by default:
- site URL;
- installation UUID;
- account ID;
- installed modules;
- current page/admin route;
- usage history.

Authenticated current-plan/promotion eligibility requires explicit account/site context and should be a separately scoped request.

Catalog response is P0/P1 and RR2 cacheable.

Checkout navigation can carry a one-time server-side session/reference rather than exposing unnecessary account/site fields in browser query parameters.

---

# 11. Support Tickets / Messages matrix

Support is an explicit user-created remote service record.

Potential data:
- account/site activation refs — P1/P2;
- requester identity — P2;
- subject/category — P1/P2/P4 depending content;
- message body — P2/P4;
- module/version context — P1;
- attachment metadata/content — P2/P4;
- timestamps/status — P1/P2.

Retention: RR5 under documented support policy.

Local WordPress cache:
- ticket IDs/status/timestamps/unread safe metadata only where useful;
- bounded RR2 cache;
- do not mirror the complete support corpus indefinitely unless a specific product requirement is accepted.

### Support content warnings

Before submit, UI warns not to paste passwords/API keys/payment card data/private keys unless a future specifically designed secure secret-exchange workflow exists.

WPE Support normal message field is not a secret vault.

---

# 12. Support Attachments

Allowed upload is explicit and policy-bounded.

Before upload:
- sanitize filename;
- validate size/type;
- preview selected file;
- warn on likely sensitive classes where detectable;
- never auto-attach arbitrary server files.

Private attachment:
- P2/P4;
- RR5;
- authenticated/short-lived download;
- no permanent public URL.

Pre-signed upload/download URLs/tokens are P3 and RR1; never log them.

Malware scan status is stored/displayed only if a real scanner returns a defined state.

---

# 13. Diagnostics Bundle matrix

Diagnostics is **separate consent** from account link and support-ticket creation.

Default permitted categories after preview:
- WPE version/module versions — P1;
- WordPress/PHP/DB high-level versions — P1;
- theme/plugin names/versions only when user selects diagnostics category — P1;
- sanitized WPE error codes/correlation IDs — P1;
- enabled WPE feature/settings summaries excluding secrets — P1;
- runner/queue health counts — P1;
- DB table schema names/counts only if narrowly needed and selected — P1/P4 risk;
- redacted environment checks — P1.

Default excluded:
- raw `wp-config.php`;
- DB dump;
- password hashes/salts;
- API/OAuth/webhook secrets;
- form/chat/member content;
- customer/user exports;
- private uploads;
- backup archives;
- full plugin/theme source;
- unrestricted PHP/server logs.

Bundle manifest records:
- categories/files included;
- redaction profile/version;
- explicit local approval actor/time;
- hashes/sizes.

Uploaded diagnostics: P1 and potentially P2/P4 depending selected categories; RR5 or shorter support-specific diagnostic retention, disclosed separately from ticket text if possible.

---

# 14. Docs Search

Public docs lookup can send:
- search query typed by user;
- language;
- WPE module/version filter if user/context requests version-specific help.

Avoid automatically attaching:
- site origin;
- account ID;
- installation UUID;
- current post/page content;
- current admin form values;
- error logs.

A search query can itself contain personal/private text. UI should treat it as data sent to remote docs service and avoid auto-populating sensitive content.

Retention:
- client result cache RR2;
- server search-query analytics are **not assumed/authorized** by account connection. If product analytics of search terms is desired, it needs separately disclosed telemetry/privacy design.

---

# 15. Changelog / Release Notes

Public resource should require no site/account identity.

Request may include product + version/language only.

Response P0/P1; local RR2 cache.

Do not piggyback update telemetry/install counts onto release-note fetch.

Automated Pro update trust remains TUF, separately from this REST resource.

---

# 16. Service Status

Public service-status fetch requires no site/account identity.

Request data: normal HTTP request metadata only.

No installation UUID, current admin route or feature usage should be added.

Response P0; RR2 short cache.

A public status fetch is not a telemetry heartbeat.

---

# 17. Idempotency / replay metadata

Idempotency keys, webhook event IDs, OAuth state records and replay tokens are operational/security metadata.

Classification: P1/P3 depending capability/secret nature.

Retention:
- minimum bounded window needed to prevent duplicate/replay behavior;
- then expire unless referenced by RR4/RR6 investigation/audit requirement.

Do not retain full request bodies merely because an idempotency key exists.

---

# 18. Request / security logs

Service infrastructure/application logs should prefer:
- request ID;
- endpoint/resource class;
- safe account/site activation opaque IDs only where needed;
- status/error code;
- timing;
- security event class.

Redact/omit:
- Authorization header;
- refresh/access token;
- OAuth code/PKCE verifier/state secret;
- support attachment pre-signed URLs;
- full request/response body by default;
- signed recovery/secret material;
- unnecessary full email/message content.

IP/user-agent may exist in infrastructure/security logs. If retained, classify P2 and define RR6/security purpose/retention rather than copying it into product/account history by default.

---

# 19. Telemetry boundary

WPE remote services must not infer that account connection grants analytics consent.

Examples requiring a separate future decision/disclosure/choice if implemented:
- feature usage counters;
- screen/view events;
- module enable/disable history sent remotely;
- performance timings unrelated to an explicit service request;
- plugin/theme inventory analytics;
- active-user/content counts;
- docs-search analytics retained for product research;
- periodic `site alive` heartbeat whose purpose is analytics rather than activation/security.

No hidden telemetry is Accepted in Phase 0.

---

# 20. Disconnect matrix

On `Disconnect WPE Account`:

Local actions:
- revoke remote credentials when reachable;
- delete access/refresh credentials from Vault;
- delete one-time linking artifacts;
- clear authenticated account/support/catalog caches;
- retain only explicitly justified non-secret references/history;
- do not delete local Free definitions/data.

Remote actions:
- mark/revoke site connection according to service policy;
- token/session revocation;
- account itself remains unless user separately requests account deletion;
- support tickets/messages remain under support retention/deletion policy;
- commercial/activation/security records may remain under RR4/RR6 policy;
- service must not claim all remote data erased merely because connection was disconnected.

UI explains this difference.

---

# 21. Account deletion / data-subject request boundary

If WPE service later exposes account deletion/export:
- it is a distinct authenticated service operation;
- impact preview includes site connections, support history and commercial records;
- records that cannot be immediately erased are identified with reason/category rather than silently retained;
- local plugin cache/credentials are cleaned after successful deletion/disconnect;
- external processors/providers are described by service privacy policy.

WordPress personal-data exporter/eraser cannot promise deletion of remote service records unless a certified remote API operation actually performs/returns that result.

---

# 22. Backups / restore implications

A WordPress backup may contain:
- local site activation references;
- cached entitlement artifact;
- support cache metadata;
- Vault-encrypted refresh credential/key slots depending backup scope.

Restore to a clone must not silently resume remote authenticated activity as the original site without clone/activation policy.

Remote service records are not erased when a local WordPress backup is deleted.

Restoring an old local backup does not roll back service-side deletion/revocation/account state.

---

# 23. Environment separation

Production/staging/development service contexts must not accidentally share:
- OAuth credentials;
- site activation authority;
- idempotency namespace;
- support test data;
- entitlement signer trust without explicit environment profile.

A staging clone should not emit production support/billing/update side effects merely because it inherited local IDs.

---

# 24. Privacy-policy/readme disclosure requirements

Before release, WPE Free documentation must clearly explain:
- which optional WPE remote services exist;
- when the plugin contacts them;
- what high-level data categories are sent;
- purpose;
- service Terms/Privacy links;
- account requirement where applicable;
- that account connection is not telemetry consent;
- how to disconnect;
- that remote support/commercial records follow service-side retention policy.

Where WordPress privacy-policy suggestion integration applies, provide administrator-facing suggested policy text through the appropriate WordPress mechanism.

---

# 25. Future service implementation evidence — NOT AUTHORIZED

Before shipping remote service integration:
- endpoint-by-endpoint transmitted-field inventory;
- OpenAPI schemas match this minimization contract;
- privacy notice/readme review;
- opt-in/account-link UX evidence;
- no network call on clean Free activation;
- public Catalog/Docs/Status requests omit account/site IDs unless required;
- token/header/body log redaction;
- support diagnostics preview/redaction;
- disconnect/revoke/cache cleanup;
- remote deletion/export behavior where offered;
- clone/restore isolation;
- retention cleanup jobs;
- unauthorized/over-scoped API response tests;
- no hidden telemetry/background heartbeat;
- service access-control and staff-role audit for P2/P4 support data.

No remote WPE endpoint, telemetry collector, account client or retention job is authorized by this document.

## Current primary references

- WordPress.org Detailed Plugin Guidelines: https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/
- WordPress.org Common Issues / external services disclosure: https://developer.wordpress.org/plugins/wordpress-org/common-issues/
- WordPress Plugin Privacy guidance: https://developer.wordpress.org/plugins/privacy/
- WordPress suggested privacy-policy text: https://developer.wordpress.org/plugins/privacy/suggesting-text-for-the-site-privacy-policy/
