# WPEssential — Search, Replace & Data Transformation Engine — Exhaustive Product Specification

Status: **Phase 0 planning / no development authorization**
Date: 2026-08-29

## 1. Purpose

Provide a safe, resumable, format-aware search/replace and bulk data transformation engine for WordPress migrations, URL/domain changes, content repair, structured-data updates and administrator/developer maintenance.

Research basis: Better Search Replace emphasizes serialization support, table selection, dry-run and Multisite; its recent security history reinforces that charset-aware parameterization, validated tables, safe unserialization and strong scope controls are essential. WPE must go beyond plain string replacement with typed Plans, backup/recovery gates, exact diff previews, checkpoints, rollback truth, WordPress-aware field policies and shared Data Source/Import-Export infrastructure.

## 2. Module identity

Pro module candidate: **Search, Replace & Data Transformation**

Navigation:
`WPEssential → Data Tools → Search & Transform`
- New Plan
- Plans
- Dry Runs
- Runs
- Profiles
- URL Migration
- Structured Data
- History / Journal
- Settings
- Diagnostics

Dependencies:
- Data Source Registry
- Definition Repository
- Field Storage
- Custom Tables
- Import/Export Plan/Journal concepts
- Backup Manager
- JobService
- Audit/Observability
- Policy/Capability
- Error Taxonomy
- Privacy
- Versioning
- Multisite
- AI Prompt Runtime

## 3. Core separation

WPE treats these as separate truths:
- Search Definition;
- Transform Definition;
- Scope Definition;
- Dry Run snapshot/fingerprint;
- reviewed Change Plan;
- execution Run;
- per-batch Checkpoint;
- mutation Journal;
- verification result;
- rollback capability/result.

A successful scan is not a successful mutation. A completed mutation is not a verified rollback.

## 4. Search definition

Search modes:
- exact string;
- case-sensitive / case-insensitive literal;
- URL-aware;
- domain/host-aware;
- path-aware;
- bounded regular expression;
- prefix/suffix where format-safe;
- typed value equality for number/bool/date;
- JSON path/value;
- structured WordPress object field;
- block attribute/text selector where certified;
- shortcode attribute where certified;
- serialized scalar/string traversal.

Options:
- unicode normalization profile;
- case-folding locale policy;
- whole value / substring;
- include/exclude null/empty;
- max match length/count;
- binary/blob handling: excluded by default;
- encrypted/Vault data: prohibited unless owning adapter provides a safe transform Ability.

## 5. Transform definition

Types:
- literal replace;
- URL host/scheme/path map;
- prefix/suffix rewrite;
- regex capture substitution;
- typed value map;
- JSON path update;
- serialized string replacement with length recalculation;
- field rename/mapping through owning API;
- block-aware transform through registered transformer;
- deterministic custom registered Transformer through SDK.

No arbitrary PHP/SQL/JS expression.

Transform controls:
- output validation;
- max expansion ratio;
- preserve type;
- preserve encoding;
- preserve serialization structure;
- reject malformed destination format;
- collision policy;
- idempotency expectation;
- reversible yes/no/partial;
- owning-module callback where required.

## 6. Scope builder

Scopes:
- selected WordPress native tables;
- selected custom tables registered through DSR;
- selected sites in Multisite where authorized;
- posts/pages/CPTs;
- terms/taxonomies;
- users/profile custom fields;
- comments;
- options/settings;
- media metadata;
- relations;
- custom field groups;
- WooCommerce entities only through certified adapter;
- arbitrary unknown table only in advanced inspected profile with explicit schema review.

Per scope:
- include/exclude table;
- include/exclude columns;
- key/identity columns;
- row Query/filter;
- batch size;
- ordering/cursor;
- protected fields;
- read-only fields;
- owning API requirement;
- estimated rows/bytes.

Default exclusions/warnings:
- `guid` changes off by default;
- `siteurl`/`home` protected/wizard-only;
- passwords/hashes/tokens/secrets prohibited;
- serialized object instantiation prohibited;
- unknown binary columns excluded;
- primary/unique keys require special migration profile;
- revision/history/audit tables follow owning-domain policy.

## 7. Format-aware processing

### Plain text
- charset/collation aware;
- length limits;
- preserve null vs empty.

### PHP serialized data
- detect serialization;
- parse without instantiating classes;
- recursively traverse supported scalar/array structures;
- preserve keys vs values according to profile;
- recalculate string lengths;
- nested/double serialization diagnostics;
- malformed serialization → no silent rewrite.

### JSON
- validate JSON;
- path-aware transforms;
- preserve scalar types;
- optional formatting preservation where feasible;
- invalid JSON can be literal-only only with explicit unsafe warning/profile.

### Gutenberg/block content
- parser-aware block attribute/text transformation where certified;
- preserve block validity/comments;
- preview structural diffs.

### HTML/shortcodes
- parser/attribute-aware transforms through certified providers;
- no blanket DOM rewrite unless profile guarantees acceptable output.

## 8. Dry Run — mandatory default

Dry Run produces:
- plan fingerprint;
- schema snapshot/hash;
- selected source count;
- matched rows/fields;
- sampled before/after values with redaction;
- exact change count where feasible;
- estimated bytes;
- protected-field warnings;
- format parse errors;
- uniqueness/collision warnings;
- external-domain/link effects;
- affected definitions/modules;
- rollback class;
- backup requirement;
- time/resource estimate;
- Multisite/site list;
- stale-plan expiry.

Dry Run never writes business data.

## 9. Change preview

