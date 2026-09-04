# Admin Columns — Reopen Saved View V1

## Scope

Surface 8 now allows an administrator to list and reopen an already-saved canonical Admin Columns View definition in the existing editor after a page reload.

The implementation uses only the already-certified shared Ability/AJAX routes:

- `admin-columns.list.views`
- `admin-columns.get.view`
- `admin-columns.save.view`

No private AJAX endpoint or REST exposure is introduced.

## Hydration contract

A saved View is not applied to the editor until the complete GET response has passed bounded client validation.

The response must retain canonical Surface 8 ownership and definition type, a valid UUID, positive revision, supported lifecycle status, bounded View key/name, compatible target, 1–100 unique Columns, valid Column UUID/key/label/enabled/source/format/primary state, and current bootstrap compatibility for every target/source/format reference.

Optional canonical assignment/layout/visibility and Column layout metadata are retained as bounded opaque canonical metadata so reopening and saving a View does not silently erase server-owned normalized state that this V1 editor does not directly expose.

Unknown or malformed payload keys fail closed.

## Existing editor state

Hydration reuses the existing scaffold and its existing input/change/add/remove handlers. It does not create a second authored-state model.

The runtime retains only persistence identity needed for optimistic save:

- definition id;
- revision;
- immutable `view_key`;
- lifecycle status;
- View enabled state;
- canonical Column UUID and key identity;
- canonical metadata that the current UI does not edit directly.

A subsequent Save therefore updates the same definition with `expected_revision` instead of creating a duplicate View.

## Failure behavior

If list/get route metadata is absent or saved-View listing fails, the new unsaved draft and certified Save path remain usable.

A malformed, cross-surface, incompatible or stale GET response is rejected before hydration and receives bounded generic UI text. No Query execution, row/source-data mutation, inline/bulk editing or export occurs while listing or reopening Views.

## Non-goals

This tranche does not add:

- Query execution;
- row/source-data mutation;
- inline or bulk editing;
- export/import;
- REST exposure;
- new persistence or migrations;
- Gate D product-parity or completion claims.
