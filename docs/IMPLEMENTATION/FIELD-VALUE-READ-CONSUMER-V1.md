# Field Value Read Consumer V1

## Status

Gate D prerequisite / Surface 3 owner-owned read seam.

This tranche adds a bounded **read-only internal service** for projecting one certified Custom Fields scalar value across an ordered list of WordPress posts. It does not expose a new Ability, REST route, AJAX route, admin control, mutation path, Query projection, or Admin Columns `fields` source yet.

## Why this seam exists

Admin Columns already preserves source ownership in its View definition and correctly refuses to read `fields` sources through the native Query projection path. Surface 3 already owns canonical Field target resolution, value storage and `write-value` authorization, but the only shared Query-facing Field contract previously exposed predicate matching, not Field value projection.

Consumers must not reconstruct Field Group internals, post-meta keys, storage rules or authorization logic themselves. V1 therefore adds an owner-certified read seam before any Admin Columns Field source or inline/bulk edit integration.

## Contract

`WPEssential\Contracts\FieldValueReadConsumerInterface`

- contract version: `1`
- maximum post IDs per call: `100`
- input Field reference: canonical `fields.<group-uuid>.<field-uuid>`
- post IDs: non-empty, ordered, unique positive integers
- execution context: authenticated WordPress user/site/network context
- output metadata:
  - `field_ref`
  - `group_revision`
  - `field_uuid`
  - `logical_type`
  - `storage_owner`
- output rows preserve requested post-ID order and contain only `{post_id,value}`.

The V1 service is registered as:

`module.custom-fields.values.read-consumer`

It is an internal service only. No new Ability or transport exposure is registered.

## Owner and security law

The implementation composes existing Surface 3 authority:

1. canonical Field metadata is resolved through `FieldQueryConsumerInterface::describe()`;
2. supported storage remains published, top-level, native scalar Field storage;
3. WordPress post caches are preloaded in one bounded phase;
4. each post is checked through the retained `WordPressPostResourceAuthorizer::assertCanRead()` boundary **before** target resolution/value projection;
5. target resolution remains `FieldValueTargetResolver` authority;
6. value projection remains `PostMetaValueStore` authority;
7. a Field Group revision change during the bounded call fails closed;
8. one unauthorized, missing or out-of-contract target fails the complete call; partial rows are not returned.

Cache preloading is not authorization. It may warm internal WordPress object/meta caches, but no Field value is returned before the exact resource authorization succeeds.

## Performance boundary

The default implementation uses one bounded `get_posts()` preload for the requested IDs with post-meta cache population enabled before per-post authorization and owner reads.

`tests/Integration/wordpress-field-value-read-performance.php` measures real WordPress SQL query count after cache flush for 5-row and 50-row reads. The 50-row call must:

- return exactly 50 ordered Field values;
- preserve owner revision/UUID/logical type/storage metadata;
- execute no more than four SQL queries above the 5-row call;
- remain below an absolute 12-query sanity ceiling.

This is a constant-query/no-N+1 regression bound, not a latency SLA.

## Explicit non-scope

This tranche does **not**:

- make Admin Columns source owner `fields` readable;
- modify Admin Columns View schema;
- add inline or bulk editing;
- expose `wpessential/fields/write-value` through Admin Columns;
- add sorting/filtering semantics beyond existing Field Query ownership;
- add export behavior;
- add REST/AJAX/UI endpoints;
- add schema or migrations;
- expose post-meta keys or let a consumer bypass Surface 3 target/resource authorization.

## Next dependency-safe step

After this owner read seam and its real WordPress no-N+1 evidence are accepted, Query/Admin Columns may add a separate optional projection integration that consumes `FieldValueReadConsumerInterface` rather than Surface 3 internals. Only after Field source reads are owner-certified end-to-end should an edit-capability tranche map canonical Field references to the already-certified `wpessential/fields/write-value` mutation Ability.
