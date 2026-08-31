# WPEssential — Query AST v1 Candidate Schema

Status: **Phase 0 paper architecture / Proposed / no compiler or runtime implementation authorized**

## 1. Goal
Define one typed, versioned query language that can express WPEssential UI-built queries without storing raw SQL or leaking provider-specific argument arrays throughout the product.

The AST is a product contract. Provider compilers translate it into WP_Query/WP_User_Query/WP_Term_Query/Custom Table/remote-source operations where supported.

## 2. Non-negotiable rules
- no raw SQL node in normal AST;
- no arbitrary PHP callbacks;
- all values are typed parameters/literals/tokens;
- provider capability determines whether a node is supported;
- authorization/data-source policy runs before execution;
- public/runtime query budgets are stricter than admin preview;
- prepared/bound values are mandatory for custom SQL compilers;
- identifiers come from registered schemas/allowlists, not raw user strings.

Official WordPress alignment notes:
- core post/user query APIs already separate meta/tax/date predicates conceptually;
- custom SQL must use WordPress prepared-query facilities for values and safe identifier handling.

---

# 3. AST envelope

Candidate top-level fields:
- `ast_version` — semantic schema version, e.g. `1`;
- `query_id` / definition UUID reference outside portable AST optional;
- `source` — registered Data Source key/UUID;
- `operation` — `select` for v1; aggregate/count still represented through projection/aggregate nodes;
- `projection`;
- `filter`;
- `joins`/relations;
- `group_by`;
- `having`;
- `order_by`;
- `pagination`;
- `distinct`;
- `parameters`;
- `context_requirements`;
- `execution_policy`;
- `cache_policy`;
- `metadata` safe/editor-only fields.

Unknown future required nodes fail validation rather than being silently dropped.

---

# 4. Source reference

`source` resolves through Data Source Registry.

Fields:
- `source_type`: wp_posts / wp_users / wp_terms / comments / media / custom_table / relation / remote_adapter / custom_registered;
- `source_ref` stable source identifier;
- optional `entity_type` post type/taxonomy/table schema reference;
- provider capability version.

The AST never carries DB credentials or arbitrary remote endpoint secrets.

---

# 5. Projection

Projection item types:
- entity/object;
- field;
- meta field;
- relation-derived field;
- aggregate;
- computed safe expression;
- constant;
- token/context value only when explicitly permitted.

Fields per item:
- stable item ID;
- alias/key;
- source/reference;
- output type;
- nullable;
- formatter/render hint separate from query semantics;
- visibility/security classification.

`select *` is not a UI primitive for custom tables/public APIs. Entity adapters may have a provider-specific default entity projection.

---

# 6. Parameter model

Parameter sources:
- static literal;
- user input/filter;
- URL/query variable through declared allowlist;
- current user ID;
- current post/resource ID;
- route parameter;
- date/time now/site timezone derived value;
- shortcode/block attribute;
- workflow/form input;
- Ability argument;
- registered token source.

Each parameter declares:
- name;
- scalar/array/object type;
- item type for arrays;
- nullable;
- default;
- required;
- enum/allowed values;
- min/max/length;
- date/time format/timezone interpretation;
- sanitizer/normalizer identifier;
- sensitive flag;
- public exposure flag.

Parameters are values, never identifier names unless an explicit schema-validated identifier parameter type is later accepted.

---

# 7. Predicate / filter tree

Node types:
- group;
- comparison;
- null/existence;
- range;
- set membership;
- text search;
- date/time;
- taxonomy/term;
- meta;
- relation existence/count;
- capability/policy-derived predicate only through registered provider;
- provider extension node with namespaced type.

## Group
- operator `AND` / `OR`;
- children;
- optional negation only if compiler can preserve semantics clearly;
- max nesting depth policy.

## Comparison
Fields:
- left field/expression;
- operator;
- right value/parameter/expression;
- type/cast;
- case-sensitivity where provider supports;
- null semantics explicit.

