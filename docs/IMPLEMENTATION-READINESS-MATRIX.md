# WPEssential — Implementation Readiness Matrix

Status: **Phase 0 planning / NO DEVELOPMENT CONSENT**  
Last synchronized: 2026-08-28

## Global rule

A surface can be Exhaustive and architecturally Accepted while still being technically unverified and unauthorized.

Implementation requires exhaustive option specification, accepted semantics, physical/runtime/security/performance evidence, acceptance tests, platform/toolchain gates, explicit owner consent under ADR-0014 and a bounded implementation checkpoint.

Current owner consent: **NOT GRANTED**. Therefore **0/31 Authorized**.

## Shared blockers

| Area | State | Protocol |
|---|---|---|
| WP/PHP/DB compatibility | ADR-0002 Proposed | P-001 |
| UI/design system | ADR-0005 Proposed | P-002 |
| Job Service concrete backend | ADR-0059 + ADR-0068 semantics/packaging Accepted; Action Scheduler runtime still Proposed | P-003 |
| Definition Repository physical DDL/indexes | ADR-0049 accepted relational shape; exact DDL pending | P-004 |
| Secrets Vault implementation | ADR-0048 accepted hierarchy; exact envelope/interoperability pending | P-005 |
| Free↔Pro runtime | ADR-0010 Proposed | P-006 |
| CI | ADR-0011 Proposed | P-007 |
| Build toolchain | ADR-0012 Proposed | P-008 |
| Owner consent | ADR-0014 Accepted, consent absent | blocks all |

## Per-surface readiness

| # | Surface | Product options | Accepted/paper architecture | Remaining technical blockers | Authorized |
|---:|---|---|---|---|---|
| 1 | Custom Post Types Builder | Exhaustive | WP registration semantics | compatibility, Definition DDL, UI/build, fixtures | No |
| 2 | Taxonomy Builder | Exhaustive | WP registration semantics | compatibility, Definition DDL, UI/build, rewrite fixtures | No |
| 3 | Custom Fields Builder | Exhaustive | ADR-0022 | storage/scale/query/revision/migration/Vault runtime | No |
| 4 | Relations Builder | Exhaustive | typed edge semantics | indexes/cardinality/concurrency — P-010 | No |
| 5 | Status Manager | Exhaustive | ADR-0038 | WP UI/migration/state history/concurrency | No |
| 6 | Custom Query Builder | Exhaustive | Query AST paper model | compiler/cost/cache/security — P-009 | No |
| 7 | Custom Tables Builder | Exhaustive | ADR-0023 | DB compiler/version/locking/backfill/recovery | No |
| 8 | Admin Columns Builder | Exhaustive | list/source semantics | adapters/N+1/performance/write proof | No |
| 9 | Dynamic Listings/Templates | Exhaustive | ADR-0035 + ADR-0039 | Query/protected pagination/cache/builder fixtures | No |
| 10 | Dashboard Widgets Manager | Exhaustive | ADR-0051 | Dashboard compatibility/remote schema/iframe/XSS/CSP | No |
| 11 | Custom Admin Menu Builder | Exhaustive | ADR-0037 | hook conflicts/site-network recovery/performance | No |
| 12 | Settings Page Builder | Exhaustive | ADR-0036 | physical storage/autoload/concurrency/multisite/Vault/REST | No |
| 13 | Frontend Dashboard Builder | Exhaustive | ADR-0031 + ADR-0035 | routing/multisite/IDOR/cache/assets/builder | No |
| 14 | User Profile Builder | Exhaustive | ADR-0030 | protected meta/identity/credential/session evidence | No |
| 15 | Membership System | Exhaustive | ADRs 0013/15/16/19/20/24 + 0057 + 0062 + 0066 | Enrollment schema/cache/files; version-scoped Manual/Woo/SureCart MB0–MB5 certification; HPOS/API profiles; identity/refunds/reconciliation/concurrency/privacy — P-012 | No |
| 16 | Builder Widgets Builder | Exhaustive | ADR-0035 | renderer/nesting/bindings/assets/accessibility/certification | No |
| 17 | Forms & Workflow Builder | Exhaustive | ADR-0025 + ADR-0059 + ADR-0068 + Workflow paper runtime | Entry schema/Workflow/Job backend evidence — P-003/P-011 | No |
| 18 | Cron Job Builder | Exhaustive | ADR-0059 + ADR-0068 + Cron semantics | concrete AS backend/load/coexistence/runner/DST/overlap/fairness — P-003 | No |
| 19 | Notification System | Exhaustive | ADR-0026 + 0058 + 0059 + 0063 + 0067 + 0068 | persistence/fan-out/dedupe/ET provider/Job backend evidence | No |
| 20 | Emails Builder | Exhaustive | ADR-0029 + ADR-0058 + ADR-0063 + ADR-0067 | renderer/client + Delivery/Attempt/Event schema + version-scoped provider ET0–ET5 runtime evidence | No |
| 21 | Message & Chat System | Exhaustive | ADR-0027 | indexes/search/transport/private assets/revocation scale | No |
| 22 | REST API Builder | Exhaustive | ADR-0028 | compiler/auth/rate/CORS/cache/fuzz/scale | No |
| 23 | Webhooks & Connections | Exhaustive | ADR-0040 + 0055 + 0059 + 0068 | I0–I5 adapters/Event Inbox/Job backend evidence | No |
| 24 | Backup Manager | Exhaustive | ADR-0021/0033/0043/0053/0056/0059/0061/0064/0065/0068 | bundle/Remote Copy/provider registry/C0–C4/Job backend evidence — P-003/P-013 | No |
| 25 | Reset Manager | Exhaustive | ADR-0047 + 0059 + 0068 | recovery schema/checkpoints/crash/Job backend/adapters/multisite | No |
| 26 | Import / Export | Exhaustive | ADR-0041 + 0059 + 0068 | run schema/checkpoints/crash/Job backend/source/media evidence | No |
| 27 | Protector | Exhaustive | ADR-0045 | hook/atomic rate/proxy/login/header evidence | No |
| 28 | Watermarker / Media Rules | Exhaustive | ADR-0046 + 0059 + 0068 | registry/image/offload/concurrency/Job backend/load evidence | No |
| 29 | XML-RPC Manager | Exhaustive | ADR-0052 | methods/hooks/parser/complete-deny/compatibility/multisite | No |
| 30 | Role & Capability Manager | Exhaustive | ADR-0032 | classifier/anti-lockout/multisite/CLI recovery | No |
| 31 | Platform Account/Docs/Support/Diagnostics | Exhaustive | ADR-0034/0042/0044/0050/0054/0060 | OpenAPI/OAuth/key custody/TUF/support/privacy/retention evidence; 30-fixture future privacy protocol documented but not executed | No |

