# ADR-0054 — Remote Service Resource and Trust Boundaries

Status: **Accepted architecture / service implementation pending**  
Date: 2026-08-27

## Context

WPEssential optionally communicates with WPE-controlled services for account linking, site activation, commercial entitlements, plan/catalog display, support, documentation and release information.

These resources do not all carry the same trust semantics. In particular:
- account API authentication does not prove a Pro package is authentic;
- plan/catalog data does not grant local Pro entitlements;
- REST/TLS delivery does not replace the signed entitlement verifier;
- a `latest_version` REST field cannot authorize executable Pro update installation;
- support content must not become executable wp-admin content.

## Decision

WPEssential remote services use explicitly separated logical resource domains:

1. Account Summary
2. Site Connection / Activation
3. Signed Product Entitlement + root-authorized signer keyset
4. Plans / Catalog
5. Support Ticket / Message / Attachment / Diagnostics
6. Documentation metadata/search
7. Changelog / release-note metadata
8. Public service status
9. Pro executable update metadata as a **separate TUF trust system**

The eventual HTTP API is versioned and schema-defined. RFC 9457 Problem Details is the baseline error representation.

## Trust rules

- OAuth access token authenticates/scopes a service request; it is not a product entitlement signature.
- Signed entitlement remains locally verified under ADR-0042.
- TUF metadata remains executable update authority under ADR-0044.
- Plan/catalog/docs/support responses are data, never remote PHP/JS/CSS executable payloads.
- Support rich content, if any, is locally rendered from a documented safe representation.
- Attachment upload/download targets are short-lived/private and treated as secrets where preauthenticated.
- Free plugin remains locally useful if all WPE remote services are unavailable.

## API rules

- explicit major API contract version;
- OpenAPI-compatible schema contract planned;
- standard HTTP statuses;
- RFC 9457 `application/problem+json` errors;
- opaque resource IDs;
- UTC timestamps;
- bounded cursor pagination where appropriate;
- idempotency keys for retryable create/write operations;
- request correlation IDs;
- bounded retries/backoff and `Retry-After` handling;
- secrets never in ordinary logs/browser bootstrap.

## Consequences

Positive:
- account/service compromise does not automatically become software-update trust compromise;
- API clients can be generated/validated later without inventing per-endpoint error formats;
- remote plan/docs/support content cannot silently become code execution;
- offline/local Free behavior stays independent;
- support and commercial domains can evolve without redefining update cryptography.

Cost:
- more explicit schemas and trust-domain testing;
- separate TUF repository/client operations;
- signed entitlement/keyset interoperability testing;
- service versioning/compatibility discipline required.

## Evidence still required

After explicit owner development consent:
- OpenAPI/schema implementation;
- OAuth/token lifecycle;
- site activation/clone transfer fixtures;
- RFC 9457 problem-type catalog;
- idempotency/retry/rate-limit tests;
- signed entitlement/keyset cross-language fixtures;
- support attachment/privacy/scanning behavior;
- TUF repository/client conformance;
- service outage/offline compatibility.

No remote service or WordPress service client implementation has been authorized/executed.
