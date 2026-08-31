# WPEssential — Release & Maintenance Policy

Status: **Phase 0 planning — no release automation/implementation authorized**

## Goal
Keep Free, Pro, module schemas, Platform API, integrations and documentation releasable without turning every update into an uncontrolled “latest everything” deployment.

This policy complements:
- `ARCHITECTURE/CONTRACT-VERSIONING-AND-DEPRECATION.md`
- `PLATFORM/DOCUMENTATION-SUPPORT-RELEASE-IA.md`
- `ARCHITECTURE/FREE-PRO-COMPATIBILITY-STATE-MACHINE.md`

---

# Release identities

WPE tracks separately:
- WPEssential Free Product Version;
- WPEssential Pro Product Version;
- Platform API Version;
- per-module Definition Schema versions;
- per-domain runtime DB migration versions;
- Ability/Event schema versions;
- SDK/Adapter API versions;
- source/builder/provider certification ranges.

One “plugin version” never replaces these compatibility facts.

---

# Product version semantics candidate

Use a predictable `MAJOR.MINOR.PATCH` product version discipline.

## PATCH
Intended for:
- bug fixes;
- compatible security fixes;
- performance/accessibility fixes;
- docs/metadata corrections shipped with artifact;
- no intentional public contract break.

A patch can still contain DB/definition migrations only when strictly backward-compatible and safely tested; migrations are not automatically “minor.”

## MINOR
Intended for:
- backward-compatible features/modules;
- additive public APIs/Abilities/events;
- new adapter certifications;
- deprecations with continued compatibility;
- meaningful compatible schema upgrades.

## MAJOR
Reserved for intentionally breaking user/developer contracts after migration/deprecation policy is satisfied.

Do not inflate major versions for marketing if no compatibility boundary changed; do not hide breaking changes inside a minor because UI looks similar.

---

# Pre-release channels

Candidate channels:
- `alpha` — architecture/implementation incomplete, engineering use;
- `beta` — feature behavior mostly fixed, compatibility/data migration still under validation;
- `rc` — release candidate, no planned feature changes;
- `stable` — quality/release gates passed.

## WordPress.org Free
Initial recommendation:
- WordPress.org Stable Tag points only at approved stable Free release;
- prerelease testing artifacts use controlled development/testing distribution rather than moving stable users onto an alpha/beta tag accidentally;
- exact beta distribution route requires later compliance/process design.

## Pro
May later expose opt-in Stable/Beta update channels from the externally distributed Pro product, but Beta is never forced on normal sites and requires a separate updater implementation/security contract.

---

# Release trains

## Free train
Contains:
- kernel changes needed by Free;
- CPT/Taxonomy;
- Platform API changes;
- shared security/compatibility fixes;
- Free admin/platform surfaces.

## Pro train
Contains:
- premium modules;
- Pro-specific integrations;
- Pro runtime schemas;
- premium UI/adapter updates.

## Integration certification train
Can update tested compatibility metadata/adapter code without forcing unrelated module feature releases where architecture permits.

## Security train
Can override ordinary feature scheduling; scope kept minimal enough for safe expedited verification.

Free/Pro independent release dates are permitted only within tested Platform API compatibility windows.

---

# Release freeze rules

## Feature freeze at RC
After RC candidate:
- no new product features;
- only release-blocking bug/security/docs/package fixes;
- every code change resets affected validation scope.

## Migration freeze
Runtime/definition migration shape freezes earlier than final RC where practical so realistic upgrade fixtures can run repeatedly.

## Dependency freeze
Do not opportunistically upgrade dependencies in RC unless needed for a verified bug/security/compatibility issue.

---

# Quality gate to Stable

Stable release requires evidence applicable to changed scope:
- clean install;
- upgrade from supported predecessor fixtures;
- Free↔Pro mismatch/update-order tests;
- migrations + recovery;
- PHP/WordPress compatibility matrix;
- lint/static/type/build;
- unit/integration/E2E;
- security regression suite;
- asset isolation;
- accessibility critical flows;
- performance regression fixtures;
- adapter/provider certification for changed integrations;
- installable artifact validation;
- version/Stable Tag/changelog consistency;
- docs/release/migration notes;
- checkpoint/release evidence.

“CI green” is necessary but not automatically sufficient when required manual/provider/recovery evidence is missing.

---

# Release artifact truth

For every package archive record:
- product/version;
- commit SHA;
- build tool/version/lockfile identity;
- artifact checksum;
- package signature metadata once accepted;
- bundled dependency list/SBOM direction where tooling supports;
- Platform API range;
- migration versions;
- generated-at/release channel;
- release notes reference.

Do not rebuild the same published version from a different commit and silently replace artifact contents.

If rebuild is unavoidable before public release, discard old prerelease artifact or increment prerelease/build identity; after stable publication use a new product version.

---

# WordPress.org Stable Tag consistency

