# ADR-0010 Compatibility Preflight — Execution Readiness V1

Status: **READINESS ONLY — NO RUNTIME IMPLEMENTATION / NO P-006 EXECUTION**  
Supervisor issue: **#916**  
Exact baseline: `main @ 624c7de57e4faca8e76770ec4c44e43da4c35113`  
Related: ADR-0001, ADR-0007, ADR-0010, ADR-0014, ADR-0128, P-006, #904/#905, #908/#909, #912/#913, #914/#915.

## 1. Purpose

This document reconciles the still-Proposed ADR-0010 Free ↔ Pro compatibility protocol with the commercial/package architecture that now exists on `main`.

It is intentionally non-runtime. It does **not**:

- move ADR-0010 from Proposed to Accepted;
- create or modify runtime compatibility code;
- execute FP-01…FP-144;
- certify a Free↔Pro pair;
- change entitlement, licensing, Membership, updater or Multisite behavior;
- deploy or release anything.

The purpose is to remove ambiguity from the next authorized implementation gate while preserving the existing evidence counters truthfully.

## 2. Governance state

`GOV-OWNER-CONSENT-001` is active at project scope and authorizes ordinary bounded source development across the accepted 56-surface architecture. That project-level grant does not waive later technical/security/evidence blockers.

ADR-0010 remains **Proposed**. Its own acceptance section requires concrete compatibility-profile evidence before it can move to Accepted, and P-006 remains a fixed executable-evidence protocol whose fixtures have not been executed.

Therefore this document is an execution-readiness artifact only. A future runtime implementation requires a fresh-main implementation issue that explicitly names the ADR-0010 compatibility-preflight gate and preserves the decision/evidence constraints below.

## 3. What current main already establishes

The following architecture no longer needs to be invented by ADR-0010 implementation:

1. **Physical package separation exists.** Free and Pro are built as separate deterministic artifacts. Free owns Platform/Kernel/Contracts/Bootstrap plus CPT and Taxonomy; Pro owns premium module implementation source.
2. **Pro is a separate add-on bootstrap.** `wpessential-pro.php` declares `Requires Plugins: wpessential` and contributes premium modules before Free kernel boot.
3. **Commercial edition metadata exists.** Implemented premium modules use Pro edition metadata after the edition-normalization gate.
4. **A provider-neutral local entitlement domain exists.** Product entitlement state is separate from Membership/auth and is consumed by activation/mutation policy.
5. **Runtime diagnostics inventory exists.** The read-only Modules inventory reports edition/package/compatibility/entitlement/runtime state/reason from canonical runtime sources available today.
6. **Evidence truth is still zero.** None of the above constitutes ADR-0010/P-006 pair certification.

These merged gates supersede any older planning assumption that Free/Pro packaging, edition identity or a local entitlement abstraction still need first-time invention.

## 4. Current compatibility gaps on main

### 4.1 No dedicated Platform API version exists

Free currently exposes marketing/plugin version through `WPE_VERSION` and `Plugin::VERSION`. There is no dedicated `WPE_PLATFORM_API_VERSION` contract.

`ModuleManifest::minimumPlatformVersion` is currently compared to `WPE_VERSION` in diagnostics. That is a local prerequisite check, not an ADR-0010 Platform API compatibility protocol. The future implementation must not silently relabel marketing/plugin version as Platform API version.

### 4.2 Pro declares no supported Free / Platform API range

`wpessential-pro.php` currently declares `WPE_PRO_VERSION` but does not publish:

- minimum/maximum supported Free plugin version;
- minimum/maximum supported Platform API version;
- supported Free platform-schema generation range;
- Pro schema generation as compatibility metadata.

Therefore too-old/too-new Free cannot yet be decided from a canonical local contract.

### 4.3 Pro touches Free runtime symbols before a real compatibility preflight

At `plugins_loaded` priority `-200`, Pro currently checks only whether `WPEssential\Bootstrap\Plugin` exists. Immediately afterwards it references Free-owned entitlement/platform classes and installs the activation policy.

