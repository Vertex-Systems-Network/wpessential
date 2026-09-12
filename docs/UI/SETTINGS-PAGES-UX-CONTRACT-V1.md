# Settings Pages — UX Contract V1

Surface: **12 / Settings Pages**  
Machine source: `config/product/option-contracts/settings.json`  
Lifecycle: **UX certification candidate**. The machine contract is `OPTION_CONTRACT_COMPLETE`; lifecycle promotion remains Supervisor-only.

## Lifecycle preconditions

- The machine contract must be `OPTION_CONTRACT_COMPLETE` or later with `missing=0` and `unclassified=0`.
- All 17 current Atomic Option IDs are mapped exactly once below.
- Validation, capability checks, scope resolution and persistence are server-authoritative; client state is never trusted as storage or authorization truth.

## Canonical route and information architecture

Canonical admin location: **WPEssential → Data & Configuration → Settings Pages**.

The editor separates **Page**, **Layout**, **Storage**, **Lifecycle**, **Composition**, **History**, **Security**, **Value Registration**, and **Feedback**. Advanced authored controls are distinct from Expert owner/provider references and high-impact lifecycle actions.

## UX state classes

### Authored definition
Page identity/navigation, layout, storage policy, inheritance and portability are revisioned Settings-owned definitions.

### Effective/runtime state
Resolved scope, inherited values, registration state and autoload behavior are shown as effective/read-only context and must not masquerade as authored values.

### Diagnostic/provider state
Fields composition and secret references are canonical-owner references. Missing owners/providers show explicit unavailable/degraded state and block unsafe save.

### Deferred / prohibited state
Secrets are never authored, echoed or exported. Migration/reset actions are explicit high-impact workflows rather than background side effects.

## Atomic Option → UX map

- `settings.page.identity` — Page → title/key/identity editor with immutable-reference warnings after use.
- `settings.page.navigation` — Page → menu placement/order/navigation policy.
- `settings.page.capability` — Page → capability requirement; presentation never bypasses Policy authorization.
- `settings.page.scope` — Page → site/network/context scope selector derived and revalidated server-side.
- `settings.layout.tabs-sections` — Layout → tabs/sections composition and accessible ordering.
- `settings.layout.columns` — Layout → bounded column/layout policy with responsive fallback.
- `settings.storage.mode` — Storage → WordPress-native storage mode selector with owner caveats.
- `settings.storage.autoload-defaults` — Storage → autoload/default policy with effective-state preview.
- `settings.storage.owner` — Storage → canonical owner/storage identity display and validation.
- `settings.storage.inheritance` — Storage → Expert inheritance/override policy with explicit source/effective value distinction.
- `settings.lifecycle.reset` — Lifecycle → high-impact reset preview/confirmation; no silent destructive reset.
- `settings.lifecycle.migration` — Lifecycle → migration readiness/preview; execution remains separately gated.
- `settings.composition.fields` — Composition → Fields-owned schema/reference picker; Settings does not redefine field semantics.
- `settings.history.portability` — History → revision/export/import/conflict tooling with secret-free payloads.
- `settings.security.secret-ref` — Security → Vault/provider reference only; secret material is never displayed or persisted here.
- `settings.value.registration` — Value Registration → native registered-setting/schema status and validation context.
- `settings.feedback.errors` — Feedback → registered settings errors/notices with control-linked recovery guidance.

## Interaction and persistence

Editing follows draft → validate → save revision. Scope/storage changes trigger dependent validation. Inherited/effective values are visually distinct from authored overrides. Reset and migration require explicit preview, affected-scope disclosure and confirmation. Failed saves preserve input and focus the first invalid control.

## Loading, empty, validation, conflict and recovery

The UX defines loading, empty-page, missing-field-owner, missing-secret-provider, invalid-capability, invalid-scope, stale-revision conflict, validation-failed, saved, reset-preview, migration-blocked and recovery states. Concurrent edits never silently overwrite newer revisions.

## Security and ownership

Fields owns reusable field schema/validation semantics. Vault/secret providers own credentials and secret material. Roles/Policy owns authorization. Settings owns page/layout/storage-definition composition only. Secret material is never displayed or persisted here. Reset and migration are privileged high-impact actions and cannot execute from mere UI visibility.

## Accessibility

Every control has a programmatic label, description association, keyboard access and visible focus. Tabs expose correct ARIA semantics and a non-tab fallback. Error summaries link to invalid controls; notices use live regions without stealing focus. Destructive confirmations expose cancel and return focus predictably.

## Multisite and scope

Site, network and inherited scope are distinct. Network scope is derived from trusted server context. A site-scoped import cannot silently become network-scoped. Effective inherited values always show their source scope.

## Portability and reference remapping

Exports are definition-only, versioned and secret-free. Imports validate Fields/Vault references, scope, storage mode and conflicts before commit. Unresolved references remain blocked with explicit remapping UI rather than fallback guesses.

## Performance and scale

Settings assets load only on relevant WPEssential screens. Large field compositions use lazy/bounded rendering. Effective-value previews avoid repeated global option scans and autoload changes surface performance impact before save.

## Degraded/provider states

Missing Fields, Vault, capability or storage providers produce explicit degraded/unavailable states. The UX never fabricates a secret, capability, effective value or migration result. Recovery directs the operator to the canonical owner.

## UX lifecycle exit criteria

Certification requires complete Atomic Option mapping, zero missing/unclassified machine semantics, reviewed state/ownership/accessibility/portability behavior, and exact-head CI for the surface-local validator. Shared registration and lifecycle promotion remain Supervisor-only.

## Non-certifications

Runtime implementation is not certified by this document. Product parity is not certified. Production deployment or release is not verified. No reset, migration, secret/provider or destructive operation is authorized by this UX contract.
