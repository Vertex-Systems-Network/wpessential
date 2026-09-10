# Forms & Workflows — Native & Market Evidence Matrix V1

Surface: **17 / Forms & Workflows**  
Issue: **#500**  
Research anchor: **`main @ fad3b2f37bd44a01d77438e71877d7990b4bfe2c`**  
Mode: planning/contract only; `runtime_allowed=false`; `product_parity_allowed=false`.

## Verified primary sources

| Source | Evidence | Seed families informed |
|---|---|---|
| Gravity Forms Developer Documentation | https://docs.gravityforms.com/category/developers/ | form/entry/field data objects and extensible form lifecycle APIs |
| Fluent Forms Developer Action Hooks | https://developers.fluentforms.com/hooks/actions/ | submission, form, payment, settings and integration lifecycle hook families |
| Fluent Forms Form Hooks | https://developers.fluentforms.com/hooks/actions/form/ | form create/render/delete/display lifecycle events |

## Seed disposition evidence

- `forms-workflows.form.identity` — stable form identity, configuration and lifecycle are established form-platform concepts; WPE adds revision ownership rather than copying provider stores.
- `forms-workflows.fields.configuration` — field definition/validation should consume canonical Fields/control contracts and adapter mappings where provider-specific behavior exists.
- `forms-workflows.calculation.server` — calculations that affect stored/actionable results must be server authoritative; client calculation may only be presentation/preflight.
- `forms-workflows.submission.policy` — authorization, abuse controls and duplicate/idempotency policy are first-class security requirements.
- `forms-workflows.entry.storage` — entry values/retention/privacy need a canonical storage/privacy owner; provider imports must not leak secrets or duplicate authoritative records silently.
- `forms-workflows.action.mapping` — actions must map to registered Abilities/canonical target owners rather than arbitrary callbacks/code.
- `forms-workflows.run.reliability` — retry/checkpoint/replay are valid workflow-runtime concerns but actual execution remains separately gated.
- `forms-workflows.secrets.references` — credentials/tokens must be Vault-backed references; ordinary form portability must not export secret material.

## Remaining gates

This matrix does **not** promote `BANK_REVIEWED`. Submission lifecycle breadth, confirmations/notifications, conditional logic, multi-step/drafts, payments, integrations, spam controls, retention/privacy, workflow reliability/provider parity and zero-unresolved review remain open. No workflow execution is authorized.