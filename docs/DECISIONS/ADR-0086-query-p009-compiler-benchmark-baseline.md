# ADR-0086 — Query P-009 Compiler Benchmark Baseline

Status: **Accepted paper benchmark profile / executable evidence pending**  
Date: 2026-08-28

## Context

Query AST v1 already defines typed parameters, registered identifiers, provider capability validation, execution-context budgets, caching and authorization boundaries. P-009 still needs a concrete future benchmark matrix that does not collapse WordPress-native, Custom Table, Relations and remote-source queries into one engine.

## Decision

Accept the following future evidence profiles:

- **QP1 — WordPress-native provider compilation** as the first baseline for WordPress-owned post/user/term/comment data;
- **QP2 — WPE Custom Table compiler** as mandatory comparison for WPE-managed typed table workloads;
- **QP3 — Relations-assisted/two-phase compiler** as mandatory relation traversal workload profile;
- **QP4 — Remote Data Source adapter** as separately certified provider behavior, not a local SQL performance comparison.

This ADR selects benchmark/compiler profile boundaries only. It does not approve a compiler implementation, SQL generation library, cache backend, numeric cost thresholds or final query plans.

## Invariants

All profiles preserve:
- no raw SQL node in normal Query AST;
- no arbitrary callback/eval;
- values are typed and bound/prepared where custom SQL exists;
- field/table/sort identifiers come from registered schemas;
- unsupported AST semantics fail validation rather than silently degrade;
- row/resource/field authorization applies at runtime;
- site/network scope is server-resolved;
- public/admin/API/Workflow budgets remain distinct;
- normal relation traversal cannot create N+1 per-result queries;
- privileged caches cannot be reused by lower-privilege principals.

## Cache rule

Persistent Query cache is acceptable only when its key/invalidation model can represent the relevant definition, provider, scope, principal/access, source, relation/policy, pagination and locale dependencies.

If authorization dependencies cannot be represented safely, persistent shared caching is not allowed for that Query.

## Multisite rule

Site scope remains default. Cross-site/network aggregation requires explicit definition, network authorization, provider support, bounded target scope and target-site Policy where site-owned data is involved.

An unbounded synchronous loop over every subsite is not an accepted network query strategy.

## Selection gates

Performance cannot override correctness. A candidate fails P-009 if any fixture shows:
- SQL/identifier/order injection;
- wrong-site data;
- unauthorized projection;
- privileged cache leakage;
- stale authorization allow after the accepted invalidation generation changes;
- silently ignored unsupported semantic node;
- unbounded relation N+1 behavior on normal list execution.

## Evidence still required

After explicit owner consent:
- QP1/QP2/QP3 representative 10k/100k/1M workloads;
- cursor vs offset;
- meta/tax/date/relation/aggregate cases;
- static cost calibration;
- cache invalidation/revocation;
- 100/1k/10k-site aggregation/isolation;
- SQL and scope attack corpus;
- exact query plans/index behavior.

Executed P-009 fixtures: **0**.

## Development gate

This ADR authorizes no compiler, SQL, cache layer, database fixture, benchmark, remote call or executable test. ADR-0014 explicit owner consent remains required.