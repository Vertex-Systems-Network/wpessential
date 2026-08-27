# WPEssential — Detailed Module Specifications

Status: **Phase 0 — specification before implementation**

This directory is the detailed source of truth for module behavior. `docs/MODULE-CATALOG.md` remains the high-level product catalog; files here define screens, fields, toggles, actions, validation, permissions, lifecycle, integration and test expectations.

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

- `SPECIFICATION-STANDARD.md` — mandatory option-level specification contract.
- `MEMBERSHIP-SYSTEM.md` — full initial Membership System specification and market-informed architecture.
- `OPTION-INVENTORY.md` — option/screen inventory for all modules; each item must be resolved before its module is implementation-ready.

## Specification maturity

- **Inventory**: screens/options identified, but edge semantics may still be open.
- **Specified**: defaults, validation, permissions, state transitions and integration semantics are explicit.
- **Accepted**: blockers resolved by ADR/research/spike and implementation may begin.
- **Implemented**: code exists but still must pass Definition of Done.
- **Verified**: implementation has passed the relevant quality gates.

No status may skip directly from Inventory to Implemented.
