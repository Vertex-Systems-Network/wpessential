# Admin Columns Fields Inline Edit UI V1

Status: **Gate D bounded implementation tranche — not Gate D PASS**

Issue: #232  
Parent: #66

## Purpose

This tranche connects the already-certified Admin Columns → Fields single-value mutation Ability/AJAX seam to the Admin Columns browser without widening Query reads, exposing hidden row identity, adding bulk mutation, or bypassing the Fields owner.

## Server capability projection

`AdminColumnsAdminController` continues to project the existing list/get/save/status/read routes. It now attempts to create a nonce for `admin-columns.write.field-value` as an **optional** route.

If the Fields write service was not admitted and the route is therefore not registered, `AjaxDispatcher::createNonce()` rejects the unknown type and the controller omits `routes.writeFieldValue`. No browser mutation capability is inferred from source discovery alone.

## Browser preconditions

A cell edit control is rendered only when all of the following are true:

1. the optional `admin-columns.write.field-value` bootstrap route exists with a nonce;
2. the active View has been saved and has no unsaved authored changes;
3. the saved View is `published` and enabled;
4. exactly one enabled authored native/Query column resolves to the explicit `post.id` reference;
5. every row on the current preview page has a unique positive integer in that explicit `post.id` column;
6. the target cell belongs to a preview column whose certified owner is `fields`;
7. the authored source still resolves to the same certified Fields source/format;
8. the original server bootstrap contains fail-closed Fields `ownerMetadata` with a positive `groupRevision`, field UUID, scalar logical type, native-post-meta storage owner, and an allowed post-type list containing the current View target;
9. the display format is one of the bounded V1 editor formats: `text`, `number`, `boolean`, or `date`.

If any condition fails, the preview remains read-only.

## Row identity rule

V1 deliberately does **not** widen `AdminColumnsReadAdapter::CONTRACT_VERSION` and does not return internal post IDs as hidden metadata. An author who wants inline editing must explicitly include the canonical `post.id` Query/native column in the saved View.

The browser uses that visible saved projection only after strict positive-integer and per-page uniqueness checks. Bulk or cross-page identity is outside this tranche.

## Mutation request

The browser sends exactly:

```text
view_id
column_key
post_id
expected_group_revision
value
```

through `admin-columns.write.field-value`.

No direct post-meta write, Fields repository access, Query mutation, or alternative WordPress write API is introduced.

## Response verification

Before invalidating the preview, the browser verifies both the Admin Columns adapter envelope and the nested Fields owner evidence:

- contract version;
- View id and unchanged View revision;
- column key;
- `source_owner=fields`;
- exact canonical Fields reference;
- expected Field Group revision and field UUID;
- logical type and `native_post_meta` storage owner;
- post id and post type;
- bounded status and boolean changed marker;
- presence of the owner-returned value.

A malformed, stale, denied, or otherwise failed response does not replace the current preview with local optimistic data.

## Successful-write behavior

A verified write does not patch the cell in place. The entire preview is invalidated and hidden, and the operator must choose **Preview rows** again. This ensures the next visible value comes from the Query + Fields read owners rather than from local mutation assumptions.

## Unsaved authored changes

Changes to the View name, target, column label/source/format/visibility, add/remove, or reordering mark the browser session dirty and invalidate any active preview. Preview and mutation are blocked until the View definition is saved again.

## Explicit non-scope

- bulk edit;
- native/query/taxonomy/relations/media/status/provider mutations;
- hidden row-id projection or read-contract V2;
- CSV/download/export orchestration;
- personal preferences/effective-state persistence;
- public REST mutation;
- production deployment;
- Gate D PASS or product-parity certification.

## Next gates

After exact-head CI and review promote this tranche, bounded bulk mutation may be designed from the proven one-row owner contract. Export work remains separately serialized behind the conflict-free CSV security encoder foundation in #233 and a later authorized orchestration contract.
