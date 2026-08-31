# ADR-0008 — Versioned Definition Storage

Status: **Proposed — Phase 0 blocker**  
Date: 2026-08-27

## Context

WPEssential builders create many configuration objects: CPTs, taxonomies, field groups, relations, queries, forms, workflows, columns, listings, dashboards, email templates, endpoints and more.

These objects require:
- stable identity across rename/import/export;
- revisions;
- dependency graphs;
- draft/published state;
- migration by definition schema version;
- efficient loading;
- compatibility between Free/Pro releases;
- human/debuggable export.

Storing each builder in unrelated option formats would reproduce the fragmentation WPEssential is intended to eliminate. Conversely, forcing all site runtime data into one generic EAV/JSON repository would harm queryability and WordPress interoperability.

## Proposed decision

Create a shared **Definition Repository** only for WPEssential configuration definitions.

Each definition contains conceptually:
- immutable UUID;
- human slug/key;
- definition type;
- owning module;
- definition schema version;
- status;
- canonical structured payload (JSON-compatible object);
- revision number;
- checksum;
- created/updated actor/time metadata;
- declared dependency references;
- compatibility/platform metadata as needed.

### Runtime content remains in appropriate stores

- posts/terms/users/comments/media → WordPress native storage when those semantics are intended;
- metadata/settings → registered WordPress meta/options or other explicit adapters;
- high-volume operational data → purpose-built module tables;
- user-defined relational/application data → Custom Tables/Relations storage;
- form/chat/job/audit data → their documented module/platform tables.

The Definition Repository is **not** a generic replacement database for all WPEssential/site data.

## Storage implementation under evaluation

Preferred direction: purpose-built WPEssential tables for definitions + revisions + dependency edges rather than one giant `wp_options` blob or hidden CPTs for every definition.

Benefits expected:
- indexed lookup by UUID/type/module/status;
- explicit revision ownership;
- easier dependency graph;
- predictable import/export;
- avoids autoload pressure;
- avoids contaminating public content queries with internal definitions.

Exact table schema is not accepted yet.

## Publication model

Definitions may support:
- draft;
- published/active;
- disabled/archived.

A published definition points to/copies a known revision so partially edited drafts cannot change runtime behavior before validation.

## Dependency graph

Dependencies are declared by stable UUID where practical, for example:
- listing → query;
- query → relation/field/table;
- form → fields/workflow/data source;
- admin column → field/query;
- dashboard → listing/form.

Before destructive delete WPEssential can resolve dependents and block/warn/migrate accordingly.

## Revisions

Revision retention is configurable by safe policy. Revisions support:
- diff;
- restore;
- audit attribution;
- migration from older schema versions.

Do not store secrets inside ordinary definition revision payloads; definitions reference Vault connection IDs.

## Import/export

Canonical export is versioned and deterministic enough for meaningful checksums/diffs after removing volatile metadata. Imports map UUID conflicts explicitly and run dependency/schema validation before applying.

## Alternatives rejected

### One serialized option per whole module
Rejected as the default because it creates large unrelated writes, poor revision/dependency support and possible autoload/performance issues.

### Hidden CPTs for every builder definition
Rejected as the universal default because internal application configuration has different lifecycle/query/permission needs than public content and would couple all builders to posts/postmeta.

### Universal EAV data model
Rejected for runtime application data because of query complexity/performance and loss of native WordPress/custom-table semantics.

## Acceptance work

Before accepting:
1. design concrete tables/indexes;
2. model multisite ownership;
3. benchmark 10k–100k definitions/revisions/edges synthetic worst cases even if normal sites are far smaller;
4. prove upgrade/migration/backup/import behavior;
5. define transaction strategy;
6. define cache/invalidation;
7. create deterministic serialization/diff rules;
8. define uninstall/retention ownership.
