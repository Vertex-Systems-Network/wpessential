# WPEssential — Multisite Physical Data Topology Classes

Status: **Phase 0 paper architecture / no DDL or migration authorized**  
Date: 2026-08-28  
Related: ADR-0022, ADR-0023, ADR-0049, ADR-0059, ADR-0069, P-004/P-009/P-010/P-011/P-012.

## 1. Purpose

ADR-0069 fixes logical scope. This document narrows how logical site/network scope should map to physical storage without pretending one topology is optimal for every WPE domain.

WordPress Multisite itself separates blog-level tables from installation/network-global tables. WPE adopts a mixed topology rather than forcing either:
- thousands of duplicated WPE infrastructure table families; or
- one giant universal table for all application data.

No physical table is created by this document.

## 2. Topology classes

### PT-A — Native WordPress site storage
Use existing site/blog-level WordPress storage where semantics naturally belong there.

Examples:
- posts/postmeta;
- terms/taxonomy/termmeta;
- comments/commentmeta;
- site options;
- media attachment posts/meta.

WPE does not duplicate native content merely to centralize it.

### PT-B — Native WordPress network/global storage
Use WordPress network/global primitives where semantics are truly network/platform configuration and native option/meta APIs fit.

Examples:
- small network settings/defaults;
- network activation metadata that is appropriate for options;
- lightweight network policy flags.

Secrets remain Vault references, not plaintext network options.

### PT-C — WPE global scoped control-plane table
One installation-level WPE table family with explicit:
- `network_id`;
- `scope_type`;
- `site_id|null`;
- WPE UUID;
- module/type/state fields.

Preferred for platform/control-plane domains requiring:
- stable UUIDs across site switches;
- network templates/defaults;
- cross-site dependency/audit inspection;
- one migration path;
- bounded network coordination.

### PT-D — WPE global scoped high-volume runtime table
One WPE runtime table family with mandatory site/network discriminator and scope-leading indexes.

Candidate for domains where network-wide operational processing and avoiding thousands of physical tables outweigh per-site-table locality.

Requires scale evidence before acceptance per domain.

### PT-E — WPE per-site custom runtime table
A per-site prefixed table family created for each participating site.

Use only where domain evidence proves meaningful benefit such as:
- strong site-local query patterns;
- very high data volume isolated by site;
- operational partitioning advantages;
- easier site-specific export/drop behavior;
- no important network aggregate requirement.

Costs include site provisioning, upgrades across many sites and table-count explosion.

### PT-F — External authoritative store/reference
Data authority is external; WPE stores only local scoped reference/cache/status.

Examples:
- Support service tickets;
- WPE commercial account/license authority;
- third-party provider source objects.

## 3. Control-plane preference

The following WPE platform domains prefer **PT-C global scoped control-plane tables** as the current paper architecture:

- Definition identities;
- immutable Definition revisions;
- Definition dependency edges;
- Module registry state where table storage is justified;
- Job logical records/attempts if WPE owns them physically;
- Audit/Event metadata requiring network-level diagnostics;
- Connection/provider profile metadata excluding secret plaintext;
- compiled-descriptor metadata where persistence is needed;
- site/network resource assignment/provenance.

Reason:
- one logical UUID namespace;
- explicit scope independent from current blog;
- network template/reference support;
- easier cross-site diagnostics;
- one schema upgrade path;
- avoids multiplying core infrastructure tables per site.

Exact DDL/indexes remain evidence-gated.

## 4. Definition Repository topology

Preferred class: **PT-C**.

Logical tables remain:
- Definitions;
- immutable Revisions;
- revision-aware Dependencies.

Required scope concepts:
- network ID;
- scope type;
- site ID nullable only for network scope;
- stable UUID;
- type/module;
- current/published revision pointer;
- lifecycle/tombstone.

### Why not per-site by default

Per-site Definition tables would make:
- network templates;
- dependency discovery;
- global schema migration;
- site transfer;
- network diagnostics;
- UUID collision handling
more complex and would create table families proportional to site count.

### Required future proof

P-004 must compare:
- global scoped DDL/indexes;
- per-site alternative;
- 1/100/1k/10k-site metadata scale;
- publish/lookup/dependency workloads;
- site deletion/transfer;
- network Backup/Restore.

## 5. JobService topology

Logical WPE Job/Attempt history prefers **PT-C/PT-D global scoped operational tables**, independent of Action Scheduler's own physical tables.

