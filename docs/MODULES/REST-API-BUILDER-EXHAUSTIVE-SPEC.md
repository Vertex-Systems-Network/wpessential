# WPEssential — REST API Builder Exhaustive Option Specification

Status: **Phase 0 exhaustive product specification / no implementation authorized**

## 1. Product boundary

REST API Builder creates typed WordPress REST endpoints over approved WPE/WordPress data/actions.

It is not:
- an unauthenticated SQL tunnel;
- a generic PHP callback executor;
- a switch that bypasses WordPress permissions;
- an OAuth server implementation by default.

Every endpoint uses explicit `permission_callback` semantics and shared WPE Policy.

---

# 2. Screens

- Endpoints
- Create/Edit Endpoint
- API Keys/Auth Integrations shortcut
- Request Logs
- Rate Limits
- Documentation / Schema Export
- Settings
- Diagnostics

---

# 3. Endpoints list

Columns:
- Name
- Key
- Status
- Method
- Namespace/version
- Route
- Binding type
- Auth mode
- Policy summary
- Rate-limit profile
- cache state
- last request
- error rate/health
- updated
- actions

Filters:
- enabled/disabled/draft
- method
- namespace/version
- auth mode
- public/authenticated
- read/write/destructive
- has errors
- owner/module

Actions:
- Edit
- Test
- Copy route
- View docs
- View logs
- Duplicate
- Enable/Disable
- Revisions
- Export
- Archive/Delete

---

# 4. Endpoint identity

Fields:
- Name required
- Key stable generated
- Description
- Status Draft default / Enabled / Disabled / Archived
- Namespace e.g. `wpessential-app`
- Version segment e.g. `v1`
- Route path pattern
- HTTP method(s)

Validation:
- namespace/route grammar
- reserved collision detection
- duplicate method+route conflict
- path parameter names unique

Changing stable public route/version after publish requires breaking-change warning and deprecation option.

---

# 5. HTTP methods

Supported according to binding/action semantics:
- GET
- POST
- PUT/PATCH
- DELETE

HEAD/OPTIONS behavior follows WordPress/server automatically where appropriate; custom semantics only if needed.

Endpoint UI labels operation class:
- Read
- Write
- Destructive

GET must not perform state-changing business action.

---

# 6. Binding types

- Query Builder read endpoint
- Data Source entity get/list
- CRUD Ability/action
- Workflow trigger/run
- registered WPE Ability
- custom SDK REST provider

Not allowed:
- raw SQL typed into endpoint
- arbitrary PHP
- arbitrary filesystem path read/write

---

# 7. Route parameters

For each path parameter:
- name
- type
- required implicit yes
- validation
- pattern/range
- mapping to binding input
- description/example

Examples:
- integer ID
- UUID
- slug

Route parameter is data, not authorization. Object-level Policy rechecks resolved resource.

---

# 8. Query parameters

Fields per parameter:
- name
- type: string/integer/number/boolean/array/object where supported
- required
- default
- enum/options
- min/max
- min/max length
- pattern
- explode/array parsing semantics
- description
- example
- mapping
- sensitive flag

Reserved/global WordPress REST parameters must not be accidentally shadowed without explicit compatibility handling.

---

# 9. Request headers

Allowlisted custom header inputs:
- header name
- type/validation
- required
- map to action input

Do not expose Authorization/Cookie/internal proxy headers as arbitrary user-mappable dynamic fields.

Idempotency header can be first-class for suitable writes.

---

# 10. Request body

Modes:
- JSON object — primary
- form/multipart only where file upload endpoint explicitly supports
- no body

JSON schema editor UI:
- property name
- type
- required
- default
- validation
- nested object/array
- max depth/items
- enum
- format: email/url/date/etc.
- description/example
- sensitive classification
- mapping to binding input

Reject unknown properties option:
- strict — default candidate for write endpoints
- allow documented additional properties only when target schema explicitly supports.

Mass assignment never maps entire request body blindly into model.

---

# 11. File upload body

Only registered file input schema.

Options:
- field name
- max files
- each/total bytes
- MIME/extension allowlist
- private/public storage policy
- target action

WordPress upload/security policies apply; no generic arbitrary server path.

---

# 12. Authentication modes

## Same-site cookie + REST nonce
Default for wp-admin/frontend authenticated WPE application calls.

## WordPress Application Passwords
Baseline external machine/client authentication where HTTPS and WordPress support apply.

## Public/anonymous
Allowed only when explicit public endpoint semantics + `permission_callback` policy exist. “Public” is not a missing callback.

## Registered OAuth/JWT/custom adapter
Future/certified auth adapter after separate security review.

Endpoint Builder itself does not invent weak static bearer tokens as default authentication.

---

# 13. Authorization / permission callback

Every endpoint config requires explicit policy mode:
- public read explicitly allowed
- authenticated user
- required capability
- WPE Resource Policy
- owner/current-user relation
- Membership entitlement + outer capability where relevant
- custom registered Policy provider

