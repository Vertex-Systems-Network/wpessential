# WPEssential — REST API Builder Executable Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Related: ADR-0028, ADR-0094, `docs/ARCHITECTURE/REST-ENDPOINT-OPERATIONAL-SECURITY-CACHE-RATE-PROFILE.md`, Query, Policy, JobService, Protector, Multisite, ADR-0014.

## 1. Purpose

Define executable evidence required before REST API Builder can claim route, authentication, authorization, schema, idempotency, rate-limit, cache, CORS, error, Multisite or scale support.

The request invariant is fixed:

**a published compiled descriptor resolves through WordPress REST, then authentication + trusted scope + Policy/operation guard precede Query/Data Source/Ability execution. Response projection, CORS, route visibility and idempotency never substitute authorization.**

## 2. Runtime profile

Certification records:
- WordPress/PHP/database versions;
- RE1/RI1/RI2/RL1 profile and backend versions;
- single-site/Multisite topology;
- authentication modes enabled;
- Endpoint/Query/Ability/Data Source/Policy versions;
- object/persistent cache profile;
- trusted-proxy/rate-store profile;
- CORS configuration;
- operational retention windows;
- load-test environment/capacity profile.

## 3. Route/descriptor/auth fixtures

### REST-01 — Published route registration
Published Endpoint revision registers once at the intended namespace/path/method and resolves the pinned compiled descriptor.

### REST-02 — Draft is not executable
Draft/editor Definition cannot become runtime route or fallback execution source.

### REST-03 — Archived/disabled route
Disabled/archived revision fails closed with stable behavior and cannot execute stale descriptor.

### REST-04 — Route conflict
Conflict with core/third-party/WPE route is detected or resolved deterministically before support is claimed.

### REST-05 — Path parameter typing
Malformed path parameters fail before target resource access.

### REST-06 — Cookie + nonce
Same-site browser mutation requires certified WordPress cookie/nonce semantics and still executes Policy.

### REST-07 — Application Password
Certified external machine request authenticates via WordPress Application Password semantics without exposing stored credential material.

### REST-08 — Explicit anonymous mode
Anonymous route works only when explicitly published and still applies configured resource/public Policy.

### REST-09 — Missing permission/auth configuration
Absence/misconfiguration does not silently become public access.

### REST-10 — Unsupported auth adapter
Unknown/uncertified adapter fails/degrades closed instead of bypassing WPE/WordPress authority.

## 4. Scope/authorization/schema fixtures

### REST-11 — Endpoint-level deny
Authenticated principal lacking endpoint Policy cannot execute underlying operation.

### REST-12 — Resource-level IDOR
Authorized endpoint user cannot read/mutate another protected resource by changing numeric ID/UUID.

### REST-13 — Wrong-site selector
Request-provided site ID cannot change effective trusted scope without explicit network mode and authorization.

### REST-14 — Cross-site authorized mode
Explicit network/cross-site endpoint reauthorizes each target site/resource and operates only within bounded authorized set.

### REST-15 — Unknown field mass assignment
Mutation rejects unknown body keys by default; target Data Source extra fields do not become writable implicitly.

### REST-16 — Protected field mutation
Password/role/secret/internal/domain-protected fields cannot be written through generic endpoint mapping.

### REST-17 — Parameter bounds
Array size, object depth, string length, enum/range/format limits are enforced server-side.

### REST-18 — Identifier injection
Raw table/column/Ability/class/function names from request cannot select executable backend primitives.

### REST-19 — Query/order/filter injection
Malicious order/filter/query identifiers are validated against registered typed Query contract.

### REST-20 — Response projection
Only authorized declared fields are serialized; hidden fields do not leak in errors, links, cursors or metadata.

### REST-21 — Concealment semantics
Configured 404 concealment does not reveal protected resource existence through divergent body/timing metadata beyond accepted profile.

### REST-22 — Fuzz/malformed JSON
Malformed/deep/type-confused payloads fail safely without stack trace, SQL or arbitrary deserialization.

## 5. Idempotency fixtures

### REST-23 — Required idempotency missing
Mutation configured `required` rejects missing/invalid key before operation.

### REST-24 — Same key same request
Replay returns/references the same logical successful operation result without duplicate side effect.

### REST-25 — Same key different request
Same key with materially different normalized request returns conflict.

### REST-26 — Concurrent same-key race
Two simultaneous requests admit one logical operation under certified RI backend semantics.

### REST-27 — Crash before target mutation
Retry can proceed safely according to stored operation state.

