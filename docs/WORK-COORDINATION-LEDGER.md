# WPEssential — Work Coordination Ledger

Status: **Active governance ledger**  
Last reviewed: 2026-08-28

This ledger is the live coordination view for critical path, work-in-progress, parallelism and shared-surface ownership. It complements `CHECKPOINT.md`; it must not become a second conflicting product roadmap.

## 1. Current execution state

Project state: `PLANNED_EXISTING_PROJECT`  
Execution mode: `PLANNER_ONLY`  
Production implementation WIP: **0**  
Active implementation approvals: **0**  
Authorized module/platform surfaces: **0/31**

## 2. Current/recent work packages

| Work ID | Scope | Lifecycle | Critical-path class | Parallelism | Shared surfaces | Notes |
|---|---|---|---|---|---|---|
| `P0-M00-WP01` | Universal Master Prompt governance hardening | `DONE` | `SHARED_CONTRACT` | `SERIALIZE` | AGENTS/governance/quality/spec/checkpoint/PR docs | Documentation-only; governance integrated. |
| `P0-M00-WP02` | Forms runtime/storage/submission executable evidence protocol | `DONE` | `SHARED_CONTRACT` | `SERIALIZE` | Forms spec/runtime/topology/quality/ADR/readiness/checkpoint docs | ADR-0117; FM-01…FM-92 documented; 0 executed. |
| `P0-M00-WP03` | Workflow/Cron scheduling/DST/claims evidence refinement | `DONE` | `SHARED_CONTRACT` | `SERIALIZE` | Workflow Runtime + JobService + scheduler/cron evidence/governance docs | ADR-0118/0119; WF-01…WF-116 and JS-01…JS-106 documented; 0 executed. |
| `P0-M00-WP04` | Notification fan-out/read/dedupe evidence protocol | `DONE` | `SHARED_CONTRACT` | `SERIALIZE` | Notification + Job/Workflow + Email/Connections truth | ADR-0120; NT-01…NT-142 documented; 0 executed. |
| `P0-M00-WP05` | Message & Chat transport/search/private-assets evidence protocol | `DONE` | `SHARED_CONTRACT` | `SERIALIZE` | Chat runtime/search/transport/private assets/access | ADR-0121; CH-01…CH-142 documented; 0 executed. |
| `P0-M00-WP06` | Webhooks & Connections signature/replay/Event Inbox/provider evidence protocol | `DONE` | `INTEGRATION` | `SERIALIZE` | Connection Registry + Safe HTTP + Webhook Gateway + Event Inbox | ADR-0122; WC-01…WC-156 documented; 0 executed; I4/I5=0. |
| `P0-M00-WP07` | P-001 compatibility floor evidence refinement | `DONE` | `BLOCKING_FOUNDATION` | `SERIALIZE` | compatibility floor + adoption baseline + Multisite + artifact/CI contract | ADR-0123; CF-01…CF-112 documented; 0 executed; ADR-0002 remains Proposed. |
| `P0-M00-WP08` | P-005 Secrets Vault crypto/recovery executable evidence refinement | `DONE` | `BLOCKING_FOUNDATION` | `SERIALIZE` | Vault + Policy + Audit + Backup/restore + Connections/credentials + Multisite | ADR-0124; VT-01…VT-128 documented; 0 executed; final V1/V2 topology remains evidence-gated. |
| `P0-M00-WP09` | P-002 UI runtime + P-008 build/externalization evidence refinement | `SPECIFICATION` | `SHARED_CONTRACT` | `SERIALIZE` | WPE UI wrappers + WP React/runtime packages + asset manifest + route loading + build/release metadata | Current planning work; reconcile ADR-0005/0012, static research and generic P-002/P-008 before defining fixed protocols. |

No production implementation work package is active.

## 3. Current planning queue

Do not restart from zero.

| Order | Planning item | Current state | Dependency / note |
|---:|---|---|---|
| 1 | P-002 UI runtime + P-008 build/externalization evidence refinement | `SPECIFICATION` current | Product-wide admin/runtime foundation; UI wrappers and build externalization must be evaluated together without introducing a second React runtime |
| 2 | Reassess P-007 CI, P-006 Free↔Pro boot, P-012 Membership, P-013 Backup and remaining blockers | `BLOCKED` by sequence | Choose by critical-path value after UI/build planning closes |

These are planning tasks only, not implementation approvals.

## 4. Critical-path classes

Use:
- `BLOCKING_FOUNDATION`
- `SHARED_CONTRACT`
- `HIGH_RISK_UNKNOWN`
- `INDEPENDENT_FEATURE`
- `INTEGRATION`
- `REGRESSION`
- `RELEASE`

Current future-development critical foundations remain P-001 compatibility, P-002 UI, P-003 Job, P-004 Definition, P-005 Vault, P-007 CI, P-008 build, P-009 Query, P-010 Relations, P-011 Workflow, P-012 Membership and P-013 Backup according to the Implementation Readiness Matrix.

## 5. WIP limits

Default approved implementation limits:
- maximum 3 active implementation work packages per milestone;
- maximum 1 writer for any `SERIALIZE` shared surface;
- maximum 1 unresolved high-risk migration/auth/secrets mutation in active implementation at a time;
- downstream work waits when it depends on an unverified shared contract.

Current implementation WIP is 0, so no limit is consumed.

Planning documentation work may advance serially without creating implementation authorization.

## 6. Shared-surface reservations

| Shared surface | Work ID | Owner/writer | Mode | Dependents | Merge/integration order | Status |
|---|---|---|---|---|---|---|
| None | — | — | — | — | — | No active implementation reservations |

Typical shared surfaces include Composer/npm manifests and lockfiles, DB schema/migrations, Definition Repository, auth/Policy/Vault, Module Registry, shared API/Ability schemas, global routing, Component Blueprint, JobService, Workflow Runtime, Entitlement engine, global config and CI/build/package/release metadata.

## 7. Parallelism decision record

When a new work package starts, record exactly one:
- `PARALLEL_SAFE`
- `COORDINATED_PARALLEL`
- `SERIALIZE`
- `BLOCKED`

For `COORDINATED_PARALLEL`/`SERIALIZE`, record shared surface, owner, interface/change boundary, integration order and post-integration verification.

## 8. Change-budget tracking

For implementation work record expected vs actual files, modules, APIs/contracts, migrations/schema, dependencies/lockfiles, configuration/build/CI and tests/evidence.

Material unexplained expansion triggers:

`STOP → REASSESS → UPDATE IMPACT → RESCOPE OR SPLIT`

## 9. Ledger update rule

Update this ledger when a material planning/implementation package starts/stops/blocks/completes, shared-surface ownership changes, parallelism changes, critical path changes or a WIP exception is intentionally changed. Do not update for every tiny edit.

## 10. Current next safe action

Continue `P0-M00-WP09`: reconcile ADR-0005 UI wrapper/design-system direction, ADR-0012 build-tool candidates, current WordPress React/package externalization requirements, route-scoped assets, DataViews/DataForm/accessibility/RTL/localization and reproducible release artifacts with the generic P-002/P-008 spike plans; create fixed evidence protocols only where dedicated equivalent protocols do not already exist.

Production implementation remains blocked until explicit scoped owner consent is granted and recorded.
