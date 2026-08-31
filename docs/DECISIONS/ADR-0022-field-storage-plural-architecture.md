# ADR-0022 — Plural Field Storage Architecture

Status: **Accepted architecture / exact adapter implementations pending evidence**  
Date: 2026-08-27

## Context

WPEssential Custom Fields spans posts/CPTs, users, terms, comments, settings, custom tables, forms and application entities. Forcing every value into one EAV table, one JSON blob, or one custom-table layout would either reduce WordPress interoperability or create poor query/constraint behavior.

## Decision

WPEssential separates:
- logical field type;
- editor control;
- storage adapter;
- presentation/return format.

Storage is plural by design.

Default architecture:
1. WordPress registered/native metadata for values that naturally belong to posts/users/terms/comments and fit ordinary scale/query semantics.
2. WordPress options/network options for bounded configuration.
3. WPE Custom Tables for high-scale/query/constraint-heavy application data.
4. Relations Engine for first-class relationship/pivot/cardinality data.
5. Secrets Vault references for secret/credential values.
6. structured single-value storage only for bounded structures that do not require child-row query/index semantics.
7. derived search/report projections only as rebuildable optimization, never hidden source of truth.

## Rejected defaults

- universal EAV store for all WPE data;
- universal JSON blob for all WPE data;
- custom table column for every field regardless of target/use;
- relation IDs encoded as comma-separated/meta text when first-class relation semantics are required;
- plaintext secrets in ordinary meta/options/custom-table columns.

## Consequences

Positive:
- WordPress ecosystem compatibility where native storage is appropriate;
- strong schema/index options where application data needs them;
- clearer migration/queryability semantics;
- no single storage model has to fake capabilities it does not possess.

Costs:
- adapters require explicit capability contracts;
- migrations between adapters are first-class operations;
- Query/Data Source engines must abstract provider differences;
- testing matrix is broader.

## Guardrails

- storage choice never auto-enables REST/public exposure;
- uniqueness guarantee level must be declared by adapter;
- revision capability must be declared, not inferred universally;
- queryability/performance class visible in builder;
- field type/storage change creates migration impact analysis rather than silent rewrite;
- native metadata limitations are not hidden behind UI promises.

## Evidence still required

Exact physical adapter/schema/index behavior remains blocked on future consent-gated benchmarks, including native meta vs custom-table scale, repeaters/child rows, migrations, revisions and privacy tooling.

## Supporting document

`docs/ARCHITECTURE/FIELD-STORAGE-ARCHITECTURE-ALTERNATIVES.md`

## Development gate

This ADR accepts architecture semantics only. It authorizes no runtime adapter/table/migration implementation under ADR-0014.