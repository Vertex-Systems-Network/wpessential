# WPEssential — P-001 Compatibility Floor Executable Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Work package: `P0-M00-WP07`  
Project state: `PLANNED_EXISTING_PROJECT`  
Execution mode: `PLANNER_ONLY`  
Governs: ADR-0002, P-001, ADR-0003 Abilities, ADR-0010 Free↔Pro compatibility, ADR-0011 CI, ADR-0012 build, ADR-0069/0071/0075 Multisite, project adoption/baseline governance and ADR-0014.

## 1. Purpose

Convert the existing coarse P-001 compatibility spike into a fixed, reproducible, adversarial evidence contract before WPEssential selects or publicly claims its WordPress/PHP/database compatibility floor.

This protocol does **not** accept the proposed floor and does not authorize installation, dependency resolution, WordPress boot, database creation, plugin activation, WP-CLI, CI, package building or runtime testing.

## 2. Planning snapshot — not certification

Static official-source snapshot on 2026-08-28:
- current WordPress release: **7.1** (released 2026-08-19);
- current proposed WPE WordPress minimum remains **6.9** because native WordPress Abilities are an architectural dependency;
- WordPress Requirements recommends **PHP 8.3+**, **MySQL 8.0+** or **MariaDB 10.11+**;
- WordPress 7.1 supports PHP 7.4 through 8.5, while WPE is intentionally considering a narrower modern floor;
- WordPress Hosting guidance for new 7.1 installs favors actively supported modern PHP/database branches.

Authoritative sources must be refreshed again at actual execution and before public beta/stable release. Static research is not a runtime pass.

## 3. Canonical compatibility invariants

A future certified compatibility floor must preserve:

1. **Floor != latest.** Minimum-supported and current-reference environments are separately tested.
2. **Official support != WPE support.** WPE may intentionally require newer secure runtimes than WordPress's absolute backward-compatible minimum.
3. **Unsupported environments fail before unsafe side effects.** No partial schema/config mutation followed by a fatal compatibility notice.
4. **One declared floor everywhere.** Plugin headers, Composer constraints, installer/boot guards, CI, diagnostics, docs and release metadata cannot disagree.
5. **Artifact evidence matters.** Installable release ZIP/package must be tested, not only source checkout.
6. **Free/Pro compatibility is part of platform compatibility.** Supported and unsupported pairings fail/degrade deliberately.
7. **Single-site success does not imply Multisite success.** Network activation, per-site lifecycle and scope behavior require separate evidence.
8. **Existing-project adoption cannot break healthy sites.** Representative plugin/theme coexistence and baseline failures are classified before attributing regressions to WPE.
9. **No deprecation blindness.** Newer PHP/WordPress compatibility includes warnings/deprecations, not only absence of fatal errors.
10. **Database family/version differences are explicit.** MySQL and MariaDB are separate evidence profiles.
11. **Optional infrastructure cannot become hidden mandatory infrastructure.** Object cache, cron, WP-CLI, loopback or rewrite availability must have explicit required/degraded behavior.
12. **Future version claims expire.** A passed matrix is version-scoped and must be rerun when material WordPress/PHP/DB/toolchain support changes.

## 4. Future evidence record

Every P-001 run records:
- execution date;
- exact WordPress core version/branch;
- exact PHP version/SAPI/extensions/config;
- exact MySQL or MariaDB version/config/sql_mode/charset/collation;
- web server/runtime image/OS architecture;
- single-site or Multisite topology;
- object-cache profile;
- WP-Cron/real-cron/loopback profile;
- WPE Free/Pro artifact versions and hashes;
- Composer/npm lock/artifact identity where applicable;
- representative theme/plugin coexistence profile;
- baseline-before-WPE status;
- commands, logs and artifact references;
- pass/fail/NA rationale per fixture.

# 5. Matrix provenance and support-policy fixtures

### CF-01 — Official WordPress latest refresh
Record current stable WordPress release from official source at execution time; do not reuse planning-date value blindly.

### CF-02 — WordPress minimum dependency rationale
Verify the proposed WPE floor still satisfies mandatory platform primitives such as native Abilities without a hidden compatibility shim.

