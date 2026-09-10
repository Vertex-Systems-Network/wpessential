# Builder Widgets — Native & Market Evidence Matrix V1

Surface: **16 / Builder Widgets**  
Issue: **#499**  
Research anchor: **`main @ fad3b2f37bd44a01d77438e71877d7990b4bfe2c`**  
Mode: planning/contract only; `runtime_allowed=false`; `product_parity_allowed=false`.

## Verified primary sources

| Source | Evidence | Seed families informed |
|---|---|---|
| WordPress Block Registration | https://developer.wordpress.org/block-editor/getting-started/fundamentals/registration-of-a-block/ | server/client block registration, `block.json` metadata and dynamic server rendering |
| WordPress `register_block_type()` | https://developer.wordpress.org/reference/functions/register_block_type/ | canonical registered block identity and metadata-backed registration |
| Elementor Widgets | https://developers.elementor.com/docs/widgets/ | widget information, controls, dependencies, rendering and output caching |
| Elementor Add New Widget | https://developers.elementor.com/docs/widgets/add-new-widget | registered widget manager/provider pattern via `elementor/widgets/register` |

## Seed disposition evidence

- `builder-widgets.blueprint.identity` — stable component identity/version is supported by WordPress block metadata and builder widget IDs.
- `builder-widgets.controls.schema` — control schemas are established builder behavior; WPE should compose a shared typed control contract rather than clone each builder API.
- `builder-widgets.render.mode` — valid server/registered-client provider boundary; arbitrary executable class/code input is prohibited.
- `builder-widgets.dynamic.bindings` — must consume Query/Listings/Relations/Ability owners through references rather than own a private data engine.
- `builder-widgets.style.bindings` — responsive style settings are valid but must be validated/allowlisted; no arbitrary unsafe CSS/script execution contract is implied.
- `builder-widgets.adapter.registry` — registered adapters are the correct compatibility boundary for Gutenberg/Elementor/other builders.
- `builder-widgets.adapter.parity` — WPE diagnostic candidate for unsupported capabilities; not evidence that parity currently exists.
- `builder-widgets.portability.blueprint` — versioned component definition portability is a valid candidate, but builder-specific environmental references must be preserved/degraded explicitly.

## Remaining gates

This matrix does **not** promote `BANK_REVIEWED`. Gutenberg attributes/supports/context APIs, Elementor controls/rendering, Bricks and other builder coverage, security/style sanitization, dynamic binding ownership, portability and zero-unresolved review remain open. No runtime adapter/widget registration is authorized.