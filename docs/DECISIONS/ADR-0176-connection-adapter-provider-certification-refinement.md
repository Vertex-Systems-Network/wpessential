# ADR-0176 — Connection Adapter Provider Certification Refinement

Status: **Accepted planning/evidence contract / executable evidence pending**  
Date: 2026-08-28

## Context

ADR-0040 defines Safe HTTP, verified webhook ingress and Event Inbox architecture. ADR-0055 defines I0–I5 adapter/provider/capability certification. ADR-0080 defines the Event Inbox physical baseline and ADR-0122 fixes generic Webhooks/Connections/Event Inbox executable evidence as WC-01…WC-156.

WC already includes generic I0–I5 runtime gates, but it is not a durable provider-profile certification overlay. A separate namespace is needed so exact provider/API/auth/capability/version evidence can be awarded without duplicating or replacing generic WC evidence.

Current provider certification truth:
- I4-certified provider profiles: **0**;
- I5 Production Profile Certified provider profiles: **0**.

## Decision

Accept:

`docs/QUALITY/CONNECTION-ADAPTER-PROVIDER-CERTIFICATION-EVIDENCE-PROTOCOL.md`

with fixed executable IDs **ICP-F001…ICP-F176**.

Current execution state:
- ICP-F documented: **176**;
- ICP-F executed: **0/176**.

`WC-01…WC-156` remains the generic Connections/Webhooks/Event Inbox runtime evidence domain. `ICP-F001…ICP-F176` is the exact provider/profile/capability certification overlay. Neither passes the other automatically.

## Preserved I0–I5 ladder

- I0 — Detected / Configurable; no provider connectivity claim.
- I1 — Authentication Certified for exact auth/account/profile.
- I2 — Read Certified per capability.
- I3 — Write / Action Certified per mutation/action.
- I4 — Event / Reconciliation Certified per event/reconciliation capability.
- I5 — Production Profile Certified only when all capabilities publicly advertised for the exact profile are closed; unsupported capabilities stay explicit.

## Certification identity

A certification is scoped to:

`adapter_key + adapter_version + provider_key + provider_profile_version + provider_api_version + environment/region + auth_profile + capability_key`

Provider brand-level permanent certification is prohibited.

## Truth boundaries

The accepted evidence preserves:
- adapter installed ≠ provider compatible;
- configuration valid ≠ provider connected;
- Test Connection success ≠ capability certification;
- provider authentication ≠ WPE authorization;
- I2 read ≠ I3 write;
- one certified mutation ≠ every mutation;
- webhook reachable ≠ authentic;
- signature valid ≠ current/correct owning-domain business fact;
- HTTP 2xx ≠ downstream business completion unless exact protocol says so;
- JobService at-least-once ≠ exactly-once external mutation;
- provider documentation/changelog ≠ runtime I-level certification;
- generic Connection I-level ≠ Backup C-level, Email ET-level or Membership MB-level certification.

## Evidence coverage

ICP-F001…ICP-F176 covers:
- exact adapter/provider/API/environment/auth/capability identity;
- I0 adapter/dependency/configuration behavior;
- I1 Vault/auth/account/tenant identity;
- OAuth/credential refresh/rotation/revoke;
- Safe HTTP/TLS/SSRF/DNS/redirect controls;
- I2 read/list/get/query/pagination/mapping;
- I3 action authorization/input/granularity;
- I3 idempotency/unknown outcome/JobService reconciliation;
- inbound webhook signature/replay identity;
- I4 Event Inbox normalization/ordering/idempotency;
- I4 source-of-truth reconciliation;
- webhook subscription lifecycle/restore/clone;
- provider error/rate/quota/backpressure handling;
- privacy/security/audit/data minimization;
- Multisite ownership/provider-tenant routing;
- I5 advertised-capability closure/version drift/runbook/recertification.

## Runtime state

**NOT EXECUTED.** No provider account, credential exchange, OAuth flow, network/API request, webhook subscription/delivery, Event Inbox runtime operation, Job, external mutation, provider reconciliation or benchmark occurred.

## Development gate

ADR-0014 remains binding. This ADR is planning/evidence acceptance only and does not authorize runtime/source implementation or provider certification execution.