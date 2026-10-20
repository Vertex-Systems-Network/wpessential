# REST API — Runtime Gap Matrix V1

Surface: **22 / REST API**  
Planning issue: **#588**  
Supervisor wave: **#583**  
Exact-main claim anchor: `b95e2ab190d452dc13882370ae613397f48f38c0`

Status: **UX_CONTRACT_COMPLETE / RUNTIME NOT AUTHORIZED**

Surface 22 has a BANK_REVIEWED 46-record Bank, schema-valid Atomic Option Contract and re-reviewed UX. Exact main has no dedicated REST API Builder runtime. This matrix records remaining prerequisites and canonical owner boundaries only.

## Gap matrix

| Area | Exact-main state | Future requirement | Boundary / gate |
|---|---|---|---|
| Master Options Bank | **PASS — BANK_REVIEWED / 46** | 46 normalized records; zero unresolved review items. | PR #1463 implementation PR; planning evidence only. |
| Atomic Option Contracts + UX | **PASS — UX_CONTRACT_COMPLETE** | 46/46 source projection; 33 parity, 2 exceed, 1 deferred, 10 rejected unsafe; zero missing/unclassified. | Runtime remains separately gated. |
| Endpoint Definition runtime | **ABSENT** | Canonical endpoint definitions, revision/CAS and lifecycle. | Surface 22. |
| Route compiler/registration | **ABSENT** | Safe WordPress REST registration with collision checks. | Runtime authorization required later. |
| Binding compiler | **ABSENT** | Typed mapping to Query/Data Source/Ability/Workflow. | Execution remains canonical owner. |
| Permission callback / Policy | **ABSENT FOR BUILDER** | Explicit server-authoritative auth + resource Policy for every endpoint. | Never optional. |
| Request schema validation | **ABSENT** | Typed path/query/header/body/file validation and bounded nesting. | No mass assignment. |
| Response projection | **ABSENT** | Explicit typed allowlist + sensitivity/permission mapping. | No raw object dumps. |
| Pagination/filter/sort | **ABSENT** | Bounded Query/Data Source mappings. | Query owner remains authoritative. |
| Embed/sparse fields | **ABSENT** | Already-allowed fields only, bounded related rows/depth and Policy. | Prevent leakage/N+1. |
| CRUD/write semantics | **ABSENT** | create/update/delete status/precondition/trash/permanent semantics. | Bound Ability/Data Source. |
| Idempotency | **ABSENT** | principal+endpoint+key scope, request hash mismatch conflict, result reuse. | Required for suitable writes. |
| Rate limiting | **ABSENT** | race-safe bounded profiles/dimensions/trusted-proxy policy. | Shared rate/Protector implementation may enforce. |
| CORS | **ABSENT** | exact origins, no unsafe reflection/wildcard+credentials. | Security evidence required. |
| Response cache | **ABSENT** | permission-safe public/private keys + invalidation. | No cross-user leakage. |
| Error envelope | **ABSENT** | stable safe codes/status/field errors/correlation/retry hint. | No internals/secrets. |
| Version/deprecation | **ABSENT** | published contract migration/deprecation/replacement. | Surface 22. |
| Docs/OpenAPI-like export | **ABSENT** | schema-derived read-only docs/export. | Same source of truth as endpoint. |
| Test console | **ABSENT** | bounded current-context/anonymous tests with redaction. | No arbitrary privilege impersonation. |
| Request logs | **ABSENT** | metadata-first redacted logs, retention/sampling. | No auth/cookies/secrets/raw P3 by default. |
| Public endpoint safeguards | **ABSENT** | field limits, anti-abuse, rate/cache, enumeration warnings. | Dedicated high-risk policy for public write. |
| Multisite | **UNSPECIFIED** | site/network route/principal/log/rate/cache scope. | Contract before implementation. |
| Accessibility | **NO SURFACE UI** | keyboard/focus/errors/status/schema-table accessibility. | Browser/axe evidence later. |
| Compatibility | **NO SURFACE RUNTIME** | WP/PHP/hosting/security-plugin/auth-adapter degradation. | Exact-head matrix later. |
| Portability | **ABSENT** | dependency refs, collision/version preflight, secret-free export. | Surface 26 orchestrates packages. |
| Reliability/performance | **NO SURFACE RUNTIME** | body/depth/page/time/byte budgets, async long work, no N+1. | Deterministic tests later. |

