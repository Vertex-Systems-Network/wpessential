# Platform Bounded AssetRegistry WordPress Loader Contract V1

Status: PREPARED_NOT_MERGEABLE
Issue: #1294
Related prerequisite audit: #1292
Current main anchor: 25f098a170c42d30f829d196452189d9f5b71763

## Purpose

Freeze the smallest shared first-party asset runtime required before Dashboard Widgets may terminally ship interactive `form_action` browser behavior.

This contract resolves the plan drift discovered between:
- accepted ADR-0150 shared Asset Registry / scoped-loader architecture;
- #1292/#1294 shared loader prerequisite;
- prepared #1301 Dashboard-module-local asset enqueue prototype.

The prepared #1301 prototype remains non-mergeable until it consumes this shared path.

## Current canonical evidence

Current Platform source contains:
- `AssetRegistry`;
- `AssetDescriptor`;
- `AssetScope`;
- `AssetLoadStrategy`;
- `AdminAssetManifest`.

Current gaps:
- `Plugin::boot()` does not publish `platform.assets`;
- no shared trusted build-entry map exists;
- no shared scoped WordPress asset loader exists;
- `AssetDescriptor` has ownership/scope/dependency/route semantics but no executable file/source identity;
- `PlatformAdminController` directly consumes `AdminAssetManifest` for its own page;
- current Dashboard #1301 preparation directly consumes `AdminAssetManifest` inside a module environment seam and therefore bypasses `AssetRegistry`.

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

Later implementation must publish:

- `platform.assets` → canonical `AssetRegistry`;
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

Prepared PR #1301 is not terminally merge-ready with its current module-local `enqueueFormActionAssets()` implementation.

After this shared runtime implementation is prepared/terminal:
- Dashboard module must register the `wpe-dashboard-form-action` descriptor + build mapping through canonical shared services;
- native Dashboard adapter/environment must stop directly resolving `AdminAssetManifest`;
- shared `platform.assets.wordpress` loader must own native Dashboard enqueue;
- #1301 must rerun exact-head PHP/Platform/Browser/Architecture/Governance gates after reconciliation.

The existing presenter/preflight/AJAX logic may otherwise remain bounded if fresh audit proves no drift.

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

After exact-head contract evidence:

`CONTRACT_FROZEN_BOUNDED_ASSETREGISTRY_WORDPRESS_LOADER_V1`

and readiness for a separately authorized bounded implementation:

`READY_FOR_BOUNDED_ASSETREGISTRY_WORDPRESS_LOADER_V1`

No runtime asset loading or Dashboard action UI is promoted by this contract alone.