### CF-03 — WordPress maintained-branch review
Record which older WordPress branches are security-maintained versus merely downloadable and distinguish that from WPE support.

### CF-04 — Official PHP compatibility refresh
Refresh WordPress↔PHP compatibility matrix and PHP upstream support lifecycle at execution time.

### CF-05 — PHP WPE-floor support runway
Proposed minimum must have an acceptable security-support runway for intended release window or be explicitly rejected/raised.

### CF-06 — WordPress Requirements refresh
Record current recommended PHP/database/HTTPS baseline from WordPress.org.

### CF-07 — Hosting compatibility guidance refresh
Record actively supported hosting recommendations separately from WordPress absolute minimums.

### CF-08 — MySQL upstream lifecycle refresh
Record supported candidate MySQL branches relevant to intended hosting market.

### CF-09 — MariaDB upstream lifecycle refresh
Record supported candidate MariaDB branches relevant to intended hosting market.

### CF-10 — Dependency floor aggregation
Compute effective minimum imposed by WPE Composer/npm/PHP libraries and required integrations; hidden stricter minimum is a failure.

### CF-11 — Market-impact evidence boundary
Install-base/hosting evidence can inform the floor but cannot lower security requirements without an explicit decision record.

### CF-12 — Matrix freeze
Every certification run publishes an immutable matrix ID so later version additions do not retroactively change earlier claims.

# 6. Metadata, preflight and fail-safe activation fixtures

### CF-13 — Plugin Requires at least header
WordPress minimum header matches canonical WPE floor.

### CF-14 — Plugin Requires PHP header
PHP minimum header matches canonical WPE floor.

### CF-15 — Composer PHP constraint
Composer platform requirement matches documented PHP floor.

### CF-16 — Composer WordPress-related constraints
Required packages/plugins do not silently contradict WPE WordPress floor.

### CF-17 — Runtime preflight consistency
Runtime guard reports the same floor as headers/Composer/docs.

### CF-18 — CI matrix consistency
Required CI minimum/current/forward cells match the declared support policy.

### CF-19 — Documentation consistency
README/install/release/support docs expose one coherent floor.

### CF-20 — Diagnostics consistency
System Status reports actual and required versions accurately.

### CF-21 — Unsupported WordPress preflight
Below-floor WordPress refuses activation/boot cleanly before WPE data/schema mutation.

### CF-22 — Unsupported PHP preflight
Below-floor PHP follows the earliest feasible safe failure path and never parses code requiring a newer language feature before compatibility handling.

### CF-23 — Unsupported database preflight
If WPE adopts a database floor stricter than WordPress, failure occurs before incompatible schema/migration work.

### CF-24 — Requirements notice recovery
Admin/recovery notice is actionable, capability-safe and does not lock the site into an unrecoverable WPE state.

# 7. Clean install, boot, activation and lifecycle fixtures

### CF-25 — Clean single-site install
Release artifact installs without source-tree-only assumptions.

### CF-26 — First activation
Activation completes without fatal, unexpected output or partial broken state.

### CF-27 — Repeated activation idempotency
Deactivate/reactivate does not duplicate schema/options/schedules/hooks.

### CF-28 — Normal deactivation
Deactivation stops relevant runtime behavior without destructive data loss.

### CF-29 — Uninstall path
Uninstall behavior follows explicit keep/remove policy and does not fatal on supported matrix.

### CF-30 — Interrupted activation
Mid-activation interruption leaves resumable/detectable state rather than silent partial success.

### CF-31 — Interrupted upgrade bootstrap
Code/schema version mismatch enters controlled migration/degraded state.

### CF-32 — WP_DEBUG off boot
No user-visible warnings/fatals on ordinary supported boot.

### CF-33 — WP_DEBUG on boot
No unclassified PHP notices/warnings/deprecations from WPE during representative boot.

### CF-34 — SCRIPT_DEBUG/profile
Debug asset/runtime mode does not require undeclared source-only dependencies.

### CF-35 — Admin request boot
Representative WPE admin route loads with bounded hooks/assets and no fatal.

