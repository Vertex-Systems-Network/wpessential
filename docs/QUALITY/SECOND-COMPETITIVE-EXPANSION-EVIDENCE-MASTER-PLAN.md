# WPEssential — Second Competitive Expansion Evidence Master Plan

Status: **Phase 0 evidence planning / execution pending / no development authorization**  
Date: **2026-08-29**

## 1. Purpose

Reserve executable-evidence namespaces for the owner-requested Backup/Media/Ordering/Security/Fonts/Profile/Crocoblock audit. These counters are documentation reservations only; no fixture has executed.

## 2. New surfaces

| Surface | Namespace | Planned | Executed |
|---|---:|---:|---:|
| 51 Content Order & Sequence Manager | `ORD-001…ORD-176` | 176 | 0 |
| 52 Security Integrity, Malware & Vulnerability Scanner | `SEC-001…SEC-176` | 176 | 0 |
| 53 Font Library, Typography & Delivery Manager | `FNT-001…FNT-176` | 176 | 0 |
| 54 User Data Stores, Favorites & Collections | `UDS-001…UDS-176` | 176 | 0 |
| 55 Staging, Clone & Migration Manager | `STG-001…STG-176` | 176 | 0 |

## 3. Existing-surface parity envelopes

| Expansion | Namespace | Planned | Executed |
|---|---:|---:|---:|
| Backup advanced/incremental/CLI/MCP parity | `BKX-001…BKX-176` | 176 | 0 |
| Media Replacement Lifecycle | `MRL-001…MRL-176` | 176 | 0 |
| Profile/Registration parity | `PBX-001…PBX-176` | 176 | 0 |
| JetEngine existing-surface parity | `JEX-001…JEX-176` | 176 | 0 |
| Link Health parity | `LHX-001…LHX-176` | 176 | 0 |
| Header/Footer Code migration/placement parity | `HFC-001…HFC-176` | 176 | 0 |

## 4. Reserved evidence groups — ORD

- 001–011 definition/version/context;
- 012–022 native `menu_order` adapter;
- 023–033 independent sequence storage;
- 034–044 drag/drop + keyboard UX;
- 045–055 hierarchy/sibling/reparent separation;
- 056–066 taxonomy/term ordering;
- 067–077 query/listing integration;
- 078–088 conflicts/coexistence;
- 089–099 concurrency/version conflicts;
- 100–110 revisions/rollback/import;
- 111–121 translation/Woo/provider adapters;
- 122–132 Multisite/site ordering;
- 133–143 security/permissions/audit;
- 144–154 lifecycle/delete/orphan repair;
- 155–165 10K/100K scale;
- 166–176 golden editorial/catalog scenarios.

## 5. Reserved evidence groups — SEC

- 001–011 baseline/provenance;
- 012–022 Core checksums;
- 023–033 plugin/theme integrity;
- 034–044 custom files/change classification;
- 045–055 signature/heuristic scanning;
- 056–066 vulnerability-feed mapping;
- 067–077 remote malware/blocklist providers;
- 078–088 finding confidence/severity/suppression;
- 089–099 quarantine/repair/recovery;
- 100–110 post-hack workflows;
- 111–121 hardening-owner integration;
- 122–132 privacy/provider data transfer;
- 133–143 Multisite/network ownership;
- 144–154 scanner degradation/failure truth;
- 155–165 scale/resource budgets;
- 166–176 incident golden/regression scenarios.

## 6. Reserved evidence groups — FNT

- 001–011 font metadata/format validation;
- 012–022 family/variant/variable axes;
- 023–033 upload/Asset Registry;
- 034–044 Google/provider adapters;
- 045–055 Adobe/authorized provider adapters;
- 056–066 licensing/provenance/redistribution;
- 067–077 typography profiles/assignment precedence;
- 078–088 theme.json/builder integration;
- 089–099 local hosting/external font detection;
- 100–110 font-display/preload/subset/performance;
- 111–121 privacy/CSP/mixed-content;
- 122–132 revisions/import/export;
- 133–143 Multisite shared libraries;
- 144–154 fallbacks/locale/script coverage;
- 155–165 page-load/font budget profiles;
- 166–176 visual/performance golden regressions.

## 7. Reserved evidence groups — UDS

- 001–011 store definitions/types;
- 012–022 add/remove/toggle idempotency;
- 023–033 ordering/metadata;
- 034–044 limits/expiry/eviction;
- 045–055 guest identity/storage;
- 056–066 guest→user merge;
- 067–077 Query/Listing integration;
- 078–088 REST/Abilities/rate limits;
- 089–099 privacy/export/erase;
- 100–110 shared/team collections;
- 111–121 Woo cart/order boundary;
- 122–132 imports/migration;
- 133–143 Multisite;
- 144–154 concurrency/cache isolation;
- 155–165 high-cardinality/store scale;
- 166–176 favorites/wishlist/compare golden scenarios.

## 8. Reserved evidence groups — STG

- 001–011 environment identity/topology;
- 012–022 staging creation;
- 023–033 environment side-effect safety;
- 034–044 DB/files copy scope;
- 045–055 serialization-safe URL/path transforms;
- 056–066 remote transfer/checkpoints;
- 067–077 clone identity/secrets;
- 078–088 live→staging pull;
- 089–099 staging→live push;
- 100–110 drift/conflict detection;
- 111–121 recovery points/rollback;
- 122–132 migration/cutover verification;
- 133–143 Multisite conversions;
- 144–154 privacy redaction;
- 155–165 large-site/network performance;
- 166–176 golden migration/staging regressions.

## 9. Stop-the-line examples

Certification must stop on:
- cross-user/store/site leakage;
- malware quarantine without recoverable evidence;
- migration reported complete before target verification;
- production side effects from a staging environment;
- font redistribution without allowed provenance;
- reorder query hijack outside configured context;
- backup chain missing required base;
- media rename/replacement leaving verified broken references;
- cloud link/security scan transferring protected data without configured provider/privacy policy;
- Header/Footer migration silently importing PHP/server code.

## 10. Current truth

All namespaces above are **0 executed**. No benchmark, WordPress runtime test, scan, staging clone, migration, reorder, font download, favorites mutation, backup run, media replacement or provider/MCP call has occurred.