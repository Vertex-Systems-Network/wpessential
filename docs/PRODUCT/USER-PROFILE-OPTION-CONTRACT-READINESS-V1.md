# User Profile — Atomic Option Contract Readiness V1

Surface: **14 / User Profile**  
Issue: **#497**  
Mode: planning/contract only; `runtime_allowed=false`; `product_parity_allowed=false`.

## Decision

Do **not** promote a canonical `config/product/option-contracts/profiles.json` yet. The branch contains 8 normalized candidate records, but all remain `UNREVIEWED` and canonical main Bank truth remains `UNSEEDED / 0`. Schema validity without reviewed source truth would be a false lifecycle promotion.

## Candidate families

- native/custom profile field schema;
- per-field privacy and editability;
- public profile slug and collision behavior;
- public profile visibility policy;
- registration verification/approval policy;
- login/logout/reset presentation integration;
- directory query and filters;
- versioned profile-definition portability.

## Prerequisites for canonical contract

1. Resolve canonical user-meta/profile field ownership against Fields and WordPress user APIs.
2. Define server-authoritative per-field view/edit authorization and privacy defaults.
3. Complete public slug collision, rewrite and multisite behavior review.
4. Separate authentication presentation from credential/session ownership.
5. Define directory privacy, pagination and query boundaries.
6. Define export/import redaction and environment remapping.
7. Review all Bank records to zero unresolved and promote through Supervisor reconciliation.

## Safety requirements

- Never export passwords, reset tokens, session material or secret authentication data.
- Resource-level authorization applies to every profile read/write operation.
- Registration, profile visibility and directory exposure must use privacy-preserving defaults.
- Authentication UI may compose registered auth flows but must not become a parallel credential store.
- Cross-site user data must not leak across multisite boundaries.

## Outcome

The V1 planning lane may close without `BANK_REVIEWED`, canonical option-contract promotion, runtime certification or product-parity certification.