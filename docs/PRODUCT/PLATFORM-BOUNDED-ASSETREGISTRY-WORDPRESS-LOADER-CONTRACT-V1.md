# Platform Bounded AssetRegistry WordPress Loader Contract V1

Status: FRESH-MAIN CONTRACT VALIDATION
Issue: #1294
Related prerequisite audit: #1292
Current main anchor: 17b00095a594435acb2b10245bdb076737849abc

## Purpose

Freeze the smallest shared first-party asset runtime needed to close the remaining ADR-0150 architectural debt after the first bounded Dashboard `form_action` action flow reached terminal PASS.

This contract reconciles:
- accepted ADR-0150 shared Asset Registry / scoped-loader architecture;
- #1292/#1294 shared loader prerequisite;
- the now-merged bounded Dashboard-owned fixed asset enqueue seam from #1300/#1301;
- the now-merged bounded execution seam from #1297/#1312.

The current Dashboard implementation is not rolled back or declared unsafe. It is a narrow, code-owned, route-bounded seam that passed full CI. The remaining debt is architectural convergence: new cross-module browser asset loading must flow through the shared Platform AssetRegistry/scoped-loader path rather than remain module-private.

## Current canonical evidence

Current Platform source contains:
- `AssetRegistry`;
- `AssetDescriptor`;
- `AssetScope`;
- `AssetLoadStrategy`;
- `AdminAssetManifest`.

Current gaps:
- `Plugin::boot()` calls `RenderingServiceRegistrar`, which already publishes the canonical `platform.assets` → `AssetRegistry` service;
- no shared trusted build-entry map exists;
- no shared scoped WordPress asset loader exists;
- `AssetDescriptor` has ownership/scope/dependency/route semantics but no executable file/source identity;
- `PlatformAdminController` directly consumes `AdminAssetManifest` for its own page;
- current merged Dashboard `form_action` runtime directly consumes `AdminAssetManifest` inside a module environment seam and therefore bypasses the shared `AssetRegistry` load plan.

ADR-0150 remains planning evidence, but its architectural direction is authoritative for this prerequisite:
one shared registry/loader owns asset identity, ownership, dependencies, scope and trusted build mapping.

## V1 design choice

Do **not** add an executable URL/path/build entry directly to `AssetDescriptor`.

Instead introduce a small Platform-owned, code-owned mapping layer:

`AssetRegistry descriptor`
+
`TrustedAssetBuildEntryRegistry`
+
`AdminAssetManifest`
+
`WordPressAssetLoader`

Reason:
- preserves accepted `AssetDescriptor` semantics;
- keeps executable build mapping code-owned rather than Definition-owned;
- allows script-only or script+style mapping without overloading generic descriptor semantics;
- supports exact fail-closed lookup;
- avoids arbitrary URL/path injection.

## Canonical service IDs

Later implementation must **reuse, not re-register**, the existing canonical service:

- `platform.assets` → canonical `AssetRegistry`, already owned by `RenderingServiceRegistrar`;

and publish the remaining services:

- `platform.assets.build-entries` → trusted code-owned build-entry registry;
- `platform.assets.manifest` → canonical trusted `AdminAssetManifest`;
- `platform.assets.wordpress` → bounded WordPress scoped loader.

A small `AssetServices` constants class should own these stable IDs.

## Trusted build-entry mapping

Introduce a Platform-owned immutable descriptor equivalent to:

`TrustedAssetBuildEntry`

Fields:
- logical AssetRegistry handle;
- required script manifest entry;
- optional style manifest entry or explicit no-style state.

Rules:
- handle must match an already registered `AssetDescriptor`;
- manifest entry grammar must be bounded and code-owned;
- no slash, traversal, scheme, host or URL;
- no Definition/user/request data may select an entry;
- duplicate logical handle mapping fails closed;
- unknown mapping fails closed.

The mapping registry is configuration/identity only. It does not enqueue.

## First consumer

Surface 10 registers one first-party logical handle for the Dashboard interaction bundle.

Canonical V1 direction:

`wpe-dashboard-form-action`

