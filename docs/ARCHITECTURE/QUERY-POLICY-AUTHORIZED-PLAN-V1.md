# Query Policy-Authorized Planning V1

Status: bounded Surface 6 Gate C orchestration slice; provider execution remains deferred.

## Purpose

This slice establishes the mandatory authorization-before-provider boundary between an already validated `QueryDefinition` and an already certified provider compiler.

It produces an immutable `AuthorizedQueryPlan`; it does not execute the provider, query WordPress, access a database, cache results, or expose a public execution route.

## Required order

`QueryAuthorizedPlanner` enforces this sequence on every call:

1. resolve the Query `source_ref` again through canonical `DataSourceRegistryInterface`;
2. verify source type and capability version still match the validated definition;
3. verify the Data Source is currently available;
4. require its shared `DataSourceAuthorizationMapping`;
5. construct canonical `AuthorizationRequest` from the caller `ExecutionContext` plus mapped Ability/capability/resource type;
6. invoke shared `PolicyEngine`;
7. stop on denial or indeterminate Policy failure;
8. only after Policy allow, verify the selected `QueryProviderCompilerInterface` supports the definition;
9. compile the provider plan;
10. verify the compiler did not return a plan for another Data Source;
11. return `AuthorizedQueryPlan`.

Provider compilation therefore cannot occur before canonical Policy allow in this orchestration path.

## Fail-closed conditions

The planner rejects:

- unknown Data Source;
- source type/capability-version drift after validation;
- degraded Data Source;
- legacy/unmapped Data Source when execution planning is requested;
- unauthenticated or capability-denied Policy context;
- Policy evaluation failure;
- unsupported compiler;
- unexpected provider compilation failure;
- provider plan source mismatch.

Existing `QueryProviderCompilationException` remains authoritative for compiler-level semantic rejection and is not weakened or approximated by the planner.

## Ownership and security boundary

The planner consumes canonical shared owners rather than duplicating them:

- source identity/capabilities: shared Data Source Registry;
- authorization metadata: shared Data Source authorization mapping;
- principal/site/channel context: shared `ExecutionContext`;
- authorization decision: shared `PolicyEngine` / `AuthorizationRequest`;
- provider semantic translation: Query-owned certified compiler.

This slice MUST NOT:

- instantiate or execute `WP_Query`;
- access `$wpdb`, SQL, physical table/column names, query-clause filters or PHP callbacks;
- grant capabilities or invent a Query-private authorization engine;
- persist or cache authorization decisions;
- execute Relations traversal;
- expose REST/admin/Ability execution;
- claim Query Gate C completion or runtime certification.

## Authorized plan value

`AuthorizedQueryPlan` contains only:

- the already-normalized `QueryProviderPlan`;
- mapped canonical Ability;
- mapped WordPress capability;
- optional semantic resource type;
- safe canonical Policy reason.

It contains no credentials, raw SQL, principal secret, provider result rows, cache payload, or executable callback.

## Verification

Unit coverage proves:

- a canonical allow decision precedes successful provider compilation;
- Policy denial prevents even compiler support/compile calls;
- unauthenticated context stops before capability/provider work;
- missing mapping fails before Policy;
- unknown, mismatched and degraded sources fail before Policy;
- unsupported compiler is evaluated only after Policy allow;
- a compiler cannot redirect the authorized plan to another source.

The next actual provider-execution tranche must consume this authorized planning boundary rather than bypass it, and must separately certify native source registration, row/projection/scope authorization semantics and real WordPress reference behavior.
