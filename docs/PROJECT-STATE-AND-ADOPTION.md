# WPEssential — Project State & Adoption Baseline

Status: **Phase 0 governance / planning-only**  
Last reviewed: 2026-08-28

## 1. Canonical project-state taxonomy

Every engineering session must classify the project using exactly one primary state:

- `GREENFIELD` — no meaningful existing implementation or plan.
- `PLANNED_EXISTING_PROJECT` — substantial product/architecture/specification exists; implementation is absent or limited.
- `ACTIVE_EXISTING_PROJECT` — production code development is underway and tracked.
- `PRODUCTION_PROJECT` — real users/data/deployments depend on the system.
- `LEGACY_OR_MIGRATION` — primary goal is modernization/migration of an existing system.
- `RECOVERY` — repository/application state is broken, corrupted, unsafe or materially uncertain.

### Current WPEssential state

`PLANNED_EXISTING_PROJECT`

Reason:
- 31/31 module/platform surfaces are documented at exhaustive planning maturity;
- architecture/evidence contracts exist through ADR-0116;
- production implementation has not started;
- owner development consent has not been granted.

## 2. State-transition rules

Project state changes only from verified evidence, never chat wording alone.

### PLANNED_EXISTING_PROJECT → ACTIVE_EXISTING_PROJECT
Requires:
- explicit owner development consent under ADR-0014;
- relevant planning/technical entry gates satisfied;
- implementation baseline recorded;
- safe implementation branch/workspace confirmed;
- first approved implementation milestone entered `IMPLEMENTING`.

### ACTIVE_EXISTING_PROJECT → PRODUCTION_PROJECT
Requires:
- a released deployment used by real users/data;
- release revision recorded;
- migrations/configuration recorded;
- production verification completed;
- rollback/recovery route known.

### Any state → RECOVERY
Use when:
- repository state cannot be safely understood;
- critical data loss/corruption is suspected;
- deployment is broken or unsafe;
- migration state is ambiguous;
- security compromise/bypass requires containment;
- current authoritative state cannot be established.

### RECOVERY → prior normal state
Only after:
- evidence preserved;
- authoritative state re-established;
- recovery verified;
- unresolved risks recorded;
- safe next action checkpointed.

## 3. Execution mode

Track execution mode separately from project state:

- `PLANNER_ONLY`
- `READ_ONLY_AUDIT`
- `IMPLEMENTATION`
- `VERIFICATION`
- `RELEASE`
- `INCIDENT`

### Current mode

`PLANNER_ONLY`

Implementation/test/deployment claims must therefore be recorded `NOT EXECUTED` unless separately authorized and actually performed.

## 4. Session capability detection

Before meaningful work, record actual available capabilities. Never infer access merely because the platform commonly supports it.

Capability values:
- `AVAILABLE`
- `READ_ONLY`
- `UNAVAILABLE`
- `UNKNOWN`

Record at minimum:
- repository/filesystem;
- terminal/shell;
- database;
- WordPress runtime;
- tests;
- VCS provider actions;
- CI/CD;
- deployment;
- project planner;
- internet/current research.

### Latest adoption-audit observation

| Capability | Observed state | Evidence/limitation |
|---|---|---|
| GitHub repository read | AVAILABLE | repository/docs/PR readable |
| GitHub documentation writes | AVAILABLE | planning branch documentation can be updated |
| Pull-request metadata | AVAILABLE | Draft PR #1 readable/updateable |
| Branch protection | UNKNOWN | provider integration returned 403; do not infer configured/not configured |
| Repository rulesets | UNKNOWN | provider access/plan limitation prevented verification |
| Local filesystem/working tree | UNKNOWN | not established by this GitHub-only audit |
| Terminal/shell | UNKNOWN | not established by this GitHub-only audit |
| WordPress runtime | UNKNOWN | not established; no runtime execution allowed pre-consent |
| Database | UNKNOWN | not established; no DB execution allowed pre-consent |
| CI result | UNKNOWN | not established in this audit |
| Deployment | UNKNOWN | not established; production development not started |

This table is a baseline example, not a permanent guarantee. Future sessions must refresh capability state when materially relevant.

## 5. Existing-project adoption workflow

For an existing project use:

