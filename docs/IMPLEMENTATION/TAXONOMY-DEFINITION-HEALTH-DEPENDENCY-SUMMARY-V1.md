# Taxonomy Definition Runtime Health + Dependency Summary V1

Issue: #609  
Parent: #474  
Scope: read-only Surface 2 information architecture.

## Canonical read model

`TaxonomyDefinitionReadModel` is the single server-authoritative projection for Definition-list runtime health, dependency/usage counts, reverse dependents and object-type impact.

The projection is read-only. It does not mutate Definitions, roles, terms, object-type associations, rewrite rules, providers or runtime registration.

## Runtime health semantics

The list reports four bounded states:

- `healthy` — the canonical Definition is published and the taxonomy key is present in the current WordPress runtime;
- `degraded` — the canonical Definition is published but the taxonomy key is absent from the current WordPress runtime;
- `inactive` — the canonical Definition lifecycle is not published;
- `unavailable` — WordPress runtime-presence APIs are unavailable to the current process.

Runtime presence does not prove which component registered the taxonomy. No ownership is inferred from `taxonomy_exists()`.

## Dependency count semantics

The Definition-list dependency count is the number of unique canonical/reference identities across:

1. declared Definition dependency UUIDs;
2. reverse canonical dependents returned by `DefinitionRepositoryInterface::dependentsOf()`;
3. object-type associations, represented by their canonical CPT Definition UUID when resolvable, otherwise by a stable external object-type key.

A canonical CPT already present as a declared dependency is not counted again merely because the Taxonomy also associates to that CPT key.

Unresolved declared UUID dependencies remain visible as unresolved references; they are never silently dropped.

## Diagnostics

Validation diagnostics reuse the same read model for:

- association health;
- dependency/usage count;
- declared dependencies;
- reverse dependents;
- object-type dependency impact.

The browser renders these server-produced facts and does not compile a second dependency graph or runtime-health engine.

## Explicit boundary

This slice does not implement role-impact preview. Role grants and role/capability membership remain owned by Surface 30 Roles & Capabilities and require a separately reviewed read seam.

Taxonomy-key migration execution, generic package orchestration, destructive term mutation, runtime certification, product-parity certification, deployment and release remain outside this slice.
