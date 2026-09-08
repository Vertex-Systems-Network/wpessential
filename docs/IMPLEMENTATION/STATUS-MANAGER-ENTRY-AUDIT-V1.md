# Status Manager Exact-Main Entry Audit V1

Exact main audited: `67dbcbb1bcae5bd5c0580f9c947762d418f5be5f`  
Audit owner: Issue #345  
Entry condition: Dynamic Listings Gate E final reconciliation PR #344 is promoted and pre-Status tracker #66 is closed.

## Verdict

**Status Manager may enter a separately scoped bounded V1 implementation gate after this Supervisor audit is promoted.**

Status is not runtime-complete on the audited main. There is no `frameworks/Modules/Status` production module/runtime. The planning Bank is strong enough to define a bounded implementation baseline, but the Atomic Option lifecycle remains `ATOMIC_INVENTORY_COMPLETE`, not `OPTION_CONTRACT_COMPLETE`, `RUNTIME_CERTIFIED` or `PRODUCT_PARITY_CERTIFIED`.

The first implementation tranche is intentionally limited to two path-disjoint, non-mutating foundations:

1. Issue #346 — canonical Status definition + registration descriptor V1;
2. Issue #347 — pure bounded transition policy model V1.

Both remain blocked until this audit reconciliation is merged. Their deterministic claim branches must not be created early.

## Planning truth

Surface 5 / Status is `BANK_REVIEWED / 129` with zero unresolved native or market review items.

The native WordPress audit is `NATIVE_AUDITED` with 35 dispositions and zero unresolved items. Relevant certified native facts include:

- `register_post_status()` is the canonical registration primitive;
- the status identifier is sanitized by Core, therefore WPE authored keys must be canonical before registration rather than silently remapped;
- `wp_posts.post_status` is `varchar(20)`, so a persistable authored status key must fit the 20-character storage bound;
- label, count labels, public/internal/protected/private, publicly-queryable, search exclusion, admin-list flags and `date_floating` are native registration semantics;
- Core derives multiple effective visibility/admin defaults when omitted, so WPE must compile explicit effective values rather than hide important runtime behavior behind implicit defaults;
- `_builtin` is Core-internal and is not an authored WPE option;
- registration must not run before `init`;
- registered status discovery is exposed by `get_post_stati()` / status objects and Core REST status endpoints;
- actual transitions must be distinguished from same-status updates even though some dynamic Core hooks fire on both;
- `wp_update_post()` is the supported native mutation primitive, but any WPE transition path must wrap it with server-owned validation and resource authorization;
- Core future, trash/untrash and attachment-inherit behavior has special lifecycle semantics that cannot be approximated by a generic custom transition engine.

The market audit is `MARKET_AUDITED` across nine capability families with zero unresolved items. It establishes demand for status definitions, presentation, applicability, permissions, transition matrices, events, scheduling/expiration, query/REST consumption and lifecycle/portability, while also preserving owner boundaries:

- workflow routing, task inboxes and conditional process orchestration belong to Forms/Workflows;
- notification/email delivery belongs to Notifications/Emails;
- durable process history belongs to Ledger, while Status may emit transition audit/event semantics;
- analytics/reporting belongs to Analytics;
- scheduled execution belongs to Cron/Jobs; Status may validate a requested target transition but does not own the scheduler;
- WooCommerce/payment/domain-specific status semantics remain provider/domain adapter behavior, not generic Status semantics;
- Query consumes statuses but retains Query composition/filter ownership.

## Exact-main dependency audit

### Ready shared foundations

The audited main already provides production-grade shared foundations that Status must reuse:

