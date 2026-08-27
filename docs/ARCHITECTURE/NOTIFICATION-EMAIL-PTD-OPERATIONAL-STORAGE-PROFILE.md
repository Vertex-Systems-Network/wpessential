# WPEssential — Notification & Email PT-D Operational Storage Profile

Status: **Phase 0 paper architecture / benchmark profile only / no DDL or delivery runtime authorized**  
Related: ADR-0026, ADR-0029, ADR-0058, ADR-0059, ADR-0063, ADR-0067, ADR-0069, ADR-0071, Notification Persistence & Delivery Model, Email Rendering & Delivery Architecture.

## Purpose

Translate accepted Notification/Email runtime semantics into a physical benchmark profile without collapsing distinct facts such as notification creation, recipient state, transport acceptance and confirmed delivery.

## Benchmark topology

### NE1 — PT-D shared scoped operational stores — first benchmark baseline

One WPE shared runtime table family with explicit network/site scope on every site-owned row.

Candidate logical stores:
1. Notification Occurrence;
2. Recipient Notification;
3. Channel Delivery;
4. Email Transport Attempt;
5. Normalized Email Delivery Evidence;
6. optional recipient/provider suppression materialization where a certified provider contract requires it.

### NE2 — PT-E per-site operational stores — mandatory comparison

Same logical contract using per-site tables.

NE2 exists to measure physical isolation/noisy-neighbor advantages against table proliferation, migrations, network operations, diagnostics and retention complexity. Benchmark order does not choose the final topology.

---

## Separation of truth

### Notification Occurrence
Represents one logical event/rule occurrence.

It does **not** mean any recipient was reached.

### Recipient Notification
Represents one recipient's durable notification/in-app state.

`read_at` means product UX read/acknowledge semantics, not email delivery.

### Channel Delivery
Represents routing state for one recipient + channel.

It aggregates/schedules channel work but is not necessarily one provider HTTP/SMTP attempt.

### Email Transport Attempt
Represents one concrete send/handoff attempt to a transport/provider.

Possible truth stops at provider/transport acceptance when no stronger evidence exists.

### Normalized Delivery Evidence
Represents verified provider facts such as delivered, bounced, complained, deferred or suppressed when the certified provider/event profile supports those facts.

This is derived from verified events and never inferred from clicks, opens, queue success or `wp_mail()` boolean success.

---

## Scope identity

Every NE1 row carries explicit:
- `network_id`;
- `site_id`;
- stable occurrence/recipient/delivery/attempt/event identity as applicable.

Provider message IDs, recipient addresses, WordPress user IDs and Definition UUIDs are never treated as site scope by themselves.

All lookups by external/provider reference must include connection/provider profile + scope before returning site-owned state.

Cross-site dashboards/diagnostics require explicit network authority and bounded aggregation; ordinary Site Admin queries remain target-site constrained.

---

## Notification Occurrence invariants

Candidate fields/invariants:
- stable UUID/internal ID;
- scope;
- Rule UUID + pinned published revision;
- source event identity/type;
- correlation/causation references;
- priority/classification;
- available/expires timestamps;
- dedupe identity/window reference;
- lifecycle/aggregate status;
- safe target/context references;
- content/template snapshot/reference policy, not arbitrary executable HTML.

Candidate indexes to benchmark:
- scope + source event/rule dedupe identity;
- scope + status + available/expiry time;
- scope + Rule + created time;
- scope + correlation ID for diagnostics.

Dedupe correctness is required under concurrent/retried source events.

---

## Recipient Notification invariants

Candidate fields:
- scope + occurrence;
- canonical recipient identity/user principal where applicable;
- recipient-resolution source/snapshot policy;
- in-app visibility state;
- available/expires;
- read/dismiss/archive timestamps;
- preference/quiet-hours result code;
- delivery summary generation/state.

Hot indexes:
- scope + user/principal + unread/available state + created/order cursor;
- scope + occurrence for fan-out diagnostics;
- scope + expiry/retention cleanup;
- scope + user + archived/read state for inbox views.

Unread count must not scan message body or all historical delivery attempts.

Knowing Recipient Notification UUID does not bypass user/site authorization.

---

## Channel Delivery invariants

Candidate fields:
- scope;
- recipient notification identity;
- channel key;
- provider/connection reference;
- routing state;
- attempt count;
- next-attempt/last-attempt timestamps;
- safe provider message reference summary;
- error category/code;
- accepted/delivered timestamps only when evidence supports the term;
- idempotency/correlation generation.

Hot indexes:
- scope + state + next attempt;
- scope + recipient + channel;
- scope + provider/connection + state;
- scope + occurrence for aggregate progress.

A single channel failure does not rewrite other channel outcomes.

---

## Email Transport Attempt invariants

One row/logical record per concrete transport submission attempt.

Candidate fields:
- scope;
- Channel Delivery identity;
- attempt ordinal/UUID;
- provider profile + connection;
- Template revision / renderer profile / locale references;
- rendered-message fingerprint and safe size/count metadata;
- provider request/idempotency reference when supported;
- started/finished timestamps;
- terminal/unknown/retryable state;
- safe error category;
- provider message reference where returned.

Do not persist by default:
- full sensitive rendered body indefinitely;
- SMTP/API secret;
- raw provider credential/header dump;
- reusable private attachment URL;
- entire provider response object.

Hot indexes:
- scope + Channel Delivery + attempt ordinal;
- scope + state + finished/created time;
- scope + provider/connection + provider message reference;
- scope + correlation/idempotency identity.

