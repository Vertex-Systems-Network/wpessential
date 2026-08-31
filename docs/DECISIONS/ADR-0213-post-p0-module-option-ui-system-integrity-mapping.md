# ADR-0213 — Post-P0 Module, Option, UI & System Integrity Mapping

Status: **Accepted planning architecture / no development authorization**  
Date: **2026-08-29**  
Work package: **WP118**

## Context

ADR-0212 closed the known Phase 0 product/architecture/exact-evidence planning gap and moved the lifecycle to `AWAITING_DEVELOPMENT_APPROVAL` while implementation authorization remained 0/56.

The owner then explicitly requested a deeper structural audit to verify:
- modules are properly mapped;
- options are mapped to correct modules;
- UI is fully planned/mapped;
- all systems map to WPE modules;
- module relationships are coherent;
- same-working options are not implemented independently across multiple modules;
- no system flow/option bypasses canonical WPE modules/services;
- additional integrity checks are performed before development.

The audit found no missing product surface, but it identified current-state **consolidation risks** because historical catalogs/inventories/IA/registries were accepted at different scope milestones.

## Decision 1 — Canonical 56-surface ownership registry

Accept:

`docs/ARCHITECTURE/CANONICAL-56-SURFACE-OWNERSHIP-REGISTRY.md`

as the current surface ID/semantic-owner authority.

Historical numbering/catalog files remain valid snapshots but are not current numeric authority when they conflict with this registry.

Each of the 56 surfaces has:
- stable current ID;
- key;
- primary semantic owner;
- explicit non-owned behavior.

No new surface is introduced by this ADR.

## Decision 2 — Cross-module option ownership and no-bypass contract

Accept:

`docs/ARCHITECTURE/CROSS-MODULE-OPTION-OWNERSHIP-AND-NO-BYPASS-CONTRACT.md`.

Every production option must ultimately identify:
- semantic owner;
- UI context;
- storage owner;
- execution owner;
- scope;
- Policy gate;
- consumers;
- side effects/events/invalidation;
- import/export owner;
- evidence namespace.

Repeated UI controls are allowed only as owner references/shared owner components/presentation-local semantics. Shadow semantic engines are prohibited.

## Decision 3 — Canonical option routing index

Accept:

`docs/MODULES/CANONICAL-OPTION-OWNERSHIP-INDEX-56-SURFACES.md`.

The detailed historical `OPTION-INVENTORY.md` remains useful for original modules, while the new index is current routing/ownership authority across 56 surfaces and points to exhaustive specs/exact evidence for detailed values/defaults/validation.

## Decision 4 — Admin IA V2

Accept:

`docs/UI/ADMIN-INFORMATION-ARCHITECTURE-V2-56-SURFACES.md`.

All 56 Surface IDs appear exactly once as canonical navigation owners across ten suites:
1. Solutions;
2. Content & Schema;
3. Data & Intelligence;
4. Experience & Presentation;
5. Identity & Access;
6. Automation & Communication;
7. Integrations & Data Movement;
8. Operations & Security;
9. Developer & AI;
10. Platform & Support.

Cross-module links are contextual references/quick-create/shared-owner components, not duplicate module pages.

Surface28 is presented as **Media Operations** with Watermark, Delivery & Performance, and Replacement areas so the current accepted scope is visible without inventing new surfaces.

## Decision 5 — System pattern containment

Accept:

`docs/SOLUTIONS/SYSTEM-PATTERN-TO-CANONICAL-SURFACE-MAP.md`.

All P01…P40 patterns are mapped to exact canonical Surface IDs/shared services/adapters.

Because the current 160 curated reference systems compose P01…P40, they are transitively mapped to WPE canonical owners.

A Solution Blueprint owns composition/provenance/install mappings only. It cannot create hidden system-specific fields, roles, queries, workflows, HTTP engines, storage engines or authorization systems.

## Decision 6 — Dependency relation matrix

Accept:

`docs/ARCHITECTURE/CANONICAL-56-SURFACE-DEPENDENCY-RELATION-MATRIX.md`.

Default architecture minimizes peer hard dependencies. Shared contracts and public Abilities/Data Sources/Events break potential cycles.

Implementation must reject:
- direct dependency cycles;
- undeclared peer-private class/table access;
- consumer fallbacks that silently reimplement a disabled owner.

## Decision 7 — Capability, Ability & Event completion for 32–56

Accept:

`docs/ARCHITECTURE/PER-SURFACE-CAPABILITY-ABILITY-EVENT-REGISTRY-32-56.md`

as the canonical supplement to the original 1–31 registry.

UI, REST, Workflow, Cron, CLI and AI/MCP must converge on the same typed Ability owner. Ability registration never automatically exposes the operation to every channel.

## Decision 8 — Data ownership lifecycle completion for 32–56

Accept:

`docs/ARCHITECTURE/DATA-OWNERSHIP-LIFECYCLE-REGISTRY-32-56.md`

as the canonical supplement to the original data ownership contract.

