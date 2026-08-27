# WPEssential — Migration & Import Compatibility Plan

Status: **Phase 0 planning — no runtime implementation authorized**

## Goal
Allow users to move from major WordPress builder/membership ecosystems into WPEssential without guessing, silently losing behavior, or making rollback impossible.

Migration is a product feature, not a one-off script.

---

# Migration pipeline

Every source migration uses the same pipeline:

**Discover → Snapshot → Parse → Normalize → Map → Validate → Dry Run → User Review → Execute → Verify → Reconcile → Commit/Checkpoint → Optional Source Deactivation**

No destructive source cleanup is part of the default migration.

## 1. Discover
Detect:
- source plugin/system and version;
- source features/entities actually in use;
- source dependencies/add-ons/builders;
- data volume;
- multisite scope;
- remote billing/storage dependencies;
- unsupported constructs.

## 2. Snapshot
Before any write:
- create a WPEssential migration manifest;
- recommend/require verified backup for high-impact imports;
- hash uploaded source artifacts;
- record source version and counts;
- never copy secrets into the manifest.

## 3. Parse
Use a versioned Source Adapter.

Preferred source order:
1. documented export/JSON/CSV artifact;
2. documented public API/CLI representation;
3. live public plugin APIs when source is installed;
4. version-gated read-only storage introspection only when necessary and explicitly supported.

Never make raw-table assumptions the first integration contract.

## 4. Normalize
Convert source objects into an intermediate source-neutral representation (IR), not directly into target DB rows.

IR families:
- content-type definition;
- taxonomy definition;
- field group/schema;
- relation definition/link;
- query definition;
- listing/template artifact;
- settings/options page;
- user/profile field;
- membership plan/group/rule;
- enrollment/access interval;
- external billing reference;
- runtime entity/value;
- source dependency/external reference.

The IR carries source IDs and migration fidelity metadata.

## 5. Map
Target mapping produces:
- target module/entity;
- exact/convertible/lossy/external-reference/unsupported/conflict class;
- transformation notes;
- dependency mappings;
- required user choice where ambiguous.

## 6. Validate
Validate before writing:
- target schema;
- slugs/keys/reserved names;
- target dependencies;
- user mappings;
- capability assumptions;
- relation cardinality;
- query source availability;
- date/timezone normalization;
- duplicate/external billing IDs;
- unsafe HTML/file references;
- media availability;
- protected-resource access implications.

## 7. Dry Run
Dry-run result must show:
- objects to create/update/skip;
- exact vs lossy mapping counts;
- conflicts;
- unsupported items;
- source dependencies that will remain;
- estimated runtime-data rows;
- potential URL/rewrite changes;
- membership access changes;
- rollback capability;
- warnings requiring acknowledgement.

Dry run performs no durable target mutation.

## 8. User Review
User explicitly selects conflict strategy per class/object where needed:
- create new;
- map to existing;
- skip;
- replace WPE-owned target definition;
- merge only where module semantics define a safe merge;
- keep external reference.

No global “overwrite everything” default.

## 9. Execute
Execution uses chunked Job Service operations for non-trivial migrations.

Requirements:
- resumable batches;
- idempotent item keys;
- optimistic conflict detection;
- source-to-target map table/manifest;
- row/object-level error records;
- no source deletion.

## 10. Verify
Post-import checks compare:
- expected vs created/mapped counts;
- schema validity;
- relationship integrity;
- sample source/target values;
- target query/listing compile state;
- membership access sample matrix;
- unresolved external references;
- failed/skipped objects.

## 11. Reconcile
Migration can produce a reconciliation report containing:
- source object → target object;
- source values/counts → target values/counts;
- intentional differences;
- unsupported artifacts;
- manual tasks;
- external dependencies still required.

## 12. Optional Source Deactivation
Only after verification may the UI suggest source-plugin deactivation readiness.

It must distinguish:
- safe to deactivate;
- safe after manual tasks;
- source plugin still required because unsupported/external artifacts remain;
- unknown/not verified.

