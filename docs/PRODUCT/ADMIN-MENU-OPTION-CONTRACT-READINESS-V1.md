# Admin Menu — Atomic Option Contract Readiness V1

Surface: **11 / Admin Menu**  
Issue: **#494**  
Mode: planning/contract only; `runtime_allowed=false`; `product_parity_allowed=false`.

## Decision

A canonical `config/product/option-contracts/admin-menu.json` must **not** be authored in this lane yet.

The branch-local Options Bank seed contains 8 candidate records, but all 8 remain `UNREVIEWED` and the canonical main Bank state remains `UNSEEDED / 0`. The option-contract schema can be satisfied syntactically, but doing so before zero-unresolved Bank review would manufacture lifecycle evidence and conflict with repository governance.

## Current candidate families

- target menu/submenu identity;
- bounded presentation transforms;
- visibility policy;
- safe custom menu items;
- reusable menu profile assignment;
- admin-bar transforms;
- preview-as actor diagnostics;
- restore/recovery policy.

## Prerequisites for canonical contract

1. Complete native hook/menu-array and admin-bar timing review.
2. Complete site/user/network-admin scope and multisite semantics.
3. Resolve semantic ownership against Roles/Capabilities, Admin Theme, Settings Pages and routing owners.
4. Record rejected-unsafe dispositions for arbitrary callbacks/PHP/HTML/script execution.
5. Review all Bank records to zero unresolved and promote the Bank through the normal Supervisor path.
6. Only then project reviewed source records into a schema-valid canonical Atomic Option Contract.

## Safety requirements

- Menu visibility never substitutes for Policy/Ability authorization.
- Custom targets use validated internal routes or safe URLs only.
- No raw callback, class, PHP, HTML or script execution input.
- Preview diagnostics must redact/bound target-user information.
- Restore uses canonical revision/history state rather than hidden duplicate configuration.

## Outcome

This issue can close its V1 planning deliverable without falsely promoting `BANK_REVIEWED`, `OPTION_CONTRACT_COMPLETE`, runtime certification or product parity. A later exact-main gate must authorize canonical contract promotion.