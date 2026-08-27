# ADR-0079 — Notification & Email PT-D Operational Benchmark Baseline

Status: **Accepted paper benchmark profile / runtime and provider evidence pending**  
Date: 2026-08-28

## Context

Notification and Email semantics are already separated into logical occurrence, recipient state, channel delivery, rendering and provider truth. ADR-0071 identified PT-D as the preferred operational topology but did not define the first comparable physical profile or the boundary between external Event Inbox evidence and Email-domain delivery evidence.

## Decision

Future first physical benchmark profile is:
- **NE1 — PT-D shared scoped Notification/Email operational stores**.

Mandatory comparison:
- **NE2 — PT-E per-site operational stores**.

NE1 keeps separate logical stores for Notification Occurrences, Recipient Notifications, Channel Deliveries, Email Transport Attempts and normalized verified Email Delivery Evidence.

This ADR accepts the benchmark/profile boundary only. It does not approve final DDL, provider adapters, renderer code or retention durations.

## Invariants

- every site-owned operational row has explicit network/site scope;
- Notification creation, recipient read state, transport acceptance and confirmed delivery remain distinct facts;
- `wp_mail()`/transport success never means Delivered without evidence;
- ambiguous timeout can remain `unknown_outcome` and cannot be blindly resent when duplication is plausible;
- provider events are verified/idempotently ingested through Event Inbox before becoming Email-domain evidence;
- Email tables do not indefinitely duplicate raw webhook bodies;
- out-of-order events cannot regress aggregate state incorrectly;
- unread counts do not scan message bodies/attempt history;
- restored terminal messages are not resent merely because historical rows were restored;
- wrong-site provider/reference collisions cannot resolve another site's delivery.

## Selection gate

A physical profile is rejected regardless of throughput if it leaks site scope, duplicates delivery under ambiguous retry, mislabels provider evidence, loses idempotency, or causes Restore to resend terminal historical mail.

## Evidence still required

After explicit owner consent:
- 1/100/10k/100k recipient fan-out;
- unread/inbox scale;
- quiet-hours/digest behavior;
- provider accepted/rejected/timeout/reconciliation;
- worker crash around send/commit boundary;
- duplicate/out-of-order/unmatched provider events;
- wrong-site attacks;
- site archive/delete/restore;
- NE1 vs NE2 noisy-neighbor, Backup and 100/1k/10k-site migration evidence;
- exact DDL/index/locking/retention evidence;
- ET0–ET5 provider certification.

Executed Notification/Email physical benchmarks: **0**.  
ET-certified providers: **0**.

## Development gate

This ADR authorizes no table, migration, Job run, renderer, transport/provider adapter, webhook/event handler, Email send or benchmark. ADR-0014 explicit owner consent remains required.