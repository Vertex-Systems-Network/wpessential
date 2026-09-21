# Dashboard Widgets — Trusted Component Blueprint / Renderer Implementation Contract V1

Status: **implementation-ready planning contract / Dashboard runtime renderer invocation NOT authorized**  
Surface: **10 — Dashboard Widgets**  
Issue: **#1168**  
Exact base: `main@a8b3539168ba72f74473d4bc9e3fef6af445147f`

## 1. Purpose

This contract closes the component-registration gap identified by Issue #1167 / PR #1169.

Surface 10 can already compile a Published Dashboard Widget Definition into a trusted `RenderInput`, but exact-main registers no Surface 10 Component Blueprints and no component renderers. V1 therefore defines the smallest safe component catalog and renderer registration contract needed before any Dashboard Widgets runtime path may invoke the shared renderer.

This contract does not itself execute a renderer and does not add WordPress Dashboard hooks.

## 2. Canonical shared services

Surface 10 MUST reuse the services installed by `RenderingServiceRegistrar`:

- `platform.components.blueprints` → concrete `ComponentBlueprintRegistry`;
- `platform.renderer` → concrete `BlueprintRendererDispatcher`.

The implementation may use the concrete classes for their existing `register()` methods. It MUST NOT change shared Platform interfaces/classes or introduce a private registry/dispatcher.

Registration occurs from `DashboardWidgetsModule::register()`, after shared rendering services are installed by the existing bootstrap.

If either canonical service is missing or has the wrong concrete type, module registration fails closed.

## 3. V1 design rule

Repository evidence proves the seven trusted content-class identities but does not define optional field-level schemas for each class.

V1 therefore introduces only a **minimal required renderer-baseline schema** for each trusted class. Optional CTA, trend, dismiss, provider, query, remote, rich asset, interactive and other product fields are not silently invented.

Because shared Blueprint V1 has no optional/nullable binding metadata, every binding listed below is required.

## 4. Canonical Blueprint catalog

All V1 Blueprints:

- owner surface id: `10`;
- revision: `1`;
- `dependencyIds: []`;
- `assetHandles: []`.

| Trusted class | Component type | Blueprint UUID | Exact V1 binding schema |
|---|---|---|---|
| `rich_text` | `dashboard-widgets.rich-text` | `31000000-0000-4000-8000-000000000001` | `{"content":"string"}` |
| `kpi` | `dashboard-widgets.kpi` | `31000000-0000-4000-8000-000000000002` | `{"label":"string","value":"string"}` |
| `chart` | `dashboard-widgets.chart` | `31000000-0000-4000-8000-000000000003` | `{"labels":"string_list","values":"int_list"}` |
| `quick_links` | `dashboard-widgets.quick-links` | `31000000-0000-4000-8000-000000000004` | `{"labels":"string_list","urls":"string_list"}` |
| `announcement` | `dashboard-widgets.announcement` | `31000000-0000-4000-8000-000000000005` | `{"title":"string","text":"string"}` |
| `support_onboarding` | `dashboard-widgets.support-onboarding` | `31000000-0000-4000-8000-000000000006` | `{"title":"string","text":"string"}` |
| `icon_link` | `dashboard-widgets.icon-link` | `31000000-0000-4000-8000-000000000007` | `{"icon":"string","label":"string","url":"string"}` |

The UUIDs, revisions, component types and binding schemas are canonical V1 contract values. Later revisions must not silently mutate revision 1.

## 5. Rendering semantics

One module-local bounded renderer may be registered under all seven component types. The shared dispatcher remains the only dispatcher.

The renderer MUST validate the expected Blueprint id/revision and exact binding shape before emitting output.

### rich_text

V1 `content` is authored text data.

V1 renders it as escaped text only. It does not treat the value as trusted HTML. A later explicit sanitized-HTML profile may promote richer markup.

### kpi

V1 renders:

- escaped `label`;
- escaped `value`.

No trend/delta/provider semantics are implied.

### chart

V1 is a bounded integer-series baseline:

