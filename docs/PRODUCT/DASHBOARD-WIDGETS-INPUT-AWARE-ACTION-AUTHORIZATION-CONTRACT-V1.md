# Dashboard Widgets Input-Aware Action Authorization Contract V1

Status: **FROZEN CANDIDATE — authorization contract only, no action execution**

Exact source anchor: `main@25f098a170c42d30f829d196452189d9f5b71763`

Issue authority: **#1287**

## 1. Purpose

This contract corrects the remaining authorization mismatch between:

- terminal bounded Dashboard action-input binding; and
- the first terminal Forms & Workflows mutating Ability with non-empty input.

Current Dashboard authorization is still zero-input only. Until this contract is implemented, trusted `form_action` UI/orchestration is not dependency-ready.

## 2. Exact dependency gap

Current `DashboardWidgetActionAuthorizationEvaluator` requires:

`descriptor->inputSchema === []`

and calls:

`AbilityRegistry::authorize($abilityId, $context, [])`.

That prevents valid non-empty owner input such as:

- `definition_id`;
- `expected_revision`;
- `enabled`.

The Set-Enabled owner handler already supports resource authorization through `InputAuthorizingAbilityHandlerInterface`, but Dashboard authorization currently never forwards the bound input to it.

## 3. Later evaluator API

The next implementation must use:

```text
authorize(
    string $abilityId,
    array $input,
    ExecutionContext $context
): PolicyDecision
```

Zero-input abilities remain supported with `[]`.

## 4. Ability descriptor validation

Before canonical authorization, evaluator must require:

- canonical Ability id shape;
- descriptor exists;
- descriptor name exactly matches requested Ability id;
- owner surface is exactly 17;
- `mutates=true`;
- UI channel allowed.

No other owner or non-mutating Ability may pass.

## 5. Input validation

Evaluator must use canonical `AbilityInputValidator`.

### Zero-input descriptor

If:

`descriptor->inputSchema === []`

then:

- supplied input must be exactly `[]`;
- any non-empty input fails closed.

### Non-empty descriptor

If descriptor input schema is non-empty:

- schema itself must validate under canonical `AbilityInputValidator`;
- root schema must be `type=object`;
- supplied input must validate against the **current** descriptor schema;
- validation failure returns evaluator-owned invalid-input denial;
- no coercion/default injection/normalization is permitted.

Current descriptor truth is authoritative. Stale compiled input that no longer matches schema fails closed.

## 6. Context validation

Evaluator accepts only:

- authenticated principal;
- actor type exactly `user`;
- `ExecutionChannel::Ui`.

It rejects:

- anonymous users;
- service/system actors;
- REST;
- CLI;
- Internal/non-UI execution context.

This evaluator is a Dashboard UI authorization gate only.

## 7. Canonical authorization

After descriptor/input/context validation, evaluator calls only:

```text
AbilityRegistry::authorize($abilityId, $context, $input)
```

This preserves:

1. global capability/policy authorization;
2. owner resource authorization through `InputAuthorizingAbilityHandlerInterface::authorizeInput()`.

For `wpessential/forms-workflows/set-enabled`, this must forward the exact validated input so owner checks can enforce:

- Forms definition ownership;
- Published/Disabled lifecycle state;
- exact `expected_revision`;
- owner UUID/input requirements.

## 8. Decision preservation

When canonical authorization completes normally, return its exact `PolicyDecision`.

Do not rewrite owner denial reasons.

Examples that must remain visible as machine-safe reasons:

- capability denial;
- Forms invalid input;
- Forms not found;
- Forms wrong owner;
- Forms invalid state;
- Forms revision conflict.

Dashboard-owned reasons apply only to Dashboard-side preconditions/failures.

## 9. Dashboard-owned deny reasons

Frozen evaluator-owned reasons:

- `dashboard_widget_action_invalid_ability`
- `dashboard_widget_action_invalid_input`
- `dashboard_widget_action_invalid_context`
- `dashboard_widget_action_authorization_failure`

Meanings:

### invalid_ability

Descriptor missing/malformed/wrong owner/non-mutating/non-UI.