Reasons:
- network coordinator sees child site jobs;
- fairness/backpressure can reason across sites;
- Job/Audit history survives backend cleanup semantics;
- explicit target site is stored, not inferred from worker context.

Action Scheduler backend records are implementation detail and do not replace WPE logical Job ownership.

Required indexes likely include scope/status/due-time/queue dimensions, but exact index order remains P-003 evidence.

## 6. Audit topology

Preference: **PT-D global scoped append-oriented runtime**.

Audit requires:
- network aggregation;
- site filtering;
- actor/resource/correlation lookup;
- destructive-operation forensic continuity.

Retention/partitioning/archive may be required at scale.

Audit table must not become a dumping ground for secret/raw payload content.

## 7. Definition-dependent compiled/cache state

Persistent compiled descriptors can use **PT-C** if DB persistence is necessary, or object cache/transient equivalents when regeneration is cheap.

Canonical Definition revision remains source of truth.

Compiled state always keys by:
- definition/revision;
- scope;
- compiler/schema version;
- relevant dependency generation.

## 8. Relations topology

Current candidate preference: **PT-D global scoped typed edge table**, not per-relation/per-site table explosion.

Every edge includes endpoint scope identity.

Benefits:
- universal reverse lookup;
- consistent cross-site-deny policy;
- one migration/index strategy;
- explicit endpoint site IDs;
- future intentionally cross-site relation possible without moving data.

Risks:
- very large global edge table;
- cardinality/concurrency constraints;
- scope-leading index design;
- site deletion cleanup.

P-010 must benchmark global edge vs per-site edge alternative before final physical acceptance.

## 9. Membership runtime topology

Enrollment/Entitlement current preference is **PT-D global scoped runtime** for Multisite-capable architecture, because:
- WordPress user ID is network-shared;
- entitlement must include target site scope;
- billing reconciliation/network diagnostics can span sites;
- one revoke/invalidation model can stay scope-explicit;
- network Membership future profile remains possible without migration between topology families.

However P-012 must prove:
- high-volume indexes;
- site-local authorization latency;
- revoke-to-deny latency;
- site deletion/transfer;
- privacy cleanup;
- multisite scale.

No final DDL is accepted here.

## 10. Form Entries

Topology remains **domain-evidence-gated between PT-D and PT-E**.

PT-D advantages:
- one schema;
- network operations/diagnostics;
- simpler shared workflow engine.

PT-E advantages:
- site-local bulk entries;
- isolated retention/export/drop;
- potentially lower global index contention.

Decision criteria:
- entry volume/site count;
- Query/Reporting needs;
- network-wide forms/reporting product scope;
- migration cost;
- table-count cost;
- Backup/Restore behavior.

## 11. Chat Messages

Topology remains **PT-D vs PT-E evidence-gated**.

Chat can be extremely high-volume; search/retention/attachments may favor operational partitioning.

Default product scope remains site. Network chat is future explicit profile.

No global chat table is accepted merely for convenience.

## 12. Notifications / Email delivery

Notification occurrences, recipient deliveries, transport attempts and event ledgers currently prefer **PT-D global scoped operational tables** because:
- provider webhooks need global correlation;
- one provider account can serve multiple sites;
- event dedupe/correlation requires site-aware mapping;
- network operational diagnostics are valuable.

Strict scope-leading indexes and retention are mandatory.

Raw provider payloads are not stored indefinitely.

## 13. Workflow runtime

Workflow Runs/Steps/Waits current preference: **PT-D global scoped operational tables**.

Reasons:
- JobService integration;
- approvals/waits/retries survive request/site switch;
- network coordinator workflows can own child-site actions;
- published revision pin references global Definition UUID/revision.

High-volume evidence remains P-011.

## 14. Event Inbox

Preference: **PT-D global scoped append-oriented table**.

Ingress starts before business dispatch and must resolve provider event to network/site scope.

Benefits:
- signature/dedupe processing centrally observable;
- provider event IDs may span site-facing integrations;
- safe async dispatch.

Needs retention/partition/PII minimization evidence.

## 15. Custom Tables Builder user tables

Custom Tables Builder is special: user-created table scope controls its own topology.

### Site custom table
Default logical scope: Site.

Physical strategies can include:
- PT-E per-site table; or
- PT-D shared table with required site discriminator.

UI/compiler must state chosen topology explicitly before creation.

### Network custom table
Network Admin-only high-risk schema class.

Physical preference can be one network/global table with explicit row-level site discriminator if rows are delegated to sites.

No site admin can ALTER/DROP a network-owned table.

## 16. Settings

