# Relations Master Options Bank — Discovery Seed V1

Snapshot: 2026-09-01

Surface: 4 — `relations`

Lifecycle decision: **`BANK_SURFACE_SEEDED`**

Record count: **135 Relations / 931 total Bank**

## Purpose

This seed establishes the discovery vocabulary for first-class persistent relations. It deliberately separates **relation storage/edge semantics** from the Fields surface's relationship selector controls.

Fields may provide a `relationship`, post-object, user, taxonomy, or other selector control. Surface 4 owns the persistent graph/edge definition: endpoint identity, cardinality, direction, storage, ordering, pivot metadata, deletion behavior, permissions, querying, API mutation and integrity.

## Research baseline

Native WordPress provides relation-like primitives but not a generic relation-definition registry:

- object ↔ taxonomy-term assignment through `wp_set_object_terms()`, including append/replace behavior;
- hierarchical post parent relationships through `post_parent`.

Current market benchmarks used for discovery:

- JetEngine Relations;
- Meta Box Relationships;
- ACF bidirectional Relationship/Post Object/User/Taxonomy fields;
- Pods Relationship fields;
- Toolset post relationships;
- ACPT relational fields.

Meta Box is especially useful for persistent-edge semantics: its documented relationship table stores connection ID, `from`, `to`, relation type, `order_from`, and `order_to`; it supports one-to-one, one-to-many, many-to-one and many-to-many relations plus REST access. ACF's bidirectional mode is intentionally treated as a compatibility/editor pattern rather than WPE's canonical graph storage model because ACF synchronizes values into fields on both objects and documents type/location/chaining constraints.

## Shard layout

| Shard | Records | Scope |
| --- | ---: | --- |
| `relations.json` | 48 | identity, endpoints, cardinality/direction, storage/native adapters, typed pivot metadata |
| `relations--lifecycle-bulk.json` | 18 | deletion/orphan lifecycle, bulk operations, editor placement |
| `relations--editor-permissions.json` | 18 | editor selection behavior, permissions, multisite |
| `relations--query-api.json` | 25 | relation queries, REST/API/Abilities/GraphQL adapters, frontend read/edit |
| `relations--portability-integrity.json` | 26 | frontend forms/listings, portability, migration, performance, integrity/observability/extensibility |
| **Total** | **135** | fully classified discovery seed |

## Boundary decisions

### Fields-owned

- relationship/post-object/user/term selector control type;
- selector presentation and generic field validation/return-format behavior;
- pivot field definitions themselves through the canonical Field Schema Registry.

### Relations-owned

- relation definition UUID/key/name;
- endpoint object type/subtype and registered entity adapters;
- one-to-one / one-to-many / many-to-one / many-to-many cardinality;
- directional labels, reciprocal/same-type behavior and traversal semantics;
- edge uniqueness/order;
- relation table/storage/index design;
- pivot metadata attachment and queryability;
- connect/disconnect/delete/orphan lifecycle;
- relation-specific permissions;
- relation query primitives;
- REST/Ability mutation exposure;
- frontend relation editing;
- import/export/migration;
- integrity, performance and observability policies.

This boundary prevents a relationship selector from becoming a second competing source of truth for stored edges.

## WPE exceed discovery

The seed deliberately captures future opportunities without presenting them as current/shipped features:

- cycle prevention and orphan repair;
- definition revisions and impact previews;
- relation change auditing;
- relation-aware Query AST;
- schema-derived Abilities;
- ID-remap-aware migrations with preview and rollback;
- query/edge budgets and index health;
- transactional mutations and double-writer guards;
- reciprocal consistency and edge health diagnostics;
- slow-query and count observability.

All such records are `WPE_EXCEED` + `WPE_FUTURE` + `P1_EXCEED`.

## What this seed does not certify

`BANK_SURFACE_SEEDED` does **not** mean:

- native WordPress relation-like APIs have been exhaustively disposed one-by-one;
- every current competitor/provider family has a zero-gap capability matrix;
- semantic overlaps across later discovery waves have been canonicalized;
- the relation runtime/storage implementation exists;
- migration or production behavior is certified.

Those are subsequent `NATIVE_AUDITED`, `MARKET_AUDITED`, and `BANK_REVIEWED` gates.

## Next gates

1. Native audit: enumerate relevant WordPress object-term and hierarchical-parent read/write/delete/query/lifecycle APIs, then classify relation ownership vs out-of-surface behavior.
2. Market audit: build provider-by-provider capability coverage for JetEngine, Meta Box, ACF, Pods, Toolset, ACPT and relevant specialists.
3. Add only genuinely missing records found by those audits.
4. Canonicalize semantic overlaps if discovery waves create duplicates.
5. Run the formal whole-surface Bank review only after native and market gates are independently certified.