### CF-36 — Frontend request boot
Frontend request with no WPE UI does not load unnecessary admin bundles or fatal.

### CF-37 — REST request boot
REST bootstrap loads required contracts without admin-only assumptions.

### CF-38 — AJAX request boot
Relevant admin-ajax path remains compatible where used.

### CF-39 — Cron request boot
Cron bootstrap works without interactive/admin session assumptions.

### CF-40 — WP-CLI bootstrap
CLI loads plugin without web-only globals/session assumptions.

# 8. WordPress and PHP version matrix fixtures

### CF-41 — Proposed minimum WordPress + minimum PHP
Primary floor cell must pass all baseline platform fixtures.

### CF-42 — Proposed minimum WordPress + next PHP
Forward PHP compatibility on floor WordPress branch.

### CF-43 — Proposed minimum WordPress + newest supported PHP
Newest supported PHP on floor WordPress branch catches deprecations/runtime changes.

### CF-44 — Current WordPress + minimum PHP
Current core must remain compatible with declared WPE PHP floor.

### CF-45 — Current WordPress + next PHP
Current core/current-ish PHP reference cell.

### CF-46 — Current WordPress + newest supported PHP
Forward compatibility cell for newest supported PHP branch.

### CF-47 — Latest maintenance of minimum branch
Test current security/maintenance release of WPE minimum WordPress branch rather than initial x.0 only.

### CF-48 — Latest maintenance of current branch
Current release reference uses latest applicable maintenance patch.

### CF-49 — WordPress minor update within floor branch
WPE remains healthy across supported security/maintenance update.

### CF-50 — WordPress major update floor→current
Upgrade preserves activation/data and detects incompatible dependencies safely.

### CF-51 — PHP patch update
Representative supported PHP patch update does not change WPE behavior unexpectedly.

### CF-52 — PHP minor upgrade minimum→next
Upgrade from minimum to next supported branch preserves boot/migrations/jobs/config.

### CF-53 — PHP minor upgrade next→newest
Forward branch catches deprecations/behavior differences before support claim.

### CF-54 — Below-floor WordPress negative control
WPE fails clearly without partial activation/data mutation.

### CF-55 — Below-floor PHP negative control
WPE fails safely/early according to packaging/runtime constraints.

### CF-56 — Future/unlisted core or PHP version
Unknown newer environment is reported as unverified/compatible-by-policy according to release policy, never falsely certified from version comparison alone.

# 9. Database engine, charset and SQL-behavior fixtures

### CF-57 — Candidate minimum MySQL
Clean install/activation/schema/query baseline on proposed MySQL floor.

### CF-58 — Current/reference MySQL
Reference modern MySQL profile passes baseline.

### CF-59 — Candidate minimum MariaDB
Clean install/activation/schema/query baseline on proposed MariaDB floor.

### CF-60 — Current/reference MariaDB
Reference modern MariaDB profile passes baseline.

### CF-61 — MySQL vs MariaDB DDL parity
WPE migrations use only declared compatible features or branch explicitly by capability.

### CF-62 — utf8mb4 baseline
Definitions/runtime tables and indexed keys behave correctly under expected utf8mb4 charset/collation.

### CF-63 — Existing site legacy collation
Activation/migration handles supported legacy site collation without silently corrupting/mixing incompatible text semantics.

### CF-64 — Long indexed identifiers
Schema stays within tested index/key limits for supported engine/charset profiles.

### CF-65 — Strict SQL mode
Representative writes/migrations pass under strict modern SQL modes rather than relying on coercion.

### CF-66 — Non-default SQL mode
Accepted hosting variations are tested or explicitly unsupported/degraded.

### CF-67 — Transaction capability boundary
Code does not assume cross-engine/DDL transaction semantics that are not guaranteed.

### CF-68 — Concurrent migration lock
Two requests cannot run incompatible schema migration concurrently.

### CF-69 — Interrupted migration
Schema-version/journal state detects and safely resumes/blocks partial migration.

### CF-70 — Database timezone behavior
Stored instants/calendar semantics do not depend on unexpected DB server timezone defaults.