- `DefinitionRepositoryInterface` plus persistent/in-memory Definition infrastructure and revision/status lifecycle;
- `ModuleInterface`, `ModuleManifest`, `ServiceRegistryInterface`, Kernel dependency ordering and `ModuleActivationPolicyInterface`;
- `AbilityRegistry`, WordPress Ability bridge, AJAX route/nonce infrastructure and `WordPressExecutionContextFactory`;
- `PolicyEngine` plus current-user/site-bound capability checks;
- Multisite-aware `ExecutionContext`;
- generic Audit record/logger contracts and persistent/in-memory Audit implementations as reusable primitives;
- `DomainEvent` / `EventBus` primitives;
- WordPress compiled registration infrastructure for post types/taxonomies and the established module-owned runtime-registration pattern.

### Shared primitives that are not canonical production services yet

Two important primitives exist but are not registered by `Plugin::boot()` as canonical services on the audited main:

- `EventBus` is a class, but no `platform.events` service is registered;
- `AuditLoggerInterface` and implementations exist, but no canonical `platform.audit` service is registered by the base bootstrap.

Status must not manufacture a private event/audit plane and then claim shared integration. Bounded definition/policy work therefore does not depend on event dispatch or durable transition-audit composition. That integration is a later serialized closure slice after a canonical service boundary is explicitly selected and certified.

### Resource authorization gap for mutation

`PolicyEngine` currently authorizes a capability string without object arguments. The production WordPress capability checker calls `currentUserCan($capability)` and therefore is not sufficient by itself to prove object-level `edit_post($postId)` / `read_post($postId)` authorization.

Fields has a module-private `WordPressPostResourceAuthorizer`, but Status must not import a peer module's private authorization helper. Before a Status transition executor can mutate a post, the Status gate must certify an appropriate object-level authorization seam that binds:

- authenticated current user identity;
- current site and optional network;
- positive target post ID;
- native meta-capability check with the target object ID;
- current post type/current status read from the server, not public input.

This is a blocker for transition mutation, not for the first two pure foundation lanes.

### Native status registration gap

The shared `RegistrationKind` currently covers post type, taxonomy, metabox and settings page; it does not contain a Status registration kind. Status therefore cannot pretend that the existing compiled registration store already owns `register_post_status()`.

The bounded V1 plan keeps native Status registration Surface-5-owned unless a later audit proves that widening the shared Registration plane is safer. A Status registrar must consume canonical Published Status descriptors, preflight the whole batch and register through supported WordPress APIs at the correct lifecycle point.

## Bounded Status V1 baseline

The implementation gate targets the following baseline, not all 129 Bank records.

### 1. Canonical Status definition/compiler

Required:

- Surface 5-owned canonical Definition type;
- stable UUID/revision/status lifecycle;
- canonical authored status key that is already safe and <= 20 characters;
- built-in/reserved status collision rejection;
- bounded labels/count labels and native visibility/admin flags;
- explicit compiled effective defaults;
- finite post-type applicability metadata;
- deterministic compatibility fingerprint/registration descriptor;
- Published-only runtime compilation.

Arbitrary PHP/callbacks, request-selected providers and hidden `_builtin` authoring are prohibited.

### 2. Pure transition policy model

Required:

- explicit old -> new edge allow-list;
- undeclared edge denied;
- same-status update is not an actual transition;
- bounded declarative per-edge capability metadata;
- optional reason-required contract;
- bounded bulk/programmatic declarations;
- deterministic ordering/fingerprint and duplicate-edge rejection.

Role/user condition engines, workflow routing, notifications and scheduled execution are deferred.

### 3. Native registration runtime — later serialized slice

After the definition compiler promotes:

- load only canonical Published Surface 5 Status definitions;
- deterministic all-status preflight before first WordPress registration mutation;
- reject built-in/foreign existing status conflicts unless exact idempotent WPE ownership can be proven;
- use `register_post_status()` at `init` or a later certified lifecycle point;
- preserve explicit compiled effective values;
- expose inspectable fail-closed runtime state rather than partial registration claims.

No Core built-in status replacement is authorized.

### 4. Authorized transition execution — later serialized slice

After definition + transition policy promote and object-level authorization is certified:

- server-read current post type/status and active scope;
- require exact allowed transition edge;
- enforce object-level native mutation capability and any additional declared edge capability;
- perform a bounded native `wp_update_post()` status mutation;
- verify resulting status;
- do not emit an actual-transition event for same-status updates;
- preserve Core future/trash/untrash/attachment lifecycle semantics by using their documented owner paths or deferring them, never by silently approximating them;
- fail closed on stale current status, scope mismatch, invalid target or authorization denial.

### 5. Module/Ability lifecycle — later composition

A Pro `StatusModule` must use the existing shared activation policy and canonical Definition/Ability/AJAX/context services. Default Free bootstrap must not auto-enable the Pro module. No Status-private licensing, Policy engine or duplicate shared infrastructure is authorized.

### 6. Admin/portability/reference evidence — later closure slices

Before a bounded Status gate may PASS, later work must prove at minimum:

- canonical admin authoring for the bounded definition/transition model without duplicating server semantics;
- deterministic definition portability with explicit conflict behavior;
- native WordPress registration/discovery evidence;
- authorized post transition reference evidence;
- Multisite isolation;
- Core built-in/future/trash/untrash/attachment lifecycle non-regression;
- accessibility for any certified admin surface;
- exact-head WordPress/PHP/MySQL/MariaDB reference evidence;
- final exact-main closure audit/shared-truth reconciliation.

## First dependency-safe worker tranche

After this audit is promoted, exactly two workers are immediately dependency-ready and path-disjoint.

### Worker S1 — Issue #346

Claim branch: `agent/status-definition-compiler-v1`

Exclusive production boundary:

- `frameworks/Modules/Status/Definition/**`
- focused Status Definition tests.

No registration mutation, transition execution, Abilities/AJAX, admin UI, events, audit or shared truth.

### Worker S2 — Issue #347

Claim branch: `agent/status-transition-policy-v1`

Exclusive production boundary:

- `frameworks/Modules/Status/Transition/**`
- focused pure transition-policy tests.

No WordPress mutation/resource authorization, Definition compiler edits, Ability/AJAX, events/audit, Cron/Jobs, notifications, workflow routing or shared truth.

S1 and S2 may run concurrently only after the Supervisor audit merge. Deterministic remote branch creation remains the claim lock; no force/reuse.

## Later serialized dependency plan

The next slices are intentionally not claimed yet:

1. native registration runtime depends on S1;
2. transition mutation/execution depends on S1 + S2 + certified object-level resource authorization;
3. StatusModule/Ability lifecycle composes promoted registration/transition services;
4. admin authoring and portability consume the stable Definition/transition contracts;
5. real WordPress composed reference proves the production module/runtime graph;
6. final exact-main closure audit decides bounded Status PASS.

Later slices may be split into additional path-disjoint issues only after their dependency inputs are promoted. No speculative worker branch should be created in advance.

## Explicit deferred/non-certified capabilities

This entry audit does not authorize or imply:

- all 129 Status Bank records as shipped runtime;
- `OPTION_CONTRACT_COMPLETE`, `RUNTIME_CERTIFIED` or `PRODUCT_PARITY_CERTIFIED`;
- role/user/conditional workflow expression engines;
- workflow routing or task inbox ownership;
- notification/email delivery;
- Cron/Jobs scheduling or expiration execution ownership;
- analytics/reporting/payment semantics;
- arbitrary provider/domain status adapters;
- replacement of WordPress built-in statuses;
- silent future/trash/untrash/attachment lifecycle reimplementation;
- bulk transition runtime before separately bounded authorization/recovery evidence;
- production deployment or release approval.

Unsupported semantics must fail closed or remain visibly deferred.

## Audit decision

Exact-main prerequisites are sufficient to begin bounded Status foundation work, but not transition mutation or product/runtime parity.

**On promotion of this audit, Issues #346 and #347 become the only immediately valid Status worker claims and may run in parallel.**
