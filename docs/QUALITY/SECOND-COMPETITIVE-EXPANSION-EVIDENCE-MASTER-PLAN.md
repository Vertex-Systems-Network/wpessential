# WPEssential — Second Competitive Expansion Evidence Master Plan

Status: **WP115 CURRENT / all 11 group envelopes fixed / NOT EXECUTED / no development authorization**  
Date: **2026-08-29**

WP115 target: **11 namespaces × 176 = 1,936 exact fixtures**. No fixture has executed.

The five new-surface envelopes were already explicit. WP115 preflight found six existing-surface supplements had reserved IDs but not explicit 16-group ranges in this master plan. Their ownership is now fixed from the accepted `SECOND-COMPETITIVE-PARITY-EXISTING-SURFACES-ADDENDUM.md` before exact fixture enumeration.

Formal WP115 exact completion/acceptance remains pending a later ADR.

## Namespace/group ownership

### ORD — Content Order & Sequence
1 001–011 definition/version/context; 2 012–022 native `menu_order`; 3 023–033 independent sequence storage; 4 034–044 drag/drop + keyboard; 5 045–055 hierarchy/sibling/reparent; 6 056–066 taxonomy/term ordering; 7 067–077 Query/Listing; 8 078–088 conflicts/coexistence; 9 089–099 concurrency; 10 100–110 revisions/rollback/import; 11 111–121 translation/Woo/provider; 12 122–132 Multisite; 13 133–143 security/audit; 14 144–154 lifecycle/orphan repair; 15 155–165 scale; 16 166–176 golden scenarios.

### SEC — Security Integrity/Malware/Vulnerability
1 baseline/provenance; 2 Core checksums; 3 plugin/theme integrity; 4 custom files/change classification; 5 signature/heuristic scan; 6 vulnerability-feed mapping; 7 remote malware/blocklist providers; 8 confidence/severity/suppression; 9 quarantine/repair/recovery; 10 post-hack workflows; 11 hardening-owner integration; 12 privacy/provider transfer; 13 Multisite; 14 degradation/failure truth; 15 scale/resource; 16 incident golden regressions.

### FNT — Font Library/Typography/Delivery
1 metadata/format; 2 family/variant/variable axes; 3 upload/Asset Registry; 4 Google/provider; 5 Adobe/authorized provider; 6 licensing/provenance/redistribution; 7 typography profiles/precedence; 8 theme.json/builder; 9 local/external detection; 10 display/preload/subset/performance; 11 privacy/CSP/mixed-content; 12 revisions/import/export; 13 Multisite library; 14 fallbacks/locale/script coverage; 15 page-load budgets; 16 visual/performance golden.

### UDS — User Data Stores/Favorites/Collections
1 definitions/types; 2 add/remove/toggle idempotency; 3 ordering/metadata; 4 limits/expiry/eviction; 5 guest identity/storage; 6 guest→user merge; 7 Query/Listing; 8 REST/Abilities/rate; 9 privacy/export/erase; 10 shared/team; 11 Woo cart/order boundary; 12 imports/migration; 13 Multisite; 14 concurrency/cache isolation; 15 high-cardinality scale; 16 favorites/wishlist/compare golden.

### STG — Staging/Clone/Migration
1 environment/topology; 2 staging creation; 3 side-effect safety; 4 DB/files scope; 5 serialization-safe URL/path transforms; 6 remote transfer/checkpoints; 7 clone identity/secrets; 8 live→staging pull; 9 staging→live push; 10 drift/conflicts; 11 recovery/rollback; 12 migration/cutover verify; 13 Multisite conversion; 14 privacy redaction; 15 large-site/network performance; 16 golden regressions.

### BKX — Backup advanced parity
1 profile identity/capabilities; 2 full/incremental/differential semantics; 3 chain/base graph; 4 changed-file/hash candidates; 5 DB incremental/change capture; 6 pre-update/pre-migration recovery point; 7 multi-destination fan-out; 8 provider/direct-restore matrix; 9 standalone recovery package; 10 WP-CLI Abilities; 11 MCP/AI governance; 12 health/provenance/restore confidence; 13 reconcile/unknown provider outcome; 14 Multisite/environment/lifecycle; 15 chain/storage/resource budgets; 16 recovery/migration golden.

