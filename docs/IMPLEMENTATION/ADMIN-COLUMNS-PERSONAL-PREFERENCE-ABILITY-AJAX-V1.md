# Admin Columns Personal Preference Ability/AJAX V1

Status: implementation candidate under Gate D. Parent: #66. Work item: #271.

## Boundary

This tranche exposes the already-promoted personal preference resolver/persistence through the canonical Admin Columns Ability/AJAX platform. It does not add browser preference UI, shared View mutation, Query execution, peer-owner writes, REST transport, bulk editing, Gate E, Status runtime or deployment behavior.

## Canonical services

`AdminColumnsModule` registers `module.admin-columns.personal-preferences` as one `AdminColumnsPersonalPreferenceStore` backed by the promoted `AdminColumnsPersonalPreferenceResolver`.

The caller-facing contract is:

- Ability: `wpessential/admin-columns/personal-preference`
- AJAX type: `admin-columns.personal.preference`
- capability: existing Admin Columns `manage_options`
- channels: Internal and UI only
- nonce operation: `Update`

The handler supports only `load`, `save` and `reset`.

## Identity and authorization invariants

Caller input never contains `user_id`. The handler derives the user exclusively from `ExecutionContext->principal`, requires an authenticated `actorType=user` principal, and fails closed otherwise. The shared Ability platform remains responsible for capability/Policy admission and WordPress execution-context construction.

## View/revision invariants

Every request requires:

- lowercase RFC 4122 `view_id`;
- positive `expected_view_revision`.

The handler resolves the canonical View through `AdminColumnsViewDefinitionService`, compares the exact persisted revision, and derives its Column state from the authoritative normalized View payload. Callers cannot inject a replacement Column catalog.

Stale View revisions fail before any personal state is returned or mutated.

## Action semantics

### load

Accepts no preference payload. Returns the current user's revision-bound personal state when valid, otherwise the store's canonical default/not-found behavior.

### save

Requires one object/map `preference`. Validation and canonicalization remain owned by `AdminColumnsPersonalPreferenceResolver`; persistence remains owned by `AdminColumnsPersonalPreferenceStore`.

### reset

Accepts no preference payload. Deletes only the current principal's state for the requested View and then returns canonical defaults for the same authoritative revision.

## Explicit non-scope

No arbitrary/cross-user preference access, no shared authored View changes, no browser preference controls, no AdminColumnsAdminController or TypeScript changes, no Query/Fields/Relations production changes, no CSV/View import transport, no bulk mutation and no Gate D PASS claim.
