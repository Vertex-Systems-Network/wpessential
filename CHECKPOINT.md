# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-08-29**  
Implementation branch: `implementation/baseline-adoption-gate`  
Planning authority: `planning/master-architecture` through ADR-0213  
Implementation decisions: through **ADR-0218**  
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
- WordPress Capability + Abilities API bridge;
- owner-mandated engineering contract / ADR-0216;
- persistent atomic compiled-registration generation storage / ADR-0217;
- **production-source Definition + Audit MySQL persistence adapters and persistent migration ledger / ADR-0218**.

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

Accepted boundaries include immutable scope-bound generations, separate active/fallback state, transactional CAS publication, checksum verification, corruption quarantine, last-known-good recovery and a historical generation high-watermark independent of pointer rollback. Corrupt/quarantined generation IDs are never reused.

## ADR-0218 Definition + Audit persistence — ACCEPTED

Implemented and accepted:
- explicit `DefinitionScope` network/site identity;
- PT-D Definition and dependency tables;
- `WpdbDefinitionTableGateway` over the existing canonical `PersistentDefinitionRepository`;
- transactional Definition row + dependency projection writes;
- revision CAS conflict rejection;
- scoped type/dependent queries and checksum verification;
- persistent `${base_prefix}wpe_migrations` migration state ledger;
- non-destructive migrations `007.create-definition-persistence` and `008.create-audit-ptd-store`;
- append-oriented PT-D Audit event store;
- unique event UUID, structured hot indexes, retention/privacy fields;
- metadata redaction before persistence;
- deterministic Audit `content_hash` as a local diagnostic fingerprint only.

The Audit hash does **not** make the database tamper-proof and is not a non-repudiation claim.

## Hosted evidence — GREEN

Current source evidence commit:
`5790ee7b69cb8ec37b17ed5815a2e4551623e248`

GitHub Actions run **33263291359 / #123** completed **SUCCESS** on GitHub-hosted Ubuntu 24.04 with PHP 8.2 and MySQL 8.4.

Job-level PASS:
- Composer metadata validation;
- canonical architecture validator;
- engineering-contract validator;
- PHP syntax;
- existing **9/9 smoke suites**;
- MySQL compiled-registration integration;
- **MySQL Definition/Audit persistence integration**.

The new integration proves migration-ledger idempotency, Definition scope isolation/dependency round-trip/revision CAS behavior, Audit scope persistence, sensitive metadata redaction, deterministic content fingerprinting and duplicate UUID rejection.

Earlier run **33261668224 / #87** remains an important historical failure that exposed generation reuse after compiled-registration corruption recovery; the model was corrected before ADR-0217 acceptance and is not current CI truth.

## Important non-certifications

Do **not** overclaim:
- no live production WordPress DB migration or rollback was executed;
- full real-WordPress bootstrap/service wiring of Definition/Audit adapters remains pending;
- network-owned Audit events without site context are not yet represented because current `ExecutionContext` requires a positive site ID;
- Audit query/UI authorization, retention purge, privacy erasure/anonymization, exports, legal holds and external/signed tamper-evidence are pending;
- 10K/100K compiled-registration performance evidence remains pending;
- large Audit dataset/performance evidence remains pending;
- Action Scheduler capability-ready ≠ coexistence/Multisite/backend certified;
- real WordPress AJAX/nonce/Policy end-to-end fixtures remain pending;
- Runtime Observatory admin graph/Policy/retention UI remains pending;
- business-module implementation has not started.

No live provider call, production deployment, destructive live-site/customer-data mutation, live production migration or irreversible external operation occurred.

## Current next action

Continue WP121 with bounded evidence-backed tranches:
1. **real WordPress AJAX/nonce/Policy integration fixtures**;
2. Action Scheduler coexistence/backend evidence;
3. durable Job attempt/lease/checkpoint contracts after backend evidence;
4. minimal Platform admin shell + Runtime Observatory graph/diagnostics UI;
5. executable 10K/100K compiled-registration scale evidence;
6. shared-foundation readiness gate;
7. first business-module tranche only after that gate passes.

Repository evidence overrides conversational memory.