### MRL — Media Replacement Lifecycle
1 Plan identity/mode; 2 preserve attachment identity/current URL; 3 rename/repath/reference Plan; 4 supersede old; 5 retained revision restore; 6 MIME/dimensions/metadata/storage preflight; 7 reference graph/builders/serialized; 8 offload/CDN/private ownership; 9 source immutability/checksum; 10 derivative regeneration; 11 cache/CDN/offload invalidation; 12 Search/Replace delegation; 13 external editing provider; 14 Multisite/lifecycle/privacy; 15 jobs/concurrency/scale; 16 replacement golden regressions.

### PBX — Profile/Registration parity
1 profile composition identity; 2 multiple role/segment compositions; 3 multi-step edit; 4 layout/repeater/group; 5 field approval; 6 public permalink/privacy; 7 directory/search/facets; 8 avatar/media ownership; 9 import/export; 10 account nav/wp-admin Policy delegation; 11 registration/Membership parity; 12 reauth/2FA/passkey/OAuth boundaries; 13 Woo mappings; 14 Policy/REST/Abilities/AI; 15 Multisite/global-user; 16 profile/registration golden.

### JEX — JetEngine existing-surface parity
1 CCT-style entity preset; 2 Custom Table/schema isolation; 3 public route/listing; 4 import/export/REST/relation; 5 CPT-vs-table guidance; 6 physical relation table; 7 relation meta/cardinality/UX; 8 relation REST Ability; 9 Query provider matrix; 10 Relations/UDS/REST/merged query; 11 endpoint permission/rate/cache; 12 Dynamic Table/listing/chart; 13 Conditional Visibility/DVR; 14 Reference Data Sets; 15 AI Website Structure/Blueprint; 16 Multisite/scale/coexistence golden.

### LHX — Link Health parity
1 Local/Remote/Hybrid profile; 2 cloud opt-in/privacy; 3 occurrence source types; 4 result provenance; 5 Edit/Unlink Fix Plan; 6 Ignore/Snooze/Recheck; 7 notifications; 8 saved views/bulk triage; 9 network summary; 10 provider auth/rate/Safe HTTP/SSRF; 11 jobs/checkpoints/cache; 12 Redirect/SearchReplace/Media composition; 13 privacy/redaction; 14 Multisite/domain/lifecycle; 15 provider failure/scale; 16 local/cloud/hybrid golden.

### HFC — Header/Footer Code parity
1 source detection/import provenance; 2 browser-snippet type/placement; 3 latest-N-posts; 4 taxonomy presets; 5 selected CPT instances; 6 desktop/mobile preset; 7 shortcode/block placement; 8 creator/editor/timestamps; 9 classic-theme compatibility; 10 block-theme compatibility; 11 builder compatibility; 12 occurrence diagnostics; 13 consent/CSP/SRI/environment; 14 PHP/server-code rejection; 15 Multisite/import/coexistence; 16 security/cache/performance/migration golden.

## Stop-the-line boundaries

- ordering only within configured context;
- security finding retains provenance/confidence and recovery-safe repair;
- font delivery/local hosting does not imply redistribution rights;
- UDS state never becomes Woo cart/order truth;
- staging/clone cannot cause unapproved production side effects and clone ≠ same identity;
- Backup remains backup/recovery owner; Staging owns staging topology/push/pull;
- backup chain needs valid base/dependency semantics;
- media replacement preserves source/private-delivery/reference truth;
- profile/account security composes native/provider auth rather than replacing session/password truth;
- JEX composes existing canonical Fields/Relations/Query/Tables/DVR/Blueprint owners;
- cloud Link Health follows privacy/provider/Safe HTTP constraints;
- HFC rejects PHP/server snippets and preserves STM consent/CSP/security boundaries.

All WP115 namespaces remain **0 executed**. Production development remains blocked by ADR-0014.