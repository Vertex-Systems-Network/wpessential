# WPEssential — Capability Gap / New Option / New System Request System

Status: **Phase 0 planning / no remote submission or development authorization**  
Date: 2026-08-29

## 1. Purpose

When a user describes a requirement that WPEssential cannot fully represent, the AI Prompt/Requirement Compiler must not silently omit it, invent unsupported capability or pretend a partial solution is complete.

The system must:
1. detect the gap;
2. explain exactly what is missing;
3. show any safe existing workaround/composition;
4. allow the user to create a structured local request;
5. optionally submit that request to WPEssential services only after explicit preview/consent;
6. preserve the request as product/solution planning input.

## 2. Gap taxonomy

- `OPTION_GAP` — existing module lacks one control/behavior.
- `FIELD_TYPE_GAP` — missing typed field/data capability.
- `QUERY_OPERATOR_GAP` — missing query/filter/aggregate capability.
- `CONDITION_GAP` — missing shared condition/operator/context.
- `ACTION_GAP` — missing Workflow/Ability action.
- `RENDERER_GAP` — missing component/listing/document renderer.
- `DATA_SOURCE_GAP` — source not modeled in Data Source Registry.
- `ADAPTER_GAP` — external/domain provider requires adapter.
- `PROVIDER_CERTIFICATION_GAP` — adapter exists but requested capability/version is uncertified.
- `FOUNDATION_GAP` — reusable primitive absent.
- `MODULE_GAP` — coherent user-facing module absent.
- `SOLUTION_PATTERN_GAP` — no existing Blueprint/pattern covers the workflow.
- `SYSTEM_BLUEPRINT_GAP` — primitives exist but no curated complete Solution.
- `COMPATIBILITY_GAP` — WordPress/PHP/plugin/builder/provider/version unsupported/unknown.
- `SECURITY_POLICY_BLOCK` — requested behavior is intentionally disallowed.
- `EXTERNAL_AUTHORITY_REQUIRED` — WPE cannot authoritatively provide the fact/operation itself.
- `UNKNOWN_RESEARCH_REQUIRED` — not enough evidence to classify safely.

## 3. User experience

When gaps are found, result screen shows:
- Requirement;
- Coverage status;
- Existing WPE module/foundation attempted;
- Missing capability;
- Why it cannot be produced now;
- Risk/authority note;
- Possible workaround;
- Workaround limitations;
- **Continue with supported subset**;
- **Modify requirement**;
- **Use recommended workaround**;
- **Request New Option/System**.

The supported subset cannot be labeled complete unless user explicitly accepts the omitted requirements.

## 4. Local Capability Request object

Fields:
- request UUID;
- type;
- title;
- user description;
- original Prompt Session ID;
- normalized requirement;
- target module/foundation/adapter/system;
- domain/pattern;
- current capability resolver evidence;
- desired behavior;
- expected inputs/outputs;
- actors/permissions;
- example scenario;
- workaround and limitation;
- business impact;
- frequency/scale estimate;
- security/privacy considerations;
- external authority/provider;
- compatibility/version info;
- related requests;
- attachments references under file policy;
- local status;
- remote submission status;
- submitter/actor;
- created/updated;
- remote reference if submitted.

No secret values are stored in the request.

## 5. Request types exposed to user

Friendly labels:
- Request an option in this module;
- Request a new field/control;
- Request a Workflow action/trigger;
- Request an integration/adapter;
- Request provider/version support;
- Request a reusable foundation;
- Request a new WPE module;
- Request a complete Solution Blueprint;
- Request compatibility support;
- Report a product gap/bug;
- Request documentation/example.

AI can recommend the request type, but user can change it before submission.

## 6. Request wizard

1. Review unsupported requirement.
2. Choose request type.
3. Review AI-normalized specification.
4. Add business context/example.
5. Choose whether current local configuration metadata may be included.
6. Preview exact payload.
7. Remove/redact fields.
8. Submit locally only or send to WPE service.
9. If remote: require account/service connection and explicit consent.
10. Show local + remote request IDs/status.

## 7. Remote submission boundary

Remote submission is optional.

