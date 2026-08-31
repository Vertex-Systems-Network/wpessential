# WPEssential — Notification Persistence & Delivery Model

Status: **Phase 0 paper architecture / no delivery runtime authorized**  
Related: Notification System Exhaustive Spec, Workflow Runtime, Event/Ability catalog, Job Service.

## Purpose

Separate notification intent, recipient resolution, in-app persistence and channel-delivery attempts so that “notification created”, “provider accepted”, and “delivered” are not conflated.

## Domain split

### Notification Rule
Definition Repository configuration:
- trigger event;
- conditions;
- recipient resolver;
- channel set;
- content/template;
- priority/classification;
- digest/quiet-hours/dedupe policy.

### Notification Instance
One logical notification occurrence before or after recipient fan-out.

### Recipient Notification
Per-user/per-recipient durable in-app state where applicable.

### Delivery Attempt
Per recipient + channel attempt/status.

Email module owns email-safe rendering/provider details; Notifications owns routing intent/status aggregation.

---

# Candidate runtime entities

## Notification occurrence
Fields:
- UUID;
- Rule UUID + published revision;
- source event ID/type;
- correlation/causation IDs;
- created/available/expires timestamps;
- priority/classification;
- title/body/template snapshot/reference;
- target action/link descriptor;
- dedupe key;
- status;
- safe source context refs.

## Recipient record
Fields:
- notification UUID;
- user/recipient canonical identity;
- recipient-resolution source;
- in-app visibility state;
- created/available/expires;
- read_at;
- dismissed_at;
- archived_at;
- preference/quiet-hours outcome snapshot;
- delivery summary state.

## Channel delivery
Fields:
- recipient record;
- channel ID;
- provider/connection reference;
- state;
- attempt count;
- next attempt;
- last attempted;
- provider message reference safe subset;
- error category/code;
- delivered/accepted timestamp only when provider semantics actually support claim.

No channel secret stored here.

---

# Status semantics

Notification-level:
- planned
- queued
- partially_dispatched
- dispatched
- completed
- suppressed
- expired
- failed_partial

Channel-level:
- queued
- suppressed
- sending
- accepted_by_provider
- delivered_confirmed
- failed_retryable
- failed_terminal
- cancelled
- expired

For transports like plain `wp_mail()` without delivery receipt, status stops at accepted/handoff; never label “Delivered”.

---

# Recipient resolution

Two modes:

## Resolve at trigger
Snapshot recipients immediately.
Useful when business requirement is “who matched at event time”.

## Resolve at delivery
Re-evaluate recipient query/policy when dispatch occurs.
Useful when access/preferences can change during delay.

Rule must choose explicitly where ambiguity matters.

Default candidate:
- direct event actor/subject: snapshot;
- broad role/query/membership recipients: resolve at dispatch for delayed notifications unless Rule selects snapshot.

Large recipients use batched Job Service fan-out.

---

# Authorization/access recheck

Target notification may link to protected resource.

Rules:
- generating notification does not grant access;
- opening/acting on target reauthorizes current resource policy;
- recipient revoked from Membership/team before delivery can be suppressed when Rule is access-dependent;
- notification body should avoid leaking sensitive protected content if delayed delivery could outlive access.

---

# Preferences/classification

Preference engine evaluates:
- mandatory security/service classification;
- marketing/promotional opt-in/out;
- channel preferences;
- quiet hours;
- digest preference;
- account/site policy.

A Rule cannot label arbitrary marketing as transactional merely to bypass preference controls without explicit administrative responsibility.

Preference decision snapshot stored as reason code, not entire user settings copy.

---

# Quiet hours

At dispatch:
- evaluate recipient timezone;
- if deferrable, schedule next allowed timestamp;
- if critical bypass permitted, deliver and record bypass reason;
- expired notification can be dropped instead of sending stale content after quiet hours.

No busy polling.

---

# Digest model