Small settings continue to prefer native options/network options (PT-A/PT-B) where appropriate.

Large/queryable/revisioned settings do not belong in one giant autoloaded option simply because WordPress options exist.

Secrets always use Vault refs.

## 17. User Profile fields

Global WordPress identity stays native users/usermeta where core semantics apply.

Site-owned custom profile data can use:
- site-scoped usermeta key/provenance where natural; or
- WPE scoped field adapter if semantics require site separation not safely represented by plain global usermeta.

P-001/Field adapter evidence decides exact storage.

## 18. Backup/Restore consequences

Backup manifest classifies WPE tables by:
- topology class;
- logical scope;
- selected site/network coverage;
- shared/global dependency.

A Site Backup must extract only the target site's rows from PT-C/PT-D global tables, not blindly copy whole global tables.

A Network Backup can include whole scoped table artifacts or row-partitioned logical exports according to format contract.

Restore must prevent Site A rows from being restored as Site B without explicit remap.

## 19. Site deletion consequences

For PT-C/PT-D:
- delete/archive site must find site-scoped rows via indexed site ID;
- retention policy decides hard-delete/tombstone/archive;
- network/global resources are not deleted;
- shared references require dependency impact.

For PT-E:
- table drop is never automatic until retention/Backup/dependency policy authorizes it;
- site lifecycle must handle missing/partial tables safely.

## 20. Migration/upgrade consequences

PT-C/PT-D:
- one schema migration per installation/table family;
- row backfills may be large;
- migrations must be online/bounded where possible.

PT-E:
- schema migration may require N site tables;
- network upgrade becomes fan-out/coordinator operation;
- one failing site table must not leave silent mixed schema state.

This is a major reason PT-E is not default for control-plane tables.

## 21. Indexing rules

No exact index is accepted yet, but scope-aware tables follow these principles:
- include `network_id` where multiple network support matters;
- site-owned high-frequency queries include `site_id` near leading index positions;
- uniqueness keys include logical scope when names/keys can repeat by site;
- UUID lookup can use globally unique UUID index where guaranteed;
- status/due-time/created-at indexes are domain-specific;
- avoid oversized redundant composite indexes without workload evidence.

P-004/P-009/P-010/P-011/P-012 produce final index evidence.

## 22. Single-site compatibility

Single-site WordPress still uses the same WPE logical model.

Global scoped WPE control-plane tables may exist with normalized single-site coordinates; code does not maintain an entirely separate architecture.

Native site storage still uses normal WordPress tables/options.

## 23. Large-network table-count principle

Architecture must be tested against both:
- row growth in shared tables; and
- table-count/schema-migration growth in per-site tables.

Neither is assumed universally superior.

A design that looks clean on 3 sites but creates 30,000 WPE tables on a 10,000-site network is rejected unless evidence justifies it.

## 24. Current preferred mapping summary

| Domain | Current paper class |
|---|---|
| Native posts/terms/comments/options | PT-A |
| Network options/default flags | PT-B |
| Definition identity/revision/dependency | **PT-C preferred** |
| Job logical history | **PT-C/PT-D preferred** |
| Audit | **PT-D preferred** |
| Relations | **PT-D candidate, P-010 pending** |
| Membership Enrollment/Entitlement | **PT-D candidate, P-012 pending** |
| Workflow runtime | **PT-D candidate, P-011 pending** |
| Notification/Email operational state | **PT-D candidate** |
| Event Inbox | **PT-D candidate** |
| Forms Entries | PT-D vs PT-E evidence-gated |
| Chat Messages | PT-D vs PT-E evidence-gated |
| User-created Custom Tables | explicit PT-D/PT-E choice by scope/product contract |
| Remote Support/Commercial authority | PT-F |

## 25. Future evidence — NOT AUTHORIZED

After explicit consent compare:
- global vs per-site Definition DDL;
- site/network lookup latency;
- publish/dependency joins;
- 100/1k/10k-site metadata scale;
- Relations edge scale/cardinality;
- Membership authorization/revoke latency;
- Form/Chat high-volume scenarios;
- Job/Workflow/Notification queue/history volume;
- site creation/deletion/transfer;
- network migration/backfill;
- Backup site-row extraction/network restore;
- object cache interactions;
- DB index sizes/locking;
- MySQL/MariaDB supported matrix.

No DDL, migration, DB benchmark or runtime fixture has been executed.

## 26. Development gate

This paper architecture creates no tables and authorizes no schema/migration/benchmark work. ADR-0014 remains mandatory.
