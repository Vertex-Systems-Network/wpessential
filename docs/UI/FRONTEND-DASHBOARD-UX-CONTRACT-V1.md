# Frontend Dashboard — UX Contract V1

Surface: **13 / Frontend Dashboard**  
Machine source: `config/product/option-contracts/dashboard.json`  
Lifecycle: **UX certification candidate**. Machine truth is `OPTION_CONTRACT_COMPLETE`; this artifact does not authorize runtime implementation.

## Lifecycle preconditions

- Machine status must remain `OPTION_CONTRACT_COMPLETE` or later with `missing=0` and `unclassified=0`.
- All 17 current Atomic Option IDs are mapped exactly once below.
- Access, content mutation and provider resolution remain server-authoritative and canonical-owner controlled.

## Canonical route and information architecture

Canonical admin location: **WPEssential → Experience → Frontend Dashboard**.

The configuration IA separates **Identity & Route**, **Access**, **Navigation**, **Endpoints**, **Content Ownership**, **Presentation**, **Portability/Diagnostics**, and **Rendering**. The frontend product shell exposes navigation and read models while peer surfaces retain mutation truth.

## UX state classes

### Authored definition
Dashboard identity, route, unauthorized behavior, navigation structure/visibility/mobile behavior and presentation policy are revisioned Dashboard-owned definitions.

### Effective/runtime state
Resolved login/session/access decisions, endpoint availability, route rewrite state and rendered template state are shown as effective read-only context.

### Diagnostic/provider state
Policy, Profile, content and service endpoints are canonical-owner/provider references. Missing references become explicit degraded states, never locally reimplemented behavior.

### Deferred / prohibited state
The Dashboard shell cannot grant capabilities or bypass owner validation. Unsupported endpoint/provider state is blocked rather than inferred.

## Atomic Option → UX map

- `dashboard.identity.definition` — Identity & Route → dashboard definition/name/lifecycle editor.
- `dashboard.identity.route` — Identity & Route → canonical route/slug configuration with collision validation.
- `dashboard.access.login` — Access → WordPress login/session requirement and redirect context.
- `dashboard.access.policy` — Access → Expert Roles/Policy reference; authorization remains externally owned.
- `dashboard.access.unauthorized` — Access → explicit unauthenticated/unauthorized presentation behavior.
- `dashboard.navigation.structure` — Navigation → ordered dashboard navigation definition.
- `dashboard.navigation.visibility` — Navigation → presentation visibility conditions; never authorization.
- `dashboard.navigation.mobile` — Navigation → Expert mobile/off-canvas/responsive navigation policy.
- `dashboard.endpoint.account-profile` — Endpoints → Profiles-owned account/profile reference.
- `dashboard.endpoint.content` — Endpoints → canonical content-owner endpoint reference.
- `dashboard.endpoint.services` — Endpoints → registered service/provider endpoint reference.
- `dashboard.content.crud-owner` — Content Ownership → Expert owner handoff; Dashboard never becomes CRUD authority.
- `dashboard.presentation.states` — Presentation → loading/empty/error/success/disabled state definitions.
- `dashboard.presentation.accessibility` — Presentation → accessibility/focus/announcement requirements.
- `dashboard.portability.diagnostics` — Portability/Diagnostics → read-only dependency/reference diagnostics and export readiness.
- `dashboard.route.rewrite-lifecycle` — Identity & Route → native rewrite lifecycle state with explicit refresh/degraded guidance.
- `dashboard.render.template-handoff` — Rendering → registered template handoff contract; no arbitrary executable template input.

## Interaction and persistence

Configuration uses draft → validate → save revision. Route/navigation edits are validated for collision and unresolved endpoint references before commit. Endpoint cards show owner, availability and scope. Frontend shell actions hand off mutations to canonical owners and surface returned validation errors without rewriting them.

## Loading, empty, validation, conflict and recovery

Required states include configuration loading, first-run empty dashboard, missing login context, unauthorized, missing endpoint owner, route conflict, stale revision, provider unavailable, frontend loading, frontend empty data, partial endpoint failure, saved and recovery. Partial failures isolate the failing module instead of collapsing the whole shell.

## Security and ownership

Roles/Policy owns authorization. Profiles owns account/profile mutation. Content/service owners own their mutations. Dashboard owns composition, navigation and presentation only. Visibility is presentation only and never authorization. Client-side hidden navigation cannot substitute for capability checks.

## Accessibility

Navigation has semantic landmarks, current-item state, keyboard operation and visible focus. Mobile navigation traps no focus and restores it on close. Loading/error/status states are announced appropriately. Endpoint errors identify the affected section without forcing context loss.

## Multisite and scope

Route, access and endpoint references display site/network scope explicitly. Network context is server-derived. Imports cannot silently remap a site dashboard to network/global scope or resolve references across sites without explicit mapping.

## Portability and reference remapping

Exports are definition-only and secret-free. Imports validate route collisions plus Profile/content/service references and report unresolved dependencies before save. Diagnostics remain read-only evidence and are not imported as authored runtime state.

## Performance and scale

The frontend shell should lazy-load expensive modules, avoid N+1 endpoint calls and isolate slow/degraded providers. Admin configuration assets are scoped. Mobile/navigation state must remain responsive with large menu definitions.

## Degraded/provider states

Missing Profiles, Policy, content/service providers or rewrite readiness produce explicit unavailable/degraded cards with owner and remediation. The Dashboard never fabricates content, access decisions or successful mutations when a provider is unavailable.

## UX lifecycle exit criteria

Certification requires complete 17-ID mapping, zero missing/unclassified machine semantics, fail-closed owner/access behavior, reviewed accessibility/portability/performance states and exact-head validator CI. Shared lifecycle promotion remains Supervisor-only.

## Non-certifications

Runtime implementation is not certified by this document. Product parity is not certified. Production deployment or release is not verified. No content/user/provider mutation is authorized by this UX contract.
