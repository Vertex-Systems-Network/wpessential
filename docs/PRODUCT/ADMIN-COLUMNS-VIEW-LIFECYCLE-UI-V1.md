# Admin Columns — Canonical View Lifecycle UI V1

## Scope

Surface 8 now exposes explicit lifecycle controls for the currently saved canonical Admin Columns View through the already-registered shared status Ability/AJAX route.

The lifecycle UI mutates only the revisioned View definition. It does not mutate row/source data, execute bulk or inline edits, export data, alter Query/Fields/Relations ownership, or introduce a REST/private endpoint.

## Shared route

The Admin Columns bootstrap projects the existing shared route only when the shared dispatcher/gateway is available:

- `admin-columns.status.view`

The client does not invent another persistence or mutation path. The canonical execution path remains:

`shared AJAX -> Ability/policy -> AdminColumnsViewAbilityHandler::STATUS -> AdminColumnsViewDefinitionService::changeStatus()`

## Explicit optimistic transition

Lifecycle mutation is available only when the editor session has:

- a canonical saved View UUID;
- a positive current revision;
- the immutable canonical `view_key`.

Unsaved Views cannot call the lifecycle route. Save remains the only create path.

The administrator explicitly selects one of the canonical states:

- `draft`;
- `published`;
- `disabled`;
- `archived`.

Applying the state sends exactly:

- `id`;
- `expected_revision`;
- `status`.

Selecting the current state is a local no-op and sends no request. There is no automatic publish/disable/archive behavior on page load, View reopen, Save, or preview.

## Response reconciliation

The local editor session is not changed until the complete returned definition validates:

- canonical Surface 8 definition header/type/owner constraints through the existing parser;
- exact original View UUID;
- revision exactly `expected_revision + 1`;
- exact requested lifecycle state;
- unchanged immutable `view_key`.

Only after those checks pass does the client replace its local revision/status.

Malformed, stale, conflicting, or failed responses preserve the previous local revision/status and return bounded generic feedback.

## Preview interaction

A successful lifecycle transition dispatches the existing saved-session change event. The bounded row-preview UI clears any previously rendered rows and requires a new explicit Preview request.

This is particularly important when moving between `published`, `draft`, `disabled`, or `archived`: rows rendered under the prior saved revision are never left presented as the active preview.

The server-side Admin Columns read adapter remains authoritative for the requirement that Query-backed row preview is available only for a published, enabled View.

## Accessibility

The lifecycle surface includes:

- a labelled native status selector;
- an explicit Apply button;
- a live status region for current state, progress, success, no-op, and failure feedback;
- disabled controls for an unsaved View or while a lifecycle request is in flight.

## Non-goals

This tranche does not add:

- lifecycle persistence semantics beyond the already-certified service;
- View enabled/disabled payload editing;
- inline or bulk row editing;
- row/source-data mutation;
- import/export;
- Query/filter/sort authoring;
- REST exposure;
- migrations;
- Gate D completion or product-parity claims.
