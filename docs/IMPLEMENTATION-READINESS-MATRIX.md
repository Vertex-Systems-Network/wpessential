# WPEssential — Implementation Readiness Matrix

Status: **Phase 0 planning / NO DEVELOPMENT CONSENT**  
Last reviewed: 2026-08-27

## Global rule

A module can be exhaustive and still be technically blocked and unauthorized.

Production implementation requires:
1. exhaustive option specification;
2. relevant architecture/semantics Accepted;
3. physical schema/dependency/security/performance evidence accepted;
4. acceptance-test plan;
5. compatibility/toolchain gates;
6. explicit owner development consent under ADR-0014;
7. bounded implementation checkpoint.

Current owner consent: **NOT GRANTED**. Therefore **0/31 modules/surfaces are Authorized**.

## Shared blockers

| Shared area | Current state | Future protocol |
|---|---|---|
| WP/PHP/DB compatibility | ADR-0002 Proposed | P-001 |
| Admin UI/design system | ADR-0005 Proposed | P-002 |
| Job Service adapter | ADR-0006 Proposed | P-003 |
| Definition Repository physical schema | ADR-0008 Proposed | P-004 |
| Secrets Vault exact crypto/key profile | ADR-0009 Proposed | P-005 |
| Free↔Pro compatibility runtime | ADR-0010 Proposed | P-006 |
| CI execution | ADR-0011 Proposed | P-007 |
| Build toolchain | ADR-0012 Proposed | P-008 |
| Owner development consent | ADR-0014 Accepted, consent absent | blocks all |

All protocols: `docs/QUALITY/CONSENT-GATED-TECHNICAL-SPIKE-PROTOCOLS.md`.

---

# Per-surface readiness