Order:
1. authentication/context
2. site/network boundary
3. required capability
4. resource Policy
5. Membership protection where resource requires it
6. request validation/business guards

No React/frontend-only authorization.

---

# 14. Public endpoint safeguards

For public endpoints configure:
- allowed method read/write
- rate-limit profile
- CAPTCHA/anti-abuse adapter where relevant
- payload/result limits
- data-field allowlist
- cache policy
- PII/private field prohibition unless explicit secure use case
- enumeration risk warning

Public write endpoint requires explicit high-risk confirmation and abuse model; cannot be enabled by one casual checkbox that removes authorization.

---

# 15. Data output selection

Response schema is explicit.

For each field:
- output key
- source field/token
- type
- nullable
- formatter
- nested structure
- include condition
- permission/sensitivity classification

Never return full WP object/user/meta structure by default.

Protected/internal fields require explicit allowlisting + policy.

---

# 16. Collection pagination

Modes:
- WordPress page/per_page style
- cursor where Data Source supports stable cursor

Options:
- default page size
- max page size
- total count include yes/no based on cost
- max offset warning/limit
- order/orderby allowlist

No unbounded `per_page=all`.

Align with WordPress REST expectations where practical, including pagination headers for page-based collections.

---

# 17. Filtering/sorting

Only predefined safe filter parameters mapped into Query AST/Data Source schema.

Each filter:
- parameter
- target field/condition
- operator allowlist
- value type
- max list items

Sorting:
- allowlisted sort keys
- default sort
- direction

Never map arbitrary client `orderby` to raw SQL identifiers.

---

# 18. `_fields` / sparse fields

Candidate support:
- allow WordPress-style sparse field selection only from endpoint's already-allowed response schema;
- never use `_fields` to reveal hidden fields;
- disabled if endpoint response semantics require fixed payload.

Sparse selection may reduce expensive field computation.

---

# 19. Embedding/relations

Explicit relation/embed definitions:
- relation key
- allowed nested fields
- max depth
- max related rows
- permission for related objects

Avoid generic recursive embed that creates N+1 or leaks related private data.

---

# 20. Action/Ability binding

Map request params/body/context to Ability input schema.

Options:
- static values
- path/query/body mappings
- authenticated principal context
- current site/network context
- registered secret reference only when Ability accepts it internally

Ability output maps through endpoint response schema.

Endpoint cannot override Ability's own permission callback with weaker policy.

---

# 21. CRUD semantics

Create:
- field allowlist
- required fields
- response status/Location behavior candidate

Update:
- resource selector
- optimistic version/ETag/precondition candidate
- partial vs full update semantics

Delete:
- trash vs permanent
- dependency/cascade policy
- confirmation is client UX; server capability remains required
- idempotent delete semantics documented

---

# 22. Idempotency

For suitable write endpoints:
- Require/accept `Idempotency-Key`
- scope: endpoint + authenticated principal + key
- retention window
- request hash mismatch on reused key → conflict
- cached prior result/reference

Especially needed for:
- create
- external side-effect workflow
- membership changes
- payment-adjacent provider operations

Do not blindly retry non-idempotent writes without key.

---

# 23. Rate limiting

Profiles:
- None only for low-risk authenticated internal cases
- Standard
- Strict public
- Custom bounded

Dimensions:
- authenticated user/application
- IP with trusted proxy rules
- endpoint
- site/network
- API credential/application password fingerprint where safe

Fields:
- requests
- window
- burst candidate
- response headers
- retry-after

Storage/algorithm implementation remains security/performance evidence work.

---

# 24. CORS

Default:
- same-origin / no permissive cross-origin headers unless endpoint needs them.

Options:
- allowed origins exact list
- methods
- headers
- credentials yes/no
- max age

Rules:
- no `*` with credentials
- validate scheme/host/port
- never mirror arbitrary Origin automatically
- preflight semantics tested

CORS does not replace authentication/authorization.

---

# 25. Response cache

Options:
- disabled default for personalized/protected writes
- public cache
- private/user-specific cache

Fields:
- TTL
- cache key dimensions
- invalidation events
- ETag/Last-Modified candidate

Do not cache permission-dependent response under shared key that leaks one user's data to another.

---

# 26. HTTP response status

Binding maps normalized outcomes:
- 200/201/204 success semantics
- 400 validation
- 401 unauthenticated
- 403 forbidden
- 404 intentionally not-found/resource hiding where policy
- 409 conflict/idempotency/version
- 413 payload too large
- 422 semantic validation candidate if API profile chooses
- 429 rate limited
- 5xx safe server/integration failures

Use consistent WPE error envelope.

---

# 27. Error envelope

Fields candidate:
- stable error code
- safe message
- HTTP status
- field errors where validation
- correlation ID
- retryable boolean/hint
- documentation link ID optional

