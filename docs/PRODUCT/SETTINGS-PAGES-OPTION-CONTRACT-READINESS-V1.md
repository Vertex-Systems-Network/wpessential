# Settings Pages — Atomic Option Contract Readiness V1

Surface: **12 / Settings Pages**  
Issue: **#495**  
Mode: planning/contract only; `runtime_allowed=false`; `product_parity_allowed=false`.

## Decision

Do **not** create a canonical `config/product/option-contracts/settings.json` yet. The branch has an 8-record candidate seed, but every record remains `UNREVIEWED` and canonical main Bank truth is `UNSEEDED / 0`. Syntactic schema validity alone is not sufficient evidence for lifecycle promotion.

## Candidate families

- page identity/navigation;
- site/network/user scope;
- tabs/sections/panels;
- typed storage mode;
- expert autoload policy;
- defaults/inheritance/effective values;
- canonical Fields/Control Registry composition;
- Vault-backed secret routing.

## Prerequisites for canonical contract

1. Resolve site/network/user persistence precedence and ownership.
2. Complete current WordPress option/autoload semantics review.
3. Complete network-admin capability and user-meta privacy semantics.
4. Confirm portability boundaries and Vault-only secret handling.
5. Resolve semantic ownership against Fields, User Profile, Vault and Admin Menu.
6. Review all Bank records to zero unresolved, then promote through Supervisor reconciliation.

## Safety requirements

- All value validation/sanitization is server authoritative.
- Page visibility never replaces mutation authorization.
- Secret material is referenced through the canonical Vault owner and excluded from ordinary exports.
- No arbitrary callbacks, executable PHP or unsafe HTML/script configuration.
- User/network/site scopes must never silently fall through to another persistence owner.

## Outcome

The V1 planning lane may close without `BANK_REVIEWED`, `OPTION_CONTRACT_COMPLETE`, runtime certification or product-parity promotion. Canonical contract generation remains a later exact-main gate.