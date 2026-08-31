# WPEssential — Email Provider Version & Evidence Registry

Status: **Phase 0 planning / static official-doc evidence only / no Email provider runtime authorized**  
Review date: **2026-08-28**  
Related: ADR-0029, ADR-0058, ADR-0063, `EMAIL-PROVIDER-CAPABILITY-MATRIX.md`.

## 1. Purpose

Email provider APIs and webhook contracts do not all use the same versioning model.

WPEssential must not reduce compatibility to a single `provider version` string when:
- WordPress `wp_mail()` behavior changes with WordPress/bundled PHPMailer;
- generic SMTP depends on protocol + server capability/security profile;
- Amazon SES exposes an API v2 contract;
- SendGrid exposes a v3 Web API;
- Mailgun's current OpenAPI description spans endpoints with different path-version families;
- Postmark exposes REST endpoints without a provider-wide path version and evolves webhook payloads/documentation over time.

This registry defines how exact send/event profiles are versioned, reviewed and eventually certified.

Static evidence does **not** grant ET0–ET5 certification.

Current ET-certified provider count remains **0**.

---

# 2. Stable profile identity

A future certified Email transport profile is identified conceptually by:

`provider_key + provider_profile_version + send_api_or_transport_profile + event_schema_profile + security_profile + adapter_version + region/account_scope`

Examples:

`email.amazon-ses + 2026-08 + ses-api-v2 + ses-event-publishing-2026-08 + aws-auth/profile + adapter-v1 + us-east-1`

`email.twilio-sendgrid + 2026-08 + web-api-v3 + event-webhook-2026-08 + signed-webhook + adapter-v1 + global`

`email.postmark + 2026-08 + rest-schema-2026-08 + webhook-schema-2026-08 + basic-auth-ip-profile + adapter-v1 + server`

A provider marketing name never substitutes for these dimensions.

---

# 3. Email static-evidence scale

Existing EE scale remains:
- EE0 — insufficient current official evidence;
- EE1 — send/transport contract reviewed;
- EE2 — send + event semantics reviewed;
- EE3 — send + event + security/deviation/retention concerns reviewed.

EE3 remains **not ET0**.

---

# 4. Compatibility states

A runtime/provider profile can later be:
- `detected_unreviewed`;
- `candidate_static_reviewed`;
- `certified_exact`;
- `certified_range`;
- `newer_schema_unverified`;
- `security_profile_incomplete`;
- `known_incompatible`;
- `degraded_send_only`;
- `degraded_events_unverified`;
- `reconciliation_required`.

A provider send API can remain usable while its webhook security/event schema becomes uncertified. Those states are separate.

---

# 5. `email.wordpress-wp-mail`

## Version dimensions

- WordPress version;
- bundled PHPMailer/WP_PHPMailer behavior;
- active filters/hooks/interceptors;
- PHP/mail environment;
- underlying MTA configuration outside WPE control.

Current WordPress snapshot reviewed:
- WordPress 7.1 released 2026-08-19;
- `wp_mail()` has documented behavior changes across WordPress releases, including multipart Content-Type handling improvement in 6.9;
- WordPress wraps bundled PHPMailer through `WP_PHPMailer`.

## WPE version rule

`wp_mail` compatibility is tied to the WPE platform compatibility matrix P-001, not a standalone external provider semantic version.

Future profile key records:
- WordPress exact/range;
- whether another plugin intercepts/reconfigures mail;
- local send result/error hooks available;
- whether a separately certified external provider can be correlated.

`wp_mail() === true` never becomes destination delivery proof regardless of version.

Current: **EE3 static / ET0–ET5 none**.

---

# 6. `email.smtp-generic`

## Version dimensions

SMTP has protocol/extension/security capabilities rather than one provider version.

Profile records:
- server/vendor label for diagnostics only;
- hostname/port;
- implicit TLS vs STARTTLS mode;
- TLS security profile;
- AUTH mechanisms;
- SMTP extensions negotiated;
- SIZE limit;
- DSN support if used;
- connection/retry semantics;
- certificate identity;
- account/provider scope.

