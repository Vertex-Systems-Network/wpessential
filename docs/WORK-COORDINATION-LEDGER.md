# WPEssential — Work Coordination Ledger

Status: **Active implementation governance ledger**  
Last reviewed: **2026-08-29**

Current classification: `GREENFIELD_IMPLEMENTATION_WITH_EXISTING_ACCEPTED_PLAN`; execution **`IMPLEMENTATION_GATED`**; lifecycle **`IMPLEMENTING_PLATFORM_FOUNDATION`**; accepted scope **56/56**; source development approval **GOV-OWNER-CONSENT-001 ACTIVE / 56/56 milestone-gated**.

## Planning closure retained

- WP117 / ADR-0212 — final planning closure PASS.
- WP118 / ADR-0213 — Module/Option/UI/System structural-integrity audit PASS after remediation.
- Known planning/semantic-owner gap: **none known**.

## Implementation sequence

### WP119 — Implementation Baseline / Adoption Gate
**DONE / PASS / ADR-0214**.

Greenfield implementation baseline and compatibility/toolchain direction locked.

### WP120 — Machine-enforced Architecture Guards
**DONE / PASS / ADR-0215**.

Machine guards cover canonical 56 surfaces/routes/suites, semantic ownership/no-bypass, P01–P40 routing, Ability/Policy, storage, Multisite, invalidation, provider/unknown-outcome, destructive/recovery and AI/MCP boundaries.

Earlier GitHub-hosted pre-runner dispatch degradation is no longer current evidence. Later hosted WP121 runs have assigned a real runner and executed the validators/smoke chain successfully.

### WP121 — Milestone 1 Platform Foundation
**CURRENT / IMPLEMENTING**.

Implemented foundation now includes:
- Bootstrap / Kernel / Service Registry / Module lifecycle;
- immutable Definition / Context / Policy / Ability / Event core;
- Audit foundation;
- backend-neutral JobService identity/state/idempotency/retry/cancellation;
- bounded Action Scheduler capability probe;
- Secrets Vault reference contract;
- Asset Registry;
- Integration Registry;
- persistent Definition repository abstraction + migration contracts/reference gateways;
- WordPress Capability + Abilities API bridge;
- owner-mandated engineering conventions / **ADR-0216**.

### ADR-0216 — mandatory engineering contract

Future implementation must preserve:
- namespace `WPEssential`;
- PSR-4 production root `frameworks/` and no parallel `src/` runtime tree;
- globals `wpessential_*`;
- constants `WPE_*`;
- custom filters exactly `wpesential/apply_*`;
- custom actions `wpessential/hook_*`;
- one canonical AJAX front door with typed allowlisted routing;
- centralized nonce operation service for `apply/create/update/reset/delete`;
- compile-on-write WordPress registration architecture;
- bounded/redacted Runtime Observatory tracing;
- machine validation of these invariants.

The asymmetric `wpesential` filter spelling is intentional public API.

## Hosted evidence — GREEN

Current branch has successful hosted GitHub Actions evidence. Run **33258502235** / run number **84** completed **SUCCESS** on a real GitHub-hosted runner after the ADR-0216/documentation reconciliation.

The validated source chain includes:
- Composer metadata validation;
- canonical architecture validator;
- engineering-contract validator;
- PHP syntax under the configured PHP 8.2 CI baseline;
- complete smoke chain covering Kernel, platform core, Audit/Jobs, Action Scheduler probe, Vault/Assets/Integrations, persistence/migrations, WordPress Abilities bridge and engineering contracts.

A prior diagnostic run exposed only a stale smoke assertion (`not exposed` vs canonical `channel_not_exposed`); the assertion was corrected without weakening authorization semantics. Subsequent hosted CI is green.

## Current non-certifications

Do not overclaim:
- `InMemoryCompiledRegistrationStore` remains reference/test-only;
- production atomic compiled-registration persistence is not yet complete;
- `PersistentDefinitionRepository` is not yet backed by a certified production WordPress/MySQL adapter;
- persistent/tamper-evident Audit storage is not yet complete;
- Action Scheduler capability-ready does not equal coexistence/Multisite/backend certification;
- real WordPress AJAX/nonce integration fixtures remain pending;
- Runtime Observatory admin graph/Policy/retention UI remains pending;
- 10K/100K compiled-registration performance evidence remains pending.

## Next WP121 bounded sequence

1. persistent atomic compiled-registration generation store + last-known-good/recovery semantics;
2. production Definition/Audit persistence adapters + bounded migrations;
3. real WordPress AJAX/nonce/Policy integration fixtures;
4. Action Scheduler coexistence/backend evidence;
5. durable Job attempt/lease/checkpoint contracts after backend evidence;
6. minimal Platform admin shell + Runtime Observatory graph/diagnostics UI;
7. executable 10K/100K compiled-registration scale evidence;
8. shared-foundation readiness gate before first business-module tranche.

## Privileged exclusions

Current source-development approval does not authorize production deployment/release, destructive live-site/customer-data mutation, chargeable/irreversible provider operations, live payment/communication side effects, or destructive production reset/restore/migration/rescue.
