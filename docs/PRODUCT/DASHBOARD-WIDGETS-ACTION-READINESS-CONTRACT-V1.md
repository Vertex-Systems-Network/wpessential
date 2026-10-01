# Dashboard Widgets Action Readiness Contract V1

Status: **FROZEN CANDIDATE — contract only, no action execution**

Exact source anchor: `main@077eec04e53a9da7994df2977b69a0d5ba763ead`

Issue authority: **#1267**

## 1. Purpose

This contract defines the dependency order required before Surface 10 may execute a Forms & Workflows action through `dashboard-widgets.type.form_action`.

It follows these terminal bounded prerequisites:

- `dashboard-widgets.action.ability_id` reference validation — PASS through #1262 / PR #1263;
- canonical capability/policy authorization evaluation — PASS through #1265 / PR #1266.

Those prerequisites do **not** authorize action execution.

## 2. Current dependency boundary

Exact main still lacks all of the following:

1. trusted `form_action` presentation/UI;
2. explicit user confirmation semantics;
3. bounded action result-notice semantics;
4. bounded action audit semantics;
5. a production mutating Forms & Workflows owner-surface-17 Ability;
6. canonical non-empty action-input validation.

Therefore `AbilityRegistry::execute()` remains forbidden from Dashboard Widgets.

## 3. Ownership

### Surface 10 — Dashboard Widgets owns

- authored widget action presentation metadata;
- confirmation presentation metadata;
- orchestration eligibility;
- trusted Dashboard Widget rendering/interaction boundary;
- forwarding the exact authenticated UI `ExecutionContext`;
- showing bounded success/failure notices after a future owner action result.

### Surface 17 — Forms & Workflows owns

- business mutation semantics;
- form/workflow action registration;
- input contract for the owner action;
- idempotency/retry/compensation behavior where applicable;
- owner-specific output contract.

Surface 10 must not duplicate or bypass those semantics.

### Shared Platform owns

- Ability descriptor registry;
- Policy/capability authorization;
- canonical input-schema validation if non-empty action input is ever admitted;
- canonical audit primitive where shared audit infrastructure is used.

## 4. Frozen dependency order

The remaining action path MUST advance in this order:

1. **Bounded Action Confirmation Metadata V1**
   - compile trusted confirmation metadata only;
   - no renderer/UI interaction and no action execution.

2. **Bounded Action Result + Audit Contract V1**
   - freeze safe result categories and audit events;
   - no action execution.

3. **Forms & Workflows Mutating Ability Owner Contract V1**
   - Surface 17-owned mutation Ability only;
   - Dashboard Widgets still cannot execute it.

4. **Action Input Validation Gate**
   - required only before admitting non-empty action input;
   - until then, only exact empty input schema remains eligible.

5. **Trusted `form_action` Component + Confirmation Orchestration**
   - render and collect explicit confirmation;
   - still no mutation until all previous gates are terminal.

6. **Final Bounded Action Execution Gate**
   - only after all prior gates pass may a separately reviewed exact zero-input owner Ability execution seam be considered.

No later step may be promoted early.

## 5. Next dependency-ready prerequisite

The exact next prerequisite is:

`READY_FOR_BOUNDED_ACTION_CONFIRMATION_METADATA_V1`

It is intentionally metadata-only.

### 5.1 Authored shape

A Dashboard Widget may optionally carry:

```text
widget.action.confirmation
```

with the exact object keys:

```text
title
message
confirm_label
cancel_label
```

No other key is accepted in V1.

### 5.2 Field rules

All four fields:

- are required when `confirmation` is present;
- must be strings;
- are trimmed for validation;
- must remain non-empty;
- are presentation text only;
- must never be interpreted as HTML, shortcode, block markup, PHP, callback, token template or executable data;
- must not contain NUL/control characters other than ordinary whitespace;
- must be output-escaped by any future trusted renderer.

Bounds:

- `title`: 1..120 UTF-8 bytes after trim;
- `message`: 1..500 UTF-8 bytes after trim;
- `confirm_label`: 1..80 UTF-8 bytes after trim;
- `cancel_label`: 1..80 UTF-8 bytes after trim;
- encoded confirmation object: <= 1024 bytes.

