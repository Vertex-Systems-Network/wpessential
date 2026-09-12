# Forms & Workflows — UX Contract V1

Surface: **17 / Forms & Workflows**  
Machine source: `config/product/option-contracts/forms-workflows.json`  
Lifecycle: **UX certification candidate**. The machine contract is `OPTION_CONTRACT_COMPLETE`; this document does not authorize workflow execution.

## Lifecycle preconditions

- Machine status must remain `OPTION_CONTRACT_COMPLETE` or later with `missing=0` and `unclassified=0`.
- All 17 current Atomic Option IDs are mapped exactly once below.
- Form validation, submission authorization, workflow state, payment/provider operations and secret access remain server-authoritative.

## Canonical route and information architecture

Canonical admin location: **WPEssential → Automation → Forms & Workflows**.

The IA separates **Form Definition**, **Controls & Validation**, **Submission**, **Entries**, **Workflow Actions**, **Run State**, **Integrations**, and **Safety**. Advanced authored policies are distinct from Expert provider/Ability/Vault references and System prohibited states.

## UX state classes

### Authored definition
Form identity/layout, validation, submission policy/confirmation, entry retention/management, workflow ordering/conditions/failure policy and run reliability are revisioned Forms-owned definitions.

### Effective/runtime state
Current form revision, validation outcome, run/checkpoint state, retry/correlation state and invocation channel health are effective/read-only context until an authorized runtime action exists.

### Diagnostic/provider state
Fields registry, payment providers, registered Abilities and Vault secret references are canonical-owner/provider references with explicit health/degraded state.

### Deferred / prohibited state
Raw PHP/script/executable workflow input is prohibited. Payment settlement and secrets are never local authored/runtime truth.

## Atomic Option → UX map

- `forms-workflows.form.identity` — Form Definition → identity/category/lifecycle/revision editor.
- `forms-workflows.form.layout` — Form Definition → steps/sections/layout composition with accessible ordering.
- `forms-workflows.controls.registry` — Controls & Validation → Fields-owned field/control registry reference and prefill source diagnostics.
- `forms-workflows.controls.validation` — Controls & Validation → server-authoritative validation/calculation policy with preview feedback.
- `forms-workflows.submission.policy` — Submission → rate-limit/duplicate/authorization policy.
- `forms-workflows.submission.confirmation` — Submission → confirmation/message/redirect behavior with safe destination validation.
- `forms-workflows.entry.storage-retention` — Entries → storage/retention/privacy policy and effective retention preview.
- `forms-workflows.entry.management` — Entries → notes/assignment/export/anonymization policy; runtime mutations remain separately gated.
- `forms-workflows.workflow.owner-order` — Workflow Actions → registered action/Ability owner and deterministic ordering.
- `forms-workflows.workflow.conditions-mapping` — Workflow Actions → bounded conditions and typed data mapping.
- `forms-workflows.workflow.failure` — Workflow Actions → Expert timeout/retry/backoff/failure/compensation policy.
- `forms-workflows.run.state` — Run State → checkpoint/resume/cancel state model and read-only run diagnostics.
- `forms-workflows.run.replay` — Run State → correlation/idempotency/replay-protection policy and evidence.
- `forms-workflows.integration.payment` — Integrations → certified payment-provider reference; payment execution/settlement remains provider-owned.
- `forms-workflows.integration.ability-secret` — Integrations → registered Ability plus Vault secret reference; no secret material is exposed.
- `forms-workflows.safety.raw-executable` — Safety → prohibited raw executable workflow input shown read-only with remediation.
- `forms-workflows.submission.invocation-channel` — Submission → registered admin-post/AJAX/REST invocation-channel identity with mandatory Policy checks.

## Interaction and persistence

Editing uses draft → validate → save revision. Layout/control changes retain unresolved Fields references for explicit repair. Workflow action reordering has keyboard alternatives. Test/preview mode does not perform external payment/provider actions unless a separately authorized sandbox operation is explicitly invoked.

## Loading, empty, validation, conflict and recovery

Required states include empty form, control registry loading/unavailable, invalid validation rule, duplicate/rate-limited submission preview, entry retention warning, missing Ability/payment/Vault provider, workflow mapping error, retry/degraded state, stale revision conflict, saved and recovery. Failed saves preserve authored input.

## Security and ownership

Fields owns reusable field/control schema and value semantics. Ability/Policy owns executable action authorization. Payment providers own payment execution/reconciliation. Vault owns secret material. Forms & Workflows owns orchestration definitions only. Raw executable workflow input is prohibited. Client-visible invocation channels never bypass permission checks.

## Accessibility

Form/editor controls require labels, descriptions, keyboard operation, visible focus and linked errors. Step navigation is semantically announced. Workflow ordering has keyboard alternatives. Validation summaries link to controls; async submission/run status uses appropriate live regions without trapping focus.

## Multisite and scope

Form definitions, provider references and entry policy display site/network scope. Network context is server-derived. Imports cannot silently remap a site form to network/global execution or cross-site secret/payment providers.

## Portability and reference remapping

Exports are definition-only and secret-free. Imports validate Fields, Ability, payment, Vault and route references, report unresolved mappings and preserve rejected raw-executable state. Runtime entries/runs/payment state are excluded from definition portability unless separately scoped.

## Performance and scale

Large forms/workflows use bounded/lazy editor rendering and avoid N+1 provider lookups. Entry lists paginate. Condition evaluation previews are bounded. Reliability policy exposes queue/run diagnostics without loading full histories by default.

## Degraded/provider states

Missing Fields, Ability/Policy, payment or Vault providers produce explicit unavailable/degraded state. The surface never fabricates a payment result, secret value, Ability authorization or workflow success. Recovery links to the canonical owner.

## UX lifecycle exit criteria

Certification requires complete 17-ID mapping, zero missing/unclassified machine semantics, reviewed raw-executable rejection and provider ownership, accessibility/portability/performance/degraded states and exact-head validator CI. Shared lifecycle promotion remains Supervisor-only.

## Non-certifications

Runtime implementation is not certified by this document. Product parity is not certified. Production deployment or release is not verified. No workflow run, payment, provider action, secret access, external dispatch or destructive mutation is authorized by this UX contract.
