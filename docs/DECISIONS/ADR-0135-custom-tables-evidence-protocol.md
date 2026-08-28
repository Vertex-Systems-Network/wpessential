# ADR-0135 — Custom Tables Physical / DDL / Migration Executable Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP18`

## Context

ADR-0023 accepts a typed desired-schema + generated Migration Plan architecture rather than arbitrary destructive SQL or universal `dbDelta()`. ADR-0088 accepts CT1/PT-E as the first site-owned physical baseline, CT2/PT-D as a mandatory shared-physical comparison, and CT3 only for genuinely network-owned data.

The paper architecture did not yet provide one fixed adversarial execution contract spanning physical identity, type/index/constraint mapping, Data Source/Query behavior, schema introspection, deterministic planning, migration crash windows, destructive recovery, Field Storage/Relations dependencies, safe query-console/import/privacy behavior, Multisite lifecycle and large-network scale.

## Decision

Accept `docs/QUALITY/CUSTOM-TABLES-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the fixed future Custom Tables executable evidence contract.

It defines **CTB-01…CTB-184** covering:

- Table Definition vs observed schema vs Migration Plan vs Migration Run vs applied fingerprint separation;
- CT1/PT-E, CT2/PT-D and CT3 network-owned scope/physical profiles;
- safe physical identifiers and no ordinary raw DDL/SQL configuration;
- logical→physical data types, null/default/charset/collation behavior;
- primary keys, indexes, uniqueness and optional constraint compatibility;
- Data Source CRUD, row/field Policy and concurrency;
- Query P-009/QP2 integration, pagination/count/cache/cost/index evidence;
- deterministic introspection/fingerprint/drift/adoption behavior;
- CM1 direct compatible alteration;
- CM2 resumable backfill + later constraint/index;
- CM3 shadow/copy/verify/swap crash windows;
- CM4 destructive/recovery-only operations;
- Field Storage, Relations, Query, REST/Admin Column dependency impact;
- import/export, safe data browser/query console and privacy operations;
- Backup/Restore, clone/transfer/delete and CT1/CT2/CT3 Multisite behavior;
- 10k/100k/1M rows and 100/1k/10k-site operational evidence;
- independent DB/security/data-integrity review.

## Preserved invariants

1. Definition publication and physical migration completion are separate facts.
2. Raw user-entered DDL is not the normal migration model.
3. `dbDelta()` is an implementation/compiler tool only for the subset its evidence proves; it is not WPE's source-of-truth migration language.
4. A reviewed source schema fingerprint must be revalidated before mutation.
5. Silent truncation/lossy coercion is forbidden.
6. CT1 is first baseline for ordinary site-owned tables; CT2 is mandatory comparison, not automatic promotion; CT3 is genuinely network-owned only.
7. CT2 site-owned row access/mutation requires trusted scope; row ID alone is insufficient where ownership matters.
8. A site lifecycle operation cannot drop/corrupt shared or network-owned data belonging elsewhere.
9. Hard uniqueness requires a proven concurrent guarantee.
10. Migration plans classify risk and recovery truth; WPE does not promise universal transactional DDL rollback or zero downtime.
11. R3/R4/destructive operations require the configured verified recovery boundary before destructive commit.
12. Definition deletion never automatically drops physical data.
13. Manual/third-party schema drift is surfaced/classified, not blindly overwritten.
14. Portable packages contain logical schema, not source-environment generated SQL/table prefixes.
15. Query/Field Storage/Relations/REST/Admin Columns consume registered logical schema and must be impact-checked across schema evolution.
16. Performance cannot override scope, authorization, data integrity or recovery correctness.

## Evidence state

- CTB fixtures documented: **184**
- CTB fixtures executed: **0/184**
- Custom Tables runtime/DDL/migration certifications: **0**
- CT1 certified profiles: **0**
- CT2 certified profiles: **0**
- CT3 certified profiles: **0**
- CM1 certified operation profiles: **0**
- CM2 certified operation profiles: **0**
- CM3 certified operation profiles: **0**
- CM4 recovery profiles: **0**
- exact DDL/types/indexes/constraints: **OPEN / evidence-gated**
- independent DB/security/data-integrity review executed: **NO**

ADR-0023 and ADR-0088 remain the accepted architecture/physical baselines. This ADR accepts the evidence contract only and does not certify a DB family, physical schema, migration compiler or topology.

## Stop-the-line examples

Custom Tables cannot certify if unchecked identifiers alter SQL/DDL structure; CT2 allows wrong-site row access/mutation; site authority mutates CT3; migration runs against stale fingerprint; lossy/truncating conversion occurs silently; partial migration is labeled applied; `dbDelta()` behavior is overstated; destructive operation proceeds without required verified recovery point; site lifecycle damages another scope; Definition publish is labeled migration success; unknown drift is overwritten automatically; schema evolution silently breaks active dependencies; secret values are logged; or online/rollback guarantees are claimed without measured proof.

## Development gate

This ADR authorizes no table, DDL, SQL, schema introspection, migration compiler, migration/backfill, row mutation, shadow/swap, import/export execution, privacy mutation, Backup/Restore, lifecycle mutation, benchmark or runtime test.

ADR-0014 explicit scoped owner consent remains required before every executable Custom Tables action.