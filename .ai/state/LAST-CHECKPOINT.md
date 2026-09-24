# AI Durable Last Checkpoint

## 2026-09-25 — #1250 bounded Native Per-User Collapse State V1 active

### Exact repository truth

- Exact current main: `b8bf30a48f5db3f36eb1309280076d7daa833e55`.
- Issue #1248 / PR #1249 — Bounded Native Per-User Hidden State V1 — terminal PASS:
  - exact head `c9b14c2d07b3ccc6612b68a6c7c3dff7751671af`;
  - Governance `36056955974` PASS;
  - Architecture `36056956136` PASS;
  - PHP Quality `36056955992` PASS;
  - Platform Compatibility `36056955986` PASS;
  - Distributable `36056956030` PASS;
  - exact eleven authorized files;
  - zero review blockers and zero behind;
  - expected-head merge `b8bf30a48f5db3f36eb1309280076d7daa833e55`;
  - verdict `PASS_BOUNDED_NATIVE_USER_HIDE_STATE_V1`.
- RB-0064 is terminal PASS.
- FAST AI-Native policy `GOV-AI-NATIVE-FAST-DELIVERY-001` remains active.

### Fresh exact-main Surface 10 audit

Canonical Bank candidate selected:

`widget.preference.user_collapse`

Audit verdict:

`READY_FOR_BOUNDED_NATIVE_USER_COLLAPSE_STATE_V1`

### #1250 frozen feature contract

- Read current user's explicitly saved `closedpostboxes_dashboard` or `closedpostboxes_dashboard-network` preference only.
- Exact supported screens: `dashboard` and `dashboard-network`.
- Absent preference returns an empty list.
- Safe output is unique deterministically sorted widget IDs.
- Malformed or unsafe preference state fails closed at the adapter boundary.
- Existing `hasClosedPostboxPreference()` remains an existence check for default-collapsed behavior.
- `default_collapsed` policy is excluded from saved per-user state.
- No postbox class mutation is added by this read seam.
- No user-meta/user-option write, update or delete.
- No user-hide/user-reorder interpretation or mutation.
- No inventory hide/remove mutation.
- No public Ability/REST expansion, provider/remote/action execution, shared Platform widening, P-006 runtime, certification, deploy or release.

### FAST delivery status

- Active Issue: **#1250 — Dashboard Widgets: bounded native per-user collapse state V1**.
- Active PR: **#1251 — Dashboard Widgets: bounded Native Per-User Collapse State V1**.
- Active branch: `agent/dashboard-widgets-bounded-native-user-collapse-state-v1`.
- RB-0065 is the single pending feature merge gate.
- Exact authorized scope: three Dashboard Widgets runtime/environment files, three focused unit-test files, and five shared-truth files.

### Persistent recovery order

1. `.ai/state/CURRENT-STATE.yaml`
2. `.ai/state/LAST-CHECKPOINT.md`
3. exact current main + OPEN Issues + OPEN PRs
4. `config/coordination/agent-work-queue.json`
5. `config/coordination/runner-benchmark.json`
6. historical `CHECKPOINT.md` only when needed

Repository/runtime evidence outranks compact state.
