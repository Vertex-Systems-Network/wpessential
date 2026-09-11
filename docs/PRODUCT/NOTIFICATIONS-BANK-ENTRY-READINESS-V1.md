# Notifications — Bank Entry Readiness V1

Surface: **19 / Notifications**  
Planning issue: **#585**  
Supervisor wave: **#583**  
Exact-main claim anchor: `3390739c85f45787743778372c616ef5c9f5d5df`

Status: **BANK ENTRY READY FOR RESEARCH/SEEDING — NOT BANK_REVIEWED — NO OPTION CONTRACT PROMOTION**

This document is planning evidence only. It does not seed or review the Master Options Bank, create schema-valid Atomic Option Contracts, authorize runtime implementation, send notifications, call providers or mutate user preferences.

## Current machine truth

Exact-main project state distinguishes three planning layers that must not be conflated:

- Surface 19 `notifications` is `UNSEEDED` with **0 normalized Master Options Bank records**.
- The global atomic planning ledger marks Surface 19 `ATOMIC_INVENTORY_COMPLETE`, meaning detailed planning inventory exists but schema-valid per-option contracts are still a later gate.
- The canonical ownership index names Surface 19 as the owner of notification occurrence/routing; Email Surface 20 owns email rendering and Connections/Webhooks Surface 23 owns webhook transport.

Therefore the next valid product gate is **Bank seeding + native/market review**, not `OPTION_CONTRACT_COMPLETE`, `UX_CONTRACT_COMPLETE`, runtime development or parity certification.

## Existing in-repo planning evidence

The repository already contains a strong pre-Bank source inventory:

1. `docs/MODULES/NOTIFICATION-SYSTEM-EXHAUSTIVE-SPEC.md` defines Notification Rule, Notification Instance, Delivery Attempt, User Preference and Digest as separate concepts.
2. `docs/MODULES/OPTION-INVENTORY.md` records the initial Notifications screens/options so later implementation does not invent unplanned semantics.
3. `docs/PRODUCT/56-SURFACE-COMPETITOR-PARITY-MATRIX.md` establishes Notifications as a product-parity surface with typed-event triggers and market-credible notification behavior.
4. `docs/MODULES/CANONICAL-OPTION-OWNERSHIP-INDEX-56-SURFACES.md` resolves the core ownership split: Notifications owns occurrence/routing, Email owns email-safe render/template behavior and Connections/Webhooks owns transport.

These sources are suitable inputs for Bank seeding, but none of them substitutes for normalized Bank records or a completed native/market audit.

## Canonical concept boundary for Bank seeding

The Bank should normalize options around these distinct records rather than flatten them into one settings object:

### Notification Rule

Owns:

- stable identity/name/category/lifecycle;
- typed Event or registered Ability trigger reference;
- bounded conditions;
- recipient-resolution policy;
- preference class and priority;
- channel-routing policy;
- delay/digest/dedupe/frequency/escalation policy;
- content/template references, not provider-owned executable payloads;
- revision identity and dependency metadata.

### Notification Instance

Owns one logical recipient notification occurrence:

- Rule/revision identity;
- event/correlation identity;
- recipient subject;
- authorization-sensitive resource references;
- lifecycle state;
- timestamps/expiry;
- safe snapshot references required by deterministic delivery/audit.

### Delivery Attempt

Owns one channel attempt:

- channel/adapter/connection reference;
- normalized delivery state;
- attempt/retry/idempotency data;
- provider-safe evidence and redacted error category;
- queued/accepted/confirmed timestamps without falsely treating provider acceptance as confirmed delivery.

### User Preference

Owns only preference-capable categories/channels:

- category/channel enabled state;
- preferred channel;
- quiet-hours profile;
- digest preference;
- locale preference where applicable.

Required system/security classification must be narrowly policy-controlled and must not become a promotional opt-out bypass.

### Digest

Owns batching/container semantics:

- cadence/timezone;
- grouping/sort/deduplication;
- max items/overflow;
- references or bounded safe snapshots;
- delivery state.

## Candidate Bank families

Bank seeding should classify at least the following families independently:

1. **Rule identity/lifecycle** — name, stable key/UUID, description, category, tags, draft/enabled/disabled/archived, revision.
2. **Trigger/event binding** — typed event/version/source, registered manual action, scope, event filters, dedupe mapping.
3. **Conditions** — shared Condition Engine references; no raw PHP/JS expressions.
4. **Recipients** — specific users, actor/subject/owner, roles/capabilities, Query users, relation-derived users, Membership/team refs, registered recipient provider, tightly validated external endpoint where channel permits.
5. **Eligibility/authorization** — resource authorization at render/delivery, verification/address health, preference, quiet-hours, rate/frequency controls.
6. **Priority/classification** — low/normal/high/critical-system plus required/transactional/optional class with anti-abuse policy.
7. **Channels** — in-app/admin, frontend dashboard, Email reference, Webhook/Connection reference and registered future adapters.
8. **Content/tokens** — in-app title/body/actions, template references, allowlisted token schemas, localization/fallback, bounded conditional blocks.
9. **Scheduling** — immediate, delay, date/time, recipient timezone policy, trigger-time vs delivery-time snapshot behavior delegated to Job Service for execution.
10. **Quiet hours/digests** — defer/digest/skip rules, priority bypass policy, cadence/grouping/overflow.
11. **Dedupe/frequency** — event/custom/time-window keys, suppress/update behavior, per-recipient caps.
12. **Instance/inbox state** — unread/read/dismissed/expired/revoked, mark-read semantics and bounded bulk operations.
13. **Delivery/retry evidence** — normalized statuses, retry categories/backoff/idempotency, provider acceptance vs confirmed delivery.
14. **Preferences/defaults** — per-user allowed controls plus site defaults; legal/compliance classification remains site responsibility.
15. **Escalation/events** — simple failure/unread-threshold event emission; complex branching remains Workflow-owned.
16. **Preview/test** — render preview, dry-run recipient resolution, explicitly marked test delivery capability.
17. **Retention/privacy/logging** — log retention, payload limits, redaction, sensitive-recipient detail permissions.
18. **Permissions/Abilities** — manage/publish/send/test/log/sensitive/preferences/self-inbox boundaries.
19. **Reliability/performance** — batching, queue/job handoff, indexes, bounded fan-out, cleanup, recursion guards and health/degraded states.
20. **Portability/dependencies** — Rule/template/connection/event references, revision dependencies and environment conflict reporting.