Descriptor:
- ownerSurfaceId: 10;
- scope: Admin;
- loadStrategy: AdminRoute;
- dependencies: bounded code-owned list;
- exact admin routes:
  - `/wp-admin/index.php`;
  - `/wp-admin/network/index.php`.

Build mapping:
- handle: `wpe-dashboard-form-action`;
- script entry: `main`;
- style entry: none for V1 unless later exact UI evidence proves a style asset is required.

The existing generated `main.js` bundle may be reused because the Dashboard behavior is data-root guarded. No second bundle/build entry is required by this contract.

## Exact route identity

The loader must derive a canonical current admin route from trusted WordPress/request environment state.

V1 accepts only:
- site Dashboard → `/wp-admin/index.php`;
- network Dashboard → `/wp-admin/network/index.php`.

The environment abstraction must normalize current admin route before registry matching.

Forbidden route matches:
- arbitrary substring/prefix;
- user-provided route;
- query-string-dependent identity;
- unrelated wp-admin route;
- frontend;
- REST;
- AJAX execution request;
- CLI;
- cron/worker.

## WordPress loader contract

The shared loader receives:
- canonical `AssetRegistry`;
- trusted build-entry registry;
- trusted `AdminAssetManifest`;
- bounded WordPress asset environment.

For a current admin route it must:

1. obtain descriptors from `AssetRegistry::forAdminRoute($route)`;
2. preserve dependency order from `AssetRegistry::resolve()`;
3. require Admin scope;
4. resolve exact code-owned build mapping for every WPE-owned descriptor;
5. resolve manifest file metadata through `AdminAssetManifest`;
6. fail closed if any required mapping/manifest/file is missing;
7. enqueue dependencies before dependents;
8. enqueue each logical handle at most once;
9. preserve WordPress-provided handles rather than replace them;
10. apply only accepted load strategy metadata;
11. expose no arbitrary URL/path;
12. never localize secret-bearing data automatically.

## Dependency coexistence

AssetRegistry dependencies may include only WPE logical handles known to the registry.

WordPress/core dependencies returned by `AdminAssetManifest` are trusted build metadata and are passed to `wp_enqueue_script`; they do not need duplicate AssetRegistry descriptors.

The loader MUST NOT replace or re-register WordPress-provided libraries under competing handles.

## Script/style behavior

V1 build mapping distinguishes:
- script entry required;
- optional style entry.

For `wpe-dashboard-form-action`:
- enqueue generated `main.js`;
- do not enqueue `main.css` unless the mapping explicitly requests it.

This prevents unrelated Platform admin styles from being loaded globally on the native Dashboard.

## Runtime request data

The shared asset loader does not own action-specific nonce/payload projection.

For the Dashboard action consumer:
- presenter/server markup may continue to carry bounded per-widget AJAX action/type/nonce/Definition metadata;
- static asset URLs/version fields never carry nonce or secret data;
- loader itself does not accept raw action input or owner data.

## Failure semantics

Any of the following fail closed with no WPE bundle enqueue:
- unknown logical handle;
- missing build mapping;
- malformed build entry;
- missing manifest entry;
- missing generated script metadata;
- dependency graph failure;
- wrong route/scope;
- environment/API failure.

Failure must not enqueue a fallback remote asset or inline script.

## Existing Platform admin controller

V1 does not require rewriting `PlatformAdminController`.

Its existing direct `AdminAssetManifest` page-specific loading may remain unchanged to avoid widening this prerequisite.

The new shared loader is required for new cross-module consumers such as Dashboard form_action.

Later convergence of Platform admin loading into AssetRegistry is a separate non-blocking cleanup.

## Dashboard reconciliation requirement

The current Dashboard flow is terminal and operational; this contract does not retroactively invalidate its security or behavioral evidence.

After the shared Platform loader implementation is terminal:
- open a separate bounded Dashboard consumer-migration tranche;
- register the `wpe-dashboard-form-action` descriptor + build mapping through canonical shared services;
- remove the direct module-local `AdminAssetManifest` resolution from `NativeWordPressDashboardWidgetEnvironment`;
- make shared `platform.assets.wordpress` own native Dashboard enqueue;
- preserve the already-terminal presenter/preflight/execution behavior unchanged unless fresh evidence requires a bounded repair;
- rerun exact-head Governance, PHP Quality, Distributable, Browser, Platform Compatibility and Architecture.

