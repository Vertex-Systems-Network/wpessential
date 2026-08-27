# WPEssential — Detailed Module Specifications

Status: **Phase 0 — 31/31 product surfaces Exhaustively Specified; technical blockers remain; development not authorized**

This directory is the product-behavior source of truth. `docs/MODULE-CATALOG.md` is the high-level catalog; detailed module files here define screens, fields, toggles, actions, defaults, validation, permissions, lifecycle, integration, failure behavior and acceptance-test expectations.

## Development consent gate

Even when a module is Exhaustive or has Accepted semantics, production development remains prohibited until the owner explicitly authorizes development under `/DEVELOPMENT-CONSENT.md` and ADR-0014.

`continue`, planning approval, ADR approval or Phase 0 completion do not count as development consent.

---

# Maturity model

1. Inventory
2. Behavioral
3. **Exhaustive Option Spec**
4. Accepted semantics
5. Technical Ready
6. Authorized
7. Implemented
8. Verified

Current product-option result: **31/31 surfaces are Exhaustive.**  
Current development authorization: **0/31 authorized.**

See `OPTION-COVERAGE-MATURITY.md` for the exact ledger.

---

# Product surfaces

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
13. Frontend Dashboard Builder — Pro
14. User Profile Builder — Pro
15. Membership System — Pro
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
31. Account / Plans / Docs / Changelog / Support / Diagnostics — Platform surfaces

---

# Shared specification contracts

- `SPECIFICATION-STANDARD.md`
- `COMMON-OPTION-CONTRACTS.md`
- `OPTION-INVENTORY.md`
- `OPTION-COVERAGE-MATURITY.md`

Suite-level behavioral sources remain useful context:
- `CONTENT-MODEL-SPECS.md`
- `DATA-QUERY-SPECS.md`
- `ADMIN-EXPERIENCE-SPECS.md`
- `IDENTITY-ACCESS-SPECS.md`
- `AUTOMATION-COMMUNICATION-SPECS.md`
- `INTEGRATION-DATA-SPECS.md`
- `OPERATIONS-PROTECTION-SPECS.md`
- `PLATFORM-SURFACES-SPEC.md`

---

# Dedicated exhaustive specifications

## Content model / data
- `FREE-CPT-TAXONOMY-EXHAUSTIVE-SPEC.md`
- `CUSTOM-FIELDS-EXHAUSTIVE-SPEC.md`
- `RELATIONS-STATUS-EXHAUSTIVE-SPEC.md`
- `QUERY-BUILDER-EXHAUSTIVE-SPEC.md`
- `CUSTOM-TABLES-ADMIN-COLUMNS-EXHAUSTIVE-SPEC.md`
- `DYNAMIC-LISTINGS-EXHAUSTIVE-SPEC.md`

## Admin / identity / builders
- `ADMIN-DASHBOARD-MENU-SETTINGS-EXHAUSTIVE-SPEC.md`
- `DASHBOARD-PROFILE-ROLES-EXHAUSTIVE-SPEC.md`
- `BUILDER-WIDGETS-EXHAUSTIVE-SPEC.md`
- `BUILDER-INTEGRATION-CERTIFICATION.md`

## Membership
- `MEMBERSHIP-SYSTEM.md`
- `MEMBERSHIP-ACCESS-POLICY.md`
- `MEMBERSHIP-ENROLLMENT-STATE-MACHINE.md`
- `MEMBERSHIP-PLAN-VERSIONING-UPGRADE-SEMANTICS.md`
- `MEMBERSHIP-TEAMS-SEATS-ROLE-SYNC.md`
- `MEMBERSHIP-MIGRATION-SEMANTICS.md`
- `MEMBERSHIP-SEMANTIC-STATUS.md`

Membership core semantics are further locked by ADR-0013, ADR-0015, ADR-0016, ADR-0019 and ADR-0020.

## Automation / communication
- `FORMS-WORKFLOW-EXHAUSTIVE-SPEC.md`
- `CRON-JOB-BUILDER-EXHAUSTIVE-SPEC.md`
- `NOTIFICATION-SYSTEM-EXHAUSTIVE-SPEC.md`
- `EMAILS-BUILDER-EXHAUSTIVE-SPEC.md`
- `MESSAGE-CHAT-EXHAUSTIVE-SPEC.md`

## Integration / data movement
- `REST-API-BUILDER-EXHAUSTIVE-SPEC.md`
- `WEBHOOKS-CONNECTIONS-EXHAUSTIVE-SPEC.md`
- `IMPORT-EXPORT-EXHAUSTIVE-SPEC.md`

## Operations / protection
- `BACKUP-MANAGER-EXHAUSTIVE-SPEC.md`
- `BACKUP-PROVIDER-CERTIFICATION-MATRIX.md`
- `BACKUP-RESTORE-SEMANTICS.md`
- `RESET-MANAGER-EXHAUSTIVE-SPEC.md`
- `PROTECTOR-EXHAUSTIVE-SPEC.md`
- `WATERMARKER-MEDIA-RULES-EXHAUSTIVE-SPEC.md`
- `XML-RPC-MANAGER-EXHAUSTIVE-SPEC.md`

## Platform surfaces
- `PLATFORM-SURFACES-SPEC.md`
- `../PLATFORM/PLATFORM-SURFACE-AMENDMENTS-2026-08-27.md`
- `../PLATFORM/REMOTE-SERVICE-API-CONTRACT.md`
- `../PLATFORM/DOCUMENTATION-SUPPORT-RELEASE-IA.md`

The amendment/Remote Service contract supersede earlier local WPE-account password-form and Free-plugin external Pro auto-install assumptions.

---

# Exhaustive-spec rule

Every module must maintain, where applicable:
- list screen columns/filters/search/sort/bulk actions;
- create/edit tabs/sections;
- each field/toggle/selector/action and default;
- conditional visibility/dependencies;
- normalization/validation/sanitization;
- publish/archive/delete/run semantics;
- permissions/re-auth;
- revisions/import/export;
- health/audit/observability;
- empty/loading/error/offline/degraded/read-only/expired states;
- cross-module dependencies;
- asset isolation;
- accessibility/keyboard behavior;
- performance safeguards;
- destructive safeguards;
- acceptance tests.

If future research or implementation uncovers a missing meaningful option, update the exhaustive spec before or in the same coherent change. The implementation must not silently invent product semantics.

---

# Architecture/readiness sources

- `../IMPLEMENTATION-READINESS-MATRIX.md` — technical blockers; Exhaustive ≠ Technical Ready.
- `../OPEN-DECISIONS-REGISTER.md` — unresolved decisions and evidence requirements.
- `/DEVELOPMENT-CONSENT.md` — owner consent boundary.
- `../DECISIONS/` — Accepted/Proposed ADRs.

# Current conclusion

**Product-option planning gate:** reached for all 31 planned surfaces.  
**Technical acceptance gate:** not reached globally.  
**Executable development:** not started, not authorized.

The next Phase 0 work is no longer broad option enumeration. It is resolving/strengthening architecture, schema, compatibility, security, performance, provider-certification and migration evidence plans without executing code.