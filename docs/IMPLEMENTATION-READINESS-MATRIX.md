# WPEssential — Implementation Readiness Matrix

Status: **Phase 0 planning / NO DEVELOPMENT CONSENT**  
Last synchronized: 2026-08-27

## Global rule

A surface can be Exhaustive and architecturally Accepted while still being technically unverified and unauthorized.

Implementation requires:
1. Exhaustive option specification;
2. relevant architecture/semantics Accepted;
3. physical schema/dependency/security/performance evidence;
4. acceptance-test plan;
5. compatibility/toolchain gates;
6. explicit owner consent under ADR-0014;
7. bounded implementation checkpoint.

Current owner consent: **NOT GRANTED**. Therefore **0/31 Authorized**.

## Shared blockers

| Area | State | Protocol |
|---|---|---|
| WP/PHP/DB compatibility | ADR-0002 Proposed | P-001 |
| UI/design system | ADR-0005 Proposed | P-002 |
| Job Service | ADR-0006 Proposed | P-003 |
| Definition Repository physical schema | ADR-0008 Proposed | P-004 |
| Secrets Vault crypto/key profile | ADR-0009 Proposed | P-005 |
| Free↔Pro compatibility runtime | ADR-0010 Proposed | P-006 |
| CI execution | ADR-0011 Proposed | P-007 |
| Build toolchain | ADR-0012 Proposed | P-008 |
| Owner development consent | ADR-0014 Accepted, consent absent | blocks all |

## Per-surface readiness

| # | Surface | Product options | Accepted/paper architecture | Remaining technical blockers | Authorized |
|---:|---|---|---|---|---|
| 1 | Custom Post Types Builder | Exhaustive | WP registration semantics specified | compatibility, Definition repo, UI/build, registration fixtures | No |
| 2 | Taxonomy Builder | Exhaustive | WP registration semantics specified | compatibility, Definition repo, UI/build, rewrite fixtures | No |
| 3 | Custom Fields Builder | Exhaustive | **ADR-0022 Accepted** | adapter schemas, scale/query/revision/migration benchmark, Vault | No |
| 4 | Relations Builder | Exhaustive | typed semantics + edge-table paper preference | physical indexes/cardinality/concurrency — P-010 | No |
| 5 | Status Manager | Exhaustive | **ADR-0038 Accepted** Post Status adapter + domain state machine | WP UI/migration adapters, state storage/history/concurrency | No |
| 6 | Custom Query Builder | Exhaustive | Query AST v1 paper model | provider compiler/cost/cache/security — P-009 | No |
| 7 | Custom Tables Builder | Exhaustive | **ADR-0023 Accepted** typed Migration Plan | DB compiler/version matrix, locking/backfill/recovery | No |
| 8 | Admin Columns Builder | Exhaustive | list-view/source semantics | list adapters, N+1/performance, inline/bulk write proof | No |
| 9 | Dynamic Listings/Templates | Exhaustive | **ADR-0035 + ADR-0039 Accepted** shared Component Blueprint + authorized listing runtime | Query integration, protected pagination, cache invalidation, builder/enhancement fixtures | No |
| 10 | Dashboard Widgets Manager | Exhaustive | product semantics specified | WP Dashboard compatibility, remote-content/iframe safety | No |
| 11 | Custom Admin Menu Builder | Exhaustive | **ADR-0037 Accepted** runtime discovery/transformation/safe mode | hook priority/plugin conflicts, site/network recovery/performance | No |
| 12 | Settings Page Builder | Exhaustive | **ADR-0036 Accepted** scoped value-document architecture | physical Options strategy, autoload, concurrency, multisite, Vault/REST | No |
| 13 | Frontend Dashboard Builder | Exhaustive | **ADR-0031 + ADR-0035 Accepted** route + component runtime | rewrite/permalink/multisite, IDOR/cache/assets/builder fixtures | No |
| 14 | User Profile Builder | Exhaustive | **ADR-0030 Accepted** identity/security boundaries | protected-meta registry, email/password/session/App Password/public-profile fixtures | No |
| 15 | Membership System | Exhaustive | **ADRs 0013/15/16/19/20/24 Accepted** | runtime schema/cache/protected files/providers/concurrency/privacy — P-012 | No |
| 16 | Builder Widgets Builder | Exhaustive | **ADR-0035 Accepted** shared Component Blueprint | renderer/nesting/bindings/assets/accessibility and builder certification | No |
| 17 | Forms & Workflow Builder | Exhaustive | **ADR-0025 Accepted** Entry model + Workflow paper runtime | Entry schema/projections, Workflow/Job runtime — P-011 | No |
| 18 | Cron Job Builder | Exhaustive | Job/Cron product semantics | ADR-0006 Job Service, DST/overlap/runtime fixtures | No |
| 19 | Notification System | Exhaustive | **ADR-0026 Accepted** | tables/indexes/fan-out/dedupe/channel certification | No |
| 20 | Emails Builder | Exhaustive | **ADR-0029 Accepted** compiled Email renderer/delivery separation | renderer/inliner/client/security/core/provider certification | No |
| 21 | Message & Chat System | Exhaustive | **ADR-0027 Accepted** | indexes/search/transport/private assets/revocation scale proof | No |
| 22 | REST API Builder | Exhaustive | **ADR-0028 Accepted** | compiler/auth/rate/CORS/cache/fuzz/scale evidence | No |
| 23 | Webhooks & Connections | Exhaustive | **ADR-0040 Accepted** Safe HTTP + verified webhook + Event Inbox | SSRF/rebinding/redirect/signature/replay schema/provider/OAuth fixtures | No |
| 24 | Backup Manager | Exhaustive | **ADR-0021 + ADR-0033 Accepted** encryption recovery + multipart logical bundle | physical part/DB/crypto/provider/restore certification — P-013 | No |
| 25 | Reset Manager | Exhaustive | destructive/recovery semantics specified | Backup restore-point runtime, execution journal/atomicity/recovery/multisite | No |
| 26 | Import / Export | Exhaustive | **ADR-0041 Accepted** durable run/checkpoint/identity-map/rollback | physical run schema, crash/resume, source fixtures, media safety | No |
| 27 | Protector | Exhaustive | policy/recovery semantics specified | interception/rate/proxy/login/header security runtime | No |
| 28 | Watermarker / Media Rules | Exhaustive | non-destructive derivative semantics | derivative identity/storage, image-editor/offload/format/memory/batch | No |
| 29 | XML-RPC Manager | Exhaustive | endpoint/method distinction specified | method inventory/hooks/parser/Jetpack/mobile/multisite proof | No |
| 30 | Role & Capability Manager | Exhaustive | **ADR-0032 Accepted** anti-lockout/recovery architecture | capability classifier, self/last-admin, multisite, WP-CLI recovery fixtures | No |
| 31 | Platform Account/Docs/Support/Diagnostics | Exhaustive | **ADRs 0017/0018/0034 Accepted architecture/profile** | entitlement/updater exact crypto/client, OAuth token lifecycle, service/support schemas | No |

