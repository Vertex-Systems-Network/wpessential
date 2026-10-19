# REST API — Native WordPress Audit V1

Surface: **22 / REST API**
Issue: **#1446**
Planning parent: **#588 / merged PR #601**
Seed: `config/product/options-bank/rest-api.json` — **46 records**

## Decision

**NATIVE AUDIT EVIDENCE COMPLETE; WORDPRESS CORE PROVIDES REST TRANSPORT, ROUTE REGISTRATION, SCHEMA VALIDATION AND AUTHENTICATION PRIMITIVES BUT NO ENDPOINT BUILDER.** Native primitives are execution transport and validation substrate only and never authorization by themselves. No endpoint registration, route exposure, CORS/auth widening or runtime promotion occurs here.

## Current native sources

- REST API Handbook — https://developer.wordpress.org/rest-api/
- Adding Custom Endpoints (`permission_callback` requirement) — https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
- `register_rest_route()` — https://developer.wordpress.org/reference/functions/register_rest_route/
- REST Schema (JSON Schema subset, validate/sanitize) — https://developer.wordpress.org/rest-api/extending-the-rest-api/schema/
- Authentication (cookie + `wp_rest` nonce, Application Passwords) — https://developer.wordpress.org/rest-api/using-the-rest-api/authentication/ and https://make.wordpress.org/core/2020/11/05/application-passwords-integration-guide/
- Pagination (`page`/`per_page`, `X-WP-Total`/`X-WP-TotalPages`) — https://developer.wordpress.org/rest-api/using-the-rest-api/pagination/
- Global parameters (`_fields`, `_embed`, `_envelope`, `_method`) — https://developer.wordpress.org/rest-api/using-the-rest-api/global-parameters/
- `rest_authentication_errors` — https://developer.wordpress.org/reference/hooks/rest_authentication_errors/
- `rest_send_cors_headers()` — https://developer.wordpress.org/reference/functions/rest_send_cors_headers/
- `wp_handle_upload()` — https://developer.wordpress.org/reference/functions/wp_handle_upload/
- `get_rest_url()` (per-site/multisite route base) — https://developer.wordpress.org/reference/functions/get_rest_url/
- In-repo: `docs/MODULES/REST-API-BUILDER-EXHAUSTIVE-SPEC.md`, `docs/PRODUCT/REST-API-BANK-ENTRY-READINESS-V1.md`

Native classification of the readiness checklist: route grammar/collision (register_rest_route; WPE adds collision detection), `permission_callback` (core warns when missing; WPE makes it mandatory), cookie+nonce and Application Passwords (native auth modes), schema validation (native subset), pagination headers (native), `_fields`/`_embed`/OPTIONS schema (native optional, WPE-bounded), media upload limits (native upload pipeline), multisite (per-site REST base), WP_Error status conventions (native, WPE envelope on top), CORS (core `rest_send_cors_headers()` reflects the request Origin with credentials allowed; WPE endpoint CORS policy must be explicit exact-origin and must not inherit arbitrary Origin mirroring), response caching (none native; personalized-data isolation is WPE policy).

## Seed-record disposition