Views:
- table/entity summary;
- row-level diff;
- field-level diff;
- structured JSON/block diff;
- URL map;
- error/skipped rows;
- excluded/protected values;
- grouped by post type/module/site.

Controls:
- search preview;
- filters;
- export redacted preview;
- select/exclude specific records;
- sample vs all depending volume;
- privacy masking.

## 10. Execution Plan

Before execution:
1. verify plan fingerprint/schema/version;
2. verify actor capability/reauth for high-risk scope;
3. verify backup tier where required;
4. verify lock/maintenance constraints;
5. show affected sites/tables/rows;
6. create durable Run/Journal;
7. freeze Transform revision;
8. execute in resumable batches;
9. verify each batch;
10. post-run consistency checks;
11. invalidate relevant caches/indexes;
12. emit audit report.

## 11. Backup / rollback

Risk classes:
- R0 preview-only;
- R1 reversible by exact mutation journal;
- R2 reversible via module-specific compensation;
- R3 requires verified backup restore;
- R4 irreversible/external side effect — standard Search/Replace must reject.

Options:
- require local verified backup;
- require V3 restore-tested backup for critical migrations;
- before-value journal within size/privacy budget;
- rollback dry run;
- partial rollback selection only when consistency rules permit;
- verification after rollback.

“Undo available” cannot be shown unless reversal path is actually certified for that Run.

## 12. Batch / JobService

- synchronous small profile;
- JobService for larger profiles;
- batch size adaptive but bounded;
- durable cursor/checkpoint;
- lease/claim safety;
- retry only when mutation idempotency/preconditions prove safe;
- cancellation at safe boundaries;
- pause/resume;
- crash recovery;
- progress counts;
- memory/time budget;
- no hidden timeout-driven partial success.

## 13. Concurrency

Detect and handle:
- row changed after Dry Run;
- schema changed;
- table dropped/renamed;
- plugin/module disabled;
- concurrent migration;
- two Search/Replace Runs overlap;
- unique constraint collision;
- site deletion/transfer mid-run.

Policies:
- fail conflicting row;
- re-read and re-evaluate;
- abort whole plan for critical scope;
- never overwrite newer value silently.

## 14. URL Migration wizard

Inputs:
- old scheme/host/base path;
- new scheme/host/base path;
- selected sites;
- scope presets;
- include serialized/JSON/block data;
- exclude GUID default;
- home/siteurl controlled step;
- redirect handoff to URL Routing module;
- media URL handling;
- domain-mapped Multisite mapping.

Flow:
`inventory → URL locations → proposed map → dry run → backup → apply → permalink/cache flush → link health scan → redirect suggestions → verify`.

## 15. Profiles

Saved Profile includes:
- search/transform definition references;
- scope;
- exclusions;
- risk/backup policy;
- batch profile;
- verification rules;
- schedule eligibility;
- owner;
- revision.

Profiles never embed Vault secrets.

## 16. AI Prompt

Examples:
- “old-domain.com ko new-domain.com se replace karne ka safe migration plan banao; GUID touch na karo.”
- “Is site mein `http://` media URLs find karo, preview do, abhi replace mat karo.”
- “Serialized aur JSON values bhi include karke `/old-path/` → `/new-path/` dry run karo.”

AI may generate Search/Transform/Scope Drafts and explain impact. AI cannot execute an unapproved destructive Run.

## 17. REST / Abilities / MCP / CLI

Abilities:
- inspect searchable sources;
- create Draft Search/Transform;
- run Dry Run;
- get redacted preview;
- create reviewed Plan;
- start authorized Run;
- pause/resume/cancel;
- verify;
- propose rollback;
- inspect history.

High-risk execution Abilities require capability + target Policy + reauth/approval profile. MCP exposure opt-in.

CLI should support noninteractive dry-run/report and authorized resumable execution with explicit plan fingerprint.

## 18. Multisite

- explicit site/network scope;
- network search must list targeted sites;
- per-site table prefixes resolved safely;
- global user tables handled separately;
- no current-blog assumption;
- site lifecycle races fenced;
- network profile can require per-site backup/verification;
- reports partition by site.

## 19. Security

- selected tables must be registry/introspection validated;
- parameterize SQL values; do not compose untrusted values into SQL;
- table/column identifiers from validated schema only;
- multibyte charset correctness tests;
- unserialize with class instantiation disabled;
- redact secrets/PII in previews/logs;
- CSV export formula safety;
- prevent public/CSRF invocation;
- rate/resource limits;
- no arbitrary raw SQL replace primitive.

## 20. Performance / scale

Evidence profiles:
- 1k rows;
- 100k rows;
- 1M rows;
- 10M+ rows where physical profile supports;
- large serialized/JSON fields;
- large Multisite networks.

Measure:
- rows/sec;
- memory;
- locks;
- transaction time;
- query count;
- journal growth;
- cache invalidation cost.

## 21. Evidence namespace

Future protocol: `SRT-001…SRT-176`, executed 0 until explicit development consent.

Groups cover plain/URL/regex, serialized/JSON/blocks, schema/table validation, dry-run fingerprint, diff/privacy, backups/rollback, concurrency, jobs/checkpoints, URL migration, DSR/module-aware mutation, REST/Abilities/MCP/CLI, Multisite, security/charset, performance and recovery.

## 22. MUST NOT

- production mutation without reviewed Plan for destructive profiles;
- arbitrary raw SQL UI;
- instantiate serialized PHP objects;
- modify passwords/tokens/secrets by generic replace;
- silently change primary/unique keys;
- claim rollback without verified reversal;
- overwrite concurrent newer changes;
- treat Dry Run count as execution proof;
- allow AI/MCP to bypass approval/Policy.
