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
| Definition Repository physical DDL/indexes | ADR-0049/0069/0071 logical shape/scope/PT-C accepted; ADR-0073 D1 benchmark baseline accepted; exact DDL still pending | P-004 |
| Secrets Vault implementation | ADR-0048 + ADR-0069 accepted hierarchy/scope; exact envelope/interoperability pending | P-005 |
| Free↔Pro runtime | ADR-0010 + ADR-0070/0072 product allocation/resource semantics; runtime compatibility pending | P-006 |
| CI | ADR-0011 Proposed | P-007 |
| Build toolchain | ADR-0012 Proposed | P-008 |
| Multisite runtime certification | ADR-0069 logical scope + ADR-0071 topology accepted; MS0–MS4 protocol documented, 0 runtime fixtures executed | P-001 + module-specific gates |
| Owner consent | ADR-0014 Accepted, consent absent | blocks all |

## Per-surface readiness

All 31 surfaces remain **Exhaustive / Unauthorized**. ADR-0069 applies as cross-cutting scope/security contract; ADR-0071 supplies physical topology classes; ADR-0073 supplies only the future P-004 Definition benchmark baseline, not final SQL.

| # | Surface | Product options | Accepted/paper architecture | Remaining technical blockers | Authorized |
|---:|---|---|---|---|---|
| 1 | Custom Post Types Builder | Exhaustive | WP registration semantics + ADR-0069 + Definition PT-C D1 baseline ADR-0073 | compatibility, P-004 DDL, UI/build, site/network registration fixtures | No |
| 2 | Taxonomy Builder | Exhaustive | WP registration semantics + ADR-0069 + Definition PT-C D1 baseline ADR-0073 | compatibility, P-004 DDL, UI/build, site/network rewrite fixtures | No |
| 3 | Custom Fields Builder | Exhaustive | ADR-0022 + ADR-0069/0071 | storage adapter topology/scale/query/revision/migration/Vault runtime | No |
| 4 | Relations Builder | Exhaustive | typed edge semantics + ADR-0069 + PT-D candidate ADR-0071 | PT-D vs PT-E/per-site indexes/cardinality/concurrency — P-010 | No |
| 5 | Status Manager | Exhaustive | ADR-0038 + ADR-0069 | WP UI/migration/state history/concurrency/site registry | No |
| 6 | Custom Query Builder | Exhaustive | Query AST + ADR-0069/0071 | compiler/cost/cache/security/network aggregate/storage-adapter scale — P-009 | No |
| 7 | Custom Tables Builder | Exhaustive | ADR-0023 + ADR-0069/0071 | explicit PT-D/PT-E compiler/version/locking/backfill/recovery | No |
| 8 | Admin Columns Builder | Exhaustive | list/source semantics + ADR-0069 | adapters/N+1/performance/write/site-template proof | No |
| 9 | Dynamic Listings/Templates | Exhaustive | ADR-0035 + ADR-0039 + ADR-0069 | Query/protected pagination/cache/site/network template fixtures | No |
| 10 | Dashboard Widgets Manager | Exhaustive | ADR-0051 + ADR-0069 | Site vs Network Dashboard compatibility/remote schema/iframe/XSS/CSP | No |
| 11 | Custom Admin Menu Builder | Exhaustive | ADR-0037 + ADR-0069 | site/network menu hook conflicts/recovery/performance | No |
| 12 | Settings Page Builder | Exhaustive | ADR-0036 + ADR-0069 + PT-A/PT-B ADR-0071 | physical storage/autoload/concurrency/network inheritance/Vault/REST | No |
| 13 | Frontend Dashboard Builder | Exhaustive | ADR-0031 + ADR-0035 + ADR-0069 | routing/domain/multisite/IDOR/cache/assets/builder | No |
| 14 | User Profile Builder | Exhaustive | ADR-0030 + ADR-0069/0071 | native/global identity vs site-field storage/credential/session evidence | No |
| 15 | Membership System | Exhaustive | ADRs 0013/15/16/19/20/24 + 0057/0062/0066/0069 + PT-D candidate ADR-0071 | Enrollment schema/index/cache/files, MB0–MB5, Multisite/scale — P-012 | No |
| 16 | Builder Widgets Builder | Exhaustive | ADR-0035 + ADR-0069 | renderer/nesting/bindings/assets/accessibility/site-network library certification | No |
| 17 | Forms & Workflow Builder | Exhaustive | ADR-0025 + 0059/0068/0069 + ADR-0071 PT-D Workflow, Forms PT-D/PT-E | Entry topology/Workflow/Job backend/site-network fan-out — P-003/P-011 | No |
| 18 | Cron Job Builder | Exhaustive | ADR-0059 + ADR-0068 + ADR-0069 + ADR-0071 Job history | concrete AS backend/runner/DST/overlap/fairness/network coordinator — P-003 | No |
| 19 | Notification System | Exhaustive | ADR-0026 + 0058/0059/0063/0067/0068/0069 + PT-D ADR-0071 | schema/fan-out/dedupe/ET provider/Job/site-scope evidence | No |
| 20 | Emails Builder | Exhaustive | ADR-0029 + 0058/0063/0067/0069 + PT-D ADR-0071 | renderer/client + Delivery/Attempt/Event schema + ET0–ET5 evidence | No |
| 21 | Message & Chat System | Exhaustive | ADR-0027 + ADR-0069 + PT-D/PT-E evidence-gated ADR-0071 | indexes/search/transport/private assets/revocation/topology scale | No |
| 22 | REST API Builder | Exhaustive | ADR-0028 + ADR-0069 | compiler/auth/rate/CORS/cache/fuzz/cross-site IDOR/scale | No |
| 23 | Webhooks & Connections | Exhaustive | ADR-0040 + 0055/0059/0068/0069 + PT-D Event Inbox ADR-0071 | I0–I5 adapters/Event Inbox DDL/Job/network-shared connection isolation | No |
| 24 | Backup Manager | Exhaustive | ADR-0021/0033/0043/0053/0056/0059/0061/0064/0065/0068/0069/0071 | shared-row extraction, provider C0–C4, site/network restore — P-003/P-013 | No |
| 25 | Reset Manager | Exhaustive | ADR-0047 + 0059/0068/0069/0071 | recovery schema/checkpoints/crash/Job backend/site vs network topology cleanup | No |
| 26 | Import / Export | Exhaustive | ADR-0041 + 0059/0068/0069/0071 | run schema/checkpoints/source/media/scope/topology remap evidence | No |
| 27 | Protector | Exhaustive | ADR-0045 + ADR-0069 | hook/atomic rate/proxy/login/header/network security-floor evidence | No |
| 28 | Watermarker / Media Rules | Exhaustive | ADR-0046 + 0059/0068/0069 | registry/image/offload/concurrency/Job/site media isolation | No |
| 29 | XML-RPC Manager | Exhaustive | ADR-0052 + ADR-0069 | method/hooks/parser/complete-deny/Jetpack/multisite network-impact evidence | No |
| 30 | Role & Capability Manager | Exhaustive | ADR-0032 + ADR-0069 | target-site classifier/anti-lockout/Super Admin/network/CLI recovery | No |
| 31 | Platform Account/Docs/Support/Diagnostics | Exhaustive | ADR-0034/0042/0044/0050/0054/0060/0069/0070/0072 + PT-F ADR-0071 | exact OpenAPI/OAuth/idempotency/concurrency/key custody/TUF/support/privacy/allocation/clone-transfer/offline-grace evidence | No |

