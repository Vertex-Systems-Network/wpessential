# ADR-0093 — Relations P-010 Evidence Protocol

Status: **Accepted paper evidence protocol / execution pending**  
Date: 2026-08-28

## Context

ADR-0074 selected R1/PT-D as the first Relations physical baseline with R2/PT-E mandatory and R3 exceptional. The remaining planning gap was an exact future evidence contract covering graph shape, forward/reverse query plans, cardinality races, high-degree endpoints, Multisite operations, Backup/Restore and scope attacks.

## Decision

Accept `docs/QUALITY/RELATIONS-P010-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the required future P-010 execution contract.

The protocol fixes:
- deterministic RF-S/RF-M/RF-L/RF-N/RF-H graph classes;
- RQ1–RQ11 forward/reverse/pair/count/order/pivot/nested/Backup/network workloads;
- RC1–RC8 duplicate/cardinality/reassignment/detach/reorder/lifecycle races;
- endpoint E1/E2/E3 and pivot PV1/PV2/PV3 isolated subtests;
- query-plan/N+1/cache/lifecycle/large-network measurements;
- R1 and R2 topology-specific wrong-site attack fixtures;
- acceptance ordering with security/cardinality first.

## Non-negotiable rejection gates

A candidate fails regardless of speed if it permits:
- wrong-site or unauthorized edge visibility/mutation;
- duplicate/cardinality violations under declared concurrency contract;
- cross-site mode becoming enabled by missing scope;
- normal relation traversal degenerating into unbounded N+1;
- Site Backup/Restore producing a different logical graph across R1/R2;
- one-site lifecycle operation damaging another site's relation data.

## Evidence output requirement

Future P-010 completion must publish exact selected topology, endpoint/pivot representation, DDL/indexes, locking/retry strategy, Query Service compilation, cache-generation contract, supported scale profile, lifecycle/Backup procedure and rejected alternatives with reasons.

Executed P-010 cases: **0**.

## Development gate

This ADR authorizes no relation table, fixture graph, SQL, lock test, query execution, Backup/Restore or benchmark. ADR-0014 explicit owner consent remains required.