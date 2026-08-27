# ADR-0010 — Free ↔ Pro Compatibility Protocol

Status: **Proposed — Phase 0 blocker**  
Date: 2026-08-27

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
5. Site remains functional with Free; Pro data remains untouched.
6. Admin displays exact required update action.

No incompatible state should intentionally produce a fatal error.

## Update-window requirement

Whenever practical, releases maintain an overlap window so either package can update first without site failure.

For breaking platform changes:
- Free introduces new API while retaining deprecated old API for a documented window;
- compatible Pro release adopts new API;
- only a later Free major release removes deprecated contract.

## Migration rule

A migration owned by Pro runs only after Free/Pro compatibility is confirmed. Migrations are versioned and idempotency/recovery tested.

Do not use license state as a substitute for schema compatibility.

## Module manifests

Premium module manifests may declare additional third-party dependencies, but missing/incompatible builders/providers put only that module/adapter into a degraded state rather than invalidating unrelated modules.

## Consequences

- explicit semantic contract between two artifacts;
- safer independent update order;
- requires disciplined deprecation/versioning;
- CI must test supported Free×Pro version combinations, not only matching heads.

## Acceptance work

1. define exact Platform API version format;
2. define compatibility range syntax;
3. design bootstrap before Composer/service registration that cannot fatal on mismatched API;
4. test Free-first/Pro-first/interrupted update fixtures;
5. define deprecated contract support window;
6. define plugin dependency metadata strategy compatible with WordPress;
7. define recovery UI/CLI for mismatch.
