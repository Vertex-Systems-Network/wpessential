# Settings Pages — UX Contract V1

Surface: **12 / Settings Pages**  
Issue: **#495**  
Lifecycle: planning-only; no runtime promotion.

## Essential

- **Page identity and navigation** — stable settings-page identity, menu placement and capability context.
- **Scope** — explicit site, network or user scope; never inferred silently.
- **Structure** — tabs, sections and panels as presentation composition over canonical settings definitions.

## Advanced

- **Storage mode** — typed site option, network option or user-meta ownership.
- **Values and inheritance** — show authored, inherited/default and effective values separately.
- **Control Registry composition** — reuse canonical Fields/Control Registry definitions rather than a private field engine.

## Expert

- **Autoload policy** — expert-only performance control with warnings, defaults and read-only diagnostics for effective behavior.

## System / safety

- **Secret routing** — secret values are Vault-backed references only; ordinary settings exports never contain secret material.

## Interaction contract

- Every control is searchable/focusable and exposes validation, loading, saved, error and reset states.
- Network/user/site scope is visible in labels and confirmation text.
- Inherited/effective values are visually distinct from authored overrides.
- Keyboard operation, semantic grouping, visible focus and screen-reader status announcements are required.

## Security / multisite / compatibility / performance

- Server-side Policy/capability checks and sanitization are authoritative.
- Site/network/user persistence owners remain isolated; network overrides require explicit network authorization.
- Missing field/control providers fail safely without data loss.
- Avoid autoloading large settings payloads by default; performance-sensitive controls remain Expert-tier.
- Assets load only on the Settings Pages surface.

This contract does not authorize runtime implementation, deployment or certification.