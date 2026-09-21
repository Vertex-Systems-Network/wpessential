# AI Durable Last Checkpoint

Policy: `GOV-AI-NATIVE-TIMEOUT-RESILIENCE-001`

## Exact observed base

- Repository: `Vertex-Systems-Network/wpessential`
- Exact main before this milestone: `cd03b5002e1562e7f04d8b37018eea8638dd4a24`
- Active Issue: #1125
- Active PR: #1127
- Active branch: `supervisor/dashboard-widgets-module-ability-transition-audit-v1`

## Active milestone

Surface 10 / Dashboard Widgets exact-main Module/Ability exposure transition audit.

Decision: **READY_FOR_BOUNDED_READ_ONLY_MODULE_ABILITY_EXPOSURE_V1**.

Current accepted precedent separates read-only Module/Ability source from central activation:
- PR #886 — Admin Menu Module + read handler + unit test only;
- PR #887 — Settings equivalent only;
- PR #888 — Frontend Dashboard equivalent only;
- PR #898 — central activation separately gated later.

## Next bounded source slot after audit promotion

Issue #1126 on `agent/dashboard-widgets-read-module-ability-exposure-v1`.

Allowed: Dashboard Widgets Module, read Ability handler, focused module test.

Forbidden in #1126:
- `wpessential-pro.php` central activation;
- WordPress dashboard widget registration;
- mutation/user preferences;
- providers/query/listing/analytics/forms execution;
- cache/remote engines;
- shared Platform changes;
- full-parity certification, deploy or release.

## Recovery behavior

On next `continue`:

1. read compact state/checkpoint;
2. resolve PR #1127 current head and exact current main;
3. perform one consolidated exact-head CI/review refresh;
4. merge only if applicable checks are terminal green, branch is current and unresolved review threads are zero;
5. if pending, stop without tight polling.

Do not claim #1126 before #1127 promotion.
