# Query Native WordPress Posts Compiler V1

Status: bounded Surface 6 Gate C provider-compilation slice.

## Purpose

This slice converts an already validated `QueryDefinition` for canonical source `wordpress.posts` into a finite `WP_Query` argument **plan**. It does not instantiate or execute `WP_Query` and does not read or write the database.

## Supported V1 semantics

- explicit supported post projection metadata;
- comparison predicates with exact public argument mappings for bounded core post fields;
- set membership for bounded ID/author/parent/type/status fields;
- provider-wide default text search only when it maps exactly to public `s` semantics;
- AND composition when child clauses map to non-conflicting public query arguments;
- bounded public `orderby` mappings;
- offset pagination;
- ID-only `fields => ids` optimization;
- deterministic `ignore_sticky_posts => true` and `suppress_filters => true` plan flags.

The plan retains canonical projection metadata because `WP_Query` does not provide arbitrary safe partial-row projection for every semantic field.

## Fail-closed behavior

The compiler rejects rather than approximates:

- cross-clause OR that would require SQL/query filters;
- field-scoped text search that public `WP_Query` would broaden beyond the requested field;
- generic DISTINCT;
- parameter binding;
- cursor compilation/signing;
- relation traversal;
- taxonomy/custom-field/date/provider-extension predicates not yet assigned an exact bounded mapping;
- duplicate semantic clauses that compete for one provider argument;
- malformed literal types or unchecked provider slugs;
- non-`wordpress.posts` sources.

## Security / ownership boundary

This slice MUST NOT:

- instantiate or execute `WP_Query`;
- access `$wpdb` or physical table/column identifiers;
- produce raw SQL fragments;
- register query-clause filters or PHP callbacks;
- access Relations-private persistence;
- add REST/admin execution paths;
- create a private Data Source registry or cache engine;
- claim Policy-authorized execution, result caching or Query Gate C completion.

The compiler is Query-owned provider translation only. Source identity/capability validation remains owned by the canonical Data Source seam and must happen before a validated definition reaches this compiler.

## Deferred Gate C work

Later bounded tranches must separately establish:

- Policy-authorized provider execution and normalized result/error values;
- parameter binding and sensitivity handling;
- relation predicate execution through the public Relations contract without N+1 behavior;
- safe taxonomy/field/date semantics where canonical owner mappings exist;
- cursor semantics;
- shared cache integration and diagnostics;
- performance/reference evidence;
- canonical Query admin UX and public execution exposure.
