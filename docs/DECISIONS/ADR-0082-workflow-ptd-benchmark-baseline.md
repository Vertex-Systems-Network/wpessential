# ADR-0082 — Workflow PT-D Benchmark Baseline

Status: **Accepted paper benchmark profile / P-011 evidence pending**  
Date: 2026-08-28

## Context

Workflow Definitions and durable execution runtime are already separated, and JobService is an execution backend abstraction rather than Workflow source of truth. ADR-0071 identifies Workflow runtime as a PT-D candidate. A concrete future P-011 benchmark profile is needed for Runs, Step Attempts, Waits, Approvals and reconciliation without creating runtime tables yet.

## Decision

Future first benchmark profile is:
- **WF1 — PT-D shared scoped Workflow Runtime**.

Mandatory comparison:
- **WF2 — PT-E per-site Workflow Runtime**.

WF1 includes logical stores for Workflow Runs, Node/Step Attempts, Wait/Subscriptions, Approvals and bounded branch/join or context references where required.

This ADR accepts benchmark order and invariants only, not final DDL, Job backend, outbox implementation or locking primitive.

## Invariants

- every site-owned runtime row has explicit network/site scope;
- every Run pins the immutable published Workflow revision;
- JobService/backend state cannot replace Workflow durable truth;
- enqueue failure after committed Workflow state is recoverable by reconciliation;
- duplicate Job delivery cannot duplicate the logical node transition/side effect when node contract is valid;
- waits are durable subscriptions, not sleeping PHP jobs;
- duplicate events cannot resume a single-consume wait twice;
- concurrent approvals/parallel joins cannot commit twice;
- unknown external outcome is distinct from definite failure and may require reconciliation rather than blind retry;
- cancellation/compensation remain explicit durable states;
- Restore cannot blindly re-execute terminal Runs or reinterpret a Run against an unrelated latest definition revision.

## Selection gate

A profile is rejected regardless of latency if it permits duplicate side effects, wrong-site resume/mutation, double approval/join, cancellation corruption or Restore replay.

## Evidence still required

After explicit owner consent P-011 must cover:
- 100k/1M Run history where practical;
- 10k/100k waits;
- approvals and bounded parallel fan-out;
- trigger and Job duplicate delivery;
- worker crashes around side effects/state commit;
- enqueue failure/reconciliation;
- wait registration/event races;
- cancellation/compensation;
- site lifecycle/restore/wrong-site attacks;
- WF1 vs WF2 noisy-neighbor and 100/1k/10k-site topology evidence;
- exact DDL/index/locking/retention/query plans.

Executed P-011 Workflow benchmarks: **0**.

## Development gate

This ADR authorizes no Workflow table/migration, trigger/event runtime, Job execution, approval action, provider call, fixture or benchmark. ADR-0014 explicit owner consent remains required.