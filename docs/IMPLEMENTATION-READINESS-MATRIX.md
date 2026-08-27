# WPEssential — Implementation Readiness Matrix

Status: **Phase 0 planning / NO DEVELOPMENT CONSENT**  
Last reviewed: 2026-08-27

This matrix prevents a module from being treated as development-ready merely because its feature list exists.

## Global rule

For every module, **all** of the following are required before production implementation:

1. option/screen inventory exists;
2. behavioral specification is at least **Specified**;
3. relevant shared-engine contracts are Accepted;
4. relevant Proposed ADRs are Accepted or superseded;
5. module-specific security/data/performance blockers are resolved;
6. acceptance-test plan exists;
7. dependencies and compatibility assumptions are resolved;
8. the project owner has given explicit development consent under ADR-0014;
9. a bounded implementation milestone is checkpointed.

At this checkpoint, requirement **8 is not satisfied for any module**. Therefore **no module is authorized for implementation**.

## Maturity legend

- **Inventory** — controls/screens identified; semantics can still be incomplete.
- **Specified** — behavior/defaults/permissions/validation/failure states are documented.
- **Accepted** — relevant decisions/blockers are resolved and spec can enter implementation after owner consent.
- **Implemented** — source exists, but verification may still be incomplete.
- **Verified** — quality/security/compatibility gates pass.

---

## Shared platform blockers

These affect multiple modules and must not be repeatedly solved inside modules.

| Shared decision/service | Current state | Blocks |
|---|---|---|
| Compatibility floor — WP/PHP | Proposed ADR-0002 | all runtime modules |
| UI/design system wrappers | Proposed ADR-0005 | all React/admin builder surfaces |
| Job Service/background execution | Proposed ADR-0006 | workflows, cron, notifications, backups, imports, watermark batch, membership lifecycle |
| Definition Repository schema | Proposed ADR-0008 | all definition-driven builders |
| Secrets Vault/key model | Proposed ADR-0009 | backups, connections, billing, email providers, external APIs |
| Free↔Pro compatibility protocol | Proposed ADR-0010 | all Pro modules and mixed-version boot |
| CI/test matrix | Proposed ADR-0011 | all implementation/release work |
| Build toolchain/externalization | Proposed ADR-0012 | all React/TypeScript assets/builds |
| Explicit owner development consent | Accepted ADR-0014, **consent not granted** | all source implementation |

---

# Module readiness

## 1. Custom Post Types Builder — Free

**Specification:** Specified  
**Implementation readiness:** BLOCKED

Required before implementation:
- ADR-0002 compatibility floor;
- ADR-0005/0012 UI/build choices;
- ADR-0008 Definition Repository;
- ADR-0011 CI matrix;
- exact handling contract for third-party-owned registrations;
- reserved slug/version compatibility fixture list;
- rewrite-flush/rollback test plan;
- owner development consent.

First allowed implementation milestone after consent: read-only registry + WPEssential-owned definition lifecycle before mutation of third-party registrations.

## 2. Taxonomy Builder — Free

**Specification:** Specified  
**Implementation readiness:** BLOCKED

Pending:
- same platform blockers as CPT;
- taxonomy reserved-name fixture set;
- default-term/version behavior matrix;
- rewrite migration/rollback tests;
- owner consent.

## 3. Custom Fields Builder — Pro

**Specification:** Specified  
**Implementation readiness:** BLOCKED

Pending:
- Definition Repository and Field Schema persistence;
- field-key migration semantics;
- storage adapter contract for meta/options/custom tables;
- repeater/flexible-content normalization limits;
- large-value/REST exposure constraints;
- secret field integration with Vault;
- compatibility tests across post/term/user/comment/media/settings targets;
- owner consent.

## 4. Relations Builder — Pro

**Specification:** Specified  
**Implementation readiness:** BLOCKED

Pending:
- relation storage schema/index strategy;
- transaction/concurrency semantics;
- cardinality enforcement behavior;
- delete-policy precedence;
- cross-source relation support matrix;
- orphan repair guarantees;
- owner consent.

## 5. Status Manager — Pro

**Specification:** Specified  
**Implementation readiness:** BLOCKED

Pending:
- WordPress custom-status compatibility matrix;
- domain-state-machine schema;
- transition guard/action ordering;
- conflict behavior with third-party statuses;
- owner consent.

## 6. Custom Query Builder — Pro

**Specification:** Specified  
**Implementation readiness:** BLOCKED

Pending:
- canonical Query AST version/schema;
- provider compiler contracts;
- parameter binding/type system;
- authorization/data-leak policy for dynamic contexts;
- public-query cost/time/row budgets;
- cache key + invalidation contract;
- remote query SSRF/auth rules;
- owner consent.

## 7. Custom Tables Builder — Pro

**Specification:** Specified  
**Implementation readiness:** BLOCKED

