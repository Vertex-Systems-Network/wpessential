# Surface 3 — Registered Post Meta Storage V1

Status: **IMPLEMENTED CANDIDATE / CI REQUIRED**  
Owner: **Surface 3 — Custom Fields**  
Scope: module-local registered post-meta compilation and WordPress registration boundary.

## Purpose

This slice turns persisted, normalized Field definitions into a bounded WordPress registered-meta contract without activating Custom Fields globally and without creating a second storage engine.

The stable per-Field UUID introduced before this slice is mandatory input. The editable Field machine key remains the current post-meta key, while the UUID is retained as durable identity for later rename/migration/conflict workflows.

## Native WordPress contract

The implementation follows current WordPress registered-meta behavior:

- `register_post_meta()` is the subtype-specific wrapper around `register_meta( 'post', ... )`;
- registered types are limited to WordPress-supported JSON/meta types;
- array meta exposed through REST carries an explicit `show_in_rest.schema.items` contract;
- non-single scalar meta remains a scalar registered type with `single=false`; WordPress REST wraps that field as an array response schema;
- explicit `auth_callback` checks object-level `edit_post` authority instead of relying on the permissive unprotected-meta fallback;
- explicit sanitization delegates to the canonical Surface 3 `FieldValueNormalizer` and rejects invalid values rather than silently coercing arbitrary blobs;
- revision-enabled meta is rejected before registration when the target post subtype does not support revisions;
- REST-visible meta is rejected before registration when the target post subtype does not support `custom-fields`.

Primary references:

- https://developer.wordpress.org/reference/functions/register_post_meta/
- https://developer.wordpress.org/reference/functions/register_meta/
- https://developer.wordpress.org/reference/classes/wp_rest_meta_fields/get_registered_fields/
- https://developer.wordpress.org/reference/functions/update_post_meta/

## Certified V1 field tranche

Scalar registered-meta compilation currently supports:

- Text
- Textarea
- Email
- URL
- Date
- Time
- DateTime
- Color
- Phone
- Number / Range (`integer` when explicitly configured, otherwise `number`)
- True/False / Switcher / Checkbox
- Image / File / Media / Video / Post Object integer references

Native list compilation currently supports:

- Gallery
- Files (`file_advanced`)
- Posts

Common repeatable scalar fields compile deliberately as either:

- one `array` meta value with explicit REST item schema; or
- multiple scalar meta rows when `clone_as_multiple` / canonical `store_as_multiple` is enabled.

The latter is a registration contract only in this slice. Atomic replacement/read-write workflow behavior belongs to the following value persistence / Ability slice.

## Fail-closed exclusions

V1 intentionally does **not** compile the following to ordinary registered post meta:

- Password / secret references;
- Hidden `mixed` values;
- choice types whose item schema may be heterogeneous until their canonical scalar schema is made explicit;
- Group / Repeater / Flexible Content / Clone structured values;
- WYSIWYG / Block / Code / Markdown content until their output and persistence policies are certified;
- Taxonomy/User polymorphic single-or-list values until their cardinality schema is explicit;
- Relationship values, which remain Relations Engine-owned;
- provider-backed/custom-table values;
- arbitrary objects, serialized provider blobs, or executable code.

Unsupported values throw and do not fall back to opportunistic serialization.

## Registration versus mutation

`PostMetaRegistrationCompiler` is pure module logic. `WordPressPostMetaRegistrar` is the thin WordPress boundary and is dependency-injectable for tests.

The Fields module registers these as module-local services but does not automatically register values during `boot()`. Target/location binding is not yet certified, and the shared canonical Pro entitlement/module-enable gate is still unresolved. This slice therefore does not bypass activation ownership.

No field value write Ability, migration, destructive replacement, admin renderer, or automatic runtime activation is claimed here.

## Persistence boundary note

WordPress post-meta writes historically unslash incoming keys/values. The following value persistence slice must normalize first and apply the WordPress slashing boundary (`wp_slash()` or an equivalent tested adapter) immediately before native metadata writes. It must not double-escape reads or store pre-serialized arbitrary blobs.

Multiple-row replacement must also define failure recovery before it is exposed as a mutating product path; delete-then-partial-add is not accepted as an implicit success strategy.

## Verification target

Applicable exact-head CI must pass before merge:

- Architecture Guards
- PHP Quality Toolchain
- Platform Compatibility Matrix
- Distributable Package

Unit coverage in this slice verifies scalar/list schema compilation, stable-identity requirement, explicit REST schemas, sanitization, repeatable storage shape, revision/custom-fields support guards, WordPress registration failures, and fail-closed unsupported types.

## Next bounded slice

After exact-head merge certification:

1. implement normalized value read/write persistence with WordPress slashing semantics and safe multi-row replacement/recovery;
2. expose read/write through typed Abilities with resource authorization;
3. bind published Field Groups to certified post-type/location targets;
4. only then add admin render/editor integration.
