# Kit elements

The WPEssential kit lives in the plugins:
- WPEssential Free has the core elements in `kit/`.
- WPEssential Pro has the advanced and WooCommerce elements in its `kit/`.

Each element works as a Gutenberg block and as an Elementor widget, with the same options. The theme (Hi WPEssential) does not include any elements; it supplies only design (theme.json, styles, templates). This page moved here from the theme wiki on 2026-11-27.

## Element status (Essential demo plan, Phase 1)

| Element | Edition | Status |
|---|---|---|
| Login / register / lost & reset password (`login-form`) | Free | Built |
| Content switcher (`content-switcher`) | Free | Built |
| Breadcrumbs, post meta, post navigation, author box, related posts, table of contents | Free | Built |
| Steps / process (`steps`) | Free | Built |
| Logo / brand strip | Free | Built, as `marquee` (static or scrolling) |
| Timeline (`timeline`) | Pro | Built |
| Comparison table (`comparison-table`) | Free | Built (the plan listed Pro) |
| WooCommerce set (`woo-*`, `mini-cart`, `products`) | Pro | Built |
| Account dashboard (`account-dashboard`) | Free | Built (the plan listed Pro) |
| Dynamic listing (`dynamic-listing`) | Free | Built (the plan listed Pro) |
| Off-canvas, Hotspots, Lottie, Animated headline, Marquee, Notification bar, Back-to-top, Social share, Cookie notice, Sticky video, Feed grid | Free | Built |
| Reading progress bar | — | Not checked: may be part of Back-to-top or Progress bar |
| Dual button, advanced table filters, mega-menu templates | — | Not built yet |

New element work follows `docs/IMPLEMENTATION/BLOCK-EDITOR-PARITY-AND-SITE-TOOLS-KIT-V1.md`, in particular WP9 (Content / Style / Advanced tabs) and WP13 (inserter previews).

## Shapes, Grain and Elevation (Batch #79)

Shared Advanced options are available on every Kit Gutenberg block and Elementor widget. All are **opt-in**.

- **Clip shapes:** Slant, Wave and Blob use fixed allowlisted `clip-path: polygon(...)` outlines. These clip the entire element, including borders, text and keyboard-focus indicators close to the edges. Use them primarily for decorative images/sections, not important text, forms or controls.
- **Noise/grain:** An embedded static SVG `feTurbulence` texture (no network request or JavaScript) sits in a non-clickable `::before` layer. Responsive intensity is limited to 1–20%, with a default of 8% when enabled; the overlay is hidden in print. Components using `::before` for their own UI may conflict and require visual review. Contrast and readability should be checked after enabling noise.
- **State-aware elevation:** Separate Small/Medium/Large theme-shadow presets for **rest**, **hover/focus-within**, and **keyboard-focus-visible**. Values resolve against WordPress theme.json `--wp--preset--shadow--sm/md/lg` variables, so themes can update them. An enabled state adds only its own allowlisted CSS classes. CSS transitions are disabled under reduced motion.

No settings are applied to existing saved content unless activated. Advanced custom SVG clipping, animated grain, drag-editable masks and per-element compatibility audits remain future work.

## Visual Effects — Gradient Borders, Blend Modes and Gradient Masks (Batch #78)

All Gutenberg Kit blocks and Elementor Kit widgets now support these opt-in controls in Advanced:

- **Gradient border:** rounded, non-interactive outer border overlay using theme-aware start/end colour presets; adjustable responsive thickness (1–16px) and angle (0–360°). Uses a mask-based `::after` ring instead of `border-image`, so it follows the element radius without changing its box size. Animation is optional (cycle 1500–12000ms), disabled automatically for reduced-motion users and printing. Controls only activate when **Enable gradient border** is on.
- **Blend mode:** multiply, screen, overlay, darken, lighten, difference or exclusion, implemented using allowlisted CSS classes; blend results vary with the background behind the widget.
- **Gradient mask:** top, bottom, left, right, both vertical or both horizontal edges; applies real `mask-image`/`-webkit-mask-image` to the widget. The fade can visually conceal children or content near edges, so avoid applying it to buttons, forms or critical messages.

All three features are off by default. The gradient overlay has `pointer-events:none` to preserve normal links/controls, but elements with custom pseudo-elements or specially positioned roots may need manual visual QA. The CSS animation does not run under `prefers-reduced-motion: reduce`. This batch covers these effects only; noise textures, clip-path shapes and state-aware elevation remain pending.


## Motion Layout — Parallax Layers and Sticky Stop Target (Batch #77)