## WPE version rule

A generic SMTP profile is certified by negotiated/probed capability plus fixture evidence, not by accepting any server that speaks SMTP.

A server software upgrade can invalidate the exact profile if TLS/auth/extension behavior materially changes.

SMTP response acceptance remains transport acceptance; it is not automatically final receiving-server or inbox truth.

Current: **EE3 static / ET0–ET5 none**.

---

# 7. `email.amazon-ses`

## Current static profile

Official documentation reviewed 2026-08-28 identifies **Amazon SES API v2** as the current API contract. The API v2 reference was published/updated in August 2026 in the reviewed documentation.

Send profile:
- SES API v2 `SendEmail`;
- AWS Region is part of endpoint/account identity;
- IAM credential/policy profile is separately recorded;
- configuration set/event destination profile is part of event support.

Event profile records:
- event-publishing configuration version/date reviewed;
- SEND/DELIVERY/BOUNCE/COMPLAINT/DELIVERY_DELAY/etc mappings;
- provider destination type (SNS/EventBridge/Firehose/CloudWatch or other certified path) where relevant;
- account/region/configuration-set scope.

## WPE version rule

SES API v2 is the current candidate send contract; a future API generation or material event-schema change requires a new provider profile review.

AWS SDK package version is an implementation dependency and does not itself define SES semantic certification.

Current: **EE3 static / ET0–ET5 none**.

---

# 8. `email.twilio-sendgrid`

## Current static profile

Official docs identify the **SendGrid v3 Web API**.

Send path:
- `POST /v3/mail/send`;
- bearer API key;
- global or EU regional base URL/profile;
- current documented message/recipient/size constraints captured in provider profile.

Event path:
- Event Webhook schema/profile reviewed by date;
- webhook ID is stable configuration identity; friendly name is not programmatic identity;
- signed Event Webhook can be enabled;
- OAuth verification is a separate optional webhook security mechanism;
- signature verification public key/profile is versioned independently from send credentials.

## WPE version rule

The provider profile records `sendgrid-v3` plus a dated Event Webhook/security schema snapshot. `v3` alone does not prove the event payload/security contract will never evolve.

Global and EU regional profiles remain distinct where data location/base URL/account semantics differ.

Current: **EE3 static / ET0–ET5 none**.

---

# 9. `email.mailgun`

## Current static profile

Current official Mailgun API documentation is described by **OpenAPI 3.0.0 / OAS 3.1.0**, but endpoint paths span several version namespaces.

Examples in the reviewed docs:
- message send: `/v3/{domain}/messages` and `/messages.mime`;
- some Domain/Webhook operations expose `/v3` and `/v4` forms;
- US and EU API base URLs are separate.

Webhook security profile:
- HMAC-SHA256 over timestamp + token with Webhook Signing Key;
- replay token cache/timestamp checks are supported/recommended;
- Mailgun also documents optional TLS client authentication for webhooks.

## WPE version rule

WPE must **not** label the entire integration simply `Mailgun API v3`.

Provider profile records independently:
- send endpoint family;
- domain/account endpoint family if used;
- webhook payload schema review date;
- webhook signing profile;
- US/EU region;
- parent/subaccount scope where used.

Additive webhook fields are tolerated according to provider guidance; strict JSON equality is prohibited.

Current: **EE3 static / ET0–ET5 none**.

---

# 10. `email.postmark`

## Current static profile

Current official Postmark REST base is:
`https://api.postmarkapp.com`

The reviewed send API is not represented by a provider-wide `/vN` path. Authentication uses server/account token headers according to resource scope.

Therefore WPE versions Postmark by a **dated provider schema/profile**, not an invented `Postmark API v1` label.

