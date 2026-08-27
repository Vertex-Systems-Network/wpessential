# WPEssential — Event Inbox PT-D Physical Benchmark Profile

Status: **Phase 0 paper architecture / benchmark profile only / no webhook runtime or DDL authorized**  
Related: ADR-0040, ADR-0055, ADR-0057, ADR-0058, ADR-0069, ADR-0071, Connection Adapter Certification Contract.

## Purpose

Define the first future physical benchmark profile for WPE's durable normalized Event Inbox while preserving the accepted webhook rule: required authenticity/replay checks happen before business mutation, provider payloads are normalized through certified adapters, and duplicate/out-of-order delivery is handled explicitly.

## Benchmark topology

### EI1 — PT-D shared scoped Event Inbox — first benchmark baseline

One shared WPE runtime store with explicit network/site scope plus provider/connection/profile identity.

### EI2 — PT-E per-site Event Inbox — mandatory large-network comparison

Equivalent per-site store used to measure physical isolation/noisy-neighbor benefits against table provisioning, network-shared Connections, migrations, diagnostics and retention complexity.

EI1 is benchmark order only, not final DDL approval.

---

## Trust boundary

Event Inbox stores **accepted verified ingress facts**, not arbitrary unauthenticated request bodies.

Inbound sequence remains:
1. resolve endpoint + provider/adapter profile;
2. enforce request byte/content-type limits;
3. capture exact raw bytes temporarily when required for signature verification;
4. verify signature/key/timestamp/replay according to certified profile;
5. derive provider delivery/event identity and destination scope from trusted endpoint/Connection configuration;
6. idempotently accept Event Inbox envelope;
7. acknowledge according to provider timing contract;
8. process asynchronously/durably;
9. reconcile uncertain/out-of-order business facts where supported.

Invalid signature/replay attempts may create safe Security/Audit diagnostics, but do not become normal processable Event Inbox records.

A provider lacking cryptographic signatures requires an explicitly certified alternative authenticity profile; `no signature` is never silently treated as verified.

---

## Scope derivation

Every EI1 row has explicit:
- `network_id`;
- `site_id` or explicit network-owned scope;
- Connection identity;
- provider/adapter profile identity.

Critical rule: **scope is not trusted from attacker-controlled payload fields**.

The trusted inbound endpoint/Connection/delegation configuration determines the allowed scope before payload business mapping.

For network-shared Connections, a certified routing rule may map an event to one or more target sites only after trusted provider identity + tenant/resource mapping succeeds. Ambiguous routing enters reconciliation/quarantine; it is never guessed from matching numeric IDs.

---

## Logical Event Inbox envelope

Candidate physical fields/invariants:
- internal numeric ID + stable WPE event UUID;
- explicit scope coordinates;
- Connection UUID;
- provider/adapter/profile version;
- provider delivery/event ID when supplied;
- provider event type;
- dedupe identity/profile;
- occurred-at timestamp when provider semantics support it;
- observed/accepted-at timestamp;
- verified authenticity profile/key reference metadata, never secret key material;
- source subject/resource reference safe subset;
- normalized typed payload document + schema version;
- normalized payload fingerprint;
- optional raw-payload retention reference only when policy requires;
- processing state/version;
- processing attempt/error safe metadata;
- reconciliation state;
- next retry/reconciliation time where relevant;
- correlation/request IDs;
- retention/purge timestamps.

Raw HTTP headers, Authorization values, webhook secrets and complete sensitive provider objects are not generic Event Inbox columns.

---

## Dedupe identity

Preferred unique identity is scoped by:
- scope;
- Connection/provider profile;
- provider event/delivery ID.

When provider does not supply a stable event ID, adapter must declare a certified deterministic dedupe strategy using authenticated payload facts/fingerprint + bounded semantic window where safe.

A generic hash of arbitrary raw request bytes is not automatically sufficient because providers may change irrelevant serialization while representing the same event.

Duplicate delivery behavior:
- same accepted identity resolves to the existing envelope;
- may update safe delivery-observation counters/timestamps if useful;
- never creates a second business transition merely because provider retried;
- conflicting payload for same provider event ID enters conflict/reconciliation diagnostics rather than silent overwrite.

---

## Processing state machine

Candidate states:
- `accepted`;
- `processing`;
- `processed`;
- `failed_retryable`;
- `failed_terminal`;
- `reconciliation_required`;
- `quarantined_schema`;
- `paused_scope_lifecycle`;
- `retained_terminal`;
- physical purge later according to retention.

`processed` means WPE consumer handling completed according to the registered event contract. It does not prove the provider's underlying commercial/business object is currently correct if the provider supports later reversals or reconciliation.

Consumer-specific durable state remains in its owning domain (Membership, Email, Connections, etc.).

---

## Consumer idempotency

Event Inbox dedupe is necessary but not sufficient.

Every consuming domain must bind its own idempotency/correlation to Event Inbox identity/provider source reference so that:
- Job retry after crash;
- manual replay;
- duplicate provider delivery;
- restore/reconciliation
cannot silently repeat an irreversible business mutation.

Event replay reprocesses the same logical ingress identity; it does not mint a fresh external fact.

