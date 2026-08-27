# WPEssential — P-010 Relations Physical Evidence Protocol

Status: **Future executable evidence protocol / NOT AUTHORIZED**  
Date: 2026-08-28  
Related: ADR-0014, ADR-0069, ADR-0071, ADR-0074, `RELATION-RUNTIME-SCHEMA-ALTERNATIVES.md`.

## Goal

Choose and verify Relations runtime physical topology without weakening scope isolation, cardinality, reverse lookup, Policy, migration or site lifecycle semantics.

**Executed fixtures: 0.**

## Candidate profiles

### R1 — PT-D shared scoped universal edge table
Accepted first benchmark baseline.

### R2 — PT-E per-site universal edge table
Mandatory comparison.

### R3 — per-relation table
Exceptional scale candidate only; benchmark only after a concrete use case warrants it.

### R4 — native/meta adapter baseline
Reference/interoperability only, never automatically eligible as universal relation engine.

## Environment matrix

Record:
- WordPress/PHP/DB versions;
- DB engine/row format/charset;
- Multisite mode/site count;
- object cache state;
- Relation profile/schema revision;
- endpoint identity encoding candidate;
- pivot profile;
- dataset scale;
- concurrency level.

## Dataset tiers

### RS-1 Functional
- 10 Relations;
- 1k edges;
- one-to-one/one-to-many/many-to-many;
- 3 sites;
- pivot/order mix.

### RS-2 Normal
- 100k edges;
- 25 Relations;
- 20 sites;
- skewed relation sizes.

### RS-3 Large
- 1M edges;
- 100 Relations;
- 100 sites;
- high-degree endpoints;
- mixed WP posts/users/terms/custom-table endpoints.

### RS-4 Extreme
- 10M edges where practical lab capacity allows;
- record actual achievable scale rather than inventing a universal requirement.

### RS-5 Large-network
- 1k/synthetic 10k site topology comparison;
- mostly small sites + one/two high-volume sites;
- measure R1 global index cost vs R2 table-provision/migration cost.

## Endpoint distributions

Include:
- WP numeric post IDs duplicated across different sites;
- user endpoints where users are network-shared but relation scope is site-owned;
- term/post/media/custom-table endpoint mixes;
- numeric and bounded string custom-table PKs where supported;
- missing/deleted endpoints;
- one endpoint with 10k+/100k related records;
- symmetric relation duplicates in reverse order;
- pivot-heavy and pivot-free relations;
- manual ordering with large sibling list.

## Mandatory correctness/security gates

Every profile must prove:
1. Site A numeric ID cannot resolve Site B entity;
2. cross-site relation creation denied by default;
3. exact duplicate edge impossible under concurrent attach;
4. one-to-one uniqueness on both sides;
5. one-to-many child uniqueness;
6. symmetric A↔B duplicate normalization;
7. reverse lookup returns same logical edge set;
8. inaccessible target does not leak via list/count/existence according to Policy;
9. detach/pivot failure does not leave partial success;
10. relation Definition revision/state respected;
11. delete policy cannot cascade arbitrary third-party entities;
12. Site Backup exports only correct site-owned edges;
13. import remaps endpoint identities and detects conflicts;
14. site deletion/transfer cannot affect unrelated site edges;
15. orphan repair cannot reveal deleted/private resource data.

Failing a correctness/security gate rejects the profile regardless of speed.

## Core read workloads

### RQ1 forward list
All B for one A + Relation, paginated.

### RQ2 reverse list
All A for one B + Relation.

### RQ3 exact pair existence
Hot permission/conditional pattern.

### RQ4 related count
Policy-aware count semantics.

### RQ5 high-degree page
Endpoint with 10k+/100k edges.

### RQ6 ordered list
Manual order token.

### RQ7 pivot filter/order
Only where Relation declares efficiently queryable pivot field.

### RQ8 Query Builder predicate
Filter parent records by related entity criteria.

### RQ9 cleanup inventory
All edges touching deleted endpoint.

### RQ10 site cleanup inventory
All edges owned by target site.

### RQ11 network diagnostic aggregate
Bounded admin health counts by site/relation.

