# WPEssential Surface 6 — Query Implementation Contract V1

Status: **implementation-ready planning contract / runtime NOT authorized**  
Bank basis: **166 BANK_REVIEWED candidate records**  
Base: `0415b2067c3882841c1359753dd34adcd0602543`

## 1. Contract principle

Query is a typed semantic layer. A saved Query definition describes *what* data is requested. A registered provider decides whether and how it can execute those semantics.

There is no universal raw-SQL escape hatch and no rule that an unsupported node may be ignored or approximated.

## 2. AST envelope

Required semantic envelope:

```text
QueryDefinition
  identity { uuid, key, name, revision, lifecycle }
  ast_version
  source { source_ref, source_type, capability_version }
  operation = select
  projection[]
  parameters{}
  filter?
  order_by[]
  pagination
  distinct?
  execution_policy
  cache_policy
  metadata?
```

Future semantic fields unknown to a reader fail closed/read-only. Non-semantic editor metadata may evolve additively.

## 3. Sources

Canonical source classes:
- WordPress posts/media;
- users;
- terms;
- comments;
- registered WPE custom-table source;
- registered provider source;
- remote Data Source adapter;
- Relations-assisted strategy.

`source_ref` resolves through the canonical Data Source/owner registry. Query cannot carry credentials, arbitrary endpoints, table names or unchecked class/function names.

## 4. Parameters and contextual values

Allowed value sources:
- typed literal;
- user/filter input;
- allowlisted URL/request value;
- current principal/user;
- current resource/post;
- route parameter;
- site-time derived value;
- Ability argument;
- registered token/context provider.

Every runtime parameter declares type, nullability, cardinality, optional bounds/enum, normalization and sensitivity.

Values never become SQL identifiers. Any identifier-like reference must resolve from a registered schema/capability descriptor.

## 5. Predicate grammar

```text
Predicate :=
    Group(AND|OR, children[])
  | Comparison(field_ref, operator, value)
  | Existence(field_ref, EXISTS|NOT_EXISTS)
  | Range(field_ref, lower?, upper?, inclusive)
  | SetMembership(field_ref, IN|NOT_IN, values[])
  | Text(field_ref/search_scope, mode, value)
  | Taxonomy(taxonomy_ref, field, operator, terms, include_children)
  | DateTime(field_ref, typed date constraint)
  | FieldPredicate(surface3_field_ref, operator, value)
  | RelationPredicate(surface4_relation_ref, ...)
  | ProviderExtension(namespaced_type, validated_payload)
```

Groups are explicitly nested. Maximum depth and total predicate count are execution-policy limits.

No provider may translate an unsupported operator into a merely similar operator.

## 6. Operator compatibility

Base scalar families:
- equality: `EQ`, `NEQ`;
- ordered scalar: `GT`, `GTE`, `LT`, `LTE`;
- set: `IN`, `NOT_IN`;
- range: `BETWEEN`, `NOT_BETWEEN`;
- text: `CONTAINS`, `NOT_CONTAINS`, `STARTS_WITH`, `ENDS_WITH`;
- existence: `EXISTS`, `NOT_EXISTS`.

Regex is provider-gated, expert-only and disabled for public contexts by default.

Compatibility is the intersection of:
1. logical value type;
2. source/provider capability;
3. field/source policy;
4. execution-context budget.

Fields-owned secret/reference-only types do not become generically searchable.

## 7. Field predicates — Surface 3 dependency

A field predicate stores a stable Fields-owned reference, never an assumed meta key.

At validation:
1. resolve the field through Surface 3/public Data Source schema;
2. obtain logical type/query capability;
3. validate operator/value type;
4. apply field-level authorization;
5. ask provider for supported compilation.

If Fields is unavailable or the field is removed/incompatible, the definition is degraded/invalid for execution and remains inspectable.

## 8. Relation predicates — Surface 4 Gate B dependency

**RELATIONS-DEPENDENT — DO NOT IMPLEMENT UNTIL GATE B PUBLIC CONTRACT EXISTS.**

Candidate semantics:
- stable relation definition reference;
- traversal direction;
- require/optional/exclude related;
- nested related predicate;
- `any`, `all`, `count`;
- bounded max traversal depth;
- optional related projection where provider certifies it.

Execution strategy is not part of the portable AST. A future provider may use bounded prefilter IDs, batching, or a certified join. Per-result N+1 traversal is prohibited.

The contract intentionally does not freeze physical table names, pivot layout, revision token shape or private Relations classes.