WPEssential never auto-uninstalls the source plugin.

---

# Configuration vs runtime-data lanes

## Lane A — Configuration migration
For:
- CPT/taxonomy definitions;
- field groups;
- relations;
- queries;
- settings pages;
- listings when convertible;
- membership plans/rules.

Writes through Definition Repository/import package service.

## Lane B — Runtime-data migration
For:
- posts/meta;
- terms/term meta;
- users/user meta;
- custom-table/CCT records;
- relation links;
- membership enrollments;
- custom runtime records.

Writes through Data Source adapters, not Definition Repository blobs.

## Lane C — External references
For:
- remote subscriptions;
- gateway customers/subscription IDs;
- external media/storage objects;
- builder templates that remain externally rendered.

WPEssential stores typed references and ownership state, not fake local copies.

---

# Conflict identity rules

Preferred matching priority:
1. previous WPE migration source identity;
2. stable source key/UUID preserved from earlier import;
3. explicit user mapping;
4. target canonical key/slug only as a conflict signal, not automatic equality.

Slugs are not globally safe identities.

Re-import must be deterministic: an unchanged source object should map to the same target object whenever prior import metadata exists.

---

# Source adapter certification levels

## Level 0 — Experimental
Static format research only. Not customer-supported.

## Level 1 — Definitions
Configuration-only conversion is tested.

## Level 2 — Runtime Data
Definitions + supported runtime data are tested.

## Level 3 — Full Operational Migration
Includes relationships, external references, membership/access semantics, reconciliation and rollback/resume behavior appropriate to the source.

Marketing must state the certification level; “supports migration from X” without scope is prohibited.

---

# Initial source roadmap

## Wave 1 — Structured content
1. CPT UI → CPT/Taxonomy definitions.
2. ACF/SCF → Fields + CPT/Taxonomy/Options definitions and supported field values.
3. Meta Box → Fields + supported CPT/Taxonomy structures and values.

Reason: high demand, foundational data models, lower billing risk.

## Wave 2 — Application builder
4. JetEngine → CPT/Taxonomy/Fields/Relations/Queries/CCT/settings/listings as fidelity allows.

JetEngine is intentionally later because it spans many modules and requires richer semantic fixtures.

## Wave 3 — Membership
5. WooCommerce Memberships (+ Woo Subscriptions reference mapping).
6. Paid Memberships Pro.
7. MemberPress.

Order can change after market/customer evidence. Membership source adapters require accepted WPE Membership runtime/access semantics first.

---

# ACF/SCF mapping candidate

- Field Group → WPE Field Group.
- simple scalar field → equivalent typed field where available.
- repeater/group → WPE nested/repeating schema.
- flexible content → WPE flexible/repeating layouts only after exact layout semantics are accepted.
- relationship/post object/user/taxonomy → typed reference/relation according to cardinality and storage intent.
- location rules → WPE location/condition rules where equivalent.
- ACF/SCF CPT → WPE CPT definition.
- ACF/SCF taxonomy → WPE Taxonomy definition.
- Options Page → WPE Settings Page.
- Local JSON/code-defined schema → imported Definition with source origin metadata.

Do not convert ACF blocks automatically into WPE Builder Widgets without a dedicated block migration contract.

---

# Meta Box mapping candidate

- field group → WPE Field Group;
- supported Meta Box field → typed WPE Field;
- group/cloning/reference behavior → explicit mapping review;
- location/settings → WPE conditions/settings where equivalent;
- CPT/taxonomy extension definitions → WPE CPT/Taxonomy where public source definition can be normalized;
- MB Views/templates are not assumed convertible into WPE Listings without a dedicated semantic adapter.

---

# JetEngine mapping candidate

