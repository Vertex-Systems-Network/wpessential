# WPEssential — Module Option Coverage & Maturity Ledger

Status: **Phase 0 planning source of truth / no development authorized**  
Date: 2026-08-27

## Purpose

The owner requires **every module's smallest practical option to be planned before development**. The planning maturity model is therefore:

1. **Inventory** — screens/features/options named.
2. **Behavioral** — defaults, validation, permissions, lifecycle/failure semantics documented.
3. **Exhaustive option spec** — screen-by-screen controls, fields, toggles, conditional visibility, defaults, allowed values, destructive behavior, row/bulk actions, empty/loading/error states and integration hooks documented at implementable product level.
4. **Accepted semantics** — product/architecture ambiguities resolved by ADR where necessary.
5. **Implementation-ready technically** — physical schema/dependencies/performance/security evidence accepted.
6. **Authorized** — explicit owner development consent exists.
7. **Implemented** — source exists.
8. **Verified** — applicable quality gates pass.

A module can be **Exhaustive** while still technically BLOCKED and unauthorized.

---

# Current product-option coverage — 31/31 Exhaustive

| # | Surface | Current option maturity | Primary exhaustive/detailed source | Remaining before technical implementation |
|---:|---|---|---|---|
| 1 | Custom Post Types Builder | **Exhaustive** | `FREE-CPT-TAXONOMY-EXHAUSTIVE-SPEC.md` | compatibility/schema/toolchain evidence |
| 2 | Taxonomy Builder | **Exhaustive** | `FREE-CPT-TAXONOMY-EXHAUSTIVE-SPEC.md` | compatibility/schema/toolchain evidence |
| 3 | Custom Fields Builder | **Exhaustive** | `CUSTOM-FIELDS-EXHAUSTIVE-SPEC.md` | storage/migration/runtime benchmark |
| 4 | Relations Builder | **Exhaustive** | `RELATIONS-STATUS-EXHAUSTIVE-SPEC.md` | physical relation schema/concurrency benchmark |
| 5 | Status Manager | **Exhaustive** | `RELATIONS-STATUS-EXHAUSTIVE-SPEC.md` | WordPress/domain-state compatibility evidence |
| 6 | Custom Query Builder | **Exhaustive** | `QUERY-BUILDER-EXHAUSTIVE-SPEC.md` | Query AST/compiler/cost benchmark |
| 7 | Custom Tables Builder | **Exhaustive** | `CUSTOM-TABLES-ADMIN-COLUMNS-EXHAUSTIVE-SPEC.md` | migration/DDL benchmark |
| 8 | Admin Columns Builder | **Exhaustive** | `CUSTOM-TABLES-ADMIN-COLUMNS-EXHAUSTIVE-SPEC.md` | list-table adapters/performance proof |
| 9 | Dynamic Listings / Templates | **Exhaustive** | `DYNAMIC-LISTINGS-EXHAUSTIVE-SPEC.md` | renderer schema/cache/builder evidence |
| 10 | Dashboard Widgets Manager | **Exhaustive** | `ADMIN-DASHBOARD-MENU-SETTINGS-EXHAUSTIVE-SPEC.md` | WP dashboard compatibility evidence |
| 11 | Custom Admin Menu Builder | **Exhaustive** | `ADMIN-DASHBOARD-MENU-SETTINGS-EXHAUSTIVE-SPEC.md` | conflict/recovery evidence |
| 12 | Settings Page Builder | **Exhaustive** | `ADMIN-DASHBOARD-MENU-SETTINGS-EXHAUSTIVE-SPEC.md` | option/storage schema evidence |
| 13 | Frontend Dashboard Builder | **Exhaustive** | `DASHBOARD-PROFILE-ROLES-EXHAUSTIVE-SPEC.md` | route/policy/renderer evidence |
| 14 | User Profile Builder | **Exhaustive** | `DASHBOARD-PROFILE-ROLES-EXHAUSTIVE-SPEC.md` | protected-meta/runtime evidence |
| 15 | Membership System | **Exhaustive + Accepted core semantics** | `MEMBERSHIP-SYSTEM.md` + Membership semantic docs/ADRs | runtime schema/cache/providers/protected-file evidence |
| 16 | Builder Widgets Builder | **Exhaustive** | `BUILDER-WIDGETS-EXHAUSTIVE-SPEC.md` | builder adapter certification/version tests |
| 17 | Forms & Workflow Builder | **Exhaustive** | `FORMS-WORKFLOW-EXHAUSTIVE-SPEC.md` | runtime schemas/job/action evidence |
| 18 | Cron Job Builder | **Exhaustive** | `CRON-JOB-BUILDER-EXHAUSTIVE-SPEC.md` | Job Service/runner coexistence evidence |
| 19 | Notification System | **Exhaustive** | `NOTIFICATION-SYSTEM-EXHAUSTIVE-SPEC.md` | channel/provider/runtime persistence evidence |
| 20 | Emails Builder | **Exhaustive** | `EMAILS-BUILDER-EXHAUSTIVE-SPEC.md` | email renderer/client/provider certification |
| 21 | Message & Chat System | **Exhaustive** | `MESSAGE-CHAT-EXHAUSTIVE-SPEC.md` | runtime schema/transport/search evidence |
| 22 | REST API Builder | **Exhaustive** | `REST-API-BUILDER-EXHAUSTIVE-SPEC.md` | endpoint compiler/auth/performance evidence |
| 23 | Webhooks & Connections Manager | **Exhaustive** | `WEBHOOKS-CONNECTIONS-EXHAUSTIVE-SPEC.md` | OAuth/provider/SSRF/idempotency evidence |
| 24 | Backup Manager | **Exhaustive** | `BACKUP-MANAGER-EXHAUSTIVE-SPEC.md` + provider/restore/encryption docs | archive/crypto/provider/restore certification |
| 25 | Reset Manager | **Exhaustive** | `RESET-MANAGER-EXHAUSTIVE-SPEC.md` | destructive workflow/recovery/multisite evidence |
| 26 | Import / Export | **Exhaustive** | `IMPORT-EXPORT-EXHAUSTIVE-SPEC.md` + migration/package docs | source-adapter fixtures/rollback evidence |
| 27 | Protector | **Exhaustive** | `PROTECTOR-EXHAUSTIVE-SPEC.md` | interception/rate-limit/recovery/proxy evidence |
| 28 | Watermarker / Media Rules | **Exhaustive** | `WATERMARKER-MEDIA-RULES-EXHAUSTIVE-SPEC.md` | image-editor/offload/format certification |
| 29 | XML-RPC Manager | **Exhaustive** | `XML-RPC-MANAGER-EXHAUSTIVE-SPEC.md` | hook ordering/integration/network evidence |
| 30 | Role & Capability Manager | **Exhaustive** | `DASHBOARD-PROFILE-ROLES-EXHAUSTIVE-SPEC.md` | anti-lockout/capability runtime tests |
| 31 | Platform surfaces / Account / Support / Docs | **Exhaustive platform contract** | `PLATFORM-SURFACES-SPEC.md` + amendment + Remote Service + Docs/Support IA | service schemas/auth/signing/executable evidence |

