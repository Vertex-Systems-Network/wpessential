# WPEssential — Module Catalog Expansion after ADR-0195

Status: **Phase 0 product planning / no development authorization**  
Date: **2026-08-29**

## 1. Current denominator

ADR-0195 expands the current planned WPEssential product scope from **50 to 55 surfaces**.

Historical snapshots remain valid:
- original: 31;
- ADR-0177: 43;
- ADR-0188: 48;
- ADR-0194: 50;
- ADR-0195: **55**.

Current product-level planning state:
- exhaustive planned surfaces: **55/55**;
- logical Multisite mapping: **55/55**;
- module-wide AI Prompt mapping: **55/55**;
- production implementation authorization: **0/55**;
- implemented/runtime certified: none.

## 2. Surface 51 — Content Order & Sequence Manager

Canonical spec: `MODULES/CONTENT-ORDER-SEQUENCE-EXHAUSTIVE-SPEC.md`.

Core capability:
- editorial/manual order definitions;
- posts/CPTs/terms/registered entity adapters;
- hierarchy-aware sequencing;
- drag/drop + keyboard ordering;
- `menu_order` compatibility without universal query hijack;
- contextual/query-local sequences;
- conflict detection, revisions, migration and Multisite.

Evidence: `ORD-001…ORD-176`, executed 0.

## 3. Surface 52 — Security Integrity, Malware & Vulnerability Scanner

Canonical spec: `MODULES/SECURITY-INTEGRITY-SCANNER-EXHAUSTIVE-SPEC.md`.

Core capability:
- integrity baselines and checksums;
- package/source provenance;
- malware/signature/heuristic scans;
- vulnerability intelligence adapters;
- reputation/blocklist monitoring;
- remediation/quarantine plans;
- post-hack evidence workflow;
- security posture reporting.

Protector remains the separate request/access hardening owner.

Evidence: `SEC-001…SEC-176`, executed 0.

## 4. Surface 53 — Font Library, Typography & Delivery Manager

Canonical spec: `MODULES/FONT-TYPOGRAPHY-DELIVERY-EXHAUSTIVE-SPEC.md`.

Core capability:
- font family/variant/variable-font registry;
- local uploads and provider adapters;
- licensing/provenance metadata;
- typography profiles and assignments;
- privacy/local-hosting migration;
- theme/builders integration;
- subset/preload/font-display/performance diagnostics.

Admin Theme remains the wp-admin theming owner.

Evidence: `FNT-001…FNT-176`, executed 0.

## 5. Surface 54 — User Data Stores, Favorites & Collections

Canonical spec: `MODULES/USER-DATA-STORES-FAVORITES-COLLECTIONS-EXHAUSTIVE-SPEC.md`.

Core capability:
- favorites/wishlists/bookmarks/compare/recent/custom stores;
- authenticated + guest ownership;
- merge-on-login with conflict policy;
- limits/expiry/order/entry metadata;
- Query/Dynamic Listing/REST integration;
- shared/team stores;
- privacy/export/erase;
- Woo adapter boundary.

Evidence: `UDS-001…UDS-176`, executed 0.

## 6. Surface 55 — Staging, Clone & Migration Manager

Canonical spec: `MODULES/STAGING-CLONE-MIGRATION-EXHAUSTIVE-SPEC.md`.

Core capability:
- environment topology;
- staging creation;
- clone/migration;
- safe staging-side provider behavior;
- serialization-safe mappings;
- push/pull plans;
- drift/conflict detection;
- remote transfer/checkpoints;
- Multisite conversion profiles;
- target verification/recovery.

Backup Manager remains the recovery-artifact owner.

Evidence: `STG-001…STG-176`, executed 0.

## 7. Existing owner expansions accepted with ADR-0195

No duplicate modules are introduced for capabilities that already have a canonical WPE owner.

- Backup Manager receives advanced/incremental/chain/CLI/MCP parity (`BKX`).
- Surface 28 receives Media Asset Replacement Lifecycle (`MRL`).
- Profile/Membership/Forms/Role/OAuth/Woo receive Profile Builder parity (`PBX`).
- Tables/Relations/Query/Listings/DVR/Conditions/Reference Data/Blueprint AI receive JetEngine parity (`JEX`).
- Link Health receives local/cloud/hybrid + quick-fix parity (`LHX`).
- Safe Script/Tag receives Header/Footer Code Manager parity (`HFC`).

Canonical addendum: `MODULES/SECOND-COMPETITIVE-PARITY-EXISTING-SURFACES-ADDENDUM.md`.

## 8. Evidence truth

All new/supplemental evidence is documented-only and has **zero executed fixtures**.

No production code or WordPress runtime action is implied by this catalog expansion.