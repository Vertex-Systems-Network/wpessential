# Notifications — Runtime Gap Matrix V1

Surface: **19 / Notifications**  
Planning issue: **#585**  
Supervisor wave: **#583**  
Exact-main claim anchor: `3390739c85f45787743778372c616ef5c9f5d5df`

Status: **PLANNING GAP MATRIX / RUNTIME NOT AUTHORIZED**

Surface 19 is `UNSEEDED / 0` in the Master Options Bank and has no dedicated `frameworks/Modules/Notifications` runtime on exact main. This matrix records implementation prerequisites and owner boundaries; it does not authorize runtime work or promote lifecycle state.

## Baseline

Existing reusable platform/module foundations may later be consumed only through their canonical APIs. Notifications must not create parallel implementations of:

- Ability/Policy authorization;
- Query recipient selection;
- Workflow branching/approval;
- Cron/Job scheduling;
- Email rendering/templates;
- Connection/Webhook provider transport or secret storage;
- Roles/Membership truth;
- resource/media authorization.

## Gap matrix

| Area | Exact-main state | Required future behavior | Gate / owner boundary |
|---|---|---|---|
| Master Options Bank | **BLOCKED — UNSEEDED / 0** | Normalize exhaustive/native/market reviewed records. | Product planning gate before option contracts/runtime. |
| Atomic Option Contracts | **PLANNING INVENTORY ONLY** | Derive schema-valid option contracts after Bank review. | No runtime promotion from current inventory status. |
| Notification runtime module | **ABSENT** | Canonical Rule/Instance/Delivery/Preference/Digest owner. | Runtime issue only after product gates. |
| Rule persistence/revision/CAS | **ABSENT** | Stable definitions, lifecycle, revisions and safe concurrency. | Surface 19 owns Rule semantics; use shared Definition patterns rather than ad-hoc options. |
| Typed trigger registry | **ABSENT FOR SURFACE 19** | Registered Event/Ability adapters with versioned safe payload schema. | Event owners + shared registry; no arbitrary PHP hook/callback input. |
| Conditions | **NOT IN NOTIFICATIONS RUNTIME** | Declarative bounded conditions. | Consume shared Condition/Decision capability; no raw code. |
| Recipient resolution | **ABSENT** | Policy-aware specific/user/role/query/relation/membership/team/provider resolution with dedupe and batch ceilings. | Query/Relations/Membership/Roles remain truth owners. |
| Delivery-time authorization | **ABSENT** | Re-check access before rendering protected resource content/actions. | Shared Policy/resource owner is authoritative. |
| In-app instances/inbox | **ABSENT** | Per-recipient unread/read/dismiss/expiry/revocation with bounded pagination/bulk actions. | Surface 19 runtime + privacy Policy. |
| Email channel | **DEPENDENCY ONLY** | Route through Email Surface 20 template/render contract and approved delivery adapter. | Do not render arbitrary browser HTML or own mail credentials. |
| Webhook channel | **DEPENDENCY ONLY** | Route through Connection/Webhook Surface 23. | Safe HTTP/OAuth/signing/secrets remain Surface 23. |
| Other channel adapters | **ABSENT/DEFERRED** | Registered adapters with health, policy and normalized delivery state. | Provider-specific review required. |
| Delay/scheduling | **ABSENT** | Durable delayed enqueue and recipient-time scheduling. | Surface 18/Job Service executes; no request sleeps. |
| Quiet hours | **ABSENT** | Timezone-aware defer/digest/skip policy with controlled bypass. | Surface 19 policy, Job execution external. |
| Digest | **ABSENT** | Bounded recipient-local batch/group/sort/dedupe/overflow semantics. | Surface 19 owns digest semantics; Job executes. |
| Dedupe | **ABSENT** | Event/custom/time-window idempotency and supported duplicate actions. | Surface 19; provider call idempotency also adapter/Connection responsibility. |
| Frequency caps | **ABSENT** | Bounded category/rule/channel caps and suppress/digest/defer outcomes. | Surface 19; required-security policy cannot be marketing bypass. |
| Preferences | **ABSENT** | Self-service and defaults with locked required classes where legitimate. | Surface 19; authorization/privacy required. |
| Localization/tokens | **ABSENT** | Allowlisted schema-safe tokens, locale variants/fallbacks, redaction. | Renderer/template owners supply safe rendering; no raw object/meta dump. |
| Retry/backoff | **ABSENT** | Adapter-classified retryability, bounded attempts, Retry-After, idempotency. | Channel adapter/Connection transport; Surface 19 routes policy. |
| Delivery status normalization | **ABSENT** | queued/attempting/provider-accepted/confirmed/failed/suppressed/deferred/etc. | Surface 19 normalizes evidence; never infer confirmation from acceptance. |
| Delivery logs | **ABSENT** | Redacted, permission-separated attempt/evidence logs. | Surface 19 occurrence evidence; provider secrets stay external. |
| Preview/test | **ABSENT** | Synthetic/authorized render preview, dry-run recipients, explicitly labelled test send. | Live send separately authorized. |
| Disable/archive/delete impact | **ABSENT** | Queued-instance/dependency impact and retention-aware archive behavior. | Surface 19 canonical lifecycle. |
| Escalation | **BOUNDARY ONLY** | Emit typed failure/unread-threshold events. | Complex branching/escalation belongs Workflow 17. |
| Portability | **ABSENT** | Secret-free Rule export/import refs + dependency/environment conflict preview. | Surface 19 owner seam; package orchestration Surface 26. |
| Multisite | **UNSPECIFIED RUNTIME** | Explicit site/network Rule, provider, recipient, preference and log scope. | Must be contracted before implementation. |
| Accessibility | **NO SURFACE UI** | Keyboard, semantic statuses, restrained live regions, focus/error management. | Browser/axe evidence required once UI exists. |
| Compatibility | **NO SURFACE RUNTIME** | Supported WP/PHP matrix, provider degradation and adapter version health. | Exact-head compatibility gates required later. |
| Performance | **NO SURFACE RUNTIME** | Bounded fan-out/job batches, indexed inbox state, paginated logs, async cleanup, no N+1 context rendering. | Deterministic budgets/tests required later. |
| Reliability | **NO SURFACE RUNTIME** | Idempotency, recursion guards, queue recovery, provider degradation, backpressure/dead-letter/reconcile where applicable. | Job/Connection boundaries explicit. |
| Privacy | **NO SURFACE RUNTIME** | Least-data payloads, masked logs, access-safe token/render behavior and inbox isolation. | Policy/privacy tests required. |

