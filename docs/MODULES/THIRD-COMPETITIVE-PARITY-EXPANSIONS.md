# WPEssential — Third Competitive Parity Expansions

Status: **Phase 0 exhaustive planning addendum / no development authorization**  
Date: **2026-08-29**

This addendum is authoritative with each owning module specification after ADR-0197 acceptance.

## 1. Surface 53 — Font Library, Typography & Delivery

Add:
- upload/import TTF, OTF, WOFF and WOFF2 subject to MIME/provenance validation;
- local conversion pipeline plus optional certified remote conversion adapter;
- WOFF2 output profile;
- static faces and variable fonts;
- weight, style, stretch, oblique and named-instance mapping;
- variable-axis min/default/max metadata;
- subsetting/unicode-range profiles where licensing permits;
- `font-display` strategy;
- preload candidate analysis instead of preload-all;
- fallback/system font stacks;
- font family aliases and migration mapping;
- WordPress `theme.json`/Global Styles registration;
- Gutenberg font-library/block-editor adapters where Core capability exists;
- Elementor/Bricks/Divi/other builder registry adapters only through certified APIs;
- bounded selector assignment profile;
- font usage inventory and orphan detection;
- local-host import of approved provider fonts;
- file/hash/revision provenance;
- licensing/redistribution metadata and operator acknowledgement;
- cache/CDN fingerprint integration;
- performance diagnostics for duplicate faces, unused weights and render-blocking behavior.

MUST NOT:
- claim self-hosted equals legally/GDPR compliant automatically;
- upload/convert a font without recorded provenance/license basis;
- inject arbitrary CSS when a typed typography/token adapter can own the result.

Supplemental evidence: **UAF-001…UAF-176**, executed 0/176.

## 2. Surface 55 — Staging, Clone & Migration

Add WP-Migrate-class parity:
- reusable Migration Profiles;
- Export / Import / Push / Pull / Find & Replace actions;
- database-only and full-site profiles;
- independent media/theme/plugin/wp-content transfer;
- incremental file transfer by hash/mtime only when certified;
- serialized data transformation delegated to Search/Replace;
- selected tables and post-type exclusion/inclusion with destructive impact preview;
- temporary-table/import staging and atomic-enough swap profile where DB adapter supports;
- preserved-target option set;
- migration compatibility mode for plugin execution suppression with explicit allowlist;
- resumable chunk/cursor state with schema validation;
- cancel cleanup/reconciliation;
- source/target version fingerprint;
- WP-CLI parity through same Abilities;
- Multisite network/subsite/single-site conversion maps;
- pre-migration backup and post-target verification;
- profile export/import without credentials.

MUST NOT:
- advertise generic database merge;
- silently delete excluded destination rows because a table replacement profile was misunderstood;
- expose credentials or reusable migration secrets.

Supplemental evidence: **MIG-001…MIG-176**, executed 0/176.

## 3. Surface 49 + Admin/Menu/Dashboard/Login composition

Add a **White-label / Client Experience Profile**:
- organization/client/agency branding layers;
- setup wizard/presets;
- login layout templates built from typed tokens/components;
- logo/background/form/input/button/footer/message controls;
- responsive/mobile preview;
- password visibility/remember-me/focus/error-state tokens;
- editable welcome/error/help text without changing authentication semantics;
- custom Dashboard welcome composition;
- role/client dashboard presets;
- admin header/footer branding;
- admin-bar identity;
- menu presentation profile;
- optional plugin/theme-list presentation suppression with warning that underlying capability remains authoritative;
- import/export/versioning;
- Multisite network template/enforcement;
- compatibility diagnostics for WooCommerce/LMS/community/auth surfaces;
- login/logout redirect definition delegated to auth/navigation Policy;
- CAPTCHA/rate-limit/hide-login delegated to Protector;
- social login delegated to OAuth Account Link;
- private/force login delegated to Membership/Protector;
- advanced CSS/JS delegated to Safe Script/Tag with explicit risk class.

Supplemental evidence: **WLB-001…WLB-176**, executed 0/176.

## 4. Surface 51 — Content Order & Sequence: Duplicate/Clone operations

Add **Content Duplicate / Clone Plan**:
- one-click, modal and bulk entry points;
- allowed source post types;
- allowed target post types;
- clone count with bounded maximum;
- title/slug suffix/template;
- status;
- author;
- publication/date policy;
- parent mapping;
- menu/order mapping;
- taxonomy copy/remap;
- field/meta copy policy via Field Storage registry;
- protected meta adapter policy;
- relation copy profile: reference same / duplicate related / skip / map;
- featured media: reuse attachment / clone asset where permitted / replace / remove;
- inline media remains referenced unless explicit Asset Clone Plan exists;
- comments default skip;
- revisions/autosaves default skip;
- password/private-state constraints;
- cross-post-type mapping preview;
- builder/template adapter data;
- Multilingual translation-link handling through adapter;
- duplicate provenance/source reference;
- audit event;
- idempotency key for automation;
- Workflow/Ability operation;
- failure rollback/partial truth for multi-clone runs.

MUST NOT:
- blindly clone secret/protected provider metadata;
- preserve IDs/GUIDs as if clone were same entity;
- allow actor to assign author/status/post type they lack authority to create/edit/publish.

Supplemental evidence: **DUP-001…DUP-176**, executed 0/176.

## 5. Audit & Observability — Activity Timeline & Audit Console

Add product-facing experiences on the existing Audit store:

### Activity Timeline
- natural-language event summary;
- actor/principal;
- initiating channel: wp-admin / frontend / REST / WP-CLI / WP-Cron / XML-RPC / Ability / Workflow / Job / remote service / AI-assisted invocation;
- target resource;
- result/severity;
- before/after diff where domain adapter supplies it;
- correlation/run/session reference;
- IP handling under privacy profile;
- safe User-Agent/device class optional;
- application-password identifier/name when Core exposes safe reference, never secret;
- AI agent/tool attribution as supplemental initiator context, never authentication authority.

### Filters / views
- user/actor;
- event/action class;
- source/channel;
- module/plugin/theme;
- resource;
- result/severity;
- site/network;
- date;
- IP/subnet where policy permits;
- automation/AI initiated only;
- saved views/shared views under Policy.

### Reporting
- dashboard widget;
- per-resource history deep link/column;
- daily/weekly digest;
- alert rules;
- statistics/report definitions;
- CSV/JSON export with Policy;
- privacy export/erase integration;
- retention status dashboard.

### External sinks
- external DB;
- Syslog;
- SIEM/log collector adapters;
- webhook/event-stream sink through Connections;
- delivery state/retry/checkpoint;
- local event remains distinct from external immutable evidence.

### SDK
- typed Logger/Event adapter;
- domain diff renderer;
- third-party integration logger;
- redaction schema;
- event versioning.

Supplemental evidence: **ALX-001…ALX-176**, executed 0/176.

## 6. Custom Fields / Relations / Tables / Settings / Forms / REST / Builders

CMB2/Meta Box parity additions:
- source detector for CMB2/Meta Box registrations;
- migration assistant from supported field groups;
- posts/terms/users/comments/settings placement parity;
- Customizer compatibility adapter where current Core still supports target;
- repeatable/group/clone mapping with stable row identity;
- 40+ common field-type parity through WPE typed schema;
- custom field type SDK;
- field-level block binding adapter;
- revision integration;
- custom-table field mapping;
- relation definitions and side-specific field groups;
- frontend submission/profile compositions;
- REST/Abilities exposure with edit/read Policy parity;
- builder integrations as Dynamic Data adapters;
- admin columns integration;
- conditional/include-exclude/show-hide mapping;
- migration adapters for ACF/Meta Box/CMB2/Pods/Toolset where formats and licensing permit;
- import preview, unsupported-field report and post-migration verification.

Meta Box/wpmetabox architecture is evidence of modular product breadth; WPE keeps one canonical typed Field/Relation/Table/Settings architecture.

Supplemental evidence: **MBX-001…MBX-176**, executed 0/176.

## 7. Reset Manager parity

Add:
- DB Snapshot as a development/recovery artifact distinct from full Backup Set;
- snapshot create/list/compare/restore/export/delete;
- table/schema/options diff summary;
- reset site database profile;
- partial tools: transients, uploads, plugin packages, theme packages, theme mods/options, custom tables and `.htaccess` where filesystem owner/adapter permits;
- WP-CLI parity through Reset Abilities;
- Development Reset preset;
- package collection/reinstall plan delegated to Solution Blueprint/package management rather than hidden remote install;
- mandatory recovery-admin preservation;
- Multisite blockers/limitations explicit;
- post-reset bootstrap and health verification.

WPE safety remains stricter than market one-click patterns.

Supplemental evidence: **RSX-001…RSX-176**, executed 0/176.

## 8. Redux-class Settings / Options Framework parity

Add to Settings Page Builder + Fields:
- accordion/tabs/sections/groups;
- sorters and sortable structures;
- richer style controls: background/border/shadow/spacing/dimensions/palettes;
- media/gallery/multi-media;
- icon/social profiles;
- taxonomy/user/metabox contexts through Field Schema;
- dependency tree / required conditions;
- per-section disable/read-only;
- per-field/section reset to default;
- validation/error summary;
- import/export;
- Customizer compatibility adapter;
- developer declarative configuration compiler to WPE definitions;
- typed CSS/style output compiler via Design Tokens/Asset Registry;
- publish-time output preview/diff;
- settings-changed Events rather than arbitrary compiler PHP callbacks;
- extension control SDK.

MUST NOT:
- expose Raw PHP as a field type;
- execute arbitrary compiler callbacks entered from admin UI;
- emit unsanitized selector/property/value CSS.

Supplemental evidence: **RDX-001…RDX-176**, executed 0/176.

## 9. CPT / Taxonomy parity

Add:
- CPTUI import detector/migration plan;
- complete current WordPress labels/arguments profile;
- registration ownership/provenance;
- registration diff before slug/capability/rewrite changes;
- network template/push/inheritance profile;
- Dynamic Listing presets for cards/grids/sliders/single output instead of private CPT renderer;
- taxonomy filter composition;
- Admin Columns preset integration;
- shortcode/block display adapter through shared Component/Listing system;
- JSON/developer config compiler;
- CI-friendly definition export;
- auto-discovery/SDK registrations normalized to inspect-only or managed ownership.

Supplemental evidence: **CPTX-001…CPTX-176**, executed 0/176.

## 10. Shared boundaries

- presentation hiding ≠ authorization;
- activity/audit event ≠ business truth or authentication authority;
- AI-agent attribution ≠ actor privilege;
- duplicate/clone ≠ same entity identity;
- migration/export ≠ live database merge;
- DB snapshot ≠ full disaster-recovery backup;
- child theme customization ≠ wp-admin branding;
- custom field competitor format ≠ WPE canonical schema;
- declarative compiler ≠ arbitrary PHP/eval runtime;
- local font delivery ≠ automatic legal compliance.

All supplemental evidence remains **0 executed**.