Digest item points to Notification/Recipient record rather than duplicating full source payload.

Digest job:
1. select eligible unread/undelivered items;
2. group by user/channel/window;
3. enforce max items;
4. render email/in-app digest;
5. mark items associated with digest attempt;
6. keep individual read/access semantics independent.

Urgent/security events can bypass only by Rule classification.

---

# Dedupe

Candidate key dimensions:
- Rule revision;
- recipient;
- channel or notification-level scope;
- explicit dedupe key/event ID;
- window.

Default event-derived Rule can use unique source event ID.

Dedupe does not erase history silently; suppressed duplicate records may be summarized/count-only depending volume.

---

# Retry/idempotency

Channel adapter declares:
- idempotency support;
- retryable errors;
- max attempts/backoff;
- provider request key.

Unknown outcome after network timeout must not blindly resend when provider may have accepted the message. Adapter can enter reconciliation/unknown state if provider supports lookup.

---

# In-app storage

In-app records are first-class and queryable by:
- user;
- read/unread;
- available/expires;
- priority;
- classification/category;
- target module.

Index strategy must support unread count without scanning body content.

Body can be:
- sanitized text/structured renderer snapshot;
- template reference + safe materialized variables if historical rendering required.

Do not store arbitrary remote HTML/scripts.

---

# Read/dismiss semantics

`read` means user opened/acknowledged according to UX; it does not imply external channel delivery.

`dismissed` hides/archives according to product behavior, not deletes security/audit record automatically.

Bulk mark-read is bounded/batched for large inboxes.

---

# Expiry

Expired Notification:
- no new delivery after expiry;
- existing in-app record may be hidden/archived;
- retention cleanup later removes according to policy;
- expiry does not rewrite whether prior delivery succeeded.

---

# Retention

Separate categories:
- notification occurrences;
- recipient in-app records;
- delivery attempt logs;
- provider references/errors.

Candidate defaults to decide later by product policy; high-volume delivery attempts should not retain full payloads indefinitely.

Secrets/message-provider raw responses excluded or minimized.

---

# Privacy

Potential P2 data:
- recipient identity;
- read state;
- target resource metadata;
- delivery address/provider ref.

Export/erase behavior:
- user notification history export where appropriate;
- delivery logs anonymized/deleted according to policy;
- security notifications may retain safe audit trace;
- other recipients' data not exposed.

---

# Performance model

Avoid one synchronous insert/API call per recipient during source request.

Flow candidate:
1. event/Rule resolves logical occurrence;
2. store occurrence;
3. enqueue fan-out batches;
4. resolve recipients;
5. bulk insert recipient rows;
6. enqueue channel batches;
7. adapters dispatch with rate limits.

Admin progress uses aggregate counts, not loading every recipient row.

---

# Failure handling

## Recipient query fails
Occurrence remains failed/degraded with retry; no partial duplicate fan-out without cursor/idempotency.

## One channel fails
Other channels can succeed; final state partial.

## Provider unavailable
Backoff/rate-limit; in-app can still exist.

## Rule deleted after occurrence
Pinned Rule revision/snapshot permits in-flight occurrence to finish or be explicitly cancelled according to policy.

## User deleted
Pending deliveries suppressed/retention policy applied; security audit safe trace preserved if required.

---

# Paper recommendation

Use separate tables/logical stores for:
- notification occurrence;
- recipient notification/read state;
- channel delivery attempts.

Do not encode all recipients/delivery statuses into one giant serialized record.

## Future benchmark — NOT AUTHORIZED

After explicit consent test:
- 1, 100, 10k, 100k recipient fan-out;
- role/query/membership recipient churn;
- quiet-hours scheduling;
- digest generation;
- duplicate source events;
- provider timeout unknown outcome;
- retry/idempotency;
- unread count/query latency;
- read-all bulk action;
- privacy cleanup;
- Job worker interruption.

No runtime table/delivery adapter/job has been implemented or run.