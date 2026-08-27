# WPEssential — Email Transport, Delivery Truth & Provider Certification

Status: **Phase 0 planning only / no email transport implementation authorized**  
Related: ADR-0026, ADR-0029, ADR-0040, ADR-0048, ADR-0055, Email Builder and Notification System exhaustive specs.

## 1. Purpose

WPEssential must not collapse these materially different facts into one green `Sent` state:

- a notification was queued locally;
- rendering succeeded;
- `wp_mail()`/PHPMailer accepted the request;
- an SMTP relay accepted responsibility;
- a provider API accepted/queued the message;
- the destination mail server accepted the message;
- delivery was delayed;
- the message later bounced;
- the recipient complained;
- the provider suppressed the address;
- a tracking pixel/link was observed;
- the recipient actually saw/read the message.

The canonical architecture separates transport submission from provider feedback and exposes only the strongest truth actually evidenced by the selected transport/provider profile.

## 2. Existing domain boundaries remain authoritative

Email Builder owns deterministic email-safe rendering.

Notification System owns recipient/channel policy and logical Delivery records.

The Email Transport adapter owns one or more transport attempts plus provider correlation.

Connections/Event Inbox owns verified external event ingress.

Secrets Vault owns SMTP/API/OAuth/webhook credentials.

Canonical path:

`Notification Instance → Recipient Delivery → Rendered Message → Transport Attempt → Provider Message Reference → Verified Provider Event Ledger → Derived Delivery Outcome`

Provider events are **source facts**, not direct arbitrary state mutations.

---

# 3. Core entities

## 3.1 Recipient Delivery

One logical notification-channel delivery to one resolved recipient.

Candidate fields:
- delivery UUID;
- notification instance/rule revision;
- recipient identity + privacy-safe endpoint reference;
- channel `email`;
- email template/layout/renderer revision references;
- sender profile/transport profile;
- purpose/category;
- desired send time;
- current derived outcome;
- created/completed timestamps;
- suppression/cancellation reason;
- current attempt number;
- audit/correlation reference.

A retry does not create a new business notification by default.

## 3.2 Transport Attempt

Each concrete submission try is separate.

Candidate fields:
- attempt UUID;
- delivery UUID;
- transport/provider profile + version;
- started/finished timestamp;
- local correlation ID;
- provider message/request ID when supplied;
- SMTP/API response class;
- provider acceptance state;
- retryability classification;
- unknown-outcome flag;
- safe error class;
- idempotency key/reference where supported.

This separation prevents a second SMTP/API try from overwriting evidence about the first.

## 3.3 Provider Message Reference

Opaque provider identity needed to correlate later events.

May include:
- provider message ID;
- provider account/subaccount/domain/stream profile;
- provider recipient identifier where event correlation is recipient-specific;
- environment;
- safe metadata/correlation value.

It is not authentication authority and must not expose credentials.

## 3.4 Provider Event Ledger

Verified append-oriented facts from provider webhooks/status APIs.

Candidate fields:
- provider/profile;
- provider event ID/dedupe identity;
- provider message ID;
- WPE delivery/attempt correlation;
- raw provider event type;
- normalized fact type;
- recipient scope;
- occurred-at and received-at;
- signature/auth verification state;
- provider response/SMTP diagnostic safe subset;
- processing state;
- source API/schema version;
- retention class.

Do not model provider feedback as one mutable status column only. A complaint can legitimately occur after a delivery confirmation, and delayed/asynchronous failures can arrive after earlier acceptance facts.

---

# 4. Canonical truth vocabulary

## 4.1 Local/control-plane states

- `queued_local`
- `rendering`
- `render_failed`
- `ready_to_submit`
- `suppressed_preference`
- `suppressed_policy`
- `suppressed_provider_known`
- `cancelled`
- `expired`

## 4.2 Transport-attempt facts

- `attempt_started`
- `local_transport_processed`
- `transport_accepted`
- `transport_rejected_transient`
- `transport_rejected_permanent`
- `provider_accepted_or_queued`
- `provider_rejected`
- `submission_unknown`

`local_transport_processed` is intentionally weaker than provider or destination acceptance.

## 4.3 Provider delivery facts

- `delivery_delayed`
- `receiving_server_delivered`
- `temporary_failure`
- `permanent_failure`
- `provider_dropped`
- `provider_suppressed`

`receiving_server_delivered` means the provider has evidence that the recipient/destination mail server accepted the message. It does **not** mean inbox placement, visibility, reading or human receipt.

