# WPEssential — Status Manager Executable Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Related: ADR-0038, `docs/ARCHITECTURE/STATUS-MANAGER-STATE-MACHINE-RUNTIME.md`, Workflow, JobService, Query, Policy, Import, ADR-0014.

## 1. Purpose

Define evidence required before WPEssential can claim Status Manager production readiness for either:

1. WordPress Post Status Adapter; or
2. Generic Domain State Machine.

These remain separate engines. A passing Post Status profile does not certify generic state-machine storage/concurrency, and vice versa.

## 2. Certification profile

A future evidence report records:
- WordPress/PHP/database versions;
- single-site/Multisite topology;
- active post types/status providers relevant to fixtures;
- WPE Definition/Policy/Query/Workflow/Job versions;
- Data Source adapter and current-state storage class;
- history storage topology/index profile;
- concurrency primitive used by target Data Source;
- import/migration profile;
- REST/Form/Dashboard adapters exercised.

## 3. WordPress Post Status fixtures

### SM-01 — WPE custom status registration
A WPE-owned status registers and is queryable/renderable according to its declared visibility semantics.

### SM-02 — Core status preservation
Core statuses remain intact; ordinary WPE operations cannot destructively unregister/delete them.

### SM-03 — Third-party status preservation
Unknown/third-party statuses are inventoried/preserved and not treated as invalid merely because WPE did not create them.

### SM-04 — Status key constraints
Configured key length/format is validated against certified WordPress/database behavior before publish/migration.

### SM-05 — Post-type availability overlay
WPE UI offers status only on configured post types while truthfully acknowledging global WordPress status registration semantics.

### SM-06 — Admin edit integration
Certified edit screen shows allowed WPE status transition choices without bypassing capability/Policy.

### SM-07 — Quick edit integration
Quick-edit support is enabled only after explicit fixture; unsupported screen/version degrades without false support claim.

### SM-08 — Bulk edit integration
Bulk transition reauthorizes every target and reports partial/denied outcomes truthfully.

### SM-09 — List filter/query
Filtering by custom status returns correct result set/counts without unrelated post-type leakage.

### SM-10 — REST/API transition
REST/Ability path validates current state, target, actor and resource; direct generic field write cannot bypass managed transition semantics where enforcement is claimed.

### SM-11 — Form transition
Frontend/admin Form action invokes the same transition authority and cannot forge a disallowed target status.

### SM-12 — Dashboard transition
Frontend Dashboard UI visibility does not authorize; direct action rechecks Policy and expected current status.

### SM-13 — Label rename
Presentation rename leaves stored machine key/data stable.

### SM-14 — Machine-key migration preview
Key change produces affected-post/dependency Plan before mutation; no blind direct rename.

### SM-15 — Key migration execution
Bounded migration registers target, updates certified rows, verifies counts/references and preserves recoverability before retiring old WPE status.

### SM-16 — Archive status with existing posts
Archived WPE status stops ordinary new assignment while existing content remains readable/queryable according to defined compatibility semantics.

### SM-17 — Remove after migration
WPE-owned status can be removed only after zero compatible remaining references or verified migration policy.

### SM-18 — Direct third-party write coverage
Evidence documents whether direct core/plugin writes can bypass WPE transition guards; UI/marketing must not claim stronger enforcement than observed.

### SM-19 — Core transition hook side effects
WPE transition does not double-fire WPE Workflow/Notification side effects when WordPress hooks also observe the same authoritative change.

### SM-20 — Scheduled/future post compatibility
WPE custom statuses do not silently break certified scheduling/date-floating/core publish semantics outside declared scope.

## 4. Generic Domain State Machine fixtures

### SM-21 — Definition publish validation
Missing initial state, duplicate state keys, invalid transitions and references prevent publish with actionable diagnostics.

### SM-22 — Fixed initial state
New entity receives the declared initial state exactly once through authoritative creation path.

### SM-23 — Conditional initial state
Condition-derived initial state is deterministic and bounded; unresolved condition does not silently pick the first visual state.

### SM-24 — Imported initial state mapping
Unknown source state is conflict/unsupported unless explicit mapping exists; label similarity alone never remaps.

