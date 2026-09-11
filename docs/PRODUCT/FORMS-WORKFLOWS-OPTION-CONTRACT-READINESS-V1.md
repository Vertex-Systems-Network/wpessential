# Forms & Workflows — Atomic Option Contract Readiness V1

Surface: **17 / Forms & Workflows**  
Issue: **#500**  
Mode: planning/contract only; `runtime_allowed=false`; `product_parity_allowed=false`.

## Decision

Do **not** promote a canonical `config/product/option-contracts/forms-workflows.json` yet. The branch contains 8 candidate Bank records, all still `UNREVIEWED`, while canonical main Bank truth remains `UNSEEDED / 0`. A canonical contract now would overstate reviewed source coverage.

## Candidate families

- versioned form identity/lifecycle;
- canonical field configuration and validation;
- server-authoritative calculations;
- submission authorization, abuse and duplicate policy;
- entry storage, retention and privacy;
- canonical workflow action/target-owner mapping;
- idempotency, retry, checkpoint and replay policy;
- Vault-backed secret references.

## Prerequisites for canonical contract

1. Resolve form field ownership against the canonical Fields/Control Registry.
2. Define server-authoritative calculation grammar without arbitrary executable expressions.
3. Define submission authorization, CSRF, abuse/rate-limit and duplicate/idempotency semantics.
4. Define entry privacy, retention, deletion and multisite scope.
5. Define allowlisted action/provider ownership and failure semantics.
6. Define retry/checkpoint/replay behavior without duplicate external side effects.
7. Keep secrets as Vault references only and exclude them from ordinary portability.
8. Review all Bank records to zero unresolved and promote through Supervisor reconciliation.

## Safety requirements

- No arbitrary PHP/JavaScript/code-expression execution.
- Workflow actions target registered Abilities/providers only.
- No live email/SMS/payment/webhook or other external side effect is authorized by this lane.
- Submission and entry operations require server-side authorization, validation and abuse protection.
- Secrets/tokens never enter frontend bootstrap data, logs or portable definitions.

## Outcome

The V1 planning lane may close without `BANK_REVIEWED`, canonical option-contract promotion, runtime certification or product-parity certification.