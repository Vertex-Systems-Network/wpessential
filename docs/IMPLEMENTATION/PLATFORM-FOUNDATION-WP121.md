# WPEssential — WP121 Platform Foundation

Status: **CURRENT / IMPLEMENTING — server-side foundation core active**  
Date: **2026-08-29**  
Approval: `GOV-OWNER-CONSENT-001` ACTIVE  
Predecessors: WP119 PASS / ADR-0214; WP120 PASS / ADR-0215

## Goal

Establish the minimum production-source platform foundation required before business modules can safely exist.

WP121 is not a feature-surface implementation milestone. It builds shared platform contracts that every later module must reuse.

## Tranche 1 — Bootstrap / Kernel / Modules

Implemented:
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

Enforced behavior:
- no third-party DI container at baseline;
- duplicate service IDs fail closed;
- one Module Registry;
- immutable module manifest identity/version/edition/dependency metadata;
- deterministic dependency boot order;
- direct and transitive missing-dependency degradation;
- late dependency registration can recover a pre-boot diagnostic degraded state;
- circular module dependencies fail explicitly;
- no module registration after kernel boot;
- all module `register()` phases precede module `boot()` phases;
- compatibility/autoload failures fail safely before platform runtime load.

## Tranche 2 — Definitions / Context / Policy / Abilities / Events

Implemented:
- `DefinitionStatus` + immutable versioned `Definition` contract;
- canonical payload SHA-256 calculation independent of associative key order;
- `DefinitionRepositoryInterface` + `InMemoryDefinitionRepository` reference implementation;
- monotonic definition revision guard;
- dependency/dependent lookup contract;
- `Principal`, `ExecutionChannel`, `ExecutionContext`;
- `CapabilityCheckerInterface`;
- `AuthorizationRequest`, `PolicyDecision`, `PolicyEngine`;
- `AbilityDescriptor`, `AbilityHandlerInterface`, `AbilityRegistry`;
- explicit Ability channel exposure (`internal`, `ui`, `rest`, `cli`, `workflow`, `ai`) with registration not implying exposure;
- typed synchronous `DomainEvent` + `EventBus`;
- `tests/Smoke/platform-core-smoke.php`.

Authorization behavior currently enforced:
- unauthenticated principals deny;
- capability denial denies;
- Ability channel must be explicitly allowed;
- Ability owner surface must be canonical `1..56`;
- registered handler executes only after Policy approval;
- UI/REST/CLI/Workflow/AI exposure is not inferred from internal registration.

## Verification

Isolated PHP runtime: PHP **8.4.23**.

Executed and PASS:
- PHP syntax checks for WP121 source/smoke files;
- `php tests/Smoke/kernel-smoke.php`;
- `php tests/Smoke/platform-core-smoke.php`.

Kernel smoke covers:
1. deterministic dependency order;
2. successful boot/state transition;
3. direct missing-dependency degradation;
4. transitive degraded propagation;
5. late dependency recovery before boot;
6. circular dependency rejection.

Platform-core smoke covers:
1. canonical Definition checksum across associative key ordering;
2. dependent lookup;
3. stale revision rejection;
4. authorized Ability execution;
5. registration-does-not-imply-UI-exposure;
6. capability denial;
7. unauthenticated denial;
8. typed Event dispatch and stable occurrence timestamp.

Local Composer CLI was unavailable in the isolated execution environment, so Composer command execution is **NOT CLAIMED**. Composer FAST configuration is present. Hosted GitHub Actions remains runner-dispatch degraded before any job step execution.

## Current exclusions

Not yet implemented:
- persistent WordPress/DB Definition Repository adapter + migrations;
- resource-aware Policy rules beyond the initial capability gate;
- WordPress core Abilities bridge;
- audit/observability runtime;
- JobService contract/backend/coexistence spike;
- Secrets Vault;
- Asset Registry;
- Integration Registry;
- admin React shell;
- CPT/Taxonomy or any other business module.

The current in-memory Definition Repository is a reference/test implementation, not accepted production persistence.

No production deployment, live provider call, persistent DB mutation or destructive live-site operation is part of these tranches.

## Next WP121 tranche

Proceed in this order unless repository evidence forces a correction:
1. audit record/logger contract tied to ExecutionContext/correlation IDs;
2. JobService contract, job identity/idempotency/state/retry semantics;
3. bounded Action Scheduler coexistence spike before backend adoption;
4. Secrets Vault contract and redaction-safe secret references;
5. Asset + Integration registries;
6. persistent Definition Repository + migration design/fixtures;
7. WordPress Capability checker / Abilities bridge;
8. minimal Platform surface/admin bootstrap only after server-side ownership core is stable.

Every tranche extends FAST smoke/negative coverage before moving onward.