- `labels: string_list`;
- `values: int_list`;
- lists must have equal length;
- lists must be non-empty and contain at most 50 points.

Float series are deferred because shared Blueprint V1 currently has no `float_list` binding type.

The V1 renderer may emit an accessible semantic list/table representation; no JavaScript charting asset is required or authorized.

### quick_links

V1 requires:

- `labels: string_list`;
- `urls: string_list`;
- equal lengths;
- at least one and at most 20 links.

All labels are escaped.

Each URL must be either:

- an absolute `https://` URL; or
- a same-site root-relative path beginning with one `/`.

Reject:

- `http://`;
- `javascript:`;
- `data:`;
- scheme-relative `//...`;
- empty URLs;
- control characters.

### announcement

V1 requires escaped `title` and `text`.

CTA, severity, schedule and dismiss behavior remain separate later contracts.

### support_onboarding

V1 requires escaped `title` and `text`.

Buttons/actions/providers remain outside V1.

### icon_link

V1 requires:

- escaped `icon` token/text;
- escaped `label`;
- validated `url` using the same URL policy as quick links.

The icon value is data, not a CSS class/callback/script identifier.

## 6. Output safety

All authored text is untrusted input.

The renderer MUST context-escape text before HTML emission. It MUST NOT:

- concatenate raw authored HTML into privileged wp-admin output;
- execute shortcodes/blocks/PHP/JavaScript;
- trust `unfiltered_html`;
- treat icon text as an executable/class callback;
- output provider or remote response HTML.

Invalid input returns:

- `RenderOutput(success:false, html:'', assetHandles:[], failure:RenderFailureCode::InvalidInput)`.

Unexpected renderer exceptions remain normalized by the shared dispatcher to fail-closed `DependencyMismatch`.

## 7. Asset policy

V1 registers no Dashboard Widgets assets.

Every V1 Blueprint declares `assetHandles: []` and the renderer returns `assetHandles: []`.

CSS/JS/chart packages and optional module assets require a later explicit Asset Registry contract.

## 8. Registration lifecycle

The later source tranche creates a module-local component registrar that:

1. receives the concrete shared `ComponentBlueprintRegistry`;
2. receives the concrete shared `BlueprintRendererDispatcher`;
3. creates the seven exact revision-1 Blueprint descriptors;
4. registers every Blueprint;
5. registers one bounded renderer under all seven exact component types.

Duplicate Blueprint/component registrations fail closed through existing shared registry/dispatcher behavior.

No renderer is called during registration.

## 9. Relationship to render-source compiler

The existing `DashboardWidgetRenderSourceCompiler` remains authoritative for Definition → `RenderInput` projection.

Definitions must reference one of the exact Surface 10 Blueprint UUID/revision pairs above and provide binding envelopes matching that Blueprint's exact schema.

Content-class and render-source consistency must be enforced by the later component tranche: a `widget.type` must map to its corresponding canonical Blueprint, not another trusted class's Blueprint.

## 10. Explicitly still blocked

This contract does not authorize:

- a Dashboard Widgets runtime call to `RendererInterface::render()`;
- WordPress Dashboard registration hooks;
- `wp_add_dashboard_widget`;
- provider/query/source execution;
- Safe HTTP/remote/iframe execution;
- shortcode/block/action execution;
- Definition or user-preference mutation;
- shared Platform source changes;
- asset registration;
- full-parity runtime/product certification;
- deployment or release.

## 11. Next bounded source tranche

Issue **#1170 — Dashboard Widgets: trusted component blueprint registrar + renderer V1** is the only dependency-gated next source tranche.

Authorized source scope is limited to:

- module-local Blueprint catalog;
- module-local bounded renderer;
- module-local registrar;
- `DashboardWidgetsModule` wiring;
- focused unit tests.

The source tranche registers renderers but still may not invoke them from Dashboard Widgets runtime.

## 12. Promotion verdict

When this contract merges with exact-head Governance/Architecture green, zero unresolved review threads and no scope widening, promote:

`READY_FOR_TRUSTED_COMPONENT_BLUEPRINT_REGISTRAR_RENDERER_V1`