---

## Ordering

Provider arrival order is not assumed causal order.

Event envelope retains both occurred and observed timestamps when trustworthy, plus provider sequencing/version tokens when supplied.

Consumers decide with domain rules:
- state version/period chronology;
- provider source-of-truth reconciliation;
- evidence precedence;
- stale event suppression.

An older event arriving later cannot blindly regress a newer Membership/Email/business state.

---

## Payload storage

Current paper direction:
- store a bounded adapter-validated normalized payload document needed for durable processing/replay;
- store only required safe source references in normalized columns;
- retain exact raw payload only by explicit provider/security/recovery need and bounded retention;
- raw payload, if retained, uses protected storage/reference and separate privacy classification;
- never index arbitrary provider JSON properties by default.

Schema evolution uses adapter/profile version + normalized payload schema version. Unknown/new event shape can enter `quarantined_schema` without being treated as a known business fact.

---

## Candidate hot indexes

Exact order/types remain benchmark evidence.

Families to test:
- scope + Connection/profile + provider event ID unique/dedupe;
- scope + processing state + next retry/reconciliation time;
- scope + observed time for admin/retention;
- scope + provider event type + observed time;
- scope + reconciliation state + observed time;
- scope + source subject/resource reference where a certified reconciliation workload proves need;
- correlation/request ID for diagnostics.

Do not build broad indexes over full normalized payload text.

---

## Claim/concurrency model

Event Inbox processing must tolerate at-least-once Job execution.

Candidate future benchmark compares:
- optimistic processing generation + compare/update;
- short DB claim/lease record;
- JobService-owned claim with Event Inbox state precondition.

Required outcome:
- two workers cannot concurrently commit the same non-idempotent consumer transition;
- abandoned claim can recover;
- long external reconciliation does not hold a DB transaction unnecessarily;
- deadlock/retry produces deterministic state.

Exact primitive remains P-003/provider evidence.

---

## Site lifecycle

When a site is archived/suspended/deleting:
- trusted inbound endpoint may still need to acknowledge provider retries safely;
- accepted events can enter `paused_scope_lifecycle` or reconciliation according to domain/provider policy;
- no protected business mutation proceeds merely because an external event arrived;
- delete planning accounts for provider webhook subscriptions, Connection delegation and dedupe retention window;
- another site's events can never be deleted because provider IDs collide.

After physical site deletion, a bounded minimal tombstone/dedupe receipt may be required until webhook subscription/provider retry risk is resolved. Exact retention is provider/privacy evidence.

---

## Backup/Restore

Site Backup includes Event Inbox rows only according to selected operational/recovery policy and target scope.

Restore rules:
- historical `processed` events do not automatically replay;
- `failed_retryable`, `reconciliation_required` and ambiguous in-flight rows are explicitly reconciled;
- restored raw-payload references must remain protected/valid or be marked unavailable;
- Event Inbox identity/dedupe survives restore where needed to prevent duplicate business application;
- external provider/source of truth can supersede stale restored operational state through certified reconciliation.

---

## Privacy and retention

Event Inbox can contain PII/business-sensitive payloads.

Separate retention classes:
- normalized processable envelope;
- raw verification payload/reference;
- terminal dedupe/tombstone metadata;
- processing/error diagnostics;
- Security/Audit record of rejected attacks.

Raw payload retention should generally be shorter than minimal dedupe/reconciliation metadata unless a real recovery/legal requirement exists.

Exporter/eraser behavior is adapter/domain-specific and cannot delete facts required for active security/reconciliation without an explicit retained-with-reason policy.

---

## Future evidence matrix — NOT AUTHORIZED

Correctness/security:
- valid/invalid signature;
- replayed timestamp/signature;
- duplicate same ID/same payload;
- same ID/conflicting payload;
- no-provider-ID certified dedupe;
- out-of-order events;
- schema drift/unknown event;
- wrong-site payload targeting;
- network-shared Connection ambiguous routing;
- two workers claiming same event;
- crash before/after consumer commit;
- manual replay;
- site archived/deleted during receipt;
- restore of processed/in-flight rows.

Scale:
- 100k / 1M / 10M retained envelopes where practical;
- burst ingestion;
- one noisy site/Connection;
- reconciliation backlog;
- retention purge;
- 100 / 1k / 10k-site networks;
- EI1 vs EI2 migration/provisioning/Backup comparison.

Measure:
- receipt/dedupe p50/p95/p99 latency;
- sustainable inserts/sec;
- Job claim/process throughput;
- duplicate business application count (must be zero for idempotent contract);
- wrong-site processing count (must be zero);
- rows examined/query plans;
- lock/deadlock/retry behavior;
- index/storage growth;
- retention/reconciliation throughput.

## Decision rule

EI1 is the first benchmark profile. Performance cannot compensate for failed authenticity, scope isolation, dedupe, consumer idempotency or restore safety. EI2 remains mandatory comparison for large-network/private-event physical isolation.

## Development gate

No webhook endpoint, provider subscription, Event Inbox table, migration, raw payload store, Job execution, provider/API call, fixture or benchmark is authorized. ADR-0014 explicit owner consent remains required.