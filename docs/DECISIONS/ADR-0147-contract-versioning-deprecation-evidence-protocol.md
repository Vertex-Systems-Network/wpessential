# ADR-0147 — Contract Versioning & Deprecation Executable Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP30`

## Context

WPEssential has an accepted paper policy for multiple independent compatibility/version surfaces: Product Version, Platform API Version, module versions, Definition schema versions, runtime-domain migration versions, Ability/Event contracts, adapter/SDK versions and portable-package format versions.

Existing executable protocols already own their domain-specific behavior (`FP-*`, `DEF-*`, `KPA-*`, `IM-*`, `CBP-*` and module-specific evidence). What remained missing was one bounded cross-cutting evidence contract for the **version transitions themselves**: version skew, migrator-chain completeness, unknown-future schema, downgrade boundaries, deprecation/removal stages, public SDK/Ability/Event evolution, runtime migration coordination and release compatibility truth.

Without a shared protocol, a successful upgrade or a matching product version could be incorrectly treated as proof that every inner contract is compatible.

ADR-0014 remains authoritative: accepting this evidence contract does not authorize execution.

## Decision

Adopt `docs/QUALITY/CONTRACT-VERSIONING-DEPRECATION-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the canonical shared future evidence contract for cross-version compatibility, migration, deprecation and removal semantics.

The protocol freezes **VER-01…VER-176**.

Current execution truth: **0/176 executed**.

No cross-version runtime certification exists yet.

## Version families remain independent

WPEssential must preserve independent truth for:
- Product Version;
- Platform API Version;
- Module Version;
- Definition Schema Version;
- runtime-data/domain migration version;
- Ability contract/version;
- Event schema version;
- adapter/SDK API version;
- package format version;
- provider/source-adapter version where relevant;
- compiler/descriptor version where generated runtime artifacts depend on it.

A Product Version match does not certify these inner versions. A successful boot does not certify a migration. A successful migration does not imply safe downgrade.

## Independent certification classes

- `VER-I` — version identity/catalog/manifest truth;
- `VER-P` — Platform API / Free↔Pro/module compatibility ranges;
- `VER-D` — Definition schema evolution/migrator chains;
- `VER-R` — runtime-data/database migration state and recovery;
- `VER-A` — Ability contract evolution;
- `VER-E` — Event schema evolution/replay;
- `VER-X` — extension/adapter/SDK/module dependency compatibility;
- `VER-G` — package/import-export compatibility boundary;
- `VER-L` — deprecation/removal lifecycle;
- `VER-O` — release/rollback/Multisite/operational version coordination;
- `VER-S` — security, observability and performance of version transitions.

Passing one class never implies another.

## Core invariants

1. Version families are explicit; runtime must not infer all compatibility from Product Version alone.
2. Missing migrator steps cannot be skipped.
3. Historical immutable Definition revisions are never silently overwritten merely to load under a newer runtime.
4. Unknown future schema must fail safely/read-only/degraded; it cannot be saved after dropping unknown fields.
5. Authorization, privacy, side-effect, idempotency and sync/async changes are compatibility semantics even when data shape is unchanged.
6. Compatibility shims never weaken current Capability + resource Policy enforcement.
7. Database/runtime migrations use explicit per-domain state, resumability/failure truth and recovery classification; unsafe downgrade is not assumed.
8. Deprecated → compatibility-only → removal transitions require explicit dependency/migration/release evidence. Deprecation warnings do not themselves authorize removal.
9. Security-critical deprecation may be accelerated, but still requires rationale, safe disable/replacement and recovery guidance.
10. Multisite migration truth is per network/site/domain; partial failure cannot be reported as globally migrated.
11. Backup/restore across versions requires compatibility evaluation and post-restore reconciliation/migration before new runtime is considered healthy.
12. Release compatibility reports must reflect actual public-contract/migration evidence rather than product-version marketing labels.

## Anti-duplication boundary

This ADR does not replace:
- `FP-*` Free↔Pro pair/boot compatibility;
- `DEF-*` Definition persistence/revision/locking evidence;
- module-specific schema/DDL/data-transform evidence;
- `IM-*` package parsing/conflict/write/rollback evidence;
- `KPA-*` registry/Policy/Ability/Event execution evidence;
- `CBP-*` Component Blueprint compiler/renderer evidence;
- `CI-*` release-pipeline enforcement.

VER reports reference those domain protocols where needed. VER success never auto-promotes them to certified.

## Current truth

- VER fixtures documented: **176**.
- VER fixtures executed: **0/176**.
- Cross-version runtime certifications: **0**.
- FP/DEF/KPA/IM/CBP/module-specific counters remain unchanged.
- No upgrade/downgrade, package switch, Definition migration, DB migration, stored-object rewrite, deprecation removal, CLI migration or release fixture has been executed by accepting this ADR.

## Consequences

WPEssential now has an explicit future gate for supported/unsupported version transitions rather than relying on package version matching, one global `db_version`, or undocumented migrator behavior.

Exact supported version ranges, deprecation windows, downgrade profiles, migration performance thresholds and release-support matrix remain evidence-gated until executed.

## Development-consent gate

**Accepted evidence only. No production code, package switching, upgrade/downgrade, migration, schema/data mutation, runtime/CLI execution, benchmark or release fixture is authorized until explicit owner consent under ADR-0014 and `/DEVELOPMENT-CONSENT.md`.**