- CPT → CPT Builder;
- taxonomy → Taxonomy Builder;
- meta box/fields → Custom Fields;
- relation → Relations;
- Query Builder query → Query AST where operator/source semantics exist;
- Listing → Dynamic Listing if supported, otherwise external/unsupported artifact;
- Options Page → Settings Page;
- glossary → dictionary/options data source;
- CCT schema → Custom Table + Field/Data Source schema candidate;
- CCT items → runtime rows;
- relation links → Relations runtime links;
- Profile Builder → Dashboard/Profile only through a later dedicated adapter;
- forms → Forms Builder only through a separately certified forms adapter.

Do not treat a JetEngine Skin JSON as one atomic object; it is a package containing multiple domain definitions and dependencies.

---

# Membership migration rules

Membership import is authorization-sensitive. Additional rules:

1. Never grant access from an unvalidated source status string alone.
2. Normalize source lifecycle into WPE Enrollment state using an explicit source-version mapping table.
3. Preserve original source status/history metadata for audit.
4. Calculate target Entitlements only after Enrollment/Plan/Rule import completes.
5. Compare sample expected access before/after import.
6. Remote subscriptions remain external references and require provider/gateway identity validation.
7. Importing historical transactions never charges users.
8. A missing remote subscription cannot be silently recreated.
9. Date/timezone semantics must be explicit.
10. Membership users are matched to WordPress users using deterministic identity/conflict rules.

---

# Rollback strategy

Importer rollback is not equivalent to full DB rollback.

Preferred approach:
- tag all target objects created/updated by import batch;
- preserve pre-import revision for WPE definitions;
- record before-state for supported target runtime updates;
- created objects can be removed by rollback if they have not acquired unrelated post-import dependencies/data;
- updates restore recorded previous state where safe;
- irreversible/external effects are prohibited from ordinary migration execution.

For high-volume/destructive migrations, verified Backup Manager restore point remains the strongest recovery boundary once Backup exists.

---

# Security

Migration inputs are untrusted.

Required defenses:
- file type/size limits;
- ZIP archive traversal/bomb protections;
- JSON/XML/CSV parser limits;
- XML external entity protections;
- spreadsheet formula injection protection on exports;
- no PHP execution from exported code/config;
- sanitize imported HTML according to destination policy;
- remote media fetch uses SSRF controls;
- no arbitrary unserialize of attacker-controlled objects;
- no secrets imported through generic mappings;
- import permission + nonce/re-auth for sensitive domains;
- audit batch and operator.

Generated PHP from ACF/other tools may be shown/read only by an offline/static converter if ever supported; it is never `eval()`ed.

---

# UX screens

Future Import Migration Center:

## Sources
Cards show source detected, version, installed/active state, certification level, supported domains.

## Scan
Counts source objects/data and detects dependencies.

## Mapping
Table columns:
- source object;
- target module/object;
- fidelity;
- action;
- dependency/conflict;
- notes.

Filters: Exact, Convertible, Lossy, External, Unsupported, Conflict.

## Dry Run Report
Summary + downloadable report.

## Execute
Progress by domain/batch; pause/cancel where safe.

## Verification
Counts, failed objects, access checks, integrity diagnostics.

## Migration History
Batch ID, source/version, date/operator, target counts, report, rollback availability.

---

# Acceptance suite per source adapter

Every certified adapter needs fixtures for:
- smallest valid source;
- realistic complex source;
- malformed export;
- duplicate/conflicting target;
- re-import/idempotency;
- source plugin newer/older than certified version;
- unsupported field/entity;
- empty values/null/false/zero distinctions;
- Unicode/RTL labels;
- dates/timezones;
- large/chunked dataset;
- interruption/resume;
- rollback;
- permissions;
- malicious import payload;
- source deactivation verification where applicable.

Membership adapters add multi-membership, expired/cancelled/grace cases, remote subscription references, missing users, and access-equivalence fixtures.

---

# Development gate

This architecture does **not** authorize writing any source adapter, reading executable source-plugin fixtures, running imports, creating migrations, or installing competitor plugins. Those activities require explicit owner development/executable-spike consent under ADR-0014.