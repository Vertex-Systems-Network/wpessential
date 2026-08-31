# WPEssential — Engineering Execution Governance

Status: **Active governance / implementation rules predeclared**  
Last reviewed: 2026-09-01

## 1. Purpose

This file governs how approved engineering work is split, coordinated, verified and reported. It exists to maximize safe throughput without giant AI diffs, duplicated foundations or conflicting parallel work.

It does not grant development permission. ADR-0014 and `DEVELOPMENT-CONSENT.md` remain authoritative.

## 2. Critical-path classification

Every approved work package should be classified as one of:

- `BLOCKING_FOUNDATION`
- `SHARED_CONTRACT`
- `HIGH_RISK_UNKNOWN`
- `INDEPENDENT_FEATURE`
- `INTEGRATION`
- `REGRESSION`
- `RELEASE`

Prefer execution order:

`BLOCKING_FOUNDATION → SHARED_CONTRACT → HIGH_RISK_UNKNOWN → INDEPENDENT_FEATURE → INTEGRATION → REGRESSION → RELEASE`

Do not build large downstream systems on an unverified shared assumption merely because they are easier to start.

## 3. Small-batch change budget

Before implementation of a meaningful task estimate:
- expected files;
- expected modules;
- public/internal APIs affected;
- migrations/schema changes;
- dependencies/lockfiles;
- configuration/build/CI surfaces;
- tests/evidence expected.

If actual scope expands materially beyond the estimate, use:

`STOP → REASSESS → UPDATE IMPACT → RESCOPE OR SPLIT`

Material expansion indicators include:
- unrelated modules suddenly affected;
- unexpected shared-contract change;
- migration appears where none was expected;
- new dependency is required;
- broad rename/reformat/rearchitecture becomes necessary;
- public API compatibility changes;
- diff becomes difficult to review as one coherent unit.

Do not continue merely to finish the original task label.

## 4. No unrelated cleanup

During feature/fix work do not automatically:
- reformat the whole repository;
- rename unrelated symbols/files;
- reorganize unrelated folders;
- upgrade unrelated dependencies;
- rewrite architecture for consistency;
- change unrelated APIs;
- fix every technical-debt item discovered.

Record unrelated improvements as separate debt/work items. Critical/High debt that directly threatens the current approved work may be addressed if it is documented in impact/scope.

## 5. Parallel-work classification

Every concurrent work package is one of:

- `PARALLEL_SAFE` — independent files/contracts; ordinary integration risk.
- `COORDINATED_PARALLEL` — can proceed concurrently but touches shared interfaces/surfaces requiring explicit ownership/merge order.
- `SERIALIZE` — only one active writer/change stream should modify the shared surface at a time.
- `BLOCKED` — cannot safely start until dependency/evidence/approval completes.

## 6. Shared surfaces

Treat these as coordinated or serialized by default:
- Composer/npm manifests and lockfiles;
- DB schema/migrations;
- Definition Repository/storage primitives;
- authentication/authorization/Policy core;
- Vault/secrets;
- Module Registry/lifecycle;
- shared API/Ability schemas;
- global REST routing;
- Component Blueprint/core renderer;
- JobService/backend mapping;
- Entitlement engine;
- global configuration;
- CI/build/package configuration;
- release/version metadata;
- repository governance files when multiple agents are active.

Two autonomous agents must not silently overwrite the same shared surface.

## 7. Shared-surface ownership record

For `COORDINATED_PARALLEL` or `SERIALIZE` work record:
- work ID;
- shared surface;
- current owner/writer;
- dependents;
- expected interface change;
- integration/merge order;
- conflict/rebase strategy;
- verification required after integration.

Ownership coordinates edits; it does not imply permanent code ownership.

## 8. WIP limits

Default execution WIP unless a milestone documents a better reason:
- maximum 3 active implementation work packages per milestone;
- maximum 1 active writer on a `SERIALIZE` shared surface;
- maximum 1 unresolved high-risk migration/auth/secret change in active implementation at a time;
- downstream work blocked if it depends on an unverified shared contract.

Planning/research can be broader, but implementation concurrency must remain reviewable and integratable.

## 9. Integration frequency

Prefer:
- short-lived branches/workspaces where review is required;
- frequent integration after coherent verified changes;
- small commits that explain intent;
- revalidation after shared-contract integration.

Do not accumulate a giant branch merely to reduce commit count.

## 10. Two-speed verification

