# Dashboard Widgets — Trusted Render-Source Implementation Contract V1

Status: **implementation-ready planning contract / renderer execution NOT authorized**
Surface: **10 — Dashboard Widgets**
Issue: **#1160**
Exact base: `main@9c5e2143978ba6644abc03eafa1ef1522fcc53da`

## 1. Purpose

This contract closes the schema gap identified by Issue #1159 / PR #1161. It defines the smallest exact Dashboard Widget Definition payload needed for a later non-executing render-source compiler while reusing the shared Component Blueprint and rendering contracts.

This document does not execute a renderer and does not authorize WordPress Dashboard hooks.

## 2. Canonical shared contracts

Surface 10 MUST reuse:

- `ComponentBlueprintRegistryInterface::get(string $blueprintId, int $revision)`;
- `ComponentBlueprintDescriptor`;
- `RenderInput`;
- `RendererInterface`;
- `BlueprintRendererDispatcher`.

Surface 10 MUST NOT create a private Blueprint registry, private rendering engine, private asset registry or alternate RenderInput model.

## 3. Exact Definition payload

For every V1 trusted structured Dashboard Widget, the canonical authored payload is:

```json
{
  "widget": {
    "type": "rich_text",
    "render_source": {
      "kind": "component_blueprint",
      "blueprint_id": "22222222-2222-4222-8222-222222222222",
      "blueprint_revision": 2,
      "bindings": {
        "title": {
          "source": "literal",
          "value": "Quarterly summary"
        }
      }
    }
  }
}
```

The existing registration, visibility and content-class fields remain separately owned and validated.

### Required `render_source` keys

Exactly these keys are accepted in V1:

- `kind`
- `blueprint_id`
- `blueprint_revision`
- `bindings`

Unknown keys fail closed.

### `kind`

V1 accepts only:

`component_blueprint`

Provider, query, remote, iframe, shortcode, block, callback, class-name and arbitrary executable kinds are not accepted.

### `blueprint_id`

- required;
- canonical lowercase RFC 4122 UUID;
- resolved only through `ComponentBlueprintRegistryInterface`;
- never interpreted as a class, callback, file path, URL or provider id.

### `blueprint_revision`

- required positive integer;
- exact revision lookup only;
- no silent fallback to latest or nearest revision.

## 4. Blueprint ownership gate

The resolved `ComponentBlueprintDescriptor` MUST have:

`ownerSurfaceId === 10`

V1 intentionally rejects cross-surface Blueprints. A later cross-surface/provider tranche requires an explicit owner contract and authorization boundary.

The Definition does not author:

- component type;
- dependency ids;
- asset handles;
- binding schema.

Those values remain registry-owned facts from the resolved Blueprint.

## 5. Binding contract

`bindings` is an object/map with at most 128 entries.

Each binding key MUST use the same bounded semantic identifier grammar as shared `RenderInput` / Blueprint binding keys:

`^[a-z][a-z0-9_.-]{0,127}$`

Each V1 binding value is an envelope:

```json
{
  "source": "literal",
  "value": "example"
}
```

Exactly these keys are accepted in the V1 binding envelope:

- `source`
- `value`

### V1 binding source

Only `source: "literal"` is admitted.

These sources are explicitly deferred and fail closed:

- `context`
- `principal`
- `request`
- `query`
- `provider`
- `data_source`
- `remote`
- `token`
- `callback`
- any unrecognized value.

This prevents Surface 10 from executing cross-surface or provider-owned data while the provider/source authorization contract is still unpromoted.

## 6. Binding type compatibility

The resolved Blueprint `bindingSchema` is authoritative.

V1 supports exactly the shared Blueprint scalar families:

- `string`
- `int`
- `float`
- `bool`
- `string_list`
- `int_list`

The future compiler MUST:

1. resolve the Blueprint first;
2. require the authored binding-key set to exactly equal the Blueprint `bindingSchema` key set;
3. reject unknown authored binding keys;
4. reject missing Blueprint binding keys;
5. validate each literal value against the Blueprint-declared type;
6. reject mixed lists and unsupported nested/object values.

The exact-key rule is intentional because shared Blueprint V1 does not currently express optional-binding metadata. Optionality must not be invented by Surface 10.

Although shared `RenderInput` can technically carry `null`, this Surface 10 V1 contract does not admit authored null bindings because Blueprint V1 has no nullable type metadata.

## 7. RenderInput projection

A later bounded compiler may project a successfully validated render source into shared `RenderInput` as:

- `blueprintId` ← `render_source.blueprint_id`
- `blueprintRevision` ← `render_source.blueprint_revision`
- `bindings[key]` ← the validated literal `value` for each binding envelope

