# Fields Long-tail Benchmark Register — V2

Snapshot: 2026-09-01

Surface: 3 — Fields / Field Groups / Control Registry

Status: `BANK_SURFACE_SEEDED`

This addendum records the second long-tail research pass for the Master Options & Possibilities Bank. It supplements `FIELDS-ECOSYSTEM-BENCHMARK-REGISTER.md`; it does not replace that register and does not claim that every discovered capability will ship unchanged.

## Research boundary

Capabilities are normalized into WPEssential product possibilities. Public WordPress APIs, official vendor documentation, authentic public repositories and provider documentation may establish behavior/evidence. Proprietary or nulled code is not copied. Executable callbacks are represented as typed/registered provider contracts where the use case is legitimate.

## Native WordPress findings

| Source | Material capability captured |
| --- | --- |
| `register_meta()` | object subtype scoping; array REST `schema.items`; REST prepare-value handling; revision-enabled metadata; post-type `custom-fields` support dependency for REST exposure |
| Block Bindings API | `core/post-meta`; server-side binding source registration; value callback/provider; `uses_context`; protected-meta restrictions; REST-visible meta requirement |
| Editor Block Bindings | editor source registration; field-list, read, write and edit-authorization providers; bound attribute/value compatibility |
| Block metadata | context remains part of the declarative block contract used by binding sources |

Primary references:

- https://developer.wordpress.org/reference/functions/register_meta/
- https://developer.wordpress.org/block-editor/reference-guides/block-api/block-bindings/
- https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/

## GraphQL / API ecosystem findings

WPGraphQL for ACF exposes product-level controls that are distinct from generic REST exposure: field-group GraphQL enablement, GraphQL field/type names, mapping from location rules or explicit GraphQL types, per-field exposure, descriptions, non-null contracts, type inference and adapters for custom field types.

Current provider documentation does not establish ACF field mutations, so `graphql.mutations` is recorded as `DEFERRED` / `WPE_FUTURE` rather than being presented as current parity.

Primary references:

- https://acf.wpgraphql.com/
- https://www.advancedcustomfields.com/resources/wp-rest-api-integration/

## Multisite / network findings

Settings frameworks and CMB2-style option boxes establish a real network-admin use case. The Bank now distinguishes network settings targeting/menu placement from ordinary site settings, while WPE-specific site-override policy and effective-value preview remain exceed candidates.

Primary references:

- https://docs.metabox.io/extensions/mb-settings-page/
- https://github.com/CMB2/CMB2

## Custom table and data-object findings

Meta Box Custom Table, JetEngine Custom Content Types and Pods Advanced Content Types reinforce that Fields must understand more than a generic “custom table storage” toggle. Long-tail records now include table naming/prefixing, create-vs-existing-table policy, field-to-column mapping, SQL column types, indexes, object-ID primary-key mapping, group serialization semantics, custom-table models and custom-content-type data-object targets.

Primary references:

- https://docs.metabox.io/extensions/mb-custom-table/
- https://crocoblock.com/knowledge-base/features/custom-content-type-overview/
- https://docs.pods.io/creating-editing-pods/creating-a-pod/advanced-content-type/

## Settings-framework and CMB2 addon findings

Redux metabox behavior adds explicit global/local override semantics plus page-template and post-format targeting. Verified CMB2 ecosystem references justify compatibility/provider mappings for attached-post selectors, post search and AJAX object search without baking third-party implementations into core.

Primary references:

- https://devs.redux.io/advanced/metaboxes.html
- https://github.com/CMB2/CMB2

## Normalized delta

This pass adds **52** normalized records in `fields--market-longtail-v2.json`.

Canonical discovery truth after this pass:

- target surfaces: 56
- seeded surfaces: 3
- CPT records: 107
- Taxonomy records: 71
- Fields records: **596**
- total Bank records: **774**
- `NATIVE_AUDITED`: 0
- `MARKET_AUDITED`: 0
- `BANK_REVIEWED`: 0

The numeric growth is not a completeness claim. Surface 3 remains `BANK_SURFACE_SEEDED` until duplicate/semantic overlap checks, current-native recheck, market-family recheck and formal review gates converge.

## Scope / safety

- Discovery/config/docs only.
- No production runtime behavior or live data migration.
- No arbitrary PHP/JS/eval configuration is adopted.
- No proprietary/nulled implementation source is copied.
- Existing owner boundaries remain unchanged.

## Next gate

Run the machine Bank contracts on the exact branch head. If certified, keep Surface 3 at `BANK_SURFACE_SEEDED`, then perform the explicit native/market dedup-review pass before considering `NATIVE_AUDITED`, `MARKET_AUDITED` or `BANK_REVIEWED`.
