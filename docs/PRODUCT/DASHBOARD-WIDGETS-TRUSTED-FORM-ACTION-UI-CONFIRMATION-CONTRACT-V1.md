# Dashboard Widgets Trusted form_action UI + Confirmation Orchestration Contract V1

Status: PREPARED_NOT_MERGEABLE  
Issue: #1296  
Stacked predecessor: #1298  
Security blocker: #1289

## Purpose

Freeze the only allowed V1 presentation and confirmation-preflight boundary for `dashboard-widgets.type.form_action` without executing the owner Ability.

This contract is stacked on the prepared Input-Aware Action Authorization implementation and remains non-mergeable until all predecessor/security gates are terminal.

## Dependency gates

Before this contract may merge or promote readiness:

1. Issue #1287 / PR #1288 must be terminal with RB-0083 PASS.
2. Issue #1291 / PR #1298 must be reconciled to the terminal contract and merged with terminal PASS.
3. Security Issue #1289 must be resolved without weakening Architecture advisory policy.
4. Fresh-main reconciliation must confirm no contract drift.

No exception above authorizes bypassing these merge gates.

## Product boundary

`dashboard-widgets.type.form_action` is a Dashboard Widgets integration atomic owned by Forms & Workflows.

Existing trusted Dashboard component classes do not include `form_action`; therefore V1 MUST use a dedicated trusted module-local presentation path.

The presentation layer may consume only bounded compiled descriptors:
- Dashboard Widget title;
- action Ability id;
- bound action-input presence metadata;
- confirmation title;
- confirmation message;
- confirmation confirm label;
- confirmation cancel label.

No authored HTML, callback, PHP, shortcode, arbitrary JavaScript, owner output, or untrusted executable payload is accepted.

## Render-cycle invariant

Normal WordPress Dashboard GET/render remains mutation-free.

Render MAY:
- load current Dashboard Widget definition;
- compile current registration/action metadata;
- bind/read bounded action metadata needed for presentation;
- create a scoped canonical AJAX nonce;
- emit inert, escaped trusted markup.

Render MUST NOT:
- call `AbilityRegistry::execute()`;
- call any owner handler `handle()`;
- mutate Forms & Workflows state;
- append an execution-attempt audit;
- use generic `AbilityAjaxHandler`.

## Canonical AJAX transport

Reuse only the existing shared AJAX plane:
- `platform.ajax.routes` / `AjaxRouteRegistry`;
- `platform.ajax.dispatcher` / `AjaxDispatcher`;
- `platform.nonce` / `NonceManager`;
- existing global WordPress AJAX gateway.

One Dashboard-owned authenticated preflight route is allowed:

`dashboard-widgets.form-action.confirm`

Route contract:
- guests forbidden;
- nonce required;
- nonce operation `NonceOperation::Apply`;
- no fixed route capability required;
- owner Ability policy remains authoritative;
- no REST route;
- no admin-post route;
- no generic `AbilityAjaxHandler`.

## Browser request envelope

The browser may submit exactly:
- `definition_id`;
- `definition_revision`;
- `confirmation_state`.

`confirmation_state` is exactly:
- `accepted`; or
- `cancelled`.

The browser MUST NOT submit:
- Ability id;
- bound owner input;
- owner capability;
- Forms definition id directly;
- audit result;
- owner result;
- raw confirmation prose;
- arbitrary callback/provider data.

The server always reconstructs sensitive action truth from current canonical state.

## Stale-definition protection

Preflight fails closed unless:
- current Dashboard Widget Definition exists;
- current owner/type/status compile is valid;
- current revision exactly equals submitted `definition_revision`;
- current registration descriptor still exposes a valid action Ability;
- current confirmation descriptor is valid.

A stale revision maps only to a bounded refresh-required result.

## Current-request execution context

The handler derives a fresh canonical UI `ExecutionContext` from current WordPress request truth:
- authenticated user;
- current site;
- current network when applicable;
- correlation id where available;
- channel exactly `ExecutionChannel::Ui`.

No authored user/site/network identity is accepted.

## Accepted-confirmation preflight order

For `confirmation_state=accepted`:

1. authenticate and verify nonce through canonical AjaxDispatcher;
2. validate the exact request envelope;
3. load the current Dashboard Widget Definition;
4. require exact current Definition revision;
5. compile current registration/action descriptor;
6. require canonical action Ability id;
7. require canonical confirmation metadata;
8. bind current action input server-side through canonical Dashboard Action-Input Binder;
9. run Input-Aware Action Authorization with the exact bound input and UI context;
10. append canonical authorization audit;
11. if authorization denies, return bounded `authorization_denied`;
12. append canonical confirmation-accepted audit;
13. return bounded `confirmation_ready`.

No owner execution follows step 13.

`confirmation_ready` means:

**authorized + confirmed + audited, but NOT executed.**

## Cancellation order

For `confirmation_state=cancelled`:
- authenticate and validate request;
- reload/recompile current Dashboard Widget truth;
- require a current confirmation descriptor;
- append canonical cancelled/denied confirmation audit where canonical audit is available;
- return `confirmation_cancelled`;
- never authorize or execute the owner mutation merely because cancellation was submitted.

## Canonical audit plane

Reuse only:

`AuditServices::LOGGER = platform.audit`

and canonical `AuditLoggerInterface`.

Allowed action ids:
- `dashboard-widgets/action.authorization`;
- `dashboard-widgets/action.confirmation`.

Resource:
- owner surface id: 10;
- resource type: `dashboard-widget-action`;
- resource id: Dashboard Widget Definition UUID.

Audit metadata is limited to:
- `widget_key`;
- `definition_revision`;
- `ability_id`;
- `result_code`;
- `confirmation_state`;
- `input_present`.

Never audit:
- raw bound input;
- confirmation prose;
- nonce;
- secrets;
- owner payload/result;
- exception text;
- SQL/storage/provider detail.

If required authorization or confirmation audit append fails, the handler returns bounded `runtime_failure` and MUST NOT return `confirmation_ready`.

## Response taxonomy

Allowed semantic response states:
- `confirmation_ready`;
- `confirmation_cancelled`;
- `authorization_denied`;
- `confirmation_invalid`;
- `stale_definition`;
- `runtime_failure`.

Responses contain bounded machine-safe state and fixed server-owned notices only.

No raw owner output, policy exception, bound input, Definition payload, stack trace, SQL/provider/storage detail, or arbitrary HTML is returned.

## Security requirements

Permanent rules:
- no `AbilityRegistry::execute()`;
- no owner handler `handle()`;
- no Forms mutation;
- no REST mutation;
- no admin-post mutation;
- no generic `AbilityAjaxHandler`;
- no private nonce system;
- no private audit logger/table;
- no browser-supplied Ability id or owner input;
- no arbitrary Definition JS/assets;
- no success language implying business mutation completed;
- no automatic retry;
- no full Surface-10 parity or release promotion.

## Later implementation expectations

A separate implementation Issue must freeze an exact file allowlist before runtime coding.

Expected bounded components may include:
- trusted form-action presenter/renderer;
- Dashboard-owned form-action preflight AJAX handler;
- Dashboard module route/service wiring;
- WordPress Dashboard adapter integration for nonce/action presentation;
- focused unit/integration tests;
- five canonical shared-truth files.

This contract does not itself authorize those runtime changes.

## Required implementation evidence

Later implementation must prove:

### Presentation
- `form_action` never routes through generic content renderer;
- confirmation title/message/labels are escaped;
- raw HTML/script/callback execution is impossible;
- Dashboard GET performs zero owner execution/mutation.

### Transport
- authenticated user + valid nonce required;
- guest rejected;
- invalid nonce rejected;
- unknown/extra payload fields rejected;
- stale Definition revision rejected;
- browser cannot supply Ability id or bound owner input.

### Authorization
- server rebinds current action input;
- Input-Aware evaluator receives exact current bound input;
- canonical owner denial maps to bounded authorization-denied state;
- zero `AbilityRegistry::execute()`.

### Audit
- authorization allow audit occurs before confirmation-ready;
- accepted confirmation audit is mandatory;
- required audit failure prevents confirmation-ready;
- raw input/confirmation prose never enters audit metadata.

### Cancellation
- returns `confirmation_cancelled`;
- zero owner execution.

## Promotion boundary

After all dependency gates are terminal and exact-head contract CI/review passes, this tranche may promote only:

`CONTRACT_FROZEN_TRUSTED_FORM_ACTION_UI_CONFIRMATION_V1`

and readiness for a separately authorized implementation:

`READY_FOR_TRUSTED_FORM_ACTION_UI_CONFIRMATION_V1`

No action execution, Forms mutation, full Surface-10 parity, deployment, or release authority is promoted.
