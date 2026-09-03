# Query — Native `wordpress.posts` Source Registration V1

Status: **IMPLEMENTED CANDIDATE — exact-head CI required before promotion**  
Surface: **6 — Query**

## Purpose

This tranche gives Query a module-lifecycle entrypoint that contributes one bounded native WordPress posts Data Source to the canonical shared Data Source Registry and exposes the already-promoted Query validation, compilation, and Policy-authorized planning services.

It does not execute a provider.

## Lifecycle

`QueryModule` implements the canonical `ModuleInterface` contract and is intended to enter the kernel through the existing contributed-module lifecycle. This tranche deliberately does not hard-wire Query into `Bootstrap/Plugin.php`.

The manifest is Pro and declares no peer hard dependencies. Relations remains an optional public-contract integration rather than a boot dependency.

During `register()` Query preflights all required shared dependencies and all Query-owned service ids before mutating the canonical registry:

- `platform.data-sources` must implement `DataSourceRegistryInterface`;
- `platform.abilities.policy` must be the canonical `PolicyEngine`;
- if `module.relations.query-consumer` is present, it must implement `RelationQueryConsumerInterface`;
- Query-owned service ids must be unoccupied;
- `wordpress.posts` must not already exist in the canonical Data Source Registry.

A failed preflight leaves the native Query source and Query-owned services unregistered.

## Canonical `wordpress.posts` descriptor

Identity and version:

- id: `wordpress.posts`
- source type: `wordpress.posts`
- capability version: `1`
- scope: `site`

Field schema:

- `post.id` — integer
- `post.title` — string
- `post.slug` — string
- `post.date` — datetime
- `post.modified` — datetime
- `post.status` — string
- `post.type` — string
- `post.author_id` — integer
- `post.parent_id` — integer
- `post.excerpt` — string
- `post.content` — string

Advertised predicate vocabulary is limited to operators represented by the promoted native posts compiler V1:

- `eq`
- `neq`
- `in`
- `not_in`
- `contains`

The descriptor advertises field sorting and offset pagination only. It does not advertise cursor pagination, aggregation, relation execution, cacheability, or diagnostics.

The compiler remains the final exact semantic gate for field/operator and sort-field combinations. A descriptor-level operator or field presence never authorizes an unsupported combination to be approximated.

## Authorization mapping

The source requires canonical Policy authorization and carries the shared Data Source authorization mapping:

- ability: `wpessential/query/execute`
- WordPress capability: `read`
- resource type: `post`

The mapping is metadata only. It grants no access itself. `QueryAuthorizedPlanner` re-resolves the source and calls the canonical `PolicyEngine` before invoking the certified compiler.

## Query-owned services

The module publishes:

- `module.query.validator` → `QueryAstValidator`
- `module.query.compiler.wordpress-posts` → `WordPressPostsQueryCompiler`
- `module.query.authorized-planner` → `QueryAuthorizedPlanner`

No private Data Source, authorization, cache, or relation registry is introduced.

## Optional Relations integration

If the canonical Relations public consumer service is available, it is supplied to `QueryAstValidator` through `RelationQueryConsumerInterface`. Its absence does not block native non-relation Query validation/planning. Relation-bearing AST remains fail-closed when the public dependency is unavailable.

This tranche performs no relation traversal execution.

## Explicit non-goals

V1 does **not**:

- instantiate or execute `WP_Query`;
- read or write the database;
- use `$wpdb`, raw SQL, query-clause filters, arbitrary PHP, callbacks, or eval;
- add cache lookup, write, invalidation, or private cache state;
- execute relation traversal;
- expose Query through REST, AJAX, admin UI, CLI, workflow, or AI;
- modify Bootstrap to force-enable the Pro module;
- claim Query Gate C completion.

Provider execution and later integrations require separate bounded tranches and their own exact-head certification.