If no WPE account/service is connected:
- save local request;
- export request JSON/Markdown;
- copy request text;
- show connection option, but do not block local WPE usage.

If connected:
- show endpoint/service identity;
- show data classes being sent;
- require explicit Submit action;
- use authenticated Connection/Account contract;
- retry/idempotency;
- preserve unknown remote outcome until reconciled;
- do not attach diagnostics automatically.

Diagnostics, database samples, logs and screenshots require their own preview/consent.

## 8. AI-assisted request drafting

AI may:
- deduplicate wording;
- convert prose into structured expected behavior;
- identify likely owning module;
- propose generalization into a reusable foundation;
- find related local requests;
- suggest acceptance criteria;
- summarize workaround limitations;
- remove irrelevant sensitive values.

AI may not:
- mark request accepted/planned/released;
- promise delivery date;
- submit remotely without user action;
- disclose private site data to strengthen a request without permission.

## 9. Product-team status model

Local/remote statuses may map to:
- Draft;
- Submitted;
- Needs Information;
- Acknowledged;
- Duplicate;
- Under Research;
- Candidate;
- Planned;
- Not Planned;
- Blocked;
- In Development;
- Available for Test;
- Released;
- Closed.

Remote status is external fact. Local UI must distinguish cached last-known remote state from live confirmed state.

## 10. Request-to-solution traceability

If capability later ships:
- request links to module/foundation/adapter version;
- capability resolver can re-evaluate affected Prompt Sessions/Blueprints;
- user may receive notification if opted in;
- previously partial Blueprint can offer a new compatibility/upgrade analysis;
- no automatic production mutation merely because a request was released.

## 11. Developer/SDK alternative

When requirement is safely extensible through SDK before first-party support:
- identify required extension contract;
- show developer-facing schema/Ability/adapter interface;
- allow third-party/local extension registration;
- certification/health state remains visible;
- unsupported custom code is not silently treated as first-party certified.

## 12. Security-blocked requests

Some asks should be explicitly classified as unsafe rather than "missing". Examples:
- arbitrary PHP/SQL execution through AI;
- bypassing Capability/Policy;
- exposing password/session/Vault secrets;
- fake reviews/orders/scarcity;
- public destructive REST/MCP action with no auth;
- direct protected-file origin exposure;
- silent cross-site access escalation.

UI can allow a product-feedback request, but must state the security invariant that prevents implementation in the requested form and suggest a safe alternative.

## 13. Analytics/privacy

Possible local metrics:
- gap count by module/category;
- most requested capabilities;
- workaround acceptance;
- request conversion;
- repeated Solution gaps.

No remote telemetry by default merely because a gap exists.

## 14. Multisite

Request scope records:
- source site/network;
- whether requirement is site-specific or network-wide;
- affected site count estimate;
- no cross-site data attached without Network authorization.

Network request aggregation can group repeated site requests without leaking site-private details.

## 15. Abilities

Suggested typed Abilities:
- `wpe-ai/create-capability-gap-draft`;
- `wpe-ai/update-capability-gap-draft`;
- `wpe-ai/read-capability-gap`;
- `wpe-ai/search-local-capability-gaps`;
- `wpe-ai/preview-capability-request-payload`;
- `wpe-ai/submit-capability-request`;
- `wpe-ai/reconcile-capability-request-status`.

Submit is always permissioned, consented and auditable.

## 16. Events

- `capability_gap_detected`;
- `capability_request_drafted`;
- `capability_request_submitted`;
- `capability_request_submission_unknown`;
- `capability_request_status_changed`;
- `capability_available`;
- `blueprint_gap_resolved`.

Events never imply product-team acceptance without authoritative remote state.

## 17. MUST NOT

- hide unsupported requirements;
- claim partial solution is complete;
- auto-open remote requests without consent;
- send full prompts/diagnostics/site DB to WPE by default;
- let remote request status become local authorization;
- auto-install new module/code when a request becomes available;
- use request volume as sole security/architecture justification.

## 18. Development gate

This is planning only. No remote feature-request API call, support ticket creation, telemetry transmission or product mutation is authorized.