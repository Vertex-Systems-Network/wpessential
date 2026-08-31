# ADR-0076 — Product License HTTP/OpenAPI Contract Principles

Status: **Accepted paper API architecture / service implementation evidence pending**  
Date: 2026-08-28

## Context

ADR-0070/0072 define Product License identity/resources/state/conflicts. A future service needs a stable HTTP/OpenAPI contract before implementation so licensing mutations do not rely on ad-hoc command endpoints, last-write-wins or ambiguous retry behavior.

## Decision

The future Product License service uses resource-oriented versioned HTTP contracts for:
- Account;
- Product Contracts;
- Installation Activations;
- Network Activations;
- Site Allocations;
- allocation reviews/clone classification where required;
- Transfers;
- signed Product Entitlement/keyset retrieval.

Signed Product Entitlement remains cryptographically authoritative and is not replaced by an authenticated API response.

Mutable resources expose opaque strong version/ETag semantics. Stale state-changing requests use HTTP conditional request behavior such as `If-Match` and fail instead of silently overwriting current state.

Retryable state-changing operations use a stable application idempotency key scoped to the authenticated Account + operation/resource semantics. The same logical retry cannot consume another allocation; reusing a key with a materially different body is a conflict.

Errors follow RFC 9457-compatible Problem Details with stable WPE machine codes and safe correlation metadata.

Collections use bounded cursor pagination. Remote requests are data-minimized; no hidden WordPress content/plugin/theme/site inventory is part of licensing by default.

Service outage continues to use verified signed offline entitlement semantics; cached ordinary API JSON cannot manufacture Pro rights.

## Safety requirements

- Account service auth never substitutes for local target WordPress capability + WPE Policy.
- Allocation IDs/idempotency keys are not bearer credentials.
- OAuth tokens stay Vault/P3.
- Unknown remote mutation result is reconciled before issuing a new logical operation.
- 403/404 behavior can intentionally avoid resource enumeration.
- disconnect, allocation release, ownership transfer and privacy deletion are separate operations.
- diagnostics remain separately consented Support flow.

## Evidence still required

After explicit owner consent:
- actual OpenAPI schemas/validation;
- OAuth scopes and endpoints;
- idempotency retention/concurrency behavior;
- ETag/If-Match contract tests;
- last-seat/release races;
- pagination/rate limits/Retry-After;
- RFC 9457 problem conformance;
- enumeration/privacy tests;
- clone/transfer workflows;
- signed entitlement verification;
- outage/offline behavior;
- no hidden telemetry/site inventory.

Executed API/service fixtures: **0**.

## Development gate

This ADR authorizes no API server/client, OpenAPI implementation, route, SDK, mock service, service database, entitlement signing or network call. ADR-0014 explicit owner consent remains required.