## Accepted architecture does not equal technical readiness

Examples:
- ADR-0035 establishes one portable Component Blueprint; it does not prove Elementor/Gutenberg/Bricks/WPBakery adapters.
- ADR-0036 establishes Settings scope/inheritance; it does not choose/benchmark final physical Options storage.
- ADR-0037 establishes Admin Menu safety semantics; it does not prove every plugin hook-order conflict.
- ADR-0038 establishes Status ownership; it does not implement transition storage/concurrency.
- ADR-0039 establishes protected Listing semantics; it does not prove cache/pagination performance.
- ADR-0040 establishes central external-I/O security; it does not certify SSRF/webhook/provider adapters.
- ADR-0041 establishes resumable imports/rollback classes; it does not prove crash recovery or source migrations.

## Recommended implementation order after future consent

1. resolve P-001…P-008 platform blockers;
2. Platform Kernel + Module Registry + Definition Repository + Policy/Abilities/Assets/Audit;
3. CPT + Taxonomy Free;
4. Fields → Relations → Query → Tables/Columns → Component Blueprint → Listings/Status;
5. Settings/Admin Menu/Dashboard/Profile/Roles;
6. Membership runtime;
7. Form Entry + Workflow/Jobs + Notifications/Email;
8. REST + Connections/Event Inbox + Import;
9. Backup/Reset/Protection/Media/XML-RPC;
10. Chat/realtime adapters;
11. AI composition only over certified Abilities/Blueprints;
12. ecosystem SDK/provider scale.

## Current conclusion

**Product specification:** 31/31 Exhaustive.  
**Architecture:** many semantics Accepted through ADR-0041; physical/runtime evidence incomplete.  
**Implemented:** none.  
**Verified runtime:** none.  
**Authorized:** 0/31.

Allowed work remains planning/research/documentation only until explicit owner development consent.