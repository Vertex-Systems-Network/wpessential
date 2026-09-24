# AI Durable Last Checkpoint

## 2026-09-24 — FAST feature #1238 Native Default-Collapsed State V1 active

### Exact repository truth

- Exact current main: `a79bf3e2e5b3f6a026ce31184f7652f933508969`.
- Issue #1236 / PR #1237 — Bounded Native Default-Hidden State V1 — terminal PASS.
  - exact head `6c90286c3ad32b9ac40f55d744e48f6d29a472d1`;
  - Governance `36017952292` PASS;
  - Architecture `36017952019` PASS;
  - Platform Compatibility `36017952031` PASS;
  - Distributable Package `36017951864` PASS;
  - exact fourteen-file implementation/test/shared-truth scope;
  - zero blockers and zero behind;
  - merge `a79bf3e2e5b3f6a026ce31184f7652f933508969`;
  - verdict `PASS_BOUNDED_NATIVE_DEFAULT_HIDDEN_STATE_V1`.
- RB-0059 is reconciled terminal PASS from exact-head evidence.
- FAST AI-Native policy `GOV-AI-NATIVE-FAST-DELIVERY-001` remains active.

### Fresh exact-main Surface 10 audit

Next smallest dependency-supported feature:

`dashboard-widgets.presentation.default_collapsed`

Audit verdict:

`READY_FOR_BOUNDED_NATIVE_DEFAULT_COLLAPSED_STATE_V1`

Safety decision:
- A naive unconditional `closed` postbox class would override saved user intent.
- V1 therefore applies authored default collapse only when saved current-user `closedpostboxes_<screen>` metadata is confirmed absent.
- WPE reads preference existence only; it does not write/update/delete user metadata.

### #1238 frozen feature contract + implementation

- Optional authored `widget.presentation.default_collapsed: bool`.
- Absent presentation/default_collapsed => false.
- Unknown presentation keys or non-boolean value => compile rejection.
- Descriptor carries immutable `defaultCollapsed`.
- WordPress environment exposes read-only `hasClosedPostboxPreference(screenId)`.
- Only exact `dashboard` and `dashboard-network` screen ids are supported by the native preference seam.
- After successful WPE widget registration and only when `defaultCollapsed=true`, adapter registers the exact dynamic native postbox-class filter for that WPE widget.
- Saved preference metadata exists => existing classes unchanged.
- Saved preference confirmed absent => append `closed` once.
- Preference read failure => classes unchanged.
- Collision-suppressed, target-ineligible, malformed or registration-failed widgets receive no default-collapsed filter.
- No user-meta write, collapsible disablement, dismissible state, inventory removal/discovery, provider/remote/action execution, loading, refresh/cache or shared Platform widening.

### FAST delivery status

- Active PR: **#1239 — Dashboard Widgets: bounded Native Default-Collapsed State V1**.
- Exact current feature head will be validated by RB-0060.

Turn A scope is exactly fourteen files:
- five Dashboard Widgets runtime/WordPress files;
- four focused unit-test files;
- five shared-truth files.

RB-0060 is the single pending feature merge gate. After terminal merge, no separate reconciliation PR is expected; terminal issue evidence + resulting-main verification close the feature unless real divergence exists.

### Persistent recovery order

1. `.ai/state/CURRENT-STATE.yaml`
2. `.ai/state/LAST-CHECKPOINT.md`
3. exact current main + OPEN Issues + OPEN PRs
4. `config/coordination/agent-work-queue.json`
5. `config/coordination/runner-benchmark.json`
6. historical `CHECKPOINT.md` only when needed

Repository/runtime evidence outranks compact state.
