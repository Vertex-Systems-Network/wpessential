# Forms & Workflows — Native WordPress Audit V1

Surface: **17 / Forms & Workflows**  
Issue: **#693**  
Parent: **#686**  
Exact-main audit anchor: `3839142cc8a225f443fcf9f6e0c26eb9641742e7`  
Seed: `config/product/options-bank/forms-workflows.json` — **16 records / BANK_SURFACE_SEEDED**

## Decision

**NATIVE AUDIT EVIDENCE COMPLETE; WORDPRESS PROVIDES REQUEST/SECURITY INVOCATION PRIMITIVES, NOT A GENERIC FORM/WORKFLOW ENGINE.** One invocation-channel family should be made explicit during Supervisor integration. No workflow execution is authorized.

## Current native sources

- `wp_verify_nonce()` — https://developer.wordpress.org/reference/functions/wp_verify_nonce/
- `check_admin_referer()` — https://developer.wordpress.org/reference/functions/check_admin_referer/
- `admin_post_{$action}` / `admin_post_nopriv_{$action}` — https://developer.wordpress.org/reference/hooks/admin_post_action/
- AJAX actions — https://developer.wordpress.org/plugins/javascript/ajax/
- REST custom endpoints + required `permission_callback` — https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
- Roles & Capabilities — https://developer.wordpress.org/apis/security/user-roles-and-capabilities/
- accepted planning evidence: `docs/PRODUCT/FORMS-WORKFLOWS-NATIVE-MARKET-EVIDENCE-MATRIX-V1.md`

Nonces are request-intent/CSRF controls and are explicitly **not** authentication or authorization. Every submission/action target still requires canonical capability/Policy/Ability checks.

## Seed-record disposition

| Seed record | Native disposition | Integration action |
|---|---|---|
| `forms-workflows.form.identity` | **WPE/MARKET FORM DEFINITION** | no native generic form-definition claim |
| `forms-workflows.form.layout` | **WPE/MARKET PRESENTATION** | no native multi-step/section engine claim |
| `forms-workflows.controls.registry` | **SHARED FIELDS OWNER** | use native HTML/request primitives only through typed Fields/control contracts |
| `forms-workflows.controls.validation` | **NATIVE SECURITY/SANITIZATION SUBSTRATE + WPE VALIDATION** | server validation authoritative; no generic native validation graph claim |
| `forms-workflows.submission.policy` | **NATIVE-CONFIRMED SECURITY SUBSTRATE** | add nonce/session/capability/permission-callback provenance; nonce never equals authorization |
| `forms-workflows.submission.confirmation` | **WPE/MARKET UX** | no native generic confirmation engine |
| `forms-workflows.entry.storage-retention` | **WPE STORAGE/PRIVACY** | WordPress has storage APIs but no canonical form-entry model |
| `forms-workflows.entry.management` | **WPE/MARKET** | no native notes/assignment/export/anonymization form-entry engine |
| `forms-workflows.workflow.owner-order` | **WPE CANONICAL-OWNER CONTRACT** | actions must reference registered Abilities/providers; native hooks are not user-authored executable config |
| `forms-workflows.workflow.conditions-mapping` | **WPE/SHARED CONDITION OWNER** | no native workflow condition/data-mapping engine |
| `forms-workflows.workflow.failure` | **WPE/PROVIDER RELIABILITY** | no native retry/compensation workflow engine |
| `forms-workflows.run.state` | **WPE WORKFLOW RUNTIME** | no native checkpoint/resume/cancel state machine |
| `forms-workflows.run.replay` | **WPE RELIABILITY** | no native workflow correlation/replay engine |
| `forms-workflows.integration.payment` | **PROVIDER/CROSS-OWNER** | no native payment claim |
| `forms-workflows.integration.ability-secret` | **WPE ABILITY/VAULT REFERENCES** | no executable/secret material in Bank portability |
| `forms-workflows.safety.raw-executable` | **REJECTED-UNSAFE CONFIRMED** | keep `REJECT`; arbitrary PHP/callback/workflow code remains forbidden |

## Missing native-facing family to add during integration

### `forms-workflows.submission.invocation-channel`

Normalize **registered** submission invocation modes without creating a private transport engine:

- admin-post authenticated/public handler identity;
- typed AJAX operation identity;
- REST route/method/schema + permission policy reference;
- nonce/session requirements where applicable;
- canonical target Ability/action owner;
- explicit public-vs-authenticated scope.

Recommended classification: `COMPATIBILITY` or `SOFT_NATIVE`, `HYBRID`, `CURRENT_NATIVE`. Authored raw hook names/callbacks remain rejected.

## Required Supervisor Bank integration

1. Populate native nonce/admin-post/AJAX/REST/capability sources.
2. Add `submission.invocation-channel`.
3. Enrich `submission.policy` with nonce-is-not-authorization and permission-callback semantics.
4. Preserve all workflow/run/retry/conditions/action-order families as WPE/shared-owner semantics.
5. Keep `safety.raw-executable` rejected.

## Native completeness / unresolved items

After the invocation-channel addition, current WordPress request/security primitives are fully represented for V1. WordPress does not supply a generic workflow executor, entry-management product, multi-step forms engine or payment workflow. Anti-spam breadth and market/provider behavior remain market review concerns.

## Gate boundary

Worker conclusion: **native evidence complete; one invocation family + metadata integration pending**.  
Not promoted here: `NATIVE_AUDITED`, market review, `BANK_REVIEWED`, workflow execution, Atomic Option Contract, UX or product parity.