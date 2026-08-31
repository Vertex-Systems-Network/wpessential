# ADR-0150 — Asset Registry & Scoped Loader Executable Evidence Protocol

Status: **Accepted planning evidence contract / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP33`

## Context

WPEssential already has consumer-side asset evidence in UI, Build, Component Blueprint, Dynamic Listings, Frontend Dashboard and builder-adapter protocols. Those contracts do not independently certify the shared platform Asset Registry itself.

The platform architecture requires one shared registry/loader responsible for asset identity, ownership, dependency resolution, WordPress-handle coexistence, scoped route/screen/component matching, build-manifest mapping, loading strategy, lifecycle/degraded states, security, Multisite and performance truth.

## Decision

Accept `docs/QUALITY/ASSET-REGISTRY-SCOPED-LOADER-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the canonical future executable-evidence contract for the shared Asset Registry & Scoped Loader.

The fixed evidence matrix is:

- `ASR-01…ASR-176`
- executed: **0/176**
- runtime certification: **none**

Independent certification classes remain separate:

- `ASR-R` registry identity/ownership/descriptor validation;
- `ASR-D` dependency graph/order/conflict resolution;
- `ASR-S` scope/route/screen/component matching;
- `ASR-W` WordPress handle/runtime coexistence;
- `ASR-B` build-manifest/version/hash/provenance integration;
- `ASR-L` enqueue/loading/late discovery/client behavior;
- `ASR-C` cache/version/invalidation/CDN-origin semantics;
- `ASR-M` module/Pro/lifecycle/degraded behavior;
- `ASR-X` security/CSP/SRI/remote/extension boundaries;
- `ASR-O` Multisite/observability/performance/regression.

## Required truth boundaries

The following are not equivalent:

`source asset ≠ built file ≠ manifest entry ≠ registry descriptor ≠ WordPress handle ≠ dependency edge ≠ resolved load plan ≠ enqueued handle ≠ browser-fetched resource ≠ executed module ≠ certified asset behavior`

Additional rules:

1. Registered does not mean enqueued, fetched or executed.
2. Route/component declaration does not authorize global loading.
3. Same handle/library name does not prove semantic or version compatibility.
4. User definitions cannot become arbitrary executable script/style URLs or inline code through the generic registry.
5. WordPress-provided runtime handles must not be silently replaced by competing WPE copies.
6. Build/CI remain authoritative for artifact reproducibility and provenance; ASR certifies runtime mapping/load semantics only.
7. Module disable/Pro expiry cannot delete or remove assets still required for accepted safe deployed output or security enforcement.
8. Current route/blog context is never durable asset ownership.
9. Multisite/network-site scope must remain explicit and isolated.
10. Passing ASR does not promote UI, Build, Component Blueprint, Builder adapter or module-specific asset certifications, and vice versa.

## Consequences

- Shared asset behavior now has a bounded, non-duplicative executable evidence contract.
- Consumer protocols may reference ASR instead of re-defining registry semantics.
- Exact implementation, package/build tool, WordPress hooks, hashes, CDN/origin strategy and performance thresholds remain evidence-gated.
- No runtime code, enqueue, browser load, build, benchmark or provider execution is authorized by this ADR.

## Authorization

ADR-0014 remains the hard consent gate. This ADR is planning/documentation acceptance only and grants **no development or executable-evidence authorization**.
