# Post-Status Next Dependency Gate Audit V1

Exact main audited: `cbd2b898847e206538086bd5033161caa506a8fb`  
Audit owner: Issue #380  
Audit date: 2026-09-08

## Verdict

**Select Surface 7 — Custom Tables Builder as the next dependency-gated implementation surface.**

The first authorized tranche is intentionally non-destructive and DDL-free: **canonical `table` Definition + deterministic schema descriptor only**.

This audit does not authorize physical table creation, schema migration execution, row CRUD, external-table adoption, full Surface 7 parity, deployment or release.

## Why Custom Tables is next

The choice is based on architecture and dependency value, not numeric Options Bank order.

### 1. Remaining implementation topology

Exact-main `frameworks/Modules` currently contains implemented business modules for CPT, Taxonomy, Fields, Relations, Query, Admin Columns, Listings and Status. There is no Surface 7 Custom Tables runtime module and no Surface 10 Dashboard Widgets runtime module.

After Status closes, the architecture has no new universal peer-hard module dependency. A new gate therefore needs to be selected by canonical ownership, foundation value, planning maturity and safe executable boundary.

### 2. Surface 7 is the remaining unimplemented Wave-1 data foundation

`config/product/atomic-option-contract-progress.json` places Surface 7 in Wave 1, **Core schema and data foundation**, alongside CPT, Taxonomy, Fields, Relations, Query and Settings.

The already-certified implementation critical path has established CPT/Taxonomy foundations plus Fields, Relations and Query. Surface 7 is the major remaining unimplemented data/storage owner in that foundation wave.

### 3. Surface 7 has mature bounded planning evidence

`config/product/options-bank-reviews/tables-bank-review-v1.json` records:

- `BANK_REVIEWED`;
- 165 records;
- zero unreviewed Bank records;
- zero native-audit unresolved items;
- zero market-audit unresolved items.

This does not equal runtime certification, but it is sufficient planning maturity for a bounded implementation gate when paired with the accepted architecture and active development consent.

### 4. Surface 7 unlocks downstream owner-governed capabilities

The canonical ownership/dependency maps assign Custom Tables ownership of WPE-managed table schema, physical migration semantics and table-backed rows/Data Source capability.

Later surfaces may consume these capabilities through public contracts, including Forms/Workflow, REST API Builder, Import/Export, Ledger and other data-oriented integrations. By contrast, Dashboard Widgets is primarily a presentation/inventory surface and does not close a comparable shared data-foundation gap.

This is a prioritization decision, not a claim that those later surfaces hard-depend on Surface 7 at boot.

## Canonical ownership boundary

Surface 7 owns:

- WPE-managed table schema definitions;
- desired physical schema semantics;
- migration-plan semantics for owned tables;
- table-backed row ownership where WPE storage is selected;
- stable table/column/index identities exposed to consumers.

Surface 7 does **not** own:

- generic Field type/validation/UI semantics — Surface 3/shared Field Schema;
- relation/cardinality/edge semantics — Surface 4;
- reusable filter/sort/search/query AST semantics — Surface 6;
- list-table presentation — Surface 8;
- frontend rendering — Surface 9/shared Renderer;
- backup/restore execution — Surface 24;
- import/export orchestration — Surface 26;
- Ledger semantics — Surface 36;
- arbitrary DBA/SQL console behavior.

## Physical topology decision

The accepted Custom Tables PT-D/PT-E profile recommends **CT1/PT-E per-site managed tables as the first baseline for ordinary site-owned tables**, while requiring CT2/PT-D shared physical storage to remain a later evidence-backed comparison and CT3 to remain reserved for genuinely network-owned semantics.

For the first implementation tranche, this topology exists only as desired-state metadata. No table is created.

No automatic site-to-network/shared topology promotion is allowed.

## First authorized implementation tranche

Issue to open after this audit promotes:

**Custom Tables V1 — canonical table Definition + schema descriptor**  
Deterministic claim: `agent/custom-tables-definition-compiler-v1`

### Required Definition contract

The first implementation owns one canonical Surface 7 Definition type: `table`.

Bounded payload must provide enough information to compile deterministic desired-state metadata while excluding executable database behavior:

- authored logical table key;
- human label/name;
- storage mode fixed to WPE-managed for V1;
- semantic scope fixed to site-owned CT1/PT-E baseline for V1;
- desired schema version;
- ordered typed columns;
- explicit primary-key column references;
- bounded secondary/unique indexes;
- compatible charset/collation inheritance metadata only where safely representable;
- data/privacy classification metadata only when bounded and non-executable.

### Required column contract

Each column must have a stable authored key and bounded physical/logical metadata.

V1 should prefer a deliberately small compatibility-safe physical type vocabulary rather than implement the entire 165-option Bank at once. The compiler must reject unknown type families and invalid option combinations.

At minimum, validation must cover:

- identifier grammar/length;
- duplicate column keys;
- primary-key references to declared columns only;
- index references to declared columns only;
- duplicate index keys;
- bounded index column count;
- nullable/default compatibility;
- auto-increment restrictions if included in the V1 vocabulary;
- length/precision/scale bounds for supported types;
- no arbitrary SQL expression defaults;
- no raw DDL, SQL fragments, PHP callbacks or executable transform channels.

### Deterministic descriptor

Compilation must return an immutable descriptor suitable for later planner/runtime consumers, including:

- Definition id/revision;
- logical table key;
- fixed site-owned CT1/PT-E desired topology;
- normalized ordered columns;
- normalized primary key and indexes;
- desired schema version;
- deterministic compatibility/canonical fingerprint over semantic desired state.

Descriptor/fingerprint generation must be stable for semantically identical input and must not include environment-specific physical table prefixes or generated SQL.

### Critical invariant

**Publishing/saving a `table` Definition is desired-state authoring only. It MUST NOT create, alter, rename or drop any physical database table or column.**

The Definition repository remains the source of authored desired-state truth. Physical observed/applied state belongs to later migration/introspection contracts.

## Explicitly deferred serialized gates

The following are not authorized by the first tranche:

1. physical `CREATE TABLE`, `ALTER TABLE`, `DROP`, rename or `dbDelta()` execution;
2. provider-specific MySQL/MariaDB DDL compiler;
3. observed-schema introspection/fingerprint service;
4. observed→desired diff and Migration Plan generation;
5. migration run state machine/executor;
6. rename/drop/type-change/backfill/unique-dedup/shadow-copy/swap;
7. recovery/restore-point execution and Backup integration;
8. CT2/PT-D shared-physical or CT3 network-owned runtime;
9. external-table inspection/adoption;
10. row CRUD/Data Source provider;
11. Query provider integration;
12. admin schema editor/data browser;
13. REST/Ability exposure;
14. portability/import-export integration;
15. full 165-option parity or provider ecosystem parity.

Each destructive or persistence-mutating lane must have its own exact-main audit/evidence and fail-closed recovery/security contract before implementation.

## Safety gates inherited from architecture

Later physical work must preserve these non-negotiable rules:

- raw user-entered DDL is not the normal product primitive;
- unchecked request identifiers never reach SQL;
- Definition publish and physical migration success are separate states;
- drift is diagnosed, never silently overwritten;
- R3/R4 destructive changes require stronger review/recovery semantics;
- no universal transactional rollback promise for DDL;
- external tables are read-only by default until explicit adoption certification;
- multisite scope is server-owned and wrong-site access is deny-by-default;
- Definition deletion never implicitly deletes physical data.

## Coordination decision

After this Supervisor audit promotes:

- `post-status-next-gate-entry-audit-v1` becomes historical completed work;
- exactly one initial `ANY` implementation slot becomes actionable: `custom-tables-definition-compiler-v1`;
- no physical DDL/migration worker is authorized in parallel with that first Definition tranche;
- Workers must not invent Dashboard Widgets, Settings, Forms, REST, Backup or other post-Status branches while the Custom Tables entry tranche is active unless a later Supervisor audit explicitly opens path-disjoint work.

## PASS boundary

Promotion of this audit means only **Custom Tables implementation gate opened / first bounded tranche authorized**.

It does not mean Surface 7 runtime PASS, all 165 Bank records implemented, `OPTION_CONTRACT_COMPLETE`, `RUNTIME_CERTIFIED`, `PRODUCT_PARITY_CERTIFIED`, production deployment or release approval.
