# WPEssential — Second Competitive Audit: Backup, Media, Ordering, Security, Fonts, Profiles & Crocoblock

Status: **Phase 0 research/planning only — no development authorization**  
Date: **2026-08-29**

## 1. Purpose

This audit extends the earlier Members / WP-Members / User Role Editor / Admin Color / image-performance / header-footer-code audit with the owner's additional targets:

- Backuply;
- Enable Media Replace;
- Post Types Order;
- Header Footer Code Manager;
- Sucuri Security;
- Broken Link Checker;
- BackWPup;
- Custom Fonts;
- Intuitive Custom Post Order;
- Fonts Plugin / Olympus Google Fonts;
- Profile Builder;
- WPvivid Backup & Migration;
- Crocoblock public repositories and JetEngine product/documentation.

Primary public references supplied by the owner were WordPress.org SVN trunk URLs plus the Crocoblock GitHub organization and JetEngine product surface. Direct SVN directory fetching was not consistently available to the research environment, so the audit also used current WordPress.org plugin pages, official vendor documentation, current changelogs and public Crocoblock GitHub/documentation evidence. Source availability does not change the planning-only status.

## 2. Existing WPE boundaries preserved

This audit does **not** create duplicate engines when an existing WPE owner already exists.

Existing owners remain authoritative:
- Membership System — Surface 15;
- User Profile Builder — Surface 14;
- Role & Capability Manager — Surface 30;
- Forms/Workflow — existing Forms surface;
- Backup Manager — existing Backup surface;
- Media/Watermark/Performance — Surface 28 family;
- Safe Script/Tag — Surface 50;
- Link Health/Crawl Intelligence — existing market-expansion surface;
- Protector — access-control/hardening orchestration, not malware scanner/WAF;
- Custom Fields / Custom Tables / Relations / Query / Dynamic Listings / REST / Settings / Dashboard — existing platform surfaces;
- shared Condition Engine / DVR / Asset Registry / JobService / Vault / Audit / Policy / Cache / Rate Limit / Multisite / AI Prompt Runtime.

## 3. Backuply / BackWPup / WPvivid audit

Market capabilities verified across the three products include:
- full/files/database backup profiles;
- scheduled/on-demand backups;
- local + remote destinations;
- FTP/FTPS/SFTP/WebDAV/S3-compatible/Drive-family destinations;
- one-click restore;
- migration/clone;
- selective include/exclude;
- incremental backup in advanced WPvivid profiles;
- staging + staging-to-live flows in WPvivid;
- Multisite backup/staging profiles;
- encryption in advanced profiles;
- WP-CLI automation;
- BackWPup 5.7+ MCP/AI-assisted backup management;
- remote-restore/standalone recovery patterns.

### Decision

**Do not replace Backup Manager. Expand it.** Existing WPE Backup already has stronger evidence semantics around verified copies, manifests, checksums, encryption, restore confidence, provider certification, recovery points and partial/unknown outcome truth.

Add parity requirements:
- incremental/differential capture strategy as a certified capability, never marketing-only;
- explicit backup-chain integrity and orphan-base detection;
- destination fan-out/mirror policy;
- WP-CLI commands using the same typed Abilities;
- MCP/AI read/plan/run/cancel actions only under Policy and approval;
- standalone/offline recovery package profile where safely supportable;
- provider-specific direct-restore certification;
- pre-update automatic recovery points;
- backup health score derived from evidence, not last-job green state.

**Create a separate Staging, Clone & Migration Manager surface** because persistent staging environments, selective push/pull and environment promotion are a different domain from backup retention/recovery.

## 4. Enable Media Replace audit

Market capabilities verified:
- replace attachment file while retaining attachment identity/name;
- replace with new filename and update references;
- link/reference rewrite;
- folder/date handling;
- replacement preview;
- cache-plugin purge integrations;
- offload compatibility;
- builder compatibility;
- modification-time output;
- optional remote AI background-removal workflow.

### Decision

No standalone duplicate Media Replace plugin surface is required. Extend Surface 28/media ownership with a **Media Asset Replacement Lifecycle**:
- keep attachment identity vs create replacement identity modes;
- canonical-original preservation option;
- derivative regeneration;
- old/new checksum and MIME validation;
- reference graph preview before rename/rewrite;
- Search/Replace integration instead of ad-hoc SQL replacement;
- cache/CDN/offload invalidation adapters;
- rollback/revision window where storage allows;
- broken-reference verification after replacement;
- builder/serialized-value adapters only after certification;
- external AI editing is an optional provider action with consent/cost/provenance, not core replacement authority.

## 5. Post Types Order / Intuitive Custom Post Order audit