Pending:
- supported MySQL/MariaDB capability matrix;
- table/schema migration planner;
- rename/drop recovery model;
- index recommendation limits;
- physical FK policy;
- safe query console classification;
- large-table migration strategy;
- backup integration gate;
- owner consent.

## 8. Admin Columns Builder — Pro

**Specification:** Specified  
**Implementation readiness:** BLOCKED

Pending:
- list-table adapter contract;
- sortable/filterable capability detection;
- N+1/performance budget;
- inline/bulk edit authorization and write adapters;
- CSV export escaping/formula-injection tests;
- owner consent.

## 9. Dynamic Listings / Template Builder — Pro

**Specification:** Specified  
**Implementation readiness:** BLOCKED

Pending:
- Renderer/template-schema v1;
- SSR/client-hydration boundary;
- cache/access-context separation;
- pagination/filter URL contract;
- reusable partial recursion/dependency limits;
- owner consent.

## 10. Dashboard Widgets Manager — Pro

**Specification:** Specified  
**Implementation readiness:** BLOCKED

Pending:
- WordPress Dashboard compatibility matrix;
- remote-content SSRF policy reuse;
- iframe allowlist/security-header behavior;
- dismissal-state storage/retention;
- owner consent.

## 11. Custom Admin Menu Builder — Pro

**Specification:** Specified  
**Implementation readiness:** BLOCKED

Pending:
- menu discovery/ownership rules;
- role/profile conflict precedence;
- recovery/safe-mode path;
- destination capability verification;
- owner consent.

## 12. Settings Page Builder — Pro

**Specification:** Specified  
**Implementation readiness:** BLOCKED

Pending:
- Settings storage contract;
- network-vs-site scope rules;
- Vault-backed secret fields;
- frontend exposure restrictions;
- owner consent.

## 13. Dashboard Builder — Pro

**Specification:** Specified  
**Implementation readiness:** BLOCKED

Pending:
- route schema and resolver;
- server-side route/resource authorization;
- dashboard navigation nesting behavior and accessibility;
- builder-template adapter contracts;
- caching rules by user/access context;
- owner consent.

## 14. User Profile Builder — Pro

**Specification:** Specified  
**Implementation readiness:** BLOCKED

Pending:
- protected user-meta denylist;
- privacy/public-field policy;
- email/password-change re-auth flow contract;
- account-route collision rules;
- owner consent.

## 15. Membership System — Pro

**Specification:** Specified, dedicated deep specification exists  
**Architecture:** ADR-0013 Accepted  
**Implementation readiness:** BLOCKED

Pending technical decisions:
- entitlement runtime schema and indexes;
- access-rule specificity/allow/deny precedence;
- cache key/invalidation model;
- enrollment state-transition concurrency;
- protected-file delivery for Apache/Nginx/CDN/offload environments;
- initial billing adapter contract and webhook reconciliation;
- WooCommerce/SureCart/source mapping strategy;
- upgrade/downgrade effective-date semantics;
- seat/team invitation concurrency;
- privacy retention/export/erasure rules;
- role-sync conflict and rollback semantics;
- migration fidelity from competitor systems;
- owner consent.

## 16. Builder Widgets Builder — Pro

**Specification:** Specified  
**Implementation readiness:** BLOCKED

Pending:
- Component Blueprint schema;
- server-render/sanitization model;
- builder capability matrix;
- Gutenberg/Elementor adapter certification versions;
- asset dependency graph rules;
- owner consent.

## 17. Forms & Workflow Builder — Pro

**Specification:** Specified  
**Implementation readiness:** BLOCKED

Pending:
- form schema/entry schema;
- Workflow Runtime contract;
- Job Service decision;
- idempotency/retry semantics;
- file upload storage/security contract;
- guest save/resume token threat model;
- anti-spam adapter contract;
- destructive CRUD re-auth/policy model;
- owner consent.

## 18. Cron Job Builder — Pro

**Specification:** Specified  
**Implementation readiness:** BLOCKED

Pending:
- ADR-0006 Job Service;
- WP-Cron vs durable-job ownership model;
- overlapping-run lock semantics;
- DST/timezone rules;
- third-party event mutation restrictions;
- system-cron/WP-CLI health contract;
- owner consent.

## 19. Notification System — Pro

**Specification:** Specified  
**Implementation readiness:** BLOCKED

Pending:
- notification/event persistence schema;
- recipient-resolution snapshot semantics;
- deduplication/digest rules;
- user-preference precedence;
- channel retry policy;
- owner consent.

## 20. Emails Builder — Pro

**Specification:** Specified  
**Implementation readiness:** BLOCKED

Pending:
- email-safe component schema/renderer;
- WordPress email interception/override matrix;
- provider/delivery adapter boundaries;
- bounce/delivery claim semantics;
- CSS inlining/client compatibility policy;
- unsubscribe/legal classification per email type;
- owner consent.

