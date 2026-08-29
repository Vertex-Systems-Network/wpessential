# WPEssential — WP121 Platform Foundation

Status: **CURRENT / IMPLEMENTING — first bounded tranche active**  
Date: **2026-08-29**  
Approval: `GOV-OWNER-CONSENT-001` ACTIVE  
Predecessors: WP119 PASS / ADR-0214; WP120 PASS / ADR-0215

## Goal

Establish the minimum production-source platform foundation required before business modules can safely exist.

WP121 is not a feature-surface implementation milestone. It builds shared platform contracts that every later module must reuse.

## First bounded tranche

Implemented source:
- `wpessential.php` WordPress plugin entrypoint with compatibility/autoload fail-safe notices;
- `src/Bootstrap/Plugin.php` platform bootstrap;
- `src/Contracts/ServiceRegistryInterface.php`;
- `src/Contracts/ModuleInterface.php`;
- `src/Kernel/ServiceRegistry.php`;
- `src/Kernel/Kernel.php`;
- `src/Platform/Modules/ModuleManifest.php`;
- `src/Platform/Modules/ModuleState.php`;
- `src/Platform/Modules/ModuleRegistry.php`;
- `tests/Smoke/kernel-smoke.php`.

## Enforced behavior in tranche 1

- no third-party DI container at baseline;
- one platform Service Registry;
- duplicate service IDs fail closed;
- one Module Registry;
- immutable module manifest identity/version/edition/dependency metadata;
- deterministic dependency boot order;
- missing dependency produces `degraded`, not fatal module execution;
- degraded dependency state propagates transitively;
- a dependency registered before final kernel boot can recover a prior diagnostic degraded state;
- circular module dependency fails explicitly;
- module registration is closed after kernel boot;
- module `register()` phase occurs before module `boot()` phase;
- WordPress/PHP compatibility is checked before loading platform runtime;
- missing packaged Composer autoload fails safely with an admin notice rather than a class fatal.

## Verification

Isolated PHP runtime used: PHP 8.4.23.

Executed:
- PHP syntax checks for the tranche source/test files: **PASS**;
- `php tests/Smoke/kernel-smoke.php`: **PASS**.

Smoke coverage currently includes:
1. deterministic base→child dependency order;
2. successful kernel boot and module states;
3. direct missing-dependency degradation;
4. transitive degraded-dependency propagation;
5. late dependency registration recovery before boot;
6. circular dependency rejection.

Local Composer CLI was unavailable in the isolated execution environment, so Composer command execution is **NOT CLAIMED**. Hosted GitHub Actions also remains runner-dispatch degraded before step execution.

## Current exclusions

Not yet implemented in tranche 1:
- Definition Repository;
- Context Resolver;
- Capability/Policy Engine;
- WordPress Abilities bridge;
- typed Event Bus;
- Audit/Observability runtime;
- JobService backend/coexistence spike;
- Secrets Vault;
- Asset Registry;
- Integration Registry;
- migrations/database schema;
- admin React shell;
- CPT/Taxonomy or any other business module.

No production deployment, live provider call, DB mutation or destructive live-site operation is part of this tranche.

## Next WP121 tranche

Proceed in this order unless repository evidence forces a correction:
1. Definition identity/repository contracts and in-memory/reference implementation;
2. request/context principal contract;
3. Capability/Policy decision contract;
4. Ability descriptors/registry independent of channel exposure;
5. typed Event Bus;
6. audit event contract;
7. JobService contract and only then bounded Action Scheduler coexistence spike;
8. Vault/Assets/Integrations contracts;
9. minimal Platform surface/admin bootstrap after server-side ownership core is stable.

Every tranche extends FAST smoke/negative coverage before moving onward.
