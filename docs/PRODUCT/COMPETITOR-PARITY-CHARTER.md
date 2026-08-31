# WPEssential — Competitor-Parity Product Completeness Charter

Status: **binding product-planning contract / implementation not implied**  
Date: 2026-08-31  
Scope: all 56 canonical WPEssential product surfaces.

## 1. Product target

WPEssential is not targeting a smaller subset of JetEngine, ACF/SCF, Meta Box, CPT UI, Redux, Pods, Toolset or other mature WordPress products.

For every WPEssential surface, the planning target is:

> **native WordPress completeness + credible market-leader parity + WPE-specific improvements**

A surface is not complete merely because:
- its owner is mapped;
- a Definition schema exists;
- a basic CRUD UI works;
- a runtime registration call succeeds;
- a small happy-path option set is implemented.

Architecture completeness and product completeness are separate gates.

## 2. Three mandatory inventories per surface

Before a surface may be marked `PRODUCT_PLANNED`, it MUST have all three inventories.

### A. Native platform inventory

Every relevant public/supported WordPress API argument, object property, lifecycle state, permission, REST behavior, storage behavior and integration point must be classified.

Each item is one of:
- `supported`;
- `supported_with_provider`;
- `deferred_with_reason`;
- `internal_or_prohibited`;
- `not_applicable`.

Silent omission is forbidden.

### B. Competitor capability inventory

The surface must be compared against current mature products that users would realistically substitute for WPEssential.

The inventory includes more than named checkboxes. It covers:
- creation/edit options;
- advanced options;
- field/control types;
- conditions;
- relationships;
- querying/filtering/sorting;
- frontend/admin rendering;
- import/export and portability;
- revisions/history;
- REST/API;
- builder integrations;
- bulk operations;
- multisite;
- permissions;
- developer extension points;
- performance/scaling controls;
- validation/diagnostics;
- migration/compatibility;
- UX productivity features.

### C. WPE exceed inventory

Every surface must identify how WPE will be better, not only equivalent.

Common WPE advantages should include where relevant:
- shared Option Contract;
- Essential / Advanced / Expert progressive disclosure;
- effective-value/default/inheritance visibility;
- preflight validation before mutation;
- dependency graph and impact analysis;
- versioned Definitions and revision CAS;
- deterministic import/export packages;
- shared typed Conditions and Query AST;
- cross-module Data Source Registry;
- Policy/Ability authorization;
- audit trail;
- runtime diagnostics;
- multisite isolation;
- performance budgets;
- AI/MCP exposure only through approved abilities;
- no arbitrary executable PHP from normal admin inputs.

## 3. Benchmark hierarchy

### Platform/content-model benchmarks

Primary references:
- Crocoblock JetEngine / JetFormBuilder / JetSmartFilters / JetBooking family;
- Advanced Custom Fields PRO;
- Secure Custom Fields;
- Meta Box AIO;
- Custom Post Type UI / CPTUI Extended;
- Redux Framework;
- Pods;
- Toolset.

### Specialist benchmarks

A surface must also compare against the strongest specialist products where applicable, for example:
- Admin Columns Pro;
- SearchWP / Relevanssi / FacetWP / WP Grid Builder;
- Gravity Forms / Fluent Forms / Formidable / JetFormBuilder;
- MemberPress / Paid Memberships Pro / Ultimate Member;
- WP All Import / WP All Export;
- UpdraftPlus / Duplicator / WP STAGING;
- Wordfence / Patchstack / Solid Security;
- Redirection;
- WP Crontrol;
- WPCode / Code Snippets;
- WP-Optimize / Advanced Database Cleaner;
- Amelia / Bookly / JetAppointment / JetBooking;
- WP Activity Log / Simple History;
- GeoDirectory / JetEngine Maps.

This list is not closed. Benchmark products are evidence sources, not architectural dependencies.

## 4. Product parity statuses

Every capability item receives one status:

- `MISSING` — absent from WPE plan;
- `PLANNED_BASELINE` — planned but below current specialist parity;
- `PARITY` — planned to match credible competitor behavior;
- `EXCEEDS` — WPE adds a material capability, safety, integration or UX advantage;
- `NOT_APPLICABLE` — intentionally outside this surface;
- `REJECTED_UNSAFE` — competitor behavior exists but WPE rejects the unsafe form and replaces it with a safer provider/ability contract.

`PRODUCT_PLANNED` requires zero unexplained `MISSING` items in the accepted benchmark inventory.

## 5. Option/feature descriptor

Every user-configurable or behaviorally important item SHOULD be representable with metadata equivalent to:

```text
id
surface
feature_family
option_key
native_source
competitor_sources
value_type
requiredness
default_behavior
inheritance
ui_tier
ui_group
control_type
visible_when
enabled_when
validation
sanitization
storage
runtime_effect
migration_effect
permissions
multisite_scope
rest_api
import_export
revision_behavior
performance_class
security_class
parity_status
exceed_reason
source_version_or_date
```

The machine-readable registry is the product coverage source of truth. A React/PHP form is not the coverage source of truth.

## 6. UI parity is part of parity

A backend option that technically exists but is painful or undiscoverable is not sufficient.

