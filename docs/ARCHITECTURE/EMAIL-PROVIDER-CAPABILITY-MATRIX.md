# WPEssential — Email Provider Capability & Evidence Matrix

Status: **Phase 0 planning only / no transport-provider implementation authorized**  
Related: ADR-0029, ADR-0058, ET0–ET5 provider certification, Notification System, Emails Builder, Connections/Event Inbox.

## 1. Purpose

Define provider-specific source truth without allowing provider terminology such as `sent`, `processed` or `delivered` to override WPEssential's canonical Email Delivery model.

Canonical WPE path remains:

`Notification Instance → Recipient Delivery → Rendered Message → Transport Attempt → Provider Message Reference → verified Provider Event Ledger → derived Delivery Outcome`

Provider-specific capabilities and event names are normalized only after their semantics are documented and later verified.

## 2. Static evidence vs runtime certification

Paper evidence levels:
- **EE0 — named candidate only**
- **EE1 — official send/transport documentation reviewed**
- **EE2 — official delivery/failure/event documentation reviewed**
- **EE3 — security/correlation/privacy/edge semantics reviewed enough for a future executable profile**

Runtime certification remains separate:
- **ET0 — Configurable/Detected**
- **ET1 — Submission Certified**
- **ET2 — Correlation + event-ingress certified**
- **ET3 — Delivery/failure truth certified**
- **ET4 — Complaint/suppression/reconciliation certified**
- **ET5 — Production Profile certified**

**EE never implies ET. Current ET-certified provider count is 0.**

## 3. Stable provider keys

- `email.wordpress-wp-mail`
- `email.smtp-generic`
- `email.amazon-ses`
- `email.twilio-sendgrid`
- `email.mailgun`
- `email.postmark`

Provider keys are stable WPE identities; provider display names and API versions are metadata.

## 4. Canonical capability columns

Each profile declares:
- submission mechanism;
- synchronous acceptance meaning;
- provider message/correlation identity;
- receiving-server delivery event;
- transient delay/failure event;
- permanent bounce/failure event;
- complaint/spam event;
- suppression/unsubscribe event;
- open/click telemetry;
- webhook/event authenticity profile;
- event dedupe/order behavior;
- status lookup/reconciliation support candidate;
- regional/account/subaccount isolation;
- provider tags/metadata privacy constraints;
- retry/idempotency/unknown-outcome notes;
- evidence maturity;
- runtime ET certification.

## 5. Summary matrix

| Provider key | Submission | Receiving-server delivery evidence | Failure/bounce | Complaint/suppression | Engagement | Event-security static profile | EE | ET |
|---|---|---|---|---|---|---|---:|---:|
| `email.wordpress-wp-mail` | WordPress `wp_mail()` | No | synchronous local error only unless another certified transport supplies evidence | No native provider evidence | No | WordPress/local only | EE3 | ET0 |
| `email.smtp-generic` | SMTP | Not inherently final-recipient delivery; relay `250` is transport acceptance | SMTP 4xx/5xx; DSN only if separately certified | No generic complaint/suppression stream | No | TLS/auth profile; no universal signed event channel | EE3 | ET0 |
| `email.amazon-ses` | SES API/SMTP | SES `DELIVERY` = recipient mail server accepted | `BOUNCE`, `DELIVERY_DELAY`, `REJECT`, rendering failure | `COMPLAINT`, suppression/subscription facts depending feature | OPEN/CLICK if tracking enabled | Event destination transport/profile must be certified; AWS identity/region/config-set correlation | EE3 | ET0 |
| `email.twilio-sendgrid` | SendGrid API/SMTP | `delivered` = receiving server accepted | `deferred`, `bounce`, `dropped`; `processed` is not delivery | spam report, unsubscribe, suppression/account facts | OPEN/CLICK when enabled | Signed Event Webhook ECDSA and/or OAuth; raw bytes required for signature verification | EE3 | ET0 |
| `email.mailgun` | Mailgun API/SMTP | `delivered` = recipient email server accepted | `temporary_fail`, `permanent_fail`; `accepted` = queued | `complained`, `unsubscribed` | OPEN/CLICK when enabled | HMAC-SHA256 timestamp+token signature; replay/timestamp defense candidate | EE3 | ET0 |
| `email.postmark` | Postmark API/SMTP | Delivery webhook = destination server returned OK; not inbox placement | Bounce webhook classifications | Spam Complaint + suppression/deactivation facts; subscription change separate | OPEN/CLICK webhooks | No provider webhook signature claim in current docs; HTTPS + HTTP auth/custom headers/IP controls profile must be certified | EE3 | ET0 |

