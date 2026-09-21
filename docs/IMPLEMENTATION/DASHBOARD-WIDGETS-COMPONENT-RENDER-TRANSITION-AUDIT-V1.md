# Dashboard Widgets — Component-Render Transition Audit V1

Surface: **10 / Dashboard Widgets**
Issue: **#1167**
Exact audited main: `d721c09c66cb2e4f8e53fd353ed5b5f36f96f816`

## Purpose

Reconcile the merged trusted render-source compiler and determine the next safe prerequisite before Surface 10 invokes the shared renderer.

## #1162 / #1166 terminal evidence

Exact implementation head: `1ee7d728cddf687138df23e403880c6bba704ae0`

- Governance Gate `35661019438` — PASS
- Architecture Guards `35661019316` — PASS
- PHP Quality Toolchain `35661019306` — PASS
- Distributable Package `35661019321` — PASS
- Platform Compatibility Matrix `35661019341` — PASS
- zero unresolved review threads
- zero commits behind main
- exactly seven Issue #1162-authorized files
- merged as `d721c09c66cb2e4f8e53fd353ed5b5f36f96f816`
- Issue #1162 closed completed

## Exact-main Surface 10 state

Surface 10 now owns a fail-closed chain for:

1. read-only Dashboard Widget Definitions;
2. registration descriptor/compiler;
3. visibility descriptor/compiler;
4. current-user/current-site visibility evaluator;
5. trusted content-class descriptor/compiler;
6. trusted render-source descriptor/compiler;
7. projection to shared `RenderInput` without renderer invocation.

The merged render-source compiler:

- resolves exact Blueprint id/revision through the canonical shared registry;
- requires Surface 10 Blueprint ownership;
- accepts only `component_blueprint`;
- accepts only literal binding sources;
- requires exact Blueprint binding keys and scalar/list types;
- rejects executable authored strings and unsupported source kinds;
- makes registration compilation fail closed when render-source validation is unavailable or invalid.

## Shared renderer audit

The Platform already owns:

- `ComponentBlueprintRegistryInterface`;
- `ComponentBlueprintRegistry`;
- `RendererInterface`;
- `BlueprintRendererDispatcher`;
- `RenderInput`;
- `RenderOutput`.

The Free bootstrap registers the shared rendering services before modules boot.

`BlueprintRendererDispatcher` fails closed:

- missing Blueprint → `MissingBlueprint`;
- missing component renderer → `DependencyMismatch`;
- renderer exception → `DependencyMismatch` with no failed-render HTML.

## Surface 10 component implementation gap

The exact-main `frameworks/Modules/DashboardWidgets/` source tree contains compilers, descriptors, visibility evaluation and read services, but no Surface 10 Component Blueprint registrar and no component renderer implementation.

`DashboardWidgetsModule::register()` currently registers:

- read service;
- content-class compiler;
- render-source compiler;
- registration compiler;
- visibility compiler;
- visibility evaluator;
- read-only abilities.

It does not register a Surface 10 Component Blueprint or a renderer with the shared dispatcher.

Therefore invoking `RendererInterface::render()` from Dashboard Widgets now cannot establish a complete trusted widget path. A Definition may compile to a valid `RenderInput`, but no Surface 10 component renderer contract/registration exists to turn that input into privileged wp-admin HTML.

## Security architecture cross-check

ADR-0051 requires:

`Definition → compiled descriptor → server visibility policy → trusted content renderer → WordPress Dashboard adapter`

The content-trust runtime model requires final output escaping/sanitization by source trust class and states that widget failure must not take down the Dashboard.

This means the next prerequisite is not the WordPress hook and not provider execution. Surface 10 first needs an explicit trusted Component Blueprint/renderer contract.

## Verdicts

- **READY_FOR_TRUSTED_COMPONENT_BLUEPRINT_RENDERER_CONTRACT_V1**
- **BLOCKED_FOR_RENDERER_EXECUTION_WITHOUT_SURFACE10_COMPONENT_REGISTRATION**
- **BLOCKED_FOR_DIRECT_WORDPRESS_DASHBOARD_REGISTRATION**
- **BLOCKED_FOR_WP_ADD_DASHBOARD_WIDGET**
- **BLOCKED_FOR_PROVIDER_QUERY_SOURCE_EXECUTION**
- **BLOCKED_FOR_REMOTE_IFRAME_EXECUTION**
- **BLOCKED_FOR_MUTATION_PREFERENCES**
- **BLOCKED_FOR_FULL_PARITY_RUNTIME_PRODUCT_CERTIFICATION**

## Authorized next tranche

Issue **#1168 — Dashboard Widgets: trusted component blueprint/renderer implementation contract V1**.

The contract must define:

- stable Surface 10 component types for the seven trusted content classes;
- exact Blueprint binding schemas;
- Blueprint/renderer ownership and registration lifecycle;
- context-correct escaping/sanitization rules for privileged wp-admin output;
- asset-handle ownership;
- fail-closed component-render failure behavior;
- explicit exclusion of providers, Query, remote, iframe, shortcode/block and actions from V1.

It is planning/implementation-contract only and may not invoke a renderer or add WordPress Dashboard hooks.

## Scope boundary

This audit authorizes no:

- `RendererInterface::render()` call from Dashboard Widgets;
- HTML output;
- provider/query/source execution;
- Safe HTTP/remote/iframe execution;
- `wp_dashboard_setup`, `wp_network_dashboard_setup`, `wp_add_dashboard_widget`;
- Definition/user-preference mutation;
- shared renderer fork;
- certification/deploy/release.

## Promotion condition

Issue #1168 remains dependency-gated until the #1167 audit PR merges with terminal green Governance/Architecture, zero unresolved review threads and zero commits behind main.
