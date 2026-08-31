# WPEssential — Portable Configuration Package Format

Status: **Phase 0 planning — no package/import implementation authorized**

## Goal
Support module-wise or whole-platform export/import of WPEssential configuration without coupling portability to local numeric IDs, current plugin version, active Pro entitlement, or one site's DB layout.

This is distinct from full site backup and from runtime-data migration.

---

# Package types

## 1. Definition Package
Contains selected WPE definitions and dependency graph.

Examples:
- CPT + taxonomy + fields + relations;
- Query + Listing;
- Form + Workflow + Email templates;
- Membership Plans + Access Rules without members/enrollments;
- dashboard/profile/settings definitions.

## 2. Blueprint Package
Curated reusable application blueprint with multiple modules plus setup instructions/sample placeholders.

Examples:
- Real Estate Directory;
- Client Portal;
- Membership Portal;
- Approval Workflow.

Blueprints do not contain customer secrets/personal production data by default.

## 3. Full WPE Configuration Package
All selected/available platform definitions/settings suitable for portability, excluding runtime data and secrets by default.

## 4. Runtime Data Export
Separate engine/artifact; never implicitly bundled into a Configuration Package merely because “Export All” is clicked.

---

# Container

Candidate package:
- ZIP container;
- UTF-8 JSON manifests/definitions;
- optional static safe assets where definition portability requires them;
- no executable PHP/JS uploaded as arbitrary code;
- deterministic paths/names where practical;
- archive traversal/bomb protections on import.

Exact ZIP library/tooling requires later implementation decision.

---

# Root manifest candidate

`manifest.json` contains:

- `format`: `wpessential-package`;
- `format_version`;
- package UUID;
- package type;
- created-at UTC;
- source WPE Product Version;
- source Platform API Version;
- package creator/tool version;
- optional human title/description;
- module requirements + minimum compatible versions;
- definition counts by type;
- dependency graph summary;
- asset list;
- checksums;
- secret policy (`excluded`, `references_only`, future separately-encrypted mode if ever approved);
- runtime-data inclusion = false for definition package;
- source site-identifying metadata minimized/optional;
- compatibility hints;
- package integrity/signature metadata if present.

Do not include license keys, account tokens, site auth secrets, passwords or payment credentials.

---

# Definition record format

Each definition artifact includes:
- WPE stable UUID;
- portable key/slug where applicable;
- definition type;
- owning module;
- schema version;
- status intended for export (`draft`/`published` etc. according to option);
- semantic payload;
- dependency references by stable UUID/key, never local numeric DB IDs;
- external references with provider/type, not secret credential value;
- source/migration metadata only when user chooses to retain it;
- asset references;
- checksum.

Local DB primary keys are never portable identity.

---

# Dependency closure modes

When exporting Definition A that references B/C:

## Include required dependencies
Default recommended mode. Package recursively includes hard dependencies.

## Reference external dependencies
Allowed for explicitly external/soft dependencies; package manifest records requirement.

## Selective/manual
Advanced user can exclude optional dependencies after impact preview.

Importer never silently creates broken dangling hard dependencies.

Circular dependency in package graph is a validation error unless the relevant domain explicitly supports a safe mutually-referential import sequence.

---

# Secrets behavior

Default:
- secret values excluded;
- definition contains typed connection/secret slot/reference placeholder;
- import marks connection as `credential_required`;
- UI prompts user to connect/select/create a local secret after import.

A future encrypted-secret transfer feature requires separate ADR/threat model and is not assumed by this package format.

---

# Personal/runtime data

Definition package excludes by default:
- users;
- form entries;
- chat messages;
- membership enrollments;
- workflow runs;
- audit logs;
- notification history;
- backup archives;
- runtime custom-table rows;
- post/term/user field values.

Blueprint sample data, if ever supported, is separately marked `sample_data` and must not contain production personal data.

---

# Assets

Portable static assets may include only items necessary for the definition and safe to redistribute, e.g.:
- WPE-owned template images/placeholders;
- non-secret icon/media assets explicitly selected;
- email/logo assets if licensing/ownership permits.

Assets include:
- portable asset UUID;
- MIME/type;
- size;
- checksum;
- source definition usages;
- license/attribution metadata where relevant.

Do not package arbitrary theme/plugin/vendor paid assets without redistribution rights.

---

# Import pipeline

**Inspect → Verify → Compatibility Check → Dependency Resolve → Conflict Analyze → Map → Dry Run → Approval → Import → Validate → Report**

Before any write, importer shows:
- package format/version;
- definitions/modules;
- missing target modules/dependencies;
- newer/older schema migrations needed;
- unsupported objects;
- conflicts;
- credential placeholders;
- external dependencies;
- assets;
- expected create/update/skip counts.

---

# Conflict classes

## UUID same, content same
`no_change` — skip by default.

## UUID same, content differs
`same_identity_modified` — show semantic diff; choose update/new-copy/skip according to ownership/schema rules.

