# WPEssential — Custom Tables PT-D/PT-E Physical & Migration Profile

Status: **Phase 0 paper physical profile / no table, DDL or migration execution authorized**  
Date: 2026-08-28  
Related: Custom Tables DDL Migration Language, Query P-009, Field Storage ADR-0087, Multisite topology ADR-0071, Site Lifecycle ADR-0075.

## Purpose

Define when a WPE-managed Custom Table should be physically per-site versus shared scoped, while preserving one desired-schema/migration language.

The Builder does not expose arbitrary raw DDL as the normal product primitive.

## Physical profiles

### CT1 — PT-E per-site managed table — first baseline for site-owned builder tables

Use when the table is conceptually owned by one site and cross-site aggregation is not a core workload.

Characteristics:
- one physical table per site/table definition mapping;
- site isolation reinforced by physical namespace;
- simpler site Backup/delete/clone extraction semantics;
- no `site_id` predicate required for every ordinary row query because table identity carries site scope;
- potential table-count/migration/provisioning explosion on large networks.

CT1 is the first benchmark for ordinary site-created application tables because site ownership is the default product scope.

### CT2 — PT-D shared scoped managed table — mandatory comparison

Use when evidence shows shared storage materially improves large-network provisioning/migration/operations or when the domain is intentionally shared while rows remain site-owned.

Hard requirements:
- explicit scope columns in every row identity/query path;
- composite uniqueness/indexes include scope where uniqueness is site-local;
- no query/update/delete by row ID alone when scope ownership matters;
- site lifecycle cleanup/extract/clone filters by trusted scope;
- wrong-site attacks are mandatory certification fixtures.

### CT3 — explicit network-owned managed table

For a table whose records are genuinely network-owned rather than site rows in a shared physical table.

Rules:
- Definition explicitly declares network scope;
- network capability required for schema and data mutation;
- site admin does not gain access merely because table is physically shared;
- site deletion does not remove network-owned rows unless domain semantics explicitly link them.

Physical implementation may use PT-D or another accepted network/runtime class based on workload, but semantic ownership is network, not synthetic `site_id = 0` convenience.

## No automatic topology promotion

A site table does not become shared/global automatically because:
- the plugin is network activated;
- many subsites use the same Table Definition;
- a network administrator created the template;
- a Query wants a cross-site report.

Topology change is a migration-class operation requiring evidence and explicit plan.

## Physical identity

Each managed table has:
- stable Table Definition UUID;
- desired schema version;
- physical identity/mapping per scope topology;
- observed schema fingerprint;
- applied migration generation;
- owner/scope classification.

Portable exports carry logical schema, never environment-generated SQL/table prefix as identity.

## Row identity

Candidate baseline:
- local numeric physical primary key where DB efficiency benefits;
- optional stable row UUID when portable/external identity is required;
- explicit site/network scope in CT2 row identity;
- business unique keys separate from physical PK.

Exact types and UUID representation remain benchmark evidence.

## Index rule

Indexes are workload-driven and bounded.

Minimum classes to evaluate:
- PK/row lookup;
- site scope + row/current-state lookup for CT2;
- declared Query filter/sort hot paths;
- uniqueness constraints;
- migration/retention scan if domain requires.

Do not auto-index every field. Field queryability Q3/Q4 requires an actual supporting index/provider plan.

## Desired schema vs observed state

Definition publish updates desired schema only.

Physical state remains independently observed. A migration run must:
1. introspect observed schema/fingerprint;
2. compare with desired revision;
3. generate deterministic typed Migration Plan;
4. classify risk;
5. revalidate before mutation;
6. execute only after future authorization;
7. introspect/verify resulting schema;
8. record applied fingerprint/generation.

No Definition save silently performs R3/R4 DDL.

## Migration strategies to compare

### CM1 — direct compatible alteration

Candidate for small/additive compatible changes after DB capability evidence.

### CM2 — chunked backfill + later constraint/index

For changes requiring data preparation before final schema enforcement.

### CM3 — shadow/copy/swap

For high-impact transformations where direct ALTER locking/availability is unacceptable and provider/host evidence supports a safer staged copy.

### CM4 — recovery-only destructive change

For drops/lossy transforms where rollback cannot be guaranteed; requires verified recovery point and explicit destructive approval.

These are planner semantics, not approval to issue SQL.

## Online/availability truth

WPE must not label a migration “online” merely because MySQL/MariaDB offers an `ALGORITHM` hint.

Future DB capability evidence must distinguish:
- metadata-only/instant where actually supported;
- in-place with possible locks;
- copy/rebuild;
- unknown/fallback behavior.

If the DB can silently fall back to a more blocking algorithm, UI/plan must represent that risk rather than promise zero downtime.

## Concurrency and drift

Before execution, migration fingerprint must still match the reviewed source.

Concurrent schema/data changes can:
- block/replan;
- require write freeze;
- use dual-write only if an explicit migration strategy proves correctness;
- never be ignored simply to make desired state appear applied.

External/manual schema drift is surfaced and classified; WPE does not blindly overwrite unknown changes.

## Site lifecycle

CT1:
- provision on site/table enablement according to accepted lifecycle coordinator;
- site Backup/clone maps physical table explicitly;
- deletion follows domain retention/destructive plan, not blind prefix drop.

CT2:
- site create may require no physical table create but must initialize scoped metadata as needed;
- site Backup extracts only owned rows;
- deletion/clone/transfer operates by trusted site scope and is resumable/auditable;
- shared table itself is never dropped because one site is deleted.

## Network scale evidence

Future comparison must include:
- 100 / 1k / 10k sites;
- 1 / 10 / 100 managed table definitions per site where reasonable;
- schema publish affecting many sites;
- site create/delete churn;
- one noisy site with high row/write volume;
- migration fan-out time/table metadata overhead;
- Backup/site extraction cost.

CT1 wins only if table proliferation remains operationally acceptable; CT2 wins only if scope isolation and shared-index/noisy-neighbor behavior pass.

## Security gates

Reject any profile if:
- user-controlled table/column names bypass registry validation;
- values can escape prepared/bound handling;
- wrong-site CT2 row can be read/updated/deleted by ID collision;
- network table can be mutated by site-only authority;
- a site delete/Reset drops shared data belonging to another site;
- migration executes against changed fingerprint without revalidation;
- destructive Definition deletion implicitly drops data.

## Future executable evidence — NOT AUTHORIZED

- MySQL/MariaDB/WP supported compatibility matrix;
- create/add/index/rename/type/null/default/drop plans;
- lock/algorithm behavior;
- chunk backfill crash/resume;
- shadow swap crash windows;
- duplicate/unique races;
- drift/adoption cases;
- CT1 vs CT2 storage/query/migration/provisioning at 100/1k/10k sites;
- wrong-site attack corpus;
- Backup/Restore/site lifecycle.

Executed Custom Tables fixtures: **0**.

## Paper recommendation

Use **CT1/PT-E as the first baseline for ordinary site-owned builder tables**, with **CT2/PT-D a mandatory large-network/shared-physical comparison** and CT3 only for genuinely network-owned data.

Topology choice cannot weaken Query/Policy/scope/Backup/lifecycle correctness, and schema publication remains separate from physical migration completion.