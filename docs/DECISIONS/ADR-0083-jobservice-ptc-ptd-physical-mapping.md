# ADR-0083 — JobService PT-C/PT-D Physical Mapping Baseline

Status: **Accepted paper benchmark profile / P-003 runtime evidence pending**  
Date: 2026-08-28

## Context

JobService semantics, fairness/backpressure and Action Scheduler packaging are already accepted, but the WPE-owned durable Job/Attempt history still needs a physical mapping independent from backend action tables. ADR-0071 left this as PT-C/PT-D evidence-gated.

## Decision

Future physical comparisons are:
- **J1 — PT-D shared scoped WPE Jobs + Attempts**, first benchmark baseline;
- **J2 — PT-C current/control Job rows + PT-D Attempts/history**, mandatory comparison;
- **J3 — PT-C Jobs + Attempts**, low-volume control comparison.

Schedule/configuration remains in its owning control-plane model. Backend action rows remain adapter implementation details.

This ADR accepts benchmark order and logical mapping only; it does not select Action Scheduler, final DDL, claim primitive or fairness algorithm.

## Invariants

- every site-owned Job/Attempt has explicit network/site scope;
- backend action/status/retention cannot fabricate or erase WPE logical outcome/history;
- lease expiry never proves no external side effect occurred;
- retried/recovered work re-enters Job Type idempotency/reconciliation contract;
- concurrent/retried unique logical enqueue cannot create multiple effective WPE Jobs;
- Action Scheduler `unique` is never the business idempotency guarantee;
- fairness/backpressure must remain observable/enforceable without full-table scans or one noisy site starving others;
- Restore does not blindly restore backend queue rows as executable work;
- stale backend references are reconciled/remapped rather than trusted.

## Selection gate

A profile is rejected regardless of speed if backend state can fabricate WPE success, stale claims cause duplicate effective mutation, scope isolation fails, fairness can be starved indefinitely under supported workload, or Restore blindly reactivates unsafe work.

## Evidence still required

After explicit owner consent P-003 must compare J1/J2/J3 and the chosen backend adapter using:
- 100k/1M/10M history where practical;
- high-volume workflow/notification/import workloads;
- duplicate enqueue/claim/worker crashes;
- lease expiry and unknown side effects;
- WPE commit/backend enqueue failures in both directions;
- backend cleanup/deletion;
- cancellation/pause/fairness/backpressure;
- recurring schedules;
- site lifecycle/restore/wrong-site context;
- Action Scheduler coexistence/version/load-order/schema migration evidence;
- 100/1k/10k-site network behavior.

Executed P-003 Job physical/backend benchmarks: **0**.

## Development gate

This ADR authorizes no Job table/migration, Action Scheduler bootstrap/action, WP-Cron event, runner, claim, queue execution, fixture or benchmark. ADR-0014 explicit owner consent remains required.