---

## Unknown-outcome rule

Network timeout after submission can mean provider accepted the message even though WPE did not receive the response.

Therefore:
- `unknown_outcome` is a real state;
- blindly retrying is prohibited when duplicate delivery is plausible;
- provider adapter declares whether lookup/reconciliation is possible;
- an idempotency-capable provider uses the same stable send key on retry according to its certified contract;
- reconciliation can resolve unknown to accepted/rejected/failed when evidence exists.

Unknown never becomes Delivered by assumption.

---

## Normalized Email Delivery Evidence

Provider webhook/event processing produces normalized evidence only after the owning Webhook Gateway/Event Inbox has verified source authenticity/replay/idempotency according to provider profile.

Candidate fields:
- scope;
- provider profile + connection;
- provider event identity;
- provider message reference;
- related Transport Attempt/Channel Delivery when resolved;
- normalized fact type;
- provider event timestamp + observed timestamp;
- schema/profile version;
- safe reason/category metadata;
- resolution state for unmatched/ambiguous events.

Hot indexes:
- scope + provider/connection + provider event ID for dedupe;
- scope + provider message reference + time;
- scope + related attempt/delivery;
- scope + fact type + observed time for retention/diagnostics;
- unmatched state + observed time for reconciliation.

Out-of-order provider events must not make the aggregate state regress incorrectly. The state reducer uses evidence precedence/version rules, not arrival order alone.

---

## Relationship with Event Inbox

Event Inbox is the durable verified external-event ingress authority.

Email Delivery Evidence is a domain projection/normalized fact ledger.

Do not duplicate full raw webhook/event bodies indefinitely into Email tables. Domain rows should retain only safe references and normalized evidence required for state/reconciliation/audit.

Replaying a retained Event Inbox item must be idempotent at the Email projection layer.

---

## Rendering references

Operational Email rows retain enough safe metadata to explain what was rendered:
- Template revision;
- layout/renderer profile version;
- locale;
- Notification occurrence/recipient;
- render fingerprint;
- attachment count/size summary where useful.

They do not make rendered HTML the long-term canonical Template definition.

A queued delivery stays pinned to its accepted immutable template/render semantics even if a draft changes later.

---

## Retention classes

Retention is separate for:
- Notification Occurrences;
- in-app Recipient rows;
- Channel Delivery routing state;
- Transport Attempts/errors;
- normalized provider evidence;
- unmatched reconciliation items;
- rendered-body diagnostics if explicitly enabled;
- generic Audit links.

Recommended paper direction:
- current in-app state retained per product/site policy;
- high-volume transport attempts retained for a bounded operational window;
- normalized failure/complaint/suppression evidence retained only as long as operational/legal/security purpose requires;
- full content/raw provider payloads minimized;
- security/service notifications may preserve a safe audit trace after user-visible content retention ends.

Exact durations remain product/privacy evidence.

---

## Site lifecycle

Site archive/suspension must stop inappropriate new fan-out/delivery according to classification while preserving enough state for deterministic resume/cancel/reconciliation.

Site deletion follows Site Lifecycle Coordinator:
- drain/cancel scoped pending work;
- stop new channel dispatch;
- preserve required operational/audit retention;
- delete/anonymize Recipient PII according to policy;
- remove PT-D rows or PT-E tables only after domain cleanup;
- never delete another site's provider events because external IDs collide.

---

## Backup/Restore

Site Backup includes only target-site Notification/Email rows required by selected retention/profile.

Restore does not resend completed historical messages merely because queued/attempt rows were restored.

On restore:
- terminal provider evidence remains historical evidence;
- pending/unknown states enter reconciliation before any resend where duplicates are possible;
- JobService schedules are regenerated/reconciled from current operational state, not blindly replayed;
- stale provider message IDs cannot cross site/connection scope.

---

## Future evidence matrix — NOT AUTHORIZED

Fan-out:
- 1 / 100 / 10k / 100k recipients;
- direct vs role/query/Membership recipient resolution;
- quiet-hours and digest scheduling;
- duplicate source events;
- site archive during fan-out.

Inbox:
- unread/read/archive queries at 10k/100k/1M recipient rows per large site;
- bulk mark-read;
- expiry/retention cleanup;
- wrong-site Recipient UUID lookup.

Delivery:
- provider accepted;
- provider rejected;
- timeout unknown outcome;
- retry with and without provider idempotency;
- rate limit/backoff;
- worker crash after send but before local commit;
- provider event before local attempt correlation exists;
- duplicate/out-of-order provider events;
- unmatched event reconciliation.

Topology:
- NE1 vs NE2 on 100/1k/10k sites;
- noisy-neighbor site generating 100k-recipient burst;
- Backup extraction/Restore;
- site delete/transfer;
- index/storage/migration cost.

Capture:
- p50/p95/p99 query and enqueue/fan-out latency;
- rows examined/query plans;
- write throughput;
- lock/deadlock/retry behavior;
- duplicate-delivery count (must meet correctness expectations);
- wrong-site leaks (must be zero);
- storage/index growth;
- cleanup/reconciliation throughput.

## Decision rule

NE1 is first benchmark baseline, not a pre-approved production topology. A profile that reports better throughput but leaks scope, duplicates sends after ambiguous outcomes, mislabels provider truth, or makes restore resend terminal mail is rejected.

## Development gate

No Notification/Email table, migration, renderer, transport adapter, provider event handler, webhook, JobService execution, message send or benchmark is authorized by this document. ADR-0014 explicit owner consent remains required.