# ADR-0060 — Remote Service Privacy, Consent & Retention Boundaries

Status: **Accepted platform/privacy architecture / service-runtime evidence pending**  
Date: 2026-08-27

## Context

WPEssential includes optional WPE-controlled remote resources for account linking, site activation, signed entitlement retrieval, catalog/plans, support, documentation, release notes and service status. Those resources can be useful without turning a WordPress installation into a telemetry client or silently exporting site/user data.

ADR-0054 separates the remote resource/trust domains. The field-level contract in `docs/PLATFORM/REMOTE-SERVICE-PRIVACY-RETENTION-MATRIX.md` now defines what may cross the site/service boundary and why.

A generic statement such as “the account is connected” is not sufficient authorization for unrelated analytics, diagnostics, site inventory or content collection.

## Decision

WPE remote-service integration follows **purpose-scoped transmission + explicit retention ownership**.

### Core privacy invariant

**Connecting a WPE account authorizes only the data needed for the explicitly requested account/service functions. It does not authorize hidden analytics, telemetry, unrelated site inventory collection or open-ended diagnostics upload.**

### Activation and public resources

- Activating WPE Free alone sends nothing to WPE service.
- Public Catalog, Docs, Release Notes and Service Status should not receive account/site/installation identifiers unless the specific resource genuinely requires authenticated/site-scoped behavior.
- A public service request is not a telemetry heartbeat.

### Account/site linking

Before account/site linking, WPE discloses the categories of information required for that operation, such as normalized site origin, random installation identity, site/network scope, requested product and compatibility data where needed.

Account connection is **not** analytics/telemetry consent.

Random installation identity is an opaque application identifier, not a device fingerprinting mechanism.

### Data minimization

Transmit the minimum field set needed for the requested operation.

Prefer opaque references over replicated personal/business content.

Do not transmit or store through ordinary remote-service resources:
- WordPress passwords or password hashes;
- salts;
- Application Passwords;
- unrelated API/OAuth/webhook credentials;
- private keys;
- card number/CVC;
- unrelated plugin/theme/source/database/user/content inventories.

P3 credentials travel only to the intended authenticated/security endpoint and are never generic logs, analytics fields, diagnostics fields or browser-localized data.

### Resource-specific authority

Remote and local authority remain explicit:
- Account summary: WPE service authority; bounded local cache.
- Site activation: WPE service commercial/account authority; local client holds only required references/cache.
- Signed entitlement: trust derives from ADR-0042 signature/freshness/binding, not generic REST/TLS alone.
- Catalog/Docs/Release/Status: display/information resources, not executable or entitlement authority.
- Support tickets/messages/attachments: WPE service authority; WordPress keeps only bounded/minimal local state where useful.
- Diagnostics: user-approved support artifact, not automatic account telemetry.

### Diagnostics consent

Diagnostics upload requires a separate explicit preview/approval action.

Opening Support or connecting an account does not imply diagnostics consent.

Default diagnostic exclusions include:
- DB dumps;
- raw `wp-config.php`;
- secrets/tokens/salts;
- Form/Chat/member/customer content;
- private uploads;
- backup archives;
- unrestricted server logs;
- plugin/theme source.

The approved diagnostic manifest records selected categories/files, redaction profile/version and local approval metadata without turning the manifest into a copy of the sensitive data.

### Retention classes

Remote retention uses lifecycle classes rather than one universal duration:
- **RR0** — no intentional application persistence;
- **RR1** — one-time transaction/replay window;
- **RR2** — bounded cache/freshness;
- **RR3** — active connection state;
- **RR4** — operational reconciliation/history;
- **RR5** — explicit user-created service record;
- **RR6** — minimal security/abuse/audit evidence.

No class means “keep forever by default”. Exact durations remain service-policy/evidence work and must be purpose-specific.

### Logs

Service/application logs prefer safe identifiers, endpoint/resource class, outcome/error class, timings and correlation IDs.

They do not intentionally persist bearer/refresh tokens, OAuth codes/verifiers, pre-signed URLs or full request/response bodies by default.

### Disconnect vs deletion

Disconnect means:
- stop authenticated site/account service use;
- revoke remote credentials where possible;
- delete local access/refresh credentials;
- clear or expire local account/site caches according to policy.

Disconnect does **not** automatically mean:
- delete the WPE account;
- erase support tickets/messages/attachments;
- erase required commercial/security history;
- delete remote records owned by another explicit service lifecycle.

Those are separate actions/policies and must be represented truthfully.

### Telemetry

There is no hidden WPE telemetry in the accepted account/service contract.

If product analytics/telemetry is later proposed, it requires a separate explicit product/privacy decision with event schema, purpose, disclosure/consent, destination, retention and disable behavior. It may not be piggybacked on entitlement, support, docs or account traffic.

## WordPress.org / privacy alignment

This architecture is intentionally compatible with WordPress.org expectations around disclosed/authorized external service communication and WordPress privacy-by-design principles such as minimization, purpose limitation, retention limitation and user choice.

This ADR is a technical/product architecture decision, not jurisdiction-specific legal advice.

## Consequences

Positive:
- account connection cannot silently become telemetry consent;
- remote resources disclose and minimize transmitted fields;
- public resources stay public where possible;
- support diagnostics become explicit and reviewable;
- secrets and user/business content are less likely to leak into service logs;
- disconnect/deletion semantics remain truthful;
- retention can be reasoned about by purpose instead of one arbitrary global TTL.

Cost:
- service endpoints require field inventories and retention ownership;
- support/diagnostics flows need preview/redaction UX;
- remote/local cleanup semantics need explicit implementation;
- infrastructure logging configuration must be verified rather than assumed.

## Evidence still required

After explicit owner development/executable-spike consent:
- concrete OpenAPI field schemas/scopes;
- OAuth/token lifetimes, revoke/disconnect and failed-revocation behavior;
- service log/redaction configuration and tests;
- support/diagnostics preview, redaction and upload fixtures;
- retention/cleanup/deletion/export workflows by resource;
- cache invalidation and account/site disconnect behavior;
- clone/site-transfer privacy behavior;
- access-control tests for support content/attachments;
- data-residency/subprocessor/service-policy documentation where applicable;
- verification that public resource calls do not add hidden site/account identifiers;
- security/abuse-log minimization and retention evidence.

No service endpoint, account flow, telemetry system, diagnostics upload, deletion workflow or retention job has been implemented or executed by this decision.
