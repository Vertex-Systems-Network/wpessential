# ADR-0195 — Second Competitive Audit and 55-Surface Product Expansion

Status: **Accepted planning decision / execution pending**  
Date: **2026-08-29**

## Context

The owner requested a second competitive audit covering Backuply, Enable Media Replace, Post Types Order, Header Footer Code Manager, Sucuri Security, Broken Link Checker, BackWPup, Custom Fonts, Intuitive Custom Post Order, Fonts Plugin/Olympus Google Fonts, Profile Builder, WPvivid Backup & Migration, Crocoblock public repositories and JetEngine.

The audit followed ADR-0189…ADR-0194, which had already expanded Membership, Role & Capability, Admin Theme, Media Performance and Safe Script/Tag. The task was therefore not to duplicate market plugins mechanically, but to decide whether each capability belongs in an existing WPE owner or requires a genuinely distinct product surface.

Primary research record:
- `docs/RESEARCH/SECOND-COMPETITIVE-AUDIT-BACKUP-MEDIA-ORDER-SECURITY-FONTS-PROFILE-CROCOBLOCK-2026-08.md`

Existing-surface parity addendum:
- `docs/MODULES/SECOND-COMPETITIVE-PARITY-EXISTING-SURFACES-ADDENDUM.md`

Evidence reservations:
- `docs/QUALITY/SECOND-COMPETITIVE-EXPANSION-EVIDENCE-MASTER-PLAN.md`

No competitor code was copied into WPEssential and no runtime plugin installation/execution occurred.

## Decision

Accept the second competitive audit and expand the WPEssential planned product denominator from **50 to 55 surfaces**.

### New Surface 51 — Content Order & Sequence Manager

Accept `docs/MODULES/CONTENT-ORDER-SEQUENCE-EXHAUSTIVE-SPEC.md`.

Purpose:
- deterministic editorial ordering for posts/CPTs/terms/registered entities;
- native `menu_order` compatibility without treating it as the universal model;
- independent contextual sequences;
- hierarchy-aware ordering;
- accessible drag/keyboard reorder;
- conflict detection and query explainability;
- revisions/import/migration/Multisite.

Evidence namespace: **ORD-001…ORD-176**, executed **0/176**.

### New Surface 52 — Security Integrity, Malware & Vulnerability Scanner

Accept `docs/MODULES/SECURITY-INTEGRITY-SCANNER-EXHAUSTIVE-SPEC.md`.

Purpose:
- Core/plugin/theme/custom-file integrity;
- provenance and checksum baselines;
- malware/signature/heuristic scanning;
- vulnerability-feed adapters;
- blocklist/reputation checks;
- quarantine/repair plans;
- post-hack evidence workflows;
- security posture reporting.

Boundary: Protector remains request/access hardening. This surface does **not** falsely claim to be an upstream network WAF or volumetric DDoS service.

Evidence namespace: **SEC-001…SEC-176**, executed **0/176**.

### New Surface 53 — Font Library, Typography & Delivery Manager

Accept `docs/MODULES/FONT-TYPOGRAPHY-DELIVERY-EXHAUSTIVE-SPEC.md`.

Purpose:
- local/provider font library;
- variants/variable fonts;
- licensing/provenance;
- typography profiles/assignments;
- Google/Adobe/registered provider adapters;
- local-hosting/privacy migration;
- theme/builder adapters;
- preload/subset/font-display/performance diagnostics.

Boundary: Admin Theme still owns wp-admin visual theming; this surface owns reusable font/typography assets and frontend delivery.

Evidence namespace: **FNT-001…FNT-176**, executed **0/176**.

### New Surface 54 — User Data Stores, Favorites & Collections

Accept `docs/MODULES/USER-DATA-STORES-FAVORITES-COLLECTIONS-EXHAUSTIVE-SPEC.md`.

Purpose:
- favorites/wishlists/bookmarks/compare/recently-viewed/custom collections;
- authenticated and guest stores;
- safe guest→user merge;
- limits/expiry/order/metadata;
- Query/Listings/REST integration;
- team/shared collections;
- Woo adapter transfer semantics;
- privacy/export/erase/Multisite.

Boundary: store entry ≠ authorization ≠ cart line ≠ reservation ≠ inventory allocation ≠ order.

