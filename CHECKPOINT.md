# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-08-29**  
Implementation branch: `implementation/baseline-adoption-gate`  
Planning authority: `planning/master-architecture` through ADR-0213  
Implementation decisions: through **ADR-0217**  
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
- owner-mandated engineering contract / ADR-0216;
- **persistent atomic compiled-registration generation storage / ADR-0217**.

## ADR-0216 engineering contract — ACTIVE

Mandatory for future code:
- namespace `WPEssential`;
- PSR-4 source root `frameworks/`;
- no parallel legacy `src/` runtime tree;
- globals `wpessential_*`;
- constants `WPE_*`;
- exact custom filters `wpesential/apply_*`;
- custom actions `wpessential/hook_*`;
- one canonical AJAX action/typed allowlisted dispatcher;
- centralized nonce operations `apply/create/update/reset/delete`;
- compile-on-write dynamic WordPress registrations;
- bounded/redacted Runtime Observatory tracing;
- machine enforcement through engineering validator + smoke.

The asymmetric filter spelling is intentional public API and must not be silently changed.

## ADR-0217 atomic compiled registrations — ACCEPTED

Implemented and accepted boundaries:
- immutable compiled-generation rows separate from mutable active/fallback scope state;
- `(network_id, site_id)` storage identity with site/network isolation;
- transaction + compare-and-swap publication;
- no partial generation activation;
- deterministic SHA-256 manifest integrity;
- explicit corruption quarantine;
- verified last-known-good recovery;
- **generation high-watermark independent of active pointer**;
- corrupt/quarantined generation IDs permanently consumed and never reused;
- runtime reads the active compiled manifest rather than scanning historical definition populations.

Recovery may move the active pointer backward, but the immutable generation sequence never rolls backward.

## Hosted evidence — GREEN

Corrected source commit:
`de2bf6ea0299ce3900a0d6dff2d4646066137497`

GitHub Actions run **33261866811 / #89** completed **SUCCESS** on an actual GitHub-hosted Ubuntu runner.

Hosted PASS:
- MySQL 8.4 service startup;
- Composer metadata validation;
- canonical architecture validator;
- engineering-contract validator;
- PHP 8.2 syntax;
- **9/9 smoke suites**;
- real **MySQL 8.4 compiled-registration transactional integration**.

The immediately preceding run **33261668224 / #87** failed the smoke gate and stopped the MySQL integration. It exposed a generation-reuse edge case after corruption recovery. The code was corrected so sequencing now uses historical `MAX(generation) + 1`, including quarantined/corrupt generations, before ADR-0217 acceptance.

## Important non-certifications

Do **not** overclaim:
- 10K/100K compiled-registration performance certification is still pending;
- `NativeWpdbAdapter` source exists, but full WordPress runtime certification is pending;
- MySQL CI evidence certifies transactional storage/recovery behavior, not complete WordPress runtime integration;
- `PersistentDefinitionRepository` still lacks certified production WordPress/MySQL storage;
- persistent/tamper-evident Audit storage is pending;
- Action Scheduler capability-ready ≠ coexistence/Multisite/backend certified;
- real WordPress AJAX/nonce/Policy integration fixtures remain pending;
- Runtime Observatory admin graph/Policy/retention UI remains pending;
- business-module implementation has not started.

No live provider call, production deployment, destructive live-site/customer-data mutation, live production migration or irreversible external operation occurred.

## Current next action

Continue WP121 with bounded evidence-backed tranches:
1. production Definition/Audit persistence adapters + bounded migrations;
2. real WordPress AJAX/nonce/Policy integration fixtures;
3. Action Scheduler coexistence/backend evidence;
4. durable Job attempt/lease/checkpoint contracts after backend evidence;
5. minimal Platform admin shell + Runtime Observatory graph/diagnostics UI;
6. executable 10K/100K compiled-registration scale evidence;
7. shared-foundation readiness gate;
8. first business-module tranche only after that gate passes.

Repository evidence overrides conversational memory.
