# ADR-0028 — REST Builder Compiled Runtime Descriptor

Status: **Accepted architecture / compiler and runtime evidence pending**  
Date: 2026-08-27

## Decision

A published REST Endpoint Definition is validated and compiled into a versioned runtime descriptor before request execution.

Request handling uses only registered references to:
- WordPress REST route/methods;
- authentication adapter;
- capability/resource Policy;
- Query/Data Source/Ability operation;
- typed request mapping;
- response projection;
- pagination/rate/idempotency/cache/CORS policy.

Runtime does **not** evaluate arbitrary PHP, raw SQL or free-form Definition JSON as executable code.

## Authorization

Every endpoint has an explicit server-side permission policy. `public` is an explicit policy, not a missing callback.

Response projection is not authorization. Sparse fields cannot request data outside allowlisted projection.

## Compatibility

Published path/method/request/response/permission semantics are versioned contracts. Breaking changes require version/deprecation strategy.

## Security defaults

- same-origin/cookie+nonce for same-site authenticated use;
- WordPress Application Passwords candidate for appropriate external machine access;
- anonymous exposure explicit;
- mutation mass-assignment impossible because only mapped fields reach operations;
- collection pagination bounded;
- CORS restrictive by default;
- permission-aware caching;
- rate limiting/idempotency first-class descriptors.

## Rejected alternatives

- arbitrary route callback PHP editor;
- raw SQL endpoint builder;
- wildcard CORS convenience defaults;
- unbounded list endpoints;
- missing permission callback as public behavior.

## Evidence pending

Exact compiler, route conflict behavior, auth adapter matrix, rate-limit storage/concurrency, cache isolation, fuzz/security tests and scale require future authorized evidence.

Supporting: `docs/ARCHITECTURE/REST-ENDPOINT-COMPILED-RUNTIME-MODEL.md`.

ADR-0014 remains controlling.