### CF-71 — Case/collation sensitivity
Stable keys/slugs/unique identities behave consistently across certified collations.

### CF-72 — Database connection loss
Temporary DB failure surfaces controlled error/recovery and does not mark partial operation successful.

# 10. Multisite and Site Lifecycle fixtures

### CF-73 — Multisite network activation
Supported network activation initializes only intended network/site resources without duplicate work.

### CF-74 — Multisite network deactivation
Deactivation does not corrupt per-site/network definitions or unrelated site data.

### CF-75 — Existing Multisite install adoption
Installing WPE into an existing network inventories scope before mutation and preserves unrelated sites.

### CF-76 — Subdirectory Multisite
Admin/runtime scope works on subdirectory network topology.

### CF-77 — Subdomain Multisite
Admin/runtime scope works on subdomain network topology where environment supports it.

### CF-78 — Create site after network activation
Site Lifecycle provisions required WPE scope deterministically/idempotently.

### CF-79 — Delete/uninitialize site
Cleanup follows lifecycle policy and never deletes another site's rows due ID/prefix collision.

### CF-80 — Archive/suspend site
Protected async/runtime work follows lifecycle pause/degrade semantics.

### CF-81 — Network admin route
Network-owned controls require network authority and do not masquerade as site settings.

### CF-82 — Site admin route
Site admin cannot mutate network-owned resources by changing IDs/URLs.

### CF-83 — Site switching
Context switch does not reuse stale site caches/Connections/Definitions/abilities.

### CF-84 — Large-network smoke
Representative many-site registration/diagnostics avoids unconditional all-sites fan-out on ordinary requests.

# 11. Core API, cache, cron, CLI and infrastructure fixtures

### CF-85 — WordPress Abilities registration
Required Abilities APIs exist/behave on every declared WordPress-supported cell.

### CF-86 — Abilities permission callback
Typed Ability execution still enforces WPE Policy/capability boundaries on supported core versions.

### CF-87 — REST route registration
No version-specific route/auth/schema regression across supported core matrix.

### CF-88 — Non-persistent object cache
Platform remains correct without persistent cache.

### CF-89 — Persistent object cache
Representative supported persistent cache profile preserves scope/revocation/version correctness.

### CF-90 — Cache flush/loss
Cache loss degrades performance, not correctness/domain truth.

### CF-91 — WP-Cron enabled
Basic schedule/runner integration initializes without false exact-time guarantees.

### CF-92 — WP-Cron disabled
Health/runner state degrades truthfully and supports accepted external runner guidance.

### CF-93 — Loopback blocked
Diagnostics detects degraded loopback/runner profile without fatal platform boot.

### CF-94 — WP-CLI lifecycle commands
Bootstrap/status/migration-safe commands run under supported CLI profile without web assumptions.

# 12. Existing-project coexistence, adoption and upgrade fixtures

### CF-95 — Pre-WPE baseline capture
Representative existing site is checked before WPE install; pre-existing failures are recorded as `BASELINE FAILURE` rather than blamed on WPE.

### CF-96 — Existing plugin/theme coexistence set
WPE activation does not require disabling unrelated healthy common plugins/themes unless a documented conflict is proven.

### CF-97 — Existing custom database state
Activation does not rewrite unrelated tables/options or assume pristine WordPress data.

### CF-98 — Existing cron/actions
WPE does not delete/rewrite third-party schedules/actions during activation/deactivation.

### CF-99 — Existing REST/admin routes
Route/menu/action registration handles collisions deterministically without silently hijacking third-party ownership.

### CF-100 — Existing object-cache/drop-in
Supported drop-in/cache profile is detected without overwriting infrastructure ownership.

### CF-101 — Free-only install
Free plugin boots independently within declared Free contract.

### CF-102 — Compatible Free + Pro
Supported pair boots once with correct ownership and no duplicate platform registration.

### CF-103 — Unsupported Free/Pro mismatch
Mismatch fails/degrades without fatal or destructive migration and exposes exact required versions.