## Cross-cutting accepted architecture that still needs evidence

- ADR-0048 Vault hierarchy; exact implementation pending.
- ADR-0049 Definition Repository relational shape + ADR-0071 PT-C + ADR-0073 D1 benchmark baseline; exact DDL/indexes remain unverified.
- ADR-0053/0061/0064/0065 Backup model: 34 targets, 0 C-certified.
- ADR-0054/0060 Remote Service trust/privacy boundaries; 30 future privacy fixtures remain unexecuted.
- ADR-0055 Connections I0–I5; provider evidence pending.
- ADR-0056 Remote Copy lifecycle; physical/runtime evidence pending.
- ADR-0057/0062/0066 Membership Billing: four version-scoped paper profiles, **0 MB-certified**.
- ADR-0058/0063/0067 Email: six version-scoped paper profiles, **0 ET-certified**.
- ADR-0059/0068 Job Service: backend-neutral semantics + AS packaging accepted; concrete P-003 runtime remains unverified.
- ADR-0069 Multisite: explicit scope/isolation accepted; MS0–MS4 documented; **0 runtime fixtures / 0 MS1+**.
- ADR-0070/0072 Product License: opaque installation/network/site-allocation identity plus versioned remote resource/conflict state semantics accepted; **0 service fixtures**.
- ADR-0071 Physical topology: PT-A…PT-F accepted; no DDL or DB benchmark executed.
- **ADR-0073 Definition D1 baseline:** textual UUID, explicit scope, bounded identity keys, text payload, minimal indexes and app integrity diagnostics are the future P-004 baseline; not a final schema claim.

## Current provider/version snapshots — paper only

Membership: WooCommerce 11.0.1; Woo Subscriptions 9.1.0; SureCart WP 4.7.0 + hosted API profile; Manual WPE source.

Email: WordPress/P-001 `wp_mail`; generic SMTP; SES API v2; SendGrid Web API v3; endpoint-specific Mailgun; dated Postmark REST/webhook profile.

Job backend: Action Scheduler 4.1.0 reviewed candidate only; WPE Free/Platform would own one bundle if selected; Pro/modules do not duplicate; business idempotency/history stay WPE-owned.

These are not runtime support claims.

## Recommended implementation order after future consent

1. P-001 Multisite + P-003 Job backend + P-004 Definition D1-vs-D2/D3/D4 evidence;
2. Kernel/Registry/Definition/Policy/Abilities/Assets/Audit/Vault/JobService with scope context;
3. CPT/Taxonomy;
4. Fields → Relations → Query → Tables/Columns → Blueprint → Listings/Status;
5. Settings/Menu/Dashboard/Profile/Roles/Widgets;
6. Membership runtime + MB certification + Multisite isolation;
7. Forms/Workflow → Notifications → Email ET profiles;
8. REST/Connections/Event Inbox/Import;
9. Backup core/provider/site-network restore → operations/security modules;
10. Chat after PT-D/PT-E evidence;
11. Account/Support/Updater/Product Allocation under ADR-0060/0070/0072;
12. AI only over certified scope-aware Abilities/Blueprints;
13. ecosystem/large-network scale.

## Current conclusion

**Product specification:** 31/31 Exhaustive.  
**Architecture:** accepted decisions through ADR-0073; physical/runtime evidence incomplete.  
**Multisite product mapping:** 31/31 surfaces documented.  
**Multisite runtime certification:** 0 surfaces MS1+.  
**Definition D1 benchmark execution:** 0.  
**Product-license runtime/service evidence:** 0 fixtures executed.  
**Implemented:** none.  
**Verified runtime:** none.  
**Authorized:** 0/31.

Allowed work remains planning/research/documentation only until explicit owner development consent.
