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
| `P0-M00-WP02` | Forms runtime/storage/submission executable evidence protocol | `DONE` | `SHARED_CONTRACT` | `SERIALIZE` | Forms spec/runtime/topology/quality/ADR/readiness/checkpoint docs | ADR-0117; FM-01…FM-92 documented; 0 executed; FRT topology remains open. |
| `P0-M00-WP03` | Workflow/Cron scheduling/DST/claims evidence refinement | `SPECIFICATION` | `SHARED_CONTRACT` | `SERIALIZE` | Workflow Runtime + JobService + scheduler/cron evidence/governance docs | Current next planning work; P-003/P-011 remain execution-gated. |

No production implementation work package is active.

## 3. Current planning queue

Do not restart from zero.

| Order | Planning item | Current state | Dependency / note |
|---:|---|---|---|
| 1 | Workflow/Cron scheduling/DST/claims evidence refinement | `SPECIFICATION` current | Reconcile ADR-0059/0068/0082/0083 + P-003/P-011 |
| 2 | Notification fan-out/read/dedupe evidence protocol | `BLOCKED` by sequence | Depends on stable Job/Workflow semantics |
| 3 | Message & Chat transport/search/private-assets evidence protocol | `BLOCKED` by sequence | Later communication/runtime work |
| 4 | Webhooks & Connections signature/replay/Event Inbox/provider evidence protocol | `BLOCKED` by sequence | Provider/event evidence remains unexecuted |

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

Current future-development critical foundations remain primarily P-001 compatibility, P-003 Job, P-004 Definition, P-005 Vault, P-007 CI, P-008 build, P-009 Query, P-010 Relations, P-011 Workflow, P-012 Membership and P-013 Backup according to the Implementation Readiness Matrix.

## 5. WIP limits

Default approved implementation limits:
- maximum 3 active implementation work packages per milestone;
- maximum 1 writer for any `SERIALIZE` shared surface;
- maximum 1 unresolved high-risk migration/auth/secrets mutation in active implementation at a time;
- downstream work waits when it depends on an unverified shared contract.

Current implementation WIP is 0, so no limit is consumed.

Planning documentation work may advance serially without creating implementation authorization.

## 6. Shared-surface reservations

Use this table whenever parallel implementation begins.

| Shared surface | Work ID | Owner/writer | Mode | Dependents | Merge/integration order | Status |
|---|---|---|---|---|---|---|
| None | — | — | — | — | — | No active implementation reservations |

Typical shared surfaces include:
- Composer/npm manifests and lockfiles;
- DB schema/migrations;
- Definition Repository;
- auth/Policy/Vault;
- Module Registry;
- shared API/Ability schemas;
- global routing;
- Component Blueprint;
- JobService;
- Workflow Runtime;
- Entitlement engine;
- global config;
- CI/build/package/release metadata.

## 7. Parallelism decision record

When a new work package starts, record exactly one:
- `PARALLEL_SAFE`
- `COORDINATED_PARALLEL`
- `SERIALIZE`
- `BLOCKED`

For `COORDINATED_PARALLEL`/`SERIALIZE`, also record:
- shared surface;
- current owner;
- interface/change boundary;
- integration/merge order;
- post-integration verification.

## 8. Change-budget tracking

For implementation work record expected vs actual:
- files;
- modules;
- APIs/contracts;
- migrations/schema;
- dependencies/lockfiles;
- configuration/build/CI;
- tests/evidence.

Material unexplained expansion triggers:

`STOP → REASSESS → UPDATE IMPACT → RESCOPE OR SPLIT`

## 9. Ledger update rule

Update this ledger when:
- a material planning/implementation work package starts/stops/blocks/completes;
- shared-surface ownership changes;
- parallelism classification changes;
- critical path materially changes;
- WIP limit/exception is intentionally changed.

Do not update it for every tiny edit.

## 10. Current next safe action

Continue `P0-M00-WP03`: reconcile Workflow Runtime, JobService, Action Scheduler packaging, Cron/timezone/DST and concurrency/claim semantics into bounded executable evidence protocols.

Production implementation remains blocked until explicit scoped owner consent is granted and recorded.