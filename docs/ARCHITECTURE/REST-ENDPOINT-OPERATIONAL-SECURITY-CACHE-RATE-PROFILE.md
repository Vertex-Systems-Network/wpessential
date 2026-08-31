# WPEssential — REST Endpoint Operational Security, Idempotency, Cache & Rate Profile

Status: **Phase 0 paper operational profile / no route, cache, limiter or runtime execution authorized**  
Date: 2026-08-28  
Related: REST Endpoint Compiled Runtime Model, Query ADR-0086, JobService, Policy, Protector, ADR-0014.

## Purpose

Narrow the operational runtime boundaries for REST API Builder without turning endpoint Definitions into mutable request logs or inventing a second authentication/authorization system.

The accepted pipeline remains:

`Endpoint Definition → published compiled descriptor → WordPress REST route adapter → authentication → Policy/operation guard → Query/Data Source/Ability → projected response`.

## Runtime profile

### RE1 — WordPress REST registered-route + compiled descriptor — first baseline

The future first runtime profile uses WordPress REST routing/authentication primitives where appropriate, with WPE compiled descriptors providing typed endpoint semantics.

RE1 does not interpret draft/editor JSON on each request.

The route adapter receives only a validated published descriptor containing registered references and bounded policies.

### RE2 — custom gateway/edge adapter — future comparison only

A reverse-proxy/API-gateway/managed edge adapter may later front WPE routes for selected deployments, but it must preserve the same WPE authentication, target-site authorization, operation semantics, idempotency and response truth.

Edge acceptance cannot make a locally forbidden operation authorized.

RE2 is not required for v1 and has no provider selected.

## State ownership split

### Definition Repository owns
- Endpoint identity;
- immutable published revision;
- request/response schema;
- auth mode configuration;
- Policy/Ability/Query/Data Source references;
- rate/idempotency/cache/CORS policy definitions;
- deprecation/version metadata.

### Compiled descriptor cache owns derived runtime projection
- route/path regex representation;
- normalized validators/mappings;
- resolved provider/operation references;
- dependency versions;
- cache/rate/idempotency policy projection.

It is rebuildable derived state and never the sole source of Definition truth.

### Operational stores/services own mutable request state
- rate-limit counters/windows;
- idempotency operation records;
- bounded request correlation/diagnostic records where policy permits;
- derived response cache entries.

Operational state must not be appended into the Definition revision payload.

## Authentication profile

Allowed baseline families:
- WordPress cookie + nonce for same-site browser clients;
- WordPress Application Passwords for certified external machine use where appropriate;
- explicit anonymous/public mode;
- future registered auth adapter after independent security certification.

Rules:
- no REST Builder-managed password/token database;
- no secret returned to browser after save;
- authentication success never replaces endpoint/target resource Policy;
- anonymous is an explicit published mode, never absence of a permission callback.

## Authorization order

Every request follows the same logical order:
1. resolve route + published descriptor;
2. establish authentication/principal context;
3. verify endpoint active/version/deprecation state;
4. verify endpoint capability/Policy;
5. resolve trusted site/network scope;
6. evaluate resource/domain Policy including Membership where configured;
7. validate operation-specific guard/re-auth/idempotency requirements;
8. execute registered Query/Data Source/Ability;
9. project only authorized fields;
10. apply response/cache headers according to visibility profile.

Response-field projection is not an authorization substitute.

## Scope resolution

Target site/network scope comes from trusted WordPress route/context/descriptor mapping, not arbitrary request body IDs.

For cross-site/network endpoints:
- Endpoint Definition must explicitly support network scope;
- caller needs network authority;
- target-site Policy is still evaluated where site-owned resources are touched;
- request-provided site identifiers are treated as untrusted selectors and checked against the authorized target set.

Wrong-site numeric IDs or UUIDs cannot change effective scope by themselves.

## Request/mass-assignment profile

Mutation bodies reject unknown fields by default.

Only declared schema properties can map to target operation arguments.

Controls:
- typed path/query/header/body parameters;
- bounded arrays/object depth/string lengths;
- enum/range/format validation;
- explicit null/default semantics;
- sensitive-field classification;
- no identifier/table/column/Ability selection from raw request strings;
- no arbitrary object deserialization.

A target Data Source with more fields than the endpoint exposes does not implicitly make those fields writable.

## Idempotency profile

Mutation endpoints can declare `required`, `optional` or `unsupported` idempotency behavior.

### RI1 — PT-D scoped idempotency record — first persistence candidate

Logical record:
- endpoint UUID + published revision;
- trusted site/network scope;
- authenticated principal/application identity class;
- operation key;
- opaque Idempotency-Key digest/normalized identity;
- normalized request fingerprint;
- state;
- safe result/resource reference;
- created/expires timestamps;
- correlation ID;
- unknown-outcome/reconciliation marker.

Raw secret request bodies are not stored merely for idempotency.

### RI2 — backend-neutral atomic idempotency service — mandatory implementation comparison

If a certified persistent cache/remote atomic store is available, WPE may map the same logical semantics there.

Requirements:
- durable enough for declared retry window;
- compare-and-set/first-writer behavior;
- namespace/scope isolation;
- recoverable/degraded behavior defined;
- no correctness dependency on non-persistent cache where mutation replay risk matters.

No backend is selected on paper.

## Idempotency states

