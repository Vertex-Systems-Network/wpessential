# WPEssential — Phase 2 Pre-Status Finalization

Date: 2026-09-01  
Authoritative starting source: `main @ 43a8d7ae03d3b5c921f058452c46138cdacae226`  
Tracker: #66

## Purpose

This document is the serialized implementation gate for everything that must be completed before Status Manager begins.

Canonical dependency order remains:

`Custom Fields → Relations → Query → Admin Columns → Dynamic Listings → Status`

Later modules must not be used to hide incomplete ownership, storage, authorization, migration, rendering or query contracts in an earlier dependency.

## Current Surface 3 source truth

The current main branch has advanced beyond the older `CHECKPOINT.md` Surface 3 snapshot.

Merged and exact-head-certified backend work now includes:

1. canonical Field types, repeatability and preset registry;
2. Field Group definition lifecycle;
3. machine-readable Field Catalog API;
4. typed Field value normalization;
5. stable per-Field UUID identity;
6. registered post-meta compiler/registrar;
7. verified post-meta value persistence;
8. typed Field value Ability with target/resource authorization;
9. explicit runtime storage projection;
10. finite OR-of-AND post-type target compiler;
11. registered-meta ownership collision guard;
12. published Field Group → registered post-meta binder;
13. verified `single=false` row replacement with snapshot, compensating restore and explicit uncertain-state failure.

The latest promoted recovery work is PR #65 / merge commit `43a8d7ae03d3b5c921f058452c46138cdacae226`. Source-head certification was green in Architecture Guards #712, PHP Quality Toolchain #170, Platform Compatibility Matrix #407 and Distributable Package #322.

## Surface 3 closure gate

Custom Fields is not allowed to advance to `COMPLETE` merely because its native post-meta tranche is robust.

The following classes must be closed or explicitly certified as bounded non-goals before Relations runtime work starts.

### A. Storage evolution

Required:

- explicit storage-key rename detection;
- migration plan produced before mutation;
- no overwrite of an already-owned destination key without an explicit compatible rule;
- exact source snapshot before destructive work;
- deterministic copy/verification before source retirement;
- compensating rollback when a migration step fails;
- uncertain-state failure when recovery cannot be verified;
- no silent raw-SQL bypass of the certified public WordPress metadata boundary;
- migration must preserve Field UUID identity independently from the mutable human-facing Field Name.

### B. Shared runtime activation

Required:

- module enablement must flow through a shared Module Registry / edition / entitlement contract;
- no unconditional Pro activation in `frameworks/Bootstrap/Plugin.php`;
- no Fields-private licensing shortcut;
- disabled/unentitled state must not expose write paths or register runtime storage accidentally;
- activation/deactivation must not destroy stored values or definitions.

### C. Admin builder and renderer

Required:

- Field Group list/create/edit/publish UX consumes backend Field Catalog truth rather than duplicating type semantics in React;
- recursive Group/Repeater behavior remains canonical;
- location rules use the same normalized OR-of-AND model as runtime target resolution;
- validation errors map to the exact field/control that produced them;
- unsupported provider/complex storage remains visibly unavailable instead of being serialized opportunistically;
- enhanced Date/Time/DateTime/Range/Color controls remain consistent with the product contract;
- Code field remains stored source text only and never becomes executable PHP/JavaScript.

### D. Import/export and compatibility

Required:

- versioned Field Group export format;
- stable UUID preservation where safe;
- collision handling for group key and Field UUID;
- explicit remapping rules when importing into another site/environment;
- provider-owned references remain typed and unresolved references fail closed;
- import must never silently activate unsupported storage modes;
- migrations are reversible or have a documented recovery path.

### E. Performance and reference evidence

Required:

- target compilation and binder behavior at realistic Field Group counts;
- value read/write evidence for scalar, single-array and multi-row tranches;
- no N+1 registration/query behavior introduced by admin/runtime rendering;
- real WordPress reference application proving definition → publish → registration → authorized value write/read → render;
- Multisite scope remains explicit and isolated.

## Surface 4 — Relations entry/exit gate

Relations runtime work starts only after Surface 3 closure.

Relations must own:

- relation definition lifecycle;
- source/target object adapters;
- one-to-one / one-to-many / many-to-many cardinality;
- directionality and inverse lookup behavior;
- safe persistence and recovery;
- authorization and object-level access;
- Multisite scope;
- relation-aware query contract;
- admin relation editing UX;
- import/export and diagnostics.

Fields may reference relation values through typed ownership boundaries but must not implement private relation storage semantics.

## Surface 6 — Query entry/exit gate

Query starts after Relations has a stable persisted contract.

Query owns backend semantics for:

- typed Query AST;
- filtering;
- sorting;
- search;
- pagination;
- Field predicates;
- Relation predicates;
- data-source/provider adapters;
- cache/invalidation rules;
- query diagnostics;
- bounded execution/performance safeguards;
- policy/authorization integration.

Admin Columns and Listings must consume Query instead of implementing parallel private query engines.

## Surface 8 — Admin Columns entry/exit gate

Existing #48/#49 contract work is downstream planning evidence, not permission to outrun dependency order.

Runtime completion requires:

- canonical Column Set/View definitions;
- Query-owned sort/filter/search;
- Fields/source-owner mutation validation;
- safe bulk/inline editing;
- export redaction/formula-injection protection;
- performance/no-N+1 evidence;
- conditional formatting without arbitrary executable configuration;
- visibility remains presentation only, never authorization;
- accessibility and personal/shared state separation;
- lifecycle progress must not claim runtime/product parity from documentation alone.

## Dynamic Listings / Template Builder entry/exit gate

Listings starts after Query and shared data-source/renderer contracts are stable.

Required:

- canonical listing/template definition lifecycle;
- Dynamic Value/Renderer contract;
- Field/Relation/Query data sources;
- safe escaping and output contexts;
- pagination/filter state;
- responsive and accessible rendering;
- import/export;
- diagnostics and empty/error states;
- reference application evidence.

## Status Manager start gate

Status Manager must not begin runtime implementation until all five preceding gates are certified and repository checkpoint truth is synchronized with accepted main.

## Immediate serialized next action

1. reconcile stale `CHECKPOINT.md` Surface 3 truth against current main;
2. audit storage-key evolution as the highest-risk remaining data-integrity blocker;
3. implement/certify that bounded migration/rollback slice before any dependency is advanced;
4. then close shared runtime activation and admin builder/import-export/reference gates;
5. only after Surface 3 closure start Relations.

## Non-negotiable safety rules

- no flag-day rewrite;
- no silent data migration;
- no ownership inferred only from matching string keys;
- no broad post-meta registration fallback;
- no authorization by visibility;
- no arbitrary executable PHP/JavaScript/SQL authored configuration;
- no Pro entitlement bypass;
- no production deployment/release under this document;
- every runtime/product completion claim requires exact-head evidence.