# WPEssential — WP121 Platform Foundation

Status: **CURRENT / IMPLEMENTING — shared platform foundation active**  
Date: **2026-08-29**  
Approval: `GOV-OWNER-CONSENT-001` ACTIVE  
Predecessors: WP119 PASS / ADR-0214; WP120 PASS / ADR-0215  
Engineering contract: ADR-0216 ACCEPTED  
Atomic compiled registrations: **ADR-0217 ACCEPTED**

## Goal

Establish the shared production-source platform foundation required before business modules can safely exist. WP121 owns reusable kernel, security, persistence, jobs, integration, WordPress bridge and observability primitives; it is not a business-feature milestone.

## Tranche 1 — Bootstrap / Kernel / Modules

Implemented:
- safe WordPress plugin entrypoint/bootstrap;
- Service Registry contract/implementation;
- Module contract, immutable manifest, lifecycle state and Module Registry;
- Kernel register→boot lifecycle;
- compatibility/autoload fail-safe notices;
- deterministic dependency order;
- direct/transitive degraded dependency propagation;
- pre-boot late dependency recovery;
- cycle rejection;
- kernel smoke coverage.

## Tranche 2 — Definitions / Context / Policy / Abilities / Events

Implemented:
- immutable/versioned Definition + canonical SHA-256 payload checksum;
- Definition Repository contract + in-memory reference implementation;
- monotonic revision guard + dependents lookup;
- Principal / ExecutionContext / explicit execution channels;
- Capability checker + initial Policy decision engine;
- Ability descriptor/handler/registry with registration != channel exposure;
- typed synchronous DomainEvent/EventBus;
- negative revision/authentication/capability/channel coverage.

## Tranche 3 — Audit + JobService logical contract

Implemented Audit foundation:
- immutable Audit records tied to ExecutionContext/correlation/owner/action/resource/outcome;
- bounded recursive metadata sanitizer;
- secret/token/Authorization/Cookie/API-key/private-key/card/reset-token/signed-URL redaction;
- append-oriented Audit logger contract + in-memory reference adapter.

Implemented JobService logical foundation:
- backend-neutral job identity/state/failure/idempotency/retry/cancellation contracts;
- stable-key logical dedupe;
- retry ceiling semantics;
- unknown external outcome -> blocked reconciliation, never blind retry;
- cooperative cancellation;
- in-memory reference JobService.

No module may depend directly on Action Scheduler private status/table semantics.

## Tranche 4 — Action Scheduler bounded capability probe

Implemented a bounded adapter capability probe distinguishing absent, partial/incompatible, not initialized and capability-ready states.

Capability readiness does **not** fabricate coexistence, packaging or Multisite/backend certification.

## Tranche 5 — Secrets / Assets / Integrations

Implemented:
- Secret Vault contract + in-memory reference vault;
- secret references and redacted serialization;
- rotation/revocation semantics;
- rejection of secret-bearing metadata;
- Asset descriptor/registry with scope, load strategy, dependency graph and route discovery;
- Integration descriptor/registry with canonical Surface 23 transport ownership;
- provider external authority and unknown-outcome semantics retained.

These are shared contracts/reference adapters, not live-provider certification.

## Tranche 6 — Definition persistence abstraction + migrations

Implemented:
- Definition row codec;
- Definition table gateway contract;
- `PersistentDefinitionRepository` over the gateway abstraction;
- deterministic migration registry/runner/state store contracts;
- monotonic migration sequence;
- destructive migration recovery-plan guard;
- failed migration not marked applied;
- in-memory table/state gateways for behavioral evidence.

This remains the persistence abstraction; certified production WordPress/MySQL Definition storage is still downstream work.

## Tranche 7 — WordPress Capability / Abilities bridge

Implemented:
- WordPress capability environment/checker;
- execution-context factory bound to actual WordPress principal/site/network context;
- WordPress Abilities API bridge;
- explicit category and Ability exposure;
- REST exposure only when descriptor channels allow REST;
- current-user capability re-check;
- canonical internal-to-core Ability name mapping + collision rejection;
- registration-hook timing guard.

Request-supplied execution context cannot impersonate a different WordPress principal.

## Tranche 8 — Owner-mandated engineering contract / ADR-0216

Implemented across future development:
- namespace `WPEssential`;
- canonical PSR-4 production root `frameworks/`;
- retired legacy parallel `src/` tree;
- global functions `wpessential_*`;
- global constants `WPE_*` including `WPE_VERSION`, `WPE_AJAX_ACTION`, `WPE_NONCE_ACTION`, `WPE_DEBUG`;
- exact custom filter contract `wpesential/apply_*`;
- exact custom action contract `wpessential/hook_*`;
- one canonical AJAX action with allowlisted typed dispatcher;
- common nonce manager for `apply/create/update/reset/delete` operation scopes;
- compile-on-write WordPress registration model;
- bounded/redacted Runtime Observatory trace foundation;
- machine engineering-contract validator and smoke suite.

