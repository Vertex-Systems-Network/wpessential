# WPEssential — Forms & Chat PT-D vs PT-E Runtime Topology Comparison

Status: **Phase 0 paper comparison / no tables or benchmarks authorized**  
Date: 2026-08-28  
Related: ADR-0014, ADR-0069, ADR-0071, ADR-0075, Form/Chat runtime candidate docs.

## Purpose

ADR-0071 deliberately left Form Entries and Chat as PT-D vs PT-E evidence-gated because both can be high-volume, private, site-owned runtime domains. This document defines first benchmark profiles without changing their accepted logical data models.

# Part A — Forms

## Forms logical model remains

- Form Definition in Definition Repository;
- Entry core normalized;
- canonical versioned submission value document;
- optional selected typed query projections;
- protected file references;
- pinned Form revision;
- Workflow runtime separate;
- passwords/reset/security tokens never stored in Entry canonical data.

## FRT1 — PT-D shared scoped Forms runtime — first benchmark baseline

Shared WPE runtime table family across network with explicit `network_id/site_id`.

Conceptual tables/families:
- Entry core;
- canonical value document or value blob column/family;
- optional query projections;
- draft/resume metadata;
- protected-asset ownership references where needed.

### Advantages
- one migration/version registry;
- generic admin/query/export/privacy implementation;
- no per-site table provisioning;
- network diagnostics can aggregate without table fan-out;
- easier JobService retention/cleanup across sites;
- stable behavior for site transfer/remap.

### Risks
- high-volume site can dominate global indexes/table;
- scope predicate failure has larger blast radius;
- privacy/erase/site Backup must always filter scope correctly;
- network-wide retention jobs need fairness/backpressure;
- large canonical value documents can cause hot-row/IO pressure.

## FRT2 — PT-E per-site Forms runtime — mandatory comparison

Each site gets Forms runtime table family.

### Advantages
- physical site isolation;
- site Backup/restore/drop conceptually simpler;
- hot high-volume site mostly isolated from other site indexes;
- per-site data residency/admin cleanup easier to reason about locally.

### Costs
- 100/1k/10k-site table/migration explosion;
- network diagnostics/privacy tooling must fan out;
- per-site schema-version drift possible;
- site provisioning/deletion more DDL-heavy;
- cross-site/network reporting requires explicit aggregate service.

## Forms benchmark decision factors

- 10k/100k/1M Entries;
- small vs large/repeater canonical payloads;
- admin list/filter by projected fields;
- privacy export/erase;
- draft cleanup;
- retention/anonymization;
- one hot site + many quiet sites;
- 1k/10k-site migration overhead;
- scoped Site Backup/restore;
- network diagnostics;
- site deletion/transfer;
- scope attack tests.

## Forms current recommendation

Use **FRT1/PT-D as first future benchmark baseline**, with FRT2 mandatory before final topology. The reason is platform migration/diagnostics simplicity, not assumed performance superiority.

# Part B — Chat

## Chat logical model remains

Dedicated runtime domains:
- conversations;
- participants/personal state;
- messages;
- moderation/report records;
- Protected Asset refs;
- optional derived search projection.

Canonical message state is transport-independent. Search never becomes authorization source.

## CRT1 — PT-D shared scoped Chat runtime — first benchmark baseline

Shared WPE runtime table families with explicit network/site scope.

Likely families:
- conversations;
- participants;
- messages;
- moderation/reports;
- derived search projection/metadata where selected.

### Advantages
- one schema/migration path;
- one Conversation/Message repository API;
- WordPress users are network-shared but site authorization stays explicit;
- easier network diagnostics/retention job orchestration;
- potential future explicit network conversation profile without second engine;
- no per-site message-table proliferation.

### Risks
- message table can become largest WPE table;
- missing scope predicate is critical privacy incident;
- one huge site/group can dominate global indexes;
- retention/search/index maintenance heavy;
- site Backup/export/delete must scope exact rows;
- global auto-increment/sequence hotspots must be assessed.

## CRT2 — PT-E per-site Chat runtime — mandatory comparison

Each site owns its Chat tables.

### Advantages
- strong physical site separation;
- site-level Backup/restore/retention isolation;
- one site's message volume does not enlarge another site's table/index;
- site deletion can remove local chat store after policy checks.

### Costs
- table count/migrations across large networks;
- Network Admin moderation/diagnostics fan-out;
- future network conversation/cross-site support requires another topology;
- shared-user conversation lookup across sites harder;
- site schema drift/repair burden;
- realtime/search adapter management per site may fragment.

## Chat benchmark decision factors

- millions of messages;
- 2-person vs 1k-participant conversations;
- one high-volume site + many quiet sites;
- cursor pagination by per-conversation sequence;
- unread counts/participant lookup;
- concurrent message idempotency;
- Membership/team revoke while send/search/download occurs;
- search projection rebuild/authorization;
- attachment retention;
- moderation/erase;
- 1k/10k-site schema migration cost;
- site Backup/delete/transfer;
- privacy scope attacks.

## Chat sequence note

Per-conversation ordering must not require a network-global serialized counter if that becomes a bottleneck. Candidate sequence allocation strategies remain benchmark evidence.

## Chat current recommendation

Use **CRT1/PT-D as first future benchmark baseline**, with CRT2 mandatory before final topology. A future external realtime/search service remains transport/index adapter, not canonical message authority.

# Shared security invariants

For either PT-D/PT-E:
1. site scope is explicit in domain API;
2. raw table selection/prefix switching never substitutes Policy;
3. Site A cannot query Site B Entries/messages by changing IDs;
4. global WordPress user ID does not imply cross-site access;
5. Site Backup exports only authorized target-site runtime;
6. site deletion follows domain retention, not generic hard-delete;
7. site transfer remaps scope via reviewed migration;
8. cache/search index includes scope and reauthorizes;
9. lifecycle drain blocks stale outbound Workflow/Notification/message actions;
10. privacy exporter/eraser stays domain-aware.

# P-Form/PT topology future evidence — NOT AUTHORIZED

Compare FRT1/FRT2 for:
- table/index size;
- p50/p95/p99 Entry list/read/write;
- projected filters;
- privacy erase/export;
- cleanup/retention;
- site Backup/restore;
- migration/provisioning at 100/1k/10k sites;
- wrong-site attacks;
- one hot-site noisy-neighbor effects.

# P-Chat/PT topology future evidence — NOT AUTHORIZED

Compare CRT1/CRT2 for:
- conversation list/participant lookup;
- message history/cursor pagination;
- concurrent sends/idempotency;
- unread state;
- large group behavior;
- retention/search/moderation;
- site Backup/restore/delete;
- 100/1k/10k-site schema overhead;
- privacy/scope attack fixtures;
- noisy-neighbor effects.

# Selection rule

PT-D wins only if it passes scope/privacy isolation and demonstrates acceptable noisy-neighbor/index/retention behavior. PT-E wins only if migration/table-count/Network Admin costs remain acceptable.

Neither profile is final before evidence.

## Development gate

No Forms/Chat table, migration, fixture data, search index, transport, benchmark or test may be created/run before explicit owner consent under ADR-0014.
