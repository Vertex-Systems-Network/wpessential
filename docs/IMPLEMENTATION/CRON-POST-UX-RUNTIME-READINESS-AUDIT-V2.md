# Cron — Post-UX Runtime Readiness Re-Audit V2

Issue: #825  
Parent: #817  
Audit anchor: `8f796aaf95f0957def439586eb1eb8fb2fd49d91`  
Surface: 18 / `cron`

## Decision

**READY_FOR_BOUNDED_RUNTIME_SLICE.**

Surface 18 is now `UX_CONTRACT_COMPLETE`; the product-contract prerequisites that blocked V1 are closed. Dedicated Cron owner runtime is absent, while Platform Jobs, Abilities/Auth, Definitions, Audit/Observability, Integrations and WordPress seams are present. That is sufficient for a read-only event/provider catalog and effective schedule diagnostic slice. It is not sufficient to certify run-now or mutation behavior, which remains later-gated.

## Runtime-family classification

| Family | V2 classification | Boundary |
|---|---|---|
| Module admission / definitions | REUSABLE_DEPENDENCY | Shared platform foundation. |
| Job target / registered ability | REUSABLE_DEPENDENCY | Platform Jobs and Abilities own executable target substrate. |
| Native WP-Cron event inspection | REUSABLE_DEPENDENCY + MISSING OWNER ADAPTER | WordPress APIs exist; Surface 18 must provide bounded owner read adapter. |
| Provider health / Action Scheduler profile | REUSABLE_DEPENDENCY / OUT_OF_SURFACE | Integrations/provider health can be referenced, not executed here. |
| Effective schedule / overdue diagnostics | MISSING | First slice read-model responsibility. |
| Reliability policy definition | MISSING | May be defined later; execution is separately gated. |
| Run-now / pause / reschedule / cancel / retry | MISSING / BLOCKED FOR FIRST SLICE | High-impact operations excluded. |
| Arbitrary callbacks/classes/shell commands | BLOCKED | Explicitly prohibited. |

## Smallest bounded first runtime slice

**Read-only registered event/provider catalog + effective schedule diagnostics.**

Candidate later paths:
- `frameworks/Modules/Cron/CronModule.php`
- `frameworks/Modules/Cron/CronDefinition.php`
- `frameworks/Modules/Cron/CronReadService.php`
- `frameworks/Modules/Cron/CronAbilityHandler.php`
- `tests/Unit/Modules/Cron/`
- real WordPress integration for `wp_get_scheduled_event` / `wp_next_scheduled`, multisite and provider coexistence

The slice may list registered native schedules, correlate registered Job/Ability references and report next-run/overdue/provider-health diagnostics. It must not trigger events, acquire execution leases, reschedule/delete events or promise exact wall-clock execution.

## Required future exact-head evidence

- native WP-Cron event identity/argument/recurrence inspection tests;
- explicit proof next-run is due-state evidence, not execution-time guarantee;
- multisite/blog-switching isolation tests;
- registered Job/Ability reference validation and missing-target degraded states;
- Action Scheduler/system-runner coexistence diagnostics without execution;
- rejection of arbitrary callable/class/shell targets;
- Architecture Guards, PHP quality, Platform Compatibility Matrix and applicable integration/package/browser gates.

## Reliability, rollback and portability

Definitions may carry stable registered target references and schedule policy only. Environment/provider-specific runner state is diagnostic and non-portable. The first slice performs no schedule/job mutation, so rollback is module disablement/removal. Missing providers return explicit degraded state; client input cannot fabricate due/lease/success truth.

## Non-certifications

No run-now, lifecycle mutation, job execution, callback/shell execution, provider execution, exact-time SLA, runtime/product certification, production migration, deployment or release is authorized. Atomic lifecycle remains `UX_CONTRACT_COMPLETE`.
