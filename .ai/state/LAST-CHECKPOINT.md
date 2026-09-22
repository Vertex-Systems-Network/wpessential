# AI Durable Last Checkpoint

Policy: `GOV-AI-NATIVE-TIMEOUT-RESILIENCE-001`

## Exact observed base

- Repository: `Vertex-Systems-Network/wpessential`
- Exact main: `7f448ff01b51f0abfd3551ef431f5cf5675ec5a9`
- Completed audit milestone: Issue #1185 / PR #1187
- Active contract Issue: #1186
- Active PR: #1189
- Deterministic contract branch: `supervisor/dashboard-widgets-wordpress-dashboard-adapter-contract-v1`

## #1185 / #1187 terminal audit evidence

- exact head `9c8fa3e106a60b179349e0a3fb20fe7ba2b8a33d`
- Governance `35787116494` PASS
- Architecture `35787116504` PASS
- zero unresolved review threads
- zero commits behind main
- exactly seven shared-truth/audit files
- merged as `7f448ff01b51f0abfd3551ef431f5cf5675ec5a9`
- Issue #1185 closed completed
- audit verdict `READY_FOR_BOUNDED_WORDPRESS_DASHBOARD_ADAPTER_CONTRACT_V1`

## Current runtime boundary

Issue #1182 / PR #1184 remains the latest merged source milestone:

- six non-throwing module-local render states;
- compiler rejection and visibility denial prevent renderer invocation;
- unexpected runtime failures do not expose raw exception text;
- exact incoming `ExecutionContext` reaches visibility and rendering;
- asset handles remain data only;
- no Dashboard hooks/provider/source/mutation/asset side effects.

## #1186 adapter contract

The contract freezes a module-local WordPress integration seam and does not yet execute native Dashboard hooks.

Exact decisions:

- module-local WordPress environment interface + native implementation;
- module `boot()` owns idempotent Dashboard hook registration;
- exact hooks are `wp_dashboard_setup` and `wp_network_dashboard_setup`;
- Definition planning sorts by canonical `slug`, then Definition id;
- expected compiler rejection skips only that Definition;
- unexpected planning failure aborts the target before any registration side effect;
- site/network targets are isolated collision domains;
- exact widget id is `wpe_dashboard_widget_` + compiled widget key;
- all members of a same-target duplicate id group are suppressed before native registration;
- collision-free entries may register after the full collision scan;
- callbacks build fresh current-request `ExecutionContext` with `ExecutionChannel::Ui`;
- no blanket `manage_options` gate is added because the existing visibility evaluator remains authoritative;
- only `rendered` emits exact trusted renderer HTML;
- all other runtime states emit nothing;
- asset handles remain ignored/data-only;
- no control callback, provider/source execution, mutation, caching/refresh or shared Platform source change.

## Next source tranche

Issue #1188 is dependency-gated on the #1186 contract PR.

Exact scope is seven files: module-local WordPress environment interface, native environment, adapter, module wiring/boot, adapter unit tests, native environment unit tests, and module tests.

## Repository blockers

- #858 remains repository-admin broader required-CI ruleset work.
- #1102 remains explicit runtime-authorization gated.
- #947 remains independent Worker-only.

## Next safe action

Perform one consolidated exact-head Governance/Architecture + review-thread + main-divergence refresh for PR #1189. Merge only on terminal green evidence.
