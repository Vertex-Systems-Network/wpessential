# WPEssential — WordPress Market Gap Audit — August 2026

Status: **Planning research / no development authorization**
Date: 2026-08-29

## 1. Purpose

Record market evidence used to decide which newly researched WordPress capabilities deserve new WPE modules, which should extend existing modules/shared services, and which should be rejected to avoid duplicate or unsafe runtimes.

Popularity is a demand signal, not architecture authority.

## 2. Research highlights

### Redirection / URL routing

Current Redirection plugin evidence shows a mature redirect product with:
- exact/regex URL redirects;
- query matching/passthrough;
- automatic redirects after permalink changes;
- conditions based on login, capability, browser, referrer, cookies, headers, IP, host/server and page type;
- redirect/404 logging;
- response headers;
- permalink migration;
- Apache/Nginx support;
- fine-grained permissions;
- JSON/CSV/.htaccess/Nginx import/export and WP-CLI;
- current REST API and React-based admin architecture.

Market signal: 2M+ active installations.

Decision: **NEW MODULE** — URL Redirection & Routing Manager.

WPE differentiation:
- typed conditions/actions;
- no arbitrary callback UI;
- Policy-aware routing;
- chain/loop/collision simulation;
- large-rule compiled cache;
- privacy-aware 404/logging;
- AI plan generation;
- Link Health/Search-Replace integration;
- lossiness-aware server export.

### Database Search & Replace

Better Search Replace evidence shows:
- serialization support;
- selected table scope;
- Dry Run;
- Multisite;
- change detail/backups/profiles in Pro.

Recent security/compatibility history reinforces:
- safe table validation;
- no unsafe unserialized object instantiation;
- multibyte charset/parameterization correctness;
- compound primary-key handling.

Market signal: 1M+ active installations.

Decision: **NEW MODULE** — Search, Replace & Data Transformation Engine.

WPE differentiation:
- immutable Dry Run fingerprint;
- exact diff preview;
- JSON/block/serialized-aware transformations;
- owner-aware Data Source APIs;
- Backup/rollback truth;
- durable Job checkpoints;
- concurrent-edit detection;
- URL migration wizard + Link Health/Redirect handoff;
- typed Abilities/MCP/AI.

### Dummy / fixture data

FakerPress market evidence covers posts/CPTs, meta, featured images, users, terms, comments, attachments, many data providers, cleanup, REST/OpenAPI and batching.

Decision: **NEW MODULE** — Dummy Data, Synthetic Dataset & Fixture Studio.

WPE differentiation:
- deterministic seed/reproduction;
- every WPE Data Source/Field/Relation/Status/Custom Table;
- Solution Blueprint scenarios;
- adapter-owned domain fixtures;
- edge/adversarial datasets;
- large-scale load profiles;
- strong generated-data ownership/cleanup journal;
- privacy-safe synthetic PII;
- no real provider/payment/email side effects by default.

### Broken Link / crawl health

Market evidence:
- Broken Link Checker 500k+ active installations;
- AIOSEO broken-link product 300k+ active installs;
- modern tools expose scan progress, link/image classification, scheduled scans, inline fixes and exports.

Decision: **NEW MODULE** — Link Health, Broken Link & Crawl Intelligence.

Reason it is not merely Redirect Manager:
- a redirect manager reacts to requests/rules;
- Link Health inventories links embedded in content/routes and verifies external/internal targets;
- it needs crawling, Safe HTTP, graph/orphan semantics, scan scheduling and source-to-link occurrence mapping.

Integration: Link Health can propose Redirect/Search-Replace Fix Plans but cannot mutate them directly.

### Database cleanup / storage health

Market signal from WP-Optimize and the broader maintenance category shows continuing demand for database cleanup, autoload/storage diagnostics and scheduled optimization.

Decision: **NEW MODULE** — Database Maintenance, Cleanup & Storage Health.

Reason:
- cleanup is a destructive owner-aware lifecycle problem, not Reset Manager;
- it needs provider-specific retention/cleanup contracts, Dry Run, Backup gates, orphan certainty and Job scheduling.

WPE deliberately avoids generic blind DELETE/TRUNCATE controls.

## 3. Market capabilities that should NOT become separate modules

### Query Monitor / developer diagnostics

Query Monitor has 200k+ active installs and provides deep DB query, PHP error, hooks/actions, REST/Ajax, block, asset, template and capability diagnostics.

Decision: **EXPAND EXISTING PLATFORM DIAGNOSTICS + AUDIT/OBSERVABILITY**, not a new sellable module.

Planned additions:
- request timeline;
- query groups/slow/duplicate/error diagnostics;
- hook/action traces;
- REST/Ajax diagnostics;
- script/style dependency warnings;
- template/block route diagnostics;
- current Ability/Policy decision trace;
- Job/Workflow correlation IDs;
- AI/MCP initiator attribution.