Standard operator vocabulary:
- EQ / NEQ;
- GT / GTE / LT / LTE;
- IN / NOT_IN;
- BETWEEN / NOT_BETWEEN;
- CONTAINS / NOT_CONTAINS;
- STARTS_WITH / ENDS_WITH;
- LIKE-style semantics only through provider-specific safe compiler;
- EXISTS / NOT_EXISTS;
- REGEX only advanced/provider-supported and disabled for public runtime by default because cost/portability differ.

No provider silently maps an unsupported operator to a “close enough” one.

---

# 8. WordPress meta predicates

Canonical meta node fields:
- registered meta/field reference;
- key resolved from schema;
- compare operator;
- value/parameter;
- value type/cast;
- relation group.

Compiler can target WP_Meta_Query where appropriate. Multiple meta clauses need query-cost estimation because joins can multiply quickly.

Public query budgets may reject too many meta predicates/OR branches.

---

# 9. Taxonomy predicates

Fields:
- taxonomy stable reference;
- field selector: term_id / slug / name / term_taxonomy_id where provider supports;
- term values/parameter;
- include_children;
- operator IN / NOT_IN / AND / EXISTS / NOT_EXISTS as provider capabilities allow;
- nested relation groups.

Term identities use portable UUID/migration mapping in exported configuration where WPE owns a portable reference; otherwise source-local IDs are explicitly source-bound.

---

# 10. Date/time predicates

Fields:
- target date field;
- relation;
- after/before;
- inclusive;
- year/month/day/week/day_of_week/hour/minute/second only where provider supports;
- timezone interpretation: stored UTC vs site-local vs source-defined;
- relative expression via safe typed token (`now - 7 days`) only through allowlisted date arithmetic.

No raw SQL date expression.

---

# 11. Relations / joins

AST distinguishes **logical relation traversal** from physical SQL JOIN.

Relation node:
- relation definition UUID/key;
- direction parent→child / child→parent / symmetric;
- alias;
- join/requirement semantic:
  - require related;
  - optional related;
  - exclude related;
  - filter related by nested predicate;
- cardinality expectation;
- aggregate mode any/all/count;
- projection from related side if provider supports;
- max traversal depth.

Compiler decides whether implementation is:
- WP query include IDs;
- relation-table join;
- batched subquery;
- post-processing only if bounded and explicitly allowed.

No N+1 per-result relation query in normal list execution.

---

# 12. Custom table join node

Only available to registered table schemas.

Fields:
- left source/field reference;
- right source/field reference;
- join type INNER / LEFT initially;
- alias;
- nested join predicate restricted to equality/approved operations;
- cardinality hint;
- selected projection.

Table/column names are schema references, not raw input. Physical compiler uses safe identifier handling + bound values.

Cross-database joins are unsupported unless an adapter explicitly certifies them.

---

# 13. Aggregates

Candidate:
- COUNT;
- COUNT_DISTINCT;
- SUM;
- AVG;
- MIN;
- MAX.

Fields:
- source field/expression;
- alias;
- distinct;
- output type;
- null behavior.

Group-by/having only enabled on providers that can preserve correct semantics/performance.

WP_Query provider may reject aggregate ASTs rather than abusing unsupported query vars; a custom-table provider may support them directly.

---

# 14. Sorting

Order item:
- field/expression;
- direction ASC/DESC;
- null ordering only if provider supports;
- case/collation behavior declared;
- stable tie-breaker automatically required for cursor pagination.

User-selectable sort fields must come from an allowlist in Query definition; URL cannot supply arbitrary SQL column names.

---

# 15. Pagination

Modes:
- offset/page;
- cursor/keyset preferred for large stable custom-table/API lists where provider supports;
- none only for bounded internal queries.

Fields:
- page/per_page;
- cursor;
- max page size;
- total count requested yes/no;
- provider-specific count cost.

Public/API list defaults are bounded. “All results” is not a public default.

---

# 16. Search

Search node declares:
- query parameter;
- allowed fields;
- mode provider native / tokenized / prefix / exact;
- minimum length;
- maximum length;
- wildcard behavior;
- case/collation behavior;
- ranking availability;
- cost classification.

