# WP122 CPT ↔ Taxonomy Association Ownership

Status: implementation boundary clarification

## Decision

For the WP122 Taxonomy Builder linking UX, the canonical writable association is the Taxonomy Surface 2 definition field `object_types`.

The UI MUST NOT mirror the same user action into Custom Post Type Surface 1 `taxonomies`. Doing so would create two independently revisioned sources of truth for one association and would violate the no-parallel-owner/no-bypass architecture rules.

Surface 4 (`relations`) remains reserved for the separate structured content-relation product surface. WordPress taxonomy-to-object registration is not promoted into a Surface 4 business relation definition in this WP122 slice.

## Compatibility

- Taxonomy `object_types` may reference WordPress core post types, WPEssential-owned CPT runtime keys, or externally registered post type keys.
- Missing runtime object types remain a compatibility warning, not an automatic destructive unlink.
- Existing CPT `taxonomies` payload support remains backward-compatible for definitions created outside this UI, but the WP122 linking UI does not write it.
- No automatic migration, rename, deletion, or external registration takeover is authorized by this decision.

## UI contract

The Taxonomy Builder may discover known object types and present them as selectable association targets. Unknown/external keys already present in a definition must remain preservable and editable without being silently dropped.

A future CPT-side backlink view may read Taxonomy Surface 2 definitions through the canonical Taxonomy owner Ability, but must remain read-only unless a later architecture decision defines a different single writer.
