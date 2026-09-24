# AI Durable Last Checkpoint

## 2026-09-25 — #1258 bounded Cron Background-Job Reference V1 active

### Exact repository truth

- Exact current main: `cfec6da42fb5ca40776d9282c5b8656623d00b71`.
- Issue #1256 / PR #1257 — Bounded Native Inventory Remove Seam V1 — terminal PASS:
  - exact head `b9b9978d25a218ba0b4fdebd2d0285a53e52550b`;
  - Governance `36070564866` PASS;
  - Architecture `36070564818` PASS;
  - PHP Quality `36070564718` PASS;
  - Platform Compatibility `36070564726` PASS;
  - Distributable `36070564880` PASS;
  - exact eleven authorized files;
  - zero review blockers and zero behind;
  - expected-head merge `cfec6da42fb5ca40776d9282c5b8656623d00b71`;
  - verdict `PASS_BOUNDED_NATIVE_INVENTORY_REMOVE_SEAM_V1`.
- RB-0068 is terminal PASS.
- All 12/12 P0_NATIVE Dashboard Widgets bank records now have bounded implementation evidence.
- FAST AI-Native policy `GOV-AI-NATIVE-FAST-DELIVERY-001` remains active.

### P1_CORE deep audit

Total P1_CORE bank records: **16**.

Already covered by existing bounded runtime:
- `dashboard-widgets.native.render_provider`: WordPress callback -> DashboardWidgetRuntimeRenderExecutor -> shared BlueprintRendererDispatcher.
- `dashboard-widgets.source.data_source_ref`: bounded Query `source_ref` -> canonical DataSourceRegistry + QueryReadConsumer.

Owner-contract blockers; do not duplicate owner logic:
- `dashboard-widgets.type.listing`
- `dashboard-widgets.source.listing_ref`
- `dashboard-widgets.source.query_ref`

Higher-risk missing provider/execution work:
- native.control_provider
- native.callback_args_provider
- type.activity
- type.form_action
- type.site_health
- type.shortcode
- type.block
- type.registered_provider
- source.context_tokens
- remote.connection_ref

Dependency-ready bounded integration selected:
- `dashboard-widgets.refresh.background_job`

### #1258 frozen feature contract

- Optional `widget.refresh.background_job`.
- `refresh` must be an object/map; V1 exact key is `background_job`.
- Background job id must be a lowercase RFC 4122 UUID.
- If authored, id must resolve through the canonical Cron read service.
- Resolved record must match exact id, `type=cron`, owner surface 18, and Published status.
- Compiled Dashboard Widget registration descriptor stores only the validated Cron definition id.
- Omitted refresh/background_job yields null.
- Missing Cron service, missing definition, wrong owner/type/status, malformed record or resolver failure fails closed.
- No schedule execution, enqueue, trigger, retry, Cron mutation, background worker or polling.
- No public mutation Ability/REST expansion.
- No Query/Listings/Cron/Platform source modification.

### FAST delivery status

- Active Issue: **#1258 — Dashboard Widgets: bounded Cron background-job reference V1**.
- Active branch: `agent/dashboard-widgets-bounded-cron-background-job-reference-v1`.
- RB-0069 is the single pending feature merge gate.
- Exact authorized scope: registration compiler + registration descriptor + DashboardWidgets module + two focused unit-test files + five shared-truth files.

### Persistent recovery order

1. `.ai/state/CURRENT-STATE.yaml`
2. `.ai/state/LAST-CHECKPOINT.md`
3. exact current main + OPEN Issues + OPEN PRs
4. `config/coordination/agent-work-queue.json`
5. `config/coordination/runner-benchmark.json`
6. historical `CHECKPOINT.md` only when needed

Repository/runtime evidence outranks compact state.
