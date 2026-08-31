# WPEssential — Free ↔ Pro Compatibility State Machine

Status: **Phase 0 paper design / Proposed / no bootstrap code implemented**  
Related: ADR-0010, ADR-0001, ADR-0014

WPEssential Free is the platform/kernel dependency. WPEssential Pro is a separate add-on that registers premium modules against the platform API.

The goal is to make mixed update order fail safely instead of producing PHP fatals, duplicate dependencies, partial migrations or broken public runtime.

---

# 1. Version concepts

Do not use one version number for every compatibility question.

## Product version

Examples: `1.4.2`, `2.0.0`.

Used for release/update/changelog semantics.

## Platform API version

A separate semantic version for the contracts Pro consumes from Free.

Candidate shape:
- `major.minor` for compatibility;
- patch implementation changes do not change contract version unless needed.

Examples:
- Platform API `1.0`
- Platform API `1.1`
- Platform API `2.0`

## Schema/migration versions

Independent migration/version numbers per shared table/module.

Do not infer DB state only from plugin product version.

---

# 2. Pro compatibility declaration

Every Pro release declares at least:
- minimum Free product version where operationally needed;
- minimum Platform API;
- maximum/exclusive incompatible Platform API boundary;
- minimum PHP/WP consistent with Free platform;
- required shared services/capabilities.

Conceptual example:

`platform_api >= 1.2 and < 2.0`

Do not require exact Free/Pro product-version equality if the API contract is compatible.

---

# 3. Boot ownership

## Free owns

- root platform bootstrap;
- Platform API/version declaration;
- Module Registry;
- shared service container/contracts;
- shared Definition Repository;
- shared Audit/Asset/Ability/Policy contracts;
- shared migration coordinator;
- compatibility diagnostics;
- account/license shell shared by editions where appropriate.

## Pro owns

- Pro module manifests/registrations;
- Pro-only runtime code;
- Pro module migrations registered into the coordinator;
- Pro integration/provider adapters;
- entitlement checks for Pro editing/runtime policy.

Pro must not replace the Free service container with a competing copy.

---

# 4. Boot state machine

## State A — Free active, Pro absent

Expected:
- Free CPT/Taxonomy work normally;
- no Pro fatal/missing references;
- Pro definitions/data remain preserved if Pro was previously installed;
- optional upgrade UI can explain unavailable Pro modules.

## State B — Pro active, Free missing/inactive

Expected:
- Pro detects missing platform dependency at the earliest safe bootstrap point;
- Pro does **not** load module runtime/migrations/assets;
- show an admin dependency notice where possible;
- no destructive cleanup;
- no attempt to emulate the full Free kernel privately.

Public behavior:
- if WordPress cannot load Pro safely without Free, Pro should remain inert rather than fatal;
- preserved generated/public artifacts must not be deleted automatically.

## State C — Free + compatible Pro

Expected normal boot:
1. Free platform initializes contracts;
2. compatibility registry opens module registration window;
3. Pro validates range;
4. Pro registers module manifests/services through public contracts;
5. migration health checked;
6. enabled modules boot;
7. scoped assets/routes/hooks register.

## State D — Free newer than Pro compatible maximum

Expected:
- detect incompatibility before Pro module boot;
- enter `pro_incompatible` degraded state;
- no Pro migrations/mutations;
- preserve data;
- precise admin notice: update Pro or roll back Free;
- Free modules continue where platform itself is healthy.

Do not call removed/changed Platform APIs and hope for the best.

## State E — Pro newer than Free minimum

Expected:
- Pro remains inert/degraded;
- instruct update Free;
- no Pro migrations requiring unavailable platform contract;
- preserve existing data/runtime artifacts.

## State F — migration required

Versions compatible, schema behind.

Expected:
- boot only services safe before migration;
- mark affected modules `migration_required`;
- execute migrations through migration coordinator only under accepted policy;
- long migrations use controlled background/maintenance workflow where necessary;
- no module uses new schema before migration success.

## State G — migration failed

Expected:
- affected module unhealthy/paused/read-only;
- preserve error metadata safely;
- no automatic destructive retries without idempotency/recovery design;
- show recovery/rollback route;
- unaffected modules remain available where isolation permits.

---

# 5. Update-order compatibility target

Release engineering should aim for a compatibility overlap window so users are not forced into an atomic two-plugin update that WordPress cannot guarantee.

Candidate policy:
- a new Free minor release remains compatible with the current Pro release where Platform API major is unchanged;
- a new Pro release supports at least the current Free platform API and the immediately previous supported minor contract where practical;
- Platform API major break requires coordinated release notes, compatibility guards and migration plan.

