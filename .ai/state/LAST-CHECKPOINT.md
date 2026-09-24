# AI Durable Last Checkpoint

## 2026-09-24 — #1248 bounded Native Per-User Hidden State V1 active

### Exact repository truth

- Exact current main: `42eb0f87bb31bef7a9494df888e78c4ae681338c`.
- Issue #1246 / PR #1247 — Bounded Native Non-Core Registered Classification V1 — terminal PASS:
  - exact head `e75c1aee3e33d352e27cf80b81eed934fb671642`;
  - Governance `36053960261` PASS;
  - Architecture `36053960358` PASS;
  - PHP Quality `36053960286` PASS;
  - Platform Compatibility `36053960290` PASS;
  - Distributable `36053960350` PASS;
  - exact seven authorized files;
  - zero review blockers and zero behind;
  - expected-head merge `42eb0f87bb31bef7a9494df888e78c4ae681338c`;
  - verdict `PASS_BOUNDED_NATIVE_NON_CORE_REGISTERED_CLASSIFICATION_V1`.
- RB-0063 is terminal PASS.
- FAST AI-Native policy `GOV-AI-NATIVE-FAST-DELIVERY-001` remains active.

### Fresh exact-main Surface 10 audit

Canonical Bank candidate selected:

`widget.preference.user_hide`

Audit verdict:

`READY_FOR_BOUNDED_NATIVE_USER_HIDE_STATE_V1`

### #1248 frozen feature contract

- Read current user's explicitly saved `metaboxhidden_dashboard` or `metaboxhidden_dashboard-network` preference only.
- Exact supported screens: `dashboard` and `dashboard-network`.
- Absent preference returns an empty list.
- Safe output is unique deterministically sorted widget IDs.
- Malformed or unsafe preference state fails closed at the adapter boundary.
- Default-hidden policy and effective hidden-meta-box filters are excluded.
- No registered-inventory inference is required.
- No user-meta/user-option write, update or delete.
- No inventory hide/remove mutation.
- No public Ability/REST expansion, provider/remote/action execution, shared Platform widening, P-006 runtime, certification, deploy or release.

### FAST delivery status

- Active Issue: **#1248 — Dashboard Widgets: bounded native per-user hidden state V1**.
- Active PR: **#1249 — Dashboard Widgets: bounded Native Per-User Hidden State V1**.
- Active branch: `agent/dashboard-widgets-bounded-native-user-hide-state-v1`.
- RB-0064 is the single pending feature merge gate.
- Exact authorized scope: three Dashboard Widgets runtime/environment files, three focused unit-test files, and five shared-truth files.

### Persistent recovery order

1. `.ai/state/CURRENT-STATE.yaml`
2. `.ai/state/LAST-CHECKPOINT.md`
3. exact current main + OPEN Issues + OPEN PRs
4. `config/coordination/agent-work-queue.json`
5. `config/coordination/runner-benchmark.json`
6. historical `CHECKPOINT.md` only when needed

Repository/runtime evidence outranks compact state.