### invalid_input

Zero-input mismatch, invalid current input schema, or supplied input fails current schema validation.

### invalid_context

Unauthenticated/non-user/non-UI context.

### authorization_failure

Unexpected exception from descriptor validation, input validation, registry authorization or owner input authorization.

## 10. Exception boundary

Any unexpected Throwable during evaluation:

- no execution;
- no mutation;
- no raw exception message;
- return `dashboard_widget_action_authorization_failure`.

Owner result/input/payload/provider/storage details are never exposed.

## 11. Ordering

Required order:

1. canonical Ability id validation;
2. descriptor resolution;
3. descriptor owner/mutation/UI checks;
4. context validation;
5. input schema/input validation;
6. canonical `AbilityRegistry::authorize(..., $input)`.

No confirmation or execution is reached by this evaluator.

## 12. No execution invariant

This evaluator must never call:

- `AbilityRegistry::execute()`;
- owner handler `handle()`;
- any callback/provider/query execution;
- repository mutation;
- REST mutation endpoint.

Authorization must remain side-effect free except owner authorization reads already permitted by owner contract.

## 13. Audit boundary

This evaluator does not write canonical action audit records itself.

The already frozen Action Result + Audit contract requires a later orchestration layer to record:

- authorization decision;
- confirmation;
- execution attempt;
- terminal result.

This contract only makes authorization input-aware.

## 14. Next implementation scope direction

Fresh exact-main verification is required, but expected bounded implementation is:

1. `frameworks/Modules/DashboardWidgets/DashboardWidgetActionAuthorizationEvaluator.php`
2. `tests/Unit/Modules/DashboardWidgets/DashboardWidgetActionAuthorizationEvaluatorTest.php`
3. `frameworks/Modules/DashboardWidgets/DashboardWidgetsModule.php` only if constructor/service wiring requires validator injection
4. `tests/Unit/Modules/DashboardWidgets/DashboardWidgetsModuleTest.php` only if wiring changes
5. five shared-truth files

No `AbilityRegistry.php` change is expected.

## 15. Required implementation tests

At minimum:

- zero-input valid Ability + empty input allows canonical policy path;
- zero-input Ability + non-empty input denies invalid_input;
- non-empty valid schema/input allows canonical policy path;
- malformed/non-object schema denies invalid_input;
- input type/bounds/unknown property failure denies invalid_input;
- owner resource authorization deny reason is preserved;
- Set-Enabled stale revision denial is preserved;
- unauthenticated/service/non-UI context denies invalid_context;
- wrong owner/read-only/non-UI Ability denies invalid_ability;
- owner authorization exception fails closed to authorization_failure;
- authorization evaluator never executes owner handler.

## 16. Permanent non-goals

No confirmation acceptance.
No UI rendering.
No audit orchestration.
No result adaptation.
No `AbilityRegistry::execute()`.
No owner mutation invocation.
No REST widening.
No package/dependency change.
No production/release promotion.

## 17. This contract batch scope

This PR may change only:

1. this contract document;
2. `.ai/state/CURRENT-STATE.yaml`;
3. `.ai/state/LAST-CHECKPOINT.md`;
4. `README.md`;
5. `config/coordination/agent-work-queue.json`;
6. `config/coordination/runner-benchmark.json`.

No runtime/product PHP/tests are authorized in this contract tranche.

## 18. Corrected dependency order

1. Input-Aware Action Authorization contract.
2. Input-Aware Action Authorization implementation.
3. Trusted `form_action` UI/orchestration contract.
4. Trusted UI/orchestration implementation.
5. Final separately reviewed execution gate.

## 19. Promotion boundary

On terminal merge promote only:

`CONTRACT_FROZEN_DASHBOARD_INPUT_AWARE_ACTION_AUTHORIZATION_V1`

and:

`READY_FOR_BOUNDED_DASHBOARD_INPUT_AWARE_ACTION_AUTHORIZATION_V1`

Not promoted:

- input-aware authorization runtime implementation;
- confirmation UI;
- action execution;
- full Surface 10 parity;
- deployment, GA or release.