### FAST GATE

Run after bounded implementation changes as applicable:
- relevant formatter/coding standard;
- lint;
- typecheck/static analysis;
- targeted unit tests;
- targeted integration/permission tests;
- affected production build;
- targeted security/static checks.

Purpose: fast feedback on the changed surface.

### FULL GATE

Run at milestone/release boundaries as applicable:
- broad unit/integration suites;
- E2E;
- migration/upgrade/recovery tests;
- authorization/security regression;
- dependency/security audits;
- compatibility matrix;
- production build/package checks;
- broader regression/performance evidence.

FAST GATE never substitutes a required FULL GATE.

## 11. Flaky-test policy

A test that intermittently fails without an intended state change is a defect.

Forbidden:
- rerun-until-green and then report success;
- hide flaky failure as “passed after retry” without recording it;
- weaken a correct assertion only to reduce flakiness.

If quarantine is temporarily necessary, record:
- test ID/name;
- failure signature;
- evidence of flakiness;
- linked defect/work item;
- owner;
- blocking/non-blocking release classification;
- quarantine reason;
- expiry/review date;
- replacement verification if needed.

## 12. Baseline failures

Use the `BASELINE FAILURE` registry defined in `PROJECT-STATE-AND-ADOPTION.md`.

New work must distinguish:
- introduced regression;
- pre-existing baseline failure;
- flaky/uncertain failure;
- environment/tooling failure.

Never silently fix unrelated baseline failures unless they block or materially threaten approved work.

## 13. Negative requirements

Every substantial module/milestone must include explicit `MUST NOT` behavior where security/data/product boundaries matter.

Important negative requirements become tests/evidence.

Examples:
- a site administrator MUST NOT mutate another site's protected resource;
- a Profile field MUST NOT mutate roles/capabilities/password/session secrets;
- route/menu hiding MUST NOT be treated as authorization;
- retries MUST NOT duplicate a committed non-idempotent side effect.

## 14. Review classification

Every meaningful implementation review is labeled:
- `INDEPENDENT REVIEW` — reviewer did not author the change and has adequate context;
- `SELF REVIEW` — same AI/person authored and reviewed the change;
- `AUTOMATED REVIEW` — static/automated review only.

Do not represent SELF REVIEW or automated checks as independent human/agent review.

Review must examine requirements, scope, architecture, security, authorization, data, concurrency, errors, tests, performance, observability, compatibility, deployment/rollback and diff cleanliness.

## 15. Planner-only compatibility

When repository execution is unavailable or intentionally prohibited, set:

`EXECUTION_MODE = PLANNER_ONLY`

Continue to produce:
- architecture;
- module specs;
- milestones/work packages/tasks;
- dependencies;
- security/test/risk plans;
- approval boundaries;
- evidence protocols.

Mark executable outcomes:

`NOT EXECUTED`

Never claim code/tests/build/deployment occurred from planning artifacts alone.

## 16. Provider/VCS capability fallback

Adapt to the actual VCS/provider.

If a provider capability is unavailable/inaccessible:
- record `UNKNOWN` or `UNAVAILABLE`;
- use repository/local evidence where legitimate;
- do not invent branch protection, CI, review or deployment state;
- do not weaken known protections merely to continue faster.

GitHub terminology must not be forced onto SVN/another provider in a different project.

## 17. Change impact contract

For substantial changes record:

**Affected** — modules/APIs/data/users  
**Unaffected** — explicitly stable areas  
**Risk** — likely failure modes  
**Migration** — existing-data/runtime transition  
**Rollback/Recovery** — recovery route/class  
**Verification** — FAST/FULL evidence

## 18. End-of-task report minimum

After meaningful engineering work report concisely:

- **Status**
- **Changed**
- **Why**
- **Research performed**
- **Tests/checks**
- **Security**
- **Data/migration**
- **Affected areas**
- **VCS/commit**
- **Documentation/Memory updated**
- **Known issues**
- **Not verified**
- **Next safe action**

Do not generate volume for its own sake.

## 19. Definition of Done reinforcement

`DONE` requires applicable:
- approved implementation complete;
- acceptance criteria satisfied;
- FAST/FULL gates appropriate to boundary;
- security/review complete;
- errors/data integrity/concurrency considered;
- integration/compatibility verified;
- documentation/checkpoint/history updated;
- limitations recorded;
- rollback/recovery understood.