For Free stable release verify as one release identity:
- plugin header `Version`;
- release tag/version;
- `readme.txt` Stable Tag;
- SVN tag;
- bundled changelog;
- Tested up to / Requires fields;
- release announcement.

Mismatch blocks release.

---

# Free ↔ Pro release compatibility

Before either package releases, matrix contains at minimum:
- new Free + current Pro;
- current Free + new Pro where Pro-first updates can occur;
- new Free + new Pro;
- minimum still-supported compatible combination;
- deliberately incompatible combination must degrade safely.

If a Free Platform API change requires newer Pro:
- compatibility window/sequence documented;
- old Pro fails safe, not fatal;
- migrations requiring both wait until compatible counterpart is active.

---

# Database / definition release policy

## Runtime migration
Every migration records:
- owner/domain;
- from/to migration version;
- expected data volume/locking risk;
- resumability;
- preconditions;
- recovery/backup requirement;
- post-check;
- downgrade behavior (usually not assumed).

## Definition schema
- persisted historical revisions remain immutable;
- read-time pure migrators may adapt old schema in memory;
- durable upgrade creates/records proper new revision/migration state;
- unknown future schema fails safe/read-only.

---

# Security releases

Security fix workflow prioritizes:
- private triage;
- least-scope patch;
- affected version analysis;
- exploit/regression test kept private as needed;
- stable artifacts available before broad exploit detail where responsible disclosure requires;
- directory/plugin team coordination where applicable;
- user-facing advisory with practical update guidance;
- rotate/revoke service/signing credentials if incident requires.

Security release can be PATCH even if urgent; severity is not encoded by semantic version number alone.

---

# Supported-version policy

Do not publish a long calendar support promise before team capacity/release cadence is known.

Initial product-policy direction:
- current stable release is fully supported;
- immediately previous compatible release line may receive critical/security fixes where practical and explicitly stated;
- older lines can become upgrade-only/unsupported;
- source/builder/provider adapter support is version-range-specific even when WPE product version itself remains supported.

Before first stable launch, convert this into a clear public support matrix based on operational capacity.

---

# Rollback policy

## Code rollback
Installing an older Free/Pro ZIP is not automatically safe after forward data/schema migration.

Release notes state:
- whether code downgrade is supported;
- minimum compatible DB/definition schema;
- recovery backup needed;
- supported rollback procedure.

## Data rollback
Prefer restore of verified pre-update backup/restore point for irreversible migrations instead of pretending every schema change has automatic down migration.

## Service rollback
Remote plans/docs/support can roll back independently because they deliver data/service, not runtime executable code. Entitlement signing/key changes still require client trust compatibility.

---

# Update failure behavior

If package update succeeds but migration fails:
- module/platform enters explicit migration-failed/degraded state;
- affected premium/runtime operations stop safely;
- unrelated Free/WordPress functionality continues where possible;
- admin recovery/diagnostics available;
- no repeated destructive auto-migration loop;
- recovery documentation/restore reference shown.

---

# Changelog/release note requirement

Every Stable artifact updates:
- Changelog;
- release notes for material changes;
- migration notes if applicable;
- developer upgrade notes for public contract changes;
- Known Issues;
- compatibility matrix;
- docs impacted by screen/option changes.

Internal Git commits are not customer release notes.

---

# Dependency maintenance

Dependency updates are controlled changes:
- maintenance/security status reviewed;
- changelog/breaking notes reviewed;
- license checked;
- supported PHP/WP/Node/browser matrix checked;
- transitive dependency diff reviewed;
- lockfile updated;
- package/build/tests run later under authorized development;
- unnecessary dependencies removed only with impact analysis.

No scheduled bot PR is auto-merged merely because tests pass.

---

# Adapter/provider recertification

Builder/billing/backup/source-migration adapters have independent compatibility ranges.

Trigger recertification on:
- upstream major release;
- documented API break/deprecation;
- auth/security model change;
- provider endpoint/feature change;
- WordPress/PHP baseline shift;
- WPE adapter contract change.

If upstream unsupported newer version is detected, UI can mark `uncertified` rather than pretending compatibility or disabling unrelated platform functions.

---

# Release ownership/sign-off

Future release checklist should identify accountable roles (human or automation evidence owner) for:
- engineering;
- QA;
- security where applicable;
- migration/data;
- compatibility/integrations;
- docs/changelog;
- package/distribution.

AI can prepare evidence/checklists but must not self-certify tests it did not run or external provider compatibility it did not verify.

---

# Hotfix policy

Hotfix branch/release is justified for:
- severe regression;
- production fatal;
- data corruption risk;
- authorization/security defect;
- widespread integration break.

Hotfix should contain minimum coherent fix, regression test and necessary migration/recovery notes, not unrelated feature work.

---

# Development gate

This is planning only. No version bump, build, SVN publish, external Pro update channel, CI release job, migration or artifact-signing implementation is authorized before explicit owner development consent under ADR-0014.