| # | Surface | Product options | Architecture/semantics | Remaining technical blockers | Authorized |
|---:|---|---|---|---|---|
| 1 | Custom Post Types Builder | Exhaustive | WP registration semantics specified | compatibility, Definition repo, UI/build, registration fixtures | No |
| 2 | Taxonomy Builder | Exhaustive | WP registration semantics specified | compatibility, Definition repo, UI/build, rewrite fixtures | No |
| 3 | Custom Fields Builder | Exhaustive | **ADR-0022 Accepted** plural storage | adapter schemas, scale/query/revision/migration benchmark, Vault | No |
| 4 | Relations Builder | Exhaustive | typed relation semantics + paper edge model | physical indexes/cardinality/concurrency — P-010 | No |
| 5 | Status Manager | Exhaustive | product semantics specified | WP status compatibility + generic state-machine physical model | No |
| 6 | Custom Query Builder | Exhaustive | Query AST v1 paper model | provider compiler/cost/cache/security — P-009 | No |
| 7 | Custom Tables Builder | Exhaustive | **ADR-0023 Accepted** typed Migration Plan | DB compiler/version matrix, locking/backfill/recovery | No |
| 8 | Admin Columns Builder | Exhaustive | list-view/source semantics specified | target adapters, N+1/performance, inline/bulk write proof | No |
| 9 | Dynamic Listings/Templates | Exhaustive | renderer semantics specified | renderer schema, SSR/cache/access context, builder adapters | No |
| 10 | Dashboard Widgets Manager | Exhaustive | product semantics specified | WP Dashboard compatibility, remote-content/iframe safety | No |
| 11 | Custom Admin Menu Builder | Exhaustive | product semantics specified | conflict precedence, recovery/safe mode, destination policy | No |
| 12 | Settings Page Builder | Exhaustive | product semantics specified | storage/site-network scope, Vault fields, frontend exposure | No |
| 13 | Frontend Dashboard Builder | Exhaustive | product semantics specified | route/component runtime, cache/access context | No |
| 14 | User Profile Builder | Exhaustive | product semantics specified | protected-meta/identity-change security matrix, route runtime | No |
| 15 | Membership System | Exhaustive | **ADRs 0013/15/16/19/20/24 Accepted** | runtime schema/cache/protected files/providers/concurrency/privacy tests — P-012 | No |
| 16 | Builder Widgets Builder | Exhaustive | builder certification architecture specified | component blueprint runtime + builder version certification | No |
| 17 | Forms & Workflow Builder | Exhaustive | **ADR-0025 Accepted** Entry architecture + Workflow paper model | Entry physical schema/projections, Workflow/Job runtime — P-011 | No |
| 18 | Cron Job Builder | Exhaustive | Job/Cron product semantics specified | ADR-0006 Job Service, DST/overlap/runtime fixtures | No |
| 19 | Notification System | Exhaustive | **ADR-0026 Accepted** occurrence/recipient/delivery split | tables/indexes/fan-out/channel certification | No |
| 20 | Emails Builder | Exhaustive | email-safe product semantics specified | email component renderer/provider/delivery/bounce certification | No |
| 21 | Message & Chat System | Exhaustive | **ADR-0027 Accepted** runtime/authorization architecture | tables/indexes/search/transport/revocation scale proof | No |
| 22 | REST API Builder | Exhaustive | **ADR-0028 Accepted** compiled descriptor | compiler/auth/rate/CORS/cache/fuzz/scale evidence | No |
| 23 | Webhooks & Connections | Exhaustive | security/product semantics specified | Vault, OAuth adapters, webhook replay, SSRF/DNS/redirect proof | No |
| 24 | Backup Manager | Exhaustive | ADR-0021 encryption architecture + manifest/chunk paper model | exact container/crypto/provider/restore certification — P-013 | No |
| 25 | Reset Manager | Exhaustive | destructive/recovery semantics specified | Backup restore-point runtime, atomicity/recovery, multisite | No |
| 26 | Import / Export | Exhaustive | neutral IR/package/migration architecture specified | parsers/checkpoints/media safety/rollback/source fixtures | No |
| 27 | Protector | Exhaustive | policy/recovery semantics specified | interception/rate/proxy/login/header security fixtures | No |
| 28 | Watermarker / Media Rules | Exhaustive | non-destructive derivative semantics specified | image-editor/offload/format/memory/batch certification | No |
| 29 | XML-RPC Manager | Exhaustive | endpoint/method distinction specified | method inventory/hooks/parser/Jetpack/mobile/multisite proof | No |
| 30 | Role & Capability Manager | Exhaustive | capability/policy architecture specified | anti-lockout/admin-equivalent classifier/multisite recovery | No |
| 31 | Platform Account/Docs/Support/Diagnostics | Exhaustive | remote service + licensing/updater architecture specified | OAuth profile, entitlement crypto, support API schemas, service integration | No |

---

# Accepted architecture does not equal technical readiness

Examples:
- ADR-0022 says **how storage families are separated**, not the final table/index benchmark.
- ADR-0023 says **how migrations are represented/reviewed**, not the final MySQL/MariaDB compiler.
- ADR-0025 says **how Form Entry truth is modeled**, not exact DDL.
- ADR-0026 says **what notification/delivery states mean**, not provider support.
- ADR-0027 says **how Chat authorization/storage domains separate**, not realtime transport implementation.
- ADR-0028 says **REST definitions compile before execution**, not that a compiler exists.

No module may skip evidence merely because an ADR is Accepted.

---

# Recommended implementation order after future consent

Consent authorizes work; it does not erase dependencies.

1. resolve P-001…P-008 platform blockers;
2. Platform Kernel + Module Registry + Definition Repository + Policy/Abilities/Assets/Audit;
3. CPT + Taxonomy Free;
4. Fields → Relations → Query → Tables/Columns → Listings/Status;
5. Admin/Identity foundations;
6. Membership runtime after Policy/Entitlement evidence;
7. Form Entry + Workflow/Job + Notifications/Email;
8. REST/Connections/Import;
9. Backup/Reset/Protection/Media;
10. Chat/realtime adapters;
11. AI composition layer only over certified Abilities;
12. ecosystem SDK/provider scale.

---

# Current conclusion

**Product specification:** 31/31 Exhaustive.  
**Architecture:** many semantics Accepted; physical/runtime evidence incomplete.  
**Implemented:** none.  
**Verified runtime:** none.  
**Authorized:** 0/31.  

Allowed work remains planning/research/documentation only until the owner explicitly authorizes development/executable spikes.