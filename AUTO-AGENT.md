# WPEssential — Automatic Multi-Agent Start Protocol

This file is the entrypoint for autonomous multi-agent work on WPEssential.

It does **not** replace `AGENTS.md`, `CONTRIBUTING.md`, `CHECKPOINT.md`, `docs/ENGINEERING-EXECUTION-GOVERNANCE.md`, approval/consent rules, ownership contracts, dependency contracts or quality gates. Those remain authoritative.

## Goal

A newly started agent should not need the repository owner to manually choose `Taxonomy`, `Query`, `Listings`, etc.

After startup, the agent must:

1. refresh exact current repository truth;
2. inspect and triage **OPEN Issues first**;
3. inspect, repair and merge eligible **OPEN PRs/MRs second**;
4. determine its role;
5. read `config/coordination/agent-work-queue.json` and active deterministic claim branches;
6. select the highest-priority valid free work slot only after existing issue/PR work is reconciled;
7. claim it without racing another agent;
8. work only inside that slot's allowed scope;
9. submit a PR/MR with exact-head evidence;
10. let the Supervisor/Integrator decide merge order from dependencies and current main;
11. at the end of the completed work cycle, reconcile README status including the module-wise progress/timeline dashboard before the engineering query/cycle is reported final.

## Mandatory issue-first / PR-second preflight

Every Supervisor and Worker work cycle, including `continue`/`resume`, starts in this exact order:

1. **Resolve exact current `main`.** Never start from conversational memory or an old branch.
2. **Issues first.** List OPEN repository Issues, read dependency-ready/active issue bodies, and continue or solve accepted unfinished issue work before inventing a new task.
3. **PRs/MRs second.** List OPEN PRs/MRs, inspect mergeability, exact-head CI, review threads, conflicts and dependency order. Fix failing or stale accepted PRs and merge merge-ready PRs before opening new implementation work.
4. **Claims/queue third.** Re-read deterministic claim branches and `config/coordination/agent-work-queue.json` after issue/PR reconciliation because merges may have changed dependencies.
5. **New development last.** Only then claim a dependency-ready free slot or create a new issue authorized by the current exact-main audit.

Rules:

- An OPEN issue that is already represented by an OPEN PR is not duplicate work; finish/review that PR path.
- Do not bypass a failing accepted PR by creating a replacement feature branch unless the existing PR is explicitly superseded/closed with repository evidence.
- Merge order remains dependency-safe and exact-head certified; "PRs second" does not mean blindly merging every PR.
- Critical/security/recovery incidents may stop the line under existing governance, but their issue/PR evidence must still be reconciled durably.

## Mandatory end-of-cycle README reconciliation

After a meaningful work cycle reaches a stable final state (merged work, resolved issue, completed audit, or an explicitly documented blocked state), the Supervisor must update `README.md` before reporting the engineering query/cycle final.

README closeout must include:

- current reconciliation/main anchor where appropriate;
- current active module/dependency gate;
- a **module-wise progress and timeline table**;
- for every implementation module shown: lifecycle/status, progress bar, percentage, start date/time, estimated completion date/time, actual completion date/time, latest evidence and next gate;
- explicit wording that percentages measure the **currently approved/certified bounded implementation milestone**, not full product parity unless `PRODUCT_PARITY_CERTIFIED` is actually promoted;
- no fabricated percentage for planning-only modules without a defined implementation baseline;
- no fabricated historical or future timestamps.

The authoritative timeline rules are in `docs/AI-NATIVE-README-MODULE-TIMELINE-CLOSEOUT.md`.

Canonical progress-bar form:

```text
██████████ 100%
████████░░ 80%
```

Canonical timestamp form is ISO-8601 UTC:

```text
2026-09-10T14:30:00Z
```

Timeline rules:

- **Start date/time** comes from the earliest repository-verifiable accepted implementation signal for the bounded module track.
- **Estimated completion date/time** is a non-binding engineering forecast based on current scope, dependencies, blockers and evidence. It is not a delivery promise, SLA, release date or certification date. Recompute it when those inputs materially change.
- **Actual completion date/time** is populated only from promoted repository evidence such as an accepted merge, issue closure or bounded certification transition.
- Active/incomplete modules use `—` for actual completion.
- If an exact historical timestamp has not been audited, use `UNKNOWN / pending evidence audit`.
- If a defensible completion forecast cannot be produced, use an explicit state such as `FORECAST PENDING / post-audit scope required` rather than inventing a date.

