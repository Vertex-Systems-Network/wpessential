# Admin Columns Fields Bulk Edit V1

Status: **Gate D bounded implementation tranche — not Gate D PASS**

Issue: #280  
Parent: #66

## Purpose

This tranche extends the promoted one-row Admin Columns → Fields browser mutation path into a bounded visible-row bulk editing workflow without introducing a second mutation API, storage path, authorization engine, hidden row identity contract, or unbounded select-all behavior.

## Reused authoritative mutation seam

Bulk V1 deliberately reuses the already-certified `admin-columns.write.field-value` route once per selected row. Every request therefore retains the existing server-side contract:

- authoritative saved View lookup;
- published + enabled View requirement;
- exact Fields-owned Column resolution;
- Query-owned target-row verification for the requested `post.id` and View post type;
- per-resource Policy/Fields owner authorization;
- expected Field Group revision conflict detection;
- Fields normalization/persistence/verification truth;
- exact bounded owner evidence returned to the browser.

No batch storage primitive or direct WordPress post-meta write is added.

## Selection boundary

Bulk V1 is page-local and explicit:

- the current preview must expose exactly one enabled native/Query `post.id` Column;
- every visible preview row must resolve to a unique positive post id;
- the operator selects individual visible rows with checkboxes;
- at most **20** rows may be selected for one operation;
- there is no cross-page selection and no implicit “all matching” mutation scope;
- selection disappears whenever preview state is invalidated or reloaded.

This keeps row identity inside the already-certified visible saved projection and does not widen the read contract.

## Column and value boundary

One Fields-owned scalar Column is edited per operation. A Column is eligible only when the same conditions used by one-row inline edit remain true:

- optional Fields write route is present;
- saved View identity/revision is current;
- View is published, enabled and locally non-dirty;
- preview/source owner is `fields`;
- authored source reference/format matches preview metadata;
- server bootstrap contains valid owner metadata and the current View target is allowed;
- format is bounded to `text`, `number`, `boolean`, or `date`.

The bulk value editor remains type-aware and validates finite numeric, explicit boolean, ISO `YYYY-MM-DD` date, and bounded text inputs before any request is sent.

## Execution and partial failures

Selected rows are executed sequentially through the existing one-row route. For each attempted row the browser validates the complete owner response with the same `parseFieldWriteResult()` contract used by one-row editing.

A failed row does not make previous successful owner writes disappear and is never misreported as success. The operator receives a bounded summary containing:

- verified success count;
- failed post ids;
- any post ids not attempted because the saved View identity/revision became stale or authored state became dirty during the operation.

No atomic rollback claim is made. Owner writes already verified as successful remain owner truth.

## Preview invalidation

If at least one row mutation request was attempted, the entire preview is invalidated after the batch completes, regardless of whether the outcome was all-success, partial failure, or all-failure. The operator must choose **Preview rows** again before seeing authoritative current values.

This prevents optimistic local patching and ensures visible state comes back through Query + source-owner reads.

## Explicit non-scope

- other-owner bulk mutation;
- direct post-meta/SQL/Fields-private writes;
- hidden row-id projection or read-contract V2;
- cross-page/all-matching mass mutation;
- background jobs or asynchronous orchestration;
- rollback/transaction claims across rows;
- export/import/preferences/row-action changes;
- production deployment;
- Gate D PASS or product-parity certification.

## Next gate

After exact-head CI and review promote this bounded bulk tranche, the remaining Gate D critical path is exact-main composed reference/closure evidence followed by Supervisor shared-truth reconciliation. Gate E and Status remain blocked until that closure decision is evidence-backed.
