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
| Job Service concrete backend | ADR-0059 semantics Accepted; ADR-0006 Action Scheduler adapter Proposed | P-003 |
| Definition Repository physical DDL/indexes | ADR-0049 accepted relational shape; exact DDL pending | P-004 |
| Secrets Vault crypto implementation | ADR-0048 accepted hierarchy; exact envelope/interoperability pending | P-005 |
| Free↔Pro compatibility runtime | ADR-0010 Proposed | P-006 |
| CI execution | ADR-0011 Proposed | P-007 |
| Build toolchain | ADR-0012 Proposed | P-008 |
| Owner development consent | ADR-0014 Accepted, consent absent | blocks all |

## Per-surface readiness

| # | Surface | Product options | Accepted/paper architecture | Remaining technical blockers | Authorized |
|---:|---|---|---|---|---|
| 1 | Custom Post Types Builder | Exhaustive | WP registration semantics specified | compatibility, Definition repo DDL, UI/build, registration fixtures | No |
| 2 | Taxonomy Builder | Exhaustive | WP registration semantics specified | compatibility, Definition repo DDL, UI/build, rewrite fixtures | No |
| 3 | Custom Fields Builder | Exhaustive | ADR-0022 Accepted | adapter schemas, scale/query/revision/migration, Vault runtime | No |
| 4 | Relations Builder | Exhaustive | typed semantics + edge-table paper preference | indexes/cardinality/concurrency — P-010 | No |
| 5 | Status Manager | Exhaustive | ADR-0038 Accepted | WP UI/migration adapters, state storage/history/concurrency | No |
| 6 | Custom Query Builder | Exhaustive | Query AST v1 paper model | provider compiler/cost/cache/security — P-009 | No |
| 7 | Custom Tables Builder | Exhaustive | ADR-0023 Accepted | DB compiler/version matrix, locking/backfill/recovery | No |
| 8 | Admin Columns Builder | Exhaustive | list-view/source semantics | adapters, N+1/performance, inline/bulk write proof | No |
| 9 | Dynamic Listings/Templates | Exhaustive | ADR-0035 + ADR-0039 Accepted | Query integration, protected pagination, cache invalidation, builder fixtures | No |
| 10 | Dashboard Widgets Manager | Exhaustive | ADR-0051 Accepted content-trust architecture | WP Dashboard compatibility, structured remote schema, iframe profile, XSS/CSP/assets | No |
| 11 | Custom Admin Menu Builder | Exhaustive | ADR-0037 Accepted | hook priority/plugin conflicts, site/network recovery/performance | No |
| 12 | Settings Page Builder | Exhaustive | ADR-0036 Accepted | physical Options strategy, autoload, concurrency, multisite, Vault/REST | No |
| 13 | Frontend Dashboard Builder | Exhaustive | ADR-0031 + ADR-0035 Accepted | rewrite/permalink/multisite, IDOR/cache/assets/builder fixtures | No |
| 14 | User Profile Builder | Exhaustive | ADR-0030 Accepted | protected-meta registry, email/password/session/App Password/public profile | No |
| 15 | Membership System | Exhaustive | ADRs 0013/15/16/19/20/24 + ADR-0057 Accepted | schema/cache/protected files, MB0–MB5 billing provider certification, reconciliation, identity resolution, concurrency/privacy — P-012 | No |
| 16 | Builder Widgets Builder | Exhaustive | ADR-0035 Accepted | renderer/nesting/bindings/assets/accessibility/builder certification | No |
| 17 | Forms & Workflow Builder | Exhaustive | ADR-0025 + ADR-0059 Job semantics + Workflow paper runtime | Entry schema/projections, Workflow state execution, Job adapter/fairness — P-003/P-011 | No |
| 18 | Cron Job Builder | Exhaustive | ADR-0059 JobService semantics + Cron product semantics | Action Scheduler backend, WP-Cron/WP-CLI runner, DST/overlap/fairness fixtures — P-003 | No |
| 19 | Notification System | Exhaustive | ADR-0026 + ADR-0058 + ADR-0059 Accepted boundaries | persistence/indexes, fan-out/dedupe, ET provider certification, Job backpressure/fairness | No |
| 20 | Emails Builder | Exhaustive | ADR-0029 + ADR-0058 Accepted | renderer/inliner/client compatibility, Delivery/Attempt/Event schema, ET0–ET5 provider certification, webhook/security evidence | No |
| 21 | Message & Chat System | Exhaustive | ADR-0027 Accepted | indexes/search/transport/private assets/revocation scale | No |
| 22 | REST API Builder | Exhaustive | ADR-0028 Accepted | compiler/auth/rate/CORS/cache/fuzz/scale evidence | No |
| 23 | Webhooks & Connections | Exhaustive | ADR-0040 + ADR-0055 + ADR-0059 composition | provider I0–I5 auth/read/write/event certification, Event Inbox DDL, Job retry/backpressure evidence | No |
| 24 | Backup Manager | Exhaustive | ADR-0021/0033/0043/0053/0056/0059 composition | physical bundle/Remote Copy schema, C0–C4 provider/restore + Job chunk/fairness certification — P-003/P-013 | No |
| 25 | Reset Manager | Exhaustive | ADR-0047 + ADR-0059 composition | recovery-store schema, destructive Job checkpoints/crash recovery, adapters, multisite | No |
| 26 | Import / Export | Exhaustive | ADR-0041 + ADR-0059 composition | run schema, checkpoint Job execution, crash/resume, source/media fixtures | No |
| 27 | Protector | Exhaustive | ADR-0045 Accepted | hook order, atomic rate store, proxy/login/header fixtures | No |
| 28 | Watermarker / Media Rules | Exhaustive | ADR-0046 + ADR-0059 composition | registry, image-editor/offload plus chunk/concurrency/load evidence | No |
| 29 | XML-RPC Manager | Exhaustive | ADR-0052 Accepted layered enforcement | method inventory/hooks/parser/complete-deny/Jetpack/mobile/multisite | No |
| 30 | Role & Capability Manager | Exhaustive | ADR-0032 Accepted | classifier, self/last-recovery, multisite, WP-CLI recovery | No |
| 31 | Platform Account/Docs/Support/Diagnostics | Exhaustive | ADR-0034/0042/0044/0050/0054/0060 Accepted | OpenAPI/service implementation, OAuth/token lifecycle, key custody/rotation, production TUF client, support API, privacy/retention/log-redaction/diagnostics evidence | No |

