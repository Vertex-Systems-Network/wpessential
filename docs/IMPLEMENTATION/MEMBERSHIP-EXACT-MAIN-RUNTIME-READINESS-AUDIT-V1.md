# Membership — Exact-Main Runtime-Readiness Audit V1

Issue: #641  
Parent: #636  
Audit anchor: `35874640a62b9cb08cec5268e949dacd55c8427f`  
Surface: 15 / `membership`

## Decision

**NOT RUNTIME-READY.** Keep `runtime_allowed=false`.

Surface 15 remains `ATOMIC_INVENTORY_COMPLETE` on exact main. The accepted gap matrix records no canonical Plan Definition/revision lifecycle, typed pricing adapter, membership state machine, idempotent plan-change planner, entitlement-to-Policy evaluator, discount mapping, provider reconciliation adapter or privacy-safe reporting layer. No dedicated Membership runtime module exists in `frameworks/Modules`.

## Classification

Plan identity, pricing model, state lifecycle, plan changes, restrictions, discounts, transaction reference and reporting are all **MISSING / BLOCKED** behind product-contract gates.

## Ownership boundaries

Future runtime must keep entitlement/resource checks server-authoritative and consume canonical Policy, Roles and provider seams. Live charge/refund/subscription mutation is a separate provider-execution gate. Secrets/tokens must not enter UI/bootstrap/export state.

## Smallest future bounded runtime slice — only after product gates

Candidate first slice: **read-only Plan Definition + entitlement preview/diagnostics**, with no money movement or membership mutation.

Candidate files if separately authorized:
- `frameworks/Modules/Membership/MembershipModule.php`
- `frameworks/Modules/Membership/PlanDefinition.php`
- `frameworks/Modules/Membership/MembershipReadService.php`
- unit tests under `tests/Unit/Modules/Membership/`
- integration tests for Policy/role dependency states

Required first: schema-valid option contracts, reviewed UX/state lifecycle contract, provider boundary contract and exact-main Supervisor authorization.

## Exit

Issue #641 is evidence-accounted as **BLOCKED / NOT RUNTIME-READY**. No provider execution, membership mutation, certification or shared-truth changes are made.