Verified market bar:
- drag/drop ordering;
- posts/pages/CPT support;
- taxonomy/term ordering;
- default archive/list ordering integration;
- `menu_order` compatibility;
- dedicated reorder screens and list-table ordering;
- hierarchical support in advanced products;
- conditional/contextual order application;
- filters/search/pagination in reorder interfaces;
- Multisite/site ordering in Intuitive CPO;
- multilingual/Woo integrations in advanced market products;
- collision risk when multiple ordering plugins rewrite queries.

### Decision — New Surface 51

Create **Content Order & Sequence Manager** rather than burying ordering inside Query Builder.

It owns canonical manual sequence definitions and adapters. Query Builder consumes order definitions but does not own editorial drag/drop state.

Must support posts/CPTs, terms, hierarchical siblings, site/network lists where valid, custom-table entities through adapters, scoped/contextual sequences, per-language/domain adapters, bulk/keyboard ordering, revision/rollback, conflict detection and query explainability.

## 6. Header Footer Code Manager audit

Verified market bar includes:
- unlimited snippets;
- head/footer/before-content/after-content placement;
- selected posts/pages/CPT/categories/tags/latest posts;
- device targeting;
- shortcodes/manual placement;
- labels;
- created/updated actor tracking.

### Decision

Surface 50 Safe Script/Tag already exceeds this baseline architecturally through typed snippet types, conditions, consent, CSP/SRI/origin validation, dependency ordering, environments, revisions and emergency pause.

Add explicit parity items:
- latest-N-content condition preset;
- category/tag archive and object condition presets;
- mobile/desktop coarse-device preset with warning that it is presentation targeting, not security;
- source actor/last editor fields surfaced in the primary list;
- migration detector/importer for Header Footer Code Manager definitions;
- before/after content placement compatibility tests across block/classic themes.

No PHP/eval boundary changes.

## 7. Sucuri Security audit

Verified market bar includes:
- security activity auditing;
- file-integrity monitoring;
- remote malware scanning;
- blocklist monitoring;
- hardening controls;
- post-hack actions;
- premium WAF/brute-force/DDoS/vulnerability scanning integrations.

Current WPE Protector explicitly states that it is **not** a malware scanner or WAF. Therefore forcing scanner functions into Protector would violate an existing architectural boundary.

### Decision — New Surface 52

Create **Security Integrity, Malware & Vulnerability Scanner**.

It owns local integrity baselines, file-change classification, WordPress core checksum verification, plugin/theme provenance checks, vulnerability-feed adapters, malware/signature/heuristic scanners, remote scanner adapters, blocklist monitoring, quarantine/remediation plans, post-hack checklist/evidence, scheduled scans, and security posture reporting.

Protector continues to own request/access hardening and composes scanner findings; an upstream/CDN WAF remains an adapter/external authority, not a fake local WPE WAF.

## 8. Broken Link Checker audit

Verified market bar includes:
- local and cloud scan engines;
- posts/pages/CPT/comments/custom fields;
- links/images/redirects;
- filtering/exclusions;
- dashboard reports;
- inline edit/unlink/ignore;
- email/dashboard alerts;
- Multisite/agency use.

### Decision

Existing Link Health & Crawl Intelligence is already broader, with safe HTTP, status truth, redirect-chain analysis, internal graph/orphans, broken media, fix plans and integration with Redirect + Search/Replace.

Add parity refinements:
- explicit local-vs-remote engine capability profile;
- central inline quick-fix UX that still compiles a typed Fix Plan;
- edit/unlink/ignore/snooze bulk actions;
- per-source custom-field/comment/menu occurrence inventory;
- notification digest profiles;
- cloud engine privacy/data-transfer disclosure + opt-in;
- agency/network rollup without cross-site raw-data leakage.

## 9. Custom Fonts / Fonts Plugin audit

Verified market bar includes:
- upload local font files;
- multiple families/variants/weights/styles;
- Google Fonts library;
- local hosting/privacy profile;
- FSE/theme integration;
- Adobe Fonts integration in Fonts Plugin;
- live preview;
- per-element typography controls;
- remove/rewrite external Google font requests;
- selective subset/weight loading;
- preload controls;
- builder/theme compatibility.

### Decision — New Surface 53

Create **Font Library, Typography & Delivery Manager**.

Admin Theme typography remains admin-only presentation and is not expanded into frontend font infrastructure.

New surface owns font families, sources, licenses/provenance, variants, local uploads, provider adapters, remote-to-local import where terms permit, subsets, CSS `@font-face` generation, `font-display`, preload decisions, variable fonts, privacy audit, external-font detector/rewrite plans, builder/theme token integration, typography assignments and performance diagnostics.

