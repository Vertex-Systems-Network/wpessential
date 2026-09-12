# Admin Menu — UX Contract V1

Surface: **11 / Admin Menu**  
Machine source: `config/product/option-contracts/admin-menu.json`  
Lifecycle: **UX certification candidate**. The machine contract is `OPTION_CONTRACT_COMPLETE`; this document does not itself promote lifecycle state or authorize runtime execution.

## Lifecycle preconditions

- The current machine contract must remain `OPTION_CONTRACT_COMPLETE` or later with `missing=0` and `unclassified=0`.
- All 16 current Atomic Option IDs are mapped exactly once below. A later Atomic Option addition requires this UX contract to be re-reviewed before certification.
- Save, preview, reset and import requests are server-authoritative. Client state never becomes authorization truth.

## Canonical route and information architecture

Canonical admin location: **WPEssential → Experience → Admin Menu**.

The surface is organized as **Menu Rules**, **Custom Items**, **Profiles**, **Admin Bar**, and **Diagnostics & Recovery**. Advanced authored controls are distinct from Expert provider/policy references and System prohibited/read-only states. Site, user-admin and network-admin scope must always be visible before edit or preview.

## UX state classes

### Authored definition
Revisioned Admin Menu-owned transforms, custom items, profile definitions and recovery policy are editable only when the actor is authorized.

### Effective/runtime state
Resolved menu placement and visibility may be previewed, but effective state is never presented as an editable authorization setting.

### Diagnostic/provider state
Role/capability assignment and profile assignment are references to canonical owner surfaces. Missing owners/providers show explicit unavailable or degraded state and block invalid save.

### Deferred / prohibited state
Unsafe destinations and executable callbacks are not normalized into controls. Prohibited state is shown read-only with remediation guidance.

## Atomic Option → UX map

- `admin-menu.transform.target` — Menu Rules → target selector with explicit site/user/network scope.
- `admin-menu.transform.rename` — Menu Rules → presentation label transform.
- `admin-menu.transform.order` — Menu Rules → deterministic order control with keyboard-accessible alternative to drag/drop.
- `admin-menu.transform.parent` — Menu Rules → validated parent placement selector.
- `admin-menu.transform.visibility` — Menu Rules → presentation visibility policy; hiding never grants or revokes authorization.
- `admin-menu.transform.role-capability` — Menu Rules → Expert Roles/Policy reference, read from the canonical authorization owner.
- `admin-menu.transform.network` — Menu Rules → multisite/network scope policy with explicit network-admin warning.
- `admin-menu.custom.route` — Custom Items → registered internal route selector.
- `admin-menu.custom.safe-url` — Custom Items → validated safe destination field; unsafe schemes are blocked server-side.
- `admin-menu.custom.group` — Custom Items → bounded grouping/placement control.
- `admin-menu.profile.definition` — Profiles → revisioned reusable profile editor.
- `admin-menu.profile.assignment` — Profiles → Expert assignment reference; canonical actor/role ownership remains external.
- `admin-menu.profile.preview` — Profiles → read-only preview as bounded role/user context with redacted identity and no impersonation side effect.
- `admin-menu.admin-bar.transform` — Admin Bar → typed node/parent/group/destination transform.
- `admin-menu.safety.recovery` — Diagnostics & Recovery → restore original/prior revision flow with confirmation and conflict handling.
- `admin-menu.safety.unsafe-url` — System safety state → prohibited unsafe URL/executable destination evidence; never an authored control.

## Interaction and persistence

Edits use draft → validate → save revision semantics. Dirty state survives validation errors. Target changes revalidate parent, destination and scope references. Reset/restore always displays the affected revision and scope before confirmation. Search and focus navigation must reach every authored control.

## Loading, empty, validation, conflict and recovery

The UX defines loading, empty-menu, missing-target, invalid-parent, invalid-destination, provider-unavailable, stale-revision conflict, saved, restore-success and restore-failure states. Validation errors remain adjacent to the responsible control and preserve user input. Concurrent edits never silently overwrite a newer revision.

## Security and ownership

Roles/Capabilities and shared Policy remain authoritative for authorization; Admin Menu owns navigation presentation only. Visibility is presentation behavior, never authorization. Raw PHP, arbitrary callbacks/classes, script/HTML execution and unsafe URL schemes are prohibited. Preview must not impersonate, mutate roles, or execute target actions. Server-side capability checks remain authoritative for every mutation.

## Accessibility

All controls require programmatic labels, visible keyboard focus, logical tab order, screen-reader error association and non-color-only status communication. Reordering must have keyboard controls. Confirmation dialogs return focus predictably and expose cancel as a first-class action.

## Multisite and scope

Site, user-admin and network-admin menu contexts are distinct. Network scope is derived from trusted server context and cannot be escalated through client payload. Imports must not silently remap site-only definitions to network scope.

## Portability and reference remapping

Exports are definition-only and secret-free. Imports validate route, role/capability and parent references, report unresolved references before save, and preserve revision history/conflict policy. Prohibited unsafe destinations are never exported as executable behavior.

## Performance and scale

The editor must avoid global admin asset loading and N+1 role/menu lookups. Large menu trees use bounded search/filtering and deterministic ordering. Preview and diagnostics are cache-safe and must not change authoritative runtime state.

## Degraded/provider states

If Roles/Policy, route registry or a referenced menu target is unavailable, the UX shows the missing dependency, disables unsafe mutation, and offers recovery/remapping. It never fabricates authorization or treats cached visibility as permission truth.

## UX lifecycle exit criteria

Certification requires complete Atomic Option coverage, zero missing/unclassified machine semantics, fail-closed validation, reviewed accessibility/security/ownership behavior, and exact-head CI for the surface-local validator. Shared lifecycle promotion remains Supervisor-only.

## Non-certifications

Runtime implementation is not certified by this document. Product parity is not certified. Production deployment or release is not verified. No role/user/provider mutation or external action is authorized by this UX contract.
