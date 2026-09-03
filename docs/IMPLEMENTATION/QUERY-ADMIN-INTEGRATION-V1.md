# Query Admin Integration V1

Status: canonical packaged admin route for the non-runtime Surface 6 Query authoring scaffold.

## Runtime boundary

The admitted Query module registers a `QueryAdminController` under the shared WPEssential admin shell at `wpessential-query`. The controller uses `QueryAdminBootstrapProjector` to expose only the canonical `wordpress.posts` Data Source metadata required by the existing authoring scaffold.

The bootstrap includes a server-owned draft identity, semantic field references/logical types, advertised predicates, capability version, page-size bound, and Relations-support flag. Authorization mappings, provider internals, storage identifiers, cache keys, SQL, and executable provider arguments are not projected.

## Build and enqueue

`admin-ui/src/query` is a production `wp-scripts` entry and is enqueued only for the Query admin screen through the shared `AdminAssetManifest`. Other WPEssential admin screens do not receive the Query bundle.

## Execution remains disabled

This tranche does not add REST/AJAX execution, save/status routes, nonce-protected mutations, or provider invocation. The internal `module.query.authorized-executor` remains internal. The rendered UI continues to expose the disabled `Preview execution unavailable` control.

## Evidence

Unit coverage verifies deterministic fail-closed bootstrap projection. Packaged Browser E2E activates the Pro Query module through a test-only pre-boot admission fixture, loads the actual distributable route, requires progressive enhancement, confirms `wordpress.posts` metadata and the page-size bound, asserts execution remains disabled, and captures axe accessibility evidence.