## Native WordPress audit requirements before Bank review

A future Bank-native audit must explicitly classify current WordPress behavior rather than assuming a native notification subsystem exists. At minimum audit:

- user/admin email helpers and mail hooks;
- comment/content/user lifecycle hooks only as **native event candidates**, not arbitrary-hook execution configuration;
- WP-Cron semantics as a scheduling primitive while Job Service remains the WPE execution boundary;
- user meta/options suitability and privacy limits for preferences/read state;
- REST/admin-AJAX capabilities only where they can expose bounded self-service/admin Abilities;
- multisite/network user/site scoping;
- native capability checks and nonce/session behavior;
- any core admin notices separately from persistent per-user Notification Instances.

Native hooks must be wrapped by registered typed Event adapters. The product must not expose arbitrary hook names + executable callbacks as user configuration.

## Market audit requirements before Bank review

The competitor-parity matrix names Better Notifications for WP plus broader automation/membership/form notification systems as benchmarks. The future market audit must turn that high-level benchmark into evidence-backed provider rows, especially for:

- trigger breadth and safe event adapters;
- recipient targeting and dynamic recipient resolution;
- in-app vs email vs webhook/channel routing;
- template/token/localization behavior;
- user preferences/unsubscribe/quiet hours;
- digest/dedupe/frequency policy;
- retry/delivery logging and provider evidence;
- test/preview and failure diagnostics;
- revision/export/dependency behavior;
- high-volume batching and provider health.

The market audit should compare semantics, not clone competitor UI or assume every competitor feature is safe/canonical.

## Ownership decisions that are already clear

| Concern | Canonical owner |
|---|---|
| Notification occurrence, recipient routing, notification instance/inbox state, preference class, digest/dedupe/frequency policy | **Surface 19 Notifications** |
| Email-safe component/template rendering, subject/preheader/branding/locale | **Surface 20 Emails** |
| Webhook/provider connection credentials, OAuth, Safe HTTP, signing, transport retry primitives | **Surface 23 Connections/Webhooks** |
| Complex branch/wait/approval/escalation orchestration | **Surface 17 Forms & Workflows** |
| Delayed execution scheduling / durable Job execution | **Surface 18 Cron + shared Job Service as appropriate** |
| Query-based recipient resolution | **Surface 6 Query**, consumed by Notifications |
| Membership/entitlement membership truth | **Surface 15 Membership**, consumed read-only |
| Role/capability membership truth | **Surface 30 Roles & Capabilities / shared Policy** |
| Media/file authorization | Owning media/file/resource surface; Notifications stores references only |

## Safety decisions to carry into Bank policy

The following should be classified as rejected-unsafe or bounded/deferred rather than normalized as arbitrary configuration:

- raw PHP/JS expressions or callback/class names;
- arbitrary WordPress hook subscription by user-entered string without registered Event adapter;
- generic `user.meta.*`/raw-object token dumps;
- secret-bearing webhook/email headers or provider credentials inside Notification Rules;
- unbounded recipients/fan-out inside one PHP request;
- unbounded retry loops;
- treating SMTP/provider acceptance as confirmed delivery;
- promotional use of critical/system preference bypass;
- protected-resource content rendered without delivery-time authorization;
- recursive notification-event loops without cycle guards;
- arbitrary external action URLs without validation/allowlist policy.

## Readiness decision

Surface 19 has enough in-repo semantic material to start a disciplined Bank-seeding/native/market review lane, but **it is not ready to feed schema-valid Atomic Option Contracts yet** because exact-main machine truth remains `UNSEEDED / 0` and no Bank-native/market review evidence exists.

Next product gate:

1. normalize the candidate families into Master Options Bank records;
2. complete current native WordPress audit;
3. complete evidence-backed market audit;
4. resolve duplicates, ownership conflicts, rejected-unsafe/deferred items;
5. promote only after the Bank has zero unresolved review items;
6. then derive schema-valid Atomic Option Contracts and review the UX against those contracts.

Until those steps occur, any Notifications UX or runtime matrix is **provisional planning guidance**, not a lifecycle promotion or runtime authorization.
