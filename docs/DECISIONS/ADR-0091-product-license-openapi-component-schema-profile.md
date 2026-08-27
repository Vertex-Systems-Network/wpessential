# ADR-0091 — Product License OpenAPI Component Schema Profile

Status: **Accepted paper API component contract / executable evidence pending**  
Date: 2026-08-28

## Context

ADR-0076 accepted Product License HTTP/OpenAPI principles, but future implementation still had field-level ambiguity around resource schemas, mutable/server-owned fields, idempotency, preconditions, pagination and conflict handling.

## Decision

Accept the paper component contract in `PRODUCT-LICENSE-OPENAPI-COMPONENT-SCHEMA-PROFILE.md` for the future v1 API.

It defines component boundaries for:
- Account Summary;
- Product Contract/Capacity Summary;
- Installation Activation;
- Network Activation;
- Site Allocation create/read/patch/release;
- Reconciliation Observation/Result;
- Site Allocation Review;
- Transfer;
- Signed Entitlement Envelope;
- Entitlement Keyset Summary;
- RFC 9457 Problem Details/Field Error;
- bounded Cursor Page;
- Idempotency-Key, ETag/If-Match, Retry-After and correlation headers.

This is not an OpenAPI YAML/JSON artifact and does not approve service persistence, routes or SDK generation.

## Server-owned state rule

Clients cannot directly set server-derived commercial authority such as:
- `production_counting`;
- arbitrary allocation/contract lifecycle state;
- another account owner;
- signed entitlement claims;
- capacity usage result.

Mutations expose only explicit allowed inputs and server returns authoritative resource state.

## Identity rule

Opaque remote IDs, local UUID continuity references, numeric WordPress site IDs and URL/domain metadata remain distinct. Domain/blog ID are mutable metadata and never sole authentication/identity.

## Concurrency/idempotency rule

Retryable commercial mutations reuse a stable Idempotency-Key after unknown outcomes. Existing mutable resources use strong ETag/If-Match preconditions where concurrency matters.

Same idempotency key with materially different normalized request is a stable conflict, not a second logical operation.

## Entitlement rule

Authenticated API transport does not make entitlement claims trusted. Signed entitlement artifact remains independently verified under ADR-0042/0017.

## Privacy rule

Reconciliation schemas are minimized. They do not require content/plugin/theme/customer/member inventory or disclose another customer's site/account details during conflict resolution.

## Compatibility rule

Unknown newer state/conflict semantics must degrade safely and never be interpreted optimistically as `active`. Breaking required semantic changes require explicit API/profile version evolution.

## Evidence still required

After explicit owner consent:
- actual OpenAPI encoding + lint/schema validation;
- OAuth scopes;
- exact string/length/format constraints;
- enum compatibility;
- idempotency retention/body comparison;
- ETag/precondition tests;
- pagination cursor tests;
- RFC 9457 conformance;
- allocation/release/transfer races;
- privacy/resource-enumeration tests;
- signed entitlement verification integration.

Executed Product License API fixtures: **0**.

## Development gate

This ADR authorizes no OpenAPI YAML/JSON, route, OAuth client, service DB, mock server, SDK, API call or contract test. ADR-0014 explicit owner consent remains required.