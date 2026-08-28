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
- **56/56** module/platform surfaces are documented at exhaustive product-option maturity;
- logical Multisite mapping is **56/56**;
- module-wide AI Prompt mapping is **56/56**;
- accepted planning/evidence decisions extend through **ADR-0208**;
- exact Market Expansion evidence RDR/SRT/DMY/LNK/DBM/PDO/MIR is **1,232/1,232 documented / 0 executed**;
- WP114–WP116 still contain **4,576 exact supplemental fixture definitions** before the next closure audit;
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

### Latest planning-session observation

| Capability | Observed state | Evidence/limitation |
|---|---|---|
| GitHub repository read | AVAILABLE | repository/docs/PR readable |
| GitHub planning-document writes | AVAILABLE | planning branch documentation updated through ADR-0208 |
| Pull-request metadata | AVAILABLE | Draft PR #1 accessible |
| Project planner | AVAILABLE | Linear mirrors planning state; GitHub remains canonical |
| Branch protection/rulesets | UNKNOWN unless freshly verified | do not infer configured/not configured from absence of evidence |
| Local filesystem/working tree | UNKNOWN | not established by connector planning work |
| Terminal/shell | UNKNOWN | not established by connector planning work |
| WordPress runtime | NOT EXECUTED / authorization blocked | no runtime execution allowed pre-consent |
| Database | NOT EXECUTED / authorization blocked | no DB mutation/test execution allowed pre-consent |
| Runtime CI/build result | NOT EXECUTED / UNKNOWN | planning evidence is not runtime evidence |
| Deployment | NOT EXECUTED | production development not started |

This table is a current planning baseline, not a permanent guarantee. Future sessions must refresh capability state when materially relevant.

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
| 56 module/platform surfaces | NOT_STARTED | DOCUMENTED | exhaustive product planning, 0 authorized |
| Shared platform architecture | NOT_STARTED | DOCUMENTED | accepted paper contracts/ADRs only |
| Exact universal/adapter evidence | NOT_STARTED | DOCUMENTED | SBP/ANL/SRH/DEC/LED/RSV/PLC/EXP/DOC/SYN/GEO/AIP/WCA exact; 0 executed |
| Exact Market Expansion evidence | NOT_STARTED | DOCUMENTED | RDR/SRT/DMY/LNK/DBM/PDO/MIR exact under ADR-0208; 0 executed |
| Remaining supplemental exact evidence | NOT_STARTED | PARTIALLY_DOCUMENTED | WP114–WP116: 4,576 individual definitions remain |
| Production PHP/React implementation | NOT_STARTED | N/A | implementation intentionally absent |
| Database/runtime migrations | NOT_STARTED | N/A | no production schema execution |
| CI/build runtime | NOT_STARTED | PARTIALLY_DOCUMENTED | policy/evidence direction exists; execution pending |
| Production deployment | NOT_STARTED | N/A | no production release |

Future implementation must update this ledger or an equivalent maintained implementation-readiness source. Do not create a second conflicting truth source.

## 7. Gap classification

Every discovered plan/repository gap must be classified:

- `CORRECTION` — existing plan/implementation is wrong or unsafe.
- `COMPLETION` — intended scope is incomplete.
- `HARDENING` — improves security/reliability/recovery/operability without changing intended product scope.
- `OPTIMIZATION` — improves performance/maintainability/throughput without changing intended behavior.
- `NEW_PRODUCT_SCOPE` — materially new user-facing capability/business behavior.

Readiness also distinguishes:
- `PLANNING GAP`;
- `RUNTIME EVIDENCE PENDING`;
- `PROVIDER CERTIFICATION PENDING`;
- `OWNER CONSENT PENDING`;
- `NO GAP / READY AS PLAN`.

A `0/N` exact evidence counter is not automatically a planning gap.

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

## 9. Baseline failure registry rule

A failure observed before the current change is labeled `BASELINE FAILURE` with check/test name, revision, first observed date, exact summary, blocking classification, issue and owner if known.

Do not attribute a pre-existing failure to new work and do not modify unrelated code merely to make all checks green.

## 10. VCS/protection audit

Before implementation/release, inspect where available protected branches/rulesets, reviews, required checks, CODEOWNERS, merge queue/train, release protection, deployment approvals and security scans. If access cannot verify an item, record `UNKNOWN`. Never weaken protection merely to let AI merge faster.

## 11. Current planning resume point

WP112 closure/readiness audit: **DONE / ADR-0207**.  
WP113 Market Expansion exact evidence: **DONE / ADR-0208**.  
Current: **WP114 — MPR/RPR/ATM/MDP/STM exact executable-evidence specification — 880 definitions**.

After WP116, a new closure audit must decide whether P0 may transition to `AWAITING_DEVELOPMENT_APPROVAL`.

Development remains **NOT GRANTED / 0/56**.

## 12. Authority order

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