In Gutenberg and Elementor, shared Kit Advanced controls provide independent **responsive parallax travel for the first four direct-child layers**, signed -240px to +240px, using X/Y direction. Set a given layer's responsive value to 0 to disable it on that screen width. Kit Container applies layers to its inner wrapper's direct children. The CSS individual `translate` property preserves existing element transforms. During reduced motion, parallax is switched off. Arbitrary deeply nested selectors and more than four layers are not supported yet.

Sticky **Stop at element ID** accepts a same-page target ID (no hash prefix). The ID must start with a letter, contain only letters, numbers, hyphens or underscores, and be at most 64 characters. The target must come **after** the sticky element in the document. Native sticky still stops at its containing block, while the optional JavaScript offset additionally prevents overlap with the target. Tablet/desktop sticky enable controls continue to work. Overflow ancestors may affect sticky positioning.

The `kit/motion-layout.js` script loads only for opted-in parallax or named stop targets, updates on scroll/resize with requestAnimationFrame, and recognizes dynamically added preview widgets. The feature is not a security or access-control boundary.

## Advanced Motion (Kit shared controls)

Every Kit Gutenberg block and Elementor widget can opt into **Scroll / Hover+focus / Click / Page load** entrance triggers while retaining the existing CSS scroll default. A small shared `kit/motion.js` is enqueued only if a trigger is configured and an entrance animation is selected. It is progressive: when JavaScript is blocked, the original CSS animation still applies and page content remains readable. Hover includes keyboard focus; click handlers do not override link navigation. IntersectionObserver handles scroll once and honors reduced-motion preference.

**Child staggering** animates the first 12 direct children using the selected animation duration and configurable stagger step. **Reduced motion** offers no entrance effect or a gentle 180ms fade with no transforms. **Sticky within parent** uses CSS positioning with responsive top offset; the browser naturally stops it at its containing block. Tablet-only and desktop-only options work at 768px and 1024px. Sticky positioning can be affected by overflow/scrolling ancestors. Named stop-target selectors and per-layer parallax remain roadmap items.

## Conditional display (Kit Advanced settings)

Shared conditional controls are available on all Kit elements in Gutenberg and Elementor. All configured **server-side** gates are ANDed: login state, required user-role slug, WooCommerce cart empty/nonempty, a purchased WooCommerce product ID, start/end date and time in the WordPress site's configured timezone (`YYYY-MM-DD HH:mm`), exact URL path, URL query key/value, and exact referrer hostname. Invalid values, absent WooCommerce state, or invalid dates fail closed. A failed condition suppresses HTML and its inline styles, not merely hides it. Cart and purchase checks require WooCommerce; purchase checks require a signed-in customer.

The **device** condition is implemented separately using CSS viewport media queries: mobile under 768px, tablet 768–1023px, desktop at least 1024px. Device hiding leaves markup in the DOM, so it is not suitable for secret information.

**Important:** These are display/personalization rules, **not an authorization system**. URL query strings and referrer headers are user-controlled, and CDN/full-page caches may ignore `DONOTCACHEPAGE`. Never place confidential or payment-sensitive content solely behind display rules; protect it with capability checks in its actual data source. Disable public caching for pages using login/cart/customer conditions. Editors can still edit the saved element, but the preview is suppressed if the active visitor does not meet the rules.

# Kit elements

WPEssential elements are defined once and work as both a Gutenberg block and an Elementor widget, with the same options. Each element is a folder `kit/<name>/` with three files:

- `element.json`
- `render.php`
- `style.css`

An optional `script.js` is loaded only on pages that use the element, in both builders.

