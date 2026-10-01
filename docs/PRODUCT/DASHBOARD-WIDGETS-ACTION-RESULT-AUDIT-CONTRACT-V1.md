# Dashboard Widgets Action Result + Audit Contract V1

Status: **FROZEN CANDIDATE — contract only, no action execution**

Exact source anchor: `main@f20d15aa5879be026dd76d89cd3e009c77b4af0f`

Issue authority: **#1273**

## 1. Purpose

This contract freezes the safe result taxonomy and canonical audit semantics required before Dashboard Widgets may execute any Forms & Workflows mutating Ability.

It follows terminal bounded prerequisites:

- `dashboard-widgets.action.ability_id` canonical owner-17 reference validation;
- capability/policy authorization evaluation;
- typed bounded `widget.action.confirmation` metadata.

Those prerequisites still do **not** authorize mutation.

## 2. Canonical shared audit plane

Dashboard Widgets MUST reuse the promoted Platform audit service:

```text
AuditServices::LOGGER = platform.audit
```

The service must satisfy:

```text
AuditLoggerInterface::record(AuditRecord)
```

The canonical `AuditRecord` already owns:

- exact `ExecutionContext`;
- owner surface id;
- stable action id;
- `AuditOutcome`;
- optional resource type/id;
- bounded reason;
- sanitized metadata;
- retention/privacy classes;
- canonical event UUID and occurrence time.

The persistent audit plane already stores:

- actor type/user;
- site/network;
- execution channel;
- correlation id;
- action/outcome;
- resource identity;
- reason;
- sanitized metadata;
- retention/privacy class.

Dashboard Widgets MUST NOT create a private audit table, file logger, option log, transient log, custom DB schema or alternate audit service.

## 3. Result object boundary

Any later runtime implementation must return a typed module-local action result.

The result must contain only bounded machine-safe information. It must never carry raw owner output, exception text, stack trace, SQL, filesystem path, provider response, credential, secret, cookie, token or raw action input.

Required semantic fields for a later implementation:

```text
status
notice_severity
notice_code
notice_message
retry_mode
audit_degraded
```

Allowed `notice_severity`:

- `info`
- `success`
- `warning`
- `error`

Allowed `retry_mode`:

- `none`
- `manual_after_refresh`

Automatic retry is forbidden in V1.

## 4. Frozen result taxonomy

### 4.1 authorization_denied

Meaning:
- canonical Ability authorization denied; or
- authorization evaluation failed closed.

Rules:
- no confirmation acceptance;
- no execution attempt;
- no mutation;
- severity `warning`;
- safe notice: action is not permitted;
- retry mode `none`.

### 4.2 confirmation_required

Meaning:
- action reference and authorization are valid;
- valid confirmation metadata exists;
- explicit user confirmation has not yet been accepted.

Rules:
- no execution attempt;
- no mutation;
- severity `info`;
- safe notice: confirmation is required;
- retry mode `none`.

This is a pre-execution state, not an execution result.

### 4.3 confirmation_invalid

Meaning:
- confirmation is missing, stale, rejected, malformed, mismatched to the action/definition revision, or otherwise invalid under the later orchestration contract.

Rules:
- no execution attempt;
- no mutation;
- severity `warning`;
- safe notice instructs the user to review the current action state before confirming again;
- retry mode `manual_after_refresh`;
- never auto-retry.

### 4.4 execution_accepted

Meaning:
- a future owner Ability explicitly returns a bounded accepted/pending semantic result rather than terminal success;
- this state is unavailable unless the separately approved owner output contract supports it.

Rules:
- severity `info`;
- no owner/provider body is exposed;
- retry mode `none`;
- accepted is not reported as succeeded.

### 4.5 execution_succeeded

Meaning:
- owner Ability returns a separately contracted, unambiguous terminal success result.

Rules:
- severity `success`;
- safe fixed notice only;
- owner output is not rendered directly;
- retry mode `none`;
- `audit_degraded=false`.

### 4.6 execution_failed

Meaning:
- owner Ability returns a separately contracted, unambiguous terminal failure and the contract proves the requested mutation did not succeed.

Rules:
- severity `error`;
- safe fixed notice only;
- no exception/provider details;
- retry mode may be `manual_after_refresh` only;
- no automatic retry.

### 4.7 execution_outcome_unknown

