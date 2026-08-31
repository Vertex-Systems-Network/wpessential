# WPEssential — Relation Runtime Storage Alternatives

Status: **Phase 0 paper architecture / Proposed / no tables or migrations authorized**

## 1. Goal
Select a durable relation storage model that can support WordPress objects, WPE Custom Tables and future registered Data Sources without abusing post meta, while preserving cardinality, ordering, pivot metadata, queryability, integrity and migration.

---

# 2. Relation definition vs runtime edges

Definition Repository stores relation configuration:
- relation UUID/key/name;
- endpoint A/B types/source schemas;
- cardinality;
- direction/symmetry;
- ownership;
- delete policy;
- ordering/pivot field schema;
- access policy;
- migration version.

Runtime relation storage stores actual edges/links between records.

Never store millions of runtime edges as Definition JSON revisions.

---

# 3. Candidate A — one universal typed edge table

Logical row:
- numeric ID;
- edge UUID optional;
- relation UUID;
- left/source entity type;
- left source key;
- left entity ID/reference;
- right/source entity type;
- right source key;
- right entity ID/reference;
- sort/order fields;
- edge state;
- created/updated;
- optional pivot payload reference/version.

Pros:
- one query surface;
- easier generic API/query integration;
- portable relation IDs;
- supports heterogeneous endpoints.

Cons:
- polymorphic ID typing/index design hard;
- very large table across all relation domains;
- integrity cannot use normal DB FKs across arbitrary sources;
- indexes must serve many patterns;
- pivot metadata complicates row width.

---

# 4. Candidate B — per-relation table

Each relation gets its own physical edge table.

Pros:
- tight indexes/cardinality constraints;
- simple endpoint columns;
- relation-specific pivot fields possible;
- potentially strong performance for high-volume relations.

Cons:
- many DDL operations/tables;
- migration/update complexity;
- WordPress hosting compatibility/support burden;
- harder generic tooling/backup/export;
- table-name lifecycle/security complexity;
- relation definition edits may require DDL.

Not preferred as default without benchmark proving substantial need.

---

# 5. Candidate C — endpoint-owner native/meta storage

Examples:
- post meta array of related IDs;
- taxonomy relationships;
- user meta;
- custom-table FK-like column.

Pros:
- familiar/simple for very narrow use cases;
- some WordPress APIs native.

Cons:
- inconsistent across endpoint types;
- reverse lookup poor without duplicate storage/indexing;
- cardinality/order/pivot hard;
- serialized arrays are weak query model;
- cross-source semantics fragment;
- relation API cannot guarantee one integrity model.

Rejected as universal WPE relation architecture, though adapters may expose existing native relationships read-only or synchronize certified relationships.

---

# 6. Current paper preference

Prefer **Candidate A: one typed relation-edge service/table family**, potentially split by scale later only with evidence.

Potential table family:
- edge identity/current table;
- pivot value table only if typed pivot schema cannot be efficiently represented inline;
- optional history/audit kept outside hot current edge table.

Do not accept exact DDL/index types before benchmark.

---

# 7. Endpoint identity model

Every endpoint reference must resolve through Data Source Registry.

Logical endpoint tuple:
- `source_type`;
- `source_ref`;
- `entity_type` optional;
- `entity_id` typed/local reference;
- optional stable UUID when source supports it.

For WordPress built-ins:
- post/media/comment/user/term IDs are numeric local identities;
- exported relation packages map entities separately; local numeric IDs are not assumed portable.

For Custom Tables:
- schema declares primary key type;
- relation compiler validates endpoint key type.

Arbitrary string/object references are not accepted without registered source schema.

---

# 8. Direction and canonical storage

Relation definition declares:
- directed;
- undirected/symmetric;
- hierarchical parent-child semantic if applicable.

For undirected relation candidate:
- normalize endpoint ordering deterministically so A↔B duplicate cannot be inserted in reverse;
- UI still renders semantic labels for either side.

For directed relation:
- left/source and right/target are preserved.

---

# 9. Cardinality

Supported semantic cardinalities:
- one-to-one;
- one-to-many;
- many-to-one;
- many-to-many.

Cardinality must be enforced at write time under concurrency.

Potential enforcement:
- composite unique indexes where physical schema can express rule;
- transaction + lock/recheck for relation-specific constraints;
- optimistic conflict result;
- no UI-only enforcement.

Examples:
- one-to-one requires uniqueness on both endpoint sides for a relation;
- one-to-many requires unique child/right endpoint per relation;
- many-to-many only unique exact pair.

Exact unique-index strategy is benchmark/DDL blocker.

---

# 10. Edge uniqueness / idempotency

Candidate unique identity:
- relation UUID + normalized endpoint tuple A + endpoint tuple B.

Create behavior:
- duplicate create is idempotent success or conflict according API option;
- bulk attach dedupes before write;
- concurrent duplicate requests must not create duplicate edges.

If pivot data differs on duplicate:
- caller explicitly chooses update-pivot / conflict / ignore;
- never silently overwrite.

---

# 11. Pivot metadata

Relation can optionally define typed edge fields such as:
- role/type;
- quantity;
- start/end date;
- rank/order;
- status;
- notes;
- numeric attributes.

Pivot schema reuses Field Schema types where storage-efficient/safe.

Storage alternatives:
A. selected normalized common columns + JSON payload;
B. generic pivot value table;
C. per-relation physical columns/table.

Current paper preference: **avoid per-relation DDL by default**; benchmark a typed versioned payload + only proven indexed pivot fields normalized.

Pivot fields marked queryable/sortable may require dedicated indexes/materialization; UI must distinguish “stored” from “efficiently queryable.”

---

# 12. Ordering

Ordering modes:
- unordered;
- manual per parent/left endpoint;
- manual per right endpoint if relation semantics require;
- computed by related entity field at query time;
- pivot field order.

