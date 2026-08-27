# ADR-0088 — Custom Tables PT-D/PT-E Physical Baseline

Status: **Accepted paper physical/migration profile / executable evidence pending**  
Date: 2026-08-28

## Context

WPEssential Custom Tables can represent site-owned application data, shared physical runtime data or genuinely network-owned data. A universal all-global or all-per-site rule would either create scope risk or operational table proliferation.

## Decision

Accept the future comparison:

- **CT1 — PT-E per-site managed table** as the first baseline for ordinary site-owned builder-created tables.
- **CT2 — PT-D shared scoped managed table** as mandatory comparison for large-network/shared-physical operation.
- **CT3 — explicit network-owned table profile** only when the data is genuinely network-owned, not as a convenience fallback.

The same desired-schema + typed Migration Plan language applies regardless of CT1/CT2/CT3.

## Why CT1 first

Site ownership is the default product scope. CT1 gives stronger physical isolation and simpler per-site Backup/clone/delete reasoning.

Its cost is table count, provisioning/migration fan-out and metadata overhead on large networks. CT2 remains mandatory because it may materially improve those operational costs.

## CT2 hard requirements

Shared physical storage cannot weaken isolation:
- every site-owned row/query/mutation carries trusted scope;
- site-local unique/index semantics include scope;
- row ID alone is insufficient where ownership matters;
- site Backup/delete/clone operates only on owned rows;
- wrong-site IDOR/destructive fixtures are mandatory.

## Migration rule

Definition publication and physical migration are separate facts.

Future migration strategies compare compatible direct change, chunked backfill, shadow/copy/swap and recovery-only destructive classes. Reviewed source fingerprint must be revalidated before mutation.

No Definition delete automatically drops physical data.

## Selection gates

Reject a physical/migration profile regardless of speed if it permits:
- unchecked identifiers/raw DDL injection;
- wrong-site CT2 row access or deletion;
- site authority over network-owned table;
- one-site lifecycle operation damaging shared rows;
- migration against stale observed fingerprint;
- silent lossy conversion/truncation;
- false zero-downtime/online guarantees.

## Evidence still required

After explicit owner consent:
- CT1 vs CT2 query/storage/index/provisioning/migration at 100/1k/10k sites;
- MySQL/MariaDB supported DDL/locking/algorithm behavior;
- backfill/shadow migration crash recovery;
- drift and concurrent writes;
- Backup/Restore/site lifecycle;
- exact DDL/types/indexes.

Executed Custom Tables fixtures: **0**.

## Development gate

This ADR authorizes no table creation, migration, SQL, backfill, schema introspection, benchmark or lifecycle hook. ADR-0014 explicit owner consent remains required.