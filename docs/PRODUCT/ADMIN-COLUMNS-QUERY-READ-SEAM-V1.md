# Admin Columns Query Read Seam V1

## Status

Implementation candidate for Gate D / Surface 8 issue #198. This document does not declare Gate D PASS or product parity.

## Ownership

Query remains the authoritative owner of backend read planning and execution. Admin Columns must consume `QueryReadConsumerInterface` instead of importing `QueryAuthorizedExecutor`, validators, provider compilers, `WP_Query` arguments, Fields/Relations private storage, or provider callbacks.

The registered service is `module.query.read-consumer`.

## V1 request boundary

The public request is versioned and accepts only:

- `contract_version`;
- canonical `source_ref`;
- bounded semantic `projection` references;
- bounded comparison/set `filters` using canonical field references;
- one bounded text `search` value;
- bounded semantic `order_by` clauses;
- bounded `page_size` and `offset`.

Unknown properties fail closed. Raw SQL, `WP_Query`, `meta_query`, provider arguments/callbacks, mutation instructions, export instructions and private owner keys are not public request surfaces.

## Canonical execution path

The adapter converts the bounded request into an internal Query AST and then uses the existing canonical path:

1. Data Source Registry resolves source type, capability version and declared fields/capabilities.
2. `QueryAstValidator` / `QueryFieldAwareAstValidator` validates semantic references and cost bounds.
3. `QueryAuthorizedPlanner` applies canonical Policy authorization and owner-aware planning.
4. the existing provider compiler and `QueryAuthorizedExecutor` execute the authorized plan.
5. the public response contains only bounded rows/projection metadata or a normalized failure.

The public seam does not return provider plans or provider arguments.

## V1 limits

- request bytes: 16 KiB;
- projection fields: 32;
- filters: 16;
- values in one set filter: 50;
- order fields: 4;
- page size: 100 or the lower Data Source limit;
- offset: 10,000;
- search length: 200 characters.

These limits are additional consumer bounds. Canonical Query/Data Source validation may reject a request more strictly.

## Scope intentionally deferred

- Admin Columns-owned adapter/rendering composition;
- public REST/AJAX endpoints;
- write, inline/bulk mutation and export;
- direct provider/private storage access;
- arbitrary Query AST authoring;
- provider extension payloads;
- unbounded scans;
- Gate D completion/parity claim.

A later Admin Columns integration tranche may consume this service while preserving source-owner rendering/mutation contracts and Query ownership of read semantics.
