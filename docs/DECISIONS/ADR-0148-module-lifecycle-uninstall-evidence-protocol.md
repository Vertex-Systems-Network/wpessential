# ADR-0148 — Module Lifecycle, Disable, Uninstall & Recovery Executable Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP31`

## Context

WPEssential already defines canonical module states and paper rules for enable, disable, re-enable, Pro expiry, plugin deactivation, dependency loss, migration failure, uninstall cleanup and recovery. These lifecycle transitions cross the Module Registry, Policy, JobService, Definitions, caches/assets, providers, data ownership, privacy, Multisite and Free↔Pro boundaries.

The repository did not contain a dedicated executable protocol proving that lifecycle state changes preserve data, avoid silent security weakening, avoid stale jobs/registrations and keep destructive cleanup explicitly scoped. Domain-specific protocols exist, but none alone prove lifecycle orchestration across them.

ADR-0014 remains authoritative: acceptance does not authorize module/plugin execution or destructive cleanup.

## Decision

Adopt `docs/QUALITY/MODULE-LIFECYCLE-UNINSTALL-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the canonical shared future executable-evidence contract for module lifecycle, disable, uninstall and recovery behavior.

The protocol freezes **MLC-01…MLC-176**.

Current execution truth: **0/176 executed**.

No module lifecycle runtime certification exists yet.

## Canonical state model

Modules map to one canonical platform state:

- `unavailable`
- `available_disabled`
- `enabled`
- `degraded`
- `read_only`
- `paused`
- `migration_required`
- `unhealthy`

These state dimensions remain distinct:

`module availability ≠ enable intent ≠ dependency health ≠ schema readiness ≠ entitlement/editing rights ≠ runtime enforcement ≠ job activity ≠ provider connection ≠ data retention ≠ cleanup state ≠ certified health`

Disable is not delete. Entitlement expiry is not uninstall. Plugin deactivation is broader than a module toggle. Uninstall-hook execution is not destructive-cleanup consent.

## Independent certification classes

- `MLC-S` — state calculation/registry truth;
- `MLC-E` — enable/preflight/activation;
- `MLC-D` — disable/dependency degradation;
- `MLC-R` — re-enable/reactivation/reconciliation;
- `MLC-P` — Pro expiry/plugin deactivation;
- `MLC-M` — migration failure/recovery mode;
- `MLC-U` — uninstall/cleanup safety;
- `MLC-O` — data ownership/Definition/runtime cleanup orchestration;
- `MLC-J` — Jobs/assets/caches/Abilities/Events lifecycle;
- `MLC-X` — security/privacy/Multisite isolation;
- `MLC-Q` — concurrency/scale/observability/recovery quality.

Passing one class never certifies another.

## Core invariants

1. Default module disable preserves user-authored Definitions and runtime business data.
2. New mutations/jobs/Abilities cannot remain available merely because stale registrations/caches survived a disable/dependency-loss state.
3. Security/access modules cannot accidentally expose protected resources through disable, entitlement expiry or plugin state transition.
4. Re-enable revalidates schema, dependency, provider, lifecycle generation and queued work before mutations resume.
5. Pro expiry preserves data and accepted safe deployed output/enforcement under ADR-0007; paid editing/new creation and cost-mutating automation may become read-only/paused without deleting configuration.
6. Free/Pro plugin deactivation is non-destructive by default.
7. Uninstall default preserves data unless a prior explicit scoped destructive-cleanup choice/dedicated cleanup flow exists.
8. Cleanup levels remain distinct: keep everything; transient/generated only; module configuration; explicit full WPE data cleanup.
9. Full cleanup does not delete ordinary WordPress posts/users/terms/media merely because WPE data references them unless separately selected and authorized.
10. One module cannot delete another module/shared platform's owned data directly; data-owner APIs and dependency inventory govern cleanup.
11. Local cleanup is not remote provider deletion and live-record cleanup is not historical-backup erasure.
12. Migration failure does not retry destructively on every request and does not mark the module healthy/enabled until invariants revalidate.
13. Recovery mode remains authorized/minimal and is not an anonymous/superuser backdoor.
14. Multisite lifecycle state is explicitly site/network scoped; current blog context is not durable ownership.
15. In-flight/stale Jobs recheck module/target/config state before side-effect commit after disable/delete/re-enable transitions.
16. Partial cleanup/lifecycle failure cannot be reported as total success.

## Anti-duplication boundary

MLC does not replace:
- KPA registry/Policy/Ability/Event execution evidence;
- FP exact Free↔Pro pair evidence;
- VER version/migration/deprecation evidence;
- PDL privacy export/erase/retention evidence;
- DEF Definition persistence/deletion mechanics;
- JS Job execution/idempotency evidence;
- BK backup/restore evidence;
- MBR/protected-file access enforcement evidence;
- MSI/LC Multisite scope/site-lifecycle evidence;
- provider-specific remote cleanup/revocation certification.

MLC reports reference these domains and never auto-promote them.

## Current truth

- MLC fixtures documented: **176**.
- MLC fixtures executed: **0/176**.
- Module lifecycle runtime certifications: **0**.
- No module toggle, plugin activation/deactivation/uninstall hook, migration, cleanup, DB/file deletion, Job execution, provider call, cache flush or Multisite lifecycle operation has been executed by accepting this ADR.

## Consequences

Module lifecycle behavior now has an explicit evidence gate instead of relying on happy-path enable/disable UI or uninstall hooks. Future implementation must preserve data ownership, security enforcement, recovery paths and truthful state transitions across module/plugin/license/dependency changes.

Exact per-module cleanup contents, decommissioning behavior for security-sensitive modules, migration recovery profiles and supported Multisite lifecycle combinations remain evidence-gated.

## Development-consent gate

**Accepted evidence only. No production code, module/plugin state execution, migration, cleanup, data/file deletion, queue/cache/provider action or recovery fixture is authorized until explicit owner consent under ADR-0014 and `/DEVELOPMENT-CONSENT.md`.**
