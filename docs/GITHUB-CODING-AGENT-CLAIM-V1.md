# GitHub Coding-Agent Issue Claim V1

This document defines an explicit exception to the deterministic remote-branch claim rule in `AUTO-AGENT.md` for queue slots that opt in to `GITHUB_AGENT_ISSUE_ASSIGNMENT`.

It does not change module ownership, dependency gates, merge gates, approval scope, or the rule that exactly one Supervisor/Integrator serializes shared truth.

## Why this mode exists

GitHub-hosted coding agents such as Copilot cloud agent are started by assigning an issue to the agent. GitHub creates the agent session and its working branch/PR. The branch name is agent-managed, so pre-creating the queue's normal deterministic branch would conflict with the hosted-agent workflow.

This mode is allowed only for slots whose write boundary is already isolated from every active writer.

## Claim lock

For a slot declaring:

```json
{
  "claim_mechanism": "GITHUB_AGENT_ISSUE_ASSIGNMENT",
  "claim_issue": 150,
  "claim_assignee": "copilot-swe-agent[bot]"
}
```

the claim lock is the live GitHub issue assignment, not a pre-created branch.

Rules:

1. Only the Supervisor may initiate the hosted-agent assignment for these slots.
2. The issue body must contain the complete scope, write boundary, non-goals, and merge gate **before assignment**.
3. Once the declared coding-agent assignee is present, manual/AUTO workers must treat the slot as claimed and skip it.
4. The Supervisor must not pre-create a deterministic worker branch for that slot.
5. The hosted agent may use its GitHub-created branch/PR; that branch is valid only for the linked issue/slot.
6. A second coding agent must not be assigned to the same slot concurrently.
7. If assignment fails or the agent is unavailable, the slot remains unclaimed. Do not silently fall back to a different branch/agent; Supervisor reconciliation is required.
8. The agent PR must remain inside the slot's declared write boundary. Any shared/global edits are Integration Requirements, not implicit permission to modify shared truth.
9. Exact-head applicable CI, clean review/thread state, dependency order, and Supervisor merge review remain mandatory.
10. Hosted-agent completion or review request is not merge evidence by itself.

## Interaction with AUTO-AGENT.md

For slots whose current queue entry explicitly declares `GITHUB_AGENT_ISSUE_ASSIGNMENT`, this document and the slot metadata override only the deterministic branch-creation steps in `AUTO-AGENT.md`. All other AUTO-AGENT preflight, ownership, dependency, scope, synchronization, CI, and merge rules remain in force.

For every other slot, the existing `DETERMINISTIC_REMOTE_BRANCH_CREATE` protocol remains unchanged.

## Conflict safety

A hosted-agent slot is eligible only when the queue gives it an exclusive or additive write boundary that does not overlap an active writer. The current Query Gate C generation uses hosted-agent eligibility only for:

- evidence/tests work that cannot edit Query production PHP;
- new non-runtime cache/diagnostics contract files that cannot edit existing Query runtime or shared Platform cache code;
- Query-owned admin UI work that cannot edit PHP runtime or shared project-state truth.

The Query relation runtime slot remains deterministic-branch claimed because it is the exclusive existing Query PHP runtime writer.