Candidate:
- `in_progress`;
- `succeeded`;
- `failed_terminal`;
- `outcome_unknown`;
- `reconciliation_required`;
- `expired`.

Rules:
- same key + same normalized request returns/references same logical operation result;
- same key + materially different request returns conflict;
- timeout after possible external side effect becomes unknown/reconciliation state rather than blind re-execution;
- idempotency is not authentication.

## Rate-limit profile

REST routes consume a shared WPE Rate Limit Service; each Endpoint Definition does not invent its own counter implementation.

Logical key dimensions can include:
- trusted site/network scope;
- endpoint/revision or stable endpoint family;
- authenticated principal/application;
- supplemental trusted-client IP class where appropriate;
- operation/risk class.

### RL1 — atomic local/shared counter service — first semantic baseline

The implementation must support atomic increment/admission semantics for the declared window/burst model.

Exact database/object-cache/Redis-like backend remains executable evidence.

Rules:
- IP is supplemental, not sole identity for authenticated high-risk APIs;
- trusted-proxy parsing follows Protector policy;
- network-wide limiter never lets one site overwrite another site's key namespace;
- rate-limit failure/degraded behavior is explicit per endpoint risk class;
- anonymous mutation endpoints require stricter publish review.

## Cache profile

Only read operations classified cacheable may use persistent response caching.

Minimum cache identity factors where relevant:
- Endpoint UUID + published revision;
- trusted site/network scope;
- normalized parameters;
- authenticated principal/access generation when visibility differs;
- Query/source/relation/policy generations;
- response projection/version;
- locale;
- pagination/cursor state.

If authorization dependencies cannot be represented safely, shared persistent response caching is prohibited.

Mutation responses are not ordinary shared response cache entries.

## CORS profile

Default is same-origin/no custom permissive CORS.

When enabled:
- exact origin allowlist;
- exact methods/headers;
- credential behavior explicit;
- no `*` with credentials;
- no arbitrary Origin reflection;
- preflight never bypasses actual request authentication/authorization.

CORS is browser-origin policy only; non-browser clients can ignore it, so it cannot protect a public API by itself.

## Error/concealment profile

Errors map to stable WPE machine categories and safe HTTP status semantics.

Security-sensitive resource lookups may intentionally return 404 instead of revealing a forbidden resource exists.

Never expose:
- stack traces;
- SQL;
- raw provider response with secrets;
- Vault values;
- another site's/account's identifying details;
- internal filesystem/service topology.

## Descriptor generation/invalidation

Compiled descriptor is pinned to one immutable Endpoint revision.

It invalidates/rebuilds on:
- Endpoint publish/archive;
- Query/Ability/Data Source dependency generation change;
- Policy/capability contract change;
- auth adapter capability change;
- schema/runtime profile incompatibility.

Missing/invalid descriptor fails closed/degraded; runtime never falls back to executing draft Definition JSON.

## Operational retention

Separate retention classes for:
- idempotency operation records;
- rate-limit counters;
- safe request diagnostics;
- response cache;
- Audit events.

Do not retain full request/response bodies by default.

Security/financial/destructive idempotency records may require longer bounded retention than read-cache data.

Exact windows remain product/evidence policy.

## Multisite

Site is default scope.

Shared RI1/RL1 stores, if used, include explicit trusted scope in identity/indexes.

Network endpoints:
- require explicit Definition mode;
- network capability;
- target-site reauthorization for site-owned data;
- bounded target set/pagination;
- no synchronous unbounded all-site mutation loop.

Site deletion:
- route Definition lifecycle follows Definition ownership;
- operational idempotency/audit retention follows domain policy;
- active in-flight high-risk operations require lifecycle drain/reconciliation.

## Future executable evidence — NOT AUTHORIZED

### Route/auth
- route conflict/registration;
- cookie+nonce;
- Application Passwords;
- explicit public mode;
- deprecated/disabled descriptor behavior.

### Schema/security
- JSON/schema fuzzing;
- mass assignment;
- malicious path/query/order/filter identifiers;
- wrong-site IDs/cursors;
- IDOR/concealment;
- sensitive response projection.

### Idempotency
- two simultaneous same-key requests;
- same-key different-body conflict;
- crash before/after target mutation;
- external timeout/unknown outcome;
- retention expiry;
- RI1 vs RI2 atomicity/degradation.

### Rate limiting
- concurrent admission;
- burst/window boundaries;
- proxy/IP spoofing;
- one-site noisy client vs another site;
- limiter backend unavailable.

### Cache/CORS
- privileged-to-anonymous cache leakage;
- revoke/generation invalidation;
- stale endpoint revision;
- malicious Origin/preflight;
- credentialed CORS wildcard rejection.

### Scale
- 100k/1M source rows through bounded Query endpoints;
- 1k/10k concurrent read requests in controlled environment as capacity permits;
- 100/1k/10k-site namespace/operational key isolation.

Wrong-scope or unauthorized mutations/results required: **0**.

Executed REST operational fixtures: **0**.

## Paper recommendation

Use **RE1 WordPress REST + compiled descriptor** as the first runtime profile. Keep idempotency, rate limiting and response cache as separate operational services/stores with explicit scope and retention rather than mixing them into Definitions.

No custom gateway, limiter backend, cache backend or exact persistence schema is production-selected by this paper profile.