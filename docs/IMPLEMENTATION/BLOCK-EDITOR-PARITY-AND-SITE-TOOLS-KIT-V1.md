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

WP5 #1596, WP6 #1598 and bounded browser-language WP7 #1600 merged. WP7 candidate 8a3ea579 passed 8/8 workflows, 10/10 checks, WordPress E2E 46/46; merged ab80f77d. PHP architecture remains separate #1601, planning only. WP8 #1602 / #1603 active: shared scalar, responsive, composite and state controls, explicit palette/scalar/bundled-font transfer, and bidirectional browser acceptance verified on Free `5d928215` / theme `4d849bc9` (8/8 workflows, 10/10 checks, 48/48 WordPress E2E). Versioned ownership contract and read-only breakpoint review are on active candidate `a9dbb1a0` with theme `b9b53ebc`; fresh verification pending. No release or full-product certification.

## Work packages

| WP | Owner request | Scope | Repo | Status |
|---|---|---|---|---|
| WP1 | 7 | Move Site Kit from Pro to **Free**, rename **Site Tools Kit**; Pro keeps premium extras (code inserter extras, Elementor sync) via filter | Free, Pro | MIGRATION MERGED (premium extension boundary pending) |
| WP2 | 15 | House `Dropdown` component (React + vanilla for admin screens); replace `<select>` in Site Tools Kit, field UI, block inspector | Free | PARTIAL (PHP/vanilla screens; React screens Demos/Modules still native — follow-up) |
| WP3 | 10 | Breakpoints list in Layout (320…1920, enable/disable, edit px); `ResponsiveControl` device dropdown with icons/tooltips; used by kit and blocks | Free | KIT DONE for Site Tools Kit; block inspector reuse lands with WP9 |
| WP4 | 4 | Units setting: multi-select allowed units (px, %, em, rem, vh, vw, vmin, vmax, ch, fr) + custom units with `1 X = n <base>`; `UnitControl` shows only enabled units; compiler converts custom → `calc(n * base)` | Free | KIT DONE for Site Tools Kit (block UnitControl reuse with WP9) |
| WP5 | 9 | Global colours / dark colours / typography: repeater "Add custom" with name + slug; exposed as CSS vars + `theme.json` palette/fontSizes + global picker in blocks/Elementor | Free | MERGED — #1596; 42/42 Free, 49/49 paired |
| WP6 | 13 | Expand groups: images, forms (labels, inputs, focus, placeholder, errors), buttons (states, sizes, variants), background, links (states, underline), shadows (named presets) — every sub-element + full CSS props | Free | MERGED — #1598; 44/44 E2E |
| WP7 | 12 | Custom code: repeater of snippets — language (CSS, SCSS-lite, JS, HTML, PHP-off-by-default), location (head, body open, footer, before/after content, admin, login), conditions (all/front/page ids), priority, enable | Free (PHP exec Pro) | MERGED — bounded browser-language #1600; PHP design deferred #1601 |
| WP8 | 14 | Customizer panels generated from the same schema/option keys | Free | ACTIVE — #1602 / #1603 shared controls and bounded handoff verified; versioned contract, native preview regression and legacy breakpoint reconciliation pending |
| WP9 | 1, 3 | `InspectorTabs` + "Reset to default" toolbar button (clears attributes to block.json defaults/example) on every block | Free | TODO |
| WP10 | 8 | Blocks/demos inherit kit: strip per-block defaults from demo content; block CSS uses kit vars | Free, theme | TODO |
| WP11 | 2 | Page/post meta "Content width": Default (kit) / Custom (value + unit) | Free | TODO |
| WP12 | 5 | Editor parity: editor styles, kit vars in iframe, theme.json alignment, patterns render identically | Free, theme | TODO |
| WP13 | 6 | Block inserter previews: `example` in every block.json + pattern viewport width | Free | TODO |
| WP14 | 11 | A11y module toggle (skip link, focus ring, reduced motion, link underline, contrast mode) + W3C/axe CI on demo pages | Free, theme | TODO |

Order: WP1 → WP2 → WP3/WP4 → WP5/WP6/WP7 → WP8 → WP9 → WP13 → WP10/WP12 → WP11 → WP14.

## WP7 SCSS-lite V1 boundary

SCSS-lite is a dependency-free subset: up to 32 unique literal variables declared before flat selector rules, with variable substitution in property values. Source limit 64 KiB; up to 128 rules and 128 declarations per rule. Unsupported syntax (including nesting, imports, mixins, interpolation and undefined variables) is rejected; no external compilation, file access or network fetch. Original source is retained and compilation is checked at save and render. Full Sass is not claimed. PHP execution remains excluded from Free.

## WP7 PHP security boundary (open)