## 4.4 Feedback / recipient-action facts

- `complained`
- `provider_unsubscribed`
- `opened_observed`
- `clicked_observed`

Open/click are telemetry observations only. They are not prerequisites for delivery and are not reliable proof of human reading because clients/proxies/security scanners may fetch images or links.

## 4.5 Derived UI outcomes

The UI derives a concise status from the event ledger while preserving detail:

- Queued
- Attempting
- Transport Processed
- Provider Accepted
- Delayed / Retrying
- Delivered to Receiving Server
- Failed — Temporary
- Failed — Permanent
- Suppressed
- Complained
- Cancelled
- Expired
- Unknown Outcome

The UI must be able to show `Delivered to Receiving Server` plus later `Complaint` as distinct facts instead of pretending a single ordinal state can capture both.

---

# 5. Truth-level rules

1. `wp_mail() === true` is at most local transport processing evidence. WordPress itself states this does not mean the user received the message.
2. SMTP `250` after `DATA` means that SMTP receiver accepted responsibility for delivery/relay; if WPE is talking to a local/relay MTA, this is not proof that the final recipient server accepted it.
3. Provider send/API success means only the provider-specific documented acceptance/queue fact.
4. Provider `delivered` may become `receiving_server_delivered` only when provider documentation defines it as destination/recipient mail-server acceptance.
5. No provider event may be normalized to `Inbox Delivered`, `Seen`, or `Read` unless a future technology provides genuinely stronger evidence and a superseding/extended contract explicitly defines it.
6. Open/click tracking never upgrades delivery truth.
7. Unknown/unrecognized provider status remains unknown/source-specific, never optimistic Delivered.

---

# 6. SMTP baseline

SMTP is a transport protocol, not a universal feedback service.

RFC 5321 principles used by WPE:
- 2yz = positive completion of the SMTP command;
- 4yz = transient negative completion; retry may be appropriate;
- 5yz = permanent negative completion for that exact request;
- after `250 OK` to DATA, the accepting receiver-SMTP accepts responsibility for delivering or relaying the message.

Implications:
- authenticated generic SMTP can certify submission behavior without pretending it has provider webhooks;
- a relay-side 250 is `transport_accepted`, not automatically `receiving_server_delivered`;
- DSN/feedback extensions can be profiled separately if actually supported/certified;
- SMTP-only profile cannot advertise complaint/suppression/open/click event support by inference.

Official reference: https://www.rfc-editor.org/rfc/rfc5321

---

# 7. WordPress `wp_mail()` baseline

`wp_mail()` is a compatibility transport/fallback, not delivery confirmation.

WPE normalization:
- successful call → `local_transport_processed`;
- synchronous exception/error → submission failure according to observed error;
- no provider message ID/event stream assumed;
- no `receiving_server_delivered` claim;
- no bounce/complaint/suppression claim unless a separately certified mail transport/provider plugin integration supplies trustworthy events.

WPE must not globally assume which SMTP/plugin has intercepted `wp_mail()` merely because mail succeeded.

Official reference: https://developer.wordpress.org/reference/functions/wp_mail/

---

# 8. Provider API/event profiles

A provider profile declares exactly which source facts it can prove.

## 8.1 Amazon SES reference profile

Official SES event publishing distinguishes:
- SEND — send request successful; SES will attempt delivery;
- REJECT;
- DELIVERY — recipient mail server accepted;
- BOUNCE;
- COMPLAINT;
- DELIVERY_DELAY;
- OPEN/CLICK when tracking is enabled;
- rendering failure and other supported events.

WPE mapping therefore keeps SEND separate from DELIVERY.

References:
- https://docs.aws.amazon.com/ses/latest/APIReference-V2/API_EventDestination.html
- https://docs.aws.amazon.com/ses/latest/dg/monitor-using-event-publishing.html

## 8.2 Twilio SendGrid reference profile

Current Event Webhook semantics distinguish `processed`, `delivered`, `deferred`, `bounce`, `dropped`, spam report/unsubscribe and engagement events. Current docs define Delivered as successful delivery to the receiving server.

Signed Event Webhook verification is available and uses the raw payload with signature/timestamp verification; WPE provider certification must test the selected security profile rather than accepting unsigned events by default when a secure profile is available.

References:
- https://www.twilio.com/docs/sendgrid/for-developers/tracking-events/event
- https://www.twilio.com/docs/sendgrid/for-developers/tracking-events/getting-started-event-webhook-security-features