## Security hard gates

Future implementation must reject or fail closed on:

- missing or permissive implicit permission callback;
- raw SQL/PHP/filesystem callback execution;
- client-controlled arbitrary identifiers/orderby;
- blind request-body mass assignment;
- raw WP/user/meta response dumps;
- public write without dedicated Policy/anti-abuse/rate controls;
- `*` CORS with credentials or arbitrary Origin reflection;
- shared caching of principal-dependent responses;
- unbounded page/embed/body/depth/file inputs;
- secrets/cookies/Application Passwords/Authorization in logs or examples;
- test-console privilege escalation/impersonation;
- weakening a bound Ability's own authorization.

## Required Policy / Ability separation

Builder abilities should separately cover:

- endpoint list/get/create/update/validate;
- publish/enable/disable/archive;
- docs generation/export;
- test console;
- logs read;
- rate/CORS settings;
- high-risk public-write publication.

Runtime endpoint authorization remains endpoint Policy + bound owner Policy. Admin-builder capability alone never authorizes a runtime request.

## Accessibility evidence required later

- keyboard endpoint/schema editor;
- field-level and summary validation linking;
- focus after add/remove/save/test;
- semantic method/risk/auth/status labels;
- responsive nested schema/parameter tables;
- accessible docs/source views;
- axe checks across read/write/public/degraded states.

## Multisite evidence required later

- route/namespace collisions by site/network;
- network vs site endpoint definitions;
- current site/network execution context;
- Application Password/user-site membership behavior;
- rate/cache/log isolation;
- public endpoint site boundaries;
- package portability and network admin capabilities.

## Reliability/performance evidence required later

1. endpoint cannot register without permission callback;
2. object-level IDOR is denied after resource resolution;
3. nested request depth/item/body limits are enforced server-side;
4. unknown/mass-assignment fields do not reach mutations;
5. page size/sort/filter/embed are bounded/allowlisted;
6. idempotency duplicate/mismatch behavior is deterministic;
7. rate limiter is race-safe and trusted-proxy aware;
8. CORS credential wildcard/reflection regressions are blocked;
9. personalized cache cannot leak across users/sites;
10. response errors redact stack/SQL/path/secret data;
11. large/long operations hand off to Job resources rather than holding HTTP request;
12. logs remain bounded/redacted and cleanup is scheduled;
13. missing bound Query/Ability/Auth adapter degrades safely;
14. disabled/deprecated routes remain predictable and observable.

## Future implementation order

Only after Bank review + schema-valid option contracts:

1. Endpoint Definition + revision/Policy/validation;
2. route collision compiler + read-only docs/diagnostics;
3. read-only Query/Data Source bindings;
4. request/response schemas + pagination/filter/sort/embed;
5. authenticated write Ability bindings + idempotency;
6. rate/CORS/cache/logging/test console;
7. public endpoint high-risk controls;
8. versioning/deprecation/portability/multisite;
9. exact-head security/accessibility/compatibility/performance certification audit.

OAuth servers, arbitrary custom code and generic SQL endpoints remain outside baseline scope.

## Exit decision

The BANK_REVIEWED options inventory, 46-record Atomic Option Contract and UX planning milestone are complete. No dedicated REST API Builder runtime exists, and `runtime_allowed=false` remains correct.

A future runtime-readiness or implementation lane must be separately scoped. This contract does not authorize endpoint registration, request execution, auth/CORS widening or external exposure.
