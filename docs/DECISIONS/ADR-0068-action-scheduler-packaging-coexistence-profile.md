# ADR-0068 — Action Scheduler Packaging, Load Order & Coexistence Profile

Status: **Accepted static backend-integration architecture / P-003 execution pending**  
Date: 2026-08-28

## Context

ADR-0059 fixes WPE JobService semantics while ADR-0006/P-003 keeps the concrete Action Scheduler backend evidence-gated. Before executing P-003, WPE still needs a static rule for packaging and coexistence because WooCommerce and many third-party plugins may bundle their own Action Scheduler copies.

Current official snapshot reviewed:
- Action Scheduler 4.1.0 is current;
- upstream is explicitly designed for distribution in multiple WordPress plugins;
- registered plugin copies are resolved so the newest registered version is loaded;
- registration must occur before `plugins_loaded` priority 0;
- APIs are used after Action Scheduler initialization around `init` priority 1 / `action_scheduler_init`;
- 4.0 changed unique-action semantics and failed-action retention defaults;
- 4.1 added schedule-deserialization security hardening and operational fixes.

## Decision

The authoritative planning contract is `docs/ARCHITECTURE/ACTION-SCHEDULER-PACKAGING-COEXISTENCE-PROFILE.md`.

### Ownership

If P-003 selects Action Scheduler:
- WPE Platform Kernel / Free owns WPE's bundled candidate copy;
- WPE Pro does not bundle another WPE copy;
- individual modules never bundle their own copies;
- third-party/Woo copies are expected and must coexist.

### Abstraction

Only the JobService Action Scheduler adapter may use Action Scheduler public APIs.

Modules consume WPE JobService concepts and must not query Action Scheduler tables/classes/statuses directly.

### Load order

The WPE bundled candidate is registered before `plugins_loaded` priority 0 and WPE waits for Action Scheduler initialization before adapter use.

WPE does not manually force its own bundled copy to win over a newer registered third-party copy.

### Capability/version behavior

A newer runtime is not automatically certified. Public APIs/capability detection (including `as_supports()` where applicable) is preferred over private version internals.

Runtime states can include:
- compatible/certified;
- below required capability;
- newer unverified;
- backend unavailable/degraded.

### Payload safety

Action Scheduler arguments are persisted operational data.

WPE backend args therefore contain minimal scalar/array references such as WPE Job UUIDs, not:
- secrets/tokens/passwords;
- private keys/recovery material;
- signed upload URLs;
- arbitrary serialized objects;
- large business payloads.

Large/sensitive Job payloads live in WPE-owned storage/Vault references.

### Idempotency

Action Scheduler `unique` scheduling is an optimization only. WPE business idempotency remains JobService-owned because upstream 4.0 changed uniqueness semantics to include action arguments and because external side effects require stronger reconciliation.

### Retention

Action Scheduler backend action/log cleanup is separate from WPE Job/Attempt/Audit retention. Upstream failed-action retention changes cannot silently delete WPE-required product/audit history.

### Shared storage ownership

WPE never treats third-party Action Scheduler rows as WPE-owned merely because they share the same backend tables. Cron/Admin UI must clearly distinguish WPE jobs from WP-Cron and third-party Action Scheduler actions.

## Current static candidate

- reviewed upstream version: **4.1.0**;
- backend selection: preferred candidate, **not Verified**;
- P-003 executable evidence: **not run**;
- no dependency/package/bundle added to repository.

## Consequences

Positive:
- Free/Pro avoids duplicate WPE dependency ownership;
- Woo/third-party coexistence is a first-class requirement;
- business semantics survive Action Scheduler version behavior changes;
- secrets and large payloads stay out of shared queue args;
- backend cleanup cannot erase WPE product history.

Cost:
- P-003 must test multiple-copy/load-order scenarios;
- WPE may temporarily block/degrade on a newer unverified Action Scheduler major release;
- adapter layer adds deliberate translation/diagnostic complexity.

## Evidence still required

After explicit owner development consent:
- WPE bundled candidate packaging method;
- load order with Woo/third-party older/newer copies;
- table migration/store coexistence;
- API readiness/capabilities;
- Job↔action mapping;
- unique/retry/claim/crash/reclaim;
- fairness/concurrency/backpressure;
- retention/cleanup;
- recurrence repair;
- WP-Cron/async/WP-CLI/system runner profiles;
- multisite;
- deactivate/uninstall/upgrade/downgrade/newer-unverified behavior.

No Action Scheduler package was installed, loaded or executed to accept this ADR.