## 8.3 Mailgun reference profile

Current Mailgun event/webhook semantics distinguish:
- accepted — queued by Mailgun;
- delivered — accepted by recipient email server;
- temporary failure;
- permanent failure;
- complained;
- unsubscribe/open/click when corresponding tracking exists.

Webhook authenticity uses provider signing data; current Mailgun docs describe HMAC verification over timestamp + token and recommend replay/timestamp defenses.

References:
- https://documentation.mailgun.com/docs/mailgun/user-manual/webhooks/webhooks
- https://documentation.mailgun.com/docs/mailgun/user-manual/webhooks/securing-webhooks

## 8.4 Postmark reference profile

Current Postmark Delivery webhook explicitly means destination/receiving server returned an OK response. Postmark also explicitly warns that this does not prove inbox placement.

Provider profile can separately certify bounce and spam-complaint webhooks.

References:
- https://postmarkapp.com/developer/webhooks/delivery-webhook
- https://postmarkapp.com/developer/webhooks/spam-complaint-webhook

---

# 9. Provider events use Event Inbox

External email events compose ADR-0040.

Ingress sequence:
1. receive raw request within body/size limits;
2. identify fixed provider/profile;
3. verify signature/authentication before business dispatch;
4. enforce timestamp/replay profile where provider supports it;
5. persist normalized Event Inbox record/dedupe identity;
6. acknowledge provider within bounded time;
7. process asynchronously when appropriate;
8. correlate provider message/recipient with WPE Delivery/Attempt;
9. append normalized source fact;
10. derive current UI outcome and emit internal WPE event.

Unknown event types are stored/ignored safely according to policy rather than breaking the entire webhook endpoint.

---

# 10. Duplicate and out-of-order events

Provider delivery feedback is not assumed exactly-once or strictly ordered.

Requirements:
- provider event ID/dedupe key when available;
- deterministic fallback dedupe profile where provider lacks event ID;
- received timestamp and provider occurred timestamp retained separately;
- duplicate event processing is idempotent;
- event ledger accepts late facts;
- derived outcome recomputed by explicit provider-neutral rules;
- no destructive preference/account action from an unverified event.

Do not make the lifecycle a simple integer where a later complaint/bounce cannot be represented because `delivered` was considered terminal.

---

# 11. Unknown submission outcome

Network timeout after request transmission is dangerous: the provider may have accepted the message even though WPE did not receive the response.

State: `submission_unknown`.

Policy:
1. if provider supports idempotency/request keys, use them according to certified semantics;
2. if provider exposes message/status lookup, reconcile before retry where possible;
3. wait for correlated provider event when appropriate;
4. if neither mechanism exists, use bounded retry policy that clearly acknowledges duplicate risk or require operator decision for high-impact sends;
5. never silently mark unknown as failed and immediately generate unlimited duplicate sends.

Retry creates a new Transport Attempt under the same logical Recipient Delivery.

---

# 12. Retry classification

Retry policy derives from the observed layer.

Examples:
- local transient connection error → retryable according to adapter;
- SMTP 4yz → transient/retry candidate;
- SMTP 5yz → do not repeat exact request automatically without provider/profile rule;
- HTTP 429 → honor `Retry-After` where present;
- provider 5xx → bounded backoff;
- invalid credentials/configuration → no tight-loop retry;
- hard/permanent recipient failure → no automatic repeated sending to same address;
- provider suppression → do not retry until source condition/policy changes.

Job Service owns scheduled retries/backoff; PHP requests never sleep waiting for delivery.

---

# 13. Bounce semantics

WPE distinguishes:
- provider temporary/deferred failure;
- provider permanent/hard failure;
- asynchronous delayed bounce where provider supports it;
- provider-specific raw classification.

A bounce is a delivery fact, not automatically a global user-account or consent decision.

Possible downstream policy:
- mark endpoint unhealthy;
- increment bounded deliverability evidence;
- suppress future optional email after certified permanent failure;
- prompt user/admin to update address;
- emit Workflow event.

Do not automatically disable in-app/SMS/other channels because email bounced.

---

# 14. Complaint semantics

A verified spam complaint is materially different from a bounce.

WPE can:
- record complaint fact;
- update email endpoint/provider suppression policy according to site/provider rules;
- stop optional/promotional email immediately where configured/required;
- surface high-severity sender health issue;
- preserve minimum audit evidence.

