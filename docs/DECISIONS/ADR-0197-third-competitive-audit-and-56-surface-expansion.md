# ADR-0197 — Third Competitive Audit and 56-Surface Expansion

Status: **Accepted planning decision / execution pending**  
Date: **2026-08-29**

## Context

ADR-0195 established a 55-surface WPEssential product scope. The owner requested a further source/market audit of Use Any Font, WP Migrate DB/WP Migrate, White Label CMS, Post Duplicator, LoginPress, Activity Log, CMB2, Child Theme Configurator, Simple History, WP Reset, WP Activity Log, Meta Box, Redux Framework, CPT UI and the public wpmetabox GitHub organization.

Research found substantial parity opportunities but only one genuinely distinct missing domain.

## Decision

Accept:

- `docs/RESEARCH/THIRD-COMPETITIVE-AUDIT-FONTS-MIGRATION-WHITELABEL-DUPLICATION-AUDIT-FIELDS-THEMES-RESET-2026-08.md`;
- `docs/MODULES/THIRD-COMPETITIVE-PARITY-EXPANSIONS.md`;
- `docs/MODULES/THEME-WORKSPACE-CHILD-THEME-CUSTOMIZATION-EXHAUSTIVE-SPEC.md`;
- `docs/QUALITY/THIRD-COMPETITIVE-EXPANSION-EVIDENCE-MASTER-PLAN.md`;
- `docs/MODULE-CATALOG-EXPANSION-ADR-0197.md`.

Add **Surface 56 — Theme Workspace, Child Theme & Theme Customization Manager**.

Current product denominator becomes:
- planned surfaces: **56/56**;
- logical Multisite mappings: **56/56**;
- module-wide AI Prompt mappings: **56/56**;
- implementation authorization: **0/56**.

Historical 31/43/48/50/55 denominators remain earlier-scope snapshots.

## Existing owner expansions

The audit does **not** create redundant engines.

Accepted parity refinements:
- Surface 53 Font Library — Use Any Font-class upload/conversion/theme.json/builder parity;
- Surface 55 Staging/Clone/Migration — WP Migrate-class export/import/push/pull/profile/CLI parity;
- Surface 49 + Admin Menu/Dashboard/Protector/OAuth — White Label CMS/LoginPress parity;
- Surface 51 Content Order & Sequence — schema-aware post/entity duplication/clone operations;
- Audit & Observability — Activity Timeline/Audit Console, source/AI attribution, reports/alerts/external sinks;
- Fields/Relations/Tables/Settings/Forms/REST/Builder adapters — CMB2/Meta Box/wpmetabox parity and migration;
- Reset Manager — snapshot/partial reset/WP-CLI parity;
- Settings Page/Field architecture — Redux-class declarative options framework parity;
- CPT/Taxonomy + Listings/Admin Columns/Multisite — CPTUI parity.

## Evidence reservations

New supplemental namespaces:
- UAF 0/176;
- MIG 0/176;
- WLB 0/176;
- DUP 0/176;
- ALX 0/176;
- MBX 0/176;
- THM 0/176;
- RSX 0/176;
- RDX 0/176;
- CPTX 0/176.

Total new reserved fixtures: **1,760**, executed **0**.

Existing canonical namespaces remain independent and are not upgraded by this decision.

## Preserved boundaries

- presentation hiding ≠ authorization;
- login branding ≠ authentication authority;
- audit event/AI attribution ≠ identity or business authority;
- DB snapshot ≠ full backup;
- migration replacement ≠ merge;
- clone ≠ original entity identity;
- competitor field schema ≠ WPE canonical storage;
- child-theme source customization ≠ Admin Theme branding;
- child-theme workspace ≠ arbitrary PHP execution console;
- local fonts ≠ automatic legal/GDPR compliance;
- declarative settings compiler ≠ eval/compiler PHP runtime.

## Surface 56 server-code boundary

Theme Workspace may analyze, scaffold, diff, preview and package declarative theme assets. It must not provide arbitrary PHP execution via an admin textarea. Server-side PHP/theme code goes through Extension SDK/VCS/CI/release and requires development authorization.

## Multisite / AI Prompt

Surface 56 receives explicit logical Multisite and AI Prompt mapping in its product spec:
- site vs network theme authority;
- network installation/enable controls;
- parent dependency;
- AI read/analyze/draft declarative changes;
- install/activate/network-enable off by default;
- PHP source generation routed to governed development workflow.

Therefore the current logical mappings are **56/56** for both Multisite and AI Prompt product coverage.

## Work coordination

Owner-requested third audit interrupt reserves:
- WP100 — source/market audit;
- WP101 — font parity;
- WP102 — migration parity;
- WP103 — white-label/login parity;
- WP104 — content duplication parity;
- WP105 — activity/audit parity;
- WP106 — CMB2/Meta Box/wpmetabox parity;
- WP107 — Surface 56 Theme Workspace;
- WP108 — Reset parity;
- WP109 — Redux settings-framework parity;
- WP110 — CPTUI parity;
- WP111 — evidence/catalog/ADR/governance synchronization.

All are planning packages. After completion, canonical work returns to **WP66 — F04 Decision/Formula/Scoring detailed evidence**.

## Development gate

No font conversion, migration, DB snapshot/reset, content duplication, audit logging, theme creation/file write/activation, settings compilation, CPT registration, provider call, AI/MCP session, test, benchmark or runtime mutation is authorized or executed by ADR-0197.

ADR-0014 explicit scoped owner development consent remains controlling.