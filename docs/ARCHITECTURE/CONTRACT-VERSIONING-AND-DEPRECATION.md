# WPEssential — Contract Versioning & Deprecation Policy

Status: **Phase 0 planning — no runtime implementation authorized**

WPEssential has several independent compatibility surfaces. One plugin version number is not enough to describe them safely.

## Version families

### 1. Product Version
Human/release version for WPEssential Free and WPEssential Pro packages.

Used for:
- release notes;
- update UI;
- package distribution;
- support diagnostics.

It does not by itself define API compatibility.

### 2. Platform API Version
Version of shared Free kernel contracts consumed by Pro and first/third-party extensions.

Covers:
- Module Registry interfaces;
- Definition Repository service contracts;
- Data Source interfaces;
- Query/Relation/Policy/Renderer interfaces;
- Ability/Event registration contracts;
- Job/Secrets/Asset/Audit services;
- SDK registration contracts.

Pro declares a supported Platform API range.

### 3. Definition Schema Version
Every definition payload has:
- definition type;
- module owner;
- schema version.

Schema migrations transform old definition revisions to supported runtime form. Historical revisions are not destructively rewritten merely because the current schema advances.

### 4. Runtime Data Schema Version
Each WPE-owned runtime table/storage domain has its own migration version, e.g. Membership runtime, Workflow runs, Chat, Audit, Jobs if WPE-owned.

Never infer table migration state from Product Version only.

### 5. Ability Version
Stable Ability IDs should remain stable for backward-compatible additive changes. Breaking input/output semantics require a versioned ability ID/schema contract or documented transition mechanism.

### 6. Event Schema Version
Every event envelope contains event schema version. Consumers must be able to reject unsupported breaking versions and ignore unknown additive fields where safe.

### 7. Source Adapter Version
Migration adapters have their own converter version independent of the source plugin version.

### 8. Integration Adapter API Version
Builder/billing/storage/notification/etc. adapters declare compatible SDK/API ranges.

---

# Compatibility policy

## Backward-compatible changes
Examples:
- adding optional nullable output field;
- adding optional input with safe default;
- adding new event type;
- adding new field type without changing old type semantics;
- adding capability that does not change existing grants automatically.

These may occur within compatible Platform/API range.

## Breaking changes
Examples:
- removing/renaming required field;
- changing default authorization semantics;
- changing identifier meaning;
- changing event ordering/side-effect semantics relied on by consumers;
- changing definition storage meaning without migration;
- narrowing/expanding capability implicitly;
- changing relation cardinality interpretation;
- making previously synchronous Ability asynchronous if consumer contract breaks.

Breaking changes require explicit new contract version and migration/deprecation plan.

---

# Deprecation stages

1. **Active** — supported and recommended.
2. **Deprecated** — works; warning/docs point to replacement.
3. **Compatibility-only** — maintained for existing definitions/integrations, no new creation in UI by default.
4. **Removal eligible** — only after documented window, telemetry/support evidence where privacy-safe, migration path and release-major policy.
5. **Removed** — only when migration/failure behavior is defined.

Security-critical functionality may need accelerated deprecation/removal; rationale and safe migration remain required.

## Minimum deprecation expectation
Public stable SDK/Ability/Event contracts should normally span at least one meaningful release cycle and migration path before removal. Exact time/calendar promise is intentionally not fixed until release cadence exists.

---

# Definition migration rules

When loading a definition:
1. identify type/schema version;
2. validate raw persisted revision;
3. run pure version-to-version migrators in sequence to supported in-memory form;
4. record warnings;
5. do not overwrite historical revision merely to render it;
6. persist a new upgraded revision only through explicit migration/save operation according to module policy.

Migrators should be deterministic and idempotent for the same input version.

Unknown future schema version → fail safe/read-only; never downgrade by dropping unknown fields silently.

---

# Free ↔ Pro package compatibility

