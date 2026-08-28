# WPEssential — Detailed Module Specifications

Status: **Phase 0 — 56/56 product surfaces Exhaustively specified; exact supplemental evidence planning remains; development not authorized**

This directory is the product-behavior source of truth. `docs/MODULE-CATALOG.md` and its expansion addenda provide high-level catalog context; detailed files here define screens, fields, toggles, actions, defaults, validation, permissions, lifecycle, integrations, failure behavior and evidence expectations.

## Development consent gate

Even when a module is Exhaustive or has Accepted semantics, production development remains prohibited until the owner explicitly authorizes scoped development under `/DEVELOPMENT-CONSENT.md` and ADR-0014.

`continue`, `resume`, planning approval, ADR approval or Phase 0 planning completion do not count as development consent.

## Maturity model

1. Inventory
2. Behavioral
3. **Exhaustive Option Spec**
4. Accepted semantics
5. Technical Ready
6. Authorized
7. Implemented
8. Verified

Current product-option result: **56/56 surfaces are Exhaustive.**  
Current development authorization: **0/56 authorized.**

See `OPTION-COVERAGE-MATURITY.md` for the exact current ledger.

## Scope lineage

- surfaces 1–31: original detailed module set;
- surfaces 32–43: ADR-0177 universal foundations;
- surfaces 44–48: market expansion through ADR-0188;
- surfaces 49–50: access/admin/media/code expansion through ADR-0194;
- surfaces 51–55: ADR-0195 second competitive expansion;
- surface 56: ADR-0197 third competitive expansion.

Historical 31/43/48/50/55 statements remain historical snapshots rather than current denominator claims.

## Current product surfaces

1. Custom Post Types Builder
2. Taxonomy Builder
3. Custom Fields Builder
4. Relations Builder
5. Status Manager
6. Custom Query Builder
7. Custom Tables Builder
8. Admin Columns Builder
9. Dynamic Listings / Templates
10. Dashboard Widgets Manager
11. Custom Admin Menu Builder
12. Settings Page Builder
13. Frontend Dashboard Builder
14. User Profile Builder
15. Membership System
16. Builder Widgets Builder
17. Forms & Workflow Builder
18. Cron Job Builder
19. Notification System
20. Emails Builder
21. Message & Chat System
22. REST API Builder
23. Webhooks & Connections Manager
24. Backup Manager
25. Reset Manager
26. Import / Export
27. Protector
28. Watermarker / Media Rules + Performance Delivery
29. XML-RPC Manager
30. Role & Capability Manager
31. Platform Account / Docs / Support / Diagnostics
32. Solution Blueprint & Application Composer
33. Analytics, Event Tracking & Journey Intelligence
34. Search & Indexing Engine
35. Decision, Formula, Scoring & Ranking Studio
36. Ledger, Balance & Movement Engine
37. Resource Scheduling, Availability & Reservation Engine
38. Experience Placement & Personalization Manager
39. Experimentation & Feature Rollout Manager
40. Documents, Records & Template Generation
41. Data Sync, ETL & Integration Pipelines
42. Geospatial, Location & Territory Engine
43. AI Gateway, Knowledge & Copilot Studio
44. URL Redirection & Routing Manager
45. Search, Replace & Data Transformation Engine
46. Dummy Data, Synthetic Dataset & Fixture Studio
47. Link Health, Broken Link & Crawl Intelligence
48. Database Maintenance, Cleanup & Storage Health
49. Admin Theme, Branding & Experience Manager
50. Safe Script, Tag & Code Injection Manager
51. Content Order & Sequence Manager
52. Security Integrity, Malware & Vulnerability Scanner
53. Font Library, Typography & Delivery Manager
54. User Data Stores, Favorites & Collections
55. Staging, Clone & Migration Manager
56. Theme Workspace, Child Theme & Theme Customization Manager

The Free/Pro distribution contract remains governed separately; this list describes product surfaces, not entitlement.

## Shared specification contracts

- `SPECIFICATION-STANDARD.md`
- `COMMON-OPTION-CONTRACTS.md`
- `OPTION-INVENTORY.md`
- `OPTION-COVERAGE-MATURITY.md`

Original and expansion specifications remain authoritative for their owned behavior. Current ownership/counters are reconciled by the maturity ledger, Checkpoint and accepted ADRs.

## Important ownership boundaries

- presentation hiding is not authorization;
- Membership, Role/Capability, Policy and entitlement remain distinct;
- Audit/AI attribution does not grant identity/privilege;
- generic formula/rank does not become Policy or business mutation authority;
- Backup is not Staging/Migration;
- Safe Script/Tag remains browser-side and does not permit arbitrary PHP/eval;
- Theme Workspace may analyze/scaffold/package declarative theme assets but must not become arbitrary live PHP execution;
- WooCommerce integration remains an adapter, not a second commerce truth engine;
- redirect simulation is not authorization;
- Search/Replace Dry Run is not mutation;
- synthetic fixtures are not production truth;
- inconclusive link checks are not proven broken;
- cleanup candidates are not deletion authority;
- market/planning signals are not product acceptance or implementation consent.

## Evidence-planning status

Exact detailed executable-evidence specifications exist for SBP/ANL/SRH/DEC/LED/RSV/PLC/EXP/DOC/SYN/GEO/AIP/WCA and many established shared/module protocols.

WP113 / ADR-0208 additionally completed all seven Market Expansion exact protocols:
- RDR, SRT, DMY, LNK, DBM, PDO, MIR;
- **1,232/1,232 exact fixtures documented**;
- **0 executed**.

ADR-0207 originally found 5,808 supplemental exact definitions missing. Current remaining known planning gap is **4,576 definitions across 26 namespaces**:
- **WP114 CURRENT** — MPR/RPR/ATM/MDP/STM = 880;
- WP115 = 1,936;
- WP116 = 1,760.

Therefore the product-option gate is complete, but the overall Phase 0 evidence-planning closure gate is not yet complete.

## Exhaustive-spec rule

Every module must maintain, where applicable:
- list/editor screens and states;
- each field/toggle/selector/action/default;
- conditional visibility/dependencies;
- normalization/validation/sanitization;
- lifecycle/run semantics;
- permissions/re-auth;
- revisions/import/export;
- health/audit/observability;
- empty/loading/error/offline/degraded/read-only states;
- cross-module dependencies and asset isolation;
- accessibility/keyboard behavior;
- performance and destructive safeguards;
- Multisite, privacy/retention and AI Prompt behavior;
- explicit acceptance/evidence requirements.

If future research uncovers a missing meaningful option, update the spec before or with the coherent planning change. Implementation must not silently invent product semantics.

## Architecture/readiness sources

- `../IMPLEMENTATION-READINESS-MATRIX.md`
- `../OPEN-DECISIONS-REGISTER.md`
- `../QUALITY/P0-FINAL-PREDEVELOPMENT-CLOSURE-READINESS-AUDIT.md`
- `/DEVELOPMENT-CONSENT.md`
- `../DECISIONS/`

## Current conclusion

**Product-option planning:** 56/56 Exhaustive.  
**Market Expansion exact evidence:** complete / ADR-0208 / 1,232 documented / 0 executed.  
**Remaining exact supplemental planning:** WP114–WP116 / 4,576 definitions.  
**Technical/runtime certification:** not reached globally.  
**Executable development:** not started, not authorized.

Current safe planning work: **WP114 — First Competitive exact executable-evidence specification (`MPR/RPR/ATM/MDP/STM`, 880 fixtures).**