The README update is part of engineering completion, not an optional cosmetic follow-up. If a Worker cannot edit shared truth, it reports the README progress/timeline reconciliation as an Integration Requirement and the Supervisor performs it after merge serialization.

## Important role rule

The **first agent is explicitly started as Supervisor/Integrator**.

Additional agents are started as **AUTO Workers**.

Do not try to infer the Supervisor role from chat memory or an old branch. Independent AI sessions do not provide a reliable lease/heartbeat proving whether another Supervisor session is still alive. Explicitly starting one Supervisor avoids split-brain coordination.

Workers, however, select and claim their work slots automatically.

## Start command — first agent only

Use exactly this intent:

```text
Start WPEssential Supervisor in AUTO mode.
Read AUTO-AGENT.md and follow it completely.
Refresh exact current main; check and resolve OPEN Issues first, then inspect/fix/merge OPEN PRs/MRs, then reconcile active claim branches and config/coordination/agent-work-queue.json before starting new work.
Take the highest-priority valid SUPERVISOR_ONLY slot first; if none exists, take the highest-priority valid ANY slot.
Coordinate submitted workers, shared writes and merge order while working on your own claimed slot.
At the end of the completed work cycle, update README current status and its module-wise progress/timeline table — including progress bar, status, start time, completion forecast and actual completion time — before reporting the engineering query/cycle final.
```

The Supervisor must not pre-create worker branches. Workers claim their own slots.

## Start command — every additional agent

Use exactly this intent:

```text
Start WPEssential Worker in AUTO mode.
Read AUTO-AGENT.md and follow it completely.
Refresh exact current main; inspect OPEN Issues first and OPEN PRs/MRs second. Do not duplicate accepted work already represented there.
Then inspect config/coordination/agent-work-queue.json and claim the highest-priority valid free ANY slot using its deterministic remote claim branch.
Do not ask me which module to work on unless repository evidence contains a genuine unresolved decision.
```

That is enough. The Worker chooses its own safe assignment.

## Worker auto-selection algorithm

Every Worker executes this sequence:

### A. Refresh repository truth

Before branch creation:

- resolve the exact current `main` SHA;
- list/read relevant OPEN Issues first;
- list/read relevant OPEN PRs/MRs second and do not duplicate their accepted work;
- read root `AGENTS.md`;
- read `CONTRIBUTING.md`;
- read current `CHECKPOINT.md`;
- read `docs/PROJECT-STATE-AND-ADOPTION.md`;
- read `docs/ENGINEERING-EXECUTION-GOVERNANCE.md`;
- read the relevant approval/consent and canonical ownership/dependency files;
- read `config/coordination/agent-work-queue.json`;
- inspect active deterministic claim branches;
- inspect commits since the queue's audit anchor.

Never assume the queue snapshot is newer than repository truth.

### B. Revalidate queue entries

Process OPEN `ANY` slots in ascending numeric priority.

For each candidate, verify:

- it is not already completed on current main;
- it has not been superseded by a newer plan/issue/PR;
- required dependencies are satisfied for the kind of work being attempted;
- its planning/runtime boundary is still correct;
- its allowed write scope does not collide with an active writer;
- its deterministic claim branch does not already represent another active claim.

Skip an invalid, obsolete, blocked or occupied candidate and evaluate the next one.

### C. Atomic claim using deterministic remote branch creation

Each slot declares one exact `claim_branch`, for example:

```text
agent/relations-gate-b-closure-v1
```

The agent must attempt to create **that exact remote branch** from the exact current main SHA.

Rules:

- do not create an alternative branch name if it already exists;
- do not force-update the claim branch;
- do not reuse another agent's branch;
- do not create branches for later slots in advance.

GitHub remote ref creation is the claim lock: only one agent can successfully create a previously nonexistent branch with that deterministic name. If creation fails because the ref already exists, another agent has already claimed that slot (or the slot requires Supervisor reconciliation). The Worker immediately tries the next eligible queue entry.

