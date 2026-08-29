# WPEssential — WP121 Platform Foundation

Status: **CURRENT / IMPLEMENTING — shared platform foundation active**  
Date: **2026-08-29**  
Approval: `GOV-OWNER-CONSENT-001` ACTIVE  
Predecessors: WP119 PASS / ADR-0214; WP120 PASS / ADR-0215  
Engineering contract: ADR-0216 ACCEPTED

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

Implemented a bounded adapter capability probe that distinguishes:
- absent;
- partially incompatible API surface;
- loaded but not initialized;
- capability-ready.

Capability readiness does **not** fabricate coexistence, packaging or Multisite certification. A real WordPress coexistence/backend runtime remains required before adopting Action Scheduler as a certified JobService backend.

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

Important: this is the **persistent repository abstraction**, not a certified production WordPress/MySQL table adapter. Real DB migrations/storage remain downstream work.

## Tranche 7 — WordPress Capability / Abilities bridge

Implemented:
- WordPress capability environment/checker;
- execution-context factory bound to the actual WordPress principal/site/network context;
- WordPress Abilities API bridge;
- explicit category and Ability exposure;
- REST exposure only when descriptor channels allow REST;
- capability re-check against the current WordPress user;
- canonical internal-to-core Ability name mapping + collision rejection;
- registration-hook timing guard.

Request-supplied execution context cannot impersonate a different WordPress principal.

## Tranche 8 — Owner-mandated engineering contract / ADR-0216

Implemented across all future development:
- namespace `WPEssential`;
- canonical PSR-4 production root `frameworks/`;
- retired legacy parallel `src/` tree;
- global functions `wpessential_*`;
- global constants `WPE_*` including `WPE_VERSION`, `WPE_AJAX_ACTION`, `WPE_NONCE_ACTION`, `WPE_DEBUG`;
- exact custom filter contract `wpesential/apply_*`;
- exact custom action contract `wpessential/hook_*`;
- one canonical AJAX action with allowlisted typed dispatcher;
- common nonce manager for `apply/create/update/reset/delete` operation scopes;
- compile-on-write WordPress registration model for CPT/taxonomy/metabox/settings-page definitions;
- bounded/redacted Runtime Observatory flow-trace foundation;
- machine engineering-contract validator;
- dedicated engineering-contract smoke suite.

Canonical architecture detail:
`docs/ARCHITECTURE/ENGINEERING-CONVENTIONS-AJAX-NONCE-COMPILED-REGISTRATION-OBSERVABILITY.md`.

### Compile-on-write boundary

`InMemoryCompiledRegistrationStore` is reference/test-only. Production readiness requires a persistent atomic active-generation store with site/network isolation, last-known-good recovery, checksum/corruption handling and executable 10K/100K performance evidence. Ordinary runtime requests must not scan/compile the historical definition population.

### Runtime Observatory boundary

Current source provides correlation identity, class/component nodes, call/data edges, ordered checkpoints, exact last-successful -> failed boundary and redaction. `WPE_DEBUG` is off by default. The admin graph/chart UI, Policy/retention controls and production diagnostics profile remain to be implemented.

## Hosted verification

GitHub Actions run **33258399467** executed on `ubuntu-24.04` with PHP **8.2** against source commit `8ea9ee13e9081beb4d3599c69051a2144c39dedf` and completed **SUCCESS**.

Verified hosted stages:
- Composer metadata validation: PASS;
- canonical architecture manifests: PASS;
- engineering-contract validator: PASS;
- PHP syntax across `frameworks/`, smoke suites and plugin entrypoint: PASS;
- complete smoke suite: PASS.

The complete smoke suite covers:
1. Kernel/module lifecycle;
2. Definition/Policy/Ability/Event core;
3. Audit/JobService;
4. Action Scheduler capability probe;
5. Vault/Assets/Integrations;
6. Definition persistence abstraction/migrations;
7. WordPress Abilities bridge;
8. engineering conventions, nonce/AJAX routing, compiled registrations and trace redaction/failure boundary.

A prior diagnostic run exposed one stale assertion in `platform-core-smoke.php`: it expected prose `not exposed` while the canonical Policy reason is `channel_not_exposed`. The assertion was corrected without changing or weakening authorization behavior; the subsequent hosted run is green.

Composer execution is now evidenced by hosted CI even though the earlier isolated local environment lacked Composer CLI.

## Current exclusions / not yet certified

- production WordPress/MySQL Definition storage adapter + actual schema migrations;
- persistent/tamper-evident Audit PT-D store/index/retention;
- persistent atomic compiled-registration generation store;
- 10K/100K registration-definition runtime/performance certification;
- durable Job attempt journal, leases/claims/heartbeat/fairness/backpressure/checkpoint persistence;
- Action Scheduler real coexistence/packaging/Multisite backend certification;
- resource-aware Policy beyond the current capability/context foundation;
- real WordPress AJAX integration/runtime fixtures for the new central gateway;
- Runtime Observatory admin UI/graph, Policy and retention controls;
- minimal Platform admin shell;
- CPT/Taxonomy/Metabox/Settings business-facing builders wired to persistent compiled registrations;
- any business module production tranche.

No production deployment, live provider call, destructive live-site/customer-data mutation or irreversible external operation was performed.

## Next WP121 work

1. implement persistent atomic compiled-registration store + recovery semantics;
2. implement production Definition/Audit persistence adapters and bounded migrations;
3. execute real WordPress AJAX/nonce/Policy integration fixtures;
4. execute bounded Action Scheduler coexistence/backend evidence;
5. deepen Job attempts/leases/checkpoints after backend evidence;
6. build minimal Platform admin shell + Runtime Observatory graph/diagnostic surface;
7. add executable scale evidence for compiled dynamic registrations;
8. only then gate the first business-module tranche against shared foundation readiness.

Every next tranche extends FAST/FULL evidence and keeps privileged production/live-provider boundaries intact.
