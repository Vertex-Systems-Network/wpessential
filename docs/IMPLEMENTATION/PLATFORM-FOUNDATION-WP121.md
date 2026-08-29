# WPEssential — WP121 Platform Foundation

Status: **CURRENT / IMPLEMENTING — shared platform foundation active**  
Date: **2026-08-29**  
Approval: `GOV-OWNER-CONSENT-001` ACTIVE  
Predecessors: WP119 PASS / ADR-0214; WP120 PASS / ADR-0215  
Accepted implementation decisions: **ADR-0216 through ADR-0222**

## Goal

Establish the shared production-source platform foundation required before business modules can safely exist. WP121 owns reusable kernel, security, persistence, jobs, integrations, WordPress bridges, release/security conventions and observability primitives; it is not a business-feature milestone.

## Accepted tranches

### ADR-0216 — Engineering contract
Canonical `WPEssential` / `frameworks/`, public hook/filter/global/constant conventions, one typed AJAX gateway, centralized nonce operations, compile-on-write registrations, bounded/redacted observability, `ABSPATH` security and machine enforcement remain mandatory.

### ADR-0217 — Atomic compiled registrations
Implemented site/network scope, immutable generations, transactional CAS publication, deterministic integrity checks, corruption quarantine, last-known-good recovery and historical generation high-watermark sequencing.

### ADR-0218 — Definition + Audit persistence
Implemented PT-D Definition/dependency persistence, revision CAS, persistent migration ledger, non-destructive migrations 007/008 and append-oriented Audit storage with structured indexes and secret-safe metadata.

### ADR-0219 — WordPress.org/release/direct-access contract
Implemented `https://wpessential.org`, `VSN Team`, clear public description, GPL v3 metadata alignment, WordPress.org `readme.txt`, `CONTRIBUTING.md`, and machine-enforced fail-closed `ABSPATH` guards across production PHP. Hosted run #138 is GREEN.

### ADR-0220 — Real WordPress AJAX/nonce/Policy
Implemented `AbilityAjaxHandler`, UI-channel Ability execution, stable Policy-denial mapping and real WordPress 7.1 integration for canonical AJAX hooks, typed routes, nonces, administrator success, subscriber denial and guest rejection. Corrected hosted run #153 is GREEN.

### ADR-0221 — Action Scheduler coexistence/backend
Implemented an isolated public-API backend adapter:
- WPE hook `wpessential/hook_job_dispatch`;
- group `wpessential-jobs`;
- backend args contain only `job_id`;
- exact WPE query/cancel ownership;
- no third-party Action Scheduler ownership mutation;
- backend uniqueness remains an optimization, never WPE business idempotency.

Real WordPress 7.1 / MySQL 8.4 fixture registers Action Scheduler 3.9.3 + 4.1.0 together, confirms 4.1.0 wins latest-version selection and verifies WPE/third-party action isolation.

Source audit corrected an early mistaken assumption: `as_has_scheduled_action()` returns boolean, so action IDs are queried with `as_get_scheduled_actions(..., 'ids')`.

Hosted run **33267115851 / #178 SUCCESS**.

Final WPE release packaging of Action Scheduler is not yet locked; the tested coexistence profile is accepted without claiming every future runtime/version or Multisite combination.

### ADR-0222 — Durable Job persistence, leases & checkpoints

Added WPE-owned durable Job state independent of Action Scheduler operational records.

Migration `009.create-job-persistence` creates:
- `${base_prefix}wpe_jobs`;
- `${base_prefix}wpe_job_attempts`.

Implemented:
- `JobScope` and network/site ownership;
- `PersistentJobService` over a scoped persistence gateway;
- durable payload/state/retry/attempt counters;
- stable-key SHA-256 idempotency digest persistence without a raw-key column;
- revision-CAS Job mutations;
- restart-style service reloading;
- leased attempt store with per-Job serialized claim acquisition;
- monotonic attempt numbers;
- random worker lease token with DB hash-only storage;
- heartbeat lease extension;
- strictly increasing checkpoint sequence;
- terminal attempt result/failure recording;
- stale/expired worker heartbeat/completion rejection;
- bounded expired-lease reclaim to `abandoned` and replacement attempt creation.

Real WordPress/MySQL fixture proves migration idempotence, persisted reload, retry/success transitions, stale-CAS rejection, exclusive leases, token hashing, heartbeat, monotonic checkpoints, stale worker rejection and fresh attempt after expiry.

Evidence source head `8601d6f17325681c63cdbc97e6b64e1a3892db1e`.

Hosted run **33267525349 / #209 SUCCESS** across all prior guards/integrations plus real durable JobService persistence/lease evidence.

## Current exclusions / not yet certified

- WordPress.org stable submission/release;
- live production DB migration/rollback;
- final public Action Scheduler vendoring/build mechanism;
- Multisite-specific AJAX/queue worker switching and network-admin matrix;
- automatic Action Scheduler dispatch → Ability → attempt lifecycle integration;
- high-concurrency fairness/resource admission/backpressure performance;
- Job checkpoint privacy/retention implementation;
- Audit viewer/retention/privacy/export/legal-hold surfaces;
- Runtime Observatory admin diagnostics UI;
- minimal Platform admin shell;
- executable 10K/100K compiled-registration scale certification;
- business-facing modules.

No production deployment, live provider call, destructive live-site/customer-data mutation, live production DB migration or irreversible external operation was performed.

## Next WP121 work

1. **build minimal Platform admin shell + Runtime Observatory diagnostics surface**;
2. add executable 10K/100K compiled-registration scale evidence;
3. run shared-foundation readiness gate;
4. begin first business-module tranche only after that gate passes.

Every next tranche extends executable evidence and preserves the canonical no-bypass/ownership boundaries.
