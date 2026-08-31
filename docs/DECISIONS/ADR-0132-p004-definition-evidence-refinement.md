# ADR-0132 — P-004 Definition Repository Evidence Refinement

Status: **Accepted evidence refinement / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP15`

## Context

ADR-0049 accepts Definition identity + immutable Revisions + revision-aware Dependencies. ADR-0069/0071 establish explicit Multisite scope and PT-C control-plane topology. ADR-0073 selects D1/PT-C as the first future physical benchmark baseline, while ADR-0092 accepts `docs/QUALITY/DEFINITION-P004-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the canonical P-004 execution contract.

The WP15 audit found that the canonical protocol already covered core D1–D4 physical comparison, Q1–Q10 workloads, C1–C7 concurrency, migration, Backup/Restore and scope attacks, so creating a second P-004 protocol would be harmful duplication. However, the existing protocol was materially incomplete against later accepted architecture/governance around package replay/conflicts, schema migrator chains, Revision retention/pruning, purge safety, compiled-cache invalidation, module disable/Pro expiry, clone/transfer/Site Lifecycle reconciliation, post-commit event ordering and current negative-requirement governance.

## Decision

Refine the **existing canonical** `docs/QUALITY/DEFINITION-P004-EXECUTABLE-EVIDENCE-PROTOCOL.md` in place rather than creating a duplicate protocol.

The refined contract defines **DEF-01…DEF-144** and preserves traceability to ADR-0092's original named workloads:

- Q1→DEF-65
- Q2→DEF-66
- Q3→DEF-67
- Q4→DEF-68
- Q5→DEF-69
- Q6→DEF-70
- Q7→DEF-71
- Q8→DEF-72
- Q9→DEF-73
- Q10→DEF-74
- C1→DEF-77
- C2→DEF-78
- C3→DEF-79
- C4→DEF-80
- C5→DEF-81
- C6→DEF-82
- C7→DEF-83

`docs/QUALITY/P004-DEFINITION-REPOSITORY-BENCHMARK-PROTOCOL.md` remains supplementary benchmark guidance. It is not a second certification authority.

## Refined evidence coverage

DEF-01…DEF-144 now explicitly cover:

- Definition identity, site/network scope and lifecycle;
- immutable Revisions and current/published pointer integrity;
- stale editor/save/publish conflicts;
- revision-aware Dependency extraction, Used-by and publish validation;
- definition-type registry, schema versions and deterministic migrator chains;
- unknown future schema and missing-migrator degraded/read-only behavior;
- portable package UUID identity, same-UUID replay, key collisions, clone/remap and deferred module objects;
- compiled runtime cache identity/invalidation;
- module disable and Pro-expiry configuration preservation;
- Revision retention, pruning, purge/dependency/recovery safety;
- D1/D2/D3/D4 physical-profile integrity;
- the original Q1–Q10 lookup/index/Backup/lifecycle workloads;
- the original C1–C7 races plus deadlock/retry/post-commit event ordering;
- schema migration, backfill, interruption/re-entry and old-code/new-schema safety;
- Backup/Restore, Site clone/transfer/uninitialize/delete and post-restore reconciliation;
- authorization, Multisite and normalization/corruption attack corpus;
- DF-S/DF-M/DF-L/DF-N scale, query-plan/index/storage evidence;
- failure/recovery and independent data-integrity/security review.

## Preserved invariants

1. Definition identity ≠ immutable Revision ≠ Dependency edge ≠ compiled cache artifact.
2. Draft/current and published Revision may differ without changing live runtime semantics.
3. Historical Revisions are immutable; load/migration does not silently rewrite them.
4. current/published pointers must reference Revisions owned by the same Definition.
5. portable identity is UUID/logical reference, never local numeric DB ID.
6. scope remains explicit security/product truth even under shared PT-C storage.
7. site-owned/network-owned Definitions remain distinct and server-authorized.
8. module/type payload validation and migration belongs to registered contracts, not Repository guesswork.
9. unknown future schema fails safe/read-only instead of lossy downgrade.
10. compiled/runtime cache is derivative and cannot override Repository source truth.
11. module disable or Pro entitlement loss preserves user configuration and safe readable/exportable state.
12. import key collision does not establish logical identity without explicit mapping.
13. archive/tombstone is not purge; purge is a separate destructive operation.
14. Backup/restore/clone/transfer must preserve or explicitly remap scope/identity.
15. event/cache invalidation success follows durable commit, never precedes it.
16. performance/storage savings cannot waive correctness, scope isolation, migration safety or recoverability.

## Physical-profile truth

**D1 / PT-C remains the first benchmark baseline only.**

Alternatives remain evidence candidates:
- D2 — compact/binary UUID representation;
- D3 — native JSON payload;
- D4 — stronger FK/check-constraint profile where compatible.

This ADR does **not** select final D1/D2/D3/D4, exact DDL, SQL types, identifier lengths, collations, index order, payload hash representation or DB constraint policy.

## Evidence state

- DEF fixtures documented: **144**
- DEF fixtures executed: **0/144**
- P-004 physical/runtime certifications: **0**
- selected final physical profile: **OPEN / evidence-gated**
- exact DDL/index/types/collations: **OPEN / evidence-gated**
- independent P-004 data-integrity/security review executed: **NO**

## Stop-the-line examples

P-004 cannot certify if wrong-site/network data is read or mutated; a committed Revision is mutated; a pointer references another Definition; stale writes silently overwrite committed state; key uniqueness is ambiguous; unknown schema is destructively downgraded; import key collision is treated as identity; module disable/Pro expiry deletes configuration; pruning/purge removes protected history/dependencies; Backup/restore/clone contaminates scope; interrupted migration leaves unverifiable schema state; stale compiled cache survives incompatible publish/schema/restore change; or event/cache success is emitted before the underlying commit.

## Development gate

This ADR authorizes no Definition table, DDL, SQL, EXPLAIN, fixture DB, migration, backfill, cache operation, package import, Backup/restore, Site Lifecycle mutation, lock/concurrency test, benchmark or runtime Repository implementation.

ADR-0014 explicit scoped owner consent remains required before every executable P-004 action.