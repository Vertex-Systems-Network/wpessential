# ADR-0115 — REST API Builder Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28

## Decision

REST API Builder cannot claim production-ready route, auth, scope, schema, idempotency, rate-limit, cache, CORS or Multisite support until a future implementation passes `docs/QUALITY/REST-API-BUILDER-EXECUTABLE-EVIDENCE-PROTOCOL.md` for its certified operational profile.

The protocol preserves RE1 WordPress REST + compiled descriptor and fixes evidence for:
- draft/archive/route conflict fail-closed behavior;
- WordPress cookie+nonce, Application Password and explicit anonymous auth modes;
- endpoint + resource Policy, trusted site/network scope and IDOR resistance;
- mass-assignment/protected-field/identifier/query fuzz resistance;
- authorized response projection and concealment;
- RI idempotency same-key/different-body/concurrency/crash/unknown-outcome/degraded-backend semantics;
- atomic shared rate limiting, trusted-proxy spoof resistance and site namespace isolation;
- principal/site/revision/dependency/revocation cache safety;
- exact CORS allowlist/credential/preflight semantics;
- redacted stable errors;
- bounded network operations and scale/load evidence.

## Current state

REST-01…REST-52 documented. **0/52 executed.**

## Development gate

No REST route/request, auth flow, mutation, idempotency/rate/cache write, CORS/fuzz/load test or Multisite runtime operation is authorized before explicit owner consent under ADR-0014.