Complaint does not erase the historical fact that an earlier receiving-server delivery confirmation occurred.

---

# 15. Suppression semantics

Suppression sources are separated:
- WPE user preference;
- WPE frequency/dedupe/quiet-hour policy;
- provider suppression list;
- invalid destination;
- compliance/abuse safety policy;
- provider account/domain restriction.

`Suppressed` means no send occurred for that attempt/path; it is not a bounce.

Provider suppression state should be reconciled only where API/profile evidence exists.

---

# 16. Unsubscribe vs provider suppression

Optional-message user preference is WPE's product policy domain.

Provider-level unsubscribe/suppression can be a source fact but must not silently mutate unrelated notification categories without mapping policy.

Required security/account messages and optional marketing messages remain semantically distinct. WPE does not claim jurisdiction-specific legal compliance automatically.

---

# 17. Open/click tracking

Tracking remains off by default at platform level unless deliberately enabled for an appropriate category/provider.

Normalized events:
- `opened_observed`
- `clicked_observed`

Never expose UI labels such as `Read`, `Human Viewed`, `Inbox Confirmed` from these signals.

Tracking data retention is separately configurable and privacy-minimized.

---

# 18. Sender/domain authentication diagnostics

Sender Profile may surface, only when evidence is available:
- verified provider sender/domain identity;
- DKIM setup/verification state;
- SPF-related provider diagnostics;
- DMARC-related diagnostics;
- return-path/bounce-domain configuration;
- provider account restrictions.

Configured From address alone does not prove SPF/DKIM/DMARC health.

Email certification does not promise deliverability/inbox placement solely because authentication records pass.

---

# 19. Credential/security boundaries

- SMTP usernames/passwords/API keys/OAuth refresh credentials live in Secrets Vault.
- browser/editor receives only masked connection state.
- API calls use least privilege/scopes where provider supports them.
- TLS certificate verification is mandatory in ordinary production mode.
- arbitrary custom SMTP/API endpoints require explicit network/Safe HTTP policy appropriate to intended admin-configured destinations.
- webhook secrets/verification keys are separate from send credentials where provider supports separation.
- raw webhook body needed for signature verification must be preserved before JSON transformations.
- provider responses/errors are redacted before logs/UI.

---

# 20. Correlation and privacy

WPE creates a high-entropy internal delivery correlation ID.

Where provider permits safe metadata/header correlation, send only opaque identifiers that disclose no private entity/user data.

Do not put:
- user IDs with predictable semantics;
- membership/payment status;
- secrets/tokens;
- private object IDs;
- raw email body data
into provider tags/headers merely for convenience.

Provider message ID mapping may be retained for the configured delivery-log retention period.

---

# 21. Delivery/event retention

Keep normalized evidence needed to explain operational state without retaining all provider payloads forever.

Candidate categories:
- Delivery/Attempt core state: operational retention configurable;
- provider message/event IDs: same or shorter lifecycle where feasible;
- bounce/complaint/suppression evidence: category-level retention because it may be required to avoid repeated unwanted sends;
- open/click telemetry: shorter/off by default;
- raw webhook payload: minimal/debug-only bounded retention, not default permanent history;
- full rendered message body: not retained indefinitely by default.

Privacy/export/delete behavior must distinguish WPE-local data from provider-owned history.

---

# 22. Provider certification profile

Certification identity is:

`transport adapter + provider/product + API/SMTP profile/version + auth mode + event profile + WPE version range`

A generic SMTP adapter passing against one server does not certify every SMTP service.

A provider's API send support does not imply its event webhook support is certified.

Capabilities are independently declared:
- submit;
- provider message ID;
- idempotency;
- rate-limit semantics;
- delivery confirmation;
- delay/temp failure;
- hard/permanent failure;
- complaint;
- suppression;
- unsubscribe;
- open/click telemetry;
- sender/domain diagnostics;
- event signature verification;
- status/reconciliation API.

---

# 23. Email Transport certification levels

## ET0 — Configured / Connectable

Credentials/settings validate or transport is discoverable.

No send-support claim.

## ET1 — Submission Certified

Can submit a deterministic Rendered Message and correctly classify synchronous acceptance/rejection; captures provider/transport correlation where available.

`wp_mail()` compatibility can reach an ET1-like local submission profile only for what it actually proves; it cannot claim remote delivery.

## ET2 — Resilient Submission Certified