| Seed record | Native disposition | Notes |
|---|---|---|
| `rest-api.endpoint.identity` | **WPE PRODUCT/POLICY** | Stable generated key and required name; key never changes after publish so dependencies/import refs stay valid. |
| `rest-api.endpoint.route` | **NATIVE SUBSTRATE + WPE POLICY** | Maps to register_rest_route() namespace/route grammar; WPE adds reserved-namespace, duplicate method+route and unique path-parameter validation. |
| `rest-api.endpoint.methods` | **NATIVE SUBSTRATE + WPE POLICY** | GET/POST/PUT/PATCH/DELETE per binding semantics; HEAD/OPTIONS follow WordPress; GET must never perform a state-changing action. |
| `rest-api.endpoint.lifecycle` | **WPE PRODUCT/POLICY** | Draft default; disabled/archived routes return safe unavailable/not-found without deleting data; delete shows dependency/usage/log impact and published-public warning. |
| `rest-api.binding.source` | **WPE PRODUCT/POLICY** | Execution stays with the canonical owner (Query S6, Data Source owner, Ability owner, Workflow S17); Surface 22 owns only the endpoint definition. |
| `rest-api.binding.ability-mapping` | **WPE PRODUCT/POLICY** | Endpoint maps typed inputs into the Ability input schema and output through the response schema; it cannot override the Ability permission callback with weaker policy; secret refs only when the Ability accepts them internally. |
| `rest-api.input.route-params` | **NATIVE SUBSTRATE + WPE POLICY** | Native REST args/validate_callback substrate. Route parameter is data, not authorization; object-level Policy rechecks the resolved resource. |
| `rest-api.input.query-params` | **NATIVE SUBSTRATE + WPE POLICY** | Native args schema; reserved/global WordPress REST parameters must not be shadowed without explicit compatibility handling. |
| `rest-api.input.headers` | **WPE PRODUCT/POLICY** | Only allowlisted custom headers map to inputs; Authorization/Cookie/internal proxy headers are never user-mappable; Idempotency-Key may be first-class. |
| `rest-api.input.body-schema` | **NATIVE SUBSTRATE + WPE POLICY** | Native JSON Schema validation substrate; strict unknown-property rejection is the default candidate for writes; bounded depth/items; never blind mass assignment. |
| `rest-api.input.file-upload` | **WPE PRODUCT/POLICY** | WordPress upload/security policies apply (wp_handle_upload substrate); private/public storage policy delegated to Media/S03; no arbitrary server path. |
| `rest-api.auth.cookie-nonce` | **NATIVE SUBSTRATE + WPE POLICY** | Default for authenticated WPE application calls; native wp_rest nonce CSRF protection. |
| `rest-api.auth.application-passwords` | **NATIVE SUBSTRATE + WPE POLICY** | Baseline external machine/client authentication where HTTPS and core support apply. |
| `rest-api.auth.adapter` | **DEFERRED / CROSS-OWNER** | Future certified adapter only after separate security review; provider lifecycle belongs to Surface 23 Connections or a certified adapter. |
| `rest-api.authz.policy` | **NATIVE SUBSTRATE + WPE POLICY** | Every endpoint requires explicit permission_callback semantics evaluated in order: context, site boundary, capability, resource Policy, Membership, business guards; no frontend-only authorization. |
| `rest-api.public.safeguards` | **WPE PRODUCT/POLICY** | Public is an explicit policy, never a missing callback; PII/private fields prohibited unless explicit secure use case; public write needs high-risk confirmation + abuse model. |
| `rest-api.output.response-schema` | **WPE PRODUCT/POLICY** | Never returns full WP object/user/meta by default; protected/internal fields need explicit allowlisting + policy. |
| `rest-api.collection.pagination` | **NATIVE SUBSTRATE + WPE POLICY** | Aligns with native page/per_page and X-WP-Total/X-WP-TotalPages headers; cursor only where the Data Source supports stable cursors; no per_page=all. |
| `rest-api.collection.filter-sort` | **WPE PRODUCT/POLICY** | Filters map into Query AST/Data Source schema; client orderby is never mapped to raw SQL identifiers. |
| `rest-api.output.sparse-fields` | **NATIVE OPTIONAL SUBSTRATE, WPE-BOUNDED** | Native _fields global parameter; restricted to already-allowed response schema and never reveals hidden fields; may be disabled for fixed payloads. |
| `rest-api.output.embed` | **NATIVE OPTIONAL SUBSTRATE, WPE-BOUNDED** | Native _embed/_links substrate; WPE requires explicit embed definitions with related-object Policy and bounded depth to avoid N+1 and leaks. |
| `rest-api.crud.semantics` | **WPE PRODUCT/POLICY** | Create field allowlist and 201/Location candidate; update optimistic version/ETag precondition candidate; delete trash vs permanent and cascade policy with server capability always required. |
| `rest-api.write.idempotency` | **WPE-EXCEED (no native equivalent)** | Not a native WordPress REST feature; WPE-exceed for create, external side-effect workflow, membership and payment-adjacent writes; key reuse with different hash returns 409. |
| `rest-api.rate.limit` | **WPE PRODUCT/POLICY** | Dimensions: principal/application, IP with trusted-proxy rules, endpoint, site; Retry-After/headers. Endpoint chooses a profile; shared rate service (Surface 27) owns storage/algorithm. |
| `rest-api.cors.policy` | **WPE PRODUCT/POLICY** | Default same-origin with no permissive headers; scheme/host/port validated; preflight tested; CORS never replaces authentication/authorization. |
| `rest-api.cache.response` | **WPE PRODUCT/POLICY** | Disabled by default for personalized/protected writes; permission-dependent responses never cached under a shared key. |
| `rest-api.response.status-errors` | **WPE PRODUCT/POLICY** | 200/201/204/400/401/403/404/409/413/422/429/5xx mapping; envelope with stable code, safe message, field errors, correlation ID, retry hint; never SQL, stack traces, paths, secrets or provider credentials. |
| `rest-api.versioning.deprecation` | **WPE-EXCEED (no native equivalent)** | No silent breaking schema change to a published endpoint; deprecation date/status, replacement endpoint and local-only usage telemetry. |
| `rest-api.docs.export` | **WPE PRODUCT/POLICY** | Docs generated from endpoint schema with synthetic/redacted examples only; OpenAPI-like export after schema stabilizes. |
| `rest-api.test.console` | **WPE PRODUCT/POLICY** | Admin-only; no impersonation of higher-privilege users without dedicated test-as capability/re-auth; public endpoints may test anonymously. |
| `rest-api.logs.request` | **WPE PRODUCT/POLICY** | Metadata-first (request ID, endpoint revision, principal summary, status, duration, bytes, rate outcome); debug payload off by default and redacted; per-class retention, cleanup job, sampling for high-volume public endpoints. |
| `rest-api.settings.defaults-caps` | **WPE PRODUCT/POLICY** | Safety caps cannot be disabled by an ordinary module editor. |
| `rest-api.permissions.builder` | **WPE PRODUCT/POLICY** | Separate capabilities for read/create/publish/test/logs/diagnostics/CORS-rate profiles and a dedicated high-risk public-write capability; runtime endpoint permission is endpoint Policy, not builder capability; AI publish/public-write/CORS-widening/destructive binding disabled by default. |
| `rest-api.events.diagnostics` | **WPE PRODUCT/POLICY** | Published/enabled/deprecated and deduped denied/rate-limited events; no event per successful request; diagnostics for missing binding, auth adapter, invalid Policy/CORS, rate-store degraded, REST disabled by host/security plugin, Pro expiry per ADR-0007. |
| `rest-api.performance.budgets` | **WPE PRODUCT/POLICY** | Long operations return a job/run resource instead of holding the request; no N+1 embeds; runtime endpoints add no frontend assets (§40). |
| `rest-api.scope.multisite-portability` | **WPE PRODUCT/POLICY** | Per-site route scope via get_rest_url substrate; package movement delegated to Surface 26 Import/Export with dependency refs and conflict/deprecation mapping. |
| `rest-api.safety.raw-execution` | **REJECTED-UNSAFE CONFIRMED** | Rejected: market no-code SQL endpoint builders are not evidence to accept raw SQL/PHP/filesystem execution or client-controlled orderby identifiers. |
| `rest-api.safety.missing-permission-callback` | **REJECTED-UNSAFE CONFIRMED** | Rejected: WordPress itself warns on missing permission_callback; every WPE endpoint needs explicit policy. |
| `rest-api.safety.static-bearer-default` | **REJECTED-UNSAFE CONFIRMED** | Rejected as a default; stronger adapters only through certified Surface 23/auth adapters after security review. |
| `rest-api.safety.sensitive-header-mapping` | **REJECTED-UNSAFE CONFIRMED** | Rejected: credentials/proxy headers are never dynamic user-mappable inputs. |
| `rest-api.safety.unbounded-output` | **REJECTED-UNSAFE CONFIRMED** | Rejected: request bodies never map blindly; responses are explicit, bounded and Policy-filtered. |
| `rest-api.safety.cors-wildcard` | **REJECTED-UNSAFE CONFIRMED** | Rejected: exact origin list only. |
| `rest-api.safety.shared-personalized-cache` | **REJECTED-UNSAFE CONFIRMED** | Rejected: cross-user leakage risk. |
| `rest-api.safety.public-write-toggle` | **REJECTED-UNSAFE CONFIRMED** | Rejected: public write requires dedicated capability, explicit high-risk confirmation and abuse model. |
| `rest-api.safety.sensitive-logging` | **REJECTED-UNSAFE CONFIRMED** | Rejected: metadata-first logs and synthetic/redacted doc examples only. |
| `rest-api.safety.test-impersonation` | **REJECTED-UNSAFE CONFIRMED** | Rejected. |

## Native completeness / unresolved items

No additional REST-specific V1 Bank family is required. Native unresolved items: **0**. Idempotency, versioning/deprecation, rate limiting, logs, response caching and docs export have no native WordPress equivalent and remain WPE/market-owned.

## Gate boundary

Not promoted here: endpoint registration, runtime, Atomic Option Contracts, UX contract closure or product parity.
