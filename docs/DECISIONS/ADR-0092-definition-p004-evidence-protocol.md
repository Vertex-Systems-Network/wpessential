# ADR-0092 — Definition Repository P-004 Evidence Protocol

Status: **Accepted paper evidence protocol / execution pending**  
Date: 2026-08-28

## Context

ADR-0073 selected D1/PT-C as the first Definition Repository benchmark baseline, with D2/D3/D4 comparisons. The remaining planning gap was an exact future evidence contract for fixture shapes, query plans, concurrency, migration and scope-security acceptance.

## Decision

Accept `docs/QUALITY/DEFINITION-P004-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the required future P-004 execution contract.

The protocol fixes:
- deterministic DF-S/DF-M/DF-L/DF-N dataset classes;
- representative Definition/revision/dependency distributions;
- required Q1–Q10 lookup/list/dependency/Backup/lifecycle workloads;
- C1–C7 publish/save/uniqueness/lifecycle race fixtures;
- query-plan/index/storage measurements;
- migration and interrupted-upgrade cases;
- wrong-site/normalization/security attacks;
- acceptance ordering: correctness/security before performance/storage.

## Non-negotiable rejection gates

A physical profile fails regardless of benchmark speed if it violates:
- immutable revision semantics;
- same-definition current/published pointer integrity;
- scoped machine-key uniqueness;
- stale-write conflict semantics;
- site/network scope isolation;
- scoped Site Backup extraction;
- supported migration recovery/verification.

## Evidence output requirement

Future P-004 completion must produce exact selected DDL/types/lengths/collations/indexes, supported engine matrix, query-plan evidence, locking/retry policy, migration strategy, known limits and reasons for rejecting alternatives.

Executed P-004 cases: **0**.

## Development gate

This ADR authorizes no fixture generator, DB, SQL, EXPLAIN, migration, lock test or benchmark. ADR-0014 explicit owner consent remains required.