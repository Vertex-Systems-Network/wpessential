# WPEssential — Query P-009 Compiler, Cost, Cache & Security Benchmark Profile

Status: **Phase 0 paper benchmark profile / no compiler, SQL, cache or benchmark execution authorized**  
Date: 2026-08-28  
Related: Query AST v1, ADR-0069, ADR-0071, ADR-0074, ADR-0014.

## Purpose

Turn the accepted Query AST semantics into a bounded future evidence matrix without creating one universal SQL engine.

The Query AST remains the product contract. Each registered provider compiles only the subset it truthfully supports.

## Provider benchmark profiles

### QP1 — WordPress-native provider compilation — first baseline

Targets:
- posts/media through `WP_Query`/registered WordPress query APIs;
- users through `WP_User_Query`;
- terms through `WP_Term_Query`;
- comments only through the registered WordPress adapter.

Rules:
- prefer supported native query arguments/APIs;
- meta/tax/date/query clauses remain typed;
- unsupported aggregate/join/operator semantics fail validation rather than degrade silently;
- no raw user SQL or arbitrary identifiers.

QP1 is first because it maximizes WordPress interoperability where WordPress objects are the real data model.

### QP2 — WPE managed Custom Table compiler — mandatory comparison

Targets registered WPE-managed table schemas only.

Rules:
- table/column/order identifiers resolve from schema registry;
- values are prepared/bound;
- projection is explicit;
- typed indexes/collation/null semantics are part of provider capability;
- cursor/keyset pagination is preferred for large stable lists where ordering permits;
- aggregate/group/having support is advertised only where compiler + DB profile prove it.

QP2 is not a license to query arbitrary WordPress/plugin tables.

### QP3 — Relations-assisted/two-phase compiler — mandatory relation workload profile

Used when a Query traverses accepted Relations runtime and the provider cannot safely express the whole request as one bounded native query.

Allowed strategies:
- bounded relation prefilter to IDs followed by provider query;
- bounded provider query followed by relation batch fetch;
- certified direct join when owning physical schemas permit it.

Normal list execution may not fall into per-row N+1 relation queries.

### QP4 — Remote Data Source adapter — separate integration profile

Remote queries inherit Connections/Safe HTTP/provider certification.

Rules:
- no local AST feature is assumed remotely;
- pagination/filter/sort/aggregate semantics must map exactly or be rejected;
- remote result caching never bypasses current local Policy;
- secrets stay server-side;
- SSRF/rate-limit/unknown provider behavior remain adapter evidence.

Remote latency is not compared as if it were a local SQL implementation.

## Security gates

Every provider/profile must prove before runtime acceptance:
- parameter values cannot become unchecked SQL identifiers;
- sort/projection fields resolve through allowlisted schema references;
- prepared/bound values for custom SQL;
- Query Preview does not bypass row/resource/field Policy;
- projected sensitive fields have independent authorization;
- site/network scope is resolved server-side and included in every relevant query path;
- cross-site/network aggregation is Off unless definition + caller + provider explicitly authorize it;
- wrong-site IDs and crafted cursor/parameter values cannot cross scope;
- privileged result cache is never shared with a lower-privilege principal.

Security failure rejects a profile regardless of benchmark speed.

## Cost model evidence

Static cost score remains advisory until calibrated.

Future fixture dimensions:
- source cardinality;
- meta OR count;
- taxonomy/date clauses;
- unindexed predicates;
- leading-wildcard/text search;
- joins/relations;
- aggregate/group/having;
- requested total count;
- offset depth;
- page size;
- remote round trips;
- projection width.

Future result classes remain `Low`, `Moderate`, `High`, `Blocked`, but numeric thresholds require executed evidence.

## Execution-context budgets

Budget classes are separate:
- admin preview;
- authenticated runtime;
- public runtime;
- REST/API;
- Workflow/internal batch.

A Query accepted for admin preview is not automatically publishable to public/API execution.

Future evidence must establish independent limits for:
- maximum page size;
- predicate count/depth;
- relation traversal depth;
- total-count allowance;
- regex/search modes;
- timeout/cost score;
- remote-source use.

## Pagination benchmark

Compare where provider supports both:
- offset/page;
- cursor/keyset.

Correctness gates:
- deterministic stable tie-breaker;
- cursor cannot leak raw secret/internal state;
- pagination cannot duplicate/skip rows under the declared consistency model beyond documented source behavior;
- scope/policy factors are bound to the cursor or revalidated on use.

## Cache profile

Candidate cache modes remain:
- off;
- request-local;
- persistent TTL;
- generation/tag invalidation;
- stale-while-revalidate only for correctness-tolerant definitions.

Minimum cache identity factors:
- Query definition + immutable revision;
- provider/compiler profile version;
- normalized typed parameters;
- site/network scope;
- principal/access context when visibility differs;
- source generation/version where available;
- relation/policy generations where relevant;
- locale if semantics depend on it;
- projection/sort/pagination.

A cache entry whose authorization dependencies cannot be represented safely must not be shared persistently.

## Cache invalidation evidence

Future fixtures include:
- source row/meta update;
- relation edge add/remove;
- Membership/role/policy revoke;
- site/network setting change used by parameters;
- definition publish;
- custom-table migration generation change;
- user deletion/site deletion.

Acceptance criterion for protected data: no stale cached allow after an authorization generation change within the accepted invalidation model.

## Multisite / network aggregation

Default Query scope is one site.

Network/cross-site query requires:
- explicit network-capable Query definition;
- network authorization;
- provider capability;
- target-site Policy checks where data is site-owned;
- bounded site set/pagination;
- no unbounded synchronous loop over all sites;
- cache identity that includes aggregation scope/site-set generation.

Future large-network evidence: 100 / 1k / 10k sites, including one noisy subsite.

## Explain/diagnostics

Admin-only future diagnostics may expose:
- normalized AST;
- selected provider/compiler profile;
- safe provider arguments;
- prepared SQL template only when safe to expose;
- estimated cost;
- expected/observed index strategy where available;
- cache dependency factors;
- unsupported/degraded nodes.

Never interpolate secret values or unauthorized record data into diagnostics.

## Future P-009 fixtures — NOT AUTHORIZED

Data sizes where relevant:
- 10k;
- 100k;
- 1M rows/objects;
- high-meta and high-relation-degree cases;
- 100/1k/10k-site network cases.

Attack/correctness fixtures:
- SQL/identifier/order injection corpus;
- wrong-site ID/cursor attacks;
- privileged-cache-to-anonymous leakage attempts;
- unsupported AST node handling;
- stale policy/relation/source generation;
- deep offset vs cursor;
- concurrent writes during pagination;
- N+1 detection.

Metrics:
- DB/query count;
- runtime duration;
- memory;
- rows examined where available;
- compiler overhead;
- payload size;
- cache hit/miss/invalidation behavior;
- remote round trips;
- wrong-scope/unauthorized result count, required to remain zero.

Executed P-009 fixtures: **0**.

## Paper recommendation

Use **QP1 provider-native WordPress compilation as the first baseline**, with **QP2 Custom Table** and **QP3 Relations-assisted** profiles mandatory for the workloads they own. QP4 remains separately certified remote-provider behavior.

No universal compiler may silently translate unsupported semantics, and no performance result can override authorization, scope or cache-isolation correctness.