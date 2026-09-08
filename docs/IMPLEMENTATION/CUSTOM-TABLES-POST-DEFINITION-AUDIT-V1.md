# Custom Tables Post-Definition Next-Lane Audit V1

Exact main audited: `36b54c7f7d43adf8609a73bbdd54a2040e2a5db0`  
Audit owner: Issue #384  
Entry condition: Issue #382 / PR #383 canonical DDL-free `table` Definition compiler is promoted.

## Verdict

**Custom Tables remains ACTIVE / NOT PASS. The next safe tranche is an execution-free observed-schema + pure diff/Migration Plan contract. Physical DDL execution remains blocked.**

PR #383 correctly establishes authored desired state only. Its compiler/descriptor has no database adapter, migration runner, `dbDelta()` or physical DDL execution dependency. That separation must remain authoritative.

This audit does not authorize physical CREATE/ALTER/DROP/rename execution, row CRUD, Query/Data Source runtime, CT2/PT-D or CT3 runtime, external table adoption, product parity, deployment or release.

## Canonical state separation

Surface 7 must preserve three different truth planes:

1. **Desired schema** — authored/published `table` Definition compiled by `TableDefinitionCompiler` into the immutable desired `TableSchemaDescriptor`.
2. **Observed schema** — read-only normalized physical facts gathered from the selected server-owned database environment for the resolved managed table identity.
3. **Applied migration state** — later durable evidence of which reviewed plan/generation actually completed and verified. This is not inferred merely from Definition revision or schema similarity.

Publishing a Definition changes desired state only. It must never mark a physical migration applied.

## Architecture reconciliation

The existing `CUSTOM-TABLES-DDL-MIGRATION-LANGUAGE.md` already defines the target boundary:

- Migration Plan is generated from `observed -> desired`;
- plan operations are typed, deterministic and reviewable;
- raw user-entered SQL/DDL is not the product primitive;
- source observed fingerprint must be revalidated before later mutation;
- success requires post-operation introspection matching expected schema;
- DDL rollback cannot be universally promised.

The existing PT-D/PT-E physical profile also requires independent desired/observed state and selects **CT1 / PT-E per-site managed tables as the first baseline**. CT2 shared scoped and CT3 network-owned runtime remain comparison/deferred classes, not automatic promotion targets.

## Next authorized tranche

### Claim
`agent/custom-tables-observed-schema-plan-v1`

### Objective
Implement the smallest read-only/pure planning seam needed before any physical migration executor exists:

- immutable normalized observed-table descriptor for the bounded CT1/PT-E V1 type/index vocabulary;
- server-owned physical identity input only; no request-selectable table prefix/provider/implementation;
- deterministic observed fingerprint over normalized physical facts;
- explicit missing-table state rather than synthetic empty schema;
- pure desired-vs-observed diff planner producing a typed immutable Migration Plan preview;
- deterministic operation ordering;
- risk classification R0–R4 consistent with the architecture language;
- explicit unsupported/drift findings and blocked-plan state;
- source observed fingerprint embedded in the plan for later revalidation;
- recovery/restore-point requirement classification, but no Backup invocation;
- no SQL generation or mutation method.

### Bounded operation families for this tranche
The pure planner may describe only:

- `create_table` — R1 candidate when table is missing;
- `add_column` — R1 only for supported safe additive shape; otherwise escalate/block;
- `alter_column_default` — risk classified, never executed;
- `alter_column_nullability` — R1/R2/R3 depending on direction/preconditions;
- `alter_column_type` — classify compatible/conditional/lossy/unsupported; no conversion execution;
- `add_index` / `add_unique_constraint` — unique requires a future duplicate precondition and is at least R2;
- `drop_index` / `drop_unique_constraint` — R2/R3 depending on dependency/unknown state;
- `drop_column` — R4 and recovery-required;
- primary-key replacement — R3/R4 and recovery-required;
- unknown column/index/type/collation/ownership drift — finding/blocker, never auto-corrected.

`rename_table`, `rename_column`, backfill/dedup transforms, shadow/copy/swap, destructive table drop and physical topology conversion remain **deferred** because identity mapping, consumer dependency evidence and recovery contracts are not yet executable/certified.

## Read-only observation boundary

The next tranche may define a narrow introspection contract or injectable reader abstraction, but tests must prove the planner itself is pure. Any concrete MySQL/MariaDB reader must:

- use only server-owned resolved physical identifiers;
- read metadata only;
- normalize engine-specific metadata into the bounded observed descriptor;
- never issue CREATE/ALTER/DROP/RENAME/INSERT/UPDATE/DELETE;
- never accept arbitrary SQL or public table/column selectors;
- fail closed on unsupported type/default/collation semantics rather than guessing.

Provider/version capability classification belongs to a server-selected compatibility profile. Public input cannot choose MySQL vs MariaDB compiler behavior.

## Risk and recovery truth

Risk classes remain:

- **R0** — metadata/no physical change;
- **R1** — additive/normally reversible;
- **R2** — controlled data-affecting/precondition-bound;
- **R3** — high-impact/destructive/availability-sensitive;
- **R4** — data-loss capable.

The plan records recovery requirements only. No future executor may claim universal transactional rollback for DDL. R3/R4 execution will require a separately audited restore/recovery boundary, stronger authorization and exact observed-fingerprint revalidation.

## Multisite boundary

The certified first implementation topology remains **CT1 / PT-E site-owned managed table**. Physical identity is derived from trusted server/site context and the managed Definition mapping.

Deferred:

- CT2/PT-D shared scoped row runtime;
- CT3 genuinely network-owned data;
- topology promotion/conversion;
- unbounded network fan-out migration.

## Explicitly blocked after this audit

Even after the next worker lane promotes, the following remain separately serialized:

- provider SQL/DDL compiler;
- CREATE/ALTER/DROP/rename executor;
- `dbDelta()` decision/runtime;
- migration run persistence/state machine/lease;
- backfill/dedup/shadow-copy/swap execution;
- verified Backup/restore-point integration;
- row CRUD/Data Source and Query provider;
- admin/REST/Ability mutation surface;
- external table adoption;
- CT2/PT-D and CT3 runtime;
- product parity, deployment or release.

## Exit for the next worker lane

Exact-head tests must prove:

- deterministic observed normalization/fingerprint;
- deterministic no-op and bounded diff plans;
- unknown/manual drift remains explicit and is never silently overwritten;
- R3/R4 findings require recovery classification;
- source observed fingerprint is carried into the plan;
- missing table is distinct from empty table;
- no SQL/DDL/database mutation primitive exists in the planner path;
- no public scope/provider/physical-name widening channel exists.

Only after that lane is promoted should a fresh exact-main audit decide whether a read-only real-DB introspector, provider DDL compiler, migration-state persistence or another prerequisite is the next safe step.
