# Emails — Exact-Main Runtime-Readiness Audit V1

Issue: #646  
Parent: #636  
Audit anchor: `35874640a62b9cb08cec5268e949dacd55c8427f`  
Surface: 20 / `emails`

## Decision

**NOT RUNTIME-READY.** Keep `runtime_allowed=false`.

Surface 20 is `ATOMIC_INVENTORY_COMPLETE` in the machine tracker but the accepted Emails gap matrix still blocks runtime: no reviewed Bank/runtime promotion, no dedicated Emails module, and no canonical template/layout/branding/render owner. Exact-main `frameworks/Modules` has no Emails module.

## Exact-main classification

- Bank/product runtime gate: **BLOCKED**.
- Atomic option contracts: **PLANNING INVENTORY ONLY / BLOCKED FOR RUNTIME**.
- Template persistence/revision/CAS and email-safe renderer/schema: **ABSENT**.
- Sanitizer/inliner, layouts/branding, tokens, localization/plaintext and responsive compatibility: **ABSENT**.
- Sender profiles, Notification delivery, Queries/conditions and preferences: **DEPENDENCY ONLY**.
- WordPress/third-party override adapters, diagnostics, portability, multisite and performance/security evidence: **ABSENT / DEFERRED**.

## Ownership boundaries

Emails owns safe template/render semantics only. Delivery orchestration belongs Notifications/transport owners; sender credentials remain external. Raw PHP/JS/template-language execution, arbitrary callback/class input, unsafe HTML, header injection and secret-bearing exports remain forbidden.

## Smallest next valid slice

**Not runtime code.** Next valid work is Bank seeding/native/market review, schema-valid option contracts and UX re-review.

Only after those gates, candidate first runtime slice: **read-only Template/Layout Definition + deterministic preview/diagnostics**, with no test/live send and no provider credentials.

Candidate files if separately authorized:
- `frameworks/Modules/Emails/EmailsModule.php`
- `frameworks/Modules/Emails/EmailTemplateDefinition.php`
- `frameworks/Modules/Emails/EmailRenderReadService.php`
- unit tests under `tests/Unit/Modules/Emails/`
- sanitizer/render fixtures and dependency degradation integration tests

## Exit

Issue #646 is evidence-accounted as **BLOCKED / NOT RUNTIME-READY**. No email sending, provider mutation, runtime implementation, certification or shared-truth changes are introduced.
