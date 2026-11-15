# ADR-0238 — Kit elements move from the theme into Free and Pro

Status: **ACCEPTED**
Date: **2026-11-15**
Approval: owner request of 2026-11-15 ("elements + customizer ki fields theme se nikal kr … plugin me move")

## Context

The Hi WPEssential theme (`Vertex-Systems-Network/hi-wpessential`) bundled a "WPEssential Core" plugin in `core/`. Its kit holds 47 elements. Each element is defined once in `kit/<name>/element.json` with a `render.php`, a `style.css` and an optional `script.js`. One engine (`Kit.php`, `Kit/Groups.php`, `Kit/Widget.php`) ships each element as a Gutenberg block (`wpessential/<name>`) and as an Elementor widget (`wpe-<name>`), with the same options and render.

Blocks and widgets are plugin territory under the WordPress.org theme rules, and the owner wants Free and Pro to own them.

## Decision

1. **Engine in Free.** The engine is now `frameworks/Platform/Kit`, namespace `WPEssential\Platform\Kit`, booted from `Bootstrap\Plugin`.
   - The code is ported as-is. The only changes are the namespace, the text domain and the paths, so it stays diffable with the theme.
   - Elementor classes are typed for PHPStan through `tools/phpstan/elementor-stubs.php`.
2. **Folders.** Free's own `kit/` folder comes first. WPEssential Pro adds its own through the `wpessential_kit_roots` filter, which takes `dir`, `url`, `edition` and the `namespace` of its widget classes.
3. **Split.**
   - **Free (29):** accordion, alert, button, call-to-action, code, comments, container, counter, divider, gallery, heading, icon, icon-list, image, info-box, menu, progress-bar, rating, search, site-archive-widgets, site-logo, social, svg, table, tabs, team-member, testimonial, text and video.
   - **Pro (17, the `ProKit` module):** business-hours, calendar, chart, countdown, flip, forms, image-carousel, image-comparison, map, modal, posts-grid, price-list, pricing-table, products, social-feed, timeline and tooltip.
4. **Switches.** Each element is switched on or off per builder on the Elements screen, through `ElementRegistry` (`wpessential_elements`). This replaces the theme's `wpessential_kit_disabled` option.
5. **Pro elements in Free.** `kit/pro-catalog.json` lists the Pro elements, so Free can show them dimmed with a Premium lock. Their code ships only in Pro, and Pro loads it only with an active licence or trial.

## Consequences

- Without a Pro licence, blocks of Pro elements already on a page render nothing.
- The theme's `core/` kit is to be removed once the theme requires WPEssential Free.
- The demo importer and the Customizer fields follow in separate changes.
