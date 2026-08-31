# WPEssential — Relations PT-D vs PT-E Physical Benchmark Profile

Status: **Phase 0 paper architecture / no DDL or benchmark authorized**  
Date: 2026-08-28  
Related: ADR-0014, ADR-0069, ADR-0071, `RELATION-RUNTIME-SCHEMA-ALTERNATIVES.md`, P-010.

## 1. Purpose

The Relations runtime architecture already prefers one universal typed edge-table family over post-meta arrays or per-relation DDL by default. ADR-0071 classifies Relations as a **PT-D candidate** but leaves physical topology evidence-gated.

This document defines the future comparison profiles without changing the accepted logical relation semantics.

## 2. Logical semantics that cannot change by topology

Any physical profile must preserve:
- explicit Relation Definition identity/revision;
- typed endpoint identities through Data Source Registry;
- site/network scope coordinates;
- directed/undirected semantics;
- one-to-one / one-to-many / many-to-one / many-to-many cardinality;
- concurrency-safe duplicate/cardinality enforcement;
- reverse lookup as first-class operation;
- optional ordering and typed pivot metadata;
- access Policy on both relation and target resources;
- deterministic import/export identity remapping;
- orphan detection/repair;
- delete policy;
- audit/events after successful commit;
- cross-site relations **Off by default**.

Physical layout must never weaken these semantics.

## 3. R1 — PT-D shared scoped universal edge table — benchmark baseline

One WPE network/global runtime table family stores site-owned relation edges with explicit scope and typed endpoint coordinates.

Conceptual hot row fields:
- physical edge ID;
- optional portable edge UUID if product use cases prove it necessary;
- relation Definition UUID/internal compiled relation key;
- relation definition revision/generation if required for diagnostics;
- owning `network_id`;
- owning `site_id` for normal site relations;
- endpoint A source/data-source key;
- endpoint A entity type/key;
- endpoint A typed local/reference identity;
- endpoint B source/data-source key;
- endpoint B entity type/key;
- endpoint B typed local/reference identity;
- canonical direction/order discriminator where needed;
- edge state;
- manual-order token(s) only where needed;
- pivot schema/version + compact payload/reference;
- created/updated timestamps.

Exact types/lengths are P-010 evidence.

### R1 advantages
- one generic Query/Ability/API path;
- reverse lookup does not require site table discovery;
- centralized migration/index maintenance;
- supports explicit future cross-site/network relation profile without moving rows to a different engine;
- simpler network diagnostics/health/orphan scans;
- Site Backup can extract rows by explicit site scope;
- avoids one relation table per relation and one table family per site.

### R1 risks
- large shared edge table;
- every query must include correct scope;
- scope leak/IDOR impact is high if query guard fails;
- endpoint polymorphism complicates index width/type design;
- very high-degree relations can create hot index ranges/lock contention;
- site deletion/transfer must clean/remap rows safely.

## 4. R2 — PT-E per-site universal edge table

Each WordPress site owns one WPE relation-edge table family using the same logical schema but without needing `site_id` in ordinary hot lookups.

### R2 advantages
- physical site isolation;
- smaller per-site indexes for many ordinary sites;
- site Backup/restore can map more naturally to per-site physical tables;
- site deletion can drop/cleanup isolated relation store after lifecycle checks.

### R2 costs
- table count multiplies with site count;
- network activation/upgrades need bounded table provisioning/migration across all sites;
- Network Admin diagnostics/aggregate queries must enumerate many tables/sites;
- cross-site relation support becomes awkward or needs a second global engine;
- 1k/10k-site networks create substantial schema-management overhead;
- new-site provisioning/migration failure can create version skew.

## 5. R3 — per-relation physical table

A dedicated table per Relation Definition.

This remains **exceptional**, not default.

Possible benefit:
- tight relation-specific endpoint types/indexes;
- strong cardinality constraints;
- pivot columns can be native/typed.

Costs:
- user creating/editing Relations creates DDL lifecycle;
- potentially thousands of tables;
- schema changes coupled to Definition edits;
- migration/backup/import/uninstall complexity;
- table-name collision/security concerns;
- network rollout explosion.

R3 can only become a certified optimization profile for a bounded high-scale use case after P-010 proves a material advantage and lifecycle cost is acceptable.

## 6. R4 — native/meta adapter baseline

Post meta/user meta/native source relationships can be benchmarked only as an interoperability baseline, not as WPE's universal relation storage.

No serialized-array relation design can become the generic engine merely because it is easy to implement.

## 7. Scope identity

### Normal site relation
R1 hot uniqueness includes owning site scope.

Conceptually:
`network + site + relation + endpointA + endpointB`

Identical numeric WordPress IDs on different sites are distinct resources.

### Network relation
Only an explicitly network-scoped Relation Definition can create network-owned edges.

### Cross-site relation
Separate advanced mode only.

Endpoint tuple must then include each endpoint's target site/network coordinate. Both endpoint resources must be Policy-authorized. Cross-site relation cannot be enabled merely by omitting the owning site ID.

## 8. Endpoint physical representation alternatives

Relation logical endpoint IDs are typed by Data Source Registry.

Future physical comparison:

### E1 — normalized typed columns
Separate source/entity-type + numeric/string/reference fields according to endpoint kind.

Pros: potentially efficient common WP numeric IDs.  
Cost: wider schema/nullable columns.

### E2 — canonical bounded textual identity
One normalized portable endpoint key.

