# Admin Columns Personal Preference Action Routes V1

Status: Gate D bounded implementation evidence. This does not mark Gate D PASS.

## Purpose

Refine the promoted current-user personal preference transport so read and mutation operations have structurally correct Ability/AJAX mutability and nonce semantics.

## Canonical operations

The transport exposes three fixed operations. The caller does not submit an `action` field.

- `wpessential/admin-columns/personal-preference/load` / `admin-columns.personal.preference.load`: non-mutating, `NonceOperation::Apply`.
- `wpessential/admin-columns/personal-preference/save` / `admin-columns.personal.preference.save`: mutating, `NonceOperation::Update`.
- `wpessential/admin-columns/personal-preference/reset` / `admin-columns.personal.preference.reset`: mutating, `NonceOperation::Update`.

Each route constructs `AdminColumnsPersonalPreferenceAbilityHandler` with a fixed operation. A caller therefore cannot reinterpret the read-only load route as save/reset by changing request data.

## Identity and authorization

The handler derives the user exclusively from `ExecutionContext->principal`. Caller input has no `user_id` field. The existing `manage_options`, AbilityRegistry, Policy, WordPressExecutionContextFactory, AbilityAjaxHandler, nonce and guest-disabled AJAX boundaries remain authoritative.

## View/revision contract

Every operation accepts only `view_id` and exact positive `expected_view_revision`; save additionally requires `preference`. The handler resolves the authoritative View through `AdminColumnsViewDefinitionService`, compares the exact revision, and projects only canonical column key/enabled information into the existing preference store/resolver.

Stale View revisions fail closed. Personal preference operations never mutate the shared authored View definition.

## Payload boundaries

Load and reset reject `preference`. All operations reject caller `action`, `user_id`, and unknown fields. Save continues to use the resolver/store allowlist and budgets for chosen View, temporary sort/filter state, hidden columns, density and saved filter state.

## Persistence ownership

`AdminColumnsPersonalPreferenceStore` remains the only persistence boundary for these routes. Load reads only the authenticated user's exact View preference. Save writes only that record. Reset deletes only that record and then returns the canonical empty/effective load result.

## Non-scope

- no browser preference controls or TypeScript/controller changes;
- no cross-user administration;
- no shared View mutation or cross-revision migration;
- no Query row execution or source-owner mutation;
- no REST transport;
- no Gate D PASS claim;
- no Gate E, Status runtime, deployment or release work.

## Tests

Focused tests prove fixed-operation handler behavior, current-user binding, stale-revision failure, caller action/user rejection, action-specific schemas, and distinct Apply/Update nonce registration. All applicable exact-head CI must be green before promotion.
