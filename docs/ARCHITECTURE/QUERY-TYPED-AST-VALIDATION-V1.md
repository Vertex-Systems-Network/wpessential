# Query Typed AST and Validation V1

Status: implementation slice for Surface 6 Query foundation.

## Scope

This slice implements the provider-neutral, non-executing Query AST boundary defined by the Query Implementation Contract V1.

It adds:

- finite typed predicate grammar;
- typed source, predicate, ordering, pagination, and definition values;
- explicit caller-supplied structural validation budgets;
- canonical Data Source registry resolution;
- public Relations Query-consumer validation for relation references;
- stable Query validation issue codes;
- fail-closed validation for unknown semantic nodes, malformed types, unsafe executable/raw-SQL payloads, unchecked field/source references, unsupported provider capabilities, and structural budget violations.

## Canonical ownership

Query owns semantic query definitions and validation only.

Source identity, fields, predicates, sorting, pagination capability, availability, and page limits are read from `DataSourceRegistryInterface` / `DataSourceDescriptor`. Query does not create a private source registry.

Relation references are validated only through `RelationQueryConsumerInterface`. Query does not import Relations-private storage or implementation classes.

## Security boundary

Authored Query AST cannot contain raw SQL, PHP/eval/callback payloads, unchecked table/column identifiers, credentials, or arbitrary endpoint values. Unknown semantic nodes fail closed rather than being dropped or approximated.

Validation never compiles or executes SQL and does not register REST/admin/Ability handlers.

## Budget boundary

Production thresholds are not invented by this slice. `QueryValidationBudget` requires the caller/integration layer to provide positive limits for:

- AST bytes;
- group depth;
- predicate count;
- IN-list size;
- page size;
- relation depth.

The validator also respects stricter Data Source page limits and the public Relations batch limit.

## Deferred

The following remain separate later tranches:

- provider compilation and execution;
- Policy-authorized runtime service orchestration;
- REST/admin/Ability execution adapters;
- cursor signing/decoding;
- provider benchmarks and production numeric budget certification;
- native WordPress provider implementations;
- cache execution/invalidation wiring;
- persistence/import/export of Query definitions.
