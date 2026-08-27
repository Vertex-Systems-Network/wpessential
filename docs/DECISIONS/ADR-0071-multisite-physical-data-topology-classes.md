# ADR-0071 — Multisite Physical Data Topology Classes

Status: **Accepted paper architecture / exact DDL evidence pending**  
Date: 2026-08-28

## Context

ADR-0069 establishes logical site/network scope but intentionally leaves physical storage open. WordPress Multisite itself separates blog-level and global/multisite-global tables. WPE needs a consistent rule for choosing between native WordPress storage, shared WPE tables, per-site WPE tables and external authority without forcing one topology onto every domain.

## Decision

WPE adopts explicit physical topology classes:

- **PT-A** — native WordPress site/blog storage;
- **PT-B** — native WordPress network/global option/meta primitives;
- **PT-C** — WPE global scoped control-plane tables;
- **PT-D** — WPE global scoped high-volume runtime tables;
- **PT-E** — WPE per-site custom runtime tables;
- **PT-F** — external authoritative store with local scoped references/cache.

Current paper preference:
- Definition identity/revision/dependency → **PT-C**;
- WPE logical Job history → **PT-C/PT-D**;
- Audit → **PT-D**;
- Relations → **PT-D candidate, P-010 evidence required**;
- Membership Enrollment/Entitlement → **PT-D candidate, P-012 evidence required**;
- Workflow runtime → **PT-D candidate, P-011 evidence required**;
- Notification/Email operational state → **PT-D candidate**;
- Event Inbox → **PT-D candidate**;
- Form Entries → **PT-D vs PT-E evidence-gated**;
- Chat Messages → **PT-D vs PT-E evidence-gated**;
- user-created Custom Tables → explicit PT-D/PT-E choice according to scope/product contract;
- remote Support/commercial authority → PT-F.

Native WordPress posts/terms/comments/options remain native rather than being duplicated into WPE tables merely for centralization.

Control-plane infrastructure prefers PT-C because it avoids multiplying core tables by site count, supports network templates/dependencies/diagnostics and gives one migration path while keeping logical scope explicit.

High-volume domains are not globally forced into PT-D until workload evidence exists.

## Multisite safety requirements

- every shared WPE table retains explicit scope coordinates;
- site-owned high-frequency indexes include site scope according to workload;
- uniqueness includes logical scope where keys may repeat by site;
- Site Backup extracts only target-site rows from shared tables;
- site deletion cannot delete network/global resources by implication;
- PT-E lifecycle must handle provisioning/upgrades across many sites;
- exact global/per-site DDL remains separate from logical product semantics.

## Consequences

Positive:
- avoids one-size-fits-all schema design;
- avoids WPE control-plane table explosion on large networks;
- keeps native WordPress data native;
- supports network coordination/diagnostics cleanly;
- preserves evidence-based choice for very high-volume runtime domains.

Costs:
- Backup/Restore must understand row-scoped shared tables;
- site deletion/transfer needs scoped cleanup logic;
- global tables require careful composite indexes;
- some runtime domains need benchmark comparisons before implementation.

## Evidence still required

After explicit owner consent:
- P-004 Definition global-vs-per-site DDL/index benchmark;
- P-003 Job storage/backend mapping;
- P-010 Relations scale/cardinality;
- P-011 Workflow runtime scale;
- P-012 Membership authorization/revoke scale;
- Form/Chat PT-D vs PT-E workload comparison;
- 100/1k/10k-site metadata/table-count comparison;
- site creation/delete/transfer;
- network migration/backfill;
- site-row Backup extraction/network restore;
- MySQL/MariaDB locking/index evidence.

No schema, migration or DB benchmark has been executed.

## Development gate

Acceptance of topology classes does not authorize DDL, migrations, table creation, benchmarks or runtime storage implementation. ADR-0014 remains the hard consent gate.