Otherwise use `PARTIALLY_COMPLETE`, `BLOCKED`, `VERIFYING` or another truthful state.

## 20. Detailed multi-agent module coordination protocol

This section is mandatory whenever two or more autonomous agents, coding agents, planning agents or workspaces operate on WPEssential concurrently. It extends Sections 5–9 and the `Safe parallel development` rule in root `AGENTS.md`.

### 20.1 Mandatory preflight for every agent

Before research, planning, Options Bank work, UI derivation or implementation, each agent must read the current versions of:

- root `AGENTS.md`;
- `CONTRIBUTING.md`;
- `config/product/competitor-parity-surfaces.json`;
- `config/product/options-bank-progress.json` when Options Bank work is involved;
- `docs/ARCHITECTURE/CROSS-MODULE-OPTION-OWNERSHIP-AND-NO-BYPASS-CONTRACT.md`;
- `docs/ARCHITECTURE/CANONICAL-56-SURFACE-DEPENDENCY-RELATION-MATRIX.md`;
- relevant surface-specific Bank, audit, architecture, product and test files.

The worker records internally the exact base SHA, surface ID/key, current lifecycle status, canonical semantic/storage/execution owner, allowed peer integrations, forbidden coupling and intended write set.

Repository evidence outranks stale agent memory or prior chat summaries.

### 20.2 One surface / one primary writer / one branch

- A surface/task has one primary writer at a time.
- Two primary agents must not edit overlapping files for the same surface concurrently.
- Research/specialist sub-agents may assist only as read-only workers or with explicitly non-overlapping outputs.
- Every worker branch starts from an explicit known `main` SHA.
- No agent overwrites or force-moves another agent's branch/ref.

Recommended branch patterns:

- `planning/options-bank-<surface>-<stage>-vN`
- `planning/<surface>-<audit-or-review>-vN`
- `implementation/<surface>-<milestone>`
- `fix/<surface>-<issue>`

### 20.3 Module-local write ownership

A parallel module worker normally owns only module-local artifacts, such as:

- `config/product/options-bank/<surface>*.json`;
- `config/product/options-bank-audits/<surface>*.json`;
- surface-specific schemas that are not shared by other surfaces;
- `tests/Smoke/<surface>-*.php` and equivalent surface-specific tests;
- surface-specific product/module/architecture/quality docs;
- surface-specific runtime/implementation files when implementation is authorized.

Keep the diff surface-scoped. Do not perform unrelated cleanup while a parallel integration program is active.

### 20.4 Shared/global files are single-writer integrator territory

Unless an agent is explicitly assigned the current coordinator/integrator role, parallel module workers must not independently update shared truth/registration files, including:

- root README progress/dashboard state;
- `config/product/options-bank-progress.json`;
- global/cross-surface `STATUS.md` files;
- `config/product/competitor-parity-surfaces.json`;
- canonical ownership/dependency registries;
- generic/shared schemas;
- `composer.json`, lockfiles and shared test registration;
- generic smoke/global test aggregators;
- CI/build/package configuration;
- shared release/version metadata;
- shared architecture/governance contracts;
- any file currently owned by another active writer.

When a module requires one of these changes, the worker records an **Integration Requirement** in its PR/handoff with the exact requested change. The integrator applies the shared change once after reconciling all active branches.

### 20.5 Canonical ownership wins over local convenience

Parallel work must never create duplicate semantic engines.

Examples:

- Surface 5 Status owns canonical state/transition semantics; Workflow may request or observe transitions but must not privately mutate protected state.
- Surface 4 Relations owns persistent relation/cardinality/pivot semantics; Fields, Forms and Listings may reference/consume Relations but must not create hidden relation stores.
- Surface 6 Query owns structured query semantics; Listings and Admin Columns consume public query/data-source contracts rather than invent private SQL engines.
- Surface 30 Roles owns role/capability definitions; peers consume capabilities/Policy rather than building local role editors.
- Surface 23 Connections owns HTTP/OAuth/webhook transport policy; peers do not build private credential/retry/signature stacks.

If two agents discover the same semantic, resolve the owner from the canonical ownership contract. The non-owner records a typed reference, integration, compatibility mapping or explicit out-of-surface disposition. Do not duplicate the capability in two canonical Banks.

### 20.6 No peer-private access

Modules interact only through approved interfaces, Abilities, Events, Data Source contracts, registries or adapters.

Forbidden shortcuts include:

- writing another module's private table/options/meta directly;
- importing peer-private implementation classes when a public contract exists;
- silently cloning a disabled peer's behavior as fallback;
- introducing undocumented peer hard dependencies;
- bypassing owner Policy/Ability/storage paths from UI, REST, Workflow, Cron, CLI or AI.

### 20.7 Options Bank → UX derivation is autonomous

A module agent must not repeatedly ask the repository owner to restate ordinary UI tabs, option lists or control structure when these can be derived from repository evidence and research.

Use:

`Master Options Bank → semantic/disposition review → UX projection → implementation contract`

A Bank record is not automatically a UI control. Classify it as appropriate, for example:

- normal user-configurable;
- advanced configurable;
- provider configurable;
- derived/effective state;
- runtime/internal;
- diagnostic;
- compatibility/migration-only;
- deferred;
- rejected unsafe.

Derived/internal/deferred/rejected records must not become duplicate authored controls merely to make the UI appear complete.

### 20.8 Options Bank lifecycle promotion

Canonical lifecycle:

`UNSEEDED → BANK_SURFACE_SEEDED → NATIVE_AUDITED → MARKET_AUDITED → BANK_REVIEWED`

A worker may prepare a candidate but shared lifecycle truth is promoted only when the appropriate audit/review evidence exists and exact-head executable gates pass.

`BANK_REVIEWED` means the surface Bank is ready to feed implementation contracts. It does not claim runtime implementation, release, deployment or production verification.

### 20.9 Coordinator / integrator responsibilities

For concurrent module work, one integration lane must:

1. track active branches and exact base SHAs;
2. prevent overlapping writer ownership;
3. reconcile semantic conflicts using canonical ownership;
4. apply shared-file Integration Requirements once;
5. recompute global Bank counts/status from repository truth;
6. update progress/README/status only from verified state;
7. serialize merges where shared truth or dependency changes collide;
8. require stale branches to synchronize with current main;
9. rerun applicable exact-head CI after integration;
10. refuse force-merge/force-overwrite as a substitute for conflict resolution.

### 20.10 Stale branch and merge protocol

Before final merge, if `main` moved after a worker started:

1. sync/rebase/rebuild the candidate onto latest main;
2. resolve semantic conflicts, not only textual conflicts;
3. re-audit counts, ownership and dependency assumptions;
4. rerun module-specific and global contracts;
5. treat the new head SHA as the only valid merge candidate.

CI from an old head is not evidence for a new head.

PR/handoff should include at minimum:

- surface and lifecycle stage;
- original base SHA;
- exact candidate head SHA;
- files/semantics owned by the branch;
- Bank count changes where applicable;
- native/market evidence used;
- explicit unresolved items;
- cross-surface ownership/dependency decisions;
- Integration Requirements;
- tests/CI executed and their exact-head result.

### 20.11 Exact-head gate and failure handling

Every applicable merge gate must be green on the exact candidate head.

When a gate fails:

- stop promotion/merge;
- inspect the actual failure;
- fix source/data/contract inconsistency when the test is correct;
- do not weaken/delete/bypass a valid gate for convenience;
- change a test only when it encodes stale or incorrect architecture, and document the reason.

A documentation-only PASS never substitutes for failed executable evidence.

### 20.12 Conflict-resolution hierarchy

When concurrent findings disagree, resolve in this order:

1. canonical semantic ownership / no-bypass contract;
2. canonical dependency matrix;
3. current WordPress/native evidence;
4. current official provider/market evidence;
5. existing certified Bank/audit state;
6. explicit WPE future/exceed design.

If the resolution changes a shared ownership/architecture contract, it is an integration/architecture decision rather than a module-local edit.

### 20.13 Planning parallelism versus runtime implementation

Options Bank discovery, native audits, market audits and surface documentation may run broadly in parallel when write ownership is isolated.

Runtime implementation remains dependency-aware. Consumers may code against stable public interfaces/fixtures, but must not depend on uncertified peer-private implementation details.

### 20.14 Clean parallel result definition

A parallel result is acceptable only when:

- surface ownership is unambiguous;
- no duplicate peer semantic engine was created;
- no active worker's file ownership was overwritten;
- shared changes were reconciled through the integrator;
- latest main was incorporated before final merge;
- counts/status claims match repository truth;
- applicable exact-head CI passes;
- other active branches can rebase/reconcile without hidden architectural breakage.

Compilation alone is not sufficient evidence of a clean multi-agent result.
