# Forms & Workflows — Exact-Main Runtime-Readiness Audit V1

Issue: #643  
Parent: #636  
Audit anchor: `35874640a62b9cb08cec5268e949dacd55c8427f`  
Surface: 17 / `forms-workflows`

## Decision

**NOT RUNTIME-READY.** Keep `runtime_allowed=false`.

Surface 17 remains `ATOMIC_INVENTORY_COMPLETE` on exact main. The accepted gap matrix records no canonical Form Definition/revision owner, Fields/Control Registry composition adapter, bounded declarative calculation evaluator, secure/idempotent submission service, privacy-aware entry persistence contract, allowlisted action registry, retry/checkpoint state machine or Vault-reference adapter. No dedicated Forms & Workflows runtime module exists in `frameworks/Modules`.

## Classification

Form identity, fields, calculations, submission policy, entry storage, action mapping, run reliability and secret references are all **MISSING / BLOCKED** behind product-contract gates.

## Ownership boundaries

Future runtime must consume canonical Fields, Ability/Policy, Vault, Notifications/Email/Webhook/provider owners. Arbitrary PHP/JS/code expressions and raw callbacks/classes remain forbidden. Live external actions require separately authorized execution lanes.

## Smallest future bounded runtime slice — only after product gates

Candidate first slice: **read-only Form Definition + validation/dependency diagnostics**, with no submissions, entries or action execution.

Candidate files if separately authorized:
- `frameworks/Modules/FormsWorkflows/FormsWorkflowsModule.php`
- `frameworks/Modules/FormsWorkflows/FormDefinition.php`
- `frameworks/Modules/FormsWorkflows/FormReadService.php`
- unit tests under `tests/Unit/Modules/FormsWorkflows/`
- integration fixtures for Fields/Action dependency health

Required first: schema-valid option contracts, reviewed UX/submission contract, retention/privacy contract and exact-main Supervisor authorization.

## Exit

Issue #643 is evidence-accounted as **BLOCKED / NOT RUNTIME-READY**. No workflow execution, external side effects, entry mutation, certification or shared-truth changes are introduced.
