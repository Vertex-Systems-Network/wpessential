# Dashboard Widgets — Render-Source Transition Audit V1

Surface: **10 / Dashboard Widgets**  
Issue: **#1159**  
Exact audited main: `6509f8e1e82292fa10bc017e0eef2dcb8eb15ac4`

## Purpose

Reconcile the merged trusted content-class foundation and determine the next safe step toward trusted Dashboard Widget rendering without inventing authored payload fields or forking shared rendering infrastructure.

## #1154 / #1158 terminal evidence

Exact implementation head: `38b0883927864e8f13b3da0d6bf4d32dbde31a36`

- Governance Gate `35657768143` — PASS
- Architecture Guards `35657768000` — PASS
- PHP Quality Toolchain `35657767995` — PASS
- Platform Compatibility Matrix `35657768081` — PASS
- Distributable Package `35657768053` — PASS
- zero unresolved review threads
- zero commits behind main at merge
- exactly seven Issue #1154-authorized files
- merged as `6509f8e1e82292fa10bc017e0eef2dcb8eb15ac4`
- Issue #1154 closed completed

## Exact-main runtime state

Surface 10 now has:

- read-only Definition/runtime ownership;
- read-only Module/Ability exposure;
- central Pro activation;
- typed registration descriptor/compiler;
- typed visibility descriptor/compiler;
- current-user/current-site visibility evaluator;
- typed trusted content-class descriptor/compiler.

Registration compilation now refuses a widget unless `widget.type` is one of the reviewed V1 structured classes:

- `rich_text`
- `kpi`
- `chart`
- `quick_links`
- `announcement`
- `support_onboarding`
- `icon_link`

Deferred/executable/provider classes remain fail-closed.

## Shared rendering infrastructure audit

The repository already owns the canonical rendering boundary:

- `WPEssential\Contracts\RendererInterface`;
- `WPEssential\Platform\Rendering\BlueprintRendererDispatcher`;
- `ComponentBlueprintRegistryInterface` + registry;
- typed `RenderInput` / `RenderOutput` under the shared Platform rendering layer.

ADR-0051 explicitly prefers shared Component Blueprint rendering and forbids treating arbitrary remote/admin HTML/JS as trusted widget content.

**Conclusion:** Surface 10 must reuse the shared rendering contracts. A private Dashboard Widgets renderer registry/engine would duplicate Platform ownership and is not authorized.

## Missing render-source contract

The exact-main Surface 10 normalized option contract exposes high-level atomic policies such as:

- `dashboard-widgets.content.safety`;
- `dashboard-widgets.sources.policy`;
- `dashboard-widgets.providers.registry`;
- `dashboard-widgets.types.policy`.

The reviewed bank/projection also classifies source/provider/remote options and unsafe script/PHP boundaries.

However the current authored/runtime Definition schema does **not** expose an explicit evidence-backed field that identifies:

- a Component Blueprint id/revision for a structured Dashboard Widget;
- a deterministic binding map sufficient to construct shared `RenderInput`;
- exact ownership semantics for Surface 10-authored static content versus cross-surface/provider content.

The UX contract says “Content or data source summary” but does not define a concrete runtime payload key/binding schema.

Therefore runtime renderer wiring now would require inventing Definition fields and would violate the evidence-first contract.

## Verdicts

- **READY_FOR_TRUSTED_RENDER_SOURCE_IMPLEMENTATION_CONTRACT_V1**
- **BLOCKED_FOR_RENDERER_EXECUTION_WITHOUT_RENDER_SOURCE_SCHEMA**
- **BLOCKED_FOR_DIRECT_WORDPRESS_DASHBOARD_REGISTRATION**
- **BLOCKED_FOR_WP_ADD_DASHBOARD_WIDGET**
- **BLOCKED_FOR_PROVIDER_SOURCE_DATA_EXECUTION**
- **BLOCKED_FOR_REMOTE_IFRAME_EXECUTION**
- **BLOCKED_FOR_MUTATION_PREFERENCES**
- **BLOCKED_FOR_FULL_PARITY_RUNTIME_PRODUCT_CERTIFICATION**

## Authorized next tranche

Issue **#1160 — Dashboard Widgets: trusted render-source implementation contract V1**

This tranche is planning/implementation-contract only. It must define the smallest explicit Surface 10 Definition fields and binding rules needed for a later runtime compiler/bridge while reusing the shared Renderer/Component Blueprint contracts.

It must not execute a renderer or add Dashboard hooks.

## Scope boundary

This audit authorizes no:

- content body rendering or HTML output;
- renderer/provider/source execution;
- Safe HTTP/remote/iframe execution;
- `wp_dashboard_setup`, `wp_network_dashboard_setup`, `wp_add_dashboard_widget`;
- Definition/user-preference mutation;
- shared renderer fork;
- certification/deploy/release.

## Promotion condition

Issue #1160 remains dependency-gated until the #1159 audit PR merges from exact current main with terminal green Governance/Architecture, zero unresolved review threads and zero commits behind main.