Meaning:
- an execution attempt occurred but the owner result is ambiguous, interrupted, exceptional or otherwise cannot prove success or failure.

This category is mandatory to prevent accidental duplicate mutation.

Rules:
- severity `warning`;
- safe notice states that final action state must be refreshed/verified;
- retry mode `none`;
- never automatically retry;
- the user must refresh/re-read canonical owner state before another action is offered.

### 4.8 runtime_failure

Meaning:
- Dashboard Widgets orchestration fails **before** any owner execution attempt, and therefore no action mutation was attempted.

Rules:
- severity `error`;
- safe fixed notice only;
- no exception text;
- retry mode `manual_after_refresh`.

A failure after owner execution starts MUST NOT use `runtime_failure`; use a proven terminal owner result or `execution_outcome_unknown`.

### 4.9 execution_succeeded_audit_degraded

Meaning:
- owner mutation is unambiguously known to have succeeded;
- the required post-execution terminal audit append then fails.

Rules:
- severity `warning`;
- safe notice must preserve the fact that the action completed;
- notice must explicitly avoid suggesting retry;
- retry mode `none`;
- `audit_degraded=true`;
- raw audit exception is never exposed;
- this state must never be converted to `execution_failed` merely because post-success logging failed.

## 5. Safe notice contract

User-facing notice strings must be server-owned fixed copy selected by machine status.

Forbidden:

- rendering raw owner result text;
- interpolating exception messages;
- provider/query/SQL/HTTP bodies;
- filesystem paths;
- stack traces;
- raw authored action input;
- credentials/secrets;
- arbitrary HTML returned by an owner Ability.

Any future trusted UI must escape notice text at output.

## 6. Canonical audit actions

Surface 10 orchestration owns these stable audit action identifiers:

```text
dashboard-widgets/action.authorization
dashboard-widgets/action.confirmation
dashboard-widgets/action.execution-attempt
dashboard-widgets/action.result
```

All Dashboard Widget orchestration records use:

```text
owner_surface_id = 10
resource_type = dashboard-widget-action
resource_id = <Dashboard Widget Definition UUID>
```

The exact same incoming `ExecutionContext` instance must be used so canonical actor/site/network/channel/correlation context remains authoritative.

## 7. Audit outcome mapping

### authorization

- policy allow -> `AuditOutcome::Success`
- policy deny -> `AuditOutcome::Denied`
- authorization exception/fail-closed -> `AuditOutcome::Failed`

### confirmation

- explicit valid acceptance -> `AuditOutcome::Success`
- rejection/cancel -> `AuditOutcome::Denied`
- invalid/stale/mismatched confirmation -> `AuditOutcome::Failed`

### execution-attempt

- successfully admitted immediately before owner execute -> `AuditOutcome::Success`
- audit append failure prevents execution and therefore no execution-attempt record can be claimed

### result

- terminal success -> `AuditOutcome::Success`
- terminal owner failure -> `AuditOutcome::Failed`
- ambiguous execution outcome -> `AuditOutcome::Unknown`
- accepted/pending -> `AuditOutcome::Partial`

## 8. Audit metadata allowlist

Later Dashboard Widget action audit records may contain only bounded non-secret orchestration metadata from this allowlist:

```text
widget_key
definition_revision
ability_id
result_code
confirmation_state
input_present
```

Rules:

- `widget_key`: canonical compiled widget key only;
- `definition_revision`: positive integer;
- `ability_id`: already validated canonical ability name;
- `result_code`: one frozen machine result status;
- `confirmation_state`: bounded machine state only, never authored confirmation text;
- `input_present`: boolean only, never input content.

Not permitted in metadata:

- raw input values;
- owner output;
- confirmation title/message/labels;
- exception/error text;
- stack traces;
- SQL/query text;
- filesystem paths;
- HTTP/provider bodies;
- cookies/auth headers;
- secrets/tokens/API keys/private keys;
- payment/card information;
- arbitrary nested owner/provider metadata.

The canonical `AuditMetadataSanitizer` remains defense-in-depth; callers must still avoid supplying prohibited data in the first place.

## 9. Audit sequencing and fail-closed semantics

### 9.1 Authorization deny

If authorization denies:
- attempt the canonical authorization audit record;
- regardless of audit append outcome, execution remains denied;
- audit failure can never turn denial into allow.

