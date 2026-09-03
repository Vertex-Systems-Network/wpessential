# Query Fields Predicate Resolution V1

Status: implementation candidate for Query Gate C / issue #177. This tranche is stacked on the reconciled Query admin writer (#182), which is itself serialized behind the packaging-fixture fix (#181). It does not independently claim Gate C PASS.

## Ownership boundary

Query does not read Field storage, infer post-meta keys, emit `meta_query`, or duplicate Surface 3 normalization/authorization semantics. The only owner seam is `FieldQueryConsumerInterface` exposed as `module.custom-fields.query-consumer`.

The accepted owner contract is bounded to 100 candidate post ids and 100 result ids and currently advertises `eq`, `neq`, `in`, and `not_in` for certified scalar native Field values.

## Validation path

When the Fields consumer is present at Query registration time, `module.query.validator` becomes `QueryFieldAwareAstValidator`. It does not replace canonical Query validation. Instead it:

1. identifies typed `field` predicate references in the raw AST;
2. requires an execution context;
3. asks the Fields owner to `describe()` each referenced Field;
4. verifies contract version, echoed Field reference, logical type, operator capabilities, bounds, and storage-owner metadata;
5. creates a read-only projected Data Source descriptor containing only those owner-described Field references;
6. blocks those owner references from projection, sorting, or non-Field predicates in this V1 boundary;
7. delegates the complete AST validation to the existing `QueryAstValidator`.

If owner metadata is unavailable or malformed, validation fails closed. Non-Field queries continue through the unmodified canonical validator path.

## Runtime planning path

`QueryFieldPredicateResolver` runs only after canonical Data Source Policy authorization inside `QueryAuthorizedPlanner`.

V1 requires:

- a root `AND` group;
- exactly one direct `field` predicate;
- exactly one explicit finite `post.id eq` or `post.id in` anchor predicate;
- no nested Field predicates;
- unique positive anchor ids;
- no more than the owner contract candidate bound;
- compatible owner contract version, echoed reference, supported operator and bounds.

The resolver calls `matchingPostIds()` with the finite candidate set. Returned ids must be a unique positive subset of that exact set. A successful result rewrites the Field predicate into a canonical bounded `post.id in` predicate before native WordPress compilation. An empty owner result uses the existing authorized short-circuit result and avoids provider execution.

## Security and fail-closed rules

- canonical Query Policy authorization executes before owner resolution;
- Fields owner remains responsible for Field definition/storage validation, value normalization, target resolution and per-post read authorization;
- Query never accepts owner results outside the authorized finite candidate set;
- malformed, duplicate, over-broad or foreign owner results fail planning;
- missing/wrong optional Fields service fails registration before Query source/service mutation;
- owner Field references are filter-only in this bounded V1 and cannot become projection/sort/provider shortcuts;
- no REST, AJAX, CLI, workflow, AI, or admin execution endpoint is introduced;
- no arbitrary Field/provider storage mode is enabled;
- no unbounded scan is permitted.

## Test evidence in this tranche

- `QueryFieldAwareAstValidatorTest`: owner-described Field refs are projected into canonical validation, filter-only ownership is enforced, and malformed owner descriptors fail closed.
- `QueryFieldPredicateResolverTest`: deterministic narrowing, empty short-circuit, finite-anchor enforcement, contract mismatch, duplicate/foreign result rejection.
- `QueryFieldAuthorizedPlannerTest`: Policy-before-owner ordering, denial boundary, native narrowing, provider short-circuit.
- `QueryModuleFieldsWiringTest`: exact service wiring and malformed optional dependency fail-closed behavior.

## Remaining Gate C closure

After #181, #182 and this stacked tranche are promoted in that order and reconciled onto exact current `main`, re-run the Query Gate C closure audit. Gate C must remain unclaimed until repository checkpoint/coordination truth is synchronized and no baseline criterion remains blocked.
