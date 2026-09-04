# Admin Columns Saved View Reopen V1

## Scope

This tranche lets the existing Admin Columns editor list and reopen canonical saved View definitions after a page reload. It reuses the already-accepted Surface 8 list/get/save Ability/AJAX routes and does not create a second persistence or authorization path.

## Loading contract

When the shared AJAX runtime is available, the server bootstrap exposes route-scoped nonces for:

- `admin-columns.list.views`;
- `admin-columns.get.view`;
- `admin-columns.save.view`.

The client bounds the displayed saved-View list to 100 entries and accepts only definitions owned by Surface 8 with the canonical `admin_columns_view` type, RFC 4122 id, positive revision, accepted lifecycle status and a valid canonical payload.

A loaded View must reference only targets, source owners/references and formats currently advertised by the server bootstrap. Duplicate Column UUIDs/keys, unavailable adapters and malformed definitions fail closed.

## Hydration and revision continuity

The runtime reuses the existing scaffold's add/remove/input/change handlers instead of introducing another editor state machine. The existing `columns.ts` scaffold remains unchanged.

On successful load, the runtime retains:

- canonical definition id;
- positive revision;
- lifecycle status;
- immutable `view_key`;
- canonical Column UUIDs and keys;
- primary flags and canonical Column payload extensions;
- canonical top-level payload extensions such as assignment/layout/visibility.

The next Save therefore updates the same definition with `expected_revision` rather than creating a duplicate View. Server-approved payload extensions not exposed as editable controls are preserved rather than silently dropped.

## Failure behavior

If list/get route metadata is unavailable, new-draft authoring and the previously certified Save behavior remain available when their route exists. A list/get request failure shows bounded generic text and does not create a private fallback endpoint.

Malformed fetched definitions are rejected before canonical identity/revision state is accepted. No raw provider/storage response is displayed.

## Explicit non-goals

This tranche does not execute Query, mutate post/user/taxonomy/media/relation/provider data, add inline or bulk editing, add result/CSV export, expose public REST, add migrations, widen source capabilities, or claim Gate D/product parity completion.