| Element | Highlights |
| --- | --- |
| Heading | Eyebrow, accent words (gradient, outline, highlight), fluid size, text shadow / stroke / blend |
| Text | Drop cap (default / stacked / framed), columns, line clamp with read more, gradient text |
| Button | Icon library and SVG upload, variants, sizes, hover effects, every state |
| Image | Responsive srcset, dark-mode image, lightbox, aspect ratio / fit per breakpoint, CSS filters |
| Icon list | Repeater items, icons or numbers, inline / grid, dividers, icon shapes |
| Info box | Icon, image or number; top / left / right per breakpoint; badge; button; link on box, title or button; stacked / framed icons; box and media hover effects; equal height |
| Accordion | Native details/summary (no JavaScript), one or many open, FAQ schema, numbers, own icon per item, open icon, style presets, open-item colours |
| Tabs | ARIA tabs with arrow keys / Home / End, top / bottom / left / right per breakpoint, icons and badges, underline / pill / box indicators, deep links (#name), autoplay that pauses on hover, scroll / stack / accordion on small screens, all panels shown without JavaScript |
| Posts grid | Published posts and public custom post types; taxonomy term filtering, configurable cards, responsive columns, featured images, pagination and empty state. Shared Gutenberg/Elementor renderer with automated WordPress smoke tests. |

| Testimonial | Accessible quote and rating, avatar, linked author name, role / company, responsive alignment, styles and optional hover effects. Gutenberg + Elementor share the same renderer. |

| Counter | Numeric milestones and statistics; custom start/end values, decimals, separators, prefix/suffix, optional icon, responsive styling, viewport count-up animation with reduced-motion fallback and accessible static final value |

| Gallery | Reorderable images, media-library responsive image output, alt and caption per image, grid or masonry columns, responsive layout, custom links and keyboard-enabled native lightbox with non-JavaScript fallback |

| Container | Responsive flex or CSS grid with gap, wrapping, width, alignment and style controls. Gutenberg: real draggable/reorderable InnerBlocks, including nested containers. Elementor: WYSIWYG content wrapper; for draggable nested Elementor widgets use Elementor's native Container. |

| Video & Audio | HTML5 video/audio files with native controls, loop/mute/preload, optional poster and WebVTT captions; YouTube/Vimeo validated embeds only load after explicit Play; non-JS source links remain available; responsive player sizing and shared Gutenberg/Elementor renderer |

| Progress bar | Multiple progress values with individual max goals, horizontal bars or circular rings, percentages/values/fractions, optional descriptions, screen-reader progress semantics, viewport animation and reduced-motion fallback |

| Code / HTML / Embed | Safe rich HTML (scripts stripped), escaped source-code samples with accessible Copy action, trusted WordPress provider oEmbed and fallback external link; shared Gutenberg and Elementor implementation |

| Data Table | Safe CSV/TSV data import, proper quoted-field parsing, accessible table headings and captions, client-side search and numeric-aware sorting, horizontal scroll or stacked mobile rows and responsive styling |

| Interactive Tooltip | Focus/hover/touch tooltip, native show-more disclosure, two-panel switcher with keyboard/RTL arrow navigation; safe rich content, reduced-motion, and readable no-JavaScript fallbacks |

| Countdown | WordPress site-timezone target date, 4-unit ticking timer, expired state, accessible completion message, and usable date fallback without JavaScript. |
| SVG/Icon Animation | Safe built-in SVG preset paths, decorative/meaningful semantics, viewport-triggered draw/pulse/spin and reduced-motion support. |
| Team Member | Profile image via WordPress media, name/role/bio, profile/email and validated social links, horizontal/stacked responsive cards. |
| Site Logo / Title | Theme custom logo, site title/tagline, branding overrides, linked home destination and responsive image sizing. |
| Divider / Spacer | Semantic horizontal rule, labelled or decorative separator, and non-semantic responsive vertical spacer. |

| Image Carousel | Media-library image slides, keyboard-accessible horizontal scroll, arrows and dots, optional autoplay with pause on hover/focus and reduced-motion respect |
| Modal / Popup | Native dialog for explicit click-to-open overlays, Escape/backdrop/close behavior, focus restoration, sanitized rich text and no-JS native disclosure fallback |
| Pricing Table | Responsive single-plan card with features, included/excluded states, featured badge, price labels and safe call-to-action URLs |

| Map | Validated map coordinates, server-rendered OpenStreetMap location link, optional consent-first iframe, responsive map frame, no third-party requests before click |
| WooCommerce Products | Published product cards from real WooCommerce queries with category/featured/stock filters, prices, sale badges, responsive grid, safe product links, missing-plugin fallback |
| Social Icons | Repeatable platform links with hardcoded SVG presets, HTTPS-only profile URLs, accessible labels, optional text and responsive shapes |

| Social Feed | Curated social posts or dynamically queried published WordPress posts, media-library images, safe links, responsive columns and no-JS visible cards with optional progressive load more. No third-party API sync. |
| Call to Action | Responsive banner, optional attachment image, safe rich message, semantic heading, validated primary/secondary button links and Elementor/Gutenberg style controls |
| Calendar / Events | Strict timezone-aware manual events, sorted upcoming list or accessible month calendar, all-day/timed entries, location and safe detail links |
| Search | Native GET search for published site content, optional post/page/WooCommerce product scope, inline or no-JS expandable form with accessible controls |

| Comments | Approved public WordPress comments, author avatar/date/count, safe comment links and optional native WordPress comment submission form, no custom comment endpoint |
| Timeline / Steps | Semantic milestones with images, dates, safe rich descriptions/links, vertical or horizontally scrollable layouts |
| Rating | Accessible display-only rating, 5/10-point scales, fractional rating symbols and numeric score with screen-reader value |
| Alert / Notice | Accessible success/info/warning/error notices with sanitized rich HTML, progressive dismissal and optional session-only persistence |
| Business Hours | Seven-day schedule, validated HH:MM clock format, closed days, optional highlight of current weekday in site's timezone |
| Image Comparison | Before/after images with alt text, native keyboard-accessible range control and visible static split without JS |

| Price List / Restaurant Menu | Repeatable menu and service prices, old prices, badges and images; responsive list/cards, safe optional links, semantic list, no JS required. |
| Navigation / Mega Menu | Native WordPress menus, focus/click/keyboard submenu toggles, two-column dropdowns and **published Gutenberg synced-pattern or Elementor saved-template mega panels** mapped by menu item ID. Mobile submenus open independently and retain no-JS fallback; unpublished/non-template content is rejected. |
| Site / Archive Widgets | Context-aware archive title/description, public post meta, breadcrumbs, author and pagination. |
| WPForms Lite Form | Embed existing published WPForms forms with their validation and antispam; no duplicate form handler. |
| Standalone Icon | Accessible icons or optional links, styles, shape and responsive controls. |
| Chart | Accessible numeric bar, column and donut visualizations with screen-reader table and no dependency. |
| Flip / Hover Box | Native keyboard/touch disclosure with front/back copy and CSS reduced-motion flip. |

## Container queries, fluid spacing and theme tokens

Every Kit element now exposes advanced opt-in layout controls:

- **Act as a CSS size query container:** establishes `container-type: inline-size` on the element.
- **Hide below/above nearest container width:** generates CSS `@container` visibility rules using a validated 160–1920px threshold. Conditions depend on an **ancestor** query container, not the viewport or the element itself. Use the source toggle on the parent Kit element. More elaborate child-column/style changes based on container size remain future work.
- **Fluid padding/gap:** independent small (360px viewport) and large (1440px viewport) values interpolate using CSS `clamp()`. Set *both* values in valid ascending order to activate fluid spacing. Opted-in fluid padding has priority over padding tokens.
- **Padding and corner-radius theme tokens:** Small/Medium/Large/XL presets use the real spacing tokens `--wp--preset--spacing--20/40/50/70` and radius tokens `--wp--custom--radius--sm/md/lg/xl`. User styles follow theme customizations. Controls do not change existing content unless set.

The shared controls work in both Gutenberg and Elementor.

## RTL-aware logical inline spacing

Every Kit widget/block now offers responsive **Margin inline start/end** and **Padding inline start/end** in its Advanced panel. Unlike physical left/right spacing, inline start/end automatically swap with the inherited document direction; no separate Arabic/Urdu configuration is necessary. Negative values are supported for margins, while padding controls start at 0. The fields are opt-in and preserve existing saved layouts. Mirroring decorative directional icons remains a separate roadmap task.

## New shared advanced presentation controls

All Kit widgets and blocks now support opt-in **keyboard focus outline** (style, theme-palette colour, responsive width and offset), **glass surfaces** (light/dark palette-aware translucency with backdrop blur and opacity), and **Hide when printing**. Existing widgets do not change unless a control is enabled. Glass uses a solid surface fallback where colour mixing is unsupported. Focus outline choices apply to the element root and its interactive children and take priority over ordinary widget link/field outlines.

## What every element offers

- 9 breakpoints
- units on every size
- custom CSS
- transform and entrance animation
- ARIA options
- custom attributes
- hiding per breakpoint

**Turning elements off:** WPEssential → Elements switches each element on or off; all are on by default. An element that is off is not loaded in either builder.

### Advanced Navigation / Mega panels

In Appearance → Menus, find the WordPress menu item's ID. Add a row in the Navigation widget's **Saved mega panels by menu item ID** repeater, set the menu item ID, choose **Gutenberg synced pattern (wp_block)** or **Elementor saved template**, then enter the published template post ID. Parent links still work, ordinary child menu links stay available, and the template appears alongside them. Only published template posts are rendered. Use the desktop trigger and mega width controls to customize interaction and layout; a separate toggle controls each mobile submenu. JavaScript progressively enhances the links (keyboard Escape closes, aria-expanded updates), with the original submenus readable without JavaScript. Nested templates that recursively embed the current mega menu are suppressed.

Full drag-and-drop menu builder, dynamic per-item badges/media, and complete benchmark parity remain separate phases.