## 6. WordPress `wp_mail()` profile

### Source truth
Official WordPress behavior establishes that successful `wp_mail()` processing does **not** prove user receipt.

### WPE mapping
- `wp_mail() === true` → `local_transport_processed` only.
- synchronous `WP_Error`/exception → local submission failure with safe error class.
- no provider message ID inferred.
- no delivery/bounce/complaint/suppression/open/click evidence inferred.

### Integration caveat
A third-party SMTP/mail plugin may intercept PHPMailer or mail delivery. WPE must not infer which provider actually handled mail solely from `wp_mail()` success.

Future provider-specific integration can elevate truth only after its own ET certification.

## 7. Generic SMTP profile

### Transport truth
RFC 5321 reply classes are transport command outcomes:
- 2yz = positive completion;
- 4yz = transient negative completion;
- 5yz = permanent negative completion for that request.

After a receiver accepts message data, it accepts responsibility to deliver or relay. If WPE is connected to a relay, that is still not necessarily the final destination server.

### WPE mapping
- relay acceptance → `transport_accepted`;
- SMTP 4xx → transient/retry candidate;
- SMTP 5xx → permanent for the exact request unless profile says otherwise;
- DSN/provider feedback is not assumed.

### Future certification
ET3+ for a generic SMTP profile requires a real, certified feedback/DSN mechanism. Plain SMTP credentials alone cannot claim receiving-server delivery truth.

## 8. Amazon SES profile

Provider key: `email.amazon-ses`

### Official source facts reviewed
SES event publishing distinguishes:
- `SEND` — send request succeeded and SES will attempt delivery;
- `REJECT` — accepted by SES but rejected before recipient delivery, such as virus detection;
- `DELIVERY` — recipient mail server accepted;
- `BOUNCE` — permanent delivery failure after applicable retry behavior;
- `COMPLAINT` — delivered then marked as spam;
- `DELIVERY_DELAY` — temporary delivery issue;
- `RENDERING_FAILURE`;
- `SUBSCRIPTION`;
- OPEN/CLICK when configured.

### WPE normalization
- `SEND` → `provider_accepted_or_queued`, never delivery.
- `DELIVERY` → `receiving_server_delivered`.
- `BOUNCE` → permanent failure fact according to event detail.
- `DELIVERY_DELAY` → delay/temporary-failure fact.
- `COMPLAINT` → complaint fact that can coexist with earlier Delivery.
- OPEN/CLICK → observation only.

### Correlation
SES returns provider message IDs; WPE maintains opaque Delivery↔SES message-ID mapping. Do not put PII/private WPE state into convenient provider tags.

### Event transport
SES can publish events through AWS destinations such as SNS/Firehose/CloudWatch. The selected ingress path must have its own authenticity/account/region/configuration-set certification.

### Multi-recipient caveat
Provider feedback may be recipient-specific or redact complaint recipient data in some circumstances. WPE's own opaque message/recipient correlation is therefore important.

## 9. Twilio SendGrid profile

Provider key: `email.twilio-sendgrid`

### Official source facts reviewed
Event Webhook delivery events include:
- `processed` — message received by SendGrid and ready for delivery;
- `delivered` — delivery to receiving server;
- `deferred`;
- `bounce`;
- `dropped`.

Other events include spam reports, unsubscribes, opens and clicks when enabled.

### WPE normalization
- `processed` → provider accepted/processing fact, never delivery.
- `delivered` → `receiving_server_delivered`.
- `deferred` → temporary/delayed fact.
- `bounce` → failure fact based on provider classification.
- `dropped` → provider-dropped/suppressed-style source fact with reason.
- spam report → complaint.
- open/click → observation.

### Webhook security
Current SendGrid supports Signed Event Webhook using ECDSA plus timestamp and raw payload, and also OAuth verification. WPE must verify raw bytes before transformation when signature mode is used.

Signature verification OFF cannot qualify for the same production event-ingress profile unless a separately accepted secure mode is certified.

