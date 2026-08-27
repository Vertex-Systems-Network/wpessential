# WPEssential — Product License OpenAPI Candidate Contract

Status: **Phase 0 paper API contract / no server or client implementation authorized**  
Date: 2026-08-28  
Related: ADR-0014, ADR-0017, ADR-0034, ADR-0042, ADR-0054, ADR-0060, ADR-0070, ADR-0072, ADR-0075.

## 1. Purpose

Translate the accepted Product License remote resource/state model into an implementation-neutral HTTP/OpenAPI candidate contract so future service/client work starts from reviewed resource semantics rather than inventing endpoints while coding.

This is **not** an OpenAPI file and does not authorize a service deployment or API call.

## 2. API design principles

- resource-oriented URLs;
- versioned API namespace;
- opaque resource IDs;
- authenticated Account context separate from local WordPress authorization;
- signed Product Entitlement remains separate cryptographic artifact;
- mutable resources expose strong resource version/ETag semantics;
- state-changing retryable operations accept a stable application idempotency key;
- stale mutation preconditions fail instead of last-write-wins;
- errors use RFC 9457 Problem Details-compatible shape;
- machine problem/error code is stable and separate from localized human detail;
- pagination bounded/cursor-based for collection resources;
- no endpoint returns secrets unnecessarily;
- no hidden WordPress site/plugin/content inventory.

## 3. Base namespace

Paper form:

`/v1/...`

Major API namespace version changes only for incompatible contract changes. Resource schema/version fields can evolve compatibly within the major API where policy allows.

Exact hostnames are deployment configuration and not persisted as identity.

## 4. Authentication boundary

Authenticated service requests use the Account-link/OAuth architecture from ADR-0034.

Client does not send:
- WPE account password;
- WordPress admin password;
- Pro signing private keys;
- Backup recovery secrets;
- third-party provider secrets unrelated to licensing.

OAuth token storage stays Vault/P3.

## 5. Local WordPress authorization boundary

Before a local WordPress user triggers a remote mutation, the plugin checks:
- current WordPress authenticated actor;
- target site/network capability;
- WPE Policy/Ability;
- local scope;
- destructive/risk confirmation where applicable.

Remote Account authorization does not replace local target-site/network authorization.

## 6. Resource families

### 6.1 Account summary
Candidate:

`GET /v1/account`

Returns only client-useful commercial identity summary.

Candidate fields:
- `id`;
- `status`;
- safe display label;
- organization/team summary if relevant;
- locale/support metadata if required;
- `resource_version`/ETag.

No site inventory by default.

### 6.2 Product Contracts

`GET /v1/product-contracts`

`GET /v1/product-contracts/{contract_id}`

Candidate fields:
- contract ID;
- product/tier;
- lifecycle state;
- trial/start/end/grace dates where applicable;
- allocation policy reference/version;
- environment allowances;
- support/update rights;
- safe capacity summary;
- feature-entitlement profile reference;
- resource version.

No card/payment credentials.

### 6.3 Installation Activations

`POST /v1/installations`

`GET /v1/installations/{installation_id}`

`PATCH /v1/installations/{installation_id}`

Candidate create input:
- local installation UUID;
- declared environment class;
- optional canonical URL metadata;
- WordPress/platform version profile required for compatibility only if policy says and disclosed;
- explicit contract binding/selection where needed.

Do not upload plugin/theme/content inventory.

### 6.4 Network Activations

`POST /v1/installations/{installation_id}/networks`

`GET /v1/networks/{network_activation_id}`

`PATCH /v1/networks/{network_activation_id}`

Candidate fields:
- network activation ID;
- installation ID;
- allocation mode;
- policy profile/version;
- safe network metadata;
- lifecycle state;
- resource version.

### 6.5 Site Allocations

`POST /v1/networks/{network_activation_id}/site-allocations`

Single-site installation can use an equivalent installation-scoped collection if no Network Activation exists.

`GET /v1/site-allocations/{allocation_id}`

`PATCH /v1/site-allocations/{allocation_id}`

`POST /v1/site-allocations/{allocation_id}/release`

Candidate fields:
- allocation ID;
- installation/network activation references;
- stable local site UUID/reference metadata;
- current WordPress numeric site ID as mutable metadata where Multisite;
- environment class;
- allocation lifecycle state;
- production-counting status as derived server fact;
- safe current URL/domain metadata;
- lineage/source allocation reference where applicable;
- conflict code;
- resource version/ETag;
- timestamps.

### 6.6 Site Allocation reconciliation

Candidate:

`POST /v1/site-allocations/{allocation_id}/reconcile`

Input describes only relevant local observed identity/state facts; server returns authoritative reconciliation result.

