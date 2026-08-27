# WPEssential — Detailed Module Specifications

Status: **Phase 0 — option-level behavioral specification complete; exhaustive module-by-module deepening in progress; technical acceptance blockers remain**

This directory is the detailed source of truth for module behavior. `docs/MODULE-CATALOG.md` is the high-level product catalog; files here define screens, fields, toggles, actions, validation, permissions, lifecycle, integration and test expectations and take precedence where more specific.

## Rule: no module implementation before option specification

A module may not enter implementation until its specification covers, at minimum:

- every screen and sub-screen;
- every visible option, toggle, selector, input and row action;
- defaults and conditional visibility of options;
- validation, sanitization and normalization rules;
- capability/policy requirements for list/read/create/update/delete/run/export/import;
- empty, loading, success, warning, error, disabled, expired-license and recovery states;
- owned data, storage, revisions and migration behavior;
- import/export semantics;
- module enable/disable behavior;
- cross-module dependencies and quick actions;
- REST/Ability/CLI extension surface where applicable;
- asset-loading boundaries;
- audit/observability events;
- performance limits;
- compatibility risks;
- destructive-action safeguards;
- acceptance tests.

See `SPECIFICATION-STANDARD.md` for the mandatory format.

## Development consent gate

Even when a module reaches **Accepted** specification maturity, production development remains prohibited until the user explicitly authorizes development under `../DEVELOPMENT-CONSENT.md` and ADR-0014.

“Continue”, planning approval, ADR approval, research approval, or completion of Phase 0 is not development consent.

## Modules

1. Custom Post Types Builder — Free
2. Taxonomy Builder — Free
3. Custom Fields Builder — Pro
4. Relations Builder — Pro
5. Status Manager — Pro
6. Custom Query Builder — Pro
7. Custom Tables Builder — Pro
8. Admin Columns Builder — Pro
9. Dynamic Listings / Template Builder — Pro
10. Dashboard Widgets Manager — Pro
11. Custom Admin Menu Builder — Pro
12. Settings Page Builder — Pro
13. Dashboard Builder — Pro
14. User Profile Builder — Pro
15. **Membership System — Pro**
16. Builder Widgets Builder — Pro
17. Forms & Workflow Builder — Pro
18. Cron Job Builder — Pro
19. Notification System — Pro
20. Emails Builder — Pro
21. Message & Chat System — Pro
22. REST API Builder — Pro
23. Webhooks & Connections Manager — Pro
24. Backup Manager — Pro
25. Reset Manager — Pro
26. Import / Export — Pro
27. Protector — Pro
28. Watermarker / Media Rules — Pro
29. XML-RPC Manager — Pro
30. Role & Capability Manager — Pro
31. Support / Docs / Changelog / Account Center — platform surface

## Detailed planning artifacts

### Rules and inventory
- `SPECIFICATION-STANDARD.md` — mandatory option-level specification contract.
- `COMMON-OPTION-CONTRACTS.md` — shared semantics/defaults for identity, lifecycle, save/list behavior, confirmations, capabilities, secrets, PII, revisions, dependencies, entitlement state, accessibility, assets, audit and inherited tests.
- `OPTION-INVENTORY.md` — screen/control ledger for all 31 modules/surfaces.

### Detailed module/suite specifications
- `CONTENT-MODEL-SPECS.md` — CPT, Taxonomy, Fields, Relations, Status.
- `DATA-QUERY-SPECS.md` — Query, Custom Tables, Admin Columns, Dynamic Listings.
- `ADMIN-EXPERIENCE-SPECS.md` — Dashboard Widgets, Admin Menu, Settings Page, Dashboard Builder, Builder Widgets.
- `IDENTITY-ACCESS-SPECS.md` — User Profile, Membership integration, Role & Capability.
- `MEMBERSHIP-SYSTEM.md` — full Membership plan/enrollment/entitlement/access/billing-adapter/lifecycle specification.
- `AUTOMATION-COMMUNICATION-SPECS.md` — Forms/Workflow, Cron, Notifications, Email, Chat.
- `INTEGRATION-DATA-SPECS.md` — REST API Builder, Webhooks/Connections, Import/Export.
- `OPERATIONS-PROTECTION-SPECS.md` — Backup, Reset, Protector, Watermark, XML-RPC.
- `PLATFORM-SURFACES-SPEC.md` — onboarding, Home/Modules, Account/License/Plans, Docs, Changelog, Support Tickets and Diagnostics.