## 9. Taxonomy and status

Taxonomy predicates use taxonomy-owner identity and provider-supported term selectors/operators.

Status predicates consume owner/source-defined statuses and visibility semantics. Query can filter status; it cannot create statuses or perform transitions.

## 10. Search ownership

`search` in Query means structured source-native matching over an allowlisted field scope.

Surface 34 Search owns:
- dedicated indexes;
- relevance/ranking models;
- synonyms/stemming;
- search analytics/index maintenance;
- specialized retrieval engines.

A Search provider may register as a Data Source/query capability without transferring index ownership to Query.

## 11. Sorting

`order_by` is an ordered list.

Each item:
- stable field/expression reference;
- `ASC` or `DESC`;
- provider-declared null/collation behavior if relevant.

Runtime-selected sort choices are allowlisted by the Query definition. Cursor pagination requires deterministic ordering and a stable tie-breaker.

## 12. Pagination

Baseline:
- page/offset for native WordPress providers;
- cursor/keyset only where a provider certifies it;
- `none` only for explicitly bounded internal workloads.

Planning defaults inherit the common contract:
- public default page size 20;
- generic public maximum 100;
- admin preview 25.

Providers may impose stricter limits. Deep offset may be rejected/diagnosed for large sources.

Public unbounded `all results` is prohibited.

## 13. Aggregation

Provider-gated operators:
- `COUNT`;
- `COUNT_DISTINCT`;
- `SUM`;
- `AVG`;
- `MIN`;
- `MAX`;
- optional `GROUP BY` / `HAVING`.

A WordPress-native provider may reject aggregate AST rather than fabricate unsupported behavior. Registered custom-table providers may advertise stronger aggregate capability.

## 14. Data Source adapter contract

Query consumes, but does not own, a registered adapter descriptor that exposes:
- source identity/type/version;
- field/projection schema;
- supported predicates/operators;
- sorting and multi-sort;
- pagination/cursor model;
- count capability/cost;
- aggregation;
- relation integration capability;
- authorization mapping;
- scope rules;
- rate/complexity limits;
- cache/invalidation hints;
- diagnostics/explain capability;
- normalized error behavior.

Execution requires a compatible capability version. Missing/older optional provider causes degraded state, not silent AST loss.

## 15. Policy boundary

Policy authorization precedes execution and is re-applied where necessary to:
- source access;
- row/resource scope;
- projection/field visibility;
- relation traversal;
- cross-site/network scope;
- diagnostic detail;
- public/REST/Ability exposure.

A successful admin preview never implies public authorization.

UI visibility is not authorization.

## 16. Multisite

Default scope: current site.

Cross-site/network execution requires all of:
- explicit definition scope;
- network authorization;
- provider capability;
- bounded site set;
- target-site policy where needed;
- cache identity including scope/site-set generation.

No unbounded synchronous loop over all sites.

## 17. Cache contract

Query declares:
- off;
- request-local;
- persistent TTL;
- generation/tag invalidation;
- stale-while-revalidate only for correctness-tolerant definitions.

The shared cache engine is canonical.

Minimum identity factors when applicable:
- Query UUID + immutable revision;
- normalized parameter values;
- provider/capability version;
- site/network scope;
- principal/access context;
- source generation;
- relation/policy generation;
- locale;
- projection/sort/pagination.

Never share privileged results into a lower-privilege cache context.

## 18. Diagnostics and admin preview

Authorized diagnostics may expose:
- normalized AST;
- selected provider/capability profile;
- safe provider argument summary;
- cost class;
- cache dependency factors;
- unsupported/degraded nodes;
- correlation ID.

Prepared SQL templates may be shown only when a provider safely exposes them and the caller has diagnostics permission. Secrets and unauthorized values are always redacted.

## 19. Performance protections

Validation/planning MUST support:
- max AST bytes;
- max group depth;
- max predicate count;
- max IN-list size;
- max page size;
- max relation depth;
- maximum aggregate/group complexity by provider;
- deep-offset warning/block policy;
- total-count cost gating;
- timeout/budget class;
- remote round-trip limit;
- no-N+1 requirement.

Numeric production thresholds beyond inherited generic defaults require P-009 executed benchmark evidence.

## 20. Error model