## 10. Profile Builder audit

Verified market capabilities beyond the earlier Members/WP-Members audit include:
- front-end login/register/edit-profile/reset;
- drag/drop profile fields;
- avatar;
- email confirmation + admin approval;
- username/email login choices;
- password policy UI;
- role-at-registration;
- redirects;
- admin-bar control;
- role editor;
- CAPTCHA;
- content restriction including Woo and block-level contexts;
- multiple registration/edit forms;
- multi-step forms;
- conditional fields;
- social login;
- Woo user-field sync;
- user listings/directories and faceted search;
- repeaters;
- profile-change approval;
- file restriction;
- 2FA in current documentation;
- import/export users/settings.

### Decision

No new duplicate profile/membership engine. Expand existing owners:
- Surface 14 User Profile Builder;
- Surface 15 Membership;
- Forms/Workflow;
- Surface 30 Role Manager;
- OAuth/account-link subsystem;
- protected-file delivery;
- Woo adapter.

Required additions include multiple role-specific profile form compositions, profile-change approval workflow, account-security/2FA adapter surface, social login composition, profile-directory presets, Woo field mapping, admin-bar presentation policy, user import/export mapping, and stronger field-level privacy/explainability.

## 11. Crocoblock / JetEngine audit

Current official JetEngine documentation confirms a broad dynamic-data platform including:
- CPTs and taxonomies;
- meta/custom fields;
- CCT/custom SQL-table content;
- relations including optional separate relation tables;
- Query Builder with posts/users/terms/CCT/relations/REST/data-store/Woo/add-on query types;
- REST endpoints for CCT, relations, fields and queries;
- Listing Grid across Elementor/Gutenberg/Bricks/Divi;
- dynamic tables;
- options pages;
- profile/account pages;
- dynamic visibility;
- macros/dynamic tags;
- glossaries/reference lists;
- user Data Stores for favorites/wishlists/bookmarks;
- AI Website Structure Builder.

The public Crocoblock GitHub organization also exposes JetFormBuilder and numerous JetEngine/JetSmartFilters integration/add-on repositories, which validates the importance of typed extension/adaptor boundaries rather than one monolithic plugin.

### WPE parity mapping

Already stronger or equivalent in planned architecture:
- CPT/Taxonomy → WPE builders;
- Meta fields → Custom Fields;
- CCT → Custom Tables + Data Source Registry;
- Relations → Relations Builder;
- Query Builder → typed AST Query Builder;
- REST → REST API Builder + Policy;
- Listing Grid/Tables → Dynamic Listings/Components;
- Options Pages → Settings Page Builder;
- Profile Builder → User Profile + Frontend Dashboard;
- Dynamic Visibility → shared Conditional Logic Engine;
- Macros/Dynamic Tags → DVR/token resolver;
- AI Structure Builder → Solution Blueprint + AI Prompt Requirement Compiler, with validation/approval.

### Missing/gap decisions

1. **New Surface 54 — User Data Stores, Favorites & Collections** for per-user/guest collections, wishlists, bookmarks, recently viewed, comparison lists, save-for-later, custom stores, ordering, limits, expiry, privacy, merge-on-login and query/listing integration.
2. Add a **Reference Data / Glossary** definition type to the Data Source/Field-option ecosystem rather than a standalone engine.
3. Expand Dynamic Listings with first-class sortable/filterable **Dynamic Table** profile and certified Chart adapter where semantics warrant.
4. Expand Query Builder with explicit provider capability matrix, merged/sub-query rules, exposed REST query endpoint safety and query-cache explainability.
5. Expand Profile/Dashboard with content/rewrite route modes, role-limited subpages and user-content CRUD compositions.

## 12. Consolidated scope decision

Current scope before this audit: **50 surfaces**.

New surfaces:
- **51 — Content Order & Sequence Manager**;
- **52 — Security Integrity, Malware & Vulnerability Scanner**;
- **53 — Font Library, Typography & Delivery Manager**;
- **54 — User Data Stores, Favorites & Collections**;
- **55 — Staging, Clone & Migration Manager**.

Current target after acceptance: **55/55 product surfaces**.

All five require:
- exhaustive options;
- Multisite mapping;
- AI Prompt mapping;
- evidence envelope;
- lifecycle/uninstall/privacy/security boundaries;
- no runtime claims until executed.

## 13. Evidence truth

This audit is research/planning evidence only.

No plugin code was installed or executed. No backup, restore, staging clone, media replacement, reorder mutation, malware scan, font download, profile registration, code injection, link scan, Crocoblock runtime or AI/provider call occurred.

Production development authorization remains **NOT GRANTED**.