WP7 lists PHP as Pro-only and off by default, but ADR-0239 supersedes only ADR-0232 decision 1; it does not supersede ADR-0004 or ADR-0193. ADR-0004 requires a separately accepted developer-mode ADR and threat model before implementing user-entered server code; ADR-0193 routes server code through the reviewed Extension SDK/plugin path and prohibits PHP/eval. Therefore Free rejects new PHP/unknown-language rows, preserves existing opaque rows even on privileged saves, excludes them from UI editing and runtime selection. No PHP executor or premium execution claim is introduced. Pro compatibility and entitlement gates are necessary but not sufficient. A dedicated ADR and recovery/security evidence must resolve the conflict before any Pro executor is considered. #1601 tracks this separately (planning only). The bounded browser-language WP7 may integrate after its acceptance gates pass; do not claim PHP feature completion. WP8 does not depend on PHP execution.

## WP8 current integration boundary

WP8 #1602 / #1603 shared scalar, responsive, composite and state controls use native changesets and canonical option keys. Verified Free `5d928215` / companion theme `4d849bc9` (hi-wpessential #129, issue #128) passed all eight workflows, ten checks and 48/48 WordPress E2E (run 46076690293). Explicit capability/nonce-gated transfer covers reviewed palette, scalar and bundled font roles, preserves legacy theme mods and canonical conflicts, requires exact canonical readback, retains reset ownership, and restores legacy behavior when the module is disabled. Google extra-font storage normalization and theme font preset resolution are verified. Versioned ownership handshake and administrator-facing read-only breakpoint review in `a9dbb1a0` / `b9b53ebc` are pending exact paired verification. Initial browser accessibility workflow failed at Chromium dependency installation (Microsoft apt repository HTTP 403), not an accessibility assertion; the failed job was rerun unchanged.

Legacy breakpoint transfer is intentionally not enabled while legacy responsive controls store numeric device keys/aliases. The new disabled-by-default smallest-phone slot represents 320 and 360 independently without changing existing small-phone settings. Even when the selected media boundaries are representable, moving only the breakpoint option without reconciling dependent controls would create divergent semantics. Preserve the raw source and defer unresolved formats (including Google delivery ownership); never claim full legacy parity. Native preview regression work checks native Desktop/Mobile switching, live width edits, disabling/re-enabling a selected breakpoint and retention of unpublished device values. This successor is not yet verified. WP8 remains ACTIVE; no merge or release claim.

## Acceptance (per WP)

- Unit tests for schema/compiler changes; Playwright screenshot of editor vs frontend for WP12; axe = 0 serious issues for WP14.
- No overflow 320–1920 (existing `rs.mjs`).
- Existing options keep working (migration maps old Pro Site Kit option keys unchanged — keys are not renamed).

## Runner Benchmark impact

Low: PHP unit + JS build per WP; WP12/WP14 add one Playground Playwright job (~5 min).

## Rollback

Each WP is an isolated PR; revert the PR. WP1 keeps option keys, so reverting restores the Pro module without data loss.

### WP8 consolidated local-cache candidate

The active font-reference guard candidate `18637d86` is the base. Local-to-local Google handoff is eligible only when all winning family metadata and existing upload files validate; transfer reuses the existing files, preserves the original theme cache and delivery mode, and confirms canonical metadata readback before claiming ownership. Missing, malformed, remote and conflicting cache entries remain legacy-owned. Breakpoint ownership remains deferred where dependent legacy responsive values cannot be represented losslessly; the independent smallest-phone slot removes the earlier 320/360 collision, the read-only review and explicit preview arbitration remain in place. No final CI or full parity claim is made here.

### WP8 canonical breakpoint bridge candidate

Free `b00694a3` passed all eight workflows, including native preview/save and local-cache regression. The new paired candidate adds a separately versioned, explicit breakpoint handoff. A frozen phone-key mapping retains both 560/480 and 360/320 legacy numeric device identities; the original responsive theme data is not rewritten. The companion theme consumes canonical widths, enabled states and media order in CSS and Customizer controls. Canonical conflicting selections or incompatible existing canonical responsive values defer transfer. Legacy responsive field values still owned by the theme remain in its original store, rather than being silently converted across different selectors, units or compiler semantics. Standalone theme fallback remains available. Fresh exact-head paired verification required; no release or complete legacy-field parity claim.

### WP8 bounded responsive menu ownership

Companion candidate `dc7a8f4f` failed strict WPCS on seven array alignment warnings, not a runtime assertion. The successor `0036bf9c` aligns the localization array and adds a separate responsive handoff handshake for menu size/gap. Free can transfer complete pixel-range trees through the frozen device mapping only after confirmed breakpoint ownership; unknown device keys, out-of-range values, incompatible units, conflicting aliases and differing canonical values defer transfer. Original theme data is retained; standalone fallback remains. Other legacy fields with different compiler semantics remain outside this bounded transfer. Fresh paired CI required.
