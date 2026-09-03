# Query Native Execution Reference V1

Status: evidence tranche for Surface 6 / Gate C. This document does not add runtime capability.

## Certified source boundary

This evidence targets the already-promoted `wordpress.posts` execution path from PR #147, corrective PR #148, and the later bounded Relations integration from PR #154/#156 without modifying Query production PHP.

The reference enters through the canonical Query AST validator, obtains a typed `QueryDefinition`, passes through `QueryAuthorizedPlanner`, and reaches `WordPressPostsQueryExecutor` only after canonical Policy authorization.

## Deterministic bounds proved

- Query validation and the native executor both reject page sizes above 100.
- A valid execution invokes the provider exactly once.
- Provider plans keep `ignore_sticky_posts=true` and `suppress_filters=true`.
- Offset pagination is passed to public `WP_Query` arguments without an unbounded fallback.
- ID-only projection uses native `fields=ids`; full projection uses normalized post rows.
- Result rows contain only declared projection keys.
- Total-count/aggregation semantics remain absent from execution V1.
- Provider exceptions are normalized to stable Query errors without SQL/provider diagnostic leakage.

## Real WordPress reference matrix

`.github/workflows/query-native-execution-reference.yml` executes `tests/Integration/wordpress-query-native-execution-reference.php` against:

- WordPress 6.9 and 7.1 on PHP 8.2, 8.3, 8.4 and 8.5 with MySQL 8.4;
- WordPress 6.9 and 7.1 on PHP 8.4 with MariaDB 10.11.

Each job checks out the exact PR head before installing dependencies or the WordPress fixture.

## Real-provider cases

The reference creates published and Draft posts and proves:

1. status filtering plus deterministic ID ordering and offset pagination on normalized full rows;
2. `posts.default` text search on an ID-only projection at the maximum certified page size;
3. Draft exclusion by the native query behavior used by the certified plan;
4. stable returned-row count without exposing `found_posts`/total-count semantics;
5. Policy-before-provider ordering for both successful and failing provider paths;
6. validation blocks a page size of 101 before any provider call;
7. provider exception details containing SQL-like data do not escape the normalized error boundary.

## Non-goals

This tranche does not add cache behavior, relation semantics, parameter binding, cursor pagination, aggregation/total count, public/admin execution, or any new Query provider argument. It is evidence for existing bounded behavior only.