### CF-104 — Rollback/recovery
After failed supported-version update/migration, documented rollback/recovery returns platform to understandable state without pretending irreversible mutations vanished.

# 13. Release artifact, CI and resource evidence fixtures

### CF-105 — Release ZIP install
Actual generated distributable artifact installs and boots; source checkout success alone is insufficient.

### CF-106 — Clean dependency install reproducibility
Lockfiles/package metadata reproduce intended runtime dependency set for each matrix profile.

### CF-107 — No bundled duplicate WordPress/React runtime
Release artifact does not accidentally ship conflicting core-provided runtime dependencies contrary to build policy.

### CF-108 — Translation/asset metadata boot
Generated PHP asset metadata/translations/styles/scripts register on min/current WordPress without missing-build/source assumptions.

### CF-109 — CI matrix reproducibility
Same required cells can run deterministically in automated CI after P-007 is available.

### CF-110 — Baseline resource budget
Activation/admin/frontend/REST smoke captures time, memory, queries and autoload impact so compatibility does not hide pathological boot cost.

### CF-111 — Failure artifact completeness
Every failing matrix cell retains version/profile/log/command evidence sufficient to distinguish WPE defect, environment defect and baseline failure.

### CF-112 — Certification report consistency
Published support table is generated/validated against passed matrix; no environment is marketed as supported from static assumptions alone.

# 14. MUST NOT / stop-the-line gates

P-001 certification fails if any required cell demonstrates:
- below-floor environment entering partial WPE migration/data mutation before safe rejection;
- conflicting minimum versions across plugin header, Composer, runtime guard, CI or documentation;
- supported WordPress/PHP/DB cell fataling during install/boot/deactivate/uninstall baseline;
- unclassified WPE deprecations/warnings on required forward-compatibility cells;
- dependency silently requiring a stricter floor than public WPE metadata;
- database-family-specific corruption, unsafe coercion or unrecoverable partial migration;
- cross-site/network data mutation or stale-scope cache behavior in Multisite;
- WPE requiring persistent object cache, loopback, WP-Cron or WP-CLI when product policy says those are optional/degradable;
- Free/Pro mismatch causing fatal/destructive schema action instead of controlled incompatibility;
- existing-project adoption mutating unrelated third-party data/schedules/routes without an explicit owning contract;
- source checkout passing while release artifact fails;
- baseline failure being mislabeled as a WPE regression/pass;
- support claim extending beyond the exact evidence matrix.

These are stop-the-line defects for the affected compatibility claim.

# 15. Required future P-001 report

Include:
- source-research refresh date and authoritative version/lifecycle references;
- immutable matrix ID;
- exact WP/PHP/MySQL/MariaDB/server/cache/cron/Multisite profiles;
- Free/Pro artifact hashes and dependency versions;
- CF-01…CF-112 pass/fail/NA with rationale;
- baseline-existing-project findings;
- install/activation/deactivation/uninstall evidence;
- unsupported-environment failure evidence;
- database/charset/migration evidence;
- Multisite/Site Lifecycle evidence;
- REST/Abilities/cache/cron/CLI evidence;
- upgrade/mismatch/rollback evidence;
- artifact/CI/resource measurements;
- supported/degraded/unsupported matrix;
- final recommendation for WordPress/PHP/database floors and forward CI targets.

## 16. Current state

**CF fixtures documented: 112.**  
**CF fixtures executed: 0/112.**  
**P-001 compatibility floor certified: no.**  
**ADR-0002 remains Proposed / evidence-gated.**

Planning snapshot does not change the current candidate by itself: WordPress 6.9 + PHP 8.3 remains a candidate, current WordPress 7.1 is the reference target, and database floor remains to be selected from executed evidence and release-market policy.

No WordPress install, PHP/DB environment, dependency solver, plugin activation, migration, WP-CLI command, CI job, release artifact build or runtime test has been executed.

## 17. Development gate

Execution requires explicit scoped owner consent under ADR-0014 / `DEVELOPMENT-CONSENT.md` / `docs/APPROVAL-LEDGER.md` and a bounded test environment plan.

Until then this protocol remains planning evidence only: `NOT EXECUTED`.