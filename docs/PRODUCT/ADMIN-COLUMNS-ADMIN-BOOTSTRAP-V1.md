# Admin Columns — Canonical Admin Bootstrap V1

Parent tracker: #66. Implementation issue: #202.

This tranche connects the existing Admin Columns authoring scaffold to a canonical WordPress admin page without creating a second Query engine or opening a write/export surface.

## Accepted inputs

- target metadata: actual WordPress post types returned from the `show_ui` object catalog;
- source metadata: the canonical `wordpress.posts` Data Source descriptor already owned by Query;
- frontend contract: the existing `admin-ui/src/columns.ts` parser/scaffold (`surface=columns`, `contractVersion=1`).

No raw SQL, `WP_Query`, provider arguments, private Field/Relation storage keys or callbacks are projected.

## Capability truth

The current Query/Data Source contract advertises source-level predicates and sort modes but does not expose a certified per-field capability matrix for Admin Columns. V1 therefore does not infer compiler-private behavior. Every projected target/source reports:

- `sort=false`
- `filter=false`
- `edit=false`
- `export=false`

A later owner-certified seam may widen exact capabilities. Absence of a capability is not approximated.

## Runtime shape

`AdminColumnsAdminBootstrapProjector` emits bounded deterministic target/source catalogs. The page controller:

- lives under the shared WPEssential admin parent;
- requires `manage_options`;
- loads only the `columns-runtime` asset entry on its own hook;
- emits JSON with HTML-sensitive characters hex escaped;
- exposes no form action, REST route or AJAX dispatcher.

`admin-ui/src/columns-runtime.ts` is intentionally isolated from the existing scaffold source. It only reads/validates the bootstrap script, mounts the existing scaffold, publishes the standard admin-ready event, and fails closed on malformed bootstrap data.

The package build adds this isolated runtime entry; it introduces no dependency or lock-file change.

## Explicit non-goals

This tranche does not implement persistence, revisioned save, inline/bulk editing, source-owner mutation, export, public execution preview, effective capability diagnostics, Fields/Relations private reads, or Gate D completion/product parity.

The existing client scaffold keeps Save disabled and continues to state that owner validation and persistence are unavailable.
