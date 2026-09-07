# Admin Columns Personal Preference Persistence V1

Status: **IMPLEMENTED FOUNDATION / GATE D STILL ACTIVE**  
Issue: #258  
Parent: #66

## Scope

This tranche persists the already-certified bounded personal preference contract without writing personal state into revisioned shared Admin Columns View definitions.

`AdminColumnsPersonalPreferenceStore` owns one private WordPress user-meta record per explicit `(user_id, view_id)` pair. Stored payloads are produced only from `AdminColumnsPersonalPreferenceResolver` output and are revalidated through that resolver on read.

## Stored envelope

Each record contains only:

- persistence contract version;
- stable View UUID;
- exact View revision at save time;
- resolver-allowlisted personal preference payload.

The meta key is deterministic and private, derived from the validated View UUID. No site/network shared option or View Definition payload is changed.

## Revision behavior

Personal state is bound to the exact shared View revision used when it was saved.

If the current View revision differs, V1 does **not** silently apply stale Column references. The store returns canonical default personal state with `applied=false` and `reason=view_revision_changed`, while retaining the stored revision as evidence.

A later reconciliation/migration UX may explicitly carry compatible preferences forward; that is outside this V1 boundary.

## Integrity behavior

- positive user identity is mandatory;
- View identity remains a lowercase RFC 4122 UUID;
- unknown/corrupt stored envelope keys fail closed;
- the stored preference is re-run through the bounded resolver before application;
- save performs exact read-back verification;
- reset verifies that only the exact View preference record is gone.

Production defaults use native `get_user_meta`, `update_user_meta`, and `delete_user_meta`. Injectable closures exist only so the persistence boundary can be tested without a live WordPress database.

## Ownership boundary

This store performs no Policy authorization. Callers must establish the authorized user context before invocation. It does not expose AJAX/REST/admin UI, execute Query, mutate Fields/Relations/other source owners, discover providers, or write shared View definitions.

## Non-scope

- admin/browser preference controls;
- Ability/AJAX/REST transport;
- cross-revision preference migration;
- network/site-wide personal state;
- authorization grants;
- Gate D PASS, Gate E start, Status runtime, product parity or deployment.