Free kernel owns Platform API. Pro metadata declares:
- minimum Platform API;
- maximum tested/compatible Platform API range;
- Pro product version;
- Pro module schema versions.

Boot states already defined by Free/Pro compatibility plan remain:
- compatible;
- Free missing;
- Free too old;
- Free too new/unsupported;
- migration required;
- migration failed;
- entitlement read-only/paused where applicable.

No version mismatch may fatal the entire wp-admin/frontend if a safe degraded path exists.

---

# Module manifest versions

Each module manifest records:
- module version;
- required Platform API range;
- definition schema versions owned;
- runtime migration version(s);
- dependencies with version constraints;
- extension points/API version exposed;
- integration adapter minimums where necessary.

Module version changes are independent of enable/disable state.

---

# Capability evolution

Capabilities are security contracts.

Rules:
- adding a new sensitive capability does not automatically grant it to every role that had a broader previous capability unless migration is explicitly justified;
- splitting a capability requires a migration matrix and least-privilege defaults;
- deleting capability requires cleanup and role backup/restore considerations;
- renamed capabilities need transitional mapping only if it does not accidentally broaden access;
- administrator role does not replace explicit high-risk capability design.

---

# Event evolution

Event payload rules:
- stable event type + schema version;
- additive optional fields preferred;
- consumers ignore unknown optional fields;
- removing/changing meaning requires new schema version;
- replay of historical events uses their historical schema;
- event ID remains unique and immutable;
- privacy classification changes may require payload redaction/versioning.

---

# Ability evolution

Ability consumers include admin UI, workflows, REST bridges, CLI, integrations and future AI/MCP.

For every change assess:
- input compatibility;
- output compatibility;
- permission change;
- side-effect change;
- idempotency change;
- sync/async behavior;
- error taxonomy;
- privacy classification;
- retry semantics.

Permission weakening is always treated as security-sensitive/breaking even if JSON schema is unchanged.

---

# Import/export package compatibility

WPE configuration package manifest contains:
- package format version;
- WPE product version;
- Platform API version;
- definition type/schema versions;
- module requirements;
- dependency graph;
- source site/environment-safe metadata;
- checksums.

Import behavior:
- older supported package → migrate through registered migrators;
- newer unknown package → inspect/report but do not destructive-import unknown semantics;
- missing Pro module → preserve package object as unresolved/importable-later where possible;
- secrets remain excluded/default-redacted.

---

# Extension SDK compatibility

Third-party adapters declare SDK compatibility range. WPE should provide:
- feature-detection APIs;
- deprecation notices for developers;
- compatibility test suite;
- no reliance on internal classes/files not declared public;
- public interfaces/wrappers rather than direct vendor-library classes.

Internal classes can change without compatibility guarantee only if they are clearly internal and not leaked through public interfaces.

---

# Database migrations

Runtime DB migration records store domain + migration version + state, not only a single global `db_version`.

Migration principles:
- forward migration ordered and resumable where needed;
- failure leaves explicit migration-failed/degraded state;
- old code must not blindly run against newer incompatible schema;
- downgrade support is explicit, not assumed;
- backup/restore is the recovery boundary for irreversible migrations;
- large migration uses Job Service/chunking only after authorization and accepted implementation design.

---

# Release compatibility report

Every future release affecting public contracts should document:
- Added;
- Deprecated;
- Breaking (if allowed by release policy);
- Definition migrations;
- Runtime migrations;
- Free/Pro compatibility;
- SDK changes;
- Ability/Event changes;
- required source adapter recertifications;
- rollback/recovery notes.

---

# AI context rule

AI agents must inspect current contract/schema versions instead of generating from remembered old shapes. An AI prompt/template cannot be treated as a compatibility layer.

When a deprecated Ability exists, AI-facing catalogs should prefer the replacement and explain migration rather than silently invoking deprecated contracts indefinitely.

---

# Development gate

This versioning policy is documentation only. No package versions, migrations, compatibility shims or runtime deprecation code are authorized until explicit owner development consent under ADR-0014.