Pros: generic.  
Cost: larger indexes and conversion overhead.

### E3 — source-specific materialized numeric key + portable external identity side metadata
Hybrid approach.

P-010 must compare actual common workloads before finalizing.

## 9. Index families to benchmark

R1 baseline requires logical support for:
- `scope + relation + endpoint A` → related B list;
- `scope + relation + endpoint B` → reverse list;
- `scope + relation + endpoint A + endpoint B` → exact pair / uniqueness;
- `scope + source/entity + entity ID` → cleanup/orphan scan;
- `scope + relation + endpoint A + order token` where manual ordering enabled;
- mirrored order index only where right-side ordering is a declared feature.

Do not create every optional index globally if the Relation Definition never uses that query mode.

Potential split between base edge table and optional materialized/index table is evidence-gated.

## 10. Cardinality under concurrency

Topology cannot rely on UI-only cardinality validation.

Future proof must cover:
- duplicate many-to-many attach;
- concurrent one-to-one attach from both sides;
- concurrent one-to-many child reassignment;
- pivot update vs detach race;
- reorder vs delete;
- deadlock/retry behavior.

Where composite uniqueness cannot express polymorphic rule efficiently, transactional lock/recheck or relation-scoped coordination must be benchmarked.

## 11. Pivot storage profiles

### PV1 — compact versioned payload + normalized common query/order columns
Current paper preference.

### PV2 — generic typed pivot-value table
Useful when arbitrary edge fields need querying, but can multiply rows/joins.

### PV3 — per-relation native columns/table
Only R3 exceptional profile.

P-010 must distinguish `stored pivot field` from `efficiently queryable pivot field`.

## 12. Site lifecycle

For R1/PT-D shared table:
- new site needs no new relation table;
- site deletion/archive enumerates scoped edges;
- deletion follows relation delete/retention policy;
- site transfer remaps scope only through reviewed migration, never bulk ID substitution;
- Site Backup exports only target-site edges and portable endpoint identities;
- network restore restores shared table while preserving site mapping.

For R2/PT-E:
- site creation may provision relation table;
- schema upgrades must track per-site version;
- deleted/archive site table retention/drop follows Site Lifecycle Coordinator policy;
- network migration must detect missing/outdated relation tables.

## 13. Query Builder integration

Query AST relation predicates consume one Relation Service API regardless of physical profile.

Query compiler must not know raw table names as public product semantics.

Performance fixtures:
- endpoint relation filter;
- reverse relation filter;
- nested relation predicate;
- count/existence;
- pivot predicate/order;
- result authorization after candidate lookup;
- bounded cross-site advanced query where supported.

## 14. Cache/invalidation

Physical topology does not become cache identity.

Cache keys include logical relation Definition revision/generation + site/network scope + endpoint + direction/order/access context.

Attach/detach/pivot/order changes increment scoped relation generation or equivalent invalidation token.

No Site A cached result can satisfy Site B.

## 15. Backup/restore/import

Portable relation runtime export uses source-specific portable/entity mapping, not raw physical table IDs.

R1 Site Backup extracts scoped rows; R2 includes site edge table. Both must produce the same logical portable result.

Restore/import must preflight:
- missing endpoints;
- relation Definition mismatch;
- site/network remap;
- cardinality conflicts;
- duplicate pair;
- pivot schema/version;
- order preservation;
- inaccessible target scope.

## 16. Security comparison

### R1 critical risks
- missing scope predicate can expose/corrupt another site;
- cross-site query accidentally enabled;
- global cleanup deletes rows from wrong site.

Required mitigations:
- repository/service always takes explicit Scope object;
- raw table access limited to Relation repository internals;
- site scope included in all hot lookup/write APIs;
- security regression fixtures mutate site IDs deliberately.

### R2 critical risks
- wrong `$wpdb->prefix`/blog context can access wrong table;
- nested `switch_to_blog()` errors can operate on wrong site;
- per-site schema version skew.

R2 is not automatically safer merely because tables are separate.

## 17. P-010 benchmark matrix — NOT AUTHORIZED

Datasets:
- 100k / 1M / 10M edges as environment permits;
- 10, 100, 1k, synthetic 10k-site topology metadata comparison;
- mostly-small sites + one high-volume site;
- high-degree endpoint 10k+/100k+;
- one-to-one and many-to-many mixes;
- pivot/no-pivot mixes.

Compare:
- R1 PT-D shared edge table;
- R2 PT-E per-site edge table;
- R3 selected per-relation table only for high-scale case;
- R4 native/meta reference baseline where meaningful.

Measure:
- table/index/storage footprint;
- p50/p95/p99 forward/reverse lookup;
- exact-pair lookup;
- insert/detach throughput;
- cardinality conflict behavior;
- lock/deadlock rate;
- Query Builder plan/rows examined;
- site deletion cleanup;
- Site Backup extraction;
- network migration/provision time;
- large-network admin/health scan cost.

## 18. Current recommendation

Use **R1/PT-D shared scoped universal edge table** as the first future P-010 benchmark baseline because it best preserves WPE's single relation service, reverse queries, Multisite control-plane integration and bounded cross-site extensibility without per-site/per-relation table explosion.

This is **not** final physical approval. R2 remains a required comparison; R3 remains an evidence-backed exceptional optimization.

## 19. Development gate

No Relation table, migration, repository code, benchmark dataset, query, lock test or fixture may be created/run before explicit owner development consent under ADR-0014.
