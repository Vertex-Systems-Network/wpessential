# AI Durable Last Checkpoint

## 2026-09-25 — #1252 bounded Native Per-User Reorder State V1 active

### Exact repository truth

- Exact current main: `22355cb57a0e3891d3fccdb24c24c95ede258823`.
- Issue #1250 / PR #1251 — Bounded Native Per-User Collapse State V1 — terminal PASS:
  - exact head `728595aad011c9e8980d1ac8af22bda3faeae76c`;
  - Governance `36066477892` PASS;
  - Architecture `36066478000` PASS;
  - PHP Quality `36066477872` PASS;
  - Platform Compatibility `36066477878` PASS;
  - Distributable `36066478003` PASS;
  - exact eleven authorized files;
  - zero review blockers and zero behind;
  - expected-head merge `22355cb57a0e3891d3fccdb24c24c95ede258823`;
  - verdict `PASS_BOUNDED_NATIVE_USER_COLLAPSE_STATE_V1`.
- RB-0065 is terminal PASS.
- FAST AI-Native policy `GOV-AI-NATIVE-FAST-DELIVERY-001` remains active.

### Fresh exact-main Surface 10 audit

Canonical Bank candidate selected:

`widget.preference.user_reorder`

Audit verdict:

`READY_FOR_BOUNDED_NATIVE_USER_REORDER_STATE_V1`

### #1252 frozen feature contract

- Read current user's explicitly saved `meta-box-order_dashboard` or `meta-box-order_dashboard-network` preference only.
- Exact supported contexts: `normal`, `side`, `column3`, `column4`.
- Saved IDs are comma-separated by WordPress and their per-context order is semantically meaningful.
- Safe projection is `list<array{context:string,ids:list<string>}>`.
- Preserve saved ID sequence per context; dedupe by first occurrence.
- Canonical projection context order is normal, side, column3, column4.
- Absent preference returns an empty list.
- Malformed context/CSV/widget ID data fails closed at the adapter boundary.
- No registered-inventory order or priority merge.
- No replay into the WordPress meta-box registry.
- No AJAX reorder execution.
- No user-meta/user-option write, update or delete.
- No user-hide/user-collapse mutation.
- No inventory hide/remove mutation.
- No public Ability/REST expansion, provider/remote/action execution, shared Platform widening, P-006 runtime, certification, deploy or release.

### FAST delivery status

- Active Issue: **#1252 — Dashboard Widgets: bounded native per-user reorder state V1**.
- Active PR: **#1253 — Dashboard Widgets: bounded Native Per-User Reorder State V1**.
- Active branch: `agent/dashboard-widgets-bounded-native-user-reorder-state-v1`.
- RB-0066 is the single pending feature merge gate.
- Exact authorized scope: three Dashboard Widgets runtime/environment files, three focused unit-test files, and five shared-truth files.

### Persistent recovery order

1. `.ai/state/CURRENT-STATE.yaml`
2. `.ai/state/LAST-CHECKPOINT.md`
3. exact current main + OPEN Issues + OPEN PRs
4. `config/coordination/agent-work-queue.json`
5. `config/coordination/runner-benchmark.json`
6. historical `CHECKPOINT.md` only when needed

Repository/runtime evidence outranks compact state.
