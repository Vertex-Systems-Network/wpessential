# ADR-0080 — Event Inbox PT-D Benchmark Baseline

Status: **Accepted paper benchmark profile / runtime and provider evidence pending**  
Date: 2026-08-28

## Context

ADR-0040/0055 require verified, replay-aware, idempotent inbound event processing and ADR-0071 identifies Event Inbox as a PT-D candidate. A concrete future benchmark profile is needed without turning unauthenticated webhook bodies into trusted business state.

## Decision

Future first benchmark profile is:
- **EI1 — PT-D shared scoped Event Inbox**.

Mandatory large-network comparison:
- **EI2 — PT-E per-site Event Inbox**.

EI1 stores accepted verified normalized ingress envelopes with explicit scope, Connection/provider profile identity, dedupe identity, typed normalized payload, processing/reconciliation state and bounded retention metadata.

This ADR accepts benchmark order and invariants only; it does not approve final DDL, webhook endpoints, provider subscriptions or raw-payload retention durations.

## Invariants

- required authenticity/replay checks occur before normal processable Event Inbox acceptance and before business mutation;
- destination scope comes from trusted endpoint/Connection/delegation configuration, never attacker-supplied payload IDs alone;
- duplicate delivery cannot create a second business transition;
- same provider event ID with conflicting payload is a conflict/reconciliation condition, not silent overwrite;
- Event Inbox dedupe does not replace consumer-domain idempotency;
- provider arrival order is not treated as causal order by default;
- raw payload retention is explicit, protected and minimized;
- arbitrary normalized payload properties are not indiscriminately indexed;
- site lifecycle can pause/reconcile events without mutating protected domains;
- Restore never blindly replays historical processed events.

## Selection gate

A physical profile is rejected regardless of throughput if authenticity, replay defense, scope isolation, dedupe, consumer idempotency or restore safety fails.

## Evidence still required

After explicit owner consent:
- valid/invalid signatures and replay windows;
- duplicate/conflicting/no-ID dedupe profiles;
- out-of-order and schema-drift events;
- wrong-site/network-shared routing attacks;
- multi-worker claim/crash/replay;
- site lifecycle and restore;
- 100k/1M/10M Event Inbox scale where practical;
- burst/noisy-neighbor/reconciliation backlog;
- EI1 vs EI2 on 100/1k/10k sites;
- exact DDL/index/locking/retention evidence;
- I4/I5 provider event certification.

Executed Event Inbox physical benchmarks: **0**.  
I4/I5 provider event certifications: **0**.

## Development gate

This ADR authorizes no webhook endpoint, Event Inbox table/migration, provider subscription/API call, raw payload store, Job execution, fixture or benchmark. ADR-0014 explicit owner consent remains required.