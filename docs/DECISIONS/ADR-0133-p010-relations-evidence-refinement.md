# ADR-0133 — P-010 Relations Evidence Refinement

Status: **Accepted evidence refinement / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP16`

## Context

ADR-0074 accepts R1/PT-D as the first Relations physical benchmark baseline, with R2/PT-E mandatory and R3 exceptional. ADR-0093 accepts `docs/QUALITY/RELATIONS-P010-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the canonical P-010 execution contract.

The WP16 audit found that the canonical protocol already covered deterministic graph classes, RQ1–RQ11 read workloads, RC1–RC8 concurrency races, R1/R2 topology-specific Multisite attacks, Query/N+1 behavior, cache, Backup/Restore and large-network operation. Creating a second P-010 protocol would therefore be harmful duplication.

The audit also found material gaps against later accepted product semantics: Relation Definition revision changes with existing links, reciprocal/self-direction + pivot compatibility, custom min/max link rules, bulk replace/idempotency, endpoint-provider outage/recovery, permission/count/existence leakage, pivot schema migration, import replay, clone/transfer, authorization-generation cache revocation, transaction/post-commit event ordering and broader failure/reconciliation cases.

## Decision

Refine the **existing canonical** `docs/QUALITY/RELATIONS-P010-EXECUTABLE-EVIDENCE-PROTOCOL.md` in place.

The refined contract defines **REL-01…REL-160** while preserving ADR-0093 workload traceability.

Original read workload mapping:
- RQ1→REL-65
- RQ2→REL-66
- RQ3→REL-67
- RQ4→REL-68
- RQ5→REL-69
- RQ6→REL-70
- RQ7→REL-71
- RQ8→REL-72
- RQ9→REL-73
- RQ10→REL-74
- RQ11→REL-75

Original concurrency mapping:
- RC1→REL-81
- RC2→REL-82
- RC3→REL-83
- RC4→REL-84
- RC5→REL-85
- RC6→REL-86
- RC7→REL-87
- RC8→REL-88

`docs/QUALITY/P010-RELATIONS-PHYSICAL-BENCHMARK-PROTOCOL.md` remains supplementary benchmark guidance, not a second certification authority.

## Refined evidence coverage

REL-01…REL-160 now explicitly cover:

- Relation Definition identity/revision/publish/disable/archive behavior;
- typed Data Source endpoint identities and provider degradation/recovery;
- directed, reciprocal/symmetric and self-relation semantics;
- one-to-one/one-to-many/many-to-one/many-to-many and custom min/max limits;
- single/bulk attach/detach, replace-set and external idempotency;
- typed/versioned pivot fields and pivot schema evolution;
- independent From/To ordering and concurrent reorder integrity;
- relation/endpoint/pivot authorization plus count/existence leakage;
- original RQ1–RQ11 read workloads;
- Query QP3 batching, stable pagination and zero-tolerance normal-list N+1;
- original RC1–RC8 concurrency races;
- deadlock/retry, process interruption and audit/event/cache post-commit ordering;
- endpoint deletion/restrict/orphan/repair/purge safety;
- Definition cardinality/direction/pivot/source/permission revision impact on existing edges;
- cache generation and authorization-revoke invalidation;
- import/export/replay/clone/restore identity remapping;
- Multisite/network/cross-site/Site Lifecycle/transfer behavior;
- R1/R2/R3/E1–E3/PV1–PV3 physical/migration evidence;
- RF-S/RF-M/RF-L/RF-H/RF-N scale and high-degree workloads;
- security/failure/recovery/final certification audit.

## Preserved invariants

1. Relation Definition/Revision ≠ runtime edge/link ≠ pivot metadata ≠ cache/history.
2. Cardinality and duplicate prevention are enforced below UI under concurrency.
3. Direction and reciprocal/symmetric semantics are explicit.
4. Endpoint identity is typed by Data Source + entity + scope; raw numeric ID alone is not universal identity.
5. Site/network/cross-site scope remains explicit security truth independent of physical topology.
6. Cross-site relations remain Off by default.
7. Attach/detach/pivot/reorder permissions remain separate from endpoint edit permission.
8. list/count/existence cannot leak inaccessible endpoints.
9. Pivot fields are typed/versioned and are only advertised queryable when physical evidence proves it.
10. Relation Definition changes incompatible with existing edges require migration/conflict resolution before Publish.
11. Generic relation delete never cascades arbitrary third-party entities.
12. Missing endpoint provider degrades without automatic link purge.
13. Cache is derivative and cannot preserve stale protected visibility after revoke/detach/definition change.
14. Normal Query/List traversal cannot use unbounded N+1.
15. Import/restore remaps portable source identities and never guesses numeric IDs.
16. Audit/events/cache invalidation follow durable transaction commit.
17. Equivalent certified R1/R2 profiles must export/restore the same logical graph.
18. Performance/storage never waive integrity, scope, authorization or recovery.

## Physical-profile truth

**R1 / PT-D remains the first benchmark baseline only.**

Mandatory/optional comparisons remain:
- R2 / PT-E — mandatory comparison;
- R3 — exceptional per-relation profile only for evidence-backed bounded use case;
- R4 — native/meta interoperability baseline only;
- E1/E2/E3 endpoint representation — open;
- PV1/PV2/PV3 pivot representation — open.

This ADR does not select final R profile, endpoint encoding, pivot encoding, DDL, types, indexes, ordering token scheme or numeric performance limits.

## Evidence state

- REL fixtures documented: **160**
- REL fixtures executed: **0/160**
- P-010 physical/runtime certifications: **0**
- selected final R1/R2/R3 profile: **OPEN / evidence-gated**
- selected E1/E2/E3 endpoint representation: **OPEN / evidence-gated**
- selected PV1/PV2/PV3 pivot representation: **OPEN / evidence-gated**
- exact DDL/types/indexes: **OPEN / evidence-gated**
- independent P-010 security/data-integrity review executed: **NO**

## Stop-the-line examples

P-010 cannot certify if wrong-site/network/unauthorized edges or counts leak; duplicate/cardinality violations occur under concurrency; symmetric/directional semantics corrupt; stale Definition generation writes incompatible links; cardinality/source/pivot changes publish over incompatible existing links without migration; generic cascade deletes third-party entities; provider outage purges links; detached edges resurrect; normal traversal becomes unbounded N+1; protected cache survives revoke/detach/site deletion; import/clone/restore guesses identity or violates cardinality; equivalent R1/R2 restore yields a different logical graph without an explicit unsupported-semantics declaration; or success events/cache invalidation occur before durable commit.

## Development gate

This ADR authorizes no Relation table, DDL, SQL, fixture graph, migration, lock/concurrency test, Query execution, cache operation, Import/Export mutation, Backup/Restore, Site Lifecycle mutation or benchmark.

ADR-0014 explicit scoped owner consent remains required before every executable P-010 action.