### SM-25 — Allowed transition
Valid actor/resource/current-state/guard combination commits target state and history.

### SM-26 — Denied actor
UI-visible transition cannot execute when actor lacks required capability/Policy.

### SM-27 — Failed guard/required field
Transition is rejected before authoritative state change and returns structured validation result.

### SM-28 — Terminal state
No outgoing ordinary transition is available unless a named reopen/reactivate transition is defined.

### SM-29 — Reopen transition
Reopen is explicit, authorized and audited; direct value overwrite is not the normal path.

### SM-30 — Optimistic concurrency race
Two requests from same old state cannot both silently win; one succeeds and stale contender receives deterministic conflict/retry semantics.

### SM-31 — History atomicity/reconciliation
State commit and transition history either satisfy the certified atomic boundary or enter a durable reconciliable state; no silent state-without-history success where history is required.

### SM-32 — Duplicate idempotency key
Retry/duplicate delivery does not create repeated transition/history/side effects beyond declared idempotency semantics.

### SM-33 — Force-state repair
High-risk repair path requires elevated authority, reason and audit; it is distinct from ordinary transitions.

### SM-34 — Custom-table current-state storage
Dedicated typed-column adapter passes read/filter/update/concurrency fixtures under its certified schema.

### SM-35 — Meta current-state storage
Post/user/term meta adapter is only certified for appropriate workloads and proves stale-write/concurrency behavior for its supported class.

### SM-36 — External/provider state adapter
External state is not marked locally authoritative until certified reconciliation/unknown-outcome semantics resolve.

### SM-37 — Query Builder integration
Current-state filter/sort uses typed authoritative field/provider and returns correct paginated counts.

### SM-38 — Transition history query
History reads are paginated/bounded and authorization/privacy aware; no unbounded per-entity history scan.

### SM-39 — Workflow reaction
State commits before post-commit Workflow reaction; Workflow failure does not silently revert state unless a specifically certified transactional boundary says so.

### SM-40 — Workflow-requested transition
Workflow can request another transition only through same transition engine/Policy; no raw state write shortcut.

### SM-41 — Timed transition
JobService executes due transition with pinned machine revision/current-state check; delayed job does not blindly apply stale transition.

### SM-42 — Duplicate/late Job
At-least-once Job delivery and lateness cannot produce repeated/stale transition success.

### SM-43 — Machine revision publish during active entities
Existing current states remain resolvable; removed/renamed state requires explicit migration/mapping semantics.

### SM-44 — Missing target Data Source
Machine becomes degraded/disabled without deleting state/history or fataling unrelated WPE surfaces.

### SM-45 — Multisite same machine key
Site scope separates machine definitions/entity state/history for identical keys/IDs across sites.

### SM-46 — Import with history
Imported history is accepted only when source semantics are known; fabricated transition history is never inferred from current state alone.

### SM-47 — Pro expiry
Safe deployed state rendering/required transitions follow ADR-0007; definition editing can lock without corrupting current state.

### SM-48 — Large history/concurrency performance
Reference large dataset/race workload meets bounded query/index/lock budgets without weakening correctness.

## 5. Pass gates

Status support fails when any certified-scope fixture shows:
- core/third-party status destructive loss;
- unmanaged direct write bypass while WPE claims enforced transition authority;
- stale concurrent transitions both succeeding incorrectly;
- state change without required recoverable history truth;
- duplicate Job/request creating repeated transition side effects;
- unknown import state silently remapped;
- UI visibility used as authorization;
- cross-site state/history leakage;
- status-key migration leaving orphaned/ambiguous content.

## 6. Required future evidence report

Include:
- runtime/storage profile;
- SM-01…SM-48 pass/fail/NA;
- exact Post Status integration coverage;
- migration/recovery evidence;
- concurrency/idempotency results;
- history query/index measurements;
- Workflow/Job integration evidence;
- REST/Form/Dashboard bypass tests;
- Multisite results;
- declared enforcement limitations.

## 7. Current state

**SM fixtures executed: 0/48.**

No status registration, post mutation, state-machine transition, DB migration, Workflow/Job execution or runtime test has occurred.

## 8. Development gate

Execution requires explicit owner consent under ADR-0014.