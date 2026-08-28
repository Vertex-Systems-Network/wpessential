# WPEssential — Module Catalog Expansion — ADR-0197

Status: **Phase 0 accepted product planning / no development authorization**  
Date: **2026-08-29**

## Current denominator

ADR-0197 expands current planned product scope from **55 → 56** surfaces.

- planned: **56/56**;
- logical Multisite product mapping: **56/56**;
- module-wide AI Prompt product mapping: **56/56**;
- implementation authorized: **0/56**;
- runtime certified: **0** for the new/expanded scope.

## Surface 56 — Theme Workspace, Child Theme & Theme Customization Manager — Pro

### Goal
Safely analyze installed themes, create/manage child themes, inspect and override declarative theme styles, track parent/child drift and package portable child-theme assets while routing PHP/server-code development through governed source/VCS workflows.

### Core capabilities
- theme inventory + parent/child dependency graph;
- theme analyzer;
- child-theme create/duplicate/package lifecycle;
- stylesheet enqueue strategy analysis;
- CSS selector/property/media-query explorer;
- child CSS override workspace with revisions/diffs;
- `theme.json`/Global Styles validation/merge preview;
- block template/template-part override workflow;
- classic-template source inventory/diff without arbitrary live PHP editor;
- asset/font inventory with Surface 53 integration;
- parent update/drift intelligence;
- preview/compare;
- ZIP package export/import;
- Multisite installation/network-enable authority model;
- activation preflight/recovery integration;
- AI/MCP read/analyze/draft only by default.

### Hard boundary
No arbitrary PHP/eval from wp-admin. PHP/theme source development goes through Extension SDK/VCS/CI/release after explicit development consent.

### Evidence
`THM-001…THM-176`, executed **0/176**.

## Existing surface amendments accepted by ADR-0197

### Surface 53 — Font Library, Typography & Delivery
Use Any Font parity: upload/conversion, WOFF2, variable axes, `theme.json`, builders, selector assignment, subsetting/performance/licensing. Supplemental `UAF 0/176`.

### Surface 55 — Staging, Clone & Migration
WP Migrate parity: migration profiles, DB/full-site export, import/push/pull, serialized transform, file transfer, incremental media, selected tables/post types, compatibility mode, WP-CLI, Multisite mapping. Supplemental `MIG 0/176`.

### Surface 49 + Admin/Menu/Dashboard/Auth composition
White Label CMS/LoginPress parity: client experience profiles, login templates/tokens/messages, dashboard welcome, branding, role/menu presentation, redirects/security/social-login delegation. Supplemental `WLB 0/176`.

### Surface 51 — Content Order & Sequence
Post Duplicator parity: schema-aware clone/duplicate plans, bulk/multiple clone, cross-type mapping, taxonomy/field/relation/media policies, permission and provenance. Supplemental `DUP 0/176`.

### Audit & Observability
Activity Log/Simple History/WP Activity Log parity: product timeline, diffs, actor/request-channel/AI attribution, reports, alerts, digests, exports, privacy, external sinks/SIEM. Supplemental `ALX 0/176`.

### Fields / Relations / Custom Tables / Settings / Forms / REST / Builders
CMB2/Meta Box/wpmetabox parity: migration/discovery, broader placement, block bindings, custom tables, relations, frontend submission/profile, REST/Abilities, builder adapters and field-extension SDK. Supplemental `MBX 0/176`.

### Reset Manager
WP Reset parity: DB snapshots, compare/restore/export, partial reset tools, WP-CLI, development presets. Supplemental `RSX 0/176`.

### Settings Page Builder / Custom Fields
Redux parity: large declarative control catalog, settings layout, dependencies, reset/defaults, Customizer adapter, typed CSS output compiler and extension controls without Raw-PHP execution. Supplemental `RDX 0/176`.

### CPT / Taxonomy / Listings / Admin Columns / Multisite
CPTUI parity: migration/import, network templates, display/listing presets, filters, admin columns and developer/JSON compiler. Supplemental `CPTX 0/176`.

## Historical denominator

31 → 43 → 48 → 50 → 55 → **56 current**.

Historical denominators remain planning snapshots and must not be rewritten.