This data is diagnostic/ephemeral by default, separate from Audit retention.

### Health Check / Troubleshooting Mode

WordPress.org Health Check category shows strong demand for safe conflict diagnosis; standalone Troubleshooting Mode lets a maintainer disable plugins/switch theme for their own session while visitors remain unaffected.

Decision: **PLATFORM DIAGNOSTICS SHARED-SERVICE EXPANSION**, not a new module.

Planned capability:
- per-operator troubleshooting session;
- temporary plugin/theme profile;
- cache-warning detection;
- health snapshot before/after;
- explicit recovery/exit;
- never global site mutation disguised as per-user isolation.

Requires deep compatibility/security evidence before implementation.

### User Switching / support impersonation

User Switching has 200k+ active installs and supports controlled switch/switch-back flows.

Decision: **ADD CONTROLLED SUPPORT IMPERSONATION OPTION** under User Profile/Role & Capability/Platform Support, not a separate module.

Requirements:
- explicit capability;
- cannot switch to protected higher-privilege principals without stricter policy;
- session provenance/audit;
- concurrent support-session warning;
- switch-back token/session safety;
- optional reason/ticket reference;
- exclude from ordinary AI/MCP exposure by default.

### WP Crontrol

WP Crontrol has 300k+ active installs and makes WordPress cron schedules/events visible/manageable.

Decision: **EXISTING CRON JOB BUILDER / JobService enhancement**, not a new module.

Add/ensure:
- native WP-Cron event inventory;
- due/overdue schedules;
- hook source/provider;
- run-now with authorization;
- delete/reschedule;
- schedule registration diagnostics;
- URL/PHP event types are not copied blindly; arbitrary PHP events conflict with WPE no-arbitrary-code architecture.

### Activity / history plugins

Simple History market demand validates activity visibility, but WPE already has Audit/Observability and domain histories.

Decision: **NO NEW MODULE**.

Add/ensure:
- human-readable event summaries;
- before/after diff where privacy-safe;
- current AI/Ability/MCP initiator attribution;
- plugin/theme/core changes;
- reports/export;
- privacy exporter linkage.

### Media replace / regenerate thumbnails

Decision: **EXTEND WATERMARKER / MEDIA RULES**, not a new module.

Planned options:
- replace attachment file while preserving attachment identity where safe;
- regenerate selected/all registered image sizes;
- compare derivative manifest;
- orphan derivative cleanup through DB Maintenance;
- offload/object-storage adapter compatibility;
- backup/rollback for source replacement.

### Code Snippets

Large market demand exists for PHP/HTML/CSS/JS snippet managers.

Decision: **REJECT GENERIC EXECUTABLE PHP/JS SNIPPET MODULE** as default WPE architecture.

Reason:
- contradicts ADR-0004 / no arbitrary privileged executable code primitive;
- high blast radius for AI/MCP;
- duplicates plugin/theme code deployment and Extension SDK.

Safe alternative:
- typed registered SDK extensions;
- declarative hooks/conditions/templates;
- CSS/theme tokens in appropriate UI layer;
- controlled developer-owned plugin package outside ordinary WPE no-code runtime.

## 4. Newly accepted module candidates from this audit

1. URL Redirection & Routing Manager
2. Search, Replace & Data Transformation Engine
3. Dummy Data, Synthetic Dataset & Fixture Studio
4. Link Health, Broken Link & Crawl Intelligence
5. Database Maintenance, Cleanup & Storage Health

These add **5** module/platform surfaces to the current ADR-0177 denominator when formally accepted by ADR.

## 5. New non-sellable shared planning services

- **S07 Product Discovery & Pre-Development Planning Orchestrator**
- **S08 Market Intelligence & Capability Radar**

These do not add module-denominator rows.

## 6. Market Radar candidate categories for future watch

Daily monitoring should continue for:
- migration/staging;
- media replacement/optimization;
- admin workflow/productivity;
- accessibility auditing;
- consent/privacy tooling;
- SEO/AEO/GEO/AI crawler tooling;
- developer diagnostics;
- data/schema migration;
- security/recovery;
- integrations/automation;
- forms/membership/commerce;
- multisite operations;
- performance/cache/CDN;
- content lifecycle/versioning;
- AI/Abilities/MCP ecosystem.

Future signal does not automatically become scope.

## 7. Research safety

- feature ideas may be learned from public products;
- WPE implementation must remain independently designed against its own architecture;
- licenses/provenance must be respected;
- support forum/review complaints are anecdotal signals;
- current versions/market stats require fresh verification;
- no source popularity metric can bypass security/governance.