ET1 plus:
- bounded retry/backoff;
- 4xx/429/5xx/provider retry classification;
- rate-limit handling;
- unknown-outcome behavior;
- duplicate-send/idempotency strategy;
- attachment/size/error fixtures;
- credential rotation/recovery.

## ET3 — Delivery Truth Certified

ET2 plus verified provider feedback for the advertised delivery facts:
- provider accepted/queued where separate;
- delayed/temp failure;
- receiving-server delivery confirmation;
- permanent failure/bounce;
- event correlation, duplicate and out-of-order handling.

A provider may advertise **Delivery Tracking** only for capabilities proven at this level/profile.

## ET4 — Feedback / Suppression / Reconciliation Certified

ET3 plus all advertised richer feedback capabilities:
- complaint;
- suppression;
- unsubscribe where integrated;
- provider status/list reconciliation where available;
- webhook signature/replay/key-rotation behavior;
- provider outage/backfill behavior;
- privacy/minimized event retention.

Open/click are optional capability flags and do not raise delivery truth.

## ET5 — Production Email Profile Certified

ET4 plus declared production profile evidence:
- sender/domain setup diagnostics;
- provider/API version compatibility;
- high-volume batching/rate/backpressure behavior;
- migration/restore/clone behavior;
- multisite isolation where supported;
- long-running operational recovery;
- security/privacy/redaction;
- monitoring/health and provider credential lifecycle.

`Production Certified` is reserved for ET5.

---

# 24. Public support-label policy

Allowed examples only after evidence:
- `SMTP submission supported` — ET1/ET2 scope stated.
- `Amazon SES delivery events supported` — ET3 capability profile passed.
- `Provider bounce/complaint reconciliation supported` — ET4 relevant capabilities passed.
- `Production Certified` — ET5.

Forbidden shortcuts:
- `Delivered` because `wp_mail()` returned true;
- `Delivered` because provider API returned HTTP 2xx;
- `Inbox delivered` from receiving-MTA acceptance;
- `Read` from open tracking;
- `Fully supported` because connection test succeeded.

---

# 25. Initial provider proof order after future consent

Recommended evidence order, not implementation authorization:
1. WordPress `wp_mail()` compatibility baseline;
2. generic authenticated SMTP reference profile;
3. Amazon SES API + event publishing;
4. Twilio SendGrid API/SMTP + signed Event Webhook;
5. Mailgun API/SMTP + signed webhooks;
6. Postmark API/SMTP + delivery/bounce/complaint webhooks;
7. additional providers (for example Resend/Brevo/Mailjet/other demand-driven adapters) only after current API/security/maintenance evidence.

The architecture should reuse provider-neutral Delivery/Attempt/Event contracts rather than adding a separate status machine for every vendor.

---

# 26. Future certification evidence — NOT AUTHORIZED

For each declared provider/profile:
- missing/bad/revoked credentials;
- sender/domain not verified;
- local rendering failure never calls provider;
- accepted send with provider message ID;
- provider synchronous rejection;
- rate limit/429 and `Retry-After`;
- provider 5xx/outage;
- timeout before request write;
- timeout/connection loss after possible request acceptance;
- idempotency/duplicate-risk handling;
- temporary SMTP/provider deferral;
- eventual delivery after deferral;
- hard/permanent bounce;
- delayed/asynchronous bounce;
- provider drop/suppression;
- spam complaint;
- duplicate webhook;
- out-of-order webhook;
- invalid/tampered webhook signature;
- replayed webhook;
- webhook key/signing-profile rotation;
- unknown event type;
- message/recipient correlation;
- multi-recipient behavior where provider event IDs differ;
- attachment size/type limits;
- Unicode/localization headers;
- unsubscribe/preferences mapping when advertised;
- open/click privacy behavior when enabled;
- provider status reconciliation/backfill where available;
- Job Service backlog/rate backpressure;
- restore/clone/staging duplicate-send protection;
- multisite isolation;
- log/redaction/retention/privacy fixtures.

No SMTP connection, provider API call, webhook endpoint test or real email send is authorized before explicit owner consent under ADR-0014.

## Paper conclusion

Accept as architecture principle:

**Email delivery is evidence-based and layered. Transport/API acceptance, destination-server acceptance, bounce/complaint/suppression feedback and engagement telemetry are different facts. WPE stores an append-oriented verified provider event ledger and derives truthful UI outcomes; it never upgrades weak evidence into inbox/read claims.**
