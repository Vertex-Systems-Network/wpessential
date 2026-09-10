# Forms & Workflows — Native & Market Evidence Matrix V1

Surface: **17 / Forms & Workflows**  
Issue: **#500**  
Research anchor: **`main @ 42303a97938f1a80f9c1c9088fa70d40ba359279`**  
Mode: planning/contract only; `runtime_allowed=false`; `product_parity_allowed=false`.

## Verified primary sources

| Source | Evidence | Seed families informed |
|---|---|---|
| WordPress nonce verification | https://developer.wordpress.org/reference/functions/wp_verify_nonce/ | CSRF intent verification with explicit warning that nonces are not authentication/authorization |
| WordPress REST custom endpoints | https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/ | typed endpoint arguments plus mandatory `permission_callback` authorization boundary |
| WordPress Roles & Capabilities | https://developer.wordpress.org/apis/security/user-roles-and-capabilities/ | server-side capability checks for public/admin data submission operations |
| Gravity Forms validation | https://docs.gravityforms.com/validation/ | required/field/state validation before entry persistence and custom validation extension points |
| Gravity Forms `gform_validation` | https://docs.gravityforms.com/gform_validation/ | custom server-side validation lifecycle prior to submission processing |
| Gravity Forms submission lifecycle | https://docs.gravityforms.com/submitting-forms-with-the-gfapi/ | validation, anti-spam, entry storage, feeds, notifications and confirmations as separable lifecycle stages |
| Fluent Forms Developer Action Hooks | https://developers.fluentforms.com/hooks/actions/ | submission, form, payment, settings and integration lifecycle hook families |

## Native/market decisions added in this pass

- Nonces prove request intent/freshness, not authorization. Every mutation/action target must still pass canonical capability/Policy/Ability checks.
- REST/public submission endpoints need explicit permission policy and typed validation. “Public form” does not imply that every configured downstream action is public.
- Market evidence separates field validation, state validation, spam/abuse checks, entry persistence, feeds/actions, notifications and confirmations. WPE Bank records should preserve these as distinct stages rather than one opaque submit callback.
- Server validation remains authoritative for values that can persist or trigger actions; client validation is UX/preflight only.
- Workflow actions must reference registered Abilities or provider adapters. Arbitrary PHP/callback/webhook executable input is rejected; remote transport belongs to Connections/Webhooks and secrets to Vault.
- Retry/checkpoint/replay can be planned as workflow reliability semantics, but no executor/queue mutation is authorized in this lane.

## Seed disposition evidence

- `forms-workflows.form.identity` — stable form identity, configuration and lifecycle are established form-platform concepts; WPE adds revision ownership rather than copying provider stores.
- `forms-workflows.fields.configuration` — field definition/validation should consume canonical Fields/control contracts and adapter mappings where provider-specific behavior exists.
- `forms-workflows.calculation.server` — calculations that affect stored/actionable results must be server authoritative; client calculation may only be presentation/preflight.
- `forms-workflows.submission.policy` — authorization, nonce/CSRF, abuse controls and duplicate/idempotency policy are first-class security requirements.
- `forms-workflows.entry.storage` — entry values/retention/privacy need a canonical storage/privacy owner; provider imports must not leak secrets or duplicate authoritative records silently.
- `forms-workflows.action.mapping` — actions must map to registered Abilities/canonical target owners rather than arbitrary callbacks/code.
- `forms-workflows.run.reliability` — retry/checkpoint/replay are valid workflow-runtime concerns but actual execution remains separately gated.
- `forms-workflows.secrets.references` — credentials/tokens must be Vault-backed references; ordinary form portability must not export secret material.

## Remaining gates

This matrix does **not** promote `BANK_REVIEWED`. Native admin-post/AJAX/form handling patterns, anti-spam breadth, multi-step/draft/save-resume, conditional logic, notification/confirmation ownership, payment/provider boundaries, retention/privacy, workflow reliability/provider parity and zero-unresolved review remain open. No workflow execution is authorized.