No additional authored fields may be injected into `RenderInput`.

The compiler may construct `RenderInput`; it may not call `RendererInterface::render()` in the descriptor/compiler tranche.

## 8. Content-class relationship

The existing trusted content-class allowlist remains authoritative:

- `rich_text`
- `kpi`
- `chart`
- `quick_links`
- `announcement`
- `support_onboarding`
- `icon_link`

V1 render-source compilation is valid only after the existing content-class compiler accepts the Definition.

The render-source contract does not promote:

- `iframe`
- `rss`
- `video`
- `listing`
- `activity`
- `form_action`
- `site_health`
- `shortcode`
- `block`
- `registered_provider`.

## 9. Fail-closed conditions

The later compiler MUST reject:

- missing `widget.render_source`;
- non-object render source;
- unknown render-source keys;
- unsupported `kind`;
- malformed/non-canonical Blueprint id;
- non-positive revision;
- missing Blueprint;
- Blueprint owned by a surface other than 10;
- non-object or oversized bindings;
- malformed binding keys;
- unknown/missing Blueprint binding keys;
- unsupported binding source;
- binding type mismatch;
- nested object payloads;
- executable authored channels rejected by shared `RenderInput`.

There is no silent default Blueprint, latest-revision fallback or unknown-key preservation at the execution boundary.

## 10. HTML and privileged admin safety

Literal strings are data, not trusted HTML.

A future component renderer remains responsible for context-appropriate escaping/sanitization. Surface 10 MUST NOT mark authored strings safe merely because they passed this compiler.

For URL-like component bindings:

- the Blueprint/component renderer owns URL semantics;
- executable schemes such as `javascript:` remain prohibited;
- external-link policy, rel/target behavior and allowlisting remain renderer/component concerns.

A visible Dashboard Widget remains presentation only. Visibility success never grants source-data access or action authorization.

## 11. Provider and cross-surface ownership

Provider/query/source execution remains outside V1.

If a future widget needs Query, Listings, Ledger, Forms, remote data or another owner surface:

1. the canonical owner must expose an approved typed public contract;
2. Surface 10 stores only stable references allowed by that contract;
3. authorization is re-applied at execution;
4. provider output becomes renderer input only through an explicitly promoted binding-source contract.

No provider credential, endpoint, SQL, PHP callback, class name or arbitrary function reference belongs in `widget.render_source`.

## 12. Atomic-option relationship

This implementation contract refines the already-reviewed Surface 10 policies:

- `dashboard-widgets.content.safety`
- `dashboard-widgets.sources.policy`
- `dashboard-widgets.providers.registry`
- `dashboard-widgets.types.policy`

It does not invent a nineteenth Atomic Option and does not change the authoritative 123-record / 18-atomic-option Bank coverage.

## 13. Acceptance examples

### Valid V1

A Surface 10-owned Blueprint with:

- id `22222222-2222-4222-8222-222222222222`;
- revision `2`;
- binding schema `{"title":"string","count":"int"}`;

accepts:

```json
{
  "kind": "component_blueprint",
  "blueprint_id": "22222222-2222-4222-8222-222222222222",
  "blueprint_revision": 2,
  "bindings": {
    "title": {"source": "literal", "value": "Orders"},
    "count": {"source": "literal", "value": 12}
  }
}
```

### Rejected V1 examples

Reject:

- missing `count` binding;
- extra `subtitle` binding;
- `count` supplied as string;
- `source: "provider"`;
- Blueprint owner surface 9;
- unknown Blueprint/revision;
- `kind: "iframe"`;
- arbitrary HTML/JS treated as pre-trusted executable output.

## 14. Next runtime tranche

Issue **#1162 — Dashboard Widgets: trusted render-source descriptor/compiler V1** is the only dependency-gated next source tranche.

It may add only the typed non-executing descriptor/compiler, module service wiring, registration fail-closed integration and focused tests required by this contract.

## 15. Explicitly still blocked

This contract does not authorize:

- `RendererInterface::render()`;
- HTML output;
- provider/query/source execution;
- remote/Safe HTTP/iframe execution;
- `wp_dashboard_setup`;
- `wp_network_dashboard_setup`;
- `wp_add_dashboard_widget`;
- Definition/user-preference mutation;
- full-parity runtime/product certification;
- deployment or release.

## 16. Promotion verdict

When this contract merges with exact-head Governance/Architecture green and no scope widening, the promoted verdict is:

`READY_FOR_TRUSTED_RENDER_SOURCE_DESCRIPTOR_COMPILER_V1`
