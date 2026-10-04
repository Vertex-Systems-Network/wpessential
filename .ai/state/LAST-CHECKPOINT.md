# AI Durable Last Checkpoint

## 2026-10-05 — #1287 Dashboard Widgets Input-Aware Action Authorization Contract V1 active

### Exact repository truth

- Exact current main: `25f098a170c42d30f829d196452189d9f5b71763`.
- Issue #1285 / PR #1286 — bounded Forms Set-Enabled Mutating Ability V1 — terminal PASS:
  - exact head `c45e623079824b7e75e4a05617f1439a6971ea2f`;
  - Governance `36932601887` PASS;
  - PHP Quality `36932601852` PASS;
  - Distributable `36932601809` PASS;
  - Architecture `36932601669` PASS;
  - Platform Compatibility `36932601737` PASS;
  - exact nine authorized files;
  - zero unresolved review blockers;
  - zero behind;
  - expected-head merge `25f098a170c42d30f829d196452189d9f5b71763`;
  - verdict `PASS_BOUNDED_FORMS_WORKFLOWS_SET_ENABLED_ABILITY_V1`.
- RB-0082 is terminal PASS.
- RB-0073 remains historical FAIL.

### Fresh dependency correction

The first real Forms mutation is ready, but current Dashboard authorization is still zero-input-only:

- `DashboardWidgetActionAuthorizationEvaluator` requires `descriptor->inputSchema === []`;
- it calls `AbilityRegistry::authorize(..., [])`;
- therefore Set-Enabled input cannot reach owner resource authorization.

Trusted `form_action` UI/orchestration is blocked until authorization becomes input-aware.

### #1287 contract direction

Later evaluator:
- accepts ability id + bound input + exact UI ExecutionContext;
- supports zero-input backward compatibility;
- validates current descriptor schema/input through canonical AbilityInputValidator;
- requires owner Surface 17 + mutates=true + UI channel;
- requires authenticated user + UI context;
- calls only `AbilityRegistry::authorize($abilityId, $context, $input)`;
- preserves canonical capability/owner denial reasons;
- fails unexpected exceptions closed;
- never calls `AbilityRegistry::execute()`.

### FAST delivery status

- Active Issue: **#1287 — Dashboard Widgets: Input-Aware Action Authorization Contract V1**.
- Active PR: **pending**.
- Active branch: `agent/dashboard-widgets-input-aware-action-authorization-contract-v1`.
- RB-0083 is the single contract merge gate.
- Exact authorized scope: one contract document + five shared-truth files.

### Corrected dependency order

1. Input-Aware Action Authorization contract.
2. Input-Aware Action Authorization implementation.
3. Trusted form_action UI/orchestration contract.
4. Trusted UI/orchestration implementation.
5. Final separately reviewed execution gate.

### Persistent recovery order

1. `.ai/state/CURRENT-STATE.yaml`
2. `.ai/state/LAST-CHECKPOINT.md`
3. exact current main + OPEN Issues + OPEN PRs
4. `config/coordination/agent-work-queue.json`
5. `config/coordination/runner-benchmark.json`
6. historical `CHECKPOINT.md` only when needed

Repository/runtime evidence outranks compact state.
