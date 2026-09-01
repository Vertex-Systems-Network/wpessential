# Shared Module Activation Gate V1

Status: implementation candidate  
Tracker: #71 / #66 Gate A  
Base: `main @ c2036fc2013838083d55ca8b29b2697fdc408d01`

## Scope

This tranche adds the Free-owned runtime admission seam required before any separately distributed premium add-on can contribute premium modules to the shared Kernel/Module Registry.

It does **not** wire any concrete Pro module into the Free bootstrap and does not implement the proposed ADR-0010 / P-006 commercial compatibility or licensing protocol.

## Contract

`ModuleManifest::edition` remains descriptive classification. It does not grant activation by itself.

The Kernel evaluates a `ModuleActivationPolicyInterface` before registry insertion:

- the default policy admits `edition=free`;
- the default policy denies `edition=pro`;
- an explicitly supplied policy may authorize an externally supplied Pro module;
- a denied module remains absent from `ModuleRegistry`;
- denied modules cannot run `register()` or `boot()`;
- existing `ModuleRegistry::has()` meaning is preserved;
- an admitted module whose dependency was denied follows the existing missing-dependency `Degraded` path.

No denied `ModuleState` is introduced.

## Free / Pro boundary

The Free bootstrap exposes only neutral, typed pre-boot integration methods:

- `Plugin::setModuleActivationPolicy(ModuleActivationPolicyInterface $policy)`;
- `Plugin::registerModule(ModuleInterface $module)`.

Both must be called before WPEssential boot begins. The Free bootstrap itself continues to instantiate only Free modules. It contains no `FieldsModule` or `Modules\\Fields` reference.

A separately distributed add-on may contribute its own module object and an authorization policy through these shared contracts. The add-on remains responsible for its own source, entitlement decision, and future compatibility preflight. Those systems are outside this V1 tranche.

## Fail-closed boundary

A Pro manifest presented to an unconfigured Free Kernel is denied before `ModuleRegistry::register()`. Because the denied module is never registered:

- `ModuleRegistry::has()` stays false for it;
- `ModuleRegistry::state()` remains `null`;
- no module-owned services or Abilities can be registered;
- dependents degrade through the already-existing missing-dependency rule.

## Certification target

Focused unit coverage must prove:

1. Free modules are admitted and booted by default.
2. Pro modules are denied by default and never execute lifecycle methods.
3. An explicit injected policy can authorize a synthetic Pro module.
4. A Free module depending on a denied Pro module becomes `Degraded` without lifecycle execution.
5. Free bootstrap source contains no concrete Fields/Pro implementation reference.

Exact-head architecture, PHP quality, platform compatibility, and distributable workflows remain authoritative before promotion.

## Non-goals

- no concrete `FieldsModule` registration in Free;
- no license key storage;
- no billing, checkout, account, trial, or vendor-server client;
- no production entitlement provider;
- no ADR-0010/P-006 compatibility handshake implementation;
- no new module lifecycle state;
- no production release or deployment.
