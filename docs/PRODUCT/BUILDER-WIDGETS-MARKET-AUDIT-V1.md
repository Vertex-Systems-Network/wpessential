# Builder Widgets — Market Audit V1

Status: `RESEARCH_COMPLETE / SUPERVISOR_INTEGRATION_PENDING`

Surface: 16 — Builder Widgets  
Snapshot: 2026-09-12  
Parent: #713  
Worker issue: #719  
Base: `54e4e70095f74a6ebff15da83cc2bbf3d447a49c`

## 1. Scope and boundary

This evidence-only audit reconciles all 18 current Builder Widgets records. WPE owns a builder-neutral component blueprint and adapter contracts; WordPress Blocks/Shortcodes/WP_Widget remain native adapters, while Elementor/Bricks/WPBakery capabilities remain provider compatibility. Fields, Query, Listings, Relations, Policy and Renderer keep their canonical ownership.

## 2. Current market evidence

### E1 — Elementor developer APIs
Official evidence:
- https://developers.elementor.com/docs/editor-controls/
- https://developers.elementor.com/docs/editor-controls/responsive-control/
- https://developers.elementor.com/docs/dynamic-tags/

Verified: regular/group/responsive controls, content/style sections, dynamic tags/data and widget render controls.

### E2 — Bricks developer APIs
Official evidence:
- https://academy.bricksbuilder.io/article/create-your-own-elements/
- https://academy.bricksbuilder.io/article/controls/
- https://academy.bricksbuilder.io/article/dynamic-data/

Verified: custom element identity, controls, render methods/scripts, responsive/control groups, query/repeater controls and custom dynamic-data tags.

### E3 — WordPress builder ecosystem
WordPress native Blocks/Block Bindings/Shortcodes/WP_Widget are already audited as native substrate. The market evidence above proves that richer control/style/dynamic-data adapters must remain explicit per-provider compatibility layers rather than being mislabeled native.

## 3. Record reconciliation

| Record | Disposition |
|---|---|
| `builder-widgets.component.identity` | MARKET_EVIDENCED — Elementor/Bricks expose typed component/element identities and categories. |
| `builder-widgets.component.adapters` | MARKET_EVIDENCED / WPE_HARD — multi-builder support requires explicit adapter/dependency declarations. |
| `builder-widgets.controls.registry` | MARKET_EVIDENCED_WITH_FIELDS_OWNER — E1/E2 expose controls; canonical reusable schema stays Fields-owned. |
| `builder-widgets.controls.content-style` | MARKET_EVIDENCED — content/style/advanced control groups are baseline parity. |
| `builder-widgets.controls.responsive` | MARKET_EVIDENCED — E1/E2 expose responsive and richer conditional/repeater-style controls. |
| `builder-widgets.render.server` | KEEP native — dynamic block/server rendering remains native/registered renderer truth. |
| `builder-widgets.render.client-provider` | PROVIDER_EVIDENCED — client rendering is adapter/provider-specific. |
| `builder-widgets.render.dynamic` | MARKET_EVIDENCED_WITH_OWNER_BOUNDARY — dynamic tags/data are common; Query/Listings/Relations/Block Bindings stay canonical sources. |
| `builder-widgets.render.ability` | KEEP_WPE_HARD — rendering must authorize protected data and escape by context; market dynamic data is not authorization proof. |
| `builder-widgets.render.states-cache` | KEEP_WPE_PRODUCT_QUALITY — explicit empty/error/loading/cache behavior remains WPE contract. |
| `builder-widgets.style.tokens` | MARKET_EVIDENCED / WPE_NORMALIZED — builder style systems exist; WPE validates canonical theme/design tokens. |
| `builder-widgets.style.box-state` | MARKET_EVIDENCED — dimensions/background/border/state styling is standard. |
| `builder-widgets.adapter.gutenberg-shortcode` | KEEP native — legacy ID remains narrowed to Gutenberg/block adapter. |
| `builder-widgets.adapter.elementor-bricks` | MARKET_EVIDENCED_COMPATIBILITY — E1/E2 directly prove both adapters. |
| `builder-widgets.adapter.legacy` | KEEP_COMPATIBILITY — WPBakery/Visual Composer remains separately certified compatibility, not native. |
| `builder-widgets.adapter.parity-portability` | KEEP_WPE_EXCEED — declare unsupported controls/data/style gaps and degraded import explicitly. |
| `builder-widgets.adapter.shortcode` | KEEP native — native Shortcode adapter remains distinct. |
| `builder-widgets.adapter.wp-widget` | KEEP native — native WP_Widget adapter remains distinct. |

Coverage: **18 / 18**. Unresolved research dispositions: **0**. Worker Bank/progress mutations: **0**.

## 4. WPE-exceed / safety

- Do not expose arbitrary PHP/callback source as authored widget configuration; only registered render/client/data providers.
- Dynamic values never bypass Policy/Ability checks or context-appropriate escaping.
- Adapter parity must be declared, not assumed: unsupported responsive, repeater, dynamic-data or styling behavior degrades visibly.
- Portability moves builder-neutral definitions and supported references, not provider secrets or executable code.

## 5. Supervisor integration requirements

A later Supervisor may add Elementor/Bricks official evidence, market-classify controls/dynamic/style and compatibility families, preserve native adapter provenance and canonical owner boundaries, and promote `MARKET_AUDITED` only after exact-head Bank integration/CI.

No adapter execution, arbitrary callbacks, `BANK_REVIEWED`, runtime/product certification, deployment or release is authorized.