# Fields Query Consumer Contract V1

Status: bounded Surface 3 public read contract for later Surface 6 Query integration.

## Public seam

`FieldQueryConsumerInterface` is implemented by the Fields-owned `FieldQueryConsumer` and registered as `module.custom-fields.query-consumer` when the admitted Fields module registers.

Stable field references use `fields.<field-group-uuid>.<field-uuid>`. Query consumers receive logical type and supported operator metadata, but no post-meta key or other storage-owned identifier.

## Certified V1 boundary

- published top-level Fields only;
- Field Group storage must be certified `native_post_meta`;
- only single scalar `string`, `boolean`, `integer`, and `number` storage shapes;
- operators are `eq`, `neq`, `in`, and `not_in`;
- at most 100 unique positive candidate post IDs per call;
- at most 100 returned IDs per call;
- set operands are finite and canonicalized through the existing Field value normalizer.

The implementation reuses the canonical Field Group normalizer/storage projection, `FieldValueTargetResolver`, `PostMetaRegistrationCompiler`, `PostMetaValueStore`, and `WordPressPostResourceAuthorizer`. It does not issue raw SQL, expose post-meta keys, or let Query infer Fields storage semantics.

## Authorization and failure behavior

The caller must provide an authenticated user `ExecutionContext`. Every candidate post is checked through the existing Fields read-authorization boundary before its value is read. Unknown or unpublished definitions, unsupported storage or field shapes, malformed references, unauthorized candidates, duplicate/oversized candidate lists, invalid operators, and invalid operands fail closed. An authorized candidate that is outside the published Field Group target is a deterministic non-match, not a contract failure.

Authorization is checked before target evaluation for every candidate. An authorization failure therefore fails the complete consumer call rather than silently dropping rows, preventing partial-result behavior from becoming an existence oracle.

## Explicit non-goals

This tranche does not wire Field predicates into the Query compiler/planner/executor, does not introduce direct `meta_query` compilation, does not support complex/container/provider-owned Fields, and does not change Query admin or public execution exposure. Those remain separately dependency-gated Surface 6 work.
