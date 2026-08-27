# WPEssential — P-004 Definition Repository Physical Evidence Protocol

Status: **Future executable evidence protocol / NOT AUTHORIZED**  
Date: 2026-08-28  
Related: ADR-0014, ADR-0049, ADR-0069, ADR-0071, ADR-0073.

## Goal

Select and verify the exact Definition Repository PT-C physical profile without allowing raw benchmark speed to override correctness, scope isolation, migration safety or maintainability.

No fixture has been executed.

## Candidate profiles

- **D1 baseline:** textual UUID + explicit scope + bounded identity keys + text revision payload + minimal indexes + app integrity diagnostics.
- **D2:** D1 semantics with compact binary UUID representation.
- **D3:** D1 identity semantics with native JSON payload.
- **D4:** D1/D2 candidate with validated DB foreign keys/constraints where compatible.

Additional variants require a new documented reason before execution.

## Environment matrix

P-001 determines supported combinations. P-004 records at least:
- WordPress version;
- PHP version;
- MySQL/MariaDB version;
- storage engine/row format;
- charset/collation;
- object cache state where relevant;
- single site vs Multisite;
- site count fixture;
- dataset scale;
- exact schema/profile revision.

Do not compare results from materially different environments as if they were one benchmark.

## Dataset tiers

### DS-1 Functional small
- 100 Definitions;
- mixed module types;
- draft/published/archived states;
- dependencies/unresolved references;
- 3-site Multisite split.

Purpose: correctness/debuggability.

### DS-2 Normal
- 10,000 Definitions;
- 5 revisions average;
- 2–5 dependencies average;
- 20 sites with skewed distribution.

### DS-3 Large
- 100,000 Definitions;
- 5–10 revisions average;
- 5 dependencies average;
- 100 sites;
- one hot site owns substantial fraction.

### DS-4 Extreme synthetic
Scale until practical lab/storage budget limit, with explicit value recorded rather than pretending a universal 1M requirement.

### DS-5 Large-network control-plane
- 1k and synthetic 10k site identities where environment permits;
- mostly low-definition sites;
- network-level templates/defaults;
- site/network lookup mix.

## Data distribution requirements

Fixtures must include:
- duplicate machine keys across different sites — valid;
- machine-key collision within same scope/type — invalid;
- network Definition + site Definitions with same key;
- very long but valid bounded identifiers;
- mixed active/archived/tombstoned states;
- deep dependency chains;
- wide fan-out dependencies;
- unresolved imported dependencies;
- revision-heavy Definitions;
- one site with heavy write activity;
- concurrent edits to same Definition;
- deletes/tombstones while referenced.

## Correctness gates — mandatory before performance

Every profile must prove:
1. UUID uniqueness;
2. scope + type + machine-key uniqueness;
3. Site A cannot list/read/mutate Site B Definition by identifier manipulation;
4. Network resource cannot be accidentally treated as Site resource;
5. revisions immutable after commit;
6. current/published pointer belongs to same Definition;
7. optimistic save/publish conflict detected;
8. required dependency validation;
9. reverse `Used by` distinguishes revision state;
10. Site Backup exports exactly target-site Definition rows/revisions/dependencies;
11. Site restore/remap does not collide with another site's keys;
12. archive/tombstone does not silently purge history;
13. invalid/missing pointer diagnosed safely;
14. interrupted migration leaves recoverable version state.

A profile failing any security/data-integrity gate is rejected regardless of benchmark speed.

## Core query workload

For each candidate capture SQL/query abstraction + EXPLAIN/plan where supported.

### Q1 UUID lookup
Single Definition by canonical UUID.

### Q2 machine-key lookup
`scope + definition_type + machine_key`.

### Q3 site/type active list
Cursor-paginated list by scope/type/lifecycle.

### Q4 network aggregate list
Bounded Network Admin list across selected/all sites according to policy.

### Q5 revision history
Recent/history pages for one Definition.

### Q6 publish pointer read
Resolve published revision with minimal joins.

### Q7 dependency compile
All dependencies for one revision.

### Q8 reverse Used By
Current/published source revisions referencing one target.

### Q9 unresolved import remap
Lookup dependency rows by target UUID.

### Q10 archive/tombstone list
Admin maintenance query.

### Q11 Site Backup extraction
Definitions + revisions + dependencies for exactly one site.

### Q12 Site lifecycle cleanup inventory
Count/list scoped resources without deleting them.

## Write workload

- create Definition + first revision;
- append draft revision;
- publish revision;
- concurrent draft save conflict;
- concurrent publish conflict;
- archive/reactivate;
- tombstone;
- dependency replacement on new revision;
- import batch create/update;
- network template rollout metadata where architecture requires it.

## Concurrency fixtures

At minimum:
- 2 writers same Definition expected generation;
- 20+ parallel independent Definition creates;
- same machine-key collision race;
- publish while another draft save occurs;
- reverse-dependency read during publish;
- Site Backup read during writes;
- migration/read concurrency according to migration policy.

Record deadlocks/retries and final correctness.

## Migration fixtures

Test future schema versions conceptually such as:
- add nullable normalized column;
- add index;
- backfill derived field;
- widen/narrow candidate identifier only when safe plan exists;
- payload canonicalization/schema-version migration;
- D1 ↔ D2 UUID representation migration experiment;
- D1 ↔ D3 payload representation experiment;
- add/remove FK in D4 lab profile.

Measure migration time, locks/downtime, disk amplification and rollback/recovery feasibility.

## Performance metrics

Record:
- p50/p95/p99 latency;
- throughput for write batches;
- rows examined;
- query plan/index used;
- lock/deadlock rate;
- CPU where measurable;
- DB/storage/index size;
- migration duration;
- peak temp/disk usage;
- PHP memory for repository operations where relevant.

No universal latency threshold is invented before environment baseline. Compare candidates against explicit acceptance budgets defined immediately before execution.

## Index audit

For every index:
- workload/query it serves;
- left-prefix overlap with another index;
- write/storage cost;
- whether query planner actually uses it;
- scope-isolation relevance.

Unused/redundant indexes must not survive merely because they seem helpful.

## Failure/adversarial fixtures

- malformed UUID/key;
- oversized identifier/payload;
- wrong-site scope;
- stale generation;
- missing target revision;
- dependency cycle if module forbids it;
- DB deadlock;
- connection interruption mid-transaction;
- disk-full/migration failure where lab supports it;
- corrupted payload/hash mismatch;
- orphan pointer injected in test DB;
- old plugin/schema version read attempt.

## Profile-selection rule

A candidate is eligible only if it passes:
1. correctness/security/integrity;
2. supported DB compatibility;
3. migration/recovery requirements;
4. maintainability/diagnostics;
5. then performance/scale budgets.

D2/D3/D4 replace D1 only if evidence shows a material benefit that justifies added complexity/compatibility cost.

## Evidence artifact

Future run must persist:
- environment matrix;
- schema/profile version;
- fixture generator version/seed;
- exact commands;
- query plans;
- metrics raw summaries;
- failures;
- decision table;
- selected/rejected profile rationale;
- known limits;
- rollback recommendation.

## Authorization gate

**Executed fixtures: 0.**

No fixture DB, DDL, migration, query benchmark or code may be created/run until explicit owner development/evidence consent under ADR-0014.