That is safe only for the currently matching codebase. It is not sufficient for an independently updated Free/Pro pair because an older/incompatible Free package can expose `Plugin` while lacking later contracts used by Pro.

The future preflight must complete before Pro references Free classes/interfaces/functions that are not guaranteed by the minimum bootstrap metadata contract.

### 4.4 `WPE_PRO_PACKAGE_ACTIVE` is not a compatibility decision

`WPE_PRO_PACKAGE_ACTIVE` currently means the Pro package bootstrap file loaded. It must remain package-presence identity only.

Free `Plugin::proCustomTablesRuntimeAvailable()` currently gates optional Pro CustomTables runtime/migrations using package-active + class presence. A future compatibility mismatch could therefore still make those paths eligible if the Pro classes are loadable.

ADR-0010 implementation must require canonical compatibility PASS before any Pro-owned runtime composition or Pro migration registration becomes eligible.

### 4.5 Runtime diagnostics calculate compatibility independently

The Modules inventory currently derives compatibility by checking PHP, WordPress and `minimumPlatformVersion` against `WPE_VERSION`, then labels Pro rows `local_prerequisites_met_adr_0010_not_certified`.

This is intentionally truthful for the current gate, but it must not become a second compatibility authority. Once ADR-0010 preflight exists, diagnostics must consume the preflight result rather than recalculate package/API/schema truth independently.

## 5. Future authoritative truth boundaries

ADR-0010 implementation must preserve these independent domains:

| Domain | Future authority | Must not imply |
|---|---|---|
| Package presence / identity | Free + Pro bootstrap metadata | compatibility, entitlement |
| Binary Free↔Pro version compatibility | local compatibility preflight | entitlement, Membership |
| Platform API compatibility | local compatibility preflight | marketing-version equality |
| Platform / Pro schema compatibility | local compatibility + migration preconditions | entitlement |
| Product entitlement | existing Product Entitlement domain | binary/API/schema compatibility |
| Remote license/account/allocation | future Product License service | entitlement unless signed contract says so |
| Membership authorization | Membership/auth policy | product licensing |
| Updater/package trust | updater/TUF/release trust domain | runtime compatibility |

No result in one row may silently upgrade another row.

## 6. Future machine-readable metadata contract

The next runtime gate should introduce the following **local-only compatibility metadata**. Names are fixed for implementation-readiness purposes; values are selected by the implementation PR and must satisfy the validation rules below.

### 6.1 Free publishes

- `WPE_VERSION` — existing Free marketing/plugin version; preserved.
- `WPE_PLATFORM_API_VERSION` — new strict Platform API version.
- `WPE_PLATFORM_SCHEMA_GENERATION` — new non-negative integer generation for Free-owned durable platform schema relevant to Pro compatibility.

### 6.2 Pro publishes

- `WPE_PRO_VERSION` — existing Pro marketing/plugin version; preserved.
- `WPE_PRO_MIN_FREE_VERSION` — inclusive minimum supported Free marketing/plugin version where a marketing-version floor is required.
- `WPE_PRO_MAX_FREE_VERSION` — inclusive maximum supported Free marketing/plugin version for the selected profile.
- `WPE_PRO_MIN_PLATFORM_API_VERSION` — inclusive minimum supported Platform API.
- `WPE_PRO_MAX_PLATFORM_API_VERSION` — inclusive maximum supported Platform API.
- `WPE_PRO_MIN_PLATFORM_SCHEMA_GENERATION` — inclusive minimum supported Free platform-schema generation.
- `WPE_PRO_MAX_PLATFORM_SCHEMA_GENERATION` — inclusive maximum supported Free platform-schema generation.
- `WPE_PRO_SCHEMA_GENERATION` — Pro-owned durable schema generation.

### 6.3 Validation syntax

For compatibility metadata:

