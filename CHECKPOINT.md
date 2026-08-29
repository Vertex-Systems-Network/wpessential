# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-08-29**  
Implementation branch: `implementation/baseline-adoption-gate`  
Planning authority: `planning/master-architecture` through ADR-0213  
Implementation decisions: through **ADR-0220**  
Project classification: **`GREENFIELD_IMPLEMENTATION_WITH_EXISTING_ACCEPTED_PLAN`**  
Execution mode: **`IMPLEMENTATION_GATED`**  
Lifecycle: **`IMPLEMENTING_PLATFORM_FOUNDATION`**  
Development approval: **GOV-OWNER-CONSENT-001 ACTIVE / source scope 56/56**

## Approval boundary

Owner authorized:

`Implementation Baseline / Adoption Gate → Machine-enforced architecture guards → Milestone 1 Platform Foundation → module development`.

Source implementation, development/test tooling, CI and milestone-scoped schemas/tests are authorized. Production deployment/release, destructive live-site/customer-data operations, chargeable/irreversible provider side effects and separately privileged release/merge operations remain excluded unless explicitly authorized.

## Product and planning truth

Accepted scope remains **56/56 Exhaustive**, Multisite **56/56**, AI Prompt **56/56**, with no known planning or semantic-owner gap after WP118 / ADR-0213.

## WP119 — DONE / PASS / ADR-0214

Greenfield implementation baseline accepted: WordPress 6.9+, PHP 8.2+, MySQL 8.0+/MariaDB 10.11+, Composer/PSR-4 and Node/build direction locked.

## WP120 — DONE / PASS / ADR-0215

Machine-readable architecture guards enforce canonical surfaces, semantic ownership/no-bypass, patterns, Ability/storage/Multisite/invalidation/provider/destructive/AI boundaries.

## WP121 — CURRENT / IMPLEMENTING through ADR-0220

Implemented shared foundation includes:
- Bootstrap / Kernel / Service Registry / Module lifecycle;
- Definition / ExecutionContext / Policy / Ability / Event core;
- Audit foundation;
- backend-neutral JobService logical contracts;
- bounded Action Scheduler capability probe;
- Secrets Vault reference contract, Asset Registry and Integration Registry;
- WordPress Capability + Abilities API bridge;
- ADR-0216 owner engineering contract;
- ADR-0217 persistent atomic compiled-registration storage;
- ADR-0218 production-source Definition + Audit MySQL persistence and migration ledger;
- **ADR-0219 WordPress.org-facing metadata/contribution/release contract + machine-enforced ABSPATH guards**;
- **ADR-0220 real WordPress AJAX/nonce/Policy integration + Ability-backed AJAX bridge**.

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
- machine enforcement through engineering validators/tests.

The asymmetric filter spelling is intentional public API.

## ADR-0217 — Atomic compiled registrations — ACCEPTED

Immutable scope-bound generations, transactional CAS publication, checksums, corruption quarantine, last-known-good recovery, active/fallback state and historical high-watermark sequencing are implemented. Corrupt/quarantined generation IDs are never reused.

## ADR-0218 — Definition + Audit persistence — ACCEPTED

Implemented scoped PT-D Definition/dependency persistence, transactional revision CAS, persistent migration ledger, non-destructive migrations 007/008, append-oriented Audit PT-D storage, structured indexes, secret-safe metadata persistence and local diagnostic content fingerprints.

Audit hashes are **not** tamper-proofing or non-repudiation.

## ADR-0219 — WordPress.org metadata + direct-access security — ACCEPTED

Implemented:
- Plugin URI + Author URI `https://wpessential.org`;
- Author `VSN Team`;
- clear platform description;
- GPL metadata aligned to repository GPL v3 (`GPL-3.0-or-later` / `GPLv3 or later`);
- WordPress.org-format `readme.txt`;
- `CONTRIBUTING.md` with engineering and WordPress.org release checklist;
- direct-access `ABSPATH` guard on every `frameworks/**/*.php` production file;
- test/integration bootstrap defines `ABSPATH` before guarded source;
- permanent engineering validator enforces metadata, contribution/release markers and direct-access guards.

One-time guard workflow `33265809474` succeeded and produced `a8e758a70fbdc0f3cf58206bc61350b9cb80f66d`; temporary write-enabled workflow was removed afterward.

Permanent hosted validation run `33265874634` / #138: **SUCCESS**.

ADR-0219 does not claim WordPress.org submission/approval or authorize a stable release.

## ADR-0220 — Real WordPress AJAX/nonce/Policy — ACCEPTED

Implemented `AbilityAjaxHandler` so canonical AJAX operations can execute through the existing AbilityRegistry/PolicyEngine instead of inventing a second authorization engine. AJAX context is bound to `ExecutionChannel::Ui` and Policy denials map to a stable 403 `policy_denied` response.

CI now boots pinned **WordPress 7.1** against MySQL 8.4 and verifies real WordPress:
- `wp_ajax_wpessential_dispatch` and nopriv hook registration;
- allowlisted typed route resolution;
- unknown type rejection;
- operation/scope-bound nonce create/verify;
- missing nonce rejection;
- actual administrator principal/site context and authorized Ability execution;
- low-privilege subscriber with valid nonce denied by canonical Policy/capability;
- guest authentication rejection.

Initial run `33266156181` / #151 failed because the generated test `wp-config.php` did not include `wp-settings.php`; fixture bootstrap was corrected without weakening source authorization.

Corrected source commit:
`fdee1aaffe026745283ce03fb63a14af7a7862ba`

Corrected GitHub Actions run **33266232577 / #153**: **SUCCESS**.

Job-level PASS includes pinned WordPress 7.1 fixture bootstrap, Composer, architecture/engineering validators, PHP 8.2 syntax, **9/9 smoke suites**, compiled-registration MySQL, Definition/Audit MySQL, and **real WordPress AJAX/nonce/Policy integration**.

## Important non-certifications

Do **not** overclaim:
- no WordPress.org submission or stable release has occurred;
- no live production WordPress DB migration/rollback was executed;
- full service-container wiring of all production adapters remains downstream;
- Multisite-specific AJAX switching/network-admin combinations remain pending;
- Action Scheduler capability-ready ≠ coexistence/Multisite/backend certified;
- durable Job attempt/lease/checkpoint persistence remains pending;
- Audit read/UI Policy, retention/privacy/export/legal-hold workflows remain pending;
- Runtime Observatory admin graph/Policy/retention UI remains pending;
- 10K/100K compiled-registration performance evidence remains pending;
- business-module implementation has not started.

No live provider call, production deployment, destructive live-site/customer-data mutation, live production migration or irreversible external operation occurred.

## Current next action

Continue WP121 with bounded evidence-backed tranches:
1. **Action Scheduler coexistence/packaging/backend evidence**;
2. durable Job attempt/lease/checkpoint contracts after backend evidence;
3. minimal Platform admin shell + Runtime Observatory graph/diagnostics UI;
4. executable 10K/100K compiled-registration scale evidence;
5. shared-foundation readiness gate;
6. first business-module tranche only after that gate passes.

Repository evidence overrides conversational memory.
