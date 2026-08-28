# ADR-0143 — Kernel / Module Registry / Capability-Policy / Abilities / Events / Extension SDK Executable Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP26`

## Decision

Accept `docs/QUALITY/KERNEL-POLICY-ABILITIES-EVENTS-SDK-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the canonical future executable-evidence contract for the shared WPEssential platform chain:

`Bootstrap/Kernel → Module Manifest/Registry → Dependency & Availability Resolution → Capability → Resource Policy → Ability/Event contracts → Extension registries/SDK`

The protocol freezes **KPA-01…KPA-176**.

## Accepted truth boundary

The following remain separate:

`Plugin loaded ≠ Kernel healthy ≠ Module registered ≠ Module available ≠ Module enabled ≠ dependency satisfied ≠ product entitled ≠ principal authenticated ≠ capability granted ≠ resource Policy allowed ≠ Ability registered ≠ Ability exposed to a channel ≠ Ability executed ≠ Event emitted ≠ Event consumed ≠ extension registered ≠ extension certified`

Additional hard separations:
- UI/menu visibility is not authorization;
- Super Admin is not an implicit WPE high-risk bypass;
- Ability registration is not automatic REST/CLI/Workflow/AI exposure;
- Event delivery is at-least-once compatible, not an exactly-once guarantee;
- extension registration is not certification;
- module disable is not data deletion;
- current blog is not durable ownership;
- Pro presence does not authorize a second kernel/service/registry/React runtime.

## Fixed evidence coverage

- bootstrap/kernel/service-container lifecycle — KPA-01…KPA-16;
- module manifest/registry/dependency/data ownership — KPA-17…KPA-32;
- capability/resource Policy — KPA-33…KPA-48;
- Ability definition/registration/schemas — KPA-49…KPA-64;
- Ability invocation/channel parity — KPA-65…KPA-80;
- Event catalog/envelope/emission/consumption — KPA-81…KPA-96;
- Extension SDK/public registries — KPA-97…KPA-112;
- Free↔Pro shared ownership/compatibility — KPA-113…KPA-128;
- Multisite authority/registry scope — KPA-129…KPA-144;
- registry/cache concurrency/failure/observability — KPA-145…KPA-160;
- performance/compatibility/composite security — KPA-161…KPA-176.

## Certification classes

Certify independently:
- `KPA-K` bootstrap/kernel/service-container;
- `KPA-M` module registry/dependency/data ownership;
- `KPA-P` capability/resource Policy;
- `KPA-A` Abilities;
- `KPA-E` Events;
- `KPA-X` extension SDK/registries;
- `KPA-F` Free↔Pro platform ownership/compatibility;
- `KPA-S` Multisite authority/scope;
- `KPA-O` failure/cache/observability/performance.

Passing one class never implies another.

## Accepted invariants

1. Free owns the shared kernel/platform registries required by Free + compatible Pro.
2. Pro registers into the shared platform and does not fork core services/admin runtime/React.
3. Module manifests are declarative; dependency order and compatibility are resolved before module boot.
4. Direct dependency cycles are prohibited and must be detected safely.
5. Missing/incompatible dependencies cause explicit unavailable/degraded states rather than hidden partial boot.
6. Disable preserves owned data by default and never silently cascades deletion/security weakening.
7. WordPress/WPE capability grants operation class; resource Policy grants target/context permission.
8. Authentication/site-network boundary/capability/module availability/entitlement/resource Policy/validation remain ordered gates.
9. UI, REST, CLI, Workflow and AI use the same semantic authorization contract.
10. Abilities are typed/versioned and carry risk, idempotency, privacy, dependency and execution metadata.
11. Registered Ability is not automatically AI-exposed; destructive AI exposure is explicit and still subject to normal authority.
12. Events are typed versioned facts with explicit scope/privacy and at-least-once-safe consumers.
13. Generic Event payloads never carry passwords, reusable tokens, Vault plaintext, card secrets or reusable private URLs.
14. Extensions use typed public registries/namespaces/contracts and cannot turn UI configuration into arbitrary PHP/JS/eval.
15. Extension registration is distinct from official/certified/supported status.
16. Optional extension failure remains isolated where possible.
17. Registry/Policy caches are generation/scope aware and cannot indefinitely preserve revoked authority.
18. Current blog/context switching never becomes durable ownership or authorization.

## Current evidence state

- KPA documented: **176**.
- KPA executed: **0/176**.
- all `KPA-*` certification classes: **0**.
- canonical concrete Kernel/service-container implementation: **NOT SELECTED / NOT IMPLEMENTED**.
- Module Registry persistence/cache implementation: **NOT SELECTED / NOT IMPLEMENTED**.
- Policy evaluator/cache implementation: **NOT SELECTED / NOT IMPLEMENTED**.
- WordPress Abilities bridge/profile: **OPEN / evidence-gated**.
- Event Bus backend/delivery implementation: **OPEN / evidence-gated**.
- extension registry lifecycle/cache implementation: **OPEN / evidence-gated**.
- extension certification harness: **NOT IMPLEMENTED**.
- runtime/Multisite/performance certification: **0**.

## Rejected shortcuts

- second/parallel kernel or security registry;
- silent last-write-wins for module/Ability/Event/extension IDs;
- menu hiding as authorization;
- capability-only authorization when resource Policy is required;
- Site Admin/network or wrong-site coordinate escalation;
- implicit Super Admin bypass of high-risk WPE controls;
- REST/CLI/Workflow/AI alternate authorization path;
- automatic AI exposure of every Ability;
- arbitrary PHP/JS/eval or unrestricted raw SQL as normal UI extension mechanism;
- plaintext secrets/private credentials in Events;
- exactly-once event assumptions;
- extension registration presented as certification;
- module-disable data deletion;
- Pro-owned duplicate platform services/runtime;
- stale security cache preserving revoked access;
- optional adapter fataling unrelated core;
- fabricated success after partial/unknown execution.

## Development gate

No kernel/container/registry/Policy/Ability/Event/SDK runtime code, WordPress hook registration, dependency installation, plugin activation, capability mutation, Ability/Event/extension execution, Multisite fixture or benchmark is authorized by this ADR.

ADR-0014 and the Approval Ledger still require explicit scoped owner consent before executable evidence or implementation.

Current execution count remains **0/176**.