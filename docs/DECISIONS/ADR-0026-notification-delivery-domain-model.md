# ADR-0026 — Notification Persistence & Delivery Domain Model

Status: **Accepted architecture / physical schema and channel adapters pending evidence**  
Date: 2026-08-27

## Decision

Notifications are separated into:
1. Notification Rule configuration;
2. logical Notification occurrence;
3. per-recipient/in-app state;
4. per-channel Delivery Attempt.

`created`, `queued`, `accepted_by_provider`, and `delivered_confirmed` are distinct facts.

A transport that only hands data to `wp_mail()` or another provider without a delivery receipt cannot claim Delivered.

## Consequences

- fan-out can be batched through Job Service;
- read/dismiss state remains user-specific;
- one channel can fail while another succeeds;
- quiet hours/digests/preferences are evaluated without duplicating business logic in Workflow;
- delayed notification never grants access to protected target; target reauthorizes when opened.

## Guardrails

- recipient queries do not run synchronously for unbounded audiences;
- delivery retries respect adapter idempotency/unknown-outcome semantics;
- private/protected content is minimized in delayed messages;
- provider secrets/raw responses are not generic Notification data;
- read state is not delivery state.

## Evidence pending

Exact tables, indexes, fan-out throughput, unread-count strategy, provider delivery semantics and retention require authorized benchmarks/adapters.

Supporting: `docs/ARCHITECTURE/NOTIFICATION-PERSISTENCE-DELIVERY-MODEL.md`.

ADR-0014 remains controlling.