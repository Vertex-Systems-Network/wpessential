# ADR-0146 — Component Blueprint Core Runtime Executable Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP29`

## Context

WPEssential already has an accepted canonical Component Blueprint architecture and a separate Builder Widgets adapter-certification protocol (`BW-01…BW-50 / BC0…BC4`). The adapter protocol is intentionally builder-specific and cannot prove the correctness, security or operational behavior of the shared Component Blueprint compiler/renderer itself.

The core runtime spans Definition publication, compiled descriptors, component-instance validation, typed dynamic bindings, Policy enforcement, render primitives, slots/nesting, styles/responsive behavior, Asset Registry integration, cache isolation, accessibility, degraded behavior, portability/versioning and Multisite execution.

Without a fixed executable matrix, future successful editor or frontend demos could be mistaken for evidence that the shared runtime is secure and production-ready.

ADR-0014 remains authoritative: planning or acceptance of this protocol does not authorize runtime implementation or fixture execution.

## Decision

Adopt `docs/QUALITY/COMPONENT-BLUEPRINT-CORE-RUNTIME-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the canonical future executable-evidence contract for the Component Blueprint core runtime.

The protocol freezes **CBP-01…CBP-176**.

Current execution truth: **0/176 executed**.

No Component Blueprint runtime certification exists yet.

## Canonical runtime boundary

The accepted pipeline is:

`Component Blueprint Definition → Published Compiled Blueprint → Component Instance Settings/Bindings → Authorized Render Context → Shared Renderer → Target Adapter/Markup/Assets`

The following remain distinct evidence domains:

`Blueprint Definition ≠ Definition Revision ≠ Published Compiled Blueprint ≠ Component Instance ≠ Binding Descriptor ≠ Authorized Render Context ≠ Resolved Value ≠ Render Tree ≠ Escaped Markup ≠ Asset Graph ≠ Builder Adapter Representation ≠ cached output ≠ certified runtime behavior`

A builder editor preview is not authorization proof. UI control visibility is not server validation. Conditional visibility is not access control. Sanitized rich text is content, not executable code. Asset declarations cannot become arbitrary remote-code loaders. Public cacheability is never inferred from visual similarity alone.

## Independent certification classes

Future evidence is classified independently as:

- `CBP-D` — Definition/revision/publish/compile lifecycle;
- `CBP-C` — control schema and component-instance validation;
- `CBP-B` — dynamic binding/context/Policy correctness;
- `CBP-R` — render primitives/escaping/sanitization/output semantics;
- `CBP-S` — slots/nesting/composition/styles/responsive behavior;
- `CBP-A` — assets/dependencies/scoped loading;
- `CBP-K` — cache/personalization/invalidation;
- `CBP-X` — portability/versioning/adapters/AI boundaries;
- `CBP-U` — accessibility/degraded/error UX;
- `CBP-O` — concurrency/performance/Multisite/operational behavior.

Passing one class never certifies another.

## Security and architecture invariants

1. Dynamic values resolve only through typed Data Source/Query/Relation/Settings/Membership/registered-resolver contracts with current Capability + target resource Policy.
2. Component configuration never becomes an arbitrary PHP/JS execution channel.
3. Render primitives and styles are schema-constrained and contextually escaped/sanitized.
4. Slots, partials and nested components have explicit type, cycle, depth and size bounds.
5. Private media, user/profile data, membership state and protected Query results must not become public merely because a component renders them.
6. Asset loading uses registered handles/dependencies and cannot turn a user Blueprint into an arbitrary remote CDN executable loader.
7. Cache keys/invalidation incorporate every security- and personalization-relevant dependency; public cache may not contain user/site/access-specific output without proof of equivalence.
8. Builder documents/private serialization remain adapter-owned representations and never the canonical WPE Component Blueprint model.
9. Builder adapter certification remains separate. `CBP-*` success does not produce a `BC*` certification, and `BW-*` success does not certify the core runtime.
10. AI/Workflow/REST/CLI or editor channels may create or mutate Component Blueprint definitions only through registered typed abilities and the same authorization/publish rules as other callers.
11. Multisite rendering always uses explicit site/network ownership and target-site authorization; blog switching or shared cache cannot leak cross-site data.
12. Accessibility-critical semantics and degraded/error behavior are part of the runtime contract, not optional builder polish.

## Evidence scope

The fixed protocol covers:

- draft/publish/revision/compiler determinism and concurrency;
- control schema, responsive values, dynamic-binding flags and server validation;
- typed field/settings/user/relation/query/membership/context/resolver bindings;
- authorization, missing/denied sources, batching and site isolation;
- text, heading, link, media, icon, list, table, conditional, repeater, slot, partial and SDK primitives;
- escaping/sanitization and unsafe URL/HTML/CSS/script boundaries;
- slots, child constraints, cycles, recursion, context narrowing and collection states;
- named style targets, typed style properties, design tokens, responsive/RTL/reduced-motion behavior and scoped output;
- Asset Registry integration, dependencies, deduplication, conditional loading and missing assets;
- cache eligibility, principal/access/site generations, invalidation and protected-output isolation;
- portability/schema evolution/import-export behavior and adapter-extension namespaces;
- accessibility semantics, error/degraded states and normalized error integration;
- concurrency, resource budgets, performance, Multisite and operational diagnostics.

## Builder adapter relationship

`docs/QUALITY/BUILDER-WIDGETS-ADAPTER-CERTIFICATION-EVIDENCE-PROTOCOL.md` remains authoritative for Gutenberg, Elementor, Bricks, WPBakery and Visual Composer adapter certification.

The two protocols are complementary:

- `CBP-*` proves the shared WPE Component Blueprint runtime;
- `BW-* / BC0…BC4` proves builder-specific registration/editor/frontend mapping and compatibility.

Neither may be substituted for the other.

## Current truth

- CBP fixtures documented: **176**.
- CBP fixtures executed: **0/176**.
- Component Blueprint runtime certification: **none**.
- Builder adapter fixtures: **BW 0/50**.
- Builder runtime certifications: **0**.
- No compiler/renderer, block/widget registration, browser render, Query execution, asset build, cache benchmark, builder package install, performance fixture or Multisite runtime fixture has been executed by accepting this ADR.

## Consequences

The shared Component Blueprint runtime now has a fixed, reviewable evidence gate before production claims can be made. Future implementation may be modular internally, but release/support claims must preserve the independent certification classes and truth boundaries above.

Exact physical implementation details, performance thresholds, builder-specific capability levels and final runtime certifications remain evidence-gated.

## Development-consent gate

**Accepted architecture/evidence only. No production code, build, database migration, renderer/compiler execution, browser test, benchmark, provider call or other executable spike is authorized until explicit owner consent under ADR-0014 and `/DEVELOPMENT-CONSENT.md`.**
