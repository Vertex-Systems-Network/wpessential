# ADR-0010 — Free ↔ Pro Compatibility Protocol

Status: **Proposed — Phase 0 blocker / fixed P-006 evidence defined by ADR-0128**  
Date: 2026-08-27  
Evidence protocol synchronized: 2026-08-28

## Context

WPEssential Free is the platform/kernel and WPEssential Pro registers premium modules into it. Independent package updates create risks:
- Free updates before Pro;
- Pro updates before Free;
- interrupted auto-updates;
- stale cached license state;
- schema migration requiring both versions;
- a module loading against an incompatible platform contract.

A normal plugin dependency check is insufficient for a long-lived modular platform.

## Proposed decision

Free exposes a machine-readable **Platform API version** separate from the marketing/plugin version.

Pro declares:
- minimum/maximum compatible Free plugin version where needed;
- supported Platform API range;
- Pro module schema/migration requirements.

### Boot protocol

1. Free kernel boots and publishes platform version/capabilities.
2. Pro bootstrap performs only lightweight compatibility checks.
3. If compatible, Pro registers module manifests/services.
4. If incompatible, Pro does **not** boot premium modules or migrations.
5. Site remains functional with Free where technically possible; Pro data remains untouched.
6. Admin displays the exact required update/recovery action without leaking commercial/private data.

No known supported/mismatch state should intentionally produce a fatal error when safe degradation is possible.

Compatibility preflight must happen before Pro code references Free symbols/contracts that may not exist in the installed platform version. Plugin dependency metadata can improve installation UX but never replaces the runtime compatibility guard.

## Truth-boundary rule

The runtime must keep these states separate:

- package/binary compatibility;
- Platform API compatibility;
- schema compatibility;
- signed Product Entitlement;
- remote Product License/account/allocation state;
- Membership authorization;
- updater/package trust.

A state in one domain cannot silently upgrade another. In particular:
- a valid product entitlement cannot force an incompatible Pro binary to boot;
- compatible binaries cannot manufacture entitlement;
- licensing API JSON cannot substitute for signed entitlement verification;
- license expiry/outage cannot be used as schema compatibility evidence;
- Product License state cannot become Membership authorization.

## Update-window requirement

Whenever practical, releases maintain an overlap window so either package can update first without site failure.

For breaking platform changes:
- Free introduces new API while retaining deprecated old API for a documented window;
- compatible Pro release adopts new API;
- only a later Free major release removes deprecated contract.

Independent updates are never treated as one atomic deployment transaction. Partial/interrupted package replacement, stale requests and rollback must fail safely and be recoverable.

## Migration rule

A migration owned by Pro runs only after Free/Pro binary + Platform API compatibility is confirmed and the applicable schema preconditions pass. Migrations are versioned and idempotency/recovery tested.

Do not use license state as a substitute for schema compatibility.

Code rollback against a newer unsupported schema must block unsafe mutation rather than attempting an implicit downgrade.

## Entitlement/expiry rule

ADR-0007 remains authoritative for confirmed Pro expiry behavior: local data/configuration and safe deployed output are preserved where technically possible; premium management/mutation can be restricted according to the product contract.

`service_unavailable` / valid offline-cache state is not the same as `expired`.

Package compatibility checks must not require a remote licensing call on ordinary boot.

## Multisite / clone / restore rule

Package compatibility and commercial allocation are separate:
- network/site activation scope is explicit;
- linking an account/network does not silently allocate every child site;
- a cloned/restored database cannot manufacture a second production allocation;
- blog ID/domain identity alone does not establish commercial entitlement;
- restored/mismatched package+schema state is reconciled before remote allocation or migration mutation.

## Module manifests

Premium module manifests may declare additional third-party dependencies, but missing/incompatible builders/providers put only that module/adapter into a degraded state rather than invalidating unrelated modules when the dependency is truly optional at platform level.

## Consequences

- explicit semantic contract between two artifacts;
- safer independent update order;
- requires disciplined deprecation/versioning;
- requires pre-autoload/pre-service compatibility checks;
- CI must test supported Free×Pro version combinations, not only matching heads;
- recovery must distinguish package mismatch from entitlement/service failures.

## Fixed future evidence

ADR-0128 accepts `docs/QUALITY/P006-FREE-PRO-COMPATIBILITY-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the bounded P-006 contract.

It defines **FP-01…FP-144** for artifact metadata, safe boot, load order, independent updates/interruption, Platform API/deprecation, schema/migrations, entitlement/expiry/outage separation, Multisite/allocation/clone/restore, security/redaction/fuzzing and recovery.

Current evidence:
- FP documented: **144**;
- FP executed: **0/144**;
- certified Free↔Pro artifact pairs: **0**;
- P-006 runtime certification: **0**.

## Acceptance work

ADR-0010 can move from Proposed only after authorized evidence proves the selected concrete compatibility profile, including:
1. exact Platform API version/range syntax;
2. bootstrap before incompatible Composer/service/module references;
3. Free-first/Pro-first/interrupted update behavior;
4. supported deprecation window behavior;
5. plugin dependency metadata strategy;
6. schema/migration/rollback recovery;
7. mismatch UI/CLI diagnostics;
8. entitlement/service separation;
9. Multisite/clone/restore safety;
10. required FP fixtures across the accepted compatibility matrix.

No P-006 execution is authorized without explicit owner consent under ADR-0014.