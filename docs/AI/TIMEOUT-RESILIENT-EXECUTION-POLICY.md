# AI-Native Timeout-Resilient Execution Policy

Status: **ACTIVE / MANDATORY GOVERNANCE**  
Policy ID: `GOV-AI-NATIVE-TIMEOUT-RESILIENCE-001`  
Source: **Issue #1115**

## Purpose

Keep AI engineering deterministic and recoverable when a chat turn is long, a connector call fails, CI is slow, the UI reports a message-delivery timeout, or a session is interrupted.

The repository must remain resumable without relying on chat memory.

## Non-negotiable source-of-truth order

Repository/runtime evidence always outranks AI memory.

For `start`, `continue`, `resume`, recovery, or a previous message-delivery failure, read in this order:

1. `.ai/state/CURRENT-STATE.yaml`;
2. `.ai/state/LAST-CHECKPOINT.md`;
3. exact current `main`, OPEN Issues, and OPEN PRs/MRs;
4. active deterministic claims and `config/coordination/agent-work-queue.json`;
5. `config/coordination/runner-benchmark.json`;
6. relevant implementation/tests/docs;
7. historical `CHECKPOINT.md` sections **only when needed** to resolve history, evidence, or a state conflict.

Compact state is a resume index, not authority to override current repository/runtime evidence.

## One-turn / one-milestone rule

By default:

**one user `continue` / `resume` turn = one logical engineering milestone.**

A logical milestone is a bounded unit such as:

- reconcile and close one accepted PR;
- implement one coherent code/governance change and persist it;
- perform one exact-head verification/merge decision;
- reconcile durable shared state after an accepted merge.

Do not chain audit → broad implementation → repeated CI polling → merge → post-merge audit → unrelated next task in one turn.

Security/recovery work may contain multiple tightly coupled actions only when splitting them would make the repository less safe. The durable state must still be written at the safe boundary.

## Remote-operation and CI status budget

AI MUST:

- batch related read-only remote calls where supported;
- fetch only the files/status needed for the active milestone;
- use one consolidated CI/status refresh per milestone by default;
- register material runner work in the Runner Benchmark before execution/deferral;
- reuse immutable exact-source evidence when its dedup identity still matches.

AI MUST NOT:

- run tight CI/status polling loops;
- repeatedly fetch unchanged workflow state merely to wait for completion;
- rerun a heavy workflow because a chat response timed out;
- rerun historical/formal evidence without current authorization;
- treat deferred or still-running CI as PASS.

If required external CI is still running after the milestone's consolidated refresh:

1. set compact state to `WAITING_EXTERNAL`;
2. record the exact workflow/run/source identity and next safe action;
3. update `LAST-CHECKPOINT.md`;
4. report the pending state;
5. stop that milestone.

The next user `continue` performs one new consolidated refresh.

A second status refresh inside the same milestone is allowed only when a security, merge, incident/recovery, or provider result materially changed and the extra refresh is necessary to make a safe decision. Record the exception in the execution journal.

## Durable-write-before-report rule

Before reporting a meaningful repository-changing milestone as complete, blocked, or waiting, the Supervisor MUST durably reconcile:

- `.ai/state/CURRENT-STATE.yaml`;
- `.ai/state/LAST-CHECKPOINT.md`;
- `.ai/state/EXECUTION-JOURNAL.md` for meaningful transitions;
- queue / Runner Benchmark when their state changed.

If durable shared truth cannot be updated, the milestone is not fully closed. Report the exact blocker instead of claiming completion.

A message-delivery timeout after the durable write does not lose progress. On the next turn, verify repository state before attempting any action again.

## Compact-state limits

To prevent the resume layer from becoming another oversized checkpoint:

- `CURRENT-STATE.yaml` maximum: **12 KiB**;
- `LAST-CHECKPOINT.md` maximum: **16 KiB**;
- `EXECUTION-JOURNAL.md` maximum: **32 KiB**.

The journal is rolling. Archive older detail when the limit approaches.

`CHECKPOINT.md` remains historical engineering evidence. It is no longer a mandatory full-file read on every `continue`; consult the relevant section only when needed.

## State drift rules

The compact state must record at minimum:

- repository;
- policy ID;
- observed main SHA;
- active issue / PR / branch;
- current milestone and status;
- last completed milestone;
- exact next safe action;
- Runner Benchmark pending/blocked IDs;
- open blockers;
- timeout contract.

Every session must verify that the recorded observed main/issue/PR state still matches current repository evidence. When it does not, reconcile it before new development.

A known merged item must not remain represented as pending-merge shared truth after reconciliation.

## Runner Benchmark interaction

This policy extends, not replaces, `GOV-AI-NATIVE-RUNNER-BENCHMARK-001`.

Non-blocking material runner work defaults to the final consolidated runner batch. Immediate security, merge-required exact-head, migration/auth/secrets/data-safety, integration-safety, and incident/recovery checks remain immediate.

Runner Benchmark registration never grants runtime, destructive, provider, production, deployment, release, or formal-evidence authority.

## README closeout scope

The complete 56-surface README dashboard remains mandatory when:

- module lifecycle/progress/timeline/public delivery truth changed; or
- a terminal product milestone/integration closeout is being reported.

A governance/security/coordination-only cycle that does not change module delivery truth MUST reconcile compact AI state and relevant governance records, but MUST NOT rewrite the full 56-row dashboard merely to generate churn.

If README public status becomes stale because the governance change materially changes a public project-status statement, update only the affected public status plus any contract-required dashboard reconciliation.

## Fail-closed rules

AI MUST NOT infer permission from urgency, a timeout, a failed connector, or missing chat context.

AI MUST NOT:

- bypass OPEN accepted Issue/PR work;
- weaken tests/security/branch protection to finish faster;
- force-push shared history;
- execute destructive/provider/production/deploy/release work without current authority;
- invent PASS evidence;
- silently reuse consumed grants;
- repeat an operation just because the previous response was not delivered.

When evidence conflicts, stop the affected action, reconcile repository truth, and persist the conflict in compact state.

## Definition of timeout-resilient completion

A milestone is timeout-resilient only when:

- the bounded work is durably stored in the repository or explicitly recorded as not yet stored;
- exact next action is in compact state;
- runner work is reconciled;
- no unsafe polling loop remains active;
- required immediate checks are either terminal or recorded as `WAITING_EXTERNAL`;
- a new session can continue correctly without reading prior chat history.
