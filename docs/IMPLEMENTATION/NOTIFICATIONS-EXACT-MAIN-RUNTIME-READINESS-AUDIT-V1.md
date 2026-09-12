# Notifications — Exact-Main Runtime-Readiness Audit V1

Issue: #645  
Parent: #636  
Audit anchor: `35874640a62b9cb08cec5268e949dacd55c8427f`  
Surface: 19 / `notifications`

## Decision

**NOT RUNTIME-READY.** Keep `runtime_allowed=false`.

Surface 19 is `ATOMIC_INVENTORY_COMPLETE` in the machine tracker but remains explicitly blocked by the accepted Notifications gap matrix: no normalized reviewed Bank promotion for runtime, no dedicated Notifications runtime module, and no canonical Rule/Instance/Delivery/Preference/Digest ownership. Exact-main `frameworks/Modules` has no Notifications module.

## Exact-main classification

- Master Options Bank/runtime product gate: **BLOCKED**.
- Atomic option contracts: **PLANNING INVENTORY ONLY / BLOCKED FOR RUNTIME**.
- Notification Rule persistence/revision/CAS: **ABSENT**.
- Typed trigger registry, recipient resolution and delivery-time authorization: **ABSENT**.
- In-app inbox, preferences, quiet hours, digest, dedupe, frequency caps: **ABSENT**.
- Email/Webhook channels: **DEPENDENCY ONLY**; delivery owners stay external.
- Scheduling/retry: **DEPENDENCY / ABSENT**; Job/Cron and provider adapters remain owners.
- Delivery evidence/logs, portability, multisite, accessibility, performance and reliability: **ABSENT / UNCONTRACTED**.

## Ownership boundaries

Notifications must consume shared Policy, Query, Relations, Membership/Roles, Cron/Job, Emails and Connections/Webhooks seams. It must not own mail credentials, generic HTTP/OAuth secrets, arbitrary WordPress hook execution, raw user-meta token access or protected resource authorization. Live/test send is a separately authorized high-impact ability.

## Smallest next valid slice

**Not runtime code.** The next valid product slice is Bank seeding/native/market review followed by schema-valid option contracts and UX re-review.

Only after those gates, candidate first runtime slice: **read-only Rule Definition + dependency/recipient preview diagnostics**, with no live/test delivery.

Candidate files if separately authorized:
- `frameworks/Modules/Notifications/NotificationsModule.php`
- `frameworks/Modules/Notifications/NotificationRuleDefinition.php`
- `frameworks/Modules/Notifications/NotificationsReadService.php`
- unit tests under `tests/Unit/Modules/Notifications/`
- integration tests for Policy/Query/Email/Job dependency degradation

## Exit

Issue #645 is evidence-accounted as **BLOCKED / NOT RUNTIME-READY**. No notification dispatch, runtime implementation, certification or shared-truth changes are introduced.
