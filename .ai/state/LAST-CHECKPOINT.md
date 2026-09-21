# AI Durable Last Checkpoint

Policy: `GOV-AI-NATIVE-TIMEOUT-RESILIENCE-001`

## Exact observed base

- Repository: `Vertex-Systems-Network/wpessential`
- Observed main: `aff745c642246ffd28e12797b29e94112ea0baca`
- Active Issue: #1115
- Active branch: `supervisor/timeout-resilient-ai-state-v1`
- Active PR: #1116

## Completed immediately before this milestone

- Security Issue #1108 / PR #1111 merged.
- The accepted Node development dependency graph is integrated through merge `aff745c642246ffd28e12797b29e94112ea0baca`.
- Runner Benchmark policy #1109/#1110 and terminal P-006 workflow enforcement #1112/#1113/#1114 are already on main.

## Active milestone

Implement compact durable AI state, strict one-milestone execution, no tight CI polling, state-drift guards, queue/Runner Benchmark reconciliation, and a portable cross-project AI prompt.

## Mandatory recovery behavior

On `start`, `continue`, `resume`, tool failure, chat interruption, or message-delivery timeout:

1. read `.ai/state/CURRENT-STATE.yaml`;
2. read this file;
3. resolve exact current main and reconcile OPEN Issues then OPEN PRs/MRs;
4. re-read queue and Runner Benchmark from current main;
5. inspect only the historical `CHECKPOINT.md` sections needed to resolve a conflict or historical evidence question;
6. continue only the next safe logical milestone.

Never repeat an operation solely because the previous chat response was not delivered. Verify repository evidence first.

## Next safe action

PR #1116 contains the #1115 implementation. Compact state is pre-persisted as `WAITING_EXTERNAL` so exact-head CI can be observed without a follow-up state-only commit invalidating the tested head. On the next user `continue`, resolve the PR's current head and perform one consolidated CI/review refresh; merge only if the exact head is green/current and review threads are zero.
