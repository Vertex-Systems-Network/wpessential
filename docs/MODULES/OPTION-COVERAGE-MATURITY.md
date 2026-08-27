# WPEssential — Module Option Coverage & Maturity Ledger

Status: **Phase 0 planning source of truth / no development authorized**  
Date: 2026-08-27

## Purpose

The earlier statement “31/31 behaviorally specified” is not the final planning bar.

The owner requires **every module's smallest practical option to be planned before development**. Therefore the maturity model is now stricter:

1. **Inventory** — screens/features/options named.
2. **Behavioral** — defaults, validation, permissions, lifecycle/failure semantics documented.
3. **Exhaustive option spec** — screen-by-screen controls, fields, toggles, conditional visibility, defaults, allowed values, destructive behavior, row/bulk actions, empty/loading/error states and integration hooks documented at implementable product level.
4. **Accepted semantics** — product/architecture ambiguities resolved by ADR where necessary.
5. **Implementation-ready technically** — physical schema/dependencies/performance/security evidence accepted.
6. **Authorized** — explicit owner development consent exists.
7. **Implemented** — source exists.
8. **Verified** — applicable quality gates pass.

A module may be Exhaustive but still BLOCKED technically and unauthorized.

## Current coverage

| # | Surface | Current option maturity | Primary exhaustive/detailed source | Remaining planning before technical implementation |
|---:|---|---|---|---|
| 1 | Custom Post Types Builder | Exhaustive | `FREE-CPT-TAXONOMY-EXHAUSTIVE-SPEC.md` | compatibility/schema/toolchain evidence |
| 2 | Taxonomy Builder | Exhaustive | `FREE-CPT-TAXONOMY-EXHAUSTIVE-SPEC.md` | compatibility/schema/toolchain evidence |
| 3 | Custom Fields Builder | Exhaustive | `CUSTOM-FIELDS-EXHAUSTIVE-SPEC.md` | field storage/migration/runtime benchmark |
| 4 | Relations Builder | Exhaustive | `RELATIONS-STATUS-EXHAUSTIVE-SPEC.md` | physical relation schema/concurrency benchmark |
| 5 | Status Manager | Exhaustive | `RELATIONS-STATUS-EXHAUSTIVE-SPEC.md` | WordPress/custom domain-state compatibility tests later |
| 6 | Custom Query Builder | Exhaustive | `QUERY-BUILDER-EXHAUSTIVE-SPEC.md` | Query AST/compiler/cost benchmark |
| 7 | Custom Tables Builder | Exhaustive | `CUSTOM-TABLES-ADMIN-COLUMNS-EXHAUSTIVE-SPEC.md` | migration/DDL benchmark |
| 8 | Admin Columns Builder | Exhaustive | `CUSTOM-TABLES-ADMIN-COLUMNS-EXHAUSTIVE-SPEC.md` | list-table adapters/performance proof |
| 9 | Dynamic Listings / Templates | Exhaustive | `DYNAMIC-LISTINGS-EXHAUSTIVE-SPEC.md` | renderer schema/cache/builder adapter evidence |
| 10 | Dashboard Widgets Manager | Exhaustive | `ADMIN-DASHBOARD-MENU-SETTINGS-EXHAUSTIVE-SPEC.md` | WP dashboard integration compatibility |
| 11 | Custom Admin Menu Builder | Exhaustive | `ADMIN-DASHBOARD-MENU-SETTINGS-EXHAUSTIVE-SPEC.md` | menu conflict/recovery tests later |
| 12 | Settings Page Builder | Exhaustive | `ADMIN-DASHBOARD-MENU-SETTINGS-EXHAUSTIVE-SPEC.md` | field/schema + options storage evidence |
| 13 | Frontend Dashboard Builder | Exhaustive | `DASHBOARD-PROFILE-ROLES-EXHAUSTIVE-SPEC.md` | route/policy/component renderer evidence |
| 14 | User Profile Builder | Exhaustive | `DASHBOARD-PROFILE-ROLES-EXHAUSTIVE-SPEC.md` | protected user-meta matrix/runtime evidence |
| 15 | Membership System | Exhaustive + Accepted semantics | `MEMBERSHIP-SYSTEM.md` + Membership semantic docs/ADRs | runtime schema/cache/providers/protected-file evidence |
| 16 | Builder Widgets Builder | Exhaustive | `BUILDER-WIDGETS-EXHAUSTIVE-SPEC.md` | adapter certification/version tests |
| 17 | Forms & Workflow Builder | Behavioral — exhaustive gap | suite spec | **create exhaustive option spec** |
| 18 | Cron Job Builder | Behavioral — exhaustive gap | suite spec | **create exhaustive option spec** |
| 19 | Notification System | Behavioral — exhaustive gap | suite spec | **create exhaustive option spec** |
| 20 | Emails Builder | Behavioral — exhaustive gap | suite spec | **create exhaustive option spec** |
| 21 | Message & Chat System | Behavioral — exhaustive gap | suite spec | **create exhaustive option spec** |
| 22 | REST API Builder | Behavioral — exhaustive gap | integration suite spec | **create exhaustive option spec** |
| 23 | Webhooks & Connections Manager | Behavioral — exhaustive gap | integration suite spec | **create exhaustive option spec** |
| 24 | Backup Manager | Detailed semantics, option UI gap | provider matrix + restore semantics | **create exhaustive builder/settings option spec** |
| 25 | Reset Manager | Behavioral — exhaustive gap | operations suite spec | **create exhaustive option spec** |
| 26 | Import / Export | Deep migration architecture, option UI gap | migration/package/source-adapter docs | **create exhaustive run/mapping UI option spec** |
| 27 | Protector | Behavioral — exhaustive gap | operations suite/security docs | **create exhaustive option spec** |
| 28 | Watermarker / Media Rules | Behavioral — exhaustive gap | operations suite spec | **create exhaustive option spec** |
| 29 | XML-RPC Manager | Behavioral — exhaustive gap | operations suite spec | **create exhaustive option spec** |
| 30 | Role & Capability Manager | Exhaustive | `DASHBOARD-PROFILE-ROLES-EXHAUSTIVE-SPEC.md` | anti-lockout/capability runtime tests later |
| 31 | Platform surfaces / Account / Support / Docs | Detailed/Exhaustive platform contract | `PLATFORM-SURFACES-SPEC.md`, amendment, Remote Service, Docs/Support IA | service API schemas + executable evidence |

## Planning gate

No module may be called **product-spec complete** until it reaches Exhaustive option spec maturity or a documented reason explains why a smaller surface has no further meaningful controls.

For every exhaustive spec, cover at minimum:
- list screen columns/filters/search/sort/bulk actions;
- create/edit screen tabs/sections;
- every field/control and default;
- conditional visibility/dependencies;
- validation/sanitization/normalization;
- save/publish/archive/delete behavior;
- preview/test/run behavior;
- permissions/re-auth;
- revision/import/export;
- status/health/observability;
- empty/loading/error/offline/degraded/read-only/expired states;
- cross-module shortcuts;
- asset loading;
- accessibility/keyboard states;
- performance guardrails;
- destructive safeguards;
- future acceptance tests.

## Immediate planning queue

Fill exhaustive option specs in this order because dependencies/risks are highest:
1. Forms & Workflow;
2. Cron;
3. Notifications;
4. Email;
5. REST API;
6. Webhooks & Connections;
7. Import / Export;
8. Backup;
9. Reset;
10. Protector;
11. Chat;
12. Watermark;
13. XML-RPC.

After each spec is added, this ledger must be updated from Behavioral → Exhaustive.

Development remains prohibited until explicit owner consent under ADR-0014 regardless of maturity.