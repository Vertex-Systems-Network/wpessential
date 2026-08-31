# ADR-0055 — Connection and Integration Adapter Certification

Status: **Accepted architecture / executable provider certification pending**  
Date: 2026-08-27

## Context

WPEssential will connect to many external APIs, OAuth providers, webhooks, storage systems, email transports and billing systems. A successful credential test does not prove every advertised read, write or event operation is correct, secure or recoverable.

## Decision

Provider support is certified at the granularity of **adapter + provider profile + capability set + provider/API version**.

Canonical path:

`Connection Definition → Vault refs → Provider Adapter → Capability Profile → Safe HTTP/Webhook Gateway → typed Ability/Event`

Adapters cannot bypass WPE Vault, Policy, Safe HTTP, error normalization, audit/redaction or durable Job semantics where those services apply.

## Certification levels

- **I0 — Detected / Configurable**
- **I1 — Authentication Certified**
- **I2 — Read Certified**
- **I3 — Write / Action Certified**
- **I4 — Event / Reconciliation Certified**
- **I5 — Production Profile Certified**

A provider can be certified at different levels for different capability sets. `Connected` means only that the applicable I1 checks passed; it never implicitly authorizes untested writes/events.

## Security requirements

- credentials are Vault references;
- OAuth uses accepted public-client/provider profiles and least scopes;
- all outbound HTTP uses centralized Safe HTTP;
- inbound webhook business processing occurs only after required signature/replay verification;
- provider raw payloads are schema-validated before normalization;
- duplicates/out-of-order events are expected and reconciled where provider APIs permit;
- unknown outcome after outbound mutation is not assumed failure;
- secrets/signed URLs/Authorization headers are redacted;
- exact provider/API version/profile is recorded with certification.

## Backup exception

Generic Connection certification cannot certify a Backup destination. Backup providers must satisfy the stronger ADR-0053 restore-oriented certification contract.

## Marketing truth

“Supports Provider X” must internally map to the exact certified capability profile. WPE must not imply billing, write, webhook, backup or other capabilities merely because OAuth succeeds.

## Evidence still required

After explicit owner development consent:
- auth/revocation/refresh fixtures;
- scope failures;
- pagination/rate limit/malformed response tests;
- SSRF/redirect/DNS rebinding tests;
- idempotent and unknown-outcome mutation tests;
- webhook signature/replay/duplicate/order tests;
- subscription renewal/reconciliation;
- Job crash/retry;
- provider schema/version drift;
- privacy/log redaction.

No integration adapter has been implemented or certified yet.