Canonical architecture detail:
`docs/ARCHITECTURE/ENGINEERING-CONVENTIONS-AJAX-NONCE-COMPILED-REGISTRATION-OBSERVABILITY.md`.

The asymmetric `wpesential` filter spelling is intentional public API.

## Tranche 9 — Persistent atomic compiled registrations / ADR-0217

Implemented:
- `DatabaseAdapterInterface` and `NativeWpdbAdapter` source boundary;
- explicit `CompiledRegistrationScope` for network/site identity;
- immutable compiled-generation persistence gateway;
- separate active/fallback pointer state;
- deterministic compiled-manifest checksum verification;
- `AtomicCompiledRegistrationStore`;
- WordPress `$wpdb`-style compiled registration persistence gateway;
- bounded non-destructive migration creating network-prefixed InnoDB generation/state tables;
- corruption quarantine and last-known-good recovery;
- stale compare-and-swap writer rejection;
- site isolation and network-vs-site isolation;
- historical generation high-watermark sequencing.

### Critical high-watermark rule

The active pointer is **not** sequence authority.

After recovery, the active pointer may move backward but new generation allocation remains:

`MAX(all historical generation IDs in scope) + 1`.

Corrupt/quarantined generation IDs remain permanently consumed and are never reused. This preserves immutable history and prevents a recovered pointer from overwriting the identity of a previously failed/corrupt generation.

### Atomicity boundary

Publication locks the scope state, verifies expected active state + historical next generation, persists the complete immutable manifest, changes the active/fallback pointer and commits as one transaction boundary. A partial generation must not become active.

The compiled manifest remains a **derived runtime projection**, not source configuration/business truth. Ordinary runtime loading consumes the active compiled manifest instead of scanning/compiling the historical definition population on every request.

## Hosted verification — current source GREEN

Corrected implementation commit:
`de2bf6ea0299ce3900a0d6dff2d4646066137497`

GitHub Actions run **33261866811 / #89** executed on a real GitHub-hosted Ubuntu runner and completed **SUCCESS**.

Verified hosted stages:
- MySQL 8.4 service: ready;
- Composer metadata: PASS;
- canonical architecture validator: PASS;
- engineering-contract validator: PASS;
- PHP 8.2 syntax: PASS;
- **9/9 smoke suites: PASS**;
- **MySQL 8.4 compiled-registration transactional integration: PASS**.

The MySQL integration verifies active/fallback persistence, site/network isolation, checksum-corrupt active recovery, immutable corrupt-generation high-watermark, post-recovery non-reuse, stale CAS rejection, invalid-JSON quarantine/recovery and immutable generation retention.

The immediately preceding run **33261668224 / #87** failed the smoke gate and therefore skipped MySQL integration. It exposed the generation-reuse defect. The implementation was corrected before ADR-0217 acceptance; the failure was not waived or relabeled green.

## Current exclusions / not yet certified

- 10K/100K compiled-registration performance certification;
- full WordPress runtime certification of `NativeWpdbAdapter`;
- production WordPress/MySQL Definition storage adapter + actual schema migration evidence;
- persistent/tamper-evident Audit PT-D store/index/retention;
- real WordPress AJAX/nonce/Policy end-to-end fixtures;
- durable Job attempt journal, leases/claims/heartbeat/fairness/backpressure/checkpoint persistence;
- Action Scheduler real coexistence/packaging/Multisite backend certification;
- resource-aware Policy beyond current capability/context foundation;
- Runtime Observatory admin UI/graph, Policy and retention controls;
- minimal Platform admin shell;
- business-facing CPT/Taxonomy/Metabox/Settings builders wired end-to-end;
- any business-module production tranche.

No production deployment, live provider call, destructive live-site/customer-data mutation, live production DB migration or irreversible external operation was performed.

## Next WP121 work

1. production Definition/Audit persistence adapters and bounded migrations;
2. real WordPress AJAX/nonce/Policy integration fixtures;
3. bounded Action Scheduler coexistence/backend evidence;
4. durable Job attempts/leases/checkpoints after backend evidence;
5. minimal Platform admin shell + Runtime Observatory graph/diagnostic surface;
6. executable 10K/100K compiled-registration scale evidence;
7. shared-foundation readiness gate;
8. first business-module tranche only after that gate passes.

Every next tranche extends FAST/FULL evidence and keeps privileged production/live-provider boundaries intact.