Webhook security facts currently documented:
- Postmark does **not** currently sign outbound webhooks with HMAC;
- recommended protection is HTTPS + HTTP Basic Authentication + IP allowlisting;
- payload shape validation and idempotency are required;
- webhook event types can be verified for reachability through Postmark's verification feature, but reachability verification is not origin cryptographic signing.

## WPE version rule

Provider profile records:
- REST schema/date reviewed;
- server/message stream identity;
- send endpoint/response schema;
- webhook event schema/date reviewed;
- current Basic Auth/IP allowlist security profile;
- retry/dedupe identity profile.

If Postmark later introduces cryptographic signing, that becomes a new security-profile revision rather than silently changing old certified evidence.

Current: **EE3 static / ET0–ET5 none**.

---

# 11. Current paper registry

| Provider | Send profile | Event/security profile | Static evidence | ET certification |
|---|---|---|---:|---|
| `email.wordpress-wp-mail` | WordPress/P-001 versioned | local hooks only unless separately correlated | EE3 | 0 |
| `email.smtp-generic` | negotiated SMTP/TLS/Auth capability profile | DSN/feedback only if explicitly certified | EE3 | 0 |
| `email.amazon-ses` | SES API v2 + region/account | dated SES event-publishing profile | EE3 | 0 |
| `email.twilio-sendgrid` | SendGrid Web API v3 + region | dated Event Webhook + signature/OAuth profile | EE3 | 0 |
| `email.mailgun` | endpoint-specific `/v3`/other path profile + region | dated webhook schema + HMAC/TLS-client-auth profile | EE3 | 0 |
| `email.postmark` | dated unversioned REST schema + server/message stream | dated webhook schema + Basic Auth/IP profile | EE3 | 0 |

---

# 12. Upgrade/schema-drift policy

## Backward-compatible additive event field

Can remain within a certified range only if parser contract ignores unknown optional fields and regression evidence confirms normalized facts remain correct.

## Removed/renamed/semantic event field

Requires provider-profile revision and ET regression certification before Supported event truth continues.

## Security-mechanism change

Requires new security profile and verification fixtures. Never silently downgrade from signed/HMAC to unauthenticated webhook ingestion.

## Provider API generation change

New API major/generation defaults to `newer_schema_unverified` until send/error/idempotency/correlation/event behavior is re-certified.

## Regional endpoint change

Region/base URL/account/subaccount scope is part of profile identity and cannot be inferred from provider name only.

---

# 13. Future ET certification matrix — NOT AUTHORIZED

After explicit owner consent, each exact profile must exercise:
- credential/permission failure;
- sender/domain restrictions;
- valid send;
- invalid recipient/payload;
- rate limit/429;
- provider 5xx/timeouts;
- unknown submission outcome;
- provider correlation ID;
- verified webhook/event ingress according to strongest available provider security profile;
- malformed/tampered/replayed event;
- duplicate/out-of-order event;
- delivery/bounce/complaint/suppression;
- provider-specific region/subaccount/message-stream isolation;
- schema additive-field tolerance;
- event schema/security-profile drift;
- privacy/log redaction;
- retention;
- JobService retry/backpressure;
- upgrade from certified profile to newer provider/API profile.

No Email send, provider API, webhook or SMTP fixture has been executed.

---

# 14. Static references reviewed

- WordPress `wp_mail()` and `WP_PHPMailer` developer references; WordPress 7.1 release information.
- RFC SMTP/TLS sources already captured in Email certification architecture.
- Amazon SES API v2 Reference / `SendEmail` and event-publishing documentation.
- Twilio SendGrid v3 API / Mail Send / Event Webhook / Signed Event Webhook documentation.
- Mailgun current API OpenAPI documentation, message endpoint families, webhook payload and HMAC security documentation.
- Postmark current API overview, send API and webhook security/delivery/bounce documentation.

## Development gate

**No provider SDK installation, API key configuration, SMTP connection, email send, webhook endpoint registration/test, DNS change, provider event simulation or ET certification is authorized until explicit owner development consent under ADR-0014.**
