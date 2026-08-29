# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-08-29**  
Implementation branch: `implementation/baseline-adoption-gate`  
Planning authority: `planning/master-architecture` through ADR-0213  
Implementation decisions: through **ADR-0216**  
Project classification: **`GREENFIELD_IMPLEMENTATION_WITH_EXISTING_ACCEPTED_PLAN`**  
Execution mode: **`IMPLEMENTATION_GATED`**  
Lifecycle: **`IMPLEMENTING_PLATFORM_FOUNDATION`**  
Development approval: **GOV-OWNER-CONSENT-001 ACTIVE / source scope 56/56**

## Approval boundary

Owner authorized:

`Implementation Baseline / Adoption Gate → Machine-enforced architecture guards → Milestone 1 Platform Foundation → module development`.

Source implementation, development/test tooling, CI and milestone-scoped schemas/tests are authorized. Production deployment, destructive live-site/customer-data operations, chargeable/irreversible provider side effects and separately privileged release/merge operations remain excluded unless explicitly authorized.

## Current product truth

Accepted scope remains **56/56 Exhaustive**, Multisite **56/56**, AI Prompt **56/56**, with no known planning or semantic-owner gap after WP118 / ADR-0213.

## WP119 — DONE / PASS / ADR-0214

Greenfield implementation baseline accepted. Minimum WordPress 6.9; PHP 8.2; MySQL 8.0+/MariaDB 10.11+; Composer/PSR-4 and Node/build direction locked.

## WP120 — DONE / PASS / ADR-0215

Machine-readable 56-surface, ownership, P01–P40, Ability/storage/Multisite/invalidation/provider/destructive/AI guards implemented.

Earlier GitHub-hosted runner-dispatch degradation is **superseded for current source evidence**: a later hosted runner executed successfully under WP121. Do not retain the old `steps=null` state as current CI truth.

## WP121 — CURRENT / IMPLEMENTING

Canonical status: `docs/IMPLEMENTATION/PLATFORM-FOUNDATION-WP121.md`.

Implemented platform foundation now includes:
- Bootstrap / Kernel / Service Registry / Module lifecycle;
- Definition / ExecutionContext / Policy / Ability / Event core;
- Audit foundation;
- backend-neutral JobService logical state/idempotency/retry/cancellation;
- bounded Action Scheduler capability probe;
- Secrets Vault reference contract;
- Asset Registry;
- Integration Registry;
- Definition persistence abstraction + migration contracts/reference gateways;
- WordPress Capability + Abilities API bridge;
- owner-mandated engineering contract / ADR-0216.

## ADR-0216 engineering contract — ACTIVE

Mandatory for future code:
- namespace `WPEssential`;
- PSR-4 source root `frameworks/`;
- legacy parallel `src/` removed;
- globals `wpessential_*`;
- global constants `WPE_*`;
- exact custom filters `wpesential/apply_*`;
- custom actions `wpessential/hook_*`;
- one canonical AJAX action/typed allowlisted dispatcher;
- centralized nonce operations `apply/create/update/reset/delete`;
- compile-on-write dynamic WordPress registrations;
- bounded/redacted flow-trace / Runtime Observatory foundation;
- machine enforcement through engineering validator + smoke.

The asymmetric filter spelling is intentional public API and must not be silently changed.

## Hosted evidence — GREEN

GitHub Actions run **33258399467** on `ubuntu-24.04` / PHP **8.2** for source commit `8ea9ee13e9081beb4d3599c69051a2144c39dedf` completed **SUCCESS**.

Hosted PASS:
- Composer metadata validation;
- architecture validator;
- engineering-contract validator;
- PHP syntax;
- complete **8/8 smoke suites**.

A preceding diagnostic run found one stale test assertion (`not exposed` vs canonical `channel_not_exposed`). Only the assertion was corrected; authorization behavior was not weakened. Post-fix hosted CI is green.

Documentation-only commits after the green source commit do not alter that verified source tree behavior.

## Important non-certifications

Do **not** overclaim the following:
- `InMemoryCompiledRegistrationStore` is reference/test-only, not production persistence;
- `PersistentDefinitionRepository` currently has abstraction/reference gateway evidence, not certified production WordPress/MySQL storage;
- Action Scheduler capability-ready ≠ coexistence/Multisite/backend certified;
- Runtime Observatory trace model ≠ complete admin visualization/retention product;
- hosted smoke ≠ production deployment certification.

No live provider call, production deployment, destructive live-site/customer-data mutation, payment/communication side effect, reset/restore/migration against production or irreversible external operation occurred.

## Current next action

Continue WP121 with bounded evidence-backed tranches:
1. persistent atomic compiled-registration generation store + last-known-good/recovery;
2. production Definition/Audit persistence adapters + bounded migrations;
3. real WordPress AJAX/nonce/Policy integration fixtures;
4. Action Scheduler coexistence/backend evidence;
5. durable Job attempt/lease/checkpoint contracts after backend evidence;
6. minimal Platform admin shell + Runtime Observatory graph/diagnostics UI;
7. 10K/100K compiled-registration performance evidence;
8. first business-module tranche only after shared-foundation readiness gate.

Repository evidence overrides conversational memory.