Use shared stable error taxonomy. Query-specific candidates:
- `wpe_query_invalid_ast`;
- `wpe_query_unknown_source`;
- `wpe_query_unsupported_operator`;
- `wpe_query_type_mismatch`;
- `wpe_query_dependency_unavailable`;
- `wpe_query_policy_denied`;
- `wpe_query_cost_blocked`;
- `wpe_query_cursor_invalid`;
- `wpe_query_scope_invalid`;
- `wpe_query_provider_failed`.

Errors preserve category, retryability, safe field/path information and correlation ID. Production responses never expose raw SQL, secrets or stack traces.

## 21. Security MUST-NOT rules

Query MUST NOT:
- accept arbitrary authored SQL;
- accept arbitrary PHP/callbacks/eval;
- accept unchecked table/column/order identifiers;
- bypass Data Source or Policy authorization;
- assume private Relations/Fields storage;
- expose secrets through parameters, diagnostics or cache;
- silently drop unsupported clauses;
- issue unbounded public queries;
- perform per-row N+1 relation hydration;
- treat cached authorization as permanently valid;
- let AI invoke a privileged execution path.

## 22. Portability and migration

Export:
- AST version;
- stable Query identity/revision;
- stable dependency references;
- provider requirements/capability versions;
- no credentials/secrets/runtime cache.

Import:
- inspect and validate first;
- map stable dependencies;
- preserve unknown future semantic AST as read-only/incompatible;
- report missing optional provider as degraded;
- block missing hard dependencies;
- never rewrite to local numeric IDs without explicit mapping.

## 23. Graceful degraded behavior

Examples:
- optional provider missing: inspect/export allowed, execution disabled;
- Fields reference missing: identify exact field dependency;
- Relations runtime not ready: relation-bearing Query remains planning/degraded; non-relation runtime must still wait for overall Query runtime authorization in this phase;
- source does not support aggregate/cursor/operator: validation error with supported alternatives, never approximation;
- Search provider absent: source-native Query search remains separate if supported; indexed Search features unavailable.

## 24. REST / Ability exposure

Future REST and Ability handlers are adapters over the same Query service, never alternative engines.

They must preserve:
- schema validation;
- capability/policy;
- scope;
- budgets;
- pagination caps;
- error taxonomy;
- cache isolation;
- audit/correlation context.

Global registration is integrator-owned.

## 25. Safe AI invocation

AI can:
- draft a new typed Query AST;
- explain validation failures;
- suggest supported operators/source changes.

AI cannot:
- write/execute raw SQL;
- choose hidden identifiers;
- elevate audience or capability;
- reveal secret/provider data;
- bypass approval for high-impact changes.

AI output is untrusted input and traverses the same validator as human-authored definitions.

## 26. Test matrix before runtime certification

### AST/validation
- valid minimal query;
- unknown AST version;
- unknown semantic node;
- malformed types;
- unsupported operator;
- max-depth/predicate/IN-list limits;
- identifier injection corpus;
- raw SQL/PHP rejection.

### Native providers
- posts: IDs/meta/tax/date/search/status/order/pagination;
- users: role/meta/search/blog scope;
- terms: taxonomy/meta/hierarchy/object IDs;
- comments: IDs/meta/status/search/order/page/offset.

### Fields
- each supported logical type/operator family;
- deleted/degraded field;
- repeatable/structured field capability;
- secret field rejection.

### Relations — after Gate B
- each cardinality/direction;
- exists/any/all/count;
- nested related filter;
- no-N+1;
- wrong-site/cross-scope denial;
- relation invalidation.

### Pagination
- stable multi-sort;
- duplicate sort values + tie-breaker;
- page/offset;
- cursor tamper;
- deep offset;
- concurrent change semantics.

### Policy/security
- anonymous/authenticated/admin;
- row denial;
- projection denial;
- REST/Ability parity;
- cache privilege leak attempt;
- cross-site crafted IDs.

### Performance
- 10k/100k/1M representative rows where applicable;
- high meta OR;
- large IN;
- relation fanout;
- aggregate/group;
- total count;
- remote round trips;
- query-count/N+1 detector.

### Portability/degraded state
- import/export round trip;
- dependency remap;
- missing optional provider;
- missing hard dependency;
- future AST version;
- clone/new identity.

## 27. Runtime start gate

Runtime starts only after:
1. Query Bank integration is coordinator-promoted;
2. Relations Gate B public Query-consumer contract is accepted;
3. any required shared Data Source/Policy/cache seams are confirmed;
4. explicit development consent covers the Query runtime tranche;
5. implementation work is scoped into reviewable slices.

This document is not runtime code and does not certify product parity, performance, accessibility runtime behavior, deployment or release.
