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

### WP120 — Machine-enforced Architecture Guards
**DONE / PASS / ADR-0215**.

Machine guards cover canonical 56 surfaces/routes/suites, semantic ownership/no-bypass, P01–P40 routing, Ability/Policy, storage, Multisite, invalidation, provider/unknown-outcome, destructive/recovery and AI/MCP boundaries.

### WP121 — Milestone 1 Platform Foundation
**CURRENT / IMPLEMENTING**.

Implemented shared foundation includes:
- Bootstrap / Kernel / Service Registry / Module lifecycle;
- Definition / Context / Policy / Ability / Event core;
- Audit foundation;
- backend-neutral JobService identity/state/idempotency/retry/cancellation;
- bounded Action Scheduler capability probe;
- Secrets Vault reference contract;
- Asset Registry;
- Integration Registry;
- Definition persistence abstraction + migration contracts/reference gateways;
- WordPress Capability + Abilities API bridge;
- owner-mandated engineering conventions / **ADR-0216**;
- persistent atomic compiled-registration storage / **ADR-0217**.

## ADR-0216 — mandatory engineering contract

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

## ADR-0217 — atomic compiled registrations

Accepted implementation:
- immutable per-scope compiled generation history;
- separate active/fallback pointer;
- network/site isolation;
- InnoDB transaction + CAS publication;
- deterministic manifest checksums;
- explicit corruption quarantine;
- verified last-known-good recovery;
- historical high-watermark sequence independent of the active pointer;
- permanent non-reuse of corrupt/quarantined generation IDs;
- runtime consumption of the active compiled projection rather than historical definition scans.

A recovery may roll the active pointer backward. It may **not** roll the generation sequence backward.

## Hosted evidence — GREEN

Corrected atomic-store implementation commit:
`de2bf6ea0299ce3900a0d6dff2d4646066137497`

GitHub Actions run **33261866811 / #89** completed **SUCCESS** on a real hosted runner.

PASS evidence:
- MySQL 8.4 service;
- Composer metadata;
- architecture validator;
- engineering-contract validator;
- PHP 8.2 syntax;
- **9/9 smoke suites**;
- real MySQL 8.4 atomic compiled-registration integration.

Preceding run **33261668224 / #87** failed and stopped before MySQL integration because the initial model could reuse a corrupt generation number after active-pointer recovery. The defect was corrected; no waiver was used.

## Current non-certifications

Do not overclaim:
- 10K/100K compiled-registration performance evidence remains pending;
- `NativeWpdbAdapter` is not yet fully WordPress-runtime certified;
- MySQL CI proves the accepted transactional store contract, not full WordPress integration;
- production WordPress/MySQL Definition adapter remains pending;
- persistent/tamper-evident Audit storage remains pending;
- Action Scheduler capability-ready does not equal coexistence/Multisite/backend certification;
- real WordPress AJAX/nonce/Policy fixtures remain pending;
- Runtime Observatory admin graph/Policy/retention UI remains pending;
- business-module implementation remains downstream of the foundation gate.

## Next WP121 bounded sequence

1. production Definition/Audit persistence adapters + bounded migrations;
2. real WordPress AJAX/nonce/Policy integration fixtures;
3. Action Scheduler coexistence/backend evidence;
4. durable Job attempt/lease/checkpoint contracts after backend evidence;
5. minimal Platform admin shell + Runtime Observatory graph/diagnostics UI;
6. executable 10K/100K compiled-registration scale evidence;
7. shared-foundation readiness gate;
8. first business-module tranche after that gate.

## Privileged exclusions

Current source-development approval does not authorize production deployment/release, destructive live-site/customer-data mutation, chargeable/irreversible provider operations, live payment/communication side effects, or destructive production reset/restore/migration/rescue.
