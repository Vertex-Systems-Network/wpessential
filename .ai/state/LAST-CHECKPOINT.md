# AI Durable Last Checkpoint

## 2026-09-24 — FAST feature #1236 Native Default-Hidden State V1 active

### Exact repository truth

- Exact current main: `a3d7ccef7c8cba7baf5a94bafd938da3f86979d0`.
- Issue #1234 / PR #1235 — Error-State source shared-truth reconciliation + FAST policy — terminal PASS.
  - exact head `d0e466565054cc51695bb6013f576f3e77525872`;
  - Governance `36014261934` PASS;
  - Architecture `36014262076` PASS;
  - exact five shared-truth files;
  - zero blockers and zero behind;
  - merge `a3d7ccef7c8cba7baf5a94bafd938da3f86979d0`.
- RB-0058 is reconciled terminal PASS from that exact-head evidence.
- FAST AI-Native policy `GOV-AI-NATIVE-FAST-DELIVERY-001` is active.

### Fresh exact-main Surface 10 audit

Next smallest dependency-supported feature:

`dashboard-widgets.inventory.default_hidden`

Audit verdict:

`READY_FOR_BOUNDED_NATIVE_DEFAULT_HIDDEN_STATE_V1`

Why:
- P0_NATIVE / MUST_HAVE / SOFT_NATIVE;
- repository native audit already identifies WordPress `default_hidden_meta_boxes`;
- current runtime already owns safe registration, site/network targeting and collision suppression;
- feature is default-state projection only and does not write user preferences;
- smaller/safer than loading, refresh/cache, dismiss/preferences, actions, provider or remote execution.

### #1236 frozen feature contract + implementation

- Optional authored `widget.inventory.default_hidden: bool`.
- Absent inventory/default_hidden => false.
- Unknown inventory keys or non-boolean value => compile rejection.
- Descriptor carries immutable `defaultHidden`.
- WordPress environment adds bounded filter registration and screen-id projection.
- Adapter registers `default_hidden_meta_boxes` once with two args.
- Only exact screens `dashboard` and `dashboard-network` are eligible.
- Default-hidden ids come from the exact same canonical target/collision plan used by registration.
- Existing hidden ids are preserved; WPE ids are appended once.
- Site-target eligibility and network-dashboard separation remain authoritative.
- Unsupported/malformed screen or environment/repository/compiler failure returns existing hidden list unchanged.
- No user-meta write, persistent preference mutation, inventory removal, provider/remote/action execution, loading, refresh/cache or shared Platform widening.

### FAST delivery status

- Active PR: **#1237 — Dashboard Widgets: bounded Native Default-Hidden State V1**.
- Exact current feature head will be validated by RB-0059.

Turn A scope is exactly fourteen files:
- five Dashboard Widgets runtime/WordPress files;
- four focused unit-test files;
- five shared-truth files.

RB-0059 is the single pending feature merge gate. After terminal merge, no separate reconciliation PR is expected; terminal issue evidence + resulting-main verification close the feature unless real divergence exists.

### Persistent recovery order

1. `.ai/state/CURRENT-STATE.yaml`
2. `.ai/state/LAST-CHECKPOINT.md`
3. exact current main + OPEN Issues + OPEN PRs
4. `config/coordination/agent-work-queue.json`
5. `config/coordination/runner-benchmark.json`
6. historical `CHECKPOINT.md` only when needed

Repository/runtime evidence outranks compact state.
