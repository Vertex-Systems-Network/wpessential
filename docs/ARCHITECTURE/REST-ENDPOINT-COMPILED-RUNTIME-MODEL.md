# WPEssential — REST API Builder Compiled Runtime Model

Status: **Phase 0 paper architecture / no REST runtime implementation authorized**  
Related: REST API Builder Exhaustive Spec, Query AST v1, Policy Engine, Abilities, Connections.

## Purpose

Define how a no-code REST Endpoint Definition becomes a safe runtime route without evaluating arbitrary user code or bypassing WordPress authentication/capability rules.

## Core architecture

`Endpoint Definition → Validate/Publish → Compiled Runtime Descriptor → WordPress REST Route → Policy/Data Source/Ability execution`

The Definition is editable configuration. The compiled descriptor is a validated, versioned runtime projection. Request execution never interprets arbitrary builder UI JSON as code.

---

# Endpoint Definition

Definition Repository owns:
- stable UUID;
- namespace/version/path;
- methods;
- authentication mode;
- permission/policy requirements;
- request schema;
- parameter mappings;
- source operation: Data Source/Query/Ability;
- response projection/schema;
- pagination;
- cache;
- rate-limit/idempotency;
- CORS policy where applicable;
- lifecycle/deprecation metadata.

Publish validates full dependency graph.

---

# Compiled Runtime Descriptor

Contains only registered/validated references:
- route regex/path segments generated from typed path params;
- method allowlist;
- request JSON Schema/arg validators;
- auth mode adapter ID;
- permission policy ID/capabilities;
- target Query/Ability/Data Source operation ID;
- typed input mapping plan;
- output projection plan;
- pagination descriptor;
- cache descriptor;
- rate-limit descriptor;
- idempotency descriptor;
- CORS descriptor;
- schema/runtime version;
- Definition published revision UUID;
- dependency versions.

No PHP callback text/SQL/JS stored in descriptor.

---

# Route identity/versioning

Default form:
`/wp-json/wpessential/<api-namespace>/<version>/<path>`

Custom namespace allowed only within sanitized/conflict-checked WPE registry policy.

Controls:
- namespace;
- API version segment;
- path;
- method(s).

Published path/method changes are compatibility-impacting and require deprecation/consumer impact preview.

Route conflicts detected against WPE registry and known WordPress REST route inventory before publish.

---

# Authentication modes

Supported modes depend on context:

## WordPress cookie + nonce
Preferred for same-site authenticated wp-admin/frontend app requests.

## Application Passwords
Candidate built-in WordPress mechanism for external machine/API access when appropriate.

## Public/anonymous
Only explicit and only for operations whose policy/data is genuinely public.

## Registered auth adapter
Future OAuth/JWT/gateway adapters through SDK after security certification.

The REST Builder does not invent password/token storage itself.

---

# Permission model

Every route has server-side permission evaluation.

Compiled order:
1. authentication context;
2. endpoint enabled/version state;
3. required capability;
4. resource Policy;
5. Membership/other domain policy where Definition requests it;
6. operation-specific guard;
7. execute.

`public` is an explicit permission policy, not absence of callback.

Response projection does not substitute for authorization.

---

# Request schema

Sources:
- path params;
- query params;
- headers from allowlist;
- JSON/form body according to method/content type;
- current principal/context.

Each param declares:
- name;
- location;
- type;
- required;
- default;
- enum/format/range/length;
- nullable;
- array/object schema;
- sanitization/normalization;
- destination mapping;
- sensitive flag.

Unknown fields policy:
- reject by default for mutation bodies;
- optionally ignore for read filter surface only when explicit.

No mass assignment: only declared mapped fields reach target operation.

---

# Parameter binding

Mapping expressions use typed token/mapping grammar:
- request.path.*;
- request.query.*;
- request.body.*;
- principal.* safe fields;
- route/context constants;
- registered resolver.

No arbitrary expression/PHP execution.

Query AST parameters remain typed/bound.

---

# Operations

Endpoint can invoke one primary registered operation:
- Query execution;
- Data Source list/get/create/update/delete;
- Ability invoke;
- Workflow trigger only through registered safe Ability/action where accepted.

Destructive Ability retains its own authorization/idempotency/re-auth rules; REST exposure does not downgrade them.

---

# Response projection

Default returns only allowlisted fields.

Controls:
- field map;
- rename;
- nested object/list mapping;
- null behavior;
- safe computed renderer;
- links/pagination metadata;
- error envelope profile.

Sensitive source fields are unavailable unless Policy explicitly authorizes and schema marks exposure.

Sparse fields (`fields`) cannot request a field outside endpoint projection allowlist.

