# WPEssential — Implementation Readiness Matrix

Status: **Phase 0 planning / NO DEVELOPMENT CONSENT**  
Last reviewed: 2026-08-27

## Global rule

A module does not become implementation-ready because its UI/options are documented.

Before production implementation, all applicable gates must hold:
1. product-option maturity = Exhaustive;
2. relevant product semantics = Accepted;
3. shared platform ADRs = Accepted/superseded;
4. physical data/dependency/runtime strategy = accepted;
5. security/performance/compatibility evidence = sufficient;
6. acceptance-test plan exists;
7. migration/recovery behavior is defined;
8. **explicit owner development consent exists under ADR-0014**;
9. bounded implementation milestone is checkpointed.

Current fact: **31/31 surfaces are Exhaustive at product-option level, but owner development consent is NOT GRANTED and multiple technical ADRs remain Proposed. Therefore 0/31 surfaces are authorized for implementation.**

---

# Shared platform blockers

| Shared decision/service | State | Blocks |
|---|---|---|
| WP/PHP compatibility floor | ADR-0002 Proposed | all runtime modules |
| Admin UI/design system | ADR-0005 Proposed | React/admin builder surfaces |
| Job Service/background adapter | ADR-0006 Proposed | workflows, cron, notifications, backup, import, watermark, membership jobs |
| Definition Repository physical schema | ADR-0008 Proposed | definition-driven modules |
| Secrets Vault exact crypto/key profile | ADR-0009 Proposed | connections, providers, backup credentials, remote service |
| Free↔Pro executable compatibility protocol | ADR-0010 Proposed | all Pro modules/mixed update order |
| CI/test execution matrix | ADR-0011 Proposed | implementation/release |
| Build toolchain/externalization | ADR-0012 Proposed | React/TS assets/build |
| Owner consent | ADR-0014 Accepted rule; **consent absent** | every executable task |

Accepted but implementation-profile-pending security architectures:
- ADR-0017 Product entitlement verification;
- ADR-0018 Pro update supply-chain trust;
- ADR-0021 Backup encryption/recovery.

---

# Module readiness table

| # | Surface | Product option maturity | Accepted semantics | Main technical blockers | Current readiness |
|---:|---|---|---|---|---|
| 1 | CPT Builder | Exhaustive | Core product semantics documented | compatibility, Definition Repository, UI/build, third-party registration/rewrite tests | **BLOCKED** |
| 2 | Taxonomy Builder | Exhaustive | Core product semantics documented | compatibility, Definition Repository, reserved/default-term/rewrite tests | **BLOCKED** |
| 3 | Custom Fields | Exhaustive | Behavioral semantics documented | field storage schema, repeaters/flexible normalization, field migration/runtime benchmark, Vault secret fields | **BLOCKED** |
| 4 | Relations | Exhaustive | Behavioral semantics documented | physical relation schema/indexes, cardinality concurrency, delete policies, cross-source adapters | **BLOCKED** |
| 5 | Status Manager | Exhaustive | Behavioral semantics documented | WP status compatibility, domain state machine physical model, transition ordering | **BLOCKED** |
| 6 | Query Builder | Exhaustive | Behavioral semantics documented | Query AST v1, compilers, cost/security budgets, cache/invalidation, remote source policy | **BLOCKED** |
| 7 | Custom Tables | Exhaustive | Behavioral semantics documented | DDL/migration planner, DB matrix, rollback, large-table operations, backup integration | **BLOCKED** |
| 8 | Admin Columns | Exhaustive | Behavioral semantics documented | list-table adapters, sortable/filter capability mapping, N+1 budgets, edit/export safety | **BLOCKED** |
| 9 | Listings/Templates | Exhaustive | Behavioral semantics documented | renderer schema, SSR/cache/access-context behavior, builder adapters | **BLOCKED** |
| 10 | Dashboard Widgets | Exhaustive | Behavioral semantics documented | WP dashboard adapters, remote/iframe security, state persistence | **BLOCKED** |
| 11 | Admin Menu | Exhaustive | Behavioral semantics documented | ownership/conflict precedence, destination authorization, safe recovery | **BLOCKED** |
| 12 | Settings Page | Exhaustive | Behavioral semantics documented | setting storage contract, site/network scope, Vault-backed fields | **BLOCKED** |
| 13 | Frontend Dashboard | Exhaustive | Behavioral semantics documented | route schema/resolver, policy cache, renderer/builder integrations | **BLOCKED** |
| 14 | User Profile | Exhaustive | Behavioral semantics documented | protected meta matrix, account-sensitive updates/re-auth, privacy rules | **BLOCKED** |
| 15 | Membership | Exhaustive | **ADR-0013, 0015, 0016, 0019, 0020 Accepted** | runtime schema/index benchmark, cache/revocation proof, protected files, billing adapters/reconciliation, migration fixtures | **BLOCKED** |
| 16 | Builder Widgets | Exhaustive | Adapter separation/certification semantics documented | Component Blueprint physical schema/rendering, per-builder version certification | **BLOCKED** |
| 17 | Forms & Workflow | Exhaustive | Behavioral semantics documented | form/entry/runtime schemas, Job Service, workflow graph persistence, upload/save-resume/anti-spam evidence | **BLOCKED** |
| 18 | Cron Builder | Exhaustive | Behavioral semantics documented | Job Service adapter, WP-Cron coexistence, overlap/DST/runner evidence | **BLOCKED** |
| 19 | Notifications | Exhaustive | Behavioral semantics documented | notification/delivery schema, recipient snapshot rules, channel adapters/deduping | **BLOCKED** |
| 20 | Email Builder | Exhaustive | Behavioral semantics documented | email component renderer, interception matrix, provider/client certification | **BLOCKED** |
| 21 | Message & Chat | Exhaustive | Behavioral semantics documented | message/conversation schema, transport, search indexing, private attachment storage, load tests | **BLOCKED** |
| 22 | REST API Builder | Exhaustive | Behavioral semantics documented | endpoint compiler/schema, auth/resource policy, rate-limit persistence, cache/idempotency evidence | **BLOCKED** |
| 23 | Webhooks & Connections | Exhaustive | Behavioral semantics documented | Vault profile, OAuth lifecycle, signature/replay inbox, SSRF/DNS/redirect defenses, provider tests | **BLOCKED** |
| 24 | Backup Manager | Exhaustive | Restore semantics + **ADR-0021 encryption architecture Accepted** | archive/container/KDF profile, provider adapters, large-site/resource tests, restore certification | **BLOCKED** |
| 25 | Reset Manager | Exhaustive | Behavioral/safety semantics documented | Backup restore-point dependency, destructive runtime/rollback limits, multisite/recovery tests | **BLOCKED** |
| 26 | Import / Export | Exhaustive | Migration/package fidelity model documented | package/source adapter fixtures, parser limits, resumable checkpoints, rollback/deactivation proof | **BLOCKED** |
| 27 | Protector | Exhaustive | Security semantics documented | request interception, rate-limit storage, trusted-proxy/recovery/login compatibility tests | **BLOCKED** |
| 28 | Watermarker / Media Rules | Exhaustive | Non-destructive original invariant documented | image-editor/format/offload certification, derivative storage/naming, batch jobs | **BLOCKED** |
| 29 | XML-RPC Manager | Exhaustive | Granular endpoint/auth/pingback semantics documented | hook-order/complete-disable tests, parser limits, third-party client/network/multisite compatibility | **BLOCKED** |
| 30 | Role & Capability Manager | Exhaustive | Behavioral semantics documented | admin-equivalent classifier, anti-lockout/recovery, multisite/Super Admin tests | **BLOCKED** |
| 31 | Platform / Account / Plans / Docs / Support | Exhaustive platform contract | **ADR-0017/0018 architecture accepted where applicable** | OAuth exact profile, service schemas, signing canonicalizer/library, updater client protocol, support API tests | **BLOCKED** |