This is a target, not yet an Accepted numeric guarantee.

---

# 6. Platform API change policy

## Patch/implementation change

No public contract break.

## Minor API addition

Backward compatible:
- new optional service/method/capability;
- old Pro should continue.

## Major API change

May break Pro.

Requires:
- new ADR;
- deprecation window where practical;
- compatibility notice before update;
- new Pro release available before/with Free release;
- rollback instructions;
- test matrix for supported old/new combinations.

Do not remove public contracts casually.

---

# 7. Shared dependency ownership

Avoid two copies of the same platform library fighting inside one WordPress request.

Candidate rules:
- Free owns shared WPEssential platform PHP contracts;
- Pro uses those contracts and does not bundle another copy under same namespace;
- module-specific third-party PHP dependencies are evaluated for namespace collision/prefixing where necessary;
- WordPress-provided JS/React packages are externalized through the accepted build tool;
- Pro does not ship a second React runtime;
- shared frontend WPEssential runtime comes from one clearly owned package/handle where needed.

Exact Composer vendor isolation strategy remains part of implementation/toolchain planning.

---

# 8. Migration ownership

## Shared kernel tables

Owned/migrated by Free platform.

Examples:
- Definition Repository;
- shared audit/platform metadata if applicable.

## Pro runtime tables

Owned by the Pro module but migrations are registered/executed through the shared Migration Coordinator.

Examples:
- Membership enrollments;
- chat messages;
- form entries where Pro-only.

Rules:
- Free does not drop Pro tables because Pro is missing;
- Pro does not modify shared kernel schema outside registered migration contracts;
- each migration records version/result;
- downgrade/rollback implications documented before irreversible changes.

---

# 9. Entitlement/license interaction

Compatibility and licensing are separate.

Possible combinations:
- compatible + entitled → normal edit/runtime;
- compatible + expired entitlement → license-expiry behavior from ADR-0007;
- incompatible + entitled → compatibility degraded state still wins; do not boot unsafe Pro code;
- incompatible + expired → preserve data; explain both conditions.

A valid paid license never permits an incompatible binary to bypass platform guards.

---

# 10. Deactivation behavior

## Deactivate Pro

- unregister Pro runtime;
- pause/cancel owned schedules according to module rules;
- preserve definitions/runtime data;
- preserve safe public output where technically designed;
- do not alter Free CPT/Taxonomy.

## Deactivate Free while Pro active

WordPress may permit user action, but WPEssential should:
- warn that Pro depends on Free;
- after deactivation Pro enters missing-platform inert state;
- never fatal during next request.

Do not implement a brittle deactivation prevention hack as the only safety mechanism.

---

# 11. Uninstall/data deletion

Plugin uninstall and data deletion are separate intentional choices.

Default:
- uninstalling/deleting binary does not automatically erase all WPEssential data unless user explicitly chose a documented uninstall-data policy;
- Pro data must not be deleted merely because Free updates/uninstalls;
- shared-definition dependencies are checked before purge;
- backup/export offered before full purge.

Exact WordPress uninstall UI/mechanics remain future implementation work.

---

# 12. Compatibility diagnostics

WPEssential Home/System Status should report:
- Free product version;
- Platform API version;
- Pro product version;
- Pro declared compatibility range;
- compatibility result;
- schema/migration health;
- entitlement state separately;
- recommended action;
- safe rollback/update guidance.

Support bundle can include these non-secret version facts with user consent.

---

# 13. Required future matrix

Executable tests after owner consent must cover at least:

| Free | Pro | Expected |
|---|---|---|
| current | absent | Free normal |
| absent | current | Pro inert, no fatal |
| current | compatible current | normal |
| current+1 minor | prior compatible Pro | normal/deprecation-safe |
| prior supported Free | current Pro within range | normal |
| too old Free | new Pro | Pro degraded/inert |
| too new incompatible Free | old Pro | Pro degraded/inert |
| compatible | schema old | migration-required |
| compatible | migration failure | module unhealthy, no data loss |
| compatible | Pro license expired | ADR-0007 behavior |

Also test update ordering:
- Free first;
- Pro first;
- interrupted update;
- rollback one plugin only.

---

# 14. Acceptance blockers

Before ADR-0010 becomes Accepted:
- finalize Platform API version format/range rules;
- finalize compatibility overlap promise;
- define exact earliest-safe boot hooks;
- decide shared Composer dependency isolation;
- define migration coordinator contract;
- define release/update metadata protocol;
- later run executable mismatch matrix after explicit owner consent.

No bootstrap, plugin header, Composer package or migration has been created in this planning step.
