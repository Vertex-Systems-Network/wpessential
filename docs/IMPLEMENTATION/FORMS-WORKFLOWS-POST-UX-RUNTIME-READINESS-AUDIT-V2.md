# Forms & Workflows — Post-UX Runtime Readiness Re-Audit V2

Issue: #824  
Parent: #817  
Audit anchor: `8f796aaf95f0957def439586eb1eb8fb2fd49d91`  
Surface: 17 / `forms-workflows`

## Decision

**READY_FOR_BOUNDED_RUNTIME_SLICE.**

Surface 17 is `UX_CONTRACT_COMPLETE`; its historical V1 product-gate blocker is closed. Owner runtime remains absent. Existing Fields plus Platform Abilities/Auth, Definitions, Events, Jobs, Integrations and Secrets provide sufficient reusable seams for a definition/validation/dependency-diagnostic first slice without executing submissions or workflows.

## Runtime-family classification

| Family | V2 classification | Boundary |
|---|---|---|
| Module admission / definitions | REUSABLE_DEPENDENCY | Shared Platform foundation exists. |
| Field/control schema | REUSABLE_DEPENDENCY | Fields is canonical. |
| Registered abilities / authorization | REUSABLE_DEPENDENCY | Platform Abilities/Auth remains authoritative. |
| Secret references | REUSABLE_DEPENDENCY | Platform Secrets owns secret material; reference-only here. |
| Form/workflow definition | MISSING | Requires Surface 17 owner definition. |
| Validation/dependency preflight | MISSING | Suitable read-only first-slice responsibility. |
| Submission/entry persistence | MISSING | Later tranche with privacy/replay evidence. |
| Workflow/job execution | OUT_OF_SURFACE / LATER | Delegates to registered Ability/Job/provider targets. |
| Raw executable workflow input | BLOCKED | `REJECTED_UNSAFE`; remains prohibited. |

## Smallest bounded first runtime slice

**Read-only Form/Workflow Definition + validation/dependency/preflight diagnostics.**

Candidate later paths:
- `frameworks/Modules/FormsWorkflows/FormsWorkflowsModule.php`
- `frameworks/Modules/FormsWorkflows/FormDefinition.php`
- `frameworks/Modules/FormsWorkflows/WorkflowDefinition.php`
- `frameworks/Modules/FormsWorkflows/FormsWorkflowsReadService.php`
- `frameworks/Modules/FormsWorkflows/FormsWorkflowsAbilityHandler.php`
- `tests/Unit/Modules/FormsWorkflows/`

The slice may validate Fields/Ability/Job/provider/Secret references, conditions/data-map structure and report capability/degraded state. It must not accept submissions, write entries, execute actions/jobs/payments, reveal secrets, or run raw hooks/callbacks.

## Required future exact-head evidence

- deterministic form/workflow definition and reference validation;
- server-authoritative validation and ability-policy tests;
- malformed/raw executable target rejection tests;
- secret-reference redaction and portability tests;
- provider/job/ability unavailable/degraded diagnostics;
- bounded condition/data-map scale tests;
- Architecture Guards, PHP quality, Platform Compatibility Matrix and applicable package/browser/integration gates.

## Rollback, portability and degraded behavior

Portability contains definitions and stable references only; secrets are never embedded. Missing abilities/providers/jobs are explicit degraded/unavailable states and are not executed during preflight. The read-first slice performs no durable form entry or run mutation, so rollback is module disablement/removal.

## Non-certifications

No submission processing, entry mutation, workflow/job/payment/provider execution, secret export, runtime/product certification, production migration, deployment or release is authorized. Atomic lifecycle remains `UX_CONTRACT_COMPLETE`.
