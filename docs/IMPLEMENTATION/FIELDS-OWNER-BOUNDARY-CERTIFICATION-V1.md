# Fields Owner-Boundary Certification V1

## Scope

This slice certifies the current Custom Fields runtime ownership boundary. It does not add a provider adapter, a Relations adapter, or a new persistence owner.

The certified runtime storage owner remains native WordPress post meta for the already-supported scalar/list tranche exposed by `PostMetaRegistrationCompiler`.

## Certified native reference exception

`post_object` and `posts` are intentionally part of the certified native post-meta tranche:

- `post_object` compiles as one integer reference.
- `posts` compiles as an integer list.

They are therefore not members of the fail-closed owner-bound matrix below.

## Fail-closed owner-bound matrix

Until a canonical owner contract is accepted and implemented, these catalog families must not silently compile to native WordPress post meta:

### Entity and provider ownership

- `relationship`
- `taxonomy`
- `user`
- `page_link`
- `nav_menu`
- `sidebar`

### Composition and container ownership

- `group`
- `repeater`
- `flexible_content`
- `clone`
- `accordion`
- `tab`

`FieldOwnerBoundaryCertificationTest` proves every matrix member is rejected by the registered post-meta compiler and that the value write path fails before any injected `update_post_meta`, `delete_post_meta`, or `add_post_meta` mutation boundary is reached.

## Ownership rule

A matching meta key is not ownership proof. Provider/complex storage may only be enabled after a canonical contract defines, at minimum:

1. the owning subsystem;
2. the canonical persisted shape and identity rules;
3. read/write/delete semantics;
4. collision and concurrency behavior;
5. migration and rollback behavior;
6. authorization and capability boundaries;
7. compatibility behavior across supported WordPress/PHP versions.

Until those conditions exist, the runtime must fail closed rather than infer or emulate ownership.

## Mutation guarantee

`PostMetaValueStore::write()` resolves its compiled storage context before any native mutation boundary. An unsupported owner-bound type therefore cannot reach WordPress post-meta mutation through this value path.

The certification test additionally injects mutation counters and requires all counters to remain zero for every owner-bound matrix member.

## Non-goals

- No Relations runtime implementation.
- No provider-specific adapter implementation.
- No ownership inference from existing metadata.
- No storage migration.
- No change to existing `post_object` / `posts` behavior.
- No production runtime behavior change in this certification slice.

## Evidence

Primary regression evidence:

- `tests/Unit/Modules/Fields/FieldOwnerBoundaryCertificationTest.php`
- `tests/Unit/Modules/Fields/PostMetaRegistrationCompilerTest.php`
- `tests/Unit/Modules/Fields/PostMetaValueStoreTest.php`

Promotion requires exact-head repository quality gates before this certification is treated as complete.
