# WPEssential — WP121 Platform Foundation

Status: **CURRENT / IMPLEMENTING — server-side foundation core active**  
Date: **2026-08-29**  
Approval: `GOV-OWNER-CONSENT-001` ACTIVE  
Predecessors: WP119 PASS / ADR-0214; WP120 PASS / ADR-0215

## Goal

Establish the minimum production-source platform foundation required before business modules can safely exist. WP121 builds shared contracts; it is not a business-feature milestone.

## Tranche 1 — Bootstrap / Kernel / Modules

Implemented:
- safe WordPress plugin entrypoint/bootstrap;
- Service Registry contract/implementation;
- Module contract, immutable manifest, lifecycle state and Module Registry;
- Kernel register→boot lifecycle;
- compatibility/autoload fail-safe notices;
- `tests/Smoke/kernel-smoke.php`.

Enforced: duplicate service IDs fail closed; deterministic dependency order; direct/transitive degraded dependencies; pre-boot late-dependency recovery; cycle rejection; no module registration after kernel boot; all module registration phases precede boot phases.

## Tranche 2 — Definitions / Context / Policy / Abilities / Events

Implemented:
- immutable/versioned Definition + canonical SHA-256 payload checksum;
- Definition Repository contract + in-memory reference implementation;
- monotonic revision guard + dependents lookup;
- Principal / ExecutionContext / explicit execution channels;
- Capability checker + initial Policy decision engine;
- Ability descriptor/handler/registry with registration != channel exposure;
- typed synchronous DomainEvent/EventBus;
- `tests/Smoke/platform-core-smoke.php`.

Authorization currently enforces unauthenticated denial, capability denial, explicit channel exposure and canonical owner surface IDs before Ability handler execution.

## Tranche 3 — Audit / Observability foundation + JobService logical contract

Implemented Audit foundation:
- `AuditOutcome`;
- immutable `AuditRecord` tied to `ExecutionContext`, canonical owner surface, action/resource/outcome/reason/correlation context;
- retention/privacy classifications;
- bounded recursive `AuditMetadataSanitizer`;
- redaction of password/secret/token/Authorization/Cookie/API-key/private-key/card/reset-token/signed-URL metadata keys;
- `AuditLoggerInterface`;
- append-oriented `InMemoryAuditLogger` reference adapter with duplicate committed UUID rejection.

Audit boundaries retained:
- Audit is not Analytics or domain-history ownership;
- raw provider/webhook/business payloads are not copied by default;
- secret values are never intentionally persisted in Audit metadata;
- the in-memory logger is reference/test-only, not production persistence or tamper-proof storage.

Implemented JobService logical foundation:
- `JobState` logical states independent of backend status;
- typed `JobFailureClass` with explicit retryable vs permanent vs `unknown_external_outcome` semantics;
- `JobIdempotencyMode` (`natural`, `stable_key`, `checkpoint`, `reconciliation`);
- bounded `RetryPolicy` with max attempts/backoff ceiling;
- registered `JobType` with canonical owner surface, Ability handler reference, resource classes and cancellation support;
- mutable logical `JobRecord` state machine;
- `JobServiceInterface`;
- `InMemoryJobService` reference adapter;
- stable-key dedupe scoped by network/site/type + hashed logical idempotency identity;
- unknown external outcome → `blocked` reconciliation state, never blind retry;
- retryable failure → `retry_wait` only below attempt ceiling;
- running cancel → `cancel_requested`; `cancelled` only after cooperative confirmation;
- pending cancellable work may cancel before execution;
- `tests/Smoke/audit-jobs-smoke.php`.

The JobService remains backend-neutral. No module may depend on Action Scheduler types/status/tables through this tranche.

## Verification

Isolated PHP runtime: PHP **8.4.23**.

Executed and PASS:
- PHP syntax checks for tranche 1–3 source/smoke files;
- `php tests/Smoke/kernel-smoke.php`;
- `php tests/Smoke/platform-core-smoke.php`;
- isolated exact tranche-3 audit/jobs behavioral smoke before branch commit.

Tranche-3 smoke verifies:
1. Audit secret/Authorization redaction;
2. Audit correlation preservation;
3. duplicate committed Audit UUID rejection;
4. stable-key logical Job deduplication;
5. retryable failure enters retry_wait below ceiling;
6. attempt ceiling produces failed_final;
7. unknown external outcome blocks for reconciliation;
8. running cancellation is cooperative;
9. pending cancellation is immediate when supported;
10. stable-key types reject missing idempotency identity.

Composer FAST now includes architecture validation plus kernel, platform-core and audit/jobs smoke suites. Local Composer CLI remains unavailable in the isolated environment, so local Composer execution is **NOT CLAIMED**.

GitHub-hosted Actions still fails before runner assignment and remains **INFRA_DEGRADED**, not green. The workflow is configured to execute Composer validation, architecture guards, PHP syntax and all smoke suites once a runner actually starts.

## Current exclusions

Not yet implemented/certified:
- Action Scheduler backend adapter/coexistence runtime;
- Job Attempt durable journal, leases/claims/heartbeat/fairness/backpressure/persistence;
- persistent Audit PT-D store/index/retention/migrations;
- persistent WordPress/DB Definition Repository + migrations;
- resource-aware Policy beyond initial capability gate;
- WordPress core Abilities bridge;
- Secrets Vault;
- Asset Registry;
- Integration Registry;
- admin React shell;
- CPT/Taxonomy or any other business module.

All current in-memory repositories/services are reference/test implementations, not accepted production persistence.

No production deployment, live provider call, persistent DB mutation or destructive live-site operation is part of these tranches.

## Next WP121 tranche

1. run bounded **Action Scheduler coexistence/backend spike** without exposing backend semantics as WPE contracts;
2. add Job Attempt/claim/lease/checkpoint contract only after spike evidence clarifies adapter needs;
3. Secrets Vault contract + redaction-safe secret references;
4. Asset + Integration registries;
5. persistent Definition/Audit repository and migration design/fixtures;
6. WordPress Capability checker / Abilities bridge;
7. minimal Platform surface/admin bootstrap after server-side ownership core is stable.

Every tranche extends FAST smoke/negative coverage before moving onward.