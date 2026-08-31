# WPEssential — Engineering Execution Governance

Status: **Active governance / implementation rules predeclared**  
Last reviewed: 2026-08-28

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