- Platform API versions must be strict numeric `MAJOR.MINOR.PATCH` strings. Pre-release/build labels are not accepted in the compatibility contract.
- Free/Pro marketing versions may retain project marketing syntax, but range parsing must be explicit and fail closed on malformed values.
- schema generations must be integers `>= 0` and must never be inferred from plugin versions;
- every declared minimum must be `<=` its corresponding maximum;
- missing, malformed or internally contradictory metadata is not treated as unrestricted compatibility.

No ordinary compatibility check may require a network request.

## 7. Canonical compatibility result

The future Pro bootstrap owns one local preflight evaluation for the installed pair. The result is immutable for the request and contains at least:

- `state`;
- `dimension`;
- `reason`;
- installed Free version or `unknown`;
- installed Pro version;
- installed Platform API or `unknown`;
- installed Free platform-schema generation or `unknown`;
- installed Pro schema generation;
- `premium_boot_allowed` boolean;
- `premium_migrations_allowed` boolean;
- deterministic remediation code safe for admin diagnostics.

### 7.1 Required top-level states

The implementation must support deterministic states covering at least:

- `compatible`;
- `free_missing`;
- `free_bootstrap_incomplete`;
- `free_metadata_missing`;
- `free_version_invalid`;
- `free_version_too_old`;
- `free_version_too_new`;
- `platform_api_missing`;
- `platform_api_invalid`;
- `platform_api_too_old`;
- `platform_api_too_new`;
- `platform_schema_missing`;
- `platform_schema_invalid`;
- `platform_schema_too_old`;
- `platform_schema_too_new`;
- `pro_metadata_invalid`;
- `pro_package_incomplete`.

Unknown/malformed metadata must resolve to a non-compatible state. There is no permissive `unknown => compatible` path.

### 7.2 Result semantics

Only `compatible` may set `premium_boot_allowed=true`.

`premium_migrations_allowed=true` additionally requires applicable schema/migration preconditions. Entitlement state must not be used to manufacture schema compatibility.

Mismatch does not delete Pro data, definitions, history or local configuration. It blocks incompatible premium boot/mutation/migration and exposes a safe remediation diagnostic.

## 8. Minimal bootstrap sequencing

The future sequence must be:

1. WordPress loads Free plugin file.
2. Free publishes bootstrap-safe metadata (`WPE_VERSION`, `WPE_PLATFORM_API_VERSION`, Free schema generation) before ordinary Platform/Kernel boot.
3. WordPress loads Pro plugin file. Pro publishes only Pro-owned constants and loads only a minimal Pro-owned compatibility evaluator that has **no dependency on Free Platform/Kernel/Contracts or premium module services**.
4. On the pre-Free-boot hook, Pro runs local compatibility preflight from constants/basic PHP/WordPress primitives only.
5. Pro publishes the sanitized compatibility result for diagnostics.
6. If result is not `compatible`, Pro returns before:
   - entitlement/platform class references;
   - activation-policy injection;
   - premium module construction/registration;
   - Pro-owned service composition;
   - Pro migrations.
7. If result is `compatible`, Pro may then verify required Free symbols guaranteed by that API profile, build the existing local entitlement policy and contribute premium modules.
8. Free boots normally.
9. Free optional Pro integrations such as CustomTables runtime/migration registration require canonical compatibility PASS, not package presence alone.
10. Runtime diagnostics consume the same published result.

`Requires Plugins: wpessential` remains installation/activation UX metadata. It is not the runtime compatibility authority.

## 9. Load-order behavior

The implementation must be deterministic for both supported plugin-file ordering scenarios.

### Free file loaded first

Free bootstrap metadata becomes available; Pro evaluates it before premium contribution; Free kernel then boots with compatible contributions only.

### Pro file encountered before usable Free metadata

Pro must not fatal and must not guess compatibility. It remains inert/degraded until the point at which the selected WordPress hook guarantees all active plugin files have loaded. If required Free metadata is still absent then, result is `free_missing` or `free_bootstrap_incomplete` and premium boot remains blocked.

