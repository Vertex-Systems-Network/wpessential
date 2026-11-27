# Block Editor Parity & Site Tools Kit — V1 plan

Status: **APPROVED / ACTIVE** (`GOV-OWNER-CONSENT-002`, owner request 2026-11-26)
Decision: ADR-0239
Repos: Free `wpessential`, Pro `wpessential-pro`, theme `hi-wpessential`
Work ID root: `P5-M14`

## Rules every AI agent must follow

1. **One source of truth.** Global defaults (colours, typography, spacing, units, breakpoints, buttons, forms…) live in Site Tools Kit options. Blocks, Elementor widgets and the Customizer read them. A block stores **only values the user changed**; empty = inherit kit (Elementor behaviour). Never hard-code a default inside a block.
2. **Editor = frontend.** Every block must render in the editor with the same CSS and markup as the frontend (`block.json` `editorStyle` = `style`, ServerSideRender or the shared view; `add_editor_style()` for theme CSS, kit CSS variables printed into the editor iframe).
3. **Shared controls only.** Inspector panels use the shared control library (`admin-ui` / block controls): Responsive wrapper, Unit picker, Colour/Typography global picker, custom Dropdown. No native `<select>` (`SelectControl`) in new UI; use the house `Dropdown` (keyboard, ARIA listbox, search).
4. **Three tabs.** Every WPEssential block inspector = `Content | Style | Advanced` tabs (shared `InspectorTabs` component). Block-specific content panel title is "Content", not the block name.
5. **Responsive per field.** A responsive field shows one device dropdown button (icon + tooltip "Desktop · 1920px"). Only **enabled** breakpoints appear. Never render a separate chunk of fields per breakpoint.
6. **Accessibility & W3C.** Valid HTML (no duplicate ids, landmark order, alt, labels), WCAG 2.2 AA contrast, focus visible, reduced motion. CI must run an HTML/a11y check (axe) on demo pages.
7. **Customizer ↔ Site Tools Kit** use the same option keys; changing one shows in the other. Never add a Customizer setting with its own option key.
8. Theme classes keep the `wpe-` prefix; plugin code keeps Free/Pro boundaries (Free must not call Pro).
9. Each WP below ships in its own PR with tests; update CHECKPOINT.md + this file's status column.

## Current execution handoff (2026-11-27)

WP5 #1596 and WP6 #1598 merged. WP6 44/44 E2E, eight workflows passed; WP7 #1599 / #1600 active with conditions, priority, protected snippets and scoped HTML/JS locations. SCSS-lite, manager, Pro-only PHP boundary and runtime acceptance pending. WP9/WP12 retain full editor/React/theme parity. No release or full-product certification.

## Work packages

| WP | Owner request | Scope | Repo | Status |
|---|---|---|---|---|
| WP1 | 7 | Move Site Kit from Pro to **Free**, rename **Site Tools Kit**; Pro keeps premium extras (code inserter extras, Elementor sync) via filter | Free, Pro | MIGRATION MERGED (premium extension boundary pending) |
| WP2 | 15 | House `Dropdown` component (React + vanilla for admin screens); replace `<select>` in Site Tools Kit, field UI, block inspector | Free | PARTIAL (PHP/vanilla screens; React screens Demos/Modules still native — follow-up) |
| WP3 | 10 | Breakpoints list in Layout (320…1920, enable/disable, edit px); `ResponsiveControl` device dropdown with icons/tooltips; used by kit and blocks | Free | KIT DONE for Site Tools Kit; block inspector reuse lands with WP9 |
| WP4 | 4 | Units setting: multi-select allowed units (px, %, em, rem, vh, vw, vmin, vmax, ch, fr) + custom units with `1 X = n <base>`; `UnitControl` shows only enabled units; compiler converts custom → `calc(n * base)` | Free | KIT DONE for Site Tools Kit (block UnitControl reuse with WP9) |
| WP5 | 9 | Global colours / dark colours / typography: repeater "Add custom" with name + slug; exposed as CSS vars + `theme.json` palette/fontSizes + global picker in blocks/Elementor | Free | MERGED — #1596; 42/42 Free, 49/49 paired |
| WP6 | 13 | Expand groups: images, forms (labels, inputs, focus, placeholder, errors), buttons (states, sizes, variants), background, links (states, underline), shadows (named presets) — every sub-element + full CSS props | Free | MERGED — #1598; 44/44 E2E |
| WP7 | 12 | Custom code: repeater of snippets — language (CSS, SCSS-lite, JS, HTML, PHP-off-by-default), location (head, body open, footer, before/after content, admin, login), conditions (all/front/page ids), priority, enable | Free (PHP exec Pro) | ACTIVE — #1599 / #1600 |
| WP8 | 14 | Customizer panels generated from the same schema/option keys | Free | TODO |
| WP9 | 1, 3 | `InspectorTabs` + "Reset to default" toolbar button (clears attributes to block.json defaults/example) on every block | Free | TODO |
| WP10 | 8 | Blocks/demos inherit kit: strip per-block defaults from demo content; block CSS uses kit vars | Free, theme | TODO |
| WP11 | 2 | Page/post meta "Content width": Default (kit) / Custom (value + unit) | Free | TODO |
| WP12 | 5 | Editor parity: editor styles, kit vars in iframe, theme.json alignment, patterns render identically | Free, theme | TODO |
| WP13 | 6 | Block inserter previews: `example` in every block.json + pattern viewport width | Free | TODO |
| WP14 | 11 | A11y module toggle (skip link, focus ring, reduced motion, link underline, contrast mode) + W3C/axe CI on demo pages | Free, theme | TODO |

Order: WP1 → WP2 → WP3/WP4 → WP5/WP6/WP7 → WP8 → WP9 → WP13 → WP10/WP12 → WP11 → WP14.

## Acceptance (per WP)

- Unit tests for schema/compiler changes; Playwright screenshot of editor vs frontend for WP12; axe = 0 serious issues for WP14.
- No overflow 320–1920 (existing `rs.mjs`).
- Existing options keep working (migration maps old Pro Site Kit option keys unchanged — keys are not renamed).

## Runner Benchmark impact

Low: PHP unit + JS build per WP; WP12/WP14 add one Playground Playwright job (~5 min).

## Rollback

Each WP is an isolated PR; revert the PR. WP1 keeps option keys, so reverting restores the Pro module without data loss.