Every future table/option/meta/storage family requires a declared owner, authority class, scope, lifecycle/retention/privacy, disable/uninstall, backup/restore and migration/reconciliation behavior.

Unowned runtime storage is a stop-the-line architecture defect.

## Decision 9 — Explicit high-risk overlap resolutions

The following current ownership decisions are binding:

### Query/Search/Order
- Query6 = structured source query/sort;
- Search34 = indexed discovery/relevance/facets;
- ContentOrder51 = persistent manual/contextual sequence.

### Status/Workflow
- Status5 = canonical state transition;
- Workflow17 = orchestration/side effects.

### HTTP/Webhook
- Connections23 = credentials, Safe HTTP, signing, transport retry/delivery;
- Workflow/Cron/Notification/Sync/Link invoke it; no parallel HTTP policy stacks.

### Redirects
- Redirect44 = generic routing/redirect engine;
- Admin Menu/Search/Protector/LinkHealth/WLB reference its route semantics while retaining their own business semantics.

### Transform
- Transform45 = typed search/replace/data transformation, serialized safety, dry-run/diff;
- Migration55, Media28, Import26, DBM48 consume it.

### Duplicate/Clone
`DUP` is not a ContentOrder51 clone engine.
- definition duplicate -> definition owner + Definition Repository;
- entity duplicate -> source Data Source/owner Clone Plan;
- relation ->4;
- media ->28;
- order/hierarchy-copy portion ->51;
- cross-type mapping -> typed mapping.

### Audit/Analytics/Event Bus
- Audit/Observability = security/admin/decision provenance;
- Analytics33 = behavioral warehouse;
- Event Bus = typed delivery vocabulary.

### Backup/Reset/Staging/Import
- Backup24 = recovery artifact/restore truth;
- Reset25 = destructive reset semantics;
- Staging55 = environment clone/migration/cutover;
- Import26 = one-time package/data runs;
- Transform45 = mutation grammar.

### Presentation boundaries
- ThemeWorkspace56 = frontend theme/child source/declarative workspace;
- AdminTheme49 = wp-admin/login branding;
- SafeScript50 = browser runtime snippets/tags;
- Fonts53 = font registry/delivery;
- Media28 = media assets/derivatives/replacement.

## Decision 10 — Competitive parity overlays are not modules

MPR/RPR/ATM/MDP/STM/BKX/MRL/PBX/JEX/LHX/HFC/UAF/MIG/WLB/DUP/ALX/MBX/THM/RSX/RDX/CPTX are canonical-owner evidence/profile overlays unless a separate accepted Surface explicitly says otherwise.

They may not register independent product module IDs, menus, duplicate storage engines or private Ability families that bypass their mapped owners.

## Decision 11 — Deep audit accepted

Accept:

`docs/QUALITY/POST-P0-MODULE-OPTION-UI-SYSTEM-INTEGRITY-AUDIT.md`.

Final result after remediation:
- 56/56 current surface identity/ownership mapped;
- 56/56 exactly-once canonical UI navigation mapping;
- 40/40 system patterns mapped;
- current 160/160 curated systems transitively contained;
- Surfaces32–56 capability/Ability/event and data-ownership gaps closed;
- known semantic-owner duplicate/bypass findings resolved at planning layer;
- no product-scope change.

## Decision 12 — Lifecycle/readiness

The audit does **not** reopen a product-option planning gap. The issues discovered were current-state integration/consolidation ambiguities and are remediated by this ADR's accepted maps.

Therefore lifecycle remains:

`AWAITING_DEVELOPMENT_APPROVAL`

with:
- project state `PLANNED_EXISTING_PROJECT`;
- execution `PLANNER_ONLY`;
- product scope 56/56 Exhaustive;
- Multisite 56/56;
- AI Prompt 56/56;
- known exact planning gap 0/0;
- production implementation authorization **0/56**.

Runtime/provider evidence remains pending.

## Decision 13 — Mandatory implementation-entry enforcement after future consent

After explicit scoped owner consent, implementation must not start with feature code first.

The Implementation Baseline / Adoption Gate must establish machine-enforced counterparts of the accepted maps, including:
- Surface Manifest Registry;
- Option Ownership Manifest;
- Route Ownership/56-uniqueness linter;
- dependency-cycle/undeclared-private-import linter;
- Ability exposure matrix;
- storage ownership registry;
- cross-module write guard tests;
- Blueprint compiler owner resolution validation;
- parity-overlay registration validation;
- Multisite scope/key validation;
- cache/index invalidation registry;
- provider authority/reconciliation registry;
- destructive operation/recovery registry;
- AI/MCP allowlist validation.

These future executable controls require development/evidence consent and are not authorized by this ADR.

## Development gate

ADR-0213 authorizes documentation/planning architecture only.

No production PHP/JS/TS source, WordPress/WooCommerce mutation, DB schema, package installation, test, benchmark, provider/API/AI/MCP execution, migration, build, package or deployment is authorized.

`continue`, ADR acceptance or structural audit PASS remains insufficient development consent under ADR-0014.
