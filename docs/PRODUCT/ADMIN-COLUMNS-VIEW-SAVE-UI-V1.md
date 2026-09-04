# Admin Columns View Save UI V1

## Scope

This tranche connects the existing Admin Columns authoring scaffold to the already-accepted Surface 8 View-definition Ability/AJAX contract. It persists configuration only.

## Canonical write path

The browser uses the shared WordPress AJAX gateway and the registered `admin-columns.save.view` route. The server issues the route-scoped Update nonce only when the canonical shared dispatcher/gateway runtime is available. No Admin Columns-private AJAX endpoint is created.

First save creates a draft View definition. The client generates one stable `view_key`, stable RFC 4122 UUIDs for authored Columns, and serializes target/source identity only from the server bootstrap. The successful response supplies the canonical definition id and revision.

Subsequent saves preserve the immutable `view_key` and definition id and submit the last accepted positive `expected_revision`. Server-side optimistic concurrency remains authoritative.

## Failure boundary

If the shared AJAX runtime is absent, the server omits save-route metadata and the page remains non-saving/fail-closed. If the browser receives a malformed or unsuccessful response, no local revision is advanced and only a bounded generic failure message is shown.

A request in flight disables the save control so one browser instance cannot intentionally submit overlapping writes from the same accepted revision.

## Explicit non-goals

This tranche does not:

- execute Surface 6 Query;
- write post, user, taxonomy, media, relation or provider-owned data;
- enable inline or bulk editing;
- add result/CSV export;
- add public REST exposure;
- add persistence tables or migrations;
- change Admin Columns source capability truth;
- claim Gate D completion or product parity.

## Conflict containment

The existing `columns.ts` scaffold remains unchanged. Save behavior lives in the isolated `columns-runtime.ts` mount wrapper and maps the scaffold's stable local draft ids to stable authored Column UUIDs, keeping future row-mutation/editor work on separate paths and semantics.
