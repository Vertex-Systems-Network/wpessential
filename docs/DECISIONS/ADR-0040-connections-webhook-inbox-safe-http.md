# ADR-0040 — Connections: Safe HTTP, Webhook Gateway & Event Inbox

Status: **Accepted security architecture / provider-runtime evidence pending**  
Date: 2026-08-27

## Decision

All standard WPE external I/O composes a centralized:

**Connection Definition + Vault → Safe Outbound HTTP Service / Verified Inbound Webhook Gateway → Normalized Event Inbox → idempotent module consumers**.

Modules do not independently perform arbitrary remote requests or parse provider webhooks directly.

Custom public outbound URLs default to HTTP(S) public-network validation, unsafe/private/link-local destinations rejected, redirects off by default, and every permitted redirect revalidated. Internal/private endpoint support, if ever offered, is an explicit separate trust mode.

Inbound webhooks verify signature/replay/idempotency before normalized business processing. Raw provider webhook receipt never directly grants Membership/access authority.

## Why

- centralizes SSRF, secrets, timeout, redirects and response limits;
- avoids duplicate webhook signature/replay implementations;
- provides durable idempotent events during job/provider failures;
- supports reconciliation for lost/out-of-order provider events.

## Consequences

- credentials remain Vault refs;
- raw webhook retention minimized/configurable;
- consumers assume at-least-once dispatch;
- outbound webhook attempts keep stable delivery/event identity across retries;
- provider adapters declare exact signature/OAuth/response contracts;
- connection failures isolate rather than breaking unrelated modules.

## Evidence still required

After explicit consent: SSRF/DNS/IPv6/redirect fixtures, WordPress safe HTTP behavior, signature/rotation/replay tests, durable inbox indexes/jobs, provider adapters, outbound retry/idempotency and multisite isolation.

Supporting doc: `docs/SECURITY/CONNECTIONS-WEBHOOK-EVENT-INBOX-SSRF-ARCHITECTURE.md`.