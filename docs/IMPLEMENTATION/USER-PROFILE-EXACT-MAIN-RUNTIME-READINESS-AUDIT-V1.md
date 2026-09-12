# User Profile — Exact-Main Runtime-Readiness Audit V1

Issue: #640  
Parent: #636  
Audit anchor: `35874640a62b9cb08cec5268e949dacd55c8427f`  
Surface: 14 / `profiles`

## Decision

**NOT RUNTIME-READY.** Keep `runtime_allowed=false`.

Exact-main machine truth keeps Surface 14 at `ATOMIC_INVENTORY_COMPLETE`. The accepted runtime gap matrix records no canonical profile Definition/field composition, field-level privacy/editability policy, public slug/collision service, visibility evaluator, registration state machine, auth adapter, privacy-aware directory query layer or versioned portability path. No dedicated User Profile runtime module exists in `frameworks/Modules`.

## Classification

Profile fields, field privacy/editability, public slug, public visibility, registration policy, authentication surface, directory query and portability are all **MISSING / BLOCKED** behind product-contract gates.

## Ownership boundaries

Future runtime must preserve WordPress user lifecycle and consume canonical Fields, Policy/Auth, Query and privacy ownership. Passwords/tokens/sessions must never enter export or diagnostics. Site/network scope and resource-level authorization must be explicit.

## Smallest future bounded runtime slice — only after product gates

Candidate first slice: **read-only Profile Definition + field/privacy effective diagnostics**, with no user mutation, registration or authentication changes.

Candidate files if separately authorized:
- `frameworks/Modules/UserProfile/UserProfileModule.php`
- `frameworks/Modules/UserProfile/ProfileDefinition.php`
- `frameworks/Modules/UserProfile/ProfileReadService.php`
- unit tests under `tests/Unit/Modules/UserProfile/`
- WordPress integration tests for field visibility and site/network isolation

Required first: schema-valid option contracts, reviewed UX/privacy contract, canonical Fields/Policy seams and exact-main Supervisor authorization.

## Exit

Issue #640 is evidence-accounted as **BLOCKED / NOT RUNTIME-READY**. No user/role mutation, runtime code, certification, shared truth, deployment or release changes are made.
