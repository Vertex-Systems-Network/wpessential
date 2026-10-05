# AI Durable Last Checkpoint

## 2026-10-05 — bounded Dashboard execution terminal PASS; AssetRegistry convergence RB-0089 active

### Exact current main truth

- Exact current main: `17b00095a594435acb2b10245bdb076737849abc`.
- Issue #1297 / PR #1312 — bounded Dashboard `form_action` execution V1 — terminal PASS:
  - exact head `e29fb878a7550a24f80328d6d77d0e68a5d259f7`;
  - Governance `37326386156` PASS;
  - PHP Quality `37326385987` PASS;
  - Distributable `37326385920` PASS;
  - Browser E2E Accessibility `37326385904` PASS;
  - Platform Compatibility `37326386023` PASS, 10/10 jobs;
  - Architecture `37326385998` FULL PASS;
  - exact 14-file allowlist;
  - zero behind;
  - zero unresolved review blockers;
  - expected-head merge as `17b00095a594435acb2b10245bdb076737849abc`;
  - verdict `PASS_BOUNDED_DASHBOARD_FORM_ACTION_EXECUTION_V1`.
- RB-0088 is terminal PASS.
- RB-0082 through RB-0088 (except preserved historical RB-0073 FAIL) remain terminal evidence for the first bounded Dashboard action chain.
- Issue #1297 is closed completed.

### Fresh architecture plan-drift audit

ADR-0150 remains Accepted and requires one shared Platform Asset Registry/scoped loader.

Current terminal Dashboard `form_action` behavior is safe and CI-proven, but its asset loading remains module-owned:
- `NativeWordPressDashboardWidgetEnvironment::enqueueFormActionAssets()` directly resolves the trusted `AdminAssetManifest('main')` entry;
- route/screen usage is bounded and Definition data cannot select arbitrary executable assets;
- the seam passed terminal Governance/Architecture/Browser/Platform evidence;
- however it bypasses the shared `AssetRegistry` load plan required by ADR-0150.

No accepted later ADR was found that supersedes ADR-0150.

Therefore the current flow is preserved while architectural convergence proceeds.

### Active #1294 / PR #1305 — bounded AssetRegistry WordPress loader contract V1

PR #1305 is reconciled onto current main `17b00095a594435acb2b10245bdb076737849abc`.

Contract role changed from a historical precondition to an architecture-debt convergence contract.

Frozen direction:
- reuse canonical `platform.assets` → `AssetRegistry` from `RenderingServiceRegistrar`;
- add code-owned trusted logical-handle → build-entry mapping;
- reuse canonical `AdminAssetManifest`;
- add one shared scoped WordPress loader;
- no arbitrary Definition/user URL/path/build entry;
- exact site/network Dashboard route scoping;
- no global wp-admin asset load;
- no remote/inline fallback;
- no package/dependency change;
- no action execution changes;
- no ASR 176/176 certification claim.

Runner sequence:
- RB-0089 — #1305 contract exact-head Governance + Architecture;
- RB-0090 — #1307 shared Platform loader implementation;
- RB-0091 — later Dashboard consumer migration from module-local manifest enqueue to `platform.assets.wordpress`.

### RB-0089 merge gate

Required:
- exact six-file contract/shared-truth diff;
- Governance PASS;
- Architecture FULL PASS;
- zero behind current main;
- zero unresolved review blockers;
- expected-head merge.

Promotion:
- `CONTRACT_FROZEN_BOUNDED_ASSETREGISTRY_WORDPRESS_LOADER_V1`;
- `READY_FOR_BOUNDED_ASSETREGISTRY_WORDPRESS_LOADER_V1`.

### Recovery order

1. Read CURRENT-STATE + this checkpoint.
2. Resolve exact current main and PR #1305.
3. Validate exact six-file RB-0089 scope and terminal CI.
4. Merge #1305 only with expected-head proof.
5. Reconcile #1307 to terminal contract/main as RB-0090.
6. After RB-0090 terminal, open/freeze RB-0091 Dashboard consumer migration.
