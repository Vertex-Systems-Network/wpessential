# Builder Widgets — UX Contract V1

Surface: **16 / Builder Widgets**  
Issue: **#499**  
Lifecycle: planning-only; no runtime promotion.

## Essential

- **Blueprint identity** — stable component key, label and definition version with collision feedback.
- **Control schema** — compose registered reusable controls with explicit defaults, validation and responsive capability.

## Advanced

- **Render mode** — select only registered server/client render providers; show provider availability/effective mode.
- **Dynamic bindings** — typed references to Query, Listings, Relations and Abilities with context and missing-reference diagnostics.
- **Style bindings** — validated theme/token/responsive values; no arbitrary executable CSS/JS.

## Expert

- **Adapter registry** — registered builder adapters with compatibility/health status and supported capability set.
- **Parity diagnostics** — read-only comparison of blueprint capabilities versus active adapter capabilities.
- **Portability** — versioned blueprint import/export with adapter/reference remapping preview.

## Interaction contract

- Search/focus reaches every setting; controls expose provider-missing, invalid, saved and reset states.
- Dynamic references show their canonical owner and effective resolution.
- Adapter parity gaps are actionable diagnostics, not silent feature loss.
- Keyboard operation, semantic labels/groups, visible focus and announced async states are required.

## Security / multisite / compatibility / performance

- Registered provider IDs only; no raw callbacks/classes/PHP/JS/template execution.
- Dynamic values pass server-authoritative validation and the target owner's Policy/Ability checks.
- Site/network blueprint scope is explicit; provider availability is environment-specific and never assumed portable.
- Missing/deactivated adapters degrade safely and preserve definitions.
- Render/binding evaluation must be bounded and avoid repeated N+1 resolution; optional assets load only where used.

This contract does not authorize runtime adapter registration, runtime implementation, deployment or certification.