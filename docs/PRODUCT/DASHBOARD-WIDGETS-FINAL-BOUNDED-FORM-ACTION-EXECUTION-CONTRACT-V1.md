# Dashboard Widgets Final Bounded form_action Execution Contract V1

Status: PREPARED_NOT_MERGEABLE  
Issue: #1297  
Stacked predecessor: #1301  
Security blocker: #1289

## Purpose

Freeze the only allowed first Dashboard Widget mutation execution path:

`dashboard-widgets.type.form_action`
→ explicit Surface-17 owner Ability allowlist
→ first allowed owner Ability:
`wpessential/forms-workflows/set-enabled`.

This contract authorizes no runtime execution implementation while predecessor/security gates remain non-terminal.

## Dependency gates

This contract may not merge or promote readiness until:

1. #1288 / RB-0083 is terminal PASS;
2. #1298 / RB-0084 is terminal PASS;
3. #1299 / RB-0085 is terminal PASS;
4. #1301 / RB-0086 is terminal PASS;
5. #1289 is resolved without weakening Architecture;
6. fresh-main/terminal-predecessor reconciliation proves no contract drift;
7. exact-head contract CI/review is green.

No exception above authorizes mutation runtime before these gates.

## Canonical owner truth audited on 2026-10-05

The first V1 owner Ability is exactly:

`wpessential/forms-workflows/set-enabled`

Canonical owner facts:
- Surface owner id: 17;
- mutates: true;
- channels: Internal + UI;
- REST exposure: false;
- capability: `manage_options`;
- input schema exact keys:
  - `definition_id` string;
  - `expected_revision` positive integer;
  - `enabled` boolean;
- additional input properties forbidden;
- mutable states: Published ↔ Disabled only;
- stale revision is denied and rechecked by the owner handler at execution;
- same-target request performs no save and no revision increment;
- changed request writes exactly one next revision.

The owner result schema is exactly seven properties:
- `definition_id`;
- `previous_revision`;
- `revision`;
- `status`;
- `enabled`;
- `changed`;
- `result_code`.

No additional owner result properties are accepted by the Dashboard result adapter.

## Execution transport

Use one dedicated authenticated canonical AJAX route:

`dashboard-widgets.form-action.execute`

Reuse only:
- `platform.ajax.routes`;
- `platform.ajax.dispatcher`;
- `platform.ajax.gateway`;
- canonical nonce services;
- canonical current-request execution-context factory;
- `AuditServices::LOGGER`.

Route requirements:
- `NonceOperation::Apply`;
- `capability=null`;
- `allowGuests=false`;
- `requiresNonce=true`;
- no REST route;
- no admin-post route;
- no generic `AbilityAjaxHandler`;
- no second/global AJAX gateway.

## Browser request envelope

The browser may send exactly:
- `definition_id`;
- `definition_revision`;
- `confirmation_state`.

`confirmation_state` is exactly `accepted`.

The browser MUST NOT send:
- Ability id;
- bound owner input;
- owner capability;
- target Forms definition directly;
- owner result;
- audit result;
- arbitrary callback/provider data;
- raw confirmation prose.

Unknown or extra keys fail closed.

## User-interaction rule

Execution may occur only as a direct continuation of an explicit user-confirmed `form_action` interaction after a current `confirmation_ready` preflight result.

The browser may issue at most one execute request for that explicit interaction.

Forbidden:
- timers/background execution;
- automatic retry;
- retry after timeout/exception/ambiguous result;
- retry after known success with degraded audit;
- execution during Dashboard GET/render;
- execution merely because prior `confirmation_ready` exists in DOM/state.

The server still revalidates all current truth and never trusts the prior client-visible preflight as authorization.

## Mandatory server execution sequence

For every admitted execute request:

1. canonical AjaxDispatcher authenticates and verifies execute-route nonce;
2. validate exact request envelope;
3. derive fresh authenticated user `ExecutionContext` with channel exactly `Ui`;
4. load current Dashboard Widget Definition;
5. require exact submitted Dashboard Definition revision;
6. compile current trusted content class;
7. require type exactly `form_action`;
8. compile current registration descriptor;
9. require canonical current action Ability, confirmation and input descriptors;
10. require Ability id exactly in the explicit V1 execution allowlist;
11. bind current owner input server-side through canonical Action-Input Binder;
12. run Input-Aware Action Authorization with the exact bound input;
13. append canonical authorization audit;
14. if denied/failure, stop with no execute;
15. append canonical confirmation-accepted audit;
16. if audit fails, stop with no execute;
17. append canonical execution-attempt audit;
18. if audit fails, stop with no execute;
19. call exactly one `AbilityRegistry::execute($abilityId, $input, $context)`;
20. classify the returned owner value through the frozen owner-specific result adapter;
21. append canonical result audit when possible;
22. return bounded server-owned response only.

No code path may invoke more than one owner execute call for a request.

## Reauthorization behavior

`AbilityRegistry::execute()` internally calls `authorize()` again before owner `handle()`.

This second canonical authorization is required defense-in-depth and MUST NOT be bypassed.

The Dashboard layer MUST NOT:
- call the owner handler directly;
- bypass `AbilityRegistry::execute()`;
- parse exception text to infer whether execution reached the owner handler.

Any exception after calling `AbilityRegistry::execute()` is treated as ambiguous execution outcome.

## Explicit owner allowlist

V1 allowlist contains exactly:

`wpessential/forms-workflows/set-enabled`

No wildcard Surface-17 execution.

Any future Ability requires a separate contract amendment covering:
- input semantics;
- authorization semantics;
- idempotency/replay behavior;
- return schema;
- result adapter;
- ambiguity handling;
- security/audit review.

## Set-Enabled success result adapter

The adapter accepts only an array with exactly these seven keys:

`definition_id`
`previous_revision`
`revision`
`status`
`enabled`
`changed`
`result_code`

All types and cross-field relationships must match the exact bound input.

### status_changed

Required:
- `result_code === status_changed`;
- `definition_id === input.definition_id`;
- `previous_revision === input.expected_revision`;
- `revision === input.expected_revision + 1`;
- `enabled === input.enabled`;
- `status === published` when enabled=true;
- `status === disabled` when enabled=false;
- `changed === true`.

Map to:
- state: `execution_succeeded`;
- notice code: `status_changed`.

### already_target_status

Required:
- `result_code === already_target_status`;
- `definition_id === input.definition_id`;
- `previous_revision === input.expected_revision`;
- `revision === input.expected_revision`;
- `enabled === input.enabled`;
- status matches requested enabled state;
- `changed === false`.

Map to:
- state: `execution_succeeded`;
- notice code: `already_target_status`.

Malformed, extra-key, wrong-type or cross-field-inconsistent owner return is never success.

## Exception and ambiguity rule

Once `AbilityRegistry::execute()` has been invoked, any thrown exception maps to:

`execution_outcome_unknown`

Likewise, any malformed/ambiguous owner return maps to:

`execution_outcome_unknown`

Rules:
- retry mode: none;
- no exception-message parsing;
- no automatic retry;
- no raw exception text;
- fixed notice instructs refresh/re-read of canonical state.

A post-attempt state re-read may guide the user but MUST NOT fabricate causal attribution to this request.

## Pre-execution failures

Before the single owner execute call, bounded outcomes may be:
- `authorization_denied`;
- `confirmation_invalid`;
- `stale_definition`;
- `runtime_failure`.

These states MUST NOT claim that mutation was attempted or succeeded.

## Canonical audit contract

Reuse only canonical `AuditLoggerInterface`.

Required action ids:
1. `dashboard-widgets/action.authorization`;
2. `dashboard-widgets/action.confirmation`;
3. `dashboard-widgets/action.execution-attempt`;
4. `dashboard-widgets/action.result`.

Owner surface:
- 10.

Resource:
- type: `dashboard-widget-action`;
- id: Dashboard Widget Definition UUID.

Metadata allowlist only:
- `widget_key`;
- `definition_revision`;
- `ability_id`;
- `result_code`;
- `confirmation_state`;
- `input_present`.

Never audit:
- raw bound input;
- raw owner output;
- confirmation prose;
- nonce;
- secret/token/cookie;
- exception text;
- SQL/storage/provider detail.