### REST-28 — Crash after target commit before success record
Recovery/reconciliation detects possible committed target effect and does not blindly repeat it.

### REST-29 — External timeout unknown outcome
Operation enters `outcome_unknown`/reconciliation state when side effect may have occurred.

### REST-30 — Idempotency scope isolation
Same key on another endpoint/principal/site cannot collide or replay first operation.

### REST-31 — Idempotency retention expiry
Expiry behavior is explicit; old keys are not guaranteed forever and high-risk retry semantics remain truthful.

### REST-32 — RI backend unavailable
Failure/degraded behavior follows endpoint risk policy and never silently removes required idempotency correctness.

## 6. Rate-limit fixtures

### REST-33 — Atomic concurrent admission
Concurrent requests respect declared window/burst count under certified shared limiter.

### REST-34 — Boundary/reset semantics
Window/burst edges behave deterministically enough for advertised policy.

### REST-35 — Authenticated identity key
High-risk authenticated API is not protected solely by spoofable IP identity.

### REST-36 — Trusted-proxy spoof resistance
Untrusted forwarded headers cannot evade limiter or change trusted client scope.

### REST-37 — Site namespace isolation
Noisy client/site cannot consume or overwrite another site's limiter key namespace unless policy explicitly network-shared.

### REST-38 — Limiter unavailable
Fail-open/fail-closed/degraded behavior is explicit per risk class and visible in diagnostics.

## 7. Cache/CORS/error fixtures

### REST-39 — Public read cache
Explicit public cacheable endpoint can reuse representation only for equivalent authorized/public context.

### REST-40 — Principal cache isolation
Privileged response cannot be served to another principal/anonymous user.

### REST-41 — Site cache isolation
Same endpoint/parameters on different sites cannot cross-serve data.

### REST-42 — Revision/dependency invalidation
Endpoint/Query/Policy/source generation change invalidates or versions stale cached response.

### REST-43 — Revocation cache invalidation
Capability/Membership/resource revoke prevents stale privileged cached allow/response beyond accepted correctness window.

### REST-44 — Pagination/cursor cache identity
Cursor/page/filter state participates in identity and cannot expose another result window/principal.

### REST-45 — CORS exact allowlist
Unapproved Origin is denied/not granted browser access; no arbitrary origin reflection.

### REST-46 — Credentialed CORS wildcard
`*` with credentials is rejected/not emitted under certified profile.

### REST-47 — Preflight is not authorization
Successful preflight never makes actual unauthenticated/unauthorized mutation executable.

### REST-48 — Error redaction
Errors exclude stack traces, SQL, Vault values, raw provider secrets, filesystem/service topology and another scope's identifying details.

## 8. Multisite/scale fixtures

### REST-49 — Multisite same endpoint key
Site-scoped endpoint/operational records/cache/rate/idempotency do not collide across sites.

### REST-50 — Network bounded operation
Network endpoint requires network authority and cannot synchronously perform an unbounded all-site mutation loop.

### REST-51 — Site lifecycle/in-flight operation
Site deletion/drain prevents new unsafe operation and reconciles high-risk in-flight state according to lifecycle policy.

### REST-52 — Scale/load profile
Reference 100k/1M-row Query endpoints and controlled concurrent workloads meet bounded latency/query/memory/error budgets without weakening authorization/rate/idempotency correctness.

## 9. Pass gates

Certification fails if:
- draft/missing permission route becomes public/executable;
- wrong-site selector changes trusted scope;
- mass assignment/protected field mutation succeeds;
- IDOR exposes/mutates unauthorized resource;
- required idempotency duplicate creates repeated side effect;
- proxy spoof bypasses limiter;
- privileged cache response reaches another principal/site;
- CORS is represented as server authorization;
- errors leak secrets/SQL/stack traces;
- network endpoint performs unbounded unsafe fan-out.

## 10. Required future evidence report

Include:
- runtime/adapter/backend profile;
- REST-01…REST-52 pass/fail;
- route/auth matrix;
- fuzz/mass-assignment/IDOR/wrong-scope evidence;
- idempotency crash/race results;
- rate-limit concurrency/spoof results;
- cache/revocation/CORS results;
- Multisite isolation;
- load/query/memory measurements;
- unsupported/degraded auth/backends.

## 11. Current state

**REST fixtures executed: 0/52.**

No REST route registration/request, auth flow, target mutation, idempotency/rate/cache write, CORS execution, fuzz/load test or Multisite runtime operation has been executed.

## 12. Development gate

Execution requires explicit owner consent under ADR-0014.