No Pro-owned migration may start merely because its PHP classes are autoloadable.

## 10. Free package independence

Free-only behavior is invariant:

- CPT and Taxonomy remain bootable without Pro;
- Free Platform boot does not require Pro compatibility classes;
- missing/incompatible Pro must not make Free public/admin/REST/cron/CLI boot fatal where safe degradation is possible;
- Free package must continue to contain no Pro implementation source prohibited by ADR-0001.

## 11. CustomTables correction required by the runtime gate

The current `proCustomTablesRuntimeAvailable()` check must be hardened.

Future minimum condition:

1. Pro package presence is true;
2. canonical compatibility result is `compatible`;
3. required CustomTables classes exist;
4. schema/migration preconditions are independently satisfied before mutation.

Package presence alone is not sufficient. Entitlement state alone is not sufficient. Class existence alone is not sufficient.

A mismatch must prevent registration/execution of Pro CustomTables migrations.

## 12. Runtime diagnostics integration

After canonical preflight exists, `RuntimeDiagnosticsSnapshot` must stop independently determining Free↔Pro compatibility from module minimums + `WPE_VERSION`.

Instead:

- Free modules continue to show local PHP/WordPress/module prerequisites normally;
- Pro rows consume the canonical sanitized Pro compatibility result;
- diagnostics keep compatibility and entitlement in separate columns;
- a compatible pair with expired/unavailable entitlement shows both truths separately;
- an incompatible pair with active entitlement still shows incompatible;
- diagnostics expose no license token, signed private artifact, account ID or allocation secret;
- ADR/P-006 certification remains a separate evidence field. A runtime `compatible` result does not mean `certified`.

The bridge from Pro preflight to Free diagnostics must be package-safe. A simple sanitized bootstrap-level state/reason publication is acceptable; Free must not import a Pro implementation class merely to render diagnostics.

## 13. Future implementation split

A fresh runtime issue should be split into bounded steps.

### Gate A — Metadata and parser

- add Free Platform API + schema metadata;
- add Pro compatibility-range/schema metadata;
- add minimal Pro-owned strict parser/evaluator with no Free runtime dependency;
- unit tests for malformed/missing/min/max boundaries.

### Gate B — Bootstrap fail-closed integration

- run preflight before Free runtime symbol use by Pro;
- block module registration/services/migrations on mismatch;
- preserve Free-only behavior;
- harden CustomTables optional Pro path to require compatibility PASS.

### Gate C — Diagnostics single-truth integration

- publish sanitized compatibility result;
- make Modules inventory consume it;
- add mismatch remediation states/notices with escaping and authority checks;
- retain entitlement as separate truth.

### Gate D — Executable compatibility evidence harness

Only after the governing decision/evidence gate permits it:

- produce exact hashed Free/Pro artifacts;
- exercise both load orders and selected update/rollback fixtures;
- record evidence against P-006 fixture IDs;
- never promote counters from static/unit assertions alone when a fixture requires packaged/runtime execution.

## 14. Minimum test matrix for the runtime implementation PR

Before merge, the implementation PR must at minimum prove locally/CI as applicable:

1. compatible exact boundaries;
2. Free missing;
3. Free bootstrap present but metadata missing;
4. malformed Free version;
5. Free version below minimum;
6. Free version above maximum;
7. Platform API missing;
8. malformed Platform API;
9. Platform API below minimum;
10. Platform API above maximum;
11. platform schema unknown/malformed;
12. platform schema below/above supported range;
13. Pro metadata malformed or contradictory;
14. compatible pair reaches entitlement-policy setup and module contribution;
15. incompatible pair never constructs/registers premium modules;
16. incompatible pair never registers/runs Pro CustomTables migrations;
17. active entitlement cannot override incompatibility;
18. expired/unavailable entitlement cannot be mislabeled as binary incompatibility;
19. Free-only boot remains unchanged;
20. Free-first packaged boot remains non-fatal;
21. Pro-first/ordering variation remains non-fatal and deterministic;
22. diagnostics consume the canonical compatibility result;
23. diagnostics escape version/reason metadata and leak no commercial secrets;
24. package determinism/boundary assertions still pass.