## UUID absent, key/slug collides
`key_collision` — never assume identity. Options: map existing, rename imported, skip, replace only if target object is WPE-owned and impact permits.

## Dependency missing
`missing_dependency` — import blocked for hard dependency; optional soft dependency can stay unresolved/degraded.

## Definition type unavailable
`module_unavailable` — preserve as deferred package object/report; do not drop silently.

## Target schema older/newer
`schema_incompatible` — migrate only through accepted migrator; unknown future schema remains inspect/read-only.

## External connection missing
`credential_or_connection_required` — import definition but keep disabled/degraded until local connection mapped, if safe.

---

# Conflict UX

Conflict center groups by:
- Safe / no change;
- Create;
- Update;
- Rename;
- Map existing;
- Missing dependency;
- Credential required;
- Lossy/incompatible;
- Unsupported/deferred.

Each row shows:
- source object;
- target object/candidate;
- reason;
- semantic diff summary;
- dependency impact;
- selected action;
- warning severity.

Bulk decisions are allowed only for homogeneous safe conflicts; e.g. “rename all key collisions” can be offered, but “overwrite all” is not a default safe action.

---

# Semantic diff

Definition diff should operate on normalized schema fields, not raw JSON line diff only.

Examples:
- CPT public false → true;
- field required false → true;
- relation one-to-many → many-to-many;
- query row limit 20 → 100;
- Membership rule resource changed;
- workflow action endpoint changed.

High-impact/security diffs are highlighted separately.

---

# Import modes

## Create-only
Never mutates existing matched target definitions. Best for templates/blueprints.

## Sync/update
Can update previously imported same-source/same-UUID definitions with diff/revision safeguards.

## Clone/new identity
Creates new WPE UUIDs and rewrites internal package references to cloned identities.

Useful for copying a reusable blueprint twice on the same site.

## Deferred
Store import report/object metadata for unavailable module, but do not pretend active functionality exists.

Exact persistence of deferred objects is later design work.

---

# Revisions and rollback

Before updating existing WPE definitions:
- preserve current revision;
- import produces a new revision;
- imported package/batch ID recorded;
- rollback can repoint/restore previous definition revision where dependencies allow.

Created definitions can be removed by batch rollback only when they have not gained unrelated target dependencies/content that would make deletion unsafe.

---

# Version compatibility

Package uses `CONTRACT-VERSIONING-AND-DEPRECATION.md` rules.

Importer handles:
- supported older schema → migrate;
- current schema → validate/import;
- future unknown schema → inspect/report, no lossy downgrade;
- missing Pro module → deferred/unavailable handling;
- Free-only site importing Pro definitions → package remains understandable/reportable but Pro functionality is not faked.

License entitlement affects editing/execution availability, not ownership/inspect/export of already imported user configuration where policy permits.

---

# Integrity

Checksums protect accidental/tampered package content detection. They do **not** establish author trust by themselves.

Optional future signed packages can establish publisher identity/trust, e.g. official WPE blueprints or organization-controlled artifacts. Signing requires separate key/distribution policy.

Import from unsigned package is not automatically prohibited, but all content remains untrusted and fully validated.

---

# Official Blueprint Registry — future

If WPE later offers official downloadable blueprints:
- signed publisher metadata;
- package version/changelog;
- minimum Platform/module requirements;
- screenshots/docs without hidden remote code;
- no auto-executed PHP;
- explicit external plugin requirements;
- update/sync policy distinct from local edits.

Marketplace/community blueprints require a separate supply-chain/security model and are not assumed for v1.

---

# Export screen options

Future UI candidate:
- Package type;
- modules/definitions selection;
- include required dependencies toggle (default on);
- include drafts toggle;
- include source migration metadata toggle;
- include selected portable assets toggle;
- include sample data (only if feature exists; off by default);
- secrets = Excluded (default/only initial option);
- package title/description;
- compatibility report preview;
- generate package.

No “include all secrets” convenience checkbox.

---

# Import history

Track safely:
- package UUID/checksum;
- package format/version;
- imported-at/operator;
- source product/platform versions;
- created/updated/skipped/deferred counts;
- definition source→target mapping;
- warnings/conflicts;
- rollback availability;
- report reference.

Imported file itself may be deleted after configurable temporary retention; history can keep checksum/manifest metadata without full package bytes.

---

# Tests required later

- minimal one-definition package;
- multi-module dependency package;
- same-UUID no-change;
- same-UUID modified;
- key collision;
- clone/new-identity reference rewrite;
- missing hard/soft dependencies;
- Free-only target with Pro objects;
- old schema migration;
- future schema rejection;
- secret placeholder handling;
- malicious ZIP paths/bomb;
- malformed JSON/checksum mismatch;
- unauthorized import;
- rollback;
- re-import/idempotency;
- Unicode/RTL;
- large package;
- official signature valid/invalid if signing feature exists.

---

# Development gate

This format is a planning contract only. No ZIP parser/writer, package schema code, import UI, signer or persistence implementation is authorized before explicit owner development consent under ADR-0014.