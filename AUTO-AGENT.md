# WPEssential — Automatic Multi-Agent Start Protocol

This file is the entrypoint for autonomous multi-agent work on WPEssential.

It does **not** replace `AGENTS.md`, `CONTRIBUTING.md`, `CHECKPOINT.md`, `docs/ENGINEERING-EXECUTION-GOVERNANCE.md`, approval/consent rules, ownership contracts, dependency contracts or quality gates. Those remain authoritative.

## Goal

A newly started agent should not need the repository owner to manually choose `Taxonomy`, `Query`, `Listings`, etc.

After startup, the agent must:

1. inspect current repository truth;
2. determine its role;
3. read `config/coordination/agent-work-queue.json`;
4. select the highest-priority valid free work slot;
5. claim it without racing another agent;
6. work only inside that slot's allowed scope;
7. submit a PR/MR with exact-head evidence;
8. let the Supervisor/Integrator decide merge order from dependencies and current main.

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
Reconcile current main, open PRs/MRs, active claim branches and config/coordination/agent-work-queue.json before changing files.
Take the highest-priority valid SUPERVISOR_ONLY slot first; if none exists, take the highest-priority valid ANY slot.
Coordinate submitted workers, shared writes and merge order while working on your own claimed slot.
```

The Supervisor must not pre-create worker branches. Workers claim their own slots.

## Start command — every additional agent

Use exactly this intent:

```text
Start WPEssential Worker in AUTO mode.
Read AUTO-AGENT.md and follow it completely.
Autonomously inspect current main and config/coordination/agent-work-queue.json, then claim the highest-priority valid free ANY slot using its deterministic remote claim branch.
Do not ask me which module to work on unless repository evidence contains a genuine unresolved decision.
```

That is enough. The Worker chooses its own safe assignment.

## Worker auto-selection algorithm

Every Worker executes this sequence:

### A. Refresh repository truth

Before branch creation:

- read root `AGENTS.md`;
- read `CONTRIBUTING.md`;
- read current `CHECKPOINT.md`;
- read `docs/PROJECT-STATE-AND-ADOPTION.md`;
- read `docs/ENGINEERING-EXECUTION-GOVERNANCE.md`;
- read the relevant approval/consent and canonical ownership/dependency files;
- read `config/coordination/agent-work-queue.json`;
- resolve the exact current `main` SHA;
- inspect relevant open/merged PRs/MRs and current remote branches;
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

The Supervisor performs the same repository preflight, then:

1. takes the highest-priority valid `SUPERVISOR_ONLY` slot, if any;
2. otherwise may claim the highest-priority valid `ANY` slot;
3. does not create branches for Workers;
4. periodically re-reads current PR/MR/main state when the user invokes or continues the Supervisor session;
5. reviews submitted work against current main and dependency order;
6. applies serialized shared-file Integration Requirements;
7. merges only merge-ready exact heads;
8. reconciles queue/shared progress after accepted merges.

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

The Supervisor must review evidence rather than trusting the phrase itself.

## After merge

After a Worker PR/MR is merged:

- authoritative current main changes;
- the Supervisor reconciles durable progress/queue truth when needed;
- other active agents must incorporate relevant new main changes before final certification;
- old exact-head CI does not certify a new synchronized head;
- no branch may be force-rewritten to hide integration conflicts.

## Current queue intent

The initial queue deliberately separates runtime dependency work from parallel-safe planning reconciliation:

- serialized shared-state reconciliation for the Supervisor;
- Relations Gate B as the current runtime blocking foundation;
- Taxonomy Bank reconciliation as parallel planning/integration work;
- Query Bank/planning reconciliation without Query runtime;
- Listings Bank/planning reconciliation without Listings runtime.

The queue is not a permanent roadmap. Current repository evidence always wins, and the Supervisor must update/rebaseline the queue as accepted work changes what is actually safe next.

## Practical launch pattern

Open separate agent sessions/workspaces.

Launch them like this:

```text
Agent 1: Start WPEssential Supervisor in AUTO mode. Read AUTO-AGENT.md and follow it completely.
Agent 2: Start WPEssential Worker in AUTO mode. Read AUTO-AGENT.md and follow it completely.
Agent 3: Start WPEssential Worker in AUTO mode. Read AUTO-AGENT.md and follow it completely.
Agent 4: Start WPEssential Worker in AUTO mode. Read AUTO-AGENT.md and follow it completely.
```

Expected behavior at the current queue snapshot, assuming nobody has claimed anything yet:

```text
Supervisor → shared-state reconciliation
Worker 1   → Relations Gate B continuation
Worker 2   → Taxonomy Bank reconciliation
Worker 3   → Query planning/Bank reconciliation
Worker 4   → Listings planning/Bank reconciliation
```

This is only an example of the present queue. Each agent must re-check repository truth before accepting the apparent assignment.