### Privacy constraint
Current SendGrid docs warn categories/unique arguments should not contain PII and may be retained long-term. WPE opaque correlation metadata therefore must be non-PII and non-secret.

## 10. Mailgun profile

Provider key: `email.mailgun`

### Official source facts reviewed
Mailgun distinguishes:
- `accepted` — request accepted/queued by Mailgun;
- `delivered` — recipient email server accepted;
- `temporary_fail`;
- `permanent_fail`;
- `complained`;
- `unsubscribed`;
- opened/clicked when tracking is enabled.

### WPE normalization
- `accepted` → `provider_accepted_or_queued`.
- `delivered` → `receiving_server_delivered`.
- temporary/permanent fail → corresponding failure facts.
- complained → complaint.
- unsubscribe → provider-unsubscribe fact, then WPE category policy decides preference mutation.
- open/click → observation only.

### Webhook security
Current Mailgun webhook signing uses HMAC-SHA256 over timestamp + token using the Webhook Signing Key. Provider docs also recommend token replay protection and bounded timestamp validation.

WPE keeps raw signature fields only as needed for verification/audit and does not log the signing key.

### TLS client auth
Mailgun also documents TLS client certificate behavior for webhooks; this can be an optional stronger transport profile but is not required to understand provider event truth.

## 11. Postmark profile

Provider key: `email.postmark`

### Official source facts reviewed
Postmark provides modular webhooks for Delivery, Bounce, Spam Complaint, Open, Click and Subscription Change.

Delivery webhook is triggered when the destination email server returns OK. Postmark explicitly states this is **not proof of inbox placement**.

Bounce webhook includes provider bounce classification; Spam Complaint is a distinct webhook and provider behavior can deactivate an address.

### WPE normalization
- API/SMTP send acceptance → provider/transport acceptance only.
- Delivery webhook → `receiving_server_delivered`.
- Bounce webhook → temporary/permanent/source-specific failure.
- Spam Complaint → complaint + provider suppression/deactivation source fact where applicable.
- Subscription Change → provider subscription source fact; WPE category preference mapping remains policy-controlled.
- Open/Click → observation only.

### Webhook security caveat
Current Postmark documentation exposes HTTPS webhook URLs with HTTP Basic Auth/custom headers and explicitly notes Postmark does not sign webhooks in its bounce guidance. WPE must not invent a signature-verification claim.

A future ET production profile must therefore certify the strongest practical endpoint protection available for the selected Postmark configuration, including TLS, secret auth/header handling, replay/dedupe behavior and network controls where appropriate.

### Duplicate delivery
Postmark documents webhook retries on non-2xx/timeouts and notes events can arrive more than once. WPE uses event dedupe/correlation rather than exactly-once assumptions.

## 12. Provider-neutral correlation rules

1. One logical Recipient Delivery can have multiple Transport Attempts.
2. Each provider message/reference is opaque and provider-scoped.
3. Send to one recipient per provider message where provider semantics/costs permit and this materially improves correlation; multi-recipient behavior must be explicitly certified.
4. Opaque WPE correlation values contain no PII, membership state, payment state, secrets or predictable internal database IDs.
5. Provider message ID plus recipient/context may be needed when provider event identity is recipient-specific.
6. Unknown events remain appendable source facts and do not optimistically change status.

## 13. Event ordering and dedupe

All provider profiles inherit:
- at-least-once webhook/event possibility;
- duplicates are expected until proven otherwise;
- provider occurrence time and WPE receipt time stored separately;
- late bounce/complaint can follow delivery;
- complaint does not erase historical delivery fact;
- event processing is idempotent;
- current UI outcome is derived from source ledger, not a monotonic integer state.

## 14. Unknown submission outcome

A timeout after transmission can mean the provider accepted the message even though WPE did not receive a response.

Provider profile declares whether it supports:
- request idempotency;
- provider message/status lookup;
- correlated event wait/reconciliation;
- safe retry.

If none exist, WPE exposes `Unknown Outcome` rather than blindly retrying high-impact messages and risking duplicates.

## 15. Suppression and complaints

Provider suppression is not the same thing as WPE preference suppression.

Source classes:
- provider hard-bounce suppression;
- complaint suppression;
- provider account/domain policy suppression;
- provider unsubscribe;
- WPE user preference;
- WPE policy/frequency/dedupe.

