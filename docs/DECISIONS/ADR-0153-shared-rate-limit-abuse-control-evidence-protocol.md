# ADR-0153 — Shared Rate Limit & Abuse Control Executable Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP36`

## Context

ADR-0045 already accepts a shared Rate Limit service with trusted-proxy-aware client identity and an atomic state adapter. Protector, REST, Forms and Webhooks/Connections also contain consumer-specific abuse/rate fixtures, but no dedicated executable contract previously certified the shared platform service itself.

The shared service must not collapse client identity, authenticated principal, rate key, admission, authorization and consumer side effects into one truth. It must also remain explicit about concurrency, storage failure, privacy, Multisite scope and application-layer limitations.

## Decision

Accept `docs/QUALITY/SHARED-RATE-LIMIT-ABUSE-CONTROL-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the canonical shared-service evidence contract.

It freezes **RLT-01…RLT-176** covering:
- trusted proxy/client/principal identity;
- key normalization, namespace and privacy;
- atomic admission/concurrency;
- window/refill/burst/backoff semantics;
- policy composition, response and bypass governance;
- backend outage/degraded/recovery behavior;
- evasion and abuse resistance;
- distributed state, cleanup and clock behavior;
- Multisite/network isolation;
- consumer integration boundaries;
- observability, privacy, performance and scale.

Independent certification classes remain separate: `RLT-I/K/A/W/P/F/E/D/M/O`.

## Preserved boundaries

- rate-limit allow is not authentication or authorization;
- forwarded headers are not trusted unless immediate-peer proxy policy authorizes them;
- ordinary transients are not presumed atomic security counters;
- admin capability is not an implicit bypass;
- idempotency, CAPTCHA/spam, provider quotas and edge/WAF protection remain distinct domains;
- Site and Network bucket ownership are explicit;
- Protector `PR`, REST, Forms `FM`, Webhooks/Connections `WC` and provider certifications remain separately executed and are not promoted by RLT evidence.

## Evidence-gated decisions

This ADR does **not** select or certify:
- a final fixed-window/token-bucket/sliding-window algorithm;
- exact numeric limits, windows, cooldowns or storage budgets;
- a DB/object-cache/edge provider state adapter;
- a trusted-proxy provider profile;
- fail-open/fail-closed policy for every consumer operation;
- performance/scale claims.

Those remain subject to executed RLT evidence and consumer-specific policy.

## Current execution truth

- RLT fixtures documented: **176**.
- RLT fixtures executed: **0/176**.
- shared limiter certifications: **0**.
- atomic state adapters certified: **0**.
- algorithm profiles certified: **0**.
- PR/REST/FM/WC counters unchanged.

## Development gate

No counter store, proxy parser, request hook, cache/DB adapter, WAF integration, load test or runtime request has been executed by accepting this ADR.

Execution and implementation remain prohibited until explicit scoped owner consent under ADR-0014 and the Approval Ledger.