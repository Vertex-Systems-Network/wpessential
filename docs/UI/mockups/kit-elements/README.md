# WPEssential — Kit Elements Desktop UI (visual specification)

**Status:** UI-only concept assets. No PHP, TypeScript, JavaScript runtime, data structures, CI, or database files changed. These five SVG artboards are editable vector/text design mockups, not registered production elements. Designed 2026-10-25.

## Source of truth
- [Elements React view](../../../../admin-ui/src/app/screens/Elements.tsx) and [Elements registry](../../../../frameworks/Platform/Admin/Elements/ElementRegistry.php): only `gutenberg` and `elementor` types; per-item enable/disable; edition badge; module-contributed registry is allowed to be empty.
- [Elements screen](../../../../frameworks/Platform/Admin/Elements/ElementsScreen.php): Elementor inactive notice; empty state; docs route; current data and switch behavior.
- [Builder Widgets UX contract](../../BUILDER-WIDGETS-UX-CONTRACT-V1.md): canonical owner Surface 16; 18 atomic option IDs; component/controls/rendering/style/adapters split; no arbitrary code callbacks.
- [Design tokens](../../../../admin-ui/src/ui/_tokens.scss): ink navy `#0B1A3D`, orange `#EC7D1A`, app background `#F3F6FC`, muted `#5D6780`, primary line `#DDE4F0`; Bricolage Grotesque headings / Plus Jakarta Sans body in source (SVG uses Plus Jakarta Sans with fallback).

## Artboards
1. [01 — Gutenberg registry, populated concept](01-elements-gutenberg.svg): search/filter, sample registrations, edition badges, on/off, counts. **All item names/sample counts are fictional UI fixtures, never claim these widgets currently ship.** The current registry can be empty.
2. [02 — Elementor unavailable + empty](02-elements-elementor-empty.svg): no widgets, actionable docs, install/activate warning; actual empty/degraded conditions.
3. [03 — Builder Widgets Component Editor](03-builder-widget-editor.svg): revisioned identity, category, lifecycle, adapter refs, editing levels, validation/preview.
4. [04 — Adapters & Portability](04-adapters-portability.svg): availability, reference health, portability, degraded/recovery.
5. [05 — Kit Elements patterns](05-kit-elements-patterns.svg): buttons, badge states, fields, level tabs, toggles, confirmation pattern.

## Wireframe → option contract mapping
- **Components:** `builder-widgets.component.identity`, `builder-widgets.component.adapters`.
- **Controls:** `builder-widgets.controls.registry`, `builder-widgets.controls.content-style`, `builder-widgets.controls.responsive`.
- **Rendering:** `builder-widgets.render.server`, `builder-widgets.render.client-provider`, `builder-widgets.render.dynamic`, `builder-widgets.render.ability`, `builder-widgets.render.states-cache`.
- **Style:** `builder-widgets.style.tokens`, `builder-widgets.style.box-state`.
- **Adapters:** `builder-widgets.adapter.gutenberg-shortcode`, `builder-widgets.adapter.elementor-bricks`, `builder-widgets.adapter.legacy`, `builder-widgets.adapter.parity-portability`, `builder-widgets.adapter.shortcode`, `builder-widgets.adapter.wp-widget`.

## Acceptance and limitations
- Desktop scope only, 1600×1000 artboards; editable SVG groups and text. No mobile or functional implementation was requested.
- The existing Figma file `deYEjOsNf0KdYSRrImXnIp` was inaccessible for editing. A new Figma file `eMmo511ETX5SYIVyFsiJm6` was created, but the Figma Starter MCP call limit prevented adding frames. These SVGs can be imported into that file when edit-tool access is available.
- All mock screens are visual proposals, not implementations, tests, backend behavior claims, or release certification. Visual review on real Figma canvas remains pending.