Any owner/policy reason may be persisted only when it matches the existing bounded machine-code reason policy; arbitrary text is dropped.

## Audit ordering and failure semantics

Pre-execution required order:

authorization allow audit
→ confirmation accepted audit
→ execution-attempt audit
→ exactly one owner execute.

Failure of any required pre-execution audit prevents owner execution.

After execute:

### Known bounded success + result audit succeeds
Return `execution_succeeded`.

### Known bounded success + result audit fails
Return:
`execution_succeeded_audit_degraded`

Rules:
- do not tell user to retry;
- do not convert known success into failure;
- fixed notice requires refresh/re-read.

### Execute exception / ambiguous return
Attempt result audit with bounded `execution_outcome_unknown`.

If that result audit also fails, response remains bounded `execution_outcome_unknown`; no retry is advised.

## Response taxonomy

Allowed semantic states:
- `execution_succeeded`;
- `execution_succeeded_audit_degraded`;
- `execution_outcome_unknown`;
- `authorization_denied`;
- `confirmation_invalid`;
- `stale_definition`;
- `runtime_failure`.

Responses may include only:
- bounded state;
- bounded server-owned notice code/text.

No raw owner output, Definition payload, bound input, stack trace, SQL/storage/provider detail or arbitrary HTML.

## Render/preflight invariants

The following paths remain mutation-free:
- WordPress Dashboard GET/render;
- widget registration hooks;
- visibility evaluation;
- content-class/registration compilation;
- form-action presenter;
- Action-Input Binder;
- preflight route `dashboard-widgets.form-action.confirm`.

Only the dedicated execute AJAX handler may call `AbilityRegistry::execute()`.

## Permanent forbidden paths

- generic arbitrary Ability execution;
- wildcard Surface-17 execution;
- direct owner `handle()`;
- generic `AbilityAjaxHandler`;
- authored PHP/callback/class execution;
- REST mutation;
- admin-post mutation;
- client-supplied Ability id/input;
- private audit logger/table;
- private nonce system;
- mutation without exact current revision;
- mutation without current authorization;
- mutation without required pre-execution audits;
- automatic retry;
- exception-message parsing;
- full Surface-10 parity/deploy/GA promotion from this tranche.

## Later implementation architecture

Fresh terminal-predecessor audit is mandatory immediately before implementation.

Expected bounded components:
- `DashboardWidgetFormActionExecuteAjaxHandler`;
- owner-specific execution result adapter/result object;
- module service/route wiring;
- presenter/browser execute-route + execute-nonce transport;
- focused unit/integration tests;
- five canonical shared-truth files.

No shared Platform mutation is expected.

## Required implementation evidence

### Admission
- guest rejected;
- invalid execute nonce rejected;
- exact payload only;
- stale Dashboard revision rejected;
- current form_action truth recompiled;
- current input rebound server-side;
- only explicit Set-Enabled allowlist accepted;
- owner authorization denial prevents execute;
- pre-execution audit failure prevents execute.

### Execution
- status_changed => exactly one execute + bounded success;
- already_target_status => exactly one execute + bounded no-change success;
- malformed owner return => outcome_unknown;
- owner execute exception => outcome_unknown;
- no exception parsing;
- execute count never exceeds one;
- no automatic retry.

### Audit
- exact pre-execution order authorization → confirmation → execution-attempt;
- execution-attempt persisted before owner execute;
- bounded result audit after owner result;
- raw input/output never audited;
- unsafe reason text not persisted;
- known success + result-audit failure => execution_succeeded_audit_degraded.

### Separation
- render path execute count zero;
- preflight path execute count zero;
- no REST/admin-post/generic AbilityAjaxHandler;
- no arbitrary Ability id;
- no shared Platform source widening.

## Promotion boundary

After dependencies are terminal and exact-head contract CI/review passes, this tranche may promote only:

`CONTRACT_FROZEN_BOUNDED_DASHBOARD_FORM_ACTION_EXECUTION_V1`

and readiness for a separately authorized exact-file implementation.

Only a later terminal implementation may promote:

`PASS_BOUNDED_DASHBOARD_FORM_ACTION_EXECUTION_V1`

Neither status equals full Surface 10 parity, full Forms parity, production deployment or GA.
