# Query Fields Predicate Resolution V1

Status: implementation candidate for Query Gate C / issue #177 on the direct current-main PR #189. Packaging fixture #181, canonical Query admin integration #184, and repaired Admin Columns contract/UX PR #185 are already merged. This tranche does not independently claim Gate C PASS; final Gate C closure still requires exact-main evidence reconciliation after this PR merges.

## Ownership boundary

Query does not read Field storage, infer post-meta keys, emit `meta_query`, or duplicate Surface 3 normalization/authorization semantics. The only owner seam is `FieldQueryConsumerInterface` exposed as `module.custom-fields.query-consumer`.

The accepted owner contract is bounded to 100 candidate post ids and 100 result ids. Query V1 deliberately consumes only the certified native scalar owner shape: logical types `string`, `boolean`, `integer`, or `number`, `storage_owner=native_post_meta`, and an owner-advertised supported operator such as the current `eq`, `neq`, `in`, and `not_in` set. Future/non-native storage modes or non-scalar logical types are not implicitly enabled by this resolver.

## Validation path

When the Fields consumer is present at Query registration time, `module.query.validator` becomes `QueryFieldAwareAstValidator`. It does not replace canonical Query validation. Instead it:

1. identifies typed `field` predicate references in the raw AST;
2. requires an execution context;
3. asks the Fields owner to `describe()` each referenced Field;
4. verifies contract version, echoed Field reference, certified scalar logical type, operator capabilities, bounds, and exact `native_post_meta` storage-owner metadata;
5. creates a read-only projected Data Source descriptor containing only those owner-described Field references;
6. blocks those owner references from projection, sorting, or non-Field predicates in this V1 boundary;
7. delegates the complete AST validation to the existing `QueryAstValidator`.

If owner metadata is unavailable, malformed, over-broad, non-scalar, or backed by an unaccepted storage owner, validation fails closed. Non-Field queries continue through the unmodified canonical validator path.

## Runtime planning path

`QueryFieldPredicateResolver` runs only after canonical Data Source Policy authorization inside `QueryAuthorizedPlanner`.

V1 requires:

- a root `AND` group;
- exactly one direct `field` predicate;
- exactly one explicit finite `post.id eq` or `post.id in` anchor predicate;
- no nested Field predicates;
- unique positive anchor ids;
- no more than the owner contract candidate bound;
- compatible owner contract version, echoed reference, supported operator and bounds;
- a certified scalar logical type and exact `storage_owner=native_post_meta` descriptor before any matching call.

The resolver calls `matchingPostIds()` with the finite candidate set. Returned ids must be a unique positive subset of that exact set. A successful result rewrites the Field predicate into a canonical bounded `post.id in` predicate before native WordPress compilation. An empty owner result uses the existing authorized short-circuit result and avoids provider execution.

Relations resolution may run first. If an earlier Relations resolver has already proven a root-AND query empty, the Field resolver does not invoke the Fields owner unnecessarily. It still validates local Field syntax and the finite anchor requirement, strips the unresolved Field predicate so the native compiler never sees it, and preserves the short-circuit result.

## Security and fail-closed rules

- canonical Query Policy authorization executes before owner resolution;
- Fields owner remains responsible for Field definition/storage validation, value normalization, target resolution and per-post read authorization;
- Query accepts only the certified native scalar owner descriptor described above;
- Query never accepts owner results outside the authorized finite candidate set;
- malformed, duplicate, over-limit, over-broad, or foreign owner results fail planning;
- missing/wrong optional Fields service fails registration before Query source/service mutation;
- owner Field references are filter-only in this bounded V1 and cannot become projection/sort/provider shortcuts;
- no REST, AJAX, CLI, workflow, AI, or admin execution endpoint is introduced;
- no arbitrary Field/provider storage mode is enabled;
- no unbounded scan is permitted.

## Test evidence in this tranche

- `QueryFieldAwareAstValidatorTest`: owner-described Field refs are projected into canonical validation; projection/sort/non-Field escapes are blocked; malformed, non-scalar, and non-native-storage owner descriptors fail closed.
- `QueryFieldPredicateResolverTest`: deterministic narrowing, empty short-circuit, missing/over-budget anchor enforcement, contract/bounds/storage/type mismatch, owner exception, and duplicate/foreign/over-limit result rejection.
- `QueryFieldAuthorizedPlannerTest`: Policy-before-owner ordering, denial boundary, native narrowing, provider short-circuit.
- `QueryFieldRelationShortCircuitTest`: Relations-proven-empty queries skip downstream Fields owner calls without leaking unresolved Field predicates into native compilation.
- `QueryModuleFieldsWiringTest`: exact service wiring and malformed optional dependency fail-closed behavior.

## Current promotion sequence

- #181 packaging-fixture correction — merged.
- #184 canonical Query admin route/bootstrap/build integration — merged; #162 completed.
- #185 repaired Admin Columns Atomic + UX contracts — merged independently with no Query production-file overlap.
- #189 — sole active Query Fields-predicate implementation writer on the authoritative current-main lineage; require applicable exact-head CI and clean review/thread state before squash merge.

After #189 is promoted, re-run the Query Gate C closure audit against exact current `main`, then synchronize `CHECKPOINT.md` and parent #66. Gate C remains unclaimed until that reconciliation finds no remaining baseline blocker.
