# WPEssential — Email Transport Certification Evidence Protocol

Status: **Phase 0 protocol only / DO NOT EXECUTE without explicit owner consent**  
Governs: ADR-0026, ADR-0029, ADR-0040, ADR-0058.

## Purpose

Define repeatable evidence required before an email transport/provider profile may claim ET0–ET5 capabilities.

This file authorizes **nothing**. SMTP connections, provider credentials, DNS changes, webhook endpoints, sends and fixtures remain blocked by ADR-0014.

## Certification identity

Every report pins:
- WPE version/commit;
- transport adapter version;
- provider/product;
- provider API/event schema version or dated documentation profile;
- SMTP mode/relay when relevant;
- authentication mode;
- event/webhook security mode;
- PHP/WordPress environment;
- Job Service adapter;
- test domain/environment;
- certification date.

A result is not automatically portable to a materially different provider/product/profile.

---

# ET0 — Configured / Connectable

Future evidence:
- credentials/configuration accepted;
- bad credentials rejected safely;
- least privilege/scopes documented;
- secret values never returned to browser/log;
- TLS verification behavior;
- sender/domain configuration prerequisites surfaced;
- connection health can distinguish unknown/unconfigured/invalid.

Pass does not authorize a send-support claim.

---

# ET1 — Submission Certified

Fixtures:
- deterministic Rendered Message accepted by adapter;
- invalid From/recipient/header rejected safely;
- synchronous provider/API rejection normalized;
- transport/provider message ID captured where supplied;
- recipient-specific provider ID handling documented;
- attachment and message-size boundary behavior;
- Unicode subject/display-name/header behavior;
- test-send isolation from production Notification workflow;
- `wp_mail()` true maps only to local transport processed;
- generic SMTP acceptance maps only to the certified accepting hop.

Pass criteria:
- no UI/log claim stronger than observed evidence;
- one Transport Attempt is persisted per actual submission try.

---

# ET2 — Resilient Submission Certified

Fault fixtures:
- DNS/connect failure before request transmission;
- TLS failure;
- timeout before request body/write;
- connection loss after possible provider acceptance;
- SMTP 4yz transient response;
- SMTP 5yz permanent response;
- provider HTTP 429 + Retry-After;
- provider 5xx;
- provider outage;
- revoked/rotated credential;
- queue backlog;
- Job worker crash during attempt bookkeeping.

Unknown-outcome fixture is mandatory.

Verify:
- `submission_unknown` is preserved where acceptance cannot be known;
- idempotency key used only where provider contract supports it;
- status/event reconciliation attempted where certified;
- blind immediate retry does not create uncontrolled duplicates;
- retry produces a new Transport Attempt under same logical Recipient Delivery;
- bounded exponential/backoff policy;
- permanent errors do not tight-loop.

---

# ET3 — Delivery Truth Certified

Event fixtures for every advertised event capability:
- provider accepted/queued;
- receiving-server delivery confirmation;
- delivery delayed/deferred;
- temporary failure;
- permanent/hard failure/bounce;
- provider dropped/rejected when distinct;
- multi-recipient event correlation;
- duplicate event;
- out-of-order event;
- delayed/asynchronous failure after earlier acceptance;
- unknown provider event type;
- event for unknown provider message ID;
- late event after local log archival boundary.

Security fixtures:
- valid webhook auth/signature;
- tampered body/signature;
- wrong provider/profile;
- stale/replayed event where provider profile supports timestamp/replay checks;
- key/signing profile rotation where applicable.

Pass criteria:
- Provider Event Ledger preserves source facts;
- derived outcome is deterministic and truthful;
- `Delivered to Receiving Server` occurs only from certified destination-server acceptance evidence;
- no inbox/read claim.

---

# ET4 — Feedback / Suppression / Reconciliation Certified

Advertised capability fixtures:
- spam complaint;
- provider suppression before send;
- provider unsubscribe event;
- suppression removal/reactivation policy if provider permits;
- full/partial event outage followed by status/reconciliation/backfill where provider offers it;
- provider event retention expiry;
- webhook disabled/re-enabled;
- bounce then manual address correction;
- complaint after delivery event;
- WPE optional-category preference vs provider-global suppression interaction;
- provider raw classification changes/unknown values.

Tracking, only if advertised:
- open tracking enabled/disabled;
- click tracking enabled/disabled;
- privacy-proxy/scanner-safe UI language;
- retention off/short/default behavior.

Pass criteria:
- complaint/suppression do not erase historical delivery evidence;
- provider suppression does not silently mutate unrelated WPE channels;
- open/click never maps to Read/Human Viewed;
- external provider history vs local erasure boundary is truthful.

---

# ET5 — Production Email Profile Certified

Long-running/operational fixtures:
- declared sender/domain authentication setup and degraded diagnostics;
- production rate limits and large batch behavior;
- fair queue/backpressure with other Job classes;
- credential rotation without message-loss/double-send state corruption;
- provider event endpoint outage and recovery;
- provider API version/schema drift handling;
- storage/event-log cleanup under retention policy;
- WordPress/site restore and staging clone duplicate-send prevention;
- multisite profile isolation where supported;
- Pro downgrade/expiry safe runtime behavior;
- support diagnostics redact secrets/body/recipient PII;
- monitoring detects stale event ingress/provider health without false Delivered state.

Certification report must enumerate unsupported capabilities, not only passed ones.

---

# Provider-specific minimum suites

## WordPress `wp_mail()` baseline
- true/false/error behavior;
- PHPMailer hook interactions where supported;
- no remote delivery claim;
- coexistence with popular SMTP replacement plugins only after separate compatibility profile.

## Generic SMTP
- AUTH modes selected by implementation;
- TLS/certificate behavior;
- 2yz/4yz/5yz classification;
- local relay vs destination-server distinction;
- connection loss after DATA ambiguity;
- DSN capability only if explicitly implemented/certified.

## Amazon SES
- Send/Reject/Delivery/Bounce/Complaint/DeliveryDelay mappings;
- message ID correlation;
- selected event destination ingress security/reliability;
- suppression/account restrictions;
- rate/throttle behavior.

## Twilio SendGrid
- processed/delivered/deferred/bounce/dropped/spam report mappings;
- signed Event Webhook raw-body verification or separately approved OAuth webhook profile;
- event/message ID correlation;
- delayed/asynchronous bounce behavior;
- rate limits.

## Mailgun
- accepted/delivered/temporary_fail/permanent_fail/complained mappings;
- HMAC timestamp+token signature verification;
- replay token/timestamp policy;
- domain/account event profile differences.

## Postmark
- delivery confirmation maps to receiving-server acceptance only;
- bounce/spam complaint profile;
- message stream correlation;
- webhook auth/profile limitations documented.

---

# Evidence artifact

Each future certification produces a report containing:
- profile identity/version;
- test matrix and fixture IDs;
- raw provider facts retained only in protected test artifacts;
- normalized WPE facts;
- expected vs observed outcomes;
- unsupported capabilities;
- privacy/security observations;
- known provider quirks;
- pass/fail per ET level;
- certification expiry/retest triggers.

Retest triggers include provider API/event schema change, adapter dependency change, material WordPress/PHPMailer change, webhook security change, or significant WPE Delivery state-machine change.

## Gate

No item in this protocol may be executed until the owner gives explicit development/executable-spike consent under ADR-0014.
