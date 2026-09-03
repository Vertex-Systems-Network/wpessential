# Query Cache and Diagnostics Contract V1

Status: non-runtime contract for Surface 6 / Gate C. Nothing in this tranche enables Query cache reads/writes, invalidation hooks, or public diagnostics.

## Existing shared owner

Query does not own a cache engine. The canonical shared Platform Cache seam already provides `CacheKey`, `CachePolicy`, `CacheDependencies`, `CacheLookup`, and `RequestLocalCache`.

This contract therefore defines only when a future Query execution may be eligible to use that seam and which diagnostics are safe to project.

## Eligibility

`QueryCacheContract` returns a disabled decision unless all of these conditions are explicit:

1. the current Data Source descriptor exactly matches Query source id/type/capability version;
2. the Data Source advertises `cacheable=true`;
3. the Data Source declares one or more canonical generation keys;
4. the execution principal is authenticated;
5. network and site scope are explicit in the execution context;
6. an enabled cache policy has a positive TTL;
7. the canonical Query semantic payload is serializable.

The current `wordpress.posts` descriptor remains `cacheable=false`, so this contract cannot silently enable caching for it.

## Key and dependency inputs

When eligible, the contract builds existing shared value objects only. The Query semantic hash includes stable identity/revision, AST version, source/capability version, operation, projection, parameter definitions, filter tree, ordering, pagination, distinct flag, execution policy and cache policy.

The shared `CacheKey` additionally separates network id, site id, principal id and Query/source revision. `CacheDependencies` carries the Data Source-owned generation keys in normalized order.

Query metadata is deliberately excluded from result-key semantics because it is not an execution semantic. Raw SQL/provider diagnostics/credentials are not valid key inputs.

## Invalidation requirement

A future runtime cache tranche MUST NOT enable reads/writes until the owner of every cacheable Data Source provides mutation-driven generation-key invalidation. Query must consume those generation keys; it must not infer table names, hook arbitrary SQL, or invent private invalidation ownership.

A missing generation key is therefore a disabled cache decision, not a cache miss.

## Safe diagnostics

`QueryDiagnosticSnapshot` is a finite value projection containing only:

- Query key and revision;
- source reference and capability version;
- bounded page size and offset;
- whether the generated cache key is principal scoped;
- cache eligibility and a stable bounded reason code.

It intentionally excludes SQL, provider exceptions, raw parameters, metadata payloads, credentials, cache values, storage keys, stack traces and wall-clock performance measurements.

The snapshot is not registered with REST/AJAX/admin/CLI/workflow/AI surfaces in this tranche.

## Integration requirements

A later serialized Supervisor/runtime tranche must, before enabling caching or diagnostics:

1. choose the canonical Query execution interception point after Policy authorization and before provider execution;
2. certify Data Source generation-key ownership and invalidation hooks;
3. define whether request-local and persistent cache tiers share or separate policies;
4. preserve principal/network/site separation and relation/field dependency revisions;
5. define safe observability exposure and authorization for diagnostic consumers;
6. add exact-head invalidation, stale-read, authorization-boundary and real-provider evidence.

## Non-goals

No cache `get`, `put`, `delete` or generation invalidation is performed here. `QueryModule.php`, existing planner/compiler/executor classes, shared Platform Cache classes and current Data Source descriptors are unchanged.