Evidence namespace: **UDS-001…UDS-176**, executed **0/176**.

### New Surface 55 — Staging, Clone & Migration Manager

Accept `docs/MODULES/STAGING-CLONE-MIGRATION-EXHAUSTIVE-SPEC.md`.

Purpose:
- staging environment lifecycle;
- clone/migration topology;
- environment-safe provider behavior;
- push/pull and drift/conflict detection;
- serialization-safe URL/path mapping;
- remote transfer/checkpoints;
- Multisite migration profiles;
- target verification and recovery points.

Boundary: Backup Manager owns recovery artifacts and backup truth. Surface 55 owns persistent environments, clone/migration and promotion semantics.

Evidence namespace: **STG-001…STG-176**, executed **0/176**.

## Existing-surface expansions accepted

Accept the parity addendum for:

- Backup Manager advanced/incremental/direct-restore/WP-CLI/MCP planning — `BKX-001…BKX-176`, executed 0;
- Media Asset Replacement Lifecycle — `MRL-001…MRL-176`, executed 0;
- Profile/Registration parity across Profile/Membership/Forms/Role/OAuth/Woo — `PBX-001…PBX-176`, executed 0;
- JetEngine/Crocoblock parity refinements across Tables/Relations/Query/Listings/DVR/Conditions/Reference Data/Blueprint AI — `JEX-001…JEX-176`, executed 0;
- Link Health parity refinements — `LHX-001…LHX-176`, executed 0;
- Header/Footer Code migration/placement parity on Surface 50 — `HFC-001…HFC-176`, executed 0.

These supplements do not replace the existing canonical evidence protocols and do not promote any static design into runtime certification.

## Product mapping

The five new surfaces include explicit Multisite and AI/Ability boundaries. Therefore the logical product-level planning maps become:

- planned surfaces: **55/55**;
- logical Multisite product mapping: **55/55**;
- module-wide AI Prompt/product mapping: **55/55**;
- authorized for production implementation: **0/55**;
- implemented: **0**;
- runtime verified: **0**.

The mapping claim means product semantics are specified; it does not claim any Multisite or AI runtime test has executed.

## Preserved architecture boundaries

- WordPress remains authorization authority for native capabilities.
- Membership remains separate from role/capability, billing and access Policy.
- Protector is not a malware scanner/WAF.
- Scanner findings are evidence, not permission to auto-delete.
- Backup ≠ staging environment lifecycle.
- Media replacement delegates broad reference transformation to Search/Replace rather than ad-hoc SQL mutation.
- Safe Script/Tag remains browser-side governed code only; no PHP/eval/arbitrary SQL/shell.
- Font delivery does not invent redistribution rights or legal compliance.
- User collections are not business/authorization truth.
- Content ordering must not globally hijack unrelated queries.
- JetEngine parity is implemented through reusable WPE platform owners, not a monolithic JetEngine clone.
- AI/MCP may draft/explain/validate within Policy; high-risk publication/mutation remains separately approved.

## Work coordination

Reserve/complete the owner-requested interrupt as:
- WP90 — second source/market audit — DONE;
- WP91 — Backup + staging/clone/migration parity — DONE;
- WP92 — Media Replacement Lifecycle parity — DONE;
- WP93 — Content Order & Sequence Surface 51 — DONE;
- WP94 — Security Integrity Surface 52 — DONE;
- WP95 — Font/Typography Surface 53 — DONE;
- WP96 — Profile/Registration parity — DONE;
- WP97 — Crocoblock/JetEngine parity + Surface 54 — DONE;
- WP98 — Header/Footer Code + Link Health parity — DONE;
- WP99 — consolidated 55-surface governance synchronization — DONE once checkpoint/catalog/PR/Linear mirror is reconciled.

After the interrupt, the previously reserved universal-foundation work resumes at **WP65 — F03 Search & Indexing detailed executable-evidence specification**. WP66…WP74 retain their prior meanings and are not reused.

## Development gate

ADR-0014 remains controlling.

No source/runtime implementation, package setup, DB/schema mutation, WordPress execution, backup/restore/staging/migration, media replacement, reorder, scanner execution, font download/registration, favorites mutation, provider/API/AI/MCP call, test, benchmark, build, deployment or scheduled workflow installation is authorized by this ADR.

Production development authorization remains **NOT GRANTED / 0/55**.