Passing these implementation tests does not automatically mean all FP-01…FP-144 fixtures have been executed.

## 15. P-006 / FP-01…FP-144 reconciliation

The fixed P-006 matrix remains authoritative and unchanged. Current counters remain:

- FP documented: **144**;
- FP executed: **0/144**;
- FP passed: **0**;
- FP failed: **0**;
- certified Free↔Pro artifact pairs: **0**;
- P-006 runtime certifications: **0**.

Current architecture helps future execution but does not retroactively execute fixtures.

### Category readiness mapping

| FP range | Current readiness after #905/#909/#913 | Remaining primary blocker |
|---|---|---|
| FP-01…12 artifact identity/metadata | Partial | dedicated Platform API/schema/range metadata + strict parser |
| FP-13…28 baseline boot combinations | Partial | canonical preflight before premium contribution |
| FP-29…44 preflight/load-order/autoload | Blocked | minimal no-Free-dependency preflight layer |
| FP-45…60 independent update/interruption | Not executed | packaged multi-version fixtures/recovery harness |
| FP-61…76 Platform API/deprecation | Blocked | Platform API version/range contract |
| FP-77…94 schema/migrations | Partial | schema generations + preflight-gated Pro migrations |
| FP-95…112 entitlement separation | Architecture partial | combined compatibility×entitlement executable fixtures |
| FP-113…128 Multisite/clone/restore | Not executed | separate Multisite/allocation gate + packaged fixtures |
| FP-129…144 security/UX/recovery | Partial | canonical mismatch UI/result + adversarial packaged evidence |

`Partial` means architecture or local unit-level primitives exist. It does not mean the FP fixtures are executed.

## 16. ADR-0010 stale assumptions now reconciled

The ADR remains directionally valid, but implementation should read these clauses in light of current main:

- “Free is platform/kernel and Pro registers premium modules” is now physically implemented by the separate packages.
- “Plugin dependency metadata is insufficient” remains valid; Pro now has `Requires Plugins`, but no canonical runtime pair guard yet.
- “Entitlement/service separation” now has a local entitlement domain, but compatibility still must precede its use during Pro bootstrap.
- “Mismatch diagnostics” now have a Modules inventory surface available to consume future canonical compatibility truth.
- “Pro migrations only after compatibility” is not fully enforced for the optional CustomTables composition because current helper uses package-active + class presence.
- “Platform API version separate from marketing version” remains wholly unimplemented and is the central next blocker.

No historical ADR text is rewritten by this readiness document.

## 17. Runtime implementation blockers

Runtime implementation must not begin as an implicit continuation of this docs branch. The fresh implementation gate must verify:

1. ADR-0010 decision status/authorization is explicitly sufficient for the selected runtime tranche;
2. exact fresh `main` SHA is recorded;
3. open Issues and PRs are reconciled first under repository governance;
4. metadata names/syntax above are preserved or any change is explicitly justified in the implementation issue;
5. no remote licensing/billing/provider call is added;
6. compatibility is evaluated before Pro Free-runtime symbol usage;
7. CustomTables runtime/migration path is included in the compatibility hardening scope;
8. Modules diagnostics consume, not recompute, pair truth;
9. P-006 counters remain zero until qualifying executable fixtures actually run;
10. no release/deployment/certification claim is made without the separate required evidence/release gates.

## 18. Readiness decision

**Ready for a future bounded implementation gate, but not authorized by this document to execute runtime compatibility code or P-006 certification.**

The next source gate should implement only the local, network-free compatibility metadata + preflight + fail-closed bootstrap + diagnostics single-truth path first. Multi-version update certification, remote commercial allocation, Multisite allocation and release certification remain later independent gates.
