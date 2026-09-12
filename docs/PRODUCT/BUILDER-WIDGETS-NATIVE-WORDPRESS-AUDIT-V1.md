# Builder Widgets — Native WordPress Audit V1

Surface: **16 / Builder Widgets**  
Issue: **#692**  
Parent: **#686**  
Exact-main audit anchor: `3839142cc8a225f443fcf9f6e0c26eb9641742e7`  
Seed: `config/product/options-bank/builder-widgets.json` — **16 records / BANK_SURFACE_SEEDED**

## Decision

**NATIVE AUDIT EVIDENCE COMPLETE WITH TWO NATIVE ADAPTER/BINDING CHANGES REQUIRED.** Worker branch is evidence-only and does not register blocks/widgets or promote lifecycle state.

WordPress currently supplies metadata-driven block registration, block attributes/supports/context, dynamic rendering, Block Bindings and the legacy `WP_Widget` API. Those are native adapter targets. Elementor/Bricks/WPBakery are market/provider adapters and must remain separate capability contracts.

## Current native sources

- Block metadata / `block.json` — https://developer.wordpress.org/block-editor/getting-started/fundamentals/block-json/
- block registration — https://developer.wordpress.org/block-editor/getting-started/fundamentals/registration-of-a-block/
- `register_block_type()` — https://developer.wordpress.org/reference/functions/register_block_type/
- Block Context — https://developer.wordpress.org/block-editor/reference-guides/block-api/block-context/
- Dynamic rendering — https://developer.wordpress.org/block-editor/getting-started/fundamentals/static-dynamic-rendering/
- Block Bindings — https://developer.wordpress.org/block-editor/reference-guides/block-api/block-bindings/
- `register_block_bindings_source()` — https://developer.wordpress.org/reference/functions/register_block_bindings_source/
- `WP_Widget` / `register_widget()` — https://developer.wordpress.org/reference/classes/wp_widget/ and https://developer.wordpress.org/reference/functions/register_widget/
- Shortcode API — https://developer.wordpress.org/plugins/shortcodes/
- accepted planning evidence: `docs/PRODUCT/BUILDER-WIDGETS-NATIVE-MARKET-EVIDENCE-MATRIX-V1.md`

Authored Bank data may select a **registered** render/binding/provider identity; it may never store arbitrary executable callbacks/classes.

## Seed-record disposition

| Seed record | Native disposition | Integration action |
|---|---|---|
| `builder-widgets.component.identity` | **NATIVE-CONFIRMED FOR BLOCK ADAPTER / WPE GENERIC BLUEPRINT** | add block metadata identity/version/category/support provenance |
| `builder-widgets.component.adapters` | **WPE ADAPTER REGISTRY** | preserve explicit provider capability/dependency declarations |
| `builder-widgets.controls.registry` | **NATIVE-PARTIAL + SHARED FIELDS OWNER** | block attributes/supports inform native adapter; generic control schema remains shared Fields-owned |
| `builder-widgets.controls.content-style` | **NATIVE-PARTIAL** | map only supported block controls/supports; richer builder controls stay provider-specific |
| `builder-widgets.controls.responsive` | **MARKET/WPE-HEAVY** | no universal native responsive/repeater control API claim |
| `builder-widgets.render.server` | **NATIVE-CONFIRMED** | dynamic block rendering/provider boundary; no authored callback text |
| `builder-widgets.render.client-provider` | **WPE/PROVIDER** | registered client provider only |
| `builder-widgets.render.dynamic` | **NATIVE-GAP / BLOCK-BINDINGS SUBSTRATE** | enrich/split to explicitly account for registered Block Bindings sources while preserving Query/Listings/Relations owners |
| `builder-widgets.render.ability` | **WPE POLICY/ABILITY** | no native Ability claim; escaping remains render-context requirement |
| `builder-widgets.render.states-cache` | **WPE/MARKET** | native block APIs do not provide universal loading/error/cache product semantics |
| `builder-widgets.style.tokens` | **NATIVE-PARTIAL + WPE TOKEN OWNER** | block supports/theme settings are substrate; canonical style tokens remain WPE/theme-owned |
| `builder-widgets.style.box-state` | **NATIVE-PARTIAL / PROVIDER-SPECIFIC** | declare per-adapter support/degrade state |
| `builder-widgets.adapter.gutenberg-shortcode` | **NATIVE BUT OVER-COMBINED** | split Gutenberg/block adapter and Shortcode adapter; they have distinct registration/render/security contracts |
| `builder-widgets.adapter.elementor-bricks` | **MARKET/PROVIDER** | retain compatibility adapter only |
| `builder-widgets.adapter.legacy` | **MARKET/PROVIDER** — WPBakery/Visual Composer is not WordPress `WP_Widget` | retain market adapter; do not use it as native legacy-widget evidence |
| `builder-widgets.adapter.parity-portability` | **WPE DIAGNOSTIC** | preserve explicit unsupported/degraded capabilities; no false universal parity |

## Required native Bank changes

### 1. Split `adapter.gutenberg-shortcode`

Preserve one Gutenberg/block adapter record and add a distinct shortcode adapter record. Shortcodes are text-token expansion with different parsing/escaping/runtime constraints from registered blocks.

### 2. Add `builder-widgets.adapter.wp-widget`

Represent the native legacy `WP_Widget` / `register_widget()` compatibility surface separately from WPBakery/Visual Composer.

### 3. Enrich `render.dynamic` with Block Bindings

Add native source metadata for registered Block Bindings and require allowlisted binding source identities. Do not create a private raw callback binding contract.

## Required Supervisor Bank integration

1. Populate native block/bindings/widget/shortcode sources.
2. Split the combined Gutenberg/Shortcode adapter family without losing semantic coverage.
3. Add the native `WP_Widget` adapter family.
4. Enrich `render.dynamic` with Block Bindings provenance and registered-source boundary.
5. Keep third-party builder adapters provider-specific and capability-degraded where unsupported.

## Native completeness / unresolved items

After those changes, no known WordPress-native component adapter family remains missing for this V1 Bank. Detailed builder-market parity, client Interactivity behavior, style safety and actual adapter registration are later gates.

## Gate boundary

Worker conclusion: **native evidence complete; adapter/binding Bank integration pending**.  
Not promoted here: `NATIVE_AUDITED`, market review, `BANK_REVIEWED`, adapter runtime, Atomic Option Contract, UX or product parity.