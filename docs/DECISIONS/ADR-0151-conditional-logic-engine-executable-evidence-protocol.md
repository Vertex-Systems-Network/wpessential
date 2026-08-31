# ADR-0151 — Conditional Logic Engine Executable Evidence Protocol

Status: **Accepted planning evidence contract / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP34`

## Context

WPEssential architecture defines one shared Conditional Logic Engine used by Forms, Workflow, Admin Columns, Dashboard Widgets, Admin Menu, Notifications, Component Blueprints/Listings and other consumers. Existing module protocols verify consumer-specific behavior but do not independently certify the shared typed condition engine.

Without a shared contract, operator meaning, null/missing behavior, source resolution, authorization, caching, failure semantics and Multisite scope could drift between modules.

## Decision

Accept `docs/QUALITY/CONDITIONAL-LOGIC-ENGINE-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the canonical future executable-evidence contract for the shared Conditional Logic Engine.

The fixed evidence matrix is:

- `CLG-01…CLG-176`
- executed: **0/176**
- runtime certification: **none**

Independent certification classes remain separate:

- `CLG-D` definition/schema/revision/compiler;
- `CLG-T` type/operator/null semantics;
- `CLG-V` value/context resolution;
- `CLG-P` Policy/privacy/inference safety;
- `CLG-B` boolean groups/order/determinism;
- `CLG-G` dependency graph/cycle/budget safety;
- `CLG-C` consumer parity/action separation;
- `CLG-X` dynamic/time/remote/integration boundaries;
- `CLG-K` cache/version/invalidation;
- `CLG-F` failures/concurrency/observability;
- `CLG-O` Multisite/scale/adversarial/release regression.

## Required truth boundaries

The following remain distinct:

`Condition Definition ≠ published revision ≠ compiled predicate ≠ value resolver ≠ authorized context ≠ evaluation input ≠ evaluation result ≠ explanation trace ≠ consumer action`

Rules:

1. Condition truth never grants Capability or resource Policy.
2. UI/menu/field visibility remains presentation behavior, not authorization.
3. Missing, null, empty, zero, false, denied, unresolved and error states cannot be silently collapsed.
4. Normal conditions cannot execute arbitrary PHP, JS, shell, raw SQL or unrestricted callbacks.
5. Protected operands/explanations/results remain principal/scope/privacy bound and inference-safe.
6. Cross-consumer operator semantics remain shared; consumers may add business guards but cannot silently redefine canonical truth.
7. Query/Relation/Data Source/remote operands remain governed by their owning contracts and do not transfer broader authority into the Condition Engine.
8. Cached results include every principal/scope/version/dependency generation required for safe reuse.
9. Unknown future condition/operator schemas fail safe under VER policy.
10. Multisite target ownership is explicit; current blog context is not durable authority.

## Consequences

- Shared condition behavior now has a bounded executable evidence contract.
- Consumer protocols can reference CLG for engine semantics while retaining their own action/runtime certification.
- Exact implementation, operator registry, compiler/evaluator representation, cache backend, numeric limits and performance thresholds remain evidence-gated.
- Passing CLG does not certify Forms, Workflow, Admin Menu, Notifications, Component Blueprint or any other consumer, and consumer success does not certify CLG.

## Authorization

ADR-0014 remains the hard consent gate. This ADR grants **no runtime development, test, benchmark, provider, migration or data-mutation authorization**.