Manual ordering candidate field:
- integer/decimal position token;
- reordering algorithm must avoid rewriting thousands of siblings per drag if scale requires fractional/rank keys;
- exact implementation benchmark later.

Concurrent reorder must preserve deterministic result.

---

# 13. Delete policies

Per endpoint side:
- restrict deletion while related;
- detach edges only — default safe candidate;
- cascade related entity delete only for explicitly owned child relationship and dedicated capability;
- set-null semantic only when relation backed by nullable native field adapter;
- archive relation edges/history.

A generic relation cannot delete third-party objects merely because an edge is removed.

Before entity delete:
- relation owner hook evaluates relevant edges/policies;
- impact preview for admin destructive operation;
- bulk delete bounded/chunked.

---

# 14. Orphan handling

External plugins/core can delete entities outside WPE relation service.

Required tools:
- lazy validation on read where appropriate;
- scheduled orphan scan/repair;
- relation health counts;
- purge orphan edge;
- remap endpoint advanced migration flow;
- retain tombstone/history if audit requires.

Never surface deleted private entity data from stale pivot cache.

---

# 15. Transactions

Write unit may include:
- validate endpoint existence/access;
- cardinality lock/recheck;
- insert/update/delete edge;
- pivot update;
- relation generation increment;
- audit/domain event metadata;
- commit;
- cache invalidation;
- emit event.

If underlying source cannot participate in same DB transaction (remote source):
- relation either cannot be writable in v1 or uses explicit eventual-consistency adapter semantics;
- do not pretend cross-system atomicity.

---

# 16. Query patterns / indexes

Hot patterns:
- all related B for one A + relation;
- all related A for one B + relation;
- existence exact pair;
- count related;
- relation membership for list filters;
- sorted related list;
- relation-based Query Builder predicate;
- cleanup all edges for deleted entity;
- relation admin browse by endpoint/type.

Candidate composite index families:
- relation + left endpoint tuple;
- relation + right endpoint tuple;
- relation + normalized pair unique;
- relation + left + order;
- relation + right + order;
- source/entity cleanup indexes.

Exact endpoint column widths/types must follow source schemas and benchmark.

---

# 17. Reverse lookup

Reverse lookup is first-class and cannot depend on scanning serialized metadata.

Every edge stored once should support both directions through indexes.

API/Ability semantics:
- relation attach/detach;
- related list with direction;
- related count;
- existence;
- pivot get/update;
- explain relation policy.

---

# 18. Access policy

Attach/detach requires:
1. relation operation capability;
2. access to both endpoint resources where policy requires;
3. relation definition policy;
4. cardinality/business constraints.

Read/list relation must not leak existence of inaccessible related entity.

Possible result policy:
- omit inaccessible related entries;
- deny whole relation list when definition demands atomic privacy;
- show count only when count itself is permitted.

Query Builder relation filters need policy-aware behavior for frontend/public contexts.

---

# 19. Caching / generations

Cache scopes:
- request-local related lists;
- optional persistent edge list/count;
- compiled relation definition.

Key factors:
- relation definition revision/generation;
- endpoint identity;
- direction;
- pagination/order;
- access context when results differ by user.

Attach/detach/pivot/order changes invalidate relation generation.

No stale allow/related-resource leak after access policy change.

---

# 20. Import/export

Configuration export:
- relation definition UUID/schema only.

Runtime data migration/export:
- edge endpoints use source-specific external/stable identity mapping;
- numeric local IDs are remapped;
- missing endpoint → conflict/unresolved, never guessed;
- pivot values type-validated;
- duplicates/cardinality conflicts previewed;
- ordering preserved if target supports.

---

# 21. History/audit

Generic Audit records attach/detach/pivot changes with safe before/after summary.

Dedicated edge history table is not required by default unless product use case demands temporal relation reconstruction. Avoid doubling hot-edge storage for history without evidence.

For compliance-critical relation domains, SDK/module may register stronger history policy.

---

# 22. Multisite

Default:
- relation endpoints site-local;
- cross-site/network relations not assumed.

Future cross-site mode needs:
- blog/site ID in endpoint identity;
- network authorization;
- user/term/post identity semantics;
- migration/domain mapping;
- cache separation.

Do not accidentally relate identical numeric post IDs from different subsites.

---

# 23. Failure states

- endpoint missing;
- endpoint unauthorized;
- source adapter unavailable;
- cardinality conflict;
- duplicate edge;
- pivot validation failure;
- relation definition unpublished/missing;
- source type changed;
- orphaned edge;
- transaction deadlock/retryable;
- relation generation/cache invalidation failure;
- remote eventual-consistency unsupported.

Every failure is structured; partial edge/pivot writes are not success.

---

# 24. Future executable benchmark — NOT AUTHORIZED

Fixtures:
- 100k / 1M / 10M edges depending practical environment;
- one-to-one and many-to-many;
- high-degree endpoint 10k+ relations;
- reverse lookups;
- sorted pivot;
- concurrent attach same endpoint;
- concurrent one-to-one conflict;
- bulk detach/entity delete;
- Query Builder relation filter;
- orphan scan;
- multisite isolation fixture if supported.

Compare:
- universal typed edge table;
- per-relation table for high-volume benchmark;
- native/meta baseline only as evidence.

Measure:
- index size;
- insert/update/delete throughput;
- p50/p95 lookup latency;
- query plans/rows examined;
- lock contention;
- cleanup cost;
- storage size.

No DDL/table/benchmark code may be created or run before explicit owner consent.

## Current recommendation
Use the **universal typed edge-table family** as the paper default, with per-relation physical tables reserved for evidence-backed exceptional scale cases. Exact schema/indexes remain Proposed pending authorized benchmark.