Every mature builder surface must plan:
- collection/list screen;
- search;
- filters/statuses;
- pagination;
- safe bulk actions;
- duplication/clone where semantically valid;
- import/export where valid;
- Screen Options/density where relevant;
- contextual help;
- Essential/Advanced/Expert modes;
- domain tabs/sections;
- setting search;
- conditional reveal;
- unsaved-change protection;
- validate/preflight;
- sticky command state;
- diagnostics linked to controls;
- keyboard accessibility;
- responsive wp-admin behavior.

SCF/ACF/Meta Box/JetEngine are references for mature information architecture. WPE should not clone their visual identity.

## 7. Data-model parity is part of parity

WPE must not force everything into post meta merely because a competitor supports it there.

Each surface must explicitly plan:
- canonical data type;
- storage adapter;
- indexing;
- queryability;
- uniqueness;
- relation semantics;
- revisions/history;
- soft/hard delete semantics;
- retention;
- portability;
- multisite scope;
- large-data behavior.

If a custom table provides materially better scale and correctness, WPE may exceed competitor storage while preserving WordPress integration.

## 8. Extensibility parity is part of parity

Mature competitors expose hooks, filters, APIs, field-type extensions and provider integrations. WPE cannot be a closed UI.

Each surface must classify:
- PHP extension contracts;
- WordPress hooks bridge;
- Ability/API contracts;
- provider registry points;
- custom control/field adapters;
- builder adapters;
- import/export adapters;
- query/data-source adapters.

Arbitrary PHP text boxes are not considered extensibility.

## 9. Portability parity is part of parity

Where competitors provide Local JSON, export/import, migration or code generation, WPE must plan an equivalent or better path.

Required direction:
- versioned canonical Definitions;
- environment-safe package format;
- dependency manifest;
- UUID preservation;
- conflict preflight;
- create-only/update modes;
- secrets excluded by default;
- preview/dry-run;
- deterministic checksums;
- local-file/version-control workflow where appropriate;
- migration adapters from major competitor formats when feasible.

## 10. Builder/integration parity is part of parity

Where the feature is presentation-facing, WPE must plan native Gutenberg first plus realistic integration coverage for Elementor, Bricks and other major builders through adapters.

A feature that works only in one builder is not platform-complete unless the surface is explicitly builder-specific.

## 11. Performance parity is part of parity

Each surface declares performance expectations.

Examples:
- no unbounded N+1 list rendering;
- indexed filters/sorts when advertised;
- pagination for large datasets;
- background jobs for large imports/backups/migrations;
- bounded remote calls;
- caching with explicit invalidation;
- custom-table options for large structured data;
- query preview/cost diagnostics;
- concurrency/lease semantics for jobs.

## 12. Safety may intentionally differ from competitors

If a competitor allows raw callbacks, raw PHP, raw SQL or unrestricted class names, WPE may deliberately not copy that input mode.

WPE should provide the same legitimate use case through:
- registered providers;
- allowlisted classes;
- safe Query AST;
- prepared SQL advanced provider;
- controlled Safe Script surface;
- explicit capabilities and audit.

This is `EXCEEDS`, not a parity failure, when the use case remains achievable.

## 13. Required planning artifacts per surface

Before implementation milestone approval, a surface needs:

1. benchmark products and source dates;
2. option/capability inventory;
3. native WordPress/API inventory when relevant;
4. data/storage model;
5. UX information architecture;
6. Essential/Advanced/Expert classification;
7. permissions/policy map;
8. import/export/migration behavior;
9. REST/Ability behavior;
10. multisite behavior;
11. extension points;
12. performance/scaling plan;
13. accessibility requirements;
14. test matrix;
15. parity gaps and explicit WPE exceed items.

## 14. Completion gates

A surface may use these lifecycle states:

```text
OWNERSHIP_MAPPED
BENCHMARKING
CAPABILITY_INVENTORY_COMPLETE
OPTION_CONTRACT_COMPLETE
UX_CONTRACT_COMPLETE
PRODUCT_PLANNED
IMPLEMENTING
RUNTIME_CERTIFIED
PRODUCT_PARITY_CERTIFIED
```

`RUNTIME_CERTIFIED` does not automatically imply `PRODUCT_PARITY_CERTIFIED`.

## 15. Product-parity certification

A module can claim competitor parity only when evidence proves:
- benchmark inventory is current enough for the release;
- all accepted parity items are implemented or explicitly rejected/replaced;
- UI paths are usable;
- runtime paths work;
- persistence round trips;
- import/export preserves supported configuration;
- accessibility passes;
- compatibility matrix passes;
- performance-critical claims have evidence;
- no lower-level implementation silently drops advanced settings.

## 16. Immediate consequence for the current roadmap

The current 56-surface architecture remains useful for ownership and composition, but it is no longer sufficient evidence of product completeness.

The roadmap must now be re-scored against the 56-surface competitor capability matrix.

Current CPT/Taxonomy runtime work is retained as a certified foundation. It must be expanded through the Native Options/Admin UX V2 plan rather than discarded.

All future modules start from the competitor-parity inventory, not from a minimal CRUD screen.