### 9.2 Authorization allow

Before confirmation/execution may proceed:
- successful authorization decision must be appendable to canonical `platform.audit`;
- if required audit append fails, return pre-execution `runtime_failure`;
- do not mutate.

### 9.3 Confirmation

Before owner execution:
- explicit confirmation acceptance must be recorded successfully;
- if confirmation audit append fails, do not execute;
- rejection/invalid confirmation never executes regardless of audit availability.

### 9.4 Execution attempt

Immediately before any future `AbilityRegistry::execute()` call:
- append `dashboard-widgets/action.execution-attempt`;
- append must succeed;
- if append fails, execution is not called.

This is the final mandatory pre-mutation audit gate.

### 9.5 Terminal result

After owner execution returns:
- map owner result to the frozen result taxonomy;
- append `dashboard-widgets/action.result`.

If the terminal result audit append fails:

- if owner success is unambiguous, return `execution_succeeded_audit_degraded`;
- if owner failure is unambiguous, preserve `execution_failed` and mark internal audit degradation without claiming success;
- if owner execution outcome is ambiguous, preserve `execution_outcome_unknown`;
- never claim that the mutation failed solely because audit persistence failed;
- never automatically retry.

No private fallback audit sink is allowed.

## 10. Canonical audit service is execution-mandatory

A future mutating `form_action` orchestration path MUST require a valid service from:

```text
AuditServices::LOGGER
```

and it MUST satisfy `AuditLoggerInterface`.

Missing/malformed audit service is a pre-execution fail-closed condition.

The future execution path must not instantiate:

- `PersistentAuditLogger`;
- `InMemoryAuditLogger`;
- a private Dashboard Widget logger;
- a second audit table.

Service ownership remains Platform.

## 11. EventBus boundary

No EventBus dispatch is required by this V1 contract.

Reason:

- action audit durability already has one canonical Platform audit plane;
- event dispatch may trigger unrelated workflow/notification ownership;
- no event semantics are needed to prove result/audit readiness for bounded Dashboard Widget action execution.

A future event contract requires separate ownership review.

## 12. Forms & Workflows owner boundary

This contract does not create a mutating Ability.

The next dependency-ready tranche after this contract is:

`READY_FOR_FORMS_WORKFLOWS_MUTATING_ABILITY_OWNER_CONTRACT_V1`

That owner contract must define, separately:

- one real Surface-17 mutating Ability use case;
- capability/policy;
- exact zero-input or validated-input contract;
- deterministic success/failure/accepted result shape;
- idempotency/retry semantics;
- owner-side mutation guarantees;
- resource/result verification;
- no Dashboard Widget-specific business semantics.

Dashboard Widgets must adapt only the owner’s explicitly frozen result contract; it must not infer mutation success from arbitrary output.

## 13. Permanent security invariants

The action path must not introduce:

- authored PHP/callback/class execution;
- direct IntegrationRegistry execution;
- generic registered-provider bypass;
- mutation during GET/render;
- execution before explicit confirmation;
- execution after authorization denial;
- mutation when mandatory pre-execution audit append fails;
- automatic retry after an execution attempt with unknown outcome;
- raw exception/provider/input/secret disclosure;
- private audit persistence;
- Surface 10 ownership of Forms business mutation;
- REST mutation widening by implication;
- shared Platform source mutation without a separate gate;
- production deployment/release promotion by feature completion.

## 14. This contract batch scope

This batch changes only:

1. this contract document;
2. `.ai/state/CURRENT-STATE.yaml`;
3. `.ai/state/LAST-CHECKPOINT.md`;
4. `README.md`;
5. `config/coordination/agent-work-queue.json`;
6. `config/coordination/runner-benchmark.json`.

No runtime/product PHP, JS or test implementation is authorized.

## 15. Promotion boundary

On terminal merge the only promotions are:

`CONTRACT_FROZEN_ACTION_RESULT_AUDIT_V1`

and:

`READY_FOR_FORMS_WORKFLOWS_MUTATING_ABILITY_OWNER_CONTRACT_V1`

Not promoted:

- any Forms mutation Ability;
- Dashboard Widget action execution;
- trusted `form_action` UI/orchestration;
- non-empty action input;
- full Surface 10 runtime/product parity;
- deployment, GA or release.
