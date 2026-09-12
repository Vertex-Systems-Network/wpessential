# Forms & Workflows — Market Audit V1

Status: `RESEARCH_COMPLETE / SUPERVISOR_INTEGRATION_PENDING`

Surface: 17 — Forms & Workflows  
Snapshot: 2026-09-12  
Parent: #713  
Worker issue: #720  
Base: `54e4e70095f74a6ebff15da83cc2bbf3d447a49c`

## 1. Scope and boundary

This evidence-only audit reconciles all 17 current records. Forms/Workflows owns definitions, submission/entry lifecycle and orchestration references. Fields owns reusable controls; Abilities/Policy authorize actions; payment/provider secrets stay with canonical providers/Vault; arbitrary executable authored workflow input remains rejected.

## 2. Current market evidence

### E1 — Gravity Forms
Official evidence:
- https://docs.gravityforms.com/page-conditional-logic/
- https://docs.gravityforms.com/personal-data/
- https://docs.gravityforms.com/reviewing-form-submissions/

Verified: multi-page/conditional forms, entry storage/management, notes, notifications and retention/deletion settings.

### E2 — Gravity Flow
Official evidence:
- https://docs.gravityflow.io/article/67-workflow-steps
- https://docs.gravityflow.io/article/72-approval-step
- https://docs.gravityflow.io/article/88-user-input-step
- https://docs.gravityflow.io/article/87-outgoing-webhook-step

Verified: ordered workflow steps, approval/user-input/notification/outgoing-webhook actions, assignees, scheduling, due/expiration/reminders and workflow state.

### E3 — Fluent Forms / Formidable Forms
Official evidence:
- https://fluentforms.com/docs/conditional-logic/
- https://fluentforms.com/docs/payment-settings/
- https://formidableforms.com/knowledgebase/calculations/

Verified: conditional logic, integrations/payments, entry handling, calculations and conditional form behavior.

## 3. Record reconciliation

| Record | Disposition |
|---|---|
| `forms-workflows.form.identity` | MARKET_EVIDENCED — named forms/workflows and lifecycle are standard. |
| `forms-workflows.form.layout` | MARKET_EVIDENCED — multi-page/sections/steps are common. |
| `forms-workflows.controls.registry` | MARKET_EVIDENCED_WITH_FIELDS_OWNER — rich controls exist; schema remains Fields-owned. |
| `forms-workflows.controls.validation` | MARKET_EVIDENCED / WPE_HARDENED — calculations/validation are common; server remains authoritative. |
| `forms-workflows.submission.policy` | MARKET_EVIDENCED / WPE_HARDENED — submission restrictions exist; nonce is not authorization and duplicate/rate policy stays explicit. |
| `forms-workflows.submission.confirmation` | MARKET_EVIDENCED — post-submit messages/redirect/next-state behavior is parity. |
| `forms-workflows.entry.storage-retention` | MARKET_EVIDENCED — E1 proves stored entries and timed retention/deletion. |
| `forms-workflows.entry.management` | MARKET_EVIDENCED — entry notes/review/export-style management is standard; anonymization remains privacy policy. |
| `forms-workflows.workflow.owner-order` | MARKET_EVIDENCED_WITH_ABILITY_OWNER | E2 ordered steps prove orchestration; targets remain registered canonical actions/providers. |
| `forms-workflows.workflow.conditions-mapping` | MARKET_EVIDENCED — conditions/data mapping are mainstream workflow features. |
| `forms-workflows.workflow.failure` | KEEP_WPE_HARD / PROVIDER_VARIANT — timeout/retry/compensation guarantees vary and need explicit semantics. |
| `forms-workflows.run.state` | MARKET_EVIDENCED / WPE_HARDENED — E2 workflow status/step lifecycle supports run-state family; checkpoints/resume/cancel need deterministic contracts. |
| `forms-workflows.run.replay` | KEEP_WPE_EXCEED — correlation/idempotent replay protection is not safely inferred from provider UI. |
| `forms-workflows.integration.payment` | PROVIDER_EVIDENCED — payments are common but execution/reconciliation remains payment-provider-owned. |
| `forms-workflows.integration.ability-secret` | KEEP_WPE_HARD — actions use registered Ability/provider references and Vault secret references only. |
| `forms-workflows.safety.raw-executable` | KEEP `REJECTED_UNSAFE` — market extensibility is not evidence to accept raw PHP/script/callback authored input. |
| `forms-workflows.submission.invocation-channel` | KEEP native + market compatibility — admin-post/AJAX/REST are bounded invocation substrates; raw hook strings remain forbidden. |

Coverage: **17 / 17**. Unresolved research dispositions: **0**. Worker Bank/progress mutations: **0**.

## 4. WPE-exceed / safety

- Client validation/calculations never replace server-authoritative validation.
- Workflow steps reference registered Abilities/providers; no executable code is stored in definitions.
- Retry, compensation, checkpoint and replay semantics must be explicit and idempotent before execution.
- Payment state/secrets remain provider/Vault-owned and are excluded from portable form definitions.

## 5. Supervisor integration requirements

A later Supervisor may add E1–E3 market provenance and classify evidenced form/layout/entry/condition/workflow families while preserving failure/replay/payment/secret safety boundaries. `MARKET_AUDITED` requires exact-head Bank reconciliation and CI.

No workflow/payment execution, `BANK_REVIEWED`, runtime/product certification, deployment or release is authorized.