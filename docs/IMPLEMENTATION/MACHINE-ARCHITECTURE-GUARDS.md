# WPEssential — Machine-enforced Architecture Guards

Status: **WP120 — DONE / PASS / HOSTED CI DEGRADED**  
Date: **2026-08-29**  
Approval: `GOV-OWNER-CONSENT-001` ACTIVE

## Implemented guard surfaces

Executable architecture enforcement exists in:
- `config/architecture/surfaces.json`;
- `config/architecture/ownership-contracts.json`;
- `config/architecture/system-patterns.json`;
- `config/architecture/operation-guards.json`;
- `tools/architecture/validate.php`;
- Composer `architecture:validate` and FAST chain;
- `.github/workflows/architecture-guards.yml`.

The workflow now also covers the WP121 foundation paths (`src/**`, `tests/Smoke/**`, `wpessential.php`) and executes Composer metadata validation, architecture validation, PHP syntax checks and kernel smoke when a hosted runner is available.

## Guarded invariants

The validator enforces, at minimum:
- exactly 56 canonical surface IDs `1..56`;
- unique surface keys and canonical admin routes;
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

### Faithful isolated validator execution — PASS

Because the GitHub-hosted runner could not be assigned, the implementation-branch manifest contents and repository validator logic were retrieved through the connected GitHub source and faithfully materialized into an isolated PHP execution environment.

Runtime used: PHP **8.4.23** CLI.

Executed validator output:

```text
WPEssential architecture guard PASS
 - 56/56 canonical surfaces
 - unique keys/routes/suite ownership
 - semantic owners + competitive overlay no-bypass rules
 - P01..P40 canonical system pattern routing
 - Ability/storage/Multisite/invalidation/provider/destructive/AI guards
```

A separate invariant check also passed the 56-surface, unique-key, exactly-once-suite, P01..P40 and critical semantic-owner assertions.

This evidence verifies the validator/manifests themselves. It does not claim GitHub-hosted runner health.

### GitHub hosted CI — degraded infrastructure

Repeated hosted runs, retries and an explicit `ubuntu-24.04` pin fail before runner assignment. The latest WP121-aware run (`33247682122`) also completed with:
- `steps = null`;
- no runner execution;
- no logs.

Classification:

`EXTERNAL CI RUNNER DISPATCH FAILURE / INFRA_DEGRADED`

This is not a validator-code failure and is not reported as a green hosted gate. The scheduled CI recovery monitor remains useful and the hosted gate must be re-observed once GitHub assigns a runner.

## Gate decision

WP120 is accepted **PASS** for implementation sequencing because:
1. the machine-readable guard source is present;
2. the validator logic executes successfully against faithfully materialized implementation-branch manifests;
3. independent invariants agree with the validator result;
4. the remaining failure is demonstrably pre-step hosted-runner infrastructure.

Hosted CI remains an open infrastructure-quality item and cannot be called green until a runner executes the workflow steps successfully.

WP121 Platform Foundation may proceed in bounded source tranches while preserving the hosted-CI degradation prominently in milestone/release readiness.

## Non-negotiable rule

Do not weaken or remove architecture guards to obtain CI green. If a future hosted/local faithful checkout executes the guard and produces a code/manifest failure, that failure becomes a stop-line regression and WP121+ adoption pauses until corrected.
