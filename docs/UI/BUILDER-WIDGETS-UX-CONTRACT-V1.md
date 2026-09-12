# Builder Widgets — UX Contract V1

Surface: **16 / Builder Widgets**  
Machine source: `config/product/option-contracts/builder-widgets.json`  
Lifecycle: **UX certification candidate**. Machine truth is `OPTION_CONTRACT_COMPLETE`; runtime/product promotion is outside this worker lane.

## Lifecycle preconditions

- Machine status must remain `OPTION_CONTRACT_COMPLETE` or later with `missing=0` and `unclassified=0`.
- All 18 current Atomic Option IDs are mapped exactly once below.
- Control, dynamic-data, rendering and adapter resolution remains server-authoritative and owner/provider aware.

## Canonical route and information architecture

Canonical admin location: **WPEssential → Experience → Builder Widgets**.

The IA separates **Components**, **Controls**, **Rendering**, **Style**, and **Adapters & Portability**. Advanced authored definitions are distinct from Expert provider references. Provider/runtime health is visible but cannot be edited as local truth.

## UX state classes

### Authored definition
Component identity/adapter availability policy, content/style controls, responsive behavior, server-render policy, state/cache policy, style tokens and safe adapter policy are revisioned Builder Widgets-owned definitions.

### Effective/runtime state
Resolved control availability, dynamic source output, renderer state/cache and active adapter are read-only/effective context.

### Diagnostic/provider state
Control registry, client provider, dynamic Query/Listings/Fields sources, Roles/Ability policy and Elementor/Bricks/legacy adapters are canonical-owner/provider references.

### Deferred / prohibited state
Arbitrary executable callbacks, raw unregistered renderers and unsafe dynamic execution are never normal authored controls. Unsupported providers show degraded state.

## Atomic Option → UX map

- `builder-widgets.component.identity` — Components → widget/component identity, category and lifecycle definition.
- `builder-widgets.component.adapters` — Components → allowed adapter/provider compatibility declaration.
- `builder-widgets.controls.registry` — Controls → Fields/control-registry reference with owner health and schema diagnostics.
- `builder-widgets.controls.content-style` — Controls → authored content/style control composition.
- `builder-widgets.controls.responsive` — Controls → responsive/breakpoint behavior with deterministic fallback.
- `builder-widgets.render.server` — Rendering → WordPress/server-render strategy and registered template behavior.
- `builder-widgets.render.client-provider` — Rendering → Expert client renderer/provider reference; no inline executable callback.
- `builder-widgets.render.dynamic` — Rendering → Query/Listings/Fields dynamic-data reference with canonical owner semantics.
- `builder-widgets.render.ability` — Rendering → Roles/Policy/Ability reference for gated actions; authorization remains external.
- `builder-widgets.render.states-cache` — Rendering → loading/empty/error/success/cache-state policy and cache diagnostics.
- `builder-widgets.style.tokens` — Style → design-token reference/fallback behavior.
- `builder-widgets.style.box-state` — Style → bounded box/layout/interaction-state styling policy.
- `builder-widgets.adapter.gutenberg-shortcode` — Adapters & Portability → native Gutenberg/registered-shortcode compatibility path.
- `builder-widgets.adapter.elementor-bricks` — Adapters & Portability → certified Elementor/Bricks provider mapping and degraded state.
- `builder-widgets.adapter.legacy` — Adapters & Portability → explicitly identified legacy provider mapping with compatibility warning.
- `builder-widgets.adapter.parity-portability` — Adapters & Portability → cross-adapter definition portability and mismatch diagnostics.
- `builder-widgets.adapter.shortcode` — Adapters & Portability → registered shortcode/provider reference; no arbitrary shortcode execution string.
- `builder-widgets.adapter.wp-widget` — Adapters & Portability → registered WP Widget compatibility/provider reference.

## Interaction and persistence

Definitions use draft → validate → save revision. Changing adapter or control registry triggers compatibility validation and preserves unresolved references instead of silently dropping configuration. Dynamic sources provide preview-only sample/effective data and never write peer-owned state.

## Loading, empty, validation, conflict and recovery

Required states include empty component library, provider loading, missing control registry, unavailable dynamic source, unsupported adapter, renderer degraded, cache stale, preview loading/error/empty, stale revision, saved and reference-remapping recovery. Per-provider failure is isolated to affected components.

## Security and ownership

Fields owns reusable controls/schema; Query/Listings/Relations own their data semantics; Roles/Policy/Ability owns authorization; renderer/adapters own execution details. Builder Widgets owns component/control composition only. Arbitrary executable callbacks are prohibited. Client preview state never authorizes a server action.

## Accessibility

Editor controls require labels, keyboard navigation, visible focus and accessible error association. Drag/reorder has keyboard alternatives. Responsive preview controls expose textual viewport state. Generated widgets must preserve semantic roles and cannot rely on hover or color alone for essential interaction.

## Multisite and scope

Component/adapters and design-token references display site/network scope. Network/global scope is server-derived. Imports cannot silently bind to a different-site adapter, token or dynamic data source.

## Portability and reference remapping

Exports are definition-only and secret-free. Imports validate control registry, data source, Ability, token and adapter references and show unresolved mappings before commit. Legacy/unsupported adapters remain explicit compatibility state rather than automatic execution fallback.

## Performance and scale

Editor assets load only when needed. Dynamic previews are bounded/cached and avoid N+1 provider calls. Large component libraries support search/filtering and lazy previews. Cache state is diagnostic and must not mask owner/source invalidation.

## Degraded/provider states

Missing Fields/Query/Listings/Relations/Policy/renderer/builder adapters produce explicit unavailable/degraded state. The surface never fabricates data, authorization, renderer success or adapter parity. Recovery links to the canonical owner/provider.

## UX lifecycle exit criteria

Certification requires complete 18-ID mapping, zero missing/unclassified machine semantics, owner/provider boundary review, prohibited executable behavior, accessibility/portability/performance/degraded states and exact-head validator CI. Shared lifecycle promotion remains Supervisor-only.

## Non-certifications

Runtime implementation is not certified by this document. Product parity is not certified. Production deployment or release is not verified. No provider execution, data mutation, Ability action or arbitrary callback is authorized by this UX contract.