## Cross-cutting accepted architecture that still needs evidence

- ADR-0048 Vault hierarchy; exact implementation pending.
- ADR-0049 Definition Repository relational shape; exact DDL pending.
- ADR-0053/0061/0064/0065 Backup model: 34 targets, 0 C-certified; static/provider/transport evidence never implies runtime certification.
- ADR-0054/0060 Remote Service trust/privacy boundaries; 30 future privacy fixtures remain unexecuted.
- ADR-0055 Connections I0–I5; provider evidence pending.
- ADR-0056 Remote Copy lifecycle; physical/runtime evidence pending.
- ADR-0057/0062/0066 Membership Billing: four version-scoped paper profiles, **0 MB-certified**.
- ADR-0058/0063/0067 Email: six version-scoped paper profiles, **0 ET-certified**.
- ADR-0059/0068 Job Service: backend-neutral semantics + Action Scheduler packaging/coexistence accepted; concrete P-003 runtime remains unverified.

## Current provider/version snapshots — paper only

Membership:
- WooCommerce 11.0.1 current snapshot;
- Woo Subscriptions 9.1.0 current secure line snapshot;
- SureCart WP 4.7.0 + separate hosted API profile;
- Manual WPE source.

Email:
- WordPress/P-001 `wp_mail` profile;
- negotiated generic SMTP profile;
- Amazon SES API v2;
- SendGrid Web API v3 + dated Event Webhook/security;
- Mailgun endpoint-specific path-version profile + dated webhook/security;
- Postmark dated REST/webhook profile.

Job backend:
- Action Scheduler 4.1.0 current reviewed candidate;
- WPE Free/Platform would own one bundled candidate if selected;
- Pro/modules do not bundle duplicates;
- shared newest registered runtime can win;
- module direct AS calls prohibited;
- WPE business idempotency/history independent from AS uniqueness/retention.

These are not runtime support claims.

## Recommended implementation order after future consent

1. platform prerequisites + P-003 Action Scheduler coexistence/backend proof;
2. Kernel/Registry/Definition/Policy/Abilities/Assets/Audit/Vault/JobService;
3. CPT/Taxonomy;
4. Fields → Relations → Query → Tables/Columns → Blueprint → Listings/Status;
5. Settings/Menu/Dashboard/Profile/Roles/Widgets;
6. Membership runtime → version-scoped Manual/Woo/SureCart MB certification;
7. Forms/Workflow/Jobs → Notifications → Email renderer → version-scoped ET certification;
8. REST/Connections/Event Inbox/Import;
9. Backup core → Remote Copy → provider adapters/C3 restore → operations/security modules;
10. Chat;
11. Account/Support/Updater under ADR-0060;
12. AI only over certified platform Abilities/Blueprints;
13. ecosystem scale.

## Current conclusion

**Product specification:** 31/31 Exhaustive.  
**Architecture:** accepted decisions through ADR-0068; physical/runtime evidence incomplete.  
**Implemented:** none.  
**Verified runtime:** none.  
**Authorized:** 0/31.

Allowed work remains planning/research/documentation only until explicit owner development consent.
