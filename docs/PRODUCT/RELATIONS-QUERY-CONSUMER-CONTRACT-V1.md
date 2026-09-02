# Surface 4 Relations — Query Consumer Contract V1

Status: **Gate B public consumer candidate / Query runtime remains blocked until accepted**  
Contract: `WPEssential\Contracts\RelationQueryConsumerInterface`  
Registered service: `module.relations.query-consumer`  
Owner: Surface 4 Relations

## Purpose

This contract is the only certified V1 seam for Surface 6 Query to read Relation topology. Query must not depend on `WpdbRelationEdgeGateway`, Relation table names, edge-state rows, migration classes, pivot layout, SQL, or any other private Surface 4 implementation detail.

The contract exposes stable Relation identity/capabilities plus bounded object-id reads. Physical execution remains Relations-owned and may change without changing Query AST semantics.

## V1 public operations

- `describe()` — published Relation identity, definition revision, opaque mutation revision, cardinality, direction policy, endpoint type/subtype, and consumer capabilities.
- `relatedObjectIds()` — bounded distinct related object IDs for one caller-authorized anchor.
- `matchingAnchorObjectIds()` — one bounded batch existence/filter operation over caller-authorized anchors, optionally constrained to caller-authorized related IDs. This is the V1 no-N+1 prefilter seam.
- `countRelatedObjects()` — distinct related-object count for one authorized anchor.

V1 capability limits are part of the public contract:

- maximum anchor batch: 100 IDs;
- maximum related-ID filter batch: 100 IDs;
- maximum returned IDs: 100;
- maximum traversal depth: 1;
- batch existence: supported;
- distinct count: supported;
- recursive/nested traversal: not supported by this V1 seam.

Callers must treat capability metadata as authoritative and fail closed when requested semantics exceed it. Unsupported semantics must never be approximated.

## Direction semantics

`from` means the supplied anchor belongs to the Relation `from` endpoint and returned/matched IDs belong to `to`.

`to` means reverse traversal. Reverse traversal is rejected when the published Relation definition does not enable `bidirectional_traversal`.

The consumer does not infer or rewrite direction from physical edge order.

## Scope and authorization boundary

Relations owns:

- canonical Relation definition ownership/type checks;
- published lifecycle requirement;
- Relation direction policy;
- registered site/network scope matching;
- bounded storage execution;
- distinct-object semantics for duplicate-capable edge storage;
- mutation revision exposure as an opaque invalidation/generation signal.

Query/Data Source Policy owns:

- authorization of every anchor ID before it is passed to Relations;
- authorization/visibility filtering of every related ID returned by Relations;
- projection authorization;
- public/REST audience policy;
- cross-source and cache privilege isolation.

This split is intentional. The Relations consumer does not perform hidden per-row WordPress capability calls because doing so inside traversal would introduce an authorization N+1 path. Query must batch and apply its canonical Data Source policy before/after the Relations read.

An `ExecutionContext` is still mandatory. Relations rejects a context whose site/network scope conflicts with its registered persistence scope.

## Storage opacity

No public response contains:

- table names;
- SQL or prepared arguments;
- concrete gateway class names;
- edge table primary keys as Query semantics;
- pivot layout;
- migration identifiers;
- lock/revision implementation details.

The integer `mutation_revision` in `describe()` is an opaque monotonic invalidation token only. Query must not assume how it is stored or advanced.

## Duplicate-capable relations

Surface 4 can persist multiple edge identities for the same source/target tuple when `unique_edge=false`.

Query-facing semantics are entity-oriented:

- related IDs are `DISTINCT`;
- batch existence returns each anchor once;
- `countRelatedObjects()` counts distinct related object IDs, not physical edge rows.

Query therefore does not accidentally change result multiplicity when Surface 4 uses duplicate-capable edge storage.

## Performance and fail-closed rules

The public adapter executes bounded SQL directly through the canonical database abstraction. It does not call unbounded `bySource()`/`byTarget()` and slice in memory.

V1 Query consumers must not:

- loop `relatedObjectIds()` once per result row;
- exceed declared batch/result limits;
- request traversal depth greater than 1;
- access Relation private storage/gateway APIs;
- infer unsupported `all`, recursive nested predicates, pivot projection, aggregate traversal, or provider-specific joins;
- bypass caller Data Source authorization.

For an existence predicate over many candidate rows, use `matchingAnchorObjectIds()` in bounded batches. Stronger future semantics require an additive contract version/capability rather than silent emulation.

## Failure model

The consumer fails closed for:

- missing/wrong-owner Relation definition;
- unpublished Relation definition;
- malformed canonical definition payload;
- unsupported traversal direction;
- reverse traversal disabled by definition policy;
- site/network scope mismatch;
- non-positive object IDs;
- oversized batches or result limits;
- malformed persistence rows/count/revision values.

Missing native Relation persistence means the public service is not registered. Query must treat that dependency as unavailable/degraded and must not fall back to private tables.

## Query integration rule

Surface 6 may depend only on `RelationQueryConsumerInterface` plus the registered public service. Query AST stores stable Relation definition identity and semantic direction; it does not store physical Relation execution details.

This contract does **not** authorize Query runtime by itself. Gate B must be explicitly reconciled/accepted and Query Gate C must satisfy its own runtime-start requirements before Query implementation begins.

## Reference evidence

`tests/Integration/relations-query-consumer-mysql.php` certifies the public seam against the real Relation schema on MySQL 8.4 and MariaDB 10.11 through the Relations Edge Persistence workflow. Unit coverage additionally proves storage opacity, bounded reads, batch existence, distinct-count semantics, scope rejection, lifecycle rejection, and reverse-direction policy enforcement.
