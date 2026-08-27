# ADR-0049 — Definition Repository Relational Shape

Status: **Accepted architecture / exact DDL & multisite placement pending**  
Date: 2026-08-27

## Decision

WPEssential Definition Repository uses three logical relational stores:

1. stable **Definitions** identity/lifecycle/current/published pointers;
2. immutable **Definition Revisions** with versioned JSON payload + fingerprint;
3. revision-aware **Definition Dependencies** with reverse lookup/unresolved UUID support.

Runtime business data remains outside this repository.

Published runtime is selected by pointer; editing creates new immutable revisions. Dependencies belong to the source revision, so draft dependency changes do not silently mutate published runtime.

## Physical direction

- normalize/index only shared proven list/filter fields;
- do not add generic definition-meta/EAV by default;
- application-valid JSON text is preferred on paper over making vendor-specific native JSON semantics a hard requirement;
- canonical UUID string representation is preferred for maintainability until benchmark proves binary storage is materially beneficial;
- archive/tombstone is distinct from physical purge;
- optimistic concurrency prevents silent last-write-wins.

## Multisite

Global/network tables with explicit scope are the current paper preference for WPE's cross-module platform, but this placement is **not yet Accepted**. P-004 must compare it against per-site tables for isolation, network scale and migrations.

## Supersession

This ADR accepts the relational shape proposed under ADR-0008. ADR-0008's exact physical DDL/index/multisite/benchmark gate remains unresolved, but future work must not reopen the three-store identity/revision/dependency model without a superseding ADR.

## Remaining evidence

Exact SQL types/indexes/collation, JSON storage type, UUID physical representation, scope placement, transactions/deadlocks, 10k/100k scale, import/tombstone/concurrency and migrations require P-004 after explicit owner consent.

See `docs/ARCHITECTURE/DEFINITION-REPOSITORY-PHYSICAL-SCHEMA-CANDIDATE.md`.

No table or migration has been implemented.