Adapters emit source facts; Notification/Membership/Workflow policy decides scoped consequences.

A provider complaint must never automatically disable non-email channels or unrelated Membership access.

## 16. Engagement privacy

Open/click tracking remains optional and off-by-default candidate at platform level.

Provider profiles must declare:
- feature enabled/disabled;
- privacy disclosure implications;
- retention;
- proxy/scanner false-positive limitations;
- whether provider metadata used for tracking has special privacy/retention behavior.

Never derive `Read`, `Seen by human`, or `Inbox confirmed` from open/click.

## 17. Provider version/profile registry

Future profile record contains:
- provider key;
- WPE adapter version;
- provider API/webhook version/scope;
- auth mode;
- region/account/subaccount/message-stream/config-set scope;
- webhook security mode;
- enabled source-fact capabilities;
- evidence level EE0–EE3;
- ET certification level;
- certification date/fixtures;
- known limitations;
- deprecation date where applicable.

A provider capability can downgrade without invalidating the entire Email module.

## 18. Future ET certification evidence — not executed

For each provider:

### Submission
- valid credential;
- invalid/revoked credential;
- rate limit;
- provider outage/5xx;
- timeout/unknown outcome;
- retry/idempotency;
- provider message ID correlation;
- single vs multi-recipient behavior.

### Event ingress
- valid signature/auth;
- invalid signature/auth;
- replay;
- duplicate;
- out-of-order;
- unknown event type;
- oversized/malformed body;
- raw-body signature preservation;
- region/subaccount/profile mismatch.

### Delivery truth
- accepted/processed only;
- receiving-server delivery;
- temporary defer;
- permanent failure;
- late bounce after earlier acceptance;
- complaint after delivery;
- suppression/unsubscribe;
- tracking disabled/enabled.

### Privacy
- no PII/secrets in provider correlation metadata;
- event-log payload minimization;
- endpoint/recipient masking;
- provider retention/disclosure documented;
- tracking remains opt-in where policy requires.

### Recovery
- webhook outage then replay/reconciliation;
- provider credential rotation;
- sender/domain configuration change;
- Job Service backlog/backpressure;
- duplicate retry avoidance.

No fixture has been executed.

## 19. Initial support roadmap

Recommended future proof order after explicit development consent:
1. `wp_mail()` compatibility baseline;
2. generic SMTP submission profile;
3. one full-feedback API provider (candidate Postmark or SES depending implementation constraints);
4. SendGrid;
5. Mailgun;
6. remaining provider expansion according to user demand/evidence.

This sequence is not an authorization or final commercial commitment.

## 20. Static references

Official research references used in this Phase 0 profile:
- WordPress `wp_mail()`: https://developer.wordpress.org/reference/functions/wp_mail/
- SMTP RFC 5321: https://www.rfc-editor.org/rfc/rfc5321
- Amazon SES event publishing: https://docs.aws.amazon.com/ses/latest/dg/monitor-using-event-publishing.html
- Amazon SES EventDestination API: https://docs.aws.amazon.com/ses/latest/APIReference-V2/API_EventDestination.html
- SendGrid Event Webhook: https://www.twilio.com/docs/sendgrid/for-developers/tracking-events/event
- SendGrid Event Webhook security: https://www.twilio.com/docs/sendgrid/for-developers/tracking-events/getting-started-event-webhook-security-features
- Mailgun Webhooks: https://documentation.mailgun.com/docs/mailgun/user-manual/webhooks/webhooks
- Mailgun Webhook security: https://documentation.mailgun.com/docs/mailgun/user-manual/webhooks/securing-webhooks
- Postmark Delivery webhook: https://postmarkapp.com/developer/webhooks/delivery-webhook
- Postmark Bounce webhook: https://postmarkapp.com/developer/webhooks/bounce-webhook
- Postmark Spam Complaint webhook: https://postmarkapp.com/developer/webhooks/spam-complaint-webhook
- Postmark Webhooks API: https://postmarkapp.com/developer/api/webhooks-api

## Development gate

**No SMTP connection, email send, API key/token, webhook endpoint, provider SDK, event replay, bounce simulator, mailbox simulator or ET certification fixture may be executed before explicit owner development consent under ADR-0014.**