## Cross-cutting accepted architecture that still needs evidence

- ADR-0048 Vault: VRK → per-secret DEK → key slots; no plaintext fallback.
- ADR-0049 Definition Repository: Definitions + immutable Revisions + revision-aware Dependencies.
- ADR-0053 Backup Providers: protocol family + provider capability profile; C3 normal Support gate; current certified count 0.
- ADR-0054 Remote Service: account/site/entitlement/catalog/support/docs/release resources have separate trust semantics.
- ADR-0055 Connections: I0–I5 capability certification; `Connected` does not imply writes/events.
- ADR-0056 Remote Copy: manifest-last, provider Commit Point, truthful retention/delete/restore identity.
- ADR-0057 Membership Billing: verified commercial source facts feed reconciliation + Membership policy; provider status never directly owns Enrollment/Entitlement state.
- ADR-0058 Email Delivery: submission/transport acceptance, receiving-server delivery, failure/complaint/suppression and engagement are separate evidence; ET0–ET5 provider profiles govern support claims.
- ADR-0059 Job Service: backend-neutral Job/Attempt/Runner semantics, at-least-once, explicit idempotency, urgency/fairness, resource/concurrency control, chunking/backpressure and cooperative cancellation. Concrete Action Scheduler mapping remains P-003.
- ADR-0060 Remote Service Privacy: Free activation sends nothing; account link is purpose-scoped and is not telemetry consent; diagnostics require separate approval; RR0–RR6 retention/disconnect/deletion semantics are explicit.

No surface may skip evidence merely because an ADR is Accepted.

## Recommended implementation order after future consent

1. resolve P-001/P-002 and P-004…P-008 platform prerequisites plus P-003 Job adapter proof;
2. Platform Kernel + Module Registry + Definition Repository + Policy/Abilities/Assets/Audit + Vault + JobService;
3. CPT + Taxonomy Free;
4. Fields → Relations → Query → Tables/Columns → Component Blueprint → Listings/Status;
5. Settings/Admin Menu/Dashboard/Profile/Roles/Dashboard Widgets;
6. Membership runtime → Manual/Woo one-time → Woo Subscriptions/SureCart MB certification;
7. Form Entry + Workflow/Jobs → Notifications → Email renderer → ET provider profiles;
8. REST + Connections/Event Inbox + Import;
9. Backup core → Remote Copy → reference provider adapters → Reset/Protection/Media/XML-RPC;
10. Chat/realtime adapters;
11. Account/Support/Updater service integration under ADR-0060 privacy/retention contract;
12. AI composition only over certified Abilities/Blueprints;
13. ecosystem SDK/provider scale.

## Current conclusion

**Product specification:** 31/31 Exhaustive.  
**Architecture:** accepted decisions through ADR-0060; physical/runtime evidence incomplete.  
**Implemented:** none.  
**Verified runtime:** none.  
**Authorized:** 0/31.

Allowed work remains planning/research/documentation only until explicit owner development consent.