## 21. Message & Chat System — Pro

**Specification:** Specified  
**Implementation readiness:** BLOCKED

Pending:
- conversation/message/read-state schema/indexes;
- IDOR/object-access policy;
- attachment access/storage contract;
- polling interval/backpressure strategy;
- moderation/report retention;
- realtime adapter protocol later;
- owner consent.

## 22. REST API Builder — Pro

**Specification:** Specified  
**Implementation readiness:** BLOCKED

Pending:
- endpoint-definition schema/versioning;
- auth adapter policy;
- capability/resource policy model;
- rate-limit storage/keying;
- CORS safe-default matrix;
- idempotency behavior;
- response caching by permission context;
- owner consent.

## 23. Webhooks & Connections Manager — Pro

**Specification:** Specified  
**Implementation readiness:** BLOCKED

Pending:
- Secrets Vault;
- OAuth lifecycle contract;
- webhook signature/replay-window standard;
- SSRF/DNS-rebinding/redirect policy;
- retry/idempotency contract;
- connection-health model;
- owner consent.

## 24. Backup Manager — Pro

**Specification:** Specified  
**Implementation readiness:** BLOCKED

Pending:
- archive/manifest format;
- chunking/resume protocol;
- archive encryption/key model;
- verified-restore contract;
- retention/pruning transaction semantics;
- provider adapter interface;
- minimum representative providers acceptance suite;
- low-memory/large-site benchmark plan;
- owner consent.

## 25. Reset Manager — Pro

**Specification:** Specified  
**Implementation readiness:** BLOCKED

Pending:
- verified Backup Manager restore-point contract;
- reset scope dependency graph;
- multisite semantics;
- atomicity/recovery limits;
- emergency reactivation/admin recovery path;
- owner consent.

## 26. Import / Export — Pro

**Specification:** Specified  
**Implementation readiness:** BLOCKED

Pending:
- configuration-package schema/version negotiation;
- dependency/UUID mapping rules;
- data parser limits;
- resumable import checkpoint schema;
- matching/update/delete semantics;
- remote media SSRF/file safety;
- rollback boundaries;
- owner consent.

## 27. Protector — Pro

**Specification:** Specified  
**Implementation readiness:** BLOCKED

Pending:
- emergency recovery/bypass design;
- trusted-proxy/IP model;
- lockout persistence and rate-limit strategy;
- password-gate storage/session behavior;
- login-slug compatibility matrix;
- security-header ownership/server compatibility;
- owner consent.

## 28. Watermarker / Media Rules — Pro

**Specification:** Specified  
**Implementation readiness:** BLOCKED

Pending:
- derived-rendition naming/storage contract;
- image editor capability matrix;
- regeneration/idempotency rules;
- SVG sanitization requirements;
- EXIF/orientation/animated-image behavior;
- batch Job Service;
- owner consent.

## 29. XML-RPC Manager — Pro

**Specification:** Specified  
**Implementation readiness:** BLOCKED

Pending:
- current method inventory fixture;
- Jetpack/mobile compatibility matrix;
- XML parser/request-limit safe hooks;
- logging/privacy policy;
- owner consent.

## 30. Role & Capability Manager — Pro

**Specification:** Specified  
**Implementation readiness:** BLOCKED

Pending:
- administrator-equivalent capability classifier;
- anti-lockout/recovery mechanism;
- multisite/Super Admin rules;
- role deletion/user reassignment behavior;
- simulation/test-as-role isolation;
- owner consent.

## 31. Support / Docs / Changelog / Account Center — Platform surface

**Specification:** Specified  
**Implementation readiness:** BLOCKED

Pending:
- remote account API contract;
- local/offline state contract;
- OAuth/token/session handling if used;
- support attachment limits and redaction;
- dynamic plan schema/caching/failure state;
- update/download entitlement protocol;
- privacy/diagnostic consent contract;
- owner consent.

---

# Cross-module implementation order after consent

Owner consent will authorize development only; it does not erase technical dependencies.

Recommended order remains:

1. resolve accepted platform contracts;
2. Platform Kernel;
3. CPT + Taxonomy Free;
4. Fields → Relations → Query → Columns → Listings → Status;
5. Admin/Identity foundations;
6. Membership after Policy/Entitlement primitives;
7. Forms/Workflow/Jobs/Communication;
8. Integration/Data Movement;
9. Operations/Recovery;
10. Chat/advanced REST;
11. user-facing AI composition layer;
12. ecosystem/SDK scale work.

# Current conclusion

**Verified:** all planned module/platform surfaces have a behavioral planning artifact.  
**Not verified:** runtime architecture, builds, migrations, tests and implementations.  
**Development authorization:** **NOT GRANTED**.  
**Allowed next action:** continue Phase 0 research/decision documentation and request owner consent before any executable/code spike or production implementation.
