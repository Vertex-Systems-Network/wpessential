# Admin Columns — Bounded Read Performance / No-N+1 Evidence V1

## Scope

This tranche adds evidence only for the already-accepted Surface 8 read path.

No Admin Columns, Query, Fields, Relations, View persistence, AJAX/REST, row-mutation, export, migration, or admin UI runtime semantics are changed.

The certified read path remains:

`AdminColumnsReadAdapter -> QueryReadConsumerInterface -> Query validation/policy -> one authorized provider execution -> bounded rows`

## Unit call-count invariant

A focused unit test returns the maximum V1 page of 100 rows through a public Query consumer spy.

For one Admin Columns adapter read it requires exactly:

- one `describe()` call;
- one `read()` call.

The adapter maps the complete returned page in memory. It must not rediscover Query metadata or re-invoke Query per row or per column.

## Real WordPress evidence

The real WordPress integration test composes:

- the canonical `QueryModule` registration;
- the canonical shared PolicyEngine;
- the public Query read-consumer service;
- a published, enabled canonical Admin Columns View;
- `AdminColumnsReadAdapter`;
- the native `wordpress.posts` provider;
- real WordPress + MySQL/MariaDB.

It seeds at least 60 published posts and performs two explicit reads through the same public contract:

1. a small page of 5 rows;
2. a large bounded page of 50 rows.

Before each measured read the WordPress object cache is flushed. SQL work is measured using `$wpdb->num_queries`; setup/seed queries are outside the measured interval.

## No-N+1 acceptance rule

The evidence intentionally avoids wall-clock thresholds because runner load and database timing are not stable compatibility contracts.

Instead it requires:

- the 5-row read to perform real database work;
- the 50-row read to return exactly 50 normalized rows;
- the 50-row SQL query count to be no more than the 5-row baseline plus 4 queries;
- the 50-row SQL query count to remain at or below an absolute sanity ceiling of 12.

This leaves room for bounded WordPress internal cache-priming/query differences while rejecting a regression that adds per-row database fetches. A 45-row increase cannot hide inside the allowed constant delta.

The test also requires exactly one policy authorization per adapter read and canonical projected row keys only.

## Compatibility matrix

The existing `Query Native Execution Reference` workflow now runs the Admin Columns evidence alongside the retained Query reference across:

- WordPress 6.9 and 7.1;
- PHP 8.2, 8.3, 8.4, and 8.5 on MySQL 8.4;
- WordPress 6.9 and 7.1 on PHP 8.4 with MariaDB 10.11.

The existing Query reference remains unchanged and runs first. The Admin Columns evidence therefore extends, rather than replaces, the accepted Query provider proof.

## Security and ownership boundaries

Performance evidence does not authorize data access.

The canonical Query Policy path remains mandatory. Admin Columns still accepts only Query-readable `native`/`query` sources on this V1 read path; Fields-owned private storage remains fail-closed until its owner contract explicitly supports the required read/mutation capability.

## Non-goals

This evidence does not certify or implement:

- inline editing;
- bulk editing;
- row/source-data mutation;
- export/import;
- Fields-owned column reads or writes;
- relation expansion;
- total-count queries;
- caching changes;
- production throughput/SLA targets;
- Gate D completion or product parity.
