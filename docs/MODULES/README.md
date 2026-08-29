# WPEssential — Detailed Module Specifications

Status: **Phase 0 — 56/56 product surfaces Exhaustively specified; supplemental evidence planning remains; development not authorized**

This directory is the product-behavior source of truth. Detailed files define screens, fields, options, defaults, validation, permissions, lifecycle, integrations, failure behavior and evidence expectations.

## Development consent gate

Production development remains prohibited until explicit scoped owner authorization under `/DEVELOPMENT-CONSENT.md` and ADR-0014. `continue`, `resume`, planning/ADR acceptance or Phase 0 completion are not consent.

## Current maturity

- Product-option result: **56/56 Exhaustive**.
- Logical Multisite mapping: **56/56**.
- Module-wide AI Prompt mapping: **56/56**.
- Development authorization: **0/56**.
- Implemented/runtime certified: **none**.

Scope lineage remains 31 → 43 → 48 → 50 → 55 → current 56; earlier denominators remain historical snapshots.

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

## Evidence-planning progress

Exact universal/adapter planning exists for SBP/ANL/SRH/DEC/LED/RSV/PLC/EXP/DOC/SYN/GEO/AIP/WCA.

WP113 / ADR-0208 completed RDR/SRT/DMY/LNK/DBM/PDO/MIR: **1,232/1,232 exact / 0 executed**.

WP114 / ADR-0209 completed:
- MPR — Membership parity — 176/176 / 0;
- RPR — Role parity — 176/176 / 0;
- ATM — Admin Theme — 176/176 / 0;
- MDP — Media Performance — 176/176 / 0;
- STM — Safe Script/Tag — 176/176 / 0.

WP114 total: **880/880 exact / 0 executed**.

Known remaining exact planning gap is **3,696 definitions / 21 namespaces**:
- **WP115 CURRENT** — ORD/SEC/FNT/UDS/STG/BKX/MRL/PBX/JEX/LHX/HFC = 1,936;
- WP116 — UAF/MIG/WLB/DUP/ALX/MBX/THM/RSX/RDX/CPTX = 1,760.

## Ownership boundaries

- presentation hiding/branding/navigation ≠ authorization;
- Membership, Role/Capability, Policy, Enrollment and Entitlement remain distinct;
- WordPress meta-cap + WPE Policy remain permission authority; Super Admin ≠ ordinary role;
- Admin Theme cannot hide critical recovery/security controls as enforcement;
- media performance hints ≠ measured CWV and private media cannot leak through optimization;
- Safe Script/Tag is browser-side only and never enables PHP/eval/server code, CSP/consent weakening or frontend secret interpolation;
- Backup ≠ Staging/Migration;
- Theme Workspace cannot become arbitrary live PHP execution;
- WooCommerce remains adapter-owned rather than a second commerce engine;
- AI/MCP cannot bypass normal Policy/approval.

## Current conclusion

**Product-option planning:** 56/56 Exhaustive.  
**WP113:** DONE / ADR-0208.  
**WP114:** DONE / ADR-0209.  
**Remaining exact supplemental planning:** WP115–WP116 / 3,696 definitions.  
**Technical/runtime certification:** not reached globally.  
**Development:** not started, not authorized.

Current safe planning work: **WP115 — Second Competitive exact executable-evidence specification (`ORD/SEC/FNT/UDS/STG/BKX/MRL/PBX/JEX/LHX/HFC`, 1,936 fixtures).**