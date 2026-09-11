# Membership — Atomic Option Contract Readiness V1

Surface: **15 / Membership**  
Issue: **#498**  
Mode: planning/contract only; `runtime_allowed=false`; `product_parity_allowed=false`.

## Decision

Do **not** promote a canonical `config/product/option-contracts/membership.json` yet. The branch contains 8 candidate Bank records, all still `UNREVIEWED`, while canonical main Bank truth remains `UNSEEDED / 0`. A schema-valid file before reviewed source truth would incorrectly imply lifecycle maturity.

## Candidate families

- membership plan identity/lifecycle;
- free, one-time and recurring pricing model;
- active/expired/cancelled lifecycle states;
- upgrade/downgrade/scheduled change policy;
- content/access restriction rules;
- coupon/discount policy;
- provider transaction/reconciliation reference;
- membership/revenue reporting summary.

## Prerequisites for canonical contract

1. Separate membership entitlement ownership from payment-provider execution.
2. Define deterministic lifecycle/expiration/change semantics and idempotency boundaries.
3. Resolve restriction authorization with canonical Policy/Ability ownership.
4. Define discount/pricing representation without duplicating payment-provider logic.
5. Define provider transaction references as immutable/redacted reconciliation evidence, not executable payment controls.
6. Define reporting privacy, multisite scope and bounded aggregation.
7. Review all Bank records to zero unresolved and promote through Supervisor reconciliation.

## Safety requirements

- No live charge, refund, subscription mutation or money movement in this lane.
- Membership visibility/restrictions never substitute for server-side authorization.
- Provider credentials and payment tokens remain outside Membership definitions and portable exports.
- External transaction references are opaque identifiers with redacted diagnostics.
- Plan changes must be previewable/recoverable before any later runtime execution is authorized.

## Outcome

The V1 planning lane may close without `BANK_REVIEWED`, canonical option-contract promotion, runtime certification or product-parity certification.