# ADR-0067 — Email Provider Version & Evidence Registry

Status: **Accepted compatibility/evidence architecture / executable ET certification pending**  
Date: 2026-08-28

## Context

ADR-0058 and ADR-0063 establish Email delivery truth and first provider source-truth profiles. A provider name alone is not a sufficient compatibility key because providers use different versioning models:

- WordPress `wp_mail()` behavior is tied to WordPress/bundled mail runtime;
- SMTP is negotiated protocol/security capability, not a SaaS version;
- Amazon SES currently exposes API v2;
- SendGrid exposes Web API v3 plus evolving Event Webhook/security schemas;
- Mailgun's current API documentation contains endpoint families using different path versions;
- Postmark REST endpoints are not provider-wide `/vN` paths and its webhook security contract is independently evolving.

## Decision

The authoritative planning contract is `docs/ARCHITECTURE/EMAIL-PROVIDER-VERSION-EVIDENCE-REGISTRY.md`.

A future Email certification is versioned conceptually by:

`provider_key + provider_profile_version + send_api_or_transport_profile + event_schema_profile + security_profile + adapter_version + region/account_scope`

### Provider-version truth

WPE never invents one universal provider version label where the provider does not have one.

Current paper identities:
- `wp_mail` → WordPress/P-001 runtime profile;
- SMTP → negotiated SMTP/TLS/AUTH/extension profile;
- SES → API v2 + region/account/event-publishing profile;
- SendGrid → Web API v3 + dated Event Webhook/security profile;
- Mailgun → endpoint-specific path families + dated webhook/security profile + region;
- Postmark → dated REST/webhook profile rather than an invented `Postmark v1`.

### Send/event separation

Send compatibility and event-feedback compatibility can degrade independently.

A provider may therefore be:
- send certified but event security/schema unverified;
- event capable but send API changed;
- degraded send-only;
- degraded events-unverified.

WPE does not collapse those states into one green `Connected` status.

### Schema drift

Additive optional event fields can remain compatible only when parsers tolerate unknown fields and evidence confirms normalized facts remain correct.

Removed/renamed/semantic fields, API generation changes, security-mechanism changes or region/account-scope changes require a new provider-profile review and appropriate ET regression evidence.

### Security downgrade prohibition

If a previously certified provider profile used a cryptographic webhook verification method, WPE cannot silently accept unauthenticated webhooks after a provider/configuration change.

Provider-specific strongest available controls remain explicit.

For the current Postmark profile, official documentation states outbound webhooks are not HMAC-signed; WPE therefore records the actual HTTPS + Basic Auth + IP-allowlist/payload-validation profile rather than fabricating signature verification.

## Current static registry

All six initial profiles remain EE3 static-paper maturity and **0 ET-certified**:
- `email.wordpress-wp-mail`;
- `email.smtp-generic`;
- `email.amazon-ses`;
- `email.twilio-sendgrid`;
- `email.mailgun`;
- `email.postmark`.

No current provider/API/version statement in this ADR is a support/certification claim.

## Consequences

Positive:
- API/webhook drift becomes visible;
- region/account/subaccount differences are auditable;
- webhook security cannot silently weaken;
- support can identify exact send/event profile under test;
- provider marketing version language cannot override WPE delivery truth.

Cost:
- provider schema/security changes require ongoing review;
- some provider profiles may temporarily be `newer_schema_unverified`;
- send and event compatibility require separate evidence.

## Evidence still required

After explicit owner development consent:
- exact provider/API/SMTP profile fixtures;
- auth/scopes/sender restrictions;
- send/error/rate-limit/unknown-outcome behavior;
- provider message correlation;
- strongest available webhook origin verification;
- malformed/tampered/replayed/duplicate/out-of-order events;
- bounce/complaint/suppression/delivery truth;
- region/subaccount/message-stream isolation;
- schema drift;
- privacy/log redaction;
- JobService load/backpressure;
- ET0–ET5 certification.

No provider API/SMTP/email/webhook execution was performed to accept this ADR.