## Security and privacy hard gates

Future implementation must reject or fail closed on:

- arbitrary PHP/JS/callback/class execution input;
- arbitrary user-entered WordPress hook execution configuration;
- generic raw user-meta/object token access;
- secrets stored in Notification Rules or exported packages;
- unvalidated external action URLs;
- protected content rendered without recipient authorization;
- unbounded recipient fan-out or retries;
- cross-user inbox/log access without explicit capability;
- unsafe cross-site multisite recipient/provider access;
- recursive notification-trigger feedback loops;
- misleading delivery-state promotion.

## Policy / Ability model required later

At minimum future canonical Abilities should distinguish:

- list/get Rules;
- draft/create/update/validate Rules;
- publish/enable/disable/archive Rules;
- preview recipients/content;
- live/test send as separate high-impact abilities;
- current-subject inbox list/read/mark/dismiss;
- delivery list/get/retry;
- self preference get/update;
- admin default-preference management;
- sensitive recipient/log viewing;
- viewing/managing another user's inbox.

Every mutation must resolve a server-authoritative execution context and Policy decision. UI visibility alone is not authorization.

## Multisite requirements

Before runtime authorization, contracts must explicitly define:

- site-local vs network-defined Rules;
- whether network defaults may be inherited/overridden;
- recipient resolution against network users vs site members;
- Connection/template visibility by site/network scope;
- preference scope and portability;
- network log/admin privacy;
- deletion/uninstall behavior per site/network.

No implementation should guess these semantics.

## Accessibility evidence required later

Applicable browser evidence should cover:

- Rule list/editor keyboard flow;
- dynamic recipient/channel/condition controls;
- validation summary linkage;
- dependency/degraded states;
- inbox read/dismiss actions and focus behavior;
- unread/status live regions without repeated noisy announcements;
- delivery tables/filters on responsive layouts;
- no color-only priority/status;
- axe checks on representative Essential/Advanced/Expert/System states.

## Compatibility and portability evidence required later

Compatibility:

- exact supported WordPress/PHP matrix;
- absence/degradation of Email/Connection/Job dependencies;
- provider/adapter version mismatch;
- multisite site/network context;
- optional plugin/theme event adapters without hard fatal coupling.

Portability:

- canonical Rule identity/revision;
- secret-free dependency manifest;
- Event/template/Connection/Query/etc. references;
- missing/conflicting dependency preflight;
- create-only and explicit update/CAS semantics;
- no generic package orchestration inside Surface 19.

## Reliability and performance evidence required later

Minimum evidence plan:

1. recipient resolution remains bounded and batched at small vs large audience sizes;
2. no N+1 recipient/profile/template context growth across a defined batch;
3. Job handoff occurs above an explicit fan-out ceiling;
4. duplicate event processing does not duplicate logical notifications/deliveries when idempotency applies;
5. retry obeys bounded attempt/backoff policy and permanent failures stop;
6. provider acceptance is not promoted to confirmed delivery without evidence;
7. recursion/cycle guard prevents a notification-generated event from indefinitely retriggering itself;
8. unread count/update paths are concurrency-safe;
9. inbox/delivery histories paginate and are indexed appropriately;
10. retention cleanup is asynchronous/bounded;
11. protected-resource access is rechecked at delivery/render/action time;
12. logs redact credentials/sensitive payloads.

## Dependency order for a future runtime program

No runtime lane should start until Bank review and Atomic Option Contract prerequisites are satisfied. After that, a dependency-safe order is:

1. canonical Rule Definition + validation/revision/Policy;
2. registered typed Event/recipient/template/connection reference contracts;
3. read-only preview/recipient resolution/dependency diagnostics;
4. in-app instance/inbox model;
5. durable Job-backed occurrence creation and channel queueing;
6. Email/Webhook adapters through their owners;
7. preferences/quiet hours/digest/dedupe/frequency;
8. delivery evidence/retry/health/reconciliation;
9. portability/multisite/degraded-state closure;
10. exact-head security/privacy/accessibility/compatibility/performance certification audit.

Each must be independently bounded; provider/network side effects should be added only after read-only/reference semantics are stable.

## Exit decision

This planning lane does **not** close a runtime gap by adding code. It establishes that exact main currently has:

- a detailed exhaustive Notifications product specification;
- canonical ownership boundaries;
- atomic inventory planning;
- **no normalized Master Options Bank records for Surface 19**;
- **no dedicated Notifications runtime**.

Therefore `runtime_allowed=false` remains correct. The next valid implementation-adjacent action is a separate Bank seeding/native/market review lane, not Notification delivery code.