## Current conclusion

**31/31 planned module/platform surfaces have now reached the Phase 0 Exhaustive product-option bar.**

This means every known surface has screen/option-level product behavior documented deeply enough that implementation should not need to invent ordinary product semantics ad hoc.

It does **not** mean:
- all ADR blockers are accepted;
- DB schemas are benchmarked;
- runtime dependencies are proven;
- provider integrations are certified;
- build/test toolchain has executed;
- code exists;
- development is authorized;
- production readiness exists.

---

# Exhaustive specification minimum

Every module remains responsible for documenting/maintaining:
- list screen columns/filters/search/sort/bulk actions;
- create/edit tabs/sections;
- every known field/control and default;
- conditional visibility/dependencies;
- validation/sanitization/normalization;
- save/publish/archive/delete behavior;
- preview/test/run behavior;
- permissions/re-auth;
- revision/import/export;
- status/health/observability;
- empty/loading/error/offline/degraded/read-only/expired states;
- cross-module shortcuts/dependencies;
- asset loading;
- accessibility/keyboard states;
- performance guardrails;
- destructive safeguards;
- future acceptance tests.

If future research or implementation discovers a meaningful missing option, the applicable exhaustive spec is updated **before or in the same coherent change**. `31/31 Exhaustive` is not permission to silently invent new semantics later.

---

# Next planning maturity gate

Product-option enumeration is no longer the main Phase 0 gap. Next planning work focuses on moving critical modules/services from **Exhaustive** toward **Accepted semantics / Technical Ready**, without writing executable implementation.

Priority planning queue:
1. resolve remaining non-executable semantic ADRs;
2. maintain exact module dependency/data ownership/capability/event registries;
3. close remote service/account/entitlement/update schemas on paper;
4. close Definition Repository/Query/Relation/Workflow schema alternatives enough to prepare consent-gated benchmarks;
5. close Membership cache/provider/protected-file certification plans;
6. close Backup archive/provider/restore certification plans;
7. finalize compatibility/build/CI spike protocols without executing them;
8. keep readiness/open-decisions/checkpoint/PR synchronized.

## Development gate

**Development remains prohibited until explicit owner consent under ADR-0014, regardless of the 31/31 Exhaustive product-option result.**