---

# Membership readiness detail

## Accepted product semantics
- Role ≠ Membership ≠ Subscription ≠ Entitlement.
- Outer WordPress/security denial cannot be bypassed by Membership.
- Resource/action specificity and same-specificity deny precedence are fixed.
- Enrollment states are pending/trialing/active/grace/paused/expired/revoked.
- Cancellation-at-period-end is intent, not a state.
- Provider payment status remains source fact translated through policy.
- Plan draft edits never mutate live access.
- Published benefit changes explicitly select follow-current/grandfather/scheduled behavior.
- Billing math belongs to provider where possible; WPE controls access effective timestamps.
- Team roles are Membership-domain roles.
- WordPress role sync is off by default and provenance-safe.

## Still technical/evidence blocked
- Enrollment/Entitlement physical tables/indexes;
- access generation/cache invalidation and revoke-to-deny guarantee;
- group/seat locking/concurrency;
- private-file delivery environments;
- WooCommerce/Woo Subscriptions/SureCart adapters;
- reconciliation/idempotency/out-of-order fixtures;
- migration fidelity/performance.

---

# Operations readiness detail

## Backup
Accepted architecture does not yet select exact archive/KDF/AEAD container. Provider “support” requires certification; restore capability requires actual restore tests.

## Reset
No reset implementation can precede a working verified restore-point contract for scopes that require it.

## Protector
Security enforcement may not depend on React/menu visibility, remote WPE service availability or license freshness. Recovery must exist before high-risk lockout rules ship.

## XML-RPC
WPE must preserve the WordPress distinction between authenticated-method enablement and pingback/custom methods; “XML-RPC disabled” marketing requires evidence for the exact enforcement mode.

---

# Implementation order after future owner consent

Consent authorizes engineering work; it does not waive dependency gates.

Recommended dependency order:
1. authorized evidence spikes for platform blockers;
2. accept compatibility/UI/build/Definition/Job/Vault/Free↔Pro/CI ADRs;
3. Platform Kernel;
4. Free CPT + Taxonomy;
5. Fields → Relations → Query → Columns/Listings/Status;
6. Admin/Identity foundations;
7. Membership after Policy/Entitlement runtime proof;
8. Forms/Workflow/Jobs/Communication;
9. Integration/Data Movement;
10. Backup/Reset/Protection/Media/XML-RPC;
11. Chat/advanced integrations;
12. AI/MCP composition only over verified typed Abilities.

# Current conclusion

**Product-option planning:** 31/31 Exhaustive.  
**Accepted semantics:** partial by module; Membership/security/commercial decisions materially advanced.  
**Technical implementation readiness:** blocked.  
**Development authorization:** **NOT GRANTED**.  
**Allowed work now:** documentation/research/architecture only.