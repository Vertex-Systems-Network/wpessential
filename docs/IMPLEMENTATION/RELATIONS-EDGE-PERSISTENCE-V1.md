# Relations Edge Persistence V1

Issue: #108  
Parent: #66 Gate B

## Boundary

This slice establishes the durable Surface 4 edge persistence foundation only. It does **not** expose connect, disconnect, bulk mutation, Query integration, admin editing, pivot values, ordering, cascade execution, import/export, provider adapters, Columns, Listings, or Status.

The low-level gateway is an internal module service. A later Relation mutation service must validate a Published canonical Relation Definition, endpoint authorization, endpoint existence, cardinality, direction policy, and request policy before calling these primitives.

## Storage ownership

Surface 4 owns two network-prefixed InnoDB tables:

- `wpe_relation_edges`: immutable edge identity plus relation/source/target identity and timestamps.
- `wpe_relation_edge_state`: one mutation revision row per scoped Relation Definition.

Every primary key, unique key, read, write, and mutation-state lookup includes explicit `network_id` + `site_id`. Site scope uses a positive site id; network scope is represented explicitly as site id `0` and is not implicitly mixed with site data.

The durable unique tuple is:

`network_id + site_id + relation_definition_id + from_object_id + to_object_id`

This rejects duplicate logical edges even when a caller supplies a different Edge UUID.

## Migration contract

Migration `010.create-relation-edge-persistence`, sequence `100`, is non-destructive and is contributed through the certified shared `MigrationCoordinator` service. Relations does not issue lazy `CREATE TABLE` statements from request handlers and does not add its migration to the default Free bootstrap.

On native MySQL/MariaDB persistence, an admitted Relations module registers the migration during module registration and applies pending migrations during module boot. In-memory/SQLite fallback preserves the already-certified Relation Definition lifecycle without exposing the durable edge gateway.

## Mutation/recovery contract

`WpdbRelationEdgeGateway` serializes mutations per scoped Relation Definition by:

1. opening a database transaction;
2. creating the scoped mutation-state row if absent;
3. locking that row with `SELECT ... FOR UPDATE`;
4. exposing the locked `mutation_revision` to the caller;
5. permitting edge insert/delete primitives only while the matching Relation Definition lock is held;
6. advancing mutation revision with a scoped compare-and-swap update;
7. committing only after the revision update succeeds.

Database/clock/write/revision failures automatically roll back and release local mutation state. If rollback itself cannot be confirmed, the gateway raises an explicit uncertain-recovery failure instead of pretending the mutation is safe.

Explicit rollback discards uncommitted edge writes without advancing mutation revision.

## Read contract

Reads are scope-bound and deterministic:

- edge UUID lookup;
- relation + source object lookup;
- relation + target object lookup;
- current scoped mutation revision.

Source/target collections order by `created_at ASC, edge_id ASC`. Persisted rows are canonically hydrated and malformed UUID/object-id/timestamp state fails closed.

## Evidence

Focused unit evidence covers:

- migration ID/sequence and DDL shape;
- network/site columns, unique tuple and source/target indexes;
- migration failure behavior;
- matching open-transaction requirement for writes;
- scoped insert and revision advancement;
- automatic rollback on failed insert;
- stale completion rollback;
- deterministic scoped hydration;
- malformed persisted-row rejection;
- admitted module migration registration/boot application;
- fallback mode retaining Definition behavior without durable edge gateway.

`tests/Integration/relations-edge-persistence-mysql.php` exercises the real migration/gateway against InnoDB, including committed round-trip, source/target reads, site/network isolation, durable duplicate rejection, automatic rollback, explicit rollback, deletion, and monotonic mutation revision.

`.github/workflows/relations-edge-persistence.yml` runs that integration on:

- MySQL 8.4 / PHP 8.2;
- MySQL 8.4 / PHP 8.5;
- MariaDB 10.11 / PHP 8.4.

Exact-head CI remains authoritative. Gate B remains open after this slice.