---

# Pagination

Collection endpoints require bounded pagination unless source is provably tiny/config-only.

Modes:
- page/per_page compatible with WP-style sources;
- cursor for large/runtime stores where provider supports.

Descriptor defines:
- default size;
- max size;
- total-count availability/cost;
- next/prev cursor/link metadata.

Client cannot set unbounded page size.

---

# Filtering/sorting

Only predeclared filter/sort keys compile to Query AST/provider operations.

Each filter declares:
- public API key;
- type/operators;
- target field/query param;
- index/cost class;
- max cardinality/list length.

No raw SQL-order-by/field name from request.

---

# Rate limiting

Descriptor declares:
- enabled;
- limit/window;
- key: authenticated principal / application credential / IP supplemental / composite;
- burst policy;
- response headers if exposed;
- trusted-proxy dependency for IP.

High-risk anonymous mutation endpoint cannot publish without explicit stronger security review/policy.

Atomic enforcement storage/algorithm remains implementation evidence.

---

# Idempotency

Mutation endpoints can require `Idempotency-Key`.

Scope:
- endpoint revision;
- principal/application;
- operation;
- key;
- bounded retention.

Stored result/reference permits safe retry. Sensitive response payload storage minimized/encrypted according to data class.

Unknown external side-effect outcome uses operation-specific reconciliation rather than blind replay.

---

# CORS

Default same-origin/no permissive custom CORS.

If enabled:
- exact origin allowlist;
- methods/headers explicit;
- credentials flag only with compatible exact-origin policy;
- preflight handling;
- no `*` with credentials;
- no reflecting arbitrary Origin.

CORS is browser policy, not authentication.

---

# Caching

Read endpoint caching only when:
- operation classified cacheable;
- policy/principal context included where needed;
- cache invalidation/version known;
- private responses not shared globally.

Cache key dimensions can include:
- endpoint revision;
- normalized request params;
- principal/access generation;
- source/query generation;
- locale.

Mutation endpoints not response-cached in normal sense.

---

# Error envelope

Maps WPE error taxonomy to HTTP status + safe machine code/message.

Examples:
- validation → 400;
- unauthenticated → 401 where appropriate;
- forbidden → 403;
- not found/concealed → 404;
- conflict/version/idempotency → 409;
- rate limit → 429;
- provider unavailable → 502/503 class according to source;
- internal → 500 with correlation ID.

No stack trace/SQL/provider secret.

---

# Logging/observability

Record safe:
- endpoint/revision;
- request/correlation ID;
- principal/application class;
- status/duration;
- policy result category;
- rate-limit/cache outcome;
- target operation;
- response size class;
- error category.

Request/response bodies off by default and sensitive fields redacted.

---

# Runtime descriptor cache

Published descriptor can be cached/generated ahead of requests.

Invalidated on:
- endpoint publish/archive;
- dependency schema/version change;
- policy capability contract change;
- source/query/Ability removal.

If descriptor invalid/missing, endpoint fails degraded/safe rather than interpreting unvalidated draft Definition.

---

# Deprecation/versioning

Route Definition can mark:
- active;
- deprecated;
- sunset date;
- replacement endpoint.

Breaking request/response/permission semantic change requires version/new route or explicit migration policy.

Permission becoming stricter can be security fix; becoming broader requires high review.

---

# Public endpoint safeguards

Publishing anonymous endpoint shows:
- data classification;
- operation side effects;
- rate-limit state;
- pagination limits;
- cache visibility;
- CORS;
- sample request/response;
- dependency health.

Anonymous create/update/delete disabled by default presets and requires dedicated explicit policy/capability to configure.

---

# Paper recommendation

Use a **compiled descriptor**, not runtime interpretation of free-form builder data.

Reuse:
- WordPress REST server;
- WordPress auth/Application Passwords where appropriate;
- WPE Policy;
- Query AST;
- Abilities;
- Data Sources;
- Error taxonomy.

Reject:
- arbitrary PHP callback route builder;
- raw SQL in endpoint;
- missing permission callback as public;
- wildcard CORS convenience defaults;
- unbounded collections;
- response-field filtering as security.

## Future tests — NOT AUTHORIZED

After consent:
- route registration/conflicts;
- cookie+nonce;
- Application Passwords;
- unauth/forbidden concealment;
- schema fuzzing;
- mass-assignment attempts;
- malicious filter/order identifiers;
- pagination max;
- CORS preflight/origin attacks;
- rate-limit concurrency/proxy spoof;
- idempotent retry;
- permission-aware cache leakage;
- Definition update/recompile;
- deprecated route;
- 100k-row query source.

No route/compiler/runtime has been implemented or run.