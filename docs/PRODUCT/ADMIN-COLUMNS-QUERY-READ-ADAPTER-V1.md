# Admin Columns Query Read Adapter V1

## Status

Implementation candidate for Gate D / Surface 8 issue #200. This does not declare Gate D PASS or product parity.

## Ownership

Admin Columns owns View definitions and rendering-oriented column keys. Query remains the owner of backend read validation, authorization, sorting, filtering, searching, pagination and provider execution.

The Admin Columns service is `module.admin-columns.read-adapter`. It consumes only the public `QueryReadConsumerInterface` service `module.query.read-consumer`; it does not import Query planner, validator, executor, compiler, provider plans or private source-owner storage.

## V1 supported runtime boundary

V1 deliberately supports only:

- published and enabled Admin Columns Views;
- `post_type` targets;
- enabled columns whose source owner is `native` or `query` and whose semantic reference is declared by `wordpress.posts`;
- filter/order inputs expressed by configured Admin Columns `column_key` values;
- bounded search, page size and offset delegated to the public Query contract;
- rendering rows keyed by configured Admin Columns column keys.

The adapter always adds the configured View post-type target as a canonical Query filter. Caller input cannot select another source type or provider.

## Fail-closed boundaries

The adapter rejects rather than approximates:

- draft/disabled Views;
- non-`post_type` targets;
- `fields`, `relations`, `provider`, `renderer`, taxonomy/media/status or other source owners not yet available through the accepted V1 read path;
- source references absent from Query Data Source metadata;
- filter/sort references to columns not present in the active View;
- arbitrary Query AST, raw SQL, `WP_Query`, `meta_query`, provider arguments/callbacks or private storage keys.

A later owner-specific read-projection tranche may add supported Field/Relation sources through their public owner contracts. This tranche must not inspect their private storage.

## Authorization and visibility

View visibility is presentation metadata only. The adapter does not turn visibility, assignment, View identity, column identity or source reference into authorization. The `ExecutionContext` is passed through the public Query contract and canonical Query Policy remains authoritative for data access.

## No write/export surface

This tranche exposes no inline edit, bulk mutation, export, persistence/migration, REST/AJAX route or public execution endpoint. Source-owner mutation validation remains deferred to a later separately certified tranche.
