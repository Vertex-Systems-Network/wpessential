# ADR-0063 — Email Provider Source-Truth Profiles

Status: **Accepted architecture / provider runtime certification pending**  
Date: 2026-08-27

## Context

ADR-0058 established provider-neutral Email Delivery truth and ET0–ET5 certification. Static research now covers the initial provider set:
- WordPress `wp_mail()`;
- generic SMTP;
- Amazon SES;
- Twilio SendGrid;
- Mailgun;
- Postmark.

These providers use materially different event names, webhook security models and correlation mechanisms. Treating a provider's word `sent`, `processed` or `delivered` as a universal state would produce false delivery claims and unsafe retry/suppression behavior.

## Decision

WPEssential maintains stable provider profiles with provider-specific source-event semantics, then normalizes those verified facts into ADR-0058's canonical Event Ledger.

Stable initial keys:
- `email.wordpress-wp-mail`
- `email.smtp-generic`
- `email.amazon-ses`
- `email.twilio-sendgrid`
- `email.mailgun`
- `email.postmark`

Provider keys, adapter version, provider API/event version and webhook-security profile are separate fields.

## Static evidence vs runtime certification

Static provider research uses:
- EE0 named candidate;
- EE1 submission docs reviewed;
- EE2 delivery/failure/event docs reviewed;
- EE3 security/correlation/privacy edge semantics reviewed.

Runtime support remains ET0–ET5 from ADR-0058.

**EE never implies ET. At acceptance time all initial profiles are EE3 paper maturity and 0 providers are ET-certified.**

## Canonical mappings

### WordPress `wp_mail()`
Successful `wp_mail()` means local processing only. It is not user receipt, receiving-server delivery, bounce, complaint or provider acceptance evidence.

### Generic SMTP
SMTP 2xx/4xx/5xx are transport command outcomes. A relay accepting the message does not prove final receiving-server/inbox delivery unless a separately certified feedback profile establishes it.

### Amazon SES
- SEND → provider accepted/will attempt delivery;
- DELIVERY → recipient mail server accepted;
- DELIVERY_DELAY → temporary delay;
- BOUNCE → delivery failure source fact;
- COMPLAINT → complaint fact;
- REJECT/RENDERING_FAILURE remain distinct failures;
- OPEN/CLICK are observations only.

### Twilio SendGrid
- processed → provider processing/accepted, not delivery;
- delivered → receiving server accepted;
- deferred/bounce/dropped remain distinct failure/drop facts;
- spam report/unsubscribe are distinct source facts;
- open/click are observations only.

Signed Event Webhook or another accepted secure webhook mode must be certified for production event ingestion. Raw payload bytes are required where the provider's signature algorithm requires them.

### Mailgun
- accepted → queued by Mailgun;
- delivered → recipient email server accepted;
- temporary_fail/permanent_fail remain distinct;
- complained/unsubscribed remain distinct;
- open/click are observations.

Webhook HMAC/timestamp/token verification and replay handling are provider-profile requirements.

### Postmark
- Delivery webhook → destination server returned OK; this is not inbox placement;
- Bounce → provider bounce classification;
- Spam Complaint → complaint/provider suppression source fact;
- Subscription Change is distinct;
- open/click are observations.

Current Postmark docs do not define a signed webhook scheme equivalent to SendGrid/Mailgun. WPE must not fabricate signature-verification support. The strongest real HTTPS/auth/header/network controls must be certified and represented truthfully.

## Event-ledger rule

Provider feedback is append-oriented evidence, not one monotonic status integer.

A later complaint or bounce can coexist with an earlier Delivery source fact. Current UI outcome is derived from the ledger without deleting contradictory/chronological facts.

## Retry / unknown-outcome rule

Network timeout after submission can mean provider acceptance occurred without a response.

Each provider profile must document whether it supports:
- request idempotency;
- message/status lookup;
- correlated event reconciliation;
- safe retry.

If certainty cannot be established, WPE exposes `Unknown Outcome` rather than automatically causing potentially duplicate high-impact email.

## Suppression boundary

Provider suppression, complaint and unsubscribe are source facts. They do not automatically become global WPE preference changes or affect non-email channels/Membership access.

Notification policy owns scoped consequences.

## Privacy rule

Provider metadata/tags used for correlation contain opaque non-PII identifiers only.

Tracking data is optional; open/click never becomes `Read`, `Human Seen` or `Inbox Confirmed`.

Provider-specific retention/privacy constraints are part of the profile and future certification.

## Consequences

Positive:
- truthful cross-provider UI;
- safer retry/reconciliation;
- provider webhook security differences remain explicit;
- provider feature changes can downgrade one profile without rewriting Email core;
- no provider marketing term becomes WPE authorization truth.

Costs:
- each provider needs maintained mapping/certification fixtures;
- production support requires webhook-security and delivery-fact evidence, not only send API success;
- provider API/event changes require profile-version review.

## Evidence still required

After explicit development consent:
- send/API/SMTP adapters;
- credential/rate-limit/outage fixtures;
- provider message-ID correlation;
- valid/invalid webhook authenticity;
- duplicate/out-of-order/replay tests;
- accepted vs delivery vs bounce/complaint/suppression fixtures;
- unknown submission outcome and retry/idempotency behavior;
- privacy/tag/log redaction;
- JobService backlog/backpressure;
- provider environment/subaccount/stream/config-set isolation;
- ET0–ET5 evidence per profile.

No provider execution has occurred.

## Supporting document

`docs/ARCHITECTURE/EMAIL-PROVIDER-CAPABILITY-MATRIX.md`