Use cases:
- unknown timeout result;
- restored stale database;
- domain/host migration;
- clone review;
- deleted/recreated site;
- remote-success/local-persist-failure;
- ownership transfer completion.

Reconciliation must not reveal another customer's private binding details.

### 6.7 Clone/environment classification

Prefer mutations on the Site Allocation resource where possible rather than command proliferation.

If workflow resource is needed, candidate:

`POST /v1/site-allocation-reviews`

Reasons:
- `staging_clone`;
- `development_clone`;
- `migration`;
- `disaster_recovery`;
- `possible_production_clone`.

Review has its own state/version/expiry when human/service approval is not immediate.

### 6.8 Transfers

Long-running ownership/network transfer can be an explicit resource:

`POST /v1/transfers`

`GET /v1/transfers/{transfer_id}`

`POST /v1/transfers/{transfer_id}/complete`

`POST /v1/transfers/{transfer_id}/cancel`

Candidate fields:
- transfer ID;
- source Account/Installation/Network/Site Allocation refs where permitted;
- target Account/Installation/Network refs;
- transfer type;
- state;
- overlap/expiry policy;
- resource version;
- safe conflict summary.

Transfer resource never carries Vault plaintext or site content.

## 7. Signed Product Entitlement endpoint

Candidate read:

`GET /v1/entitlements/current?scope=...`

or a binding-specific resource URL returned from activation/allocation response.

Response contains:
- signed canonical entitlement artifact;
- keyset/signing-profile references;
- safe verification metadata.

Important:
- authenticated API transport is not the trust basis for entitlement claims;
- client verifies ADR-0042 signature/canonicalization/binding/freshness;
- ordinary API `active=true` cannot substitute for signature verification.

## 8. Keyset metadata

Public verification metadata can be fetched from a separate trusted resource family, potentially unauthenticated if security design permits:

`GET /v1/entitlement-keysets/{keyset_id}`

Keyset rotation/root authorization follows ADR-0042. Exact distribution/trust bootstrap remains evidence-gated.

## 9. Idempotency

State-changing operations likely to be retried accept:

`Idempotency-Key: <opaque-client-operation-id>`

Candidate applicable operations:
- create Installation;
- create Network Activation;
- allocate site;
- release site;
- create review/transfer;
- complete/cancel transfer;
- explicit reconciliation mutation.

Server stores enough operation/result identity for a bounded policy window so identical retries do not consume duplicate allocations.

Rules:
- key scope includes authenticated Account + operation route/resource class;
- same key + materially different request body returns conflict/error;
- client persists operation key across unknown-outcome retries;
- idempotency cache/record retention is explicit and documented;
- key is not treated as authorization credential.

Exact header name/retention implementation remains service evidence.

## 10. Optimistic concurrency

Mutable resource responses expose an `ETag` or equivalent opaque strong version.

State-changing mutation of an existing allocation/transfer/review sends:

`If-Match: <etag>`

If resource changed, server rejects stale mutation with HTTP precondition/conflict semantics and a stable problem code.

Use cases:
- two admins allocate/release last seat;
- transfer conflicts;
- ownership change;
- environment reclassification;
- contract policy changed while stale admin screen open.

Client then re-fetches/reconciles; it does not silently retry against new state without re-evaluating intent.

## 11. Capacity mutation semantics

`GET` capacity summary is advisory UX.

Only successful authoritative allocation mutation + later signed entitlement determine rights.

Concurrent last-seat requests:
- at most policy-allowed number become active/reserved;
- losers receive stable capacity conflict;
- retry with same idempotency key returns same prior logical outcome.

## 12. Collection pagination

Collections such as Contracts/Site Allocations/Transfers use bounded cursor pagination.

Candidate query parameters:
- `limit` bounded by server maximum;
- opaque `cursor`;
- explicit state/environment filters;
- target network/installation only where actor/account has access.

Do not expose arbitrary account-wide site search solely for convenience if not needed.

## 13. Problem Details

Errors follow RFC 9457-compatible `application/problem+json`.

Core fields:
- `type`;
- `title`;
- `status`;
- `detail` safe for end user;
- `instance`/correlation reference where useful.

WPE extensions:
- `code` stable machine code;
- `retryable` boolean where meaningful;
- `field_errors` for validation where one problem type supports it;
- `current_resource_version` only if safe;
- `support_correlation_id` safe opaque value.

Never expose stack traces, database IDs of another customer, tokens, entitlement signatures/private metadata or internal service topology.

## 14. Candidate error codes

Validation/auth:
- `invalid_request`;
- `account_auth_required`;
- `account_forbidden`;
- `local_scope_reauthorization_required` (client-side mapping, not necessarily remote error).