This later consumer migration is tracked as RB-0091 direction and is not part of this contract PR or the Platform loader implementation PR.

## Expected later implementation maximum scope

Fresh exact-main audit may narrow this list. It may not widen without explicit Issue amendment.

Expected Platform runtime files:
1. `frameworks/Platform/Assets/AssetServices.php` — new;
2. `frameworks/Platform/Assets/TrustedAssetBuildEntry.php` — new;
3. `frameworks/Platform/Assets/TrustedAssetBuildEntryRegistry.php` — new;
4. `frameworks/Platform/Assets/WordPressAssetEnvironmentInterface.php` — new;
5. `frameworks/Platform/Assets/NativeWordPressAssetEnvironment.php` — new;
6. `frameworks/Platform/Assets/WordPressAssetLoader.php` — new;
7. `frameworks/Bootstrap/Plugin.php` — publish canonical services and shared hook;
8. existing `AssetRegistry.php` only if exact audit proves a minimal helper is required;
9. existing `AdminAssetManifest.php` only if exact audit proves script-only/style-entry lookup needs a backward-compatible helper.

Expected focused tests:
10. `tests/Unit/Platform/Assets/TrustedAssetBuildEntryRegistryTest.php`;
11. `tests/Unit/Platform/Assets/WordPressAssetLoaderTest.php`;
12. `tests/Unit/Platform/Assets/NativeWordPressAssetEnvironmentTest.php`;
13. bootstrap/service wiring test only if an existing focused bootstrap test surface is available.

Canonical shared truth:
14. `.ai/state/CURRENT-STATE.yaml`;
15. `.ai/state/LAST-CHECKPOINT.md`;
16. `README.md`;
17. `config/coordination/agent-work-queue.json`;
18. `config/coordination/runner-benchmark.json`.

No Dashboard runtime change belongs in the Platform implementation PR. Dashboard consumption is a later reconciliation commit/PR.

## Required implementation evidence

### Registry/mapping
- duplicate logical asset handle rejected;
- duplicate build mapping rejected;
- unknown handle/mapping fails closed;
- invalid entry grammar rejected;
- no URL/path/traversal accepted;
- dependency cycles/missing dependencies fail closed.

### Route scope
- site Dashboard route selects first consumer;
- network Dashboard route selects first consumer;
- unrelated wp-admin selects zero;
- frontend/REST/AJAX/CLI/cron select zero.

### Manifest/load
- script entry resolves only through trusted manifest;
- missing script/metadata fails closed;
- optional no-style mapping enqueues no style;
- dependencies preserve deterministic order;
- logical handle enqueues once;
- WordPress dependency handles are preserved;
- defer strategy only when accepted.

### Security
- no Definition controls build entry/URL;
- no remote origin fallback;
- no inline executable payload;
- no secret-bearing localized config;
- no dynamic package install;
- no second React/runtime copy.

### Regression
- existing Platform admin controller behavior unchanged;
- build/distributable package still includes expected generated admin assets;
- Dashboard consumer not yet promoted by the Platform runtime tranche alone.

## Permanent non-goals

- ASR 176/176 certification;
- arbitrary user-authored assets;
- remote asset CDN/origin policy;
- frontend asset loader expansion;
- builder/editor asset orchestration;
- action execution;
- REST/admin-post mutation;
- deployment/release promotion.

## Promotion boundary

RB-0089 is the exact-head contract merge gate.

After terminal RB-0089 evidence:

`CONTRACT_FROZEN_BOUNDED_ASSETREGISTRY_WORDPRESS_LOADER_V1`

and:

`READY_FOR_BOUNDED_ASSETREGISTRY_WORDPRESS_LOADER_V1`

The bounded shared loader implementation is RB-0090. The later Dashboard consumer convergence is RB-0091.

No runtime asset loading, ASR 176/176 certification, or new Dashboard action behavior is promoted by this contract alone.
