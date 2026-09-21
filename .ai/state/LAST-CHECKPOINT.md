# AI Durable Last Checkpoint

Policy: `GOV-AI-NATIVE-TIMEOUT-RESILIENCE-001`

## Exact observed base

- Repository: `Vertex-Systems-Network/wpessential`
- Exact main before this milestone: `d3817ee2ddc10a4c625f2b0e47ddf1d9b90cdf46`
- Active Issue: #1137
- Active branch: `supervisor/dashboard-widgets-wordpress-registration-transition-audit-v1`

## Active milestone

Surface 10 / Dashboard Widgets exact-main WordPress Dashboard registration transition audit.

Verdict:
- **BLOCKED_FOR_DIRECT_WORDPRESS_DASHBOARD_REGISTRATION**
- **READY_FOR_REGISTRATION_DESCRIPTOR_FOUNDATION_V1**

## Why direct registration is blocked

Current runtime has canonical definitions, read-only abilities and central Pro activation, but lacks the compiled descriptor, visibility policy, trusted renderer/provider boundary and WordPress adapter required by the accepted privileged wp-admin content-trust model.

No `wp_dashboard_setup`, `wp_network_dashboard_setup` or `wp_add_dashboard_widget` call is authorized.

## Next bounded prerequisite after audit promotion

Issue #1138 on `agent/dashboard-widgets-registration-descriptor-foundation-v1`.

The tranche may only add a fail-closed typed registration descriptor/compiler, register it as a module-local service, and add focused tests.

Native allowlists:
- context: `normal|side|column3|column4`;
- priority: `high|core|default|low`.

No provider/render execution, visibility implementation, WordPress hook registration, mutation, shared Platform change, certification, deploy or release.

## Recovery behavior

On next `continue`, resolve the audit PR exact head and perform one consolidated CI/review/main-divergence refresh. Do not claim #1138 until the audit PR is merged.
