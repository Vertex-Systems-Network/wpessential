# WPEssential — Machine-enforced Architecture Guards

Status: **WP120 — IMPLEMENTED / INDEPENDENT INVARIANTS PASS / HOSTED CI INFRA_BLOCKED**  
Date: **2026-08-29**  
Approval: `GOV-OWNER-CONSENT-001` ACTIVE

## Implemented guard surfaces

Executable architecture enforcement now exists in:
- `config/architecture/surfaces.json`;
- `config/architecture/ownership-contracts.json`;
- `config/architecture/system-patterns.json`;
- `config/architecture/operation-guards.json`;
- `tools/architecture/validate.php`;
- `composer.json` command `architecture:validate` / FAST alias;
- `.github/workflows/architecture-guards.yml`.

## Guarded invariants

The validator enforces, at minimum:
- exactly 56 canonical surface IDs `1..56`;
- unique surface keys;
- unique canonical admin routes;
- one valid canonical suite per surface;
- canonical semantic owners and valid delegated surface references;
- competitive overlays cannot register canonical surfaces, parallel kernels/admin apps or owner-Ability bypasses;
- exactly P01..P40 system pattern IDs and only valid canonical surface references;
- mutation through canonical owner + Policy;
- storage ownership resolution;
- Multisite authority/clone/cross-site restrictions;
- invalidation ownership for derived caches/indexes;
- external provider authority + unknown outcome semantics;
- destructive operations require impact preview + recovery contract;
- AI cannot use raw PHP/SQL/shell, Vault secret prompt context or hidden privileged paths and requires explicit mutation Ability allowlisting.

## Verification evidence

### Independent invariant check
A separate local verification of the canonical manifest sets passed:
- 56 surfaces;
- 56 unique canonical keys;
- exactly-once suite coverage;
- 40 pattern IDs;
- critical semantic owner IDs all resolve to canonical surfaces.

This independent check is supporting evidence; it does not replace execution of the repository PHP validator.

### GitHub hosted CI
Three hosted-run attempts were made, including an explicit `ubuntu-24.04` runner pin and a retry.

Observed signature:
- workflow created;
- job immediately completed as failure;
- no runner assigned;
- no job steps available;
- no job logs produced.

This means checkout/PHP/validator code never executed. It is classified as:

`EXTERNAL CI RUNNER DISPATCH FAILURE / INFRA_BLOCKED`

It is **not** classified as an architecture-validator code failure and is **not** reported as a green hosted gate.

## Gate decision

WP120 source/config implementation is present, but WP120 remains **PARTIALLY_COMPLETE / INFRA_BLOCKED** until the canonical repository validator executes successfully in an authorized faithful environment and the result is recorded.

Per ADR-0214/WP119, ordinary Milestone 1 Platform Foundation runtime source does **not** begin while this blocking architecture gate is unresolved.

## Safe next action

1. execute `php tools/architecture/validate.php` against the exact implementation branch checkout on a working runner/local faithful checkout;
2. if it fails, fix the manifest/validator and rerun;
3. if it passes, record WP120 PASS and accept the machine-guard implementation decision;
4. then start Milestone 1 Platform Foundation.

Do not weaken or remove the guard merely to bypass hosted CI infrastructure failure.