### Exhaustive module specifications completed so far

These files go below suite-level behavior and audit individual WordPress/provider arguments and small controls explicitly:

- `FREE-CPT-TAXONOMY-EXHAUSTIVE-SPEC.md` — every planned CPT/Taxonomy registration argument, label, support, REST, capability, rewrite, query-var, block-template, callback-adapter, migration and acceptance-test control.
- `CUSTOM-FIELDS-EXHAUSTIVE-SPEC.md` — field-group contract, shared field options, cardinality/repeatability, field types, option sources, storage/queryability, REST/meta registration, migration and type-specific tests.
- `QUERY-BUILDER-EXHAUSTIVE-SPEC.md` — typed Query AST, provider capability matrix, WordPress query clauses/operators, parameters, joins, relations, projection, aggregates, sorting, pagination, caching, permissions, diagnostics and provider tests.
- `RELATIONS-STATUS-EXHAUSTIVE-SPEC.md` — relation endpoint/cardinality/pivot/order/integrity semantics and exact WordPress post-status plus generic state-machine/transition/history behavior.
- `CUSTOM-TABLES-ADMIN-COLUMNS-EXHAUSTIVE-SPEC.md` — schema/types/indexes/migrations/data browser/query-console rules plus complete list-view source, formatting, filter, sort, inline/bulk edit, views, export and performance contracts.

### Architecture/readiness planning

- `../IMPLEMENTATION-READINESS-MATRIX.md` — module-by-module readiness blockers; Specified is not Accepted or Authorized.
- `../OPEN-DECISIONS-REGISTER.md` — unresolved architecture/product decisions and required evidence.
- `../DEVELOPMENT-CONSENT.md` — explicit user-consent boundary for all executable development/spikes.

### Research
- `../RESEARCH/COMPETITIVE-LANDSCAPE.md`
- `../RESEARCH/MODULE-BENCHMARK-MATRIX.md`
- `../RESEARCH/MEMBERSHIP-LANDSCAPE.md`

## Current coverage

- **31/31 module/platform surfaces:** option/screen inventory present.
- **31/31 module/platform surfaces:** Phase 0 behavioral specification present through common + suite/module specifications.
- **Foundation/data exhaustive deep specs:** CPT, Taxonomy, Custom Fields, Relations, Status, Query, Custom Tables and Admin Columns now have dedicated argument/control-level specifications.
- **Membership System:** dedicated deep specification plus access precedence, enrollment/runtime/protected-file/billing planning artifacts present.
- **Production implementation:** not started and not authorized.
- **Technical acceptance:** not complete; Proposed ADRs and future evidence spikes still block Phase 1.

“Behaviorally Specified” means product semantics are written down. It does **not** mean storage schemas, framework/toolchain choices, provider implementations or performance/security claims have been proven.

## Specification maturity

- **Inventory**: screens/options identified, but edge semantics may still be open.
- **Specified**: defaults, validation, permissions, state transitions and integration semantics are explicit.
- **Exhaustively Specified**: module-specific current platform/provider arguments and small controls have received a dedicated deep audit; technical evidence blockers may still remain.
- **Accepted**: blockers resolved by ADR/research/authorized spike and implementation may become technically eligible.
- **Authorized**: the user has explicitly consented to development; this is separate from Accepted.
- **Implemented**: code exists but still must pass Definition of Done.
- **Verified**: implementation has passed the relevant quality gates.

No status may skip directly from Inventory/Specified to Implemented, and Accepted never implies Authorized.

## Next planning gate

Continue exhaustive module-by-module deepening, prioritizing dependency-critical modules first. Resolve platform-level Proposed ADRs through static research where possible. Any evidence requiring executable code/install/build/runtime testing remains blocked until explicit user development consent.
