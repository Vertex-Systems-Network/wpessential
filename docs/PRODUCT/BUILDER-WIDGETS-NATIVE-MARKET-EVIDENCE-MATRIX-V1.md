# Builder Widgets — Native & Market Evidence Matrix V1

Surface: **16 / Builder Widgets**  
Issue: **#499**  
Research anchor: **`main @ 42303a97938f1a80f9c1c9088fa70d40ba359279`**  
Mode: planning/contract only; `runtime_allowed=false`; `product_parity_allowed=false`.

## Verified primary sources

| Source | Evidence | Seed families informed |
|---|---|---|
| WordPress block metadata (`block.json`) | https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/ | typed block identity, attributes, supports, scripts/styles and metadata-driven registration |
| WordPress Block Registration | https://developer.wordpress.org/block-editor/getting-started/fundamentals/registration-of-a-block/ | server/client block registration and recommended server metadata registration |
| WordPress `register_block_type()` | https://developer.wordpress.org/reference/functions/register_block_type/ | canonical registered block identity and metadata-backed server registration |
| WordPress Block Context | https://developer.wordpress.org/block-editor/reference-guides/block-api/block-context/ | explicit provided/consumed context contracts for nested/dynamic components |
| WordPress Dynamic Rendering | https://developer.wordpress.org/block-editor/getting-started/fundamentals/static-dynamic-rendering/ | server render callback/template boundary and dynamic output lifecycle |
| Elementor Widgets | https://developers.elementor.com/docs/widgets/ | widget information, controls, dependencies, rendering and output caching |
| Elementor widget registration | https://developers.elementor.com/docs/managers/registering-widgets | registered widget-manager/provider pattern via `elementor/widgets/register` |

## Native decisions added in this pass

- WPE component identity should map through registered adapter/provider contracts. `block.json`/registered block metadata is evidence for declarative metadata, not permission to accept arbitrary PHP classes or render callbacks from authored input.
- Block attributes and block context are distinct: attributes are component state while context is explicitly provided/consumed inherited data. WPE dynamic binding records should preserve that distinction.
- Dynamic rendering is a server-owned render path. User-authored templates/settings may select an allowlisted renderer/provider, but may not inject executable callbacks.
- Builder asset dependencies should be provider-declared and conditionally loaded through the target builder/native runtime rather than arbitrary script URLs.
- Adapter parity must remain per-provider capability evidence; unsupported controls/context/render features degrade explicitly instead of pretending one builder API is universal.

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

This matrix does **not** promote `BANK_REVIEWED`. Full Gutenberg attributes/supports/bindings inventory, Elementor controls/render/cache semantics, Bricks and other builder coverage, safe style/asset policies, dynamic binding ownership, portability and zero-unresolved review remain open. No runtime adapter/widget registration is authorized.