Contract/allocation:
- `contract_not_active`;
- `allocation_limit_exceeded`;
- `environment_not_allowed`;
- `allocation_already_bound`;
- `allocation_not_found`;
- `allocation_conflict`;
- `allocation_release_pending`.

Concurrency/idempotency:
- `resource_version_mismatch`;
- `idempotency_key_reused_with_different_request`;
- `operation_in_progress`;
- `operation_result_unknown_reconcile`.

Clone/transfer:
- `possible_production_clone`;
- `staging_approval_required`;
- `transfer_conflict`;
- `transfer_expired`;
- `source_not_released`;
- `target_not_authorized`.

Entitlement:
- `entitlement_binding_mismatch`;
- `entitlement_revalidation_required`;
- `entitlement_revoked`;
- `entitlement_rollback_detected`.

## 15. HTTP status direction

Paper mapping only:
- 400 malformed/schema invalid;
- 401 missing/invalid service auth;
- 403 authenticated but not allowed;
- 404 resource unavailable in caller's authorized view;
- 409 domain conflict/idempotency body conflict where appropriate;
- 412 stale `If-Match` precondition;
- 422 semantic validation where the service contract chooses it consistently;
- 429 rate limit;
- 5xx safe service failure.

Exact mapping is future OpenAPI evidence. Security-sensitive 403/404 choices may deliberately avoid resource enumeration.

## 16. Retry policy

Client retries only operations classified retry-safe.

Network/5xx/429 handling:
- honor `Retry-After` where supplied;
- exponential/backoff policy bounded;
- state-changing retry uses same idempotency key;
- unknown outcome reconciles before issuing a new logical operation;
- non-idempotent/non-compensatable operation is never blindly retried with a new key.

## 17. Rate limits

Rate limit dimensions can include:
- Account;
- installation;
- resource/operation class;
- abuse/security state.

Admin UI should show retry-safe actionable state, not expose provider/internal limits unnecessarily.

## 18. Audit/correlation

Request may carry safe client correlation ID distinct from idempotency key.

Service/client audit correlates:
- local WordPress actor ID (local log only unless remote needs a pseudonymous operation actor reference);
- local Ability/action;
- remote Account/Installation/Allocation;
- idempotency operation ID;
- server correlation/Problem `instance`;
- signed entitlement version.

Do not transmit WordPress username/email merely for correlation unless explicit service purpose requires it.

## 19. Data minimization

Remote request body contains only fields needed for licensing/reconciliation.

Explicitly excluded by default:
- post/page/content data;
- customer/member data;
- WordPress passwords;
- complete plugin/theme list;
- database tables;
- analytics/traffic;
- unrelated site settings;
- server file paths;
- arbitrary diagnostics.

Diagnostics remain separate consented Support flow under ADR-0060.

## 20. Disconnect/delete distinction

Potential client actions:
- Disconnect Account link: revoke/remove local OAuth credentials and stop remote management where safe;
- Release Allocation: commercial allocation mutation;
- Transfer ownership: dedicated workflow;
- Delete account/data: service policy/privacy operation.

These are not one endpoint or one UI action.

## 21. Offline behavior

No local cached collection response can manufacture rights.

When service is unavailable:
- use last verified signed entitlement under its freshness/offline rules;
- mark service state unavailable;
- block remote allocation/transfer mutations;
- preserve public safe deployed output under ADR-0007;
- do not reclassify outage as expiry.

## 22. OpenAPI artifact requirements after consent

Future actual OpenAPI document must define:
- version/server policy;
- OAuth security scheme(s);
- every request/response schema;
- enums/state machines;
- header semantics;
- idempotency requirements;
- ETags/preconditions;
- pagination;
- RFC 9457 problem schemas;
- rate-limit/retry headers;
- examples containing no secrets;
- schema compatibility/deprecation policy;
- data classification annotations/documentation where useful.

Generated SDKs are optional and require dependency/licensing/security review; OpenAPI does not force code generation.

## 23. Future evidence — NOT AUTHORIZED

After explicit owner service/development consent:
- OpenAPI lint/schema validation;
- client/server contract tests;
- OAuth scopes;
- idempotency same-body/different-body behavior;
- lost response + retry;
- ETag stale-write conflict;
- last-seat allocation race;
- release/reallocate race;
- cursor pagination;
- rate limit/Retry-After;
- RFC 9457 compatibility;
- resource enumeration resistance;
- clone/transfer conflict privacy;
- remote-success/local-persist-failure;
- signed-entitlement response verification;
- service outage/offline cache;
- no hidden telemetry/site inventory.

Executed API fixtures: **0**.

## 24. Development gate

No OpenAPI YAML/JSON server implementation, route, OAuth client, service DB, SDK, API call, mock server or contract test is authorized by this document. ADR-0014 explicit owner consent remains mandatory.