For command-line Git, the equivalent intent is a normal non-force remote branch creation/push. A non-fast-forward/ref-exists rejection is not something to bypass with `--force`; it means the claim was lost.

### D. No valid slot

If every valid slot is claimed, blocked, completed or invalid:

```text
NO_VALID_WORK_SLOT
```

Make no repository changes. Do not invent work merely to keep the agent busy.

## Supervisor algorithm

The Supervisor performs the mandatory issue-first / PR-second preflight, then:

1. resolves/triages OPEN Issues and continues accepted unfinished issue work;
2. reviews/fixes OPEN PRs/MRs and merges only merge-ready exact heads in dependency-safe order;
3. re-reads current main, claim branches and queue after those merges;
4. takes the highest-priority valid `SUPERVISOR_ONLY` slot, if any;
5. otherwise may claim the highest-priority valid `ANY` slot;
6. does not create branches for Workers;
7. periodically re-reads current issue/PR/MR/main state when the user invokes or continues the Supervisor session;
8. reviews submitted work against current main and dependency order;
9. applies serialized shared-file Integration Requirements;
10. merges only merge-ready exact heads;
11. reconciles queue/shared progress after accepted merges;
12. updates README module-wise progress/status/timeline before final query/cycle reporting, including evidence-backed start time, non-binding completion forecast and actual completion time.

A Worker finishing first does not automatically mean it merges first. Merge order follows dependency and shared-truth safety.

## Work execution after claim

Once a slot is successfully claimed:

- record the exact base SHA;
- inspect the slot's source issue/branch/plan and relevant files;
- continue existing accepted work instead of restarting it;
- keep the diff bounded to allowed scope;
- treat shared/global changes as Integration Requirements unless explicitly permitted;
- run applicable FAST/FULL gates;
- if main moves, synchronize non-destructively and re-certify the new exact head;
- open a PR/MR;
- never weaken a valid test to manufacture green evidence.

## Completion signal

An agent may state:

```text
Work Done and Submitted
```

only together with:

- slot ID;
- work mode;
- base SHA;
- branch;
- exact head SHA;
- PR/MR number;
- files changed;
- status/lifecycle changes;
- Integration Requirements;
- exact tests/CI results;
- unresolved items and risks;
- next safe action.

A Worker must additionally identify whether README/shared progress/timeline reconciliation is required. The Supervisor may call a work cycle fully complete only after the README module-wise progress/timeline table is current or the inability to update it is explicitly recorded as a blocker.

The Supervisor must review evidence rather than trusting the phrase itself.

## After merge

After a Worker PR/MR is merged:

- authoritative current main changes;
- the Supervisor reconciles durable progress/queue truth when needed;
- the Supervisor updates README module progress/status/timeline as part of cycle closeout;
- other active agents must incorporate relevant new main changes before final certification;
- old exact-head CI does not certify a new synchronized head;
- no branch may be force-rewritten to hide integration conflicts.

## Current queue intent

The queue is the machine-readable authority for dependency-ready work and is intentionally dynamic. Do not hard-code a stale module assignment into this protocol.

At every invocation:

- Issues and PRs/MRs are reconciled before queue selection;
- the queue is re-read from current main;
- only dependency-ready conflict-safe slots may run in parallel;
- Supervisor/shared-truth lanes remain serialized;
- no module is selected merely from Options Bank ordering.

## Practical launch pattern

Open separate agent sessions/workspaces.

Launch them like this:

```text
Agent 1: Start WPEssential Supervisor in AUTO mode. Read AUTO-AGENT.md and follow it completely.
Agent 2: Start WPEssential Worker in AUTO mode. Read AUTO-AGENT.md and follow it completely.
Agent 3: Start WPEssential Worker in AUTO mode. Read AUTO-AGENT.md and follow it completely.
Agent 4: Start WPEssential Worker in AUTO mode. Read AUTO-AGENT.md and follow it completely.
```

Each session independently re-checks exact current main, OPEN Issues, OPEN PRs/MRs and the coordination queue before accepting an assignment. The repository evidence at that moment—not this example or chat memory—determines the safe work.
