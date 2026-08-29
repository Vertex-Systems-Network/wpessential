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

### WP121 — Milestone 1 Platform Foundation
**CURRENT / IMPLEMENTING through ADR-0218**.

Implemented shared foundation includes:
- Bootstrap / Kernel / Service Registry / Module lifecycle;
- Definition / Context / Policy / Ability / Event core;
- Audit logical foundation;
- backend-neutral JobService identity/state/idempotency/retry/cancellation;
- bounded Action Scheduler capability probe;
- Secrets Vault reference contract;
- Asset Registry;
- Integration Registry;
- WordPress Capability + Abilities API bridge;
- owner engineering contract / ADR-0216;
- persistent atomic compiled-registration storage / ADR-0217;
- **Definition + Audit MySQL persistence adapters, non-destructive schemas and persistent migration ledger / ADR-0218**.

## ADR-0216 — mandatory engineering contract

Future implementation preserves canonical `WPEssential`/`frameworks/` architecture, public naming contracts, single typed AJAX gateway, centralized nonce operations, compile-on-write registrations, bounded/redacted Runtime Observatory tracing and machine enforcement.

The asymmetric `wpesential/apply_*` filter spelling is intentional public API.

## ADR-0217 — atomic compiled registrations

Accepted immutable per-scope compiled generation history, separate active/fallback state, InnoDB transaction + CAS publication, checksums, corruption quarantine, last-known-good recovery and a historical high-watermark independent of the active pointer. Corrupt/quarantined generation IDs are never reused.

## ADR-0218 — Definition + Audit persistence

Accepted implementation:
- explicit Definition network/site scope;
- PT-D Definition + dependency tables;
- canonical `PersistentDefinitionRepository` backed by `WpdbDefinitionTableGateway`;
- transactional Definition/dependency writes and stale-revision CAS rejection;
- persistent `${base_prefix}wpe_migrations` ledger;
- non-destructive migrations 007/008;
- append-oriented PT-D Audit event storage;
- unique Audit UUID + structured scope indexes;
- existing secret-safe metadata sanitizer enforced before storage;
- deterministic Audit content fingerprint for local integrity diagnostics only.

Audit content hashes are **not** a tamper-proof/non-repudiation claim.

## Hosted evidence — GREEN

Current executable evidence source commit:
`5790ee7b69cb8ec37b17ed5815a2e4551623e248`

GitHub Actions run **33263291359 / #123** completed **SUCCESS** on GitHub-hosted Ubuntu 24.04 / PHP 8.2 / MySQL 8.4.

PASS evidence:
- Composer metadata;
- architecture validator;
- engineering-contract validator;
- PHP syntax;
- **9/9 smoke suites**;
- MySQL atomic compiled-registration integration;
- **MySQL Definition/Audit persistence integration**.

The new integration proves ordered/idempotent migration state, Definition scope isolation/dependencies/revision CAS, Audit scope persistence, redaction before storage, deterministic fingerprinting and duplicate UUID rejection.

Historical run #87 remains a legitimate earlier failure for compiled-registration generation reuse; it was corrected before ADR-0217 and is not current health.

## Current non-certifications

Do not overclaim:
- no live production WordPress DB migration/rollback has been executed;
- real WordPress bootstrap/service wiring of Definition/Audit adapters remains pending;
- network-only Audit events without site context remain unresolved in current `ExecutionContext`;
- Audit read/UI Policy, retention purge, privacy erasure/anonymization, export, legal hold and stronger external/signed tamper-evidence remain pending;
- large Audit dataset/performance evidence remains pending;
- 10K/100K compiled-registration performance evidence remains pending;
- Action Scheduler capability-ready does not equal coexistence/Multisite/backend certification;
- real WordPress AJAX/nonce/Policy fixtures remain pending;
- Runtime Observatory admin graph/Policy/retention UI remains pending;
- business-module implementation remains downstream of the foundation gate.

## Next WP121 bounded sequence

1. **real WordPress AJAX/nonce/Policy integration fixtures**;
2. Action Scheduler coexistence/backend evidence;
3. durable Job attempt/lease/checkpoint contracts after backend evidence;
4. minimal Platform admin shell + Runtime Observatory graph/diagnostics UI;
5. executable 10K/100K compiled-registration scale evidence;
6. shared-foundation readiness gate;
7. first business-module tranche after that gate.

## Privileged exclusions

Current source-development approval does not authorize production deployment/release, destructive live-site/customer-data mutation, chargeable/irreversible provider operations, live payment/communication side effects, or destructive production reset/restore/migration/rescue.