The metadata compiler must fail closed on malformed shape, unknown keys, invalid type, empty text or bounds violation.

### 5.3 Eligibility semantics

For this metadata tranche:

- `action.ability_id` may continue to compile without confirmation for backward compatibility;
- confirmation metadata may compile and be stored in the registration descriptor;
- **no execution eligibility follows**.

For any future `type.form_action` execution tranche:

- a valid confirmation descriptor MUST be present;
- policy authorization MUST allow the action;
- missing/invalid confirmation MUST prevent execution.

## 6. Exact later source/test allowlist for Confirmation Metadata V1

A later implementation Issue may authorize only:

1. `frameworks/Modules/DashboardWidgets/DashboardWidgetActionConfirmationDescriptor.php` — new
2. `frameworks/Modules/DashboardWidgets/DashboardWidgetRegistrationCompiler.php`
3. `frameworks/Modules/DashboardWidgets/DashboardWidgetRegistrationDescriptor.php`
4. `tests/Unit/Modules/DashboardWidgets/DashboardWidgetActionConfirmationDescriptorTest.php` — new
5. `tests/Unit/Modules/DashboardWidgets/DashboardWidgetRegistrationCompilerTest.php`
6. `.ai/state/CURRENT-STATE.yaml`
7. `.ai/state/LAST-CHECKPOINT.md`
8. `README.md`
9. `config/coordination/agent-work-queue.json`
10. `config/coordination/runner-benchmark.json`

No other file is dependency-ready for that tranche.

## 7. Result-notice contract requirements

The later result contract must use bounded machine categories rather than raw exception/provider payloads.

At minimum it must distinguish:

- authorization denied;
- execution accepted/succeeded;
- execution failed safely;
- invalid/expired confirmation;
- runtime/internal failure.

It must explicitly prohibit:

- raw exception messages;
- stack traces;
- filesystem paths;
- SQL/provider payloads;
- secrets/tokens;
- arbitrary owner output rendered directly as HTML.

No result contract is implemented by this document.

## 8. Audit contract requirements

A later audit contract must define events for:

- action authorization decision;
- explicit confirmation;
- execution attempt;
- terminal result.

Audit data must use canonical actor/site/network/correlation context and must not log secret or raw action payload values.

No audit source change is authorized by this contract.

## 9. Forms owner mutation requirements

Dashboard Widgets must not create a synthetic Surface 17 mutation Ability.

A real mutating Ability must be separately owned and implemented by Forms & Workflows, with:

- owner surface 17;
- `mutates=true`;
- explicit UI exposure if intended for Dashboard Widgets;
- owner-specific policy/capability;
- deterministic output contract;
- explicit idempotency/failure behavior;
- validated input contract.

Until such an Ability exists, Dashboard Widgets action execution remains unavailable.

## 10. Input validation boundary

Current bounded action references admit only abilities with exactly empty input schema.

If a future Forms action requires non-empty input:

- a canonical platform input validator must exist first;
- Dashboard Widgets must not implement a private JSON-Schema validator;
- authored action input must be separately contracted and size-bounded;
- Dynamic/Query values must not bypass owner schema validation.

## 11. Permanent V1 security invariants

This action-readiness path must never introduce:

- authored PHP/callback/class execution;
- direct IntegrationRegistry execution;
- generic registered-provider bypass;
- mutation during GET/render;
- execution before explicit confirmation;
- execution after authorization denial or authorization exception;
- Surface 10 ownership of Forms business mutation;
- raw provider/exception/secret disclosure;
- REST mutation widening by implication;
- shared Platform changes without a separate owner gate;
- production deployment or release promotion by feature completion.

## 12. Promotion boundary

When this six-file contract batch merges, the only promotion is:

`CONTRACT_FROZEN_ACTION_READINESS_V1`

and the next implementation claim becomes:

`READY_FOR_BOUNDED_ACTION_CONFIRMATION_METADATA_V1`

Not promoted:

- `dashboard-widgets.type.form_action`;
- action execution;
- trusted `form_action` UI;
- Forms mutation Ability;
- non-empty action input;
- result/audit implementation;
- full Surface 10 runtime/product parity;
- deployment, GA or release.