## Write workloads

- attach one edge;
- bulk attach 100/1k/10k;
- detach;
- bulk detach;
- update pivot;
- reorder;
- child reassignment one-to-many;
- one-to-one replacement according explicit API mode;
- orphan purge/remap;
- site transfer scoped remap experiment where architecture allows.

## Concurrency fixtures

- 20+ clients attach exact same pair;
- two clients claim same one-to-one endpoint;
- concurrent child reparent;
- detach vs pivot update;
- detach vs reorder;
- entity delete vs attach;
- relation Definition disable/publish vs attach;
- Site Backup read vs writes;
- site lifecycle drain vs queued relation writes.

Record deadlocks/retries and final invariant state.

## Physical endpoint encoding comparison

Where useful compare:
- E1 normalized typed endpoint columns;
- E2 bounded canonical textual endpoint identity;
- E3 hybrid local materialized key + portable identity metadata.

Selection must consider index width, source flexibility, diagnostics and import/export—not speed alone.

## Pivot profiles

Compare where warranted:
- PV1 compact versioned payload + selected normalized query/order columns;
- PV2 generic typed pivot-value table;
- R3 native relation-specific columns only for exceptional profile.

A pivot field shown as queryable in UI must have proven query plan/budget.

## R1 PT-D specific tests

- deliberate missing/wrong `site_id` predicate security tests;
- global index hot-site skew;
- site cleanup range efficiency;
- one hot site vs many small sites;
- network Backup/restore;
- scoped Site Backup extraction;
- table/index growth at large network scale.

## R2 PT-E specific tests

- site table provisioning;
- 100/1k/10k-site migration/upgrade cost;
- missing/outdated site table detection;
- nested wrong blog context attack/error;
- Network Admin diagnostic fan-out;
- site Backup/drop/restore;
- cross-site advanced relation limitation/secondary-engine cost.

## R3 specific tests

Only after justified:
- relation creation/deletion DDL lifecycle;
- thousands-of-relations table count;
- pivot schema migration;
- Backup/export discovery;
- table-name collision/security;
- network rollout cost.

## Performance metrics

Record:
- p50/p95/p99 forward/reverse/exact lookup;
- throughput attach/detach/bulk;
- index/table size;
- rows examined/query plans;
- lock/deadlock/retry rate;
- PHP memory where relevant;
- cleanup time;
- Site Backup extraction time;
- per-site provisioning/migration time for R2;
- large-network health/admin cost.

No universal latency target is invented until test environment and product budget are defined.

## Query-plan audit

For each index/query:
- intended workload;
- actual planner choice;
- selectivity under hot-site/high-degree skew;
- left-prefix overlap/redundancy;
- write/storage cost;
- whether optional order/pivot index should be materialized only for Relations that need it.

## Failure/adversarial fixtures

- malformed endpoint identity;
- unsupported Data Source;
- endpoint missing after validation;
- unauthorized endpoint;
- source adapter unavailable;
- cardinality race;
- duplicate attach;
- deadlock;
- partial pivot validation failure;
- orphan edge;
- wrong site/network scope;
- site deleted mid-operation;
- stale Relation Definition revision;
- import duplicate/cardinality conflict;
- Backup restore to remapped site IDs.

## Profile-selection rule

Candidate must first pass:
1. security/scope correctness;
2. cardinality/data integrity;
3. API/query semantics;
4. migration/site lifecycle/recovery;
5. compatibility/maintainability;
6. then performance/scale budget.

R2 can replace R1 default only with evidence of material total-system benefit. R3 cannot become default from one synthetic speed result.

## Evidence artifact

Future authorized run persists:
- environment/profile/version;
- schema DDL;
- fixture generator/seed;
- exact commands;
- query plans;
- metrics;
- concurrency outcomes;
- security failures;
- site lifecycle/Backup results;
- selected/rejected profile rationale;
- known limits and migration implications.

## Authorization gate

**Executed fixtures: 0.**

No tables, migrations, fixture data, benchmarks, load tests or query execution are authorized before explicit owner consent under ADR-0014.
