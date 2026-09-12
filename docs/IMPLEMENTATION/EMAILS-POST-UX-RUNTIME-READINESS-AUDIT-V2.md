# Emails — Post-UX Runtime Readiness Re-Audit V2

Issue: #827  
Parent: #817  
Audit anchor: `8f796aaf95f0957def439586eb1eb8fb2fd49d91`  
Surface: 20 / `emails`

## Decision

**READY_FOR_BOUNDED_RUNTIME_SLICE.**

Surface 20 is now `UX_CONTRACT_COMPLETE`; the Bank/Atomic/UX prerequisites that blocked V1 are closed. Dedicated Emails owner runtime is absent, while shared Definitions, Rendering, Assets/Media-facing references, Secrets, Integrations, Auth/Policy and observability seams are available. This is sufficient for a deterministic read-only Template/Layout Definition plus safe preview/diagnostic slice. It is not sufficient to authorize sending or provider credential use.

## Runtime-family classification

| Family | V2 classification | Boundary |
|---|---|---|
| Module admission / definitions | REUSABLE_DEPENDENCY | Shared module and Definitions foundation. |
| Template/layout identity and revision | MISSING | Surface-owned first-slice definition responsibility. |
| Deterministic rendering / preview | REUSABLE_DEPENDENCY + MISSING OWNER PROFILE | Shared Rendering can be consumed; email-safe schema/profile remains Surface-owned. |
| Tokens / dynamic values | REUSABLE_DEPENDENCY | Consume registered typed values only; no executable template language. |
| Sanitization / email-safe markup policy | MISSING | Must be explicitly owned before mutation/send paths. |
| Brand/media references | REUSABLE_DEPENDENCY / OUT_OF_SURFACE | Refer to canonical asset/media owners; do not duplicate binaries. |
| Sender profile / credentials | OUT_OF_SURFACE / BLOCKED | Secrets/provider owners retain custody. |
| Delivery orchestration / transport | OUT_OF_SURFACE / BLOCKED | Notifications/transport owners; no send from first slice. |
| Third-party overrides / compatibility | REUSABLE_DEPENDENCY + MISSING ADAPTER | Integrations may expose compatibility metadata only. |
| Raw PHP/JS/template-language execution, unsafe HTML/header injection | BLOCKED | Explicitly prohibited. |

## Smallest bounded first runtime slice

**Read-only Email Template/Layout Definition + deterministic safe preview diagnostics.**

Candidate later paths:
- `frameworks/Modules/Emails/EmailsModule.php`
- `frameworks/Modules/Emails/EmailTemplateDefinition.php`
- `frameworks/Modules/Emails/EmailRenderReadService.php`
- `frameworks/Modules/Emails/EmailsAbilityHandler.php`
- `tests/Unit/Modules/Emails/`
- sanitizer/render fixtures plus integration tests for DynamicValues/Assets/Secrets/provider degradation

The slice may load schema-valid template/layout definitions, resolve only registered typed tokens/media references, render a deterministic non-sending preview, and report missing sender/provider/dependency health. It must not call `wp_mail`, contact transport providers, expose secrets, mutate sender profiles or execute arbitrary template code.

## Required future exact-head evidence

- deterministic revisioned template/layout parsing and stable IDs;
- allowlisted token resolution with explicit missing-token/degraded states;
- email-safe sanitization and header-injection rejection;
- no raw PHP/JS/eval/callback/template-language execution;
- plaintext and localization fallback behavior where contracted;
- media/reference portability without embedding secret/private environment paths;
- bounded preview size/performance and multisite/site/network scope isolation;
- sender/provider secrets remain opaque and absent from diagnostics/export;
- Architecture Guards, PHP quality, Platform Compatibility Matrix and applicable integration/package/browser gates.

## Reliability, rollback and portability

Portable definitions may contain stable template/layout IDs, typed token references, safe markup/content and canonical media references only. Credentials, transport state, provider message IDs and environment-specific sender secrets are non-portable/delegated. The first slice has no send side effects; rollback is module disablement/removal. Missing renderer/token/media/provider dependencies must fail explicitly without unsafe fallback execution.

## Non-certifications

No test/live send, transport/provider execution, credential handling, delivery orchestration, unsafe HTML execution, sender mutation, runtime/product certification, production migration, deployment or release is authorized. Atomic lifecycle remains `UX_CONTRACT_COMPLETE`.
