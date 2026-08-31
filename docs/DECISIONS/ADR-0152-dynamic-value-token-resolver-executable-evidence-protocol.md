# ADR-0152 — Dynamic Value / Token Resolver Executable Evidence Protocol

Status: **Accepted planning evidence contract / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP35`

## Context

WPEssential architecture defines one shared token/value resolver supplying dynamic data to listings, dashboards, columns, emails, notifications, forms, builder adapters and other consumers. Existing Data Source, Field Storage, Relations, Query, Conditional Logic, Component Blueprint and consumer protocols verify their own ownership/runtime behavior but do not independently certify the shared resolver boundary.

A shared resolver needs explicit evidence for provider identity, typed resolution, source-owner authorization, formatting versus escaping, output-context safety, privacy, dependency/budget behavior, batching, cache invalidation, versioning, cross-consumer parity and Multisite scope.

## Decision

Accept `docs/QUALITY/DYNAMIC-VALUE-TOKEN-RESOLVER-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the canonical future executable-evidence contract for the shared Dynamic Value / Token Resolver.

The fixed evidence matrix is:

- `DVR-01…DVR-176`
- executed: **0/176**
- runtime certification: **none**

Independent certification classes remain separate:

- `DVR-D` descriptor/provider registration/versioning;
- `DVR-S` source resolution/source-owner boundaries;
- `DVR-T` canonical type/cardinality/null semantics;
- `DVR-P` Policy/privacy/secret/inference safety;
- `DVR-E` formatting/escaping/output-context safety;
- `DVR-G` dependency graph/cycles/budgets/batching;
- `DVR-K` cache identity/invalidation/version behavior;
- `DVR-C` cross-consumer parity;
- `DVR-X` remote/time/locale/media/integration boundaries;
- `DVR-F` failure/concurrency/observability;
- `DVR-O` Multisite/scale/adversarial/release regression.

## Required truth boundaries

The following remain distinct:

`source definition ≠ source value ≠ resolved canonical value ≠ formatted value ≠ escaped value ≠ trusted markup ≠ rendered consumer output ≠ cached representation`

Rules:

1. Resolver success never grants downstream action authorization.
2. Source-owner DSR/Field/Relation/Query/Settings/Membership/media/remote Policy remains authoritative.
3. Missing, null, empty, zero, false, denied, unresolved and error states remain distinct where required.
4. Generic dynamic values cannot reveal Vault secrets/passwords/reset/session tokens/private keys or other prohibited security data.
5. Formatting is separate from canonical value; escaping is explicit per HTML text/attribute/URL/JSON/email/plain/other approved context.
6. Ordinary strings are never implicitly trusted HTML/JS/CSS.
7. Normal providers cannot execute arbitrary PHP, JS, shell, raw SQL or unrestricted callbacks.
8. Cache reuse includes every principal/scope/source/version/locale/time dependency needed for safe correctness.
9. Consumer-specific formatting may differ but cannot redefine source truth or access authority.
10. Multisite target ownership is explicit; current blog/request context is not durable source ownership.

## Consequences

- Shared dynamic-value behavior now has a bounded non-duplicative executable evidence contract.
- Consumer protocols may reference DVR for common resolution/escaping/cache semantics while retaining their own rendering/action certification.
- Exact implementation, provider registry representation, formatter set, cache backend, numeric budgets and performance thresholds remain evidence-gated.
- Passing DVR does not certify DSR/FST/REL/QRY/CLG/CBP/Emails/Notifications/Forms/builders, and those consumer/owner protocols do not certify DVR.

## Authorization

ADR-0014 remains the hard consent gate. This ADR grants **no runtime development, renderer/provider execution, test, benchmark, migration or data-mutation authorization**.