Never expose:
- SQL
- stack trace
- filesystem path
- secrets
- provider credentials

---

# 28. Versioning/deprecation

Endpoint version belongs in namespace/contract.

Breaking changes require:
- new version/route or accepted migration strategy
- deprecation date/status
- usage telemetry only local logs unless opt-in remote telemetry
- replacement endpoint link/docs

Old endpoint can remain enabled during migration window.

No silent breaking schema change to published endpoint.

---

# 29. Endpoint documentation

Generate schema-driven docs:
- method/path
- auth
- parameters
- request body
- response schema
- errors
- examples
- rate limits
- deprecation

OpenAPI-like export candidate after schema stabilizes.

Examples contain synthetic/redacted values, not real secrets/user data.

---

# 30. Test console

Admin-only tool:
- choose authenticated current user/application context where safe
- fill params/body
- dry-run Ability if supported
- send test request
- show status/headers/body/timing
- policy trace safe summary

Cannot impersonate arbitrary higher-privilege user without dedicated test-as capability/re-auth.

Public endpoint test can run anonymous context.

---

# 31. Request logs

Default metadata:
- request ID
- endpoint/revision
- time
- principal type/ID safe summary
- method
- status
- duration
- response bytes
- rate-limit outcome
- error category

Optional sensitive debugging payload logging is off by default and bounded/redacted.

Do not store Authorization headers, cookies, Application Passwords or raw P3 data.

---

# 32. Log retention

- successful request metadata retention
- error retention
- security/rate-limit event retention
- no body retention default
- cleanup job

High-volume public endpoints require sampling/aggregation option for non-security success logs.

---

# 33. Endpoint disable/archive/delete

Disable:
- route returns safe unavailable/not found according policy
- no data deletion

Archive:
- same as disabled + historical definition retained

Delete:
- dependency/usage/log impact
- published public endpoint warning
- logs retained per policy

---

# 34. Settings

- default namespace prefix
- default API version
- max request body bytes
- max collection page size
- default rate profile
- CORS default none
- request log retention
- slow request threshold for diagnostics
- public endpoint maximum execution budget
- documentation export settings
- deprecated endpoint warning window

Safety caps cannot be disabled by ordinary module editor.

---

# 35. Permissions

Separate:
- read endpoints
- create/update/delete/publish
- test console
- view logs
- view sensitive diagnostics
- manage CORS/rate profiles
- create public write endpoint — dedicated high-risk capability candidate
- manage auth adapters delegated appropriately

Runtime endpoint permission is endpoint Policy, not admin-builder capability alone.

---

# 36. Abilities

Builder management:
- endpoint list/get/create/update/validate/publish/enable/disable/archive/export
- docs generate
- test
- logs list/get

Runtime endpoint may bind to existing Ability; it does not need to create a second untyped execution path.

AI default exposure:
- list/get/explain/schema/validate
- draft endpoint creation opt-in
- publish/public-write/CORS widening/destructive binding disabled by default.

---

# 37. Events

- endpoint published/updated/enabled/disabled/deprecated
- request denied/rate-limited only security/audit class with dedupe
- endpoint repeated failure/unhealthy transition

Do not emit every successful high-volume request as expensive generic workflow event by default.

---

# 38. Empty/error/degraded

- missing bound Query/Ability
- auth adapter missing
- Policy invalid
- CORS invalid
- schema incompatible after imported dependency
- rate-limit store degraded
- Pro expired → builder read-only; existing safe deployed endpoint runtime follows ADR-0007, with security policies continuing safely
- REST API disabled by hosting/security plugin conflict diagnostics

---

# 39. Performance

- request body/depth limits
- page-size caps
- Query cost budget
- no N+1 embeds
- response byte ceiling candidate
- timeout budget
- `_fields`/sparse computation optimization where safe
- cache only permission-safe responses
- async long operations return job/run resource rather than hold HTTP request
- logs batched/trimmed

---

# 40. Assets

REST Builder admin editor assets only WPE REST screens.
Runtime endpoints add no frontend JS/CSS.
Test console assets only when open.

---

# 41. Future tests

After consent:
- missing permission callback impossible
- public vs authenticated policy
- cookie+nonce CSRF
- Application Password external auth
- object-level IDOR
- mass assignment
- nested schema limits
- file upload attacks
- pagination/order allowlist
- `_fields` cannot reveal hidden field
- relation embed policy/N+1
- rate-limit race/proxy
- CORS credential wildcard rejection
- idempotency duplicate/mismatch
- cache cross-user leakage regression
- response error redaction
- route version deprecation
- disabled/missing dependency
- Pro expiry safe runtime/security behavior

## Maturity

REST API Builder is now **Exhaustive option spec** at Phase 0 product level. Exact auth adapters, rate limiter, runtime endpoint compiler and performance tests remain technical/consent-gated.