Full-text search is not assumed to exist on every provider.

---

# 17. Execution policy

Fields:
- audience: admin_preview / authenticated_runtime / public_runtime / workflow / API;
- required capability/policy;
- maximum rows;
- maximum page size;
- maximum predicate count;
- maximum relation traversal;
- timeout/budget class;
- allow expensive total count;
- allow regex;
- allow remote source;
- allow cache;
- stale-cache policy;
- PII/sensitive-field restrictions.

A Query published for admin use is not automatically safe for public shortcode/API use.

---

# 18. Cost model candidate

Static cost score inputs:
- source cardinality estimate;
- meta OR clauses;
- unindexed fields;
- wildcard leading search;
- relation traversal depth;
- joins;
- group/aggregate;
- total count;
- remote calls;
- page size;
- regex.

Result classes:
- Low;
- Moderate;
- High;
- Blocked for selected execution context.

Cost model is advisory until benchmark data calibrates thresholds.

---

# 19. Cache policy

Controls:
- off;
- request only;
- persistent TTL;
- tag/version invalidation;
- stale-while-revalidate only if correctness allows.

Cache key must include:
- Query definition/revision;
- normalized parameters;
- source generation/version where available;
- user/access context when result visibility differs;
- locale/site/network;
- pagination/sort/projection.

Never share a privileged result cache with anonymous users.

---

# 20. Explain / compile preview

Admin-only diagnostics can display:
- normalized AST;
- provider compiler chosen;
- provider arguments safe representation;
- generated prepared SQL **only when capability and provider safely expose it**;
- parameter types separately;
- estimated cost;
- indexes used/expected where provider can explain;
- unsupported/degraded nodes;
- cache key factors;
- permission context.

Never interpolate secrets into displayed SQL/logs.

---

# 21. Provider capability contract

Each Query provider declares:
- provider ID/version;
- source types;
- supported predicates/operators;
- joins/relations;
- aggregates;
- group/having;
- sorting;
- cursor pagination;
- total count;
- projection;
- explain;
- cache invalidation hints;
- max supported AST version;
- extension nodes.

Validation happens before execution; compiler never silently ignores unsupported AST nodes.

---

# 22. Versioning

AST v1 changes:
- additive optional nodes/fields can be minor schema evolution where old readers safely ignore only non-semantic metadata;
- any new semantic node unknown to reader makes definition incompatible/read-only rather than dropped;
- breaking operator semantics require new AST version/provider capability;
- portable packages include AST version and provider dependencies.

---

# 23. Security boundaries

- all runtime parameters treated untrusted;
- field/column/orderby identifiers resolved through schema registry;
- custom SQL values use prepared/bound query path;
- remote Data Sources inherit Connections SSRF/auth policies;
- projected protected fields require policy independently of row visibility;
- Query preview does not bypass row/resource authorization;
- no raw callback/eval;
- no direct DB table name free text in public query builder.

---

# 24. Future executable acceptance — NOT AUTHORIZED

After owner consent, benchmark fixtures must include:
- WP posts with meta/tax/date predicates;
- WP users with meta/role filters;
- terms;
- custom tables with indexed/unindexed fields;
- relations;
- 10k/100k/1M representative rows where relevant;
- public vs admin budgets;
- cursor vs offset;
- expensive meta OR queries;
- unsafe sort/identifier injection attempts;
- SQL injection corpus;
- permission/cache isolation;
- source/provider unsupported node behavior.

Metrics:
- query count;
- SQL/runtime duration;
- memory;
- rows examined where available;
- cache hit/miss;
- serialized payload;
- compiler overhead.

No benchmark/compiler/runtime code is authorized before ADR-0014 consent.

## Current recommendation
Adopt this AST shape as the **paper candidate** and use it to prepare provider-specific compiler acceptance matrices. Do not mark it Accepted until it has been reviewed against the exhaustive Query spec, relation model and representative provider constraints.