# Membership — Post-UX Runtime Readiness Re-Audit V2

Issue: #822  
Parent: #817  
Audit anchor: `8f796aaf95f0957def439586eb1eb8fb2fd49d91`  
Surface: 15 / `membership`

## Decision

**READY_FOR_BOUNDED_RUNTIME_SLICE.**

Surface 15 is now `UX_CONTRACT_COMPLETE`; the Bank/Atomic/UX prerequisites that blocked the historical V1 audit are closed. Dedicated Membership owner runtime is still absent, but Roles plus Platform Abilities/Auth, Definitions, Audit/Observability and Integrations provide sufficient substrate for a read-only plan/entitlement-preview owner slice. Payment execution remains provider-owned and is not a readiness prerequisite for that read-only tranche.

## Runtime-family classification

| Family | V2 classification | Boundary |
|---|---|---|
| Module admission / definitions | REUSABLE_DEPENDENCY | Shared module and definition substrate exists. |
| Role/capability policy | REUSABLE_DEPENDENCY | Roles + Abilities/Auth remain canonical. |
| Plan definition / revision | MISSING | Requires Membership owner definition. |
| Entitlement resolution preview | MISSING | Requires owner read service; must separate configured policy from effective provider/runtime truth. |
| Provider mapping / health | REUSABLE_DEPENDENCY / OUT_OF_SURFACE | Integrations can expose references/health; settlement remains provider-owned. |
| Charge/refund/subscription lifecycle | OUT_OF_SURFACE | Provider/payment execution, not first slice. |
| Role grants from entitlement | MISSING / BLOCKED FOR MUTATION | Later high-impact path with Policy and reconciliation proof. |
| Reconciliation/idempotency | MISSING | Later mutation tranche; diagnostics may expose capability/readiness only. |

## Smallest bounded first runtime slice

**Read-only Membership Plan Definition + entitlement/provider-reference preview.**

Candidate later paths:
- `frameworks/Modules/Membership/MembershipModule.php`
- `frameworks/Modules/Membership/MembershipDefinition.php`
- `frameworks/Modules/Membership/MembershipReadService.php`
- `frameworks/Modules/Membership/MembershipAbilityHandler.php`
- `tests/Unit/Modules/Membership/`
- real WordPress integration for role/policy and multisite scope

The slice may enumerate plans, validate role/provider references, compute a non-mutating entitlement preview and report unavailable/degraded provider state. It must not charge, refund, cancel, renew, grant/revoke roles, or persist entitlement/settlement truth.

## Required future exact-head evidence

- deterministic plan/reference validation;
- server-authoritative actor/resource policy tests;
- proof local configured state cannot fabricate provider settlement or active entitlement truth;
- explicit provider unavailable/degraded states;
- multisite/site ownership and role-scope tests;
- bounded plan/member preview performance tests;
- Architecture Guards, PHP quality, Platform Compatibility Matrix and applicable package/browser gates.

## Security, rollback and portability

Provider credentials remain outside Surface 15 and never enter definitions/export. Portability contains stable plan/role/provider references with import remapping/validation. The read-first slice creates no membership/payment mutations, so rollback is module disablement/removal.

## Non-certifications

No charge/refund/subscription operation, role grant/revoke, entitlement mutation, external provider call, runtime/product certification, production migration, deployment or release is authorized. Atomic lifecycle remains `UX_CONTRACT_COMPLETE`.
