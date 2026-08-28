# WPEssential — Project State & Adoption Baseline

Status: **Phase 0 governance / planning-only**  
Last reviewed: 2026-08-29

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
- **56/56** current module/platform surfaces are documented at Exhaustive product-option maturity;
- logical Multisite mapping is **56/56**;
- module-wide AI Prompt mapping is **56/56**;
- accepted planning/evidence decisions extend through **ADR-0207**;
- WP112 found additional exact supplemental evidence planning still required before approval-readiness;
- production implementation has not started;
- owner development consent has not been granted.

Historical 31/43/48/50/55-surface baselines remain valid snapshots of their earlier planning milestones; they are not current denominators.

## 2. State-transition rules

Project state changes only from verified evidence, never chat wording alone.

### PLANNED_EXISTING_PROJECT → ACTIVE_EXISTING_PROJECT
Requires:
- Phase 0 planning gaps closed and a later closure audit allows an approval-ready transition;
- explicit owner development consent under ADR-0014;
- relevant planning/technical entry gates satisfied for the approved milestone;
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
Use when repository/runtime/data/security state cannot be safely established, critical corruption/loss is suspected, deployment is unsafe, or migration/security state is materially ambiguous.

### RECOVERY → prior normal state
Only after evidence is preserved, authoritative state re-established, recovery verified, unresolved risks recorded and a safe checkpoint is restored.

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

Implementation/test/deployment/provider/AI claims must therefore be recorded `NOT EXECUTED` unless separately authorized and actually performed.

## 4. Current planning closure state

WP112 / ADR-0207 completed the first final pre-development closure audit and found P0 **not approval-ready**.

The remaining true planning gap is exact fixture expansion for 33 already-reserved market/competitive namespaces:
- WP113 — 1,232 fixtures;
- WP114 — 880 fixtures;
- WP115 — 1,936 fixtures;
- WP116 — 1,760 fixtures;
- total: **5,808 exact fixture definitions**.

Current safe planning work: **WP113**.

A later closure audit after WP116 decides whether the lifecycle can move from `SPECIFICATION` to `AWAITING_DEVELOPMENT_APPROVAL`. It must not move automatically.

## 5. Session capability detection

Before meaningful work, record actual available capabilities. Never infer access merely because the platform commonly supports it.

Capability values:
- `AVAILABLE`
- `READ_ONLY`
- `UNAVAILABLE`
- `UNKNOWN`

Record at minimum repository/filesystem, terminal/shell, database, WordPress runtime, tests, VCS provider actions, CI/CD, deployment, project planner and internet/current research when materially relevant.

### Latest planning-audit observation

| Capability | Observed state | Evidence/limitation |
|---|---|---|
| GitHub repository read | AVAILABLE | repository/docs/PR readable |
| GitHub planning documentation writes | AVAILABLE | planning branch docs updated |
| Pull-request metadata | AVAILABLE | Draft PR #1 readable/updateable |
| Linear planning mirror | AVAILABLE | planning issues/project synchronized |
| Branch protection/rulesets | UNKNOWN | no current verified provider evidence recorded here |
| Local filesystem/working tree | UNKNOWN | not established by GitHub planning audit |
| Terminal/shell | UNKNOWN | not established by GitHub planning audit |
| WordPress runtime | NOT EXECUTED / capability not relied upon | runtime prohibited pre-consent |
| Database runtime | NOT EXECUTED / capability not relied upon | DB execution prohibited pre-consent |
| CI execution result | UNKNOWN / NOT EXECUTED for current planning | no runtime certification claim |
| Deployment | NOT EXECUTED | production development not started |

Future sessions refresh capability state when materially relevant.

## 6. Existing-project adoption workflow

Use:

`Inspect → Baseline → Audit Existing Plan → Compare Plan With Reality → Identify Gaps → Amend Plan → Preserve Existing Work → Continue Safely`

Do not restart the project, rebuild from zero, overwrite accepted ADRs, discard unknown work or rewrite working implementation merely for stylistic consistency.

## 7. Bidirectional adoption ledger

Plan → Repository statuses:
- `NOT_STARTED`
- `PARTIALLY_IMPLEMENTED`
- `IMPLEMENTED_NOT_VERIFIED`
- `VERIFIED`
- `DIFFERS_FROM_PLAN`
- `UNKNOWN`

Repository → Plan statuses:
- `DOCUMENTED`
- `PARTIALLY_DOCUMENTED`
- `UNDOCUMENTED`
- `OBSOLETE`
- `UNKNOWN_PURPOSE`

### Current WPEssential baseline

| Area | Plan → Repository | Repository → Plan | Notes |
|---|---|---|---|
| 56 module/platform surfaces | NOT_STARTED | DOCUMENTED | 56/56 Exhaustive; 0/56 authorized |
| Shared platform architecture | NOT_STARTED | DOCUMENTED | accepted paper contracts/ADRs only |
| Detailed universal/adapter evidence | NOT_STARTED | DOCUMENTED | SBP/ANL/SRH/DEC/LED/RSV/PLC/EXP/DOC/SYN/GEO/AIP/WCA exact; execution 0 |
| Supplemental market/competitive exact evidence | NOT_STARTED | PARTIALLY_DOCUMENTED | 33 namespaces fixed at group-envelope level; WP113–WP116 must enumerate 5,808 fixtures |
| Production PHP/React implementation | NOT_STARTED | N/A | intentionally absent |
| Database/runtime migrations | NOT_STARTED | N/A | no production schema execution |
| CI/build runtime | NOT_STARTED | DOCUMENTED | protocols/plans exist; execution pending |
| Production deployment | NOT_STARTED | N/A | no production release |

Do not create a second conflicting current-state source; `CHECKPOINT.md`, the readiness matrix and current ADRs must stay synchronized.

## 8. Gap classification

Every discovered plan/repository gap is classified as `CORRECTION`, `COMPLETION`, `HARDENING`, `OPTIMIZATION` or `NEW_PRODUCT_SCOPE`.

WP112 additionally classifies readiness blockers as:
- `PLANNING GAP`;
- `RUNTIME EVIDENCE PENDING`;
- `PROVIDER CERTIFICATION PENDING`;
- `OWNER CONSENT PENDING`;
- `NO GAP / READY AS PLAN`.

New product scope is never silently treated as implementation approval.

## 9. Implementation Baseline / Adoption Gate

Before the first production code change after explicit consent, create a bounded implementation baseline. Do not redo Phase 0 planning.

Record exact branch/revision, worktree state where accessible, runtime/tool versions, dependencies/lockfiles, baseline build/test commands/failures, CI status, security blockers, migration/schema baseline, environment assumptions, verified VCS protections and first approved milestone.

## 10. Baseline failure registry rule

A failure observed before a current implementation change is `BASELINE FAILURE`. Record check/test, baseline revision, first observation, exact summary, blocking classification, related work item and owner if known. Do not attribute pre-existing failures to new work.

## 11. VCS/protection audit

Before implementation/release, verify where available protected branches/rulesets, required reviews/checks, CODEOWNERS, merge queue/train, tag/release protection, deployment approvals and security scans. Unknown provider state remains `UNKNOWN`; protections must not be weakened merely for speed.

## 12. Authority order

For actual state use:

1. repository/code;
2. database/schema/config;
3. observed execution;
4. executed tests;
5. CI/CD result;
6. VCS history;
7. approved documentation/ADRs;
8. maintained project checkpoint/memory;
9. previous AI conversation.

Conversation memory never overrides repository evidence.

Production development authorization remains **NOT GRANTED / 0/56**.