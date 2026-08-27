# ADR-0094 — REST Endpoint Operational Runtime Profile

Status: **Accepted paper runtime/security profile / executable evidence pending**  
Date: 2026-08-28

## Context

REST API Builder already compiles Endpoint Definitions into validated descriptors, but operational ownership for idempotency, rate limiting, caching, CORS and Multisite scope still needed an explicit paper boundary.

## Decision

Accept:
- **RE1 — WordPress REST registered-route + WPE compiled descriptor** as the first runtime profile;
- **RE2 — custom gateway/edge adapter** only as a future comparison that cannot weaken local WPE authorization;
- **RI1 — PT-D scoped idempotency record** as the first persistence candidate;
- **RI2 — backend-neutral atomic idempotency service** as a mandatory implementation comparison where suitable;
- one shared WPE Rate Limit Service rather than per-endpoint ad-hoc counters;
- response caching only for read operations whose authorization/scope/generation dependencies can be represented safely.

No limiter/cache/idempotency backend is production-selected by this ADR.

## Authorization invariant

Authentication, CORS and response projection are not substitutes for authorization.

Every request must establish trusted scope and pass endpoint capability/Policy, resource/domain Policy and operation-specific guards before mutation or protected data exposure.

Anonymous/public mode is explicit; it is never the absence of a permission callback.

## Scope invariant

Request-provided site/resource identifiers are untrusted selectors. Site/network scope is derived from trusted route/context/descriptor mapping and reauthorized against the target scope.

## Idempotency invariant

Same key + same normalized logical request identifies one operation. Same key + materially different request is a conflict. Timeout after a possible external side effect becomes `outcome_unknown`/reconciliation rather than blind replay.

Idempotency is not authentication.

## Cache invariant

Privileged/private result caches cannot be reused across lower-privilege principals or sites. If access dependencies cannot be safely encoded/invalidation-versioned, shared persistent caching is prohibited.

## CORS invariant

Default is same-origin. Credentialed wildcard or arbitrary Origin reflection is not accepted. CORS remains browser policy, not API security authority.

## Evidence still required

After explicit owner consent:
- route registration/auth adapters;
- schema fuzz/mass-assignment/IDOR/wrong-site attacks;
- RI1 vs RI2 atomicity/crash/unknown-outcome behavior;
- rate-limit concurrency/proxy/noisy-site behavior;
- permission-aware cache invalidation/leakage;
- CORS attacks;
- large Query-source and large-network isolation tests.

Executed REST operational fixtures: **0**.

## Development gate

This ADR authorizes no WordPress route, descriptor compiler, idempotency table/service, rate limiter, cache backend, CORS handler, request execution or benchmark. ADR-0014 explicit owner consent remains required.