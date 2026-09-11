# Admin Menu — UX Contract V1

Surface: **11 / Admin Menu**  
Issue: **#494**  
Lifecycle: planning-only; no runtime promotion.

## Essential

- **Target menu or submenu** — select an existing validated menu/submenu identity; show site/user/network-admin scope explicitly.
- **Safe custom menu item** — title, validated internal route/safe URL, position and icon metadata; no executable callbacks.

## Advanced

- **Presentation transform** — rename, icon, order and parent placement with reversible reset.
- **Visibility policy** — role/capability/user/network presentation targeting with an explicit warning that hiding does not grant/revoke authorization.
- **Menu profile assignment** — reusable bounded menu configurations assigned to actors/scopes.
- **Admin-bar transform** — typed node identity/parent/group/destination transformations only.

## Expert

- **Preview as role/user** — read-only effective menu preview with bounded/redacted actor data and no impersonation side effects.

## System / safety

- **Restore original** — recover canonical defaults or prior revision without deleting authorization state.

## Interaction contract

- Search/focus must reach every authored control.
- Controls expose loading, empty, validation, conflict, saved and recovery states.
- Keyboard navigation and visible focus are required; no information is color-only.
- Invalid/missing menu targets block save with actionable messages.
- Network-scoped controls are visually and semantically distinct from site/user scope.

## Security and ownership

- Server-side Policy/Ability checks remain authoritative.
- No raw PHP, arbitrary callbacks/classes, script/HTML execution or unsanitized external destinations.
- Roles/Capabilities owns authorization semantics; Admin Menu owns navigation/presentation only.
- Admin Theme owns global visual theming; Settings Pages owns settings-page definitions.

## Compatibility / multisite / performance

- Preserve WordPress site, user-admin and network-admin menu distinctions.
- Avoid global asset loading; UI assets load only on the Admin Menu surface.
- Menu transformations must be deterministic, bounded and cache-safe.

This UX contract does not authorize runtime implementation or lifecycle promotion.