`Inspect → Baseline → Audit Existing Plan → Compare Plan With Reality → Identify Gaps → Amend Plan → Preserve Existing Work → Continue Safely`

Do not:
- restart the project;
- rebuild from zero;
- replace architecture merely because another design appears cleaner;
- overwrite accepted plans/ADRs;
- discard unknown work;
- rewrite working code only for stylistic consistency.

## 6. Bidirectional adoption ledger

### Plan → Repository statuses

Use one:
- `NOT_STARTED`
- `PARTIALLY_IMPLEMENTED`
- `IMPLEMENTED_NOT_VERIFIED`
- `VERIFIED`
- `DIFFERS_FROM_PLAN`
- `UNKNOWN`

### Repository → Plan statuses

Use one:
- `DOCUMENTED`
- `PARTIALLY_DOCUMENTED`
- `UNDOCUMENTED`
- `OBSOLETE`
- `UNKNOWN_PURPOSE`

### Current WPEssential baseline

| Area | Plan → Repository | Repository → Plan | Notes |
|---|---|---|---|
| 31 module/platform surfaces | NOT_STARTED | DOCUMENTED | exhaustive planning, 0 authorized |
| Shared platform architecture | NOT_STARTED | DOCUMENTED | paper contracts/ADRs only |
| Evidence protocols | NOT_STARTED | DOCUMENTED | protocols defined; runtime fixtures 0 |
| Production PHP/React implementation | NOT_STARTED | N/A | implementation intentionally absent |
| Database/runtime migrations | NOT_STARTED | N/A | no production schema execution |
| CI/build runtime | NOT_STARTED | PARTIALLY_DOCUMENTED | policy/ADR direction exists; execution pending |
| Production deployment | NOT_STARTED | N/A | no production release |

Future implementation must update this ledger or an equivalent maintained implementation-readiness source. Do not create a second conflicting truth source.

## 7. Gap classification

Every discovered plan/repository gap must be classified:

- `CORRECTION` — existing plan/implementation is wrong or unsafe.
- `COMPLETION` — intended scope is incomplete.
- `HARDENING` — improves security/reliability/recovery/operability without changing intended product scope.
- `OPTIMIZATION` — improves performance/maintainability/throughput without changing intended behavior.
- `NEW_PRODUCT_SCOPE` — materially new user-facing capability/business behavior.

Rules:
- CORRECTION/COMPLETION/HARDENING/OPTIMIZATION may be proposed automatically when clearly necessary.
- `NEW_PRODUCT_SCOPE` is never silently treated as approved implementation scope.
- Major new product scope requires explicit owner approval before it enters an implementation milestone.

## 8. Implementation Baseline / Adoption Gate

Before the first production code change after consent, create a bounded implementation baseline. Do not redo Phase 0 planning.

Record:
- exact branch/revision;
- working-tree/uncommitted/staged/untracked state where accessible;
- merge/rebase/cherry-pick state;
- runtime/tool versions;
- dependency/lockfile state;
- baseline build/test commands;
- baseline test failures;
- current CI status;
- security-critical open blockers;
- migrations/schema baseline;
- environment assumptions;
- VCS protections actually verified;
- first approved milestone/work package.

This is the logical `Milestone 0 — Baseline & Adoption` only when no reliable implementation baseline already exists.

## 9. Baseline failure registry rule

A failure observed before the current change is labeled:

`BASELINE FAILURE`

Record:
- check/test name;
- baseline revision;
- first observed date;
- exact failure summary;
- blocking/non-blocking classification;
- related issue/work item;
- owner if known.

Do not attribute a pre-existing failure to new work and do not modify unrelated code merely to make all checks green.

## 10. VCS/protection audit

Before implementation/release, inspect where available:
- protected branches/rulesets;
- required reviews;
- required checks;
- CODEOWNERS;
- merge queue/train;
- tag/release protection;
- deployment approvals;
- security scans.

If provider access cannot verify an item, record `UNKNOWN`. Never weaken protection merely to let AI merge faster.

## 11. Authority order

For actual state use:

1. repository/code;
2. database/schema/config;
3. observed execution;
4. executed tests;
5. CI/CD result;
6. VCS history;
7. approved documentation/ADRs;
8. maintained project memory/checkpoint;
9. previous AI conversation.

Conversation memory never overrides repository evidence.