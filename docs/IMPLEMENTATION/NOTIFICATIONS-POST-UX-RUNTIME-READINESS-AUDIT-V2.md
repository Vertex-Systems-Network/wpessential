# Notifications — Post-UX Runtime Readiness Re-Audit V2

Issue: #826  
Parent: #817  
Audit anchor: `8f796aaf95f0957def439586eb1eb8fb2fd49d91`  
Surface: 19 / `notifications`

## Decision

**READY_FOR_BOUNDED_RUNTIME_SLICE.**

Surface 19 is now `UX_CONTRACT_COMPLETE`; the Bank/Atomic/UX product gates that blocked V1 are closed. Dedicated Notifications owner runtime is still absent, while shared Policy/Auth, Query/Relations/Roles inputs, Jobs/Cron substrate, Integrations, Audit/Observability and secret/provider boundaries are available. This is sufficient for a read-only Notification Rule definition plus dependency/recipient preview diagnostic slice. It is not sufficient to authorize delivery, retries or provider mutation.

## Runtime-family classification

| Family | V2 classification | Boundary |
|---|---|---|
| Module admission / definitions | REUSABLE_DEPENDENCY | Shared module and Definitions foundation. |
| Trigger identity / typed event references | REUSABLE_DEPENDENCY + MISSING OWNER ADAPTER | Shared Events/Abilities may be referenced; Surface 19 must own normalized rule references. |
| Recipient source preview | REUSABLE_DEPENDENCY + MISSING OWNER COMPOSITION | Query/Relations/Roles/Policy remain canonical data/authorization owners. |
| Rule definition / revision / enabled state | MISSING | Surface-owned first-slice definition responsibility; no live activation side effect. |
| Channel mapping | REUSABLE_DEPENDENCY / OUT_OF_SURFACE | Email/Webhook/other transports remain delegated owners. |
| Preferences / quiet hours / digest policy | MISSING | Definition/read-model work only in later bounded slices. |
| Dedupe / frequency caps / delivery evidence | MISSING | Requires execution-state owner before any certification. |
| Scheduling / retry | REUSABLE_DEPENDENCY / BLOCKED FOR FIRST SLICE | Jobs/Cron own substrate; no dispatch here. |
| Live/test send / external dispatch | BLOCKED | Separately authorized high-impact ability only. |
| Raw hooks/callbacks/classes or secret-bearing channel config | BLOCKED | Explicitly prohibited/delegated. |

## Smallest bounded first runtime slice

**Read-only Notification Rule Definition + dependency/recipient preview diagnostics.**

Candidate later paths:
- `frameworks/Modules/Notifications/NotificationsModule.php`
- `frameworks/Modules/Notifications/NotificationRuleDefinition.php`
- `frameworks/Modules/Notifications/NotificationsReadService.php`
- `frameworks/Modules/Notifications/NotificationsAbilityHandler.php`
- `tests/Unit/Modules/Notifications/`
- integration fixtures for Policy/Query/Roles/Jobs/Email/Webhook dependency degradation

The slice may parse schema-valid rule definitions, resolve only registered trigger/channel references, preview bounded recipient source metadata/counts where canonical owners permit, and report missing provider/dependency health. It must not enqueue, send, retry, mutate preferences, contact external providers or persist delivery state.

## Required future exact-head evidence

- schema/revision validation and deterministic normalized rule identity;
- Policy-enforced recipient preview with no authorization bypass from UI visibility;
- missing Query/Relations/Roles/channel/provider references produce explicit degraded states;
- secret references remain opaque and no credential material appears in diagnostics/export;
- multisite/site/network scope isolation and explicit Super Admin caveats where applicable;
- large-recipient previews are bounded/paginated and do not materialize unsafe full datasets;
- raw hook/callback/class input is rejected;
- Architecture Guards, PHP quality, Platform Compatibility Matrix and applicable integration/package/browser gates.

## Reliability, rollback and portability

Definitions may carry stable IDs, typed trigger references, recipient-source references and channel IDs only. Provider credentials, delivery attempts, transient queue state and environment-specific endpoints are non-portable/delegated. The first slice has no dispatch side effects, so rollback is module disablement/removal. Missing dependencies must degrade explicitly rather than falling back to unsafe direct WordPress/provider access.

## Non-certifications

No live/test send, external dispatch, retry execution, preference mutation, recipient mutation, provider credential handling, delivery evidence certification, runtime/product certification, production migration, deployment or release is authorized. Atomic lifecycle remains `UX_CONTRACT_COMPLETE`.
