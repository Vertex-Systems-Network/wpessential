# ADR-0029 — Email Rendering & Delivery Architecture

Status: **Accepted architecture / renderer-provider implementation pending**  
Date: 2026-08-27

## Decision

WPEssential Email Builder uses this separation:

**Template Definition → Compiled Email Descriptor → Authorized Render Context → Email IR → HTML + Plaintext Rendered Message → Notification/Transport Delivery Attempt**.

Email templates use a restricted email component vocabulary. Arbitrary frontend builder runtime, JavaScript, PHP templates and unrestricted CSS are not canonical email execution mechanisms.

Transport acceptance is not delivery proof. `wp_mail()` or provider handoff may be recorded as accepted/processed only; Delivered requires a meaningful provider event where available.

## Why

- deterministic revision rendering;
- email-client compatibility differs from browsers;
- template editing must not control recipients/transport credentials;
- security/privacy require typed tokens and authorized context;
- delivery truth must not be overstated.

## Consequences

- exact CSS inliner/renderer library remains open;
- WordPress and third-party email overrides require certified semantic adapters;
- queued notifications pin template/layout/renderer revisions according to policy;
- tracking remains opt-in/off by default candidate;
- sensitive rendered bodies are not retained indefinitely by default.

## Evidence still required

After explicit development consent:
- renderer/inliner dependency comparison;
- mail-client fixtures;
- sanitization/header/link/attachment security tests;
- core/third-party email adapter tests;
- transport status truth tests;
- performance/bundle/license review.

Supporting architecture: `docs/ARCHITECTURE/EMAIL-RENDERING-DELIVERY-ARCHITECTURE.md`.