# ADR-0058 — Email Delivery Truth & Provider Certification

Status: **Accepted communication architecture / executable transport/provider evidence pending**  
Date: 2026-08-27

## Context

Email systems expose multiple non-equivalent success/failure signals. WordPress `wp_mail()` can process a request successfully without proving recipient receipt. SMTP acceptance transfers/relays responsibility at the accepting SMTP hop but does not inherently prove final inbox placement. Provider APIs commonly distinguish queued/accepted, destination-server delivery, delays, bounces, complaints and suppression. Engagement tracking is weaker still and may be affected by privacy proxies/scanners.

A generic `sent`/`delivered` boolean would therefore overstate evidence, trigger unsafe retries, hide bounces/complaints and make provider behavior impossible to certify honestly.

## Decision

WPEssential accepts a layered evidence model:

`Notification Instance → Recipient Delivery → Rendered Message → Transport Attempt → Provider Message Reference → verified Provider Event Ledger → Derived Delivery Outcome`

### Truth boundaries

- `wp_mail()` success is local transport processing evidence, not Delivery.
- SMTP/provider submission acceptance is not recipient-server delivery unless the certified profile specifically proves that hop.
- `Delivered` in WPE UI is scoped as **Delivered to Receiving Server** only when a verified provider event documents recipient/destination mail-server acceptance.
- Receiving-server acceptance is not inbox placement, user visibility or human reading.
- Open/click events are engagement observations only and never upgrade delivery truth.
- Unknown provider states remain unknown; no optimistic mapping.

### Event-ledger model

Provider feedback is stored as verified source facts in an append-oriented event ledger using ADR-0040 Event Inbox semantics. Duplicate and out-of-order events are expected and must be idempotently processed.

Delivery state is not represented as one irreversible ordinal. A complaint, suppression or asynchronous failure can be recorded after earlier acceptance/delivery facts without erasing history.

### Retry/unknown outcome

A network timeout after submission may leave an ambiguous outcome. WPE records `submission_unknown` and prefers provider idempotency, status reconciliation or correlated events before retry. A retry is a new Transport Attempt under the same logical Recipient Delivery.

### Provider certification

Email transport/provider profiles use:

- **ET0 — Configured / Connectable**
- **ET1 — Submission Certified**
- **ET2 — Resilient Submission Certified**
- **ET3 — Delivery Truth Certified**
- **ET4 — Feedback / Suppression / Reconciliation Certified**
- **ET5 — Production Email Profile Certified**

A normal public claim of provider **Delivery Tracking** requires the relevant ET3 event profile. Bounce/complaint/suppression/reconciliation claims require the corresponding ET4 capability. `Production Certified` requires ET5.

Generic SMTP, WordPress `wp_mail()`, API send and webhook/event capabilities are certified separately; a connection or successful send test does not imply full lifecycle support.

### Initial evidence priority

1. WordPress `wp_mail()` compatibility baseline;
2. generic authenticated SMTP;
3. Amazon SES;
4. Twilio SendGrid;
5. Mailgun;
6. Postmark;
7. additional demand-driven providers after current API/security evidence.

## Security/privacy

- SMTP/API/OAuth/webhook credentials live in Secrets Vault.
- Provider webhook authenticity/replay protections are verified before source-fact dispatch.
- Raw-body signature semantics are preserved where required.
- Delivery/provider IDs are correlation data, not authentication secrets.
- Full sensitive rendered bodies/raw webhook payloads are not retained indefinitely by default.
- Open/click tracking remains off by default unless deliberately enabled for an appropriate category/provider.

## Consequences

Positive:
- UI and logs no longer overclaim inbox delivery;
- `wp_mail()`/SMTP/provider API success is represented truthfully;
- duplicate/out-of-order provider events are recoverable;
- retry ambiguity is explicit, reducing duplicate sends;
- providers can be certified by actual capabilities rather than marketing names;
- complaints/suppression remain visible without corrupting historical delivery evidence.

Cost:
- Recipient Delivery, Transport Attempt and provider Event Ledger require separate persistence/indexes;
- provider-specific correlation/signature/reconciliation adapters are required;
- production support claims require substantial event/failure fixtures.

## Evidence still required

After explicit owner development consent:
- physical Delivery/Attempt/Event indexes and retention;
- wp_mail/PHPMailer behavior matrix;
- SMTP relay/direct-server response fixtures;
- provider API idempotency/rate/unknown-outcome tests;
- SES/SendGrid/Mailgun/Postmark event mappings;
- webhook signature/replay/rotation tests;
- duplicate/out-of-order/asynchronous failure fixtures;
- bounce/complaint/suppression policy behavior;
- sender-domain diagnostics;
- high-volume Job Service/backpressure tests;
- restore/clone/staging duplicate-send protection;
- privacy/redaction/multisite fixtures.

No SMTP connection, provider API call, webhook execution or email send has been implemented/executed.
