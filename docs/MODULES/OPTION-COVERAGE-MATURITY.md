# WPEssential — Module Option Coverage & Maturity Ledger

Status: **Phase 0 planning source of truth / no development authorized**  
Date: 2026-08-28  
Expanded scope accepted by: ADR-0177.

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

# Current product-option coverage — 43/43 Exhaustive

The original scope reached 31/31 before the universal-system expansion. ADR-0177 added 12 reusable foundation surfaces after the WooCommerce Commerce OS / 100K-system audit. Historical 31/31 statements remain true for the earlier scope; the current canonical product-option denominator is **43**.

## Original 31 surfaces

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
| 25 | Reset Manager | **Exhaustive** | `RESET-MANAGER-EXHAUSTIVE-SPEC.md` | destructive workflow/recovery/Multisite evidence |
| 26 | Import / Export | **Exhaustive** | `IMPORT-EXPORT-EXHAUSTIVE-SPEC.md` + migration/package docs | source-adapter fixtures/rollback evidence |
| 27 | Protector | **Exhaustive** | `PROTECTOR-EXHAUSTIVE-SPEC.md` | interception/rate-limit/recovery/proxy evidence |
| 28 | Watermarker / Media Rules | **Exhaustive** | `WATERMARKER-MEDIA-RULES-EXHAUSTIVE-SPEC.md` | image-editor/offload/format certification |
| 29 | XML-RPC Manager | **Exhaustive** | `XML-RPC-MANAGER-EXHAUSTIVE-SPEC.md` | hook ordering/integration/network evidence |
| 30 | Role & Capability Manager | **Exhaustive** | `DASHBOARD-PROFILE-ROLES-EXHAUSTIVE-SPEC.md` | anti-lockout/capability runtime tests |
| 31 | Platform surfaces / Account / Support / Docs | **Exhaustive platform contract** | `PLATFORM-SURFACES-SPEC.md` + amendment + Remote Service + Docs/Support IA | service schemas/auth/signing/executable evidence |

## Universal foundation expansion — ADR-0177

All 12 foundations share the detailed option source:
`../SOLUTIONS/UNIVERSAL-FOUNDATIONS-EXHAUSTIVE-SPEC.md`.
Their Multisite behavior is mapped in `../SOLUTIONS/UNIVERSAL-FOUNDATIONS-MULTISITE-SCOPE-MATRIX.md`.

| # | Surface | Current option maturity | Strategic role | Remaining before technical implementation |
|---:|---|---|---|---|
| 32 | Solution Blueprint & Application Composer | **Exhaustive product behavior** | whole-application composition/install/upgrade/drift | Blueprint physical schema/package/compiler/evidence |
| 33 | Analytics, Event Tracking & Journey Intelligence | **Exhaustive product behavior** | behavioral/session/metric/funnel/attribution runtime | event-store/topology/privacy/performance evidence |
| 34 | Search & Indexing Engine | **Exhaustive product behavior** | full-text/facet/ranking/search index | backend/index/security/invalidation certification |
| 35 | Decision, Formula, Scoring & Ranking Studio | **Exhaustive product behavior** | deterministic typed calculations/scorecards/decision tables | expression/compiler/decimal/performance evidence |
| 36 | Ledger, Balance & Movement Engine | **Exhaustive product behavior** | immutable balances/movements/holds/reconciliation | physical transaction/concurrency/rebuild evidence |
| 37 | Resource Scheduling, Availability & Reservation Engine | **Exhaustive product behavior** | availability/capacity/atomic reservation | lock/topology/calendar/DST/concurrency evidence |
| 38 | Experience Placement & Personalization Manager | **Exhaustive product behavior** | reusable frontend/admin contextual placements | slot adapters/cache/assets/frequency evidence |
| 39 | Experimentation & Feature Rollout Manager | **Exhaustive product behavior** | assignment/exposure/experiments/rollout | statistics/instrumentation/cache/assignment evidence |
| 40 | Documents, Records & Template Generation | **Exhaustive product behavior** | governed generated documents/PDF/private records | renderer/font/protected-storage/version evidence |
| 41 | Data Sync, ETL & Integration Pipelines | **Exhaustive product behavior** | cursor/checkpoint/bidirectional synchronization | connector/idempotency/conflict/scale evidence |
| 42 | Geospatial, Location & Territory Engine | **Exhaustive product behavior** | geocode/radius/zone/territory semantics | spatial storage/query/provider/privacy evidence |
| 43 | AI Gateway, Knowledge & Copilot Studio | **Exhaustive product behavior** | provider/model/task/RAG/eval/Ability control plane | provider/runtime/privacy/eval/cost/security evidence |

---

# Expanded shared-service planning

ADR-0177 also accepts six non-sellable platform-service expansions; they are **not** extra module denominator rows:

1. Simulation & Historical Replay Service
2. Transaction / Saga Coordination Contract
3. Protected Asset Service generalized beyond Membership
4. Context Resolver
5. Money / Decimal / Unit Type Library
6. Approval Policy Profile

The WooCommerce Commerce Domain Adapter is an adapter pack, not another module denominator row.

---

# Current conclusion

**43/43 planned module/platform surfaces have reached the Phase 0 Exhaustive product-option bar.**

This means every current product surface has screen/option-level product behavior documented deeply enough that ordinary semantics should not need to be invented ad hoc during implementation.

It does **not** mean:
- all ADR/physical blockers are accepted;
- DB schemas/indexes/locks are benchmarked;
- runtime dependencies are proven;
- provider/domain adapters are certified;
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

If future research or implementation discovers a meaningful missing option, the applicable exhaustive spec is updated **before or in the same coherent change**. `43/43 Exhaustive` is not permission to silently invent new semantics later.

---

# Current planning expansion artifacts

- `docs/SOLUTIONS/README.md` — Solution Blueprint architecture.
- `docs/SOLUTIONS/FOUNDATIONAL-MODULE-GAP-PLAN.md` — reuse/gap analysis.
- `docs/SOLUTIONS/COMMERCE-OS-71-SYSTEM-AUDIT.md` — source 71-system buildability map.
- `docs/SOLUTIONS/REFERENCE-FLOW-AND-OPTION-PATTERNS.md` — 40 reusable system patterns.
- `docs/SOLUTIONS/UNIVERSAL-SYSTEM-CATALOG.md` — 160 curated reference systems.
- `docs/SOLUTIONS/100K-SYSTEM-SPACE.md` — 268,800 raw primary Blueprint combinations + validation model.
- `docs/SOLUTIONS/SYSTEM-BLUEPRINT-SPECIFICATION-STANDARD.md` — full Blueprint documentation bar.
- `docs/SOLUTIONS/UNIVERSAL-FOUNDATIONS-EXHAUSTIVE-SPEC.md` — F01–F12 options/flows.
- `docs/SOLUTIONS/UNIVERSAL-FOUNDATIONS-MULTISITE-SCOPE-MATRIX.md` — 12/12 expanded Multisite mapping.
- `docs/SOLUTIONS/WOOCOMMERCE-COMMERCE-DOMAIN-ADAPTER-EXHAUSTIVE-SPEC.md` — A01 Woo adapter.

## Development gate

**Development remains prohibited until explicit owner consent under ADR-0014, regardless of the 43/43 Exhaustive product-option result.**