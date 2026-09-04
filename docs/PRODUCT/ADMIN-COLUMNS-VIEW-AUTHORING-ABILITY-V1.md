# Admin Columns — View Authoring Ability/AJAX V1

Parent tracker: #66. Implementation issue: #204.

This tranche exposes revisioned **Admin Columns View-definition configuration** through the canonical shared Ability and AJAX route registries. It does not authorize or implement source-row mutation.

## Abilities

Surface 8 owns four admin-only abilities:

- `wpessential/admin-columns/list-views` — read;
- `wpessential/admin-columns/get-view` — read;
- `wpessential/admin-columns/save-view` — Definition repository mutation;
- `wpessential/admin-columns/status-view` — Definition lifecycle mutation.

All descriptors require `manage_options`, are limited to Internal/UI execution channels, and are deliberately not exposed to the REST channel.

Matching AJAX route types are registered through the shared route registry. Read operations use the Apply nonce operation; save/status use Update. No module-private `$_POST` dispatcher is introduced.

## Revision and ownership law

The ability handler delegates to the already accepted `AdminColumnsViewDefinitionService` and normalizer. Therefore:

- only Surface 8 `admin_columns_view` Definitions are visible;
- `view_key` remains immutable after creation;
- existing-view writes require positive `expected_revision` and fail on stale revision;
- status transitions require exact id + expected revision;
- canonical checksums/revisions are returned from the shared Definition repository path;
- foreign Definitions cannot be read or mutated through these abilities.

## Explicit non-goals

This is configuration authoring only. It does not:

- edit post/user/taxonomy/meta/relation/provider data;
- implement inline or bulk editing;
- execute Query requests;
- add export/import;
- add public REST endpoints;
- change Admin Columns read-adapter semantics;
- claim Gate D completion or product parity.

A later client-integration tranche may enable the existing disabled Save control against these abilities after exact-head acceptance. Source-owner row mutation remains a separate owner-certified lane.
